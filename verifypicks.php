<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser();

$dev_turn_off_mail_call = true;
$site_name = ADMIN_TABLE;
if ($site_name = 'mysuperpicks') {
   $dev_turn_off_mail_call = false;
}

$user_id =     (!empty($_SESSION['user_id']))      ? $_SESSION['user_id'] : '';
$league_id =   (!empty($_SESSION['league_id']))    ? $_SESSION['league_id'] : '';
$league_name = (!empty($_SESSION['league_name']))  ? $_SESSION['league_name'] : '';
$active_week = (!empty($_SESSION['active_week']))  ? $_SESSION['active_week'] : '';
$name =        (!empty($_SESSION['valid_user']))  ? $_SESSION['valid_user'] : '';

writeDataToFile("verifypicks.php begins: '$user_id', '$league_id', '$league_name', '$active_week'", __FILE__, __LINE__);

$mysql = "
   select p.user,
          if (p.home_away = 'h', s.home, s.away) as teampick,
          s.gametime,
          p.pick_time as picktime,
          now() as timenow
   FROM picks as p,
   schedules as s
   where p.schedule_id = s.schedule_id
     and p.user = (select username from users where id = ? limit 1)
     and p.league_id = ?
     and s.week = ?";

$error = true;
$contact_to       = '';
$contact_from     = '';
$msg_head         = '';
$ref_status_text  = '';
$msg_body         = '';
$msg_head         = '';
$msg_body_html    = '';
$msg_head_html    = '';
$time_now         = '';
$signature        = '';
$there_are_picks  = true;
$status           = false;
$mail_status      = '';
$record_mailing   = false;
$status           = false;
while (1) {
   
   if ($user_id == '' || $league_id == '' || $league_name == '' || $active_week == '') {
      // session messages were set above
      formatSessionMessage("We are unable to mail your verification at this time.", 'info', $msg, "vp-53:'$user_id' '$league_id' '$league_name' '$active_week'");
      setSessionMessage($msg, 'error');
      break;
   }
   
   if (!$user_email_address = getUserEmailAddress($user_id)) {
      // session messages were set above
      formatSessionMessage("We are unable to mail your verification at this time. '$contact_to'", 'info', $msg, "vp-60'$user_email_address'");
      setSessionMessage($msg, 'error');
      break;
   }
   
   $no_reply_address = getNoReplyEmailAddress(); 
   $time_now = getDatabaseTime();
   
   $ans = runSql($mysql, array("iii", $user_id, $league_id, $active_week), 0, $ref_status_text);
   
   if (!$ans) {
      if ($ans === 0 || $ans === null) {
         $there_are_picks = false;
              $msg_body = "          There are no picks in league $league_name, week $active_week.";
         $msg_body_html = "          There are no picks in league $league_name, week $active_week.";
      } else {
         formatSessionMessage("An unknown database error occurred.  The email was not sent.  Please contact the site administrator.", 'danger', $msg,
            "vp-77 '$user_id', '$league_id', '$active_week', text: $ref_status_text");
         setSessionMessage($msg, 'error');
         break;
      }
   }
   
   //   build this
   // 
   //   To:  mattleisen@yahoo.com
   //  Date:  (west coast)        
   //League:  Original            
   //  Week:  3                   
   // Picks:
   // 
   //        team           game time (west coast)              picked at
   //         PIT              2016-07-31 18:00:00    2016-07-31 09:15:14
   //          SD              2016-07-31 18:15:00    2016-07-31 09:15:14
   //         CHI              2016-07-31 18:30:00    2016-07-31 09:15:13
   //         ALT              2016-07-31 18:45:00    2016-07-31 09:15:12
   //         NYJ              2016-07-31 17:45:00    2016-07-31 09:15:15
   // 
   // 
   writeDataToFile("ans returned " . print_r($ans, true), __FILE__, __LINE__);
   
   
   $rint = rand();
   $signature_hash = hash('sha256', $rint);
   
   $msg_head =  sprintf("%10s:  %-20s\n",    'To',       $user_email_address);
   $msg_head .=  sprintf("%10s:  %-20s\n",   'Date',    $time_now . ' (west coast)');
   $msg_head .= sprintf("%10s:  %-20s\n\n",  'Signature',     $signature_hash);
   $msg_head .= sprintf("%10s:  %-20s\n",    'League',   $league_name);
   $msg_head .= sprintf("%10s:  %-20s\n",    'Week',     $active_week);
   $msg_head .= sprintf("%10s:\n\n",         'Picks',    $active_week);
   
   $msg_head_html =  sprintf("%10s:  %-20s<br />",       'To',       $user_email_address);
   $msg_head_html .=  sprintf("%10s:  %-20s<br />",      'Date',     $time_now . ' (west coast)');
   $msg_head_html .= sprintf("%10s:  %-20s<br /><br />", 'Signature',     $signature_hash);
   $msg_head_html .= sprintf("%10s:  %-20s<br />",       'League',   $league_name);
   $msg_head_html .= sprintf("%10s:  %-20s<br />",       'Week',     $active_week);
   $msg_head_html .= sprintf("%10s:<br /><br />",        'Picks',    $active_week);
   
   if ($there_are_picks) {
      $msg_head .= sprintf("          %6s   %20s   %20s\n",  'team', 'game time (west coast)', 'picked at');
      $msg_head_html .= sprintf("          %6s   %30s   %20s<br />",  'team', 'game time (west coast)', 'picked at');
      for ($ndx = 0; $ndx < sizeof($ans); $ndx++) {
         // League week teamname time
         $time_now =  $ans[$ndx]['timenow'];
         $team_name = $ans[$ndx]['teampick'];
         $game_time = $ans[$ndx]['gametime'];
         $pick_time = $ans[$ndx]['picktime'];
         $msg_body .= sprintf("          %6s   %30s   %20s\n",  $team_name, $game_time, $pick_time);
         $msg_body_html .= sprintf("          %6s   %30s   %20s<br />",  $team_name, $game_time, $pick_time);
      }
   }
   
   
   $msg_head .= "$msg_body\n";
   $msg_head_html.= "$msg_body_html\n";

   
   writeDataToFile("here is the message: \n$msg_head", __FILE__, __LINE__);
   
   // http://php.net/manual/en/function.mail.php
   $to_address = $user_email_address;
   $subject = "Your MySuperPicks verification: week $active_week, league $league_name";
   $mail_content = "$msg_head \n";
   $from_address = "From: " . getNoReplyEmailAddress();
   
   writeDataToFile("
      subject: '$subject\n
      toaddress: $to_address\n
      from: $from_address\n
      content: $mail_content\n", __FILE__, __LINE__);
   
   $record_mailing = true;
   if (!@mail($to_address, $subject, $mail_content, $from_address)) {
      $mail_error = print_r(error_get_last(), true);
      $mail_status = 'fail';
      formatSessionMessage("An unknown mailing error occurred.  The email was not sent.  Please contact the site administrator.", 'danger', $msg, "vp-147 $mail_error");
      setSessionMessage($msg, 'error');
      break;
   } else {
      $mail_status = 'success';
//      formatSessionMessage("Your picks verification mail was sent.", 'success', $msg, "vp-149 $to_address");
      formatSessionMessage("Your picks verification mail was sent.", 'success', $msg);
      setSessionMessage($msg, 'error');
   }
   
   $status = true;
   break;
}

$concerning = 'picks verification';
$mail_content = 'see sent_email.body';

$mysql = "
   insert into sent_email
      ( to_address, league_id, senddate, signature, userid, concerning, body, mail_content, status)
   values
      (          ?,         ?,        ?,         ?,      ?,          ?,    ?,            ?,      ?)";
if ($record_mailing) {
   if (!$ans = runSql($mysql, array("sississss", 
                    $to_address, $league_id, $time_now, $signature_hash, $user_id, $concerning, $msg_head, $mail_content, $mail_status), 1, $ref_status_text)) 
   {
      formatSessionMessage("Please contact the site administrator.", 'danger', $msg, "vp-185 $ref_status_text");
      setSessionMessage($msg, 'error');
   }
}   

do_header('MySuperPicks.com - Verify Picks');
do_nav();
echo "<div class='container'>";
echoContainerBreaks();
echoSessionMessage();
echo "
<div class='hidden-sm hidden-md hidden-lg'>
    <br />
    <br />
    <br />
</div>
      <h2 class='text-center'>Picks Verify Mail</h2>
      <br />
      <br />

         <div id='IDd_about'>
            <p>Success or failure of the mail attempt is shown above.</p>
            <p>
            <pre>$msg_head_html</pre>
            </p>
         </div>";

do_footer('bottom');
?>