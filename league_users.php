<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: league_users.php
   date: apr-2016
 author: original
   desc: This is a commissioner level page allowing edits to league
      member accounts.
      
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');

do_header('MySuperPicks.com - Edit Users');
do_nav();
?> 
   <div class="container">
<?php
echoContainerBreaks();
echoSessionMessage();

   $username_add = (!empty($_SESSION['username_add'])) ? $_SESSION['username_add'] : '';
   $fname_add =    (!empty($_SESSION['fname_add'])) ?    $_SESSION['fname_add'] : '';
   $lname_add =    (!empty($_SESSION['lname_add'])) ?    $_SESSION['lname_add'] : '';
   $usertype_add = (!empty($_SESSION['usertype_add'])) ? $_SESSION['usertype_add'] : 'noselect';
   $email_add =    (!empty($_SESSION['email_add'])) ?    $_SESSION['email_add'] : '';
   $new_password_add =     (!empty($_SESSION['new_password_add'])) ?       $_SESSION['new_password_add'] : '';
   $confirm_password_add = (!empty($_SESSION['confirm_password_add'])) ?   $_SESSION['confirm_password_add'] : '';
   
   $league_name = (!empty($_SESSION['league_admin'])) ? getLeagueName($_SESSION['league_admin']) : '';
?>
      <br />
      <h3>Edit League <i><?php echo $league_name; ?></i> Players</h3>
      <h5>Players must have active Site and League accounts to play.</h5>
      <br />
      <div id='IDd_ajaxmessageshere' style='text-align:center;'></div>
<?php 
      if(!echoEditLeagueUsers($_SESSION['league_admin'])) {
         header('Location league_users.php');
         die();
      }
      echo "<br /><br /><br /><br />";
?>
   </div>
<?php
do_footer('clean');
?>