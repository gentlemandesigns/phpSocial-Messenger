<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

use \Database as Database;

class Authentificated extends Database {
    
    public function __invoke(Request $request, Response $response, $next){
        global $CONF;
        $data = $request->getParsedBody();
        $header = $request->getHeader('Authorization');

        // Token: "Bearer JWT"
        $token = str_replace('Bearer ', '', $header[0]);

        $decoded = null;
        try {
            $decoded = JWT::decode($token, $CONF['secret'], array('HS256'));
            if( !$decoded->data ){
                $resp = [
                  'status' => 'unauthorized',
                  'message' => 'Invalid token.'
                ];
                return $response->withJson($resp, 401);
            }
        } catch(Exception $e){
            $resp = [
              'status' => 'unauthorized',
              'message' => 'Invalid token.'
            ];
            return $response->withJson($resp, 401);
        }

        $this->stmt = $this->database->prepare("SELECT `idu` FROM `users` WHERE `username` = :username AND `email` = :email");
        $this->stmt->bindParam(":username", $decoded->data->username);
        $this->stmt->bindParam(":email", $decoded->data->email);
        $this->stmt->execute();

        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if( $result['idu'] < 1 ){
            $resp = [
              'status' => 'unauthorized',
              'message' => 'Invalid token.'
            ];
            return $response->withJson($resp, 401);
        }

        $response = $next($request, $response);
        return $response;
    }
}