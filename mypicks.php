<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: mypicks.php
   date: apr-2016
 author:  
   desc: 
   
marbles: 
  
*/
require_once 'mypicks_def.php';
require_once 'mypicks_db.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser();

$week = (isset($_SESSION['active_week'])) ? $_SESSION['active_week'] : '';
$league_name = (isset($_SESSION['league_name'])) ? $_SESSION['league_name'] : '';

$status = 0;
while (1) {
   if (!$league_name) { 
      formatSessionMessage("You must be associated with a league to access My Picks.", 'info', $msg,
         "mp-38");
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$week || !$league_name) {
      formatSessionMessage("Your picks cannot be displayed at this time", 'warning', $msg, "mp-43 '$week'$league_name'");
      setSessionMessage($msg, 'error');
      break;
   }
   $status = 1;
   break;
}

if (!$status) {
   header("Location: index.php");
   die();
}
     
      
do_header('MySuperPicks.com - Home');
do_nav();
echo "<div class='container'>";
echoContainerBreaks();
echoSessionMessage();
?>
    
<div class="hidden-sm hidden-md hidden-lg">
    <br />
    <br />
    <br />
</div>
        <h1 class="text-center">My Picks - Week <?php echo $week; ?></h1>
        
<?php 
        echo " <p class='lead muted text-center'>Need to add to or change your picks?  Edit them in <b>This Week's Lines</b> page.</p>";
        echo " <p class='lead muted text-center'>The spread 
                  <a href='#' rel='tooltip' data-placement='right' 
title='How do point spreads work? 

The team with the minus sign (e.g. -3) is the favorite and is \"giving\" 3 points. They 
must win the game by more than 3 points to cover, or for you to \"win\" the point if 
you picked them. 

The team with the plus sign (+3) is the underdog and is \"getting\" 3 points. 
They can  cover, or win, by winning the game outright, or by losing the game by less
than 3 points.

If the favored team, in this scenario, ends up winning by exactly 3 points,
this is a \"push\" and anyone who picked either team may receive points.  
Usually a push is scored 0.5 pts, but this is configured by the league Admin.
' >
                  <span class='glyphicon glyphicon-question-sign'></span></a> is listed to the right of the team name.
               </p>
               <script type='text/javascript'>
                   $(function () {
                       $('[rel='tooltip']').tooltip();
                   });
               </script>";
               
        echo "<h3>Your Picks for This Week (in red)</h3>";
        get_my_picks_page($week);
        echo "<br />";
        echo "<h3>The Rest of Your Picks for This Season (in red)</h3>";
        get_picks_history($week);
        echo "<br /><br /><br /><br />";
  
?>
    </div>
<?php
do_footer('bottom');
?>