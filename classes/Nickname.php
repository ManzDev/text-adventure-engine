<?php

  class Nickname {

  	private $nickname = NULL;

  	function set($nickname) {

		if (!isset($_COOKIE['mzname'])) {
			
			$this->nickname = load(USERFILE, 'info', 'name');

			// nickname already exist
	    	if ($this->nickname)
	        	return array("nickname", "NONICK_EXIST");

	      	// check nickname empty
	      	if (strlen(trim($nickname)) == 0)
	        	return array("nickname", "NONICK_EMPTY");

	        // check nickname alphabetic or digits only
	      	if (!ctype_alnum($nickname))
	        	return array("nickname", "NONICK_ERROR");

	      	//$nickname = ucfirst($nickname);

	      	setcookie('mzgame', USERID, time()+3600, "/", $_SERVER['SERVER_NAME']);	    // Cookie from MD5 IP
	      	setcookie('mzname', $nickname, time()+3600, "/", $_SERVER['SERVER_NAME']);	// Cookie for nickname

	      	save(USERFILE, 'info', 'name', $nickname);

	      	return array("nickname", "OK");
	    }
	    
	    // nickname already exist
	    return array("nickname", "NONICK_EXIST");
  	}

  }

?>