<?php

  // plik
  $WywolanyPlik = 'zamowienia_przegladaj';

  include('start.php');

  if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $tablica = array();

    $zapytanie = "SELECT o.orders_id, o.currency, o.date_purchased, o.delivery_name, o.delivery_name, o.delivery_company, o.delivery_street_address, o.delivery_postcode, o.delivery_city, o.delivery_country, o.payment_method, o.shipping_module, ot.text, s.orders_status_type, s.orders_status_id, osd.orders_status_name 
                    FROM orders o 
                    LEFT JOIN orders_total ot ON ot.orders_id = o.orders_id AND ot.class = 'ot_total'
                    LEFT JOIN orders_status s ON o.orders_status = s.orders_status_id
                    LEFT JOIN orders_status_description osd ON osd.orders_status_id = s.orders_status_id AND osd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                    WHERE o.customers_id = '" . (int)$_SESSION['customer_id'] . "' ORDER BY orders_id DESC";

    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    $IloscNaStronie = 20;
    $KolejneStrony = '';
    $CzySaKolejneStrony = 'nie';

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
  
       if ( (int)$GLOBALS['db']->ile_rekordow($sql) > $IloscNaStronie ) {

            $Strony = Stronicowanie::PokazStrony((int)$GLOBALS['db']->ile_rekordow($sql), 'zamowienia-przegladaj.html', $IloscNaStronie);
            //
            $LinkiDoStron = $Strony[0];
            $LimitSql = $Strony[1];
            //
            $KolejneStrony = $LinkiDoStron;
            //
            $zapytanie = $zapytanie . " LIMIT " . $LimitSql . "," . $IloscNaStronie;
            $GLOBALS['db']->close_query($sql);
            //
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            $CzySaKolejneStrony = 'tak';
            
       }

       while ( $info = $sql->fetch_assoc() ) {

          $zapytanie_ilosc = "SELECT COUNT(*) AS ilosc FROM orders_products WHERE orders_id = '" . (int)$info['orders_id'] . "'";
          $sql_ilosc = $GLOBALS['db']->open_query($zapytanie_ilosc);
          
          $info_ilosc = $sql_ilosc->fetch_assoc();

          if ( $info_ilosc['ilosc'] > 0 ) {
            
              $zapytanie_dostawy = "SELECT orders_shipping_type, orders_shipping_number, orders_shipping_link FROM orders_shipping WHERE orders_id = '" . (int)$info['orders_id'] . "' ORDER BY orders_shipping_date_created asc";
              $sql_dostawy = $GLOBALS['db']->open_query($zapytanie_dostawy);

              $dostawa_nr_przesylki = '';
              $dostawa_link_sledzenia = '';
              
              if ((int)$GLOBALS['db']->ile_rekordow($sql_dostawy) > 0) {
               
                  while ($info_dostawy = $sql_dostawy->fetch_assoc()) {

                      // jezeli nie xml DHL
                      if ( !strpos((string)$info_dostawy['orders_shipping_number'], '.xml') ) {                      
                           //
                           $dostawa_nr_przesylki = $info_dostawy['orders_shipping_number'];
                           //                  
                           if ( !empty($info_dostawy['orders_shipping_number']) ) {
                                //
                                $dostawa_link_sledzenia = $info_dostawy['orders_shipping_link'];
                                //
                           }                                             
                      }
                      
                  }

                  unset($info_dostawy);
                 
              }    

              $GLOBALS['db']->close_query($sql_dostawy);
              unset($zapytanie_dostawy);              
            
              $tablica[$info['orders_id']] = array('numer_zamowienia' => $info['orders_id'],
                                                   'waluta_zamowienia' => $info['currency'],
                                                   'data_zamowienia' => $info['date_purchased'],
                                                   'odbiorca' => $info['delivery_name'],
                                                   'odbiorca_firma' => $info['delivery_company'],
                                                   'odbiorca_ulica' => $info['delivery_street_address'],
                                                   'odbiorca_kod' => $info['delivery_postcode'],
                                                   'odbiorca_miasto' => $info['delivery_city'],
                                                   'odbiorca_kraj' => $info['delivery_country'],
                                                   'wartosc' => $info['text'],
                                                   'status_zamowienia' => $info['orders_status_name'],
                                                   'typ_statusu' => $info['orders_status_type'],
                                                   'id_statusu' => $info['orders_status_id'],
                                                   'ilosc_produktow' => $info_ilosc['ilosc'],
                                                   'rodzaj_platnosci' => $info['payment_method'],
                                                   'rodzaj_wysylki' => $info['shipping_module'],
                                                   'nr_przesylki' => $dostawa_nr_przesylki,
                                                   'link_sledzenia' => $dostawa_link_sledzenia);
                                                   
              unset($dostawa_nr_przesylki, $dostawa_link_sledzenia);
                           
           }
          
           $GLOBALS['db']->close_query($sql_ilosc);
           unset($zapytanie_ilosc, $info_ilosc);

       }
       
       unset($info);
      
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
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_HISTORIA_ZAMOWIEN']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // style css
    $tpl->dodaj('__CSS_PLIK', ',listingi');      
    
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $tablica, $CzySaKolejneStrony);

    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);

    // stronicowanie
    $srodek->dodaj('__STRONICOWANIE', $KolejneStrony);    
      
    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $tablica, $KolejneStrony, $IloscNaStronie, $CzySaKolejneStrony);

    include('koniec.php');

  } else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

  }
?>