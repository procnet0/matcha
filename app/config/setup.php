<?php
if (!isset($_SESSION['db_status']) || $_SESSION['db_status'] != '1')
{
try {
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
}
if (!isset($_SESSION['db_status']) || $_SESSION['db_status'] != '2')
{
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
    birthday DATETIME NOT NULL,
    sexe ENUM('male','female','other') DEFAULT 'male' NOT NULL,
    oriented ENUM('hetero', 'homo', 'bi') DEFAULT 'bi' NOT NULL,
    bio TEXT,
    profil_pict VARCHAR(255)  DEFAULT '#' NOT NULL,
    activated ENUM('yes','no') DEFAULT 'no' NOT NULL,
    admin ENUM('yes','no') DEFAULT 'no' NOT NULL
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS tags
  (
    id_tag INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name_tag varchar(64) NOT NULL
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS tags_members
  (
    id_assoc INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_tag INT NOT NULL,
    id_members INT NOT NULL
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS pictures
  (
    id_pict INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_user INT NOT NULL,
    pict1 VARCHAR(255),
    pict2 VARCHAR(255),
    pict3 VARCHAR(255),
    pict4 VARCHAR(255),
    pict5 VARCHAR(255)
  )");

  $pdo->exec("INSERT IGNORE INTO tags VALUES
    (1,'Geek'),
    (2,'Food'),
    (3,'Sport'),
    (4,'Sleep'),
    (5,'Drink')
    ");

  $pdo->exec("CREATE TABLE IF NOT EXISTS geoloc
    (
      id_geoloc INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      id_user INT NOT NULL,
      latitude FLOAT NOT NULL,
      longitude FLOAT NOT NULL,
      timeof INT NOT NULL,
      type ENUM('auto','user') DEFAULT 'auto' NOT NULL
    )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS likes
    (
      id_like INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      id_from INT NOT NULL,
      id_to INT NOT NULL,
      timeof INT NOT NULL
    )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS visite
    (
      id_visite INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      id_from INT NOT NULL,
      id_to INT NOT NULL,
      timeof INT NOT NULL
    )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS report
    (
      id_report INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      id_from INT NOT NULL,
      id_to INT NOT NULL,
      timeof INT NOT NULL,
      subject ENUM('Message indesirable','Fake profil','Photo non conforme','Other') DEFAULT 'Other' NOT NULL,
      content TEXT
    )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS logs
    (
      id_logs INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      id_user INT NOT NULL,
      logip VARCHAR(40) NOT NULL,
      timeof INT NOT NULL
    )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS ping
  (
    id_ping INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_user INT NOT NULL,
    timeof INT NOT NULL
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS blocked
  (
    id_block INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_from INT NOT NULL,
    id_to INT NOT NULL,
    timeof INT NOT NULL
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
    id_msg INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_from INT NOT NULL,
    id_to INT NOT NULL,
    timeof INT NOT NULL,
    content TEXT
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS notification (
    id_notif INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_user INT NOT NULL,
    id_item INT NOT NULL,
    id_from INT NOT NULL,
    type TINYINT NOT NULL,
    timeof INT NOT NULL,
    new TINYINT DEFAULT '1' NOT NULL
  )");

  $_SESSION['db_status'] ='2';
  $pdo->commit();
  } catch (PDOException $e) {
  $pdo->rollBack();
  $_SESSION['db_status'] ='0';
  print "Error!: DATABASE ALL -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
  die();
}
}

?>
