<?php
	require_once 'constant.php';
	require_once '../db.class.php';

	session_start();

	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}

	require_once '../db.class.php';
	require_once('../twitter/TwitterAPIExchange.php');

	/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
	$settings = array(
	    'oauth_access_token' => ACCESS_TOKEN,
	    'oauth_access_token_secret' => ACCESS_TOKEN_SECRET,
	    'consumer_key' => CONSUMER_KEY,
	    'consumer_secret' => CONSUMER_SECRET
	);

	if (isset($_GET['round']) && isset($_GET['question'])) {
		$round = $_GET['round'];
		$question_id = $_GET['question'];
		
		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();

		$stm = "SELECT question_code FROM questions WHERE id=" . $question_id;
		$question_code = $connection->selectQuery($stm);

		$question_code_arr = explode('_', $question_code['question_code']);
		$question_code = substr($question_code_arr[0], 1, 2);
		$result_code = $question_code . "_" . $round;

		$stm = "SELECT * FROM results WHERE result_code='" . $result_code . "'";
		$rs = $connection->selectQuery($stm);
		
		if ( empty($rs)) {
			$_SESSION['no_feedback'] = "No feedback image available and feedback is not tweeted";
			header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		}

		$img_path = $rs['image_path'];

		/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
		$url = 'https://api.twitter.com/1.1/statuses/update_with_media.json';
		$requestMethod = 'POST';

		$postfields = array(
		    'status' => 'POSTED',
		    'media[]' => "@{$img_path}"
		);

		// var_dump($postfields);
		/** Perform a POST request and echo the response **/
		$twitter = new TwitterAPIExchange($settings);
		$response = $twitter->buildOauth($url, $requestMethod)
		             ->setPostfields($postfields)
		             ->performRequest();
		$response = json_decode($response);
		
		if (! empty($response->id)) {
			$_SESSION['yes_feedback'] = "Feedback is tweeted";
		}
		header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		exit();
	}
?>