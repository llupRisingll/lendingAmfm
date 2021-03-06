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

	/**
	 * Approving Transaction for Binary and Uni level Algorithm
	 * @param String $tid
	 * @param $userId
	 * @param $parentID
	 * @param String $transType
	 * @param null $packType
	 * @param null $loanAmount
	 * @param int|null $loanDuration
	 * @return bool
	 */
	public static function  approveTransaction(String $tid, $userId, $parentID, String $transType, $packType=null, $loanAmount=null, int $loanDuration=null){
		$database = DatabaseModel::initConnections();
		$connection = DatabaseModel::getMainConnection();

		$database->mysqli_begin_transaction($connection);

		try {
			// Remove from the pending
			$prepared = $database->mysqli_prepare($connection,
				"DELETE FROM `pending_requests` WHERE `id`=:TRANSACTION_ID;"
			);
			$database->mysqli_execute($prepared, array(
				":TRANSACTION_ID" => $tid
			));

			// Approval of Binary Level
			if ($transType == "binary"){
				// Activate the binary account by converting to 1
				$prepared = $database->mysqli_prepare($connection,
					"UPDATE `accounts` SET `bin_active`='1', `binparent`=:PARENT_ID WHERE `id`=:USER_ID;"
				);
				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId,
					":PARENT_ID" => $parentID
				));

				// Add to the pending bin path
				$prepared = $database->mysqli_prepare($connection,
					"INSERT INTO `pending_binpath`(`invitee_id`, `invitor_id`) VALUES (:USER_ID, :PARENT_ID)"
				);
				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId,
					":PARENT_ID" => $parentID
				));

				// Create eWallet
				$prepared = $database->mysqli_prepare($connection,
					"INSERT INTO `bin_wallet`(`amount`, `balance`, `paid`) VALUES (0,0,0)"
				);
				$database->mysqli_execute($prepared, array());

				$prepared = $database->mysqli_prepare($connection,
					"INSERT INTO `bin_info`(`bwid`, `cid`) VALUES (:WALLET_ID,:CLIENT_ID)"
				);
				$database->mysqli_execute($prepared, array(
					":WALLET_ID" => $database->mysqli_insert_id($connection),
					":CLIENT_ID" => $userId
				));
			}
			// Approval of Uni level
			else{
				// Activate the uni level account
				$prepared = $database->mysqli_prepare($connection,
					"SELECT `uni_active` FROM `accounts` WHERE `id`=:USER_ID LIMIT 1;"
				);
				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId
				));

				// Prepare the dataArray algorithm by adding extra column
				$uni_active = $database->mysqli_fetch_assoc($prepared)[0]["uni_active"];

				if ($uni_active != 1){
					// Activate the uni level account
					$prepared = $database->mysqli_prepare($connection,
						"UPDATE `accounts` SET `uni_active`='1' WHERE `id`=:USER_ID;"
					);
					$database->mysqli_execute($prepared, array(
						":USER_ID" => $userId
					));

					// Add to the uni path / Plot the Data

					$prepared = $database->mysqli_prepare($connection, "
                    INSERT INTO `unipath`(`anc`, `desc`, `parent`)
						(SELECT `anc`, :USER_ID AS `desc`, :PARENT_ID AS `parent` FROM `unipath` WHERE `desc`=:PARENT_ID) 
							UNION
	                    (SELECT :USER_ID AS `enc`, :USER_ID AS `desc`, :PARENT_ID AS `parent`) 
	                        ON DUPLICATE KEY UPDATE `parent`= :PARENT_ID;
            		");
					$database->mysqli_execute($prepared, array(
						":USER_ID" => $userId,
						":PARENT_ID" => $parentID
					));
				}

				// Save the loan
				$prepared = $database->mysqli_prepare($connection, "
              		INSERT INTO `loan`
              		(`loan_type`, `loan_amount`, `monthly_due`, `gross_loan`, `loan_balance`, `loan_paid`, `lend_date`, `maturity_date`, `paid`) 
              			VALUES 
              		(:PCKG_TYPE, :LOAN_AMOUNT, :MONTHLY_DUE,:GROSS_LOAN, :LOAN_BALANCE, 0, NOW(), NOW() + INTERVAL $loanDuration MONTH, 0)
                ");

				$database->mysqli_execute($prepared, array(
					":PCKG_TYPE" => $packType,
					":LOAN_AMOUNT" => $loanAmount,
					":MONTHLY_DUE" => ceil(self::grossLoan($loanAmount, $loanDuration)/$loanDuration),
					":GROSS_LOAN" => self::grossLoan($loanAmount, $loanDuration),
					":LOAN_BALANCE" => self::grossLoan($loanAmount, $loanDuration),
				));

				// Save the loan information
				$prepared = $database->mysqli_prepare($connection, "
              		INSERT INTO `loan_info`(`lid`, `cid`) VALUES (:LOAN_ID,:USER_ID)
                ");

				$database->mysqli_execute($prepared, array(
					":USER_ID" => $userId,
					":LOAN_ID" => $database->mysqli_insert_id($connection)
				));

				// Create eWallet
				if ($uni_active != 1){
					$prepared = $database->mysqli_prepare($connection,
						"INSERT INTO `uni_wallet`(`amount`, `balance`, `paid`) VALUES (0,0,0)"
					);
					$database->mysqli_execute($prepared, array());

					$prepared = $database->mysqli_prepare($connection,
						"INSERT INTO `uni_info`(`uwid`, `cid`) VALUES (:WALLET_ID,:CLIENT_ID)"
					);
					$database->mysqli_execute($prepared, array(
						":WALLET_ID" => $database->mysqli_insert_id($connection),
						":CLIENT_ID" => $userId
					));
				}

				// Save the Earnings
				$total_earnings = UniLevelEarningModel::compute_total_earnings($userId);
				DB_UnilevelEarning::save_information($total_earnings);
			}

			// Commit the changes when no error found.
			$database->mysqli_commit($connection);
			return true;

		} catch(Exception $e){
			/**
			 * An exception has occured, which means that one of our database queries
			 * failed. Print out the error message.
			 */
			echo $e->getMessage();

			//Rollback the transaction.
			$database->mysqli_rollback($connection);
			return false;
		}
	}

	private static function grossLoan($loanAmount, $durationMonths=6){
		// Generate Total GrossLoan According to the loanAmount
		$interest = 0.05;
		return ceil((1+ ($interest * $durationMonths)) * $loanAmount);
	}
}

