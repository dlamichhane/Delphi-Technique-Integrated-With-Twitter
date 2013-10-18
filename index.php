<?php
	require_once 'admin/constant.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Delphi technique using social media</title>
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.css">
		<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>
		<script src="bootstrap/js/bootstrap.js"></script>
		<script src="bootstrap/js/application.js"></script>
	</head>
	<body>
		<a href="<?php echo ADMIN_BASE_PATH ?>/login.php">Sign into Admin</a>
		<h1>Tweet from here</h1>
		<div>
			<form method="POST" action="index.php">
				<textarea id="question_area" rows="4" cols="50" name="questions"></textarea>
				<input type="submit" value="Ask Question" name="">
			</form>
		</div>
		<div><strong>Note</strong><p>Total characters left : <span id="text_length">140</span></p> <p>Characters greater than 140 are discarded</p></div>
		<br>
		<br>

	</body>
</html>

<?php 

	ini_set('display_errors', 1);
	require_once('db.class.php');
	
	require_once('twitter/TwitterAPIExchange.php');
	/* Localhost access token and secret key */

	define("ACCESS_TOKEN", "1931901835-yt9Gmg8SoyvqwbKg1bBVD9kNi6UBMoP3BITLyyc");
	define("ACCESS_TOKEN_SECRET", "R90T0KsHzIC4Nd31jwT37PSSsP2CLZm6qPOLbgSFY4");
	define("CONSUMER_KEY", "UpjOaNLQxBs1TdLHRbEbPA");
	define("CONSUMER_SECRET", "tn7tL66w0kzzROqyqJtXq6gXnbJ8L0rB3efVEh3DM");

	/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
	$settings = array(
	    'oauth_access_token' => ACCESS_TOKEN,
	    'oauth_access_token_secret' => ACCESS_TOKEN_SECRET,
	    'consumer_key' => CONSUMER_KEY,
	    'consumer_secret' => CONSUMER_SECRET
	);

	if (isset($_POST['questions']) && ! empty($_POST['questions'])) {
		/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
		// $url = 'https://api.twitter.com/1.1/statuses/update.json';
		$url = 'https://api.twitter.com/1.1/statuses/update_with_media.json';
		$requestMethod = 'POST';

		$image = 'image/flood.jpg';
		
		/** POST fields required by the URL above. See relevant docs as above **/
		$postfields = array(
		    'status' => $_POST['questions'],
		    'media[]' => "@{$image}"
		);

		/** Perform a POST request and echo the response **/
		$twitter = new TwitterAPIExchange($settings);
		// $twitter->buildOauth($url, $requestMethod)
		//              ->setPostfields($postfields)
		//              ->performRequest();
	}
	

	/** Perform a GET request and echo the response **/
	/** Note: Set the GET field BEFORE calling buildOauth(); **/
	$url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
	// $url = 'https://api.twitter.com/1.1/search/tweets.json';
	$getfield = '?screen_name=delphi_head&count=800&optional=true';
	// $getfield = '?q=#Options_Q1&result_type=recent';
	$requestMethod = 'GET';
	$twitter = new TwitterAPIExchange($settings);
	$response = json_decode($twitter->setGetfield($getfield)
	             					->buildOauth($url, $requestMethod)
	             					->performRequest());
	
	$connection = new DBConnection();
	$connection->db_connection();
	$connection->selectDb();
	
	$a = array();
	foreach ($response as $key => $value) {
		
		$tweet_created = strtotime($value->created_at);
		$user_id = $value->user->id;
		$tweet_id = $value->id;
		
		$str = $value->text;
		$pattern = "/(#\w+)/";

		preg_match_all($pattern, $str, $matches, PREG_PATTERN_ORDER);
		$matches = $matches[1];

		$check_array = array('#Q1_code', "#Q1_answer");

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
			$str = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '', $str); //url

			$result = preg_split('/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)/i', $str);
			preg_match_all('/\d+|[a-z]+/i', $str, $result);
			$result = $result[0];

			$answer_valid = false;
			$result = array_unique($result);
			
			// var_dump($result);

			$alphabet = array();
			$ranking = array();

			if (count($result) == 14) {
				/* Seperate answer into two arrays */
				$i = 0;
				do {
				    array_push($alphabet, $result[$i]);
				    array_push($ranking, $result[$i + 1]);
				    $i = $i + 2;
				} while ($i < count($result));
				
				$tmp_alphabet = $alphabet;
				$tmp_ranking = $ranking;

				if (sort($tmp_alphabet) == array('A', 'B', 'C', 'D', 'E', 'F', 'G') && sort($tmp_ranking) == array('1', '2', '3', '4', '5', '6', '7')) {
					$answer_valid = true;
				}

				if ($answer_valid) {
					$tag = explode('_', $value->entities->hashtags[0]->text);

					if ($tag[1] == "code") {
						$question_code = "#". $value->entities->hashtags[0]->text;
					} else if ($tag[1] == 'answer') {
						$answer_code = "#". $value->entities->hashtags[0]->text;
					}

					$tag = explode('_', $value->entities->hashtags[1]->text);

					if ($tag[1] == "code") {
						$question_code = "#". $value->entities->hashtags[1]->text;
					} else if ($tag[1] == 'answer') {
						$answer_code = "#". $value->entities->hashtags[1]->text;
					}

					//$stm = "SELECT user_id FROM response WHERE user_id=". $user_id ." AND tweet_created=";
					// $a[$user_id] = array(
					// 	'user_id' => $user_id,
					// 	'tweet_created' => $tweet_created,
					// 	'tweet_id' => $tweet_id
					// );


					$answer = "(";
					$answer_header = "(";
					end($ranking);
					$last_index = key($ranking);
					
					$result= array_chunk($result,2);

					foreach ($result as $idx => $val) {

						switch ($val[0]) {
							case 'A':
								$answer_header .= "answer_1";
								$answer .= "'" . $val[1] . "'";
								break;
							case 'B':
								$answer_header .= "answer_2";
								$answer .= "'" . $val[1] . "'";
								break;
							case 'C':
								$answer_header .= "answer_3";
								$answer .= "'" . $val[1] . "'";
								break;
							case 'D':
								$answer_header .= "answer_4";
								$answer .= "'" . $val[1] . "'";
								break;
							case 'E':
								$answer_header .= "answer_5";
								$answer .= "'" . $val[1] . "'";
								break;
							case 'F':
								$answer_header .= "answer_6";
								$answer .= "'" . $val[1] . "'";
								break;
							case 'G':
								$answer_header .= "answer_7";
								$answer .= "'" . $val[1] . "'";
								break;
						}

						if ($last_index == $idx) {
							$answer_header .= ', created, expert_id, question_code, answer_code)';
							$answer .= ', ' . $tweet_created .', ' . $user_id. ', ' . $question_code . ', ' . $answer_code. ')';	
						} else {
							$answer_header .= ', ';
							$answer .= ', ';	
						}
					}

					$stm = "INSERT INTO response " . $answer_header ." VALUES " . $answer;
					var_dump($stm);
					//$res = $connection->createQuery($stm);
					// var_dump($user_id);
					// var_dump($tweet_created);
					// var_dump($id);
					// if ($res) {
					// 	echo "Answer inserted";
					// }
				}
			}
			
		}

	}


?>

