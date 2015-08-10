<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

$host = "localhost";
$dbname = "carolinedemaubeuge";
$user = "carolinedemaubeu";
$password = "Xpa0WsbUPZFCN23p";
try
{
	$bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $user, $password);
}
catch (PDOException $e)
{
	die('Error : '.$e->getMessage());
}

date_default_timezone_set('Europe/Brussels');
$date = date('d-m-Y H:i:s', time());
?>