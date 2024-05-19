<?php
class Upload extends  Db
{
  public function receiveFiles()
  {
    $idata = getDataInput();

    //outPut($idata);
    //echo $idata['data'][0] ;die();
    // Directory where uploaded files will be stored
    $targetDir = "./uploads/images/" . $idata['dir'] . '/';
    $allowedTypes = [];

    $class_counts = [];

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
      // $classId=$idata['class_id'][$i];
      $fileName = basename($_FILES['files']['name'][$i]);
      $fileTmpPath = $_FILES['files']['tmp_name'][$i];
      $fileSize = $_FILES['files']['size'][$i];
      $fileType = $_FILES['files']['type'][$i];
      $fileError = $_FILES['files']['error'][$i];

      // if(isset($class_counts['class_'.$classId])){
      //   $class_counts['class_'.$classId] = $class_counts['class_'.$classId] + 1;
      // }
      // else{
      //   $class_counts['class_'.$classId]= 1;
      // }
      // Set the target file path
      $targetFilePath = $targetDir . $fileName;
      // Check for errors
      // if ($fileError !== UPLOAD_ERR_OK) {
      //   // Validate file size (example: max 5MB)
      //   output(servError("The file $fileName exceeds the maximum allowed size.<br>"));
      //   die();
      // }
      $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', ""];
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
    //$class_counts = [];
    for ($i = 0; $i < count($idata['class_id']); $i++) {
      $class = $idata['class'][$i] . '_' . $idata['class_id'][$i];
      if ($this->storeReportLog(
        [
          $idata['class'][$i], $idata['cam_id'],
          getDateTime(),
          substr($idata['image_path'][$i], 1), $idata['location'][$i], $idata['class_id'][$i], getDateTime(), $idata['model']
        ]
      )) {
        if (isset($class_counts[$class])) {
          $class_counts[$class] = $class_counts[$class]  + 1;
        } else {
          $class_counts[$class] = 1;
        }
      };
    }

    // if(count($this->query("SELECT ")))
    foreach ($class_counts as $key => $value) {
      if (count($this->query(
        "SELECT id from num_reports_per_day where date = ? and class_id = ? and model_id = ?",
        [getDateNow(), explode('_', $key)[1], $idata['model']]
      )->getRows()) > 0) {
        $this->query(
          "UPDATE num_reports_per_day set num = num + ? , updated_on = ? where  date = ? and class_id = ? and model_id = ?  ",
          [$value, getDateTime(), getDateNow(), explode('_', $key)[1], $idata['model']]
        );
      } else {
        $this->query(
          "INSERT INTO num_reports_per_day (date, class_id, model_id, updated_on,num) values (?,?,?,?,?)",
          [getDateNow(), explode('_', $key)[1], $idata['model'], getDateTime(), 1]
        );
      }
    }
    output(servSus("Upload successfully"));
    die();
  }



  protected function storeReportLog($params)
  {

    //INSERT INTO `report_logs` (`id`, `report`, `cam_id`, `created_at`, `date`, `image_url`, `location`, `report_id`, `report_time`) VALUES (NULL, '', '1', '2024-05-17 07:13:35.000000', '2024-05-01', 's', 's', '0', NULL);
    if ($this->query(
      "INSERT  INTO report_logs
       (report ,cam_id ,date ,image_url ,location ,report_id ,created_at , model_id) 
       VALUES (?,?,?,?,?,?,?,?)
       ",
      $params
    )->lastId()) {
      return true;
    }
    return false;
  }


  
}






$Upload = new Upload();
