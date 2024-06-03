<?php
class Camera_router extends Router
{
  function __construct($action = null)
  {
    $this->action = $action;
    global $Camera;
    switch ($this->action) {
    
      case 'get_logs':{
        $Camera->getLogs();
        break;
      }


      case 'get_classes':{
        $Camera->getClasses();
        break;
      }
     case 'get_dash':{
        $Camera->getDashBoard();
        break;
      }

        case 'get_report_for':{
        $Camera->getReportsFor();
        break;
      }
  }
}
}
$Camera_router = new Camera_router($action);
?>
