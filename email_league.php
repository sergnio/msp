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

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');

$league_id = $_SESSION['league_id'];
$league_name = $_SESSION['league_name'];
$ref_email_list = '';

if (!$league_id || !$league_name) {
   formatSessionMessage("There is missing league information.  We cannot mail.", 'warning', $msg);
   setSessionMessage($msg, 'error');
   header("Location: index.php");
   die();
}
if (!getEmailCommaList('league', $ref_email_list, $league_id, $ref_status_text)) {
   formatSessionMessage("The league $league_name has no email list.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header("Location: index.php");
   die();
}
if (empty($_SESSION['emailleague'])) {
   $_SESSION['emailleague'] = $ref_email_list;
}

do_header('MySuperPicks.com - Email League'); 
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
       <?php echo "<h1 class='text-center'>Email League <i>$league_name</i></h1>"; ?>
         <br />
         <br />
         <form action="email_league2.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data"> 
            <div class="form-group">
               <label for="name" class="col-sm-2 control-label">Subject</label>
               <div class="col-sm-8">  
                  <?php echo "<input type='text' class='form-control' name='name' placeholder='Name' value='A league message from the Admin of $league_name'/>";?>
               </div>
            </div>
            <div class="form-group">
               <label for="email" class="col-sm-2 control-label">Email</label>
               <div class="col-sm-8">  
                  <input type="text" class="form-control" name="email" placeholder="Email" value="<?php if(!empty($_SESSION['emailleague'])) { echo $_SESSION['emailleague']; } ?>" />
               </div>
            </div>
            <div class="form-group">
               <label for="sfname" class="col-sm-2 control-label">Message</label>
               <div class="col-sm-8">  
				      <textarea class="form-control" rows="3" placeholder="Message" name="message"><?php if(!empty($_SESSION['messageleague'])) { echo $_SESSION['messageleague']; } ?></textarea>
				      <br />
				      <button type="submit" class="btn btn-primary">Send Message</button>
               </div>
            </div>
         </div>
<?php
unset($_SESSION['emailleague'], $_SESSION['messageleague']);
do_footer('bottom');
?>