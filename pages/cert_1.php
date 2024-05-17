<?php
$date = explode('-',getDateTime());
//$date = date('F d Y', strtotime($date));
//header('content-type: text/html; charset=utf-8');

$ypost =   intval($date[0]) - 2000; 
$num = new NumberFormatter('en',NumberFormatter::SPELLOUT);

$year = ucwords($num->format(2000)).' and '.ucwords($num->format($ypost ),'-' );

$day = explode(' ',$date[2])[0];


$monthNum  = $date[1];
$dateObj   = DateTime::createFromFormat('!m', $monthNum);
$month = $dateObj->format('F'); // March
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alice&display=swap" rel="stylesheet">
</head>
<style>
  /* @font-face {
  font-family: alice;
  src: url('https://user.themelchizedekcenter.com/api/src/assets/fonts/Alice-Regular.ttf');
} */

  body {
    background-image: url('https://user.themelchizedekcenter.com/api/src/assets/images/imgs/cert_1.png');
    background-repeat: no-repeat;
    background-size: cover;
    /* background-color:red */
    position: relative;
  }

  span,
  div {
    width: 100%;
    text-align: center;
  }

  #span1 {
    position: absolute;
    top: 37%;
    height: 13%;
    width: 100%;
    /* background-color: greenyellow;
    font-weight: 700;
    display: flex;
    align-items: center; */
  }

  #span1>span {
    position: relative;
    background-color: red;

  }

  #span2 {
    position: absolute;
    top: 66%;
    font-size: 1.3rem;
    line-height: 1.1;
    color:rgb(83,88,99);
  }
  tr{
    position: relative;
    height: 37%;
  }
  td{
    text-align: center; 
    vertical-align: middle;
    height: 12%;
    padding-left:50px ;
    padding-right: 50px;
    font-size: 2.1rem;
    font-weight: 700;
  }
  *{
    box-sizing: border-box;
    /* font-family: sans-serif; */
    /* font-family: alice; */
    font-family:alice;
    
  }
</style>

<body>
  <table id = 'span1'  >
    <tr>
      <td>
        <?php
        echo $name
        ?>
    
      

      </td>

    </tr>
    </table>

    <span id='span2'>
      Given this<br />



      <?php
      echo $day;
      echo substr($day, -1) == 1 && $day != 11 ? 'st' : ( substr($day, -1) == 2 && $day != 12?  'nd' : (substr($day, -1) == 3 && $day != 13 ? 'rd' : 'th'));
      ?>
      day of
      <?php
      echo $month . ', ';
      ?>
      in the year
      <br />
      <?php
      echo "$year.";
      ?>
    </span>
</body>

</html>