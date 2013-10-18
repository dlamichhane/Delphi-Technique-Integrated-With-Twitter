<?php

	require_once 'constant.php';
	
	session_start();
	unset($_SESSION['current_user']);
	header("Location: " . ADMIN_BASE_PATH . "/index.php");
	exit();
?>