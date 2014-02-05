<?php

  class Nickname {

  	private $nickname = NULL;

  	function __construct() {
  	}

  	function set($nickname) {

  		$this->nickname = load(USERFILE, 'info', 'name');

		if (!isset($_COOKIE['mzname'])) {

			// nickname already exist
	    	if ($this->nickname)
	        	return array("nickname", "NONICK_EXIST");

	      	// check nickname empty
	      	if (strlen(trim($nickname)) == 0)
	        	return array("nickname", "NONICK_EMPTY");

	        // check nickname alphabetic or digits only
	      	if (!ctype_alnum($nickname))
	        	return array("nickname", "NONICK_ERROR");

	      	$this->setCookie($nickname);
	      	save(USERFILE, 'info', 'name', $nickname);

	      	return array("nickname", "OK");
	    }
	    
	    // nickname already exist
	    return array("nickname", "NONICK_EXIST");
  	}

  	function checkCookie() {
  		if (!isset($_COOKIE['mzname'])) {
  			$this->nickname = load(USERFILE, 'info', 'name');
  			$this->setCookie($this->nickname);
  		}
  	}

  	private function setCookie($nickname) {
		//$nickname = ucfirst($nickname);
  		setcookie('mzgame', USERID, time()+3600, "/", $_SERVER['SERVER_NAME']);	    // Cookie from MD5 IP
	    setcookie('mzname', $nickname, time()+3600, "/", $_SERVER['SERVER_NAME']);	// Cookie for nickname
  	}

  }

?>