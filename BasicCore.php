<?php

	// REQUIRE: load/save (from *Driver)

	// CONSTANTS
	define('SECURE_JSON', 1);				// Strip tags from JSON content
	define('CURRENT_GAME', 'default');		// Change dir for play other game

	// PATHS DIR & FILES
	define('APPDIR',  $_SERVER['DOCUMENT_ROOT']);
	define('GAMEDIR', APPDIR . 'games/' . CURRENT_GAME);	
	define('USERDIR', GAMEDIR . '/users/');
	define('ROOMDIR', GAMEDIR . '/data/');
	define('CHATDIR', GAMEDIR . '/chats/');

	// Public dir
	define('RAWDIR', str_replace(APPDIR, '', GAMEDIR) . '/assets/');

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

	function show_end($v) {

	    if (!property_exists($v, 'target')) {
	      $end = new StdClass();	    

	      // Show text
	      $end->text = (property_exists($v, 'text') ? $v->text : _('END_ADVENTURE'));

	      // Show score
	      if (property_exists($v, 'showscore'))
	        $end->score = load(USERFILE, 'vars', $v->showscore);

	  	  // Title of nice window
	      $end->title = (property_exists($v, 'title') ? $v->title : _('WORDS_END'));

	      return array("end", $end);
		}

		return array("endurl", $v->target);	    
	}

	// Detected end
	if (isset($datauser->end)) {
		$v = (object)load(ROOMFILE, 'data', 'ends', $datauser->end);
		$response = new StdClass();
		list($response->action, $response->data) = show_end($v);
		print_r(json_encode($response));
		exit();
	}

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
	  return ($n === 0 ? $empty : $array[0]);	    
	}
	

?>