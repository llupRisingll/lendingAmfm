<?php

class TransactionsModel {
	public static function declineTransaction(String $tid){
		// DELETE FROM DATABASE algorithm
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		try {
			// Save the account auth data
			$prepared = $database->mysqli_prepare($connection, "
                DELETE FROM `pending_requests` WHERE `id`=:TRANSACTION_ID;
            ");

			$database->mysqli_execute($prepared, array(
				":TRANSACTION_ID" => $tid,
			));

			return true;
		} catch(Exception $e){
			/**
			 * An exception has occured, which means that one of our database queries
			 * failed. Print out the error message.
			 */
			echo $e->getMessage();
			return false;
		}
	}

	public static function approveTransaction(String $tid){

	}
}

