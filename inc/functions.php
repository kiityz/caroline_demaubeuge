<?php
	function clean_data($data) 
	{
		$data = htmlspecialchars(strip_tags(stripslashes(trim($data))));
		return $data;
	}
?>