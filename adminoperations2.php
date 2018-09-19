<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: adminoperations2.php
   date: jul-2016
 author: original
   desc: 
      URL_HOME_PAGE

   note:
   
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';
require_once 'mypicks_once.php';

$msg = '';
if (0) {
   formatSessionMessage("The edit operations facility is not yet implemented.", 'info', $msg);
   setSessionMessage($msg, 'error');
   header("Location: adminoperations.php");
   die();
}

validateUser('admin');

$site                             = (isset($_POST['sitehidden'                       ])) ? $_POST['sitehidden'                      ] : '';
$analytics                        = (isset($_POST['analytics'                        ])) ? $_POST['analytics'                       ] : '';
$developmentmode                  = (isset($_POST['developmentmode'                  ])) ? $_POST['developmentmode'                 ] : '';
$writelog                         = (isset($_POST['writelog'                         ])) ? $_POST['writelog'                        ] : '';
$writelogfilespec                 = (isset($_POST['writelogfilespec'                 ])) ? $_POST['writelogfilespec'                ] : '';
$emailnoreply                     = (isset($_POST['emailnoreply'                     ])) ? $_POST['emailnoreply'                    ] : '';
$emailsiteadmin                   = (isset($_POST['emailsiteadmin'                   ])) ? $_POST['emailsiteadmin'                  ] : '';
$emailtositecontact               = (isset($_POST['emailtositecontact'               ])) ? $_POST['emailtositecontact'              ] : '';
$emailfromsitecontact             = (isset($_POST['emailfromsitecontact'             ])) ? $_POST['emailfromsitecontact'            ] : '';
$emailsitelimit_longterm          = (isset($_POST['emailsitelimitlongterm'           ])) ? $_POST['emailsitelimitlongterm'          ] : '';
$emailsitelimit_shortterm         = (isset($_POST['emailsitelimitshortterm'          ])) ? $_POST['emailsitelimitshortterm'         ] : '';
$emailsitelimit_longtermcount     = (isset($_POST['emailsitelimitlongtermcount'      ])) ? $_POST['emailsitelimitlongtermcount'     ] : '';
$emailsitelimit_shorttermcount    = (isset($_POST['emailsitelimitshorttermcount'     ])) ? $_POST['emailsitelimitshorttermcount'    ] : '';
$emailsitelimit_longtermbasetime  = (isset($_POST['emailsitelimitlongtermbasetime'   ])) ? $_POST['emailsitelimitlongtermbasetime'  ] : '';
$emailsitelimit_shorttermbasetime = (isset($_POST['emailsitelimitshorttermbasetime'  ])) ? $_POST['emailsitelimitshorttermbasetime' ] : '';
$linkconfirm                      = (isset($_POST['linkconfirm'                      ])) ? $_POST['linkconfirm'                     ] : '';
$linkcontact                      = (isset($_POST['linkcontact'                      ])) ? $_POST['linkcontact'                     ] : '';
$passwordadmin2                   = (isset($_POST['passwordadmin2'                   ])) ? $_POST['passwordadmin2'                  ] : '';
$passwordadmin2hint               = (isset($_POST['passwordadmin2hint'               ])) ? $_POST['passwordadmin2hint'              ] : '';
$passwordadmin2question           = (isset($_POST['passwordadmin2question'           ])) ? $_POST['passwordadmin2question'          ] : '';
$siteactive                       = (isset($_POST['siteactive'                       ])) ? $_POST['siteactive'                      ] : '';
$sitemaintenancemessage           = (isset($_POST['sitemaintenancemessage'           ])) ? $_POST['sitemaintenancemessage'          ] : '';
$sitemaintenancemessagedefault    = (isset($_POST['sitemaintenancemessagedefault'    ])) ? $_POST['sitemaintenancemessagedefault'   ] : '';
$siteloginmessage                 = (isset($_POST['siteloginmessage'                 ])) ? $_POST['siteloginmessage'                ] : '';
$siteloginmessageshow             = (isset($_POST['siteloginmessageshow'             ])) ? $_POST['siteloginmessageshow'            ] : '';
$timezonedefault                  = (isset($_POST['timezonedefault'                  ])) ? $_POST['timezonedefault'                 ] : '';
$loginattemptsactive              = (isset($_POST['loginattemptsactive'              ])) ? $_POST['loginattemptsactive'             ] : '';
$loginattemptsallowed             = (isset($_POST['loginattemptsallowed'             ])) ? $_POST['loginattemptsallowed'            ] : '';
$loginlockouttimeminutes          = (isset($_POST['loginlockouttimeminutes'          ])) ? $_POST['loginlockouttimeminutes'         ] : '';
$devemailnoreply                  = (isset($_POST['devemailnoreply'                  ])) ? $_POST['devemailnoreply'                 ] : '';
$devemailsiteadmin                = (isset($_POST['devemailsiteadmin'                ])) ? $_POST['devemailsiteadmin'               ] : '';
$devemailtositecontact            = (isset($_POST['devemailtositecontact'            ])) ? $_POST['devemailtositecontact'           ] : '';
$devemailfromsitecontact          = (isset($_POST['devemailfromsitecontact'          ])) ? $_POST['devemailfromsitecontact'         ] : '';
$deverrormailto                   = (isset($_POST['deverrormailto'                   ])) ? $_POST['deverrormailto'                  ] : '';
$deverrormaillimit                = (isset($_POST['deverrormaillimit'                ])) ? $_POST['deverrormaillimit'               ] : '';
$deverrormailcount                = (isset($_POST['deverrormailcount'                ])) ? $_POST['deverrormailcount'               ] : '';    
$sessionmessagereferencemode      = (isset($_POST['sessionmessagereferencemode'      ])) ? $_POST['sessionmessagereferencemode'     ] : ''; 
$playnopaygracedays               = (isset($_POST['playnopaygracedays'               ])) ? $_POST['playnopaygracedays'              ] : '';   
$playnopaygracemode               = (isset($_POST['playnopaygracemode'               ])) ? $_POST['playnopaygracemode'              ] : '';   

if (0) {
echo "
site                             '$site'                                   <br />
analytics                        '$analytics'                              <br />
developmentmode                  '$developmentmode'                        <br />
writelog                         '$writelog'                               <br />
writelogfilespec                 '$writelogfilespec'                       <br />
emailnoreply                     '$emailnoreply'                           <br />
emailsiteadmin                   '$emailsiteadmin'                         <br />
emailtositecontact               '$emailtositecontact'                     <br />
emailfromsitecontact             '$emailfromsitecontact'                   <br />
emailsitelimit_longterm          '$emailsitelimit_longterm'                <br />
emailsitelimit_shortterm         '$emailsitelimit_shortterm'               <br />
emailsitelimit_longtermcount     '$emailsitelimit_longtermcount'           <br />
emailsitelimit_shorttermcount    '$emailsitelimit_shorttermcount'          <br />
emailsitelimit_longtermbasetime  '$emailsitelimit_longtermbasetime'        <br />
emailsitelimit_shorttermbasetime '$emailsitelimit_shorttermbasetime'       <br />
linkconfirm                      '$linkconfirm'                            <br />
linkcontact                      '$linkcontact'                            <br />
passwordadmin2                   '$passwordadmin2'                         <br />
passwordadmin2hint               '$passwordadmin2hint'                     <br />
passwordadmin2question           '$passwordadmin2question'                 <br />
siteactive                       '$siteactive'                             <br />
sitemaintenancemessage           '$sitemaintenancemessage'                 <br />
sitemaintenancemessagedefault    '$sitemaintenancemessagedefault'          <br />
siteloginmessage                 '$siteloginmessage'                       <br />
siteloginmessageshow             '$siteloginmessageshow'                   <br />
timezonedefault                  '$timezonedefault'                        <br />
loginattemptsactive              '$loginattemptsactive'                    <br />
loginattemptsallowed             '$loginattemptsallowed'                   <br />
loginlockouttimeminutes          '$loginlockouttimeminutes'                <br />
devemailnoreply                  '$devemailnoreply'                        <br />
devemailsiteadmin                '$devemailsiteadmin'                      <br />
devemailtositecontact            '$devemailtositecontact'                  <br />
devemailfromsitecontact          '$devemailfromsitecontact'                <br />
deverrormailto                   '$deverrormailto'                         <br />
deverrormaillimit                '$deverrormaillimit'                      <br />
deverrormailcount                '$deverrormailcount'                      <br />
sessionmessagereferencemode      '$sessionmessagereferencemode'            <br />
playnopaygracedays               '$playnopaygracedays'                     <br />
playnopaygracemode               '$playnopaygracemode'                     <br />";
}

$mysql = "
   update nsp_admin
      set analytics                                 = ?,
          developmentmode                           = ?,
          writelog                                  = ?,
          writelogfilespec                          = ?,
          emailnoreply                              = ?,
          emailsiteadmin                            = ?,
          emailtositecontact                        = ?,
          emailfromsitecontact                      = ?,
          emailsitelimit_longterm                   = ?,
          emailsitelimit_shortterm                  = ?,
          emailsitelimit_longtermcount              = ?,
          emailsitelimit_shorttermcount             = ?,
          emailsitelimit_longtermbasetime           = ?,
          emailsitelimit_shorttermbasetime          = ?,
          linkconfirm                               = ?,
          linkcontact                               = ?,
          passwordadmin2                            = ?,
          passwordadmin2hint                        = ?,
          passwordadmin2question                    = ?,
          siteactive                                = ?,
          sitemaintenancemessage                    = ?,
          sitemaintenancemessagedefault             = ?,
          siteloginmessage                          = ?,
          siteloginmessageshow                      = ?,
          timezonedefault                           = ?,
          loginattemptsactive                       = ?,
          loginattemptsallowed                      = ?,
          loginlockouttimeminutes                   = ?,
          devemailnoreply                           = ?,
          devemailsiteadmin                         = ?,
          devemailtositecontact                     = ?,
          devemailfromsitecontact                   = ?,
          deverrormailto                            = ?,
          deverrormaillimit                         = ?,
          deverrormailcount                         = ?,
          sessionmessagereferencemode               = ?,
          playnopaygracedays                        = ?,
          playnopaygracemode                        = ?
    where site = ?";
    
$status = 0;
while (1) {
   
   if (!$site) {
      formatSessionMessage("Missing site information.  Unable to proceed. ($site)", 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   
   if (!local_oneOrTwo('analytics', $analytics)
      | !local_oneOrTwo('developmentmode', $developmentmode)
      | !local_oneOrTwo('siteactive', $siteactive)
      | !local_oneOrTwo('siteloginmessageshow', $siteloginmessageshow)
      | !local_oneOrTwo('loginattemptsactive', $loginattemptsactive)
      | !local_oneOrTwo('playnopaygracemode', $playnopaygracemode)
      | !local_oneOrTwo('writelog', $writelog))
   {
      break;
   }
   
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
   
   // good arg for PDO here
   
   /*
analytics                               i
developmentmode                         i
writelog                                i
writelogfilespec                        s
emailnoreply                            s
emailsiteadmin                          s
emailtositecontact                      s
emailfromsitecontact                    s
emailsitelimit_longterm                 i
emailsitelimit_shortterm                i
emailsitelimit_longtermcount            i
emailsitelimit_shorttermcount           i
emailsitelimit_longtermbasetime         s
emailsitelimit_shorttermbasetime        s
linkconfirm                             s
linkcontact                             s
passwordadmin2                          s
passwordadmin2hint                      s
passwordadmin2question                  s
siteactive                              i
sitemaintenancemessage                  s
sitemaintenancemessagedefault           s
siteloginmessage                        s
siteloginmessageshow                    i
timezonedefault                         s
loginattemptsactive                     i
loginattemptsallowed                    i
loginlockouttimeminutes                 i
devemailnoreply                         s
devemailsiteadmin                       s
devemailtositecontact                   s
devemailfromsitecontact                 s
deverrormailto                          s
deverrormaillimit                       i
deverrormailcount                       i
sessionmessagereferencemode             i
playnopaygracedays                      i
playnopaygracemode                      i
site                                    s

iiisssssiiiisssssssisssisiiisssssiisiii
*/
   if (!$sth->bind_param("iiisssssiiiisssssssisssisiiisssssiiiiis",
                  $analytics,
                  $developmentmode,
                  $writelog,
                  $writelogfilespec,
                  $emailnoreply,
                  $emailsiteadmin,
                  $emailtositecontact,
                  $emailfromsitecontact,
                  $emailsitelimit_longterm,
                  $emailsitelimit_shortterm,
                  $emailsitelimit_longtermcount,
                  $emailsitelimit_shorttermcount,
                  $emailsitelimit_longtermbasetime,
                  $emailsitelimit_shorttermbasetime,
                  $linkconfirm,
                  $linkcontact,
                  $passwordadmin2,
                  $passwordadmin2hint,
                  $passwordadmin2question,
                  $siteactive,
                  $sitemaintenancemessage,
                  $sitemaintenancemessagedefault,
                  $siteloginmessage,
                  $siteloginmessageshow,
                  $timezonedefault,
                  $loginattemptsactive,
                  $loginattemptsallowed,
                  $loginlockouttimeminutes,
                  $devemailnoreply,
                  $devemailsiteadmin,
                  $devemailtositecontact,
                  $devemailfromsitecontact,
                  $deverrormailto,
                  $deverrormaillimit,
                  $deverrormailcount,
                  $sessionmessagereferencemode,
                  $playnopaygracedays,        
                  $playnopaygracemode,    
                  $site))
   {
      formatSessionMessage("bind error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   if(!$sth->execute()) {
      formatSessionMessage("execute error: " . $sth->error, 'info', $msg);
      setSessionMessage($msg, 'error');
      break;
   }
   
   $update_count = $sth->affected_rows;
   $msg = '';
   $switch_status = 1;
   switch($update_count) {
   case -1 :
      formatSessionMessage("There was an error updating the admin record. '$update_count'", 'danger', $msg);
      $switch_status = 0;
      break;
   case 0 :
      formatSessionMessage("No updates were made.  Nothing was found to update. '$update_count'", 'info', $msg);
      break;
   case 1:
      formatSessionMessage("The update was successful.", 'success', $msg);
      break;
   default:
      formatSessionMessage("There was an error updating the admin record. '$update_count'", 'danger', $msg);
      $switch_status = 0;
      break;
   }
   setSessionMessage($msg, 'error');
   
   $status = ($switch_status == 1) ? 1 : 0;
   break;
}

if (!empty($sth)) {
   $sth->close();
}

header ('Location: adminoperations.php');


function local_oneOrTwo(
   $var_name,
   $value
) {
   $msg = '';
   if (!$value  || !($value == 1 || $value == 2)) {
      formatSessionMessage("Var '$var_name' must be valued 1 or 2.", 'info', $msg);
      setSessionMessage($msg, 'error');
      return 0;
   }
   return 1;
}

?>