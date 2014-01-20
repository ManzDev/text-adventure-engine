<?php

  // BUSCA SINONIMOS 
  // Si los encuentra, devuelve la palabra principal.
  // Si no los encuentra, la palabra buscada.
  function busca_sinonimos($palabra) {

    $sinonimos = load(ROOMFILE, 'sinonimos');

    foreach ($sinonimos as $p => $s ) {

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

    // Simple format
    if (is_string($yes))
      $yes = array($yes);

    // Separa en dos arrays (yes=deben cumplirse), (no=deben no cumplirse)
    $no = array();
    foreach ($yes as $k => $v) {
      if ($v[0] == '!') {
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

    return TRUE;
  }

  // MUESTRA LAS SALIDAS
  // Sólo muestra las salidas existentes en la habitación actual (aunque no se puedan utilizar). No analiza requisitos.
  function exits() {
    $obj = load(ROOMFILE, 'data', 'exits');

    // Extrae sólo la clave de exits (las salidas presentes)
    foreach ($obj as $k => $v)
      $exits[] = $k;
    unset($obj);

    $num = count($exits);
    if ($num == 1)
      return "Sólo hay una salida posible: " . enumerate($exits) . ".";
    else 
      return "Las salidas posibles son " . enumerate($exits) . ".";
  }

  // Comprueba si un lugar no ha sido visitado, y lo marca como tal
  function check_visited($room) {
    if (!load(USERFILE, 'visited', $room))
      save(USERFILE, 'visited', $room, "1");
  }

  // VA HACIA UNA SALIDA
  function go_to($exit) {
    $obj = load(ROOMFILE, 'data', 'exits');

    // Si hay una salida definida...
    if (property_exists($obj, $exit)) {

      $data = $obj->{$exit};
      // Simple format
      if (is_string($data)) {
        save(USERFILE, 'info', 'room', $data);
        check_visited($data);
      }
      else {
        // Complex format
        if (required_check($data->required)) {
          save(USERFILE, 'info', 'room', $data->target);
          check_visited($data->target);
        }
        else {
          // No se cumplen requisitos
          if (property_exists($data, 'else')) {
            save(USERFILE, 'info', 'room', $data->else);
            check_visited($data->else);
          }
          else
            return $data->excuse;
        }
      }
      return 'OK';
    }
    return 'FAIL';
  }

  // Comprueba si hay una acción setAction o setObject (opcionales) y si existe, la aplica
  function check_saveparam($obj, $prop, $func) {

    if (property_exists($obj, $prop)) {
      
      $array = $obj->{$prop};

      // Simple format
      if (is_string($array))
        $array = array($array);

      foreach ($array as $i)
        $func($i);
    }
  }

  // MIRAR
  function mirar($words) {
    $words = busca_sinonimos($words);
    $obj = load(ROOMFILE, 'mirar', $words);

    // Si hay un objeto que mirar...
    if ($obj !== NULL) {

      // Simple format
      if (is_string($obj)) {
        return $obj;
      }
      else {
        // Complex format

        // Hay restricciones
        if (property_exists($obj, 'required')) {

          // ¿Se cumplen las restricciones?
          if (required_check($obj->required)) {
            // OK, SAFISFIED
            
            // Only first time
            $seen = $words . '_seen';
            if (!load(USERFILE, 'actions', $seen)) {
              //if ($obj->sound) // play sound
              check_saveparam($obj, 'setAction', function($p) { save(USERFILE, 'actions', $p, "1"); });
              check_saveparam($obj, 'setObject', function($p) { save(USERFILE, 'inventory', $p, "1"); });
              // PTE: Incrementar score
              save(USERFILE, 'actions', $seen, "1");
            }
            return $obj->message;
          }
          else {
            // NO, EXCUSE
            if ($obj->excuse)
              return $obj->excuse;
            // Si no hay excusa definida, muestra una de objeto no encontrado
            else
              return "FAIL";
          }

        }
        // No hay restricciones
        else {
          //if ($obj->sound) // play sound
          check_saveparam($obj, 'setAction', function($p) { save(USERFILE, 'actions', $p, "1"); });
          check_saveparam($obj, 'setObject', function($p) { save(USERFILE, 'inventory', $p, "1"); });
          return $obj->message;
        }

      }

    } // hay objeto

    // PTE: Buscar en inventario

    return "FAIL";    
  }

  // COGER
  function coger($words) {
    $words = busca_sinonimos($words);
    $obj = load(ROOMFILE, 'coger', $words);

    // Si hay un objeto que coger...
    if ($obj !== NULL) {

      // Simple format
      if (is_string($obj)) {
        save(USERFILE, 'inventory', $words, "1");
        return $obj;
      }
      else {
        // Complex format

        // Hay restricciones
        if (property_exists($obj, 'required')) {

          // ¿Se cumplen las restricciones?
          if (required_check($obj->required)) {
            // OK, SAFISFIED
            
            // Only first time
            if (!load(USERFILE, 'inventory', $words)) {
              //if ($obj->sound) // play sound
              check_saveparam($obj, 'setAction', function($p) { save(USERFILE, 'actions', $p, "1"); });
              check_saveparam($obj, 'setObject', function($p) { save(USERFILE, 'inventory', $p, "1"); });
              // PTE: Incrementar score
              save(USERFILE, 'inventory', $words, "1");
              return $obj->message;
            }
            else
              return "Ya lo tengo.";
          }
          else {
            // NO, EXCUSE
            if ($obj->excuse)
              return $obj->excuse;
            // Si no hay excusa definida, muestra una de objeto no encontrado
            else
              return "FAIL";
          }

        }
        // No hay restricciones
        else {
          //if ($obj->sound) // play sound
          check_saveparam($obj, 'setAction', function($p) { save(USERFILE, 'actions', $p, "1"); });
          check_saveparam($obj, 'setObject', function($p) { save(USERFILE, 'inventory', $p, "1"); });
          return $obj->message;
        }

      }

    } // hay objeto

    return "FAIL";    
  }

  // MUESTRA EL INVENTARIO
  // Analiza el inventario y lo devuelve en forma de lista al usuario.
  function inventory() {
    $obj = load(USERFILE, 'inventory');
    foreach ($obj as $k => $v)
      $inv[] = $k;
    unset($obj);

    $num = count($inv);
    if ($num == 0)
      return "No tengo nada.";
    else
      return "Tengo los siguientes objetos en mi inventario: " . enumerate($inv) . ".";
  }

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
    $item->m = "[Dejar de hablar]";
    $item->v = "dark";
    $talk->abort = $item;
    
    return $talk;
  }

?>