<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'mysql';
$DB_NAME = 'userDb';

//--- DB Connection
try{
	$DB_con = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}",$DB_USER,$DB_PASS);
	$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
	echo $e->getMessage();
}

//--- Function redirect
function GoToNow ($url){
	echo '<script type="text/javascript">setTimeout(function(){window.top.location="'.$url.'"} , 500);</script>';
}

?>
