<?php
class WithdrawalsPresenter {
    public function get(){
	    SessionModel::restrictNotLogged();

    	View::addVar("view_title", "Withdrawal Requests");

	    // Import Jquery JS Library
	    View::addScript("http://".Route::domain()."/js/".md5("JQuery").".min.js");

	    // Import dataTable library
	    View::addScript("//".Route::domain()."/js/".md5("dataTable").".min.js");
	    View::addCSS("//".Route::domain()."/css/".md5("dataTable").".min.css");

	    // Import Bootstrap
	    View::addCSS("//".Route::domain()."/css/".md5("Bootstrap").".min.css");
	    View::addCSS("/_layouts/dashboard/css/jquery-ui.css");
	    View::addScript("//".Route::domain()."/js/".md5("Bootstrap").".min.js");
    }

    public function post(){
	    Params::permit(
		    "search", "order", "start", "length", "draw",
		    "cid", "type"
	    );

	    if (Params::get("search") != false && Params::get("order") != false &&
		    Params::get("length") != false && Params::get("draw") != false) {

		    // When getting the Transactions
		    $SERVER_RESPONSE = DataTableModel::fetchWithdrawals(
		    	Params::get("start"),
			    Params::get("length"),
			    Params::get("order"),
			    Params::get("draw"),
			    Params::get("search")
		    );

		    echo $SERVER_RESPONSE;
		    exit;
	    }

		if (Params::get("cid") != false && Params::get("type") != false){
	    	WithdrawalsModels::respond_to_transaction(Params::get("cid"), Params::get("type"));
			header("location: /withdrawals");
			exit;
		}

    }

    public function put(){
        Route::returnCode(401);
    }

    public function delete(){
        Route::returnCode(401);
    }
}
    