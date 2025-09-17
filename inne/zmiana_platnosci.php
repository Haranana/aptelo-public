<?php
chdir('../');

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {
      
        if ( isset($_SESSION['rodzajDostawy']['wysylka_id']) && (int)$_SESSION['rodzajDostawy']['wysylka_id'] > 0 ) {

            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

            $platnosci = new Platnosci( $_SESSION['rodzajDostawy']['wysylka_id'] );
            $tablicaPlatnosci = $platnosci->platnosci;
            $_POST['data'] = (int)$_POST['data'];
            
            if ( !isset($tablicaPlatnosci[$_POST['data']]) ) {
                  exit;
            }

            unset($_SESSION['rodzajPlatnosci']);
            $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $tablicaPlatnosci[$_POST['data']]['id'],
                                                 'platnosc_klasa' => $tablicaPlatnosci[$_POST['data']]['klasa'],
                                                 'platnosc_koszt' => $tablicaPlatnosci[$_POST['data']]['wartosc'],
                                                 'platnosc_nazwa' => $tablicaPlatnosci[$_POST['data']]['text'],
                                                 'platnosc_punkty' => ( isset($tablicaPlatnosci[$_POST['data']]['punkty']) ? $tablicaPlatnosci[$_POST['data']]['punkty'] : '' ),
                                                 'platnosc_kanal' => ( isset($tablicaPlatnosci[$_POST['data']]['kanal_platnosci']) ? $tablicaPlatnosci[$_POST['data']]['kanal_platnosci'] : '' ));

            // parametry do ustalenia podsumowania zamowienia
            $podsumowanie = new Podsumowanie();
            $podsumowanie_zamowienia = $podsumowanie->Generuj();

            $wynik = array();
            $wynik['podsumowanie'] = $podsumowanie_zamowienia;

            if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_santander', $tablicaPlatnosci) ) {
              $wynik['santander'] = '<a onclick="PoliczRateSantander('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_koszyk.png" alt="" /></a>';
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
            
            // kupon rabatowy
            if ( isset($_SESSION['podsumowanieZamowienia']) && isset($_SESSION['podsumowanieZamowienia']['ot_discount_coupon']) ) {
                 $wynik['kupon'] = 'tak';
            } else {
                 $wynik['kupon'] = 'nie';
            }
                 
            unset($tablicaPlatnosci,$podsumowanie_zamowienia);

            echo json_encode($wynik);
            
        }

    }
    
}

?>