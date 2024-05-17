<?php
  require './vendor/autoload.php';

  $options = array(
    'cluster' => 'mt1',
    'useTLS' => true
  );

    $pusher = new Pusher\Pusher(
      'aed507476a0b2fee7ec7',
      'fda1f58864a6304a4eb2',
      '1483332',
      $options
    );

  // $pp = new Pusher\Pusher(
  //   'b5dad7a5878d035f1345',
  //   'f885163a7e16344a00c4',
  //   '1487171',
  //   $options
  // );

  
  //$data['message'] = 'hello world';
 
?>