<?php
/*
:mode=php:

   file: mypicks_def.php
   date: apr-2016
 author: originall
   desc: A support and definitions file.  Supports environment selection, 
    behavior and php utilities.  This should be required before all other
    files, but after the php start function.  Some functionality of site_fns.php
    was moved here.
         Global vars all begin with global_.
         Since this file is required before the headers are written, it must be
    QUIET!  Errors should be suppressed or logged. There are globals 
   
marbles: 
*/

// DONT set any session variable here.  The session may be flushed at 
// any time.

error_reporting(E_ALL);
ini_set("error_reporting", E_ALL | E_STRICT | E_NOTICE);  // php5.4 E_ALL includes E_STRICT
//ini_set('display_errors', '1'); //for verbose debug to screen
$chatty = false;
//$global_chatty = true;

// Analytics are disabled regardless of the argument used in do_header()
// if the following def is set to other than ENABLE.  It's a master override
// to shutdown all analytics.
define('BEHAVIOR_ANALYTICS', 'DISABLE');

define('DEFAULT_TIME_ZONE', 'America/Los_Angeles');
define('DEBUG_FILESPEC', './ROACH.TXT');

define('RUNTIME_ENVIRONMENT_DRILLBRAIN', 'drillbrain');
define('RUNTIME_ENVIRONMENT_MYSUPERPICKS', 'mysuperpicks');
define('RUNTIME_ENVIRONMENT_DEV_PC', 'devpc');

// All relative directories are relative from the home dir.  All end with
// file system delimiter '/'
define('REL_DIR_IMAGES', './images/');
define('IMAGE_MAIN_BANNER', 'MySuperPicks0103.jpg'); // April 2016 - Leisen
define('IMAGE_OLD_MAIN_BANNER', 'players.jpg'); // Line of nondescript players


// Some constructions are based on the number of weeks in the NFL season.  The 
// first week is always one, the last week is:
define('NFL_LAST_WEEK', 17);

// The bootstrap nav-bar overwrites content as the browser resizes.  A number
// of <br /> tags under this element has been the fix.  This just defines it.  
// For tuning.  echo_container_breaks() will override with a numeric arg.
define ('BOOTSTRAP_NAVBAR_BREAKS_DEFAULT', '6');

// TODO Do css 
define ('STANDINGS_PLAYER_HIGHLIGHT', 'MistyRose');

// Leagues and type
define ('PAGE_LEAGUE_SWITCH',  'standings_switch.php');
define ('PAGE_PICKUM',         'standings_pickum.php');
define ('PAGE_KO_MAN',         'standings_ko_last_man.php');
define ('PAGE_KO_COHORT',      'standings_ko_cohort.php');

define ('PAGE_PICK_SWITCH',    'picks_switch.php');
define ('PAGE_PICK_PICKUM',    'picks_ajax.php');
define ('PAGE_PICK_KO_MAN',    'picks_ko_ajax.php');
define ('PAGE_PICK_KO_COHORT', 'picks_ko_ajax.php');

define ('LEAGUE_TYPE_PICKUM',    1);
define ('LEAGUE_TYPE_COHORT',    2);
define ('LEAGUE_TYPE_LAST_MAN',  3);

define ('SCHEDULE_TZ', 'e'); // e, c, m, p; edt, cdt, mdt, pdt

define ('LEAGUE_PUSH_ZERO', 1);
define ('LEAGUE_PUSH_HALF', 2);
define ('LEAGUE_PUSH_ONE',  3);

define ('PICK_ALL_GAMES', 11);  // The 'All Teams' number when creating a league


define ('LEAGUE_ODDS_IN_NOT_USE', 1);
define ('LEAGUE_ODDS_IN_USE', 2);

// Database constraints
define ('USER_NAME_MAX_LENGTH', 16);
define ('PLAYER_NAME_MAX_LENGTH', 16);

// Addresses
define ('MAIL_FROM_NO_REPLY', 'noreply@mysuperpicks.com');

// Globals
$global_runtime_environment_description = 'NOT_DEFINED';  // Set to current env
$global_chatty_text = '';  // Information recorded here if chatty is on
$global_mysuperpicks_dbo = '';
$global_write_roach_file = false;


@ $php_self_string = strtolower ($_SERVER['PHP_SELF']);
@ $php_server_string = $_SERVER['SERVER_NAME'];

// Dev notes.  (PHP site)
// As strpos may return either FALSE (substring absent) or 0 (substring at 
// start of string), strict versus loose equivalency operators must be used very
// carefully.
// 
// To know that a substring is absent, you must use: === FALSE
// 
// To know that a substring is present (in any position including 0), you can 
// use either of: !== FALSE  (recommended)

// ID the environment and set definitions appropriately.
// All this, except 'ADMIN_TABLE' will be replaced by table nsp_admin.
// $result = new mysqli('localhost', 'mysuperpicks', 'english71', 'mysuperpicks'); 
if (strpos($php_server_string,  'mysuperpicks.com') !== false) { // mysuperpicks.com
   define('DOMAIN_NAME',      'mysuperpicks.com');
   define('ADMIN_TABLE',      'mysuperpicks');
   define('URL_HOME_PAGE',    'index.php');
   define('HOST',             'localhost');
   define('DATABASE_NAME',    'mysuperpicks');
   define('USER_NAME',        'mysuperpicks'); 
   define('USER_PASSWORD',    'english71');
   define('MY_SERVER_NAME',   'mysuperpicks.com');
   define('MY_SHORT_PATH',    '/');
   define('MAIL_TO_CONTACT',  'mattleisen@yahoo.com');
   define('MAIL_FROM_CONTACT','info@mysuperpicks.com');
   define('LINK_CONFIRM',     'http://www.mysuperpicks.com/register.php');
   define('LINK_CONTACT',     'http://www.mysuperpicks.com/contact.php');
   date_default_timezone_set(DEFAULT_TIME_ZONE);
   $global_runtime_environment_description = RUNTIME_ENVIRONMENT_MYSUPERPICKS;
} elseif (strpos($php_self_string, '/nflbrain/') !== false) { // drillbrain.com
   define('DOMAIN_NAME',      'drillbrain.com');
   define('ADMIN_TABLE',      'nflbrain');
   define('URL_HOME_PAGE',    'http://drillbrain.com/nflbrain/index.php');
   define('HOST',             'mysuperpicksx.db.8772532.hostedresource.com');
   define('DATABASE_NAME',    'mysuperpicksx');
   define('USER_NAME',        'mysuperpicksx'); 
   define('USER_PASSWORD',    'nflTest1234!x');
   define('MY_SERVER_NAME',   'drillbrain.com');
   define('MY_SHORT_PATH',    '/nflbrain/');
   define('MAIL_TO_CONTACT',  'shedd2013@yahoo.com');
   define('MAIL_FROM_CONTACT','info@drillbrain.com');
   define('LINK_CONFIRM',     'http://www.drillbrain.com/nflbrain/register.php');
   define('LINK_CONTACT',     'http://www.drillbrain.com/nflbrain/contact.php');
   date_default_timezone_set(DEFAULT_TIME_ZONE);
   $global_runtime_environment_description = RUNTIME_ENVIRONMENT_DRILLBRAIN;
} elseif (strpos($php_self_string, '/nflx/') !== false) { // local box
   define('DOMAIN_NAME',      'mysuperpicks.com');
   define('ADMIN_TABLE',      'nflx');
   define('URL_HOME_PAGE',    'http://localhost/index.php');
   define('HOST',             'localhost');
   define('DATABASE_NAME',    'mysuperpickslocal');
   define('USER_NAME',        'nfllocal'); 
   define('USER_PASSWORD',    'nfllocalTest1234!');
   define('MY_SERVER_NAME',   'localhost');
   define('MY_SHORT_PATH',    '/nflx/');
   define('MAIL_TO_CONTACT',  '');
   define('MAIL_FROM_CONTACT','');
   define('LINK_CONFIRM',     'http://localhost/nflx/register.php');  // to -> http://localhost/nflx/register.php?confirmcode=eab3070ac3bcf507f26cc99c244bf502
   define('LINK_CONTACT',     'http://localhost/nflx/contact.php');
   date_default_timezone_set(DEFAULT_TIME_ZONE);
   $global_runtime_environment_description = RUNTIME_ENVIRONMENT_DEV_PC;
} else {  // local console execution will fall into here
   define('URL_HOME_PAGE',    'http://localhost/index.php');
   define('ADMIN_TABLE',      'mysuperpicks');
   define('HOST',             '127.0.0.1');
   define('DATABASE_NAME',    'mysuperpickslocal');
   define('USER_NAME',        'msplocal');
   define('USER_PASSWORD',    'msplocal');
   define('MY_SERVER_NAME',   'localhost');
   define('MY_SHORT_PATH',    '/');
   define('MAIL_TO_CONTACT',  '');
   define('MAIL_FROM_CONTACT','');
   define('LINK_CONFIRM',     'http://www.mysuperpicks.com/register.php');
   define('LINK_CONTACT',     'http://www.mysuperpicks.com/contact.php');
   date_default_timezone_set(DEFAULT_TIME_ZONE);
   $global_runtime_environment_description = RUNTIME_ENVIRONMENT_DEV_PC;
}
$whereami = ADMIN_TABLE;
if ($whereami == 'nflbrain') {
   $global_write_roach_file = false;
}
if ($chatty) {
   foreach ($_SERVER as $k => $v ) {
      $global_chatty_text .= "$k  => $v <br />";  
   }
   $global_chatty_text .=  "php_self_string = $php_self_string" . '<br />';
   $global_chatty_text .=  "global runtime = $global_runtime_environment_description" . '<br />';
}
unset($php_self_string, $chatty);

function writeDataToFile(
   $data = "NO MESSAGE WAS DEFINED", 
   $fromfile = "NO FROM FILE NAME DEFINED", 
   $fromline = "NO LINE DEFINED", 
   $write_to_filename = DEBUG_FILESPEC
) {
   global $global_write_roach_file;
   if ($global_write_roach_file == false) {
      return;
   }
   $data_info = $data; 
   if (is_array($data)) {
      $data_info = print_r($data, true);
   }
   
   $mode = 'a';   
   // if ($__writeDataToFile_first_write) {
   //    $__writeDataToFile_first_write = false;
   //    $mode = 'w';
   // }
   
   $my_date = date("c");
   //echo "Writing file $write_to_filename, mode $mode";
   $fh = fopen($write_to_filename, $mode) or die ('writeDataToFile() could not open the file ' . $write_to_filename);
   fwrite($fh, "================================\n");
   fwrite($fh, "writeDataToFile() message from FILE:$fromfile:$fromline.  $my_date\n");
   fwrite($fh, $data_info . "\n") or die ('could not write the file');
   fclose($fh);
   return true;
}

function writeDataToFileAlways(
   $data = "NO MESSAGE WAS DEFINED", 
   $fromfile = "NO FROM FILE NAME DEFINED", 
   $fromline = "NO LINE DEFINED", 
   $write_to_filename = DEBUG_FILESPEC
) {
   global $global_write_roach_file;
   if ($global_write_roach_file == false) {
      return;
   }
   $data_info = $data; 
   if (is_array($data)) {
      $data_info = print_r($data, true);
   }
   
   $mode = 'a';   
   // if ($__writeDataToFile_first_write) {
   //    $__writeDataToFile_first_write = false;
   //    $mode = 'w';
   // }
   
   $my_date = date("c");
   //echo "Writing file $write_to_filename, mode $mode";
   $fh = fopen($write_to_filename, $mode) or die ('writeDataToFile() could not open the file ' . $write_to_filename);
   fwrite($fh, "================================\n");
   fwrite($fh, "writeDataToFileAlways() message from FILE:$fromfile:$fromline.  $my_date\n");
   fwrite($fh, $data_info . "\n") or die ('could not write the file');
   fclose($fh);
   return true;
}
?>
