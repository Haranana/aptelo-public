<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;
    
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && Sesje::TokenSpr()) {

        $ResetWysylki = false;

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'REJESTRACJA') ), $GLOBALS['tlumacz'] );

        //zapisanie danych klienta do sesji - START

        if (!isset($_SESSION['adresDostawy'])) {
            $_SESSION['adresDostawy'] = array();
        }

        $krajPrzedZmiana = $_SESSION['krajDostawy']['id'];
        $telefonPrzedZmiana = ((isset($_SESSION['adresDostawy']['telefon'])) ? $_SESSION['adresDostawy']['telefon'] : '');
        $rodzajDostawyPrzedZmiana = ((isset($_SESSION['rodzajDostawy']['wysylka_klasa'])) ? $_SESSION['rodzajDostawy']['wysylka_klasa'] : '');

        unset($_SESSION['adresDostawy']);
        
        $wybranyDokument = '';
        if ( isset($_SESSION['adresFaktury']['dokument']) && !empty($_SESSION['adresFaktury']['dokument']) ) {
             $wybranyDokument = $_SESSION['adresFaktury']['dokument'];
        }

        $_SESSION['adresDostawy'] = array('imie' => $filtr->process($_POST['imie']),
                                          'nazwisko' => $filtr->process($_POST['nazwisko']),
                                          'firma' => $filtr->process($_POST['nazwa_firmy']),
                                          'ulica' => $filtr->process($_POST['ulica']),
                                          'kod_pocztowy' => $filtr->process($_POST['kod_pocztowy']),
                                          'miasto' => $filtr->process($_POST['miasto']),
                                          'telefon' => ( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' ),
                                          'panstwo' => $filtr->process($_POST['panstwo']),
                                          'wojewodztwo' => ( isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : '' )
        );

        if (!isset($_SESSION['adresFaktury'])) {
            $_SESSION['adresFaktury'] = array();
        }
        unset($_SESSION['adresFaktury']);
        
        // jezeli faktura na osobe fizyczna
        $imie = ''; $nazwisko = ''; $pesel = '';
        if ( isset($_POST['osobowosc']) && (int)$_POST['osobowosc'] == 1 ) {
            $imie = $filtr->process($_POST['imieFaktura']);
            $nazwisko = $filtr->process($_POST['nazwiskoFaktura']);
            if ( isset($_POST['peselFaktura']) ) {
                $pesel = $filtr->process($_POST['peselFaktura']);
            } else {
                $pesel = '';
            }
        }
        $firma = ''; $nip = '';
        if ( isset($_POST['osobowosc']) && (int)$_POST['osobowosc'] == 0 ) {
            $firma = $filtr->process($_POST['nazwa_firmyFaktura']);    
            $nip = $filtr->process($_POST['nip_firmyFaktura']);
        }                      
        //     
        
        $_SESSION['adresFaktury'] = array('imie' => $imie,
                                          'nazwisko' => $nazwisko,
                                          'pesel' => $pesel,
                                          'firma' => $firma,
                                          'nip' => $nip,
                                          'ulica' => $filtr->process($_POST['ulicaFaktura']),
                                          'kod_pocztowy' => $filtr->process($_POST['kod_pocztowyFaktura']),
                                          'miasto' => $filtr->process($_POST['miastoFaktura']),
                                          'panstwo' => $filtr->process($_POST['panstwoFaktura']),
                                          'wojewodztwo' => ( isset($_POST['wojewodztwoFaktura']) ? $filtr->process($_POST['wojewodztwoFaktura']) : '' )
        );
        
        if ( $wybranyDokument != '' ) {
             $_SESSION['adresFaktury']['dokument'] = $wybranyDokument;
        }
        
        unset($imie, $nazwisko, $firma, $nip, $wybranyDokument);

        if ( $krajPrzedZmiana != $_POST['panstwo'] ) {

            $ResetWysylki = true;

            $zapytanie_panstwo = "SELECT c.countries_iso_code_2
                                    FROM countries c
                                    WHERE c.countries_id = '".(int)$_POST['panstwo']."'";

            $sql_panstwo = $GLOBALS['db']->open_query($zapytanie_panstwo);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql_panstwo) > 0) { 
            
                $info_panstwo = $sql_panstwo->fetch_assoc();

                unset($_SESSION['krajDostawy'], $_SESSION['rodzajDostawy'], $_SESSION['rodzajPlatnosci']);

                $_SESSION['krajDostawy'] = array();
                $_SESSION['krajDostawy'] = array('id' => $filtr->process($_POST['panstwo']),
                                                 'kod' => $info_panstwo['countries_iso_code_2']);
                                                 
            }
            
            unset($zapytanie_panstwo);
            $GLOBALS['db']->close_query($sql_panstwo);

        }

        if ( ($rodzajDostawyPrzedZmiana == 'wysylka_inpost_international' || $rodzajDostawyPrzedZmiana == 'wysylka_inpost_weekend' || $rodzajDostawyPrzedZmiana == 'wysylka_inpost_eko' || $rodzajDostawyPrzedZmiana == 'wysylka_inpost') && (isset($_POST['telefon']) && $telefonPrzedZmiana != $_POST['telefon']) && !Funkcje::CzyNumerGSM($_POST['telefon']) ) {

            $ResetWysylki = true;
            unset($_SESSION['rodzajDostawy'], $_SESSION['rodzajPlatnosci']);

        }
        
        // tylko cenny netto - zwolnienie dla UE
        //
        $netto_eu = 'nie';
        //
        if ( NETTO_DLA_UE_AKTYWACJA == 'automatycznie' && NETTO_DLA_UE == 'tak' ) {
            //
            if ( isset($_POST['osobowosc']) && (int)$_POST['osobowosc'] == 0 ) {
                 //
                 if ( !empty($_POST['nip_firmyFaktura']) ) {
                      //
                      if ( isset($_SESSION['krajDostawyDomyslny']['id']) && isset($_POST['panstwoFaktura']) && (int)$_POST['panstwoFaktura'] != $_SESSION['krajDostawyDomyslny']['id'] ) {
                           //
                           $zapytanie_kraj = "SELECT countries_id, countries_iso_code_2 FROM countries WHERE countries_id = '" . (int)$_POST['panstwoFaktura'] . "'";
                           $sql_kraj = $GLOBALS['db']->open_query($zapytanie_kraj);
                           
                           if ((int)$GLOBALS['db']->ile_rekordow($sql_kraj) > 0) { 
                           
                               $wynik_kraj = $sql_kraj->fetch_assoc();                       
                               //
                               $sprNip = Klient::sprawdzNip($filtr->process($_POST['nip_firmyFaktura']), $wynik_kraj['countries_iso_code_2']);
                               //
                               $GLOBALS['db']->close_query($sql_kraj);
                               unset($zapytanie_kraj, $wynik_kraj);                        
                               //
                               if ( $sprNip == true ) {
                                    //
                                    $netto_eu = 'tak';
                                    //
                               }
                               //
                               
                           }
                           
                           $GLOBALS['db']->close_query($sql_kraj);
                           unset($zapytanie_kraj);
                           
                      }
                      //
                 }
                 //
            }
            //
        }
        
        $ResetCen = false;
        
        if ( isset($_SESSION['netto']) && $_SESSION['netto'] != $netto_eu && NETTO_DLA_UE_AKTYWACJA == 'automatycznie' && NETTO_DLA_UE == 'tak' ) {
          
            $_SESSION['netto'] = $netto_eu;
            
            $pola = array(array('vat_netto', (($netto_eu == 'tak') ? 1 : 0)));    

            $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . $_SESSION['customer_id'] . "'");	
            unset($pola);  

            $ResetCen = true;
            
            $GLOBALS['koszykKlienta']->PrzeliczKoszyk();

        }
        
        if ( $ResetWysylki == true || $ResetCen == true ) {

            echo '<div id="PopUpInfo" class="PopUpZmianaKrajuWysylki" aria-live="assertive" aria-atomic="true">';  
            
            if ( $ResetWysylki == true || $netto_eu == 'nie' ) {

                 echo $GLOBALS['tlumacz']['ZMIANA_KRAJU_WYSYLKI'];
                 
            }
            
            if ( $ResetCen == true && $netto_eu == 'tak' ) {
              
                if (( $_SESSION['adresDostawy']['panstwo'] == $_SESSION['adresFaktury']['panstwo'] ) || ( isset($_SESSION['netto_wymuszone']) && $_SESSION['netto_wymuszone'] == 'tak' )) {

                      echo '<div><br /><b style="color:#ff0000">' . $GLOBALS['tlumacz']['INFO_CENY_NETTO'] . '</b></div>';
                      
                 }

            }            

            echo '</div>';
            
            echo '<div id="PopUpPrzyciski" class="PopUpZmianaKrajuWysylkiPrzyciski">';  
            
                if ( WLACZENIE_SSL == 'tak' ) {
                  $link = ADRES_URL_SKLEPU_SSL . '/koszyk.html';
                } else {
                  $link = 'koszyk.html';
                }

                echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_KOSZYKA'].'</a>'; 
                unset($link);

            echo '</div>';

        }

    }

}
?>