<?php
require $_SERVER['DOCUMENT_ROOT'] . "/api/core/Helpers.php";

// autoloader for requiring classes on initialize or call
spl_autoload_register(function($class) {
  require base_path($class . '.php');
});

$router = new \API\Core\Router;
$routes = require("../routes.php");

$router->HandleRequest($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], $_REQUEST);