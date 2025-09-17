<?php

  // plik
  $WywolanyPlik = 'produkty_przegladaj';

  include('start.php');

  if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $tablica = array();

    $zapytanie = "SELECT o.orders_id, o.customers_id, o.date_purchased, op.products_id, op.products_name, op.products_model 
                    FROM orders o 
                   RIGHT JOIN  orders_products op ON op.orders_id = o.orders_id
                   WHERE o.customers_id = '" . (int)$_SESSION['customer_id'] . "' GROUP BY op.products_id ORDER BY op.products_name";

    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        while ( $info = $sql->fetch_assoc() ) {
          
            $tablica[] = array('numer_zamowienia' => $info['orders_id'],
                               'id_produktu' => $info['products_id'],
                               'nazwa_produktu' => $info['products_name'],
                               'numer_katalogowy' => $info['products_model'],
                               'id_klienta' => $info['customers_id']);
                               
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);    
    
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_KUPIONE_PRODUKTY']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $tablica);

    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
    
    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $tablica);

    include('koniec.php');

  } else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

  }
?>