<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: index.php
   date: apr-2016
 author: original
   desc: This is the home page.  Defined in mypick_def.php as
      URL_HOME_PAGE

   note:
   
*/


require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

function specCheckUserNames(
   $name,
){
   

   return 1;
}


function specCheckLeagueName(
   $name
){
   returnareDisallowCharacters
   return 1;
}

function specCheckNames(
   $name
){

   return 1;
}

function specCheckNames(
   $name
){

   return 1;
}

function specCheckNames(
   $name
){

   return 1;
}
function areDisallowCharacters(
   $string
){
   if ( preg_match('[^0123456789abcdefghijklmnopqrstuvwxyz-_\']/i', $string)) {
      return 0;
   } else {
      return 1;
   } 
}

