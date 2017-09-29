<?php
namespace App\Controllers;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller {
  private $container;

  public function __construct($container) {
      $this->container = $container;
  }

  public function render(Response $reponse, $file) {
    $this->container->view->render($reponse, $file);
  }

  public function __get($name) {
    return $this->container->get($name);
  }

}
 ?>
