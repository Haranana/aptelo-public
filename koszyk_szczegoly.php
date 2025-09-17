<?php

  // plik
  $WywolanyPlik = 'koszyk_szczegoly';

  include('start.php');

  if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_GET['id_poz'])) {
  
    $blad = false;
    if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
    
        $zapytanie = "SELECT basket_id, customers_id FROM basket_save WHERE basket_id = '".(int)$_GET['id_poz']."'";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
          
            $info = $sql->fetch_assoc();
            if ( (int)$info['customers_id'] == (int)$_SESSION['customer_id'] ) {
                $blad = false;
            } else {
                $blad = true;
            }
            unset($info);
            
        } else {
          
            $blad = true;
            
        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);

    } else {
      
        $blad = true;
        
    }
    
    if ( KOSZYK_ZAPIS == 'nie' ) {
         $blad = true;
    }

    if ( $blad ) {
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
    }

    unset($blad);  

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI_PANEL', 'PRZYCISKI') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZAPISANY_KOSZYK']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    $TablicaProduktow = array();

    $DodanoWszystkie = true;
    
    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();      

    $SumaProduktowNetto = 0;
    $SumaProduktowBrutto = 0;
    
    $zapytanie = "SELECT bsp.basket_products_id,
                         bsp.basket_id,
                         bsp.products_id,
                         bsp.basket_quantity,
                         bsp.products_comments,
                         bsp.products_text_fields,
                         bsp.basket_quantity,                                
                         p.products_jm_id,
                         pj.products_jm_quantity_type
                    FROM basket_save_products bsp
               LEFT JOIN products p ON p.products_id = bsp.products_id 
               LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                   WHERE bsp.basket_id = '" . (int)$_GET['id_poz'] . "'";    
    
    $sql = $GLOBALS['db']->open_query($zapytanie); 

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        while ($info = $sql->fetch_assoc()) {
            //
            $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $info['products_id'] ), 40, 40 );
            //
            if ($Produkt->CzyJestProdukt == true) {
              
                $SprMagazyn = $GLOBALS['koszykKlienta']->SprawdzIloscProduktuMagazynZapisanyKoszyk( $info['products_id'], $info['basket_quantity'], (int)$_GET['id_poz'] );
                
                if ( $SprMagazyn == false ) {
                  
                    $CechaProduktu = '';
                    
                    if ( strpos((string)$info['products_id'], "x") > -1 ) {
                      
                        $TabCechy = Funkcje::CechyProduktuPoId( $info['products_id'] );
                    
                        foreach ( $TabCechy as $cecha ) {
                          
                            if ( isset($cecha['nazwa_cechy']) && isset($cecha['wartosc_cechy']) ) {
                              
                                 $CechaProduktu .= '<span class="Cecha">' . $cecha['nazwa_cechy'] . ':  <b>' . $cecha['wartosc_cechy'] . '</b></span>';
                                 
                            }
                            
                        }
                        
                        unset($TabCechy);
                        
                    }
                    
                    $KomentarzProduktu = '';
                    
                    if ( $info['products_comments'] != '' ) $KomentarzProduktu .= '<span class="Cecha">{__TLUMACZ:KOMENTARZ_PRODUKTU} <b>' . $info['products_comments'] . '</b></span>';
                    
                    $PolaTekstowe = '';
                    if (!empty($info['products_text_fields'])) {
                      //
                      $PoleTxt = Funkcje::serialCiag($info['products_text_fields']);
                      
                      if ( count($PoleTxt) > 0 ) {
                        
                          foreach ( $PoleTxt as $WartoscTxt ) {
                              // jezeli pole to plik
                              if ( $WartoscTxt['typ'] == 'plik' ) {
                                  $PolaTekstowe .= '<span class="Cecha">' . $WartoscTxt['nazwa'] . ': <a href="inne/wgranie.php?src=' . base64_encode(str_replace('.', ';', (string)$WartoscTxt['tekst'])) . '"><b>' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</b></a></span>';
                                } else {
                                  $PolaTekstowe .= '<span class="Cecha">' . $WartoscTxt['nazwa'] . ': <b>' . $WartoscTxt['tekst'] . '</b></span>';
                              }                                          
                          }
                          
                      }
                      
                      unset($PoleTxt);
                      //
                    }              
                    
                    // ustala czy jm jest w wartosciach calkowitych
                    if ( ( $info['products_jm_quantity_type'] == 1 && (int)$info['products_jm_id'] != 0 ) || ( ( $info['products_jm_id'] == '' || $info['products_jm_id'] == '0' ) && ( $GLOBALS['jednostkiMiary'][0]['typ'] == 1 ) ) ) {
                        //
                        // sprawdzi czy wartosc ilosci nie jest ulamkowa
                        if ( (int)$info['basket_quantity'] == $info['basket_quantity'] ) {
                             $info['basket_quantity'] = (int)$info['basket_quantity'];
                        }
                        //
                    }                  
                    
                    // obliczanie ceny
                    $Produkt->ProduktKupowanie();
                    
                    $WartoscCechBrutto = 0;
                    $WartoscCechNetto = 0;                
                    
                    // przeliczy cechy tylko jezeli produkt nie jest za PUNKTY
                    if ( $Produkt->info['tylko_za_punkty'] == 'nie' ) {
                    
                        // jezeli produkt ma cechy oraz cechy wplywaja na wartosc produktu to musi ustalic ceny cech
                        if ( strpos((string)$info['products_id'], "x") > -1 && $Produkt->info['typ_cech'] == 'cechy' ) {
                            //
                            $DodatkoweParametryCechy = $Produkt->ProduktWartoscCechy( $info['products_id'] );
                            //                        
                            $WartoscCechNetto = $DodatkoweParametryCechy['netto'];
                            $WartoscCechBrutto = $DodatkoweParametryCechy['brutto'];
                            //
                            unset($DodatkoweParametryCechy);
                            //
                            // lub jezeli sa stale ceny dla kombinacji cech
                        } else if ( strpos((string)$info['products_id'], "x") > -1 && $Produkt->info['typ_cech'] == 'ceny' ) {
                            //
                            $DodatkoweCenyCech = $Produkt->ProduktWartoscCechyCeny( $info['products_id'] );
                            //
                            $Produkt->info['cena_netto_bez_formatowania'] = $DodatkoweCenyCech['netto'];
                            $Produkt->info['cena_brutto_bez_formatowania'] = $DodatkoweCenyCech['brutto'];
                            //
                            unset($DodatkoweCenyCech);
                            //
                        }
                        
                        // znizki zalezne od ilosci
                        $StosujZnizki = true;
                        $Znizka = 1;
                        
                        // jezeli nie ma sumowania rabatow
                        if ( ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'nie' && $Produkt->info['rabat_produktu'] != 0 ) {
                            $StosujZnizki = false;
                        }

                        // jezeli znizki zalezne od ilosci produktow w koszyku sa wlaczone dla promocji lub produkt nie jest w promocji
                        if ( ZNIZKI_OD_ILOSCI_PROMOCJE == 'nie' && $Produkt->ikonki['promocja'] == '1' && $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                            $StosujZnizki = false;                
                        }

                        if ( $StosujZnizki == true ) {
                                        
                            $IloscSztDoZnizek = 0;
                            
                            // jezeli produkty ze cechami maja byc traktowane jako osobne produkty
                            if ( ZNIZKI_OD_ILOSCI_PRODUKT_CECHY == 'nie' ) {
                            
                                // ---------------------------------------------------------------------------
                                // musi poszukac ile jest produktow z roznymi cechami i zsumowac produkty
                                $zapytanieZnizki = "SELECT DISTINCT products_id, basket_quantity FROM basket_save_products WHERE basket_id = '" . (int)$_GET['id_poz'] . "'";
                                $sqlZnizki = $GLOBALS['db']->open_query($zapytanieZnizki);
                                
                                if ((int)$GLOBALS['db']->ile_rekordow($sqlZnizki) > 0) {
                                
                                    while ($infoZnizki = $sqlZnizki->fetch_assoc()) {
                                        //
                                        if (Funkcje::SamoIdProduktuBezCech($infoZnizki['products_id']) == Funkcje::SamoIdProduktuBezCech($info['products_id'])) {
                                            $IloscSztDoZnizek += $infoZnizki['basket_quantity'];
                                        }
                                        //
                                    }
                                    
                                    unset($infoZnizki);
                                    
                                }
                                
                                $GLOBALS['db']->close_query($sqlZnizki);
                                unset($zapytanieZnizki);                            
                                // ---------------------------------------------------------------------------
                                //
                                
                              } else {
                              
                                $IloscSztDoZnizek = $info['basket_quantity'];
                                
                            }

                            if ($Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek ) > 0) {
                                // jezeli jest procent to obliczy wskaznik dzielenia - jezeli cena to pobierze cene
                                if ( $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                                     $Znizka = 1 - ($Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek ) / 100);
                                  } else {
                                     $Znizka = $Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek );
                                     if ( $Znizka <= 0 ) {
                                          $Znizka = 1;
                                     }
                                }
                            }
                            //
                            unset($IloscSztDoZnizek);
                            //
                            
                        }

                        // jezeli nie ma znizki
                        if ($Znizka == 1) {
                            //
                            $Produkt->info['cena_brutto_bez_formatowania'] += $WartoscCechBrutto;
                            $Produkt->info['cena_netto_bez_formatowania'] += $WartoscCechNetto;
                            //
                        } else {
                            //
                            if ( $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                                 //
                                 $Produkt->info['cena_brutto_bez_formatowania'] = round((($Produkt->info['cena_brutto_bez_formatowania'] + $WartoscCechBrutto) * $Znizka), CENY_MIEJSCA_PO_PRZECINKU );                                            
                                 $Produkt->info['cena_netto_bez_formatowania'] = round((($Produkt->info['cena_netto_bez_formatowania'] + $WartoscCechNetto) * $Znizka), CENY_MIEJSCA_PO_PRZECINKU );               
                                 //
                            }
                            if ( $Produkt->znizkiZalezneOdIlosciTyp == 'cena' ) {
                                 //
                                 $Produkt->info['cena_brutto_bez_formatowania'] = round(($Znizka + $WartoscCechBrutto), CENY_MIEJSCA_PO_PRZECINKU );             
                                 $Produkt->info['cena_netto_bez_formatowania'] = round(($Znizka + $WartoscCechNetto), CENY_MIEJSCA_PO_PRZECINKU );             
                                 //
                            }                            
                            //
                        }
                        
                        $CenaKoncowa = $GLOBALS['waluty']->PokazCene( $Produkt->info['cena_brutto_bez_formatowania'], $Produkt->info['cena_netto_bez_formatowania'] , 0, $_SESSION['domyslnaWaluta']['id']);
                        $WartoscKoncowa = $GLOBALS['waluty']->PokazCene( $Produkt->info['cena_brutto_bez_formatowania'] * $info['basket_quantity'], $Produkt->info['cena_netto_bez_formatowania'] * $info['basket_quantity'], 0, $_SESSION['domyslnaWaluta']['id']);
                        //                             
                        unset($Znizka, $StosujZnizki);

                    } else {

                        $CenaKoncowa = $GLOBALS['waluty']->PokazCenePunkty( (int)$Produkt->info['cena_w_punktach'], $Produkt->info['cena_brutto_bez_formatowania'] );
                        $WartoscKoncowa = $GLOBALS['waluty']->PokazCenePunkty( (int)$Produkt->info['cena_w_punktach'] * $info['basket_quantity'], $Produkt->info['cena_brutto_bez_formatowania'] * $info['basket_quantity'] );

                    }
                    
                    if ( (float)$Produkt->info['cena_brutto_bez_formatowania'] > 0 ) {
            
                        $TablicaProduktow[] = array( 'id' => $info['basket_products_id'],
                                                     'id_produktu' => $info['products_id'],
                                                     'zdjecie_produktu' => $Produkt->fotoGlowne['zdjecie_link'],
                                                     'nazwa' => $Produkt->info['nazwa'],
                                                     'link' => $Produkt->info['link'],
                                                     'cechy' => $CechaProduktu,
                                                     'pola_txt' => $PolaTekstowe,
                                                     'komentarz' => $KomentarzProduktu,
                                                     'ilosc' => $info['basket_quantity'],
                                                     'cena' => $CenaKoncowa,
                                                     'wartosc' => $WartoscKoncowa );
                                                 
                    } else {
                      
                        $GLOBALS['db']->delete_query('basket_save_products', 'products_id = "' . $info['products_id'] . '" and basket_id = "' . $info['basket_id'] . '"');
                      
                    }
                    
                    unset($CenaKoncowa, $WartoscKoncowa, $WartoscCechBrutto, $WartoscCechNetto);
                    
                    $SumaProduktowNetto += $Produkt->info['cena_netto_bez_formatowania'] * $info['basket_quantity'];                
                    $SumaProduktowBrutto += $Produkt->info['cena_brutto_bez_formatowania'] * $info['basket_quantity'];
                    
                }
                
                if ( $SprMagazyn == true ) { 
                     $DodanoWszystkie = false;
                }
                
                unset($SprMagazyn);

            } else {
              
                $DodanoWszystkie = false;
             
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
    
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $TablicaProduktow);
    
    // nazwa koszyka
    $zapytanie = "SELECT basket_id, basket_name FROM basket_save WHERE basket_id = '".(int)$_GET['id_poz']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        $info = $sql->fetch_assoc();
        
        $srodek->dodaj('__NAZWA_KOSZYKA', $info['basket_name']);
        
        unset($info);
        
    } else {
      
        $srodek->dodaj('__NAZWA_KOSZYKA', '');
      
    }
    
    $srodek->dodaj('__SUMA_KOSZYKA', $GLOBALS['waluty']->PokazCene($SumaProduktowBrutto, $SumaProduktowNetto, 0, $_SESSION['domyslnaWaluta']['id']) );
    
    // informacja o produktach
    if ($DodanoWszystkie == true) {
        //
        $srodek->dodaj('__CSS_INFO_ZAPISANY_KOSZYK', 'display:none');
        //
      } else {
        //
        $srodek->dodaj('__CSS_INFO_ZAPISANY_KOSZYK', '');
        //
    }    
    
    unset($SumaProduktow);
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);     

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $TablicaProduktow);

    include('koniec.php');

  } else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

  }
?>