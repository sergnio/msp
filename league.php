<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

// HOT link - do not validate

$error = '';
if (isset($_GET['error'])) {
   $error = $_GET['error'];
   $error = sql_sanitize($error);
   $error = html_sanitize($error);
} 
if (isset($_GET['erupdateror'])) {
   $update = $_GET['update'];
   $update = sql_sanitize($update);
   $update = html_sanitize($update); 
}

do_header('MySuperPicks.com - Start Your League');
do_nav();
?>

<div class="container"><br /><br /><br /><br />
<?php
if (!empty($error) && $error!=='error') {
?>
   <br /><div class="alert alert-danger"><h4>Please check error(s) in red below.</h4></div><br />
<?php
}
if ($error=='error') {
?>
   <div class="alert alert-danger">Alert - There was an error on our part in your registration. If this error persists, please contact us right away. Thank you.</div>

<?php
   if (!empty($name_option)) { 
      echo "<p class=\"lead muted text-center\"><small><em>".$name_option."</em> is available</small></p>";
   }
?>
<?php
}
?>
   <form action="league2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
      <h1 class="text-center">League Registration Information</h1>
      <p class="lead muted text-center">Once you fill out the form below, you will
        be registered and sent to the payment page. After payment is processed, 
        you will be sent to your league setup page.
      </p>
      <br />
<?php
if (strpos($error,'username')) {
?>
      <div class="alert alert-danger">Alert - The username you entered was already chosen. Please choose another one and try again.</div>

<?php
if (!empty($name_option)) { echo "<p class=\"lead muted text-center\"><small><em>".$name_option."</em> is available</small></p>"; }
?>
<?php
}
?>
      <div class="form-group">
         <label for="register_username" class="col-sm-2 control-label">Username</label>
         <div class="col-sm-8">  
             <input type="text" class="form-control" id="register_username" name="register_username" placeholder="Username" value="<?php if (isset($_SESSION['register_username'])) { echo $_SESSION['register_username']; }?>" />
         </div>
      </div>
      <div class="form-group">
         <label for="register_fname" class="col-sm-2 control-label">First Name</label>
         <div class="col-sm-8">  
             <input type="text" class="form-control" id="register_fname" name="register_fname" placeholder="First Name" value="<?php if (isset($_SESSION['register_fname'])) { echo $_SESSION['register_fname']; } ?>" />
         </div>
      </div>
      <div class="form-group">
         <label for="register_lname" class="col-sm-2 control-label">Last Name</label>  
         <div class="col-sm-8">  
             <input type="text" class="form-control" id="register_lname" name="register_lname" placeholder="Last Name" value="<?php if (isset($_SESSION['register_lname'])) { echo $_SESSION['register_lname']; } ?>" />
         </div>
      </div>
<?php
if (strpos($error,'email')) {
?>
      <div class="alert alert-danger">Error - The email you entered was not valid. Please check it and try again.</div>
<?php
}
?>    
      <div class="form-group">
         <label for="register_email" class="col-sm-2 control-label">Email</label>  
         <div class="col-sm-8">  
            <input type="email" class="form-control" id="register_email" name="register_email" placeholder="Email" value="<?php if (isset($_SESSION['register_email'])) { echo $_SESSION['register_email']; } ?>" />
         </div>
      </div>
<?php
if (strpos($error,'match')) {
?>
      <div class="alert alert-danger">Error - The new passwords did not match. Please try again.</div>
<?php
}
?>               
      <div class="form-group">
         <label or="register_new" class="col-sm-2 control-label">New Password</label>  
         <div class="col-sm-8">  
            <input type="password" class="form-control" id="register_new" name="register_new" placeholder="New Password" value="<?php if (isset($_SESSION['register_new'])) { echo $_SESSION['register_new']; } ?>" />
         </div>
      </div>
      <div class="form-group">
         <label or="register_new2" class="col-sm-2 control-label">Confirm New Password</label>  
         <div class="col-sm-8">  
            <input type="password" class="form-control" id="register_new2" name="register_new2" placeholder="Confirm New Password" value="<?php if (isset($_SESSION['register_new2'])) { echo $_SESSION['register_new2']; } ?>" />
         </div>
      </div>
      <div class="form-group">
         <div class="text-center">
            <button type="submit" class="btn btn-warning"><span class="glyphicon glyphicon-ok"></span> Submit Registration</button>
         </div>
      </div>
   </form>
   <br />
   <br />
</div> 
<?php
do_footer('bottom');
?>