<?php

// plik
$WywolanyPlik = 'reklamacje_przegladaj';

include('start.php');

if ( REKLAMACJE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

  $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

  $tablica = array();

  $zapytanie = "SELECT c.complaints_id, c.complaints_rand_id, c.complaints_customers_id, c.complaints_customers_orders_id, c.complaints_subject, c.complaints_status_id, c.complaints_date_created, c.complaints_date_modified, c.complaints_service
                  FROM complaints c 
                 WHERE c.complaints_customers_id = '" . (int)$_SESSION['customer_id'] . "' ORDER BY complaints_id DESC";

  $sql = $GLOBALS['db']->open_query($zapytanie); 
  
  if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
      while ( $info = $sql->fetch_assoc() ) {

        $tablica[$info['complaints_id']] = array(
                       'id_zgloszenia' => $info['complaints_id'],
                       'numer_zgloszenia' => $info['complaints_rand_id'],
                       'tytul_zgloszenia' => $info['complaints_subject'],
                       'status_zgloszenia' => Reklamacje::pokazNazweStatusuReklamacji($info['complaints_status_id'],(int)$_SESSION['domyslnyJezyk']['id']),
                       'data_zgloszenia' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['complaints_date_created'])),
                       'data_modyfikacji' => date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['complaints_date_modified'])),
                       'numer_zamowienia' => $info['complaints_customers_orders_id']);
        
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
  $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PRZEGLADANIE_REKLAMACJI']);
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