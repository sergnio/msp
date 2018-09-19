<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser();

$message_id = (!empty($_POST['message_id']))    ? $_POST['message_id']     :   '';
$comment =    (!empty($_POST['comment']))       ? $_POST['comment']        :   '';
$user_name =  (!empty($_SESSION['valid_user'])) ? $_SESSION['valid_user']  :   '';  // player name instead?
	

$mysql = "
   insert into comments
      (comment_date, comment_user, comment_content, message_id)
   values 
      (       now(),            ?,               ?,          ?)";
      
$status = 0;
while(1) {
   
   if (!$comment) {
      formatSessionMessage("No comment was found to save.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!$user_name  || !$message_id) {
      formatSessionMessage("We are unable to save this message.  Information is missing.  Please contact the administrator.", 'danger', $msg, 'unmi');
      setSessionMessage($msg, 'error');
      break;
   }
      
   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   $sth->bind_param("ssi", $user_name, $comment, $message_id);
   if (!$sth->execute()) {
      formatSessionMessage("There was a serious system error. We are unable to save this comment. Please contact the administrator.", 'danger', $msg, 'ex');
      setSessionMessage($msg, 'error');
      @ $sth->close();
      break;
   }
   $comment_id = $sth->insert_id;  // this was collected and returned in query string for #2 'comment added' - but I don't see where it is used.

   @ $sth->close();
   $status = 1;
   break;
   
}
if ($status) {
   formatSessionMessage("The comment was successfully added.", 'success', $msg, 'ex');
   setSessionMessage($msg, 'happy');
}

header('Location: messageboard.php') ;
die();
?>