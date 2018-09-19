<?php
/*
:mode=php:

   file: season_standings_table.php
   date: apr-2016
 author: hfs
   desc: This is server call back "ajax-jason" support file.  standings.php
      has a click event attached to the two pagination displays.  The event
      first the jQuery found in mypicks.js.  This file runs the sql and returns
      the data to populate the weekly standings table located on the standings.php
      page.
                                               
*/

function getSeasonStandingsTable(
   $user_id       = 1,
   $league_id     = 147,
   $week_start    = 3,     // league value
   $week_end      = 4,     // last round or active week
   $is_spread_yn  = 'no',
   $push_points   = 0,
   $report_mode   = '',    // data, table
   &$data         = '',
   &$err          = ''
){
   $msg = '';

   writeDataToFile("BEGIN getSeasonStandingsTable(
      user_id     '$user_id', 
      week_start  '$week_start', 
      week_end    '$week_end',   
      league_id   '$league_id',  
      is_spread   '$is_spread_yn',  
      push_points '$push_points',
      report_mode '$report_mode')", __FILE__, __LINE__);

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
   if ($is_spread_yn == 'yes') {
      $mysql = $mysql_spread;
   }
   
   $ans = '';
   $data = '';
   $status_text = '';
   $season_table = '';
   $ref_status_text = '';
   $status = false;
   while (1) {
      
      if (  !$user_id ||
            !$report_mode ||
            !$week_start  ||
            !$week_end    ||
            !$league_id   ||
            !$is_spread_yn   ||
            $push_points === null ||
            $push_points === false ||
            !($is_spread_yn == 'yes' || $is_spread_yn == 'no'))
      {
         formatSessionMessage("The season standings table cannot be displayed.  Data is missing", 'danger', $msg, 
            "sst-122 '$user_id' '$report_mode' '$week_start' '$week_end' '$league_id' '$is_spread_yn' '$push_points'");
         $err = array('status' => '0', 'ermsg' => $msg);
         break;
      }
      
      if ( ($week_start < 1 || $week_start > NFL_LAST_WEEK )
        || ($week_end < 1 || $week_end > NFL_LAST_WEEK )) {
         formatSessionMessage("Week values are out of bounds.", 'danger', $msg, 
            "sst-130 '$week_start' '$week_end'");
         $err = array('status' => '0', 'ermsg' => $msg);
         break;
      }
      if ($week_end < $week_start) {   // This should be caught in the page
         formatSessionMessage("The ending week is before the start.", 'danger', $msg, 
            "sst-136 '$week_start' '$week_end'");
         $err = array('status' => '0', 'ermsg' => $msg);
         break;
      }
      
      $ans = runSql($mysql, array("diii", $push_points, $league_id, $week_start, $week_end), 0, $ref_status_text);
      if (!$ans) {
         if ($ans === false) {
            formatSessionMessage("There is no system has failed.  We are unable to display the table.", 'danger', $msg, 
               "sst-145 $ref_status_text");
            $err = array('status' => '0', 'ermsg' => $msg);
            break;
         } elseif ($ans === null) {
            $data = "<div id='IDd_noweekdata'>There is no data available for the season.</div>";
         }
      }
      $status = true;
      break;
   }
   
   if (!$status || $data) {
      return $status;
   }
   
   while (1) {
      
      if ($report_mode == 'data') {
         $data = array();
         for ($ndx = 0; $ndx <= sizeof($ans); $ndx++) {
            $data[] = array(
               'username' =>     $ans[$ndx]['user'],
               'playername'=>    $ans[$ndx]['playername'],
               'wins' =>         $ans[$ndx]['wins'],
               'losses' =>       $ans[$ndx]['losses'],
               'pushes' =>       $ans[$ndx]['push'],
               'totalscore' =>   $ans[$ndx]['totals']
               );
         }
         break;
      }
   
      if ($report_mode == 'table') {
         $rows = '';
         for ($ndx = 0; $ndx < sizeof($ans); $ndx++) {
            
            $username   = $ans[$ndx]['user'];
            $playername = $ans[$ndx]['playername'];
            $wins       = $ans[$ndx]['wins'];
            $losses     = $ans[$ndx]['losses'];
            $pushes     = $ans[$ndx]['push'];
            $total_score = $ans[$ndx]['totals'];
            $db_user_id = $ans[$ndx]['id'];
            
            if ($db_user_id == $user_id) {
               $rows .= "<tr class='Ctr_weekly' style='background-color:MistyRose;' >";
            } else {
               $rows .= "<tr class='Ctr_weekly' >";
            }
            
            $rows .= "\n
               <td class='Ctd_player'     >$playername&nbsp;&nbsp;&nbsp;</td> 
               <td class='Ctd_numbers'    >$wins</td>
               <td class='Ctd_numbers'    >$losses</td>
               <td class='Ctd_numbers'    >$pushes</td>
               <td class='Ctd_numbers'    >$total_score</td>
            </tr>";
         }
         
         if($rows == '') {
            $data = "<div id='IDd_noweekdata'>There is no data available for the season.</div>";
         } else {
            $data = "\n
            <table class='table-condensed' id='IDtable_season' style='margin-left:auto;margin-right:auto;'>
               <thead>
                  <tr>
                     <th style='text-align:center'>Player</th>
                     <th style='text-align:center'>W</th>
                     <th style='text-align:center'>L</th>
                     <th style='text-align:center'>T</th>
                     <th style='text-align:center'>Tot</th>
                  </tr>
               </thead>
               <tbody>
                 $rows
               </tbody>
            </table>"; 
         }
         break;
      }
      $status = false;
      break;
   }
   
   return $status;
}
?>