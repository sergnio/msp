<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: edithtml.php
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


//must be admin
validateUser('admin');

//get the html key to edit
$htmlKey = isset($_GET['key']) ? $_GET['key'] : '';

if (empty($htmlKey))
{
	header('Location: /');
	die();
}


if(isPostBack()) {
	//posting back the text
	
	$htmlContent = isset($_POST['htmlcontent']) ? $_POST['htmlcontent'] : '';
	
	if(!empty($htmlContent))
	{
		$res = updateHomepageText($htmlKey, $htmlContent, $ref_status_text);
		
	   if (!$res) {
	      formatSessionMessage("error updating HTML" . $ref_status_text, 'info', $msg);
	      setSessionMessage($msg, 'error');
	   }
	   else {
		  formatSessionMessage("Updated HTML successfully.", 'success', $msg);
	      setSessionMessage($msg, 'happy');
	   }
	}
}

	
	$mysql = "SELECT field_text 
	     FROM homepage_text 
	    WHERE field_name = ?"; 
	
	$status = 0;
	$text = '';
	while (1) {
	   
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
	   if (!$sth->bind_param("s", $htmlKey)) {
	      formatSessionMessage("bind_param error: " . $sth->error, 'info', $msg);
	      setSessionMessage($msg, 'error');
	      break;
	   }
	   if (!$sth->execute()) {
	      formatSessionMessage("execute error: " . $sth->error, 'info', $msg);
	      setSessionMessage($msg, 'error');
	      break;
	   }
	   $sth->store_result();
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


do_header('MySuperPicks.com - Edit ' . $htmlKey);
do_nav();

echo "    <div class='container'>";
echo_container_breaks();
echoSessionMessage();
$_SESSION['homepage_message'] = $text;
echo "
        <h1 class='text-center'>Edit the HTML for $htmlKey</h1>
        <br />
<p class='lead muted text-center'>Edit the HTML</p>          
          <form action='edithtml.php?key=$htmlKey' method='post' class='form' role='form' enctype='multipart/form-data'> 
            <div class='form-group'>
              <textarea class='form-control ckeditor' rows='8' placeholder='New Homepage Text' name='htmlcontent'>$text</textarea>
              <br /><button type='submit' class='btn btn-primary'>Save</button>
              <input type='hidden' name='submit' value='true' />
            </div>
          </form>
    </div>";
    
unset($_SESSION['homepage_message']);
do_footer('bottom');
?>