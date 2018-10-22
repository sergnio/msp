<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: adminmessage.php
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

do_header('MySuperPicks.com - Update Homepage Text');
do_nav();
?> 
    <div class="container">
<?php 
echo_container_breaks();
echoSessionMessage();

$conn = db_connect();
$result = $conn->query("SELECT * FROM homepage_text WHERE field_name='index'");
$row=$result->fetch_object();
$text = $row->field_text;
$_SESSION['homepage_message'] = $text;
mysqli_close($conn);  
?>
        <h1 class="text-center">Edit No User/No League Homepage Text</h1>
        <br />
<p class="lead muted text-center">Add/edit your text below</p>          
          <form action="adminmessage2.php" method="post" class="form" role="form" enctype="multipart/form-data"> 
            <div class="form-group">
              <textarea class="form-control ckeditor" rows="8" placeholder="New Homepage Text" name="homepage_message"><?php echo $text;  ?></textarea>
              <br /><button type="submit" class="btn btn-primary">Save</button>
              <input type="hidden" name="submit" value="true" />
            </div>
          </form>
<?php 
unset($_SESSION['homepage_message']);
?>
    </div>
<?php
do_footer('bottom');
?>