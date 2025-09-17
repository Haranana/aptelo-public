<?php

class TagiChmura {
  
    public static function TablicaFraz($ilosc = 10) {
      
        global $db;
        
        $TablicaWynik = array();
      
        $zapytanie = "SELECT search_key, freq FROM customers_searches WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
        $sql = $db->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
          
            $TablicaFraz = array();

            while ($info = $sql->fetch_assoc()) {
              
                // usuwa niepoprawne znaki
                $CoZmienic = array("'", '"', '>', '<', '(', ')', '*', '+', '[', ']', '|', '?', '\\', '/', '//');
                $NaCozmienic = '';
                $info["search_key"] = str_replace($CoZmienic, (string)$NaCozmienic, (string)$info["search_key"]);
                $info["search_key"] = trim((string)$info["search_key"]);
                  
                $TablicaFraz[] = array('fraza' => $info["search_key"], 'ilosc' => $info["freq"]);
                
            }

            $TablicaWynik = Funkcje::wylosujElementyTablicyJakoTablica($TablicaFraz, $ilosc);
            $TablicaWynik = Funkcje::wymieszajTablice($TablicaWynik);
            
        }

        $db->close_query($sql); 
        unset($zapytanie, $info);      
        
        return $TablicaWynik;
      
    }
  
    public static function TagiGeneruj($ilosc = 10) {
      
        $tagi = TagiChmura::TablicaFraz($ilosc);
      
        $ciagWynik = '';
      
        // minimalna i maksymalna wiekosc czcionki
        
        $minFontSize = 0.9;
        $maxFontSize = 2.2;
        
        $minOpacity = 0.5;
        $maxOpacity = 1;
        
        // minimalna i maksymalna ilosc wyswietlen
        if ( is_array($tagi) && count($tagi) > 0 ) {
            $minIlosc = min(array_column($tagi, 'ilosc'));
            $maxIlosc = max(array_column($tagi, 'ilosc'));
        } else {
            $minIlosc = 1;
            $maxIlosc = 2;
        }

        // przelicz zakres ilosci wyswietlen na zakres wielkosci czcionki
        $iloscZakres = $maxIlosc - $minIlosc;
        $fontSizeRange = $maxFontSize - $minFontSize;
        $fontSizeOpacity = $minOpacity - $maxOpacity;
        if ( $maxIlosc == $minIlosc ) {
            $iloscZakres = 1;
        }

        // sortuj tagi wedlug ilosci wyswietlen
        
        usort($tagi, function($a, $b) {
            return $b['ilosc'] - $a['ilosc'];
        });
        
        // wygeneruj znacznik linku dla kazdego tagu
        
        foreach ($tagi as $tag) {
          
            // oblicz wielkosc czcionki dla danego tagu
            
            $fontSize = number_format((float)($minFontSize + (($tag['ilosc'] - $minIlosc) * $fontSizeRange / $iloscZakres)), 0, '.', '');
            $fontOpacity = number_format((float)($minOpacity + (($tag['ilosc'] - $minIlosc) * $fontSizeRange / $iloscZakres)), 2, '.', '');
            
            // wyswietli link
            $ciagWynik .= '<a style="font-size: ' . ($fontSize * 100) . '%;opacity:' . $fontOpacity . '" href="wyszukiwanie-' . urlencode($tag['fraza']) . '.html">' . $tag['fraza'] . '</a>';

        }
        
        return $ciagWynik;
        
    }
    
}
?>