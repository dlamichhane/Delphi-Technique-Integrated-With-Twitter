<?php
	require_once 'constant.php';
	require_once '../db.class.php';

	session_start();

	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}

	if (isset($_GET['action']) && isset($_GET['action']) == 'tweet_reset') {
		$stm = "UPDATE questions SET question_tweet_count=0, answer_tweet_count=0, R1_feedback_tweet_count=0, R2_feedback_tweet_count=0";
		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();
		$rs = $connection->createQuery($stm);
		
		$connection->closeConnection();
		if ($rs) {
			$_SESSION['tweet_reset'] = "Reset all the tweet status";
		} else {
			$_SESSION['tweet_reset'] = $rs;
		}

		header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		exit();
	}
?>
