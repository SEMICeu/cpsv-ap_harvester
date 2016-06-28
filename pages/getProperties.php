<?php

$c = $_GET['c'];
$p = $_GET['p'];
$cad = 'py getProperties.py' . " " . $c . " " . $p;
system($cad);

?>