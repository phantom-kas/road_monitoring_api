<?php

function upload($fname,$nm, $sub = '', $dir = "./src/assets/images",$die = true,$tp = 'i')
{
$target_dir = $dir;

$uploadOk = 1;

$ext = end((explode(".", $_FILES[$fname]["name"])));
$target_file = $target_dir.$sub."/". $nm.$ext;

$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(!isset($_FILES[$fname])){
    output(servError('No File Found'));
    die();
  }

if(!file_exists($_FILES[$fname]["tmp_name"])){
  output(servError('No File Found'));
  die();
}



if($tp == 'i'){
  $check = getimagesize($_FILES[$fname]["tmp_name"]);
  if($check !== false) {
    // outPut(servError("File is an image - " . $check["mime"] . "."));
    $uploadOk = 1;
  } else {
    if($die){
       outPut(servError("File is not an image - " . $check["mime"] . "."));
    }}
   

    $uploadOk = 0;
  }

  if (file_exists($target_file)) {
   // outPut(servSus("Successfully Updated"));
     $uploadOk = 2;
  }

  if ($uploadOk == 0) {

   
    return false;
  // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES[$fname]["tmp_name"], $target_file)) {
    
      $url = pathUrl().IMG_fd."profileimages/".$nm;
      if( $uploadOk == 2 && $die){

        $respons = servSus("Successfully Updated");
        $respons['data']['profile_img_url'] =   $url = pathUrl().IMG_fd."profileimages/".$nm;
        outPut($respons);
      }
      else if($die){
        $respons = servSus("Successfully uploaded");
        $respons['data']['profile_img_url'] =   $url = pathUrl().IMG_fd."profileimages/".$nm;
        outPut($respons);
      }
      return true;
    } else {
     return false;
    }
  }
}
?>