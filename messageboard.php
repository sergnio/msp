<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
require_once 'site_fns_diminished.php';
$msg = '';

@ $league_id = (isset($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';
if (!$league_id) {
   formatSessionMessage("You must be associated with a league to leave a league message.", 'info', $msg, "mb-11");
   setSessionMessage($msg, 'error');
   header('Location: index.php');
   die();
}

validateUser();

$update = '';
if (!empty($_GET['update'])) { $update = $_GET['update']; }
$update = sql_sanitize($update);
$update = html_sanitize($update);

do_header('MySuperPicks.com - My Picks');
do_nav();
?>

   <div class="container">
<?php echo_container_breaks();
echoSessionMessage(); ?>
      <div class="hidden-sm hidden-md hidden-lg">
         <br />
         <br />
         <br />
      </div>
<?php 
// if ($update == '1') {
//    echo "<br /><div class=\"alert alert-success\">Message Added.</div>";
// }
// if ($update == '2') {
//    echo "<br /><div class=\"alert alert-warning\">Comment Added.</div>";
// }
// if ($update == '3') {
//    echo "<br /><div class=\"alert alert-danger\">Error - Comment Not Saved</div>";
// }
// if ($update == '4') {
//    echo "<br /><div class=\"alert alert-success\">Message Deleted.</div>";
// }
// if ($update == '5') {
//    echo "<br /><div class=\"alert alert-success\">Comment Deleted.</div>";
// }
// if ($update == 'error') {
//    echo "<br /><div class=\"alert alert-danger\">Error - Message Not Saved</div>";
// }

get_messageboard($_SESSION['league_id']);
?>
      <script>
         CKEDITOR.replace( 'message', {
            height: '70px',
            // Define the toolbar groups as it is a more accessible solution.
            toolbarGroups: [
              {"name":"basicstyles","groups":["basicstyles"]},
              {"name":"paragraph","groups":["list","blocks"]},
              {"name":"insert","groups":["insert"]},
              {"name":"styles","groups":["styles"]},
              {"name":"about","groups":["about"]}
            ],
            // Remove the redundant buttons from toolbar groups defined above.
            removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar,Image,Flash,Table,PageBreak,Iframe'
         } );
      </script>
   </div>
   <br /><br />
<?php
do_footer('bottom');
?>