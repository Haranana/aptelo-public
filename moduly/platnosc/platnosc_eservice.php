<?php

if(!class_exists('platnosc_eservice')) {
  class platnosc_eservice {

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

        $nr_tmp = explode('-', (string)$id_zamowienia);
        
        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie((int)$nr_tmp[0]);
        }
        
        $suma_zamowienia = number_format($zamowienie->info['wartosc_zamowienia_val'], 2, ".", "");
        
        $waluta = $zamowienie->info['waluta'];
        
        $waluta_zamowienia = '985';
        
        // tablica walut
        $tablica_walut = array(array('985','PLN'),
                               array('978','EUR'),
                               array('840','USD'),
                               array('826','GBP'),
                               array('756','CHF'),
                               array('208','DKK'),
                               array('124','CAD'),
                               array('578','NOK'),
                               array('752','SEK'),
                               array('643','RUB'),
                               array('440','LTL'),
                               array('946','RON'),
                               array('203','CZK'),
                               array('392','JPY'),
                               array('348','HUF'),
                               array('191','HRK'),
                               array('980','UAH'),
                               array('949','TRY'));   

        foreach ( $tablica_walut as $tmp ) {
            //
            if ( $tmp[1] == $waluta ) {
                 $waluta_zamowienia = $tmp[0];
            }
            //
        }
        
        // pobranie tokena
        $par_token = "ClientId=" . $this->paramatery['parametry']['PLATNOSC_ESERVICE_CLIENTID'] . "&Password=" . $this->paramatery['parametry']['PLATNOSC_ESERVICE_HASLO'] . "&OrderId=" . $zamowienie->info['id_zamowienia'] . "&Total=" . $suma_zamowienia . "&Currency=" . $waluta_zamowienia;

        $ch = curl_init();
        if ( (int)$this->paramatery['parametry']['PLATNOSC_ESERVICE_SANDBOX'] == 1 ) {
             curl_setopt($ch, CURLOPT_URL, 'https://testvpos.eservice.com.pl/pg/token');
        } else {
             curl_setopt($ch, CURLOPT_URL, 'https://pay.eservice.com.pl/pg/token');
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $odpowiedz = array();
        $odpowiedz = explode('&', (string)$result);

        for ( $i=0; $i < count($odpowiedz); $i++ ) {
            $tmp = explode('=', (string)$odpowiedz[$i]);
            $odpowiedz[$i] = array($tmp[0] => ( isset($tmp[1]) ? $tmp[1] : '' ));
            unset($tmp);
        }        
        
        $wynik = array();

        foreach ( $odpowiedz as $rekord ) {
            foreach ( $rekord as $key => $value ) {
                $wynik[$key] = urldecode($value);
            }
        }        
        
        $token = '';        
        if ( isset($wynik['status']) && strtoupper((string)$wynik['status']) == 'OK' ) {      
            $token = $wynik['msg'];
        }        
        
        // jezeli jest zwrocony token
        if ( $token != '' ) {       

            $parameters['ClientId']         = $this->paramatery['parametry']['PLATNOSC_ESERVICE_CLIENTID'];
            $parameters['StoreType']        = '3d_pay_hosting';
            $parameters['Token']            = $token;
            $parameters['TranType']         = 'Auth';
            $parameters['Total']            = $suma_zamowienia;
            $parameters['Currency']         = $waluta_zamowienia;
            
            $parameters['OrderId']          = (((int)$nr_tmp[0] == 0) ? (int)$_SESSION['zamowienie_id'] : (int)$nr_tmp[0]);
            
            $PodzielImieNazwisko = explode(' ', (string)$zamowienie->klient['nazwa']);
            
            $parameters['ConsumerName']     = $PodzielImieNazwisko[0];
            $parameters['ConsumerSurname']  = $PodzielImieNazwisko[count($PodzielImieNazwisko)-1];
            
            unset($PodzielImieNazwisko);        
            
            $parameters['okUrl']            = ADRES_URL_SKLEPU . "/platnosc_koniec.php?typ=eservice&status=OK";            
            $parameters['failUrl']          = ADRES_URL_SKLEPU . "/platnosc_koniec.php?typ=eservice&status=FAIL";
            $parameters['pendingUrl']       = ADRES_URL_SKLEPU . "/platnosc_koniec.php?typ=eservice&status=OK";
            $parameters['callbackUrl']      = ADRES_URL_SKLEPU . "/moduly/platnosc/raporty/eservice/raport.php";
            
            $parameters['lang']             = (($_SESSION['domyslnyJezyk']['kod'] != 'pl') ? 'en' : 'pl');

            $parameters['hashAlgorithm']    = 'ver2';
            
            if ( $zamowienie->platnik['nazwa'] != '' ) {
                 $parameters['BillToName']       = $zamowienie->platnik['nazwa'];
            }
            if ( $zamowienie->platnik['firma'] != '' ) {
                 $parameters['BillToCompany']    = $zamowienie->platnik['firma'];
            }
            $parameters['BillToPostalCode'] = $zamowienie->platnik['kod_pocztowy'];
            $parameters['BillToStreet1']    = $zamowienie->platnik['ulica'];
            $parameters['BillToCity']       = $zamowienie->platnik['miasto'];    
            $parameters['BillToCountry']    = Funkcje::kodISOKrajuDostawy( (((int)$nr_tmp[0] == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']) );      
            
            if ( $zamowienie->dostawa['nazwa'] != '' ) {
                 $parameters['ShipToName']       = $zamowienie->dostawa['nazwa'];
            }
            if ( $zamowienie->dostawa['firma'] != '' ) {
                 $parameters['ShipToCompany']    = $zamowienie->dostawa['firma'];
            }
            $parameters['ShipToPostalCode'] = $zamowienie->dostawa['kod_pocztowy'];
            $parameters['ShipToStreet1']    = $zamowienie->dostawa['ulica'];
            $parameters['ShipToCity']       = $zamowienie->dostawa['miasto'];    
            $parameters['ShipToCountry']    = Funkcje::kodISOKrajuDostawy( (((int)$nr_tmp[0] == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->dostawa['kraj']) );        

            $parametry                      = serialize($parameters);

            $formularz = '';
            foreach ( $parameters as $key => $value ) {
                if ( $key != 'rodzaj_platnosci' ) {
                    $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
                }
            }

            $tekst = '<form action="' . (((int)$this->paramatery['parametry']['PLATNOSC_ESERVICE_SANDBOX'] == 1) ? 'https://testvpos.eservice.com.pl/fim/eservicegate' : 'https://pay.eservice.com.pl/fim/eservicegate') . '" method="post" name="eserviceform" class="cmxform">
                      <div style="text-align:center;padding:5px;">
                          {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
                          
            $tekst .= $formularz;
            
            $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br /><br />';
            
            if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && (int)$nr_tmp[0] == 0) {
                $tekst .= '   {__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}';
            }
            
            $tekst .= '</div>
                       </form>';
                       
            $pola = array(
                    array('payment_method_array',$parametry),
            );

            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (((int)$nr_tmp[0] == 0) ? (int)$_SESSION['zamowienie_id'] : (int)$nr_tmp[0]) . "'");
            unset($pola);

        } else {
          
            $tekst = '<div style="text-align:center;padding:5px;">{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}</div>';
          
        }

        return $tekst;
    }

  }
}
?>