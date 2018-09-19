<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: rpt_confirm_table.php
   date: sept-2016
 author: hugh shedd
   desc: 
marbles: 
   note:
   
 referencetemplate: rptcon
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';

validateUser('admin');
$order_by = 'confirm_email, confirm_date';
@$sort_by = (isset($_GET['so'])) ? $_GET['so'] : '';
$sort_message = '';
switch ($sort_by) {
case 'e' :
   $order_by = 'confirm_email, confirm_date, league_name';
   $sort_message = 'The current sort is by email.';
   break;
case 's' :
   $order_by = 'confirm_date, confirm_email, league_name';
   $sort_message = 'The current sort is by confirm date (date the mail was sent).';
   break;
case 'l' :
   $order_by = 'league_name, confirm_email, confirm_date';
   $sort_message = 'The current sort is by league name.';
   break;
case 'u' :
   $order_by = ' used desc, league_name, actiondate';
   $sort_message = 'The current sort is by used.';
   break;
case 'a' :
   $order_by = ' actiondate desc, league_name';
   $sort_message = 'The current sort is by action date (when was invite accepted).';
   break;
default:
   $sort_by = 'e';
   $order_by = 'confirm_email, confirm_date';
   $sort_message = 'The current sort is by by email.';
   break;
}
   


$msg = '';
$data = '';
$ans = '';
$mysql = "
   select t.id,
          t.confirm_date,
          t.confirm_code,
          t.confirm_email,
          t.used,
          t.league_id,
          t.retire,
          t.mailingerror,
          t.actiondate,
          l.league_name as league_name
     from temp_confirm as t
     left join league as l on t.league_id = l.league_id
 order by " . $order_by;
 
if (!$ans = runSql($mysql, '', 0, $ref_status_text)) {
   if ($ans === false) {
      formatSessionMessage("Unable to create report.", 'danger', $msg, "rptcon-40 $ref_status_text");
      setSessionMessage($msg, 'error');
   }
   if ($ans === null) {
      formatSessionMessage("No records were found.", 'info', $msg, "rptcon-44");
      setSessionMessage($msg, 'error');
   }
   header('location: adminreports.php');
   die();
}

$nbsp10 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$nbsp5 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
//            email, date, league, used usedate error
$text =  sprintf("%-40s %-25s %-40s %-5s %-25s %-5s\n", 
   "<a href='rpt_confirm_table.php?so=e'>email</a>$nbsp10$nbsp10$nbsp10$nbsp5", 
   "<a href='rpt_confirm_table.php?so=s'>sent</a>$nbsp10$nbsp10",
   "<a href='rpt_confirm_table.php?so=l'>league</a>$nbsp10$nbsp10$nbsp10$nbsp5",
   "<a href='rpt_confirm_table.php?so=u'>used</a>$nbsp5",
   "<a href='rpt_confirm_table.php?so=a'>used date</a>$nbsp10", 'error');
$background_color = '';
$font_wt = '';
$text_color = '';
foreach ($ans as $row) {
   $text_color = ($row['used'] != 0) ? 'red' : 'black';
   $font_wt = ($row['used'] != 0) ? 'bold' : 'normal';
   $background_color = ($background_color ==  '#F0F8FF') ? '#ADD8E6' : '#F0F8FF';
   $text .= sprintf("<span style='color:$text_color;background-color:$background_color'>%40s %25s %40s %5s %25s %5s</span>\n",
      $row['confirm_email'],  $row['confirm_date'],   $row['league_name'],
      $row['used'],           $row['actiondate'],     $row['mailingerror']);
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
   Report may be sorted by email, sent, league, used or used date.<br/>
   $sort_message<br/><br/>
   <pre>
   $text
   </pre>";
do_footer('clean');
?>

