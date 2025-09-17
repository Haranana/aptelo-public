<?php

// plik
$WywolanyPlik = 'reklamacje_napisz';

include('start.php');

if ( REKLAMACJE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

// po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {

        if ( isset($_POST['zamowienie_id']) && $_POST['zamowienie_id'] != '' && isset($_POST['temat']) && $_POST['temat'] != '' && isset($_POST['wiadomosc']) && $_POST['wiadomosc'] != '' ) {

            $IdKlienta = 0;
            
            if ( isset($_SESSION['customer_id']) ) {
                 //
                 $IdKlienta = (int)$_SESSION['customer_id'];
                 //
            }
            
            $ZgodnoscDanych = false;

            if ( isset($_POST['hash']) && $_POST['hash'] != '' && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
              
                $zamowienie = new Zamowienie((int)$_POST["zamowienie_id"]);
                
                $hashKod = '';
                
                if ( count($zamowienie->info) > 0 ) {
                
                    $hashKod = hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);

                    if ( $_POST['hash'] === $hashKod ) {
                         //
                         $IdKlienta = (int)$zamowienie->klient['id'];
                         //
                         $ZgodnoscDanych = true;
                         //
                    }
                    
                }

                unset($zamowienie);
              
            } else {

                // sprawdzi czy nr zamowienia zgadza sie z id klienta
                $zamowienie = new Zamowienie((int)$_POST["zamowienie_id"]);
                
                if ( count($zamowienie->info) > 0 ) {
                
                    if ( isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == $zamowienie->klient['id'] ) {
                         //
                         $ZgodnoscDanych = true;
                         //  
                    }     

                }                    
                
                unset($zamowienie);

            }              
          
            if ( $ZgodnoscDanych == false ) {
                 
                 Funkcje::PrzekierowanieURL('brak-strony.html');
          
            } else {

                // zapisanie danych klienta do bazy - START
                $Id_Reklamacji = Reklamacje::UtworzIdReklamacji(15);
                
                $foto1 = '';
                $foto2 = '';
                $foto3 = '';
                
                if (isset($_FILES)) {
                    //
                    if (count($_FILES) > 0) {
                        //
                        if ( isset($_FILES['zdjecie_1']) ) {
                             $foto1 = Funkcje::WgrajPlik($_FILES['zdjecie_1'], 100, 'rkl-');
                        }
                        if ( isset($_FILES['zdjecie_2']) ) {
                             $foto2 = Funkcje::WgrajPlik($_FILES['zdjecie_2'], 100, 'rkl-');
                        }
                        if ( isset($_FILES['zdjecie_3']) ) {
                             $foto3 = Funkcje::WgrajPlik($_FILES['zdjecie_3'], 100, 'rkl-');
                        }                        
                        //
                    }
                    //
                }            

                $nazwaKlienta = '';
                $emailKlienta = '';
                $telefonKlienta = '';
                
                $zapytanie_klient = "SELECT customers_id, customers_firstname, customers_lastname, customers_email_address, customers_telephone FROM customers WHERE customers_id = '" . (int)$IdKlienta . "'";
                $sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
                
                if ((int)$GLOBALS['db']->ile_rekordow($sql_klient) > 0) {
                
                    $info_klient = $sql_klient->fetch_assoc();
                    
                    $nazwaKlienta = $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'];
                    $emailKlienta = $info_klient['customers_email_address'];
                    $telefonKlienta = $info_klient['customers_telephone'];

                }
                
                $GLOBALS['db']->close_query($sql_klient);
                unset($zapytanie_klient);                

                $pola = array(array('complaints_rand_id',$Id_Reklamacji),
                              array('complaints_customers_orders_id',(int)$_POST["zamowienie_id"]),
                              array('complaints_subject',$filtr->process($_POST["temat"])),
                              array('complaints_date_created','now()'),
                              array('complaints_date_modified','now()'),
                              array('complaints_date_end',date("Y-m-d H:i:s", time() + (REKLAMACJA_CZAS_ROZPATRZENIA * 86400))),
                              array('complaints_service','0'),
                              array('complaints_status_id',Reklamacje::domyslnyStatusReklamacji()),
                              array('complaints_customers_id',(int)$IdKlienta),
                              array('complaints_customers_name',$nazwaKlienta),
                              array('complaints_customers_email',$emailKlienta),
                              array('complaints_customers_telephone',$telefonKlienta),
                              array('complaints_image_1',$foto1),
                              array('complaints_image_2',$foto2),
                              array('complaints_image_3',$foto3));

                $GLOBALS['db']->insert_query('complaints' , $pola);
                $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();

                unset($pola);
                    
                $pola = array(
                        array('complaints_id',(int)$id_dodanej_pozycji),
                        array('complaints_status_id',Reklamacje::domyslnyStatusReklamacji()),
                        array('date_added','now()'),
                        array('comments',$filtr->process($_POST["wiadomosc"])));

                $GLOBALS['db']->insert_query('complaints_status_history' , $pola);
                unset($pola);

                // wyslanie maila do klienta - START
                $jezyk_maila = (int)$_SESSION['domyslnyJezyk']['id'];

                $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_REKLAMACJA_ZGLOSZENIE'";
                $sql = $GLOBALS['db']->open_query($zapytanie_tresc);
                
                if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                    //
                    $tresc = $sql->fetch_assoc();        
                    //
                }
                
                $hashKod = '';
                
                if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                     //
                     $zapytanieReklamacja = "SELECT * FROM complaints where complaints_rand_id = '" . $Id_Reklamacji . "'";
                     $sqlReklamacja = $GLOBALS['db']->open_query($zapytanieReklamacja);  
                     
                     if ((int)$GLOBALS['db']->ile_rekordow($sqlReklamacja) > 0) {

                        $DaneReklamacji = $sqlReklamacja->fetch_assoc();
                        
                        $hashKod = '/reklamacja=' . hash("sha1", $DaneReklamacji['complaints_rand_id'] . ';' . $DaneReklamacji['complaints_date_created'] . ';' . $DaneReklamacji['complaints_customers_email'] . ';' . $DaneReklamacji['complaints_customers_id'] . ';' . $DaneReklamacji['complaints_customers_orders_id']);
                        
                     }
    
                     $GLOBALS['db']->close_query($sqlReklamacja); 
                     unset($zapytanieReklamacja); 
 
                }            

                define('LINK', ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU )."/reklamacje-szczegoly-rs-" . $Id_Reklamacji . ".html" . $hashKod);  
                define('BIEZACA_DATA', date("d-m-Y H:i:s"));  
                define('KLIENT_IP', ((isset($_POST["adres_ip"])) ? $filtr->process($_POST["adres_ip"]) : ''));  
                define('KLIENT', $nazwaKlienta);  
                define('NUMER_ZAMOWIENIA', (int)$_POST["zamowienie_id"]);  
                define('NUMER_REKLAMACJI', $Id_Reklamacji);  
                define('TYTUL_REKLAMACJI', $filtr->process($_POST["temat"]));
                define('OPIS_REKLAMACJI', $filtr->process($_POST["wiadomosc"]));

                $email = new Mailing;

                if ( $tresc['email_file'] != '' ) {
                    $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
                } else {
                    $tablicaZalacznikow = array();
                }

                $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
                $cc              = Funkcje::parsujZmienne($tresc['dw']);

                $adresat_email   = $emailKlienta;
                $adresat_nazwa   = $nazwaKlienta;

                $temat           = Funkcje::parsujZmienne($tresc['email_title']);
                $tekst           = $tresc['description'];
                $zalaczniki      = $tablicaZalacznikow;
                $szablon         = $tresc['template_id'];
                $jezyk           = (int)$jezyk_maila;

                $tekst = Funkcje::parsujZmienne($tekst);
                $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);

                if ( $emailKlienta != '' ) {
                     //
                     $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
                     //
                }

                $GLOBALS['db']->close_query($sql);
                unset($tresc, $zapytanie_tresc);             

                Funkcje::PrzekierowanieURL('reklamacje-napisz-sukces.html' . ((isset($_POST['hash']) && $_POST['hash'] != '' && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak') ? '/id=' . (int)$_POST["zamowienie_id"] . '/zamowienie=' . $filtr->process($_POST['hash']) : ''));
                
            }

        }

    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html');
        
    }        
        
}

$AktywnyHash = false;
$IdKlienta = 0;
if ( isset($_SESSION['customer_id']) ) {
     $IdKlienta = (int)$_SESSION['customer_id'];
}
$hashKod = '';

if ( isset($_GET['zamowienie']) && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' && isset($_GET['id']) && (int)$_GET['id'] > 0) {
  
    $zamowienie = new Zamowienie((int)$_GET['id']);
    
    if ( count($zamowienie->info) == 0 ) {
         //
         Funkcje::PrzekierowanieURL('brak-strony.html');
         //
    }
    
    // ilosc dni od zlozenia zamowienia
    $IloscDni = round(((time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])) / 86400), 0);
    
    if ( $IloscDni <= STR_PANELU_KLIENTA_BEZ_LOGOWANIA_WAZNOSC ) {    
    
        $hashKod = hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
        
        if ( $_GET['zamowienie'] === $hashKod ) {
             $AktywnyHash = true;
             //
             $IdKlienta = (int)$zamowienie->klient['id'];
        }
        
    } else {
      
        $_SESSION['bladDniToken'] = true;
        Funkcje::PrzekierowanieSSL( 'logowanie.html' );
      
    }        

    unset($zamowienie, $IloscDni);
  
}

if ((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0') || $AktywnyHash == true) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

    $tablica = array();
    
    $zapytanie_klient = "SELECT customers_id, customers_firstname, customers_lastname, customers_email_address, customers_telephone FROM customers WHERE customers_id = '" . (int)$IdKlienta . "'";
    $sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql_klient) > 0) {
    
        $info_klient = $sql_klient->fetch_assoc();

        $tablica = array('id' => $info_klient['customers_id'],
                         'imie' => $info_klient['customers_firstname'],
                         'nazwisko' => $info_klient['customers_lastname'],
                         'email' => $info_klient['customers_email_address'],
                         'telefon' => $info_klient['customers_telephone']);
                         
        unset($info_klient);

    } else {
      
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
      
    }
                         
    $GLOBALS['db']->close_query($sql_klient);
    unset($zapytanie_klient);
        
    $tablica['zamowienia'][] = array('id' => '',
                                     'text' => $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE']);

    $zapytanie_zamowienia = "SELECT orders_id, date_purchased FROM orders WHERE customers_id = '" . $IdKlienta . "' " . (($AktywnyHash == true && isset($_GET['id']) && (int)$_GET['id'] > 0) ? " AND orders_id = '" . (int)$_GET['id'] . "'" : "") . " ORDER BY orders_id DESC";
    $sql_zamowienia = $GLOBALS['db']->open_query($zapytanie_zamowienia);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql_zamowienia) > 0) {
        //
        while ( $info_zamowienia = $sql_zamowienia->fetch_assoc() ) {
          
            if ( (FunkcjeWlasnePHP::my_strtotime($info_zamowienia['date_purchased']) + ((int)REKLAMACJE_ILE_DNI * 86400)) >= time() ) {
              
                // sprawdzi ile bylo reklamacji
                $zapytanie_reklamacja = "SELECT * FROM complaints where complaints_customers_orders_id = '" . $info_zamowienia['orders_id'] . "'";
                $sql_reklamacja = $db->open_query($zapytanie_reklamacja);  
                
                if ((int)$GLOBALS['db']->ile_rekordow($sql_reklamacja) < (int)REKLAMACJE_ILOSC_ZGLOSZEN) {                
          
                    $tablica['zamowienia'][] = array('id' => $info_zamowienia['orders_id'],
                                                     'text' => $GLOBALS['tlumacz']['KLIENT_NUMER_ZAMOWIENIA'] . ': ' . $info_zamowienia['orders_id'] . '; ' . $GLOBALS['tlumacz']['DATA_ZAMOWIENIA'] . ': ' . date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info_zamowienia['date_purchased'])));
                                                     
                }
                
                $GLOBALS['db']->close_query($sql_reklamacja);
                unset($zapytanie_reklamacja);                
                  
            }
            
        }
        //
    }
    
    $GLOBALS['db']->close_query($sql_zamowienia);   

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZGLOSZENIE_REKLAMACJI']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), count($tablica['zamowienia']));

    //parametry do podstawienia
    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
    $srodek->dodaj('__ID_KLIENTA', $tablica['id']);
    $srodek->dodaj('__IMIE_KLIENTA', $tablica['imie']);
    $srodek->dodaj('__NAZWISKO_KLIENTA', $tablica['nazwisko']);
    $srodek->dodaj('__EMAIL_KLIENTA', $tablica['email']);
    $srodek->dodaj('__ZAMOWIENIA_KLIENTA', Funkcje::RozwijaneMenu('zamowienie_id', $tablica['zamowienia'], ((isset($_GET['id']) && (int)$_GET['id'] > 0) ? (int)$_GET['id'] : ''), 'class="required" id="lista_zamowien" style="width:70%"'));
    
    $srodek->dodaj('__TOKEN',Sesje::Token());
    
    $srodek->dodaj('__HASH',$hashKod);

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $tablica, $hashKod);

    include('koniec.php');
    
} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}
?>