<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: standings_ko_cohort.php
   date: may-2016
 author: hugh shedd
   desc: One of three standings pages accesses thru the league type page
      standings_switch.php.  The three are pickem, knockout: cohort and last man.
      This is the cohort standings page.
marbles: 
   note:
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';

require_once 'support_ko_cohort2.php';
$msg = '';

//validateUser();
// If here, he's a valid user.  Cold users redirected to index.php

validateUser();

$league_name =        (!empty($_SESSION['league_name'])) ?       $_SESSION['league_name'] : '';
$league_type =        (!empty($_SESSION['league_type'])) ?       $_SESSION['league_type'] : '';
$league_id =          (!empty($_SESSION['league_id'])) ?         $_SESSION['league_id'] : '';
$league_first_round = (isset($_SESSION['league_firstround'])) ?  $_SESSION['league_firstround'] : '';
$league_last_round =  (isset($_SESSION['league_lastround'])) ?   $_SESSION['league_lastround'] : '';
$league_points =      (!empty($_SESSION['league_points'])) ?     $_SESSION['league_points'] : '';
$active_week =        (!empty($_SESSION['active_week'])) ?       $_SESSION['active_week'] : '';
$user_id  =           (!empty($_SESSION['user_id'])) ?           $_SESSION['user_id'] : '';
$ref_status_text = '';

writeDataToFile("sayKOCohortStandingsTable.php session: " . print_r($_SESSION, true));
writeDataToFile("standings_ko_cohort.php !$league_name !$league_type || !$league_id || !$league_first_round || !$active_week || !$user_id\n", __FILE__, __LINE__);

if (!$league_type || !$league_id || !$league_name || !($league_first_round >= 0 && $league_first_round <= NFL_LAST_WEEK ) || !$active_week || !$user_id) {
   formatSessionMessage("We are unable to display the standings. Information is missing.  Please contact the site administrator",
      'danger', $msg, "skc-44 !$league_type || !$league_id || !$league_name || !$league_first_round !$active_week || !$user_id");
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}

if ($league_type != LEAGUE_TYPE_COHORT) {
   header('location: ' . PAGE_LEAGUE_SWITCH);
   die;
}
if ($league_first_round === 0) {
   formatSessionMessage("There is an error.  First round is indicated as week 0. Please contact the league's Admin.",
      'warning', $msg, "skc-58 '$league_name' '$league_first_round'");
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}
   
if ($league_first_round > $active_week) {  // for hot linking
   formatSessionMessage("$league_name league's first round doesn't begin until season week $first_round.  The current week is $active_week.  Please return later.",
      'info', $msg, "skc-70" );
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}

$title_line = "<h2 style='text-align:center;'><i>$league_name</i>&nbsp;&nbsp;Survivor - (cohort)" .
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . 
              "<button  id='IDb_koabout' type='button' class='btn btn-info' name='aboutbutton' style='width:80px;'>About</button>
               <button  id='IDb_kolegendcohort' type='button' class='btn btn-info' name='legendbutton' style='width:80px;'>Legend</button></h2>";
              
$about = " 
<p>There are two ways to play Survivor - <b>Last Man</b> and <b>Cohort</b>.</p> 
<p>In both versions of Survivor, players choose a single team each week to win.  
Players choosing winning teams advance to the next week and play again. You are 
only allowed to choose a team one time during the season. Once you have chosen a 
team, they are not available again. NFL games ending in a tie are considered a 
loss - you must pick a <b>winning</b> team to advance.</p> 

<p>Very important - make sure you make your pick by game time.  Players who do 
not submit a pick forfeit the game and cannot win. If all remaining players fail 
to make a pick, the season ends and the Admin will declare a winner(s).</p> 

<p><b>Survivor - Last Man Version:</b> Play continues in Last Man until a single 
player remains - the last man standing. If all remaining players loose one week, 
their losses are 'forgiven' and they continue to play the next week. The forgiven 
loss is indicated by a yellow <span style='background-color:#FFFF99;'>&nbsp&nbsp;halo&nbsp&nbsp;</span>.  
Forgiven players advance to the next week and play continues until a single player remains.</p>

<p><b>Important:</b> Failing to make a pick cannot be forgiven. A player could 
win the game even with a loss should all the other remaining players fail to submit 
a pick.</p> 
<p><b>Survivor - Cohort Version:</b> In Cohort, more than one player, or a 'cohort'
can win. Just like Last Man, a player wins if he or she is the only winning, or 
surviving player in a week. However, in Cohort if <b>all remaining players loose,</b> 
the season ends and those remaining players are the winners and the season is done. 
The 'cohort' has won.</p> 
<p>If, after week 17, there are still multiple players without a loss, each of them
is declared a winner in both Last Man and Cohort.</p> 
<p>Points spreads may or may not be used as determined by the league Admin at
the time when the league is created. Leagues that use point spreads are not picking
'winners', but instead are picking teams to beat, the spread, or 'cover'.  Points are
displayed at pick time if used.</p> 
"; 


$legend = "         
         <table id='IDtable_ko_legend' class='table' style='margin-left:auto;margin-right:auto;'>
            <thead>
               <tr>
                  <th style='text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Play&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
               </tr>
            </thead>
               <tr>
                  <td style='color:blue;font-weight:bold;text-align:right;'>Player's Name</td>
                  <td>The game has been won. I (we) win the game!</td>
               </tr>
               <tr>
                  <td style='text-align:right;'>???</td>
                  <td>The pick has been made.  The football game has not started.  The pick is not yet public.</td>
               </tr>
               <tr>
                  <td style='color:green;text-align:right;'>VIK</td>
                  <td>The football game is in progress.  The final score is not available.</td>
               </tr>
               <tr>
                  <td style='color:blue;text-align:right;'>VIK</td>
                  <td>The football game is complete. It's a win.</td>
               </tr>
               <tr>
                  <td  style='color:red;text-align:right;'>VIK</td>
                  <td>The football game is complete.  It's a loss.</td>
               </tr>
               <tr>
                  <td style='text-align:right;'> - </td>
                  <td>The player has not picked OR the player is out.</td>
               </tr>
            </tbody>
         </table>";


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
            <div id='IDd_legend' style='margin: 30px 200px 1px 200px;text-align:left;' hidden>
               <?php echo $legend; ?>
            </div>
            <div id='IDd_about' style='margin: 30px 200px 30px 200px;text-align:left;' hidden> <!-- t,r,b,l -->
               <?php echo $about; ?>
            </div>
            <div id='IDdiv_putWeeklyTable'>
            <?php 
               if (!sayKOCohortStandingsTable($league_first_round, $league_last_round, $active_week, $league_id, $league_points, $ref_status_text )) {
                  echoSessionMessage();
               }
            ?>
            </div>
            <br />

         </div>
      </div>  <!-- END col-md-6 #1 -->
   </div>
</div>
<?php

do_footer('clean');
?>
