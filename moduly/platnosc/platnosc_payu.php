<?php

if(!class_exists('platnosc_payu')) {
  class platnosc_payu {

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

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        if ( strtoupper((string)$zamowienie->info['waluta']) == 'EUR' && $this->paramatery['parametry']['PLATNOSC_PAYU_POS_ID_EUR'] != '' ) {
            $Waluta                        = 'EUR';
            $PosId                         = $this->paramatery['parametry']['PLATNOSC_PAYU_POS_ID_EUR'];
            $PosAuthKey                    = $this->paramatery['parametry']['PLATNOSC_PAYU_POS_AUTH_KEY_EUR'];
            $PosPayuKey1                   = $this->paramatery['parametry']['PLATNOSC_PAYU_KEY_2_EUR'];
        } else {
            $Waluta                        = 'PLN';
            $PosId                         = $this->paramatery['parametry']['PLATNOSC_PAYU_POS_ID'];
            $PosAuthKey                    = $this->paramatery['parametry']['PLATNOSC_PAYU_POS_AUTH_KEY'];
            $PosPayuKey1                   = $this->paramatery['parametry']['PLATNOSC_PAYU_KEY_2'];
        }

        $Kwota                          = '0';
        $tekst                          = '';
        $jezyk                          = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
        $session_id                     = session_id() . '-' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . '-'. substr(md5(time()), 16);

        $sygnatura                      = '';
        $ts                             = time();
        $parameters                     = array();

        $parameters['rodzaj_platnosci'] = 'payu';
        $parameters['waluta']           = $Waluta;

        $parameters['pos_id']           = $PosId;
        $parameters['session_id']       = $session_id;
        $parameters['pos_auth_key']     = $PosAuthKey;
        
        $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

        if ( strtoupper((string)$zamowienie->info['waluta']) == 'EUR' && $this->paramatery['parametry']['PLATNOSC_PAYU_POS_ID_EUR'] != '' ) {
            $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
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

        $parameters['amount']           = (int)$Kwota;

        $parameters['desc']             = 'Klient: ' . trim((string)preg_replace('/\s+/', ' ', str_replace(['"',"'"], " ", (string)$zamowienie->klient['nazwa'])));
        $parameters['desc2']            = 'Numer zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
        $parameters['order_id']         = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
        
        $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', (string)$zamowienie->klient['nazwa']));
        
        $parameters['first_name']       = str_replace(['"',"'"], " ", (($id_zamowienia == 0) ? trim((string)$_SESSION['adresDostawy']['imie']) : trim((string)$PodzielImieNazwisko[0])));
        $parameters['last_name']        = str_replace(['"',"'"], " ", (($id_zamowienia == 0) ? trim((string)$_SESSION['adresDostawy']['nazwisko']) : trim((string)$PodzielImieNazwisko[count($PodzielImieNazwisko)-1])));
        
        unset($PodzielImieNazwisko);
        
        $parameters['street']           = trim(str_replace(['"',"'"], " ", (string)$zamowienie->platnik['ulica']));
        $parameters['city']             = trim(str_replace(['"',"'"], " ", (string)$zamowienie->platnik['miasto']));
        $parameters['post_code']        = trim(str_replace(['"',"'"], " ", (string)$zamowienie->platnik['kod_pocztowy']));
        $parameters['country']          = Funkcje::kodISOKrajuDostawy( (($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']) );
        $parameters['email']            = trim(str_replace(['"',"'"], " ", (string)$zamowienie->klient['adres_email']));
        $parameters['phone']            = trim(str_replace(['"',"'"], " ", (string)$zamowienie->klient['telefon']));
        $parameters['language']         = $jezyk;
        $parameters['client_ip']        = $_SERVER["REMOTE_ADDR"];
        $parameters['ts']               = $ts;

        ksort($parameters);
        $emptyRemoved = array_filter($parameters);
        ksort($emptyRemoved);

        foreach ( $emptyRemoved as $key => $value ) {
            if ( $key != 'rodzaj_platnosci' && $key != 'waluta' ) {
                $sygnatura .= $key . '=' . urlencode($value) . '&';
            }
        }
        $sygnatura .= $PosPayuKey1;

        $parameters['sig']              = hash('sha256', $sygnatura);

        $parametry                      = serialize($parameters);

        $formularz = '';

        foreach ( $parameters as $key => $value ) {
            if ( $key != 'rodzaj_platnosci' && $key != 'waluta' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://secure.payu.com/paygw/UTF/NewPayment" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
        $tekst .= $formularz;
        $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br /><br />';
        if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
            $tekst .= '   {__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}';
        }
        $tekst .= '</div>
                   </form>';


        $pola = array(
                array('payment_method_array',$parametry),
        );

        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
        unset($pola);

        return $tekst;
    }

  }
}
?>