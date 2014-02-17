<?php

  class Inventory {

  	private $items = NULL;

  	function __construct() {
  		$this->items = (array)load(USERFILE, 'inventory');
  	}

  	// Get item list from inventory
  	function show() {
  		// Get only items on inventory (=1)
  		$itemlist = array_keys($this->items, "1");
    	$num = count($itemlist);
    	return array('inventario', ($num == 0 ? __('INVENTORY_EMPTY') : __('INVENTORY_LIST') . enumerate($itemlist) . '.'));
  	}

  	// Look a item on inventory or user-dropable item on rooms
  	function look($words) {

		// Check global items list
	    $item = load(ITEMFILE, $words);
	    
	    // No takable item
	    if ($item === NULL)
			return array("mirar", "FAIL");

	    // Si no lo tenemos en el inventario
	    if (!array_key_exists($words, $this->items))
	        if ($item->room == CURRENT_ROOM)
	        	return array("item", array("image" => RAWDIR . $item->image, 
	        							   "message" => $item->look));
	        else
	        	return array("mirar", "FAIL");	// Item exist, but not here

	    $item_inv = $this->items[$words];

	    // Comprobamos si lo tenemos en el inventario
	    if ($item_inv == 1)
	        return array("item", array("image" => RAWDIR . $item->image, 
	       	 						   "message" => $item->look));
	    
	    if ($item_inv == CURRENT_ROOM)
	      return array("item", array("image" => RAWDIR . $item->image, 
	      							 "message" => $item->look));  

	    // No debería llegar aquí
	    return array("mirar", "FAIL");
  	}

  	function drop($words) {

		$obj = $this->items[$words];

		// Item from inventory to room
		if ($obj == 1) {
		  save(USERFILE, 'inventory', $words, CURRENT_ROOM);
		  return array('drop', '+' . $words);
		}

		// Not have item
		return array('drop', 'FAIL');
	}
  }

?>