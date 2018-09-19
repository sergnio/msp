<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: standings_pickum.php
   date: apr-2016
 author: hugh shedd
   desc: File is accessed thru the league type page standings_switch.php.
   This is the 'Pickem' standings - the page with two tables - weekly results
   and to-date season results.  It's all callback driven.  See mypicks01.js for the
   js code.
marbles:
   note:
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'season_standings_table.php';
require_once 'weekly_standings_table.php';
$msg = '';

validateUser();

$league_type = (!empty($_SESSION['league_type'])) ? $_SESSION['league_type'] : '';
if ($league_type != LEAGUE_TYPE_PICKUM) {
   header('location: ' . PAGE_LEAGUE_SWITCH);
   die();
}

@$active_week =   $_SESSION['active_week'];  // The current week in play.  This would have no standings
@$first_week =    $_SESSION['league_firstround'];
@$last_week =     $_SESSION['league_lastround'];
@$user_id =       $_SESSION['user_id'];
@$user_name =     $_SESSION['valid_user'];
@$league_id =     $_SESSION['league_id'];

if (  !$active_week ||
      !$first_week  ||
      !$user_id     ||
      !$user_name   ||
      !$league_id )
{

   // getLeagueParameters

}

if ($active_week < $first_week) {
   formatSessionMessage("$league_name league's first round doesn't begin until season week $first_week.  The current week is $active_week.  Please return later.",
         'info', $msg, "sp-51");
   setSessionMessage($msg, 'error');
   header("Location: index.php");
   die();
}


$ref_league_name = '';
$ref_push = '';
$ref_points = '';
$ref_first_round = '';
$ref_last_round = '';
$ref_status_text = '';

if (!getLeagueParameters($league_id, $ref_league_name, $ref_push, $ref_points, $ref_first_round, $ref_last_round, $ref_status_text)) {
   formatSessionMessage("There is missing information. League standings cannot be displayed.",
      'info', $msg, "sp-57 '$league_id', '$ref_league_name', '$ref_push', '$ref_points', '$ref_first_round', '$ref_last_round', '$ref_status_text'");
   setSessionMessage($msg, 'error');
   header('location: index.php');
   die;
}



$last_completed_week = $active_week;
if ($ref_last_round) {
   $last_completed_week = $ref_last_round;
}
$points_yes_no = ($ref_points) ? 'yes' : 'no';
$points_text = ($ref_points) ? 'Points (spreads) in use.' : 'No points (spreads).';
$push_scoring_text = $ref_push;


$report_mode = 'table';
$ref_season_data = '';
$ref_weekly_data = '';
$ref_err = '';
if (!getSeasonStandingsTable(
         $user_id            ,
         $league_id          ,
         $first_week         ,      // first round
         $last_completed_week,      // last round   last_round or active week
         $points_yes_no      ,
         $ref_push           ,
         $report_mode        ,
         $ref_season_data    ,
         $ref_err) )
{
   $error_message = 'Unknown error from season table.';
   if (is_array($ref_err)) {
      $error_message = $ref_err['ermsg'];
   }
   setSessionMessage($error_message, 'error');
}
$ref_err = '';
if (!getWeeklyStandingsTable(
         $user_name        ,
         $active_week      ,
         $league_id        ,
         $ref_push         ,
         $points_yes_no    ,
         $report_mode      ,
         $ref_weekly_data  ,
         $ref_err) )
{
   $error_message = 'Unknown error from weekly table.';
   if (is_array($ref_err)) {
      $error_message = $ref_err['ermsg'];
   }
   setSessionMessage($error_message, 'error');
}


$legend = "
   <table class='table'>
   <thead>\n
      <tr>
         <th style='text-align:center;'>Team</th>
         <th style='text-align:center;'>Description</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td><span style=\"color:black;\">???</span></td>
         <td>Pick made. Game not started. Pick hidden from public until game time.</td>
      </tr>
         <td><span style=\"color:green;\">DEN</span></td>
         <td>Game has started. Game is not yet scored.</td>
      </tr>
        <td><span style=\"color:blue;\">DEN</span></td>
        <td>Game has ended. Pick was a win. </td>
      </tr>
        <td><span style=\"color:red;\">DEN</span></td>
        <td>Game has ended. Pick was a loss.</td>
      </tr>
         <td><span style=\"color:black;\">DEN</span></td>
         <td>Game has ended in a tie. Pick was a push.</td>
      </tr>
      </tr>
         <td>Points</span></td>
         <td>$points_text</td>
      </tr>
      </tr>
         <td>Push</td>
         <td>Push (tie) awards $push_scoring_text points.</td>
      </tr>
   </tbody>
   </table>";

// If here, he's a valid user.  Cold users redirected to index.php

do_header('MySuperPicks.com - Standings');
do_nav('paintCurrentWeek()');

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
<?php


// TODO may want to set a "last week viewed / league" in SESSION

// TODO - now with first week of play defined, can I set errors to week 1?


$dateis = date('Y');  // Season is year based.
echo "
   <h1 class='text-center'>Standings for the NFL $dateis Season &nbsp;&nbsp;
      <button  id='IDb_pickemlegend' type='button' class='btn btn-info' name='pickemlegendbutton' style='width:80px;'>Legend</button></h1>
   <input type='hidden' id='IDi_league'            value='$league_id' />
   <input type='hidden' id='IDi_week'              value='$active_week' />
   <input type='hidden' id='IDi_firstweek'         value='$ref_first_round' />
   <input type='hidden' id='IDi_lastweek'          value='$last_completed_week' />
   <input type='hidden' id='IDi_pointsyesno'       value='$points_yes_no' />
   <input type='hidden' id='IDi_push'              value='$ref_push' />
   <input type='hidden' id='IDi_userid'            value='$user_id' />
   <input type='hidden' id='IDi_player'            value='$user_name' />
   <input type='hidden' id='IDi_highlightplayer'   value='"  . STANDINGS_PLAYER_HIGHLIGHT . "' />

   <div id='IDd_legend' style='margin: 30px 200px 1px 200px;' hidden>
      $legend
   </div>
";
?>
   <div id='IDd_ajaxmessageshere'></div>
   <div class="row">
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Weeky Standing. Week #<span id='IDs_weeknumber'><?php echo $active_week;?></span></h3>
<?php
            put_pagination_weekly_ajax($active_week, $league_id, 'IDul_upperWeekSelector', 'up', $last_completed_week);
?>
         <br /><br />

            <div id='IDdiv_putWeeklyTable'></div>
            <?php echo $ref_weekly_data; ?>
            <br />

<?php
            put_pagination_weekly_ajax($active_week, $league_id,'IDul_lowerWeekSelector', 'down', $last_completed_week);
?>
         </div>
      </div>  <!-- END col-md-6 #1 -->
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Overall Season Standings<br style='margin-top:0px;' /><small>as of</small><br />Week #<span id='IDs_seasonweeknumber'><?php echo $active_week;?></span></h3>
            <div id='IDdiv_putSeasonTable'></div>
            <?php echo $ref_season_data; ?>
         </div>
      </div> <!--  END col-md-6 #2 -->
   </div> <!-- END row -->
</div>
<br /><br /><br /><br />
</div>
<?php
do_footer('clean');


//============================================================================================================ functions

function put_pagination_weekly_ajax(
   $selected_week,      // 1 thru 17
   $league,             // The league id number
   $IDvalue,            // The ID will render as ID01 and ID02 - there are two separate UL elements
   $elevation,          // 'up' or 'down' set.
   $last_week_completed = 12  // The last week the user is able to select.  All others greater are disabled.
   ) {

   $pagination_break_after_week = 10;
   $pageination_break_made = false;
   $msg = '';

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

?>
