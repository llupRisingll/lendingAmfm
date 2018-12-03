<?php
class WithdrawalsModels {

	public static function respond_to_transaction($userID, $type){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		if ($type == "approve"){
			$database->mysqli_begin_transaction($connection);

			try {
				// Check the remaining balance
				$prepare = $database->mysqli_prepare($connection, "
				SELECT `amount` FROM `withdrawal_request` WHERE `cid`=:USER_ID
			");
				$database->mysqli_execute($prepare, array(
					":USER_ID" => $userID
				));
				$balance = $database->mysqli_fetch_assoc($prepare)[0]["amount"];

				// Add to history
				$prepare = $database->mysqli_prepare($connection, "
				INSERT INTO `withdrawal_history`(`cid`, `amount`, `trans_date`) VALUES (:USER_ID,:AMOUNT,now())
				");
				$database->mysqli_execute($prepare, array(
					":USER_ID" => $userID,
					":AMOUNT" => $balance
				));

				// Delete request
				$prepare = $database->mysqli_prepare($connection, "
				DELETE FROM `withdrawal_request` WHERE `cid`=:USER_ID
			");
				$database->mysqli_execute($prepare, array(
					":USER_ID" => $userID
				));

				// Commit the changes when no error found.
				$database->mysqli_commit($connection);
				return true;

			} catch(Exception $e){
				echo $e->getMessage();

				//Rollback the transaction.
				$database->mysqli_rollback($connection);
				return false;
			}
			return;
		}

		if ($type == "decline"){

			$database->mysqli_begin_transaction($connection);

			try {
				// Check the remaining balance
				$prepare = $database->mysqli_prepare($connection, "
				SELECT `amount` FROM `withdrawal_request` WHERE `cid`=:USER_ID
			");
				$database->mysqli_execute($prepare, array(
					":USER_ID" => $userID
				));
				$balance = $database->mysqli_fetch_assoc($prepare)[0]["amount"];

				// Increase e_wallet amount
				$prepare = $database->mysqli_prepare($connection, "
				UPDATE `e_wallet` SET `amount`=`amount`+:AMOUNT WHERE `cid`=:USER_ID;
			");
				$database->mysqli_execute($prepare, array(
					":USER_ID" => $userID,
					":AMOUNT" => $balance
				));

				// Delete request
				$prepare = $database->mysqli_prepare($connection, "
				DELETE FROM `withdrawal_request` WHERE `cid`=:USER_ID
			");
				$database->mysqli_execute($prepare, array(
					":USER_ID" => $userID
				));

				// Commit the changes when no error found.
				$database->mysqli_commit($connection);
				return true;

			} catch(Exception $e){
				echo $e->getMessage();

				//Rollback the transaction.
				$database->mysqli_rollback($connection);
				return false;
			}
		}
	}

}