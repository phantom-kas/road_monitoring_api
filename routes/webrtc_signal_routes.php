<?php
class WertcSignaling_router extends Router
{
  function __construct($action = null)
  {
    $this->action = $action;
    global $WertcSignaling;
    switch ($this->action) {
    
      case 'start_streaming':{
        $WertcSignaling->start_streaming();
        break;
      }

case 'send_ice_candidates':{
        $WertcSignaling->sendIceCandedates();
        break;
      }
      case 'send_offer':{
        $WertcSignaling->sendOffer();
        break;
      }
      
      case 'anser_cam':{
        $WertcSignaling->anserCam();
        break;
      }
  }
}
}
$WertcSignaling_router = new WertcSignaling_router($action);
