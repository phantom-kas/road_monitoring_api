<?php

class User extends Db
{
  public $columns = [''];
  public $name;
  public $password;
  public $id;
  public $tokenLength = (3 * 60 * 60);
  public $refreshTokenLength = (3 * 24 * 60 * 60);
  public $table = "users";
  public $RtknId;






  public function getUidFromEmail($em, $die = true)
  {
    $data = $this->query("SELECT id FROM users WHERE email = ?", [$em])->getRows();
    if ($die) {
      if (count($data) < 1) {
        output(servError('User Not Found'));
        die();
      }
    }
    return $data[0]['id'];
  }

  public function checkHasEmal($email): bool
  {

    $data = $this->query("SELECT email FROM users where email =?", [$email])->getRows();
    if (count($data) > 0) {
      return true;
    }
    return false;
  }

  public function invalidateRefreshToken($refreshToken)
  {
    $num_rows = $this->query("DELETE FROM refresh_tokens WHERE user_id = ? AND isvalid = ? AND token = ?", [$this->id, 1, $refreshToken], 'iis')->numAffectedRows();
    if ($num_rows < 1) {
      header("HTTP/1.1 401 Unauthorized");
      output(servError('Unkown error, please try again later.'));
      die();
    }
  }

  public function generateRefereshToken($payload = null, $exp = null)
  {
    $iat = time();
    $exp = $exp ?? time() + $this->refreshTokenLength;
    $paylod = $payload ??  [
      'iat' => $iat,
      'iss' => 'localhost',
      'exp' => $exp,
      'userId' => $this->id
    ];
    $token = JWT::encode($paylod, REFRESH_SECRETE_KEY);
    $lid =  $this->query('INSERT INTO refresh_tokens (user_id, token,  created_at, expire_at) VALUES (?,?,?,?)', [$this->id, $token, $iat, $exp], 'isii')->lastId();
    $this->RtknId = $lid;
    return  $token;
  }

  public function generateAccessToken($payload = null, $exp = null)
  {
    $exp = $exp ?? time() + $this->tokenLength;
    $paylod = $payload ??  [
      'iat' => time(),
      'iss' => 'localhost',
      'exp' => $exp,
      'userId' => $this->id,
      'RtknId' => $this->RtknId
    ];
    $token = JWT::encode($paylod, ACCESS_SECRETE_KEY);
    return  $token;
  }


  public function generateNewAccessToken()
  {
    $iData =   getDataInput() ?? $_POST;

    if (!isset($iData['refresh_token'])) {
      unAuth(true, 'Invalid Token');
    }



    try {
      //code...
      $payload =  JWT::decode($iData['refresh_token'], REFRESH_SECRETE_KEY, ['HS256'], 'r');
    } catch (\Throwable $th) {
      //throw $th;
      unAuth(true, $th->getMessage());
    };

    $iat = $payload->iat;
    $exp = $payload->exp;
    $this->id = $payload->userId;

    $num_rows = $this->query('SELECT * FROM refresh_tokens WHERE user_id = ? AND token = ?', [$this->id, $iData['refresh_token']], 'is')->numRows();


    if ($num_rows < 1) {
      unAuth(true, 'Invalids Token');
    }


    $this->invalidateRefreshToken($iData['refresh_token']);

    $refreshToken = $this->generateRefereshToken();
    $accessToken = $this->generateAccessToken();
    $msg = 'Token generation successful';
    output(array('refreshToken' => $refreshToken, 'accessToken' => $accessToken, 'message' => $msg));
    die();
  }


  public function checkToken()
  {
    $token = $this->getBearerToken();
    try {
      //code...
      $payload =  JWT::decode($token, ACCESS_SECRETE_KEY, ['HS256'], 'a');
    } catch (\Throwable $th) {
      //throw $th;
      unAuth(true, $th->getMessage());
    };
  }

  public function getUserInfo($uid)
  {
    return $this->query('SELECT user_name,lname,lname,created_at,id,is_blocked,profile_img_url FROM `users`
    where id = ?
    ',  [$uid])->getRows();
  }

  public function getStudentCurrentProgram($uid, $program_id)
  {
    return $this->query("SELECT s.id as student_id ,up.* , p.title as program_title ,cpp.price
     from students as s left outer join user_programs as up on up.user_id = s.user_id 
     && s.program_id = up.program_id
     LEFT OUTER JOIN program as p on s.program_id = p.id && s.user_id = up.user_id
     LEFT OUTER JOIN users as u on u.id = s.user_id 
     LEFT OUTER JOIN cert_program_prices as cpp on cpp.program_id = up.program_id
     && cpp.country_id = u.country_id
       where s.user_id = ? and s.program_id = ?", [$uid, $program_id])->getRows();
  }



  public function signIn()
  {
    $iData = $this->getPostData();
    if (!$iData) {
      unAuth(true, 'username and password must be provided');
    }
    if (!isset($iData['username'])) {
      header("HTTP/1.1 401 Unauthorized");
      output(servError('User name must be provided'));
      die();
    }
    if (!isset($iData['password'])) {
      header("HTTP/1.1 401 Unauthorized");
      output(servError('password must be provided'));
      die();
    }
    $results = $this->query('SELECT id ,password , user_name from users where user_name = ?  ', [$iData['username']])->getRows();
    if (count($results) < 1) {
      header("HTTP/1.1 401 Unauthorized");
      output($this->serverOutputWithAlertsError('User name or password error'));
      die();
    }
    if (!password_verify($iData['password'], $results[0]['password'])) {
      output($this->serverOutputWithAlertsError('User name or password error..'));
      die();
    }

    if (isset($iData['refresh_token'])) {
      $this->invalidateRefreshToken($iData['refresh_token']);
    }
    $this->id = $results[0]['id'];
    $data = $this->getUserInfo($this->id)[0];

    if ($data['is_blocked'] == 1) {
      output($this->serverOutputWithAlertsError('Your account has been blocked. COntact admin.'));
      die();
    }
    // $premissions = $this->query("SELECT * FROM premissions WHERE user_id = ?",$data['id'])->getRows();
    $refreshToken = $this->generateRefereshToken();
    $accessToken = $this->generateAccessToken();
    $msg = 'Login successful';
    $data['profile_img_url'] = pathUrl() . IMG_fd . "profileimages/" . $data['profile_img_url'];
    $this->recordActivity($data['id'], 'Signed in at ' . getDateTime(), 'Sign in', 'Sign in');
    output(array(
      'data' => $data,
      'refreshToken' => $refreshToken,
      'accessToken' => $accessToken,
      //'premissons' => $premissions,
      'alerts' => [servSus('Login Successful')],
      'message' => $msg,
      'profile_img_dir_url' => pathUrl() . IMG_fd . "profileimages/",
      'status' => 'success'
    ));
    die();
  }







  public function signOut()
  {
    session_unset();
  }

  public function updateColumns($end = true, $data = null)
  {
    $iData = null;
    if ($data != null) {
      $iData = $data;
    } else {
      $iData =  getDataInput();
      if (!$iData) {
        outPut(servError('no data'));
        die();
      };
    }


    $uid = $this->id;

    if (isset($iData['uid'])) {
      if ($iData['uid'] == $this->id) {
      }
      // else if (!$this->checkCanEdithUsers($this->id)){
      //   forbid();
      else if (!$this->canP($this->id, 'users_edit')) {
        forbid();
      } else {
        $uid = $iData['uid'];
      }
    }




    $numRows =  $this->getUserIdFromAtoken()->smartUpdate('users', null, $iData, $uid)->numAffectedRows();
    if ($numRows > 0) {
      if ($end) {
        outPut(servSus("$numRows rows updated successfully"));
        die();
        return;
      }
    }

    if ($end) {
      outPut(servError('0 items updated'));
      die();
    }

    return true;
  }


  public function updateUserInfo()
  {
  $idata = getDataInput();
  if(!isset($idata['fname']) && !isset($idata['lname']) && !isset($idata['user_name'])){
    output($this->serverOutputWithAlertsError('Error'));
    die();
    }
    $uid = $idata['uid'];

    unset($idata['uid']);
    $numAff = $this->smartUpdate('users',null,$idata,$uid)->numAffectedRows();
    if(!$numAff){
      output($this->serverOutputWithAlertsError('Unknown error'));
      die();
    }
    output($this->serverOutputWithAlertsSuccess('Update Successful'));
    die();
  }
  // public function generateRefereshToken($payload){}

  public function getImgUrl($id = null)
  {
    $id = $id ?? $this->id;
    return $this->query("Select profile_img_url From users WHERE id = ?", [$id])->getRows()[0]['profile_img_url'];
  }

  public function delete()
  {
  }

  public function rQPassreset()
  {
    // $uid = $this->getUserIdFromAtoken()->id;
    // echo 'sds';
    $tkn = generateRandomString();

    //$data = $this->query("SELECT email  FROM users where id = ?",[$data[0]['email']])->getRows();
    $iData = getDataInput() ?? $_POST;
    if (!isset($iData['username'])) {
      outPut(servError('Email not found'));
      die();
    }
    $email = $iData['username'];

    $data = $this->query("SELECT email , id  FROM users where email = ?", [$iData['username']])->getRows();

    if (!isset($data[0]['id'])) {
      outPut(servError('Email not found, error 324'));
      die();
    }

    $uid = $data[0]['id'];

    $this->smartUpdate('users', null, ['remember_token' => $tkn], $uid);


    $msg = "click here to reset password <a href = 'https://user.themelchizedekcenter.com/#/comfirm_pass?tkn=$tkn&email=" . urlencode(strtolower($email)) . "'>LINK</a>";




    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers  .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    if (mail($email, 'Password Reset', $msg, $headers)) {
      outPut(['alerts' => [servSus('Password reset email has been sent. ')], 'status' => 'success', 'message' => 'Password reset request successfull']);
      die();
    } else {
      output(servError());
    }


    die();
  }


  public function resetPas()
  {


    $iData = getDataInput() ?? $_POST;



    if (!isset($iData['tkn'])) {
      outPut(servError('No token found'));
      die();
    }

    if (!isset($iData['email'])) {
      outPut(servError('No email found'));
      die();
    }

    $uid = $this->getUidFromEmail(trim($iData['email']));

    $tkn = trim($iData['tkn']);

    if (count($this->query(
      "SELECT id FROM users where remember_token = ? && id = ?",
      [$tkn, $uid]
    )->getRows()) < 1) {
      outPut(servError('Invalid token'));

      die();
    }

    $this->smartUpdate(
      "users",
      null,
      ['password' => $iData['pass'], 'remember_token' => null],
      $uid
    );

    outPut(servSus('Password Updated successfully'));
    die();
  }

  public function getDAshC()
  {
    $uid = $this->getUserIdFromAtoken()->id;
    $prem = $this->checkPremission(['student', 'payment', 'programs'], $uid);
    if (count($prem) < 1) {
      forbid();
    }
    $studs = [];
    $pymts = [];
    $progs = [];

    $numAffs = [];

    $wwStu = [];





    $numWk = [];
    $numSt = [];

    $numdg = [];

    // if($prem['student'] == 8){
    $studs = $this->query("SELECT s.id FROM students as s inner join users as u on s.user_id = u.id where 1")->getRows();
    // }

    $wwStu = $this->query("SELECT u.created_at as enDt, u.id, u.fname , u.lname  , u.img_filename as img_nm, u.email
     FROM users as u inner join user_programs as up on  up.user_id = u.id 
     LEFT JOIN students as stu on stu.user_id = u.id where 1 && stu.id IS NULL  group by u.id limit 101")->getRows();

    if ($prem[0]['programs'] == 8) {
      $progs = $this->query("SELECT id FROM program where type = 'cert' limit 101 ")->getRows();
    } else {
      $progs = $this->query("SELECT p.id FROM program as p inner join user_programs_premissions as up on p.id = up.program_id where up.user_id = ? limit 101", [$uid])->getRows();
    }

    $pymts = $this->query("SELECT u.id FROM  users as u inner join students as s on u.id = s.user_id inner join user_programs as up on up.user_id = u.id where  up.status != 1  && up.status != 8 limit 101 ")->getRows();

    $numAffs = $this->query("SELECT id from users where aflt = 1 limit 101 ")->getRows();


    $ttCh = $this->query("SELECT total_fees from system where id = 1",)->getRows()[0]['total_fees'];


    $numdg =   $this->query("SELECT id FROM program where type = 'degree' limit 101 ")->getRows();
    $numWk =   $this->query("SELECT id FROM program where type = 'wk_shp' limit 101 ")->getRows();

    $output = servSus();
    $output['overDue'] = count($pymts);
    $output['num_students'] = count($studs) + count($wwStu);

    $output['num_progs'] = count($progs);
    $output['numAffs'] = count($numAffs);
    $output['ttCh'] = $ttCh;
    $output['numdg'] = count($numdg);
    $output['numWk'] = count($numWk);
    outPut($output);
    die();
  }

  public function getAllPremissions($uid)
  {
    return $this->query("SELECT * FROM premissions WHERE user_id = ?", [$uid])->getRows();
  }
  public function changePremission()
  {
    // checkCheckGetParam('prem');
    $iData = getDataInput() ?? $_POST;



    $uid = $this->getUserIdFromAtoken()->id;

    $this->canP($uid, 'edit_premissions');


    $uuid =  $uid;
    if (isset($iData['uid'])) {
      $uid = $iData['uid'];
    }

    $prem = $iData['prem'];
    $value = $iData['value'];
    $data = $this->query("SELECT id FROM premissions where user_id = ?", [$uid])->getRows();
    $num = null;
    if (count($data) < 1) {
      $this->smartInsertQuery2('premissions', null, [
        $prem => $value,
        'user_id' => $uid,
        'created_at' => getDateTime(),
        'updated_by' => $uuid
      ]);
    } else {
      $num = $this->query("UPDATE user_premissions set $prem = ? , updated_by = ? , updated_at = ? WHERE user_id = ?", [$iData['value'], $uuid, getDateTime(), $uid])->numAffectedRows();
    }
    $output = servSus('Update Successful');
    $output['alerts'] = [servInfo("Update Successful $num")];
    outPut($output);
    die();
  }



  public function createStaff($end = true, $data = null)
  {
    $error = false;
    $iData = null;
    $errorMsg = null;
    $numRows1 = null;
    $numRows2 = null;
    if ($data != null) {
      $iData = $data;
    } else {
      $iData =  getDataInput();
      if (!$iData) {
        outPut(servError('no data'));
        die();
      };
    }
    $this->checkRequired(['email', 'fname', 'lname'], $iData);


    if (isset($iData)) {
      if ($this->checkHasEmal($iData['email'])) {
        output(servError('Duplicate Entry for email not allowed'));
        die();
      }
    }
    $uid = $this->getUserIdFromAtoken()->id;
    // if(isset($iData['uid'])  && $iData['uid'] != $uid){
    //   // if(!$this->checkCanEdithUsers( $uid )){
    //   //   forbid();
    //   // }
    //   $uid = $iData['uid'];
    // }
    $iData['created_at'] = getDateTime();
    $iData['created_by'] = $uid;

    if (isset($iData['number'])) {
      $iData['mobile'] = $iData['number'];
    }
    $iData['memCh'] = 'yes';
    if (isset($iData['country'])) {
      $c = explode('__', $iData['country']);

      $iData['nationality'] = $c[1];
      $iData['country'] = $c[1];
      $iData['country_id'] = $c[0];
    }
    if (!isset($iData['password'])) {
      $iData['password'] = 'asdas{"dasdadfnadsfj<mmv{{::>>":;.::LASD!@#$#$#$#$#*$#)@gknakjsfnakjnfkjasdnfkjansdfkjndsakjfnadskj12924hui8u2i90r38ui9320ir32';
    }
    $numRows2 =  $this->smartInsertQuery2('users', null, $iData)->numAffectedRows();
    $lid = $this->lastId();
    $TMTC_TBData = $iData;
    $TMTC_TBData['id'] = $lid;
    if ($numRows2 > 0) {
      $numRows1 = $this->smartInsertQuery2('melchizedek', null, $TMTC_TBData)->numAffectedRows();
      $iData['user_id'] = $lid;
      $numRows1 = $this->smartInsertQuery2('staff', null, $iData)->numAffectedRows();

      $this->smartInsertQuery2('premissions', null, [
        'user_id' => $lid,
        'created_at' => getDateTime(),
        'created_by' => $uid
      ]);

      if ($numRows1 > 0 || $numRows2 > 0) {
        if ($end) {
          outPut(servSus("User created successfuly"));
          die();
          return;
        }
      } else {
        $error = true;
        $errorMsg = "0 rows updated successfully";
      }
    } else {
      $error = true;
      $errorMsg = "0 rows updated successfully";
    }
    if ($error && $numRows1 < 1 && $numRows2 < 1) {
      outPut(servError($errorMsg));
      die();
    }
    if ($end) {
      outPut(servError());
      die();
    }
    return true;
  }


  public function getRecentsActivities()
  {
    $uid = $this->getUserIdFromAtoken()->id;


    if (isset($_GET['uid'])) {
      $this->addQwhere(' && u.id = ?', $_GET['uid']);
    }
    // if(!isset($_GET['uid'])){
    //   if(!$this->canAll($uid,'can_view_all_activity')){
    //     $this->addQwhere(' && u.id = ?',  $uid );
    //   }
    // }
    $this->qLim  = 'Limit 25';
    $this->getFromLastIdInDecOrder('a.id');
    $data = $this->query("SELECT  a.id,
    a.title,
    a.route, a.dis, a.user_id , a.created_at,
    u.fname, u.lname, u.profile_img_url ,a.user_id 
    FROM activity as a inner join users as u on a.user_id = u.id
      $this->qwhere
      $this->qOrder
      $this->qLim 
    ", $this->qVars)->getRows();
    outPutDataWithImgUrlRoot($data);
    die();
  }

  // public function getUserName($uid)
  // {
  //   return outPutDataWithImgUrlRoot ($this->query("SELECT lname , fname ,aflt_link_param,crypto_wallet FROM users where id = ?",[$uid])->getRows());
  // }




  public function logOut()
  {
    $uid = $this->getUserIdFromAtoken()->id;






    $this->invalidateAtknRtkn();
    $this->recordActivity($uid, "Signed out at " . getDateTime(), "Sign Out", $uid);
    output(servSus());
  }


  public function activate()
  {
    $idata = getDataInput();
    $uid = $idata['uid'];

    // echo $uid; die();
    $num = $this->smartUpdate('users', null, ['active' => 1], $uid)->numAffectedRows();

    if ($num > 0) {
      output(servSus());
      return;
    }
    output(servError());
  }

  public function deactivate()
  {
    $uid = $this->getUserIdFromAtoken()->id;
    if (!$this->canAll($uid, 'users_activate')) {
      forbid();
      die();
    }
    $idata = getDataInput();
    if (!$idata) {
      bad_req();
      die();
    }
    $acitveState  = $idata['action'] == 2 ? 1 : 0;
    $numAffected = $this->smartUpdate('users', null, ['active' => $acitveState], $idata['uid']);
    if (!$numAffected) {
      outPut(['alerts' => [servError('Update fail')], 'status' => 'error', 'message' => 'Update fail']);
      die();
    }
    outPut(['alerts' => [servSus('Update successful')], 'status' => 'success', 'message' => 'Update successful']);
    die();
  }


  public function deleteUser()
  {
    $uid = $this->getUserIdFromAtoken()->id;
    if (!$this->canAll($uid, 'users_delete')) {
      forbid();
      die();
    }
    $idata = getDataInput();
    if (!$idata['uid']) {
      bad_req();
      die();
    }
    $numAffected = $this->query("DELETE FROM users WHERE id = ?", [$idata['uid']]);
    if (!$numAffected) {
      outPut(['alerts' => [servError('Delete fail')], 'status' => 'error', 'message' => 'Delete fail']);
      die();
    }
    outPut(['alerts' => [servSus('Delete successful')], 'status' => 'success', 'message' => 'Delete successful']);
    die();
  }


  public function registerUser()
  {
    $this->getPostData();

    $this->validateREquiredInputData(['fname', 'user_name', 'password', 'lname']);
    // echo 'test';
    $uuid = $this->getUserIdFromAtoken()->id;
    // if(!$this->canAll($uuid,'can_register_users')){
    //   forbid();
    // }
    if (count($this->query("Select user_name from users where user_name = ?", [$this->InputData['user_name']])->getRows()) > 0) {
      output($this->serverOutputWithAlertsError('Duplicate email not allowed'));
      die();
    }

    require './uploader.php';

    require_once './uploader.php';

    $ffname = date("d_m_Y0H-i-s");


    if ($ffname  == null || file_exists("./src/assets/images/profileimages/$ffname.png")) {


      $ffname = time() . rand() . '.png';
    }

    $URL = pathUrl() . IMG_fd . $ffname . '.png';


    if (!upload('image', "$ffname.png", "/profileimages", "./src/assets/images", false)) {
      outPut(servError());
      die();
    }


    $sqlData = [
      'fname' => $this->InputData['fname'],
      'lname' =>  $this->InputData['lname'],
      'user_name' =>  $this->InputData['user_name'],
      'profile_img_url' => "$ffname.png",
      'created_at' => getDateTime(),
      'created_by' =>  $uuid,
      'password' => '$2y$10$PRsShvurdjcmuFWq8qnzsez2LXGdqqdwmbRmOgovqNyjuHndIiI2C'
    ];



    // $sqlData['country_id'] = explode('__',$this->InputData['country'])[0];

    $uid = $this->smartInsertQuery2('users', null, $sqlData)->lastId();

    if (!$uid) {
      outPut(servError());
      die();
    }
    if (isset($this->InputData['permissions'])) {
      foreach ($this->InputData['permissions'] as $key => $value) {
        $this->smartInsertQuery2(
          'user_premissions',
          null,
          [
            'premission_id' =>  $value,
            'user_id' => $uid,
            'created_at' => getDateTime(),
            'created_by' => $uuid
          ]
        );
      }
    }

    output($this->serverOutputWithAlertsSuccess('User Registeration Successfull'));
    die();
  }


  public function getUsers()
  {
    $uid = $this->getUserIdFromAtoken()->id;
    if (isset($_GET['uid'])) {
      $this->addQwhere(' && u.id = ?',  $_GET['uid']);
    }
    $this->qLim  = 'Limit 25';
    $this->getFromLastIdInDecOrder('u.id');
    $data = $this->query("SELECT
     u.user_name,u.lname,u.lname,u.created_at,u.fname,u.id,u.is_blocked,u.profile_img_url FROM users as u
      $this->qwhere
      $this->qOrder
      $this->qLim 
    ", $this->qVars)->getRows();
    outPutDataWithImgUrlRoot($data);
    die();
  }



  public function blockUnblock()
  {
    $this->getPostData();
    $uid = $this->getUserIdFromAtoken()->id;
    $this->validateREquiredInputData(['id']);
    // if()
    $this->InputData['status'] = $this->InputData['status'] ?? 0;
    if (!$this->canAll($uid, 'can_block')) {
      forbid();
      die();
    }
    $num = $this->query("UPDATE users set is_blocked = ? where id = ? ", [$this->InputData['status'], $this->InputData['id']])->numAffectedRows();
    if ($num >  0) {
      output($this->serverOutputWithAlertsSuccess('User status updated successfully'));
      die();
    }
    outPut($this->serverOutputWithAlertsError('Update Failed'));
    die();
  }



  public function getUser()
  {
    $data = $this->getUserName($_GET['uid']);
    // dd($data);
    outPutDataWithImgUrlRoot($data);
    die();
  }


  public function getRelationsData()
  {

    $uuid = $this->getUserIdFromAtoken()->id;
    $uid = $uuid;
    if (isset($_GET['uid'])) {
      if ($_GET['uid']) {
        $uid = $_GET['uid'];
      }
    }
    if (isset($_GET['relationship'])) {
      $this->addQwhere(' && relationship = ?', $_GET['relationship']);
    }
    // if(isset($_GET['for'])){
    //   if(isset($_GET['uid'])){
    //     $uid = $_GET['uid'];
    //   }
    //   $this->addQwhere(' && a.for_user_id = ? && a.for_user_id is not null',  $uid );
    // }

    $this->addQwhere(' && u.id = ?',  $uid);


    $this->qLim  = '';
    $this->getFromLastIdInDecOrder('u.id');
    $data = $this->query("SELECT 
    u.first_name , u.last_name , u.id as user_id ,
    fh.id as id , fh.relationship,fh.history,fh.created_at,
    fh.updated_at
  FROM users as u  inner join family_history as fh on fh.user_id = u.id
      $this->qwhere
      $this->qOrder
      $this->qLim 
    ", $this->qVars)->getRows();
    outPutDataWithImgUrlRoot($data);
    die();
  }


  public function createRelationship()
  {
    $uid = $this->getUserIdFromAtoken()->id;
    $this->getPostData();
    $this->validateREquiredInputData(['uid', 'relationship', 'history']);
    if (!$this->canAll($uid, 'can_edit_medical_reports')) {
      forbid();
      die();
    }
    $hisToryData = $this->query("SELECT id FROM family_history where user_id =   ? and relationship = ?", [
      $this->InputData['uid'],
      $this->InputData['relationship']
    ])->getRows();
    $lid = null;
    if (count($hisToryData) < 1) {
      $lid =  $this->smartInsertQuery2('family_history', null, [
        'user_id' => $this->InputData['uid'],
        'relationship' => $this->InputData['relationship'],
        'created_by' => $uid,
        'history' => $this->InputData['history'],
        'created_at' => getDateTime(),
      ])->lastId();
    } else {

      $this->smartUpdate('family_history', null, [
        'updated_at' => getDateTime(),
        'history' => $this->InputData['history'],
        'updated_by' => $uid
      ], $hisToryData[0]['id']);
      $lid = $hisToryData[0]['id'];
    }


    $this->recordActivity($uid, 'Updated User id no_' . $this->InputData['uid'] . '. Family history', 'Updated User record ', $lid);

    output($this->serverOutputWithAlertsSuccess('Paitients family history updated successfully.'));
    die();
  }


  public function getPremissions()
  {
    $data = $this->query("SELECT * from premissions")->getRows();
    outPutData($data);
  }


  public function getUserPremissions()
  {
    $uid = $_GET['uid'];
    $data = $this->query(
      "SELECT p.id as `premission_id` ,p.premission ,p.Description, up.user_id 
    from premissions as p 
    left outer join user_premissions 
    as up on p.id = up.premission_id and (up.user_id = ? or up.user_id is null);",
      [$uid]
    )->getRows();
    outPutData($data);
    die();
  }


  public function addPremission()
  {
    $idata = getDataInput();
    $uuid = $this->getUserIdFromAtoken()->id;

    $uid = $idata['uid'];

    $prem = $this->query("SELECT id from user_premissions where user_id = ? and premission_id = ? ", [$uid, $idata['pid']])->getRows();
    if (count($prem) > 0) {
      output($this->serverOutputWithAlertsError('Premission already set'));
      die();
    }

    $lid = $this->smartInsertQuery2('user_premissions', null, [
      'premission_id' => $idata['pid'],
      'user_id' => $uid,
      'created_at' => getDateTime(),
      'created_by' => $uuid
    ])->lastId();

    if ($lid) {
      output($this->serverOutputWithAlertsSuccess('Premission added successfully'));
      die();
    }
    output($this->serverOutputWithAlertsError('Unknown Error'));
  }




  public function removePremission()
  {
    $uuid = $this->getUserIdFromAtoken()->id;
    $idata = getDataInput();
    $uid = $idata['uid'];
    $numAff = $this->query("DELETE FROM user_premissions where user_id = ? and premission_id =  ?", [$uid, $idata['pid']])->numAffectedRows();

    if ($numAff) {
      output($this->serverOutputWithAlertsSuccess('Premission removed successfully'));
      die();
    }
    output($this->serverOutputWithAlertsError('Unknown Error'));
  }
}


$User = new User();
