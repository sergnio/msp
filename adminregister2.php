<?php
require_once 'mypicks_startsession.php';
/*
:mode=php:

   file: adminregister2.php
   date: jul 2016
 author: original
   desc: 

  notes:
   referencetemplate: ar2
   
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');
$dev_turn_off_mail_call = false;
$site_name = ADMIN_TABLE;
if ($site_name = 'mysuperpicks') {
   $dev_turn_off_mail_call = false;
} else if ($site_name == 'nflbrain') {
   $dev_turn_off_mail_call = false;
}

$register_email = sanitize(trim($_POST['register_email']));

if (empty($register_email)) {
   formatSessionMessage("No addresses.  No mail was sent.", 'warning', $formatted_msg);
   setSessionMessage($formatted_msg, 'error');
   header( 'Location: adminregister.php') ;
   die();
}
      
$_SESSION['register_email'] = $register_email;
$first_name = (!empty($_SESSION['fname'])) ? $_SESSION['fname'] : '';
$last_name =  (!empty($_SESSION['lname'])) ? $_SESSION['lname'] : '';

$useremail = explode(',', $register_email);
$error = '';
foreach ($useremail as $email) {
   $email = trim($email);
   if (!valid_email($email)) {
      if (!$error) {
         $error = $email;
      } else {
         $error .= ", $email";
      }
   }
}

if ($error) {
   formatSessionMessage("These addresses, within the <b>()</b>, are not valid: <b>( $error )</b>.  No emails were sent.", 'warning', "ar2-57 $formatted_msg");
   setSessionMessage($formatted_msg, 'error');
   header( 'Location: adminregister.php?hahahh=1') ;
   die();
}

$league_name = (!empty($_SESSION['league_name'])) ? $_SESSION['league_name'] : '';
$league_id = (!empty($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';
if (!$league_name || !$league_id) {
   formatSessionMessage("League information is required to send mail.", 'warning', "ar2-66 $formatted_msg");
   setSessionMessage($formatted_msg, 'error');
   header( 'Location: adminregister.php') ;
   die();
}

$mysql = '
   INSERT INTO temp_confirm 
          (confirm_date, confirm_code, confirm_email, used, league_id, mailingerror) 
   VALUES (       NOW(),            ?,             ?,    0,         ?,            0)';
   
$mysql_error = '
      UPDATE temp_confirm 
         SET mailingerror = 1
       WHERE confirm_code = ?';
       

$mailing_status = false;      // set true if the script runs 
$mailing_failed = false;      // if any mail() executions fail
$mailing_success = false;     // if any mail() executions succeed
while (1) {
   
   $link_confirm = getLinkConfirm($ref1);
   $link_contact = getLinkContact($ref2);
   
   writeDataToFile("adminregister2 $link_confirm, $link_contact, $ref1, $ref2", __FILE__, __LINE__); 
   if (!$link_confirm) {
      formatSessionMessage('The confirm link is unavailable.  No mail can be sent at this time.', 'info', $msg, "ar2-93");
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$link_contact) {
      formatSessionMessage('The site contact address is unavailable.  No mail can be sent at this time.', 'info', $msg, "ar2-98");
      setSessionMessage($msg, 'error');
      break;
   }
   $link_confirm .= '?confirmcode=';
   $conn = db_connect();
   if (!$conn) {
      writeDataToFile("Failed to connect to the database.", __FILE__, __LINE__);
      break;
   }
   $conn2 = db_connect();
   if (!$conn2) {
      writeDataToFile("Failed to connect to the database 2.", __FILE__, __LINE__);
      break;
   }
   

   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_ALL;
   writeDataToFile("league id is: " . $league_name, __FILE__, __LINE__);
   
   try {
      
      $sth = $conn->prepare($mysql);
      $sth2 = $conn2->prepare($mysql_error);
      
      $failed_addresses = '';
      foreach ($useremail as $email) {
         $temail = trim($email);
         if (!$temail) {
            continue;  // null email
         }
         $confirm_code=create_code();
         $mail_confirm_link = $link_confirm . $confirm_code;
         $sth->bind_param("ssi", $confirm_code, $temail, $league_id); 
         $sth->execute();
         
         $toaddress = $email; 
         $subject = "Registration for " . DOMAIN_NAME;
         // http://www.mysuperpicks.com/register.php?confirmcode="
         $mailcontent = "Hello,\n\n $first_name $last_name" .
            " has sent you an invitation to join $league_name" .
            " at www.MySuperPicks.com.\n \nTo complete your registration, please fill" .
            " out the form at:\n\n $mail_confirm_link \n\n" .            
            " This link will be active for approximately two weeks." .
            " If you have any questions, feel free to contact us via\n\n" .
            " $link_contact \n\n" .
            " Thank you.\n\nSincerely,\n\n" . DOMAIN_NAME;
         writeDataToFile($mailcontent, __FILE__, __LINE__);
         
         $fromaddress = "From: " . MAIL_FROM_NO_REPLY; 
         
         if ($dev_turn_off_mail_call) {
            formatSessionMessage("The 'mail' call has been disabled by developement.  No confirm codes will be recorded.  Ignore other messages.", 'warning', $msg, "ar2-151");
            setSessionMessage($msg, 'error');
         } else {
            if(!mail($toaddress, $subject, $mailcontent, $fromaddress)) {
               $mailing_failed = true;
               if (!$failed_addresses) {
                  $failed_addresses = $toaddress;
               } else {
                  $failed_addresses .= ", $toaddress";
               }
                  
               $sth2->bind_param("s", $confirm_code); 
               $sth2->execute();
            } else {
               $mailing_success = true;
            }
         }
      }
      $mail_status = true;
   } catch (mysqli_sql_exception $e) {
      $ermsg = "Attempt to mail failed.\n
         sql: $mysql \n
         mail: $register_email \n
         league_id $league_id \n
         MYSQL ERROR TO STRING: " . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      @ $sth->close();
      die;
   } 
   @$sth->close();
   
   $msg = '';
   if ($mail_status == true) {
      if ($mailing_success == true && $mailing_failed == false) {
         formatSessionMessage('Email was successfully sent without error.', 'success', $msg);
         setSessionMessage($msg, 'happy');
      } elseif ($mailing_success == true && $mailing_failed == true) {
         formatSessionMessage("Mailing was successful with exceptions.  These emails may have failed: <b>( $failed_addresses )</b>.  Please contact the site administrator.", 'warning', $msg, "ar2-188");
         setSessionMessage($msg, 'error');
      } elseif ($mailing_success == false) {
         formatSessionMessage('All emails failed.  Please contact the site administrator.', 'warning', $msg, "ar2-191");
         setSessionMessage($msg, 'error');
      }
   } else {
         formatSessionMessage('All emails failed.  Please contact the site administrator.', 'warning', $msg, "ar2-195");
         setSessionMessage($msg, 'error');
   }
   header( 'Location: adminregister.php') ;
   die();
}

   // $result = $conn->query("INSERT INTO temp_confirm VALUES ('', NOW(), '$confirm_code', '$email', '0', '".$_SESSION['league_admin']."')");
?>

