<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');

do_header('MySuperPicks.com - Manage Your League');
do_nav();
?>
   <div class="container">
<?php 
echo_container_breaks(); 
echoSessionMessage();
get_my_league($_SESSION['league_admin']); ?>
   </div>  <!-- END container -->
<?php
do_footer('bottom');
?>