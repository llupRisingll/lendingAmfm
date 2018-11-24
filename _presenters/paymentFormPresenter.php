<?php
class paymentFormPresenter {
    public function get(){
        View::addVar("view_title", "Payment Form");

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

    public function post(){// Parameters to be accepted
	    Params::permit(
		    "search", "order", "start", "length", "draw", "type"
	    );

	    // When getting the Transactions
	    $SERVER_RESPONSE = DataTableModel::fetchAllLoans(
		    Params::get("start"),
		    Params::get("length"),
		    Params::get("order"),
		    Params::get("draw"),
		    Params::get("search"),
		    Params::get("type")
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
    