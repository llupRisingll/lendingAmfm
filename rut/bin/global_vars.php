<?php

// Define Constant Variables
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(dirname(dirname(__FILE__))));
define("RUT", dirname(dirname(__FILE__)));
define("VIEW_PATH", ROOT.DS.'_layouts');
define("PRESENTER_PATH", ROOT.DS.'_presenters');
define("MODEL_PATH", ROOT.DS.'_models');
define("CACHE_PATH", RUT.DS.'cached');
define("VENDORS", RUT.DS.'vendor');