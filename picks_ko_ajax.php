<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: picks_ko_ajax.php
   date: apr-2016
 author: original
   desc: File is linked to main page "This Week's Lines"
marbles: 
   note:
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


validateUser('user');
validateUser('league');

$league_type = (!empty($_SESSION['league_type'])) ? $_SESSION['league_type'] : '';
if ($league_type != LEAGUE_TYPE_COHORT  && $league_type != LEAGUE_TYPE_LAST_MAN ) {
   header('location: ' . PAGE_PICK_SWITCH);
   die;
}

do_header('MySuperPicks.com - Picks KO');
do_nav();

$user_id =        $_SESSION['user_id'];
$active_week =    $_SESSION['active_week'];
$league_id =      $_SESSION['league_id'];
$league_type =    $_SESSION['league_type'];
$league_picks =   $_SESSION['league_picks'];
$league_name =   $_SESSION['league_name'];
$league_type_category_display_name = ($league_type == LEAGUE_TYPE_COHORT) ? 'cohort' : 'last man';
$odds_in_use =    ($_SESSION['league_points'] == LEAGUE_ODDS_IN_USE) ? true : false;

writeDataToFile("picks_ko_ajax.php() userid activeweek leaguetype leagueid: '$user_id', '$active_week','$league_id','$league_type'", __FILE__, __LINE__)

?> 
   <div class="container">
   <?php echo_container_breaks();
   
echo "      <div class='hidden-sm hidden-md hidden-lg'>
         <br />
         <br />
         <br />
      </div>
      <div id='IDd_ajaxmessageshere'></div>
      <h2 class='text-center'>League <i>$league_name</i>  <small>  (KO - $league_type_category_display_name)</small>&nbsp;&nbsp; Week $active_week
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
              <button  id='IDb_mailverify' type='button' class='btn btn-info' name='mailverify' style='width:100px;'>Submit</button></h2>\n
              <input type='hidden' id='IDi_gohere' value='verifypicks.php' />\n";
              
echo "<p class=\"lead muted text-center\"><small>To enter picks press the desired button.  To deselect toggle the selection.<br />
      The spread (if used)</small><a href=\"#\" rel=\"tooltip\" data-placement=\"right\" title='How do point spreads work?
      The team with the minus sign (e.g. -3) is the favorite and is \"giving\" 3 points. 
      They must win the game by more than 3 points to cover, or for you to \"win\" the point if you picked them. 
      The team with the plus sign (+3) is the underdog and is \"getting\" 3 points. 
      They can  cover, or win, by winning the game outright, or by losing the game by less than 3 points. 
      If the favorited team in this scenario ends up winning by exactly 3 points, this is a \"push\" 
      and anyone who picked either team will receive 0.5 pts. in the standings. ' >
      <span class=\"glyphicon glyphicon-question-sign\"></span>
      </a><small> is listed to the right of the team name.</small>
</p>
<script type=\"text/javascript\">
    $(function () {
        $(\"[rel='tooltip']\").tooltip();
    });
</script>";
      echo "<h3>This Week's Lines <span style='font-size: 14px;'> Please pick $league_picks team to win</span></h3>";
      //get_ko_cohort_public_picks($week,$user_id, $league_id);
      echoKOCohortPublicPicks($user_id, $active_week, $league_id, $league_type, $odds_in_use);
      echo "<br /><br /><br /><br />";
?>
   </div>
<?php
do_footer('bottom');


//=====================================================================================================================

// This is jQuery driven now. See mypicks03.js.  All the hooks
// are in the table elements.  The click event selector is 
// 'table tbody tr td button[name=pickerbutton]' 
function echoKOCohortPublicPicks(
   $user_id = 222,
   $current_week = 2, 
   $league_id = 210,
   $league_type = 2,
   $odds_in_use,
   // Use switch statement, add checked based off value passed
   $tz = 'c'
) {

   $p_checked = ($tz == 'p') ? "checked='checked'" : '';
   $m_checked = ($tz == 'm') ? "checked='checked'" : '';
   $c_checked = ($tz == 'c') ? "checked='checked'" : '';
   $e_checked = ($tz == 'e') ? "checked='checked'" : '';

   $user_name = getUsernameViaId($user_id);
   // time format:  Friday, May 13, 2016 1:00 pm
   // http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
   
   // This will give us all week up to the present week.  We need the previous weeks to find
   // the spent teams.
   $mysql = "
      select s.schedule_id,
             concat_ws(' ', date_format(s.gametime, '%a, %b %d %l:%i'), lower(date_format(s.gametime, '%p'))) displaydate,
             s.gametime,
             if (s.gametime < now(), 'gamestarted', 'gamescheduled') gameclass,
             s.home,
             s.away,
             s.spread,
             s.homescore,
             s.awayscore,
             p.home_away,
             p.pick_id,
             s.week
        from schedules s left join picks p on
                           s.schedule_id = p.schedule_id 
                           and p.user = ?
                           and p.league_id = ?
       where s.week <= ?
       order by s.week,s.gametime, s.away";  // TODO sort options 

       
   $stable = "\n";
   //$stable .= "<table id=\"single\" class=\"table table-hover table-striped\">\n";
   $stable .= "<table id=\"single\" class=\"table table-hover table-responsive pickskotable\">\n";
    $stable .= "   <thead>\n";
    $stable .= "      <tr>\n";
    $stable .= "         <div id='IDh_gametime' class='col-12 text-left'>Game Time</div>
                                <div> <input type='radio' id='IDi_tzp' name='tz' value='p' adj='0' $p_checked > Pacific    
                                <input type='radio' id='IDi_tzm' name='tz' value='m' adj='1' $m_checked > Mountain  
                                <input type='radio' id='IDi_tzc' name='tz' value='c' adj='2' $c_checked > Central  
                                <input type='radio' id='IDi_tze' name='tz' value='e' adj='3' $e_checked > Eastern</div>\n";

    $stable .= "      </tr>\n";
    $stable .= "      <tr>\n";
    $stable .= "         <th class='text-left'>Date</th>\n";
    $stable .= "         <th class='text-center'>Away Team</th>\n";
    $stable .= "         <th class='text-center'>Home Team</th>\n";
    $stable .= "      </tr>\n";
    $stable .= "   </thead>\n";
   $stable .= "   <tbody id='IDtb_fullschedule' leagueid='$league_id' leaguetype='$league_type' week='$current_week' userid='$user_id' picklimit='1'>\n";
   
   // No index used in query/prepared statement select  TODO fix
   $driver = new mysqli_driver();
   $driver->report_mode = MYSQLI_REPORT_STRICT;
   
   $conn = db_connect();
   $sth = $conn->prepare($mysql); 
   $sth->bind_param("sii", $user_name, $league_id, $current_week);
   $sth->execute();
   $sth->bind_result($id, $display_gametime, $gametime, $gameclass, $home, $away, $spread, $homescore, $awayscore, $user_choice, $pick_id, $game_week);
   $a_spent = array();
   while ($sth->fetch()) {

       $display_gametime = changeDefaultTime($tz, $gametime);

       if ($game_week < $current_week) {   // then we are building the spent array
         if ($user_choice) {
            if ($user_choice == 'h') {
               $a_spent["$home"] = 1;
            } else {
               $a_spent["$away"] = 1;
           }
         }
         continue;
      }
      
      $spread_h = '';
      $spread_a = '';
      
      if ($odds_in_use) {
         $spread_h = ($spread > 0) ? "+".(float)$spread : (float)$spread;
         if ($spread == 0) { 
            $spread_a = 'PK'; 
            $spread_h = 'PK'; 
         } elseif ($spread < 0) {
            $spread_a = "+".((float)$spread*-1); 
            $spread_h = (float)$spread;
         } elseif ($spread > 0) {
            $spread_a = (float)$spread*-1;
            $spread_h = "+".(float)$spread;
         }
      }
      
      $row_id = 'IDr_' . $id;
      $away_id = 'IDb_away' . $id;
      $home_id = 'IDb_home' . $id;
      $game_class_string= "class='$gameclass'";
      
      $choose_away = (isset($user_choice) && $user_choice == 'a') ? 'btn-primary' : 'btn-info';
      $choose_home = (isset($user_choice) && $user_choice == 'h') ? 'btn-primary' : 'btn-info';
      $home_game_selected = (isset($user_choice) && $user_choice == 'h') ? $pick_id : -1;
      $away_game_selected = (isset($user_choice) && $user_choice == 'a') ? $pick_id : -1;
      $home_is_available = (isset($a_spent[$home])) ? 'no' : 'yes';
      $away_is_available = (isset($a_spent[$away])) ? 'no' : 'yes';
      $choose_away = ($away_is_available == 'no') ? 'btn-default' : $choose_away;
      $choose_home = ($home_is_available  == 'no') ? 'btn-default' : $choose_home;
      
      // http://www.w3schools.com/bootstrap/bootstrap_buttons.asp
      $stable .= "      <tr id='$row_id' $game_class_string gameat='$gametime' scheduleid='$id'>\n";
      $stable .= "         <td name='gametimedisplay' class='pickskogametime col-md-4 text-nowrap pickskocol'>$display_gametime</td>\n";
      $stable .= "         <td class='col-md-4 text-center pickskocol'><button  id='$away_id' type='button' class='btn $choose_away pickskobutton' available='$away_is_available' myrowid='$row_id' name='pickerbutton' whereplay='a' myfriendpickid='$home_game_selected' myfriendbuttonid='$home_id' gamepickid='$away_game_selected' >$away $spread_a</button></td>\n";
      $stable .= "         <td class='col-md-4 text-center pickskocol'><button  id='$home_id' type='button' class='btn $choose_home pickskobutton' available='$home_is_available' myrowid='$row_id' name='pickerbutton' whereplay='h' myfriendpickid='$away_game_selected' myfriendbuttonid='$away_id' gamepickid='$home_game_selected' ><span class='smallerAtSign'>@</span>$home $spread_h</button></td>\n";
      $stable .= "      </tr>\n";
      //  }
   }
   $stable .= "   </tbody>\n";
   $stable .= "</table>\n";
   echo $stable;
   @ $sth->close();
}

/**
 * @param $tz
 * @param $gametime
 * @return string
 * @throws Exception
 */
// TODO : refactor so both picks_ajax and picks_ko_ajax use this method
function changeDefaultTime($tz, $gametime)
{
    $o_base_date = new DateTime($gametime);
    $hours_adjust = 0;
    switch ($tz) {
        case 'p' :
            $hours_adjust = 'PT0H';
            break;
        case 'm' :
            $hours_adjust = 'PT1H';
            break;
        case 'c' :
            $hours_adjust = 'PT2H';
            break;
        case 'e' :
            $hours_adjust = 'PT3H';
            break;
    }
    $o_base_date->add(new DateInterval($hours_adjust));
    $display_gametime = $o_base_date->format('D, M d') . '&nbsp;' . $o_base_date->format('g:i a');
    return $display_gametime;
}


?>