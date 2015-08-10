<?php
session_start();
require_once('./inc/functions.php');
require_once('./inc/db_connect.php');

if(isset($_SESSION['username']))
{
	if($_GET)
	{
		$id = clean_data($_GET['id']);
		
		$query = 'DELETE FROM gallery_posts WHERE id=:id';
		$preparedStatement = $bdd->prepare($query);
		$preparedStatement->bindParam(':id', $id);
		$preparedStatement->execute();
		
		header('Location:index.php');
		exit();
	}
	else
	{
		header('Location:index.php');
		exit();
	}
}
else
{
	header('Location:index.php');
	exit();
}
?>