<?php

class Kategorie {

    public static function TablicaKategorieGlobal() {
        
        $TablicaKategorii = array();
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('TablicaKategorii_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_KATEGORIE); 

        if ( !$WynikCache ) {        
        
            // pobiera dane z bazy
            $sql = $GLOBALS['db']->open_query("SELECT c.categories_image as Foto, 
                                                      c.categories_icon as Ikona, 
                                                      cd.categories_name as Nazwa,
                                                      cd.categories_name_info as NazwaDodatkowa,
                                                      cd.categories_name_menu as NazwaMenu,
                                                      cd.categories_description_info as OpisDodatkowy,
                                                      cd.categories_seo_url as NazwaSeo,
                                                      cd.categories_description as Opis,
                                                      cd.categories_description_bottom as OpisDol,
                                                      c.categories_id as IdKat, 
                                                      c.categories_view as Widocznosc,
                                                      c.categories_filters as Filtry,
                                                      c.parent_id as Parent, 
                                                      c.categories_color as Kolor, 
                                                      c.categories_color_status as KolorStatus, 
                                                      c.categories_background_color as KolorTla, 
                                                      c.categories_background_color_status as KolorTlaStatus,                                                       
                                                      c.sort_order as Sortowanie,
                                                      0 as IloscProduktow, 
                                                      0 as Produkty,
                                                      0 as WszystkichProduktow,
                                                      0 as WszystkichProduktowId
                                                 FROM categories c
                                            LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id AND cd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                                WHERE c.categories_status = '1'
                                             ORDER BY c.sort_order, cd.categories_name");

            while ($info = $sql->fetch_assoc()) {
                //
                $TablicaKategorii[$info['IdKat']] = $info;
                //
                if ( empty($TablicaKategorii[$info['IdKat']]['NazwaSeo']) ) {
                     //
                     $TablicaKategorii[$info['IdKat']]['NazwaSeo'] = $TablicaKategorii[$info['IdKat']]['Nazwa'];
                     //
                }
                //
            }
            
            $GLOBALS['cache']->zapisz('TablicaKategorii_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaKategorii, CACHE_KATEGORIE);
            
        } else {
        
            $TablicaKategorii = $WynikCache;
        
        }

        if ( !$WynikCache ) {  
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info);
        }
        
        unset($WynikCache);      
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii lub ukrywanie kategorii bez produktow
        if (LISTING_ILOSC_PRODUKTOW == 'tak' || LISTING_PUSTE_KATEGORIE == 'nie') {
        
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('TablicaKategorii_Ilosc_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_KATEGORIE, true);  

            if ( !$WynikCache ) { 
                           
                $sql = $GLOBALS['db']->open_query("SELECT GROUP_CONCAT(p.products_id) as IdProduktu, p2c.categories_id as Kategoria 
                                                     FROM products p, products_to_categories p2c, categories c 
                                                    WHERE p.products_id = p2c.products_id AND c.categories_id = p2c.categories_id AND c.categories_status = '1' AND p.products_status = '1' AND p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . "  
                                                 GROUP BY kategoria");                                             
                    
                while ($info = $sql->fetch_assoc()) {
                    //
                    // ilosc produktow
                    $TablicaKategorii[$info['Kategoria']]['IloscProduktow'] = count(explode(',', (string)$info['IdProduktu']));
                    //
                    // id produktow
                    $TablicaKategorii[$info['Kategoria']]['Produkty'] = $info['IdProduktu'];
                    //
                }

                foreach ($TablicaKategorii as $Pozycja) {
                  
                    $IdKat = $Pozycja['IdKat'];
                    $TabIdProduktow = $Pozycja['Produkty'];
                    
                    do {
                        if (!isset($TablicaKategorii[$IdKat])) {
                            $TablicaKategorii[$IdKat] = array();
                        }
                        
                        if (isset($TablicaKategorii[$IdKat]['WszystkichProduktowId'])) {
                            $TablicaKategorii[$IdKat]['WszystkichProduktowId'] .= (($TabIdProduktow != '0') ? ',' . $TabIdProduktow : '');
                        }
                        
                        if (isset($TablicaKategorii[$IdKat]['Parent'])) {
                            $IdKat = $TablicaKategorii[$IdKat]['Parent'];
                          } else {
                            $IdKat = 0;
                        }
                    } while ($IdKat > 0);
                    
                    unset($IdKat);
                    
                }
            
                // przypisze ilosc produktow w danej kategorii i usunie dublowanie id produktow
                foreach ($TablicaKategorii as $Pozycja) {
                    //
                    if ( isset($Pozycja['IdKat']) ) {
                      
                        $IdKat = $Pozycja['IdKat'];
                        //
                        if ( $TablicaKategorii[$IdKat]['WszystkichProduktowId'] != '' ) {
                             //
                             $TmpTb = explode(',', (string)$TablicaKategorii[$IdKat]['WszystkichProduktowId']);
                             $TmpTb = array_unique($TmpTb);
                             //
                             if (isset($TablicaKategorii[$IdKat]['WszystkichProduktow'])) {
                                $TablicaKategorii[$IdKat]['WszystkichProduktow'] = count($TmpTb) - 1;
                                //
                                // jezeli kategoria nie ma produktow
                                if (LISTING_PUSTE_KATEGORIE == 'nie') {
                                    //
                                    if ( count($TmpTb) - 1 == 0 ) {
                                         unset($TablicaKategorii[$IdKat]);
                                    }
                                    //
                                }
                                //
                             }                    
                             //
                             unset($TmpTb);
                             //
                        }
                        //
                        unset($TablicaKategorii[$IdKat]['WszystkichProduktowId']);
                        unset($TablicaKategorii[$IdKat]['Produkty']);
                        //
                        
                    }
                    
                }
                
                $GLOBALS['cache']->zapisz('TablicaKategorii_Ilosc_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaKategorii, CACHE_KATEGORIE);
            
            } else {
        
                $TablicaKategorii = $WynikCache;
        
            }                
            
            if ( !$WynikCache ) {  
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $info);
            }
            
            unset($WynikCache);              

        }
        
        return $TablicaKategorii;

    }
    
    // zwraca nazwe kategorii po id kategorii
    public static function NazwaKategoriiId ( $id, $menu = false ) {
        //
        if ( isset($GLOBALS['tablicaKategorii'][$id]) ) {
             //
             if ( isset($GLOBALS['tablicaKategorii'][$id]['Nazwa']) ) {
                  //
                  if ( $menu == true && isset($GLOBALS['tablicaKategorii'][$id]['NazwaMenu']) && $GLOBALS['tablicaKategorii'][$id]['NazwaMenu'] != '' ) {
                       return $GLOBALS['tablicaKategorii'][$id]['NazwaMenu'];
                  } else {
                       return $GLOBALS['tablicaKategorii'][$id]['Nazwa'];
                  }
                  //
             } else {
                  return '';
             }
          } else {
             return '';
        }
        //
    }
    
    // zwraca nazwe SEO kategorii po id kategorii
    public static function NazwaKategoriiSeoId ( $id ) {
        //
        if ( isset($GLOBALS['tablicaKategorii'][$id]) ) {
             if ( isset($GLOBALS['tablicaKategorii'][$id]['NazwaSeo']) ) {
                  return $GLOBALS['tablicaKategorii'][$id]['NazwaSeo'];
             } else {
                  return '';
             }
          } else {
             return '';
        }
        //
    }    
    
    // zwraca tablice tylko z danymi o okreslonym parent - uzywane do wyszukiwania zaawansowanego
    public static function TablicaKategorieParent( $Parent = '0', $brak = '' ) {
        //
        $TablicaWynik = array();
        //
        if ($brak != '') {
            $TablicaWynik[] = array('id' => 0, 'text' => $brak);
        }        
        //
        foreach ($GLOBALS['tablicaKategorii'] as $IdKat => $TablicaWartosci) {
            //
            if ( isset($TablicaWartosci['Parent']) && $TablicaWartosci['Parent'] == $Parent) {
                //
                // tylko widoczne kategorie
                if ( $TablicaWartosci['Widocznosc'] == '1' ) {
                     $TablicaWynik[] = array('id' => $IdKat, 'text' => $TablicaWartosci['Nazwa'], 'seo' => ((!empty($TablicaWartosci['NazwaSeo'])) ? $TablicaWartosci['NazwaSeo'] : $TablicaWartosci['Nazwa']), 'foto' => $TablicaWartosci['Foto']);
                }
                //
            }
            //
        }
        //
        return $TablicaWynik;
        //
    }
    
    // tworzy tablice z drzewem kategorii - poszczegolne podkategorie sa jako podtablice
    public static function DrzewoKategorii( $Parent = '0', $Widocznosc = false ) {

        $ListaPodkategorii = array();
        $ListaKategorii = array();
        
        foreach($GLOBALS['tablicaKategorii'] as $Tmp) {
        
            if (isset($Tmp['IdKat'])) {
            
                $Pozycja = &$ListaPodkategorii[ $Tmp['IdKat'] ];
                $Pozycja['IdKat'] = $Tmp['IdKat'];
                $Pozycja['Parent'] = $Tmp['Parent'];
                $Pozycja['Widocznosc'] = $Tmp['Widocznosc'];
                $Pozycja['Filtry'] = $Tmp['Filtry'];
                $Pozycja['Nazwa'] = $Tmp['Nazwa'];
                $Pozycja['NazwaDodatkowa'] = $Tmp['NazwaDodatkowa'];
                $Pozycja['NazwaMenu'] = $Tmp['NazwaMenu'];
                $Pozycja['OpisDodatkowy'] = $Tmp['OpisDodatkowy'];
                $Pozycja['NazwaSeo'] = ((!empty($Tmp['NazwaSeo'])) ? $Tmp['NazwaSeo'] : $Tmp['Nazwa']);
                $Pozycja['Opis'] = ((LISTING_PODKATEGORIE_OPIS == 'tak') ? '<div class="FormatEdytor">' . $Tmp['Opis'] . '</div>' : '');
                $Pozycja['OpisDol'] = '<div class="FormatEdytor">' . $Tmp['OpisDol'] . '</div>';
                $Pozycja['Foto'] = $Tmp['Foto'];
                $Pozycja['Ikona'] = $Tmp['Ikona'];
                $Pozycja['Kolor'] = $Tmp['Kolor'];
                $Pozycja['KolorStatus'] = (($Tmp['KolorStatus'] == 1) ? 'tak' : 'nie');
                $Pozycja['KolorTla'] = $Tmp['KolorTla'];
                $Pozycja['KolorTlaStatus'] = (($Tmp['KolorTlaStatus'] == 1) ? 'tak' : 'nie');                
                $Pozycja['IloscProduktow'] = $Tmp['IloscProduktow'];
                $Pozycja['WszystkichProduktow'] = $Tmp['WszystkichProduktow'];
                $Pozycja['Sortowanie'] = $Tmp['Sortowanie'];
                
                if ( ( $Tmp['Widocznosc'] == '1' && $Widocznosc == false ) || $Widocznosc == true ) {
                
                    if ($Tmp['Parent'] == $Parent) {
                        //
                        $ListaKategorii[ $Tmp['IdKat'] ] = &$Pozycja;
                        //
                    } else {
                        //
                        $ListaPodkategorii[ $Tmp['Parent'] ]['Podkategorie'][ $Tmp['IdKat'] ] = &$Pozycja;
                        //
                    }
                
                }
                
            }
            
        }
        
        return $ListaKategorii;

    }    
    
    // wyswietla kategorie w formie ul i li
    //
    // opis opcji //
    // TylkoRozwin = id kategorii dla jakiej ma byc rozwijane drzewo
    // GlebokoscDrzewa - ile podkategorii ma wyswietlac
    // Przyklej - alternatywny tekst na poczatku cpath - np jezeli ma wyswietlac tylko drzewo podkategorii
    // Separator - jaki ciag ma byc separatorem kategorii
    // KlasaCss - klasa dla aktywnej kategorii
    //
    // np wywolanie samych podkategorii dla okreslonej id kategorii
    // <ul>
    // foreach(Kategorie::DrzewoKategorii('14') as $IdKategorii => $Tablica) {
    //    echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica, '',10,'14_');
    // }    
    // </ul>
    //
    // rozwiniecie drzewa tylko dla podkategori id 2
    // foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    //    echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica, '2');
    // }
    // 
    // kompletne drzewo kategorii
    // foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    //    echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica);
    // }
    //
    public static function WyswietlKategorie($IdKat, $Tablica, $TylkoRozwin = array(), $GlebokoscDrzewa = 10, $Przyklej = '', $KlasaCss = 'Aktywna', $Separator = '_', $ParentGlowny = '', $CiagDoWyswietlania = '', $IdAktywne = array(), $PokazIkone = 'nie') {
    
        $cPath = $ParentGlowny . $IdKat;
        $PodzielCPath = explode($Separator, (string)$cPath);    
        
        // klasa css aktywnej kategorii
        $css = array();
        $cssTla = '';
        $cssKolor = '';
        
        if ( !empty($TylkoRozwin) || !empty($IdAktywne) ) {
            //
            if (in_array($IdKat, $TylkoRozwin) || in_array($IdKat, $IdAktywne)) {
                $css[] = $KlasaCss;
            }
            //
        }
        
        // kolorowanie kategorii
        if ( $Tablica['KolorStatus'] == 'tak' && trim((string)$Tablica['Kolor']) != '' && strlen((string)$Tablica['Kolor']) == 6 ) {
             $cssKolor = ' style="color:#' . $Tablica['Kolor'] . '"';
        }  

        // kolorowanie tla kategorii
        if ( $Tablica['KolorTlaStatus'] == 'tak' && trim((string)$Tablica['KolorTla']) != '' && strlen((string)$Tablica['KolorTla']) == 6 ) {
             $cssTla = ' style="background:#' . $Tablica['KolorTla'] . '"';
        }        
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em' . $cssKolor . '>(' . $Tablica['WszystkichProduktow'] . ')</em>';
        }
        
        // ikona produktu
        $Ikona = '';
        $Nazwa = $Tablica['Nazwa'] . $SumaProduktow . ((!empty($Tablica['NazwaDodatkowa'])) ? '<small>' . $Tablica['NazwaDodatkowa'] . '</small>' : '');
        
        if ( $PokazIkone == 'tak' ) {
             //
             if ( $Tablica['Ikona'] != '' && file_exists(KATALOG_ZDJEC . '/' . $Tablica['Ikona']) ) {
                  //
                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $Tablica['Ikona']);
                  //
                  if ( (int)$szerokosc == 0 ) {
                       $szerokosc = 100;
                  }
                  if ( (int)$wysokosc == 0 ) {
                       $wysokosc = 100;
                  }                  
                  //
                  $Ikona = '<span class="GrafikaLink"><img src="/' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '" width="' . $szerokosc . '" height="' . $wysokosc . '" alt="' . $Tablica['Nazwa'] . '" /></span>';
                  $Nazwa = '<span class="IkonaLink">' . $Nazwa . '</span>';
                  //
                  $css[] = 'KategoriaIkona';
                  //
             } else {
                  //
                  $Nazwa = '<span>' . $Nazwa . '</span>';
                  //
             }
             //
        }

        $CiagDoWyswietlania .= '<li>' . (( $cssTla != '') ? '<div' . $cssTla . '>' : '<div>') . '<a' . ((count($css) > 0) ? ' class="' . implode(' ', (array)$css) . '"' : '') . ' href="' . Seo::link_SEO($Tablica['NazwaSeo'], $Przyklej . $ParentGlowny . $IdKat, 'kategoria') . '"' . $cssKolor . '>' . $Ikona . $Nazwa . '</a>' . (( $cssTla != '') ? '</div>' : '</div>') . '';
        
        unset($Ikona, $Nazwa);

        if (Funkcje::SzukajwTablicy($PodzielCPath, $TylkoRozwin) || empty($TylkoRozwin)) {
        
            if (count($PodzielCPath) <= $GlebokoscDrzewa) {

                if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
                    $CiagDoWyswietlania .= '<ul>';
                    foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                        $CiagDoWyswietlania .= Kategorie::WyswietlKategorie($PodkatId, $Podkat, $TylkoRozwin, $GlebokoscDrzewa, $Przyklej, $KlasaCss, $Separator, $cPath . $Separator, '', $IdAktywne, $PokazIkone);
                    }
                    $CiagDoWyswietlania .= '</ul>';
                }
            
            }
            
        }
        
        unset($cPath, $PodzielCPath, $css, $cssKolor, $cssTla, $SumaProduktow);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }    
    
    // funkcja j.w. - uproszczona do cennikow produktow
    public static function WyswietlKategorieCennik($Tablica, $typ = 'pdf', $CiagDoWyswietlania = '') {
    
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }

        if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/pobierz.svg') ) {

             $CiagDoWyswietlania .= '<li><div><span>' . $Tablica['Nazwa'] . $SumaProduktow . '</span><a class="PobierzIkona"  title="' . $GLOBALS['tlumacz']['POBIERZ_CENNIK'] . '" href="pobierz-cennik.html/typ=' . $typ . '/id=' . $Tablica['IdKat'] . '"></a></div>';
             
        } else {
            
             $CiagDoWyswietlania .= '<li>' . $Tablica['Nazwa'] . $SumaProduktow . '<a href="pobierz-cennik.html/typ=' . $typ . '/id=' . $Tablica['IdKat'] . '"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/pobierz.png" alt="' . $GLOBALS['tlumacz']['POBIERZ_CENNIK'] . '" title="' . $GLOBALS['tlumacz']['POBIERZ_CENNIK'] . '" /></a>';
             
        }

        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $CiagDoWyswietlania .= '<ul>';
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                $CiagDoWyswietlania .= Kategorie::WyswietlKategorieCennik($Podkat, $typ);
            }
            $CiagDoWyswietlania .= '</ul>';
        }

        unset($SumaProduktow);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }    

    // funkcja j.w. - uproszczona do gornego menu
    public static function WyswietlKategorieGorneMenu($Tablica, $IdKategorii, $CiagDoWyswietlania, $poziomMenu, $nrPoziom, $WysokoscKolumn = 0, $PozycjaKonfiguracji = array()) {
      
        if ( $IdKategorii == '' ) {
            $IdKategorii = '';
        }
        if ( $CiagDoWyswietlania == '' ) {
            $CiagDoWyswietlania = '';
        }

        $Losowa = rand(1,100000);

        $CssPierwszyLink = '';
        $MenuDalej = '';
        //
        if ( $poziomMenu > 1 && Wyglad::TypSzablonu() == true ) { 
            //
            if ( $nrPoziom == 1 && isset($PozycjaKonfiguracji['podkategorie']) && $PozycjaKonfiguracji['podkategorie'] == 'tak' ) {
                 //
                 if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                      //
                      $CssPierwszyLink = 'KolejneKategorie';
                      //
                 }
                 //
            }
            //
            if ( isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie']) ) {
                 //
                 if ( $nrPoziom + 1 <= $poziomMenu && (isset($PozycjaKonfiguracji['szerokosc']) && !in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent'))) ) {
                      //
                      $CssPierwszyLink = 'KolejneKategorie';                        
                      $MenuDalej = '<span class="RozwinDalej" style="display:none" data-id="' . $Losowa . '_' . $Tablica['IdKat'] . '"></span>';
                      //
                 }
                 //
            }
            //
        }

        $CiagDoWyswietlaniaGrafika = ( $Tablica['NazwaMenu'] != '' ? $Tablica['NazwaMenu'] : $Tablica['Nazwa'] );
        
        $CzyJestGrafikaKategorii = false;

        if ( $nrPoziom == 1 ) {
          
             if ( isset($PozycjaKonfiguracji['grafika_kategorie']) && $PozycjaKonfiguracji['grafika_kategorie'] == 'tak' && Wyglad::TypSzablonu() == true ) {
                  
                  // grafika kategorii
                  $GrafikaKategorie = '';
                  $MiejsceGrafikiKategorii = '';
                  $SzerokoscGrafikiKategorii = '';
                  $SzerokoscNazwyKategorii = '';
                  
                  $TypGrafikiKategorii = 'ikona';
                  $WersjaMobilnaGrafikiKategorii = '';
                  
                  if ( isset($PozycjaKonfiguracji['rodzaj_grafika_kategorie']) ) {
                       //
                       if ( $Tablica['Foto'] == '' || !file_exists(KATALOG_ZDJEC . '/' . $Tablica['Foto']) ) {
                            //
                            $Tablica['Foto'] = 'domyslny.webp';
                            //
                       }
                       if ( $Tablica['Ikona'] == '' || !file_exists(KATALOG_ZDJEC . '/' . $Tablica['Ikona']) ) {
                            //
                            $Tablica['Ikona'] = 'domyslny.webp';
                            //
                       }                       
                       //
                       if ( $PozycjaKonfiguracji['rodzaj_grafika_kategorie'] == 'grafika' && $Tablica['Foto'] != '' ) {
                            //
                            $TypGrafikiKategorii = 'grafika';
                            //
                            if ( isset($PozycjaKonfiguracji['rozmiar_grafika_kategorie']) && $PozycjaKonfiguracji['rozmiar_grafika_kategorie'] != 'brak' && (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'] > 0 ) {
                                 //
                                 $GrafikaKategorie = Funkcje::pokazObrazek($Tablica['Foto'], $Tablica['Nazwa'], (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'], (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'], array(), '', 'maly', true, false, false);
                                 $SzerokoscGrafikiKategorii = ' style="width:' . (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'] . 'px"';
                                 $SzerokoscNazwyKategorii = ' style="width:calc(100% - ' . (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'] . 'px)"';
                                 //
                            }
                            if ( isset($PozycjaKonfiguracji['rozmiar_grafika_kategorie']) && $PozycjaKonfiguracji['rozmiar_grafika_kategorie'] == 'brak' ) {
                                 //
                                 $GrafikaKategorie = '<img src="' . KATALOG_ZDJEC . '/' . $Tablica['Foto'] . '" alt="' . $Tablica['Nazwa'] . '" />';
                                 //
                            }                            
                            //
                       }
                       if ( $PozycjaKonfiguracji['rodzaj_grafika_kategorie'] == 'ikona' && $Tablica['Ikona'] != '' ) {
                            //
                            $TypGrafikiKategorii = 'ikona';
                            //
                            if ( isset($PozycjaKonfiguracji['rozmiar_grafika_kategorie']) && $PozycjaKonfiguracji['rozmiar_grafika_kategorie'] != 'brak' && (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'] > 0 ) {
                                 //
                                 $GrafikaKategorie = Funkcje::pokazObrazek($Tablica['Ikona'], $Tablica['Nazwa'], (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'], (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'], array(), '', 'maly', true, false, false);
                                 $SzerokoscGrafikiKategorii = ' style="width:' . (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'] . 'px"';
                                 $SzerokoscNazwyKategorii = ' style="width:calc(100% - ' . (int)$PozycjaKonfiguracji['rozmiar_grafika_kategorie'] . 'px)"';
                                 //
                            }
                            if ( isset($PozycjaKonfiguracji['rozmiar_grafika_kategorie']) && $PozycjaKonfiguracji['rozmiar_grafika_kategorie'] == 'brak' ) {
                                 //
                                 $GrafikaKategorie = '<img src="' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '" alt="' . $Tablica['Nazwa'] . '" />';
                                 //
                            }                            
                            //
                       }                       
                       //
                  }
                  
                  if ( isset($PozycjaKonfiguracji['miejsce_grafika_kategorie']) && $PozycjaKonfiguracji['miejsce_grafika_kategorie'] == 'obok' ) {
                       //
                       $MiejsceGrafikiKategorii = ' GrafikaObokNazwy';
                       //
                       if ( $GrafikaKategorie != '' ) {
                            //
                            $GrafikaKategorie = '<span class="GrafikaKategoriiMenu"' . $SzerokoscGrafikiKategorii . '>' . $GrafikaKategorie . '</span>';
                            $CzyJestGrafikaKategorii = true;
                            //
                       }
                       //
                  } else {
                       //
                       $MiejsceGrafikiKategorii = ' GrafikaNadNazwa';
                       //
                       if ( $GrafikaKategorie != '' ) {
                            //
                            $GrafikaKategorie = '<span class="GrafikaKategoriiMenu">' . $GrafikaKategorie . '</span>';
                            $CzyJestGrafikaKategorii = true;
                            //
                       }
                       //
                  }
                  
                  if ( isset($PozycjaKonfiguracji['podkategorie']) && $PozycjaKonfiguracji['podkategorie'] == 'nie' && isset($PozycjaKonfiguracji['miejsce_grafika_kategorie']) && $PozycjaKonfiguracji['miejsce_grafika_kategorie'] == 'nad' ) {
                       //
                       $SzerokoscNazwyKategorii = '';
                       //
                  }

                  $CiagDoWyswietlaniaGrafika = '<span class="MenuGorneGrafikiPozycji' . $MiejsceGrafikiKategorii . '">' . $GrafikaKategorie . '<span' . $SzerokoscNazwyKategorii . '>' . $Tablica['Nazwa'] . '</span></span>';
                  
             }

        }
        
        // jezeli nie ma podkategorii a jest wyswietlanie ikony grafiki to zastosuje inna klase css dla pierwszego linku
        if ( $CzyJestGrafikaKategorii == true && isset($PozycjaKonfiguracji['podkategorie']) && $PozycjaKonfiguracji['podkategorie'] == 'nie' && Wyglad::TypSzablonu() == true ) {
             //
             $CssPierwszyLink = 'LinkDlaGrafikiKategorii';
             //
             if ( isset($PozycjaKonfiguracji['miejsce_grafika_kategorie']) && $PozycjaKonfiguracji['miejsce_grafika_kategorie'] == 'nad' ) {
                  //
                  $CssPierwszyLink = 'LinkDlaGrafikiKategorii NazwaWysrodkowana';
                  //               
             }
             //
        }
        
        if ( $nrPoziom == 1 ) {
          
             if ( isset($PozycjaKonfiguracji['grafika_kategorie']) && $PozycjaKonfiguracji['grafika_kategorie'] == 'tak' && Wyglad::TypSzablonu() == true ) {
               
                  if ( isset($PozycjaKonfiguracji['mobile_grafika_kategorie']) && $PozycjaKonfiguracji['mobile_grafika_kategorie'] == 'nie' ) {
                    
                       $CssPierwszyLink .= ' MenuGorneBezGrafikiPozycjiMobilne';
                    
                  } else {
                       
                       $CssPierwszyLink .= ' MenuGorneGrafikiPozycjiMobilne';
                       
                  }
               
             }
             
        }
        
        if ( $CssPierwszyLink != '' ) {
             //
             $CssPierwszyLink = 'class="' . $CssPierwszyLink . '"';
             //
        }
        $CiagDoWyswietlania .= '<li' . (($nrPoziom == 1) ? ' class="LinkiMenu"' : '') . '>' . $MenuDalej . '<a ' . $CssPierwszyLink . ' href="' . Seo::link_SEO( $Tablica['NazwaSeo'], (($IdKategorii != '') ? $IdKategorii . '_' : '') . $Tablica['IdKat'], 'kategoria') . '">' . $CiagDoWyswietlaniaGrafika . '</a>';
        
        unset($CiagDoWyswietlaniaGrafika, $CssPierwszyLink);

        if ( $poziomMenu > 1 && Wyglad::TypSzablonu() == true ) { 
          
            if ( $nrPoziom + 1 <= $poziomMenu ) {
          
                if (isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
                    //
                    // wysokosc podkategorii
                    $cssWysokosc = '';
                    if ( (int)$WysokoscKolumn > 0 && $nrPoziom == 1 ) {
                         $cssWysokosc = ' class="Scroller" style="max-height:' . (int)$WysokoscKolumn . 'px;overflow-y:auto"';
                    }
                    //
                    $CiagDoWyswietlania .= '<ol id="kat_' . $Losowa . '_' .  $Tablica['IdKat'] . '"' . $cssWysokosc . '>';
                    //
                    foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                        //
                        $CiagDoWyswietlania .= Kategorie::WyswietlKategorieGorneMenu($Podkat, (($IdKategorii != '') ? $IdKategorii . '_' : '') . $Tablica['IdKat'], '', $poziomMenu, $nrPoziom + 1, $WysokoscKolumn, $PozycjaKonfiguracji);
                        //
                    }
                    //
                    $CiagDoWyswietlania .= '</ol>';
                    //
                }
                
            }
        
        } else if ( $poziomMenu > 1 && Wyglad::TypSzablonu() == false ) { 
        
            if (isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
                //
                $CiagDoWyswietlania .= '<ol>';
                //
                foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                    //
                    $CiagDoWyswietlania .= '<li>' .  '<a href="' . Seo::link_SEO( $Podkat['NazwaSeo'], (($IdKategorii != '') ? $IdKategorii . '_' : '') . $Tablica['IdKat'] . '_' . $PodkatId, 'kategoria') . '">' . $Podkat['Nazwa'] . '</a></li>';
                    //
                }
                //
                $CiagDoWyswietlania .= '</ol>';
                //
            }        
        
        }

        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
        
    }       
    
    // funkcja j.w. do wyswietlania kategorii rozwijanych
    public static function WyswietlKategorieAnimacja($IdKat, $Tablica, $TylkoRozwin = array(), $KlasaCss = 'Aktywna', $Separator = '_', $ParentGlowny = '', $CiagDoWyswietlania = '', $PokazIkone = 'nie') {
    
        $cPath = $ParentGlowny . $IdKat;
        $PodzielCPath = explode($Separator, (string)$cPath);    
        
        // klasa css aktywnej kategorii
        $css = array();
        $cssTla = '';
        $cssKolor = '';
        
        if (in_array($IdKat, $TylkoRozwin)) {
            $css[] = $KlasaCss;
        }
        
        // kolorowanie kategorii
        if ( $Tablica['KolorStatus'] == 'tak' && trim((string)$Tablica['Kolor']) != '' && strlen((string)$Tablica['Kolor']) == 6 ) {
             $cssKolor = ' style="color:#' . $Tablica['Kolor'] . '"';
        }      

        // kolorowanie tla kategorii
        if ( $Tablica['KolorTlaStatus'] == 'tak' && trim((string)$Tablica['KolorTla']) != '' && strlen((string)$Tablica['KolorTla']) == 6 ) {
             $cssTla = ' style="background:#' . $Tablica['KolorTla'] . '"';
        }          
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em' . $cssKolor . '>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }
        
        $Rozwin = '';
        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $Rozwin = '<span id="s' . $cPath . '" class="Rozwin Plus"></span>';
        }
        
        // ikona produktu
        $Ikona = '';
        $Nazwa = $Tablica['Nazwa'] . $SumaProduktow . ((!empty($Tablica['NazwaDodatkowa'])) ? '<small>' . $Tablica['NazwaDodatkowa'] . '</small>' : '');
        
        if ( $PokazIkone == 'tak' ) {
             //
             if ( $Tablica['Ikona'] != '' && file_exists(KATALOG_ZDJEC . '/' . $Tablica['Ikona']) ) {
                  //
                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $Tablica['Ikona']);
                  //
                  $Ikona = '<span class="GrafikaLink"><img src="/' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . $Tablica['Nazwa'] . '" /></span>';
                  $Nazwa = '<span class="IkonaLink">' . $Nazwa . '</span>';
                  //
                  $css[] = 'KategoriaIkona';
                  //
             } else {
                  //
                  $Nazwa = '<span>' . $Nazwa . '</span>';
                  //
             }
             //             
        }        

        $CiagDoWyswietlania .= '<li><div' . $cssTla . '>' . $Rozwin . '<a' . ((count($css) > 0) ? ' class="' . implode(' ', (array)$css) . '"' : '') . ' href="' . Seo::link_SEO($Tablica['NazwaSeo'], $ParentGlowny . $IdKat, 'kategoria') . '"' . $cssKolor . '>' . $Ikona . $Nazwa . '</a></div>';
        
        unset($Ikona, $Nazwa);

        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $CiagDoWyswietlania .= '<ul id="rs' . $cPath . '">';
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                $CiagDoWyswietlania .= Kategorie::WyswietlKategorieAnimacja($PodkatId, $Podkat, $TylkoRozwin, $KlasaCss, $Separator, $cPath . $Separator, '', $PokazIkone);
            }
            $CiagDoWyswietlania .= '</ul>';
        }

        unset($cPath, $css, $cssTla, $cssKolor, $SumaProduktow, $Rozwin);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }      
    
    // funkcja j.w. do wyswietlania kategorii wysuwanych
    public static function WyswietlKategorieWysuwane($IdKat, $Tablica, $TylkoRozwin = '', $KlasaCss = 'Aktywna', $Separator = '_', $ParentGlowny = '', $CiagDoWyswietlania = '', $PokazIkone = 'nie') {
    
        $cPath = $ParentGlowny . $IdKat;
        $PodzielCPath = explode($Separator, (string)$cPath);    

        $Pokaz = '';
        $css = array();
        $cssTla = '';
        $cssKolor = '';
        
        if ( isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie']) ) {
            $Pokaz = ' id="w' . $cPath . '" class="Pokaz"';
            $css[] = 'Rozwin';
        }
        
        // kolorowanie kategorii
        if ( $Tablica['KolorStatus'] == 'tak' && trim((string)$Tablica['Kolor']) != '' && strlen((string)$Tablica['Kolor']) == 6 ) {
             $cssKolor .= ' style="color:#' . $Tablica['Kolor'] . '"';
        }        
        
        // kolorowanie tla kategorii
        if ( $Tablica['KolorTlaStatus'] == 'tak' && trim((string)$Tablica['KolorTla']) != '' && strlen((string)$Tablica['KolorTla']) == 6 ) {
             $cssTla = ' style="background:#' . $Tablica['KolorTla'] . '"';
        }  

        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em ' . $cssKolor . '>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }        
        
        // ikona produktu
        $Ikona = '';
        $Nazwa = $Tablica['Nazwa'] . $SumaProduktow . ((!empty($Tablica['NazwaDodatkowa'])) ? '<small>' . $Tablica['NazwaDodatkowa'] . '</small>' : '');
        
        if ( $PokazIkone == 'tak' ) {
             //
             if ( $Tablica['Ikona'] != '' && file_exists(KATALOG_ZDJEC . '/' . $Tablica['Ikona']) ) {
                  //
                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $Tablica['Ikona']);
                  //
                  $Ikona = '<span class="GrafikaLink"><img src="/' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . $Tablica['Nazwa'] . '" /></span>';
                  $Nazwa = '<span class="IkonaLink">' . $Nazwa . '</span>';
                  //
                  $css[] = 'KategoriaIkona';
                  //
             } else {
                  //
                  $Nazwa = '<span>' . $Nazwa . '</span>';
                  //
             }
             //
        }          
        
        // klasa css aktywnej kategorii       
        if ( $ParentGlowny == '' && isset($_GET['idkat']) ) {
            //
            $PodzielTmp = Kategorie::WyczyscPath($_GET['idkat']);
            //
            if ( isset($PodzielTmp[0]) ) {              
                 if ($PodzielTmp[0] == $IdKat) {
                     //
                     $css[] = $KlasaCss;
                     //
                 }
            }
            //
        }         
        
        // przycisk rozwijania mobilny
        $przyciskMobilny = '';
        
        if ( Wyglad::TypSzablonu() == true ) {
             //
             if ( isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie']) ) {
                  //
                  $przyciskMobilny = '<input type="checkbox" class="CheckboxRozwinKategorieWysuwane CheckboxNawigacja" id="irw' . $cPath . '" /><label for="irw' . $cPath . '" class="PrzyciskMobilny"></label>';
                  //
             }
        }
        
        $CiagDoWyswietlania .= '<li' . $Pokaz . '>' . $przyciskMobilny . (( $cssTla != '') ? '<div' . $cssTla . '>' : '') . '<a' . ((count($css) > 0) ? ' class="' . implode(' ', (array)$css) . '"' : '') . $cssKolor . ' href="' . Seo::link_SEO($Tablica['NazwaSeo'], $ParentGlowny . $IdKat, 'kategoria') . '">' . $Ikona . $Nazwa . '</a>' . (( $cssTla != '') ? '</div>' : '') . '';
        
        unset($Ikona, $Nazwa);

        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $CiagDoWyswietlania .= '<ul id="rw' . $cPath . '">';
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                $CiagDoWyswietlania .= Kategorie::WyswietlKategorieWysuwane($PodkatId, $Podkat, $TylkoRozwin, $KlasaCss, $Separator, $cPath . $Separator, '', $PokazIkone);
            }
            $CiagDoWyswietlania .= '</ul>';
        }

        unset($cPath, $cssTla, $css, $cssKolor, $SumaProduktow, $Pokaz);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }      
    
    
    // czysci GET cPath
    public static function WyczyscPath($cPath, $coReturn = 'tablica') {
        //
        $Ciag = explode('_', (string)$cPath);
        $Tablica = array();
        foreach ($Ciag as $Wynik) {
            $Tablica[] = (int)$Wynik;
        }    
        //
        $Tmp = array();
        for ($i=0, $n = sizeof($Tablica); $i < $n; $i++) {
          if (!in_array($Tablica[$i], $Tmp)) {
            $Tmp[] = $Tablica[$i];
          }
        }
        if ($coReturn == 'tablica') {
            return $Tmp;
          } else {
            return implode('_', (array)$Tmp);
        }
    } 
    
    // funkcja zwraca w formie tablicy id wszystkich podkategorii z danej kategorii
    public static function TablicaPodkategorie($Tablica, $IdKat = '') {
    
        $IdKat .= $Tablica['IdKat'] . ',';
    
        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            //
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                //
                $IdKat .= Kategorie::TablicaPodkategorie($Podkat, '');
                //
            }
        }

        return $IdKat;
    }      
      
    // funkcja generujaca pelna sciezke cPath dla id kategorii
    public static function SciezkaKategoriiId($kat_id, $wynik = 'id', $separator = '_') {
        //
        $kategorie = array();
        Kategorie::NadrzednaKategoria($kategorie, $kat_id);
        //
        $ciag = '';
        //
        // jezeli ma zwrocic ciag tekstowy
        if ($wynik == 'nazwy') {
            for ($v = count($kategorie) - 1; $v > -1; $v--) {
                $ciag .= $kategorie[$v]['Nazwa'];
                if ($v > 0) {
                    $ciag .= $separator;
                }
            }
        }
        //
        if ($wynik == 'id') {
            for ($v = count($kategorie) - 2; $v > -1; $v--) {
                $ciag .= $kategorie[$v]['Parent'];
                if ($v > 0) {
                    $ciag .= $separator;
                }
            }
            $ciag .= (($ciag != '') ? $separator : '') . $kat_id;
        }
        //
        return $ciag;
    }     

    // zwraca id nadrzednej kategorii - uzywane do funkcji powyzej
    static function NadrzednaKategoria(&$kategorie, $kategorie_id) {
        //
        if ( !isset($GLOBALS['tablicaKategorii']) ) {
             //
             $GLOBALS['tablicaKategorii'] = Kategorie::TablicaKategorieGlobal();
             //
        }
        //
        $Tmp = array();
        foreach ($GLOBALS['tablicaKategorii'] AS $klucz => $Tablica) {
            if ($klucz == $kategorie_id) {
                if ( isset($Tablica['Parent']) && isset($Tablica['Nazwa']) ) {
                    $Tmp[] = array( 'Parent' => $Tablica['Parent'],
                                    'Nazwa' => $Tablica['Nazwa'] );
                }
                break;
            }
        }
        //
        if (count($Tmp) > 0) {
            $kategorie[count($kategorie)] = array( 'Parent' => $Tmp[0]['Parent'], 
                                                   'Nazwa' => $Tmp[0]['Nazwa'] );
            if ($Tmp[0] != $kategorie_id) {
                Kategorie::NadrzednaKategoria($kategorie, $Tmp[0]['Parent']);
            }
        }
        //
        unset($Tmp);
    }    
    
    
    // zwraca tablice id do jakich nalezy produkt
    static function ProduktKategorie($id = '0', $domyslne = false) {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $id . '_kategorie', CACHE_PRODUKTY);  
        $kategorie = array();
        $kategorieWszystkie = array();
        $kategoriaDomyslna = array();
        $kategorieDomyslne = array();
            
        if ( !$WynikCache && !is_array($WynikCache) ) {        
            //
            $zapytanie = "select ptc.categories_id, ptc.categories_default from products_to_categories ptc, categories c where ptc.categories_id = c.categories_id and c.categories_status = '1' and ptc.products_id = '" . $id . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            
                while ($info = $sql->fetch_assoc()) {
                    //
                    $kategorieWszystkie[] = $info['categories_id'];
                    $kategorieDomyslne[] = array('id' => $info['categories_id'], 'domyslna' => $info['categories_default']);
                    if ( isset($info['categories_default']) && $info['categories_default'] == '1' ) {
                        $kategoriaDomyslna[] = $info['categories_id'];
                    }
                    //            
                }
                
                unset($info);
                
            }
            
            $GLOBALS['db']->close_query($sql); 

            unset($zapytanie);

            if ( count($kategorieDomyslne) == 1 && count($kategoriaDomyslna) > 0 ) {
                $kategorie = $kategoriaDomyslna;
            } else {
                $kategorie = $kategorieWszystkie;
            }
            
            $GLOBALS['cache']->zapisz('Produkt_Id_' . $id . '_kategorie', array('kategorie' => $kategorie, 'kategorie_domyslne' => $kategorieDomyslne), CACHE_PRODUKTY);  
            
          } else {
            
            if ( $domyslne == false ) {
              
                 $kategorie = $WynikCache['kategorie'];
                 
              } else {
              
                 $kategorieDomyslne = $WynikCache['kategorie_domyslne'];
                 
            }
            
        }
        
        if ( $domyslne == false ) {
          
             return $kategorie;
             
          } else {
          
             return $kategorieDomyslne;
             
        }
        //
    }     
    
}

?>