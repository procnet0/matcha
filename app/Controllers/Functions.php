<?php

function checkForAccount($name, $password, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT login,password FROM members WHERE login = ?");
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


  $params['password'] =  hash('whirlpool',$params['password']);
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("INSERT INTO members (login,nom,prenom,email,password,secret_answer) VALUES
      (?, ?, ?, ?, ?, ?)");
    $sql->bindParam(1, $params['pseudo'], PDO::PARAM_STR);
    $sql->bindParam(2, $params['nom'], PDO::PARAM_STR);
    $sql->bindParam(3, $params['prenom'], PDO::PARAM_STR);
    $sql->bindParam(4, $params['email'], PDO::PARAM_STR);
    $sql->bindParam(5, $params['password'], PDO::PARAM_STR);
    $sql->bindParam(6, $params['answer'], PDO::PARAM_STR);
    $sql->execute();
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    return(array( ['error' => "</br>Error!: DATABASE create Account-> " . $e->getMessage() . " FAILED TO CREATE<br/>"]));
  }

  return true;
};

function getAccountInfo($name, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT * FROM members WHERE login = ?");
    $sql->bindParam(1, $name , PDO::PARAM_STR);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();

  } catch (PDOException $e) {
    $pdo->rollBack();
    return "Error!: DATABASE getAccountInfo-> " . $e->getMessage() . " FAILED TO PULL<br/>";
  }

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT * FROM pictures WHERE id_user = ?");
    $sql->bindParam(1, $result['id_user'] , PDO::PARAM_STR);
    $sql->execute();
    $tmp = $sql->fetch(PDO::FETCH_ASSOC);
    if(!empty($tmp))
    {
      $result = array_merge($result, $tmp);
    }
    $pdo->commit();

  } catch (PDOException $e) {
    $pdo->rollBack();
    return "Error!: DATABASE getAccountInfo-> " . $e->getMessage() . " FAILED TO PULL<br/>";
  }
  return $result;
}

function updateAccountInfo($login, $post,$pdo) {
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("UPDATE members SET nom = ? , prenom = ? , email = ? ,sexe= ? , oriented = ?, bio = ? WHERE login = ? ");
    $sql->bindParam(1, $post['nom'] , PDO::PARAM_STR);
    $sql->bindParam(2, $post['prenom'] , PDO::PARAM_STR);
    $sql->bindParam(3, $post['email'] , PDO::PARAM_STR);
    $sql->bindParam(4, $post['gender'] , PDO::PARAM_STR);
    $sql->bindParam(5, $post['oriented'] , PDO::PARAM_STR);
    $sql->bindParam(6, $post['bio'] , PDO::PARAM_STR);
    $sql->bindParam(7, $login , PDO::PARAM_STR);
    $sql->execute();
    $pdo->commit();

  } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE updateAccountInfo-> " . $e->getMessage() . " FAILED TO UPDATE<br/>";
    die();
  }
  return;
}

function updatePict($data, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT members.id_user,profil_pict, login FROM pictures INNER JOIN members ON members.id_user = pictures.id_user WHERE  ? IN(pict1,pict2,pict3,pict4,pict5)");
    $sql->bindParam(1, $data['profil_pict'] , PDO::PARAM_STR);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE Profilpict Update-> " . $e->getMessage() . " FAILED TO COMBINE<br/>";
    die();
  }
  if ($result['profil_pict'] == $data['profil_pict'])
  {
    return false;
  }

  if($result && $result['login'] == $_SESSION['loggued_as'])
  {
    try {
      $pdo->beginTransaction();
      $sql = $pdo->prepare("UPDATE members SET profil_pict = ?  WHERE login = ? ");
      $sql->bindParam(1, $data['profil_pict'] , PDO::PARAM_STR);
      $sql->bindParam(2, $result['login'] , PDO::PARAM_STR);
      $sql->execute();
      $pdo->commit();
    } catch (PDOException $e) {
      $pdo->rollBack();
      print "Error!: DATABASE Profilpict Update-> " . $e->getMessage() . " FAILED TO UPDATE<br/>";
      die();
    }
  }
  return true;
}

function AddOrChangePicturePhp($data, $pdo) {


  return (print_r($data));
}
 ?>
