<?php
class WithdrawalsPresenter {
    public function get(){
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
		    "search", "order", "start", "length", "draw"
	    );

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

    public function put(){
        Route::returnCode(401);
    }

    public function delete(){
        Route::returnCode(401);
    }
}
    