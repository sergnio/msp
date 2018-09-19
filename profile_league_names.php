<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: index.php
   date: apr-2016
 author: original
   desc: Tbis is the home page.  Defined in mypick_def.php as
      URL_HOME_PAGE
marbles: 
   note: Any changes to accomodate dev will be marked with a TODO.
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

validateUser();
$user_id = $_SESSION['user_id'];

do_header('MySuperPicks.com - Profile - Leagues');
do_nav();
?>

<div class="container">
<?php echo_container_breaks(); ?>
   <div class="hidden-sm hidden-md hidden-lg">
      <br />
      <br />
      <br />
   </div>

<?php

// Every user in each league must have a player name.  I will default to the
// login name if not defined.

$mysql = "
   select e.league_name, e.league_id,
          (case
             when e.league_type = 0 then '-0-'
             when e.league_type = 1 then 'Pick\'em'
             when e.league_type = 2 then 'Survivor'
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
      $sth->bind_result($league_name, $league_id, $league_type, $player_name);
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
   $stable .= "         <th>Type</th>\n";
   $stable .= "         <th>Player Name</th>\n";
   $stable .= "      </tr>\n";
   $stable .= "   </thead>\n";
   $stable .= "   <tbody id='IDtb_leaguenames' userid='$user_id'>\n";

   while($sth->fetch()) { 
      $stable .= "      <tr >\n";
      $stable .= "         <td>$league_name</td>\n";
      $stable .= "         <td>$league_type</td>\n";
      $stable .= "         <td userid='$user_id' leagueid='$league_id'><input id='IDi_playername' name='playnameinput' value='$player_name'></input></td>\n";
      $stable .= "         <td><button type='button' class='btn' name='editbutton'style='width:100px;'>Edit</button></td>\n";
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