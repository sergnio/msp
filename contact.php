<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: contact.php
   date: apr-2016
 author: original
   desc: File is linked to main page "Contact Us"
marbles:
   note: cleaned up some code; fixed session start issues; timeout to days
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


$name =    (!empty($_SESSION['contactname']))    ? $_SESSION['contactname']    : '';
$email =   (!empty($_SESSION['contactemail']))   ? $_SESSION['contactemail']   : '';
$message = (!empty($_SESSION['contactmessage'])) ? $_SESSION['contactmessage'] : '';

do_header('MySuperPicks.com - Contact Us');
do_nav();
echo "   <div class='container'>";
echo_container_breaks();
echoSessionMessage();
echo "
      <div class='hidden-sm hidden-md hidden-lg'>
         <br />
         <br />
         <br />
      </div>
      <h1 class='text-center'>Contact Us</h1>
      <br />
      <br />
      <form action='contact2.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'>
         <div class='form-group'>
            <label for='name' class='col-sm-2 control-label'>Name</label>
            <div class='col-sm-8'>
               <input type='text' class='form-control' name='contactname' placeholder='Name' value='$name' />
            </div>
         </div>
         <div class='form-group'>
            <label for='email' class='col-sm-2 control-label'>Email</label>
            <div class='col-sm-8'>
               <input type='email' class='form-control' name='contactemail' placeholder='Your Email Address' value='$email' />
            </div>
         </div>
         <div class='form-group'>
            <label for='sfname' class='col-sm-2 control-label'>Message</label>
            <div class='col-sm-8'>
			       <textarea class='form-control' rows='3' placeholder='Your message to us.' name='contactmessage'>$message</textarea>
			       <br />
				    <button type='submit' name ='submit' class='btn btn-primary'>Send Message</button>
            </div>
         </div>
";
unset($_SESSION['contactname']);
unset($_SESSION['contactemail']);
unset($_SESSION['contactmessage']);
echo "
         </div>";
do_footer('bottom');
