<?php

header('Access-Control-Allow-Origin: *');
$c = $_GET['c'];
$p = $_GET['p'];
$cad = 'python3 getProperties.py' . " " . $c . " " . $p;
system($cad);

?>
