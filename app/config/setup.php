<?php
//$_SESSION['db_status'] = 0;
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

  $pdo->exec("CREATE TABLE IF NOT EXISTS matchs (
    id_match INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_1 INT NOT NULL,
    id_2 INT NOT NULL,
    timeof INT NOT NULL,
    active TINYINT NOT NULL
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

    $sql = $pdo->exec("DROP FUNCTION IF EXISTS `GetScore`");
    $sql = $pdo->exec('CREATE DEFINER=`root`@`localhost` FUNCTION `GetScore`(`id1` INT) RETURNS INT(11) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN
    DECLARE tags INT;
    DECLARE likes INT;
    DECLARE visites INT;
    DECLARE pictures INT;
    DECLARE tot INT;
    DECLARE ptags INT;
    DECLARE plikes INT;
    DECLARE pvis INT;
    DECLARE ppict INT;

    SET ptags = 10;
    SET plikes = 50;
    SET pvis = 25;
    SET ppict = 15;
    SET tags = IFNULL((SELECT COUNT(*) FROM `tags_members` WHERE id_members = id1),0);
    SET likes = IFNULL((SELECT COUNT(*) FROM `likes` WHERE id_to = id1),0);
    SET visites = IFNULL((SELECT COUNT(*) FROM `visite` WHERE id_to = id1  AND timeof >= (UNIX_TIMESTAMP() - 604800)),0);
    SET pictures = IFNULL((SELECT (ISNULL(pict1) + ISNULL(pict2) + ISNULL(pict3) + ISNULL(pict4) + ISNULL(pict5)) as nbpict FROM `pictures` WHERE id_user = id1),0);

    SET tags = (((tags * ptags )/100)*10);
    SET likes = (((likes * plikes)/100)*50);
    SET visites = (((visites * pvis)/100));
    SET pictures = (((pictures * ppict )/100)*15);
    SET tags = IF(tags > ptags, ptags, tags);
    SET likes = IF(likes > plikes, plikes, likes);
    SET  visites = IF(visites > pvis, pvis, visites);
    SET pictures = IF(pictures > ppict, ppict, pictures);

    SET tot = ( tags + likes + visites + pictures);
        RETURN  tot;
    END');

    $sql = $pdo->exec("DROP FUNCTION IF EXISTS `checkblock`");
    $sql = $pdo->exec('CREATE DEFINER=`root`@`localhost` FUNCTION `checkblock`(`id1` INT, `id2` INT) RETURNS INT(11) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN
    DECLARE cross1 INT;
    DECLARE cross2 INT;
    DECLARE tot INT;
        SET cross1 = IFNULL((SELECT blocked.id_block FROM `blocked` WHERE blocked.id_from = id1 AND blocked.id_to = id2 LIMIT 1),0);
      SET cross2 = IFNULL((SELECT blocked.id_block FROM `blocked` WHERE blocked.id_from = id2 AND blocked.id_to = id1 LIMIT 1),0);

        SET tot = cross1 + cross2;
        RETURN  tot;
    END');

    $sql = $pdo->exec("DROP FUNCTION IF EXISTS `IsValid`");
    $sql = $pdo->exec("CREATE DEFINER=`root`@`localhost` FUNCTION `IsValid`(`sexcase` INT, `idtarget` INT) RETURNS INT(11) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
    BEGIN
    DECLARE orient INT;
    DECLARE sexc INT;
    DECLARE valid INT;
    DECLARE summ INT ;
    SET orient = (SELECT CASE oriented
                WHEN 'bi' THEN 10
                WHEN 'homo' THEN 20
                WHEN 'hetero' THEN 30
                ELSE 0
                  END as orientcase
                        FROM members WHERE id_user = idtarget);
    SET sexc = (SELECT CASE sexe
                WHEN 'male' THEN 100
                WHEN 'female' THEN 200
                WHEN 'other' THEN 300
                ELSE 0
                  END as sexc
                        FROM members WHERE id_user = idtarget);

    SET summ = sexcase + sexc + orient;
    SET valid =  CASE
          WHEN summ IN(112,114,115,117) THEN 1
          WHEN summ IN(127,124) THEN 1
                WHEN summ IN(132,135) THEN 1
                WHEN summ IN(211,214,215,218) THEN 1
                WHEN summ IN(225,228) THEN 1
                WHEN summ IN(231,234) THEN 1
                WHEN summ IN(313,316,319,323,326,329,333,336,339) THEN 1
                ELSE 0
        END;

    RETURN valid;
    END");

    $sql = $pdo->exec("DROP FUNCTION IF EXISTS `get_distance_km`");
    $sql = $pdo->exec("CREATE DEFINER=`root`@`localhost` FUNCTION get_distance_km (lat1 DOUBLE, lng1 DOUBLE, lat2 DOUBLE, lng2 DOUBLE) RETURNS DOUBLE
  BEGIN
    DECLARE rlo1 DOUBLE;
    DECLARE rla1 DOUBLE;
    DECLARE rlo2 DOUBLE;
    DECLARE rla2 DOUBLE;
    DECLARE dlo DOUBLE;
    DECLARE dla DOUBLE;
    DECLARE a DOUBLE;

    SET rlo1 = RADIANS(lng1);
    SET rla1 = RADIANS(lat1);
    SET rlo2 = RADIANS(lng2);
    SET rla2 = RADIANS(lat2);
    SET dlo = (rlo2 - rlo1) / 2;
    SET dla = (rla2 - rla1) / 2;
    SET a = SIN(dla) * SIN(dla) + COS(rla1) * COS(rla2) * SIN(dlo) * SIN(dlo);
    RETURN ROUND((6378137 * 2 * ATAN2(SQRT(a), SQRT(1 - a)) / 1000), 2);
  END");

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
