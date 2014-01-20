<?php

	function _($l) {
		$_lang = (array)json_decode(file_get_contents(APPDIR . '/language.json'));
		return $_lang[$l];
	}

?>