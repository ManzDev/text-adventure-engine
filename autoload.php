<?php

  // AUTOLOADER
  // Automatic class loader
  spl_autoload_register(
  	function ($class) {
  	  if (file_exists(APPDIR . 'classes' . DIRECTORY_SEPARATOR . $class . '.php')) {
  	    require_once(APPDIR . 'classes' . DIRECTORY_SEPARATOR . $class . '.php');
  	  }
  });

?>