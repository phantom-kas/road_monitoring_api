<?php
  require './vendor/autoload.php';

  $options = array(
    'cluster' => 'us3',
    'useTLS' => true
  );

  $pusher = new Pusher\Pusher(
    'c00790363bec04452136',
    '91c7da8b5121f761f1dd',
    '1786007',
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