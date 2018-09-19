<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: standings.php 
   date: apr-2016
 author: hugh shedd
   desc: File is linked to main page "Standings"
marbles: 
   note: This will be used to play with the tables displayed.  There are two;
      Results, and Overall Standings for This Season.  The login used was 
      'matt'; (all passwords for the dev and drillbrain site where set to 
      'duck'.
*/

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');

do_header('MySuperPicks.com - Standings');
do_nav();
writeDataToFile("HERE IS GET: " . print_r($_GET, true), __LINE__, __FILE__);
writeDataToFile("HERE IS POST: " .  print_r($_POST, true), __LINE__, __FILE__);
?>

<div class="container">
   <br />
   <br />
   <br />
   <div class="hidden-sm hidden-md hidden-lg">
      <br />
      <br />
      <br />
   </div>
<?php
$login = '';
if (!empty($_GET['login'])) { $login = $_GET['login']; }
$login = sql_sanitize($login);
$login = html_sanitize($login);
if ($login == 'success') {
   //echo "<div class=\"alert alert-success\">You are logged in!</div>";
}
if ($login == 'fail') {
   echo "<div class=\"alert alert-danger\">We were not able to log you in. Please check your username and password and try again. Thank you.</div>";
}


@$week = $_SESSION['active_week'];
$last_completed_week = 12;  // TODO Get this value from session?  hfs 4/2016

if(isset($_GET['submit_week']) && !empty($_GET['submit_week'])) {
   $week = trim($_GET['submit_week']);
}

// Hot link?
if (!is_numeric($week) || !preg_match('/^\d+$/', $week)) {
   $week = 1;
} elseif ($week < 0 || $week > 17) {
   $week = 1;
} elseif ($week > $last_completed_week) {  // TODO If none?
   $week = $last_completed_week;
}


?>
   <h1 class="text-center">Standings for the NFL <?php echo date("Y"); ?> Season</h1>
<?php 
if(check_valid_user()) {
   // var_dump($_SESSION);
?>   
   <div class="row">
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Weeky Standing.  <?php echo "Week #" . $week;?></h3>
<?php
            put_pagination_weekly($week, $last_completed_week);
?>
         <br /><br />

            <?php put_table_standings_week($week); ?>
            <br />

<?php
            put_pagination_weekly($week, $last_completed_week);
?>            
         </div>
      </div>  <!-- END col-md-6 #1 -->
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Overall Standings for This Season</h3>
            <?php put_table_standings_season($week); ?>
         </div>
      </div> <!--  END col-md-6 #2 -->
   </div> <!-- END row -->
</div>
<br /><br /><br /><br />
<?php
   } else {
?>
<br /><br /><br /><br />
<p class="lead muted text-center">Log in to view the standings for your league. </p>
<br /><br /><br /><br />
<?php
}
?>
</div>
<?php
do_footer('bottom');
?>