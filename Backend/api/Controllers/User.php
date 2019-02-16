<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

use \Database as Database;

class User extends Database {
  function __construct(){
    parent::__construct();
  }

  public function login(Request $request,  Response $response, $args = []){
    global $CONF;
    $data = $request->getParsedBody();

    if(empty($data['username']) || empty($data['password'])){
      $resp = [
        'status' => 'error',
        'message' => 'No username or password provided.'
      ];
      return $response->withJson($resp, 400);
    }
    
    $this->stmt = $this->database->prepare("SELECT `idu`, `username`, `email`, `password`, `suspended`, `first_name`, `last_name`, `image` as 'avatar', `cover` as 'cover' FROM `users` WHERE `username`=:user OR `email`=:user");
    $data['username'] = trim(strtolower($data['username']));
    $assets_url = $CONF['url'].'/thumb.php?src=';
    $this->stmt->bindParam(':user', $data['username'], PDO::PARAM_STR);
    $this->stmt->execute();
    
    $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
    if( password_verify($data['password'], $result['password']) ){
      switch( $result['suspended'] ){
        case 2:
            $resp = [
              'status' => 'inactive',
              'message' => 'Account is not activated yet.'
            ];
            return $response->withJson($resp, 400);
          break;
        case 1:
            $resp = [
              'status' => 'suspended',
              'message' => 'Account suspended.'
            ];
            return $response->withJson($resp, 400);
          break;

        default:
            $jwt = [
              'iat' => time(),
              'exp' => time() + ( $CONF['token_validity'] * 60 * 24 ),
              'iss' => $CONF['token_issuer'],
              'aud' => $CONF['token_audience'],
              'data' => [
                'username' => $result['username'],
                'email' => $result['email']
              ]
            ];
            $jwtEncoded = JWT::encode($jwt, $CONF['secret']);
            $resp = [
              'status' => 'success',
              'message' => 'Loged in with success.',
              'data' => [
                'id' => $result['idu'],
                'username' => $result['username'],
                'email' => $result['email'],
                'name' => $result['first_name'] + " " + $result['last_name'],
                'avatar' => $assets_url . $result['avatar'] . '&t=a&w=200&h=200',
                'cover' => $assets_url . $result['cover'] . '&t=c&w=900&h=300',
                'token' => $jwtEncoded,
              ]
            ];
            return $response->withJson($resp, 200);
          break;
      }
    } else {
      $resp = [
        'status' => 'error',
        'message' => 'Could not log in.'
      ];
      return $response->withJson($resp, 400);
    }
  }

  public function friendsList(Request $request,  Response $response, $args = []){
    global $CONF;

    $data = $request->getParsedBody();
    
    if( empty($data['user']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("SELECT usr.`idu` as 'user_id', CONCAT(usr.`first_name`, ' ', usr.`last_name`) as 'name', usr.`username` as 'username', CONCAT(:assets_url, usr.`image`) as 'avatar', usr.`online`, usr.`offline` FROM `friendships` friend JOIN `users` usr ON usr.`idu` = friend.`user1` WHERE friend.`user1` != :current AND friend.`status` = '1' UNION ALL SELECT usr.`idu` as 'user_id', CONCAT(usr.`first_name`, ' ', usr.`last_name`) as 'name', usr.`username` as 'username', CONCAT(:assets_url, usr.`image`) as 'avatar', usr.`online`, usr.`offline` FROM `friendships` friend  JOIN `users` usr ON usr.`idu` = friend.`user2` WHERE friend.`user2` != :current AND friend.`status` = '1' ORDER BY `user_id` ASC LIMIT :offset, :take");
    
    $assets_url = $CONF['url'].'/uploads/avatars/';
    $this->stmt->bindParam(':assets_url', $assets_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':current', $data['user'], PDO::PARAM_INT);

    $offset = $data['offset'] ?? 0;
    $take = $data['take'] ?? 10;
    $this->stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $this->stmt->bindParam(':take', $take, PDO::PARAM_INT);

    $this->stmt->execute();

    $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);

    $foundIds = [];
    for ($i=0, $arraySize = count($result); $i < $arraySize; $i++) { 
      if( in_array($result[$i]['user_id'], $foundIds) ){
        unset($result[$i]);
      } else {
        $foundIds[] = $result[$i]['user_id'];
      }
    }

    $currentTime = time();
    foreach ($result as &$userResult) {
      $userResult['offline'] = ( ($currentTime - $userResult['online']) > intval($this->settings['conline']) )? true: false;
    }
    $resp = [
      'status' => 'success',
      'message' => 'Friends list.',
      'data' => array_values($result)
    ];
    return $response->withJson($resp, 200);
  }


  public function userInfo(Request $request,  Response $response, $args = []){
    global $CONF;

    $data = $request->getParsedBody();

    $this->stmt = $this->database->prepare("SELECT `idu`, `username`, `email`, CONCAT(`first_name`, ' ', `last_name`) as `name`, `first_name`, `last_name`, `country`, `location`, `address`, `work`, `school`, `website`, `bio`, `facebook`, `twitter`, `gplus`, CONCAT(:avatars_url, `image`) as `avatar`, CONCAT(:covers_url, `cover`) as `cover`, `private`, `suspended`, `verified`, `gender`, `interests`, `born`, (SELECT 1 FROM `blocked` WHERE (`uid` = :user AND `by` = :current) OR (`uid` = :current AND `by` = :user) ) as `blocked` FROM `users` WHERE `idu` = :user");
    
    $avatars_url = $CONF['url'].'/uploads/avatars/';
    $covers_url = $CONF['url'].'/uploads/covers/';
    $this->stmt->bindParam(':avatars_url', $avatars_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':covers_url', $covers_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':user', $args['id'], PDO::PARAM_INT);
    $this->stmt->bindParam(':current', $data['user'], PDO::PARAM_INT);
    $this->stmt->execute();

    $result = $this->stmt->fetch(PDO::FETCH_ASSOC);

    if($result['private'] == 2 && $args['id'] != $data['user']){
      unset($result['email']);
      unset($result['country']);
      unset($result['location']);
      unset($result['address']);
      unset($result['work']);
      unset($result['school']);
      unset($result['website']);
      unset($result['bio']);
      unset($result['facebook']);
      unset($result['twitter']);
      unset($result['gplus']);
      unset($result['gender']);
      unset($result['interests']);
      unset($result['born']);
    }

    if($result['suspended'] == 1 && $args['id'] != $data['user'] ){
      $result = [
        'status' => 'User suspended.'
      ];
    }
    $result['blocked'] = ($result['blocked'] == '1' )? true: false;

    $resp = [
      'status' => 'success',
      'message' => 'User information.',
      'data' => $result
    ];
    return $response->withJson($resp, 200);
  }

  public function blockUser(Request $request,  Response $response, $args = []){
    global $CONF;

    $data = $request->getParsedBody();

    if( empty($data['user']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("INSERT INTO `blocked` (`uid`, `by`) VALUES (:user, :current)");
    $this->stmt->bindParam(':user', $args['id'], PDO::PARAM_INT);
    $this->stmt->bindParam(':current', $data['user'], PDO::PARAM_INT);
    $this->stmt->execute();

    $this->stmt = $this->database->prepare("DELETE FROM `friendships` WHERE `user1` = :user1 AND `user2` = :user2");
    $this->stmt->bindParam(':user1', $args['id'], PDO::PARAM_INT);
    $this->stmt->bindParam(':user2', $data['user'], PDO::PARAM_INT);
    $this->stmt->execute();

    $this->stmt = $this->database->prepare("DELETE FROM `friendships` WHERE `user1` = :user1 AND `user2` = :user2");
    $this->stmt->bindParam(':user2', $args['id'], PDO::PARAM_INT);
    $this->stmt->bindParam(':user1', $data['user'], PDO::PARAM_INT);
    $this->stmt->execute();

    $resp = [
      'status' => 'success',
      'message' => 'User blocked.'
    ];
    return $response->withJson($resp, 200);

  }



}
