<?php

class MetaTagi {

  // funkcja zwraca meta tagi dla wybranej strony
  public static function ZwrocMetaTagi( $link = '' ) {
    global $filtr;
    
    $link = str_replace('/', '', (string)$link);

    // pobieranie wartosci domyslnych
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('MetaTagiDomyslne_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
    
    if ( !$WynikCache ) {  
    
        $Domyslne = array();
    
        $zapytanieDomyslne = "SELECT default_title, 
                                     default_keywords, 
                                     default_description, 
                                     default_index_title, 
                                     default_index_keywords, 
                                     default_index_description,
                                     default_title_tab
                                FROM headertags_default 
                               WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
                               
        $sqlDomyslne = $GLOBALS['db']->open_query($zapytanieDomyslne);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sqlDomyslne) > 0 ) {
        
            $Domyslne = $sqlDomyslne->fetch_assoc();
            
        }
        
        $GLOBALS['db']->close_query($sqlDomyslne);
        unset($zapytanieDomyslne);
        
        $GLOBALS['cache']->zapisz('MetaTagiDomyslne_' . $_SESSION['domyslnyJezyk']['kod'], $Domyslne, CACHE_INNE);   
  
      } else {
      
        $Domyslne = $WynikCache;
        
    }    
    
    unset($WynikCache);
    
    // jezeli jest to strona glowna sklepu
    
    $metaTytul = '';
    $metaSlowa = '';
    $metaOpis = '';
    
    if ( $link == 'strona_glowna' ) {
    
        $metaTytul = ((isset($Domyslne['default_index_title'])) ? $Domyslne['default_index_title'] : '');
        $metaSlowa = ((isset($Domyslne['default_index_keywords'])) ? $Domyslne['default_index_keywords'] : '');
        $metaOpis = ((isset($Domyslne['default_index_description'])) ? $Domyslne['default_index_description'] : '');
        
      } else {
        
        $metaTytul = ((isset($Domyslne['default_title'])) ? $Domyslne['default_title'] : '');
        $metaSlowa = ((isset($Domyslne['default_keywords'])) ? $Domyslne['default_keywords'] : '');
        $metaOpis = ((isset($Domyslne['default_description'])) ? $Domyslne['default_description'] : '');
        
    }
    
    $ZdefiniowaneMeta = false;

    if ( $link != '' ) {
    
        // cache zapytania 
        $WynikCache = $GLOBALS['cache']->odczytaj('MetaTagiPodstrony_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
        
        $Podstrony = array();
        
        if ( !$WynikCache ) {
                
            $Podstrony = array();
            
            $zapytanie = "SELECT page_name, page_title, page_keywords, page_description, append_default, sortorder FROM headertags WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);

            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
               
                while ( $info = $sql->fetch_assoc() ) {
                    //
                    $Podstrony[] = array('nazwa_pliku' => $info['page_name'],
                                         'tytul' => $info['page_title'],
                                         'slowa_kluczowe' => $info['page_keywords'],
                                         'opis' => $info['page_description'],
                                         'domyslne' => $info['append_default'],
                                         'sort' => $info['sortorder']);
                    //
                }
                
                unset($info);
                
            }
            
            $GLOBALS['db']->close_query($sql);  
            unset($zapytanie);
            
            $GLOBALS['cache']->zapisz('MetaTagiPodstrony_' . $_SESSION['domyslnyJezyk']['kod'], $Podstrony, CACHE_INNE);   
            
        } else {
        
            $Podstrony = $WynikCache;
            
        }            

        foreach ( $Podstrony as $Podstrona) {
            //
            if ( $Podstrona['nazwa_pliku'] == $filtr->process($link) ) {
                //
                // jezeli ma dodawac wartosc domyslna
                if ($Podstrona['domyslne'] == 1) {
                    //
                    // czy na poczatku czy na koncu
                    if ($Podstrona['sort'] == 0) {
                        //
                        $metaTytul = ((isset($Domyslne['default_title'])) ? $Domyslne['default_title'] : '') . ' ' . $Podstrona['tytul'];
                        $metaSlowa = ((isset($Domyslne['default_keywords'])) ? $Domyslne['default_keywords'] : '') . ' ' . $Podstrona['slowa_kluczowe'];
                        $metaOpis = ((isset($Domyslne['default_description'])) ? $Domyslne['default_description'] : '') . ' ' . $Podstrona['opis'];
                        //
                      } else {
                        //
                        $metaTytul = $Podstrona['tytul'] . ' ' . ((isset($Domyslne['default_title'])) ? $Domyslne['default_title'] : '');
                        $metaSlowa = $Podstrona['slowa_kluczowe'] . ' ' . ((isset($Domyslne['default_keywords'])) ? $Domyslne['default_keywords'] : '');
                        $metaOpis = $Podstrona['opis'] . ' ' . ((isset($Domyslne['default_description'])) ? $Domyslne['default_description'] : '');
                        //
                    }
                    //
                } else {
                    //
                    $metaTytul = $Podstrona['tytul'];
                    $metaSlowa = $Podstrona['slowa_kluczowe'];
                    $metaOpis = $Podstrona['opis'];            
                    //
                }
                //
                $ZdefiniowaneMeta = true;
                //
            }
            //
        }
        
        unset($Podstrony);

    }

    $tablica = array( 'nazwa_pliku' => (($ZdefiniowaneMeta == true) ? $link : null),
                      'tytul' => $metaTytul,
                      'tytul_zakladka' => $Domyslne['default_title_tab'],
                      'opis' => $metaOpis,
                      'slowa' => $metaSlowa);
    
    unset($metaTytul, $metaSlowa, $metaOpis, $Domyslne);    

    return $tablica;
  }  
  
  public static function MetaTagiProduktPodmien( $Rodzaj, $Produkt, $Meta ) {
    
    if ( $Rodzaj == '' ) {
        $Rodzaj = '';
    }

    // tytul produktu meta
    if ( $Rodzaj == 'tytul' ) {
      
        if ( $Produkt->metaTagi['tytul_uzupelniony'] == false && trim((string)$Meta['tytul']) != '' ) {
            $TrescMeta = $Meta['tytul'];
         } else {
            $TrescMeta = ((empty($Produkt->metaTagi['tytul'])) ? $Meta['tytul'] : $Produkt->metaTagi['tytul']);
        }
         
    }
    
    // slowa kluczowe produktu meta
    if ( $Rodzaj == 'slowa' ) {
      
        if ( $Produkt->metaTagi['slowa_uzupelnione'] == false && trim((string)$Meta['slowa']) != '' ) {
            $TrescMeta = $Meta['slowa'];
         } else {
            $TrescMeta = ((empty($Produkt->metaTagi['slowa'])) ? $Meta['slowa'] : $Produkt->metaTagi['slowa']);
        }
         
    }

    // opis produktu meta
    if ( $Rodzaj == 'opis' ) {
      
        if ( $Produkt->metaTagi['opis_uzupelniony'] == false && trim((string)$Meta['opis']) != '' ) {
            $TrescMeta = $Meta['opis'];
         } else {
            $TrescMeta = ((empty($Produkt->metaTagi['opis'])) ? $Meta['opis'] : $Produkt->metaTagi['opis']);
        }
         
    }    
    
    // podmiana zmiennych
    $TrescMeta = str_replace('{NAZWA_PRODUKTU}', strip_tags((string)$Produkt->info['nazwa']), (string)$TrescMeta);         
    $TrescMeta = str_replace('{DUZE_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);   

    $TrescMeta = str_replace('{NAZWA_PRODUCENTA}', strip_tags((string)$Produkt->info['nazwa_producenta']), (string)$TrescMeta);
    $TrescMeta = str_replace('{DUZE_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);

    //
    $KategorieProduktu = $Produkt->ProduktKategoriaGlowna();
    
    if ( isset($KategorieProduktu['id']) ) {
        //
        $NazwaKategorii = Kategorie::NazwaKategoriiId($KategorieProduktu['id']);
        //
        $TrescMeta = str_replace('{NAZWA_KATEGORII}', strip_tags((string)$NazwaKategorii), (string)$TrescMeta);
        $TrescMeta = str_replace('{DUZE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
        $TrescMeta = str_replace('{MALE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);            
        $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);
        
        // sciezka kategorii
        $KategoriaSciezka = Kategorie::SciezkaKategoriiId($KategorieProduktu['id'], 'nazwy', ' - ');
        $TrescMeta = str_replace('{SCIEZKA_KATEGORII}', strip_tags((string)$KategoriaSciezka), (string)$TrescMeta);
        
        unset($KategoriaSciezka, $NazwaKategorii);
    }
    
    //
    unset($KategorieProduktu);
    //
    $TrescMeta = str_replace('{NR_KATALOGOWY}', strip_tags((string)$Produkt->info['nr_katalogowy']), (string)$TrescMeta);
    $TrescMeta = str_replace('{KOD_PRODUCENTA}', strip_tags((string)$Produkt->info['kod_producenta']), (string)$TrescMeta);
    $TrescMeta = str_replace('{KOD_EAN}', strip_tags((string)$Produkt->info['ean']), (string)$TrescMeta);    
    
    // opis krotki
    $TrescMeta = str_replace('{OPIS_PRODUKTU}', Funkcje::UsunFormatowanie($Produkt->info['opis_krotki']), (string)$TrescMeta);    
  
    return $TrescMeta;
  
  }
  
  public static function MetaTagiListingKategoriePodmien( $Rodzaj, $TrescMeta, $info ) {

    if ( $Rodzaj == '' ) {
        $Rodzaj = '';
    }

    $TrescMeta = str_replace('{NAZWA_KATEGORII}', strip_tags((string)$info['categories_name']), (string)$TrescMeta);
    $TrescMeta = str_replace('{DUZE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$info['categories_name']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$info['categories_name']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);            
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$info['categories_name']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);
    
    // sciezka kategorii
    $KategoriaSciezka = Kategorie::SciezkaKategoriiId($info['categories_id'], 'nazwy', ' - ');
    $TrescMeta = str_replace('{SCIEZKA_KATEGORII}', strip_tags((string)$KategoriaSciezka), (string)$TrescMeta);
    
    unset($KategoriaSciezka);

    // opis krotki
    $TrescMeta = str_replace('{OPIS_KATEGORII}', Funkcje::UsunFormatowanie($info['categories_description']), (string)$TrescMeta);    
  
    return $TrescMeta;
  
  } 
  
  public static function MetaTagiRecenzjaPodmien( $Rodzaj, $Produkt, $TrescMeta, $IdRecenzji ) {
     
    if ( $Rodzaj == '' ) {
        $Rodzaj = '';
    }
    // podmiana zmiennych
    $TrescMeta = str_replace('{NAZWA_PRODUKTU}', strip_tags((string)$Produkt->info['nazwa']), (string)$TrescMeta);         
    $TrescMeta = str_replace('{DUZE_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);   

    $TrescMeta = str_replace('{NAZWA_PRODUCENTA}', strip_tags((string)$Produkt->info['nazwa_producenta']), (string)$TrescMeta);
    $TrescMeta = str_replace('{DUZE_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);

    //
    $KategorieProduktu = $Produkt->ProduktKategoriaGlowna();
    
    if ( isset($KategorieProduktu['id']) ) {
        //
        $NazwaKategorii = Kategorie::NazwaKategoriiId($KategorieProduktu['id']);
        //
        $TrescMeta = str_replace('{NAZWA_KATEGORII}', strip_tags((string)$NazwaKategorii), (string)$TrescMeta);
        $TrescMeta = str_replace('{DUZE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
        $TrescMeta = str_replace('{MALE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);            
        $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);
        
        // sciezka kategorii
        $KategoriaSciezka = Kategorie::SciezkaKategoriiId($KategorieProduktu['id'], 'nazwy', ' - ');
        $TrescMeta = str_replace('{SCIEZKA_KATEGORII}', strip_tags((string)$KategoriaSciezka), (string)$TrescMeta);
        
        unset($KategoriaSciezka, $NazwaKategorii);
    }
    
    //
    unset($KategorieProduktu);
    //
    $TrescMeta = str_replace('{NR_KATALOGOWY}', strip_tags((string)$Produkt->info['nr_katalogowy']), (string)$TrescMeta);
    $TrescMeta = str_replace('{KOD_PRODUCENTA}', strip_tags((string)$Produkt->info['kod_producenta']), (string)$TrescMeta);
    $TrescMeta = str_replace('{KOD_EAN}', strip_tags((string)$Produkt->info['ean']), (string)$TrescMeta);    
    
    // opis krotki
    $TrescMeta = str_replace('{OPIS_PRODUKTU}', Funkcje::UsunFormatowanie($Produkt->info['opis_krotki']), (string)$TrescMeta);    
    
    // dane recenzji
    $TrescMeta = str_replace('{AUTOR_RECENZJI}', strip_tags((string)$Produkt->recenzje[$IdRecenzji]['recenzja_oceniajacy']), (string)$TrescMeta);    
    $TrescMeta = str_replace('{DATA_RECENZJI}', strip_tags((string)$Produkt->recenzje[$IdRecenzji]['recenzja_data_dodania']), (string)$TrescMeta);  
    $TrescMeta = str_replace('{TRESC_RECENZJI}', Funkcje::UsunFormatowanie($Produkt->recenzje[$IdRecenzji]['recenzja_tekst']), (string)$TrescMeta); 
    
    return $TrescMeta;
  
  }  
  
  public static function MetaTagiRecenzjeNapiszPodmien( $Rodzaj, $Produkt, $TrescMeta ) {
    
    if ( $Rodzaj == '' ) {
        $Rodzaj = '';
    }

    // podmiana zmiennych
    $TrescMeta = str_replace('{NAZWA_PRODUKTU}', strip_tags((string)$Produkt->info['nazwa']), (string)$TrescMeta);         
    $TrescMeta = str_replace('{DUZE_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_PRODUKTU}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);   

    $TrescMeta = str_replace('{NAZWA_PRODUCENTA}', strip_tags((string)$Produkt->info['nazwa_producenta']), (string)$TrescMeta);
    $TrescMeta = str_replace('{DUZE_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_PRODUCENTA}', mb_convert_case(strip_tags((string)$Produkt->info['nazwa_producenta']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);

    //
    $KategorieProduktu = $Produkt->ProduktKategoriaGlowna();
    
    if ( isset($KategorieProduktu['id']) ) {
        //
        $NazwaKategorii = Kategorie::NazwaKategoriiId($KategorieProduktu['id']);
        //
        $TrescMeta = str_replace('{NAZWA_KATEGORII}', strip_tags((string)$NazwaKategorii), (string)$TrescMeta);
        $TrescMeta = str_replace('{DUZE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
        $TrescMeta = str_replace('{MALE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);            
        $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$NazwaKategorii), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);
        
        // sciezka kategorii
        $KategoriaSciezka = Kategorie::SciezkaKategoriiId($KategorieProduktu['id'], 'nazwy', ' - ');
        $TrescMeta = str_replace('{SCIEZKA_KATEGORII}', strip_tags((string)$KategoriaSciezka), (string)$TrescMeta);
        
        unset($KategoriaSciezka, $NazwaKategorii);
    }
    
    //
    unset($KategorieProduktu);
    //
    $TrescMeta = str_replace('{NR_KATALOGOWY}', strip_tags((string)$Produkt->info['nr_katalogowy']), (string)$TrescMeta);
    $TrescMeta = str_replace('{KOD_PRODUCENTA}', strip_tags((string)$Produkt->info['kod_producenta']), (string)$TrescMeta);
    $TrescMeta = str_replace('{KOD_EAN}', strip_tags((string)$Produkt->info['ean']), (string)$TrescMeta);    
    
    // opis krotki
    $TrescMeta = str_replace('{OPIS_PRODUKTU}', Funkcje::UsunFormatowanie($Produkt->info['opis_krotki']), (string)$TrescMeta);    
  
    return $TrescMeta;
  
  }  
  
  public static function MetaTagiArtykulPodmien( $Rodzaj, $Artykul, $Meta ) {
    
    if ( $Rodzaj == '' ) {
        $Rodzaj = '';
    }

    // tytul produktu meta
    if ( $Rodzaj == 'tytul' ) {
      
        if ( $Artykul['meta_tytul_uzupelniony'] == false && trim((string)$Meta['tytul']) != '' ) {
            $TrescMeta = $Meta['tytul'];
         } else {
            $TrescMeta = ((empty($Artykul['meta_tytul'])) ? $Meta['tytul'] : $Artykul['meta_tytul']);
        }
         
    }
    
    // slowa kluczowe produktu meta
    if ( $Rodzaj == 'slowa' ) {
      
        if ( $Artykul['meta_slowa_uzupelnione'] == false && trim((string)$Meta['slowa']) != '' ) {
            $TrescMeta = $Meta['slowa'];
         } else {
            $TrescMeta = ((empty($Artykul['meta_slowa'])) ? $Meta['slowa'] : $Artykul['meta_slowa']);
        }
         
    }

    // opis produktu meta
    if ( $Rodzaj == 'opis' ) {
      
        if ( $Artykul['meta_opis_uzupelniony'] == false && trim((string)$Meta['opis']) != '' ) {
            $TrescMeta = $Meta['opis'];
         } else {
            $TrescMeta = ((empty($Artykul['meta_opis'])) ? $Meta['opis'] : $Artykul['meta_opis']);
        }
         
    }    
    
    // podmiana zmiennych
    $TrescMeta = str_replace('{TYTUL_ARTYKULU}', strip_tags((string)$Artykul['tytul']), (string)$TrescMeta);         
    $TrescMeta = str_replace('{DUZE_TYTUL_ARTYKULU}', mb_convert_case(strip_tags((string)$Artykul['tytul']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_TYTUL_ARTYKULU}', mb_convert_case(strip_tags((string)$Artykul['tytul']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{Z_DUZEJ_TYTUL_ARTYKULU}', mb_convert_case(strip_tags((string)$Artykul['tytul']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);   
    
    $TrescMeta = str_replace('{TRESC_ARTYKULU}', Funkcje::UsunFormatowanie($Artykul['opis_krotki']), (string)$TrescMeta); 

    $TrescMeta = str_replace('{NAZWA_KATEGORII}', strip_tags((string)$Artykul['nazwa_kategorii']), (string)$TrescMeta);
    $TrescMeta = str_replace('{DUZE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$Artykul['nazwa_kategorii']), MB_CASE_UPPER, "UTF-8"), (string)$TrescMeta);
    $TrescMeta = str_replace('{MALE_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$Artykul['nazwa_kategorii']), MB_CASE_LOWER, "UTF-8"), (string)$TrescMeta);            
    $TrescMeta = str_replace('{Z_DUZEJ_NAZWA_KATEGORII}', mb_convert_case(strip_tags((string)$Artykul['nazwa_kategorii']), MB_CASE_TITLE, "UTF-8"), (string)$TrescMeta);

    return $TrescMeta;
  
  }  
  
  // funkcja zwraca tagi open graph dla strony glownej
  public static function ZwrocTagiOpenGraph() {
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('OpenGraphDomyslne_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
    
    if ( !$WynikCache ) {  
    
        $TagiOpenGraph = array();
    
        $zapytanie = "SELECT og_title, 
                             og_site_name, 
                             og_description, 
                             og_image
                        FROM headertags_default 
                       WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
                               
        $sqlOpenGraph = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sqlOpenGraph) > 0 ) {
        
            $TagiOpenGraph = $sqlOpenGraph->fetch_assoc();
            
        }
        
        $GLOBALS['db']->close_query($sqlOpenGraph);
        unset($zapytanie);
        
        $GLOBALS['cache']->zapisz('OpenGraphDomyslne_' . $_SESSION['domyslnyJezyk']['kod'], $TagiOpenGraph, CACHE_INNE);   
  
      } else {
      
        $TagiOpenGraph = $WynikCache;
        
    }    
    
    unset($WynikCache);
    
    $tablica = array();
    
    if ( isset($TagiOpenGraph['og_title']) && isset($TagiOpenGraph['og_site_name']) && isset($TagiOpenGraph['og_description']) && isset($TagiOpenGraph['og_image']) ) {
    
        if ( trim((string)$TagiOpenGraph['og_title']) != '' && trim((string)$TagiOpenGraph['og_site_name']) != '' && trim((string)$TagiOpenGraph['og_description']) != '' && trim((string)$TagiOpenGraph['og_image']) != '' ) {
        
            $tablica = array( 'og_title' => $TagiOpenGraph['og_title'],
                              'og_site_name' => $TagiOpenGraph['og_site_name'],
                              'og_description' => $TagiOpenGraph['og_description'],
                              'og_image' => ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $TagiOpenGraph['og_image'] );
                              
        }
        
    }

    return $tablica;
    
  }    

} 

?>