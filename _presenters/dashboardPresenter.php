<?php
class dashboardPresenter {
    // HTTP Header Method: GET
    // Used to retrive a data or a view
    public function get(){
        View::addVar("view_title", "Dashboard");

        View::addScript("/_layouts/dashboard/js/jquery.min.js");
        View::addScript("/_layouts/dashboard/js/jquery.easing.js");

        View::addCSS("http://".Route::domain()."/css/".md5("Bootstrap").".min.css");
        View::addScript("http://".Route::domain()."/js/".md5("Bootstrap").".min.js");

    }

    // HTTP Header Method: POST
    // Usually used when to insert a new data
    public function post(){
        Route::returnCode(401);
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
    