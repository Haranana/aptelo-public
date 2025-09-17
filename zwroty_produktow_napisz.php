<?php

// plik
$WywolanyPlik = 'zwroty_produktow_napisz';

include('start.php');

if ( ZWROTY_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

// po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {

        if ( isset($_POST['zamowienie_id']) && $_POST['zamowienie_id'] != '' && isset($_POST['produkty']) && is_array($_POST['produkty']) && count($_POST['produkty']) > 0 && isset($_POST['id_produktow']) && $_POST['id_produktow'] == 'x' ) {

            $IdKlienta = 0;
            
            if ( isset($_SESSION['customer_id']) ) {
                 //
                 $IdKlienta = (int)$_SESSION['customer_id'];
                 //
            }
            
            $ZgodnoscDanych = false;

            if ( isset($_POST['hash']) && $_POST['hash'] != '' && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
              
                $zamowienie = new Zamowienie((int)$_POST["zamowienie_id"]);
                
                $emailKlienta = $zamowienie->klient['adres_email'];
                $telefonKlienta = $zamowienie->klient['telefon'];

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
                
                $emailKlienta = $zamowienie->klient['adres_email'];
                $telefonKlienta = $zamowienie->klient['telefon'];

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
                $Id_Zwrotu = Zwroty::UtworzIdZwrotu(13);
                
                $foto1 = '';
                
                if (isset($_FILES)) {
                    //
                    if (count($_FILES) > 0) {
                        //
                        if ( isset($_FILES['zdjecie_1']) ) {
                             $foto1 = Funkcje::WgrajPlik($_FILES['zdjecie_1'], 100, 'zwr-');
                        }                    
                        //
                    }
                    //
                }          

                $nazwaKlienta = '';
                $emailKlienta = '';
                
                $zapytanie_klient = "SELECT customers_id, customers_firstname, customers_lastname, customers_email_address FROM customers WHERE customers_id = '" . (int)$IdKlienta . "'";
                $sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
                
                if ((int)$GLOBALS['db']->ile_rekordow($sql_klient) > 0) {
                
                    $info_klient = $sql_klient->fetch_assoc();
                    
                    $nazwaKlienta = $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'];
                    $emailKlienta = $info_klient['customers_email_address'];
                    
                    unset($info_klient);

                }
                
                $GLOBALS['db']->close_query($sql_klient);
                unset($zapytanie_klient);                     

                // data ostatniej zmiany statusu
                $data_statusu = time();
                
                $zapytanie_status = "SELECT date_added FROM orders_status_history WHERE orders_id = '" . (int)$_POST["zamowienie_id"] . "' ORDER BY date_added DESC LIMIT 1";
                $sql_status = $GLOBALS['db']->open_query($zapytanie_status);    
                  
                if ( (int)$GLOBALS['db']->ile_rekordow($sql_status) > 0 ) {
                     //
                     $infs = $sql_status->fetch_assoc();
                     //
                     $data_statusu = FunkcjeWlasnePHP::my_strtotime($infs['date_added']);
                     //
                     unset($infs);
                     //
                }
                unset($zapytanie_status);  
                $GLOBALS['db']->close_query($sql_status);                  
                
                // suma zwrotu
                
                $produkty_suma = array();
                
                $zapytanie_suma = "SELECT orders_products_id, final_price_tax FROM orders_products WHERE orders_id = '" . (int)$_POST["zamowienie_id"] . "'";
                $sql_suma = $GLOBALS['db']->open_query($zapytanie_suma);                   
                
                if ( (int)$GLOBALS['db']->ile_rekordow($sql_suma) > 0 ) {
                     //
                     while ($infs = $sql_suma->fetch_assoc() ) {
                        //
                        $produkty_suma[$infs['orders_products_id']] = $infs['final_price_tax'];
                        //
                     }
                     //
                     unset($infs);
                     //
                }
                unset($zapytanie_suma);  
                $GLOBALS['db']->close_query($sql_suma);                   
                
                $suma_zwrotu = 0;

                foreach ( $_POST['produkty'] as $klucz => $produkt ) {                
                    //
                    if ( isset($produkty_suma[$klucz]) ) {
                         //
                         $ile = ((isset($_POST['ilosc_' . $klucz])) ? (float)$_POST['ilosc_' . $klucz] : 1);
                         $suma_zwrotu += ($ile * $produkty_suma[$klucz]);
                         //
                    }
                    //
                }   
                
                $pola = array(array('return_rand_id',$Id_Zwrotu),
                              array('return_customers_orders_id',(int)$_POST["zamowienie_id"]),
                              array('return_customers_orders_date_purchased',((isset($_POST["data_zamowienia"])) ? $filtr->process($_POST["data_zamowienia"]) : 'NULL')),
                              array('return_customers_id',(int)$IdKlienta),
                              array('return_customers_telephone',((isset($_POST["telefon"])) ? $filtr->process($_POST["telefon"]) : '')),
                              array('return_customers_invoice_number',((isset($_POST["nr_dokument_sprzedazy"])) ? $filtr->process($_POST["nr_dokument_sprzedazy"]) : '')),
                              array('return_customers_bank',((isset($_POST["nr_bank"])) ? $filtr->process($_POST["nr_bank"]) : '')),
                              array('return_value',$suma_zwrotu),
                              array('return_status_id',Zwroty::domyslnyStatusZwrotu()),
                              array('return_date_created','now()'),
                              array('return_date_modified','now()'),
                              array('return_date_end',date("Y-m-d H:i:s", time() + (REKLAMACJA_CZAS_ROZPATRZENIA * 86400))),
                              array('return_image_1',$foto1));                

                $GLOBALS['db']->insert_query('return_list' , $pola);
                $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();

                unset($pola, $suma_zwrotu, $produkty_suma);
                  
                foreach ( $_POST['produkty'] as $klucz => $produkt ) {
                 
                    $pola = array(
                            array('return_id',$id_dodanej_pozycji),
                            array('return_products_orders_id',(int)$produkt),
                            array('return_products_shop_id',((isset($_POST['id_produktu_sklep_' . $klucz])) ? (int)$_POST['id_produktu_sklep_' . $klucz] : '')),
                            array('return_products_quantity',((isset($_POST['ilosc_' . $klucz])) ? (float)$_POST['ilosc_' . $klucz] : '')),
                            array('return_products_notes',((isset($_POST['powod_' . $klucz])) ? $filtr->process($_POST['powod_' . $klucz]) : '')));
        
                    $GLOBALS['db']->insert_query('return_products' , $pola);	
                    unset($pola);   
                
                }       

                // Generowanie szybkiego zwrotu Inpost - START
                $KodNadania = '';

                if ( INTEGRACJA_INPOST_ZWROTY_WLACZONY == 'tak' && isset($_POST['ZwrotPaczkomat']) ) {

                    $email    = INTEGRACJA_INPOST_ZWROTY_LOGIN;
                    $password = INTEGRACJA_INPOST_ZWROTY_LOGIN_HASLO;
                    $expire   = INTEGRACJA_INPOST_ZWROTY_EXPIRATIONDATE;
                    $content  = '';
                    $DataEnd = time() + (3600*24*$expire);
                    $expirationDate = date_format(date_create('@'.$DataEnd), 'c') . "\n";

                    $NumerTelefonu = ((isset($_POST["telefon"])) ? $filtr->process($_POST["telefon"]) : $telefonKlienta);
                    $NumerTelefonu = preg_replace("/\([0-9]+?\)/", "", (string)$NumerTelefonu);
                    $NumerTelefonu = preg_replace("/[^0-9]/", "", (string)$NumerTelefonu);
                    $NumerTelefonu = substr((string)$NumerTelefonu , -9);

                    $api_url = 'https://'.(INTEGRACJA_INPOST_ZWROTY_SANDBOX == 'tak' ? 'sandbox-api.paczkomaty.pl' : 'api.paczkomaty.pl' ).'/?do=revloggenerateactivecode';
                        
                    $headers = [
                                'Content-Type: application/x-www-form-urlencoded',
                                'accept: application/json'
                               ];

                    $content .= '
                    <paczkomaty>
                    <rma>'.(int)$_POST["zamowienie_id"].'</rma>
                    <packType>'.$_POST['packType'].'</packType>
                    <expirationDate>'.$expirationDate.'</expirationDate>
                    <senderPhone>'.$NumerTelefonu.'</senderPhone>
                    <senderEmail>'.$emailKlienta.'</senderEmail>
                    <returnDescription1>'.INTEGRACJA_INPOST_ZWROTY_OPIS.': '.(int)$_POST["zamowienie_id"].'</returnDescription1>
                    </paczkomaty>
                    ';

                    $Data = array(
                                   "email" => $email,
                                   "password" => $password,
                                   "content" => $content
                    );

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                    curl_setopt($ch, CURLOPT_URL, $api_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($Data));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                    $WynikXML = curl_exec($ch);
                    curl_close($ch);

                    $WynikXML = simplexml_load_string($WynikXML);
                    $WynikJSON = json_encode($WynikXML);
                    $Wynik = json_decode($WynikJSON,TRUE);

                    if ( isset($Wynik['error']) && $Wynik['error'] != '' ) {
                        //$_POST["wiadomosc"] = $_POST["wiadomosc"] . "\n" . $Wynik['error'];
                    } else {
                        $_POST["wiadomosc"] = $_POST["wiadomosc"] . "\n" .$GLOBALS['tlumacz']['ZWROT_ODESLANIE_KOD']. ": " . $Wynik['return']['code'];
                    }

                    unset($email, $password, $expire, $content, $DataEnd, $expirationDat, $NumerTelefonu, $WynikXML, $WynikJSON, $Wynik);

                }

                // Generowanie szybkiego zwrotu Inpost - KONIEC

                $pola = array(
                        array('return_id',(int)$id_dodanej_pozycji),
                        array('return_status_id',Zwroty::domyslnyStatusZwrotu()),
                        array('date_added','now()'),
                        array('comments',((isset($_POST["wiadomosc"])) ? $filtr->process($_POST["wiadomosc"]) : '')));

                $GLOBALS['db']->insert_query('return_status_history' , $pola);
                unset($pola);

                // wyslanie maila do klienta - START
                $jezyk_maila = (int)$_SESSION['domyslnyJezyk']['id'];

                $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_ZWROT_ZGLOSZENIE'";
                $sql = $GLOBALS['db']->open_query($zapytanie_tresc);
                
                if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                    //
                    $tresc = $sql->fetch_assoc();        
                    //
                }
                
                $hashKod = '';
                
                if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                     //
                     $zapytanieZwrot = "SELECT * FROM return_list where return_rand_id = '" . $Id_Zwrotu . "'";
                     $sqlZwrot = $GLOBALS['db']->open_query($zapytanieZwrot);  
                     
                     if ((int)$GLOBALS['db']->ile_rekordow($sqlZwrot) > 0) {

                        $DaneZwrotu = $sqlZwrot->fetch_assoc();
                        
                        $hashKod = '/zwrot=' . hash("sha1", $DaneZwrotu['return_rand_id'] . ';' . $DaneZwrotu['return_date_created'] . ';' . $DaneZwrotu['return_customers_id'] . ';' . $DaneZwrotu['return_customers_orders_id']);
                        
                        unset($DaneZwrotu);
                        
                     }
    
                     $GLOBALS['db']->close_query($sqlZwrot); 
                     unset($zapytanieZwrot); 
 
                }            

                define('LINK', ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU )."/zwroty-produktow-szczegoly-zp-" . $Id_Zwrotu . ".html" . $hashKod);  
                define('BIEZACA_DATA', date("d-m-Y H:i:s"));  
                define('KLIENT_IP', ((isset($_POST["adres_ip"])) ? $filtr->process($_POST["adres_ip"]) : ''));  
                define('KLIENT', $nazwaKlienta);  
                define('NUMER_ZAMOWIENIA', (int)$_POST["zamowienie_id"]);  
                define('NUMER_ZWROTU', $Id_Zwrotu);  

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

                Funkcje::PrzekierowanieURL('zwroty-produktow-napisz-sukces.html' . ((isset($_POST['hash']) && $_POST['hash'] != '' && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak') ? '/id=' . (int)$_POST["zamowienie_id"] . '/zamowienie=' . $filtr->process($_POST['hash']) : ''));
                
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
    $IloscDni = round((time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])) / 86400, 0);
    
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
                                     
    $nr_zamowien = array();                                 

    $zapytanie_zamowienia = "SELECT orders_id, date_purchased FROM orders WHERE customers_id = '" . $IdKlienta . "' " . (($AktywnyHash == true && isset($_GET['id']) && (int)$_GET['id'] > 0) ? " AND orders_id = '" . (int)$_GET['id'] . "'" : "") . " ORDER BY orders_id DESC";
    $sql_zamowienia = $GLOBALS['db']->open_query($zapytanie_zamowienia);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql_zamowienia) > 0) {
        //
        while ( $info_zamowienia = $sql_zamowienia->fetch_assoc() ) {
          
            // data ostatniej zmiany statusu
          
            $data_statusu = FunkcjeWlasnePHP::my_strtotime('1990-12-31 23:59:59');
            
            $zapytanie_status = "SELECT date_added FROM orders_status_history WHERE orders_id = '" . (int)$info_zamowienia['orders_id'] . "' ORDER BY date_added DESC LIMIT 1";
            $sql_status = $GLOBALS['db']->open_query($zapytanie_status);    
              
            if ( (int)$GLOBALS['db']->ile_rekordow($sql_status) > 0 ) {
                 //
                 $infs = $sql_status->fetch_assoc();
                 //
                 $data_statusu = FunkcjeWlasnePHP::my_strtotime($infs['date_added']);
                 //
                 unset($infs);
                 //
            }
            unset($zapytanie_status);  
            $GLOBALS['db']->close_query($sql_status);           
          
            if ( $data_statusu + (ZWROTY_ILE_DNI * 86400) >= time() ) {   
              
                 // sprawdzi czy nie bylo zwrotu
                 $zapytanie_suma = "SELECT * FROM return_list WHERE return_customers_orders_id = '" . (int)$info_zamowienia['orders_id'] . "'";
                 $sql_suma = $GLOBALS['db']->open_query($zapytanie_suma);
             
                 if ( (int)$GLOBALS['db']->ile_rekordow($sql_suma) == 0 ) {                
          
                      $tablica['zamowienia'][] = array('id' => $info_zamowienia['orders_id'],
                                                       'text' => $GLOBALS['tlumacz']['KLIENT_NUMER_ZAMOWIENIA'] . ': ' . $info_zamowienia['orders_id'] . '; ' . $GLOBALS['tlumacz']['DATA_ZAMOWIENIA'] . ': ' . date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info_zamowienia['date_purchased'])));
                                                       
                      $nr_zamowien[] = $info_zamowienia['orders_id'];
                                                       
                 }
                 
                 $GLOBALS['db']->close_query($sql_suma);   
                 unset($zapytanie_suma);
                  
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
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZGLOSZENIE_ZWROTU']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), count($tablica['zamowienia']), $nr_zamowien);

    //parametry do podstawienia
    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
    $srodek->dodaj('__ID_KLIENTA', $tablica['id']);
    $srodek->dodaj('__IMIE_KLIENTA', $tablica['imie']);
    $srodek->dodaj('__NAZWISKO_KLIENTA', $tablica['nazwisko']);
    $srodek->dodaj('__EMAIL_KLIENTA', $tablica['email']);
    $srodek->dodaj('__ZAMOWIENIA_KLIENTA', Funkcje::RozwijaneMenu('zamowienie_id', $tablica['zamowienia'], ((isset($_GET['id']) && (int)$_GET['id'] > 0) ? (int)$_GET['id'] : ''), 'class="required" style="width:70%" id="zamowienie_zwrot" ') . '<script>$(document).ready(function(){PobierzDaneZwrotu()})</script>');
    
    $srodek->dodaj('__TOKEN',Sesje::Token());
    
    $srodek->dodaj('__HASH',$hashKod);

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $tablica, $hashKod);

    include('koniec.php');
    
} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}
?>