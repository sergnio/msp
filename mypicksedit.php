<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

$pick_id = $_POST['pick_id'];
$pick_id = sql_sanitize($pick_id);
$pick_id = html_sanitize($pick_id);
$new_pick = $_POST['new_pick'];
$new_pick = sql_sanitize($new_pick);
$new_pick = html_sanitize($new_pick);
$pick = explode('-', $new_pick);
$homeaway = $pick[1];
$schedule_id = $pick[0]; 
if(check_valid_user()) {
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM picks WHERE user='".$_SESSION['valid_user']."'  AND league_id = '".$_SESSION['league_id']."'");
   while ($row=$result->fetch_object()) {
      $id = $row->schedule_id;
      if ($schedule_id==$id) {
         header( 'Location: mypicks.php?update=3' ) ;
         die();
      }
   }
   mysqli_close($conn);
   $conn = db_connect();
   $result = $conn->query("UPDATE picks SET home_away='$homeaway', schedule_id='$schedule_id' WHERE pick_id='$pick_id'");
   mysqli_close($conn);
   header( 'Location: mypicks.php?update=1' ) ;
   die();
} else {
   header( 'Location: mypicks.php?update=0' ) ;
   die();
}
?>