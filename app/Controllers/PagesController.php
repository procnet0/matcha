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
      $this->render($response, 'pages/preview.twig');
    }
  }

  public function getAccount(Request $request, Response $response) {

    if(!empty($_SESSION['loggued_as']))
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $info = [];
      $info['profil'] = getAccountInfo($_SESSION['loggued_as'], $pdo);
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
      $errors['email'] = 'Your email is not valid.';}
      if($Validator->validate('content',$request->getParam('nom')) != true) {
      $errors['nom'] = 'Your lastname is empty.';}
      if($Validator->validate('content',$request->getParam('prenom')) != true) {
      $errors['prenom'] = 'Your firstname is empty.';}
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
        $info = [];
        $info['profil'] = getAccountInfo($_SESSION['loggued_as'], $pdo);
        $_SESSION['id'] = $info['profil']['id_user'];
        $info['geo'] = getAddrWithCoord($info['profil']['latitude'], $info['profil']['longitude']);
        updateLocation($param,$pdo);
        $this->render($response, 'pages/account.twig', $info);
      }
      else if($result['name'] == True && $result['password'] == False)
      {
        $_SESSION['loggued_as'] = "";
        $_SESSION['id'] = "";
        $_SESSION['Alert'] = "Wrong password";
        $this->render($response, 'pages/home.twig');
      }
      else if($result['name'] != True)
      {
        $_SESSION['loggued_as'] = "";
        $_SESSION['id'] = "";
        $info = "Login not Found, Please Sign up";
        $this->render($response, 'pages/signUp.twig', array('login' => filter_var($param['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)));
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
    $errors['email'] = 'Your email is not valid.';}
    if($Validator->validate('pseudo',$request->getParam('pseudo','new')) != true) {
    $errors['pseudo'] = 'Pseudo not valid or already used.';}
    if($Validator->validate('content',$request->getParam('nom')) != true) {
    $errors['nom'] = 'Your lastname is empty.';}
    if($Validator->validate('content',$request->getParam('prenom')) != true) {
    $errors['prenom'] = 'Your firstname is empty.';}
    if($Validator->validate('password',$request->getParam('password')) != true) {
    $errors['password'] = 'Your password is to short';}
    if($Validator->validate('content',$request->getParam('answer')) != true) {
    $errors['answer'] = 'Your secret answer is empty.';}
    if($Validator->validate('birthday',$request->getParam('birthday')) != true) {
    $errors['birthday'] = 'Your birthday is not known by abracadamatcha what kind of creature are you?';}

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
      $this->render($response, 'pages/signUp.twig');
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
    $errors['email'] = 'Your email is not valid.';}
    if($Validator->validate('pseudo',$request->getParam('pseudo')) != true) {
    $errors['pseudo'] = 'Login not found please create an account before contacting.';}
    if($Validator->validate('content',$request->getParam('content')) != true) {
    $errors['content'] = 'Something is wrong with your message.';}


    if (empty($errors)) {
      $message = \Swift_Message::newInstance('Message de contact')
        ->setFrom([$request->getParam('email') => $request->getParam('pseudo')])
        ->setTo('vincent.balart@hotmail.fr')
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
    $inactive = json_decode($request->getParam('inactiveTag'), true);
    $error = [];
    $x = 0;
    foreach($active as $key => $value)
    {
      $active[$key]['id_tag'] = str_replace('tagitem','',$value['id_tag']);
      if($active[$key]['id_tag'] < 1 || $active[$key]['id_tag'] > 5)
      {
        $error[$x] = 'id error -> '.$active[$key]['id_tag'].'//  tag name was ->'.$active[$key]['name'];
      }
    }
    foreach($inactive as $key => $value)
    {
      $inactive[$key]['id_tag'] = str_replace('tagitem','',$value['id_tag']);
      if($inactive[$key]['id_tag'] < 1 || $inactive[$key]['id_tag'] > 5)
      {
        $error[$x] = 'id error -> '.$inactive[$key]['id_tag'].'// tag name was ->'.$inactive[$key]['name'];
      }
    }
    if(isset($subject) && $subject = 'tagupdt' && empty($error))
    {
      $pdo = $this->pdo;
      include_once ('Functions.php');
      $ret = updateTags($active, $inactive, $pdo);
      print($ret);
    }
    else {
      $ret = json_encode($error);
      print $ret;
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
    }
    else {
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
}
?>
