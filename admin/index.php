<?php
	require_once 'constant.php';

	session_start();
	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Delphi technique using social media</title>
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap-responsive.css">
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/application.css">
		<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/application.js"></script>
	</head>
	<body>
		<div class="container">
			<form class="form-signin">
				<?php 
					if ( ! empty($_SESSION['current_user'])) {
						echo "<p>Welcome " . $_SESSION['current_user'] . " | <a href='logout.php'> Logout </a></p>";
					}
				?>
				<h2 class="form-signin-heading">Navigation</h2>
				<a class="btn btn-large btn-primary" href="questions.php">Prepare questions</a> <br><br>
				<a class="btn btn-large btn-primary" href="results.php">View results</a>
			</form>
		</div>		
	</body>
</html>

