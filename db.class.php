<?php 

	/**
	* Database connection
	* @author dlamichhane
	* @version 0.1 
	* 
	* 04 Oct 2013
	*/

	
	class DBConnection
	{
		//Localhost
		const HOST_NAME = "localhost"; // https://mysqladmin.ipage.com/mysqladmin/index.php
		const USER_NAME = "root"; // delphi_1
		const PASSWORD = "root";  // passcode
		const DB_NAME = "delphi"; //delphi
		//Server
		// const HOST_NAME = "dlamichhanecom.ipagemysql.com"; // https://mysqladmin.ipage.com/mysqladmin/index.php
		// const USER_NAME = "delphi_1"; // delphi_1
		// const PASSWORD = "passcode";  // passcode
		// const DB_NAME = "delphi"; //delphi
		var $connection;


		public function db_connection()
		{
			$connection = mysql_connect(DBConnection::HOST_NAME, DBConnection::USER_NAME, DBConnection::PASSWORD);

			if (! $connection) {
				die("Cannot connect to database");
			} else {
				$this->$connection =  $connection;
			}
		}

		public function selectDb()
		{
			mysql_select_db(DBConnection::DB_NAME);

			if (mysql_error()) {
				die("Cannot find this database :" . DBConnection::DB_NAME);
			}
		}

		public function closeConnection()
		{
			$response = mysql_close($this->connection);
			return $response;
		}

		public function selectQuery($stm)
		{

			$resource_result =  mysql_query($stm);

			if (! $resource_result) {
				die("Invalid query :" . mysql_error());
			}

			$result = array();
			if (mysql_num_rows($resource_result) == 0) {
			    return false;
			} else if (mysql_num_rows($resource_result) == 1) {
				return mysql_fetch_assoc($resource_result);
			} else if (mysql_num_rows($resource_result) > 1) {
				while ($row = mysql_fetch_assoc($resource_result)) {
					array_push($result, $row);
				}
			}
			return $result;
		}

		public function createQuery($stm)
		{
			$resource_result =  mysql_query($stm);

			if (! $resource_result) {
				die("Invalid query :" . mysql_error());
			} else {
				return $resource_result;
			}
		}

		public function deleteQuery($stm) {
			$resource_result =  mysql_query($stm);

			if (! $resource_result) {
				die("Invalid query :" . mysql_error());
			}
		}
	}

?>