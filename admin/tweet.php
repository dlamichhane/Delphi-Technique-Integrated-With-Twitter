
<?php

	require_once 'constant.php';
	
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

	/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
	$url = 'https://api.twitter.com/1.1/statuses/update.json';
	$requestMethod = 'POST';

	if (isset($_GET['action']) && $_GET['action'] == 'tweet_q' && isset($_GET['question']) && ! empty($_GET['question'])) {
		
		$stm = "SELECT question_code, questions, question_tweet_count FROM questions WHERE id = " . $_GET['question'];
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
		$response = $twitter->buildOauth($url, $requestMethod)
		             ->setPostfields($postfields)
		             ->performRequest();
		$response = json_decode($response);

		if ($response->errors[0]->code == '187') {
			$_SESSION['tweet_fail'] = $response->errors[0]->message . " Wait sometimes to tweet the message.";
		}

		if (! empty($response->id)) {
			$question_tweet_count = $result['question_tweet_count'] + 1;
			$stm = "UPDATE questions SET question_tweet_count=" . $question_tweet_count . " WHERE id=" . $_GET['question'];
			$connection->createQuery($stm);
		}
		
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