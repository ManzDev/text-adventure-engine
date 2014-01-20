<?php

	// FileDriver.php
	// Driver for read and write data game file JSON.

	// LOAD(sect, [var], [...])
	// Load and return entire section (array) or variable (string).
	function load() {
		$n = func_num_args();
		$g = func_get_args();

		$content = file_get_contents($g[0]);
		if (SECURE_JSON)
			$content = strip_tags($content);
		$data = json_decode($content);
		for ($i = 1; $i < $n; $i++) 
			if (property_exists($data, $g[$i]))
				$data = $data->{$g[$i]};
			else
				return NULL;
		return $data;
	}

	// SAVE(sect, var, value)
	// Save or update a section and variable with specified value (string)
	function save() {
		$n = func_num_args();
		$g = func_get_args();

		$data = json_decode(file_get_contents($g[0]));
		if ($n == 3)
			$data->{$g[1]} = $g[2];
		else if ($n == 4)
			$data->{$g[1]}->$g[2] = $g[3];
		unlink($g[0]);
		file_put_contents($g[0], json_encode($data, JSON_PRETTY_PRINT));
	}

	// Efficient Unix-tail function
	// http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	function tail($filename, $lines = 10, $buffer = 4096) {

	    $f = fopen($filename, "rb");
	    fseek($f, -1, SEEK_END);				// Jump to last character

	    // Read it and adjust line number if necessary
	    // (Otherwise the result would be wrong if file doesn't end with a blank line)
	    if(fread($f, 1) != "\n") $lines -= 1;

	    // Start reading
	    $output = '';
	    $chunk = '';

	    // While we would like more
	    while(ftell($f) > 0 && $lines >= 0)
	    {
	        // Figure out how far back we should jump
	        $seek = min(ftell($f), $buffer);

	        // Do the jump (backwards, relative to where we are)
	        fseek($f, -$seek, SEEK_CUR);

	        // Read a chunk and prepend it to our output
	        $output = ($chunk = fread($f, $seek)).$output;

	        // Jump back to where we started reading
	        //fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
	        fseek($f, -strlen($chunk), SEEK_CUR);

	        // Decrease our line counter
	        $lines -= substr_count($chunk, "\n");
	    }

	    // While we have too many lines
	    // (Because of buffer size we might have read too many)
	    while($lines++ < 0)
	    {
	        // Find first newline and remove all text before that
	        $output = substr($output, strpos($output, "\n") + 1);
	    }

	    // Close file and return
	    fclose($f); 
	    return $output; 
	}

	function loadchat($name) {
		$file = CHATDIR . $name . '.log';
		if (!file_exists($file))
			file_put_contents($file, "\xEF\xBB\xBF");	// UTF-8 BOM Support

		$line = tail($file, 5);
		return nl2br($line);
	}

	function savechat($name, $msg) {
		$file = CHATDIR . $name . '.log';
		$f = fopen($file, 'ab');
		$line = '[' .USERNAME. '] ' .$msg. "\r\n";
		fwrite($f, $line);
		fclose($f);
	}

?>