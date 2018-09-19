<?php
//ob_start();
//header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: support_ko_last_man.php
   date: may-2016
 author: hfs
   desc: 
                                               
*/
//require_once('mypicks_def.php');
//require_once('mysql_min_support.php');

// $league = 1;
// $league_type = 2;    // cohort 2, last man 3
// $begin = 12;
// $end = 17;

// lookahead array indexes
define ('IN_OUT',    0);   // is the player in or out
define ('SPECIAL',   1);   // special strings: norm, forfeit
define ('TEAM_NAME', 2);   // team name (2-3 letter abbreviation)
define ('STATUS',    3);   // the winloss (wi, lo, ti, sc, ip, np)
define ('TIE_WINS', false);
   
//$weekly_data = sayKOLastManStandingTable($begin, $end, $league);

//echo json_encode($weekly_data);
//ob_end_flush();
//exit();

// Builds the tr and td elements for the KO standings table and returns them
// in a string.
function sayKOLastManStandingsTable(
   $week_begin = 4,  // league parameter
   $week_end = 17,   // league parameter
   $week_current = 4,
   $league_id = 1,
   $use_vegas = 1,   // 1 = no odds, 2 = odds
   &$ref_status_text = ''
){

   writeDataToFile(" 1 support_ko_last_man 
      week_begin,      '$week_begin',
      week_end,        '$week_end',
      week_current,    '$week_current',
      league_id,       '$league_id',
      use_vegas,       '$use_vegas',
      ref_status_text, '$ref_status_text'", __FILE__, __LINE__);

   $writes_on = true;
      
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
    order by y.playername";
   
   $weekly_table = '';
   $ref_status_text = '';
   $weekly_table_data = array();
   $a_player_wins = array();
   $a_player_out = array();
   $a_player_row = array();
   $a_main = array();
   $status = 0;
   $calculated_end_week = 0;
   $hard_end_week = false;
   while (1) {
      
      if (!$week_end) {  // league parameter 'lastround'
         $week_end = NFL_LAST_WEEK;
      }
      
      if ($week_begin > $week_end) {
         formatSessionMessage("The beginning week is after the end.", 'info', $msg, "sklm-115 '$week_begin'$week_end'");
         setSessionMessage($msg, 'error');
         break;
      }
          
      if (!$ans = runSql($sql_players, array("i", $league_id), 0, $ref_status_text)) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm-122 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings.  There are no players active in this league.", 'info', $msg, "sklm-127 $ref_status_test");
            setSessionMessage($msg, 'error');
            break;
         }
      }
      
      //writeDataToFile("sayKOLastManStandingsTable() ans structure: " . print_r($ans, true), __FILE__, __LINE__); 
      
      // build array of players with slots for each week of play (here there is only one week, week 4)
      // Set to the default of no pick made (team = - and status = np)
      // (
      //     [cardtheplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => -
      //                     [1] => np
      //                 )
      //         )
      //     [cottonplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => -
      //                     [1] => np
      //                 )
      //         )
      //     [silkplayer] => Array
      //
      
      $calculated_end_week = ($week_end > $week_current) ? $week_current : $end_week;  // end_week == 0 was previously reset to NFL_LAST_WEEK;
      
      $hard_end_week = false;    // The hard end says "this is it"; the final week of play.  There will be no more.  The game is over.
      if ($calculated_end_week == NFL_LAST_WEEK) {       // this is it; there are no games after the NFL's final week.
         $hard_end_week = true;
      } elseif ($calculated_end_week == $week_end) {     // this is it; commissioner says so (league.finalround is set)
         $hard_end_week = true;
      } elseif ($calculated_end_week == $week_current) { // perhaps more to come; this is just the current week (as set by the site adminsitrator)
         $hard_end_week = false;
      }
      
      foreach ($ans as $player) {
         $player_name = $player['playername'];
         $a_players[$player_name] = array();
         for ($i = $week_begin; $i <= $calculated_end_week; $i++) {
            $a_players[$player_name][$i] = array('-', 'np');
         }
      }

      writeDataToFile("sayKOLastManStandingsTable() structure a_players" . print_r($a_players, true), __FILE__, __LINE__);
      
      if (!$ans = runSql($mysql, array("iii", $league_id, $week_begin, $calculated_end_week))) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm-180 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings.  There is no league activity.", 'info', $msg, "sklm-185 $ref_status_test");
            setSessionMessage($msg, 'error');
            break;
         }
      }
      
      //writeDataToFile("sayKOLastManStandingsTable() ans structure 2: " . print_r($ans, true), __FILE__, __LINE__); 
      
      
      $name_break = '';
      $week_break = '';
      $a_week = array();
      $pick = 1;
      $are_no_players = true;
      
      // Fill the player array with weekly data; [0]team name and [1]status. 
      // Players having no picks will have a - team name and status of np.
      // Status definitiions:
      //    sc - game is scheduled    (if score entered then wi,lo,ti)
      //    ip - game is in progress  (if score entered then wi,lo,ti)
      //    wi - win      (score is entered)
      //    lo - loose    (score is entered)
      //    ti - tie      (score is entered)
      //    np - no pick
      // (
      //     [cardtheplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => MIA
      //                     [1] => ip
      //                 )
      //         )
      //     [cottonplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => IND
      //                     [1] => ip
      //                 )
      //         )
      //     [silkplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => -
      //                     [1] => np
      //                 )
      //         )
      //     [woolplayer] => Array
      foreach ($ans as $picks) {
         
         $are_no_players = false;
         
         $user_name                 = $picks['user'];      
         $week                      = $picks['week'];      
         $last_round2               = $picks['lastround'];
         $player_name               = $picks['player'];
         $win_loss_push_no_spread   = $picks['nospreadwinloss'];
         $win_loss_push             = $picks['winloss'];
         $team                      = $picks['teampicked'];
         $gamestatus                = $picks['gamestatus'];
         
         $winloose = ($use_vegas == LEAGUE_ODDS_IN_USE) ? $win_loss_push : $win_loss_push_no_spread;
         if ($winloose == 'ti') {
            if (TIE_WINS) {
               $winloose = 'wi';
            } else {
               $winloose = 'lo';
            }
         }
               
         $pick = array($team, $winloose);
         $a_players[$player_name][$week] = $pick;
         
      }
      
      writeDataToFile("sayKOLastManStandingsTable(307)  a_players: filled " . print_r($a_players, true), __FILE__, __LINE__);
      
      if ($are_no_players) {  // Not likely given all users must belong to a league.  Active?  Failure to install properly?
         formatSessionMessage("No members of this league have yet made a play.", 'info', $msg, "sklm-257");
         setSessionMessage($msg, 'error');
         break;
      }
      
      $status = 1;
      break;
   }
   
   if ($status == 0) {
      echoSessionMessage();
      return $status;
   }
   
   //===========================================================================  begin
   // input array:
   //     [cardtheplayer] => Array
   //         (
   //             [4] => Array
   //                 (
   //                     [0] => MIA
   //                     [1] => ip
   //                 )
   //         )
   // output: see next process below
   //
   define('PLAYERS_TEAMNAME', 0);
   define('PLAYERS_STATUS', 1);
   $a_winloss_lookahead = '';
   $a_player_wins = '';
   foreach ($a_players as $player => $a) {
      
      $player_forfeit = false;
      $player_in_out_status = 'in';           // as we begin parsing each week of play, start with the assumption that the player is still 'in'
      $a_player_wins[$player] = '';
      $a_player_out[$player] = '';
      
      // player array indexes, 0 = team name, 1 = status
      for ($w = $week_begin; $w <= $calculated_end_week; $w++) {
         if ($a_players[$player][$w][PLAYERS_STATUS] != 'np') {  // player->week->[team, winloose]
            $game_status =  $a_players[$player][$w][PLAYERS_STATUS];
            $team =     $a_players[$player][$w][PLAYERS_TEAMNAME];
         } else {
            $game_status = 'np';
            $player_forfeit = true;
            $winloss = '';
            $team = '-';
         }
         
         // The two historic tests
         if ($player_forfeit) {
            $a_winloss_lookahead[$player][$w] = array('out', 'forfeit', $team, $game_status);
            continue;
         }
         if ($player_in_out_status == 'out') {
            $a_winloss_lookahead[$player][$w] = array('out', 'norm', $team, $game_status);
            continue;
         }
         
         if ($game_status == 'np'){
            $player_in_out_status = 'out';
            $player_forfeit = true;
            $a_winloss_lookahead[$player][$w] = array('out', 'forfeit', $team, $game_status);
            continue;
         }
         if ($game_status == 'wi' || $game_status == 'ip' || $game_status == 'sc') {
            $a_winloss_lookahead[$player][$w] = array('in', 'norm', $team, $game_status);
            continue;
         }
         if ($game_status == 'lo') {
            $player_in_out_status = 'out';
            $a_winloss_lookahead[$player][$w] = array('out', 'norm', $team, $game_status);
            continue;
         }
         formatSessionMessage("Never here!", 'danger', $msg, "sklm-322 '$player' '$w'");
         setSessionMessage($msg, 'error');
      }
   }
   
   writeDataToFile("look ahead a_winloss_lookahead " . print_r($a_winloss_lookahead, true), __FILE__, __LINE__);

   
   // Input: (from just above) $a_winloss_lookahead
   // define ('IN_OUT',    0);
   // define ('SPECIAL',   1);
   // define ('TEAM_NAME', 2);
   // define ('STATUS',    3);
   // [cottonplayer] => Array
   //     (
   //         [4] => Array
   //             (
   //                 [0] => in
   //                 [1] => norm
   //                 [2] => IND
   //                 [3] => ip
   //             )
   //     )
   // [silkplayer] => Array
   //     (
   //         [4] => Array
   //             (
   //                 [0] => out
   //                 [1] => forfeit
   //                 [2] => -
   //                 [3] => ip
   //             )
   //     )
   // [woolplayer] => Array


   $game_is_over = false;
   $game_over_on_week = 0;
   $shootout_begin_week = 0;
   $ref_status_text = '';
   for ($w = $week_begin; $w <= $calculated_end_week && !$game_is_over; $w++) {
      
      $is_final_week_loop = ($w == $calculated_end_week) ? true : false;
      $game_is_over = ($is_final_week_loop) ? true : false;   // loop control
      $game_over_on_week = ($game_is_over) ? $w : 0;
      
      // Since the lookahead is modified by 'shootout', each week must be read everytime.
      if ($w < $calculated_end_week ) {
         if (!winnersWeekLastManStanding($a_players, $a_winloss_lookahead, $winners_count,           $games_pending,            $w,       $ref_status_text)
          || !winnersWeekLastManStanding($a_players, $a_winloss_lookahead, $winners_count_next_week, $games_pending_next_week,  ($w + 1), $ref_status_text))
          {
             formatSessionMessage("winnersWeekLastManStanding() failed", 'danger', $msg, "sklm-381 $ref_status_text");
             setSessionMessage($msg, 'error');
             return;
          }
         $is_final_week_loop = ($games_pending) ? true : false;
         $game_is_over = ($is_final_week_loop) ? true : false;   // redundant on last week
         $game_over_on_week = ($game_is_over) ? $w : 0;
         
      } else {
         if (!winnersWeekLastManStanding($a_players, $a_winloss_lookahead, $winners_count,           $games_pending,            ($w),     $ref_status_text))
         {
             formatSessionMessage("winnersWeekLastManStanding() failed", 'danger', $msg, "sklm-388 $ref_status_text");
             setSessionMessage($msg, 'error');
             return;
         }
         $winners_count_next_week = 0;
         $games_pending_next_week = ($hard_end_week) ? false : true;
      }
      
      //echo "this week '$winners_count', next week $winners_count_next_week'\n";
         
      foreach ($a_players as $player => $a) {
         
         $player_in_out =  $a_winloss_lookahead[$player][$w][IN_OUT];
         $special =        $a_winloss_lookahead[$player][$w][SPECIAL];
         $team =           $a_winloss_lookahead[$player][$w][TEAM_NAME];
         $game_status =    $a_winloss_lookahead[$player][$w][STATUS];
         
         $team = ($game_status == 'sc') ? '???' : $team;
         
          writeDataToFile("421 foreach player: 
            week                    '$w'
            player                  '$player',
            winners_count           '$winners_count',
            winners_count_next_week '$winners_count_next_week',
            player_in_out           '$player_in_out',
            special                 '$special',
            team                    '$team',
            game_status             '$game_status',
            is_final_week_loop      '$is_final_week_loop'
            games pending'          '$games_pending'
            ", __FILE__, __LINE__);
         
         if ($a_player_out[$player] == 'out') {
            $a_player_row[$player][$w] = "         <td inout='out' special='$special'  db='sklm-420'>-</td>\n";
            continue;
         }
   
         $is_a_winner = false;
         if ( ($special == 'blessed' && $game_status == 'wi')
              ||
              ($special == 'norm' && $player_in_out == 'in' && $game_status == 'wi') )
         {
            $is_a_winner = true;
         }
         
         
         
         // Is this THE game winner?
         //========================================================================================= 1
         if ($winners_count == 1) {
            writeDataToFile("452 winners_count > 1", __FILE__, __LINE__);
            if (!$games_pending) {  // Only one 'winner'.  Game over.
               $game_is_over = true;
               $game_over_on_week = $w;   // This is the only premature end possible.
               if ($is_a_winner) {
                  $a_player_wins[$player] = "winner='winner'";
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status' db='sklm-443'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-446'>$team</td>\n";
               }
               continue;
            } else {  // games are still pending
               if ($is_a_winner) {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-451'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-454'>$team</td>\n";
               }
               continue;
            }
         }
         
         // If the winner week count > 1, nobody has won yet (unless this is the final week)
         //=============================================================================================================================== > 1
         if ($winners_count > 1 ) { 
            writeDataToFile("478 winners_count > 1", __FILE__, __LINE__);
            
            if ($hard_end_week) {   // it's the NFL last week or the league lastround; everyone wins
               if ($is_a_winner) {
                  $a_player_wins[$player] = "winner='winner'";
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-468'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-471'>$team</td>\n";
               }
               continue;
            }
            
            
            if ($winners_count_next_week == 0 ) {  // This group of winning players all loose next week.  Time for blessing
               writeDataToFile("493 if winner count ==  0 '$player'", __FILE__, __LINE__);
               
               // He's one of the last standing.  He fails next week but allow shootout status.
               
               if ($is_a_winner) {
                  if (!$shootout_begin_week) { $shootout_begin_week = $w; }
                     if ($a_winloss_lookahead[$player][($w + 1)][SPECIAL] != 'forfeit') {
                        $a_winloss_lookahead[$player][($w + 1)][SPECIAL] = 'blessed';
                        $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-486'>$team</td>\n";
                     } else {
                        $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-488'>$team</td>\n";
                        $a_player_out[$player] = 'out';
                     }
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-493'>$team</td>\n";
               }
               continue;
            }
            
            ($writes_on && writeDataToFile("'$player'", __FILE__, __LINE__));
            if ($winners_count_next_week > 0) {
               if ($is_a_winner) {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'   db='sklm-501'>$team</td>\n";
               }  else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-504'>$team</td>\n";
               }
               continue;
            }
         }
         // With a shootout, the last round may have no winners
         writeDataToFile("525 '$player'", __FILE__, __LINE__);
         
         
         //================================================================================================================================== 0
         if ($winners_count == 0) {
            if ($hard_end_week) {
               if ($special != 'forfeit') {
                  $a_player_wins[$player] = "winner='winner'";
                  $a_player_row[$player][$w] = "         <td inout='in' special='blessed' gamestatus='$game_status'  db='sklm-533'>$team</td>\n";
                  continue;
               } else {
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-536'>$team</td>\n";
                  continue;
               }
            }
            if (!$is_final_week_loop) {
               if ($special != 'forfeit') {
                  $a_player_row[$player][$w] = "         <td inout='in' special='blessed' gamestatus='$game_status'  db='sklm-542'>$team</td>\n";
                  continue;
               } else {
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-545'>$team</td>\n";
                  continue;
               }
            }
            
            if ($winners_count_next_week == 0 ) {  // This group of loosing players all loose again next week.  Time for blessing
               writeDataToFile("551'$player'", __FILE__, __LINE__);
               
               if (!$shootout_begin_week)
                  if ($a_winloss_lookahead[$player][($w + 1)][SPECIAL] != 'forfeit') {
                     $a_winloss_lookahead[$player][($w + 1)][SPECIAL] = 'blessed';
                     $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-556'>$team</td>\n";
                  } else {
                     $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-558'>$team</td>\n";
                     $a_player_out[$player] = 'out';
                  }
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-563'>$team</td>\n";
               }
               continue;
            }
            
               
            
            
            // He's not a winner.  He's not out.  Bless him.
            
            
            
            //// Then they all lost.  Everyone get blessed.
            //writeDataToFile("531 winners_count 0  shootout_begin_week '$shootout_begin_week' special '$special'", __FILE__, __LINE__);
            //if (!$shootout_begin_week) {
            //   $a_player_out[$player] = 'out';
            //   $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-518'>$team</td>\n";
            //}
            //if ($special == 'shootout') {
            //   if (!$hard_end_week) {
            //      $a_winloss_lookahead[$player][($w + 1)][SPECIAL] = 'shootout';
            //      $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'   db='sklm-523'>$team</td>\n";
            //   } else {  // final week, eveyone wins
            //      $a_player_wins[$player] = "winner='winner'";
            //      $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'   db='sklm-526'>$team</td>\n";
            //   }
            //}

      } // END player
   } // END week   
   
   
   
   
   //-//$game_is_over = false;
   //-//$game_over_on_week = 0;
   //-//$shootout_begin_week = 0;
   //-//for ($w = $week_begin; $w <= $week_end && !$game_is_over; $w++) {
   //-//   
   //-//   $is_final_week = ($w == $week_end) ? true : false;
   //-//   $game_is_over = ($is_final_week) ? true : false;   // redundant on last week
   //-//   $game_over_on_week = ($game_is_over) ? $w : 0;
   //-//   
   //-//   // Since the lookahead is modified by 'shootout', each week must be read everytime.
   //-//   if (!$is_final_week) {
   //-//      $winners_count =           winnersWeekLastManStanding($a_players, $a_winloss_lookahead, $w);
   //-//      $winners_count_next_week = winnersWeekLastManStanding($a_players, $a_winloss_lookahead, ($w + 1));
   //-//   } else {
   //-//      $winners_count =           winnersWeekLastManStanding($a_players, $a_winloss_lookahead, ($w));
   //-//      $winners_count_next_week = 0;
   //-//   }
   //-//   
   //-//   //echo "this week '$winners_count', next week $winners_count_next_week'\n";
   //-//      
   //-//   foreach ($a_players as $player => $a) {
   //-//      
   //-//      $player_status =  $a_winloss_lookahead[$player][$w][INOUT];
   //-//      $special =        $a_winloss_lookahead[$player][$w][SPEC];
   //-//      $team =           $a_winloss_lookahead[$player][$w][TEAM];
   //-//      $real_winlose =   (!empty($a_winloss_lookahead[$player][$w][REAL])) ? $a_winloss_lookahead[$player][$w][REAL] : '';
   //-//      
   //-//       writeDataToFile("
   //-//       player          '$player' ,
   //-//       winnners count  '$winners_count'  ,
   //-//       next week       '$winners_count_next_week'  ,
   //-//       player status   '$player_status'  ,
   //-//       special         '$special'  ,
   //-//       team            '$team'  ,
   //-//       real winloose   '$real_winlose' ,
   //-//       is final week   '$is_final_week'", __FILE__, __LINE__);
   //-//      
   //-//      if ($a_player_out[$player] == 'out') {
   //-//         $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='pout'>-</td>\n";
   //-//         continue;
   //-//      }
   //-//
   //-//      ($writes_on && writeDataToFile("'$player'", __FILE__, __LINE__));
   //-//      $is_a_winner = false;
   //-//      if ( ($special == 'shootout' && $real_winlose == 'wi')
   //-//           ||
   //-//           ($special == 'norm' && $player_status == 'in') )
   //-//      {
   //-//         $is_a_winner = true;
   //-//      }
   //-//      
   //-//      ($writes_on && writeDataToFile("'$player'", __FILE__, __LINE__));
   //-//      if ($winners_count == 1) {  // Only one 'winner'.  Game over.
   //-//         $game_is_over = true;
   //-//         $game_over_on_week = $w;   // This is the only premature end possible.
   //-//         if ($is_a_winner) {
   //-//            $player_wins = true;
   //-//            $a_player_wins[$player] = "winner='winner'";
   //-//            $a_player_row[$player][$w] = "         <td inout='in' special='$special' db='wc1'>$team</td>\n";
   //-//         } else {
   //-//            $a_player_out[$player] = 'out';
   //-//            $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='wc1'>$team</td>\n";
   //-//         }
   //-//         continue;
   //-//      }
   //-//      
   //-//      // If the winner week count > 1, nobody has won yet (unless this is the final week)
   //-//      ($writes_on && writeDataToFile("'$player'", __FILE__, __LINE__));
   //-//      if ($winners_count > 1 ) { 
   //-//         
   //-//         if ($is_final_week) {
   //-//            if ($is_a_winner) {
   //-//               $a_player_wins[$player] = "winner='winner'";
   //-//               $a_player_row[$player][$w] = "         <td inout='in' special='$special' db='fwc+1'>$team</td>\n";
   //-//            } else {
   //-//               $a_player_out[$player] = 'out';
   //-//               $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='fwc+'>$team</td>\n";
   //-//            }
   //-//            continue;
   //-//         }
   //-//         
   //-//         // The current count is > 1 and next week has no winners.  Shootout time.
   //-//         ($writes_on && writeDataToFile("if winner count ==  0 '$player'", __FILE__, __LINE__));
   //-//         if ($winners_count_next_week == 0) {
   //-//            
   //-//            // He's one of the last standing.  He fails next week but allow shootout status.
   //-//            ($writes_on && writeDataToFile("winner count was zero", __FILE__, __LINE__));
   //-//            if ($is_a_winner) {
   //-//               if (!$shootout_begin_week) { $shootout_begin_week = $w; }
   //-//                  if ($a_winloss_lookahead[$player][($w + 1)][SPEC] != 'forfeit') {
   //-//                     $a_winloss_lookahead[$player][($w + 1)][SPEC] = 'shootout';
   //-//                     $a_player_row[$player][$w] = "         <td inout='in' special='$special' db='wc+1nw0'>$team</td>\n";
   //-//                  } else {
   //-//                     $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='wc+1nw0xx'>$team</td>\n";
   //-//                     $a_player_out[$player] = 'out';
   //-//                  }
   //-//            } else {
   //-//               $a_player_out[$player] = 'out';
   //-//               $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='wc+1nw0'>$team</td>\n";
   //-//            }
   //-//            continue;
   //-//         }
   //-//         
   //-//         ($writes_on && writeDataToFile("'$player'", __FILE__, __LINE__));
   //-//         if ($winners_count_next_week > 0) {
   //-//            if ($is_a_winner) {
   //-//               $a_player_row[$player][$w] = "         <td inout='in' special='$special'  db='wc+1nw+1'>$team</td>\n";
   //-//            }  else {
   //-//               $a_player_out[$player] = 'out';
   //-//               $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='nw+1'>$team</td>\n";
   //-//            }
   //-//            continue;
   //-//         }
   //-//      }
   //-//      // With a shootout, the last round may have no winners
   //-//      ($writes_on && writeDataToFile("'$player'", __FILE__, __LINE__));
   //-//      if ($winners_count == 0) {
   //-//         if (!$shootout_begin_week) {
   //-//            $a_player_out[$player] = 'out';
   //-//            $a_player_row[$player][$w] = "         <td inout='out' special='$special' db='wc0nnotso'>$team</td>\n";
   //-//         }
   //-//         if ($special == 'shootout') {
   //-//            if (!$is_final_week) {
   //-//               $a_winloss_lookahead[$player][($w + 1)][SPEC] = 'shootout';
   //-//               $a_player_row[$player][$w] = "         <td inout='out' special='$special'  db='wc0'>$team</td>\n";
   //-//            } else {  // final week, eveyone wins
   //-//               $a_player_wins[$player] = "winner='winner'";
   //-//               $a_player_row[$player][$w] = "         <td inout='in' special='$special'  db='wc0'>$team</td>\n";
   //-//            }
   //-//         }
   //-//         continue;
   //-//      }
   //-//   } // END player
   //-//} // END week
   
   writeDataToFile("support_ko_last_man.php a_player_out(): \n" . print_r($a_player_out, true), __FILE__, __LINE__);
   writeDataToFile("support_ko_last_man.php a_player_row(): \n" . print_r($a_player_row, true), __FILE__, __LINE__);
   
   $rows = '';
   foreach ($a_players as $player => $a) {
      $winner = (isset($a_player_wins[$player])) ? $a_player_wins[$player] : '';
      $rows .= "      <tr>
         <td $winner >$player</td>\n";
      for ($wk = $week_begin; $wk <= $game_over_on_week; $wk++) {
         if (empty($a_player_row[$player][$wk])) {  // bug - 
            writeDataToFile("support_ko_last_man.php Player has empty datapoint in row: player wk:  '$player',  '$wk', row: " . 
               print_r($a_player_row, true), __FILE__, __LINE__);
            // Need to abort here.
         }
         $rows .= $a_player_row[$player][$wk];
      }
      $rows .= "      </tr>\n";
   }
   
   $week_headers = '';
   for ($wk = $week_begin; $wk <= $game_over_on_week; $wk++) {
      $week_headers .= "         <th>&nbsp;&nbsp;Week $wk&nbsp;&nbsp;</th>\n";
   }
   
   //<table id='IDtable_singleWeek' style='margin-left:auto;margin-right:auto;'>
   $table = "
<table id='IDtable_ko_lastman' style='margin-left:auto;margin-right:auto;'>
   <thead>
      <tr>
         <th style='text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Player&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>\n";
   $table .= $week_headers;
   $table .= "      </tr>
   </thead>
   <tbody>\n";
   
   //////////////////////////////////////////////////////////////////////////////////////// build the table rows
   
   $table .= $rows;
   
$table .= "   </tbody>\n";
$table .= "</table>\n";

echo $table;

}


// Ties, 'ti', are changed to 'wi' or 'lo' before this function is called.
function winnersWeekLastManStanding(
   &$a_players,
   &$a_lookahead,
   &$count,
   &$games_pending,
   $w,
   $ref_status_text = ''
){
   $count = 0;
   $ref_status_text = '';
   $games_pending = false;
   foreach($a_players as $player => $a) {
      $is_shootout = ($a_lookahead[$player][$w][SPECIAL] == 'blessed') ? true : false;
      if ($a_lookahead[$player][$w][IN_OUT] == 'in') {  // consider only eligible players
         $game_status =  $a_lookahead[$player][$w][STATUS];
         switch ($game_status) {
         case 'ip':
         case 'sc':
            $games_pending = true;
            break;
         case 'wi':
            $count++;
            break;
         case 'lo':
            // what to do with shootout?
            break;
         default:
            $ref_status_text = "Never here: '$player' '$game_status' '$games_pending'";
            return false;
         }
      }
   }
   return true;
   
}

?>