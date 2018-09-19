<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: adduser.php
   date: apr-2016
 author: original
   desc: 
marbles: 
   note: 
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

validateUser('admin');

@ $username_add =         (!empty($_POST['username_add'])) ?           $_POST['username_add'] :      '';
@ $fname_add =            (!empty($_POST['fname_add'])) ?              $_POST['fname_add'] :         '';
@ $lname_add =            (!empty($_POST['lname_add'])) ?              $_POST['lname_add'] :         '';
@ $usertype_add =         (!empty($_POST['usertype_add'])) ?           $_POST['usertype_add'] :      'noselect';
@ $email_add =            (!empty($_POST['email_add'])) ?              $_POST['email_add'] :         '';
@ $new_password_add =     (!empty($_POST['new_password_add'])) ?       $_POST['new_password_add'] :  '';
@ $confirm_password_add = (!empty($_POST['confirm_password_add'])) ?   $_POST['new_password_add'] :  '';
   
   // add user in users.php did not present a league option.  This was the default.  It was removed
   //@ $league_id = (!empty($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';
   
writeDataToFile("adduser.php ", __FILE__, __LINE__);

// repeater
$_SESSION['username_add'] = $username_add;
$_SESSION['fname_add'] = $fname_add;
$_SESSION['lname_add'] = $lname_add;
$_SESSION['email_add'] = $email_add;
$_SESSION['new_password_add'] = $new_password_add;
$_SESSION['confirm_password_add'] = $confirm_password_add;
$_SESSION['usertype_add'] = $usertype_add;

$error = '';
while (1) { // error frame
   if ((empty($username_add)) || (empty($fname_add)) || (empty($lname_add)) || (empty($email_add)) || (empty($new_password_add)) || (empty($confirm_password_add)) || (empty($usertype_add))) {
      $error .= '|empty';
      break;
   }
   if ($usertype_add == 'noselect') {
      $error .= '|usertype';
   } 
   if (!valid_email($email_add)) {
      $error .= '|email';
   }
   if ($new_password_add !== $confirm_password_add) {
     $error .= '|match';
   }
   if (!isUniqueUsername($username_add)) {
      $error .= '|username';
   }
   // activate if a league option is for add new user is installed.
   // The 'league' error is still active in users.php
   //if (!isValidLeageId($league_id)) {
   //   $error .= '|league';
   //}
   break;
}

if (!empty($error)) { 
   writeDataToFile("is saying there is an error!  " . $error, __FILE__, __LINE__);
   $error_report = '&error='.$error; 
   header( 'Location: users.php?update=0'.$error_report ) ; 
   die();
}

$active_status = 1;
$pw_hash = hash('sha256', $confirm_password_add);

while (1) {  // updates
   if(!insertNewUser($username_add, $pw_hash, $fname_add, $lname_add, $email_add, $usertype_add, 
      $active_status, $ref_new_user_id, $ref_status_text))
   {
      writeDataToFile("adduser.php -> insertNewUser() fail: " . $ref_status_text, __FILE__, __LINE__);
      $error .= '|newuser';
      break;
   }
   // Activate if the league option gets included
   //if(!addLeagueMembershipToUser($ref_new_user_id, $league_id, $ref_status_text))
   //{
   //   writeDataToFile("addusers.php -> addLeagueMembershipToUser() fail: " . $ref_status_text, __FILE__, __LINE__);
   //   $error .= '|leaguemembership';
   //   break;
   //}
   break;
}
writeDataToFile("error is " . $error, __FILE__, __LINE__);
if (!empty($error)) { 
   $error_report = '&error='.$error; 
   header( 'Location: users.php?update=0'.$error_report ) ; 
   die();
}

header( 'Location: users.php?update=1' ) ;
die();

?>