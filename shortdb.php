<?php

// pretty much swiped from 
// http://php.net/manual/en/mysqli-stmt.bind-param.php  (canche_x at yahoo dot com )
// http://stackoverflow.com/questions/16236395/bind-param-with-array-of-parameters
// http://php.net/manual/en/mysqli-stmt.bind-param.php


// select - no records found == NULL
function runSql (
   $mysql,
   $abind_params,
   $close = false
){

   $ref_status_text = '';
   $conn = '';
   $sth = '';
   try {
      
      if (!$conn = db_connect()) {
         formatSessionMessage("runSql() db_connect() error: " . $conn->error, 'info', $msg);
         setSessionMessage($msg, 'error');
         $ref_status_text = '';
         return false;
      }
      
      if (!$sth = $conn->prepare($mysql)) {
         formatSessionMessage("runSql() prepare() error: " . $conn->error, 'info', $msg);
         setSessionMessage($msg, 'error');
         $ref_status_text = '';
         (!empty($sth) && $sth->close());
         @$conn->close();
         return false;
      }
      
      if (!call_user_func_array(array($sth, 'bind_param'), refValues($abind_params))) {
         formatSessionMessage("runSql() bind_param() error: " . $sth->error, 'info', $msg);
         setSessionMessage($msg, 'error');
         $ref_status_text = '';
         (!empty($sth) && $sth->close());
         $conn->close();
         return false;
      }
      
      if (!$sth->execute()) {
         formatSessionMessage("runSql() execute() error: " . $sth->error, 'info', $msg);
         setSessionMessage($msg, 'error');
         $ref_status_text = '';
         $sth->close();
         $conn->close();
         return false;
      }
      
      if($close){
         $result = $sth->affected_rows;
      } else {
         $meta = $sth->result_metadata();
         
         // http://us2.php.net/manual/en/mysqli-result.fetch-field.php
         // Returns the definition of one column of a result set as an object. Call 
         // this function repeatedly to retrieve information about all columns in the result set. 
         while ( $field = $meta->fetch_field() ) {
             $parameters[] = &$row[$field->name];
         } 
      
         call_user_func_array(array($sth, 'bind_result'), refValues($parameters));
           
         while ( $sth->fetch() ) { 
            $x = array(); 
            foreach( $row as $key => $val ) { 
               $x[$key] = $val; 
            } 
            $results[] = $x; 
         }
         $result = $results;  // Nothing?  Returns null;
      }

   } catch (mysqli_sql_exception $e) {
      $ermsg = "
         runSql() mysqli_sql_exception \n
         sql: $mysql \n\n
         MYSQL ERROR TO STRING: " . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'mysqli_sql_exception';
   }  catch (exception $e) {
      $ermsg = "
         runSql() exception \n
         sql: $mysql \n\n
         exception: " . $e->__toString();
      writeDataToFile($ermsg, __FILE__, __LINE__);
      $ref_status_text = 'exception';
   }
   
   @ $sth->close();
   @ $conn->close();
   
   return  $result;

}

function refValues($arr){
   if (strnatcmp(phpversion(),'5.3') >= 0) {//Reference is required for PHP 5.3+
      $refs = array();
      foreach($arr as $key => $value) {
          $refs[$key] = &$arr[$key];
      }
      return $refs;
   }
   return $arr;
}

?>