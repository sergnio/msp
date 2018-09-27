/* :mode=javascript:

   file: mypicks03.js
   date: may-2016
 author: hfs
   desc: A javascript collection supporting picks_ajax.php.  This is the page
   "This Week's Lines" accessed via the line menu ->This Week's Lines.  The user
   must be logged in.
   
*/

// globalize for now 
var where_play = '';
var button_id = '';
var game_pick_id = '';
var my_friend_pick_id = '';
var my_friend_button_id = '';
var selector_rowid = '';
var games_starts_at = '';
var schedule_id = '';

var pick_limit = '';
var league_id = '';
var user_id = '';
var week = '';
var league_type = '';

var alerts03_on = false;
var alerts03_trivial_on = false;

$(document).ready(function() {
   $('input[name=tz]').click(function(e) {
      var adjustment = $(this).attr('adj');
      $dateColText = $('td[name=gametimedisplay]');
      $dateColText.each(function() {
         var p = $(this).parent();
         var pacific_gametime = p.attr('gameat');
         om = new moment(pacific_gametime);
         om.add(adjustment, 'hours');
         var display_time = om.format('ddd, MMM Do, h:mm a');
         $(this).html(display_time);
         //alert("gameat " + pacific_gametime + ", now at " + display_time);
      });
   });
});

$(document).ready(function() {   // Both KO and Pickem leagues use
   $('#IDb_mailverify').click(function(e) {
      var page = $('#IDi_gohere').val();
      //alert("page is " + page);
      var werhere = window.location.href;// = 'verifypicks.php';
      //alert("verify mail " + werhere + ", and goto " + page);
      window.location.href = page;
   });
});


$(document).ready(function() {
   $('table tbody tr td button[name=pickerbutton]').click(function(e) {
         
      e.preventDefault();  // There is none at this time.
      $("#IDd_ajaxmessageshere").children().remove();
      
      var iam = 'mypicks03.js';
      
      $('#single tbody tr td button').prop("disabled",true);
      where_play =     $(this).attr('whereplay');
      button_id =      $(this).attr('id');
      game_pick_id =   $(this).attr('gamepickid');
      my_friend_pick_id =      $(this).attr('myfriendpickid');
      my_friend_button_id = $(this).attr('myfriendbuttonid');
      
      selector_rowid = '#' + $(this).attr('myrowid'); // mysql datetime datatype
      games_starts_at = $(selector_rowid).attr('gameat');
      schedule_id =     $(selector_rowid).attr('scheduleid');
      
      pick_limit = $('#IDtb_fullschedule').attr('picklimit');
      if (pick_limit == 11) { pick_limit = 100; }
      
      league_id =  $('#IDtb_fullschedule').attr('leagueid');
      user_id =    $('#IDtb_fullschedule').attr('userid');
      week =       $('#IDtb_fullschedule').attr('week');
      league_type =       $('#IDtb_fullschedule').attr('leaguetype');
      if (league_type == 2 || league_type == 3) {
         var is_available = $(this).attr('available');
         
         if (is_available == 'no') {         
            var formated_ermsg = "<div class='alert alert-info'><b>Info!</b>&nbsp;&nbsp; This team has already be used in a previous round.  It is 'spent' and may not be used again.</div>";
            $("<div> " + formated_ermsg + " </div>").appendTo('#IDd_ajaxmessageshere');
            //alert("This team has already be used in a previous round.  It is 'spent' and may not be used again."); 
            $('#single tbody tr td button').prop("disabled",false);
            return true;
         }
         
      }
       
      var action = 'removing';   // or 'adding'
      var present_number_picks = '';
      if (game_pick_id == -1) {  // Then he's newly picking this team.  Check pick limits.
         action = 'adding';
         present_number_picks = $('#IDtb_fullschedule tr td button[game_selected=yes]').length;
         if (present_number_picks  >= pick_limit) {         
            var formated_ermsg = "<div class='alert alert-info'><b>Info!</b>&nbsp;&nbsp; Only " + pick_limit + " picks are allowed.  You must first delete a pick to choose this team.</div>";
            $("<div> " + formated_ermsg + " </div>").appendTo('#IDd_ajaxmessageshere');
            //alert("Only " + pick_limit + " picks are allowed.  You must first delete a pick to choose this team.");
            $('#single tbody tr td button').prop("disabled",false);
            return true;
         }
      }
      if (alerts03_on == true) {
         alert(" dothis: "                + action                +
              "\n whereplay: "            + where_play            +
              "\n userid: "               + user_id               +
              "\n schedule_id: "          + schedule_id           +
              "\n week: "                 + week                  +
              "\n pick_limit: "           + pick_limit            +
              "\n my_friend_pick_id: "    + my_friend_pick_id     +
              "\n game_pick_id: "         + game_pick_id          +
              "\n my_friend_button_id: "  + my_friend_button_id   +
              "\n league_id: "            + league_id             +
              "\n present_number_picks: " + present_number_picks  +
              "\n iam: " + iam);
      }
 
      $.ajax ({ 
         type: 'POST',
         url: 'ajax_support_picks.php',
         dataType: 'json',
         timeout: 2000,
         async: true,
         data:  {  dothis: action,      authority: 'member',           userid: user_id, 
               scheduleid: schedule_id,  homeaway: where_play,     gamepickid: game_pick_id,
                     week: week,         leagueid: league_id,  myfriendpickid: my_friend_pick_id,
                picklimit: pick_limit,        iam: iam },
         error: function() {
            alert("An error occurred.  Please try again later." );
            $('#single tbody tr td button').prop("disabled",false);
         },
         success: function(data) {
            (alerts03_trivial_on && alert("good to go." ));
            postPickProcessing(data);
            $('#single tbody tr td button').prop("disabled",false);
         },
         complete: function() { 
           (alerts03_trivial_on && alert("complete"));
           $('#single tbody tr td button').prop("disabled",false);
         }
      });
      
      //$('#single tbody tr td button').show();

   });
});
     
// json return
//   'status' => $status, 
//   'ermsg' => $database_action_ermsg,
//   'databaseaction' => $database_action, 
//   'returnparameter' => $return_parameter,
//   'refstatuscheck' => $ref_status_text

function postPickProcessing(data) { 
   $(document).ready(function() {
      //alert ("data is " + data.status);
      
      if (data.status != 1) {
         // Is it possible the database was modified?  Probably not, but maybe a page refresh might be wise.
         if (data.ermsg) {     
            var formated_ermsg = "<div class='alert alert-info'><b>Info!</b>&nbsp;&nbsp;" + data.ermsg + "</div>";
            $("<div> " + formated_ermsg + " </div>").appendTo('#IDd_ajaxmessageshere');
            //alert("sdfsdfsfds" + data.ermsg);
            $('html').scrollTop(0);
            return false;
         }
         // messsages for consumption?
         return false;
      }
      
      // database actions are: swap, insert and remove
      if (data.databaseaction == '') {
         alert("There was no database action.");
         return false;
      }
      
      if (data.returnparameter < 1) {
         alert("The attempted " + data.databaseaction + " failed to execute properly.");
         return false;
      }

      button_selector = '#' + button_id;
      friend_button_selector = '#' + my_friend_button_id;
      
      if (data.databaseaction == 'insert') {
         // Highlight the new pick.  Since this was not a swap there is no other highlighting to do.
         // btn-primary and btn-info bootstrap classes are used for coloring.
         // -primary is selected, -info is NOT selected
         
         $(button_selector).removeClass("btn-info");
         $(button_selector).addClass("btn-primary");
         
         // House keeping
         $(button_selector).attr('gamepickid', data.returnparameter);  // this is the last index of the new record - the new picks.pick_id
         $(friend_button_selector).attr('myfriendpickid', data.returnparameter);
         
         
         return true;
      }
      if (data.databaseaction == 'remove') {
         // Back to non-select.  This pick was just removed.
         // Since this was not a swap there is no other highlighting to do.
         // btn-primary and btn-info bootstrap classes are used for coloring.
         // -primary is selected, -info is NOT selected
         
         $(button_selector).removeClass("btn-primary");
         $(button_selector).addClass("btn-info");
         
         // House keeping
         $(button_selector).attr('gamepickid', '-1');  // there is NO pick id now
         $(friend_button_selector).attr('myfriendpickid', '-1');
         return true;
      }
      if (data.databaseaction == 'swap') {
         // This is a swap.  One of teams was already picked.  He's now
         // changed his mind and is going with the other team.  Probably a female.
         // -primary is selected, -info is NOT selected
         
         // Button pressed.  Show selected.
         $(button_selector).removeClass("btn-info");
         $(button_selector).addClass("btn-primary");
         
         // This team was is no longer selected; back to non-select.
         $(friend_button_selector).removeClass("btn-primary");
         $(friend_button_selector).addClass("btn-info");
         
         // House keeping.  Remove, and save, the pick id from the previous selected team in this matchup
         // and give it to the other, newly selected, team. The picks.pick_id value was not changed.
         // The home_away field was updated (along with picks.pick_time).
         pick_id_of_previously_selected_team = $(friend_button_selector).attr('gamepickid');
         
         // I am no longer selected.
         $(friend_button_selector).attr('gamepickid', '-1');
         $(friend_button_selector).attr('myfriendpickid', pick_id_of_previously_selected_team);
         
         // I am selected now.  My friend is not.
         $(button_selector).attr('myfriendpickid', '-1');
         $(button_selector).attr('gamepickid', pick_id_of_previously_selected_team);
         return true;
      }
      
      return true;
   });
}


