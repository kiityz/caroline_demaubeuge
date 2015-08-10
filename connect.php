<?php
	session_start();
	require_once('./inc/functions.php');
	require_once('./inc/db_connect.php');
	
	if($_POST)
	{
		$username = clean_data($_POST['username']);
		$password = clean_data($_POST['password']);
		
		$query = 'SELECT * FROM gallery_admins WHERE username =:username AND password=:password';
		$preparedStatement = $bdd->prepare($query);
		$preparedStatement->bindParam(':username', $username);
		$preparedStatement->bindParam(':password', $password);
		$preparedStatement->execute();
		$posts = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
		
		if(count($posts) >0)
		{
			$_SESSION['username'] = $posts[0]['username'];
			header('Location:index.php');
			exit();
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Connection | The Mashup project</title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0" />
		<link rel="icon" type="image/png" href="img/favicon.png" />
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
		<div class="container">
			<h1>The Mashup project</h1>
			<h2>Admin login zone</h2>
			<form class="connect-zone" action="" method="post">
				<label class="connexion-title">Username</label>
				<input class="connexion" type="text" name="username" placeholder="Username" />
				<label class="connexion-title">Password</label>
				<input class="connexion" type="password" name="password" placeholder="Password" />
				<button type="submit">Log in</button>
			</form>
		</div>
	</body>
</html>