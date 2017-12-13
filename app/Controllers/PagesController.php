<?php
namespace App\Controllers;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PagesController extends Controller{
  public function home(Request $request, Response $response) {
    if(empty($_SESSION['loggued_as'])) {
    $this->render($response, 'pages/home.twig');
    }
    else {
      $data = array('age' => '18,100' , 'range' => '25' , 'pop' => '0,100' , 'tags' => '', 'area' =>'', 'extracted' => '0');
      include_once ('Functions.php');
      $res = Researcher($data, $this->pdo);
      var_dump(array('profils'=>$res['result']));
      $this->render($response, 'pages/preview.twig', array('profils'=>$res['result']));
    }
  }

  public function getAccount(Request $request, Response $response) {

    if(!empty($_SESSION['loggued_as']))
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $info = [];
      $info['profil'] = getAccountInfo($_SESSION['loggued_as'], $pdo);
      $_SESSION['id'] = $info['profil']['id_user'];
      $info['geo'] = getAddrWithCoord($info['profil']['latitude'], $info['profil']['longitude']);
      $this->render($response, 'pages/account.twig', $info);
    }
    else {
      $this->render($response, 'pages/home.twig');
    }
  }

  public function UpdateProfil(Request $request, Response $response) {


    if(!empty($_SESSION['loggued_as']))
    {
      $pdo = $this->pdo;
      include_once('Functions.php');

      $errors = [];
      $Validator = new Validator();
      if($Validator->validate('email',$request->getParam('email')) != true) {
      $errors['email'] = $Validator->returner('error');}
      if($Validator->validate('content',$request->getParam('nom')) != true) {
      $errors['nom'] = $Validator->returner('error');}
      if($Validator->validate('content',$request->getParam('prenom')) != true) {
      $errors['prenom'] = $Validator->returner('error');}
      $value = $request->getParsedBody();

      if(empty($errors) && !empty($value)) {
          updateAccountInfo($_SESSION['loggued_as'],$value,$pdo);
         $info['profil'] = getAccountInfo($_SESSION['loggued_as'], $pdo);
      }
      else {
        $info['profil'] = getAccountInfo($_SESSION['loggued_as'], $pdo);
      }
      $this->render($response, 'pages/account.twig', $info);
    }
    else {
      return $this->redirect($response, 'home');
    }
  }

  public function postAccount(Request $request, Response $response) {

    $pdo = $this->pdo;
    include_once ('Functions.php');
    $param = $request->getParams();
    if(!empty($param['name']) && !empty($param['password']))
    {
      $result = checkForAccount($param['name'], $param['password'], $pdo);

      if($result['name'] == True && $result['password'] == True )
      {
        $_SESSION['loggued_as'] = $param['name'];
        $_SESSION['Alert'] = "Connexion Succeeded";
        updateLocation($param,$pdo);
          return $this->redirect($response ,'profil');
      }
      else if($result['name'] != True)
      {
        $_SESSION['loggued_as'] = "";
        $_SESSION['id'] = "";
        $_SESSION['flash'] = array('login' => "Login not Found, Please Sign up");
        return $this->redirect($response ,'home');
      }
      else if($result['name'] == True && $result['password'] == False)
      {
        $_SESSION['loggued_as'] = "";
        $_SESSION['id'] = "";
        $_SESSION['flash'] = array('pass'=> "Wrong password");
        return $this->redirect($response ,'home');
      }
    }
    else {
      $_SESSION['loggued_as'] = "";
      $_SESSION['id'] = "";
      $this->render($response, 'pages/home.twig');
    }
  }

  public function getMember(Request $request, Response $response , $info) {
      if(isset($info))
    {
      $_SESSION['Alert'] = $info['Alert'] ? $info['Alert'] : [];
    }
    $this->render($response, 'pages/signUp.twig');
  }

  public function postMember(Request $request, Response $response) {
    $pdo = $this->pdo;
    include_once ('Functions.php');
    $params = $request->getParams();
    $errors = [];

    $Validator = new Validator();
    if($Validator->validate('email',$request->getParam('email')) != true) {
    $errors['email'] = $Validator->returner('error');}
    if($Validator->validate('pseudo',$request->getParam('pseudo','new')) != true) {
    $errors['pseudo'] = $Validator->returner('error');}
    if($Validator->validate('content',$request->getParam('nom')) != true) {
    $errors['nom'] = $Validator->returner('error');}
    if($Validator->validate('content',$request->getParam('prenom')) != true) {
    $errors['prenom'] = $Validator->returner('error');}
    if($Validator->validate('password',$request->getParam('password')) != true) {
    $errors['password'] = $Validator->returner('error');}
    if($Validator->validate('content',$request->getParam('answer')) != true) {
    $errors['answer'] = $Validator->returner('error');}
    if($Validator->validate('birthday',$request->getParam('birthday')) != true) {
    $errors['birthday'] = $Validator->returner('error');}

    if(empty($errors))
    {
      $result = createNewAccount($params, $pdo);
      if ($result === true) {
        $_SESSION['loggued_as'] = $request->getParam('pseudo');
        $this->flash('Account created, mail have been sent for activation' ,'success');
        $info['profil'] = getAccountInfo($_SESSION['loggued_as'], $pdo);
        $_SESSION['id'] = $info['profil']['id_user'];
      return  $this->redirect($response, 'profil');
      }
      else {
        $this->flash($result ,'error');
        return $this->redirect($response, 'signUp');
      }
    }
    else {
      $this->flash($errors, 'error');
      return $this->redirect($response, 'signUp');
    }
  }

  public function logout(Request $request, Response $response) {
    if(!empty($_SESSION['loggued_as'])) {
      $_SESSION['loggued_as'] = "";
      $_SESSION['id'] = "";
    }
    return $this->redirect($response, 'home');
  }

  public function getContact(Request $request, Response $response) {
      return $this->render($response, 'pages/contact.twig');
  }

  public function postContact(Request $request, Response $response) {
    $errors = [];

    $Validator = new Validator();
    if($Validator->validate('email',$request->getParam('email')) != true) {
    $errors['email'] = $Validator->returner('error');}
    if($Validator->validate('pseudo',$request->getParam('pseudo')) != true) {
    $errors['pseudo'] = $Validator->returner('error');}
    if($Validator->validate('content',$request->getParam('content')) != true) {
    $errors['content'] = $Validator->returner('error');}


    if (empty($errors)) {
      $message = \Swift_Message::newInstance('Message de contact')
        ->setFrom([$request->getParam('email') => $request->getParam('pseudo')])
        ->setTo('vincent_balart@hotmail.fr')
        ->setBody("Ceci est une copie du message que vous avez envoyé : {$request->getParam('email')} have send {$request->getParam('content')}");
      $this->mailer->send($message);
      $this->flash('Votre message a bien été envoyé');
    } else {
      $this->flash($errors, 'error');
    }
    return $this->redirect($response, 'contact');
  }

  public function setAsProfil(Request $request, Response $response) {
    if($_POST && $_POST['profil_pict'])
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      print updatePict($_POST, $pdo);
    }
  }

  public function updateAccountPict(Request $request, Response $response) {
      if($_POST && $_POST['newone'] && isset($_POST['old']))
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $ret =  (AddOrChangePicturePhp($_POST, $pdo));
      print ($ret);
    }
  }

  public function getTagInfo(Request $request, Response $response) {
    $subject = $request->getParam('subject');
    if(isset($subject) && $subject = 'tagmbt')
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $ret = getTags($_SESSION['loggued_as'],$pdo);
      print($ret);
    }
  }

  public function updateTagInfo(Request $request, Response $response) {
    $subject = $request->getParam('subject');
    $active = json_decode($request->getParam('activeTag'), true);
    $error = [];
    $x = 0;
    foreach($active as $key => $value)
    {
      $active[$key]['id_tag'] = str_replace('tagitem','',$value['id_tag']);
    }
    if(isset($subject) && $subject = 'tagupdt' && empty($error))
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $ret = updateTags($active, $pdo);
      print($ret);
    }

  }

  public function getSearch(Request $request, Response $response) {
    if(!empty($_SESSION['loggued_as']))
    {
      $this->render($response, 'pages/search.twig');
    }
    else {
      $this->render($response, 'pages/home.twig');
    }
  }

  public function postSearch(Request $request, Response $response) {
    $datas = $request->getParams();
    if($datas){
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $ret = Researcher($datas, $pdo);
      $ret['paramenter'] = $datas;
      print json_encode($ret);
    }
  }

  public function updatePosition(Request $request, Response $reponse) {
     $data = $request->getParams();
    if(!empty($data['input']) || (!empty($data['lng']) && !empty($data['lat']))) {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $ret = updateLocation($data,$pdo);
      var_dump( $ret);
    }
  }

  public function lookat(Request $request, Response $response, $info) {
    if(!empty($_SESSION['loggued_as'])) {
      $inf = $request->getParams();
      include_once ('Functions.php');
      $pdo = $this->pdo;

      $res = lookathim($info['name'], $pdo);
      if($res){
          $res['logs']['totaldate'] = date("m.d.y",$res['logs']['timeof']);
        $diff = abs(time() - $res['logs']['timeof']);
        $tmp = $diff;
        $res['logs']['sec'] = $tmp % 60 ;
        $tmp = floor(($tmp - $res['logs']['sec'])/60);
        $res['logs']['min'] = $tmp % 60;
        $tmp = floor(($tmp - $res['logs']['min'])/60);
        $res['logs']['hour'] = $tmp % 24;
        $tmp = floor(($tmp - $res['logs']['hour'])/24);
        $res['logs']['day'] = $tmp % 30;
        $tmp = floor(($tmp - $res['logs']['day'])/30);
        $res['logs']['month'] =  $tmp % 12;
        $tmp = floor(($tmp - $res['logs']['month'])/12);
        $res['logs']['year'] = $tmp % 9999;
        $this->render($response, 'pages/lookat.twig', $res);
      }
      else {
          return $this->redirect($response, 'home');
      }
    } else {
      return $this->redirect($response, 'home');
    }
  }

  public function reportUser(Request $request, Response $response) {
    if(!empty($_SESSION['loggued_as']))
    {
    $param = $request->getParams();
      if(isset($param['action']) && isset($param['type']) && isset($param['content']) && isset($param['to']) && $param['action'] == "report") {
        $pdo = $this->pdo;
        include_once ('Functions.php');
        $res = reportevent($_SESSION['loggued_as'], $param, $pdo);
        print ($res);
      }
    }
  }

  public function likeUser(Request $request, Response $response) {
    if(!empty($_SESSION['loggued_as']))
    {
    $param = $request->getParams();
      if(isset($param['action']) && isset($param['to']) && $param['action'] == "like") {
        $pdo = $this->pdo;
        include_once ('Functions.php');
        $res = likevent($_SESSION['id'], $param['to'], $pdo);
        print json_encode($res);
      }
    }
  }

  public function blockUser(Request $request, Response $response) {
    if(!empty($_SESSION['loggued_as']) && !empty($_SESSION['id']))
    {
    $param = $request->getParams();
      if(isset($param['action']) && isset($param['to']) && $param['action'] == "block") {
        $pdo = $this->pdo;
        include_once ('Functions.php');
        $res = blockevent($_SESSION['id'], $param['to'], $pdo);
        print json_encode($res);
      }
    }
  }

  public function get_block_list(Request $request, Response $response) {
    $param = $request->getParams();
    $res =[];
    if($_SESSION['loggued_as'] && $param['subject'] == 'blklst') {
      $pdo = $this->pdo;
        include_once ('Functions.php');
      $res = getblocklist($pdo);
    }
    print json_encode($res);
  }

  public function removeblock(Request $request, Response $response) {
    $param = $request->getParams();
    $res = [];
    if($_SESSION['loggued_as']  && $param['subject'] == 'blkrmv' && !empty($param['target'])) {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $res = removeblocks($param['target'],$pdo);
    }
    else{
      $res['STATUS'] = 'error';
    }
    print (json_encode($res));
  }

  public function getmessenger(Request $request, Response $response) {

    if(!empty($_SESSION['loggued_as']) && !empty($_SESSION['id'])) {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $res = GetMsgInterface($pdo);
      if($res) {
        $this->render($response, 'pages/chat.twig', $res);
      }
      else {
      return $this->redirect($response, 'home');
      }
    }
    else
      $this->render($response, 'pages/home.twig');
  }

  public function postmessenger(Request $request, Response $response) {
    header('Content-type: application/json');
    $info = $request->getParams();
    if(!empty($_SESSION['loggued_as']) && !empty($info['id']) && isset($info['content']))
    {
      $ret = [];
      if(empty($info['content'])) {
        $ret['status'] = 'Message is empty';
      }
      else {
      $pdo = $this->pdo;
      $ret['status'] = 'OK';
        include_once ('Functions.php');
        $ret['content'] = PostNewMsg($info['id'],$info['content'],$pdo);
      }
      print json_encode($ret);
    }
  }

  public function getMsgList(Request $request, Response $response) {
    header("Content-type: application/json");
    $data = $request->getParams();
    $ret = [];
    if (!empty($_SESSION['loggued_as']) && isset($data['nb']) && !empty($data['id']))
    {
      include_once('Functions.php');
      $ret = RedeemMsg($data['id'], $data['nb'], $this->pdo);
      return json_encode($ret);
    }
  }

  public function updateNotif(Request $request, Response $response) {
    $data = $request->getParams();
    if(!empty($_SESSION['loggued_as']) && !empty($_SESSION['id']))
    {
      $ret = [];
      if(!empty($data['id_notif'])) {
          include_once ('Functions.php');
        $ret['status'] = UpdateNotifStatus($data['id_notif'], $this->pdo);
      }
      else {
        $ret['status'] = 'ERROR';
      }
      print json_encode($ret);
    }
  }

  public function getNotifList(Request $request, Response $response) {
    header('Content-type: application/json');
    $data = $request->getParams();
    if ($data['action'] == 'notif' && !empty($data['type']))
    {
      if (!empty($_SESSION['loggued_as']))
      {
        include_once('Functions.php');
        if (isset($data['nb']))
          $tab = RedeemNotifContent($this->pdo, $data['nb'], $data['type'], 1);
        else {
          $tab = RedeemNotifContent($this->pdo, NULL, $data['type'], 0);
        }
        return json_encode($tab);
      }
    }
  }

  public function Auto_notif(Request $request, Response $response) {
    header("Content-type: application/json");
    $data = $request->getParams();
    if(!empty($_SESSION['loggued_as'])) {
      include_once('Functions.php');
      $ret = RNewNotif($data['id'], $this->pdo);
      print json_encode($ret);
    }
    else {
      print "Error";
    }
  }

  public function getRecover(Request $request, Response $response) {
    $this->render($response, 'pages/recover.twig');
  }

  public function postRecover(Request $request, Response $response) {
    $data = $request->getParams();
    if(!empty($data['login'] && !empty($data['secret']))) {
      include_once ('Functions.php');
      $ret =  recoverPassword($data['login'], $data['secret'], $this->pdo);
      if(!empty($ret['status']) && $ret['status'] == 'OK') {
        $prekey = microtime() . 'recover';
        $key = hash('whirlpool', $prekey);
        $url = $_SERVER['HTTP_HOST'].str_replace('index.php','Reset',$_SERVER['PHP_SELF']).'/'.$key;
        $_SESSION['key'] = $key;
        $_SESSION['recover'] = $data['login'];
        $message = \Swift_Message::newInstance('Reinitialiser Password')
          ->setFrom(['password_recover@matcha.fr' => 'A-bra-ca-da-matcha'])
          ->setTo([$ret['data']['email'] => $data['login']])
          ->setBody("Hello {$data['login']} click sur ce lien => http://{$url} <= pour changer ton mot de pass.");
        $result = $this->mailer->send($message);
        $_SESSION['flash'] = array('status' => 'Message envoyer');
        return $this->redirect($response, 'home');
      }
    }
    else {
      $_SESSION['flash'] = array('status' => 'Secret erroner');
      return $this->redirect($response, 'Recover');
    }
  }

  public function getReset(Request $request, Response $response, $key) {
    var_dump($key);
    if(!empty($_SESSION['key']) && $key['key'] == $_SESSION['key'])
    {
      $key = hash('whirlpool', $key['key'].time());
      $_SESSION['key'] = $key;
      $this->render($response, 'pages/Reset.twig', array('key' => $key));
    }
    else {
      $_SESSION['flash'] = array('error' => 'Clé invalid try again');
      $_SESSION['key'] = '';
      $_SESSION['recover'] = '';
      return $this->redirect($response, 'Recover');
    }
  }

  public function postReset(Request $request, Response $response) {
    $data = $request->getParams();
    if( !empty($data['key']) && $data['key'] == $_SESSION['key'] && !empty($_SESSION['recover'])) {
      $_SESSION['key'] = '';
      include_once ('Functions.php');
      $ret = resetPassword($_SESSION['recover'], $data['password'], $this->pdo);
      $_SESSION['recover'] = '';
      if($ret) {
          $_SESSION['flash'] = array('status' => 'Password Reset Success');
        }
      else {
          $_SESSION['flash'] = array('status' => 'Password Reset FAIL contact the webmaster');
        }
      return $this->redirect($response, 'home');
    }
    else {
        $_SESSION['key'] = '';
        $_SESSION['recover'] = '';
        return $this->redirect($response, 'Recover');
    }

  }

  public function setNewToOld(Request $request, Response $response) {
    $data = $request->getParams();
    if ($data['action'] != 'newold' && !isset($data['notif']))
    {
      echo "error";
    } else {
      include_once('Functions.php');
      $ret = UpdateNotifStatus($data['notif'], $this->pdo);
      if ($ret['status'] != "OK")
        echo $ret['status'];
      else {
        echo "ok";
      }
    }
  }
}
?>
