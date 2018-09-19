<?php
//ob_start();
//header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: support_ko_last_man2.php
   date: may-2016
 author: hfs
   desc: 
                                               
*/

// lookahead array indexes
define ('LOOK_AHEAD_IN_OUT',    0);   // is the player in or out
define ('LOOK_AHEAD_SPECIAL',   1);   // special strings: norm, forfeit
define ('LOOK_AHEAD_TEAM_NAME', 2);   // team name (2-3 letter abbreviation)
define ('LOOK_AHEAD_STATUS',    3);   // the winloss (wi, lo, ti, sc, ip, np)

// a_players indexes
define('PLAYERS_TEAMNAME', 0);
define('PLAYERS_STATUS', 1);

define ('TIE_WINS', false);
define ('NO_PICK_TEAM_NAME', '-');
define ('NO_SHOW_TEAM_NAME', '???');
   
function sayKOLastManStandingsTable(
   $week_begin,   // league parameter
   $week_end,     // league parameter
   $week_current,
   $league_id,
   $use_vegas,    // 1 = no odds, 2 = odds
   &$ref_status_text = ''
){
   $ref_status_text = '';
   $msg = '';
   //$week_end = 4;

   writeDataToFile(" 1 support_ko_last_man 
      week_begin,      '$week_begin',
      week_end,        '$week_end',
      week_current,    '$week_current',
      league_id,       '$league_id',
      use_vegas,       '$use_vegas',
      ref_status_text, '$ref_status_text'", __FILE__, __LINE__);

   $writes_on = false;
      
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
    
    $sql_pending = "
      select s1.week as thisweek,
            (select if(s2.gametime < now(), 'ip', 'sc') 
               from schedules as s2 
              where s2.week = thisweek 
           order by s2.gametime desc limit 1 ) as gamestatus
       from schedules as s1
      where s1.week >= 1
        and s1.week <= 17
      group by s1.week";
   
   // The 'play' arrays.
   $a_players_win = array();  // Players have their weekly win recorded here.
   $a_players_inout = array();   // Players that their weekly lose recorded here.
   $a_players_row = array();   // HTML player row.  It consists of TD elements for each processed week
   $a_players = array();      // All the players.  An array 'player name' => (team name, game status)
   $a_last_scheduled_game_status = array();  // The weekly status of the last scheduled game.  Is either 'sc' SCheduled or 'ip' InProgress
   
   $calculated_end_week = 0;
   $hard_end_week = false;
   $game_is_over = false;
   $game_over_on_week = 0;
   $games_pending = '';
   $winners_count = 0;
   $losers_count = 0;
   $no_pick_count = 0;
   $all_games_locked = false; // The last scheduled game of the week may not be in play, but all users have committed to teams in progress.
   
   $status = 0;   
   while (1) {
      
      if (!$week_end) {  // league parameter 'lastround'
         $week_end = NFL_LAST_WEEK;
      }
      
      if ($week_begin > $week_end) {
         formatSessionMessage("The beginning week is after the end.", 'info', $msg, "sklm3-114 '$week_begin'$week_end'");
         setSessionMessage($msg, 'error');
         break;
      }
      
      $calculated_end_week = ($week_end > $week_current) ? $week_current : $week_end;  // end_week == 0 was previously reset to NFL_LAST_WEEK;
      
      $hard_end_week = false;    // The hard end says "this is it"; the final week of play.  There will be no more.  The game is over.
      if ($calculated_end_week == NFL_LAST_WEEK) {       // this is it; there are no games after the NFL's final week.
         $hard_end_week = true;
      } elseif ($calculated_end_week == $week_end) {     // this is it; commissioner says so (league.finalround is set)
         $hard_end_week = true;
      } elseif ($calculated_end_week == $week_current) { // perhaps more to come; this is just the current week (as set by the site adminsitrator)
         $hard_end_week = false;
      }
      
      //============================================================================================================== sql pending
      if (!$ans = runSql($sql_pending, '', 0, $ref_status_text)) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm3-153 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm3-158 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
      }
      $a_last_scheduled_game_status = array();
      foreach ($ans as $game_status) {
         $this_week = $game_status['thisweek'];
         $a_last_scheduled_game_status[$this_week] = $game_status['gamestatus'];
      }

      ($writes_on && writeDataToFile("array a_last_scheduled_game_status() " . print_r($a_last_scheduled_game_status, true), __FILE__, __LINE__));
      
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
      //============================================================================================================== sql players
      if (!$ans = runSql($sql_players, array("i", $league_id), 0, $ref_status_text)) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm3-153 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings.  There are no players active in this league.", 'info', $msg, "sklm3-158 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
      }
      foreach ($ans as $player) {
         $player_name = $player['playername'];
         $a_players[$player_name] = array();
         $a_players_win[$player_name] = '';
         $a_players_inout[$player_name] = 'in';
         for ($i = $week_begin; $i <= $calculated_end_week; $i++) {
            $a_players[$player_name][$i] = array('-', 'np', 'curse');
         }
      }

      ($writes_on && writeDataToFile("210 array a_players" . print_r($a_players, true), __FILE__, __LINE__));
      
      //============================================================================================================== sql game info
      if (!$ans = runSql($mysql, array("iii", $league_id, $week_begin, $calculated_end_week))) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm3-177 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings.  There is no league activity.", 'info', $msg, "sklm3-182 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
      }
      
      
      // Fill the a_player array with weekly data; [0]team name and [1]status. 
      // Players having no picks will have a - team name and status of np.
      // Status definitiions:
      //    sc - game is scheduled    (if score entered then wi,lo,ti)
      //    ip - game is in progress  (if score entered then wi,lo,ti)
      //    wi - win      (score is entered)
      //    lo - lose    (score is entered)
      //    ti - tie      (score is entered)
      //    np - no pick
      // (
      //     [cardtheplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => MIA
      //                     [1] => ip
      //                     [2] => curse
      //                 )
      //         )
      //     [cottonplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => IND
      //                     [1] => ip
      //                     [2] => curse
      //                 )
      //         )
      //     [silkplayer] => Array
      //         (
      //             [4] => Array
      //                 (
      //                     [0] => -
      //                     [1] => np
      //                     [2] => curse
      //                 )
      //         )
      //     [woolplayer] => Array
      
      $are_no_players = true;
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
         
         $winlose = ($use_vegas == LEAGUE_ODDS_IN_USE) ? $win_loss_push : $win_loss_push_no_spread;
         if ($winlose === 'ti') {
            if (TIE_WINS) {
               $winlose = 'wi';
            } else {
               $winlose = 'lo';
            }
         }
         $pick = array($team, $winlose, 'curse');
         $a_players[$player_name][$week] = $pick;
      }
      
      ($writes_on && writeDataToFile("292 array a_players " . print_r($a_players, true), __FILE__, __LINE__));
      
      if ($are_no_players) {
         formatSessionMessage("No members of this league have yet made a play.", 'info', $msg, "sklm3-256");
         setSessionMessage($msg, 'error');
         break;
      }
      
      $status = 1;
      break;
   }  // end building a_players input data
   
   if ($status == 0) {
      echoSessionMessage();
      return $status;
   }
   
   //=========================================================================================== begin last man calculation
   $ref_status_text = '';
   for ($w = $week_begin; $w <= $calculated_end_week && !$game_is_over; $w++) {
      
      $is_first_week =        ($w == $week_begin)           ? true : false;
      $is_final_week_loop =   ($w == $calculated_end_week)  ? true : false;
      
      // Since the lookahead is modified by 'shootout', each week must be read everytime.
      if (!winnersWeekLastManStanding( $a_players,
                                       $a_players_inout,
                                       $a_last_scheduled_game_status,
                                       $winners_count,
                                       $losers_count,
                                       $no_pick_count,
                                       $games_pending,
                                       $all_games_locked,
                                       $w,
                                       $calculated_end_week,
                                       $ref_status_text                 ))
      {
          formatSessionMessage("winnersWeekLastManStanding() failed", 'danger', $msg, "sklm3-323 $ref_status_text");
          setSessionMessage($msg, 'error');
          return;
      }
      
      if ($games_pending) {
         $is_final_week_loop = true;
         $game_is_over = true;
         $game_over_on_week = $w;  
      } 
         
      //echo "this week '$winners_count', next week $winners_count_next_week'\n";
      
      // Here's a sitch where previous players fail to pick.
      // This is only tested for true in scenarios where games are not pending since it is marking as winner
      $is_only_loser = false;
      if ($winners_count == 0 && $losers_count == 1 && !$games_pending) {
         $is_only_loser = true;
      }
      
         
      foreach ($a_players as $player => $a) {
         
         $player_in_out =  $a_players_inout[$player];
         $team =           $a_players[$player][$w][0];
         $game_status =    $a_players[$player][$w][1];
         $bless =          $a_players[$player][$w][2];
         
         $team = ($game_status == 'sc') ? NO_SHOW_TEAM_NAME : $team;
         
         ($writes_on && writeDataToFile("359 foreach player: 
            week                    '$w'
            player                  '$player',
            winners_count           '$winners_count',
            losers_count            '$losers_count',
            no_pick_count           '$no_pick_count',
            player_in_out           '$player_in_out',
            is_only_loser           '$is_only_loser',
            team                    '$team',
            game_status             '$game_status',
            is_first_week           '$is_first_week',
            is_final_week_loop      '$is_final_week_loop',
            calculated_end_week     '$calculated_end_week',
            games pending'          '$games_pending',
            bless                   '$bless'
            ", __FILE__, __LINE__));
          
         if (!$games_pending) {
            if ($game_status == 'np') {
               ($writes_on && writeDataToFile("378 Player has NP.  Marking as out.  $player", __FILE__, __LINE__));
               $a_players_inout[$player] = 'out';
               continue;
            }
            if ($a_players_inout[$player] == 'out') {
               ($writes_on && writeDataToFile("382 Player is marked as out SKIPPING.  $player", __FILE__, __LINE__));
               continue;
            }
         }
         
         //========================================================================================================================== 0
         if ($winners_count == 0) {
            
            // You bless this weeks players that were blessed/wi in 
            
            if (!$games_pending) {
               
               ($writes_on && writeDataToFile("395 winners = 0 and no pending games $player", __FILE__, __LINE__));
               
               if ($is_first_week) {   // Bless all the losers unless forfeit
                  if ($a_players[$player][$w][1] != 'np') {
                     $a_players[$player][$w][2] = 'bless';
                     if ($is_only_loser) {
                        $a_players_win[$player] = 'win';
                     }  
                  } else {
                     $a_players_inout[$player] = 'out';  // was a np.
                  }
                  continue;
               }
               
               if (!$is_final_week_loop) {
                  $last_week_bless = $a_players[$player][($w - 1)][2];
                  ($writes_on && writeDataToFile("411 is final week, no winners player $player last week bless is '$last_week_bless'", __FILE__, __LINE__));
                  if ($a_players[$player][($w - 1)][2] == 'bless') {
                     $a_players[$player][$w][2] = 'bless';
                     if ($is_only_loser) {
                        $a_players_win[$player] = 'win';
                     }
                  } elseif ($a_players[$player][($w - 1)][1] == 'wi' && $a_players_inout[$player] == 'in') {
                     $a_players[$player][$w][2] = 'bless';
                     if ($is_only_loser) {
                        $a_players_win[$player] = 'win';
                     }
                  }
                  continue;
               }
               
               if ($is_final_week_loop && $hard_end_week) { // end of play - forever - everyone is bless AND wins
                  if ($a_players[$player][($w - 1)][2] == 'bless') {
                     $a_players[$player][$w][2] = 'bless';
                     $a_players_win[$player] = 'win';
                  } elseif ($a_players[$player][($w - 1)][1] == 'wi' && $a_players_inout[$player] == 'in') {
                     $a_players[$player][$w][2] = 'bless';
                     $a_players_win[$player] = 'win';
                  }
                  continue;
               }
               
               if ($is_final_week_loop) {
                  if ($a_players[$player][($w - 1)][2] == 'bless') {
                     $a_players[$player][$w][2] = 'bless';
                     if ($is_only_loser) {
                        $a_players_win[$player] = 'win';
                     }
                  } elseif ($a_players[$player][($w - 1)][1] == 'wi' && $a_players_inout[$player] == 'in') {
                     $a_players[$player][$w][2] = 'bless';
                     if ($is_only_loser) {
                        $a_players_win[$player] = 'win';
                     }
                  } 
                  continue;
               }
            }  // END winner_count == 0 && !pending
            
            if ($games_pending) {
               // There's no blessing to be done.
            }
            
         }  // END winner_count == 0
         
         //========================================================================================================================== 1
         if ($winners_count == 1) {
            ($writes_on && writeDataToFile("461 winners count = 1", __FILE__, __LINE__));
            if (!$games_pending) {
               $game_is_over = true;   // kills the weeks
               $game_over_on_week = $w;
               if ($a_players[$player][$w][1] == 'wi'  && $a_players_inout[$player] == 'in') {
                  ($writes_on && writeDataToFile("466 winners=1, !games_pending player $player week $w WINS", __FILE__, __LINE__));
                  $a_players_win[$player] = 'win';
               } else {
                  $a_players_inout[$player] = 'out';
               }
            }
            continue;
         }
         
         //========================================================================================================================= >1 
         if ($winners_count > 1) {
            if (!$games_pending) {
               if ($a_players[$player][$w][1] != 'wi') {
                  ($writes_on && writeDataToFile("479 winners>1, !game_pending, player $player, week $w OUT", __FILE__, __LINE__));
                  $a_players_inout[$player] = 'out';
               }
            }
            continue;
         }
         
      }  // END foreach player
      ($writes_on && writeDataToFile("487 END OF PLAYERS FOR WEEEK '$w'", __FILE__, __LINE__));
   }  // END foreach week
            
   ($writes_on && writeDataToFile("490  a_player: " . print_r($a_players, true), __FILE__, __LINE__));
   ($writes_on && writeDataToFile("490 after cals, a_players_inout: " . print_r($a_players_inout, true), __FILE__, __LINE__));
   ($writes_on && writeDataToFile("490 after cals, a_players_win: " . print_r($a_players_win, true), __FILE__, __LINE__));
   
   //========================================================================================================================= display
   $rows_html = '';
   $a_player_row = array();
   $ending_display_week = ($game_over_on_week == 0) ? $calculated_end_week : $game_over_on_week;
   foreach ($a_players as $player => $a) {
      
      $player_out = false;
      $winner = ($a_players_win[$player] == 'win') ? "player_win" : '';
      
      $rows_html .= "  <tr>\n";
      $rows_html .= "     <td class='$winner' >$player</td>\n";
      
      ($writes_on && writeDataToFile("506  $week_begin;  $game_over_on_week", __FILE__, __LINE__));
      
      for ($wk = $week_begin; $wk <= $ending_display_week; $wk++) {
         
         $team =           $a[$wk][0];
         $game_status =    $a[$wk][1];
         $bless =          $a[$wk][2];
         $combined_class = 'player_' . $bless . '_' . $game_status;   // bless_ lo,   curse_ wi, lo, ip, sc, np
         
         
         ($writes_on && 
             writeDataToFile("517 player      '$player'
                              team            '$team'        
                              game_status     '$game_status'
                              bless           '$bless'        
                              combined_class  '$combined_class' ", __FILE__, __LINE__));
         
         if ($player_out) {
            $team = '-';
            $rows_html .= "     <td class='playerout' >$team</td>\n";
         } elseif ($game_status == 'sc') {
            $team = '???';
            $rows_html .= "     <td class='$combined_class' >$team</td>\n";
         } else {
            $rows_html .= "     <td class='$combined_class' >$team</td>\n";
         }
         
         if ($bless != 'bless' && ($game_status == 'lo' || $game_status == 'np')) {
            $player_out = true;  
         }
      }
      $rows_html .= "      </tr>\n";
   }
   
   $week_headers = '';
   for ($wk = $week_begin; $wk <= $ending_display_week; $wk++) {
      $week_headers .= "         <th>&nbsp;&nbsp;Week $wk&nbsp;&nbsp;</th>\n";
   }
   
   //<table id='IDtable_singleWeek' style='margin-left:auto;margin-right:auto;'>
   $repeating_nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
   $table = "
<table id='IDtable_ko_lastman' style='margin-left:auto;margin-right:auto;'>
   <thead>
      <tr>
         <th style='text-align:center'>$repeating_nbsp Player $repeating_nbsp</th>\n";
   $table .= $week_headers;
   $table .= "      </tr>
   </thead>
   <tbody>\n";
   
   $table .= $rows_html;
   
   $table .= "   </tbody>\n";
   $table .= "</table>\n";

echo $table;

}


// Ties, 'ti', are changed to 'wi' or 'lo' before this function is called.
function winnersWeekLastManStanding(
   &$a_players,
   &$a_players_inout,
   $a_last_scheduled_game_status,
   &$winners_count,
   &$losers_count,
   &$no_pick_count,
   &$games_pending,
   &$all_games_locked,
   $w,
   $w_end,
   $ref_status_text = ''                                                                          
){
   $winners_count = 0;
   $losers_count = 0;
   $no_pick_count = 0;
   $games_pending = false;
   $ref_status_text = '';
   $all_games_locked = true;
   $msg = '';
   
   foreach($a_players as $player => $a) {
      if ($a_players_inout[$player] == 'in') {
         switch ($a[$w][1]) {
         case 'ip':
            $games_pending = true;
            break;
         case 'sc':
            $games_pending = true;
            $all_games_locked = false;
            break;
         case 'np':
            if ($a_last_scheduled_game_status[$w] != 'ip') {
               $games_pending = true;
               $all_games_locked = false;
            }
            $no_pick_count++;
            break;
         case 'wi':
            $winners_count++;
            break;
         case 'lo':
            $losers_count++;
            break;
         default:
            $ref_status_text = "Never here: '$player' '$w' '$game_status' '$games_pending'";
            writeDataToFile("winnersWeekLastManStanding() default '$ref_status_text'", __FILE__, __LINE__);
            return false;
         }
      }
   }
   return true;
}

?>
