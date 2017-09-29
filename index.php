<?php
use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); ini_set('display_errors','On');

require 'vendor/autoload.php';

include_once('app/config/database.php');

if (!isset($db_status) || $db_status != '2')
{
  include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/config/setup.php');
}

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true
  ]
]);



require('app/container.php');

$container['pdo'] = function() {

  require('app/config/database.php');
  try {
  $pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name, $DB_USER,$DB_PASSWORD);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    print "Error!: DATABASE members -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
  die();
    }
  return $pdo;
};

$container = $app->getContainer();

$app->get("/", \App\Controllers\PagesController::class . ':home')->setName('home');
$app->get("/profil", \App\Controllers\PagesController::class . ':getAccount')->setName('profil');
$app->post("/profil", \App\Controllers\PagesController::class . ':postAccount');
$app->get("/signUp", \App\Controllers\PagesController::class . ':createAccount')->setName('Sign_Up');

$app->run();
?>
