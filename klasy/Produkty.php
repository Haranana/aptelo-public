<?php

class Produkty {

  // zwraca tablice z jednostkami miary produktow
  public static function TablicaJednostekMiaryProduktow($brak = '') {

    $sql = $GLOBALS['db']->open_query("SELECT * FROM products_jm s, products_jm_description sd where s.products_jm_id = sd.products_jm_id and sd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."' ORDER BY sd.products_jm_name");  

    $tab = array();
    if ($brak != '') {
        $tab['0'] = array('id' => 0,
                          'text' => $brak);
    } 
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
    
        while ($jm = $sql->fetch_assoc()) {
            $tab[$jm['products_jm_id']] = array('id' => $jm['products_jm_id'],
                                                'text' => $jm['products_jm_name']);
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);                   
    return $tab;
    
  } 

  // zwraca tablice stawkami VAT - na potrzeby formularza faktury
  public static function TablicaStawekVat($brak = '') {

    $sql = $GLOBALS['db']->open_query("SELECT * FROM tax_rates ORDER BY sort_order");  

    $tab = array();
    if ($brak != '') {
        $tab[] = array('id' => 0,
                       'text' => $brak);
    } 
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
    
        while ($vat = $sql->fetch_assoc()) {
            $tab[] = array('id' => $vat['tax_rate'].'|'.$vat['tax_short_description'],
                           'text' => $vat['tax_short_description']);
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);                   
    return $tab;
  }   
  
  // funkcja zwraca wartosc vat po id
  public static function PokazStawkeVAT( $vat_id, $pelna = false ) {

    $wynik = '0';
    $zapytanie = "SELECT * FROM tax_rates WHERE tax_rates_id = '".$vat_id."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $stawka_vat = $sql->fetch_assoc();
        if ( $pelna == false ) {
             $wynik = $stawka_vat['tax_rate'];
          } else {
             if ( isset($stawka_vat['tax_rates_id']) ) {
                  $wynik = array('id' => $stawka_vat['tax_rates_id'],
                                 'stawka' => $stawka_vat['tax_rate'],
                                 'opis' => $stawka_vat['tax_description'],
                                 'opis_krotki' => $stawka_vat['tax_short_description'],
                                 'domyslny' => $stawka_vat['tax_default']);   
             } else {
                  $wynik = array();
             }
        }
        
    }

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  }    
  
  // zwraca jednostke miary produktow
  public static function PokazJednostkeMiary($id) {

    $sql = $GLOBALS['db']->open_query("SELECT * FROM products_jm_description WHERE products_jm_id = '".(int)$id."' and language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'");  

    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while ($jm = $sql->fetch_assoc()) {
          $nazwa = $jm['products_jm_name'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);                   
    return $nazwa;
  }  
  
  // pasek stanu magazynowego produktu - zalezny od stanu magazynowego
  public static function PokazPasekMagazynu($ilosc, $alarm = 0) {
  
    $wynik = '';
    
    if ( $alarm == 0 ) {
         $alarm = MAGAZYN_STAN_MINIMALNY;
    }

    if ( MAGAZYN_STAN_MINIMALNY == '' ) {
         $alarm = 0;
    }

    $alarm = (int)$alarm;
  
    // jezeli liczba produktow = 0
    if ($ilosc <= 0) {
        $wynik = ( Wyglad::TypSzablonu() == true ? '<span class="MagazynIlosc" style="--ilosc: 0.0;"></span>' : '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/0.gif" width="86" height="8" alt="" />' );
    }
    
    // jezeli liczba produktow jest wieksza o 0 ale mniejsza o 1/2 liczby minimalnej
    if ($ilosc > 0 && $ilosc <= $alarm / 2) {
        $wynik = ( Wyglad::TypSzablonu() == true ? '<span class="MagazynIlosc" style="--ilosc: 1.0;"></span>' : '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/1.gif" width="86" height="8" alt="" />' );
    }
    
    // jezeli liczba produktow jest wieksza od 1/2 liczby minimalnej ale mniejsza od liczby minimalnej
    if ($ilosc > $alarm / 2 && $ilosc <= $alarm) {
        $wynik = ( Wyglad::TypSzablonu() == true ? '<span class="MagazynIlosc" style="--ilosc: 2.0;"></span>' : '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/2.gif" width="86" height="8" alt="" />' );
    }
    
    // jezeli liczba produktow jest wieksza od liczby minimalnej ale mniejsza od 1,5 liczby minimalnej
    if ($ilosc > $alarm && $ilosc <= $alarm * 1.5) {
        $wynik = ( Wyglad::TypSzablonu() == true ? '<span class="MagazynIlosc" style="--ilosc: 3.0;"></span>' : '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/3.gif" width="86" height="8" alt="" />' );
    }
    
    // jezeli liczba produktow jest wieksza od 1,5 liczby minimanlej ale mniejsza od 2-krotnosci liczby mininalnej
    if ($ilosc > $alarm * 1.5 && $ilosc <= $alarm * 2) {
        $wynik = ( Wyglad::TypSzablonu() == true ? '<span class="MagazynIlosc" style="--ilosc: 4.0;"></span>' : '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/4.gif" width="86" height="8" alt="" />' );
    }
    
    // jezeli liczba produktow jest wieksza od 2-krotnosci liczby minimalnej
    if ($ilosc > $alarm * 2) {
        $wynik = ( Wyglad::TypSzablonu() == true ? '<span class="MagazynIlosc" style="--ilosc: 5.0;"></span>' : '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/5.gif" width="86" height="8" alt="" />' );
    }   

    return $wynik;
    
  }
  
  // -------------------------------------------------------
  
  // zapytanie o promocje 
  public static function SqlPromocjeProste( $warunek = '' ) {
    //
    $warunek_dat = " ";
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE c.categories_status = '1' AND p.specials_status = '1' AND 
                         (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end) AND
                         p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunek;     
    return $zapytanie;
    //
  }
  
  // zapytanie o promocje (strona z listingiem)
  public static function SqlPromocjeZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.specials_status = '1' AND                                  
                                  p.products_status = '1' and p.listing_status = '0' AND " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' p.products_quantity > 0 AND ' : '' ) . "
                                  (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end) " . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie);                                  
                  
    return $zapytanie;
    //
  }
  
  // zapytanie o wyprzedaze 
  public static function SqlWyprzedazProste( $warunek = '' ) {
    //
    $warunek_dat = " ";
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                   WHERE c.categories_status = '1' AND p.sale_status = '1' AND p.specials_status = '0' AND p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunek;     
    return $zapytanie;
    //
  }
  
  // zapytanie o wyprzedaze (strona z listingiem)
  public static function SqlWyprzedazZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                       INNER JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                       INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE c.categories_status = '1' AND p.sale_status = '1' AND p.specials_status = '0' AND p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;   
                            
    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o nowosci
  public static function SqlNowosciProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id 
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND 
                         p.new_status = '1' AND 
                         p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] . $warunek;
                  
    return $zapytanie;
    //
  } 
  
  // zapytanie o nowosci (strona z listingiem)
  public static function SqlNowosciZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                       INNER JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                       INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE c.categories_status = '1' AND p.new_status = '1' AND                                  
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie);                                   
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o nowosci
  public static function SqlPolecaneProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id 
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                   WHERE c.categories_status = '1' AND p.featured_status = '1' AND p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunek;

    return $zapytanie;
    //
  }   
  
  // zapytanie o polecane (strona z listingiem)
  public static function SqlPolecaneZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                       INNER JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                       INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.featured_status = '1' AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;
                                  
    $zapytanie .= Produkty::PodzialSortowania($sortowanie);                               
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie o nasz hit
  public static function SqlNaszHitProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                   WHERE c.categories_status = '1' AND p.star_status = '1' AND 
                         p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunek;
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie o hity (strona z listingiem)
  public static function SqlNaszHitZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                       INNER JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                       INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.star_status = '1' AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o bestsellery
  public static function SqlBestselleryProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . " AND
                         p.products_ordered > 0 " . $warunek;
      
    return $zapytanie;
    //
  }    

  // zapytanie o bestsellery (w boxach)
  public static function SqlBestsellery( $ilosc = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_ordered, 
                         pd.products_name, pd.products_seo_url
                    FROM products p
              INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' AND p.products_ordered > 0 " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . "
                ORDER BY p.products_ordered DESC
                   LIMIT " . $ilosc ;
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o bestsellery (strona z listingiem)
  public static function SqlBestselleryZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                       INNER JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id                       
                            WHERE p.products_ordered > 0 AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;
                                  
    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
                  
    return $zapytanie;
    //
  }    
  
  // zapytanie o produkty oczekiwane
  public static function SqlOczekiwaneProste( $warunek = '' ) {
    //
    $data = date('Y-m-d');
    
    $zapytanie = "SELECT DISTINCT p.products_id 
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND p.products_date_available > '" . $data . "' AND 
                         p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunek;
                  
    unset($data);
    
    return $zapytanie;
    //
  }   
  
  // zapytanie o produkty oczekiwane (strona z listingiem)
  public static function SqlOczekiwaneZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $data = date('Y-m-d');
    
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                       INNER JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                       INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.products_date_available > '" . $data . "' AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
                                  
    unset($data);

    return $zapytanie;
    //
  }   
  
  // zapytanie o produkty z recenzjami
  public static function SqlProduktyZawierajaceRecenzje() {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN reviews r ON p.products_id = r.products_id AND r.approved = '1'
              INNER JOIN reviews_description rd ON r.reviews_id = rd.reviews_id AND rd.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND p.products_status = '1' " . $GLOBALS['warunekProduktu'];
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o recenzje
  public static function SqlRecenzje( $sortowanie ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, r.reviews_id
                    FROM reviews r
              INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id AND rd.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
              INNER JOIN products p ON p.products_id = r.products_id AND p.products_status = '1' " . $GLOBALS['warunekProduktu']
               . ((strpos((string)$sortowanie, 'pd.') > -1) ? "LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'" : "") . "
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND r.approved = '1'
                ORDER BY " . $sortowanie;

    return $zapytanie;
    //
  } 

  // zapytanie do recenzji
  public static function SqlRecenzja( $id ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM reviews r
              INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id AND rd.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
              INNER JOIN products p ON p.products_id = r.products_id AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . "
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND r.approved = '1' AND 
                         r.reviews_id = '" . $id . "'";
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie do napisz recenzje
  public static function SqlNapiszRecenzje( $id ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND p.products_id = '" . $id . "' AND p.products_status = '1' " . $GLOBALS['warunekProduktu'];
                  
    return $zapytanie;
    //
  }

  // zapytanie do szukania
  public static function SqlSzukajProdukty( $warunkiSzukania, $sortowanie, $dodatkowePola = '' ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id ";
    if ( $dodatkowePola != '' ) {
        $zapytanie .= "LEFT JOIN products_to_products_extra_fields p2pef ON p.products_id = p2pef.products_id AND products_extra_fields_id IN (".$dodatkowePola.") ";
    }
    $zapytanie .= "    INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                        LEFT JOIN products_stock ps ON ps.products_id = p.products_id
                            WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . ""  . $GLOBALS['warunekProduktu'] . $warunkiSzukania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 

    return $zapytanie;
    //
  }  

  // zapytanie do porownania produktow
  public static function SqlPorownanieProduktow( $doPorownaniaId ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_status, pd.products_name, pd.products_seo_url
                             FROM products p
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                            WHERE c.categories_status = '1' AND p.products_id in (" . $doPorownaniaId . ")
                         ORDER BY pd.products_name";  
                  
    return $zapytanie;
    //
  }  

  // zapytanie od id produktow do listingu z kategorii  
  public static function SqlProduktyKategorii( $idPodkategorii, $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . " 
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                        LEFT JOIN products_to_categories c ON c.products_id = p.products_id
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE c.categories_id in (" . $idPodkategorii . ") AND
                                  " . ( LISTING_PRODUKTY_ZERO == 'nie' ? 'p.products_quantity > 0 AND ' : '' ) . "
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;
                                  
    $zapytanie .= Produkty::PodzialSortowania($sortowanie);                              

    return $zapytanie;
    //
  } 
  
  // zapytanie od id produktow do listingu z kategorii - do stronicowania - ograniczone !!  
  public static function SqlProduktyKategoriiStronicowanie( $idPodkategorii, $warunkiFiltrowania ) {
    //
    if ( strpos((string)$warunkiFiltrowania, 'cu.value') > -1 ) {
      
        $zapytanie = Produkty::SqlProduktyKategorii($idPodkategorii, $warunkiFiltrowania, 'pd.products_name');
        
    } else {
      
        $zapytanie = "SELECT DISTINCT p.products_id 
                                 FROM products p
                            LEFT JOIN products_to_categories c ON c.products_id = p.products_id
                                WHERE c.categories_id in (" . $idPodkategorii . ") AND
                                      " . ( LISTING_PRODUKTY_ZERO == 'nie' ? 'p.products_quantity > 0 AND ' : '' ) . "
                                      p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;   

    }

    return $zapytanie;
    //
  }   
  
  // zapytanie od id produktow do listingu z producenta 
  public static function SqlProduktyProducenta( $idProducenta, $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.manufacturers_id = '" . $idProducenta . "' AND
                                  c.categories_status = '1' AND
                                  " . ( LISTING_PRODUKTY_ZERO == 'nie' ? 'p.products_quantity > 0 AND ' : '' ) . "
                                  p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 

    return $zapytanie;
    //
  }  
  
  // zapytanie od id produktow do listingu z producenta - do stronicowania - ograniczone !! 
  public static function SqlProduktyProducentaStronicowanie( $idProducenta, $warunkiFiltrowania ) {
    //
    if ( strpos((string)$warunkiFiltrowania, 'cu.value') > -1 ) {
      
        $zapytanie = Produkty::SqlProduktyProducenta($idProducenta, $warunkiFiltrowania, 'pd.products_name');
        
    } else {
          
        $zapytanie = "SELECT DISTINCT p.products_id
                                 FROM products p
                           INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                           INNER JOIN categories c ON c.categories_id = ptc.categories_id  
                                WHERE p.manufacturers_id = '" . (int)$idProducenta . "' AND
                                      c.categories_status = '1' AND
                                      " . ( LISTING_PRODUKTY_ZERO == 'nie' ? 'p.products_quantity > 0 AND ' : '' ) . "
                                      p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;   
                                      
    }

    return $zapytanie;
    //
  }    
  
  // zapytanie dla akcesorii dodatkowych
  public static function SqlProduktyAkcesoriaDodatkowe( $idProduktu, $ilosc = 9999 ) {
    //
    // do jakich kategorii nalezy produkt
    $idKategorie = Kategorie::ProduktKategorie( $idProduktu );
    //
    $zapytanie = "SELECT DISTINCT pa.pacc_products_id_slave as products_id 
                             FROM products_accesories pa
                       INNER JOIN products p ON p.products_id = pa.pacc_products_id_slave AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . "
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                            WHERE c.categories_status = '1' AND (pa.pacc_products_id_master = '" . (int)$idProduktu . "' and pa.pacc_type = 'produkt')" . 
                                  ((count($idKategorie) > 0 ) ? " || (pa.pacc_products_id_master in (" . implode(',', (array)$idKategorie) . ") and pa.pacc_type = 'kategoria') " : "") . "ORDER BY pa.pacc_sort_order" . (($ilosc < 9999) ? " limit " . $ilosc : ""); 
                               
    return $zapytanie;
    //
  }
  
  // zapytanie dla produktow podobnych
  public static function SqlProduktyPodobne( $idProduktu, $ilosc = KARTA_PRODUKTU_PODOBNE_PRODUKTY_ILOSC ) {
    //
    $zapytanie = "SELECT DISTINCT pa.pop_products_id_slave as products_id
                             FROM products_options_products pa
                       INNER JOIN products p ON p.products_id = pa.pop_products_id_slave AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . "
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                            WHERE c.categories_status = '1' AND " . ( LISTING_PRODUKTY_ZERO == 'nie' ? 'p.products_quantity > 0 AND ' : '' ) . " pa.pop_products_id_master = '" . (int)$idProduktu . "' ORDER BY RAND() LIMIT " . $ilosc;
            
    return $zapytanie;
    //
  } 

  // zapytanie dla produktow powiazanych
  public static function SqlProduktyPowiazane( $idProduktu, $ilosc = 500 ) {
    //
    $zapytanie = "SELECT DISTINCT pa.prp_products_id_slave as products_id
                             FROM products_related_products pa
                       INNER JOIN products p ON p.products_id = pa.prp_products_id_slave AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . "
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                            WHERE c.categories_status = '1' AND " . ( LISTING_PRODUKTY_ZERO == 'nie' ? 'p.products_quantity > 0 AND ' : '' ) . " pa.prp_products_id_master = '" . (int)$idProduktu . "' ORDER BY p.sort_order LIMIT " . $ilosc;
            
    return $zapytanie;
    //
  }  
  
  // zapytanie dla klienci zakupili takze
  public static function SqlProduktyKlienciKupiliTakze( $idProduktu, $naZamowien ) {
    //
    if ( count($naZamowien) > 0 ) {
        //
        $zapytanie = "SELECT p.products_id
                        FROM orders_products opb
                  INNER JOIN orders o ON opb.orders_id = o.orders_id
                  INNER JOIN products p ON opb.products_id = p.products_id
                  INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                  INNER JOIN categories c ON c.categories_id = ptc.categories_id
                       WHERE p.products_status = '1' AND p.listing_status = '0'
                             " . (LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0' : '') . "
                             " . $GLOBALS['warunekProduktu'] . "
                             AND opb.products_id != '" . (int)$idProduktu . "' 
                             AND o.orders_id IN (" . implode(',', (array)$naZamowien) . ") 
                             AND c.categories_status = '1'
                   GROUP BY p.products_id
                   ORDER BY RAND()
                      LIMIT " . (int)KARTA_PRODUKTU_KLIENCI_KUPILI_TAKZE_ILOSC;
                             
        //
      } else {
        //
        $zapytanie = "SELECT p.products_id FROM products p WHERE p.products_status = '2'";
        //
    }
    
    return $zapytanie;
    //
  }  

  // zapytanie o nasz hit
  public static function SqlProduktyPozostaleKategorii( $idKategoriiProducenta, $typ, $idProduktu ) {
    //
    $zapytanie = 'SELECT DISTINCT p.products_id FROM products p WHERE p.products_id = 0';
    //
    if ( $typ == 'kategoria' ) {
        //
        $zapytanie = "SELECT DISTINCT p.products_id
                        FROM products p
                  INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id AND ptc.categories_id = '" . (int)$idKategoriiProducenta . "'
                  INNER JOIN categories c ON c.categories_id = ptc.categories_id
                       WHERE c.categories_status = '1' AND p.products_id != '" . (int)$idProduktu . "' AND p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . " ORDER BY RAND() LIMIT " . KARTA_PRODUKTU_POZOSTALE_PRODUKTY_ILOSC;
        //
    }
    
    if ( $typ == 'producent' ) {
        //
        $zapytanie = "SELECT DISTINCT p.products_id
                        FROM products p
                  INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                  INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                       WHERE c.categories_status = '1' AND p.products_id != '" . (int)$idProduktu . "' AND p.manufacturers_id = '" . (int)$idKategoriiProducenta . "' AND p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] . " ORDER BY RAND() LIMIT " . KARTA_PRODUKTU_POZOSTALE_PRODUKTY_ILOSC;
        //
    }    

    return $zapytanie;
    //
  }    
  
  // zapytanie o produkty nastepny poprzedni
  public static function ProduktyPoprzedniNastepny( $idKategoriiProducenta, $sortowanie, $idProduktu  ) {
    //
    //
    $tablica = array();

    $zapytanie = "SELECT DISTINCT p.products_id, p.sort_order, pd.products_name
                        FROM products p
                  INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id AND ptc.categories_id = '" . (int)$idKategoriiProducenta . "'
                  INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                   LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                       WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'];
                       
    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
    
    $sql = $GLOBALS['db']->open_query($zapytanie);
    $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);

    if ( $GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();

        // Jezeli jest tylko jeden produkt
        $Poprzedni = $Nastepny = $info['products_id'];
        $PoprzedniNazwa = $NastepnyNazwa = $info['products_name'];

        // Jezeli wybrany produkt jest pierwszy
        if ($info['products_id'] == (int)$idProduktu ) {

            $info = $sql->fetch_assoc();
            $Poprzedni = $Nastepny = $info['products_id'];
            $PoprzedniNazwa = $NastepnyNazwa = $info['products_name'];
            while ( $info = $sql->fetch_assoc() ) {
                $Poprzedni = $info['products_id'];
                $PoprzedniNazwa = $info['products_name'];
            }
        // Jezeli nie jest to pierwszy produkt
        } else { 
            while ( $info = $sql->fetch_assoc() ) {
                if ( $info['products_id'] == (int)$idProduktu ) {
                    $info = $sql->fetch_assoc();
                    $Nastepny = $info['products_id'];
                    $NastepnyNazwa = $info['products_name'];
                    break;
                } else {
                    $Poprzedni = $info['products_id'];
                    $PoprzedniNazwa = $info['products_name'];
                }
            }
        }

        $GLOBALS['db']->znajdz_rekord($sql, 0);
        $info = $sql->fetch_assoc();
        $PierwszyProdukt = $info['products_id'];

        $GLOBALS['db']->znajdz_rekord($sql, $IloscProduktow-1);
        $info = $sql->fetch_assoc();
        $OstatniProdukt = $info['products_id'];

        if ( $PierwszyProdukt != $idProduktu ) {
            $tablica['prev'] = array('id' => $Poprzedni,
                                     'nazwa' => $PoprzedniNazwa
                            );
            }
        if ( $OstatniProdukt != $idProduktu ) {
            $tablica['next'] = array('id' => $Nastepny,
                                     'nazwa' => $NastepnyNazwa
                            );
        }
    }
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);    

    return $tablica;
    //
  }    

  // zapytanie do cennika - id produktow z danej kategorii
  public static function SqlProduktyCennik( $idKat ) {
  
    $IdPodkategorii = $idKat . ',';
    //    
    // musi znalezc podkategorie dla danej kategorii
    foreach(Kategorie::DrzewoKategorii($idKat) as $IdKategorii => $Tablica) {
        $IdPodkategorii .= Kategorie::TablicaPodkategorie($Tablica);
    }                 
    //
    $IdPodkategorii = substr((string)$IdPodkategorii, 0, -1);        
    //       
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id AND ptc.categories_id in (" . $IdPodkategorii . ")
              INNER JOIN categories c ON c.categories_id = ptc.categories_id 
              INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                   WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . "
                ORDER BY p.sort_order, pd.products_name";

    unset($IdPodkategorii);

    return $zapytanie;
    //
  }   
  
  // zapytanie o produkty dla autouzupelnienia
  public static function SqlAutoUzupelnienie() {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, pd.products_name
                    FROM products p
              INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
              INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              INNER JOIN categories c ON c.categories_id = ptc.categories_id
                   WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] . "
                ORDER BY pd.products_name ASC";
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o wszystkie produkty - do katalogu produktow
  public static function SqlProduktyZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                } else {
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                  " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;

    $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o wszystkie produkty
  public static function SqlProduktyProste( $sortowanie = '', $limit = '', $warunek = '', $bez_cen = false ) {
    //
    if ( $bez_cen == false ) {
         //
         if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
              if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
                   //
                   if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                        $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                     } else {
                        $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
                   }
                 } else {
                   $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
              }    
            } else {
              $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
         }   
         //
         $zapytanie = "SELECT DISTINCT p.products_id, p.products_date_added, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                                       " . (($sortowanie == 'p.products_ordered desc') ? ',(SELECT count(o.orders_id) FROM orders o, orders_products op WHERE o.orders_id = op.orders_id AND op.products_id = p.products_id AND o.orders_id > 0) as IloscZamowien' : '') . "
                                  FROM products p
                             LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                            INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                            INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                             LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                 WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] .  $warunek;

         $zapytanie .= Produkty::PodzialSortowania($sortowanie); 
         
         $zapytanie .= (( $limit != '' ) ? " LIMIT " . $limit : "" );
 
         return $zapytanie;
         //
    } else { 
         //
         $zapytanie = "SELECT DISTINCT p.products_id
                                  FROM products p
                            INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                            INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                                 WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'];
                                 
         return $zapytanie;
         //
    }
    
  }        
  
  // zapytanie o wszystkie produkty
  public static function SqlProduktyWszystkieStronicowanie( $warunkiFiltrowania ) {
    
    if ( strpos((string)$warunkiFiltrowania, 'cu.value') > -1 ) {
      
        $zapytanie = Produkty::SqlProduktyZlozone($warunkiFiltrowania, 'pd.products_name');
        
    } else {
      
        $zapytanie = "SELECT DISTINCT p.products_id
                                 FROM products p
                           INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                           INNER JOIN categories c ON c.categories_id = ptc.categories_id  
                                WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania;   
            
    }

    return $zapytanie;
    //
  }      
  
  // zapytanie o wszystkie produkty do statystyki
  public static function SqlProduktyProsteStatystyka() {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                             FROM products p
                       INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       INNER JOIN categories c ON c.categories_id = ptc.categories_id
                            WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . " " . $GLOBALS['warunekProduktu'];
                  
    return $zapytanie;
    //
  }   
    
  // funkcja zwracajaca nazwe kategorii do jakiej nalezy produkt
  public static function pokazKategorieProduktu( $produkt_id ) {

    $wynik = '';

    $zapytanie = "
               SELECT cd.categories_name FROM categories_description cd
               LEFT JOIN products_to_categories p2c ON p2c.categories_id = cd.categories_id
               WHERE p2c.products_id = '".$produkt_id."' AND cd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."' ";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
      
        while ($info = $sql->fetch_assoc()) {
            $wynik = $info['categories_name'];      
        }
        
        unset($info);
        
    }
    
    //
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $sql);

    return $wynik;

  }
  
  // funkcja zwraca tablice id produktow modulowych
  public static function ProduktyModulowe( $limit = 9999, $modul = 'nowosci', $warunek = '' ) {
  
    switch ($modul) {
        case "polecane":
            $plik = 'PolecaneProste';
            $cache = CACHE_POLECANE;
            $sqlZap = Produkty::SqlPolecaneProste( $warunek );
            break;  
        case "oczekiwane":
            $plik = 'OczekiwaneProste';
            $cache = CACHE_OCZEKIWANE;
            $sqlZap = Produkty::SqlOczekiwaneProste( $warunek );
            break;
        case "nowosci":
            if ( $warunek == '' ) {
                $warunek = ' ORDER BY p.products_date_added DESC ';
            }
            $plik = 'NowosciProste';
            $cache = CACHE_NOWOSCI;
            $sqlZap = Produkty::SqlNowosciProste( $warunek );
            break; 
        case "hity":
            $plik = 'NaszHitProste';
            $cache = CACHE_HITY;
            $sqlZap = Produkty::SqlNaszHitProste( $warunek );
            break; 
        case "promocje":
            $plik = 'PromocjeProste';
            $cache = CACHE_PROMOCJE;
            $sqlZap = Produkty::SqlPromocjeProste( $warunek );
            break; 
        // wszystkie produkty
        case "produkty":
            $plik = 'Produkty';
            $cache = CACHE_PRODUKTY;
            $sqlZap = Produkty::SqlProduktyProste( '', $limit, $warunek, true );
            break;             
    }      
  
    $Tablica = array();
    $WybraneProdukty = array();
    
    // jezeli nie ma dodatkowego warunku produktow
    if ( $warunek == '' ) {

          // cache zapytania
          $WynikCache = $GLOBALS['cache']->odczytaj($plik, $cache, true);

          if ( !$WynikCache && !is_array($WynikCache) ) {
               $sql_random = $GLOBALS['db']->open_query( $sqlZap );
               while ($info_random = $sql_random->fetch_assoc()) {
                  $Tablica[] = $info_random['products_id'];
               }
               //
               $GLOBALS['cache']->zapisz($plik, $Tablica, $cache, true);
               //
               $GLOBALS['db']->close_query($sql_random); 
          } else {
               $Tablica = $WynikCache;
          }  
        
      } else {
      
          $sql_random = $GLOBALS['db']->open_query( $sqlZap );
          while ($info_random = $sql_random->fetch_assoc()) {
            $Tablica[] = $info_random['products_id'];
          }
          //
          $GLOBALS['db']->close_query($sql_random);       
      
    }
    
    // wybranie tylko unikalnych rekordow w tablicy
    $Tablica = array_unique($Tablica);
    
    if (count($Tablica) > 0) {
        $WybraneProdukty = explode(',', (string)Funkcje::wylosujElementyTablicyJakoTekst($Tablica, $limit));
    }
    
    unset($Tablica, $plik, $cache, $sqlZap);
    
    return $WybraneProdukty;
  
  }

  // funkcja zwraca tablice id produktow dla kreatora modulow
  public static function ProduktyKreatorModulow( $limit = 9999, $modul = 'nowosci', $sort = '', $id_kategorii = 0, $id_producenta = 0, $warunki_produktow = array(), $tylko_dostepne = 'nie' ) {
    
    $warunek_dostepne = '';
    
    if ( $tylko_dostepne == 'tak' ) {
         //
         $warunek_dostepne = ' AND p.products_quantity > 0';
         //
    }
      
    $sortowanie = '';
    
    if ( $modul != 'recenzje' ) {
      
        switch ($sort) {
            case "losowo":
                $sortowanie = ' ORDER BY rand() LIMIT 100';
                break;
            case "sort":
                $sortowanie = ' ORDER BY p.sort_order LIMIT 100';
                break;
            case "data":
                $sortowanie = ' ORDER BY p.products_date_added DESC LIMIT 100';
                break;
        }  

    }   

    if ( $modul == 'recenzje' ) {
      
        switch ($sort) {
            case "losowo":
                $sortowanie = ' ORDER BY rand() LIMIT 100';
                break;
            case "data":
                $sortowanie = ' ORDER BY r.date_added DESC LIMIT 100';
                break;
        }  

    }   

    $warunek = $warunek_dostepne . $sortowanie;
  
    switch ($modul) {
        case "bestsellery":
            $plik = 'BestselleryProste';
            $cache = CACHE_PRODUKTY;
            $sqlZap = Produkty::SqlBestselleryProste( $warunek );
            break; 
        case "hity":
            $plik = 'NaszHitProste';
            $cache = CACHE_HITY;
            $sqlZap = Produkty::SqlNaszHitProste( $warunek );
            break; 
        case "nowosci":
            $plik = 'NowosciProste';
            $cache = CACHE_NOWOSCI;
            $sqlZap = Produkty::SqlNowosciProste( $warunek );
            break; 
        case "polecane":
            $plik = 'PolecaneProste';
            $cache = CACHE_POLECANE;
            $sqlZap = Produkty::SqlPolecaneProste( $warunek );
            break;  
        case "promocje":
            $plik = 'PromocjeProste';
            $cache = CACHE_PROMOCJE;
            $sqlZap = Produkty::SqlPromocjeProste( $warunek );
            break; 
        case "promocje_czasowe":
            $plik = 'PromocjeProsteZegar';
            $cache = CACHE_PROMOCJE;
            $sqlZap = Produkty::SqlPromocjeProste( ' AND p.specials_date_end > "' . date('Y-m-d') . '"' . $warunek );
            break;             
        case "wyprzedaz":
            $plik = 'WyprzedazProste';
            $cache = CACHE_PROMOCJE;
            $sqlZap = Produkty::SqlWyprzedazProste( $warunek );
            break; 
        case "oczekiwane":
            $plik = 'OczekiwaneProste';
            $cache = CACHE_OCZEKIWANE;
            $sqlZap = Produkty::SqlOczekiwaneProste( $warunek );
            break;
        case "produkty":
            $plik = 'ProduktyProste';
            $cache = CACHE_PRODUKTY;
            $sqlZap = "SELECT DISTINCT p.products_id 
                         FROM products p
                   INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                   INNER JOIN categories c ON c.categories_id = ptc.categories_id
                        WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . $warunek;
            break; 
        case "kategoria":
            $plik = 'KategoriaProste_Id_' . $id_kategorii;
            $cache = CACHE_PRODUKTY;
            //
            // kategoria i jej podkategorie
            $IdPodkategorii = (int)$id_kategorii . ',';
            //        
            foreach(Kategorie::DrzewoKategorii((int)$id_kategorii) as $IdKategorii => $TablicaKat) {
                //
                $IdPodkategorii .= Kategorie::TablicaPodkategorie($TablicaKat);
                //
            }
            $IdPodkategorii = substr((string)$IdPodkategorii, 0, -1);               
            //
            $sqlZap = "SELECT DISTINCT p.products_id 
                         FROM products p
                   INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                   INNER JOIN categories c ON c.categories_id = ptc.categories_id
                        WHERE c.categories_status = '1' AND c.categories_id in (" . $IdPodkategorii . ") AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . $warunek;
            break;     
        case "producent":
            $plik = 'ProducentProste_Id_' . $id_producenta;
            $cache = CACHE_PRODUKTY;
            //
            $sqlZap = "SELECT DISTINCT p.products_id , p.products_date_added
                         FROM products p
                   INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                   INNER JOIN categories c ON c.categories_id = ptc.categories_id 
                        WHERE c.categories_status = '1' AND p.manufacturers_id = '" . $id_producenta . "' AND p.products_status = '1' and p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . $warunek;
            break; 
        case "recenzje":
            $plik = 'RecenzjeProste';
            $cache = CACHE_RECENZJE;
            //
            $sqlZap = "SELECT DISTINCT p.products_id, r.date_added
                                  FROM products p
                            INNER JOIN reviews r ON p.products_id = r.products_id AND r.approved = '1'
                            INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                            INNER JOIN categories c ON c.categories_id = ptc.categories_id
                                 WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0'" . $GLOBALS['warunekProduktu'] . $sortowanie; 
            break;    
        case "warunki":
            //
            $szukana_wartosc = '';
            //
            if ( isset($warunki_produktow['szukaj_nazwa']) && !empty(trim((string)$warunki_produktow['szukaj_nazwa'])) ) {
                 //
                 $szukana_wartosc .= " and pd.products_name LIKE '%" . $warunki_produktow['szukaj_nazwa'] . "%'";
                 //
            }
            if ( isset($warunki_produktow['szukaj_nr_kat']) && !empty(trim((string)$warunki_produktow['szukaj_nr_kat'])) ) {
                 //
                 $szukana_wartosc .= " and p.products_model LIKE '%" . $warunki_produktow['szukaj_nr_kat'] . "%'";
                 //
            }
            if ( isset($warunki_produktow['szukaj_tag']) && !empty(trim((string)$warunki_produktow['szukaj_tag'])) ) {
                 //
                 $szukana_wartosc .= " and pd.products_search_tag LIKE '%" . $warunki_produktow['szukaj_tag'] . "%'";
                 //
            }            
            //
            if ( $szukana_wartosc == '' ) {
                 //
                 $szukana_wartosc = " and p.products_id = 0";
                 //
            }
            //                 
            $sqlZap = "SELECT DISTINCT p.products_id
                                  FROM products p
                            INNER JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                            INNER JOIN categories c ON c.categories_id = ptc.categories_id
                                 WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' " . $szukana_wartosc . $warunek_dostepne . $GLOBALS['warunekProduktu'] . $sortowanie; 
                                 
            unset($szukana_wartosc);
            
            break;    
        case "poprzednio_ogladane":
            //
            $TablicaId = '0';
            //            
            if ( $_SESSION['produktyPoprzednioOgladane'] !== null && count((array)$_SESSION['produktyPoprzednioOgladane']) > 0 ) {
                //  
                $TablicaId = implode(',', array_reverse((array)$_SESSION['produktyPoprzednioOgladane']));
                //
            } 
            //
            $sqlZap = "SELECT DISTINCT p.products_id 
                         FROM products p
                   INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                   INNER JOIN categories c ON c.categories_id = ptc.categories_id
                        WHERE c.categories_status = '1' AND p.products_status = '1' and p.listing_status = '0' and p.products_id in (" . $TablicaId . ") " . $GLOBALS['warunekProduktu'] . $sortowanie;             
            //
            unset($TablicaId);
            //
            break; 
    }      

    $Tablica = array();
    $WybraneProdukty = array();
    
    if ( $modul != 'warunki' && $modul != 'poprzednio_ogladane' ) {
    
        $PlikCache = $plik . '_' . ucfirst($sort);
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj($PlikCache, $cache, true);

    } else {
      
        $WynikCache = false;
        
    }
    
    if ( !$WynikCache && !is_array($WynikCache) ) {
         //
         $sql_random = $GLOBALS['db']->open_query( $sqlZap );
         while ($info_random = $sql_random->fetch_assoc()) {
                //
                $Tablica[] = $info_random['products_id'];
                //
         }
         //
         if ( $modul != 'warunki' && $modul != 'poprzednio_ogladane' ) {
              //
              $GLOBALS['cache']->zapisz($PlikCache, $Tablica, $cache, true);
              //
         }         
         //
         $GLOBALS['db']->close_query($sql_random); 
         //
    } else {
         //
         $Tablica = $WynikCache;
         //
    }  

    // wybranie tylko unikalnych rekordow w tablicy
    $Tablica = array_unique($Tablica);
    
    if ( $sort == 'losowo' ) {
         //
         shuffle($Tablica);
         //
    }
    
    $WybraneProdukty = array();
    
    for ($x = 0; $x < $limit; $x++ ) {
         //
         if ( isset($Tablica[$x]) ) {
              //
              $WybraneProdukty[] = $Tablica[$x];
              //
         }
         //
    }

    unset($Tablica, $PlikCache, $plik, $cache, $sqlZap);
    
    return $WybraneProdukty;
  
  }  

  // funkcja zwraca tablice id produktow modulowych z recenzjami
  public static function ProduktyModuloweRecenzje( $limit = 9999 ) {

    $Tablica = array();
    $WybraneProdukty = array();

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('ProduktyZawierajaceRecenzje_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_RECENZJE, true);

    if ( !$WynikCache && !is_array($WynikCache) ) {
      
         $sql_random = $GLOBALS['db']->open_query( Produkty::SqlProduktyZawierajaceRecenzje() );
         
         if ( (int)$GLOBALS['db']->ile_rekordow($sql_random) > 0 ) {
           
             while ($info_random = $sql_random->fetch_assoc()) {
                $Tablica[] = $info_random['products_id'];
             }
             
         }
         //
         $GLOBALS['cache']->zapisz('ProduktyZawierajaceRecenzje_' . $_SESSION['domyslnyJezyk']['kod'], $Tablica, CACHE_RECENZJE, true);
         //
         $GLOBALS['db']->close_query($sql_random); 
         
    } else {
      
         $Tablica = $WynikCache;
         
    }  
    
    //wybranie tylko unikalnych rekordow w tablicy;
    $Tablica = array_unique($Tablica);
    
    if (count($Tablica) > 0) {
        $WybraneProdukty = explode(',', (string)Funkcje::wylosujElementyTablicyJakoTekst($Tablica, $limit));
    }
    
    unset($Tablica, $plik, $cache, $sqlZap);
    
    return $WybraneProdukty;
  
  }  
  
  // funkcja zwraca tablice id produktow modulowych - bestsellery
  public static function ProduktyModuloweBestsellery( $limit = 9999 ) {

    $Tablica = array();
    $WybraneProdukty = array();

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Bestsellery', 30, true);

    if ( !$WynikCache && !is_array($WynikCache) ) {

         $sql_random = $GLOBALS['db']->open_query( Produkty::SqlBestsellery( 200 ) );
         
         if ( (int)$GLOBALS['db']->ile_rekordow($sql_random) > 0 ) {
           
             while ($info_random = $sql_random->fetch_assoc()) {
                $Tablica[] = $info_random['products_id'];
             }
             
         }
         //
         $GLOBALS['cache']->zapisz('Bestsellery', $Tablica, 30, true);
         //
         $GLOBALS['db']->close_query($sql_random); 
         
    } else {
      
         $Tablica = $WynikCache;
         
    }  
    
    //wybranie tylko unikalnych rekordow w tablicy;
    $Tablica = array_unique($Tablica);
    
    $limt = 0;
    foreach ( $Tablica as $Poz ) {
        if ($limt < $limit) {
            $WybraneProdukty[] = $Poz;
        } else {
            break;
        }
        $limt++;
    }
    
    unset($Tablica, $limt);
    
    return $WybraneProdukty;
  
  }    
  
  // podzial sortowania
  public static function PodzialSortowania($sortowanie = '') {
    
    $tmp = '';
    
    if ( $sortowanie != '' ) {
      
        if ( $sortowanie == 'p.products_ordered desc' ) {
             //
             $sortowanie = 'IloscZamowien desc';
             //
        }
        
        if ( LISTING_PRODUKTY_ZERO_NA_KONCU == 'tak' ) {
        
            $podziel = explode(',', $sortowanie);
            
            $glownySort = explode(' ', $podziel[0]);
            
            $tmp = " ORDER BY 
                      CASE 
                            WHEN (p.products_quantity > 0 or (p.products_control_storage = 0 or p.products_control_storage = 2)) and p.products_buy = 1 THEN 0 
                            ELSE 1 
                        END ASC, 
                        " . $glownySort[0] . " " . ((isset($glownySort[1])) ? $glownySort[1] : '') . ", 
                        p.products_quantity DESC";  

            for ( $r = 1; $r < count($podziel); $r++ ) {
                  //
                  $tmp .= ", " . $podziel[$r];
                  //
            }
            
        } else {
          
           $tmp = " ORDER BY " . $sortowanie;
           
        }
        
    }
    
    return $tmp;  
    
  }

  // funkcja zwraca tablice dodatkowych pol do produktow
  public static function TablicaDodatkowePola() {

    // sprawdzenie czy sa dodatkowe pola do wyszukiwania
    $DodatkowePola = array();

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePolaListing_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 

        $zapytanie = "SELECT products_extra_fields_id, products_extra_fields_name, products_extra_fields_icon FROM products_extra_fields WHERE products_extra_fields_status = '1' AND (languages_id = '0' OR languages_id = '".(int)$_SESSION['domyslnyJezyk']['id']."')";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            while ($info = $sql->fetch_assoc()) {
                $DodatkowePola[$info['products_extra_fields_id']] = array( 'id' => $info['products_extra_fields_id'],
                                                                     'nazwa'   => $info['products_extra_fields_name'],
                                                                     'ikona'   => $info['products_extra_fields_icon'] );

            }
            
            unset($info);
            
        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);
        
        $GLOBALS['cache']->zapisz('DodatkowePolaWyszukiwanie_' . $_SESSION['domyslnyJezyk']['kod'], $DodatkowePola, CACHE_INNE);
        
    } else {
     
       $DodatkowePola = $WynikCache;

    }

    return $DodatkowePola;

  }
}

?>