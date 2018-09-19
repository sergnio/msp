<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

$week = $_POST['week'];
$week = sql_sanitize($week);
$week = html_sanitize($week);
$add_pick = $_POST['add_pick'];
$add_pick = sql_sanitize($add_pick);
$add_pick = html_sanitize($add_pick);
$pick = explode('-', $add_pick);
$homeaway = $pick[1];
$schedule_id = $pick[0]; 
if(check_valid_user()) {
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM picks WHERE user='".$_SESSION['valid_user']."'  AND league_id = '".$_SESSION['league_id']."'");
   while ($row=$result->fetch_object()) {
      $id = $row->schedule_id;
      if ($schedule_id==$id) {
         header( 'Location: picks.php?update=3' ) ;
         die();
      }
   }
   mysqli_close($conn);
   $conn = db_connect();
   $result = $conn->query("INSERT INTO picks VALUES ('', '".$_SESSION['valid_user']."', '".$homeaway."', '".$schedule_id."', NOW(), '".$_SESSION['league_id']."')");
   mysqli_close($conn);
   header( 'Location: picks.php?update=1' ) ;
   die();
} else {
   header( 'Location: picks.php' ) ;
   die();
}
?>