<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        //
        $tablica = array();
        //
        $wykluczenia = array( 'akcja',
                              'opis',
                              'wyswietla',
                              'polozenie',
                              'szerokosc_modul',
                              'tlo_modulu',
                              'rwd',
                              'margines_gorny',
                              'margines_dolny',
                              'margines_lewy',
                              'margines_prawy',
                              'odstep_gorny',
                              'odstep_dolny',
                              'odstep_lewy',
                              'odstep_prawy',
                              'kolor',
                              'tlo_szerokosc',
                              'tlo_obrazkowe',
                              'tlo_powtarzanie',
                              'tlo_skalowanie',
                              'modul_podstrony_polozenie',
                              'strony');
                             
        foreach ( $_POST as $klucz => $wartosc ) {
             //
             if ( !in_array((string)$klucz, $wykluczenia) ) {
                  //
                  if ( is_array($wartosc) && strpos((string)$klucz, 'film_youtube_kolumna') === false && 
                                             strpos((string)$klucz, 'film_filmmp4_kolumna') === false && 
                                             strpos((string)$klucz, 'film_szerokosc_filmmp4_kolumna') === false && 
                                             strpos((string)$klucz, 'film_wysokosc_filmmp4_kolumna') === false && 
                                             strpos((string)$klucz, 'film_nazwa_filmmp4_kolumna') === false  && 
                                             strpos((string)$klucz, 'film_link_filmmp4_kolumna') === false ) {
                       //
                       foreach ( $wartosc as $klc => $wart ) {
                          //
                          if ( is_array($wart) ) {
                               //
                               $wartosc[$klc] = implode(',', $wart);
                               //
                          }
                          //
                       }
                       //
                  }
                  if ( is_array($wartosc) && ( strpos((string)$klucz, 'opis_kolumna_') > -1 || strpos((string)$klucz, 'opis_nazwy_kolumny_') > -1 ) ) {
                       //
                       $tmp = array();
                       //
                       foreach ( $wartosc as $klc => $wart ) {
                          //
                          $tmp[$klc] = $filtr->process($wart);
                          //
                       }
                       //
                       $wartosc = $tmp;
                       //
                  }
                  //
                  $tablica[ $klucz ] = $wartosc;
                  //
             }
             //
        }
        //
        $pola = array(
                array('modul_type','kreator'),
                array('modul_header',0),
                array('modul_header_link',''),
                array('modul_description',$filtr->process($_POST['opis'])),
                array('modul_big_description',$filtr->process($_POST['opis_konfiguracja'])),
                array('modul_display',$filtr->process($_POST['wyswietla'])),
                array('modul_localization',(int)$_POST['polozenie']),
                array('modul_width',$filtr->process($_POST['szerokosc_modul'])),
                array('modul_background_type',$filtr->process($_POST['tlo_modulu'])),
                array('modul_column_array',serialize($tablica)),
                array('modul_align_column',$filtr->process($_POST['wyrownanie_kolumn'])),
                array('modul_rwd',1),                
                array('modul_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']),
                array('modul_wcag_color',(int)$_POST['wcag_kolor']),
                array('modul_preload',(int)$_POST['doladowanie']),
                array('modul_v2',1)); 

        unset($tablica);

        // dodatkowe marginesy
        $pola[] = array('modul_margines_top',(int)$_POST['margines_gorny']);
        $pola[] = array('modul_margines_bottom',(int)$_POST['margines_dolny']);
        $pola[] = array('modul_margines_left',(int)$_POST['margines_lewy']);
        $pola[] = array('modul_margines_right',(int)$_POST['margines_prawy']);
        
        // dodatkowe odstepy
        $pola[] = array('modul_padding_top',(int)$_POST['odstep_gorny']);
        $pola[] = array('modul_padding_bottom',(int)$_POST['odstep_dolny']);
        $pola[] = array('modul_padding_left',(int)$_POST['odstep_lewy']);
        $pola[] = array('modul_padding_right',(int)$_POST['odstep_prawy']);        
        
        // tlo modulu
        if ( $_POST['tlo_modulu'] == 'brak' ) {
            $pola[] = array('modul_background_value','');
            $pola[] = array('modul_background_repeat','');
            $pola[] = array('modul_background_attachment','');
            $pola[] = array('modul_background_width','');
            $pola[] = array('modul_background_image_width','');
        }
        if ( $_POST['tlo_modulu'] == 'kolor' ) {
            $pola[] = array('modul_background_value',$filtr->process($_POST['kolor']));
            $pola[] = array('modul_background_repeat','');
            $pola[] = array('modul_background_attachment','');
            $pola[] = array('modul_background_width',$filtr->process($_POST['tlo_szerokosc']));
            $pola[] = array('modul_background_image_width','');
        }        
        if ( $_POST['tlo_modulu'] == 'obrazek' ) {
            $pola[] = array('modul_background_value',$filtr->process($_POST['tlo_obrazkowe']));
            $pola[] = array('modul_background_repeat',$filtr->process($_POST['tlo_powtarzanie']));
            $pola[] = array('modul_background_attachment',$filtr->process($_POST['tlo_przewijanie']));
            $pola[] = array('modul_background_width',$filtr->process($_POST['tlo_szerokosc']));
            $pola[] = array('modul_background_image_width',$filtr->process($_POST['tlo_skalowanie']));
        }               
                
        // jezeli ma byc wyswietlany na podstronach
        if ( (int)$_POST['polozenie'] == 2 ) {
            $pola[] = array('modul_localization_position','gora');
          } else {
            $pola[] = array('modul_localization_position',$filtr->process($_POST['modul_podstrony_polozenie']));
        }                
        
        // jezeli jest wyswietlany na podstronach (wybranych)
        if ( (int)$_POST['polozenie'] == 3 ) {
             if ( isset($_POST['strony']) && count($_POST['strony']) > 0 ) {
                  $pola[] = array('modul_localization_site',implode(';', (array)$filtr->process($_POST['strony'])));
             } else {
                  $pola[] = array('modul_localization_site','');
             }
        } else {
             $pola[] = array('modul_localization_site','');
        }    

        // jezeli jest wyswietlany na podstronach w formie linkow
        if ( (int)$_POST['polozenie'] == 4 ) {
             if ( isset($_POST['linki_strony']) && $_POST['linki_strony'] != '' ) {
                  $pola[] = array('modul_localization_link', $filtr->process($_POST['linki_strony']));
             } else {
                  $pola[] = array('modul_localization_link','');
             }
        } else {
             $pola[] = array('modul_localization_link','');
        }             
        
        // jezeli jest indywidualny modul
        if (isset($_POST['modul_wyglad']) && $_POST['modul_wyglad'] == '1') {
            $pola[] = array('modul_theme',$filtr->process($_POST['modul_wyglad']));
            $pola[] = array('modul_theme_file',$filtr->process($_POST['plik_wyglad']));         
        }
        if (isset($_POST['modul_wyglad']) && $_POST['modul_wyglad'] == '0') {
            $pola[] = array('modul_theme','0');
            $pola[] = array('modul_theme_file','');          
        }            
        
        if ( !isset($_POST['id_poz']) ) {
          
            $pola[] = array('modul_status','0');

            $sql = $db->insert_query('theme_modules' , $pola);
            $id_pozycji = $db->last_id_query();
            
            unset($pola);
            
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //            
                $pola = array(
                        array('modul_id',$id_pozycji),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('modul_title','Kreator modułów'));  

                $sql = $db->insert_query('theme_modules_description' , $pola);
                unset($pola);
            }    

        } else {
          
            $id_pozycji = (int)$_POST['id_poz'];
            
            $sql = $db->update_query('theme_modules' , $pola, " modul_id = '" . $id_pozycji . "'");
            
        }
        
        if (isset($id_pozycji) && $id_pozycji > 0) {

            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('srodek_kreator_modulow.php?id_poz=' . (int)$id_pozycji . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka='.(int)$_POST['zakladka'] : ''));
                //
              } else {        
                //
                if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
                  
                    Funkcje::PrzekierowanieURL('wyglad.php?zakladka='.(int)$_POST['zakladka']);
                  
                  } else {
                  
                    Funkcje::PrzekierowanieURL('srodek.php?id_poz='.(int)$id_pozycji);
                    
                } 
                //
            }
            
        } else {
            //
            Funkcje::PrzekierowanieURL('srodek.php');
            //
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }    
    
    $konfig = array();
    $byla_tablica = false;
    
    if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {

         $zapytanie = "select * from theme_modules where modul_id = '" . (int)$_GET['id_poz'] . "' and modul_type = 'kreator'";
         $sql = $db->open_query($zapytanie);
        
         if ((int)$db->ile_rekordow($sql) > 0) {
           
             $info = $sql->fetch_assoc();
        
             foreach ( $info as $klucz => $wartosc ) {
                //
                if ( $klucz != 'modul_column_array' ) {
                     //
                     $konfig[ $klucz ] = $wartosc;
                     //
                } else {
                     //
                     $unser = @unserialize($info['modul_column_array']);
                     //
                     if ( is_array($unser) ) {
                          //
                          $byla_tablica = true;
                          //
                          foreach ( $unser as $dod_klucz => $dod_wartosc ) {
                             //
                             $konfig[ $dod_klucz ] = $dod_wartosc;
                             //
                          }
                          //
                     }
                     //
                     unset($unser);
                     //
                }
                //
             }
             
             unset($info);         
             $db->close_query($sql);        
 
         }
         
    }
    
    if ( $byla_tablica == false ) {
         $konfig = array();
    }
    
    //echo '<pre>';
    //print_r($konfig);
    //echo '</pre>';
    ?>
    
    <div id="naglowek_cont">Kreator nowych modułów</div>
    
    <div id="cont">
    
        <script type="text/javascript" src="programy/jscolor/jscolor.js"></script>
        <script src="programy/ace/ace.js"></script>
        
        <form action="wyglad/srodek_kreator_modulow.php" method="post" id="modForm" class="cmxform">          
        
            <input type="hidden" name="akcja" value="zapisz" />

            <div class="poleForm">
            
              <div class="naglowek"><?php echo ((count($konfig) > 0) ? 'Edycja' : 'Dodawanie'); ?> danych</div>
              
              <div class="pozycja_edytowana">
              
                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                  <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                  <?php } ?>      

                  <?php if (isset($_GET['id_poz']) && count($konfig) > 0 ) { ?>
                  <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />
                  <?php } ?>                   
                  
                  <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                  <div class="KreatorModulu">
                  
                      <p> 
                          <label>Układ modułu:</label>
                      </p>
                      
                      <div class="WybieranieRodzajuModulu">
                      
                          <div class="OknaRodzajuWyboru">
                          
                              <div class="OknaWyboruWygladu<?php echo (((isset($konfig['ile_kolumn']) && $konfig['ile_kolumn'] == '1') || !isset($konfig['ile_kolumn'])) ? ' OknaWyboruWygladuAktywny' : ''); ?>" data-nr="1" data-id="Pierwsza">                                
                                    <span class="JednaPozycja">TREŚĆ</span>
                                    <strong>Układ 1 kolumnowy</strong>
                              </div>
                                                          
                              <div class="OknaWyboruWygladu<?php echo ((isset($konfig['ile_kolumn']) && $konfig['ile_kolumn'] == '2') ? ' OknaWyboruWygladuAktywny' : ''); ?>" data-nr="2" data-id="Druga">                                 
                                    <span class="JednaPozycja DwiePozycjeLewa">TREŚĆ</span>
                                    <span class="JednaPozycja DwiePozycjePrawa">TREŚĆ</span>
                                    <div class="cl"></div>
                                    <strong>Układ 2 kolumnowy</strong>
                              </div>
                              
                              <div class="OknaWyboruWygladu<?php echo ((isset($konfig['ile_kolumn']) && $konfig['ile_kolumn'] == '3') ? ' OknaWyboruWygladuAktywny' : ''); ?>" data-nr="3" data-id="Trzecia">                                 
                                    <span class="JednaPozycja TrzyPozycjeLewa">TREŚĆ</span>
                                    <span class="JednaPozycja TrzyPozycjeSrodek">TREŚĆ</span>
                                    <span class="JednaPozycja TrzyPozycjePrawa">TREŚĆ</span>
                                    <div class="cl"></div>
                                    <strong>Układ 3 kolumnowy</strong>
                              </div>                                
                              
                              <div class="cl"></div>
                              
                          </div>
                          
                          <input type="hidden" name="ile_kolumn" id="ile_kolumn" value="<?php echo ((isset($konfig['ile_kolumn'])) ? $konfig['ile_kolumn'] : '1'); ?>" />
                          
                          <div class="maleInfo">Podział na kolumny dotyczy wyglądu sklepu w wersji na PC (dla dużych rozdzielczości). W wersji mobilnej moduły będą wyświetlane jeden pod drugim.</div>
                          
                      </div>
                      
                      <div class="cl"></div>
                      
                      <div id="ProporcjeDwochKolumn" <?php echo (((isset($konfig['ile_kolumn']) && $konfig['ile_kolumn'] == '2')) ? '' : 'style="display:none"'); ?>>
                      
                          <p> 
                              <label>Proporcje kolumn:</label>
                          </p>
                          
                          <div class="ProporcjeKolumny">
                          
                              <div class="OknaWyboruProporcji">
                              
                                  <?php if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { ?>

                                      <div class="OknoProporcji">   
                                            <div class="WygladOknoProporcje">
                                                <span style="width:50%; border-bottom:2px solid #868686;">Tab 1</span>
                                                <span style="width:50%; border-bottom:2px solid #868686;">Tab 2</span>
                                            </div>
                                            <strong><input type="radio" value="zakladki" name="proporcje_2_kolumny" id="proporcje_2_kolumny_zakladki" <?php echo (((isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] == 'zakladki')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_2_kolumny_zakladki">wybierz</label></strong>
                                      </div>

                                  <?php } else {

                                      if ( isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] == 'zakladki' ) {
                                          ?>
                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:50%; border-bottom:2px solid #868686;">Tab 1</span>
                                                    <span style="width:50%; border-bottom:2px solid #868686;">Tab 2</span>
                                                </div>
                                                <strong><input type="radio" value="zakladki" name="proporcje_2_kolumny" id="proporcje_2_kolumny_zakladki" <?php echo (((isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] == 'zakladki')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_2_kolumny_zakladki">wybierz</label></strong>
                                          </div>
                                          <?php
                                      }

                                  } ?>

                                  <?php if ( !isset($_SESSION['programista']) || ( isset($_SESSION['programista']) && $_SESSION['programista'] != '1') ) { ?>

                                      <?php if ( !isset($konfig['proporcje_2_kolumny']) || (isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] != 'zakladki') ) { ?>

                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:50%">50%</span>
                                                    <span style="width:50%">50%</span>
                                                </div>
                                                <strong><input type="radio" value="50-50" name="proporcje_2_kolumny" id="proporcje_2_kolumny_50_50" <?php echo (((isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] == '50-50') || !isset($konfig['proporcje_2_kolumny'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_2_kolumny_50_50">wybierz</label></strong>
                                          </div>
                                          
                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:33%">33%</span>
                                                    <span style="width:66%">66%</span>
                                                </div>
                                                <strong><input type="radio" value="33-66" name="proporcje_2_kolumny" id="proporcje_2_kolumny_33-66" <?php echo (((isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] == '33-66')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_2_kolumny_33-66">wybierz</label></strong>
                                          </div>                                  

                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:66%">66%</span>
                                                    <span style="width:33%">33%</span>
                                                </div>
                                                <strong><input type="radio" value="66-33" name="proporcje_2_kolumny" id="proporcje_2_kolumny_66-33" <?php echo (((isset($konfig['proporcje_2_kolumny']) && $konfig['proporcje_2_kolumny'] == '66-33')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_2_kolumny_66-33">wybierz</label></strong>
                                          </div>    
                                      
                                      <?php } ?>

                                  <?php } ?>

                                  <div class="cl"></div>
                                  
                              </div>

                          </div> 

                      </div>
                      
                      <div id="ProporcjeTrzechKolumn" <?php echo (((isset($konfig['ile_kolumn']) && $konfig['ile_kolumn'] == '3')) ? '' : 'style="display:none"'); ?>>
                      
                          <p> 
                              <label>Proporcje kolumn:</label>
                          </p>
                          
                          <div class="ProporcjeKolumny">
                          
                              <div class="OknaWyboruProporcji">
                              
                                  <?php if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { ?>

                                      <div class="OknoProporcji">   
                                            <div class="WygladOknoProporcje">
                                                <span style="width:33%; border-bottom:2px solid #868686;">Tab 1</span>
                                                <span style="width:33%; border-bottom:2px solid #868686;">Tab 2</span>
                                                <span style="width:33%; border-bottom:2px solid #868686;">Tab 3</span>
                                            </div>
                                            <strong><input type="radio" value="zakladki" name="proporcje_3_kolumny" id="proporcje_3_kolumny_zakladki" <?php echo (((isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == 'zakladki')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_3_kolumny_zakladki">wybierz</label></strong>
                                      </div>

                                  <?php } else {

                                      if ( isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == 'zakladki' ) {
                                          ?>
                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:33%; border-bottom:2px solid #868686;">Tab 1</span>
                                                    <span style="width:33%; border-bottom:2px solid #868686;">Tab 2</span>
                                                    <span style="width:33%; border-bottom:2px solid #868686;">Tab 3</span>
                                                </div>
                                                <strong><input type="radio" value="zakladki" name="proporcje_3_kolumny" id="proporcje_3_kolumny_zakladki" <?php echo (((isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == 'zakladki')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_3_kolumny_zakladki">wybierz</label></strong>
                                          </div>
                                          <?php
                                      }

                                  } ?>

                                  <?php if ( !isset($_SESSION['programista']) || ( isset($_SESSION['programista']) && $_SESSION['programista'] != '1') ) { ?>

                                      <?php if ( !isset($konfig['proporcje_3_kolumny']) || (isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] != 'zakladki') ) { ?>

                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:33%">33%</span>
                                                    <span style="width:33%">33%</span>
                                                    <span style="width:33%">33%</span>
                                                </div>
                                                <strong><input type="radio" value="33-33-33" name="proporcje_3_kolumny" id="proporcje_3_kolumny_33-33-33" <?php echo (((isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == '33-33-33') || !isset($konfig['proporcje_3_kolumny'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_3_kolumny_33-33-33">wybierz</label></strong>
                                          </div>
                                          
                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:25%">25%</span>
                                                    <span style="width:50%">50%</span>
                                                    <span style="width:25%">25%</span>
                                                </div>
                                                <strong><input type="radio" value="25-50-25" name="proporcje_3_kolumny" id="proporcje_3_kolumny_25-50-25" <?php echo (((isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == '25-50-25')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_3_kolumny_25-50-25">wybierz</label></strong>
                                          </div>                                  

                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:25%">25%</span>
                                                    <span style="width:25%">25%</span>
                                                    <span style="width:50%">50%</span>
                                                </div>
                                                <strong><input type="radio" value="25-25-50" name="proporcje_3_kolumny" id="proporcje_3_kolumny_25-25-50" <?php echo (((isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == '25-25-50')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_3_kolumny_25-25-50">wybierz</label></strong>
                                          </div>  

                                          <div class="OknoProporcji">   
                                                <div class="WygladOknoProporcje">
                                                    <span style="width:50%">50%</span>
                                                    <span style="width:25%">25%</span>
                                                    <span style="width:25%">25%</span>                                            
                                                </div>
                                                <strong><input type="radio" value="50-25-25" name="proporcje_3_kolumny" id="proporcje_3_kolumny_50-25-25" <?php echo (((isset($konfig['proporcje_3_kolumny']) && $konfig['proporcje_3_kolumny'] == '50-25-25')) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="proporcje_3_kolumny_50-25-25">wybierz</label></strong>
                                          </div>                                     
                                      
                                      <?php } ?>

                                  <?php } ?>

                                  <div class="cl"></div>
                                  
                              </div>

                          </div> 

                      </div>                      
                      
                      <script>
                      $(document).ready(function() {
                          //
                          $('.OknaWyboruWygladu').click(function() {
                              //
                              $('#ekr_preloader').css('display','block');
                              //
                              var nr = $(this).attr('data-id');
                              var ile_kolumn = $(this).attr('data-nr');
                              //
                              $('#ile_kolumn').val( ile_kolumn );
                              //
                              $('.OknaWyboruWygladu').removeClass('OknaWyboruWygladuAktywny');
                              $(this).addClass('OknaWyboruWygladuAktywny');
                              //
                              if ( nr == 'Pierwsza' ) {
                                   $('.KolumnaDruga').stop().slideUp();
                                   $('.KolumnaTrzecia').stop().slideUp();
                                   //
                                   $('#ProporcjeDwochKolumn').stop().slideUp();
                                   $('#ProporcjeTrzechKolumn').stop().slideUp();
                                   //
                              }
                              if ( nr == 'Druga' ) {
                                   $('.KolumnaDruga').stop().slideDown();
                                   $('.KolumnaTrzecia').stop().slideUp();
                                   //
                                   $('#ProporcjeDwochKolumn').stop().slideDown();
                                   $('#ProporcjeTrzechKolumn').stop().slideUp();
                                   //                                   
                              }                                
                              if ( nr == 'Trzecia' ) {
                                   $('.KolumnaDruga').stop().slideDown();
                                   $('.KolumnaTrzecia').stop().slideDown();
                                   //
                                   $('#ProporcjeDwochKolumn').stop().slideUp();
                                   $('#ProporcjeTrzechKolumn').stop().slideDown();
                                   //                                   
                              }      
                              //
                              setTimeout(function() { $('#ekr_preloader').stop().fadeOut() }, 300);
                              //
                          });
                          //
                          $("#modForm").validate({
                            rules: {
                              "nazwa_kolumna_<?php echo $ile_jezykow[0]['id']; ?>[pierwsza]": {
                                required: true
                              },
                              "nazwa_kolumna_<?php echo $ile_jezykow[0]['id']; ?>[druga]": {
                                required: function(element) {
                                  if ($(".KolumnaDruga").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },
                              "nazwa_kolumna_<?php echo $ile_jezykow[0]['id']; ?>[trzecia]": {
                                required: function(element) {
                                  if ($(".KolumnaTrzecia").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },                              
                              "plik_wyglad_kolumna[pierwsza]": {
                                required: function(element) {
                                  if ($("#wyglad_kolumna_pierwsza").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },
                              "plik_wyglad_kolumna[druga]": {
                                required: function(element) {
                                  if ($("#wyglad_kolumna_druga").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },
                              "plik_wyglad_kolumna[trzecia]": {
                                required: function(element) {
                                  if ($("#wyglad_kolumna_trzecia").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },  
                              "plik_listing_kolumna[pierwsza]": {
                                required: function(element) {
                                  if ($("#listing_kolumna_pierwsza").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },
                              "plik_listing_kolumna[druga]": {
                                required: function(element) {
                                  if ($("#listing_kolumna_druga").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },
                              "plik_listing_kolumna[trzecia]": {
                                required: function(element) {
                                  if ($("#listing_kolumna_trzecia").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              },
                              plik_wyglad: {
                                required: function(element) {
                                  if ($("#wyglad").css('display') == 'block') {
                                      return true;
                                    } else {
                                      return false;
                                  }
                                }
                              }                               
                            },
                            messages: {
                              "nazwa_kolumna_<?php echo $ile_jezykow[0]['id']; ?>[pierwsza]": {
                                required: "Pole jest wymagane."
                              },
                              "nazwa_kolumna_<?php echo $ile_jezykow[0]['id']; ?>[druga]": {
                                required: "Pole jest wymagane."
                              },
                              "nazwa_kolumna_<?php echo $ile_jezykow[0]['id']; ?>[trzecia]": {
                                required: "Pole jest wymagane."
                              },                               
                              "plik_wyglad_kolumna[pierwsza]": {
                                required: "Pole jest wymagane."
                              },
                              "plik_wyglad_kolumna[druga]": {
                                required: "Pole jest wymagane."
                              },
                              "plik_wyglad_kolumna[trzecia]": {
                                required: "Pole jest wymagane."
                              },  
                              "plik_listing_kolumna[pierwsza]": {
                                required: "Pole jest wymagane."
                              },
                              "plik_listing_kolumna[druga]": {
                                required: "Pole jest wymagane."
                              },
                              "plik_listing_kolumna[trzecia]": {
                                required: "Pole jest wymagane."
                              },
                              plik_wyglad: {
                                required: "Pole jest wymagane."
                              }                                  
                            }
                          });  
                          //
                          $('.WyswietalneDane div').click(function() {
                              //
                              var rodzaj = $(this).attr('data-rodzaj');
                              var kolumna = $(this).attr('data-kolumna');
                              var kolumna_nazwa = $(this).attr('data-nazwa');
                              //
                              $('#dane_kolumna_' + kolumna).val( rodzaj );
                              //
                              zakres_kolumna(rodzaj, kolumna_nazwa, kolumna);
                              //
                              $('.WyswietlaneDane' + kolumna_nazwa + ' div').removeClass('WyswietalneDaneAktywny');
                              $(this).addClass('WyswietalneDaneAktywny');
                              //     
                              // ukrywanie ustawien statycznych dla bannerow
                              if ( $('#dane_kolumna_' + kolumna).val() == 'bannery' ) {
                                   if ( $('#forma_wyswietlania_kolumna_' + kolumna).val() == 'statyczny' ) {
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaBannerowStatyczne').show();
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaBannerowAnimowane').hide();
                                   } else {
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaBannerowStatyczne').hide();
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaBannerowAnimowane').show();                                        
                                   }
                              } else {
                                   $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaBannerowStatyczne').hide();
                                   $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaBannerowAnimowane').hide();
                              }  
                              // ukrywanie ustawien statycznych dla producentow
                              if ( $('#dane_kolumna_' + kolumna).val() == 'producenci' ) {
                                   if ( $('#forma_wyswietlania_kolumna_' + kolumna).val() == 'statyczny' ) {
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaProducentowStatyczne').show();
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaProducentowAnimowane').hide();
                                   } else {
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaProducentowStatyczne').hide();
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaProducentowAnimowane').show();                                        
                                   }
                              } else {
                                   $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaProducentowStatyczne').hide();
                                   $('.Kolumna' + kolumna_nazwa + 'ZakresUstawieniaProducentowAnimowane').hide();
                              } 
                              // ukrywanie paska animacji
                              if ( rodzaj == 'bannery' ) {
                                   if ( $('#animacja_sama_' + kolumna + ' option:selected').val() == 'nie' ) {
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresPasekAnimacji').hide();
                                   } else {
                                        $('.Kolumna' + kolumna_nazwa + 'ZakresPasekAnimacji').show();
                                   }
                              } else {
                                   $('.Kolumna' + kolumna_nazwa + 'ZakresPasekAnimacji').hide();
                              }
                              //
                          });
                          //
                      });
                      
                      function zakres_kolumna(tryb, nr, kolumna) {
                          $('.Kolumna' + nr + ' .KolumnaZakres').hide();
                          if (tryb == 'bannery') {
                               $('.Kolumna' + nr + 'ZakresBannery').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();
                          }
                          if (tryb == 'produkty') {
                               $('.Kolumna' + nr + 'ZakresProdukty').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }
                          if (tryb == 'recenzje') {
                               $('.Kolumna' + nr + 'ZakresRecenzje').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }     
                          if (tryb == 'aktualnosci') {
                               $('.Kolumna' + nr + 'ZakresAktualnosci').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }  
                          if (tryb == 'strony_info') {
                               $('.Kolumna' + nr + 'ZakresStronyInformacyjne').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }                            
                          if (tryb == 'producenci') {
                               $('.Kolumna' + nr + 'ZakresProducenci').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }   
                          if (tryb == 'kategorie') {
                               $('.Kolumna' + nr + 'ZakresKategorie').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }     
                          if (tryb == 'youtube') {
                               $('.Kolumna' + nr + 'ZakresYoutube').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }  
                          if (tryb == 'filmmp4') {
                               $('.Kolumna' + nr + 'ZakresFilmMp4').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown(); 
                               //
                               zmien_film('100_' + kolumna, nr);
                               //                               
                          }                            
                          if (tryb == 'opis') {
                               $('.Kolumna' + nr + 'ZakresOpis').show(); 
                               $('.SposobWyswietlania' + nr).hide();
                               //
                               zmien_edytor('0_' + kolumna, nr);
                               //
                               // zmiana listingu na standardowy
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideUp();
                               $('#listing_kolumna_' + kolumna).hide();
                               $('#listing_standardowy_kolumna_' + kolumna).prop('checked',true);
                               $('#listing_indywidualny_kolumna_' + kolumna).prop('checked',false);                               
                          }
                          if (tryb == 'java') {
                               $('.Kolumna' + nr + 'ZakresKod').stop().slideDown();
                               $('.SposobWyswietlania' + nr).hide();
                               //
                               // zmiana listingu na standardowy
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideUp();                               
                               $('#listing_kolumna_' + kolumna).hide();
                               $('#listing_standardowy_kolumna_' + kolumna).prop('checked',true);
                               $('#listing_indywidualny_kolumna_' + kolumna).prop('checked',false);                                  
                          }   
                          if (tryb == 'opiniesklep') {
                               $('.Kolumna' + nr + 'ZakresOpinieSklep').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }  
                          if (tryb == 'produkt_dnia') {
                               $('.Kolumna' + nr + 'ZakresProduktDnia').stop().slideDown();
                               $('.SposobWyswietlania' + nr).hide()
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }    
                          if (tryb == 'galerie') {
                               $('.Kolumna' + nr + 'ZakresGalerie').stop().slideDown();
                               $('.SposobWyswietlania' + nr).stop().slideDown();
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }  
                          if (tryb == 'ankiety') {
                               $('.Kolumna' + nr + 'ZakresAnkiety').stop().slideDown();
                               $('.SposobWyswietlania' + nr).hide()
                               $('.Kolumna' + nr + 'WygladListingu').stop().slideDown();                               
                          }                              
                      }                    

                      function zakres_produktow_kolumna(wartosc, nr) {
                          if ( wartosc == 'kategoria' || wartosc == 'producent' || wartosc == 'warunki' ) {
                            if ( wartosc == 'kategoria' ) {
                                 $('.Kolumna' + nr + 'ZakresProduktyProducenci').stop().slideUp();
                                 $('.Kolumna' + nr + 'ZakresProduktyWarunki').stop().slideUp();
                                 $('.Kolumna' + nr + 'ZakresProduktyKategorie').stop().slideDown();
                            }
                            if ( wartosc == 'producent' ) {
                                 $('.Kolumna' + nr + 'ZakresProduktyKategorie').stop().slideUp();
                                 $('.Kolumna' + nr + 'ZakresProduktyWarunki').stop().slideUp();
                                 $('.Kolumna' + nr + 'ZakresProduktyProducenci').stop().slideDown();
                            }                                  
                            if ( wartosc == 'warunki' ) {
                                 $('.Kolumna' + nr + 'ZakresProduktyKategorie').stop().slideUp();
                                 $('.Kolumna' + nr + 'ZakresProduktyProducenci').stop().slideUp();
                                 $('.Kolumna' + nr + 'ZakresProduktyWarunki').stop().slideDown();                                 
                            }                                                              
                          } else { 
                            $('.Kolumna' + nr + 'ZakresProduktyProducenci, .Kolumna' + nr + 'ZakresProduktyKategorie, .Kolumna' + nr + 'ZakresProduktyWarunki').stop().slideUp();
                          }
                      }    
                      
                      function zakres_aktualnosci_kolumna(wartosc, nr) {
                          if ( wartosc == '999999' ) {
                            $('.Kolumna' + nr + 'ZakresAktualnosciWarunki').stop().slideDown();                                                                                             
                          } else { 
                            $('.Kolumna' + nr + 'ZakresAktualnosciWarunki').stop().slideUp();
                          }
                      }  
                      
                      function zakres_kategoria_kolumna(wartosc, nr) {
                          if ( wartosc == 'wszystkie' ) {
                               $('.Kolumna' + nr + 'WyborKategorie').stop().slideUp();
                            } else {
                               $('.Kolumna' + nr + 'WyborKategorie').stop().slideDown();
                          }                                
                      }   

                      function zakres_producenci_kolumna(wartosc, nr) {
                          if ( wartosc == 'wszyscy' ) {
                               $('.Kolumna' + nr + 'WyborProducenci').stop().slideUp();
                            } else {
                               $('.Kolumna' + nr + 'WyborProducenci').stop().slideDown();
                          }                                
                      }                       

                      function forma_animacji_kolumna(tryb, nr, kolumna) {
                          if (tryb == 'statyczny') {                                 
                               $('.Kolumna' + nr + 'ZakresUstawieniaOgolneAnimacja').stop().slideUp();
                               //
                               if ( $('#dane_kolumna_' + kolumna).val() == 'bannery' ) {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowStatyczne').stop().slideDown();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowAnimowane').stop().slideUp();
                               } else {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowStatyczne').stop().slideUp();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowAnimowane').stop().slideUp();                                    
                               }
                               //
                               if ( $('#dane_kolumna_' + kolumna).val() == 'producenci' ) {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowStatyczne').stop().slideDown();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowAnimowane').stop().slideUp();
                               } else {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowStatyczne').stop().slideUp();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowAnimowane').stop().slideUp();                                    
                               }                               
                               //
                             } else {
                               $('.Kolumna' + nr + 'ZakresUstawieniaOgolneAnimacja').stop().slideDown();
                               //
                               if ( $('#dane_kolumna_' + kolumna).val() == 'bannery' ) {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowStatyczne').stop().slideUp();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowAnimowane').stop().slideDown();
                               } else {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowStatyczne').stop().slideUp();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaBannerowAnimowane').stop().slideUp();                                    
                               }  
                               //
                               if ( $('#dane_kolumna_' + kolumna).val() == 'producenci' ) {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowStatyczne').stop().slideUp();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowAnimowane').stop().slideDown();
                               } else {
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowStatyczne').stop().slideUp();
                                    $('.Kolumna' + nr + 'ZakresUstawieniaProducentowAnimowane').stop().slideUp();                                    
                               }                                  
                          }
                      }       

                      function zmien_naglowek(id, nr) {
                          if (id == 0) {
                              $('#naglowek_link_kolumna_' + nr).stop().slideUp();
                             } else {
                              $('#naglowek_link_kolumna_' + nr).stop().slideDown();
                          }
                      }          

                      function zakladki_moduly(nr, nazwa) {
                          //
                          $('.Tytuly' + nazwa).find('.TytulyKolumn').hide();
                          $('.Zakladki' + nazwa).find('span').removeClass('a_href_info_tab_wlaczona');
                          //
                          $("#link_" + nr).addClass('a_href_info_tab_wlaczona');
                          $('#info_tab_id_' + nr).stop().fadeIn(); 
                          //
                          var jest_edytor = false;
                          //
                          for(var i in CKEDITOR.instances) {
                            if (CKEDITOR.instances[CKEDITOR.instances[i].name]) {
                                if ( CKEDITOR.instances[i].name == 'edytor_nazwa_' + nr ) {
                                     jest_edytor = true;
                                }
                            }
                          }                               
                          //
                          if ( jest_edytor == false ) {
                               ckedit('edytor_nazwa_' + nr,'99%','100');
                          }
                          //
                      }
                      
                      function zmien_film(nr, nazwa) {
                          //
                          $('.ZakladkiFilmMp4' + nazwa + ' span').removeClass('a_href_info_tab_wlaczona');  
                          $('#filmmp4_link_' + nr).addClass('a_href_info_tab_wlaczona');
                          //
                          $('.Kolumna' + nazwa + 'ZakresFilmMp4 .PoleFilmMp4').hide();
                          $('#info_tab_filmmp4_id_' + nr).stop().fadeIn();
                          //                       
                      }
                      
                      function zmien_edytor(nr, nazwa) {
                          //
                          $('.ZakladkiOpis' + nazwa + ' span').removeClass('a_href_info_tab_wlaczona');  
                          $('#opis_link_' + nr).addClass('a_href_info_tab_wlaczona');
                          //
                          $('.Kolumna' + nazwa + 'ZakresOpis .PoleEdytora').hide();
                          $('#info_tab_opis_id_' + nr).stop().fadeIn();
                          //
                          for(var i in CKEDITOR.instances) {
                            if (CKEDITOR.instances[CKEDITOR.instances[i].name]) {
                                if ( CKEDITOR.instances[i].name.indexOf('edytor_nazwa_') == -1 ) {
                                     CKEDITOR.instances[CKEDITOR.instances[i].name].destroy();
                                }
                            }
                          }     
                          //
                          for ( x = 1; x < 4; x++ ) {
                              //
                              if ( x == 1 ) {
                                   var NazwaZak = 'Pierwsza';
                                   var NrZak = 'pierwsza';
                              }
                              if ( x == 2 ) {
                                   var NazwaZak = 'Druga';
                                   var NrZak = 'druga';
                              }
                              if ( x == 3 ) {
                                   var NazwaZak = 'Trzecia';
                                   var NrZak = 'trzecia';
                              }                                
                              //
                              if ( $('.Kolumna' + NazwaZak + 'ZakresOpis').css('display') == 'block' ) {
                                   var nr_jezyka = 0;
                                   $('.ZakladkiOpis' + NazwaZak + ' span').each(function() {
                                       if ( $(this).attr('class').indexOf('a_href_info_tab_wlaczona') > -1 ) {
                                            ckedit('edytor_' + $(this).attr('data-nr') + '_' + NrZak,'99%','200'); 
                                        }
                                   });
                              }                           
                          }
                          
                      }   

                      function zmien_wyglad(id, kolumna, nazwa) {
                        if (id == 0 || id == 2) {
                            $('#wyglad_kolumna_' + kolumna).stop().slideUp();
                           } else {
                            $('#wyglad_kolumna_' + kolumna).stop().slideDown();
                        }
                        if (id == 2) {
                            $('#naglowek_link_kolumna_' + kolumna).hide();
                            $('#ModulNaglowek' + nazwa).hide();                           
                            $('#naglowek_kolumna_' + kolumna + '_tak').prop('checked',false);
                            $('#naglowek_kolumna_' + kolumna + '_nie').prop('checked',true);
                        } else {
                            $('#naglowek_link_kolumna_' + kolumna).show();
                            $('#ModulNaglowek' + nazwa).show();
                            $('#naglowek_kolumna_' + kolumna + '_tak').prop('checked',true);
                            $('#naglowek_kolumna_' + kolumna + '_nie').prop('checked',false);                        
                        }                         
                      }
                      
                      function zmien_wyglad_caly_modul(id) {
                          if (id == 0 ) {
                              $('#wyglad').stop().slideUp();
                             } else {
                              $('#wyglad').stop().slideDown();
                          }                   
                      }                       

                      function zmien_listing(id, kolumna, nazwa) {
                        if (id == 0) {
                            $('#listing_kolumna_' + kolumna).stop().slideUp();
                           } else {
                            $('#listing_kolumna_' + kolumna).stop().slideDown();
                        }                         
                      }                        

                      function zmien_polozenie(id) {
                          if (id == 1) {
                              $('#podstrony').slideDown();
                              $('#lista_podstron').slideUp();
                              $('#lista_linki').slideUp();
                          } else if (id == 3) {
                              $('#lista_podstron').slideDown();
                              $('#podstrony').slideDown();
                              $('#lista_linki').slideUp();
                          } else if (id == 4) {
                              $('#lista_linki').slideDown();
                              $('#lista_podstron').slideUp();
                              $('#podstrony').slideDown();
                          } else if (id == 2) {
                              $('#podstrony').slideUp();
                              $('#lista_podstron').slideUp();
                              $('#lista_linki').slideUp();
                          }                          
                      }           

                      function zmien_strzalki(wartosc, nr) {
                          if ( wartosc == 'tak' ) {
                               $('.Kolumna' + nr + 'ZakresUstawieniaStrzalek').stop().slideDown();
                          } else {
                               $('.Kolumna' + nr + 'ZakresUstawieniaStrzalek').stop().slideUp();
                          }
                      }  

                      function zmien_kropki(wartosc, nr) {
                          if ( wartosc == 'tak' ) {
                               $('.Kolumna' + nr + 'ZakresUstawieniaKropek').stop().slideDown();
                          } else {
                               $('.Kolumna' + nr + 'ZakresUstawieniaKropek').stop().slideUp();
                          }
                      }  

                      function zmien_polozenie_strzalek(wartosc, nr) {
                          if ( wartosc == 'tak' ) {
                               $('.Kolumna' + nr + 'ZakresPolozeniaStrzalek').stop().slideDown();
                          } else {
                               $('.Kolumna' + nr + 'ZakresPolozeniaStrzalek').stop().slideUp();
                          }
                      }                         
                      
                      function zmien_czas_animacji(wartosc, nr) {
                          if ( wartosc == 'tak' ) {
                               $('.Kolumna' + nr + 'ZakresUstawieniaCzasuAnimacji').stop().slideDown();
                               $('.Kolumna' + nr + 'ZakresPasekAnimacji').show();
                          } else {
                               $('.Kolumna' + nr + 'ZakresUstawieniaCzasuAnimacji').stop().slideUp();
                               $('.Kolumna' + nr + 'ZakresPasekAnimacji').hide();
                          }
                      }               

                      function zmien_pasek_animacji(wartosc, nr) {
                          if ( wartosc == 'tak' ) {
                               $('.Kolumna' + nr + 'ZakresUstawieniaPaskaAnimacji').stop().slideDown();
                          } else {
                               $('.Kolumna' + nr + 'ZakresUstawieniaPaskaAnimacji').stop().slideUp();
                          }
                      }              
                      </script>                             
                      
                      <?php for ( $q = 1; $q < 4; $q++ ) {
                            
                            if ( $q == 1 ) {
                                 //
                                 $NrKolumna = 'pierwsza';
                                 //
                            }
                            if ( $q == 2 ) {
                                 //
                                 $NrKolumna = 'druga';
                                 //
                            }
                            if ( $q == 3 ) {
                                 //
                                 $NrKolumna = 'trzecia';
                                 //
                            }
                            ?>
                            
                            <div class="ZakresWyswietlania Kolumna<?php echo ucfirst($NrKolumna); ?>" <?php echo (($q > ((isset($konfig['ile_kolumn'])) ? (int)$konfig['ile_kolumn'] : 1)) ? ' style="display:none"' : ''); ?>>

                                <div class="TytulKolumna">Zakres wyświetlanych danych w kolumnie nr <?php echo $q; ?></div>

                                <div class="RamkaZakresuWyswietlania">

                                    <div class="info_tab Zakladki<?php echo ucfirst($NrKolumna); ?>">
                                    <?php
                                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                        echo '<span id="link_' . $w . '_' . $NrKolumna . '" class="a_href_info_tab" onclick="zakladki_moduly(\'' . $w . '_' . $NrKolumna . '\', \'' . ucfirst($NrKolumna) . '\')">' . $ile_jezykow[$w]['text'] . '</span>';
                                    }                    
                                    ?>                   
                                    </div>
                                    
                                    <div style="clear:both"></div>
                                    
                                    <div class="info_tab_content Tytuly<?php echo ucfirst($NrKolumna); ?>">
                                    
                                        <?php for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) { ?>
                                            
                                            <div id="info_tab_id_<?php echo $w . '_' . $NrKolumna; ?>" class="TytulyKolumn" style="display:none;">
                                            
                                                <p>
                                                    <label <?php echo (($w == 0) ? 'class="required"' : ''); ?> for="nazwa_<?php echo $w; ?>_<?php echo $NrKolumna; ?>">Nazwa kolumny:</label>   
                                                    <input type="text" name="nazwa_kolumna_<?php echo $ile_jezykow[$w]['id']; ?>[<?php echo $NrKolumna; ?>]" style="min-width:50%" size="45" value="<?php echo ((isset($konfig['nazwa_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna])) ? $konfig['nazwa_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna] : ''); ?>" id="nazwa_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" />
                                                </p> 
                                                
                                                <p>
                                                    <label for="nazwa_druga_<?php echo $w; ?>_<?php echo $NrKolumna; ?>">Wiersz nr 1:</label>   
                                                    <input type="text" name="nazwa_druga_kolumna_<?php echo $ile_jezykow[$w]['id']; ?>[<?php echo $NrKolumna; ?>]" style="min-width:50%" size="45" value="<?php echo ((isset($konfig['nazwa_druga_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna])) ? $konfig['nazwa_druga_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna] : ''); ?>" id="nazwa_druga_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" />
                                                    <em class="TipIkona"><b>Dodatkowa nazwa wyświetlana pod głównym tytułem (wyświetlana mniejszą czcionką)</b></em>
                                                </p>                                                
                                                
                                                <p>
                                                    <label for="nazwa_trzecia_<?php echo $w; ?>_<?php echo $NrKolumna; ?>">Wiersz nr 2:</label>   
                                                    <input type="text" name="nazwa_trzecia_kolumna_<?php echo $ile_jezykow[$w]['id']; ?>[<?php echo $NrKolumna; ?>]" style="min-width:50%" size="45" value="<?php echo ((isset($konfig['nazwa_trzecia_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna])) ? $konfig['nazwa_trzecia_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna] : ''); ?>" id="nazwa_trzecia_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" />
                                                    <em class="TipIkona"><b>Dodatkowa nazwa wyświetlana pod głównym tytułem (wyświetlana mniejszą czcionką)</b></em>
                                                </p>                                                                                                

                                                <div class="OpisKolumny">                                
                                                    <label>Opis kolumny:</label> 
                                                    <textarea cols="80" rows="10" id="edytor_nazwa_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" name="opis_nazwy_kolumny_<?php echo $ile_jezykow[$w]['id']; ?>[<?php echo $NrKolumna; ?>]"><?php echo ((isset($konfig['opis_nazwy_kolumny_' . $ile_jezykow[$w]['id']][$NrKolumna])) ? $konfig['opis_nazwy_kolumny_' . $ile_jezykow[$w]['id']][$NrKolumna] : ''); ?></textarea>      
                                                </div>
                                                            
                                            </div>
                                        
                                        <?php } ?>     
                                        
                                    </div>                

                                    <script>
                                    ckedit('edytor_nazwa_0_<?php echo $NrKolumna; ?>','99%','100');
                                    zakladki_moduly('0_<?php echo $NrKolumna; ?>','<?php echo ucfirst($NrKolumna); ?>');
                                    </script>      

                                    <p>
                                      <label>Czy wyświetlać opis kolumny przy małych rozdzielczościach (na urządzeniach mobilnych) ?</label>
                                      <input type="radio" value="tak" name="rwd_mala_rozdzielczosc_opis_kolumna[<?php echo $NrKolumna; ?>]" id="rwd_mala_rozdzielczosc_opis_kolumna_tak_<?php echo $NrKolumna; ?>" <?php echo (((isset($konfig['rwd_mala_rozdzielczosc_opis_kolumna'][$NrKolumna]) && $konfig['rwd_mala_rozdzielczosc_opis_kolumna'][$NrKolumna] == 'tak') || !isset($konfig['rwd_mala_rozdzielczosc_opis_kolumna'][$NrKolumna])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_mala_rozdzielczosc_opis_kolumna_tak_<?php echo $NrKolumna; ?>">tak<em class="TipIkona"><b>Opis kolumny będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                                      <input type="radio" value="nie" name="rwd_mala_rozdzielczosc_opis_kolumna[<?php echo $NrKolumna; ?>]" id="rwd_mala_rozdzielczosc_opis_kolumna_nie_<?php echo $NrKolumna; ?>" <?php echo ((isset($konfig['rwd_mala_rozdzielczosc_opis_kolumna'][$NrKolumna]) && $konfig['rwd_mala_rozdzielczosc_opis_kolumna'][$NrKolumna] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_mala_rozdzielczosc_opis_kolumna_nie_<?php echo $NrKolumna; ?>">nie<em class="TipIkona"><b>Opis kolumny nie będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                                    </p>                                     
                                    
                                    <div id="ModulNaglowek<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna]) && $konfig['kolumna_wyglad_kolumna'][$NrKolumna] == '2') ? 'style="display:none;margin-top:15px"' : 'style="margin-top:15px"'); ?>>
                                    
                                        <div class="TytulDzialu" style="margin-top:0px">Nagłówek kolumny</div>                                    
                                    
                                        <p>
                                            <label>Nagłówek kolumny:</label>
                                            <input type="radio" value="1" name="naglowek_kolumna[<?php echo $NrKolumna; ?>]" id="naglowek_kolumna_<?php echo $NrKolumna; ?>_tak" <?php echo (((isset($konfig['naglowek_kolumna'][$NrKolumna]) && $konfig['naglowek_kolumna'][$NrKolumna] == '1') || !isset($konfig['naglowek_kolumna'][$NrKolumna])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_kolumna_<?php echo $NrKolumna; ?>_tak" onclick="zmien_naglowek(1,'<?php echo $NrKolumna; ?>')">tak<em class="TipIkona"><b>W tej kolumnie modułu będzie wyświetał się nagłówek z nazwą</b></em></label>
                                            <input type="radio" value="0" name="naglowek_kolumna[<?php echo $NrKolumna; ?>]" id="naglowek_kolumna_<?php echo $NrKolumna; ?>_nie" <?php echo ((isset($konfig['naglowek_kolumna'][$NrKolumna]) && $konfig['naglowek_kolumna'][$NrKolumna] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_kolumna_<?php echo $NrKolumna; ?>_nie" onclick="zmien_naglowek(0,'<?php echo $NrKolumna; ?>')">nie<em class="TipIkona"><b>W tej kolumnie modułu nie będzie się wyświetał nagłówek z nazwą - tylko sama treść</b></em></label>
                                        </p>
                                        
                                        <p id="naglowek_link_kolumna_<?php echo $NrKolumna; ?>" <?php echo ((isset($konfig['naglowek_kolumna'][$NrKolumna]) && $konfig['naglowek_kolumna'][$NrKolumna] == '0') ? 'style="display:none"' : ''); ?>>
                                            <label for="naglowek_link_kolumna_<?php echo $NrKolumna; ?>">Link nagłówka kolumny:</label>
                                            <input type="text" name="naglowek_link_kolumna[<?php echo $NrKolumna; ?>]" id="naglowek_link_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['naglowek_link_kolumna'][$NrKolumna])) ? $konfig['naglowek_link_kolumna'][$NrKolumna] : ''); ?>" size="50" /><em class="TipIkona"><b>Adres do jakiego ma prowadzić nagłówek - np nowosci.html</b></em>
                                        </p>   
                                        
                                        <p>
                                          <label for="color_naglowek_kolumna_<?php echo $NrKolumna; ?>">Kolor tekstu nagłówka (pierwszego wiersza):</label>
                                          <input name="kolor_naglowek_kolumna[<?php echo $NrKolumna; ?>]" class="color {required:false}" id="color_naglowek_kolumna_<?php echo $NrKolumna; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['kolor_naglowek_kolumna'][$NrKolumna])) ? $konfig['kolor_naglowek_kolumna'][$NrKolumna] : ''); ?>" size="8" />                    
                                        </p>                                        
                                    
                                    </div>
                                    
                                    <div class="TytulDzialu">Rodzaj wyświetlanych danych w kolumnie</div>

                                    <div class="ZakresDanychLabel">
                                      <label for="dane_kolumna_<?php echo $NrKolumna; ?>">Wyświetlane dane w kolumnie nr <?php echo $q; ?>:</label>             
                                      
                                      <div class="WyswietalneDane WyswietlaneDane<?php echo ucfirst($NrKolumna); ?>">
                                          <div data-rodzaj="bannery" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo (((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'bannery') || !isset($konfig['dane_kolumna'][$NrKolumna])) ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_galeria.png" alt="Bannery graficzne" /><b>bannery <br /> graficzne</b></div>
                                          <div data-rodzaj="produkty" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'produkty') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_produkty.png" alt="Produkty" /><b>wybrane <br /> produkty</b></div>
                                          <div data-rodzaj="produkt_dnia" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'produkt_dnia') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_produkt_dnia.png" alt="Produkt dnia" /><b>produkt <br /> dnia</b></div>
                                          <div data-rodzaj="recenzje" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'recenzje') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_recenzje.png" alt="Recenzje" /><b>recenzje o <br /> produktach</b></div>
                                          <div data-rodzaj="aktualnosci" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'aktualnosci') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_artykuly.png" alt="Artykuły" /><b>artykuły z <br /> aktualności</b></div>
                                          <div data-rodzaj="strony_info" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'strony_info') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_artykuly.png" alt="Strony informacyjne" /><b>strony <br /> informacyjne</b></div>
                                          <div data-rodzaj="producenci" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'producenci') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_producenci.png" alt="Producenci" /><b>producenci <br /> produktów</b></div>
                                          <div data-rodzaj="kategorie" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'kategorie') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_kategorie.png" alt="Kategorie" /><b>kategorie <br /> produktów</b></div>
                                          <div data-rodzaj="youtube" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'youtube') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_youtube.png" alt="Youtube" /><b>filmy z <br /> YouTube</b></div>
                                          <div data-rodzaj="filmmp4" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'filmmp4') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_mp4.png" alt="Filmy Mp4" /><b>filmy <br /> Mp4</b></div>
                                          <div data-rodzaj="opis" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'opis') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_tekst.png" alt="Dowolny tekst" /><b>dowolny <br /> tekst</b></div>
                                          <div data-rodzaj="java" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'java') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_kod.png" alt="Dowolny tekst" /><b>dowolny skrypt <br /> np javascript</b></div>
                                          <div data-rodzaj="opiniesklep" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'opiniesklep') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_opiniesklep.png" alt="Opinie o sklepie" /><b>opinie o <br /> sklepie</b></div>
                                          <div data-rodzaj="galerie" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'galerie') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_galerie.png" alt="Galerie" /><b>galerie <br /> grafik</b></div>
                                          <div data-rodzaj="ankiety" data-kolumna="<?php echo $NrKolumna; ?>" data-nazwa="<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'ankiety') ? 'class="WyswietalneDaneAktywny"' : ''); ?>><img src="obrazki/modul_ankiety.png" alt="Ankiety" /><b>ankiety</b></div>
                                      </div>
                                      
                                      <input type="hidden" name="dane_kolumna[<?php echo $NrKolumna; ?>]" id="dane_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['dane_kolumna'][$NrKolumna])) ? $konfig['dane_kolumna'][$NrKolumna] : 'bannery'); ?>" />
                                      
                                    </div>       

                                    <div class="cl"></div>
                                    
                                    <!--- ################ ZAKRES BANNERY ############ -->

                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresBannery" <?php echo (((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'bannery') || !isset($konfig['dane_kolumna'][$NrKolumna])) ? '' : 'style="display:none"'); ?>>
                                    
                                        <p>
                                          <label for="grupa">Wyświetlaj bannery z grupy:</label>             
                                          <?php
                                          echo Funkcje::RozwijaneMenu('grupa_bannerow_kolumna[' . $NrKolumna . ']', BoxyModuly::ListaGrupBannerow(), ((isset($konfig['grupa_bannerow_kolumna'][$NrKolumna])) ? $konfig['grupa_bannerow_kolumna'][$NrKolumna] : ''), ' id="grupa_bannerow_kolumna_' . $NrKolumna . '" style="width:400px"'); 
                                          ?>
                                        </p>    
                                        
                                        <p>
                                          <label for="ilosc_bannerow_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych bannerów:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_bannerow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_bannerow_kolumna'][$NrKolumna])) ? $konfig['ilosc_bannerow_kolumna'][$NrKolumna] : ''), ' id="ilosc_bannerow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>         

                                        <div class="TytulDzialu">Ustawienia wyświetlania bannerów</div>
                                        
                                        <p>
                                          <label for="sortowanie_bannerow_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania bannerów:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'losowo', 'text' => 'losowo'),
                                                            array('id' => 'sort', 'text' => 'sortowanie'));
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_bannerow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_bannerow_kolumna'][$NrKolumna])) ? $konfig['sortowanie_bannerow_kolumna'][$NrKolumna] : ''), ' id="sortowanie_bannerow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                           

                                    </div>

                                    <!--- ################ ZAKRES PRODUKTY ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresProdukty" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'produkty') ? '' : 'style="display:none"'); ?>>
                                    
                                        <p>
                                          <label for="grupa_produktow_kolumna_<?php echo $NrKolumna; ?>">Wyświetlaj produkty z grupy produktów:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'bestsellery', 'text' => 'Bestsellery'),
                                                            array('id' => 'hity', 'text' => 'Hity'),
                                                            array('id' => 'nowosci', 'text' => 'Nowości'),
                                                            array('id' => 'polecane', 'text' => 'Produkty polecane'),
                                                            array('id' => 'promocje', 'text' => 'Promocje'),
                                                            array('id' => 'promocje_czasowe', 'text' => 'Promocje czasowe (z zegarem odliczającym czas)'),
                                                            array('id' => 'wyprzedaz', 'text' => 'Wyprzedaż'),
                                                            array('id' => 'oczekiwane', 'text' => 'Produkty oczekiwane'),
                                                            array('id' => 'produkty', 'text' => 'Wszystkie produkty'),
                                                            array('id' => 'kategoria', 'text' => 'Kategoria produktów'),
                                                            array('id' => 'producent', 'text' => 'Producent'),
                                                            array('id' => 'warunki', 'text' => 'Wg wybranych warunków'),
                                                            array('id' => 'poprzednio_ogladane', 'text' => 'Ostatnio oglądane produkty') );

                                          echo Funkcje::RozwijaneMenu('grupa_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['grupa_produktow_kolumna'][$NrKolumna])) ? $konfig['grupa_produktow_kolumna'][$NrKolumna] : ''), ' onchange="zakres_produktow_kolumna(this.value,\'' . ucfirst($NrKolumna) . '\')" id="grupa_produktow_kolumna_' . $NrKolumna . '" style="width:330px"');
                                          unset($tablica);
                                          ?>
                                        </p>    

                                        <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresProduktyProducenci" <?php echo ((isset($konfig['grupa_produktow_kolumna'][$NrKolumna]) && $konfig['grupa_produktow_kolumna'][$NrKolumna] == 'producent') ? '' : 'style="display:none"'); ?>>
                                            
                                            <p> 
                                                <label>Wybierz producenta:</label>
                                            </p>                                     
                                            
                                            <div class="WybieranieZakresu">
                                            
                                                <div class="OknoKategoriaProducenci">
                                        
                                                    <?php
                                                    $Prd = Funkcje::TablicaProducenci();
                                                    //
                                                    if (count($Prd) > 0) {
                                                        //
                                                        echo '<table class="pkc">';
                                                        //
                                                        for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                                            echo '<tr>                                
                                                                    <td class="lfp">
                                                                        <input type="radio" value="' . $Prd[$b]['id'] . '" name="id_producent_kolumna[' . $NrKolumna . ']" id="id_producent_kolumna_' .$NrKolumna . '_' . $Prd[$b]['id'] . '" ' . (((isset($konfig['id_producent_kolumna'][$NrKolumna]) && $konfig['id_producent_kolumna'][$NrKolumna] == $Prd[$b]['id']) || (!isset($konfig['id_producent_kolumna'][$NrKolumna]) && $b == 0)) ? 'checked="checked"' : '') . ' /> <label class="OpisFor" for="id_producent_kolumna_' .$NrKolumna . '_' . $Prd[$b]['id'] . '">' . $Prd[$b]['text'] . '</label>
                                                                    </td>                                
                                                                  </tr>';
                                                        }
                                                        echo '</table>';
                                                        //
                                                    } else {
                                                        //
                                                        echo '<table class="pkc">';
                                                        //
                                                        echo '<tr>                                
                                                                <td class="lfp">
                                                                    <input type="radio" value="999999999" name="id_producent_kolumna[' . $NrKolumna . ']" id="id_producent_kolumna_' .$NrKolumna . '_0" checked="checked" /> <label class="OpisFor" for="id_producent_kolumna_' .$NrKolumna . '_0">-- brak producentów --</label>
                                                                </td>                                
                                                              </tr>';
                                                        //
                                                        echo '</table>';
                                                        //
                                                    }
                                                    unset($Prd);
                                                    ?> 
                                                    
                                                </div>
                                            
                                            </div>
                                            
                                        </div>  

                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresProduktyKategorie" <?php echo ((isset($konfig['grupa_produktow_kolumna'][$NrKolumna]) && $konfig['grupa_produktow_kolumna'][$NrKolumna] == 'kategoria') ? '' : 'style="display:none"'); ?>>
                                            
                                            <p> 
                                                <label>Wybierz kategorię:</label>
                                            </p>                                     
                                            
                                            <div class="WybieranieZakresu">
                                            
                                                <div class="OknoKategoriaProducenci">
                                                
                                                    <table class="pkc">
                                        
                                                    <?php
                                                    $b = 0;
                                                    foreach ( Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica ) {
                                                        //
                                                        echo '<tr><td class="lfp"><input type="radio" value="' . $Tablica['id'] . '" name="id_kategoria_kolumna[' . $NrKolumna . ']" id="id_kategoria_kolumna_' .$NrKolumna . '_' . $Tablica['id'] . '" ' . (((isset($konfig['id_kategoria_kolumna'][$NrKolumna]) && $konfig['id_kategoria_kolumna'][$NrKolumna] == $Tablica['id']) || (!isset($konfig['id_kategoria_kolumna'][$NrKolumna]) && $b == 0)) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="id_kategoria_kolumna_' .$NrKolumna . '_' . $Tablica['id'] . '">' . $Tablica['text'] . '</label>';
                                                                   
                                                              if ( isset($Tablica['podkategorie']) && is_array($Tablica['podkategorie']) ) {
                                                              
                                                                  echo '<table>';
                                                                  
                                                                  foreach ( $Tablica['podkategorie'] as $PodkatId => $Podkat ) {
                                                                      //
                                                                      echo '<tr><td class="lfp"><input type="radio" value="' . $Podkat['id'] . '" name="id_kategoria_kolumna[' . $NrKolumna . ']" id="id_kategoria_kolumna_' .$NrKolumna . '_' . $Podkat['id'] . '" ' . ((isset($konfig['id_kategoria_kolumna'][$NrKolumna]) && $konfig['id_kategoria_kolumna'][$NrKolumna] == $Podkat['id']) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="id_kategoria_kolumna_' .$NrKolumna . '_' . $Podkat['id'] . '">' . $Podkat['text'] . '</label></td></tr>';
                                                                      //
                                                                  }
                                                                  
                                                                  echo '</table>';
                                                                  
                                                              }                                                       
                                                                   
                                                        echo '</td></tr>';
                                                        //
                                                        $b++;
                                                        //
                                                    }
                                                    //
                                                    if ( $b == 0 ) {
                                                         //
                                                         echo '<tr>                                
                                                                 <td class="lfp">
                                                                     <input type="radio" value="999999999" name="id_kategoria_kolumna[' . $NrKolumna . ']" id="id_kategoria_kolumna_' .$NrKolumna . '_0" checked="checked" /> <label class="OpisFor" for="id_kategoria_kolumna_' .$NrKolumna . '_0">-- brak kategorii --</label>
                                                                 </td>                                
                                                               </tr>';
                                                         //
                                                    } 
                                                    ?> 
                                                    
                                                    </table>
                                                    
                                                </div>
                                            
                                            </div>
                                            
                                        </div>  
                                        
                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresProduktyWarunki" <?php echo ((isset($konfig['grupa_produktow_kolumna'][$NrKolumna]) && $konfig['grupa_produktow_kolumna'][$NrKolumna] == 'warunki') ? '' : 'style="display:none"'); ?>>

                                            <p>
                                              <label for="warunki_produkty_nazwa_kolumna_<?php echo $NrKolumna; ?>">Produkty zawierające w nazwie frazę:</label>             
                                              <input type="text" name="warunki_produkty_nazwa_kolumna[<?php echo $NrKolumna; ?>]" id="warunki_produkty_nazwa_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['warunki_produkty_nazwa_kolumna'][$NrKolumna])) ? $konfig['warunki_produkty_nazwa_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Należy wpisać frazę po jakiej mają być wyszukiwane produkty - szukanie odbywa się po nazwach produktów - minimum 2 znaki</b></em>
                                            </p>   

                                            <p>
                                              <label for="warunki_produkty_nr_kat_kolumna_<?php echo $NrKolumna; ?>">Produkty zawierające w numerze katalogowym frazę:</label>             
                                              <input type="text" name="warunki_produkty_nr_kat_kolumna[<?php echo $NrKolumna; ?>]" id="warunki_produkty_nr_kat_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['warunki_produkty_nr_kat_kolumna'][$NrKolumna])) ? $konfig['warunki_produkty_nr_kat_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Należy wpisać frazę po jakiej mają być wyszukiwane produkty - szukanie odbywa się po numerach katalogowych - minimum 2 znaki</b></em>
                                            </p> 

                                            <p>
                                              <label for="warunki_produkty_tagi_kolumna_<?php echo $NrKolumna; ?>">Produkty zawierające dodatkowy tag:</label>             
                                              <input type="text" name="warunki_produkty_tagi_kolumna[<?php echo $NrKolumna; ?>]" id="warunki_produkty_tagi_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['warunki_produkty_tagi_kolumna'][$NrKolumna])) ? $konfig['warunki_produkty_tagi_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Należy nazwę tagu jaki muszą mieć wyszukiwane produkty - szukanie odbywa się po dodatkowych tagach - minimum 2 znaki</b></em>
                                            </p>                                              

                                        </div>
                                        
                                        <p>
                                          <label for="ilosc_produktow_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych produktów:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_produktow_kolumna'][$NrKolumna])) ? $konfig['ilosc_produktow_kolumna'][$NrKolumna] : ''), ' id="ilosc_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        

                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane produkty (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlany jest tylko <u><b>jeden</b></u> produkt bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_produktow_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 7; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_produktow_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_produktow_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_produktow_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_produktow_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania produktów</div>

                                        <p>
                                          <label for="zakup_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać możliwość zakupu produktu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('zakup_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['zakup_produktow_kolumna'][$NrKolumna])) ? $konfig['zakup_produktow_kolumna'][$NrKolumna] : ''), ' id="zakup_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                        
                                        <p>
                                          <label for="schowek_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać przycisk dodania do schowka ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('schowek_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['schowek_produktow_kolumna'][$NrKolumna])) ? $konfig['schowek_produktow_kolumna'][$NrKolumna] : ''), ' id="schowek_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                        

                                        <p>
                                          <label for="dostepnosc_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać dostępność produktu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('dostepnosc_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['dostepnosc_produktow_kolumna'][$NrKolumna])) ? $konfig['dostepnosc_produktow_kolumna'][$NrKolumna] : ''), ' id="dostepnosc_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                        
                                        <p>
                                          <label for="data_dostepnosci_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać datę od kiedy produkt będzie dostępny ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('data_dostepnosci_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['data_dostepnosci_produktow_kolumna'][$NrKolumna])) ? $konfig['data_dostepnosci_produktow_kolumna'][$NrKolumna] : ''), ' id="data_dostepnosci_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                        

                                        <p>
                                          <label for="producent_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać nazwę producenta ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('producent_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['producent_produktow_kolumna'][$NrKolumna])) ? $konfig['producent_produktow_kolumna'][$NrKolumna] : ''), ' id="producent_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>

                                        <p>
                                          <label for="nr_kat_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać numer katalogowy ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('nr_kat_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['nr_kat_produktow_kolumna'][$NrKolumna])) ? $konfig['nr_kat_produktow_kolumna'][$NrKolumna] : ''), ' id="nr_kat_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                        
                                        <p>
                                          <label for="opis_krotki_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać opis krótki ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('opis_krotki_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['opis_krotki_produktow_kolumna'][$NrKolumna])) ? $konfig['opis_krotki_produktow_kolumna'][$NrKolumna] : ''), ' id="opis_krotki_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                        

                                        <p>
                                          <label for="sortowanie_produktow_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania produktów:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'losowo', 'text' => 'losowo'),
                                                            array('id' => 'sort', 'text' => 'sortowanie'),
                                                            array('id' => 'data', 'text' => 'data dodania') );
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_produktow_kolumna'][$NrKolumna])) ? $konfig['sortowanie_produktow_kolumna'][$NrKolumna] : ''), ' id="sortowanie_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                     
                                        
                                        <p>
                                          <label for="tylko_dostepne_produktow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać tylko produkty ze stanem magazynowym większym od 0 ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('tylko_dostepne_produktow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['tylko_dostepne_produktow_kolumna'][$NrKolumna])) ? $konfig['tylko_dostepne_produktow_kolumna'][$NrKolumna] : 'nie'), ' id="tylko_dostepne_produktow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                        
                                    </div>  
                                    
                                    <!--- ################ ZAKRES PRODUKT DNIA ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresProduktDnia" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'produkt_dnia') ? '' : 'style="display:none"'); ?>>
                                    
                                        <div class="TytulDzialu">Ustawienia wyświetlania produktu dnia</div>

                                        <p>
                                          <label for="zakup_produkt_dnia_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać możliwość zakupu produktu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('zakup_produkt_dnia_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['zakup_produkt_dnia_kolumna'][$NrKolumna])) ? $konfig['zakup_produkt_dnia_kolumna'][$NrKolumna] : ''), ' id="zakup_produkt_dnia_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                        
                                        <p>
                                          <label for="oszczedzasz_produkt_dnia_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać "oszczędzasz ...%" ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('oszczedzasz_produkt_dnia_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['oszczedzasz_produkt_dnia_kolumna'][$NrKolumna])) ? $konfig['oszczedzasz_produkt_dnia_kolumna'][$NrKolumna] : ''), ' id="oszczedzasz_produkt_dnia_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                        

                                        <p>
                                          <label for="opis_krotki_produkt_dnia_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać opis krótki ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('opis_krotki_produkt_dnia_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['opis_krotki_produkt_dnia_kolumna'][$NrKolumna])) ? $konfig['opis_krotki_produkt_dnia_kolumna'][$NrKolumna] : ''), ' id="opis_krotki_produkt_dnia_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>

                                        <p>
                                          <label for="nastepny_produkt_dnia_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać datę dostępności następnego produktu dnia ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('nastepny_produkt_dnia_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['nastepny_produkt_dnia_kolumna'][$NrKolumna])) ? $konfig['nastepny_produkt_dnia_kolumna'][$NrKolumna] : ''), ' id="nastepny_produkt_dnia_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                             

                                    </div>                                      

                                    <!--- ################ ZAKRES RECENZJE ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresRecenzje" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'recenzje') ? '' : 'style="display:none"'); ?>>

                                        <p>
                                          <label for="ilosc_recenzji_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych recenzji:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_recenzji_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_recenzji_kolumna'][$NrKolumna])) ? $konfig['ilosc_recenzji_kolumna'][$NrKolumna] : ''), ' id="ilosc_recenzji_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        

                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane recenzje (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlana jest tylko <u><b>jedna</b></u> recenzja bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_recenzji_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 7; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_recenzji_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_recenzji_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_recenzji_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_recenzji_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania recenzji</div>

                                        <p>
                                          <label for="sortowanie_recenzji_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania recenzji:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'losowo', 'text' => 'losowo'),
                                                            array('id' => 'data', 'text' => 'data napisania') );
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_recenzji_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_recenzji_kolumna'][$NrKolumna])) ? $konfig['sortowanie_recenzji_kolumna'][$NrKolumna] : ''), ' id="sortowanie_recenzji_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                     
                                        
                                    </div>  
                                    
                                    <!--- ################ ZAKRES AKTUALNOSCI ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresAktualnosci" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'aktualnosci') ? '' : 'style="display:none"'); ?>>
                                    
                                        <p>
                                          <label for="kategoria_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Wyświetlaj artykuły z kategorii / wg warunków:</label>             
                                          <?php
                                          $sqls = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" order by n.sort_order, nd.categories_name ');  
                                          //
                                          $tablica = array();
                                          
                                          $tablica[] = array('id' => 0,
                                                             'text' => 'Ze wszystkich kategorii (ostatnio dodane wg daty)');                                              
                                          $tablica[] = array('id' => 999999,
                                                             'text' => 'Wg wybranych warunków');                                              
                                                               
                                          while ($kategorie = $sqls->fetch_assoc()) {
                                              //
                                              $nazwa = $kategorie['categories_name'];
                                              //
                                              // sprawdzi czy nie jest podkategoria
                                              if ( $kategorie['parent_id'] > 0 ) {
                                                   //
                                                   $sqlsp = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "'.(int)$_SESSION['domyslny_jezyk']['id'].'" and n.categories_id = ' . (int)$kategorie['parent_id']);  
                                                   if ((int)$db->ile_rekordow($sqlsp) > 0) {
                                                      //
                                                      $kategorie_parent = $sqlsp->fetch_assoc();
                                                      $nazwa = $kategorie_parent['categories_name'] . ' / ' . $nazwa;
                                                      //
                                                   }
                                                   //
                                                   $db->close_query($sqlsp);
                                                   //
                                              }
                                              //
                                              $tablica[] = array('id' => $kategorie['categories_id'],
                                                                 'text' => $nazwa);                                
                                              //
                                              unset($nazwa);
                                              //
                                          }
                                          $db->close_query($sqls);
                                          //
                                                           
                                          echo Funkcje::RozwijaneMenu('kategoria_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['kategoria_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['kategoria_aktualnosci_kolumna'][$NrKolumna] : ''), ' onchange="zakres_aktualnosci_kolumna(this.value,\'' . ucfirst($NrKolumna) . '\')" id="kategoria_aktualnosci_kolumna_' . $NrKolumna . '" style="width:330px"');
                                          unset($tablica);
                                          ?>
                                        </p>    
                                        
                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresAktualnosciWarunki" <?php echo ((isset($konfig['kategoria_aktualnosci_kolumna'][$NrKolumna]) && $konfig['kategoria_aktualnosci_kolumna'][$NrKolumna] == 999999) ? '' : 'style="display:none"'); ?>>

                                            <p>
                                              <label for="warunki_aktualnosci_tytul_kolumna_<?php echo $NrKolumna; ?>">Artykuły zawierające w tytule frazę:</label>             
                                              <input type="text" name="warunki_aktualnosci_tytul_kolumna[<?php echo $NrKolumna; ?>]" id="warunki_aktualnosci_tytul_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['warunki_aktualnosci_tytul_kolumna'][$NrKolumna])) ? $konfig['warunki_aktualnosci_tytul_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Należy wpisać frazę po jakiej mają być wyszukiwane artykuły - szukanie odbywa się po tytułach - minimum 2 znaki</b></em>
                                            </p>   

                                            <p>
                                              <label for="warunki_aktualnosci_autor_kolumna_<?php echo $NrKolumna; ?>">Artykuły autora:</label>             
                                              <input type="text" name="warunki_aktualnosci_autor_kolumna[<?php echo $NrKolumna; ?>]" id="warunki_aktualnosci_autor_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['warunki_aktualnosci_autor_kolumna'][$NrKolumna])) ? $konfig['warunki_aktualnosci_autor_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Należy wpisać nazwę autora artykułu po jakim mają być wyszukiwane aktualnosci - szukanie odbywa się po polu autora artykułu - minimum 2 znaki</b></em>
                                            </p> 

                                        </div>                                        

                                        <p>
                                          <label for="ilosc_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych artykułów:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['ilosc_aktualnosci_kolumna'][$NrKolumna] : ''), ' id="ilosc_aktualnosci_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        

                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane artykuły (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlany jest tylko <u><b>jeden</b></u> artykuł bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_aktualnosci_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 7; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_aktualnosci_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_aktualnosci_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_aktualnosci_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_aktualnosci_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania artykułów</div>
                                        
                                        <p>
                                          <label for="foto_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać zdjęcie artykułu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('foto_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['foto_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['foto_aktualnosci_kolumna'][$NrKolumna] : ''), ' id="foto_aktualnosci_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                            
                                        
                                        <p>
                                          <label for="odslony_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać ilość odsłon artykułu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('odslony_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['odslony_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['odslony_aktualnosci_kolumna'][$NrKolumna] : ''), ' id="odslony_aktualnosci_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>   

                                        <p>
                                          <label for="data_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać datę dodania artykułu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('data_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['data_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['data_aktualnosci_kolumna'][$NrKolumna] : ''), ' id="data_aktualnosci_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p> 

                                        <p>
                                          <label for="autor_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać nazwę autora artykułu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('autor_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['autor_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['autor_aktualnosci_kolumna'][$NrKolumna] : ''), ' id="autor_aktualnosci_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                           
                                        
                                        <p>
                                          <label for="sortowanie_aktualnosci_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania artykułów:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'data', 'text' => 'data dodania'),
                                                            array('id' => 'losowo', 'text' => 'losowo') );
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_aktualnosci_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_aktualnosci_kolumna'][$NrKolumna])) ? $konfig['sortowanie_aktualnosci_kolumna'][$NrKolumna] : ''), ' id="sortowanie_aktualnosci_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                     

                                    </div>  
                        
                                    <!--- ################ STRONY INFORMACYJNE ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresStronyInformacyjne" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'strony_info') ? '' : 'style="display:none"'); ?>>
                                    
                                        <p>
                                          <label for="grupa_strony_info_kolumna_<?php echo $NrKolumna; ?>">Wyświetlaj strony informacyjne z grupy stron:</label>             
                                          <?php
                                          $sqls = $db->open_query('select distinct * from pages_group order by pages_group_code');  
                                          //
                                          $tablica = array();                                         
                                                               
                                          while ($grupy = $sqls->fetch_assoc()) {
                                              //
                                              $tablica[] = array('id' => $grupy['pages_group_code'],
                                                                 'text' => $grupy['pages_group_code'] . ' - ' . $grupy['pages_group_title']);                                
                                              //
                                          }
                                          $db->close_query($sqls);
                                          //
                                                           
                                          echo Funkcje::RozwijaneMenu('grupa_strony_info_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['grupa_strony_info_kolumna'][$NrKolumna])) ? $konfig['grupa_strony_info_kolumna'][$NrKolumna] : ''), ' onchange="zakres_strony_info_kolumna(this.value,\'' . ucfirst($NrKolumna) . '\')" id="grupa_strony_info_kolumna_' . $NrKolumna . '" style="width:330px"');
                                          unset($tablica);
                                          ?>
                                        </p>    

                                        <p>
                                          <label for="ilosc_strony_info_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych pozycji:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_strony_info_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_strony_info_kolumna'][$NrKolumna])) ? $konfig['ilosc_strony_info_kolumna'][$NrKolumna] : ''), ' id="ilosc_strony_info_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        

                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane pozycje (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlana jest tylko <u><b>jedna</b></u> pozycja bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_strony_info_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 7; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_strony_info_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_strony_info_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_strony_info_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_strony_info_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania stron informacyjnych</div>
                                        
                                        <p>
                                          <label for="foto_strony_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać zdjęcie przypisane do strony ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('foto_strony_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['foto_strony_kolumna'][$NrKolumna])) ? $konfig['foto_strony_kolumna'][$NrKolumna] : ''), ' id="foto_strony_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                              

                                    </div>  

                                    <!--- ################ ZAKRES PRODUCENCI ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresProducenci" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'producenci') ? '' : 'style="display:none"'); ?>>

                                        <p>
                                          <label for="ilosc_producentow_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych producentow:</label>             
                                          <?php
                                          $tablica = array();
                                          $tablica[] = array('id' => 9999, 'text' => '-- wszystkie pozycje ---');
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_producentow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_producentow_kolumna'][$NrKolumna])) ? $konfig['ilosc_producentow_kolumna'][$NrKolumna] : ''), ' id="ilosc_producentow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        
                                        
                                        <p>
                                          <label for="grupa_kategorie_zakres_kolumna_<?php echo $NrKolumna; ?>">Wyświetlaj producentów:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'wszyscy', 'text' => '-- wszystkich producentów --'),
                                                            array('id' => 'wybrane', 'text' => 'tylko wybranych') );

                                          echo Funkcje::RozwijaneMenu('grupa_producenci_zakres_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['grupa_producenci_zakres_kolumna'][$NrKolumna])) ? $konfig['grupa_producenci_zakres_kolumna'][$NrKolumna] : ''), ' onchange="zakres_producenci_kolumna(this.value,\'' . ucfirst($NrKolumna) . '\')" id="grupa_producenci_zakres_kolumna_' . $NrKolumna . '" style="width:330px"');
                                          unset($tablica);
                                          ?>
                                        </p>         

                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>WyborProducenci" <?php echo ((isset($konfig['grupa_producenci_zakres_kolumna'][$NrKolumna]) && $konfig['grupa_producenci_zakres_kolumna'][$NrKolumna] == 'wybrane') ? '' : 'style="display:none"'); ?>>
                                            
                                            <p> 
                                                <label>Wybierz producentów:</label>
                                            </p>                                     
                                            
                                            <div class="WybieranieZakresu">
                                            
                                                <div class="OknoKategoriaProducenci">
                                                
                                                    <?php
                                                    $Prd = Funkcje::TablicaProducenci();
                                                    //
                                                    echo '<table class="pkc">';
                                                    //
                                                    if (count($Prd) > 0) {
                                                        //
                                                        for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                                          
                                                            echo '<tr>                                
                                                                    <td class="lfp">
                                                                        <input type="checkbox" value="' . $Prd[$b]['id'] . '" name="id_producent_zakres_kolumna[' . $NrKolumna . '][]" id="id_producent_zakres_kolumna_' .$NrKolumna . '_' . $Prd[$b]['id'] . '" ' . ((isset($konfig['id_producent_zakres_kolumna'][$NrKolumna]) && in_array($Prd[$b]['id'], explode(',',(string)$konfig['id_producent_zakres_kolumna'][$NrKolumna]))) ? 'checked="checked"' : '') . ' /> <label class="OpisFor" for="id_producent_zakres_kolumna_' .$NrKolumna . '_' . $Prd[$b]['id'] . '">' . $Prd[$b]['text'] . '</label>
                                                                    </td>                                
                                                                  </tr>';
                                                                  
                                                        }                                                        
                                                        //
                                                    } else {
                                                        //
                                                         echo '<tr>                                
                                                                 <td class="lfp">
                                                                     <input type="checkbox" value="999999999" name="id_producent_zakres_kolumna[' . $NrKolumna . '][999999999]" id="id_producent_zakres_kolumna_' .$NrKolumna . '_0" checked="checked" /> <label class="OpisFor" for="id_producent_zakres_kolumna_' .$NrKolumna . '_0">-- brak producentów --</label>
                                                                 </td>                                
                                                               </tr>';
                                                         //
                                                    }
                                                    //
                                                    echo '</table>';
                                                    //
                                                    unset($Prd);
                                                    ?>

                                                </div>
                                            
                                            </div>
                                            
                                        </div> 
                                        

                                        <div class="TytulDzialu">Ustawienia wyświetlania producentów</div>
                                        
                                        <p>
                                          <label for="logo_producentow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać logo producenta ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('logo_producentow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['logo_producentow_kolumna'][$NrKolumna])) ? $konfig['logo_producentow_kolumna'][$NrKolumna] : ''), ' id="logo_producentow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>    

                                        <p>
                                          <label for="logo_rozmiar_producentow_kolumna_<?php echo $NrKolumna; ?>">Rozmiar logotypów producentów w px:</label>             
                                          <?php
                                          $tablica = array( array('id' => '50', 'text' => '50px'),
                                                            array('id' => '60', 'text' => '60px'),
                                                            array('id' => '70', 'text' => '70px'),
                                                            array('id' => '80', 'text' => '80px'),
                                                            array('id' => '100', 'text' => '100px'),
                                                            array('id' => '120', 'text' => '120px'),
                                                            array('id' => '140', 'text' => '140px'),
                                                            array('id' => '160', 'text' => '160px'),
                                                            array('id' => '180', 'text' => '180px'),
                                                            array('id' => '200', 'text' => '200px'),
                                                            array('id' => 'brak', 'text' => '-- oryginalny rozmiar --') );
                                                            
                                          echo Funkcje::RozwijaneMenu('logo_rozmiar_producentow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['logo_rozmiar_producentow_kolumna'][$NrKolumna])) ? $konfig['logo_rozmiar_producentow_kolumna'][$NrKolumna] : ''), ' id="logo_rozmiar_producentow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                           

                                        <p>
                                          <label for="nazwa_producentow_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać nazwę producenta (w formie tekstu) ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('nazwa_producentow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['nazwa_producentow_kolumna'][$NrKolumna])) ? $konfig['nazwa_producentow_kolumna'][$NrKolumna] : ''), ' id="nazwa_producentow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>     

                                        <p>
                                          <label for="sortowanie_producentow_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania producentów:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'nazwa', 'text' => 'wg nazwy'),
                                                            array('id' => 'losowo', 'text' => 'losowo') );
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_producentow_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_producentow_kolumna'][$NrKolumna])) ? $konfig['sortowanie_producentow_kolumna'][$NrKolumna] : ''), ' id="sortowanie_producentow_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                     
                                        
                                    </div>   

                                    <!--- ################ ZAKRES KATEGORIE ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresKategorie" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'kategorie') ? '' : 'style="display:none"'); ?>>
                                    
                                        <p>
                                          <label for="grupa_kategorie_zakres_kolumna_<?php echo $NrKolumna; ?>">Wyświetlaj kategorie:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'wszystkie', 'text' => '-- wszystkie kategorie główne --'),
                                                            array('id' => 'wybrane', 'text' => 'tylko wybrane') );

                                          echo Funkcje::RozwijaneMenu('grupa_kategorie_zakres_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['grupa_kategorie_zakres_kolumna'][$NrKolumna])) ? $konfig['grupa_kategorie_zakres_kolumna'][$NrKolumna] : ''), ' onchange="zakres_kategoria_kolumna(this.value,\'' . ucfirst($NrKolumna) . '\')" id="grupa_kategorie_zakres_kolumna_' . $NrKolumna . '" style="width:330px"');
                                          unset($tablica);
                                          ?>
                                        </p>         

                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>WyborKategorie" <?php echo ((isset($konfig['grupa_kategorie_zakres_kolumna'][$NrKolumna]) && $konfig['grupa_kategorie_zakres_kolumna'][$NrKolumna] == 'wybrane') ? '' : 'style="display:none"'); ?>>
                                            
                                            <p> 
                                                <label>Wybierz kategorię:</label>
                                            </p>                                     
                                            
                                            <div class="WybieranieZakresu">
                                            
                                                <div class="OknoKategoriaProducenci">
                                                
                                                    <table class="pkc">
                                        
                                                    <?php
                                                    $b = 0;
                                                    foreach ( Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica ) {
                                                        //
                                                        echo '<tr><td class="lfp"><input type="checkbox" value="' . $Tablica['id'] . '" name="id_kategoria_zakres_kolumna[' . $NrKolumna . '][]" id="id_kategoria_zakres_kolumna_' .$NrKolumna . '_' . $Tablica['id'] . '" ' . ((isset($konfig['id_kategoria_zakres_kolumna'][$NrKolumna]) && in_array($Tablica['id'], explode(',',(string)$konfig['id_kategoria_zakres_kolumna'][$NrKolumna]))) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="id_kategoria_zakres_kolumna_' .$NrKolumna . '_' . $Tablica['id'] . '">' . $Tablica['text'] . '</label>';
                                                                   
                                                              if ( isset($Tablica['podkategorie']) && is_array($Tablica['podkategorie']) ) {
                                                              
                                                                  echo '<table>';
                                                                  
                                                                  foreach ( $Tablica['podkategorie'] as $PodkatId => $Podkat ) {
                                                                      //
                                                                      echo '<tr><td class="lfp"><input type="checkbox" value="' . $Podkat['id'] . '" name="id_kategoria_zakres_kolumna[' . $NrKolumna . '][]" id="id_kategoria_zakres_kolumna_' .$NrKolumna . '_' . $Podkat['id'] . '" ' . ((isset($konfig['id_kategoria_zakres_kolumna'][$NrKolumna]) && in_array($Podkat['id'], explode(',',(string)$konfig['id_kategoria_zakres_kolumna'][$NrKolumna]))) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="id_kategoria_zakres_kolumna_' .$NrKolumna . '_' . $Podkat['id'] . '">' . $Podkat['text'] . '</label></td></tr>';
                                                                      //
                                                                  }
                                                                  
                                                                  echo '</table>';
                                                                  
                                                              }                                                       
                                                                   
                                                        echo '</td></tr>';
                                                        //
                                                        $b++;
                                                        //
                                                    }
                                                    //
                                                    if ( $b == 0 ) {
                                                         //
                                                         echo '<tr>                                
                                                                 <td class="lfp">
                                                                     <input type="checkbox" value="999999999" name="id_kategoria_zakres_kolumna[' . $NrKolumna . '][999999999]" id="id_kategoria_zakres_kolumna_' .$NrKolumna . '_0" checked="checked" /> <label class="OpisFor" for="id_kategoria_zakres_kolumna_' .$NrKolumna . '_0">-- brak kategorii --</label>
                                                                 </td>                                
                                                               </tr>';
                                                         //
                                                    } 
                                                    ?> 
                                                    
                                                    </table>
                                                    
                                                </div>
                                            
                                            </div>
                                            
                                        </div> 

                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane kategorie (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlany jest tylko <u><b>jedna</b></u> kategoria bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_kategorii_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 9; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_kategorii_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_kategorii_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_kategorii_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_kategorii_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>                                    

                                        <div class="TytulDzialu">Ustawienia wyświetlania kategorii</div>
                                        
                                        <p>
                                          <label for="grafika_kategorii_kolumna_<?php echo $NrKolumna; ?>">Wyświetlanie ikony / grafiki kategorii:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'brak', 'text' => 'nie wyświetlaj żadnej grafiki'),
                                                            array('id' => 'ikonka', 'text' => 'wyświetlaj ikonki kategorii'),
                                                            array('id' => 'zdjecie', 'text' => 'wyświetlaj zdjęcie kategorii')
                                                            );                                                            
                                                            
                                          echo Funkcje::RozwijaneMenu('grafika_kategorii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['grafika_kategorii_kolumna'][$NrKolumna])) ? $konfig['grafika_kategorii_kolumna'][$NrKolumna] : ''), ' id="grafika_kategorii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                          <em class="TipIkona"><b>Wyświetlane są grafiki przypisane do kategorii w menu Asortyment / Kategorie.</b></em>
                                        </p>    

                                        <p>
                                          <label for="grafika_rozmiar_kategorii_kolumna_<?php echo $NrKolumna; ?>">Rozmiar grafik kategorii w px:</label>             
                                          <?php
                                          $tablica = array( array('id' => '50', 'text' => '50px'),
                                                            array('id' => '60', 'text' => '60px'),
                                                            array('id' => '70', 'text' => '70px'),
                                                            array('id' => '80', 'text' => '80px'),
                                                            array('id' => '100', 'text' => '100px'),
                                                            array('id' => '120', 'text' => '120px'),
                                                            array('id' => '140', 'text' => '140px'),
                                                            array('id' => '160', 'text' => '160px'),
                                                            array('id' => '180', 'text' => '180px'),
                                                            array('id' => '200', 'text' => '200px'),
                                                            array('id' => 'brak', 'text' => '-- oryginalny rozmiar --') );
                                                            
                                          echo Funkcje::RozwijaneMenu('grafika_rozmiar_kategorii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['grafika_rozmiar_kategorii_kolumna'][$NrKolumna])) ? $konfig['grafika_rozmiar_kategorii_kolumna'][$NrKolumna] : ''), ' id="grafika_rozmiar_kategorii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                           

                                        <p>
                                          <label for="nazwa_kategorii_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać nazwę kategorii (w formie tekstu) ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('nazwa_kategorii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['nazwa_kategorii_kolumna'][$NrKolumna])) ? $konfig['nazwa_kategorii_kolumna'][$NrKolumna] : ''), ' id="nazwa_kategorii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>     

                                        <p>
                                          <label for="podkategorie_kategorii_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać podkategorie:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                            array('id' => 'tak', 'text' => 'tak') );
                                                            
                                          echo Funkcje::RozwijaneMenu('podkategorie_kategorii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['podkategorie_kategorii_kolumna'][$NrKolumna])) ? $konfig['podkategorie_kategorii_kolumna'][$NrKolumna] : ''), ' id="podkategorie_kategorii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>  

                                        <p>
                                          <label for="podkategorie_ilosc_kategorii_kolumna_<?php echo $NrKolumna; ?>">Ile podkategorii ma być wyświetlanych:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 3; $t < 41; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }         
                                          echo Funkcje::RozwijaneMenu('podkategorie_ilosc_kategorii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['podkategorie_ilosc_kategorii_kolumna'][$NrKolumna])) ? $konfig['podkategorie_ilosc_kategorii_kolumna'][$NrKolumna] : ''), ' id="podkategorie_ilosc_kategorii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p> 

                                        <p>
                                          <label for="wyrownanie_kategorii_kolumna_<?php echo $NrKolumna; ?>">Wyrównanie nazwy kategorii i podkategorii:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'srodek', 'text' => 'wyśrodkowane'),
                                                            array('id' => 'lewa', 'text' => 'do lewej strony'),
                                                            array('id' => 'prawa', 'text' => 'do prawej strony') );
                                                            
                                          echo Funkcje::RozwijaneMenu('wyrownanie_kategorii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['wyrownanie_kategorii_kolumna'][$NrKolumna])) ? $konfig['wyrownanie_kategorii_kolumna'][$NrKolumna] : ''), ' id="wyrownanie_kategorii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                          
                                        
                                    </div> 

                                    <!--- ################ ZAKRES YOUTUBE ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresYoutube" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'youtube') ? '' : 'style="display:none"'); ?>>
                                    
                                        <div class="TytulDzialu" style="margin-top:0px">Ilość kolumn w ilu mają być wyświetlane filmy (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlany jest tylko <u><b>jeden</b></u> film bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_youtube_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 9; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_youtube_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_youtube_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_youtube_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_youtube_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>       

                                        <div class="TytulDzialu">Dane wyświetlanych filmów YouTube</div>
                                        
                                        <?php for ( $e = 1; $e < 11; $e++ ) { ?>
                                        
                                        <p>
                                          <label for="film_youtube_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>">URL filmu nr <b><?php echo $e; ?></b>:</label>             
                                          <input type="text" name="film_youtube_kolumna[<?php echo $NrKolumna; ?>][<?php echo $e; ?>]" id="film_youtube_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['film_youtube_kolumna'][$NrKolumna][$e])) ? $konfig['film_youtube_kolumna'][$NrKolumna][$e] : ''); ?>" size="40" /><em class="TipIkona"><b>Należy wkleić tylko nr ID filmu, np. z linku http://www.youtube.com/watch?v=BvtXXXAF8 będzie to BvtXXXAF8</b></em>
                                        </p>                                        
                                        
                                        <?php } ?>

                                        <div class="TytulDzialu">Ustawienia wyświetlania YouTube</div>
                                        
                                        <p>
                                          <label for="screen_youtube_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać bezpośrednio film czy najpierw miniaturkę graficzną filmu ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'film', 'text' => 'od razu film'),
                                                            array('id' => 'miniaturka', 'text' => 'najpierw miniaturka graficzna filmu') );
                                                            
                                          echo Funkcje::RozwijaneMenu('screen_youtube_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['screen_youtube_kolumna'][$NrKolumna])) ? $konfig['screen_youtube_kolumna'][$NrKolumna] : ''), ' id="screen_youtube_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                          

                                    </div> 

                                    <!--- ################ ZAKRES FILMY MP4 ############ -->

                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresFilmMp4 KolumnyWszystkieFilmMp4" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'filmmp4') ? '' : 'style="display:none"'); ?>>

                                        <div class="TytulDzialu" style="margin-top:0px">Ilość kolumn w ilu mają być wyświetlane filmy (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlany jest tylko <u><b>jeden</b></u> film bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_filmmp4_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 9; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_filmmp4_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_filmmp4_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_filmmp4_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_filmmp4_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>       

                                        <div class="TytulDzialu">Dane wyświetlanych filmów Mp4</div>
                                        
                                        <div style="margin:0 15px 15px 15px">
                                        
                                            <div class="info_tab ZakladkiFilmMp4<?php echo ucfirst($NrKolumna); ?>">
                                            <?php
                                            for ($w = 100, $c = 100 + count($ile_jezykow); $w < $c; $w++) {
                                                echo '<span id="filmmp4_link_' . $w . '_' . $NrKolumna . '" class="a_href_info_tab" data-nr="' . $w . '" onclick="zmien_film(\'' . $w . '_' . $NrKolumna . '\', \'' . ucfirst($NrKolumna) . '\')">' . $ile_jezykow[$w - 100]['text'] . '</span>';
                                            }                    
                                            ?>                   
                                            </div>
                                            
                                            <div style="clear:both"></div>                
                                        
                                            <div class="info_tab_content">
                                                
                                                <?php for ($w = 100, $c = 100 + count($ile_jezykow); $w < $c; $w++) { ?>
                                                    
                                                    <div id="info_tab_filmmp4_id_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" class="PoleFilmMp4" style="display:none;">

                                                        <?php for ( $e = 1; $e < 11; $e++ ) { ?>
                                                        
                                                        <p>
                                                          <label for="film_filmmp4_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>">Film Mp4 nr <b><?php echo $e; ?></b>:</label>
                                                          <input type="text" ondblclick="openFileBrowser('film_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>','','<?php echo KATALOG_ZDJEC; ?>')" name="film_filmmp4_kolumna_<?php echo $ile_jezykow[$w - 100]['id']; ?>[<?php echo $NrKolumna; ?>][<?php echo $e; ?>]" id="film_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['film_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e])) ? $konfig['film_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e] : ''); ?>" size="60" />
                                                          <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                                          <span class="usun_plik TipChmurka" data-plik="film_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>"><b>Usuń przypisany plik</b></span>
                                                          <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('film_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                                        </p>

                                                        <p>
                                                          <label for="film_szerokosc_filmmp4_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>">Szerokość klipu w pikselach:</label>
                                                          <input type="text" name="film_szerokosc_filmmp4_kolumna_<?php echo $ile_jezykow[$w - 100]['id']; ?>[<?php echo $NrKolumna; ?>][<?php echo $e; ?>]" id="film_szerokosc_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['film_szerokosc_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e])) ? $konfig['film_szerokosc_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e] : ''); ?>" size="5" class="calkowita" />
                                                        </p>
                                                        
                                                        <p>
                                                          <label for="film_wysokosc_filmmp4_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>">Wysokość klipu w pikselach:</label>
                                                          <input type="text" name="film_wysokosc_filmmp4_kolumna_<?php echo $ile_jezykow[$w - 100]['id']; ?>[<?php echo $NrKolumna; ?>][<?php echo $e; ?>]" id="film_wysokosc_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['film_wysokosc_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e])) ? $konfig['film_wysokosc_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e] : ''); ?>" size="5" class="calkowita" />
                                                          
                                                          <span data-nr="filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" class="PrzeliczProporcje PrzeliczProporcjeVideo">oblicz wysokość w stosunku do szerokości (propocja 16:9)</span>
                                                        </p>
                                                        
                                                        <p>
                                                          <label for="film_nazwa_filmmp4_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>">Nazwa klipu:</label>
                                                          <input type="text" name="film_nazwa_filmmp4_kolumna_<?php echo $ile_jezykow[$w - 100]['id']; ?>[<?php echo $NrKolumna; ?>][<?php echo $e; ?>]" id="film_nazwa_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['film_nazwa_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e])) ? $konfig['film_nazwa_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e] : ''); ?>" size="50" />
                                                        </p>                                                        
                                                 
                                                        <p>
                                                          <label for="film_link_filmmp4_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>">Link:</label>
                                                          <input type="text" name="film_link_filmmp4_kolumna_<?php echo $ile_jezykow[$w - 100]['id']; ?>[<?php echo $NrKolumna; ?>][<?php echo $e; ?>]" id="film_link_filmmp4_<?php echo $ile_jezykow[$w - 100]['id']; ?>_<?php echo $e; ?>_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['film_link_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e])) ? $konfig['film_link_filmmp4_kolumna_' . $ile_jezykow[$w - 100]['id']][$NrKolumna][$e] : ''); ?>" size="50" />
                                                        </p>  
                                                        
                                                        <?php if ( $e < 10 ) { ?>
                                                        <div style="border-bottom:1px dashed #ccc;margin:10px"></div>
                                                        <?php } ?>
                                                        
                                                        <?php } ?>

                                                    </div>
                                                                  
                                                <?php } ?>  

                                            </div>
                                            
                                            <script>
                                            $(document).ready(function(){
                                                //
                                                $('.PrzeliczProporcjeVideo').click(function() {
                                                    var nr = $(this).attr('data-nr');
                                                    if ( parseInt($('#film_szerokosc_' + nr).val()) > 0 ) {
                                                         propr = parseInt($('#film_szerokosc_' + nr).val()) * 0.5625;
                                                         $('#film_wysokosc_' + nr).val(parseInt(propr));
                                                    }
                                                });
                                                //
                                            });
                                            </script>                                             
                                            
                                            <script>
                                            zmien_film('100_<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>');
                                            </script> 

                                        </div>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania filmów Mp4</div>
                                        
                                        <p>
                                          <label for="nawigacja_filmmp4_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać przyciski kontrolne (play / stop / dźwięk) ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                            array('id' => 'tak', 'text' => 'tak') );
                                                            
                                          echo Funkcje::RozwijaneMenu('nawigacja_filmmp4_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['nawigacja_filmmp4_kolumna'][$NrKolumna])) ? $konfig['nawigacja_filmmp4_kolumna'][$NrKolumna] : ''), ' id="nawigacja_filmmp4_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>  

                                        <p>
                                          <label for="autoodtwarzanie_filmmp4_kolumna_<?php echo $NrKolumna; ?>">Czy uruchomić film od razu po wyświetlaniu strony ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                            array('id' => 'tak', 'text' => 'tak') );
                                                            
                                          echo Funkcje::RozwijaneMenu('autoodtwarzanie_filmmp4_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['autoodtwarzanie_filmmp4_kolumna'][$NrKolumna])) ? $konfig['autoodtwarzanie_filmmp4_kolumna'][$NrKolumna] : ''), ' id="autoodtwarzanie_filmmp4_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                            
                                            
                                    </div>                                    

                                    <!--- ################ ZAKRES OPIS ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresOpis KolumnyWszystkieOpis" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'opis') ? '' : 'style="display:none"'); ?>>
                                    
                                        <div class="info_tab ZakladkiOpis<?php echo ucfirst($NrKolumna); ?>">
                                        <?php
                                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                            echo '<span id="opis_link_' . $w . '_' . $NrKolumna . '" class="a_href_info_tab" data-nr="' . $w . '" onclick="zmien_edytor(\'' . $w . '_' . $NrKolumna . '\', \'' . ucfirst($NrKolumna) . '\')">' . $ile_jezykow[$w]['text'] . '</span>';
                                        }                    
                                        ?>                   
                                        </div>
                                        
                                        <div style="clear:both"></div>                
                                    
                                        <div class="info_tab_content">
                                            
                                            <?php for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) { ?>
                                                
                                                <div id="info_tab_opis_id_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" class="PoleEdytora" style="display:none;">

                                                    <div class="edytor">
                                                        <textarea cols="80" rows="10" id="edytor_<?php echo $w; ?>_<?php echo $NrKolumna; ?>" name="opis_kolumna_<?php echo $ile_jezykow[$w]['id']; ?>[<?php echo $NrKolumna; ?>]"><?php echo ((isset($konfig['opis_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna])) ? $konfig['opis_kolumna_' . $ile_jezykow[$w]['id']][$NrKolumna] : ''); ?></textarea>    
                                                    </div>

                                                </div>
                                                              
                                            <?php } ?>  

                                        </div>
                                            
                                        <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>
                                            
                                    </div>
                                    
                                    <?php if ( isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'opis' ) { ?>
                                    
                                    <script>
                                    zmien_edytor('0_<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>');
                                    </script>
                                    
                                    <?php } ?>
                                    
                                    <!--- ################ ZAKRES KOD JAVA ############ -->

                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresKod" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'java') ? '' : 'style="display:none"'); ?>>
                                    
                                        <table class="OknoTextareaModulBox">
                                            <tr><td><label>Wstaw kod:</label></td>
                                            <td class="TrybEdytor">
                                                <textarea cols="120" rows="15" name="kod_kolumna[<?php echo $NrKolumna; ?>]"><?php echo ((isset($konfig['kod_kolumna'][$NrKolumna])) ? $konfig['kod_kolumna'][$NrKolumna] : ''); ?></textarea>
                                            </td></tr>
                                        </table>    
                                        
                                    </div>  
            
                                    <!--- ################ ZAKRES OPINIE O SKLEPIE ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresOpinieSklep" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'opiniesklep') ? '' : 'style="display:none"'); ?>>

                                        <p>
                                          <label for="ilosc_opinii_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych opinii o sklepie:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_opinii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_opinii_kolumna'][$NrKolumna])) ? $konfig['ilosc_opinii_kolumna'][$NrKolumna] : ''), ' id="ilosc_opinii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        

                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane opinie o sklepie (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlana jest tylko <u><b>jedna</b></u> opinia bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_opinii_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 7; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_opinii_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_opinii_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_opinii_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_opinii_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania opinii o sklepie</div>

                                        <p>
                                          <label for="sortowanie_opinii_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania opinii:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'losowo', 'text' => 'losowo'),
                                                            array('id' => 'data', 'text' => 'data napisania') );
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_opinii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_opinii_kolumna'][$NrKolumna])) ? $konfig['sortowanie_opinii_kolumna'][$NrKolumna] : ''), ' id="sortowanie_opinii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                     
                                        
                                    </div>  

                                    <!--- ################ ZAKRES GALERIE ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresGalerie" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'galerie') ? '' : 'style="display:none"'); ?>>

                                        <p>
                                          <label for="grupa_galerie">Wyświetlaj grafiki z galerii:</label>             
                                          <?php
                                          echo Funkcje::RozwijaneMenu('grupa_galerii_kolumna[' . $NrKolumna . ']', BoxyModuly::ListaGalerii(), ((isset($konfig['grupa_galerii_kolumna'][$NrKolumna])) ? $konfig['grupa_galerii_kolumna'][$NrKolumna] : ''), ' id="grupa_galerii_kolumna_' . $NrKolumna . '" style="width:400px"'); 
                                          ?>
                                        </p>   
                                        
                                        <p>
                                          <label for="ilosc_galerii_kolumna_<?php echo $NrKolumna; ?>">Ilość wyświetlanych grafik z galerii:</label>             
                                          <?php
                                          $tablica = array();
                                          $tablica[] = array('id' => 9999, 'text' => '-- wszystkie grafiki ---');
                                          for ( $t = 1; $t < 21; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t);
                                          }
                                          echo Funkcje::RozwijaneMenu('ilosc_galerii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_galerii_kolumna'][$NrKolumna])) ? $konfig['ilosc_galerii_kolumna'][$NrKolumna] : ''), ' id="ilosc_galerii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>        


                                        <div class="TytulDzialu">Ilość kolumn w ilu mają być wyświetlane grafiki galerii (dla różnych rozdzielności ekranu)</div>
                                        
                                        <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy <b>statycznej</b> wyświetlania oraz animacji w postaci <b>przewijanej</b> - w animacji w formie przenikania wyświetlany jest tylko <u><b>jedna</b></u> grafika bez dodatkowych opcji.</div>

                                        <?php
                                        $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                        //
                                        for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                     
                                          <p>
                                            <label for="ilosc_kolumn_galerii_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                            <?php
                                            $tablica = array();
                                            for ( $t = 1; $t < 7; $t++ ) {
                                                  $tablica[] = array('id' => $t, 'text' => $t);
                                            }
                                            echo Funkcje::RozwijaneMenu('ilosc_kolumn_galerii_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_galerii_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_galerii_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_galerii_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                            unset($tablica);
                                            ?>
                                          </p>
                                          
                                        <?php 
                                        } 
                                        //
                                        unset($rozdzielczosci);
                                        ?>
                                        
                                        <div class="TytulDzialu">Ustawienia wyświetlania galerii</div>

                                        <p>
                                          <label for="opis_galerii_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać opis zdjęcia galerii ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('opis_galerii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['opis_galerii_kolumna'][$NrKolumna])) ? $konfig['opis_galerii_kolumna'][$NrKolumna] : ''), ' id="opis_galerii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>    

                                        <p>
                                          <label for="rozmiar_grafik_galerii_kolumna_<?php echo $NrKolumna; ?>">Rozmiar grafik galerii w px:</label>             
                                          <?php
                                          $tablica = array( array('id' => '100', 'text' => '100px'),
                                                            array('id' => '120', 'text' => '120px'),
                                                            array('id' => '140', 'text' => '140px'),
                                                            array('id' => '160', 'text' => '160px'),
                                                            array('id' => '180', 'text' => '180px'),
                                                            array('id' => '200', 'text' => '200px'),
                                                            array('id' => '220', 'text' => '220px'),
                                                            array('id' => '250', 'text' => '250px'),
                                                            array('id' => '280', 'text' => '280px'),
                                                            array('id' => '300', 'text' => '300px'),
                                                            array('id' => '350', 'text' => '350px'),
                                                            array('id' => 'brak', 'text' => '-- oryginalny rozmiar --') );
                                                            
                                          echo Funkcje::RozwijaneMenu('rozmiar_grafik_galerii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['rozmiar_grafik_galerii_kolumna'][$NrKolumna])) ? $konfig['rozmiar_grafik_galerii_kolumna'][$NrKolumna] : ''), ' id="rozmiar_grafik_galerii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                           

                                        <p>
                                          <label for="sortowanie_galerii_kolumna_<?php echo $NrKolumna; ?>">Sposób sortowania grafik galerii:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'sort', 'text' => 'wg ustawionej kolejności'),
                                                            array('id' => 'losowo', 'text' => 'losowo') );
                                                            
                                          echo Funkcje::RozwijaneMenu('sortowanie_galerii_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['sortowanie_galerii_kolumna'][$NrKolumna])) ? $konfig['sortowanie_galerii_kolumna'][$NrKolumna] : ''), ' id="sortowanie_galerii_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                     
                                        
                                    </div>   

                                    <!--- ################ ZAKRES ANKIETY ############ -->
                                    
                                    <div class="KolumnaZakres Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresAnkiety" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'ankiety') ? '' : 'style="display:none"'); ?>>

                                        <p>
                                          <label for="grupa_ankiety">Wyświetlaj ankietę:</label>             
                                          <?php
                                          echo Funkcje::RozwijaneMenu('grupa_ankiety_kolumna[' . $NrKolumna . ']', BoxyModuly::ListaAnkiet(), ((isset($konfig['grupa_ankiety_kolumna'][$NrKolumna])) ? $konfig['grupa_ankiety_kolumna'][$NrKolumna] : ''), ' id="grupa_ankiety_kolumna_' . $NrKolumna . '" style="width:400px"'); 
                                          ?>
                                        </p>   
                                        
                                        <p>
                                          <label for="tytul_ankiety_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać tytuł ankiety ?</label>             
                                          <?php
                                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                            array('id' => 'nie', 'text' => 'nie') );
                                                            
                                          echo Funkcje::RozwijaneMenu('tytul_ankiety_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['tytul_ankiety_kolumna'][$NrKolumna])) ? $konfig['tytul_ankiety_kolumna'][$NrKolumna] : ''), ' id="tytul_ankiety_kolumna_' . $NrKolumna . '"');
                                          unset($tablica);
                                          ?>
                                        </p>                                           

                                    </div>

                                    <div class="SposobWyswietlania<?php echo ucfirst($NrKolumna); ?>" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && ($konfig['dane_kolumna'][$NrKolumna] == 'opis' || $konfig['dane_kolumna'][$NrKolumna] == 'java' || $konfig['dane_kolumna'][$NrKolumna] == 'produkt_dnia' || $konfig['dane_kolumna'][$NrKolumna] == 'ankiety')) ? 'style="display:none"' : ''); ?>>
                                    
                                        <p class="OpisKolumnyInfo">
                                          <label for="forma_wyswietlania_kolumna_<?php echo $NrKolumna; ?>">Sposób wyświetlania:</label>             
                                          <?php
                                          $tablica = array( array('id' => 'statyczny', 'text' => 'statyczny'),
                                                            array('id' => 'animowany', 'text' => 'animowany') );
                                                            
                                          echo Funkcje::RozwijaneMenu('forma_wyswietlania_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna])) ? $konfig['forma_wyswietlania_kolumna'][$NrKolumna] : ''), ' id="forma_wyswietlania_kolumna_' . $NrKolumna . '" onchange="forma_animacji_kolumna(this.value,\'' . ucfirst($NrKolumna) . '\',\'' . $NrKolumna . '\')"');
                                          unset($tablica);
                                          ?>                                          
                                        </p>     

                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaOgolneAnimacja" <?php echo ((isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna]) && $konfig['forma_wyswietlania_kolumna'][$NrKolumna] == 'animowany') ? '' : 'style="display:none"'); ?>>
                                        
                                            <div class="TytulDzialu">Ustawienia wyświetlania animacji</div>
                                        
                                            <p>
                                              <label for="animacja_kolumna_<?php echo $NrKolumna; ?>">Sposób wyświetlania animacji:</label>             
                                              <?php
                                              $tablica = array( array('id' => 'przewijanie', 'text' => 'przewijanie'),
                                                                array('id' => 'przenikanie', 'text' => 'przenikanie') );
                                                                
                                              echo Funkcje::RozwijaneMenu('animacja_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_kolumna'][$NrKolumna])) ? $konfig['animacja_kolumna'][$NrKolumna] : ''), ' id="animacja_kolumna_' . $NrKolumna . '"');
                                              unset($tablica);
                                              ?>
                                            </p> 

                                            <p>
                                              <label for="animacja_kropki_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać przyciski nawigacyjne ?</label>             
                                              <?php
                                              $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                                array('id' => 'nie', 'text' => 'nie') );
                                                                
                                              echo Funkcje::RozwijaneMenu('animacja_kropki_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_kropki_kolumna'][$NrKolumna])) ? $konfig['animacja_kropki_kolumna'][$NrKolumna] : 'nie'), ' id="animacja_kropki_kolumna_' . $NrKolumna . '" onchange="zmien_kropki(this.value, \'' . ucfirst($NrKolumna) . '\')"');
                                              unset($tablica);
                                              ?>
                                            </p>  

                                            <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaKropek" <?php echo ((isset($konfig['animacja_kropki_kolumna'][$NrKolumna]) && $konfig['animacja_kropki_kolumna'][$NrKolumna] == 'tak') ? '' : 'style="display:none"'); ?>>
                                            
                                                <p>
                                                    <label for="animacja_kropki_kolumna_urzadzenie_<?php echo $NrKolumna; ?>">Kiedy wyświetlać przyciski nawigacyjne ?</label>
                                                    <?php
                                                    $tablica = array( array('id' => 'zawsze', 'text' => 'zawsze (na każdym urządzeniu)'),
                                                                      array('id' => 'komputer', 'text' => 'tylko przy dużych rozdzielczościach (na komputerach stacjonarnych)'),
                                                                      array('id' => 'mobile', 'text' => 'tylko przy małych rozdzielczościach (na urządzeniach mobilnych)') );
                                                                      
                                                    echo Funkcje::RozwijaneMenu('animacja_kropki_kolumna_urzadzenie[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_kropki_kolumna_urzadzenie'][$NrKolumna])) ? $konfig['animacja_kropki_kolumna_urzadzenie'][$NrKolumna] : 'nie'), ' id="animacja_kropki_kolumna_urzadzenie_' . $NrKolumna . '"');
                                                    unset($tablica);
                                                    ?>
                                                </p>                                               
                                            
                                                <p>
                                                  <label for="animacja_kropki_rozmiar_kolumna_<?php echo $NrKolumna; ?>">Wielkość przycisków nawigacyjnych w px:</label>             
                                                  <?php
                                                  $tablica = array( array('id' => '6', 'text' => '6px'),
                                                                    array('id' => '7', 'text' => '7px'),
                                                                    array('id' => '8', 'text' => '8px'),
                                                                    array('id' => '9', 'text' => '9px'),
                                                                    array('id' => '10', 'text' => '10px'),
                                                                    array('id' => '11', 'text' => '11px'),
                                                                    array('id' => '12', 'text' => '12px'),
                                                                    array('id' => '15', 'text' => '15px'),
                                                                    array('id' => '18', 'text' => '18px'),
                                                                    array('id' => '20', 'text' => '20px'),
                                                                    array('id' => '22', 'text' => '22px'),
                                                                    array('id' => '24', 'text' => '24px')
                                                    );
                                                                    
                                                  echo Funkcje::RozwijaneMenu('animacja_kropki_rozmiar_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_kropki_rozmiar_kolumna'][$NrKolumna])) ? $konfig['animacja_kropki_rozmiar_kolumna'][$NrKolumna] : ''), ' id="animacja_kropki_rozmiar_kolumna_' . $NrKolumna . '"');
                                                  unset($tablica);
                                                  ?>
                                                </p>    

                                                <p>
                                                  <label for="color_kropki_kolor_kolumna_<?php echo $NrKolumna; ?>">Kolor nieaktywnego przycisku nawigacyjnego:</label>
                                                  <input name="animacja_kropki_kolor_kolumna[<?php echo $NrKolumna; ?>]" class="color {required:false}" id="color_kropki_kolor_kolumna_<?php echo $NrKolumna; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['animacja_kropki_kolor_kolumna'][$NrKolumna])) ? $konfig['animacja_kropki_kolor_kolumna'][$NrKolumna] : 'DDDDDD'); ?>" size="8" />                    
                                                </p>
                                                
                                                <p>
                                                  <label for="color_kropki_kolor_aktywny_kolumna_<?php echo $NrKolumna; ?>">Kolor aktywnego przycisku nawigacyjnego:</label>
                                                  <input name="animacja_kropki_kolor_aktywny_kolumna[<?php echo $NrKolumna; ?>]" class="color {required:false}" id="color_kropki_kolor_aktywny_kolumna_<?php echo $NrKolumna; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['animacja_kropki_kolor_aktywny_kolumna'][$NrKolumna])) ? $konfig['animacja_kropki_kolor_aktywny_kolumna'][$NrKolumna] : '000000'); ?>" size="8" />                    
                                                </p>                                                

                                                <p>
                                                  <label>Wygląd kropek w animacjach:</label>             
                                                  <?php
                                                  echo '<span class="SlickFont"><span class="icon-circle"></span>';
                                                  echo '<input type="radio" value="70" name="animacja_kropki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_kropki_czcionka_70_'.$NrKolumna.'" '.((isset($konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna] == '70') ? 'checked="checked"' : ''). '  '.(!isset($konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna]) ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_kropki_czcionka_70_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-circle-o"></span>';
                                                  echo '<input type="radio" value="63" name="animacja_kropki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_kropki_czcionka_63_'.$NrKolumna.'" '.((isset($konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna] == '63') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_kropki_czcionka_63_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-stop"></span>';
                                                  echo '<input type="radio" value="71" name="animacja_kropki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_kropki_czcionka_71_'.$NrKolumna.'" '.((isset($konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna] == '71') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_kropki_czcionka_71_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-square"></span>';
                                                  echo '<input type="radio" value="6c" name="animacja_kropki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_kropki_czcionka_6c_'.$NrKolumna.'" '.((isset($konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna] == '6c') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_kropki_czcionka_6c_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-square-o"></span>';
                                                  echo '<input type="radio" value="6d" name="animacja_kropki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_kropki_czcionka_6d_'.$NrKolumna.'" '.((isset($konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_kropki_czcionka_kolumna'][$NrKolumna] == '6d') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_kropki_czcionka_6d_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  ?>
                                                </p>            

                                            </div>                                            

                                            <p>
                                              <label for="animacja_strzalki_kolumna_<?php echo $NrKolumna; ?>">Czy wyświetlać strzałki nawigacyjne (lewo/prawo) ?</label>             
                                              <?php
                                              $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                                array('id' => 'nie', 'text' => 'nie') );
                                                                
                                              echo Funkcje::RozwijaneMenu('animacja_strzalki_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_strzalki_kolumna'][$NrKolumna])) ? $konfig['animacja_strzalki_kolumna'][$NrKolumna] : 'nie'), ' id="animacja_strzalki_kolumna_' . $NrKolumna . '" onchange="zmien_strzalki(this.value, \'' . ucfirst($NrKolumna) . '\')"');
                                              unset($tablica);
                                              ?>
                                            </p>
                                            
                                            <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaStrzalek" <?php echo ((isset($konfig['animacja_strzalki_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_kolumna'][$NrKolumna] == 'tak') ? '' : 'style="display:none"'); ?>>
                                            
                                                <p>
                                                    <label for="animacja_strzalki_kolumna_urzadzenie_<?php echo $NrKolumna; ?>">Kiedy wyświetlać strzałki nawigacyjne ?</label>
                                                    <?php
                                                    $tablica = array( array('id' => 'zawsze', 'text' => 'zawsze (na każdym urządzeniu)'),
                                                                      array('id' => 'komputer', 'text' => 'tylko przy dużych rozdzielczościach (na komputerach stacjonarnych)'),
                                                                      array('id' => 'mobile', 'text' => 'tylko przy małych rozdzielczościach (na urządzeniach mobilnych)') );
                                                                      
                                                    echo Funkcje::RozwijaneMenu('animacja_strzalki_kolumna_urzadzenie[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_strzalki_kolumna_urzadzenie'][$NrKolumna])) ? $konfig['animacja_strzalki_kolumna_urzadzenie'][$NrKolumna] : 'nie'), ' id="animacja_strzalki_kolumna_urzadzenie_' . $NrKolumna . '"');
                                                    unset($tablica);
                                                    ?>
                                                </p>                                             
                                            
                                                <p>
                                                  <label for="animacja_strzalki_rozmiar_kolumna_<?php echo $NrKolumna; ?>">Wielkość strzałek nawigacyjnych w px:</label>             
                                                  <?php
                                                  $tablica = array( array('id' => '15', 'text' => '15px'),
                                                                    array('id' => '20', 'text' => '20px'),
                                                                    array('id' => '25', 'text' => '25px'),
                                                                    array('id' => '30', 'text' => '30px'),
                                                                    array('id' => '35', 'text' => '35px'),
                                                                    array('id' => '40', 'text' => '40px'),
                                                                    array('id' => '50', 'text' => '50px'),
                                                                    array('id' => '60', 'text' => '60px'),
                                                                    array('id' => '70', 'text' => '70px') );
                                                                    
                                                  echo Funkcje::RozwijaneMenu('animacja_strzalki_rozmiar_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_strzalki_rozmiar_kolumna'][$NrKolumna])) ? $konfig['animacja_strzalki_rozmiar_kolumna'][$NrKolumna] : ''), ' id="animacja_strzalki_rozmiar_kolumna_' . $NrKolumna . '"');
                                                  unset($tablica);
                                                  ?>
                                                </p>    

                                                <p>
                                                  <label for="color_strzalki_kolumna_<?php echo $NrKolumna; ?>">Kolor strzałek nawigacyjnych:</label>
                                                  <input name="animacja_strzalki_kolor_kolumna[<?php echo $NrKolumna; ?>]" class="color {required:false}" id="color_strzalki_kolumna_<?php echo $NrKolumna; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['animacja_strzalki_kolor_kolumna'][$NrKolumna])) ? $konfig['animacja_strzalki_kolor_kolumna'][$NrKolumna] : 'FFFFFF'); ?>" size="8" />                    
                                                </p>

                                                <p>
                                                  <label>Wygląd strzałek w animacjach:</label>             
                                                  <?php
                                                  echo '<span class="SlickFont"><span class="icon-angle-left"></span>';
                                                  echo '<input type="radio" value="62" name="animacja_strzalki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_strzalki_czcionka_kolumna_62_'.$NrKolumna.'" '.((isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna] == '62') ? 'checked="checked"' : ''). '  '.(!isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_strzalki_czcionka_kolumna_62_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-chevron-left"></span>';
                                                  echo '<input type="radio" value="65" name="animacja_strzalki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_strzalki_czcionka_kolumna_65_'.$NrKolumna.'" '.((isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna] == '65') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_strzalki_czcionka_kolumna_65_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-chevron-circle-left"></span>';
                                                  echo '<input type="radio" value="6e" name="animacja_strzalki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_strzalki_czcionka_kolumna_6e_'.$NrKolumna.'" '.((isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna] == '6e') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_strzalki_czcionka_kolumna_6e_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-arrow-left-chevron-circle"></span>';
                                                  echo '<input type="radio" value="66" name="animacja_strzalki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_strzalki_czcionka_kolumna_66_'.$NrKolumna.'" '.((isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna] == '66') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_strzalki_czcionka_kolumna_66_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-chevron-square-left"></span>';
                                                  echo '<input type="radio" value="68" name="animacja_strzalki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_strzalki_czcionka_kolumna_68_'.$NrKolumna.'" '.((isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna] == '68') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_strzalki_czcionka_kolumna_68_'.$NrKolumna.'"></label>';
                                                  echo '</span>';

                                                  echo '<span class="SlickFont"><span class="icon-chevron-square-left-ostry"></span>';
                                                  echo '<input type="radio" value="6a" name="animacja_strzalki_czcionka_kolumna[' . $NrKolumna . ']" id="animacja_strzalki_czcionka_kolumna_6a_'.$NrKolumna.'" '.((isset($konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_czcionka_kolumna'][$NrKolumna] == '6a') ? 'checked="checked"' : ''). ' /><label class="OpisFor" for="animacja_strzalki_czcionka_kolumna_6a_'.$NrKolumna.'"></label>';
                                                  echo '</span>';
                                                  ?>
                                                </p>            

                                                <p>
                                                  <label for="animacja_strzalki_wyswietlanie_kolumna_<?php echo $NrKolumna; ?>">Czy strzałki mają być wyświetlane poza treścią kolumny ?</label>             
                                                  <?php
                                                  $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                                    array('id' => 'tak', 'text' => 'tak') );
                                                                    
                                                  echo Funkcje::RozwijaneMenu('animacja_strzalki_wyswietlanie_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_strzalki_wyswietlanie_kolumna'][$NrKolumna])) ? $konfig['animacja_strzalki_wyswietlanie_kolumna'][$NrKolumna] : 'nie'), ' id="animacja_strzalki_wyswietlanie_kolumna_' . $NrKolumna . '" onchange="zmien_polozenie_strzalek(this.value, \'' . ucfirst($NrKolumna) . '\')"');
                                                  unset($tablica);
                                                  ?>
                                                  <em class="TipIkona"><b>Opcja TAK wyświetli strzałki na elementach kolumny, opcja NIE - poza elementami. Ustawienie wyświetlania na TAK spowoduje dodanie dodatkowych marginesów po lewej i prawej stronie na których będą wyświetlane strzałki. To spowoduje zmniejszenie miejsca na wyświetlanie treści kolumny.</b></em>
                                                </p>            
                                                  
                                                <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresPolozeniaStrzalek" <?php echo ((isset($konfig['animacja_strzalki_wyswietlanie_kolumna'][$NrKolumna]) && $konfig['animacja_strzalki_wyswietlanie_kolumna'][$NrKolumna] == 'tak') ? '' : 'style="display:none"'); ?>>
                                                
                                                    <p>
                                                      <label for="polozenie_strzalki_wyswietlanie_kolumna_<?php echo $NrKolumna; ?>">Miejsce wyświetlania strzałek nawigacyjnych animacji:</label>             
                                                      <?php
                                                      $tablica = array( array('id' => 'boki', 'text' => 'po bokach animacji'),
                                                                        array('id' => 'tytul', 'text' => 'obok tytułu kolumny (tylko jeżeli jest włączony nagłówek kolumny)') );
                                                                        
                                                      echo Funkcje::RozwijaneMenu('polozenie_strzalki_wyswietlanie_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['polozenie_strzalki_wyswietlanie_kolumna'][$NrKolumna])) ? $konfig['polozenie_strzalki_wyswietlanie_kolumna'][$NrKolumna] : ''), ' id="polozenie_strzalki_wyswietlanie_kolumna_' . $NrKolumna . '"');
                                                      unset($tablica);
                                                      ?>
                                                    </p> 

                                                </div>
                                            
                                            </div>
                                            
                                            <p>
                                              <label for="animacja_sama_kolumna_<?php echo $NrKolumna; ?>">Czy elementy mają się same animować ?</label>             
                                              <?php
                                              $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                                array('id' => 'tak', 'text' => 'tak') );
                                                                
                                              echo Funkcje::RozwijaneMenu('animacja_sama_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_sama_kolumna'][$NrKolumna])) ? $konfig['animacja_sama_kolumna'][$NrKolumna] : 'nie'), ' id="animacja_sama_kolumna_' . $NrKolumna . '" onchange="zmien_czas_animacji(this.value, \'' . ucfirst($NrKolumna) . '\')"');
                                              unset($tablica);
                                              ?>
                                            </p>         

                                            <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaCzasuAnimacji" <?php echo ((isset($konfig['animacja_sama_kolumna'][$NrKolumna]) && $konfig['animacja_sama_kolumna'][$NrKolumna] == 'tak') ? '' : 'style="display:none"'); ?>>

                                                <p>
                                                  <label for="animacja_czas_<?php echo $NrKolumna; ?>">Czas w sekundach pomiędzy animacją kolejnych elementów:</label>             
                                                  <?php
                                                  $tablica = array();
                                                  for ( $t = 2; $t < 21; $t++ ) {
                                                        $tablica[] = array('id' => $t, 'text' => $t);
                                                  }
                                                  echo Funkcje::RozwijaneMenu('animacja_czas_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_czas_kolumna'][$NrKolumna])) ? $konfig['animacja_czas_kolumna'][$NrKolumna] : ''), ' id="animacja_czas_' . $NrKolumna . '"');
                                                  unset($tablica);
                                                  ?>
                                                </p> 
                                                
                                                <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresPasekAnimacji" <?php echo ((isset($konfig['animacja_sama_kolumna'][$NrKolumna]) && $konfig['animacja_sama_kolumna'][$NrKolumna] == 'tak' && isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'bannery') ? '' : 'style="display:none"'); ?>>
                                                
                                                    <p>
                                                      <label for="pasek_animacji_<?php echo $NrKolumna; ?>">Czy wyświetlać pasek czasu do wyświetlania kolejnej animacji ?</label>             
                                                      <?php
                                                      $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                                        array('id' => 'tak', 'text' => 'tak') );
                                                                        
                                                      echo Funkcje::RozwijaneMenu('pasek_animacji_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['pasek_animacji_kolumna'][$NrKolumna])) ? $konfig['pasek_animacji_kolumna'][$NrKolumna] : 'nie'), ' id="pasek_animacji' . $NrKolumna . '" onchange="zmien_pasek_animacji(this.value, \'' . ucfirst($NrKolumna) . '\')"');
                                                      unset($tablica);
                                                      ?>
                                                    </p>  
                                                    
                                                    <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaPaskaAnimacji" <?php echo ((isset($konfig['pasek_animacji_kolumna'][$NrKolumna]) && $konfig['pasek_animacji_kolumna'][$NrKolumna] == 'tak') ? '' : 'style="display:none"'); ?>>

                                                        <p>
                                                          <label for="kolor_pasek_animacji_tlo_<?php echo $NrKolumna; ?>">Kolor tła paska czasu:</label>
                                                          <input name="kolor_pasek_animacji_tlo_kolumna[<?php echo $NrKolumna; ?>]" class="color {required:false}" id="kolor_pasek_animacji_tlo_kolumna_<?php echo $NrKolumna; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['kolor_pasek_animacji_tlo_kolumna'][$NrKolumna])) ? $konfig['kolor_pasek_animacji_tlo_kolumna'][$NrKolumna] : 'eeeeee'); ?>" size="8" />                    
                                                        </p>                                                 
                                                        
                                                        <p>
                                                          <label for="kolor_pasek_animacji_<?php echo $NrKolumna; ?>">Kolor paska czasu:</label>
                                                          <input name="kolor_pasek_animacji_kolumna[<?php echo $NrKolumna; ?>]" class="color {required:false}" id="kolor_pasek_animacji_kolumna_<?php echo $NrKolumna; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['kolor_pasek_animacji_kolumna'][$NrKolumna])) ? $konfig['kolor_pasek_animacji_kolumna'][$NrKolumna] : 'a2a2a2'); ?>" size="8" />                    
                                                        </p>       

                                                        <p>
                                                          <label for="pasek_animacji_wysokosc_<?php echo $NrKolumna; ?>">Wysokość paska czasu (w px):</label>             
                                                          <?php
                                                          $tablica = array( array('id' => '3', 'text' => '3px'),
                                                                            array('id' => '5', 'text' => '5px'),
                                                                            array('id' => '7', 'text' => '7px'),
                                                                            array('id' => '10', 'text' => '10px'),
                                                                            array('id' => '12', 'text' => '12px'),
                                                                            array('id' => '15', 'text' => '15px'),
                                                                            array('id' => '20', 'text' => '20px'),
                                                                            array('id' => '25', 'text' => '25px'));
                                                                            
                                                          echo Funkcje::RozwijaneMenu('pasek_animacji_wysokosc_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['pasek_animacji_wysokosc_kolumna'][$NrKolumna])) ? $konfig['pasek_animacji_wysokosc_kolumna'][$NrKolumna] : ''), ' id="pasek_animacji_wysokosc_' . $NrKolumna . '"');
                                                          unset($tablica);
                                                          ?>
                                                        </p> 

                                                    </div>
                                                    
                                                </div>
                                                
                                            </div>

                                            <p>
                                              <label for="animacja_czas_szybkosc_<?php echo $NrKolumna; ?>">Czas w sekundach trwania animacji (efektu przewijania/przenikania do kolejnego elementu):</label>             
                                              <?php
                                              $tablica = array();
                                              for ( $t = 3; $t < 12; $t++ ) {
                                                    $tablica[] = array('id' => (($t * 100) / 1000), 'text' => number_format((($t * 100) / 1000), 2, '.', ' '));
                                              }
                                              echo Funkcje::RozwijaneMenu('animacja_czas_szybkosc_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_czas_szybkosc_kolumna'][$NrKolumna])) ? $konfig['animacja_czas_szybkosc_kolumna'][$NrKolumna] : ''), ' id="animacja_czas_szybkosc_' . $NrKolumna . '"');
                                              unset($tablica);
                                              ?>
                                            </p>    

                                            <p>
                                              <label for="animacja_ilosc_przewiniec_<?php echo $NrKolumna; ?>">Co ile elementów ma być przewijana animacja ?</label>             
                                              <?php
                                              $tablica = array( array('id' => 'jeden', 'text' => 'co jeden element'),
                                                                array('id' => 'kilka', 'text' => 'cały wiersz (o ilość elementów w wierszu)') );
                                                                
                                              echo Funkcje::RozwijaneMenu('animacja_ilosc_przewiniec[' . $NrKolumna . ']', $tablica, ((isset($konfig['animacja_ilosc_przewiniec'][$NrKolumna])) ? $konfig['animacja_ilosc_przewiniec'][$NrKolumna] : ''), ' id="animacja_ilosc_przewiniec_' . $NrKolumna . '"');
                                              unset($tablica);
                                              ?>
                                            </p>                                                

                                            <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaBannerowAnimowane" <?php echo ((isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna]) && $konfig['forma_wyswietlania_kolumna'][$NrKolumna] == 'animowany' && isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'bannery') ? '' : 'style="display:none"'); ?>>

                                                <!-- tylko do animowanych bannerow -->
                                                
                                                <div class="TytulDzialu">Ustawienia wyświetlania bannerów animowanych - ilość kolumn w ilu mają być wyświetlane bannery (dla różnych rozdzielności ekranu)</div>
                                                
                                                <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy animacji w postaci <b>przewijanej</b> - w formie przenikania wyświetlana jest tylko <u><b>jedna</b></u> grafika bez dodatkowych opcji.</div>

                                                <?php
                                                $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                                //
                                                for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                             
                                                  <p>
                                                    <label for="ilosc_kolumn_bannerow_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                                    <?php
                                                    $tablica = array();
                                                    for ( $t = 1; $t < 11; $t++ ) {
                                                          $tablica[] = array('id' => $t, 'text' => $t);
                                                    }
                                                    echo Funkcje::RozwijaneMenu('ilosc_kolumn_bannerow_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_bannerow_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_bannerow_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_bannerow_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                                    unset($tablica);
                                                    ?>
                                                  </p>
                                                  
                                                <?php 
                                                } 
                                                //
                                                unset($rozdzielczosci);
                                                ?>  
                                                
                                                <p>
                                                  <label for="animowane_bannery_margines_<?php echo $NrKolumna; ?>">Margines w px pomiędzy bannerami:</label>             
                                                  <?php
                                                  $tablica = array( array('id' => '0', 'text' => '0px'),
                                                                    array('id' => '2', 'text' => '2px'),
                                                                    array('id' => '3', 'text' => '3px'),
                                                                    array('id' => '4', 'text' => '4px'),
                                                                    array('id' => '5', 'text' => '5px'),
                                                                    array('id' => '10', 'text' => '10px'),
                                                                    array('id' => '15', 'text' => '15px'),
                                                                    array('id' => '20', 'text' => '20px'),
                                                                    array('id' => '25', 'text' => '25px'),
                                                                    array('id' => '30', 'text' => '30px'),
                                                                    array('id' => '40', 'text' => '40px'),
                                                                    array('id' => '50', 'text' => '50px') );
                                                                    
                                                  echo Funkcje::RozwijaneMenu('animowane_bannery_margines_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animowane_bannery_margines_kolumna'][$NrKolumna])) ? $konfig['animowane_bannery_margines_kolumna'][$NrKolumna] : ''), ' id="animowane_bannery_margines_' . $NrKolumna . '"');
                                                  unset($tablica);
                                                  ?>
                                                </p> 

                                                <p>
                                                  <label for="animowane_bannery_ladowanie_<?php echo $NrKolumna; ?>">Wczytaj grafikę dopiero w momencie wyświetlania banneru:</label>             
                                                  <?php
                                                  $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                                    array('id' => 'tak', 'text' => 'tak') );
                                                                    
                                                  echo Funkcje::RozwijaneMenu('animowane_bannery_ladowanie_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['animowane_bannery_ladowanie_kolumna'][$NrKolumna])) ? $konfig['animowane_bannery_ladowanie_kolumna'][$NrKolumna] : ''), ' id="animowane_bannery_ladowanie_' . $NrKolumna . '"');
                                                  unset($tablica);
                                                  ?>
                                                  <em class="TipIkona"><b>Opcja TAK powoduje wyświetlanie grafiki dopiero kiedy będzie widoczna na ekranie (szybsze wczytywanie strony - jednak mogą występować opóźnienia w wyświetlaniu kolejnych slajdów banneru) - opcja NIE powoduje załadowanie grafiki od razu przy wczytywaniu strony.</b></em>
                                                </p>                                                 

                                            </div>
 
                                            <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaProducentowAnimowane" <?php echo ((isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna]) && $konfig['forma_wyswietlania_kolumna'][$NrKolumna] == 'animowany' && isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'producenci') ? '' : 'style="display:none"'); ?>>
                                            
                                                <!-- tylko do animowanych producentow -->
                                            
                                                <div class="TytulDzialu">Ustawienia wyświetlania producentów animowanych - ilość kolumn w ilu mają być wyświetlani producenci (dla różnych rozdzielności ekranu)</div>
                                                
                                                <div class="maleInfo" style="margin:-5px 0px 5px 25px">Poniższe ustawienia dotyczą formy animacji w postaci <b>przewijanej</b> - w formie przenikania wyświetlany jest tylko <u><b>jeden</b></u> producent bez dodatkowych opcji.</div>

                                                <?php
                                                $rozdzielczosci = array(1600,1200,1024,800,480,300);
                                                //
                                                for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                             
                                                  <p>
                                                    <label for="ilosc_kolumn_producentow_<?php echo $rozdzielczosci[$tr]; ?>_kolumna_<?php echo $NrKolumna; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                                    <?php
                                                    $tablica = array();
                                                    for ( $t = 1; $t < 16; $t++ ) {
                                                          $tablica[] = array('id' => $t, 'text' => $t);
                                                    }
                                                    echo Funkcje::RozwijaneMenu('ilosc_kolumn_producentow_' . $rozdzielczosci[$tr] . '_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['ilosc_kolumn_producentow_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna])) ? $konfig['ilosc_kolumn_producentow_' . $rozdzielczosci[$tr] . '_kolumna'][$NrKolumna] : ''), ' id="ilosc_kolumn_producentow_' . $rozdzielczosci[$tr] . '_kolumna_' . $NrKolumna . '"');
                                                    unset($tablica);
                                                    ?>
                                                  </p>
                                                  
                                                <?php 
                                                } 
                                                //
                                                unset($rozdzielczosci);
                                                ?>     

                                            </div>

                                        </div>
                                        
                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaBannerowStatyczne" <?php echo (((isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna]) && $konfig['forma_wyswietlania_kolumna'][$NrKolumna] == 'statyczny' && isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'bannery') || !isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna])) ? '' : 'style="display:none"'); ?>>
                                        
                                            <div class="TytulDzialu">Ustawienia wyświetlania bannerów statycznych</div>
                                        
                                            <p>
                                              <label for="statyczne_bannery_wyswietlanie_<?php echo $NrKolumna; ?>">Sposób wyświetlania bannerów:</label>             
                                              <?php
                                              $tablica = array( array('id' => 'osobno', 'text' => 'każdy w osobnej linii'),
                                                                array('id' => 'obok', 'text' => 'obok siebie') );
                                                                
                                              echo Funkcje::RozwijaneMenu('statyczne_bannery_wyswietlanie_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['statyczne_bannery_wyswietlanie_kolumna'][$NrKolumna])) ? $konfig['statyczne_bannery_wyswietlanie_kolumna'][$NrKolumna] : ''), ' id="statyczne_bannery_wyswietlanie_' . $NrKolumna . '"');
                                              unset($tablica);
                                              ?>
                                            </p> 
                                            
                                            <p>
                                              <label for="statyczne_bannery_margines_<?php echo $NrKolumna; ?>">Margines w px pomiędzy bannerami:</label>             
                                              <?php
                                              $tablica = array( array('id' => '0', 'text' => '0px'),
                                                                array('id' => '5', 'text' => '5px'),
                                                                array('id' => '10', 'text' => '10px'),
                                                                array('id' => '15', 'text' => '15px'),
                                                                array('id' => '20', 'text' => '20px'),
                                                                array('id' => '25', 'text' => '25px'),
                                                                array('id' => '30', 'text' => '30px'),
                                                                array('id' => '40', 'text' => '40px'),
                                                                array('id' => '50', 'text' => '50px') );
                                                                
                                              echo Funkcje::RozwijaneMenu('statyczne_bannery_margines_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['statyczne_bannery_margines_kolumna'][$NrKolumna])) ? $konfig['statyczne_bannery_margines_kolumna'][$NrKolumna] : ''), ' id="statyczne_bannery_margines_' . $NrKolumna . '"');
                                              unset($tablica);
                                              ?>
                                            </p>                                            

                                        </div>
                                        
                                        <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>ZakresUstawieniaProducentowStatyczne" <?php echo ((isset($konfig['forma_wyswietlania_kolumna'][$NrKolumna]) && $konfig['forma_wyswietlania_kolumna'][$NrKolumna] == 'statyczny' && isset($konfig['dane_kolumna'][$NrKolumna]) && $konfig['dane_kolumna'][$NrKolumna] == 'producenci') ? '' : 'style="display:none"'); ?>>
                                        
                                            <div class="TytulDzialu">Ustawienia wyświetlania producentów statycznych (wyświetlane jeden obok drugiego w formie chmury)</div>

                                            <p>
                                              <label for="statyczne_producentow_margines_<?php echo $NrKolumna; ?>">Margines w px pomiędzy producentami:</label>             
                                              <?php
                                              $tablica = array( array('id' => '0', 'text' => '0px'),
                                                                array('id' => '5', 'text' => '5px'),
                                                                array('id' => '10', 'text' => '10px'),
                                                                array('id' => '15', 'text' => '15px'),
                                                                array('id' => '20', 'text' => '20px'),
                                                                array('id' => '25', 'text' => '25px'),
                                                                array('id' => '30', 'text' => '30px'),
                                                                array('id' => '40', 'text' => '40px'),
                                                                array('id' => '50', 'text' => '50px') );
                                                                
                                              echo Funkcje::RozwijaneMenu('statyczne_producentow_margines_kolumna[' . $NrKolumna . ']', $tablica, ((isset($konfig['statyczne_producentow_margines_kolumna'][$NrKolumna])) ? $konfig['statyczne_producentow_margines_kolumna'][$NrKolumna] : ''), ' id="statyczne_producentow_margines_' . $NrKolumna . '"');
                                              unset($tablica);
                                              ?>
                                            </p>                                            

                                        </div>                                        
                                        
                                    </div>
                                    
                                    <div class="TytulDzialu">Ustawienia wyglądu kolumny</div>
                                    
                                    <p>
                                        <label>Wygląd kolumny:</label>
                                        <input type="radio" value="0" name="kolumna_wyglad_kolumna[<?php echo $NrKolumna; ?>]" id="szablon_standardowy_kolumna_<?php echo $NrKolumna; ?>" onclick="zmien_wyglad(0, '<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>')" <?php echo (((isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna]) && $konfig['kolumna_wyglad_kolumna'][$NrKolumna] == '0') || !isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_standardowy_kolumna_<?php echo $NrKolumna; ?>">standardowy<em class="TipIkona"><b>Zawartość będzie wyświetlana w standardowym wyglądzie</b></em></label>
                                        <input type="radio" value="1" name="kolumna_wyglad_kolumna[<?php echo $NrKolumna; ?>]" id="szablon_indywidualny_kolumna_<?php echo $NrKolumna; ?>" onclick="zmien_wyglad(1, '<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>')" <?php echo ((isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna]) && $konfig['kolumna_wyglad_kolumna'][$NrKolumna] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_indywidualny_kolumna_<?php echo $NrKolumna; ?>">indywidualny<em class="TipIkona"><b>Zawartość będzie wyświetlana w indywidualnym wyglądzie</b></em></label>
                                        <input type="radio" value="2" name="kolumna_wyglad_kolumna[<?php echo $NrKolumna; ?>]" id="szablon_brak_kolumna_<?php echo $NrKolumna; ?>" onclick="zmien_wyglad(2, '<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>')" <?php echo ((isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna]) && $konfig['kolumna_wyglad_kolumna'][$NrKolumna] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_brak_kolumna_<?php echo $NrKolumna; ?>">sama treść<em class="TipIkona"><b>Bezpośrednie wyświetlanie ustawionej treści - bez żadnych dodatkowych elementów (tytułu, ramki etc)</b></em></label>
                                    </p>   

                                    <div id="wyglad_kolumna_<?php echo $NrKolumna; ?>" <?php echo (((isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna]) && $konfig['kolumna_wyglad_kolumna'][$NrKolumna] != '1') || !isset($konfig['kolumna_wyglad_kolumna'][$NrKolumna])) ? 'style="display:none"' : ''); ?>>
                                        <p>
                                            <label class="required" for="plik_wyglad_kolumna_<?php echo $NrKolumna; ?>">Nazwa pliku wyglądu kolumny w szablonie:</label>
                                            <input type="text" name="plik_wyglad_kolumna[<?php echo $NrKolumna; ?>]" id="plik_wyglad_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['plik_wyglad_kolumna'][$NrKolumna]) && $konfig['kolumna_wyglad_kolumna'][$NrKolumna] == '1') ? $konfig['plik_wyglad_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd w szablonie np. moj_wyglad.tp (plik musi się znajdować w katalogu szablonu w podkatalogu /moduly_kreator_wyglad)</b></em>
                                        </p>
                                    </div> 
                                    
                                    <div class="Kolumna<?php echo ucfirst($NrKolumna); ?>WygladListingu" <?php echo ((isset($konfig['dane_kolumna'][$NrKolumna]) && in_array((string)$konfig['dane_kolumna'][$NrKolumna], array('opis', 'java'))) ? 'style="display:none"' : ''); ?>>
                                    
                                        <p>
                                            <label>Wygląd listingu:</label>
                                            <input type="radio" value="0" name="kolumna_listing_kolumna[<?php echo $NrKolumna; ?>]" id="listing_standardowy_kolumna_<?php echo $NrKolumna; ?>" onclick="zmien_listing(0, '<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>')" <?php echo (((isset($konfig['kolumna_listing_kolumna'][$NrKolumna]) && $konfig['kolumna_listing_kolumna'][$NrKolumna] == '0') || !isset($konfig['kolumna_listing_kolumna'][$NrKolumna])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="listing_standardowy_kolumna_<?php echo $NrKolumna; ?>">standardowy<em class="TipIkona"><b>Listing będzie wyświetlany w standardowym wyglądzie</b></em></label>
                                            <input type="radio" value="1" name="kolumna_listing_kolumna[<?php echo $NrKolumna; ?>]" id="listing_indywidualny_kolumna_<?php echo $NrKolumna; ?>" onclick="zmien_listing(1, '<?php echo $NrKolumna; ?>', '<?php echo ucfirst($NrKolumna); ?>')" <?php echo ((isset($konfig['kolumna_listing_kolumna'][$NrKolumna]) && $konfig['kolumna_listing_kolumna'][$NrKolumna] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="listing_indywidualny_kolumna_<?php echo $NrKolumna; ?>">indywidualny<em class="TipIkona"><b>Listing będzie wyświetlany w indywidualnym wyglądzie</b></em></label>
                                        </p>   

                                        <div id="listing_kolumna_<?php echo $NrKolumna; ?>" <?php echo (((isset($konfig['kolumna_listing_kolumna'][$NrKolumna]) && $konfig['kolumna_listing_kolumna'][$NrKolumna] != '1') || !isset($konfig['kolumna_listing_kolumna'][$NrKolumna])) ? 'style="display:none"' : ''); ?>>
                                            <p>
                                                <label class="required" for="plik_listing_kolumna_<?php echo $NrKolumna; ?>">Nazwa pliku listingu w szablonie:</label>
                                                <input type="text" name="plik_listing_kolumna[<?php echo $NrKolumna; ?>]" id="plik_listing_kolumna_<?php echo $NrKolumna; ?>" value="<?php echo ((isset($konfig['plik_listing_kolumna'][$NrKolumna]) && $konfig['kolumna_listing_kolumna'][$NrKolumna] == '1') ? $konfig['plik_listing_kolumna'][$NrKolumna] : ''); ?>" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego listing w szablonie np. moj_listing.php (plik musi się znajdować w katalogu szablonu w podkatalogu /moduly_kreator_wyglad)</b></em>
                                            </p>
                                        </div>                                     

                                    </div>
                                    
                                    <table class="MarginesyOdstep">
                                        <tr><td><label>Dodatkowe marginesy kolumny dla rozdzielczości <b>powyżej 1024px</b> - <span>kolumny ułożone obok siebie w wierszu</span>:</label></td>
                                        <td>
                                            <div class="TloMarginesy">
                                                <div class="MargGora"><input type="number" min="0" max="150" name="margines_gorny_kolumna_pc[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_gorny_kolumna_pc'][$NrKolumna])) ? (int)$konfig['margines_gorny_kolumna_pc'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                                <div class="MargDol"><input type="number" min="0" max="150" name="margines_dolny_kolumna_pc[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_dolny_kolumna_pc'][$NrKolumna])) ? (int)$konfig['margines_dolny_kolumna_pc'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                                <div class="MargLewy"><input type="number" min="0" max="150" name="margines_lewy_kolumna_pc[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_lewy_kolumna_pc'][$NrKolumna])) ? (int)$konfig['margines_lewy_kolumna_pc'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                                <div class="MargPrawy"><input type="number" min="0" max="150" name="margines_prawy_kolumna_pc[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_prawy_kolumna_pc'][$NrKolumna])) ? (int)$konfig['margines_prawy_kolumna_pc'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                            </div>
                                        </td></tr>
                                    </table> 

                                    <table class="MarginesyOdstep">
                                        <tr><td><label>Dodatkowe marginesy kolumny dla rozdzielczości <b>poniżej 1024px</b> - <span>każda kolumna będzie wyświetlana w osobnym wierszu</span>:</label></td>
                                        <td>
                                            <div class="TloMarginesy">
                                                <div class="MargGora"><input type="number" min="0" max="150" name="margines_gorny_kolumna_mobile[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_gorny_kolumna_mobile'][$NrKolumna])) ? (int)$konfig['margines_gorny_kolumna_mobile'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                                <div class="MargDol"><input type="number" min="0" max="150" name="margines_dolny_kolumna_mobile[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_dolny_kolumna_mobile'][$NrKolumna])) ? (int)$konfig['margines_dolny_kolumna_mobile'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                                <div class="MargLewy"><input type="number" min="0" max="150" name="margines_lewy_kolumna_mobile[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_lewy_kolumna_mobile'][$NrKolumna])) ? (int)$konfig['margines_lewy_kolumna_mobile'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                                <div class="MargPrawy"><input type="number" min="0" max="150" name="margines_prawy_kolumna_mobile[<?php echo $NrKolumna; ?>]" class="zero" value="<?php echo ((isset($konfig['margines_prawy_kolumna_mobile'][$NrKolumna])) ? (int)$konfig['margines_prawy_kolumna_mobile'][$NrKolumna] : '0'); ?>" size="4" /> px</div>
                                            </div>
                                        </td></tr>
                                    </table> 
                                    
                                    <p>
                                      <label>Czy wyświetlać tą kolumnę przy małych rozdzielczościach (na urządzeniach mobilnych) ?</label>
                                      <input type="radio" value="tak" name="rwd_mala_rozdzielczosc_kolumna[<?php echo $NrKolumna; ?>]" id="rwd_mala_rozdzielczosc_kolumna_tak_<?php echo $NrKolumna; ?>" <?php echo (((isset($konfig['rwd_mala_rozdzielczosc_kolumna'][$NrKolumna]) && $konfig['rwd_mala_rozdzielczosc_kolumna'][$NrKolumna] == 'tak') || !isset($konfig['rwd_mala_rozdzielczosc_kolumna'][$NrKolumna])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_mala_rozdzielczosc_kolumna_tak_<?php echo $NrKolumna; ?>">tak<em class="TipIkona"><b>Kolumna będzie widoczna przy małych rozdzielczościach ekranu</b></em></label>
                                      <input type="radio" value="nie" name="rwd_mala_rozdzielczosc_kolumna[<?php echo $NrKolumna; ?>]" id="rwd_mala_rozdzielczosc_kolumna_nie_<?php echo $NrKolumna; ?>" <?php echo ((isset($konfig['rwd_mala_rozdzielczosc_kolumna'][$NrKolumna]) && $konfig['rwd_mala_rozdzielczosc_kolumna'][$NrKolumna] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_mala_rozdzielczosc_kolumna_nie_<?php echo $NrKolumna; ?>">nie<em class="TipIkona"><b>Kolumna nie będzie widoczna przy małych rozdzielczościach ekranu</b></em></label>
                                    </p>                                  
                                        
                                    <p>
                                      <label>Czy wyświetlać tą kolumnę jeżeli nie ma treści do wyświetlania (np. produktów) ?</label>
                                      <input type="radio" value="tak" name="wyswietlana_kolumna[<?php echo $NrKolumna; ?>]" id="wyswietlana_kolumna_tak_<?php echo $NrKolumna; ?>" <?php echo (((isset($konfig['wyswietlana_kolumna'][$NrKolumna]) && $konfig['wyswietlana_kolumna'][$NrKolumna] == 'tak') || !isset($konfig['wyswietlana_kolumna'][$NrKolumna])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wyswietlana_kolumna_tak_<?php echo $NrKolumna; ?>">tak</label>
                                      <input type="radio" value="nie" name="wyswietlana_kolumna[<?php echo $NrKolumna; ?>]" id="wyswietlana_kolumna_nie_<?php echo $NrKolumna; ?>" <?php echo ((isset($konfig['wyswietlana_kolumna'][$NrKolumna]) && $konfig['wyswietlana_kolumna'][$NrKolumna] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wyswietlana_kolumna_nie_<?php echo $NrKolumna; ?>">nie</label>
                                    </p>    
                                    
                                    <p class="DodatkowyCssKolumny">
                                      <label style="width:100%" for="css_kolumna_<?php echo $NrKolumna; ?>">Dodatkowy kod CSS kolumny:</label>
                                      <textarea hidden name="css_kolumna[<?php echo $NrKolumna; ?>]" id="css_kolumna_<?php echo $NrKolumna; ?>" rows="5" cols="50" style="width:50%"><?php echo ((isset($konfig['css_kolumna'][$NrKolumna])) ? $konfig['css_kolumna'][$NrKolumna] : ''); ?></textarea>  
                                      <span class="maleInfo" style="display:block;margin:0">Sam kod css bez znacznika style. Każdą linie trzeba zacząć od znacznika {KLASA_CSS_KOLUMNY} w miejsce którego zostanie podstawiona klasa css kolumny - kod będzie działał tylko w obrębie tej kolumny</span>
                                    </p>   
                                    
                                    <div style="padding:3px;margin:0 15px 10px 25px;border:1px solid #dbdbdb;border-radius:3px;">
                                    
                                        <div id="ace_css_kolumna_<?php echo $NrKolumna; ?>"><?php echo ((isset($konfig['css_kolumna'][$NrKolumna])) ? $konfig['css_kolumna'][$NrKolumna] : ''); ?></div>
                                        
                                    </div>

                                    <script>
                                    var textarea<?php echo $NrKolumna; ?> = $('#css_kolumna_<?php echo $NrKolumna; ?>');
                                    var editor<?php echo $NrKolumna; ?> = ace.edit("ace_css_kolumna_<?php echo $NrKolumna; ?>", {
                                        theme: "ace/theme/chrome",
                                        mode: "ace/mode/css",
                                        minLines: 5,
                                        maxLines: 50,
                                        tabSize: 2,
                                        showPrintMargin: false,
                                        showInvisibles: false,
                                        fontSize: '13px',
                                        useWorker: false
                                    });

                                    editor<?php echo $NrKolumna; ?>.getSession().on('change', function () {
                                       textarea<?php echo $NrKolumna; ?>.val(editor<?php echo $NrKolumna; ?>.getSession().getValue());
                                    });

                                    textarea<?php echo $NrKolumna; ?>.val(editor<?php echo $NrKolumna; ?>.getSession().getValue());
                                    </script>

                                </div>

                            </div>
                            
                      <?php } ?>
         
                  </div>

                  <div class="TytulDzialu">Szerokość modułu w sklepie</div>
                  
                  <script> 
                  function zmiana_szerokosci_modulu(wartosc) {                      
                      if (wartosc == 'szerokosc_sto') {
                          $('#tlo_szerokosc option[value=szerokosc_sklep]').attr('disabled','disabled');
                      } else {
                          $('#tlo_szerokosc option[value=szerokosc_sklep]').prop('disabled',false);                        
                      }
                      $('#tlo_szerokosc').prop("selectedIndex", 1);
                  }                     
                  </script>                    

                  <p class="SzerokoscModulu">
                      <label for="szerokosc_modul">Szerokość modułu:</label>
                      <?php
                      $tablica = array( array('id' => 'szerokosc_sklep', 'text' => 'na szerokość sklepu'),
                                        array('id' => 'szerokosc_sto', 'text' => 'na 100% szerokości ekranu') );
                                        
                      echo Funkcje::RozwijaneMenu('szerokosc_modul', $tablica, ((isset($konfig['modul_width'])) ? $konfig['modul_width'] : ''), ' id="szerokosc_modul" onchange="zmiana_szerokosci_modulu(this.value)"');
                      unset($tablica);
                      ?>
                      <span class="maleInfo">100% szerokości ekranu będzie wyświetlane tylko w sklepach z wyłączoną lewą i prawą kolumną z boxami - aktywna tylko kolumna środkowa z modułami</span>
                  </p>   
                  
                  <div class="TytulDzialu" style="margin-top:5px">Ustawienia tła modułu</div>

                  <script> 
                  function zmien_tlo_modulu(id) {
                      if (id == 0) {
                          $('#tlo_modulu_1').stop().slideUp();
                          $('#tlo_modulu_2').stop().slideUp();
                          $('#tlo_modulu_rodzaj').stop().slideUp();
                      }                        
                      if (id == 1) {
                          $('#tlo_modulu_2').stop().slideUp();
                          $('#tlo_modulu_1').stop().slideDown();
                          $('#tlo_modulu_rodzaj').stop().slideDown();
                      }
                      if (id == 2) {
                          $('#tlo_modulu_1').stop().slideUp();
                          $('#tlo_modulu_2').stop().slideDown(); 
                          $('#tlo_modulu_rodzaj').stop().slideDown();
                      }  
                  }                     
                  </script>
                  
                  <p>
                      <label>Tło modułu:</label>
                      <input type="radio" value="brak" name="tlo_modulu" id="tlo_modulu_brak" onclick="zmien_tlo_modulu(0)" <?php echo (((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'brak') || !isset($konfig['modul_background_type'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_modulu_brak">brak<em class="TipIkona"><b>Moduł będzie miał tło jak reszta sklepu</b></em></label>
                      <input type="radio" value="kolor" name="tlo_modulu" id="tlo_modulu_jednolity" onclick="zmien_tlo_modulu(1)" <?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'kolor') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_modulu_jednolity">jednolity kolor</label>
                      <input type="radio" value="obrazek" name="tlo_modulu" id="tlo_modulu_obrazek" onclick="zmien_tlo_modulu(2)" <?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'obrazek') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_modulu_obrazek">tło obrazkowe</label>
                  </p>

                  <div id="tlo_modulu_1" <?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'kolor') ? '' : 'style="display:none"'); ?>>
                  
                      <p>
                        <label for="color">Kolor:</label>
                        <input name="kolor" class="color {required:false}" id="color" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'kolor') ? ((isset($konfig['modul_background_value'])) ? $konfig['modul_background_value'] : '') : ''); ?>" size="8" />                    
                      </p>
                      
                  </div>
                  
                  <div id="tlo_modulu_2" <?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'obrazek') ? '' : 'style="display:none"'); ?>>
                  
                      <p>
                        <label for="tlo_obrazkowe">Ścieżka zdjęcia:</label>           
                        <input type="text" name="tlo_obrazkowe" size="95" value="<?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] == 'obrazek') ? ((isset($konfig['modul_background_value'])) ? $konfig['modul_background_value'] : '') : ''); ?>" class="obrazek" ondblclick="openFileBrowser('tlo_obrazkowe','','<?php echo KATALOG_ZDJEC; ?>')" id="tlo_obrazkowe" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                        <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('tlo_obrazkowe','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                      </p> 
                      
                      <p>
                        <label for="tlo_powtarzanie">Powtarzanie i wyrównanie tła:</label>
                        <?php
                        $tablica = array( array('id' => 'no-repeat center center', 'text' => 'bez powtarzania wyśrodkowane (w pionie i poziomie)'),
                                          array('id' => 'no-repeat left center', 'text' => 'bez powtarzania wyrównane do lewej krawędzi w poziomie, wyśrodkowe w pionie'),
                                          array('id' => 'no-repeat right center', 'text' => 'bez powtarzania wyrównane do prawej krawędzi w poziomie, wyśrodkowe w pionie'),
                                          array('id' => 'no-repeat top center', 'text' => 'bez powtarzania wyśrodkowe w poziomie, wyrównane do górnej krawędzi w pionie'),
                                          array('id' => 'no-repeat bottom center', 'text' => 'bez powtarzania wyśrodkowe w poziomie, wyrównane do dolnej krawędzi w pionie'),
                                          array('id' => 'repeat-x', 'text' => 'w poziomie'),
                                          array('id' => 'repeat-y', 'text' => 'w pionie'),
                                          array('id' => 'repeat', 'text' => 'w poziomie i pionie') );
                                          
                        echo Funkcje::RozwijaneMenu('tlo_powtarzanie', $tablica, ((isset($konfig['modul_background_repeat'])) ? $konfig['modul_background_repeat'] : ''), ' id="tlo_powtarzanie"');
                        unset($tablica);
                        ?>                              
                      </p>
                      
                      <p>
                        <label for="tlo_skalowanie">Wielkość grafiki tła:</label>
                        <?php
                        $tablica = array( array('id' => 'auto', 'text' => 'szerokość i wysokość grafiki bez skalowania'),
                                          array('id' => '100_szerokosc', 'text' => '100% szerokości - wysokość proporcjonalna'),
                                          array('id' => '100_wysokosc', 'text' => '100% wysokości - szerokość proporcjonalna'),
                                          array('id' => '100_szerokosc_wysokosc', 'text' => '100% szerokości i 100% wysokości'),
                                          array('id' => 'cover', 'text' => 'cover - skaluje obrazek w taki sposób, aby wypełnić w całości rozmiar szerokości obrazka') );
                                          
                        echo Funkcje::RozwijaneMenu('tlo_skalowanie', $tablica, ((isset($konfig['modul_background_image_width'])) ? $konfig['modul_background_image_width'] : ''), ' id="tlo_skalowanie"');
                        unset($tablica);
                        ?>                              
                      </p>                          
                      
                      <p>
                        <label for="tlo_przewijanie">Sposób wyświetlania tła:</label>
                        <?php
                        $tablica = array( array('id' => 'scroll', 'text' => 'tła przewijane razem z oknem przeglądarki'),
                                          array('id' => 'fixed', 'text' => 'tło nieruchome względem okna przeglądarki') );
                                          
                        echo Funkcje::RozwijaneMenu('tlo_przewijanie', $tablica, ((isset($konfig['modul_background_attachment'])) ? $konfig['modul_background_attachment'] : ''), ' id="tlo_przewijanie"');
                        unset($tablica);
                        ?>                              
                      </p>                      
                      
                  </div>                    
                  
                  <div class="SzerokoscModulu" id="tlo_modulu_rodzaj" <?php echo ((isset($konfig['modul_background_type']) && $konfig['modul_background_type'] != 'brak') ? '' : 'style="display:none"'); ?>>
                      <p>
                          <label for="tlo_szerokosc">Tło modułu szerokość:</label>
                          <?php
                          $tablica = array( array('id' => 'szerokosc_sklep', 'text' => 'na szerokość sklepu'),
                                            array('id' => 'szerokosc_sto', 'text' => 'na 100% szerokości ekranu') );
                                            
                          echo Funkcje::RozwijaneMenu('tlo_szerokosc', $tablica, ((isset($konfig['modul_background_width'])) ? $konfig['modul_background_width'] : ''), ' id="tlo_szerokosc"');
                          unset($tablica);
                          ?>
                          <span class="maleInfo">100% szerokości ekranu będzie wyświetlane tylko w sklepach z wyłączoną lewą i prawą kolumną z boxami - aktywna tylko kolumna środkowa z modułami</span>
                      </p>                   
                  </div>
                  
                  <script> 
                  $(document).ready(function() {    
                      if ( $('#szerokosc_modul option:selected').val() != 'szerokosc_sklep' ) {
                           $('#tlo_szerokosc option[value=szerokosc_sklep]').attr('disabled','disabled');
                           $('#tlo_szerokosc').prop("selectedIndex", 1);
                      }                      
                  });                  
                  </script>                     

                  <table class="MarginesyOdstep">
                      <tr><td><label>Dodatkowe marginesy dla całego modułu:</label></td>
                      <td>
                          <div class="TloMarginesy">
                              <div class="MargGora"><input type="number" min="0" max="150" name="margines_gorny" class="zero" value="<?php echo ((isset($konfig['modul_margines_top'])) ? (int)$konfig['modul_margines_top'] : '0'); ?>" size="4" /> px</div>
                              <div class="MargDol"><input type="number" min="0" max="150" name="margines_dolny" class="zero" value="<?php echo ((isset($konfig['modul_margines_bottom'])) ? (int)$konfig['modul_margines_bottom'] : '0'); ?>" size="4" /> px</div>
                              <div class="MargLewy"><input type="number" min="0" max="150" name="margines_lewy" class="zero" value="<?php echo ((isset($konfig['modul_margines_left'])) ? (int)$konfig['modul_margines_left'] : '0'); ?>" size="4" /> px</div>
                              <div class="MargPrawy"><input type="number" min="0" max="150" name="margines_prawy" class="zero" value="<?php echo ((isset($konfig['modul_margines_right'])) ? (int)$konfig['modul_margines_right'] : '0'); ?>" size="4" /> px</div>
                          </div>
                      </td></tr>
                  </table>   
                      
                  <table class="MarginesyOdstep">
                      <tr><td><label>Dodatkowe odstępy dla całego modułu:</label></td>
                      <td>
                          <div class="TloOdstep">
                              <div class="MargGora"><input type="number" min="0" max="150" name="odstep_gorny" class="zero" value="<?php echo ((isset($konfig['modul_padding_top'])) ? (int)$konfig['modul_padding_top'] : '0'); ?>" size="4" /> px</div>
                              <div class="MargDol"><input type="number" min="0" max="150" name="odstep_dolny" class="zero" value="<?php echo ((isset($konfig['modul_padding_bottom'])) ? (int)$konfig['modul_padding_bottom'] : '0'); ?>" size="4" /> px</div>
                              <div class="MargLewy"><input type="number" min="0" max="150" name="odstep_lewy" class="zero" value="<?php echo ((isset($konfig['modul_padding_left'])) ? (int)$konfig['modul_padding_left'] : '0'); ?>" size="4" /> px</div>
                              <div class="MargPrawy"><input type="number" min="0" max="150" name="odstep_prawy" class="zero" value="<?php echo ((isset($konfig['modul_padding_right'])) ? (int)$konfig['modul_padding_right'] : '0'); ?>" size="4" /> px</div>
                          </div>
                      </td></tr>
                  </table>
                  
                  <p class="DodatkowyCssModul">
                    <label style="width:100%" for="css_caly_modul">Dodatkowy kod CSS dla całego modułu:</label>
                    <textarea hidden name="css_caly_modul" id="css_caly_modul" rows="5" cols="50" style="width:50%"><?php echo ((isset($konfig['css_caly_modul'])) ? $konfig['css_caly_modul'] : ''); ?></textarea>
                    <span class="maleInfo" style="display:block;margin-left:14px;">Sam kod css bez znacznika style. Każdą linie trzeba zacząć od znacznika {KLASA_CSS_MODUL} w miejsce którego zostanie podstawiona klasa css całego moduł - kod będzie działał tylko w obrębie całego modułu</span>
                  </p>          

                  <div style="padding:3px;margin:0 15px 0 24px;border:1px solid #dbdbdb;border-radius:3px;">
                  
                      <div id="ace_css_caly_modul"><?php echo ((isset($konfig['css_caly_modul'])) ? $konfig['css_caly_modul'] : ''); ?></div>
                      
                  </div>

                  <script>
                  var textareamodul = $('#css_caly_modul');
                  var editormodul = ace.edit("ace_css_caly_modul", {
                      theme: "ace/theme/chrome",
                      mode: "ace/mode/css",
                      minLines: 5,
                      maxLines: 50,
                      tabSize: 2,
                      showPrintMargin: false,
                      showInvisibles: false,
                      fontSize: '13px',
                      useWorker: false
                  });

                  editormodul.getSession().on('change', function () {
                     textareamodul.val(editormodul.getSession().getValue());
                  });

                  textareamodul.val(editormodul.getSession().getValue());
                  </script>   

                  <p class="DodatkowyJsModul" style="margin-top:20px">
                    <label style="width:100%" for="js_caly_modul">Dodatkowy kod JavaScript dla całego modułu:</label>
                    <textarea hidden name="js_caly_modul" id="js_caly_modul" rows="5" cols="50" style="width:50%"><?php echo ((isset($konfig['js_caly_modul'])) ? $konfig['js_caly_modul'] : ''); ?></textarea>
                    <span class="maleInfo" style="display:block;margin-left:14px;">Sam kod JavaScript bez znacznika script. W kodzie można użyć znacznik {KLASA_CSS_MODUL} w miejsce którego zostanie podstawiona klasa css całego moduł</span>
                  </p>          

                  <div style="padding:3px;margin:0 15px 0 24px;border:1px solid #dbdbdb;border-radius:3px;">
                  
                      <div id="ace_js_caly_modul"><?php echo ((isset($konfig['js_caly_modul'])) ? $konfig['js_caly_modul'] : ''); ?></div>
                      
                  </div>

                  <script>
                  var textareamoduljs = $('#js_caly_modul');
                  var editormoduljs = ace.edit("ace_js_caly_modul", {
                      theme: "ace/theme/chrome",
                      mode: "ace/mode/javascript",
                      minLines: 5,
                      maxLines: 50,
                      tabSize: 2,
                      showPrintMargin: false,
                      showInvisibles: false,
                      fontSize: '13px',
                      useWorker: false
                  });

                  editormoduljs.getSession().on('change', function () {
                     textareamoduljs.val(editormoduljs.getSession().getValue());
                  });

                  textareamoduljs.val(editormoduljs.getSession().getValue());
                  </script>                              

                  <div class="TytulDzialu">Ustawienia wyglądu i miejsca wyświetlania modułu</div>

                  <p>
                      <label>Wygląd całego modułu:</label>
                      <input type="radio" value="0" name="modul_wyglad" id="szablon_standardowy" onclick="zmien_wyglad_caly_modul(0)" <?php echo (((isset($konfig['modul_theme']) && $konfig['modul_theme'] == 0) || !isset($konfig['modul_theme'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_standardowy">standardowy<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w standardowym wyglądzie</b></em></label>
                      <input type="radio" value="1" name="modul_wyglad" id="szablon_indywidualny" onclick="zmien_wyglad_caly_modul(1)" <?php echo ((isset($konfig['modul_theme']) && $konfig['modul_theme'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_indywidualny">indywidualny<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w indywidualnym wyglądzie</b></em></label>
                  </p>   

                  <div id="wyglad" <?php echo ((isset($konfig['modul_theme']) && $konfig['modul_theme'] == 1) ? '' : 'style="display:none"'); ?>>
                      <p>
                          <label class="required" for="plik_wyglad">Nazwa pliku w szablonie:</label>
                          <input type="text" name="plik_wyglad" id="plik_wyglad" value="<?php echo ((isset($konfig['modul_theme_file'])) ? $konfig['modul_theme_file'] : ''); ?>" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd całego modułu w szablonie np. kreator_moj_modul.tp (w katalogu /moduly_kreator_wyglad w szablonie)</b></em>
                      </p>
                  </div> 
                    
                  <p>
                      <label>Wyświetlanie modułu:</label>
                      <input type="radio" value="1" name="polozenie" id="polozenie_wszystkie" onclick="zmien_polozenie(1)" <?php echo ((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_wszystkie">wszystkie strony<em class="TipIkona"><b>Moduł będzie wyświetlany na wszystkich stronach</b></em></label>
                      <input type="radio" value="3" name="polozenie" id="polozenie_podstrony" onclick="zmien_polozenie(3)" <?php echo ((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_podstrony">tylko podstrony<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na podstronach (bez strony głównej)</b></em></label>
                      <input type="radio" value="4" name="polozenie" id="polozenie_linki" onclick="zmien_polozenie(4)" <?php echo ((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_linki">tylko podstrony wg linków<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na określonych podstronach wg podanych linków (bez strony głównej)</b></em></label>
                      <input type="radio" value="2" name="polozenie" id="polozenie_glowna" onclick="zmien_polozenie(2)" <?php echo (((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '2') || !isset($konfig['modul_localization'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_glowna">tylko strona główna<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na stronie głównej sklepu</b></em></label>
                  </p>  

                  <div id="lista_podstron" <?php echo ((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '3') ? '' : 'style="display:none"'); ?>>                    
                  
                      <table class="WyborStrony" style="margin-top:10px;margin-bottom:0px">
                          <tr><td><label>Wybierz strony na których ma się wyświetlać moduł:</label></td>
                          <td>
                              <div>
                              <?php
                              $ZapisaneStrony = array();
                              if ( isset($konfig['modul_localization_site']) ) {                                   
                                   $ZapisaneStrony = explode(';', (string)$konfig['modul_localization_site']);
                              }
                              //
                              foreach ( Funkcje::TablicaPodstronSklepu(true) as $Strona => $Nazwa ) {
                                  //
                                  echo '<input type="checkbox" value="' . $Strona . '" name="strony[]" id="' . $Strona . '" ' . ((in_array((string)$Strona, $ZapisaneStrony)) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="' . $Strona . '">' . $Nazwa . '</label><br />';
                                  //
                              }
                              //
                              ?>
                              </div>
                              <span class="maleInfo" style="margin:5px 0px 0px 7px">Jeżeli nie zostanie wybrana żadna podstrona moduł będzie wyświetlany na wszystkich podstronach</span>
                          </td></tr>
                      </table>                    
                  
                  </div>                

                  <div id="lista_linki" <?php echo ((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '4') ? '' : 'style="display:none"'); ?>>                    
                  
                      <table class="WyborStrony" style="margin-top:10px;margin-bottom:0px">
                          <tr><td><label>Podaj adresy stron na jakich ma wyświetlać się moduł:</label></td>
                          <td>
                              <textarea name="linki_strony" id="linki_strony" rows="10" cols="100"><?php echo ((isset($konfig['modul_localization_link'])) ? (string)$konfig['modul_localization_link'] : ''); ?></textarea>
                              <span class="maleInfo" style="margin:5px 0px 0px 7px">Adresy trzeba wpisywać każdy w osobnej linii - bez adresu sklepu. Tylko sam link np. adres-strony-p-1.html <br /> Jeżeli nie zostanie wpisany żaden adres moduł będzie wyświetlany na wszystkich podstronach</span>
                          </td></tr>
                      </table>                    
                  
                  </div>    
                  
                  <div id="podstrony" class="PodstronyWyswietlanie" <?php echo (((isset($konfig['modul_localization']) && $konfig['modul_localization'] == '1') || (isset($konfig['modul_localization']) && $konfig['modul_localization'] == '3')  || (isset($konfig['modul_localization']) && $konfig['modul_localization'] == '4') ) ? '' : 'style="display:none"'); ?>>
                      <p>
                          <label>W którym miejscu wyświetlać moduł na podstronach sklepu ?</label>
                          <input type="radio" value="gora" name="modul_podstrony_polozenie" id="modul_podstrony_polozenie_gora" <?php echo (((isset($konfig['modul_localization_position']) && $konfig['modul_localization_position'] == 'gora') || !isset($konfig['modul_localization_position'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="modul_podstrony_polozenie_gora">w części górnej sklepu nad główną treścią<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w części górnej sklepu</b></em></label>
                          <input type="radio" value="dol" name="modul_podstrony_polozenie" id="modul_podstrony_polozenie_dol" <?php echo ((isset($konfig['modul_localization_position']) && $konfig['modul_localization_position'] == 'dol') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="modul_podstrony_polozenie_dol">w części dolnej sklepu pod główną treścią<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w części dolnej sklepu</b></em></label>
                          <span class="maleInfo">Powyższe ustawienie jest używane jeżeli moduł jest wyświetlany na podstronach sklepu i jest dodany w menu Wygląd / Ustawienia wyglądu / Moduły środkowe - do wyświetlania w <b>części głównej sklepu</b> (w części gdzie są wyświetlane boxy)</span>
                      </p>                       
                  </div> 

                  <p>
                    <label for="wyrownanie_kolumn">Wyrównanie kolumn w module (w pionie):</label>             
                    <?php
                    $tablica = array( array('id' => 'srodek', 'text' => 'wyrównanie do środka'),
                                      array('id' => 'gora', 'text' => 'wyrównanie do góry'),
                                      array('id' => 'dol', 'text' => 'wyrównanie do dołu') );
                                      
                    echo Funkcje::RozwijaneMenu('wyrownanie_kolumn', $tablica, ((isset($konfig['wyrownanie_kolumn'])) ? $konfig['wyrownanie_kolumn'] : ''), ' id="wyrownanie_kolumna"');
                    unset($tablica);
                    ?>
                  </p>                                    

                  <div class="TytulDzialu">Opis modułu (informacja tylko dla administratora sklepu)</div>
                  
                  <p>
                      <label for="opis_konfiguracja">Opis konfiguracji modułu:</label>
                      <textarea name="opis_konfiguracja" id="opis_konfiguracja" rows="8" cols="70"><?php echo ((isset($konfig['modul_big_description'])) ? $konfig['modul_big_description'] : ''); ?></textarea><em class="TipIkona"><b>Opis konfiguracji modułu - informacja tylko dla administratora sklepu</b></em>
                  </p>                  

                  <p>
                      <label for="opis">Opis modułu:</label>
                      <textarea name="opis" id="opis" rows="2" cols="70"><?php echo ((isset($konfig['modul_description'])) ? $konfig['modul_description'] : ''); ?></textarea><em class="TipIkona"><b>Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                  </p>

                  <p>
                      <label for="wyswietla">Co wyświetla ?</label>
                      <input name="wyswietla" id="wyswietla" type="text" size="40" value="<?php echo ((isset($konfig['modul_display'])) ? $konfig['modul_display'] : ''); ?>" /><em class="TipIkona"><b>Co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                  </p>   

                  <div class="TytulDzialu"><div class="TytulRwd">Ustawienia dodatkowe</div></div>
                  
                  <p class="SzerokoscModulu">
                      <label>Dynamiczne doładowanie modułu:</label>
                      <input type="radio" value="0" name="doladowanie" id="doladowanie_nie" <?php echo (((isset($konfig['doladowanie']) && $konfig['doladowanie'] == 0) || !isset($konfig['doladowanie'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="doladowanie_nie">wczytaj moduł standardowo<em class="TipIkona"><b>Treść modułu zostanie wczytana od razu po wczytaniu sklepu</b></em></label>
                      <input type="radio" value="1" name="doladowanie" id="doladowanie_tak" <?php echo ((isset($konfig['doladowanie']) && $konfig['doladowanie'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="doladowanie_tak">wczytaj dynamiczne<em class="TipIkona"><b>Treść modułu zostanie wczytana jeżeli nastąpi przewinięcie ekranu i moduł będzie widoczny dla użytkownika</b></em></label>
                      <span class="maleInfo">UWAGA !! Doczytywania dynamicznego nie można stosować dla wszystkich modułów wyświetlanych na stronie (może to spowodować większe obciążenie przeglądarki klienta) - najlepiej dla modułów wyświetlanych na końcu strony, które wyświetlają np wiele produktów</span>
                  </p>
                  
                  <p>
                      <label>Wygląd modułu przy małych rozdzielczościach (na urządzeniach mobilnych):</label>
                      <input type="radio" value="0" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_bezzmian" <?php echo (((isset($konfig['rwd_mala_rozdzielczosc']) && $konfig['rwd_mala_rozdzielczosc'] == 0) || !isset($konfig['rwd_mala_rozdzielczosc'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_bezzmian">bez zmian<em class="TipIkona"><b>Moduł będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                      <input type="radio" value="1" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_ukrywanie" <?php echo ((isset($konfig['rwd_mala_rozdzielczosc']) && $konfig['rwd_mala_rozdzielczosc'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_ukrywanie">ma być <u>niewidoczny</u> na urządzeniach mobilnych<em class="TipIkona"><b>Moduł nie będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                      <input type="radio" value="2" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_widoczny" <?php echo ((isset($konfig['rwd_mala_rozdzielczosc']) && $konfig['rwd_mala_rozdzielczosc'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_widoczny">ma być <u>widoczny</u> tylko na urządzeniach mobilnych<em class="TipIkona"><b>Moduł będzie widoczny tylko przy małych rozdzielczościach ekranu</b></em></label>
                  </p>

                  <p>
                      <label>Wyświetlanie modułu w wersjach kolorystycznych ?</label>
                      <input type="radio" value="0" name="wcag_kolor" id="wcag_kolor_zawsze" <?php echo (((isset($konfig['wcag_kolor']) && $konfig['wcag_kolor'] == 0) || !isset($konfig['wcag_kolor'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wcag_kolor_zawsze">zawsze</label>
                      <input type="radio" value="1" name="wcag_kolor" id="wcag_kolor_jasny" <?php echo ((isset($konfig['wcag_kolor']) && $konfig['wcag_kolor'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wcag_kolor_jasny">tylko w domyślnym wyglądzie</label>
                      <input type="radio" value="2" name="wcag_kolor" id="wcag_kolor_ciemny" <?php echo ((isset($konfig['wcag_kolor']) && $konfig['wcag_kolor'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wcag_kolor_ciemny">tylko w trybie ciemnym</label>
                  </p>
                  
              </div>
                  
              <div class="przyciski_dolne">
              
                <input type="hidden" name="powrot" id="powrot" value="0" />
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />
                
                <?php 
                // jezeli jest get zakladka wraca do ustawien wygladu
                if (isset($_GET['zakladka']) ) { ?>
                
                <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                
                <?php } else { ?>

                <button type="button" class="przyciskNon" onclick="cofnij('srodek','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
                
                <?php } ?>
                
              </div>  

            </div>         
            
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
