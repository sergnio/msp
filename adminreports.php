<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: adminreports.php 
   date: july-2016
 author: hugh shedd
   desc: 
marbles: 
   note:
*/


require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

validateUser('admin');


do_header('MySuperPicks.com - Admin Reports');
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
   <a href='rpt_confirm_table.php'>Confirm table</a><br />
   <a href='rpt_leagues.php'>Active Leagues (and all players)</a><br />
   <a href='rpt_users.php'>Users and Active Leagues</a><br />
   <br/>
   <br/>
   <b>The following scheduleling tools will be disabled on Sept 15.  That's
   a good thing.</b>
   <br />
   <br />
   Installing the schedule changes the date and game time.  No lines <br />
   are touched.  No scores are touched.  Running this at anytime     <br />
   will not cause any problems.<br />
   <span style='color:red;'> I KNOW!  These are all eastern time schedules.</span><br />
   The shedule loader converts EDT -> PDT. - Hugh 9/6/2016 2pm<br />
   EXECUTE LINK-> <a href='load_2016_schedule.php?dowhat=2016'>Install the NFL 2016 Schedule</a>
      <br />
      <br />
   This link will <span style='color:red;font-weight:bold;'>NULL ALL SCORES</span>.  It will not touch schedule  <br />
   dates, game times or points.                                 <br />
   EXECUTE LINK-> <span style='font-weight:bold;'><a href='load_2016_schedule.php?dowhat=zero'>Null all Game Scores</a><br /></span>
<?php
do_footer('clean');
?>

