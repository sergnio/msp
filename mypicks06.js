/* :mode=javascript: 

   file: mypicks01.js
   date: may-2016
 author: hfs
   desc: support to hide/show league options based on league type.

*/

//TODO - need to fire selected button so on page arrival tables are present.

$(document).ready(function() {
   $('input[name=league_type]').click(function(e) {
      var thisval = $(this).val();
      if (thisval == 2 || thisval == 3) {
         $('#IDd_pickumcount').hide();
         $('#IDd_pushvalue').hide();
         $('#IDd_seasonbegins').show();
      } else {
         $('#IDd_pickumcount').show();
         $('#IDd_pushvalue').show();
         $('#IDd_seasonbegins').hide();
      }
   });
});

$(document).ready(function() {
   if ($("#IDi_leaguecohort").is(":checked") ||
         $("#IDi_leaguelastman").is(":checked"))
   {
      $('#IDd_pickumcount').hide();
      $('#IDd_pushvalue').hide();
      $('#IDd_seasonbegins').show();
   } else if($("#IDd_seasonbegins").is(":checked")) {
      $('#IDd_pickumcount').show();
      $('#IDd_pushvalue').show();
      $('#IDd_seasonbegins').hide();
   } else {
      $('#IDd_pickumcount').show();
      $('#IDd_pushvalue').show();
      $('#IDd_seasonbegins').show();
   }
});