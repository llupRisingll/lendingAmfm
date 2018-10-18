<?php

class Route {
	private static $uri;
	public static $requestMethod = "GET";
	public static $domainName = "";
	private static $layoutName = "";
	public static $matchURI = "/";
	private static $uriParams = array();
	private static $configJSON = array();

	public function __construct($requestDetails) {
		// Get the HTTP REQUEST METHOD
		self::$requestMethod = $requestDetails["REQUEST_METHOD"];
		self::$domainName = $requestDetails["SERVER_NAME"];
		self::$uri = $requestDetails["REQUEST_URI"];

		// Execute Function
		$routingConfig = self::routingJSON();
		if (empty(self::$configJSON)){
			self::$configJSON = self::configJSON();
		}

		self::presenterViewLoader($routingConfig);
	}

	public static function domain(){
		return self::$domainName;
	}

	public static function config(String $key){
		if (empty(self::$configJSON)){
			self::$configJSON = self::configJSON();
		}
		return self::$configJSON[$key];
	}

	private static function routingJSON(){
		// Get the routing
		$Routing = json_decode(
			file_get_contents(ROOT.DS.'routing.json'), true
		);

		return $Routing;
	}
	
	private static function configJSON(){
		// Get the configuration
		$Config = json_decode(
			file_get_contents(ROOT.DS.'config.json'), true
		);

		return $Config;
	}

	private static function isValidMd5(String $md5){
		return preg_match('/^[a-f0-9]{32}$/', $md5);
	}

	public static function getURIParams(){
		$uri = parse_url(self::$uri);

		switch(self::$requestMethod){
			case "GET":
				// Get the query paramters and put it in $query variable
				if(isset($uri['query'])){
					parse_str($uri['query'], $query);
					self::$uriParams = $query;
				}
				break;
			case "POST":
				self::$uriParams = $_POST;
				break;
			case "PUT":
				self::$uriParams = $_PUT;
				break;
		}
		return self::$uriParams;
	}

	private static function presenterViewLoader(array $Routing){
		$uri = parse_url(self::$uri);

		// RewriteRule Algorithm for PHP
		foreach ($Routing as $pattern => $r){
			if ($pattern[0] != "~"){
				continue;
			}

			// Check if the visited URI is parallel to the pattern
			// Generate a new URI visited according to the pattern;
			$pattern = substr($pattern, 1);
			if (preg_match($pattern, $uri["path"])){
				// Generate a new URL based on the pattern just like htaccess does
				$url = preg_replace($pattern, $r, $uri["path"]);
				$new_url = parse_url($url);

				// Map the Array Dictionary when it exists within the routing table
				if (isset($Routing[$url["path"]])){
					$uri["path"] = $new_url["path"];
					$uri["query"] = $new_url["query"];
					self::$uri = $url;
				}
				break;
			}
		}

		// Check the existence of the URL
		if (!isset($Routing[$uri["path"]])){
			self::returnCode(404);
			return;
		}

		// Cache the URI and its location
		$presenter = $Routing[$uri["path"]];
		self::$matchURI = $uri["path"];
		$presenterLocation = PRESENTER_PATH .DS .$presenter;

		// Check the existence of the preenter path
		if (!file_exists($presenterLocation)) {
			// Show error message
			echo "Error $presenter does not exist.";
			exit;
		}

		$presenterName = preg_replace("/(.+)\.php$/", "$1", $presenter);
		$layoutName = preg_replace("/(.+)Presenter\.php$/", "$1", $presenter);

		// Catch Errors within the switch case
		try{
			$requestMethod = strtoupper(self::$requestMethod);

			if ($requestMethod == "GET"){
				if (isset($_GET["_method"]) && strtolower($_GET["_method"]) == "delete"){
					$presenterName::delete();
					return;
				}

				$presenterName::get();
				// Get the query parameters and put it in $query variable
				if(isset($uri['query'])){
					parse_str($uri['query'], $query);
					self::$uriParams = $query;
				}
				self::renderTwigView($layoutName);
				return;
			}

			if($requestMethod == "POST"){
				if (isset($_POST["_method"]) && strtolower($_POST["_method"]) == "put"){
					$presenterName::put();
					parse_str(file_get_contents('php://input'), $_PUT);
					self::$uriParams = $_PUT;
					return;
				}

				$presenterName::post();
				self::$uriParams = $_POST;
				return;

			}
			if($requestMethod == "PUT"){
				$presenterName::put();
				parse_str(file_get_contents('php://input'), $_PUT);
				self::$uriParams = $_PUT;
				return;
			}

			if($requestMethod == "DELETE"){
				$presenterName::delete();
				return;
			}

		}catch(Error $e){
			echo $e->getMessage();
			exit;
		}
	}

	public static function renderTwigView(String $layoutName){
		self::$layoutName = $layoutName;

		// Load the Vendor Files Automatically when called
		require_once VENDORS.DS.'autoload.php';

		$loader = new Twig_Loader_Filesystem(VIEW_PATH);

		// Use the config JSON to decide about the caching
		$twigEnvironmentArr = array();
		if (self::$configJSON["cache"]){
			$twigEnvironmentArr["cache"] = CACHE_PATH;
		}

		$twig = new Twig_Environment($loader, $twigEnvironmentArr);
		
		// Add View Variables declared inside the presenter
		foreach(View::getVars() as $key => $val){
			$twig->addGlobal($key, $val);
		}

		if (!isset(View::getVars()["body"])){
			$twig->addGlobal("body", "$layoutName/index.twig");
		}

		// Load the default Functions
		View::defaultFunctions($layoutName);
		
		// Add all of the functions
		foreach (View::listFunction() as $value) {
			$twig->addFunction($value);
		}

		// Start Rendering the view
		try{
			$template = $twig->render("barebone.twig");
			if (!View::formHelperChecker()){
				exit;
			}else{
				echo $template;
			}
		} catch(Twig_Error_Loader $e){
			echo $e->getMessage();
			exit;
		} catch(Twig_Error_Syntax $e){
			echo $e->getMessage();
			echo " Please check _layouts/$layoutName/index.twig on line ".$e->getLine();
			exit;
		}
	}

	public static function returnCode(int $code){
		if (in_array($code, array(400, 401, 403, 404, 500), true)){
			http_response_code($code);
			require_once(VIEW_PATH.DS."defaults/$code.html");
			exit;
		}
	}
}