<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: league_join_options.php
   date: 8/2017
 author: original
   desc: sets the password for the league, the user can come to this page no logged in or logged in or redirected to after login
      
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

$leagueID_POST = isset($_POST['joinleague_leagueid']) ? $_POST['joinleague_leagueid'] : '';
$leaguePassword_POST = isset($_POST['joinleague_password']) ? $_POST['joinleague_password'] : '';

$loginMessage = isset($_GET['login']) ? $_GET['login'] : '';

if($loginMessage!='fail')
{

	$successMsg = '';
	
	$league_id_to_add = '';
	
	if(!empty($leagueID_POST) && !empty($leaguePassword_POST))
		$league_id_to_add = $leagueID_POST;
	elseif(isset($_SESSION['join_league_id']))
		$league_id_to_add = $_SESSION['join_league_id'];
	
	
	if(!empty($league_id_to_add))
	{
		//get the password and compare
		getLeaguePassword($league_id_to_add, $password_enabled_res, $password_res);
		if($password_enabled_res && ($password_res === $leaguePassword_POST || isset($league_id_to_add)))
		{
			
			//this is a match continue on
			$usr_is_logged_in = validateUser('user', 'status');
			if(!$usr_is_logged_in)
			{
				$_SESSION['join_league_id'] = $league_id_to_add;
				//need the user to login, or create a user
			}
			else
			{
				$user_id = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
				$lname = (!empty($_SESSION['lname'])) ? $_SESSION['lname'] : '';
				$fname = (!empty($_SESSION['fname'])) ? $_SESSION['fname'] : '';
				
				//already logged in, add the user to the league
				addLeagueMembershipToUser($user_id, $league_id_to_add);
				
				insertNewPlayer($league_id_to_add, $user_id, "$fname $lname");
				
				setSessionActiveLeague($user_id);
				
				//set message, allow to continue on
				$successMsg = "You have been added to the league : $league_id_to_add";
				
				formatSessionMessage($successMsg, 'success', $msg, ''); //$msg is the return reference
	        	setSessionMessage($msg, 'happy');
				
				header('Location: /');
	  			die();
			}
		}
		else 
		{
			formatSessionMessage("The league id and password are not valid.", 'danger', $msg); //$msg is the return reference
	        setSessionMessage($msg, 'login');
			header('Location: /', true, 303);
	  		die();
		}
	}
	else
	{
		header('Location: /', true, 303);
	  	die();
	}
}

do_header('MySuperPicks.com - Join a league');
do_nav();
?>

<div class="container">
<?php echo_container_breaks();
echoSessionMessage();
?>

	<div class="panel-heading">
		<h3 class="panel-title text-center">Log In to Join League with Current Account</h3>
	</div>
	<div class="panel-body text-center">
		<form method="post" action="login.php" class="form-inline">
			<div class="form-group">
				<input type="hidden" name="login_request_made" value="1" >
				<input type="text" class="form-control" name="username" placeholder="Username">
			</div>
			<div class="form-group">
				<input type="password" class="form-control" name="password" placeholder="Password">
			</div>
			<input type="hidden" name="url" value="/league_join_verify.php" />
			<input type="hidden" name="keep" value="true" />
			<button type="submit" class="btn btn-default">
				Log In <span class="glyphicon glyphicon-log-in"></span>
			</button>
		</form>
	</div>
	<h4 class="text-center">OR</h4>
	<br />
	<div class="text-center">
		<a class="btn btn-success" href="league_join_register.php?id=new" role="button">Create New Account <span class="glyphicon glyphicon-arrow-right"></span></a>
	</div>

</div>

<?php
do_footer('bottom');
?>



