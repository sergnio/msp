<?php
require_once 'mypicks_startsession.php';

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');

do_header('MySuperPicks.com - Thank You');
do_nav();
?>
<script type="text/javascript">
   var my_image = new Image();
   my_image.src = 'images/players.jpg';
</script>
   <div class="jumbotron" id="home_page">
   </div>
   <div class="container">
<?php
$update = '';
if (!empty($_GET['update'])) { $update = $_GET['update']; }
$update = sql_sanitize($update);
$update = html_sanitize($update);
if(check_valid_user()) {
   if ($update == '1') {
     echo "<div class=\"alert alert-success text-center\"><h3><i>Thank you!</i></h3></div>";
   }
   ?>
         <h1 class="text-center"><i>You are registered and logged in.</i></h1>
         <p class="lead">You are logged in and ready to play. You will remain logged in even if you close your browser, so be sure to use the log out button if you are on an unsecured computer. You can add/edit your picks on the "This Week's Lines" page, view your pick history on the "My Pick's" page, and see everyone's standings and picks on the "Standings" page. If you need to change your email or password, you can do so using the "My Profile" page. Again, thank you and good luck!</p>
      </div>
   <?php
} else {
?>
      <h1><i>Error</i></h1>
      <p class="lead">Something went wrong with your registration. Please <a href="contact.php">contact us</a>.</p>
   </div>
<?php    
}
do_footer('bottom');
?>