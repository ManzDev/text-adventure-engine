<?php

	class Required {

		private $items = NULL;
		private $actions = NULL;
		private $vars = NULL;

		function __construct() {
			$this->items = load(USERFILE, 'inventory');
			$this->actions = array_keys((array)load(USERFILE, 'actions'));
			$this->vars = (array)load(USERFILE, 'vars');
		}

  		// check requeriments (a, b, !c, d@5, ...)
  		function check($yes) {

			// Simple format
			if (is_string($yes))
				$yes = array($yes);

			// Split on arrays (yes=required), (no=excluded), (var=greater value), (drop=item dropped), (taked=item taked at least one time)
			$no = $req = $drop = $taked = array();

			foreach ($yes as $k => $v) {
				if ($v[0] == '#') {
					$req[] = substr($v, 1);
					unset($yes[$k]);
				}
				else if ($v[0] == '!') {
					$no[] = substr($v, 1);
					unset($yes[$k]);
				}
				else if ($v[0] == '-') {
					$drop[] = substr($v, 1);
					unset($yes[$k]);
				}
				else if ($v[0] == '+') {
					$taked[] = substr($v, 1);
					unset($yes[$k]);
				}
			}

			// Check possitive constraints
			foreach ($yes as $i) {
				if (in_array($i, array_keys((array)$this->items, 1)) || in_array($i, $this->actions))
					continue;
				return FALSE;
			}

			// Check negative constraints
			foreach ($no as $i) {
				$ic = in_array($i, array_keys((array)$this->items, 1));     // Is on inventory
				$ac = in_array($i, $this->actions);   // Is on actions

				// Not present on inventory neither actions
				if (($ic === FALSE) && ($ac === FALSE)) // (!($ic || $ac)) // NOR Gate
					continue;
				return FALSE;
			}

			// check greater values
			foreach ($req as $i) {
				list($var, $num) = explode('@', $i);

				if ((array_key_exists($var, $this->vars)) && ($this->vars[$var] >= $num))
					continue;
				return FALSE;
			}

			// check dropped items
			foreach ($drop as $i) {
				if (strpos($i, '@') !== FALSE) 
					list($i, $room) = explode('@', $i);

				$items_inv = array_keys((array)$this->items, 1);	// Is on inventory
				
				if (isset($room))
					$items_tak = array_keys((array)$this->items, $room);	// All taked dropped on $room
				else
					$items_tak = array_keys((array)$this->items);			// All taked


				if (in_array($i, array_diff($items_tak, $items_inv)))	// Items dropped
					continue;
				return FALSE;
			}

			// check taked items
			foreach ($taked as $i) {
				$items_tak = array_keys((array)$this->items);

				if (in_array($i, $items_tak))
					continue;
				return FALSE;
			}

			return TRUE;
		}
	}

?>