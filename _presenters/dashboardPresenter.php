<?php
class dashboardPresenter {
    // HTTP Header Method: GET
    // Used to retrive a data or a view
    public static function get(){
        View::addVar("view_title", "Dashboard");

        // Import Jquery JS Library
	    View::addScript("http://".Route::domain()."/js/".md5("JQueryOnly").".min.js");

	    // Import dataTable library
	    View::addScript("//".Route::domain()."/js/".md5("dataTable").".min.js");
	    View::addCSS("//".Route::domain()."/css/".md5("dataTable").".min.css");

	    // Import Bootstrap
	    View::addCSS("//".Route::domain()."/css/".md5("Bootstrap").".min.css");
	    View::addScript("//".Route::domain()."/js/".md5("Bootstrap").".min.js");
    }

    // HTTP Header Method: POST
    // Usually used when to insert a new data
    public static function post(){
    	Params::permit("search", "order", "start", "length", "draw");

    	$SERVER_RESPONSE = DataTableModel::getPendingTransactions(
    		Params::get("start"),
		    Params::get("length"),
		    Params::get("order"),
		    Params::get("draw"),
		    Params::get("search")
	    );

    	echo $SERVER_RESPONSE;

    }

    // HTTP Header Method: PUT
    // Usually used when about to update a data
    public static function put(){
        Route::returnCode(401);
    }

    // HTTP Header Method: DELETE
    // Usually used when about to delete a data
    public static function delete(){
        Route::returnCode(401);
    }
}
    