<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:
   file: picks_switch.php
   date: jun-2016
 author: originall
   desc: The league type director.  League types each have a page to present
      standings.  There's not a lot of difference but anything to eliminate
      logic.  There are 3 league type pages from which to select. (cohort and
      last man share same page now).  This is also a gatekeeper; have to have
      a league, the league has started, ...
marbles: 
   note:
 lineid: psw-
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('user');

$first_round =   (isset($_SESSION['league_firstround'])) ? $_SESSION['league_firstround'] : '' ;
$active_week =   (isset($_SESSION['active_week']      )) ? $_SESSION['active_week']       : '' ;
$league_active = (isset($_SESSION['leagueactive']     )) ? $_SESSION['leagueactive']      : '' ;
$league_name =   (isset($_SESSION['league_name']      )) ? $_SESSION['league_name']       : '' ;
$last_round =    (isset($_SESSION['league_lastround'] )) ? $_SESSION['league_lastround']  : '0' ;        // 0 == in play

writeDataToFile("picks_switch() $first_round, $active_week, $league_active, $league_name, $last_round", __FILE__, __LINE__);

$league_id = (!empty($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';

$status = 0;
while (1) {
   if ($league_id == '') {
      formatSessionMessage("You must be associated with a league to access This Week's Lines.", 'info', $msg,
         "psw-38 '$league_id'");
      setSessionMessage($msg, 'error');
      break;
   }
   if ($first_round === '' || $active_week === '' || $league_active === '' || $league_name === '') {
      formatSessionMessage("There is missing information.  The page cannot be displayed.", 'warning', $msg,
         "psw-44 first_round='$first_round' active_week='$active_week' league_active='$league_active' league_name='$league_name'");
      setSessionMessage($msg, 'error');
      break;
   }
   if ($last_round !== 0) {
      formatSessionMessage("No more picks. The game ended week $last_round.", 'info', $msg, "psw-50");
      setSessionMessage($msg, 'error');
      break;
   }
   if ($first_round === 0) {
      formatSessionMessage("There is an error.  League $league_name's first round is indicated as week 0. Please contact the league's Admin.", 'warning', $msg, "psw-55");
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

$league_type = getLeagueType($league_id);

writeDataToFile("f a n: $first_round, $active_week, $league_name, $league_type", __FILE__, __LINE__);
switch ($league_type) {
   case LEAGUE_TYPE_PICKUM:
      header('location: ' . PAGE_PICK_PICKUM);
      die();
   case LEAGUE_TYPE_COHORT:
      if ($first_round > $active_week) {
         formatSessionMessage("$league_name league's first round doesn't begin until season week $first_round.  The current week is $active_week.  Please return later.",
            'info', $msg, "psw-77");
         setSessionMessage($msg, 'error');
         header('Location: index.php');
         die();
      }
      header('location: ' . PAGE_PICK_KO_COHORT); 
      die();
   case LEAGUE_TYPE_LAST_MAN:
      if ($first_round > $active_week) {
         formatSessionMessage("$league_name league's first round doesn't begin until season week $first_round.  The current week is $active_week.  Please return later.",
            'info', $msg, "psw-88");
         setSessionMessage($msg, 'error');
         header('Location: index.php');
         die();
      }
      header('location: ' . PAGE_PICK_KO_MAN); 
      die();
   default:
      formatSessionMessage("A serious error has occurred.  Please contact the site administrator. (ref:unL$league_id)", 'danger', $msg, "psw-96");
      setSessionMessage($msg, 'error');
      header('Location: index.php');
      die();
}
?>
