/* :mode=javascript: 

   file: mypicks01.js
   date: may-2016
 author: hfs
   desc: A javascript collection supporting the KO Standings pages.  

*/

//TODO - need to fire selected button so on page arrival tables are present.

var color_red =               '#FF0000';
var color_skyblue =           '#87CEEB';
var color_green =             '#008000';
var color_blue =              '#0000FF';
var color_shootout_yellow =   '#FFFF99';
var color_black =             '#000000';

$(document).ready(function() {
   //alert("page is loaded");
   $('#IDtable_ko_cohort tbody tr td[winloss=in]').css(       {'background-color': color_skyblue         });
   $('#IDtable_ko_cohort tbody tr td[playerstatus=win]').css( {           'color': color_blue, 'font-weight': 'bold' });
   
   $('#IDtable_ko_lastman tbody tr td[gamestatus=wi]').css(   {           'color': color_blue               });
   $('#IDtable_ko_lastman tbody tr td[gamestatus=ip]').css(   {           'color': color_green              });
   $('#IDtable_ko_lastman tbody tr td[gamestatus=lo]').css(   {           'color': color_red                });
   $('#IDtable_ko_lastman tbody tr td[special=blessed]').css( {'background-color': color_shootout_yellow    });
   $('#IDtable_ko_lastman tbody tr td[special=forfeit]').css( {           'color': color_black              });
   $('#IDtable_ko_lastman tbody tr td[winner=winner]').css(   {           'color': color_blue, 'font-weight': 'bold' }); // blue  special='shootout IDtable_ko_legend
   
   
   $('#IDtable_ko_legend tbody tr td[special=blessed]').css('background-color','#FFFF99'); // blue  special='shootout
   $('#IDtable_ko_legend tbody tr td[gamestatus=wi]').css({'color': color_blue});  // skyblue
   $('#IDtable_ko_legend tbody tr td[gamestatus=ip]').css({'color': color_green});  // green
   $('#IDtable_ko_legend tbody tr td[gamestatus=lo]').css({'color': color_red});  // skyblue
   $('#IDtable_ko_legend tbody tr td[winner=winner]').css({'color': '#0000FF', 'font-weight': 'bold'}); // blue  special='shootout IDtable_ko_legend
});

// Hide show the about text.
$(document).ready(function() {
   $('#IDb_koabout').click(function(e) {
      var thisval = $('#IDd_about').is(":visible");
      if (thisval == true) {
         $('#IDd_about').hide();
      } else {
         $('#IDd_about').show();
      }
   });
});

// Hide show the about text.
$(document).ready(function() {
   $('#IDb_kolegendlastman').click(function(e) {
      var thisval = $('#IDd_legend').is(":visible");
      if (thisval == true) {
         $('#IDd_legend').hide();
      } else {
         $('#IDd_legend').show();
      }
   });
});

// Hide show the about text.
$(document).ready(function() {
   $('#IDb_kolegendcohort').click(function(e) {
      var thisval = $('#IDd_legend').is(":visible");
      if (thisval == true) {
         $('#IDd_legend').hide();
      } else {
         $('#IDd_legend').show();
      }
   });
});