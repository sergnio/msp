<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: rpt_leagues.php
   date: sept-2016
 author: hugh shedd
   desc: 
marbles: 
   note:
   
 referencetemplate: rptleg
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';

validateUser('admin');
$msg = '';
$data = '';
$ans = '';
$mysql = "
   SELECT l.league_name, 
          p.playername, 
          u.username, 
          u.fname, 
          u.lname, 
          u.email, 
          p.paid
     FROM league AS l
LEFT JOIN nspx_leagueplayer AS p ON l.league_id = p.leagueid, users AS u
    WHERE u.id = p.userid
      and l.active = 1";
 
if (!$ans = runSql($mysql, '', 0, $ref_status_text)) {
   if ($ans === false) {
      formatSessionMessage("Unable to create report.", 'danger', $msg, "rptleg-40 $ref_status_text");
      setSessionMessage($msg, 'error');
   }
   if ($ans === null) {
      formatSessionMessage("No records were found.", 'info', $msg, "rptleg-44");
      setSessionMessage($msg, 'error');
   }
   header('location: adminreports.php');
   die();
}

//            league, player, username, first, last, paid, email
$text =  sprintf("%-40s %-20s %-17s %-30s %-5s %-35s\n", 'league', 'player', 'username', 'name', 'paid', 'email');
$league_break = '';
$background_color = '';
$font_wt = '';
foreach ($ans as $row) {
   if ($league_break != $row['league_name']) {
      $text .= "\n";
      $league_break = $row['league_name'];
   }
   
   $display_paid = ($row['paid'] == 2) ? 'yes' : 'no';
   $text_color = ($row['paid'] == 2) ? 'navy' : 'black';
   $font_wt = ($row['paid'] == 2) ? 'bold' : 'normal';
   $background_color = ($background_color == '#F0F8FF') ? '#ADD8E6' : '#F0F8FF';
   
   $text .= sprintf("<span style='color:$text_color;background-color:$background_color;font-weight:$font_wt;'>%40s %20s %17s %30s %5s %35s</span>\n",
      $row['league_name'],  $row['playername'],   $row['username'],
      $row['fname'] . " " . $row['lname'],        $display_paid,
      $row['email']);
}

     

do_header('MySuperPicks.com -Confirm Table');
do_nav();

?>
<div class="container">
<?php 
echo_container_breaks();
echoSessionMessage();
echo "
   <div class='hidden-sm hidden-md hidden-lg'>
      <br />
      <br />
      <br />
   </div>
   <pre>
   $text
   </pre>";
do_footer('clean');
?>

