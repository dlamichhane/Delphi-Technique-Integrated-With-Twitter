<?php
	require_once 'constant.php';
	
	session_start();
	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}

	require_once '../db.class.php';

	if (isset($_POST['question_img']) && isset($_POST['round_img'])) {
		$upload_directory = "../image/";

		$picture = $_FILES['image'];
		$picture_path = md5(rand()) . basename( $_FILES['image']['name']);
		$target = $upload_directory . $picture_path;

		if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
			$connection = new DBConnection();
			$connection->db_connection();
			$connection->selectDb();

			$question = "#" . $_POST['question_img'] . "_code";
			$round = "#" . $_POST['round_img']. "_answer";
			// $question = "#Q1_code";
			// $round = "#R1_answer";
			$result_code = $_POST['question_img'] . "_" . $_POST['round_img'];
			// $result_code = "Q1_R1";
			$id = $connection->selectQuery("SELECT id FROM results WHERE result_code='" . $result_code . "'");
			
			if (! empty($id)) {
				$sub_stm = "image_path='" . $target . "'";
				$stm = "UPDATE results SET " . $sub_stm . " WHERE id = " . $id['id'] . " AND result_code = '" . $result_code . "'";
				$connection->createQuery($stm);
				header("Location: " . ADMIN_BASE_PATH . "/results.php");
			}

		} else {

			echo "Sorry, there was a problem uploading your file.";
		}
	}
?>