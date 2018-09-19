<?php
require_once 'mypicks_startsession.php';


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
$msg = '';

$error = '';
@ $id = $_POST['edituserbutton'];
@ $id = sql_sanitize($id);
@ $id = html_sanitize($id);
@ $username = $_POST['username'];
@ $username = sql_sanitize($username);
@ $username = html_sanitize($username);
@ $fname = $_POST['fname'];
@ $fname = sql_sanitize($fname);
@ $fname = html_sanitize($fname);
@ $lname = $_POST['lname'];
@ $lname = sql_sanitize($lname);
@ $lname = html_sanitize($lname);
@ $email = $_POST['email'];
@ $email = sql_sanitize($email);
@ $email = html_sanitize($email);
@ $usertype = $_POST['usertype'];
@ $usertype = sql_sanitize($usertype);
@ $usertype = html_sanitize($usertype);
@ $active_status_yesno = $_POST['active_status'];  // yes/no
@ $active_status_yesno = sql_sanitize($active_status);
@ $active_status_yesno = html_sanitize($active_status);

$active_status = $active_status_yesno;
if ($active_status_yesno == 'yes' || $active_status_yesno == 'no') {
   $active_status = ($active_status_yesno == 'yes') ? 1 : 0;
}

writeDataToFile('usersedit.php   $error$id =$username =fname =lname =email =      ertype =active_status' . "$error,
$id,    
$username,
$fname, 
$lname,
$email,
$usertype,
$active_status" , __FILE__, __LINE__);

while (1) {
   if (empty($id) || empty($username) || empty($fname) || empty($lname) 
     || empty($email) || ($usertype === 'noselect') || empty($active_status))
   {
      $error .= '|empty';
      break;
   }
   if (!valid_email($email)) {
      $error .= '|email';
   }
   if (!isValidUserMode($usertype)) {  // select - will never fail unless empty
      $error .= '|usertype';
   }
   if (isUserNameAvailable($id, $username)) {
      $error .= '|username';
   }
   break;
}

if (!empty($error)) { 
   $_SESSION['to_getusers_from_usersedit_error_in_edit'] = $id;  // this is a "message" and deleted immediately
   header( 'Location: users.php?update=0&error='.$error ) ;
   die();
}
   
if (validateUser('admin', 'status')) {
   if (updateUser($id,$username,$fname,$lname,$email,$usertype,$active_status,$ref_status_text)){
      header( 'Location: users.php?update=1' ) ;
      die();
   } elseif ($ref_status_text === 'usernameinuse') {
      $error .= '|username';
      header( 'Location: users.php?update=0&error='.$error ) ;
      die();
   } else {
      header( 'Location: users.php?update=0&error=error' ) ;
      die();
   }
} else {
   header( 'Location: users.php?update=0&error=|rights' ) ;
   die();
}
?>