<?php
class loginPresenter {
    public function get(){
    	SessionModel::restrictLogged();

        View::addVar("view_title", "Lending login Page");
        View::addVar("BODY_CLASS", "bg-light");
        View::addVar("fc", \Plugins\FlashCard::getFlashCard()[0]);
        View::addCSS("/_layouts/login/login.css");
        View::addCSS("http://".Route::domain()."/css/".md5("Bootstrap").".min.css");

        View::addScript("/_layouts/Home/js/jquery.min.js");
        View::addScript("/_layouts/Home/js/bootstrap.min.js");
    }

    // HTTP Header Method: POST
    // Usually used when to insert a new data
    public function post(){
    	Params::permit("password");

    	if (Params::get("password") == Route::config("password")){
			SessionModel::setUser();

		    header("location: /");
		    exit;
	    }else{
    		\Plugins\FlashCard::setFlashCard("invalid", "/login", "Sorry Invalid Password");

    		header("location: /login");
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
    