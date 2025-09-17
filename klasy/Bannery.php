<?php

class Bannery {

    public $info;

    public function __construct() {

        // tablica wlaczonych bannerow
        $this->info = array();

        $this->BannerInfo();
    }

    // tworzy tablice z grupami bannerow
    public function BannerInfo() {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Bannery_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_BANNERY, true);    
        
        if ( !$WynikCache && !is_array($WynikCache) ) {
            //
            // dodatkowy warunek dla grup klientow
            $warunekTmp = " and (b.banners_customers_group_id = '0'";
            if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", b.banners_customers_group_id)";
            }
            $warunekTmp .= ") "; 
            
            $zapytanie = "SELECT b.banners_id, 
                                 b.banners_title, 
                                 b.banners_url, 
                                 b.banners_url_blank, 
                                 b.banners_image, 
                                 b.banners_type, 
                                 b.banners_mp4_width, 
                                 b.banners_mp4_height, 
                                 b.banners_mp4_controls, 
                                 b.banners_mp4_mute, 
                                 b.banners_mp4_autoplay,
                                 b.banners_mp4_loop,
                                 b.banners_image_text, 
                                 b.banners_group, 
                                 b.banners_html_text, 
                                 b.banners_clicked, 
                                 b.status, 
                                 b.sort_order, 
                                 b.only_categories_id, 
                                 b.banners_date, 
                                 b.banners_date_end, 
                                 b.banners_text_config 
                            FROM banners b 
                           WHERE b.status = '1' AND (b.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' OR b.languages_id = '0') " . $warunekTmp . "
                        ORDER BY b.sort_order";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

                while ( $info = $sql->fetch_assoc() ) {
                  
                    $this->info[$info['banners_group']][] = array('id_bannera' => $info['banners_id'],
                                                                  'nazwa_bannera' => $info['banners_title'],
                                                                  'grupa' => $info['banners_group'],
                                                                  'adres_url_bannera' => $info['banners_url'],
                                                                  'adres_okno' => (($info['banners_url_blank'] == 1) ? 'tak' : 'nie'),
                                                                  'obrazek_bannera' => $info['banners_image'],
                                                                  'obrazek_alt_bannera' => $info['banners_image_text'],
                                                                  'tekst_bannera' => $info['banners_html_text'],
                                                                  'klikniecia_bannera' => $info['banners_clicked'],
                                                                  'kategorie_id' => $info['only_categories_id'],
                                                                  'data_od' => $info['banners_date'],
                                                                  'data_do' => $info['banners_date_end'],
                                                                  'konfiguracja_teksty' => $info['banners_text_config'],
                                                                  'sortowanie' => $info['sort_order'],
                                                                  'rodzaj_grafiki' => $info['banners_type'],
                                                                  'film_szerokosc' => $info['banners_mp4_width'],
                                                                  'film_wysokosc' => $info['banners_mp4_height'],
                                                                  'film_nawigacja' => (($info['banners_mp4_controls'] == 1) ? 'tak' : 'nie'),
                                                                  'film_dzwiek' => (($info['banners_mp4_mute'] == 1) ? 'tak' : 'nie'),
                                                                  'film_autostart' => (($info['banners_mp4_autoplay'] == 1) ? 'tak' : 'nie'),
                                                                  'film_zapetlenie' => (($info['banners_mp4_loop'] == 1) ? 'tak' : 'nie'));
                }
                
                unset($info);
                
            }
            
            $GLOBALS['db']->close_query($sql);    
            unset($zapytanie);
            
            $GLOBALS['cache']->zapisz('Bannery_' . $_SESSION['domyslnyJezyk']['kod'], $this->info, CACHE_BANNERY);
            
        } else {
        
            $this->info = $WynikCache;
        
        }
        
        unset($WynikCache);
        
        // sprawdzi czy nie ma jakis bannerow czasowych
        foreach ( $this->info as $GrupaNazwa => $GrupaWartosci ) {
              //
              foreach ( $GrupaWartosci as $Klucz => $Wartosci ) {
                  //
                  $DataOd = time() - 86400;
                  $DataDo = time() + 86400;
                  //
                  if ( Funkcje::czyNiePuste($Wartosci['data_od']) ) {
                       $DataOd = FunkcjeWlasnePHP::my_strtotime($Wartosci['data_od']);
                  } 
                  if ( Funkcje::czyNiePuste($Wartosci['data_do']) ) {
                       $DataDo = FunkcjeWlasnePHP::my_strtotime($Wartosci['data_do']);
                  }                                      
                  //
                  if ( time() < $DataOd || time() > $DataDo ) {
                       //
                       unset( $this->info[$GrupaNazwa][$Klucz] );
                       //
                       // $GLOBALS['db']->open_query("UPDATE banners SET status = '0' WHERE banners_id = '" . $Wartosci['id_bannera'] . "'");
                       //
                  }
                  //
                  unset($DataOd, $DataDo);
                  //
              }
              //          
        }

        // jezeli jest wybrana kategoria wyswietli tylko te bannery ktore sa przewidziane dla danej kategorii
        if (isset($_GET['idkat'])) {
          
            $PodzialGet = explode('_', (string)$_GET['idkat']);

            foreach ( $this->info as $NazwaGrupy => $TablicaDlaKategorii ) {
                
                foreach ( $TablicaDlaKategorii as $IdGrupy => $GrupaBanerow ) {

                      // jezeli nie jest pusta
                      if ( !empty($GrupaBanerow['kategorie_id']) ) {

                          $PodzielTablice = explode(',', (string)$GrupaBanerow['kategorie_id']);
                          if ( !in_array( $PodzialGet[ count($PodzialGet) - 1], $PodzielTablice ) ) {
                               //
                               unset( $this->info[$NazwaGrupy][$IdGrupy] );
                               //
                          }
                          unset($PodzielTablice);
                        
                      }
                  
                }
              
            }
            
            unset($PodzialGet);
          

        } elseif ( isset($_GET['idprod']) ) {

            $PodzialGet = Kategorie::ProduktKategorie($_GET['idprod']);
            
            if ( count($PodzialGet) > 0 ) {

                foreach ( $this->info as $NazwaGrupy => $TablicaDlaKategorii ) {
                  
                    foreach ( $TablicaDlaKategorii as $IdGrupy => $GrupaBanerow ) {
                      
                          // jezeli nie jest pusta
                          if ( !empty($GrupaBanerow['kategorie_id']) ) {
                           
                              $PodzielTablice = explode(',', (string)$GrupaBanerow['kategorie_id']);
                              if ( !in_array( $PodzialGet[ count($PodzialGet) - 1], $PodzielTablice ) ) {
                                   //
                                   unset( $this->info[$NazwaGrupy][$IdGrupy] );
                                   //
                              }
                              unset($PodzielTablice);
                            
                          }
                      
                    }
                  
                }
                
            }

            unset($PodzialGet);

        } else {
          
            foreach ( $this->info as $NazwaGrupy => $TablicaDlaKategorii ) {
          
                foreach ( $TablicaDlaKategorii as $IdGrupy => $GrupaBanerow ) {
                  
                      // jezeli nie jest pusta
                      if ( isset($GrupaBanerow['kategorie_id']) && !empty($GrupaBanerow['kategorie_id']) ) {
                       
                          unset( $this->info[$NazwaGrupy][$IdGrupy] );
                        
                      }
                  
                }          
          
            }
            
        }

    }
    
    // funkcja generuje tag img lub video
    public function bannerGenerujTag($banner,$preload = false, $tekst = false) {
            
        $ciag = '';
      
        if ( $banner['rodzaj_grafiki'] == 'grafika' ) {
             //
             list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
             //
             if ( $preload == false ) {
                  //
                  $ciag = '<img src="' . KATALOG_ZDJEC . '/' . $banner['obrazek_bannera'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . $banner['nazwa_bannera'] . '" title="' . $banner['nazwa_bannera'] . '" />';
                  //
                  if ( $tekst == true ) {
                       //
                       $ciag .= '<span class="GrafikaTytul">' . $banner['nazwa_bannera'] . '</span>';
                       //
                  }
                  //
             } else {
                  //
                  $ciag = '<img data-lazy="' . KATALOG_ZDJEC . '/' . $banner['obrazek_bannera'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . $banner['nazwa_bannera'] . '" title="' . $banner['nazwa_bannera'] . '" />';
                  //
             }             
             //
        }
        
        if ( $banner['rodzaj_grafiki'] == 'film' ) {
             //
             $szerokosc = (((int)$banner['film_szerokosc'] > 0) ? ';max-width:' . (int)$banner['film_szerokosc'] . 'px' : '');
             $wysokosc = (((int)$banner['film_wysokosc'] > 0) ? ';max-height:' . (int)$banner['film_wysokosc'] . 'px' : '');
             //
             $nawigacja = (($banner['film_nawigacja'] == 'tak') ? ' controls' : '');
             $dzwiek = (($banner['film_dzwiek'] == 'tak') ? ' muted' : '');
             $autostart = (($banner['film_autostart'] == 'tak') ? ' autoplay' : '');
             $zapetlenie = (($banner['film_zapetlenie'] == 'tak') ? ' loop="true"' : ' loop="false"');
             //
             // jezeli jest wylaczona nawigacja / wlaczony dzwiek i autostart to trzeba wylaczyc dzwiek bo sie od razu nie uruchomi
             if ( $banner['film_nawigacja'] == 'nie' && $banner['film_autostart'] == 'tak' ) {
                  $dzwiek = ' muted';
             }
             //
             $ciag = '<video style="width:100%;height:auto' . $szerokosc . $wysokosc . '"' . $nawigacja . $dzwiek . $autostart . $zapetlenie . '><source src="' . KATALOG_ZDJEC . '/' . $banner['obrazek_bannera'] . '" type="video/mp4"></video>';
             //
             unset($szerokosc, $wysokosc, $nawigacja, $dzwiek, $autostart, $zapetlenie);
             //
        }      
          
        return $ciag;
        
    }

    // funkcja uzywana do wyswietlania banneru statycznego
    public function bannerWyswietlStatyczny($banner) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' && $banner['adres_url_bannera'] == '' && $banner['tekst_bannera'] != '' ) {
            echo htmlspecialchars_decode($banner['tekst_bannera']);
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
              
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if (( $typ == '1' || $typ == '2' || $typ == '3' || $typ == '18' ) && $banner['rodzaj_grafiki'] == 'grafika') {
                
                    if ( $banner['adres_url_bannera'] != '' ) {
                        echo '<a href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($banner) . '</a>';
                    } else {
                        echo $this->bannerGenerujTag($banner);
                    }
                      
                } else {
                  
                    $rozszerzenie = pathinfo(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                    
                    if ( !isset($rozszerzenie['extension']) ) {
                         //
                         $roz = explode('.', KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                         $rozszerzenie = $roz[ count($roz) - 1];
                         //
                    }

                    if ( $rozszerzenie['extension'] == 'mp4' && $banner['rodzaj_grafiki'] == 'film' ) {
                      
                         if ( $banner['adres_url_bannera'] != '' ) {
                             echo '<a href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($banner) . '</a>';
                         } else {
                             echo $this->bannerGenerujTag($banner);
                         }
                      
                    } else {
                                    
                        return;
                        
                    }
                    
                }
                
            } else {
              
                return;
                
            }
            
        }

        return;

    }
    
    // funkcja uzywana do wyswietlania banneru w gorym menu
    public function bannerWyswietlMenu($banner, $tekst = false) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' && $banner['adres_url_bannera'] == '' && $banner['tekst_bannera'] != '' ) {
             //
             return;
             //
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
              
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if (( $typ == '1' || $typ == '2' || $typ == '3' || $typ == '18' ) && $banner['rodzaj_grafiki'] == 'grafika') {
                  
                    if ( $banner['adres_url_bannera'] != '' ) {
                        return '<a href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($banner, false, $tekst) . '</a>';
                    } else {
                        return $this->bannerGenerujTag($banner, false, $tekst);
                    }
                    
                } else {
                  
                    $rozszerzenie = pathinfo(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                    
                    if ( !isset($rozszerzenie['extension']) ) {
                         //
                         $roz = explode('.', KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                         $rozszerzenie = $roz[ count($roz) - 1];
                         //
                    }

                    if ( $rozszerzenie['extension'] == 'mp4' && $banner['rodzaj_grafiki'] == 'film' ) {
                      
                        if ( $banner['adres_url_bannera'] != '' ) {
                            return '<a href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($banner) . '</a>';
                        } else {
                            return $this->bannerGenerujTag($banner);
                        }
                      
                    } else {
                                    
                        return;
                        
                    }
                    
                }
                
            } else {
              
                return;
                
            }
            
        }

        return;

    }    
    
    // funkcja uzywana do wyswietlania bannerow z kreatora modulow
    public function bannerWyswietlKreatorModulow($banner, $tekst_na_bannerze = false, $t = 0, $preloader = false, $id_modulu = 0, $bez_animacji = false) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' && $banner['adres_url_bannera'] == '' && $banner['tekst_bannera'] != '' ) {
             //
             // return;
             //
        }
        
        if ( $banner['tekst_bannera'] != '' ) {
          
             $wynik = '<div class="GrafikaKreator GrafikaTekstowa FormatEdytor GrafikaNr-' . $t . '" ><div>';
             
             $wynik .= htmlspecialchars_decode($banner['tekst_bannera']);
             
             $wynik .= '</div></div>';
             
             return $wynik;
             
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
              
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if (( $typ == '1' || $typ == '2' || $typ == '3' || $typ == '18' ) && $banner['rodzaj_grafiki'] == 'grafika') {
                  
                    $wynik = '<div class="GrafikaKreator GrafikaNr-' . $t . '"><div>';
                    
                    // konfiguracja tekstow na bannerze
                    $konfig = array();
                    $unser = @unserialize($banner['konfiguracja_teksty']);
                    //
                    $byla_tablica = false;
                    //
                    if ( is_array($unser) ) {
                         //
                         if ( count($unser) > 5 ) {
                              //
                              $byla_tablica = true;
                              //
                              foreach ( $unser as $klucz => $wartosc ) {
                                 //
                                 $konfig[ $klucz ] = $wartosc;
                                 //
                              }
                              //
                         }
                         //
                    }                    
                    
                    $KlasaCss = 'G-txt-' . $banner['id_bannera'] . '-' . $t . '-' . $id_modulu;
                    
                    $IdKontenera = 'GrafikaDaneTekstu-' . $banner['id_bannera'] . '-' . $t . '-' . $id_modulu;
                    
                    $IdLinia = array();                    
                    $IdLinia[1] = 'Linia-1-' . $banner['id_bannera'] . '-' . $t . '-' . $id_modulu;
                    $IdLinia[2] = 'Linia-2-' . $banner['id_bannera'] . '-' . $t . '-' . $id_modulu;
                    $IdLinia[3] = 'Linia-3-' . $banner['id_bannera'] . '-' . $t . '-' . $id_modulu;
                    
                    // czy jest tekst
                    if ( $byla_tablica == true ) {
                         //
                         if ( nl2br(strip_tags((string)$konfig['txt_linia_1'])) == '' && nl2br(strip_tags((string)$konfig['txt_linia_2'])) == '' && nl2br(strip_tags((string)$konfig['txt_linia_3'])) == '' ) {
                              //
                              $tekst_na_bannerze = false;
                              //
                         }                         
                         //
                    } else {
                         //
                         $tekst_na_bannerze = false;
                         //
                    }
                    
                    if ( $banner['adres_url_bannera'] != '' ) {
                      
                        $wynik .= '<a ' . ((isset($konfig['txt_rodzaj_efektu']) && (int)$konfig['txt_rodzaj_efektu'] > 0) ? 'class="Efekt-' . (int)$konfig['txt_rodzaj_efektu'] . '"' : '') . ' href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>';
                        
                    }
                        
                        if ( $tekst_na_bannerze == true && $byla_tablica == true ) {
                             
                             $wynik .= '<figure class="GrafikiAnimacjaTekstu ' . $KlasaCss . '" data-id="' . $banner['id_bannera'] . '-' . $t . '-' . $id_modulu . '" data-animacja="' . (((int)$konfig['txt_rodzaj_animacji'] > 0) ? $konfig['txt_rodzaj_animacji'] : '0') . '">';
                              
                        }

                        // ladowanie bannerow z opoznieniem
                        if ( $preloader == true ) {
                             //
                             $wynik .= $this->bannerGenerujTag($banner, true);
                             //
                        } else {
                             //
                             $wynik .= $this->bannerGenerujTag($banner);
                             //
                        }
                        
                        if ( $tekst_na_bannerze == true && $byla_tablica == true ) {
                          
                             // jezeli nie ma byc animacja
                             if ( $bez_animacji == true ) {
                                  //
                                  $konfig['txt_rodzaj_animacji'] = 0;
                                  //
                             }
                             
                             $wynik .= '<figcaption class="GrafikaOpisKontener">';
                                
                                $wynik .= '<strong class="GrafikaDaneTekstu ' . $IdKontenera . (((int)$konfig['txt_rodzaj_animacji'] > 0) ? ' Animacja-' . $konfig['txt_rodzaj_animacji'] . '-DaneTekstu-Normal Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Wspolny-Normal' : '') . '">';

                                    // linia nr 1
                                    if ( isset($konfig['txt_linia_1']) && trim((string)$konfig['txt_linia_1']) != '' ) {
                                         //
                                         $wynik .= '<span class="TekstLinia"><span class="Linia-1 ' . $IdLinia[1] . (((int)$konfig['txt_rodzaj_animacji'] > 0) ? ' Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Linia-1-Normal Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Wspolny-Normal' : '') . '">' . nl2br(strip_tags((string)$konfig['txt_linia_1'])) . '</span></span>';
                                         //
                                    }
                                    
                                    // linia nr 2
                                    if ( isset($konfig['txt_linia_2']) && trim((string)$konfig['txt_linia_2']) != '' ) {
                                         //
                                         $wynik .= '<span class="TekstLinia"><span class="Linia-2 ' . $IdLinia[2] . (((int)$konfig['txt_rodzaj_animacji'] > 0) ? ' Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Linia-2-Normal Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Wspolny-Normal' : '') . '">' . nl2br(strip_tags((string)$konfig['txt_linia_2'])) . '</span></span>';
                                         //
                                    }
                                    
                                    // linia nr 3
                                    if ( isset($konfig['txt_linia_3']) && trim((string)$konfig['txt_linia_3']) != '' ) {
                                         //
                                         $wynik .= '<span class="TekstLinia"><span class="Linia-3 ' . $IdLinia[3] . (((int)$konfig['txt_rodzaj_animacji'] > 0) ? ' Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Linia-3-Normal Animacja-' . $konfig['txt_rodzaj_animacji'] . '-Wspolny-Normal' : '') . '">' . nl2br(strip_tags((string)$konfig['txt_linia_3'])) . '</span></span>';
                                         //
                                    }                                    
                                    
                                $wynik .= '</strong>';
                                
                             $wynik .= '</figcaption>';
                             
                             // css dla tla 
                             $kontener = array();
                             $kontener_pc = array(); // @media only screen and (min-width:1024px) {
                             $kontener_mobile = array(); // @media only screen and (max-width:1023px) {
                             $tlo = array();
                             //
                             
                             // wyrownanie tekstu w wierszach
                             if ( isset($konfig['txt_wyrownanie_tekstu']) ) {
                                  $tlo[] = 'text-align:' . $konfig['txt_wyrownanie_tekstu'];
                             }
                             
                             // szerokosc bloku tekstu w wersji pc
                             if ( isset($konfig['txt_szerokosc_tla_pc']) ) {
                                  $kontener_pc[] = 'width:' . $konfig['txt_szerokosc_tla_pc'] . '%';
                             }
                             
                             // szerokosc bloku tekstu w wersji mobilnej
                             if ( isset($konfig['txt_szerokosc_tla_mobile']) ) {
                                  $kontener_mobile[] = 'width:' . $konfig['txt_szerokosc_tla_mobile'] . '%';
                             }
                             
                             // kolor tla bloku tekstu
                             if ( isset($konfig['txt_kolor_tla']) && $konfig['txt_kolor_tla'] != '' ) {
                                  if ( isset($konfig['txt_przezroczystosc_tla']) && (int)$konfig['txt_przezroczystosc_tla'] == 100 ) {
                                       $tlo[] = 'background-color:#' . $konfig['txt_kolor_tla'];
                                  }
                                  if ( isset($konfig['txt_przezroczystosc_tla']) && (int)$konfig['txt_przezroczystosc_tla'] < 100 ) {                                    
                                       if ( strlen((string)$konfig['txt_kolor_tla']) == 6 ) { 
                                            list($r,$g,$b) = str_split($konfig['txt_kolor_tla'], 2);
                                            $tlo[] = 'background-color:rgba(' . hexdec($r) . ',' . hexdec($g) . ',' . hexdec($b) . ',' . ($konfig['txt_przezroczystosc_tla'] / 100) . ')';
                                        }                               
                                  }
                             }
                             
                             // odstep tla od tekstu - padding
                             if ( isset($konfig['txt_odstep_tla']) && (int)$konfig['txt_odstep_tla'] > 0 ) {
                                  $tlo[] = 'padding:' . $konfig['txt_odstep_tla'] . 'px';
                             }
                             
                             // grubosc ramki
                             if ( isset($konfig['txt_grubosc_ramki']) && (int)$konfig['txt_grubosc_ramki'] > 0 ) {
                                  if ( isset($konfig['txt_kolor_ramki_tla']) && $konfig['txt_kolor_ramki_tla'] > 0 ) {
                                       $tlo[] = 'border:' . $konfig['txt_grubosc_ramki'] . 'px solid #' . $konfig['txt_kolor_ramki_tla'];
                                  }
                             }
                             
                             // polozenie bloku tekstu (left;top....)
                             if ( isset($konfig['txt_polozenie_tekstu']) ) {
                                  $kontener[] = $konfig['txt_polozenie_tekstu'];
                             }
                             
                             // dodatkowe marginesy                             
                             if ( isset($konfig['txt_margines_gorny']) && (int)$konfig['txt_margines_gorny'] > 0 ) {   
                                  $kontener[] = 'margin-top:' . $konfig['txt_margines_gorny'] . 'px';
                             }
                             if ( isset($konfig['txt_margines_dolny']) && (int)$konfig['txt_margines_dolny'] > 0 ) {   
                                  $kontener[] = 'margin-bottom:' . $konfig['txt_margines_dolny'] . 'px';
                             }
                             if ( isset($konfig['txt_margines_lewy']) && (int)$konfig['txt_margines_lewy'] > 0 ) {   
                                  $kontener[] = 'margin-left:' . $konfig['txt_margines_lewy'] . 'px';
                             }
                             if ( isset($konfig['txt_margines_prawy']) && (int)$konfig['txt_margines_prawy'] > 0 ) {   
                                  $kontener[] = 'margin-right:' . $konfig['txt_margines_prawy'] . 'px';
                             }       

                             // linie
                             $linie = array();
                             //
                             for ( $w = 1; $w < 4; $w++ ) {

                                   // szerokosc linii - display czy inline-block
                                   if ( isset($konfig['txt_rozmiar_linia_' . $w]) ) {   
                                        $linie[$w][] = 'display:' . $konfig['txt_rozmiar_linia_' . $w];
                                   }
                                   
                                   // font-family czcionki
                                   if ( isset($konfig['txt_czcionka_linia_' . $w]) && $konfig['txt_czcionka_linia_' . $w] != '' ) {   
                                        $linie[$w][] = 'font-family:' . $konfig['txt_czcionka_linia_' . $w];
                                   } 
                                   
                                   // linie-height
                                   if ( isset($konfig['txt_odstep_linii_linia_' . $w]) ) {   
                                        $linie[$w][] = 'line-height:' . $konfig['txt_odstep_linii_linia_' . $w];
                                   }       
                                   
                                   // kolor czcionki
                                   if ( isset($konfig['txt_czcionka_kolor_linia_' . $w]) && $konfig['txt_czcionka_kolor_linia_' . $w] != '' ) {     
                                        $linie[$w][] = 'color:#' . $konfig['txt_czcionka_kolor_linia_' . $w];
                                   }  

                                   // grubosc czcionki
                                   if ( isset($konfig['txt_czcionka_grubosc_linia_' . $w]) ) {     
                                        $linie[$w][] = 'font-weight:' . $konfig['txt_czcionka_grubosc_linia_' . $w];
                                   }   

                                   // pochylenie czcionki
                                   if ( isset($konfig['txt_czcionka_pochylenie_linia_' . $w]) ) {     
                                        $linie[$w][] = 'font-style:' . $konfig['txt_czcionka_pochylenie_linia_' . $w];
                                   }                                        
                                   
                                   // cien czcionki
                                   if ( isset($konfig['txt_czcionka_cien_linia_' . $w]) && $konfig['txt_czcionka_cien_linia_' . $w] == 'tak' ) {     
                                        if ( isset($konfig['txt_czcionka_cien_kolor_linia_' . $w]) && !empty($konfig['txt_czcionka_cien_kolor_linia_' . $w]) ) {
                                             if ( isset($konfig['txt_czcionka_cien_poziomy_linia_' . $w]) && isset($konfig['txt_czcionka_cien_pion_linia_' . $w]) && isset($konfig['txt_czcionka_cien_rozmycie_linia_' . $w]) ) {                                               
                                                  $linie[$w][] = 'text-shadow:' . $konfig['txt_czcionka_cien_poziomy_linia_' . $w] . 'px ' . $konfig['txt_czcionka_cien_pion_linia_' . $w] . 'px ' . $konfig['txt_czcionka_cien_rozmycie_linia_' . $w] . 'px #' . $konfig['txt_czcionka_cien_kolor_linia_' . $w];
                                             }
                                        }
                                   }    
                                   
                                   // kolor tla linii tekstu
                                   if ( isset($konfig['txt_kolor_tla_linia_' . $w]) && $konfig['txt_kolor_tla_linia_' . $w] != '' ) {
                                        if ( isset($konfig['txt_przezroczystosc_tla_linia_' . $w]) && (int)$konfig['txt_przezroczystosc_tla_linia_' . $w] == 100 ) {
                                             $linie[$w][] = 'background-color:#' . $konfig['txt_kolor_tla_linia_' . $w];
                                        }
                                        if ( isset($konfig['txt_przezroczystosc_tla_linia_' . $w]) && (int)$konfig['txt_przezroczystosc_tla_linia_' . $w] < 100 ) {                                    
                                             if ( strlen((string)$konfig['txt_kolor_tla_linia_' . $w]) == 6 ) { 
                                                  list($r,$g,$b) = str_split($konfig['txt_kolor_tla_linia_' . $w], 2);
                                                  $linie[$w][] = 'background-color:rgba(' . hexdec($r) . ',' . hexdec($g) . ',' . hexdec($b) . ',' . ($konfig['txt_przezroczystosc_tla_linia_' . $w] / 100) . ')';
                                              }                               
                                        }
                                   }  
                                   
                                   // odstep tekstu od tla
                                   if ( isset($konfig['txt_odstep_tla_linia_' . $w]) && (int)$konfig['txt_odstep_tla_linia_' . $w] > 0 ) {     
                                        $linie[$w][] = 'padding:' . $konfig['txt_odstep_tla_linia_' . $w] . 'px';
                                   }                     
                                   
                                   // grubosc ramki linii
                                   if ( isset($konfig['txt_grubosc_ramki_linia_' . $w]) && (int)$konfig['txt_grubosc_ramki_linia_' . $w] > 0 ) {
                                        if ( isset($konfig['txt_kolor_ramki_tla_linia_' . $w]) ) {
                                             $linie[$w][] = 'border:' . $konfig['txt_grubosc_ramki_linia_' . $w] . 'px solid #' . $konfig['txt_kolor_ramki_tla_linia_' . $w];
                                        }
                                   }                                     
                             }
                             
                             $cssLinie = '';

                             foreach ( $linie as $klucz => $linia ) {
                                   //
                                   $cssLinie .= '.' . $IdLinia[$klucz] . ' { ' . implode('; ', (array)$linia) . '; }';
                                   //
                             }
                             
                             // odstep linii nr 2 od 1
                             if ( isset($konfig['txt_odstep_gorny_linia_2']) && (int)$konfig['txt_odstep_gorny_linia_2'] > 0 ) {
                                  $cssLinie .= '.' . $IdLinia[2] . ' { margin-top:' . $konfig['txt_odstep_gorny_linia_2'] . 'px; }';
                             }
                             
                             // odstep linii nr 3 od 2
                             if ( isset($konfig['txt_odstep_gorny_linia_3']) && (int)$konfig['txt_odstep_gorny_linia_3'] > 0 ) {
                                  $cssLinie .= '.' . $IdLinia[3] . ' { margin-top:' . $konfig['txt_odstep_gorny_linia_3'] . 'px; }';
                             }
                             
                             // ukrywanie tekstu dla malych rozdzielczosci
                             for ( $r = 1; $r < 4; $r++ ) {
                                   if ( isset($konfig['txt_mobile_tekst_linia_' . $r]) && $konfig['txt_mobile_tekst_linia_' . $r] == 'nie' ) {
                                        $cssLinie .= '@media only screen and (max-width:1023px) { .' . $IdLinia[$r] . ' { display:none; } }';
                                   }
                             }
                             
                             // rozmiar czcionki dla rozdzielczosci
                             $rozdzielczosci = array(300,480,800,1024,1200);
                             //
                             for ( $r = 1; $r < 4; $r++ ) {
                                   for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) {                             
                                         if ( isset($konfig['txt_rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $r]) ) {
                                              if ( (int)$konfig['txt_rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $r] > 100 ) {
                                                   $cssLinie .= '@media only screen and (min-width:' . $rozdzielczosci[$tr] . 'px) { .' . $IdLinia[$r] . ' { font-size:' . $konfig['txt_rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $r] . '%; } }';
                                              }
                                         }
                                   }
                             }
                             //
                             unset($rozdzielczosci);   

                             // ukrywanie calego bloku tekstu
                             $cssUkryjTekst = '';
                             if ( isset($konfig['txt_mobile_caly_tekst']) && $konfig['txt_mobile_caly_tekst'] == 'nie' ) {     
                                  $cssUkryjTekst = '@media only screen and (max-width:1023px) {
                                                      .' . $KlasaCss . ' .GrafikaOpisKontener { display:none; }                                          
                                                    }';
                             }                              
                             
                             $StylCss = '.' . $KlasaCss . ' .GrafikaOpisKontener { ' . implode('; ', (array)$kontener) . '; }
                                        ' . $cssUkryjTekst . '
                                        .' . $IdKontenera . ' { ' . implode('; ', (array)$tlo) . '; }
                                        @media only screen and (max-width:1023px) {
                                          .' . $KlasaCss . ' .GrafikaOpisKontener { ' . implode('; ', (array)$kontener_mobile) . '; }                                          
                                        }                                        
                                        @media only screen and (min-width:1024px) {
                                          .' . $KlasaCss . ' .GrafikaOpisKontener { ' . implode('; ', (array)$kontener_pc) . '; }                                          
                                        }
                                        ' . $cssLinie;
                             //
                             $GLOBALS['css'] .= $StylCss;
                             //
                             unset($StylCss);
                             //                                        
                             
                        }

                        if ( $tekst_na_bannerze == true && $byla_tablica == true ) {
                             
                             $wynik .= '</figure>';
                              
                        }                        
                        
                    if ( $banner['adres_url_bannera'] != '' ) {
                        $wynik .= '</a>';
                    }   

                    $wynik .= '</div></div>';
                    
                    return $wynik;
                    
                } else {
                  
                    $rozszerzenie = pathinfo(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                    
                    if ( !isset($rozszerzenie['extension']) ) {
                         //
                         $roz = explode('.', KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                         $rozszerzenie = $roz[ count($roz) - 1];
                         //
                    }

                    if ( $rozszerzenie['extension'] == 'mp4' && $banner['rodzaj_grafiki'] == 'film' ) {
                                            
                         $wynik = '<div class="GrafikaKreator GrafikaNr-' . $t . '">';
                         
                         if ( $banner['adres_url_bannera'] != '' ) {
                             $wynik .= '<a href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($banner) . '</a>';
                         } else {
                             $wynik .= $this->bannerGenerujTag($banner);
                         }                      
                         
                         $wynik .= '</div>';
                         
                         return $wynik;
                      
                    } else {
                                    
                        return;
                        
                    }
                    
                }
                
            } else {
              
                return;
                
            }
            
        }

        return;

    }    
    
    // funkcja uzywana do modulow bannerow przewijany, mieszany i przenikany 
    public function bannerWyswietlAnimowany($banner) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' ) {
                return;
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
              
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if (( $typ == '1' || $typ == '2' || $typ == '3' || $typ == '18' ) && $banner['rodzaj_grafiki'] == 'grafika') {

                    echo '<a href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>';
                    echo $this->bannerGenerujTag($banner);
                    
                    if ( !empty($banner['obrazek_alt_bannera']) ) {
                         echo '<span>' . $banner['obrazek_alt_bannera'] . '</span>';
                    }
                    
                    echo '</a>';

                } else {
                  
                    $rozszerzenie = pathinfo(KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                    
                    if ( !isset($rozszerzenie['extension']) ) {
                         //
                         $roz = explode('.', KATALOG_ZDJEC . '/' . $banner['obrazek_bannera']);
                         $rozszerzenie = $roz[ count($roz) - 1];
                         //
                    }

                    if ( $rozszerzenie['extension'] == 'mp4' && $banner['rodzaj_grafiki'] == 'film' ) {
                      
                         echo '<a style="display:block;text-align:center" href="reklama-b-' . $banner['id_bannera'] . '.html"' . (($banner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($banner) . '</a>';
                      
                    } else {
                                    
                        return;
                        
                    }
                    
                }
                
            } else {
            
                return;
                
            }
            
        }

        return;

    }    
        
    // funkcja uzywana do wyswietlania bannerow popup
    public function bannerWyswietlPopUp() {
      
        if ( BANNER_POPUP_WLACZONY == 'tak' ) {

            $tablicaBannerow = array();
            $tablicaBannerow = $this->info['POPUP'];

            // jezeli jest wybrana kategoria wyswietli tylko te bannery ktore sa przewidziane dla danej kategorii
            if (isset($_GET['idkat'])) {
              
                $PodzialGet = explode('_', (string)$_GET['idkat']);

                foreach ( $tablicaBannerow as $bannerPop ) {

                      // jezeli nie jest pusta
                      if ( !empty($bannerPop['kategorie_id']) ) {
                        
                          $PodzielTablice = explode(',', (string)$bannerPop['kategorie_id']);
                          //
                          
                          if ( in_array( $PodzialGet[ count($PodzialGet) - 1], $PodzielTablice ) ) {
                               //
                               $tablicaBannerow = array($bannerPop);
                               //
                          }
                          //
                          unset($PodzielTablice);
                        
                      }
                  
                }
      
                unset($PodzalGet);

            }
            
            if ( count($tablicaBannerow) > 0 )  {

                $wybranyBanner = Funkcje::wylosujElementyTablicyJakoTablica($tablicaBannerow,'1');
                
                $wybranyBanner = $wybranyBanner[0];

                $wynik = '';

                if (file_exists(KATALOG_ZDJEC . '/' . $wybranyBanner['obrazek_bannera']) && !empty($wybranyBanner['obrazek_bannera'])) {
                  
                    // pobranie parametrow pliku
                    list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $wybranyBanner['obrazek_bannera']);

                    // jezeli jest to obrazek
                    if (( $typ == '1' || $typ == '2' || $typ == '3' || $typ == '18' ) && $wybranyBanner['rodzaj_grafiki'] == 'grafika') {
                      
                        if ( $wybranyBanner['adres_url_bannera'] != '' ) {
                            $tresc = '<a href="reklama-b-' . $wybranyBanner['id_bannera'] . '.html"' . (($wybranyBanner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($wybranyBanner) . '</a>';
                        } else {
                            $tresc = $this->bannerGenerujTag($wybranyBanner);
                        }

                    } else {
                      
                        $rozszerzenie = pathinfo(KATALOG_ZDJEC . '/' . $wybranyBanner['obrazek_bannera']);
                        
                        if ( !isset($rozszerzenie['extension']) ) {
                             //
                             $roz = explode('.', KATALOG_ZDJEC . '/' . $wybranyBanner['obrazek_bannera']);
                             $rozszerzenie = $roz[ count($roz) - 1];
                             //
                        }

                        if ( $rozszerzenie['extension'] == 'mp4' && $wybranyBanner['rodzaj_grafiki'] == 'film' ) {
                          
                             if ( $wybranyBanner['adres_url_bannera'] != '' ) {
                                  $tresc = '<a href="reklama-b-' . $wybranyBanner['id_bannera'] . '.html"' . (($wybranyBanner['adres_okno'] == 'tak') ? ' target="_blank"' : '') . '>' . $this->bannerGenerujTag($wybranyBanner) . '</a>';
                             } else {
                                  $tresc = $this->bannerGenerujTag($wybranyBanner);
                             }

                        } else {
                                        
                             return;
                            
                        }
                        
                    }

                    $wynik = '<div id="TloPopUp"' . ((BANNER_POPUP_EKRAN_SCIEMNIAJ == 'tak') ? ' class="TloPopUpCiemne"' : '') . '>
                    
                                <div id="OknoPopUp">
                                
                                    <div id="PopUpZawartosc">
                                        
                                        <div id="PopUpTylkoZdjecie">
                                          
                                            <div id="PopUpZamknij"></div>
                                            
                                            ' . $tresc . '
                                            
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                                
                              </div>';
                    
                } else if (!empty($wybranyBanner['tekst_bannera']))  {
                  
                    $wynik = '<div id="TloPopUp"' . ((BANNER_POPUP_EKRAN_SCIEMNIAJ == 'tak') ? ' class="TloPopUpCiemne"' : '') . '>
                    
                                <div id="OknoPopUp">
                                
                                    <div id="PopUpZawartosc">
                                        
                                        <div id="PopUpTylkoTekst">
                                          
                                            <div id="PopUpZamknij"></div>
                                            
                                            ' . html_entity_decode($wybranyBanner['tekst_bannera']) . '
                                            
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                                
                              </div>';              

                }
                
                $wynik .= '<script>';
                $wynik .= '$.GrafikaPopup();';
                
                // co ile klikniec
                
                $ile_klikniec = 1;
                
                if ( BANNER_POPUP_ILOSC_KLIKNIEC != '0' ) {
                  
                     if ( isset($_COOKIE['popup']) && (int)$_COOKIE['popup'] > 0 ) { 
                     
                          $ile_klikniec = (int)$_COOKIE['popup'] - 1;
                          
                          if ( $ile_klikniec == 0 ) {
                               
                               $ile_klikniec = BANNER_POPUP_ILOSC_KLIKNIEC;
                               
                          }

                     }
                     
                     $wynik .= 'ustawCookie(\'popup\', ' . $ile_klikniec . ', ' . ((BANNER_POPUP_WAZNOSC_COOKIE != '0') ? BANNER_POPUP_WAZNOSC_COOKIE : 0) . ');';

                } else {
                  
                     $wynik .= 'ustawCookie(\'popup\', 0, ' . ((BANNER_POPUP_WAZNOSC_COOKIE != '0') ? BANNER_POPUP_WAZNOSC_COOKIE : 0) . ');';
                  
                }
                
                // opoznienie otwierania
                if ( $ile_klikniec == 1 ) {
                  
                     if ( BANNER_POPUP_OPOZNIENIE != '0' ) {

                         $wynik .= 'setTimeout(function(){ PokazGrafikaPopup(); }, ' . (BANNER_POPUP_OPOZNIENIE * 1000) . ');';
         
                     } else {
                        
                         $wynik .= 'PokazGrafikaPopup();';
                         
                     }
                     
                     // automatyczne zamykanie po okreslonym czasie        
                     if ( BANNER_POPUP_AUTOCLOSE != '0' ) {
                       
                          $wynik .= 'setTimeout(function(){ zamknijGrafikaPopup(' . ((BANNER_POPUP_ILOSC_KLIKNIEC != '0') ? 1 : 0) . '); }, ' . (BANNER_POPUP_AUTOCLOSE * 1000) . ');';
                       
                     }
                    
                }

                $wynik .= '</script>';        

                return $wynik;
                
            }
            
        }

    }


} 

?>