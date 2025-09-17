<?php

class Wyglad {

    public $PlikiBoxy;
    public $PlikiBoxyLokalne;
    public $PlikiBoxySzablon;
    public $PlikiModuly;
    public $PlikiModulySzablon;
    public $PlikiModulyKreator;
    public $PlikiModulyLokalne;
    public $PlikiListingiLokalne;
    public $PlikiModulyStale;
    public $PlikiModulyStalePliki;
    public $PlikiTresciLokalne;
    public $StronyInformacyjne;
    public $Galerie;
    public $Formularze;
    public $KategorieArtykulow;
    public $Artykuly;
    public $Czcionki;

    public function __construct() {
        //
        // Tworzenie tablicy z plikami boxow i modulow
        //
        // pliki z boxami
        $this->PlikiBoxy = $this->pobierzPliki('boxy');
        // pliki z boxami
        $this->PlikiBoxyLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne');    
        // pliki indywidualne wygladu szablonu z boxami
        $this->PlikiBoxySzablon = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/boxy_wyglad');           
        // pliki z modulami
        $this->PlikiModuly = $this->pobierzPliki('moduly');  
        // pliki z modulami w szablonie
        $this->PlikiModulySzablon = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/moduly_wyglad');         
        // pliki z modulami kreatora w szablonie
        $this->PlikiModulyKreator = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/moduly_kreator_wyglad');         
        // pliki z modulami lokalnymi
        $this->PlikiModulyLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne'); 
        // pliki z listingami lokalnymi
        $this->PlikiListingiLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne');   
        // pliki z modulami stalymi
        $this->PlikiModulyStale = $this->pobierzPliki('moduly_stale'); 
        $this->PlikiModulyStalePliki = array();
        // pliki z tresciami lokalnymi
        $this->PlikiTresciLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/tresc');          
        //   
        // Tworzenie tablic z nazwami do menu
        //
        // strony informacyjne
        $this->StronyInformacyjne = $this->PobierzNazwyMenu('strona'); 
        // galerie
        $this->Galerie = $this->PobierzNazwyMenu('galeria');         
        // formularze
        $this->Formularze = $this->PobierzNazwyMenu('formularz'); 
        // kategorie artykulow
        $this->KategorieArtykulow = $this->PobierzNazwyMenu('kategoria');         
        // artykuly
        $this->Artykuly = $this->PobierzNazwyMenu('artykul');          
        $this->Czcionki = $this->CzcionkiSlick();          
    }
    
    // funkcja do listowania plikow z katalogow
    public function pobierzPliki( $katalog ) {
        //
        $wynik = array();
        //
        if (is_dir( $katalog )) {
            if ($dh = opendir( $katalog )) { 
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && !is_dir( $katalog . '/' . $file)) { $wynik[] = $file; }
                }                            
                closedir($dh);
            }      
        }    
        //
        return $wynik;
    }

    // funkcja zwraca boxy z lewej lub prawej kolumny
    public function KolumnaBoxu( $strona ) {
        global $WywolanyPlik;
    
        // dodatkowy warunek wyswietlania 
        $warunek = ' and tb.box_localization in (';
        if ( $GLOBALS['stronaGlowna'] == true ) {
             $warunek .= '1,2';
             $przedrostek = '_glowna';
        }
        if ( $GLOBALS['stronaGlowna'] == false ) {
             $warunek .= '1,3';
             $przedrostek = '_podstrony';
        }        
        $warunek .= ')';
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('BoxyKolumn_' . $_SESSION['domyslnyJezyk']['kod'] . '_' . $strona . $przedrostek, CACHE_WYGLAD); 
        $ByloCache = false;

        if ( !$WynikCache ) {   

            $warunek_v2 = ' and (tb.box_v2 = 0 or tb.box_v2 = 2)';
            
            if ( Wyglad::TypSzablonu() == true ) {
                 //
                 $warunek_v2 = ' AND (tb.box_v2 = 1 or tb.box_v2 = 2)';
                 //
            }                

            $zapytanie = "SELECT tb.box_id,
                                 tb.box_file,
                                 tb.box_pages_id,
                                 tb.box_code,
                                 tb.box_type,
                                 tb.box_sort,
                                 tb.box_column,
                                 tb.box_header,
                                 tb.box_theme,
                                 tb.box_theme_file,
                                 tb.box_localization,
                                 tb.box_localization_site,                                 
                                 tb.box_rwd,
                                 tb.box_rwd_resolution,
                                 td.box_title,
                                 td.box_text,
                                 pd.pages_id,
                                 pd.pages_title,
                                 pd.pages_short_text,
                                 pd.pages_text,
                                 p.nofollow,
                                 p.pages_more,
                                 p.pages_customers_group_id
                            FROM theme_box tb
                       LEFT JOIN theme_box_description td ON tb.box_id = td.box_id AND td.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages_description pd ON pd.pages_id = tb.box_pages_id AND pd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages p ON p.pages_id = tb.box_pages_id
                           WHERE tb.box_column = '" . $strona . "' AND 
                                 tb.box_status = '1'" . $warunek . " AND tb.box_rwd = '1' " . $warunek_v2 . " 
                        ORDER BY tb.box_sort";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql);

          } else {
          
            $IleRekordow = count($WynikCache);
            $ByloCache = true;
            
        }

        $DoWyswietlania = '';

        if ($IleRekordow > 0) { 
        
            $Tablica = array();
        
            if ( !$WynikCache ) {
                while ($infwg = $sql->fetch_assoc()) {
                    $Tablica[] = $infwg;
                }
                //
                $GLOBALS['cache']->zapisz('BoxyKolumn_' . $_SESSION['domyslnyJezyk']['kod'] . '_' . $strona . $przedrostek, $Tablica, CACHE_WYGLAD);
            } else {
                $Tablica = $WynikCache;
            }        

            foreach ( $Tablica as $infwg ) {
                //
                $PokazBox = true;
                //
                // jezeli wyswietlanie na podstronach i tylko wybranych stronach
                if ( $infwg['box_localization'] == 3 && !empty($infwg['box_localization_site']) && isset($WywolanyPlik) ) {
                     //
                     if ( !in_array($WywolanyPlik, explode(';', (string)$infwg['box_localization_site'])) ) {
                          $PokazBox = false;
                     }
                     //
                }                    
                 
                if ( $PokazBox == true ) {                
                
                    $box = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/boxy_wyglad/box.tp', $infwg);
                    if ($infwg['box_theme'] == '1') {
                        if (in_array( $infwg['box_theme_file'], $this->PlikiBoxySzablon )) {
                            $box = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/boxy_wyglad/' . $infwg['box_theme_file'], $infwg);
                        }
                        //
                        // dodatkowe sprawdzenie czy nie ma indywidualnego pliku dla szablonu
                    } else if (in_array( str_replace('.php', '.tp', (string)$infwg['box_file']), $this->PlikiBoxySzablon )) {
                        $box = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/boxy_wyglad/' . str_replace('.php', '.tp', (string)$infwg['box_file']), $infwg);                    
                    }
                    
                    if ($infwg['box_theme'] == '2') {
                        if (in_array( 'box_sama_tresc.tp', $this->PlikiBoxySzablon )) {
                            $box = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/boxy_wyglad/box_sama_tresc.tp', $infwg);
                        }
                    }                     
                    //
                    // rodzaj strony
                    //
                    $ZawartoscBoxu = '';
                    switch ($infwg['box_type']) {
                        case "strona":
                            // sprawdzi czy strona nie jest przypisana do grup klientow
                            $PokazStrone = false;
                            if ( $infwg['pages_customers_group_id'] == '0' ) {
                                 //
                                 $PokazStrone = true;
                                 //
                            } else if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                                 //
                                 if ( in_array( (int)$_SESSION['customers_groups_id'], explode(',', (string)$infwg['pages_customers_group_id']) ) ) {
                                      //
                                      $PokazStrone = true;
                                      //
                                 }
                                 //
                            }

                            // jezeli jest tekst skrocony             
                            if ( $PokazStrone == true ) {
                                 //
                                 if (strlen((string)$infwg['pages_short_text']) > 10) {
                                     //
                                     $ZawartoscBoxu = '<div class="FormatEdytor">' . $infwg['pages_short_text'] . '</div>';
                                     //
                                     if (strlen((string)$infwg['pages_text']) > 10) {
                                         $ZawartoscBoxu .= '<div class="StronaInfo"><a class="przycisk" ' . (($infwg['nofollow'] == 1) ? 'rel="nofollow" ' : '') . 'href="' . Seo::link_SEO( $infwg['pages_title'], $infwg['pages_id'], 'strona_informacyjna' ) . '">' . $GLOBALS['tlumacz']['PRZYCISK_PRZECZYTAJ_CALOSC'] . '</a></div>';
                                     }
                                  } else {
                                     //
                                     $ZawartoscBoxu = '<div class="FormatEdytor">' . $infwg['pages_text'] . '</div>';
                                     //
                                  }   
                                  //
                            }                                     
                            break;    
                        case "plik":
                            //
                            // sprawdza czy jest indywidlany box w szablonie
                            if (in_array( $infwg['box_file'], $this->PlikiBoxyLokalne )) {
                                //
                                if ( file_exists('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne/'.$infwg['box_file']) ) {
                                     //
                                     ob_start();
                                     require('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne/'.$infwg['box_file']);
                                     $_wynik = ob_get_contents();
                                     ob_end_clean();        
                                     $ZawartoscBoxu = $_wynik;
                                     unset($_wynik);
                                     //
                                }
                                //
                              } else if (in_array( $infwg['box_file'], $this->PlikiBoxy )) {
                                //
                                if ( file_exists('boxy/'.$infwg['box_file']) ) {
                                     //
                                     ob_start();
                                     require('boxy/'.$infwg['box_file']);
                                     $_wynik = ob_get_contents();
                                     ob_end_clean();                         
                                     $ZawartoscBoxu = $_wynik;
                                     unset($_wynik);
                                     //
                                }
                                //
                              } else {
                                //
                                $box->dodaj('__TRESC_BOXU', '... brak pliku boxu ...');
                                //                      
                            }
                            break;  
                        case "java":
                            $ZawartoscBoxu = $infwg['box_code'];
                            break;       
                        case "txt":
                            //
                            if ( strpos((string)$infwg['box_text'], '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
                                 //
                                 $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$infwg['box_text']);
                                 //
                                 if ( count($PodzielTekst) == 2 ) {
                                      //
                                      $ZawartoscBoxu = '<div class="FormatEdytor">' . Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . $infwg['box_id'] . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . $infwg['box_id'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . $infwg['box_id'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div></div>';
                                      //
                                 } else {
                                      //
                                      $ZawartoscBoxu = '<div class="FormatEdytor">' . str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$infwg['box_text']) . '<div style="clear:both"></div></div>';
                                      //
                                 }
                                 //
                            } else {
                                 //
                                 $ZawartoscBoxu = '<div class="FormatEdytor">' . $infwg['box_text'] . '<div style="clear:both"></div></div>';
                                 //
                            }
                            //
                            break;                            
                    }              
                    
                    // wyswietla box tylko jezeli ma jakas zawartosc
                    if ( !empty($ZawartoscBoxu) ) {
                    
                        // dodawanie strzalki do rozwijania przy RWD
                        if ( $infwg['box_rwd_resolution'] == 2 ) {
                            //
                            // jezeli jest opcja minimalizowania boxu to dodana do naglowka strzalke do rozwijania
                            $box->dodaj('__NAGLOWEK_BOXU', $infwg['box_title'] . '<span class="BoxRozwinZwin BoxRozwin"></span>');
                            //
                          } else {
                            //
                            $box->dodaj('__NAGLOWEK_BOXU', $infwg['box_title']);
                            //
                        }
                        
                        $box->dodaj('__TRESC_BOXU', $ZawartoscBoxu);
                        //
                        // ukrywanie w RWD przy malych rozdzielczosciach
                        if ( ($infwg['box_rwd_resolution'] == 1 || $infwg['box_rwd_resolution'] == 2) ) {
                             //
                             // jezeli jest ukrywanie boxu przy malych rozdzielczosciach to doda inna klase (caly box w div)
                             if ( $infwg['box_rwd_resolution'] == 1 ) {
                                  //
                                  if ( Wyglad::UrzadzanieMobilne() == false ) {
                                       $DoWyswietlania .= '<div class="BoxRwdUkryj">' . "\r\n" . $box->uruchom() . '</div>' . "\r\n";
                                  }
                                  //
                             }
                             // jezeli jest tylko minimalizowanie przy malych rozdzielczosciach to tylko wstawi caly box w nowy div
                             if ( $infwg['box_rwd_resolution'] == 2 ) {
                                  $DoWyswietlania .= '<div class="BoxRwd">' . "\r\n" . $box->uruchom() . '</div>' . "\r\n";
                             }                         
                             //
                           } else {
                             //
                             $DoWyswietlania .= $box->uruchom();
                             //
                        }
                        //
                    }
                    //
                }
                //
                unset($PokazBox);
                //
            }
            //
        }
        
        unset($WynikCache, $przedrostek, $warunek);
        
        if ( $ByloCache == false ) {  
            $GLOBALS['db']->close_query($sql); 
            unset($zapytanie, $infwg);
        }
        
        unset($ByloCache, $Tablica, $IleRekordow, $box);

        return $DoWyswietlania;

    }  
    
    // funkcja zwraca moduly z czesci srodkowej sklepu
    // miejsce wyswietlania
    // 1 - wszedzie (na kazdej podstronie)
    // 2 - tylko strona glowna
    // 3 - tylko podstrony
    public function SrodekSklepu( $pozycjaModulow = 'srodek', $miejsceWyswietlania = array(2), $podstrony = '', $TylkoModulId = 0, $Status = true, $TylkoKreator = false ) {
        global $SzerokoscSrodek, $WywolanyPlik;
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('SrodekSklepu_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_WYGLAD); 
        $ByloCache = false;
        
        if ( $TylkoModulId > 0 && $Status == false ) {
             //
             $WynikCache = false;
             //
        }

        if ( !$WynikCache ) {   

            $warunek = ' and (m.modul_v2 = 0 or m.modul_v2 = 2)';
            
            if ( Wyglad::TypSzablonu() == true ) {
                 //
                 $warunek = ' AND (m.modul_v2 = 1 or m.modul_v2 = 2)';
                 //
            }        
            
            if ( defined('TYLKO_MODULY') ) {
                 //
                 if ( is_array(TYLKO_MODULY) && count(TYLKO_MODULY) > 0 ) {
                      //
                      $warunek .= ' AND m.modul_id IN (' . implode(',', TYLKO_MODULY) . ')';
                      //
                 }
                 //
            }
            
            if ( $TylkoKreator == true ) {
                 //
                 $warunek .= " AND m.modul_type = 'kreator'";
                 //
            }

            $zapytanie = "SELECT m.modul_id as IdModulu,
                                 m.modul_file,
                                 m.modul_pages_id,
                                 m.modul_code,
                                 m.modul_type,
                                 m.modul_sort,
                                 m.modul_header,
                                 m.modul_header as NaglowekModulu,
                                 m.modul_header_link,
                                 m.modul_theme,
                                 m.modul_theme_file,  
                                 m.modul_rwd,
                                 m.modul_rwd_resolution,    
                                 m.modul_wcag_color,
                                 m.modul_localization,
                                 m.modul_localization_site,
                                 m.modul_localization_link,
                                 m.modul_localization_position,
                                 m.modul_position,
                                 m.modul_width as SzerokoscModulu,                                
                                 m.modul_margines_top,
                                 m.modul_margines_bottom,
                                 m.modul_margines_left,
                                 m.modul_margines_right,  
                                 m.modul_padding_top,
                                 m.modul_padding_bottom,
                                 m.modul_padding_left,
                                 m.modul_padding_right,                                    
                                 m.modul_background_type,
                                 m.modul_background_width as SzerokoscTla,
                                 m.modul_background_value,
                                 m.modul_background_repeat,    
                                 m.modul_background_attachment,
                                 m.modul_background_image_width,
                                 m.modul_align_column,
                                 m.modul_column_array,
                                 m.modul_preload,
                                 md.modul_title,
                                 md.modul_title_line_2,
                                 md.modul_title_line_3,
                                 md.modul_title_description,
                                 md.modul_text,
                                 pd.pages_id,
                                 pd.pages_title,
                                 pd.pages_short_text,
                                 pd.pages_text,
                                 p.nofollow,
                                 p.pages_more,
                                 p.pages_customers_group_id
                            FROM theme_modules m
                       LEFT JOIN theme_modules_description md ON m.modul_id = md.modul_id AND md.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages_description pd ON pd.pages_id = m.modul_pages_id AND pd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages p ON p.pages_id = m.modul_pages_id
                           WHERE " . (($Status == true) ? "m.modul_status = '1' AND " : "") . " m.modul_rwd = '1' " . $warunek . "
                        ORDER BY m.modul_sort";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql);

          } else {
          
            $IleRekordow = count($WynikCache);
            $ByloCache = true;
            
        }            

        $DoWyswietlania = '';

        if ($IleRekordow > 0) { 
        
            $Tablica = array();
        
            if ( !$WynikCache ) {
                while ($infwg = $sql->fetch_assoc()) {
                    $Tablica[] = $infwg;
                }
                //
                if ( $TylkoModulId == 0 && $Status == true ) {
                     //
                     $GLOBALS['cache']->zapisz('SrodekSklepu_' . $_SESSION['domyslnyJezyk']['kod'], $Tablica, CACHE_WYGLAD);
                     //
                }
            } else {
                $Tablica = $WynikCache;
            }        
            
            if ( defined('TYLKO_MODULY') ) {
                 //
                 $TablicaTmp = array();
                 //
                 if ( is_array(TYLKO_MODULY) && count(TYLKO_MODULY) > 0 ) {
                      //
                      foreach ( TYLKO_MODULY as $Tmp ) {
                          //
                          foreach ( $Tablica as $infwg ) {
                              //
                              if ( $Tmp == $infwg['IdModulu'] ) {
                                   //
                                   $TablicaTmp[] = $infwg;
                                   //
                              }
                          }
                          //
                      }
                      //
                 }
                 //
                 $Tablica = $TablicaTmp;
                 unset($TablicaTmp);
                 //
            }            

            foreach ( $Tablica as $infwg ) {
                //
                if ( ( $TylkoModulId > 0 && $TylkoModulId == $infwg['IdModulu'] ) || $TylkoModulId == 0 ) {

                    if ( ($infwg['modul_position'] == $pozycjaModulow && in_array( $infwg['modul_localization'], $miejsceWyswietlania ) && (( $podstrony != '' ) ? $infwg['modul_localization_position'] == $podstrony : true ) ) || $TylkoModulId ) {
                        //

                        // tworzenie tymczasowej talicy konfiguracji kolumn na potrzeby modulu w formie zakladek
                        $TablicaKonfiguracjiKolumn = array();
                        if ( isset($infwg['modul_column_array']) ) {
                            $TablicaKonfiguracjiKolumn = @unserialize($infwg['modul_column_array']);
                        }
                        //

                        $PokazModul = true;
                        $WyswietlModul = true;
                        //
                        // jezeli wyswietlanie na podstronach i tylko wybranych stronach
                        if ( $infwg['modul_localization'] == 3 && !empty($infwg['modul_localization_site']) && isset($WywolanyPlik) ) {
                             //
                             if ( !in_array($WywolanyPlik, explode(';', (string)$infwg['modul_localization_site'])) ) {
                                  $PokazModul = false;
                             }
                             //
                        }     
                        // jezeli wyswietlanie na podstronach w formie linkow
                        if ( $infwg['modul_localization'] == 4 && !empty($infwg['modul_localization_link']) ) {
                             //
                             $podzielLink = explode('?', (string)$_SERVER['REQUEST_URI'], 2);
                             //
                             if ( substr((string)$podzielLink[0], 0, 1) == '/' ) {
                                  $podzielLink[0] = substr((string)$podzielLink[0], 1);
                             }
                             //
                             $podzielSlash = explode('/', (string)$podzielLink[0]);              
                             //
                             if ( isset($podzielSlash[0]) ) {
                                  //
                                  if ( !in_array($podzielSlash[0], preg_split('/\n|\r\n?/', (string)$infwg['modul_localization_link'])) ) {
                                       $PokazModul = false;
                                  }
                                  //
                             }
                             //
                        }              

                        // szerokosc tla
                        if ( $infwg['SzerokoscTla'] == 'szerokosc_sklep' ) {
                             $infwg['SzerokoscTla'] = 'SzerokoscSklepu';
                        } else {
                             $infwg['SzerokoscTla'] = 'SzerokoscEkranu';
                        }
                        
                        // szerokosc modulu
                        if ( $infwg['SzerokoscModulu'] == 'szerokosc_sklep' ) {
                             $infwg['SzerokoscModulu'] = 'SzerokoscSklepu';
                        } else {
                             $infwg['SzerokoscModulu'] = 'SzerokoscEkranu';
                        }  
                        
                        // dla modulow w tresci - zeby nie bylo klasy .Strona
                        if ( $Status == false ) {
                             $infwg['SzerokoscModulu'] = 'SzerokoscEkranu';
                             $infwg['SzerokoscTla'] = 'SzerokoscEkranu';
                        }
                        
                        // naglowek modulu
                        if ( $infwg['NaglowekModulu'] == '1' ) {
                             $infwg['NaglowekModulu'] = 'tak';
                        } else {
                             $infwg['NaglowekModulu'] = 'nie';
                        }
                        
                        // dodatkowe css
                        $infwg['DodatkoweCss'] = '';
                        $infwg['DodatkoweMarginesy'] = '';
                        
                        // dodatkowe marginesy modulu
                        $DodatkoweMarginesy = array();
                        if ( $infwg['modul_margines_top'] > 0 ) {
                             $DodatkoweMarginesy[] = 'margin-top:' . $infwg['modul_margines_top'] . 'px';
                        }
                        if ( $infwg['modul_margines_bottom'] > 0 ) {
                             $DodatkoweMarginesy[] = 'margin-bottom:' . $infwg['modul_margines_bottom'] . 'px';
                        }
                        if ( $infwg['modul_margines_left'] > 0 ) {
                             $DodatkoweMarginesy[] = 'margin-left:' . $infwg['modul_margines_left'] . 'px';
                        }
                        if ( $infwg['modul_margines_right'] > 0 ) {
                             $DodatkoweMarginesy[] = 'margin-right:' . $infwg['modul_margines_right'] . 'px';
                        }    

                        // dodatkowe odstepy modulu
                        $DodatkoweCss = array();
                        if ( $infwg['modul_padding_top'] > 0 ) {
                             $DodatkoweCss[] = 'padding-top:' . $infwg['modul_padding_top'] . 'px';
                        }
                        if ( $infwg['modul_padding_bottom'] > 0 ) {
                             $DodatkoweCss[] = 'padding-bottom:' . $infwg['modul_padding_bottom'] . 'px';
                        }
                        if ( $infwg['modul_padding_left'] > 0 ) {
                             $DodatkoweCss[] = 'padding-left:' . $infwg['modul_padding_left'] . 'px';
                        }
                        if ( $infwg['modul_padding_right'] > 0 ) {
                             $DodatkoweCss[] = 'padding-right:' . $infwg['modul_padding_right'] . 'px';
                        }                       

                        // tlo modulu
                        if ( $infwg['modul_background_type'] != 'brak' ) {
                             //
                             if ( $infwg['modul_background_type'] == 'kolor' && $infwg['modul_background_value'] != '' ) {
                                  $DodatkoweCss[] = 'background-color:#' . $infwg['modul_background_value'];
                             }
                             //
                             if ( $infwg['modul_background_type'] == 'obrazek' ) {
                                  $DodatkoweCss[] = 'background:url(\'' . KATALOG_ZDJEC . '/' . $infwg['modul_background_value'] . '\') ' . $infwg['modul_background_repeat'];
                                  //
                                  if ( $infwg['modul_background_image_width'] == '100_szerokosc' ) {
                                       $DodatkoweCss[] = 'background-size:100% auto';
                                  }
                                  if ( $infwg['modul_background_image_width'] == '100_wysokosc' ) {
                                       $DodatkoweCss[] = 'background-size:auto 100%';
                                  }
                                  if ( $infwg['modul_background_image_width'] == '100_szerokosc_wysokosc' ) {
                                       $DodatkoweCss[] = 'background-size:100% 100%';
                                  }      
                                  if ( $infwg['modul_background_image_width'] == 'cover' ) {
                                       $DodatkoweCss[] = 'background-size:cover';
                                  } 
                                  //
                                  if ( $infwg['modul_background_attachment'] == 'fixed' ) {
                                       $DodatkoweCss[] = 'background-attachment:fixed';
                                  }                                    
                                  //
                             }                         
                             //
                        }
     
                        if ( $PokazModul == true ) {
                            //
                            // wczytanie pliku wygladu dla modulu - oprocz kreatora
                            if ( $infwg['modul_type'] != 'kreator' ) {
                                 //
                                 $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_wyglad/modul.tp', $infwg);
                                 if ($infwg['modul_theme'] == '1') {
                                     //
                                     // podmiana banner_animacja.tp na modul_sama_tresc.tp
                                     if ( $infwg['modul_theme_file'] == 'banner_animacja.tp' ) {
                                          $infwg['modul_theme_file'] = 'modul_sama_tresc.tp';
                                     }
                                     //
                                     if (in_array( $infwg['modul_theme_file'], $this->PlikiModulySzablon )) {
                                         $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_wyglad/' . $infwg['modul_theme_file'], $infwg);
                                     }
                                 } 
                                 if ($infwg['modul_theme'] == '2') {
                                     //
                                     if (in_array( 'modul_sama_tresc.tp', $this->PlikiModulySzablon )) {
                                         $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_wyglad/modul_sama_tresc.tp', $infwg);
                                     }
                                     //
                                 }
                                 //
                            } else {
                                 //
                                 if ( Wyglad::TypSzablonu() == true ) {
                                      //
                                      $StylCss = '';
                                      //
                                      // dodatkowy kod css dla modulu
                                      if ( isset($TablicaKonfiguracjiKolumn['css_caly_modul']) && $TablicaKonfiguracjiKolumn['css_caly_modul'] != '' ) {
                                           //
                                           $StylCss .= str_replace('{KLASA_CSS_MODUL}', '.ModulId-' . $infwg['IdModulu'], (string)$TablicaKonfiguracjiKolumn['css_caly_modul']);
                                           //
                                      }                       
                                      //
                                      $GLOBALS['css'] .= $StylCss;
                                      //
                                      unset($StylCss);      

                                      // opcje preloader modulow
                                      if ( !isset($_POST['pobierz']) ) {
                                           //
                                           // jezeli jest aktywny preloader
                                           if ( (int)$infwg['modul_preload'] == 1 ) {
                                                //
                                                $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_caly_modul_preload.tp', $infwg);
                                                //
                                           } else {
                                                // warunek na potrzeby modulu w formie zakladek
                                                if ( (isset($TablicaKonfiguracjiKolumn['proporcje_2_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_2_kolumny'] == 'zakladki') || (isset($TablicaKonfiguracjiKolumn['proporcje_3_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_3_kolumny'] == 'zakladki') ) {
                                                    $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_caly_modul_zakladki.tp', $infwg);
                                                } else {
                                                    $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_caly_modul.tp', $infwg);
                                                }
                                                //
                                           }
                                           //
                                      } else {
                                           //
                                           // warunek na potrzeby modulu w formie zakladek
                                           if ( (isset($TablicaKonfiguracjiKolumn['proporcje_2_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_2_kolumny'] == 'zakladki') || (isset($TablicaKonfiguracjiKolumn['proporcje_3_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_3_kolumny'] == 'zakladki') ) {
                                                $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_caly_modul_zakladki.tp', $infwg);
                                           } else {
                                                $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_caly_modul.tp', $infwg);
                                           }
                                           //
                                      }
                                      //
                                      if (in_array( 'kreator_caly_modul_' . $infwg['IdModulu'] . '.tp', $this->PlikiModulyKreator )) {
                                          $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_caly_modul_' . $infwg['IdModulu'] . '.tp', $infwg);
                                      }       
                                      if ($infwg['modul_theme'] == '1') {
                                          if (in_array( $infwg['modul_theme_file'], $this->PlikiModulyKreator )) {
                                              $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $infwg['modul_theme_file'], $infwg);
                                          }
                                      }                                      
                                      //
                                 } else {
                                      //
                                      $modul = new Szablony();
                                      //
                                 }
                                 //
                            }
                            
                            // rodzaj strony
                            //
                            $ZawartoscModulu = '';
                            switch ($infwg['modul_type']) {
                                case "strona":
                                    // sprawdzi czy strona nie jest przypisana do grup klientow
                                    $PokazStrone = false;
                                    if ( $infwg['pages_customers_group_id'] == '0' ) {
                                         //
                                         $PokazStrone = true;
                                         //
                                    } else if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                                         //
                                         if ( in_array( (int)$_SESSION['customers_groups_id'], explode(',', (string)$infwg['pages_customers_group_id']) ) ) {
                                              //
                                              $PokazStrone = true;
                                              //
                                         }
                                         //
                                    }

                                    // jezeli jest tekst skrocony             
                                    if ( $PokazStrone == true ) {
                                         //
                                         if (strlen((string)$infwg['pages_short_text']) > 10) {
                                             //
                                             $ZawartoscModulu = '<div class="FormatEdytor">' . $infwg['pages_short_text'] . '<div style="clear:both"></div></div>';
                                             //
                                             if (strlen((string)$infwg['pages_text']) > 10) {
                                                 if ( $infwg['pages_more'] == '0' ) {
                                                    $ZawartoscModulu .= '<div class="StronaInfo"><a class="przycisk" ' . (($infwg['nofollow'] == 1) ? 'rel="nofollow" ' : '') . 'href="' . Seo::link_SEO( $infwg['pages_title'], $infwg['pages_id'], 'strona_informacyjna' ) . '">' . $GLOBALS['tlumacz']['PRZYCISK_PRZECZYTAJ_CALOSC'] . '</a></div>';
                                                 } else {
                                                    $ZawartoscModulu .= '<div class="StronaInfoRozwiniecie" id="StronaInfoText-' . $infwg['pages_id'] . '"><div class="StronaInfoRozwiniecieTresc">' . $infwg['pages_text'] . '</div></div><div id="StronaInfoWiecej-' . $infwg['pages_id'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="'.$infwg['pages_id'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div>';
                                                 }
                                             }
                                          } else {
                                             //
                                             $ZawartoscModulu = '<div class="FormatEdytor">' . $infwg['pages_text'] . '<div style="clear:both"></div></div>';
                                             //
                                          }   
                                          //
                                    }                                     
                                    break; 
                                case "plik":
                                    //
                                    // sprawdza czy jest indywidlany box w szablonie
                                    if (in_array( $infwg['modul_file'], $this->PlikiModulyLokalne )) {
                                        //
                                        if ( file_exists('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne/'.$infwg['modul_file']) ) {
                                             //
                                             ob_start();
                                             require('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne/'.$infwg['modul_file']);
                                             $_wynik = ob_get_contents();
                                             ob_end_clean();        
                                             $ZawartoscModulu = $_wynik;
                                             unset($_wynik);
                                             //
                                        }
                                        //
                                      } else if (in_array( $infwg['modul_file'], $this->PlikiModuly )) {
                                        //
                                        if ( file_exists('moduly/'.$infwg['modul_file']) ) {
                                             //                                
                                             ob_start();
                                             require('moduly/'.$infwg['modul_file']);
                                             $_wynik = ob_get_contents();
                                             ob_end_clean();                         
                                             $ZawartoscModulu = $_wynik;
                                             unset($_wynik);
                                             //
                                        }
                                        //
                                      } else {
                                        //
                                        $modul->dodaj('__TRESC_MODULU', '... brak pliku modułu ...');
                                        //                      
                                    }
                                    break;  
                                case "java":
                                    $ZawartoscModulu = $infwg['modul_code'];
                                    break;   
                                case "txt":
                                    //
                                    if ( strpos((string)$infwg['modul_text'], '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
                                         //
                                         $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$infwg['modul_text']);
                                         //
                                         if ( count($PodzielTekst) == 2 ) {
                                              //
                                              $ZawartoscModulu = '<div class="FormatEdytor">' . Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . $infwg['IdModulu'] . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . $infwg['IdModulu'] . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . $infwg['IdModulu'] . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div></div>';
                                              //
                                         } else {
                                              //
                                              $ZawartoscModulu = '<div class="FormatEdytor">' . str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$infwg['modul_text']) . '<div style="clear:both"></div></div>';
                                              //
                                         }
                                         //
                                    } else {
                                         //
                                         $ZawartoscModulu = '<div class="FormatEdytor">' . $infwg['modul_text'] . '<div style="clear:both"></div></div>';
                                         //
                                    }
                                    //
                                    break;  
                                case "kreator":
                                    if ( Wyglad::TypSzablonu() == true ) {
                                         $ZawartoscModulu = $this->ModulyKreatora($infwg);
                                    }
                            }      
 
                            // wyswietla modul tylko jezeli ma jakas zawartosc - jezeli nie jest to kreator
                            if ( !empty($ZawartoscModulu) && !is_array($ZawartoscModulu) ) {
                                 //
                                 $NazwaModulu = $infwg['modul_title'];
                                 //
                                 // dodatkowa nazwa nr 2
                                 if ( !empty($infwg['modul_title_line_2']) && Wyglad::TypSzablonu() == true ) {
                                      //
                                      $NazwaModulu .= '<i>' . $infwg['modul_title_line_2'] . '</i>';
                                      //
                                 }
                                 //
                                 // dodatkowa nazwa nr 3
                                 if ( !empty($infwg['modul_title_line_3']) && Wyglad::TypSzablonu() == true ) {
                                      //
                                      $NazwaModulu .= '<i>' . $infwg['modul_title_line_3'] . '</i>';
                                      //
                                 }                       
                                 //                             
                                 if ( $infwg['modul_header_link'] != '' ) {
                                      $modul->dodaj('__NAGLOWEK_MODULU', '<a href="' . $infwg['modul_header_link'] . '">' . $NazwaModulu . '</a>');
                                 } else {
                                      $modul->dodaj('__NAGLOWEK_MODULU', $NazwaModulu);
                                 }                          
                                 $modul->dodaj('__TRESC_MODULU', $ZawartoscModulu);
                                 //
                                 unset($NazwaModulu);
                                 //
                            } else {
                                 //
                                 if ( $infwg['modul_type'] != 'kreator' ) {
                                      //
                                      $WyswietlModul = false;
                                      //
                                 }
                                 //
                            }
                            
                            // opis modulu
                            $modul->dodaj('__OPIS_MODULU', '');
                            if ( strlen((string)$infwg['modul_title_description']) > 10 && Wyglad::TypSzablonu() == true ) {
                                 $modul->dodaj('__OPIS_MODULU', '<div class="OpisModulu FormatEdytor">' . $infwg['modul_title_description'] . '<div class="cl"></div></div>');
                            }                      

                            // id modulu
                            $modul->dodaj('__ID_MODULU', $infwg['IdModulu']);
                            
                            // dodatkowy css
                            $modul->dodaj('__CSS_DODATKOWY', '');    
                            if ( count($DodatkoweCss) > 0 ) {
                                 $modul->dodaj('__CSS_DODATKOWY', 'style="' . implode(';', (array)$DodatkoweCss) . '"');    
                            }
                            unset($DodatkoweCss);
                             
                            // dodatkowe marginesy
                            $modul->dodaj('__CSS_DODATKOWE_MARGINESY', '');
                            if ( count($DodatkoweMarginesy) > 0 ) {
                                 $modul->dodaj('__CSS_DODATKOWE_MARGINESY', 'style="' . implode(';', (array)$DodatkoweMarginesy) . '"');
                            }
                            unset($DodatkoweMarginesy);    

                            // kreator - wyrownanie kolumn
                            $modul->dodaj('__CSS_WYROWNANIE_KOLUMN', '');
                            if ( $infwg['modul_align_column'] == 'srodek' ) {
                                 $modul->dodaj('__CSS_WYROWNANIE_KOLUMN', 'style="align-items:center"');
                            }
                            if ( $infwg['modul_align_column'] == 'gora' ) {
                                 $modul->dodaj('__CSS_WYROWNANIE_KOLUMN', 'style="align-items:flex-start"');
                            } 
                            if ( $infwg['modul_align_column'] == 'dol' ) {
                                 $modul->dodaj('__CSS_WYROWNANIE_KOLUMN', 'style="align-items:flex-end"');
                            }                           

                            // jezeli sa to moduly z kreatora
                            if ( is_array($ZawartoscModulu) && Wyglad::TypSzablonu() == true ) { 
                                 //
                                 $Konfig = $ZawartoscModulu;
                                 //
                                 $modul->dodaj('__ILOSC_KOLUMN', $Konfig['ile_kolumn']);
                                 //                             
                                 // proporcje kolumn
                                 $modul->dodaj('__KLASA_CSS_PROPORCJE_KOLUMN', '');
                                 //
                                 if ( (int)$Konfig['ile_kolumn'] > 1 ) {
                                       //
                                       if ( isset($Konfig['proporcje_' . (int)$Konfig['ile_kolumn'] . '_kolumny']) ) {
                                            //
                                            $modul->dodaj('__KLASA_CSS_PROPORCJE_KOLUMN', ' ProporcjeKolumn-' . $Konfig['proporcje_' . (int)$Konfig['ile_kolumn'] . '_kolumny']);
                                            //
                                       }
                                       //

                                     // na potrzeby modulu w formie zakladek - dodaje naglowek kolumny jako zakladke
                                     if ( (isset($TablicaKonfiguracjiKolumn['proporcje_2_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_2_kolumny'] == 'zakladki') || (isset($TablicaKonfiguracjiKolumn['proporcje_3_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_3_kolumny'] == 'zakladki') ) {
                                         $modul->dodaj('__NAGLOWEK_KOLUMNA_PIERWSZA', '<div class="ZakladkaNaglowekModul ZakladkaNaglowekModulAktywna">'.$Konfig['nazwa_kolumna_1']['pierwsza'].'</div>');
                                         $modul->dodaj('__NAGLOWEK_KOLUMNA_DRUGA', '');
                                         $modul->dodaj('__NAGLOWEK_KOLUMNA_TRZECIA', '');
                                         //
                                         if ( $Konfig['ile_kolumn'] > 1 ) {
                                              $modul->dodaj('__NAGLOWEK_KOLUMNA_DRUGA', '<div class="ZakladkaNaglowekModul">'.$Konfig['nazwa_kolumna_1']['druga'].'</div>');
                                         }
                                         if ( $Konfig['ile_kolumn'] > 2 ) {
                                              $modul->dodaj('__NAGLOWEK_KOLUMNA_TRZECIA', '<div class="ZakladkaNaglowekModul">'.$Konfig['nazwa_kolumna_1']['trzecia'].'</div>');
                                         }
                                     }
                                     //
                                 }
                                 //
                                 // na potrzeby modulu w formie zakladek - dodaje naglowek kolumny jako zakladke
                                 if ( (isset($TablicaKonfiguracjiKolumn['proporcje_2_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_2_kolumny'] == 'zakladki') || (isset($TablicaKonfiguracjiKolumn['proporcje_3_kolumny']) && $TablicaKonfiguracjiKolumn['proporcje_3_kolumny'] == 'zakladki') ) {
                                     $modul->dodaj('__TRESC_KOLUMNA_PIERWSZA', '<div class="ZakladkaTrescModul AnimacjaFade ZakladkaWidoczna ZakladkaTrescModulAktywna">'.$Konfig['kolumna_pierwsza'].'</div>');
                                     $modul->dodaj('__TRESC_KOLUMNA_DRUGA', '');
                                     $modul->dodaj('__TRESC_KOLUMNA_TRZECIA', '');
                                     //
                                     if ( $Konfig['ile_kolumn'] > 1 ) {
                                          $modul->dodaj('__TRESC_KOLUMNA_DRUGA', '<div class="ZakladkaTrescModul AnimacjaFade">'.$Konfig['kolumna_druga'].'</div>');
                                     }
                                     if ( $Konfig['ile_kolumn'] > 2 ) {
                                          $modul->dodaj('__TRESC_KOLUMNA_TRZECIA', '<div class="ZakladkaTrescModul AnimacjaFade">'.$Konfig['kolumna_trzecia'].'</div>');
                                     }
                                 } else {
                                     $modul->dodaj('__TRESC_KOLUMNA_PIERWSZA', $Konfig['kolumna_pierwsza']);
                                     $modul->dodaj('__TRESC_KOLUMNA_DRUGA', '');
                                     $modul->dodaj('__TRESC_KOLUMNA_TRZECIA', '');
                                     //
                                     if ( $Konfig['ile_kolumn'] > 1 ) {
                                          $modul->dodaj('__TRESC_KOLUMNA_DRUGA', $Konfig['kolumna_druga']);
                                     }
                                     if ( $Konfig['ile_kolumn'] > 2 ) {
                                          $modul->dodaj('__TRESC_KOLUMNA_TRZECIA', $Konfig['kolumna_trzecia']);
                                     }
                                 }
                                 //
                                 unset($Konfig);
                                 //
                                 // dodatkowy kod js dla modulu
                                 if ( isset($TablicaKonfiguracjiKolumn['js_caly_modul']) && $TablicaKonfiguracjiKolumn['js_caly_modul'] != '' ) {
                                      //
                                      $modul->dodaj_dodatkowo('__TRESC_KOLUMNA_TRZECIA', jsMin::minify('<script>' . str_replace('{KLASA_CSS_MODUL}', '.ModulId-' . $infwg['IdModulu'], (string)$TablicaKonfiguracjiKolumn['js_caly_modul']) . '</script>'));
                                      //
                                 }  
                                 //
                            }
                            //
                            if ( $WyswietlModul == true ) {
                                 //
                                 $UkryjWcag = false;
                                 //
                                 // wyswietlanie tryb ciemny/jasny
                                 if ( $infwg['modul_wcag_color'] == 1 ) { // tylko tryb jasny
                                      //
                                      if ( isset($_COOKIE['wcagk']) && $_COOKIE['wcagk'] == '1' && WCAG_KONTRAST == 'tak' ) {
                                           //
                                           $UkryjWcag = true;                     
                                           //
                                      }
                                      //
                                 }     
                                 if ( $infwg['modul_wcag_color'] == 2 ) { // tylo tryb ciemny
                                      //
                                      if ( isset($_COOKIE['wcagk']) && $_COOKIE['wcagk'] == '1' && WCAG_KONTRAST == 'tak' ) {
                                           //
                                           $UkryjWcag = false;                     
                                           //
                                      } else {
                                           //
                                           $UkryjWcag = true;
                                           //
                                      }
                                      //
                                 }                                     
                                 //
                                 if ( $UkryjWcag == false ) {
                                   
                                      // ukrywanie w RWD przy malych rozdzielczosciach
                                      if ( $infwg['modul_rwd_resolution'] == 1 ) {
                                           //
                                           if ( Wyglad::UrzadzanieMobilne() == false ) {
                                                //
                                                // jezeli jest ukrywanie modulu przy malych rozdzielczosciach to doda inna klase (caly modul w div)
                                                $DoWyswietlania .= '<div class="ModulRwdUkryj">' . "\r\n" . $modul->uruchom() . '</div>' . "\r\n";                       
                                                //
                                           }
                                            //
                                      }
                                      // widoczny tylko na urzadzeniach mobilnych
                                      if ( $infwg['modul_rwd_resolution'] == 2 ) {
                                           //
                                           if ( Wyglad::UrzadzanieMobilne() == false ) {
                                                //
                                                // jezeli jest ukrywanie modulu przy malych rozdzielczosciach to doda inna klase (caly modul w div)
                                                $DoWyswietlania .= '<div class="ModulMobileRwdWyswietl">' . "\r\n" . $modul->uruchom() . '</div>' . "\r\n";                       
                                                //
                                           } else {
                                                //
                                                $DoWyswietlania .= $modul->uruchom();
                                                //
                                           }
                                           //
                                      }                              
                                      if ( $infwg['modul_rwd_resolution'] == 0 ) {
                                           //
                                           $DoWyswietlania .= $modul->uruchom();
                                           //
                                      }
                                     
                                 }

                            }
                            //
                        }
                        //
                        unset($PokazModul, $WyswietlModul);
                        //
                    }
                    //
                    
                }
                //
            }
            //
        }

        unset($WynikCache);
        
        if ( $ByloCache == false ) {  
            $GLOBALS['db']->close_query($sql); 
            unset($zapytanie, $infwg);
        }
        
        unset($ByloCache, $Tablica, $IleRekordow, $box);

        return $DoWyswietlania;

    }  

    // generowanie modulow kreatora
    public function ModulyKreatora( $infwg ) {
      
        global $filtr;

        $Wynik = array();
        $Tablica = @unserialize($infwg['modul_column_array']);
        //
        if ( is_array($Tablica) ) {
             //
             if ($Tablica['ile_kolumn'] == 1 ) {
                 $KolumnyNazwy = array('pierwsza');
             }
             if ($Tablica['ile_kolumn'] == 2 ) {
                 $KolumnyNazwy = array('pierwsza', 'druga');
             }
             if ($Tablica['ile_kolumn'] == 3 ) {
                 $KolumnyNazwy = array('pierwsza', 'druga', 'trzecia');
             }                          
             //
             $Wynik = $Tablica;
             //
             $t = 1;
             foreach ( $KolumnyNazwy as $Nr ) {
                  //
                  $Konfig = $infwg;
                  //
                  if ( $Tablica['naglowek_kolumna'][$Nr] == 1 ) {
                       //
                       $Konfig['NaglowekKolumny'] = 'tak';
                       //
                  } else {
                       //
                       $Konfig['NaglowekKolumny'] = 'nie';
                       //
                  }
                  //

                  // na potrzeby modulu w formie zakladek - jezeli ma byc zakladka to zamienia modul kolumny na sama tresc
                  if ( ( isset($Tablica['ile_kolumn'] ) && ($Tablica['ile_kolumn'] == '2' || $Tablica['ile_kolumn'] == '3') ) && ( (isset($Tablica['proporcje_2_kolumny']) && $Tablica['proporcje_2_kolumny'] == 'zakladki') || ( isset($Tablica['proporcje_3_kolumny']) && $Tablica['proporcje_3_kolumny'] == 'zakladki' )) ) {
                    $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_kolumna_sama_tresc.tp', $Konfig);
                  } else {
                    $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_kolumna.tp', $Konfig);
                  }
                  if ($Tablica['kolumna_wyglad_kolumna'][$Nr] == '1') {
                     if (in_array( $Tablica['plik_wyglad_kolumna'][$Nr], $this->PlikiModulyKreator )) {
                         $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_wyglad_kolumna'][$Nr], $Konfig);
                     }
                  } 
                  if ($Tablica['kolumna_wyglad_kolumna'][$Nr] == '2') {
                     if (in_array( 'kreator_kolumna_sama_tresc.tp', $this->PlikiModulyKreator )) {
                         $modul = new Szablony('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/kreator_kolumna_sama_tresc.tp', $Konfig);
                     }
                  }             
                  
                  // dodatkowa klasa dla animacji
                  $modul->dodaj('__ANIMACJA_STRZALKI', '');      

                  if ( $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' && $Tablica['animacja_strzalki_kolumna'][$Nr] == 'tak' && $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr] == 'tak' && $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr] == 'tytul' ) {
                       $modul->dodaj('__ANIMACJA_STRZALKI', ' AnimacjaStrzalki');                  
                  }

                  // nr kolumny
                  $modul->dodaj('__NR_KOLUMNY', $t);
                  
                  // id modulu
                  $modul->dodaj('__ID_MODULU', $infwg['IdModulu']);
                  
                  // nazwa kolumny
                  if ( isset($Tablica['nazwa_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) ) {
                       //
                       $NazwaKolumny = '<span' . ((isset($Tablica['kolor_naglowek_kolumna'][$Nr]) && $Tablica['kolor_naglowek_kolumna'][$Nr] != '') ? ' style="color:#' . $Tablica['kolor_naglowek_kolumna'][$Nr] . '"' : '') . '>'.$Tablica['nazwa_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr].'</span>';
                       //
                       // dodatkowa nazwa nr 2
                       if ( isset($Tablica['nazwa_druga_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) && !empty($Tablica['nazwa_druga_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) ) {
                            //
                            $NazwaKolumny .= '<i>' . $Tablica['nazwa_druga_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr] . '</i>';
                            //
                       }
                       //
                       // dodatkowa nazwa nr 3
                       if ( isset($Tablica['nazwa_trzecia_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) && !empty($Tablica['nazwa_trzecia_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) ) {
                            //
                            $NazwaKolumny .= '<i>' . $Tablica['nazwa_trzecia_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr] . '</i>';
                            //
                       }                       
                       //
                       if ( $Tablica['naglowek_link_kolumna'][$Nr] != '' ) {
                            $modul->dodaj('__NAGLOWEK_KOLUMNY', '<a href="' . $Tablica['naglowek_link_kolumna'][$Nr] . '">' . $NazwaKolumny . '</a>');
                       } else {
                            $modul->dodaj('__NAGLOWEK_KOLUMNY', $NazwaKolumny);
                       } 
                       //
                       unset($NazwaKolumny);
                       //
                  } else {
                       //
                       $modul->dodaj('__NAGLOWEK_KOLUMNY', '');
                       //
                  }
                  //
                  
                  // opis kolumny
                  $modul->dodaj('__OPIS_KOLUMNY', '');
                  if ( isset($Tablica['opis_nazwy_kolumny_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) ) {
                       //
                       if ( strlen((string)$Tablica['opis_nazwy_kolumny_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) > 10 ) {
                            $modul->dodaj('__OPIS_KOLUMNY', '<div class="OpisKolumnyModulu FormatEdytor">' . $Tablica['opis_nazwy_kolumny_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr] . '<div class="cl"></div></div>');
                       }
                       //
                  }
                  
                  $ZawartoscKolumny = '';

                  switch ($Tablica['dane_kolumna'][$Nr]) {
                        
                      case "opis":
                           //
                           $Txt = '';
                           //
                           if ( isset($Tablica['opis_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr]) ) {
                                $Txt = $Tablica['opis_kolumna_' . (int)$_SESSION['domyslnyJezyk']['id']][$Nr];
                           }
                           //
                          //
                           if ( strpos((string)$Txt, '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
                                //
                                $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$Txt);
                                //
                                if ( count($PodzielTekst) == 2 ) {
                                     //
                                     $ZawartoscKolumny = '<div class="FormatEdytor">' . Funkcje::TrimBr($PodzielTekst[0]) . '</div><div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . $infwg['IdModulu'] . '-' . $Nr . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . $infwg['IdModulu'] . '-' . $Nr . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . $infwg['IdModulu'] . '-' . $Nr . '">{__TLUMACZ:CZYTAJ_WIECEJ}</span></div><div style="clear:both"></div>';
                                     //
                                } else {
                                     //
                                     $ZawartoscKolumny = '<div class="FormatEdytor">' . str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$Txt) . '<div style="clear:both"></div></div>';
                                     //
                                }
                                //
                           } else {
                                //
                                $ZawartoscKolumny = '<div class="FormatEdytor">' . $Txt . '<div style="clear:both"></div></div>';
                                //
                           }
                           //
                           unset($Txt);
                           break; 
                           
                      case "bannery":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_bannery.php';               
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_bannerow_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'grupa_bannerow' => ((isset($Tablica['grupa_bannerow_kolumna'][$Nr])) ? $Tablica['grupa_bannerow_kolumna'][$Nr] : '--'),
                                                       'ilosc_bannerow' => $Tablica['ilosc_bannerow_kolumna'][$Nr],                                                       
                                                       'sortowanie_bannerow' => ((isset($Tablica['sortowanie_bannerow_kolumna'][$Nr])) ? $Tablica['sortowanie_bannerow_kolumna'][$Nr] : 'losowo'),
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],                                                       
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'ilosc_kolumn_bannerow' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'margines_bannerow_animowanych' => (int)$Tablica['animowane_bannery_margines_kolumna'][$Nr],
                                                       'animowane_bannery_ladowanie' => $Tablica['animowane_bannery_ladowanie_kolumna'][$Nr],
                                                       'sposob_wyswietlania_bannerow_statycznych' => $Tablica['statyczne_bannery_wyswietlanie_kolumna'][$Nr],
                                                       'margines_bannerow_statycznych' => (int)$Tablica['statyczne_bannery_margines_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $Rozdzielczosci);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;
                           
                      case "produkty":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_produkty.php';                         
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_produktow_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $WarunkiProduktow = array();
                                //
                                if ( isset($Tablica['warunki_produkty_nazwa_kolumna'][$Nr]) ) {
                                     //
                                     $WarunkiProduktow['szukaj_nazwa'] = $filtr->process($Tablica['warunki_produkty_nazwa_kolumna'][$Nr]);
                                     //
                                }
                                if ( isset($Tablica['warunki_produkty_nr_kat_kolumna'][$Nr]) ) {
                                     //
                                     $WarunkiProduktow['szukaj_nr_kat'] = $filtr->process($Tablica['warunki_produkty_nr_kat_kolumna'][$Nr]);
                                     //
                                }  
                                if ( isset($Tablica['warunki_produkty_tagi_kolumna'][$Nr]) ) {
                                     //
                                     $WarunkiProduktow['szukaj_tag'] = $filtr->process($Tablica['warunki_produkty_tagi_kolumna'][$Nr]);
                                     //
                                }                                     
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'grupa_produktow' => $Tablica['grupa_produktow_kolumna'][$Nr],
                                                       'produkty_id_producenta' => ((isset($Tablica['id_producent_kolumna'][$Nr])) ? $Tablica['id_producent_kolumna'][$Nr] : 0),
                                                       'produkty_id_kategoria' => ((isset($Tablica['id_kategoria_kolumna'][$Nr])) ? $Tablica['id_kategoria_kolumna'][$Nr] : 0),                                                       
                                                       'warunki_produktow' => $WarunkiProduktow,                                                       
                                                       'ilosc_produktow' => $Tablica['ilosc_produktow_kolumna'][$Nr],
                                                       'ilosc_kolumn_produktow' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'kupowanie_produktow' => $Tablica['zakup_produktow_kolumna'][$Nr],
                                                       'schowek_produktow' => $Tablica['schowek_produktow_kolumna'][$Nr],
                                                       'dostepnosc_produktow' => $Tablica['dostepnosc_produktow_kolumna'][$Nr],
                                                       'data_dostepnosci_produktow' => $Tablica['data_dostepnosci_produktow_kolumna'][$Nr],
                                                       'producent_produktow' => $Tablica['producent_produktow_kolumna'][$Nr],
                                                       'nr_kat_produktow' => $Tablica['nr_kat_produktow_kolumna'][$Nr],
                                                       'opis_krotki_produktow' => $Tablica['opis_krotki_produktow_kolumna'][$Nr],
                                                       'tylko_dostepne_produkty' => $Tablica['tylko_dostepne_produktow_kolumna'][$Nr],
                                                       'sortowanie_produktow' => $Tablica['sortowanie_produktow_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny, $WarunkiProduktow);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;       

                      case "recenzje":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_recenzje.php';                         
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_recenzji_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                       
                                           //
                                      }
                                      //                                                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'ilosc_recenzji' => $Tablica['ilosc_recenzji_kolumna'][$Nr],
                                                       'ilosc_kolumn_recenzji' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'sortowanie_recenzji' => $Tablica['sortowanie_recenzji_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;       

                      case "aktualnosci":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_aktualnosci.php';                         
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_aktualnosci_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'aktualnosci_id_kategoria' => $Tablica['kategoria_aktualnosci_kolumna'][$Nr],     
                                                       'warunki_aktualnosci_tytul' => ((isset($Tablica['warunki_aktualnosci_tytul_kolumna'][$Nr])) ? $Tablica['warunki_aktualnosci_tytul_kolumna'][$Nr] : ''),
                                                       'warunki_aktualnosci_autor' => ((isset($Tablica['warunki_aktualnosci_autor_kolumna'][$Nr])) ? $Tablica['warunki_aktualnosci_autor_kolumna'][$Nr] : ''),
                                                       'ilosc_aktualnosci' => $Tablica['ilosc_aktualnosci_kolumna'][$Nr],
                                                       'ilosc_kolumn_aktualnosci' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'foto_aktualnosci' => $Tablica['foto_aktualnosci_kolumna'][$Nr],
                                                       'odslony_aktualnosci' => $Tablica['odslony_aktualnosci_kolumna'][$Nr],
                                                       'data_dodania_aktualnosci' => $Tablica['data_aktualnosci_kolumna'][$Nr],
                                                       'autor_aktualnosci' => $Tablica['autor_aktualnosci_kolumna'][$Nr],
                                                       'sortowanie_aktualnosci' => $Tablica['sortowanie_aktualnosci_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;     

                      case "strony_info":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_strony_informacyjne.php';                         
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_strony_info_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'grupa_stron_info' => ((isset($Tablica['grupa_strony_info_kolumna'][$Nr])) ? $Tablica['grupa_strony_info_kolumna'][$Nr] : ''),
                                                       'ilosc_stron_info' => ((isset($Tablica['ilosc_strony_info_kolumna'][$Nr])) ? $Tablica['ilosc_strony_info_kolumna'][$Nr] : ''),
                                                       'ilosc_kolumn_stron_info' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'foto_strony' => ((isset($Tablica['foto_strony_kolumna'][$Nr])) ? $Tablica['foto_strony_kolumna'][$Nr] : 'nie'),
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;     

                      case "producenci":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_producenci.php';                          
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_producentow_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,                                                 
                                                       'zakres_producenci' => ((isset($Tablica['grupa_producenci_zakres_kolumna'][$Nr])) ? $Tablica['grupa_producenci_zakres_kolumna'][$Nr] : 'wszyscy'),
                                                       'id_zakres_producenci' => ((isset($Tablica['id_producent_zakres_kolumna'][$Nr])) ? explode(',', (string)$Tablica['id_producent_zakres_kolumna'][$Nr]) : array()),    
                                                       'ilosc_producentow' => $Tablica['ilosc_producentow_kolumna'][$Nr],
                                                       'ilosc_kolumn_producentow' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'margines_producentow_statycznych' => $Tablica['statyczne_producentow_margines_kolumna'][$Nr],
                                                       'logo_producentow' => $Tablica['logo_producentow_kolumna'][$Nr],
                                                       'logo_rozmiar_producentow' => $Tablica['logo_rozmiar_producentow_kolumna'][$Nr],
                                                       'nazwa_producentow' => $Tablica['nazwa_producentow_kolumna'][$Nr],
                                                       'sortowanie_producentow' => $Tablica['sortowanie_producentow_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;                             

                      case "kategorie":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_kategorie.php';                         
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_kategorii_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,                                                  
                                                       'ilosc_kolumn_kategorii' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'zakres_kategorie' => ((isset($Tablica['grupa_kategorie_zakres_kolumna'][$Nr])) ? $Tablica['grupa_kategorie_zakres_kolumna'][$Nr] : 'wszystkie'),                                                       
                                                       'id_zakres_kategorii' => ((isset($Tablica['id_kategoria_zakres_kolumna'][$Nr])) ? explode(',', (string)$Tablica['id_kategoria_zakres_kolumna'][$Nr]) : array()),                                                        
                                                       'grafika_kategorii' => $Tablica['grafika_kategorii_kolumna'][$Nr],
                                                       'grafika_rozmiar_kategorii' => $Tablica['grafika_rozmiar_kategorii_kolumna'][$Nr],
                                                       'nazwa_kategorii' => $Tablica['nazwa_kategorii_kolumna'][$Nr],
                                                       'podkategorie_kategorii' => $Tablica['podkategorie_kategorii_kolumna'][$Nr],
                                                       'ilosc_podkategorii' => $Tablica['podkategorie_ilosc_kategorii_kolumna'][$Nr],
                                                       'wyrownanie_podkategorii' => $Tablica['wyrownanie_kategorii_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;  
                           
                      case "youtube":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_youtube.php';                          
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_youtube_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $FilmyYoutube = array();
                                //
                                for ( $e = 1; $e < 11; $e++ ) {
                                      //
                                      if ( isset($Tablica['film_youtube_kolumna'][$Nr][$e]) && !empty($Tablica['film_youtube_kolumna'][$Nr][$e]) ) {
                                           //
                                           $FilmyYoutube[] = $Tablica['film_youtube_kolumna'][$Nr][$e];
                                           //
                                      }
                                      //
                                }
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,                                                  
                                                       'ilosc_kolumn_youtube' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'filmy_youtube' => $FilmyYoutube,
                                                       'miniaturka_youtube' => $Tablica['screen_youtube_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny, $FilmyYoutube);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;                             

                      case "filmmp4":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_filmy_mp4.php';                          
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_filmmp4_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                        
                                           //
                                      }
                                      //                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $FilmyMp4 = array();
                                //
                                for ( $e = 1; $e < 11; $e++ ) {
                                      //
                                      if ( isset($Tablica['film_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e]) && !empty($Tablica['film_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e]) ) {
                                           //
                                           $FilmyMp4[] = array('film' => ((isset($Tablica['film_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e])) ? $Tablica['film_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e] : ''),
                                                               'szerokosc' => ((isset($Tablica['film_szerokosc_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e])) ? $Tablica['film_szerokosc_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e] : ''),
                                                               'wysokosc' => ((isset($Tablica['film_wysokosc_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e])) ? $Tablica['film_wysokosc_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e] : ''),
                                                               'nazwa' => ((isset($Tablica['film_nazwa_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e])) ? $Tablica['film_nazwa_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e] : ''),
                                                               'link' => ((isset($Tablica['film_link_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e])) ? $Tablica['film_link_filmmp4_kolumna_' . $_SESSION['domyslnyJezyk']['id']][$Nr][$e] : ''));
                                           //
                                      }
                                      //
                                }                                    

                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,                                                  
                                                       'ilosc_kolumn_filmmp4' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'filmy_filmmp4' => $FilmyMp4,
                                                       'przyciski_kontrolne' => ((isset($Tablica['nawigacja_filmmp4_kolumna'][$Nr])) ? $Tablica['nawigacja_filmmp4_kolumna'][$Nr] : ''),
                                                       'autoodtwarzanie' => ((isset($Tablica['autoodtwarzanie_filmmp4_kolumna'][$Nr])) ? $Tablica['autoodtwarzanie_filmmp4_kolumna'][$Nr] : ''),
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                       
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny, $FilmyMp4);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;                             

                      case "java":
                           //
                           $ZawartoscKolumny = $Tablica['kod_kolumna'][$Nr];
                           //
                           break;                             
                           
                      case "opiniesklep":
                           //
                           if ( OPINIE_STATUS == 'tak' ) {
                             
                                $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_opinie_o_sklepie.php';                 
                                //
                                if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                                    //
                                    if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                        $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                                    }
                                    //
                                } 
                                //
                                if ( file_exists($PlikListingu) ) {
                                     //
                                     // ilosc kolumn dla roznych rozdzielczosci
                                     $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                     $RozdzielczosciKolumny = array();
                                     //
                                     for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                           //
                                           // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                           if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                                //
                                                $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                                //                                
                                           } else {
                                                //
                                                $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_opinii_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                       
                                                //
                                           }
                                           //                                                                
                                     }
                                     //
                                     unset($Rozdzielczosci);
                                     //
                                     $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                            'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                            'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                            'kolumna' => $Nr,
                                                            'ilosc_opinii' => $Tablica['ilosc_opinii_kolumna'][$Nr],
                                                            'ilosc_kolumn_opinii' => $RozdzielczosciKolumny,
                                                            'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                            'sortowanie_opinii' => $Tablica['sortowanie_opinii_kolumna'][$Nr],
                                                            'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                            'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                            'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                            'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                            'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                            'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                            'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                            'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                            'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                            'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                            'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                            'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                            'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                            'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                            'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                            'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                            'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                            'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                            'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                            'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                            'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                            
                                                            'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                            'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                            'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );   
                                     //
                                     ob_start();
                                     require($PlikListingu);
                                     $ZawartoscKolumny = ob_get_contents();
                                     ob_end_clean();     
                                     //
                                     unset($Konfiguracja, $RozdzielczosciKolumny);
                                     //
                                }
                                //
                                unset($PlikListingu);
                                //
                           }  

                           break; 

                      case "produkt_dnia":
                           //
                           if ( PRODUKT_DNIA_STATUS == 'tak' ) {
                             
                                $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_produkt_dnia.php';                   
                                //
                                if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                                    //
                                    if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                        $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                                    }
                                    //
                                } 
                                //
                                if ( file_exists($PlikListingu) ) {
                                     //
                                     $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                            'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                            'kolumna' => $Nr,
                                                            'opis_krotki_produkt_dnia' => $Tablica['opis_krotki_produkt_dnia_kolumna'][$Nr],
                                                            'kupowanie_produktu_dnia' => $Tablica['zakup_produkt_dnia_kolumna'][$Nr],
                                                            'oszczedzasz_produkt_dnia' => $Tablica['oszczedzasz_produkt_dnia_kolumna'][$Nr],
                                                            'nastepny_produkt_dnia' => $Tablica['nastepny_produkt_dnia_kolumna'][$Nr]);               
                                     //
                                     ob_start();
                                     require($PlikListingu);
                                     $ZawartoscKolumny = ob_get_contents();
                                     ob_end_clean();     
                                     //
                                     unset($Konfiguracja, $RozdzielczosciKolumny);
                                     //
                                }
                                //
                                unset($PlikListingu);
                                //

                           }  

                           break;

                      case "galerie":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_galerie.php';                          
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                // ilosc kolumn dla roznych rozdzielczosci
                                $Rozdzielczosci = array(1600,1200,1024,800,480,300);
                                $RozdzielczosciKolumny = array();
                                //
                                for ( $tr = 0; $tr < count($Rozdzielczosci); $tr++ ) {
                                      //
                                      // jezeli jest forma animacji - przenikanie - przyjmuje 1 kolumne
                                      if ( $Tablica['animacja_kolumna'][$Nr] == 'przenikanie' && $Tablica['forma_wyswietlania_kolumna'][$Nr] == 'animowany' ) {                                  
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => 1 );
                                           //                                
                                      } else {
                                           //
                                           $RozdzielczosciKolumny[] = array( 'rozdzielczosc' => $Rozdzielczosci[$tr], 'kolumny' => $Tablica['ilosc_kolumn_galerii_' . $Rozdzielczosci[$tr] . '_kolumna'][$Nr] );                                       
                                           //
                                      }
                                      //                                                                
                                }
                                //
                                unset($Rozdzielczosci);
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'ile_kolumn_modulu' => $Tablica['ile_kolumn'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'grupa_galerii' => ((isset($Tablica['grupa_galerii_kolumna'][$Nr])) ? $Tablica['grupa_galerii_kolumna'][$Nr] : 0),                                                      
                                                       'ilosc_galerii' => $Tablica['ilosc_galerii_kolumna'][$Nr],
                                                       'ilosc_kolumn_galerii' => $RozdzielczosciKolumny,
                                                       'ilosc_przewiniec' => $Tablica['animacja_ilosc_przewiniec'][$Nr],
                                                       'sortowanie_galerii' => $Tablica['sortowanie_galerii_kolumna'][$Nr],
                                                       'opis_grafik_galerii' => $Tablica['opis_galerii_kolumna'][$Nr],
                                                       'rozmiar_grafik_galerii' => $Tablica['rozmiar_grafik_galerii_kolumna'][$Nr],
                                                       'sposob_wyswietlania' => $Tablica['forma_wyswietlania_kolumna'][$Nr],
                                                       'sposob_animacji' => $Tablica['animacja_kolumna'][$Nr],
                                                       'nawigacja_przyciski' => $Tablica['animacja_kropki_kolumna'][$Nr],
                                                       'nawigacja_przyciski_urzadzenie' => ((!isset($Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_kropki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_przyciski_rozmiar' => $Tablica['animacja_kropki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor' => $Tablica['animacja_kropki_kolor_kolumna'][$Nr],
                                                       'nawigacja_przyciski_kolor_aktywna' => $Tablica['animacja_kropki_kolor_aktywny_kolumna'][$Nr],
                                                       'nawigacja_przyciski_czcionka' => $Tablica['animacja_kropki_czcionka_kolumna'][$Nr],
                                                       'nawigacja_strzalki' => $Tablica['animacja_strzalki_kolumna'][$Nr],
                                                       'nawigacja_strzalki_urzadzenie' => ((!isset($Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr])) ? 'zawsze' : $Tablica['animacja_strzalki_kolumna_urzadzenie'][$Nr]),
                                                       'nawigacja_strzalki_polozenie' => $Tablica['animacja_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_miejsce_wyswietlania' => $Tablica['polozenie_strzalki_wyswietlanie_kolumna'][$Nr],
                                                       'nawigacja_strzalki_rozmiar' => $Tablica['animacja_strzalki_rozmiar_kolumna'][$Nr],
                                                       'nawigacja_strzalki_kolor' => $Tablica['animacja_strzalki_kolor_kolumna'][$Nr],
                                                       'nawigacja_strzalki_czcionka_wstecz' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['0'],
                                                       'nawigacja_strzalki_czcionka_naprzod' => $this->Czcionki[$Tablica['animacja_strzalki_czcionka_kolumna'][$Nr]]['1'],
                                                       'auto_animacja' => $Tablica['animacja_sama_kolumna'][$Nr],
                                                       'pasek_animacji' => ((isset($Tablica['pasek_animacji_kolumna'][$Nr])) ? $Tablica['pasek_animacji_kolumna'][$Nr] : 'nie'),
                                                       'pasek_animacji_tlo_kolor' => ((isset($Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_tlo_kolumna'][$Nr] : '#cfcfcf'),
                                                       'pasek_animacji_kolor' => ((isset($Tablica['kolor_pasek_animacji_kolumna'][$Nr])) ? $Tablica['kolor_pasek_animacji_kolumna'][$Nr] : '#000000'),
                                                       'pasek_animacji_wysokosc' => ((isset($Tablica['pasek_animacji_wysokosc_kolumna'][$Nr])) ? $Tablica['pasek_animacji_wysokosc_kolumna'][$Nr] : '5px'),                                                            
                                                       'czas_zmiany_animacji' => $Tablica['animacja_czas_kolumna'][$Nr],
                                                       'czas_przejscia_efektu_animacji' => $Tablica['animacja_czas_szybkosc_kolumna'][$Nr],
                                                       'wyswietlana_kolumna' => ((isset($Tablica['wyswietlana_kolumna'][$Nr])) ? $Tablica['wyswietlana_kolumna'][$Nr] : 'tak') );               
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                            }
                            //
                            unset($PlikListingu);
                            //
                            break;        

                      case "ankiety":
                           //
                           $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/listing_ankiety.php';                          
                           //
                           if ($Tablica['kolumna_listing_kolumna'][$Nr] == '1' && $Tablica['plik_listing_kolumna'][$Nr] != '') {
                               //
                               if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr]) ) {
                                   $PlikListingu = 'szablony/' . DOMYSLNY_SZABLON . '/moduly_kreator_wyglad/' . $Tablica['plik_listing_kolumna'][$Nr];
                               }
                               //
                           } 
                           //
                           if ( file_exists($PlikListingu) ) {
                                //
                                $Konfiguracja = array( 'id_modulu' => $infwg['IdModulu'],
                                                       'naglowek_kolumny' => $Konfig['NaglowekKolumny'],
                                                       'kolumna' => $Nr,
                                                       'grupa_ankiety' => ((isset($Tablica['grupa_ankiety_kolumna'][$Nr])) ? $Tablica['grupa_ankiety_kolumna'][$Nr] : 0),
                                                       'tytul_ankiety' => ((isset($Tablica['tytul_ankiety_kolumna'][$Nr])) ? $Tablica['tytul_ankiety_kolumna'][$Nr] : 'tak'));               
                                //
                                ob_start();
                                require($PlikListingu);
                                $ZawartoscKolumny = ob_get_contents();
                                ob_end_clean();     
                                //
                                unset($Konfiguracja, $RozdzielczosciKolumny);
                                //
                           }
                           //
                           unset($PlikListingu);
                           //
                           break;                                 
                           
                  }                    
                  
                  // dodatkowe marginesy kolumny
                  $DodatkoweMarginesyPc = array();
                  if ( isset($Tablica['margines_gorny_kolumna_pc'][$Nr]) && $Tablica['margines_gorny_kolumna_pc'][$Nr] > 0 ) {
                       $DodatkoweMarginesyPc[] = 'margin-top:' . $Tablica['margines_gorny_kolumna_pc'][$Nr] . 'px';
                  }
                  if ( isset($Tablica['margines_dolny_kolumna_pc'][$Nr]) && $Tablica['margines_dolny_kolumna_pc'][$Nr] > 0 ) {
                       $DodatkoweMarginesyPc[] = 'margin-bottom:' . $Tablica['margines_dolny_kolumna_pc'][$Nr] . 'px';
                  }
                  if ( isset($Tablica['margines_lewy_kolumna_pc'][$Nr]) && $Tablica['margines_lewy_kolumna_pc'][$Nr] > 0 ) {
                       $DodatkoweMarginesyPc[] = 'margin-left:' . $Tablica['margines_lewy_kolumna_pc'][$Nr] . 'px';
                  }
                  if ( isset($Tablica['margines_prawy_kolumna_pc'][$Nr]) && $Tablica['margines_prawy_kolumna_pc'][$Nr] > 0 ) {
                       $DodatkoweMarginesyPc[] = 'margin-right:' . $Tablica['margines_prawy_kolumna_pc'][$Nr] . 'px';
                  }  
                  
                  $DodatkoweMarginesyMobile = array();
                  if ( isset($Tablica['margines_gorny_kolumna_mobile'][$Nr]) && $Tablica['margines_gorny_kolumna_mobile'][$Nr] > 0 ) {
                       $DodatkoweMarginesyMobile[] = 'margin-top:' . $Tablica['margines_gorny_kolumna_mobile'][$Nr] . 'px';
                  }
                  if ( isset($Tablica['margines_dolny_kolumna_mobile'][$Nr]) && $Tablica['margines_dolny_kolumna_mobile'][$Nr] > 0 ) {
                       $DodatkoweMarginesyMobile[] = 'margin-bottom:' . $Tablica['margines_dolny_kolumna_mobile'][$Nr] . 'px';
                  }
                  if ( isset($Tablica['margines_lewy_kolumna_mobile'][$Nr]) && $Tablica['margines_lewy_kolumna_mobile'][$Nr] > 0 ) {
                       $DodatkoweMarginesyMobile[] = 'margin-left:' . $Tablica['margines_lewy_kolumna_mobile'][$Nr] . 'px';
                  }
                  if ( isset($Tablica['margines_prawy_kolumna_mobile'][$Nr]) && $Tablica['margines_prawy_kolumna_mobile'][$Nr] > 0 ) {
                       $DodatkoweMarginesyMobile[] = 'margin-right:' . $Tablica['margines_prawy_kolumna_mobile'][$Nr] . 'px';
                  }                     

                  $StylCss = '';
                  //
                  if ( count($DodatkoweMarginesyPc) > 0 ) {
                       //
                       $StylCss .= '@media only screen and (min-width:1024px) { .KolumnaCssMarginesyNumer-' . $t . '-' . $infwg['IdModulu'] . ' { '. implode(';', (array)$DodatkoweMarginesyPc) . ' } }';
                       //
                  }
                  if ( count($DodatkoweMarginesyMobile) > 0 ) {
                       //
                       $StylCss .= '@media only screen and (max-width:1023px) { .KolumnaCssMarginesyNumer-' . $t . '-' . $infwg['IdModulu'] . ' { '. implode(';', (array)$DodatkoweMarginesyMobile) . ' } }';
                       //
                  }                  
                  // ukrywanie kolumny na mobile
                  if ( isset($Tablica['rwd_mala_rozdzielczosc_kolumna'][$Nr]) && $Tablica['rwd_mala_rozdzielczosc_kolumna'][$Nr] == 'nie' ) {
                       //
                       $StylCss .= '@media only screen and (max-width:1023px) { .KolumnaCssMarginesyNumer-' . $t . '-' . $infwg['IdModulu'] . ' { display:none; } }';
                       //
                  }     
                  // ukrywanie opisu kolumny na mobile
                  if ( isset($Tablica['rwd_mala_rozdzielczosc_opis_kolumna'][$Nr]) && $Tablica['rwd_mala_rozdzielczosc_opis_kolumna'][$Nr] == 'nie' ) {
                       //
                       $StylCss .= '@media only screen and (max-width:1023px) { .KolumnaCssMarginesyNumer-' . $t . '-' . $infwg['IdModulu'] . ' .OpisKolumnyModulu { display:none; } }';
                       //
                  }     
                  
                  // dodatkowy kod css dla kolumny
                  if ( isset($Tablica['css_kolumna'][$Nr]) && $Tablica['css_kolumna'][$Nr] != '' ) {
                       //
                       $StylCss .= str_replace('{KLASA_CSS_KOLUMNY}', '.KolumnaCssMarginesyNumer-' . $t . '-' . $infwg['IdModulu'], (string)$Tablica['css_kolumna'][$Nr]);
                       //
                  }                       
                  
                  $GLOBALS['css'] .= $StylCss;
                  //
                  unset($StylCss, $DodatkoweMarginesyPc, $DodatkoweMarginesyMobile);                     
                  
                  // wstawienie tresci
                  $modul->dodaj('__TRESC_KOLUMNY', $ZawartoscKolumny);
                  
                  // czy kolumna ma byc wyswietlana
                  $modul->parametr('WyswietlajKolumne', 'tak'); 
                  //
                  if ( (empty($ZawartoscKolumny) || strpos($ZawartoscKolumny, 'BrakDanychKolumnyKreatora') > -1) && isset($Tablica['wyswietlana_kolumna'][$Nr]) && $Tablica['wyswietlana_kolumna'][$Nr] == 'nie' ) {
                       //
                       $modul->parametr('WyswietlajKolumne', 'nie'); 
                       //
                  }                       
                  //
                  $Wynik['kolumna_' . $Nr] = $modul->uruchom();
                  //
                  unset($modul, $Konfig);
                  //
                  $t++;
                  //
             }
             //
        }    
        //
        unset($Tablica);
        //
        return $Wynik;
        
    }

    // funkcja zwraca linki gornego menu, dolnego menu, stopki
    public function Linki( $rodzaj, $tagPoczatek = '<li>', $tagKoniec = '</li>', $pelneDrzewo = false, $poziomMenu = 1, $tylko_numer = -1) {
      
        $ciecie = '';
        //
        switch ($rodzaj) {
            case "gorne_menu":
                $ciecie = GORNE_MENU;
                break; 
            case "dolne_menu":
                $ciecie = DOLNE_MENU;
                break;      
            case "szybkie_menu":
                $ciecie = SZYBKIE_MENU;
                break;                 
            case "pierwsza_stopka":
                $ciecie = STOPKA_PIERWSZA;
                break;
            case "druga_stopka":
                $ciecie = STOPKA_DRUGA;
                break;
            case "trzecia_stopka":
                $ciecie = STOPKA_TRZECIA;
                break;
            case "czwarta_stopka":
                $ciecie = STOPKA_CZWARTA;
                break;
            case "piata_stopka":
                $ciecie = STOPKA_PIATA;
                break;
        }
        
        $TablicaLinkow = explode('/', (string)$_SERVER['REQUEST_URI']);
        $AktualnyLink = '';
        if ( trim((string)$TablicaLinkow[0]) != '' ) {
             //
             $AktualnyLink = trim((string)$TablicaLinkow[0]);
             //
        }
        if ( trim((string)$TablicaLinkow[1]) != '' ) {
             //
             $AktualnyLink = trim((string)$TablicaLinkow[1]);
             //
        }      

        $Podkategorie = array();
        
        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
             //
             $PodTmp = @unserialize(MENU_PODKATEGORIE);
             //
             if ( is_array($PodTmp) ) {
                  //
                  $Podkategorie = $PodTmp;
                  //
             }
             //
             unset($PodTmp);
             //
        }        

        $DoWyswietlania = '';
        
        if ($ciecie != '') {
            //
            $pozycje_menu = explode(',', (string)$ciecie);
            //
            for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {

                $strona = explode(';', (string)$pozycje_menu[$x]);
                
                switch ($strona[0]) {
                    case "strona":
                        //
                        if ( !empty($this->StronyInformacyjne[$strona[1]][0]) ) {
                            //
                            $NazwaPozycji = $this->StronyInformacyjne[$strona[1]][0];
                                
                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$strona[1] . '|strona']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|strona'];
                            }                         

                            if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                 //
                                 $FlagaPozycji = '';
                                 //
                                 if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                           //
                                           $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                           //
                                      }
                                      //
                                 }
                                 //                               
                                 $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                                 //
                                 unset($FlagaPozycji);
                                 //
                            }                            
                            
                            // dodanie ikonki jak jest
                            if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                  //
                                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                  //
                                  if ( (int)$szerokosc == 0 ) {
                                       $szerokosc = 100;
                                  }
                                  if ( (int)$wysokosc == 0 ) {
                                       $wysokosc = 100;
                                  }                  
                                  //
                                  $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                  //
                                 }
                                 //
                            }
                            //
                          
                            $DodatkoweCss = array();
                           
                            // kolor linku
                            if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                      $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                 }
                                 //
                            }
                            
                            // kolor tla
                            if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                      $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                 }
                                 //
                            }                           
  
                            $tagPoczatekTmp = str_replace('<li ', '<li data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatek);
                       
                            if ( is_array($this->StronyInformacyjne[$strona[1]]) && count($this->StronyInformacyjne[$strona[1]]) == 3 ) {
                                //                        
                                $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' ' . $this->StronyInformacyjne[$strona[1]][2] . ' href="' . $this->StronyInformacyjne[$strona[1]][1] . '"' . (($AktualnyLink == $this->StronyInformacyjne[$strona[1]][1]) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') .  $tagKoniec;
                                //
                              } else {
                                //
                                $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' ' . $this->StronyInformacyjne[$strona[1]][1] . ' href="' . Seo::link_SEO( $this->StronyInformacyjne[$strona[1]][0], $strona[1], 'strona_informacyjna') . '"' . (($AktualnyLink == Seo::link_SEO( $this->StronyInformacyjne[$strona[1]][0], $strona[1], 'strona_informacyjna')) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                                //
                            }
                            //
                            unset($tagPoczatekTmp, $NazwaPozycji, $DodatkoweCss);
                            //
                        }
                        //
                        unset($NazwaPozycji, $PozycjaKonfiguracji);
                        //
                        break;
                    case "galeria":
                        //
                        $NazwaPozycji = ((isset($this->Galerie[$strona[1]])) ? $this->Galerie[$strona[1]] : '');
                        
                        $PozycjaKonfiguracji = array();
                        if ( isset($Podkategorie[$strona[1] . '|galeria']) ) {
                             $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|galeria'];
                        }         

                        if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                             //
                             $FlagaPozycji = '';
                             //
                             if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                       isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                       isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                       //
                                       $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                       //
                                  }
                                  //
                             }
                             //                           
                             $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                             //
                             unset($FlagaPozycji);
                             //
                        }                         
                        
                        // dodanie ikonki jak jest
                        if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                             //
                             if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                  //
                                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                  //
                                  if ( (int)$szerokosc == 0 ) {
                                       $szerokosc = 100;
                                  }
                                  if ( (int)$wysokosc == 0 ) {
                                       $wysokosc = 100;
                                  }                  
                                  //
                                  $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                  //
                             }
                             //
                        }
                        //  
                        if ( !empty($this->Galerie[$strona[1]]) ) {
                          
                             $DodatkoweCss = array();
                            
                             // kolor linku
                             if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                       $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                  }
                                  //
                             }
                             
                             // kolor tla
                             if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                       $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                  }
                                  //
                             }                           
                             //
                             $tagPoczatekTmp = str_replace('<li ', '<li data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatek);
                             //
                             $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . Seo::link_SEO( $this->Galerie[$strona[1]], $strona[1], 'galeria') . '"' . (($AktualnyLink == Seo::link_SEO( $this->Galerie[$strona[1]], $strona[1], 'galeria')) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                             //                                                                   
                             unset($tagPoczatekTmp, $DodatkoweCss);  
                             //                           
                        }
                        //
                        unset($NazwaPozycji, $PozycjaKonfiguracji);
                        //
                        break; 
                    case "formularz":
                        //
                        $NazwaPozycji = ((isset($this->Formularze[$strona[1]])) ? $this->Formularze[$strona[1]] : '');
                        
                        $PozycjaKonfiguracji = array();
                        if ( isset($Podkategorie[$strona[1] . '|formularz']) ) {
                             $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|formularz'];
                        }             

                        if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                             //
                             $FlagaPozycji = '';
                             //
                             if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                       isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                       isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                       //
                                       $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                       //
                                  }
                                  //
                             }
                             //                               
                             $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                             //
                             unset($FlagaPozycji);
                             //
                        }                         
                        
                        // dodanie ikonki jak jest
                        if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                             //
                             if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                  //
                                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                  //
                                  if ( (int)$szerokosc == 0 ) {
                                       $szerokosc = 100;
                                  }
                                  if ( (int)$wysokosc == 0 ) {
                                       $wysokosc = 100;
                                  }                  
                                  //
                                  $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                  //
                             }
                             //
                        }
                        //
                        if ( !empty($this->Formularze[$strona[1]]) ) {
                          
                             $DodatkoweCss = array();
                            
                             // kolor linku
                             if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                       $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                  }
                                  //
                             }
                             
                             // kolor tla
                             if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                       $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                  }
                                  //
                             }                           
                             //
                             $tagPoczatekTmp = str_replace('<li ', '<li data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatek);
                             //
                             $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( $this->Formularze[$strona[1]], $strona[1], 'formularz') . '"' . (($AktualnyLink == Seo::link_SEO( $this->Formularze[$strona[1]], $strona[1], 'formularz')) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                             //                                                                   
                             unset($tagPoczatekTmp, $DodatkoweCss);  
                             //                                                        
                        }
                        //
                        unset($NazwaPozycji, $PozycjaKonfiguracji);
                        //
                        break; 
                    case "kategoria":
                        //
                        $NazwaPozycji = $this->KategorieArtykulow[$strona[1]];
                        
                        $PozycjaKonfiguracji = array();
                        if ( isset($Podkategorie[$strona[1] . '|kategoria']) ) {
                             $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|kategoria'];
                        }             

                        if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                             //
                             $FlagaPozycji = '';
                             //
                             if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                       isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                       isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                       //
                                       $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                       //
                                  }
                                  //
                             }
                             //                             
                             $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                             //
                             unset($FlagaPozycji);
                             //
                        }                         

                        // dodanie ikonki jak jest
                        if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                             //
                             if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                  //
                                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                  //
                                  if ( (int)$szerokosc == 0 ) {
                                       $szerokosc = 100;
                                  }
                                  if ( (int)$wysokosc == 0 ) {
                                       $wysokosc = 100;
                                  }                  
                                  //
                                  $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                  //
                             }
                             //
                        }
                        //
                        if ( !empty($this->KategorieArtykulow[$strona[1]]) ) {
                          
                             $DodatkoweCss = array();
                            
                             // kolor linku
                             if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                       $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                  }
                                  //
                             }
                             
                             // kolor tla
                             if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                  //
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                       $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                  }
                                  //
                             }                           
                             //
                             $tagPoczatekTmp = str_replace('<li ', '<li data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatek);
                             //
                             $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . Seo::link_SEO( $this->KategorieArtykulow[$strona[1]], $strona[1], 'kategoria_aktualnosci') . '"' . (($AktualnyLink == Seo::link_SEO( $this->KategorieArtykulow[$strona[1]], $strona[1], 'kategoria_aktualnosci')) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                             //                                                                   
                             unset($tagPoczatekTmp, $DodatkoweCss);  
                             //                            
                        }
                        //
                        unset($NazwaPozycji, $PozycjaKonfiguracji);
                        //
                        break;   
                    case "artykul":
                        //
                        if ( isset($this->Artykuly[$strona[1]]) ) {
                          
                              $NazwaPozycji = $this->Artykuly[$strona[1]];

                              $PozycjaKonfiguracji = array();
                              if ( isset($Podkategorie[$strona[1] . '|artykul']) ) {
                                   $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|artykul'];
                              }         

                              if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                   //
                                   $FlagaPozycji = '';
                                   //
                                   if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                        //
                                        if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                             isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                             isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                             //
                                             $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                             //
                                        }
                                        //
                                   }
                                   //                                     
                                   $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                                   //
                                   unset($FlagaPozycji);
                                   //
                              }                               

                              // dodanie ikonki jak jest
                              if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                   //
                                   if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                        //
                                        list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                        //
                                        if ( (int)$szerokosc == 0 ) {
                                             $szerokosc = 100;
                                        }
                                        if ( (int)$wysokosc == 0 ) {
                                             $wysokosc = 100;
                                        }                  
                                        //
                                        $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                        //
                                   }
                                   //
                              }
                              //
                              if ( !empty($this->Artykuly[$strona[1]]) ) {
                                
                                   $DodatkoweCss = array();
                                  
                                   // kolor linku
                                   if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                        //
                                        if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                             $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                        }
                                        //
                                   }
                                   
                                   // kolor tla
                                   if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                        //
                                        if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                             $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                        }
                                        //
                                   }                           
                                   //
                                   $tagPoczatekTmp = str_replace('<li ', '<li data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatek);
                                   //
                                   $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . Seo::link_SEO( $this->Artykuly[$strona[1]], $strona[1], 'aktualnosc') . '"' . (($AktualnyLink == Seo::link_SEO( $this->Artykuly[$strona[1]], $strona[1], 'aktualnosc')) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                                   //                                                                   
                                   unset($tagPoczatekTmp, $DodatkoweCss);  
                                   //  

                              }                                   
                        }
                        //
                        unset($NazwaPozycji, $PozycjaKonfiguracji);
                        //
                        break; 
                    case "kategproduktow":
                        //
                        $NazwaPozycji = Kategorie::NazwaKategoriiId($strona[1], true);
                        
                        if ( $NazwaPozycji != '' ) {

                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$strona[1] . '|kategproduktow']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|kategproduktow'];
                            }   
                            
                            if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                 //
                                 $FlagaPozycji = '';
                                 //
                                 if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                           //
                                           $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                           //
                                      }
                                      //
                                 }
                                 //                              
                                 $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji. $NazwaPozycji . '</b>';
                                 //
                                 unset($FlagaPozycji);
                                 //
                            }                         
                                
                            // dodanie ikonki jak jest
                            if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                      //
                                      list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                      //
                                      if ( (int)$szerokosc == 0 ) {
                                           $szerokosc = 100;
                                      }
                                      if ( (int)$wysokosc == 0 ) {
                                           $wysokosc = 100;
                                      }                  
                                      //
                                      $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                      //
                                 }
                                 //
                            }
                            //
                            $NazwaKategoriiSeo = Kategorie::NazwaKategoriiSeoId($strona[1]);
                            //
                            if ( !empty($NazwaPozycji) ) {
                              
                                 $DodatkoweCss = array();
                                
                                 // kolor linku
                                 if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                           $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                      }
                                      //
                                 }
                                 
                                 // kolor tla
                                 if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                           $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                      }
                                      //
                                 }                           
                                 //
                                 $tagPoczatekTmp = str_replace('<li ', '<li data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatek);
                                 //
                                 $DoWyswietlania .= $tagPoczatekTmp . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . Seo::link_SEO( $NazwaKategoriiSeo, $strona[1], 'kategoria') . '"' . (($AktualnyLink == Seo::link_SEO( $NazwaKategoriiSeo, $strona[1], 'kategoria')) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaPozycji . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                                 //                                                 
                                 unset($tagPoczatekTmp, $DodatkoweCss);  
                                 //                                                      
                            }
                            //
                            unset($PozycjaKonfiguracji, $NazwaKategoriiSeo);
        
                        }

                        unset($NazwaPozycji);
                        //
                        break;                            
                    case "artkategorie":
                        //
                        if ( !empty($this->KategorieArtykulow[$strona[1]]) ) {
                            //
                            $BylLink = false;
                            //
                            $NazwaKategorii = $this->KategorieArtykulow[$strona[1]];
                            
                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$strona[1] . '|artkategorie']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|artkategorie'];
                            }                                
                            
                            if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                 //
                                 $FlagaPozycji = '';
                                 //
                                 if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                           //
                                           $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                           //
                                      }
                                      //
                                 }
                                 //                                
                                 $NazwaKategorii = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaKategorii) . '">' . $FlagaPozycji . $NazwaKategorii . '</b>';
                                 //
                                 unset($FlagaPozycji);
                                 //
                            }
                                                        
                            // dodanie ikonki jak jest
                            if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                      //
                                      list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                      //
                                      if ( (int)$szerokosc == 0 ) {
                                           $szerokosc = 100;
                                      }
                                      if ( (int)$wysokosc == 0 ) {
                                           $wysokosc = 100;
                                      }                  
                                      //
                                      $NazwaKategorii = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaKategorii) . '" /></i>' . $NazwaKategorii;
                                      //
                                 }
                                 //
                            }
                            
                            $DodatkoweCss = array();
                            
                            // kolor linku
                            if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                      $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                 }
                                 //
                            }
                            
                            // kolor tla
                            if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                      $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                 }
                                 //
                            }                                  

                            // ilosc wyswietlanych artykulow
                            if ( isset($PozycjaKonfiguracji['ile_artykulow_kategorii']) && $PozycjaKonfiguracji['ile_artykulow_kategorii'] != 'wszystkie' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria($strona[1], (int)$PozycjaKonfiguracji['ile_artykulow_kategorii']);
                                 //
                            } else {
                                 //
                                 $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria($strona[1]);
                                 //
                            }
                            
                            $tagPoczatekTmp = $tagPoczatek;
                            
                            if ( Wyglad::TypSzablonu() == true ) {
                                 //
                                 $MenuPreloader = '';
                                 //          
                                 if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                      //
                                      $MenuPreloader = ' PozycjaMenuPreloader';
                                      //
                                 } 
                                 //
                                 $CssMenu = 'class="PozycjaMenuNormalne' . $MenuPreloader . '"';
                                 //
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                      //
                                 }
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                      //
                                 }
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                      //
                                 }
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                      //
                                 }
                                 //
                                 $tagPoczatekTmp = str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp);
                                 //
                                 unset($CssMenu);
                                 //
                            }
                            
                            $DoWyswietlaniaTmp = $tagPoczatekTmp;
                            
                            unset($tagPoczatekTmp);
                            
                            if ( Wyglad::TypSzablonu() == true ) {
                                 //
                                 $DoWyswietlaniaTmp .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';
                                 //
                            }

                            $DoWyswietlaniaTmp .= '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . Seo::link_SEO( $this->KategorieArtykulow[$strona[1]], $strona[1], 'kategoria_aktualnosci') . '" ' . (($AktualnyLink == Seo::link_SEO( $this->KategorieArtykulow[$strona[1]], $strona[1], 'kategoria_aktualnosci')) ? ' class="AktywnyLinkMenu MenuLinkAktualnosci' . ((count($TablicaArtykulow) > 0) ? ' PozycjaRozwijanaMenu' : '') . '"' : ' class="MenuLinkAktualnosci' . ((count($TablicaArtykulow) > 0) ? ' PozycjaRozwijanaMenu' : '') . '"') . '>' . $NazwaKategorii . '</a>';
                            
                            if ( Wyglad::TypSzablonu() == true ) {
                                 //
                                 $DoWyswietlaniaTmp .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu" tabindex="0" role="button"></label></div>';
                                 //
                            }   
                                                      
                            unset($DodatkoweCss);  
                            //
                            if ( count($TablicaArtykulow) > 0 ) {
                              
                                $DodatkowyCss = ' ';
                                
                                if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                      //
                                      $CssMenu = 'MenuSzerokie';
                                      //
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                           $CssMenu = 'MenuSzerokie Menu30Procent';
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                           $CssMenu = 'MenuSzerokie Menu50Procent';
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                           $CssMenu = 'MenuSzerokie Menu70Procent';
                                      }
                                      //
                                      $DodatkowyCss = ' ' . $CssMenu . ' MenuSzerokie-' . $PozycjaKonfiguracji['ilosc_kolumn'] . ' ';
                                      //
                                      unset($CssMenu);
                                      //
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' ) {                                              
                                           $DodatkowyCss .= 'GrafikiNadPodLinkami GrafikiNadLinkami ';
                                      }
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' ) {
                                           $DodatkowyCss .= 'GrafikiNadPodLinkami GrafikiPodLinkami ';
                                      }                                          
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {
                                           $DodatkowyCss .= 'GrafikiPrawaLewaStrona GrafikiPrawaStrona ';
                                      }                                                                                    
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {
                                           $DodatkowyCss .= 'GrafikiPrawaLewaStrona GrafikiLewaStrona ';
                                      }    
                                }
                                if ( !isset($PozycjaKonfiguracji['szerokosc']) || (isset($PozycjaKonfiguracji['szerokosc']) && empty($PozycjaKonfiguracji['szerokosc']) || $PozycjaKonfiguracji['szerokosc'] == 'normalne') ) {
                                      $DodatkowyCss = ' MenuNormalne ';
                                }
                                
                                //
                                $EfektMenu = 'EfektMenu-1';
                                //
                                if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                     //
                                     $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                     //
                                }
                                //
                                $DoWyswietlaniaTmp .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . $DodatkowyCss . 'MenuDrzewoAktualnosci" id="OknoMenu-' . $x . '">';
                                unset($EfektMenu);
                                
                                $DoWyswietlaniaTmpLinki = '';
                                $DoWyswietlaniaTmpBannery = '';
                                //
                                foreach ( $TablicaArtykulow as $Artykul ) {
                                    //
                                    if ( $Artykul['seo'] == $AktualnyLink ) {
                                         //
                                         $BylLink = true;
                                         //
                                    }
                                    //
                                    //
                                    if ( isset($PozycjaKonfiguracji['ikony_aktualnosci']) && $PozycjaKonfiguracji['ikony_aktualnosci'] == 'tak' && Wyglad::TypSzablonu() == true ) {
                                          
                                          // grafika kategorii
                                          $IkonyAktualnosci = '';
                                          $MiejsceIkonyAktualnosci = '';
                                          $SzerokoscIkonyAktualnosci = '';
                                          $SzerokoscNazwyAktualnosci = '';

                                          if ( $Artykul['ikona'] == '' || !file_exists(KATALOG_ZDJEC . '/' . $Artykul['ikona']) ) {
                                               //
                                               $Artykul['ikona'] = 'domyslny.webp';
                                               //
                                          } 

                                          if ( isset($PozycjaKonfiguracji['rozmiar_ikony_aktualnosci']) && $PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'] != 'brak' && (int)$PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'] > 0 ) {
                                               //
                                               $IkonyAktualnosci = Funkcje::pokazObrazek($Artykul['ikona'], $Artykul['tytul'], (int)$PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'], (int)$PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'], array(), '', 'maly', true, false, false);
                                               $SzerokoscIkonyAktualnosci = ' style="width:' . (int)$PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'] . 'px"';
                                               $SzerokoscNazwyAktualnosci = ' style="width:calc(100% - ' . (int)$PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'] . 'px)"';
                                               //
                                          }
                                          if ( isset($PozycjaKonfiguracji['rozmiar_ikony_aktualnosci']) && $PozycjaKonfiguracji['rozmiar_ikony_aktualnosci'] == 'brak' ) {
                                               //
                                               $IkonyAktualnosci = '<img src="' . KATALOG_ZDJEC . '/' . $Artykul['ikona'] . '" alt="' . $Artykul['tytul'] . '" />';
                                               //
                                          }                                                 
                                          
                                          if ( isset($PozycjaKonfiguracji['miejsce_ikony_aktualnosci']) && $PozycjaKonfiguracji['miejsce_ikony_aktualnosci'] == 'obok' ) {
                                               //
                                               $MiejsceIkonyAktualnosci = ' GrafikaObokNazwy';
                                               //
                                               if ( $IkonyAktualnosci != '' ) {
                                                    //
                                                    $IkonyAktualnosci = '<span class="GrafikaKategoriiMenu"' . $SzerokoscIkonyAktualnosci . '>' . $IkonyAktualnosci . '</span>';
                                                    $CzyJestIkonaAktualnosci = true;
                                                    //
                                               }
                                               //
                                          } else {
                                               //
                                               $MiejsceIkonyAktualnosci = ' GrafikaNadNazwa';
                                               //
                                               if ( $IkonyAktualnosci != '' ) {
                                                    //
                                                    $IkonyAktualnosci = '<span class="GrafikaKategoriiMenu">' . $IkonyAktualnosci . '</span>';
                                                    $CzyJestIkonaAktualnosci = true;
                                                    //
                                               }
                                               //
                                          }
                                          
                                          if ( isset($PozycjaKonfiguracji['miejsce_ikony_aktualnosci']) && $PozycjaKonfiguracji['miejsce_ikony_aktualnosci'] == 'nad' ) {
                                               //
                                               $SzerokoscNazwyAktualnosci = '';
                                               //
                                          }
                                          
                                          $CssLinkAktualnosci = '';

                                          // wyswietlanie ikony grafiki 
                                          if ( $CzyJestIkonaAktualnosci == true && Wyglad::TypSzablonu() == true ) {
                                               //
                                               $CssLinkAktualnosci = 'LinkDlaGrafikiKategorii';
                                               //
                                               if ( isset($PozycjaKonfiguracji['miejsce_ikony_aktualnosci']) && $PozycjaKonfiguracji['miejsce_ikony_aktualnosci'] == 'nad' ) {
                                                    //
                                                    $CssLinkAktualnosci = 'LinkDlaGrafikiKategorii NazwaWysrodkowana';
                                                    //               
                                               }
                                               //
                                          }
                                          
                                          if ( isset($PozycjaKonfiguracji['mobile_ikony_aktualnosci']) && $PozycjaKonfiguracji['mobile_ikony_aktualnosci'] == 'nie' ) {
                                           
                                               $CssLinkAktualnosci .= ' MenuGorneBezGrafikiPozycjiMobilne';
                                            
                                          } else {
                                               
                                               $CssLinkAktualnosci .= ' MenuGorneGrafikiPozycjiMobilne';
                                               
                                          }

                                          if ( $CssLinkAktualnosci != '' ) {
                                               //
                                               $CssLinkAktualnosci = 'class="' . $CssLinkAktualnosci . '"';
                                               //
                                          }
        
                                          $CiagDoWyswietlaniaGrafika = '<a ' . $CssLinkAktualnosci . 'href="' . $Artykul['seo'] . '"><span class="MenuGorneGrafikiPozycji' . $MiejsceIkonyAktualnosci . '">' . $IkonyAktualnosci . '<span' . $SzerokoscNazwyAktualnosci . '>' . $Artykul['tytul'] . '</span></span></a>';
                                          
                                     } else {
                                       
                                          $CiagDoWyswietlaniaGrafika = $Artykul['link'];
                                       
                                     }
                                     
                                     $DoWyswietlaniaTmpLinki .= '<li class="LinkiMenu">' . $CiagDoWyswietlaniaGrafika . '</li>';
                                     //
                                }
                                //
                                unset($DodatkowyCss);
                                //
                                if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($PozycjaKonfiguracji['grupa_bannerow']) && !empty($PozycjaKonfiguracji['grupa_bannerow']) && Wyglad::TypSzablonu() == true ) {
                                     //
                                     $GrupaBannerow = $PozycjaKonfiguracji['grupa_bannerow'];
                                     //
                                     if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {

                                          $TablicaBannerow = $GLOBALS['bannery']->info[$GrupaBannerow];
                                          //
                                          if ( count($TablicaBannerow) > 0 ) {
                                               //
                                               if ( isset($PozycjaKonfiguracji['ilosc_bannerow']) ) {
                                                    //
                                                    $TablicaBannerow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, (($PozycjaKonfiguracji['ilosc_bannerow'] == 'jeden') ? 1 : 20));
                                                    //
                                               }
                                               //
                                          }
                                          //
                                          if ( count($TablicaBannerow) > 0 ) {
                                              
                                               $DoWyswietlaniaTmpBannery = '<li class="GrafikiMenu">';
 
                                               foreach ($TablicaBannerow as $Banner ) {
 
                                                   $DoWyswietlaniaBanner = $GLOBALS['bannery']->bannerWyswietlMenu($Banner);
                                                   
                                                   if ( $DoWyswietlaniaBanner != '' ) {
                                                        //
                                                        $DoWyswietlaniaTmpBannery .= '<div>' . $DoWyswietlaniaBanner . '</div>';
                                                        //
                                                   }
                                                   
                                                   unset($DoWyswietlaniaBanner);
 
                                               }
                                               
                                               $DoWyswietlaniaTmpBannery .= '</li>';
                                             
                                          }  
                                          //
                                          unset($TablicaBannerow);                                     
                                          //
                                     }
                                     //
                                     unset($GrupaBannerow);                                     
                                     //                                     
                                }                                
                                  
                                if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                     //
                                     $DoWyswietlaniaTmp = '';
                                     //
                                }                                  

                                $DoWyswietlaniaTresc = '';
                                //
                                if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $DoWyswietlaniaTmpBannery != '' && Wyglad::TypSzablonu() == true ) { 
                                     //
                                     if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {                                    
                                          $DoWyswietlaniaTresc .= '<li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li><li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li>';
                                     }
                                     if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {   
                                          $DoWyswietlaniaTresc .= '<li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li><li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li>';
                                     }                                       
                                     //
                                } else {
                                     //
                                     $DoWyswietlaniaTresc .= $DoWyswietlaniaTmpLinki . $DoWyswietlaniaTmpBannery;
                                     //
                                }
                                  
                                if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                     //
                                     return $DoWyswietlaniaTresc;
                                     //
                                }    
                                
                                if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                     //
                                     $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                     //
                                }
                                
                                $DoWyswietlaniaTmp .= $DoWyswietlaniaTresc . '</ul>';
                                //
                                unset($DoWyswietlaniaTmpBannery, $DoWyswietlaniaTmpLinki);                                
                                //
                            }
                            //
                            unset($TablicaArtykulow, $NazwaKategorii, $PozycjaKonfiguracji);
                            //
                            $DoWyswietlaniaTmp .= $tagKoniec;
                            
                            if ( $BylLink == true ) {
                                 //
                                 $DoWyswietlaniaTmp = str_replace('class="MenuLinkAktualnosci"', 'class="AktywnyLinkMenu MenuLinkAktualnosci"', (string)$DoWyswietlaniaTmp);
                                 //
                            }                            
                            
                            $DoWyswietlania .= $DoWyswietlaniaTmp;
                            
                            unset($DoWyswietlaniaTmp, $BylLink);
                            //
                        }
                        //
                        break;    
                    case "grupainfo":
                        //
                        $GrupyStron = StronyInformacyjne::TablicaGrupInfo();
                        //
                        if ( isset( $GrupyStron[$strona[1]] ) ) {
                            //
                            $BylLink = false;
                            //
                            $NazwaGrupy = $GrupyStron[$strona[1]]['nazwa'];
                            
                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$GrupyStron[$strona[1]]['id'] . '|grupainfo']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$GrupyStron[$strona[1]]['id'] . '|grupainfo'];
                            }     

                            if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                 //
                                 $FlagaPozycji = '';
                                 //
                                 if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                           //
                                           $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                           //
                                      }
                                      //
                                 }
                                 //                               
                                 $NazwaGrupy = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaGrupy) . '">' . $FlagaPozycji . $NazwaGrupy . '</b>';
                                 //
                                 unset($FlagaPozycji);
                                 //
                            }                             
                            
                            // dodanie ikonki jak jest
                            if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                      //
                                      list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                      //
                                      if ( (int)$szerokosc == 0 ) {
                                           $szerokosc = 100;
                                      }
                                      if ( (int)$wysokosc == 0 ) {
                                           $wysokosc = 100;
                                      }                  
                                      //
                                      $NazwaGrupy = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaGrupy) . '" /></i>' . $NazwaGrupy;
                                      //
                                 }
                                 //
                            }
                            
                            $DodatkoweCss = array();
                            
                            // kolor linku
                            if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                      $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                 }
                                 //
                            }
                            
                            // kolor tla
                            if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                      $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                 }
                                 //
                            }                                  

                            $TablicaStronInformacyjnych = StronyInformacyjne::TablicaStronInfoGrupa( $GrupyStron[$strona[1]]['kod'] );

                            $tagPoczatekTmp = $tagPoczatek;

                            if ( Wyglad::TypSzablonu() == true ) {
                                 //
                                 $MenuPreloader = '';
                                 //          
                                 if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                      //
                                      $MenuPreloader = ' PozycjaMenuPreloader';
                                      //
                                 } 
                                 //
                                 $CssMenu = 'class="PozycjaMenuNormalne' . $MenuPreloader . '"';
                                 //
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                      //
                                 }
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                      //
                                 }
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                      //
                                 }
                                 if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                      //
                                      $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                      //
                                 }
                                 //
                                 $tagPoczatekTmp = str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" tabindex="0" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp);
                                 //
                                 unset($CssMenu);
                                 //
                            }                            

                            $DoWyswietlaniaTmp = $tagPoczatekTmp;
                            
                            unset($tagPoczatekTmp);
                            
                            if ( Wyglad::TypSzablonu() == true ) {
                                 //
                                 $DoWyswietlaniaTmp .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';
                                 //
                            }

                            $DoWyswietlaniaTmp .= '<span class="MenuLinkStronyInformacyjne' . ((count($TablicaStronInformacyjnych) > 0) ? ' PozycjaRozwijanaMenu' : '') . '" ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . '>' . $NazwaGrupy . '</span>';
                            
                            if ( Wyglad::TypSzablonu() == true ) {
                                 //
                                 $DoWyswietlaniaTmp .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu" tabindex="0" role="button"></label></div>';
                                 //
                            }   

                            unset($DodatkoweCss);  
                            //
                            if ( count($TablicaStronInformacyjnych) > 0 ) {
                                //
                                
                                $DodatkowyCss = ' ';
                                
                                if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                      //
                                      $CssMenu = 'MenuSzerokie';
                                      //
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                           $CssMenu = 'MenuSzerokie Menu30Procent';
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                           $CssMenu = 'MenuSzerokie Menu50Procent';
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                           $CssMenu = 'MenuSzerokie Menu70Procent';
                                      }
                                      //
                                      $DodatkowyCss = ' ' . $CssMenu . ' MenuSzerokie-' . $PozycjaKonfiguracji['ilosc_kolumn'] . ' ';
                                      //
                                      unset($CssMenu);
                                      //
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' ) {                                              
                                           $DodatkowyCss .= 'GrafikiNadPodLinkami GrafikiNadLinkami ';
                                      }
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' ) {
                                           $DodatkowyCss .= 'GrafikiNadPodLinkami GrafikiPodLinkami ';
                                      }                                          
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {
                                           $DodatkowyCss .= 'GrafikiPrawaLewaStrona GrafikiPrawaStrona ';
                                      }                                                                                    
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {
                                           $DodatkowyCss .= 'GrafikiPrawaLewaStrona GrafikiLewaStrona ';
                                      }                                              
                                }
                                if ( !isset($PozycjaKonfiguracji['szerokosc']) || (isset($PozycjaKonfiguracji['szerokosc']) && empty($PozycjaKonfiguracji['szerokosc']) || $PozycjaKonfiguracji['szerokosc'] == 'normalne') ) {
                                      $DodatkowyCss = ' MenuNormalne ';
                                }

                                //
                                $EfektMenu = 'EfektMenu-1';
                                //
                                if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                     //
                                     $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                     //
                                }
                                //                                
                                $DoWyswietlaniaTmp .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . $DodatkowyCss . 'MenuDrzewoStronyInformacyjne" id="OknoMenu-' . $x . '">';
                                unset($EfektMenu);
                                
                                $DoWyswietlaniaTmpLinki = '';
                                $DoWyswietlaniaTmpBannery = '';                                
                                //                           
                                foreach ( $TablicaStronInformacyjnych as $Strona ) {
                                    //
                                    $AdresLinku = (($Strona['url'] != '') ? $Strona['url'] : Seo::link_SEO( $Strona['tytul'], $Strona['id'], 'strona_informacyjna' ));
                                    //
                                    if ( $AdresLinku == $AktualnyLink ) {
                                         //
                                         $BylLink = true;
                                         //
                                    }
                                    //
                                    $DoWyswietlaniaTmpLinki .= '<li class="LinkiMenu">' . $Strona['link'] . '</li>';
                                    //
                                    unset($AdresLinku);
                                    //
                                }
                                //
                                unset($DodatkowyCss);
                                //
                                if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($PozycjaKonfiguracji['grupa_bannerow']) && !empty($PozycjaKonfiguracji['grupa_bannerow']) && Wyglad::TypSzablonu() == true ) {
                                     //
                                     $GrupaBannerow = $PozycjaKonfiguracji['grupa_bannerow'];
                                     //
                                     if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {

                                          $TablicaBannerow = $GLOBALS['bannery']->info[$GrupaBannerow];
                                          //
                                          if ( count($TablicaBannerow) > 0 ) {
                                               //
                                               if ( isset($PozycjaKonfiguracji['ilosc_bannerow']) ) {
                                                    //
                                                    $TablicaBannerow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, (($PozycjaKonfiguracji['ilosc_bannerow'] == 'jeden') ? 1 : 20));
                                                    //
                                               }
                                               //
                                          }
                                          //
                                          if ( count($TablicaBannerow) > 0 ) {
                                               
                                               $DoWyswietlaniaTmpBannery = '<li class="GrafikiMenu">';
 
                                               foreach ($TablicaBannerow as $Banner ) {
 
                                                   $DoWyswietlaniaBanner = $GLOBALS['bannery']->bannerWyswietlMenu($Banner);
                                                   
                                                   if ( $DoWyswietlaniaBanner != '' ) {
                                                        //
                                                        $DoWyswietlaniaTmpBannery .= '<div>' . $DoWyswietlaniaBanner . '</div>';
                                                        //
                                                   }
                                                   
                                                   unset($DoWyswietlaniaBanner);
 
                                               }
                                               
                                               $DoWyswietlaniaTmpBannery .= '</li>';
                                             
                                          }  
                                          //
                                          unset($TablicaBannerow);                                     
                                          //
                                     }
                                     //
                                     unset($GrupaBannerow);                                     
                                     //
                                }
                                  
                                if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                     //
                                     $DoWyswietlaniaTmp = '';
                                     //
                                }                                  

                                $DoWyswietlaniaTresc = '';
                                //
                                if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $DoWyswietlaniaTmpBannery != '' && Wyglad::TypSzablonu() == true ) { 
                                     //
                                     if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {                                    
                                          $DoWyswietlaniaTresc .= '<li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li><li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li>';
                                     }
                                     if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {   
                                          $DoWyswietlaniaTresc .= '<li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li><li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li>';
                                     }                                       
                                     //
                                } else {
                                     //
                                     $DoWyswietlaniaTresc .= $DoWyswietlaniaTmpLinki . $DoWyswietlaniaTmpBannery;
                                     //
                                }
                                  
                                if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                     //
                                     return $DoWyswietlaniaTresc;
                                     //
                                }    
                                
                                if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                     //
                                     $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                     //
                                }
                                
                                $DoWyswietlaniaTmp .= $DoWyswietlaniaTresc . '</ul>';
                                //
                                unset($DoWyswietlaniaTmpBannery, $DoWyswietlaniaTmpLinki);                                
                                //
                            }
                            //
                            unset($GrupyStron, $TablicaStronInformacyjnych, $NazwaGrupy, $PozycjaKonfiguracji);
                            //
                            $DoWyswietlaniaTmp .= $tagKoniec;
                            
                            if ( $BylLink == true ) {
                                 //
                                 $DoWyswietlaniaTmp = str_replace('class="MenuLinkStronyInformacyjne"', 'class="AktywnyLinkMenu MenuLinkStronyInformacyjne"', (string)$DoWyswietlaniaTmp);
                                 //
                            }
                            
                            $DoWyswietlania .= $DoWyswietlaniaTmp;
                            
                            unset($DoWyswietlaniaTmp, $BylLink);
                            //
                        }
                        //
                        break; 
                    case "prodkategorie":
                        //
                        $NazwaKategorii = Kategorie::NazwaKategoriiId($strona[1], true);
                        
                        if ( $NazwaKategorii != '' ) {
                        
                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$strona[1] . '|katprod']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|katprod'];
                            }                         

                            if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                 //
                                 $FlagaPozycji = '';
                                 //
                                 if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                      //
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                           isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                           //
                                           $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                           //
                                      }
                                      //
                                 }
                                 //                                   
                                 $NazwaKategorii = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaKategorii) . '">' . $FlagaPozycji . $NazwaKategorii . '</b>';
                                 //
                                 unset($FlagaPozycji);
                                 //
                            }      

                            // dodanie ikonki jak jest
                            if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                 //
                                 if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                      //
                                      list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                      //
                                      if ( (int)$szerokosc == 0 ) {
                                           $szerokosc = 100;
                                      }
                                      if ( (int)$wysokosc == 0 ) {
                                           $wysokosc = 100;
                                      }                  
                                      //
                                      $NazwaKategorii = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaKategorii) . '" /></i>' . $NazwaKategorii;
                                      //
                                 }
                                 //
                            }
                            //
                            $NazwaKategoriiSeo = Kategorie::NazwaKategoriiSeoId($strona[1]);
                            //
                            if ( !empty($NazwaKategorii) ) {
                                //
                                
                                $DodatkowyCss = array();

                                // kolor linku
                                if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                     //
                                     if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                          $DodatkowyCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                     }
                                     //
                                }
                                
                                // kolor tla
                                if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                     //
                                     if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                          $DodatkowyCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                     }
                                     //
                                }  
                                
                                $TablicaPodkategorii = Kategorie::TablicaKategorieParent($strona[1]);

                                $tagPoczatekTmp = $tagPoczatek;
                                
                                if ( Wyglad::TypSzablonu() == true ) {
                                     //
                                     $MenuPreloader = '';
                                     //          
                                     if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                          //
                                          $MenuPreloader = ' PozycjaMenuPreloader';
                                          //
                                     } 
                                     //
                                     $CssMenu = 'class="PozycjaMenuNormalne' . $MenuPreloader . '"';
                                     //
                                     if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                          //
                                          $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                          //
                                     }
                                     if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                          //
                                          $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                          //
                                     }
                                     if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                          //
                                          $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                          //
                                     }
                                     if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                          //
                                          $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                          //
                                     }
                                     //
                                     $tagPoczatekTmp = str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp);
                                     //
                                     unset($CssMenu);
                                     //
                                }
                                
                                $DoWyswietlaniaTmp = $tagPoczatekTmp;
                                
                                unset($tagPoczatekTmp);
                                
                                if ( Wyglad::TypSzablonu() == true ) {
                                     //
                                     $DoWyswietlaniaTmp .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';
                                     //
                                }

                                $DoWyswietlaniaTmp .= '<a ' . ((count((array)$DodatkowyCss) > 0) ? 'style="' . implode(';', (array)$DodatkowyCss) . '"' : '') . ' href="' . Seo::link_SEO( $NazwaKategoriiSeo, $strona[1], 'kategoria') . '" ' . (($AktualnyLink == Seo::link_SEO( $NazwaKategoriiSeo, $strona[1], 'kategoria')) ? ' class="AktywnyLinkMenu MenuLinkKategorie' . ((count($TablicaPodkategorii) > 0) ? ' PozycjaRozwijanaMenu' : '') . '"' : ' class="MenuLinkKategorie' . ((count($TablicaPodkategorii) > 0) ? ' PozycjaRozwijanaMenu' : '') . '"') . '>' . $NazwaKategorii . '</a>';
                                
                                if ( Wyglad::TypSzablonu() == true ) {
                                     //
                                     $DoWyswietlaniaTmp .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu" tabindex="0" role="button"></label></div>';
                                     //
                                }   
                                
                                unset($DodatkowyCss); 
                                //
                                if ( count($TablicaPodkategorii) > 0 ) {

                                    $PokazPodkategorie = false;
                                    $poziomMenuWszystkieKategorie = 1;
                                    $DodatkowyCss = array();
                                    
                                    if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                          //
                                          $CssMenu = 'MenuSzerokie';
                                          //
                                          if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                               $CssMenu = 'MenuSzerokie Menu30Procent';
                                          }
                                          if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                               $CssMenu = 'MenuSzerokie Menu50Procent';
                                          }
                                          if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                               $CssMenu = 'MenuSzerokie Menu70Procent';
                                          }
                                          //
                                          $DodatkowyCss[] = ' ' . $CssMenu . ' MenuSzerokie-' . $PozycjaKonfiguracji['ilosc_kolumn'];
                                          //
                                          unset($CssMenu);
                                          //                                       
                                          if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' ) {                                              
                                               $DodatkowyCss[] = 'GrafikiNadPodLinkami GrafikiNadLinkami';
                                          }
                                          if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' ) {
                                               $DodatkowyCss[] = 'GrafikiNadPodLinkami GrafikiPodLinkami';
                                          }                                          
                                          if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {
                                               $DodatkowyCss[] = 'GrafikiPrawaLewaStrona GrafikiPrawaStrona';
                                          }                                                                                    
                                          if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {
                                               $DodatkowyCss[] = 'GrafikiPrawaLewaStrona GrafikiLewaStrona';
                                          }                                                                                                                              
                                    }
                                    if ( !isset($PozycjaKonfiguracji['szerokosc']) || (isset($PozycjaKonfiguracji['szerokosc']) && empty($PozycjaKonfiguracji['szerokosc']) || $PozycjaKonfiguracji['szerokosc'] == 'normalne') ) {
                                          $DodatkowyCss = array('MenuNormalne');
                                    }
                                    if ( isset($PozycjaKonfiguracji['podkategorie']) && $PozycjaKonfiguracji['podkategorie'] == 'tak' ) {
                                          //
                                          if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                               $poziomMenuWszystkieKategorie = 2;
                                               //
                                               if ( isset($PozycjaKonfiguracji['glebokosc_drzewa']) ) {
                                                    if ( (int)$PozycjaKonfiguracji['glebokosc_drzewa'] == 1 ) {
                                                         $poziomMenuWszystkieKategorie = 2;
                                                    }
                                                    if ( (int)$PozycjaKonfiguracji['glebokosc_drzewa'] == 2 ) {
                                                         $poziomMenuWszystkieKategorie = 3;
                                                    }                                                      
                                               }
                                               //                                               
                                               $DodatkowyCss[] = 'MenuWielopoziomoweSzerokie';
                                          } else {
                                               $poziomMenuWszystkieKategorie = $poziomMenu;
                                               //
                                               // jezeli jest ilosc glebokosc drzewa
                                               if ( isset($PozycjaKonfiguracji['glebokosc_drzewa']) ) {
                                                    $poziomMenuWszystkieKategorie = (int)$PozycjaKonfiguracji['glebokosc_drzewa'] + 1;
                                                    $DodatkowyCss[] = 'MenuWielopoziomoweNormalne';
                                               }
                                          }
                                          //
                                    }

                                    //
                                    $EfektMenu = 'EfektMenu-1';
                                    //
                                    if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                         //
                                         $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                         //
                                    }
                                    //  
                                    $DoWyswietlaniaTmp .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . ((count((array)$DodatkowyCss) > 0) ? ' ' . implode(' ', (array)$DodatkowyCss) . ' ' : ' ') . 'MenuDrzewoKategorie" id="OknoMenu-' . $x . '">';
                                    unset($EfektMenu);
                                    
                                    $DoWyswietlaniaTmpLinki = '';
                                    $DoWyswietlaniaTmpBannery = '';  
                                    
                                    unset($DodatkowyCss);

                                    // wsteczna kombatybilnosc
                                    if ( Wyglad::TypSzablonu() == false ) {
                                         //
                                         if ( $pelneDrzewo == true ) {                                       
                                              $poziomMenuWszystkieKategorie = 2;
                                         } else {
                                              $poziomMenuWszystkieKategorie = 1;
                                         }
                                         //
                                    }
                                    //
                                    foreach ( Kategorie::DrzewoKategorii($strona[1]) as $IdKategorii => $Tablica ) {
                                          //
                                          $DoWyswietlaniaTmpLinki .= Kategorie::WyswietlKategorieGorneMenu($Tablica, $strona[1], '', $poziomMenuWszystkieKategorie, 1, ((isset($PozycjaKonfiguracji['wysokosc_kolumn']) && isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie') ? $PozycjaKonfiguracji['wysokosc_kolumn'] : 0), $PozycjaKonfiguracji);
                                          //
                                    }

                                    if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($PozycjaKonfiguracji['grupa_bannerow']) && !empty($PozycjaKonfiguracji['grupa_bannerow']) && Wyglad::TypSzablonu() == true ) {
                                         //
                                         $GrupaBannerow = $PozycjaKonfiguracji['grupa_bannerow'];
                                         //
                                         if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {
              
                                              $TablicaBannerow = $GLOBALS['bannery']->info[$GrupaBannerow];
                                              //
                                              if ( count($TablicaBannerow) > 0 ) {
                                                   //
                                                   if ( isset($PozycjaKonfiguracji['ilosc_bannerow']) ) {
                                                        //
                                                        $TablicaBannerow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, (($PozycjaKonfiguracji['ilosc_bannerow'] == 'jeden') ? 1 : 20));
                                                        //
                                                   }
                                                   //
                                              }
                                              //
                                              if ( count($TablicaBannerow) > 0 ) {
                                                   
                                                   $DoWyswietlaniaTmpBannery = '<li class="GrafikiMenu">';
     
                                                   foreach ($TablicaBannerow as $Banner ) {
     
                                                       $DoWyswietlaniaBanner = $GLOBALS['bannery']->bannerWyswietlMenu($Banner);
                                                       
                                                       if ( $DoWyswietlaniaBanner != '' ) {
                                                            //
                                                            $DoWyswietlaniaTmpBannery .= '<div>' . $DoWyswietlaniaBanner . '</div>';
                                                            //
                                                       }
                                                       
                                                       unset($DoWyswietlaniaBanner);
     
                                                   }
                                                   
                                                   $DoWyswietlaniaTmpBannery .= '</li>';
                                                 
                                              }  
                                              //
                                              unset($TablicaBannerow);                                     
                                              //
                                         }
                                         //
                                         unset($GrupaBannerow);                                     
                                         //
                                    }                                
                                      
                                    if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                         //
                                         $DoWyswietlaniaTmp = '';
                                         //
                                    }                                  

                                    $DoWyswietlaniaTresc = '';
                                    //
                                    if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $DoWyswietlaniaTmpBannery != '' && Wyglad::TypSzablonu() == true ) { 
                                         //
                                         if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {                                    
                                              $DoWyswietlaniaTresc .= '<li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li><li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li>';
                                         }
                                         if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {   
                                              $DoWyswietlaniaTresc .= '<li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li><li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li>';
                                         }                                       
                                         //
                                    } else {
                                         //
                                         $DoWyswietlaniaTresc .= $DoWyswietlaniaTmpLinki . $DoWyswietlaniaTmpBannery;
                                         //
                                    }
                                      
                                    if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                         //
                                         return $DoWyswietlaniaTresc;
                                         //
                                    }    
                                    
                                    if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                         //
                                         $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                         //
                                    }
                                    
                                    $DoWyswietlaniaTmp .= $DoWyswietlaniaTresc . '</ul>';
                                    //
                                    unset($DoWyswietlaniaTmpBannery, $DoWyswietlaniaTmpLinki);                                
                                    //
                                }
                                //
                                unset($TablicaPodkategorii);
                                //
                                $DoWyswietlaniaTmp .= $tagKoniec;
                                
                                if ( !empty($AktualnyLink) ) {
                                  
                                    if ( strpos((string)$DoWyswietlaniaTmp, (string)$AktualnyLink) > -1 ) {
                                         //
                                         $DoWyswietlaniaTmp = str_replace('class="MenuLinkKategorie"', 'class="AktywnyLinkMenu MenuLinkKategorie"', (string)$DoWyswietlaniaTmp);
                                         //
                                    }
                                    
                                }

                                $DoWyswietlania .= $DoWyswietlaniaTmp;                            
                                //
                                unset($DoWyswietlaniaTmp);
                            }
                            //
                            unset($NazwaKategoriiSeo, $PozycjaKonfiguracji);

                        }
                        
                        unset($NazwaKategorii);

                        break;                         
                    case "linkbezposredni":
                        //
                        $RodzielenieAdresu = explode('adreslinku', (string)$strona[1]);
                        
                        $PozycjaKonfiguracji = array();
                        if ( isset($Podkategorie[$strona[1] . '|linkbezposredni']) ) {
                             $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|linkbezposredni'];
                        }                        
                        //
                        if ( count($RodzielenieAdresu) > 1 ) {
                             //
                             $DaneRozdzielone = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$RodzielenieAdresu[1]));
                             $TablicaLinku = unserialize($DaneRozdzielone);  
                             //
                             if ( isset($TablicaLinku['linkbezposredni']) && isset($TablicaLinku['jezyk_' . (int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                  //
                                  $NazwaLinku = $TablicaLinku['jezyk_' . (int)$_SESSION['domyslnyJezyk']['id']];
                                  
                                  if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                       //
                                       $FlagaPozycji = '';
                                       //
                                       if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                            //
                                            if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                                 isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                                 isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                                 //
                                                 $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                                 //
                                            }
                                            //
                                       }
                                       //                                       
                                       $NazwaLinku = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaLinku) . '">' . $FlagaPozycji . $NazwaLinku . '</b>';
                                       //
                                       unset($FlagaPozycji);
                                       //
                                  }                                    
                                  
                                  // dodanie ikonki jak jest
                                  if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                            //
                                            list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                            //
                                            if ( (int)$szerokosc == 0 ) {
                                                 $szerokosc = 100;
                                            }
                                            if ( (int)$wysokosc == 0 ) {
                                                 $wysokosc = 100;
                                            }                  
                                            //
                                            $NazwaLinku = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaLinku) . '" /></i>' . $NazwaLinku;
                                            //
                                       }
                                       //
                                  }
                                  
                                  $DodatkoweCss = array();
                                  
                                  // kolor linku
                                  if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                            $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                       }
                                       //
                                  }
                                  
                                  // kolor tla
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' ) {
                                       //
                                       if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                            $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                       }
                                       //
                                  }                                  
                                  // 
                                  $DoWyswietlania .= $tagPoczatek . ((Wyglad::TypSzablonu() == true ) ? '<div>' : '') . '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="' . $TablicaLinku['linkbezposredni'] . '"' . ((isset($TablicaLinku['nowa_strona'])) ? ' target="_blank"' : '') . (($AktualnyLink == $TablicaLinku['linkbezposredni']) ? ' class="AktywnyLinkMenu"' : '') . '>' . $NazwaLinku . '</a>' . ((Wyglad::TypSzablonu() == true ) ? '</div>' : '') . $tagKoniec;
                                  //
                                  unset($NazwaLinku, $DodatkoweCss); 
                                  //
                             }
                             unset($DaneRozdzielone, $TablicaLinku);
                             //
                        }
                        //
                        unset($RodzielenieAdresu, $PozycjaKonfiguracji);
                        //
                        break;               
                    case "linkwszystkiekategorie":
                        //
                        $RodzielenieAdresu = explode('nazwapozycji', (string)$strona[1]);
                        
                        $PozycjaKonfiguracji = array();
                        if ( isset($Podkategorie['99999998|katprod']) ) {
                             $PozycjaKonfiguracji = $Podkategorie['99999998|katprod'];
                        }
                        
                        //
                        if ( count($RodzielenieAdresu) > 1 ) {
                             //
                             $DaneRozdzielone = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$RodzielenieAdresu[1]));
                             $TablicaLinku = unserialize($DaneRozdzielone);  
                             //
                             if ( isset($TablicaLinku['menu_kategorie_jezyk_' . (int)$_SESSION['domyslnyJezyk']['id']]) ) {

                                  $PokazPodkategorie = false;
                                  $poziomMenuWszystkieKategorie = 1;
                                  $DodatkowyCss = array();

                                  if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                        //
                                        $CssMenu = 'MenuSzerokie';
                                        //
                                        if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                             $CssMenu = 'MenuSzerokie Menu30Procent';
                                        }
                                        if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                             $CssMenu = 'MenuSzerokie Menu50Procent';
                                        }
                                        if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                             $CssMenu = 'MenuSzerokie Menu70Procent';
                                        }
                                        //
                                        $DodatkowyCss[] = ' ' . $CssMenu . ' MenuSzerokie-' . $PozycjaKonfiguracji['ilosc_kolumn'];
                                        //
                                        unset($CssMenu);
                                        //
                                        if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' ) {                                              
                                             $DodatkowyCss[] = 'GrafikiNadPodLinkami GrafikiNadLinkami';
                                        }
                                        if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' ) {
                                             $DodatkowyCss[] = 'GrafikiNadPodLinkami GrafikiPodLinkami';
                                        }                                          
                                        if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {
                                             $DodatkowyCss[] = 'GrafikiPrawaLewaStrona GrafikiPrawaStrona';
                                        }                                                                                    
                                        if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {
                                             $DodatkowyCss[] = 'GrafikiPrawaLewaStrona GrafikiLewaStrona';
                                        }                                                    
                                  }
                                  if ( !isset($PozycjaKonfiguracji['szerokosc']) || (isset($PozycjaKonfiguracji['szerokosc']) && empty($PozycjaKonfiguracji['szerokosc']) || $PozycjaKonfiguracji['szerokosc'] == 'normalne') ) {
                                        $DodatkowyCss = array('MenuNormalne');
                                  }
                                  if ( isset($PozycjaKonfiguracji['podkategorie']) && $PozycjaKonfiguracji['podkategorie'] == 'tak' ) {
                                        //
                                        if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                             $poziomMenuWszystkieKategorie = 2;
                                             //
                                             if ( isset($PozycjaKonfiguracji['glebokosc_drzewa']) ) {
                                                  if ( (int)$PozycjaKonfiguracji['glebokosc_drzewa'] == 1 ) {
                                                       $poziomMenuWszystkieKategorie = 2;
                                                  }
                                                  if ( (int)$PozycjaKonfiguracji['glebokosc_drzewa'] == 2 ) {
                                                       $poziomMenuWszystkieKategorie = 3;
                                                  }                                                      
                                             }
                                             //
                                             $DodatkowyCss[] = 'MenuWielopoziomoweSzerokie';
                                        } else {
                                             $poziomMenuWszystkieKategorie = $poziomMenu;
                                             //
                                             // jezeli jest ilosc glebokosc drzewa
                                             if ( isset($PozycjaKonfiguracji['glebokosc_drzewa']) ) {
                                                  $poziomMenuWszystkieKategorie = (int)$PozycjaKonfiguracji['glebokosc_drzewa'] + 1;                                                      
                                             }              
                                             $DodatkowyCss[] = 'MenuWielopoziomoweNormalne';
                                        }
                                        //
                                  }

                                  $NazwaKategorii = $TablicaLinku['menu_kategorie_jezyk_' . (int)$_SESSION['domyslnyJezyk']['id']];
                                  
                                  if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                       //
                                       $FlagaPozycji = '';
                                       //
                                       if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                            //
                                            if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                                 isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                                 isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                                 //
                                                 $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                                 //
                                            }
                                            //
                                       }
                                       //
                                       $NazwaKategorii = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaKategorii) . '">' . $FlagaPozycji . $NazwaKategorii . '</b>';
                                       //
                                       unset($FlagaPozycji);
                                       //
                                  }                                     
                                  
                                  // dodanie ikonki jak jest
                                  if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                            //
                                            list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                            //
                                            if ( (int)$szerokosc == 0 ) {
                                                 $szerokosc = 100;
                                            }
                                            if ( (int)$wysokosc == 0 ) {
                                                 $wysokosc = 100;
                                            }                  
                                            //
                                            $NazwaKategorii = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaKategorii) . '" /></i>' . $NazwaKategorii;
                                            //
                                       }
                                       //
                                  }
                                  
                                  $DodatkoweCss = array();
                                  
                                  // kolor linku
                                  if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                            $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                       }
                                       //
                                  }
                                  
                                  // kolor tla
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                            $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                       }
                                       //
                                  }                                  

                                  $tagPoczatekTmp = $tagPoczatek;
                                  
                                  if ( Wyglad::TypSzablonu() == true ) {
                                       //
                                       $MenuPreloader = '';
                                       //          
                                       if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                            //
                                            $MenuPreloader = ' PozycjaMenuPreloader';
                                            //
                                       } 
                                       //
                                       $CssMenu = 'class="PozycjaMenuNormalne' . $MenuPreloader . '"';
                                       //
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                            //
                                       }
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                            //
                                       }
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                            //
                                       }
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                            //
                                       }                                      
                                       //
                                       $tagPoczatekTmp = str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp);
                                       //
                                       unset($CssMenu);
                                       //
                                  }

                                  $DoWyswietlania .= $tagPoczatekTmp;
                                  
                                  unset($tagPoczatekTmp);
                                  
                                  if ( Wyglad::TypSzablonu() == true ) {
                                       //
                                       $DoWyswietlania .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';
                                       //
                                  }

                                  $DoWyswietlania .= '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="kategorie.html"' . (($AktualnyLink == 'kategorie.html' || isset($_GET['idkat'])) ? ' class="AktywnyLinkMenu MenuLinkWszystkieKategorie MenuLinkKategorie PozycjaRozwijanaMenu"' : ' class="MenuLinkKategorie MenuLinkWszystkieKategorie PozycjaRozwijanaMenu"') . '>' . $NazwaKategorii . '</a>';
                                  
                                  if ( Wyglad::TypSzablonu() == true ) {
                                       //
                                       $DoWyswietlania .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu" tabindex="0" role="button"></label></div>';
                                       //
                                  }   

                                  //
                                  $EfektMenu = 'EfektMenu-1';
                                  //
                                  if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                       //
                                       $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                       //
                                  }
                                  //  
                                  $DoWyswietlania .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . ((count((array)$DodatkowyCss) > 0) ? ' ' . implode(' ', (array)$DodatkowyCss) . ' ' : ' ') . 'MenuDrzewoKategorie" id="OknoMenu-' . $x . '">';
                                  unset($EfektMenu);
                                  
                                  $DoWyswietlaniaTmpLinki = '';
                                  $DoWyswietlaniaTmpBannery = '';  
                                  
                                  unset($NazwaKategorii, $DodatkoweCss, $DodatkowyCss);   
                                  
                                  // wsteczna kombatybilnosc
                                  if ( Wyglad::TypSzablonu() == false ) {
                                       //
                                       if ( $pelneDrzewo == true ) {                                       
                                            $poziomMenuWszystkieKategorie = 2;
                                       } else {
                                            $poziomMenuWszystkieKategorie = 1;
                                       }
                                       //
                                  }
                                  //                                  
                                  foreach ( Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica ) {
                                        //
                                        $DoWyswietlaniaTmpLinki .= Kategorie::WyswietlKategorieGorneMenu($Tablica, '', '', $poziomMenuWszystkieKategorie, 1, ((isset($PozycjaKonfiguracji['wysokosc_kolumn']) && isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent'))) ? $PozycjaKonfiguracji['wysokosc_kolumn'] : 0), $PozycjaKonfiguracji);
                                        //
                                  }
                                  
                                  unset($DodatkowyCss);

                                  if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($PozycjaKonfiguracji['grupa_bannerow']) && !empty($PozycjaKonfiguracji['grupa_bannerow']) && Wyglad::TypSzablonu() == true ) {
                                       //
                                       $DoWyswietlaniaTmpBannery = '<li class="GrafikiMenu">';
                                       //
                                       $GrupaBannerow = $PozycjaKonfiguracji['grupa_bannerow'];
                                       //
                                       if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {
                                            //
                                            $TablicaBannerow = $GLOBALS['bannery']->info[$GrupaBannerow];
                                            //
                                            if ( count($TablicaBannerow) > 0 ) {
                                                 //
                                                 if ( isset($PozycjaKonfiguracji['ilosc_bannerow']) ) {
                                                      //
                                                      $TablicaBannerow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, (($PozycjaKonfiguracji['ilosc_bannerow'] == 'jeden') ? 1 : 20));
                                                      //
                                                 }
                                                 //
                                            }
                                            //
                                            if ( count($TablicaBannerow) > 0 ) {

                                                 foreach ($TablicaBannerow as $Banner ) {
 
                                                     $DoWyswietlaniaBanner = $GLOBALS['bannery']->bannerWyswietlMenu($Banner);
                                                     
                                                     if ( $DoWyswietlaniaBanner != '' ) {
                                                          //
                                                          $DoWyswietlaniaTmpBannery .= '<div>' . $DoWyswietlaniaBanner . '</div>';
                                                          //
                                                     }
                                                     
                                                     unset($DoWyswietlaniaBanner);
 
                                                 }

                                            }  
                                            //
                                            unset($TablicaBannerow);                                     
                                            //
                                       }
                                       //
                                       $DoWyswietlaniaTmpBannery .= '</li>';
                                       //
                                       unset($GrupaBannerow);                                     
                                       //
                                  }       
                                  
                                  if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                       //
                                       $DoWyswietlania = '';
                                       //
                                  }                                  
 
                                  $DoWyswietlaniaTresc = '';
                                  //
                                  if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $DoWyswietlaniaTmpBannery != '' && Wyglad::TypSzablonu() == true ) { 
                                       //
                                       if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {                                    
                                            $DoWyswietlaniaTresc .= '<li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li><li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li>';
                                       }
                                       if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {   
                                            $DoWyswietlaniaTresc .= '<li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li><li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li>';
                                       }                                       
                                       //
                                  } else {
                                       //
                                       $DoWyswietlaniaTresc .= $DoWyswietlaniaTmpLinki . $DoWyswietlaniaTmpBannery;
                                       //
                                  }
                                  
                                  if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                       //
                                       return $DoWyswietlaniaTresc;
                                       //
                                  }    
                                  
                                  if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                       //
                                       $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                       //
                                  }
                                  
                                  $DoWyswietlania .= $DoWyswietlaniaTresc . '</ul>';
                                  //
                                  unset($DoWyswietlaniaTmpBannery, $DoWyswietlaniaTmpLinki);                                
                                  //                                   
                                  $DoWyswietlania .= $tagKoniec;
                                  //
                                  unset($poziomMenuWszystkieKategorie);
                                  //
                             }
                             //
                             unset($DaneRozdzielone, $TablicaLinku);
                             //
                        }
                        //
                        unset($RodzielenieAdresu, $PozycjaKonfiguracji);
                        //
                        break;
                    case "linkwszyscyproducenci":
                        //
                        $RodzielenieAdresu = explode('nazwapozycji', (string)$strona[1]);
                        
                        $PozycjaKonfiguracji = array();
                        if ( isset($Podkategorie['99999999|producenci']) ) {
                             $PozycjaKonfiguracji = $Podkategorie['99999999|producenci'];
                        }                        
                        
                        //
                        if ( count($RodzielenieAdresu) > 1 ) {
                             //
                             $DaneRozdzielone = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$RodzielenieAdresu[1]));
                             $TablicaLinku = unserialize($DaneRozdzielone);  
                             //
                             if ( isset($TablicaLinku['menu_producenci_jezyk_' . (int)$_SESSION['domyslnyJezyk']['id']]) ) {
                               
                                  $NazwaPozycji = $TablicaLinku['menu_producenci_jezyk_' . (int)$_SESSION['domyslnyJezyk']['id']];
                                  
                                  if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                                       //
                                       $FlagaPozycji = '';
                                       //
                                       if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                            //
                                            if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                                 isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                                 isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                                 //
                                                 $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                                 //
                                            }
                                            //
                                       }
                                       //                                        
                                       $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                                       //
                                       unset($FlagaPozycji);
                                       //
                                  }  
                                  
                                  // dodanie ikonki jak jest
                                  if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                            //
                                            list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                            //
                                            if ( (int)$szerokosc == 0 ) {
                                                 $szerokosc = 100;
                                            }
                                            if ( (int)$wysokosc == 0 ) {
                                                 $wysokosc = 100;
                                            }                  
                                            //
                                            $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                            //
                                       }
                                       //
                                  }
                                  
                                  $DodatkoweCss = array();
                                  
                                  // kolor linku
                                  if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                            $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                       }
                                       //
                                  }
                                  
                                  // kolor tla
                                  if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' && $rodzaj == 'gorne_menu' && Wyglad::TypSzablonu() == true ) {
                                       //
                                       if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                            $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                       }
                                       //
                                  }                                  
                                  
                                  $tagPoczatekTmp = $tagPoczatek;
                                  
                                  if ( Wyglad::TypSzablonu() == true ) {
                                       //
                                       $MenuPreloader = '';
                                       //          
                                       if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                            //
                                            $MenuPreloader = ' PozycjaMenuPreloader';
                                            //
                                       } 
                                       //
                                       $CssMenu = 'class="PozycjaMenuNormalne' . $MenuPreloader . '"';
                                       //
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == 'szerokie' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                            //
                                       }
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                            //
                                       }
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                            //
                                       }
                                       if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                            //
                                            $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                            //
                                       }
                                       //
                                       $tagPoczatekTmp = str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp);
                                       //
                                       unset($CssMenu);
                                       //
                                  }
                            
                                  $DoWyswietlania .= $tagPoczatekTmp;
                                  
                                  unset($tagPoczatekTmp);
                                  
                                  if ( Wyglad::TypSzablonu() == true ) {
                                       //
                                       $DoWyswietlania .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';
                                       //
                                  }

                                  $DoWyswietlania .= '<a ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . ' href="producenci.html"' . (($AktualnyLink == 'producenci.html' || isset($_GET['idkat'])) ? ' class="AktywnyLinkMenu MenuLinkWszyscyProducenci MenuLinkKategorie PozycjaRozwijanaMenu"' : ' class="MenuLinkKategorie MenuLinkWszyscyProducenci PozycjaRozwijanaMenu"') . '>' . $NazwaPozycji . '</a>';
                                  
                                  if ( Wyglad::TypSzablonu() == true ) {
                                       //
                                       $DoWyswietlania .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu" tabindex="0" role="button"></label></div>';
                                       //
                                  }   
                                  //
                                  unset($NazwaPozycji, $DodatkoweCss);
                                  //
                                  $TablicaProducenci = Producenci::TablicaProducenci();
                                  //
                                  if ( count($TablicaProducenci) > 0 ) {
                                    
                                      $DodatkowyCss = ' ';
                                             
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                            //
                                            $CssMenu = 'MenuSzerokie';
                                            //
                                            if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                                 $CssMenu = 'MenuSzerokie Menu30Procent';
                                            }
                                            if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                                 $CssMenu = 'MenuSzerokie Menu50Procent';
                                            }
                                            if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                                 $CssMenu = 'MenuSzerokie Menu70Procent';
                                            }
                                            //
                                            $DodatkowyCss = ' ' . $CssMenu . ' MenuSzerokie-' . $PozycjaKonfiguracji['ilosc_kolumn'] . ' ';
                                            //
                                            unset($CssMenu);
                                            //
                                            if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' ) {                                              
                                                 $DodatkowyCss .= 'GrafikiNadPodLinkami GrafikiNadLinkami ';
                                            }
                                            if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' ) {
                                                 $DodatkowyCss .= 'GrafikiNadPodLinkami GrafikiPodLinkami ';
                                            }                                          
                                            if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {
                                                 $DodatkowyCss .= 'GrafikiPrawaLewaStrona GrafikiPrawaStrona ';
                                            }                                                                                    
                                            if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {
                                                 $DodatkowyCss .= 'GrafikiPrawaLewaStrona GrafikiLewaStrona ';
                                            }                                                
                                      }
                                      if ( !isset($PozycjaKonfiguracji['szerokosc']) || (isset($PozycjaKonfiguracji['szerokosc']) && empty($PozycjaKonfiguracji['szerokosc']) || $PozycjaKonfiguracji['szerokosc'] == 'normalne') ) {
                                            $DodatkowyCss = ' MenuNormalne ';
                                      }    

                                      //
                                      $EfektMenu = 'EfektMenu-1';
                                      //
                                      if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                           //
                                           $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                           //
                                      }
                                      //  
                                      $DoWyswietlania .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . $DodatkowyCss . 'MenuDrzewoKategorie" id="OknoMenu-' . $x . '">';
                                      unset($EfektMenu);
                                      
                                      $DoWyswietlaniaTmpLinki = '';
                                      $DoWyswietlaniaTmpBannery = ''; 
                                  
                                      foreach ( $TablicaProducenci as $Producent ) {
                                          //
                                          $DoWyswietlaniaTmpLinki .= '<li class="LinkiMenu"><a href="' . Seo::link_SEO( $Producent['Nazwa'], $Producent['IdProducenta'], 'producent' ) . '">' . $Producent['Nazwa'] . '</a></li>';
                                          //
                                      }
                                      //
                                      unset($DodatkowyCss);
                                      //
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($PozycjaKonfiguracji['grupa_bannerow']) && !empty($PozycjaKonfiguracji['grupa_bannerow']) && Wyglad::TypSzablonu() == true ) {
                                           //
                                           $GrupaBannerow = $PozycjaKonfiguracji['grupa_bannerow'];
                                           //
                                           if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {
                                             
                                                $TablicaBannerow = $GLOBALS['bannery']->info[$GrupaBannerow];
                                                //
                                                if ( count($TablicaBannerow) > 0 ) {
                                                     //
                                                     if ( isset($PozycjaKonfiguracji['ilosc_bannerow']) ) {
                                                          //
                                                          $TablicaBannerow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, (($PozycjaKonfiguracji['ilosc_bannerow'] == 'jeden') ? 1 : 20));
                                                          //
                                                     }
                                                     //
                                                }
                                                //
                                                if ( count($TablicaBannerow) > 0 ) {
                                                     
                                                     $DoWyswietlaniaTmpBannery = '<li class="GrafikiMenu">';
 
                                                     foreach ($TablicaBannerow as $Banner ) {
 
                                                         $DoWyswietlaniaBanner = $GLOBALS['bannery']->bannerWyswietlMenu($Banner);
                                                         
                                                         if ( $DoWyswietlaniaBanner != '' ) {
                                                              //
                                                              $DoWyswietlaniaTmpBannery .= '<div>' . $DoWyswietlaniaBanner . '</div>';
                                                              //
                                                         }
                                                         
                                                         unset($DoWyswietlaniaBanner);
 
                                                     }
                                                     
                                                     $DoWyswietlaniaTmpBannery .= '</li>';
                                                   
                                                }  
                                                //
                                                unset( $TablicaBannerow);                                     
                                                //                                               
                                           }
                                           //
                                           unset($GrupaBannerow);                                     
                                           //
                                      }                                         
                                  
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                           //
                                           $DoWyswietlania = '';
                                           //
                                      }                                  
     
                                      $DoWyswietlaniaTresc = '';
                                      //
                                      if ( isset($PozycjaKonfiguracji['polozenie_bannerow']) && $DoWyswietlaniaTmpBannery != '' && Wyglad::TypSzablonu() == true ) { 
                                           //
                                           if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'nad_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'lewa_strona' ) {                                    
                                                $DoWyswietlaniaTresc .= '<li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li><li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li>';
                                           }
                                           if ( $PozycjaKonfiguracji['polozenie_bannerow'] == 'pod_linkami' || $PozycjaKonfiguracji['polozenie_bannerow'] == 'prawa_strona' ) {   
                                                $DoWyswietlaniaTresc .= '<li class="LinkiMenuKontenter"><ul>' . $DoWyswietlaniaTmpLinki . '</ul></li><li class="GrafikiMenuKontener' . ((isset($PozycjaKonfiguracji['mobile_bannery']) && $PozycjaKonfiguracji['mobile_bannery'] == 'tak') ? ' GrafikiMenuMobilePokaz' : '') . '"><ul>' . $DoWyswietlaniaTmpBannery . '</ul></li>';
                                           }                                       
                                           //
                                      } else {
                                           //
                                           $DoWyswietlaniaTresc .= $DoWyswietlaniaTmpLinki . $DoWyswietlaniaTmpBannery;
                                           //
                                      }
                                      //
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                           //
                                           return $DoWyswietlaniaTresc;
                                           //
                                      }    
                                      
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                           //
                                           $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                           //
                                      }
                                      
                                      $DoWyswietlania .= $DoWyswietlaniaTresc . '</ul>';
                                      //
                                      unset($DoWyswietlaniaTmpBannery, $DoWyswietlaniaTmpLinki);                                
                                      //                                    
                                  }     
                                  //
                                  $DoWyswietlania .= $tagKoniec;
                                  //
                                  unset($TablicaProducenci);
                                  //
                             }
                             //
                             unset($DaneRozdzielone, $TablicaLinku);
                             //
                        }
                        //
                        unset($RodzielenieAdresu, $PozycjaKonfiguracji);
                        //
                        break;              
                    case "pozycjabannery":
                        //
                        if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                          
                            $RodzieleniePozycji = explode('tylkografiki', (string)$strona[1]);
                            
                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$strona[1] . '|pozycjabannery']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|pozycjabannery'];
                            }                        

                            if ( count($RodzieleniePozycji) > 0 ) {
                                 //
                                 $DaneRozdzielone = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$RodzieleniePozycji[1]));
                                 $TablicaPozycji = unserialize($DaneRozdzielone);  
                                 //
                                 if ( isset($TablicaPozycji['jezyk_bannery_' . (int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                      //
                                      $NazwaPozycji = $TablicaPozycji['jezyk_bannery_' . (int)$_SESSION['domyslnyJezyk']['id']];
                                      
                                      $FlagaPozycji = '';
                                      //
                                      if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                           //
                                           if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                                isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                                isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                                //
                                                $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                                //
                                           }
                                           //
                                      }
                                      //                                        
                                      $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                                      //
                                      unset($FlagaPozycji);                        
                                
                                      // dodanie ikonki jak jest
                                      if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' ) {
                                           //
                                           if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                                //
                                                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                                //
                                                if ( (int)$szerokosc == 0 ) {
                                                     $szerokosc = 100;
                                                }
                                                if ( (int)$wysokosc == 0 ) {
                                                     $wysokosc = 100;
                                                }                  
                                                //
                                                $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                                //
                                           }
                                           //
                                      }
                                
                                      $DodatkoweCss = array();
                                      
                                      // kolor linku
                                      if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' ) {
                                           //
                                           if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                                $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                           }
                                           //
                                      }
                                      
                                      // kolor tla
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' ) {
                                           //
                                           if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                                $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                           }
                                           //
                                      }                                  

                                      $tagPoczatekTmp = $tagPoczatek;

                                      $MenuPreloader = '';
                                      //          
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                           //
                                           $MenuPreloader = ' PozycjaMenuPreloader';
                                           //
                                      } 
                                      //
                                      $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                      //
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                           //
                                           $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                           //
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                           //
                                           $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                           //
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                           //
                                           $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                           //
                                      }
                                      //
                                      $tagPoczatekTmp = str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" tabindex="0" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp);
                                      //
                                      unset($CssMenu);

                                      $DoWyswietlaniaTmp = $tagPoczatekTmp;
                                      
                                      unset($tagPoczatekTmp);
                                      
                                      $DoWyswietlaniaTmp .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';

                                      $DoWyswietlaniaTmp .= '<span class="MenuPozycjaGrafiki PozycjaRozwijanaMenu" ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . '>' . $NazwaPozycji . '</span>';
                                      
                                      $DoWyswietlaniaTmp .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu"></label></div>';
  
                                      unset($DodatkoweCss);  

                                      $DodatkowyCss = ' ';
                                      
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) ) {
                                      
                                           if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                                //
                                                $CssMenu = 'MenuSzerokie';
                                                //
                                                if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                                     $CssMenu = 'MenuSzerokie Menu30Procent';
                                                }
                                                if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                                     $CssMenu = 'MenuSzerokie Menu50Procent';
                                                }
                                                if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                                     $CssMenu = 'MenuSzerokie Menu70Procent';
                                                }
                                                //
                                                $DodatkowyCss = ' ' . $CssMenu . ' ';
                                                //
                                                unset($CssMenu);
                                                // 
                                           }

                                      }
                                      
                                      //
                                      $EfektMenu = 'EfektMenu-1';
                                      //
                                      if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                           //
                                           $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                           //
                                      }
                                      //
                                      $DoWyswietlaniaTmp .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . $DodatkowyCss . 'MenuGrafiki" id="OknoMenu-' . $x . '">';
                                      unset($EfektMenu);

                                      $DoWyswietlaniaTmpBannery = '';                                

                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($PozycjaKonfiguracji['grupa_bannerow']) && !empty($PozycjaKonfiguracji['grupa_bannerow']) ) {
                                           //
                                           $GrupaBannerow = $PozycjaKonfiguracji['grupa_bannerow'];
                                           //
                                           if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {

                                                $TablicaBannerow = $GLOBALS['bannery']->info[$GrupaBannerow];
                                                //
                                                if ( count($TablicaBannerow) > 0 ) {
                                                     //
                                                     if ( isset($PozycjaKonfiguracji['ilosc_bannerow']) ) {
                                                          //
                                                          $TablicaBannerow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, (($PozycjaKonfiguracji['ilosc_bannerow'] == 'jeden') ? 1 : 20));
                                                          //
                                                     }
                                                     //
                                                }
                                                //
                                                if ( count($TablicaBannerow) > 0 ) {
                                                     
                                                     $DoWyswietlaniaTmpBannery = '<li class="TylkoGrafikiMenu">';
       
                                                     foreach ($TablicaBannerow as $Banner ) {
       
                                                         $DoWyswietlaniaBanner = $GLOBALS['bannery']->bannerWyswietlMenu($Banner, ((isset($PozycjaKonfiguracji['tekst_bannery']) && $PozycjaKonfiguracji['tekst_bannery'] == 'tak') ? true : false));
                                                         
                                                         if ( $DoWyswietlaniaBanner != '' ) {
                                                              //
                                                              $DoWyswietlaniaTmpBannery .= '<div>' . $DoWyswietlaniaBanner . '</div>';
                                                              //
                                                         }
                                                         
                                                         unset($DoWyswietlaniaBanner);
       
                                                     }
                                                     
                                                     $DoWyswietlaniaTmpBannery .= '</li>';
                                                   
                                                }  
                                                //
                                                unset($TablicaBannerow);                                     
                                                //
                                           }
                                           //
                                           unset($GrupaBannerow);                                     
                                           //
                                      }
                                        
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                           //
                                           $DoWyswietlaniaTmp = '';
                                           //
                                      }                                  

                                      $DoWyswietlaniaTresc = $DoWyswietlaniaTmpBannery;                               

                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                           //
                                           return $DoWyswietlaniaTresc;
                                           //
                                      }    
                                      
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                           //
                                           $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                           //
                                      }
                                      
                                      $DoWyswietlaniaTmp .= $DoWyswietlaniaTresc . '</ul>';
                                      //
                                      unset($DoWyswietlaniaTmpBannery);                                
                                      //
                                      unset($PozycjaKonfiguracji);
                                      //
                                      $DoWyswietlaniaTmp .= $tagKoniec;
                                      
                                      $DoWyswietlaniaTmp = str_replace('class="MenuPozycjaGrafiki"', 'class="AktywnyLinkMenu MenuPozycjaGrafiki"', (string)$DoWyswietlaniaTmp);
                                      
                                      $DoWyswietlania .= $DoWyswietlaniaTmp;
                                      
                                      unset($DoWyswietlaniaTmp, $BylLink);
                                      //
                                      
                                 }
                                 
                            }
                            
                        }
                        
                        break; 
                    case "dowolnatresc":
                    
                        if ( Wyglad::TypSzablonu() == true && $rodzaj == "gorne_menu" ) {
                        
                            $PozycjaKonfiguracji = array();
                            if ( isset($Podkategorie[$strona[1] . '|dowolnatresc']) ) {
                                 $PozycjaKonfiguracji = $Podkategorie[$strona[1] . '|dowolnatresc'];
                            }                        
                            
                            $DowolnaTresc = array();

                            $sqls = $GLOBALS['db']->open_query("select ac.id_any_content,
                                                                       ac.any_content_css,
                                                                      acd.any_content_name,
                                                                      acd.any_content_description
                                                                 from any_content ac left join any_content_description acd on ac.id_any_content = acd.id_any_content and acd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                                                where ac.id_any_content = '" . (int)$strona[1] . "' and ac.any_content_status = 1");  
                                                      
                            if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) { 
                                //
                                $DowolnaTresc = $sqls->fetch_assoc();
                                //
                            }

                            $GLOBALS['db']->close_query($sqls);                                                 
                        
                            if ( count($DowolnaTresc) > 0 ) {
                                 //
                                 if ( isset($DowolnaTresc['any_content_name']) ) {
                                      //
                                      $NazwaPozycji = $DowolnaTresc['any_content_name'];
                                      
                                      $FlagaPozycji = '';
                                      //
                                      if ( isset($PozycjaKonfiguracji['flaga_pozycji']) && $PozycjaKonfiguracji['flaga_pozycji'] == 'tak' ) {
                                           //
                                           if ( isset($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_tla_flaga_pozycji']) &&
                                                isset($PozycjaKonfiguracji['kolor_flaga_pozycji']) && !empty($PozycjaKonfiguracji['kolor_flaga_pozycji']) &&
                                                isset($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']]) ) {
                                                //
                                                $FlagaPozycji = '<em class="FlagaMenu" style="background:#' . $PozycjaKonfiguracji['kolor_tla_flaga_pozycji'] . ';color:#' . $PozycjaKonfiguracji['kolor_flaga_pozycji'] . '">' . $PozycjaKonfiguracji['nazwa_flaga_pozycji'][(int)$_SESSION['domyslnyJezyk']['id']] . '</em>';
                                                //
                                           }
                                           //
                                      }
                                      //                                        
                                      $NazwaPozycji = '<b data-hover="' . str_replace('"', "&quot;", (string)$NazwaPozycji) . '">' . $FlagaPozycji . $NazwaPozycji . '</b>';
                                      //
                                      unset($FlagaPozycji);

                                      // dodanie ikonki jak jest
                                      if ( isset($PozycjaKonfiguracji['menu_ikonka']) && $PozycjaKonfiguracji['menu_ikonka'] != '' ) {
                                           //
                                           if ( file_exists(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']) ) {
                                                //
                                                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka']);
                                                //
                                                if ( (int)$szerokosc == 0 ) {
                                                     $szerokosc = 100;
                                                }
                                                if ( (int)$wysokosc == 0 ) {
                                                     $wysokosc = 100;
                                                }                  
                                                //
                                                $NazwaPozycji = '<i class="IkonkaMenu"><img src="' . KATALOG_ZDJEC . '/' . $PozycjaKonfiguracji['menu_ikonka'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . strip_tags($NazwaPozycji) . '" /></i>' . $NazwaPozycji;
                                                //
                                           }
                                           //
                                      }
                                
                                      $DodatkoweCss = array();
                                      
                                      // kolor linku
                                      if ( isset($PozycjaKonfiguracji['kolor_pozycji_rodzaj']) && $PozycjaKonfiguracji['kolor_pozycji_rodzaj'] == 'inny' ) {
                                           //
                                           if ( isset($PozycjaKonfiguracji['kolor_pozycji_kolor']) && $PozycjaKonfiguracji['kolor_pozycji_kolor'] != '' ) {
                                                $DodatkoweCss[] = 'color:#' . $PozycjaKonfiguracji['kolor_pozycji_kolor'] . ' !important';
                                           }
                                           //
                                      }
                                      
                                      // kolor tla
                                      if ( isset($PozycjaKonfiguracji['kolor_tla_rodzaj']) && $PozycjaKonfiguracji['kolor_tla_rodzaj'] == 'inny' ) {
                                           //
                                           if ( isset($PozycjaKonfiguracji['kolor_tla_kolor']) && $PozycjaKonfiguracji['kolor_tla_kolor'] != '' ) {
                                                $DodatkoweCss[] = 'background-color:#' . $PozycjaKonfiguracji['kolor_tla_kolor'] . ' !important';
                                           }
                                           //
                                      }                                  

                                      $tagPoczatekTmp = $tagPoczatek;

                                      $MenuPreloader = '';
                                      //          
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $rodzaj == 'gorne_menu' ) {
                                           //
                                           $MenuPreloader = ' PozycjaMenuPreloader';
                                           //
                                      } 
                                      //
                                      $CssMenu = 'class="PozycjaMenuSzerokie' . $MenuPreloader . '"';
                                      //
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                           //
                                           $CssMenu = 'class="PozycjaMenu30Procent' . $MenuPreloader . '"';
                                           //
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                           //
                                           $CssMenu = 'class="PozycjaMenu50Procent' . $MenuPreloader . '"';
                                           //
                                      }
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                           //
                                           $CssMenu = 'class="PozycjaMenu70Procent' . $MenuPreloader . '"';
                                           //
                                      }
                                      //
                                      $tagPoczatekTmp = str_replace(' aria-haspopup="true"', '', str_replace('<li ', '<li ' . $CssMenu . ' data-id="' . $x . '" tabindex="0" id="MenuPozycja-' . $x . '" ', (string)$tagPoczatekTmp));
                                      //
                                      unset($CssMenu);

                                      $DoWyswietlaniaTmp = $tagPoczatekTmp;
                                      
                                      unset($tagPoczatekTmp);
                                      
                                      $DoWyswietlaniaTmp .= '<input type="checkbox" class="CheckboxRozwinGorneMenu" id="PozycjaMenuGornego-' . $x . '" /><div>';

                                      $DoWyswietlaniaTmp .= '<span class="MenuPozycjaDowolnaTresc PozycjaRozwijanaMenu" ' . ((count($DodatkoweCss) > 0) ? 'style="' . implode(';', (array)$DodatkoweCss) . '"' : '') . '>' . $NazwaPozycji . '</span>';
                                      
                                      $DoWyswietlaniaTmp .= '<label for="PozycjaMenuGornego-' . $x . '" class="IkonaSubMenu"></label></div>';

                                      unset($DodatkoweCss);  

                                      $DodatkowyCss = ' ';
                                      
                                      if ( isset($PozycjaKonfiguracji['szerokosc']) ) {
                                      
                                           if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) ) {
                                                //
                                                $CssMenu = 'MenuSzerokie';
                                                //
                                                if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '30procent' ) {
                                                     $CssMenu = 'MenuSzerokie Menu30Procent';
                                                }
                                                if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '50procent' ) {
                                                     $CssMenu = 'MenuSzerokie Menu50Procent';
                                                }
                                                if ( isset($PozycjaKonfiguracji['szerokosc']) && $PozycjaKonfiguracji['szerokosc'] == '70procent' ) {
                                                     $CssMenu = 'MenuSzerokie Menu70Procent';
                                                }
                                                //
                                                $DodatkowyCss = ' ' . $CssMenu . ' ';
                                                //
                                                unset($CssMenu);
                                                // 
                                           }

                                      }
                                      
                                      //
                                      $EfektMenu = 'EfektMenu-1';
                                      //
                                      if ( isset($PozycjaKonfiguracji['efekt_menu']) && (int)$PozycjaKonfiguracji['efekt_menu'] > 0 ) {
                                           //
                                           $EfektMenu = 'EfektMenu-' . (int)$PozycjaKonfiguracji['efekt_menu'];
                                           //
                                      }
                                      //
                                      $DoWyswietlaniaTmp .= '<ul class="MenuRozwijaneKolumny ' . $EfektMenu . $DodatkowyCss . 'MenuDowolnaTresc" id="OknoMenu-' . $x . '">';
                                      unset($EfektMenu);

                                      $DoWyswietlaniaTmpTresc = '';                                

                                      if ( isset($PozycjaKonfiguracji['szerokosc']) && in_array($PozycjaKonfiguracji['szerokosc'], array('szerokie','30procent','50procent','70procent')) && isset($DowolnaTresc['any_content_description']) ) {
                                           //
                                           $DoWyswietlaniaTmpTresc = '<div class="CssDowolnaTresc-' . $DowolnaTresc['id_any_content'] . ' FormatEdytor">' . $DowolnaTresc['any_content_description'] . '</div>';            
                                           //
                                      }
                                        
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                           //
                                           $DoWyswietlaniaTmp = '';
                                           //
                                      }                                  

                                      $DoWyswietlaniaTresc = $DoWyswietlaniaTmpTresc;                               

                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer > -1 && $tylko_numer == $x ) {
                                           //
                                           return $DoWyswietlaniaTresc;
                                           //
                                      }    
                                      
                                      if ( isset($PozycjaKonfiguracji['rodzaj_wczytanie']) && $PozycjaKonfiguracji['rodzaj_wczytanie'] == 'preload' && $tylko_numer == -1 ) {
                                           //
                                           $DoWyswietlaniaTresc = '<li class="MenuPreloader"></li>';
                                           //
                                      }
                                      
                                      $DoWyswietlaniaTmp .= $DoWyswietlaniaTresc . '</ul>';
                                      //
                                      unset($DoWyswietlaniaTmpTresc);                                
                                      //
                                      unset($PozycjaKonfiguracji);
                                      //
                                      $DoWyswietlaniaTmp .= $tagKoniec;
                                      
                                      $DoWyswietlaniaTmp = str_replace('class="MenuPozycjaDowolnaTresc"', 'class="AktywnyLinkMenu MenuPozycjaDowolnaTresc"', (string)$DoWyswietlaniaTmp);
                                      
                                      $DoWyswietlania .= $DoWyswietlaniaTmp;
                                      
                                      unset($DoWyswietlaniaTmp, $BylLink);
                                      
                                      $StylCss = '';
                                      
                                      // dodatkowy kod css dla tresci
                                      if ( !empty($DowolnaTresc['any_content_css']) ) {
                                           //
                                           $StylCss = str_replace('{KLASA_CSS_TRESCI}', '.CssDowolnaTresc-' . $DowolnaTresc['id_any_content'], (string)$DowolnaTresc['any_content_css']);
                                           //
                                      }                       
                                      
                                      $GLOBALS['css'] .= $StylCss;
                                      
                                 }
                                 
                            }
                            
                        }
                        
                        break; 
                }
            }
            
            unset($pozycje_menu);
            //
        }
        unset($ciecie, $AktualnyLink);
        //
        // usuwanie duplikatow .AktywnyLinkMenu
        $IloscWystapien = substr_count($DoWyswietlania, 'AktywnyLinkMenu');
        //
        if ( $IloscWystapien > 1 ) {
             //
             $CiagTmp = '/' . preg_quote('AktywnyLinkMenu', '/') . '/';
             $DoWyswietlania = preg_replace($CiagTmp, '', (string)$DoWyswietlania, 1);
             unset($CiagTmp);
             //
        }
        //
        return $DoWyswietlania;
        //
    }   
    
    public static function PobierzNazwyMenu( $rodzaj = '' ) {  

        $Wynik = array();
        $ByloCache = false;
        //
        switch ($rodzaj) {
            case "strona":
            
                $Strony = StronyInformacyjne::TablicaStronInfo();
                
                if ( count($Strony) > 0 ) {
                
                    foreach ($Strony as $Strona) {
                        //
                        if ( !empty($Strona['url']) ) {
                            $Wynik[ $Strona['id'] ] = array($Strona['tytul'], $Strona['url'], (($Strona['nofollow'] == 1) ? ' rel="nofollow" ' : ' '));
                          } else { 
                            $Wynik[ $Strona['id'] ] = array($Strona['tytul'], (($Strona['nofollow'] == 1) ? ' rel="nofollow" ' : ' '));
                        }
                        //                    
                    }
                
                }
                
                unset($Strony);
                $ByloCache = true;

                break;
                
            case "galeria":
            
                // cache zapytania
                $WynikCache = $GLOBALS['cache']->odczytaj('Galerie_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_GALERIE, true);            

                if ( !$WynikCache ) {
                            
                    // dodatkowy warunek dla grup klientow
                    $warunekTmp = " and (p.gallery_customers_group_id = '0'";
                    if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                        $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", p.gallery_customers_group_id)";
                    }
                    $warunekTmp .= ") "; 
                    //                              
                    $sql = $GLOBALS['db']->open_query("select p.id_gallery, pd.gallery_name from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' and p.gallery_status = '1'" . $warunekTmp);
                    unset($warunekTmp);
                    
                    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                        //
                        while ($infwg = $sql->fetch_assoc()) {
                            $Wynik[ $infwg['id_gallery'] ] = $infwg['gallery_name'];
                        }
                        //
                    }
 
                    $GLOBALS['cache']->zapisz('Galerie_' . $_SESSION['domyslnyJezyk']['kod'], $Wynik, CACHE_GALERIE, true);
                    
                } else {
                
                    $Wynik = $WynikCache;
                    $ByloCache = true;
                    
                }
                
                unset($WynikCache);        
                
                break; 
                
            case "formularz":
            
                // cache zapytania
                $WynikCache = $GLOBALS['cache']->odczytaj('Formularze_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_FORMULARZE, true);            

                if ( !$WynikCache ) {
                                    
                    // dodatkowy warunek dla grup klientow
                    $warunekTmp = " and (p.form_customers_group_id = '0'";
                    if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                        $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", p.form_customers_group_id)";
                    }
                    $warunekTmp .= ") "; 
                    //                                            
                    $sql = $GLOBALS['db']->open_query("select p.id_form, pd.form_name from form p, form_description pd where p.id_form = pd.id_form and language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' and p.form_status = '1'" . $warunekTmp);
                    unset($warunekTmp);
                    
                    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                        //
                        while ($infwg = $sql->fetch_assoc()) {
                            $Wynik[ $infwg['id_form'] ] = $infwg['form_name'];
                        }
                        //
                    }
                    
                    $GLOBALS['cache']->zapisz('Formularze_' . $_SESSION['domyslnyJezyk']['kod'], $Wynik, CACHE_FORMULARZE, true);
                    
                } else {
                
                    $Wynik = $WynikCache;
                    $ByloCache = true;
                    
                }
                
                unset($WynikCache);        
                                    
                break; 
                
            case "kategoria":
            
                $ArtykulyKategorie = Aktualnosci::TablicaKategorieAktualnosci();
                
                if ( count($ArtykulyKategorie) > 0 ) {
                
                    foreach ($ArtykulyKategorie as $Kategoria) {
                        //
                        $Wynik[ $Kategoria['id'] ] = $Kategoria['nazwa'];
                        //                    
                    }
                
                }
                
                unset($ArtykulyKategorie);               
                $ByloCache = true;     
                
                break;   
                
            case "artykul":
            
                $Artykuly = Aktualnosci::TablicaAktualnosci();
                
                if ( count($Artykuly) > 0 ) {
                
                    foreach ($Artykuly as $Artykul) {
                        //
                        $Wynik[ $Artykul['id'] ] = $Artykul['tytul'];
                        //                    
                    }
                
                }
                
                unset($Artykuly);          
                $ByloCache = true;     
                
                break;     

        }

        if ( $ByloCache == false ) {
            $GLOBALS['db']->close_query($sql);                                                 
            unset($infwg); 
        }
        
        return $Wynik;
        //
    }     
    
    public function ModulyStale() {  
        global $i18n;
        //
        $DoWyswietlenia = '';
        //
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('ModulyStale', CACHE_INNE);      

        $Tablica = array();
        
        $ByloCache = false;

        if ( !$WynikCache && !is_array($WynikCache) ) {
                          
            $sql = $GLOBALS['db']->open_query("select modul_file from theme_modules_fixed where modul_status = '1'");
            //
            while ($infwg = $sql->fetch_assoc()) {
                if (in_array( $infwg['modul_file'], $this->PlikiModulyStale )) {
                    //
                    $Tablica[] = $infwg;
                    $this->PlikiModulyStalePliki[] = str_replace('.php', '', (string)$infwg['modul_file']);
                    //
                }
            }
            //
            
            $GLOBALS['cache']->zapisz('ModulyStale', $Tablica, CACHE_INNE);   
            
        } else {
        
            $Tablica = $WynikCache;
            
            foreach ( $Tablica as $Tmp ) {
                 //
                 $this->PlikiModulyStalePliki[] = str_replace('.php', '', (string)$Tmp['modul_file']);
                 //
            }
                        
            $ByloCache = true;
            
        }        
        //
        if ( count($Tablica) > 0 ) {
            //
            foreach ( $Tablica as $infwg ) {
                //
                if ( file_exists('moduly_stale/' . $infwg['modul_file']) ) {
                     //                     
                     ob_start();
                     require('moduly_stale/' . $infwg['modul_file']);
                     $_wynik = ob_get_contents();
                     ob_end_clean();                         
                     $DoWyswietlenia .= $_wynik;
                     unset($_wynik);                        
                     //
                }
                //
            }
            //
        }
        //
        if ( $ByloCache == false ) {  
            $GLOBALS['db']->close_query($sql); 
            unset($infwg);
        }        
        //
        unset($Tablica);
        //
        return $DoWyswietlenia;
        //
    }
    
    public function TrescLokalna( $plik ) {
        //
        if (in_array( $plik . '.tp', $this->PlikiTresciLokalne )) {
            //
            return 'szablony/'.DOMYSLNY_SZABLON.'/tresc/' . $plik . '.tp';
            //
          } else {
            //
            return 'szablony/__tresc/' . $plik . '.tp';
            //
        }
        //
    }

    public static function ZmianaJezyka() {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Jezyki', CACHE_INNE);                     
                          
        if ( !$WynikCache ) {

            $zapytanie_box = "SELECT languages_id, name, code, image 
                              FROM languages
                              WHERE status = '1' ORDER BY sort_order";

            $sql_box = $GLOBALS['db']->open_query($zapytanie_box);
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql_box);
            
            unset($zapytanie_box);
            
          } else {
          
            $IleRekordow = count($WynikCache);
            
        }    

        if ($IleRekordow > 1) {
        
            //
            $Tablica = array();
            $Tresc = '';
            //
            if ( !$WynikCache ) {
                while ($infwg_box = $sql_box->fetch_assoc()) {
                    $Tablica[] = $infwg_box;
                }
                //
                $GLOBALS['cache']->zapisz('Jezyki', $Tablica, CACHE_INNE);      
            } else {
                $Tablica = $WynikCache;
            }
            
            foreach ($Tablica as $infwg_box) { 
                 //
                 if ( $infwg_box['image'] != '' ) {
                      //
                      if ( file_exists(KATALOG_ZDJEC . '/' . $infwg_box['image']) ) {
                           //
                           list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $infwg_box['image']);
                           //
                           $Tresc .= '<span tabindex="0" role="button" class="Flaga" id="Jezyk' . $infwg_box['languages_id'] . '" lang="' . $infwg_box['code'] . '" aria-label="{__TLUMACZ:JEZYK} ' . $infwg_box['name'] . '"><img ' . ( $infwg_box['languages_id'] == (int)$_SESSION['domyslnyJezyk']['id'] ? '' : ' class="FlagaOff"').' src="' . KATALOG_ZDJEC . '/' . $infwg_box['image'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . $infwg_box['name'] . '" title="' . $infwg_box['name'] . '" /></span>';
                           //
                      }
                      //
                 }
                 //
            }
            //
            
            unset($Tablica);
            //
            
            if ( !$WynikCache ) {
                $GLOBALS['db']->close_query($sql_box); 
            }        
            
            return $Tresc;
            
        }
    
    }

    public static function ZmianaWaluty() {
      
        $Tablica = array();
        $Txt = '';

        if ( isset($GLOBALS['waluty']->waluty) && count($GLOBALS['waluty']->waluty) > 0 ) { 

            foreach ( $GLOBALS['waluty']->waluty as $key => $value ) {
                  //
                  $Tablica[] = array('id' => $value['id'], 'text' => $value['nazwa']);
                  //
            }
            //
            $Txt .= '<div>' . Funkcje::RozwijaneMenu('waluta', $Tablica, ((isset($_SESSION['domyslnaWaluta']['id'])) ? $_SESSION['domyslnaWaluta']['id'] : ''), 'id="WybierzWalute" aria-label="{__TLUMACZ:WALUTA}"') . '</div>';
            //
            
        }

        unset($Tablica);     

        return $Txt;
    
    }      
    
    public function KontaktStopka() {
      
        $Txt = '<ul class="KontaktStopka"' . ((STOPKA_DANE_STRUKTURALNE_STATUS == 'tak') ? ' itemscope itemtype="http://schema.org/LocalBusiness"' : '') . '>';
        
        // logo
        if ( STOPKA_DANE_KONTAKTOWE_LOGO == 'tak' && LOGO_FIRMA != '' ) {
             //
             if ( file_exists(KATALOG_ZDJEC . '/' . LOGO_FIRMA) ) {
                  //
                  list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . LOGO_FIRMA);
                  //
                  $Txt .= '<li class="KontaktStopkaLogo"><img' . ((STOPKA_DANE_STRUKTURALNE_STATUS == 'tak') ? ' itemprop="image"' : '') . ' src="' . KATALOG_ZDJEC . '/' . LOGO_FIRMA . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . ( DANE_NAZWA_FIRMY_PELNA != '' ? str_replace('"', '', strip_tags((string)DANE_NAZWA_FIRMY_PELNA)) : '' ) . '" /></li>';
                  //
             }
             //
        }          
        
        // dane sklepu
        if ( STOPKA_DANE_KONTAKTOWE_FIRMA == 'tak' ) {
          
            $Txt .= '<li class="KontaktStopkaFirma Iko Firma">';
                  //
                  if ( DANE_FIRMY_BOX_KONTAKT != '' && DANE_FIRMY_BOX_KONTAKT != '' ) { 
                       //
                       $Txt .= '<span class="DaneFirmaKontakt">' . nl2br(DANE_FIRMY_BOX_KONTAKT) . '</span>';
                       //
                  }
                  // nip
                  if ( STOPKA_DANE_KONTAKTOWE_NIP == 'tak' && DANE_NIP != '' ) {
                       //
                       $Txt .= '<span class="DaneFirmaNipKontakt"><span>{__TLUMACZ:KONTAKT_NIP}:</span> <span>' . DANE_NIP . '</span></span>';
                       //
                  }  
                  // regon
                  if ( STOPKA_DANE_KONTAKTOWE_REGON == 'tak' && DANE_REGON != '' ) {
                       //
                       $Txt .= '<span class="DaneFirmaRegonKontakt"><span>{__TLUMACZ:KONTAKT_REGON}:</span> <span>' . DANE_REGON . '</span></span>';
                       //
                  }     
                  // bdo
                  if ( STOPKA_DANE_KONTAKTOWE_BDO == 'tak' && DANE_BDO != '' ) {
                       //
                       $Txt .= '<span class="DaneFirmaBdoKontakt"><span>{__TLUMACZ:KONTAKT_BDO}:</span> <span>' . DANE_BDO . '</span></span>';
                       //
                  }                      
                  //
                  if ( STOPKA_DANE_STRUKTURALNE_STATUS == 'tak' ) {
                    
                      $Txt .= '<meta itemprop="name" content="' . DANE_NAZWA_FIRMY_SKROCONA . '" />';
                      //
                      $Txt .= '<div style="display:none" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                                <meta itemprop="name" content="' . str_replace('"', '', strip_tags((string)DANE_NAZWA_FIRMY_PELNA)) . '" />
                                <meta itemprop="streetAddress" content="' . DANE_ADRES_LINIA_1 . ' ' . DANE_ADRES_LINIA_2 . '" />
                                <meta itemprop="postalCode" content="' . DANE_KOD_POCZTOWY . '" />
                                <meta itemprop="addressLocality" content="' . DANE_MIASTO . '" />
                              </div>';
                              
                  }
            
            $Txt .= '</li>';        
        
        }
        
        // konto bankowe
        if ( STOPKA_DANE_KONTAKTOWE_KONTO == 'tak' && DANE_NAZWA_BANKU != '' && DANE_NUMER_KONTA_BANKOWEGO != '' ) {
             //
             $Txt .= '<li class="KontaktStopkaLinia Iko Bank"><span>' . DANE_NAZWA_BANKU . '</span><span>' . DANE_NUMER_KONTA_BANKOWEGO . '</span></li>';
             //
        } 
        
        // konto bankowe nr 2
        if ( STOPKA_DANE_KONTAKTOWE_KONTO == 'tak' && DANE_NAZWA_BANKU_2 != '' && DANE_NUMER_KONTA_BANKOWEGO_2 != '' ) {
             //
             $Txt .= '<li class="KontaktStopkaLinia Iko Bank"><span>' . DANE_NAZWA_BANKU_2 . '</span><span>' . DANE_NUMER_KONTA_BANKOWEGO_2 . '</span></li>';
             //
        }         
        
        // konto bankowe dla euro
        if ( STOPKA_DANE_KONTAKTOWE_KONTO == 'tak' && DANE_NAZWA_BANKU_EURO != '' && DANE_NUMER_KONTA_BANKOWEGO_EURO != '' ) {
             //
             $Txt .= '<li class="KontaktStopkaLinia Iko Bank"><span>' . DANE_NAZWA_BANKU_EURO . '</span><span>' . DANE_NUMER_KONTA_BANKOWEGO_EURO . '</span></li>';
             //
        }     
        
        // email
        if ( STOPKA_DANE_KONTAKTOWE_EMAIL == 'tak' && INFO_EMAIL_SKLEPU != '' ) {
             //
             if ( STOPKA_DANE_KONTAKTOWE_EMAIL_FORMA == 'formularz' ) { 
                  //
                  if ( isset($this->Formularze[1]) ) {
                       $Txt .= '<li class="KontaktStopkaLinia Iko Mail"> <a href="' . Seo::link_SEO( $this->Formularze[1], 1, 'formularz' ) . '"><span' . ((STOPKA_DANE_STRUKTURALNE_STATUS == 'tak') ? ' itemprop="email"' : '') . '>' . INFO_EMAIL_SKLEPU . '</span></a></li>';
                  }
                  //             
             } else {
                  //
                  $Txt .= '<li class="KontaktStopkaLinia Iko Mail"> <a href="mailto:' . INFO_EMAIL_SKLEPU . '"><span' . ((STOPKA_DANE_STRUKTURALNE_STATUS == 'tak') ? ' itemprop="email"' : '') . '>' . INFO_EMAIL_SKLEPU . '</span></a></li>';
                  //
             }
             //
        }         
        
        // telefon
        if ( DANE_TELEFON_1 != '' || DANE_TELEFON_2 != '' || DANE_TELEFON_3 != '' ) {
             //
             if ( STOPKA_DANE_KONTAKTOWE_TELEFON_1 == 'tak' || STOPKA_DANE_KONTAKTOWE_TELEFON_2 == 'tak' || STOPKA_DANE_KONTAKTOWE_TELEFON_3 == 'tak' ) {
                  //
                  $Txt .= '<li class="KontaktStopkaLinia Iko Tel">';
                  //
                  if ( DANE_TELEFON_1 != '' && STOPKA_DANE_KONTAKTOWE_TELEFON_1 == 'tak' ) { $Txt .= '<a rel="nofollow" href="tel:' . preg_replace("/[^+0-9]/", "", (string)DANE_TELEFON_1) . '"><span' . ((STOPKA_DANE_STRUKTURALNE_STATUS == 'tak') ? ' itemprop="telephone"' : '') . '>' . DANE_TELEFON_1 . '</span></a>'; }
                  if ( DANE_TELEFON_2 != '' && STOPKA_DANE_KONTAKTOWE_TELEFON_2 == 'tak' ) { $Txt .= '<a rel="nofollow" href="tel:' . preg_replace("/[^+0-9]/", "", (string)DANE_TELEFON_2) . '">' . DANE_TELEFON_2 . '</a>'; }
                  if ( DANE_TELEFON_3 != '' && STOPKA_DANE_KONTAKTOWE_TELEFON_3 == 'tak' ) { $Txt .= '<a rel="nofollow" href="tel:' . preg_replace("/[^+0-9]/", "", (string)DANE_TELEFON_3) . '">' . DANE_TELEFON_3 . '</a>'; }
                  //
                  $Txt .= '</li>';
                  //
             }
             //             
        }     

        // fax
        if ( STOPKA_DANE_KONTAKTOWE_FAX == 'tak' && DANE_FAX_1 != '' ) {
             //
             $Txt .= '<li class="KontaktStopkaLinia Iko Fax"><span>' . DANE_FAX_1 . '</span></li>';
             //
        }          
        
        // nr gg
        if ( STOPKA_DANE_KONTAKTOWE_GG == 'tak' && DANE_GG_1 != '' ) {
             //
             $Txt .= '<li class="KontaktStopkaLinia Iko Gg"><a rel="nofollow" href="gg:' . DANE_GG_1 . '">' . DANE_GG_1 . '</a></li>';
             //
        }  

        // godziny dzialania
        if ( STOPKA_DANE_KONTAKTOWE_GODZINY == 'tak' && GODZINY_DZIALANIA != '' ) {
             //
             $Txt .= '<li class="KontaktStopkaLinia Iko Godziny"><span>' . nl2br(GODZINY_DZIALANIA) . '</span></li>';
             //
        }           
        
        // kod QR
        if ( STOPKA_DANE_KONTAKTOWE_KOD_QR == 'tak' && KOD_QR != '' ) {
             //
             if ( file_exists(KATALOG_ZDJEC . '/' . KOD_QR) ) {
                  //
                  $Txt .= '<li class="KontaktStopkaQr"><img src="' . KATALOG_ZDJEC . '/' . KOD_QR . '" alt="Kod QR - ' . ( DANE_NAZWA_FIRMY_PELNA != '' ? str_replace('"', '', strip_tags((string)DANE_NAZWA_FIRMY_PELNA)) : '' ) . '" /></li>';
                  //
             }
             //
        }          
            
        $Txt .= '</ul>';
        
        return $Txt;
      
    }
    
    public function PortaleSpolecznisciowe() {
      
        $Txt = '';
      
        if ( DANE_PROFIL_FACEBOOK != '' || DANE_PROFIL_YOUTUBE != '' || DANE_PROFIL_INSTAGRAM != '' || DANE_PROFIL_TWITTER != '' || DANE_PROFIL_PINTEREST != '' || DANE_PROFIL_LINKEDIN != '' || DANE_PROFIL_TIKTOK != '' ) {
             //
             $Txt .= '<ul class="PortaleSpolecznoscioweIkony' . ((NAGLOWEK_PORTALE_MOBILE == 'nie') ? ' PortaleSpolecznoscioweIkonyMobile' : '') . '">';
             //
             if ( DANE_PROFIL_FACEBOOK != '' ) {
                  //
                  $Txt .= '<li class="PortaleFacebook" title="Facebook"><a target="_blank" href="' . DANE_PROFIL_FACEBOOK . '">Facebook</a></li>';
                  //
             }
             if ( DANE_PROFIL_YOUTUBE != '' ) {
                  //
                  $Txt .= '<li class="PortaleYoutube" title="Youtube"><a target="_blank" href="' . DANE_PROFIL_YOUTUBE . '">Youtube</a></li>';
                  //
             }    
             if ( DANE_PROFIL_INSTAGRAM != '' ) {
                  //
                  $Txt .= '<li class="PortaleInstagram" title="Instagram"><a target="_blank" href="' . DANE_PROFIL_INSTAGRAM . '">Instagram</a></li>';
                  //
             }  
             if ( DANE_PROFIL_LINKEDIN != '' ) {
                  //
                  $Txt .= '<li class="PortaleLinkedIn" title="LinkedIn"><a target="_blank" href="' . DANE_PROFIL_LINKEDIN . '">LinkedIn</a></li>';
                  //
             }   
             if ( DANE_PROFIL_TWITTER != '' ) {
                  //
                  $Txt .= '<li class="PortaleTwitter" title="X (Twitter)"><a target="_blank" href="' . DANE_PROFIL_TWITTER . '">X (Twitter)</a></li>';
                  //
             } 
             if ( DANE_PROFIL_PINTEREST != '' ) {
                  //
                  $Txt .= '<li class="PortalePinterest" title="Pinterest"><a target="_blank" href="' . DANE_PROFIL_PINTEREST . '">Pinterest</a></li>';
                  //
             }  
             if ( DANE_PROFIL_TIKTOK != '' ) {
                  //
                  $Txt .= '<li class="PortaleTiktok" title="TikTok"><a target="_blank" href="' . DANE_PROFIL_TIKTOK . '">TikTok</a></li>';
                  //
             }           
             //
             $Txt .= '</ul>';
             //
        }      
              
        return $Txt;
      
    }    
    
    public static function PrzegladarkaJavaScript( $js, $wymus = false ) {
    
        if ( $wymus == false ) {
             return '<script> $(document).ready(function() { ' . $js . ' }); </script>';
          } else {
             return '<script> $(window).load(function() { ' . $js . ' }); </script>';
        }
        
    }
    
    // sprawdza typ ustawionego szablonu
    public static function TypSzablonu() {

        if ( strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1 ) {
             return true;
        } else {
             return false;
        }

    }      
    
    // sprawdza czy jest urzadzenie mobilne
    public static function UrzadzanieMobilne() {
        
        // rozpoznaje urzadzanie mobilne
        $jestMobilne = false;
        
        if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
        
            $useragent = $_SERVER['HTTP_USER_AGENT'];
            if ( preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr((string)$useragent,0,4)) ) {        
                 //
                 $jestMobilne = true;
                 //
            } else {
                 //
                 $jestMobilne = false;
                 //
            }
        
        }
        
        return $jestMobilne;

    }    
    
    // jakie urzadzenie
    public static function RodzajUrzadzania($zmienna = '') {
  
        $wynik = 'Nierozpoznany';
        
        if ( $zmienna == '' ) {
             //
             if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
                  //
                  $zmienna = $_SERVER['HTTP_USER_AGENT'];
                  //
             }
             //
        }

        if ( $zmienna != '' ) {

            $Tablet = 0;
            $Smartphone = 0;
            
            if ( isset($_SERVER['HTTP_USER_AGENT']) ) {

                if ( preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT'])) ) {
                     $Tablet++;
                }

                if ( preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT'])) ) {
                     $Smartphone++;
                }

                if ( (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower((string)$_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']))) ) {
                     $Smartphone++;
                }

                $MobileUserAgent = strtolower(substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 4));
                
                $MobileTablica = array('w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
                                       'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
                                       'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
                                       'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
                                       'newt','noki','palm','pana','pant','phil','play','port','prox',
                                       'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
                                       'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
                                       'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                                       'wapr','webc','winw','winw','xda ','xda-');

                if ( in_array($MobileUserAgent,$MobileTablica) ) {
                     $Smartphone++;
                }

                if ( strpos(strtolower((string)$_SERVER['HTTP_USER_AGENT']),'opera mini') > 0 ) {
                     //
                     $Smartphone++;
                     //
                     $ParametrUserAgent = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
                     //
                     if ( preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $ParametrUserAgent) ) {
                          $Tablet++;
                     }
                     //
                }

                if ( $Tablet > 0 ) {
                     //
                     $wynik = 'Tablet';
                     //
                } else if ($Smartphone > 0) {
                     //
                     $wynik = 'Smartphone';
                     //
                } else {
                     //
                     $wynik = 'Komputer stacjonarny / laptop'; 
                     //
                }         

            }                

        }
        
        return $wynik;

    }
    
    // tablica czcionek slicka
    public function CzcionkiSlick() {
        //
        $Tablica = array();

        $Tablica['62'] = array('62','61');
        $Tablica['65'] = array('65','64');
        $Tablica['6e'] = array('6e','6f');
        $Tablica['66'] = array('66','67');
        $Tablica['68'] = array('68','69');
        $Tablica['6a'] = array('6a','6b');

        return $Tablica;
        //
    }

} 

?>