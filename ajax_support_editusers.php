<?php
ob_start();
header("Content-type: application/json; charset=utf-8");
/*
:mode=php:

   file: ajax_support_editusers.php
   date: apr-2016
 author: hfs
   desc: This is server side support for the php using tables created by 
      get_users()
                                               
*/
require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

@ $do_this =  $_POST['dothis'];
@ $username =     (!empty($_POST['username'])) ? $_POST['username']  : '';
@ $fname =        (!empty($_POST['fname'])) ? $_POST['fname'] : '';
@ $lname =        (!empty($_POST['lname'])) ? $_POST['lname'] : '';
@ $email =        (!empty($_POST['email'])) ? $_POST['email'] : '';
@ $utype =        (!empty($_POST['utype'])) ? $_POST['utype'] : '';
@ $actstatus =    (!empty($_POST['actstatus'])) ? $_POST['actstatus'] : '';

writeDataToFile("ajax_support_editusers.php do - user - f - l - em - type - act: " .
   $do_this . ", " . $username . ", " . $fname . ", " . $lname . ", " . $email . ", " .
   $utype . ", " . $actstatus, __FILE__, __LINE__);

$status_array = array('error' => 0, 'ermsg' => 'success');

echo json_encode($weekly_data);
ob_end_flush();
exit();

?>