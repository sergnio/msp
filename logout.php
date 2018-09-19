<?php
require_once 'mypicks_startsession.php';  // :-)

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

// store  to test if they *were* logged in
unset($_SESSION['valid_user']);
$result_dest = session_destroy();
session_start();
formatSessionMessage("You are successfully logged out.  Come back soon.", 'success', $msg);
setSessionMessage($msg, 'happy');
header( 'Location: index.php' ) ;
die();
?>