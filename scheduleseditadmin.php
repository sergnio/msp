<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

$array = $_POST;

if (validateUser('admin', 'status')) {
   if (!is_array($array)) { 
      header( 'Location: schedules_nsp.php?update=0' ) ;
      die();
   } else {
   	   //translate the local time to the server time
   	   if(is_array($array['gametime'])) {
   	   	$j = count($array['gametime']);
         for($i = 0; $i<$j; $i++) {
         	$current = $array['gametime'][$i];
			$servertime = convert_to_server_date($current, "Y-m-d H:i:s");
			$array['gametime'][$i] = $servertime;
		 }
   	   }
	   
   	   if (updateScheduleWeek($array)) { 
	      $_SESSION['updateweek'] = $array['week'][0];
	      header( 'Location: schedules_nsp.php?update=1&week='.$array['week'][0] );
	      die();
	   } else {
	      header( 'Location: schedules_nsp.php?update=0' ) ;
	      die();
	   }
   }
}
?>