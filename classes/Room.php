<?php

	class Room {

		private $synonyms = NULL;

		function look($words) {

			$words = $this->findSynonym($words);
			$obj = load(ROOMFILE, 'mirar', $words);

			// Si no hay un objeto en el lugar, busca en el inventario...
			if ($obj === NULL) {
				$inventory = new Inventory();
				return $inventory->look($words);
			}

			// ** Simple format
			if (is_string($obj))
				return array("mirar", $obj);

			// ** Complex format
			// No hay restricciones
			if (!property_exists($obj, 'required')) {
				$this->check_optional_param($obj);
				return array("mirar", $obj->message);
			}

			// ¿Se cumplen las restricciones?
			$required = new Required();
			if ($required->check($obj->required)) {

				// Only first time
				if (!load(USERFILE, 'actions', $words . '_seen')) {
					$this->check_optional_param($obj);
					save(USERFILE, 'actions', $words . '_seen', "1");
				}
				return array("mirar", $obj->message);
			}

			// No se cumplen las restricciones
			if (property_exists($obj, 'excuse'))
				return array("mirar", $obj->excuse);
			else
				return array("mirar", "FAIL");    // No hay excusa definida => objeto no encontrado 

			return array("mirar", "FAIL");

		}

		function take($words) {
			//$words = find_synonyms($words);
			$obj = load(ITEMFILE, $words);

			// If object not exist
			if ($obj === NULL)
				return array("coger", "FAIL");

		    $objinv = load(USERFILE, 'inventory', $words);

			// Si el objeto está en la room inicial, lo procesa
			if (($objinv == NULL) && ($obj->room == CURRENT_ROOM)) {

				// Ya lo tengo
				if ($objinv == "1")
					return array("coger", _('INVENTORY_ITEM_ALREADY'));      

				// ** Complex format
				// No hay restricciones
				if (!property_exists($obj, 'required')) {
					if (!$objinv) {
						$this->check_optional_param($obj);
						save(USERFILE, 'inventory', $words, "1");
						return array("coger", $obj->message);
					}
					return array("coger", "FAIL");
				}

				// ¿Se cumplen las restricciones?
				$required = new Required();
				if ($required->check($obj->required)) {
		     
					// Only first time
					if (!$objinv) {
						$this->check_optional_param($obj);
						save(USERFILE, 'inventory', $words, "1");
						return array("coger", $obj->message);
					}
				}

				return array("coger", (property_exists($obj, 'excuse') ? $obj->excuse : "FAIL"));
			}

			// Caso especial: El objeto se ha dejado en una room (no procesar restricciones)
			if ($objinv == CURRENT_ROOM) {
				save(USERFILE, 'inventory', $words, "1");
				return array("coger", '-' . $words);
			}

			// No se cumplen las restricciones 
			// Si no hay excusa definida => objeto no encontrado
			return array("coger", "FAIL");
			//return (property_exists($obj, 'excuse') ? $obj->excuse : "FAIL");
		}

		// If found synonym, return main word
		// If not, return same word tried
		function findSynonym($word) {

			$this->synonyms = (array)load(ROOMFILE, 'synonyms');
			foreach ($this->synonyms as $p => $s ) {

				// Simple format
				if (is_string($s))
					$s = array($s);

				// Array format
				if (in_array($word, $s))
					return $p;
			}
			return $word;
		}

		// Optional parameters block
		private function check_optional_param($obj) {

			// Check if exists amount to inc/dec, and apply
			function get_var_and_number($p) {
				if (strpos($p, '@') === FALSE)
					return array($p, 1);

				return explode('@', $p);
			}

			// Comprueba si existe una propiedad (opcional) y si existe, aplica la función $func()
			function check_saveparam($obj, $prop, $func) {

				if (property_exists($obj, $prop)) {
					$array = $obj->{$prop};

					// ** Simple format
					if (is_string($array))
						$array = array($array);

					// ** Array format
					foreach ($array as $i)
						$func($i);
				}
			}

			// PTE: if ($obj->sound) // play sound
			check_saveparam($obj, 'inc', function($p) {
				list($v, $inc) = get_var_and_number($p);
				$inc = (int)($inc === NULL ? 1 : $inc);
				$var = (int)load(USERFILE, 'vars', $v);
				save(USERFILE, 'vars', $v, $var + $inc);
			});
			check_saveparam($obj, 'dec', function($p) {
				list($v, $dec) = get_var_and_number($p);
				$dec = (int)($dec === NULL ? 1 : $dec);
				$var = (int)load(USERFILE, 'vars', $v);
				save(USERFILE, 'vars', $v, $var - $dec);
			});
			check_saveparam($obj, 'setAction', function($p) { save(USERFILE, 'actions', $p, "1"); });
			check_saveparam($obj, 'setObject', function($p) { save(USERFILE, 'inventory', $p, "1"); });
		}		

	}

?>