/* :mode=javascript:

   file: mypicks04.js
   date: may-2016
 author: hfs
   desc: A javascript collection supporting league_player_names.php.  This is
   the page "My League Player Names" accessed via ->Memeber Area ->My Names
   
      TODO A clock to refresh the game lockout
   
*/

var alerts04_on = false;
var alerts04_trivial_on = false;


$(document).ready(function() { 
   $('#IDb_lockbutton').click(function(e) {
      lock_status = $(this).attr('status');
      if (lock_status == 'LOCKED') {
         $(this).attr('status', 'UNLOCKED');
         $(this).text('LOCK');
         $('#IDtb_leaguenames tr td button[name=editbutton]').removeClass("btn-default");
         $('#IDtb_leaguenames tr td button[name=editbutton]').addClass("btn-info");
         $('#IDtb_leaguenames tr td input[name=playnameinput]').prop('disabled', false)
      } else {
         $(this).attr('status', 'LOCKED'); 
         $(this).text('UNLOCK');
         $('#IDtb_leaguenames tr td button[name=editbutton]').removeClass("btn-info");
         $('#IDtb_leaguenames tr td button[name=editbutton]').addClass("btn-default");
         $('#IDtb_leaguenames tr td input[name=playnameinput]').prop('disabled', true)
      }
   });
});

$(document).ready(function() { 
   $('#IDtb_leaguenames tr td button[name=editbutton]').click(function(e) {
      lock_status = $('#IDb_lockbutton').attr('status');
      if (lock_status == 'LOCKED') {
         alert("Editing is disabled.  Unlock the page to proceed.");
         return true;
      }
      
      input_selector = '#' + $(this).attr('myinputid'); 
      player_name_change = $(input_selector).val();
      league_id = $(input_selector).attr('leagueid');
      user_id = $(input_selector).attr('userid');  // check on the server

      (alerts04_on && alert("Input:" +
         "\n input_selector     " + input_selector     +
         "\n player_name_change " + player_name_change +
         "\n league_id          " + league_id          +
         "\n user_id            " + user_id ));

       $.ajax ({
          type: 'POST',
          url: 'ajax_support_player_names.php',
          dataType: 'json',
          timeout: 2000,
          async: true,
          data:  { dothis: 'edit', authority: 'member',
                 leagueid: league_id, userid: user_id, playernamechange: player_name_change },
          error: function() {
             alert("An error occurred.  Please try again later." );
          },
          success: function(data) {
             (alerts04_trivial_on && alert("ajax - success"));
             postPlayerProcessing(data);
          },
          complete: function() { 
          }
       });
   });
});

////'status' => $status, 
////'ermsg' => $ref_status_text,
////'returnparameter' => $return_parameter
//   
function postPlayerProcessing(data) {
   $(document).ready(function() {
      (alerts04_trivial_on && alert("data status " + data.status));
      if (data.status != 1) {
         // Is it possible the database was modified?  Probably not, but maybe a page refresh might be wise.
         if (data.ermsg) {
            alert(data.ermsg);
            return false;
         }
      }
      if (data.ermsg) {
         // messsages for consumption?
         alert(data.ermsg);  // informational - not real error
         return true;
      }
      if (data.status == 1) {
         alert("Edit completed.");
         return true;
      }
      return true;
   });
};
