<?php

	// PARSER (TEMPORAL)
  // This parser create a easy-analize output sentence for game.
  // Current parser is very basic and only remove chars or unneeded words
  // Sample:
  // -> ENTRADA: "quiero mirar el coche aparcado"
  // -> SALIDA: "mirar coche"
  //
  // NOTE: Parser need be fast and efficient (run constantly). Avoid regexp or expensive process.
  function parser($data) {

  	// Suprimimos posibles espacios múltiples
  	$data = preg_replace( '/\s+/', ' ', strtolower($data));

  	// Reemplazamos por letras no acentuadas
  	$data = str_replace(array('á', 'é', 'í', 'ó', 'ú', 'ü', 'à', 'è', 'ì', 'ò', 'ù'),
                       	array('a', 'e', 'i', 'o', 'u', 'u', 'a', 'e', 'i', 'o', 'u'), $data);

  	// Eliminar caracteres especiales
    $data = str_replace(array('?', '¿', '¡', '!', ',', '.', '|', '\\', '/', ';', ':'), '', $data);

    // Eliminar palabras superfluas usadas de conectores (artículos o preposiciones)
    $words = __('WORDS_CONNECTORS');
    $data = str_replace($words, ' ', $data);

    // Elimina espacios sobrantes
    $data = trim($data);

    // Contabiliza las palabras
    $words = explode(' ', $data);
    $num = count($words);

    if ($words[0] == __('LOOK_SHORT')) {
      $words[0] = __('LOOK_VERB');
      $data = implode(' ', $words);
    }

    // Traduce abreviaturas unitarias (n, s, e, o, i, x)
    if ($num == 1) {
      switch ($data) {
        case __('NORTH_SHORT'):
          $data = __('NORTH_VERB');
          break;
        case __('SOUTH_SHORT'):
          $data = __('SOUTH_VERB');
          break;
        case __('EAST_SHORT'):
          $data = __('EAST_VERB');
          break;
        case __('WEST_SHORT'):
          $data = __('WEST_VERB');
          break;
        case __('INVENTORY_SHORT'):
          $data = __('INVENTORY_VERB');
          break;
        case __('EXIT_SHORT'):
          $data = __('EXIT_VERB');
          break;
      }
    }

    // Traduce dirección compuesta (ir al norte, andar hacia sur, caminar al este...)
    if ($num == 2) {
      if (in_array($words[0], __('GOTO_SYN')))
        $data = $words[1];

      if (in_array($words[0], __('LOOK_SYN'))) {
        $words[0] = __('LOOK_VERB');
        $data = implode(' ', $words);
      }

      if (in_array($words[0], __('TAKE_SYN'))) {
        $words[0] = __('TAKE_VERB');
        $data = implode(' ', $words);
      }

      if (in_array($words[0], __('TALK_SYN'))) {
        $words[0] = __('TALK_VERB');
        $data = implode(' ', $words);
      }
    }

    return $data;
  }

?>