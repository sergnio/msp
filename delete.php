<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

validateUser('admin');

$id = $_GET['id'];
$id=sql_sanitize($id);
$id=html_sanitize($id);
$conn = db_connect();
$result = $conn->query("DELETE FROM users WHERE id='".$id."'");
mysqli_close($conn);
header( 'Location: users.php?update=2' ) ;
die();


?>