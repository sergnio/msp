<?php
/*
:mode=php:

   file: site_fns_diminished.php
   date: apr-2016
 author: origninal
   desc: A support and definintions file.  HTML header and footer are posted
        here.  This is a diminished (abridged) copy of the original file
        site_fns.php.  The global definitions and standard behaviors were
        removed to mypicks_def.php.  The file mypicks_def.php must be included
        before this file as it contains information required by this file.
        Expect to see the sequence

               require_once('mypicks_def.php');
               require_once('site_fns_diminished.php');
      function setFocusToUserName() {
function checkArray(form,arrayName)
function check_valid_user() {
function check_valid_user_admin() {
function checkboxlimit(checkgroup, limit) {
function cmp($a, $b)
function create_code() {
function db_connect() {
function do_footer(
function do_header(
function do_login_form() {
function do_nav() {
function do_password_form($msg = '') {
function do_password_form($msg) {
function echoAccessViolation () {
function echoContainerBreaks($breaks = BOOTSTRAP_NAVBAR_BREAKS_DEFAULT) {
function echoEditLeagueUsers(
function echoEditSiteUsers(
function echoLoginAttempt (   // return: false on login failure message - true on all else
function echoLoginAttempt () {
function echo_container_breaks($breaks = BOOTSTRAP_NAVBAR_BREAKS_DEFAULT) {
function filled_out($form_vars) {
function generateNewPassword(&$password, &$hash) {
function generate_username($username, $iteration) {
function get_inbox() {
function get_league_text($type) {
function get_messageboard($league_id) {
function get_my_league($admin) {  // $admin $_SESSION['league_admin'] set at login($username, $password)
function get_my_league($admin) {  // This is the commission page.
function get_my_leagues($league) {  // Doesn't seem to be used - see get_my_league()
function get_my_picks($week) {
function get_my_picks_page($week) {
function get_picks($week) {
function get_picks_history($week) {
function get_public_picks_moved_to_picksphp($week) {
function get_public_picks_old($week) {
function get_random_word() {
function get_schedules($week) {
function get_schedules_admin($week) {  //nsp 4/2016
function get_sent() {
function get_standings_season($week) {
function get_standings_season_test($week) {
function get_standings_week($week) {
function get_standings_week_test($week) {
function get_text(
function get_text($type) {
function get_users(
function html_sanitize($sCode) {
function notify_password($fname, $email, $password) {
function pick_drop_down($week) {
function put_pagination_weekly_remove(
function put_pagination_weekly_remove_xxxx(
function reset_password($user_id, $hash, &$ref_status_text) {
function reset_password($username) {
function sanitize($sCode) {
function sql_sanitize($sCode) {
function valid_email($address) {
function validate(form) {
function validatePhone($cell) {
function validateUser(
function validate_flag($flag) {
function view_mail($id) {
*/

require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

function db_connect() {
   global $global_mysuperpicks_dbo;
//   $global_mysuperpicks_dbo = new mysqli(HOST, USER_NAME, USER_PASSWORD, DATABASE_NAME,Allow Zero Datetime=true);  Allow Zero Datetime=True
   $global_mysuperpicks_dbo = new mysqli(HOST, USER_NAME, USER_PASSWORD, DATABASE_NAME, 3307);
   // TODO: Finish testing, forcing the connect to localhost
//   $global_mysuperpicks_dbo = new mysqli('127.0.0.1', 'root', '', 'mysuperpicks', 3306);
   if ($global_mysuperpicks_dbo->connect_errno) {
      $er = 'errno: ' . $global_mysuperpicks_dbo->connect_error;
      if (isset($global_mysuperpicks_dbo->error)) {
         $er .= '\nerrmsg: ' . $global_mysuperpicks_dbo->error;
      }
      $ermsg = array('ERROR_MESSAGE'=>'Failed to create db handle',
         'HOST'=>HOST, 'DATABASE_NAME'=>DATABASE_NAME,
         'USER_NAME'=>USER_NAME, 'USER_PASSWORD'=>USER_PASSWORD,
         'MYSQL_CONNECTION_ERROR' => $er);
      writeDataToFile($ermsg, __FILE__, __LINE__);
      return false;
   } else {
     return $global_mysuperpicks_dbo;
   }

}

// Modified 4/2016 hfs
// Additional default parameter 'include_analytics' added along with code
// testing its value.  Analytics will now be written inside the header.
function do_header(
   $title = 'No Title Supplied',
   $include_analytics = false
) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <title><?php echo $title; ?></title>

   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="<?php echo $title; ?>"/>
   <meta name="keywords" content="mysuperpicks">

   <!-- copy-paste https://developers.google.com/speed/libraries/#jquery -->
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>  <!-- ok 2016/07/07 -->

   <!-- copy-paste  http://getbootstrap.com/getting-started/ April 2016 hfs -->
      <!-- Latest compiled and minified CSS  ok 2016/07/07 -->
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
         integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
      <!-- Optional theme  ok 2016/07/07 -->
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
         integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
      <!-- Latest compiled and minified JavaScrip  ok 2016/07/07 -->
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
         integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

   <!-- moved from footer  apr2016 hfs -->
   <!--<script src="../../assets/js/docs.min.js"></script> -->
   <!-- <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script> -->
   <script src="mypicks01.js"></script>
   <script src="mypicks02.js"></script>
   <script src="mypicks03.js"></script>
   <script src="mypicks04.js"></script>
   <script src="mypicks05.js"></script>
   <script src="mypicks06.js"></script>
    <script type="text/javascript" src="sorttable.js"></script>

   <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
   <link rel="stylesheet" href="css/style.css?ver=1.12" type="text/css" />
   <link rel="stylesheet" href="css/site.css?ver=1.12" type="text/css" />

   <!-- https://curioussolutions.github.io/DateTimePicker/ -->
   <!-- <link rel="stylesheet" href="css/DateTimePicker.css" type="text/css"/> -->
   <link rel="stylesheet" href="css/dtp130/DateTimePicker.css" type="text/css"/>    <!-- 0.1.30 ok 2016/07/07 -->
   <!-- <script type="text/javascript" src="js/DateTimePicker.js"></script> -->
   <script type="text/javascript" src="js/dtp130/DateTimePicker.js"></script>       <!-- 0.1.30 ok 2016/07/07 -->

   <link rel="stylesheet" href="css/chosen.css">

   <script type="text/javascript" src="js/moment.js"></script>    <!-- 2.14.1     ok 2016/07/07-->
   <script src="ckeditorfull459/ckeditor.js"></script>            <!-- 4.5.9 full ok 2016/07/07 -->
   <!-- <script src="ckeditorstd459/ckeditor.js"></script> -->    <!-- 4.5.9 std  ok 2016/07/07 -->

<?php
   if ($include_analytics == true) {
      if (BEHAVIOR_ANALYTICS == 'ENABLE') {
         include_once('analyticstracking.php');
      }
   }
?>
</head>
<?php
} // end function do_header()

function do_nav(
   $my_onload_func = ''
) {
   echo "<body onload='$my_onload_func' >\n";
?>
<!-- begin do_nav() -->
   <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
         <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
               <span class="sr-only">Toggle navigation</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" id="logo_title" href="#">MySuperPicks.com
<?php
if (check_valid_user()) {
           $user_name =   $_SESSION['valid_user'];
           $league_name = (isset($_SESSION['league_name'])) ? $_SESSION['league_name'] : 'no name';
           $player_name = (isset($_SESSION['league_player_name'])) ? $_SESSION['league_player_name'] : 'no player name';
           $is_commisioner_display = (!empty($_SESSION['league_admin'])) ? '(commissioner)' : '';
           $active_week_display = (!empty($_SESSION['active_week'])) ?$_SESSION['active_week'] : '';
           echo "
              <small id='welcome'>League - <span style='color:white;'>$league_name</span>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Player - <span style='color:white;'>$player_name</span>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; User - <span style='color:white;'>$user_name $is_commisioner_display</span>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Week - <span style='color:white;'>$active_week_display</span>
              </small>";
}
?>
            </a>
         </div>  <!-- END navbar-header -->
         <div class="navbar-collapse collapse pull-right">
            <ul class="nav navbar-nav">
               <li <?php if ($_SERVER['PHP_SELF'] == '/index.php') { echo 'class="active"';} ?>>
                 <a href="index.php">Home</a>
               </li>
               <li <?php if ($_SERVER['PHP_SELF'] == '/picks_switch.php') { echo 'class="active"';} ?>>
                 <a href="picks_switch.php">This Week&apos;s Games</a>
               </li>
               <li>
                 <a href="https://www.nfl.com/standings" target="nflstandings">NFL Standings</a>
               </li>
               <li <?php if ($_SERVER['PHP_SELF'] == '/against-the-spread.php') { echo 'class="active"';} ?>>
                 <a href="against-the-spread.php">Records ATS</a>
               </li>
               <li <?php if ($_SERVER['PHP_SELF'] == '/standings_switch.php') { echo 'class="active"';} ?>>
                 <a href="standings_switch.php">Standings</a>
               </li>
               <li <?php if ($_SERVER['PHP_SELF'] == '/contact.php') { echo 'class="active"';} ?>>
                 <a href="contact.php">Contact Us</a>
               </li>
<?php
if (isset($_SESSION['league_admin']) && !empty($_SESSION['league_admin'])) {
?>
               <li <?php if ($_SERVER['PHP_SELF'] == '/league_management.php') { echo 'class="active"';} ?>>
                 <a href="league_management.php">League Commissioner</a>
               </li>
<?php
}
if (isset($_SESSION['usermode'])) {
   if ($_SESSION['usermode']=='admin') {
?>
               <li <?php if ($_SERVER['PHP_SELF'] == '/admin.php') { echo 'class="active"';} ?>>
                 <a href="admin.php">Admin</a>
               </li>
<?php
   }
}
if (check_valid_user()) {
?>

               <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Member Area<b class="caret"></b></a>
                  <ul class="dropdown-menu">
                     <li <?php if ($_SERVER['PHP_SELF'] == '/mypicks.php') { echo 'class="active"';} ?>>
                       <a href="mypicks.php"><span class="glyphicon glyphicon-list-alt"></span> My Picks</a>
                     </li>
                     <li <?php if ($_SERVER['PHP_SELF'] == '/inbox.php') { echo 'class="active"';} ?>>
                       <a href="inbox.php"><span class="glyphicon glyphicon-envelope"></span> Inbox</a>
                     </li>
                     <li <?php if ($_SERVER['PHP_SELF'] == '/messageboard.php') { echo 'class="active"';} ?>>
                       <a href="messageboard.php"><span class="glyphicon glyphicon-comment"></span> League Messageboard</a>
                     </li>
                     <li <?php if ($_SERVER['PHP_SELF'] == '/profile.php') { echo 'class="active"';} ?>>
                       <a href="profile.php"><span class="glyphicon glyphicon-user"></span> My Profile</a>
                     </li>
                     <li <?php if ($_SERVER['PHP_SELF'] == '/league_player_names.php') { echo 'class="active"';} ?>>
                       <a href="league_player_names.php"><span class="glyphicon glyphicon-user"></span> My Names</a>
                     </li>
                     <li class="divider"></li>
                     <li class="dropdown-header">My Leagues</li>
<?php
   $league = explode('-', $_SESSION['leagues']);
   $conn = db_connect();
   foreach ($league as $league_id) {
      if(is_numeric($league_id)) {
         $result = $conn->query("SELECT * FROM league WHERE league_id='$league_id' and active = 1");  // These are the leagues
         if($row=$result->fetch_object()) {
            $league_name = $row->league_name;
            if($_SESSION['league_id'] == $league_id) {?>

                     <li><?php
               echo '                  <a href="change_league.php?leagueid='.$league_id.'" style="color:red;">'.$league_name.'</a></li>'; // Is marking as presently selected
            } else {               ?>

                     <li><?php
               echo '                  <a href="change_league.php?leagueid='.$league_id.'">'.$league_name.'</a></li>';
            }
         }
      }
   }
   mysqli_close($conn);
?>

                     <li class="divider"></li>
                     <li>
                       <a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a>
                     </li>
                  </ul>
               </li>
<?php
} else {
?>
               <li class="dropdown">
                 <a href="#"  data-toggle="modal" data-target="#loginModal">Log In<b class="caret"></b></a>

               </li>
<?php
}
?>            </ul>
         </div>
      </div>

   </div>
	<div class="modal fade" id="loginModal">
		<form method="post" action="login.php">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h4 class="modal-title">Login</h4>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div>
		      <div class="modal-body">

                       Username/Email<br /><input type="text" id='IDi_username' class="form-control" name="username" autocomplete="off" /><br />
                       Password<br /><input type="password" class="form-control" name="password" onkeydown = "if (event.keyCode == 13) document.getElementById('mainLoginBtn').click()"  /><br />
                       <a href="password_reset.php">Forgot password?</a><br /><br />
                       <input type="hidden" name="url" value="<?php echo $_SERVER['PHP_SELF']; ?>" />
		      </div>
		      <div class="modal-footer">
		        <button type="submit" id="mainLoginBtn" class="btn btn-primary">Login</button>
		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
		      </div>
		    </div>
		  </div>
		 </form>
		</div>

		<script>
		$("#loginModal").on("shown.bs.modal", function () {
    		$("#IDi_username").focus();
		})
		</script>
<!-- END do_nav() -->
<?php
}

function do_footer(
   $type = 'bottom'
) {
   if ($type === 'clean') {
?>
</body>
</html>
<?php
   } else {
?>
   <!-- FOOTER -->

   <footer class="footer<?php if ($type == 'bottom') { echo ' fixed-footer';} ?> hidden-xs">
   	  <div class="scoreboardWrapper fixed-footer"><div class="scoreboard"><?php echo get_text2('scoreboard-footer');?></div></div>
      <br />
      <div class="text-center">
         <p>Copyright &#169;<?php echo date('Y'); ?><br />
            <?php echo '<a href="' . URL_HOME_PAGE . '" id="footer_link">MySuperPicks.com</a>' ?>
         </p>
      </div>
   </footer>
</body>
</html>
<?php
   }
}

function sanitize($sCode) {
   return html_sanitize(sql_sanitize($sCode));
}

function sql_sanitize($sCode) {
   $sCode = addslashes($sCode); // Precede sensitive characters with a slash \
   return $sCode; // Return the sanitized code
}

function html_sanitize($sCode) {
   $sCode = strip_tags($sCode);
   $sCode = htmlspecialchars($sCode);
   return $sCode; // Return the sanitized code
}

function valid_email($address) {
   // check if an email address is possibly valid
   if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
     return true;
   } else {
     return false;
  }
}

function validatePhone($cell) {  // Cut this out
   $numbersOnly = preg_replace("/[^0-9]/", "", $cell);
   $pattern = "/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i";
   $numberOfDigits = strlen($numbersOnly);
   if ((preg_match($pattern, $numbersOnly)) || ($numberOfDigits === 7) || ($numberOfDigits === 10)) {
      return true;
   } else {
      return false;
   }
}

function contact_form_filled_out($form_vars) {
    return (empty($form_vars['message']) || empty($form_vars['name']) || empty($form_vars['email']));
}

function filled_out($form_vars) {
  // if name or message is empty, fail
  if (empty($form_vars['contactname']) || empty($form_vars['contactmessage'])) {
    return false;
  }
   // test that each variable has a value
   // foreach ($form_vars as $key => $value) {
   //    if ((!isset($key)) || ($value == '')) {
   //       return false;
   //    }
   // }
   return true;
}

function validate_flag($flag) {
   if ($flag == 'submit') {
      return true;
   } else {
      return false;
   }
}
function do_login_form() {
?>
<br />
<br />
<br />
<div class=\"alert alert-success\">
   <h4>Your new password has been emailed to the address that is associated with your account. This may take several minutes to reach you. Thank you.</h4>
</div>
<div id="test_uploads" class="panel panel-default">
   <div class="panel-heading">
      <h3 class="panel-title">Log In</h3>
   </div>
   <div class="panel-body text-center">
      <form method="post" action="login.php">
         Username<br /><input type="text" name="username" size="20" class="input" /><br />
         Password<br /><input type="password" name="password" size="20" class="input" /><br /><br />
         <input type="hidden" name="url" value="<?php echo $_SERVER['PHP_SELF']; ?>" />
         <button type="submit" class="btn btn-lg btn-default">Log In YYY</button>
      </form>
   </div>
   <div id="password" class="text-center">
      <a href="password_reset.php">Forgot Password?</a>
      <br />
      <br />
   </div>
</div>
<br /><br /><br /><br /><br /><br />
<?php
}

function do_password_form($msg = '') {
   $value = ($msg) ? "value='$msg'" : '';
?>
<div id="test_uploads" class="panel panel-default">
   <div class="panel-heading">
      <h3 class="panel-title">Password Reset Form</h3>
   </div>
   <div class="panel-body text-center">
      <form method="post" action="password_reset2.php">
         <h4 style='text-align:center;'>Enter either <span style='color:blue;'><i>login user name</i></span> or <span style='color:blue;'><i>email address</i></span></h4>
         <br />
         <div style='text-align:center;'>
         <?php echo "<input type='text' name='username' size='50' maxlength='100' class='input' $value /></div>"; ?>
         <br />
         <button type="submit" class="btn btn-default">Reset Password</button>
      </form>
   </div>
</div>
<br /><br /><br /><br /><br /><br />
<?php
}

function check_valid_user_admin() {
   // see if somebody is logged in and notify them if not
   if (isset($_SESSION['valid_user']))  {
      if (($_SESSION['active'] == '1')
        && (($_SESSION['usermode'] == 'admin') || ($_SESSION['league_id']==$_SESSION['league_admin'])))
      {
         return true;
      } else {
         // they are not logged in
        return false;
      }
   }
}

function check_valid_user() {
   // see if somebody is logged in and notify them if not
   if (isset($_SESSION['valid_user']))  {
      if ((($_SESSION['usermode'] == 'admin') || ($_SESSION['usermode'] == 'user'))
        && ($_SESSION['active'] == '1'))
      {
         return true;
      } else {
         // they are not logged in
         return false;
      }
   }
}

function get_random_word() {
   // The numeric value below indicates the password length
   $length = '8';

   $var = "abcdefghijkmnpqrstuvwxyz23456789ABCDEFGHIJKLMNPQRSTUVWXZY!@#";
   srand((double)microtime()*1000000);
   $i = 0;    $word = '' ;
   while ($i < $length) {
       $num = rand() % 33;
       $tmp = substr($var, $num, 1);
       $word = $word . $tmp;
       $i++;
   }
   return $word;
}

function generateNewPassword(&$password, &$hash) {
   $status = 0;
   while(1) {
      $password = get_random_word();
      if($password == false) {
         break;
      }
      $hash = hash('sha256', $password);
      $status = 1;
      break;
   }
   return $status;
}

function reset_password($user_id, $hash, &$ref_status_text = '') {
   $mysql = '
      update users
        set password = ?
      where id = ?';

   $ref_status_text = '';
   $status = 0;
   while(1) {
      try {
         $conn = db_connect();
         $sth = $conn->prepare($mysql);
         $sth->bind_param("si", $hash, $user_id);
         if (!$sth->execute()) {
            $ref_status_text = "fail";
            $sth->close();
            break;
         }
         $status = 1;
         break;
      } catch (mysqli_sql_exception $e) {
         $ermsg = "reset_password()  \n" .
            'sql: ' . $mysql . " \n\n" .
            'user_id ' . $user_id . " \n" .
            'ref_status_text ' . $ref_status_text . " \n" .
            'MYSQL ERROR TO STRING: ' . $e->__toString();
         writeDataToFile($ermsg, __FILE__, __LINE__);
         $ref_status_text = 'dberror';
      }
      break;
   }
   return $status;
}

function notify_password($fname, $email, $password) {
   // notify the user that their password has been changed
   $subject = "MySuperPicks Login Information";
   $from = "From: info@mysuperpicks.com";
   $mesg =
     "Dear ".$fname.":\r\n\n".
      "Your password has been changed to [  ".$password."  ]. If you would like " .
      "to change it the next time you log in, please click on the My Profile " .
      "link in the navigation and enter your new password.\r\n\n"."If you need " .
      "anything else, just let us know. Thank you.\r\n\n" .
      "Sincerely,\r\n\n" .
      "MySuperPicks.com";
    if (mail($email, $subject, $mesg, $from)) {
       return true;
    } else {
       return false;
    }
}

function get_schedules($week) {
   echo "\n";
   echo "<tr><form action=\"scheduleedit.php\" method=\"post\" class=\"form-horizontal\" role=\"form\" enctype=\"multipart/form-data\">";
   echo "<table id=\"single\" class=\"table table-hover table-striped\">\n";
   echo "   <thead><th>Date/Time</th>\n";
   echo "      <tr>\n";
   echo "         <th>Home Team</th>\n";
   echo "         <th>Away Team</th>\n";
   echo "         <th>Spread</th>\n";
   echo "         <th>Home Score</th>\n";
   echo "         <th>Away Score</th>\n";
   echo "         <th>Edit</th>\n";
   echo "      </tr>\n";
   echo "   </thead>\n";
   echo "   <tbody>\n";

   $mysql = "
      select schedule_id,
             home,
             away,
             spread,
             homescore,
             awayscore,
             if (gametime is null, 'null', gametime)
       where week = ?";

   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   $sth->bind_param("i", $week);
   $sth->execute();
   $counter = $sth->num_rows;
   $sth->bind_result($id, $home, $away, $spread, $homescore, $awayscore, $gametime);
   while ($sth->fetch()) {
      echo "   <tr>\n";
      echo "      <td><input type=\"text\" name=\"gametime\" value=\"$gametime\" data-field=\"datetime\" /><div id=\"dtBox$i\"></div></td>\n";
      echo "      <td><input type=\"text\" name=\"home\" value=\"$home\" /></td>\n";
      echo "      <td><input type=\"text\" name=\"away\" value=\"$away\" /></td>";
      echo "      <td><input type=\"text\" name=\"spread\" value=\"$spread\" /></td>\n";
      echo "      <td><input type=\"text\" name=\"homescore\" value=\"$homescore\" /></td>\n";
      echo "      <td><input type=\"text\" name=\"awayscore\" value=\"$awayscore\" /><input type=\"hidden\" name=\"week\" value=\"$week\" /></td>\n";
      echo "      <td><button type=\"submit\" class=\"btn btn-primary\">Edit</button><input type=\"hidden\" name=\"id\" value=\"$schedule_id\" /></td>\n";
      echo "   </tr>\n";
   }

   echo "   </tbody>\n";
   echo "</table>\n";
   echo "</form>\n";
   mysqli_close($conn);
?>
<script type="text/javascript">
   $(document).ready(function()
   {
<?php
for ($j=1;$j<=$counter;$j++) {
?>
   $("#dtBox<?php echo $j; ?>").DateTimePicker();
<?php
}
?>
 });
</script>
<?php
}  // end function get_schedules


function get_schedules_admin($week) {  //nsp 4/2016
   echo "\n";
   echo "<form action=\"scheduleseditadmin.php\" method=\"post\" class=\"form-horizontal\" role=\"form\" enctype=\"multipart/form-data\">\n";
   echo "<table id=\"single\" class=\"table table-hover table-striped\">\n";
   echo "   <thead>\n";
   echo "      <tr>\n";
   echo "         <th>Date/Time (CST)</th>\n";
   echo "         <th>Away Team</th>\n";
   echo "         <th>Home Team</th>\n";
   echo "         <th>Spread</th>\n";
   echo "         <th>Away Score</th>\n";
   echo "         <th>Home Score</th>\n";
   echo "      </tr>\n";
   echo "   </thead>\n";
   echo "   <tbody>\n";
   $conn = db_connect();
   $result = $conn->query("SELECT COUNT(*) as count FROM schedules WHERE week='".$week."'");
   $row=$result->fetch_object();
   $count = $row->count;
   $result2 = $conn->query("SELECT * FROM schedules WHERE week='".$week."'");
   $i=1;
   while ($row=$result2->fetch_object()) {
      $id = $row->schedule_id;
      $gametime = $row->gametime;
	  $localTime = convert_to_user_date($gametime, "Y-m-d H:i:s");
      $home = $row->home;
      $away = $row->away;
      $spread = $row->spread;
      $homescore = $row->homescore;
      $awayscore = $row->awayscore;
      echo "      <tr>\n";
      echo "		 <input type=\"hidden\" name=\"gametime_utc[]\" value=\"$row->gametime\">";
      echo "         <td><input type=\"text\" name=\"gametime[]\" value=\"$localTime\" data-field=\"datetime\" /><div id=\"dtBox$i\"></div></td>\n";
      echo "         <td><input type=\"text\" name=\"away[]\" value=\"$row->away\" /></td>\n";
	  echo "         <td><input type=\"text\" name=\"home[]\" value=\"$row->home\" /></td>\n";
      echo "         <td><input type=\"text\" name=\"spread[]\" value=\"$row->spread\" /></td>\n";
	  echo "         <td><input type=\"text\" name=\"awayscore[]\" value=\"$row->awayscore\" /><input type=\"hidden\" name=\"id[]\" value=\"$row->schedule_id\" /></td>\n";
      echo "         <td><input type=\"text\" name=\"homescore[]\" value=\"$row->homescore\" /><input type=\"hidden\" name=\"week[]\" value=\"$week\" /></td>\n";
      echo "      </tr>\n";
      $i++;
   }
   echo "   </tbody>\n";
   echo "</table>\n";
   echo "<button type=\"submit\" class=\"btn btn-primary btn-lg btn-block\">Update Schedules</button>\n";
   echo "</form>\n";
   mysqli_close($conn);
   // http://curioussolutions.github.io/DateTimePicker/
?>
<script type="text/javascript">
 $(document).ready(function()
 {
<?php
for ($j=1;$j<=$count;$j++) {
?>
   $("#dtBox<?php echo $j; ?>").DateTimePicker({
         dateTimeFormat: 	"yyyy-MM-dd hh:mm:ss"
   });
<?php
}
?>
 });
</script>
<?php
}

// js support mypicks02.js - doc ready click edituserbutton ( vs editleagueuserbutton, the league version of all users)
// this is an 'admin' level function
function echoEditSiteUsers(
) {

   $disable_editing = '';
   $disable_username =     "disabled='disabled'";
   $disable_firstname =    "";
   $disable_lastname =     "";
   $disable_email =        "";
   $disable_site_active =  "";
   $disable_user_type =    "";
   $disable_username =     "disabled='disabled'";

   $html = "<!-- begin echoEditSiteUsers() -->\n";
   $html .= "<form action='usersedit.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'>\n";
   $html .= "<input type='hidden' id='IDi_iam' name='iam' value='echoEditSiteUsers' />";
   $html .=  "<table id='single' class='table table-hover table-striped'>\n" .
      "   <thead>\n" .
      "      <tr>\n" .
      "         <th>Username</th>\n" .
      "         <th>First Name</th>\n" .
      "         <th>Last Name</th>\n" .
      "         <th>Email</th>\n" .
      "         <th>Admin<br />User</th>\n" .
      "         <th>Site<br />Active</th>\n" .
      "         <th>Edit</th>\n" .
      "         <th>Change Password</th>\n" .
      "      </tr>\n" .
      "   </thead>\n" .
      "   <tbody>\n";

   $error_id_from_session_message = getSessionInfo('toechoEditSiteUsers');

   $error_id = (!empty($_SESSION['to_getusers_from_usersedit_error_in_edit'])) ? $_SESSION['to_getusers_from_usersedit_error_in_edit'] : '';
   if (!empty($_SESSION['to_getusers_from_usersedit_error_in_edit'])) {
      unset($_SESSION['to_getusers_from_usersedit_error_in_edit']);
   }
   //$user_id, $user_name, $f_name, $l_name, $email, $user_type, $active_status
   $mysql = "
      select id,
             username,
             fname,
             lname,
             email,
             usermode,
             if(active_status = 1, 'yes', 'no')
        from users
    order by lname";

   $status = 0;
   while (1) {

      $driver = new mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_STRICT;   // TODO is doing full table scan, lower threshold

      $conn = db_connect();
      $sth = $conn->prepare($mysql);
      $sth->execute();

      $sth->bind_result($user_id, $user_name, $f_name, $l_name, $email, $user_type, $active_status);

      while ($sth->fetch()) {

         $background = '';
         if ($error_id == $user_id || $error_id_from_session_message == $user_id) {
            $background = "style='background-color:MistyRose;'";
         }

         $html .= "      <tr id='IDr_editrow$user_id' $background>\n";
         $html .= "         <td><input type='text'   name='username' style='max-width:130px;' value='$user_name' $disable_username   />  <input type='hidden' name='username_old'  value='$user_name' /></td>\n";
         $html .= "         <td><input type='text'   name='fname'    style='max-width:80px;'  value='$f_name'    $disable_firstname  />  <input type='hidden' name='fname_old'     value='$f_name' /></td>\n";
         $html .= "         <td><input type='text'   name='lname'    style='max-width:100px;' value='$l_name'    $disable_lastname   />  <input type='hidden' name='lname_old'     value='$l_name' /></td>\n";
         $html .= "         <td><input type='email'  name='email'    style='max-width:250px;' value='$email'     $disable_email      />  <input type='hidden' name='email_old'     value='$email' />";
         $html .= "             <input type='hidden' name='usertype' value='$user_type' /></td>\n";

         $selected_admin =       ($user_type == 'admin')    ? "selected='selected'" : '';
         $selected_user =        ($user_type == 'user')     ? "selected='selected'" : '';
         $selected_is_active =   ($active_status == 'yes')  ? "selected='selected'" : '';
         $selected_not_active =  ($active_status == 'no')   ? "selected='selected'" : '';

         $html .= "         <td><select name='usertype' class='form-control input-medium' style='max-width:40px; $disable_user_type' >\n";
         $html .= "              <option value='admin' $selected_admin >A</option>\n";
         $html .= "              <option value='user'  $selected_user >U</option>\n";
         $html .= "            </select></td>\n";

         $html .= "         <td><select name='active_status' class='form-control input-medium' style='max-width:70px;' $disable_site_active>\n";
         $html .= "              <option value='yes' $selected_is_active  >yes</option>\n";
         $html .= "              <option value='no' $selected_not_active >no</option>\n";
         $html .= "            </select></td>\n";

         $html .= "         <td><button type='submit' class='btn btn-primary' name='edituserbutton' value='$user_id' $disable_editing >Edit</button></td>\n";
		 $html .= "         <td><button type='button' class='btn btn-info' data-toggle='modal' data-target='#passwordChangeModal' name='changepasswordbutton_$user_id' data-username='$user_name' value='$user_id' onClickNotRightNow(\"$('#passwordChangeModal').modal('show')); return false;\" $disable_editing >Change Password</button></td>\n";

         $html .= "      </tr>\n";
      }

      $status = 1;
      $html .= "      </tbody>\n";
      $html .= "   </table>\n";
      $html .= "</form>\n";
      @ $sth->close($conn);
      break;
   }
   if($status) {
      echo $html;
   }
   return $status;
}

// js support mypicks02.js - doc ready click editleagueuserbutton  ( vs edituserbutton, the all users version of all users)
// this is a 'commissioner' level function
function echoEditLeagueUsers(
   $league_id = 0
) {

   $error_user_id = getSessionInfo('toechoEditLeagueUsers');

   $disable_editing = "";
   $disable_username =        "disabled='disabled'";
   $disable_firstname =       "disabled='disabled'";
   $disable_lastname =        "disabled='disabled'";
   $disable_email =           "disabled='disabled'";
   $disable_site_active =     "disabled='disabled'";
   $disable_league_acitve =   "";
   $disable_player_name =     "";
   $disable_join_date =       "";
   $disable_paid =            "";

   $disable_editing = '';
   $form = "<form action='league_usersedit.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'>\n
               <input type='hidden' id='IDi_iam' name='iam' value='echoEditLeagueUsers' />\n
               <input type='hidden' id='IDi_leagueid' name='leagueid' value='$league_id' />\n";

   $form .=  "<table id='single' class='table table-hover table-striped'>\n" .
      "   <thead>\n" .
      "      <tr>\n" .
      "         <th>Username<br />(site)</th>\n" .
      "         <th>First Name<br />(site)</th>\n" .
      "         <th>Last Name<br />(site)</th>\n" .
      "         <th>Email<br />(site)</th>\n" .
      "         <th>Active<br />(site)</th>\n" .
      "         <th>Player Name<br />(league)</th>\n" .
      "         <th>Joined Date<br />(league)</th>\n" .
      "         <th>Active<br />(league)</th>\n" .
      "         <th>Paid<br />(league)</th>\n" .
      "         <th>Edit</th>\n" .
      "      </tr>\n" .
      "   </thead>\n" .
      "   <tbody>\n";


   // $error_id = (!empty($_SESSION['to_getusers_from_usersedit_error_in_edit'])) ? $_SESSION['to_getusers_from_usersedit_error_in_edit'] : '';
   // if (!empty($_SESSION['to_getusers_from_usersedit_error_in_edit'])) {
   //    unset($_SESSION['to_getusers_from_usersedit_error_in_edit']);
   // }
   // writeDataToFile("geting users league is " . $league, __FILE__, __LINE__);

   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_STRICT;   // TODO is doing full table scan, lower threshold

   $mysql = "
      select u.id,
             u.username,
             u.fname,
             u.lname,
             u.usermode,
             y.playername,
             u.email,
             if(u.active_status = 1, 'yes', 'no'),
             if(y.active = 2, 'yes', 'no'),
             if(y.paid = 2, 'yes', 'no'),
             y.joindate
        from nspx_leagueplayer as y, users as u
       where y.userid = u.id
         and y.leagueid = ?
       order by y.playername";

   $conn = db_connect();

   $status = 0;
   $msg = '';
   while (1) {

      if ($league_id == 0) {
         formatSessionMessage("A serious error has occurred.  We cannot preform league user management.  Please contact the site administrator", 'danger', $msg, '861');
         setSessionMessage($msg, 'error');
         break;
      }

      $conn = db_connect();
      $sth = $conn->prepare($mysql);
      $sth->bind_param("i", $league_id);
      $sth->execute();
      $sth-> bind_result($user_id, $user_name, $first_name, $last_name, $user_type, $player_name, $email, $active_site, $active_league, $paid, $join_date);

      while ($sth->fetch()) {
         $background = '';
         if ($error_user_id == $user_id) {
            $background = "style='background-color:MistyRose;'";
         }
         $form .= "      <tr id='IDr_editrow$user_id' $background>\n";
         $form .= "         <td><input type='text'  name='username'   value='$user_name'   style='max-width:120px;' $disable_username    /> <input type='hidden' name='username_old' value='$user_name' />     </td>\n";
         $form .= "         <td><input type='text'  name='fname'      value='$first_name'  style='max-width:100px;' $disable_firstname   /> <input type='hidden' name='fname_old' value='$first_name' />       </td>\n";
         $form .= "         <td><input type='text'  name='lname'      value='$last_name'   style='max-width:100px;' $disable_lastname    /> <input type='hidden' name='lname_old' value='$last_name' />        </td>\n";
         $form .= "         <td><input type='email' name='email'      value='$email' $disable_email />                                      <input type='hidden' name='email_old' value='$email' />            </td>\n";
         $form .= "         <td><input type='text'  name='siteactive' value='$active_site' style='max-width:40px;'  $disable_site_active />
                                 <input type='hidden' name='siteactive_old' value='$active_site' />
                                 <input type='hidden' name='usertype' value='$user_type' />
                                 </td>\n";
         $form .= "         <td><input type='text'  name='playername' value='$player_name' style='max-width:125px;' $disable_player_name /> <input type='hidden' name='playername_old' value='$player_name' /> </td>\n";
         $form .= "         <td><input type='text'  name='joindate'   value='$join_date' style='max-width:80px;' $disable_join_date /> <input type='hidden' name='playername_old' value='$player_name' /> </td>\n";

         $selected_yes = ($active_league == 'yes') ? "selected='selected'" : '';
         $selected_no =  ($active_league == 'no') ?  "selected='selected'" : '';
         $selected_paid_yes = ($paid == 'yes') ? "selected='selected'" : '';
         $selected_paid_no =  ($paid == 'no') ?  "selected='selected'" : '';

         $form .= "         <td>\n";
         $form .= "            <select name='leagueactive' class='form-control input-medium' $disable_league_acitve >\n";
         $form .= "               <option value='yes' $selected_yes >yes</option>\n";
         $form .= "               <option value='no'  $selected_no >no</option>\n";
         $form .= "            </select>\n";
         $form .= "         </td>\n";

         $form .= "         <td>\n";
         $form .= "            <select name='leaguepaid' class='form-control input-medium' $disable_paid >\n";
         $form .= "               <option value='yes' $selected_paid_yes >yes</option>\n";
         $form .= "               <option value='no'  $selected_paid_no >no</option>\n";
         $form .= "            </select>\n";
         $form .= "         </td>\n";

         $form .= "         <td><button type='submit' class='btn btn-primary' name='editleagueuserbutton' value='$user_id' $disable_editing >Edit</button></td>\n";
         $form .= "      </tr>\n";
      }

      $form .= "      </tbody>\n";
      $form .= "   </table>\n";
      $form .= "</form>\n";

      @ $sth->close();
      $status = 1;
      break;
   }
   @ $sth->close();

   if ($status) {
      echo $form;
   }
   return $status;
}

function get_picks($week) {
?>
<p class="lead muted text-center">Please choose your five picks for this week.</p>
<script>
function checkArray(form,arrayName)
{
   var retval=new Array();
   for(var i=0;i<form.elements.length;i++){
      var el=form.elements[i];
      if(el.type=="checkbox"&&el.name==arrayName&&el.checked){
         retval.push("\n" + el.parentElement.innerText);
      }
   }
   return retval.join('');
}

function validate(form) {
   var itemsChecked = checkArray(form, "my_pick[]");
   return confirm('Please confirm the following picks:\n\t' + itemsChecked);
}
</script>
<?php
   echo "<form id=\"my_picks\" name=\"my_picks\" action=\"picks2.php\" method=\"post\" class=\"form-horizontal\" role=\"form\" onsubmit=\"return validate(this);\" enctype=\"multipart/form-data\"><div class=\"col-md-3\"></div><button type=\"submit\" class=\"btn btn-primary col-md-6\">Submit Picks <span class=\"glyphicon glyphicon-ok\"></span></button><br /><br />";
   echo "<table id=\"single\" class=\"table table-hover table-striped\">";
   echo "<thead><th>Date/Time</th>";
   echo "<th>Away Team</th>";
   echo "<th>Home Team</th>";
   echo "</thead>";
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM schedules WHERE week='".$week."'");
   while ($row=$result->fetch_object()) {
      $id = $row->schedule_id;
      $gametime = $row->gametime;
      $home = $row->home;
      $away = $row->away;
      $spread = $row->spread;
      $homescore = $row->homescore;
      $awayscore = $row->awayscore;
      $gametime = date("l, M j, Y g:i a", strtotime($row->gametime));
      $checked_a='';
      $checked_h='';
      if ($spread > 0) { $spread_h = "+".(float)$spread; } else { $spread_h = (float)$spread;}
      if ($spread == 0) { $spread_a = (float)$spread; }
      elseif ($spread < 0) { $spread_a = "+".((float)$spread*-1); }
      elseif ($spread > 0) { $spread_a = (float)$spread*-1; }
      $gametime2 = date("YmdHi", strtotime($row->gametime));
      $now = date("YmdHi");
      if ($now < $gametime2) {
         if (in_array("$id-a", $_SESSION['pick_array'])) { $checked_a='checked="checked"'; }
         if (in_array("$id-h", $_SESSION['pick_array'])) { $checked_h='checked="checked"'; }
         echo "<tr>";
         echo "<td>$gametime</td>";
         echo "<td><div class=\"checkbox\"><label><input type=\"checkbox\" name=\"my_pick[]\" value=\"$id-a\" $checked_a> $away $spread_a</label></div></td>";
         echo "<td><div class=\"checkbox\"><label><input type=\"checkbox\" name=\"my_pick[]\" value=\"$id-h\" $checked_h> @$home $spread_h</label></div></td>";
         echo "</tr>";
      }
   }
   echo "</table><div class=\"col-md-3\"></div><button type=\"submit\" class=\"btn btn-primary col-md-6\">Submit Picks <span class=\"glyphicon glyphicon-ok\"></span></button></form>";
   mysqli_close($conn);
   unset($_SESSION['pick_array']);
}

function get_public_picks_old($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped\">";
   echo "<thead><th>Game Time</th>";
   echo "<th>Away Team</th>";
   echo "<th>Home Team</th>";
   echo "</thead>";
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM schedules WHERE week='".$week."'");
   while ($row=$result->fetch_object()) {
      $id = $row->schedule_id;
      $gametime = $row->gametime;
      $home = $row->home;
      $away = $row->away;
      $spread = $row->spread;
      if ($spread > 0) { $spread_h = "+".(float)$spread; } else { $spread_h = (float)$spread;}
      if ($spread == 0) { $spread_a = (float)$spread; }
      elseif ($spread < 0) { $spread_a = "+".((float)$spread*-1); }
      elseif ($spread > 0) { $spread_a = (float)$spread*-1; }
      $homescore = $row->homescore;
      $awayscore = $row->awayscore;
      $gametime = date("l, M j, Y g:i a", strtotime($row->gametime));
      $gametime2 = date("YmdHi", strtotime($row->gametime));
      $now = date("YmdHi");
      // if ($now < $gametime2) {
      echo "<tr>";
      echo "<td>$gametime</td>";
      echo "<td>$away $spread_a</td>";
      echo "<td>@$home $spread_h</td>";
      echo "</tr>";
      //  }
   }
   echo "</table>";
   mysqli_close($conn);
}


function get_public_picks_moved_to_picksphp($week) {

   // time format:  Friday, May 13, 2016 1:00 pm
   // http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
   $mysql = "
      select schedule_id,
             concat_ws(' ', date_format(gametime, '%a, %b %d, %Y %l:%i'), lower(date_format(gametime, '%p'))) displaydate,
             if (gametime < now(), 'disabled', '') disabled,
             home,
             away,
             spread,
             homescore,
             awayscore
        from schedules
       where week = ?";

   $stable = '';
   //$stable .= "<table id=\"single\" class=\"table table-hover table-striped\">\n";
   $stable .= "<table id=\"single\" class=\"table table-hover \">\n";
   $stable .= "   <thead>\n";
   $stable .= "      <tr>\n";
   $stable .= "         <th>Game Time</th>\n";
   $stable .= "         <th>Away Team</th>\n";
   $stable .= "         <th>Home Team</th>\n";
   $stable .= "      </tr>\n";
   $stable .= "   </thead>\n";
   $stable .= "   <tbody>\n";

   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   $sth->bind_param("i", $week);
   $sth->execute();
   $sth->bind_result($id, $gametime, $disabled, $home, $away, $spread,$homescore, $awayscore);
   while ($sth->fetch()) {

      $spread_h = ($spread > 0) ? "+".(float)$spread : (float)$spread;

      if ($spread == 0) {
         $spread_a = (float)$spread;
      } elseif ($spread < 0) {
         $spread_a = "+".((float)$spread*-1);
      } elseif ($spread > 0) {
         $spread_a = (float)$spread*-1;
      }

      $now = date("YmdHi");
      $row_id = 'IDr_' . $id;
      $away_id = 'IDb_away' . $id;
      $home_id = 'IDb_home' . $id;
      $started_class = ($disabled) ? "class='gamestarted'" : '';
      // if ($now < $gametime2) {
      $stable .= "      <tr id='$row_id' $started_class $disabled>\n";
      $stable .= "         <td>$gametime</td>\n";
      $stable .= "         <td><button  id='$away_id' type='button' class='btn btn-success' name='pickerbutton' schid='$id' whereplay='a' style='width:150px;'>$away $spread_a</button></td>\n";
      $stable .= "         <td><button  id='$home_id' type='button' class='btn btn-success' name='pickerbutton' schid='$id' whereplay='h' style='width:150px;'>@$home $spread_h</button></td>\n";
      $stable .= "      </tr>\n";
      //  }
   }
   $stable .= "   </tbody>\n";
   $stable .= "</table>\n";
   echo $stable;
   @ $sth->close();
}

function get_my_picks($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped\">\n";
   echo "   <thead>\n";
   echo "      <tr>\n";
   echo "         <th>Date/Time</th>\n";
   echo "         <th>Away Team</th>\n";
   echo "         <th>Home Team</th>\n";
   echo "         <th>Delete Pick</th>\n";
   echo "      </tr>\n";
   echo "   </thead>\n";
   echo "   <tbody>\n";
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id = '".$_SESSION['league_id']."' AND user='".$_SESSION['valid_user']."' ORDER BY picks.schedule_id ASC");
   while ($row=$result->fetch_object()) {
      $id = $row->schedule_id;
      $pick_id = $row->pick_id;
      $gametime = $row->gametime;
      $home = $row->home;
      $away = $row->away;
      $home_away = $row->home_away;
      $spread = $row->spread;
      $homescore = $row->homescore;
      $awayscore = $row->awayscore;
      $gametime = date("l, M j, Y g:i a", strtotime($row->gametime));
      echo "      <tr>\n";
      echo "         <td>$gametime</td>\n";
      if ($spread > 0) { $spread_h = "+".(float)$spread; } else { $spread_h = (float)$spread;}
      if ($spread == 0) { $spread_a = (float)$spread; }
      elseif ($spread < 0) { $spread_a = "+".((float)$spread*-1); }
      elseif ($spread > 0) { $spread_a = (float)$spread*-1; }
      if ($home_away == "a") {
         echo "         <td style=\"color:red;\"><b>$away $spread_a</b></td>\n";
      } else {
         echo "         <td>$away $spread_a</td>\n";
      }
      if ($home_away == "h") {
         echo "         <td style=\"color:red;\"><b>@$home $spread_h</b></td>\n";
      } else {
         echo "         <td>@$home $spread_h</td>\n";
      }
      echo "         <td>\n";
      echo "            <a href=\"deletepick.php?id=$pick_id\" onclick=\"return confirm('Are you sure you want to delete this pick?');\">\n";
      echo "               <button type=\"button\" class=\"btn btn-danger\">Delete</button>\n";
      echo "            </a>\n";
      echo "         </td>\n";
      echo "      </tr>\n";
   }
   echo "   </tbody>\n";
   echo "</table>\n";
   mysqli_close($conn);
   $conn = db_connect();
   $result = $conn->query("SELECT COUNT(*) as count FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND user='".$_SESSION['valid_user']."' AND league_id='".$_SESSION['league_id']."'");
   $row=$result->fetch_object();
   $count = $row->count;
   if (($count < $_SESSION['league_picks']) && ($count > 0)) {
       $count = $_SESSION['league_picks']-$count;
?>
<br />
<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><button class="btn btn-success">Add Pick  <span class="caret"></span></button></a>
<div id="collapseOne" class="panel-collapse collapse">
   <form action="mypicksadd.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
      <br />
      <div class="form-group">
         <label for="email" class="col-sm-2 control-label">Choose Team</label>
         <div class="col-sm-8">
            <div class="input-group">
                <select name='add_pick' class='form-control'>
                  <?php pick_drop_down($week); ?>
                </select>
                <span class="input-group-btn"><button type="submit" class="btn btn-primary">Submit Pick</button></span>
            </div>
         </div>
      </div>
      <input type="hidden" name="week" value="<?php echo $week; ?>" />
   </form>
</div>
<br />
<br />
<?php
  }
  mysqli_close($conn);
}

function get_my_picks_page($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped\">";
   echo "<thead><th>Date/Time</th>";
   echo "<th>Away Team</th>";
   echo "<th>Home Team</th>";
   echo "</thead>";
   $conn = db_connect();
   $result_count = $conn->query("SELECT COUNT(*) as count FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id = '".$_SESSION['league_id']."' AND user='".$_SESSION['valid_user']."' ORDER BY week DESC");
   $row=$result_count->fetch_object();
   $count=$row->count;
   if ($count == 0) {
      echo "<tr><td>You have not made your picks yet. Please go to \"This Week's Lines\" and choose your teams for this week.</td><td></td><td></td></tr>";
   } else {
      $conn = db_connect();
      $result = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id = '".$_SESSION['league_id']."' AND user='".$_SESSION['valid_user']."' ORDER BY picks.schedule_id ASC");
      while ($row=$result->fetch_object()) {
         $id = $row->schedule_id;
         $pick_id = $row->pick_id;
         $gametime = $row->gametime;
         $home = $row->home;
         $away = $row->away;
         $home_away = $row->home_away;
         $spread = $row->spread;
         $homescore = $row->homescore;
         $awayscore = $row->awayscore;
         $gametime = date("l, M j, Y g:i a", strtotime($row->gametime));
         echo "<tr>";
         echo "<td>$gametime</td>";
         if ($spread > 0) { $spread_h = "+".(float)$spread; } else { $spread_h = (float)$spread;}
         if ($spread == 0) { $spread_a = (float)$spread; }
         elseif ($spread < 0) { $spread_a = "+".((float)$spread*-1); }
         elseif ($spread > 0) { $spread_a = (float)$spread*-1; }
         if ($home_away == "a") {
            echo "<td style=\"color:red;\"><b>$away $spread_a</b></td>";
         } else {
            echo "<td>$away $spread_a</td>";
         }
         if ($home_away == "h") {
            echo "<td style=\"color:red;\"><b>@$home $spread_h</b></td>";
         } else {
            echo "<td>@$home $spread_h</td>";
         }
         echo "</tr>";
      }
  }
  echo "</table>";
  mysqli_close($conn);
}

function pick_drop_down($week) {
   $conn_pick = db_connect();
   $result_pick_home = $conn_pick->query("SELECT * FROM schedules WHERE week='$week' ORDER BY home ASC");
   echo "<optgroup label=\"Home Teams\">";
   while ($row=$result_pick_home->fetch_object()) {
      $id = $row->schedule_id;
      $home = $row->home;
      $spread = $row->spread;
      if ($spread > 0) { $spread_h = "+".(float)$spread; } else { $spread_h = (float)$spread;}
      $gametime2 = date("YmdHi", strtotime($row->gametime));
      $now = date("YmdHi");
      if ($now < $gametime2) {
         echo "<option value=\"$id-h\">$home $spread_h</option>
      ";
      }
   }
   $result_pick_away = $conn_pick->query("SELECT * FROM schedules WHERE week='$week' ORDER BY away ASC");
   echo "<optgroup label=\"Away Teams\">";
   while ($row=$result_pick_away->fetch_object()) {
      $id = $row->schedule_id;
      $away = $row->away;
      $spread = $row->spread;
      if ($spread == 0) { $spread_a = (float)$spread; }
      elseif ($spread < 0) { $spread_a = "+".((float)$spread*-1); }
      elseif ($spread > 0) { $spread_a = (float)$spread*-1; }
      $gametime2 = date("YmdHi", strtotime($row->gametime));
      $now = date("YmdHi");
      if ($now < $gametime2) {
         echo "<option value=\"$id-a\">$away $spread_a</option>
      ";
      }
   }
   mysqli_close($conn_pick);
}

function get_picks_history($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped\">";
   echo "<thead><th>Week</th>";
   echo "<th>Date/Time</th>";
   echo "<th>Away Team</th>";
   echo "<th>Home Team</th>";
   echo "</thead>";

   $conn = db_connect();
   $result_count = $conn->query("SELECT COUNT(*) as count FROM picks JOIN schedules USING (schedule_id) WHERE week!='$week'  AND league_id = '".$_SESSION['league_id']."' AND user='".$_SESSION['valid_user']."' ORDER BY week DESC");
   $row=$result_count->fetch_object();
   $count=$row->count;
   if ($count == 0) {
     echo "<tr><td>No Results</td><td></td><td></td><td></td><td></td></tr>";
   } else {
     $result = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week!='$week' AND league_id = '".$_SESSION['league_id']."' AND user='".$_SESSION['valid_user']."' ORDER BY week DESC");
     while ($row=$result->fetch_object()) {
        $week = $row->week;
        $id = $row->schedule_id;
        $gametime = $row->gametime;
        $home = $row->home;
        $away = $row->away;
        $home_away = $row->home_away;
        $spread = $row->spread;
        $homescore = $row->homescore;
        $awayscore = $row->awayscore;
        $gametime = date("l, M j, Y g:i a", strtotime($row->gametime));
        if ($spread > 0) { $spread_h = "+".(float)$spread; } else { $spread_h = (float)$spread;}
        if ($spread == 0) { $spread_a = (float)$spread; }
        elseif ($spread < 0) { $spread_a = "+".((float)$spread*-1); }
        elseif ($spread > 0) { $spread_a = (float)$spread*-1; }
        echo "<tr>";
        echo "<td>$week</td>";
        echo "<td>$gametime</td>";
        if ($home_away == "a") {
           echo "<td style=\"color:red;\"><b>$away $spread_a</b></td>";
        } else {
           echo "<td>$away $spread_a</td>";
        }
        if ($home_away == "h") {
           echo "<td style=\"color:red;\"><b>@$home $spread_h</b></td>";
        } else {
           echo "<td>@$home $spread_h</td>";
        }
        echo "</tr>";
     }
  }
  echo "</table>";
  mysqli_close($conn);
}

function get_standings_week($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   echo "<thead><th>Username</th>";
   echo "<th>Win</th>";
   echo "<th>Loss</th>";
   echo "<th>Push</th>";
   echo "<th>Total Points for the Week</th>";
   echo "</thead>";
   $weekly_standings=array();
   $conn = db_connect();
   $result_update = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id='".$_SESSION['league_id']."' GROUP BY user");
   while ($row=$result_update->fetch_object()) {
      $user = $row->user;
      $win = 0;
      $loss = 0;
      $push = 0;
      $result_picks_count = $conn->query("SELECT COUNT(*) as num FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id='".$_SESSION['league_id']."' and user='$user'");
      $row=$result_picks_count->fetch_object();
      $numrows=$row->num;
      if ($numrows > 0) {
         $result_picks_update = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id='".$_SESSION['league_id']."' and user='$user'");
         $data='';
         $now = date("YmdHi");
         while ($row=$result_picks_update->fetch_object()) {
            $user_result = $row->user;
            $spread = $row->spread;
            $homescore = $row->homescore;
            $awayscore = $row->awayscore;
            $homeaway = $row->home_away;
            if ($homescore+$awayscore !== 0) {
               $final_spread = $awayscore - $homescore;
               if ($final_spread == $spread) {
                  $push++;
               }
               if ($final_spread > $spread) {
                  if ($homeaway == 'h') { $loss++; } else { $win++; }
               }
               if ($final_spread < $spread) {
                  if ($homeaway == 'h') { $win++; } else { $loss++; }
               }
            }
         }
         $total = $win*1 + $push*0.5;
         if ($win+$push+$loss > 0) {
            //$weekly_standings=array($user, $win, $loss, $push, $total);
            //array_push($weekly_standings, array('key' => 'value'));
            array_push($weekly_standings, array($user, $win, $loss, $push, $total));
            usort($weekly_standings, "cmp");
         }
      }
   }
   foreach($weekly_standings as $result) {
      echo "<tr>";
      echo "<td>$result[0]</td>";
      echo "<td>$result[1]</td>";
      echo "<td>$result[2]</td>";
      echo "<td>$result[3]</td>";
      echo "<td>$result[4]</td>";
      echo "</tr>";
   }
   echo "</table>";
   mysqli_close($conn);
}

function get_standings_season($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   echo "<thead><th>Username</th>";
   echo "<th>Win</th>";
   echo "<th>Loss</th>";
   echo "<th>Tie</th>";
   echo "<th>Total Points</th>";
   echo "</thead>";

   $conn = db_connect();
   $result = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week!='$week' GROUP BY user");
   while ($row=$result->fetch_object()) {
      $user = $row->user;
      echo "<tr>";
      echo "<td>$user</td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "</tr>";
   }
   echo "</table>";
   mysqli_close($conn);
}

function get_standings_week_test($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   echo "<thead><th>Username</th>";
   echo "<th>Win</th>";
   echo "<th>Loss</th>";
   echo "<th>Push</th>";
   echo "<th>Total Points</th>";
   echo "<th>Picks</th>";
   echo "</thead>";
   $ldata = $_SESSION['league_id'];
   writeDataToFile($ldata, __FILE__, __LINE__);
   $weekly_standings=array();
   $conn = db_connect();
   $result_update = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id='".$_SESSION['league_id']."' GROUP BY user");
   while ($row=$result_update->fetch_object()) {
      $user = $row->user;
      $win = 0;
      $loss = 0;
      $push = 0;
      $result_picks_count = $conn->query("SELECT COUNT(*) as num FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id='".$_SESSION['league_id']."' and user='$user'");
      $row=$result_picks_count->fetch_object();
      $numrows=$row->num;
      if ($numrows > 0) {
         $result_picks_update = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND league_id='".$_SESSION['league_id']."' and user='$user'");
         $data='';
         $now = date("YmdHi");
         while ($row=$result_picks_update->fetch_object()) {
            $user_result = $row->user;
            $spread = $row->spread;
            $homescore = $row->homescore;
            $awayscore = $row->awayscore;
            $homeaway = $row->home_away;
            $gametime2 = date("YmdHi", strtotime($row->gametime));
            if ($now > $gametime2) {
               if ($row->home_away == "h") {
                  $data .= $row->home.', ';
               }
               if ($row->home_away == "a") {
                  $data .= $row->away.', ';
               }
            }
            if ($homescore+$awayscore !== 0) {
               $final_spread = $awayscore - $homescore;
               if ($final_spread == $spread) {
                  $push++;
               }
               if ($final_spread > $spread) {
                  if ($homeaway == 'h') { $loss++; } else { $win++; }
               }
               if ($final_spread < $spread) {
                  if ($homeaway == 'h') { $win++; } else { $loss++; }
               }
            }
         }
         $data = rtrim($data, ', ');
         $total = $win*1 + $push*0.5;
         if ($win+$push+$loss > 0) {
            //$weekly_standings=array($user, $win, $loss, $push, $total);
            //array_push($weekly_standings, array('key' => 'value'));
            array_push($weekly_standings, array($user, $win, $loss, $push, $total, $data));
            usort($weekly_standings, "cmp");
         }
      }
   }
   foreach($weekly_standings as $result) {
      echo "<tr>";
      echo "<td>$result[0]</td>";
      echo "<td>$result[1]</td>";
      echo "<td>$result[2]</td>";
      echo "<td>$result[3]</td>";
      echo "<td>$result[4]</td>";
      echo "<td>$result[5]</td>";
      echo "</tr>";
   }
   echo "</table>";
   mysqli_close($conn);
}

function cmp($a, $b)
{
   return strcmp($b[4], $a[4]);
}

function get_standings_season_test($week) {
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   echo "<thead><th>Username</th>";
   echo "<th>Win</th>";
   echo "<th>Loss</th>";
   echo "<th>Push</th>";
   echo "<th>Total Points</th>";
   echo "</thead>";
   $weekly_standings=array();
   $conn = db_connect();
   $result_update = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE league_id='".$_SESSION['league_id']."' GROUP BY user");
   while ($row=$result_update->fetch_object()) {
      $user = $row->user;
      $win = 0;
      $loss = 0;
      $push = 0;
      $result_picks_count = $conn->query("SELECT COUNT(*) as num FROM picks JOIN schedules USING (schedule_id) WHERE league_id='".$_SESSION['league_id']."' and user='$user'");
      $row=$result_picks_count->fetch_object();
      $numrows=$row->num;
      if ($numrows > 0) {
         $result_picks_update = $conn->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE league_id='".$_SESSION['league_id']."' and user='$user'");
         $data='';
         $now = date("YmdHi");
         while ($row=$result_picks_update->fetch_object()) {
            $user_result = $row->user;
            $spread = $row->spread;
            $homescore = $row->homescore;
            $awayscore = $row->awayscore;
            $homeaway = $row->home_away;
            if ($homescore+$awayscore !== 0) {
               $final_spread = $awayscore - $homescore;
               if ($final_spread == $spread) {
                  $push++;
               }
               if ($final_spread > $spread) {
                  if ($homeaway == 'h') { $loss++; } else { $win++; }
               }
               if ($final_spread < $spread) {
                  if ($homeaway == 'h') { $win++; } else { $loss++; }
               }
            }
         }
         $total = $win*1 + $push*0.5;
         $total = (float)$total;
         if ($win+$push+$loss > 0) {
            //$weekly_standings=array($user, $win, $loss, $push, $total);
            //array_push($weekly_standings, array('key' => 'value'));
            array_push($weekly_standings, array($user, $win, $loss, $push, $total));
            usort($weekly_standings, function($a, $b) {
                return $b[4] > $a[4];
            });
         }
      }
   }
   foreach($weekly_standings as $result) {
              echo "<tr>";
              echo "<td>$result[0]</td>";
              echo "<td>$result[1]</td>";
              echo "<td>$result[2]</td>";
              echo "<td>$result[3]</td>";
              echo "<td>$result[4]</td>";
              echo "</tr>";
   }
   echo "</table>";
   mysqli_close($conn);
} // end get_standings_season_test()


//No index used in query/prepared statement
//Need to install an index or turn off error reporting
//This gets called inside an email send loop
// hfs 4/24/2015 'create unique index ndxconfirmcode on temp_confirm (confirm_code);'  Has run on local.
// included in nsp_createtables.sql
// Note - I don't think checking for duplicate codes is warranted.  Duplicate chance is almost nothing.
function create_code() {

   //$mysql_nobind = "
   //   SELECT COUNT(*) as count
   //     FROM temp_confirm
   //    WHERE confirm_code = '$confirm_code'
   //      AND confirm_date <= DATE_ADD(curdate(), INTERVAL 2 WEEK)
   //      AND used='0'";
   //
   //$conn = db_connect();
   //do {
   //   $confirm_code=md5(uniqid(rand()));
   //   $result_check = $conn->query($mysql_nobind);
   //   $row=$result_check->fetch_object();
   //   $count = $row->count;
   //} while($count > 0);
   //mysqli_close($conn);
   return md5(uniqid(rand()));
}

function generate_username($username, $iteration) {
   if ($iteration > 0) {
      $generated = $username.$iteration;
   } else {
      $generated = $username;
   }
   $conn = db_connect();
   $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE username='$generated'");
   $row=$result->fetch_object();
   $count = $row->count;
   if ($count > 0) {
      return generate_username($username,$iteration + 1);
   }
   return $generated;
}

// I have no idea how this works... don't touch  http://www.w3schools.com/bootstrap/bootstrap_modal.asp
function get_text(
   $league_id = ''
) {

   if (!$league_id) {
      $league_id = 'index';
   }
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM homepage_text WHERE field_name='".$league_id."'");
   if ($row=$result->fetch_object()) {
      $text = $row->field_text;
   }
   if (empty($text)) {
      $result2 = $conn->query("SELECT * FROM homepage_text WHERE field_name='index'");
      $row2=$result2->fetch_object();
      $text = $row2->field_text;
      $league_id = 'index'; //zz
   }
   if ($league_id=="index") { $text = "
<div class=\"modal fade\" id=\"myModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
   <div class=\"modal-dialog\">
      <div class=\"modal-content\">
         <div class=\"modal-header\">
            <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
               <span aria-hidden=\"true\">&times;</span>
            </button>
            <h4 class=\"modal-title\" id=\"myModalLabel\">Start Your League</h4>
         </div>
         <div class=\"modal-body\">
            <div class=\"panel-heading\">
                <h3 class=\"panel-title text-center\">Log In to Register League with Current Account</h3>
            </div>
            <div class=\"panel-body text-center\">
               <form method=\"post\" action=\"league_login.php?id=login\" class=\"form-inline\">
                  <div class=\"form-group\">
                    <input type=\"hidden\" name=\"login_request_made\" value=\"1\" >
                    <input type=\"text\" class=\"form-control\" name=\"login_request_username\" placeholder=\"Username\">
                  </div>
                  <div class=\"form-group\">
                    <input type=\"password\" class=\"form-control\" name=\"login_request_password\" placeholder=\"Password\">
                  </div>
                  <button type=\"submit\" class=\"btn btn-default\">Log In <span class=\"glyphicon glyphicon-log-in\"></span></button>
               </form>
            </div>
            <h4 class=\"text-center\">OR</h4>
            <br />
            <div class=\"text-center\">
               <a class=\"btn btn-success\" href=\"league_login.php?id=new\" role=\"button\">Create League with New Account <span class=\"glyphicon glyphicon-arrow-right\"></span></a>
            </div>
         </div>
         <div class=\"modal-footer\">
         </div>
      </div>
   </div>
</div>
    ";
} else {
$text = "
<script type='text/javascript'>
   $(document).ready(function () {
         $('#IDb_startleague').click(function(e) {
               window.location ='league_login.php';
         });
   });
</script>
";
}

$text .= "<div class=\"modal fade\" id=\"joinLeageModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
   <div class=\"modal-dialog\">
      <div class=\"modal-content\">
         <div class=\"modal-header\">
            <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
               <span aria-hidden=\"true\">&times;</span>
            </button>
            <h4 class=\"modal-title\" id=\"myModalLabel\">Join A League</h4>
         </div>
         <div class=\"modal-body\">
            <div class=\"panel-heading\">
                <h3 class=\"panel-title text-center\">Join an existing league</h3>
            </div>
            <div class=\"panel-body text-center\">
               <form method=\"post\" action=\"league_join_verify.php\" class=\"form-inline\">
                  <div class=\"form-group\">
                    <input type=\"hidden\" name=\"login_request_made\" value=\"1\" >
                    <input type=\"text\" class=\"form-control\" name=\"joinleague_leagueid\" placeholder=\"League ID\">
                  </div>
                  <div class=\"form-group\">
                    <input type=\"password\" class=\"form-control\" name=\"joinleague_password\" placeholder=\"Password\">
                  </div>
                  <button type=\"submit\" class=\"btn btn-default\">Join <span class=\"glyphicon glyphicon-log-in\"></span></button>
               </form>
            </div>
         </div>
         <div class=\"modal-footer\">
         </div>
      </div>
   </div>
</div>";

   mysqli_close($conn);
   return $text;
}


function get_text2(
   $league_id = ''
) {
   $text = '';
   $custom_welcome = '';
   if (!$league_id) {
      $league_id = 'index';
   }
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM homepage_text WHERE field_name='".$league_id."'");
   if ($row=$result->fetch_object()) {
      $custom_welcome = $row->field_text;
   }
   if ($league_id == "index") { $text = "
<div class=\"modal fade\" id=\"myModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
   <div class=\"modal-dialog\">
      <div class=\"modal-content\">
         <div class=\"modal-header\">
            <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
               <span aria-hidden=\"true\">&times;</span>
            </button>
            <h4 class=\"modal-title\" id=\"myModalLabel\">Start Your League</h4>
         </div>
         <div class=\"modal-body\">
            <div class=\"panel-heading\">
                <h3 class=\"panel-title text-center\">Log In to Register League with Current Account</h3>
            </div>
            <div class=\"panel-body text-center\">
               <form method=\"post\" action=\"league_login.php?id=login\" class=\"form-inline\">
                  <div class=\"form-group\">
                    <input type=\"hidden\" name=\"login_request_made\" value=\"1\" >
                    <input type=\"text\" class=\"form-control\" name=\"login_request_username\" placeholder=\"Username\">
                  </div>
                  <div class=\"form-group\">
                    <input type=\"password\" class=\"form-control\" name=\"login_request_password\" placeholder=\"Password\">
                  </div>
                  <button type=\"submit\" class=\"btn btn-default\">Log In <span class=\"glyphicon glyphicon-log-in\"></span></button>
               </form>
            </div>
            <h4 class=\"text-center\">OR</h4>
            <br />
            <div class=\"text-center\">
               <a class=\"btn btn-success\" href=\"league_login.php?id=new\" role=\"button\">Create League with New Account <span class=\"glyphicon glyphicon-arrow-right\"></span></a>
            </div>
         </div>
         <div class=\"modal-footer\">
         </div>
      </div>
   </div>
</div>

$custom_welcome ";
   } else {
      $text .= "
<script type='text/javascript'>
   $(document).ready(function () {
         $('#IDb_startleague').click(function(e) {
               window.location ='league_login.php';
         });
   });
</script>
$custom_welcome
";
   }

$text .= "<div class=\"modal fade\" id=\"joinLeageModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
   <div class=\"modal-dialog\">
      <div class=\"modal-content\">
         <div class=\"modal-header\">
            <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
               <span aria-hidden=\"true\">&times;</span>
            </button>
            <h4 class=\"modal-title\" id=\"myModalLabel\">Join A League</h4>
         </div>
         <div class=\"modal-body\">
            <div class=\"panel-heading\">
                <h3 class=\"panel-title text-center\">Join an existing league</h3>
            </div>
            <div class=\"panel-body text-center\">
               <form method=\"post\" action=\"league_join_verify.php\" class=\"form-inline\">
                  <div class=\"form-group\">
                    <input type=\"hidden\" name=\"login_request_made\" value=\"1\" >
                    <input type=\"text\" class=\"form-control\" name=\"joinleague_leagueid\" placeholder=\"League ID\">
                  </div>
                  <div class=\"form-group\">
                    <input type=\"password\" class=\"form-control\" name=\"joinleague_password\" placeholder=\"Password\">
                  </div>
                  <button type=\"submit\" class=\"btn btn-default\">Join <span class=\"glyphicon glyphicon-log-in\"></span></button>
               </form>
            </div>
         </div>
         <div class=\"modal-footer\">
         </div>
      </div>
   </div>
</div>";

   mysqli_close($conn);
   return $text;
}


function get_league_text($type) {
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM homepage_text WHERE field_name='".$type."'");
   if ($row=$result->fetch_object()) {
      $text = $row->field_text;
   } else {
      $text = '';
   }
   mysqli_close($conn);
   return $text;
}

function get_messageboard($league_id) {

   echo "<h2>League Messageboard</h2><br />";
?>
   <div class="row">
      <a data-toggle="collapse" data-parent="#accordion" href="#collapselead">
        <button class="btn btn-success">New Message  <span class="caret"></span></button>
      </a>
      <br />
      <div id="collapselead" class="panel-collapse collapse">
         <form action="messageboard_add.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
            <div class="form-group col-md-12">
               <textarea class="form-control ckeditor" id="message" rows="3" placeholder="My Comment" name="message">
                 <?php if(!empty($_SESSION['leaguemessage'])) { echo $_SESSION['leaguemessage']; } ?>
               </textarea>
               <br />
               <button type="submit" class="btn btn-primary">Post New Message</button>
            </div>
         </form>
      </div>
   </div>
   <br />
<?php
   unset($_SESSION['leaguemessage']);
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM messages WHERE league_id='".$_SESSION['league_id']."' ORDER BY message_date DESC");
   $count=0;
   while($row=$result->fetch_object()) {
      $id = $row->id;
      $message_date = $row->message_date;
      $message_user = $row->message_user;
      $message_content = $row->message_content;
      echo "<div class=\"well\">";
      if ($message_user == $_SESSION['valid_user'] || $_SESSION['usermode'] == 'admin') { echo "<div class=\"pull-right\"><a href=\"messageboard_delete.php?id=$id\" onclick=\"return confirm('Are you sure you want to delete this message?');\">Delete</a></div><br />"; }
?>
   <div class="pull-right"><a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $count; ?>">Add Comment</a></div><br />
   <div id="collapse<?php echo $count; ?>" class="panel-collapse collapse">
      <form action="comment_add.php" method="post" class="form" role="form" enctype="multipart/form-data">
         <div class="form-group">
             <textarea class="form-control ckeditor" id="comment<?php echo $count; ?>" rows="3" placeholder="New Comment" name="comment"></textarea>
             <br /><button type="submit" class="btn btn-primary">Submit Comment</button>
             <input type="hidden" name="message_id" value="<?php echo $id; ?>" />
         </div>
      </form>
   </div>
   <script>
       CKEDITOR.replace( 'comment<?php echo $count; ?>', {
          height: '70px',
          // Define the toolbar groups as it is a more accessible solution.
          toolbarGroups: [
            {"name":"basicstyles","groups":["basicstyles"]},
            {"name":"paragraph","groups":["list","blocks"]},
            {"name":"insert","groups":["insert"]},
            {"name":"styles","groups":["styles"]},
            {"name":"about","groups":["about"]}
          ],
          // Remove the redundant buttons from toolbar groups defined above.
          removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar,Image,Flash,Table,PageBreak,Iframe'
       } );
   </script>
<?php
      echo "<blockquote>
             $message_content
             <footer>$message_user - $message_date</footer>
             </blockquote>
             </div>";
      $result2 = $conn->query("SELECT * FROM comments WHERE message_id='".$id."'");
      while($row2=$result2->fetch_object()) {
         $comment_id = $row2->id;
         $comment_date = $row2->comment_date;
         $comment_user = $row2->comment_user;
         $comment_content = $row2->comment_content;
         echo "<div class=\"panel panel-default comment\">
               <div class=\"panel-body\">
               <a name=\"$comment_id\"></a>";
         if ($comment_user == $_SESSION['valid_user'] || $_SESSION['usermode'] == 'admin') {
            setSessionMessage($comment_id, 'info', 'commentid');
            echo "<div class=\"pull-right\">
                     <a href=\"comment_delete.php\" onclick=\"return confirm('Are you sure you want to delete this comment?');\">Delete</a>
                  </div>";
         }
         echo "<blockquote>
         $comment_content
         <footer>$comment_user - $comment_date</footer>
         </blockquote>
         </div>
         </div>";
      }
      $count++;
   }
   mysqli_close($conn);
}  // end get_messageboard()

function get_inbox() {
   echo "<h2>My Inbox</h2>";
   echo "<div><a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapsemsg\"><button class=\"btn btn-success\">New Message  <span class=\"glyphicon glyphicon-envelope\"></span></button></a></div><br />";
?>
<div id="collapsemsg" class="panel-collapse collapse">
   <form action="new_mail.php" method="post" class="form" role="form" enctype="multipart/form-data">
      <div class="form-group">
         <label for="to_field">To:</label>
         <select class="form-control chosen-select" name="to_field[]" multiple tabindex="3">
<?php
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM users WHERE league_id LIKE '%-".$_SESSION['league_id']."-%' ORDER BY username ASC");
   while($row=$result->fetch_object()) {
      $username = $row->username;
      $id = $row->id;
      echo "<option value=\"$id\">$username</option>";
   }
?>
         </select>
      </div>
      <div class="form-group">
          <label for="subject">Subject</label>
          <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" />
      </div>
      <div class="form-group">
         <label for="message">Message</label>
         <textarea class="form-control ckeditor" id="message" rows="5" placeholder="New Comment" name="message"></textarea>
         <br /><button type="submit" class="btn btn-primary">Send Message <span class="glyphicon glyphicon-send"></span></button>
      </div>
   </form>
</div>
<br />
      <script>
    CKEDITOR.replace( 'message', {
      height: '70px',
      // Define the toolbar groups as it is a more accessible solution.
      toolbarGroups: [
        {"name":"basicstyles","groups":["basicstyles"]},
        {"name":"paragraph","groups":["list","blocks"]},
        {"name":"insert","groups":["insert"]},
        {"name":"styles","groups":["styles"]},
        {"name":"about","groups":["about"]}
      ],
      // Remove the redundant buttons from toolbar groups defined above.
      removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar,Image,Flash,Table,PageBreak,Iframe'
    } );

  </script>
  <script src="js/chosen.jquery.js" type="text/javascript"></script>
  <script type="text/javascript">
    var config = {
       '.chosen-select'           : {width:"100%"}
    }
    for (var selector in config) {
       $(selector).chosen(config[selector]);
    }
  </script>
<?php
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   echo "<thead><th>From</th>";
   echo "<th>Subject</th>";
   echo "<th>Date</th>";
   echo "<th>Delete</th>";
   echo "</thead>";

   $conn = db_connect();
   $result = $conn->query("SELECT * FROM inbox WHERE to_field='".$_SESSION['valid_user']."' ORDER BY mail_date DESC");
   if ($result) {
      while ($row=$result->fetch_object()) {
        $id = $row->id;
        $mail_date = $row->mail_date;
        $mail_content = $row->mail_content;
        $mail_subject = $row->mail_subject;
        $from_field = $row->from_field;
        echo "<tr>";
        echo "<td><a href=\"viewmail.php?id=$id\">$from_field</a></td>";
        echo "<td><a href=\"viewmail.php?id=$id\">$mail_subject</a></td>";
        echo "<td><a href=\"viewmail.php?id=$id\">$mail_date</a></td>";
        echo "<td><a href=\"delete_mail.php?id=$id\" onclick=\"return confirm('Are you sure you want to delete this message? This cannot be undone.');\"><button type=\"button\" class=\"btn btn-danger\">Delete</button></a></td>";
        echo "</tr>";
      }
   }
   echo "</table>";
   mysqli_close($conn);
}

function get_sent() {
   echo "<h2>Sent Messages</h2>";
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   echo "<thead><th>To</th>";
   echo "<th>Subject</th>";
   echo "<th>Date</th>";
   echo "</thead>";

   $conn = db_connect();
   $result = $conn->query("SELECT * FROM inbox WHERE from_field='".$_SESSION['valid_user']."' ORDER BY mail_date DESC");
   if ($result) {
      while ($row=$result->fetch_object()) {
         $id = $row->id;
         $mail_date = $row->mail_date;
         $mail_content = $row->mail_content;
         $mail_subject = $row->mail_subject;
         $to_field = $row->to_field;
         echo "<tr>";
         echo "<td><a href=\"viewmail.php?id=$id\">$to_field</a></td>";
         echo "<td><a href=\"viewmail.php?id=$id\">$mail_subject</a></td>";
         echo "<td><a href=\"viewmail.php?id=$id\">$mail_date</a></td>";
         echo "</tr>";
      }
   }
   echo "</table>";
   mysqli_close($conn);
}

function view_mail($id) {
   echo "<h2>Message</h2>";
   echo "<div><a href=\"inbox.php\"><button class=\"btn btn-success\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to Inbox</button></a></div><br />";
   echo "<table id=\"single\" class=\"table table-hover table-striped table-bordered\">";
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM inbox WHERE id='".$id."'");
   if ($result) {
      while ($row=$result->fetch_object()) {
         $id = $row->id;
         $mail_date = $row->mail_date;
         $mail_content = htmlspecialchars_decode($row->mail_content);
         $mail_subject = $row->mail_subject;
         $to_field = $row->to_field;
         $from_field = $row->from_field;
         echo "<tr><td>To:</td><td>$to_field</td></tr>";
         echo "<tr><td>From:</td><td>$from_field</td></tr>";
         echo "<tr><td>Subject:</td><td>$mail_subject</td></tr>";
         echo "<tr><td>Date:</td><td>$mail_date</td></tr>";
         echo "<tr><td>Message:</td><td><pre>$mail_content</pre></td></tr>";
      }
   }
   echo "</table>";
   mysqli_close($conn);
}

function get_my_leagues($league) {  // Doesn't seem to be used - see get_my_league()
   writeToDataFile("this is the session in get_my_leages()   " . print_r($_SESSION, true), __FILE__, __LINE__);
   if(empty($league) && empty($_SESSION['league_id'])) {
      $conn = db_connect();
      $result = $conn->query("SELECT league_id FROM users WHERE username='".$_SESSION['validuser']."'");
      $row=$result->fetch_object();
      $league_id = $row->league_id;
      $league = explode('-', $league_id);
      writeToDataFile("this is the exploded league in get_my_leages()   " . print_r($league, true), __FILE__, __LINE__);
      if (preg_match('/[0-9]/', $row->league_id)) {
         $i=0;
         while(!is_numeric($league[$i])) {
            $i++;
         }
         $_SESSION['league_id'] = $league[$i];
      } else {
          $_SESSION['league_id'] = '0';
      }
      mysqli_close($conn);
   }
}

function get_my_league($admin) {  // This is the commissioner page.
   $conn = db_connect();
   $result = $conn->query("SELECT * FROM league WHERE league_id='$admin'");
   $row=$result->fetch_object();
   $league_name = $row->league_name;
   $league_points = $row->league_points;
   $commissioner = $row->commissioner;
   if(check_valid_user() && ($commissioner == $_SESSION['user_id'])) {
?>
      <h2>League Management for <i><?php echo $league_name; ?></i></h2>
      <!--
      <h3>League Settings</h3>
      <div class="btn-toolbar">
         <a href="schedules_nsp.php" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button">
            <span class="glyphicon glyphicon-list-alt"></span> League Stuff</a>
      </div>
      -->

      <!-- <h3>Schedules</h3>
      <div class="btn-toolbar">
<?php if($league_points == '3') { ?>
      <a href="schedules.php" class="btn btn-success btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-list-alt"></span> Edit Schedules/Scores</a> -->
<?php } ?>
         <a href="setweek.php" class="btn btn-success btn-lg col-md-3" id="admin_button" role="button">
            <span class="glyphicon glyphicon-calendar"></span> Set Active Week</a>
      </div>
      -->
      <h3>Users</h3>
      <div class="btn-toolbar">
         <a href="adminregister.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button">
           <span class="glyphicon glyphicon-plus"></span> Invite Users</a>
         <a href="league_users.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button">
           <span class="glyphicon glyphicon-user"></span> Edit League Users</a>
         <a href="league_join_options.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button">
           <span class="glyphicon glyphicon-cog"></span> Set League Password</a>
         <a href="email_league.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button">
           <span class="glyphicon glyphicon-envelope"></span> Email League</a>
      </div>
      <h3>League Pages</h3>
      <div class="btn-toolbar">
         <a href="league_adminmessage.php" class="btn btn-warning btn-lg col-md-3" id="admin_button" role="button">
            <span class="glyphicon glyphicon-pencil"></span> Edit Homepage</a>
      </div>  <!-- END get_my_league -->
      <h3>Operations</h3>
      <div class="btn-toolbar">
         <a href="league_operations.php" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button">
            <span class="glyphicon glyphicon-wrench"></span> League Operations</a>
      </div>  <!-- END get_my_league -->
<?php
         } else {
?>
      <p class="lead">You are not allowed to view this page.</p>
<?php
   }
} //end get_my_league()

function put_pagination_weekly_remove_xxxx(
   $selected_week,
   $last_week_completed = 12
   ) {

   $pagination_break_after_week = 10;
   $pageination_break_made = false;

   //writeDataToFile(print_r($_SERVER, true), __FILE__, __LINE__);

   echo "            <ul class='pagination pagination-sm' style='margin: 0px !important;'>
      ";
   for ($week_number = 1; $week_number <= NFL_LAST_WEEK; $week_number++) {

      if ($week_number > $pagination_break_after_week && $pageination_break_made === false) {
         $pageination_break_made = true;
         echo "            </ul><br />
            <ul class='pagination pagination-sm' style='margin: 0px !important;'>
         ";
      }
      $li_class = '';
      $anchor = "<a href='$_SERVER[PHP_SELF]?submit_week=$week_number'>$week_number</a>";
      if ($week_number > $last_week_completed) {
         $anchor = "<a >$week_number</a>";
         $li_class .= "class='disabled'";
      }
      if ($week_number == $selected_week) {
         $li_class .= "class='active'";
      }
      echo "               <li $li_class>$anchor</li>
      ";
   }
   echo "            </ul>";
}

function echo_container_breaks($breaks = BOOTSTRAP_NAVBAR_BREAKS_DEFAULT) {
   echoContainerBreaks();
}
function echoContainerBreaks($breaks = BOOTSTRAP_NAVBAR_BREAKS_DEFAULT) {

   if ($breaks < 0) {
       $breaks = 0;
   } elseif ($breaks > 10) {
      $breaks = 10;
   }

   $breaks_string = '';
   for(;$breaks > 0; $breaks--) {
      $breaks_string .= '<br />';
   }
   echo $breaks_string;
}

// Credentials may ONLY be:  admin, commissioner, user
function validateUser(
   $credential = 'user',   // Credentials may be:  admin, commissioner, user, league
   $action = 'redirect',   // redirect, status
   &$ref_access_denied_message = ''
){
   //writeDataToFile("1 validateUser() credential $credential, action $action,  this is the SESSION: " . print_r($_SESSION, true), __FILE__, __LINE__);

   $access = false;
   $ref_access_denied_message = '';
   $user_id = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
   $user_mode = (!empty($_SESSION['usermode'])) ? $_SESSION['usermode'] : '';
   $user_active = (!empty($_SESSION['active'])) ? $_SESSION['active'] : '';

   //writeDataToFile("2 validateUser() credential $credential, action $action, userid '$user_id'  this is the SESSION: " . print_r($_SESSION, true), __FILE__, __LINE__);
   $msg = '';
   while (1) {

      if ($user_id == ''  || $user_mode == '') {
         $ref_access_denied_message = 'Please login.';
         if ($action != 'status') {
            formatSessionMessage($ref_access_denied_message, 'info', $msg);
            setSessionMessage($msg, 'error');
         }
         break;
      }

      writeDataToFile("2059 validateUser =credentials", __FILE__, __LINE__);
      if (!($credential === 'admin'
         || $credential === 'commissioner'
         || $credential === 'user'
         || $credential === 'league'
         || $action === 'redirect'
         || $action === 'status'))
      {
         $ref_access_denied_message = "There was a problem.  Please contact the site administrator. (ref: unkcredential)";
         formatSessionMessage($ref_access_denied_message, 'danger', $msg, "unkcredential'$credential'$action'");
         setSessionMessage($msg, 'error');
         break;
      }

      if (!isSiteActive() && $credential != 'admin') {
         $ref_access_denied_message = "The site is currently unavailable.) (ref: sitemaint)";
         formatSessionMessage("The site is currently unavailable.", 'info', $msg, "sitemaint");
         setSessionMessage($msg, 'error');
         header('Location: ' . URL_HOME_PAGE);
         die();
      }

      writeDataToFile("2081 validateUser =site IS active", __FILE__, __LINE__);
      if (!$user_active) {
         $ref_access_denied_message = 'This account has been deactivated.';
         if ($action != 'status') {
            formatSessionMessage($ref_access_denied_message, 'warning', $msg);
            setSessionMessage($msg, 'error');
         }
         break;
      }

      writeDataToFile("2091 validateUser =user IS active", __FILE__, __LINE__);
      //Admin always - unless deactivated ... above
      if ($user_mode == 'admin') {
         $access = true;
         break;
      }
      writeDataToFile("2095 validateUser =user mode is not, user mode is $user_mode", __FILE__, __LINE__);

      if ($credential == 'league') {
         writeDataToFile("2096 validateUser = mode test 'league'", __FILE__, __LINE__);
         $league_id = (isset($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';
         if (!$league_id) {
         writeDataToFile("validateUser !league_id'", __FILE__, __LINE__);
            if ($action != 'status') {
               $ref_access_denied_message = "You have no current league.";
               formatSessionMessage($ref_access_denied_message, 'warning', $msg);
               setSessionMessage($msg, 'error');
            }
            break;
         }
         writeDataToFile("2107 validateUser = calling isactiveleagueplayer($user_id, $league_id)", __FILE__, __LINE__);
         if (!$return = isActiveLeaguePlayer($user_id, $league_id)) {
         writeDataToFile("2109validateUser = mode test 'league'  not isactiveleagueplayer($user_id, $league_id)", __FILE__, __LINE__);
            if ($action != 'status') {
               if ($return === 0) {
                  $ref_access_denied_message = "Your league membership has be deactivated.";
                  formatSessionMessage($ref_access_denied_message, 'info', $msg);
                  setSessionMessage($msg, 'error');
               } else if ($return === false) {
                  $ref_access_denied_message = "There was a database error.  Please contact the administrator.";
                  formatSessionMessage($ref_access_denied_message, 'warning', $msg);
                  setSessionMessage($msg, 'error');
               } else if ($return === null) {
                  $ref_access_denied_message = "You are not a player in this league.";
                  formatSessionMessage($ref_access_denied_message, 'info', $msg);
                  setSessionMessage($msg, 'error');
               }
            }
            break;
         } else {
         writeDataToFile("2127validateUser = mode test 'league'  IS an isactiveleagueplayer($user_id, $league_id)", __FILE__, __LINE__);
            $access = true;
            break;
         }
      }  // end == 'league'


      if ($user_mode == 'user' && $credential == 'user') {
         $access = true;
         break;
      }
      if ($user_mode = 'user' && $credential == 'admin') {
         if ($action != 'status') {
            $ref_access_denied_message = "Access is denied.  Administrative privledges are required.";
            formatSessionMessage($ref_access_denied_message, 'info', $msg);
            setSessionMessage($msg, 'error');
         }
         break;
      }

      // The league may not be choosen yet.  If the user has status on
      // any active league, allow access.  TODO There is no active status
      // bit for leagues.
      if ($credential == 'commissioner') {
         if (!isCommissionerWithScope($_SESSION['user_id'], $_SESSION['league_id'])) {
            writeDataToFile("2144 validateUser -> CommissionerWithScope fail, session (user_id and league_id) : " . print_r($_SESSION, true), __FILE__, __LINE__);
            $ref_access_denied_message = 'You are not authorized to access this resource.  You are not a league Admin.';
            if ($action != 'status') {
               formatSessionMessage($ref_access_denied_message, 'warning', $msg);
               setSessionMessage($msg, 'error');
            }
            break;
         } else {
            $access = true;
            break;
         }
      }

      break;
   }

   if ($access) {
      checkAndSetActiveWeek();
   }

   if ($action == 'status') {
      return $access;
   }

   //writeDataToFile("SESSION IN the validateUser func: " . print_r($_SESSION, true) . "\n\nSERVER: " . print_r($_SERVER, true), __FILE__, __LINE__);
   // Note.  SERVER_PROTOCOL may be ok to use for the determining the protocol http-https
   // TODO http is hardwired.  No.
   if ($access == false) {
      $host =  (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
      $url = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
      writeDataToFile("access failed redirection: host '$host', url '$url', session " . print_r($_SESSION, true), __FILE__, __LINE__);
      if (!$url) {
         formatSessionMessage('Hot linking is not allowed.', 'warning', $msg);
         setSessionMessage($msg, 'error');
         header('Location: http://' . $host . MY_SHORT_PATH . 'index.php?accessviolation=Hot linking not allowed.');
         die;
      }

      $path = parse_url($url, PHP_URL_PATH);
      writeDataToFile("access success redirection: http://" .$host . $path, __FILE__, __LINE__);
      header('Location: http://' . $host . $path);
      die;
   }
}


// With hotlink protection
// There are also access violations.  Don't know where to put those yet since
// there are allowed pages that have no access requirement (contact.php) but
// do allow a login option.
//
// A note about FAILED_LOGINHISTORY_ID.
function echoLoginAttempt (   // return: false on login failure message - true on all else
   $quiet = false
) {
   $get_login_status = (!empty($_GET['login'])) ? $_GET['login'] : '';
   $login_status = (!empty($_SESSION['login'])) ? $_SESSION['login'] : '';
   writeDataToFile("login status '$login_status'", __FILE__, __LINE__);
   if ($login_status != '') {
      unset($_SESSION['login']);
   }
   if ($login_status == '' && $get_login_status == '') {
      return true;
   }
   if ($login_status != '') {
      switch ($login_status) {
      case  "attempt" :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please check your username and password and try again. Thank you.</div>";
         return false;
      case  "success" :
         echo "<div class=\"alert alert-success\">You are logged in!</div>";
         return true;
      case  "fail" :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please check your username and password and try again. Thank you.</div>";
         return false;
      case  "failactive" :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. This account has been deactivated.  Please contact the site administrator for more information. Thank you.</div>";
         return false;
      case  "dberror" :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please contact the site administrator or try again later.  Thank you. (ref: dberror).</div>";
         return false;
      default :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please contact the site administrator (ref: dflt).</div>";
         return false;
      }
   }
   if ($get_login_status != '') {
      switch ($get_login_status) {
      case  "success" :
         echo "<div class=\"alert alert-success\">You are logged in!</div>";
         return true;
      case  "fail" :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please check your username and password and try again. Thank you.</div>";
         return false;
      case  "dberror" :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please contact the site administrator (ref: dberror).</div>";
         return false;
      default :
         echo "<div class=\"alert alert-danger\">We were unable to log you in. Please contact the site administrator (ref: dflt).</div>";
         return false;
      }
   }
}
//validateUser() can be asked to validate based on a credential, such as 'commissioner'.
//It will set/clear SESSION var 'accessviolation'
function echoAccessViolation () {
   $get_access_message = (!empty($_GET['accessviolation'])) ? $_GET['accessviolation'] : '';
   $access_message = (!empty($_SESSION['accessviolation'])) ? $_SESSION['accessviolation'] : '';
   if ($access_message != '') {
      unset($_SESSION['accessviolation']);
   }
   if ($get_access_message == '' && $access_message == '') {
      return true;
   }
   echo "<div class=\"alert alert-danger\">The resource requested requires additional priviledges. Access denied. ( $get_access_message $access_message) </div>";
   return false;
}

function isPostBack(){
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && isset($_SERVER['HTTP_REFERER']) && basename(strtok($_SERVER['HTTP_REFERER'],'?')) == basename($_SERVER['SCRIPT_NAME']);
}

function convert_to_user_date($date, $format = 'n/j/Y g:i A', $userTimeZone = 'America/Chicago', $serverTimeZone = 'America/Los_Angeles')
{
    try {
        $dateTime = new DateTime ($date, new DateTimeZone($serverTimeZone));
        $dateTime->setTimezone(new DateTimeZone($userTimeZone));
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '';
    }
}

function convert_to_server_date($date, $format = 'n/j/Y g:i A', $userTimeZone = 'America/Chicago', $serverTimeZone = 'America/Los_Angeles')
{
    try {
        $dateTime = new DateTime ($date, new DateTimeZone($userTimeZone));
        $dateTime->setTimezone(new DateTimeZone($serverTimeZone));
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '';
    }
}
?>
