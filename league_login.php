<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: league_login.php
   date: apr-2016
 author: origninal
   desc: Called:
      home->start your league->create league with new account
  notes:  This is a complete page.  It offers to create both a new user and
   create a new league.  So....
   
   If cold both the user and league info must be correct before executing.  Both
   must be completed at the same time.
  
marbles: 
*/

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
require_once 'mypicks_phpgeneral.php';
$msg = '';

/* You arrive here hot or cold - but you arrive here to create
   a new league.  Anyone of the getvars may be mangled.
*/

$new = (isset($_GET['id'])) ? sanitize($_GET['id']) : '';  // TODO shouldn't be used...
$login_request = (isset($_POST['login_request_made'])) ? sanitize($_POST['login_request_made']) : ''; 
$login_user_name = (isset($_POST['login_request_username'])) ? trim(sanitize($_POST['login_request_username'])) : ''; 
$login_password = (isset($_POST['login_request_password'])) ? trim(sanitize($_POST['login_request_password'])) : ''; 

writeDataToFile("League login TOP before login request '$login_request' '$login_user_name' '$login_password'",   __FILE__, __LINE__);

if ($login_request) {
   if (!login($login_user_name, $login_password)) {
      header('location: league_login.php');
      die;
   }
}

writeDataToFile("League login TOP env:\n GET: " .     print_r($_GET, true),     __FILE__, __LINE__);
writeDataToFile("League login TOP env:\n POST: " .    print_r($_POST, true),    __FILE__, __LINE__);
writeDataToFile("League login TOP env:\n SESSION: " . print_r($_SESSION, true), __FILE__, __LINE__);

$usr_is_logged_in = validateUser('user', 'status');
do_header('MySuperPicks.com - League Setup');
do_nav();
?>

<div class="container">
<?php echo_container_breaks();
//writeDataToFile("league_login jst before echosession env:\n SESSION: " . print_r($_SESSION, true), __FILE__, __LINE__);
echoSessionMessage();

$welcome = '';
if ($usr_is_logged_in) {
   $welcome = "Welcome, ".$_SESSION['valid_user'].". ";
}

// http://stackoverflow.com/questions/4526273/what-does-enctype-multipart-form-data-mean?
?>

   <form action="league2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
      <h1 class="text-center">League Setup</h1>
<?php 
$active_week = getActiveWeek();

if(!$usr_is_logged_in) {  // Then he's not logged in and is creating a new user and league.  Show the whole form.
?>             
            <h3>Admin Information</h3>
            <div class="form-group">
                <label for="register_username" class="col-sm-2 control-label">Username</label>
                <div class="col-sm-8">  
                    <input type="text" class="form-control" id="register_username" name="register_username" placeholder="Username" 
                     value="<?php if (isset($_SESSION['register_username'])) { echo $_SESSION['register_username']; }?>" />
                </div>
            </div>
            <div class="form-group">
                <label for="register_fname" class="col-sm-2 control-label">First Name</label>
                <div class="col-sm-8">  
                    <input type="text" class="form-control" id="register_fname" name="register_fname" placeholder="First Name" 
                     value="<?php if (isset($_SESSION['register_fname'])) { echo $_SESSION['register_fname']; } ?>" />
                </div>
            </div>
            <div class="form-group">
                <label for="register_lname" class="col-sm-2 control-label">Last Name</label>  
                <div class="col-sm-8">  
                    <input type="text" class="form-control" id="register_lname" name="register_lname" placeholder="Last Name" 
                    value="<?php if (isset($_SESSION['register_lname'])) { echo $_SESSION['register_lname']; } ?>" />
                </div>
            </div>
            <div class="form-group">
               <label for="register_email" class="col-sm-2 control-label">Email</label>  
               <div class="col-sm-8">  
                  <input type="email" class="form-control" id="register_email" name="register_email" placeholder="Email" 
                     value="<?php if (isset($_SESSION['register_email'])) { echo $_SESSION['register_email']; } ?>" />
               </div>
            </div> <div class="form-group">
               <label or="register_new" class="col-sm-2 control-label">Password</label>  
               <div class="col-sm-8">  
                   <input type="password" class="form-control" id="register_new" name="register_new" placeholder="New Password" 
                     value="<?php if (isset($_SESSION['register_new'])) { echo $_SESSION['register_new']; } ?>" />
               </div>
            </div>
            <div class="form-group">
               <label or="register_new2" class="col-sm-2 control-label">Confirm Password</label>  
               <div class="col-sm-8">  
                   <input type="password" class="form-control" id="register_new2" name="register_new2" placeholder="Confirm New Password" 
                     value="<?php if (isset($_SESSION['register_new2'])) { echo $_SESSION['register_new2']; } ?>" />
               </div>
            </div>
            
<?php
}
?>
            <h3>League Information</h3>
            <div class="form-group">
               <label for="league_name" class="col-sm-2 control-label">League Name</label>
               <div class="col-sm-8">  
                  <input type="text" class="form-control" id="league_name" 
                     name="league_name" placeholder="League Name" 
                     value="<?php if (isset($_SESSION['register_league_name'])) { echo $_SESSION['register_league_name']; } ?>" />
               </div>
            </div>
            <!-- It's a new league.  The name will be unique within the new league  ... let hope. -->
            <div class="form-group">
               <label for="league_player" class="col-sm-2 control-label">Player Name</label>  
               <div class="col-sm-8">  
                  <input type="text" class="form-control" id="league_player" 
                     name="league_player" placeholder="The name displayed in league standings.  It must be unique within the league itself." 
                     value="<?php if (isset($_SESSION['register_league_player'])) { echo $_SESSION['register_league_player']; } ?>" />
               </div>
            </div>            
            <h3>League Settings</h3>
            <div class="form-group">
               <label for="league_type" class="col-sm-2 control-label">League Type</label>  
               <div class="col-sm-8">
                  <label class="radio-inline">
                     <input id="IDi_leaguepickum" type="radio" name="league_type" value="1" 
                        <?php if (isset($_SESSION['register_league_type']) && $_SESSION['register_league_type']=="1") { echo "checked='checked'"; } ?>> Pick'em
                  </label>
                  <!--
                  <label class="radio-inline">
                     <input id="IDi_leaguecohort" type="radio" name="league_type" value="2" 
                        < ? php if (isset($_SESSION['register_league_type']) && $_SESSION['register_league_type']=="2") { echo "checked='checked'"; } ?>> Knockout (cohort)
                  </label>  
                  -->
                  <label class="radio-inline">
                     <input id="IDi_leaguelastman" type="radio" name="league_type" value="3" 
                        <?php if (isset($_SESSION['register_league_type']) && $_SESSION['register_league_type']=="3") { echo "checked='checked'"; } ?>> Survivor (last man)
                  </label>                             
               </div>
            </div>
            <div class="form-group">
               <label for="league_points" class="col-sm-2 control-label">Point spreads are:</label>  
               <div class="col-sm-8">
                  <select class="form-control" name="league_points">
                     <option value="1" <?php if (isset($_SESSION['register_league_points']) && $_SESSION['register_league_points']=="1") { echo "selected"; } ?>>not used</option>
                     <option value="2" <?php if (isset($_SESSION['register_league_points']) && $_SESSION['register_league_points']=="2") { echo "selected"; } ?>>used and set by MySuperPicks</option>
                  </select>               
               </div>
            </div>
            <div class="form-group" id="IDd_pickumcount" >
               <label for="league_picks" class="col-sm-2 control-label"># of Pick Per Week</label>  
               <div class="col-sm-8">
                  <!-- <select id="IDs_pickumcount" class="form-control" name="league_picks"> -->
                  <select id="IDs_pickumcount" class="form-control" name="league_picks">
                     <option value="1" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="1") { echo "selected"; } ?>>1</option>
                     <option value="2" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="2") { echo "selected"; } ?>>2</option>
                     <option value="3" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="3") { echo "selected"; } ?>>3</option>
                     <option value="4" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="4") { echo "selected"; } ?>>4</option>
                     <option value="5" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="5") { echo "selected"; } ?>>5</option>
                     <option value="6" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="6") { echo "selected"; } ?>>6</option>
                     <option value="7" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="7") { echo "selected"; } ?>>7</option>
                     <option value="8" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="8") { echo "selected"; } ?>>8</option>
                     <option value="9" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="9") { echo "selected"; } ?>>9</option>
                     <option value="10" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="10") { echo "selected"; } ?>>10</option>
                     <option value="11" <?php if (isset($_SESSION['register_league_picks']) && $_SESSION['register_league_picks']=="11") { echo "selected"; } ?>>All Games</option>
                  </select>               
               </div>
            </div>
            <div class="form-group" id="IDd_seasonbegins" >
               <label for="league_seasonbegins" class="col-sm-2 control-label">First Week of Play</label>  
               <div class="col-sm-8">
                  <select id="IDs_seasonbegins" class="form-control" name="league_seasonbegins">
                     <?php
                     $selected_week = (isset($_SESSION['register_league_start'])) ? $_SESSION['register_league_start'] : '';
                     $last_legal_week = NFL_LAST_WEEK;
                     $last_legal_week--;
                     for($week = $active_week; $week <= $last_legal_week; $week++) {
                        $selected = ($selected_week == $week) ? 'selected' : '';
                        echo "<option value='$week' $selected> Week $week</option>\n";
                     }
                     ?>
                  </select>               
               </div>
            </div>
            <div class="form-group" id="IDd_pushvalue" >
               <label for="league_push" class="col-sm-2 control-label">A Push is Worth</label>  
               <div class="col-sm-8">
                  <select id="IDs_pushvalue" class="form-control" name="league_push"> <!-- ops vals were 0, .5 and 1.  changed to 1, 2, 3  4/21/16 hfs  Problem .5 would not store in int type -->
                     <option value="1" <?php if (isset($_SESSION['register_league_push']) && $_SESSION['register_league_push']=="1") { echo "selected"; } ?>>0 Points</option>
                     <option value="2" <?php if (isset($_SESSION['register_league_push']) && $_SESSION['register_league_push']=="2") { echo "selected"; } ?>>0.5 points</option>
                     <option value="3" <?php if (isset($_SESSION['register_league_push']) && $_SESSION['register_league_push']=="3") { echo "selected"; } ?>>1 point</option>
                  </select>               
               </div>
            </div>

            <input type="hidden" name="setting" value="
               <?php
                  if(isset($_SESSION['valid_user'])) {
                     echo $_SESSION['valid_user'];
                  } else {
                     echo $new;
                  }
               ?>"
            />
            <div class="form-group">
               <div class="text-center">
                  <button type="submit" class="btn btn-warning"><span class="glyphicon glyphicon-ok"></span> Submit League Registration</button>
               </div>
             </div>
          </form>

<br />
<br />
</div> 
<?php
do_footer('bottom');
?>