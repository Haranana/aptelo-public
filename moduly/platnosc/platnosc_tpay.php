<?php

if(!class_exists('platnosc_tpay')) {
  class platnosc_tpay {

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
    public $punkty;
    public $AuthToken;
    public $api_url;

    // class constructor
    function __construct( $parametry = array() ) {
      global $zamowienie, $Tlumaczenie;

        $Tlumaczenie          = $GLOBALS['tlumacz'];
        $this->paramatery     = $parametry;

        $this->klasa          = $this->paramatery['klasa'];
        $this->tytul          = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TYTUL'];
        $this->objasnienie    = ( isset($Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc      = $this->paramatery['sortowanie'];
        $this->wyswietl       = false;
        $this->id             = $this->paramatery['id'];
        $this->wysylka_id     = $this->paramatery['wysylka_id'];

        $this->koszty         = $this->paramatery['parametry']['PLATNOSC_KOSZT'];
        $this->koszty_minimum = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_KOSZT_MINIMUM'],'',true);

        $this->wartosc_od      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'],'',true);
        $this->wartosc_do      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'],'',true);
        $this->darmowa         = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_DARMOWA_PLATNOSC'],'',true);
        
        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];
        
        $this->ikona           = $this->paramatery['parametry']['PLATNOSC_IKONA'];
        $this->punkty          = $this->paramatery['parametry']['STATUS_PUNKTY'];

        $this->AuthToken       = '';
        $this->api_url         = '';

        unset($Tlumaczenie);

    }

    function przetwarzanie( $id_zamowienia = 0 ) {

      $wynik = array();
      
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
          $tablica_wysylek = explode(';', $_SESSION['rodzajDostawy']['dostepne_platnosci']);

          if ( in_array( $this->id, $tablica_wysylek ) ) {

            // sprawdzenie czy wartosc zamowienia miesci sie w dopuszczalnym zakresie dla danej platnosci
            if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia_koncowa, $this->wartosc_do, $this->wartosc_od) && $wartosc_zamowienia_koncowa > 0 ) {
              $this->wyswietl = true;
            }

          }

          if ( $this->wyswietl ) {
            // jezeli koszt platnosci jest okreslony wzorem, to oblicza wartosc
            if ( !is_numeric($this->koszty) && $this->koszty != '' ) {
              $koszt_platnosci = str_replace( 'x', (($wartosc_zamowienia / $_SESSION['domyslnaWaluta']['przelicznik']) / ((100 + $_SESSION['domyslnaWaluta']['marza']) / 100)), $this->koszty);
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

      } else {
          
          $koszt_platnosci = 0;
          $this->wyswietl = true;
            
      }
      
      if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' && isset($_SESSION['rodzajDostawy']['wysylka_id']) && isset($koszt_platnosci) ) {
           //
           $zapytanie_vat_wysylki = "SELECT wartosc FROM modules_shipping_params WHERE kod = 'WYSYLKA_STAWKA_VAT' and modul_id = '" . $_SESSION['rodzajDostawy']['wysylka_id'] . "'";
           $sql_wysylki = $GLOBALS['db']->open_query($zapytanie_vat_wysylki);
           $vat_wysylka = $sql_wysylki->fetch_assoc();  

           $wartosc_vat = explode('|', $vat_wysylka['wartosc']);

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

          $DostepneProdukty = array();

          $wynik = array('id' => $this->id,
                         'klasa' => $this->klasa,
                         'text' => $this->tytul,
                         'wartosc' => $koszt_platnosci,
                         'objasnienie' => $this->objasnienie,
                         'klasa' => $this->klasa,
                         'ikona' => $this->ikona,
                         'punkty' => $this->punkty,
                         'kanaly_platnosci_tekst' => '',
                         'kanal_platnosci' => ''

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

        $tekst  = '';
        $Waluta = 'PLN';
        $Jezyk = 'pl';
        $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
        $Kwota = 0;

        if ( $this->paramatery['parametry']['PLATNOSC_TPAY_SANDBOX'] == '1' ) {
            $this->api_url = 'https://openapi.sandbox.tpay.com/transactions';
            $urlAuth = 'https://openapi.sandbox.tpay.com/oauth/auth';
        } else {
            $this->api_url = 'https://api.tpay.com/transactions';
            $urlAuth = 'https://api.tpay.com/oauth/auth';
        }
        

        $DataAuth = array(
                      "client_id" => $this->paramatery['parametry']['PLATNOSC_TPAY_CLIENT_ID'],
                      "client_secret" => $this->paramatery['parametry']['PLATNOSC_TPAY_SECRET'],
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

        if ( isset($WynikAuth['access_token']) && $WynikAuth['access_token'] != '' ) {
            $this->AuthToken = $WynikAuth['access_token'];

            unset($WynikAuth, $WynikJsonAuth, $DataAuth, $urlAuth );
            
            if ( $id_zamowienia == 0 ) {          
                $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
              } else {
                $zamowienie = new Zamowienie($id_zamowienia);
            }

            if ( $id_zamowienia == 0 ) {

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

                $headers = [
                   'Content-Type: application/json',
                   'accept: application/json',
                   'Authorization: Bearer ' . $this->AuthToken
                ];

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
                             //"pay" => array(
                             //               "groupId" => 150
                             //)
                );

                $DaneWejscioweJson = json_encode($DaneWejsciowe);
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $this->api_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);


                $WynikJson = curl_exec($ch);
                curl_close($ch);
                $Wynik = json_decode($WynikJson,true);

                if ( isset($Wynik['transactionPaymentUrl']) && isset($Wynik['result']) && $Wynik['result'] == 'success' ) {

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
        } else {
            $tekst = 'Niestety, nie udało się wygenerować płatności';
        }

        return $tekst;

    }

    function getFinancialProducts() {
 
      $url = $this->api_url . "paymethods";

      $headers = array(
        "Cache-Control: no-cache",
        "Authorization: Bearer " . $this->AuthToken,
      );

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);

      $Wynik = curl_exec($ch);

      curl_close($ch);

      return $Wynik;

    }
  }
}

?>