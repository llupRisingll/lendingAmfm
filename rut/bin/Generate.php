<?php

// Load the Global Variable File Container
require_once "global_vars.php";

class Generate{
    public static function layout($name){
        $layoutLocation = VIEW_PATH.DS.$name;
        if (!file_exists($layoutLocation)){
            
            if (mkdir($layoutLocation, 0777, true)) {
                $layoutHTML = fopen($layoutLocation. "/index.twig", "w");
                // Write the file
                fwrite($layoutHTML, "<!-- HTML/TWIG body content here -->");
                fclose($layoutHTML);

                echo "\tcreated\t _layouts/" .$name. "/index.twig\r\n";
            }else{
                echo "\tfailed\t _layouts/" .$name. "/index.twig\r\n";
            }

        }else{
            echo "\terror\t _presenters/" .$name. "Presenter.php already exist.\r\n";
        }
    }

    public static function presenter($name){
        $name .= "Presenter" ;

        // Prepare the File Content
        $classContent = "<?php";
        $classContent .= <<<XML

class $name {
    // HTTP Header Method: GET
    // Used to retrive a data or a view
    public function get(){
        View::addVar("view_title", "$name View Page");
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
    
XML;
        $presenterLocation = PRESENTER_PATH.DS.$name. ".php";

        if (!file_exists($presenterLocation)) {   
            $presenterPHP = fopen($presenterLocation, "w");
            
            // Write the files
            fwrite($presenterPHP, $classContent);

            // Close the files
            fclose($presenterPHP);
    
            echo "\tcreated\t _presenters/" .$name. ".php\r\n";
        }else{
            echo "\terror\t _presenters/" .$name. ".php already exist.\r\n";
        }
    }

    public static function model($name){
        $name .= "Model" ;

        // Prepare the File Content
        $classContent = "<?php";
        $classContent .= <<<XML

class $name {
    public function __construct() {

    }
}
    
XML;
        $modelLocation = MODEL_PATH.DS.$name. ".php";

        if (!file_exists($modelLocation)) {   
            $modelPHP = fopen($modelLocation, "w");
            
            // Write the files
            fwrite($modelPHP, $classContent);

            // Close the files
            fclose($modelPHP);
    
            echo "\tcreated\t _models/" .$name. ".php\r\n";
        }else{
            echo "\terror\t _models/" .$name. ".php already exist.\r\n";
        }
    }
}
    