<?php

// plik
$WywolanyPlik = 'zwroty_produktow_szczegoly';

include('start.php');

if ( ZWROTY_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$AktywnyHash = false;
$hashKod = '';

$NrZwrotu = '';

if ( isset($_GET['id']) && $_GET['id'] != '' ) {

    $IdGetZwrotu = $filtr->process($_GET['id']);
    
    $TablicaZwrotow = array();
    //
    $zapytanie = "SELECT * FROM return_list";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {
            //
            $TablicaZwrotow[] = array('id' => $info['return_id'],
                                         'nr_zwrotu' => $info['return_rand_id']);
            //
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);         

    foreach ( $TablicaZwrotow as $Zwrot ) {  
        //
        if ( $Zwrot['nr_zwrotu'] == $IdGetZwrotu ) {
             //
             $NrZwrotu = $Zwrot['nr_zwrotu'];
             //
        }
        //
    }
    
    unset($IdGetZwrotu, $TablicaZwrotow);
    
}

if ( $NrZwrotu == '' ) {
     //
     Funkcje::PrzekierowanieURL('brak-strony.html'); 
     //
} else {
     //
     $_GET['id'] = $NrZwrotu;
     //
}

if ( isset($_GET['zwrot']) && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' && isset($_GET['id']) && $_GET['id'] != '' ) {

    $hashKod = '';
    //
    $zapytanieZwrot = "SELECT * FROM return_list where return_rand_id = '" . $filtr->process($_GET['id']) . "'";
    $sqlZwrot = $GLOBALS['db']->open_query($zapytanieZwrot);  
    
    if ((int)$GLOBALS['db']->ile_rekordow($sqlZwrot) > 0) {

        $DaneZwrotu = $sqlZwrot->fetch_assoc();
    
        $hashKod = hash("sha1", $DaneZwrotu['return_rand_id'] . ';' . $DaneZwrotu['return_date_created'] . ';' . $DaneZwrotu['return_customers_id'] . ';' . $DaneZwrotu['return_customers_orders_id']);
        
        unset($DaneZwrotu);
        
    }

    $GLOBALS['db']->close_query($sqlZwrot); 
    unset($zapytanieZwrot); 
    
    if ( $_GET['zwrot'] === $hashKod && $hashKod != '' ) {
         $AktywnyHash = true;
    }

}  

if ( ((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') || $AktywnyHash == true) && isset($_GET['id']) && $_GET['id'] != '' ) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

    $zapytanie = "SELECT * FROM return_list rl
               LEFT JOIN customers c ON rl.return_customers_id = c.customers_id
                   WHERE rl.return_rand_id = '" . $filtr->process($_GET['id']) . "'";

    $sql = $GLOBALS['db']->open_query($zapytanie);
  
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
      $info = $sql->fetch_assoc();

      // jezeli jest przeslanie informacji od klienta
      if ( isset($_POST['info_klienta']) && $_POST['info_klienta'] == 'tak' && isset($_POST['id']) && (int)$_POST['id'] > 0 && isset($_POST['aktualny_status']) && (int)$_POST['aktualny_status'] > 0 ) {
        
          // sprawdzi ile bylo komentarzy
          $zapytanie_komentarze = "SELECT return_customers_info FROM return_status_history WHERE return_id = '" . (int)$info['return_id'] . "' AND return_customers_info = '1'";
          $sql_komentarze = $GLOBALS['db']->open_query($zapytanie_komentarze);

          if ((int)$GLOBALS['db']->ile_rekordow($sql_komentarze) < (int)ZWROTY_ILOSC_KOMENTARZY) {          
        
              if ( !empty($_POST['komentarz']) ) {

                    $pola = array(
                            array('return_id',(int)$info['return_id']),
                            array('return_status_id',(int)$info['return_status_id']),
                            array('date_added','now()'),
                            array('customer_notified','0'),
                            array('comments',$filtr->process($_POST['komentarz'])),
                            array('return_customers_info','1'),
                            array('return_customers_info_view','0'));

                    $GLOBALS['db']->insert_query('return_status_history' , $pola);
                    unset($pola);        
                                
                    // wysylanie maila do administratora sklepu
                    $nadawca_email   = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                    $nadawca_nazwa   = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);
                    $odpowiedz_email = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                    $odpowiedz_nazwa = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);

                    $adres_email     = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                    
                    $adresat_nazwa   = $filtr->process(INFO_NAZWA_SKLEPU);

                    $temat           = $GLOBALS['tlumacz']['TEMAT_ZWROTU_MAIL'] . ' ' . $filtr->process($_GET['id']);

                    $zalaczniki      = Array();
                    $szablon         = 1;
                    $jezyk           = (int)$_SESSION['domyslnyJezyk']['id'];

                    $tekst = $filtr->process($_POST['komentarz']);

                    $email = new Mailing;
                    $email->wyslijEmail($nadawca_email, $nadawca_nazwa ,$adres_email, $adresat_nazwa, '', $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email, $odpowiedz_nazwa);
                    unset($email);        
          
              }
          
              Funkcje::PrzekierowanieSSL( 'zwroty-produktow-szczegoly-zp-' . $filtr->process($_GET['id']) . '.html' . ((isset($_GET['zwrot']) && $_GET['zwrot'] != '' && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak') ? '/zwrot=' . $filtr->process($_GET['zwrot']) : '') );
              
          } else {
            
              Funkcje::PrzekierowanieURL('brak-strony.html'); 
              
          }
          
          $GLOBALS['db']->close_query($sql_komentarze);
          unset($zapytanie_komentarze);                        
        
      }
      
      $tablica = array();

      $id_zwrotu = $info['return_id'];

      // pobieranie informacji od uzytkownikach
      if ($info['return_service'] > 0) {

          $zapytanie_uzytkownicy = "SELECT * FROM admin WHERE admin_id = '" . $info['return_service'] . "'";
          $sql_uzytkownicy = $GLOBALS['db']->open_query($zapytanie_uzytkownicy);
          $uzytkownicy = $sql_uzytkownicy->fetch_assoc();
          $obsluga = $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'];
          $GLOBALS['db']->close_query($sql_uzytkownicy); 
          unset($zapytanie_uzytkownicy, $uzytkownicy);
        
      } else {
        
          $obsluga = '-';
        
      }
      
      // produkty do zwrotu
      $produkty = array();
      //
      $zamowienie = new Zamowienie((int)$info['return_customers_orders_id']);
      //
      if ( count($zamowienie->info) > 0 ) {
           //
           $zapytanie_produkty = "SELECT return_products_quantity, return_products_orders_id FROM return_products WHERE return_id = '" . $info['return_id'] . "'";
           $sql_produkty = $GLOBALS['db']->open_query($zapytanie_produkty);    
          
           if ( (int)$GLOBALS['db']->ile_rekordow($sql_produkty) > 0) {
                //
                while ($infs = $sql_produkty->fetch_assoc()) {
                     //
                     $ilosc = $infs['return_products_quantity'];
                     $nazwa_produktu = '<b>Brak nazwy</b>';
                     $link_produktu = '';
                     $cechy_nazwa = '';
                     $id_produktu = 0;                     
                     //
                     // sprawdzi czy calkowita wartosc
                     foreach ( $zamowienie->produkty as $id_klucz => $prod ) {
                         //
                         if ( $id_klucz == $infs['return_products_orders_id'] ) {
                              //
                              if ( $prod['wartosc_calkowita'] == true ) {
                                   //
                                   $ilosc = (int)$infs['return_products_quantity'];
                                   //
                              }
                              //
                              $nazwa_produktu = $prod['nazwa'];
                              //
                              $cechy = array();
                              //
                              if ( isset($prod['attributes']) && (count($prod['attributes']) > 0) ) {  
                                   //
                                   foreach ($prod['attributes'] as $cecha ) {
                                       $cechy[] = $prod['attributes'][$cecha['id_cechy']]['cecha'] . ': ' . $prod['attributes'][$cecha['id_cechy']]['wartosc'];
                                   }
                                   //
                              }      
                              //
                              if ( count($cechy) > 0 ) {
                                   //
                                   $cechy_nazwa = ' (' . implode(', ', $cechy) . ')';
                                   //
                              }
                              //
                              $id_produktu = (int)$prod['id_produktu'];  
                              $link_produktu = $prod['adres_url']; 
                              //
                         }
                         //
                     }
                     //                  
                     if ( $id_produktu > 0 && $link_produktu != '' ) {
                          $produkty[] = '<li>' . $ilosc . ' x <a href="' . $link_produktu . '" title="' . str_replace('"', '', (string)$nazwa_produktu) . '">' . $nazwa_produktu . $cechy_nazwa . '</a></li>';
                     } else {
                          $produkty[] = '<li>' . $ilosc . ' x ' . $nazwa_produktu . $cechy_nazwa . '</li>';
                     }                                                 
                     //
                     unset($ilosc, $nazwa_produktu, $id_produktu, $cechy_nazwa);
                     //
                }
                //
                unset($infs);
                //
           }
          
           unset($zapytanie_produkty, $zapytanie);  
           $GLOBALS['db']->close_query($sql_produkty);  
           
      }
      
      unset($zamowienie);

      $tablica = array('id' => $filtr->process($_GET['id']),
                       'id_zwrotu' => $info['return_id'],
                       'data_zgloszenia' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['return_date_created'])),
                       'data_modyfikacji' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['return_date_modified'])),
                       'numer_zamowienia' => $info['return_customers_orders_id'],
                       'opiekun' => $obsluga,
                       'aktualny_status_id' => $info['return_status_id'],
                       'aktualny_status' => Zwroty::pokazNazweStatusuZwrotu($info['return_status_id'],(int)$_SESSION['domyslnyJezyk']['id']));
                       
      // zdjecia zwrotu
      
      $zdjecia_zwrotu = array();

      if ( !empty($info['return_image_1']) ) {

          if ( file_exists('grafiki_inne/' . $info['return_image_1']) ) {
               
               $zdjecia_zwrotu[] = '<a style="margin:5px 10px 5px 0px" class="ZdjeciaReklamacji" href="grafiki_inne/' . $info['return_image_1'] . '"><img style="max-width:150px;max-height:100px;width:auto;height:auto" src="grafiki_inne/' . $info['return_image_1'] . '" alt="" /></a>';
               
          }
          
      }
      
      $zdjecia_zwrotu_wynik = '';
      
      if ( count($zdjecia_zwrotu) > 0 ) {
           //
           $zdjecia_zwrotu_wynik = '<div class="ZdjeciaReklamacjiKlienta" style="display:flex;flex-wrap:wrap;margin-top:15px">' . implode('', $zdjecia_zwrotu) . '</div>';
           //
      }
                       
      $GLOBALS['db']->close_query($sql);
      unset($zapytanie, $info, $zdjecia_zwrotu);
      
      $tablica_statusow = array();
      
      $info_klienta = 0;

      $zapytanie_statusy = "SELECT rsh.return_status_id, rsh.date_added, rsh.customer_notified, rsh.comments, rsh.return_customers_info, rs.return_status_type
                              FROM return_status_history rsh, return_status rs WHERE rsh.return_id = '" . (int)$id_zwrotu . "' AND rsh.return_status_id = rs.return_status_id ORDER BY rsh.date_added";
                              
      $sql_statusy = $GLOBALS['db']->open_query($zapytanie_statusy);

      if ((int)$GLOBALS['db']->ile_rekordow($sql_statusy) > 0) {
        
          $j = 0;
        
          while ($info_statusy = $sql_statusy->fetch_assoc()) {
            
              $tablica_statusow[] = array('id_statusu' => $info_statusy['return_status_id'],
                                          'klient_powiadomiony' => $info_statusy['customer_notified'],
                                          'data_dodania' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info_statusy['date_added'])),
                                          'komentarz' => '<div class="FormatEdytor">' . $info_statusy['comments'] . '</div>',
                                          'informacja_od_klienta' => (($info_statusy['return_customers_info'] == '1') ? 'tak' : 'nie'),
                                          'status_zamkniety' => (($info_statusy['return_status_type'] == 3 || $info_statusy['return_status_type'] == 4) ? 'tak' : 'nie'),
                                          'status' => Zwroty::pokazNazweStatusuZwrotu($info_statusy['return_status_id'],(int)$_SESSION['domyslnyJezyk']['id']),
                                          'zdjecia_zwrotu' => (($j == 0) ? $zdjecia_zwrotu_wynik : ''));
                                          
              $j++;
              
              if ( $info_statusy['return_customers_info'] == '1') {
                   //
                   $info_klienta++;
                   //
              }              
                                      
          }
        
      }
      
      $GLOBALS['db']->close_query($sql_statusy);
      unset($zapytanie_statusy, $info_statusy);

      // meta tagi
      $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
      $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
      $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
      $tpl->dodaj('__META_OPIS', $Meta['opis']);
      unset($Meta);

      // breadcrumb
      $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
      $nawigacja->dodaj($GLOBALS['tlumacz']['PRZEGLADAJ_ZWROTY'],Seo::link_SEO('zwroty_produktow_przegladaj.php', '', 'inna'));
      $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZGLOSZENIE_ZWROTU']);
      $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

      // wyglad srodkowy
      $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $tablica, $tablica_statusow, (($info_klienta < (int)ZWROTY_ILOSC_KOMENTARZY) ? 'tak' : 'nie'));

      //parametry do podstawienia
      $srodek->dodaj('__ID_ZGLOSZENIA', $tablica['id']);
      $srodek->dodaj('__DATA_ZGLOSZENIA', $tablica['data_zgloszenia']);
      $srodek->dodaj('__DATA_MODYFIKACJI', $tablica['data_modyfikacji']);
      $srodek->dodaj('__OPIEKUN_ZGLOSZENIA', $tablica['opiekun']);
      $srodek->dodaj('__STATUS_ZGLOSZENIA', $tablica['aktualny_status']);
      $srodek->dodaj('__NUMER_ZAMOWIENIA', $tablica['numer_zamowienia']);
      
      $srodek->dodaj('__LISTA_PRODUKTOW_DO_ZWROTU', '<ul class="ListaProduktowZwrotu">' . implode('', $produkty) . '</ul>');

      $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

      unset($srodek, $WywolanyPlik, $tablica);

    } else {
      //
      $GLOBALS['db']->close_query($sql);
      unset($WywolanyPlik, $zapytanie);    
      //
      Funkcje::PrzekierowanieURL('brak-strony.html'); 
      //
    }      

    include('koniec.php');

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}
?>