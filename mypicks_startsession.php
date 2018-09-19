<?php
$lifetime= 60 * 60 * 24 * 30;  // 30 days
// Prevents javascript XSS attacks aimed to steal the session ID
ini_set('session.cookie_httponly', 1);

// Prevent Session ID from being passed through  URLs
ini_set('session.use_only_cookies', 1);
	
// Uses a secure connection (HTTPS) 
ini_set('session.cookie_secure', 1); 
session_start();
//setcookie(session_name(),session_id(),time()+$lifetime);
setcookie(session_name(),session_id(),time()+$lifetime, "/", ".mysuperpicks.com", true,true);
//http://php.net/manual/en/function.session-set-cookie-params.php
?>