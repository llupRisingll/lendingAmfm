<?php
class SessionModel {
	// Start the Session
	public static function start(){
		if(session_status() != PHP_SESSION_ACTIVE){
			session_name("_sA");
			session_start();
		}
	}
	// Get the Login Status based on username
	public static function getLoginStatus(){
		self::start();
		return isset($_SESSION["hash"]);
	}
	// Restrict Logged User
	public static function restrictLogged(){
		self::start();
		// Disallow: User Visit if it is already logged in
		if (self::getLoginStatus()){
			header("location: /");
			exit;
		}
	}
	// Restrict Guest
	public static function restrictNotLogged(){
		self::start();
		// Disallow: User Visit if it is not yet logged in
		if (!self::getLoginStatus()){
			header("location: /login");
			exit;
		}
	}
	// Log a user
	public static function setUser(){
		self::start();
		$_SESSION["hash"] = "you are now login motherfucker";
	}

	/* ======= END SETTERS ======= */


	/* ======= START GETTERS ======= */

	// Get Logged user
	public static function getUser(){
		self::start();
		return $_SESSION["hash"];
	}
	// Destroy the session
	public static function destroy(){
		self::start();

		// Destroy the current Session
		session_destroy();
	}
}
