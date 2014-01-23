<?php
	
	$time = microtime(1);
	include($_SERVER['DOCUMENT_ROOT'] . '/Localize.php'); 		// Constants for Localization
	include($_SERVER['DOCUMENT_ROOT'] . '/FileDriver.php');		// FileFlat Driver
	include($_SERVER['DOCUMENT_ROOT'] . '/BasicCore.php');		// Constants & Functions

 	$response = new StdClass();

	$info = (isset($_GET['info'])? (int)$_GET['info'] : 0);
	$chat = (isset($_GET['chat'])? (int)$_GET['chat'] : 0);
	$data = (isset($_POST['data'])? $_POST['data'] : null);

	// Get current room data
	if ($info == 1) {
		$response->action = 'info';
		$response->data = (array)load(ROOMFILE, 'info');

		if (!array_key_exists('name', $response->data))
			$response->data['name'] = CURRENT_ROOM;

		if (array_key_exists('image', $response->data))
			$response->data['image'] = RAWDIR . $response->data['image'];
		else
			$response->data['image'] = RAWDIR . CURRENT_ROOM . '.jpg';

		if (array_key_exists('music', $response->data))
			$response->data['music'] = RAWDIR . $response->data['music'];

		print_r(json_encode($response));
		return;
	}

	// Get current chat room data
	if ($chat == 1) {
		header('Content-type: text/plain; charset=utf-8');
		print_r(loadchat(CURRENT_ROOM));
	}

	// No data for parser, save process
	if (!$data)
		return;
		
	include($_SERVER['DOCUMENT_ROOT'] . '/Parser.php');
	include($_SERVER['DOCUMENT_ROOT'] . '/GameEngine.php');
	$data = parser(sanitize($data));
	
	if (strpos($data, " ") !== FALSE)
		list($verb, $words) = explode(" ", $data, 2);
	else 
		list($verb, $words) = array($data, "");

	if ((!defined('USERNAME')) && ($verb != 'nickname')) {
		$response->action = 'nickname';
		$response->data = 'NONICK_SET';
		print_r(json_encode($response));
		return;
	}

	switch ($verb) {

		case 'nickname':
			$response->action = 'nickname';
			$response->data = nickname($words);
			break;
		case _('EXIT_VERB'):
			$response->action = 'salidas';
			$response->data = exits();
			break;
		case _('NORTH_VERB'):
		case _('SOUTH_VERB'):
		case _('EAST_VERB'):
		case _('WEST_VERB'):
		case _('UP_VERB'):
		case _('DOWN_VERB'):
		case _('INSIDE_VERB'):
		case _('OUTSIDE_VERB'):
			// = 'goto';
			list($response->action, $response->data) = go_to($verb);
			break;
		case _('INVENTORY_VERB'):
			$response->action = 'inventario';
			$response->data = inventory();
			break;
		case _('LOOK_VERB'):
			$response->action = 'mirar';
			$response->data = mirar($words);
			break;
		case _('TAKE_VERB'):
			$response->action = 'coger';
			$response->data = coger($words);
			break;
		case _('TALK_VERB'):
			$response->action = 'hablar';
			$response->talk = conversation($words);
			break;
		default: // chat
			savechat(CURRENT_ROOM, $data);
			$response->action = 'chat';
			break;
	}

	file_put_contents("profiler.txt", memory_get_usage() . ' => ' . (microtime(1) - $time) );
	print_r(json_encode($response));

?>