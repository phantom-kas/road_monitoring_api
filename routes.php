<?php
$routes = [
  "/user/signIn" => "models/user.php",
  "/user/signOut" => "models/user.php"
];

//returns the root url
$url = str_replace(['/index.php','index.php'],'', strtok($_SERVER['REQUEST_URI'],'?'));



$self =str_replace('/index.php','', $_SERVER['PHP_SELF']);
$route = str_replace($self,'',$url);
$route = str_replace('//','/',$route);
$countSlash = substr_count($route,'/');

$class = $countSlash >= 1 ? explode('/',$route)[1] :false;
$class = strtolower($class);

$action =  $countSlash >= 2 ? explode('/',$route)[2] :false;
$action = strtolower($action);


if($class !== false){
  if(file_exists('./models/'.$class.'.php'))
  {require_once './models/'.$class.'.php';}
  else{
    outPut(servError('Unkown request'));
    die();
  }
}
?>