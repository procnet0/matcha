<?php
namespace App\Controllers;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PDO;


class Validator {

  public $pdo;

  public function __construct()
  {
    $this->pdo = new PDO("mysql:host=". DBHOST.";dbname=".DBNM, DBUSR, DBPWD);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function isempty($content) {
      return empty($content);
  }

  public function isemail($content) {
    $result = filter_var($content, FILTER_VALIDATE_EMAIL);
    if($result != NULL && $result != false) {
      return true;
    }
    else {
      return false;
    }
  }

  public function ispseudo($content) {
    try {
      $sql = $this->pdo->prepare('SELECT login FROM members WHERE login = ?');
      $sql->bindParam(1, $content, PDO::PARAM_STR);
      $sql->execute();
      $result = $sql->fetch(PDO::FETCH_ASSOC);
    } catch  (PDOException $e) {
      print "Error!: Validator checkForAccount-> " . $e->getMessage() . " FAILED TO VALIDATE<br/>";
      die();
    }
    return $result;
  }

  public function isgender($content) {

    if($content === 'male' || $content ==='female' || $content === 'other') {
      return true;
    } else {
    return false;
    }
  }

  public function password(string $content) {
    if(strlen($content) >= PASSWORD_LENGHT)
    {
      return true;
    }
    else {
      return false;
    }
  }

  public function validate($name, $value , $revert = 'no') {
    if($name == 'email' && !$this->isempty($value) && $this->isemail($value)) {
      return true;
    }
    if($name == 'pseudo' && !$this->isempty($value) && ( ($revert === 'new' )? $this->ispseudo($value) : !$this->ispseudo($value))) {
      return true;
    }
    if($name == 'content' && !$this->isempty($value)){
      return true;
    }
    if($name == 'gender' && !$this->isempty($value) && $this->isgender($value)) {
      return true;
    }
    if($name == 'password' && !$this->isempty($value) && $this->password($value)){
      return true;
    }

    return false;
  }
}
?>
