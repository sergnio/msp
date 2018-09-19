<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: schedules.php
   date: apr-2016
 author: origninal
   desc: 
  notes:
  
marbles: 
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');

do_header('MySuperPicks.com - Edit Schedules');
do_nav();
?> 
   <div class="container">
<?php
echo_container_breaks();
$login = '';
if (!empty($_GET['login'])) { $login = $_GET['login']; }
$login = sql_sanitize($login);
$login = html_sanitize($login);
$update = '';
if (!empty($_GET['update'])) { $update = $_GET['update']; }
$update = sql_sanitize($update);
$update = html_sanitize($update);
$submit = '';
if (!empty($_GET['week'])) {
   $week = $_GET['week'];
   $week = sql_sanitize($week);
   $week = html_sanitize($week);
   $submit = 'true';
}
if (!empty($_POST['week'])) {
   $week = $_POST['week'];
   $week = sql_sanitize($week);
   $week = html_sanitize($week);
   $submit = $_POST['submit'];
   $submit = sql_sanitize($submit);
   $submit = html_sanitize($submit);
}
if ($login == 'success') {
   echo "<div class=\"alert alert-success\">You are logged in!</div>";
}
if ($login == 'fail') {
   echo "<div class=\"alert alert-danger\">We were not able to log you in. Please check your username and password and try again. Thank you.</div>";
}
if ($update == '1') {
   echo "<div class=\"alert alert-success\">Schedule Updated!</div>";
}
if ($update == '0') {
   echo "<div class=\"alert alert-danger\">The schedule was not updated. Please check the information and try again.</div>";
}
if(check_valid_user_admin()) {
?>
      <h1 class="text-center">Edit Schedules</h1>
      <br />
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
         <div class="form-group">
            <label for="pstate" class="col-sm-2 control-label">Choose Week:</label>
            <div class="col-sm-4">
<?php 
   echo "<div class=\"input-group\"><select name='week' class='form-control input-medium'>";
   $conn = db_connect();
   $result = $conn->query("SELECT week FROM schedules GROUP BY week ORDER BY week ASC");
   while ($row=$result->fetch_object()) {
      $row_week = $row->week;
      if ($row_week == $week) {
         echo "<option value='$row_week' selected='selected'>Week $row_week</option>";
      } else {
         echo "<option value='$row_week'>Week $row_week</option>";
      }
   }
   mysqli_close($conn);
   echo "</select>";
   echo "<span class=\"input-group-btn\"><button type=\"submit\" class=\"btn btn-primary\">Submit</button></span></div><input type=\"hidden\" name=\"submit\" value=\"true\" /></div></div></form>";

   if ($submit == 'true') {
      get_schedules_admin($week);
      echo "<br /><br /><br /><br />";
   }
} else {
?>
      <p class="lead">You are not allowed to view this page.</p>
<?php
}
?>
   </div>
<?php
do_footer('clean');
?>