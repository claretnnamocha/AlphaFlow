<?php

#Start PHP
// $command = '$a = "hello";$b = "world";print($a.\' \'.$b);';
// $command = preg_replace("#(\\\\')#", '\'', addslashes($command));
// $f = system('php -r "' .$command."\n". '"');
#End PHP

#Start Python
$command = 'print("hello")';
$command = preg_replace("#(\\\\')#", '\'', addslashes($command));
// print($command);
$f = system('python -c "'.$command.'"');
// var_dump($f);

// print($command);