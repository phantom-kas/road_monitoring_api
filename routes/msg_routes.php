<?php
class Msg_router extends Router
{
  function __construct($action = null)
  {
    $this->action = $action;
    global $Msg;
    switch ($this->action) {
    
        case 'send_msg': {
          $uid = $Msg->getUserIdFromAtoken()->id;
          $iData = getDataInput() ?? $_POST;
          $channel = null;
          if(isset($iData['channel'])){
            $channel = $iData['channel'];
            $iData['type'] = $iData['channel'];
      
            if($iData['channel'] == 'support'){
              $dd = $Msg->query("SELECT num FROM user_support WHERE type = ? && from_uid = ?",['support' , $uid])->getRows();
              if(count(($dd)) > 0){
                $Msg->query("UPDATE user_support SET last_update = ?, num =  ".$dd[0]['num'] + 1 .
                " WHERE from_uid = ?",[getDateTime(), $uid]);
              }
              else{
                $Msg->smartInsertQuery2("user_support",null,
                [
                  'from_uid' => $uid,
                  'last_updated' => getDateTime(),
                  'num' => 1,
                  'type' => 'support'
                ]);
              }
              $Msg->recordActivity($uid,$iData['msg'],'Support','Support');
            }
            else if(isset($iData['to_uid']))
            {
              $dd = $Msg->query("SELECT from_uid, to_uid , num FROM user_msg")->getRows();
              if(count($dd) > 0){
                $Msg->query(
                  "UPDATE user_msg SET num =? , last_update =? 
                  WHERE from_uid =? AND to_uid =?",
                  [
                    $dd[0]['num'] + 1,
                    getDateTime(),
                    $uid,
                    $iData['to_uid']
                  ]);
              }
              else{
                $Msg->smartInsertQuery2('user_msg' ,null, 
                [
                  'from_uid' =>$uid,
                  'to_uid' =>$iData['to_uid'],
                  'last_update' => getDateTime(),
                  'num' => 1
                ]);
              }
            } 
          }
          else if(isset($iData['to_uid'])){ 
            $channel = "channel_0".$iData['to_uid'];
          }

						require_once "real_time_msg.php";

            $uid = $Msg->getUserIdFromAtoken()->id;
            $iData['from_uid'] = $uid;
            $iData['created_at'] = getDateTime();
            $iData['created_by'] = $uid;
            $lid = $Msg->smartInsertQuery2('msg',null,$iData)->lastId();

						$data =$iData;
             $data['disType'] ='msg' ;
             $data['msg_id'] =$lid ;
            $pusher->trigger($channel, 'msg', $data ,array('socket_id' => $iData['socket_id'])
          );
          output(servSus());
          break;
        }
        case 'get_msgs':{
          $Msg->getUserMsgs();
          break;
        }
        case 'get_message_users':{
          $Msg->getMsgUsers();
          break;
        }
    }
  }
}
$Week_router = new Msg_router($action);
