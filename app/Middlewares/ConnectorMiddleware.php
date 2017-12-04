<?php
namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

class ConnectorMiddleware {

  private $twig;

  public function __construct(\Twig_Environment $twig)
  {
    $this->twig = $twig;
  }

  public function __invoke(Request $request, Response $response, $next)
  {
    $this->twig->addGlobal('Connector', !empty($_SESSION['loggued_as']) ? 1 : 0 );
    return $next($request, $response);
  }
}
 ?>
