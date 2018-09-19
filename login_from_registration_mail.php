<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: login_from_registration_mail.php
   date: apr-2016
 author: original
   desc: A new player has been invited to a league.  An active site user already
      has the confirm email. If he already is a user, let him login
marbles: 
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

// This page doesn't require validateUser()
// All login scripts now prepare.  It's not necessary to scrub the POST data.
// SESSION var are populated only with database data.


$confirm_code =       (!empty($_SESSION['registerconfirmcode'])) ? $_SESSION['registerconfirmcode'] : '';
$league_id =          (!empty($_SESSION['registerconfirmleagueid'])) ? $_SESSION['registerconfirmleagueid'] : '';
$email =              (!empty($_SESSION['registerconfirmemail'])) ? $_SESSION['registerconfirmemail'] : '';
$confirm_email_date = (!empty($_SESSION['registerconfirmdate'])) ? $_SESSION['registerconfirmdate'] : '';
$confirm_user_id =    (!empty($_SESSION['registerconfirmuserid'])) ? $_SESSION['registerconfirmuserid'] : '';

if (!$confirm_code || !$league_id || !$email || !$confirm_email_date || !$confirm_user_id) {
   formatSessionMessage("League registration has failed.  The email address indicated you have a current account but information is missing. Please contact the site administrator.", 'warning', $msg);
   setSessionMessage($msg, 'error');
   header('Location index.php');
   die();
}

$league_name = getLeagueName($league_id);
if (empty($league_name)) {
   formatSessionMessage("The league does not exist.  Please have the League Admin resend the invitation email.", 'warning', $msg);
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}
$_SESSION['registerleaguename'] = $league_name;

do_header('MySuperPicks.com - Contact Us');
do_nav();

?> 
   <div class="container">
<?php
echo_container_breaks();
echoSessionMessage();
?>

      <div class="hidden-sm hidden-md hidden-lg">
         <br />
         <br />
         <br />
      </div>
      <h1 class="text-center">Join League <i><?php echo $league_name; ?></i></h1>
      <br />
      <p class="text-center">You were invited to join the above league.  Based on the email address <?php echo $email; ?> you presently have an active account.  Please login using your current password.</p>
      <br />
      <form action="login_from_registration_mail2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
         <div class="form-group">
            <label for="registerconfirmpassword" class="col-sm-2 control-label">Password</label>
            <div class="col-sm-8">  
               <input type="password" class="form-control" name="registerconfirmpassword" placeholder="Your password" />
            </div>
         </div>
         <div class="form-group">
            <label for="registerconfirmplayername" class="col-sm-2 control-label">Player Name</label>
            <div class="col-sm-8">  
               <input type="text" class="form-control" name="registerconfirmplayername" placeholder="League Player Name.  This is the public name displayed in league standings. " />
            </div>
         </div>
         <div class="form-group">
            <div class="text-center">
               <button type="submit" class="btn btn-default btn-sm">Log in <span class="glyphicon glyphicon-log-in"></span></button>
            </div>
         </div>
      </form>
   </div>
<?php
unset($_SESSION['name']);
unset($_SESSION['email']);
unset($_SESSION['message']);
?>  
<?php
do_footer('bottom');
?>