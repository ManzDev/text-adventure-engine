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
    $words = _('WORDS_CONNECTORS');
    $data = str_replace($words, ' ', $data);

    // Elimina espacios sobrantes
    $data = trim($data);

    // Contabiliza las palabras
    $words = explode(' ', $data);
    $num = count($words);

    // Traduce abreviaturas unitarias (n, s, e, o, i, x)
    if ($num == 1) {
      switch ($data) {
        case _('NORTH_SHORT'):
          $data = _('NORTH_VERB');
          break;
        case _('SOUTH_SHORT'):
          $data = _('SOUTH_VERB');
          break;
        case _('EAST_SHORT'):
          $data = _('EAST_VERB');
          break;
        case _('WEST_SHORT'):
          $data = _('WEST_VERB');
          break;
        case _('INVENTORY_SHORT'):
          $data = _('INVENTORY_VERB');
          break;
        case _('EXIT_SHORT'):
          $data = _('EXIT_VERB');
          break;
      }
    }

    // Traduce dirección compuesta (ir al norte, andar hacia sur, caminar al este...)
    if ($num == 2) {
      if (in_array($words[0], _('GOTO_SYN')))
        $data = $words[1];

      if (in_array($words[0], _('LOOK_SYN'))) {
        $words[0] = _('LOOK_VERB');
        $data = implode(' ', $words);
      }

      if (in_array($words[0], _('TAKE_SYN'))) {
        $words[0] = _('TAKE_VERB');
        $data = implode(' ', $words);
      }

      if (in_array($words[0], _('TALK_SYN'))) {
        $words[0] = _('TALK_VERB');
        $data = implode(' ', $words);
      }

    }

    return $data;
  }

?>