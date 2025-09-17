<?php

if(!class_exists('platnosc_santander')) {
  class platnosc_santander {

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
    function __construct( $parametry ) {
      global $zamowienie, $Tlumaczenie, $numer_sklepu, $wariant_sklepu;

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
        
        $numer_sklepu    = $this->paramatery['parametry']['PLATNOSC_SANTANDER_NUMER_SKLEPU'];
        $wariant_sklepu  = $this->paramatery['parametry']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'];

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

        $tekst .= '<div class="WyrazamZgode"><label for="regulamin_santander"><input type="checkbox" value="1" name="regulamin_santander" id="regulamin_santander" class="raty" />Zapoznałem się z <a style="cursor: pointer; text-decoration: underline;" onclick="SantanderRegulamin()">procedurą udzielenia kredytu konsumenckiego na zakup towarów i usług eRaty Santander Consumer Bank</a><span class="check" id="check_regulamin_santander" ></span><em class="required checkreq" id="em_'.uniqid().'"></em></label><div id="error-potwierdzenie-raty"></div></div>';

        return $tekst;
    }

    static function GenerujKalkulator() {
      global $numer_sklepu, $wariant_sklepu;

      $wynik = '';
      $wynik .= "<script type=\"text/javascript\">";
      $wynik .= "function PoliczRateSantander(wartosc) { window.open('https://wniosek.eraty.pl/symulator/oblicz/numerSklepu/".$numer_sklepu."/wariantSklepu/".$wariant_sklepu."/typProduktu/0/wartoscTowarow/'+wartosc, 'Policz_rate', 'width=630,height=680,directories=no,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); }";
      $wynik .= "</script>";

      return $wynik;
    }

    function podsumowanie( $id_zamowienia = 0 ) {

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        $adres_klienta = array();
        $adres_klienta_local = array();

        $tekst                          = '';
        $parameters                         = array();

        $koszt_wysylki                      = 0;
        $wartosc_produktow                  = 0;
        $wartosc_rabatow                    = 0;
        $ilosc_produktow                    = 0;
        $n                                  = 1;

        $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->klient['ulica']);

        if ( isset($adres_klienta['dom']) && $adres_klienta['dom'] != '' ) {
            $adres_klienta_local  = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);
        }

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki = $koszt_wysylki + $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_subtotal' ) {
                $wartosc_produktow = $podsuma['wartosc'];
            }
            if ( $podsuma['prefix'] == '0' ) {
                $wartosc_rabatow = $wartosc_rabatow + $podsuma['wartosc'];
            }
            unset($podsuma);
        }

        $parameters['rodzaj_platnosci']     = 'santander';

        $parameters['numerSklepu']              = $this->paramatery['parametry']['PLATNOSC_SANTANDER_NUMER_SKLEPU'];
        $parameters['wariantSklepu']            = $this->paramatery['parametry']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'];
        $parameters['typProduktu']              = '0';
        $parameters['nrZamowieniaSklep']        = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);

        foreach ( $zamowienie->produkty as $produkt ) {

            $parameters['idTowaru'.$n]              = $produkt['products_id'];
            $parameters['nazwaTowaru'.$n]           = $produkt['nazwa'];
            $parameters['wartoscTowaru'.$n]         = $produkt['cena_koncowa_brutto'];
            $parameters['liczbaSztukTowaru'.$n]     = $produkt['ilosc'];
            $parameters['jednostkaTowaru'.$n]       = $GLOBALS['jednostkiMiary'][$produkt['jm']]['nazwa'];
            $ilosc_produktow += 1;

            $n++;
        }

        if ( $koszt_wysylki > 0 ) {
            $parameters['idTowaru'.$n]              = 'kosztTransportu';
            $parameters['nazwaTowaru'.$n]           = 'Koszt dostawy';
            $parameters['wartoscTowaru'.$n]         = $koszt_wysylki;
            $parameters['liczbaSztukTowaru'.$n]     = '1';
            $parameters['jednostkaTowaru'.$n]       = 'szt.';
            $ilosc_produktow += 1;
        }

        $parameters['wartoscTowarow']           = $wartosc_produktow + $koszt_wysylki - $wartosc_rabatow;
        $parameters['liczbaSztukTowarow']       = $ilosc_produktow;

        $parameters['sposobDostarczeniaTowaru'] = $zamowienie->info['wysylka_modul'];

        $parameters['char']                     = 'UTF';

        $parameters['wniosekZapisany']          = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=santander&status=OK&zamowienie_id='.(($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia).'';
        $parameters['wniosekAnulowany']         = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=santander&status=FAIL&zamowienie_id='.(($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia).'';


        $parameters['pesel']                    = '';

        $parameters['email']                = $zamowienie->klient['adres_email'];
        $parameters['telKontakt']           = $zamowienie->klient['telefon'];
        $parameters['imie']                 = $_SESSION['adresDostawy']['imie'];
        $parameters['nazwisko']             = $_SESSION['adresDostawy']['nazwisko'];

        $parameters['ulica']                = $adres_klienta['ulica'];
        $parameters['nrDomu']               = $adres_klienta_local['dom'];
        $parameters['nrMieszkania']         = $adres_klienta_local['mieszkanie'];
        $parameters['miasto']               = $zamowienie->platnik['miasto'];
        $parameters['kodPocz']              = $zamowienie->platnik['kod_pocztowy'];

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

        $tekst .= '<form action="https://wniosek.eraty.pl/formularz/" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}:<br /><br />';
        $tekst .= $formularz;
        $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_SANTANDER}" />
                   </div>
                   </form>';

        return $tekst;
    }

  
  }
}
?>