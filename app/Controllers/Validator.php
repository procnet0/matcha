<?php
namespace App\Controllers;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PDO;


class Validator {

  protected $pdo;
  protected $return;

  public function __construct()
  {
    $this->pdo = new PDO("mysql:host=". DBHOST.";dbname=".DBNM, DBUSR, DBPWD);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function isempty($content) {
      return empty($content);
  }

  public function returner($type = null){
    if($type) {
    return $this->return['error'];
    }
    else {
      return $this->return;
    }
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

  public function isbirthday($content) {
    return strtotime($content);
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

  public function isalphanum($content) {
    if(preg_match('/[^a-z_\-0-9]/i', $content))
    {
      return false;
    }
    else {
      return true;
    }
  }

  public function issecured($content) {
    if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$%^&]).*$/', $content)) {
      return true;
    }
    else {
      return false;
    }
  }

  public function validate($name, $value , $revert = 'no') {
    $this->return = ['status' => false , 'error'=> 'none'];


    if(!$this->isempty($value))
    {
      if($name == 'email') {
        if($this->isemail($value)) {
          $this->return['status'] = true;
        }
        else {
          $this->return['error'] = 'Email is not valid.';
            $this->return['status'] = false;
        }
      }
      if($name == 'pseudo' ) {
        if(($revert === 'new') ? $this->ispseudo($value) : !$this->ispseudo($value)) {
          if($this->isalphanum($value) == true) {
              $this->return['status'] = true;
          }
          else {
              $this->return['status'] = false;
              $this->return['error'] = 'Invalid Character, use only letters, number or - and _ .';
          }
        }
        else {
          $this->return['status'] = false;
          $this->return['error'] = 'Pseudo already used';
        }
      }
      if($name == 'content') {
          $this->return['status'] = true;
      }
      if($name == 'gender') {
        if($this->isgender($value)) {
          $this->return['status'] = true;
        }
        else {
          $this->return['status'] = false;
          $this->return['error'] = 'Gender unknown.';
        }
      }
      if($name == 'password') {
        if($this->password($value)) {
          $this->return['status'] = true;
          if($this->issecured($value)) {
            $this->return['status'] = true;
          }
          else {
            $this->return['status'] = false;
            $this->return['error'] = 'Use at least one uppercase+Lowercase letters, one number and one special character.';
          }
        }
        else {
          $this->return['status'] = false;
          $this->return['error'] = 'Min length is '. PASSWORD_LENGHT.'.';
        }
      }
      if($name == 'birthday') {
        if($this->isbirthday($value)) {
          $this->return['status'] = true;

        }
        else {
          $this->return['status'] = false;
          $this->return['error'] = 'Wrong Format for your birthday';
        }
      }
    }
    else {
      $this->return['error'] = 'empty value';
    }
    return $this->return['status'];
  }
}
?>
