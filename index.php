<?php function Ip() {
 $ip = $_SERVER['REMOTE_ADDR'];    
 return $ip;
 } 
 echo Ip();
 ?>
