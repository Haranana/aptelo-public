<?php

if(!class_exists('ot_shopping_discount')) {

  class ot_shopping_discount {

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

      $this->tytul          = $Tlumaczenie['OT_SHOPPING_DISCOUNT_TYTUL'];
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
      
      if ( isset($GLOBALS['koszykKlienta']) ) {
        
          // wykluczenie dla kuponow
          if ( $this->paramatery['parametry']['ZNIZKI_KOSZYKA_KUPON'] == 'nie' ) {
               //
               if ( isset($_SESSION['kuponRabatowy']) ) {
                    return;  
               }
               //
          }

          // ustalenie czy klient nalezy do grupy dla ktorej sa naliczane nizki
          if ( $this->paramatery['parametry']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW'] != '' ) {
          
              $tablica_grup = explode(';', (string)$this->paramatery['parametry']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW']);

              if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
              
                  if ( !in_array((string)$_SESSION['customers_groups_id'], $tablica_grup) ) {
                    return;
                  }

              } else {
                
                  if ( !in_array('0', $tablica_grup) ) {
                      //
                      return;
                      //
                  }

              }                  
              
              unset($tablica_grup);
          
          }
          
          $zawartosc_koszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
          
          // ustalenie wartosci lub ilosci produktow w zamowieniu
          $wartosc_koszyka = 0;
          $ilosc_koszyka = 0;
          foreach ( $_SESSION['koszyk'] as $produkt ) {
              //
              $wylaczony_z_rabatow = false;
              //
              // czy produkt nie jest wylaczony z rabatow
              $produkt_tmp = new Produkt( Funkcje::SamoIdProduktuBezCech( $produkt['id'] ) );
              //
              if ( $produkt_tmp->info['wylaczone_rabaty'] == 'tak' ) {
                   $wylaczony_z_rabatow = true;
              }
              //
              unset($produkt_tmp);
              //
              if ( $produkt['promocja'] == 'nie' || ( $produkt['promocja'] == 'tak' && $this->paramatery['parametry']['ZNIZKI_KOSZYKA_PROMOCJE'] == 'tak' ) ) {
                  //
                  if ( $wylaczony_z_rabatow == false ) {
                       $wartosc_koszyka += $produkt['cena_brutto'] * $produkt['ilosc'];
                       $ilosc_koszyka +=  $produkt['ilosc'];
                  }
                  //
              }
              //
          }
          
          // ustalenie znizki w zaleznosci od wartosci zamowien
          if ( $wartosc_koszyka == 0 ) {
          
            return;
            
          } else {
          
            $tablica_znizek = preg_split("/[:;]/" , (string)$this->paramatery['parametry']['ZNIZKI_KOSZYKA_PROGI_ZNIZEK']);

            $znizka = 0;
            for ($i = 0, $c = count($tablica_znizek); $i < $c; $i+=2) {
              //
              // jezeli znizka jest zalezna od wartosci koszyka
              if ( $this->paramatery['parametry']['ZNIZKI_KOSZYKA_SPOSOB'] == 'kwota' ) {
                  //
                  if ( $wartosc_koszyka > $GLOBALS['waluty']->PokazCeneBezSymbolu($tablica_znizek[$i],'',true) ) {
                    $znizka = $tablica_znizek[$i+1];
                  }
                  //
                } else {
                  //
                  if ( $ilosc_koszyka > $tablica_znizek[$i] ) {
                    $znizka = $tablica_znizek[$i+1];
                  }
                  //
              }
              //
            }
            
          }

          if ( $znizka == 0 ) {
          
            return;
            
          }

          $wartosc_pomniejszenia = 0;
          if ( isset($_SESSION['kuponRabatowy']) ) {
              //
              $wartosc_kuponu = $_SESSION['kuponRabatowy']['kupon_wartosc'];
              
              if ( $_SESSION['kuponRabatowy']['kupon_typ'] == 'wysylka' ) {
                   $wartosc_kuponu = $_SESSION['rodzajDostawy']['wysylka_koszt'];
              }              
              //
              $wartosc_pomniejszenia = $wartosc_kuponu;
              
              unset($wartosc_kuponu);
          }
          $wartosc_koszyka -= $wartosc_pomniejszenia;

          // ustalenie wartosci znizki
          $wartosc_znizki = round(($wartosc_koszyka * ( $znizka / 100 )), 2);
          
          if ( $wartosc_znizki <= 0 ) {
               return;
          }

          $wynik = array();

          $wynik = array('id' => $this->id,
                         'text' => $this->tytul . ' (' . $znizka . '%)',
                         'prefix' => $this->prefix,
                         'klasa' => $this->klasa,
                         'wartosc' => $wartosc_znizki,
                         'sortowanie' => $this->sortowanie);
                         
          unset($zawartosc_koszyka, $wartosc_koszyka, $znizka, $wartosc_znizki, $ilosc_koszyka);

          return $wynik;
          
      }

    }
    
  }

}
?>