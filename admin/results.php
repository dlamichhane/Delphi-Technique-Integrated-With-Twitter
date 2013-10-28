<?php
	require_once 'constant.php';
	
	session_start();
	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	}

	require_once '../db.class.php';
?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Delphi technique using social media</title>
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap-responsive.css">
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/application.css">
		<script src="../js/jquery-2.0.3.min.js"></script>
		<script src="../highcharts/highcharts.js"></script>
		<script src="../highcharts/exporting.js"></script>
		<script src="../highcharts/highcharts_custom.js"></script>
		<script src="../js/chart.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="row-fluid">
				<div class="span3">
					<a href="<?php echo ADMIN_BASE_PATH ?>/index.php">Back to navigation</a>
				</div>
				<div class="span9">
					
				</div>
			</div>

			<div class="row-fluid">
				<div class="span3">
					<ul class="nav nav-list"><li class="divider"></li>
						<li class="nav-header">View graph and comments</li>
						<li class="divider"></li>
						<li>
							<select name="question">
								<option value="0">Choose question</option>
								<option value="Q1">Question 1</option>
								<option value="Q2">Question 2</option>
								<option value="Q3">Question 3</option>
								<option value="Q4">Question 4</option>
							</select>
						</li>
						<li>
							<select name="round">
								<option value="0">Choose round</option>
								<option value="R1">Round 1</option>
								<option value="R2">Round 2</option>
								<option value="R3">Round 3</option>
							</select>
									
						</li>
						<li>
							<select name="result_type">
								<option value="0">Choose result type</option>
								<option value="graph">Graph</option>
								<option value="comments">Comments</option>
							</select>
						</li>

						<li>
							<button class="btn btn-primary btn-small" id="show_graph">Show graph</button>		
						</li>
						<li class="divider"></li>
						<li class="nav-header">Upload graph</li>
						<li class="divider"></li>
						<form action="upload.php" method="POST" enctype="multipart/form-data">
							<li>
								<select name="question_img">
									<option value="0">Choose question</option>
									<option value="Q1">Question 1</option>
									<option value="Q2">Question 2</option>
									<option value="Q3">Question 3</option>
									<option value="Q4">Question 4</option>
								</select>
							</li>
							
							<li>
								<select name="round_img">
									<option value="0">Choose round</option>
									<option value="R1">Round 1</option>
									<option value="R2">Round 2</option>
									<option value="R3">Round 3</option>
								</select>
										
							</li>
							<li>
								<input type="file" name="image" class=""/>
							</li>
							<li>
								<button class="btn btn-primary btn-small" id="upload_graph">Upload graph</button>		
							</li>
						</form>

						<li class="divider"></li>
						<li class="nav-header">Compare coefficient of variation</li>
						<li class="divider"></li>

						<li>
							<select name="question_img">
								<option value="0">Choose question</option>
								<option value="Q1">Question 1</option>
								<option value="Q2">Question 2</option>
								<option value="Q3">Question 3</option>
								<option value="Q4">Question 4</option>
							</select>
						</li>
							
						<li>
							<select name="round_img">
								<option value="0">Choose round</option>
								<option value="R1">Round 1</option>
								<option value="R2">Round 2</option>
								<option value="R3">Round 3</option>
							</select>	
						</li>
						<li>
							<button class="btn btn-primary btn-small" id="upload_graph">Compare coefficient</button>
						</li>
					</ul>
				</div>
				<div class="span9">
					<table class="table">
						<thead>
							<tr>
								<th style="text-align:center;"><span class="label label-important">Choose question and round to view the graph</span></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto">
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div id="coefficient" style="min-width: 310px; height: 400px; margin: 0 auto">
									
									</div>
								</td>
							</tr>
							
						</tbody>
					</table>
					
					
					
				</div>
				
			</div>
		</div>
	</body>
</html>

