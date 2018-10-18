<?php

class CLIHandler{

    public static function init($argv){
    	// Make sure that you receive an argument.
	    if (count($argv) < 1){
	    	echo "No Command Found";
	    	exit;
	    }

        @ $str1 = $argv[1];
        @ $str2 = $argv[2];
        @ $str3 = $argv[3];
        @ $str4 = $argv[4];

        /***
         * Command: rut/rut create -p [PresenterName]
         */
        if ($str1 == "create"){
            if ($str2 == "-p"){
                $presenterName = $str3;
                $splitName = explode(" ",ucwords($presenterName));

                if (count($splitName) > 1){
                    $userInput = fopen ("php://stdin","r");
                    $presenterClassName = implode("", $splitName);

                    // Information
                    echo "Your presenter will be named: " .$presenterClassName. "Presenter";
                    echo "\r\n";

                    // Ask for user input
                    echo "Do you wish to continue (Y/N)? ";
                    $answer = fgets($userInput);

                    // Generate template if yes
                    if(trim($answer) == 'y' || trim($answer) == "Y"){
                        require_once 'Generate.php';
                        Generate::presenter($presenterClassName);
                        Generate::layout($presenterClassName);
                    }
                    exit;
                }

                require_once 'Generate.php';
                Generate::presenter($presenterName);
                Generate::layout($presenterName);
                exit;
            }

            /**
             * Command: rut/rut create -m [ModelName]
             */
            if ($str2 == "-m"){
                $modelName = ucfirst($str3);
                $splitName = explode(" ",ucwords($modelName));

                if (count($splitName) > 1){
                    $userInput = fopen ("php://stdin","r");
                    $modelClassName = implode("", $splitName);

                    // Information
                    echo "Your model will be named: " .$modelClassName. "Model";
                    echo "\r\n";

                    // Ask for user input
                    echo "Do you wish to continue (Y/N)? ";
                    $answer = fgets($userInput);

                    // Generate template if yes
                    if(trim($answer) == 'y' || trim($answer) == "Y"){
                        require_once 'Generate.php';
                        Generate::model($modelClassName);
                    }
                }else{
                    require_once 'Generate.php';
                    Generate::model($modelName);
                }
                exit;
            }
        }
        /**
         * Command: rut/rut route [URI] [PresenterName]
         */
        if ($str1 == "route"){

            // Load the Global Variable File Container
            require_once "global_vars.php";

            $URIPath = $str2; $PresenterName = $str3;

            // Check the key-value pair in JSON
            $file = file_get_contents(ROOT.DS."routing.json");
            $assetsArr = json_decode($file, true);

            // Show Error or Append and prettify
            if (isset($assetsArr[$URIPath]) && $str4 !== "-r"){
                echo "Sorry the following path already exists:\r\n\t$URIPath";
            }else{
				$assetsArr[$URIPath] = $PresenterName;
	            $assetsArr = json_encode($assetsArr, JSON_PRETTY_PRINT);
	            file_put_contents(ROOT.DS.'routing.json', $assetsArr);
				echo "The following path was successfully added:\r\n\t$URIPath > $PresenterName";
            }
            exit;
        }

        /**
         * Command: rut/rut clean
         */
        if($str1 == "clean"){
            require_once "global_vars.php";

            function recursiveRemoveDirectory($directory) {
                foreach(glob("{$directory}/*") as $file) {
                    if(is_dir($file)) {
                        recursiveRemoveDirectory($file);
                    } else {
                        unlink($file);
                    }
                }
            }
            recursiveRemoveDirectory(RUT.DS."cached");
            exit;
        }

        /**
         * Command: rut/rut compile
         */

        if ($str1 == "compile"){
            require_once "global_vars.php";

            self::compile(VIEW_PATH);
	        self::compile(ROOT.DS."_compile");
	        exit;
        }

        echo "Rut Command not found";
        exit;
    }

    private static function compile($path){

	    // Get Directories inside Layout folder
	    foreach (glob($path.DS."*") as $file){
		    if (!is_dir($file))
			    continue;

		    $jsonPath = $file.DS."assets.json";
		    $presenterName = explode(DS, $file);
		    $presenterName = $presenterName[count($presenterName) - 1];

		    // Check sure that the JSON within the path exists
		    if (!file_exists($jsonPath))
			    continue;

		    // Check the key-value pair in JSON
		    $jsonFile = file_get_contents($jsonPath);
		    $assetsArr = json_decode($jsonFile, true);

		    // Make sure that the css Key exist and has a value
		    if (isset($assetsArr["css"]) && count($assetsArr["css"]) > 0){
			    echo "Compressing CSS ".$jsonPath."...\n";

			    // Combine array to be able to used in the command prompt
			    $CSSLinks = "";
			    foreach ($assetsArr["css"] as $css)
				    $CSSLinks .= $path.DS."$css ";

			    // Command Options
			    $shellOutput = array();
			    $uglifyCSSPath = "rut/resources/uglifycss";
			    $outputPathCSS = "public/css/".md5($presenterName).".min.css";

			    // Command Execution
			    exec("\"$uglifyCSSPath\" $CSSLinks --ugly-comments --output $outputPathCSS", $shellOutput);

			    // Output Message
			    if (count($shellOutput < 0))
				    echo "Successfully compressed CSS files within `".$presenterName."` Directory\n";
		    }

		    // Make sure that the js Key exist and has a value
		    if (isset($assetsArr["js"]) && count($assetsArr["js"]) > 0){
			    echo "Compressing JS ".$jsonPath."...\n";

			    // Combine array to be able to used in the command prompt
			    $JSLinks = "";
			    foreach ($assetsArr["js"] as $js)
				    $JSLinks .= $path.DS."$js ";

			    // Command Options
			    $shellOutput = array();
			    $uglifyJSPath = "rut/resources/uglifyjs";
			    $outputPathJS = "public/js/".md5($presenterName).".min.js";

			    // Command Execution
			    exec("\"$uglifyJSPath\" $JSLinks --compress --mangle toplevel --output $outputPathJS", $shellOutput);

			    // Output Message
			    if (count($shellOutput < 0))
				    echo "Successfully compressed JS files within `".$presenterName."` Directory\n";
		    }

		    // Create New md5 time hash in the config
		    $file = file_get_contents(ROOT.DS."config.json");
		    $assetsArr = json_decode($file, true);
		    $assetsArr["revisionID"] = md5(time());
		    $assetsArr = json_encode($assetsArr, JSON_PRETTY_PRINT);
		    file_put_contents(ROOT.DS.'config.json', $assetsArr);
	    }
    }

}