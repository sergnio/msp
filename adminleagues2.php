<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

$msg = '';
validateUser('admin');

$selector_league_id = (!empty($_POST['leagueselector'])) ? $_POST['leagueselector'] : '';
   
$edit_league =    (!empty($_POST['editleague'])) ?   $_POST['editleague'] : '';
$edit_player =    (!empty($_POST['editplayer'])) ?   $_POST['editplayer'] : '';
$edit_league_id = (!empty($_POST['editleagueid'])) ? $_POST['editleagueid'] : '';

$checkbox_all_league_status =  (!empty($_POST['activeleaguesonly'])) ? $_POST['activeleaguesonly'] : '';
$league_status_selection = ($checkbox_all_league_status == 'on') ? 'both' : 'active';
setSessionInfo('adminleaguestatusselect', $league_status_selection);

writeDataToFile("edit commands: '$edit_league', '$edit_player', '$edit_league_id' '$selector_league_id'
   '$checkbox_all_league_status'  '$league_status_selection'", __FILE__, __LINE__);

// He just selected a league.  Abandon edits and go back and repaint the page with new league info.
if ($selector_league_id) {
   setSessionInfo('adminleagueselect', $selector_league_id);
   header("Location: adminleagues.php");
   die();
} else {
   setSessionInfo('adminleagueselect', $edit_league_id);
}

setSessionInfo('adminleagueselect', $edit_league_id);  // supplied by both league and player edit sections

$league_name =     (isset($_POST['leaguename']))   ? trim($_POST['leaguename']  )   : '';
$commissioner =    (isset($_POST['commissioner'])) ? trim($_POST['commissioner'])   : '';
$found_date =      (isset($_POST['founddate']))    ? trim($_POST['founddate']   )   : '';
$league_type =     (isset($_POST['leaguetype']))   ? trim($_POST['leaguetype']  )   : '';
$league_points =   (isset($_POST['leaguepoints'])) ? trim($_POST['leaguepoints'])   : '';
$league_picks =    (isset($_POST['leaguepicks']))  ? trim($_POST['leaguepicks'] )   : '';
$league_push =     (isset($_POST['leaguepush']))   ? trim($_POST['leaguepush']  )   : '';
$league_active =   (isset($_POST['leagueactive'])) ? trim($_POST['leagueactive'])   : '';
$first_round =     (isset($_POST['firstround']))   ? trim($_POST['firstround']  )   : '';
$last_round =      (isset($_POST['lastround']))    ? trim($_POST['lastround']   )   : '';

if ($edit_player) {
   $arrayx = $_POST;
   writeDataToFile("player edit post array: " . print_r($arrayx, true), __FILE__, __LINE__);
}


$status = 0;
$msg = '';
while ($edit_league) {
   if (
      $league_name   === ''  ||   
      $commissioner  === ''  ||  
      $found_date    === ''  ||    
      $league_type   === ''  ||   
      $league_points === ''  || 
      $league_picks  === ''  ||  
      $league_push   === ''  ||   
      $league_active === ''  || 
      $first_round   === ''  ||   
      $last_round    === ''
   ) {   
      formatSessionMessage("There is missing information.  The league cannot be updated.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $nfl_last_first_round_week = NFL_LAST_WEEK;
   $nfl_last_first_round_week--;
   if ($first_round < 1 || $first_round > $nfl_last_first_round_week) {
      formatSessionMessage("<b>FRN</b> The first round must be valued 1 thru $nfl_last_first_round_week.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if ($last_round < 0 || $last_round > NFL_LAST_WEEK) {   
      formatSessionMessage("<b>LRN</b> The last round must be valued 0 thru 17.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if ($last_round != 0 && ($last_round <= $first_round)) {   
      formatSessionMessage("<b>LRN</b> The last round must be greater than the first round value.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!($league_push == 1 || $league_push == 2 || $league_push == 3)){   
      formatSessionMessage("<b>PSH</b> League push option must be 1, 2 or 3.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!($league_active == 0 || $league_active == 1)){   
      formatSessionMessage("<b>ACT</b> League active option must be 0 or 1.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!($league_points == 1 || $league_points == 2)){   
      formatSessionMessage("<b>PTS</b> League spread option must be 1 or 2.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!($league_type == 1 || $league_type == 2 || $league_type == 3)){   
      formatSessionMessage("<b>LGT</b> League type must be valued 1, 2 or 3.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if ($league_picks < 1 || $league_picks > 11 ){   // magic TODO
      formatSessionMessage("<b>PCK</b> League picks must be valued 1 thru 11.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   // expecting yyyy-mm-dd hh:mm:ss
   $pattern = "/^(\d{4})-(\d{2})-(\d{2}) +(\d{2}):(\d{2}):(\d{2})$/"; 

   preg_match($pattern, $found_date, $match);
   writeDataToFile("adminleage2() match: " . print_r($match, true), __FILE__, __LINE__);
   if (sizeof($match) != 7) {
      formatSessionMessage("<b>DFN</b> Expecting a league founded date in the format of YYYY-MM-DD 24:MM:SS.  No other format will do.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!checkdate($match[2], $match[3], $match[1])) {
      formatSessionMessage("<b>DFN</b> The founded date, date part, is invalid.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $hour = $match[4];
   $min = $match[5];
   $sec = $match[6];
   if ($hour > 23 || $min > 59 || $sec > 59) {
      formatSessionMessage("<b>DFN</b> The founded date, time part, is invalid.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   $mysql = "
      update league 
         set league_name   = ?,
             commissioner  = ?,
             found_date    = ?,
             league_type   = ?,
             league_points = ?,
             league_picks  = ?,
             league_push   = ?,
             active        = ?,
             firstround    = ?,
             lastround     = ?
       where league_id     = ?
       limit 1";
       
   $conn = db_connect();
   
   if (!$conn) {
      formatSessionMessage("The database is not available.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   writeDataToFile("
league_name    '$league_name   '
commissioner   '$commissioner  '
found_date     '$found_date    '
league_type    '$league_type   '
league_points  '$league_points '
league_picks   '$league_picks  '
league_push    '$league_push   '
active         '$league_active '
first_round    '$first_round   '
last_round     '$last_round    '
edit_league_id '$edit_league_id'", __FILE__, __LINE__);
   
   $sth = $conn->prepare($mysql);
   $sth->bind_param("sisiiiiiiii", 
                           $league_name   ,
                           $commissioner  ,
                           $found_date    ,
                           $league_type   ,
                           $league_points ,
                           $league_picks  ,
                           $league_push   ,
                           $league_active ,
                           $first_round   ,
                           $last_round    ,
                           $edit_league_id);
   if (!$sth->execute()) {
      $sql_error_message = $sth->error;
      formatSessionMessage("No update was made. The update failed to execute. League id '$edit_league_id'.  sqlerr $sql_error_message", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $update_count = $sth->affected_rows;
   writeDataToFile("count $update_count", __FILE__, __LINE__);
   if ($update_count === 0) {
      formatSessionMessage("No update was made. There was nothing to update.", 'info', $msg);
      setSessionMessage($msg, 'error');
      $status = 1;
      break;
   }
   if ($update_count > 1) {
      formatSessionMessage("A serious error has occurred. Stop.  League id '$edit_league_id'.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if ($update_count == 1) {
      formatSessionMessage("Update was successful.", 'success', $msg);
      setSessionMessage($msg, 'happy');
   }
   
   $status = 1;
   break;
}

if (!empty($sth)) {
   @$sth->close();
}

if ($edit_league && !$status) {
   header( 'Location: adminleagues.php' ) ;
   die();
}

$status = 1;
while ($edit_player) {
   
   $user_id = '';
   foreach ($_POST['playereditbutton'] as $key_id => $val_id) {
      $user_id = $val_id;
      break;
   }

   $player_active_text = (isset($_POST['playeractive'][$user_id])) ? $_POST['playeractive'][$user_id] : '';
   $player_paid_text = (isset($_POST['playerpaid'][$user_id])) ? $_POST['playerpaid'][$user_id] : '';
   $player_join_date  = (isset($_POST['playerjoindate'][$user_id])) ? $_POST['playerjoindate'][$user_id] : '';
   $player_name = (isset($_POST['playerplayername'][$user_id])) ? $_POST['playerplayername'][$user_id] : '';

   $player_active = ($player_active_text == 'yes') ? 2 : 1;
   $player_paid = ($player_paid_text == 'yes') ? 2 : 1;   
   
   writeDataToFile(" active '$player_active'  
'$player_paid'   
'$player_join_date'", __FILE__, __LINE__);
   
   if (!$player_active_text || !$player_paid_text) {
      formatSessionMessage("Player active/paid cannot be empty.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!local_oneOrTwo('playeractive', $player_active)
      | !local_oneOrTwo('playerpaid', $player_paid))
   {
      break;
   }
   
   if (!$player_name) {
      formatSessionMessage("Player name cannot be empty.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   } 
   if(areDisallowCharactersSpace($player_name)) {
      formatSessionMessage("The league player may contain only alphanumeric characters.  Apostrophes, dashes, spaces and underscores are also allowed.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (strlen($player_name) < 2 || strlen($player_name) > 16) {
      formatSessionMessage("The player name must be 2 to 16 characters in length.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!isPlayerNameAvailable($user_id, $edit_league_id, $player_name, $ref_status_text)) {
      formatSessionMessage("The player is already in use.  Please create another.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   $mysql_player = "
      update nspx_leagueplayer 
         set playername = ?,
             active = ?,
             paid = ?,
             joindate = ?
       where leagueid  = ?
         and userid = ?
       limit 1";
       
   $conn = db_connect();
   if (!$conn) {
      formatSessionMessage("The database is not available.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   writeDataToFile("update info: '$player_name,
                                 '$player_active,
                                 '$player_paid,
                                 '$player_join_date,
                                 '$edit_league_id,
                                 '$user_id", __FILE__, __LINE__);
   
   $sth = $conn->prepare($mysql_player);
   $sth->bind_param("siisii", $player_name,
                              $player_active,
                              $player_paid,
                              $player_join_date,
                              $edit_league_id,
                              $user_id);
   
   if (!$sth->execute()) {
      $sql_error_message = $sth->error;
      formatSessionMessage("No updates were made. The update failed to execute. League id '$edit_league_id'.  sqlerr $sql_error_message", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   $update_count = $sth->affected_rows;
   @$sth->close();
   
   if ($update_count === 0) {
      formatSessionMessage("No update was made. There was nothing to update.", 'info', $msg);
      setSessionMessage($msg, 'error');
      $status = 1;
      break;
   }
   if ($update_count > 1) {
      formatSessionMessage("A serious error has occurred. Stop.  League id '$edit_league_id' cnt $update_count.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if ($update_count == 1) {
      formatSessionMessage("Update was successful.", 'success', $msg);
      setSessionMessage($msg, 'happy');
   }
   
   $status = 1;
   break;
}

if (!empty($sth)) {
   @$sth->close();
}

header( 'Location: adminleagues.php' ) ;
die();



function local_oneOrTwo(
   $var_name,
   $value
) {

   $msg = '';
   if (!$value  || !($value == 1 || $value == 2)) {
      formatSessionMessage("Var '$var_name' must be valued 1 or 2.", 'info', $msg);
      setSessionMessage($msg, 'error');
      return 0;
   }
   return 1;
}


?>