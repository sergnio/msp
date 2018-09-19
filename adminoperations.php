<?php
require_once 'mypicks_startsession.php';
//zz
/*
:mode=php:

   file: adminoperations.php
   date: apr-2016
 author: original
   desc: 
marbles: 

*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

validateUser('admin');

$site                             = '';
$analytics                        = '';
$developmentmode                  = '';
$writelog                         = '';
$writelogfilespec                 = '';
$emailnoreply                     = '';
$emailsiteadmin                   = '';
$emailtositecontact               = '';
$emailfromsitecontact             = '';
$emailsitelimit_longterm          = '';
$emailsitelimit_shortterm         = '';
$emailsitelimit_longtermcount     = '';
$emailsitelimit_shorttermcount    = '';
$emailsitelimit_longtermbasetime  = '';
$emailsitelimit_shorttermbasetime = '';
$linkconfirm                      = '';
$linkcontact                      = '';
$passwordadmin2                   = '';
$passwordadmin2hint               = '';
$passwordadmin2question           = '';
$siteactive                       = '';
$sitemaintenancemessage           = '';
$sitemaintenancemessagedefault    = '';
$siteloginmessage                 = '';
$siteloginmessageshow             = '';
$timezonedefault                  = '';
$loginattemptsactive              = '';
$loginattemptsallowed             = '';
$loginlockouttimeminutes          = '';
$devemailnoreply                  = '';
$devemailsiteadmin                = '';
$devemailtositecontact            = '';
$devemailfromsitecontact          = '';
$errormailto                      = '';
$errormaillimit                   = '';
$deverrormailcount                = '';

$sessionmessagereferencemode       = '';
$playnopaygracedays                = '';
$playnopaygracemode                = '';

$table = '';
$status = 0;
$msg = '';
while (1) {
   
   $mysql = "
      select site                            ,
             analytics                       ,
             developmentmode                 ,
             writelog                        ,
             writelogfilespec                ,
             emailnoreply                    ,
             emailsiteadmin                  ,
             emailtositecontact              ,
             emailfromsitecontact            ,
             emailsitelimit_longterm         ,
             emailsitelimit_shortterm        ,
             emailsitelimit_longtermcount    ,
             emailsitelimit_shorttermcount   ,
             emailsitelimit_longtermbasetime ,
             emailsitelimit_shorttermbasetime,
             linkconfirm                     ,
             linkcontact                     ,
             passwordadmin2                  ,
             passwordadmin2hint              ,
             passwordadmin2question          ,
             siteactive                      ,
             sitemaintenancemessage          ,
             sitemaintenancemessagedefault   ,
             siteloginmessage                ,
             siteloginmessageshow            ,
             timezonedefault                 ,
             loginattemptsactive             ,
             loginattemptsallowed            ,
             loginlockouttimeminutes         ,
             devemailnoreply                 ,
             devemailsiteadmin               ,
             devemailtositecontact           ,
             devemailfromsitecontact         ,
             deverrormailto                  ,
             deverrormaillimit               ,
             deverrormailcount               ,
             sessionmessagereferencemode     ,
             playnopaygracedays              ,
             playnopaygracemode              
        from nsp_admin
       where site = ?";
       
   $conn = db_connect();
   if (!$conn) {
      formatSessionMessage("The database is not available.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $sth = $conn->prepare($mysql);
   $site_name = ADMIN_TABLE;
   $sth->bind_param("s", $site_name);
   if (!$sth->execute()) {
      formatSessionMessage("The query failed to execute.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   $sth->bind_result($site                                 ,   
                     $analytics                            ,   
                     $developmentmode                      ,   
                     $writelog                             ,   
                     $writelogfilespec                     ,   
                     $emailnoreply                         ,   
                     $emailsiteadmin                       ,   
                     $emailtositecontact                   ,   
                     $emailfromsitecontact                 ,   
                     $emailsitelimit_longterm              ,   
                     $emailsitelimit_shortterm             ,   
                     $emailsitelimit_longtermcount         ,   
                     $emailsitelimit_shorttermcount        ,   
                     $emailsitelimit_longtermbasetime      ,   
                     $emailsitelimit_shorttermbasetime     ,   
                     $linkconfirm                          ,   
                     $linkcontact                          ,   
                     $passwordadmin2                       ,   
                     $passwordadmin2hint                   ,   
                     $passwordadmin2question               ,   
                     $siteactive                           ,   
                     $sitemaintenancemessage               ,   
                     $sitemaintenancemessagedefault        ,   
                     $siteloginmessage                     ,   
                     $siteloginmessageshow                 ,   
                     $timezonedefault                      ,   
                     $loginattemptsactive                  ,   
                     $loginattemptsallowed                 ,   
                     $loginlockouttimeminutes              ,
                     $devemailnoreply                      ,
                     $devemailsiteadmin                    ,
                     $devemailtositecontact                ,
                     $devemailfromsitecontact              ,
                     $deverrormailto                       ,
                     $deverrormaillimit                    ,
                     $deverrormailcount                    ,
                     $sessionmessagereferencemode          ,
                     $playnopaygracedays                   ,
                     $playnopaygracemode);        
   // new coming:
   // sessionmessagereferencemode
   
   if (!$sth->fetch()) {
      formatSessionMessage("No record was found for site '$site_name'.", 'danger', $msg);
      setSessionMessage($msg, 'error');
      break;
   }

   $table = "
<table id='single' class='table table-hover table-striped table-bordered'>\n
   <thead>
      <tr>
         <th style='text-align:center;' >Parameter</th>\n
         <th style='text-align:center;width:40%;' >Value</th>\n
         <th style='text-align:center' >Description</th>\n
      </tr>
   </thead>
   <tbody>
   <tr>
      <td style='text-align:right' ><b>*SN</b><br />site name</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='site' disabled='disabled' value='$site' />
         <input style='width:100%;' type='hidden' name='sitehidden' value='$site' /></td>
      <td>The key to this site specific record.  At the present time there are three optional values, 'nflx', 'nflbrain' and 'mysuperpicks'
         The name shown is the name assigned to your current site.  The name is determined by site recognition code in mypicks_def.php.
         The constant tested is ADMIN_TABLE</td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>ANA</b><br />analytics mode</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='analytics' value='$analytics' /></td>
      <td>Enable or disable analytics reporting.<br /><b>[1,2] = [inactive, active]</b></td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>*SMREF</b><br />session message reference mode</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='sessionmessagereferencemode' value='$sessionmessagereferencemode' /></td>
      <td>When active, additional technical information is included in the usual user prompts.  
      This is usually in the form (ref: data).  References are always shown for administrators.<br /><b>[1,2] = [inactive,active]</b></td> 
   </tr>   
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>DEV</b><br />development mode</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='developmentmode' value='$developmentmode' /></td>
      <td>Changes site behavior.  Should be 1 in production.<br /><b>[1,2] = [inactive, active]</b></td> 
   </tr>   
   <tr>
      <td style='text-align:right' ><b>*ECTO</b><br />email to site contact</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailtositecontact' value='$emailtositecontact' /></td>
      <td>The contact address use by the form 'Contact Us'.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>*ECFROM</b><br />email contact from</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailfromsitecontact' value='$emailfromsitecontact' /></td>
      <td>The contact from address use by the form 'Contact Us'.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>*ENR</b><br />email no reply</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailnoreply' value='$emailnoreply' /></td>
      <td>The 'no reply' email address place holder.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>ESA</b><br />email site admin</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailsiteadmin' value='$emailsiteadmin' /></td>
      <td>The site administrator's address.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>ELONG</b><br />email site long term limit [+int]</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailsitelimitlongterm' value='$emailsitelimit_longterm' /></td>
      <td>Set the limit to how many site emails can initiated within the long term.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>ECLONG</b><br />email site long term count [+int]</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailsitelimitlongtermcount' value='$emailsitelimit_longtermcount' /></td>
      <td>The current long term mailing count</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>ESHORT</b><br />email site short term limit [+int]</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailsitelimitshortterm' value='$emailsitelimit_shortterm' /></td>
      <td>Set the limit to how many site emails can initiated within the short term.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>ECSHORT</b><br />email site short term count [+int]</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='emailsitelimitshorttermcount' value='$emailsitelimit_shorttermcount' /></td>
      <td>The current short term mailing count</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>*LFIRM</b><br />link confirm</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='linkconfirm' value='$linkconfirm' /></td>
      <td>The Admins's invite email confirmation link.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>*LCON</b><br />link contact</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='linkcontact' value='$linkcontact' /></td>
      <td>The 'hot link' to the 'Contact Us' page.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>FSPEC</b><br />logging filespec</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='writelogfilespec' value='$writelogfilespec' /></td>
      <td>The logging filespec.  It is relative and should begin './'. </td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>GRACE</b><br />payment grace mode</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='playnopaygracemode' value='$playnopaygracemode' /></td>
      <td>If active a grace period is allowed before player access is revoked.  The period is measured from his league join date.<br /><b>[1,2] = [inactive,active]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>GRACEP</b><br />payment grace period</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='playnopaygracedays' value='$playnopaygracedays' /></td>
      <td>The number of days play is allowed without payment.  Based on league join date.<br /><b>int > 0 (unit days)</b></td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>LOG</b><br />logging mode</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='writelog' value='$writelog' /></td>
      <td>Turn the logging on or off. This is not the godaddy error facility which must be accesses via the panel. (dev note: global_write_roach_file)<br /><b>[1,2] = [inactive, active]</b></td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>LDOG</b><br />login watchdog mode</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='loginattemptsactive' value='$loginattemptsactive' /></td>
      <td>If the watchdog is active, failed serial login attempts are limited.  This function is both time and count based.  A lockout period occurs if this limit is exceeded.<br /><b>[1,2] = [inactive, active]</b> </td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>LDOGL</b><br />login attempts limit</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='loginattemptsallowed' value='$loginattemptsallowed' /></td>
      <td>How many login attempts are allowed before a login lockout.  The counting occurs in users.loginattemptcount <br /><b>int > 0 (countdown)</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>LLO</b><br />login lockout time</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='loginlockouttimeminutes' value='$loginlockouttimeminutes' /></td>
      <td>If the user suffers a login lockout, he must wait LLO minutes until trying again.<br /><b>int > 0 (unit minutes)</b></td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>**SACT</b><br />site active</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='siteactive' value='$siteactive' /></td>
      <td>This is the website 'up/down' control.  In production it should be set to 2.  During maintenance it should be set to 1.  Administrators are always allowed access.
         This does not redirect the home page, it just fails out the login.<br /><b>[1,2] = [inactive,active]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>SMM</b><br />site maintenance message</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='sitemaintenancemessage' value='$sitemaintenancemessage' /></td>
      <td>If the site is down, this message will appear on the home page.  If this message empty, the default maintenance will be shown instead.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>*SLM</b><br />site login message</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='siteloginmessage' value='$siteloginmessage' /></td>
      <td>Upon a successful login, this message will be shown on the home page.  The message displayed if the 'show' control is true.  This message is in addition to the standard splash.</td>
   </tr>
   <tr>
      <td style='text-align:right;background-color:powderblue;' ><b>*SLMSHOW</b><br />site login message show</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='siteloginmessageshow' value='$siteloginmessageshow' /></td>
      <td>Upon successful login, show the login message.<br /><b>[1,2]=[hide,show]</b></td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>SMMDFLT</b><br />site maintenance message default</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='sitemaintenancemessagedefault' value='$sitemaintenancemessagedefault' /></td>
      <td>This is the default maintenance message. It will show only if the formal maintenance is empty.</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>TZ</b><br />server database timezone</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='timezonedefault' value='$timezonedefault' /></td>
      <td>The godaddy default TZ is pacific.  I don't think this field will be use.  All time calculations are done on the database server.</td>
   </tr>
   
   <!-- =========================================================================== dev mode stuff =========================== -->
   
   <tr>
      <td style='text-align:right' ><b>DEVENR</b><br />Dev mode: no-reply address</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='devemailnoreply' value='$devemailnoreply' /></td>
      <td>dev mode mail address</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>DEVEADMIN</b><br />Dev mode: admin address</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='devemailsiteadmin' value='$devemailsiteadmin' /></td>
      <td>dev mode mail address</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>DEVECON</b><br />Dev mode: contact to address </td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='devemailtositecontact' value='$devemailtositecontact' /></td>
      <td>dev mode mail address</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>DEVECFROM</b><br />Dev mode: contact from address </td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='devemailfromsitecontact' value='$devemailfromsitecontact' /></td>
      <td>dev mode mail address</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>DEVEE</b><br />Dev mode: error mail address</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='deverrormailto' value='$deverrormailto' /></td>
      <td>dev mode mail address</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>DEVEEL</b><br />Dev mode: error mail limit</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='deverrormaillimit' value='$deverrormaillimit' /></td>
      <td>dev mode error mail limit</td>
   </tr>
   <tr>
      <td style='text-align:right' ><b>DEVEEC</b><br />Dev mode: error mail count</td>
      <td style='text-align:center;'><input style='width:100%;' type='text' name='deverrormailcount' value='$deverrormailcount' /></td>
      <td>dev mode error mail current count</td>
   </tr>
   </tbody>
</table>";

   $status = 1;
   break;
}


do_header('MySuperPicks.com - Admin Ops');
do_nav();
 ?> 
    <div class="container">
<?php echo_container_breaks();
echoSessionMessage();?>
<div class="hidden-sm hidden-md hidden-lg">
    <br />
    <br />
    <br />
</div>
        <h1 class='text-center'>Site Operations (table nsp_admin)</h1>
        <h5  class='text-center'>Not all values are checked.  Know what you're doing.</h5>
        <h5  class='text-center'>Don't use HTML special characters in text fields.</h5>
        <h5  class='text-center'><b>*</b> implemented</h5>
        <h5  class='text-center'><b>**</b> kinda implemented</h5>
<?php
if ($status) {
   echo "
        <form action='adminoperations2.php' method='post' class='form-horizontal' role='form' enctype='multipart/form-data'> 
            $table
         <span class='input-group-btn'>
            <button type='submit' class='btn btn-success'>Submit Edits</button>
         </span>
        </form>
        ";
} else {
   echo "No data available";
}
?>
 
<br />
<br />

<?php
do_footer('clean'); 
?>