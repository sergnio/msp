<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');

foreach ($_POST['to_field'] as $to_field) {
   $to_field = sql_sanitize($to_field);
   $to_field = html_sanitize($to_field);
}

$subject = $_POST['subject'];
$subject = sql_sanitize($subject);
$subject = html_sanitize($subject);
$message = $_POST['message'];
$message = htmlentities($message);
$message = sql_sanitize($message);
$message = html_sanitize($message);
$message = html_entity_decode($message);

if(check_valid_user()) {
   foreach ($_POST['to_field'] as $to_field) {
      $to_field = sql_sanitize($to_field);
      $to_field = html_sanitize($to_field);
      $conn = db_connect();
      $result = $conn->query("SELECT * FROM users WHERE id='".$to_field."' LIMIT 1");
      $row=$result->fetch_object();
      $username = $row->username;
      $result = $conn->query("INSERT INTO inbox VALUES ('', NOW(), '".$username."', '".$message."', '".$subject."', '".$_SESSION['valid_user']."')");
      mysqli_close($conn);  
   }
   header( 'Location: inbox.php?update=1' ) ;
   die();
}
?>