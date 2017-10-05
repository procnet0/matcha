<?php
use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); ini_set('display_errors','On');

session_start();

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

$container = $app->getContainer();

$app->add(new \App\Middlewares\FlashMiddleware($container->view->getEnvironment()));

$app->get("/", \App\Controllers\PagesController::class . ':home')->setName('home');
$app->get("/contact", \App\Controllers\PagesController::class . ':getContact')->setName('contact');
$app->post("/contact", \App\Controllers\PagesController::class . ':postContact');
$app->get("/profil", \App\Controllers\PagesController::class . ':getAccount')->setName('profil');
$app->post("/profil", \App\Controllers\PagesController::class . ':postAccount');
$app->get("/signUp", \App\Controllers\PagesController::class . ':getMember')->setName('signUp');
$app->post("/signUp", \App\Controllers\PagesController::class . ':postMember');
$app->get("/logout", \App\Controllers\PagesController::class . ':logout')->setName('logout');
$app->post("/UpdateProfil", \App\Controllers\PagesController::class . ':UpdateProfil')->setName('UpdateProfil');

$app->run();
?>
