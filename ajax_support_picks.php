<?php
ob_start();
if (!session_start()) {
   writeDataTofile("ajax_support_picks.phpSession failed to start", __FILE__, __LINE__);
}

/*
:mode=php: 

   file: ajax_support_picks.php 
   date: may-2016
 author: hfs
   desc: This is server side support for picks in mypicks03.js
      
      TODO taint checks
                                               
*/

require_once 'mypicks_def.php'; 
//require_once 'site_fns_diminished.php';
require_once 'mysql_min_support.php';
require_once 'mypicks_db.php';
//require_once 'mypicks_phpgeneral.php';  
$msg = '';

$bug_ignore_gamestart = false;

@ $do_this =      (!empty($_POST['dothis']))       ? $_POST['dothis']  : '';     // adding, removing - maybe ignored in extensive testing
@ $authority =    (!empty($_POST['authority']))    ? $_POST['authority']  : '';  // adding, removing - maybe ignored in extensive testing
@ $schedule_id =  (!empty($_POST['scheduleid']))   ? $_POST['scheduleid']  : '';
@ $home_away =    (!empty($_POST['homeaway']))     ? $_POST['homeaway'] : '';
@ $user_id =      (!empty($_POST['userid']))       ? $_POST['userid'] : '';
@ $week =         (!empty($_POST['week']))         ? $_POST['week'] : '';
@ $league_id =    (!empty($_POST['leagueid']))     ? $_POST['leagueid'] : '';
@ $pick_id =      (!empty($_POST['gamepickid']))   ? $_POST['gamepickid'] : '';
@ $pick_limit =   (!empty($_POST['picklimit']))   ? $_POST['picklimit'] : '';
@ $my_friend_pick_id =    (!empty($_POST['myfriendpickid']))     ? $_POST['myfriendpickid'] : '';

// If he's adding to his picks make sure the limit is not exceeded.
$user_name = getUsernameViaId($user_id);
$text = "BEGIN "       .
   "\n do_this: "      .            $do_this             .
   "\n authority: "    .            $authority           .
   "\n schedule_id: "  .            $schedule_id         .
   "\n home_away: "    .            $home_away           .
   "\n user_id: "      .            $user_id             .
   "\n week: "         .            $week                .
   "\n league_id: "    .            $league_id           . 
   "\n my_friend_pick_id: "    .    $my_friend_pick_id   . 
   "\n user_name: "    .            $user_name           . 
   "\n pick_id: " .                 $pick_id             . 
   "\n END";
   
writeDataToFile($text, __FILE__, __LINE__);


$mysql_is_game_started = "
   select 'started'
     from schedules
    where schedule_id = ?
      and gametime is not null
      and gametime < now()
    limit 1";
      
$mysql_current_pick_count = "
   select count(*)
     from schedules s left join picks p on
                        s.schedule_id = p.schedule_id 
                        and p.user = (select username from users where id = ? limit 1) 
                        and p.league_id = ?
    where s.week = ?
      and p.pick_id is not null"; 

$mysql_add_pick = "
   insert 
     into picks (user, home_away, schedule_id, pick_time, league_id)
   values       (    ?,        ?,           ?,     now(),         ?)";
   
// pick_id is unique - boilerplate.
$mysql_swap = "
      update picks
         set home_away = ?
      where schedule_id = ?
        and league_id = ?
        and user = ?
        and pick_id = ?
        limit 1";
        
$mysql_remove = "
   delete
     from picks
    where pick_id = ?
      and user = ?
      and league_id = ?
      and schedule_id = ?
    limit 1";
      
$system_error_header = 'There has been a system error.  Please contact the site administrator.';
$status = 0;
$ref_status_text = '';
$return_parameter = '';
$database_action = '';
$database_action_ermsg = '';
$has_game_started = '';
while (1) {
   
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;
      
   $session_user_id = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
   
   if ($user_id != $session_user_id ) {  // taint TODO validateUser out of site_fns...
      $database_action_ermsg = $system_error_header . 'ref (asp-112 id!=id)';
      break;
   }
   
   if (!isLeagueMember($user_id, $league_id)) {
      $database_action_ermsg = $system_error_header . 'ref (asp-117 !member)';
      break;
   }

   try {
      $conn = db_connect();
      $sth = $conn->prepare($mysql_is_game_started);
      $sth->bind_param("i", $schedule_id);
      $sth->execute();
      $sth->bind_result($has_game_started);
      $sth->fetch();
      //writeDataToFile("games has started result is: $has_game_started, schedid $schedule_id", __FILE__, __LINE__);
   } catch (mysqli_sql_exception $e) {
      $ermsg = 'ajax_support_picks()  \n' .
         'sql: ' . $mysql_is_game_started . "\n\n" .
         'league_id ' . $league_id . "\n" .
         'user_id ' . $user_id . "\n" .
         'week ' . $week . "\n" .
         'MYSQL ERROR TO STRING: ' . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'dberror';
      break;
   }
   @ $sth->close();

   if ($has_game_started === 'started') {
      // game has started - picks are closed
      $database_action_ermsg = 'The game has started.  No changes are allowed.';
      break;
   }

   $current_pick_count = -1;
   try {
      $conn = db_connect();
      $sth = $conn->prepare($mysql_current_pick_count);
      $sth->bind_param("iii", $user_id, $league_id, $week);
      $sth->execute();
      $sth->bind_result($current_pick_count);
      $sth->fetch();
   } catch (mysqli_sql_exception $e) {
      $ermsg = 'ajax_support_picks()  \n' .
         'sql: ' . $mysql_current_pick_count . "\n\n" .
         'league_id ' . $league_id . "\n" .
         'user_id ' . $user_id . "\n" .
         'week ' . $week . "\n" .
         'MYSQL ERROR TO STRING: ' . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'dberror';
      break;
   }
   @ $sth->close();
   
   $league_pick_limit = getLeagueLimit($league_id);
   writeDataToFile("league_pick_limit '$league_pick_limit'", __FILE__, __LINE__);
   $league_pick_limit = ($league_pick_limit == PICK_ALL_GAMES) ? 100 : $league_pick_limit; 

   while (1) {    // Determine database action frame
      
      if ($current_pick_count < 0) {
         // never here - bad something
         $database_action_ermsg = $system_error_header . '(ref asp-177 p<0)';
         break;
      }
      
      if ($current_pick_count > $league_pick_limit) {
         // Database is corrupted.  There should not be more than the allowed number of picks
         $database_action_ermsg = $system_error_header . "(ref asp-182 p>limit $current_pick_count)";
         break;
      }
      
      if ($pick_id != -1 && $my_friend_pick_id != -1) {
         // Page is corrupted.  Both home and away are selected.
         $database_action_ermsg = $system_error_header . "(ref asp-189!=-1!=-1 $pick_id)";
      }
      
      if ($pick_id == -1 && $my_friend_pick_id == -1 && ($current_pick_count == $league_pick_limit)) {
         $database_action_ermsg = 'You may not choose additional teams.  This would exceed the league\'s pick limit.';
         break;
      }
      
      if ($pick_id == -1 && $my_friend_pick_id == -1) {  // pick limit was tested just above
         // He's not changing home-away.  This is a brand new pick.  He will not exceed the pick limit.
         $database_action = 'insert';
         break;
      }
      
      if ($pick_id == -1 && $my_friend_pick_id != -1) {
         // Just a home-away swap.  There is no impact on the pick limit.
         $database_action = 'swap';
         break;
      }
      
      if ($pick_id != -1 ) {
         // He's removing the pick.
         $database_action = 'remove';
         break;
      }
      
      $database_action_ermsg .= " (ref nhere)";
      break;
   }  //END database action frame
   
   writeDataToFile("ajax_support_picks.php end logic $database_action, $database_action_ermsg", __FILE__, __LINE__);
   
   
   while (1 && !$database_action_ermsg) {    // database actions: insert, remove, swap
      if ($database_action == 'insert') {
         try {
            $conn = db_connect();
            $sth = $conn->prepare($mysql_add_pick);
            $sth->bind_param("ssii", $user_name, $home_away, $schedule_id, $league_id);
            $sth->execute();
            $return_parameter =  $sth->insert_id;
            if ($return_parameter == 0) {
               $database_action_ermsg = 'No record was inserted.';
               break;
            }
            $status = 1;
            break;
         } catch (mysqli_sql_exception $e) {
            $ermsg = 'ajax_support_picks()  \n' .
               'sql: ' . $mysql_add_pick . "\n\n" .
               'league_id ' . $league_id . "\n" .
               'user_name ' . $user_name . "\n" .
               'schedule_id ' . $schedule_id . "\n" .
               'home_away ' . $home_away . "\n" .
               'MYSQL ERROR TO STRING: ' . $e->__toString();
            writeDataToFile($ermsg, __FILE__, __LINE__);
            $ref_status_text = 'dberror';
            break;
         }
      }
      if ($database_action == 'swap') {
         try {
            // myfriend is selected, select me instead; if h set a, if a set h
            $conn = db_connect();
            $sth = $conn->prepare($mysql_swap);
            $sth->bind_param("siisi", $home_away, $schedule_id, $league_id, $user_name, $my_friend_pick_id);
            $sth->execute();
            $return_parameter = $sth->affected_rows;
            if ($return_parameter == 0) {
               $database_action_ermsg = 'No record was updated.';
               break;
            }
            $status = 1;
            break;
         } catch (mysqli_sql_exception $e) {
            $ermsg = 'ajax_support_picks()  \n' .
               'sql: ' . $mysql_swap . "\n\n" .
               'league_id ' . $league_id . "\n" .
               'user_name ' . $user_name . "\n" .
               'schedule_id ' . $schedule_id . "\n" .
               'pick_id ' . $pick_id . "\n" .
               'home_away ' . $home_away . "\n" .
               'MYSQL ERROR TO STRING: ' . $e->__toString();
            writeDataToFile($ermsg, __FILE__, __LINE__);
            $ref_status_text = 'dberror';
            break;
         }
      }
      if ($database_action == 'remove') {
         try {
            $conn = db_connect();
            $sth = $conn->prepare($mysql_remove);
            $sth->bind_param("isii", $pick_id, $user_name, $league_id, $schedule_id);
            $sth->execute();
            $return_parameter = $sth->affected_rows;
            if ($return_parameter == 0) {
               $database_action_ermsg = 'No record was removed.';
               break;
            }
            $status = 1;
            break;
         } catch (mysqli_sql_exception $e) {
            $ermsg = 'ajax_support_picks()  \n' .
               'sql: ' . $mysql_remove . "\n\n" .
               'pick_id ' . $pick_id . "\n" .
               'league_id ' . $league_id . "\n" .
               'user_name ' . $user_name . "\n" .
               'schedule_id ' . $schedule_id . "\n" .
               'home_away ' . $home_away . "\n" .
               'MYSQL ERROR TO STRING: ' . $e->__toString();
            writeDataToFile($ermsg, __FILE__, __LINE__);
            $ref_status_text = 'dberror';
            break;
         }
      }
      // no database actions were taken
      break;

   }  //END database actions frame
   break;
}

if (isset($sth)) { @$sth->close(); }

$status_array = array(
   'status' => $status, 
   'ermsg' => $database_action_ermsg,
   'databaseaction' => $database_action, 
   'returnparameter' => $return_parameter,
   'refstatuscheck' => $ref_status_text
   );

writeDataToFileAlways("ajax_support_picks.php  Status array: " . print_r($status_array, true), __FILE__, __LINE__);
echo json_encode($status_array);

header("Content-type: application/json; charset=utf-8");
ob_end_flush();
exit();

?>