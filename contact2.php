<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';

$dev_turn_off_mail_call = true;
$site_name = ADMIN_TABLE;
if ($site_name = 'mysuperpicks') {
    $dev_turn_off_mail_call = false;
}

$name = (!empty($_POST['contactname'])) ? $_POST['contactname'] : '';    // The client's name.
$email = (!empty($_POST['contactemail'])) ? $_POST['contactemail'] : '';   // the client's email address
$message = (!empty($_POST['contactmessage'])) ? $_POST['contactmessage'] : '';   // His complaint - comment ...
$subject = (!empty($_POST['contactsubject'])) ? $_POST['contactsubject'] : '';   // Subject

writeDataToFile(" '$name', '$email', '$message', '$subject'", __FILE__, __LINE__);

$_SESSION['contactname'] = $name;
$_SESSION['contactemail'] = $email;
$_SESSION['contactmessage'] = $message;
$_SESSION['contactsubject'] = $subject;

$contact_to = '';
$contact_from = '';
$error = true;
$msg = '';
while (1) {

    if (!localGetSiteContactToFromAddresses($contact_to, $contact_from)) {
        // session messages were set above
        formatSessionMessage("We are unable to mail at this time.  Contact addresses are not available.", 'info', $msg);
        setSessionMessage($msg, 'error');
        break;
    }
    if (!filled_out($_POST)) {
        formatSessionMessage("Please complete the form.", 'info', $msg);
        setSessionMessage($msg, 'error');
        break;
    }
    if (!valid_email($email)) {
        formatSessionMessage("The email address $email is not valid.  Please correct.  No mail was sent.", 'info', $msg);
        setSessionMessage($msg, 'error');
        break;
    }
    if (areDisallowCharactersSpace($name)) {
        formatSessionMessage("The name may contain only alphanumeric characters.  Apostrophes, dashes, spaces and underscores are also allowed.", 'info', $msg);
        setSessionMessage($msg, 'error');
        break;
    }
    // http://php.net/manual/en/function.mail.php
    $toaddress = $contact_to;
    // The spaces in this string get transfer to the e-mail, so be careful when adding/removing spaces here.
    $mailcontent = "Contact name: $name,
   Contact email: $email,
   Contact comments: $message";
    $fromaddress = "From: $email";

    writeDataToFile("toaddress $toaddress name $name contact_from $contact_from", __FILE__, __LINE__);

    if ($dev_turn_off_mail_call) {
        formatSessionMessage("The contact 'mail' call has been disabled by developement. Ignore other messages.", 'warning', $msg);
        setSessionMessage($msg, 'error');
        break;
    } else {
        if (isset($_POST['submit'])) {
            // TODO: change hard coded to email address
            // $mailto = 'mattleisen@yahoo.com';
            $mailto = 'admin@mysuperpicks.com';

            mail($mailto, $subject, $mailcontent, $fromaddress);

            header('Location: contact.php');
        }
        // if (!mail($toaddress, $subject, $mailcontent, $fromaddress)) {
        //    formatSessionMessage("An unknown mailing error occurred.  The email was not sent.  Please contact the site administrator.", 'danger', $msg);
        //    setSessionMessage($msg, 'error');
        //    break;
        // }
    }
    $error = false;
    break;
}

if (!$error) {
    formatSessionMessage("Mail was sent.  Server load effects delivery times.", 'success', $msg);
    setSessionMessage($msg, 'happy');
}

header('Location: contact.php');
die();

function localGetSiteContactToFromAddresses(
    &$contact_to,
    &$contact_from
)
{

    $mysql = "
      select emailtositecontact,
             emailfromsitecontact
        from nsp_admin
       where site = ?";

    $current_site_name = ADMIN_TABLE;   // mypicks_def.php

    writeDataToFile("admin table: $current_site_name", __FILE__, __LINE__);
    $status = 0;
    $contact_to = '';
    $contact_from = '';
    $msg = '';
    while (1) {

        if (!$current_site_name) {
            formatSessionMessage("The current site name alias is not available.", 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }
        if (!$current_site_name == 'ADMIN_TABLE') {
            formatSessionMessage("The current site name alias was not defined.", 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }
        if (!$conn = db_connect()) {
            formatSessionMessage("The database is unavailable.", 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }

        if (!$sth = $conn->prepare($mysql)) {
            formatSessionMessage("SQL error: " . $conn->error, 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }

        if (!$sth->bind_param("s", $current_site_name)) {
            formatSessionMessage("bind_param error: " . $sth->error, 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }

        if (!$sth->execute()) {
            formatSessionMessage("Database error.  Error from database: " . $sth->error, 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }

        $sth->bind_result($contact_to, $contact_from);

        $fetch_status = $sth->fetch();

        if ($fetch_status === false) {
            formatSessionMessage("Database error.  Error from database: " . $sth->error, 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }
        if ($fetch_status === 0) {
            formatSessionMessage("No record was found for current site $current_site_name.", 'danger', $msg);
            setSessionMessage($msg, 'error');
            break;
        }

        $status = 1;
        break;
    }
    return $status;
}


?>
