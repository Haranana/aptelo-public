<?php

if(!class_exists('platnosc_lukas')) {
  class platnosc_lukas {

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
    public $kategorie;
    public $tekst_info;
    public $punkty;

    // class constructor
    function __construct( $parametry ) {
      global $zamowienie, $Tlumaczenie, $numer_sklepu;

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
        
        $numer_sklepu          = $this->paramatery['parametry']['PLATNOSC_LUKAS_NUMER_SKLEPU'];

        $this->kategorie       = $this->paramatery['parametry']['PLATNOSC_LUKAS_KATEGORIE'];

        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];
        
        $this->ikona           = $this->paramatery['parametry']['PLATNOSC_IKONA'];
        $this->punkty          = $this->paramatery['parametry']['STATUS_PUNKTY'];
        
        unset($Tlumaczenie);

    }

    function przetwarzanie( $id_zamowienia = 0 ) {

      $wynik = array();
      
      if ( $id_zamowienia == 0 ) {
        
          $this->wyswietl = false;

          // ustalenie wartosci zamowienia oraz czy w koszyku sa produkty z wykluczonych kategorii
          $wartosc_zamowienia = 0;
          $wartosc_produktow = 0;
          foreach ( $_SESSION['koszyk'] as $rekord ) {
            //wartosc zamowienia
            $wartosc_zamowienia += $rekord['cena_brutto']*$rekord['ilosc'];
            $wartosc_produktow += $rekord['cena_brutto']*$rekord['ilosc'];

            //wykluczone kategorie
            $wykluczoneKategorie = explode(',', (string)$this->kategorie);
            for ( $i=0, $x=sizeof($wykluczoneKategorie); $i<$x; $i++ ) {
                if ( $wykluczoneKategorie[$i] == $rekord['id_kategorii'] ) {
                     $this->wyswietl = false;
                     return;
                }
            }
            unset($wykluczoneKategorie);

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
              $koszt_platnosci = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->koszty,'',true);
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

    static function GenerujKalkulator() {
      global $numer_sklepu, $wariant_sklepu;

      $wynik = '';
      $wynik .= "<script type=\"text/javascript\">";
      $wynik .= "function PoliczRateLukas(wartosc) { window.open('https://wniosek.eraty.pl/symulator/oblicz/numerSklepu/".$numer_sklepu."/wariantSklepu/".$wariant_sklepu."/typProduktu/0/wartoscTowarow/'+wartosc, 'Policz_rate', 'width=630,height=680,directories=no,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); }";
      $wynik .= "</script>";

      return $wynik;
    }

    function podsumowanie( $id_zamowienia = 0 ) {
        global $numer_sklepu;

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        $tablicaCzyszczenia = array('&','>','<','"',"'");
        $tablicaOdniesien   = array('','','','',"");

        $tekst                          = '';
        $parameters                     = array();
        $towary                         = array();

        $koszt_wysylki                      = 0;
        $wartosc_produktow                  = 0;
        $wartosc_rabatu                     = 0;
        $ilosc_produktow                    = 0;
        $n                                  = 1;
        $wartosc_kredytu                    = 0;
        $randomizer                         = date("YmdHis") . rand();

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki = $koszt_wysylki + $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_subtotal' ) {
                $wartosc_produktow = $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_discount_coupon' || $podsuma['klasa'] == 'ot_loyalty_discount' || $podsuma['klasa'] == 'ot_shopping_discount' ) {
                $wartosc_rabatu = $wartosc_rabatu + $podsuma['wartosc'];
            }

            unset($podsuma);
        }
        $wartosc_kredytu                    = $wartosc_produktow + $koszt_wysylki - $wartosc_rabatu;

        $parameters['rodzaj_platnosci']            = 'agricole';

        $parameters['numer_sklepu']                = $numer_sklepu;
        $parameters['nazwa_sklepu']                = INFO_NAZWA_SKLEPU;

        $parameters['PARAM_TYPE']                  = 'RAT';
        $parameters['PARAM_PROFILE']               = $numer_sklepu;
        $parameters['POST_ATTR']                   = '1';
        $parameters['cart.shopName']               = INFO_NAZWA_SKLEPU;
        $parameters['creditInfo.creditAmount']     = $wartosc_kredytu;
        $parameters['creditInfo.creditPeriod']     = '12';

        $parameters['email.address']               = $zamowienie->klient['adres_email'];
        $parameters['cart.orderNumber']            = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
        $parameters['mailAddress.sameAsPermanent'] = 'tak';

        foreach ( $zamowienie->produkty as $produkt ) {
            if ( $n == 1 ) {
                $nazwa_produktu = trim((string)str_replace($tablicaCzyszczenia, $tablicaOdniesien, (string)$produkt['nazwa']));
                $nazwa_produktu = urlencode($nazwa_produktu);
                $cena_produktu  = $produkt['cena_koncowa_brutto'];
            }
            $parameters['cart.itemName'.$n]        = $nazwa_produktu;
            $parameters['cart.itemQty'.$n]         = number_format($produkt['ilosc'], 0, '.', '');
            $parameters['cart.itemPrice'.$n]       = $produkt['cena_koncowa_brutto'];
            $n++;
        }

        if ( $koszt_wysylki > 0 ) {
            $parameters['cart.itemName'.$n]        = 'Przesylka';
            $parameters['cart.itemQty'.$n]         = '1';
            $parameters['cart.itemPrice'.$n]       = $koszt_wysylki;
            $n++;
        }
        if ( $wartosc_rabatu > 0 ) {
            $parameters['cart.itemName'.$n]        = 'bonus';
            $parameters['cart.itemQty'.$n]         = '1';
            $parameters['cart.itemPrice'.$n]       = 0 - $wartosc_rabatu;
        }

        $parameters['pastCreditDataAgr.agreement'] = 'true';
        $parameters['marketingAgr.agreement']      = 'true';
        $parameters['emailAgr.agreement']          = 'true';
        $parameters['verificationAgr.agreement']   = 'true';
        $parameters['robinsonLBAgr.agreement']     = 'true';

        $parameters['PARAM_CREDIT_AMOUNT']         = $wartosc_kredytu;
        $parameters['PARAM_AUTH']                  = '2';
        $parameters['randomizer']                  = $randomizer;

        $hash = $numer_sklepu .'RAT' . '2' . $wartosc_kredytu . $nazwa_produktu . $cena_produktu . $randomizer . $this->paramatery['parametry']['PLATNOSC_LUKAS_HASLO'];

        $parameters['PARAM_HASH']                  = md5($hash);

        $parametry                          = serialize($parameters);

        $pola = array(
                array('payment_method_array',$parametry)
        );

        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
        unset($pola);
        
        $formularz = '';
        foreach ( $parameters as $key => $value ) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://ewniosek.credit-agricole.pl/eWniosek/simulator.jsp" method="post" name="payform" id="payform" class="cmxform">';
        $tekst .= '<div style="text-align:center;padding:5px;padding-top:15px;">';
        $tekst .= $formularz;
        $tekst .= '<input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_LUKAS}" />
                   </div>
                   </form>';

        return $tekst;
    }

  }
}
?>