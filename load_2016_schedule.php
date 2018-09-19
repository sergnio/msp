<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: load_2016_schedule.php
   date: jun-2016
 author: original
   desc: This is the home page.  Defined in mypick_def.php as
      URL_HOME_PAGE

   note:
   
*/

$lock_out = false;
$lockout_date = '2016-09-15 12:00';
$ans = '';
$ref_status_text = '';
$msg = '';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
require_once 'mypicks_once.php';

validateUser('admin');

$do_what = (!empty($_GET['dowhat'])) ? $_GET['dowhat'] : '';

if (strtotime('now') > strtotime($lockout_date)) {
   formatSessionMessage("Scheduling operation were disabled $lockout_date.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header("Location: adminreports.php");
   die();
}

if (!$do_what || !($do_what == '2016' || $do_what == 'zero')) {
   formatSessionMessage("Scheduling operation cannot be run.  I don't understand the action request '$do_what'.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header("Location: adminreports.php");
   die();
}

if ($lock_out) {  
   formatSessionMessage("Schedule loading has been disabled by dev.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header("Location: adminreports.php");
   die();
}

$mysql = 
   "update schedules
       set week = ?,
           gametime = ?,
           home = ?,
           away = ?
     where schedule_id = ?";
     
$mysql_scores = "
    update schedules
       set awayscore = NULL,
           homescore = NULL";
     
     
if ($lock_out) {  
   formatSessionMessage("Schedule loading has been disabled by dev.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header("Location: adminreports.php");
   die();
}
      

define('WEEK', 0);
define('SCH_DATE', 1);
define('SCH_TIME', 2);
define('AWAY_INI', 5);
define('NOME_INI', 6); 
writeDataToFile("dev schedule First game at $first_game", __FILE__, __LINE__);
   
$site_name = ADMIN_TABLE;
$first_game = '20:00:00';
if ($site_name == 'nflbrain') {
   $first_game = '15:30:00';
}

if ($do_what == '2016') {
   $conn = db_connect();
   $sth = $conn->prepare($mysql);
   
   $row = 1;
   if (($handle = fopen("nfl_schedule.csv", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 225, ",")) !== FALSE) {
         $num = count($data);
         if ($num == 7) {  // week, date, time, away team, home team, away initials, home initials
            $week = $data[WEEK];
            $sch_date = $data[SCH_DATE];
            $sch_time = $data[SCH_TIME];
            $away_ini = $data[AWAY_INI];
            $home_ini = $data[NOME_INI];
            $date_time = $sch_date . " " . $sch_time;
            //echo "$week, $sch_date, $sch_time, $away_ini, $home_ini <br />";
            $date = DateTime::createFromFormat('Y-m-d H:i A', $date_time);
            $date->sub(new DateInterval("PT3H"));  // adjust to pacific time
            $mysql_datetime = $date->format('Y-m-d H:i:s');
            //echo $mysql_datetime . "<br />";
            
            $sth->bind_param("isssi", $week, $mysql_datetime, $home_ini, $away_ini, $row);
            if (!$sth->execute()) {
               break;
            }
            $row++;
         }
      }
      fclose($handle);
   }
} elseif ($do_what == 'dev') { 

writeDataToFile("First game at $first_game", __FILE__, __LINE__);
$weeky = 1;
$diditrun = 'yes';
  setScheduleTimesXXXXX($weeky++, '2016:08:11', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:12', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:13', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:14', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:15', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:16', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:17', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:18', $first_game);       //   
  setScheduleTimesXXXXX($weeky++, '2016:08:19', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:20', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:21', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:22', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:23', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:24', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:25', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:26', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:27', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:28', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:29', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:30', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:08:31', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:01', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:02', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:03', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:04', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:05', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:06', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:07', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:08', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:09', $first_game);       //  
  setScheduleTimesXXXXX($weeky++, '2016:09:10', $first_game);       //  

} elseif ($do_what == 'zero') { 
   $ans = runSql($mysql_scores, '', 1, $ref_status_text);
   if ($ans === false) {
      formatSessionMessage("There was a database error.  The scores were not nulled.", 'info', $msg, "ls-159 $ref_status_text");
      setSessionMessage($msg, 'error');
      header("Location: adminreports.php");
      die();
   }
}
      

formatSessionMessage("The '$do_what' schedule operation was successful.", 'success', $msg);
setSessionMessage($msg, 'happy');
header ("location: adminreports.php");
die();
?>