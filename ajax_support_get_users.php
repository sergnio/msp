<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
if (!session_start()) {
   writeDataTofile("ajax_support_get_users.php Session failed to start", __FILE__, __LINE__);
}

/*
:mode=php: 

   file: ajax_support_get_users.php 
   date: apr-2016
 author: hfs
   desc: Used by /league_users.php and /users.php.  These are league scope and
      user scope edit screens accessed by commissioners and site administrators
      respectively.  Both supply a complete set of values for a users table edit.
      Only league scope supplies the player and league active values for table
      nspx_leagueplayer edits.
      
      This file \mypicks02.js has the ajax call that uses this file; as 
      in url: ajax_support_get_users.php 
      
      
      TODO taint checks
                                               
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php'; 
$msg = '';

// league and site edits
@ $do_this =      (!empty($_POST['dothis']))       ? $_POST['dothis']  : ''; 
@ $authority =    (!empty($_POST['authority']))    ? $_POST['authority']  : '';
@ $user_account =  (!empty($_POST['useraccount']))  ? $_POST['useraccount']  : '';
@ $u_type =       (!empty($_POST['utype']))  ? $_POST['utype']  : '';

// league and site edits
@ $username =  (!empty($_POST['username']))  ?  $_POST['username']  : '';
@ $fname =     (!empty($_POST['fname']))     ?  $_POST['fname'] : '';
@ $lname =     (!empty($_POST['lname']))     ?  $_POST['lname'] : '';
@ $email =     (!empty($_POST['email']))     ?  $_POST['email'] : '';
@ $email_old = (!empty($_POST['oldemail']))  ?  $_POST['oldemail'] : '';
@ $actstatus = (!empty($_POST['actstatus'])) ?  $_POST['actstatus'] : '';
      
// Only league edits
@ $league_status_text = (!empty($_POST['leagueactstatus'])) ?  $_POST['leagueactstatus'] : '';
@ $player_name =        (!empty($_POST['playername'])) ?       $_POST['playername'] : '';
@ $player_name_old =    (!empty($_POST['oldplayername'])) ?    $_POST['oldplayername'] : '';
@ $league_id =          (!empty($_POST['leagueid'])) ?         $_POST['leagueid'] : '';
@ $join_date = (!empty($_POST['joindate'])) ?   trim($_POST['joindate']) : '';
@ $paid_text = (!empty($_POST['leaguepaid'])) ? $_POST['leaguepaid'] : '';

$paid = ($paid_text == 'yes') ? 2 : 1;  // may be invalid ... 
$league_status_numeric = ($league_status_text == 'yes') ? 2 : 1;
$active_status_numeric = ($actstatus == 'yes') ? 1 : 0;

$status = 0;
$ermsg_text = '';
$supporting_error = '';
$field = '';
$ref_access_status_message = '';
$ref_status_text = '';
$ermsg_text_array = array();

writeDataToFile('BEGIN C:\xampp\htdocs\nflx\ajax_support_get_users.php', __FILE__, __LINE__);

while (1) {      
   
   writeDataToFile(
      "!do_this $do_this || authority !$authority || user_account !$user_account || username !$username ||
       fname !$fname || lname !$lname || email !$email || email_old !$email_old  || actstatus !$actstatus  || u_type !$u_type || 
       league_status $league_status_text  || player_name  $player_name || player_name_old $player_name_old || 
       league_id $league_id || league_paid $paid || join_date $join_date", __FILE__, __LINE__);

   if (!$do_this || !$authority || !$user_account || !$username || !$fname || !$lname || !$email || !$email_old  || !$actstatus  || !$u_type) {
      formatSessionMessage("There is missing data.  Please submit complete information.", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $supporting_error .= '|missing';
      $ermsg_text = 'There is missing data.  Please submit complete information. (ref:missingall)';
      break;
   }   
   if (!($do_this == 'leagueuseredit' || $do_this == 'siteuseredit')) { 
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $supporting_error .= '|dothis';
      $ermsg_text = 'There was a system error.  Please contact the site adminstrator (ref:unknowninstruction)';
      break;
   }
   if ($do_this == 'leagueuseredit') {
      if (!$league_status_numeric || !$player_name || !$player_name_old || !$league_id || !$paid || !$join_date) {
         formatSessionMessage("TODO", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|missing';
         $ermsg_text = 'There is missing data.  Please submit complete information. (ref:missingleague)';
         break;
      }
      
      if (!($paid == 1 || $paid == 2)) {
         formatSessionMessage("League paid value must be 1 (not paid) or  2 (paid)", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|oneortwo';
         $ermsg_text = 'League paid value must be 1 (not paid) or  2 (paid) (ref:oneortwo)';
         break;
      }
      // expecting 2016-07-12 format
      $pattern = "/^(\d{4})-(\d{2})-(\d{2})$/";
      preg_match($pattern, $join_date, $match);
      if (!checkdate($match[2], $match[3], $match[1])) {
         formatSessionMessage("Expecting a join date in the format of YYYY-MM-DD.  No other format will do.", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|oneortwo';
         $ermsg_text = 'Expecting a join date in the format of YYYY-MM-DD.  No other format will do.';
         break;
      }
      writeDataToFile("data pattern matches: " . print_r($match, true), __FILE__, __LINE__);
   }
   if ($do_this == 'siteuseredit') {
      if (!$u_type) {
         formatSessionMessage("TODO", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|missing';
         $ermsg_text = 'There is missing data.  Please submit complete information. (ref:missingsite)';
         break;
      }
   }
   writeDataToFile('TTTTTtt 84');
   if (!validateUser('user', 'status')) {  // logged in check
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $supporting_error .= '|login';
      $ermsg_text = 'You are not logged into the site.  Please login.';
      break;
   }
   writeDataToFile('TTTTTtt 90');
   if (!($authority == 'commissioner' || $authority == 'admin')) {
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $supporting_error .= 'error';
      $ermsg_text = 'There was a system error.  Please contact the site adminstrator (ref:unknownauthority)';
      break;
   }
   writeDataToFile('TTTTTtt 96');
   if (!validateUser($authority, 'status', $ref_access_status_message)) {   // asof can be 'user',' commissioner', 'admin'
      //writeDataToFile("validateUser fail: access message: " . $ref_access_status_message, __FILE__, __LINE__);
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $supporting_error .= '|authority';
      $ermsg_text = 'You do not have the priviledges required to continue.';
      break;
   }
   writeDataToFile('TTTTTtt 103');
   if (!valid_email($email)) {
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $field = 'email';
      $supporting_error .= '|email';
      $ermsg_text = 'The email is not valid.';
      break;
   }
   writeDataToFile('TTTTTtt 110');
   if (!isValidUserMode($u_type)) {
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $field = 'usermode';
      $supporting_error .= '|usermode';
      $ermsg_text = 'There was a system error.  Please contact the site adminstrator (ref:unknownmode).';
      break;
   }
   
   writeDataToFile('TTTTTtt 118');
   // player names and emails are the two changes that have unique contraints.  Don't fuck this up.
   if (!isUniqueEmailAddress($email, $user_account)) {
      formatSessionMessage("TODO", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $supporting_error .= 'error';
      $ermsg_text = 'The email address must be unique within the site.  This address is already in use. Please create another.)';
      break;
   }

   writeDataToFile('TTTTTtt 127');
   if ($do_this == 'leagueuseredit') {
      if(areDisallowCharacters($player_name)) {
         formatSessionMessage("TODO", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $sub_error = true;
         $supporting_error .= '|characters';
         $ermsg_text = "The league player may contain only alphanumeric characters.  Apostrophes, dashes, and underscores are also allowed."; //, 'warning', $msg);
         break;
      } 
      if (strlen($player_name) < 2) {
         formatSessionMessage("TODO", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $sub_error = true;
         $supporting_error .= '|short';
         $ermsg_text = "Player names must be greater than 1 character."; //, 'warning', $msg);
         break;
      }
      if (!isPlayerNameAvailable($user_account, $league_id, $player_name, $ref_status_text)) {
         formatSessionMessage("TODO", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|playernameunique';
         $ermsg_text = 'Player names within a league must be unique.  This one is already in use.  Please create another.';
         break;
      } 
   }
   
   writeDataToFile('TTTTTtt 138');
   $status = 1;
   break;
}

// Leave this ... callbacks are so touchy
//writeDataToFile("ajax_support_editusers.php\n" .
//   print_r($_SESSION, true) .
//   "\n do: " . $do_this .
//   "\n username: " . $username . 
//   "\n accountnumber: " . $useraccount . 
//   "\n fname: " . $fname . 
//   "\n lname: " . $lname .
//   "\n email: " . $email .
//   "\n mode: " .   $utype .
//   "\n act: " . $actstatus . 
//   "\n field: " . $field . 
//   "\n auth: " . $authority .
//   "\n status: " . $status .
//   "\n errtxt: " . $ermsg_text, __FILE__, __LINE__);


if (!$status) {
   writeDataToFile("AJAX SUPPORT status $status, supporting $supporting_error, ermsg $ermsg_text, field $field", __FILE__, __LINE__);
   $status_array = array('status' => $status, 'supportingerror' => $supporting_error, 'ermsg' => $ermsg_text, 'useraccount' => $user_account, 'field' => $field );
   echo json_encode($status_array);
   ob_end_flush();
   exit();   
}

   writeDataToFile('TTTTTtt 168');
if ($do_this == 'leagueuseredit') { 
   
   $ref_update_status_text = '';
   while (1) {
      writeDataTofile("date to updateLeagueUser(): '$league_id', '$user_account', '$player_name', '$paid', '$join_date', '$league_status_numeric'", __FILE__, __LINE__);
      if (!updateLeagueUser($league_id, $user_account, $player_name, $paid, $join_date, $league_status_numeric, $ref_update_status_text)) {
         $status = 0;
         if ($ref_update_status_text == 'updatefail') {
            formatSessionMessage("The database update failed.  Please contact the site adminstrator.", 'danger', $msg);
            $ermsg_text_array[] = $msg;
            $supporting_error .= '|updatefail';
            $ermsg_text .= 'The database update failed.  Please contact the site adminstrator.';
            break;
         }
         
         formatSessionMessage("An unknown error occurred during the update.  Please contact the site administrator.", 'danger', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= "|$ref_update_status_text";
         $ermsg_text .= 'An unknown error occurred during the update.  Please contact the site administrator.';
         break;
      }
      $status = 1;
      break;
   }
   writeDataToFile("ref_update_status_text $ref_update_status_text", __FILE__, __LINE__);
   if ($status && $ref_update_status_text == 'updatenorows') {  // Failure to update is not an error  sth->affected_rows returns count of CHANGED records - no edit - no change
      $supporting_error .= '|updatenorows';
      formatSessionMessage("No records were updated.  No edits were submitted.", 'info', $msg);
      $ermsg_text_array[] = $msg;
      $ermsg_text .= $msg;
      $status = 1;   // special case
   }
}
   writeDataToFile('TTTTTtt 196');

if ($do_this == 'siteuseredit') {

   if (!updateUser($user_account,$username,$fname,$lname,$email,$u_type,$active_status_numeric,$ref_status_text)){
   writeDataToFile('TTTTTtt 254 udpateUser  failed');
      $status = 0;
      if ($ref_status_text == 'usernameinuse') {
         formatSessionMessage("The current user name is in use.", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|username';
         $ermsg_text .= 'The user name is already in use.  Please choose another login name.';
      } elseif ($ref_status_text == 'usermodenotvalid') {
         formatSessionMessage("The user mode is not valid.", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|username';
         $ermsg_text .= 'The account type is unknown.  Please contact the site administrator.';
      } else {
         formatSessionMessage("There was an error with the account.  The system returned " . "'$ref_status_text'", 'info', $msg);
         $ermsg_text_array[] = $msg;
         $supporting_error .= '|username';
         $ermsg_text .= 'There was an error with the account.  The system returned ' . "'$ref_status_text'";
      }
   }
}

//$status_array = array('status' => $status, 'supportingerror' => $supporting_error, 'ermsg' => '', 'useraccount' => $user_account, 'field' => $field );

$status_array = array('status' => $status, 'supportingerror' => $supporting_error, 'ermsg' => $ermsg_text, 
                      'useraccount' => $user_account, 'field' => $field, 'ermsgarray' => $ermsg_text_array);
$status_array = array('status' => $status);

writeDataToFile("status array before json encode: " . print_r($status_array, true), __FILE__, __LINE__);
$json_formatted = json_encode($status_array);
writeDataToFile("status array json encoded: " . print_r($json_formatted, true), __FILE__, __LINE__);
echo json_encode($status_array);
ob_end_flush();
exit();
?>