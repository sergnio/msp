<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: ajax_support_weekly_standings_table.php.php
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

writeDataToFile("ajax_support_weekly_standings_table.php :  BEGIN");

@ $do_this =      $_POST['dothis'];
@ $build_mode =   (isset($_POST['buildMode'])) ? $_POST['buildMode']  : 'rows';
@ $week =         $_POST['weekselected'];
@ $league_id =    $_POST['leagueid'];
@ $is_spread =    (isset($_POST['pointsyesno'])) ? $_POST['pointsyesno'] : 'yes';
@ $push_points =  (isset($_POST['pushpoints'])) ?  $_POST['pushpoints'] :  '0.5';

writeDataToFile("ajax_support_weekly_standings_table.php INPUT:
 do_this,    $do_this,
 build_mode, $build_mode, 
 week,       $week,     
 league_id,  $league_id,
 is_spread,  $is_spread,
 push_points $push_points", __FILE__, __LINE__);

$weekly_data = get_weekly_standings_table_data($week, $league_id, $push_points, $is_spread, $build_mode);
$status_array = array('status' => 1, 'timer' => 'just so');

writeDataToFile("status array from func: " . print_r($weekly_data, true), __FILE__, __LINE__);
echo json_encode($weekly_data);
ob_end_flush();
exit();

// Builds the tr and td elements for the WEEKLY table and returns them
// in a string.
function get_weekly_standings_table_data(
   $week,
   $league,
   $push_points,
   $is_spread,
   $build_mode = 'rows'
){

$mysql_weekly_no_spread = "
   SELECT p.user AS user,
          y.playername as player,
         group_concat(
            if (p.home_away = 'h' and (s.homescore > s.awayscore), concat('<span style=\"color:blue;\">',   s.home, '</span>'),
            if (p.home_away = 'a' and (s.awayscore > s.homescore), concat('<span style=\"color:blue;\">',   s.away, '</span>'),
            if (p.home_away = 'h' and (s.homescore < s.awayscore), concat('<span style=\"color:red;\">',    s.home, '</span>'),
            if (p.home_away = 'a' and (s.awayscore < s.homescore), concat('<span style=\"color:red;\">',    s.away, '</span>'),
            if (p.home_away = 'h' and (s.homescore = s.awayscore), concat('<span style=\"color:black;\">',  s.home, '</span>'),
            if (p.home_away = 'a' and (s.homescore = s.awayscore), concat('<span style=\"color:black;\">',  s.away, '</span>'), 
            if (s.gametime < now()and p.home_away = 'h',           concat('<span style=\"color:green;\">',  s.home, '</span>'), 
            if (s.gametime < now()and p.home_away = 'a',           concat('<span style=\"color:green;\">',  s.away, '</span>'),'???'))))))))
               SEPARATOR '-') AS picks,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + 0 ) > s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) > (s.homescore + 0))), 1, 0)) AS wins,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + 0 ) < s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) < (s.homescore + 0))), 1, 0)) AS losses,
          sum(
            if(  (p.home_away = 'h' AND ((s.homescore + 0 ) = s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) = (s.homescore + 0))), 1, 0)) AS push,
          sum(
            if(  
                 (p.home_away = 'h' AND ((s.homescore + 0 ) > s.awayscore))
              or (p.home_away = 'a' AND             ((s.awayscore) > (s.homescore + 0))),
            1,
              (if(  (p.home_away = 'h' AND ((s.homescore + 0) = s.awayscore))
                 or (p.home_away = 'a' AND          ((s.awayscore) = (s.homescore + 0))), ?, 0))
               )) AS total_points
       FROM picks AS p 
       JOIN schedules AS s USING (schedule_id)
  left join nspx_leagueplayer as y on y.leagueid = p.league_id and y.userid = (select id from users where username = p.user limit 1)
      WHERE s.week = ?
        AND p.league_id = ?
   GROUP BY p.user
   ORDER BY total_points DESC";


$mysql_weekly_spread = "
   SELECT p.user AS user,
          y.playername as player,
         group_concat(
            if (p.home_away = 'h' and ((s.homescore + spread) > s.awayscore),   concat('<span style=\"color:blue;\">',   s.home, '</span>'),
            if (p.home_away = 'a' and (s.awayscore > (s.homescore + s.spread)), concat('<span style=\"color:blue;\">',   s.away, '</span>'),
            if (p.home_away = 'h' and ((s.homescore + spread) < s.awayscore),   concat('<span style=\"color:red;\">',    s.home, '</span>'),
            if (p.home_away = 'a' and (s.awayscore < (s.homescore + s.spread)), concat('<span style=\"color:red;\">',    s.away, '</span>'),
            if (p.home_away = 'h' and ((s.homescore + s.spread) = s.awayscore), concat('<span style=\"color:black;\">',  s.home, '</span>'),
            if (p.home_away = 'a' and ((s.homescore + s.spread) = s.awayscore), concat('<span style=\"color:black;\">',  s.away, '</span>'), 
            if (s.gametime < now()and p.home_away = 'h',                        concat('<span style=\"color:green;\">',  s.home, '</span>'), 
            if (s.gametime < now()and p.home_away = 'a',                        concat('<span style=\"color:green;\">',  s.away, '</span>'),'???'))))))))
               SEPARATOR '-') AS picks,
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
               )) AS total_points
       FROM picks AS p 
       JOIN schedules AS s USING (schedule_id)
  left join nspx_leagueplayer as y on y.leagueid = p.league_id and y.userid = (select id from users where username = p.user limit 1)
      WHERE s.week = ?
        AND p.league_id = ?
   GROUP BY p.user
   ORDER BY total_points DESC";
   
   $mysql = $mysql_weekly_no_spread;
   if ($is_spread == 'yes') {
      $mysql = $mysql_weekly_spread;
   }

   $weekly_table = '';
   while (1) {
     
      // I can't figure out how this wants me to index.
      //$driver = new mysqli_driver();
      //$driver->report_mode = MYSQLI_REPORT_ALL;
      
      try {
         $conn = db_connect();
         $conn->query('SET @@group_concat_max_len = 4096');
         $sth = $conn->prepare($mysql);
         $sth->bind_param("dii", $push_points, $week, $league);
         $sth->execute();
         $weekly_table_data = array();
         $sth->bind_result($user_name, $player_name, $picks, $wins, $losses, $pushes, $total_score);
         
         if ($build_mode == 'rows') {
            while ($sth->fetch()) {
               $weekly_table_data[] = array('user_name' => $user_name, 'playername'=> $player_name,  'picks' => $picks, 
                  'wins' => $wins, 'losses' => $losses, 'pushes' => $pushes, 
                  'total_score' => $total_score);
            }
            @ $sth->close();
         } elseif ($build_mode == 'html') {
            // not supported
         } else {
            // never here
         }
         return $weekly_table_data;         

      } catch (mysqli_sql_exception $e) {
         $ermsg = "get_weekly_standings_table_data()  \n" .
            'sql: ' . $sql_color_player . "\n\n" .
            '$week = ' . $week  . ", \n" .
            '$league_id = ' . $league  . ", \n" .
            '$build_mode = ' . $build_mode  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
}
?>