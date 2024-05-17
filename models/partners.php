<?php


class Partners extends  Db
{
  public function storePartner(){
    $idata = getDataInput();
    $num = $this->smartInsertQuery2('partners' , null, 
    $idata
    )->lastId();

    if($num){
      outPut(servSus('Form submitted successfully'));
      die();

      return;
    }
    outPut(servError());
    die();

  }

  public function getPartners(){
 $this->getFromLastIdInDecOrder('id');
  
 

  
    $data = $this->query("SELECT *
      FROM partners  
      $this->qwhere
    $this->qOrder
      limit 25
   ",$this->qVars)->getRows();
    outPutData($data); 
  }
}
$Partners = new Partners();

?>