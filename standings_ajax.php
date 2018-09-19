<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: standings_ajax.php 
   date: apr-2016
 author: hugh shedd
   desc: File is linked to main page menu item "Standings"  It's
   the page with two tables - weekly results and to-date season
   results.  It's all callback driven.  See mypicks01.js for the
   js code.
marbles: 
   note:
*/

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');

validateUser();
// If here, he's a valid user.  Cold users redirected to index.php

do_header('MySuperPicks.com - Standings');
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

@$week = $_SESSION['active_week'];  // The current week in play.  This would have no standings
$last_completed_week = $_SESSION['active_week'];  // TODO Get this value from session?  hfs 4/2016
$league = $_SESSION['league_id'];   // Everyone belongs to a league.  This is assigned at login
                                    // league_id as a SESSION var is ONE LEAGUE

// TODO may want to set a "last week viewed / league" in SESSION                                    

// Hot link?
if (!preg_match('/^\d+$/', $week)) {
   $week = 1;
} elseif ($week < 0 || $week > NFL_LAST_WEEK) {
   $week = 1;
} elseif ($week > $last_completed_week) {  // TODO If none?
   $week = $last_completed_week;
}

$dateis = date('Y');  // Season is year based.
echo "
   <h1 class='text-center'>Standings for the NFL $dateis Season</h1>
   <input type='hidden' id='IDi_league'            value='$league' />
   <input type='hidden' id='IDi_week'              value='$week' />
   <input type='hidden' id='IDi_lastCompletedWeek' value='$last_completed_week' />
   <input type='hidden' id='IDi_userid'            value='" . $_SESSION['user_id'] . "' />
   <input type='hidden' id='IDi_player'            value='" . $_SESSION['valid_user'] . "' />
   <input type='hidden' id='IDi_highlightplayer'   value='"  . STANDINGS_PLAYER_HIGHLIGHT . "' />
";
?>
   <div class="row">
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Weeky Standing. Week #<span id='IDs_weeknumber'><?php echo $week;?></span></h3>
<?php
            put_pagination_weekly_ajax($week, $league, 'IDul_upperWeekSelector', 'up', $last_completed_week);
?>
         <br /><br />

            <div id='IDdiv_putWeeklyTable'></div>
            <br />

<?php
            put_pagination_weekly_ajax($week, $league,'IDul_lowerWeekSelector', 'down', $last_completed_week);
?>            
         </div>
      </div>  <!-- END col-md-6 #1 -->
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Overall Season Standings<br style='margin-top:0px;' /><small>as of</small><br />Week #<span id='IDs_seasonweeknumber'><?php echo $week;?></span></h3>
            <div id='IDdiv_putSeasonTable'></div>
         </div>
      </div> <!--  END col-md-6 #2 -->
   </div> <!-- END row -->
</div>
<br /><br /><br /><br />
</div>
<?php
do_footer('clean');


//============================================================================================================ functions
// I know this is a little hookie, but it simplies the jQuery quite-a-bit.
// Since we render the page once, why not.
// The way this works... The click event is attached to the li anchor in the
// bootstrap 'pagination' element.  That click event does a call back to get
// the data to render the table.  The jQuery is coded in mypicks01.js - a javascript
// file included via a link in the main defs file.

function put_pagination_weekly_ajax(
   $selected_week,      // 1 thru 17
   $league,             // The league id number
   $IDvalue,            // The ID will render as ID01 and ID02 - there are two separate UL elements
   $elevation,          // 'up' or 'down' set.
   $last_week_completed = 12  // The last week the user is able to select.  All others greater are disabled.
   ) {

   $pagination_break_after_week = 10;
   $pageination_break_made = false;
   
   // For convenience, bootstrap paginations appear both above and below the table
   // 17 seasons and 17 buttons wouldn't align right so I cut them up into to
   // groups; as shown below.  The javascript hits them both.  This is only the 
   // upper or lower group of buttons.  put_pagination_weekly_ajax() is called once
   // for each - the lower set and the upper set of buttons.
   $id_number01 = $IDvalue . '01';
   $id_number02 = $IDvalue . '02';

   echo "            <ul id='$id_number01' class='pagination pagination-sm' lastweek='$last_week_completed' style='margin: 0px !important;'>
      ";
   for ($week_number = 1; $week_number <= NFL_LAST_WEEK; $week_number++) {
      
      $week_rendered = $week_number + 10;
      $week_rendered .= "$elevation";
      if ($week_number > $pagination_break_after_week && $pageination_break_made === false) {
         $pageination_break_made = true;
         echo "            </ul><br />
            <ul id='$id_number02' class='pagination pagination-sm' lastweek='$last_week_completed' style='margin: 0px !important;'>
         ";
      }
      $li_class = '';
      $anchor = "<a href='#' leagueis='$league' week='$week_rendered' status='enabled'>$week_number</a>";
      
      if ($week_number > $last_week_completed) {
         $li_class .= "class='disabled'";
         $anchor = "<a status='disabled'>$week_number</a>";
      }
      if ($week_number == $selected_week) {
         $li_class .= "class='active'";
      }
      echo "               <li $li_class id='IDli_weekval$week_rendered'>$anchor</li>
      ";
   }
   echo "            </ul>";
}

// table building removed may 19 - all callbacks now

?>