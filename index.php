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
		<!--<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script> -->
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
	$getfield = '?screen_name=delphi_head&count=1&optional=true';
	$requestMethod = 'GET';
	$twitter = new TwitterAPIExchange($settings);
	$response = json_decode($twitter->setGetfield($getfield)
	             					->buildOauth($url, $requestMethod)
	             					->performRequest());
	
	$connection = new DBConnection();
	$connection->db_connection();
	$connection->selectDb();
	
	$a = array();
	echo "<ul>";
	foreach ($response as $key => $value) {
		echo "<li>" . $value->user->id . " - ". date('Y-m-d H:i:s',strtotime($value->created_at)) . " - ". $value->text ."</li>";
	}
	echo "</ul>";
	// $twitter = new TwitterAPIExchange($settings);
	// foreach ($response as $k => $v) {
	// 	$destroy_url = "https://api.twitter.com/1.1/statuses/destroy/" . $v->id_str. ".json";
	// 	$requestMethod = 'POST';
	// 	$postfields = array('id' => $v->id);

	// 	$json =  $twitter->buildOauth($destroy_url, $requestMethod)
 //                 ->setPostfields($postfields)
 //                 ->performRequest();
 //        var_dump(json_decode($json));
 //       // die();

	// }

	die();	
	





	foreach ($response as $key => $value) {
		
		$tweet_created = date('Y-m-d H:i:s',strtotime($value->created_at));
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

					$stm = "SELECT tweet_created, modified_count FROM response WHERE expert_id='". $user_id ."' AND question_code='" . $question_code. "' AND answer_code='" . $answer_code ."'";
					$rs = $connection->selectQuery($stm);

					$query_type = 'insert';

					if (! empty($rs)) {
						if (strtotime($rs['tweet_created']) < strtotime($value->created_at)) {
							$query_type = 'update';
						} else if (strtotime($rs['tweet_created']) >= strtotime($value->created_at)) {
							$query_type = "";
						}
					}

					$a[$key] = array(
                                    'user_id' => $user_id,
                                    'tweet_created' => $tweet_created,
                                    'tweet_id' => $tweet_id
                            );
					
					if ($query_type == 'update') {
						$query_sub_stm = "";

						$query_sub_stm .= "tweet_created='" . $tweet_created . "',";
						$query_sub_stm .= "updated='" . date('Y-m-d H:i:s') . "',";
						$query_sub_stm .= "modified_count='" . ($rs['modified_count'] + 1)."'";
						
						$result= array_chunk($result,2);
						end($result);
						$last_index = key($result);

						foreach ($result as $idx => $val) {
						
							switch ($val[0]) {
								case 'A':
									$query_sub_stm .= ", answer_1= '" . $val[1] . "'";
									break;
								case 'B':
									$query_sub_stm .= ", answer_2= '" . $val[1] . "'";
									break;
								case 'C':
									$query_sub_stm .= ", answer_3= '" . $val[1] . "'";
									break;
								case 'D':
									$query_sub_stm .= ", answer_4= '" . $val[1] . "'";
									break;
								case 'E':
									$query_sub_stm .= ", answer_5= '" . $val[1] . "'";
									break;
								case 'F':
									$query_sub_stm .= ", answer_6= '" . $val[1] . "'";
									break;
								case 'G':
									$query_sub_stm .= ", answer_7= '" . $val[1] . "'";
									break;
							}
						}						

						$stm = "UPDATE response SET " . $query_sub_stm . " WHERE expert_id='" . $user_id . "' AND question_code='" . $question_code . "' AND answer_code='" . $answer_code . "'";
						$res = $connection->createQuery($stm);
						if ($res) {
							echo "Answer updated";
						}

					} else if ($query_type == 'insert') {
						$answer = "(";
						$answer_header = "(";
						
						$result= array_chunk($result,2);
						end($result);
						$last_index = key($result);

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
								$answer_header .= ', tweet_created, expert_id, question_code, answer_code, modified_count, created)';
								$answer .= ", '" . $tweet_created ."', '" . $user_id. "', '" . $question_code . "', '" . $answer_code. "', '1', '" . date('Y-m-d H:i:s') . "')";	
							} else {
								$answer_header .= ', ';
								$answer .= ', ';	
							}
						}

						$stm = "INSERT INTO response " . $answer_header ." VALUES " . $answer;
						$res = $connection->createQuery($stm);
						if ($res) {
							echo "Answer inserted";
						}
					}
				}
			}
		}
	}
	var_dump($a);
?>

