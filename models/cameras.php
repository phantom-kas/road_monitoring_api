<?php


class Camera extends  Db
{
 




  public function getLogs(){
    $uid = $this->getUserIdFromAtoken()->id;

    
      if(isset($_GET['cid'])){
        $this->addQwhere(' && c.id = ?', $_GET['cid'] );
      }

      if(isset($_GET['date_from'])){
        $this->addQwhere(' && l.date >= ?', $_GET['date_from'] );
      }

      if(isset($_GET['date_to'])){
        $this->addQwhere(' && l.date <= ?', $_GET['date_to'] );
      }

      if(isset($_GET['id'])){
        $this->addQwhere(' && l.id = ?', $_GET['id'] );
      }

      if(isset($_GET['classes'])){
        $where_clause = ' &&  r.id in (';
        $params = array();
        $ln = count($_GET['classes']);
        for ($i = 0; $i < $ln; $i++)  {
          $where_clause.= '?';

            if ($i < $ln - 1) {
              $where_clause .= ',';
          }
          else{
            $where_clause .= ') ';
          }
          array_push($params,$_GET['classes'][$i]['report_id']);
          // echo  $_GET['classes'][$i]['report_id'];
        }
        $this->addQwhere( $where_clause, $params );
        // dd( $params );
        // die();
      }

      /// $this->addQwhere( ' && r.model_id = ?', [2] );
    $this->qLim  = 'Limit 25';
    $this->getFromLastIdInDecOrder('l.id');
    $data = $this->query("SELECT l.id,r.report,r.id as report_id,l.cam_id,l.created_at,l.date,l.image_url,l.location	
    ,c.type , m.id as model_id , l.box
    from report_logs as l  inner join cameras as c
    inner join report as r on r.class_id = l.report_id
     inner join model as m on m.id = r.model_id
      $this->qwhere
      $this->qOrder
      $this->qLim 
    ",$this->qVars)->getRows();
    outPutDataWithUploadDir($data);
    die();
  }

  public function getClasses(){
    $data = $this->query("SELECT report as class  , id as report_id   from report")->getRows();
    outPutDataWithImgUrlRoot($data);
    die();
  }

  public function getDashBoard(){

    if(isset($_GET['model_id'])){
      $this->addQwhere( " && np.model_id = ?", [$_GET['model_id']] );
    }
    $this->addQwhere( " &&  date between adddate(now(),-7) and now()" );
    $data = $this->query("SELECT r.report,np.num , np.date from num_reports_per_day as np
    inner join report as r on np.class_id = r.class_id and np.model_id = r.model_id
    ".  $this->qwhere,$this->qVars)->getRows();
    $date = getDateNow(); 
    
    $todayData = $this->query("SELECT np.num,r.report from report as r
     left outer join
      num_reports_per_day as np on r.class_id = np.class_id
       and r.model_id = np.model_id and np.date =? where r.model_id = ?",[getDateNow() , $_GET['model_id']])->getRows();
    outPut(['today'=>$todayData,...servSus(),'data'=> $data,'datenow'=>$date]);
    die();
  }
}
$Camera = new Camera();

?>