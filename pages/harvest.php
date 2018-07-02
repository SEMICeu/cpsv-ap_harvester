<?php

header('Access-Control-Allow-Origin: *');
system('python harvester.py');

// $res="it is result";
// echo "var result = ".json_encode($res).";";
?>