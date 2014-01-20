<?php

	// REQUIRE: load/save (from *Driver)

	// CONSTANTS
	define('SECURE_JSON', 1);			// Strip tags from JSON content

	// PATHS DIR & FILES
	define('USERDIR', $_SERVER['DOCUMENT_ROOT'] . '/users/');
	define('ROOMDIR', $_SERVER['DOCUMENT_ROOT'] . '/data/');
	define('CHATDIR', $_SERVER['DOCUMENT_ROOT'] . '/chats/');

	define('USERID', md5($_SERVER['REMOTE_ADDR']));
	define('USERFILE', USERDIR . USERID .'.json');

	if (!isset($_COOKIE['mzgame'])) {
		// <-- here redirect for set nickname
		$nickname = 'Manz';	// temp
		setcookie('mzgame', USERID, time()+3600, "/", $_SERVER['SERVER_NAME']);
		setcookie('mzname', $nickname, time()+3600, "/", $_SERVER['SERVER_NAME']);
	}	

	$datauser = load(USERFILE, 'info');
	define('CURRENT_ROOM', $datauser->room);	// load(USERFILE, 'info', 'room')
	define('USERNAME', $datauser->name);
	define('ROOMFILE', ROOMDIR . CURRENT_ROOM .'.json');


	// FUNCTIONS

	// Sanitize user input string
	function sanitize($s) {
		// <-- Here remove Spam URLs
		return htmlspecialchars(strip_tags($s));
	}

	// ENUMERA UN ARRAY
	// Devuelve una lista de items (array) en el formato [1, 2, ... y n]
	function enumerate($array, $empty = '') {

	  $n = count($array);   // total items
	    
	  // list with 0 items
	  if ($n === 0) {
	    return $empty;
	  } 
	  // list with 1 item
	  else if ($n === 1) {
	    return $array[0];
	  } 
	  // list with 2 or more items
	  else 
	    return implode(', ', array_slice($array, 0, $n -1)) . ' y ' . $array[$n -1];
	}

	

?>