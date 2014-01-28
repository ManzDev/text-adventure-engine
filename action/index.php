<?php
	
	$time = microtime(1);
	include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');		// Autoload classes
	include($_SERVER['DOCUMENT_ROOT'] . '/Localize.php'); 		// Localization translation
	include($_SERVER['DOCUMENT_ROOT'] . '/FileDriver.php');		// File-Flat Driver
	include($_SERVER['DOCUMENT_ROOT'] . '/BasicCore.php');		// Constants & Functions

 	$response = new StdClass();

	$info = (isset($_GET['info'])? (int)$_GET['info'] : 0);
	$chat = (isset($_GET['chat'])? (int)$_GET['chat'] : 0);
	$data = (isset($_POST['data'])? $_POST['data'] : null);

	// Get current room data
	if ($info == 1) {
		list($response->action, $response->data) = array("info", (array)load(ROOMFILE, 'info'));

		if (!array_key_exists('name', $response->data))
			$response->data['name'] = CURRENT_ROOM;

		if (array_key_exists('image', $response->data))
			$response->data['image'] = RAWDIR . $response->data['image'];
		else
			$response->data['image'] = RAWDIR . CURRENT_ROOM . '.jpg';

		if (array_key_exists('music', $response->data))
			$response->data['music'] = RAWDIR . $response->data['music'];

		$items = array_keys((array)load(USERFILE, 'inventory'), CURRENT_ROOM);
		if (count($items) !== 0)
			$response->data['items'] = $items;

		print_r(json_encode($response));
		return;
	}

	// Get current chat room data
	if ($chat == 1) {
		header('Content-type: text/plain; charset=utf-8');
		print_r(loadchat(CURRENT_ROOM));
	}

	// No data for parser, finish process
	if (!$data)
		return;
		
	include($_SERVER['DOCUMENT_ROOT'] . '/Parser.php');
	$data = parser(sanitize($data));
	
	if (strpos($data, " ") !== FALSE)
		list($verb, $words) = explode(" ", $data, 2);
	else 
		list($verb, $words) = array($data, "");

	if ((!defined('USERNAME')) && ($verb != 'nickname')) {
		list($response->action, $response->data) = array('nickname', 'NONICK_SET');
		print_r(json_encode($response));
		return;
	}

	switch ($verb) {

		case 'nickname':
			$nickname = new Nickname();
			list($response->action, $response->data) = $nickname->set($words);
			break;
		case _('EXIT_VERB'):
			$exits = new Exits();
			list($response->action, $response->data) = $exits->show();
			break;
		case _('NORTH_VERB'):
		case _('SOUTH_VERB'):
		case _('EAST_VERB'):
		case _('WEST_VERB'):
		case _('UP_VERB'):
		case _('DOWN_VERB'):
		case _('INSIDE_VERB'):
		case _('OUTSIDE_VERB'):
			$exits = new Exits();
			list($response->action, $response->data) = $exits->go_to($verb);
			break;
		case _('INVENTORY_VERB'):
			$inventory = new Inventory();
			list($response->action, $response->data) = $inventory->show();
			break;
		case _('LOOK_VERB'):
			$room = new Room();
			list($response->action, $response->data) = $room->look($words);
			break;
		case _('TAKE_VERB'):
			$room = new Room();
			list($response->action, $response->data) = $room->take($words);
			break;
		case _('TALK_VERB'):
			$talk = new Talk();
			list($response->action, $response->data) = $talk->with($words);
			break;
		case _('DROP_VERB'):
			$inventory = new Inventory();
			list($response->action, $response->data) = $inventory->drop($words);
			break;
		default: // chat
			savechat(CURRENT_ROOM, $data);
			$response->action = 'chat';
			break;
	}

	file_put_contents("profiler.txt", memory_get_usage() . ' => ' . (microtime(1) - $time) );
	print_r(json_encode($response));

?>