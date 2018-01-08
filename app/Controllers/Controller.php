<?php
namespace App\Controllers;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PDO;

class Controller {
  private $container;

  public function __construct($container) {
      $this->container = $container;
      if(!empty($_SESSION['id'])) {
      $pdo = $this->pdo;
      $sql = $pdo->prepare("SELECT id_user FROM members WHERE login=?");
      $sql->bindParam(1, $_SESSION['loggued_as'], PDO::PARAM_STR);
      $sql->execute();
      $res = $sql->fetch();
      if($res)
      {
        $pdo->exec("UPDATE ping SET timeof =".time()." WHERE id_user =".$_SESSION['id']);
      }
      else {
        $_SESSION['loggued_as'] = '';
        $_SESSION['id'] = '';
      }
      if (!empty($_SESSION['loggued_as']) && (empty($_SERVER['REDIRECT_URL']) || $_SERVER['REDIRECT_URL'] != "/matcha/notif"))
      {
        $ret = $pdo->query("SELECT MAX(id_notif) FROM `notification`");
        $tab = $ret->fetch();
        $_SESSION['max_id'] = $tab[0];
      }
    }
  }

  public function render(Response $response, $file , $params = []) {
    $this->container->view->render($response, $file, $params);
  }

  public function __get($name) {

    return $this->container->get($name);
  }

  public function flash($message, $type = 'success') {
    if (!isset($_SESSION['flash'])) {
      $_SESSION['flash'] = [];
    }
    return $_SESSION['flash'][$type] = $message;
  }

  public function redirect(Response $response, $name) {
    return $response->withStatus(302)->withHeader('Location' , $this->router->pathFor($name));
  }
}
 ?>
