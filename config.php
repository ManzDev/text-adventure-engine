<?php

	/***
	  ** Config.php
	  **
	  ** Configuration variables for Innocent Hill Engine
	  ** 
	***/

	// Fix for some servers without timezone setted.  
	// IMPORTANT!! Set your timezone: http://es1.php.net/timezones
	ini_set('date.timezone', 'Europe/London');

	// Secure external game. Strip tags from JSON content for avoid malicious tags.
	define('SECURE_JSON', 1);

	// Current game folder. By default, 'default' folder. Change for play other game
	define('CURRENT_GAME', 'default');

	// Autodiscover folder path game. Use current URL without 'master' (-6)
	if (!defined('APPDIR'))
		define('APPDIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

	// PATHS DIR & FILES
	define('GAMEDIR', APPDIR . 'games' . DIRECTORY_SEPARATOR . CURRENT_GAME . DIRECTORY_SEPARATOR);	
	define('USERDIR', GAMEDIR . 'users' . DIRECTORY_SEPARATOR);
	define('ROOMDIR', GAMEDIR . 'data' . DIRECTORY_SEPARATOR);
	define('CHATDIR', GAMEDIR . 'chats' . DIRECTORY_SEPARATOR);
	define('ITEMFILE', ROOMDIR . 'items.json');
	
	// Public dir
	define('RAWDIR', str_replace(APPDIR, '', GAMEDIR) . 'assets' . DIRECTORY_SEPARATOR);	

?>