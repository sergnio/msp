<?php


$iam_null = null;

$iam_empty_string = "";

$iam_unset = "this is";
unset($iam_unset);

if (empty($iam_null)){
   echo "iam_null is empty\n";
}
if (empty($iam_unset)){
   echo "iam_unset is empty\n";
}
//if ($iam_null == $iam_empty_string ){
//   echo "==iam_null same as empty string\n";
//}
//if ($iam_null == $iam_unset ){
//   echo "==iam null same as unset\n";
//}
//if ($iam_null === $iam_empty_string ){
//   echo "===iam_null same as empty string\n";
//}
//if ($iam_null === $iam_unset ){
//   echo "===iam null same as unset\n";
//}

?>