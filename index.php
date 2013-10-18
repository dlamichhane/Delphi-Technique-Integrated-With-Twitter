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
	$getfield = '?screen_name=delphi_head';
	// $getfield = '?q=#vaasa_oulu_delphi&result_type=recent';
	$requestMethod = 'GET';
	$twitter = new TwitterAPIExchange($settings);
	// $response = json_decode($twitter->setGetfield($getfield)
	//              					->buildOauth($url, $requestMethod)
	//              					->performRequest());
	// $response = $twitter->setGetfield($getfield)
	//              					->buildOauth($url, $requestMethod)
	//              					->performRequest();
	
	// var_dump($response);
	// echo "<ul>";
	// foreach ($response as $key => $value) {
	// 	echo "<li>" . $value->text . '</li>';
	// }
	// echo "</ul>";
	

	
?>

