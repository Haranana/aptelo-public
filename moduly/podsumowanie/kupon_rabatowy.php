<?php

if (!class_exists('ot_discount_coupon')) {

    class ot_discount_coupon {
    
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

            $Tlumaczenie        = $GLOBALS['tlumacz'];

            $this->paramatery   = $parametry;

            $this->tytul        = $Tlumaczenie['OT_DISCOUNT_COUPON_TYTUL'];
            $this->sortowanie   = $this->paramatery['sortowanie'];
            $this->prefix       = $this->paramatery['prefix'];
            $this->klasa        = $this->paramatery['klasa'];
            $this->ikona        = '';
            $this->wyswietl     = false;
            $this->id           = $this->paramatery['id'];

            unset($Tlumaczenie);

        }

        function przetwarzanie() {

            if ( isset($_SESSION['kuponRabatowy']) ) {

                $wartosc_kuponu = $_SESSION['kuponRabatowy']['kupon_wartosc'];
                
                $wylacz = false;
                
                if ( $_SESSION['kuponRabatowy']['kupon_typ'] == 'wysylka' ) {
                  
                     $DostepnaWysylkaKupon = true;
                     
                     if ( $_SESSION['kuponRabatowy']['dostepne_wysylki'] != '' ) {
                          //
                          $PodzielWysylki = explode(',', (string)$_SESSION['kuponRabatowy']['dostepne_wysylki']);
                          //
                          if ( !in_array((string)$_SESSION['rodzajDostawy']['wysylka_id'], $PodzielWysylki) ) {
                               //
                               $DostepnaWysylkaKupon = false;
                               //
                          }
                          //
                     }
                  
                     if ( $DostepnaWysylkaKupon == true ) {
                          //
                          $wartosc_kuponu = round((($_SESSION['rodzajDostawy']['wysylka_koszt'] * $_SESSION['domyslnaWaluta']['przelicznik']) * ((100 + $_SESSION['domyslnaWaluta']['marza']) / 100)),2);
                          //
                     } else {
                          //
                          $wartosc_kuponu = round(0,2);
                          $wylacz = true;
                          //
                     }

                     unset($DostepnaWysylkaKupon);
  
                }

                if ( isset($_SESSION['kuponRabatowy']['kraj']) && $_SESSION['kuponRabatowy']['kraj'] != '' && !empty($_SESSION['kuponRabatowy']['kraj']) && isset($_SESSION['krajDostawy']['id']) ) {
                     //
                     $podzial = explode(',', trim((string)$_SESSION['kuponRabatowy']['kraj']));
                     
                     if ( !in_array($_SESSION['krajDostawy']['id'], $podzial) ) {
                          $wylacz = true;
                     }
                     
                     unset($podzial);                     
                     //
                }
                
                if ( $wartosc_kuponu <= 0 ) {
                     //
                     $wylacz = true;
                     //
                }
                
                if ( isset($_SESSION['rodzajPlatnosci']) ) {
                     //
                     if ( isset($_SESSION['rodzajPlatnosci']['platnosc_id']) && !empty($_SESSION['kuponRabatowy']['dostepne_platnosci']) ) {
                          //
                          $PodzielPlatnosci = explode(',', (string)$_SESSION['kuponRabatowy']['dostepne_platnosci']);
                          //
                          if ( !in_array($_SESSION['rodzajPlatnosci']['platnosc_id'], $PodzielPlatnosci) ) {
                               //
                               $wylacz = true;
                               unset($_SESSION['kuponRabatowy']);
                               //
                          }
                          //
                     }
                     //
                }                
                
                if ( $wylacz == false ) {
                  
                    $wynik = array('id' => $this->id,
                                   'text' => $this->tytul . ': ' . $_SESSION['kuponRabatowy']['kupon_kod'],
                                   'prefix' => $this->prefix,
                                   'klasa' => $this->klasa,
                                   'wartosc' => $wartosc_kuponu,
                                   'sortowanie' => $this->sortowanie);
                                   
                    unset($wartosc_kuponu);

                    return $wynik;
                    
                }
                
            }

            return;
        }
        
    }

}
?>