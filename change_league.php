<?php
require_once 'mypicks_startsession.php'; 

/*
:mode=php:

   file: change_league.php
   date: apr-2016
 author: original
   desc: The active league environment (SESSION) is changed here.  The page
      is redirected to HTTP_REFERER when complete.  Use SESSION instead of
      GET or POST to render pages.
marbles: 

*/

require_once 'mypicks_def.php'; 
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('user');
writeDataToFile("change league " . print_r($_SERVER, true), __FILE__, __LINE__);

$change_to_league_id = (!empty($_GET['leagueid'])) ? $_GET['leagueid'] : '0';
$user_id = $_SESSION['user_id'];

$ref_status_text = '';
while (1) {
   // There is already a SESSION list, but who knows what is coming in here.
   if (!isLeagueMember($user_id, $change_to_league_id, $ref_status_text)) {
      formatSessionMessage("League change denied.  You are not a member.", 'info', $msg, "cl-33 '$user_id' '$change_to_league_id'");
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!setSessionActiveLeague($_SESSION['user_id'], $change_to_league_id)) {
      formatSessionMessage("There was a system error.  Please logout and back in.", 'danger', $msg, "cl-39 '$user_id' '$change_to_league_id'");
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!setUserLastLeagueSelected($user_id, $change_to_league_id, $ref_status_text)) { 
      formatSessionMessage("We are unable to record your last selected league.  Your next login may not default to this selection.", 'info',
         $msg, "cl-48 '$user_id' '$change_to_league_id' '$ref_status_text'");
      setSessionMessage($msg, 'error');
      break;
   }
   
   break;
}

// Redirect to current page.
$host =  (!empty($_SERVER['HTTP_HOST'])) ?    $_SERVER['HTTP_HOST'] : '';   
$url =   (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
if (!$url) {
   header( 'Location: index.php' );  // The server globals differ.  Is there a standard way to do this?    
   die();
}

$path = parse_url($url, PHP_URL_PATH);
header('Location: http://' . $host . $path);
die;

?>