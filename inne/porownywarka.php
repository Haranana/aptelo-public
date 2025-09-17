<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0) {

    if (Sesje::TokenSpr()) {

        //
        $DoPorownaniaId = '';
        foreach ($_SESSION['produktyPorownania'] AS $Id) {
            $DoPorownaniaId .= $Id . ',';
        }
        $DoPorownaniaId = substr((string)$DoPorownaniaId, 0, -1);
        //
        
        $DodatkowePolaPorownywarka = array();
        
        $zapytanie = "SELECT pef.products_extra_fields_id, pef.products_extra_fields_name 
                        FROM products_extra_fields pef, products_to_products_extra_fields ppxf 
                       WHERE pef.products_extra_fields_status = '1' AND (pef.languages_id = '0' OR pef.languages_id = '".(int)$_SESSION['domyslnyJezyk']['id']."')
                             AND pef.products_extra_fields_compare = '1' 
                             AND pef.products_extra_fields_id = ppxf.products_extra_fields_id 
                             AND ppxf.products_id in (" . $DoPorownaniaId . ")
                       ORDER BY products_extra_fields_order";

        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            while ($info = $sql->fetch_assoc()) {
                $DodatkowePolaPorownywarka[$info['products_extra_fields_id']] = $info['products_extra_fields_name'];
            }
            
        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);
        
        //

        $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId);
        //
        $sqlNazwy = $db->open_query($zapNazwy);
        //
        $DefinicjePol = array('nazwa', 'zdjecie', 'cena', 'nr_kat', 'producent', 'opis', 'koszyk');
        //
        $TablicaProduktow = array();
        $TablicaProduktow[] = array( 'nazwa' => $GLOBALS['tlumacz']['NAZWA_PRODUKTU'],
                                     'zdjecie' => $GLOBALS['tlumacz']['INFO_FOTO'],
                                     'cena' => $GLOBALS['tlumacz']['CENA'],
                                     'nr_kat' => $GLOBALS['tlumacz']['NUMER_KATALOGOWY'],
                                     'producent' => $GLOBALS['tlumacz']['PRODUCENT'],
                                     'opis' => $GLOBALS['tlumacz']['OPIS'],
                                     'koszyk' => '&nbsp;');

        $DodatkowePolaTablicaOgolna = array();
        //
        while ($infc = $sqlNazwy->fetch_assoc()) {
            //
            $Produkt = new Produkt( $infc['products_id'], '', '', '', false );
            
            if ( $Produkt->CzyJestProdukt ) {
            
                $Produkt->ProduktProducent();
                $Produkt->ProduktDodatkowePola();
                $Produkt->ProduktKupowanie(); 
                //
                $DodatkowePola = '<div class="DodatkowePola">';
                
                foreach ($Produkt->dodatkowePolaOpis AS $Id => $TablicaPola) {
                    //
                    // jezeli jest w porownywarce jako osobna pozycja to nie wyswietli
                    if ( !isset($DodatkowePolaPorownywarka[$Id]) ) {
                         //
                         $DodatkowePola .= '<div class="PolaTbl">';
                         $DodatkowePola .= '<div>' . $TablicaPola['nazwa'] . ':</div><div><b>' . $TablicaPola['wartosc'] . '</b></div>';
                         $DodatkowePola .= '</div>';
                         //
                    }
                    //
                }
                
                $DodatkowePola .= '</div>';
                
                $Koszyk = '<div class="Zakup">';
                
                    // jezeli jest aktywne kupowanie produktow
                    if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                        //
                        if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                            $Koszyk .= $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                        } else {
                            $Koszyk .= $Produkt->zakupy['przycisk_szczegoly'];
                        }
                        //
                    }            
                    
                $Koszyk .= '</div>';                 
                
                //
                $TablicaProduktow[] = array( 'nazwa' => '<h3>' . $Produkt->info['link'] . '</h3>',
                                             'zdjecie' => '<div class="Foto">' . $Produkt->fotoGlowne['zdjecie_ikony'] . '</div>',
                                             'cena' => $Produkt->info['cena'],
                                             'nr_kat' => $Produkt->info['nr_katalogowy'],
                                             'producent' => $Produkt->info['nazwa_producenta'],
                                             'opis' => '<div class="Opisy">' . $Produkt->info['opis'] . $DodatkowePola . '</div>',
                                             'koszyk' => $Koszyk);        

                unset($Koszyk);
                
                $DodatkowePolaTablica = $Produkt->dodatkowePola;
                
                $LicznikPol = 0;
                foreach ( $DodatkowePolaPorownywarka as $IdPola => $PolePorownywarki ) {
                    //
                    if ( isset($DodatkowePolaTablica[ $IdPola ]) ) {
                        //
                        if ( !empty($DodatkowePolaTablica[ $IdPola ][ 'wartosc' ]) ) {
                             //
                             $DodatkowePolaTablicaOgolna[ $LicznikPol ][ 'nazwa' ] = $PolePorownywarki;
                             $DodatkowePolaTablicaOgolna[ $LicznikPol ][ 'produkty' ][ $infc['products_id'] ] = $DodatkowePolaTablica[ $IdPola ][ 'wartosc' ];
                             //
                        }
                        //
                    } else {
                        //
                        $DodatkowePolaTablicaOgolna[ $LicznikPol ][ 'nazwa' ] = $PolePorownywarki;
                        $DodatkowePolaTablicaOgolna[ $LicznikPol ][ 'produkty' ][ $infc['products_id'] ] = '-';
                        //                      
                    }
                    //
                    $LicznikPol++;
                    //
                }
                
                unset($Produkt, $DodatkowePola, $DodatkowePolaTablica, $LicznikPol);
                //
                
            }
            
        }
        $db->close_query($sqlNazwy); 
        unset($zapNazwy, $DoPorownaniaId, $infc, $LicznikPol);
        
        foreach ( $DodatkowePolaTablicaOgolna as $Klucz => $Pole ) {
            //
            if ( empty($DodatkowePolaTablicaOgolna[ $Klucz ]) ) {
                 unset($DodatkowePolaTablicaOgolna[ $Klucz ]);
            }
            //
        }
        //
        echo '<style>';
        echo '#PorownywarkaTable .Opisy { width:auto; }';
        echo '#PorownywarkaTable img { max-width:100%; height:auto; }';
        
        if ( Wyglad::TypSzablonu() != true ) {
             //
             echo '#PorownywarkaTable .Zakup { display:inline-block; margin:0px auto; margin-top:10px; }';
             echo '#PorownywarkaTable .Zakup .InputIlosc { float:left; text-align:center; width:30px; margin:4px 5px 4px 0px; }';
             echo '#PorownywarkaTable .Zakup em { display:inline-block; float:left; font-style:normal; margin:10px 15px 5px 0px; }';       
             //
        }
        
        echo '</style>';
        echo '<div style="overflow-x:auto;overflow-y:auto;height:100%"><div><table id="PorownywarkaTable" style="width:' . ((count($TablicaProduktow) * 250) + 200) . 'px">';
        //
        for ($t = 0, $u = count($DefinicjePol); $t < $u; $t++) {
            //
            // pomija opis - bedzie na koncu                
            if ( $DefinicjePol[$t] != 'opis' && $DefinicjePol[$t] != 'koszyk' ) {
                //
                echo '<tr>';
                //
                foreach ($TablicaProduktow AS $Wartosc) {
                    //
                    if ( isset($Wartosc[ $DefinicjePol[$t] ]) ) {
                         //
                         echo '<td>' . $Wartosc[ $DefinicjePol[$t] ] . '</td>';
                         //
                    }
                    //
                }
                //
                echo '</tr>';
                //
            }
            //
        }
        
        // dodatkowe pola
        foreach ( $DodatkowePolaTablicaOgolna as $PoleDodatkowe ) {
            //
            echo '<tr>';
            //
            echo '<td style="vertical-align:middle">' . $PoleDodatkowe[ 'nazwa' ] . '</td>';
            //
            foreach ( $PoleDodatkowe[ 'produkty' ] as $Produkty ) {
                //
                echo '<td style="vertical-align:middle">' . $Produkty . '</td>';
                //
            }
            //
            echo '</tr>';
            //
        }

        // tylko opis - na koncu
        for ($t = 0, $u = count($DefinicjePol); $t < $u; $t++) {
            //
            // pomija opis - bedzie na koncu                
            if ( $DefinicjePol[$t] == 'opis' ) {
                //
                echo '<tr>';
                //
                foreach ($TablicaProduktow AS $Wartosc) {
                    //
                    if ( isset($Wartosc[ $DefinicjePol[$t] ]) ) {
                         //
                         echo '<td>' . $Wartosc[ $DefinicjePol[$t] ] . '</td>';
                         //
                    }
                    //
                }
                //
                echo '</tr>';
                //
            }
            //
        }       


        // tylko do koszyka - na koncu
        for ($t = 0, $u = count($DefinicjePol); $t < $u; $t++) {
            //
            // pomija opis - bedzie na koncu                
            if ( $DefinicjePol[$t] == 'koszyk' ) {
                //
                echo '<tr>';
                //
                foreach ($TablicaProduktow AS $Wartosc) {
                    //
                    if ( isset($Wartosc[ $DefinicjePol[$t] ]) ) {
                         //
                         echo '<td>' . $Wartosc[ $DefinicjePol[$t] ] . '</td>';
                         //
                    }
                    //
                }
                //
                echo '</tr>';
                //
            }
            //
        }            

        //
        echo '</table></div></div>';
        //
        
    }
    
}

?>