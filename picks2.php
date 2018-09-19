<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

$picks = $_POST['my_pick'];
if(empty($picks)) {
   header( 'Location: picks.php?update=0' ) ;
   die();  
} else {
   $N = count($picks);
   for($i=0; $i < $N; $i++) {
      $pick1 = explode('-', $picks[$i]);
      $schedule_id1 = $pick1[0]; 
      $pick2 = explode('-', $picks[$i+1]);
      $schedule_id2 = $pick2[0]; 
      if ($schedule_id1 == $schedule_id2) {
         $_SESSION['pick_array']=$picks;
         header( 'Location: picks.php?update=error' ) ;
         die();  
      }
   }
    /*
   $conn = db_connect();
   $N = count($picks);
   for($i=0; $i < $N; $i++) {
      $pick = explode('-', $picks[$i]);
      $schedule_id = $pick[0]; 
      $result = $conn->query("SELECT * FROM schedules WHERE schedule_id='$schedule_id'");    
      $now = date("YmdHi");
      $row=$result_picks->fetch_object();
      $gametime = date("YmdHi", strtotime($row->gametime));
      if ($now > $gametime) {
         $_SESSION['pick_array']=$picks;
         header( 'Location: picks.php?update=error2' ) ;
         die();  
      }
   }
   mysqli_close($conn);
   $toaddress = $_SESSION['email'];
   $subject = "Your Picks for ".date("m-d-Y");
   $mailcontent = $picks;
   $fromaddress = "From: info@mysuperpicks.com";
   mail($toaddress, $subject, $mailcontent, $fromaddress);
*/
   $conn = db_connect();
   $my_picks = '';
   $N = count($picks);
   for($i=0; $i < $N; $i++) {
      $pick = explode('-', $picks[$i]);
      $homeaway = $pick[1];
      $schedule_id = $pick[0]; 
      $result_check = $conn->query("SELECT * FROM picks WHERE user='".$_SESSION['valid_user']."'  AND league_id = '".$_SESSION['league_id']."'");
      while ($row=$result_check->fetch_object()) {
         $id = $row->schedule_id;
         if ($schedule_id==$id) {
            header( 'Location: mypicks.php?update=4' ) ;
            die();
         }
      }
   
      $result = $conn->query("INSERT INTO picks VALUES ('', '".$_SESSION['valid_user']."', '".$homeaway."', '".$schedule_id."', NOW(), '".$_SESSION['league_id']."')");   
      $result2 = $conn->query("SELECT * FROM schedules WHERE schedule_id='$schedule_id'");   
      $row=$result2->fetch_object();
      if ($homeaway == "h") { $my_picks .= $row->home.", "; }
      if ($homeaway == "a") { $my_picks .= $row->away.", "; }
      $this_week = $row->week;
   }
   $my_picks = rtrim($my_picks, ', ');
   //$toaddress = $_SESSION['email'];
   $toaddress = 'shedd2013@yahoo.com';
   $subject = "Your Picks for ".date("m-d-Y");
   $mailcontent = "Here are the picks you submitted for League ".$_SESSION['league_name']." for Week ".$this_week.": ".$my_picks;
   //$fromaddress = "From: info@mysuperpicks.com";
   $fromaddress = "From: drillbrain@drillbrain.com";
   $result3 = $conn->query("INSERT INTO sent_email VALUES ('', '".$toaddress."', '".$mailcontent."', '".$_SESSION['league_id']."')");   
   mysqli_close($conn);
   mail($toaddress, $subject, $mailcontent, $fromaddress);
   header( 'Location: mypicks.php?update=1' ) ; 
   die();
}
?>