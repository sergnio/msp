<?php
//header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: ajax_support_weekly_ko_standings_table.php.php
   date: apr-2016
 author: hfs
   desc: This is server call back "ajax-jason" support file.  standings_ko.php
      has a click event attached to the two pagaination displays.  The event
      first the jQuery found in mypicks.js.  This file runs the sql and returns
      the data to populate the weekly standings table located on the standings.php
      page. 
                                               
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
function getKOCohortStandings(
   $week_begin = 1,
   $week_end = 17,
   $league_id = 1,
   $league_type = 2  // 2 == cohort, 3 == last man
){
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

   $mysql = "
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
  order by p.user, s.week, s.gametime";
  
   
   $weekly_table = '';
   $status = 0;
   $ref_status_text = '';
   $weekly_table_data = array();
   $user_id = 1;
   $a_main = array();
   while (1) {
      
      try {
          //$driver = new mysqli_driver();
          //$driver->report_mode = MYSQLI_REPORT_ALL;
         
         
         $conn = db_connect();
         if (!$conn) {
            $ref_status_text = 'dbconnect';
            break;
         }
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
         if (!$sth->bind_result($user_name, $week, $player_name, $win_loss_push, $team)) {
            $ref_status_text = 'bind_result';
            break;
         }
         
         $name_break = '';
         $week_break = '';
         $a_week = array();
         $a_players = array();
         $pick = 1;
         while ($sth->fetch()) {
            if ($name_break == '') {
               $name_break = $player_name;
               $week_break = $week;
               $a_players[$player_name] = 1;
            }
            if ($player_name != $name_break) {     // week changes too
               $a_players[$player_name] = 1;
               $a_main["$name_break"][$week_break] = $a_week;
               $name_break = $player_name;
               $week_break = $week;
               $a_week = array();
               $pick = 1;
            }
            if ($week_break != $week) {
               $a_main["$player_name"][$week_break] = $a_week;
               $week_break = $week;
               $a_week = array();
               $pick = 1;
            }
            $a_week[$pick++] = array($team, $win_loss_push);
         }
          
         @ $sth->close(); 
         //print_r($a_main);

      } catch (mysqli_sql_exception $e) {
         $ermsg = "get_ko_standings_table_data()  \n" .
            'sql: ' . $mysql . "\n\n" .
            '$week begin = ' . $week_begin  . ", \n" .
            '$week end = ' . $week_end  . ", \n" .
            '$league_id = ' . $league_id  . ", \n" .
            'league_type = ' . $league_type  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   
   //print_r($a_main);
   //print_r($a_players);
   
   $player_keys = array_keys($a_players);
   sort($player_keys, SORT_NATURAL | SORT_FLAG_CASE);
   
   $a_winloss_lookahead = '';
   foreach ($player_keys as $player) {
      $player_status = 'in';
      for ($w = $week_begin; $w <= $week_end; $w++) {
         if ($player_status == 'out') {
            $a_winloss_lookahead[$player][$w] = array('out', '-');
            continue;
         }
         if (isset($a_main[$player][$w][1][1])) {  // player->week->pick#->winloss string
            $winloss = $a_main[$player][$w][1][1];;
            $team = $a_main[$player][$w][1][0];
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
   foreach ($player_keys as $player) {
      
      $winners_count = '';
      $player_wins = false;
      $player_status = 'in';
      
      for ($w = $week_begin; $w <= $week_end; $w++) {
         
         if ($winners_count == '' && $w < $week_end) {
            $winners_count = winnersWeek($player_keys, $a_winloss_lookahead, $w);
            $winners_count_next_week = winnersWeek($player_keys, $a_winloss_lookahead, ($w + 1));
         } elseif ($w < $week_end) {
            $winners_count = $winners_count_next_week;
            $winners_count_next_week = winnersWeek($player_keys, $a_winloss_lookahead, ($w + 1));
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

function winnersWeek(   // r - 0,1,2
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