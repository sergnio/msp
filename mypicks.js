/* :mode=javascript:

   file: mypicks01.js
   date: apr-2016
 author: hfs
   desc: A javascript collection supporting the mysuperpicks website.
   
*/

var alerts_on = false;
var alerts_trivial_on = false;  // real trouble

// It is possible to reselect before the callback completes.  This generates
// an error.  We have to disable the buttons.

$(document).ready(function() {
   $('#IDul_lowerWeekSelector01 a, #IDul_lowerWeekSelector02 a, #IDul_upperWeekSelector01 a, #IDul_upperWeekSelector02 a').click(function(e) {
         
      e.preventDefault();

      var anchor_status = $(this).attr("status");
      if (anchor_status == 'disabled') {
         alert("This week has not been completed.  There is nothing to report.");
         return;
      }
      
      var week_selected = $(this).text();
      var league = $(this).attr("leagueis");
      var userid = $('#IDi_userid').attr('value');
      var week_id = parseInt(week_selected, 10) + 10;
      var set_active_id = "#IDli_weekval" + week_id;
      
      (alerts_trivial_on && alert ("text is " + week_selected));
      
      $('#IDul_lowerWeekSelector01 a, #IDul_lowerWeekSelector02 a, #IDul_upperWeekSelector01 a, #IDul_upperWeekSelector02 a').hide();
      
      
      $('li').removeClass('active');
      $(set_active_id + 'up').addClass('active');  // There's an upper and lower selector.
      $(set_active_id + 'down').addClass('active');
      
      (alerts_on && alert("week, league, user: " + week_selected + ", " + league + ", " + userid));
      
      $.ajax ({
         type: 'POST',
         url: 'ajax_support_weekly_standings_table.php',
         dataType: 'json',
         timeout: 2000,
         async: true,
         data:  {dothis: 'buildWeeklyStandings', buildMode: 'rows', week: week_selected, league: league, player: userid},
         error: function() {
            alert("An error occurred.  Please try later." );
         },
         success: function(data) {
            //alert("data[5].user_name " + data[5].user_name);
            publish_weekly_table(data, week_selected);
         },
         complete: function() { 
           // alert("complete");
         }
      });
      $('#IDul_lowerWeekSelector01 a, #IDul_lowerWeekSelector02 a, #IDul_upperWeekSelector01 a, #IDul_upperWeekSelector02 a').show();
   });
   
});

var table_weekly = "\n" +
"      <table id='IDtable_singleWeek' style='margin-left:auto;margin-right:auto;'>\n" +
"         <thead>\n" +
"            <tr>" +
"               <th style='text-align:center'>Player</th>\n" +
"               <th style='text-align:center'>W</th>\n" +
"               <th style='text-align:center'>L</th>\n" +
"               <th style='text-align:center'>T</th>\n" +
"               <th style='text-align:center'>Tot</th>\n" +
"               <th style='text-align:center;'>Picks</th>\n" +
"            </tr>\n" +
"         </thead>\n" +
"         <tbody>\n" +
"           <tr style='display:none;'><td></td></tr>\n" +
"         </tbody>\n" +
"      </table>\n";

// Table:  Player  W  L  T  Tot   Picks
//$user_name, $picks, $wins, $losses, $pushes, $total_score
function publish_weekly_table (data, week_selected) {
   $(document).ready(function() {
      $('#IDs_weeknumber').html(week_selected);
      $('#IDtable_singleWeek').remove();
      $('#IDd_noweekdata').remove();
      var html_row_string = '';
      var html_row_string_inline = '';
      var user_name = $('#IDi_player').attr('value');
      var highlight_player = $('#IDi_highlightplayer').attr('value');
      
      (alerts_on && alert("user name, highlight, week: " + user_name + ", " + highlight_player + ", " + week_selected));
      (alerts_on && alert("data length is just befor for loop " + data.length));
      
      for (var ndx = 1; ndx < data.length; ndx++) {
         var data_user_name = data[ndx].user_name;
         html_row_string += "\n";
         if (user_name == data_user_name) {
            html_row_string += "<tr class='Ctr_weekly'  style='background-color:" + highlight_player + ";' >";
         } else {
            html_row_string += "<tr class='Ctr_weekly' >";
         }
         html_row_string += "\n" + 
"               <td class='Ctd_player'>" + data[ndx].user_name +    "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].wins +        "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].losses +      "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].pushes +      "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].total_score + "</td>\n" +
"               <td class='Ctd_weeklyPicks'>" + data[ndx].picks +   "</td>\n" +
"            </tr>";
     }
     (alerts_on && alert(table_weekly));
     (alerts_on && alert(html_row_string));
      if (!html_row_string) {
         $("<div id='IDd_noweekdata'>There is no data for week " + week_selected + ".</div>").insertAfter('#IDdiv_putWeeklyTable');
      } else {
         $(table_weekly).insertAfter('#IDdiv_putWeeklyTable');
         $('#IDtable_singleWeek > tbody:first').append(html_row_string);
      }
   });
};
