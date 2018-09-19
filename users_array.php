<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('admin');


do_header('MySuperPicks.com - Edit Users');
do_nav();
?> 
   <div class="container">
<?php
echo_container_breaks();

//writeDataToFile("TOP users.php ... " . print_r($_GET, true), __FILE__, __LINE__);

$login = '';
if (!empty($_GET['login'])) { $login = $_GET['login']; }
$login = sql_sanitize($login);
$login = html_sanitize($login);
$update = '';
if (!empty($_GET['update'])) { $update = $_GET['update']; }
$update = sql_sanitize($update);
$update = html_sanitize($update);
$error = '';
if (!empty($_GET['error'])) { $error = $_GET['error']; }
$error = sql_sanitize($error);
$error = html_sanitize($error);

if ($login == 'success') {
   echo "<div class=\"alert alert-success\">You are logged in!</div>";
}
if ($login == 'fail') {
   echo "<div class=\"alert alert-danger\">We were not able to log you in. Please check your username and password and try again. Thank you.</div>";
}
if ($update == '2') {
   echo "<div class=\"alert alert-success\">User Deleted!</div>";
}
if ($update == '1') {
   echo "<div class=\"alert alert-success\">User Updated!</div>";
}
if ($update == '0') {
   echo "<div class=\"alert alert-danger\">The user was not updated/added. Please check the information and try again.</div>";
}
if (strpos($error,'username')) {
?>
  <div class="alert alert-danger">Error - The username is already used.  Choose another.</div>
<?php
}
if (strpos($error,'empty')) {
?>
  <div class="alert alert-danger">Error - There is a blank entry.</div>
<?php
}
if (strpos($error,'sdfasdfasdfasdfasdf future')) {
?>
  <div class="alert alert-danger">Error - The user type is not valid.</div>
<?php
}
if (strpos($error,'email')) { 
?>
  <div class="alert alert-danger">Error - The email you entered was not valid. Please check it and try again.</div>
<?php
}
if (strpos($error,'match')) {
?>
  <div class="alert alert-danger">Error - The new passwords did not match. Please try again.</div>
<?php
}
if (strpos($error,'league')) {
?>
  <div class="alert alert-danger">Error - The league does not exist.</div>
<?php
}
if (strpos($error,'newuser')) {
?>
  <div class="alert alert-danger">Error - System failed to create a new user.  Please contact site administrator.</div>
<?php
}
if (strpos($error,'usertype')) {  // not used ... leave for future
?>
  <div class="alert alert-danger">Error - Please select a user mode; Administrator or User.</div>
<?php
}if (strpos($error,'rights')) {  // not used ... leave for future
?>
  <div class="alert alert-danger">Error - Update was not executed.  You do not have permission.</div>
<?php
}
if(validateUser('admin', 'status')) {  // Since this is not league (commissioner) based, you must be an administrator
   $username_add =         (!empty($_SESSION['username_add'])) ?           $_SESSION['username_add'] :      '';
   $fname_add =            (!empty($_SESSION['fname_add'])) ?              $_SESSION['fname_add'] :         '';
   $lname_add =            (!empty($_SESSION['lname_add'])) ?              $_SESSION['lname_add'] :         '';
   $usertype_add =         (!empty($_SESSION['usertype_add'])) ?           $_SESSION['usertype_add'] :      'noselect';
   $email_add =            (!empty($_SESSION['email_add'])) ?              $_SESSION['email_add'] :         '';
   $new_password_add =     (!empty($_SESSION['new_password_add'])) ?       $_SESSION['new_password_add'] :  '';
   $confirm_password_add = (!empty($_SESSION['confirm_password_add'])) ?   $_SESSION['new_password_add'] :  '';
   
   // This is not a league based page
   $current_user_league_id = ''
   //$current_user_league_id = (!empty($_SESSION['league_id'])) ?   $_SESSION['league_id'] :  '';
?>
<br />
<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><button class="btn btn-success">Add User  <span class="caret"></span></button></a>
<div id="collapseOne" class="panel-collapse collapse">
         <form action="adduser.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
          <br /> 
                <div class="form-group">
                <label for="username_add" class="col-sm-2 control-label">Username</label>
                <div class="col-sm-8">  
                    <input type="text" class="form-control" id="username_add" name="username_add" placeholder="User Name" value="<?php echo $username_add; ?>" />
                </div>
            </div><div class="form-group">
                <label for="fname_add" class="col-sm-2 control-label">First Name</label>
                <div class="col-sm-8">  
                    <input type="text" class="form-control" id="fname_add" name="fname_add" placeholder="First Name" value="<?php echo $fname_add; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label for="lname_add" class="col-sm-2 control-label">Last Name</label>  
                <div class="col-sm-8">  
                    <input type="text" class="form-control" id="lname_add" name="lname_add" placeholder="Last Name" value="<?php echo $lname_add; ?>" />
                </div>
            </div>
         <div class="form-group">
                <label for="usertype_add" class="col-sm-2 control-label">User Type</label>  
                <div class="col-sm-8">
                    <select name='usertype_add' class='form-control input-medium'>
                      <option value='noselect' <?php if ($usertype_add == "noselect") { echo "selected='selected'";} ?>>Select User Type</option>
                      <option value='admin' <?php if ($usertype_add == "admin") { echo "selected='selected'";} ?>>Administrator</option>
                      <option value='user' <?php if ($usertype_add == "user") { echo "selected='selected'";} ?>>User</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="email_add" class="col-sm-2 control-label">Email</label>  
                <div class="col-sm-8">  
                    <input type="email_add" class="form-control" id="email_add" name="email_add" placeholder="Email" value="<?php echo $email_add; ?>" />
                </div>
            </div>               
      <div class="form-group">
                <label or="new_password_add" class="col-sm-2 control-label">Password</label>  
                <div class="col-sm-8">  
                    <input type="password" class="form-control" id="new_password_add" name="new_password_add" placeholder="New Password" value="<?php echo $new_password_add; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label or="confirm_password_add" class="col-sm-2 control-label">Confirm Password</label>  
                <div class="col-sm-8">  
                    <input type="password" class="form-control" id="confirm_password_add" name="confirm_password_add" placeholder="Confirm New Password" value="<?php echo $confirm_password_add; ?>" />
                </div>
            </div>
            <div class="form-group">
              <div class="text-center">
                <button type="submit" class="btn btn-primary">Submit User Info</button>
              </div>
            </div>

        </form>
      </div>
        <h3>Edit <?php if($current_user_league_id) {echo "League";} else { echo "All"; } ?> Users</h3>
        <br />

<?php 
   if (!echoEditSiteUsers()) {
      echoSessionMessage();
   }
   echo "<br /><br /><br /><br />";
} else {
?>
        <p class="lead">You are not allowed to view this page.</p>
<?php
}
?>
    </div>
<?php
do_footer('bottom');
?>