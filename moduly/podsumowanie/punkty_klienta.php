<?php

if(!class_exists('ot_redemptions')) {
  class ot_redemptions {

    public $paramatery;
    public $tytul;
    public $sortowanie;
    public $prefix;
    public $klasa;
    public $ikona;
    public $wyswietl;
    public $id;

    var $wyjscie;

    function __construct( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];

      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_REDEMPTIONS_TYTUL'];
      $this->sortowanie     = $this->paramatery['sortowanie'];
      $this->prefix         = $this->paramatery['prefix'];
      $this->klasa          = $this->paramatery['klasa'];
      $this->ikona          = '';
      $this->wyswietl       = false;
      $this->id             = $this->paramatery['id'];

      unset($Tlumaczenie);

    }

    function przetwarzanie() {
      global $zamowienie, $filtr;

      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_SESSION['punktyKlienta']) ) {

        $wynik = array();

        $ilosc_punktow = $_SESSION['punktyKlienta']['punkty_ilosc'];

        $kwota_rabatu = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$ilosc_punktow/(float)SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU,'',true);

        $wartosc_zamowienia_do_punktow = 0;
        foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {
          if ( $podsumowanie['prefix'] == '1' ) {
            if ( $podsumowanie['klasa'] == 'ot_shipping' ) {
              $wartosc_zamowienia_do_punktow;
            } else {
              $wartosc_zamowienia_do_punktow += $podsumowanie['wartosc'];
            }
          } elseif ( $podsumowanie['prefix'] == '0' ) {
            $wartosc_zamowienia_do_punktow -= $podsumowanie['wartosc'];
          }
        }

        // wykluczenie produktow z zakupu
        $wartosc_wykluczenia = 0;
        foreach ( $_SESSION['koszyk'] as $rekord ) {
          if (isset($rekord['zakup_za_punkty']) && $rekord['zakup_za_punkty'] == 'nie') {
              $wartosc_wykluczenia += $rekord['cena_brutto']*$rekord['ilosc'];
          }
        }        
        $wartosc_zamowienia_do_punktow = $wartosc_zamowienia_do_punktow - $wartosc_wykluczenia;

        if ( $kwota_rabatu > $wartosc_zamowienia_do_punktow ) {
          $kwota_rabatu = $wartosc_zamowienia_do_punktow;
        }
        
        // sprawdzi czy nie jest wiecej pkt niz klient posiada na koncie        
        $punkty = new Punkty((int)$_SESSION['customer_id'], true);

        // jezeli wartosc punktow klienta jest wieksza niz wartosc zamawianych produktow
        if ( $punkty->wartosc < $kwota_rabatu ) {
             $kwota_rabatu = $punkty->wartosc;
        }
            
        unset($punkty);        
                
        $tmp_marza = 1;
        if ( $_SESSION['domyslnyJezykStaly']['id'] != $_SESSION['domyslnaWaluta']['id'] ) {
             $tmp_marza = (100 + $_SESSION['domyslnaWaluta']['marza']) / 100;
        }                
                
        // zabezpieczenie jezeli wartosc rabatu jest mniejsza niz wykorzystana wartosc punktow
        if ( $kwota_rabatu * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU < $ilosc_punktow ) {

          $tablica_punktow = array('punkty_ilosc' => ceil((($kwota_rabatu/$_SESSION['domyslnaWaluta']['przelicznik']) / $tmp_marza) * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU),
                                   'punkty_status' => true);        

          $_SESSION['punktyKlienta'] = $tablica_punktow;

        }

        // zabezpieczenie jezeli ilosc pkt jest wieksza niz maksymalna wartosc % zamowienia
        if ( (float)SYSTEM_PUNKTOW_MAX_ZAMOWIENIA_PROCENT < 100 ) {
          //            
          $zawartosc_koszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
          //
          $max_wartosc_pkt_procent = $zawartosc_koszyka['brutto'] * ((float)SYSTEM_PUNKTOW_MAX_ZAMOWIENIA_PROCENT / 100);
          
          if ( $kwota_rabatu > $max_wartosc_pkt_procent ) {
            
              $tablica_punktow = array('punkty_ilosc' => ceil((($max_wartosc_pkt_procent/$_SESSION['domyslnaWaluta']['przelicznik']) / $tmp_marza) * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU),
                                       'punkty_status' => true);        

              $_SESSION['punktyKlienta'] = $tablica_punktow;            

          }
          //
          unset($zawartosc_koszyka);
          //
        }        
        
        $wynik = array('id' => $this->id,
                       'text' => $this->tytul,
                       'prefix' => $this->prefix,
                       'klasa' => $this->klasa,
                       'wartosc' => $kwota_rabatu,
                       'sortowanie' => $this->sortowanie);

        if ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktow() >= $GLOBALS['waluty']->PokazCeneBezSymbolu(SYSTEM_PUNKTOW_MIN_WARTOSC_ZAMOWIENIA,'',true) ) {  
             //
             return $wynik;
             //
        }
        
      }

      return;
    }
  }

}
?>