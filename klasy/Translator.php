<?php

/**
* Klasa do obslugi tlumaczen
*/

class Translator {

  public $language;

  public function __construct($jezyk = '1') {
    $this->language = $jezyk;
  }

  public function tlumacz($sekcja = null, $element = null, $glowne = false) {

    $warunek = '';
    if (isset($sekcja)) {
        
        if (is_array($sekcja)) {
            
            $warunek .= " AND ( "; 
            foreach ($sekcja as $sek) {
                //
                 $warunek .= "s.section_name = '" . $sek . "' OR ";
                //
            }
            $warunek = substr((string)$warunek, 0, -3) . " )";
        
        } else {
        
            $warunek = " AND s.section_name = '" . $sekcja . "'";
            
        }
        $zapytanie = "SELECT
                      e.translate_constant_id AS id, e.translate_constant AS element, 
                      ec.translate_value AS content,
                      ec.language_id AS language
                      FROM (translate_constant e, translate_section s, translate_value ec)
                      WHERE e.translate_constant_id = ec.translate_constant_id AND
                      e.section_id = s.section_id " . $warunek . "";

    } else {
        $zapytanie = "SELECT
                      e.translate_constant_id AS id, e.translate_constant AS element, 
                      ec.translate_value AS content,
                      ec.language_id AS language
                      FROM (translate_constant e, translate_section s, translate_value ec)
                      WHERE e.translate_constant_id = ec.translate_constant_id AND
                      e.section_id = s.section_id";
    }

    if (isset($element)) {
        $zapytanie .= " AND e.translate_constant = '" . $element . "'";
    }
   
    $elem = array();  
    
    // cache zapytania
    $WynikCache = false;
    
    // cache tylko dla glownych tlumaczen w start.php
    if ( $glowne == true ) {
         $WynikCache = $GLOBALS['cache']->odczytaj('Tlumaczenia_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
    }

    //utworzenie tymczasowej tablicy zawierajacej wszystkie stale
    $sql = $GLOBALS['db']->open_query($zapytanie);

    $a = array();
    $b = array();

    while ($wiersz = $sql->fetch_assoc()) {
        if ( $wiersz['language'] == '1' ) {
            $a[$wiersz['element']] = $wiersz['content'];
        }
        if ( $wiersz['language'] == $this->language ) {
            $b[$wiersz['element']] = $wiersz['content'];
        }
    }

    $WszystkieStale = array_merge($a, $b);
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);

    if ( !$WynikCache ) {        

        foreach ( $WszystkieStale as $Stala => $Tresc ) {
          
            $zastapElement = false;

            if (!isset($elem[$Stala])) {
                $zastapElement = true;
            }
            
            // zamienia linki w tlumaczeniach
            if ( strpos((string)$Tresc, '{__LINK') > -1 ) {
                 //
                 $preg = preg_match_all('|{__LINK:([0-9a-zA-Z-._]+?)}|', $Tresc, $matches);
                 foreach ($matches[1] as $WartoscLink) {
                     //
                     $Tresc = str_replace('{__LINK:' . $WartoscLink . '}', '<a href=\'' . $WartoscLink . '\'>', (string)$Tresc);
                    //
                 }           
                 $Tresc = str_replace('{/__LINK}', '</a>', (string)$Tresc);            
                 //
            }

            if ($zastapElement == true) {
                $elem[$Stala] = $Tresc;
            }

        }

        if ( $glowne == true ) {
             $GLOBALS['cache']->zapisz('Tlumaczenia_' . $_SESSION['domyslnyJezyk']['kod'], $elem, CACHE_INNE);   
        }
        
      } else {
      
        $elem = $WynikCache;
        
    }

    unset($zapytanie);
    return $elem;
  }

}
?>