/* :mode=javascript:

   file: mypicks2.js
   date: apr-2016
 author: hfs
   desc: A javascript collection supporting the mysuperpicks website.
   
*/

var alerts_on = false;
var alerts_trivial_on = false;  // real trouble :-)

var username;
var fname; 
var lname; 
var email; 
var utype; 
var actstatus;


// league_users.php has a button that fires an edit.  Here it is.
$(document).ready(function() {
   $('button[name=edituserbutton]').click(function(e) {
      e.preventDefault();
      var useraccountid = $(this).attr("value");
      var scope = ($(this).attr("scope")) ? $(this).attr("scope") : 'league';
      
      var username = $("#IDr_editrow" + useraccountid + " td input[name=username]").attr("value");
      var fname = $("#IDr_editrow" + useraccountid + " td input[name=fname]").val();
      var lname = $("#IDr_editrow" + useraccountid + " td input[name=lname]").val();
      var email = $("#IDr_editrow" + useraccountid + " td input[name=email]").val();
      var utype = $("#IDr_editrow" + useraccountid + " td select[name=usertype] option[selected=selected]").val();
      var actstatus = $("#IDr_editrow" + useraccountid + " td select[name=active_status] option[selected=selected]").val();
      
      (alerts_on && alert("username  value is " + username + ", "  + fname + ", "  + lname + ", "  +
         email + ", "  + utype + ", "  + actstatus + ", "  + useraccountid));
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
         data:  {  dothis: 'edit',        authority: 'admin',  scope: scope, 
              useraccount: useraccountid,  username: username, 
                    fname: fname,             lname: lname,        email: email, 
                    utype: utype,         actstatus: actstatus},
         error: function() {
            alert("An error occured.  Please try later." );
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
      
      return true;
   });
}
