<?php

function getWeeklyStandingsTable(
   $user_name,
   $week,
   $league_id,
   $push_points,
   $is_spread_yn,
   $report_mode   = '',    // data, table
   &$data         = '',
   &$err          = ''
){

   $msg = '';
   
   writeDataToFile("BEGIN weekly_standings_table.php(
      user_name    '$user_name',
      wee,         '$week',
      league_id    '$league_id',
      push_points  '$push_points',
      is_spread_yn '$is_spread_yn',
      report_mode  '$report_mode'", __FILE__, __LINE__);  

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
   if ($is_spread_yn == 'yes') {
      $mysql = $mysql_weekly_spread;
   }

   $ans = '';
   $data = '';
   $err = '';
   $ref_status_text = '';
   $status = false;
   while (1) {
     
      if (  !$week                 ||
            !$user_name            ||
            !$league_id            ||
            !$report_mode          ||
            $push_points === null  ||
            $push_points === false ||
            !($is_spread_yn == 'yes' || $is_spread_yn == 'no'))
      {
         formatSessionMessage("The weekly standings table cannot be displayed.  Data is missing.", 'danger', $msg, 
            "wst-115 '$user_name' '$week' '$league_id' '$report_mode' '$push_points' '$is_spread_yn'");
         $err = array('status' => '0', 'ermsg' => $msg);
         break;
      }
      writeDataToFile("weekly .....................116", __FILE__, __LINE__);
      if ( $week < 1 || $week > NFL_LAST_WEEK ) {
         formatSessionMessage("Week is out of bounds.", 'danger', $msg, 
            "wst-122 '$week' '$week_end'");
         $err = array('status' => '0', 'ermsg' => $msg);
         break;
      }
      writeDataToFile("weekly .....................123", __FILE__, __LINE__);
      if (!($report_mode == 'data' || $report_mode == 'table')) {
         formatSessionMessage("Unknow command request made.", 'danger', $msg, 
            "wst-128 '$report_mode'");
         $err = array('status' => '0', 'ermsg' => $msg);
         break;
      }
      writeDataToFile("weekly .....................130", __FILE__, __LINE__);
      
      $ans = runSql($mysql, array("dii", $push_points , $week, $league_id), 0, $ref_status_text);
      writeDataToFile("weekly .....................130++", __FILE__, __LINE__);
      if (!$ans) {
      writeDataToFile("weekly .....................130-- $ref_status_text", __FILE__, __LINE__);
         if ($ans === false) {
            formatSessionMessage("We are unable to display the table.", 'danger', $msg, 
               "wst-137 $ref_status_text");
            $err = array('status' => '0', 'ermsg' => $msg);
            break;
         } elseif ($ans === null) {
            $data = "<div id='IDd_noweekdata'>There is no data available for week $week.</div>";
         } else {
            formatSessionMessage("???.", 'danger', $msg, 
               "wst-146 $ref_status_text");
            $err = array('status' => '0', 'ermsg' => $msg);
            break;
         }
      }
      $status = true;
      break;
   }
   
      writeDataToFile("weekly .....................148", __FILE__, __LINE__);
   if (!$status || $data) {
      return $status;
   }
   
      writeDataToFile("weekly .....................151", __FILE__, __LINE__);
   $status = false;
   while (1) {
   
      if ($report_mode == 'data') {
         
         $data = '';
         for ($ndx = 0; $ndx < sizeof($ans); $ndx++) {
            $user_name      =    $ans[$ndx]['user'];
            $player_name    =    $ans[$ndx]['player'];
            $picks          =    $ans[$ndx]['picks'];
            $wins           =    $ans[$ndx]['wins'];
            $losses         =    $ans[$ndx]['losses'];
            $pushes         =    $ans[$ndx]['push'];
            $total_score    =    $ans[$ndx]['total_points'];
            
            $data[] = array('user_name' => $user_name, 'playername'=> $player_name,  'picks' => $picks, 
               'wins' => $wins, 'losses' => $losses, 'pushes' => $pushes, 
               'total_score' => $total_score);
         }
         $status = true;
         break;
      }
      
      if ($report_mode == 'table') {
      
      writeDataToFile("weekly .....................116", __FILE__, __LINE__);
         $rows = '';
         for ($ndx = 0; $ndx < sizeof($ans); $ndx++) {
            
            $db_user_name  = $ans[$ndx]['user'];
            $player_name   = $ans[$ndx]['player'];
            $wins          = $ans[$ndx]['wins'];
            $losses        = $ans[$ndx]['losses'];
            $pushes        = $ans[$ndx]['push'];
            $total_score   = $ans[$ndx]['total_points'];
            $picks         = $ans[$ndx]['picks'];
            
            if ($db_user_name == $user_name) {
               $rows .= "<tr class='Ctr_weekly' style='background-color:MistyRose;' >";
            } else {
               $rows .= "<tr class='Ctr_weekly' >";
            }
            
            $rows .= "
                  <td class='Ctd_player' >$player_name&nbsp;&nbsp;&nbsp;</td>
                  <td class='Ctd_numbers'>$wins</td>
                  <td class='Ctd_numbers'>$losses</td>
                  <td class='Ctd_numbers'>$pushes</td>
                  <td class='Ctd_numbers'>$total_score</td>
                  <td class='Ctd_weeklyPicks'>$picks</td>
               </tr>";
         }
         $data = "
         <table class='table-condensed' id='IDtable_singleWeek' style='margin-left:auto;margin-right:auto;'>
            <thead>
               <tr>
                  <th style='text-align:center'>Player</th>
                  <th style='text-align:center'>W</th>
                  <th style='text-align:center'>L</th>
                  <th style='text-align:center'>T</th>
                  <th style='text-align:center'>Tot</th>
                  <th style='text-align:center;'>Picks</th>
               </tr>
            </thead>
            <tbody>
               $rows
            </tbody>
         </table>";
      
         $status = true;
         break;
      }
      break;
   }
   return $status;
}
?>