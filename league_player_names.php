<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: leage_player_names.php
   date: apr-2016
 author: original
   desc: Tbis is the home page.  Defined in mypick_def.php as
      URL_HOME_PAGE
marbles: 
   note: Any changes to accommodate dev will be marked with a TODO.
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser();

$user_id = $_SESSION['user_id'];

do_header('MySuperPicks.com - Profile - Leagues'); 
do_nav();
?>

<div class="container">
<?php 
echo_container_breaks();
echoSessionMessage();
?>
   <div class="hidden-sm hidden-md hidden-lg">
      <br />
      <br />
      <br />
   </div>
   <h3 >My League Player Names</h3>
   <button id='IDb_lockbutton' class='btn' status='LOCKED' style='width:100px;'>UNLOCK</button> 
   <br />
   <br />
<?php

// Every user in each league must have a player name.  I will default to the
// login name if not defined.

$mysql = "
   select e.league_name, e.league_id, 
          if (e.active = 1, 'active', 'closed'),
          (case
             when e.league_type = 0 then '-0-'
             when e.league_type = 1 then 'Pick\'em'
             when e.league_type = 2 then 'Survivor - cohort'
             when e.league_type = 3 then 'Survivor - last man'
             else 'unknown league'
          end) as leatype,
          p.playername
     from nspx_leagueplayer p
left join league e on e.league_id = p.leagueid
   where p.userid = ?
     and e.league_name is not null
order by e.league_name";  // not null ... the database is broken.
     

$status = 0;
$status_msg = '';
$refstatus_text = '';
while (1) {
   
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_STRICT;   //lower threshold
   //$driver->report_mode = MYSQLI_REPORT_ALL;   //lower threshold

   $league_count = -1;
   $sth = '';
   try {
      $conn = db_connect();
      $sth = $conn->prepare($mysql);
      $sth->bind_param("i", $user_id);
      $sth->execute();
      $sth->bind_result($league_name, $league_id, $league_status, $league_type, $player_name);
   } catch (mysqli_sql_exceSption $e) {
      $ermsg = "profile_league_names.php)  \n".
         ' sql: ' . $mysql . "\n\n" .
         ' user_id: ' . $user_id . "\n" .
         ' MYSQL ERROR TO STRING: ' . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'dberror';
      break;
   }

   $stable = "\n";
   $stable .= "<table id=\"single\" class=\"table table-hover table-striped\">\n";
   $stable .= "   <thead>\n";
   $stable .= "      <tr>\n";
   $stable .= "         <th>League</th>\n";
   $stable .= "         <th>Status</th>\n";
   $stable .= "         <th>Type</th>\n";
   $stable .= "         <th>Player Name</th>\n";
   $stable .= "      </tr>\n";
   $stable .= "   </thead>\n";
   $stable .= "   <tbody id='IDtb_leaguenames' userid='$user_id'>\n";

   // the event element (the button) has all the id attributes to help jquery find input required.
   $row_count = 0;
   while($sth->fetch()) { 
      $row_count++;
      $input_id = "IDi_$row_count";
      $row_id = "IDr_$row_count";
      $stable .= "      <tr id='$row_id'>\n";
      $stable .= "         <td>$league_name</td>\n";
      $stable .= "         <td>$league_status</td>\n";
      $stable .= "         <td>$league_type</td>\n";
      $stable .= "         <td><input id='$input_id' name='playnameinput' value='$player_name' disabled leagueid='$league_id' userid='$user_id'/></td>\n";
      $stable .= "         <td><button type='button' class='btn btn-default' name='editbutton' style='width:100px;' myinputid='$input_id'>Edit</button></td>\n";
      $stable .= "      </tr>\n";      
   }

   $stable .= "   </tbody>\n";
   $stable .= "</table>\n";
   $stable .= "</div>\n";

   echo $stable;
      
   break;
}

do_footer('bottom');
?>