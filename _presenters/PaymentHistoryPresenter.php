<?php
class PaymentHistoryPresenter {
    public function get(){
	    View::addVar("view_title", "Payment History");

	    View::addVar("view_title", "Account List");

	    // Import Jquery JS Library
	    View::addScript("http://".Route::domain()."/js/".md5("JQueryOnly").".min.js");

	    // Import dataTable library
	    View::addScript("//".Route::domain()."/js/".md5("dataTable").".min.js");
	    View::addCSS("//".Route::domain()."/css/".md5("dataTable").".min.css");

	    // Import Bootstrap
	    View::addCSS("//".Route::domain()."/css/".md5("Bootstrap").".min.css");
	    View::addScript("//".Route::domain()."/js/".md5("Bootstrap").".min.js");
    }

    public function post(){
	    Params::permit(
		    "search", "order", "start", "length", "draw"
	    );


	    // When getting the Transactions
	    $SERVER_RESPONSE = DataTableModel::fetchLoanHistory(
		    Params::get("start"),
		    Params::get("length"),
		    Params::get("order"),
		    Params::get("draw"),
		    Params::get("search")
	    );

	    echo $SERVER_RESPONSE;
	    exit;
    }

    // HTTP Header Method: PUT
    // Usually used when about to update a data
    public function put(){
        Route::returnCode(401);
    }

    // HTTP Header Method: DELETE
    // Usually used when about to delete a data
    public function delete(){
        Route::returnCode(401);
    }
}
    