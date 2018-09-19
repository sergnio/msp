<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');

validateUser();

$conn = db_connect();
$result = $conn->query("SELECT * FROM users WHERE username='".$_SESSION['valid_user']."'");
mysqli_close($conn);
   
$row=$result->fetch_object();
$old_fname = $row->fname;
$old_lname = $row->lname;
$old_useremail = $row->email;
$old_password = $row->password;
$user_name = $row->username;

$fname =                (!empty($_POST['fname']            )) ? $_POST['fname']                    : '';
$lname =                (!empty($_POST['lname']            )) ? $_POST['lname']                    : '';
$useremail =            (!empty($_POST['email']            )) ? $_POST['email']                    : '';
$old_password_plain =   (!empty($_POST['old_password']     )) ? trim($_POST['old_password'])       : '';
$new_plain_pw =         (!empty($_POST['new_password']     )) ? trim($_POST['new_password'])       : '';
$new2_plain_pw =        (!empty($_POST['confirm_password'] )) ? trim($_POST['confirm_password'])   : '';
$user_id =              (!empty($_SESSION['user_id'] ))       ? $_SESSION['user_id']               : '';

writeDataToFile("profile2.php 
$fname =             
$lname =             
$useremail =         
$old_password_plain =
$new_plain_pw =               
$new2_plain_pw =", __FILE__, __LINE__);                 

$_SESSION['profileemail'] = $useremail;
$_SESSION['profilefname'] = $fname;
$_SESSION['profilelname'] = $lname;

$status = 1;
$pending_error = false;
$new_password_hash = '';
$ref_status_text = '';
$msg = '';
while (1) {
   
   if (!$fname || !$lname || !$useremail || !$user_id) {
      formatSessionMessage("First and Last names and email are required.  Please complete the form.", 'info', $msg, "pro2-47 !$fname || !$lname || !$useremail || !$user_id");
      setSessionMessage($msg, 'error');
      $status = 0;
      break;
   }
   if (!valid_email($useremail)) {
      formatSessionMessage("The email address $useremail is not valid.", 'info', $msg);
      setSessionMessage($msg, 'error');
      $status = 0;
   }
   if(areDisallowCharacters($fname)) {
      formatSessionMessage("The user's first name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.", 'info', $msg, "pro2-58");
      setSessionMessage($msg, 'error');
      $status = 0;
   }
   if(areDisallowCharacters($lname)) {
      formatSessionMessage("The user's last name may contain only alphanumeric characters.  Apostrophes, dashes and underscores are also allowed.", 'info', $msg, "pro2-63");
      setSessionMessage($msg, 'error');
      $status = 0;
   }
   
   if ($new_plain_pw || $new2_plain_pw && $status == 1) {  // entered because he wants a password change
      if (!$new_plain_pw || !$new2_plain_pw) {
         formatSessionMessage("Both the new and confirm passwords must be complete.", 'info', $msg, "pro2-70");
         setSessionMessage($msg, 'error');
         $status = 0;
         break;
      }
      if ($new_plain_pw !== $new2_plain_pw) {
         formatSessionMessage("The new and confirm passwords do not match", 'info', $msg, "pro2-76");
         setSessionMessage($msg, 'error');
         $status = 0;
         break;
      }
      $new_password_hash = hash('sha256',$new_plain_pw);
   }
   break;
}

if (!$status) {
   header( 'Location: profile.php' ) ;
   die();
}

$mysql_nopwchange = "
   UPDATE users 
      SET fname =  ?, 
          lname = ?, 
          email = ?
    WHERE id = ?";
    
$mysql_pwchange = "
   UPDATE users 
      SET fname =  ?, 
          lname = ?, 
          email = ?,
          password = ?,
          temppassword = ''
    WHERE id = ?";    
    
while (1) {
   
   $conn = db_connect();
   if (!$conn) {
      formatSessionMessage("The database is unavailable.", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   if ($new_password_hash) {
      
      if (!checkUserPassword($user_id, $old_password_plain)) {
         formatSessionMessage("The current password in invalid.", 'info', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
      $ans = runSql($mysql_pwchange, array("ssssi", $fname, $lname, $useremail, $new_password_hash, $user_id), 1, $ref_status_text);
      if (!$ans) {
         formatSessionMessage("The profile failed to update.", 'danger', $msg, "pro2-124 $ref_status_text");
         setSessionMessage($msg, 'error');
         $status = 0;
         break;
      } else {
         formatSessionMessage("Your profile, with new password, was successfully updated.", 'success', $msg, "pro2-129 $ans");
         setSessionMessage($msg, 'error');
         break;
      }
      
   } else { // no password change
      
      $ans = runSql($mysql_nopwchange, array("sssi", $fname, $lname, $useremail, $user_id), 1, $ref_status_text);
      if (!$ans) {
         if ($ans === 0 || $ans === null) {
            formatSessionMessage("No updates were made.  There was nothing to change.", 'info', $msg, "pro2-139 $ref_status_text");
            setSessionMessage($msg, 'error');
         } else {
            formatSessionMessage("The profile failed to update.", 'danger', $msg, "pro2-139 $ref_status_text");
            setSessionMessage($msg, 'error');
            $status = 0;
            break;
         }
      } else {
         formatSessionMessage("Your profile was successfully updated.  Your password was not changed.", 'success', $msg, "pro2-148 $ans");
         setSessionMessage($msg, 'error');
         break;
      }
   }
   break;
}

header( 'Location: profile.php' ) ;
die();
?>