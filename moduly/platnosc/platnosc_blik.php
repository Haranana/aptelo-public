<?php

if(!class_exists('platnosc_blik')) {
  class platnosc_blik {

    public $paramatery;
    public $klasa;
    public $tytul;
    public $objasnienie;
    public $kolejnosc;
    public $ikona;
    public $wyswietl;
    public $id;
    public $wysylka_id;
    public $koszty;
    public $koszty_minimum;
    public $wartosc_od;
    public $wartosc_do;
    public $darmowa;
    public $tekst_info;
    public $txtguzik;
    public $punkty;
    public $system_platnosci;
    public $pokaz_platnosc;
    public $pokaz_platnosc_wartosc;
    public $id_platnosci;

    // class constructor
    function __construct( $parametry = array() ) {
      global $zamowienie, $Tlumaczenie;
      
        $Tlumaczenie          = $GLOBALS['tlumacz'];
        $this->paramatery     = $parametry;

        $this->klasa          = $this->paramatery['klasa'];
        $this->tytul          = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TYTUL'];
        $this->objasnienie    = ( isset($Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc      = $this->paramatery['sortowanie'];
        $this->ikona          = '';
        $this->wyswietl       = false;
        $this->id             = $this->paramatery['id'];
        $this->wysylka_id     = $this->paramatery['wysylka_id'];

        $this->koszty         = $this->paramatery['parametry']['PLATNOSC_KOSZT'];
        $this->koszty_minimum = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_KOSZT_MINIMUM'],'',true);

        $this->wartosc_od      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'],'',true);
        $this->wartosc_do      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'],'',true);
        $this->darmowa         = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_DARMOWA_PLATNOSC'],'',true);

        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];

        $this->txtguzik        = $Tlumaczenie['PRZYCISK_POWROT_DO_SKLEPU'];
        
        $this->ikona           = $this->paramatery['parametry']['PLATNOSC_IKONA'];
        $this->punkty          = $this->paramatery['parametry']['STATUS_PUNKTY'];
        
        $this->system_platnosci = $this->paramatery['parametry']['PLATNOSC_BLIK_SYSTEM_PLATNOSCI'];

        $this->pokaz_platnosc  = false;
        $this->pokaz_platnosc_wartosc  = false;
        $this->id_platnosci    = '0';

        $zapytanie = "SELECT id, klasa FROM modules_payment WHERE status = '1'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            if ( $info['klasa'] == $this->system_platnosci ) {
                $this->pokaz_platnosc  = true;
                $this->id_platnosci    = $info['id'];
            }
        }
        $GLOBALS['db']->close_query($sql);

        unset($zapytanie, $info, $sql);
        unset($Tlumaczenie);

    }

    function przetwarzanie( $id_zamowienia = 0 ) {

      $wynik = array();
      $DostepnoscBlik = array();
      
      if ( $id_zamowienia == 0 ) {
        
          $this->wyswietl = false;
        
          // ustalenie wartosci zamowienia
          $wartosc_zamowienia = 0;
          $wartosc_produktow = 0;
          foreach ( $_SESSION['koszyk'] as $rekord ) {
            $wartosc_zamowienia += $rekord['cena_brutto']*$rekord['ilosc'];
            $wartosc_produktow += $rekord['cena_brutto']*$rekord['ilosc'];
          }
          if ( is_numeric($_SESSION['rodzajDostawy']['wysylka_koszt']) ) {
               $wartosc_zamowienia += $GLOBALS['waluty']->PokazCeneBezSymbolu($_SESSION['rodzajDostawy']['wysylka_koszt'], '' ,true);
          }
          
          $podsumowanieTmp = new Podsumowanie();
          $wartosc_zamowienia_koncowa = 0;
          
          foreach ( $podsumowanieTmp->podsumowanie as $podsum ) {
              //
              if ( isset($podsum['klasa']) && $podsum['klasa'] == 'ot_total' ) {
                   $wartosc_zamowienia_koncowa = (float)$podsum['wartosc'];
              }
              //
          }

          // sprawdzenie czy dana platnosc jest dostepna dla wybranego rodzaju dostawy
          $tablica_wysylek = explode(';', (string)$_SESSION['rodzajDostawy']['dostepne_platnosci']);

          if ( in_array( $this->id, $tablica_wysylek ) ) {
            if ( (!isset($_SESSION['blik_min']) || $_SESSION['blik_min'] == '' ) && (!isset($_SESSION['blik_max']) || $_SESSION['blik_max'] == '' ) && (!isset($_SESSION['blik_status']) || $_SESSION['blik_status'] == '' ) ) {

                if ( $this->system_platnosci == 'platnosc_payu_rest' ) {
                    $DostepnoscBlik = $this->dostepnoscPayUREST();
                } elseif ( $this->system_platnosci == 'platnosc_payu' ) {
                    $DostepnoscBlik = $this->dostepnoscPayU();
                } elseif ( $this->system_platnosci == 'platnosc_paynow' ) {
                    $DostepnoscBlik = $this->dostepnoscPayNow();
                } elseif ( $this->system_platnosci == 'platnosc_tpay' ) {
                    $DostepnoscBlik = $this->dostepnoscTpay();
                } elseif ( $this->system_platnosci == 'platnosc_przelewy24' ) {
                    $DostepnoscBlik = $this->dostepnoscPrzelewy24($wartosc_zamowienia);
                }

                if ( count($DostepnoscBlik) > 0 ) {
                    if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia, $_SESSION['blik_max'], $_SESSION['blik_min']) ) {
                      $this->pokaz_platnosc_wartosc = true;
                    }
                }

            } else {

                if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia, $_SESSION['blik_max'], $_SESSION['blik_min']) ) {
                    $this->pokaz_platnosc_wartosc = true;
                }

            }

            if ( isset($_SESSION['blik_status']) && $_SESSION['blik_status'] == false ) {
                $this->pokaz_platnosc_wartosc = false;
                $this->pokaz_platnosc = false;
            }

            // sprawdzenie czy wartosc zamowienia miesci sie w dopuszczalnym zakresie dla danej platnosci
            if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia_koncowa, $this->wartosc_do, $this->wartosc_od) && $wartosc_zamowienia_koncowa > 0 ) {
              $this->wyswietl = true;
            }

          }

          if ( $this->wyswietl ) {
            // jezeli koszt platnosci jest okreslony wzorem, to oblicza wartosc
            if ( !is_numeric($this->koszty) && $this->koszty != '' ) {
              $koszt_platnosci = str_replace( 'x', (($wartosc_zamowienia / $_SESSION['domyslnaWaluta']['przelicznik']) / ((100 + $_SESSION['domyslnaWaluta']['marza']) / 100)), (string)$this->koszty);
              $koszt_platnosci = Funkcje::obliczWzor($koszt_platnosci);
              if ( $GLOBALS['waluty']->PokazCeneBezSymbolu($koszt_platnosci,'',true) < $this->koszty_minimum ) {
                $koszt_platnosci = $this->koszty_minimum;
              }
            } else {
              $koszt_platnosci = $this->koszty;
            }
            
          }
          
          // darmowy koszt
          if ( $wartosc_produktow >= $this->darmowa && (float)$this->darmowa > 0 ) {
               $koszt_platnosci = 0;
          }
          
          if ( $this->pokaz_platnosc == false && $this->pokaz_platnosc_wartosc == false ) {
            $this->wyswietl = false;
          }
          
      } else {
          
          $koszt_platnosci = 0;
          $this->wyswietl = true;
            
          if ( $this->pokaz_platnosc == false && $this->pokaz_platnosc_wartosc == false ) {
            $this->wyswietl = false;
          }
      }
      
      if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' && isset($_SESSION['rodzajDostawy']['wysylka_id']) && isset($koszt_platnosci) ) {
           //
           $zapytanie_vat_wysylki = "SELECT wartosc FROM modules_shipping_params WHERE kod = 'WYSYLKA_STAWKA_VAT' and modul_id = '" . $_SESSION['rodzajDostawy']['wysylka_id'] . "'";
           $sql_wysylki = $GLOBALS['db']->open_query($zapytanie_vat_wysylki);
           $vat_wysylka = $sql_wysylki->fetch_assoc();  

           $wartosc_vat = explode('|', (string)$vat_wysylka['wartosc']);

           // obliczy netto platnosci
           if ( isset($wartosc_vat[0]) && (float)$wartosc_vat[0] > 0 ) {
                $koszt_platnosci = $koszt_platnosci / ((100 + (float)$wartosc_vat[0]) / 100);
           }
           //
           $GLOBALS['db']->close_query($sql_wysylki);         
           unset($zapytanie_vat_wysylki, $vat_wysylka);           
           //
      }      
 
      if ( $this->wyswietl == true ) {
        
          $wynik = array('id' => $this->id,
                         'klasa' => $this->klasa,
                         'text' => $this->tytul,
                         'wartosc' => $koszt_platnosci,
                         'objasnienie' => $this->objasnienie,
                         'klasa' => $this->klasa,
                         'ikona' => $this->ikona,
                         'punkty' => $this->punkty
          );
  
      }
      
      return $wynik;
      
    }

    function potwierdzenie() {

        $tekst = '';

        $tekst .= '
                  <div id="PlatnoscText">'.$this->tekst_info.'</div>
                  <div><textarea name="platnosc_info" id="platnoscInfo" style="display:none;" >'.$this->tekst_info.'</textarea></div>';


        if ( isset($_SESSION['rodzajPlatnosci']['opis']) ) {
            unset($_SESSION['rodzajPlatnosci']['opis']);
        }
        $_SESSION['rodzajPlatnosci']['opis'] = $this->tekst_info;

        return $tekst;
    }

    function podsumowanie( $id_zamowienia = 0 ) {

        $tekst = '';
        $Waluta = 'PLN';
        $Jezyk = 'pl';
        $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
        $Kwota = 0;

        $zapytanie = "SELECT * FROM modules_payment_params WHERE modul_id = '".$this->id_platnosci."'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            if (!defined('BLIK_'.$info['kod'])) {
                define('BLIK_'.$info['kod'], $info['wartosc']);
            }
        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        if ( $id_zamowienia == 0 ) {

            $tekst .= '<div style="text-align:center;padding:5px;">';

            //#######################################################
            // Platnosc poprzez PayU REST
            if ( $this->system_platnosci == 'platnosc_payu_rest' ) {

                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                if ( BLIK_PLATNOSC_PAYU_REST_SANDBOX == '1' ) {
                    $api_url = 'https://secure.snd.payu.com/api/v2_1/';
                    $urlAuth = 'https://secure.snd.payu.com/pl/standard/user/oauth/authorize';
                } else {
                    $api_url = 'https://secure.payu.com/api/v2_1/';
                    $urlAuth = 'https://secure.payu.com/pl/standard/user/oauth/authorize';
                }
        
                // pobranie tokena
                $AuthTokenTab = array();
                $AuthToken = '';

                if ( $_SESSION['domyslnaWaluta']['kod'] == 'EUR' && BLIK_PLATNOSC_PAYU_REST_OAUTH_ID_EUR != '' && BLIK_PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR != '' ) {
                    $par_token = 'grant_type=client_credentials&client_id='.BLIK_PLATNOSC_PAYU_REST_OAUTH_ID_EUR.'&client_secret='.BLIK_PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR;
                } else {
                    $par_token = 'grant_type=client_credentials&client_id='.BLIK_PLATNOSC_PAYU_REST_OAUTH_ID.'&client_secret='.BLIK_PLATNOSC_PAYU_REST_OAUTH_SECRET;
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlAuth);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      
                $result = curl_exec($ch);
                curl_close($ch);

                $AuthTokenTab = json_decode($result);

                if ( !isset($AuthTokenTab->error) ) {
                    $AuthToken = $AuthTokenTab->access_token;

                    unset($result, $par_token, $urlAuth, $AuthTokenTab);

                    // generowanie platnosci
                    if ( strtoupper((string)$zamowienie->info['waluta']) == 'EUR' && BLIK_PLATNOSC_PAYU_REST_POS_ID_EUR != '' ) {
                        $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
                        $Waluta = 'EUR';
                    } else {
                        if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                            $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
                        } else {
                            // sprawdzenie marzy
                            $marza = 1;
                            if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                                $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                            }
                            if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                                $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza) * 100, 0, "", "");
                            } else {
                                $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza) * 100, 0, "", "");
                            }
                        }
                    }

                    $headers = [
                              'Content-Type: application/json',
                              'Authorization: Bearer ' . $AuthToken
                    ];

                    $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', $zamowienie->klient['nazwa']));
                    
                    $Produkty = array();

                    foreach ( $zamowienie->produkty as $Produkt ) {

                        $CenaProduktu = $Produkt['cena_koncowa_brutto'];

                        if ( strtoupper((string)$zamowienie->info['waluta']) == 'EUR' && BLIK_PLATNOSC_PAYU_REST_POS_ID_EUR != '' && BLIK_PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR != '' ) {
                            $CenaProduktu = $Produkt['cena_koncowa_brutto'];
                            $Waluta = 'EUR';
                        } else {
                            if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                                $CenaProduktu = $Produkt['cena_koncowa_brutto'];
                            } else {
                                // sprawdzenie marzy
                                $marza = 1;
                                if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                                    $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                                }
                                if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                                    $CenaProduktu = (($Produkt['cena_koncowa_brutto'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza);
                                } else {
                                    $CenaProduktu = (($Produkt['cena_koncowa_brutto'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza);
                                }
                            }
                        }

                        $Produkty[] = array("name"      => ( $Produkt['nazwa'] != '' ? $Produkt['nazwa'] : 'Produkt' ),
                                        "unitPrice" => (int)number_format($CenaProduktu, 2, ".", "") * 100,
                                        "quantity"  => $Produkt['ilosc']);
                    }
                
                    $DaneWejsciowe = array(
                              "notifyUrl"     => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/payu_rest/raport.php',
                              "customerIp"    => $_SERVER["REMOTE_ADDR"],
                              "merchantPosId" => ( $zamowienie->info['waluta'] == 'EUR' && BLIK_PLATNOSC_PAYU_REST_POS_ID_EUR != '' && BLIK_PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR != '' ? BLIK_PLATNOSC_PAYU_REST_POS_ID_EUR : BLIK_PLATNOSC_PAYU_REST_POS_ID ),
                              "extOrderId"    => (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . ':' . time(),
                              "description"   => 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "currencyCode"  => $Waluta,
                              "totalAmount"   => (int)$Kwota,
                              "continueUrl"   => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=payu_rest&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "validityTime"  => '86400',
                              "buyer" => array("email"     => trim($zamowienie->klient['adres_email']),
                                               "phone"     => trim($zamowienie->klient['telefon']),
                                               "firstName" => (($id_zamowienia == 0) ? trim($_SESSION['adresDostawy']['imie']) : trim($PodzielImieNazwisko[0])),
                                               "lastName"  => (($id_zamowienia == 0) ? trim($_SESSION['adresDostawy']['nazwisko']) : trim($PodzielImieNazwisko[count($PodzielImieNazwisko)-1])),
                                               "language"  => $Jezyk
                                              ),
                              "products" => $Produkty,
                              "payMethods" => array("payMethod" => array(
                                                        "type" => "PBL",
                                                        "value" => "blik"
                                                        )
                                              )
                             );

                    unset($PodzielImieNazwisko, $Produkty);

                    $DaneWejscioweJson = json_encode($DaneWejsciowe);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_URL, $api_url . "orders");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);    
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                    $WynikJson = curl_exec($ch);
                    curl_close($ch);

                    $Wynik = json_decode($WynikJson,true);

                    if ( isset($Wynik['redirectUri']) && isset($Wynik['status']) && $Wynik['status']['statusCode'] == 'SUCCESS' ) {

                        $Wynik['platnosc_id']     = $this->id_platnosci;
                        $Wynik['platnosc_system'] = $this->system_platnosci;

                        $parametry                      = serialize($Wynik);

                        $pola = array(
                                 array('payment_method_array',$parametry)
                        );

                        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                        unset($pola);

                        $tekst .= '<div style="text-align:center;padding:5px;">';
                        $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                        $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$Wynik['redirectUri'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                        if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                            $tekst .= '   <div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}</div>';
                        }
                        $tekst .= '</div>';

                    } else {

                        $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
                        if ( isset($Wynik['status']) && isset($Wynik['status']['statusDesc']) ) {
                            $tekst .= ' : ' . $Wynik['status']['statusDesc'] . '<br />';
                        }

                    }
                } else {

                    $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';

                }

            }

            //#######################################################
            // Platnosc poprzez PayNow
            if ( $this->system_platnosci == 'platnosc_paynow' ) {

                $sygnatura = '';

                $api_key         = BLIK_PLATNOSC_PAYNOW_API_KEY;
                $signature_key   = BLIK_PLATNOSC_PAYNOW_SIGNATURE_KEY;
                $idempotency_key = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . '-' . time();

                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                if ( $zamowienie->info['waluta'] == 'PLN' ) {

                    $kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");

                } else {

                    $przelicznikWaluty = $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'];
                    $kwota = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $przelicznikWaluty) * 100, 0, "", "");

                }

                $opis = 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
                $adres_email = $zamowienie->klient['adres_email'];

                $buyer = array("email" => $adres_email,
                               "locale" => (string)$Jezyk
                               );

                $data = array("amount" => (int)$kwota,
                              "currency"   => (string)$Waluta,
                              "externalId" => (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "description" => $opis,
                              "continueUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paynow&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "buyer" => $buyer,
                              "paymentMethodId" => '2007'
                              );

                $signatureBody = [
                                'headers' => [
                                    'Api-Key' => $api_key,
                                    'Idempotency-Key' => $idempotency_key,
                                ],
                                'parameters' => $parsedParameters ?: new \stdClass(),
                                'body' => $data ? json_encode($data, JSON_UNESCAPED_SLASHES) : ''
                ];

                $sygnatura = base64_encode(hash_hmac('sha256', json_encode($signatureBody, JSON_UNESCAPED_SLASHES), $signature_key, true));

                $headers = [
                        'Content-Type: application/json',
                        'Api-Key: ' . $api_key . '',
                        'Signature: ' . (string)$sygnatura . '',
                        'Idempotency-Key: ' . (string)$idempotency_key . '' 
                ];

                $ch = curl_init();

                curl_setopt_array($ch, array(
                  CURLOPT_URL => 'https://' . ( BLIK_PLATNOSC_PAYNOW_SANDBOX == "1" ? "api.sandbox.paynow.pl" : "api.paynow.pl" ) . '/v3/payments',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                  CURLOPT_HTTPHEADER => $headers,
                ));


                $wynik_json = curl_exec($ch);
                curl_close($ch);

                $wynik = json_decode($wynik_json,true);

                if ( !isset($wynik['errors']) && isset($wynik['redirectUrl']) && isset($wynik['status']) && $wynik['status'] == 'NEW' ) {

                    $wynik['platnosc_id']     = $this->id_platnosci;
                    $wynik['platnosc_system'] = $this->system_platnosci;

                    $parametry                      = serialize($wynik);

                    $pola = array(
                            array('payment_method_array',$parametry),
                            array('paynow_idempotency',$idempotency_key)
                    );

                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                    unset($pola);

                    $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                    $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$wynik['redirectUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                    if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                        $tekst .= '   <div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}</div>';
                    }

                } else {

                    $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}' . '<br />';

                    if ( isset($wynik['errors']) && isset($wynik['errors']) > 0 ) {
                        foreach ( $wynik['errors'] as $blad ) {
                            $tekst .= $blad['message'] . '<br />';
                        }
                    }
                }

            }

            //#######################################################
            // Platnosc poprzez PayU Classic
            if ( $this->system_platnosci == 'platnosc_payu' ) {

                $PosId                         = BLIK_PLATNOSC_PAYU_POS_ID;
                $PosAuthKey                    = BLIK_PLATNOSC_PAYU_POS_AUTH_KEY;
                $PosPayuKey1                   = BLIK_PLATNOSC_PAYU_KEY_2;
                $session_id                     = session_id() . '-' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . '-'. substr(md5(time()), 16);

                $sygnatura                      = '';
                $ts                             = time();
                $parameters                     = array();

                $parameters['rodzaj_platnosci'] = 'payu';
                $parameters['waluta']           = $Waluta;

                $parameters['pos_id']           = $PosId;
                $parameters['session_id']       = $session_id;
                $parameters['pos_auth_key']     = $PosAuthKey;
                $parameters['pay_type']         = 'blik';
        
                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                    $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
                } else {
                    // sprawdzenie marzy
                    $marza = 1;
                    if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                        $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                    }
                    if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                        $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza) * 100, 0, "", "");
                    } else {
                        $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza) * 100, 0, "", "");
                    }
                }

                $parameters['amount']           = (int)$Kwota;

                $parameters['desc']             = 'Klient: ' . trim((string)preg_replace('/\s+/', ' ', (string)$zamowienie->klient['nazwa']));
                $parameters['desc2']            = 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
                $parameters['order_id']         = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
                
                $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', (string)$zamowienie->klient['nazwa']));
                
                $parameters['first_name']       = (($id_zamowienia == 0) ? trim((string)$_SESSION['adresDostawy']['imie']) : trim((string)$PodzielImieNazwisko[0]));
                $parameters['last_name']        = (($id_zamowienia == 0) ? trim((string)$_SESSION['adresDostawy']['nazwisko']) : trim((string)$PodzielImieNazwisko[count($PodzielImieNazwisko)-1]));
                
                unset($PodzielImieNazwisko);
                
                $parameters['street']           = trim((string)$zamowienie->platnik['ulica']);
                $parameters['city']             = trim((string)$zamowienie->platnik['miasto']);
                $parameters['post_code']        = trim((string)$zamowienie->platnik['kod_pocztowy']);
                $parameters['country']          = Funkcje::kodISOKrajuDostawy( (($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']) );
                $parameters['email']            = trim((string)$zamowienie->klient['adres_email']);
                $parameters['phone']            = trim((string)$zamowienie->klient['telefon']);
                $parameters['language']         = $Jezyk;
                $parameters['client_ip']        = $_SERVER["REMOTE_ADDR"];
                $parameters['ts']               = $ts;

                ksort($parameters);

                foreach ( $parameters as $key => $value ) {
                    if ( $key != 'rodzaj_platnosci' && $key != 'waluta' ) {
                        $sygnatura .= $key . '=' . urlencode($value) . '&';
                    }
                }
                $sygnatura .= $PosPayuKey1;

                $parameters['sig']              = hash('sha256', $sygnatura);

                $formularz = '';

                foreach ( $parameters as $key => $value ) {
                    if ( $key != 'rodzaj_platnosci' && $key != 'waluta' ) {
                        $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
                    }
                }

                $tekst .= '<form action="https://secure.payu.com/paygw/UTF/NewPayment" method="post" name="payform" class="cmxform">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
                $tekst .= $formularz;
                $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br /><br />';
                if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                    $tekst .= '   {__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}';
                }
                $tekst .= '</form>';

                $parameters['platnosc_id']      = $this->id_platnosci;
                $parameters['platnosc_system']  = $this->system_platnosci;
                $parametry                      = serialize($parameters);

                $pola = array(
                        array('payment_method_array',$parametry),
                );

                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                unset($pola);

            }

            //#######################################################
            // Platnosc poprzez Tpay
            if ( $this->system_platnosci == 'platnosc_tpay' ) {

                if ( BLIK_PLATNOSC_TPAY_SANDBOX == '1' ) {
                    $api_url = 'https://openapi.sandbox.tpay.com/transactions';
                    $urlAuth = 'https://openapi.sandbox.tpay.com/oauth/auth';
                } else {
                    $api_url = 'https://api.tpay.com/transactions';
                    $urlAuth = 'https://api.tpay.com/oauth/auth';
                }

                // pobranie tokena
                $AuthToken = '';

                $DataAuth = array(
                              "client_id" => BLIK_PLATNOSC_TPAY_CLIENT_ID,
                              "client_secret" => BLIK_PLATNOSC_TPAY_SECRET,
                              "scope" => ''
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_URL, $urlAuth);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($DataAuth));
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      
                $WynikJsonAuth = curl_exec($ch);
                curl_close($ch);

                $WynikAuth = json_decode($WynikJsonAuth,true);

                $AuthToken = $WynikAuth['access_token'];

                $headers = [
                   'Content-Type: application/json',
                   'accept: application/json',
                   'Authorization: Bearer ' . $AuthToken
                ];

                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                    $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'], 2, ".", "");
                } else {
                    // sprawdzenie marzy
                    $marza = 1;
                    if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                        $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                    }
                    if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                        $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza), 2, ".", "");
                    } else {
                        $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza), 2, ".", "");
                    }
                 }

                 $DaneWejsciowe = array(
                             "amount" => $Kwota,
                             "description" => 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                             "hiddenDescription" => 'zam_'.($id_zamowienia == 0 ? (string)$_SESSION['zamowienie_id'] : (string)$id_zamowienia),
                             "lang" => $Jezyk,
                             "payer" => array(
                                             "email" => trim($zamowienie->klient['adres_email']),
                                             "name" => $zamowienie->klient['nazwa'],
                                             "phone" => trim($zamowienie->klient['telefon']),
                                             "address" => $zamowienie->platnik['ulica'],
                                             "code" => $zamowienie->platnik['kod_pocztowy'],
                                             "city" => $zamowienie->platnik['miasto'],
                                             "country" => Funkcje::kodISOKrajuDostawy( (($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']) )
                             ),
                             "callbacks" => array(
                                                  "payerUrls" => array(
                                                                       "success" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=tpay&status=OK&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                                                                       "error" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=tpay&status=FAIL&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia)
                                                  ),
                                                  "notification" => array(
                                                                         "url" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/tpay/raport.php',
                                                                         "email" => INFO_EMAIL_SKLEPU
                                                  )
                              ),
                             "pay" => array(
                                            "groupId" => 150
                             )
                 );

                 $DaneWejscioweJson = json_encode($DaneWejsciowe);
                 $ch = curl_init();

                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_URL, $api_url);
                 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);
                 curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                 $WynikJson = curl_exec($ch);
                 curl_close($ch);
                 $Wynik = json_decode($WynikJson,true);

                 if ( isset($Wynik['transactionPaymentUrl']) && isset($Wynik['result']) && $Wynik['result'] == 'success' ) {

                    $Wynik['platnosc_id']     = $this->id_platnosci;
                    $Wynik['platnosc_system'] = $this->system_platnosci;

                    $parametry                      = serialize($Wynik);

                    $pola = array(
                             array('payment_method_array',$parametry)
                    );

                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                    unset($pola);

                    $tekst .= '<div style="text-align:center;padding:5px;">';
                    $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                    $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$Wynik['transactionPaymentUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                    if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                        $tekst .= '   <div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}</div>';
                    }
                    $tekst .= '</div>';

                 } else {

                    $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
                    if ( isset($Wynik['errors']) && isset($Wynik['result']) && $Wynik['result'] == 'failed' ) {
                        foreach ( $Wynik['errors'] as $Blad ) {
                            $tekst .= ' : ' . $Blad['errorMessage'] . '<br />';
                        }
                    }

                 }

            }

            //#######################################################
            // Platnosc poprzez Przelewy24
            if ( $this->system_platnosci == 'platnosc_przelewy24' ) {

                $Waluta = 'PLN';
                $Jezyk = 'pl';
                $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
                $Kwota = 0;
                $KanalyPlatnosci = 0;

                $secretId         = BLIK_PLATNOSC_PRZELEWY24_API_KEY;
                $posId            = BLIK_PLATNOSC_PRZELEWY24_ID;

                $Waluta = strtoupper((string)$zamowienie->info['waluta']);

                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                $parameters                         = array();

                $kwota                              = number_format(($zamowienie->info['wartosc_zamowienia_val']) * 100, 0, "", "");

                $p24_session_id                     = session_id() . '-'. substr(md5(time()), 16);

                $sign   = array(
                    'sessionId'  => (string)$p24_session_id,
                    'merchantId' => (int)BLIK_PLATNOSC_PRZELEWY24_ID,
                    'amount'     => (int)$kwota,
                    'currency'   => (string)$Waluta,
                    'crc'        => (string)BLIK_PLATNOSC_PRZELEWY24_CRC,
                );
                $string     = json_encode( $sign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                $p24_sign   = hash( 'sha384', $string );

                $LimitCzasu = 0;

                $DaneWejsciowe = array(
                              "merchantId"     => (int)BLIK_PLATNOSC_PRZELEWY24_ID,
                              "posId"          => (int)BLIK_PLATNOSC_PRZELEWY24_ID,
                              "sessionId"      => (string)$p24_session_id,
                              "amount"         => (int)$kwota,
                              "currency"       => (string)$Waluta,
                              "description"    => 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "email"          => (string)$zamowienie->klient['adres_email'],
                              "client"         => (string)( $zamowienie->platnik['nazwa'] != '' ? $zamowienie->platnik['nazwa'] : $zamowienie->klient['nazwa'] ),
                              "address"        => (string)trim($zamowienie->platnik['ulica']),
                              "zip"            => (string)trim($zamowienie->platnik['kod_pocztowy']),
                              "city"           => (string)trim($zamowienie->platnik['miasto']),
                              "country"        => Funkcje::kodISOKrajuDostawy( (($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']) ),
                              "phone"          => (string)trim($zamowienie->klient['telefon']),
                              "language"       => (string)$Jezyk,
                              "method"         => '154',
                              "urlStatus"      => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/przelewy24/raport.php',
                              "urlReturn"      => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=przelewy24&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "timeLimit"      => (int)$LimitCzasu,
                              "waitForResult"  => true,
                              "regulationAccept" => true,
                              "transferLabel"  => 'Nr zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                              "sign"           => (string)$p24_sign,
                              "encoding"       => 'UTF-8'
                );

                $headers = [
                    'Content-Type: application/json'
                ];

                $data_json = json_encode($DaneWejsciowe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "https://" . ( BLIK_PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/transaction/register");
                curl_setopt($ch, CURLOPT_USERPWD, $posId.":".$secretId);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);    
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                $WynikJson = curl_exec($ch);

                curl_close($ch);

                $Wynik = json_decode($WynikJson);

                if ( isset($Wynik->error) ) {
                    $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
                    $tekst .= ' : ' . $Wynik->error . '<br />';
                    return $tekst;
                }

                if ( isset($Wynik->data) && isset($Wynik->data->token) && $Wynik->data->token != '' ) {

                    $Wynik->p24_session_id    = $p24_session_id;
                    $Wynik->platnosc_id       = $this->id_platnosci;
                    $Wynik->platnosc_system   = $this->system_platnosci;

                    $parametry                = serialize((array)$Wynik);

                    $pola = array(
                             array('payment_method_array',$parametry),
                             array('p24_session_id',$p24_session_id)
                    );

                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                    unset($pola);

                    $tekst .= "<script>";
                    $tekst .= "$(document).ready(function() {";
                    $tekst .= "$('#link_Przelewy24').click(function(){return false; });";
                    $tekst .= "$('#regulamin_przelewy24').click(function() {";
                    $tekst .= "  if(!$(this).is(':checked')){";
                    $tekst .= "    $('#link_Przelewy24').bind('click', function(){ return false; });";
                    $tekst .= "  }else{";
                    $tekst .= "    $('#link_Przelewy24').unbind('click');";
                    $tekst .= "  }";
                    $tekst .= "});";
                    $tekst .= "});";
                    $tekst .= "</script>";

                    $tekst .= '<style>';
                    $tekst .= '.WyrazamZgode { text-align:left; width:fit-content;  margin:auto;}';
                    $tekst .= '.WyrazamZgode a { font-weight:bold; }';
                    $tekst .= '</style>';

                    $tekst .= '<div style="text-align:center;padding:5px;">';
                    $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                    $tekst .= '<div class="WyrazamZgode"><form class="cmxform"><label for="regulamin_przelewy24"><input type="checkbox" value="1" name="regulamin_przelewy24" id="regulamin_przelewy24" class="raty" />Oświadczam, że zapoznałem się z <a href="https://www.przelewy24.pl/regulamin" target="_blank">regulaminem</a> i <a href="https://www.przelewy24.pl/obowiazek-informacyjny-platnik" target="_blank">obowiązkiem informacyjnym</a> serwisu Przelewy24</a><span class="check" id="check_regulamin_przelewy24" ></span><em class="required checkreq" id="em_'.uniqid().'"></em></label></form><div id="error-potwierdzenie-raty"></div></div>';

                    $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" id="link_Przelewy24" href="https://'.(BLIK_PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ).'/trnRequest/'.$Wynik->data->token.'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';
                    $tekst .= '<div id="Wynik"></div>';

                    if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                        $tekst .= '   <div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}</div>';
                    }
                    $tekst .= '</div>';

                }

            }
            
            $tekst .= '</div>';
        }

        return $tekst;
    }

    function dostepnoscPayUREST () {

        $Przedzialy = array();

        if ( $this->id_platnosci != '0' ) {

            $zapytanie = "SELECT * FROM modules_payment_params WHERE modul_id = '".$this->id_platnosci."'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            while ($info = $sql->fetch_assoc()) {
                if (!defined('BLIK_'.$info['kod'])) {
                    define('BLIK_'.$info['kod'], $info['wartosc']);
                }
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

            if ( BLIK_PLATNOSC_PAYU_REST_SANDBOX == '1' ) {
                $api_url = 'https://secure.snd.payu.com/api/v2_1/';
                $urlAuth = 'https://secure.snd.payu.com/pl/standard/user/oauth/authorize';
            } else {
                $api_url = 'https://secure.payu.com/api/v2_1/';
                $urlAuth = 'https://secure.payu.com/pl/standard/user/oauth/authorize';
            }
            
            // pobranie tokena
            $AuthTokenTab = array();
            $AuthToken = '';

            $par_token = 'grant_type=client_credentials&client_id='.BLIK_PLATNOSC_PAYU_REST_OAUTH_ID.'&client_secret='.BLIK_PLATNOSC_PAYU_REST_OAUTH_SECRET;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlAuth);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          
            $result = curl_exec($ch);
            curl_close($ch);

            $AuthTokenTab = json_decode($result);

            if ( !isset($AuthTokenTab->error) ) {
                $AuthToken = $AuthTokenTab->access_token;

                $headers = [
                           'Content-Type: application/json',
                           'Authorization: Bearer ' . $AuthToken
                ];

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $api_url . "paymethods");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                $WynikJson = curl_exec($ch);
                curl_close($ch);

                $Wynik = json_decode($WynikJson,true);

                if ( !isset($Wynik['error']) && isset($Wynik['payByLinks']) ) {
                    foreach ( $Wynik['payByLinks'] as $Platnosc ) {
                        if ( $Platnosc['value'] == 'blik' && $Platnosc['status'] == 'ENABLED' ) {
                            $Przedzialy['min'] = round($Platnosc['minAmount']/100,0);
                            $Przedzialy['max'] = round($Platnosc['maxAmount']/100,0);
                            $_SESSION['blik_min'] = round($Platnosc['minAmount']/100,0);
                            $_SESSION['blik_max'] = round($Platnosc['maxAmount']/100,0);
                            $_SESSION['blik_status'] = true;
                        }
                    }
                } else {
                    $_SESSION['blik_status'] = false;
                }

            }
        }

        return $Przedzialy;

    }

    function dostepnoscPayU () {

        $Przedzialy = array();

        if ( $this->id_platnosci != '0' ) {

            $zapytanie = "SELECT * FROM modules_payment_params WHERE modul_id = '".$this->id_platnosci."'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            while ($info = $sql->fetch_assoc()) {
                if (!defined('BLIK_'.$info['kod'])) {
                    define('BLIK_'.$info['kod'], $info['wartosc']);
                }
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

            $headers = [
                           'Content-Type: application/json',
            ];

            $url = 'https://secure.payu.com/paygw/UTF/xml/'.BLIK_PLATNOSC_PAYU_POS_ID.'/'.substr(BLIK_PLATNOSC_PAYU_KEY_1, 0, 2).'/paytype.xml';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $WynikXML = curl_exec($ch);
            curl_close($ch);

            $WynikXML = simplexml_load_string($WynikXML);
            $WynikJSON = json_encode($WynikXML);
            $Wynik = json_decode($WynikJSON,TRUE);

            if ( isset($Wynik['paytype']) ) {
                foreach ( $Wynik['paytype'] as $Platnosc ) {
                    if ( $Platnosc['type'] == 'blik' && $Platnosc['enable'] == 'true' ) {
                            $Przedzialy['min'] = $Platnosc['min'];
                            $Przedzialy['max'] = $Platnosc['max'];
                            $_SESSION['blik_min'] = $Platnosc['min'];
                            $_SESSION['blik_max'] = $Platnosc['max'];
                            $_SESSION['blik_status'] = true;
                    }
                }
            } else {
                $_SESSION['blik_status'] = false;
            }

        }

        return $Przedzialy;

    }

    function dostepnoscPayNow() {

        $Przedzialy = array();
        $data = array();
        $parsedParameters = array();
        
        if ( $this->id_platnosci != '0' ) {

            $zapytanie = "SELECT * FROM modules_payment_params WHERE modul_id = '".$this->id_platnosci."'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            while ($info = $sql->fetch_assoc()) {
                if (!defined('BLIK_'.$info['kod'])) {
                    define('BLIK_'.$info['kod'], $info['wartosc']);
                }
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

            $idempotency_key = md5($_SESSION['domyslnaWaluta']['kod'] . '_' . $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] . '_' . BLIK_PLATNOSC_PAYNOW_API_KEY . '_' . time());

            $signatureBody = [
                            'headers' => [
                                'Api-Key' => (string)BLIK_PLATNOSC_PAYNOW_API_KEY,
                                'Idempotency-Key' => (string)$idempotency_key,
                            ],
                            'parameters' => new stdClass(),
                            'body' => ''
            ];

            $sygnatura = base64_encode(hash_hmac('sha256', json_encode($signatureBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), (string)BLIK_PLATNOSC_PAYNOW_SIGNATURE_KEY, true));

            $headers = [
                        'Api-Key: ' . (string)BLIK_PLATNOSC_PAYNOW_API_KEY,
                        'Idempotency-Key: ' . $idempotency_key,
                        'Signature: ' . (string)$sygnatura,
                        'Accept: application/json'
            ];

            $ch = curl_init();

            curl_setopt_array($ch, array(
              CURLOPT_URL => 'https://' . ( BLIK_PLATNOSC_PAYNOW_SANDBOX == "1" ? "api.sandbox.paynow.pl" : "api.paynow.pl" ) . '/v3/payments/paymentmethods',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => $headers,
            ));

            $WynikJSON = curl_exec($ch);
            curl_close($ch);

            $Wynik = json_decode($WynikJSON,true);

            if ( !isset($Wynik['errors']) ) {
                foreach ( $Wynik as $Platnosc ) {
                    if ( $Platnosc['type'] == 'BLIK' ) {
                        foreach ( $Platnosc['paymentMethods'] as $Metoda ) {
                            if ( $Metoda['id'] == '2007' && $Metoda['status'] == 'ENABLED' ) {
                                $_SESSION['blik_min'] = 0;
                                $_SESSION['blik_max'] = 0;
                                $_SESSION['blik_status'] = true;
                            }
                        }
                    }
                }
            } else {
                $_SESSION['blik_status'] = false;
            }

        }

        return $Przedzialy;

    }  

    function dostepnoscTpay() {

        $Przedzialy = array();

        if ( $this->id_platnosci != '0' ) {

            $zapytanie = "SELECT * FROM modules_payment_params WHERE modul_id = '".$this->id_platnosci."'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            while ($info = $sql->fetch_assoc()) {
                if (!defined('BLIK_'.$info['kod'])) {
                    define('BLIK_'.$info['kod'], $info['wartosc']);
                }
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

            if ( BLIK_PLATNOSC_TPAY_SANDBOX == '1' ) {
                $api_url = 'https://openapi.sandbox.tpay.com/transactions/bank-groups';
                $urlAuth = 'https://openapi.sandbox.tpay.com/oauth/auth';
            } else {
                $api_url = 'https://api.tpay.com/transactions/bank-groups';
                $urlAuth = 'https://api.tpay.com/oauth/auth';
            }
        
            $DataAuth = array(
                      "client_id" => BLIK_PLATNOSC_TPAY_CLIENT_ID,
                      "client_secret" => BLIK_PLATNOSC_TPAY_SECRET,
                      "scope" => ''
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_URL, $urlAuth);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($DataAuth));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $WynikJsonAuth = curl_exec($ch);

            curl_close($ch);

            $WynikAuth = json_decode($WynikJsonAuth,true);

            if ( !isset($WynikAuth['error']) ) {

                $AuthToken = $WynikAuth['access_token'];

                unset($WynikAuth, $WynikJsonAuth, $headers, $DataAuth, $urlAuth );

                $headers = [
                           'accept: application/json',
                           'Authorization: Bearer ' . $AuthToken
                           ];

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $WynikJson = curl_exec($ch);

                curl_close($ch);

                $Wynik = json_decode($WynikJson,true);

                if ( isset($Wynik['result']) && $Wynik['result'] == 'success' ) {
                    if ( isset($Wynik['groups']['150']) ) {
                        $_SESSION['blik_min'] = 0;
                        $_SESSION['blik_max'] = 0;
                        $_SESSION['blik_status'] = true;
                    } else {
                        $_SESSION['blik_status'] = false;
                    }
                } else {
                    $_SESSION['blik_status'] = false;
                }

                unset($WynikJson, $headers );

            } else {

                return $Przedzialy;

            }
        }

        return $Przedzialy;

    }

    function dostepnoscPrzelewy24($Wartosc) {

        $Przedzialy = array();

        if ( $this->id_platnosci != '0' ) {

            $zapytanie = "SELECT * FROM modules_payment_params WHERE modul_id = '".$this->id_platnosci."'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            while ($info = $sql->fetch_assoc()) {
                if (!defined('BLIK_'.$info['kod'])) {
                    define('BLIK_'.$info['kod'], $info['wartosc']);
                }
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

            $secretId         = BLIK_PLATNOSC_PRZELEWY24_API_KEY;
            $posId            = BLIK_PLATNOSC_PRZELEWY24_ID;


            $headers = [
                    'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, "https://" . ( BLIK_PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/payment/methods/".$_SESSION['domyslnyJezyk']['kod']."?amount=".$Wartosc."&amp;currency=".$_SESSION['domyslnaWaluta']['kod']."");
            curl_setopt($ch, CURLOPT_USERPWD, $posId.":".$secretId);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $WynikJson = curl_exec($ch);

            curl_close($ch);

            $Wynik = json_decode($WynikJson);

            if ( isset($Wynik->error) ) {
                $Przedzialy['min'] = '0';
                $Przedzialy['max'] = '0';
                $_SESSION['blik_min'] = '0';
                $_SESSION['blik_max'] = '0';
                $_SESSION['blik_status'] = false;
            } else {
                $Przedzialy['min'] = '0';
                $Przedzialy['max'] = '99999';
                $_SESSION['blik_min'] = '0';
                $_SESSION['blik_max'] = '99999';
                $_SESSION['blik_status'] = true;
                foreach ( $Wynik->data as $kanal ) {
                    if ( $kanal->id == '154' && $kanal->status != '1' ) {
                        $_SESSION['blik_status'] = false;
                    }
                }
            }

        }

        return $Przedzialy;

    }

  }
}
?>