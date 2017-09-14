<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/config/database.php');

if (!isset($db_status) || $db_status != '1')
{
  include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/config/setup.php');
}

class Context {
    public function __invoke(Request $request, Response $response, $next)
    {
      $response->write('<div>
      <h1>Bienvenue</h1>
      <div id="header"> header </div>
      </div>');
      $response = $next($request, $response);
      $response->write('<div id="footer">
      footer
      </div>');
      return $response;
    }
}

$app = new \Slim\App();

$container = $app->getContainer();

$container['pdo'] = function() {
  $pdo = new PDO($DB_DSN, $DB_USER,$DB_PASSWORD);
  $pdo->setAttribute(PDO::ATTR_ERRMODR, PDO::ERRMODE_EXCEPTION);
  return $pdo;
};

$app->add(new Context());
$app->get("/pede/{nom}", function (Request $request, Response $response, $args) {
    return $response->write(" hello ". $args['nom']);
});


$app->run();
?>
