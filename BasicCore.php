<?php

	// REQUIRE: load/save (from *Driver)

	// CONSTANTS
	define('SECURE_JSON', 1);				// Strip tags from JSON content
	define('CURRENT_GAME', 'default');		// Change dir for play other game

	// PATHS DIR & FILES
	define('APPDIR',  $_SERVER['DOCUMENT_ROOT'] . '/');
	define('GAMEDIR', APPDIR . 'games/' . CURRENT_GAME);	
	define('USERDIR', GAMEDIR . '/users/');
	define('ROOMDIR', GAMEDIR . '/data/');
	define('CHATDIR', GAMEDIR . '/chats/');

	// Public dir
	define('RAWDIR', str_replace(APPDIR, '', GAMEDIR) . '/assets/');

	define('USERID', md5($_SERVER['REMOTE_ADDR']));
	define('USERFILE', USERDIR . USERID .'.json');
	define('ITEMFILE', ROOMDIR . 'items.json');

	// FIRST TIME (NEW USER)
	// Crea un nuevo fichero de datos de usuario de un molde de base
	if (!file_exists(USERFILE)) {
		$base = json_decode(file_get_contents(USERDIR . '/base.json'));
		file_put_contents(USERFILE, json_encode($base, JSON_PRETTY_PRINT));
	}

	$datauser = (object)load(USERFILE, 'info');
	define('CURRENT_ROOM', $datauser->room);
	define('ROOMFILE', ROOMDIR . CURRENT_ROOM .'.json');

	if (isset($datauser->name)) {
		define('USERNAME', $datauser->name);
		$nickname = new Nickname();
		$nickname->checkCookie();
	}

	if (isset($datauser->end)) {
		$response = new StdClass();
		$ends = new Ends();
		list($response->action, $response->data) = $ends->run($datauser->end);
		print_r(json_encode($response));
		exit();
	}

	// MAIN FUNCTIONS

	// Sanitize user input string
	function sanitize($s) {
		// <-- Here remove Spam URLs
		return htmlspecialchars(strip_tags($s));
	}

	// ENUMERA UN ARRAY
	// Devuelve una lista de items (array) en el formato [1, 2, ... y n]
	function enumerate($array, $empty = '') {

	  $n = count($array);   // total items

	  // list with 2 or more items
	  if ($n > 1)
	  	return implode(', ', array_slice($array, 0, $n -1)) . ' '._('WORDS_AND').' ' . $array[$n -1];
	    
	  // list with 0 or 1 items
	  return ($n === 0 ? $empty : $array[0] );	    
	}	

?>