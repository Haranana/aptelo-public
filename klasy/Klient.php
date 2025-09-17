<?php

class Klient {

  public $id_klienta;
  public $info;
  public $znizki;

  public function __construct( $id_klienta ) {

    $this->id_klienta = $id_klienta;

    // informacje ogolne o kliencie
    $this->info = array();
    // informacje znizkach klienta
    $this->znizki = array();

    if ( !isset($_SESSION['znizkiKlienta']) ) {
      $_SESSION['znizkiKlienta'] = $this->ZnizkiKlienta($this->id_klienta);
    }

  }
  
  // funkcja sprawdzajaca poprawnosc hasla klienta podczas logowania
  public static function sprawdzHasloKlienta($hasloBazy, $hasloKlienta) {
    //
    if (Funkcje::czyNiePuste($hasloBazy) && Funkcje::czyNiePuste($hasloKlienta)) {
        //
        $spr = explode(':', (string)$hasloKlienta);
        //
        if (sizeof($spr) != 2) return false;
        if (md5($spr[1] . $hasloBazy) == $spr[0]) { return true; }
    }
    //
    return false;
  }   

  // funkcja usuwajaca informacje po wylogowaniu klienta
  public static function WylogujKlienta() {

    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) {
        //
        unset($_SESSION['customer_default_address_id'], $_SESSION['customer_firstname'], $_SESSION['customer_email'], $_SESSION['znizkiKlienta'], $_SESSION['adresDostawy'], $_SESSION['rodzajDostawyKoszyk'], $_SESSION['adresFaktury'], $_SESSION['poziom_cen'], $_SESSION['customers_groups_id'], $_SESSION['customers_groups_name'], $_SESSION['min_zamowienie']);
        unset($_SESSION['krajDostawy'], $_SESSION['rodzajDostawy']);
        $_SESSION['customer_id'] = 0;
        $_SESSION['gosc'] = 1;
        if ( isset($_SESSION['koszyk']) ) {
            unset($_SESSION['koszyk']);
        }
        if ( isset($_SESSION['podsumowanieZamowienia']) ) {
            unset($_SESSION['podsumowanieZamowienia']);
        }
        if ( isset($_SESSION['punktyKlienta']) ) {
            unset($_SESSION['punktyKlienta']);
        }
        if ( isset($_SESSION['kuponRabatowy']) ) {
            unset($_SESSION['kuponRabatowy']);
        }
        if ( isset($_SESSION['opakowanieOzdobne']) ) {
            unset($_SESSION['opakowanieOzdobne']);
        } 
        //
        // program partnerski
        if ( isset($_SESSION['pp_id']) ) {
             unset($_SESSION['pp_id']);
        }  
        if ( isset($_SESSION['pp_id_coupon']) ) {
             unset($_SESSION['pp_id_coupon']);
        }        
        if ( isset($_SESSION['pp_statystyka']) ) {
            unset($_SESSION['pp_statystyka']);
        }        
        // logowanie facebook  
        if ( isset($_SESSION['social']) ) {
            unset($_SESSION['social']);
        }         
        if ( isset($_SESSION['fb_id']) ) {
            unset($_SESSION['fb_id']);
        } 
        if ( isset($_SESSION['google_id']) ) {
            unset($_SESSION['google_id']);
        }            
        // ceny netto
        if ( isset($_SESSION['netto']) ) {
            unset($_SESSION['netto']);
        }
        if ( isset($_SESSION['netto_wymuszone']) ) {
            unset($_SESSION['netto_wymuszone']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    return;
  }
  
  // funkcja zwraca w formie tablicy indywidualne ceny dla klienta
  public static function IndywidualneCenyKlienta($idKlienta) {
    //
    $TablicaWynik = array();
    //
    $zapytanie = "select cp.cp_id as id,
                         cp.cp_customers_id as id_klienta,
                         cp.cp_groups_id as id_grupy,    
                         cp.cp_products_id as id_produktu,
                         cp.cp_price as cena_netto, 
                         cp.cp_price_tax as cena_brutto,
                         pd.products_name as nazwa_produktu
                    from customers_price cp
               left join products_description pd on pd.products_id = cp.cp_products_id and pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                   where cp.cp_customers_id = '" . $idKlienta . "' or cp.cp_groups_id = '" . ((isset($_SESSION['customers_groups_id'])) ? (int)$_SESSION['customers_groups_id'] : '0') . "'
                order by nazwa_produktu, id_klienta, cena_brutto";  
                
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        while ($info = $sql->fetch_assoc()) {
            //
            $Produkt = new Produkt( $info['id_produktu'], 50, 50 );
            //
            if ($Produkt->CzyJestProdukt == true) {
                //
                if ( !isset($TablicaWynik[ $info['id_produktu'] ]) || ( isset($TablicaWynik[ $info['id_produktu'] ]) && $TablicaWynik[ $info['id_produktu'] ]['id_grupy'] > 0 && $TablicaWynik[ $info['id_produktu'] ]['id_klienta'] == 0 ) ) {
                     //
                     $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $Produkt->info['cena_brutto_bez_formatowania'], $Produkt->info['cena_netto_bez_formatowania'], '', $_SESSION['domyslnaWaluta']['id'], true );
                     //
                     // elementy kupowania
                     $Produkt->ProduktKupowanie();                  
                     $PrzyciskZakupu = '';
                     // jezeli jest aktywne kupowanie produktow
                     if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                         //
                         $PrzyciskZakupu .= '<div class="Zakup" style="display:inline-block">';
                         $PrzyciskZakupu .= $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                         $PrzyciskZakupu .= '</div>';
                         //
                     }                            
                     //
                     $TablicaWynik[ $info['id_produktu'] ] = array('nazwa' => $Produkt->info['nazwa'],
                                                                   'link' => $Produkt->info['link'],
                                                                   'zdjecie' => $Produkt->fotoGlowne['zdjecie_link'],
                                                                   'id_grupy' => $info['id_grupy'],
                                                                   'id_klienta' => $info['id_klienta'],
                                                                   'cena_brutto' => $TablicaCenyProduktu['brutto'],
                                                                   'cena_netto' => $TablicaCenyProduktu['netto'],
                                                                   'kupowanie' => $PrzyciskZakupu);
                     //
                     unset($TablicaCenyProduktu);
                     //
                }
                //
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
    return $TablicaWynik;
    //
  }  

  // funkcja zwraca w formie tablicy znizki klienta
  public static function ZnizkiKlienta($idKlienta, $ZnizkaIndywidualna = null) {

    $TablicaWynik = array();

    // znizka indywidualna
    if (!empty($ZnizkaIndywidualna) && $ZnizkaIndywidualna != 0) {
        //
        $TablicaWynik[] = array('Indywidualna','Indywidualna',$ZnizkaIndywidualna);
        //
      } else { 
        //
        $zapytanie = "select customers_discount from customers where customers_id = '" . $idKlienta . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            //
            $info = $sql->fetch_assoc();
            //
            if ($info['customers_discount'] != 0) {
                $TablicaWynik[] = array('Indywidualna','Indywidualna',$info['customers_discount']);
            }
            //
            unset($info);
            //
        }
        //
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);        
    }

    // znizka grupowa
    $zapytanie = "select cg.customers_groups_discount, c.customers_groups_id from customers c, customers_groups cg where customers_id = '" . $idKlienta . "' and c.customers_groups_id = cg.customers_groups_id";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        //
        $IdGrupyKlienta = $info['customers_groups_id'];
        //
        if ($info['customers_groups_discount'] != 0) {
            $TablicaWynik[] = array('Grupa klientów','Grupa klientów',$info['customers_groups_discount']);
        }
        //
        unset($info);
        //
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);         
    //
    
    // znizki dla producentow
    $zapytanie = "select dm.discount_discount, dm.discount_manufacturers_id from discount_manufacturers dm where dm.discount_customers_id = '" . $idKlienta . "' or dm.discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $znizkiProducentow = explode(',', (string)$info['discount_manufacturers_id']);
            //
            foreach ( $znizkiProducentow as $producentId ) {
                //       
                // szuka czy juz nie ma takiego producenta
                $Jest = false;
                foreach ( $TablicaWynik as $Tmp ) {
                   if ( $Tmp[0] == 'Producent' && $Tmp[1] == $producentId ) {
                        $Jest = true;
                   }
                }
                //
                if ( $Jest == false ) {
                    $TablicaWynik[] = array('Producent',$producentId,$info['discount_discount']);
                }
                //
                unset($Jest);
                //
            }
            //
            unset($znizkiProducentow);
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
    
    // znizki dla kategorii
    $zapytanie = "select dp.discount_discount, dp.discount_categories_id from discount_categories dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $znizkiKategorii = explode(',', (string)$info['discount_categories_id']);
            //
            foreach ( $znizkiKategorii as $kategoriaId ) {
                 //        
                 // szuka czy juz nie ma takiej kategorii
                 $Jest = false;
                 foreach ( $TablicaWynik as $Tmp ) {
                     if ( $Tmp[0] == 'Kategoria' && $Tmp[1] == $kategoriaId ) {
                          $Jest = true;
                     }
                 }
                 //             
                 if ( $Jest == false ) { 
                    //
                    $TablicaWynik[] = array('Kategoria',$kategoriaId,$info['discount_discount']);
                    //
                 }
                 //
                 unset($Jest);
                 //
            }
            //
            unset($znizkiKategorii);
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
    
    // znizki dla kategorii i producenta
    $zapytanie = "select dp.discount_discount, dp.discount_categories_id, dp.discount_manufacturers_id from discount_categories_manufacturers dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $kategoriaId = $info['discount_categories_id'];
            $producentId = $info['discount_manufacturers_id'];
            //      
            $Jest = false;
            foreach ( $TablicaWynik as $Tmp ) {
               if ( $Tmp[0] == 'Kategoria/Producent' ) {
                    if ( $Tmp[3] == $kategoriaId && $Tmp[4] == $producentId ) {
                         $Jest = true;
                    }
               }
            }
            //             
            if ( $Jest == false ) { 
              //
              $TablicaWynik[] = array('Kategoria/Producent',0,$info['discount_discount'],$kategoriaId,$producentId);
              //
            }
            //
            unset($Jest, $kategoriaId, $producentId);
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

    // znizki dla produktow
    $zapytanie = "select dp.discount_discount, 
                         dp.discount_products_id 
                    from discount_products dp 
                   where ( dp.discount_customers_id = '" . $idKlienta . "' 
                         or discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%' )
                         and dp.discount_products_id not in ( select cp_products_id from customers_price where cp_customers_id = '" . $idKlienta . "' or cp_groups_id LIKE '%," . $IdGrupyKlienta . ",%' )";
                         
    //$zapytanie = "select dp.discount_discount, dp.discount_products_id from discount_products dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id = '" . $IdGrupyKlienta . "'";
 
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            // szuka czy juz nie ma takiego produktu
            $Jest = false;
            foreach ( $TablicaWynik as $Tmp ) {
               if ( $Tmp[0] == 'Produkt' && $Tmp[1] == $info['discount_products_id'] ) {
                    $Jest = true;
               }
            }
            //    
            if ( $Jest == false ) {
                //
                $TablicaWynik[] = array('Produkt',$info['discount_products_id'],$info['discount_discount']);
                //
            }
            //
            unset($Jest);
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
    
    return $TablicaWynik;
  }
  
  // funkcja zwraca w formie tablicy znizki klienta
  public static function ZnizkiKlientaInfo($idKlienta, $ZnizkaIndywidualna = null) {

    $TablicaWynik = array();
    $IdGrupyKlienta = '';

    // znizka indywidualna
    if (!empty($ZnizkaIndywidualna) && $ZnizkaIndywidualna != 0) {
        //
        $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_INDYWIDUALNA'],'Indywidualna',$ZnizkaIndywidualna);
        //
      } else { 
        //
        $zapytanie = "select customers_id, customers_discount from customers where customers_id = '" . $idKlienta . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            //
            $info = $sql->fetch_assoc();
            //
            if ($info['customers_discount'] != 0) {
                $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_INDYWIDUALNA'],'Indywidualna',$info['customers_discount'],$info['customers_id']);
            }
            //
            unset($info);
            //
        }
        //
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);        
    }

    // znizka grupowa
    $zapytanie = "select cg.customers_groups_discount, c.customers_groups_id from customers c, customers_groups cg where customers_id = '" . $idKlienta . "' and c.customers_groups_id = cg.customers_groups_id";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        //
        $IdGrupyKlienta = $info['customers_groups_id'];
        //
        if ($info['customers_groups_discount'] != 0) {
            $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_GRUPA_KLIENTOW'],$GLOBALS['tlumacz']['ZNIZKI_GRUPA_KLIENTOW'],$info['customers_groups_discount'],$info['customers_groups_id']);
        }
        //
        unset($info);
        //
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);         
    //
    
    $NazwyProducentow = Producenci::TablicaProducenci();

    // znizki dla producentow
    $zapytanie = "select * from discount_manufacturers dm where dm.discount_customers_id = '" . $idKlienta . "' or dm.discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $znizkiProducentow = explode(',', (string)$info['discount_manufacturers_id']);
            //
            foreach ( $znizkiProducentow as $producentId ) {
                //
                if ( isset($NazwyProducentow[$producentId]) ) {
                     //       
                     // szuka czy juz nie ma takiego producenta
                     $Jest = false;
                     foreach ( $TablicaWynik as $Tmp ) {
                         if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_PRODUCENT'] && $Tmp[3] == $producentId ) {
                              $Jest = true;
                         }
                     }
                     //
                     if ( $Jest == false ) {
                          $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_PRODUCENT'],$NazwyProducentow[$producentId]['Nazwa'],$info['discount_discount'], $producentId);
                     }
                     //
                     unset($Jest);
                     //
                }
                //
            }
            //
            unset($znizkiProducentow);
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

    // znizki dla kategorii
    $zapytanie = "select dp.discount_discount, dp.discount_categories_id from discount_categories dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $znizkiKategorii = explode(',', (string)$info['discount_categories_id']);
            //
            foreach ( $znizkiKategorii as $kategoriaId ) {
                //
                if ( isset($GLOBALS['tablicaKategorii'][$kategoriaId]) ) {
                     //        
                     // szuka czy juz nie ma takiej kategorii
                     $Jest = false;
                     foreach ( $TablicaWynik as $Tmp ) {
                         if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_KATEGORIA'] && $Tmp[3] == $kategoriaId ) {
                              $Jest = true;
                         }
                     }
                     //
                     if ( $Jest == false ) {          
                         //    
                         $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_KATEGORIA'],$GLOBALS['tablicaKategorii'][$kategoriaId]['Nazwa'],$info['discount_discount'],$kategoriaId);
                         //
                     }
                     //
                     unset($Jest);
                     //                     
                }
                //
            }
            //
            unset($znizkiKategorii);
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
    
    // znizki dla kategorii i producenta
    $zapytanie = "select dp.discount_discount, dp.discount_categories_id, dp.discount_manufacturers_id from discount_categories_manufacturers dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $kategoriaId = $info['discount_categories_id'];
            $producentId = $info['discount_manufacturers_id'];
            //      
            $Jest = false;
            foreach ( $TablicaWynik as $Tmp ) {
               if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_KATEGORIA'] . ' / ' . $GLOBALS['tlumacz']['ZNIZKI_PRODUCENT'] && $Tmp[3] == $kategoriaId . '#' . $producentId ) {
                    $Jest = true;
               }
            }
            //             
            if ( $Jest == false ) { 
              //
              $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_KATEGORIA'] . ' / ' . $GLOBALS['tlumacz']['ZNIZKI_PRODUCENT'],$GLOBALS['tablicaKategorii'][$kategoriaId]['Nazwa'] . ' / ' . $NazwyProducentow[$producentId]['Nazwa'],$info['discount_discount'],$kategoriaId . '#' . $producentId);          
              //
            }
            //
            unset($Jest, $kategoriaId, $producentId);
            //    
        }
        //
        unset($info);
        //
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $NazwyProducentow);         
    //        
    
    // znizki dla produktow
    $zapytanie = "select dp.discount_discount, pd.products_name, pd.products_id from discount_products dp, products_description pd where (dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id LIKE '%," . $IdGrupyKlienta . ",%')
                    and pd.products_id = dp.discount_products_id and pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' and dp.discount_products_id not in ( select cp_products_id from customers_price where cp_customers_id = '" . $idKlienta . "' or cp_groups_id LIKE '%," . $IdGrupyKlienta . ",%' )";                    
                    
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            // szuka czy juz nie ma takiego produktu
            $Jest = false;
            foreach ( $TablicaWynik as $Tmp ) {
               if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_PRODUKT'] && $Tmp[3] == $info['products_id'] ) {
                    $Jest = true;
               }
            }
            //    
            if ( $Jest == false ) {
                //
                // sprawdzi czy jest produkt aktywny
                $Produkt = new Produkt( $info['products_id'] );
                //
                if ($Produkt->CzyJestProdukt == true && $Produkt->info['wylaczone_rabaty'] == 'nie') {     
                    //
                    if ( $Produkt->info['produkt_dnia'] == 'nie' ) {                
                         $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_PRODUKT'],$Produkt->info['link'],$info['discount_discount'],$info['products_id']);
                    }
                    //
                }
                //
                unset($Produkt);
                //
            }
            //
            unset($Jest);
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
    
    return $TablicaWynik;
  }
  
  // funkcja wyswietlajaca dane adresowe klienta
  public static function PokazAdresKlienta( $typ ) {
    global $zamowienie;

    $dane = Array();
    if ( $typ == 'klient' ) {
      $dane = $zamowienie->klient;
    } elseif ( $typ == 'dostawa' ) {
      $dane = $zamowienie->dostawa;
    } elseif ( $typ == 'platnik' ) {
      $dane = $zamowienie->platnik;
    }

    $tekst = '';
    $tekst .= $dane['nazwa'] . '<br />';
    $tekst .= ( $dane['firma'] != '' ? $dane['firma'] . '<br />' : '' );
    $tekst .= $dane['ulica'] . '<br />';
    $tekst .= $dane['kod_pocztowy'] . ' ' . $dane['miasto'] . '<br />';
    $tekst .= ( $dane['wojewodztwo'] != '' ? $dane['wojewodztwo'] . '<br />' : '' );
    $tekst .= ( $dane['kraj'] != '' ? $dane['kraj'] . '<br />' : '' );
    $tekst .= '<br />';
    $tekst .= ( $dane['nip'] != '' && $typ == 'platnik' ? 'NIP: ' . $dane['nip'] . '<br />' : '' );

    if ( $typ == 'klient' ) {
      $tekst .= 'Tel: ' . $dane['telefon'] . '<br />';
      $tekst .= $dane['adres_email'];
    }
    
    if ( $typ == 'dostawa' && !empty($dane['telefon']) && KLIENT_POKAZ_TELEFON == 'tak' ) {
        $tekst .= 'Tel: ' . $dane['telefon'] . '<br />';
    }      

    return $tekst;
  }  

  // funkcja wyswietlajaca komentarz do zamowienia klienta
  public static function pokazKomentarzZamowienia( $zamowienie_id ) {

    $wynik = '';

    $zapytanie = "SELECT orders_status_id, customer_notified, comments FROM orders_status_history WHERE orders_id = '" . $zamowienie_id . "' ORDER BY date_added LIMIT 1";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($komentarz = $sql->fetch_assoc()) {
          $wynik = $komentarz['comments'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  }  
  
  // funkcja zwaraca ilosc zamowien klienta
  public static function IloscZamowien( $id_email_klienta, $typ = 'id', $nr_zam = '' ) {

    if ( $typ == 'id' ) {
    
        $zapytanie = "SELECT orders_id FROM orders WHERE customers_id = '" . $id_email_klienta . "'";
        
      } else {
      
        $zapytanie = "SELECT orders_id FROM orders WHERE lower(customers_email_address) = '" . strtolower((string)$id_email_klienta) . "' and orders_id != '" . $nr_zam . "'";
        
    }

    $sql = $GLOBALS['db']->open_query($zapytanie);

    $wynik = (int)$GLOBALS['db']->ile_rekordow($sql);
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  }   
  
  // funkcja zwracajaca wartosc minimalnego zamowienia
  public static function MinimalneZamowienie() {
    
    $MinimalneZamowienieGrupy = 0;
  
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        //
        $MinimalneZamowienieGrupy = $_SESSION['min_zamowienie'];
        //
      } else {
        // jezeli klient nie jest zalogowany przyjmie min zamowienie domyslnej grupy
        $zapytanie = "SELECT customers_groups_min_amount FROM customers_groups WHERE customers_groups_id = '1'";
        $sql = $GLOBALS['db']->open_query($zapytanie);  
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            //
            $info = $sql->fetch_assoc(); 
            //
            $MinimalneZamowienieGrupy = $info['customers_groups_min_amount'];
            //
            unset($info);
            //
        }
        //
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie);     
        //
    }  
    
    return $MinimalneZamowienieGrupy;
    
  } 
  
  // funkcja do wyswietlania nazwy panstwa
  public static function pokazNazwePanstwa($id) {

    $wynik = ''; 
    
    $zapytanie = "SELECT countries_name FROM countries_description WHERE countries_id = ".(int)$id." AND language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($nazwa_grupy = $sql->fetch_assoc()) {
          $wynik = $nazwa_grupy['countries_name'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);

  	return $wynik;
  }    

  // funkcja do wyswietlania kod panstwa
  public static function pokazKodPanstwa($id) {

    $wynik = ''; 
    
    $zapytanie = "SELECT countries_iso_code_2 FROM countries WHERE countries_id = ".(int)$id."";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($nazwa_grupy = $sql->fetch_assoc()) {
          $wynik = $nazwa_grupy['countries_iso_code_2'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);

  	return $wynik;
  }    

  // funkcja do wyswietlania nazwy wojewodztwa
  public static function pokazNazweWojewodztwa($id) {

    $wynik = ''; 
    
    $zapytanie = "SELECT zone_name FROM zones WHERE zone_id = ".(int)$id." ";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($nazwa = $sql->fetch_assoc()) {
          $wynik = $nazwa['zone_name'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

  	return $wynik;
  }  

  // funkcja generujaca rozwijana liste panstw
  public static function ListaPanstw( $tryb = 'countries_id' ) {

    $tablicaPanstw = array();

    $panstwa = "SELECT c.".$tryb.", cd.countries_name 
                  FROM countries c
             LEFT JOIN countries_description cd ON cd.countries_id = c.countries_id AND cd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'
              ORDER BY cd.countries_name";

    $sql = $GLOBALS['db']->open_query($panstwa);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while ($wartosciPanstw = $sql->fetch_assoc()) {

            $tablicaPanstw[] = array('id' => $wartosciPanstw[$tryb],
                                     'text' => $wartosciPanstw['countries_name']);
                                     
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($panstwa);

    return $tablicaPanstw;
  }

  // funkcja generujaca rozwijana liste panstw
  public static function ListaPanstwDymyslna() {

    $tablicaPanstw = array();

    $panstwa = "SELECT c.countries_id, cd.countries_name 
                  FROM countries c
             LEFT JOIN countries_description cd ON cd.countries_id = c.countries_id AND cd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."' 
                 WHERE c.countries_id = '".$_SESSION['krajDostawyDomyslny']['id']."'";

    $sql = $GLOBALS['db']->open_query($panstwa);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while ($wartosciPanstw = $sql->fetch_assoc()) {
          
            $tablicaPanstw[] = array('id' => $wartosciPanstw['countries_id'],
                                     'text' => $wartosciPanstw['countries_name']);
                                   
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($panstwa);

    return $tablicaPanstw;
  }
  
  // funkcja generujaca rozwijana liste wojewodztw
  public static function ListaWojewodztw($filtr = '') {

    $tablicaWojewodztw = array();

    $wojewodztwa = "SELECT zone_id, zone_country_id, zone_name 
                      FROM zones 
                     WHERE zone_country_id = '".$filtr."' 
                  ORDER BY zone_name";

    $sql = $GLOBALS['db']->open_query($wojewodztwa);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while ($wartosciWojewodztw = $sql->fetch_assoc()) {
          
            $tablicaWojewodztw[] = array('id' => $wartosciWojewodztw['zone_id'],
                                         'text' => $wartosciWojewodztw['zone_name']);
                                       
        }
    
    }
    
    if ( count($tablicaWojewodztw) == 0 ) {
      
         $tablicaWojewodztw[] = array( 'id' => '0',
                                       'text' => '----');

    }                                           
      
    $GLOBALS['db']->close_query($sql);
    unset($wojewodztwa);

    return $tablicaWojewodztw;
  }  
  
  
  // funkcja do wyswietlania prefiksu telefonu panstwa
  public static function pokazPrefixPanstwa($id, $telefon) {

    $wynik = $telefon;

    $country_list = [
        'AF' => 93,
        'AX' => 358,
        'AL' => 355,
        'DZ' => 213,
        'AS' => 1,
        'AD' => 376,
        'AO' => 244,
        'AI' => 1,
        'AQ' => 672,
        'AG' => 1,
        'AR' => 54,
        'AM' => 374,
        'AW' => 297,
        'AU' => 61,
        'AT' => 43,
        'AZ' => 994,
        'BS' => 1,
        'BH' => 973,
        'BD' => 880,
        'BB' => 1,
        'BY' => 375,
        'BE' => 32,
        'BZ' => 501,
        'BJ' => 229,
        'BM' => 1,
        'BT' => 975,
        'BO' => 591,
        'BA' => 387,
        'BW' => 267,
        'BV' => 55,
        'BR' => 55,
        'IO' => 246,
        'BN' => 673,
        'BG' => 359,
        'BF' => 226,
        'BI' => 257,
        'CV' => 238,
        'KH' => 855,
        'CM' => 237,
        'CA' => 1,
        'BQ' => 599,
        'KY' => 1,
        'CF' => 236,
        'TD' => 235,
        'CL' => 56,
        'CN' => 86,
        'CX' => 61,
        'CC' => 61,
        'CO' => 57,
        'KM' => 269,
        'CG' => 242,
        'CD' => 243,
        'CK' => 682,
        'CR' => 506,
        'HR' => 385,
        'CU' => 53,
        'CW' => 599,
        'CY' => 357,
        'CZ' => 420,
        'CI' => 225,
        'DK' => 45,
        'DJ' => 253,
        'DM' => 1,
        'DO' => 1,
        'EC' => 593,
        'EG' => 20,
        'SV' => 503,
        'GQ' => 240,
        'ER' => 291,
        'EE' => 372,
        'SZ' => 268,
        'ET' => 251,
        'FK' => 500,
        'FO' => 298,
        'FJ' => 679,
        'FI' => 358,
        'FR' => 33,
        'GF' => 594,
        'PF' => 689,
        'TF' => 262,
        'GA' => 241,
        'GM' => 220,
        'GE' => 995,
        'DE' => 49,
        'GH' => 233,
        'GI' => 350,
        'GR' => 30,
        'GL' => 299,
        'GD' => 1,
        'GP' => 590,
        'GU' => 1,
        'GT' => 502,
        'GG' => 44,
        'GN' => 224,
        'GW' => 245,
        'GY' => 592,
        'HT' => 509,
        'HM' => 61,
        'HN' => 504,
        'HK' => 852,
        'HU' => 36,
        'IS' => 354,
        'IN' => 91,
        'ID' => 62,
        'IR' => 98,
        'IQ' => 964,
        'IE' => 353,
        'IM' => 44,
        'IL' => 972,
        'IT' => 39,
        'JM' => 1,
        'JP' => 81,
        'JE' => 44,
        'JO' => 962,
        'KZ' => 7,
        'KE' => 254,
        'KI' => 686,
        'KP' => 850,
        'KR' => 82,
        'XK' => 383,
        'KW' => 965,
        'KG' => 996,
        'LA' => 856,
        'LV' => 371,
        'LB' => 961,
        'LS' => 266,
        'LR' => 231,
        'LY' => 218,
        'LI' => 423,
        'LT' => 370,
        'LU' => 352,
        'MO' => 853,
        'MK' => 389,
        'MG' => 261,
        'MW' => 265,
        'MY' => 60,
        'MV' => 960,
        'ML' => 223,
        'MT' => 356,
        'MH' => 692,
        'MQ' => 596,
        'MR' => 222,
        'MU' => 230,
        'YT' => 262,
        'MX' => 52,
        'FM' => 691,
        'MD' => 373,
        'MC' => 377,
        'MN' => 976,
        'ME' => 382,
        'MS' => 1,
        'MA' => 212,
        'MZ' => 258,
        'MM' => 95,
        'NA' => 264,
        'NR' => 674,
        'NP' => 977,
        'NL' => 31,
        'NC' => 687,
        'NZ' => 64,
        'NI' => 505,
        'NE' => 227,
        'NG' => 234,
        'NU' => 683,
        'NF' => 672,
        'MP' => 1,
        'NO' => 47,
        'OM' => 968,
        'PK' => 92,
        'PW' => 680,
        'PS' => 970,
        'PA' => 507,
        'PG' => 675,
        'PY' => 595,
        'PE' => 51,
        'PH' => 63,
        'PN' => 64,
        'PL' => 48,
        'PT' => 351,
        'PR' => 1,
        'QA' => 974,
        'RE' => 262,
        'RO' => 40,
        'RU' => 7,
        'RW' => 250,
        'BL' => 590,
        'SH' => 290,
        'KN' => 1,
        'LC' => 1,
        'MF' => 590,
        'PM' => 508,
        'VC' => 1,
        'WS' => 685,
        'SM' => 378,
        'ST' => 239,
        'SA' => 966,
        'SN' => 221,
        'RS' => 381,
        'SC' => 248,
        'SL' => 232,
        'SG' => 65,
        'SX' => 1,
        'SK' => 421,
        'SI' => 386,
        'SB' => 677,
        'SO' => 252,
        'ZA' => 27,
        'GS' => 500,
        'SS' => 211,
        'ES' => 34,
        'LK' => 94,
        'SD' => 249,
        'SR' => 597,
        'SJ' => 47,
        'SE' => 46,
        'CH' => 41,
        'SY' => 963,
        'TW' => 886,
        'TJ' => 992,
        'TZ' => 255,
        'TH' => 66,
        'TL' => 670,
        'TG' => 228,
        'TK' => 690,
        'TO' => 676,
        'TT' => 1,
        'TN' => 216,
        'TR' => 90,
        'TM' => 993,
        'TC' => 1,
        'TV' => 688,
        'UM' => 1,
        'UG' => 256,
        'UA' => 380,
        'AE' => 971,
        'GB' => 44,
        'US' => 1,
        'UY' => 598,
        'UZ' => 998,
        'VU' => 678,
        'VA' => 39,
        'VE' => 58,
        'VN' => 84,
        'VG' => 1,
        'VI' => 1,
        'WF' => 681,
        'EH' => 212,
        'YE' => 967,
        'ZM' => 260,
        'ZW' => 263
    ];
    if ( isset($country_list[$id]) ) {
        $prefix = $country_list[$id];
        $SamNumer = preg_replace("/^\+?{$prefix}/", '',$telefon);
        $wynik = preg_replace('/^(?:\+?{$prefix}|0)?/','+'.$prefix, $SamNumer);

    }
    return $wynik;

  }

  // funkcja generujaca tablice zawierajaca statusy punktow
  public static function ListaStatusowReklamacji( $dowolna = true, $tekst = 'dowolny', $CzyscHtml = false ) {

    $tablica = array();
    
    if ( $dowolna ) {
      $tablica[] = array('id' => '0', 'text' => $tekst);
    }
    
    $zapytanie = "SELECT s.points_status_id, sd.points_status_name FROM customers_points_status s LEFT JOIN customers_points_status_description sd ON sd.points_status_id = s.points_status_id AND sd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($nazwa_statusu = $sql->fetch_assoc()) {
            $tablica[] = array('id' => $nazwa_statusu['points_status_id'], 'text' => (($CzyscHtml == true) ? strip_tags((string)$nazwa_statusu['points_status_name']) : $nazwa_statusu['points_status_name']));
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie); 

    return $tablica;
  }  
  
  
  // funkcja wyswietlajaca status punktow
  public static function pokazNazweStatusuPunktow( $status_id, $jezyk = '1') {
  
    /*
    typy punktow
    1 - oczekujace
    2 - zatwierdzone
    3 - anulowane
    4 - wykorzystane
    */     

    $wynik = '';
    
    $zapytanie = "SELECT s.points_status_id, s.points_status_color, sd.points_status_name FROM customers_points_status s LEFT JOIN customers_points_status_description sd ON sd.points_status_id = s.points_status_id WHERE s.points_status_id = '".$status_id."' AND sd.language_id = '".$jezyk."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($nazwa_statusu = $sql->fetch_assoc()) {
            $wynik = '<span style="color: #'.$nazwa_statusu['points_status_color'].'">'.$nazwa_statusu['points_status_name'].'</span>';
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);    

    return $wynik;
  }    
  
  // funkcja generujaca dodatkowe pola dla klientow
  static public function pokazDodatkowePolaKlientow($klient_id,$languages_id = '1' ) {
    global $i18n;

    $ciag_dodatkowych_pol ='';

    $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type, ce.fields_required_status, cei.fields_input_value, cei.fields_name, ce.fields_status, ce.fields_input_type, ce.fields_type, ce.fields_required_label 
                                  FROM customers_extra_fields ce, customers_extra_fields_info cei 
                                 WHERE ce.fields_status = '1' AND cei.fields_id = ce.fields_id AND cei.languages_id = '".$languages_id."' ORDER BY ce.fields_order";

    $sql = $GLOBALS['db']->open_query($dodatkowe_pola_klientow);

    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

        while ( $dodatkowePola = $sql->fetch_assoc() ) {

          $wartosc = '';

          if( isset($klient_id) && (int)$klient_id > 0 ) {

            $wartosc_query = "SELECT value FROM customers_to_extra_fields WHERE customers_id = '" . $klient_id . "' AND fields_id= '" . $dodatkowePola['fields_id'] . "'";

            $wartosc_info = $GLOBALS['db']->open_query($wartosc_query);
            
            if ( (int)$GLOBALS['db']->ile_rekordow($wartosc_info) > 0  ) {
              
                $dodatkowePolaInfo = $wartosc_info->fetch_assoc();

                $wartosc_list = explode("\n", (string)$dodatkowePolaInfo['value']);

                for($i = 0, $n = count($wartosc_list); $i < $n; $i++) {
                  $wartosc_list[$i] = trim((string)$wartosc_list[$i]);
                }
                $wartosc = $wartosc_list[0];

                $GLOBALS['db']->close_query($wartosc_info);
                
            }

          }

          $ciag_dodatkowych_pol .= '<div class="DodatkowePolaKlientow">';

          if ( $dodatkowePola['fields_required_label'] == '1' ) {
              $ciag_dodatkowych_pol .= '<span>' . (($dodatkowePola['fields_input_type'] != 2 && $dodatkowePola['fields_input_type'] != 3) ? '<label class="formSpan" for="fields_' . $dodatkowePola['fields_id'] . '">' : '') . $dodatkowePola['fields_name'] . ': ' . (($dodatkowePola['fields_required_status' ]== 1) ? '<em class="required" id="em_'.uniqid().'"></em>': '') . (($dodatkowePola['fields_input_type'] != 2 && $dodatkowePola['fields_input_type'] != 3) ? '</label>' : '') . '</span>';
          }

          $wartosci_pola_lista = explode("\n", (string)$dodatkowePola['fields_input_value']);
          $wartosci_pola_tablica = array();
          
          foreach($wartosci_pola_lista as $wartosc_pola) {
            $wartosc_pola = trim((string)$wartosc_pola);
            $wartosci_pola_tablica[] = array('id' => $wartosc_pola, 'text' => $wartosc_pola);
          }
          
          switch($dodatkowePola['fields_input_type']) {
            // Pole typu INPUT
            case 0:
              if ( $dodatkowePola['fields_type'] == 'kalendarz' ) {
                   $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc).'" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required datefields"': 'class="datefields"').' size="30" />';
                 } else {
                   $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc).'" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" />';
              }
              $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
              break;

            // Pole typu TEXTAREA
            case 1:
              $ciag_dodatkowych_pol .= '<textarea name="fields_' . $dodatkowePola['fields_id'].'" cols="40" rows="4" id="fields_'.$dodatkowePola['fields_id'].'" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').'>'.FunkcjeWlasnePHP::my_htmlentities($wartosc).'</textarea>';
              $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
              break;

            // Pole typu RADIO
            case 2:
              $cnt = 0;
              foreach($wartosci_pola_lista as $wartosc_pola) {
                $zaznaczone = '';
                $pole_rand = 'pole_' . rand(1,100000000);
                $wartosc_pola = trim((string)$wartosc_pola);
                if ( $wartosc != '' && $wartosc == $wartosc_pola ) {
                  $zaznaczone = 'checked="checked"';
                }
                if ( $wartosc == '' && $cnt == 0 ) {
                  $zaznaczone = 'checked="checked"';
                }

                $ciag_dodatkowych_pol .= '<label for="' . $pole_rand . '">' . FunkcjeWlasnePHP::my_htmlentities($wartosc_pola);
                $ciag_dodatkowych_pol .= '<input type="radio" value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc_pola).'" id="' . $pole_rand . '" name="fields_' . $dodatkowePola['fields_id'].'" '.$zaznaczone. ' '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' />';
                $ciag_dodatkowych_pol .= '<span class="radio" id="radio_' . $pole_rand . '"></span>';
                $ciag_dodatkowych_pol .= '</label>';

                $cnt++;
                unset($pole_rand);
              }
              $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_JEDNA_OPCJE'] . '</label>';            
              break;

            // Pole typu CHECKBOX
            case 3:
              $cnt = 0;
              foreach($wartosci_pola_lista as $wartosc_pola) {
                $pole_rand = 'pole_' . rand(1,100000000);
                $wartosc_pola = trim((string)$wartosc_pola);

                if ( isset($wartosc_list) && count($wartosc_list) > 0 ) {
                     $zaznaczone = ( in_array($wartosc_pola, $wartosc_list) ? 'checked="checked"' : '' );
                   } else {
                     $zaznaczone = '';
                }

                $ciag_dodatkowych_pol .= '<label for="' . $pole_rand . '">' . FunkcjeWlasnePHP::my_htmlentities($wartosc_pola);
                $ciag_dodatkowych_pol .= '<input type="checkbox" value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc_pola).'" id="' . $pole_rand . '" name="fields_' . $dodatkowePola['fields_id'].'[]" ' . $zaznaczone . ' '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' />';
                $ciag_dodatkowych_pol .= '<span class="check" id="check_' . $pole_rand . '"></span>';

                if ( $dodatkowePola['fields_required_label'] == '0' && $dodatkowePola['fields_required_status'] == '1' ) {
                   $ciag_dodatkowych_pol .= '<em class="required checkreq" id="em_'.uniqid().'"></em>';
                }

                $ciag_dodatkowych_pol .= '</label>';

                $cnt++;
                unset($pole_rand);
              }
              $ciag_dodatkowych_pol .= '<div class="errorInformacjapola">';
              $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'[]" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_OPCJE'] . '</label>';
              $ciag_dodatkowych_pol .= '</div>';
              break;

            // Pole typu SELECT
            case 4:
                $ciag_dodatkowych_pol .= Funkcje::RozwijaneMenu('fields_' . $dodatkowePola['fields_id'], $wartosci_pola_tablica, $wartosc, ' id="fields_' . $dodatkowePola['fields_id'] . '"');
              break;

            default:
              $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc).'" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" />';
              break;
          }

          $ciag_dodatkowych_pol .= '</div>';
        }
          
    }
    $GLOBALS['db']->close_query($sql);
    
    unset($dodatkowe_pola_klientow, $dodatkowe_pola);        
    
    return $ciag_dodatkowych_pol;
  }  
  
  
  // funkcja generujaca tablice zawierajaca statusy punktow
  public static function KoszykiKlienta( $idKlienta ) {

    $tablica = array();

    $zapytanie = "SELECT * FROM basket_save WHERE customers_id = '" . $idKlienta . "' ORDER BY basket_date_added DESC";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($info = $sql->fetch_assoc()) {
          
            $tablica[] = array('id' => $info['basket_id'],
                               'nazwa' => $info['basket_name'], 
                               'opis' => $info['basket_description'],
                               'data' => date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['basket_date_added'])));
                             
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie); 

    return $tablica;
  }    
  
  // funkcja wysyla wiadomosci email z prosba o opinie o sklepie
  public static function WyslijMailOpinie() {
    global $filtr;
        
    $TablicaZamowien = array();
    
    if ( OPINIE_SPRAWDZAJ_KLIENTA == 'tak' ) {
    
        $zapytanie = "SELECT distinct o.orders_id, o.customers_email_address, o.customers_name, o.date_purchased, c.language_id, c.customers_newsletter
                        FROM orders o
                   LEFT JOIN customers c ON o.customers_id = c.customers_id
                       WHERE c.customers_reviews = '1'
                         AND o.orders_status in (" . ((OPINIE_STATUSY_ZAMOWIEN == '') ? '0' : OPINIE_STATUSY_ZAMOWIEN) . ")
                         AND STR_TO_DATE(o.date_purchased,'%Y-%m-%d') = '" . date('Y-m-d', time() - (OPINIE_ILE_DNI * 86400)) . "' 
                         AND (o.review_date = '0000-00-00 00:00:00' or o.review_date is null)";
                         
      } else {
        
        $zapytanie = "SELECT distinct o.orders_id, o.customers_email_address, o.customers_name, o.date_purchased, c.language_id, c.customers_newsletter
                        FROM orders o
                   LEFT JOIN customers c ON o.customers_id = c.customers_id
                       WHERE c.customers_reviews = '1'
                         AND o.customers_email_address not in ( select customers_email_address from orders where review_date != '0000-00-00' )
                         AND o.orders_status in (" . ((OPINIE_STATUSY_ZAMOWIEN == '') ? '0' : OPINIE_STATUSY_ZAMOWIEN) . ")
                         AND STR_TO_DATE(o.date_purchased,'%Y-%m-%d') = '" . date('Y-m-d', time() - (OPINIE_ILE_DNI * 86400)) . "' 
                         AND (o.review_date = '0000-00-00 00:00:00' or o.review_date is null)";                   
    
    }

    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {     

            if ( !in_array($info['orders_id'], $TablicaZamowien) ) {
                
                // sprawdzi dodatkowo czy nie bylo wyslanego maila
                $zapytanie_testowe = "SELECT distinct o.orders_id, o.customers_email_address FROM orders o WHERE o.orders_id = '" . $info['orders_id'] . "' AND (o.review_date = '0000-00-00 00:00:00' or o.review_date is null)";
                $sqlt = $GLOBALS['db']->open_query($zapytanie_testowe);

                if ((int)$GLOBALS['db']->ile_rekordow($sqlt) > 0) {

                    $zapytanie_tresc = "SELECT t.sender_name, 
                                               t.email_var_id, 
                                               t.sender_email, 
                                               t.dw, 
                                               t.template_id, 
                                               t.email_file, 
                                               tz.email_title, 
                                               tz.description
                                          FROM email_text t 
                                     LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (((int)$info['language_id'] > 0) ? $info['language_id'] : (int)$_SESSION['domyslnyJezyk']['id']) . "' 
                                         WHERE t.email_var_id = 'OPINIA_O_SKLEPIE'";
                                         
                    $sqlMail = $GLOBALS['db']->open_query($zapytanie_tresc);
                    $tresc = $sqlMail->fetch_assoc();    

                    $email = new Mailing;
                    
                    if ( $tresc['email_file'] != '' ) {
                      $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
                    } else {
                      $tablicaZalacznikow = array();
                    }

                    $nadawca_email = Funkcje::parsujZmienne($tresc['sender_email']);
                    $nadawca_nazwa = Funkcje::parsujZmienne($tresc['sender_name']); 
                    $kopia_maila   = Funkcje::parsujZmienne($tresc['dw']);

                    $adresat_email = $filtr->process( $info['customers_email_address'] );
                    $adresat_nazwa = $filtr->process( $info['customers_name'] );
                    
                    $odpowiedz_email = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                    $odpowiedz_nazwa = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);        
                    
                    $temat         = $filtr->process( $tresc['email_title'] );
                    $tekst         = $filtr->process( $tresc['description'] );
                    $tekst         = str_replace('{LINK_DO_FORMULARZA_OPINII}', ADRES_URL_SKLEPU . '/napisz-opinie-o-sklepie.html/opinia=' . base64_encode(serialize(array('id' => $info['orders_id'], 'czas' => FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])))), (string)$tekst);
                    
                    $zalaczniki    = $tablicaZalacznikow;
                    $szablon       = $tresc['template_id'];
                    $jezyk         = (((int)$info['language_id'] > 0) ? $info['language_id'] : (int)$_SESSION['domyslnyJezyk']['id']);  

                    $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $kopia_maila, $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email,$odpowiedz_nazwa);

                    $GLOBALS['db']->close_query($sqlMail);
                    unset($email, $tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $kopia_maila, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk);           
                    
                    // zapisuje dane w bazie o wyslaniu
                    $pola = array(array('review_date','now()'));
                    $GLOBALS['db']->update_query('orders' , $pola, " orders_id = '" . $info['orders_id'] . "'");	    
                    
                }
                
                $GLOBALS['db']->close_query($sqlt);
                unset($zapytanie_testowe);
                
                $TablicaZamowien[] = $info['orders_id'];
                
            }
            
        }
      
    }
    
    unset($TablicaZamowien);

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);     

  }
  
  // funkcja wysyla wiadomosci email z prosba o recenzje
  public static function WyslijMailRecenzje() {
    global $filtr;
    
    $TablicaZamowien = array();
    
    $dodatkowy_warunek = " AND c.customers_reviews = '1'";

    $zapytanie = "SELECT o.orders_id, o.customers_email_address, o.customers_name, o.date_purchased, c.language_id, c.customers_newsletter 
                    FROM orders o
               LEFT JOIN customers c ON o.customers_id = c.customers_id
                   WHERE c.customers_reviews = '1' 
                     AND o.orders_status in (" . ((RECENZJE_STATUSY_ZAMOWIEN == '') ? '0' : RECENZJE_STATUSY_ZAMOWIEN) . ")
                     AND STR_TO_DATE(o.date_purchased,'%Y-%m-%d') = '" . date('Y-m-d', time() - (RECENZJE_ILE_DNI * 86400)) . "' 
                     AND (o.reviews_products_date = '0000-00-00 00:00:00' or o.reviews_products_date is null)";
                         
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while ($info = $sql->fetch_assoc()) {     

            if ( !in_array($info['orders_id'], $TablicaZamowien) ) {
              
                // sprawdzi dodatkowo czy nie bylo wyslanego maila
                $zapytanie_testowe = "SELECT distinct o.orders_id, o.customers_email_address FROM orders o WHERE o.orders_id = '" . $info['orders_id'] . "' AND (o.reviews_products_date = '0000-00-00 00:00:00' or o.reviews_products_date is null)";
                $sqlt = $GLOBALS['db']->open_query($zapytanie_testowe);

                if ((int)$GLOBALS['db']->ile_rekordow($sqlt) > 0) {

                    $zapytanie_tresc = "SELECT t.sender_name, 
                                               t.email_var_id, 
                                               t.sender_email, 
                                               t.dw, 
                                               t.template_id, 
                                               t.email_file, 
                                               tz.email_title, 
                                               tz.description
                                          FROM email_text t 
                                     LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (((int)$info['language_id'] > 0) ? $info['language_id'] : (int)$_SESSION['domyslnyJezyk']['id']) . "' 
                                         WHERE t.email_var_id = 'RECENZJA_O_PRODUKTACH'";
                                         
                    $sqlMail = $GLOBALS['db']->open_query($zapytanie_tresc);
                    $tresc = $sqlMail->fetch_assoc();    

                    $email = new Mailing;
                    
                    if ( $tresc['email_file'] != '' ) {
                      $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
                    } else {
                      $tablicaZalacznikow = array();
                    }

                    $nadawca_email = Funkcje::parsujZmienne($tresc['sender_email']);
                    $nadawca_nazwa = Funkcje::parsujZmienne($tresc['sender_name']); 
                    $kopia_maila   = Funkcje::parsujZmienne($tresc['dw']);

                    $adresat_email = $filtr->process( $info['customers_email_address'] );
                    $adresat_nazwa = $filtr->process( $info['customers_name'] );
                    
                    $odpowiedz_email = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
                    $odpowiedz_nazwa = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);        
                    
                    $temat         = $filtr->process( $tresc['email_title'] );
                    $tekst         = $filtr->process( $tresc['description'] );
                    
                    $zamowienie    = new Zamowienie((int)$info['orders_id']);
                    
                    if ( count($zamowienie->info) > 0 ) {

                        $hashKod = '/nr=' . $zamowienie->info['id_zamowienia'] . '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
                        
                        $LinkiRecenzji = array();
                        foreach ( $zamowienie->produkty as $id => $produkt ) {
                            //
                            if ( $produkt['id_produktu'] > 0 ) {
                                 //
                                 if ( isset($zamowienie->link_recenzji[$produkt['id_produktu']]) ) {
                                      $LinkiRecenzji[ $produkt['id_produktu'] ] = '<a href="' . ADRES_URL_SKLEPU . '/napisz-recenzje-rw-' . $produkt['id_produktu'] . '.html/recenzja=' . $zamowienie->link_recenzji[$produkt['id_produktu']] . $hashKod . '">' . $produkt['nazwa'] . '</a>';
                                 }
                                 //
                            }
                            //
                        }
                        
                        unset($zamowienie, $hashKod);
                        
                        $tekst         = str_replace('{LINKI_DO_RECENZJI}', implode('<br />', (array)$LinkiRecenzji), (string)$tekst);        
                        $tekst         = str_replace('{ILOSC_PKT_ZA_RECENZJE}', (string)SYSTEM_PUNKTOW_PUNKTY_RECENZJE, (string)$tekst);
                        
                        $zalaczniki    = $tablicaZalacznikow;
                        $szablon       = $tresc['template_id'];
                        $jezyk         = (((int)$info['language_id'] > 0) ? $info['language_id'] : (int)$_SESSION['domyslnyJezyk']['id']);  

                        if ( count($LinkiRecenzji) ) {
                             $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $kopia_maila, $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email,$odpowiedz_nazwa);
                        }

                        unset($szablon, $jezyk, $LinkiRecenzji);           
                        
                        // zapisuje dane w bazie o wyslaniu
                        $pola = array(array('reviews_products_date','now()'));
                        $GLOBALS['db']->update_query('orders' , $pola, " orders_id = '" . $info['orders_id'] . "'");	    
                    
                        $TablicaZamowien[] = $info['orders_id'];
                        
                    }
                    
                    unset($email, $tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $kopia_maila, $adresat_nazwa, $temat, $tekst);             
                    $GLOBALS['db']->close_query($sqlMail);
                    
                }
                
                $GLOBALS['db']->close_query($sqlt);
                unset($zapytanie_testowe);

            }
            
        }
        
    }
    
    unset($TablicaZamowien);

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);     

  }  
  
  // funkcja sprawdzajaca poprawnosc nip
  public static function sprawdzNip($nip, $kraj) {
     //
     ini_set('default_socket_timeout',60);
     
     if ( !empty($nip) ){
        $nip = str_replace(array('A','B','C','D','E','F','G','H','I','K','L','M','N','O','P','Q','R','S','T','V','X','Y','Z','-',' ',',','.'),'',mb_strtoupper((string)$nip));
     }

     if ( !empty($kraj) ){
        $kraj = mb_strtoupper((string)$kraj);
     }

     $sprawdzVat = array('countryCode' => $kraj, 'vatNumber' => $nip);

     $klient = new SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl',
            array('trace' => false,
                  'exceptions' => true, 
                  'encoding'=> 'UTF-8', 
                  'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                  "stream_context" => stream_context_create(
                      array(
                        'ssl' => array(
                            'verify_peer'       => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                         )
                      )
                  )
     ));

     try {
        $wynik = $klient->checkVat($sprawdzVat);
        if ( true === (bool)$wynik->valid){
          return true;
        } else {
          return false;
        }
     } catch (SoapFault $exception) {		
        return false;
     }
     //
  }     

  // funkcja zwraca tablice z danymi klineta - uzywane do edrone
  public static function daneKlienta($id) {
     //
     $info = array();
     
     $zapytanie = "SELECT c.customers_id, 
                          c.customers_firstname as Imie,
                          c.customers_lastname as Nazwisko,                          
                          c.customers_email_address as Email,
                          c.customers_telephone as Telefon,
                          c.customers_default_address_id,
                          c.customers_newsletter as Newsletter,
                          ca.entry_country_id,
                          ca.entry_city as Miasto,
                          cd.countries_name as Kraj
                     FROM customers c 
                LEFT JOIN address_book ca ON ca.customers_id = c.customers_id and ca.address_book_id = c.customers_default_address_id
                LEFT JOIN countries_description cd ON cd.countries_id = ca.entry_country_id and cd.language_id = '1'
                    WHERE c.customers_id = '" . $id . "'";

     $sql = $GLOBALS['db']->open_query($zapytanie);
    
     if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
       
        $info = $sql->fetch_assoc(); 
        
     }
     
     //
     $GLOBALS['db']->close_query($sql);
     unset($zapytanie);          
     //
     return $info;
     //
  }

  // funkcja do wyswietlania kodu ISO panstwa
  public static function pokazISOPanstwa($kraj, $jezyk = '1') {
    
    $wynik = '';

    $zapytanie = "
    SELECT c.countries_id, c.countries_iso_code_2, cd.countries_name 
        FROM countries c
        LEFT JOIN countries_description cd ON c.countries_id = cd.countries_id
        WHERE cd.countries_name = '".$kraj."' AND cd.language_id = '".(int)$jezyk."'
    ";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($nazwa_grupy = $sql->fetch_assoc()) {
      $wynik = $nazwa_grupy['countries_iso_code_2'];
    }
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie);

  	return $wynik;
  }


} 

?>