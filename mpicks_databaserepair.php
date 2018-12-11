<?php
/*
:mode=php:

   file: mypicks_databaserepair.php
   date: may-2016
 author: originall
   desc:
  notes:

marbles: may 2016


*/

//require_once 'mypicks_def.php';
//require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

function fixusersandleagues() {
   
   
      $global_write_roach_file = true;
   
   $s = "select league_id from league;"; 
   $conn = db_connect();
   $sth = $conn->prepare($s);
   $sth->execute();
   $data = $sth->get_result();
   $all_leagues = $data->fetch_all(MYSQLI_NUM);
   writeDataToFile("League arrary " . print_r($all_leagues, true));
   $sth->close();
   
   $s = "select id, league_id from users";
   $conn = db_connect();
   $sth = $conn->prepare($s);
   $sth->execute();
   $data = $sth->get_result();
   $usersandleaguestring = $data->fetch_all(MYSQLI_ASSOC);
   $sth->close();
   
   $mysql_update = "
      update users
            set league_id = ?
          where id = ?
          limit 1";
   $conn = db_connect();
   $sth = $conn->prepare($mysql_update);

   foreach ($usersandleaguestring as $uandl){
      $leaguestring = $uandl['league_id'];
      $user_id = $uandl['id'];

      $league = explode('-', $leaguestring);
      $league = str_replace('-', "", $league);
      $league = array_filter( $league, 'strlen' );  // len != 0
      sort($league);
      $new_league_string = '';            
      foreach($league as $le) {
        // writeDataToFile("check $user_id league $le exists");
         for($n = 0; $n < sizeof($all_leagues); $n++) {
            $xleague =  $all_leagues[$n][0];
            //writeDataToFile("For user $user_id, checking xleagure '$xleague' and his '$le'", __FILE__, __LINE__);
            if ($xleague == $le) {
               $new_league_string .= '-' . $xleague . '-';
            }
         }
         if ($new_league_string == '') {
            $new_league_string = '--';
         }
         
         $sth->bind_param("si", $new_league_string, $user_id);
         $sth->execute();
        
         writeDataToFile("For user $user_id, this is his new league string $new_league_string", __FILE__, __LINE__);
      }
   }
   
}

?>