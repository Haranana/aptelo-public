<?php

class Koszyk {

    public function __construct() {
        //
        if (!isset($_SESSION['koszyk'])) {
            $_SESSION['koszyk'] = array();
        }    
        //
    }
    
    public function PrzywrocKoszykZalogowanego() {
        //    
        $wynikPrzeliczania = false;
        //
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
          
            $tablicaProdukty = array();

            // przeniesie produktow z bazy do sesji
            $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "' and price_type = 'baza'";
            $sql = $GLOBALS['db']->open_query($zapytanie);  
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $info['products_id'] ) );
                    //
                    if ($Produkt->CzyJestProdukt == true) {
                    
                        if ($this->CzyJestWKoszyku($info['products_id']) == false) {
                            //
                            $_SESSION['koszyk'][$info['products_id']] = array('id'          => $info['products_id'],
                                                                              'ilosc'       => $info['customers_basket_quantity'],
                                                                              'komentarz'   => $info['products_comments'],
                                                                              'pola_txt'    => $info['products_text_fields'],
                                                                              'rodzaj_ceny' => 'baza');
                            //
                         } else {
                            //
                            if ( !in_array($info['products_id'], $tablicaProdukty) ) {
                                 //
                                 $_SESSION['koszyk'][$info['products_id']]['ilosc'] += $info['customers_basket_quantity'];
                                 //
                            }
                            //
                        } 
                        
                        $tablicaProdukty[] = $info['products_id'];

                        $this->SprawdzIloscProduktuMagazyn( $info['products_id'] );
                        
                        $this->SprawdzCechyProduktu( $info['products_id'] );
                        
                        $wynikPrzeliczania = true;

                    } else {
                    
                        // jezeli nie jest aktywny usunie produkt z bazy                
                        $GLOBALS['db']->delete_query('customers_basket' , "products_id = '" . $info['products_id'] . "'");
                        //
                        
                    }
                    //
                    unset($Produkt);
                    //
                }
                //
                unset($info);
                //
            }

            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);                  

            // sprawdzi czy nie trzeba zmienic id klienta
            $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE session_id = '" . (string)session_id() . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);   
            //
            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {           
                 //
                 // aktualizuje produkt
                 $pola = array(array('customers_id',(int)$_SESSION['customer_id']),
                               array('customers_ip',((isset($_SESSION['ippp'])) ? $_SESSION['ippp'] : '')));   
                 $GLOBALS['db']->update_query('customers_basket' , $pola, "session_id = '" . (string)session_id() . "'");	
                 unset($pola);  
                 //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);   
            
            // usunie duplikaty
            $tablicaDuplikaty = array();
                                                                
            $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "' and price_type = 'baza'";
            $sql = $GLOBALS['db']->open_query($zapytanie);  
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    if ( in_array($info['products_id'], $tablicaProdukty) ) {
                         //
                         $tablicaDuplikaty[] = $info['products_id'];
                         //
                    }
                    //
                }
                //
                unset($info);
            }
            //
            if ( count($tablicaDuplikaty) > 0 ) {
                 //
                 foreach ( $tablicaDuplikaty as $id ) {
                      //
                      $GLOBALS['db']->delete_query('customers_basket' , "products_id = '" . $id . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");	  
                      //
                 }
                 //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $tablicaProdukty, $tablicaDuplikaty);

            $this->PrzeliczKoszyk();  
                        
        }
        //
        return $wynikPrzeliczania;
        //
    }    
    
    public function PrzywrocKoszykCookie() {
        //    
        if (isset($_COOKIE['koszykGold']) ) {
            //
            $ProduktyCookie = array();
            //
            $PodzielCookie = explode('prd', (string)$_COOKIE['koszykGold']);
            //
            if ( count($PodzielCookie) > 0 ) {
                 //
                 foreach ( $PodzielCookie as $ProduktTablica ) {
                    //
                    $ProduktCookie = explode('qt', (string)$ProduktTablica);
                    //
                    if ( count($ProduktCookie) == 2 ) {
                         //
                         $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $ProduktCookie[0] ) );
                         //
                         if ( $Produkt->CzyJestProdukt == true ) {
   
                              $ProduktyCookie[] = array('id'          => $ProduktCookie[0],
                                                        'ilosc'       => (float)$ProduktCookie[1],
                                                        'komentarz'   => '',
                                                        'pola_txt'    => '',
                                                        'rodzaj_ceny' => 'baza');

                         }
                         //
                         unset($Produkt);
                         //
                    }
                    //
                    unset($ProduktCookie, $SprawdzCechy);
                    //
                }         
                // 
            }                
            //            
        }
        //
        if ( count($ProduktyCookie) > 0 ) {
             //
             unset($_SESSION['koszyk']);
             //
             foreach ( $ProduktyCookie as $Produkt ) {
                  //
                  $_SESSION['koszyk'][$Produkt['id']] = array('id'          => $Produkt['id'],
                                                              'ilosc'       => $Produkt['ilosc'],
                                                              'komentarz'   => '',
                                                              'pola_txt'    => '',
                                                              'rodzaj_ceny' => 'baza');  
                  //
                  $this->SprawdzIloscProduktuMagazyn( $Produkt['id'] ); 
                  //
                  $this->SprawdzCechyProduktu( $Produkt['id'] );               
                  //
             }
             //
             $this->PrzeliczKoszyk();   
             //
        }
        //
    }        
    
    // sprawdzanie przy przywracaniu koszyka czy zgdzaja sie cechy z produktem
    public function SprawdzCechyProduktu($id) {
      
        if ( strpos((string)$id, "U") > -1 ) {
             //
             $id = substr((string)$id, 0, strpos((string)$id, 'U'));
             //
        } 

        // wyciaga same cechy z produktu
        $cechy_tab = array();
        //
        if ( strpos((string)$id, "x") > -1 ) {
             //
             $cechy = substr((string)$id, strpos((string)$id, "x"), strlen((string)$id) );
             //
             $cechy_tab = explode('x', substr($cechy,1,strlen($cechy)));
             //
        }
        //
        $tab_tmp = array();
        $ile_cech = array();
        //
        // tablica jakie produkt ma cechy
        $zapytanie = "SELECT DISTINCT options_id, options_values_id FROM products_attributes WHERE products_id = '" . (int)Funkcje::SamoIdProduktuBezCech($id) . "'";      
        $sql = $GLOBALS['db']->open_query($zapytanie);             
        //
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) ) {
            //
            while ( $info = $sql->fetch_assoc() ) {
                //
                $tab_tmp[] = $info['options_id'] . '-' . $info['options_values_id'];
                $ile_cech[ $info['options_id'] ] = $info['options_id'];
                //
            }
            //
        }
        //
        $GLOBALS['db']->close_query($sql);
        //
        if ( ( count($ile_cech) > 0 && count($ile_cech) != count($cechy_tab) ) || ( count($ile_cech) == 0 && count($cechy_tab) > 0 ) ) {
             //
             $this->UsunZKoszyka( $id, true );
             return false;
             //
        } else {   
             //
             foreach ( $cechy_tab as $tmp ) {
                //
                if ( !in_array($tmp, $tab_tmp) ) {
                     //
                     $this->UsunZKoszyka( $id, true );
                     return false;
                     //
                }
               //
             }
             //
        }
        //
        unset($cechy_tab, $tab_tmp);   
        
        return true;
        
    }
    
    // sprawdzanie ilosci produktow przy przywracaniu koszyka klienta oraz przy potwierdzeniu zamowienia - czy ktos nie kupil produktu
    public function SprawdzIloscProduktuMagazyn($id, $przelicz = true) {
      
        if ( !isset($_SESSION['koszyk'][$id]) ) {
              return true;
        }
        
        $KoncowaIlosc = $_SESSION['koszyk'][$id]['ilosc'];

        // jezeli jest wlaczona opcja kazdego produktu osobno w koszyku to sprawdzi czy nie ma wiecej pozycji
        if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
             //
             $KoncowaIlosc = 0;
             //
             foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
                
                if ( substr((string)$id, 0, strpos((string)$id, 'U')) == substr((string)$TablicaWartosci['id'], 0, strpos((string)$TablicaWartosci['id'], 'U')) ) {
                     $KoncowaIlosc += $TablicaWartosci['ilosc'];
                }
                
             }
             //                     
        }          

        $Akcja = '';
        
        $ProduktKontrola = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );
        
        // jezeli produkt jest wylaczony to usuwa go z koszyka
        if ( $ProduktKontrola->CzyJestProdukt == false) {
             //
             $this->UsunZKoszyka( $id, $przelicz );
             return true;
             //
        }
        
        // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
        $Przecinek = 2;
        // jezeli sa wartosci calkowite to dla pewnosci zrobi int
        if ( $ProduktKontrola->info['jednostka_miary_typ'] == '1' ) {
            $Przecinek = 0;
        }
        //         
    
        // czy produkt ma cechy
        $cechy = '';
        
        if ( strpos((string)$id, "x") > -1 ) {
            // wyciaga same cechy z produktu
            $cechy = substr((string)$id, strpos((string)$id, "x"), strlen((string)$id) );
        }   

        if ( $cechy != '' ) {
            $ProduktKontrola->ProduktKupowanie( $cechy ); 
          } else {
            $ProduktKontrola->ProduktKupowanie();
        }   
        
        // sprawdzi czy nie zostalo wylaczone kupowanie
        if ( $ProduktKontrola->zakupy['mozliwe_kupowanie'] == 'nie' ) {
             //
             $Akcja = 'usun';
             //
        }

        // jezeli ilosc w magazynie jest mniej niz w koszyku
        if ( $ProduktKontrola->zakupy['ilosc_magazyn'] < $KoncowaIlosc && MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $ProduktKontrola->info['kontrola_magazynu'] == 1 ) {
             //
             $KoncowaIlosc = $ProduktKontrola->zakupy['ilosc_magazyn'];
             $Akcja = 'przelicz';
             //
        }
        
        // jezeli ilosc jest mniejsza o minimalnej
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $KoncowaIlosc < $ProduktKontrola->zakupy['minimalna_ilosc'] && $ProduktKontrola->info['kontrola_magazynu'] == 1 ) {
        
            // jezeli jest mniej niz wymagana ilosc - usunie produkt z koszyka
            $Akcja = 'usun';
        
        }
        
        // jezeli ilosc jest wieksza niz maksymalna
        if ( $KoncowaIlosc > $ProduktKontrola->zakupy['maksymalna_ilosc'] && $ProduktKontrola->zakupy['maksymalna_ilosc'] > 0 ) {
             //
             $KoncowaIlosc = $ProduktKontrola->zakupy['maksymalna_ilosc'];
             $Akcja = 'przelicz';
             //
        }
        
        // jezeli jest przyrost ilosci
        if ( $ProduktKontrola->zakupy['przyrost_ilosci'] > 0 ) {
            //
            $Przyrost = $ProduktKontrola->zakupy['przyrost_ilosci'];
            //
            if ( $KoncowaIlosc >= $Przyrost ) {
                 //
                 if ( (int)(round((($KoncowaIlosc / $Przyrost) * 100), 2) / 100) != (round((($KoncowaIlosc / $Przyrost) * 100), 2) / 100) ) {
                     // 
                     $KoncowaIlosc = (int)(ceil($KoncowaIlosc / $Przyrost)) * $Przyrost;
                     $Akcja = 'przelicz';
                     //
                 }
                 //
            } else {
                 //
                 if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $ProduktKontrola->info['kontrola_magazynu'] == 1 ) {
                      //
                      $Akcja = 'usun';
                      //
                 }
                 //
            } 
            //
        }  
        
        if ( $KoncowaIlosc <= 0 ) {
             //
             $this->UsunZKoszyka( $id, $przelicz );
             return true;
             //
        }
        
        // jezeli jest dodawanie osobno do koszyka to usunie pozycje ktore nie spelniaja magazynu
        if ( $Akcja != '' && KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
             //
             foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
                
                if ( substr((string)$id, 0, strpos((string)$id, 'U')) == substr((string)$TablicaWartosci['id'], 0, strpos((string)$TablicaWartosci['id'], 'U')) ) {
                     $this->UsunZKoszyka( $TablicaWartosci['id'], $przelicz );
                }
                
             }
             // 
        } else {
             //
             if ( $Akcja == 'przelicz' ) {
                //
                $_SESSION['koszyk'][$id]['ilosc'] = $KoncowaIlosc;
                //
              } else if ( $Akcja == 'usun' ) {
                //
                $this->UsunZKoszyka( $id, $przelicz );
                //
             }
        }
        
        if ( isset($_SESSION['koszyk'][$id]) ) {
             //
             $_SESSION['koszyk'][$id]['ilosc'] = number_format( $_SESSION['koszyk'][$id]['ilosc'], $Przecinek, '.', '' );
             //
        }
        
        if ( $Akcja != '' ) {
             return true;
        }
    
    }
    
    // sprawdzanie ilosci produktow przy podgladzie zapisanego koszyka
    public function SprawdzIloscProduktuMagazynZapisanyKoszyk($id, $ilosc, $id_koszyka) {
      
        $KoncowaIlosc = $ilosc;

        // jezeli jest wlaczona opcja kazdego produktu osobno w koszyku to sprawdzi czy nie ma wiecej pozycji w zapisanym koszyku
        if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
             //
             $KoncowaIlosc = 0;
             //
             $zapytanie = "SELECT DISTINCT * FROM basket_save_products WHERE basket_id = '" . (int)$id_koszyka . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie); 
             //
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
               
                 while ($info = $sql->fetch_assoc()) {
                   
                    if ( substr((string)$id, 0, strpos((string)$id, 'U')) == substr((string)$info['products_id'], 0, strpos((string)$info['products_id'], 'U')) ) {
                         $KoncowaIlosc += $info['basket_quantity'];
                    }               
                   
                 }
                 
                 unset($info);
                 
             }
             //
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);
             //                     
        }          

        $Akcja = false;
        
        $ProduktKontrola = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );

        // czy produkt ma cechy
        $cechy = '';
        
        if ( strpos((string)$id, "x") > -1 ) {
            // wyciaga same cechy z produktu
            $cechy = substr((string)$id, strpos((string)$id, "x"), strlen((string)$id) );
        }   

        if ( $cechy != '' ) {
            $ProduktKontrola->ProduktKupowanie( $cechy ); 
          } else {
            $ProduktKontrola->ProduktKupowanie();
        }   
        
        // sprawdzi czy nie zostalo wylaczone kupowanie
        if ( $ProduktKontrola->zakupy['mozliwe_kupowanie'] == 'nie' ) {
             //
             $Akcja = true;
             //
        }        

        // jezeli ilosc w magazynie jest mniej niz w koszyku
        if ( $ProduktKontrola->zakupy['ilosc_magazyn'] < $KoncowaIlosc && MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $ProduktKontrola->info['kontrola_magazynu'] == 1 ) {
             //
             $Akcja = true;
             //
        }
        
        // jezeli ilosc jest mniejsza o minimalnej
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $KoncowaIlosc < $ProduktKontrola->zakupy['minimalna_ilosc'] && $ProduktKontrola->info['kontrola_magazynu'] == 1 ) {
        
            $Akcja = true;
        
        }
        
        // jezeli ilosc jest wieksza niz maksymalna
        if ( $KoncowaIlosc > $ProduktKontrola->zakupy['maksymalna_ilosc'] && $ProduktKontrola->zakupy['maksymalna_ilosc'] > 0 ) {
             //
             $Akcja = true;
             //
        }
        
        // jezeli jest przyrost ilosci
        if ( $ProduktKontrola->zakupy['przyrost_ilosci'] > 0 ) {
            //
            $Przyrost = $ProduktKontrola->zakupy['przyrost_ilosci'];
            //
            if ( (int)(round((($KoncowaIlosc / $Przyrost) * 100), 2) / 100) != (round((($KoncowaIlosc / $Przyrost) * 100), 2) / 100) ) {
                // 
                $Akcja = true;
                //
            }
            //
        }  
        
        return $Akcja;
    
    }    

    // czysci sesje koszyka przy wylogowaniu - tylko dla zalogowanych klientow
    public function WyczyscSesjeKoszykZalogowanego() {
        //  
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            $_SESSION['koszyk'] = array(); 
            //           
        }
        //
    }      
    
    // sprawdza czy produkt jest w koszyku sesji
    public function CzyJestWKoszyku( $id ) {
        //
        // czy juz nie ma produktu w koszyku
        $ProduktJest = false;
        foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
            //
            if ( $id == $TablicaWartosci['id'] ) {
                $ProduktJest = true;
            }
            //
        }
        //
        return $ProduktJest;
    }

    public function DodajDoKoszyka( $id, $ilosc, $komentarz, $pola_txt, $rodzaj_ceny = 'baza', $cena = 0 ) {
        //
        if ( $rodzaj_ceny == 'baza' ) {
            //
            if ($this->CzyJestWKoszyku($id) == false || KOSZYK_SPOSOB_DODAWANIA == 'tak') {
                //
                $LosowaWartosc = '';
                //
                if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
                     //
                     $LosowaWartosc = 'U' . rand(1,99999);
                     //
                }
                //
                $_SESSION['koszyk'][$id . $LosowaWartosc] = array('id'          => $id . $LosowaWartosc,
                                                                  'ilosc'       => $ilosc,
                                                                  'komentarz'   => $komentarz,  
                                                                  'pola_txt'    => $pola_txt,
                                                                  'rodzaj_ceny' => $rodzaj_ceny);
                //
             } else {
                //
                $_SESSION['koszyk'][$id]['ilosc'] += $ilosc;
                if ( isset($_SESSION['koszyk'][$id]['komentarz']) && $_SESSION['koszyk'][$id]['komentarz'] != '' ) {
                     $_SESSION['koszyk'][$id]['komentarz'] .= ' / ' . $komentarz;
                } else {
                     $_SESSION['koszyk'][$id]['komentarz'] .= $komentarz;
                }
                $_SESSION['koszyk'][$id]['pola_txt'] = $pola_txt;
                //
            }
            //
        }
        if ( $rodzaj_ceny == 'gratis' ) {
            //
            $_SESSION['koszyk'][$id . '-gratis'] = array('id'          => $id . '-gratis',
                                                         'ilosc'       => $ilosc,
                                                         'komentarz'   => '',   
                                                         'pola_txt'    => '',
                                                         'rodzaj_ceny' => 'gratis',
                                                         'cena_brutto' => $cena);
            //
        }
        if ( $rodzaj_ceny == 'inna' ) {
            //
            $_SESSION['koszyk'][$id . '-inna'] = array('id'          => $id . '-inna',
                                                       'ilosc'       => $ilosc,
                                                       'komentarz'   => '',   
                                                       'pola_txt'    => '',
                                                       'rodzaj_ceny' => 'inna',
                                                       'cena_brutto' => $cena);
            //
        } 
        if ( $rodzaj_ceny == 'wariant-ubezpieczenie' ) {
            //
            // ubezpieczenie easyprotect
            $_SESSION['koszyk'][$id]['wariant']['ubezpieczenie'] = array('ilosc'          => $ilosc,
                                                                         'ile_lat'        => $komentarz,
                                                                         'cena_brutto'    => $cena,
                                                                         'cena_netto'     => round(($cena / 1.23), 2),
                                                                         'rodzaj_wariant' => 'ubezpieczenie');
            //
        }            
        $this->PrzeliczKoszyk();
        //
        unset($ProduktJest);
        //  
    } 
    
    public function AktualizujKomentarz( $id, $komentarz ) {
        //
        $_SESSION['koszyk'][$id]['komentarz'] = $komentarz;
        //
    }
    
    public function ZmienIloscKoszyka( $id, $ilosc, $przeliczaj = true ) {
        //
        $_SESSION['koszyk'][$id]['ilosc'] = $ilosc;
        //
        if ( $przeliczaj == true ) {
             $this->PrzeliczKoszyk();
        }
        //
    }
    
    public function UsunZKoszyka( $id, $przeliczaj = true ) {
        //
        global $filtr;
        //
        $id = $filtr->process($id);
        //
        unset($_SESSION['koszyk'][$id]);
        //
        // usuwa z bazy jezeli jest zalogowany klient
        if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) { // && $_SESSION['gosc'] == '0'
            //
            $GLOBALS['db']->delete_query('customers_basket' , "products_id = '" . $id . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");	   
            //
        } else {
            //
            $GLOBALS['db']->delete_query('customers_basket' , "products_id = '" . $id . "' and session_id = '" . (string)session_id() . "'");	   
            //
        }
        //
        if ( $przeliczaj == true ) {
             $this->PrzeliczKoszyk();
        }
        // 
    } 

    public function PrzeliczKoszyk( $sesja_usun = true ) {
        //
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            if ( isset($TablicaZawartosci['id']) ) {
            
                $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) );
                
                if ( $Produkt->CzyJestProdukt ) {
                
                    // definicja czy jest tylko akcesoria dodatkowe
                    $TylkoAkcesoria = false;
                
                    // sprawdzi czy produkt nie jest jako tylko akcesoria dodatkowe i czy jest w koszyku produkt z ktorym mozna go kupic
                    if ( $Produkt->info['status_akcesoria'] == 'tak' ) {
                         //
                         $TylkoAkcesoria = true;
                         //
                         // tablica do id produktow i kategorii ktore maja akcesoria dodatkowe o danym id produktu
                         $TablicaIdProduktow = array();
                         $TablicaIdKategorie = array();
                         //
                         $zapytanie = "select distinct pacc_type, pacc_products_id_master from products_accesories where pacc_products_id_slave = '" . $Produkt->info['id'] . "'";
                         $sql = $GLOBALS['db']->open_query($zapytanie);    
                         //
                         if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                             //
                             while ($info = $sql->fetch_assoc()) {
                                    if ( $info['pacc_type'] == 'produkt' ) {
                                         $TablicaIdProduktow[] = $info['pacc_products_id_master'];
                                    }
                                    if ( $info['pacc_type'] == 'kategoria' ) {
                                         $TablicaIdKategorie[] = $info['pacc_products_id_master'];
                                    }      
                             }
                             //
                             unset($info);
                             //
                         }
                         //
                         $GLOBALS['db']->close_query($sql);
                         unset($zapytanie);  
                         //
                         if ( count($TablicaIdProduktow) > 0 ) {
                              //
                              // sprawdzi czy w koszyku jest produkt z ktorym mozna kupic ten produkt
                              foreach ($_SESSION['koszyk'] AS $TablicaZawartosciAkcesoria) {
                                 //
                                 $IdProduktuKoszyka = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosciAkcesoria['id'] );
                                 //
                                 if ( in_array($IdProduktuKoszyka, $TablicaIdProduktow) ) {
                                      $TylkoAkcesoria = false;
                                      break;
                                 }
                                 //
                                 unset($IdProduktuKoszyka);
                                 //
                              }
                              //
                         }
                         //
                         if ( count($TablicaIdKategorie) > 0 ) {
                              //
                              // sprawdzi czy w koszyku jest produkt z ktorym mozna kupic ten produkt
                              foreach ($_SESSION['koszyk'] AS $TablicaZawartosciAkcesoria) {
                                 //
                                 $IdKategoriiProduktu = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosciAkcesoria['id'] ) );
                                 //
                                 foreach ( $IdKategoriiProduktu as $TmpIdKat ) {
                                      //
                                      if ( in_array($TmpIdKat, $TablicaIdKategorie) ) {
                                           $TylkoAkcesoria = false;
                                           break;
                                      }
                                      //
                                }
                                 //
                                 unset($IdKategoriiProduktu);
                                 //
                              }
                              //
                         }
                         //
                         unset($TablicaIdKategorie, $TablicaIdProduktow);
                         //                     
                    }           

                    // jezeli produkt moze byc tylko jako acesoria dodatkowe a nie ma produktu dla ktorego jest przypisany
                    if ( $TylkoAkcesoria == true ) {
                    
                         $this->UsunZKoszyka( $TablicaZawartosci['id'] );
                         
                      } else {
                
                        // elementy kupowania
                        $Produkt->ProduktKupowanie();
                        //
                                    
                        //
                        // jezeli do koszyka jest dodawany normalny produkt
                        if ( $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) {

                            $WartoscCechBrutto = 0;
                            $WartoscCechNetto = 0;
                            $WagaCechy = 0;
                            $Znizka = 1;
                            
                            // przeliczy cechy tylko jezeli produkt nie jest za PUNKTY
                            if ( $Produkt->info['tylko_za_punkty'] == 'nie' ) {
                            
                                // jezeli produkt ma cechy oraz cechy wplywaja na wartosc produktu to musi ustalic ceny cech
                                if ( strpos((string)$TablicaZawartosci['id'], "x") > -1 && $Produkt->info['typ_cech'] == 'cechy' ) {
                                    //
                                    $DodatkoweParametryCechy = $Produkt->ProduktWartoscCechy( $TablicaZawartosci['id'] );
                                    //
                                    $WartoscCechBrutto = $DodatkoweParametryCechy['brutto'];
                                    $WartoscCechNetto = $DodatkoweParametryCechy['netto'];
                                    $WagaCechy = $DodatkoweParametryCechy['waga'];
                                    //
                                    unset($DodatkoweParametryCechy);
                                    //
                                    // lub jezeli sa stale ceny dla kombinacji cech
                                } else if ( strpos((string)$TablicaZawartosci['id'], "x") > -1 && $Produkt->info['typ_cech'] == 'ceny' ) {
                                    //
                                    $DodatkoweCenyCech = $Produkt->ProduktWartoscCechyCeny( $TablicaZawartosci['id'] );
                                    //
                                    if ( $DodatkoweCenyCech['netto'] > 0 ) {
                                         //
                                         $Produkt->info['cena_netto_bez_formatowania'] = $DodatkoweCenyCech['netto'];
                                         $Produkt->info['cena_brutto_bez_formatowania'] = $DodatkoweCenyCech['brutto'];
                                         $Produkt->info['vat_bez_formatowania'] = $DodatkoweCenyCech['brutto'] - $DodatkoweCenyCech['netto']; 
                                         //
                                    }
                                    //
                                    $WagaCechy = $DodatkoweCenyCech['waga'];
                                    //
                                    unset($DodatkoweCenyCech);
                                    //
                                }

                            }
                            
                            //
                            $IloscSzt = $TablicaZawartosci['ilosc'];      
                            //
                            // znizki zalezne od ilosci
                            // warunki czy stosowac znizki od ilosci
                            $StosujZnizki = true;
                            
                            // jezeli produkt jest jako produkt dnia
                            if ( $Produkt->info['produkt_dnia'] == 'tak' ) {
                                $StosujZnizki = false;
                            }                        
                            
                            // jezeli nie ma sumowania rabatow
                            if ( ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'nie' && $Produkt->info['rabat_produktu'] != 0 ) {
                                $StosujZnizki = false;
                            }

                            // jezeli znizki zalezne od ilosci produktow w koszyku sa wlaczone dla promocji lub produkt nie jest w promocji
                            if ( ZNIZKI_OD_ILOSCI_PROMOCJE == 'nie' && $Produkt->ikonki['promocja'] == '1' && $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                                $StosujZnizki = false;                
                            }
                            
                            // jezeli produkt jest tylko za PUNKTY to nie ma znizek
                            if ( $Produkt->info['tylko_za_punkty'] == 'tak' ) {
                                $StosujZnizki = false;                
                            }   

                            $BylaZnizka = false;
                            
                            if ( $StosujZnizki == true ) {
                                            
                                $IloscSztDoZnizek = 0;
                                
                                // jezeli produkty ze cechami maja byc traktowane jako osobne produkty
                                if ( ZNIZKI_OD_ILOSCI_PRODUKT_CECHY == 'nie' ) {
                                
                                    // ---------------------------------------------------------------------------
                                    // musi poszukac ile jest produktow z roznymi cechami i zsumowac produkty
                                    foreach ($_SESSION['koszyk'] AS $TablicaDoZnizek) {
                                        //
                                        if (isset($TablicaDoZnizek['id'])) {
                                            if (Funkcje::SamoIdProduktuBezCech($TablicaDoZnizek['id']) == Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id'])) {
                                                $IloscSztDoZnizek += $TablicaDoZnizek['ilosc'];
                                            }
                                        }
                                        //
                                    }
                                    // ---------------------------------------------------------------------------
                                    //
                                    
                                  } else {
                                  
                                    $IloscSztDoZnizek = $IloscSzt;
                                    
                                }
                                
                                $BylaZnizka = false;

                                if ($Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek ) > 0) {
                                    // jezeli jest procent to obliczy wskaznik dzielenia - jezeli cena to pobierze cene
                                    if ( $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                                         $Znizka = 1 - ($Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek ) / 100);
                                      } else {
                                         $Znizka = $Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek );
                                         if ( $Znizka <= 0 ) {
                                              $Znizka = 1;
                                          } else {
                                              $BylaZnizka = true;
                                         }
                                    }
                                }
                                //
                                unset($IloscSztDoZnizek);
                                //
                                
                            }

                            // jezeli nie ma znizki
                            if ($Znizka == 1 && $BylaZnizka == false) {
                                //
                                $CenaNetto = $Produkt->info['cena_netto_bez_formatowania'] + $WartoscCechNetto;
                                
                                if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                     //
                                     $CenaBrutto = $Produkt->info['cena_netto_bez_formatowania'] + $WartoscCechNetto;
                                     $Vat = 0;
                                     //
                                  } else {
                                     //
                                     $CenaBrutto = $Produkt->info['cena_brutto_bez_formatowania'] + $WartoscCechBrutto;
                                     $Vat = $Produkt->info['vat_bez_formatowania'];
                                     //
                                }
                                //
                            } else {
                                //
                                if ( $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                                    //
                                    if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                         //
                                         $CenaBrutto = round( (($Produkt->info['cena_netto_bez_formatowania'] + $WartoscCechNetto) * $Znizka), CENY_MIEJSCA_PO_PRZECINKU );     
                                         //
                                      } else {
                                         //
                                         $CenaBrutto = round( (($Produkt->info['cena_brutto_bez_formatowania'] + $WartoscCechBrutto) * $Znizka), CENY_MIEJSCA_PO_PRZECINKU );     
                                         //
                                     }
                                     //
                                }
                                if ( $Produkt->znizkiZalezneOdIlosciTyp == 'cena' ) {
                                     //
                                     // jezeli znizki od ilosci sa w formie cen cechy trzeba policzyc od ceny po znizkach
                                     $DodatkoweParametryCechy = $Produkt->ProduktWartoscCechy( $TablicaZawartosci['id'], $Znizka, round(($Znizka / (1 + ($Produkt->info['stawka_vat'] / 100))), CENY_MIEJSCA_PO_PRZECINKU ) );
                                     //
                                     $WartoscCechBrutto = $DodatkoweParametryCechy['brutto'];
                                     
                                     if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                          //
                                          $WartoscCechNetto = $DodatkoweParametryCechy['brutto'];
                                          //
                                      } else {
                                          //
                                          $WartoscCechNetto = $DodatkoweParametryCechy['netto'];
                                          //
                                     }
                                     //
                                     $WagaCechy = $DodatkoweParametryCechy['waga'];
                                     //
                                     unset($DodatkoweParametryCechy);
                                     //
                                     $CenaBrutto = round(($Znizka + $WartoscCechBrutto), CENY_MIEJSCA_PO_PRZECINKU );             
                                     //
                                }                            
                                //
                                if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                     //
                                     $CenaNetto = $CenaBrutto;
                                     //
                                } else {
                                     //
                                     $CenaNetto = round(($CenaBrutto / (1 + ($Produkt->info['stawka_vat'] / 100))), CENY_MIEJSCA_PO_PRZECINKU );
                                     //
                                }
                                
                                $Vat = $CenaBrutto - $CenaNetto;                             
                                //
                            }
                            //
                        }
                        
                        unset($BylaZnizka);
                        
                        // jezeli do koszyka jest dodawany gratis
                        if ( $TablicaZawartosci['rodzaj_ceny'] == 'gratis' ) {
                            //
                            $WagaCechy = 0;
                            $IloscSzt = $TablicaZawartosci['ilosc'];
                            //
                            if ( $TablicaZawartosci['cena_brutto'] > 0 ) {
                                  //
                                  $CenaBrutto = $TablicaZawartosci['cena_brutto'];
                                  //
                                  if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                       //
                                       $CenaNetto = $CenaBrutto;
                                       //
                                    } else {
                                       //
                                       $CenaNetto = round(($CenaBrutto / (1 + ($Produkt->info['stawka_vat'] / 100))), CENY_MIEJSCA_PO_PRZECINKU );
                                       //
                                  }
                                  //
                                  $Vat = $CenaBrutto - $CenaNetto;
                                  //
                              } else { 
                                  //
                                  $CenaBrutto = 0;
                                  $CenaNetto = 0;
                                  $Vat = 0;
                                  //
                            }
                            //
                        }
                        
                        if ( $TablicaZawartosci['rodzaj_ceny'] == 'inna' ) {
                             //
                             $IloscSzt = $TablicaZawartosci['ilosc'];
                             $CenaNetto = round(($TablicaZawartosci['cena_brutto'] / (1 + ($Produkt->info['stawka_vat'] / 100))), CENY_MIEJSCA_PO_PRZECINKU );
                             $CenaBrutto = $TablicaZawartosci['cena_brutto'];
                             $Vat = $CenaBrutto - $CenaNetto;
                             $WagaCechy = 0;
                             //
                        }

                        // usuwa wpis z koszyka sesji
                        unset($WartoscCechBrutto, $WartoscCechNetto);
                        
                        $DaneCechy = $Produkt->ProduktCechyNrKatalogowy( substr((string)$TablicaZawartosci['id'], strpos((string)$TablicaZawartosci['id'], "x") + 1, strlen((string)$TablicaZawartosci['id']) ) );

                        $NrKatalogowy = $DaneCechy['nr_kat'];
                        
                        $CzasWysylkiNazwa = '';
                        $CzasWysylkiDni = '';
                        
                        if ( isset($DaneCechy['czas_wysylki']['nazwa']) && isset($DaneCechy['czas_wysylki']['dni']) ) {
                             $CzasWysylkiNazwa = $DaneCechy['czas_wysylki']['nazwa'];
                             $CzasWysylkiDni = $DaneCechy['czas_wysylki']['dni'];
                        }
                        
                        $KodEan = '';
                        
                        if ( isset($DaneCechy['ean']) ) {
                             $KodEan = $DaneCechy['ean'];
                        }
                        
                        $ZdjecieProduktu = $Produkt->fotoGlowne['plik_zdjecia'];
                        
                        if ( isset($DaneCechy['zdjecie']) ) {
                             $ZdjecieProduktu = $DaneCechy['zdjecie'];
                        } 
            
                        // integracja z ubezpieczeniami easyprotect

                        $CenaNettoWariant = $CenaNetto;
                        $CenaBruttoWariant = $CenaBrutto;
            
                        $DodatkoweWarianty = array();

                        // sprawdzi dodatkowe warunki dla ubezpieczenia

                        if ( $_SESSION['domyslnaWaluta']['kod'] != 'PLN' || $CenaBrutto < 200 || $CenaBrutto > 21999 || $Produkt->info['stawka_vat'] != 23 || $Produkt->info['zestaw'] == 'tak' ) {
                             //
                             if ( isset($TablicaZawartosci['wariant']['ubezpieczenie']) ) {
                                  //
                                  $TablicaZawartosci['wariant']['ubezpieczenie'] = array();
                                  //
                             }
                             //
                        } else {
                             //
                             // przeliczy koszyk dla easyprotect
                             if ( isset($TablicaZawartosci['wariant']['ubezpieczenie']) && count($TablicaZawartosci['wariant']['ubezpieczenie']) > 0 ) {
                                  //
                                  $KosztUbezpieczenia = IntegracjeZewnetrzne::EasyProtectKoszykKwota($CenaBrutto, (int)$TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] * 12);                         
                                  // 
                                  if ( $KosztUbezpieczenia > 0 ) {
                                       //
                                       $TablicaZawartosci['wariant']['ubezpieczenie']['cena_brutto'] = (float)$KosztUbezpieczenia;
                                       $TablicaZawartosci['wariant']['ubezpieczenie']['cena_netto'] = round(((float)$KosztUbezpieczenia / 1.23), 2);
                                       //
                                  }
                                  //
                                  unset($KosztUbezpieczenia);
                                  //
                             }
                             //
                             if ( isset($TablicaZawartosci['wariant']) ) {
                                  //
                                  foreach ( $TablicaZawartosci['wariant'] as $KluczWariant => $Wariant ) {
                                     //
                                     if ( isset($Wariant['cena_netto']) && isset($Wariant['cena_brutto']) ) {
                                          //
                                          $DodatkoweWarianty[ $KluczWariant ] = $Wariant;
                                          //
                                          $CenaNettoWariant += $Wariant['cena_netto'];
                                          $CenaBruttoWariant += $Wariant['cena_brutto'];
                                          //
                                     }
                                     //
                                  }
                                  //
                             }   
                             //
                        }

                        //
                        // dodaje na nowo do koszyka sesji przeliczone wartosci
                        $_SESSION['koszyk'][$TablicaZawartosci['id']] = array('id'                         => $TablicaZawartosci['id'],
                                                                              'nazwa'                      => $Produkt->info['nazwa'],
                                                                              'zdjecie'                    => $ZdjecieProduktu,
                                                                              'ilosc'                      => $IloscSzt,
                                                                              'cena_netto'                 => $CenaNettoWariant,
                                                                              'cena_brutto'                => $CenaBruttoWariant,
                                                                              'cena_netto_bez_wariantow'   => $CenaNetto,
                                                                              'cena_brutto_bez_wariantow'  => $CenaBrutto,
                                                                              'cena_bazowa_netto'          => (($TablicaZawartosci['rodzaj_ceny'] != 'baza') ? $CenaNetto : $Produkt->info['cena_netto_bez_formatowania']),
                                                                              'cena_bazowa_brutto'         => (($TablicaZawartosci['rodzaj_ceny'] != 'baza') ? $CenaBrutto : $Produkt->info['cena_brutto_bez_formatowania']),
                                                                              'cena_punkty'                => (($Produkt->info['tylko_za_punkty'] == 'tak') ? (int)$Produkt->info['cena_w_punktach'] : 0),
                                                                              'zakup_za_punkty'            => $Produkt->info['zakup_za_punkty'],
                                                                              'status_akcesoria'           => $Produkt->info['status_akcesoria'],
                                                                              'vat'                        => $Vat,
                                                                              'vat_stawka'                 => $Produkt->info['stawka_vat'],
                                                                              'waga'                       => $Produkt->info['waga'] + $WagaCechy,
                                                                              'waga_szerokosc'             => (int)$Produkt->info['waga_szerokosc'],
                                                                              'waga_wysokosc'              => (int)$Produkt->info['waga_wysokosc'],
                                                                              'waga_dlugosc'               => (int)$Produkt->info['waga_dlugosc'],
                                                                              'promocja'                   => (($Produkt->ikonki['promocja'] == 1 && $Produkt->info['tylko_za_punkty'] == 'nie') ? 'tak' : 'nie'),
                                                                              'darmowa_wysylka'            => $Produkt->info['darmowa_wysylka'], 
                                                                              'wykluczona_darmowa_wysylka' => $Produkt->info['wykluczona_darmowa_wysylka'],
                                                                              'gabaryt'                    => $Produkt->info['gabaryt'],
                                                                              'osobna_paczka'              => $Produkt->info['osobna_paczka'],
                                                                              'osobna_paczka_ilosc'        => $Produkt->info['osobna_paczka_ilosc'],
                                                                              'wysylki'                    => $Produkt->info['dostepne_wysylki'],
                                                                              'koszt_wysylki'              => $Produkt->info['koszt_wysylki'],
                                                                              'koszt_wysylki_ilosc'        => $Produkt->info['koszt_wysylki_ilosc'],
                                                                              'inpost_gabaryt'             => $Produkt->info['inpost_gabaryt'],
                                                                              'inpost_ilosc'               => $Produkt->info['inpost_ilosc'],
                                                                              'wykluczony_punkt_odbioru'   => $Produkt->info['wykluczony_punkt_odbioru'],
                                                                              'nr_katalogowy'              => $NrKatalogowy,
                                                                              'ean'                        => $KodEan,
                                                                              'czas_wysylki_nazwa'         => $CzasWysylkiNazwa,
                                                                              'czas_wysylki_dni'           => $CzasWysylkiDni,
                                                                              'komentarz'                  => $TablicaZawartosci['komentarz'],
                                                                              'pola_txt'                   => $TablicaZawartosci['pola_txt'],
                                                                              'rodzaj_ceny'                => $TablicaZawartosci['rodzaj_ceny'],
                                                                              'id_kategorii'               => $Produkt->info['id_kategorii'],
                                                                              'wariant'                    => $DodatkoweWarianty,
                                                                              'zestaw'                     => $Produkt->info['zestaw']);
                                                                              
                        unset($DaneCechy, $CzasWysylkiNazwa, $CzasWysylkiDni, $ZdjecieProduktu, $DodatkoweWarianty);

                        // jezeli klient jest zalogowany to aktualizuje baze
                        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) { // && $_SESSION['gosc'] == '0'
                            //
                            // nie zapisuje koszyka jezeli produkt jest za punkty
                            if ($TablicaZawartosci['rodzaj_ceny'] == 'baza') {
                                //
                                // musi sprawdzic czy produkt jest juz w bazie
                                $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE products_id = '" . $TablicaZawartosci['id'] . "' and (customers_id = '" . (int)$_SESSION['customer_id'] . "' or session_id = '" . (string)session_id() . "')";
                                $sql = $GLOBALS['db']->open_query($zapytanie);  
                                //
                                if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                                  
                                    $infc = $sql->fetch_assoc();
                                  
                                    // aktualizuje produkt
                                    $pola = array(
                                            array('products_id',$TablicaZawartosci['id']),
                                            array('customers_id',(int)$_SESSION['customer_id']),
                                            array('customers_ip',((isset($_SESSION['ippp'])) ? $_SESSION['ippp'] : '')),
                                            array('session_id',(string)session_id()),
                                            array('customers_basket_quantity',(float)$IloscSzt),
                                            array('products_price',(float)$CenaNetto),
                                            array('products_price_tax',(float)$CenaBrutto),
                                            array('products_tax',(float)$Vat),
                                            array('products_weight',(float)$Produkt->info['waga']),
                                            array('products_comments',$TablicaZawartosci['komentarz']),
                                            array('products_text_fields',$TablicaZawartosci['pola_txt']),
                                            array('products_model',$NrKatalogowy),
                                            array('products_ean',$KodEan),
                                            array('price_type',$TablicaZawartosci['rodzaj_ceny']));
                                            
                                    if ( $infc['customers_id'] > 0 && (int)$_SESSION['customer_id'] == $infc['customers_id'] ) {
                                         //
                                         $GLOBALS['db']->update_query('customers_basket' , $pola, "products_id = '" . $TablicaZawartosci['id'] ."' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");	
                                         //
                                    } else {
                                         //
                                         $GLOBALS['db']->update_query('customers_basket' , $pola, "products_id = '" . $TablicaZawartosci['id'] ."' and session_id = '" . (string)session_id() . "'");	
                                         //
                                    }
                                    unset($pola);                       
                                    //         
                                    
                                } else {
                                  
                                    // jezeli go nie ma musi go dodac
                                    $pola = array(
                                            array('products_id',$TablicaZawartosci['id']),
                                            array('customers_id',(int)$_SESSION['customer_id']),
                                            array('customers_ip',((isset($_SESSION['ippp'])) ? $_SESSION['ippp'] : '')),
                                            array('session_id',(string)session_id()),                                        
                                            array('customers_basket_quantity',(float)$IloscSzt),
                                            array('products_price',(float)$CenaNetto),
                                            array('products_price_tax',(float)$CenaBrutto),
                                            array('products_tax',(float)$Vat),
                                            array('products_weight',(float)$Produkt->info['waga']),
                                            array('products_comments',$TablicaZawartosci['komentarz']),
                                            array('products_text_fields',$TablicaZawartosci['pola_txt']),
                                            array('products_model',$NrKatalogowy),
                                            array('products_ean',$KodEan),
                                            array('customers_basket_date_added','now()'),
                                            array('price_type',$TablicaZawartosci['rodzaj_ceny']));

                                    $GLOBALS['db']->insert_query('customers_basket' , $pola);	
                                    unset($pola);
                                    //
                                    
                                }
                                //        
                                $GLOBALS['db']->close_query($sql);
                                unset($zapytanie);
                                //
                            }
                            //
                        } else {
                            //
                            // jezeli klient nie jest zalogowany
                            //
                            // nie zapisuje koszyka jezeli produkt jest za punkty
                            if ($TablicaZawartosci['rodzaj_ceny'] == 'baza') {
                                //
                                // musi sprawdzic czy produkt jest juz w bazie
                                $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE products_id = '" . $TablicaZawartosci['id'] . "' and session_id = '" . (string)session_id() . "'";
                                $sql = $GLOBALS['db']->open_query($zapytanie);   
                                //
                                if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                                  
                                    // aktualizuje produkt
                                    $pola = array(
                                            array('products_id',$TablicaZawartosci['id']), // array('customers_id',0),                                        
                                            array('customers_ip',((isset($_SESSION['ippp'])) ? $_SESSION['ippp'] : '')),
                                            array('session_id',(string)session_id()),
                                            array('customers_basket_quantity',(float)$IloscSzt),
                                            array('products_price',(float)$CenaNetto),
                                            array('products_price_tax',(float)$CenaBrutto),
                                            array('products_tax',(float)$Vat),
                                            array('products_weight',(float)$Produkt->info['waga']),
                                            array('products_comments',$TablicaZawartosci['komentarz']),
                                            array('products_text_fields',$TablicaZawartosci['pola_txt']),
                                            array('products_model',$NrKatalogowy),
                                            array('products_ean',$KodEan),
                                            array('price_type',$TablicaZawartosci['rodzaj_ceny']));
                                            
                                    $GLOBALS['db']->update_query('customers_basket' , $pola, "products_id = '" . $TablicaZawartosci['id'] ."' and session_id = '" . (string)session_id() . "'");	
                                    unset($pola);                       
                                    //         
                                    
                                } else {
                                  
                                    // jezeli go nie ma musi go dodac
                                    $pola = array(
                                            array('products_id',$TablicaZawartosci['id']),
                                            array('customers_id',0),
                                            array('customers_ip',((isset($_SESSION['ippp'])) ? $_SESSION['ippp'] : '')),
                                            array('session_id',(string)session_id()),                                        
                                            array('customers_basket_quantity',(float)$IloscSzt),
                                            array('products_price',(float)$CenaNetto),
                                            array('products_price_tax',(float)$CenaBrutto),
                                            array('products_tax',(float)$Vat),
                                            array('products_weight',(float)$Produkt->info['waga']),
                                            array('products_comments',$TablicaZawartosci['komentarz']),
                                            array('products_text_fields',$TablicaZawartosci['pola_txt']),
                                            array('products_model',$NrKatalogowy),
                                            array('products_ean',$KodEan),
                                            array('customers_basket_date_added','now()'),
                                            array('price_type',$TablicaZawartosci['rodzaj_ceny']));

                                    $GLOBALS['db']->insert_query('customers_basket' , $pola);	
                                    unset($pola);
                                    //
                                    
                                }
                                //        
                                $GLOBALS['db']->close_query($sql);
                                unset($zapytanie);
                                //
                            }
                            //
                        }
                        
                    }
                    
                    unset($TylkoAkcesoria, $NrKatalogowy, $CenaNetto, $CenaBrutto, $Vat, $WagaCechy);
                    
                } else {
                
                    $this->UsunZKoszyka( $TablicaZawartosci['id'] );
                
                }
                //
                unset($Produkt);
            
            }
            //
        }
        //
        // sprawdzi czy nie trzeba skasowac jakis gratisow jezeli zmienila sie wartosc koszyka
        $JakieSaGratisy = Gratisy::TablicaGratisow( 'nie' );
        $GratisyLista = array();
        $GratisTylkoJeden = '';
        //   
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            if ( $TablicaZawartosci['rodzaj_ceny'] == 'gratis' ) {
                //
                if (!isset($JakieSaGratisy[ Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) ])) {
                    $this->UsunZKoszyka($TablicaZawartosci['id']);
                }
                //
                if ( isset($JakieSaGratisy[ Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) ]) ) {
                    //
                    if ( (int)$JakieSaGratisy[ Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) ]['tylko_jeden'] == 1 ) {
                         $GratisTylkoJeden = $TablicaZawartosci['id'];
                    }
                    //
                    $GratisyLista[ $TablicaZawartosci['id'] ] = $JakieSaGratisy[ Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) ];
                    //
                }
                //
            }
            //
        }
        //
        if ( $GratisTylkoJeden != '' ) {
             //
             foreach ( $GratisyLista as $Klucz => $Tab ) {
                 //
                 if ( $Klucz != $GratisTylkoJeden ) {
                      $this->UsunZKoszyka($Klucz);
                 }
                 //
             }
             //
        }
        //
        unset($GratisyLista, $GratisTylkoJeden);
        //

        if ( isset($_SESSION['rodzajDostawy']) && isset($_SESSION['rodzajPlatnosci']) && $sesja_usun == true ) {

            $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);

            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

            $wysylki = new Wysylki( $_SESSION['krajDostawy']['kod'] );
            $tablicaWysylek = $wysylki->wysylki;
            $WysylkaID = $_SESSION['rodzajDostawy']['wysylka_id'];
            
            if ( isset($tablicaWysylek[$WysylkaID]) && count($tablicaWysylek[$WysylkaID]) > 0 ) {
            
                if ( isset($_SESSION['rodzajDostawyKoszyk']) ) {
                    if ( $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_paczkaruch' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_bliskapaczka' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_dhlparcelshop' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_dpdpickup' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_glspickup' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_inpost_weekend' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_inpost_eko' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_inpost_international' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_pocztapunkt' &&
                         $tablicaWysylek[$WysylkaID]['klasa'] != 'wysylka_inpost' ) {
                            unset($_SESSION['rodzajDostawyKoszyk']);
                    }
                }

                unset($_SESSION['rodzajDostawy']);
                $_SESSION['rodzajDostawy'] = array('wysylka_id' => $tablicaWysylek[$WysylkaID]['id'],
                                                   'wysylka_klasa' => $tablicaWysylek[$WysylkaID]['klasa'],
                                                   'wysylka_koszt' => $tablicaWysylek[$WysylkaID]['wartosc'],
                                                   'wysylka_nazwa' => $tablicaWysylek[$WysylkaID]['text'],
                                                   'wysylka_vat_id' => $tablicaWysylek[$WysylkaID]['vat_id'],
                                                   'wysylka_vat_stawka' => $tablicaWysylek[$WysylkaID]['vat_stawka'],    
                                                   'wysylka_kod_gtu' => $tablicaWysylek[$WysylkaID]['kod_gtu'],
                                                   'dostepne_platnosci' => $tablicaWysylek[$WysylkaID]['dostepne_platnosci'] );

                $platnosci = new Platnosci( $_SESSION['rodzajDostawy']['wysylka_id'] );
                $tablicaPlatnosci = $platnosci->platnosci;
                $PlatnoscID = $_SESSION['rodzajPlatnosci']['platnosc_id'];
                unset($_SESSION['rodzajPlatnosci']);
                if ( isset($_SESSION['KanalyPlatnosciComfino']) ) {
                    unset($_SESSION['KanalyPlatnosciComfino']);
                }
                
                if ( isset($tablicaPlatnosci[$PlatnoscID]['id']) ) {
                    $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $tablicaPlatnosci[$PlatnoscID]['id'],
                                                         'platnosc_klasa' => $tablicaPlatnosci[$PlatnoscID]['klasa'],
                                                         'platnosc_koszt' => $tablicaPlatnosci[$PlatnoscID]['wartosc'],
                                                         'platnosc_nazwa' => $tablicaPlatnosci[$PlatnoscID]['text'],
                                                         'platnosc_punkty' => ( isset($tablicaPlatnosci[$PlatnoscID]['punkty']) ? $tablicaPlatnosci[$PlatnoscID]['punkty'] : 'nie' ),
                                                         'platnosc_kanal' => ( isset($tablicaPlatnosci[$PlatnoscID]['kanal_platnosci']) ? $tablicaPlatnosci[$PlatnoscID]['kanal_platnosci'] : '' ),
                    );
                }
                
            }
        }

    }

    public function ZawartoscKoszyka() {
        //
        $WartoscKoszykaNetto = 0;
        $WartoscKoszykaBrutto = 0;
        $WartoscKoszykaVat = 0;
        $IloscProduktowKoszyka = 0;
        $WagaProduktowKoszyka = 0;
        $IloscProduktowKoszykaJednostkowa = 0;
        //
        $WartoscKoszykaNettoInne = 0;
        $WartoscKoszykaBruttoInne = 0;
        $WartoscKoszykaVatInne = 0;
        $IloscProduktowKoszykaInne = 0;
        //
        $WartoscProduktowZaPunkty = 0;
        //
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            if ( isset($TablicaZawartosci['cena_brutto']) && isset($TablicaZawartosci['ilosc']) && isset($TablicaZawartosci['waga']) ) {
              
                $SumaBrutto = $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
                $SumaNetto = $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'];
                $SumaVat = $SumaBrutto - $SumaNetto;
                //
                $WagaProduktowKoszyka += $TablicaZawartosci['waga'] * $TablicaZawartosci['ilosc'];
                //
                $WartoscKoszykaNetto += $SumaNetto;
                $WartoscKoszykaBrutto += $SumaBrutto;
                $WartoscKoszykaVat += $SumaVat;
                $IloscProduktowKoszyka += $TablicaZawartosci['ilosc'];
                //
                $WartoscProduktowZaPunkty += $TablicaZawartosci['cena_punkty'] * $TablicaZawartosci['ilosc'];
                //
                unset($SumaBrutto, $SumaNetto, $SumaVat);
                
                // suma innych produktow (np gratisow)
                if ( $TablicaZawartosci['rodzaj_ceny'] != 'baza' ) {
                    //
                    $SumaBrutto = $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
                    $SumaNetto = $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'];
                    $SumaVat = $SumaBrutto - $SumaNetto;
                    //
                    $WartoscKoszykaNettoInne += $SumaNetto;
                    $WartoscKoszykaBruttoInne += $SumaBrutto;
                    $WartoscKoszykaVatInne += $SumaVat;
                    //
                    $IloscProduktowKoszykaInne += $TablicaZawartosci['ilosc'];
                    //
                    unset($SumaBrutto, $SumaNetto, $SumaVat);
                }

                $IloscProduktowKoszykaJednostkowa += 1;
                //
            }
            //
        }
        //
        // wynik z _baza sa to produkty wg cen z bazy - odliczone np ceny gratisow - potrzebne do obliczania np gratisow
        $Wynik = array('netto'             => $WartoscKoszykaNetto,
                       'brutto'            => $WartoscKoszykaBrutto,
                       'wartosc_pkt'       => $WartoscProduktowZaPunkty,
                       'vat'               => $WartoscKoszykaVat,
                       'ilosc'             => $IloscProduktowKoszyka,
                       'ilosc_jednostkowa' => $IloscProduktowKoszykaJednostkowa,
                       'waga'              => $WagaProduktowKoszyka,
                       'ilosc_baza'        => $IloscProduktowKoszyka - $IloscProduktowKoszykaInne,
                       'netto_baza'        => $WartoscKoszykaNetto - $WartoscKoszykaNettoInne,
                       'brutto_baza'       => $WartoscKoszykaBrutto - $WartoscKoszykaBruttoInne,
                       'vat_baza'          => $WartoscKoszykaVat - $WartoscKoszykaVatInne);
        //
        unset($WartoscKoszykaNetto, $WartoscKoszykaBrutto, $WartoscProduktowZaPunkty, $WartoscKoszykaVat, $IloscProduktowKoszyka, $WagaProduktowKoszyka, $WartoscKoszykaNettoInne, $WartoscKoszykaBruttoInne, $WartoscKoszykaVatInne, $IloscProduktowKoszykaInne);
        //
        return $Wynik;
        //
    }
    
    public function KoszykIloscProduktow() {
        //
        $ZawartoscKoszyka = $this->ZawartoscKoszyka();
        return $ZawartoscKoszyka['ilosc'];
        //
    }
    
    public function KoszykWartoscProduktow() {
        //
        $ZawartoscKoszyka = $this->ZawartoscKoszyka();
        return $ZawartoscKoszyka['brutto'];
        //
    } 

    public function KoszykWartoscProduktowZaPunkty() {
        //
        $ZawartoscKoszyka = $this->ZawartoscKoszyka();
        return $ZawartoscKoszyka['wartosc_pkt'];
        //
    }     
    
    // zapisanie koszyka
    public function KoszykZapisz($nazwa = '', $opis = '', $uwagi = '') {
      
        global $filtr;
        
        if ( KOSZYK_ZAPIS == 'tak' ) {

            $CzasZapisanegoKoszyka = time();
            $IdZapisanegoKoszyka = 0;

            // dane glowne zapisu
            $pola = array(
                    array('customers_id',((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) ? (int)$_SESSION['customer_id'] : '')),
                    array('basket_code',$CzasZapisanegoKoszyka),
                    array('basket_name',$nazwa),
                    array('basket_description',$opis),
                    array('basket_comment',$uwagi),
                    array('basket_date_added','now()'));

            $GLOBALS['db']->insert_query('basket_save' , $pola);	
            unset($pola); 
            
            $IdZapisanegoKoszyka = $GLOBALS['db']->last_id_query();

            foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
              
                // dodaje tylko same produkty - bez gratisow
                if ( $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) {
                  
                    // dane produktow
                    $pola = array(
                            array('basket_id',(int)$IdZapisanegoKoszyka),
                            array('products_id',$filtr->process($TablicaZawartosci['id'])),
                            array('basket_quantity',(float)$TablicaZawartosci['ilosc']),
                            array('products_comments',$filtr->process($TablicaZawartosci['komentarz'])),
                            array('products_text_fields',$filtr->process($TablicaZawartosci['pola_txt'])));

                    $GLOBALS['db']->insert_query('basket_save_products' , $pola);	
                    unset($pola);
                    //
                    
                }

            }     

            return array('id' => $IdZapisanegoKoszyka, 'czas' => $CzasZapisanegoKoszyka);
            
        }
     
    }
    
    // wczytanie koszyka
    public function WczytajKoszyk( $id ) {
      
        global $filtr;

        // usuwa zawartosc koszyka przed wczytaniem
        foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
            //
            $GLOBALS['koszykKlienta']->UsunZKoszyka( $TablicaWartosci['id'], false ); 
            //
        }      

        // wczytanie komentarzy do koszyka
        $zapytanie_uwagi = "SELECT DISTINCT * FROM basket_save WHERE basket_id = '" . (int)$id . "'";
        $sql_uwagi = $GLOBALS['db']->open_query($zapytanie_uwagi); 
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql_uwagi) > 0) {
      
            $info_uwagi = $sql_uwagi->fetch_assoc();
            
            if ( $info_uwagi['basket_comment'] != '' ) {
                 //
                 $_SESSION['uwagiKoszyka'] = $filtr->process($info_uwagi['basket_comment']);
                 //
            }
            
            unset($info_uwagi);
            
        }
        
        $GLOBALS['db']->close_query($sql_uwagi);
        unset($zapytanie_uwagi);  
        
        $DodanoWszystkie = true;
        
        // przeniesie produktow z bazy do sesji
        $zapytanie = "SELECT DISTINCT * FROM basket_save_products WHERE basket_id = '" . (int)$id . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie); 
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

            while ($info = $sql->fetch_assoc()) {
                //
                $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $info['products_id'] ) );
                //
                if ($Produkt->CzyJestProdukt == true) {
                
                    //
                    if ($this->CzyJestWKoszyku($info['products_id']) == false) {
                        //
                        $_SESSION['koszyk'][$info['products_id']] = array('id'          => $info['products_id'],
                                                                          'ilosc'       => $info['basket_quantity'],
                                                                          'komentarz'   => $info['products_comments'],
                                                                          'pola_txt'    => $info['products_text_fields'],
                                                                          'rodzaj_ceny' => 'baza');
                        //
                     } else {
                        //
                        $_SESSION['koszyk'][$info['products_id']]['ilosc'] += $info['basket_quantity'];
                        //
                    }
                    //   
                    
                    $SprMagazyn = $this->SprawdzIloscProduktuMagazyn( $info['products_id'], false );
                    
                    if ( $SprMagazyn == true ) { 
                         $DodanoWszystkie = false;
                    }
                    
                    $this->SprawdzCechyProduktu($info['products_id']);
                    
                    unset($SprMagazyn);

                } else {
                  
                    $DodanoWszystkie = false;
                 
                }
                //
                unset($Produkt);
                //
            }
            
            unset($info);
            
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);                  
        //
        $this->PrzeliczKoszyk();            
        //                   
        
        return $DodanoWszystkie;

    }
    
    // wczytanie koszyka
    public function WczytajKoszykLinku( $id_koszyka = '' ) {    
    
         global $i18n;
         
         $BladWczytywania = false;
    
         parse_str( preg_replace("/[^a-z0-9-=]/", "", (string)$id_koszyka), $GetTablicaTmp);
         //
         if ( isset($GetTablicaTmp['koszyk']) ) {
              //
              $DaneKoszyka = explode('-', (string)$GetTablicaTmp['koszyk']);
              //
              if ( count($DaneKoszyka) == 2 ) {
                   //
                   $zapytanie = "SELECT basket_id FROM basket_save WHERE basket_id = '" . (int)$DaneKoszyka[0] . "' AND basket_code = '" . (int)$DaneKoszyka[1] . "'";
                   $sql = $GLOBALS['db']->open_query($zapytanie); 
                   //
                   if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                        //
                        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI_PANEL') ), $GLOBALS['tlumacz'] );
                        //
                        $WczytanyKoszyk = $GLOBALS['koszykKlienta']->WczytajKoszyk( (int)$DaneKoszyka[0] );
                        //
                        $InfoWczytanyKoszyk = $GLOBALS['tlumacz']['KOSZYK_WCZYTANY'] . ' ';
                
                        if ( $WczytanyKoszyk == false ) {
                             //
                             $InfoWczytanyKoszyk .= $GLOBALS['tlumacz']['KOSZYK_WCZYTANY_PROBLEM'];
                             //
                        }

                        $_SESSION['wczytanyKoszyk'] = $InfoWczytanyKoszyk;
                        //
                        unset($InfoWczytanyKoszyk);
                        //
                        $BladWczytywania = true;
                        //
                   }               
                   //
                   $GLOBALS['db']->close_query($sql);
                   unset($zapytanie);  
                   //
              }
              //
              if ( $BladWczytywania == false ) {
                   //
                   $_SESSION['wczytanyKoszyk'] = $GLOBALS['tlumacz']['KOSZYK_ZAPISANY_BLAD'];
                   //
              }
              //
              $_SESSION['koszyk_id'] = (int)$DaneKoszyka[0] . '-' . (int)$DaneKoszyka[1];
              //              
              unset($DaneKoszyka);
              //
              Funkcje::PrzekierowanieSSL('koszyk.html'); 
              exit;
              //
         }    

    }

    public function SprawdzCzyDodanyDoKoszyka( $id ) {
      
        $DodanyDoKoszyka = false;
      
        if ( isset($GLOBALS['koszykKlienta']) && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {
            //
            foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                //
                if ( $id == Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) ) {
                     //
                     $DodanyDoKoszyka = true;
                     //
                }
                //
            }
            //
        }      
        
        return $DodanyDoKoszyka;
      
    }
    
}

?>