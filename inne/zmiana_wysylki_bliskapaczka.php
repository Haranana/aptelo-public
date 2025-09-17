<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

//    if (Sesje::TokenSpr()) {


        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

        $wysylki = new Wysylki( $_SESSION['krajDostawy']['kod'] );
        $tablicaWysylek = $wysylki->wysylki;

        $_POST['data'] = $filtr->process($_POST['data']);

        unset($_SESSION['rodzajDostawy']);
        $_SESSION['rodzajDostawy'] = array('wysylka_id' => $tablicaWysylek[$_POST['data']]['id'],
                                           'wysylka_klasa' => $tablicaWysylek[$_POST['data']]['klasa'],
                                           'wysylka_koszt' => $_POST['price'],
                                           'wysylka_nazwa' => $tablicaWysylek[$_POST['data']]['text'],
                                           'wysylka_vat_id' => $tablicaWysylek[$_POST['data']]['vat_id'],
                                           'wysylka_vat_stawka' => $tablicaWysylek[$_POST['data']]['vat_stawka'],
                                           'wysylka_kod_gtu' => $tablicaWysylek[$_POST['data']]['kod_gtu'],
                                           'dostepne_platnosci' => $tablicaWysylek[$_POST['data']]['dostepne_platnosci'] );

        $platnosci = new Platnosci( $_POST['data'] );
        $tablicaPlatnosci = $platnosci->platnosci;

        if ( isset($_SESSION['rodzajPlatnosci']['platnosc_id']) && !array_key_exists($_SESSION['rodzajPlatnosci']['platnosc_id'], $tablicaPlatnosci) ) {
        
          $pierwsza_platnosc = array_slice((array)$tablicaPlatnosci,0,1);
          unset($_SESSION['rodzajPlatnosci']);
          
          $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $pierwsza_platnosc['0']['id'],
                                               'platnosc_klasa' => $pierwsza_platnosc['0']['klasa'],
                                               'platnosc_koszt' => $pierwsza_platnosc['0']['wartosc'],
                                               'platnosc_nazwa' => $pierwsza_platnosc['0']['text'],
                                               'platnosc_punkty' => ( isset($pierwsza_platnosc['0']['punkty']) ? $pierwsza_platnosc['0']['punkty'] : 'nie' ));
                                              
        } else {
        
          if ( isset($tablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['id']) ) {
          
              $PlatnoscId = $tablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['id'];
              unset($_SESSION['rodzajPlatnosci']);
              
              $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $tablicaPlatnosci[$PlatnoscId]['id'],
                                                   'platnosc_klasa' => $tablicaPlatnosci[$PlatnoscId]['klasa'],
                                                   'platnosc_koszt' => $tablicaPlatnosci[$PlatnoscId]['wartosc'],
                                                   'platnosc_nazwa' => $tablicaPlatnosci[$PlatnoscId]['text'],
                                                   'platnosc_punkty' => ( isset($tablicaPlatnosci[$PlatnoscId]['punkty']) ? $tablicaPlatnosci[$PlatnoscId]['punkty'] : 'nie' ));
              unset($PlatnoscId);
          }
          
        }

        // parametry do ustalenia podsumowania zamowienia
        $podsumowanie = new Podsumowanie();
        $podsumowanie_zamowienia = $podsumowanie->Generuj();

        $wynik = array();
        $bezplatna_dostawa = '';
        $bylKalkulator = false;

        if ( $tablicaWysylek[$_POST['data']]['wysylka_free'] > 0 && isset($_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc']) ) {

          $wartosc_zamowienia = $_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc'];
          //       
          // jezeli musi pominac promocje
          if ( $tablicaWysylek[$_POST['data']]['free_promocje'] == 'nie' ) {

               // wartosc produktow w promocji - potrzebne do wysylek
               $wartosc_produktow_promocje = 0;
               
               foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {

                  if ( $TablicaZawartosci['promocja'] == 'tak' ) {
                       $wartosc_produktow_promocje += $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
                  }
                  
               }
               
               $wartosc_zamowienia -= $wartosc_produktow_promocje;

          }
          
          if ( $tablicaWysylek[$_POST['data']]['wysylka_free'] > $wartosc_zamowienia ) {

              $bezplatna_dostawa = str_replace( '{KWOTA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($tablicaWysylek[$_POST['data']]['wysylka_free'], $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', (string)$GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA'] );
              
              if ( $tablicaWysylek[$_POST['data']]['free_promocje'] == 'nie' ) {
                   $bezplatna_dostawa .= ' ' . $GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA_BEZ_PROMOCJI'];
              }          

              // ile brakuje
              $ile_brakuje = $tablicaWysylek[$_POST['data']]['wysylka_free'] - $wartosc_zamowienia;
              //
              if ( $ile_brakuje > 0 ) {
                   //
                   $procent_suwak = (int)(($wartosc_zamowienia/$tablicaWysylek[$_POST['data']]['wysylka_free']) * 100);
                   //
                   $bezplatna_dostawa .= '<div class="WysylkaSuwak"><div class="WysylkaSuwakTlo"><div class="WysylkaSuwakWartosc" style="width:' . $procent_suwak . '%"></div></div></div>';
                   $bezplatna_dostawa .= '<div class="WysylkaIleBrakuje">' . (string)$GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA_BRAKUJE'] . ' <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ile_brakuje, $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                   //
                   unset($procent_suwak);
                   //
              }
              //
              unset($ile_brakuje);
              
          }
          
        }

        $wynik['platnosci'] = Funkcje::ListaRadioKoszyk('rodzaj_platnosci', $tablicaPlatnosci, $_SESSION['rodzajPlatnosci']['platnosc_id'], '');

        $wynik['podsumowanie'] = $podsumowanie_zamowienia;

//        if ( $_SESSION['rodzajPlatnosci']['platnosc_id'] == '0' || $_SESSION['rodzajDostawy']['wysylka_id'] == '0' ) {
          $wynik['przycisk_zamow'] = false;
//        } else {
//          $wynik['przycisk_zamow'] = true;
//        }
        $wynik['wysylka_free'] = $bezplatna_dostawa;

        // kupon rabatowy
        if ( isset($_SESSION['podsumowanieZamowienia']) && isset($_SESSION['podsumowanieZamowienia']['ot_discount_coupon']) ) {
             $wynik['kupon'] = 'tak';
        } else {
             $wynik['kupon'] = 'nie';
        }
        
        unset($tablicaWysylek,$tablicaPlatnosci,$podsumowanie_zamowienia);

        echo json_encode($wynik);

//    }
    
}

?>