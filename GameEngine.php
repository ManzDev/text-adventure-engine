<?php

  // Set nickname user on base userfile data and cookie
  function nickname($nickname) {

    if (!isset($_COOKIE['mzname'])) {

      if (load(USERFILE, 'info', 'name'))
        return "NONICK_EXIST";

      // check nickname
      if (strlen(trim($nickname)) == 0)
        return "NONICK_EMPTY";

      if (!ctype_alnum($nickname))
        return "NONICK_ERROR";

      $nickname = ucfirst($nickname);
      setcookie('mzgame', USERID, time()+3600, "/", $_SERVER['SERVER_NAME']);
      setcookie('mzname', $nickname, time()+3600, "/", $_SERVER['SERVER_NAME']);
      save(USERFILE, 'info', 'name', $nickname);
      return "OK";
      
    }
    else 
      return "NONICK_EXIST"; // ya existe
  }

  // FIND SYNONYMS
  // Si los encuentra, devuelve la palabra principal.
  // Si no los encuentra, la palabra buscada.
  function find_synonyms($palabra) {

    $synonyms = (array)load(ROOMFILE, 'synonyms');

    foreach ($synonyms as $p => $s ) {

      // Simple format
      if (is_string($s))
        $s = array($s);

      // Array format
      if (in_array($palabra, $s))
        return $p;
    }
    return $palabra;
  }

  // Comprueba si se cumplen los criterios de restricción indicados (a, b, c, !d, ...)
  function required_check($yes) {
    $items = array_keys((array)load(USERFILE, 'inventory'));
    $actions = array_keys((array)load(USERFILE, 'actions'));
    $vars = (array)load(USERFILE, 'vars');

    // Simple format
    if (is_string($yes))
      $yes = array($yes);

    // Separa en arrays (yes=deben cumplirse), (no=deben no cumplirse), (var=deben superarse)
    $no = array();
    $req = array();
    foreach ($yes as $k => $v) {
      if (strpos($v, '@') !== FALSE) {
        $req[] = $v;
        unset($yes[$k]);
      }
      else if ($v[0] == '!') {
        $no[] = substr($v, 1);
        unset($yes[$k]);
      }
    }

    // Comprueba si se cumplen las restricciones positivas
    foreach ($yes as $i) {
      if (in_array($i, $items) || in_array($i, $actions))
        continue;
      return FALSE;
    }

    // Comprueba si se cumplen las restricciones negativas
    foreach ($no as $i) {
      $ic = in_array($i, $items);     // Está en el inventario
      $ac = in_array($i, $actions);   // Está en las acciones

      // No debe estar en ninguna de las dos
      if (($ic === FALSE) && ($ac === FALSE)) // (!($ic || $ac)) // NOR Gate
        continue;
      return FALSE;
    }

    // Comprueba si se supera el mínimo requerido
    foreach ($req as $i) {
      list($var, $num) = explode('@', $v);

      if ((array_key_exists($var, $vars)) && ($vars[$var] >= $num))
        continue;
      return FALSE;
    }

    return TRUE;
  }

  function check_ends($ends) {

    // typecast fix for reverse order object
    foreach ((object)array_reverse((array)$ends) as $k => $v) {

      // No se cumplen los requisitos para este final
      if (property_exists($v, 'required'))
        if (!required_check($v->required))
          continue;

      save(USERFILE, 'info', 'end', $k);
      //return show_end($v);

    }
    return array("goto", "OK");  // No hay final
  }

  // MUESTRA LAS SALIDAS
  // Muestra TODAS las salidas existentes en la habitación 
  // actual (aunque no se cumplan los requisitos)
  function exits() {
    $exits = array_keys((array)load(ROOMFILE, 'data', 'exits'));
    $num = count($exits);
    return ($num == 1 ? _('EXIT_ONLY_ONE') : _('EXIT_AVAILABLE')) . enumerate($exits) . '.';
  }

  // Go to new room and save changes on file data. 
  // Also check visited room and mark it.
  function check_visited_and_goto($room) {

    // go to new room
    save(USERFILE, 'info', 'room', $room);

    // check visited (and mark)
    if (!load(USERFILE, 'visited', $room))
      save(USERFILE, 'visited', $room, "1");

    // check possible ends
    $ends = load(ROOMDIR . $room . '.json', 'data', 'ends');
    if ($ends) 
      return check_ends($ends);    

    return array('goto', 'OK');
  }

  // Check available exits
  function go_to($exit) {

    // get possible exits
    $obj = load(ROOMFILE, 'data', 'exits');

    // Si no hay una salida definida...
    if (!property_exists($obj, $exit))
      return array('goto', 'FAIL');

    // Hay una salida definida...
    $data = $obj->{$exit};

    // ** Simple format
    if (is_string($data)) 
      return check_visited_and_goto($data);

    // ** Complex format
    if (required_check($data->required)) 
      return check_visited_and_goto($data->target);

    // No se cumplen requisitos
    if (property_exists($data, 'else')) 
      return check_visited_and_goto($data->else);
    else
      return array('goto', $data->excuse);
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

  // Check if exists amount to inc/dec, and apply
  function get_var_and_number($p) {
    if (strpos($p, '@') === FALSE)
      return array($p, 1);
    
    return explode('@', $p);
  }

  // Optional parameters block
  function check_optional_param($obj) {

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

  // MUESTRA EL INVENTARIO
  // Analiza el inventario y lo devuelve en forma de lista al usuario.
  function inventory() {
    $inv = array_keys((array)load(USERFILE, 'inventory'));
    $num = count($inv);
    return ($num == 0 ? _('INVENTORY_EMPTY') : _('INVENTORY_LIST') . enumerate($inv) . '.');
  }

  // PTE: Buscar objeto en inventario
  function mirar_inventory($words) {
    return "FAIL"; // temporalmente, no examina inventario
  }

  // MIRAR
  function mirar($words) {
    $words = find_synonyms($words);
    $obj = load(ROOMFILE, 'mirar', $words);

    // Si no hay un objeto en el lugar, busca en el inventario...
    if ($obj === NULL)
      return mirar_inventory($words);
    
    // ** Simple format
    if (is_string($obj))
      return $obj;
    
    // ** Complex format
    // No hay restricciones
    if (!property_exists($obj, 'required')) {
      check_optional_param($obj);
      return $obj->message;
    }

    // ¿Se cumplen las restricciones?
    if (required_check($obj->required)) {

      // Only first time
      if (!load(USERFILE, 'actions', $words . '_seen')) {
        check_optional_param($obj);
        save(USERFILE, 'actions', $words . '_seen', "1");
      }
      return $obj->message;
    }

    // No se cumplen las restricciones
    if (property_exists($obj, 'excuse'))
      return $obj->excuse;
    else
      return "FAIL";    // No hay excusa definida => objeto no encontrado 

    return "FAIL";  
  }

  // COGER
  function coger($words) {
    $words = find_synonyms($words);
    $obj = load(ROOMFILE, 'coger', $words);

    // Si no hay objeto en el lugar...
    if ($obj === NULL)
      return "FAIL";

    $objinv = load(USERFILE, 'inventory', $words);

    if ($objinv == "1")
      return _('INVENTORY_ITEM_ALREADY');

    // ** Simple format
    if (is_string($obj)) {
      $temp = $obj;
      $obj = new StdClass();
      $obj->message = $temp;
    }

    // ** Complex format
    // No hay restricciones
    if (!property_exists($obj, 'required')) {
      if (!$objinv) {
        check_optional_param($obj);
        save(USERFILE, 'inventory', $words, "1");
        return $obj->message;
      }
      return "FAIL";
    }

    // ¿Se cumplen las restricciones?
    if (required_check($obj->required)) {
   
      // Only first time
      if (!$objinv) {
        check_optional_param($obj);
        save(USERFILE, 'inventory', $words, "1");
        return $obj->message;
      }
    }

    // No se cumplen las restricciones 
    // Si no hay excusa definida => objeto no encontrado
    return (property_exists($obj, 'excuse') ? $obj->excuse : "FAIL");
  }

  /*
  // PTE: Soporte de conversación al estilo Aventura gráfica
  function conversation($char) {

    $talk = new StdClass();
    $obj = load(ROOMFILE, 'hablar', $char);
    foreach ($obj as $k => $v) {
      $item = new StdClass();
      $item->m = $v->message;
      $item->v = "new";
      $talk->{"$k"} = $item;
    }

    $item = new StdClass();
    $item->m = _('TALK_STOP');
    $item->v = "dark";
    $talk->abort = $item;
    
    return $talk;
  }
  */

?>