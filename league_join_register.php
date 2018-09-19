<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
require_once 'mypicks_phpgeneral.php';
$msg = '';

$league_id =            (!empty($_SESSION['join_league_id'])) ?   $_SESSION['join_league_id'] : '';

$status = 0;
$first_name = (!empty($_SESSION['register_fname'])) ?   $_SESSION['register_fname'] : '';;
$last_name = (!empty($_SESSION['register_lname'])) ?   $_SESSION['register_lname'] : '';;
$register_player_name = (!empty($_SESSION['register_player_name'])) ?   $_SESSION['register_player_name'] : '';
$username = (!empty($_SESSION['register_username'])) ?   $_SESSION['register_username'] : '';;
$email = (!empty($_SESSION['register_email'])) ?   $_SESSION['register_email'] : '';;

$ref_status_text = '';
$league_name = '';


while (1) {
   if (!getCommissonerInfo($league_id, $first_name, $last_name, $user_id, $email, $ref_status_text)) {
      formatSessionMessage("We were unable to service the league information.", 'info', $msg, "r2-51 $ref_status_text");
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$league_name = getLeagueName($league_id)) {
      formatSessionMessage("We were unable to service the league information.", 'info', $msg, "r2-56");
      setSessionMessage($msg, 'error');
      break;
   }
   $status = 1;
   break;
}

if (!$status) {
   header('Location: index.php');
   die();
}

do_header('MySuperPicks.com - Register');
do_nav();
?>
<div class="container">
<?php
echoContainerBreaks();
echoSessionMessage();
echo "   <form action='league_join_register2.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'>
      <h1 class='text-center'>League Registration Information</h1>
      <p>Thank you for responding to league Admin $first_name $last_name's invitation to join league <i>$league_name</i>.  Before
      joining, your account must be created.  Please complete the form.  Your membership will be awarded on completion. For answers to any questions,
      please mail the league Admin at <b>$email</b> or contact us directly via the <b>Contact Us</b> menu link above.
      Welcome to MySuperPicks!</p>
      <br />";
?>

      <noscript>
          <div style='text-align:center;color:red;'><b>You must have javascript (cookies) enabled to play.</b></div>
      </noscript>
      <div class="form-group">
         <label for="register_username" class="col-sm-2 control-label">Username</label>
         <div class="col-sm-8">
            <!-- <input type="text" class="form-control" id="register_username" name="register_username" placeholder="Username" value="<?php echo $username; ?>" /> -->
            <input type="text" class="form-control" id="register_username" name="register_username" placeholder="Username. This is your account that you will use to log in to the site" />
         </div>
      </div>
      <div class="form-group">
         <label for="register_fname" class="col-sm-2 control-label">First Name</label>
         <div class="col-sm-8">
            <!-- <input type="text" class="form-control" id="register_fname" name="register_fname" placeholder="First Name" value="<?php echo $first_name; ?>" /> -->
            <input type="text" class="form-control" id="register_fname" name="register_fname" placeholder="First Name" />
         </div>
      </div>
      <div class="form-group">
         <label for="register_lname" class="col-sm-2 control-label">Last Name</label>
         <div class="col-sm-8">
            <!-- <input type="text" class="form-control" id="register_lname" name="register_lname" placeholder="Last Name" value="<?php echo $last_name; ?>" /> -->
            <input type="text" class="form-control" id="register_lname" name="register_lname" placeholder="Last Name" />
         </div>
      </div>
      <div class="form-group">
         <label for="register_email" class="col-sm-2 control-label">Email</label>
         <div class="col-sm-8">
            <!-- <input type="email" class="form-control" id="register_email" name="register_email" placeholder="Email" value="<?php echo $email; ?>" /> -->
            <input type="email" class="form-control" id="register_email" name="register_email" placeholder="Email" />
         </div>
      </div>
      <div class="form-group">
         <label for="register_player_name" class="col-sm-2 control-label">Player Name</label>
         <div class="col-sm-8">
            <!-- <input type="text" class="form-control" id="register_player_name" name="register_player_name" placeholder="Name displayed in league activities." value="<?php echo $register_player_name; ?>" /> -->
            <input type="text" class="form-control" id="register_player_name" name="register_player_name"
            placeholder="Name displayed in league activities. You can change this later in your profile settings" />
         </div>
      </div>
      <div class="form-group">
         <label or="register_new" class="col-sm-2 control-label">New Password</label>
         <div class="col-sm-8">
             <input type="password" class="form-control" id="register_new" name="register_new" placeholder="New Password" />
         </div>
      </div>
      <div class="form-group">
         <label or="register_new2" class="col-sm-2 control-label">Confirm New Password</label>
         <div class="col-sm-8">
             <input type="password" class="form-control" id="register_new2" name="register_new2" placeholder="Confirm New Password" />
         </div>
      </div>
      <div class="form-group">
         <div class="text-center">
           <button type="submit" class="btn btn-warning"><span class="glyphicon glyphicon-ok"></span> Submit Registration</button>
         </div>
      </div>
      <input type="hidden" name="league_id" value="<?php echo $league_id; ?>" />
   </form>
   <br />
   <br />
</div>
<?php
do_footer('bottom');
?>
