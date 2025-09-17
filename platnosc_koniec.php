<?php
$WywolanyPlik = 'platnosci_sukces';

include('start.php');

if ( !isset($_SESSION['platnoscElektroniczna']) ) {

    $tablicaGet = $_GET;
    if ( isset($_POST) ) {
    
        $tablicaPost = $_POST;
        $platnosci = array_merge($tablicaGet, $tablicaPost);
        
    } else {
    
        $platnosci = $tablicaGet;
        
    }

    if ( isset($platnosci['nrzam']) && $platnosci['nrzam'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['nrzam'];
        unset($platnosci['nrzam']);
        
    }

    // numer zamowienia z CashBill
    if ( isset($platnosci['userdata']) && $platnosci['userdata'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['userdata'];
        unset($platnosci['userdata']);
        
    }

    // numer zamowienia z Payeezy
    if ( isset($platnosci['order_id']) && $platnosci['order_id'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['order_id'];
        unset($platnosci['order_id']);
        
    }

    // numer zamowienia z CA
    if ( isset($platnosci['orderNumber']) && $platnosci['orderNumber'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['orderNumber'];
        unset($platnosci['orderNumber']);
        
    }
    
    // numer zamowienia z BlueMedia
    if ( isset($platnosci['OrderID']) && $platnosci['OrderID'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['OrderID'];
        unset($platnosci['OrderID']);
        
    }

    // numer zamowienia z eservice
    if ( isset($platnosci['OrderId']) && $platnosci['OrderId'] != '' ) {
    
        if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) {
          
             $platnosci['zamowienie_id'] = $platnosci['OrderId'];
             
        } else {
          
             $platnosci['zamowienie_id'] = $platnosci['OrderId'];
             $platnosci['eservice'] = 'tak';
        }
        
    }

    if ( !isset($platnosci['eservice']) ) {
      
        $_SESSION['platnoscElektroniczna'] = $platnosci;
      
        unset($tablicaGet, $tablicaPost, $platnosci);
        Funkcje::PrzekierowanieURL('platnosc_koniec.php');
    
    }
    
}

if ( !isset($platnosci['eservice']) ) {

    if ( !isset($_SESSION['platnoscElektroniczna']['zamowienie_id']) ) {
        $_SESSION['platnoscElektroniczna']['zamowienie_id'] = '0';
    }

}

if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && !isset($platnosci['eservice']) ) {

    // sprawdzenie czy istnieje zamowienie w bazie danych
    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "' AND customers_id = '" . (int)$_SESSION['customer_id'] . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {


        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('PLATNOSCI','PRZYCISKI') ), $GLOBALS['tlumacz'] );

        $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
        // meta tagi
        $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
        $tpl->dodaj('__META_OPIS', $Meta['opis']);
        unset($Meta);

        $komunikat       = '';
        $komunikat_bledu = '';

        // ##############################################################################
        // platnosc DotPay
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'dotpay' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc BlueMedia
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'bluemedia' && ( isset($_SESSION['platnoscElektroniczna']['ServiceID']) && $_SESSION['platnoscElektroniczna']['ServiceID'] != '') ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLUEMEDIA']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC_BLUEMEDIA'];
                
        }

        // ##############################################################################
        // platnosc Hotpay
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'hotpay' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLUEMEDIA']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC_BLUEMEDIA'];
                
        }

        // ##############################################################################
        // platnosc Comfino
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'comfino' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
        }

        // ##############################################################################
        // platnosc Paynow
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'paynow' ) {
            if ( $_SESSION['platnoscElektroniczna']['paymentStatus'] == 'CONFIRMED' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['paymentStatus'] == 'ERROR' || $_SESSION['platnoscElektroniczna']['paymentStatus'] == 'REJECTED' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc Payeezy
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'payeezy' ) {

            $komentarz = '';
            $blad = '';

            //$status_zamowienia_id  = Funkcje::PokazDomyslnyStatusZamowienia();
            //$tranzakcjaPoprawna    = false;

            //if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' && $_SESSION['platnoscElektroniczna']['response_code'] == '35' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik          = 'platnosci_sukces';
                $komunikat             = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $status_zamowienia_id  = Funkcje::PokazDomyslnyStatusZamowienia('PAYEEZY');

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
                $komentarz          .= $GLOBALS['tlumacz']['DATA_TRANZAKCJI'].': ' . date("d-m-Y H:i:s") . '<br />';
                if ( isset($_SESSION['platnoscElektroniczna']['message']) ) {
                    $komentarz          .= 'Status transakcji: ' . $_SESSION['platnoscElektroniczna']['message'];
                }

                $pola = array(
                        array('orders_id ',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified ','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                if ( isset($_SESSION['platnoscElektroniczna']['message']) ) {
                    $blad = $_SESSION['platnoscElektroniczna']['message'];
                }
                $komunikat_bledu = $blad;

            }

            //unset($tranzakcjaPoprawna, $komentarz);

        }

        // ##############################################################################
        // platnosc PayByNet
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'pbn' ) {
        
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
            
        }

        // ##############################################################################
        // platnosc Przelewy24
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'przelewy24' ) {
        
            $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLUEMEDIA']);
            $WywolanyPlik    = 'platnosci_sukces';
            $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC_BLUEMEDIA'];

        }

        // ##############################################################################
        // platnosc PayU
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'payu' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];

                $blad = PlatnosciElektroniczne::payu_tablicaBledow($_SESSION['platnoscElektroniczna']['error']);
                $komunikat_bledu = $blad;
            }
        }

        // ##############################################################################
        // platnosc PayU - REST
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'payu_rest' ) {

            if ( isset($_SESSION['platnoscElektroniczna']['error']) ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                $blad = PlatnosciElektroniczne::payu_tablicaBledow($_SESSION['platnoscElektroniczna']['error']);
                $komunikat_bledu = $blad;

            } else {

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            }

        }

        // ##############################################################################
        // platnosc eservice
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'eservice' ) {
  
            if ( PlatnosciElektroniczne::HashEservice($_SESSION['platnoscElektroniczna']) == true ) {
                 //
                 Funkcje::PrzekierowanieURL('brak-strony.html');
                 //
            } else {
                 //
                 PlatnosciElektroniczne::StatusEservice($_SESSION['platnoscElektroniczna']);     
                 //
            }

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];

                $komunikat_bledu = '';
                if ( isset($_SESSION['platnoscElektroniczna']['ErrMsg']) ) {
                     $komunikat_bledu = $_SESSION['platnoscElektroniczna']['ErrMsg'];
                }
                
            }

        }        
        
        // ##############################################################################
        // platnosc PayPal
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'paypal' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                if ( isset($_SESSION['platnoscElektroniczna']['error']) ) {
                    $komunikat_bledu = $_SESSION['platnoscElektroniczna']['error'];
                }
            }
        }

        // ##############################################################################
        // platnosc Transferuj
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'transferuj' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc Tpay
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'tpay' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc CashBill
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'cashbill' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'ok' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'err' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc imoje
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'imoje' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc PayPo
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'paypo' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' || $_SESSION['platnoscElektroniczna']['status'] == 'ERR') {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }  
        
        // ##############################################################################
        // platnosc Santander raty
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'santander' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('SANTANDER');

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {

                $komentarz = '';
                $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_SANTANDER_%'";
                $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

                while ($info_p = $sql_p->fetch_assoc()) {
                    define($info_p['kod'], $info_p['wartosc']);
                }
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);


                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'];

                if ( isset($_SESSION['platnoscElektroniczna']['id_wniosku']) ) {
                    $komentarz  = $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'].': ' . $_SESSION['platnoscElektroniczna']['id_wniosku'] . '<br />';
                }
                $komentarz .= $GLOBALS['tlumacz']['DATA_REJESTRACJI_WNIOSKU_RATALNEGO'].': ' . date("d-m-Y H:i:s") . '<br />';

                if ( PLATNOSC_SANTANDER_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_SANTANDER_STATUS_ZAMOWIENIA;
                }

                $pola = array(
                        array('payment_method_array','#')
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {

                $komentarz = '';
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'];
                $komentarz       = '';

                if ( isset($_SESSION['platnoscElektroniczna']['id_wniosku']) ) {
                    $komentarz  .= $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'].': ' . $_SESSION['platnoscElektroniczna']['id_wniosku'] . '<br />';
                }
                $komentarz .= $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'].'<br />';

            }

            $pola = array(
                    array('orders_id',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmiana statusu zamowienia
            $pola = array(
                    array('orders_status',(int)$status_zamowienia_id)
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
            unset($pola);

        }

        // ##############################################################################
        // platnosc mBank RATY
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'mbank' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('MBANK');

            $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_MBANK_%'";
            $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

            while ($info_p = $sql_p->fetch_assoc()) {
                define($info_p['kod'], $info_p['wartosc']);
            }
            $GLOBALS['db']->close_query($sql_p);
            unset($zapytanie_p, $info_p, $sql_p);

            $sig = md5($_SESSION['platnoscElektroniczna']['nrwniosku'] . $_SESSION['platnoscElektroniczna']['zamowienie_id'] . PLATNOSC_MBANK_NUMER_SKLEPU);

            if ( $sig == $_SESSION['platnoscElektroniczna']['sig'] ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'] . '<br /><br />' . $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'] .': <b>'.$_SESSION['platnoscElektroniczna']['nrwniosku'].'</b>';
                $komentarz       = '';

                if ( isset($_SESSION['platnoscElektroniczna']['nrwniosku']) ) {
                    $komentarz  .= $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'].': ' . $_SESSION['platnoscElektroniczna']['nrwniosku'] . '<br />';
                }

                if ( PLATNOSC_MBANK_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_MBANK_STATUS_ZAMOWIENIA;
                }

                $pola = array(
                        array('orders_id',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmina statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                        array('payment_method_array','#'),
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);

            } else {
                Funkcje::PrzekierowanieURL('/');
            }
        }

        // ##############################################################################
        // platnosc iLeasing
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'ileasing' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('ILEASING');

            $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_ILEASING_%'";
            $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

            while ($info_p = $sql_p->fetch_assoc()) {
                define($info_p['kod'], $info_p['wartosc']);
            }
            $GLOBALS['db']->close_query($sql_p);
            unset($zapytanie_p, $info_p, $sql_p);

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'] . '<br />';
                $komentarz       = $GLOBALS['tlumacz']['WNIOSEK_RATALNY_ZLOZONY'] . ': ' . date("d-m-Y H:i:s") . '';

                if ( PLATNOSC_ILEASING_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_ILEASING_STATUS_ZAMOWIENIA;
                }

                $pola = array(
                        array('orders_id',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmina statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                        array('payment_method_array','#')
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);

            } else {
                Funkcje::PrzekierowanieURL('/');
            }
        }
        
        // ##############################################################################
        // platnosc iRaty
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'iraty' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('IRATY');

            $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_IRATY_%'";
            $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

            while ($info_p = $sql_p->fetch_assoc()) {
                define($info_p['kod'], $info_p['wartosc']);
            }
            $GLOBALS['db']->close_query($sql_p);
            unset($zapytanie_p, $info_p, $sql_p);

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'] . '<br />';
                $komentarz       = $GLOBALS['tlumacz']['WNIOSEK_RATALNY_ZLOZONY'] . ': ' . date("d-m-Y H:i:s") . '';

                if ( PLATNOSC_IRATY_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_IRATY_STATUS_ZAMOWIENIA;
                }

                $pola = array(
                        array('orders_id',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmina statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                        array('payment_method_array','#')
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);

            } else {
                Funkcje::PrzekierowanieURL('/');
            }
        }
        
        // ##############################################################################
        // platnosc Lukas RATY
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'agricole' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('LUKAS');

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {

                $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_LUKAS_%'";
                $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

                while ($info_p = $sql_p->fetch_assoc()) {
                    define($info_p['kod'], $info_p['wartosc']);
                }
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);


                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'];

                $komentarz = $GLOBALS['tlumacz']['DATA_REJESTRACJI_WNIOSKU_RATALNEGO'].': ' . date("d-m-Y H:i:s") . '<br />';

                if ( PLATNOSC_LUKAS_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_LUKAS_STATUS_ZAMOWIENIA;
                }

                $pola = array(
                        array('payment_method_array','#')
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'];
                $komentarz       = '';

                $komentarz .= $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'].'<br />';

            }

            $pola = array(
                    array('orders_id',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmaina statusu zamowienia
            $pola = array(
                    array('orders_status',(int)$status_zamowienia_id)
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
            unset($pola);
        }


        // ##############################################################################
        // platnosc Leaselink
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'leaselink' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('LEASELINK');

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {

                $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_LEASELINK_%'";
                $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

                while ($info_p = $sql_p->fetch_assoc()) {
                    define($info_p['kod'], $info_p['wartosc']);
                }
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);


                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'];

                $komentarz = $GLOBALS['tlumacz']['WNIOSEK_RATALNY_ZLOZONY'].': ' . date("d-m-Y H:i:s") . '<br />';

            }

            $pola = array(
                    array('orders_id',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

        }

        // breadcrumb
        $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

        // wyglad srodkowy
        $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

        $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
        $tpl->dodaj('__KOMUNIKAT', $komunikat);
        $tpl->dodaj('__KOMUNIKAT_BLEDU', $komunikat_bledu);

        unset($srodek, $WywolanyPlik);

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $sql);

        unset($_SESSION['platnoscElektroniczna']);

        include('koniec.php');

        if ( $_SESSION['gosc'] == '1' ) {
            unset($_SESSION['adresDostawy'], $_SESSION['adresFaktury'], $_SESSION['customer_firstname'], $_SESSION['customer_default_address_id'], $_SESSION['customer_id']);
        }

    } else {
    
        Funkcje::PrzekierowanieURL('/');
        
    }

} else {

   // jezeli powrot platnosci eservice
   if ( isset($platnosci['eservice']) && isset($_POST['HASHPARAMS']) && isset($_POST['HASHPARAMSVAL']) ) { 
     
      if ( PlatnosciElektroniczne::HashEservice($_POST) == true ) {
           //
           Funkcje::PrzekierowanieURL('brak-strony.html');
           //
      } else {
           //
           PlatnosciElektroniczne::StatusEservice($_POST);     
           //
      }

   } else {

      Funkcje::PrzekierowanieURL('/');
      
   }

}


?>