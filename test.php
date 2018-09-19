

$x = '@#$@#$';


   if ( preg_match('[^0123456789abcdefghijklmnopqrstuvwxyz-_\']/i', $x)) {
      echo 'bad chars';
   } else {
      echo 'good chars';
   } 
}