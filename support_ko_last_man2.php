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
   //$week_end = 4;

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
   
   
   // The 'play' arrays.
   $a_player_wins = array();  // Players have their weekly win recorded here.
   $a_player_out = array();   // Players that their weekly lose recorded here.
   $a_player_row = array();   // HTML player row.  It consists of TD elements for each processed week
   $a_players = array();      // All the players.  An array 'player name' => (team name, game status)
   $a_lookahead = array();    // The 'process' array contain player status.  It's used in the play calculations loop.
   
   $calculated_end_week = 0;
   $hard_end_week = false;
   $game_is_over = false;
   $game_over_on_week = 0;
   
   $status = 0;   
   while (1) {
      
      if (!$week_end) {  // league parameter 'lastround'
         $week_end = NFL_LAST_WEEK;
      }
      
      if ($week_begin > $week_end) {
         formatSessionMessage("The beginning week is after the end.", 'info', $msg, "sklm-114 '$week_begin'$week_end'");
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
      if (!$ans = runSql($sql_players, array("i", $league_id), 0, $ref_status_text)) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm-153 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings.  There are no players active in this league.", 'info', $msg, "sklm-158 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
      }
      foreach ($ans as $player) {
         $player_name = $player['playername'];
         $a_players[$player_name] = array();
         $a_player_wins[$player_name] = '';
         $a_player_out[$player_name] = '';
         for ($i = $week_begin; $i <= $calculated_end_week; $i++) {
            $a_players[$player_name][$i] = array('-', 'np');
         }
      }

      writeDataToFile("166 sayKOLastManStandingsTable() structure a_players" . print_r($a_players, true), __FILE__, __LINE__);
      
      if (!$ans = runSql($mysql, array("iii", $league_id, $week_begin, $calculated_end_week))) {
         if ($ans === false) {
            formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm-177 $ref_status_text");
            setSessionMessage($msg, 'error');
            break;
         }
         if ($ans === null) {
            formatSessionMessage("We are unable to display standings.  There is no league activity.", 'info', $msg, "sklm-182 $ref_status_text");
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
               
         $pick = array($team, $winlose);
         $a_players[$player_name][$week] = $pick;
         
      }
      
      writeDataToFile("sayKOLastManStandingsTable(241)  a_players: filled " . print_r($a_players, true), __FILE__, __LINE__);
      
      if ($are_no_players) {
         formatSessionMessage("No members of this league have yet made a play.", 'info', $msg, "sklm-256");
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
   
   //==================================================================================================================  begin
   // Add the special and in-out data.  Build a_lookahead.
   // Special values:
   // first == first lo
   // running == lo based on first lo.  Once a running out is set it carries thru forever
   // forfeit == based on a 'np' lose.  Once forfeit is set it carries thru forever.
   // norm == wi, sc, ip
   // input: a_players:
   //     [cardtheplayer] => Array
   //         (
   //             [4] => Array
   //                 (
   //                     [0] => MIA
   //                     [1] => ip
   //                 )
   //         )
   // output:   a_lookahead
   //     [cardtheplayer] => Array
   //         (
   //             [4] => Array
   //                 (
   //                     [0] => out
   //                     [1] => first
   //                     [2] => MIA
   //                     [3] => lo
   //                 )
   //
   $foreach_status = 1;
   foreach ($a_players as $player => $a) {
      
      $player_forfeit = false;
      $player_in_out_status = 'in';
      $special = '';
      
      for ($w = $week_begin; $w <= $calculated_end_week; $w++) {
         
         $game_status =    $a_players[$player][$w][PLAYERS_STATUS];
         $team =           $a_players[$player][$w][PLAYERS_TEAMNAME];
         $end_of_week_loop = ($w == $calculated_end_week) ? true : false;
         
         if ($player_forfeit) {
            $team = NO_PICK_TEAM_NAME;
            $a_lookahead[$player][$w] = array('out', 'forfeit', $team, $game_status);
            continue;
         }
         
         if ($game_status == 'np') {
            if (!$end_of_week_loop) {  // You can't forfeit until the week closes.  It's closed if  weeks remain to process.
               $player_forfeit = true;
               $team = NO_PICK_TEAM_NAME;
               $a_lookahead[$player][$w] = array('out', 'forfeit', $team, $game_status);
               continue;
            } else {
               $a_lookahead[$player][$w] = array('in', 'norm', $team, $game_status);
               continue;
            }
         }
         // Players that were previouly cut are now out.  Their team names are nuked incase they are still playing.
         if ($player_in_out_status == 'out') {
            $team = NO_PICK_TEAM_NAME;
            $a_lookahead[$player][$w] = array('out', 'norm', $team, $game_status);
            continue;
         }
         if ($game_status == 'wi' || $game_status == 'ip' || $game_status == 'sc') {
            $a_lookahead[$player][$w] = array('in', 'norm', $team, $game_status);
            continue;
         }
         if ($game_status == 'lo') {
            $player_in_out_status = 'out';
            $a_lookahead[$player][$w] = array('in', 'norm', $team, $game_status);
            continue;
         }
         formatSessionMessage("Never here!", 'danger', $msg, "sklm-337 '$player' '$w' '$game_status' '$team'");
         setSessionMessage($msg, 'error');
         $foreach_status = 0;
      }
   }
   
   if (!$foreach_status) {
      // TODO process error  
   }
   
   writeDataToFile("334 look ahead a_lookahead " . print_r($a_lookahead, true), __FILE__, __LINE__);

   
   // Input: (from just above) $a_lookahead
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



   //========================================================================================================= DISPLAY
   
   // Any episode marked 'in' is considered, otherwise it is rejected.
   
   $shootout_begin_week = 0;
   $ref_status_text = '';
   for ($w = $week_begin; $w <= $calculated_end_week && !$game_is_over; $w++) {
      
      $is_final_week_loop = ($w == $calculated_end_week) ? true : false;
      $game_is_over = ($is_final_week_loop) ? true : false;   // loop control
      $game_over_on_week = ($game_is_over) ? $w : false;   // redundant on last week
      
      // Since the lookahead is modified by 'shootout', each week must be read everytime.
      if ($w < $calculated_end_week ) {
         if (!winnersWeekLastManStanding($a_players, $a_lookahead, $winners_count,           $games_pending,            $w,       $ref_status_text)
          || !winnersWeekLastManStanding($a_players, $a_lookahead, $winners_count_next_week, $games_pending_next_week,  ($w + 1), $ref_status_text))
         {
             formatSessionMessage("winnersWeekLastManStanding() failed", 'danger', $msg, "sklm-386 $ref_status_text");
             setSessionMessage($msg, 'error');
             return;
         }
         $is_final_week_loop = ($games_pending) ? true : false;
         $game_is_over = ($is_final_week_loop) ? true : false;   // redundant on last week
         $game_over_on_week = ($game_is_over) ? $w : false;   // redundant on last week
         
      } else {
         if (!winnersWeekLastManStanding($a_players, $a_lookahead, $winners_count,           $games_pending,            ($w),     $ref_status_text))
         {
             formatSessionMessage("winnersWeekLastManStanding() failed", 'danger', $msg, "sklm-397 $ref_status_text");
             setSessionMessage($msg, 'error');
             return;
         }
         $winners_count_next_week = 0;
         $games_pending_next_week = ($hard_end_week) ? false : true;
      }
      
      //echo "this week '$winners_count', next week $winners_count_next_week'\n";
      
         
      foreach ($a_players as $player => $a) {
         
         $player_in_out =  $a_lookahead[$player][$w][LOOK_AHEAD_IN_OUT];
         $special =        $a_lookahead[$player][$w][LOOK_AHEAD_SPECIAL];
         $team =           $a_lookahead[$player][$w][LOOK_AHEAD_TEAM_NAME];
         $game_status =    $a_lookahead[$player][$w][LOOK_AHEAD_STATUS];
         
         $team = ($game_status == 'sc') ? NO_SHOW_TEAM_NAME : $team;
         
          writeDataToFile("417 foreach player: 
            week                    '$w'
            player                  '$player',
            winners_count           '$winners_count',
            winners_count_next_week '$winners_count_next_week',
            player_in_out           '$player_in_out',
            special                 '$special',
            team                    '$team',
            game_status             '$game_status',
            is_final_week_loop      '$is_final_week_loop'
            calculated_end_week     '$calculated_end_week'
            games pending'          '$games_pending'
            shootout_begin_week     '$shootout_begin_week'
            ", __FILE__, __LINE__);
         
         
         // Out for good.  A status of 'out' is assigned to forfeit and previous loss players only.
         // For example, there are now 'out' players the first week, until the next week "opens".
         if ($special == 'forfeit' || $player_in_out == 'out') {
            $a_player_out[$player] = 'out';
            $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-448'>" . NO_PICK_TEAM_NAME . "</td>\n";
            continue;
         }
         
         // There are no final actions that can be taken.  Just write'em up.
         if ($games_pending) {
            switch($game_status) {
            case 'wi':
            case 'lo':
            case 'sc':
            case 'ip':
            case 'np':
            default:
            }
            if ($games_pending) {   // There are no final actions that can be taken
               if ($game_status == 'wi') {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-436'>$team</td>\n";
               }
            }
         }
         
         $is_a_winner = false;
         if ( ($special == 'blessed' && $game_status == 'wi')
              ||
              ($special == 'norm' && $player_in_out == 'in' && $game_status == 'wi') )
         {
            $is_a_winner = true;
         }
         
         // Is this THE game winner?
         //=============================================================================================================================== 1
         if ($winners_count == 1) {
            
            writeDataToFile("452 winners_count > 1", __FILE__, __LINE__);
            
            $game_is_over = true;
            $game_over_on_week = $w;
            
            if (!$games_pending) { // If games are pending we can't declare a winner.
               
               if ($is_a_winner) {
                  $a_player_wins[$player] = "winner='winner'"; // use later to highlight html
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status' db='sklm-461'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-464'>$team</td>\n";
               }
               continue;
               
            } else {  // There are pending games
               
               if ($is_a_winner) {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-471'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-474'>$team</td>\n";
               }
               
               continue;
            }
         }
         
         // If the winner week count > 1, nobody has won yet (unless this is the final week)
         //=============================================================================================================================== > 1
         if ($winners_count > 1 ) {
            writeDataToFile("478 winners_count > 1", __FILE__, __LINE__);
            
            // 
            // Scenarios:
            // 
            // #1 Hard week end:  
            //    Nothing much matters.  This is the last round.  Mark the winners.
            //    
            // #2 Final week processing:
            //    There is no look ahead so there is no blessing.  We don't know if players win or lose next week.
            //    Build the row as in or out.  No lookahead adjustments.
            //
            // #3 There are winners next week (and a last week loop condition - taken care of by 1st two tests):
            //    No blessing as the game continues on its own.  Just fill in the rows.
            //    
            // #4 No winners and no pending games next week (and a last week loop condition - taken care of by 1st two tests):
            //    Every winner this week loses.  They all need blessing.  Don't bless the winners; bless their next week loss.
            //
            // #5 Else (and a last week loop condition - taken care of by 1st two tests):
            //    There isn't anything left to affect the week.  Just fill the rows.
            //    
            // There are winners next week  (and a last week loop condition - taken care of by 1st two tests):
            //    Just build the rows.
            // 
            
            if ($hard_end_week) {   // ================================================================= #1
               if ($is_a_winner) {
                  $a_player_wins[$player] = "winner='winner'";
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-512'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-515'>$team</td>\n";
               }
               continue;
            }
            
            if ($is_final_week_loop) {   // ============================================================ #2
               if ($is_a_winner) {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-522'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-525'>$team</td>\n";
               }
               continue;
            }
            
            if ($winners_count_next_week > 0 ) {   // =================================================== #3
               if ($is_a_winner) {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-532'>$team</td>\n";
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-535'>$team</td>\n";
               }
               continue;
            }
            
            if ($winners_count_next_week == 0 && !$games_pending_next_week) {   // ====================== #4
               
               if (!$shootout_begin_week) { 
                  $shootout_begin_week = $w; 
               }
               
               writeDataToFile("493 if winner count ==  0 '$player'", __FILE__, __LINE__);
               
               // He's one of the last standing.  He fails next week but allow shootout status.
               
               if ($is_a_winner) {  // He'll lose next week.  He needs a blessing
                  
                  $a_lookahead[$player][($w + 1)][LOOK_AHEAD_SPECIAL] = 'blessed';
                  $a_lookahead[$player][($w + 1)][LOOK_AHEAD_IN_OUT] = 'in';
                  $a_player_row[$player][$w] = "         <td inout='in' special='blessing' gamestatus='$game_status'  db='sklm-554'>$team</td>\n";
               
               } else {
                  
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-559'>$team</td>\n";
                  
               }
               continue;
            }
            
            if (true) {   // ==================================================== #5
               if ($is_a_winner) {
                  $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'   db='sklm-567'>$team</td>\n";
               }  else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-570'>$team</td>\n";
               }
               continue;
            }
                     
          writeDataToFile("562 > 1 fallout foreach player: 
            week                    '$w'
            player                  '$player',
            winners_count           '$winners_count',
            winners_count_next_week '$winners_count_next_week',
            player_in_out           '$player_in_out',
            special                 '$special',
            team                    '$team',
            game_status             '$game_status',
            is_final_week_loop      '$is_final_week_loop'
            calculated_end_week     '$calculated_end_week'
            games pending'          '$games_pending'
            games_pending_next_week '$games_pending_next_week'
            hard_end_week           '$hard_end_week'
            ", __FILE__, __LINE__);
            
            
         } // END $winners_count > 1 
         
         writeDataToFile("594 '$player'", __FILE__, __LINE__);
         
         
         //=============================================================================================================================== 0
         if ($winners_count == 0) {   writeDataToFile("601 winners_count is ZERO, $player", __FILE__, __LINE__);
            
            if ($hard_end_week) { writeDataToFile("601-604 HARD END WEEK, $player", __FILE__, __LINE__);
               if ($special != 'forfeit') { writeDataToFile("601-604 HARD END WEEK, $player", __FILE__, __LINE__);
                  $a_player_wins[$player] = "winner='winner'";
                  $a_player_row[$player][$w] = "         <td inout='in' special='blessed' gamestatus='$game_status'  db='sklm-603'>$team</td>\n";
                  continue;
               } else {
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-606'>$team</td>\n";
                  continue;
               }
            }
            
            if (!$is_final_week_loop) { writeDataToFile("601-614 final loop, $player", __FILE__, __LINE__);
               if ($special != 'forfeit') {
                  $a_player_row[$player][$w] = "         <td inout='in' special='blessed' gamestatus='$game_status'  db='sklm-613'>$team</td>\n";
                  $a_lookahead[$player][($w + 1)][LOOK_AHEAD_SPECIAL] = 'blessed';
                  $a_lookahead[$player][($w + 1)][LOOK_AHEAD_IN_OUT] = 'in';
                  continue;
               } else {
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-618'>$team</td>\n";
                  continue;
               }
            }
            
            if ($winners_count_next_week == 0 && !$is_final_week_loop) {  // This group of loosing players all lose again next week.  Time for blessing
               writeDataToFile("551'$player'", __FILE__, __LINE__);
               
               if (!$shootout_begin_week) {
                  if ($a_lookahead[$player][($w + 1)][LOOK_AHEAD_SPECIAL] != 'forfeit') {
                     $a_lookahead[$player][($w + 1)][LOOK_AHEAD_SPECIAL] = 'blessed';
                     $a_player_row[$player][$w] = "         <td inout='in' special='$special' gamestatus='$game_status'  db='sklm-629'>$team</td>\n";
                  } else {
                     $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-631'>$team</td>\n";
                     $a_player_out[$player] = 'out';
                  }
               } else {
                  $a_player_out[$player] = 'out';
                  $a_player_row[$player][$w] = "         <td inout='out' special='$special' gamestatus='$game_status'  db='sklm-636'>$team</td>\n";
               }
               continue;
            }
            
            if ($is_final_week_loop) {
               $a_player_out[$player] = 'out';
               $a_player_row[$player][$w] = "         <td inout='out' special='blessed' gamestatus='$game_status'  db='sklm-636'>$team</td>\n";
               continue;
            }
            
            writeDataToFile("579 winners == 0 category miss: 
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
            
            continue;
         } // END $winners_count == 0
      } // END player
   } // END week   
   
   writeDataToFile("659 support_ko_last_man.php a_player_out(): \n" . print_r($a_player_out, true), __FILE__, __LINE__);
   writeDataToFile("660 support_ko_last_man.php a_player_row(): \n" . print_r($a_player_row, true), __FILE__, __LINE__);
   writeDataToFile("601 week_begin $week_begin game_over_on_week $game_over_on_week ", __FILE__, __LINE__);
   
   $rows = '';
   foreach ($a_players as $player => $a) {
      $winner = (isset($a_player_wins[$player])) ? $a_player_wins[$player] : '';
      $rows .= "      <tr>
         <td $winner >$player</td>\n";
      for ($wk = $week_begin; $wk <= $game_over_on_week; $wk++) {
         if (empty($a_player_row[$player][$wk])) {  // bug - 
            writeDataToFile("668 support_ko_last_man.php Player has empty datapoint in row: player wk:  '$player',  '$wk', row: " . 
               print_r($a_player_row, true), __FILE__, __LINE__);
            // Need to abort here.
         }
         writeDataToFile("674 '$player' '$wk'", __FILE__, __LINE__);
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
      if ($a_lookahead[$player][$w][LOOK_AHEAD_IN_OUT] == 'in') {  // consider only eligible players
         $game_status =  $a_lookahead[$player][$w][LOOK_AHEAD_STATUS];
         switch ($game_status) {
         case 'ip':
         case 'sc':
            $games_pending = true;
            break;
         case 'wi':
            $count++;
            break;
         case 'lo':
            break;
         default:
            $ref_status_text = "Never here: '$player' '$w' '$game_status' '$games_pending'";
            writeDataToFile("$ref_status_text", __FILE__, __LINE__);
            return false;
         }
      }
   }
   return true;
}

?>
