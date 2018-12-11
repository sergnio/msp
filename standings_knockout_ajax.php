<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:
standings_knockout_ajax.php
   file: standings_knockout_ajax.php
   date: may-2016
 author: originall
   desc: File is linked to main page menu item "Standings"  It's
   the Knockout version of the Pick'm standings page with two tables - weekly 
   results and to-date season results.  It's all callback driven.  See mypicks05.js
   for the js code.
marbles: 
   note:
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'support_ko_cohort.php';
require_once 'support_ko_last_man.php';
$msg = '';

//validateUser();
// If here, he's a valid user.  Cold users redirected to index.php

do_header('MySuperPicks.com - Standings KO');
do_nav();
?>
<div class="container">
<?php echo_container_breaks(); ?>
   <div class="hidden-sm hidden-md hidden-lg">
      <br />
      <br />
      <br />
   </div>
   <div class="row">
      <div class="col-md-12">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Survivor - Last man demo</h3>
            <h5 style='text-align:center;'>to leave, select 'Home'</h5>

         <br />
         
         <table id='IDtable_ko_legend' style='margin-left:auto;margin-right:auto;'>
            <thead>
               <tr>
                  <th style='text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Player&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Week ##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Week ##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Week ##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Week ##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
               </tr>
            </thead>
               <tr>
                  <td winner='winner'>I win the game!</td>
                  <td inout='in' > Wins pick </td>
                  <td inout='out'> Looses pick </td>
                  <td special='shootout'> Shootout </td>
                  <td> - (out of play)</td>
               </tr>
            </tbody>
         </table>
         <br />
         <br />
                  
            <div id='IDdiv_putWeeklyTable'>
            <?php sayKOLastManStandingsTable(11, 17,  1); ?>
            </div>
            <br />
            <br />

         </div>                                                                        
      </div>  <!-- END col-md-6 #1 -->
      <div class="col-md-12">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Survivor - Cohort demo</h3>
            <h5 style='text-align:center;'>to leave, select 'Home'</h5>
         <br />

            <div id='IDdiv_putWeeklyTable'>
            <?php sayKOCohortStandingsTable(10, 14,  1); ?>
            </div>
            <br />

         </div>
      </div>  <!-- END col-md-6 #1 -->
   </div>
</div>
<?php

do_footer('clean');
?>
