<?php

class MyAutoloader {

    public static function load($className){
			
		// Initiate all of possible paths in a variable
		$namespace=str_replace("\\","/",__NAMESPACE__);
		$className=str_replace("\\","/",$className);

		$explodedClass = explode("/", $className);

		if (count($explodedClass) > 1){
			if ($explodedClass[0] == "Plugins"){
				$pluginsPath = RUT.DS.'plugins'.DS.ucfirst($explodedClass[1]).'.php';
				if (file_exists($pluginsPath)){
					// Use the Processors Class
					require_once $pluginsPath;
				}else{
					// TODO: Show a 500 ERROR not found instead...
					throw new Exception('Plugin Not found:'. $className);
				}
			}
		}else{
			self::normalLoad($className);
		}
	}
	

	public static function normalLoad($className){
		
		$presenterPath = PRESENTER_PATH.DS.ucfirst($className).'.php';
		$processorsPath = RUT.DS.'bin'.DS.ucfirst($className).'.php';
		$modelsPath = MODEL_PATH.DS.ucfirst($className).'.php';

		if (file_exists($presenterPath)){
			// Use the Handler Class
			require_once $presenterPath;
		} elseif (file_exists($processorsPath)){
			// Use the Processors Class
			require_once $processorsPath;
		} elseif (file_exists($modelsPath)){
			// Use the Models Class
			require_once $modelsPath;
		} else{

			// TODO: Show a 500 ERROR not found instead...
			throw new Exception('Failed to include ClassName:'. $className);
		}
	}
}

spl_autoload_register("MyAutoloader::load");