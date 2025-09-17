<?php

if(!class_exists('platnosc_przelewy24')) {
  class platnosc_przelewy24 {

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

          // sprawdzenie czy dana platnosc jest dostepna dla wybranego rodzaju dostawy
          $tablica_wysylek = explode(';', (string)$_SESSION['rodzajDostawy']['dostepne_platnosci']);

          if ( in_array( $this->id, $tablica_wysylek ) ) {

            // sprawdzenie czy wartosc zamowienia miesci sie w dopuszczalnym zakresie dla danej platnosci
            if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia, $this->wartosc_do, $this->wartosc_od) ) {
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


        $tekst  = '';
        $Waluta = 'PLN';
        $Jezyk = 'pl';
        $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
        $Kwota = 0;
        $KanalyPlatnosci = 0;

        $secretId         = $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_API_KEY'];
        $posId            = $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_ID'];

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

        $Waluta = strtoupper((string)$zamowienie->info['waluta']);

        if ( $id_zamowienia == 0 ) {

            $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

            $parameters                         = array();

            $kwota                              = number_format(($zamowienie->info['wartosc_zamowienia_val']) * 100, 0, "", "");

            $p24_session_id                     = session_id() . '-'. substr(md5(time()), 16);

            $sign   = array(
                'sessionId'  => (string)$p24_session_id,
                'merchantId' => (int)$this->paramatery['parametry']['PLATNOSC_PRZELEWY24_ID'],
                'amount'     => (int)$kwota,
                'currency'   => (string)$Waluta,
                'crc'        => (string)$this->paramatery['parametry']['PLATNOSC_PRZELEWY24_CRC'],
            );
            $string     = json_encode( $sign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            $p24_sign   = hash( 'sha384', $string );

            $KanalyPlatnosciArr = explode(';', $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_CHANNEL']);
            $KanalyPlatnosci = array_sum($KanalyPlatnosciArr);
            $LimitCzasu = 0;

            $DaneWejsciowe = array(
                          "merchantId"     => (int)$this->paramatery['parametry']['PLATNOSC_PRZELEWY24_ID'],
                          "posId"          => (int)$this->paramatery['parametry']['PLATNOSC_PRZELEWY24_ID'],
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
                          "method"         => null,
                          "urlStatus"      => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/przelewy24/raport.php',
                          "urlReturn"      => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=przelewy24&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                          "timeLimit"      => (int)$LimitCzasu,
                          "waitForResult"  => true,
                          "transferLabel"  => 'Nr zamowienia: ' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                          "sign"           => (string)$p24_sign,
                          "encoding"       => 'UTF-8'
            );

            if ( $KanalyPlatnosci > 0 ) {
                $DaneWejsciowe['channel']  = (int)$KanalyPlatnosci;
            }

            $headers = [
                'Content-Type: application/json'
            ];

            $data_json = json_encode($DaneWejsciowe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, "https://" . ( $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_SANDBOX'] == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/transaction/register");
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
                $parametry                  = serialize($Wynik);

                $pola = array(
                         array('payment_method_array',$parametry),
                         array('p24_session_id',$p24_session_id)
                );

                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                unset($pola);

                $tekst .= '<div style="text-align:center;padding:5px;">';
                $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="https://'.( $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_SANDBOX'] == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ).'/trnRequest/'.$Wynik->data->token.'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                    $tekst .= '   <div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}</div>';
                }
                $tekst .= '</div>';

            }

        }

        return $tekst;
    }

  }


}
?>