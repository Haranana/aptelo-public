<?php

if(!class_exists('platnosc_paynow')) {
  class platnosc_paynow {

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

        $this->txtguzik        = $Tlumaczenie['PRZYCISK_POWROT_DO_SKLEPU'];
        
        $this->ikona           = $this->paramatery['parametry']['PLATNOSC_IKONA'];
        $this->punkty          = $this->paramatery['parametry']['STATUS_PUNKTY'];
        
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
          $tablica_wysylek = explode(';', (string)$_SESSION['rodzajDostawy']['dostepne_platnosci']);

          if ( in_array( $this->id, $tablica_wysylek ) ) {

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
          
      } else {
          
          $koszt_platnosci = 0;
          $this->wyswietl = true;
            
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

        $tekst            = '';
        $sygnatura        = '';
        $Waluta           = 'PLN';
        $Kwota            = 0;
        $Jezyk            = 'pl-PL';
        $parsedParameters = array();

        $api_key         = $this->paramatery['parametry']['PLATNOSC_PAYNOW_API_KEY'];
        $signature_key   = $this->paramatery['parametry']['PLATNOSC_PAYNOW_SIGNATURE_KEY'];

        $idempotency_key = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . '-' . time();

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        $Waluta = strtoupper((string)$zamowienie->info['waluta']);


        if ( $id_zamowienia == 0 ) {


            $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

            if ( strtoupper($zamowienie->info['waluta']) == 'PLN' ) {

                $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");

            } elseif ( strtoupper($zamowienie->info['waluta']) == 'EUR' || strtoupper($zamowienie->info['waluta']) == 'USD' || strtoupper($zamowienie->info['waluta']) == 'GBP' ) {

                $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
                $Jezyk = 'en-GB';

            } else {

                $Waluta = 'PLN';

                $przelicznikWaluty = $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'];

                $marza = 1;
                if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                     $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                }

                $Kwota = number_format(($zamowienie->info['wartosc_zamowienia_val'] / ($przelicznikWaluty * $marza)) * 100, 0, "", "");

            }

            $opis = 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
            $adres_email = $zamowienie->klient['adres_email'];

            $AdresPlatnika = Funkcje::rozbijAdres(trim(str_replace(['"',"'"], " ", (string)$zamowienie->platnik['ulica'])));
            $AdresWysylki  = Funkcje::rozbijAdres(trim(str_replace(['"',"'"], " ", (string)$zamowienie->dostawa['ulica'])));

            $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', (string)$zamowienie->klient['nazwa']));

            $buyer_address = array(
                                   "billing" => array(
                                       "street" => (string)$AdresPlatnika['ulica'],
                                       "houseNumber" => (string)$AdresPlatnika['numer_domu'],
                                       "apartmentNumber" => (string)$AdresPlatnika['numer_mieszkania'],
                                       "zipcode" => PlatnosciElektroniczne::format_postcode($zamowienie->platnik['kod_pocztowy']),
                                       "city" => (string)$zamowienie->platnik['miasto'],
                                       "country" => (string)Funkcje::kodISOKrajuDostawy((($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']))
                                    ),
                                    "shipping" => array(
                                       "street" => (string)$AdresWysylki['ulica'],
                                       "houseNumber" => (string)$AdresWysylki['numer_domu'],
                                       "apartmentNumber" => (string)$AdresWysylki['numer_mieszkania'],
                                       "zipcode" => PlatnosciElektroniczne::format_postcode($zamowienie->dostawa['kod_pocztowy']),
                                       "city" => (string)$zamowienie->dostawa['miasto'],
                                       "country" => (string)Funkcje::kodISOKrajuDostawy((($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->dostawa['kraj']))
                                    )
                               );

            $buyer_addres_encoded = PlatnosciElektroniczne::urlencode_recursive($buyer_address);

            $buyer = array("email" => $adres_email,
                           "firstName" => urlencode((($id_zamowienia == 0) ? trim((string)$_SESSION['adresDostawy']['imie']) : trim((string)$PodzielImieNazwisko[0]))),
                           "lastName" => urlencode((($id_zamowienia == 0) ? trim((string)$_SESSION['adresDostawy']['nazwisko']) : trim((string)$PodzielImieNazwisko[count($PodzielImieNazwisko)-1]))),
                           "address" => $buyer_addres_encoded,
                           "locale" => (string)$Jezyk
                           );

            $data = array("amount" => (int)$Kwota,
                          "currency"   => (string)$Waluta,
                          "externalId" => (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                          "description" => $opis,
                          "continueUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paynow&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                          "buyer" => $buyer
                         );

            unset($PodzielImieNazwisko, $AdresPlatnika, $AdresWysylki);

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
              CURLOPT_URL => 'https://' . ( $this->paramatery['parametry']['PLATNOSC_PAYNOW_SANDBOX'] == "1" ? "api.sandbox.paynow.pl" : "api.paynow.pl" ) . '/v3/payments',
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

            $tekst .= '<div style="text-align:center;padding:5px;">';
            if ( !isset($wynik['errors']) && isset($wynik['redirectUrl']) && isset($wynik['status']) && $wynik['status'] == 'NEW' ) {
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
            $tekst .= '</div>';
        }

        return $tekst;
    }

  }
}
?>