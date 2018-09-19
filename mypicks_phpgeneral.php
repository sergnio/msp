<?php


/*
:mode=php:

   file: mypicks_phpgeneral.php
   date: jun-2016
 author: hfs
   desc: Another php general support file.

   note:
   
function areDisallowCharacters(
function areDisallowCharactersSpace(
function buildLeaguesArray(
function clearSessionMessageCategory(
function echoSessionMessage(
function formatSessionMessage (
function formatSessionMessage (
function getSessionInfo(
function isValidUserMode(
function setSessionInfo(
function setSessionMessage(
function testSessionMessage() {

*/

define('MESSAGE_REFERENCES', 'on');

$msg = '';

function testSessionMessage() {
   
   if(isset($_SESSION['messages']['error'])) {
      return 1;
   }
   if(isset($_SESSION['messages']['login'])) {
      return 1;
   }
   if(isset($_SESSION['messages']['happy'])) {
      return 1;
   }
   return 0;
}

function buildLeaguesArray(
   $league_id
) {
   $league = explode('-', $league_id);
   $league = str_replace('-', "", $league);
   $league = array_filter( $league, 'strlen' );  // len != 0
   sort($league);
   return $league;
}

function isValidUserMode(
   $user_mode
){
   switch ($user_mode) {
   case "admin" : case "user" : return 1; break;  // TODO These should be defines
   default : return 0; break;
   }
}


function areDisallowCharacters(
   $string,
   $alpha_leading = false
){
   $pattern = '/[^0123456789abcdefghijklmnopqrstuvwxyz\-_\']/i';
   $pattern2 = '/^[^abcdefghijklmnopqrstuvwxyz]/i';
   
   if ( preg_match($pattern, $string) ) {
      return 1;
   }
   if($alpha_leading) {
      if ( preg_match($pattern2, $string) ) {
         return 1;
      }
   }
   return 0;
}
function areDisallowCharactersSpace(  
   $string,
   $alpha_leading = false
){
   
   $pattern = '/[^.0123456789abcdefghijklmnopqrstuvwxyz\-_ \']/i';
   $pattern2 = '/^[^abcdefghijklmnopqrstuvwxyz]/i';
   if ( preg_match($pattern, $string) ) {
      return 1;
   }
   if($alpha_leading) {
      if ( preg_match($pattern2, $string) ) {
         return 1;
      }
   }
   return 0;
}


function getSessionInfo(
   $key
){
   if (isset($_SESSION['messages']['info'][$key])){
      $value = $_SESSION['messages']['info'][$key];
      unset($_SESSION['messages']['info'][$key]);
      return $value;
   }
   return '';
}
function clearSessionMessageCategory(
   $key = 'login'
){
   $status = 0;
   while(1) {
      if (!($key == 'login' || $key == 'error' || $key == 'happy' || $key == 'all')) {
         break;
      }
      switch ($key) {
      case 'login' :
      case 'error' :
      case 'happy' :
         if (empty($_SESSION['messages'])) {
            $a_msg = array ('login' => array(),
                      'error' => array(),
                      'happy' => array(),
                      'info' => array());
            $_SESSION['messages'] = $a_msg;
         } elseif (!empty($_SESSION['messages'][$key])) {
            unset ($_SESSION['messages'][$key]);
            $_SESSION['messages'][$key] = array();
         }
         $status = 1;
         break;
      case 'all' :
         if (!empty($_SESSION['messages'])) {
            unset ($_SESSION['messages']);
         }
         $a_msg = array('login' => array(),
                   'error' => array(),
                   'happy' => array(),
                   'info' => array());
         $_SESSION['messages'] = $a_msg;
         $status = 1;
         break;
      default :
            break;
      }
      break;
   }
   return $status;
}

function setSessionInfo(
   $key,
   $msg
){
writeDataToFile("setSessionInfo '$key', '$msg'", __FILE__, __LINE__);
   return setSessionMessage($msg, 'info', $key);
}

function setSessionMessage(
   $msg,
   $type = 'error',  // error, login, happy, info.  info requires a hash key
   $key = ''
){

   if ($type == 'info' && !$key) {
      return 0;
   }
   
   // enforcement
   if (!($type == 'error' || $type == 'login' || $type == 'happy' || $type == 'info')) {
      return 0;
   }

   while(1) {
      if(!isset($_SESSION['messages'])) {
         $a_msg = array (
            'login' => array(),
            'error' => array(),
            'happy' => array(),
            'info' => array()
            );
         $_SESSION['messages'] = $a_msg;
         break;
      }
      if(!isset($_SESSION['messages']['error'])) {
         $_SESSION['messages']['error'] = array();
      }
      if(!isset($_SESSION['messages']['login'])) {
         $_SESSION['messages']['login'] = array();
      }
      if(!isset($_SESSION['messages']['happy'])) {
         $_SESSION['messages']['happy'] = array();
      }
      if(!isset($_SESSION['messages']['info'])) {
         $_SESSION['messages']['info'] = array();
      }
      break;
   } 
   
   if ($type == 'info' && $key) {
      $_SESSION['messages'][$type][$key] = $msg;
   } elseif ($type == 'info'){
      $_SESSION['messages'][$type]['NOKEY'] = $msg;
   } else {
      $_SESSION['messages'][$type][] = "$msg";
   }
   writeDataToFile("all messages " . print_r($_SESSION['messages'], true), __FILE__, __LINE__);
   return 1;
}

// login, error, happy  If any hit all are then erased.  Info is keyed and remains.
function echoSessionMessage(
   $dump = false  // dump true echos every message pending.  The hierarchy is abandoned. 
){

   if (!isset($_SESSION['messages'])) {
      return;
   }

   $login_count = (isset($_SESSION['messages']['login'])) ? count($_SESSION['messages']['login']) : 0;
   $error_count = (isset($_SESSION['messages']['error'])) ? count($_SESSION['messages']['error']) : 0;
   $happy_count = (isset($_SESSION['messages']['happy'])) ? count($_SESSION['messages']['happy']) : 0;
   
   $already_said = false;
   if ($login_count  && !$already_said) {
      foreach ($_SESSION['messages']['login'] as $m) {
         echo $m;
      }
      if  (!$dump) { $already_said = true;}
   }
   if ($error_count  && !$already_said) {
      foreach ($_SESSION['messages']['error'] as $m) {
         echo $m;
      }
      if  (!$dump) { $already_said = true;}
   }
   if ($happy_count  && !$already_said) {
      foreach ($_SESSION['messages']['happy'] as $m) {
         echo $m;
      }
      if  (!$dump) { $already_said = true;}
   }
   if ($dump) {
      $info_count = (isset($_SESSION['messages']['info'])) ? count($_SESSION['messages']['info']) : 0;
      if ($info_count) {
         foreach ($_SESSION['messages']['info'] as $m) {
            echo $m;
         }
      }
   }
   
   unset($_SESSION['messages']['login'], $_SESSION['messages']['error'],$_SESSION['messages']['happy']);
}


function formatSessionMessage (
   $msg,
   $alert_class,     //success, info, infonoheader warning, danger - any other key is default formatting
   &$formatted_message = '',
   $reference = 'no reference available'
) {

   if (!($alert_class == 'success' || $alert_class == 'info' || $alert_class == 'infonoheader'  || $alert_class == 'warning' || $alert_class == 'danger')) {
      $alert_class = 'unknownclass';
   }
   
   $ref_text = '';
   $ref_mode = (isset($_SESSION['reference'])) ? $_SESSION['reference'] : '';
   
   if ($ref_mode == 2 && !empty($reference)) {
      $ref_text = "(ref:$reference)";
   }
   
   switch ($alert_class) {
   case  "success" : 
      $formatted_message = "<div class=\"alert alert-success\"><b>Success!</b>&nbsp;&nbsp; $msg $ref_text</div>";
      return 1;
   case  "info" : 
      $formatted_message = "<div class=\"alert alert-info\"><b>Info!</b>&nbsp;&nbsp; $msg $ref_text</div>";
      return 1;
   case  "infonoheader" : 
      $formatted_message = "<div class=\"alert alert-info\"> $msg</div>";
      return 1;
   case  "warning" :
      $formatted_message = "<div class=\"alert alert-warning\"><b>Warning!</b>&nbsp;&nbsp; $msg $ref_text</div>";
      return 1;
   case  "danger" : 
      $formatted_message = "<div class=\"alert alert-danger\"><b>Serious error!</b>&nbsp;&nbsp; $msg $ref_text</div>";
      return 1;
   case "unknownclass": 
      $formatted_message = "<div class=\"alert alert-danger\"><b>Serious error!</b>&nbsp;&nbsp; $msg $ref_text</div>";
      return 1;
   default :
      $formatted_message = "<div class=\"alert alert-danger\">&nbsp;&nbsp; A serious error. Please contact the site administrator (ref:nhere)</div>";
      return 1;
   }
   return 0;
}

function updateSession (
   $key,
   $value
){

   $status = false;
   switch($key) {
   case 'currentleaguelastweek' :
      $_SESSION['league_lastround'] = $value;
      $status = true;
      break;
   case 'currentleaguestatus' :
      $_SESSION['league_lastround'] = $value;
      $status = true;
      break;
   default:
      break;
   }
   return $status;
}
   
function getLeagueTypeName(
   $league_type
) {
   if (!$league_type) {
      return false;
   }
   switch ($league_type) {
   case LEAGUE_TYPE_PICKUM:
      return 'Pickem';
   case LEAGUE_TYPE_COHORT:
      return 'Survivor - cohort';
   case LEAGUE_TYPE_LAST_MAN:
      return 'Survivor - last man';
   default : 
      return false;
   }
}
   
function getLeaguePushValue(
   $league_push
) {
   if (!$league_push) {
      return false;
   }
   switch ($league_push) {
   case LEAGUE_PUSH_ZERO:
      return 0;
   case LEAGUE_PUSH_HALF:
      return '1/2';
   case LEAGUE_PUSH_ONE:
      return 1;
   default : 
      return false;
   }
}

?>