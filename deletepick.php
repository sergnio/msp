<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// require_once('site_fns.php');
// session_start();
// date_default_timezone_set('America/Chicago');

if (!check_valid_user()) {exit;}

$id = $_GET['id'];
$id=sql_sanitize($id);
$id=html_sanitize($id);

	$conn_pick = db_connect();
	$result_pick = $conn_pick->query("SELECT * FROM picks JOIN schedules USING (schedule_id) WHERE pick_id='".$id."'");
	$row=$result_pick->fetch_object();
	$gametime2 = date("YmdHi", strtotime($row->gametime));
	$now = date("YmdHi");
	mysqli_close($conn_pick);
	if ($now > $gametime2) {
	header( 'Location: picks.php?update=error3' ) ;
	die();
	}
	else {
	$conn = db_connect();
	$result = $conn->query("DELETE FROM picks WHERE pick_id=$id");
	mysqli_close($conn);
	header( 'Location: picks.php?update=2' ) ;
	die();
	}
?>