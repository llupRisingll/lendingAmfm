<?php
class PaymentModel {
	public static function clientPay($loan_id, $amount){
		// Database connection
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			// Add payment history
			$prepared = $database->mysqli_prepare($connection, "
				INSERT INTO `payment_history`
						(`loan_id`, `amount`, `date_paid`) 
				VALUES (:LOAN_ID, :AMOUNT, NOW())
	        ");

			$database->mysqli_execute($prepared, array(
				":LOAN_ID" => $loan_id,
				":AMOUNT" => $amount
			));

			// Update the `loan` table
			$prepared = $database->mysqli_prepare($connection, "
				UPDATE 	`loan` SET
	                  	`loan_paid`=`loan_paid` + :AMOUNT,
						`loan_balance`=`gross_loan` - (`loan_paid`),
						 `paid`=IF(`loan_balance` = 0, 1, 0)
				WHERE `id`=:LOAN_ID
	        ");

			$database->mysqli_execute($prepared, array(
				":LOAN_ID" => $loan_id,
				":AMOUNT" => $amount
			));

			// Commit the changes when no error found.
			$database->mysqli_commit($connection);
			return true;
		} catch(Exception $e){
			/**
			 * An exception has occur, which means that one of our database queries
			 * failed. Print out the error message.
			 */
			echo $e->getMessage();

			//Rollback the transaction.
			$database->mysqli_rollback($connection);
			return false;
		}
	}
}