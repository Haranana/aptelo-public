<?php

if(!class_exists('platnosc_paypo')) {
  class platnosc_paypo {

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
    public $api_id;
    public $api_token;
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

        $this->txtguzik        = $Tlumaczenie['PRZYCISK_POWROT_DO_SKLEPU'];
        
        $this->ikona           = $this->paramatery['parametry']['PLATNOSC_IKONA'];
        $this->punkty          = $this->paramatery['parametry']['STATUS_PUNKTY'];

        $this->api_id          = $this->paramatery['parametry']['PLATNOSC_PAYPO_ID'];
        $this->api_token       = $this->paramatery['parametry']['PLATNOSC_PAYPO_API'];
        
        if ( $this->paramatery['parametry']['PLATNOSC_PAYPO_SANDBOX'] == '1' ) {
            $this->api_url = 'https://api.sandbox.paypo.pl/v3/';
        } else {
            $this->api_url = 'https://api.paypo.pl/v3/';
        }
        
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
      
      // tylko dla waluty PLN
      if ( $_SESSION['domyslnaWaluta']['kod'] != 'PLN' ) {
           //
           $this->wyswietl = false;
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

        $parameters                          = array();

        $tekst                               = '';
        $parameters['rodzaj_platnosci']      = 'paypo';
        
        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }
        

        $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->klient['ulica']);

        if ( isset($adres_klienta['dom']) && $adres_klienta['dom'] != '' ) {
            $adres_klienta_local  = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);
        }

        $adres_dostawy  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);

        if ( isset($adres_dostawy['dom']) && $adres_dostawy['dom'] != '' ) {
            $adres_dostawy_local  = Funkcje::PrzeksztalcAdresDomu($adres_dostawy['dom']);
        }

        // sprawdzenie marzy
        $marza = 1;
        if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
             $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
        }        
        $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

        $kwota                               = number_format(((($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']) / $marza) * 100), 0, "", "");

        // pobranie tokena - START
        $par_token = 'grant_type=client_credentials&client_id='.$this->api_id.'&client_secret='.$this->api_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url . 'oauth/tokens');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $wynik = curl_exec($ch);

        curl_close($ch);

        $AuthToken = json_decode($wynik);

        if ( isset($AuthToken->error) ) {
            return $AuthToken->error_description;
        } else {
            $token = $AuthToken->access_token;
        }

        unset($AuthToken, $par_token, $wynik);
        // pobranie tokena - KONIEC

        // pobranie linku do platnosci - START
        $headers = [
                   'Content-Type: application/json',
                   'Authorization: Bearer ' . $token
                   ];

        $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', $zamowienie->klient['nazwa']));

        $DaneWejsciowe = array(
                      "id" => Funkcje::UUIDv4(),
                      "order" => array(
                            "referenceId" => (string)(($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                            "description" => 'Zamowienie: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                            "amount" => (int)$kwota,
                            "billingAddress" => array(
                                "street" => trim($adres_klienta['ulica']),
                                "building" => trim($adres_klienta_local['dom']),
                                "flat" => trim($adres_klienta_local['mieszkanie']),
                                "zip" => trim($zamowienie->platnik['kod_pocztowy']),
                                "city" => trim($zamowienie->platnik['miasto']),
                                "country" => Funkcje::kodISOKrajuDostawy($zamowienie->platnik['kraj'])
                             ),
                            "shippingAddress" => array(
                                "street" => trim($adres_dostawy['ulica']),
                                "building" => trim($adres_dostawy_local['dom']),
                                "flat" => trim($adres_dostawy_local['mieszkanie']),
                                "zip" => trim($zamowienie->dostawa['kod_pocztowy']),
                                "city" => trim($zamowienie->dostawa['miasto']),
                                "country" => Funkcje::kodISOKrajuDostawy($zamowienie->dostawa['kraj'])
                             ),
                      ),
                      "customer" => array(
                            "name" => (($id_zamowienia == 0) ? trim($_SESSION['adresDostawy']['imie']) : trim($PodzielImieNazwisko[0])),
                            "surname" => (($id_zamowienia == 0) ? trim($_SESSION['adresDostawy']['nazwisko']) : trim($PodzielImieNazwisko[count($PodzielImieNazwisko)-1])),
                            "email" => trim($zamowienie->klient['adres_email']),
                            "phone" => trim($zamowienie->klient['telefon']),
                      ),
                      "configuration" => array(
                            "returnUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paypo&status=OK&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                            "notifyUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/paypo/raport.php',
                            "cancelUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paypo&status=FAIL&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia)
                      )
        );

        $DaneWejscioweJson = json_encode($DaneWejsciowe);
        $parameters['platnosc'] = array();
        $parameters['platnosc'] = $DaneWejsciowe;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->api_url . 'transactions');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $wynik_json = curl_exec($ch);

        curl_close($ch);

        $paypoResp = json_decode($wynik_json,true);

        if ( isset($paypoResp['code']) ) {
            $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= $paypoResp['code'] . ' : ' . $paypoResp['message'] . '<br />';
            $tekst .= '</div>';
            return $tekst;
        }

        if ( isset($paypoResp['transactionId']) && isset($paypoResp['redirectUrl']) ) {

            $linkToPayPo = "";
            $linkToPayPo = $paypoResp["redirectUrl"];
        
            $parameters['linkToPayPo'] = $linkToPayPo;
            $parameters['uuid'] = $paypoResp['transactionId'];

            $parametry                      = serialize($parameters);

            $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '   <a href="'. $linkToPayPo .'" class="przyciskZaplac" type="submit" id="submitButton">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}</a><br /><br />';

            if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                $tekst .= '   {__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}';
            }
            $tekst .= '</div>';

            $pola = array(
                    array('payment_method_array',$parametry),
            );

            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
            unset($pola);
        } else {
            $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD}' . '<br />';
            if ( isset($paypoResp['code']) ) {
                $tekst .= $paypoResp['code'] . ' : ' . $paypoResp['message'] . '<br />';
            }
       }

       return $tekst;
    }

  }


}
?>