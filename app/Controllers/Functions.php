<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/geoloc/geoipcity.inc');
include_once($_SERVER['DOCUMENT_ROOT'].'/matcha/app/geoloc/geoipregionvars.php');

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
  $inactivlist = [];
  $inactivlist = DiffArrayDepth1($taglist, $activelist);
  $ret = [];
  $ret['active'] =  $activelist;
  $ret['inactive'] = $inactivlist;
  return json_encode($ret);
}

function checkTag($array, $tags) {
  $result = [];
  $result['conform'] = true;
  $result['doble'] = false;
  $result['result'] = false;
  $result['new'] = $array;
  foreach ($array as $key =>$sub)
  {
    $result[$key]['count'] = 0;
    foreach ($tags as $keys => $subs) {
      if($sub['id_tag'] == $subs['id_tag'] && $sub['name'] == $subs['name_tag'])
      { $result[$key]['count'] += 1; }
    }
    if( $result[$key]['count'] > 1) {
      $result['doble'] = true;
    }
    else if ($result[$key]['count'] == 0) {
      $result['conform'] = false;
    }
  }

  if( $result['conform'] == true && $result['doble'] == false) {
    $result['result'] = true;
  }


  return $result;
}

function updateTags($active,$inactive,$pdo) {
  try {
    $pdo->beginTransaction();
    $sql = $pdo->prepare("SELECT id_tag, name_tag FROM tags");
    $sql->execute();
    $taglist = $sql->fetchAll(PDO::FETCH_ASSOC);
    $sql2 = $pdo->prepare("SELECT tags.id_tag,name_tag FROM tags INNER JOIN tags_members ON tags_members.id_tag = tags.id_tag INNER JOIN members ON members.id_user = tags_members.id_members  WHERE members.login = ?");
    $sql2->bindParam(1, $_SESSION['loggued_as'],PDO::PARAM_STR);
    $sql2->execute();
    $activelist= $sql2->fetchAll(PDO::FETCH_ASSOC);
    $sql3 = $pdo->prepare("SELECT id_user FROM members WHERE login = ?");
    $sql3->bindParam(1, $_SESSION['loggued_as'], PDO::PARAM_STR);
    $sql3->execute();
    $iduser = $sql3->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    print "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() . " FAILED TO GET TAG<br/>";
    die();
  }
  $check = checkTag(array_merge($active,$inactive),$taglist);
  if($check['result'] == true){
     $error = 'none';
     $removed = DiffArrayDepth1($activelist, $active);
     $added = DiffArrayDepth1($active, $activelist);
     if($added) {
     try {
       $pdo->beginTransaction();
       $query = "INSERT INTO tags_members (id_tag, id_members) VALUES ";
       $qpart = array_fill(0, count($added), "(?,?)");
       $query .= implode(",", $qpart);
       $sql = $pdo->prepare($query);
       $i = 1;
       foreach($added as $item) {
         $sql->bindValue($i++, $item['id_tag'], PDO::PARAM_STR);
         $sql->bindValue($i++, $iduser['id_user'], PDO::PARAM_STR);
       }
       $sql->execute();
       $pdo->commit();
     } catch (PDOException $e) {
       $pdo->rollBack();
       print "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() .'/'. $query.'/'." FAILED TO ADD<br/>";
       die();
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
       $sql->bindValue($i++, $iduser['id_user'], PDO::PARAM_STR);
     foreach($removed as $item) {
       $sql->bindValue($i++, $item['id_tag'], PDO::PARAM_STR);
       $binded += 1;
     }
     $ret['rquery']= $query;
     $sql->execute();
     $pdo->commit();
   } catch (PDOException $e) {
     $pdo->rollBack();
     print "Error!: DATABASE TAGUPDATE-> " . $e->getMessage() ." FAILED TO DELET<br/>";
     die();
   }
  }
  }
  else {
    $error = 'Wrong data sended no modification made';
    $removed = [];
    $added = [];
  }
  $ret = [];
  $ret['added'] =$added;
  $ret['removed'] = $removed;
  $ret['error'] = $error;
  return json_encode($ret);
}

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

function updateLocation($data, $pdo) {
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

function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $decimals = 2) {

  $degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));

  $distance = $degrees * 111.13384;
  return round($distance, $decimals);
}

function Researcher($datas, $pdo) {

  $age = explode(',',$datas['age']);
  $range = explode(',', $datas['range']);
  $area = $datas['area'];
  $extracted = $datas['extracted'];
  if($area){
    $area = json_decode($area);
  }
  $pop = explode(',', $datas['pop']);
  $tagsnm = [];
  $tagsnm = explode(',', $datas['tags']);


  try {
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

    $or = "members.sexe IN (";
    switch($sexnor)  {
      case array('male','hetero'):
      $or .= "'female'"; break;
      case array('female','hetero'):
      $or .= "'male'"; break;
      case array('other','hetero'):
      $or .= "'female','male','other'"; break;
      case array('male','bi'):
      $or .= "'female','male','other'"; break;
      case array('female','bi'):
      $or .= "'female','male','other'"; break;
      case array('other','bi'):
      $or .= "'female','male','other'"; break;
      case array('male','homo'):
      $or .= "'male'"; break;
      case array('female','homo'):
      $or .= "'female'"; break;
      case array('other','homo'):
      $or .= "'female','male','other'"; break;
    }
    $or .= ")";

    $reqsql = "SELECT
    members.login,
    members.id_user,
    members.prenom,
    members.nom,
    TIMESTAMPDIFF(YEAR, members.birthday, NOW()) AS age,
    members.sexe,
    members.oriented,
    members.profil_pict,
    GROUP_CONCAT(tags_members.id_tag) AS tags,
    checkblock(". $user['0']['id_user'].", members.id_user) AS blocki,
    get_distance_km(".$pos.",(SELECT latitude FROM geoloc WHERE geoloc.id_user = members.id_user ORDER BY timeof DESC LIMIT 1),
    (SELECT longitude FROM geoloc WHERE geoloc.id_user = members.id_user ORDER BY timeof DESC LIMIT 1)) AS dist, (SELECT COUNT(tags_members.id_tag) FROM tags_members WHERE tags_members.id_members = members.id_user AND tags_members.id_tag IN ('". str_replace(',',"','",$user['0']['nb'])."')) AS nb
  FROM
    members, geoloc
    LEFT JOIN tags_members ON (id_user=tags_members.id_members)
  WHERE ";
    $reqsql .= $or;
    $reqsql .= ' AND members.id_user != '.$user['0']['id_user'].' AND geoloc.id_user = members.id_user ';
    $reqsql .= ' GROUP BY id_user
     HAVING age BETWEEN '.$age['0'].' AND '.$age['1'].'
     AND dist BETWEEN 0 AND '.$range['0'].' AND blocki = 0';

    if(isset($tlist)) {
      foreach ($tlist as $elem) {
        $reqsql .= " AND tags LIKE '%" . $elem ."%'";
      }
    }
    $reqsql .= ' LIMIT '.$extracted.', 5';
    $sql = $pdo->query($reqsql);
    $resultaa = $sql->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {

    print "Error!: DATABASE searching-> " . $e->getMessage() . " FAILED TO search<br/>";
    die();
  }

  if($resultaa){
    foreach($resultaa as $key =>$array) {
      if ($array['profil_pict'] == "#" || !file_exists($array['profil_pict'])) {
        $resultaa[$key]['profil_pict'] = 'app/css/image/Photo-non-disponible.png';
      }
    }
  }

  $idlist = [];
  foreach ($resultaa as $key=>$elem) {
    $idlist[$key] = $elem['id_user'];
  }

  $res = [];
  $res['online'] = getOnlineMembers($idlist, $pdo);
  $res['taglist']= isset($tlist) ? $tlist : '';
  $res['req']= $reqsql;
  $res['result']= $resultaa;
  $res['extracted']= count($resultaa);
  return($res);
}

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

      $sql = $pdo->prepare("SELECT id_user, timeof, IF(timeof > (UNIX_TIMESTAMP() - 900), 'yes', 'no') AS connected FROM ping WHERE id_user = ?");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['logs']= $sql->fetch(PDO::FETCH_ASSOC);

      $sql = $pdo->prepare("SELECT latitude, longitude FROM geoloc WHERE geoloc.id_user = ? AND type = 'auto' ORDER BY timeof DESC LIMIT 1");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['geoloc']= $sql->fetch(PDO::FETCH_ASSOC);
      $result['geoloc']['info'] = getAddrWithCoord($result['geoloc']['latitude'],$result['geoloc']['longitude']);

      $sql = $pdo->prepare("SELECT latitude, longitude FROM geoloc WHERE geoloc.id_user = ? ORDER BY timeof DESC LIMIT 1");
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->execute();
      $current= $sql->fetch(PDO::FETCH_ASSOC);

      $sql = $pdo->prepare("SELECT DISTINCT (SELECT id_like FROM `likes` WHERE id_from = ? AND id_to = ? LIMIT 1) AS toyou , (SELECT id_like FROM `likes` WHERE id_from = ? AND id_to = ? LIMIT 1) AS fromyou FROM likes");
      $sql->bindParam(1, $id, PDO::PARAM_INT);
      $sql->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(4, $id, PDO::PARAM_INT);
      $sql->execute();
      $result['likes'] = $sql->fetch(PDO::FETCH_ASSOC);

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
      $sql = $pdo->prepare("INSERT INTO visite (id_from, id_to, timeof) VALUES (? ,? , UNIX_TIMESTAMP() )");
      $sql->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
      $sql->bindParam(2, $id, PDO::PARAM_INT);
      $sql->execute();
    } catch (PDOException $e) {
      print "error = ".$e." in lookathim stage= add visite";
      die();
    }

  }
  return $result;
}

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
      }
      else if(!empty($result)) {
        $sql = $pdo->prepare("DELETE FROM likes WHERE id_like = ?");
        $sql->bindParam(1, $result['id_like'],PDO::PARAM_INT);
        $res['status'] = $sql->execute();
        $res['action'] = 'delete';
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

      $tot['already']= $result;
      $tot['new'] = $res;
      $tot['status'] = "OK";
    } catch (PDOException $e) {
      print "error ". $e ." on likevent";
      $tot['status'] = "fail";
      die();
    }
  }
  return($tot);
}

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

 ?>
