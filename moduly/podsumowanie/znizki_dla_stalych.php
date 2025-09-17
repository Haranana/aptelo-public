<?php

if(!class_exists('ot_loyalty_discount')) {

  class ot_loyalty_discount {

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

      $this->tytul          = $Tlumaczenie['OT_LOYALTY_DISCOUNT_TYTUL'];
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

      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        
        // wykluczenie dla kuponow
        
        if ( $this->paramatery['parametry']['STALI_KLIENCI_KUPON'] == 'nie' ) {
             //
             if ( isset($_SESSION['kuponRabatowy']) ) {
                  return;  
             }
             //
        }        

        // ustalenie czy klient nalezy do grupy dla ktorej sa naliczane nizki
        $tablica_grup = explode(';', (string)$this->paramatery['parametry']['STALI_KLIENCI_GRUPA_KLIENTOW']);
        if ( !in_array((string)$_SESSION['customers_groups_id'], $tablica_grup) && !empty($this->paramatery['parametry']['STALI_KLIENCI_GRUPA_KLIENTOW']) ) {
          return;
        }

        //obliczenie dotychczasowych wartosci zamowien klienta
        $zapytanie = "SELECT 
          o.date_purchased, o.currency_value, ot.value 
          FROM orders o 
          LEFT JOIN orders_total ot ON (o.orders_id = ot.orders_id) 
          WHERE o.customers_dummy_account != '1' AND o.customers_id = '" . (int)$_SESSION['customer_id'] . "' AND ot.class = 'ot_subtotal' AND o.orders_status = '" . $this->paramatery['parametry']['STALI_KLIENCI_STATUS_ZAMOWIEN'] . "' ORDER BY date_purchased DESC";

          $sql = $GLOBALS['db']->open_query($zapytanie);

          if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            // okres z jakiego sa pobierane zamowienia
            $okres_naliczania = $this->paramatery['parametry']['STALI_KLIENCI_OKRES_NALICZANIA_ZAMOWIEN'];

            $wartosc_wszystkich_zamowien = 0;

            while ( $info = $sql->fetch_assoc() ) {
              if ( $info['currency_value'] == 0 ) { $info['currency_value'] = 1; }
              switch ($okres_naliczania) {
                case '99':
                  $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  break;
                case '1':
                  $rok = 60*60*24*365;
                  if ( time() - FunkcjeWlasnePHP::my_strtotime($info['date_purchased']) < $rok ) {
                    $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  }
                  break;
                case '4':
                  $polroku = 60*60*24*182;
                  if ( time() - FunkcjeWlasnePHP::my_strtotime($info['date_purchased']) < $polroku ) {
                    $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  }
                  break;                  
                case '3':
                  $kwartal = 60*60*24*92;
                  if ( time() - FunkcjeWlasnePHP::my_strtotime($info['date_purchased']) < $kwartal ) {
                    $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  }
                  break;
              }
            }
            
            unset($okres_naliczania);
            
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);
            
          } else {
          
            return;
            
          }

        // ustalenie znizki w zaleznosci od wartosci zamowien
        if ( $wartosc_wszystkich_zamowien == 0 ) {
        
          return;
          
        } else {
        
          $tablica_znizek = preg_split("/[:;]/" , (string)$this->paramatery['parametry']['STALI_KLIENCI_PROGI_ZNIZEK']);

          $znizka = 0;
          for ($i = 0, $c = count($tablica_znizek); $i < $c; $i+=2) {
            if ( $wartosc_wszystkich_zamowien > $tablica_znizek[$i] ) {
              $znizka = $tablica_znizek[$i+1];
              //break;
            }
          }
          
        }

        if ( $znizka == 0 ) {
        
          return;
          
        }

        // ustalenie wartosci produktow w zamowieniu
        $wartosc_znizki = 0;
        foreach ( $_SESSION['koszyk'] as $rekord ) {
            if ( $rekord['promocja'] == 'nie' || ( $rekord['promocja'] == 'tak' && $this->paramatery['parametry']['STALI_KLIENCI_PROMOCJE'] == 'tak' ) ) {
                $wartosc_znizki += $rekord['cena_brutto']*$rekord['ilosc'];
            }
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
        $wartosc_znizki -= $wartosc_pomniejszenia;

        // ustalenie wartosci znizki
        $wartosc_znizki = round(($wartosc_znizki * ( $znizka / 100 )), 2);

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
                       
        unset($tablica_grup, $wartosc_wszystkich_zamowien, $tablica_znizek, $wartosc_znizki, $wartosc_znizki, $wartosc_pomniejszenia);

        return $wynik;
        
      }

      return;
      
    }
    
  }

}
?>