<?php

// plik
$WywolanyPlik = 'listing';

include('start.php');

if (!isset($_GET['idkat']) && !isset($_GET['idproducent'])) {
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
}

// jezeli jest wywolana kategoria - szukanie danych kategorii
if (isset($_GET['idkat'])) {
    //
    $TabCPath = Kategorie::WyczyscPath($_GET['idkat']);
    $IdWyswietlanejKategorii = $TabCPath[ count($TabCPath) - 1 ];
    //
    // jezeli nie ma danej kategorii w globalnej tablicy
    if ( !isset($GLOBALS['tablicaKategorii'][$IdWyswietlanejKategorii]) && LISTING_PUSTE_KATEGORIE == 'nie' ) {
        //
        $id = 0;
        //
    } else {    
        //
        if (isset($GLOBALS['tablicaKategorii'][$IdWyswietlanejKategorii]) ) {
            //
            // szukanie meta tagow do kategorii
            $zapytanie = "SELECT c.categories_id,
                                 c.categories_status,
                                 c.categories_image,
                                 c.categories_filters,
                                 c.parent_id,
                                 c.categories_banner_image,
                                 c.categories_banner_color,
                                 c.categories_banner_font_size,
                                 c.categories_banner_font_align,
                                 c.categories_banner_image_status,
                                 cd.categories_name,
                                 cd.categories_description,
                                 cd.categories_description_bottom,
                                 cd.categories_meta_title_tag,
                                 cd.categories_meta_desc_tag,
                                 cd.categories_meta_keywords_tag,
                                 cd.categories_seo_url,
                                 cd.categories_link_canonical
                            FROM categories c
                      INNER JOIN categories_description cd 
                              ON c.categories_id = cd.categories_id
                           WHERE cd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                             AND c.categories_id = '" . (int)$IdWyswietlanejKategorii . "'";

            $sql = $GLOBALS['db']->open_query($zapytanie);                         
            //
            $id = 0;
            //
            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {  
                 //
                 $info = $sql->fetch_assoc();
                 //
                 $id = (int)$info['categories_id'];
                 //
                 // sprawdzenie linku SEO z linkiem w przegladarce
                 //
                 $SciezkaKategorii = $info['categories_id'];
                 if ( $info['parent_id'] > 0 && SEO_KATEGORIE == 'nie' ) {
                      //
                      $SciezkaKategorii = Kategorie::SciezkaKategoriiId($info['categories_id']);
                      //
                 }
                 //    
                 Seo::link_Spr(Seo::link_SEO(((!empty($info['categories_seo_url'])) ? $info['categories_seo_url'] : $info['categories_name']), $SciezkaKategorii, 'kategoria'));
                 //
                 $LinkKanonicznyKategoria = '';
                 if ( $info['categories_link_canonical'] != '' ) {
                      //
                      $LinkKanonicznyKategoria = $info['categories_link_canonical'];
                      //
                 }
                 //
            }
            //
            unset($SciezkaKategorii);
            //
        } else {
            //
            $id = 0;
            //
        }
        //
    }
}
    
// jezeli jest wywolany producent - szukanie danych producenta
if (isset($_GET['idproducent'])) {
    //
    // szukanie meta tagow do producenta
    $zapytanie = "SELECT *
                    FROM manufacturers m, manufacturers_info mi
                   WHERE m.manufacturers_id = mi.manufacturers_id AND 
                         mi.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' AND
                         m.manufacturers_id = '" . (int)$_GET['idproducent'] . "'";
                         
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    //
    $id = 0;
    //
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) { 
         //
         $info = $sql->fetch_assoc();
         $id = (int)$info['manufacturers_id'];
         //
         // sprawdzenie linku SEO z linkiem w przegladarce
         if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
             //        
             Seo::link_Spr(Seo::link_SEO($info['manufacturers_name'], $info['manufacturers_id'], 'producent'));
             //
         } 
         //    
    }
    //
}
    
if ( $id == 0 ) {
    //
    header('HTTP/1.1 404 Not Found');
    //
    // plik
    $WywolanyPlik = 'brak_strony';

    // ponowne wywolanie modulow - czy sa jakies do wyswietlania na stronie braku-strony
 
    $tpl->dodaj('__MODULY_SRODKOWE_GORA', $Wyglad->SrodekSklepu( 'gora', array(1,3,4)));
    $tpl->dodaj('__MODULY_SRODKOWE_DOL', $Wyglad->SrodekSklepu( 'dol', array(1,3,4) ));

    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'gora' ));
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'dol' ));

    $Meta = MetaTagi::ZwrocMetaTagi( 'brak_strony.php' );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // wyglad srodkowy
    $srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 

    $srodek->dodaj('__NAGLOWEK_INFORMACJI', $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA']);

    if (isset($_GET['idkat'])) {
        //
        $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_KATEGORII']);
        $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_KATEGORII']);        
        //
    }
    if (isset($_GET['idproducent'])) {
        //
        $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUCENTA']);
        $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUCENTA']);       
        //
    }
    
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    $tpl->dodaj('__JS_PLIK', '');

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik); 
    //
    
} else {

    // jezeli jest wywolana kategoria - szukanie danych kategorii
    if (isset($_GET['idkat'])) {
        //                        

        $LinkDoPrzenoszenia = Seo::link_SEO(((!empty($info['categories_seo_url'])) ? $info['categories_seo_url'] : $info['categories_name']), implode('_', (array)$TabCPath), 'kategoria');
        
        // *****************************
        // jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
        if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
            $GLOBALS['db']->close_query($sql); 
            unset($info, $WywolanyPlik, $IdWyswietlanejKategorii, $srodek, $zapytanie);
            //
            Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/'));
        }    
        // ***************************** 

        include('listing_gora.php');

        // meta tagi
        $Meta = MetaTagi::ZwrocMetaTagi( 'listing.php' );
        //
        $tpl->dodaj('__META_TYTUL', ((empty($info['categories_meta_title_tag'])) ? MetaTagi::MetaTagiListingKategoriePodmien('tytul', $Meta['tytul'], $info) : $info['categories_meta_title_tag']));
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['categories_meta_keywords_tag'])) ? MetaTagi::MetaTagiListingKategoriePodmien('slowa', $Meta['slowa'], $info) : $info['categories_meta_keywords_tag']));
        $tpl->dodaj('__META_OPIS', ((empty($info['categories_meta_desc_tag'])) ? MetaTagi::MetaTagiListingKategoriePodmien('opis', $Meta['opis'], $info) : $info['categories_meta_desc_tag']));
        unset($Meta); 

        // Breadcrumb dla kategorii produktow
        if ( isset($_GET['idkat']) && $_GET['idkat'] != '' ) {
            //
            $tablica_kategorii = explode('_', (string)$_GET['idkat']); 
            //
            for ( $i = 0, $n = count($tablica_kategorii); $i < $n; $i++ ) {
                if ( isset($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat']) ) {
                    //
                    $SciezkaKategorii = $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat'];
                    if ( $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Parent'] > 0 ) {
                         $SciezkaKategorii = Kategorie::SciezkaKategoriiId($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat']);
                    }
                    //
                    //if ( $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Widocznosc'] == '1' ) {
                         $nawigacja->dodaj($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Nazwa'], Seo::link_SEO($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['NazwaSeo'], $SciezkaKategorii, 'kategoria'));
                    //}
                    //
                    unset($SciezkaKategorii);
                }
            }
            unset($tablica_kategorii);
            //
            $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
            //
        }
        
        // dodatkowy banner kategorii
        if ( $info['categories_banner_image_status'] == 1 && $info['categories_banner_image'] != '' && file_exists(KATALOG_ZDJEC . '/' . $info['categories_banner_image']) && Wyglad::TypSzablonu() == true ) {
             //
             list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $info['categories_banner_image']);
             //
             $CiagBanner = '<div class="GrafikaDuzaKategorie" style="height:' . $wysokosc . 'px;background-image:url(\'' . KATALOG_ZDJEC . '/' . $info['categories_banner_image'] . '\')"><div class="Strona">';
             //
             $CiagBanner .= '<strong style="text-align:' . $info['categories_banner_font_align'] . ';font-size:' . $info['categories_banner_font_size'] . '%' . (($info['categories_banner_color'] != '') ? ';color:#' . $info['categories_banner_color'] : '') . '">' . $info['categories_name'] . '</strong>';
             //
             $CiagBanner .= '</div></div>';
             //
             $tpl->dodaj('__GRAFIKA_PODSTRONY', $CiagBanner);
             //
             unset($CiagBanner, $szerokosc, $wysokosc, $typ, $atrybuty);
             //
        }
        
        //
        $srodek->dodaj('__NAGLOWEK_LISTINGU', $info['categories_name']);
        //
        if ( strpos((string)$info['categories_description'], '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
             //
             $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$info['categories_description']);
             //
             if ( count($PodzielTekst) == 2 ) {
                  $info['categories_description'] = Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . '0-' . $info['categories_id'] . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . '0-' . $info['categories_id'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . '0-' . $info['categories_id'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div>';
             } else {
                  $info['categories_description'] = str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$info['categories_description']) . '<div style="clear:both"></div>';
             }
             //
        } else {
             //
             $info['categories_description'] = $info['categories_description'] . '<div style="clear:both"></div>';
             //
        }
        //
        if ( strpos((string)$info['categories_description_bottom'], '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
             //
             $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$info['categories_description_bottom']);
             //
             if ( count($PodzielTekst) == 2 ) {
                  $info['categories_description_bottom'] = Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . '1-' . $info['categories_id'] . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . '1-' . $info['categories_id'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . '1-' . $info['categories_id'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div>';
             } else {
                  $info['categories_description_bottom'] = str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$info['categories_description_bottom']) . '<div style="clear:both"></div>';
             }
             //
        } else {
             //
             $info['categories_description_bottom'] = $info['categories_description_bottom'] . '<div style="clear:both"></div>';
             //
        }
        //
        $srodek->dodaj('__OPIS_LISTINGU', ((strlen((string)$info['categories_description']) > 40) ? '<div class="FormatEdytor">' . $info['categories_description'] . '</div>' : ''));
        $srodek->dodaj('__OPIS_LISTINGU_DOL', ((strlen((string)$info['categories_description_bottom']) > 40) ? '<div class="FormatEdytor">' . $info['categories_description_bottom'] . '</div>' : ''));
        $srodek->dodaj('__CSS_OPIS_LISTINGU', '');
        //
        $srodek->dodaj('__ZDJECIE_LISTINGU', '');
        $srodek->dodaj('__OPIS_BEZ_ZDJECIA', ' OpisListingGornyBezZdjecia');
        
        if (strlen((string)$info['categories_description']) > 40) {
          
            if ( $info['categories_image'] != '' && file_exists(KATALOG_ZDJEC . '/' . $info['categories_image']) && LISTING_OPIS_ZDJECIE == 'tak' ) {
                 //
                 $srodek->dodaj('__ZDJECIE_LISTINGU', '<div class="ZdjecieListing">' . Funkcje::pokazObrazek($info['categories_image'], $info['categories_name'], SZEROKOSC_ZDJECIA_KATEGORII_PRODUCENTA, WYSOKOSC_ZDJECIA_KATEGORII_PRODUCENTA, array(), '', 'maly', true, false, false) . '</div>');
                 //
                 if ( Wyglad::TypSzablonu() == true ) {
                      //
                      $srodek->dodaj('__OPIS_BEZ_ZDJECIA', '');
                      //
                 }
                 //
            }
            
        } else {
          
            $srodek->dodaj('__CSS_OPIS_LISTINGU', ' style="display:none"');
            
        }
        
        // jezeli jest wylaczone wyswietlanie opisu kategorii na kolejnych stronach
        if ( LISTING_OPIS_PODSTRONY == 'nie' && isset($_GET['s']) && (int)$_GET['s'] > 1 ) {
             $srodek->dodaj('__OPIS_LISTINGU', '');
             $srodek->dodaj('__OPIS_LISTINGU_DOL', '');
             $srodek->dodaj('__ZDJECIE_LISTINGU', '');
             $srodek->dodaj('__OPIS_BEZ_ZDJECIA', '');
             $srodek->dodaj('__CSS_OPIS_LISTINGU', ' style="display:none"');
        }        
        //
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie); 
        
        // podkategorie lista - do wyswietlenia podkategorii
        $PodkatLista = '';
        
        //    
        // podkategorie dla kategorii
        $IdPodkategorii = $IdWyswietlanejKategorii . ',';
        //        
        $TablicaPodkategorii = array();
        foreach(Kategorie::DrzewoKategorii($IdWyswietlanejKategorii) as $IdKategorii => $Tablica) {
            //
            // wyswietli wszystkie produkty z kategorii razem z produktami z podkategorii
            if ( LISTING_PODKATEGORIE_PRODUKTY == 'tak' ) {
                 $IdPodkategorii .= Kategorie::TablicaPodkategorie($Tablica);
            }
            //
            if ( $Tablica['Parent'] == $IdWyswietlanejKategorii ) {
                $TablicaPodkategorii[] = $Tablica;
            }
            //
        }

        $LicznikPodkategorii = 0;

        foreach ( $TablicaPodkategorii as $Tablica ) {
            if ( LISTING_PODKATEGORIE_PRZEWIJANIE == 'tak' && Wyglad::TypSzablonu() == true && count($TablicaPodkategorii) > LISTING_PODKATEGORIE_KOLUMNY ) {
                $PodkatLista .= '<div class="KategoriaOkno"><div class="ElementOknoRamka">';
            } else {
                $PodkatLista .= '<li class="OknoRwd">';
            }
            
            unset($SzerokoscPodkategorii);
            //
            $PodkatLista .= '<h2 class="' . ((LISTING_PODKATEGORIE_ZDJECIA != 'tak') ? 'PodkategoriaBezZdjec' : 'PodkategoriaZdjecia') . '"><a href="' . Seo::link_SEO($Tablica['NazwaSeo'], Kategorie::SciezkaKategoriiId( $Tablica['IdKat'] ), 'kategoria') . '">';
            //
            if (LISTING_PODKATEGORIE_ZDJECIA == 'tak') {
                $PodkatLista .= '<span class="NazwaPodkategoria">' . Funkcje::pokazObrazek($Tablica['Foto'], $Tablica['Nazwa'], SZEROKOSC_MINIATUREK_PODKATEGORII, WYSOKOSC_MINIATUREK_PODKATEGORII, array(), '', 'maly', true, false, false) . '</span><br />';
            }
            //
            $PodkatLista .= '<span class="NazwaPodkategoria' . ((LISTING_PODKATEGORIE_ZDJECIA != 'tak') ? ' NazwaPodkategoriaBezZdjecia' : '') . '">' . $Tablica['Nazwa'];
            //
            if (LISTING_ILOSC_PRODUKTOW == 'tak') {
                $PodkatLista .= '<em>('.$Tablica['WszystkichProduktow'] . ')</em>';
            }            
            //
            $PodkatLista .= '</span></a></h2>';
            
            if ( LISTING_PODKATEGORIE_ZDJECIA == 'tak' && LISTING_PODKATEGORIE_OPIS == 'tak' ) {
                 //
                 if ( LISTING_PODKATEGORIE_OPIS_RODZAJ == 'opis podstawowy kategorii' ) {
                      $PodkatLista .= '<p>' . $Tablica['Opis'] . '</p>';
                 } else {
                      $PodkatLista .= '<p>' . $Tablica['OpisDodatkowy'] . '</p>';
                 }
                 //
            }
            
            if ( LISTING_PODKATEGORIE_PRZEWIJANIE == 'tak' && Wyglad::TypSzablonu() == true && count($TablicaPodkategorii) > LISTING_PODKATEGORIE_KOLUMNY ) {
                $PodkatLista .= '</div></div>';
            } else {
                $PodkatLista .= '</li>';
            }

            $LicznikPodkategorii++;

        }
        //

        $srodek->dodaj('__PODKATEGORIE', '');
        $srodek->dodaj('__CSS_PODKATEGORIE', '');
        $srodek->dodaj('__CSS_PODKATEGORIE_LISTA', 'PodkategorieWlaczone');
        
        if ( LISTING_PODKATEGORIE == 'tak' ) {
            
            // jezeli sa podkategorie
            if ( !empty($PodkatLista) ) {
                //
                if ( LISTING_PODKATEGORIE_PRZEWIJANIE == 'tak' && Wyglad::TypSzablonu() == true && count($TablicaPodkategorii) > LISTING_PODKATEGORIE_KOLUMNY ) {
                    $PodkatLista = '<div class="KategoriaPrzewijana">' . $PodkatLista . '</div>';
                } else {
                    $PodkatLista = '<ul ' . ((LISTING_PODKATEGORIE_ZDJECIA == 'tak') ? ' class="KategoriaZdjecie OknaRwd Kol-' . LISTING_PODKATEGORIE_KOLUMNY . '"' : ' class="KategoriaBezZdjecia OknaRwd Kol-' . LISTING_PODKATEGORIE_KOLUMNY . '"') . '>' . $PodkatLista . '</ul>';
                }
                $srodek->dodaj('__PODKATEGORIE', '<strong>' . $GLOBALS['tlumacz']['LISTING_PODKATEGORIE'] . '</strong>' . $PodkatLista);
                //
                if ( LISTING_PODKATEGORIE_MOBILNE == 'tak' ) {
                     $srodek->dodaj('__CSS_PODKATEGORIE_LISTA', 'PodkategorieWylaczone');
                }
                //
            } else {
                //
                $srodek->dodaj('__CSS_PODKATEGORIE', ' style="display:none"');               
                //
            }
        
        } else {
          
            $srodek->dodaj('__CSS_PODKATEGORIE', ' style="display:none"');
            
        }
        
        unset($TablicaPodkategorii, $SumaPodkategorii);
        unset($PodkatLista);
        
        $IdPodkategorii = substr((string)$IdPodkategorii, 0, -1);
        //    
        $zapytanie_stronicowanie = Produkty::SqlProduktyKategoriiStronicowanie($IdPodkategorii, $WarunkiFiltrowania);                            
        //
        $zapytanie = Produkty::SqlProduktyKategorii($IdPodkategorii, $WarunkiFiltrowania, $Sortowanie);                            
        //
        $sql = $GLOBALS['db']->open_query($zapytanie_stronicowanie);
        //

        // filtr nowosci
        if (POKAZUJ_FILTRY_NOWOSCI == 'tak' && $info['categories_filters'] == '1' ) {
            $srodek->dodaj('__FILTRY_NOWOSCI', Filtry::FiltrNowosciSelect($IdPodkategorii, 'kategoria'));
        } else {
            $srodek->dodaj('__FILTRY_NOWOSCI', '');
        }
        
        // filtr promocji
        if (POKAZUJ_FILTRY_PROMOCJE == 'tak' && $info['categories_filters'] == '1' ) {
            $srodek->dodaj('__FILTRY_PROMOCJE', Filtry::FiltrPromocjeSelect($IdPodkategorii, 'kategoria'));      
        } else {
            $srodek->dodaj('__FILTRY_PROMOCJE', '');
        }
        
        // filtry cech
        if (POKAZUJ_FILTRY_CECH == 'tak' && $info['categories_filters'] == '1' ) {
            $srodek->dodaj('__FILTRY_PO_CECHACH', Filtry::FiltrSelect( Filtry::FiltrCech($IdPodkategorii, 'kategoria'), 'c' ));
        } else {
            $srodek->dodaj('__FILTRY_PO_CECHACH', '');
        }
        
        // filtry dodatkowych pol
        if (POKAZUJ_FILTRY_DODATKOWE_POLA == 'tak' && $info['categories_filters'] == '1' ) {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', Filtry::FiltrSelect( Filtry::FiltrDodatkowePola($IdPodkategorii, 'kategoria'), 'p' ));        
        } else {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', ''); 
        }
        
        // filtr producenta
        if (POKAZUJ_FILTRY_PRODUCENCI == 'tak' ) {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', Filtry::FiltrProducentaSelect($IdPodkategorii) );
        } else {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', '');
        }
        
        // filtr dostepnosci
        if (POKAZUJ_FILTRY_DOSTEPNOSCI == 'tak' ) {
            $srodek->dodaj('__FILTRY_DOSTEPNOSCI', Filtry::FiltrDostepnoscSelect($IdPodkategorii, 'kategoria') );
        } else {
            $srodek->dodaj('__FILTRY_DOSTEPNOSCI', '');
        }    

        // filtr czasu wysylki
        if (POKAZUJ_FILTRY_CZAS_WYSYLKI == 'tak' ) {
            $srodek->dodaj('__FILTRY_CZAS_WYSYLKI', Filtry::FiltrCzasWysylkiSelect($IdPodkategorii, 'kategoria') );
        } else {
            $srodek->dodaj('__FILTRY_CZAS_WYSYLKI', '');
        }        
        
        unset($IdPodkategorii);
        
        unset($info);

    }

    // jezeli jest wywolany producent - szukanie danych producenta
    if (isset($_GET['idproducent'])) {
        //                            
        
        $LinkDoPrzenoszenia = Seo::link_SEO($info['manufacturers_name'], (int)$_GET['idproducent'], 'producent');

        // *****************************
        // jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
        if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
            $GLOBALS['db']->close_query($sql); 
            unset($WywolanyPlik, $Meta, $IdWyswietlanejKategorii, $srodek, $zapytanie);
            //
            Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/'));
        }    
        // *****************************  

        include('listing_gora.php');

        // meta tagi
        $Meta = MetaTagi::ZwrocMetaTagi( '' );
        //
        $tpl->dodaj('__META_TYTUL', ((empty($info['manufacturers_meta_title_tag'])) ? $Meta['tytul'] : $info['manufacturers_meta_title_tag']));
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['manufacturers_meta_keywords_tag'])) ? $Meta['slowa'] : $info['manufacturers_meta_keywords_tag']));
        $tpl->dodaj('__META_OPIS', ((empty($info['manufacturers_meta_desc_tag'])) ? $Meta['opis'] : $info['manufacturers_meta_desc_tag']));
        unset($Meta); 
        
        // Breadcrumb dla producenta
        $nawigacja->dodaj($info['manufacturers_name'], Seo::link_SEO($info['manufacturers_name'], (int)$_GET['idproducent'], 'producent'));
        $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

        //
        $srodek->dodaj('__NAGLOWEK_LISTINGU', $info['manufacturers_name']);
        //
        if ( strpos((string)$info['manufacturers_description'], '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
             //
             $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$info['manufacturers_description']);
             //
             if ( count($PodzielTekst) == 2 ) {
                  $info['manufacturers_description'] = Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . '0-' . $info['manufacturers_id'] . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . '0-' . $info['manufacturers_id'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . '0-' . $info['manufacturers_id'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div>';
             } else {
                  $info['manufacturers_description'] = str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$info['manufacturers_description']) . '<div style="clear:both"></div>';
             }
             //
        } else {
             //
             $info['manufacturers_description'] = $info['manufacturers_description'] . '<div style="clear:both"></div>';
             //
        }
        // dane rozporzadzania gpsr
        $gpsr = '';
        //
        if ( !empty($info['manufacturers_url']) ) {
             //
             $gpsr .= '<div style="padding-bottom:20px" class="InfoProducentLink FormatEdytor"><h4>' . $GLOBALS['tlumacz']['STRONA_WWW'] . '</h4>';
             //
             $gpsr .= $info['manufacturers_url'];
             //
             $gpsr .= '</div>';        
             //
        }
        //
        if ( !empty($info['manufacturers_street']) && !empty($info['manufacturers_city']) && !empty($info['manufacturers_country']) && (!empty($info['manufacturers_email']) || !empty($info['manufacturers_phone'])) ) {
             //
             $gpsr .= '<div style="padding-bottom:20px" class="InfoProducentOpis FormatEdytor"><h4>' . $GLOBALS['tlumacz']['DANE_KONTAKTOWE_PRODUCENTA'] . '</h4>';
             //
             $gpsr .= ((!empty($info['manufacturers_full_name'])) ? $info['manufacturers_full_name'] : $info['manufacturers_name']) . ', ' . $info['manufacturers_street'];
             //
             if ( !empty($info['manufacturers_post_code']) ) {
                  //
                  $gpsr .= ', ' . $info['manufacturers_post_code'] . ' ' . $info['manufacturers_city'];
                  //
             } else {
                  //
                  $gpsr .= ', ' . $info['manufacturers_city'];
                  //
             }
             //
             foreach ( Funkcje::KrajeIso() as $kraj => $iso ) {
                  //
                  if ( $iso ==  $info['manufacturers_country'] ) {
                       //
                       $gpsr .= ', ' . $kraj;
                       //
                  }
                  //
             }
             //
             if ( !empty($info['manufacturers_email']) ) {
                  //
                  $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['EMAIL']) . ' ' . $info['manufacturers_email'];
                  //
             }    
             if ( !empty($info['manufacturers_phone']) ) {
                  //
                  $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['TELEFON']) . ': ' . $info['manufacturers_phone'];
                  //
             }              
             //
             $gpsr .= '</div>';
             //
        }
        //
        if ( (int)$info['importer_unchanged'] == 1 ) {
              //
              if ( !empty($info['importer_street']) && !empty($info['importer_city']) && !empty($info['importer_country']) && (!empty($info['importer_email']) || !empty($info['importer_phone'])) ) {
                   //
                   $gpsr .= '<div style="padding-bottom:20px" class="InfoImporterOpis FormatEdytor"><h4>' . $GLOBALS['tlumacz']['DANE_KONTAKTOWE_IMPORTERA'] . '</h4>';
                   //
                   $gpsr .= $info['importer_name'] . ', ' . $info['importer_street'];
                   //
                   if ( !empty($info['importer_post_code']) ) {
                        //
                        $gpsr .= ', ' . $info['importer_post_code'] . ' ' . $info['importer_city'];
                        //
                   } else {
                        //
                        $gpsr .= ', ' . $info['importer_city'];
                        //
                   }
                   //
                   foreach ( Funkcje::KrajeIso() as $kraj => $iso ) {
                        //
                        if ( $iso ==  $info['importer_country'] ) {
                             //
                             $gpsr .= ', ' . $kraj;
                             //
                        }
                        //
                   }
                   //
                   if ( !empty($info['importer_email']) ) {
                        //
                        $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['EMAIL']) . ' ' . $info['importer_email'];
                        //
                   }    
                   if ( !empty($info['importer_phone']) ) {
                        //
                        $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['TELEFON']) . ': ' . $info['importer_phone'];
                        //
                   }              
                   //
                   $gpsr .= '</div>';
                   //
              }
              //
        }
        //
        if ( $gpsr != '' ) {
             //
             $gpsr = '<div class="InfoProducentImporterOpis" style="padding:20px 0 10px 0">' . $gpsr . '</div>';
             //
        }
        //
        if ( LISTING_PRODUCENT_GPSR == 'nad listingiem produktów' ) {
             //
             $info['manufacturers_description'] .= $gpsr;
             //
        } else {
             //
             $info['manufacturers_description_bottom'] .= $gpsr;
             //
        }
        //
        if ( strpos((string)$info['manufacturers_description_bottom'], '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
             //
             $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$info['manufacturers_description_bottom']);
             //
             if ( count($PodzielTekst) == 2 ) {
                  $info['manufacturers_description_bottom'] = Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . '1-' . $info['manufacturers_id'] . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . '1-' . $info['manufacturers_id'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . '1-' . $info['manufacturers_id'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div>';
             } else {
                  $info['manufacturers_description_bottom'] = str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$info['manufacturers_description_bottom']) . '<div style="clear:both"></div>';
             }
             //
        } else {
             //
             $info['manufacturers_description_bottom'] = $info['manufacturers_description_bottom'] . '<div style="clear:both"></div>';
             //
        }       
        //
        $srodek->dodaj('__OPIS_LISTINGU', (strlen((string)$info['manufacturers_description']) > 40 ? '<div class="FormatEdytor">' . $info['manufacturers_description'] . '</div>' : ''));
        $srodek->dodaj('__OPIS_LISTINGU_DOL', ((strlen((string)$info['manufacturers_description_bottom']) > 40) ? '<div class="FormatEdytor">' . $info['manufacturers_description_bottom'] . '</div>' : ''));
        $srodek->dodaj('__ZDJECIE_LISTINGU', '');
        $srodek->dodaj('__OPIS_BEZ_ZDJECIA', 'OpisListingGornyBezZdjecia');
        $srodek->dodaj('__CSS_OPIS_LISTINGU', '');
        
        if (strlen((string)$info['manufacturers_description']) > 40) {
          
            if ( $info['manufacturers_image'] != '' && file_exists(KATALOG_ZDJEC . '/' . $info['manufacturers_image']) && LISTING_OPIS_ZDJECIE == 'tak' ) {
                 //
                 $srodek->dodaj('__ZDJECIE_LISTINGU', '<div class="ZdjecieListing">' . Funkcje::pokazObrazek($info['manufacturers_image'], $info['manufacturers_name'], SZEROKOSC_ZDJECIA_KATEGORII_PRODUCENTA, WYSOKOSC_ZDJECIA_KATEGORII_PRODUCENTA, array(), '', 'maly', true, false, false) . '</div>');
                 //
                 if ( Wyglad::TypSzablonu() == true ) {
                      //
                      $srodek->dodaj('__OPIS_BEZ_ZDJECIA', '');
                      //
                 }
                 //
            }         
            
        } else {
          
            $srodek->dodaj('__CSS_OPIS_LISTINGU', ' style="display:none"');
            
        }
        // jezeli jest wylaczone wyswietlanie opisu kategorii (producenta) na kolejnych stronach
        if ( LISTING_OPIS_PODSTRONY == 'nie' && isset($_GET['s']) && (int)$_GET['s'] > 1 ) {
             $srodek->dodaj('__OPIS_LISTINGU', '');
             $srodek->dodaj('__OPIS_LISTINGU_DOL', '');
             $srodek->dodaj('__ZDJECIE_LISTINGU', '');
             $srodek->dodaj('__OPIS_BEZ_ZDJECIA', '');
             $srodek->dodaj('__CSS_OPIS_LISTINGU', ' style="display:none"');
        } 
        
        //
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie, $info); 
        //  
        $zapytanie_stronicowanie = Produkty::SqlProduktyProducentaStronicowanie((int)$_GET['idproducent'], $WarunkiFiltrowania);                             
        //
        $zapytanie = Produkty::SqlProduktyProducenta((int)$_GET['idproducent'], $WarunkiFiltrowania, $Sortowanie);                            
        //
        $sql = $GLOBALS['db']->open_query($zapytanie_stronicowanie);
        //
        
        $srodek->dodaj('__PODKATEGORIE', '');
        $srodek->dodaj('__CSS_PODKATEGORIE', ' style="display:none"');
        $srodek->dodaj('__CSS_PODKATEGORIE_ZWINIETE', ' style="display:none"');
        
        // filtr nowosci
        if (POKAZUJ_FILTRY_NOWOSCI == 'tak') {
            $srodek->dodaj('__FILTRY_NOWOSCI', Filtry::FiltrNowosciSelect((int)$_GET['idproducent'], 'producent'));
        } else {
            $srodek->dodaj('__FILTRY_NOWOSCI', '');
        }    
        
        // filtr promocji
        if (POKAZUJ_FILTRY_PROMOCJE == 'tak') {
            $srodek->dodaj('__FILTRY_PROMOCJE', Filtry::FiltrPromocjeSelect((int)$_GET['idproducent'], 'producent'));        
        } else {
            $srodek->dodaj('__FILTRY_PROMOCJE', '');
        }
        
        // filtry cech
        if (POKAZUJ_FILTRY_CECH == 'tak') {
            $srodek->dodaj('__FILTRY_PO_CECHACH', Filtry::FiltrSelect( Filtry::FiltrCech((int)$_GET['idproducent'], 'producent'), 'c' ));
        } else {
            $srodek->dodaj('__FILTRY_PO_CECHACH', '');
        }
        
        // filtry dodatkowych pol
        if (POKAZUJ_FILTRY_DODATKOWE_POLA == 'tak') {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', Filtry::FiltrSelect( Filtry::FiltrDodatkowePola((int)$_GET['idproducent'], 'producent'), 'p' ));    
        } else {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', ''); 
        }
        
        // filtr kategorii
        if (POKAZUJ_FILTRY_KATEGORIE == 'tak') {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', Filtry::FiltrKategoriiSelect((int)$_GET['idproducent']));
        } else {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', '');
        }
        
        // filtr dostepnosci
        if (POKAZUJ_FILTRY_DOSTEPNOSCI == 'tak') {
            $srodek->dodaj('__FILTRY_DOSTEPNOSCI', Filtry::FiltrDostepnoscSelect((int)$_GET['idproducent'], 'producent') );
        } else {
            $srodek->dodaj('__FILTRY_DOSTEPNOSCI', '');
        }    

        // filtr czasu wysylki
        if (POKAZUJ_FILTRY_CZAS_WYSYLKI == 'tak') {
            $srodek->dodaj('__FILTRY_CZAS_WYSYLKI', Filtry::FiltrCzasWysylkiSelect((int)$_GET['idproducent'], 'producent') );
        } else {
            $srodek->dodaj('__FILTRY_CZAS_WYSYLKI', '');
        }           

    }

    // integracja z CRITEO
    $tpl->dodaj('__CRITEO', IntegracjeZewnetrzne::CriteoListing( $sql ));

    include('listing_dol.php');
    
}    

unset($id);

include('koniec.php');

?>