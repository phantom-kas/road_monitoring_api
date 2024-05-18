<?php
 function servError($msg = 'unknown error'){
  if($msg === null){
    $msg =  'unknown error';
  }
        return array('message' => $msg, 'status' => 'error');
       
    };

    function servInfo($msg = 'Message'){
      if($msg === null){
        $msg =  'unknown error';
      }
            return array('message' => $msg, 'status' => 'info');
           
        };

     function servSus($msg = 'success'){
        return array('message' => $msg, 'status' => 'success');
    };
    function servWarn($msg = 'warning'){
      return array('message' => $msg, 'status' => 'warning');
  };

    function outPut($svr){
      echo json_encode($svr);
    }

    function postVar($pp){
      return !isset($pp)? false :  (empty($pp)? false:  $pp);
    }
    
    
    function checkvalue($pp){
      return !isset($pp)? false :  (empty($pp)? false:  $pp);
    }

    function dd() {
      foreach (func_get_args() as $arg){
          var_dump($arg);
      }
      exit;
  }

  function getDataInput() {
     $data =  json_decode(file_get_contents('php://input'), true) ?? $_POST;
     if($data == NULL){
      return false;
     }
     return $data;
  }

  function unAuth($die = true,$msg = 'Not signed in'):void{
    header("HTTP/1.1 401 Unauthorized");
    
    if($die)
    {
      outPut(servError($msg));
      die();
    }
   
  }

  function forbid($die = true,$msg = 'Access denied'){
    header("HTTP/1.1 403 Forbidden");
    
    if($die)
    {
      outPut(servError($msg));
      die();
    }
   return true;
  }

  function bad_req($die = true,$msg = 'No id provided'){
    header("HTTP/1.1 400 Bad Request");
    
    if($die)
    {
      outPut(servError($msg));
      die();
    }
   return true;
  }

  function payment_required($die = true,$msg = 'Payment is overdue.'){
    header("HTTP/1.1 402 Payment Required");
    if($die)
    {
      outPut(servError($msg));
      die();
    }
   return true;
  }


  function notFound($die = true,$msg = 'Resource not found'){
    header("HTTP/1.1 404 Not found");
    
    if($die)
    {
      outPut(servError($msg));
      die();
    }
   return true;
  }


  function checkSignedIn($die = true) {
    if(!isset($_SESSION['uid'])){
      unAuth($die);
    }
    else{
      return servSus('Already sign in');
    }
  }

  function requireParam($p){
    if(!isset($p)){
      outPut(servError('No parameter found'));
      die();
    }
    if($p == null){
      outPut(servError('No parameter found'));
      die();
    }
    return $p;
  }

  function pathUrl($dir = __DIR__){
    $root = "";
    $dir = str_replace('\\', '/', realpath($dir));
    //HTTPS or HTTP
    $root .= !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    //HOST
    $root .= '://' . $_SERVER['HTTP_HOST'];
    //ALIAS
    if(!empty($_SERVER['CONTEXT_PREFIX'])) {
        $root .= $_SERVER['CONTEXT_PREFIX'];
        $root .= substr($dir, strlen($_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ]));
    } else {
        $root .= substr($dir, strlen($_SERVER[ 'DOCUMENT_ROOT' ]));
    }
    $root .= '/';
    return $root;
}
function getBaseUrl($dir = __DIR__){
  $root = "";
  $dir = str_replace('\\', '/', realpath($dir));
  //HTTPS or HTTP
  $root .= !empty($_SERVER['HTTPS']) ? 'https' : 'http';
  //HOST
  $root .= '://' . $_SERVER['HTTP_HOST'];
  $root .= '/';
  return $root;
}

function getDateTime(){
  return gmdate('Y-m-d H:i:s');
}

function getDateNow(){
  return gmdate('Y-m-d');
}


function echoTimeZone(){
  //date_default_timezone_set("Africa/Accra");
  echo date_default_timezone_get();
  //echo getDateTime();
}
function outPutData($data,$die = true,$msg = 'success'){
  $output = servSus($msg);
  $output['data'] = $data;
  if(!$die){
    return ;
  }
      output($output);
      die();
}

function outPutDataWithImgUrlRoot($data,$die = true,$msg = 'success'){
  $output = servSus($msg);
  $output['data'] = $data;
  $output['profile_img_url'] = pathUrl().IMG_fd."profileimages/";
  if(!$die){
    return ;
  }
      output($output);
      die();
}

function outPutDataWithUploadDir($data,$die = true,$msg = 'success'){
  $output = servSus($msg);
  $output['data'] = $data;
  $output['upload_dir'] = pathUrl()."uploads/";
  if(!$die){
    return ;
  }
      output($output);
      die();
}




function outPutDataWithImgUrlRootAndBaseUrl($data,$die = true,$msg = 'success'){
  $output = servSus($msg);
  $output['data'] = $data;
  $output['base_url'] = getBaseUrl();
  $output['profile_img_url'] = pathUrl().IMG_fd."profileimages/";
  if(!$die){
    return ;
  }
      output($output);
      die();
}

function outPutDataWithResRootAndBaseUrl($data,$die = true,$msg = 'success'){
  $output = servSus($msg);
  $output['data'] = $data;
  $output['base_url'] = getBaseUrl();
  $output['profile_img_url'] = pathUrl().RESOURCE_PATH;
  if(!$die){
    return ;
  }
      output($output);
      die();
}


function checkCheckGetParam($param,$die = true,$msg = ''){
  if(!isset($_GET[$param])  ){
    if( $die){
       bad_req($die);
    }
   return false;
  }
  return $_GET[$param];
}
function checkCheckPostParam($param,$die = true,$msg = ''){
  if(!isset($_POST[$param]) and $die ){
    bad_req($die);
  }
}

function getPayementInfo(){

  if(!isset($_GET['reference'])){
    outPut(servError('No reference found'));
    die();
  }
  if(empty($_GET['reference'])){
    outPut(servError('No reference found'));
    die();
  }

  if($_GET['reference'] == null){
    outPut(servError('No reference found'));
    die();
  }
    $curl = curl_init();
      
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/transaction/verify/".rawurlencode($_GET['reference']),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".PAYSTACK_KEY,
        "Cache-Control: no-cache",
      ),
    ));
    // sk_live_118fa6ee7090edcbbf3d2bb944cffb2df8095a8d : bs
    // sk_test_dbf13de80a40f474923a40463beb19febe5f7e6e : kas
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      
      outPut(servError("cURL Error #:" . $err));
      die();
    } else {
      $ress = json_decode($response , true); 
    }
   // dd($ress);die();
   if(!isset($ress['data'])){
    outPut(['alerts' =>[ servError('payment Error')],servSus() ]);
    die();
    }
    if( $ress['data']['status'] !== 'success'  ){
      outPut(servError('payment Error'));
      die();
    }
    $data = $ress['data'] ?? null;

    return $data;
    
}
function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[random_int(0, $charactersLength - 1)];
  }
  return $randomString;
}
function addToDate($date , $str = "num days"){
  $date=date_create($date);
  date_add($date,date_interval_create_from_date_string($str));
  return date_format($date,"Y-m-d");
}


function padNumber($number , $pad = 9){
  $number; // Replace with your single digit

$sixDigitNumber = str_pad($number,  $pad, '0', STR_PAD_LEFT);
return $sixDigitNumber; 

}
function base64_encode_url( $str )
{
    return urlencode( base64_encode( $str ) );
}


function getCid($cc){
 
    $c = explode('__', $cc);

      $idata['nationality'] = $c[1];
      $idata['country'] = $c[1];
      $idata['country_id'] = $c[0];
  
      return $idata;
}
?>