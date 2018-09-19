<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: ajax_support_season_standings_table.php.php
   date: apr-2016
 author: hfs
   desc: This is server call back "ajax-jason" support file.  standings.php
      has a click event attached to the two pagination displays.  The event
      first the jQuery found in mypicks.js.  This file runs the sql and returns
      the data to populate the weekly standings table located on the standings.php
      page.
                                               
*/
require_once('mypicks_def.php');
require_once('mysql_min_support.php');
$msg = '';

@ $do_this     =  $_POST['dothis'];
@ $end_week    =  $_POST['selectedweek'];   // the selected week is the endpoint
@ $start_week  =  $_POST['firstweek'];
@ $league_id   =  $_POST['leagueid'];
@ $is_spread   =  (isset($_POST['pointsyesno'])) ? $_POST['pointsyesno'] : 'yes';
@ $push_points =  (isset($_POST['pushpoints'])) ?  $_POST['pushpoints'] :  '0.5';

writeDataToFile("ajax_support_season_standings_table.php  INPUT:
do_this       $do_this,     
end_week      $end_week,    
start_week    $start_week,  
league_id     $league_id,   
is_spread     $is_spread,   
push_points   $push_points,", __FILE__, __LINE__); 


if (  !$do_this     ||
      !$end_week        ||
      !$start_week  ||
      !$end_week    ||
      !$league_id   ||
      !$is_spread   ||
      $push_points === null ||
      $push_points === false)
{
   $status_text = 'missing data';
   $err = array('status' => '0', 'ermsg' => $status_text);
   echo json_encode($err);
   ob_end_flush();
   exit();
}

$season_data = get_season_standings_table_data($start_week, $end_week, $league_id, $is_spread, $push_points);
writeDataToFile("get_season_standings_table_data() " . print_r($season_data, true), __FILE__, __LINE__);
$status_array = array('status' => 1, 'timer' => 'just so');

echo json_encode($season_data);
ob_end_flush();
exit();

// Builds the tr and td elements for the WEEKLY table and returns them
// in a string.
function get_season_standings_table_data(
   $week_start = 3,
   $week_end = 4,
   $league_id = 147,
   $is_spread = 'no',
   $push_points = 0
){

   writeDataToFile("get_season_standings_table_data(
   $week_start,
   $week_end,
   $league_id,
   $is_spread,
   $push_points)", __FILE__, __LINE__);

   // This is failing index in report all
   $mysql = '';
   $mysql_spread = "
     SELECT p.user AS user, u.id, x.playername,
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
                 or (p.home_away = 'a' AND             ((s.awayscore) = (s.homescore + s.spread))), ?, 0))
               )) AS totals
       FROM picks AS p 
  left join schedules as s on p.schedule_id = s.schedule_id
  left join users u on u.username = p.user
  left join nspx_leagueplayer x on x.userid = u.id and x.leagueid = p.league_id
      WHERE p.league_id = ?
        and s.week >= ?
        and s.week <= ?
   GROUP BY p.user
   ORDER BY totals DESC";   
   
   
   $mysql_no_spread = " 
     SELECT p.user AS user, u.id, x.playername,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + 0 ) > s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) > (s.homescore + 0 ))), 1, 0)) AS wins,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + 0 ) < s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) < (s.homescore + 0 ))), 1, 0)) AS losses,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + 0 ) = s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) = (s.homescore + 0 ))), 1, 0)) AS push,
          sum(
            if(  
                 (p.home_away = 'h' AND ((s.homescore + 0 ) > s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) > (s.homescore + 0 ))),
            1,
              (if(  (p.home_away = 'h' AND ((s.homescore + 0 ) = s.awayscore))
                 or (p.home_away = 'a' AND             ((s.awayscore) = (s.homescore + 0 ))), ?, 0))
               )) AS totals
       FROM picks AS p 
  left join schedules as s on p.schedule_id = s.schedule_id
  left join users u on u.username = p.user
  left join nspx_leagueplayer x on x.userid = u.id and x.leagueid = p.league_id
      WHERE p.league_id = ?
        and s.week >= ?
        and s.week <= ?
   GROUP BY p.user
   ORDER BY totals DESC";
   
   
   $mysql = $mysql_no_spread;
   if ($is_spread == 'yes') {
      $mysql = $mysql_spread;
   }
   
   $status = 0;  // TODO
   $status_text = '';
   $season_table = '';
   while (1) {
      
      if ($week_start < 1 || $week_start > 17) {
         $status_text = 'Week start is out of bounds.';
         $err = array('status' => '0', 'ermsg' => $status_text);
         return $err;
      }
      if ($week_end < $week_start) {
         $status_text = 'Week end before start.';
         $err = array('status' => '0', 'ermsg' => $status_text);
         return $err;
      }
         
      try {
         $conn = db_connect();
         if (!$sth = $conn->prepare($mysql)) {
            $status_text = "prep failed";
            $err = array('status' => '0', 'ermsg' => $status_text);
            return $err;
         }
         if (!$sth->bind_param("diii", $push_points, $league_id, $week_start, $week_end)) {
            $status_text = "bind_param(diii, $push_points, $league_id, $week_start, $week_end)";
            $err = array('status' => '0', 'ermsg' => $status_text);
            return $err;
         }
         $sth->execute();
         $sth->bind_result($user_name, $user_id, $player_name, $wins, $losses, $pushes, $total_score);
         
         $weekly_table_data = array();
         while ($sth->fetch()) {
            $season_table[] = array(
               'username' => $user_name,
               'playername'=> $player_name,
               'wins' => $wins, 
               'losses' => $losses, 
               'pushes' => $pushes, 
               'totalscore' => $total_score);
         }
         @ $sth->close();
         writeDataToFile($season_table, __FILE__, __LINE__);
         return $season_table;         

      } catch (mysqli_sql_exception $e) {
         $ermsg = "get_season_standings_table_data.php \n" .
            'sql: ' . $mysql . "\n\n" .
            '$week_start = ' . $week_start  . ", \n" .
            '$league_id = ' . $league_id  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }
}
?>