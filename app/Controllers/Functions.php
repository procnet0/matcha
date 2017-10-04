<?php

function checkForAccount($name, $password, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT * FROM members WHERE login = ?");
    $sql->bindParam(1, $name , PDO::PARAM_STR);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();

  } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE checkForAccount-> " . $e->getMessage() . " FAILED TO CREATE<br/>";
    die();
  }

  $rpwd =  hash('whirlpool', $password);

  if(!empty($result['login']) && !empty($result['password']) && $rpwd == $result['password'])
  {
    return ($result = array('name' => true, 'password' => true ));
  }
  else if(!empty($result['login']) && !empty($result['password']) && $rpwd != $result['password'])
  {
    return ($result = array('name' => true, 'password' => false ));
  }
  else if(empty($result['login']))
  {
    return ($result = array('name' => false, 'password' => false ));
  }
};


function createNewAccount($params, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("INSERT INTO members VALUES
      (NULL, ?, ?, ?, ?, ?, ?, 'no', 'no')");
    $sql->bindParam(1, $params['pseudo'], PDO::PARAM_STR);
    $sql->bindParam(2, $params['nom'], PDO::PARAM_STR);
    $sql->bindParam(3, $params['prenom'], PDO::PARAM_STR);
    $sql->bindParam(4, $params['email'], PDO::PARAM_STR);
    $sql->bindParam(5, hash('whirlpool',$params['password']), PDO::PARAM_STR);
    $sql->bindParam(6, $params['answer'], PDO::PARAM_STR);
    $sql->execute();
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    print "</br>Error!: DATABASE create Account-> " . $e->getMessage() . " FAILED TO CREATE<br/>";
    die();
  }

  return true;
};
 ?>
