<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
// require_once('mysql_min_support.php');
/*
define('URL_HOME_PAGE',  'http://localhost/nflx/index.php');
define('HOST',           'localhost');
define('DATABASE_NAME',  'mysuperpickslocal');
define('USER_NAME',      'nfllocal'); 
define('USER_PASSWORD',  'nfllocalTest1234!');
*/
/*
@ $player =    $_POST['ajaxusername'];
@ $week =      $_POST['ajaxweek'];
@ $league = $_POST['ajaxleague'];
*/
$week = 1; $league = 1;

$status_array = array('status' => 1, 'timer' => 'just so');
echo json_encode("one thing");
ob_end_flush();
exit();
// Builds the tr and td elements for the WEEKLY table and returns them
// in a string.
function get_weekly_standings_table_data(
   $week,
   $league
){
   $conn = db_connect();

   $sql = "
     SELECT p.user AS user, 
         group_concat(if(p.home_away = 'h', s.home, s.away) SEPARATOR '-') AS picks,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + s.spread ) > s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) > (s.homescore + s.spread))), 1, 0)) AS wins,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + s.spread ) < s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) < (s.homescore + s.spread))), 1, 0)) AS losses,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + s.spread ) = s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) = (s.homescore + s.spread))), 1, 0)) AS push,
          sum(
            if(  
                 (p.home_away = 'h' AND ((s.homescore + s.spread ) > s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) > (s.homescore + s.spread))),
            1,
              (if(  (p.home_away = 'h' AND ((s.homescore + s.spread ) = s.awayscore))
                 or (p.home_away = 'a' AND             ((s.awayscore) = (s.homescore + s.spread))), .5, 0))
               )) AS total_points
       FROM picks AS p JOIN schedules AS s USING (schedule_id) 
      WHERE s.week=?
        AND p.league_id = ?
   GROUP BY p.user
   ORDER BY total_points DESC";
     
   $sth = $conn->prepare($sql);
   if (!$sth) {
     @ $sth->close();
     $ermsg['ERROR_MESSAGE'] = 
        'Failed prepare() (db was connected)  \nSQL = ' . $sql;
     writeDataToFile($ermsg, __FILE__, __LINE__);
     return null;
   }
   if(!$sth->bind_param("ii", $week, $league)) {
      @ $sth->close();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      return null;
   }
   
   $sth->execute();
   if (!$sth) {
      $ermsg['ERROR_MESSAGE'] =
         'Failed execute()! (db was connected)  \nSQL = ' . $sql_source . 
         "\nErrorno:" . $sth->errno . ', Errormsg:' . $sth->error;
      writeDataToFile($ermsg, __FILE__, __LINE__);
      return null;
   }
   
   $weekly_table_data = array();
   $sth->bind_result($user_name, $picks, $wins, $losses, $pushes, $total_score);
   while ($sth->fetch()) {
      $weekly_table_data[] = array('user_name' => $user_name, 'picks' => $picks, 
         'wins' => $wins, 'losses' => $losses, 'pushes' => $pushes, 
         'total_score' => $total_score);
   }
   return $weekly_table_data;
}
function db_connect() {
   global $global_mysuperpicks_dbo;
   $global_mysuperpicks_dbo = new mysqli(HOST, USER_NAME, USER_PASSWORD, DATABASE_NAME);
   if (mysqli_connect_errno()) {
      $ermsg = array('ERROR_MESSAGE'=>'Failed to create db handle',  
         'HOST'=>HOST, 'DATABASE_NAME'=>DATABASE_NAME, 
         'USER_NAME'=>USER_NAME, 'USER_PASSWORD'=>USER_PASSWORD);
      writeDataToFile($ermsg, __FILE__, __LINE__);
   }
   if ($global_mysuperpicks_dbo) {
     return $global_mysuperpicks_dbo;
   } else {
     return false;
   }
}
?>