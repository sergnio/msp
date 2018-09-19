<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: admin.php
   date: apr-2016
 author: original
   desc: 
marbles: 

*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

validateUser('admin');

do_header('MySuperPicks.com - Administration');
do_nav();
?> 
    <div class="container">
<?php echo_container_breaks();
echoSessionMessage();?>
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
  echo "<div class=\"alert alert-success\">You are logged in!</div>";
}
if ($login == 'fail') {
  echo "<div class=\"alert alert-danger\">We were not able to log you in. Please check your username and password and try again. Thank you.</div>";
}
?>
        <h1 class="text-center">Administration Page</h1>
<h3>Schedules</h3>
<div class="btn-toolbar">
<a href="schedules_nsp.php" class="btn btn-success btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-list-alt"></span> Edit Schedules/Scores</a>
<a href="setweek.php" class="btn btn-success btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-hand-right"></span> Set Active Week</a>
</div>
<h3>Users</h3>
<div class="btn-toolbar">
<a href="adminregister.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-send"></span> Send Registration Code</a>
<a href="users.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-user"></span> Edit Users</a>
<a href="email_site.php" class="btn btn-primary btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-envelope"></span> Email All Site</a>
</div>
<h3>Site Pages</h3>
<div class="btn-toolbar">
<a href="adminmessage.php" class="btn btn-warning btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-pencil"></span> Edit Homepage</a>
</div>
<h3>Site Operations</h3>
<div class="btn-toolbar">
<a href="adminoperations.php" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-wrench"></span> Operations</a>
<a href="adminsitemaintmessage.php" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-pencil"></span> Maintenance Message</a>
<a href="adminleagues.php" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-knight"></span> Leagues</a>
<a href="adminreports.php" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-briefcase"></span> Reports</a>
</div>
<h3>Content</h3>
<div class="btn-toolbar">
<a href="edithtml.php?key=against-the-spread" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-pencil"></span> Against the Spread HTML</a>
<a href="edithtml.php?key=scoreboard" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-pencil"></span> Scoreboard</a>
<a href="edithtml.php?key=scoreboard-footer" class="btn btn-danger btn-lg col-md-3" id="admin_button" role="button"><span class="glyphicon glyphicon-pencil"></span> Scoreboard Footer</a>
</div>
</div>
    </div>
<?php
do_footer('clean');
?>