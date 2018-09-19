/* :mode=javascript: 

   file: mypicks01.js
   date: apr-2016
 author: hfs
   desc: A javascript collection supporting the Standings page.  This page 
   includes both the weekly standings table and the season standings to date.  Two
   call backs are used to support these tables.
   
   In the HTML you will find elements <div id='IDdiv_putWeeklyTable'> and
   <div id='IDdiv_putSeasonTable'>.   The jQuery uses these as selection targets
   to place the HTML table code.
   
   The callback functions are in files mypicks##.js  These things are so brittle
   I made the call to isolate them.  Someone may wish to move them into the 
   php files that use them.  I'll leave them here.
   
*/

var alerts_on = false;
var alerts_trivial_on = false;  // real trouble

//TODO - need to fire selected button so on page arrival tables are present.

$(document).ready(function() {
   $('#IDul_lowerWeekSelector01 li a, #IDul_lowerWeekSelector02 a, #IDul_upperWeekSelector01 a, #IDul_upperWeekSelector02 a').click(function(e) {
         
      e.preventDefault();
      $("#IDd_ajaxmessageshere").children().remove();

      var anchor_status = $(this).attr("status");
      if (anchor_status == 'disabled') {
         var formated_ermsg = "<div class='alert alert-info'><b>Info!</b>&nbsp;&nbsp; This week has not been completed.  There is nothing to report.</div>";
         $("<div> " + formated_ermsg + " </div>").appendTo('#IDd_ajaxmessageshere');
         return;
      }
      
      var week_selected =  $(this).text();
      var league_id     =  $(this).attr("leagueis");
      var userid        =  $('#IDi_userid').attr('value');
      var points_yes_no =  $('#IDi_pointsyesno').attr('value');
      var push_points   =  $('#IDi_push').attr('value');
      var first_week    =  $('#IDi_firstweek').attr('value');
      var last_week     =  $('#IDi_lastweek').attr('value');
      var week_id       =  parseInt(week_selected, 10) + 10;
      var set_active_id = "#IDli_weekval" + week_id;
      
      if (week_selected < first_week) {
         var formated_ermsg = "<div class='alert alert-info'><b>Info!</b>&nbsp;&nbsp; The league's first week of play is " + first_week +
            ". There is nothing to report for week " + week_selected + ".</div>";
         $("<div> " + formated_ermsg + " </div>").appendTo('#IDd_ajaxmessageshere');
         return;
      }

     
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
         data:  {   dothis: 'buildWeeklyStandings', buildMode: 'rows',         weekselected: week_selected, 
                    leagueid: league_id,                 player: userid,  pointsyesno: points_yes_no,
                pushpoints: push_points},
         error: function() {
            alert("An error occurred - weekly processing.  Please try later." );
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
   
   
     (alerts_on && alert ("text is " + 
      "\n week_selected " +       week_selected  +       
      "\n league_id     " +      league_id       +
      "\n userid        " +      userid          +
      "\n points_yes_no " +      points_yes_no   +
      "\n push_points   " +      push_points     +
      "\n first_week    " +      first_week      +
      "\n last_week     " +      last_week       +
      "\n week_id       " +      week_id         +
      "\n set_active_id " +      set_active_id));
     
      $.ajax ({
         type: 'POST',
         url: 'ajax_support_season_standings_table.php',
         dataType: 'json',
         timeout: 2000,
         async: true,
         data:  {dothis: 'seasontable', selectedweek: week_selected, leagueid: league_id,
              firstweek: first_week,        lastweek: last_week,  pointsyesno: points_yes_no,
              pushpoints: push_points},
         error: function() {
            alert("An error occurred - seasonal processing.  Please try later." );
         },
         success: function(data) {
            //alert("data[5].playername " + data[5].playername);
            publish_season_table(data, week_selected);
         },
         complete: function() { 
           // alert("complete");
         }
      });
   });
});

var table_weekly = "\n" +
"      <table class='table-condensed' id='IDtable_singleWeek' style='margin-left:auto;margin-right:auto;'>\n" +
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

var table_season = "\n" +
"      <table class='table-condensed' id='IDtable_season' style='margin-left:auto;margin-right:auto;'>\n" +
"         <thead>\n" +
"            <tr>" +
"               <th style='text-align:center'>Player</th>\n" +
"               <th style='text-align:center'>W</th>\n" +
"               <th style='text-align:center'>L</th>\n" +
"               <th style='text-align:center'>T</th>\n" +
"               <th style='text-align:center'>Tot</th>\n" +
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
      
      for (var ndx = 0; ndx < data.length; ndx++) {
         var data_user_name = data[ndx].user_name;
         html_row_string += "\n";
         if (user_name == data_user_name) {
            html_row_string += "<tr class='Ctr_weekly'  style='background-color:" + highlight_player + ";' >";
         } else {
            html_row_string += "<tr class='Ctr_weekly' >";
         }
         html_row_string += "\n" + 
"               <td class='Ctd_player'>" + data[ndx].playername + "&nbsp;&nbsp;&nbsp;</td>\n" +
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


// Table:  Player  W  L  T  Tot
//'username'
//'playername'=> $player_name,
//'wins' => $wins, 
//'losses' => $losses, 
//'pushes' => $pushes, 
//'totalscore' => $total_score);
function publish_season_table (data, week_selected) {
   $(document).ready(function() {
      $('#IDs_seasonweeknumber').html(week_selected);
      $('#IDtable_season').remove();
      var html_row_string = '';
      var html_row_string_inline = '';
      var user_name = $('#IDi_player').attr('value');
      var highlight_player = $('#IDi_highlightplayer').attr('value');
      
      (alerts_on && alert("user name, highlight, week: " + user_name + ", " + highlight_player + ", " + week_selected));
      (alerts_on && alert("data length is just befor for loop " + data.length));
      
      for (var ndx = 0; ndx < data.length; ndx++) {
         var data_user_name = data[ndx].username;
         html_row_string += "\n";
         if (user_name == data_user_name) {
            html_row_string += "<tr class='Ctr_weekly'  style='background-color:" + highlight_player + ";' >";
         } else {
            html_row_string += "<tr class='Ctr_weekly' >";
         }
         html_row_string += "\n" + 
"               <td class='Ctd_player'>" + data[ndx].playername + "&nbsp;&nbsp;&nbsp;</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].wins +        "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].losses +      "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].pushes +      "</td>\n" +
"               <td class='Ctd_numbers'>" + data[ndx].totalscore + "&nbsp;&nbsp;</td>\n" +
"            </tr>";
     }
     (alerts_on && alert(table_weekly));
     (alerts_on && alert(html_row_string));
      if (!html_row_string) {
         $("<div id='IDd_noweekdata'>There is no data for the season " + week_selected + ".</div>").insertAfter('#IDdiv_putSeasonTable');
      } else {
         $(table_season).insertAfter('#IDdiv_putSeasonTable');
         $('#IDtable_season > tbody:first').append(html_row_string);
      }
   });
};

function paintCurrentWeek() {
   var e = document.getElementById('IDli_weekval11up');
   e.click();
   //alert("here we are");
}

$(document).ready(function() {
   $('#IDul_lowerWeekSelector01 li .active').trigger('click');
});


$(document).ready(function() {
   $('#IDb_pickemlegend').click(function(e) {
      var thisval = $('#IDd_legend').is(":visible");
      if (thisval == true) {
         $('#IDd_legend').hide();
      } else {
         $('#IDd_legend').show();
      }
   });
});
