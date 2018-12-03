<?php

class BinaryEarningModel {
	private static $treeArray = [];
	private static $pairArray = [];

	private static function fetch_binary_children($userID){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		SELECT a.binparent, a.id, b.lside, b.parent, b.desc, b.anc FROM `accounts` a 
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
		INSERT INTO `bin_history`
			(`bwid`, `amount`, `earn_date`)
			(
			SELECT bi.bwid, ($amount - bw.amount), NOW() 
				FROM bin_wallet bw 
			INNER JOIN `bin_info` bi ON bi.bwid=bw.id 
			WHERE bi.cid=:CLIENT_ID  AND ($amount - bw.amount) > 0
			)
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [
			":CLIENT_ID" => $user_id
		]);
	}

	private static function classify_tree_levels($userID, $treeArray, $loop_handler=[]){
		// When the level 1 does not yet exist create it
		if(!isset(self::$treeArray[1])){
			self::$treeArray[1] = [];
		}

		// Classify the nodes/ Generate Leveled Data
		foreach ($treeArray as $nodes){
			$parent = $nodes["parent"];
			$child = $nodes["desc"];

			// Exclude yourself and put your data in a variable
			if ($child == $userID){
				continue;
			}

			if (isset($loop_handler) && !empty($loop_handler)){
				$loop_handler($nodes);
			}

			// When it is a direct invite
			if ($parent == $userID){
				array_push(self::$treeArray[1], $child);
				continue;
			}

			// The key return is the current level of our node
			$key = ExtendedFunctions::get_parent_level(self::$treeArray,$parent) + 1;

			// Add the child
			self::$treeArray[$key][] = $child;
		}
	}

	private static function classify_tree_parents($nodes){
		$parent = $nodes["parent"];
		$child = $nodes["desc"];

		// Add the child;
		self::$pairArray[$parent][] = $child;
	}

	private static function update_bin_wallet($amount, $user_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
		UPDATE `bin_wallet` as bw 
		INNER JOIN `bin_info` as bi ON bi.bwid=bw.id 
			SET bw.`amount`=:AMOUNT, 
				bw.`balance` = (:AMOUNT-bw.`paid`)
		WHERE bi.cid=:CLIENT_ID
		";

		$prepare = $database->mysqli_prepare($connection, $sql);
		$database->mysqli_execute($prepare, [
			":CLIENT_ID" => $user_id,
			":AMOUNT" => $amount
		]);
	}

	private static function earn_direct_from($nodes, $userID){
		if ($nodes["binparent"] == $userID){
			return 100;
		}

		return 0;
	}

	private static function compute_pair_earnings(){
		$totalEarnings = 0;
		foreach (self::$pairArray as $pairs){
			// Constraints
			if (count($pairs) != 2)
				continue;

			$currentLevel = ExtendedFunctions::get_parent_level(self::$treeArray,$pairs[0]);

			if ($currentLevel <= 7){
				$totalEarnings += 1000;
				continue;
			}

			if ($currentLevel <=12){
				$totalEarnings += 500;
				continue;
			}

			$totalEarnings += 300;
		}

		return $totalEarnings;
	}

	private static $directEarnings = 0;
	public static function compute_total_earnings($userID){
		// Fetch From the Database
		$dataArr = self::fetch_binary_children($userID);

		// Classify Tree Levels
		self::classify_tree_levels($userID, $dataArr, function ($nodes) use ($userID){
			self::classify_tree_parents($nodes);
			$amount = self::earn_direct_from($nodes, $userID);
			self::$directEarnings += $amount;
		});

		// Compute Total Earnings Amount in Pairs
		$pairEarning = 0;
		$pairEarning += self::compute_pair_earnings();
		$totalEarnings = $pairEarning + self::$directEarnings;

		foreach ($dataArr as $nodes){
			$parent = $nodes["parent"];
			$child = $nodes["desc"];

			// Exclude yourself and put your data in a variable
			if ($child == $userID) continue;

			// Use your package Earning when the invitee has package higher than you..
			$currentLevel = ExtendedFunctions::get_parent_level(self::$treeArray,$parent);
			if ($currentLevel <= 7 && !(bool)$nodes["mature"]){

			}
		}

		echo "totalEarned: ", $totalEarnings;
		return $totalEarnings;
	}

	public static function save_information($amount, $user_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			self::add_bin_history($amount, $user_id);
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