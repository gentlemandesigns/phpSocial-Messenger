<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * This is the routes file. All API routes are defined here.
 */
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function () {
  return 'phpSocial Messenger API';
});

// Login to application
$app->post('/login', \User::class.':login' );

/**
 * Requires LOGIN via Middleware
 */
$app->group('', function () {
  // All conversations
  $this->post('/messages', \Messages::class.':conversationsList' );
  $this->post('/messages/pagination/{take}/{offset}', \Messages::class.':conversationsList' );

  // Messages from a specific chat
  $this->post('/messages/chat', \Messages::class.':conversationAllChat' );
  $this->delete('/messages/chat', \Messages::class.':deleteMessage' );
  $this->post('/messages/chat/pagination/{take}/{offset}', \Messages::class.':conversationAllChat' );
  $this->post('/messages/chat/unread', \Messages::class.':conversationLoadUnread' );
  $this->post('/messages/chat/markread', \Messages::class.':markAsRead' );

  // Post message
  $this->post('/messages/chat/post', \Messages::class.':conversationPost' );

  // User Friends
  $this->post('/user/friends', \User::class.':friendsList' );
  $this->post('/user/info/{id}', \User::class.':userInfo' );
  $this->post('/user/block/{id}', \User::class.':blockUser' );
  $this->post('/user/friends/pagination/{take}/{offset}', \User::class.':friendsList' );
})->add( \Authentificated::class );

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
  $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
  return $handler($req, $res);
});