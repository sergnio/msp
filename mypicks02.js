/* :mode=javascript:

   file: mypicks02.js
   date: may-2016
 author: hfs
   desc: A javascript collection supporting php function get_users() in
   site_fns_diminished.php.  get_users() is used in both users.php
   and league_users.php.  ->Admin ->Edit Users = "Edit All Users", 
   ->League Admin ->Edit Users = "Edit League LAEAGUENAME Users"
   
*/

var alerts_on = false;
var alerts_trivial_on = false;  // real trouble :-)

var username;
var fname; 
var lname; 
var email; 
var oldemail;
var utype; 
var actstatus;
var leagueactstatus;


// users.php page (main page table rendered by echoEditSiteUsers()) has a button that fires an edit.  Here it is.
// This is an 'admin' level function
$(document).ready(function() {
   $('button[name=edituserbutton]').click(function(e) {
      e.preventDefault();
      $("#IDd_ajaxmessageshere").children().remove();
      var useraccountid = $(this).attr("value");
      var scope = ($(this).attr("scope")) ? $(this).attr("scope") : 'league';
      
      var username =  $("#IDr_editrow" + useraccountid + " td input[name=username]").attr("value");
      var fname =     $("#IDr_editrow" + useraccountid + " td input[name=fname]").val();
      var lname =     $("#IDr_editrow" + useraccountid + " td input[name=lname]").val();
      var email =     $("#IDr_editrow" + useraccountid + " td input[name=email]").val();
      var oldemail =  $("#IDr_editrow" + useraccountid + " td input[name=email_old]").val();
      var actstatus = $("#IDr_editrow" + useraccountid + " td select[name=active_status]").val();
      var utype =     $("#IDr_editrow" + useraccountid + " td select[name=usertype]").val();
       
      (alerts_on && alert("username  value is " + username + ", fname "  + fname + ", lname "  + lname + ", email "  +
         email + ", utype "  + utype + ", siteactive "  + actstatus + ", user_id "  + useraccountid  + ", old email "  + oldemail));
      
      // TODO clear all the row status background colors
      // var num = 3;  // bug
      $('#single tbody tr').each(function() {

         // if (num-- > 0) {
         //    alert($(this).css('background-color'));
         // }
         bg = $(this).css('background-color');
         if (bg === 'rgb(152, 251, 152)' || bg === 'rgb(255, 228, 225)') {  // palegreen or misty rose
            $(this).css('background-color', 'white');
         }
      });

      $.ajax ({
         type: 'POST',
         url: 'ajax_support_get_users.php',
         dataType: 'json',
         timeout: 2000,
         async: true,
         data:  {  dothis: 'siteuseredit',        authority: 'admin',  scope: scope, 
              useraccount: useraccountid,  username: username, 
                    fname: fname,             lname: lname,        email: email, 
                    utype: utype,         actstatus: actstatus, oldemail: oldemail},
         error: function() {
            alert("An error occurred.  Please try later." );
         },
         success: function(data) {
            postEditProcessing(data, useraccountid);
         },
         complete: function() { 
           (alerts_trivial_on && alert("complete"));
         }
      });
      
      (alerts_trivial_on && alert("ajax fallout"));

   });
});



// league_users.php page (main page table rendered by echoEditLeagueUsers()) has a button that fires an edit.  Here it is.
// This is an 'commissioner' level function
$(document).ready(function() {
   $('button[name=editleagueuserbutton]').click(function(e) {
      e.preventDefault();
      $("#IDd_ajaxmessageshere").children().remove();
      var useraccountid = $(this).attr("value");
      var scope = ($(this).attr("scope")) ? $(this).attr("scope") : 'league';
      
      var username = $("#IDr_editrow" + useraccountid + " td input[name=username]").attr("value");
      var fname =    $("#IDr_editrow" + useraccountid + " td input[name=fname]").val();
      var lname =    $("#IDr_editrow" + useraccountid + " td input[name=lname]").val();
      var lname =    $("#IDr_editrow" + useraccountid + " td input[name=lname]").val();
      var utype =    $("#IDr_editrow" + useraccountid + " td input[name=usertype]").val();
      
      var email =       $("#IDr_editrow" + useraccountid + " td input[name=email]").val();
      var oldemail =    $("#IDr_editrow" + useraccountid + " td input[name=email_old]").val();
      var playername =  $("#IDr_editrow" + useraccountid + " td input[name=playername]").val(); 
      var join_date =  $("#IDr_editrow" + useraccountid + " td input[name=joindate]").val(); 
      
      var oldplayername =     $("#IDr_editrow" + useraccountid + " td input[name=playername_old]").val();
      var actstatus =         $("#IDr_editrow" + useraccountid + " td input[name=siteactive]").val();
      // var leagueactstatus =   $("#IDr_editrow" + useraccountid + " td select[name=leagueactive] option[selected=selected]").val();  // yes or no
      var leagueactstatus =   $("#IDr_editrow" + useraccountid + " td select[name=leagueactive]").val();  // yes or n
      var league_paid =       $("#IDr_editrow" + useraccountid + " td select[name=leaguepaid]").val();  // yes or n
      
      var leagueid =          $("#IDi_leagueid").val();
      
      //actstatus = (actstatus == 'yes') ? 1 : 0;
      //leagueactstatus = (leagueactstatus == 'yes') ? 1 : 0;
      
      
      var iam = $("#IDi_iam").val();  // should be echoEditLeagueUsers
      
      (alerts_on && alert("vals: " + useraccountid + ", " + username + ", "  + fname + ", "  + lname + ", "  +
         email + ", " + actstatus + ", " + leagueactstatus +  ", "  + 
         playername  + ", " + scope  + ", " + leagueid ));
      // TODO clear all the row status background colors
      // var num = 3;  // bug
      $('#single tbody tr').each(function() {

         // if (num-- > 0) {
         //    alert($(this).css('background-color'));
         // }
         bg = $(this).css('background-color');
         if (bg === 'rgb(152, 251, 152)' || bg === 'rgb(255, 228, 225)') {  // palegreen or misty rose
            $(this).css('background-color', 'white');
         }
      });

      $.ajax ({
         type: 'POST',
         url: 'ajax_support_get_users.php',
         dataType: 'json',
         timeout: 2000,
         async: true,
         data:  {  dothis: 'leagueuseredit', authority: 'commissioner',      scope: scope, 
              useraccount: useraccountid,     username: username,            utype: utype,
                    fname: fname,                lname: lname,     leagueactstatus: leagueactstatus,
               playername: playername,       actstatus: actstatus,           email: email,
            oldplayername: oldplayername,     leagueid: leagueid,      oldemail: oldemail,
                 joindate: join_date,       leaguepaid: league_paid},
         error: function() {
            alert("An error occurred.  Please try later." );
         },
         success: function(data) {
            postLeagueUserProcessing(data, useraccountid);
         },
         complete: function() { 
           (alerts_trivial_on && alert("complete"));
         }
      });
      
      (alerts_trivial_on && alert("ajax fallout"));

   });
});

// expected response example:
// {"status":1,"supportingerror":"|updatefailonerow","ermsg":"The database update executed properly but no records were updated.  There was no cha
// nged data.","useraccount":"240","field":""}


function postEditProcessing(data, accountid) {
   $(document).ready(function() {
      
      var x = '#IDr_editrow' + accountid;
      (alerts_on && alert ("data is " + data.ermsg + ", and account num is " + accountid ));
      
      if (data.status == 1) {
         $(x).css('background-color', 'PaleGreen');   // rgb(152, 251, 152)
         (alerts_on && alert(data.ermsg));
         
      } else {
         $(x).css('background-color', 'MistyRose');   // rgb(255, 228, 225)
      }
      writeAjaxMessagesArray(data);
      return true;
   });
}


function postLeagueUserProcessing(data, accountid) {
   $(document).ready(function() {
      
      var x = '#IDr_editrow' + accountid;
      (alerts_on && alert ("data is " + data.ermsg + ", and account num is " + accountid ));
      
      if (data.status == 1) {
         $(x).css('background-color', 'PaleGreen');   // rgb(152, 251, 152)
         (alerts_on && alert(data.ermsg));
         
      } else {
         $(x).css('background-color', 'MistyRose');   // rgb(255, 228, 225)
      }
      writeAjaxMessagesArray(data);
      return true;
   });
}

function writeAjaxMessages(data) {
   $(document).ready(function() {
         
      var error_message = '';
      while (1) {
         if (!data.hasOwnProperty('ermsg')) {
            (alerts_on && alert("no ermsg property"));
            break;
         }
         if (data.ermsg === '') {
            (alerts_on && alert("the error message is empty"));
            break;
         }
         error_message = data.ermsg;
         break;
      }
      if (!error_message) {
         return false;
      }
      
      //$("<div id='IDd_noweekdata'> " + error_message + " </div>").insertAfter('#IDd_ajaxmessageshere');
      $("<div> " + error_message + " </div>").appendTo('#IDd_ajaxmessageshere');
      return true;
   });
}

function writeAjaxMessagesArray(data) {
   $(document).ready(function() {
         
      var error_message = '';
      var status = false;
      while (1) {
         if (!data.hasOwnProperty('ermsgarray')) {
            (alerts_on && alert("no ermsgarray property"));
            break;
         }
         if (data.ermsgarray.length == 0) {
            (alerts_on && alert("the error message is empty"));
            break;
         }
         for (ndx = 0; ndx < data.ermsgarray.length; ndx++) {
            //$("<div id='IDd_noweekdata'> " + error_message + " </div>").insertAfter('#IDd_ajaxmessageshere');
            $("<div> " + data.ermsgarray[ndx] + " </div>").appendTo('#IDd_ajaxmessageshere');
         }
         status = true;
         break;
      }
      return true;
   });
}
