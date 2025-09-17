<?php

if(!class_exists('ot_total')) {
  class ot_total {

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

      $this->tytul          = $Tlumaczenie['OT_TOTAL_TYTUL'];
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

      // ustalenie wartosci produktow w zamowieniu
      $razem = 0;
      if ( isset($_SESSION['podsumowanieZamowienia']) ) {
          foreach ( $_SESSION['podsumowanieZamowienia'] as $rekord ) {
            if ( $rekord['prefix'] == '1' ) {
              $razem += $rekord['wartosc'];
            } elseif ( $rekord['prefix'] == '0' ) {
              $razem -= $rekord['wartosc'];
            }
          }
      }

      $wynik = array();

      $wynik = array('id' => $this->id,
                     'text' => $this->tytul,
                     'prefix' => $this->prefix,
                     'klasa' => $this->klasa,
                     'wartosc' => $razem,
                     'sortowanie' => $this->sortowanie);

      return $wynik;

    }
  }

}

?>