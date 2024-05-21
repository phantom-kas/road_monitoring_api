<?php
require_once "real_time_msg.php";

class WertcSignaling extends  Db
{
  
  public function start_streaming():void{
    global $pusher;
    $idata = getDataInput();
    $pusher->trigger($idata['camera_name'].'_channel', 'start_streaming', ['message' =>'ok','from_user' => $idata['from_user'] ]);
    outPut(servSus());
   
    die();                                
  }

  public function sendIceCandedates():void{
    global $pusher;
    $idata = getDataInput();
    $pusher->trigger($idata['to_user'].'_channel', 'recieve_ice_candidate',[ 'icecandedate'=>$idata['icecandedate'] ,'to_user'=> $idata['to_user'], 'from_user' => $idata['from_user']]);
    outPut(servSus());
   
    die();          
  }

 public function sendOffer():void{
    global $pusher;
    $idata = getDataInput();
    $pusher->trigger($idata['camera_name'].'_channel', 'recieve_offer', ['offer' => $idata['offer'] , 'camera_name'=> $idata['camera_name'] , 'admin_name' => $idata['admin_name']]);
    outPut(servSus());
    die();          
  }

public function anserCam():void{
    global $pusher;
    $idata = getDataInput();
    $pusher->trigger($idata['admin_name'].'_channel', 'answerResponse', ['answer' => $idata['answer'] , 'camera_name' =>$idata['camera_name'] , 'admin_name' =>$idata['admin_name']]);
    outPut(servSus());
    die();          
  }
  
}
$WertcSignaling = new WertcSignaling();

?>