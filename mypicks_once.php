<?php
/*
:mode=php:

   file: mypicks_db.php
   date: apr-2016
 author: originall
   desc: This file consolodates some of the sql centric php functions.  
   The file site_fns_diminished.php is getting too large.  RULES.  Except
   for login, no SESSION vars are touched unless 'Session' is used in the
   function name; setSessionActiveLeague() is an example.
  notes:

marbles: jun 2016

function addLeagueMembershipToUser(
function buildLeaguesArrayNsp(
function deactivateInvitation(
function getActiveWeek(
function getLeagueActiveWeek(
function getLeagueCommissionerViaLeagueName(
function getLeagueId(
function getLeagueLimit(
function getLeagueName(
function getLeagueType(
function getUserDataViaEmail(
function getUserIDVia(
function getUserLeagueMemberships(
function getUsernameViaId(
function insertNewLeague(
function insertNewPlayer(
function insertNewUser(
function insertOrUpdateLeagueGreetingText(
function isCommissionerWithScope(
function isLeagueMember(
function isPlayerNameAvailable(
function isUniqueEmailAddress(
function isUniqueLeagueName(
function isUniqueTextField(
function isUniqueUsername(
function isUserNameAvailable(
function isValidLeageId(
function isValidUserAccount(
function login(
function recordLogin(
function setActiveWeek(
function setSessionActiveLeague(
function setSessionActiveWeek(
function updateScheduleWeek(
function updateUser(
function validateUserID(

*/

//require_once 'mypicks_def.php';
//require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';

function cleanout(
){
   
   $sql = "delete from temp_confirm";
   $conn = db_connect();
   

}

function setScheduleTimesXXXXX (
   $week,
   $first_date = '2016:06:20',
   $first_time = '13:00:00'
){

   if ($week < 1 || $week > 17) {
      return;
   }
   
   $ts = $first_date . ' ' . $first_time;
   $dt = new DateTime($ts);
   $ts_string = $dt->format('Y:m:d H:i:s');
   http://us2.php.net/manual/en/dateinterval.construct.php
   // http://us2.php.net/manual/en/datetime.format.php
   $ts_string = $dt->format('Y:m:d H:i:s');
   
   $update_time = "
      update schedules
         set gametime = timestamp(?)
       where schedule_id = ?
         and week = ?";
       
   $get_schid = '
      select schedule_id, gametime
        from schedules
       where week = ?
      order by schedule_id';
      
   $conn = db_connect();
   $sth = $conn->prepare($update_time);
   
   $id = '';
   $conn1 = db_connect();
   $sth1 = $conn1->prepare($get_schid);
   $sth1->bind_param("i", $week);
   $sth1->execute();
   $sth1->bind_result($id, $gt);
   while ($sth1->fetch()) {
      $sth->bind_param("sii", $ts_string, $id, $week);
      $sth->execute();
      //echo "<br />Id is $id $ts_string<br />\n";
      $dt->add(new DateInterval('PT15M'));
      $ts_string = $dt->format('Y:m:d H:i:s');
   }
   @ $sth->close();
   @ $sth1->close();
   
   $conn = db_connect();
   $s = 'update schedules set season = 2016';
   $sth = $conn->prepare($s);
   $sth->execute();
   $sth->close();
}
   
function assignPlayerNamesXXXXXXinitplayernamedb() {     
   $dropit = 'delete from nspx_leagueplayer';
   
   $conn = db_connect();
   $sth = $conn->prepare($dropit);
   $sth->execute();
   $sth->close();
   
   $s = "select id, username from users"; 
   $conn = db_connect();
   $sth = $conn->prepare($s);
   $sth->execute();
   $data = $sth->get_result();
   $result = $data->fetch_all(MYSQLI_ASSOC);
   
   //[64] => Array
   // (
   //     [id] => 94
   //     [username] => SiouxForever
   // )
   //
   //[65] => Array
   // (
   //     [id] => 95
   //     [username] => nateskala
   // )
   
   
   $inserter = "
      insert
        into nspx_leagueplayer
         (userid, leagueid, playername)
      values
         (     ?,        ?,         ?)";
   
   $s2 = "select league_id from users where id = ?";
   $conn = db_connect();
   $sth = $conn->prepare($s2);
   $conn2 = db_connect();
   $sth2 = $conn2->prepare($inserter);

   
   for ($i=0; $i<sizeof($result); $i++) {
      $user_info_array = $result[$i];
      $user_id = $user_info_array['id'];
      $user_name = $user_info_array['username'];
      
      $sth->bind_param("i", $user_id);
      $sth->execute();
      $sth->bind_result($leagues);
      $sth->fetch();
      writeDataToFile("bouind " . print_r($leagues, true));
      $league = explode('-', $leagues);
      $league = str_replace('-', "", $league);
      $league = array_filter( $league, 'strlen' );  // len != 0
      sort($league);
      
      writeDataToFile('here are the leagues ' . print_r($league, true), __FILE__, __LINE__);
      
      foreach ($league as $league_id) {
         echo "user_id " . $user_info_array['id'] . ", " . $user_info_array['id'] . ", league id $league_id <br />";
         $player_name = $user_info_array['username'] . "_L" . $league_id . "_id" . $user_info_array['id'];
         $sth2->bind_param("iis", $user_info_array['id'], $league_id, $player_name);
         $sth2->execute();
      }
   }
   $sth->close();
   $sth2->close();
}

function loadHomepageTextXXXXX() {
   return false;
// drop TABLE mysuperpickslocal.homepage_text;
   $records = 181; 

   $insert_text =  '<h2 style="font-style:italic; text-align:center;"><span style="font-size:36px;">';
   $insert_text .= 'Welcome to MySuperPicks.com</span></h2><br /><p style="text-align:center;" class="lead">';
   $insert_text .= 'Show off your NFL mind!</p><br />';   
   
   $mysql = "
      insert into homepage_text (field_name, field_text)
      values
      (?, ?)";
      
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   try {
      $conn = db_connect();
      $sth = $conn->prepare($mysql);
      $sth->bind_param("ss", $ndx, $insert_text);
      for ($ndx = 1 ;$ndx < $records; $ndx++) {
         $sth->execute();
      }
      $ndx = 'index';
      $sth->execute();
   } catch (mysqli_sql_exceSption $e) {
      $ermsg = "loadHomepageText()  \n".
         ' sql: ' . $mysql . "\n\n" .
         ' MYSQL ERROR TO STRING: ' . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'dberror';
      break;
   }

}

?>