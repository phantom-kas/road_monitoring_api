<?php
class User_router extends Router{
 function __construct( $action = null)
 {  
  $this->action = $action;
    global $User;

    switch ($this->action){
      case 'signin':{
        $User->signIn();
        break;
      }

      case 'checksignedin':{
        outPut(checkSignedIn());
        break;
      }
    case 'checktoken':{
        outPut( $User->checkToken());
        break;
      }
      case 'checktoken':{
        $User->checkToken();
        break;
      }
      
      case 'getnewtoken':{
        $User->generateNewAccessToken();
        break;
      }
    
   

      case 'delete_profile':{
        $User->delete();
        break;
      }
      case 'get_colum_names':{
        outPut($User->getColumns('users'));
        break;
      }

      case "get_user_details":{

        

        output(array('data' =>$User->getUserInfo($_GET['id']),
   
   
    'img_url' =>   pathUrl().IMG_fd."profileimages/", 
   
    'message' => 'success' ,'status' => 'success'));
        
        break;
      }
      case 'update_profile_image':{
        $id = $User->getUserIdFromAtoken()->id;
        $idata = getDataInput();
        $uid = $idata['uid'];
         require_once './uploader.php';
         $fname = $User->getImgUrl($uid);
        if( $fname  == null || !file_exists("./src/assets/images/profileimages/$fname")){
          $fname = time(). $uid.'.png';
        }
         $URL = pathUrl().IMG_fd.$fname.'.png';
        if(upload('file-input',$fname,"/profileimages",'./src/assets/images',false))
        { 
          $NumAff =  $User->smartUpdate('users',null,['profile_img_url'=> $fname ], $uid);
          if($NumAff){
           output([...$User->serverOutputWithAlertsSuccess("User Profile image updated successfully"),'New_url'=>$URL]);
           die();
          }
        };
         break;
        }

        case 'reset_pass':{
          
          $User->resetPas();
          break;
        } 
         case 'get_user_proginfo':{
          $uid = $User->getUserIdFromAtoken()->id;
          $pid = checkCheckGetParam('pid');
          outPutData($User->getStudentCurrentProgram($uid,$pid)[0]);
          break;
        }
       case 'rq_password_reset':{
          
          $User->rQPassreset();
          break;
        } 
        case 'get_dash':{
          
          $User->getDAshC();
          break;
        }

        case 'change_premission':{
          $User->changePremission();
          break;
        }


        case 'get_user_premission':{
          $uid = null;
          if(isset($_GET['uid'])){
            $uid = $_GET['uid'];
          }
          else{
            $uid = $User->getUserIdFromAtoken();
          }
          outPutData( $User->getAllPremissions($uid));
        }

        case 'get_countires':{
          outputData($User->query("SELECT * FROM country")->getRows());
        }

        case 'add_user':{

            $User->createStaff();
      break;
        }

        

        case 'get_recent_activites':{
           $User->getRecentsActivities();
          break;
        }

        case 'get_user_name':{
          checkCheckGetParam('uid');
          $User->getUser();
         break;
       }

  case 'log_out':{
       
         $User->logOut();
         break;
       }
       
  case 'activate':{
       
         $User->activate();
         
         break;
       }

       case 'deactivate':{
        $User->deactivate();


        break;
       }
          case 'delete_user':{
        $User->deleteUser();

        
        
        break;
        
       }


        case 'register_user':{
        $User->registerUser();

        
        
        break;
        
       }
        case 'get_users':{
        $User->getUsers();
        
        
        break;
        
       }case 'block_unblock':{
        $User->blockUnblock();
        
        
        break;
        
       }


       case 'get_relations_data':{
        $User->getRelationsData();
        break;
       }

case 'update_family_relationship_history_info':{
        $User->createRelationship();
        break;
       }
       



       
     case 'get_premissions':{
      $User->getPremissions();
      break;
     }
          
     case 'get_user_premissions':{
      // echo "dsadsa";
      $User->getUserPremissions();
      break;
     }

     case 'add_premission':{
      $User->addPremission();
      break;
     }


     case 'remove_premission':{
      $User->removePremission();
      break;
     }

     case 'update_user_info':{
      $User->updateUserInfo();
      break;
    }
}
}
}
$User_router = new User_router( $action);
?>
