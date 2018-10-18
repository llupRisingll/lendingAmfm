<?php
// Load the Vendor Files Automatically when called
require_once VENDORS.DS.'autoload.php';

class View{
    
    private static $variableList = array();
    private static $twigFunction = array();
	private static $layoutName = "";
	private static $addedMarkup = "";

	// +1 when opening tag, -1 when closing a tag.
	// If > 0 then "There is an unclosed tag"
	// else if < 0 there is missing opening tag
	private static $formHelpersCount = 0; 
    
	private static function isAssoc(array $arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
    public static function addVar(String $key, $value){
        self::$variableList[$key] = $value;
        
        return true;
    }

    public static function getVars(){
        return self::$variableList;
    }

    public static function tokenProtection(bool $state){

    }

    public static function addFunction(Twig_Function $function){
        array_push(self::$twigFunction, $function);
    }
    
    public static function listFunction(){
        return self::$twigFunction;
	}
	
	public static function formHelperChecker(){
		if(self::$formHelpersCount > 0){
			echo "Seems there was an unclosed `form` tag";
			return false;
		}else
		if (self::$formHelpersCount < 0){
			echo "Seems there was excess `form` closing tag";
			return false;
		}
		else{
			return true;
		}
	}

	public static function addScript($link, $raw=false){
		if ($raw) {
			$minifiedScript = file_get_contents(ROOT.DS.$link);
			self::$addedMarkup .= "<script type='text/javascript'>$minifiedScript</script>";
		}else{
			$revisionID = Route::config("revisionID");
			self::$addedMarkup .= "<script type=\"text/javascript\" src=\"$link?r=$revisionID\"></script>";
		}
	}

	public static function addCSS($link, $raw=false){
		if ($raw){
			$minifiedCSS = file_get_contents(ROOT.DS.$link);
			self::$addedMarkup .= "<style type='text/css'>$minifiedCSS</style>";
		}else{
			$revisionID = Route::config("revisionID");
			self::$addedMarkup .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$link?r=$revisionID\">";
		}
	}

    public static function defaultFunctions($layoutName){
        self::$layoutName = $layoutName;

	    // Import added Markup
		$default_assets = new Twig_Function('default_assets', function () {
			return new Twig_Markup(self::$addedMarkup, 'UTF-8');
        });
        
        // <form>
        $formHelperStart = new Twig_Function('form_tag', function ($action, $method = "get", $class=null, $id=null, $attr=array()) {			
			$newMethod = strtoupper($method);
			if ($newMethod == "DELETE"){
				$newMethod = "GET";
			}elseif($newMethod == "PUT"){
				$newMethod = "POST";
			}
			
			$markup = "<form method='$newMethod' action='$action'";
			
			// add class to the tag
			if ($class != null) 
				$markup .= " class='$class'";

			// Add id to the tag
			if ($id != null) 
				$markup .= " id='$id'";

			if (count($attr) > 0 && self::isAssoc($attr))
				foreach ($attr as $key => $value)
					$markup .= " $key='$value'";
			
			$markup .= ">";

			// Add Token Protection
			if ($method == "PUT" || $method == "DELETE"){
				$markup .= "\n<input type='hidden' name='_method' value='$method'>";
				$markup .= "\n<input type='hidden' name='_token' value='tokenhere'>";
			}

			self::$formHelpersCount++;
			return new Twig_Markup($markup, 'UTF-8');
        });

        // </form>
        $formHelperEnd = new Twig_Function('end_form_tag', function () {
			$markup = "</form>";
			self::$formHelpersCount--;
			return new Twig_Markup($markup, 'UTF-8');
		});
		
		$formToken = new Twig_Function('form_token', function($lock_to = null){
			if(session_status() != PHP_SESSION_ACTIVE){
				session_start();
			}

			if (empty($_SESSION['token'])) {
                $_SESSION['token'] = bin2hex(random_bytes(32));
            }

            if (empty($lock_to)) {
                return $_SESSION['token'];
			}
			
			return hash_hmac('sha256', $lock_to, $_SESSION['token']);
		});

		// Activate the functions
        self::addFunction($formHelperStart);
        self::addFunction($formHelperEnd);
        self::addFunction($default_assets);
        self::addFunction($formToken);
    }
}