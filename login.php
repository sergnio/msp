<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: login.php
   date: apr-2016
 author: original
   desc: Linked to the index.php "Log in" button inside the drop down Log In on main.  
  notes: The url was mangled - the '/'s were replaced.  It bombed with a
     url of dirfile, which should have /dir/file.  See TODO01
marbles: 
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
$msg = '';

// This page doesn't require validateUser()
// All login scripts now prepare.  It's not necessary to scrub the POST data.
// SESSION var are populated only with database data.

$username = (!empty($_POST['username'])) ? trim($_POST['username']) : '';
$password = (!empty($_POST['password'])) ? trim($_POST['password']) : '';
$url = (!empty($_POST['url'])) ? $_POST['url'] : '';
$keep = (!empty($_POST['keep'])) ? $_POST['keep'] === "true" : false; //keep session vars
writeDataToFile("login.php ", __FILE__, __LINE__);

// $url = str_replace('/', '', $url);  // Disabled. Why is this url being mangled?  hfs 4/7/2016 TODO01
if (($url == 'password_reset2.php') || (empty($url))) { $url = 'index.php'; }
// login() (in mypicks_db.php) will set SESSION var 'login'.  This will be used
// instead of url encoding

if (login2($username, $password, !($keep) )) {
	
	if($_SESSION['login'] == 'updatepassword')
	{
		$_SESSION['login'] = 'success';
		$mesg = formatSessionMessage('Your password needs to be updated.', 'success', $formatted_ret_mesage, '');
		unset($_SESSION['messages']['login']);
		setSessionMessage($formatted_ret_mesage, 'login');
		header( 'Location: /profile.php');		
	}
	else
	{
		header( 'Location: '.$url.'?login=successXXX' );
	}
	die();
} else {
   writeDataToFile( "Login failed.  This is the url ''Location:$url?login=failXXX",  __FILE__, __LINE__);
	header( 'Location: '.$url.'?login=fail' );
	die();
}
?>