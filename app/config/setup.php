<?php

try
{
  $pdo = new PDO("mysql:host=localhost", $DB_USER, $DB_PASSWORD);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $dbname = "`".str_replace("`","``",$dbname)."`";
  $pdo->query("CREATE DATABASE IF NOT EXISTS $dbname");
  $pdo->query("use $dbname");

  $db_status ='1';

} catch (PDOException $e) {
    print "Error!: DATABASE -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
    die();
}

try {
  $pdo->beginTransaction();
  $pdo->exec("CREATE TABLE IF NOT EXISTS members
  (
    id_user INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    login VARCHAR(64) NOT NULL,
    email VARCHAR(64) NOT NULL,
    password VARCHAR(128) NOT NULL,
    secret_answer VARCHAR(128) NOT NULL,
    activated ENUM('yes','no') DEFAULT 'no' NOT NULL,
    admin ENUM('yes','no') DEFAULT 'no' NOT NULL
  )");
  $pdo->commit();

} catch (PDOException $e) {
  $pdo->rollBack();
  print "Error!: DATABASE -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
  die();
}


?>
