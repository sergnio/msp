<?php

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
require_once 'mypicks_once.php';
$msg = '';

header('HTTP/1.1 200 OK'); // Send an empty HTTP 200 OK response to acknowledge receipt of the notification 
  
   formatSessionMessage("header", 'info', $msg);
   setSessionMessage($msg, 'error');
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
   $value = urlencode(stripslashes($value));
   $req .= "&$key=$value";
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
 
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

if (!$fp) {
   formatSessionMessage("socket fail", 'info', $msg);
   setSessionMessage($msg, 'error');
   header( 'Location: index.php') ;  
   die();
} else {
   formatSessionMessage("good ftp", 'info', $msg);
   setSessionMessage($msg, 'error');
   header( 'Location: index.php') ;  
   fputs ($fp, $header . $req);
   while (!feof($fp)) {
      $res = fgets ($fp, 1024);
      if (strcmp ($res, "VERIFIED") == 0) {
    
         // PAYMENT VALIDATED & VERIFIED!
    
      } else if (strcmp ($res, "INVALID") == 0) {
   formatSessionMessage("invalid", 'info', $msg);
   setSessionMessage($msg, 'error');
   header( 'Location: index.php') ;  
   die();
    
         // PAYMENT INVALID & INVESTIGATE MANUALY!
      }
   }
   fclose ($fp);
   formatSessionMessage("end - fail?", 'info', $msg);
   setSessionMessage($msg, 'error');
   header( 'Location: index.php') ;  
   die();
}
?>