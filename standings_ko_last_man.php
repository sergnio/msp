<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: standings_kko_last_man.php
   date: may-2016
 author: hugh shedd
   desc: One of three standings pages accessed thru the league type page
      standings_switch.php.  The three are pickum, knockout: cohort and last man.
      This is the last man standings page.
marbles:
   note:

*/

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
require_once 'support_ko_last_man3.php';


//validateUser();
// If here, he's a valid user.  Cold users redirected to index.php

validateUser();

$league_name =        (!empty($_SESSION['league_name'])) ?       $_SESSION['league_name'] : '';
$league_type =        (!empty($_SESSION['league_type'])) ?       $_SESSION['league_type'] : '';
$league_id =          (!empty($_SESSION['league_id'])) ?         $_SESSION['league_id'] : '';
$league_first_round = (!empty($_SESSION['league_firstround'])) ? $_SESSION['league_firstround'] : '';
$league_last_round =  (isset($_SESSION['league_lastround'])) ?   $_SESSION['league_lastround'] : '';
$league_points =      (!empty($_SESSION['league_points'])) ?     $_SESSION['league_points'] : '';
$active_week =        (!empty($_SESSION['active_week'])) ?       $_SESSION['active_week'] : '';
$user_id  =           (!empty($_SESSION['user_id'])) ?           $_SESSION['user_id'] : '';

if (!$league_name || !$league_type || !$league_id || !$league_first_round || $league_last_round === '' || !$active_week || !$user_id) {
   formatSessionMessage("We are unable to display the standings. Information is missing.  Please contact the site administrator", 'danger', $msg);
   setSessionMessage($msg, 'error');
   writeDataToFile("standings_ko_last_man.php !$league_type || !$league_id || !$league_first_round || !$active_week || !$user_id\n" .
      print_r($_SESSION, true), __FILE__, __LINE__);
   header('Location: index.php');
   die();
}

if ($league_type != LEAGUE_TYPE_LAST_MAN) {
   header('location: ' . PAGE_LEAGUE_SWITCH);
   die;
}
if ($league_first_round > $active_week) {  // for hot linking
   formatSessionMessage("$league_name league's first round doesn't begin until season week $first_round.  The current week is $active_week.  Please return later.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}

$title_line = "<h2 style='text-align:center;'><i>$league_name</i>&nbsp;Survivor" .
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
              "<button  id='IDb_koabout' type='button' class='btn btn-info' name='aboutbutton' style='width:80px;'>About</button>
               <button  id='IDb_kolegendlastman' type='button' class='btn btn-info' name='legendbutton' style='width:80px;'>Legend</button></h2>";


$about = "
<p>Players pick a single NFL team each week to win. (Your pick can be the same as other player&rsquo;s). If the pick is a winner, you advance to the next week to play again. Players who choose a losing team are eliminated. Play continues until there is a single player remaining.</p>
<p>Players can pick each NFL team only once during the season. Teams that you have picked in previous weeks will be greyed out on the schedule and the site will not allow you to pick that team again. However, you can change a pick that hasn&rsquo;t been used yet up until game time. Picks don&rsquo;t lock until the kick-off of each game.</p>
<p>Make sure you make your pick by game time. Players who do not submit a pick in a given week are eliminated. NFL games ending in a tie are considered a loss - you must pick a winning team to advance.</p>
<p>If all remaining players lose during the same week, their losses are 'forgiven' and they advance to the next week to continue playing until a single player remains. The forgiven loss is indicated by a yellow <span style='background-color:#FFFF99;'>&nbsp&nbsp;halo&nbsp&nbsp;</span> and that team is not allowed to be picked again.</p>
<p>For example: If there are only 2 remaining players late in the season and they both pick a losing team, (whether it&rsquo;s the same pick or not), both players continue playing the next week. A player must make a final winning pick in order to be the declared the &ldquo;Survivor&rdquo; of their league. If there are multiple players remaining after the completion of week 17, the game ends and each of them are declared winning survivors.</p>
";

$legend = "
<div class='container'>
         <table id='IDtable_ko_legend' class='table' style='margin-left:auto;margin-right:auto;'>
            <thead>
               <tr>
                  <th style='text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Play&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
               </tr>
            </thead>
               <tr>
               <div  class='col-4'><td class='player_curse_sc' style='text-align:right;' >???</td></div>
                  <div class='col-8'><td>Pick made. Game not started. Pick hidden from public until game time.</td></div>
               </tr>
               <tr>
                  <td  class='player_curse_ip' style='text-align:right;' >MIN</td>
                  <td>Game has started. Game is not yet scored.</td>
               </tr>
               <tr>
                  <td class='player_curse_wi' style='text-align:right;' >MIN</td>
                  <td>Game has ended. Pick was a win.</td>
               </tr>
               <tr>
                  <td class='player_curse_lo' style='text-align:right;' >MIN</td>
                  <td>Game has ended. Pick was a loss.</td>
               </tr>
               <tr>
                  <td  class='player_bless_lo' style='text-align:right;' ><span  style='background-color:yellow;color:red;text-align:right;'>&nbspMIN&nbsp;</span></td>
                  <td>Game has ended. Pick was a loss but all remaining players also lost. The loss is blessed.</td>
               </tr>
               <tr>
                  <td  class='player_curse_out' style='text-align:right;' > - </td>
                  <td>No pick made or player has been knocked out of the game.</td>
               </tr>
            </tbody>
         </table>
         </div>";

do_header('MySuperPicks.com - Standings KO');
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
   <div class="row">
      <div class="col-md-12">
         <div style='text-align:center;'>
            <?php echo $title_line; ?>
         <br />
         <div id='IDd_legend' hidden>
            <?php echo $legend; ?>
         </div>
         <div id='IDd_about' hidden>
            <?php echo $about; ?>
         </div>
         <br />
         <br />

            <div id='IDdiv_putWeeklyTable'>
            <?php
               if (!sayKOLastManStandingsTable($league_first_round, $league_last_round, $active_week, $league_id, $league_points, $ref_status_text )) {  // support_ko_last_man.php
                  echoSessionMessage();
               }
            ?>
            </div>
            <br />
            <br />

         </div>
      </div>  <!-- END col-md-6 #1 -->
   </div>
</div>

<?php

do_footer('clean');
?>
