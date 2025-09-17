<?php

// plik
$WywolanyPlik = 'reklamacje_szczegoly';

include('start.php');

if ( REKLAMACJE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$AktywnyHash = false;
$hashKod = '';

$NrReklamacji = '';

if ( isset($_GET['id']) && $_GET['id'] != '' ) {

    $IdGetReklamacja = $filtr->process($_GET['id']);
    
    $TablicaReklamacji = array();
    //
    $zapytanie = "SELECT * FROM complaints";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {
            //
            $TablicaReklamacji[] = array('id' => $info['complaints_id'],
                                         'nr_reklamacji' => $info['complaints_rand_id']);
            //
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);         

    foreach ( $TablicaReklamacji as $Reklamacja ) {  
        //
        if ( $Reklamacja['nr_reklamacji'] == $IdGetReklamacja ) {
             //
             $NrReklamacji = $Reklamacja['nr_reklamacji'];
             //
        }
        //
    }
    
    unset($IdGetReklamacja, $TablicaReklamacji);
    
}

if ( $NrReklamacji == '' ) {
     //
     Funkcje::PrzekierowanieURL('brak-strony.html'); 
     //
} else {
     //
     $_GET['id'] = $NrReklamacji;
     //
}

if ( isset($_GET['reklamacja']) && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' && isset($_GET['id']) && $_GET['id'] != '' ) {

    $hashKod = '';
    //
    $zapytanie_reklamacja = "SELECT * FROM complaints where complaints_rand_id = '" . $filtr->process($_GET['id']) . "'";
    $sql_reklamacja = $db->open_query($zapytanie_reklamacja);  
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql_reklamacja) > 0) {

        $DaneReklamacji = $sql_reklamacja->fetch_assoc();
    
        $hashKod = hash("sha1", $DaneReklamacji['complaints_rand_id'] . ';' . $DaneReklamacji['complaints_date_created'] . ';' . $DaneReklamacji['complaints_customers_email'] . ';' . $DaneReklamacji['complaints_customers_id'] . ';' . $DaneReklamacji['complaints_customers_orders_id']);
        
        unset($DaneReklamacji);
        
    }

    $GLOBALS['db']->close_query($sql_reklamacja); 
    unset($zapytanie_reklamacja); 
    
    if ( $_GET['reklamacja'] === $hashKod && $hashKod != '' ) {
         $AktywnyHash = true;
    }

}  

if ( ((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') || $AktywnyHash == true) && isset($_GET['id']) && $_GET['id'] != '' ) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

    $zapytanie = "SELECT * FROM complaints cu
               LEFT JOIN customers c ON cu.complaints_customers_id = c.customers_id
                   WHERE cu.complaints_rand_id = '" . $filtr->process($_GET['id']) . "'";

    $sql = $GLOBALS['db']->open_query($zapytanie);
  
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
      $info = $sql->fetch_assoc();

      // jezeli jest przeslanie informacji od klienta
      if ( isset($_POST['info_klienta']) && $_POST['info_klienta'] == 'tak' && isset($_POST['id']) && (int)$_POST['id'] > 0 && isset($_POST['aktualny_status']) && (int)$_POST['aktualny_status'] > 0 ) {
        
          // sprawdzi ile bylo komentarzy
          $zapytanie_komentarze = "SELECT complaints_customers_info FROM complaints_status_history WHERE complaints_id = '" . (int)$info['complaints_id'] . "' AND complaints_customers_info = '1'";
          $sql_komentarze = $GLOBALS['db']->open_query($zapytanie_komentarze);

          if ((int)$GLOBALS['db']->ile_rekordow($sql_komentarze) < (int)REKLAMACJE_ILOSC_KOMENTARZY) {          
        
              if ( !empty($_POST['komentarz']) ) {

                  $pola = array(
                          array('complaints_id',(int)$info['complaints_id']),
                          array('complaints_status_id',(int)$info['complaints_status_id']),
                          array('date_added','now()'),
                          array('customer_notified','0'),
                          array('comments',$filtr->process($_POST['komentarz'])),
                          array('complaints_customers_info','1'),
                          array('complaints_customers_info_view','0'));

                  $GLOBALS['db']->insert_query('complaints_status_history' , $pola);
                  unset($pola);        
                              
                  // wysylanie maila do administratora sklepu
                  $nadawca_email   = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                  $nadawca_nazwa   = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);
                  $odpowiedz_email = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                  $odpowiedz_nazwa = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);

                  $adres_email     = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                  
                  $adresat_nazwa   = $filtr->process(INFO_NAZWA_SKLEPU);

                  $temat           = $GLOBALS['tlumacz']['TEMAT_REKLAMACJA_MAIL'] . ' ' . $filtr->process($_GET['id']);

                  $zalaczniki      = Array();
                  $szablon         = 1;
                  $jezyk           = (int)$_SESSION['domyslnyJezyk']['id'];

                  $tekst = '<b>' . $GLOBALS['tlumacz']['TYTUL_REKLAMACJI'] . ': ' . $info['complaints_subject'] . '</b><br /><br />' . $filtr->process($_POST['komentarz']);

                  $email = new Mailing;
                  $email->wyslijEmail($nadawca_email, $nadawca_nazwa ,$adres_email, $adresat_nazwa, '', $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email, $odpowiedz_nazwa);
                  unset($email);        
              
                  Funkcje::PrzekierowanieSSL( 'reklamacje-szczegoly-rs-' . $filtr->process($_GET['id']) . '.html' . ((isset($_GET['reklamacja']) && $_GET['reklamacja'] != '' && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak') ? '/reklamacja=' . $filtr->process($_GET['reklamacja']) : '') );
                  
              }
              
          } else {
            
              Funkcje::PrzekierowanieURL('brak-strony.html'); 
              
          }
          
          $GLOBALS['db']->close_query($sql_komentarze);
          unset($zapytanie_komentarze);          
        
      }
      
      $tablica = array();

      $id_reklamacji = $info['complaints_id'];

      // pobieranie informacji od uzytkownikach
      if ($info['complaints_service'] > 0) {

          $zapytanie_uzytkownicy = "SELECT * FROM admin WHERE admin_id = '" . $info['complaints_service'] . "'";
          $sql_uzytkownicy = $GLOBALS['db']->open_query($zapytanie_uzytkownicy);
          $uzytkownicy = $sql_uzytkownicy->fetch_assoc();
          $obsluga = $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'];
          $GLOBALS['db']->close_query($sql_uzytkownicy); 
          unset($zapytanie_uzytkownicy, $uzytkownicy);
        
      } else {
        
          $obsluga = '-';
        
      }
      //

      $tablica = array('id' => $filtr->process($_GET['id']),
                       'id_reklamacji' => $info['complaints_id'],
                       'tytul_reklamacji' => $info['complaints_subject'],
                       'data_zgloszenia' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['complaints_date_created'])),
                       'data_modyfikacji' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['complaints_date_modified'])),
                       'numer_zamowienia' => $info['complaints_customers_orders_id'],
                       'opiekun' => $obsluga,
                       'aktualny_status_id' => $info['complaints_status_id'],
                       'aktualny_status' => Reklamacje::pokazNazweStatusuReklamacji($info['complaints_status_id'],(int)$_SESSION['domyslnyJezyk']['id']));
                       
      // zdjecia reklamacji
      
      $zdjecia_reklamacji = array();

      for ( $x = 1; $x < 4; $x++ ) {
        
          if ( !empty($info['complaints_image_' . $x]) ) {

              if ( file_exists('grafiki_inne/' . $info['complaints_image_' . $x]) ) {
                   
                   $zdjecia_reklamacji[] = '<a style="margin:5px 10px 5px 0px" class="ZdjeciaReklamacji" href="grafiki_inne/' . $info['complaints_image_' . $x] . '"><img style="max-width:150px;max-height:100px;width:auto;height:auto" src="grafiki_inne/' . $info['complaints_image_' . $x] . '" alt="" /></a>';
                   
              }
              
          }
          
      }
      
      $zdjecia_reklamacji_wynik = '';
      
      if ( count($zdjecia_reklamacji) > 0 ) {
           //
           $zdjecia_reklamacji_wynik = '<div class="ZdjeciaReklamacjiKlienta" style="display:flex;flex-wrap:wrap;margin-top:15px">' . implode('', $zdjecia_reklamacji) . '</div>';
           //
      }
                       
      $GLOBALS['db']->close_query($sql);
      unset($zapytanie, $info, $zdjecia_reklamacji);
      
      $tablica_statusow = array();
      
      $info_klienta = 0;

      $zapytanie_statusy = "SELECT csh.complaints_status_id, csh.date_added, csh.customer_notified, csh.comments, csh.complaints_customers_info, cs.complaints_status_type
                              FROM complaints_status_history csh, complaints_status cs WHERE csh.complaints_id = '" . (int)$id_reklamacji . "' AND csh.complaints_status_id = cs.complaints_status_id ORDER BY csh.date_added";
                              
      $sql_statusy = $GLOBALS['db']->open_query($zapytanie_statusy);

      if ((int)$GLOBALS['db']->ile_rekordow($sql_statusy) > 0) {
        
          $j = 0;
        
          while ($info_statusy = $sql_statusy->fetch_assoc()) {
            
              $tablica_statusow[] = array('id_statusu' => $info_statusy['complaints_status_id'],
                                          'klient_powiadomiony' => $info_statusy['customer_notified'],
                                          'data_dodania' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info_statusy['date_added'])),
                                          'komentarz' => '<div class="FormatEdytor">' . $info_statusy['comments'] . '</div>',
                                          'informacja_od_klienta' => (($info_statusy['complaints_customers_info'] == '1') ? 'tak' : 'nie'),
                                          'status_zamkniety' => (($info_statusy['complaints_status_type'] == 3 || $info_statusy['complaints_status_type'] == 4) ? 'tak' : 'nie'),
                                          'status' => Reklamacje::pokazNazweStatusuReklamacji($info_statusy['complaints_status_id'],(int)$_SESSION['domyslnyJezyk']['id']),
                                          'zdjecia_reklamacji' => (($j == 0) ? $zdjecia_reklamacji_wynik : ''));
                                          
              $j++;
              
              if ( $info_statusy['complaints_customers_info'] == '1') {
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
      $nawigacja->dodaj($GLOBALS['tlumacz']['PRZEGLADAJ_REKLAMACJE'],Seo::link_SEO('reklamacje_przegladaj.php', '', 'inna'));
      $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_SZCZEGOLY_REKLAMACJI']);
      $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

      // wyglad srodkowy
      $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $tablica, $tablica_statusow, (($info_klienta < (int)REKLAMACJE_ILOSC_KOMENTARZY) ? 'tak' : 'nie'));

      //parametry do podstawienia
      $srodek->dodaj('__ID_ZGLOSZENIA', $tablica['id']);
      $srodek->dodaj('__TYTUL_ZGLOSZENIA', $tablica['tytul_reklamacji']);
      $srodek->dodaj('__DATA_ZGLOSZENIA', $tablica['data_zgloszenia']);
      $srodek->dodaj('__DATA_MODYFIKACJI', $tablica['data_modyfikacji']);
      $srodek->dodaj('__OPIEKUN_ZGLOSZENIA', $tablica['opiekun']);
      $srodek->dodaj('__STATUS_ZGLOSZENIA', $tablica['aktualny_status']);
      $srodek->dodaj('__NUMER_ZAMOWIENIA', $tablica['numer_zamowienia']);

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