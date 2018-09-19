<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

$id = '';
if (!empty($_GET['id'])) { $id = $_GET['id']; }
$id = sql_sanitize($id);
$id = html_sanitize($id);
do_header('MySuperPicks.com - Message');
do_nav();
?> 

<div class="container">
    <br />
    <br />
    <br />
<div class="hidden-sm hidden-md hidden-lg">
    <br />
    <br />
    <br />
</div>
<?php 
if(check_valid_user()) {
   view_mail($id);
}
?>
</div>
<?php
do_footer('bottom');
?>