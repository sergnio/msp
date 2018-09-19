<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
if (!session_start()) {
   writeDataTofile("ajax_support_users.php Session failed to start", __FILE__, __LINE__);
}

/*
:mode=php: 

   file: ajax_support_users.php 
   date: 8-2017
 author: srm
   desc: some user ajax support
                       
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php'; 
$msg = '';

// league and site edits
@ $data =      (!empty($_POST['data']))       ? $_POST['data']  : ''; 

$jsondata = json_decode($data);
$method = $jsondata->{'method'};


$status = 0;
$ermsg_text = '';
$supporting_error = '';
$field = '';
$ref_access_status_message = '';
$ref_status_text = '';
$ermsg_text_array = array();
$status_array = array();

writeDataToFile('BEGIN C:\xampp\htdocs\nflx\ajax_support_users.php', __FILE__, __LINE__);

//Check permission to use this page
$hasAccess = validateUser('admin', 'status', $ref_access_denied_message);
if(!$hasAccess)
{
	header("Location : /index.php");
	die(); //no access
}

if($method==="resetpassword") //reset password call.
{
	$username = $jsondata->{'username'};
	$password = $jsondata->{'password'};
	$userid = $jsondata->{'userid'};
	
	$status = true;
	$error = '';
	
	//lookup the user
	while(1)
	{
		if(!isValidUserAccount($username, 'username'))
		{
			$status = false;
			break;
		}
		

		//get user info
		getUserInfo($userid, $fnameret, $lnameret, $usernamered, $emailret, $ref_status_text);
		
		if (!empty($fnameret))
		{
			$new_password_hash = hash('sha256', $password);
			
			if (!reset_password($userid, $new_password_hash, $ref_status_text)) {
				$error = ("A serious error occurred.  Please contact the site administrator.  The password was not changed.");
			break;
			}
			
			//future use location
			if (!recordForgotPasswordChange($userid, $password, $ref_status_text)) {
				$error = "Your password was changed, however, an unknown error occurred and the new one will not be mailed. " .
				"Please contact the site administrator to obtain your new password. ";
				break;
			}
		   	
			//send email
			notify_password($fnameret, $emailret, $password);	
		}
	
		break;
	}

	$status_array = array('status', 'ok');
}



$returnjson =  json_encode($status_array);
writeDataToFile("status array json encoded: " . $returnjson, __FILE__, __LINE__);
echo $returnjson;
ob_end_flush();
exit();
?>