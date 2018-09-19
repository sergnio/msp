<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: league_join_options.php
   date: 8/2017
 author: original
   desc: sets the password for the leauge
      
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

validateUser('commissioner');
$league_id = (!empty($_SESSION['league_id'])) ? $_SESSION['league_id'] : '';
$password_enabled = FALSE;
$league_password = '';
$showSuccess = FALSE;
	
if(isPostBack()){
	//we are posting to the same page, process the values
	
	$leaguePassword_POST = $_POST['leaguePassword'];
	$leaguePasswordEnabled_POST = $_POST['enableJoiningWithPassword'];
	
	$passwordEnabledFlag = 0;
	if(isset($leaguePasswordEnabled_POST) && $leaguePasswordEnabled_POST == 'on')
		$passwordEnabledFlag = 1;
	
	updateLeaguePassword($league_id, $passwordEnabledFlag, $leaguePassword_POST);
	
	$password_enabled = $passwordEnabledFlag;
	$league_password = $leaguePassword_POST;
	$showSuccess = TRUE;
}
else
{
	getLeaguePassword($league_id, $password_enabled_get, $league_password_get);
	if(isset($password_enabled_get))
		$password_enabled = $password_enabled_get ? true : false;
	if(isset($league_password_get))
		$league_password = $league_password_get;
}

do_header('MySuperPicks.com - Join Options');
do_nav();
?> 
   <div class="container">
<?php
echoContainerBreaks();
echoSessionMessage();

$league_name = (!empty($_SESSION['league_admin'])) ? getLeagueName($_SESSION['league_admin']) : '';
?>
	<?php if($showSuccess){?>
		<div class="alert alert-success" role="alert">Join options have been updated.</div>
	<?php } ?>
      <br />
      <h3>League Join Options for <i><?php echo $league_name; ?></i></h3>
      <h5>Allow users to join the league using the League ID and Password.</h5>
      <br />
      <form class="" action="league_join_options.php" method="post">
		<div class="checkbox">
			<label>
				<input type="checkbox" name="enableJoiningWithPassword" value="on" <?php echo ($password_enabled==1 ? 'checked' : '');?>>
				Enable users to register using league password
			</label>
		</div>

		<div class="form-group">
			<label class="control-label">League ID</label>
			<?php echo $league_id; ?>
		</div>
		<div class="form-group">
			<label for="inputPassword3" class="control-label">League Password</label>
			<input type="text" class="form-control" id="leaguePassword" name="leaguePassword" placeholder="Password" maxlength="20" value="<?php echo $league_password; ?>">
		</div>
			<button class="btn btn-primary" type="submit">Save</button>
		
	
      <br />
      <div id='IDd_ajaxmessageshere' style='text-align:center;'></div>
	 </form>
   </div>
<?php
do_footer('clean');
?>