<?php
class DB_UnilevelEarning {

	public static function fetch_unilevel_parents($target_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$prepare = $database->mysqli_prepare($connection, "SELECT anc FROM `unipath` WHERE `desc`=:USER_ID");
		$database->mysqli_execute($prepare, array(
			":USER_ID" => $target_id
		));

		// Convert to ordinary Array since you are only fetching a single data
		$result_array = [];
		foreach ($database->mysqli_fetch_rows($prepare) as $key => $val){
			array_push($result_array, $val[0]);
		}

		return $result_array;
	}

	public static function fetch_unilevel_children($userID){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		SELECT up.*, l.loan_type,
		IF(TIMESTAMPDIFF(MONTH, l.maturity_date, SYSDATE()) >= 1,true,false) as mature
	  		FROM `unipath` up 
			INNER JOIN (SELECT MAX(lid) as lid, cid FROM `loan_info` GROUP BY `cid`) li ON up.desc = li.cid
			INNER JOIN `loan` l ON l.id=li.lid
		WHERE anc = :USER_ID";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, array(
			":USER_ID" => $userID
		));

		// Get the matched data from the database
		return $database->mysqli_fetch_assoc($prepare);
	}

	public static function generate_sql_values(Array $array_values){
		$sqlTemplate = "";
		$firstFlag = true;
		foreach ($array_values as $cid => $amount){
			// Skip the 0 amount earnings
			if ($amount == 0)
				continue;

			// Capture the first value then remove the UNION
			$union = ($firstFlag) ? "" : "UNION ALL";

			if ($firstFlag)
				$firstFlag = false;

			$string = "SELECT $amount AS `amount`, $cid AS `cid`";
			$sqlTemplate .= "$union ". (count($array_values) > 1 ? "($string)": $string). "\n";
		}

		return $sqlTemplate;
	}

	private static function upsert_uni_monthly($arrayValues){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$generatedQuery = self::generate_sql_values($arrayValues);

		// FETCH THE BRANCH TREE DATA
		$sql = "
		INSERT INTO `uni_monthly`(`amount`, `cid`) 
		SELECT amount, cid FROm
		( $generatedQuery) gs ON DUPLICATE KEY UPDATE amount = gs.amount 
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [ ]);
	}


	private static function add_uni_history($arrayValues){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$generatedQuery = self::generate_sql_values($arrayValues);

		// OLD UNI MONTHLY - NEW COMPUTED
		$sql = "
		INSERT INTO `uni_history`
			(`uwid`, `amount`, `earn_date`)
			(
			SELECT ui.uwid, (gs.amount - IFNULL(um.amount, 0)), NOW() 
				FROM uni_wallet uw 
			INNER JOIN `uni_info` ui ON ui.uwid=uw.id 
			INNER JOIN ( $generatedQuery) gs ON gs.cid = ui.cid
		 	LEFT JOIN `uni_monthly` um ON um.cid = gs.cid

			WHERE (gs.amount - IFNULL(um.amount, 0)) > 0
			)
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [ ]);
	}

	private static function update_uni_wallet($arrayValues){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$generatedQuery = self::generate_sql_values($arrayValues);

		// FETCH THE BRANCH TREE DATA
		$sql = "
		UPDATE `uni_wallet` as uw 
		INNER JOIN `uni_info` as ui ON ui.uwid=uw.id 
		INNER JOIN ( $generatedQuery) gs ON gs.cid = ui.cid
			SET uw.`amount`=gs.amount,
				uw.`balance` = (gs.amount-uw.`paid`) 
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [ ]);
	}

	public static function save_information($arrayValues){
		self::add_uni_history($arrayValues);
		self::upsert_uni_monthly($arrayValues);
		self::update_uni_wallet($arrayValues);
	}
}