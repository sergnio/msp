<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: adminsitemaintmessage.php
   date: apr-2016
 author: original
   desc: 
marbles: 
   note: 
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('admin');

$mysql = "
   SELECT sitemaintenancemessage 
     FROM nsp_admin 
    WHERE site = ?"; 

$status = 0;
$text = '';
while (1) {
   
   $site_name = ADMIN_TABLE; 

   if (!$conn = db_connect()) {
      formatSessionMessage("db connect error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$sth = $conn->prepare($mysql)) {
      formatSessionMessage("prepare error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$sth->bind_param("s", $site_name)) {
      formatSessionMessage("bind_param error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$sth->execute()) {
      formatSessionMessage("execute error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   if (!$sth->bind_result($text)) {
      formatSessionMessage("bind_result error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $fetch_status = '';
   if (!$fetch_status = $sth->fetch()) {
      if ($fetch_status === null) {
         formatSessionMessage("No record found.", 'info', $msg);
         setSessionMessage($msg, 'error');
         break;
      } else { 
         formatSessionMessage("fetch error: " . $sth->error, 'info', $msg);
         setSessionMessage($msg, 'error');
         break;
      }
   }
   
   $status = 1;
   break;
}
$sth->close();

do_header('MySuperPicks.com - Update Maintenance Message');
do_nav();

echo "    <div class='container'>";
echo_container_breaks();
echoSessionMessage();
$_SESSION['homepage_message'] = $text;
echo "
        <h1 class='text-center'>Update Maintenance Message</h1>
        <br />
<p class='lead muted text-center'>Shown when site is 'down'.  Use login message for general information</p>          
          <form action='adminsitemaintmessage2.php' method='post' class='form' role='form' enctype='multipart/form-data'> 
            <div class='form-group'>
              <textarea class='form-control ckeditor' rows='8' placeholder='New Homepage Text' name='homepage_message'>$text</textarea>
              <br /><button type='submit' class='btn btn-primary'>Edit Maintenance Message</button>
              <input type='hidden' name='submit' value='true' />
            </div>
          </form>
    </div>";
    
unset($_SESSION['homepage_message']);
do_footer('bottom');
?>