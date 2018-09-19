<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: adminregister.php
   date: apr-2016
 author: original
   desc:
marbles: 

*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

@ $league_id = (isset($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';
if (!$league_id) {
   formatSessionMessage("You must be associated with a league to invite players.", 'info', $msg, "ar-22");
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}

validateUser('commissioner');

do_header('MySuperPicks.com - Invite Users');
do_nav();
?> 
   <div class="container">
<?php echo_container_breaks(); ?>
<?php

@$login = (!empty($_GET['login'])) ? sanitize($_GET['login']) : '';
@$update = (!empty($_GET['update'])) ? sanitize($_GET['update']) : '';
@$error = (!empty($_GET['error'])) ? sanitize($_GET['error']) : '';
@$league_name = (!empty($_SESSION['league_name'])) ? $_SESSION['league_name'] : '';

writeDataToFile(print_r($_SESSION, true), __FILE__, __LINE__);
echoSessionMessage();

?>
      <?php echo "<h1 class='text-center'>Invite Users to League: <i>$league_name</i></h1>"; ?>
      <br />
      <br />
      <br />
      <p class="lead muted text-center">Separate multiple email addresses with a comma.</p>          
      <form action="adminregister2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
         <div class="form-group">
            <label for="register_email" class="col-sm-2 control-label">New User Email(s)</label>
            <div class="col-sm-8">  
               <div class="input-group">
                  <input type="text" class="form-control" id="register_email" name="register_email" placeholder="Email address(es) to send code" value="<?php if(!empty($_SESSION['register_email'])) { echo $_SESSION['register_email']; } ?>" />
                  <span class="input-group-btn">
                     <button type="submit" class="btn btn-success">Send Invitations(s)</button>
                  </span>
               </div><input type="hidden" name="submit" value="true" />
            </div>
         </div>
      </form>
<?php 

unset($_SESSION['register_email']);
?>
   </div>
<?php
do_footer('bottom');
?>