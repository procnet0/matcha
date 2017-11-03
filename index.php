<?php
use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); ini_set('display_errors','On');

session_start();

require 'vendor/autoload.php';

include_once('app/config/database.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/config/setup.php');

if(!is_dir('app/imgprofil/'))
{
  mkdir('app/imgprofil/', '0744');
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
$app->get("/lookat/{name}", \App\Controllers\PagesController::class . ':lookat')->setName('lookat');
$app->post("/profil", \App\Controllers\PagesController::class . ':postAccount');
$app->get("/recherche", \App\Controllers\PagesController::class . ':getSearch')->setName('recherche');
$app->post("/recherche", \App\Controllers\PagesController::class . ':postSearch');

$app->get("/signUp", \App\Controllers\PagesController::class . ':getMember')->setName('signUp');
$app->post("/signUp", \App\Controllers\PagesController::class . ':postMember');
$app->get("/logout", \App\Controllers\PagesController::class . ':logout')->setName('logout');
$app->post("/UpdateProfil", \App\Controllers\PagesController::class . ':UpdateProfil')->setName('UpdateProfil');
$app->post("/setAsProfil", \App\Controllers\PagesController::class . ':setAsProfil');
$app->post("/updateAccountPict", \App\Controllers\PagesController::class . ':updateAccountPict');
$app->post("/getTagInfo", \App\Controllers\PagesController::class . ':getTagInfo');
$app->post("/updateTagInfo", \App\Controllers\PagesController::class . ':updateTagInfo');
$app->post("/updatePosition", \App\Controllers\PagesController::class . ':updatePosition');
$app->post("/lookat/reportUser", \App\Controllers\PagesController::class . ':reportUser');
$app->run();
?>
