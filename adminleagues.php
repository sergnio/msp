<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: adminleagues3.php
   date: jul-2016
 author: original
   desc: 
      URL_HOME_PAGE

   note:
   
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

validateUser('admin');

$selected_league_id = getSessionInfo('adminleagueselect');
$league_status_select = getSessionInfo('adminleaguestatusselect');
writeDataToFile("3 session info: '$selected_league_id', '$league_status_select'", __FILE__, __LINE__);
$league_status_select = ($league_status_select == '') ? 'active' : $league_status_select;
$league_status_checkbox_all_checked = ($league_status_select == 'both') ? "checked='checked'" : '';

$active_status_message = '';
if ($league_status_select == 'active') {
   $active_status_message = "(Only active leagues are currently available as selection options.)";
} else {
   $active_status_message = "(Both active and inactive leagues are currently available as selection options.)";
}

writeDataToFile("getleaguenames '$league_status_select'", __FILE__, __LINE__);
$a_leagues = array(); 
if (!getLeagueNamesIDsArray($a_leagues, $league_status_select)) { 
   formatSessionMessage("League information is unavailable.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header ('Location: admin.php');
   die();
}

writeDataToFile("adminleaguesuelelldl '$selected_league_id'", __FILE__, __LINE__);

$select_options = '';
foreach ($a_leagues as $league_name => $league_id) {
   $selected = ($selected_league_id == $league_id) ? "selected='selected'" : '';
   $select_options .= "                  <option value='$league_id' $selected >$league_name</option>\n";
}

$league_id      = '';
$league_name    = '';
$commissioner   = '';
$found_date     = '';
$league_type    = '';
$league_points  = '';
$league_push    = '';
$active         = '';
$firstround     = '';
$lastround      = '';

$status = 1;
$table = '';
$table_players = '';
while ($selected_league_id) {
   $status = 0;
   
   $mysql = "
      select league_id,
             league_name,
             commissioner,
             found_date,
             league_type,
             league_points,
             league_picks,
             league_push,
             active,
             firstround,
             lastround
        from league
       where league_id = ?";
   
   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   $sth->bind_param("i", $selected_league_id);
   $sth->execute();
   $sth->bind_result($league_id       ,
                     $league_name     ,
                     $commissioner    ,
                     $found_date      ,
                     $league_type     ,
                     $league_points   ,
                     $league_picks    ,
                     $league_push     ,
                     $active          ,
                     $firstround      ,
                     $lastround );
   if (!$sth->fetch()) {
      break;
   }
   
   $table = "<div style='align-center;'>
<table id='single' class='table table-hover table-striped table-bordered'>\n
   <thead>
      <tr>
         <th style='text-align:center' >Parameter</th>\n
         <th style='text-align:center' >Value</th>\n
         <th style='text-align:center' >Description</th>\n
      </tr>
   </thead>
   <tbody>
   <tr>
      <td style='text-align:right' ><b>LN</b><br />league name<br /></td>
      <td style='text-align:center;'><input type='text' name='leaguename' value='$league_name' /></td>
      <td>The displayed name of the league.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>COM</b><br />commissioner</td>
      <td style='text-align:center;'><input type='text' name='commissioner' value='$commissioner' /></td>
      <td>The commmissioner of the league.  The commissioner has special league administrative privileges.</td> 
   </tr>   
   <tr>
      <td style='text-align:right' ><b>DFN</b><br />date founded</td>
      <td style='text-align:center;'><input type='text' name='founddate' value='$found_date' /></td>
      <td>The date the league was created.<br /><b>'YYYY-MM-DD 24:MM:SS'</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>LGT</b><br />leaguetype</td>
      <td style='text-align:center;'><input type='text' name='leaguetype' value='$league_type' /></td>
      <td>League type - what game is playing?<br /><b>[1,2,3]=[pickem,cohort,lastman]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>PTS</b><br />points</td> 
      <td style='text-align:center;'><input type='text' name='leaguepoints' value='$league_points' /></td>
      <td>In Pickem, this determines whether Vegas spreads are used in determining the winning team.<br /><b>[1,2]=[no odds,odds]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>PCK</b><br />picks</td>
      <td style='text-align:center;'><input type='text' name='leaguepicks' value='$league_picks' /></td>
      <td>In Pickem, this number determines how many games are selected each week.  Historically, 5 game picks per week were used. This is ignored in KO leagues.  
      KO leagues get only one pick.<br /><b>[1..10,11] = [1..10 picks, 11 ALL GAMES]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>PSH</b><br />push</td>
      <td style='text-align:center;'><input type='text' name='leaguepush' value='$league_push' /></td>
      <td>This defines how tied game are scored.  Ties can be awarded 0, 1/2 or 1 point.<br /><b>[1,2,3]=[zero,half,one]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>ACT</b><br />active</td>
      <td style='text-align:center;'><input type='text' name='leagueactive' value='$active' /></td>
      <td>Is the league active; in use.  If not, all access is removed.  Retire leagues by deactivating them.<br /><b>[0,1]=[inactive,active]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>FRN</b><br />first round</td>
      <td style='text-align:center;'><input type='text' name='firstround' value='$firstround' /></td>
      <td>Defines the first round of play in the 17 week season.  No games may start on the last week of the season.<br /><b>[1...16]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>LRN</b><br />last round</td>
      <td style='text-align:center;'><input type='text' name='lastround' value='$lastround' /></td>
      <td>This marks the league play as complete.  It is 0 while the league is in play.  A league with last round set can be viewed.  
      Play is prohibited.<br /><b>[0...17] RULE: if LRN != 0 then LRN > FRN</b></td>
   </tr>
   </tbody>
</table>
</div>";
   $sth->close();
   $status = 1;
   break;
}

if ($status == 0) {
   formatSessionMessage("League information is not available.", 'danger', $msg);
   setSessionMessage($msg, 'error');
   header ('Location: adminleagues.php');
   die();
}


$disable_editing = "";
$disable_user_id =         "disabled='disabled'";
$disable_username =        "disabled='disabled'";
$disable_firstname =       "disabled='disabled'";
$disable_lastname =        "disabled='disabled'";
$disable_email =           "disabled='disabled'";
$disable_site_active =     "disabled='disabled'";
$disable_player_acitve =   "";
$disable_player_name =     "";
$disable_player_paid =     "";
$disable_player_join_date =     "";
$num_players = '';

$status_players = 0;
while (1) {
  
   $mysql_players = "     
      select u.id,
             u.username, 
             u.fname, 
             u.lname,
             u.usermode,
             y.playername, 
             if(y.paid = 2, 'yes', 'no'),
             y.joindate,
             u.email,
             g.commissioner,
             if(u.active_status = 1, 'yes', 'no'), 
             if(y.active = 2, 'yes', 'no')
        from nspx_leagueplayer as y, users as u, league as g
       where y.userid = u.id
         and y.leagueid = ?
         and g.league_id = y.leagueid
       order by y.playername";
   
   $conn = db_connect();
   $sth = $conn->prepare($mysql_players);
   $sth->bind_param("i", $selected_league_id);
   $sth->execute();
   $sth->bind_result($user_id,
                     $user_name, 
                     $first_name, 
                     $last_name,
                     $user_mode,
                     $player_name,
                     $paid,
                     $join_date,
                     $email, 
                     $commissioner,
                     $active_site, 
                     $active_player);
   
   $result = $sth->store_result();
   $num_players = $sth->num_rows;
   writeDataToFile("Number of players in league are $num_players, league id is $selected_league_id", __FILE__, __LINE__);
   
   $table_players = "
   <div style='align-center;'>
   <table id='single' class='table table-hover table-striped table-bordered'>\n" .
      "   <thead>\n" .
      "      <tr>\n" .
      "         <th>ID<br />(site)</th>\n" .
      "         <th>Active<br />(site)</th>\n" .
      "         <th>Username<br />(site)</th>\n" .
      "         <th>First Name<br />(site)</th>\n" .
      "         <th>Last Name<br />(site)</th>\n" .
      "         <th>Email<br />(site)</th>\n" .
      "         <th>Player Name<br />(league)</th>\n" .
      "         <th>Joined<br />(player)</th>\n" .
      "         <th>Active<br />(player)</th>\n" .
      "         <th>Paid<br />(player)</th>\n" .
      "         <th>Edit<br /></th>\n" .
      "      </tr>\n" .
      "   </thead>\n" .
      "   <tbody>\n";
      
   while ($sth->fetch()) {
      $commissioner_highlight = '';
      
      if ($commissioner == $user_id) {
         $commissioner_highlight = "style='background-color:lightblue;'";
      }
      $table_players .= "      <tr $commissioner_highlight >\n";
      $table_players .= "         <td><input type='text'       name='playeruserid[$user_id]'  value='$user_id'     style='max-width:35px;'  $disable_user_id     /> </td>\n";
      $table_players .= "         <td><input type='text'   name='playersiteactive[$user_id]'  value='$active_site' style='max-width:40px;'  $disable_site_active />
                                     <input type='hidden'    name='playerusertype[$user_id]'  value='$user_mode' />
         </td>\n";

      $table_players .= "         <td><input type='text'     name='playerusername[$user_id]'  value='$user_name'   style='max-width:120px;' $disable_username    /> </td>\n";
      $table_players .= "         <td><input type='text'        name='playerfname[$user_id]'  value='$first_name'  style='max-width:80px;'  $disable_firstname   /> </td>\n";
      $table_players .= "         <td><input type='text'        name='playerlname[$user_id]'  value='$last_name'   style='max-width:80px;'  $disable_lastname    /> </td>\n";
      $table_players .= "         <td><input type='email'       name='playeremail[$user_id]'  value='$email' $disable_email /></td>\n";
      $table_players .= "         <td><input type='text'   name='playerplayername[$user_id]'  value='$player_name' style='max-width:150px;' $disable_player_name /> </td>\n";
      $table_players .= "         <td><input type='text'     name='playerjoindate[$user_id]'  value='$join_date'   style='max-width:150px;' $disable_player_join_date /> </td>\n";

      $selected_yes = ($active_player == 'yes') ? "selected='selected'" : '';
      $selected_no =  ($active_player == 'no') ?  "selected='selected'" : '';
      $selected_paid_yes = ($paid == 'yes') ? "selected='selected'" : '';
      $selected_paid_no =  ($paid == 'no') ?  "selected='selected'" : '';
      
      // TODO need hidden input
      $table_players .= "         <td>\n";
      $table_players .= "            <select name='playeractive[$user_id]' class='form-control input-medium' $disable_player_acitve >\n";
      $table_players .= "               <option value='yes' $selected_yes >yes</option>\n";
      $table_players .= "               <option value='no'  $selected_no >no</option>\n";
      $table_players .= "            </select>\n";
      $table_players .= "         </td>";
      
      $table_players .= "         <td>\n";
      $table_players .= "            <select name='playerpaid[$user_id]' class='form-control input-medium' $disable_player_paid >\n";
      $table_players .= "               <option value='yes' $selected_paid_yes >yes</option>\n";
      $table_players .= "               <option value='no'  $selected_paid_no >no</option>\n";
      $table_players .= "            </select>\n";
      $table_players .= "         </td>";
      
      $table_players .= "
         <td>
            <span class='input-group-btn'>
               <button type='submit' name='playereditbutton[$user_id]' class='btn btn-primary' value='$user_id' >Edit</button>
            </span>
         </td>\n";
      $table_players .= "      </tr>\n";
   }
   $table_players .= "   </tbody>\n";
   $table_players .= "</table>\n";
   $table_players .= "</div>\n";
   
   $num_players = $sth->num_rows;
   @ $sth->close();
   $status = 1;
   break;
}

do_header('MySuperPicks.com - Admin Leagues');
do_nav();
?> 
<div class="container">
<?php echo_container_breaks();
echoSessionMessage();
?>

   <div class="hidden-sm hidden-md hidden-lg">
      <br />S
      <br />
      <br />
   </div>
   <h1 class="text-center">Leagues Administration</h1>
   <h5  class="text-center">NB! - Most of these values are now checked... but know what you're doing.</h5><br />
   <form action="adminleagues2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
      <div class="form-group">
         <label for="pstate" class="col-sm-2 control-label">Select League:<br />Selecting ABONDONS pending edits!</label>
         <div class="col-sm-8">
<?php 
echo "               
            <div class='input-group'>
               <select name='leagueselector' class='form-control input-medium'>\n";
echo $select_options;
echo "               </select>
               <span class='input-group-btn'>
                  <button type='submit' class='btn btn-primary'>Submit</button>
               </span>
            </div>
            <br />
            <input type='checkbox' name='activeleaguesonly' $league_status_checkbox_all_checked > Show all Leagues $active_status_message
            <input type='hidden' name='submit' value='true' />
         </div>
      </div>
   </form>\n";


if ($selected_league_id) {
   echo "
<br />
   <form action='adminleagues2.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data' >
      <div class='form-group'>
         <div class='col-sm-12'>
         <h4>The League</h4>
            <div class='input-group'>
$table
            </div>
            <br />
            <span class='input-group-btn'>
               <button type='submit' class='btn btn-primary'>Edit League</button>
            </span>
            <input type='hidden' name='editleague' value='edit' />
            <input type='hidden' name='editleagueid' value='$selected_league_id' />
         </div>
      </div>
   </form>
<br />\n";
}

if ($selected_league_id) {
   echo "
   <form action='adminleagues2.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'> 
      <div class='form-group'>
         <div class='col-sm-12'>
         <h4>The Players &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><span style='background-color:lightblue;'>&nbsp;&nbsp;( commissioner )&nbsp;&nbsp;</span></small> </h4>
            <div class='input-group'>
$table_players
            </div>
            <br />
            <input type='hidden' name='editplayer' value='edit' />
            <input type='hidden' name='editleagueid' value='$selected_league_id' />
         </div>
      </div>
   </form>";
}

if ($selected_league_id) {
echo "
</div>";
}
do_footer('clean');
?>
