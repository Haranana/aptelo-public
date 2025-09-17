<?php

if ( isset($pobierzFunkcje) ) {

    // czy wogole sa jakies dodatkowe pola
    if ( Funkcje::OgolnaIloscDodatkowychPol() > 0 ) {

        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_pola_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);

        if ( !$WynikCache && !is_array($WynikCache) ) {
            
            $zapisz = array();
            
            $zapytanie = "SELECT pef.products_extra_fields_id, pef.products_extra_fields_location, pef.products_extra_fields_number, pef.products_extra_fields_image, pef.products_extra_fields_name, pef.products_extra_fields_icon, ptf.products_extra_fields_value, ptf.products_extra_fields_value_1, ptf.products_extra_fields_value_2, ptf.products_extra_fields_link, pef.products_extra_fields_compare
                            FROM products_extra_fields pef
                       LEFT JOIN products_to_products_extra_fields ptf
                              ON ptf.products_extra_fields_id = pef.products_extra_fields_id
                        WHERE ptf.products_id = '" . $this->id_produktu . "' AND pef.products_extra_fields_status = '1' AND pef.products_extra_fields_view = '1' AND ptf.products_extra_fields_value <> '' AND (pef.languages_id = '0' OR pef.languages_id = '" . $this->jezykDomyslnyId . "')
                     ORDER BY products_extra_fields_order";
                     
            $sql = $GLOBALS['db']->open_query($zapytanie);

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
              
                while ($info = $sql->fetch_assoc()) {
                    //
                    if ( (int)$info['products_extra_fields_number'] == 1 ) {
                          //
                          $info['products_extra_fields_value_1'] = '';
                          $info['products_extra_fields_value_2'] = '';
                          //
                    }
                    //
                    if ( (int)$info['products_extra_fields_number'] == 1 ) {
                         //
                         $info['products_extra_fields_value'] = number_format((float)$info['products_extra_fields_value'],2,',','');
                         //
                    }
                    //
                    $zapisz[] = array( 'id'           => $info['products_extra_fields_id'],
                                       'lokalizacja'  => $info['products_extra_fields_location'],
                                       'porownywarka' => $info['products_extra_fields_compare'],
                                       'zdjecie'      => $info['products_extra_fields_image'],
                                       'nazwa'        => $info['products_extra_fields_name'],
                                       'format'       => (((int)$info['products_extra_fields_number'] == 0) ? 'tekst' : 'liczba'),
                                       'wartosc'      => (((int)$info['products_extra_fields_image'] == 1) ? $info['products_extra_fields_value'] : $info['products_extra_fields_value'] . ((!empty($info['products_extra_fields_value_1'])) ? ', ' . $info['products_extra_fields_value_1'] : '') . ((!empty($info['products_extra_fields_value_2'])) ? ', ' . $info['products_extra_fields_value_2'] : '')),
                                       'wartosc_1'    => $info['products_extra_fields_value'],
                                       'wartosc_2'    => $info['products_extra_fields_value_1'],
                                       'wartosc_3'    => $info['products_extra_fields_value_2'],
                                       'link'         => $info['products_extra_fields_link'],
                                       'ikona'        => $info['products_extra_fields_icon'],
                        );
                    //
                }
                
                unset($info);
                
            }
            
            $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_pola_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
            //
            $GLOBALS['db']->close_query($sql);
            //
            $WynikCache = $zapisz;
            //
            unset($zapytanie, $zapisz);
            //
        }     

        foreach ( $WynikCache as $info ) {
            //
            // sprawdzi czy nie jest link
            $Wartosc = $info['wartosc'];
            // sprawdzi czy nie jest obrazek
            if ( $info['zdjecie'] == 1 ) {
                //
                $Wartosc = '<img src="' . KATALOG_ZDJEC . '/domyslny.webp" alt="' . $info['nazwa'] . '" width="500" height="500" />';
                //
                if ( file_exists(KATALOG_ZDJEC . '/' . $info['wartosc']) ) {
                     //
                     list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $info['wartosc']);
                     //              
                     $Wartosc = '<img src="' . KATALOG_ZDJEC . '/' . $info['wartosc'] . '" alt="' . $info['nazwa'] . '" title="' . $info['nazwa'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' />';
                     //
                     unset($szerokosc, $wysokosc, $typ, $atrybuty);
                     //
                }
                //
            }
            if ( !empty($info['link']) ) {
                $Wartosc = '<a href="' . $info['link'] . '">' . $Wartosc . '</a>';
            }                
            //
            // jezeli wyswietlane obok zdjecia
            if ( $info['lokalizacja'] == 'foto' ) {
                $this->dodatkowePolaFoto[ $info['id'] ] = array( 'id'      => $info['id'],
                                                                 'nazwa'   => $info['nazwa'],
                                                                 'wartosc' => $Wartosc,
                                                                 'rodzaj'  => ( $info['zdjecie'] == 1 ? 'zdjecie' : 'tekst' ),
                                                                 'ikona'   => $info['ikona'] );
            }
            // jezeli wyswietlane pod opisem
            if ( $info['lokalizacja'] == 'opis' ) {
                $this->dodatkowePolaOpis[ $info['id'] ] = array( 'id'      => $info['id'],
                                                                 'nazwa'   => $info['nazwa'],
                                                                 'wartosc' => $Wartosc,
                                                                 'rodzaj'  => ( $info['zdjecie'] == 1 ? 'zdjecie' : 'tekst' ),
                                                                 'ikona'   => $info['ikona'] );

            }  

            // wszystkie pola produktu po id
            $this->dodatkowePola[ $info['id'] ] = array( 'id'      => $info['id'],
                                                         'nazwa'   => $info['nazwa'],
                                                         'wartosc' => $Wartosc,
                                                         'rodzaj'  => ( $info['zdjecie'] == 1 ? 'zdjecie' : 'tekst' ),
                                                         'ikona'   => $info['ikona'] );
            //
            unset($Wartosc);
            //
        }

        unset($info);
        
    }
        
}
       
?>