<?php

	class Ends {

		private $room = NULL;
		private $ends = NULL;
		private $current_end = NULL;

		// load possible ends from specified room (default: current room)
		function __construct($room = CURRENT_ROOM) {
			$this->room = $room;
			$this->ends = load(ROOMDIR . $room . '.json', 'data', 'ends');
		}

  		// Check possible ends (and set if apply)
		function check() {

			// typecast fix for reverse order object (???)
			// (object)array_reverse((array)$this->ends)
			foreach ((object)$this->ends as $k => $v) {

				// No se cumplen los requisitos para este final
				if (property_exists($v, 'required')) {
					$required = new Required();

					if (!$required->check($v->required))
						continue;
				}

				save(USERFILE, 'info', 'end', $k);
				return TRUE;
			}
			return FALSE;  // No hay final
		}

		function run($end) {

			$v = $this->ends->{$end};

			if (!property_exists($v, 'target')) {
				
				$newend = new StdClass();	    

				// Show text
				$newend->text = (property_exists($v, 'text') ? $v->text : __('END_ADVENTURE'));

				// Show score
				if (property_exists($v, 'showscore'))
					$newend->score = load(USERFILE, 'vars', $v->showscore);

				// Title of nice window
				$newend->title = (property_exists($v, 'title') ? $v->title : __('WORDS_END'));

				return array("end", $newend);
			}

			return array("endurl", $v->target);
		}

	}

?>