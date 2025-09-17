<?php

// plik
$WywolanyPlik = 'zwroty_produktow_przegladaj';

include('start.php');

if ( ZWROTY_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0') {

  $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

  $tablica = array();

  $zapytanie = "SELECT rl.return_id, rl.return_rand_id, rl.return_customers_id, rl.return_customers_orders_id, rl.return_status_id, rl.return_date_created, rl.return_date_modified, rl.return_service
                  FROM return_list rl 
                 WHERE rl.return_customers_id = '" . (int)$_SESSION['customer_id'] . "' ORDER BY return_id DESC";

  $sql = $GLOBALS['db']->open_query($zapytanie); 
  
  if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
      while ( $info = $sql->fetch_assoc() ) {
        
          // ilosc produktow do zwrotu
          $zapytanie_produkty = "SELECT return_products_quantity, return_products_orders_id FROM return_products WHERE return_id = '" . $info['return_id'] . "'";
          $sql_produkty = $GLOBALS['db']->open_query($zapytanie_produkty);    

          $tablica[$info['return_id']] = array(
                         'id_zgloszenia' => $info['return_id'],
                         'numer_zgloszenia' => $info['return_rand_id'],
                         'status_zgloszenia' => Zwroty::pokazNazweStatusuZwrotu($info['return_status_id'],(int)$_SESSION['domyslnyJezyk']['id']),
                         'data_zgloszenia' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['return_date_created'])),
                         'data_modyfikacji' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['return_date_modified'])),
                         'numer_zamowienia' => $info['return_customers_orders_id'],
                         'ilosc_produktow_do_zwrotu' => (int)$GLOBALS['db']->ile_rekordow($sql_produkty));
                         
          $GLOBALS['db']->close_query($sql_produkty);
        
      }
      
      unset($info);
    
  }
  
  $GLOBALS['db']->close_query($sql);
  unset($zapytanie);
  
  // meta tagi
  $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
  $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
  $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
  $tpl->dodaj('__META_OPIS', $Meta['opis']);
  unset($Meta);

  // breadcrumb
  $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
  $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PRZEGLADANIE_ZWROTOW']);
  $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

  // wyglad srodkowy
  $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $tablica);
  $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

  unset($srodek, $WywolanyPlik, $tablica);

  include('koniec.php');

} else {

  Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}
?>