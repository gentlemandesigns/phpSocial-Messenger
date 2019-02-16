<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Database {
  protected $database = null;
  protected $stmt = null;
  protected $settings = null;

  function __construct(){
    global $CONF;

    $this->database = new PDO('mysql:host=' . $CONF['host'] . ';dbname=' . $CONF['name'],  $CONF['user'], $CONF['pass']);
    $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $this->settings = $this->database->query("SELECT * from `settings`", PDO::FETCH_ASSOC)->fetch();
  }
}
