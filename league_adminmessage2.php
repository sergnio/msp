<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


validateUser('commissioner');

@$homepage_message = $_POST['homepage_message'];
@$league = $_SESSION['league_id'];
if (!insertOrUpdateLeagueGreetingText($league, $homepage_message, $ref_status_text)) {
   if ($ref_status_text == 'insert') {
      formatSessionMessage("The system failed to insert the new league home page splash.  Please contact the system administrator.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      writeDataToFile("league_adminmessage2.php message: $league, $homepage_message,", __FILE__, __LINE__);
   }
}
if ( $ref_status_text == 'norows') {
      formatSessionMessage("No update was made. There was nothing to update.", 'info', $msg);
      setSessionMessage($msg, 'error');
} else {
      formatSessionMessage("Update was successful.", 'success', $msg);
      setSessionMessage($msg, 'happy');
}
header( 'Location: league_adminmessage.php' ) ;
die();
?>