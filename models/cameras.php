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
        $where_clause = ' &&  l.report_id in (';
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
    $this->qLim  = 'Limit 25';
    $this->getFromLastIdInDecOrder('l.id');
    $data = $this->query("SELECT l.id,r.report,r.id as report_id,l.cam_id,l.created_at,l.date,l.image_url,l.location	
    ,c.type
    from report_logs as l  inner join cameras as c
    inner join report as r on r.id = l.report_id
      $this->qwhere
      $this->qOrder
      $this->qLim 
    ",$this->qVars)->getRows();
    outPutDataWithImgUrlRoot($data);
    die();
  }

  public function getClasses(){
    $data = $this->query("SELECT report as class  , id as report_id   from report")->getRows();
    outPutDataWithImgUrlRoot($data);
    die();
  }
}
$Camera = new Camera();

?>