<?php
	require_once 'constant.php';
	
	session_start();
	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}

	if (isset($_POST['question']) && isset($_POST['round'])) {
		require_once '../db.class.php';

		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();

		$question = "#" . $_POST['question'] . "_code";
		$round = "#" . $_POST['round']. "_answer";
		// $question = "#Q1_code";
		// $round = "#R1_answer";
		$result_code = $_POST['question'] . "_" . $_POST['round'];
		// $result_code = "Q1_R1";
		
		$stm = "SELECT 	SUM(answer_1) / COUNT(answer_1) AS option_1, SUM(answer_2) / COUNT(answer_2) AS option_2,
						SUM(answer_3) / COUNT(answer_3) AS option_3, SUM(answer_4) / COUNT(answer_4) AS option_4,
						SUM(answer_5) / COUNT(answer_5) AS option_5, SUM(answer_6) / COUNT(answer_6) AS option_6,
						SUM(answer_7) / COUNT(answer_7) AS option_7
				FROM response
				WHERE answer_code = '" . $round ."' AND question_code='".$question."'";
		$rs = $connection->selectQuery($stm);

		$stm = "SELECT option_1, option_2, option_3, option_4, option_5, option_6, option_7 
				FROM questions WHERE question_code='" .$question. "'";
		$rs1 = $connection->selectQuery($stm);
		
		foreach ($rs1 as $key => $value) {
			switch ($key) {
				case 'option_1':
					$rs1[$key] = "(A) " . $value;
					break;

				case 'option_2':
					$rs1[$key] = "(B) " . $value;
					break;

				case 'option_3':
					$rs1[$key] = "(C) " . $value;
					break;

				case 'option_4':
					$rs1[$key] = "(D) " . $value;
					break;

				case 'option_5':
					$rs1[$key] = "(E) " . $value;
					break;

				case 'option_6':
					$rs1[$key] = "(F) " . $value;
					break;

				case 'option_7':
					$rs1[$key] = "(G) " . $value;
					break;
			}
		}

		$new_rs = array();
		
		foreach ($rs as $key => $value) {
			if (! empty($value)) {
				foreach ($rs1 as $k => $v) {
					if ($key == $k) {
						$new_rs[$v] = $value;
					}
				}	
			}
		}

		/* To calculate the coefficient of variation 
		*  Steps 
		*  1. Calculate the mean (Xbar)
		*  2. Caluclate the standard deviation (SD)
		*  Formula : CV = SD/Xbar;
		*/

		$stm = "SELECT answer_1 as option_1, answer_2 as option_2, answer_3 as option_3, answer_4 as option_4, answer_5 as option_5, answer_6 as option_6, answer_7 as option_7 FROM response WHERE answer_code = '" . $round ."' AND question_code='".$question."'";
		$tmp_result = $connection->selectQuery($stm);
		
		// Re-arrange the results according to statistical table
		$result = array();

		if (! empty($tmp_result)) {
			foreach ($tmp_result as $key => $value) {
				foreach ($value as $k => $v) {
					if (array_key_exists($k, $result)) {
						$result[$k][] = intval($v);
					} else {
						$result[$k] = [];
						$result[$k][] = intval($v);
					}
				}
			}	
		}
		
		// Calculated squeare of (X-Xbar) for finding the standard deviation
		$mean_difference_score =array();

		foreach ($rs as $option_key => $mean) { 
			if (! empty($mean)) {
				foreach ($result as $option_k => $v) {
					if ($option_key == $option_k) {
						foreach ($v as $key => $val) {
							$mean_difference_score[$option_k][] = pow(($val - $mean), 2);
						}
					}
				}	
			}
		}

		// var_dump($mean_difference_score);

		// Calculate the standard deviation
		$standard_deviation = array();
		
		if (! empty($mean_difference_score)) {
			foreach ($mean_difference_score as $key => $value) {
				$standard_deviation[$key] = sqrt(array_sum($value) / (count($value) - 1));
			}
		}
		
		// var_dump($standard_deviation);

		// Calculated the coefficient of variation
		$coefficient_of_variation = array();

		if (! empty($standard_deviation)) {
			foreach ($rs as $option_key => $mean) {
				foreach ($standard_deviation as $option_k => $sd) {
					if ($option_key == $option_k) {
						$coefficient_of_variation[$option_k] = round(($sd/$mean), 4);	
					}
				}
			}	
		}
		
		$coff_var = array();
		foreach ($coefficient_of_variation as $key => $value) {
			if (! empty($value)) {
				foreach ($rs1 as $k => $v) {
					if ($key == $k) {
						$coff_var[$v] = $value;
					}
				}	
			}
		}

		// echo json_encode($coff_var);
		$id = $connection->selectQuery("SELECT id FROM results WHERE result_code='" . $result_code . "'");
		
		if (empty($id)) {
			$sub_string_field .= "(result_code, created, ";	
			$sub_string_value .= "('" . $result_code . "', '" . date('Y-m-d H:i:s') . "', ";
			end($rs);
			$lst_idx = key($rs);

			
			foreach ($rs as $key => $value) {
				$sub_string_field .= $key . "_mean";	
				$sub_string_value .= "'" . $value . "'";

				if ($key != $lst_idx) {
					$sub_string_field .= ", ";
					$sub_string_value .= ", ";
				}
			}

			end($coefficient_of_variation);
			$lst_idx = key($coefficient_of_variation);

			$sub_string_field .= ", ";	
			$sub_string_value .= ", ";
			foreach ($coefficient_of_variation as $key => $value) {
				$sub_string_field .= $key . "_CV";	
				$sub_string_value .= "'" . $value . "'";

				if ($key == $lst_idx) {
					$sub_string_field .= ")";
					$sub_string_value .= ")";
				} else {
					$sub_string_field .= ", ";
					$sub_string_value .= ", ";
				}
			}
			
			$stm = "INSERT INTO results " . $sub_string_field . " VALUES " . $sub_string_value;
			if (! empty($coefficient_of_variation)) {
				$connection->createQuery($stm);
			}
		} else {
			$sub_stm = "updated= '" .  date('Y-m-d H:i:s') . "', ";
			end($rs);
			$lst_idx = key($rs);

			foreach ($rs as $key => $value) {
				$sub_stm .= $key . "_mean = '" . $value . "'";

				if ($key != $lst_idx) {
					$sub_stm .= ", ";
				}
			}

			end($coefficient_of_variation);	
			$lst_idx = key($coefficient_of_variation);
			$sub_stm .= ", ";
			
			foreach ($coefficient_of_variation as $key => $value) {
				$sub_stm .= $key . "_CV = '" . $value . "'";

				if ($key != $lst_idx) {
					$sub_stm .= ", ";
				}
			}
			$stm = "UPDATE results SET " . $sub_stm . " WHERE id = " . $id['id'] . " AND result_code = '" . $result_code . "'";
			$connection->createQuery($stm);
		}
		
		$response = array();
		array_push($response, json_encode($new_rs));
		array_push($response, json_encode($coff_var));
		echo json_encode($response);
		exit();
	}



	
?>