<?php 
session_start();
require_once('./inc/functions.php');
require_once('./inc/class.upload.php');
require_once('./inc/db_connect.php');

/*Get client IP*/
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$ip = $_SERVER['REMOTE_ADDR'];
}

/*Get image gallery*/
$query = 'SELECT * FROM gallery_posts ORDER BY id DESC LIMIT 10';
	$preparedStatement = $bdd->prepare($query);
	$preparedStatement->execute();
	$posts = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

/*Manage file transfer if there is a post*/
if($_POST)
{
	$text = clean_data($_POST['text']);
	if($text > 90)
	{
		$text = mb_substr($text, 0, 90);
	}
	if(!empty($_FILES))
	{
		$file = $_FILES['file'];
		if($file['type'] == 'image/jpeg' || $file['type'] == 'image/jpg' || $file['type'] == 'image/png' || $file['type'] == 'image/gif')
		{
			$format = substr($file['type'], 6);
			if($format == 'jpeg')
			{
				$format = "jpg";
			}
			$file = new Upload($_FILES['file']);
			if($file->uploaded)
			{
				if(strlen($posts[0]['id']) == "")
				{
					$number = $posts[0]['id'] = 1;
				}
				else
				{
					$number = intval($posts[0]['id']) +1;
				}
				
				$source = "./uploads/img/$number-post-x900.$format";
				
				$file->file_new_name_body = $number . '-post-x900'; /*This variable comes from the sql request done just before*/
				$file->image_resize = true;
				$file->file_overwrite = true;
				$file->image_ratio_crop = true;
				$file->image_x = 900;
				$file->image_y = 900;
				$file->image_text = $text;
				$file->image_text_color ="#ffffff";
				$file->image_text_background = "#000000";
				$file->image_text_background_opacity = 67;
				$file->image_text_font = 5;
				$file->image_text_position = "BR";
				$file->image_text_padding = 15;
				$file->image_text_line_spacing = 10;
				$file->Process('./uploads/img');
				if($file->processed)
				{
					$query = 'INSERT INTO gallery_posts(source, title, ip, datetime) VALUES(:source, :title, :ip, :datetime)';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->bindParam(':source', $source);
					$preparedStatement->bindParam(':title', $text);
					$preparedStatement->bindParam(':ip', $ip);
					$preparedStatement->bindParam(':datetime', $date);
					$preparedStatement->execute();
					$posts = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
					
					header('Location:index.php');
				}
				else
				{
					die('Error: ' . $file->error);
					exit();
				}
			}
		}
		else{
			die('Error: Wrong file format');
			exit();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Home | The Mashup project</title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0" />
		<link rel="icon" type="image/png" href="img/favicon.png" />
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<?php
	/*START CLIENT ZONE*/
	if(!isset($_SESSION['username']))
	{
	?>
		<body>
			<div class="container">
				<div class="log"> 
					<a href="connect.php">Log in</a>
				</div>
				<h1>The Mashup project</h1>
				<h2>Welcome to the Anonymous Picture Sharing Network</h2>
				<p>Your current upload IP : <?php echo $ip;?></p>
				
				<h3>Upload a new picture</h3>		
				<form action="" method="post" enctype="multipart/form-data">
					<label for="file">Your picture</label>
					<input id="file" type="file" name="file" />
					<label for="message">Your picture label</label>
					<textarea id="message" name="text" placeholder="Type your picture label here" maxlength="90"></textarea>
					<button type="submit">Share picture</button>
				</form>
			
				<h3>Recent uploads</h3>
				<ul>
				<?php
				$query = 'SELECT * FROM gallery_posts ORDER BY id DESC LIMIT 1';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->execute();
					$total = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
				if(!isset($_GET['posts']) || intval($_GET['posts']) < 2)
				{
					foreach($posts as $keys => $p)
					{
				?>
					<li>
						<figure class="img-controller">
							<img src="<?php echo $p['source']?>" alt="<?php echo $p['title']?>" />
							<figcaption><?php echo $p['title']?></figcaption>
						</figure>
					</li>
				<?php
					}
					$query = 'SELECT COUNT(*) FROM gallery_posts ORDER BY id DESC';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->bindParam(':offset', $offset);
					$preparedStatement->execute();
					$count = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
					if($count[0]['COUNT(*)'] > 10)
					{
				?>
					<a href="index.php?posts=2">Next page</a>
				<?php
					}
				}
				else
				{
					$id = clean_data($_GET['posts']);
					$offset = (intval(clean_data($_GET['posts'])) * 10)-1;
					$query = 'SELECT * FROM gallery_posts ORDER BY id DESC LIMIT 10 OFFSET :offset';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->bindParam(':offset', $offset);
					$preparedStatement->execute();
					$posts = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($posts as $keys => $p)
					{
				?>
					<li>
						<figure class="img-controller">
							<img src="<?php echo $p['source']?>" alt="<?php echo $p['title']?>" />
							<figcaption><?php echo $p['title']?></figcaption>
						</figure>
					</li>
				<?php
					}
					if(intval($posts[0]['id']) < intval($total[0]['id']))
					{
				?>
					<a href="index.php?posts=<?php if(intval($id) >2){echo intval($id)-1;}?>">Previous page</a>
				<?php
					}
					if($posts[9]['id'] > 1)
					{
				?>
					<a href="index.php?posts=<?php echo $id;?>">Next page</a>
				<?php
					}
				}
				?>
				</ul>
			</div>
		</body>
	<?php
		} /*END OF CLIENT ZONE*/
		else /*START ADMIN ZONE*/
		{
	?>
		<body>
			<div class="container">
				<div class="log"> 
					<a href="disconnect.php">Disconnect</a>
				</div>
				<h1>The Mashup project</h1>
				<h2>Welcome home <?php echo $_SESSION['username']?> !</h2>
				<p>Your current admin IP : <?php echo $ip;?></p>
					
					<h3>Upload a new picture</h3>		
					<form action="" method="post" enctype="multipart/form-data">
						<label for="file">Your picture</label>
						<input id="file" type="file" name="file" />
						<label for="message">Your picture label</label>
						<textarea id="message" name="text" placeholder="Type your picture label here" maxlength="90"></textarea>
						<button type="submit">Share picture</button>
					</form>
					
					<h3>Manage your gallery</h3>
					<ul>
				<?php
				$query = 'SELECT * FROM gallery_posts ORDER BY id DESC LIMIT 1';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->execute();
					$total = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
				if(!isset($_GET['posts']) || intval($_GET['posts']) < 2)
				{
					foreach($posts as $keys => $p)
					{
				?>
					<li>
						<figure class="img-controller">
							<img src="<?php echo $p['source']?>" alt="<?php echo $p['title']?>" />
							<figcaption><?php echo $p['title']?></figcaption>
						</figure>
						<a href="delete.php?id=<?php echo $p['id']; ?>">Delete this pict</a>
					</li>
				<?php
					}
					$query = 'SELECT COUNT(*) FROM gallery_posts ORDER BY id DESC';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->bindParam(':offset', $offset);
					$preparedStatement->execute();
					$count = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
					if($count[0]['COUNT(*)'] > 10)
					{
				?>
					<a href="index.php?posts=2">Next page</a>
				<?php
					}
				}
				else
				{
					$id = clean_data($_GET['posts']);
					$offset = (intval(clean_data($_GET['posts'])) * 10)-1;
					$query = 'SELECT * FROM gallery_posts ORDER BY id DESC LIMIT 10 OFFSET :offset';
					$preparedStatement = $bdd->prepare($query);
					$preparedStatement->bindParam(':offset', $offset);
					$preparedStatement->execute();
					$posts = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($posts as $keys => $p)
					{
				?>
					<li>
						<figure class="img-controller">
							<img src="<?php echo $p['source']?>" alt="<?php echo $p['title']?>" />
							<figcaption><?php echo $p['title']?></figcaption>
						</figure>
						<a href="delete.php?id=<?php echo $p['id']; ?>">Delete this pict</a>
					</li>
				<?php
					}
					if(intval($posts[0]['id']) < intval($total[0]['id']))
					{
				?>
					<a href="index.php?posts=<?php if(intval($id) >2){echo intval($id)-1;}?>">Previous page</a>
				<?php
					}
					if($posts[9]['id'] > 1)
					{
				?>
					<a href="index.php?posts=<?php echo $id;?>">Next page</a>
				<?php
					}
				}
				?>
				</ul>
				
			</div>
		</body>
	<?php
		}/*END ADMIN ZONE*/
	?>
	<div class="container">
		<footer>
			<p>Copyright 2015 | The Mashup project | <?php echo $total[0]['id'];?> pictures already shared</p>
			<?php
			if(!isset($_SESSION['username']))
			{
			?>
			<!-- <a href="connect.php">Log in</a> -->
			<?php
			}
			?>
		</footer>
	</div>
</html>