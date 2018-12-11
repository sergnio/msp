<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:
   file: standings_switch.php
   date: jun-2016
 author: originall
   desc: The league type director.  League types each have a page to present
      standings.  There's not a lot of difference but anything to eliminate
      logic.  There are 3 league type pages from which to select.
marbles: 
   note:
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';
writeDataToFile("standings_switch() begin 1", __FILE__, __LINE__);

validateUser('user');
writeDataToFile("standings_switch() begin 2", __FILE__, __LINE__);

$league_id = (!empty($_SESSION['league_id'])) ? $_SESSION['league_id'] : ''; 

if (!$league_id) {
   formatSessionMessage("You must be associated with a league to access Standings.", 'info', $msg, "ssw-27 '$league_id'");
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die;
}

$league_type = getLeagueType($league_id);
$first_round = (isset($_SESSION['league_firstround'])) ?     $_SESSION['league_firstround'] : '' ;
$active_week = (isset($_SESSION['active_week']      )) ?     $_SESSION['active_week']       : '' ;
$league_name = (isset($_SESSION['league_name']      )) ?     $_SESSION['league_name']       : '' ;


$status = 0;
while (1) {
   if (!$league_type || $first_round === '' || !$active_week || !$league_name) {
      writeDataToFile("standing_switch() !$league_type || $first_round == '' || !$active_week || !$league_name", __FILE__, __LINE__);
      formatSessionMessage("There is missing information.  The page cannot be displayed.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   } 
   if ($first_round === 0) {
      formatSessionMessage("There is an error.  League $league_name's first round is indicated as week 0. Please contact the league's Admin.",
         'warning', $msg, 'ssw-49');
      setSessionMessage($msg, 'error');
      header('Location: index.php');
      die();
   }
   $status = 1;
   break;
}
if (!$status) {
   header('Location: index.php');
   die;
}


switch ($league_type) {
case LEAGUE_TYPE_PICKUM:
   header('location: ' . PAGE_PICKUM);
   die();
case LEAGUE_TYPE_COHORT:
   if ($first_round > $active_week) {
      formatSessionMessage("$league_name league's first round doesn't begin until season week $first_round.  The current week is $active_week.  Please return later.",
         'info', $msg, "ssw-69");
      setSessionMessage($msg, 'error');
      header('Location: index.php');
      die();
   }
   header('location: ' . PAGE_KO_COHORT);
   die();
case LEAGUE_TYPE_LAST_MAN:
   if ($first_round > $active_week) {
      formatSessionMessage("$league_name league's first round doesn't begin until season week $first_round.  The current week is $active_week.  Please return later.",
         'info', $msg, "ssw-79");
      setSessionMessage($msg, 'error');
      header('Location: index.php');
      die();
   }
   header('location: ' . PAGE_KO_MAN);
   die();
default:
   formatSessionMessage("A serious error has occurred.  Please contact the site administrator.", 'danger', $msg, "ssw-87 $league_type" );
   setSessionMessage($msg, 'error');
   header('location: index.php');
   die();
}

?>
