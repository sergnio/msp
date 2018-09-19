<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

$debug_mode = true;  // limit site mailin

$subject = (!empty($_POST['name'])) ? trim($_POST['name']) : '';        // is subject 
$email_list_additional = (!empty($_POST['email'])) ? trim($_POST['email']) : '';   // additional emails to include with the 'site' addresses
$message = (!empty($_POST['message'])) ? trim($_POST['message']) : '';

$subject = sanitize($subject);
$message = sanitize($message);
$email_list_additional = sanitize($email_list_additional);

writeDataToFile("post: " . print_r($_POST, true), __FILE__, __LINE__);

$_SESSION['emailsite'] = $email_list_additional;
$_SESSION['messagesite'] = $message;

$error = '';
$status = 0;
$usermail = '';
$additional_useremail = array();
while(1) {
   
   if(!$message || !$subject) {
      formatSessionMessage("Please complete the form. (Additional emails are not required.)", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   writeDataToFile("additional email: $email_list_additional", __FILE__, __LINE__);
   if ($email_list_additional) {
      $additional_useremail = explode(',', $email_list_additional);
      foreach ($additional_useremail as $email) {
         $email = trim($email);
         if (!valid_email($email)) {
            if (!$error) {
               $error = $email;
            } else {
               $error .= ", " . $email;
            }
         }
      }
   }
   
   if ($error) {
      formatSessionMessage("The following added email addresses were not valid.  Please correct.  No mail was sent.<br />$error", 'warning', $msg);
      setSessionMessage($msg, 'error');
      break;
   }

   
   $ref_site_email_list = '';
   if (!getEmailCommaList('site', $ref_site_email_list, '', $ref_status_text)) {
      formatSessionMessage("The site email is unavailable.", 'info', $msg);
      setSessionMessage($msg, 'error');
      header("Location: index.php");
      die();
   }
   
   $status = 1;
   break;
}

if (!$status) {
   header( 'Location: email_site.php') ;
   die();
}

writeDataToFile("$ref_site_email_list", __FILE__, __LINE__);

$failed_addresses = '';
$fail_count = 0;
$total_emails = 0;
if ($email_list_additional) {
   foreach ($additional_useremail as $email) {
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
}

$limit = 10;
if ($ref_site_email_list) {
   $site_email = explode(',', $ref_site_email_list);
   foreach ($site_email as $email) {
      if ($debug_mode && $limit-- < 1) {
         break;
      }
      $total_emails++;
      $temail = trim($email);
      $to_address = $email; 
      $email_subject = $subject;
      $fromaddress = "From: " . MAIL_FROM_NO_REPLY; 
      if(!mail($to_address, $subject, $message, $fromaddress)) {  // There a mass mailer somewhere... TODO
         $fail_count++;
         if (!$failed_addresses) {
            $failed_addresses = $to_address;
         } else {
            $failed_addresses .= ", $to_address";
         }
      }
   }
}


$say_email = ($total_emails > 1) ? 'emails were' : 'email was';
$say_failed_email = ($fail_count > 1) ? 'emails were' : 'email was';

if ($fail_count == $total_emails) {
   formatSessionMessage("An unknown mailing error occurred. No $say_email sent.  (Attempted $total_emails)", 'warning', $msg);
   setSessionMessage($msg, 'error');
} elseif ($fail_count) {
   formatSessionMessage("An unknown mailing error occurred. $fail_count $say_failed_email not sent.  Failed:<br />
      $failed_addresses.", 'warning', $msg);
   setSessionMessage($msg, 'error');
} else {
   formatSessionMessage("$total_emails $say_email sent.  Delivery may be delayed on a busy server.", 'success', $msg);
   setSessionMessage($msg, 'happy');
}

header( 'Location: email_site.php') ;
die();

?>