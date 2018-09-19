<?php
$default_timeout =  60 * 60 * 24 * 30;  // 30 days
session_set_cookie_params($default_timeout);
session_start();

/*
:mode=php:

   file: standings_js.php 
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
$msg = '';

validateUser();

do_header('MySuperPicks.com - Standings');
do_nav();
?>

<div class="container">
<?php
echo_container_breaks();
?>
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
$last_completed_week = 12;

if(isset($_GET['submit_week']) && !empty($_GET['submit_week'])) {
   $week = $_GET['submit_week'];
   $week = sql_sanitize($week);
   $week = html_sanitize($week);
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
            <a href='standings_js' id='standings_js_id' name='standings_js_name' >write it</a>
            <div id="standings_week"></dvi>
            <br />

<?php
            put_pagination_weekly($week, $last_completed_week);
?>            
         </div>
      </div>  <!-- END col-md-6 #1 -->
      <div class="col-md-6">
         <div style='text-align:center;'>
            <h3 style='text-align:center;'>Overall Standings for This Season</h3>
            <div id="standings_season"></div>
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
<script type='text/javascript' >

$('#standings_js_id').click(function(e) {
   answer = true;
   
   var emailaddress = $('#email').val();
   var emailaddresstrimmed = String(emailaddress).myTrim();
   var msg = $('#comment').val();
   var msgtrimmed = String(msg).myTrim();
   
   if (emailaddresstrimmed || msgtrimmed) {
      var answer = confirm(
        'Are you sure?  Any data will be lost.' 
        + '\\nPress \"OK\" to continue to the Email Log');
   }
   
   if (answer == true ) {
      return  true;
   } else {
      return false;
   }
});

</script>

<?php
do_footer('bottom');
?>
