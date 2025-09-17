<?php

if(!class_exists('platnosc_paypal')) {
  class platnosc_paypal {

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

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        $tekst                              = '';
        $waluta                             = $zamowienie->info['waluta'];
        $jezyk                              = strtoupper((string)$_SESSION['domyslnyJezyk']['kod']);

        $kraj_dostawy                       = Funkcje::kodISOKrajuDostawy($zamowienie->dostawa['kraj']);
        $wojewodztwo                        = Funkcje::kodWojewodztwa($zamowienie->dostawa['kraj'], $zamowienie->dostawa['wojewodztwo'], 'CA');

        $koszt_wysylki                      = 0;
        $wartosc_produktow                  = 0;
        $ilosc_produktow                    = 0;
        $n                                  = 0;
        $rabat                              = 0;

        $parameters                         = array();

        $parameters['rodzaj_platnosci'] = 'paypal';

        $parameters['VERSION']                        = '114.0'; 
        $parameters['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
        $parameters['RETURNURL']                      = ADRES_URL_SKLEPU . '/moduly/platnosc/raporty/paypal/akcja_koniec.php?zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
        $parameters['CANCELURL']                      = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paypal&status=FAIL&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
        $parameters['REQCONFIRMSHIPPING']             = '0'; 
        $parameters['NOSHIPPING']                     = '1'; 
        $parameters['ADDROVERRIDE']                   = '1';

        $parameters['LOCALECODE']                     = $jezyk;
        $parameters['HDRIMG']                         = ADRES_URL_SKLEPU . '/images/' . $this->paramatery['parametry']['PLATNOSC_PAYPAL_LOGO'];

        $parameters['METHOD']                         = 'SetExpressCheckout'; 

        $parameters['PAYMENTREQUEST_0_SHIPTONAME']        = $zamowienie->dostawa['nazwa'];
        $parameters['PAYMENTREQUEST_0_SHIPTOSTREET']      = $zamowienie->dostawa['ulica'];
        $parameters['PAYMENTREQUEST_0_SHIPTOCITY']        = $zamowienie->dostawa['miasto'];
        $parameters['PAYMENTREQUEST_0_SHIPTOSTATE']       = $wojewodztwo;
        $parameters['PAYMENTREQUEST_0_SHIPTOZIP']         = $zamowienie->dostawa['kod_pocztowy'];
        $parameters['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $kraj_dostawy;
        $parameters['PAYMENTREQUEST_0_EMAIL']             = $zamowienie->klient['adres_email'];
        $parameters['PAYMENTREQUEST_0_SHIPTOPHONENUM']    = $zamowienie->klient['telefon'];

        foreach ( $zamowienie->produkty as $produkt ) {
            $parameters['L_PAYMENTREQUEST_0_NAME'.$n]  = $produkt['nazwa']; 
            $parameters['L_PAYMENTREQUEST_0_AMT'.$n]   = $produkt['cena_koncowa_brutto']; 
            $parameters['L_PAYMENTREQUEST_0_QTY'.$n]   = round($produkt['ilosc'],0); 
            $n++;
        }

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki = $koszt_wysylki + $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_subtotal' ) {
                $wartosc_produktow = $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_total' ) {
                $wartosc_zamowienia = $podsuma['wartosc'];
            }
            if ( $podsuma['prefix'] == '0' ) {
                $parameters['L_PAYMENTREQUEST_0_NAME'.$n]  = $podsuma['tytul']; 
                $parameters['L_PAYMENTREQUEST_0_AMT'.$n]   = -$podsuma['wartosc']; 
                $parameters['L_PAYMENTREQUEST_0_QTY'.$n]   = '1'; 
                $rabat += $podsuma['wartosc'];
                $n++;
            }
            unset($podsuma);
        }

        $wartosc_produktow = $wartosc_produktow - $rabat;

        $parameters['PAYMENTREQUEST_0_AMT'] = $wartosc_zamowienia;
        $parameters['PAYMENTREQUEST_0_CURRENCYCODE'] = $waluta;

        $parameters['PAYMENTREQUEST_0_ITEMAMT'] = $wartosc_produktow; 
        $parameters['PAYMENTREQUEST_0_SHIPPINGAMT'] = $koszt_wysylki; 

        reset($parameters);
        $parametry                      = serialize($parameters);

        $formularz = '';
        foreach ( $parameters as $key => $value ) {
            $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
        }

        $tekst .= '<form action="moduly/platnosc/raporty/paypal/akcja_zaplac.php" method="post" name="payform" class="cmxform">
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