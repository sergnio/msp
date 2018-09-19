<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

$register_user_name =    (!empty($_POST['register_username'])) ?        $_POST['register_username'] : '';
$register_first_name =   (!empty($_POST['register_fname'])) ?           $_POST['register_fname'] : '';
$register_last_name =    (!empty($_POST['register_lname'])) ?           $_POST['register_lname'] : '';
$register_email =        (!empty($_POST['register_email'])) ?           $_POST['register_email'] : '';  
$register_pw01 =         (!empty($_POST['register_new'])) ?             $_POST['register_new'] : '';
$register_pw02 =         (!empty($_POST['register_new2'])) ?            $_POST['register_new2'] : '';
$register_player_name =  (!empty($_POST['register_player_name'])) ?     $_POST['register_player_name'] : '';
$register_league_id =    (!empty($_SESSION['join_league_id'])) ?  $_SESSION['join_league_id'] : '';
//$confirm_code =          (!empty($_SESSION['registerconfirmcode'])) ?      $_SESSION['registerconfirmcode'] : '';
//$register_confirm_email = (!empty($_SESSION['registerconfirmemail'])) ?    $_SESSION['registerconfirmemail'] : '';

unset($_SESSION['register_new']);
unset($_SESSION['register_new2']);

$_SESSION['register_username']       = $register_user_name;   
$_SESSION['register_fname']          = $register_first_name;
$_SESSION['register_lname']          = $register_last_name;
$_SESSION['register_email']          = $register_email;
$_SESSION['register_player_name']    = $register_player_name;

if (!$register_league_id ) {
   formatSessionMessage("We are unable to register.", 'warning', $msg, 'noleagueid');
   setSessionMessage($msg, 'error');
   header( 'Location: index.php');
}

$status = 0;
$sub_error = false;
$msg = '';
while(1) {      
   if (empty($register_user_name) || empty($register_first_name) || empty($register_last_name) || empty($register_email) || empty($register_pw01) || empty($register_pw02)) {
      writeDataToFile("register3() $register_user_name || !$register_first_name || !$register_last_name || !$register_pw01 || !$register_pw02", __FILE__, __LINE__);
      formatSessionMessage("Please complete the form.", 'warning', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if(areDisallowCharacters($register_user_name, true)) {
      $sub_error = true;
      formatSessionMessage("The user name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.  The first character must be a letter.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if(areDisallowCharacters($register_first_name, true)) {
      $sub_error = true;
      formatSessionMessage("The user's first name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.  The first character must be a letter.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if(areDisallowCharacters($register_last_name, true)) {
      $sub_error = true;
      formatSessionMessage("The user's last name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.  The first character must be a letter.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if (!valid_email($register_email)) {
      $sub_error = true;
      formatSessionMessage("The email address is not valid.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if (strlen($register_user_name) > USER_NAME_MAX_LENGTH || strlen($register_user_name) < 2) {
      $sub_error = true;
      formatSessionMessage("The user name must be at least 2 characters and not longer than " .  USER_NAME_MAX_LENGTH . " characters in length.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if (strlen($register_player_name) > PLAYER_NAME_MAX_LENGTH || strlen($register_player_name) < 2) {
      $sub_error = true;
      formatSessionMessage("The player name must be at least 2 characters and not longer than " .  PLAYER_NAME_MAX_LENGTH . " characters in length.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   
   if ($sub_error) {
      break;
   }
   
   if (!isUniqueUsername($register_user_name)) {
      $sub_error = true;
      $name_option = generate_username($register_user_name, 0);
      formatSessionMessage("The user name is already in use.  Please consider '$name_option'.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }   
   if (!valid_email($register_email)) {
      $sub_error = true;
      formatSessionMessage("The email address is not valid.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if (!isUniqueEmailAddress($register_email)) {
      $sub_error = true;
      formatSessionMessage("The email address is already in use.  Please choose another.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   if (!isPlayerNameAvailable(0, $register_league_id, $register_player_name, $ref_status_text)) {
      $sub_error = true;
      formatSessionMessage("The player name is already in use.  Please choose another.", 'warning', $msg, $ref_status_text);
      setSessionMessage($msg, 'error');
   }
   if (strcasecmp($register_pw01, $register_pw02) !== 0) {
      $sub_error = true;
      formatSessionMessage("The passwords do not match.", 'warning', $msg);
      setSessionMessage($msg, 'error');
   }
   $status = 1;
   break;
}

if (!$status || $sub_error) {      
   header( 'Location: league_join_register.php' ) ;
   die();
}

// die die die
$ref_new_user_id = '';
$hash_password = hash('sha256', $register_pw01);

if(!insertNewUser($register_user_name, $hash_password, $register_first_name, $register_last_name,
      $register_email, 'user', 1, $ref_new_user_id, $ref_status_text))
{
   formatSessionMessage("The system failed to install the new user '$register_user_name'.  Please contact the site administrator.  (ref:ijnu2)", 'danger', $msg, 
      "r3-120 '$register_user_name', '$hash_password', '$register_first_name', '$register_last_name', '$register_email' '$ref_new_user_id', text: $ref_status_text");
   setSessionMessage($msg, 'error');
   writeDataToFile("league2.php - insertNewUser($register_user_name, $hash_password, $register_first_name, $register_last_name,
      $register_email, 'user', 1, $ref_new_user_id, $ref_status_text) Failed.\n  Session: " . 
      print_r($_SESSION, true), __FILE__, __LINE__);
   header('Location: index.php') ;  
   die();
}

// Heat the new inserted user.  
// NB! - login scrubs the SESSION!
// NB! - Set the active league SESSION suit to the new league after it's created.
if (login($register_user_name, $register_pw01)) {
   $valid_user_logged_in = true;
} else {
   // Everything was checked.  This can only be a system error.
   formatSessionMessage("The system failed to login the new user '$register_user_name'.  Please contact the site administrator. This may be a redundant alert.",
      'danger', $msg, "r3-137");
   setSessionMessage($msg, 'login');
   header( 'Location: index.php') ;  
   die();
}


$user_id = $_SESSION['user_id'];    // login was successful should be here

//=====================================================================================================================
$membership_update_success = 0;
$ref_status_text = '';
if (addLeagueMembershipToUser($user_id, $register_league_id, $ref_status_text)) {
   $membership_update_success = 1;
} elseif ($ref_status_text == 'alreadyleaguemember') { // Accomodate subsequent failures below
   $membership_update_success = 1;
} else {
   formatSessionMessage("The system was unable to add your league membership to '$league_name'.  Please contact the site administrator.", 'danger', $msg,
      "r3-155 $ref_status_text");
   setSessionMessage($msg, 'login');
   writeDataToFile("register3.php (ref:almn66) addLeagueMembershipToUser($user_id, $register_league_id, $ref_status_text) failed.  Session: \n" . 
      print_r($_SESSION, true), __FILE__, __LINE__);
   header('Location: index.php');
   die();
}

if (!insertNewPlayer($register_league_id, $user_id, $register_player_name, $ref_status_text)) {
   formatSessionMessage("The system was unable to save your player name.  Please contact the administrator (ref:inp162)", 'danger', $msg, 
      "r3-166 $ref_status_text");
   setSessionMessage($msg, 'login');
   header('Location: index.php');
   die();
}

// The user is logged in.  He has just created a new league and memebership is recorded
// Initialize his active league env
if (!setSessionActiveLeague($user_id, $register_league_id, $ref_status_text)) {
   formatSessionMessage("The system was unable to initialize properly.  Please logout and login again.", 'danger', $msg, 
      "r3-176 $ref_status_text");
   setSessionMessage($msg, 'login');
   writeDataToFile("fail (ref:setal279) setSessionActiveLeague($user_id, $register_league_id)  ref status: $ref_status_text", __FILE__, __LINE__); 
   header('Location: index.php');
   die();
}
   
$user_name_for_display = $_SESSION['valid_user'];
$league_name_for_display = $_SESSION['league_name'];

// nix the good login message
//clearSessionMessageCategory('login');  // TODO
formatSessionMessage("Congratulations '$user_name_for_display'.  You are logged in and a player in league <i>$league_name_for_display</i>  Your league player name is $register_player_name.", 'success', $msg);
setSessionMessage($msg, 'happy');
header( 'Location: index.php');  
die();

?>