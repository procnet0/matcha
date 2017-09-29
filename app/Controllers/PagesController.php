<?php
namespace App\Controllers;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PagesController extends Controller{

  public function home(Request $request, Response $response) {
    $this->render($response, 'pages/home.twig');
  }

  public function getAccount(Request $request, Response $response) {
    if(!empty($_SESSION['loggued_as']))
    {
      $this->render($response, 'pages/account.twig');
    }
    else {
      $this->render($response, 'pages/home.twig');
    }
  }

  public function postAccount(Request $request, Response $response) {

    $pdo = $this->pdo;
    include_once ('Functions.php');
    $param = $request->getParams();

    if(!empty($param['name']) && !empty($param['password']))
    {
      $result = checkForAccount($param['name'], $param['password'], $pdo);

      if($result['name'] == True && $result['password'] == True )
      {
        $_SESSION['loggued_as'] = $param['name'];
        $_SESSION['Alert'] = "Connexion Succeeded";
        $this->render($response, 'pages/account.twig');
      }
      else if($result['name'] == True && $result['password'] == False)
      {
        $_SESSION['loggued_as'] = "";
        $_SESSION['Alert'] = "Wrong password";
        $this->render($response, 'pages/home.twig');
      }
      else if($result['name'] != True)
      {
        $_SESSION['loggued_as'] = "";
        $_SESSION['Alert'] = "Login not Found, Please Sign up";
        $this->render($response, 'pages/signUp.twig');
      }

    }
  }

  public function createAccount(Request $request, Response $response , $info) {
    $this->render($response, 'pages/signUp.twig');
  }
}


 ?>
