<?php
class Upload extends  Db
{
  public function receiveFiles()
  {
    $idata = getDataInput();

    //outPut($idata);
    //echo $idata['data'][0] ;die();
    // Directory where uploaded files will be stored
    $targetDir = "./uploads/".$idata['dir'].'/';
    $allowedTypes = [];

    $class_counts=[];
    
    // Ensure the target directory exists
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }

    // Check if files have been uploaded
    if ($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_FILES['files'])) {
      output(servError("error"));
      die();
    }
      $files = $_FILES['files']['tmp_name'];
      // outPut($_FILES);die();
      // Loop through each file
      for ($i = 0; $i < count($files); $i++) {
        // Get the file details
        $classId=$idata['class_id'][$i];
        $fileName = basename($_FILES['files']['name'][$i]);
        $fileTmpPath = $_FILES['files']['tmp_name'][$i];
        $fileSize = $_FILES['files']['size'][$i];
        $fileType = $_FILES['files']['type'][$i];
        $fileError = $_FILES['files']['error'][$i];

        if(isset($class_counts['class_'.$classId])){
          $class_counts['class_'.$classId] = $class_counts['class_'.$classId] + 1;
        }
        else{
          $class_counts['class_'.$classId]= 1;
        }
        // Set the target file path
        $targetFilePath = $targetDir . $fileName;
        // Check for errors
        if ($fileError !== UPLOAD_ERR_OK) {
          // Validate file size (example: max 5MB)
          output(servError("The file $fileName exceeds the maximum allowed size.<br>"));
          die();
        }
        $allowedTypes = ['image/jpeg', 'image/png','image/jpg',""]; 
        if (!in_array($fileType, $allowedTypes)) {
          output(servError("The file type of $fileName is not allowed.<br>"));
          die();
        }
        // Move the file to the target directory
        if (!move_uploaded_file($fileTmpPath, $targetFilePath)) {
          output(servError("There was an error moving the file $fileName.<br>"));
          die();
        }

        
      }
      output(servSus("Upload successfully"));
      die();

    }



    protected function storeReportLog(){
      
    }
  }






$Upload = new Upload();
