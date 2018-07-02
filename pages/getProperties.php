<?php

header('Access-Control-Allow-Origin: *');
$c = $_GET['c'];
$p = $_GET['p'];
$cad = 'python getProperties.py' . " " . $c . " " . $p;
system($cad);

?>