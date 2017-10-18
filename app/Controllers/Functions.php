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
 ?>
