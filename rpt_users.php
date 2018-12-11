<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: rpt_users.php
   date: sept-2016
 author: originall
   desc: 
marbles: 
   note:
   
 referencetemplate: rptul
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';

validateUser('admin');
$msg = '';
$data = '';
$ans = '';
$ref_status_text = '';
$league_info = '';
$user_info = '';

@ $selected_user_id = (isset($_POST['useridselector'])) ? $_POST['useridselector'] : '';
$mysql_user = "
   SELECT u.id             ,
          u.username       ,
          u.fname          ,
          u.lname          ,
          u.email          , 
          u.usermode       ,
          u.active_status
     from users as u
    order by u.lname";

$mysql_single_user = "
   SELECT u.id             ,
          u.username       ,
          u.fname          ,
          u.lname          ,
          u.email          , 
          u.usermode       ,
          if (u.active_status = 1, 'yes', 'no') as active_status,
          temppassword
     from users as u
    where u.id = ?
    order by u.lname";
    
$mysql_league = "
   SELECT l.league_name    , 
          l.league_type    ,
          p.playername     ,
          if (p.active = 2,   'yes', 'no')      as playeractive,  
          if (p.paid = 2,     'yes', 'no')      as paid,
          p.joindate       as playerjoindate,
          (select k.pick_time from picks as k where k.user = u.username and k.league_id = l.league_id order by k.pick_time desc limit 1) as lastpick,
          (select count(*) from nspx_leagueplayer as p2 where l.league_id = p2.leagueid and p2.active = 2) as activeplayers
     FROM nspx_leagueplayer as p
LEFT JOIN league as l on l.league_id = p.leagueid
left join users as u on u.id = p.userid
    WHERE p.userid = ?
      and l.active = 1
 order by l.league_name";
      
if (!$ans = runSql($mysql_user, '', 0, $ref_status_text)) {
   if ($ans === false) {
      formatSessionMessage("Unable to create report.", 'danger', $msg, "rptul-71 $ref_status_text");
      setSessionMessage($msg, 'error');
   }
   if ($ans === null) {
      formatSessionMessage("No records were found.", 'info', $msg, "rptul-75");
      setSessionMessage($msg, 'error');
   }
   header('location: adminreports.php');
   die();
}

$user_options = '';

foreach ($ans as $row) {
   $op_user_id = $row['id'];
   $op_user_fn= $row['fname'];
   $op_user_ln = $row['lname'];
   $op_email = $row['email'];
   $selected = ($selected_user_id == $op_user_id) ? "selected='selected'" : '';
   $user_options .= "<option value='$op_user_id' $selected >$op_user_fn $op_user_ln, &nbsp;&nbsp;&nbsp;$op_email</option>\n";
}

if ($selected_user_id) {
 
   if (!$ans = runSql($mysql_single_user, array("i", $selected_user_id), 0, $ref_status_text)) {
      if ($ans === false) {
         formatSessionMessage("Unable to create report.", 'danger', $msg, "rptul-97 $selected_user_id $ref_status_text");
         setSessionMessage($msg, 'error');
      }
      if ($ans === null) {
         formatSessionMessage("No user record was found.", 'info', $msg, "rptul-101 $selected_user_id");
         setSessionMessage($msg, 'error');
      }
      header('location: adminreports.php');
      die();
   }
   
   $user_id          = $ans[0]['id'];
   $user_name        = $ans[0]['username'];
   $user_email       = $ans[0]['email'];
   $user_full_name   = $ans[0]['fname'] . " " . $ans[0]['lname'] ;
   $user_role        = $ans[0]['usermode'];
   $user_status      = $ans[0]['active_status'];
   $temp_password    = $ans[0]['temppassword'];
         //               id          uname     full       email      role       active
   $user_info = sprintf("\n%20s %-17s\n%20s %-30s\n%20s %-35s\n%20s %-35s\n%20s %-35s\n%20s %-35s\n%20s %-35s", 
     'user id: ',     $user_id,
     'username: ',    $user_name,
     'full name: ',   $user_full_name,
     'email: ',       $user_email,
     'role: ',        $user_role,
     'active: ',      $user_status,
     'forgot password: ', $temp_password);
   
   while (1) {
   
      if (!$ans = runSql($mysql_league, array("i", $selected_user_id), 0, $ref_status_text)) {
         if ($ans === false) {
            formatSessionMessage("Unable to create report.", 'danger', $msg, "rptul-127 $ref_status_text");
            setSessionMessage($msg, 'error');
            header('location: adminreports.php');
            die();
         }
         if ($ans === null) {
            $league_info = "No league records were found.";
            break;
         }
      }

   
      $league_name      = '';
      $league_type      = '';
      $player_name      = '';
      $league_joined    = '';
      $paid             = '';
      $last_pick        = '';
      $player_active    = '';
      $active_players   = '';
      
      //            league, player, username, first, last, paid, email
      $league_break = '';
      $background_color = '';
      $font_wt = '';
      $text_color = '';
      for ($i = 0; $i < sizeof($ans); $i++) {   
         $league_name      = $ans[$i]['league_name'];
         $player_name      = $ans[$i]['playername'];
         $league_joined    = $ans[$i]['playerjoindate'];
         $paid             = $ans[$i]['paid'];
         $last_pick        = $ans[$i]['lastpick'];
         $player_active    = $ans[$i]['playeractive'];
         $active_players   = $ans[$i]['activeplayers'];
         $league_type      = $ans[$i]['league_type'];
         $league_type = getLeagueTypeName($league_type);

         //                       leaguename player    lastpick    paid       joined    active
         $league_info .= sprintf("
               \n%20s %-17s\n%20s %-30s\n%20s %-35s\n%20s %-35s\n%20s %-35s\n%20s %-35s", 
               'league name: ',     $league_name,
               'active players: ',  $active_players,
               'game: ',            $league_type,
               'player name: ',     $player_name,
               'last pick: ',       $last_pick,
               'paid: ',            $paid);
      }
      break;
   }
      
}  // END if selected user

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
   <form action='rpt_users.php' method='post' enctype='multipart/form-data'>
      <select name='useridselector'>
         $user_options
      </select>
      <button type='submit' >Select User</button>
   </form>
   <br/><br/><br/>
   <pre>
   $user_info
   </pre>
   <pre>
   $league_info
   </pre>
</div>";
   
do_footer('clean');
?>

