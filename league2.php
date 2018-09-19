<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: league2.php
   date: apr-2016
 author: origninal
   desc: This is the submit target for league_login.php
  notes:
  
marbles: 
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

/* setting is a hidden input variable in league_login.php */
$setting =     (isset($_POST['setting'])) ? sanitize($_POST['setting']) : '';  // hot or cold

writeDataToFile("TOP league2.php TOP league2.php TOP league2.php TOP league2.php TOP league2.php  POST, SESSION, SERVER : " .
   "POST: " . print_r($_POST, true) .
   "SESSION: " .  print_r($_SESSION, true) .
   "SERVER: " . print_r($_SERVER, true),
   __FILE__, __LINE__);

if (empty($setting)) { 
   writeDataToFile("Setting is empty,  exit now", __FILE__, __LINE__);
   exit;
}

$valid_user_logged_in = validateUser('user', 'status');

// These are global since the league check is done with a file scoped function.
$league_name =   (isset($_POST['league_name'])) ? trim($_POST['league_name']) : '';
$league_type =   (isset($_POST['league_type'])) ? sanitize($_POST['league_type']) : '';
$league_points = (isset($_POST['league_points'])) ? sanitize($_POST['league_points']) : '';
$league_push =   (isset($_POST['league_push'])) ? sanitize($_POST['league_push']) : '';
$league_player = (isset($_POST['league_player'])) ? trim($_POST['league_player']) : '';
$league_picks =  (isset($_POST['league_picks'])) ? sanitize($_POST['league_picks']) : '';
$league_start =  (isset($_POST['league_seasonbegins'])) ? sanitize($_POST['league_seasonbegins']) : '';

writeDataToFile("League type is '$league_type' league_picks '$league_picks'", __FILE__, __LINE__);

$_SESSION['register_league_name'] = $league_name;
$_SESSION['register_league_type'] = $league_type;
$_SESSION['register_league_points'] = $league_points;
$_SESSION['register_league_picks'] = $league_picks;
$_SESSION['register_league_player'] = $league_player;
$_SESSION['register_league_push'] = $league_push;
$_SESSION['register_league_start'] = $league_start;

writeDataToFile("league name '$league_name'", __FILE__, __LINE__);

//writeDataToFile("SESSION  TOP league2.php: " . print_r($_SESSION, true), __FILE__, __LINE__);
//writeDataToFile("POST  TOP league2.php: " . print_r($_POST, true), __FILE__, __LINE__);

// use sub_error to watch 'accumulated' no break errors.  It is used everywhere
// to allow edit of error processing - early break or continue checking.
$error = false;
$sub_error = false;

if (!$valid_user_logged_in) {  // No user - check all the form
   
   $error = true; // the break error
   
   $username =       (isset($_POST['register_username'])) ? trim($_POST['register_username']) : '';
   $fname =          (isset($_POST['register_fname'])) ?    trim($_POST['register_fname']) : '';
   $lname =          (isset($_POST['register_lname'])) ?    trim($_POST['register_lname']) : '';
   $useremail =      (isset($_POST['register_email'])) ?    trim($_POST['register_email']) : '';
   $new =            (isset($_POST['register_new'])) ?      trim($_POST['register_new']) : '';
   $new2 =           (isset($_POST['register_new2'])) ?     trim($_POST['register_new2']) : '';  // confirm password
   
   writeDataToFile("user name is '$username'", __FILE__, __LINE__);
   
   while (1) {
      
      if(!filled_out($_POST)) {
         $sub_error = true;
         formatSessionMessage('Please complete the form.', 'warning', $msg);
         setSessionMessage($msg, 'error');
         break;   // further check superfluous
      }
      
      if(areDisallowCharacters($username)) {
         $sub_error = true;
         formatSessionMessage("The user name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if (strlen($username) > USER_NAME_MAX_LENGTH) {
         $sub_error = true;
         formatSessionMessage("The user name must be " .  USER_NAME_MAX_LENGTH . " characters or less.  Please shorten your user name.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if (!isUniqueUsername($username)) {
         $sub_error = true;
         $name_option = generate_username($username, 0);
         formatSessionMessage("The user name is already in use.  Please consider '$name_option'.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if(areDisallowCharacters($fname)) {
         $sub_error = true;
         formatSessionMessage("The user's first name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if(areDisallowCharacters($lname)) {
         $sub_error = true;
         formatSessionMessage("The user's last name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if (!isUniqueEmailAddress($useremail)) {
         $sub_error = true;
         formatSessionMessage("The email address is already in use.  Please choose another.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if (!valid_email($useremail)) {
         $sub_error = true;
         formatSessionMessage("The email address is not valid.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if (strcasecmp($new, $new2) !== 0) {
         $sub_error = true;
         formatSessionMessage("The passwords do not match.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      
      if (!checkNewLeagueFormDataCold(
          $league_name,
          $league_type,
          $league_points,
          $league_push,
          $league_player,
          $league_start))
      {
         $sub_error = true;
         break;
      }
      $error = false;
      break;
   }
   
   // record what the cold user is trying to do
   
   $_SESSION['register_username'] = $username;
   $_SESSION['register_fname'] = $fname;
   $_SESSION['register_lname'] = $lname;
   $_SESSION['register_email'] = $useremail;
   $_SESSION['register_league_player'] = $league_player;
   $_SESSION['register_new'] = $new;
   $_SESSION['register_new2'] = $new2; 
   
}  // END cold user

writeDataToFile ("name type points push player: $league_name, $league_type, $league_points, $league_push, $league_player, error $error", __FILE__, __LINE__);

if ($error || $sub_error) {
   header( 'Location: league_login.php') ;   
   die();
}

// There were no new user information problems - if there was a new user request
// ====================================================================================================================
if (!$valid_user_logged_in) {
   
   $hash_password = hash('sha256', $new);
   //$conn = db_connect();
   
   $ref_new_user_id = '';
   if(!insertNewUser($username, $hash_password, $fname, $lname,
         $useremail, 'user', 1, $ref_new_user_id, $ref_status_text))
   {
      formatSessionMessage("The system failed to install the new user '$username'.  Please contact the site administrator.", 'danger', $msg,
         "l2-183 $username', '$hash_password', '$fname', '$lname', '$useremail', '$ref_new_user_id',  text: $ref_status_text");
      setSessionMessage($msg, 'error');
      writeDataToFile("league2.php - insertNewUser($username, $hash_password, $fname, $lname,
         $useremail, 'user', 1, $ref_new_user_id, $ref_status_text) Failed.\n  Session: " . 
         print_r($_SESSION, true), __FILE__, __LINE__);
      header('Location: index.php') ;  
      die();
   }
   $last_userid = $ref_new_user_id;
   
   // Heat the new inserted user.  
   // NB! - login scrubs the SESSION!
   // NB! - Set the active league SESSION suit to the new league after it's created.
   if (login($username, $new2)) {
      $valid_user_logged_in = true;
   } else {
      // Everything was checked.  This can only be a system error.
      formatSessionMessage("The system failed to login the new user '$username'.  Please contact the site administrator. This may be a redundant alert.  (ref:login2)", 'danger', $msg);
      setSessionMessage($msg, 'login');
      header( 'Location: index.php') ;  
      die();
   }
      
   // A successful login scrubs SESSION.
   $_SESSION['register_league_name'] = $league_name;
   $_SESSION['register_league_type'] = $league_type;
   $_SESSION['register_league_points'] = $league_points;
   $_SESSION['register_league_picks'] = $league_picks;
   $_SESSION['register_league_player'] = $league_player;
   $_SESSION['register_league_push'] = $league_push;   
}

$last_userid = $_SESSION['user_id'];

//=====================================================================================================================

if ($valid_user_logged_in) {
writeDataToFile("hot user", __FILE__, __LINE__);
   
   // League checks
   $league_complete = 0;
   $ref_new_league_id = '';
   $error = false; 
   // All this has been checked for a new user.  For a new user, this should not fail
   if(!checkNewLeagueFormDataHot(
       $league_name,
       $league_type,
       $league_points,
       $league_push,
       $league_player,
       $league_start,
       $ref_league_id)) 
   {
      $error = true;
      writeDataToFile("!checkNewLeagueFormDataHot(
         $league_name,
         $league_type,
         $league_points,
         $league_push,
         $league_player,
         $error,
         $ref_league_id)", __FILE__, __LINE__
      );
   }
   
   if ($error) {
      header( 'Location: league_login.php') ;   
      die();
   }
   
//=====================================================================================================================

   
   writeDataToFile("ref_league_id = '$ref_league_id'", __FILE__, __LINE__); 
   
   if (!$ref_league_id) {  // If the league is already created, skip all this.
      writeDataToFile("the league type being tested is '$league_type'", __FILE__, __LINE__);
      if ($league_type == LEAGUE_TYPE_COHORT || $league_type == LEAGUE_TYPE_LAST_MAN) {
         // Only one pick is allowed.
         $league_picks = 1;
      }
      if(!insertNewLeague($league_name, $last_userid, $league_type, 
         $league_points, $league_picks, $league_push, $league_player, $league_start, $ref_new_league_id, $ref_status_text)) 
      {
         formatSessionMessage("The system failed to install the new league '$league_name'.  Please contact the site administrator. (ref:nl4)", 'danger', $msg);
         setSessionMessage($msg, 'error');
         writeDataToFile("insert_new_league((ref:nl4)): " . $ref_status_text, __FILE__, __LINE__);
         header( 'Location: index.php') ;   
         die();
         
      } else { 
         // get the established league id
         $ref_new_league_id = getLeagueId($league_name);
      }
   }
   
   if (!empty($error)) {
      $error_report = '?ermsg='.$error; 
      header( 'Location: index.php' . $error_report ) ;   
      die();
   }
   
   
//=====================================================================================================================
   $membership_update_success = 0;
   $ref_status_text = '';
   if (addLeagueMembershipToUser($last_userid, $ref_new_league_id, $ref_status_text)) {
      $membership_update_success = 1;
   } elseif ($ref_status_text == 'alreadyleaguemember') { // Accomodate subsequent failures below
      $membership_update_success = 1;
   } else {
      formatSessionMessage("The system was unable to add the user to league '$league_name'.  Please contact the site administrator. (ref:almn66)", 'danger', $msg);
      setSessionMessage($msg, 'error');
      writeDataToFile("league2.php (ref:almn66) addLeagueMembershipToUser($last_userid, $ref_new_league_id, $ref_status_text) failed.  Session: \n" . 
         print_r($_SESSION, true), __FILE__, __LINE__);
      header('Location: index.php');
      die();
   }
   
   // The user is logged in.  He has just created a new league and memebership is recorded
   // Initialize his active league env
   $return_val = setSessionActiveLeague($_SESSION['user_id'], $ref_new_league_id, $ref_status_text);
   writeDataToFile("setSessionActiveLeague return val '$return_val'", __FILE__, __LINE__);
   if (!$return_val) {
      formatSessionMessage("The system was unable to initialize properly.  Please logout and login again. (ref:setal279)", 'danger', $msg);
      setSessionMessage($msg, 'error');
      writeDataToFile("fail (ref:setal279) setSessionActiveLeague($uid, $ref_new_league_id)  ref status: $ref_status_text", __FILE__, __LINE__); 
      header('Location: index.php');
      die();
      
   }
      
   $insert_text =  '<h2 style="font-style:italic; text-align:center;"><span style="font-size:36px;">';
   $insert_text .= 'Welcome to MySuperPicks.com</span></h2><br /><p style="text-align:center;" class="lead">';
   $insert_text .= 'Show off your NFL mind!</p><br />';
   
   // Text field fieldname is the league_id.
   $text_update_success = 0;
   if (!insertOrUpdateLeagueGreetingText($ref_new_league_id, $insert_text, $ref_status_text)) {
      formatSessionMessage("The system was unable to update the league greeting.  Please contact the site administrator. (ref:gm293)", 'danger', $msg);
      setSessionMessage($msg, 'error');
      writeDataToFile("fail insertOrUpdateLeagueGreetingText($ref_new_league_id, $insert_text, $ref_status_text), SESSION: " . 
         print_r($_SESSION, true), __FILE__, __LINE__);
      header( 'Location: index.php') ;   
      die();
   } else {
      $text_update_success = 1;
   }

   cleanOutRegisterLeagueSessionVars();

   writeDataToFile("league2 complete AFTER login: " . print_r($_SESSION, true) . "\n\nSERVER: " . print_r($_SERVER, true), __FILE__, __LINE__);   
   
   if ($membership_update_success && $text_update_success) {
      $user_name_for_display = $_SESSION['valid_user'];
      $league_name_for_display = $_SESSION['league_name'];
      formatSessionMessage("Congratulations.  User '$user_name_for_display' is now the Admin of the new league <i>$league_name_for_display</i>.  You may now proceed to <b>League Commissioner</b> and invite others to join and play.", 'success', $msg);
      setSessionMessage($msg, 'happy');
      header( 'Location: index.php');  
      die();
   }
   
   // most of the failures were serious enough to redirect and fail.  You shouldn't be here.
   header( 'Location: index.php?neverhere=1' ) ;  
   die();
}

// These are the global values in this file
// $league_name =
// $league_type =
// $league_points
// $league_push =
// $league_player
function checkNewLeagueFormDataCold(
   $league_name,
   $league_type,
   $league_points,
   $league_push,
   $league_player,
   $league_start
){
   // League checks
   $league_complete = 0;
   $ref_new_league_id = '';
   $status = 0;
   $sub_error = false;
   $msg = '';
   while(1) {
      
      if (empty($league_name) || empty($league_player)
          || empty($league_type)) {
         formatSessionMessage('Please complete the form.', 'warning', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      if(areDisallowCharactersSpace($league_name)) {
         $sub_error = true;
         formatSessionMessage("The league name may contain only alphanumeric characters.  Apostrophes, dashes, underscores and spaces are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      if(areDisallowCharacters($league_player)) {
         $sub_error = true;
         formatSessionMessage("The league player may contain only alphanumeric characters.  Apostrophes, dashes, and underscores are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      if (strlen($league_player) < 2 || strlen($league_name) < 2) {
         $sub_error = true;
         formatSessionMessage("The league name and player names must be greater than 1 character.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      if(!isValidLeagueType($league_type)) {
         formatSessionMessage("The league type is unknown.  Please contact the site administrator.  (ref:LT2$league_type)", 'danger', $msg);
         setSessionMessage($msg, 'error');
         break;
      }      
      if ($league_type == LEAGUE_TYPE_COHORT || $league_type == LEAGUE_TYPE_LAST_MAN) {
         if ($league_start < 1 || $league_start > NFL_LAST_WEEK) {
            formatSessionMessage("The league's first season week of play is not valid.  (ref:lswc$league_start)", 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
         }
      }     
      if(!($league_push == LEAGUE_PUSH_ZERO  || $league_push == LEAGUE_PUSH_HALF || $league_push == LEAGUE_PUSH_ONE )) {
         formatSessionMessage("The league push option is unknown.  Please contact the site administrator.  (ref:LT3$league_type)", 'danger', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      
      //=====================================================================================================================   
      if (!isUniqueLeagueName($league_name)) {
         formatSessionMessage('The league name is already in use.  Please choose another.', 'warning', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      if (!$sub_error) {
         $status = 1;
      }
      break;
   }
   return $status;
}

function checkNewLeagueFormDataHot(
   $league_name,
   $league_type,
   $league_points,
   $league_push,
   $league_player,
   $league_start,
   &$ref_league_id   // This is set if the hot user is already the commissioner of the league
){
   // League checks
   $league_complete = 0;
   $ref_new_league_id = '';
   
   writeDataToFile("437 checkNewLeagueFormDataHot(
   $league_name,
   $league_type,
   $league_points,
   $league_push,
   $league_player,
   $league_start", __FILE__, __LINE__);
   $msg = '';
   
   $status = 0;
   $sub_error = false;
   while(1) {
      
      if (empty($league_name) || empty($league_player)
          || empty($league_type)) {
         formatSessionMessage('Please complete the form.', 'warning', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      if(areDisallowCharactersSpace($league_name)) {
         $sub_error = true;
         formatSessionMessage("The league name may contain only alphanumeric characters.  Apostrophes, dashes, underscores and spaces are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      if(areDisallowCharacters($league_player)) {
         $sub_error = true;
         formatSessionMessage("The league player name may contain only alphanumeric characters.  Apostrophes, dashes, and underscores are also allowed.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      if (strlen($league_player) < 2 || strlen($league_name) < 2) {
         $sub_error = true;
         formatSessionMessage("The league name and player names must be greater than 1 character.", 'warning', $msg);
         setSessionMessage($msg, 'error');
      }
      if(!isValidLeagueType($league_type)) {
         formatSessionMessage("The league type is unknown.  Please contact the site administrator.  (ref:LT3$league_type)", 'danger', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      if ($league_type == LEAGUE_TYPE_COHORT || $league_type == LEAGUE_TYPE_LAST_MAN) {
         if ($league_start < 1 || $league_start > NFL_LAST_WEEK) {
            formatSessionMessage("The league's first season week of play is not valid.  (ref:lswh$league_start)", 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
         }
      }     
      if(!($league_push == LEAGUE_PUSH_ZERO  || $league_push == LEAGUE_PUSH_HALF || $league_push == LEAGUE_PUSH_ONE )) {
         formatSessionMessage("The league push option is unknown.  Please contact the site administrator.", 'danger', $msg, "l2-484 '$league_type'");
         setSessionMessage($msg, 'error');
         break;
      }
      
      writeDataToFile("isUniqueLeagueName($league_name) running now....  ===================================================", __FILE__, __LINE__);
      //=====================================================================================================================   
      if (!isUniqueLeagueName($league_name)) {
         writeDataToFile("League is NOT unique $league_name", __FILE__, __LINE__);
         
         $user_id_x = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
         if (!$user_id_x) {
            formatSessionMessage('The league name is already in use.  Please choose another.', 'info', $msg, "l2-496 $league_name");
            setSessionMessage($msg, 'error');
            $sub_error = true;
            $ref_new_league_id = getLeagueId($league_name);
         }
         if (getLeagueCommissionerViaLeagueName($league_name) == $user_id_x) {
            formatSessionMessage('The league exists.  You are the Admin.  No action will be taken.', 'info', $msg, "l2-501 $league_name");
            setSessionMessage($msg, 'error');
            $sub_error = true;
            $ref_new_league_id = getLeagueId($league_name);
         } else {
            formatSessionMessage('The league name is already in use.  Please choose another.', 'info', $msg, "l2-507 $league_name");
            setSessionMessage($msg, 'error');
            $sub_error = true;
            break;
         }
      }
      if (!$sub_error) {
         $status = 1;
      }
      break;
   }
   writeDataToFile("END checkNewLeagueFormDataHot status is '$status', ref new league '$ref_new_league_id'", __FILE__, __LINE__);
   return $status;
}


function isValidLeagueType(
   $league_type
){
   $status = 0;
   $msg = '';
   while(1) {
      if (empty($league_type)){
         break;
      }
      if (!($league_type == LEAGUE_TYPE_PICKUM
         || $league_type == LEAGUE_TYPE_COHORT
         || $league_type == LEAGUE_TYPE_LAST_MAN ))
      {
         break;
      }
      $status = 1;
      break;
   }
   return $status;
}

function cleanOutRegisterLeagueSessionVars() {
   unset(
     $_SESSION['register_league_name'],
     $_SESSION['register_league_type'],
     $_SESSION['register_league_points'],
     $_SESSION['register_league_picks'],
     $_SESSION['register_league_player'],
     $_SESSION['register_league_push'],
     $_SESSION['register_league_start']
   );
}
?>