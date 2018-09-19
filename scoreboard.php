<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


do_header('MySuperPicks.com - Against The Spread');
do_nav();
?>
<div class='container'>
<?php
echo_container_breaks();
echoSessionMessage();

echo get_text2('scoreboard');
?>



</div>
<?php
do_footer('bottom');
?>