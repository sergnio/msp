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

validateUser('admin');

$ref_status_text = '';
$num_active_users = getCount('site', $ref_status_text);

do_header('MySuperPicks.com - Email Site'); 
do_nav();

?> 
   <div class="container">
<?php
echo_container_breaks();
echoSessionMessage();
?>
      <div class="hidden-sm hidden-md hidden-lg">
         <br />
         <br />
         <br />
      </div>
       <?php echo "<h1 class='text-center'>Email <i>MySuperPicks</i> Active Members</h1>
         <h4  class='text-center'>($num_active_users active accounts)</h4>"; ?>
         <br />
         <br />
         <form action="email_site2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
            <div class="form-group">
               <label for="name" class="col-sm-2 control-label">Subject</label>
               <div class="col-sm-8">  
                  <?php echo "<input type='text' class='form-control' name='name' placeholder='Name' value='A message from the MySuperPicks administrator'/>";?>
               </div>
            </div>
            <div class="form-group">
               <label for="email" class="col-sm-2 control-label">Additional Emails</label>
               <div class="col-sm-8">  
                  <input type="text" class="form-control" name="email" placeholder="Additional email addresses here.  Seperate each with a comma." value="<?php if(!empty($_SESSION['emailsite'])) { echo $_SESSION['emailsite']; } ?>" />
               </div>
            </div>
            <div class="form-group">
               <label for="sfname" class="col-sm-2 control-label">Message</label>
               <div class="col-sm-8">  
				      <textarea class="form-control" rows="3" placeholder="Message" name="message"><?php if(!empty($_SESSION['messagesite'])) { echo $_SESSION['messagesite']; } ?></textarea>
				      <br />
				      <button type="submit" class="btn btn-primary">Send Message</button>
               </div>
            </div>
         </div>
<?php
unset($_SESSION['emailsite'], $_SESSION['messagesite']);
do_footer('bottom');
?>