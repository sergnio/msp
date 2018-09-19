<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser();

do_header('MySuperPicks.com - Profile');
do_nav();
?>

<div class="container">
<?php echo_container_breaks();
echoSessionMessage();
?>
   <div class="hidden-sm hidden-md hidden-lg">
      <br />
      <br />
      <br />
   </div>

<?php
$conn = db_connect();
$result = $conn->query("SELECT * FROM users WHERE username='".$_SESSION['valid_user']."'");
$row=$result->fetch_object();
$username = $row->username;
$fname = $row->fname;
$lname = $row->lname;
$email = (!empty($_SESSION['profileemail'])) ? $_SESSION['profileemail'] : $row->email;
$fname = (!empty($_SESSION['profilefname'])) ? $_SESSION['profilefname'] : $row->fname;
$lname = (!empty($_SESSION['profilelname'])) ? $_SESSION['profilelname'] : $row->lname;



// Don't shuffle passwords around
$old_pw = '';
$new_pw = '';
$new_pw2 = '';

echo "
   <form action='profile2.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'> 
      <h3>Profile Information</h3>
      <br />
      <div class='form-group'>
         <label for='fname' class='col-sm-2 control-label'>First Name</label>
         <div class='col-sm-8'>  
            <input type='text' class='form-control' id='fname' name='fname' placeholder='First Name' value='$fname' />
         </div>
      </div>
      <div class='form-group'>
         <label for='lname' class='col-sm-2 control-label'>Last Name</label>  
         <div class='col-sm-8'>  
            <input type='text' class='form-control' id='lname' name='lname' placeholder='Last Name' value='$lname' />
         </div>
      </div>
      <div class='form-group'>
         <label for='email' class='col-sm-2 control-label'>Email</label>  
         <div class='col-sm-8'>  
            <input type='email' class='form-control' id='email' name='email' placeholder='Email' value='$email' />
         </div>
      </div>
      <div class='form-group'>
         <label for='old_password' class='col-sm-2 control-label'>Old Password<br />(for pw change)</label>  
         <div class='col-sm-8'>  
            <input type='password' class='form-control' id='old_password' name='old_password' placeholder='Old Password' value='$old_pw' />
         </div>
      </div>
      <div class='form-group'>
         <label or='new_password' class='col-sm-2 control-label'>New Password</label>  
         <div class='col-sm-8'>  
            <input type='password' class='form-control' id='new_password' name='new_password' placeholder='New Password' value='$new_pw' />
         </div>
      </div>
      <div class='form-group'>
         <label or='confirm_password' class='col-sm-2 control-label'>Confirm New Password</label>  
         <div class='col-sm-8'>  
            <input type='password' class='form-control' id='confirm_password' name='confirm_password' placeholder='Confirm New Password' value='$new_pw2' />
         </div>
      </div>
      <div class='form-group'>
         <div class='text-center'>
            <button type='submit' class='btn btn-warning'>Update Profile</button>
         </div>
      </div>
   </form>
   <br />
   <br />
</div>";
unset($_SESSION['profileemail'],$_SESSION['profilefname'],$_SESSION['profilelname']);
do_footer('bottom');
?>