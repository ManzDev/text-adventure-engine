<?php

  class Talk {

  	function with($char) {

		$talk = new StdClass();
		$obj = load(ROOMFILE, 'hablar', $char);
		
		foreach ($obj as $k => $v) {
			$item = new StdClass();
			$item->m = $v->m; // message
			$item->v = "new";
			$talk->{"$k"} = $item;
		}

		$item = new StdClass();
		$item->m = _('TALK_STOP');
		$item->v = "dark";
		$talk->abort = $item;

		return array('hablar', $talk);
  	} 

  }

?>