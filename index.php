<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/config/database.php');

if (!isset($db_status) || $db_status != '2')
{
  include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/config/setup.php');
}



class Context {
    public function __invoke(Request $request, Response $response, $next)
    {
      $response->write('<html>
      <head>
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <link type="text/css" rel="stylesheet" href="/matcha/app/css/materialize.css"  media="screen,projection"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      </head>

      <body>
      <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
      <script type="text/javascript" src="/matcha/app/js/materialize.js"></script>

      <header class="page-header internal-border" id="header">
      <div class="col s12 m12 l12 header-top internal-border "><h1 class="center-align center-block" >Welcome to Matcha</h1></div>
      <div class="container section cyan">
        <div class="row">
          <div class="col s8 m8 l8"><h3 class="center-align" > header <h3></div>
          <div class="col s4 m4 l4"><h3 class="center-align" > log menu<h3></div>
          </div>
        </div>
      </div>
     </header>
     <main>
      ');
      $response = $next($request, $response);
      $response->write('
      </main>
      <footer class="page-footer">
          <div class="container">
            <div class="row">
              <div class="col l6 s12">
                <h5 class="white-text">Footer Content</h5>
                <p class="grey-text text-lighten-4">You can use rows and columns here to organize your footer content.</p>
              </div>
              <div class="col l4 offset-l2 s12">
                <h5 class="white-text">Links</h5>
                <ul>
                  <li><a class="grey-text text-lighten-3" href="#!">Link 1</a></li>
                  <li><a class="grey-text text-lighten-3" href="#!">Link 2</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="footer-copyright">
            <div class="container">
            © 2014 Copyright Text
            <a class="grey-text text-lighten-4 right" href="#!">More Links</a>
            </div>
          </div>
        </footer>
  </body></html>');
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
    return $response->write("<div>
     hello ". $args['nom'] . "</div>");
});


$app->run();
?>
