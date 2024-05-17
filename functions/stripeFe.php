<?php
// require_once "./models/payments.php";
require_once "./classes/stripe/init.php";
$iData = getDataInput()  ?? $_POST ;
$stripe = new \Stripe\StripeClient(STRIPE_SECRETE_KEY);
//$uid = $Payment->getUserIdFromAtoken()->id;

// $iData = ['amount' => 1000,  'item_id'  => 23];

// $paymentData = ['user_id'=>
// $uid, 
//  'amount' => $iData['amount'],
//  'created_at' =>getDateTime(),
//  'platform' => "Card",
// // 'meta' => json_encode($pData),
//  'item_id'=> $iData['item_id'],
//  'confirmed' => -1,
// // 'date_confirmed' => getDateTime()
// ];


//$pid = $Payment->smartInsertQuery2('payments' , null,$paymentData)->lastId();

$IineItems = [
  [
    'price_data' =>[
      'currency' => 'USD',
      'product_data' =>[
        'name' => $iData['ppxyz']['program_title'],
      ],
        'unit_amount' => $iData['ppxyz']['amount'] ,
    ],
      'quantity' => 1
  ]
  ];
  // output($iData);
  // die();
  $exp = $exp ?? time() + 300000;
  $paylod = $payload ??  [
    'iat' => time(),
    'iss' => 'localhost',
    'exp' => $exp,
  //  'userId' => $uid,
  //  'pid' => $pid,
  //  'itemId' => $iData['item_id'],
    'redirectUrl' => $iData['ppxyz']['redirectUrl'],
    'amount' => $iData['ppxyz']['amount'],
    'dd' => $iData
  ];
  $token = JWT::encode($paylod, 'ACCESSSSSS_SSSSECRETE_KEYSSSS');

  // echo $token;
  // die();
  $checkoutSession = $stripe->checkout->sessions->create([
    'line_items' => $IineItems,
    'mode' =>'payment',
    'success_url' => pathUrl().'pages/handel_stripe_pay_success_FE.php?tkn='.$token.'&provider_session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' =>  pathUrl().'pages/handel_stripe_pay_error_FE.php?tkn='.$token.'&provider_session_id={CHECKOUT_SESSION_ID}',
  ]);

  $ref = $checkoutSession->id;
  
 // $Payment->smartupdate('payments',null,['reference'=>$ref],$pid);

 

//echo $checkoutSession->url;
$url = JWT::encodeStr($checkoutSession->url);
//die();
 outPutData(['redirectUrl' =>$url]);
//header("Location:". $checkoutSession->url);
exit();
// 
?>