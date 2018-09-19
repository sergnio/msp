/* :mode=javascript:

   file: mypicks04.js
   date: may-2016
 author: hfs
   desc: A javascript collection supporting league_player_names.php.
   
      There are two functions needed here, a clock to refresh the game lockout
      and button action; selecting/deselecting picks
   
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

var alerts03_on = false;
var alerts03_trivial_on = false;


$(document).ready(function() {
   $('#IDb_lockbutton').click(function(e) {
         
      e.preventDefault();  // There is none at this time.
      
      $('#single tbody tr td button').hide();
      lock_status =    $(this).attr('status');
      if (lock_status == 'locked') {
         $(this).attr('status', 'unlocked');
         $(this).html('lock');
      }
      return true;
         });
});
      /*
      button_id =      $(this).attr('id');
      game_pick_id =   $(this).attr('gamepickid');
      my_friend_pick_id =      $(this).attr('myfriendpickid');
      my_friend_button_id = $(this).attr('myfriendbuttonid');
      
      selector_rowid = '#' + $(this).attr('myrowid'); // mysql datetime datatype
      games_starts_at = $(selector_rowid).attr('gameat');
      schedule_id =     $(selector_rowid).attr('scheduleid');
      
      pick_limit = $('#IDtb_fullschedule').attr('picklimit');
      league_id =  $('#IDtb_fullschedule').attr('leagueid');
      user_id =    $('#IDtb_fullschedule').attr('userid');
      week =       $('#IDtb_fullschedule').attr('week');
      
      var action = 'removing';   // or 'adding'
      if (game_pick_id != -1) {  // Then he's newly picking this team.  Check pick limits.
         action = 'adding';
         var present_number_picks = $('#IDtb_fullschedule tr td button[game_selected=yes]').length;
         if (present_number_picks  >= pick_limit) {
            alert("Only " + pick_limit + " picks are allowed.  You must first delete a pick to choose this team.");
            return true;
         }
      }
      if (alerts03_on == true) {
         alert(" dothis: " + action +
              "\n whereplay: " + where_play +
              "\n action " + action +
              "\n userid: " + user_id +
              "\n schedule_id: " + schedule_id +
              "\n week: " + week +
              "\n pick_limit: " + pick_limit +
              "\n my_friend_pick_id: " + my_friend_pick_id +
              "\n game_pick_id: " + game_pick_id +
              "\n my_friend_button_id: " + my_friend_button_id +
              "\n league_id: " + league_id);
      }
 
      $.ajax ({
         type: 'POST',
         url: 'ajax_support_picks.php',
         dataType: 'json',
         timeout: 2000,
         async: true,
         data:  {  dothis: action,      authority: 'member',           userid: user_id, 
               scheduleid: schedule_id,  homeaway: where_play,     gamepickid: game_pick_id,
                     week: week,         leagueid: league_id,  myfriendpickid: my_friend_pick_id },
         error: function() {
            alert("An error occured.  Please try again later." );
         },
         success: function(data) {
            (alerts03_trivial_on && alert("good to go." ));
            postPickProcessing(data);
         },
         complete: function() { 
           (alerts03_trivial_on && alert("complete"));
         }
      });
      
      $('#single tbody tr td button').show();

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
      (alerts03_trivial_on && alert ("data is " + data.status));
      
      if (data.status != 1) {
         // Is it possible the database was modified?  Probably not, but maybe a page refresh might be wise.
         if (data.ermsg) {
            alert(data.ermsg);
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
*/
}


