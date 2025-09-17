<?php

class Aktualnosci {

    // zwraca tablice z aktualnosciami
    public static function TablicaAktualnosci() {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('Aktualnosci_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_AKTUALNOSCI, true);
        //
        if ( !$WynikCache ) {
            // 
            //Data biezaca
            $BiezacaData = date('Y-m-d');

            // dodatkowy warunek dla grup klientow
            $warunekTmp = " and (n.newsdesk_customers_group_id = '0'";
            if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", n.newsdesk_customers_group_id)";
            }
            $warunekTmp .= ") "; 
            //            
            $zapytanie = "SELECT n.newsdesk_id,
                                 n.newsdesk_date_added,
                                 n.newsdesk_author,
                                 n.newsdesk_customers_group_id,
                                 n.newsdesk_image,
                                 n.newsdesk_icon,
                                 n.newsdesk_comments_status,
                                 n.newsdesk_structured_data_status,
                                 n.newsdesk_structured_data_type,
                                 n.newsdesk_structured_data_publisher_name,
                                 n.newsdesk_structured_data_publisher_image,                                 
                                 nd.newsdesk_article_name,
                                 nd.newsdesk_article_short_text,
                                 nd.newsdesk_article_description,
                                 nd.newsdesk_article_viewed,
                                 nd.newsdesk_meta_title_tag,
                                 nd.newsdesk_meta_desc_tag,
                                 nd.newsdesk_meta_keywords_tag,
                                 nd.newsdesk_og_title,
                                 nd.newsdesk_og_description,
                                 nd.newsdesk_og_image,         
                                 nd.newsdesk_link_canonical,
                                 ntc.categories_id,
                                 nc.parent_id,
                                 ncd.categories_name
                            FROM newsdesk n
                       LEFT JOIN newsdesk_description nd ON n.newsdesk_id = nd.newsdesk_id AND nd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'  
                       LEFT JOIN newsdesk_to_categories ntc ON n.newsdesk_id = ntc.newsdesk_id
                       LEFT JOIN newsdesk_categories_description ncd ON ncd.categories_id = ntc.categories_id AND ncd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'                             
                       LEFT JOIN newsdesk_categories nc ON nc.categories_id = ncd.categories_id
                           WHERE n.newsdesk_status = '1'" . $warunekTmp . "
                           AND n.newsdesk_date_added <= '".$BiezacaData."'
                        ORDER BY n.newsdesk_date_added desc, nd.newsdesk_article_name";

            unset($warunekTmp);
                        
            $_sql = $GLOBALS['db']->open_query($zapytanie);
            
            $TablicaArtykulow = array();

            if ((int)$GLOBALS['db']->ile_rekordow($_sql) > 0) {
                //
                $licznik = 0;
                $pozycja = 1;
                //
                while ($_info = $_sql->fetch_assoc()) {
                    //
                    $TablicaArtykulow[$_info['newsdesk_id']] = array('id' => $_info['newsdesk_id'],
                                                                     'parent' => $_info['parent_id'],
                                                                     'foto_artykulu' => $_info['newsdesk_image'],
                                                                     'ikona' => $_info['newsdesk_icon'],
                                                                     'tytul' => $_info['newsdesk_article_name'],
                                                                     'link' => '<a href="' . Seo::link_SEO( $_info['newsdesk_article_name'], $_info['newsdesk_id'], 'aktualnosc' ) . '" title="' . $_info['newsdesk_article_name'] . '">' . $_info['newsdesk_article_name'] . '</a>',
                                                                     'seo' => Seo::link_SEO( $_info['newsdesk_article_name'], $_info['newsdesk_id'], 'aktualnosc' ),
                                                                     'opis_krotki' => '<div class="FormatEdytor">' . $_info['newsdesk_article_short_text'] . '</div>',
                                                                     'opis' => '<div class="FormatEdytor">' . $_info['newsdesk_article_description'] . '</div>',
                                                                     'data' => date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($_info['newsdesk_date_added'])),
                                                                     'autor' => $_info['newsdesk_author'],
                                                                     'id_kategorii' => $_info['categories_id'],
                                                                     'nazwa_kategorii' => $_info['categories_name'],
                                                                     'wyswietlenia' => $_info['newsdesk_article_viewed'],
                                                                     'meta_tytul' => $_info['newsdesk_meta_title_tag'],
                                                                     'meta_tytul_uzupelniony' => (( empty($_info['newsdesk_meta_title_tag']) ) ? false : true),
                                                                     'meta_opis' => $_info['newsdesk_meta_desc_tag'],
                                                                     'meta_opis_uzupelniony' => (( empty($_info['newsdesk_meta_desc_tag']) ) ? false : true),
                                                                     'meta_slowa' => $_info['newsdesk_meta_keywords_tag'],
                                                                     'meta_slowa_uzupelnione' => (( empty($_info['newsdesk_meta_keywords_tag']) ) ? false : true),
                                                                     'og_title' => $_info['newsdesk_og_title'],
                                                                     'og_description' => $_info['newsdesk_og_description'],
                                                                     'og_image' => $_info['newsdesk_og_image'],
                                                                     'link_kanoniczny' => $_info['newsdesk_link_canonical'],
                                                                     'status_komentarzy' => (($_info['newsdesk_comments_status'] == 1) ? 'tak' : 'nie'),
                                                                     'pozycja' => $pozycja,
                                                                     'dane_strukturalne_status' => (($_info['newsdesk_structured_data_status'] == 1) ? 'tak' : 'nie'),
                                                                     'dane_strukturalne_typ' => $_info['newsdesk_structured_data_type'],
                                                                     'dane_strukturalne_wydawca_nazwa' => ((!empty($_info['newsdesk_structured_data_publisher_name'])) ? $_info['newsdesk_structured_data_publisher_name'] : DANE_NAZWA_FIRMY_SKROCONA),
                                                                     'dane_strukturalne_wydawca_foto' => KATALOG_ZDJEC . '/' . ((!empty($_info['newsdesk_structured_data_publisher_image'])) ? $_info['newsdesk_structured_data_publisher_image'] : ((LOGO_FIRMA != '') ? LOGO_FIRMA : 'domyslny.webp')));   
                                                                     
                    $pozycja++;
                    // 
                    // komentarze tylko dla zalogowanych
                    if ( KOMENTARZE_ZALOGOWANI_WIDOCZNOSC == 'tak' ) {
                         //
                         if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && $_info['newsdesk_comments_status'] == 1) {
                             //
                             $TablicaArtykulow[$_info['newsdesk_id']]['status_komentarzy'] = 'tak';
                             //
                         } else {
                             //
                             $TablicaArtykulow[$_info['newsdesk_id']]['status_komentarzy'] = 'nie';
                             //
                         }
                         //
                    }
                    
                    // komentarze do artykulow
                    $TablicaKomentarzy = array();
                    //
                    if ( $TablicaArtykulow[$_info['newsdesk_id']]['status_komentarzy'] == 'tak' ) {
                        //
                        $zapytanieKomentarze = "SELECT newsdesk_comments_id, newsdesk_id, comments_answers, comments, nick, date_added FROM newsdesk_comments WHERE newsdesk_id = '" . $_info['newsdesk_id'] . "' and status = '1' ORDER BY date_added asc";
                        $_sqlKomentarze = $GLOBALS['db']->open_query($zapytanieKomentarze);
                        //
                        if ((int)$GLOBALS['db']->ile_rekordow($_sqlKomentarze) > 0) {
                            //
                            while ($_infc = $_sqlKomentarze->fetch_assoc()) {
                                //
                                $TablicaKomentarzy[] = array('komentarz' => $_infc['comments'], 'odpowiedz' => $_infc['comments_answers'], 'nick' => $_infc['nick'], 'data' => date('d-m-Y G:H:i',FunkcjeWlasnePHP::my_strtotime($_infc['date_added'])));
                                //
                            }
                            //
                        }
                        //
                        $GLOBALS['db']->close_query($_sqlKomentarze); 
                        unset($zapytanieKomentarze);                     
                        //
                    }
                    //
                    $TablicaArtykulow[$_info['newsdesk_id']]['komentarze'] = $TablicaKomentarzy;
                    //                   
                }
                unset($_info);
                //
            }
            
            $GLOBALS['db']->close_query($_sql); 
            unset($zapytanie); 
            //
            $GLOBALS['cache']->zapisz('Aktualnosci_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaArtykulow, CACHE_AKTUALNOSCI, true);
            //
        } else { 
            //
            if (count($WynikCache)) {
                $TablicaArtykulow = $WynikCache;
            }
            //
        }            
        
        return $TablicaArtykulow;
        
    }
    
    public static function strposa($ciag, $frazy = array()) {
        $jest = true;
        foreach ( $frazy as $szuk ) {
            //
            if ( WYSZUKIWANIE_PL_ZNAKI == 'tak' ) {
                 //
                 if ( !preg_match('/' . Funkcje::ZamienPlZnaki(mb_strtolower((string)$szuk, 'UTF-8')) . '/i', mb_strtolower(strip_tags((string)$ciag), 'UTF-8')) ) {
                      $jest = false;
                 } else {
                      $jest = true;
                 }
                 //
            } else {
                 //
                 if ( mb_strpos(mb_strtolower(strip_tags((string)$ciag), 'UTF-8'), $szuk) === false ) {
                      $jest = false;
                 } else {
                      $jest = true;
                 }
                 //
            }
            //
        }
        return $jest;
    }    
    
    // zwraca tablice ze artykulami z konkretnej kategorii - id
    public static function TablicaAktualnosciKategoria( $IdKategorii = 0, $ilosc = 9999, $limit = array(), $szukaj = '' ) {

        $IdKategoriiTablica = array($IdKategorii);
        
        $TablicaArtykulowKategorii = array();
        $TablicaArtykulowKategoriiWynik = array();
        
        // wyszukiwanie podkategorii        
        if ( LISTING_AKTUALNOSCI_WSZYSTKIE_PRODUKTY == 'tak' ) {
             //
             $ListaKategorii = Aktualnosci::TablicaKategorieAktualnosci();
             //
             foreach ( $ListaKategorii as $KategoriaAktualnosci ) {
                   //
                   if ( $KategoriaAktualnosci['parent'] == $IdKategorii ) {    
                        $IdKategoriiTablica[] = $KategoriaAktualnosci['id'];
                   }
                   //
             }                
             //
             unset($ListaKategorii);
             //
        }

        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        if ( count($TablicaArtykulow) > 0 ) {
          
            //
            $szukaj = mb_strtolower((string)$szukaj, 'UTF-8');
          
            // utworzy tablice tylko z artykulami z danej kategorii
            foreach ( $TablicaArtykulow as $Artykul ) {
                //
                if ( in_array($Artykul['id_kategorii'], $IdKategoriiTablica) ) {
                     //
                     if ( $szukaj != '' ) {
                          //
                          if ( Aktualnosci::strposa($Artykul['tytul'], explode(' ', (string)$szukaj)) !== false || Aktualnosci::strposa($Artykul['opis_krotki'], explode(' ', (string)$szukaj)) !== false || Aktualnosci::strposa($Artykul['opis'], explode(' ', (string)$szukaj)) !== false ) {
                               //
                               $TablicaArtykulowKategorii[] = $Artykul;
                               //
                          }
                          //
                     } else {
                          //
                          $TablicaArtykulowKategorii[] = $Artykul;
                          //                        
                     }
                }              
                //
            }
            
            unset($Artykul);
        
            $licznik = 0;

            if ( count($limit) > 0 ) {
                 //
                 $licznikOd = $limit[0];
                 $licznikDo = $limit[0] + $limit[1];
                 //
              } else {
                 //
                 $licznikOd = 0;
                 $licznikDo = 99999;                 
                 //
            }

            foreach ( $TablicaArtykulowKategorii as $Indeks => $Artykul ) {
                //
                // dla stronicowania
                if ( $Indeks >= $licznikOd && $Indeks < $licznikDo && $licznik < $ilosc ) {
                     //
                     $TablicaArtykulowKategoriiWynik[] = $Artykul;
                     //
                     $licznik++;
                     //                         
                }
                //
            }
            
        }
        
        unset($TablicaArtykulow, $TablicaArtykulowKategorii);
        
        return $TablicaArtykulowKategoriiWynik;    

    }
    
    // zwraca tablice ze artykulami z warunkami szukania
    public static function TablicaAktualnosciSzukaj( $szukaj_tytul = '', $szukaj_autor = '', $ilosc = 9999 ) {

        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        $TablicaArtykulowWynik = array();
        
        $a = 0;
        
        if ( count($TablicaArtykulow) > 0 ) {
          
            if ( $szukaj_tytul != '' || $szukaj_autor != '' ) {
          
                $szukaj_tytul = mb_strtolower((string)$szukaj_tytul, 'UTF-8');
                $szukaj_autor = mb_strtolower((string)$szukaj_autor, 'UTF-8');
              
                foreach ( $TablicaArtykulow as $Artykul ) {
                    //
                    if ( strlen((string)$szukaj_tytul) > 1 && strlen((string)$szukaj_autor) > 1 ) {
                         //
                         if ( Aktualnosci::strposa($Artykul['tytul'], explode(' ', (string)$szukaj_tytul)) !== false && Aktualnosci::strposa($Artykul['autor'], explode(' ', (string)$szukaj_autor)) !== false ) {
                              //
                              $TablicaArtykulowWynik[ $Artykul['id'] ] = $Artykul;
                              //
                         }
                         //
                    } else {
                         //
                         if ( strlen((string)$szukaj_tytul) > 1 ) {
                              //
                              if ( Aktualnosci::strposa($Artykul['tytul'], explode(' ', (string)$szukaj_tytul)) !== false ) {
                                   //
                                   $TablicaArtykulowWynik[ $Artykul['id'] ] = $Artykul;
                                   //
                              }
                              //
                         }                         
                         if ( strlen((string)$szukaj_autor) > 1 ) {
                              //
                              if ( Aktualnosci::strposa($Artykul['autor'], explode(' ', (string)$szukaj_autor)) !== false ) {
                                   //
                                   $TablicaArtykulowWynik[ $Artykul['id'] ] = $Artykul;
                                   //
                              }
                              //
                         }
                         //
                    }
                    //
                    $a++;
                    //
                    if ( $a > $ilosc ) {
                         //
                         break;
                         //
                    }
                    //
                }
                
                unset($Artykul);
            
            }
            
        }
        
        unset($TablicaArtykulow);
        
        return $TablicaArtykulowWynik;    

    }
    
    // zwraca tablice z okreslona iloscia artykulow
    public static function TablicaAktualnosciLimit( $ilosc = 9999 ) {

        $TablicaArtykulowIlosc = array();
        
        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        if ( count($TablicaArtykulow) > 0 ) {
        
            $licznik = 0;
            foreach ( $TablicaArtykulow as $Artykul ) {
                //
                if ( $licznik < $ilosc ) {
                    //
                    $TablicaArtykulowIlosc[] = $Artykul;
                    //
                } else {
                    //
                    break;
                    //
                }
                //
                $licznik++;
                //
            }
        
        }
        
        unset($TablicaArtykulow);
        
        return $TablicaArtykulowIlosc;    

    }    
    
    // zwraca tablice z danymi aktualnosci o konkretnym ID
    public static function AktualnoscId( $id ) {
        //
        $WynikArtykul = '';
        
        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        if ( count($TablicaArtykulow) > 0 ) {
        
            foreach ( $TablicaArtykulow as $Artykul ) {
                //
                if ( $Artykul['id'] == $id ) {
                     $WynikArtykul = $Artykul;
                     break;
                }
                //
            }
            
        }
        
        unset($TablicaArtykulow);
        
        return $WynikArtykul;
        
    }   

    // zwraca tablice z kategoriami aktualnosci
    public static function TablicaKategorieAktualnosci( $tylkoAktywne = true ) {
        //
        //Data biezaca
        $BiezacaData = date('Y-m-d');

        $WynikCache = $GLOBALS['cache']->odczytaj('Aktualnosci_Kategorie_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_AKTUALNOSCI);
        //
        if ( !$WynikCache ) {
            //        
            $zapytanie = "SELECT nc.categories_id,
                                 nc.parent_id,
                                 nc.categories_image,
                                 nc.search,
                                 nc.newsdesk_categories_structured_data_status,
                                 nc.newsdesk_categories_structured_data_type,
                                 ncd.categories_name,
                                 ncd.categories_description,
                                 ncd.categories_meta_title_tag,
                                 ncd.categories_meta_desc_tag,
                                 ncd.categories_meta_keywords_tag
                            FROM newsdesk_categories nc
                       LEFT JOIN newsdesk_categories_description ncd ON nc.categories_id = ncd.categories_id AND ncd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'                
                        ORDER BY nc.sort_order, ncd.categories_name";
                        
            $_sql = $GLOBALS['db']->open_query($zapytanie);
            
            $TablicaKategoriiArtykulow = array();

            if ((int)$GLOBALS['db']->ile_rekordow($_sql) > 0) {
                //
                $licznik = 0;
                //
                while ($_info = $_sql->fetch_assoc()) {
                    //
                    // ile pozycji kategorii
                    $ile = 0;
                    //
                    $zapytanieC = "select count(ntc.newsdesk_id) as ile_artykulow FROM newsdesk_to_categories ntc, newsdesk n WHERE ntc.categories_id = " . $_info['categories_id'] . " AND ntc.newsdesk_id = n.newsdesk_id AND n.newsdesk_status = '1'AND n.newsdesk_date_added <= '".$BiezacaData."'";
                    $_sqlC = $GLOBALS['db']->open_query($zapytanieC);
                    //
                    $_infoC = $_sqlC->fetch_assoc();
                    //
                    $ile = $_infoC['ile_artykulow'];
                    //
                    $GLOBALS['db']->close_query($_sqlC); 
                    unset($zapytaniC);                     
                    //
                    $TablicaKategoriiArtykulow[ $_info['categories_id'] ] = array('id' => $_info['categories_id'],
                                                                                  'parent' => $_info['parent_id'],
                                                                                  'foto' => $_info['categories_image'],
                                                                                  'nazwa' => $_info['categories_name'],
                                                                                  'wyszukiwanie' => (($_info['search'] == 1) ? 'tak' : 'nie'),
                                                                                  'link' => '<a href="' . Seo::link_SEO( $_info['categories_name'], $_info['categories_id'], 'kategoria_aktualnosci' ) . '" title="' . $_info['categories_name'] . '">' . $_info['categories_name'] . '</a>',
                                                                                  'seo' => Seo::link_SEO( $_info['categories_name'], $_info['categories_id'], 'kategoria_aktualnosci' ),
                                                                                  'opis' => $_info['categories_description'],
                                                                                  'ile_artykulow' => $ile,
                                                                                  'meta_tytul' => $_info['categories_meta_title_tag'],
                                                                                  'meta_opis' => $_info['categories_meta_desc_tag'],
                                                                                  'meta_slowa' => $_info['categories_meta_keywords_tag'],
                                                                                  'dane_strukturalne_status' => (($_info['newsdesk_categories_structured_data_status'] == 1) ? 'tak' : 'nie'),
                                                                                  'dane_strukturalne_typ' => $_info['newsdesk_categories_structured_data_type'],
                                                                                  'dane_strukturalne_itemscope' => (($_info['newsdesk_categories_structured_data_status'] == 1) ? (($_info['newsdesk_categories_structured_data_type'] == 'artykuł') ? 'itemscope itemtype="https://schema.org/ItemList"' : 'itemscope itemtype="http://schema.org/Blog"') : ''),
                                                                                  'dane_strukturalne_itemprop' => (($_info['newsdesk_categories_structured_data_status'] == 1) ? (($_info['newsdesk_categories_structured_data_type'] == 'artykuł') ? 'itemprop="itemListElement" itemscope itemtype="https://schema.org/Article"' : 'itemprop="blogPosts" itemscope itemtype="http://schema.org/BlogPosting"') : ''));
                    //
                }
                unset($_info);
                //
                /*
                // usunie kategorie w ktorych nie ma artykulow
                if ( $tylkoAktywne == true ) {
                    //
                    $WszystkieArtykuly = Aktualnosci::TablicaAktualnosci();
                    //                    
                    foreach ( $TablicaKategoriiArtykulow as $KategoriaAktualnosci ) {
                    
                        $saArtykuly = false;
                        foreach ( $WszystkieArtykuly as $Artykul ) {
                            //
                            if ( $Artykul['id_kategorii'] == $KategoriaAktualnosci['id'] ) {
                                 $saArtykuly = true;
                                 break;
                            }
                            //
                        }
                        
                        if ( $saArtykuly == false ) {
                             //unset( $TablicaKategoriiArtykulow[ $KategoriaAktualnosci['id'] ] );
                        }
                    
                    }
                    //
                    unset($WszystkieArtykuly);
                    //
                }
                */
                //
            }
            
            $GLOBALS['db']->close_query($_sql); 
            unset($zapytanie); 
            //
            $GLOBALS['cache']->zapisz('Aktualnosci_Kategorie_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaKategoriiArtykulow, CACHE_AKTUALNOSCI);
            //
        } else { 
            //
            if (count($WynikCache)) {
                $TablicaKategoriiArtykulow = $WynikCache;
            }
            //
        }            
        
        return $TablicaKategoriiArtykulow;   
        
    }
    
    // zwraca tablice z danymi aktualnosci o konkretnym ID
    public static function KategoriaAktualnoscId( $id ) {
        //
        $WynikKategoriaArtykul = '';
        
        $TablicaKategoriiArtykulow = Aktualnosci::TablicaKategorieAktualnosci();
        
        if ( count($TablicaKategoriiArtykulow) > 0 ) {
        
            foreach ( $TablicaKategoriiArtykulow as $Kategoria ) {
                //
                if ( $Kategoria['id'] == $id ) {
                     $WynikKategoriaArtykul = $Kategoria;
                     break;
                }
                //
            }
            
        }
        
        unset($TablicaKategoriiArtykulow);
        
        return $WynikKategoriaArtykul;
        
    }       
    
}
?>