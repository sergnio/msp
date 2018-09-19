<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');

// This is the league based add user.  So having a leage to assign to 
// is important.
$league_id = (!empty($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';

$username =  (!empty($_POST['username'])) ? $_POST['username'] : '';
$fname =     (!empty($_POST['fname'])) ? $_POST['fname'] : '';
$lname =     (!empty($_POST['lname'])) ? $_POST['lname'] : '';
$useremail = (!empty($_POST['email'])) ? $_POST['email'] : '';
$new =       (!empty($_POST['new_password'])) ? $_POST['new_password'] : '';
$new2 =      (!empty($_POST['confirm_password'])) ? $_POST['confirm_password'] : '';
$usertype =  (!empty($_POST['usertype'])) ? $_POST['usertype'] : '';  // Not sure about this.  Can this new league play be added as an admin?

// the session for echo
$_SESSION['username_add'] = $username;
$_SESSION['fname_add'] = $fname;
$_SESSION['lname_add'] = $lname;
$_SESSION['email_add'] = $useremail;
$_SESSION['new_password'] = $new;
$_SESSION['confirm_password'] = $new2;
$_SESSION['usertype_add'] = $usertype;

$error = '';
while (1) {  // error frame
   if ((empty($username)) || (empty($fname)) || (empty($lname)) || (empty($new)) || (empty($new2))  || (empty($useremail))  || (empty($usertype))) {
         $error .= '|empty';
         break;  // no point continuing
   }
   if (!valid_email($useremail)) {
      $error .= '|email';
   }
   if ($new != $new2) {
     $error .= '|match';
   }
   if ($usertype != 'user') {  //TODO Determine if league commissioners can add admin users.
      $error .= 'usertype';
   }
   if (!isUniqueUsername($username)) {
      $error .= '|username';
   }
   if (!isValidLeageId($league_id)) {
      $error .= '|league';
   }
   break;
}
writeDataToFile("league_adduser.php error is " . $error, __FILE__, __LINE__);

if (!empty($error)) { 
   $error_report = '&error='.$error; 
   header( 'Location: league_users.php?update=0'.$error_report ) ;
   die();
}

$active_status = 1;
$user_mode = 'user';
$pw_hash = hash('sha256', $new);

while (1) {  // updates
   if(!insertNewUser($username, $pw_hash, $fname, $lname, $useremail, $usertype, 
      $active_status, $ref_new_user_id, $ref_status_text))
   {
      writeDataToFile("league_addnewuser() -> insertNewUser() fail: " . $ref_status_text, __FILE__, __LINE__);
      $error .= '|newuser';
   }
   if(!addLeagueMembershipToUser($addLeagueMembershipToUser, $league_id, $ref_status_text))
   {
      writeDataToFile("league_addnewuser() -> addLeagueMembershipToUser() fail: " . $ref_status_text, __FILE__, __LINE__);
      $error .= '|leaguemembership';
   }
   break;
}

if (!empty($error)) { 
   $error_report = '&error='.$error; 
   header( 'Location: league_users.php?update=0'.$error_report );
   die();
}

header( 'Location: league_users.php?update=1' ) ;
die();

?>