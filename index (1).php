<?php
$sub = '';
require_once './headers.php';
require_once './env.php';
require_once './fuctions.php';


require_once './classes/mysqli.php';
require_once './session.php';

require_once './routes.php';
require_once './classes/router.php';




$router = new Router(null);


// $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// outPut($url)

// echo $_POST["user_name"].'sasad';
// outPut($_SERVER);
// die();
// dd($_SERVER);
die();
switch ($action){
  case 'signIn':{
   $User->signIn();
   break;
  }

  case 'signOut':{
    $User->signOut();
    break;
   }



  case 'create-post':{
   checkSignedIn();
    require_once './uploader.php';
    $fname = time();
    $URL = pathUrl().IMG_fd.$fname.'.png';
    $_POST['flyer_url'] = $URL;  
   if(upload('file-input',$fname))
   {
   $Post->createPost();
  };
    break;
   }

   case 'update-post':{
    if($countSlash < 3){
      outPut(servError('No id specified'));
      die();
    }
    $Post->update(requireParam(explode('/',$route)[3]));
    break;
   }

   case 'update-post-column':{
    if($countSlash < 4){
      outPut(servError('No id specified'));
      die();
    }
    $Post->updateColunm(requireParam(explode('/',$route)[3]),requireParam(explode('/',$route)[4]));
    break;
   }

   case 'delete':{
    outPut($Post->delete(requireParam(explode('/',$route)[3])));
    break;
   }

   case 'getpost':{
    output($Post->getAll($_GET['start'] ?? 0, $_GET['length'] ?? 50));
    break;
   }
     case 'getSinglePost':{
    checkSignedIn();
    output($Post->getBById(explode('/',$route)[3]));
    break;
   }

  case 'update':{
    if(file_exists($_FILES['file-input']['tmp_name'])){
    require_once './uploader.php';
    $fname = time();
    $URL = pathUrl().IMG_fd.$fname.'.png';
    $_POST['flyer_url'] = $URL;  
    upload('file-input',$fname);
    }
    output(servError($Post->smartUpdate($Post->table,['posted_by', 'event_name', 'theme', 'date', 'venue', 'additional_info', 'flyer_url', 'street_add', 'state', 'city','zip_code', 'zoom_id', 'zoom_pas','time', 'zoom_link', 'fb_live', 'youtube','host', 'google', 'other'], $_POST, explode('/',$route)[3])->affectedRows().' items updated successfully'));
    break;
   }

   case 'checkSignedIn':{
    outPut(checkSignedIn());
    break;
   }
   case 'checkToken':{
    $User->checkToken();
    break;
   }

   case 'getNewToken':{
    $User->generateNewAccessToken();
    break;
   }


   case 'updateUserInfo':{
    $User->updateInfo();
    break;
   }

  }
?>
