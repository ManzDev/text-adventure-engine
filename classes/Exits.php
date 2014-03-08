<?php

	class Exits {

		private $exits = NULL;

		function __construct() {
			$this->exits = load(ROOMFILE, 'data', 'exits');
		}

		// Muestra TODAS las salidas existentes en la habitación 
		// actual (aunque no se cumplan los requisitos)
		function show() {
			$exits = $this->exits;
			foreach ($exits as $exit => $o) {
				if ((is_object($o)) && (property_exists($o, 'hidden')) && ($o->hidden == true))
					unset($exits->$exit);
			}
			$exits = array_keys((array)$this->exits);
			$num = count($exits);
			return array("salidas", ($num == 1 ? __('EXIT_ONLY_ONE') : __('EXIT_AVAILABLE')) . enumerate($exits) . '.');
		}

		// Go to available exits (before, check it)
		function go_to($exit) {

			$obj = $this->exits;

			// Si no hay una salida definida...
			if (!property_exists($obj, $exit))
			  return array('goto', 'FAIL');

			// Hay una salida definida...
			$data = $obj->{$exit};

			// ** Simple format
			if (is_string($data)) 
				return $this->check_visited_and_goto($data);

			// ** Complex format
			if (property_exists($data, 'required')) {
				$required = new Required();
				if ($required->check($data->required))
			  		return $this->check_visited_and_goto($data->target);

				// No se cumplen requisitos
				if (property_exists($data, 'else')) 
					return $this->check_visited_and_goto($data->else);
				else
					return array('goto', $data->excuse);
			}

			if (property_exists($data, 'target'))
				return $this->check_visited_and_goto($data->target);

			// BAD FORMAT JSON
		}

		// Go to new room and save changes on file data. 
		// Also check visited room and mark it.
		function check_visited_and_goto($room) {

			// go to new room
		    save(USERFILE, 'info', 'room', $room);

		    // check visited (and mark)
		    if (!load(USERFILE, 'actions', $room . '_visited'))
		      save(USERFILE, 'actions', $room . '_visited', "1");

		  	// check if end
		  	$ends = new Ends($room);
		  	$ends->check();

		    return array('goto', 'OK');
		}

	}

?>