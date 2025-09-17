<?php
chdir('../');            
if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        if ( isset($_SESSION['krajDostawy']['kod']) ) {
          
            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

            $_POST['data'] = (int)$_POST['data'];

            $wysylki = new Wysylki( $_SESSION['krajDostawy']['kod'] );
            $tablicaWysylek = $wysylki->wysylki;
            
            if ( !isset($tablicaWysylek[$_POST['data']]) ) {
                  exit;
            }            

            unset($_SESSION['rodzajDostawy']);

            $_SESSION['rodzajDostawy'] = array('wysylka_id' => $tablicaWysylek[$_POST['data']]['id'],
                                               'wysylka_klasa' => $tablicaWysylek[$_POST['data']]['klasa'],
                                               'wysylka_koszt' => $tablicaWysylek[$_POST['data']]['wartosc'],
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
                                                   'platnosc_punkty' => ( isset($pierwsza_platnosc['0']['punkty']) ? $pierwsza_platnosc['0']['punkty'] : 'nie' ),
                                                   'platnosc_kanal' => ( isset($pierwsza_platnosc['0']['kanal_platnosci']) ? $pierwsza_platnosc['0']['kanal_platnosci'] : '' ),
              );
                                                  
            } else {
            
              if ( isset($tablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['id']) ) {
              
                  $PlatnoscId = $tablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['id'];
                  unset($_SESSION['rodzajPlatnosci']);
                  
                  $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $tablicaPlatnosci[$PlatnoscId]['id'],
                                                       'platnosc_klasa' => $tablicaPlatnosci[$PlatnoscId]['klasa'],
                                                       'platnosc_koszt' => $tablicaPlatnosci[$PlatnoscId]['wartosc'],
                                                       'platnosc_nazwa' => $tablicaPlatnosci[$PlatnoscId]['text'],
                                                       'platnosc_punkty' => ( isset($tablicaPlatnosci[$PlatnoscId]['punkty']) ? $tablicaPlatnosci[$PlatnoscId]['punkty'] : 'nie' ),
                                                       'platnosc_kanal' => ( isset($tablicaPlatnosci[$PlatnoscId]['kanal_platnosci']) ? $tablicaPlatnosci[$PlatnoscId]['kanal_platnosci'] : '' ),
                  );
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

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_santander', $tablicaPlatnosci) ) {
              $wynik['santander'] = '<a onclick="PoliczRateSantander('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_koszyk.png" alt="" /></a>';
              $bylKalkulator = true;
            } else {
              $wynik['santander'] = '';
            }

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_lukas', $tablicaPlatnosci) ) {
               $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_LUKAS_NUMER_SKLEPU'";
               $sqlp = $GLOBALS['db']->open_query($zap);
               //
               if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                //
                $infop = $sqlp->fetch_assoc();
                //
                $wynik['lukas'] = '<a onclick="PoliczRateLukas('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="https://ewniosek.credit-agricole.pl/eWniosek/button/img.png?creditAmount='.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].'&posId='.$infop['wartosc'].'&imgType=1" alt="" /></a>';
                $bylKalkulator = true;
               }
               //
               unset($infop);
               //
               //
               $GLOBALS['db']->close_query($sqlp); 
               unset($zap);    
            } else {
              $wynik['lukas'] = '';
            }

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_mbank', $tablicaPlatnosci) ) {
              $wynik['mbank'] = '<a onclick="PoliczRateMbank('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_mbank_koszyk.png" alt="" /></a>';
              $bylKalkulator = true;
            } else {
              $wynik['mbank'] = '';
            }

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_transferuj', $tablicaPlatnosci) || Funkcje::CzyJestWlaczonaPlatnosc('platnosc_tpay', $tablicaPlatnosci) ) {
                $zap = "SELECT kod, wartosc FROM modules_payment_params";
                $sqlp = $GLOBALS['db']->open_query($zap);
                //
                if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                    //
                    $raty_tpay = 'nie';
                    $rodzaj = '';
                    //
                    while ($infop = $sqlp->fetch_assoc()) {

                        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_transferuj', $tablicaPlatnosci) ) {
                            if ( $infop['kod'] == 'PLATNOSC_TPAY_RATY_KALKULATOR' ) {
                                 $raty_tpay = $infop['wartosc'];
                            }
                            if ( $infop['kod'] == 'PLATNOSC_TPAY_RATY_RODZAJ' ) {
                                 $rodzaj = $infop['wartosc'];
                            }
                        } elseif ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_tpay', $tablicaPlatnosci) ) {
                            if ( $infop['kod'] == 'PLATNOSC_TPAY_REST_RATY_KALKULATOR' ) {
                                 $raty_tpay = $infop['wartosc'];
                            }
                            if ( $infop['kod'] == 'PLATNOSC_TPAY_REST_RATY_RODZAJ' ) {
                                 $rodzaj = $infop['wartosc'];
                            }
                        }
                        //
                    }
                    //
                    if ( $rodzaj != '' && $raty_tpay == 'tak' ) {
                        $wynik['tpay'] = '<div id="RataTPay"><a onclick="PoliczRateTPay(\'' . $rodzaj . '\',' . ((float)$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] * 100) . ');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/tpay_raty.png" alt="TPAY Raty" /></a></div>';
                        $bylKalkulator = true;
                         //
                    }
                    //
                }
                //
                $GLOBALS['db']->close_query($sqlp); 
                unset($zap);   
            } else {
              $wynik['tpay'] = '';
            }
            
            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_bgz', $tablicaPlatnosci) ) {
              $wynik['bgz'] = '<a onclick="PoliczRateBgz('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_bgz_koszyk.png" alt="" /></a>';
              $bylKalkulator = true;
            } else {
              $wynik['bgz'] = '';
            }

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_ileasing', $tablicaPlatnosci) ) {
              $WartoscKoszykaNetto = 0;
              foreach ( $_SESSION['koszyk'] as $ProduktLeasing ) {
                //
                $WartoscKoszykaNetto += $ProduktLeasing['cena_netto'] * $ProduktLeasing['ilosc'];
                //
              }         

              $wynik['ileasing'] = '<a onclick="PoliczRateIleasing('.$WartoscKoszykaNetto.');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_ileasing_koszyk.png" alt="" /></a>';
              $bylKalkulator = true;
              unset($WartoscKoszykaNetto);
            } else {
              $wynik['ileasing'] = '';
            }

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_iraty', $tablicaPlatnosci) ) {
              $WartoscKoszykaBrutto = 0;
              foreach ( $_SESSION['koszyk'] as $ProduktIraty ) {
                //
                $WartoscKoszykaBrutto += $ProduktIraty['cena_brutto'] * $ProduktIraty['ilosc'];
                //
              }         

              $wynik['iraty'] = '<a onclick="PoliczRateIraty('.$WartoscKoszykaBrutto.');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_iraty_koszyk.png" alt="" /></a>';
              $bylKalkulator = true;
              unset($WartoscKoszykaBrutto);
            } else {
              $wynik['iraty'] = '';
            }

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu', $tablicaPlatnosci) || Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu_rest', $tablicaPlatnosci)) {
              if ( isset($_SESSION['podsumowanieZamowienia']) && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] >= 300 && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] < 20000 ) {
                  if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu', $tablicaPlatnosci) ) {
                      $Wiget = false;
                      $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_RATY_WLACZONE'";
                      $sqlp = $GLOBALS['db']->open_query($zap);
                      if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                        $infop = $sqlp->fetch_assoc();
                        if ( $infop['wartosc'] == 'tak' ) {
                            $zap_widget = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_RATY_KALKULATOR'";
                            $sqlp_widget = $GLOBALS['db']->open_query($zap_widget);

                            if ((int)$GLOBALS['db']->ile_rekordow($sqlp_widget) > 0) {
                                $infop_widget = $sqlp_widget->fetch_assoc();
                                if ( $infop_widget['wartosc'] == 'tak' ) {
                                    $Wiget = true;
                                }
                            }
                            $GLOBALS['db']->close_query($sqlp_widget); 
                            unset($zap_widget);    

                            if ( $Wiget == true ) {
                              $wynik['payu'] = '<script> $(document).ready(function() { PoliczRatePauYRaty('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].'); }); </script> <div style="margin-bottom:5px"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_payu_koszyk.png" alt="" /></div><div id="RataPayUKoszyk"></div>';                   
                              $bylKalkulator = true;
                            }
                        }
                        unset($infop);
                      }
                      $GLOBALS['db']->close_query($sqlp); 
                      unset($zap);
                  } elseif ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu_rest', $tablicaPlatnosci) ) {
                      $Wiget = false;
                      $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_REST_RATY_WLACZONE'";
                      $sqlp = $GLOBALS['db']->open_query($zap);
                      if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                        $infop = $sqlp->fetch_assoc();
                        if ( $infop['wartosc'] == 'tak' ) {
                            $zap_widget = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_REST_RATY_KALKULATOR'";
                            $sqlp_widget = $GLOBALS['db']->open_query($zap_widget);

                            if ((int)$GLOBALS['db']->ile_rekordow($sqlp_widget) > 0) {
                                $infop_widget = $sqlp_widget->fetch_assoc();
                                if ( $infop_widget['wartosc'] == 'tak' ) {
                                    $Wiget = true;
                                }
                            }
                            $GLOBALS['db']->close_query($sqlp_widget); 
                            unset($zap_widget);    

                            if ( $Wiget == true ) {
                                $wynik['payu'] = '<script> $(document).ready(function() { PoliczRatePauYRaty('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].'); }); </script> <div style="margin-bottom:5px"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_payu_koszyk.png" alt="" /></div><div id="RataPayUKoszyk"></div>';                   
                                $bylKalkulator = true;
                            }
                        }
                        unset($infop);
                      }
                      $GLOBALS['db']->close_query($sqlp); 
                      unset($zap);
                  }
              }
            } else {
              $wynik['payu'] = '';
            }

            if ( $_SESSION['rodzajPlatnosci']['platnosc_id'] == '0' || $_SESSION['rodzajDostawy']['wysylka_id'] == '0' ) {
              $wynik['przycisk_zamow'] = false;
            } else {
              $wynik['przycisk_zamow'] = true;
            }

            if ( $tablicaWysylek[$_POST['data']]['klasa'] == 'wysylka_bliskapaczka' ) {
              if ( isset($_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']['punktodbioru']) && $_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']['punktodbioru'] != '' ) {
                $wynik['przycisk_zamow'] = true;
              } else {
                $wynik['przycisk_zamow'] = false;
              }
            }

            $wynik['wysylka_free'] = $bezplatna_dostawa;

            if ( $bylKalkulator == true ) {
                $wynik['raty'] = 'OK';
            } else {
                $wynik['raty'] = '';
            }
            
            // kupon rabatowy
            if ( isset($_SESSION['podsumowanieZamowienia']) && isset($_SESSION['podsumowanieZamowienia']['ot_discount_coupon']) ) {
                 $wynik['kupon'] = 'tak';
            } else {
                 $wynik['kupon'] = 'nie';
            }        

            unset($tablicaWysylek,$tablicaPlatnosci,$pierwsza_platnosc,$podsumowanie_zamowienia);

            echo json_encode($wynik);
            
        }

    }
    
}

?>