<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: password_reset2.php
   date: orignal
 author: original
   desc: password_reset.php support. 

  notes:
   referencetemplate: pr2
   
*/


require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

// Do not validate

//todo : move to global location for reuse
function areHTMLCharacters($username)
{
	$pattern = '/[<>&]/';
	return preg_match($pattern, $username, $matches);
}

$post_user_name = (!empty($_POST['username'])) ? $_POST['username'] : '';  // user name or email

if (areHtmlCharacters($post_user_name)) {
   formatSessionMessage("The username/email address contains disallowed characters.  Please do not use these 3: < > &", 'info', $msg, 'pr2-27');
   setSessionMessage($msg, 'error');
   header( 'Location: password_reset.php') ;
   die();
}
      
writeDataToFile("thisis the user name passed $post_user_name", __FILE__, __LINE__);
setSessionMessage($post_user_name, 'info', 'resetpasswordusername');

$mysql = '
   SELECT username, fname, id, email, active_status
     FROM users 
    WHERE username = ?
       OR email = ?';

$status = 0;
$ref_status_text = '';
$ref_password = '';
$ermsg = '';
$ref_hash = '';
$ans = '';
$status = 0;
while(1) {
   
   $user_name = '';
   $first_name = '';
   $user_id = '';
   $email = '';
   $account_active = '';
   
   if(!$post_user_name) {
      formatSessionMessage("An email address or user name is required.", 'info', $msg, 'pr2-58');
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!$ans = runSql($mysql, array("ss",  $post_user_name, $post_user_name), 0, $ref_status_text)) {
      if ($ans === false) {
         formatSessionMessage("There has been a database error.", 'danger', $msg, "pr2-65 $ref_status_text'");
         setSessionMessage($msg, 'error');
         break;
      }
      if ($ans === null) {
         formatSessionMessage("The account could not be found.  Please insure the email address or user name (your login name) is correct.", 'info', $msg, 'pr2-70');
         setSessionMessage($msg, 'error');
         break;
      }
   }
    
   $sizeofans = sizeof($ans);
   if ($sizeofans != 1) {
         formatSessionMessage("There is an account problem.  Please contact the administrator.", 'warning', $msg, 'pr2-78');
         setSessionMessage($msg, 'error');
         break;
   }
   
   $user_name          = $ans[0]['username'];
   $first_name         = $ans[0]['fname'];
   $user_id            = $ans[0]['id'];
   $email              = $ans[0]['email'];
   $account_active     = $ans[0]['active_status'];

   if (!$account_active) {
      formatSessionMessage("This account has be deactivated.  No action was taken.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!generateNewPassword($ref_password, $ref_hash)) { 
      formatSessionMessage("A serious error occurred.  Please contact the site administrator.  The password was not changed.", 'danger', $msg, 'pr2-95');
      setSessionMessage($msg, 'error');
      break;
   }
   writeDataToFile("67 $first_name, $email, $ref_password, $ref_hash)", __FILE__, __LINE__);
   if (!reset_password($user_id, $ref_hash, $ref_status_text)) {
      formatSessionMessage("A serious error occurred.  Please contact the site administrator.  The password was not changed.", 'danger', $msg, "pr2-101 '$ref_status_text'");
      setSessionMessage($msg, 'error');
      break;
   }
   if (!recordForgotPasswordChange($user_id, $ref_password, $ref_status_text)) {
      formatSessionMessage("Your password was changed, however, an unknown error occurred and the new one will not be mailed. " .
         "Please contact the site administrator to obtain your new password. $user_id, $ref_password, $ref_status_text", 'warning', $msg, "pr2-107 '$ref_status_text'");
      setSessionMessage($msg, 'error');
      break;
   }
   
   writeDataToFile("!notify_password($first_name, $email, $ref_password)", __FILE__, __LINE__);
   if (!notify_password($first_name, $email, $ref_password)) {
      formatSessionMessage("Your password was changed, however, we were unable to mail you the new password.  Please contact the site administrator.",
         'danger', $msg, "pr2-115 '$ref_password'");
      setSessionMessage($msg, 'error');
      break;
   }
   $status = 1;
   formatSessionMessage("Your password was changed.  The password was mailed to your email account.", 'success', $msg, "pr2-120 $ref_password ");
   setSessionMessage($msg, 'error');
   break;
}

header( 'Location: password_reset.php') ;
die();

?>