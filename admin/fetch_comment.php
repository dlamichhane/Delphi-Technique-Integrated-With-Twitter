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

	if (isset($_GET['question']) && isset($_GET['comments']) && $_GET['comments'] == "R3_comment") {
		/** Perform a GET request and echo the response **/
		/** Note: Set the GET field BEFORE calling buildOauth(); **/
		$url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
		$getfield = '?screen_name=delphi_head&count=800&optional=true';
		$requestMethod = 'GET';
		$twitter = new TwitterAPIExchange($settings);
		$response = json_decode($twitter->setGetfield($getfield)
		             					->buildOauth($url, $requestMethod)
		             					->performRequest());
		
		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();
		// var_dump($response);
		// #Q1_code #R3_comment I didn't like this sort of things so
		foreach ($response as $key => $value) {
			$tweet_created = date('Y-m-d H:i:s',strtotime($value->created_at));
			$user_id = $value->user->id;
			$tweet_id = $value->id;
			
			$str = $value->text;
			$pattern = "/(#\w+)/";
			preg_match_all($pattern, $str, $matches, PREG_PATTERN_ORDER);
			$matches = $matches[1];

			// $check_array = array('#Q1_code', "#R3_comment");
			$check_array = array('#' . $_GET['question'], "#" . $_GET['comments']);
			$exist = false;

			foreach ($check_array as $k => $v) {
				if (in_array($v, $matches)) {
					$exist = true;
				} else {
					$exist = false;
					break;
				}
			}

			if ($exist) {
				$str = preg_replace('/#([\w-]+)/i', '', $str); // #someone
				$str = preg_replace('/@([\w-]+)/i', '', $str); // @tag
				$result = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '', $str); //url

				$tag = explode('_', $value->entities->hashtags[0]->text);

				if ($tag[1] == "code") {
					$question_code = "#". $value->entities->hashtags[0]->text;
				} else if ($tag[1] == 'comment') {
					$comment_code = "#". $value->entities->hashtags[0]->text;
				}

				$tag = explode('_', $value->entities->hashtags[1]->text);

				if ($tag[1] == "code") {
					$question_code = "#". $value->entities->hashtags[1]->text;
				} else if ($tag[1] == 'comment') {
					$comment_code = "#". $value->entities->hashtags[1]->text;
				}
				
				$stm = "SELECT tweet_created, modified_count FROM comments WHERE expert_id='". $user_id ."' AND question_code='" . $question_code. "' AND comment_code='" . $comment_code ."'";
				$rs = $connection->selectQuery($stm);
				
				$query_type = 'insert';

				if (! empty($rs)) {
					if (strtotime($rs['tweet_created']) < strtotime($tweet_created)) {
						$query_type = 'update';
					} else if (strtotime($rs['tweet_created']) >= strtotime($tweet_created)) {
						$query_type = "";
					}
				}

				if ($query_type == "update") {
					$val = "comments='" . mysql_real_escape_string(trim($result)) . "'";
					$val .= ", expert_id='" . $user_id. "'";
					$val .= ", tweet_id='" . $tweet_id . "'";
					$val .= ", tweet_updated='" . $tweet_created . "'";
					$val .= ", question_code='" . $question_code . "'";
					$val .= ", comment_code='" . $comment_code . "'";
					$val .= ", modified_count='" . ($rs['modified_count'] + 1) . "'";
					$stm = "UPDATE comments SET " . $val . " WHERE expert_id='" . $user_id . "' AND question_code='" . $question_code . "' AND comment_code='" . $comment_code . "'";
					$res = $connection->createQuery($stm);
				} else if ($query_type == "insert") {
					$val = "'" . mysql_real_escape_string(trim($result)) . "'";
					$val .= ", '" . $user_id . "'";
					$val .= ", '" . $tweet_id . "'";
					$val .= ", '" . $tweet_created . "'";
					$val .= ", '" . $check_array[0] . "'";
					$val .= ", '" . $check_array[1] . "'";
					$val .= ", 1";
					$val .= ", '" . date('Y-m-d H:i:s') . "'";

					$stm = "INSERT INTO comments (comments, expert_id, tweet_id, tweet_created, question_code, comment_code, modified_count, created) VALUES (" . $val. ")";
					$res = $connection->createQuery($stm);
				}
			}
		}

		if ($res) {
			$_SESSION['res_insert'] = "You inserted the comments";
		}

		header("Location: " . ADMIN_BASE_PATH . "/preprocessor.php");
		exit();
	}

?>