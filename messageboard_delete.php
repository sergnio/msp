<?php
$default_timeout =  60 * 60 * 24 * 30;  // 30 days
session_set_cookie_params($default_timeout);
session_start();

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require_once('mypicks_def.php');
require_once('site_fns_diminished.php');

if (!check_valid_user()) {exit;}

$id = $_GET['id'];
$id=sql_sanitize($id);
$id=html_sanitize($id);
$conn = db_connect();
$result = $conn->query("DELETE FROM messages WHERE id='".$id."'");
mysqli_close($conn);
header( 'Location: messageboard.php?update=4' ) ;
die();
?>