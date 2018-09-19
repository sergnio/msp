<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: league_operations.php
   date: jul-2016
 author: hfs
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

$week             = (isset($_SESSION['active_week']))          ? $_SESSION['leagueactive'] : false;
$league_status    = (isset($_SESSION['leagueactive']))         ? $_SESSION['leagueactive'] : false;
$first_round      = (isset($_SESSION['league_firstround']))    ? $_SESSION['league_firstround'] : false;
$last_round       = (isset($_SESSION['league_lastround']))     ? $_SESSION['league_lastround'] : false;  // gets changed - don't forget to update local
$league_id        = (isset($_SESSION['league_id']))            ? $_SESSION['league_id'] : false;
$league_type      = (isset($_SESSION['league_type']))          ? $_SESSION['league_type'] : false;
$league_picks     = (isset($_SESSION['league_picks']))         ? $_SESSION['league_picks'] : false;
$league_push      = (isset($_SESSION['league_push']))          ? $_SESSION['league_push'] : false;
$league_points    = (isset($_SESSION['league_points']))        ? $_SESSION['league_points'] : false;

if (
   $week           === false ||
   $league_status  === false ||
   $first_round    === false ||
   $last_round     === false ||
   $league_id      === false ||
   $league_type    === false ||
   $league_picks   === false ||
   $league_push    === false ||
   $league_points  === false ) 
{
   formatSessionMessage("League Operations cannot be displayed at this time.", 'info', $msg, "wk-ls-fr-lid: writefile'");
   setSessionMessage($msg, 'error');
   writeDataToFile("wk-ls-fr-lid:
      'week           '$week,          
      'league_status  '$league_status, 
      'first_round    '$first_round,   
      'last_round     '$last_round,    
      'league_id      '$league_id,     
      'league_type    '$league_type,   
      'league_picks   '$league_picks,  
      'league_push    '$league_push,   
      'league_points  '$league_points, ... ", __FILE__, __LINE__); 
   header("Location league_management.php");
   die();
}
if ($first_round < 1 || $first_round > (NFL_LAST_WEEK - 1)) {
   formatSessionMessage("Bounds error.", 'danger', $msg, "first:'$first_round'");
   setSessionMessage($msg, 'error');
   header("Location league_management.php");
   die();
}

$edit_last_week =       (isset($_POST['submitlastweek']))      ? $_POST['submitlastweek'] : false;
$edit_league_status =   (isset($_POST['submitleaguestatus']))  ? $_POST['submitleaguestatus'] : false;
$select_last_week =     (isset($_POST['lastweek']))            ? $_POST['lastweek'] : false;
$select_league_status = (isset($_POST['leaguestatus']))        ? $_POST['leaguestatus'] : false;
$edit_status_sure =     (isset($_POST['yesiam']))              ? $_POST['yesiam'] : false;  // don't check

while (1) {
   
   if ($edit_last_week) {
       if (!$rtn = setLeagueLastWeek($league_id, $select_last_week, $ref_status_text)) {
         if ($rtn === NULL) {
            formatSessionMessage("League final week was not updated.", 'info', $msg);
            setSessionMessage($msg, 'error');
         } else if ($rtn === false) {
            formatSessionMessage("Failed to set lastround.", 'info', $msg, "'$rtn'$ref_status_text'");
            setSessionMessage($msg, 'error');
            break;
         } else if ($rtn === 0) {
            formatSessionMessage("League final week was not updated.  No changes were found.", 'info', $msg, "'$rtn'$ref_status_text'");
            setSessionMessage($msg, 'error');
            break;
         }
      } else {
         formatSessionMessage("Last week of play was updated.", 'success', $msg);
         setSessionMessage($msg, 'error');
      }
      if (!updateSession('currentleaguelastweek', $select_last_week)) {
         formatSessionMessage("Failed to update current session lastround.", 'info', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      $last_round = $select_last_week;
   }
   
   if ($edit_league_status) {
      $numeric_league_status = ($select_league_status == 'yes') ? 1 : 0;
      if (!$rtn = setLeagueStatus($league_id, $numeric_league_status)) {
         if ($rtn === null) {
            formatSessionMessage("League status was not updated.", 'info', $msg);
            setSessionMessage($msg, 'error'); 
         } else if ($rtn === false) {
            formatSessionMessage("Failed to set status.", 'info', $msg);
            setSessionMessage($msg, 'error');
            break;
         } else if  ($rtn === 0)  {
            formatSessionMessage("League status was not updated.  No changes were found.", 'info', $msg);
            setSessionMessage($msg, 'error');
            break;
         }
      } else {
         formatSessionMessage("League status was updated.", 'success', $msg);
         setSessionMessage($msg, 'error');
      }
      if (!updateSession('currentleaguestatus', $numeric_league_status)) {
         formatSessionMessage("Failed to update current session lastround.", 'info', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      $com_status = validateUser('commissioner', 'status');
      if (!$com_status) {
         formatSessionMessage("League status was changed to inactive.  You no longer have access rights..", 'info', $msg);
         setSessionMessage($msg, 'error');
         if (!empty($_SESSION['league_admin'])) { // TODO do a proper league session update after inactivation.
            unset($_SESSION['league_admin']);
         }
         header("Location: index.php");
         die();
      }
   }
   $status = 1;
   break;
}

// Do this here.  Some redirects above don't need this info.
formatSessionMessage("Partially implemented.", 'warning', $msg);
setSessionMessage($msg, 'error');

//Displays for status table
$display_league_type = getLeagueTypeName($league_type);

$display_league_push = '';
if ($league_type == LEAGUE_TYPE_COHORT || $league_type == LEAGUE_TYPE_LAST_MAN) { 
   $display_league_push = 'A push is a loss in KO leagues';
} else {
   $display_league_push = getLeaguePushValue($league_push) . ' point(s)';
}
$display_last_round =   (!$last_round)       ? 'not set - league in play if active' : $last_round . ' - league is not in play';
$display_active =       ($league_status)     ? 'active' : 'inactive';
$display_points =       ($league_points == LEAGUE_ODDS_IN_USE)  ? 'points (spread) in use' : 'no points (spread)';
$display_picks =        ($league_type == LEAGUE_TYPE_PICKUM)         ? $league_picks : '1 - Knockout leagues allow only 1';

writeDataToFile("league_operations.php: '$edit_status_sure' '$edit_last_week' '$edit_league_status' '$select_last_week' '$select_league_status' ", __FILE__, __LINE__);


$active_options = '';
$week_options = "                  <option value='0' >unset</option>\n";
for ($i=$first_round + 1; $i <= NFL_LAST_WEEK; $i++) {
   if ($last_round == $i) {
      $week_options .= "                  <option value='$i' selected='selected'>Week $i</option>\n";
   } else {
      $week_options .= "                  <option value='$i'>Week $i</option>\n";
   }
}

$is_active =      ($league_status) ? "selected='selected'"  : '';
$is_not_active =  ($league_status) ? ''                     : "selected='selected'";
$active_options  = "                  <option value='yes' $is_active >yes</option>\n";
$active_options .= "                  <option value='no' $is_not_active >no</option>\n";


$about = "
<b>Select Last Week of Play</b> Setting the last week, with any value other the 'unset' (0), 
ends league play. Results can be viewed but there is no longer
any player activity allowed.  The value of the last week is used in scoring calculations. Calculations begin
at the first week and proceed thru the last (if possible).<br /><br />

<b>Set League Status</b> League status will remove the league from view if set no (inactive). 
It can be functionally viewed as a delete.  Setting a league to inactive removes
all access of any kind.  Once the league is made inactive it may be restore only
by the site administrator.  League Admins also loose access.<br /><br >

DEV NOTE: <span style='color:red'>Deactivating the league?</span> A proper reinitialization of the session has not be programmed.  It
is recommended you <b>logout</b> and then <b>login</b> again after a deactivation.  Nothing bad will 
happen, but you'll get strange error messages.";

$current_configuration = "
   <table class='table'>
   <thead>\n
      <tr>
         <th style='text-align:center;'>Description</th>
         <th style='text-align:center;'>Setting</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>First week of play</td>
         <td>$first_round</td>
      </tr>
      <tr>
         <td>Last week of play</td>
         <td>$display_last_round</td>
      </tr>
      <tr>
         <td>League status</td>
         <td>$display_active</td>
      </tr>
      <tr>
         <td>League type</td>
         <td>$display_league_type</td>
      </tr>
      <tr>
         <td>Number of picks</td>
         <td>$display_picks</td>
      </tr>
      <tr>
         <td>Points</td>
         <td>$display_points</td>
      </tr>
      <tr>
         <td>Push</td>
         <td>$display_league_push</td>
      </tr>
   </tbody>
   </table>
";

do_header('MySuperPicks.com - Set League Active Week');
do_nav();

echo "   <div class='container'>\n";
echoContainerBreaks();
echoSessionMessage();

echo "<div class='col-md-6'>";
echo $about;
echo "</div>";
echo "<div class='col-md-6'>";
echo $current_configuration;
echo "</div>";

//localEchoAreYouSureModal();

echo "
      <h1 class='text-center'>Set League Parameters</h1>
      <br />
      <form action='league_operations.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'> 
         <div class='form-group'>
            <label for='pstate' class='col-sm-2 control-label'>Select Last Week of Play:</label>
            <div class='col-sm-4'>
               <div class='input-group'>
                  <select name='lastweek' class='form-control input-medium'>
                  $week_options
                  </select>
                  <span class='input-group-btn'>
                     <button type='submit' class='btn btn-primary' name='submitlastweek' value='1' >Submit</button> (current)
                  </span>
               </div> 
            </div>
         </div>
      </form>\n";
echo "      
      <br />
      <form action='league_operations.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'> 
         <div class='form-group'>
            <label for='pstate' class='col-sm-2 control-label'>Select League Status:</label>
            <div class='col-sm-4'>
               <div class='input-group'>
                  <select name='leaguestatus' class='form-control input-medium'>
                     $active_options
                  </select>
                  <span class='input-group-btn'>
                     <button type='submit' class='btn btn-primary' id='IDb_submitleaguestatus' name='submitleaguestatus'  value='1' >Submit</button>
                  </span>
               </div>
            </div>
         </div>
      </form>\n";
?>
</div>
<?php
do_footer('clean');

// http://stackoverflow.com/questions/22636819/confirm-delete-using-bootstrap-3-modal-box
function localEchoAreYouSureModal(
){

   $javascript = "
   <script type='text/javascript'>
      $(document).ready(function() {
         $('#IDb_submitleaguestatus').on('click', function(e){
            var \$form=$(this).closest('form'); 
            e.preventDefault();
            //alert('here we are');
            $('#confirmx').modal({ backdrop: 'static', keyboard: false })
               .one('click', '#yesiam', function() {
                  \$form.trigger('submit'); // submit the form
            });
         });
      });
   </script>
   ";
   
   $modal = "
<div id='confirmx' class='modal fade' role='dialog'>
  <div class='modal-dialog'>
    Are you sure?
  </div>
  <div class='modal-footer'>
    <button type='button' data-dismiss='modal' name='yesiam' class='btn btn-primary' id='yesiam' value='yesiam' >Yes</button>
    <button type='button' data-dismiss='modal' class='btn'>Cancel</button>
  </div>
</div>
";
   echo $javascript;
   echo $modal;
}
?>