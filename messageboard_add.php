<?php

require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
require_once 'site_fns_diminished.php';
$msg = '';

validateUser();

$message = (!empty($_POST['message'])) ? $_POST['message'] : '';
$_SESSION['leaguemessage'] = $message;
$user_name = $_SESSION['valid_user'];
$league_id = $_SESSION['league_id'];

$mysql = "
   insert into messages
      (message_date, message_user, message_content, league_id)
   values
      (       now(),            ?,               ?,         ?)";

$status = 0;
while(1) {
   
   if (!$message) {
      formatSessionMessage("No message was found to save.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!$user_name  || !$league_id) {
      formatSessionMessage("We are unable to save this message.  Information is missing.  Please contact the administrator.", 'danger', $msg, 'unli');
      setSessionMessage($msg, 'error');
      break;
   }
      
   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   $sth->bind_param("ssi", $user_name, $message, $league_id);
   if (!$sth->execute()) {
      formatSessionMessage("There was a serious system error. We are unable to save this message. Please contact the administrator.", 'danger', $msg, 'ex');
      setSessionMessage($msg, 'error');
      @ $sth->close();
      break;
   }

   @ $sth->close();
   $status = 1;
   break;
   
}

if ($status) {
   formatSessionMessage("The message was successfully added.", 'success', $msg, 'ex');
   setSessionMessage($msg, 'happy');
}

header( 'Location: messageboard.php') ;
die();

?>