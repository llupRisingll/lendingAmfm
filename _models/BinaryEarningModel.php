<?php

class BinaryEarningModel {
	private static $treeArray = [];
	private static $pairArray = [];

	private static function fetch_binary_children($userID){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		// FETCH THE BRANCH TREE DATA
		$sql = "
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

	public static function save_information($amount, $user_id){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			// TODO: add to bin history
			// TODO: update bin wallet

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