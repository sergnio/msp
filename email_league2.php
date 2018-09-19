<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');

$subject = (!empty($_POST['name'])) ? trim($_POST['name']) : '';  // is subject 
$email_list = (!empty($_POST['email'])) ? trim($_POST['email']) : '';
$email_list = sql_sanitize($email_list);
$email_list = html_sanitize($email_list);
$message = (!empty($_POST['message'])) ? trim($_POST['message']) : '';
$message = sql_sanitize($message);
$message = html_sanitize($message);

$_SESSION['emailleague'] = $email_list;
$_SESSION['messageleague'] = $message;

$error = '';
$status = 0;
$usermail = '';
while(1) {
   
   if(contact_form_filled_out($_POST)) {
       formatSessionMessage("Please complete the form.", 'info', $msg);
       setSessionMessage($msg, 'error');
       break;
   }
   
   $useremail = explode(',', $email_list);
   foreach ($useremail as $email) {
      $email = trim($email);
      if (!valid_email($email)) {
         if (!$error) {
            $error = $email;
         } else {
            $error .= ", " . $email;
         }
      }
   }
   if ($error) {
      formatSessionMessage("The following email addresses were not valid.  Please correct.  No mail was sent.<br />$error", 'warning', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $status = 1;
   break;
}

if (!$status) {
   header( 'Location: email_league.php') ;
   die();
}

$failed_addresses = '';
$fail_count = 0;
$total_emails = 0;
foreach ($useremail as $email) {
   $total_emails++;
   $temail = trim($email);
   $to_address = $email; 
   $email_subject = $subject;
   $fromaddress = "From: " . MAIL_FROM_NO_REPLY; 
   if(!mail($to_address, $subject, $message, $fromaddress)) {
      $fail_count++;
      if (!$failed_addresses) {
         $failed_addresses = $to_address;
      } else {
         $failed_addresses .= ", $to_address";
      }
   }
}
$say_email = ($total_emails > 1) ? 'emails were' : 'email was';
$say_failed_email = ($fail_count > 1) ? 'emails were' : 'email was';

if ($fail_count == $total_emails) {
   formatSessionMessage("An unknown mailing error occurred.  No $say_email successfully sent.", 'warning', $msg);
   setSessionMessage($msg, 'error');
} elseif ($fail_count) {
   formatSessionMessage("An unknown mailing error occurred.  $fail_count $say_failed_email not sent.  Failed:<br />
      $failed_addresses.", 'warning', $msg);
   setSessionMessage($msg, 'error');
} else {
   formatSessionMessage("$total_emails $say_email sent.  Delivery may be delayed on a busy server.", 'success', $msg);
   setSessionMessage($msg, 'happy');
}

header( 'Location: email_league.php') ;
die();

?>