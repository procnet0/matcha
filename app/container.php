<?php

$container = $app->getContainer();

$container['view'] = function ($container) {
  $dir = dirname(__DIR__);
    $view = new \Slim\Views\Twig($dir.'/app/views', [
        'cache' => false //$dir . 'tmp/cache'
    ]);

    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['debug'] = function() {
  return true;
};

$container['pdo'] = function() {

  //require('app/config/database.php');
  try {
  $pdo = new PDO("mysql:host=". DBHOST.";dbname=".DBNM, DBUSR, DBPWD);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    print "Error!: DATABASE members -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
  die();
    }
  return $pdo;
};

$container['mailer'] = function($container) {
if($container->debug)
{
  $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587)
    ->setEncryption('tls')
    ->setUsername('vincent.balart@gmail.com')
    ->setPassword('jupvkqytmtlqsaks');
} else {
  $transport = Swift_MailTransport::newInstance();
}
  $mailer = Swift_Mailer::newInstance($transport);
  return $mailer;
};


?>
