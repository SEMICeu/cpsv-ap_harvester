<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: text/html; charset=utf-8');
$locale='et_EE.UTF-8';
setlocale(LC_ALL,$locale);
putenv('LC_ALL='.$locale);
$u = $_GET['URI'];
$c = $_GET['class'];
/*$u=$argv[1];
$c=$argv[2];*/
$cad = 'python getTriplesURI.py' . " " . $u . " " . $c;
$text = utf8_decode(utf8_encode(system($cad)));
//$text = iconv("ASCII", "UTF-8", system($cad));
$enc = mb_detect_encoding($text);
//echo $enc."<br />";
//echo 'Original : ', $text."<br />";
//echo 'UTF8 Encode : ', utf8_encode($text)."<br />";
//echo 'UTF8 Decode : ', utf8_decode($text)."<br />";
//echo 'TRANSLIT : ', iconv($enc, "UTF-8//TRANSLIT", $text)."<br />";
//echo 'IGNORE TRANSLIT : ', iconv($enc, "UTF-8//IGNORE//TRANSLIT", $text)."<br />";
//echo 'IGNORE   : ', iconv($enc, "UTF-8//IGNORE", $text)."<br />";
//echo 'Plain    : ', iconv($enc, "UTF-8", $text)."<br />";
?>
