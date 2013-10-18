<?php
	require_once '../db.class.php'; 
	require_once "constant.php";

	session_start();
	if (isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/index.php");
		exit();
	} else if (isset($_POST['username']) && isset($_POST['password']) && ! empty($_POST['username']) && ! empty($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];

		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();

		$stm = "SELECT user_id from users where username = '" . $username . "' AND password = '" . $password . "'";
		$result = $connection->selectQuery($stm);
		$connection->closeConnection();
		
		session_start();

		if (! is_bool($result) || ! empty($_SESSION[current_user])) {
			if ( empty($_SESSION[current_user])) {
				$_SESSION['current_user'] = $username;
			}
			
			header("Location: " . ADMIN_BASE_PATH . "/index.php");
			exit();

		} else {
			header("Location: " . ADMIN_BASE_PATH . "/login.php");
			exit();
		}

	} else {
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
			<form class="form-signin" method="POST" action="login.php">
				<a href="<?php echo BASE_PATH ?>/index.php">Back to front</a>
				<h2 class="form-signin-heading">Please sign in</h2>
				<input type="text" class="input-block-level" name="username" placeholder="Username">
				<input type="password" class="input-block-level" name="password" placeholder="Password">
				<button class="btn btn-large btn-primary">Sign in</button>
			</form>
		</div>
	</body>
</html>

<?php
	}
?>