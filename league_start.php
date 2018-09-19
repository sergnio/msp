<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

do_header('MySuperPicks.com - Start Your League');
do_nav();
?>

   <div class="container"><br /><br /><br /><br />
      <h2>Here is the paypal processing part</h2>
      <a href="league_management.php">Click here to "process" payment.</a>
   </div> 
<?php
do_footer('bottom');
?>