<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('admin');

$week = $_POST['week'];

$ref_status_text = '';
if (!setActiveWeek($week, $ref_status_text)) { 
   formatSessionMessage("Failed to set the active week.", 'danger', $msg, "sw2-15 $ref_status_text");
   setSessionMessage($msg, 'error');
} else {
   formatSessionMessage("Active week has been changed to $week.", 'success', $msg);
   setSessionMessage($msg, 'happy');
}
   
header( 'Location: setweek.php' ) ;
die();

?>