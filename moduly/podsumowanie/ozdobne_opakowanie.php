<?php

if(!class_exists('ot_package')) {
  class ot_package {

    public $paramatery;
    public $tytul;
    public $sortowanie;
    public $wartosc;
    public $vat;
    public $klasa;
    public $ikona;
    public $wyswietl;
    public $id;

    var $wyjscie;

    function __construct( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];

      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_PACKAGE_TYTUL'];
      $this->sortowanie     = $this->paramatery['sortowanie'];
      $this->wartosc        = $this->paramatery['parametry']['OPAKOWANIE_OZDOBNE_KOSZT'];
      $this->vat            = explode('|', (string)$this->paramatery['parametry']['OPAKOWANIE_OZDOBNE_STAWKA_VAT']);
      $this->klasa          = $this->paramatery['klasa'];
      $this->ikona          = '';
      $this->wyswietl       = false;
      $this->id             = $this->paramatery['id'];

      unset($Tlumaczenie);

    }

    function przetwarzanie() {
      
      if ( isset($_SESSION['opakowanieOzdobne']) ) {
      
          $przelicznik = 1 / $_SESSION['domyslnaWaluta']['przelicznik'];
          $marza = 1 + ( $_SESSION['domyslnaWaluta']['marza']/100 );

          $wartosc = number_format( round((($this->wartosc / $przelicznik) * $marza), CENY_MIEJSCA_PO_PRZECINKU), CENY_MIEJSCA_PO_PRZECINKU, '.', '');
          
          $vat_id = $this->vat[1];
          $vat_stawka = $this->vat[0];
          
          if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
               //
               // obliczy netto wysylki
               if ( $wartosc > 0 && $this->vat[0] > 0 ) {
                    $wartosc = $wartosc / ((100 + $this->vat[0]) / 100);
               }
               //
               $vat_id = $_SESSION['vat_zwolniony_id'];
               $vat_stawka = $_SESSION['vat_zwolniony_wartosc'];
               //
          }          

          $wynik = array('id' => $this->id,
                         'text' => $this->tytul,
                         'prefix' => 1,
                         'klasa' => $this->klasa,
                         'wartosc' => $wartosc,
                         'sortowanie' => $this->sortowanie,
                         'vat_id' => $vat_id,
                         'vat_stawka' => $vat_stawka);
                         
          unset($przelicznik, $marza, $vat_id, $vat_stawka);

          return $wynik;
          
      }

    }
  }

}

?>