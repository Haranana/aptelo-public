<?php
if (!class_exists('ot_shipping')) {

  class ot_shipping {

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

      $this->tytul          = $Tlumaczenie['OT_SHIPPING_TYTUL'];
      $this->sortowanie     = $this->paramatery['sortowanie'];
      $this->prefix         = $this->paramatery['prefix'];
      $this->klasa          = $this->paramatery['klasa'];
      $this->ikona          = '';
      $this->wyswietl       = false;
      $this->id             = $this->paramatery['id'];

      unset($Tlumaczenie);

    }


    function przetwarzanie() {
      global $zamowienie;

      if ( isset($_SESSION['rodzajDostawy']) && isset($_SESSION['rodzajPlatnosci']) ) {
        $koszt_wysylki = $_SESSION['rodzajDostawy']['wysylka_koszt'];
        $vat_id = $_SESSION['rodzajDostawy']['wysylka_vat_id'];
        $vat_stawka = $_SESSION['rodzajDostawy']['wysylka_vat_stawka'];
        $kod_gtu = $_SESSION['rodzajDostawy']['wysylka_kod_gtu'];
      } else {
        $koszt_wysylki = 0;
        //
        $vat_tb = Funkcje::domyslnyPodatekVat();
        $vat_id = $vat_tb['id'];
        $vat_stawka = $vat_tb['stawka'];  
        $kod_gtu = '';
        unset($vat_tb);
        //
      }

      $wynik = array();

      $wynik = array('id' => $this->id,
                     'text' => $this->tytul,
                     'prefix' => $this->prefix,
                     'klasa' => $this->klasa,
                     'wartosc' => $GLOBALS['waluty']->PokazCeneBezSymbolu($koszt_wysylki,'',true),
                     'sortowanie' => $this->sortowanie,
                     'vat_id' => $vat_id,
                     'vat_stawka' => $vat_stawka,
                     'kod_gtu' => $kod_gtu);

      return $wynik;

    }
  } 
  
}
?>