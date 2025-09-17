<?php
chdir('../');            

if (isset($_POST['value']) && (int)$_POST['value'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    $ResetWysylki = false;

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'REJESTRACJA') ), $GLOBALS['tlumacz'] );

    //zapisanie danych klienta do sesji - START

    if (!isset($_SESSION['adresDostawy'])) {
        $_SESSION['adresDostawy'] = array();
    }

    $krajPrzedZmiana = $_SESSION['krajDostawy']['id'];
    $rodzajDostawyPrzedZmiana = $_SESSION['rodzajDostawy']['wysylka_klasa'];
    
    if ( isset($_SESSION['adresDostawy']['telefon']) ) {
         $telefonKlienta = $_SESSION['adresDostawy']['telefon'];
      } else {
         $telefonKlienta = '';
    }
    
    unset($_SESSION['adresDostawy']);
    
    $zapytanie = "SELECT c.customers_id, 
                         c.customers_telephone,
                         a.address_book_id, 
                         a.entry_company, 
                         a.entry_firstname, 
                         a.entry_lastname, 
                         a.entry_street_address, 
                         a.entry_postcode, 
                         a.entry_city, 
                         a.entry_country_id, 
                         a.entry_zone_id,
                         a.entry_telephone
                    FROM customers c 
               LEFT JOIN address_book a ON a.customers_id = c.customers_id
                   WHERE a.address_book_id = '" . (int)$_POST['value'] . "' AND c.customers_id = '".$_SESSION['customer_id']."' AND c.customers_guest_account = '0' AND c.customers_status = '1'";

    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
    
        $info = $sql->fetch_assoc();   

        $_SESSION['adresDostawy'] = array('imie' => $info['entry_firstname'],
                                          'nazwisko' => $info['entry_lastname'],
                                          'firma' => $info['entry_company'],
                                          'ulica' => $info['entry_street_address'],
                                          'kod_pocztowy' => $info['entry_postcode'],
                                          'miasto' => $info['entry_city'],
                                          'panstwo' => $info['entry_country_id'],
                                          'wojewodztwo' => $info['entry_zone_id'],
                                          'telefon' => ((!empty($info['entry_telephone'])) ? $info['entry_telephone'] : $info['customers_telephone'])
        );
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);    

    if ( $krajPrzedZmiana != $info['entry_country_id'] ) {

        $ResetWysylki = true;

        $zapytanie_panstwo = "SELECT c.countries_iso_code_2
                                FROM countries c
                                WHERE c.countries_id = '".$info['entry_country_id']."'";

        $sql_panstwo = $GLOBALS['db']->open_query($zapytanie_panstwo);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql_panstwo) > 0) { 
        
            $info_panstwo = $sql_panstwo->fetch_assoc();

            unset($_SESSION['krajDostawy'], $_SESSION['rodzajDostawy'], $_SESSION['rodzajPlatnosci']);

            $_SESSION['krajDostawy'] = array();
            $_SESSION['krajDostawy'] = array('id' => $info['entry_country_id'],
                                             'kod' => $info_panstwo['countries_iso_code_2']);
        
        }
        
        unset($zapytanie_panstwo);
        $GLOBALS['db']->close_query($sql_panstwo);

    }

    if ( ($rodzajDostawyPrzedZmiana == 'wysylka_inpost_international' || $rodzajDostawyPrzedZmiana == 'wysylka_inpost_weekend' || $rodzajDostawyPrzedZmiana == 'wysylka_inpost_eko' || $rodzajDostawyPrzedZmiana == 'wysylka_inpost') && ($telefonKlienta != $info['entry_telephone']) && !Funkcje::CzyNumerGSM($info['entry_telephone']) ) {

        $ResetWysylki = true;
        unset($_SESSION['rodzajDostawy'], $_SESSION['rodzajPlatnosci']);

    }

    if ( $ResetWysylki ) {
        
        echo '<div id="PopUpInfo" class="PopUpZmianaKrajuWysylki" aria-live="assertive" aria-atomic="true">';  

        echo $GLOBALS['tlumacz']['ZMIANA_KRAJU_WYSYLKI'];

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

    unset($info);

}
?>