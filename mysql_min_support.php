<?php

/*
:mode=php:

   file: mysql_min_support.php
   date: may-2016
 author: hugh shedd
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
   $global_mysuperpicks_dbo = new mysqli(HOST, USER_NAME, USER_PASSWORD, DATABASE_NAME);
   if (mysqli_connect_errno()) {
      $ermsg = array('ERROR_MESSAGE'=>'Failed to create db handle',  
         'HOST'=>HOST, 'DATABASE_NAME'=>DATABASE_NAME, 
         'USER_NAME'=>USER_NAME, 'USER_PASSWORD'=>USER_PASSWORD);
      writeDataToFile($ermsg, __FILE__, __LINE__);
   }
   if ($global_mysuperpicks_dbo) {
     return $global_mysuperpicks_dbo;
   } else {
     return false;
   }
}
?>
