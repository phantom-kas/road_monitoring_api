<?php
header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
// header('Access-Control-Allow-Headers','Origin, Content-Type, X-Requested-With, Accept,Authorization');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type ,Authorization");
// header("Access-Control-Allow-Headers", "Access-Control-Allow-Headers, Authorization, authorization,Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
  die();
}
?>