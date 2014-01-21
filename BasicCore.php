<?php

	// REQUIRE: load/save (from *Driver)

	// CONSTANTS
	define('SECURE_JSON', 1);			// Strip tags from JSON content

	// PATHS DIR & FILES
	define('APPDIR',  $_SERVER['DOCUMENT_ROOT']);
	define('USERDIR', APPDIR . '/users/');
	define('ROOMDIR', APPDIR . '/data/');
	define('CHATDIR', APPDIR . '/chats/');

	define('USERID', md5($_SERVER['REMOTE_ADDR']));
	define('USERFILE', USERDIR . USERID .'.json');

	// FIRST TIME (NEW USER)
	// Crea un nuevo fichero de datos de usuario de un molde de base
	if (!file_exists(USERFILE)) {
		$base = json_decode(file_get_contents(USERDIR . '/base.json'));
		file_put_contents(USERFILE, json_encode($base, JSON_PRETTY_PRINT));
	}

	$datauser = (object)load(USERFILE, 'info');
	define('CURRENT_ROOM', $datauser->room);

	if (isset($datauser->name))
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
	    return implode(', ', array_slice($array, 0, $n -1)) . ' '._('WORDS_AND').' ' . $array[$n -1];
	}

	

?>