<?php

if ( isset($pobierzFunkcje) ) {

    // czy wogole sa jakies dodatkowe pola tekstowe
    if ( Funkcje::OgolnaIloscDodatkowychPolTekstowych() > 0 ) {  

        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_pola_tekstowe_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);

        if ( !$WynikCache && !is_array($WynikCache) ) {        

            $zapisz = array();
            
            $zapytanie = "SELECT pe.products_text_fields_id, 
                                 pe.products_text_fields_type, 
                                 pe.products_text_fields_file_type,
                                 pe.products_text_fields_file_size,                             
                                 pe.products_text_fields_required,                             
                                 pei.products_text_fields_name,
                                 pei.products_text_fields_description,
                                 pei.products_text_fields_default_text
                            FROM products_text_fields pe
                      RIGHT JOIN products_text_fields_info pei ON pei.products_text_fields_id = pe.products_text_fields_id AND pei.languages_id = '" . $this->jezykDomyslnyId . "'
                      RIGHT JOIN products_to_text_fields pep ON pep.products_text_fields_id = pe.products_text_fields_id AND pep.products_id = '" . $this->id_produktu . "'
                           WHERE pe.products_text_fields_status = '1' ORDER BY pe.products_text_fields_order";        
            
            $sql = $GLOBALS['db']->open_query($zapytanie);

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
              
                while ($info = $sql->fetch_assoc()) {
                    //
                    switch( $info['products_text_fields_type'] ) {
                        case 0: $typ_pola = 'input'; break;
                        case 1: $typ_pola = 'textarea'; break;
                        case 2: $typ_pola = 'plik'; break;
                        case 3: $typ_pola = 'data'; break;
                    }            
                    //
                    $zapisz[] = array( 'id_pola'  => $info['products_text_fields_id'],
                                       'nazwa'    => $info['products_text_fields_name'],
                                       'opis'     => $info['products_text_fields_description'],
                                       'domyslny' => $info['products_text_fields_default_text'],
                                       'typ'      => $typ_pola,
                                       'formaty'  => $info['products_text_fields_file_type'],
                                       'wymagane'  => $info['products_text_fields_required'],
                                       'wielkosc' => $info['products_text_fields_file_size'] );
                                                            
                    unset($typ_pola);

                }
                
                unset($info);
                
            }
            //
            $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_pola_tekstowe_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
            //
            $GLOBALS['db']->close_query($sql); 
            
            $this->dodatkowePolaTekstowe = $zapisz;
            //
            unset($zapytanie, $zapisz, $info);

        } else {
        
            $this->dodatkowePolaTekstowe = $WynikCache;
            
        }
        
    }
        
}
       
?>