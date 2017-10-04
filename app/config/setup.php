<?php

try
{
  $pdo = new PDO("mysql:host=localhost", $DB_USER, $DB_PASSWORD);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $pdo->query("CREATE DATABASE IF NOT EXISTS $db_name");
  $pdo->query("use $db_name");

  $_SESSION['db_status'] ='1';

} catch (PDOException $e) {
  $_SESSION['db_status'] ='0';
    print "Error!: DATABASE db-> " . $e->getMessage() . " FAILED TO CREATE<br/>";
    die();
}

try {
  $pdo->beginTransaction();
  $pdo->exec("CREATE TABLE IF NOT EXISTS members
  (
    id_user INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    login VARCHAR(64) NOT NULL,
    nom VARCHAR(64) NOT NULL,
    prenom VARCHAR(64) NOT NULL,
    email VARCHAR(64) NOT NULL,
    password VARCHAR(128) NOT NULL,
    secret_answer VARCHAR(128) NOT NULL,
    activated ENUM('yes','no') DEFAULT 'no' NOT NULL,
    admin ENUM('yes','no') DEFAULT 'no' NOT NULL
  )");

  $_SESSION['db_status'] ='2';
  $pdo->commit();
} catch (PDOException $e) {
  $pdo->rollBack();
  $_SESSION['db_status'] ='1';
  print "Error!: DATABASE members -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
  die();
}


?>
