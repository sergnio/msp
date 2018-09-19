<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

do_header('MySuperPicks.com - Password Reset');
do_nav();
?> 
   <div class="container">
<?php
echoContainerBreaks();
echoSessionMessage();
$user_name = getSessionInfo('resetpasswordusername');
do_password_form($user_name);
?>
   </div>
<?php
do_footer('bottom');
?>