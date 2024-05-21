<?php

class Upload_router extends Router{
 function __construct( $action = null)
 {  
  $this->action = $action;
    global $Upload;

    switch ($this->action){
      case 'upload_file':{
        $Upload->receiveFiles();
        break;
      }
       case 'txt':{
        $Upload->testPuser();
        break;
      }
    }
  }
  }
  $Upload_router = new Upload_router( $action);
?>