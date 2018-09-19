<?php
if (isset($_POST['submit'])){
    $mailto = 'sergniotrash@gmail.com';
    $headers = 'headers';
    $msg = 'please work' . $_POST["name"];

    mail($mailto, 'subject', $msg, $headers);
    header('location: testpost.php');
}

  ?>
