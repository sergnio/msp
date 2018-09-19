<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
if (!session_start()) {
   writeDataTofile("ajax_support_picks.phpSession failed to start", __FILE__, __LINE__);
}

/*
:mode=php: 

   file: ajax_support_player_names.php 
   date: may-2016
 author: hfs
   desc: This is server side support for player names 
      
                                               
*/

require_once 'mypicks_def.php'; 
require_once 'site_fns_diminished.php';
//require_once 'mysql_min_support.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';  
$msg = '';

@ $do_this =      (!empty($_POST['dothis']))       ? $_POST['dothis']  : ''; 
@ $authority =    (!empty($_POST['authority']))    ? $_POST['authority']  : '';
@ $league_id =    (!empty($_POST['leagueid']))     ? $_POST['leagueid'] : '';
@ $user_id_post = (!empty($_POST['userid']))       ? $_POST['userid'] : '';
@ $player_name_change =  (!empty($_POST['playernamechange']))   ? trim($_POST['playernamechange'])  : '';

validateUser();
$user_id = $_SESSION['user_id'];

$user_name = getUsernameViaId($user_id);
$text = "BEGIN C:\xampp\htdocs\nflx\ajax_support_player_names.php" .
   "\n do_this: "      .  $do_this     .
   "\n authority: "    .  $authority   .
   "\n user_id: "      .  $user_id     .
   "\n league_id: "    .  $league_id   . 
   "\n END";
   
//writeDataToFile($text, __FILE__, __LINE__);

$mysql = "
   update nspx_leagueplayer
      set playername = ?
    where leagueid = ?
      and userid = ?
    limit 1";
      
$system_error_header = 'There has been a system error.  Please contact the site administrator. ';
$status = 0;
$ref_status_text = '';
$return_parameter = -1;
while (1) {
   
   $play_name_length = strlen($player_name_change);
   
   if ($play_name_length < 1) {
      $ref_status_text = 'The player name cannot be empty.';
      break;
   }
   if ($play_name_length > 16) {
      $ref_status_text = 'The player name can not exceed 16 characters.';
      break;
   }
   
   if ($user_id_post != $_SESSION['user_id'] ) {  // taint TODO validateUser out of site_fns... 
      $ref_status_text = $system_error_header . 'ref (id!=id)';
      break;
   }
   
   if (!isLeagueMember($user_id, $league_id)) {
      $ref_status_text = $system_error_header . 'ref (!member)';
      break;
   }
   
   $ref_text = '';
   if (!isPlayerNameAvailable($user_id, $league_id, $player_name_change, $ref_text)) {
      $ref_status_text = 'This player name is already in use.  Create another.';
      break;
   }
   if ($ref_text == 'usedbyself') {  // case may differ but name is already used by this user_id
      $ref_status_text = 'This is your present player name.  No edits were made.';
      $status = 1;
      break;
   }

   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_ALL;
   
   try {
      $conn = db_connect();
      $sth = $conn->prepare($mysql);
      $sth->bind_param("sii", $player_name_change, $league_id, $user_id);
      $sth->execute();
      $return_parameter = $sth->affected_rows;
      @ $sth->close();
   } catch (mysqli_sql_exception $e) {
      $ermsg = 'ajax_support_player_names()  \n' .
         'sql: ' . $mysql . "\n\n" .
         'league_id ' . $league_id . "\n" .
         'user_id ' . $user_id . "\n" .
         'player_name_change ' . $player_name_change . "\n" .
         'MYSQL ERROR TO STRING: ' . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'dberror';
      break;
   }
   break;
}

$status_array = array(
   'status' => $status, 
   'ermsg' => $ref_status_text,
   'returnparameter' => $return_parameter
   );

//writeDataToFile("ajax_support_player_names.php - status array: " . print_r($status_array, true), __FILE__, __LINE__);
echo json_encode($status_array);
ob_end_flush();
exit();

?>