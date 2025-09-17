<?php

class Wysylki {

  public $kraj;
  public $produktId;
  public $produktWaga;
  public $produktCena;
  public $produktDostepne;
  public $produktGabaryt;
  public $produktKosztWysylki;
  public $wykluczonaDarmowaWysylka;
  public $produktPromocja;
  public $wysylki;
  public $wysylki_parametry;
  public $waga_zamowienia;
  public $wartosc_zamowienia;
  public $ilosc_produktow;
  public $wartosc_zamowienia_bez_promocji;

  public function __construct( $KrajId, $IdProduktu = '', $WagaProduktu = '', $CenaProduktu = '', $WysylkiProduktu = '', $GabarytProduktu = '', $KosztWysylkiProduktu = '0', $WykluczonaDarmowaWysylka = 'nie', $ProduktWPromocji = '0' ) {

    // wybrane panstwo
    $this->kraj = $KrajId;

    // czy przesylka ma byc liczona dla produktu czy koszyka
    $this->produktId                = $IdProduktu;
    $this->produktWaga              = $WagaProduktu;
    $this->produktCena              = $CenaProduktu;
    $this->produktDostepne          = $WysylkiProduktu;
    $this->produktGabaryt           = $GabarytProduktu;
    $this->produktKosztWysylki      = $KosztWysylkiProduktu;
    $this->wykluczonaDarmowaWysylka = $WykluczonaDarmowaWysylka;
    $this->produktPromocja          = $ProduktWPromocji;

    // tablica dostepnych wysylek
    $this->wysylki = array();
    $this->wysylki_parametry = array();

    // ustalenie wagi zamowienia i wartosci zamowienia
    $this->waga_zamowienia = 0;
    $this->wartosc_zamowienia = 0;    
    $this->ilosc_produktow = 0;

    if ( $this->produktId == '' && $this->produktWaga == '' && $this->produktCena == '' && $this->produktDostepne == '' ) {
         
         if ( isset($_SESSION['koszyk']) ) {
           
            foreach ( $_SESSION['koszyk'] as $rekord ) {
                
                if ( isset($rekord['waga']) && isset($rekord['ilosc']) ) {
                     $this->waga_zamowienia += $rekord['waga'] * $rekord['ilosc'];
                }
                if ( isset($rekord['cena_brutto']) && isset($rekord['ilosc']) ) {
                     $this->wartosc_zamowienia += $rekord['cena_brutto'] * $rekord['ilosc'];
                }
                if ( isset($rekord['ilosc']) ) {
                     $this->ilosc_produktow += $rekord['ilosc'];
                }
            }
            
         }
         
    } else {
      
         $this->waga_zamowienia = $this->produktWaga;
         $this->wartosc_zamowienia = $this->produktCena;
         $this->ilosc_produktow = 1;
        
    }
    
    // zlicza wartosc produktow z promocjami i odlicza je od ogolnej wartosci zamowienia
    $this->wartosc_zamowienia_bez_promocji = $this->wartosc_zamowienia;
    
    if ( isset($_SESSION['koszyk']) ) {
    
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            if (isset($TablicaZawartosci['promocja']) && $TablicaZawartosci['promocja'] == 'tak') {
                //
                $this->wartosc_zamowienia_bez_promocji -= $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];                  
                //
            }
            //
        }    
        
    }

    $this->DostepneWysylki();

  }

  // funkcja zwraca w formie tablicy dostepne wysylki
  public function DostepneWysylki() {
    global $tablica_wysylki;

    // utworzenie tablicy parametrow
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('WysylkiParametry', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        //$zapytanie_parametry = "SELECT modul_id, kod, wartosc FROM modules_shipping_params";

        $zapytanie_parametry = "SELECT p.modul_id, p.kod, p.wartosc FROM modules_shipping_params p
                                LEFT JOIN modules_shipping m ON p.modul_id = m.id
                                WHERE m.status = '1'";

        $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
        while ($info_parametry = $sql_parametry->fetch_assoc()) {
          $this->wysylki_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
        }
        $GLOBALS['db']->close_query($sql_parametry);
        unset($zapytanie_parametry, $info_parametry);
        
        $GLOBALS['cache']->zapisz('WysylkiParametry', $this->wysylki_parametry, CACHE_INNE);
        
      } else {
     
       $this->wysylki_parametry = $WynikCache;
    
    }       

    unset($WynikCache);
    
    // wysylki
    
    $WysylkiTablica = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Wysylki', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie = "SELECT id, nazwa, skrypt, klasa, sortowanie 
                        FROM modules_shipping
                       WHERE status = '1'
                       ORDER BY sortowanie";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
          $WysylkiTablica[] = $info;
        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);
        
        $GLOBALS['cache']->zapisz('Wysylki', $WysylkiTablica, CACHE_INNE);
        
      } else {
     
        $WysylkiTablica = $WynikCache;
    
    }       

    foreach ( $WysylkiTablica as $info ) {

      if ( !isset($this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA']) ) $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] = '';
      if ( !isset($this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC']) ) $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] = '';
      if ( !isset($this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE']) ) $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'] = '';

      if ( $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] == '' || $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] == '0' ) $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] = '999999999';
      if ( $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] == '' || $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] == '0') $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] = '999999999';
      if ( $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'] == '' || $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'] == '0') $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'] = '999999999';

      // jezeli laczna wartosc zamowienia przekracza maksymalna wartosc dla przesylki - przesylka niedostepna
      if ( $this->wartosc_zamowienia <= $GLOBALS['waluty']->PokazCeneBezSymbolu($this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'],'',true) ) {

        // sprawdzenie czy przesylka jest dostepna do danego kraju
        $tablica_krajow = explode(';', (string)$this->wysylki_parametry[$info['id']]['WYSYLKA_KRAJE_DOSTAWY']);
        if ( in_array( $this->kraj, $tablica_krajow ) ) {

          $tablica_wysylki = array( 'id' => $info['id'],
                                    'text' => $info['nazwa'],
                                    'skrypt' => $info['skrypt'],
                                    'klasa' => $info['klasa'],
                                    'sortowanie' => $info['sortowanie'],
                                    'waga_zamowienia' => $this->waga_zamowienia,
                                    'wartosc_zamowienia' => $this->wartosc_zamowienia,
                                    'wartosc_zamowienia_bez_promocji' => $this->wartosc_zamowienia_bez_promocji,
                                    'ilosc_produktow' => $this->ilosc_produktow,
                                    'parametry' => $this->wysylki_parametry[$info['id']]);

          require_once('moduly/wysylka/'.$info['klasa'].'.php');
          $wysylka = new $info['klasa']($tablica_wysylki, $this->kraj, $this->produktId, $this->produktWaga, $this->produktCena, $this->produktDostepne, $this->produktGabaryt, $this->produktKosztWysylki, $this->wykluczonaDarmowaWysylka, $this->produktPromocja);

          $TablicaPrzetwarzanie = $wysylka->przetwarzanie();
          if (isset($TablicaPrzetwarzanie) && count($TablicaPrzetwarzanie) > 0 ) {
            $this->wysylki[$info['id']] = $TablicaPrzetwarzanie;
          }

        }
      }
      
    }
    
    unset($WysylkiTablica);
    
    if ( count($this->wysylki) < 1 ) {
    
      $this->wysylki['0'] = array('id' => '0',
                                  'klasa' => ( isset($info['klasa']) ? $info['klasa'] : ''),
                                  'text' => '0',
                                  'wartosc' => '---',
                                  'vat_id' => '1',
                                  'vat_stawka' => '23',
                                  'kod_gtu' => '',
                                  'dostepne_platnosci' => '',
                                  'objasnienie' => '',
                                  'wysylka_free' => '0',
                                  'free_promocje' => 'nie',
                                  'ikona' => '');
      
    } else {
    
      if ( isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) > 0 ) {
            
          // jezeli wszystkie produkty w koszyku maja przesylke gratis to wyzeruje koszty wszystkich wysylek
          $ProduktyWysylkaGratis = true;
          $ProduktyWysylkaGratisIlosc = 0;
          $WykluczonaDarmowaWysylka = false;
          //
          if ( isset($_SESSION['koszyk']) ) {
            
              foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                  //
                  if ( isset($TablicaZawartosci['darmowa_wysylka']) && $TablicaZawartosci['darmowa_wysylka'] == 'nie' ) {
                       $ProduktyWysylkaGratis = false;
                  }
                  if ( isset($TablicaZawartosci['darmowa_wysylka']) && $TablicaZawartosci['darmowa_wysylka'] == 'tak' ) {
                       $ProduktyWysylkaGratisIlosc++;
                  }              
                  if ( isset($TablicaZawartosci['wykluczona_darmowa_wysylka']) && $TablicaZawartosci['wykluczona_darmowa_wysylka'] == 'tak' ) {
                       $WykluczonaDarmowaWysylka = true;
                  }              
                  //
              }
              
          }
          
          // jezeli opcja darmowej wysylki ma byc tylko dla domyslnego kraju
          if ( PRODUKT_WYSYLKA_GRATIS_KRAJE == 'domyślny' && isset($_SESSION['krajDostawy']) && isset($_SESSION['krajDostawyDomyslny']) && $_SESSION['krajDostawy']['id'] != $_SESSION['krajDostawyDomyslny']['id'] ) {
               $WykluczonaDarmowaWysylka = true;
          }          
          
          if (( $ProduktyWysylkaGratis == true && $WykluczonaDarmowaWysylka == false ) || ( $ProduktyWysylkaGratisIlosc > 0 && PRODUKT_WYSYLKA_GRATIS == 'tak' && $WykluczonaDarmowaWysylka == false )) {
              //
              foreach ( $this->wysylki as $klucz => $wartosc ) {
                  //
                  if ( !isset($this->wysylki[$klucz]['wykluczona_darmowa']) || $this->wysylki[$klucz]['wykluczona_darmowa'] == 'nie' ) {
                     $this->wysylki[$klucz]['wartosc'] = 0;
                     $this->wysylki[$klucz]['wysylka_free'] = 0;
                  }
                  //
              } 
              //

          }
          
          unset($ProduktyWysylkaGratis);
          
      }
    
    }

  }
  
  // funkcja wykonywana podczas potwierdzenia zamowienia
  public function Potwierdzenie( $wysylka_id, $wysylka_klasa ) {
    global $tablica_wysylki;

    //utworzenie tablicy parametrow
    $zapytanie_parametry = "SELECT modul_id, kod, wartosc 
                              FROM modules_shipping_params
                             WHERE modul_id = '".$wysylka_id."'";

    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);

    while ($info_parametry = $sql_parametry->fetch_assoc()) {
      $this->wysylki_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
    }

    $tablica_wysylek = array('id' => $wysylka_id,
                             'wysylka_id' => '',
                             'text' => '',
                             'skrypt' => $wysylka_klasa.'.php',
                             'klasa' => $wysylka_klasa,
                             'sortowanie' => '',
                             'waga_zamowienia' => $this->waga_zamowienia,
                             'wartosc_zamowienia' => $this->wartosc_zamowienia,
                             'wartosc_zamowienia_bez_promocji' => $this->wartosc_zamowienia_bez_promocji,
                             'ilosc_produktow' => $this->ilosc_produktow,
                             'parametry' => $this->wysylki_parametry[$wysylka_id]);

    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry, $info_parametry);

    require_once('moduly/wysylka/'.$wysylka_klasa.'.php');
    $wysylka = new $wysylka_klasa($tablica_wysylek);

    return $wysylka->potwierdzenie();

  }

} 

?>