
<?php

	require_once 'constant.php';
	
	session_start();
	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}

	require_once '../db.class.php';
	require_once('../twitter/TwitterAPIExchange.php');
	/* Localhost access token and secret key */

	define("ACCESS_TOKEN", "1931901835-yt9Gmg8SoyvqwbKg1bBVD9kNi6UBMoP3BITLyyc");
	define("ACCESS_TOKEN_SECRET", "R90T0KsHzIC4Nd31jwT37PSSsP2CLZm6qPOLbgSFY4");
	define("CONSUMER_KEY", "UpjOaNLQxBs1TdLHRbEbPA");
	define("CONSUMER_SECRET", "tn7tL66w0kzzROqyqJtXq6gXnbJ8L0rB3efVEh3DM");

	/* Server access token and secret key */

	// define("ACCESS_TOKEN", "1931901835-Bwc6m8pXn4AgTlBhVRwQI2SEtj1d6L4h0SG6iQm");
	// define("ACCESS_TOKEN_SECRET", "OSaME5plc4mjNphuMHHezVJulwyAn9qI9a0g7GwOZ4k");
	// define("CONSUMER_KEY", "dgEfVF2QouXhUiJncVXWdQ");
	// define("CONSUMER_SECRET", "nmjKJDxhyjA5OhMmPDUSuk37bGVgjn3HiiODEVIMn4");

	/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
	$settings = array(
	    'oauth_access_token' => ACCESS_TOKEN,
	    'oauth_access_token_secret' => ACCESS_TOKEN_SECRET,
	    'consumer_key' => CONSUMER_KEY,
	    'consumer_secret' => CONSUMER_SECRET
	);

	/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
	$url = 'https://api.twitter.com/1.1/statuses/update.json';
	$requestMethod = 'POST';

	if (isset($_GET['action']) && $_GET['action'] == 'tweet_q' && isset($_GET['question']) && ! empty($_GET['question'])) {
		
		$stm = "SELECT question_code, questions FROM questions WHERE id = " . $_GET['question'];
		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();
		$result = $connection->selectQuery($stm);
		$connection->closeConnection();

		$question = array_shift($result);
		$question .= " " . array_shift($result);
		$question = substr($question, 0, 140);
		
		/** POST fields required by the URL above. See relevant docs as above **/
		$postfields = array(
		    'status' => $question
		);

		/** Perform a POST request and echo the response **/
		$twitter = new TwitterAPIExchange($settings);
		$twitter->buildOauth($url, $requestMethod)
		             ->setPostfields($postfields)
		             ->performRequest();
		header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		exit();
	}


	if (isset($_GET['action']) && $_GET['action'] == 'tweet_o' && isset($_GET['question']) && ! empty($_GET['question'])) {

		$stm = "SELECT option_1, option_2, option_3, option_4, option_5, option_6, option_7 FROM questions WHERE id = " . $_GET['question'];
		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();
		$result = $connection->selectQuery($stm);
		$connection->closeConnection();
		
		// $option = "";
		$opt_arr = array();

		$result = array_combine(array('A', 'B', 'C', 'D', 'E', 'F', 'G'), array_values($result));
		$result = array_filter($result);
		
		end($result);
		$last_idx = key($result);

		foreach ($result as $key => $value) {
			$value = $key . ') '. $value;
			if (strlen($value) > 140) {
				$value = substr($value, 0, 140);
			}

			$max_length = strlen($option) + strlen($value);
			
			if ($max_length <= 140) {
				$option .= $value . ' ';
			} else {
				array_push($opt_arr, $option);
				$option = $value . ' ';
			}

			if ($key == $last_idx) {
				array_push($opt_arr, $option);
			}
		}

		$twitter = new TwitterAPIExchange($settings);
		
		foreach ($opt_arr as $key => $option) {
			/** POST fields required by the URL above. See relevant docs as above **/
			$postfields = array(
			    'status' => $option
			);

			/** Perform a POST request and echo the response **/
			
			$twitter->buildOauth($url, $requestMethod)
			             ->setPostfields($postfields)
			             ->performRequest();
		}
		
		header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		exit();
	}
?>