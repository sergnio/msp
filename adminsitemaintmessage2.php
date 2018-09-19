<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


validateUser('admin');

@$homepage_message = $_POST['homepage_message'];


$mysql = "
   update nsp_admin
      set sitemaintenancemessage = ?
    where site = ?";
      

while (1) {
   
   $site_name = ADMIN_TABLE; 
   
   if (!$conn = db_connect()) {
      formatSessionMessage("db connect error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$sth = $conn->prepare($mysql)) {
      formatSessionMessage("prepare error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$sth->bind_param("ss", $homepage_message, $site_name)) {
      formatSessionMessage("prepare error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if(!$sth->execute()) {
      formatSessionMessage("execute error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   $update_count = $sth->affected_rows;
   $switch_status = 1;
   switch($update_count) {
   case -1 :
      formatSessionMessage("There was an error updating the admin record. '$update_count'", 'danger', $msg);
      $switch_status = 0;
      break;
   case 0 :
      formatSessionMessage("No updates were made.  Nothing was found to update. '$update_count'", 'info', $msg);
      break;
   case 1:
      formatSessionMessage("The update was successful.", 'success', $msg);
      break;
   default:
      formatSessionMessage("There was an error updating the admin record. '$update_count'", 'danger', $msg);
      $switch_status = 0;
      break;
   }
   setSessionMessage($msg, 'error');
   
   $status = ($switch_status == 1) ? 1 : 0;
   break;
}

header( 'Location: adminsitemaintmessage.php' ) ;
die();
?>