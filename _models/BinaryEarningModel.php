<?php

class BinaryEarningModel {
	private static $treeArray = [];
	private static $pairArray = [];

	private static function fetch_binary_children($userID){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		SELECT a.binparent, a.id, b.lside, b.parent FROM `accounts` a 
			JOIN `binpath` b
    			ON (a.id=b.`desc`)
    		WHERE b.anc = :USER_ID AND  a.id != :USER_ID
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, array(
			":USER_ID" => $userID
		));

		// Get the matched data from the database
		return $database->mysqli_fetch_assoc($prepare);
	}

	private static function add_bin_history($amount, $user_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		INSERT INTO `uni_history`
			(`uwid`, `amount`, `earn_date`)
			(
			SELECT ui.uwid, ($amount - uw.amount), NOW() 
				FROM uni_wallet uw 
			INNER JOIN `uni_info` ui ON ui.uwid=uw.id 
			WHERE ui.cid=:CLIENT_ID
			)
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [
			":CLIENT_ID" => $user_id,
			":AMOUNT" => $amount
		]);
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
			$key = ExtendedFunctions::get_parent_level(self::$treeArray,$parent) + 1;
			if(!isset(self::$treeArray[$key])){
				self::$treeArray[$key] = [];
			}
			array_push(self::$treeArray[$key], $child);
		}
	}

	private static function update_bin_wallet($amount, $user_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		UPDATE `uni_wallet` as uw 
		INNER JOIN `uni_info` as ui ON ui.uwid=uw.id 
		SET uw.`amount`=:AMOUNT WHERE ui.cid=:CLIENT_ID
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [
			":CLIENT_ID" => $user_id,
			":AMOUNT" => $amount
		]);
	}


	public static function compute_total_earnings($userID){
		// Fetch From the Database
		$dataArr = self::fetch_binary_children($userID);

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
			$currentLevel = ExtendedFunctions::get_parent_level(self::$treeArray,$parent);
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

	public static function save_information($amount, $user_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			// TODO: add to bin history
			self::add_bin_history($amount, $user_id);
			// TODO: update bin wallet
			self::update_bin_wallet($amount, $user_id);

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