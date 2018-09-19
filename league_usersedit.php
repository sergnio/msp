<?php
require_once 'mypicks_startsession.php'; 

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

$error = '';
$id =             (!empty($_POST['id'])) ?            $_POST['id'] :       '';
$username =       (!empty($_POST['username'])) ?      $_POST['username'] : '';
$fname =          (!empty($_POST['fname'])) ?         $_POST['fname'] :    '';
$lname =          (!empty($_POST['lname'])) ?         $_POST['lname'] :    '';
$email =          (!empty($_POST['email'])) ?         $_POST['email'] :    '';
$active_status =  (!empty($_POST['leagueactive'])) ?  $_POST['leagueactive'] : '';

// This the league based copy of users.
//validateUser('commissioner');

writeDataToFile(" league_edit_user $error,
$id,    
$username,
$fname, 
$lname,
$email,
$usertype,
$active_status" , __FILE__, __LINE__);
   
   
   
while (1) {
   if (empty(trim($username))) {
      $error .= '|empty';
      break;
   }
   if (!valid_email($email)) {
      $error .= '|email';
   }   
   if (!isValidUserMode($usertype)) {  // select - will never fail unless empty
      $error .= '|usertype';
   }
   if (isUserNameAvailable($id, $username, $ref_status_text)) {  // The user name edit has been disabled at the league level
      if ($ref_status_text === 'usernameinuse') {
         $error .= '|username';
      }
   }
   break;
}

      writeDataToFile("league_usersedit.php -> updateUser() error: " . $error, __FILE__, __LINE__);

if (!empty($error)) { 
   $error_report = '&error='.$error; 
   $_SESSION['to_getusers_from_usersedit_error_in_edit'] = $id;  // this is a "message" and deleted immediately
   writeDataToFile("league_usersedit.php error! " . $error_report, __FILE__, __LINE__);
   header( 'Location: league_users.php?update=0'.$error_report ) ; 
   die();
}

while (1) {  // final checks, update
   if(!updateUser($id, $username, $fname, $lname, $email, 
     $usertype, $active_status, $ref_status_text))
   {
      writeDataToFile("league_usersedit.php -> updateUser() fail: " . $ref_status_text, __FILE__, __LINE__);
      $error .= '|newuser';
      header( 'Location: league_users.php?update=0&error='.$error ) ;
      die();
   }
   break;
}

header( 'Location: league_users.php?update=1' ) ;
die();
?>