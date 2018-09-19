<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: index.php
   date: jul 17, 2016
 author: original
   desc: This is the home page.  Defined in mypick_def.php as
      URL_HOME_PAGE.  Access is by browser address.  The top menu
      'Home' links.

   note:

*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
require_once 'mypicks_once.php';
$msg = '';
$dev_messages = false;

// This page doesn't require validateUser()
$stat = validateUser('user', 'status');
$league_id = (isset($_SESSION['league_id'])) ? $_SESSION['league_id'] : 'index';

writeDataToFile("SESSION: " . print_r($_SESSION, true), __FILE__, __LINE__);

do_header('MySuperPicks.com - Home');
do_nav();

if (validateUser('user', 'status')) {
      echo "<br /><br /><br />";
}

   //echo "admin table: " . ADMIN_TABLE . "<br />";
   //echo "controller: " . print_r($_SERVER) . "<br />";
   // <a href='load_2016_schedule.php?dowhat=2016'> Load 2016 schedule (no touch scores, no touch spreads)</a><br />
   //echo "&nbsp;&nbsp;&nbsp; <a href='load_2016_schedule.php?dowhat=dev'> Load dev schedule (no touch scores, no touch spreads)</a><br />";

?>
   <noscript>
       <div style='text-align:center;color:red;'><b>You must have javascript (cookies) enabled to play.</b></div>
   </noscript>

   <div class="container containeraddbreaks maincontent">
   	   <div class="jumbotron" id="home_page"></div>
<?php
echoSessionMessage(1);
?>
      <div class="hidden-sm hidden-md hidden-lg">
      </div>
<?php echo get_text2($league_id);  // title == league_id number.  If not, it renders 'index' AND the fancy dialog to start a league
?>
   <br />
   <div class="text-center">
      <button type="button" id="IDb_startleague" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#myModal">
         Start Your League <span class="glyphicon glyphicon-arrow-right"></span>
      </button>
      <button type="button" id="IDb_joinleague" class="btn btn-default btn-lg" data-toggle="modal" data-target="#joinLeageModal">
         Join A League <span class="glyphicon glyphicon-arrow-right"></span>
      </button>
   </div>
   <br />
   <br />
   <br />
   </div>
<?php
do_footer('bottom');
?>
