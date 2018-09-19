<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: schedules_nsp.php
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

validateUser('admin');

do_header('MySuperPicks.com - Edit Schedules');
do_nav();
?> 
   <div class="container">
<?php
echo_container_breaks();
echoSessionMessage();
                                                          
$update = (!empty($_GET['update'])) ?  $_GET['update'] : '';

$week = 'unselected';
if (!empty($_SESSION['updateweek'])) {  // set by scheduleseditadmin.php
   $week = $_SESSION['updateweek'];
   unset($_SESSION['updateweek']);
} elseif (!empty($_POST['week'])) {
   $week = sanitize($_POST['week']);
   if ($week < 1 || $week > NFL_LAST_WEEK) {
      $week = 0;
   }
}


if ($update == '1') {
   echo "<div class=\"alert alert-success\">Schedule Updated!</div>";
}
if ($update == '0') {
   echo "<div class=\"alert alert-danger\">The schedule was not updated. Please check the information and try again.</div>";
}
?>
      <h1 class="text-center">Edit Schedules (nsp)</h1>
      <?php echo "<h3 class='text-center'>Currently selected week $week</h3>"; ?>
      <br />
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
         <div class="form-group">
            <label for="pstate" class="col-sm-2 control-label">Choose Week:</label>
            <div class="col-sm-4">
<?php 
echo "               <div class=\"input-group\">\n";
echo "                  <select name='week' class='form-control input-medium'>\n";
$conn = db_connect();
$result = $conn->query("SELECT week FROM schedules GROUP BY week ORDER BY week ASC");
while ($row=$result->fetch_object()) {
   $row_week = $row->week;
   writeDataToFile("row and week " . $row_week . ", " . $week, __FILE__, __LINE__);
   if ($row_week == $week) {
      echo "                     <option value='$row_week' selected='selected'>Week $row_week</option>\n";
   } else {
      echo "                     <option value='$row_week'>Week $row_week</option>\n";
   }
}
mysqli_close($conn);
echo "                  </select>\n";
echo "                  <span class=\"input-group-btn\"><button type=\"submit\" class=\"btn btn-primary\">Submit</button></span>\n";
echo "               </div>\n";
echo "               <input type=\"hidden\" name=\"submit\" value=\"true\" />\n";
echo "            </div>\n";
echo "         </div>\n";
echo "      </form>\n";

if ($week) {
   get_schedules_admin($week);  // <form action=\"scheduleseditadmin.php\" method=\"post\" class=\"form-horizontal\" role=\"form\" enctype=\"multipart/form-data\">
}
?>
   </div>
<?php
do_footer('clean');
?>