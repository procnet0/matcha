<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/geoloc/geoipcity.inc');
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/geoloc/geoipregionvars.php');

function checkForAccount($name, $password, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT id_user,login,password FROM members WHERE login = ?");
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
    return ($result = array('name' => true, 'password' => true , 'id' => $result['id_user']));
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

  if(empty($params['latitude']) || empty($params['longitude']))
  {
    $gi = geoip_open(realpath("GeoLiteCity.dat"),GEOIP_STANDARD);
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
         $ip=$_SERVER['HTTP_CLIENT_IP'];
       }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
         $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
       }
    else {
         $ip=$_SERVER['REMOTE_ADDR'];
       }
    $record = geoip_record_by_addr($gi,$ip);
    $params['latitude']= $record->latitude ;
    $params['latitude']= $record->longitude . "\n";
    geoip_close($gi);
  }

  $params['password'] =  hash('whirlpool',$params['password']);
  $datec = new DateTime($params['birthday']);
  $date = $datec->format('Y-m-d H:i:s');
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("INSERT INTO members (login,nom,prenom,email,password,secret_answer,birthday) VALUES
      (?, ?, ?, ?, ?, ?, ?)");
    $sql->bindParam(1, $params['pseudo'], PDO::PARAM_STR);
    $sql->bindParam(2, $params['nom'], PDO::PARAM_STR);
    $sql->bindParam(3, $params['prenom'], PDO::PARAM_STR);
    $sql->bindParam(4, $params['email'], PDO::PARAM_STR);
    $sql->bindParam(5, $params['password'], PDO::PARAM_STR);
    $sql->bindParam(6, $params['answer'], PDO::PARAM_STR);
    $sql->bindParam(7, $date, PDO::PARAM_STR);
    $sql->execute();

    $id = $pdo->lastInsertId();
    $time = time();
    $sql = $pdo->prepare("INSERT INTO pictures (id_user) VALUES (?)");
    $sql->bindParam(1, $id, PDO::PARAM_INT);
    $sql->execute();

    $sql = $pdo->prepare("INSERT INTO geoloc (id_user,latitude,longitude,timeof) VALUES (?,?,?,?)");
    $sql->bindParam(1, $id, PDO::PARAM_INT);
    $sql->bindParam(2, $params['latitude'], PDO::PARAM_STR);
    $sql->bindParam(3, $params['longitude'], PDO::PARAM_STR);
    $sql->bindParam(4, $time, PDO::PARAM_INT);
    $sql->execute();

    $sql = $pdo->prepare("INSERT INTO ping (id_user,timeof) VALUES (?,?)");
    $sql->bindParam(1, $id, PDO::PARAM_INT);
    $sql->bindParam(2, $time, PDO::PARAM_INT);
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

    $sql = $pdo->prepare("SELECT pict1,pict2,pict3,pict4,pict5 FROM pictures WHERE id_user = ?");
    $sql->bindParam(1, $result['id_user'] , PDO::PARAM_STR);
    $sql->execute();
    $tmp = $sql->fetch(PDO::FETCH_ASSOC);
    if(!empty($tmp))
    {
      $tmp = array_filter($tmp);
      $tmp2 = [];
      foreach ($tmp as $key => $value) {
        if($value && file_exists($value)) {
          $tmp2[$key] = $value;
        }}
      if($output = array_diff($tmp, $tmp2))
      {
        $result['output'] = $output;
        foreach($output as $key => $value) {
          if($value) {
          $sql = $pdo->prepare("UPDATE pictures SET ".$key." = '' WHERE id_user = ".$result['id_user']);
          $sql->execute();
          $tmp[$key] = '';
          }
        }
      }
      $result += $tmp;
    }
    $sql = $pdo->prepare("SELECT latitude,longitude,type,timeof FROM geoloc WHERE id_user= ? ORDER BY timeof DESC");
    $sql->bindParam(1, $result['id_user'] , PDO::PARAM_STR);
    $sql->execute();
    $tmp = $sql->fetchAll(PDO::FETCH_ASSOC);
    $first = $tmp['0'];
    if($first['type'] != 'user')
    {
      foreach ($tmp as $index => $item)
      {
        if($item != $first && $item['type'] == 'user' && $item['timeof'] > ($first['timeof'] - 84600)){
          $set = $item;
          break;
        }
      }
    }
    if(isset($set)){
    $result += $set;
    }
    else {
      $result += $first;
    }
    $pdo->commit();
  }catch (PDOException $e) {
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

// modifie la photo de profil
function updatePict($data, $pdo) {

  $data['profil_pict'] = str_replace('http://'.$_SERVER['HTTP_HOST'].'/matcha/','',$data['profil_pict']);
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT members.id_user,profil_pict, login FROM pictures INNER JOIN members ON members.id_user = pictures.id_user WHERE  login = ?");
    $sql->bindParam(1, $_SESSION['loggued_as'] , PDO::PARAM_STR);
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

// ajoute (<5) ou modifie (5) la photo active du carrousel
function AddOrChangePicturePhp($data, $pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT pict1, pict2, pict3, pict4, pict5, members.id_user FROM pictures INNER JOIN members ON members.id_user = pictures.id_user WHERE ? = members.login");
    $sql->bindParam(1, $_SESSION['loggued_as'], PDO::PARAM_STR);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();
    } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE Add/change pict-> " . $e->getMessage() . " FAILED TO Check number of pictures<br/>";
    die();
  }
  $error = '';
  $ret = [];
  $tmp = explode(',',$data['newone']);
  $newpict = base64_decode($tmp[1]);

  $info = getimagesize($data['newone']);
  if($info !== false) {
  $data['old'] = str_replace('http://localhost:8080/matcha/','',$data['old']);
  $newpict = imagecreatefromstring($newpict);
  }
  else {
    $error = 'Invalid file.';
  }
  if($newpict && !$error )
  {
    $pictname = 'app/imgprofil/'.$_SESSION['loggued_as'].'_'.date('h:i:s_z-o', time()).'.png';
    imagepng($newpict, $pictname );
    $key = array_keys($result,NULL);
    if($key && $key['0'])
    {
      try {
        $pdo->beginTransaction();
        $sql = $pdo->prepare("UPDATE pictures SET ".$key['0']." = ? WHERE id_user = ? ");
        $sql->bindParam(1, $pictname, PDO::PARAM_STR);
        $sql->bindParam(2, $result['id_user'], PDO::PARAM_STR);
        $sql->execute();
        $pdo->commit();
        } catch (PDOException $e) {
        $pdo->rollBack();
        return "Error!: DATABASE Add/change pict-> " . $e->getMessage() . " FAILED TO add picture to db<br/>";
        }
        $ret['status'] = 'added';
        $ret['number']= str_replace('pict','',$key['0']);
        $ret['src'] = $pictname;
       return json_encode($ret);
    }

    else {
      $cle = array_keys($result,$data['old']);
      if($cle && $cle['0']) {

      try {
        $pdo->beginTransaction();
        $sql = $pdo->prepare("UPDATE pictures SET  ".$cle['0']."= ?  WHERE id_user = ? ");
        $sql->bindParam(1, $pictname, PDO::PARAM_STR);
        $sql->bindParam(2, $result['id_user'], PDO::PARAM_STR);
        $sql->execute();
        $pdo->commit();
      } catch (PDOException $e) {
        $pdo->rollBack();
        return "Error!: DATABASE Add/change pict-> " . $e->getMessage() . " FAILED TO change picture to db<br/>";
      }
      unlink($data['old']);
      $ret['status'] = 'changed';
      $ret['number']= str_replace('pict','',$cle['0']);
      $ret['src'] = $pictname;
       return json_encode($ret);
     }
    }
  }
  else {
    $ret['status'] = $error;
    }
  return json_encode($ret);
}

// compare 2 tableau en profondeur 1 (utile pour comparer les object javascript)
function DiffArrayDepth1($array1 , $array2) {
  $arrayret = [];
  $x = 0;
  foreach ($array1 as $key => $value) {
    if(is_array($value)) {
      $isinside = false;
      foreach ($array2 as $key2 => $val2) {
          if(is_array($val2)){
            $tmp = array_diff($value , $val2);
          if(empty($tmp)) {
            $isinside = true;
            }
          }
        }
        if ($isinside == false) {
        $arrayret[$x] =$value;
        $x += 1;
        }
    }
  }
  return $arrayret;

}

// recupere les tags d un utilisateur
function getTags($log,$pdo) {

  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT id_tag, name_tag FROM tags");
    $sql->execute();
    $taglist = $sql->fetchAll(PDO::FETCH_ASSOC);

    $sql2 = $pdo->prepare("SELECT tags.id_tag,name_tag FROM tags INNER JOIN tags_members ON tags_members.id_tag = tags.id_tag INNER JOIN members ON members.id_user = tags_members.id_members  WHERE members.login = ?");
    $sql2->bindParam(1, $log,PDO::PARAM_STR);
    $sql2->execute();
    $activelist= $sql2->fetchAll(PDO::FETCH_ASSOC);
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE TAGINFO-> " . $e->getMessage() . " FAILED TO GET TAG<br/>";
    die();
  }
  $ret = [];
  $ret['active'] =  $activelist;
  $ret['taglist'] = $taglist;
  return json_encode($ret);
}

// verifie que le tableau de tag est valid / present dans la db / ne contien pas de doublon
function checkTag($array, $tags) {
  $result = [];
  $result['absent'] = [];
  $result['doble'] = false;
  $result['result'] = true;
  $result['new'] = $array;
  foreach ($array as $key =>$sub)
  {
    $result[$key]['count'] = 0;
    $result[$key]['doblecount'] = 0;

    foreach ($array as $keys => $items)
    {
        if($items['name'] == $sub['name'])
        {
          $result[$key]['doblecount'] += 1;
        }
    }
    if($result[$key]['doblecount'] > 1)
    {
      $result['doble'] = true;
    }

    foreach ($tags as $keys => $subs)
    {
      if($sub['name'] == $subs['name_tag'])
      {
         $result[$key]['count'] += 1;

      }
    }
    if ($result[$key]['count'] == 0)
    {
      $result['absent'][] = $sub ;
    }
  }
  if(!empty($result['absent'])) {
    $result['new'] = DiffArrayDepth1($result['new'], $result['absent']);
  }
  if($result['doble'] != false) {
    $result['result'] = false;
  }
  return $result;
}

// Ajout nouveaux tags + manage l
function updateTags($active,$pdo) {
  try {
    $debug = '';
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT id_tag, name_tag FROM tags");
    $sql->execute();
    $taglist = $sql->fetchAll(PDO::FETCH_ASSOC);
    $iduser = $_SESSION['id'];
    $sql2 = $pdo->prepare("SELECT tags.id_tag,name_tag FROM tags INNER JOIN tags_members ON tags_members.id_tag = tags.id_tag  WHERE tags_members.id_members = ?");
    $sql2->bindParam(1, $_SESSION['id'],PDO::PARAM_STR);
    $sql2->execute();
    $activelist= $sql2->fetchAll(PDO::FETCH_ASSOC);

    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() . " FAILED TO GET TAG<br/>";
    die();
  }
  foreach($active as $key => $sub) {
    $active[$key]['name'] = ucfirst(strtolower($sub['name']));
  }
  $check = checkTag($active,$taglist);
  $debug = $check;

  if($check['result'] == true){

     $error = 'none';
     $removed = DiffArrayDepth1($activelist, $active);
     $added = DiffArrayDepth1($active, $activelist);
     $added = DiffArrayDepth1($added, $check['absent']);

     if($added) {
       try {
         $pdo->beginTransaction();
         $query = "INSERT INTO tags_members (id_tag, id_members) VALUES ";
         $qpart = array_fill(0, count($added), "((SELECT tags.id_tag FROM tags WHERE name_tag = ? LIMIT 1),?)");
         $query .= implode(",", $qpart);
         $sql = $pdo->prepare($query);
         $i = 1;
         foreach($added as $item) {
           $sql->bindValue($i++, $item['name'], PDO::PARAM_STR);
           $sql->bindValue($i++, $iduser, PDO::PARAM_STR);
         }
         $sql->execute();
         $pdo->commit();
       } catch (PDOException $e) {
         $pdo->rollBack();
         $error = "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() .'/'. $query.'/'." FAILED TO ADD<br/>";
       }
   }
     if($removed) {
         try {
           $pdo->beginTransaction();
           $query = "DELETE FROM tags_members WHERE id_members = ? AND id_tag IN (";
           $qpart2 = array_fill(0, count($removed), "?");
           $query .= implode(",", $qpart2).")";
           $sql = $pdo->prepare($query);
           $i = 1;
           $binded = 0;
           $sql->bindValue($i++, $iduser, PDO::PARAM_STR);
         foreach($removed as $item) {
           $sql->bindValue($i++, $item['id_tag'], PDO::PARAM_STR);
           $binded += 1;
         }
         $ret['rquery']= $query;
         $sql->execute();
         $pdo->commit();
       } catch (PDOException $e) {
         $pdo->rollBack();
         $error  = "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() ." FAILED TO DELET<br/>";

       }
   }
   if(!empty($check['absent'])) {
     try {
       $pdo->beginTransaction();

      $query = "INSERT INTO tags (name_tag) VALUES ";
      $qpart = array_fill(0, count($check['absent']), "(?)");
      $query .= implode(",", $qpart);
      $sql = $pdo->prepare($query);
      $i = 1;
      foreach($check['absent'] as $item) {
        $sql->bindValue($i++, $item['name'], PDO::PARAM_STR);
      }
      $sql->execute();

       $query = "INSERT INTO tags_members (id_tag, id_members) VALUES ";
       $qpart = array_fill(0, count($check['absent']), "((SELECT tags.id_tag FROM tags WHERE name_tag = ?),?)");
       $query .= implode(",", $qpart);
       $sql = $pdo->prepare($query);
       $i = 1;
       foreach($check['absent'] as $item) {
         $sql->bindValue($i++, $item['name'], PDO::PARAM_STR);
         $sql->bindValue($i++, $iduser, PDO::PARAM_STR);
       }
       $sql->execute();
       $pdo->commit();
     } catch (PDOException $e) {
       $pdo->rollBack();
       $error  = "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() .'/'. $query.'/'." FAILED TO ADD TO DB<br/>";
     }
   }

  }
  else {
    $debug = $check;
    $error = 'Wrong data sended no modification made';
    $removed = [];
    $added = [];
  }
  $ret = [];
  $ret['debug'] = $debug;
  $ret['added'] = $added;
  $ret['removed'] = $removed;
  $ret['error'] = $error;
  return json_encode($ret);
}

//recupere l addresse avec lat et lng
function getAddrWithCoord($lat , $lng) {
  $url ="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.','.$lng."&key=AIzaSyAEwSUfxPzIphYziId_jFOIdx54clUnsdo";

  $ret = [];
  if($json = file_get_contents($url)) {
    $informations = json_decode($json, true);
  }
  if($informations['status'] == "OK") {

      $ret = $informations['results']['0'];
  }
 return $ret;
}

//change la location de l utilisateur courant
function updateLocation($data, $pdo) {
  //user input location
  if(!empty($data['input'])) {
    $url="https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($data['input'])."&key=AIzaSyAEwSUfxPzIphYziId_jFOIdx54clUnsdo";
    $ret = [];
    if($json = file_get_contents($url)) {
      $informations = json_decode($json, true);
    }
      if($informations['status'] === "OK") {
        $loc = $informations['results']['0']['geometry']['location'];
        try {
          $tim = time();
          $sql = $pdo->prepare("INSERT INTO geoloc (id_user,latitude,longitude,timeof,type) SELECT  members.id_user, ? , ? ,? , 'user' FROM members WHERE login = ? ");
          $sql->bindParam(1, $loc['lat'], PDO::PARAM_STR);
          $sql->bindParam(2, $loc['lng'], PDO::PARAM_STR);
          $sql->bindParam(3, $tim, PDO::PARAM_INT);
          $sql->bindParam(4, $_SESSION['loggued_as'], PDO::PARAM_STR);
          $sql->execute();
        }catch (PDOException $e){
          print "Error!: DATABASE UPDATE LOCATION-> " . $e->getMessage() . " FAILED TO UPDATE 1<br/>";
          die();
        }
        return $informations['results']['0']['formatted_address'];
      }
  }
  // auto locate
  else if (!empty($data['latitude']) && !empty($data['longitude'])) {
      $info = getAddrWithCoord($data['latitude'], $data['longitude']);

     if(!empty($info))
     {
       try {
         $tim = time();
         $sqt = $pdo->prepare("INSERT INTO geoloc (id_user,latitude,longitude,timeof,type) SELECT members.id_user, ? , ? ,? , 'auto' FROM members WHERE login = ? ");
         $sqt->bindParam(1, $data['latitude'], PDO::PARAM_STR);
         $sqt->bindParam(2, $data['longitude'], PDO::PARAM_STR);
         $sqt->bindParam(3, $tim, PDO::PARAM_INT);
         $sqt->bindParam(4, $_SESSION['loggued_as'], PDO::PARAM_STR);
         $sqt->execute();
       }catch (PDOException $e){
      print "Error!: DATABASE UPDATE LOCATION-> " . $e->getMessage() . " FAILED TO UPDATE 2 <br/>";
       die();
     }
     return $info['formatted_address'];
    }
  }
  else {
    return 'error';
  }
}

//distance entre 2 coordoneer
function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $decimals = 2) {

  $degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));

  $distance = $degrees * 111.13384;
  return round($distance, $decimals);
}

// recupere la liste des utilisateur correspondant au parametre
function Researcher($datas, $pdo) {
  $res = [];
  $age = explode(',',$datas['age']);
  $range = explode(',', $datas['range']);
  $area = $datas['area'];
  $extracted = intval($datas['extracted']);
  if($area){
    $area = json_decode($area);
  }
  $pop = explode(',', $datas['pop']);
  $tagsnm = [];
  $tagsnm = explode(',', $datas['tags']);


  try {
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


  } catch (PDOException $e) {
    print 'error =>'. $e;
    die();
  }
  try {
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

  if($datas['tags']) {
    $sql = $pdo->query("SELECT id_tag, name_tag FROM tags WHERE name_tag IN ('".str_replace(',', "','",$datas['tags'])."')");
    $taglist = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach($taglist as $key => $elem) {
      $tlist[$key] = $elem['id_tag'];
    }
  }
  else {
    $tlist = [];
  }

    $sql = $pdo->prepare("SELECT members.login, members.id_user, members.prenom, members.nom, TIMESTAMPDIFF( year,members.birthday,NOW()) AS age, members.sexe, members.oriented, members.profil_pict ,
      (
        SELECT CONCAT(latitude,',',longitude) AS pos
        FROM geoloc
        INNER JOIN members
        WHERE type = 'user'
        AND FROM_UNIXTIME(geoloc.timeof) > DATE_ADD(NOW(), INTERVAL -1 DAY)
        AND login = ?
        ORDER BY timeof
        DESC LIMIT 1
      )
      AS geouser,
      (
        SELECT CONCAT(latitude,',',longitude) AS pos
        FROM geoloc
        INNER JOIN members
        WHERE type = 'auto'
        AND login = ?
        ORDER BY timeof
        DESC LIMIT 1
      )
      AS geoauto ,
       (SELECT GROUP_CONCAT(id_tag) AS nb FROM `tags_members` WHERE id_members = members.id_user GROUP BY id_members) AS nb
      FROM members
      INNER JOIN geoloc ON geoloc.id_user = members.id_user
      WHERE login = ?
      GROUP BY id_user");
    $sql->bindParam(1, $_SESSION['loggued_as'], PDO::PARAM_STR);
    $sql->bindParam(2, $_SESSION['loggued_as'], PDO::PARAM_STR);
    $sql->bindParam(3, $_SESSION['loggued_as'], PDO::PARAM_STR);
    $sql->execute();
    $user = $sql->fetchAll(PDO::FETCH_ASSOC);
     if(!empty($user['0']['geoauto'])) {
      $pos = $user['0']['geoauto'];
      $arr = explode(',',$user['0']['geoauto']);
      $user['0']['geoauto'] = $arr;
    }
    else if (!empty($user['0']['geouser'])) {
        $pos = $user['0']['geouser'];
        $arr = explode(',',$user['0']['geouser']);
        $user['0']['geouser'] = $arr;
      }
    $sexnor = array($user['0']['sexe'], $user['0']['oriented']);

    switch($sexnor)  {
      case array('male','hetero'):
      $or = "'female'";$SexCase = '1'; break;

      case array('female','hetero'):
      $or = "'male'";$SexCase = '2'; break;

      case array('other','hetero'):
      $or = "'female','male','other'";$SexCase = '3'; break;

      case array('male','bi'):
      $or = "'female','male','other'";$SexCase = '4'; break;

      case array('female','bi');
      $or = "'female','male','other'";$SexCase = '5'; break;

      case array('other','bi'):
      $or = "'female','male','other'";$SexCase = '6'; break;

      case array('male','homo'):
      $or = "'male'";$SexCase = '7'; break;

      case array('female','homo'):
      $or = "'female'";$SexCase = '8'; break;

      case array('other','homo'):
      $or = "'female','male','other'";$SexCase = '9'; break;
    }

    $reqsql = "SELECT
    members.login,
    members.id_user,
    members.prenom,
    members.nom,
    GetScore(members.id_user) as score,
    TIMESTAMPDIFF(YEAR, members.birthday, NOW()) AS age,
    members.sexe,
    IsValid( ? , members.id_user) as Valid,
    members.oriented,
    members.profil_pict,
    GROUP_CONCAT(tags_members.id_tag) AS tags,
    checkblock(?, members.id_user) AS blocki,
    get_distance_km(".$pos.",(SELECT latitude FROM geoloc WHERE geoloc.id_user = members.id_user ORDER BY timeof DESC LIMIT 1),
    (SELECT longitude FROM geoloc WHERE geoloc.id_user = members.id_user ORDER BY timeof DESC LIMIT 1)) AS dist, (SELECT COUNT(tags_members.id_tag) FROM tags_members WHERE tags_members.id_members = members.id_user AND tags_members.id_tag IN ('".str_replace(',',"','",$user['0']['nb'])."')) AS nb
  FROM
    members, geoloc
    LEFT JOIN tags_members ON (id_user=tags_members.id_members)
  WHERE ";
    $reqsql .= "members.sexe IN (".$or.")";
    $reqsql .= ' AND members.id_user != ? AND geoloc.id_user = members.id_user ';
    $reqsql .= ' GROUP BY id_user
     HAVING age BETWEEN ? AND ?
     AND Valid = 1 AND dist BETWEEN 0 AND ? AND blocki = 0 AND score BETWEEN ? AND ?';

    if(isset($tlist)) {
      foreach ($tlist as $elem) {
        $reqsql .= " AND tags LIKE ?";
      }
    }
    $reqsql .= ' LIMIT ?, 5';
    $sql = $pdo->prepare($reqsql);
    $sql->bindParam(1,$SexCase, PDO::PARAM_INT);
    $sql->bindParam(2, $user['0']['id_user'], PDO::PARAM_INT);
    $sql->bindParam(3, $user['0']['id_user'], PDO::PARAM_INT);
    $sql->bindParam(4, $age['0'], PDO::PARAM_INT);
    $sql->bindParam(5, $age['1'], PDO::PARAM_INT);
    $sql->bindParam(6, $range['0'], PDO::PARAM_INT);
    $sql->bindParam(7, $pop['0'], PDO::PARAM_INT);
    $sql->bindParam(8, $pop['1'], PDO::PARAM_INT);
    $binded = 9;
    if(isset($tlist)) {
      $res['tlist'] = $tlist;
      foreach ($tlist as $elem) {
        $tmp = "%".$elem ."%";
        $sql->bindParam($binded, $tmp , PDO::PARAM_STR);
        $binded += 1;
      }
    }
    $sql->bindParam($binded, $extracted, PDO::PARAM_INT);
    $sql->execute();
    $res['binded'] = $binded;
    $resultaa = $sql->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $res['binded'] = $binded;
    $res['error'] =  "Error!: DATABASE searching-> " . $e->getMessage() . " FAILED TO search<br/>";
  }
  if(isset($resultaa)){
    foreach($resultaa as $key =>$array) {
      if ($array['profil_pict'] == "#" || !file_exists($array['profil_pict'])) {
        $resultaa[$key]['profil_pict'] = 'app/css/image/Photo-non-disponible.png';
      }
    }
    $idlist = [];
    foreach ($resultaa as $key=>$elem) {
      $idlist[$key] = $elem['id_user'];
    }
    $res['online'] = getOnlineMembers($idlist, $pdo);
    $res['taglist']= isset($tlist) ? $tlist : '';
    $res['result']= $resultaa;
    $res['extracted']= count($resultaa);
  }
  /*
  if(isset($reqsql))
  {
    $res['req']= $reqsql;
  }
  */
  return($res);
}

//recupere la list des membre connecté
function getOnlineMembers($array_id_user, $pdo) {
  $res = [];
  if($array_id_user) {
    $query = "SELECT id_user, IF(timeof > (UNIX_TIMESTAMP() - 900), 'yes', 'no') AS connected FROM ping WHERE id_user IN (";
    $query .= implode(",",$array_id_user);
    $query .= ")";
    try {

    $sql= $pdo->query($query);
    $res = $sql->fetchall(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
    print "Error!: DATABASE getOnlineMembers-> " . $e->getMessage() . " FAILED TO search<br/>";
    die();
    }
  }
  if($res) {
    $tmp = [];
    foreach($res as $key=>$elem) {
      $tmp[$elem['id_user']] = $elem['connected'];
    }
    $res = $tmp;
  }
  return $res;
}

//recuper les info du target
function lookathim($login, $pdo) {

  $result = [];
  try {
    $sql = $pdo->prepare("SELECT checkblock(?,id_user) as blocki FROM members WHERE login = ?");
    $sql->bindParam(1,$_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(2,$login, PDO::PARAM_STR);
    $sql->execute();
    $block = $sql->fetch(PDO::FETCH_ASSOC);


    if($block['blocki'] == 0) {
      $st = '0';
      $sql = $pdo->prepare("SELECT id_user,login, nom, prenom, TIMESTAMPDIFF( year,members.birthday,NOW()) AS age, sexe, oriented, bio, profil_pict FROM members WHERE login = ?");
      $sql->bindParam(1, $login, PDO::PARAM_INT);
      $sql->execute();
      $result = $sql->fetch(PDO::FETCH_ASSOC);
      if($result) {
        if($result['profil_pict'] == "#" || !file_exists($result['profil_pict'])) {
          $result['profil_pict'] = 'app/css/image/Photo-non-disponible.png';
        }

      $id = $result['id_user'];

      $st= '1';
      $sql = $pdo->prepare("SELECT pict1,pict2,pict3,pict4,pict5 FROM pictures WHERE id_user = ?");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['pictures']= $sql->fetch(PDO::FETCH_ASSOC);
      foreach($result['pictures'] as $key =>$elem) {
        if ($elem == 'NULL' || !file_exists($elem)) {
          $result['pictures'][$key] = 'app/css/image/Photo-non-disponible.png';
        }
      }

      $st = '2';
      $sql = $pdo->prepare("SELECT tags_members.id_tag, name_tag FROM tags_members LEFT JOIN tags ON tags.id_tag= tags_members.id_tag WHERE id_members = ?");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->execute();
      $tags = $sql->fetchall(PDO::FETCH_ASSOC);
      $result['tags'] = $tags;


      $st = '3';
      $sql = $pdo->prepare("SELECT id_user, timeof, IF(timeof > (UNIX_TIMESTAMP() - 900), 'yes', 'no') AS connected FROM ping WHERE id_user = ?");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['logs']= $sql->fetch(PDO::FETCH_ASSOC);

      $st = '4';
      $sql = $pdo->prepare("SELECT latitude, longitude FROM geoloc WHERE geoloc.id_user = ? AND type = 'auto' ORDER BY timeof DESC LIMIT 1");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['geoloc']= $sql->fetch(PDO::FETCH_ASSOC);
      $result['geoloc']['info'] = getAddrWithCoord($result['geoloc']['latitude'],$result['geoloc']['longitude']);

      $st = '5';
      $sql = $pdo->prepare("SELECT latitude, longitude FROM geoloc WHERE geoloc.id_user = ? ORDER BY timeof DESC LIMIT 1");
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->execute();
      $current= $sql->fetch(PDO::FETCH_ASSOC);

      $result['score'] = getscore($id, $pdo);

      $st = '6';
      $sql = $pdo->prepare("SELECT DISTINCT (SELECT id_like FROM `likes` WHERE id_from = ? AND id_to = ? LIMIT 1) AS toyou , (SELECT id_like FROM `likes` WHERE id_from = ? AND id_to = ? LIMIT 1) AS fromyou FROM likes");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(4, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['likes'] = $sql->fetch(PDO::FETCH_ASSOC);

      $st = '7';
      $sql = $pdo->query("SELECT get_distance_km(".$result['geoloc']['latitude'].",".$result['geoloc']['longitude'].",". $current['latitude'].",". $current['longitude'].") AS dist");
      $result['dist'] = $sql->fetch(PDO::FETCH_ASSOC);
      }
    }
  } catch (PDOException $e) {
    print "error = ".$e." in lookathim stage=".$st;
    die();
  }
  if($result) {
    try {
      $pdo->beginTransaction();
      $sql = $pdo->prepare("INSERT INTO visite (id_from, id_to, timeof) VALUES (? ,? , UNIX_TIMESTAMP() )");
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(2, $id, PDO::PARAM_INT);
      $sql->execute();
      $last = $pdo->lastInsertId();

      $sql = $pdo->prepare("INSERT INTO notification (id_user,id_item,id_from,type,timeof) VALUES (?,?,?,'2',UNIX_TIMESTAMP())");
      $sql->bindParam(1,$id, PDO::PARAM_INT);
      $sql->bindParam(2,$last, PDO::PARAM_INT);
      $sql->bindParam(3,$_SESSION['id'], PDO::PARAM_INT);
      $sql->execute();
      $pdo->commit();
    } catch (PDOException $e) {
      print "error = ".$e." in lookathim stage= add visite";
      $pdo->rollBack();
      die();
    }
  }
  return $result;
}

// Add un report dans la db
function reportevent($from, $param, $pdo) {
  $to = $param['to'];
  $type = $param['type'];
    switch ($type) {
      case 1:
        $type = "Message indesirable";
        break;
      case 2:
        $type= "Fake profil";
        break;
      case 3:
        $type = "Photo non conforme";
        break;
      default:
        $type = "Other";

    }
  $result='';
  $content = $param['content'];
  try {

    $sql =$pdo->prepare("SELECT id_user FROM members WHERE login = ?");
    $sql->bindParam(1, $to, PDO::PARAM_STR);
    $sql->execute();
    $tmp = $sql->fetch(PDO::FETCH_ASSOC);
    $to = $tmp['id_user'];
    var_dump($tmp);

    $sql =$pdo->prepare("SELECT id_user FROM members WHERE login = ?");
    $sql->bindParam(1, $from, PDO::PARAM_STR);
    $sql->execute();
    $tmp = $sql->fetch(PDO::FETCH_ASSOC);
    $from = $tmp['id_user'];

    var_dump($tmp);

    $sql = $pdo->prepare("INSERT INTO report (id_from, id_to,timeof,subject,content) VALUES (?,?,UNIX_TIMESTAMP(),?,?) ");
    $sql->bindParam(1, $from, PDO::PARAM_INT);
    $sql->bindParam(2, $to, PDO::PARAM_INT);
    $sql->bindParam(3, $type, PDO::PARAM_STR);
    $sql->bindParam(4, $content, PDO::PARAM_STR);
    $sql->execute();
    $result = $pdo->lastInsertId();
  } catch (PDOException $e) {
    print "error database reportevent ->".$e." param = ". implode(' ',$param);
    die();
  }
  return $result;
}

// gere les like unlike
function likevent($from, $to, $pdo) {
  $time = time();
  $tot = [];

  if(!empty($from) && !empty($to)) {
  $sql = $pdo->prepare("SELECT id_user, checkblock(?,id_user) AS blocki FROM members WHERE login = ? HAVING blocki = 0");
  $sql->bindparam(1, $from, PDO::PARAM_STR);
  $sql->bindparam(2, $to, PDO::PARAM_STR);
  $sql->execute();
  $toinfo = $sql->fetch();
  }
  if($toinfo) {
    $to = $toinfo['id_user'];
    try {
      $pdo->beginTransaction();
      $sql = $pdo->prepare("SELECT id_like FROM likes WHERE id_from = ? AND id_to = ?");
      $sql->bindParam(1, $from, PDO::PARAM_INT);
      $sql->bindParam(2, $to, PDO::PARAM_INT);
      $sql->execute();
      $result = $sql->fetch();

      if(empty($result) || $result == false) {

        $sql = $pdo->prepare("INSERT INTO likes (id_from, id_to, timeof) VALUES (?,?,UNIX_TIMESTAMP())");
        $sql->bindParam(1, $from, PDO::PARAM_INT);
        $sql->bindParam(2, $to, PDO::PARAM_INT);
        $res['status'] = $sql->execute();
        $res['id'] = $pdo->lastInsertId();
        $res['action'] ='insert';
        $sql = $pdo->prepare("INSERT INTO notification (id_user,id_item,id_from,type,timeof) VALUES (?,?,?,'1',UNIX_TIMESTAMP())");
        $sql->bindParam(1,$to, PDO::PARAM_INT);
        $sql->bindParam(2,$res['id'], PDO::PARAM_INT);
        $sql->bindParam(3,$from, PDO::PARAM_INT);
        $sql->execute();
      }
      else if(!empty($result)) {
        $sql = $pdo->prepare("DELETE FROM likes WHERE id_like = ?");
        $sql->bindParam(1, $result['id_like'],PDO::PARAM_INT);
        $res['status'] = $sql->execute();
        $res['action'] = 'delete';
        $sql = $pdo->prepare("INSERT INTO notification (id_user,id_item,id_from,type,timeof) VALUES (?,'0',?,'5',UNIX_TIMESTAMP())");
        $sql->bindParam(1,$to, PDO::PARAM_INT);
        $sql->bindParam(2,$from, PDO::PARAM_INT);
        $sql->execute();
      }

      $sql = $pdo->prepare("SELECT DISTINCT (SELECT id_like FROM `likes` WHERE id_to = ? AND id_from = ? LIMIT 1) AS toyou , (SELECT id_like FROM `likes` WHERE id_from = ? AND id_to = ? LIMIT 1) AS fromyou FROM likes");
      $sql->bindParam(2, $to, PDO::PARAM_INT);
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(4, $to, PDO::PARAM_INT);
      $sql->execute();
      $tmp = $sql->fetch(PDO::FETCH_ASSOC);
      $tot['likes'] = [];
      $tot['likes']['toyou'] = $tmp['toyou'];
      $tot['likes']['fromyou'] = $tmp['fromyou'];

      if( !empty($tot['likes']['toyou']) && !empty($tot['likes']['fromyou'])) {
        $sql = $pdo->prepare("INSERT INTO notification (id_user,id_item,id_from,type,timeof) VALUES (?,?,?,'4',UNIX_TIMESTAMP())");
        $sql->bindParam(1,$to, PDO::PARAM_INT);
        $sql->bindParam(2,$res['id'], PDO::PARAM_INT);
        $sql->bindParam(3,$_SESSION['id'], PDO::PARAM_INT);
        $sql->execute();
      }

      $tot['match'] = gestionmatch($from,$to,$pdo,$tot['likes']);
      $tot['already']= $result;
      $tot['new'] = $res;
      $tot['status'] = "OK";
      $pdo->commit();
    } catch (PDOException $e) {
      $pdo->rollBack();
      print "error ". $e ." on likevent";
      $tot['status'] = "fail";
      die();
    }
  }
  return($tot);
}

// Gere les match
function gestionmatch($from, $to, $pdo, $tab) {
  if(!empty($from) && !empty($to) && !empty($tab)){
    try {
      $sql = $pdo->prepare("SELECT matchs.* FROM matchs WHERE( id_1 = ? AND id_2 = ?) OR ( id_1 = ? AND id_2 = ?)");
      $sql->bindParam(1, $from,PDO::PARAM_INT);
      $sql->bindParam(2, $to,PDO::PARAM_INT);
      $sql->bindParam(3, $to,PDO::PARAM_INT);
      $sql->bindParam(4, $from,PDO::PARAM_INT);
      $sql->execute();
      $match = $sql->fetch(PDO::FETCH_ASSOC);
      $statut = 'match -';
      if(!empty($match)) {
         if(!empty($tab['toyou']) && !empty($tab['fromyou']))
         {
           $sql = $pdo->prepare("UPDATE matchs SET active = 1 , timeof = UNIX_TIMESTAMP() WHERE id_match = ? ");
           $sql->bindParam(1, $match['id_match'], PDO::PARAM_INT);
           $sql->execute();
           $statut = 'match +';
         }
         else
         {
           $sql = $pdo->prepare("UPDATE matchs SET active = 0 , timeof = UNIX_TIMESTAMP() WHERE id_match = ? ");
           $sql->bindParam(1, $match['id_match'], PDO::PARAM_INT);
           $sql->execute();
           $statut = 'match -';
         }
      }
      else if ((!empty($tab['toyou']) && !empty($tab['fromyou']))) {
          $sql = $pdo->prepare("INSERT INTO matchs (id_1, id_2 , timeof, active) VALUES
          (?,?,UNIX_TIMESTAMP(),1)");
          $sql->bindParam(1, $from, PDO::PARAM_INT);
          $sql->bindParam(2, $to, PDO::PARAM_INT);
          $sql->execute();
          $statut = 'match +';
        }
      return $statut;
      } catch (PDOException $e) {
         return $e;
      }
  }
  else {
    return 'error';
  }
}

// Creer un block entre from et to
function blockevent($from, $to, $pdo) {
  $ret = [];
  if(!empty($from) && !empty($to)) {
    $sql = $pdo->prepare("SELECT id_user FROM members WHERE login = ?");
    $sql->bindparam(1, $to, PDO::PARAM_STR);
    $sql->execute();
    $toinfo = $sql->fetch();
      if($toinfo){
        $to = $toinfo['id_user'];
      }
      else {
        $ret['status'] = 'fail';
        return $ret;
      }
    try {
      $sql = $pdo->prepare("INSERT INTO blocked (id_from, id_to, timeof) VALUES (?,?,UNIX_TIMESTAMP())");
      $sql->bindParam(1, $from, PDO::PARAM_INT);
      $sql->bindParam(2, $to, PDO::PARAM_INT);
      $sql->execute();

    } catch (PDOException $e) {
      $ret = [];
      $ret['error'] = "error ". $e ." on blockevent";
      $ret['status'] = "fail";
      return $ret;
    }
    $ret['status'] = 'OK';
    return $ret;
  }
}

//recupere la list des blocks
function getblocklist($pdo) {
  $res = [];
  try {
    $sql = $pdo->prepare("SELECT id_block, id_to, members.login FROM blocked LEFT JOIN members ON id_to = members.id_user WHERE id_from = ?");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $res['error'] = 'Error in getblocklist =>'.$e;
    return $res;
  }
  return $res;
}

//unblock le target
function removeblocks($target, $pdo) {
    $res = [];
    try {
      $sql = $pdo->prepare("DELETE blocked FROM blocked LEFT JOIN members ON id_to = members.id_user WHERE id_from=? AND members.login = ? AND id_to = members.id_user");
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(2, $target, PDO::PARAM_STR);
      $sql->execute();
      $res['STATUS'] = 'OK';
    } catch (PDOException $e) {
      $res['error'] = 'Error in getblocklist =>'.$e;
      $res['STATUS'] = 'Error';
      return $res;
    }
    return $res;
  }

// Recupe le score du target
function getscore($target, $pdo) {
  $ret = [];
  if($target) {
    try {
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

      $sql = $pdo->prepare("SELECT GetScore(?) as score");
      $sql-> bindParam(1, $target, PDO::PARAM_INT);
      $sql->execute();
      $ret['result'] = $sql->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      $ret['error'] = $e . ' in getScore';
      $ret['result'] = 'null';
      die();
    }
  }
  return $ret;
}

//msg et notif interface
function GetMsgInterface($pdo) {
  $ret = [];
  $ret['notif'] = [];
  $ret['UserActiv'] = [];
  $ret['msg'] = [];
  try {

    $sql = $pdo->prepare("SELECT id_match , members.profil_pict, members.login, timeof, active, IF(id_1=? , id_2 , id_1) AS id FROM matchs LEFT JOIN members ON members.id_user = IF(id_1=?, id_2, id_1) WHERE (id_1 = ? OR id_2 = ?)");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(4, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret['UserActiv'] = $sql->fetchAll(PDO::FETCH_ASSOC);

    $sql = $pdo->prepare("SELECT COUNT(*) as new_notification, COUNT(IF(type = 1 OR type = 4 OR type = 5,1,NULL)) as like_count, COUNT(IF(type = 2,1,NULL)) as visites_count, COUNT(IF(type = 3,1,NULL)) as messages_count FROM notification WHERE notification.id_user = ? AND new = 1");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret['notif'] += $sql->fetch(PDO::FETCH_ASSOC);

    $sql = $pdo->prepare("SELECT COUNT(id_notif) as num, notification.timeof, id_from, members.login FROM notification INNER JOIN members WHERE notification.id_user = ? AND id_from = members.id_user AND type = 3 GROUP BY id_notif");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret['notif']['msg'] = $sql->fetchAll(PDO::FETCH_ASSOC);

    if(!empty($ret['notif']['new']['msg'])) {
    $FirstUserMsg = $ret['notif']['new']['msg']['0']['id_from'];
    $ret['msg']['id'] = $FirstUserMsg;
    }

  } catch (PDOException $e) {
    $ret['msg']['Error'] = $e . ' in GetMsgInterface';
  }
  return $ret;
}

//contenu des MSG
function RedeemMsg($id_user, $offset, $pdo) {
  $ret = [];
  $ret['status'] = 'OK';
  $ret['msg'] = [];
  try {
    $sql = $pdo->prepare("SELECT notification.id_notif, notification.new, messages.timeof, content, IF(messages.id_from = ?, '1', '0') as fromyou
      FROM messages INNER JOIN notification
      WHERE
        notification.id_item = messages.id_msg
        AND ((messages.id_from = ? AND id_to = ?)
        OR (messages.id_from = ? AND id_to = ?))
        AND notification.type = 3
        ORDER BY timeof DESC LIMIT ?, 10");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(3, $id_user, PDO::PARAM_INT);
    $sql->bindParam(4, $id_user, PDO::PARAM_INT);
    $sql->bindParam(5, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindValue(6, intval($offset), PDO::PARAM_INT);
    $sql->execute();
    $ret['msg'] = $sql->fetchAll(PDO::FETCH_ASSOC);
    $sql->closeCursor();
    $arr = [];
    if (count($ret['msg']) > 0)
    {
      foreach($ret['msg'] as $elem)
      {
        $arr[] = $elem['id_notif'];
      }
      $str = implode(",", $arr);
      $pdo->query("UPDATE notification SET new = 0 WHERE id_notif IN (".implode(",", $arr).") AND id_from != ".$_SESSION['id']);
    }
    $sql = $pdo->prepare("SELECT COUNT(*) as `counter` FROM messages INNER JOIN notification WHERE notification.id_item = messages.id_msg AND ((messages.id_from = ? AND id_to = ?) OR (messages.id_from = ? AND id_to = ?)) AND notification.type = 3");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(2, $id_user, PDO::PARAM_INT);
    $sql->bindParam(3, $id_user, PDO::PARAM_INT);
    $sql->bindParam(4, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret += $sql->fetch(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
    $ret['status'] = $e;
  }
  return($ret);
}

// Insert new MSG in db
function PostNewMsg($id_user, $content, $pdo) {
  $ret['status'] = 'OK';
  try {
    $pdo->beginTransaction();

    $sql= $pdo->prepare("SELECT checkblock(?, ?) AS blocki, active as matchi FROM matchs WHERE IF(id_1 = ?,id_2, id_1)=? AND IF(id_2=?, id_1, id_2)=?");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(2, $id_user, PDO::PARAM_INT);
    $sql->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
    $sql->bindParam(4, $id_user, PDO::PARAM_INT);
    $sql->bindParam(5, $id_user, PDO::PARAM_INT);
    $sql->bindParam(6, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $block = $sql->fetch(PDO::FETCH_ASSOC);
    if($block['blocki'] == 0 && $block['matchi'] == 1) {
      $sql = $pdo->prepare("INSERT INTO messages (id_from, id_to, timeof, content) VALUES (?,?,UNIX_TIMESTAMP(),?)");
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(2, $id_user, PDO::PARAM_INT);
      $sql->bindParam(3, $content, PDO::PARAM_STR);
      $sql->execute();
      $last_id = $pdo->lastInsertId();
      $sql->closeCursor();
      $sql = $pdo->prepare("INSERT INTO notification VALUES (null, ?, $last_id, ?, 3, UNIX_TIMESTAMP(), 1)");
      $sql->bindParam(1, $id_user, PDO::PARAM_INT);
      $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
      $sql->execute();
      $ret['error'] = "NO";
      $ret['content'] = $content;
    }
    else
      $ret['error'] = "Blocked";
    $pdo->commit();
    } catch (PDOException $e) {
      $pdo->rollBack();
      $ret['status'] = $e;
  }
  return $ret;
}

// Recup Notif  ancien / news     avec les types / logins
function RedeemNotifContent($pdo, $nb, $type) {
  $ret = [];
  $ret['status'] = 'OK';
  $_SESSION['id_msg'] = "";
  try {
    if ($type == 1)
      $actual = 'IN (1,4,5)';
    else {
      $actual = '= 2';
    }
    $sql = $pdo->prepare("SELECT notification.*, members.login, members.profil_pict, checkblock(notification.id_user, members.id_user) as blocki FROM notification LEFT JOIN members ON notification.id_from = members.id_user WHERE notification.id_user = ? AND type $actual HAVING blocki = 0 ORDER BY timeof DESC LIMIT $nb,10");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret['notif'] = $sql->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $ret['status'] = $e;
  }
  return($ret);
}

// Change le statuts non lu --> lu
function UpdateNotifStatus($id_notif , $pdo) {
  $ret = [];
  $ret['status'] = 'OK';
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("UPDATE notification SET new = 0 WHERE id_notif = ?");
    $sql->bindParam(1, $id_notif, PDO::PARAM_INT);
    $sql->execute();
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollback();
    $ret['status'] = $e;
  }
  return($ret);
}

// Recupere le nombre de nouvel notif et messages chat( total msg et autre )
function RNewNotif($id, $pdo) {
  $ret = [];
  $ret['status'] = 'OK';
  try {
    if ($id != "-1")
    {
      $sql = $pdo->prepare("SELECT notification.new, notification.id_notif, messages.content, notification.timeof FROM notification INNER JOIN messages
      WHERE
        messages.id_msg = notification.id_item
        AND notification.type = 3
        AND (messages.id_from = ? AND messages.id_to = ?)
        AND new = 1
        ");
        $sql->bindParam(1, $id, PDO::PARAM_INT);
        $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
        $sql->execute();
        $ret['msg'] = $sql->fetchAll(PDO::FETCH_ASSOC);
        if (count($ret['msg']) > 0)
        {
          $arr = [];
          foreach($ret['msg'] as $elem)
          {
            $arr[] = $elem['id_notif'];
          }
          $str = implode(",", $arr);
          $pdo->query("UPDATE notification SET new = 0 WHERE id_notif IN (".implode(",", $arr).")");
        }
    }
    $sql = $pdo->prepare("SELECT
      COUNT(IF(type = 3, 1, NULL)) as nb_msg,
      COUNT(IF(type != 3, 1, NULL)) as nb_other,
      COUNT(IF(type = 1 OR type = 5 OR type = 4, 1, NULL)) as nb_like,
      COUNT(IF(type = 2, 1, NULL)) as nb_visits
      FROM notification
      WHERE
        id_user = ?
        AND new = 1");
    $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret += $sql->fetch(PDO::FETCH_ASSOC);
    $sql->closeCursor();
    $sql = $pdo->prepare("SELECT
      COUNT(*) as nb_notif,
      members.login,
      notification.type
      FROM notification
      INNER JOIN members
      WHERE
      	members.id_user = notification.id_from
        AND notification.id_notif > ?
        AND notification.id_user = ?
        AND new = 1
        GROUP BY members.login, notification.type");
    $sql->bindParam(1, $_SESSION['max_id'], PDO::PARAM_INT);
    $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret['notif'] = $sql->fetchAll(PDO::FETCH_ASSOC);
    $lel = $pdo->query("SELECT MAX(id_notif) FROM `notification`");
    $tab = $lel->fetch();
    $ret['previous_off'] = $_SESSION['max_id'];
    $_SESSION['max_id'] = $tab[0];
  } catch (PDOException $e) {
    $ret['status'] = $e;
  }
  return ($ret);
}

function newNotifAuto($pdo, $offset){
  $ret = [];
  $ret['status'] = "OK";
  try{
    $sql = $pdo->prepare("SELECT
    notification.*,
    members.login,
    members.profil_pict
    FROM notification
    INNER JOIN members
    WHERE
      members.id_user = notification.id_from
      AND notification.id_notif > ?
      AND notification.id_user = ?
      AND new = 1
      AND notification.type != 3
      GROUP BY notification.id_notif");
    $sql->bindParam(1, $offset, PDO::PARAM_INT);
    $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    $sql->execute();
    $ret['notif'] = $sql->fetchAll(PDO::FETCH_ASSOC);
  } catch(PDOException $e)
  {
    $ret['status'] = $e;
  }
  return ($ret);
}

function recoverPassword($log,$answer,$pdo) {
  $ret = [];
  try {
    $sql = $pdo->prepare("SELECT count(id_user) as result, email FROM members WHERE login = ? AND secret_answer = ? GROUP BY id_user");
    $sql->bindParam(1, $log, PDO::PARAM_STR);
    $sql->bindParam(2, $answer, PDO::PARAM_STR);
    $sql->execute();
    $res = $sql->fetch(PDO::FETCH_ASSOC);
    $ret['data'] = $res;
    if($res['result'] == 1)
    {
      $ret['status'] = 'OK';
    }
    else
    {
      $ret['status'] = 'Mauvaise informations';
    }
  } catch (PDOException $e) {
    $ret['status'] = 'Error';
    $ret['error'] = $e;
  }
  return $ret;
}

function resetPassword($log, $pass, $pdo) {
  $pass =  hash('whirlpool', $pass);
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare('UPDATE members SET password = ? WHERE login = ?');
    $sql->bindParam(1, $pass, PDO::PARAM_STR);
    $sql->bindParam(2, $log, PDO::PARAM_STR);
    $sql->execute();
    $status = 1;
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollback();
    print 'error'. $e;
    $status = 0;
  }
  return $status;
}

?>
