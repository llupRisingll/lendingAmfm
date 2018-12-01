<?php

class UniLevelEarning {
	private static $treeArray = [];
	private static $yourPackage = "b";

	private static function getParentKey($parentId){
		foreach (self::$treeArray as $key => $value){
			foreach ($value as $val){
				if ($parentId == $val){
					return $key;
				}
			}
		}
		return false;
	}

	private static function fetch_unilevel_children($userID){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		SELECT up.*, l.loan_type,
		 IF(TIMESTAMPDIFF(MONTH, l.`maturity_date`, SYSDATE()) <=0, TRUE , FALSE) as mature
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

	private static function package_lower_than($packageType){
		// Value/Importance of a package Representation
		$packageSort = [
			"b" => 1,
			"s" => 2,
			"g" => 3,
			"d" => 4,
			"v" => 5,
		];

		// Check whether you invited a higher package
		return ($packageSort[$packageType] > $packageSort[self::$yourPackage]);
	}

	private static function money_value($packageType){
		switch ($packageType){
			case "b": return 10;
			case "s": return 15;
			case "g": return 20;
			case "d": return 25;
			case "v": return 30;
			default: return 10;
		}
	}

	private static function classify_tree_levels($userID, $dataArr){
		// When the level 1 does not yet exist create it
		if(!isset(self::$treeArray[1])){
			self::$treeArray[1] = [];
		}

		// Classify the nodes/ Generate Leveled Data
		foreach ($dataArr as $nodes){
			$parent = $nodes["parent"];
			$child = $nodes["desc"];

			// Exclude yourself and put your data in a variable
			if ($child == $userID){
				self::$yourPackage = $nodes["loan_type"];
				continue;
			}

			// When it is a direct invite
			if ($parent == $userID){
				array_push(self::$treeArray[1], $child);
				continue;
			}

			// The key return is the current level of our node
			$key = self::getParentKey($parent) + 1;
			if(!isset(self::$treeArray[$key])){
				self::$treeArray[$key] = [];
			}
			array_push(self::$treeArray[$key], $child);
		}
	}

	public static function compute_total_earnings($userID){
		// Fetch From the Database
		$dataArr = self::fetch_unilevel_children($userID);

		// Classify Tree Levels
		self::classify_tree_levels($userID, $dataArr);

		// Compute Total Earnings Amount
		$totalEarnings = 0;
		foreach ($dataArr as $nodes){
			$package = $nodes["loan_type"];
			$parent = $nodes["parent"];
			$child = $nodes["desc"];

			// Exclude yourself and put your data in a variable
			if ($child == $userID) continue;

			// Use your package Earning when the invitee has package higher than you..
			$currentLevel = self::getParentKey($parent);
			if ($currentLevel <= 7 && !(bool)$nodes["mature"]){
				if (self::package_lower_than($package)){
					$amountEarned = self::money_value(self::$yourPackage);
				}else{
					$amountEarned = self::money_value($package);
				}
				$totalEarnings += $amountEarned;
			}
		}

		//		print_r(self::$treeArray);
		echo "totalEarned: ", $totalEarnings;

		return $totalEarnings;
	}

	private static function upsert_uni_monthly($amount){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [

		]);

	}

	private static function update_uni_wallet($amount){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();
	}

	private static function add_uni_history($amount){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();
	}

	public static function save_information($amount){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			// TODO: Upsert uni_monthly
			self::upsert_uni_monthly($amount);
			// TODO: Update Uni_wallet
			self::update_uni_wallet($amount);
			// TODO: Add to Uni History
			self::add_uni_history($amount);

			// Commit the changes when no error found.
			$database->mysqli_commit($connection);
			return true;
		} catch(Exception $e){
			/**
			 * An exception has occurs, which means that one of our database queries
			 * failed. Print out the error message.
			 */
			echo $e->getMessage();

			//Rollback the transaction.
			$database->mysqli_rollback($connection);
			return false;
		}
	}
}