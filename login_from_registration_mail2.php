<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'mypicks_db.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


$confirm_code =         (!empty($_SESSION['registerconfirmcode'])) ?         $_SESSION['registerconfirmcode'] : '';
$confirm_league_id =    (!empty($_SESSION['registerconfirmleagueid'])) ?     $_SESSION['registerconfirmleagueid'] : '';
$confirm_email =        (!empty($_SESSION['registerconfirmemail'])) ?        $_SESSION['registerconfirmemail'] : '';
$confirm_email_date =   (!empty($_SESSION['registerconfirmdate'])) ?         $_SESSION['registerconfirmdate'] : '';
$confirm_user_id =      (!empty($_SESSION['registerconfirmuserid'])) ?       $_SESSION['registerconfirmuserid'] : '';
$confirm_league_name =  (!empty($_SESSION['registerleaguename'])) ?          $_SESSION['registerleaguename'] : '';
$confirm_password =     (!empty($_POST['registerconfirmpassword'])) ?   trim($_POST['registerconfirmpassword']) : '';
$confirm_player_name =  (!empty($_POST['registerconfirmplayername'])) ? trim($_POST['registerconfirmplayername']) : '';
$_SESSION['registerconfirmplayername'] = $confirm_player_name;  // this is the only new entry on the login_from_registration_mail page


if (!$confirm_code || !$confirm_league_id || !$confirm_email || !$confirm_email_date || !$confirm_user_id) {
   formatSessionMessage("League registration has failed.  The email address indicated you have a current account but information is missing. Please contact the site administrator.", 'warning', $msg);
   setSessionMessage($msg, 'error');
   unsetRegisterConfirmSessionVars();
   header('Location index.php');
   die();
}

$status = 0;
$sub_status = 1;
while(1) {
   
   if(empty($confirm_password) || empty($confirm_player_name)) {
      formatSessionMessage('Please complete the form.', 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if(areDisallowCharacters($confirm_player_name)) {
      $sub_status = 0;
      formatSessionMessage("The player name can contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.", 'info', $msg, "lfrm2-41");
      setSessionMessage($msg, 'error');
   }
   if(strlen($confirm_player_name) < 2) {
      $sub_status = 0;
      formatSessionMessage("The player name must contain at least 2 characters.", 'info', $msg);
      setSessionMessage($msg, 'error');
   }
   if(strlen($confirm_player_name) > PLAYER_NAME_MAX_LENGTH) {
      $sub_status = 0;
      formatSessionMessage("The player length is restricted to " . PLAYER_NAME_MAX_LENGTH . " characters.  Please shorten your name.", 'info', $msg, "lfrm2-51");
      setSessionMessage($msg, 'error');
   }
   if (!isPlayerNameAvailable($confirm_user_id, $confirm_league_id, $confirm_player_name, $ref_status_text)) {
      $sub_status = 0;
      formatSessionMessage("The player name is already in use in this league.  Please choose another.", 'info', $msg, "lfrm2-56");
      setSessionMessage($msg, 'error');
   }
   writeDataToFile("re2 checkUserPassword($confirm_email, $confirm_password)", __FILE__, __LINE__);
   if (!checkUserPassword($confirm_email, $confirm_password)) {
      formatSessionMessage('Your password is_a incorrect.  Please try again.', 'warning', $msg, "lfrm2-61");
      setSessionMessage($msg, 'login');
      $sub_status = 0;
   }
   $status = 1;
   break;
}

if (!$status || !$sub_status) {
   header('Location: login_from_registration_mail.php');
   die();
}

// die die die
writeDataToFile("login with login($confirm_user_id, $confirm_password)", __FILE__, __LINE__);
if (!login($confirm_email, $confirm_password)) {
   deactivateInvitation($confirm_code);
   formatSessionMessage('The login failed. The confirmation code has be closed.  Please contact the league Admin.', 'danger', $msg, "lfrm2-78 $ref_status_text");
   setSessionMessage($msg, 'login');
   header('Location: index.php');
   die();
}

deactivateInvitation($confirm_code);

if (isCommissionerWithScope($_SESSION['user_id'], $confirm_league_id, $ref_status_text)) {
   formatSessionMessage('You are the Admin of this league.  You are already a member.', 'info', $msg, "lfrm2-86 $ref_status_text");
   setSessionMessage($msg, 'login');
   header('Location: index.php');
   die();
}
if (!addLeagueMembershipToUser($confirm_user_id, $confirm_league_id, $ref_status_text)) {
   $strpos = strpos($ref_status_text, 'alreadyleaguemember');
   if ($strpos !== false) {
      formatSessionMessage("You are already a member of league $confirm_league_name.", 'info', $msg,
         "lfrm2-96 '$confirm_user_id', '$confirm_league_id', '$ref_status_text', $ref_status_text");
      setSessionMessage($msg, 'login');
      header('Location: index.php');
      die();
   }
}
writeDataToFile("insertNewPlayer2($confirm_league_id, $confirm_user_id, $confirm_player_name, $ref_status_text)", __FILE__, __LINE__);
if (!insertNewPlayer($confirm_league_id, $confirm_user_id, $confirm_player_name, $ref_status_text)) {
   $strpos = strpos($ref_status_text, 'alreadyleagueplayer');
   if ($strpos !== false) {
      formatSessionMessage("You are already a player in league $confirm_league_name.", 'info', $msg,
         "lfrm2-107 $ref_status_text");
      setSessionMessage($msg, 'login');
   } else {
      formatSessionMessage("We were unable to add your player name to league $confirm_league_name.  Please contact the site administrator.", 'danger', $msg,
         "lfrm2-111 $ref_status_text");
      setSessionMessage($msg, 'login');
   }
   header('Location: index.php');
   die();
}
// He was logged in before membership.  Fix it.
writeDataToFile("setSessionActiveLeague($confirm_user_id, $confirm_league_id, $ref_status_text)", __FILE__, __LINE__);

if (!setSessionActiveLeague($confirm_user_id, $confirm_league_id, $ref_status_text)) {
   formatSessionMessage("We were unable to configure your login correctly.  Please logout and back in. Please contact the site administrator.", 
      'warning', $msg, "lfrm2-111 '$confirm_user_id' '$confirm_league_id', $ref_status_text");
   setSessionMessage($msg, 'login');
   header('Location: index.php');
   die();
}

// Addendum to successful login.  There's a login already queued
formatSessionMessage("You are now a member of league <i>" . $confirm_league_name .   "</i>.  Your player name is " . $confirm_player_name . ".", 'info', $msg);
setSessionMessage($msg, 'error');
header('Location: index.php');
die();


function unsetRegisterConfirmSessionVars(
){
   unset($_SESSION['registerconfirmcode']);
   unset($_SESSION['registerconfirmleagueid']);
   unset($_SESSION['registerconfirmemail']);
   unset($_SESSION['registerconfirmdate']);
   unset($_SESSION['registerconfirmuserid']);
   unset($_SESSION['registerconfirmpassword']);
   unset($_SESSION['registerconfirmplayername']);
}

?>