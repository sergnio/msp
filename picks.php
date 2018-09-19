<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: picks.php
   date: apr-2016
 author: original
   desc: File is linked to main page "This Week's Lines"
marbles: 
   note: cleaned up some code; fixed session start issues; timeout to days
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';


validateUser('user');

do_header('MySuperPicks.com - Picks');
do_nav();

?> 
   <div class="container">
<?php
echo_container_breaks(); //CSS! 
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
$update = '';
if (!empty($_GET['update'])) { $update = $_GET['update']; }
$update = sql_sanitize($update);
$update = html_sanitize($update);
if ($login == 'success') {
   echo "<div class=\"alert alert-success\">You are logged in!</div>";
}
if ($login == 'fail') {
   echo "<div class=\"alert alert-danger\">We were not able to log you in. Please check your username and password and try again. Thank you.</div>";
}
if ($update == '0') {
   echo "<div class=\"alert alert-danger\">You did not pick any teams. Please select your picks and try again.</div>";
}
if ($update == '1') {
   echo "<div class=\"alert alert-success\">Pick successfully saved!</div>";
}
if ($update == '2') {
   echo "<div class=\"alert alert-success\">Pick successfully deleted!</div>";
}
if ($update == '3') {
   echo "<div class=\"alert alert-warning\">You cannot pick that team. Please check your pick and try again.</div>";
}
if ($update == 'error') {
   echo "<div class=\"alert alert-danger\">You picked both teams of a single game. Please check your picks and try again.</div>";
}
if ($update == 'error2') {
   echo "<div class=\"alert alert-danger\">One of your picks is no longer available. Please check your picks and try again.</div>";
}
if ($update == 'error3') {
   echo "<div class=\"alert alert-danger\">Sorry, you cannot delete a pick once the start time for the game has already passed.</div>";
}
$conn = db_connect();
$result = $conn->query("SELECT active_week FROM league WHERE league_id='".$_SESSION['league_id']."'");
$row=$result->fetch_object();
$week = $row->active_week;
?>
      <h1 class="text-center">Week <?php echo $week; ?></h1>
<?php 
if(check_valid_user()) {
   $result2 = $conn->query("SELECT COUNT(*) as count FROM picks JOIN schedules USING (schedule_id) WHERE week='$week' AND user='".$_SESSION['valid_user']."' AND league_id='".$_SESSION['league_id']."'");
   $row2=$result2->fetch_object();
   $count = $row2->count;
   if($count > 0) {
      echo "<p class=\"lead muted text-center\">If you need to change your picks, delete them and reselect them using the Add Pick button below. <br />The spread <a href=\"#\" rel=\"tooltip\" data-placement=\"right\" title='How do point spreads work? The team with the minus sign (e.g. -3) is the favorite and is \"giving\" 3 points. They must win the game by more than 3 points to cover, or for you to \"win\" the point if you picked them. The team with the plus sign (+3) is the underdog and is \"getting\" 3 points. They can  cover, or win, by winning the game outright, or by losing the game by less than 3 points. If the favorited team in this scenario ends up winning by exactly 3 points, this is a \"push\" and anyone who picked either team will receive 0.5 pts. in the standings. ' ><span class=\"glyphicon glyphicon-question-sign\"></span></a> is listed to the right of the team name.
</p>
<script type=\"text/javascript\">
    $(function () {
        $(\"[rel='tooltip']\").tooltip();
    });
</script>";
      echo "<h3>Your Picks (highlighted in red)</h3>";
      get_my_picks($week);
      echo "<br />";
      echo "<h3>This Week's Lines</h3>";
      get_public_picks($week);
      echo "<br /><br /><br /><br />";
   } else {
      echo "<p class=\"lead muted text-center\">The spread <a href=\"#\" rel=\"tooltip\" data-placement=\"right\" title='How do point spreads work? The team with the minus sign (e.g. -3) is the favorite and is \"giving\" 3 points. They must win the game by more than 3 points to cover, or for you to \"win\" the point if you picked them. The team with the plus sign (+3) is the underdog and is \"getting\" 3 points. They can  cover, or win, by winning the game outright, or by losing the game by less than 3 points. If the favorited team in this scenario ends up winning by exactly 3 points, this is a \"push\" and anyone who picked either team will receive 0.5 pts. in the standings. ' ><span class=\"glyphicon glyphicon-question-sign\"></span></a> is listed to the right of the team name.
</p>
<script type=\"text/javascript\">
    $(function () {
        $(\"[rel='tooltip']\").tooltip();
    });
</script>";
      get_picks($week);
      echo "<br /><br /><br /><br />";
   }
} else {
   echo "<p class=\"lead muted text-center\">Log in to make your picks for this week. If you have already made them, then visit the My Picks link to edit them.<br />The spread <a href=\"#\" rel=\"tooltip\" data-placement=\"right\" title='How do point spreads work? The team with the minus sign (e.g. -3) is the favorite and is \"giving\" 3 points. They must win the game by more than 3 points to cover, or for you to \"win\" the point if you picked them. The team with the plus sign (+3) is the underdog and is \"getting\" 3 points. They can  cover, or win, by winning the game outright, or by losing the game by less than 3 points. If the favorited team in this scenario ends up winning by exactly 3 points, this is a \"push\" and anyone who picked either team will receive 0.5 pts. in the standings. ' ><span class=\"glyphicon glyphicon-question-sign\"></span></a> is listed to the right of the team name.
        </p>
<script type=\"text/javascript\">
    $(function () {
        $(\"[rel='tooltip']\").tooltip();
    });
</script>";
        get_public_picks($week);
   echo "<br /><br /><br /><br />";
}
if (!empty($_SESSION['league_picks'])) { $limit = $_SESSION['league_picks']; } else { $limit = '5'; }
if ($_SESSION['league_picks'] == '11') { $limit = '16'; }
?>
   </div>
   <script type="text/javascript">
//Syntax: checkboxlimit(checkbox_reference, limit)
      checkboxlimit(document.forms.my_picks, <?php echo $limit; ?>)
   </script>
<?php
do_footer('bottom');
mysqli_close($conn);
?>