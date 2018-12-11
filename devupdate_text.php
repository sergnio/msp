<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: devupdate_text.php 
   date: july-2016
 author: originall
   desc: File is accessed thru the league type page standings_switch.php.
   This is the 'Pickum' standings - the page with two tables - weekly results
   and to-date season results.  It's all callback driven.  See mypicks01.js for the
   js code.
marbles: 
   note:
*/

require_once('mypicks_def.php');
require_once('site_fns_diminished.php');
$msg = '';

validateUser('admin');
$text = "
   <table class='table'>
   <thead>
      <tr>
         <th style='text-align:center;'>date</th>
         <th style='text-align:center;'>text</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>date</td>
         <td>
         - <br /><br />
         </td>
      </tr>
      <tr>
         <td>date</td>
         <td>
         - <br /><br />
         </td>
      </tr>
      <tr>
         <td>2016-08-14</td>
         <td>
         - Went thru all the files and insured '\$msg' was defined.  There shouldn't be any of those redirection problems caused by that missing variable.<br /><br />
         - Fixed duplicate league names - can't do it now.<br /><br />
         - KO last man is worth a look now.  There are so many scenarios to consider when calculating the winner!<br /><br />
         </td>
      </tr>
      <tr>
         <td>2016-08-11</td>
         <td>
         - database wiped  Leagues, players, picks, messages/comments, login history, users have been deleted or trimmed.  Remaining users are hugh, matt, aleisen.  
         Tables schedules, nsp_admin remain unchanged.<br /><br />
         - Code at mysuperpicks.com was backed and the new code installed.  It seems to be running..<br /><br />
         - Issue may arise at mysuperpicks.com.   Here are some that concern me. <br />
         --- mysuperpicks.com does not use the same mysql server that drillbrain does.<br />
         --- The _SERVER vars are different.  I had to rewrite site recognition code to accommodate it.<br />
         --- Redirection may fail - same reason - the _SERVER vars are different<br />
         --- I don't know how the mail is setup here.  That should be exercised.<br />
         --- Login timeouts differ and perhaps server configurations.  I assume the cookies will validate for 30 days, but I don't know.<br />
         --- SO, <b>please do all you testing at the mysuperpicks.com site.</b><br /><br />
         - <br /><br />
         </td>
      </tr>
      <tr>
         <td>2016-08-09</td>
         <td> - <pre>alter table users add column lastleagueselectedid int default 0</pre> godaddy updated  Players last selected league
         (selected via <b>->Member Area, ->My Leagues</b>.  Is now the login default league.<br /><br />
         - Better email record. alter table sent_email add column body text;
alter table sent_email add column senddate   datetime DEFAULT '2016-07-11 12:00:00';
alter table sent_email add column signature  char(64) not null default 'no signature applied';
alter table sent_email add column userid     int not null default 0;
alter table sent_email add column concerning char(64) not null default 'none';
alter table sent_email add column status char(25)  (Dev: not implemented)<br /><br />
         </td>
      </tr>
      <tr>
         <td>2016-08-07</td>
         <td>
         - Fix KO - last man standing <br /><br />
         - A registered user would fail joining a league if he reused his playername.  Fixed.  Removed unique index on playername.  The
         unique key is UNIQUE KEY `leagueid` (`leagueid`,`playername`)  (dev note: applied at godaddy <pre> ALTER TABLE nspx_leagueplayer
  DROP INDEX playername)</pre><br />
  This probably fixed the second image of 'Finally, I've included two more screenshots of errors we encountered:', with 'The system failed to install new user'.
  I installed better monitoring on this new user function.  If it fails again I'll know why. <br /><br />
         </td>
      </tr>
      <tr>
         <td>2016-08-01</td>
         <td>
         - (Finding)(Player #1)+ Standings page does not render on entry.  Fixed.<br /><br />
         </td>
      </tr>
      <tr>
         <td>2016-07-31</td>
         <td>
         - Continue with below fixes.  See <b>TODO</b> for testing requests.  Don't have time to pound them.<br /><br />
         
         - copied drillbrain database and will work the calc problems locally.  Feel free to change any thing you want.
            I have a 'snap shot'  The database port differ between drillbrain and my box.  I'm finding, for what ever reason,
            results differ.<br /><br />
            
            </td>
      </tr>
      <tr>
         <td>2016-07-30</td>
         <td>
         - (Finding)(Admin #1)+ Active week - must logout/login. Fixed - Refresh and page to page travel will now always 
         check for the current active week and update it.  Function was placed in validateUser().  Don't feel to good about doing this.<br /><br />
         
         - (Finding)(Admin #2)+ Game score of zero doesn't register.  Fixed - the NULL, zero, empty thing again.<br /><br />
         
         - (Finding)(Player #1)+ Standings page does not render on entry.  I have to figure out Boostrap event chaining.  
         I haven't been able to trigger the week.  Same with the login in drop down in the main menu.  
         I haven't been able to figure out how to move focus into the user name field.  Fix <br /><br />
         
         - (Finding)(Player #2)+ This Weeks Lines - a error pop-up.  This is the Nature of the ajax callback.  They fail sometimes but 
         not like you describe.  I'll investigate.  Didn't see anything in the error logs. step 1.installed logging on call back support always.
         (Dev errors are error messages returned from the server and not shown.  Format and show)  Fixed.<br /><br />    
         
         - (Finding)(Player #3)+ The text at the top should indicate how many games they need to pick.  Fixed - See: Please pick 5 teams to win'<br /><br />    
         
         - (Finding)(Player #4)+ Don't show spreads for teams that don't use them.  Fixed<br /><br />
         
         - (Finding)(Player #5)+ There must be mail confirmation Fixing.  A verify mail request button has been installed on the lines pages.<br />
         <b style='color:red;'>TODO</b> Please test at drillbrain.  Let me know.<br /><br />
         
         - (Finding)(Player #6)+ Players are unable to change their password.  Fixed - mess - rewrite <br /><br />
         
         - (Finding)(League #1)+ Where is all games? Fixed - reinstalled the all teams option.<br /><br />
         
         - (Finding)(League #2)+ One time login allowed when registering. (Dev allow how many?) Fixed.  There is no limit on attempts.  
         Login attempts will move to the general login monitor when it is installed.<br /><br />
         
         - (Finding)(League #3)+ When successfully joining league receive serious error Fixed.  (Dev note: message correct, format is wrong) <br /><br />
         
         - (Finding)(Cal #1)+ Inconsistent formating/scoring (BAL & OAK wk 4 TEST 2) <br /><br />
         
         - (Finding)(Cal #2)+ Player able to choose team twice. Yes, he did! Fixed.  Double click.  
         After first click all buttons are now disabled until the process completes.  Changing for async to sync process. 
         (<b>DEV TODO</b> - just to be sure, install checking code on server side support) <br /><br />
         
         - (Finding)(Cal #3)+ same as #1 with different expression. <br /><br />
         
         - (Finding)(Image)- inu2 <br /><br />
         
         - (Finding)(Image)- Standings - Pickem, alert 'An error occured...' (buttons, no labels) (spelling occured corrected ... lots of places)<br /><br />
         
         </td>
      </tr>
      <tr>
         <td>2016-07-29</td>
         <td> - Session message reference control installed.<br />
         - League level league edit - first pass done  TODO reinitialize the league session variables.<br />
         - References will always be on for admin types<br />
         - Fix MyPicks db error - conn no def
         </td>
      </tr>
      <tr>
         <td>2016-07-26</td>
         <td>- Installed administrator's login message facility in operations (SLMSHOW and SLM)<br />
         
         </td>
      </tr>
      <tr>
         <td>2016-07-25 7:30pm</td>
         <td>-  Fixed the league invite mailer.  Here are the errors received:<br />
         <pre>
         Notice: Undefined variable: site_admin in /home/content/32/8772532/html/nflbrain/mypicks_db.php on line 255
         Notice: Undefined variable: site_admin in /home/content/32/8772532/html/nflbrain/mypicks_db.php on line 295
         Warning: Cannot modify header information - headers already sent by (output started at 
            /home/content/32/8772532/html/nflbrain/mypicks_db.php:255)
            in /home/content/32/8772532/html/nflbrain/adminregister2.php on line 175
         </pre>
         These were variable naming errors in new support functions getLinkContact() and getLinkConfirm()<br /><br />
         -  Modified database tables:
         <pre>
         ALTER TABLE league
            DROP COLUMN active_week;

         ALTER TABLE users
           DROP COLUMN win,
           DROP COLUMN lose,
           DROP COLUMN push,
           DROP COLUMN win_week,
           DROP COLUMN lose_week,
           DROP COLUMN push_week,
           DROP COLUMN total_points,
           DROP COLUMN total_points_week;
           
         alter table users
            add created                               timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            add salt                                  char(64) NOT NULL default '54c2932a2036332b01c67d9d65f5ee86',  
            add passwordchangerequired                int not null default 0, 
            add passwordchangerequiredcountdown       int not null default 10,
            add dateoflastpasswordchange              timestamp not null default '2016-07-25 21:00:00',
            add loginattemptcount                     int not null default 10,
            add loginlockouttime                      int not null default 5,
            add lastloginattempt                      timestamp not null default '2016-07-25 21:00:00',
            add lastlogin                             timestamp not null default '2016-07-25 21:00:00', 
            add specialstatus                         char(15) not null default 'normal';      
        </pre>
        Code was modified mainly in mypicks_db.php  drillbrain has been updated<br />
        -
         </td>
      </tr>
      <tr>
         <td>2016-07-25 12:30pm</td>
         <td>-  Pickum to Pickem...  various<br />
         -  Finding more than 5 picks in Orignial is a development artifact.  No problem here.  And <b>no one should be using old data</b>. 
         All users,admins,commissioners should be new as of the start of testing.  I think Matt will start on Thrs.  <b>I need to be informed when this
         testing begins.</b>  It will change my update behavior.<br />
         -  Pickem standings page.  Added legend.  Fixed points and push - they were fixed at points used and push at .5 points.<br />
         -  Pickem standings page.  Hide picks until game start OR the admin scores the match.  As discussed, admin, and maybe the commissioner, 
         have additional rights and could view this data.<br />
         -  Disabled schedule loading on drillbrain<br />
         -  !!! Couldn't duplicate contact/invite page error seen at the library.  Please send a screen shot.<br />
         -  New Password to just Password<br />
         -  The toggle error in 'This Week's Lines' is correct.  Week 7 has 6 picks.  The condition is a 'serious' error.  There is a question now
         as to how it should be handled.  Here's the message <b> 'Info!  There has been a system error. Please contact the site administrator.(ref p>limit 6)'</b><br />
         -  The drillbrain error logging has been enabled.
         
         </td>
      </tr>
      <tr>
         <td>date</td>
         <td>- dev notes <br /><pre> 
KO cohort behavior

process_week == the current week being processed - checked for winners and losers
next week == the week directly following the process_week
lastround == commission defined value marking end of play.  The game can be ended at any time.
hard_end == completes play as in, this is the final round of play.  Winners are indicated
   (if any - can have no winners in first round)

   hard_end == (process_week == lastround) || (process_week == last week of NFL season)

For the purpose of process_week calculations, next week data is considered only if next week 
   games (any game) are not pending.

# All loose first week  (next week - don't care)
- game is over
- addition activity ignored
- there are no winners

# One player wins (next week - don't care)
- game is over
- picks are marked
- winner is marked
- additional activity is ignored

#a >1 player wins (next week - unavailable)
- game continues
- picks are marked
- there are no winners

#b >1 player wins (next week - available - no winners)
- game is over
- picks are marked
- winners are marked

#c >1 player wins (next week - available - there are winners)
- game continues
- picks are marked
- there are no winners

Special conditions.

Any player failing to pick forever forfeits.   He will not be included in further play.

Access to play is denied until the defined first week of play is >= the active week. 
   The active week is set by the site admin.

To play: Player must be active && user account must be active && league must be 
   active && finalround must be ZERO

Player picks are not shown in standings until the game begins (unless scored)

Scoring always exposes team names.  Scored games are not pending.
</pre>



         </td>
      </tr>
   </tbody>
   </table>";
do_header('MySuperPicks.com - Dev Update Text');
do_nav();

?>
<div class="container">
<?php 
echo_container_breaks();
echoSessionMessage();
?>
   <div class="hidden-sm hidden-md hidden-lg">
      <br />
      <br />
      <br />
   </div>
<?php
echo $text;
do_footer('clean');
?>

