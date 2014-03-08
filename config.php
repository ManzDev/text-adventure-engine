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

	// Folder of game. Change if you install game on specific path.
	define('APPDIR',  $_SERVER['DOCUMENT_ROOT'] . '/');

?>