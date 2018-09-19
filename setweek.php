<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: setweek.php
   date: apr-2016
 author: origninal
   desc: Called from league_management.php =>Button Set Active Week
  notes:
  
marbles: 
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('admin');

do_header('MySuperPicks.com - Set League Active Week');
do_nav();
?> 
   <div class="container">
<?php
echoContainerBreaks();
echoSessionMessage();
$current_active_week = (!empty($_SESSION['active_week'])) ? $_SESSION['active_week'] : 'Unknown';
?>
      <h1 class="text-center">Set Active Week</h1>
<?php echo "<h3 class='text-center'>Current week is $current_active_week</h3>"; ?> 
      <br />
      <form action="setweek2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
         <div class="form-group">
            <label for="pstate" class="col-sm-2 control-label">Select Active Week:</label>
            <div class="col-sm-4">
<?php 
echo "               <div class=\"input-group\">
               <select name='week' class='form-control input-medium'>\n";
$week = $_SESSION['active_week'];
for ($i=1; $i <= NFL_LAST_WEEK; $i++) {
   if ($week == $i) {
      echo "                  <option value='$i' selected='selected'>Week $i</option>\n";
   } else {
      echo "                  <option value='$i'>Week $i</option>\n";
   }
}
echo "               </select>
               <span class=\"input-group-btn\">
                  <button type=\"submit\" class=\"btn btn-primary\">Submit</button>
               </span>
            </div>
            <input type=\"hidden\" name=\"submit\" value=\"true\" />
         </div>
      </div>
   </form>\n";
?>
</div>
<?php
do_footer('clean');
?>