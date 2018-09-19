<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: standings_table_weekly_ajax.php
   date: apr-2016
 author: hfs
   desc: This is server call back "ajax-jason" support file.  standings.php
      has a click event attached to the two pagaination displays.  The event
      first the jQuery found in mypicks.js.  This file runs the sql and returns
      the data to populate the weekly standings table located on the standings.php
      page.
                                               
*/
require_once('mypicks_def.php');
require_once('mysql_min_support.php');

// Defaults for testing
$week = 1; $league = 1;

@ $do_this =  $_POST['dothis'];
@ $build_mode =   (isset($_POST['buildMode'])) ? $_POST['buildMode']  : 'rows';
@ $week =   $_POST['week'];
@ $player = $_POST['player'];
@ $league = $_POST['league'];

writeDataToFile("do, mode, week, player, leage " .
 $do_this . ", " .
 $build_mode . ", " .
 $week . ", " .
 $player . ", " .
 $league  . ", ", __FILE__, __LINE__);


$weekly_data = get_weekly_standings_table_data($week, $league, $build_mode);
$status_array = array('status' => 1, 'timer' => 'just so');

//array_unshift($weekly_data, $status_array);

//writeDataToFile(print_r($weekly_data, true), __FILE__, __LINE__);

echo json_encode($weekly_data);
ob_end_flush();
exit();

// Builds the tr and td elements for the WEEKLY table and returns them
// in a string.
function get_weekly_standings_table_data(
   $week,
   $league,
   $build_mode = 'rows'
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
   
   if ($build_mode == 'rows') {
      while ($sth->fetch()) {
         $weekly_table_data[] = array('user_name' => $user_name, 'picks' => $picks, 
            'wins' => $wins, 'losses' => $losses, 'pushes' => $pushes, 
            'total_score' => $total_score);
      }
   } elseif ($build_mode == 'html') {
      // not supported
   } else {
      // never here
   }
   return $weekly_table_data;
}
?>