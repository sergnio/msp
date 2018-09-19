<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

$id = $_POST['id'];
$id = sql_sanitize($id);
$id = html_sanitize($id);
$gametime = $_POST['gametime'];
$gametime = sql_sanitize($gametime);
$gametime = html_sanitize($gametime);
$gametime = date("Y-m-d H:i:s", strtotime($gametime));
$home = $_POST['home'];
$home = sql_sanitize($home);
$home = html_sanitize($home);
$away = $_POST['away'];
$away = sql_sanitize($away);
$away = html_sanitize($away);
$spread = $_POST['spread'];
$spread = sql_sanitize($spread);
$spread = html_sanitize($spread);
$homescore = $_POST['homescore'];
$homescore = sql_sanitize($homescore);
$homescore = html_sanitize($homescore);
$awayscore = $_POST['awayscore'];
$awayscore = sql_sanitize($awayscore);
$awayscore = html_sanitize($awayscore);
$week = $_POST['week'];
$week = sql_sanitize($week);
$week = html_sanitize($week);
if(check_valid_user_admin()) {
   $conn = db_connect();
   $result = $conn->query("UPDATE schedules SET gametime='$gametime', home='$home', away='$away', spread='$spread', homescore='$homescore', awayscore='$awayscore' WHERE schedule_id='$id'");
   mysqli_close($conn);
   header( 'Location: schedules.php?update=1&week='.$week ) ;
   die();
} else {
   header( 'Location: schedules.php?update=0' ) ;
   die();
}
?>