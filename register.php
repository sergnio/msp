<?php

session_start();
$result_dest = session_destroy();

require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
require_once 'mypicks_phpgeneral.php';
$msg = '';

$confirm_code = (!empty($_GET['confirmcode'])) ? $_GET['confirmcode'] : '';

// This is a hot link page.  There shouldn't be any session vars.


// The ONLY way you arrive here is via the invite.  Kill the hot links with logged in users

$count = 0;   // Used to vector error page
$confirm_date    = '';
$confirm_code2   = '';
$confirm_email   = '';
$league_id       = '';
while (1) {
   
   if (!$confirm_code) {
      formatSessionMessage("Registration by mail requires a valid registration code.", 'warning', $msg, "r0-20");
      setSessionMessage($msg, 'error');
      break;
   }
   
   $conn = db_connect();
   if (!$conn) {
      break;  // error log has alread been written by db_connect
   }
   
   $mysql = "
      SELECT confirm_date,
             confirm_code,
             confirm_email,
             league_id,
             if (confirm_date <= DATE_ADD(curdate(), INTERVAL 2 WEEK), 'fresh', 'stale'),
             retire,
             used,
             COUNT(*) as count 
        FROM temp_confirm 
       WHERE confirm_code= ?
         AND used = 0
         AND retire <> 1";
         
   $sth = $conn->prepare($mysql);
   if (!$sth) {
     @ $sth->close();
     $ermsg = 
        'Failed prepare() (db was connected)  \nSQL = ' . $mysql;
     writeDataToFile($ermsg, __FILE__, __LINE__);
     break;
   }
   if(!$sth->bind_param("s", $confirm_code)) {
      @ $sth->close();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      break;;
   }
   
   $sth->execute();
   if (!$sth) {
      $ermsg =
         'Failed execute()! (db was connected)  \nSQL = ' . $mysql . 
         "\nErrorno:" . $sth->errno . ', Errormsg:' . $sth->error;
      @$sth->close();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      break;
   }
   
   $weekly_table_data = array();
   $sth->bind_result($confirm_date,
          $confirm_code2,
          $confirm_email,
          $league_id,
          $fresh,
          $retired,
          $used,
          $count);
   
   if (!$sth->fetch()) {
      $count = 0;  // Not sure I have to do this on a failed fetch but may be NULL. 
      writeDataToFile("YYYYY register.php  fetch failed", __FILE__, __LINE__);
      @$sth->close();
      break;
   }
   break;  // success
}
writeDataToFile("confirm count = $count", __FILE__, __LINE__);
if ($count == 1) {
   
   if ($fresh == 'stale') {
      formatSessionMessage("Your invitation has expired. Please contact the Admin of the league and have him resend the mail. Thank you.", 'warning', $msg, "r0-90");
      setSessionMessage($msg, 'error');
      retireInvitation($confirm_code);
      header('Location: index.php');
      die();
   }
   
   $_SESSION['registerconfirmcode'] = $confirm_code;
   $_SESSION['registerconfirmleagueid'] = $league_id;
   $_SESSION['registerconfirmemail']= $confirm_email;
   $_SESSION['registerconfirmdate']= $confirm_date;
   
   if(!isset($_SESSION['registertries'])) { 
      $_SESSION['registertries'] = 3; 
   }
   
   writeDataToFile("YYYYY register.php  going to register2.php now", __FILE__, __LINE__);
   header( 'Location: register2.php') ;
   die();  
} else {
   formatSessionMessage("Your confirmation code is no longer valid. Please contact us if this is in error. Thank you.", 'warning', $msg, "r0-110");
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}
?>