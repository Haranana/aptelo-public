<?php

class Schowek {

    public $IloscProduktow;
    public $IloscProduktowTablicaId;

    public function __construct() {
        //
        if (!isset($_SESSION['schowek'])) {
            $_SESSION['schowek'] = array();
        }    
        //
        $this->IloscProduktow = count($_SESSION['schowek']);
        $this->IloscProduktowTablicaId = $_SESSION['schowek'];
        //
    }

    public function PrzywrocSchowekZalogowanego() {
        //    
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            // najpierw musi dodac do bazy produkty dodane do schowka przed zalogowaniem
            if ($this->IloscProduktow > 0) {
                //
                foreach ($_SESSION['schowek'] AS $Id => $IdWart) {
                    //
                    // sprawdzi czy w bazie juz nie ma takiego produktu
                    $zapytanie = "SELECT products_id FROM customers_wishlist WHERE products_id = '" . $Id . "' AND customers_id = '" . (int)$_SESSION['customer_id'] . "'";
                    $sql = $GLOBALS['db']->open_query($zapytanie);        
                    //    
                    if ( (int)$GLOBALS['db']->ile_rekordow($sql) == 0 ) {
                        //
                        $this->DodajProduktSchowkaDoBazy($Id);            
                        //                    
                    }
                    //
                    $GLOBALS['db']->close_query($sql);
                    unset($zapytanie); 
                    //  
                }
                //
            }
            //
            $_SESSION['schowek'] = array();
            //
            // przeniesie produkty z bazy do sql
            $zapytanie = "SELECT DISTINCT products_id FROM customers_wishlist WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                  //
                  while ($info = $sql->fetch_assoc()) {
                      //
                      $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $info['products_id'] ) );
                      //
                      if ($Produkt->CzyJestProdukt == true) {
                          //
                          $_SESSION['schowek'][$info['products_id']] = $info['products_id'];
                          //
                        } else {
                          // 
                          // jezeli jest wylaczony usunie go ze schowka
                          $this->UsunProduktSchowkaBazy($info['products_id']); 
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
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie); 
            //  
            $this->IloscProduktow = count($_SESSION['schowek']);
            $this->IloscProduktowTablicaId = $_SESSION['schowek'];
            //            
        }
        //
    }
    
    // czysci sesje schowka przy wylogowaniu - tylko dla zalogowanych klientow
    public function WyczyscSesjeSchowekZalogowanego() {
        //  
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            $_SESSION['schowek'] = array(); 
            //  
            $this->IloscProduktow = 0;
            $this->IloscProduktowTablicaId = $_SESSION['schowek'];
            //            
        }
        //
    }    
    
    public function DodajDoSchowka( $id ) {
        //
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            if (!in_array($id, (array)$_SESSION['schowek'])) {
                $this->DodajProduktSchowkaDoBazy($id);
            }
            //
        }
        //
        // czy juz nie ma produktu w schowku
        if (!in_array($id, (array)$_SESSION['schowek'])) {
            $_SESSION['schowek'][$id] = $id;
        }
        //  
        $this->IloscProduktow = count($_SESSION['schowek']);
        $this->IloscProduktowTablicaId = $_SESSION['schowek'];
        //
    } 
    
    public function UsunZeSchowka( $id ) {
        //
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            $this->UsunProduktSchowkaBazy($id);
            //
        }
        //
        unset($_SESSION['schowek'][$id]);
        //  
        $this->IloscProduktow = count($_SESSION['schowek']);
        $this->IloscProduktowTablicaId = $_SESSION['schowek'];
        //
    }     

    public function DodajProduktSchowkaDoBazy( $id ) {
        //
        $pola = array(
                array('products_id',(int)$id),
                array('customers_id',(int)$_SESSION['customer_id']),
                array('customers_wishlist_date_added','now()'),
        );
        $GLOBALS['db']->insert_query('customers_wishlist' , $pola);	
        unset($pola);            
        //    
    }
    
    public function UsunProduktSchowkaBazy( $id ) {
        //
        $GLOBALS['db']->delete_query('customers_wishlist' , "products_id = '" . $id . "' and customers_id = '".(int)$_SESSION['customer_id']."'");	          
        //    
    }   

    public function WartoscProduktowSchowka() {
        //
        $WartoscSchowkaBrutto = 0;
        $WartoscSchowkaNetto = 0;
        //
        if ($this->IloscProduktow > 0) {
            //
            foreach ($_SESSION['schowek'] AS $Id => $IdWart) {
                //
                $Produkt = new Produkt( $Id );
                //
                if ( $Produkt->CzyJestProdukt ) {
                    //
                    $WartoscSchowkaBrutto = $WartoscSchowkaBrutto + $Produkt->info['cena_brutto_bez_formatowania'];
                    $WartoscSchowkaNetto = $WartoscSchowkaNetto + $Produkt->info['cena_netto_bez_formatowania'];
                    //
                } else {
                    //
                    $this->UsunZeSchowka( $Id );
                    //
                }
                //
                unset($Produkt);
                //
            }
            //
        }
        //
        return array( 'netto' => $WartoscSchowkaNetto, 'brutto' => $WartoscSchowkaBrutto);
        //
    }

    public function SprawdzCzyDodanyDoSchowka( $id ) {
      
        $DodanyDoSchowka = false;
      
        if ($this->IloscProduktow > 0) {
            //
            foreach ($_SESSION['schowek'] AS $IdProdSchowka => $IdWart) {
                //
                if ( $id == Funkcje::SamoIdProduktuBezCech( $IdProdSchowka ) ) {
                     //
                     $DodanyDoSchowka = true;
                     //
                }
                //
            }
            //
        }      
        
        return $DodanyDoSchowka;
      
    }
    
}

?>