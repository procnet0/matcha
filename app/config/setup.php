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
  $pdo->exec("CREATE TABLE IF NOT EXISTS members");
  $pdo->commit();

} catch (PDOException $e) {
  $pdo->rollBack();
  print "Error!: DATABASE -> " . $e->getMessage() . " FAILED TO CREATE<br/>";
  die();
}


?>
