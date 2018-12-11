<?php

/*
:mode=php:

   file: mysql_min_support.php
   date: may-2016
 author: originall
   desc: This was created to support the ajax server side database actions.  The 
   site_fns_diminished.php and mypicks_db.php were just to big.
   
  notes: Functions included here copied from the "big" files so they cannot
  be included.  Requiring site_fns_diminished.php, for example, would cause a 
  collision with db_connect() which is defined here.
  
  I decided to not rename a new set of support functions for callbacks since
  these already work and as new support is required working code be pasted in.
  
*/


function db_connect() {
   global $global_mysuperpicks_dbo;
   if(!$global_mysuperpicks_dbo = mysqli_init()) {
   		writeDataToFile("mysql_init failed", __FILE__, __LINE__);
   }
    // TODO: DUPLICATE CODE - See site_fns_diminished.php:87 for duplicate
    //godady does not have timezone data loaded in sql
   //http://jdnash.com/2014/03/godaddy-mysql-and-the-time-zone-problem/
   $cTZNow = new DateTime("now", new DateTimeZone(DEFAULT_TIME_ZONE));
   $dateOffset = date("P", $cTZNow->getTimestamp());
	
   if (!$global_mysuperpicks_dbo->options(MYSQLI_INIT_COMMAND, "SET time_zone = '" . $dateOffset . "';")) {
   		writeDataToFile("set timezone failed", __FILE__, __LINE__);
   }
   
   if (!$global_mysuperpicks_dbo->real_connect(HOST, USER_NAME, USER_PASSWORD, DATABASE_NAME)) {
   		$ermsg = array('ERROR_MESSAGE'=>'Failed to create db handle',  
         'HOST'=>HOST, 'DATABASE_NAME'=>DATABASE_NAME, 
         'USER_NAME'=>USER_NAME, 'USER_PASSWORD'=>USER_PASSWORD,
         'MYSQL_CONNECTION_ERROR' => $er);
      writeDataToFile($ermsg, __FILE__, __LINE__);
   }
   
   return $global_mysuperpicks_dbo;
}
?>
