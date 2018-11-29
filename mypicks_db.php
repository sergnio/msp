<?php
/*
:mode=php:

   file: mypicks_db.php
   date: apr-2016
 author: hugh shedd
   desc: This file consolidates some of the sql centric php functions.  
   The file site_fns_diminished.php is getting too large.  RULES.  Except
   for login, no SESSION vars are touched unless 'Session' is used in the
   function name; setSessionActiveLeague() is an example.
  notes:

marbles: aug 2016

function addLeagueMembershipToUser(
function buildLeaguesArrayNsp(
function checkAndSetActiveWeek(
function checkUserPassword(
function deactivateInvitation(
function getActiveWeek(
function getCommissonerInfo(
function getCount(
function getDatabaseTime(
function getEmailCommaList(
function getLeagueActiveWeek(
function getLeagueCommissionerViaLeagueName(
function getLeagueId(
function getLeagueLimit(
function getLeagueName(
function getLeagueNamesIDsArray(
function getLeagueParameters(
function getLeagueType(
function getLinkConfirm(
function getLinkContact(
function getNoReplyEmailAddress(
function getSiteContactToFromAddresses(
function getUserDataViaEmail(
function getUserEmailAddress(
function getUserIDVia(
function getUserLastLeagueSelect(
function getUserLeagueMemberships(
function getUsernameViaId(
function insertNewLeague(
function insertNewPlayer(
function insertNewPlayerOldOne(
function insertNewUser(
function insertOrUpdateLeagueGreetingText(
function isActiveLeaguePlayer(
function isCommissionerWithScope(
function isLeagueMember(
function isLoginShowMessage(
function isPlayerNameAvailable(
function isSiteActive(
function isUniqueEmailAddress(
function isUniqueLeagueName(
function isUniqueTextField(
function isUniqueUsername(
function isUserNameAvailable(
function isValidLeageId(
function isValidUserAccount(
function login(
function recordLogin(
function refValues($arr){
function retireInvitation(
function runSql(
function setActiveWeek(
function setLeagueLastWeek(
function setLeagueStatus(
function setSessionActiveLeague(
function setSessionActiveWeek(
function setSessionReferenceMode(
function setUserLastLeagueSelected(
function updateLeagueUser(
function updateScheduleWeek(
function updateUser(
function validateUserID(


school: ->fetch()
   TRUE 	Success. Data has been fetched
   FALSE Error occurred
   NULL 	No more rows/data exists or data truncation occurred
   
      ->execute()
      true, false
   
new exception model: http://us2.php.net/manual/en/mysqli.error.php

mysqli_report(MYSQLI_REPORT_OFF); //Turn off irritating default messages

$mysqli = new mysqli("localhost", "my_user", "my_password", "world");

$query = "SELECT XXname FROM customer_table ";
$res = $mysqli->query($query);

if ($mysqli->error) {
    try {   
        throw new Exception("MySQL error $mysqli->error <br> Query:<br> $query", $msqli->errno);   
    } catch(Exception $e ) {
        echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
    }
}   

*/

//require_once 'mypicks_def.php';
//require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
function recordForgotPasswordChange(
   $user_id,
   $new_plain_text_password,
   &$ref_status_text = ''
){

   $mysql = "
      update users
         set temppassword = ?
       where id = ?";
   
   $ref_status_text = '';
   $ans = '';
   $status = false;
   while (1) {
      if (!$user_id || !$new_plain_text_password) {
         $ref_status_text = "mpdb-572 invalidparam '$user' '$new_plain_text_password'";
         break;
      }
      
      if (!$ans = runSql($mysql, array("si", $new_plain_text_password, $user_id), 1, $ref_status_text)) {
         if ($ans === false) {
            $ref_status_text .= ' mpdb-578 dberror';
            break;
         }
         if ($ans === null) {
            $ref_status_text .= ' mpdb-582 isnull';
            break;
         }
      }
      
      if ($ans !== 1) {
         $ref_status_text = "mpdb-588 notone '$ans'";
         break;
      }
      $status = true;
      break;         
   }
   return $status;
}


//Todo use this more fitting name than insertOrUpdateLeagueGreetingText
function updateHomepageText($field_name, $new_text, &$ref_status_text = '')
{
	return insertOrUpdateLeagueGreetingText($field_name, $new_text, $ref_status_text);
}

function insertOrUpdateLeagueGreetingText(
   $league_id, // field_name is the index and league_id is used
   $new_text,
   &$ref_status_text = ''
){
   
   $mysql_updateOrInsert = "
      select 88
        from homepage_text
       where field_name = ?";
         
   $mysql_insert = "
      insert into homepage_text
         (  id, field_text, field_name)
      values
         (NULL,          ?,          ?)";
         
   $mysql_update = "
      update homepage_text 
         set field_text = ?
       where  field_name = ?";
     
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   $junk = 0;
   $status = 0;
   $ref_status_text = '';
   while (1) {

      $execute_this_sql = '';
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_updateOrInsert);
         $sth->bind_param("s", $league_id);
         $sth->execute();
         $sth->bind_result($junk);
         $insert_or_update = '';
         if($sth->fetch()) {
            $insert_or_update = 'update';
            $execute_this_sql = $mysql_update;
         } else {
            $insert_or_update = 'insert';
            $execute_this_sql = $mysql_insert;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = "insertOrUpdateLeagueGreetingText()  \n".
            " sql: " . $mysql_updateOrInsert . "\n\n" .
            " league_id " . $league_id . "\n" .
            " MYSQL ERROR TO STRING: " . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      $sth->close();
      try {
         $sth = $conn->prepare($execute_this_sql);
         $sth->bind_param("ss", $new_text, $league_id);
         $sth->execute();
         $sth->store_result();
         $update_count = $sth->affected_rows;
         if ($insert_or_update == 'insert' && $update_count === 0){
            $ref_status_text = 'insert';
            break;
         } elseif ($insert_or_update == 'update' && $update_count === 0) {
            $status = 1;   // special case
            $ref_status_text = 'norows';
            break;
         }
         @$sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "insertOrUpdateLeagueGreetingText()  \n".
            'sql: ' . $execute_this_sql . "\n\n" .
            'league_id ' . $league_id . "\n" .
            'new_text ' . $new_text . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }

   return $status;
}

// Has to exist and be active
function isValidUserAccount(
   $user_info,
   $which_key = 'id', // userid, id, username
   $ref_status_text = ''
){

   $field_name = '';
   $bind_types = '';
   switch ($which_key) {
   case "id" :
   case "userid" :
      $field_name = 'id';
      $bind_types .= 'i';
      break;
   case "username":
      $field_name = 'username';
      $bind_types .= 's';
      break;
   default :
      $ref_status_text = 'nosuchuserfield';
      return false;
   }

   $mysql = "
      select count(*)
        from users
       where $field_name = ?
         and active_status = 1";
         
   $status = 0;
   $count = 0;
   
   while (1) {
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param($bind_types, $user_info);
         $sth->execute();
         $sth->bind_result($count);
         $sth->fetch();
      } catch (mysqli_sql_exception $e) {
         $ermsg = 'isValidUserAccount()  \n' .
            'sql: ' . $mysql . "\n\n" .
            'user ' . $user_info . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }

      if ($count == 1) {
         $status = 1;
      } elseif ($count > 1) {
         $ref_status_text = 'multipleuseraccounts';
      }
      break;
   }
   @$sth->close();
   return $status;
}

function getLinkConfirm(
   &$ref_status_text = ''
){

   $mysql = "
      select linkconfirm
        from nsp_admin
       where site = ?
       limit 1";
        
   $link = false;
   $ref_status_text = '';
   while (1) {
      
      $site_name = ADMIN_TABLE;
      if ($site_name == 'ADMIN_TABLE') {
         $ref_status_text = 'admintablenotdefined';
         break;
      }
      
      if (!$ans = runSql($mysql, array("s", $site_name), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-286';
         if ($ans === null) {
            $link = null;
            break;
         }
         if ($ans === false) {
            $link = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }

      if (sizeof($ans) != 1) {
         $link = false;
         $ref_status_text = 'notone';
         break;
      }
      
      $link = $ans[0]['linkconfirm'];
      break;
   }
   return $link;
}

function getLinkContact(
   &$ref_status_text = ''
){

   $mysql = "
      select linkcontact
        from nsp_admin
       where site = ?
       limit 1";
        
   $link = false;
   $ref_status_text = '';
   while (1) {
      
      $site_name = ADMIN_TABLE;
      if ($site_name == 'ADMIN_TABLE') {
         $ref_status_text = 'admintablenotdefined';
         break;
      }
      
      if (!$ans = runSql($mysql, array("s", $site_name), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-331';
         if ($ans === null) {
            $link = null;
            break;
         }
         if ($ans === false) {
            $link = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      
      if (sizeof($ans) != 1) {
         $ref_status_text = 'notone';
         break;
      }
      
      $link = $ans[0]['linkcontact'];
      break;
   }
   return $link;
}



function getLeagueParameters(
   $league_id,
   &$ref_league_name,
   &$ref_push,
   &$ref_points,
   &$ref_first,
   &$ref_last,
   $ref_status_text = ''
){

   $mysql = "
      select league_points, league_push, firstround, lastround, league_name
        from league
        where league_id = ?
        and active = 1";
        
   $status = false;
   
   $ref_league_name = '';
   $ref_push = '';
   $ref_points = '';
   $ref_first = '';
   $ref_last = '';
   $tmp_points = '';
   $tmp_push = '';
   while (1) {
      if (!$ans = runSql($mysql, array("i", $league_id), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-386';
         
         if ($ans === null) {
            $status = null;
            break;
         }
         if ($ans === false) {
            $status = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      
      if (sizeof($ans) != 1) {
         $status = false;
         $ref_status_text .= " notone";
         break;
      }
      
      $ref_first =         $ans[0]['firstround'];
      $ref_last =          $ans[0]['lastround'];
      $ref_league_name =   $ans[0]['league_name'];
      
      $tmp_points = $ans[0]['league_points'];
      $tmp_push = $ans[0]['league_push'];
      
      $switch_error = false;
      switch ($tmp_points) {
      case LEAGUE_ODDS_IN_NOT_USE: $ref_points = 0; break;
      case LEAGUE_ODDS_IN_USE: $ref_points = 1; break;
      default: $switch_error = true; $ref_status_text = "mpdb-426 pointdata '$tmp_points'"; break;
      }
      if ($switch_error) {
         break;
      }
      //writeDataToFile("tmp push: $tmp_push, $ref_push
      switch ($tmp_push) {
      case LEAGUE_PUSH_ZERO : $ref_push = 0; break;
      case LEAGUE_PUSH_HALF :  $ref_push = 0.5; break;
      case LEAGUE_PUSH_ONE :  $ref_push = 1; break;
      default: $switch_error = true;  $ref_status_text = "mpdb-426 pushrecord '$tmp_push'"; break;
      }
      if ($switch_error) {
         break;
      }
      
      $status = 1;
      break;
   }
      
   return $status;
}

function isLoginShowMessage(
   &$login_message,
   $ref_status_text = ''
){

   $mysql = "
      select siteloginmessage
        from nsp_admin
       where siteloginmessageshow = 2
         and site = ?";

   $status = false;
   $login_message = '';
   $ans = '';
   while (1) {
      
      $site_name = ADMIN_TABLE;
      if ($site_name == 'ADMIN_TABLE') {
         $ref_status_text = 'sitename';
         break;
      }
      
      if (!$ans = runSql($mysql, array("s", $site_name), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-464';
         if ($ans === null) {
            $status = null;
            break;
         }
         if ($ans === false) {
            $status = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      
      if (sizeof($ans) != 1) {
         $ref_status_text .= 'notone';
         break;
      }
      $login_message = $ans[0]['siteloginmessage'];
      $status = 1;
      break;
   }
   
   return $status;
}

function getLeagueId(
   $league_name,
   &$ref_status_text = ''
){

   $mysql = "
      select league_id
        from league
      where league_name = ?
        and active = 1";
        
   $league_id = false;
   while (1) {
      if(!$ans = runSql($mysql, array("s", $league_name), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-500';
         if ($ans === null) {
            $league_id = null;
            break;
         }
         if ($ans === false) {
            $league_id = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      if (sizeof($ans) != 1) {
         $ref_status_text = 'notone';
         break;
      }
      $league_id = $ans[0]['league_id'];
      break;
   }
      
   return $league_id;
}

function setLeagueStatus(
   $league_id, 
   $league_status,
   &$ref_status_text = ''
){
   $mysql = "
      update league
         set active = ?
       where league_id = ?
       limit 1";
        
   $status = false;
   while (1) {
      
      if (!$league_id || !($league_status !== 0 || $league_status != 1)) {
         $ref_status_text = 'input';
         break;
      }
      
      if(!$ans = runSql($mysql, array("ii", $league_status, $league_id), 1, $ref_status_text)) {
         $ref_status_text .= ' mpdb-542';
         if ($ans === null) {
            $status = NULL;
            break;
         }
         if ($ans === false) {
            $status = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      
      if ($ans === 0) {
         $status = 0;
         break;
      }
      if ($ans === 1) {
         $status = 1;
         break;
      }
      $ref_status_text .= ' mpdb-564 never here ';
      if (isset($ans)) {
         $ref_status_text .=  print_r($ans, true);
      }
      break;
   }
   return $status;
}


function setLeagueLastWeek(
   $league_id, 
   $week,
   &$ref_status_text = ''
){
   $mysql = "
      update league
         set lastround = ?
       where league_id = ?
       limit 1";
        
   $status = false;
   while (1) {
      
      if (!$league_id || $week === '' || $week === NULL || $week === false) {
         $ref_status_text = 'input';
         break;
      }
      
      if (!$ans = runSql($mysql, array("ii", $week, $league_id), 1, $ref_status_text)) {
         $ref_status_text .= ' mpdb-542';
         if ($ans === null) {
            $status = 'null';
            return;
         }
         if ($ans === false) {
            $status = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      if ($ans === 0) {
         $status = 0;
         break;
      }
      if ($ans == 1) {
         $status = 1;
         break;
      }
      $ref_status_text .= ' mpdb-614 never here ';
      if (isset($ans)) {
         $ref_status_text .=  print_r($ans, true);
      }
      break;
   }
   
   return $status;
}

function getCommissonerInfo(
   $league_id,
   &$first_name,
   &$last_name,
   &$user_id,
   &$email,
   &$ref_status_text = ''
){

   $mysql = "
      select u.fname,
             u.lname,
             u.email,
             u.id
        from users as u, league as g
       where u.id = g.commissioner
         and g.league_id = ?
         and g.active = 1";
        
   $status = false;
   $ref_status_text = '';
   $first_name = '';
   $last_name = '';
   $user_id = '';
   $email = '';
   while (1) {
      if (!$ans = runSql($mysql, array("i", $league_id), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-650';
         if ($ans === null) {
            $status = 'null';
            return;
         }
         if ($ans === false) {
            $status = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      if (sizeof($ans) != 1) {
         $ref_status_text = 'notone';
         break;
      }
      $first_name =  $ans[0]['fname'];
      $last_name =   $ans[0]['lname'];
      $user_id =     $ans[0]['id'];
      $email =       $ans[0]['email'];
      $status = true;
      break;
   }
   return $status;
}

function getLeagueLimit(
   $league_id,
   &$ref_status_text = ''
){

   $mysql = "
      select league_picks
        from league
      where league_id = ?
        and active = 1";
        
   $league_picks = false;
   while (1) {
      if (!$ans = runSql($mysql, array("i", $league_id), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-690';
         if ($ans === null) {
            $league_picks = 'null';
            return;
         }
         if ($ans === false) {
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      if (sizeof($ans) != 1) {
         $ref_status_text = 'notone';
         break;
      }
      $league_picks = $ans[0]['league_picks'];
      break;
   }
   return $league_picks;
}


// Should this error if already a member?
function addLeagueMembershipToUser(
   $user_id,
   $league_id, 
   &$ref_status_text = ''
){

   $mysql_chkAlreadyMember = "
      select 88
        from users
       where id = ?
       and league_id like ?";

   $like_string = '%-' . $league_id . '-%';

   $mysql_insert = "
      update users 
      set league_id = concat(league_id, '-', ?, '-') 
      where id = ?";
      
   $status = 0;
   while (1) {
      
      if (!isValidUserAccount($user_id, 'id', $ref_status_text)) {
         $ref_status_text = 'notvalidaccount';
         break;
      }
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_chkAlreadyMember);
         $sth->bind_param("is", $user_id, $like_string);
         $sth->execute();
         if ($sth->fetch()) {
            $ref_status_text = 'alreadyleaguemember';
            break;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = 'addLeagueMembershipToUser()  \n' .
            'sql: ' . $mysql_chkAlreadyMember . "\n\n" .
            'user_id ' . $user_id . "\n" .
            'league_id ' . $league . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror1';
         break;
      }
      
      $sth->close();
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_insert);
         $sth->bind_param("ii", $league_id, $user_id);
         if(!$sth->execute()) {
            $ref_status_text = 'dberror2';
            break;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = 'addLeagueMembershipToUser()  \n' .
            'sql: ' . $mysql_insert . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror3';
         break;
      }
      
      $status = 1;
      @$sth->close();
      break;
   }
   writeDataToFile("addLeagueMembershipToUser() user, league, status: $user_id, $league_id, $status, $ref_status_text", __FILE__, __LINE__);
   return $status;
}

function getLeagueCommissionerViaLeagueName(
   $league_name,
   &$ref_status_text = ''
){

   $mysql = "
      select commissioner
        from league
      where league_name = ?
        and active = 1";
     
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   $comissioner_id = '';
   $ref_status_text = '';
   while (1) {
      
      try {
         $count = '';
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("s", $league_name);
         $sth->execute();
         $sth->bind_result($comissioner_id);
         if(!$sth->fetch()) {
            $ref_status_text = 'norecordfound';
            $comissioner_id = 0;  // be sure
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getLeagueCommissionerViaLeagueName()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'league_name: ' . $league_name . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      @$sth->close();
      break;
   }
   return $comissioner_id;
}

function validateUserID(
   $user_id,
   &$ref_status_text = ''
){

   $mysql = "
      select count(*) 
        from users
      where id = ?";
     
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   $status = 0;
   $ref_status_text = '';
   while (1) {
      
      try {
         $count = '';
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("i", $user_id);
         $sth->execute();
         $sth->bind_result($count);
         $sth->fetch();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "validateUserID()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      if ($count == 0) {
         $ref_status_text = 'userdoesnotexist';
      } elseif ($count == 1) {
         $status = 1;
      } elseif ($count > 1) {
         $ref_status_text = 'useridnotunique';
      }
      break;
   }
   @$sth->close();
   return $status;
}

// error check only
function getUsernameViaId(
   $userid
){
   $mysql = "
      select username
        from users
       where id = ?";
       
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;
   $user_name = '';
   
   try {
      $conn = db_connect();
      $sth = $conn->prepare($mysql);
      $sth->bind_param("i", $userid);
      $sth->execute();
      $sth->bind_result($user_name);
      $sth->fetch();
   } catch (mysqli_sql_exception $e) {
      $ermsg = "getUsernameViaId()  \n" .
         'sql: ' . $mysql . "\n\n" .
         'userid = ' . $userid . " \n" .
         'MYSQL ERROR TO STRING: ' . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
   }
   
   @$sth->close();
   return $user_name;
}

function isUniqueUsername(
   $user_name = NULL
){
   if (!$user_name) {
      return 0;
   }
   writeDataToFile("isUniqueUsername '$user_name'", __FILE__, __LINE__);
   $mysql = '
      select count(*) as count
        from users
       where username = ?';
    return isUniqueTextField($mysql, $user_name);
}


function isUniqueEmailAddress(
   $email_address = NULL,
   $user_id = 0
){
   if (!$email_address) {
      return 0;
   }
   writeDataToFile("isUniqueEmailAddress '$email_address'", __FILE__, __LINE__);
   $mysql = '
      select count(*) as count, id
        from users
       where email = ?';
       
   $is_unique = 0;
   while (1) {
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF; // TODO index for leaguename

      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("s", $email_address);
         $sth->execute();
         $sth->bind_result($counter, $id_owner);
         $sth->fetch();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "isUniqueEmailAddress()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'email_address = ' . $email_address . " \n" .
            'user_id = ' . $user_id . " \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
      }
      if ($counter == 0) {
         $is_unique = 1;
         break;
      }
      if ($user_id != 0 && $counter == 1) {
         if ($user_id == $id_owner) {
            // he already owns it, so it's unique.
            $is_unique = 1;
            break;
         }
         break;  // not unique
      }
      break;
   }
   return $is_unique;
}

function isUniqueLeagueName(
   $league_name = NULL,
   $ref_status_text = ''
){

   if (!$league_name) {
      $ref_status_text = 'noleague';
      return 0;
   }
   
   $mysql = '
      select count(*) as count
        from league
       where league_name = ?';
   
   $is_unique = false;
   $counter = 0;
   $ans = '';
   while (1) {
      if (!$ans = runSql($mysql, array("s", $league_name), 0, $ref_status_text)) {
         if ($ans === null) {
            $ref_status_text = 'null';
            $is_unique = null;
            break;
         }
         if ($ans === false) {
            $ref_status_text = 'false';
            break;
         }
      }
      if (sizeof($ans) == 1) {
         $counter = $ans[0]['count'];
         if ($counter === 0) {
            $is_unique = true;
            $ref_status_text = 'unique';
         } else {
            $is_unique = false;
         }  
      } else {
         break;
      }
      
      $status = true;
      break;
   }
      
   writeDataToFile("isUniqueLeagueName(
      league_name     '$league_name'
      ref_status_text '$ref_status_text' 
      counter         '$counter'
      return          '$is_unique'", __FILE__, __LINE__);
   
   return $is_unique;
}

function isUniqueTextField(
   $mysql,  // projection is count(*)
   $string
){

   writeDataToFile("isUniqueTextField($mysql, \n$string", __FILE__, __LINE__);
   
   $counter = 0;
   $is_unique = 0;
   while (1) {
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF; // TODO index for leaguename

      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("s", $string);
         $sth->execute();
         $sth->bind_result($counter);
         $sth->fetch();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "isUniqueTextField()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'string = ' . $string . " \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
      }
      if ($counter == 0) {
         $is_unique = 1;
         break;
      }
      break;
   }
   return $is_unique;
}

// TODO This needs a roll back or something.  We can't check player name
// unique until the league id is obtained.  If in some strange case
// insertNewPlayer fails... what?
function insertNewLeague(
   $league_name, 
   $last_userid, 
   $league_type, 
   $league_points, 
   $league_picks, 
   $league_push, 
   $league_player_name,
   $league_start,
   &$ref_new_league_id = NULL,
   &$ref_status_text = ''
){

   $ref_status_text = '';
   if (!validateUserID($last_userid, $ref_status_text)) { 
      $ref_status_text = 'notvaliduser';
      return 0;
   }

   writeDataToFile("Passed params insert_new_league(): league_name, last_userid, league_type, league_points, league_picks, league_push, player_name : " . 
   " lnm "  . $league_name   . ",  " .
   " uid "  . $last_userid   . ",  " .
   " type " . $league_type   . ",  " .
   " pts "  . $league_points . ",  " .
   " pks "  . $league_picks  . ",  " .
   " pus "  . $league_push   . ",  " .
   " str "  . $league_start   . ",  " .
   " ply "  . $league_player_name . ",  ", __FILE__, __LINE__);
   
   $mysql = "
      insert into league
         (league_id, league_name, commissioner, found_date, league_type, league_points, league_picks, league_push, firstround)
      values
         (     NULL,           ?,            ?,      NOW(),           ?,             ?,            ?,           ?,           ?)";      
  
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   $status = 0;
   $ref_status_text = '';
   while (1) {
      
      try {

         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("siiiiii",
                                 $league_name, 
                                 $last_userid, 
                                 $league_type, 
                                 $league_points, 
                                 $league_picks,
                                 $league_push,
                                 $league_start);
         $sth->execute();
         $ref_new_league_id = $sth->insert_id;
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "insertNewLeague()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      $ref_newplay_status_text = '';
      if (!insertNewPlayer($ref_new_league_id, $last_userid, $league_player_name, $ref_newplay_status_text)) {
         // TODOO What?  This can't happen.  insertNewPlayer will record the failure
         writeDataToFile("insertNewPlayer(): " . $ref_newplay_status_text, __FILE__, __LINE__);
         die();
      }
      
      $status = 1;
      break;
   }
   return $status;
}

function getUserInfo(
   $user_id,
   &$first_name,
   &$last_name,
   &$username,
   &$email,
   &$ref_status_text = ''
){

   $mysql = "
      select u.fname,
             u.lname,
             u.username,
             u.email,
             u.id
        from users as u
        where id = ?";
        
   $status = false;
   $ref_status_text = '';
   $first_name = '';
   $last_name = '';
   $username = '';
   $email = '';
   while (1) {
      if (!$ans = runSql($mysql, array("i", $user_id), 0, $ref_status_text)) {
         $ref_status_text .= ' mpdb-660';
         if ($ans === null) {
            $status = 'null';
            return;
         }
         if ($ans === false) {
            $status = false;
            break;
         }
         $ref_status_text .= " unknown ans: '$ans'";
         break;
      }
      if (sizeof($ans) != 1) {
         $ref_status_text = 'notone';
         break;
      }
      $first_name =  $ans[0]['fname'];
      $last_name =   $ans[0]['lname'];
      $username =    $ans[0]['username'];
      $email =       $ans[0]['email'];
      $status = true;
      break;
   }
   return $status;
}

// Check everything again.  Hot link issues.
function insertNewUser(
   $username,
   $password,
   $fname,
   $lname,
   $useremail,
   $usermode,  // also seen as usertype; user, admin ONLY
   $activestatus,
   &$ref_new_user_id,
   &$ref_status_text = ''  // notunique, emailnotvalid, 
){
   
   
   
   $mysql = "
      insert into users
         (username,    id,  password,    fname,     lname,     email,     usermode,     active_status,  
          league_id)
      values
         (       ?,  NULL,         ?,        ?,         ?,         ?,            ?,                 ?,
                 '')";
   
   $status = 0;
   while (1) {
      
      if (!($usermode === 'user' || $usermode === 'admin')) {
         $ref_status_text = 'unknownusermode';
         break;
      }

      if(!isUniqueUsername($username)) {  // unique mean DOES NOT EXIST
         $ref_status_text = 'usernamenotunique';
         break;
      }
      
      if (!isUniqueEmailAddress($useremail)) {
         $ref_status_text = 'emailNotUnique';
         break;
      }
      
      if (!valid_email($useremail)) {
         $ref_status_text = 'emailnotvalid';
         break;
      }
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT; // throw exceptions

      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("ssssssi",
                  $username,
                  $password,
                  $fname,
                  $lname,
                  $useremail,
                  $usermode,
                  $activestatus);
         if (!$sth->execute()) {
            $ref_status_text = 'insertfailed';
            break;
         }
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "insertNewUser()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      @$sth->close();
      $status = 1;
      break;
   }
   return $status;
}


function insertNewPlayer(
   $leagueid, 
   $userid,
   $playername,
   &$ref_status_text = ''  //notvaliduseraccount, usersalreadyhasplayername, leagueplayernameused, dberror
){

   //     league valid
   // AND user account valid
   // AND league player name not already used 
   // AND user does not already have a league player name
   
   writeDataToFile("insertNewPlayer-newone(
   $leagueid, 
   $userid,
   $playername,
   $ref_status_text)", __FILE__, __LINE__);
   
   $mysql_chkUserHasNoPlayerName = "
      select playername
        from nspx_leagueplayer
       where userid = ?
         and leagueid = ?";
   
   $mysql_chkPlayerNameNotUsed = "
      select playername
        from nspx_leagueplayer
       where playername = ?
         and leagueid = ?";
       
   $mysql_insert = "
      insert into nspx_leagueplayer 
         (userid, leagueid, playername, paid,  joindate,  active)
      values
         (    ?,        ?,           ?,    1, curdate(),       2)";

   $status = false;
   while (1) { 
      
      if (!isValidUserAccount($userid, 'userid')) {
         $ref_status_text = 'notvaliduseraccount';
         break;
      }
     
      $ans = runSql($mysql_chkUserHasNoPlayerName, array("ii",  $userid, $leagueid), 0, $ref_status_text);
      if ($ans === false) {
         //formatSessionMessage("We are unable to display standings at this time.", 'info', $msg, "sklm-153 $ref_status_text");
         //setSessionMessage($msg, 'error');
         $ref_status_text .= ' dberror-1380';
         break;
      }
      if ($ans !== null) {
         $ref_status_text = 'alreadyleagueplayer';
         break;
      }
      
      $ans = runSql($mysql_chkPlayerNameNotUsed, array("si", $playername, $leagueid), 0, $ref_status_text);
      if ($ans === false) {
         $ref_status_text .= ' dberror-1380';
         break;
      }
      if ($ans !== null) {
         $ref_status_text .= ' leagueplayernameused-1400';
         break;
      }
      
      $ans = runSql($mysql_insert, array("iis", $userid, $leagueid, $playername), 1, $ref_status_text);
      if ($ans === false) {
         $ref_status_text .= ' dberror-1380';
         break;
      }
      if ($ans === null) {
         $ref_status_text .= ' dberror-1416';
         break;
      }
      
      if ($ans != 1) {
         $ref_status_text .= ' dberror-1315';
         break;
      }
         
      $status = true;
      break;
   }
   
   return $status;
}

function insertNewPlayerNew(
   $leagueid, 
   $userid,
   $playername,
   &$ref_status_text = ''  //notvaliduseraccount, usersalreadyhasplayername, leagueplayernameused, dberror
){

   //     league valid
   // AND user account valid
   // AND league player name not already used 
   // AND user does not already have a league player name
   
   writeDataToFile("insertNewPlayer-newone(
   $leagueid, 
   $userid,
   $playername,
   $ref_status_text)", __FILE__, __LINE__);
   
   // In this league, does the user already have this player name?
   $mysql_chkUserHasNoPlayerName = "
      select playername
        from nspx_leagueplayer
       where userid = ?
         and leagueid = ?";
   
   // In this league, is this player name already in use?
   $mysql_chkPlayerNameNotUsed = "
      select playername
        from nspx_leagueplayer
       where playername = ?
         and leagueid = ?";
       
   $mysql_insert = "
      insert into nspx_leagueplayer 
         (userid, leagueid, playername, paid,  joindate,  active)
      values
         (    ?,        ?,           ?,    1, curdate(),       2)";

   $status = false;
   $ref_status_text = '';
   while (1) { 
      
      if (!isValidUserAccount($userid, 'userid')) {
         $ref_status_text = 'notvaliduseraccount';
         break;
      }
      
      if (!$userid || !$leagueid || !$playername) {
         $ref_status_text = "mpdb-1367 paramsmissing '$leagueid', '$userid', '$playername'";
         break;
      }
     
      if (!$ans = runSql($mysql_chkUserHasNoPlayerName, array("ii",  $userid, $leagueid), 0, $ref_status_text)) {
         if ($ans === false) {
            $ref_status_text .= ' mpdb-1373';
            break;
         } elseif ($ans === NULL) {
            // ok
         } elseif ($ans === 0) {
            // ok
         }
      }
      if ($ans) {
         $ref_status_text .= ' mpdb-1382 usersalreadyhasplayername';
         break;
      }
      
      if (!$ans = runSql($mysql_chkPlayerNameNotUsed, array("si", $playername, $league_id), 0, $ref_status_text)) {
         if ($ans === false) {
            $ref_status_text .= ' mpdb-1388';
            break;
         } elseif ($ans === NULL) {
            // ok
         } elseif ($ans === 0) {
            // ok
         }
      }
      if ($ans) {
         $ref_status_text .= ' mpdb-1397 leagueplayernameused';
         break;
      }
      
      if (!$ans = runSql($mysql_insert, array("iis", $userid, $leagueid, $playername), 1, $ref_status_text)) {
         $ref_status_text .= ' mpdb-1402';
         if ($ans === false) {
            break;
         } elseif ($ans === NULL) {
            $status = null;
            $ref_status_text .= ' mpdb-1407 noupdate';
            break;
         }
      }
      if ($ans === 0) {
         $status = 0;
         $ref_status_text .= ' mpdb-1413 noupdate';
         break;
      }
      if ($ans > 1) {
         $ref_status_text .= ' mpdb-1417 greaterthanone';
         break;
      }
      
      $status = $ans;
      break;
   }
   
   return $status;
}



// This will change - just stubbing it now
function buildLeaguesArrayNsp(
   $league_id,  // the -3-6-9 thing in users
   &$ref_all_my_leagues_array,
   &$ref_test_leagues_array,
   &$ref_active_league_id = ''
){

   $is_active = ' 
      select 88
        from league
       where league_id = ?
         and active = 1
       limit 1';
        
   $mysql = '
      select league_id,
             league_name,
             commissioner,
             league_id,
             league_type,
             league_points,
             league_picks,
             league_push
        from league 
       where league_id = ?
         and active = 1';
       
   $league = explode('-', $league_id);
   $league = str_replace('-', "", $league);
   $league = array_filter( $league, 'strlen' );  // len != 0
   sort($league);
       
   $status = false;
   $active_leagues = array();
   $ref_all_my_leagues_array = '';  // expanded session array with all my active leagues info
   $ref_active_league_id = '';      // the new thingy with only active leagues -1--34--4 ....
   while (1) {

      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      $cnt = 0;
      
      try {
         
         $conn = db_connect();
         $sth = $conn->prepare($is_active);
         foreach ($league as $id) {
            $sth->bind_param("i", $id);
            $sth->execute();
            if ($sth->fetch()) {
               $active_leagues[] = $id;
            }
         }
         @ $sth->close();
         $ref_test_leagues_array = $active_leagues;
   
         $ref_active_league_id = '';
         foreach ($active_leagues as $id) {
            $ref_active_league_id .= '-' . $id . '-';
         }
         
         $league_no_name_index = 1000;
         $member_of_league_id = '';
         
         
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         if (!$sth) {
            writeDataToFile("failed to prepare $mysql", __FILE__, __LINE__);
            break;
         }
         
         foreach ($league as $member_of_league_id) {
            $sth->bind_param("i",  $member_of_league_id);
            $sth->execute();
            $sth->bind_result($league_id,
                              $league_name,
                              $commissioner,
                              $league_id,
                              $league_type,
                              $league_points,
                              $league_picks,
                              $league_push);
            
            if (!$sth->fetch()) {
               continue;
            }
            
            if (empty($league_name)) {
               $league_name = "NO_NAME_" . $league_no_name_index;
               $league_no_name_index += 1;
            }
            
            $vals = array( 'league_id' =>     $league_id,
                           'league_name' =>   $league_name,
                           'commissioner' =>  $commissioner,
                           'league_id' =>     $league_id,
                           'league_type' =>   $league_type,
                           'league_points' => $league_points,
                           'league_picks' =>  $league_picks,
                           'league_push' =>   $league_push
                           );
            $ref_all_my_leagues_array[$league_name] = $vals;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = "buildLeaguesArrayNsp()  \n" .
            'sql: ' . $mysql_insert . " \n\n" .
            'league string ' . $league_id . " \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = true;
      break;
   }
   @$sth->close();
   return $status;
}


//SESSION var FAILED_LOGINHISTORY_ID will be set/cleared here.
//This will be used in the future in login attempt coding.
// interesting salt art =- https://crackstation.net/hashing-security.htm
function recordLogin(
   $action, // attempt, success, fail, inactive, dberror
   &$ref_loginid,
   $username = NULL
){
   $sql_attempt = '
      insert into nsp_loginhistory 
         (loginid, loginusername, loginagent, loginhost, loginremoteaddress, loginremoteuser, loginreferer, loginquery, loginserverport, loginsuccess, logintime)
      values 
         (   NULL,             ?,          ?,          ?,                 ?,               ?,             ?,         ?,                ?,            1,     NOW())';
   $sql_result = '
      update nsp_loginhistory
         set loginsuccess = ?
      where loginid = ?';
      
   switch($action) {
      case "attempt" : $login_result = 1; break;
      case "fail" : $login_result = 2; break;
      case "dberror" : $login_result = 3; break;
      case "inactive" : $login_result = 4; break;
      case "success" : $login_result = 5; break;
      default:  $login_result = 99; break;
   }
      
   $conn = db_connect();
   
   if ($action === 'attempt') {
      
      $http_agent =         (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'NO HTTP_USER_AGENT';
      $http_host =          (isset($_SERVER['HTTP_HOST']))       ? $_SERVER['HTTP_HOST']       : 'NO HTTP_HOST';
      $http_remoteaddress = (isset($_SERVER['REMOTE_ADDR']))     ? $_SERVER['REMOTE_ADDR']     : 'NO REMOTE_ADDR';
      $http_remoteuser =    (isset($_SERVER['REMOTE_USER']))     ? $_SERVER['REMOTE_USER']     : 'NO REMOTE_USER';
      $http_referer =       (isset($_SERVER['HTTP_REFERER']))    ? $_SERVER['HTTP_REFERER']    : 'NO HTTP_REFERER';
      $http_remoteport =    (isset($_SERVER['REMOTE_PORT']))     ? $_SERVER['REMOTE_PORT']     : 'NO REMOTE_PORT';
      $http_query =         (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) != 0)
         ? $_SERVER['QUERY_STRING']    : 'NO QUERY_STRING';
      
      $sth = $conn->prepare($sql_attempt);
      $sth->bind_param("ssssssss", $username, $http_agent, $http_host, $http_remoteaddress, $http_remoteuser, $http_referer, $http_query, $http_remoteport);
      $sth->execute();
      $ref_loginid = $sth->insert_id;
      $sth->close();
      
   } elseif ($action === 'success' || $action === 'fail' || $action === 'dberror' || $action === 'inactive') { 
      $sth = $conn->prepare($sql_result);
      $sth->bind_param("ii", $login_result, $ref_loginid);
      $sth->execute();
      $sth->close();
      if ($action === 'success') {
         unset($_SESSION['FAILED_LOGINHISTORY_ID']);
      } else {
         $_SESSION['FAILED_LOGINHISTORY_ID'] = $ref_loginid;
      }
   } else {
      $sth = $conn->prepare($sql_result);
      $sth->bind_param("ii", $login_result, $ref_loginid);
      $sth->execute();
      $sth->close();
   }
}

function login2(
   $user_name,  // or email address
   $password,
   $unsetSessionVars
){
	
   if($unsetSessionVars)
   		session_unset();  // Not sure how all this works.  This wouldn't be enough for a 'change user' would it?  What happens to my db connection?
   
	
   $conn = db_connect();
   $pw_hash = hash('sha256', $password);
   $_SESSION['login'] = 'attempt';
   
   $mysql = '
      select id,
             username,
             fname,
             lname,
             email,
             usermode,
             active_status,
             league_id,
             case when ifnull(temppassword,\'\') <> \'\' then 1 else 0 end passwordNeedsToChange
        from users 
       where (username = ?
          or email = ?)
         and password = ?';
   
    
   $login_status = false;
   $ref_loginhistory_id = '';
   $active_status = 88;
   $msg = '';
   while (1) {
      
      recordLogin('attempt', $ref_loginhistory_id, $user_name); 
      
      if (!$user_name || !$password) {
         $_SESSION['login'] = 'fail';
         formatSessionMessage("Login failed. Please supply all login credentials.", 'warning', $msg);
         setSessionMessage($msg, 'login');
         recordLogin('fail', $ref_loginhistory_id);
         break;
      }
      
      try {
         $driver = new mysqli_driver();
         $driver->report_mode = MYSQLI_REPORT_OFF;
         
         $sth = $conn->prepare($mysql);
         $sth->bind_param("sss", $user_name, $user_name, $pw_hash);
         $sth->execute();
         $sth->bind_result($id,$username,$fname,$lname,$email,$usermode,$active_status,$league_id, $passwordNeedsToChange);
         
         if ($sth->fetch()) {
            writeDataToFile("$id,$username,$fname,$lname,$email,$usermode,$active_status,$league_id, pw $password", __FILE__, __LINE__);
            
            $_SESSION['login'] = 'attempt';
            
            if (!isSiteActive()) {
               if ($usermode != 'admin') {
                  $_SESSION['login'] = 'fail';
                  formatSessionMessage("The site is currently unavailable.  It may be down for maintenance.", 'info', $msg);
                  setSessionMessage($msg, 'login');
                  break;
               }
            }
            
            if ($active_status != 0) {
               $_SESSION['login'] = 'success';
               formatSessionMessage("Welcome $fname $lname. You are now logged in.", 'success', $msg);
               setSessionMessage($msg, 'login');
            } elseif ($active_status == 0) {
               $_SESSION['login'] = 'inactive';
               formatSessionMessage('Login failed.  This account has been deactivated.  Please contact site administrator.', 'warning', $msg);
               setSessionMessage($msg, 'login');
               recordLogin('inactive', $ref_loginhistory_id);
               @ $sth->close();
               break;
            } else {
               $_SESSION['login'] = 'fail';
               formatSessionMessage('Login failed.', 'warning', $msg);
               setSessionMessage($msg, 'login');
               recordLogin('fail', $ref_loginhistory_id);
               @ $sth->close();
               break;
            }
            
            // Good user.  Good password.
            recordLogin('success', $ref_loginhistory_id);
            $_SESSION['user_id'] = $id;
            $_SESSION['valid_user'] = $username;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['email'] = $email;
            $_SESSION['usermode'] = $usermode;
            $_SESSION['active'] = $active_status;
            
            //existing bug only showing 1 league
            //$_SESSION['leagues'] = $league_id;
            @ $sth->close();
			
            setSessionActiveLeague($id, '', $ref_status_text);
            writeDataToFile(" setSessionActiveLeague($id, '', $ref_status_text);", __FILE__, __LINE__);
            setSessionActiveWeek();
            if ($usermode = 'admin') {
               setSessionReferenceMode('on');
            } else {
               setSessionReferenceMode();
            }
			
			//change password
			if($passwordNeedsToChange===1)
			{
				$_SESSION['login'] = 'updatepassword';
			}
               
            
         } elseif ($sth->errno !== 0) {
            recordLogin('dberror', $ref_loginhistory_id);
            formatSessionMessage("A system error occurred. Login failed.  Please try again or contact the site administrator.  (ref:$ref_loginhistory_id)", 'danger', $msg);
            setSessionMessage($msg, 'login');
            $_SESSION['login'] = 'dberror';
            @ $sth->close();
            break;
         } else {
            // Don't know this guy OR his password is bad.  Doesn't matter.
            recordLogin('fail', $ref_loginhistory_id);
            $_SESSION['login'] = 'fail';
            formatSessionMessage('Login failed.', 'warning', $msg);
            setSessionMessage($msg, 'login');
            @ $sth->close();
            break;
         }
      } catch (mysqli_sql_exception $e) {
         formatSessionMessage("A system error occurred. Login failed.  Please try again or contact the site administrator. (ref:ex$ref_loginhistory_id)", 'danger', $msg);
         setSessionMessage($msg, 'login');
         $ermsg = "login()  \n" .
            'sql: ' . $mysql . " \n\n" .
            'username ' . $user_name . " \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         $_SESSION['login'] = 'dberror';
         break;
      }
      $login_status = true;
      break;
   }
   $pr = print_r($_SESSION, true);
   //writeDataToFile("LOGIN COMPLETE......" . $pr, __FILE__, __LINE__);
   
   $login_message = '';
   if ($login_status && isLoginShowMessage($login_message)){
      formatSessionMessage("<b>SITE MESSAGE</b><br />The site administrator has issued the following message for users:
         <br /><br />" . $login_message, 'infonoheader', $msg);
      setSessionMessage($msg, 'login'); 
   }
   
   return $login_status;
}


function login(
   $user_name,  // or email address
   $password
){

   return login2($user_name, $password, true);
}

function isValidLeageId(
   $league_id,
   $ref_status_text = ''   // leaguedoesnotexist multipleleaguesexist
){

   $mysql = "
      select count(*)
        from league
       where league_id = ?
         and active = 1";

   $status = 0;
   $count = 0;
   while (1) {
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("i", $league_id);
         $sth->execute();
         $sth->bind_result($count);
         $sth->fetch();
      } catch (mysqli_sql_exception $e) {
         $ermsg = 'isValidLeageId()  \n' .
            'sql: ' . $mysql . "\n\n" .
            'user ' . $user_info . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
writeDataToFile("isvalidleague count = " . $count, __FILE__, __LINE__);
      if ($count == 0) {
         $ref_status_text = 'leaguedoesnotexist';
         break;
      } elseif ($count == 1) {
         $status = 1;
      } else {
         $ref_status_text = 'multipleleaguesexist';         
      }
      break;
   }
   @$sth->close();
   return $status;
}

function isCommissionerWithScope(
   $user_id,
   $league_id = '',
   $ref_status_text = ''
){

   $mysql = '';
   if ($league_id) {
      $mysql = "
         select count(*) 
           from league
         where commissioner = ?
           and league_id = ?
           and active = 1
         limit 1";
   } else {
      $mysql = "
         select count(*) 
           from league
         where commissioner = ?
           and active = 1
         limit 1";
   }
     
   
   writeDataToFile("called isCommissionerWithScope($user_id,$league_id ", __FILE__, __LINE__);
   
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   $status = 0;
   $count = -1;
   while (1) {
      
      try {
         $count = '';
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         if ($league_id) {
            $sth->bind_param("ii", $user_id, $league_id);
         } else {
            $sth->bind_param("i", $user_id);
         }
         $sth->execute();
         $sth->bind_result($count);
         $sth->fetch();
         @$sth->close();
         writeDataToFile("isCommissionerWithScope count: $count", __FILE__, __LINE__);
      } catch (mysqli_sql_exception $e) {
         $ermsg = "isCommissionerWithScope()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'user_id: ' . $user_id . "\n" .
            'league_id: ' . $user_id . "\n" .
            'count: ' . $count . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      if ($count == 0) {
         $ref_status_text = 'notcommissioner';
      } elseif ($count == 1) {
         $status = 1;
      }
      break;
   }
   return $status;
}


// Check everything again.  Hot link issues.
function updateUser(
   $user_id,
   $username,  // This may be a new edit.  Make sure someone else doesn't own it.
   $fname,
   $lname,
   $useremail,
   $usermode,  // also seen as usertype; user, admin ONLY 
   $activestatus,
   &$ref_status_text = ''  // notunique, emailnotvalid, 
){

   $mysql_update = "
      UPDATE users 
         SET username = ?,
             fname = ?,
             lname = ?,
             email = ?,
             usermode = ?,
             active_status = ?
       WHERE id = ?";
       
       
   // $env = "updateUser()  \n" .
   //    'sql: ' . $mysql_update . "\n\n" .
   //    '$username     = ' . $username       . ", \n" .
   //    '$fname        = ' . $fname          . ", \n" .
   //    '$lname        = ' . $lname          . ", \n" .
   //    '$useremail    = ' . $useremail      . ", \n" .
   //    '$usermode     = ' . $usermode       . ", \n" .
   //    '$activestatus = ' . $activestatus   . ", \n" .
   //    '$user_id      = ' . $user_id        . ", \n";
   // writeDataToFile($env, __FILE__, __LINE__);            
       
   $status = 0;
   while (1) {
      
      if (!isValidUserMode($usermode)) {
         $ref_status_text = 'usermodenotvalid';
         break;
      }
      writeDataToFile("isUserNameAvailable with: '$user_id', '$username', '$ref_status_text'", __FILE__, __LINE__);
      if (!isUserNameAvailable($user_id, $username, $ref_status_text)) {
         $ref_status_text = 'usernamenotavailable';
         break;
      }
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_update);
         $sth->bind_param("sssssii",
                  $username,
                  $fname,
                  $lname,
                  $useremail,
                  $usermode,
                  $activestatus,
                  $user_id);         
         if (!$sth->execute()) {
            $ref_status_text = 'updatefail';
            break;
         }
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "updateUser()  \n" .
            'sql: ' . $mysql_update . "\n\n" .
            '$username     = ' . $username       . ", \n" .
            '$fname        = ' . $fname          . ", \n" .
            '$lname        = ' . $lname          . ", \n" .
            '$useremail    = ' . $useremail      . ", \n" .
            '$usermode     = ' . $usermode       . ", \n" .
            '$activestatus = ' . $activestatus   . ", \n" .
            '$user_id      = ' . $user_id        . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      @$sth->close();
      $status = 1;
      break;
   }
   return $status;
}

function isUserNameAvailable(
   $user_id,               // (i) The ID of the user wishing to change his name
   $user_name,             // (i) The new name desired
   $ref_status_text = ''
){

       
   $mysql_chkdupname = "
      select id
        from users
       where username = ?";
       
   $is_name_available = 0;
   $id_of_current_name_owner = 0;
   while (1) {
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      $userid_of_current_username = '';
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_chkdupname);
         $sth->bind_param("s", $user_name);
         $sth->execute();
         $sth->bind_result($id_of_current_name_owner);
         $sth->fetch();
         if ($id_of_current_name_owner != $user_id) {  // If IDs don't match then somebody already owns the name
            $ref_status_text = 'usernameinuse';
            break;
         }
         $is_name_available = 1;
      } catch (mysqli_sql_exception $e) {
         $ermsg = "isUserNameAvailable()  \n" .
            'sql: ' . $mysql_chkdupname . "\n\n" .
            '$userid = ' . $user_id . ", \n" .
            '$username = ' . $user_name  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   @ $sth->close();
   
   //writeDataToFile("isUserNameAvailable(): \n" .  
   //   "status " . $is_name_available . "\n" . 
   //   "user name " . $user_name . "\n" . 
   //   "user id " . $user_id . "\n" . 
   //   "used by " . $id_of_current_name_owner, __FILE__, __LINE__);
   
   return $is_name_available;
}


function setActiveWeek(
   $week, 
   &$ref_status_text = ''
){

   $mysql_clear = "
      UPDATE active_week 
         SET active_status = 0";
         
   $mysql_set = "
      UPDATE active_week 
         SET active_status = 1
       WHERE week = ?";
         
   $status = 0;
   $_SESSION['active_week'] = 0;
   while (1) {
      
      if ($week < 1 || $week > NFL_LAST_WEEK) {
         $ref_status_text = 'weekoutofbounds';
         break;
      }
      
      if (!validateUser('admin', 'status')) {
         // only admin is allowed to update site active week
         $ref_status_text = 'notadmin';
         break;
      }
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         
         $sth = $conn->prepare($mysql_clear);
         if (!$sth->execute()) {
            $ref_status_text = 'updatefail'; // TODO Can this be thrown?
            @ $sth->close();
            break;
         }
         
         $sth = $conn->prepare($mysql_set);
         $sth->bind_param("i", $week);
         if (!$sth->execute()) {
            $ref_status_text = 'updatefail'; // TODO Can this be thrown?
            @ $sth->close();
            break;
         }
         $sth->close();
         
      } catch (mysqli_sql_exception $e) {
         $ermsg = "setActiveWeek()  \n" .
            'sql set: ' . $mysql_set . "\n\n" .
            '$week = ' . $week . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      $_SESSION['active_week'] = $week;
      $status = 1;
      break;
   }
   return $status;
}

// If nobody is logged in...
function getActiveWeek(
){

   $mysql = "
      select week
        from active_week
      where active_status = 1";
      
   $week = 0;
   $active_week = 0;
   while (1) {
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         
         $sth = $conn->prepare($mysql);
         if (!$sth->execute()) {
            $ref_status_text = 'dberror';
            @ $sth->close();
            break;
         }
         
         $sth->bind_result($week);
         
         if (!$sth->fetch()) {
            $ref_status_text = 'noactiveweek';
            @ $sth->close();
            break;
         }
         @ $sth->close();
         
      } catch (mysqli_sql_exception $e) {
         $ermsg = "setActiveWeek()  \n" .
            'sql set: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $active_week = $week;
      break;
   }
   return $active_week;
}



function getLeagueType(
   $league_id
){

   $mysql = "
      select league_type
        from league
       where league_id = ?
         and active = 1";
       
   $league_type = '';
   while (1) {
     
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("i", $league_id);
         $sth->execute();
         $sth->bind_result($league_type);
         $sth->fetch();
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getLeagueType()  \n" .
            'sql: ' . $mysql . "\n\n" .
            '$league_id = ' . $league_id  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      $status = 1;
      @ $sth->close();
      break;
   }
   @ $sth->close();
   return $league_type;
}


function getLeagueName(
   $league_id
){

   $mysql = "
      select league_name
        from league
       where league_id = ?
         and active = 1";
       
   $league_name = '';
   while (1) {
     
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("i", $league_id);
         $sth->execute();
         $sth->bind_result($league_name);
         $sth->fetch();
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getLeagueName()  \n" .
            'sql: ' . $mysql . "\n\n" .
            '$league_id = ' . $league_id  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   return $league_name;
}

function updateScheduleWeek(
   &$adata,    // ref not required.  used to kill copy overhead
   &$ref_status_text = ''
){

   $mysql_update = "
      UPDATE schedules 
         SET gametime = timestamp(?),
             home = ?,
             away = ?,
             spread = ?,
             homescore = ?,
             awayscore = ?
       WHERE schedule_id = ?";

   $status = 0;
   $ref_status_text = '';
   while (1) {
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_update);
         $j = count($adata['gametime']);
         for($i = 0; $i<$j; $i++) {
            $gt = $adata['gametime'][$i];
            $h =  $adata['home'][$i];
            $a =  $adata['away'][$i];
            $s =  $adata['spread'][$i];
            $hs = (isset($adata['homescore'][$i])) ? trim($adata['homescore'][$i]) : NULL ;
            $as = (isset($adata['awayscore'][$i])) ? trim($adata['awayscore'][$i]) : NULL ;
            $id = $adata['id'][$i];
            
            if (!$hs && $hs === '') {
               $hs = NULL;
            }
            if (!$as && $as === '') {
               $as = NULL;
            }
                  
            $home_score_null = ($hs === NULL  ) ? 1 : 0;
            $home_score_empty = ($hs === ''  ) ? 1 : 0;
            $home_score_zero  = ($hs == 0 ) ? 1 : 0;
            $home_score_false = ($hs === false  ) ? 1 : 0;
            
            writeDataToFile("
            hs                 '$hs'
            home_score_null    '$home_score_null 
            home_score_empty   '$home_score_empty
            home_score_zero    '$home_score_zero 
            home_score_false   '$home_score_false", __FILE__, __LINE__);
            
            $sth->bind_param("sssdiii", $gt, $h, $a, $s, $hs, $as, $id);
            if (!$sth->execute()) {
               writeDataToFile($sth->error, __FILE__, __LINE__);
            }
         }
         $status = 1;
      } catch (mysqli_sql_exception $e) {
         $ermsg = "updateScheduleWeek()  \n" .
            'sql: ' . $mysql_update . "\n\n" .
            'schedule array = ' . print_r($adata, true)  . ", \n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   return $status;
}



function setSessionActiveLeague(
   $user_id,
   $active_league_id = '', // an empty id behaves USEFIRST
   &$ref_status_text = ''
){

   writeDataToFile("setSessionActiveLeague( user_id $user_id, active_league_id $active_league_id", __FILE__, __LINE__);

   $mysql = '
      select L.league_id,
             L.league_name,
             L.commissioner,
             L.league_type,
             L.league_points,
             L.league_picks,
             L.league_push,
             L.firstround,
             L.lastround,
             p.playername,
             L.active
        from league as L
   left join nspx_leagueplayer as p on p.leagueid = L.league_id
       where L.active = 1
         and p.active = 2
         and p.userid = ?
         and L.league_id = ?';

   // An attempt to change a league deletes current.
   // If it fails, the env should be considered unknown and other
   // actions should fail on empty data.
   //$_SESSION['active'] =        '';  // this is about the user, not league
   $_SESSION['league_id'] =     '';
   $_SESSION['league_name'] =   '';
   $_SESSION['commissioner'] =  '';
   $_SESSION['league_type'] =   '';
   $_SESSION['league_points'] = '';
   $_SESSION['league_picks'] =  '';
   $_SESSION['league_push'] =   '';  
   $_SESSION['league_admin'] =  '';
   $_SESSION['league_player_name'] =  ''; 
   $_SESSION['league_firstround'] =  ''; 
   $_SESSION['league_lastround'] =  ''; 
   $_SESSION['leagueactive'] =  ''; 
   $_SESSION['leagues'] =       '';
   $_SESSION['league_test'] =   array();
   $_SESSION['allmyleagues'] =  array();
   
   if (!$active_league_id) {
      $active_league_id = 'USEFIRST';
   }
   
   $status = 0;
   $ref_status_text = '';
   while (1) {
      
      if ($active_league_id == '') {
         $ref_status_text = 'noparamleagueid';
         break;
      }
      if ($user_id != $_SESSION['user_id']) {
         $ref_status_text = 'notcurrentuserid';
         break;
      }
      // get the --1--4--56-- thing
      if(!getUserLeagueMemberships($user_id, $ref_leagues_string)) {
         $ref_status_text = 'nomemberships';
         break;
      }
      
      if(buildLeaguesArrayNsp($ref_leagues_string, $ref_leagues_array, $ref_test_leagues_array, $ref_active_leagues_string)) {
         $_SESSION['allmyleagues'] = $ref_leagues_array;
         $_SESSION['league_test'] = $ref_test_leagues_array;
         $_SESSION['leagues'] = $ref_active_leagues_string;  // bogus - has every league the guy has ever belonged to, active or not
      } else {
         $ref_status_text = 'buildLeaguesArrayNsp() fail';
         break;
      }
      $first_index = (!empty($_SESSION['league_test'][0])) ? $_SESSION['league_test'][0] : '';
      //writeDataToFile("buildLeaguesArrayNsp($ref_leagues_string, the first index '$first_index' " . print_r($ref_leagues_array, true) . "\n and test: " . print_r($ref_test_leagues_array, true), __FILE__, __LINE__);

      if ($active_league_id == 'USEFIRST') {
         if (!$active_league_id = getUserLastLeagueSelect($user_id)) {
            if (!empty($_SESSION['league_test'][0])) {
               $active_league_id = $_SESSION['league_test'][0];
            } else {
               $ref_status_text = 'noleague-2299';
               break;
            }
         }
      }
      
      if (!isLeagueMember($user_id, $active_league_id, $ref_status_text)) {
         $ref_status_text = 'notleaguemember';
         unset($_SESSION['allmyleagues'], $_SESSION['league_test'], $_SESSION['leagues']); // had to set to accomodate 'USEFIRST' 
         break;   // notleaguemember
      }
         
      try {
         $driver = new mysqli_driver();
         $driver->report_mode = MYSQLI_REPORT_STRICT;
         
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("ii", $user_id, $active_league_id);
         $sth->execute();
         $sth->bind_result(
            $league_id,
            $league_name,
            $commissioner,
            $league_type,
            $league_points,
            $league_picks,
            $league_push,
            $league_firstround,
            $league_lastround,
            $player_name,
            $league_active);
         
         if ($sth->fetch()) {
            $_SESSION['league_id'] =     $league_id;
            $_SESSION['league_name'] =   $league_name;
            $_SESSION['commissioner'] =  $commissioner;
            $_SESSION['league_type'] =   $league_type;
            $_SESSION['league_points'] = $league_points;
            $_SESSION['league_picks'] =  $league_picks;
            $_SESSION['league_push'] =   $league_push; 
            $_SESSION['league_player_name'] = $player_name; 
            $_SESSION['league_firstround'] =  $league_firstround; 
            $_SESSION['league_lastround'] =   $league_lastround;
            $_SESSION['leagueactive'] =  $league_active; 
            
            if($_SESSION['commissioner'] == $user_id) {
               $_SESSION['league_admin'] = $league_id;
            } else {
               $_SESSION['league_admin'] = '';
            }
            $status = 1;
            @ $sth->close();
            break;
         } else {
            $ref_status_text = 'noleague-2353';
            @ $sth->close();
         }
         break;
      } catch (mysqli_sql_exception $e) {
         $ermsg = "setSessionActiveLeague()  \n" .
            'sql: ' . $mysql . " \n\n" .
            'user_id ' . $user_id . " \n" .
            'league_id ' . $active_league_id . " \n" .           
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   //writeDataToFile("setSessionActiveLeague() status '$status' refstatus = '$ref_status_text'", __FILE__, __LINE__);
   return $status;
}

function isActiveLeaguePlayer(
   $user_id,
   $league_id,
   &$ref_status_text = ''
){
   $mysql = "
      select active
        from nspx_leagueplayer
      where userid = ?
        and leagueid = ?";
        
   $active_player = false;  // error
   while (1) {
      $ans = runSql($mysql, array("ii", $user_id, $league_id), 0, $ref_status_text);
      
      if ($ans === false) {
         break;
      }
      if ($ans === null) {
         $active_player == null;
         $ref_status_text = 'null';
         break;
      }
      if (sizeof($ans) != 1) {
         $ref_status_text = 'notone';
         break;
      }
      $active_player = $ans[0]['active'];
      $active_player = ($active_player == 2) ? 1 : 0;  // db table 1,2, functions 0,1
      break;
   }
      
   return $active_player;
}

function isLeagueMember(
   $user_id,
   $change_to_league, 
   &$ref_status_text = ''
){
   // If the projection is 1 verses a field ->num_rows.
   $mysql = "
      select 88 as ismember
        from users
       where id = ?
         and league_id like concat('%-',?,'-%')";
       
   $junk = 0;
   $is_member = false;
   $ref_status_text = '';
   while (1) {
      if (!$ans = runSql($mysql, array("ii",$user_id, $change_to_league), 0, $ref_status_text)) {
         if ($ans === false) {
            break;
         }
         if ($ans === null) {
            $is_member = null;
            break;
         }
      }

      if (sizeof($ans) == 1) {
         $junk = $ans[0]['ismember'];
         $is_member = ($junk == 88) ? true : false;
      }
      
      $status = 1;
      break;
   }
   return $is_member;
}

function getLeagueActiveWeek(
   $league_id, 
   &$ref_status_text = ''
){
   return $_SESSION['active_week'];
}

function isPlayerNameAvailable(
   $user_id,
   $league_id, 
   $player_name,
   &$ref_status_text = ''
){

       
   $mysql_chkdupname = "
      select userid
        from nspx_leagueplayer
       where playername = ?
         and leagueid = ?";
       
   $is_name_available = 0;
   $id_of_current_name_owner = 0;
   while (1) {
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      $userid_of_current_username = '';
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_chkdupname);
         $sth->bind_param("si", $player_name, $league_id);
         $sth->execute();
         $sth->bind_result($id_of_current_name_owner);
         if (!$sth->fetch()) {
            $is_name_available = 1;
            @ $sth->close();
            break;
         }
         
         @ $sth->close();
         if ($id_of_current_name_owner != $user_id) {  // If IDs don't match then somebody already owns the name
            $ref_status_text = 'usernameinuse';
            break;
         }
         $is_name_available = 1;
         $ref_status_text = 'usedbyself';
         
      } catch (mysqli_sql_exception $e) {
         $ermsg = "isPlayerNameAvailable()  \n" .
            'sql: ' . $mysql_chkdupname . "\n\n" .
            '$userid = ' . $user_id . ", \n" .
            '$playername = ' . $player_name  . ", \n" .
            '$leagueid = ' . $league_id  . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   return $is_name_available;
}

function getUserLeagueMemberships(
   $user_id,
   &$ref_leagues = '',
   &$ref_status_text = ''
){

   //$mysql = "
   //   select league_id
   //     from users
   //    where id = ?";
       
   $mysql = "
      select leagueid
        from nspx_leagueplayer
       where userid = ?
         and active = 2
    order by leagueid";
       
   $status = 0;
   $ref_leagues = '';
   $ref_status_text = '';
   while (1) {
      
      if (!$user_id) {
         $ref_status_text = 'noparam';
         break;
      }
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      $userid_of_current_username = '';
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("i", $user_id);
         $sth->execute();
         $sth->bind_result($league_membership_id);
         
         $ref_status_text = 'nouser';
         while ($sth->fetch()) {
            $ref_status_text = '';
            $ref_leagues .= '-' . $league_membership_id . '-';
         }
         
         if ($ref_status_text == 'nouser') {
            @ $sth->close();
            break;
         }
         
         $status = 1;
         @ $sth->close();
         
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getUserLeagueMemberships()  \n" .
            'sql: ' . $mysql . "\n\n" .
            '$userid = ' . $user_id . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   return $status;
}



function setSessionActiveWeek(
   &$ref_status_text = ''
){

   $mysql = "
      select week
        from active_week
       where active_status = 1";
       
   $active_week = 0;
   $_SESSION['active_week'] = 0;
   while (1) {
     
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->execute();
         $sth->bind_result($active_week);
         $sth->fetch();
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "setSessionActiveWeek()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      if ($active_week > 0 && $active_week < NFL_LAST_WEEK) {
         $_SESSION['active_week'] = $active_week;
      } else {
         $ref_status_text = 'weekoutofbounds';
         $_SESSION['active_week'] = 0;
         $active_week = 0;
      }
         
      break;
   }
   return $active_week;
}
function getUserIDVia(
   $field_value,
   $field = 'email'  // email or username
){

   $mysql_email = "
      select id
        from users
       where active_status = 1
         and email = ?";
         
   $mysql_user_name = "
      select id
        from users
       where active_status = 1
         and user_name = ?";
         
   $mysql = '';
   if ($field == 'email') {
      $mysql = $mysql_email;
   } elseif ($field == 'username') {
      $mysql = $mysql_user_name;
   } else {
      return 0;
   }

   $user_id = 0;
   while (1) {
     
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("s", $field_value);
         $sth->execute();
         $sth->bind_result($user_id);
         $sth->fetch();
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getUserIDVia()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      break;
   }
   return $user_id;
}

function deactivateInvitation(
   $confirm_else_league_id_with_email = '',
   $email = ''
){

   $mysql = "
      update temp_confirm 
         set used = 1, 
         actiondate = now()
       where confirm_code = ?";
       
   $mysql_multiple_invites = "
      update temp_confirm 
         set used = 1, 
          actiondate = now()
       where confirm_email = ?
         and league_id = ?";
         
   if ($email) {
      $mysql = $mysql_multiple_invites;
   }
       
   $status = 0;
   while (1) {
     
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         if ($email) {
            $sth->bind_param("is", $confirm_else_league_id_with_email, $email);
         } else {
            $sth->bind_param("s", $confirm_else_league_id_with_email);
         }
            
         $sth->execute();
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "deactivateInvitation()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'confirm_code: ' . $confirm_code . "\n" .
            'email: ' . $email . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         break;
      }
      $status = 1;
      break;
   }
   return $status;
}

function retireInvitation(
   $confirm_code
){

   $mysql = "
      update temp_confirm 
         set retire = 1,
         actiondate = now()
       where confirm_code = ?";
       
   $status = 0;
   while (1) {
     
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("s", $confirm_else_league_id_with_email);
         if(!$sth->execute()) {
            break;
         }
         $sth->store_result();
         if ($sth->num_rows() != 1) {
            $ref_status_text = 'updatefailonerow';
            $sth->close();
            break;
         }
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "deactivateInvitation()  \n" .
            'sql: ' . $mysql . "\n\n" .
            'confirm_code: ' . $confirm_code . "\n" .
            'email: ' . $email . "\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         break;
      }
      $status = 1;
      break;
   }
   return $status;
}


// Check everything again.  Hot link issues.
function updateLeagueUser(
   $league_id,
   $user_id,
   $player_name,
   $paid,
   $join_date,
   $active_status,
   &$ref_status_text = ''  // notunique, emailnotvalid, 
){

   writeDataToFile("
   'league_id,   ' $league_id,
   'user_id,     ' $user_id,
   'player_name, ' $player_name,
   'paid,        ' $paid,
   'join_date,   ' $join_date,
   'active_status' $active_status,", __FILE__, __LINE__);

   $mysql_update = "
      UPDATE nspx_leagueplayer 
         SET playername = ?,
             active = ?,
             paid = ?,
             joindate = ?
       WHERE userid = ?
         AND leagueid = ?
       LIMIT 1";
       
   $status = 0;
   $ref_status_text = '';
   $update_count = '';
   while (1) {
      
      if (!($paid == 1 || $paid == 2)) {
         $ref_status_text = 'paidoneortwo';
         break;
      }
      if (!($active_status == 1 || $active_status == 2)) {
         $ref_status_text = 'activeoneortwo';
         break;
      }
   writeDataToFile("11 updateLeagueUser() '$ref_status_text', '$status', '$update_count'", __FILE__, __LINE__);
      
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql_update);
         $sth->bind_param("siisii",
                  $player_name,
                  $active_status,
                  $paid,
                  $join_date,
                  $user_id,
                  $league_id);         
         
   writeDataToFile("22 updateLeagueUser() '$ref_status_text', '$status', '$update_count'", __FILE__, __LINE__);
         if (!$sth->execute()) {
            $ref_status_text = 'updatefail';
            break;
         }
         $sth->store_result();
         $update_count = $sth->affected_rows;
         writeDataToFile(" the return count is $update_count", __FILE__, __LINE__);
         if ($update_count === 0) {
            $ref_status_text = 'updatenorows';
            $status = 1;
            break;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = "updateUser()  \n" .
            'sql: ' . $mysql_update . "\n\n" .
            '$league_id      = ' . $league_id     . ", \n" .
            '$player_name    = ' . $player_name   . ", \n" .
            '$user_id        = ' . $user_id       . ", \n" .
            '$active_status  = ' . $active_status . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }
   writeDataToFile("33 updateLeagueUser() '$ref_status_text', '$status', '$update_count'", __FILE__, __LINE__);
   @ $sth->close();
   return $status;
}


// Check everything again.  Hot link issues.
function getEmailCommaList(
   $mode,   // league, site
   &$ref_email_list = '',
   $league_id = '',
   &$ref_status_text = ''  // notunique, emailnotvalid, 
){

   $mysql = '';
   $mysql_league = "
      select u.email
        from nspx_leagueplayer as y, users as u
       where y.userid = u.id
         and y.leagueid = ?
         and u.active_status = 1
         and y.active = 2
    order by u.lname";
    
   $mysql_site = "
      select u.email
        from users as u
       where u.active_status = 1
    order by u.lname";
       
   $status = 0;
   $ref_status_text = '';
   $ref_email_list = '';
   while (1) {
      
      if (!($mode == 'league' || $mode == 'site')) {
         $ref_status_text = 'unknowncommand';
         break;
      }
      if ($mode == 'league') {
         if (!$league_id) {
            $ref_status_text = 'leaguemissing';
            break;
         }
      }
      
      if ($mode == 'league') {
         $mysql = $mysql_league;
      } elseif ($mode == 'site') {
         $mysql = $mysql_site;
      }
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         if ($mode == 'league') {
            $sth->bind_param("i", $league_id);
         }
         if (!$sth->execute()) {
            $ref_status_text = 'updatefail';
            break;
         }
         $sth->bind_result($email);
         while ($sth->fetch()) {
            if (!$ref_email_list) {
               $ref_email_list = $email;
            } else {
               $ref_email_list .= ', ' . $email;
            }
         }
         $sth->close();
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getEmailCommaList()  \n" .
            'sql: ' . $mysql . "\n\n" .
            '$league_id      = ' . $league_id     . ", \n" .
            '$mode      = ' . $mode     . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }
   @ $sth->close();
   return $status;
}

function getCount(   // on count == 0 check the status text
   $mode = '',  // site, league, siteleague
   &$ref_status_text = '',
   $league = ''
){


   $mysql_site = "
      select count(*) 
        from users 
       where active_status = 1";
       
   $mysql_league = "
      select count(*)
        from nspx_leagueplayer
       where leagueid = ?
         and active = 2";
         
   $mysql_siteleague = "
      select count(*)
        from nspx_leagueplayer as y, users as u
       where y.leagueid = ?
         and y.active = 2
         and u.active_status = 1
         and u.id = y.userid";
         
   $status = 0;
   $count = '';
   $ref_status_text = '';
   while (1) {
      
      if (!($mode == 'site' || $mode == 'league'  || $mode == 'siteleague')) {
         $ref_status_text = 'unknownmode';
         break;
      }
      if ($mode != 'site' && !$league_id) {
         $ref_status_text = 'leaguemissing';
         break;
      }
      
      if ($mode == 'site') {
         $mysql = $mysql_site;
      } elseif ($mode == 'league') {
         $mysql = $mysql_league;
      } elseif ($mode == 'siteleague') {
         $mysql = $mysql_siteleague;
      }
         
      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_OFF;
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         if ($mode != 'site') {
            $sth->bind_param("i", $league_id);
         }
         if (!$sth->execute()) {
            $ref_status_text = 'dberror';
            break;
         }
         $sth->bind_result($count);
         if (!$sth->fetch()) {
            $ref_status_text = 'norecords';
            break;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = "getCount()  \n" .
            'sql: ' . $mysql . "\n\n" .
            '$mode      = ' . $moe     . ", \n" .
            '$league_id    = ' . $league_id   . ", \n" .
            '$ref_status_text        = ' . $ref_status_text       . ", \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }
   @ $sth->close();
   return $count;
}


function getLeagueNamesIDsArray(
   &$a_league,
   $active_status = 'both'    // 'active' 'inactive' 'both'
){

   $mysql = "
      select league_id, league_name
        from league
       where active = ?
          or active = ?
    order by league_name";
     
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;
   
   $a_league = array();
   $status = 0;
   while (1) {
      
      $active = -1;
      switch ($active_status) {
      case 'active': $active = 1; $active_two = 1;  break;
      case 'both' :  $active = 1; $active_two = 0;  break;
      case 'inactive' :  $active = 0; $active_two = 0;  break;
      default: // never here
      }
      
      if ($active == -1) {
         break;  
      }
      writeDataToFile("active: '$active', '$active_two'", __FILE__, __LINE__);
      try {
         $count = '';
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("ii", $active, $active_two);
         $sth->execute();
         $sth->bind_result($league_id, $league_name);
         while($sth->fetch()) {
            $status = 1;
            $a_league[$league_name] = $league_id;
         }
      } catch (mysqli_sql_exception $e) {
         $ermsg = 'getLeagueNamesIDsArray()  \n' .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      
      @$sth->close();
      break;
   }
   return $status;
}


function isSiteActive(
){
   // 2 == active
   $mysql = "
      select siteactive
        from nsp_admin
       where site = ?
      limit 1";
     
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;

   $site_active = 0;
   $ref_status_text = '';
   while (1) {
      
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $site_name = ADMIN_TABLE;
         $sth->bind_param("s", $site_name);
         $sth->execute();
         $sth->bind_result($site_status);
         if (!$sth->fetch()) {
            break;
         }
         if ($site_status == 2) {
            $site_active = 1;
         }
         writeDataToFile("'$site_active', '$site_name'", __FILE__, __LINE__);
      } catch (mysqli_sql_exception $e) {
         $ermsg = 'isSiteActive()  \n' .
            'sql: ' . $mysql . "\n\n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
         break;
      }
      $status = 1;
      break;
   }
   @ $sth->close();
   if (!$status) {
      return 0;
   } else {
      return $site_active;
   }
}
function setUserLastLeagueSelected(
   $user_id,
   $league_id,
   &$ref_status_text = ''
){

   $mysql = "
      update users
         set lastleagueselectedid = ?
       where id = ?
       limit 1";
       
   $status = false;
   $ref_status_text = 'means nothing';
   while (1) {
      
      if (!$user_id || !$league_id) {
         $ref_status_text = 'missinginputdata';
         break;
      }
      
      $ans = runSql($mysql, array("ii", $league_id, $user_id), 1, $ref_status_text);
      
      if ($ans === false) {
         $ref_status_text .= "  false $league_id";
         break;
      }
      if ($ans === null) {  // no updates, no erros
         $ref_status_text = 'datasame';
         $status = 1;
         break;
      }
      if ($ans == 1) {
         $ref_status_text = 'updated';
         $status = 1;
         break;
      }
      
      $ref_status_text = 'neverhere';
      break;
   }
   return $status;
}

function checkAndSetActiveWeek(
   &$ref_status_text = ''
){
   $mysql = "
      select week
        from active_week
       where active_status = 1";
       
   $active_week = 0;
   while (1) {

      $ans = runSql($mysql, '', 0, $ref_status_text);
      if (!$ans) {
         $active_week = $ans;
         break;
      }
      if (sizeof($ans) == 1) {
         $_SESSION['active_week'] = $ans[0]['week'];
         $active_week =  $_SESSION['active_week'];
      }
      break;
   }
   return $active_week;
}
function getNoReplyEmailAddress(
   &$ref_status_text = ''
) {
   $mysql = "
      select emailnoreply
        from nsp_admin
       where site = ?";
   $no_reply_address = '';    
   while (1) {
      $site = ADMIN_TABLE;
      if ($site == 'ADMIN_TABLE') {
         break;
      }
      
      $ans = runSql($mysql, array("s", $site), 0, $ref_status_text);
      if (!$ans) {
         break;
      }
      $no_reply_address = $ans[0]['emailnoreply'];
      break;
   }
   return $no_reply_address;
}
function getUserLastLeagueSelect(   // 0..n == league (0 is default field value), null == no record found, false == dberror
   $user_id,
   $ref_status_text = ''
){

   $mysql = "
      select u.lastleagueselectedid as league_id
        from users u, league e
       where u.lastleagueselectedid <> 0
         and u.lastleagueselectedid = e.league_id
         and e.active = 1
         and u.id = ?";
         
   while (1) {
      
      if (!$user_id) {
         $ref_status_text = 'missinginputdata';
         return false;
      }
      
      if (!$ans = runSql($mysql, array("i", $user_id), 0, $ref_status_text)){
         if ($ans === false) {
            break;
         }
         if ($ans === null) {
            return null;
         }
         return false;
      }
      
      if (sizeof($ans) == 1) {
         return $ans[0]['league_id'];
      } else {
         return 0;
      }
      
      break;
   }
}

function getUserEmailAddress(
   $user_id,
   &$ref_status_text = ''
){
  $mysql = "
      select email
        from users
       where id = ?
       limit 1";
       
   $user_email = 0;
   while (1) {

      $ans = runSql($mysql, array("i", $user_id), 0, $ref_status_text);
      if (!$ans) {
         $user_email = $ans;
         break;
      }
      if (sizeof($ans) == 1) {
         $user_email = $ans[0]['email'];
         break;
      }
      break;
   }
   return $user_email;
}
function setSessionReferenceMode(
   $force = '',
   &$ref_status_text = ''
){

   $mysql = "
      select sessionmessagereferencemode
        from nsp_admin
      where site = ?
      limit 1";
        
   $site_name = ADMIN_TABLE;
   if ($site_name == 'ADMIN_TABLE') {
      return false;
   }
   
   if ($force == 'on') {
      $_SESSION['reference'] = 2;
      return true;
   }
   if ($force == 'off') {
      $_SESSION['reference'] = 1;
      return true;
   }
   
   $status = false;
   while (1) {
      $ans = runSql($mysql, array("s", $site_name), 0, $ref_status_text);
      if ($ans === null) {
         $ref_status_text = 'null';
         break;
      }
      if ($ans === false) {
         $ref_status_text = 'false';
         break;
      }
      if ($ans === 0) {
         $ref_status_text = 'zero';
         break;
      }
      if (sizeof($ans) == 1) {
         $_SESSION['reference'] = $ans[0]['sessionmessagereferencemode'];
      }
      $status = true;
      break;
   }
      
   return $status;
}


function getSiteContactToFromAddresses(
   &$contact_to,
   &$contact_from,
   &$ref_status_text = ''
){

   $mysql = "
      select emailtositecontact,
             emailfromsitecontact
        from nsp_admin
       where site = ?";
       
   $contact_to = '';
   $contact_from = ''; 
   $status = 0;
   while (1) {
      $site = ADMIN_TABLE;
      if ($site == 'ADMIN_TABLE') {
         break;
      }
      
      $ans = runSql($mysql, array("s", $site), 0, $ref_status_text);
      if (!$ans) {
         break;
      }
      $contact_to = $ans[0]['emailtositecontact'];
      $contact_from = $ans[0]['emailfromsitecontact'];
      $status = 1;
      break;
   }
   return $status;
}
function getDatabaseTime(
   &$ref_status_text = ''
){

   $time = 0;
   $mysql = "
      select now() as timenow
        from dual";
        
   $ans = runSql($mysql, '', 0, $ref_status_text);
   $time = $ans[0]['timenow'];
   return $time;

}

function checkUserPassword(
   $user_id_or_email,
   $plain_text_password,
   &$ref_status_text = ''
){
   $mysqlpw = "
      select 88 as goodpw
        from users
       where id = ?
         and password = ?";
         
   $mysqlemail = "
      select 88 as goodpw
        from users
       where email = ?
         and password = ?";
   
   $password_hash = hash('sha256', $plain_text_password);
   $ans = runSql($mysqlpw, array("is", $user_id_or_email, $password_hash), 0, $ref_status_text); 
   if (sizeof($ans) == 1) {
      $rtn = $ans[0]['goodpw'];
      if ($rtn == 88) {
         return true;
      }
   }
   
   $ans = runSql($mysqlemail, array("ss", $user_id_or_email, $password_hash), 0, $ref_status_text);
   if (sizeof($ans) == 1) {
      $rtn = $ans[0]['goodpw'];
      if ($rtn == 88) {
         return true;
      }
   }
   
   return false;
}

// pretty much swiped from 
// http://php.net/manual/en/mysqli-stmt.bind-param.php  (canche_x at yahoo dot com )
// http://stackoverflow.com/questions/16236395/bind-param-with-array-of-parameters
// http://php.net/manual/en/mysqli-stmt.bind-param.php

// null = no records found
// false = error
// 1+ = count
function runSql ( // r - null == no records found/updated, false == error, success 0,1,2,3....n
   $mysql,
   $abind_params,
   $close = false,
   &$ref_status_text = ''
){

   $ref_status_text = '';
   $conn = '';
   $sth = '';
   $result = false;
   
   $msg = '';
   
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_OFF;
 
   //ini_set('display_errors', '0');
   
   try {
      
      if (!$conn = db_connect()) {
         $error = '';
         if (isset($conn->error)) {
            $error = $conn->error;
         }
         formatSessionMessage("runSql() db_connect() error", 'danger', $msg, $error);
         setSessionMessage($msg, 'error');
         $ref_status_text = 'dberror';
         return false;
      }
      
      if (@!$sth = $conn->prepare($mysql)) {
         formatSessionMessage("runSql() prepare() error", 'danger', $msg, $conn->error);
         setSessionMessage($msg, 'error');
         $ref_status_text = 'dberror';
         (!empty($sth) && @$sth->close());
         @$conn->close();
         return false;
      }
      
      if ($abind_params != '') {
         if (@!call_user_func_array(array($sth, 'bind_param'), refValues($abind_params))) {
            $error = '';
            if (isset($sth->error)) {
               $error = $sth->error;
            } else if (isset($conn->error)) {
               $error = $conn->error;
            }
            formatSessionMessage("runSql() bind_param() error", 'danger', $msg, "rs-3205 $error");
            setSessionMessage($msg, 'error');
            $ref_status_text = 'dberror';
            (!empty($sth) && @$sth->close());
            @$conn->close();
            return false;
         }
      }
      
      if (@!$sth->execute()) {
         formatSessionMessage("runSql() execute() error: ", 'danger', $msg, $sth->error);
         setSessionMessage($msg, 'error');
         $ref_status_text = 'dberror';
         @$sth->close();
         @$conn->close();
         return false;
      }
      
      // ... right from the manual
      // An integer greater than zero indicates the number of rows affected or retrieved. 
      // Zero indicates that no records were updated for an UPDATE statement, no rows 
      // matched the WHERE clause in the query or that no query has yet been executed.
      // -1 indicates that the query returned an error. 
      
      if($close){  // update action
         $result = $sth->affected_rows;
         if ($result === -1) {            // There was an error.  It didn't execute.
            $ref_status_text = 'error';
            return false;
         } else if ($result === false) {  // This isn't spec'd but test anyway.
            $ref_status_text = 'false';
            return false;
         } else if ($result === 0) {      // No record was found.  No error.
            $ref_status_text = 'zero';
            return null;
         } else {
            $ref_status_text = 'number';  // Record was updated
            return $result;
         }
      } else {
         $meta = $sth->result_metadata();
         
         // http://us2.php.net/manual/en/mysqli-result.fetch-field.php
         // Returns the definition of one column of a result set as an object. Call 
         // this function repeatedly to retrieve information about all columns in the result set. 
         while ( $field = $meta->fetch_field() ) {
             $parameters[] = &$row[$field->name];
         } 
      
         if (!$rtn = call_user_func_array(array($sth, 'bind_result'), refValues($parameters))) {
            if (isset($sth->error)) {
               formatSessionMessage("runSql() call_user_func_array() bind_result error: ", 'danger', $msg, "mpdb-3253 " . $sth->error);
               setSessionMessage($msg, 'error');
               $ref_status_text = 'dberror';
               @$sth->close();
               @$conn->close();
               return false;
            } elseif ($rtn === false) {
               formatSessionMessage("runSql() call_user_func_array() false ", 'danger', $msg, "mpdb-3260");
               setSessionMessage($msg, 'error');
               $ref_status_text = 'dberror';
               @$sth->close();
               @$conn->close();
               return false;
            }
            formatSessionMessage("runSql() call_user_func_array() unknown error ", 'danger', $msg, 'mpdb-3267');
            setSessionMessage($msg, 'error');
            $ref_status_text = 'dberror';
            @$sth->close();
            @$conn->close();
            return false;
         }
         
         while ( $sth->fetch() ) { 
            $x = array(); 
            foreach( $row as $key => $val ) { 
               $x[$key] = $val; 
            } 
            $results[] = $x; 
         }
         $result = (isset($results)) ? $results : null;  // Nothing?  Returns null;
      }

   } catch (mysqli_sql_exception $e) {
      $ermsg = "
         runSql() mysqli_sql_exception \n
         sql: $mysql \n\n
         MYSQL ERROR TO STRING: " . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
   }  catch (exception $e) {
      $ermsg = "
         runSql() exception \n
         sql: $mysql \n\n
         exception: " . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
   }
   
   @ $sth->close();
   @ $conn->close();
   
   return  $result;

}

// runSql support
function refValues($arr){
   if (strnatcmp(phpversion(),'5.3') >= 0) {//Reference is required for PHP 5.3+
      $refs = array();
      foreach($arr as $key => $value) {
          $refs[$key] = &$arr[$key];
      }
      return $refs;
   }
   return $arr;
}

function getLeaguePassword(
   $league_id,
   &$password_enabled,  // This may be a new edit.  Make sure someone else doesn't own it.
   &$password,
   &$ref_status_text = ''
){

   $mysql = "
      select password_join_enabled,
             password
        from league
       where league_id = ?
       limit 1";
       
   $password_enabled = FALSE;
   $password= ''; 
   $status = 0;
   while (1) {
      
      $ans = runSql($mysql, array("s", $league_id), 0, $ref_status_text);
      if (!$ans) {
         break;
      }
      $password_enabled = $ans[0]['password_join_enabled'];
      $password = $ans[0]['password'];
      $status = 1;
      break;
   }
   return $status;
}

function updateLeaguePassword(
   $league_id,
   $password_enabled, 
   $password
){

   $mysql_update = "
      UPDATE league 
         SET password_join_enabled = ?,
             password = ?
       WHERE league_id = ?";
       
   $status = 0;

           
  $driver = new mysqli_driver();
  $driver->report_mode = MYSQLI_REPORT_OFF;
  
  try {
     $conn = db_connect();
     $sth = $conn->prepare($mysql_update);
     $sth->bind_param("isi",
              $password_enabled,
              $password,
              $league_id);         
     if (!$sth->execute()) {
        $ref_status_text = 'updatefail';
        break;
     }
     $sth->close();
  } catch (mysqli_sql_exception $e) {
     $ermsg = "updateUser()  \n" .
        'sql: ' . $mysql_update . "\n\n" .
        '$password_enabled = ' . $password_enabled   . ", \n" .
        '$league_id        = ' . $league_id          . ", \n" .
        'MYSQL ERROR TO STRING: ' . $e->__toString();
     writeDataToFile($ermsg, __FILE__, __LINE__);
     $ref_status_text = 'dberror';
     break;
  }
  
  @$sth->close();

  return $status;
}


?>