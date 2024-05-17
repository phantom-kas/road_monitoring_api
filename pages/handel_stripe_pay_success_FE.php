<?php
$sub = '../';
require_once "../classes/mysqli.php";
require_once "../env.php";
require_once "../fuctions.php";
require_once "../models/res.php";


$M = new  Db();

if(!isset($_GET['tkn']) || !isset($_GET['provider_session_id'])){
 // dd($_GET);die();
  echo "error";//.$_GET['tkn'].$_GET['provider_session_id'];
  exit();      
}

$token = $_GET['tkn'];
    try {
      //code...
     
     
      $payload =  JWT::decode( $token , 'ACCESSSSSS_SSSSECRETE_KEYSSSS', ['HS256'], 'a');
    } catch (\Throwable $th) {
      //throw $th;
      echo '2';
      echo   $th->getMessage();

      
     exit();
    }

 //  outPut($payload);die();

    $Res->idata = json_decode(json_encode($payload->dd) ,true);
    $Res->pid = $payload->dd->ppxyz->pid;
   // outPut($payload ); die();
    $Res->validateInput()
    ->storeUser()
    ->storeStutDds()
    ->storeStupDds()
    ->handleAff()
    ->increaseStudentNum();



    $Res->smartInsertQuery2('payments',null,[
      'confirmed' => 1,
      'amount' => $payload->dd->ppxyz->amount,
      'created_at' =>getDateTime(),
      'platform' => "Card",
      'item_id'=> $payload->dd->ppxyz->pid,
      'user_id'=>$Res->lUid
]);


    $Res->createPaymentdata(
      $payload->amount,
      $Res->lUid,
      $payload->dd->ppxyz->pid)->queryProgramData()->queryUpdata();
   $Res->updateUserPaymentStatus();

    //outPut($payload);
$url  = $payload->redirectUrl;
$sperator = '';
if(strpos($url, '?') !== false){
  $sperator = '&';
}
else{
  $sperator = '?';
  
}
echo "o";
$msgs = JWT::encodeJsonArr(servSus('Payment successful'));

$url.=$sperator.'msgs='.$msgs ;






$Res->recordActivity($Res->lUid,'Made Card Payment (Stripe)'.$_GET['provider_session_id'].'Payment Made' ,'Payment', '');

    
    header("Location:". $url);
    exit();
// $uid =  $payload->userId;
// $amount = $payload->amount;
// $redirectUrl = $payload->redirectUrl;
// header("Location: " . $redirectUrl);
// exit();

?>