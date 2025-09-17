<?php

class Platnosci {

  public $wysylka;
  public $platnosci;
  public $platnosci_parametry;

  public function __construct( $wysylka_id, $id_zamowienia = 0 ) {

    // wybrana wysylka
    $this->wysylka = $wysylka_id;

    // tablica dostepnych platnosci
    $this->platnosci = array();
    $this->platnosci_parametry = array();

    $this->DostepnePlatnosci( $id_zamowienia );

  }

  // funkcja zwraca w formie tablicy dostepne platnosci
  public function DostepnePlatnosci( $id_zamowienia ) {
    global $tablica_platnosci;

    // utworzenie tablicy parametrow
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('PlatnosciParametry', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 

        $zapytanie_parametry = "SELECT p.modul_id, p.kod, p.wartosc FROM modules_payment_params p
                                LEFT JOIN modules_payment m ON p.modul_id = m.id
                                WHERE m.status = '1'";

        $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql_parametry) > 0 ) {
          
            while ($info_parametry = $sql_parametry->fetch_assoc()) {
              $this->platnosci_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
            }
            
            unset($info_parametry);
            
        }
        
        $GLOBALS['db']->close_query($sql_parametry);
        unset($zapytanie_parametry);
        
        $GLOBALS['cache']->zapisz('PlatnosciParametry', $this->platnosci_parametry, CACHE_INNE);
        
      } else {
     
       $this->platnosci_parametry = $WynikCache;
    
    } 
    
    unset($WynikCache);
    
    // platnosci
    
    $PlatnosciTablica = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Platnosci', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie = "SELECT id, nazwa, skrypt, klasa, sortowanie 
                        FROM modules_payment
                       WHERE status = '1'
                    ORDER BY sortowanie";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
          
            while ($info = $sql->fetch_assoc()) {
              $PlatnosciTablica[] = $info;
            }
            
            unset($info);
            
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);
        
        $GLOBALS['cache']->zapisz('Platnosci', $PlatnosciTablica, CACHE_INNE);
        
      } else {
     
        $PlatnosciTablica = $WynikCache;
    
    }      
    
    // id wysylki - jak to klasa
    $zapytanie = "SELECT klasa FROM modules_shipping WHERE id = '1'";

    foreach ( $PlatnosciTablica as $info ) {
      
      // jezeli platnosc indywidualna za pobraniem
      $PokazPlatnosc = true;
      
      if ( $info['klasa'] == 'platnosc_pobranie_indywid' ) {
           //
           if ( isset($_SESSION['rodzajDostawy']['wysylka_klasa']) && $_SESSION['rodzajDostawy']['wysylka_klasa'] != 'wysylka_indywidualna' ) {
                $PokazPlatnosc = false;
           }     
           //
           // sprawdzi czy ta wysylka nie jest wlaczona dla tej platnosci
           if ( isset($_SESSION['rodzajDostawy']['dostepne_platnosci']) && isset($_SESSION['rodzajDostawy']['wysylka_id']) ) {
                //
                $Podzial = explode(';', (string)$_SESSION['rodzajDostawy']['dostepne_platnosci']);
                //
                if ( in_array($info['id'], $Podzial) ) {
                     $PokazPlatnosc = true;
                }
                //
           }                
           //
      } 
      
      // czy platnosc jest dostepna dla waluty
      if ( isset($_SESSION['domyslnaWaluta']) && isset($_SESSION['domyslnaWaluta']['id']) ) {
           
           // jakie waluty
           if ( isset($this->platnosci_parametry[$info['id']]['TYLKO_WALUTA']) && !empty($this->platnosci_parametry[$info['id']]['TYLKO_WALUTA']) ) {
                
                if ( !in_array($_SESSION['domyslnaWaluta']['id'], explode(';', (string)$this->platnosci_parametry[$info['id']]['TYLKO_WALUTA'])) ) {
                     $PokazPlatnosc = false;
                }
                
           }
           
      }

      if ( $PokazPlatnosc == true ) {
        
          $tablica_platnosci = array('id' => $info['id'],
                                     'wysylka_id' => $this->wysylka,
                                     'text' => $info['nazwa'],
                                     'skrypt' => $info['skrypt'],
                                     'sortowanie' => $info['sortowanie'],
                                     'klasa' => $info['klasa'],
                                     'parametry' => $this->platnosci_parametry[$info['id']]);

          require_once('moduly/platnosc/'.$info['klasa'].'.php');
          $platnosc = new $info['klasa']($tablica_platnosci);

          if ( is_array($platnosc->przetwarzanie( $id_zamowienia )) && count($platnosc->przetwarzanie( $id_zamowienia )) > 0 ) {
            $this->platnosci[$info['id']] = $platnosc->przetwarzanie( $id_zamowienia );
          }
          
      }
      
      unset($PokazPlatnosc);

    }
    
    unset($PlatnosciTablica);

    if ( count($this->platnosci) == '0' ) {
      $this->platnosci['0'] = array('id' => '0',
                                    'klasa' => ( isset($info['klasa']) ? $info['klasa'] : ''),
                                    'text' => '0',
                                    'wartosc' => '---',
                                    'punkty' => 'nie'
      );
    }

  }
  
  // funkcja wykonywana podczas potwierdzenia zamowienia
  public function Potwierdzenie( $platnosc_id, $platnosc_klasa ) {

    //utworzenie tablicy parametrow
    $zapytanie_parametry = " SELECT modul_id, kod, wartosc 
                               FROM modules_payment_params
                              WHERE modul_id = '".$platnosc_id."'";

    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql_parametry) > 0 ) {

        while ($info_parametry = $sql_parametry->fetch_assoc()) {
          $this->platnosci_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
        }
        
        unset($info_parametry);

    }
    
    $tablica_platnosci = array('id' => $platnosc_id,
                               'wysylka_id' => '',
                               'text' => '',
                               'skrypt' => $platnosc_klasa.'.php',
                               'klasa' => $platnosc_klasa,
                               'sortowanie' => '',
                               'parametry' => $this->platnosci_parametry[$platnosc_id]);

    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry);

    require_once('moduly/platnosc/'.$platnosc_klasa.'.php');
    $platnosc = new $platnosc_klasa($tablica_platnosci);

    return $platnosc->potwierdzenie();

  }

  // funkcja wykonywana po zlozeniu zamowienia - dotyczy platnosci elektronicznych
  public function Podsumowanie( $platnosc_id, $platnosc_klasa, $id_zamowienia = 0 ) {

    // utworzenie tablicy parametrow
    if ( $platnosc_id == 0 ) {
         $zapytanie_parametry = "SELECT mpp.modul_id, mpp.kod, mpp.wartosc FROM modules_payment mp, modules_payment_params mpp WHERE mp.id = mpp.modul_id and mp.klasa = '" . $platnosc_klasa . "'";
      } else {
         $zapytanie_parametry = "SELECT modul_id, kod, wartosc FROM modules_payment_params WHERE modul_id = '" . $platnosc_id . "'";
    }

    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql_parametry) > 0 ) {

        while ($info_parametry = $sql_parametry->fetch_assoc()) {
            //
            $this->platnosci_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
            //
            if ( $platnosc_id == 0 ) {
                 $platnosc_id = $info_parametry['modul_id'];
            }
            //
        }
        
        unset($info_parametry);
        
    }

    $tablica_platnosci = array('id' => $platnosc_id,
                               'wysylka_id' => '',
                               'text' => '',
                               'skrypt' => $platnosc_klasa.'.php',
                               'klasa' => $platnosc_klasa,
                               'sortowanie' => '',
                               'parametry' => $this->platnosci_parametry[$platnosc_id]);

    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry);

    require_once('moduly/platnosc/'.$platnosc_klasa.'.php');
    $platnosc = new $platnosc_klasa($tablica_platnosci);

    return $platnosc->podsumowanie( $id_zamowienia );

  }

} 

?>