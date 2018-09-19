<?php

require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser();
$id = getSessionInfo('commentid');

$mysql = "
   delete
     from comments
     where id = ?";

$status = 0;
while (1) {
   
   if (empty($id)) {
      formatSessionMessage("The comment id is missing.", 'warning', $msg, 'ncid');
      setSessionMessage($msg, 'error');
      break;
   }
   
   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   $sth->bind_param("i", $id);
   if (!$sth->execute()) {
      formatSessionMessage("There was a serious system error. We are unable to delete this comment. Please contact the administrator.", 'danger', $msg, 'ex');
      setSessionMessage($msg, 'error');
      @ $sth->close();
      break;
   }

   @ $sth->close();
   $status = 1;
   break;
}
if ($status) {
   formatSessionMessage("The comment was successfully deleted.", 'success', $msg, 'ex');
   setSessionMessage($msg, 'happy');
}

header('Location: messageboard.php') ;
die();
?>