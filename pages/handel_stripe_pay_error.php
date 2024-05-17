<?php
$sub = '../';
require_once "../classes/mysqli.php";
require_once "../env.php";
require_once "../fuctions.php";
require_once "../models/payments.php";

$M = new  Db();

if(!isset($_GET['tkn'])){
  exit();
}

$token = $_GET['tkn'];
    try {
      //code...
     
     
      $payload =  JWT::decode( $token , 'ACCESS_SECRETE_KEYS', ['HS256'], 'a');
    } catch (\Throwable $th) {
      //throw $th;
      echo '2';
      echo   $th->getMessage();

      
     exit();
    }

   // outPut($payload);
   // $Payment->smartUpdate('payments',null,['confirmed' => 1],$payload->pid);
    $Payment->query("DELETE FROM payments WHERE id = ?",$payload->pid);
  //   $Payment->createPaymentdata(
  //     $payload->amount,
  //    $payload->userId,
  //    $payload->itemId)->queryProgramData()->queryUpdata();
  //  $Payment->updateUserPaymentStatus();

    //outPut($payload);
$url  = $payload->redirectUrl;
// $sperator = '';
// if(strpos($url, '?') !== false){
//   $sperator = '&';
// }
// else{
//   $sperator = '?';
  
// }

// $msgs = JWT::encodeJsonArr(servSus('Payment successful'));


//.'msgs='.$msgs ;

$Payment->recordActivity($payload->userId,'Made Card Payment (Stripe)'.'Payment Canceled' ,'Payment', '');


    header("Location:". $url);
    
// $uid =  $payload->userId;
// $amount = $payload->amount;
// $redirectUrl = $payload->redirectUrl;
// header("Location: " . $redirectUrl);
// exit();

?>