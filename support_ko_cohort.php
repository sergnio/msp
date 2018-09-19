<?php
//ob_start();
//header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: support_ko_cohort.php.php
   date: apr-2016
 author: hfs
   desc: 
                                               
*/
//require_once('mypicks_def.php');
//require_once('mysql_min_support.php');

$league = 1;
$league_type = 2;    // cohort 2, last man 3
$begin = 14;
$end = 17;
//$weekly_data = getKOCohortStandings($begin, $end, $league, $league_type);

//echo json_encode($weekly_data);
//ob_end_flush();
//exit();

// Builds the tr and td elements for the WEEKLY table and returns them
// in a string.
function sayKOCohortStandingsTable(
   $week_begin = 1,
   $week_end = 17,
   $league_id = 1,
   $use_vegas = false,
   &$ref_status_text = ''
   
){
   $writes_on = false;

   $league_last_round = (!empty($_SESSION['leage_lastround'])) ? $_SESSION['leage_lastround'] : '';
   $week_end = ($league_last_round != 0 && $league_last_round <= $week_end) ? $league_last_round : $week_end;
   
   if ($week_begin > $week_end) {
      formatSessionMessage("A system error has occurred.", 'danger', $msg, "wbwe$week_begin$week_end");
      setSessionMessage($msg, 'error');
      writeDataToFile("support_ko_cohort. ref:wbwe$week_begin$week_end\n" . print_r($_SEESION, true), __FILE__, __LINE__);
      break;
   }
      
/* Build this Player->week->pick->(team, win/loss)
ns-no score, wi-win, lo-loss, ti-tie
A NULL value in table schedule.homescore or schedule.awayscore is read as the no
score at all.  The ability to NULL these fields, by entering nothing in the
score fields was fixed may2016.
  
    [34Bears_L1_id28] => Array
        (
            [1] => Array
                (
                    [1] => Array
                        (
                            [0] => NE
                            [1] => ns
                        )

                    [2] => Array
                        (
                            [0] => NYJ
                            [1] => wi
                        )

                    [3] => Array
                        (
                            [0] => MIA
                            [1] => wi
                        )

                    [4] => Array
                        (
                            [0] => TEN
                            [1] => wi
                        )

                    [5] => Array
                        (
                            [0] => PHI
                            [1] => lo
                        )

                )

            [2] => Array
                (
                    [1] => Array
                        (
                            [0] => NE
                            [1] => wi
                        )

                    [2] => Array
                        (
                            [0] => ARI
                            [1] => wi
                        )

                    [3] => Array
                        (
                            [0] => CIN
                            [1] => wi
                        )

                    [4] => Array
                        (
                            [0] => MIA
                            [1] => lo
                        )

*/


   $mysqlkolm = "     
   select p.user,
          s.week,
          x.lastround,
          y.playername as player,
          if (p.home_away = 'h' and (s.homescore > s.awayscore), 'wi',
            if (p.home_away = 'a' and (s.awayscore > s.homescore), 'wi',
              if (p.home_away = 'h' and (s.homescore < s.awayscore), 'lo',
                if (p.home_away = 'a' and (s.awayscore < s.homescore), 'lo',
                  if ((p.home_away = 'h' or p.home_away = 'a') and (s.homescore = s.awayscore), 'ti', 
                    if (s.gametime > now() and p.home_away = 'h', 'sc', 
                      if (s.gametime > now() and p.home_away = 'a', 'sc',
                        'ip'))))))) as nospreadwinloss,
          if (p.home_away = 'h' and ((s.homescore + spread) > s.awayscore),   'wi',
            if (p.home_away = 'a' and (s.awayscore > (s.homescore + s.spread)), 'wi',
              if (p.home_away = 'h' and ((s.homescore + spread) < s.awayscore),   'lo',
                if (p.home_away = 'a' and (s.awayscore < (s.homescore + s.spread)), 'lo',
                  if (p.home_away = 'h' and ((s.homescore + s.spread) = s.awayscore), 'ti',
                    if (p.home_away = 'a' and ((s.homescore + s.spread) = s.awayscore), 'ti',
                      if (s.gametime > now() and p.home_away = 'h', 'sc', 
                        if (s.gametime > now() and p.home_away = 'a', 'sc', 
                          'ip')))))))) as winloss,
          if (p.home_away = 'h', s.home, s.away) as teampicked,
          if (s.gametime > now(), 'scheduled', 'started') as gamestatus
     from league x, picks as p 
     join schedules as s using (schedule_id)
left join nspx_leagueplayer as y on y.leagueid = p.league_id and y.userid = (select id from users where username = p.user limit 1)
    where p.league_id = ?
      and x.league_id = p.league_id
      and s.week >= ?
      and s.week <= ?
      and x.active = 1
      and y.active = 2
    order by p.user, s.week, s.gametime, teampicked";    
  
   $mysqlx = "
   SELECT p.user,
          s.week,
          y.playername as player,
            if (p.home_away = 'h' and ((s.homescore + spread) > s.awayscore),   'wi',
            if (p.home_away = 'a' and (s.awayscore > (s.homescore + s.spread)), 'wi',
            if (p.home_away = 'h' and ((s.homescore + spread) < s.awayscore),   'lo',
            if (p.home_away = 'a' and (s.awayscore < (s.homescore + s.spread)), 'lo',
            if (p.home_away = 'h' and ((s.homescore + s.spread) = s.awayscore), 'ti',
            if (p.home_away = 'a' and ((s.homescore + s.spread) = s.awayscore), 'ti', 'ns'))))))
               AS winloss,
            if (p.home_away = 'h', s.home, s.away) as teampicked
       FROM picks AS p 
       JOIN schedules AS s USING (schedule_id)
  left join nspx_leagueplayer as y on y.leagueid = p.league_id and y.userid = (select id from users where username = p.user limit 1)
      WHERE  p.league_id = ?
        and s.week >= ?
        and s.week <= ?
  order by p.user, s.week, s.gametime, teampicked";
  
   $mysqlo = "  
     select p.user,
          s.week,
          x.lastround,
          y.playername as player,
          if (p.home_away = 'h' and (s.homescore > s.awayscore), 'wi',
            if (p.home_away = 'a' and (s.awayscore > s.homescore), 'wi',
              if (p.home_away = 'h' and (s.homescore < s.awayscore), 'lo',
                if (p.home_away = 'a' and (s.awayscore < s.homescore), 'lo',
                  if (p.home_away = 'h' or p.home_away = 'a' and (s.homescore = s.awayscore), 'ti', 'ns'))))) as nospreadwinloss,
          if (p.home_away = 'h' and ((s.homescore + spread) > s.awayscore),   'wi',
            if (p.home_away = 'a' and (s.awayscore > (s.homescore + s.spread)), 'wi',
              if (p.home_away = 'h' and ((s.homescore + spread) < s.awayscore),   'lo',
                if (p.home_away = 'a' and (s.awayscore < (s.homescore + s.spread)), 'lo',
                  if (p.home_away = 'h' and ((s.homescore + s.spread) = s.awayscore), 'ti',
                    if (p.home_away = 'a' and ((s.homescore + s.spread) = s.awayscore), 'ti', 'ns'))))))
               as winloss,
            if (p.home_away = 'h', s.home, s.away) as teampicked
       from league x, picks as p 
       join schedules as s using (schedule_id)
  left join nspx_leagueplayer as y on y.leagueid = p.league_id and y.userid = (select id from users where username = p.user limit 1)
      where p.league_id = ?
        and x.league_id = p.league_id
        and s.week >= ?
        and s.week <= ?
  order by p.user, s.week, s.gametime, teampicked";
  
  
  
  $mysql = "
   select p.user,
          s.week,
          x.lastround,
          y.playername as player,
          if (p.home_away = 'h' and (s.homescore > s.awayscore), 'wi',
            if (p.home_away = 'a' and (s.awayscore > s.homescore), 'wi',
              if (p.home_away = 'h' and (s.homescore < s.awayscore), 'lo',
                if (p.home_away = 'a' and (s.awayscore < s.homescore), 'lo',
                  if ((p.home_away = 'h' or p.home_away = 'a') and (s.homescore = s.awayscore), 'ti', 
                    if (s.gametime >= now() and p.home_away = 'h', 'sc', 
                      if (s.gametime >= now() and p.home_away = 'a', 'sc',
                        'ip'))))))) as nospreadwinloss,
          if (p.home_away = 'h' and ((s.homescore + spread) > s.awayscore),   'wi',
            if (p.home_away = 'a' and (s.awayscore > (s.homescore + s.spread)), 'wi',
              if (p.home_away = 'h' and ((s.homescore + spread) < s.awayscore),   'lo',
                if (p.home_away = 'a' and (s.awayscore < (s.homescore + s.spread)), 'lo',
                  if (p.home_away = 'h' and ((s.homescore + s.spread) = s.awayscore), 'ti',
                    if (p.home_away = 'a' and ((s.homescore + s.spread) = s.awayscore), 'ti',
                      if (s.gametime >= now() and p.home_away = 'h', 'sc', 
                        if (s.gametime >= now() and p.home_away = 'a', 'sc', 
                          'ip')))))))) as winloss,
          if (p.home_away = 'h', s.home, s.away) as teampicked,
          if (s.gametime >= now(), 'scheduled', 'started') as gamestatus
     from league x, picks as p 
     join schedules as s using (schedule_id)
left join nspx_leagueplayer as y on y.leagueid = p.league_id and y.userid = (select id from users where username = p.user limit 1)
    where p.league_id = ?
      and x.league_id = p.league_id
      and s.week >= ?
      and s.week <= ?
      and x.active = 1
      and y.active = 2
    order by p.user, s.week, s.gametime, teampicked";    
  
   $sql_players = "
      select y.playername,
             y.userid
        from nspx_leagueplayer as y, league as g
       where y.leagueid = ?
         and y.active = 2
         and y.leagueid = g.league_id
    order by y.playername
  
   $sql_players = "
      select y.playername,
             y.userid
        from nspx_leagueplayer as y, league as g
       where y.leagueid = ?
    order by y.playername";  
  
   
   $weekly_table = '';
   $status = 0;
   $ref_status_text = '';
   $weekly_table_data = array();
   $user_id = 1;
   $a_main = array();
   $a_playersx = array();
   $are_no_players = true;
   while (1) {
      
      try {
         
          //$driver = new mysqli_driver();
          //$driver->report_mode = MYSQLI_REPORT_ALL;     
          
         $conn = db_connect();
         if (!$conn) {
            $ref_status_text = 'dbconnect';
            break;
         }
         $mysql_running = $sql_players;
         if (!($sth = $conn->prepare($sql_players))) {
            $ref_status_text = 'prep';
            break;
         }
         if (!$sth->bind_param("i", $league_id)) {
            $ref_status_text = 'bindparam';
            break;
         }
         if (!$sth->execute()) {
            $ref_status_text = 'execute';
            break;
         }
         if (!$sth->bind_result($player_name, $user_id)) {
            $ref_status_text = 'bind_result';
            break;
         }
         
         // build all players name with week indexes
         //[bencarlton_L1_id] => Array
         //(
         //    [1] => Array
         //        (
         //        )
         //
         //    [2] => Array
         //        (
         //        )
         while($sth->fetch()) {
            $a_playersx[$player_name] = array();
            for ($i = $week_begin; $i <= $week_end; $i++) {
               $a_playersx[$player_name][$i] = array();
            }
         }          
         $sth->close();
            
         $conn = db_connect();
         if (!$conn) {
            $ref_status_text = 'dbconnect';
            break;
         }
         $mysql_running = $mysql;
         if (!($sth = $conn->prepare($mysql))) {
            $ref_status_text = 'prep';
            break;
         }
         if (!$sth->bind_param("iii", $league_id, $week_begin, $week_end)) {
            $ref_status_text = 'bind_param';
            break;
         }
         if (!$sth->execute()) {
            $ref_status_text = 'execute';
            break;
         }
         if (!$sth->bind_result($user_name, $week, $last_round2, $player_name, $win_loss_push_no_spread, $win_loss_push, $team)) {
            $ref_status_text = 'bind_result';
            break;
         }
         
         // build all players name with week indexes.  There is only one pick per/week.
         //[bencarlton_L1_id] => Array
         //(
         //    [1] => Array
         //        (
         //        )
         //
         //    [2] => Array
         //        (
         //        )
         while ($sth->fetch()) {
            $are_no_players = false;
            $winloose = ($use_vegas) ? $win_loss_push : $win_loss_push_no_spread;
            $pick = array($team, $winloose);
            $a_playersx[$player_name][$week] = $pick;
         }
          
         @ $sth->close(); 

      } catch (mysqli_sql_exception $e) {
         $ermsg = "sayKOCohortStandingsTable()  \n" .
            'sql: ' . $mysql_running . "\n\n" .
            '$week begin = ' . $week_begin  . ", \n" .
            '$week end = ' . $week_end  . ", \n" .
            '$league_id = ' . $league_id  . ", \n" .
            'vegas = ' . $use_vegas   . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }
   
   if (!$status) {
      formatSessionMessage("There was a serious error.", 'danger', $msg, $ref_status_text);
      setSessionMessage($msg, 'error');
      return 0;
   }
      
   if ($are_no_players) {  // Not likely given all users must belong to a league.  Active?  Failure to install properly?
      $ref_status_text = 'noplay';
      formatSessionMessage("No members of this league have yet made a play.", 'info', $msg);
      setSessionMessage($msg, 'error');
      return 0;
   }
   
   // build ...
   //[34Bears_L1_id28] => Array
   // (
   //     [1] => Array
   //         (
   //             [1] => Array
   //                 (
   //                     [0] => NE
   //                     [1] => ns
   //                 )
   //
   //             [2] => Array
   //                 (
   
   $a_winloss_lookahead = '';
   foreach ($a_playersx as $player => $a) {
      $player_status = 'in';
      for ($w = $week_begin; $w <= $week_end; $w++) {
         if ($player_status == 'out') {
            $a_winloss_lookahead[$player][$w] = array('out', '-');
            continue;
         }
         if (isset($a_playersx[$player][$w][1])) {  //   // player->week->[team, winloose]
            $winloss = $a_playersx[$player][$w][1];
            $team = $a_playersx[$player][$w][0];;
            if ($winloss == 'wi' || $winloss == 'ns') {
               $a_winloss_lookahead[$player][$w] = array('in', $team);
               continue;
            } else {
               $player_status = 'out';
               $a_winloss_lookahead[$player][$w] = array('out', $team);
               continue;
            }
         } else {  // not set - he's out
            $player_status = 'out';
            $a_winloss_lookahead[$player][$w] = array('out', '-');
            continue;
         }  
      }
   }
   //print_r($a_winloss_lookahead);
   //echo '<br />';
   //foreach ($player_keys as $player) {
   //   for ($w = $week_begin; $w <= $week_end; $w++) {
   //      echo "First pick week $w for $player is: " . $a_winloss_lookahead[$player][$w][0] . "<br />";
   //   }
   //}

   // Last cohort, find winners
   $table_rows = '';
   foreach ($a_playersx as $player => $a) {
      
      $winners_count = '';
      $winners_count_next_week = '';
      $player_wins = false;
      $player_status = 'in';
      $player_row = '';
      for ($w = $week_begin; $w <= $week_end; $w++) {
         
         if ($winners_count == '' && $w < $week_end) {
            $winners_count = winnersWeekCohort($player_keys, $a_winloss_lookahead, $w);
            $winners_count_next_week = winnersWeekCohort($player_keys, $a_winloss_lookahead, ($w + 1));
         } elseif ($w < $week_end) {
            $winners_count = $winners_count_next_week;
            $winners_count_next_week = winnersWeekCohort($player_keys, $a_winloss_lookahead, ($w + 1));
         } elseif ($w == $week_end) {
            $winners_count = $winners_count_next_week;
            $winners_count_next_week = 0;
         }
         
         
         $player_status = $a_winloss_lookahead[$player][$w][0];
         $team =$a_winloss_lookahead[$player][$w][1];
         
         if ($winners_count == 1) {
            $player_row[$player][$w] = "         <td winloss='$player_status' db='wc1'>$team</td>\n";
            if ($player_status == 'in') {
               $player_wins = true; 
            }
            continue;
         }
         if ($winners_count > 1 ) {
            if ($winners_count_next_week == 0) {
               $player_row[$player][$w] = "         <td winloss='$player_status' db='wc+1nw0'>$team</td>\n";
               if ($player_status == 'in') {
                  $player_wins = true;
               }
               continue;
            }
            if ($winners_count_next_week > 0) {
               $player_row[$player][$w] = "         <td winloss='$player_status' db='nw+1'>$team</td>\n";
               continue;
            }
         }
         if ($winners_count == 0) {
            $player_row[$player][$w] = "         <td winloss='$player_status' db='wc0'>$team</td>\n";
         }
      } // END weeks
      
      $tdplayerstatus = '';
      if ($player_wins) {
         $tdplayerstatus = "playerstatus='win'";
      }
      $table_rows .= "      <tr>\n";
      $table_rows .= "         <td name='playername' $tdplayerstatus >$player</td>\n";
      
      for($i = $week_begin; $i <= $week_end; $i++) {
         $table_rows .= $player_row[$player][$i];
      }
      $table_rows .= "      </tr>\n";
      
   } // END players
   
   $week_headers = '';
   for ($ndx = $week_begin; $ndx <= $week_end; $ndx++) {
      $week_headers .= "         <th>&nbsp;&nbsp;Week $ndx&nbsp;&nbsp;</th>\n";
   }
   //<table id='IDtable_singleWeek' style='margin-left:auto;margin-right:auto;'>
   $table = "
<table id='IDtable_ko_cohort' style='margin-left:auto;margin-right:auto;'>
   <thead>
      <tr>
         <th style='text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Player&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>\n";
   $table .= $week_headers;
   $table .= "      </tr>
   </thead>
   <tbody>\n";
   
   //////////////////////////////////////////////////////////////////////////////////////// build the table rows
   
   $table .= $table_rows;
   
$table .= "   </tbody>\n";
$table .= "</table>\n";

echo $table;

}

function winnersWeekCohort(   // r - 0,1,2
   &$players,
   &$a_lookahead,
   $w
){
   $count = 0;
   foreach($players as $player) {
      if ($a_lookahead[$player][$w][0] == 'in') {
         if ($count++ > 0) {
            return $count;
         }
      }
   }
   return $count;
}

?>