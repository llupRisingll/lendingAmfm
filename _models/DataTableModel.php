<?php
class DataTableModel {

	/**
	 * Get the pending Transaction from database
	 * @param String $start
	 * @param String $length
	 * @param String $order
	 * @param String $draw
	 * @param String|null $search
	 */
	public static function getPendingTransactions($start, $length, $order, $draw, $search=null){
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();


		// DataTable Arrangement according to its index usage on the dataTable
		$columns = array("id", "user_id", "parent_id", "type");


		/**
		 * GET ALL OF THE DATA WITHOUT FILTERING
		 */
		// Select all of the data in the pending table
		$sql = "SELECT `id` FROM `pending_requests` WHERE 1";

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare);

		// Get the total amount of data without any filter or search
		$totalData = $database->mysqli_num_rows($prepare);


		/**
		 * GET ALL OF THE DATA WITH SEARCH FILTER
		 */
		// Generate SQL according to parameters
		$sql = "SELECT `id`, `user_id`, `parent_id`, `type` FROM `pending_requests` WHERE 1";
		$dict = array();

		if (!empty($search["value"])){
			$sql .= " AND `type` LIKE %:SEARCH_STRING%";
			$dict[":SEARCH_STRING"] = Params::get("search")["value"];
		}

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, $dict);

		// Get the total amount of data without any filter or search
		$totalFiltered = $database->mysqli_num_rows($prepare);

		/**
		 * GET ALL OF THE DATA WITH SEARCH FILTER, ORDERING AND PAGE LIMITATION
		 */
		$sql .= " ORDER BY :COLUMN_NAME :DIRECTION LIMIT $start, $length";
		$dict[":COLUMN_NAME"] = $columns[$order[0]["column"]];
		$dict[":DIRECTION"] = $order[0]["dir"];

		// Process of querying data
		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, $dict);

		// Prepare the dataArray algorithm
		$dataArr = array();
		foreach($database->mysqli_fetch_assoc($prepare) as $row){
			$nestedData = array(
				$row["id"], $row["user_id"], $row["parent_id"], $row["type"], null
			);

			array_push($dataArr, $nestedData);
		}


		// Return the generated JSOn
		return self::generateJSON($draw, $totalData, $totalFiltered, $dataArr);
	}


	/**
	 * Generate JSON from the given parameters
	 * @param $draw
	 * @param $totalData
	 * @param $totalFiltered
	 * @param $data
	 * @return array
	 */
	public static function generateJSON($draw, $totalData, $totalFiltered, $data){

		// Return the JSON data
		$generatedData = array(
			// For every request/draw by client-side , they send a number as a parameter.
			// When they receive a response/data they first check the draw number, so we are sending same number in draw.
			"draw" => intval($draw),

			// Total Number of Records
			"recordsTotal"=> intval( $totalData ),

			// Total number of records after searching, if there is no searching then the totalFiltered is the totalData
			"recordsFiltered" => intval( $totalFiltered ),

			// All of the data Array
			"data" => $data
		);

		return json_encode($generatedData);
	}
}
    