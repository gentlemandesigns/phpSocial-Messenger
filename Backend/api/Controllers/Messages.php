<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

use \Database as Database;

class Messages extends Database {
  function __construct(){
    parent::__construct();
  }

  private function getAvatarFromFile($file){
    global $CONF;

    return $CONF['url'].'/uploads/avatars/'.$file;
  }

  public function conversationsList(Request $request,  Response $response, $args = []) {
    global $CONF;
    $data = $request->getParsedBody();

    if( empty($data['user']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("SELECT usr.`idu` as 'user_id', cht.`id` as 'id', CONCAT(usr.`first_name`, ' ', usr.`last_name`) as `name`, usr.`username` as 'username', usr.`image` as 'avatar', cht.`message` as 'message', FROM_UNIXTIME(UNIX_TIMESTAMP(cht.`time`)) as 'time', cht.`type` as `message_type`, cht.`value` as `message_value`, conv.`read` as `message_read`, cht.`from`, cht.`to`, usr.`online`, usr.`offline` FROM `conversations` conv JOIN `chat` cht on conv.`cid` = cht.`id` JOIN `users` usr ON (usr.`idu` != :current AND (usr.`idu` = cht.`from` OR usr.`idu` = cht.`to`)) WHERE (conv.`from` = :current OR conv.`to` = :current) AND usr.`idu` != :current ORDER BY cht.`time` DESC LIMIT :offset , :take");

    $assets_url = $CONF['url'].'/thumb.php?src=';
    $this->stmt->bindParam(':current', $data['user'], PDO::PARAM_INT);
    $this->stmt->bindParam(':timezoneOffset', date('Z'), PDO::PARAM_INT);

    $offset = $data['offset'] ?? 0;
    $take = $data['take'] ?? 10;
    $this->stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $this->stmt->bindParam(':take', $take, PDO::PARAM_INT);
    $this->stmt->execute();

    $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    $foundIds = [];
    for ($i=0, $arraySize = count($result); $i < $arraySize; $i++) {
      $result[$i]['message'] = html_entity_decode($result[$i]['message']);
      $result[$i]['avatar'] = $assets_url . $result[$i]['avatar'] . '&t=a&w=200&h=200';
      $result[$i]['message_thumbnail'] = $assets_url . $result[$i]['message_value'] . '&t=m&w=300&h=300';
      $result[$i]['message_value'] = $assets_url . $result[$i]['message_value'] . '&t=m&zc=3';
      if( in_array($result[$i]['user_id'], $foundIds) ){
        unset($result[$i]);
      } else {
        $foundIds[] = $result[$i]['user_id'];
      }
    }
    
    $currentTime = time();

    for ($i=0, $size = count($result); $i < $size; $i++) { 
      $message = &$result[$i];

      $message['message_read'] = ( $message['from'] == $data['user'] )? 1: $message['message_read'];
      $message['offline'] = ( ($currentTime - $message['online']) > intval($this->settings['conline']) )? true: false;
    }

    $this->stmt = $this->database->prepare("UPDATE `users` SET `users`.`online` = UNIX_TIMESTAMP(NOW()) WHERE `idu` = :user");
    $this->stmt->bindParam(':user', $data['user'], PDO::PARAM_INT);
    $this->stmt->execute();

    $resp = [
      'status' => 'success',
      'message' => array_values($result)
    ];
    return $response->withJson($resp, 200);

  }

  public function conversationLoadUnread(Request $request,  Response $response, $args = []) {
    global $CONF;
    $data = $request->getParsedBody();

    if( empty($data['user1']) || empty($data['user2']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("SELECT usr.`idu` as 'user_id', cht.`id` as 'id', CONCAT(usr.`first_name`, ' ', usr.`last_name`) as `name`, usr.`username` as 'username', usr.`image` as 'avatar', cht.`message` as 'message', FROM_UNIXTIME(UNIX_TIMESTAMP(cht.`time`) - :timezoneOffset) as 'time', cht.`type` as `message_type`, cht.`value` as `message_value` FROM `chat`cht  LEFT JOIN `users` usr ON usr.`idu` = cht.`from` WHERE (cht.`from` = :user2 AND cht.`to` = :user1) AND cht.`read` = 0 ORDER BY cht.`id` DESC");

    $assets_url = $CONF['url'].'/thumb.php?src=';
    $this->stmt->bindParam(':avatars_url', $assets_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':files_url', $files_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':user1', $data['user1'], PDO::PARAM_INT);
    $this->stmt->bindParam(':user2', $data['user2'], PDO::PARAM_INT);
    $this->stmt->bindParam(':timezoneOffset', date('Z'), PDO::PARAM_INT);
    $this->stmt->execute();

    $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);

    $this->stmt = $this->database->prepare("UPDATE `users` SET `online` = UNIX_TIMESTAMP(NOW()) WHERE `idu` = :user");
    $this->stmt->bindParam(':user', $data['user1'], PDO::PARAM_INT);
    $this->stmt->execute();

    for ($i=0, $arraySize = count($result); $i < $arraySize; $i++) {
      $result[$i]['message'] = html_entity_decode($result[$i]['message']);
      $result[$i]['avatar'] = $assets_url . $result[$i]['avatar'] . '&t=a&w=200&h=200';
      $result[$i]['message_thumbnail'] = $assets_url . $result[$i]['message_value'] . '&t=m&w=300&h=300';
      $result[$i]['message_value'] = $assets_url . $result[$i]['message_value'] . '&t=m&zc=3';
    }

    $resp = [
      'status' => 'success',
      'message' => array_values($result)
    ];
    return $response->withJson($resp, 200);
  }

  public function conversationAllChat(Request $request,  Response $response, $args = []) {
    global $CONF;
    $data = $request->getParsedBody();

    if( empty($data['user1']) || empty($data['user2']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("SELECT usr.`idu` as 'user_id', cht.`id` as 'id', CONCAT(usr.`first_name`, ' ', usr.`last_name`) as `name`, usr.`username` as 'username', usr.`image` as 'avatar', cht.`message` as 'message', FROM_UNIXTIME(UNIX_TIMESTAMP(cht.`time`) - :timezoneOffset) as 'time', cht.`type` as `message_type`, cht.`value` as `message_value` FROM `chat`cht  LEFT JOIN `users` usr ON usr.`idu` = cht.`from` WHERE (cht.`from` = :user1 AND cht.`to` = :user2) OR (cht.`from` = :user2 AND cht.`to` = :user1) ORDER BY cht.`id` DESC LIMIT :offset , :takeitems");

    $assets_url = $CONF['url'].'/thumb.php?src=';
    $this->stmt->bindParam(':avatars_url', $avatars_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':files_url', $files_url, PDO::PARAM_STR);
    $this->stmt->bindParam(':user1', $data['user1'], PDO::PARAM_INT);
    $this->stmt->bindParam(':user2', $data['user2'], PDO::PARAM_INT);
    $this->stmt->bindParam(':timezoneOffset', date('Z'), PDO::PARAM_INT);

    $offset = isset($args['offset'])? intval($args['offset']): 0;
    $take = isset($args['take'])? intval($args['take']): 10;

    $this->stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $this->stmt->bindParam(':takeitems', $take, PDO::PARAM_INT);
    $this->stmt->execute();

    $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);

    $this->stmt = $this->database->prepare("UPDATE `users` SET `users`.`online` = UNIX_TIMESTAMP(NOW()) WHERE `users`.`idu` = :user");
    $this->stmt->bindParam(':user', $data['user1'], PDO::PARAM_INT);
    $this->stmt->execute();

    for ($i=0, $arraySize = count($result); $i < $arraySize; $i++) {
      $result[$i]['message'] = html_entity_decode($result[$i]['message']);
      $result[$i]['avatar'] = $assets_url . $result[$i]['avatar'] . '&t=a&w=200&h=200';
      $result[$i]['message_thumbnail'] = $assets_url . $result[$i]['message_value'] . '&t=m&w=300&h=300';
      $result[$i]['message_value'] = $assets_url . $result[$i]['message_value'] . '&t=m&zc=3';
    }

    $resp = [
      'status' => 'success',
      'message' => array_values($result)
    ];
    return $response->withJson($resp, 200);
  }

  public function conversationPost(Request $request,  Response $response, $args = []) {
    global $CONF;
    $data = $request->getParsedBody();

    if( empty($data['from']) || empty($data['to']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("SELECT * FROM `blocked` WHERE (`uid` = :from AND `by` = :to) OR (`uid` = :to AND `by` = :from)");
    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->execute();

    $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
    if( $result['by'] == $data['from'] ){
      $resp = [
        'status' => 'error',
        'message' => 'You blocked this user.'
      ];
      return $response->withJson($resp, 400);
    }

    if( $result['by'] == $data['from'] ){
      $resp = [
        'status' => 'error',
        'message' => 'This user blocked you.'
      ];
      return $response->withJson($resp, 400);
    }
    
    $this->stmt = $this->database->prepare("INSERT INTO `chat`(`from`, `to`, `message`, `type`, `value`, `read`) VALUES (:from, :to, :message, :type, :value, 0)");
    if( $data['type'] == 'picture' ){
      $finalName = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.'jpg';
      
      // Define the type for picture
      $type = 'picture';
      $value = $finalName;
      $imageData = explode( ',', $data['value'] );
      $decoded=base64_decode($imageData[1]);
      file_put_contents(__DIR__ . '/../../uploads/media/'.$finalName,$decoded);
      $data['message'] = '';
    }

    $type = $type ? $type: '';
    $finalName = $finalName ? $finalName: '';

    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->bindParam(':message', $data['message'], PDO::PARAM_STR);
    $this->stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $this->stmt->bindParam(':value', $finalName, PDO::PARAM_STR);
    $this->stmt->execute();

    
    $this->stmt = $this->database->prepare("SELECT usr.`idu` as 'user_id', cht.`id` as 'id', CONCAT(usr.`first_name`, ' ', usr.`last_name`) as `name`, usr.`username` as 'username', usr.`image` as 'avatar', cht.`message` as 'message', FROM_UNIXTIME(UNIX_TIMESTAMP(cht.`time`) - :timezoneOffset) as 'time', cht.`type` as `message_type`, cht.`value` as `message_value` FROM `chat`cht  LEFT JOIN `users` usr ON usr.`idu` = cht.`from` WHERE cht.`from` = :from AND cht.`to` = :to ORDER BY cht.`id` DESC LIMIT 1");

    $assets_url = $CONF['url'].'/thumb.php?src=';
    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->bindParam(':timezoneOffset', date('Z'), PDO::PARAM_INT);
    $this->stmt->execute();

    $result = $this->stmt->fetch(PDO::FETCH_ASSOC);

    $result['avatar'] = $assets_url . $result['avatar'] . '&t=a&w=200&h=200';
    $result['message_thumbnail'] = $assets_url . $result['message_value'] . '&t=m&w=300&h=300';
    $result['message_value'] = $assets_url . $result['message_value'] . '&t=m&zc=3';

    $this->stmt = $this->database->prepare("UPDATE `users` SET `online` = UNIX_TIMESTAMP(NOW()) WHERE `idu` = :user");
    $this->stmt->bindParam(':user', $data['from'], PDO::PARAM_INT);
    $this->stmt->execute();

    $this->stmt = $this->database->prepare("SELECT * FROM `conversations` WHERE `from` = :from AND `to` = :to");
    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->execute();
    $conversation = $this->stmt->fetch(PDO::FETCH_ASSOC);
    
    if( count($conversation) > 0 ){
      $this->stmt = $this->database->prepare("UPDATE `conversations` SET `read` = 0, `cid` = :id WHERE `from` = :from AND `to` = :to");
      $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
      $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
      $this->stmt->bindParam(':id', $result['id'], PDO::PARAM_INT);
      $this->stmt->execute();
    } else {
      $this->stmt = $this->database->prepare("INSERT INTO `conversations`(`from`, `to`, `read`, `cid`) VALUES (:from, :to, 0, :cid)");
      $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
      $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
      $this->stmt->bindParam(':cid', $result['id'], PDO::PARAM_INT);
      $this->stmt->execute();
    }

    // Send push notification
    $message = [
      'from' => $data['from'],
      'destination' => $data['to'],
      'message' => $result['message'],
      'image' => $result['message_value'],
    ];
    $notification = $this->sendMessage($message);
    $result['push_sent'] = $notification;

    $result['message'] = html_entity_decode($result['message']);

    // Display post result
    $resp = [
      'status' => 'success',
      'message' => $result,
      'sent' => $data,
    ];
    return $response->withJson($resp, 200);
  }

  private function sendMessage($message) {
    global $CONF, $LNG;

    $content = [
      "en" => (( $message['message'] && strlen($message['message']) > 0 )? $message['message']: $LNG['image'])
    ];
    $fields = array(
      'app_id' => $CONF['onesignal']['appid'],
      'data' => [
        'from' => $message['from']
      ],
      'headings' => [
        'en' => $LNG['newmessage'],
      ],
      'contents' => $content,
      'filters' => [
        [
          "field" => "tag", 
          "key" => "userId", 
          "relation" => "=", 
          "value" => $message['destination']
        ],
      ],
      'big_picture' => $message['image'],
      'ios_attachments' => [
        'id' => $message['image']
      ],
    );
    
    $fields = json_encode($fields);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic '.$CONF['onesignal']['restkey']
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

  public function markAsRead(Request $request,  Response $response, $args = []) {
    global $CONF;
    $data = $request->getParsedBody();

    if( empty($data['from']) || empty($data['to']) ){
      $resp = [
        'status' => 'error',
        'message' => 'User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("UPDATE `chat` SET `read` = '1' WHERE `from` = :from AND `to` = :to AND `read` = '0'");
    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->execute();

    $this->stmt = $this->database->prepare("UPDATE `conversations` SET `read` = '1' WHERE `from` = :from AND `to` = :to AND `read` = '0'");
    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->execute();

    $this->stmt = $this->database->prepare("UPDATE `users` SET `online` = UNIX_TIMESTAMP(NOW()) WHERE `idu` = :user");
    $this->stmt->bindParam(':user', $data['to'], PDO::PARAM_INT);
    $this->stmt->execute();

    $resp = [
      'status' => 'success',
    ];
    return $response->withJson($resp, 200);
  }

  public function deleteMessage(Request $request,  Response $response, $args = []) {
    global $CONF;
    $data = $request->getParsedBody();

    if( empty($data['message']) || empty($data['from']) || empty($data['to']) ){
      $resp = [
        'status' => 'error',
        'message' => 'Message ID or User ID not valid.'
      ];
      return $response->withJson($resp, 400);
    }

    $this->stmt = $this->database->prepare("DELETE * FROM `chat` WHERE `id` = :id AND `from` = :from AND `to` = :to");
    $this->stmt->bindParam(':from', $data['from'], PDO::PARAM_INT);
    $this->stmt->bindParam(':to', $data['to'], PDO::PARAM_INT);
    $this->stmt->bindParam(':id', $data['message'], PDO::PARAM_INT);
    $this->stmt->execute();

    $this->stmt = $this->database->prepare("UPDATE `users` SET `online` = UNIX_TIMESTAMP(NOW()) WHERE `idu` = :user");
    $this->stmt->bindParam(':user', $data['from'], PDO::PARAM_INT);
    $this->stmt->execute();
  }

}