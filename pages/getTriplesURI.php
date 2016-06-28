<?php

$u = $_GET['URI'];
$c = $_GET['class'];
$cad = 'py getTriplesURI.py' . " " . $u . " " . $c;
system($cad);

?>