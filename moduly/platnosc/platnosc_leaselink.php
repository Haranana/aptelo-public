<?php

if(!class_exists('platnosc_leaselink')) {
  class platnosc_leaselink {

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

    function podsumowanie( $id_zamowienia = 0 ) {

        $Waluta       = 'PLN';
        $Kwota        = 0;
        $parameters   = array();
        $tekst        = '';
        $Produkty     = array();
        $koszt_wysylki= 0;
        $rabaty       = 0;

        if ( $this->paramatery['parametry']['PLATNOSC_LEASELINK_SANDBOX'] == '1' ) {
            $URL          = 'https://onlinetest.leaselink.pl/api/';
            $URLPlatnosc  = 'https://onlinetest.leaselink.pl';
        } else {
            $URL          = 'https://online.leaselink.pl/api/';
            $URLPlatnosc  = 'https://online.leaselink.pl';
        }

        $ApiKey         = $this->paramatery['parametry']['PLATNOSC_LEASELINK_APIKEY'];
        $externalID     = $this->paramatery['parametry']['PLATNOSC_LEASELINK_EXTERNALID'];

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        // Pobranie tokena
        $headers = [
                   'Content-Type: application/json',
                   ];

        $data = array("ApiKey" => $ApiKey
                   );

        $data_json = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $URL . "GetToken");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $wynik_json = curl_exec($ch);
        curl_close($ch);

        $wynik = json_decode($wynik_json,true);

        $Token = $wynik['Token'];

        unset($headers, $data, $data_json, $wynik_json, $wynik);


        // Przygotowanie danych
        $headers = [
                   'Content-Type: application/json',
                   'Authorization: Bearer ' . $Token,
                   ];
        $p = 0;

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki += $podsuma['wartosc'];
            }
            if ( $podsuma['prefix'] == '0' ) {
                $rabaty += $podsuma['wartosc'];
            }
            unset($podsuma);
        }

        foreach ( $zamowienie->produkty as $produkt ) {

            $IdKategorii        = $produkt['id_kategorii'];
            $SciezkaKategorii   = Kategorie::SciezkaKategoriiId($IdKategorii);
            $TablicaKategorii   = explode('_', $SciezkaKategorii);
            $KategoriaGlownaID  = $TablicaKategorii['0'];
            $KategoriaGlownaNazwa  = Kategorie::NazwaKategoriiId($TablicaKategorii['0']);

            $Produkty[$p] = array("Name"          => $produkt['nazwa'],
                                "Quantity"        => round($produkt['ilosc']),
                                "CategoryLevel1"  => $KategoriaGlownaNazwa,
                                "UnitNetPrice"    => $produkt['cena_koncowa_netto'],
                                "UnitGrossPrice"  => $produkt['cena_koncowa_brutto'],
                                "Tax"             => $produkt['tax_info'],
                                "UnitTaxValue"    => $produkt['cena_koncowa_brutto'] - $produkt['cena_koncowa_netto']
                          );
            $p++;
        }

        // Jezeli jest koszt dostawy to dodaje do pierwszego produktu
        if ( $koszt_wysylki > 0 ) {
            $KosztWysylkiBrutto = $koszt_wysylki;
            $KosztWysylkiNetto  = $koszt_wysylki / (1 + ($produkt['tax']/100) );
            $KosztWysylkiNetto  = number_format($KosztWysylkiNetto, 2, '.', '');

            $Produkty['0']['Name'] = $Produkty['0']['Name'] . ' + usługa dostawy';
            $Produkty['0']['UnitGrossPrice'] = $Produkty['0']['UnitGrossPrice'] + $KosztWysylkiBrutto;
            $Produkty['0']['UnitNetPrice'] = $Produkty['0']['UnitNetPrice'] + $KosztWysylkiNetto;
            $Produkty['0']['UnitTaxValue'] = $Produkty['0']['UnitGrossPrice'] - $Produkty['0']['UnitNetPrice'];

        }

        // Jezeli sa jakies rabaty to odejmuje do pierwszego produktu
        if ( $rabaty > 0 ) {
            $RabatyBrutto = $rabaty;
            $RabatyNetto  = $rabaty / (1 + ($produkt['tax']/100) );
            $RabatyNetto  = number_format($RabatyNetto, 2, '.', '');

            $Produkty['0']['Name'] = $Produkty['0']['Name'] . ' - rabaty';
            $Produkty['0']['UnitGrossPrice'] = $Produkty['0']['UnitGrossPrice'] - $RabatyBrutto;
            $Produkty['0']['UnitNetPrice'] = $Produkty['0']['UnitNetPrice'] - $RabatyNetto;
            $Produkty['0']['UnitTaxValue'] = $Produkty['0']['UnitGrossPrice'] - $Produkty['0']['UnitNetPrice'];

        }

        $data = array("ApiKey"          => $ApiKey,
                      "Email"           => trim((string)$zamowienie->klient['adres_email']),
                      "Phone"           => trim((string)$zamowienie->klient['telefon']),
                      "ExternalOrderId" => (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                      "Items"           => $Produkty,
                   );

        $data_json = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $URL . "CreateCalculation");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $wynik_json = curl_exec($ch);
        curl_close($ch);

        $wynik = json_decode($wynik_json,true);
        $parameters['rodzaj_platnosci']     = 'leaselink';

        $tekst .= '<div style="text-align:center;padding:5px;">';

        if ( isset($wynik['status']) && $wynik['status'] == '500' && $wynik['title'] != '' ) {
            $tekst .= $wynik['title'] . '<br />';
        }

        if ( isset($wynik['errors']) ) {
            $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}' . '<br />';
        }

        if ( isset($wynik['CalculationId']) && $wynik['CalculationId'] != '' ) {

            $parametry                      = serialize($wynik);

            $pola = array(
                        array('payment_method_array',$parametry)
            );

            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
            unset($pola);

            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$URLPlatnosc.$wynik['CalculationUrl'].'">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU_LEASINGOWEGO}</a>';

            if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                $tekst .= '   <div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}</div>';
            }

        }

        $tekst .= '</div>';

        return $tekst;
    }

  
  }
}
?>