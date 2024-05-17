<?php
    include_once "../../private/session/session.php";
    require __DIR__ . '/vendor/autoload.php';

    function autht(){
        if(isset($_SESSION['admin_id']) || isset($_SESSION['user_id']))
        {
            return true;
        }
        else
        {
            return false;
        }
      };

      if(!autht())
      {
        header('HTTP/1.1 403 Forbidden');
        exit('Not authorised');
      }
      else
      {
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

      

    $id = isset($_SESSION['admin_id'])? $_SESSION['admin_id'] : $_SESSION['user_id'] ;

    $user_data = array(
        'id' => (string) $id,
        'user_info' =>array('name' => 'ama')
    );
      
      $auth = $pusher->authorizePresenceChannel($_POST['channel_name'], $_POST['socket_id'],$id,$user_data);
     // $auth = $pusher->authenticateUser($_POST['socket_id'],$id);
    echo $auth;
}


?>