<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_modul = (int)$_POST['id'];
        //
        $pola = array(
                array('modul_description',$filtr->process($_POST['opis'])),
                array('modul_display',$filtr->process($_POST['wyswietla'])),
                array('modul_localization',(int)$_POST['polozenie']),
                array('modul_width',$filtr->process($_POST['szerokosc_modul'])),
                array('modul_background_type',$filtr->process($_POST['tlo_modulu'])),
                array('modul_rwd',1),                                
                array('modul_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']),
                array('modul_v2',(int)$_POST['v2']));   

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
            $pola[] = array('modul_background_width',((!isset($_POST['tlo_szerokosc'])) ? 'szerokosc_sto' : $filtr->process($_POST['tlo_szerokosc'])));
            $pola[] = array('modul_background_image_width','');
        }        
        if ( $_POST['tlo_modulu'] == 'obrazek' ) {
            $pola[] = array('modul_background_value',$filtr->process($_POST['tlo_obrazkowe']));
            $pola[] = array('modul_background_repeat',$filtr->process($_POST['tlo_powtarzanie']));
            $pola[] = array('modul_background_attachment',$filtr->process($_POST['tlo_przewijanie']));
            $pola[] = array('modul_background_width',((!isset($_POST['tlo_szerokosc'])) ? 'szerokosc_sto' : $filtr->process($_POST['tlo_szerokosc'])));
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
             
        // jezeli wybrano plik php
        if ($_POST['tryb'] == 'plik') {
            $pola[] = array('modul_file',$filtr->process($_POST['plik']));            
        } else {
            $pola[] = array('modul_file','');   
        }
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'strona') {
            $pola[] = array('modul_pages_id',$filtr->process($_POST['stronainfo']));
        } else {
            $pola[] = array('modul_pages_id','');   
        }       
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'java') {
            $pola[] = array('modul_code',$_POST['kod']);
        } else {
            $pola[] = array('modul_code','');   
        }      

        // jezeli jest indywidualny modul
        if ($_POST['modul_wyglad'] == '1') {
            $pola[] = array('modul_theme',$filtr->process($_POST['modul_wyglad']));
            $pola[] = array('modul_theme_file',$filtr->process($_POST['plik_wyglad']));
            $pola[] = array('modul_header',(int)$_POST['naglowek']);
            $pola[] = array('modul_header_link',(((int)$_POST['naglowek'] == 1) ? $filtr->process($_POST['naglowek_link']) : ''));          
        }
        if ($_POST['modul_wyglad'] == '0') {
            $pola[] = array('modul_theme','0');
            $pola[] = array('modul_theme_file','');
            $pola[] = array('modul_header',(int)$_POST['naglowek']);
            $pola[] = array('modul_header_link',(((int)$_POST['naglowek'] == 1) ? $filtr->process($_POST['naglowek_link']) : ''));            
        }        
        if ($_POST['modul_wyglad'] == '2') {
            $pola[] = array('modul_theme','2');
            $pola[] = array('modul_theme_file','');
            $pola[] = array('modul_header','0');
            $pola[] = array('modul_header_link','');
        } 
        
        $sql = $db->update_query('theme_modules' , $pola, " modul_id = '".$id_modul."'");
        unset($pola);
        
        $db->delete_query('theme_modules_description' , " modul_id = '".$id_modul."'");
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //       
            $pola = array(
                    array('modul_id',$id_modul),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('modul_title',$filtr->process($_POST['nazwa_'.$w])),
                    array('modul_title_line_2',$filtr->process($_POST['nazwa_linia_2_'.$w])),
                    array('modul_title_line_3',$filtr->process($_POST['nazwa_linia_3_'.$w])),
                    array('modul_title_description',$filtr->process($_POST['opis_nazwy_'.$w]))); 

            // jezeli wybrano tresc w edytorze
            if ($_POST['tryb'] == 'txt') {
                $pola[] = array('modul_text',$filtr->process($_POST['opis_'.$w]));
            } else {
                $pola[] = array('modul_text','');
            }                    
                    
            $sql = $db->insert_query('theme_modules_description' , $pola);
            unset($pola);
        }
        //
        // jezeli jest strona informacyjna doda do strony info ze jest wyswietlana w module
        if ($_POST['tryb'] == 'strona') {
            //
            // sprawdzi czy zostala zmieniona strona - jezeli tak to usunie ze starej ikone ze jest przypisana do modulu
            if ( $_POST['stronainfo'] != $_POST['poprzednie_id'] ) {
                //
                $pola = array( array('pages_modul',0));
                $db->update_query('pages' , $pola, 'pages_id = ' . (int)$_POST['poprzednie_id']);
                unset($pola);
                //            
            }
            //
            $pola = array( array('pages_modul',1));
            $db->update_query('pages' , $pola, 'pages_id = ' . (int)$_POST['stronainfo']);
            unset($pola);
            //
            // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
            Funkcje::UsuwanieWygladu('strona',$filtr->process($_POST["stronainfo"]));
            //            
        }          
        //
        // sprawdza czy sa jakies dodatkowe ustawienia
        foreach($_POST as $key => $value) {    
            //
            if (substr((string)$key,0,2) == "__") {
                //
                $pola = array(array('value',$filtr->process($value)));
                $sql = $db->update_query('settings' , $pola, " code = '".substr((string)$key,2)."'");
                unset($pola);                
                //
            }
            //
        }
           
        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('srodek_edytuj.php?id_poz=' . (int)$id_modul . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka='.(int)$_POST['zakladka'] : ''));
            //
          } else {        
            //
            if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('wyglad.php?zakladka='.(int)$_POST['zakladka']);
              
              } else if ( isset($_POST['strona']) && (int)$_POST['strona'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('/zarzadzanie/strony_informacyjne/strony_informacyjne_edytuj.php?id_poz='.(int)$_POST['strona']);
              
              } else {
              
                Funkcje::PrzekierowanieURL('srodek.php?id_poz='.(int)$_POST["id"]);
                
            } 
            //
        }        
 
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    
    <div id="cont">        

       <script type="text/javascript" src="programy/jscolor/jscolor.js"></script>

       <form action="wyglad/srodek_edytuj.php" method="post" id="srodekForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            if ( isset($_GET['id_modul']) && (int)$_GET['id_modul'] > 0 && isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {
                 $_GET['id_strony'] = (int)$_GET['id_poz'];
                 $_GET['id_poz'] = (int)$_GET['id_modul'];                 
            }
            
            $zapytanie = "select * from theme_modules where modul_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <script>
                $(document).ready(function() {
                  $("#srodekForm").validate({
                    rules: {
                      <?php if ($info['modul_type'] == 'plik') { ?>
                      plik: {
                        required: true
                      },                 
                      <?php } ?>
                      nazwa_0: {
                        required: true
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
                      <?php if ($info['modul_type'] == 'plik') { ?>
                      plik: {
                        required: "Pole jest wymagane."
                      },                   
                      <?php } ?>              
                      nazwa_0: {
                        required: "Pole jest wymagane."
                      },
                      plik_wyglad: {
                        required: "Pole jest wymagane."
                      }             
                    }
                  });
                });
                
                function zmien_wyglad(id) {
                    if (id == 0 || id == 2) {
                        $('#wyglad').stop().slideUp();
                       } else {
                        $('#wyglad').stop().slideDown();
                    }
                    if (id == 2) {
                        $('#naglowek_link_kont').hide();
                        $('#ModulNaglowek').stop().slideUp();                        
                        $('#naglowek_tak').prop('checked',false);
                        $('#naglowek_nie').prop('checked',true);
                    } else {
                        $('#naglowek_link_kont').show();
                        $('#ModulNaglowek').stop().slideDown();                        
                        $('#naglowek_tak').prop('checked',true);
                        $('#naglowek_nie').prop('checked',false);                        
                    }                    
                } 

                function zmien_naglowek(id) {
                    if (id == 0) {
                        $('#naglowek_link_kont').stop().slideUp();
                       } else {
                        $('#naglowek_link_kont').stop().slideDown();
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
                </script>                  
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>
                    
                    <?php if (isset($_GET['id_modul']) && (int)$_GET['id_modul'] > 0 && isset($_GET['id_strony']) && (int)$_GET['id_strony'] > 0 ) { ?>
                    <input type="hidden" name="strona" value="<?php echo (int)$_GET['id_strony']; ?>" />
                    <?php } ?>                    
                    
                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from theme_modules_description where modul_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa modułu:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" style="min-width:50%" value="<?php echo (isset($nazwa['modul_title']) ? $nazwa['modul_title'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa modułu:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" style="min-width:50%%" size="45" value="<?php echo (isset($nazwa['modul_title']) ? $nazwa['modul_title'] : ''); ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <?php if ( Wyglad::TypSzablonu() != true ) { echo '<div style="display:none">'; } ?>
                                
                                <p>
                                    <label for="nazwa_linia_2_<?php echo $w; ?>">Wiersz nr 1:</label>   
                                    <input type="text" name="nazwa_linia_2_<?php echo $w; ?>" style="min-width:50%" size="45" value="<?php echo (isset($nazwa['modul_title_line_2']) ? $nazwa['modul_title_line_2'] : ''); ?>" id="nazwa_linia_2_<?php echo $w; ?>" />
                                    <em class="TipIkona"><b>Dodatkowa nazwa wyświetlana pod głównym tytułem (wyświetlana mniejszą czcionką)</b></em>
                                </p>                                                
                                
                                <p>
                                    <label for="nazwa_linia_3_<?php echo $w; ?>">Wiersz nr 2:</label>   
                                    <input type="text" name="nazwa_linia_3_<?php echo $w; ?>" style="min-width:50%" size="45" value="<?php echo (isset($nazwa['modul_title_line_3']) ? $nazwa['modul_title_line_3'] : ''); ?>" id="nazwa_linia_3_<?php echo $w; ?>" />
                                    <em class="TipIkona"><b>Dodatkowa nazwa wyświetlana pod głównym tytułem (wyświetlana mniejszą czcionką)</b></em>
                                </p>     

                                <div class="OpisModulu">                                
                                    <label>Opis modułu:</label> 
                                    <textarea cols="80" rows="10" id="edytor_nazwa_<?php echo $w; ?>" name="opis_nazwy_<?php echo $w; ?>"><?php echo (isset($nazwa['modul_title_description']) ? $nazwa['modul_title_description'] : ''); ?></textarea>      
                                </div>   

                                <?php if ( Wyglad::TypSzablonu() != true ) { echo '</div>'; } ?>

                            </div>
                            
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                            
                            <script>
                            ckedit('edytor_nazwa_<?php echo $w; ?>','99%','100');
                            </script> 

                            <?php } ?>
                                                        
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk, $nazwa);
                        }
                        ?>                      
                    </div>   

                    <input type="hidden" value="<?php echo $info['modul_type']; ?>" name="tryb" />                    
                    
                    <p>
                      <label>Rodzaj modułu:</label>
                      <input type="radio" value="txt" name="tryb_tmp" id="rodzaj_txt" disabled="disabled" <?php echo (($info['modul_type'] == 'txt') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_txt">dowolna treść<em class="TipIkona"><b>Moduł będzie wyświetlał zawartość wpisaną w edytorze tekstu</b></em></label>
                      <input type="radio" value="strona" name="tryb_tmp" id="rodzaj_strona" disabled="disabled" <?php echo (($info['modul_type'] == 'strona') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_strona">strona informacyjna<em class="TipIkona"><b>Moduł będzie wyświetlał treść wybranej strony informacyjnej</b></em></label>                      
                      <input type="radio" value="plik" name="tryb_tmp" id="rodzaj_plik" disabled="disabled" <?php echo (($info['modul_type'] == 'plik') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_plik">plik php<em class="TipIkona"><b>Moduł będzie wyświetlał zawartość generowaną przez plik napisany w języku PHP</b></em></label>
                      <input type="radio" value="java" name="tryb_tmp" id="rodzaj_skrypt" disabled="disabled" <?php echo (($info['modul_type'] == 'java') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_skrypt">dowolny skrypt np javascript<em class="TipIkona"><b>Moduł będzie wyświetlał wynik działania skryptu</b></em></label>
                    </p>       

                    <?php if ($info['modul_type'] == 'txt') { ?>
                    
                    <div class="PoleTrybuEdytor">
                    
                        <script>
                        function zmien_edytor(nr) {
                            //
                            $('.info_tab_opis span').removeClass('a_href_info_tab_wlaczona');  
                            $('#link_' + nr + '_1').addClass('a_href_info_tab_wlaczona');
                            //
                            $('.PoleEdytora').hide();
                            $('#info_tab_id_' + nr + '_1').fadeIn();
                            //
                            for(var i in CKEDITOR.instances) {
                              if (CKEDITOR.instances[CKEDITOR.instances[i].name]) {
                                  if ( CKEDITOR.instances[i].name.indexOf('edytor_nazwa_') == -1 ) {
                                       CKEDITOR.instances[CKEDITOR.instances[i].name].destroy();
                                  }
                              }
                            }     
                            //
                            ckedit('edytor_' + nr + '_1','99%','200'); 
                        }
                        </script>
                        
                        <table class="OknoEdytoraModulBox">
                            <tr><td><label>Wpisz treść w oknie edytora:</label></td>
                            <td>
                                <div class="info_tab info_tab_opis">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    echo '<span id="link_' . $w . '_1" class="a_href_info_tab" onclick="zmien_edytor(\'' . $w . '\')">' . $ile_jezykow[$w]['text'] . '</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>                
                            
                                <div class="info_tab_content">
                                    <?php
                                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                      
                                        // pobieranie danych jezykowych
                                        $zapytanie_jezyk = "select distinct * from theme_modules_description where modul_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $opis_txt = $sqls->fetch_assoc();   
                                        ?>
                                        
                                        <div id="info_tab_id_<?php echo $w; ?>_1" class="PoleEdytora" style="display:none;">

                                            <div class="edytor">
                                                <textarea cols="80" rows="10" id="edytor_<?php echo $w; ?>_1" name="opis_<?php echo $w; ?>"><?php echo (isset($opis_txt['modul_text']) ? $opis_txt['modul_text'] : ''); ?></textarea>    
                                            </div>

                                        </div>
                                                      
                                        <?php                    
                                        $db->close_query($sqls);
                                        unset($zapytanie_jezyk, $opis_txt);
                            
                                    }                    
                                    ?>                      
                                </div>
                            </td></tr>
                            <tr>
                                <td></td><td><div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div></td>
                            </tr>
                        </table>                       

                        <script>
                        zmien_edytor(0);
                        </script>
                                
                    </div>  
                    
                    <?php } ?>
                    
                    <?php if ($info['modul_type'] == 'plik') { ?>
                    
                        <div class="PoleTrybu">
                        
                            <p>
                                <label class="required" for="plik">Nazwa pliku:</label>
                                <input type="text" name="plik" id="plik" value="<?php echo $info['modul_file']; ?>" size="40" />
                            </p>
                            
                            <?php
                            // jezeli jest plik modu
                            if (is_file('../moduly/' . $info['modul_file'])) {
                                //
                                $tytul = false;
                                $lines = file('../moduly/' . $info['modul_file']);
                                for ($i = 0, $j = count($lines); $i < $j; $i++) {
                                    //
                                    if (strpos((string)$lines[$i],'{{') > -1) {
                                        //
                                        if ( $tytul == false ) {
                                             echo '<div class="TytulDzialu">Dodatkowe ustawienia modułu</div>';
                                             $tytul = true;
                                        }
                                        //
                                        $preg = preg_match('|{{([0-9A-Za-ząćęłńóśźż _,;:-?()]+?)}}|', $lines[$i], $matches);
                                        //
                                        $PodzialOpis = explode(';',str_replace(array('{{','}}'), '', (string)$matches[0]));
                                        //
                                        echo '<p>';
                                        echo '<label for="__'.$PodzialOpis[0].'">' . $PodzialOpis[1] . ':</label>' . "\n";
                                        
                                        // pobieranie danych z settings o stalej
                                        $zapytanieDef = "select distinct code, value, limit_values from settings where code = '" . $PodzialOpis[0] . "'";
                                        $sqld = $db->open_query($zapytanieDef);
                                        
                                        if ((int)$db->ile_rekordow($sqld) > 0) {
                                            //
                                            $infd = $sqld->fetch_assoc();
                                            //
                                            if ( strpos((string)$infd['limit_values'], '::') > -1 ) {
                                            
                                                eval('$WynikFunkcji = ' . $infd['limit_values'] . ';'); 

                                                echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $WynikFunkcji, $infd['value'], 'id="__'.$PodzialOpis[0].'"');
     
                                                unset($WynikFunkcji);

                                              } else { 

                                                //
                                                $Pod = array();
                                                foreach (explode(',', (string)$infd['limit_values']) as $Wart) {
                                                    $Pod[] = array('id' => $Wart, 'text' => $Wart);
                                                }
                                                echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $Pod, $infd['value'], 'id="__'.$PodzialOpis[0].'"');
                                                unset($Pod);
                                            
                                            }
                                            
                                            unset($infd);
                                            
                                            //
                                          } else {
                                            //
                                            if ( strpos((string)$PodzialOpis[3], '::') > -1 ) {
                                            
                                                eval('$WynikFunkcji = ' . $PodzialOpis[3] . ';');

                                                echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $WynikFunkcji, $PodzialOpis[2], 'id="__'.$PodzialOpis[0].'"');

                                                unset($WynikFunkcji);

                                              } else { 
                                            
                                                $Pod = array();
                                                foreach (explode(',', (string)$PodzialOpis[3]) as $Wart) {
                                                    $Pod[] = array('id' => $Wart, 'text' => $Wart);
                                                }                                    
                                                echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $Pod, $PodzialOpis[2], 'id="__'.$PodzialOpis[0].'"');
                                                //
                                            }
                                            
                                            // jezeli nie ma stalej trzeba ja dodac do bazy
                                            if (Funkcje::czyNiePuste(strtoupper((string)$PodzialOpis[0]))) {
                                                $pola = array(array('code',strtoupper((string)$PodzialOpis[0])),
                                                              array('description',$PodzialOpis[1]),
                                                              array('type','box'),
                                                              array('value',$PodzialOpis[2]),
                                                              array('limit_values',$PodzialOpis[3]));
                                                $db->insert_query('settings' , $pola);
                                                unset($pola);
                                            }
                                            //

                                            $db->close_query($sqld); 
                                            unset($zapytanieDef); 
                                            
                                        }
                                        
                                        echo '</p>';
                                    }
                                    //
                                }
                                //
                            }
                            ?>     

                        </div>
                    
                    <?php } ?>

                    <?php if ($info['modul_type'] == 'strona') { ?>
                    
                        <div class="PoleTrybu">
                        
                            <p <?php echo ((isset($_GET['id_modul'])) ? 'style="display:none"' : ''); ?>>
                                <label for="stronainfo">Wybierz stronę informacyjną:</label>
                                <?php
                                // pobieranie danych o stronach informacyjnych
                                $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and (p.pages_modul = '0' or p.pages_id = '" . (int)$info['modul_pages_id'] . "')";
                                $sqls = $db->open_query($zapytanie_tmp);
                                //
                                $tablica = array();
                                while ($infs = $sqls->fetch_assoc()) { 
                                    $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                                }
                                $db->close_query($sqls); 
                                unset($zapytanie_tmp, $infs);    
                                //                          
                                echo Funkcje::RozwijaneMenu('stronainfo', $tablica, $info['modul_pages_id'], 'id="stronainfo"'); 
                                unset($tablica);
                                ?>
                                
                                <input name="poprzednie_id" type="hidden" value="<?php echo $info['modul_pages_id']; ?>" />
                                
                            </p>
                            
                        </div>
                    
                    <?php } ?>

                    <?php if ($info['modul_type'] == 'java') { ?>
                    
                        <div class="PoleTrybu">
                        
                            <table class="OknoTextareaModulBox">
                                <tr><td><label>Wstaw kod:</label></td>
                                <td class="TrybEdytor">
                                    <textarea cols="120" rows="15" name="kod" id="kod"><?php echo htmlspecialchars(html_entity_decode((string)$info['modul_code'])); ?></textarea>
                                </td></tr>
                            </table> 
                            
                        </div>  

                    <?php } ?>
                    
                    <div id="ModulNaglowek" <?php echo (($info['modul_theme'] == 2) ? 'style="display:none;margin-top:15px"' : 'style="margin-top:15px"'); ?>>
                    
                        <div class="TytulDzialu" style="margin-top:0px">Nagłówek modułu</div>
                        
                        <p>
                            <label>Nagłówek modułu:</label>
                            <input type="radio" value="1" name="naglowek" id="naglowek_tak" <?php echo (($info['modul_header'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_tak" onclick="zmien_naglowek(1)">tak<em class="TipIkona"><b>W module będzie wyświetał się nagłówek z nazwą modułu</b></em></label>
                            <input type="radio" value="0" name="naglowek" id="naglowek_nie" <?php echo (($info['modul_header'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_nie" onclick="zmien_naglowek(0)">nie<em class="TipIkona"><b>W module nie będzie się wyświetał nagłówek z nazwą modułu - tylko sama treść"</b></em></label>
                        </p>
                        
                        <p id="naglowek_link_kont" <?php echo (($info['modul_header'] == 0) ? 'style="display:none"' : ''); ?>>
                            <label for="naglowek_link">Link nagłówka modułu:</label>
                            <input type="text" name="naglowek_link" id="naglowek_link" value="<?php echo $info['modul_header_link']; ?>" size="50" /><em class="TipIkona"><b>Adres do jakiego ma prowadzić nagłówek modułu - np nowosci.html</b></em>
                        </p> 
                    
                    </div>
                    
                    <?php if ( Wyglad::TypSzablonu() != true ) { echo '<div style="display:none">'; } ?>
                    
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
                        <label for="szerokosc_modulu">Szerokość modułu:</label>
                        <?php
                        $tablica = array( array('id' => 'szerokosc_sklep', 'text' => 'na szerokość sklepu'),
                                          array('id' => 'szerokosc_sto', 'text' => 'na 100% szerokości ekranu') );
                                          
                        echo Funkcje::RozwijaneMenu('szerokosc_modul', $tablica, $info['modul_width'], ' id="szerokosc_modulu" onchange="zmiana_szerokosci_modulu(this.value)"');
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
                        <input type="radio" value="brak" name="tlo_modulu" id="tlo_modulu_brak" onclick="zmien_tlo_modulu(0)" <?php echo (($info['modul_background_type'] == 'brak') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_modulu_brak">brak<em class="TipIkona"><b>Moduł będzie miał tło jak reszta sklepu</b></em></label>
                        <input type="radio" value="kolor" name="tlo_modulu" id="tlo_modulu_jednolity" onclick="zmien_tlo_modulu(1)" <?php echo (($info['modul_background_type'] == 'kolor') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_modulu_jednolity">jednolity kolor</label>
                        <input type="radio" value="obrazek" name="tlo_modulu" id="tlo_modulu_obrazek" onclick="zmien_tlo_modulu(2)" <?php echo (($info['modul_background_type'] == 'obrazek') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_modulu_obrazek">tło obrazkowe</label>
                    </p>

                    <div id="tlo_modulu_1" <?php echo (($info['modul_background_type'] != 'kolor') ? ' style="display:none"' : ''); ?>>
                    
                        <p>
                          <label for="color">Kolor:</label>
                          <input name="kolor" class="color {required:false}" id="color" style="-moz-box-shadow:none" value="<?php echo (($info['modul_background_type'] == 'kolor') ? $info['modul_background_value'] : ''); ?>" onchange="zmienGet(this.value,'tlo_modulu')" size="8" />                    
                        </p>
                        
                    </div>
                    
                    <div id="tlo_modulu_2" <?php echo (($info['modul_background_type'] != 'obrazek') ? ' style="display:none"' : ''); ?>>
                    
                        <p>
                          <label for="tlo_obrazkowe">Ścieżka zdjęcia:</label>           
                          <input type="text" name="tlo_obrazkowe" size="95" value="<?php echo (($info['modul_background_type'] == 'obrazek') ? $info['modul_background_value'] : ''); ?>" class="obrazek" ondblclick="openFileBrowser('tlo_obrazkowe','','<?php echo KATALOG_ZDJEC; ?>')" id="tlo_obrazkowe" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
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
                                            
                          echo Funkcje::RozwijaneMenu('tlo_powtarzanie', $tablica, $info['modul_background_repeat'], ' id="tlo_powtarzanie"');
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
                                            
                          echo Funkcje::RozwijaneMenu('tlo_skalowanie', $tablica, $info['modul_background_image_width'], ' id="tlo_skalowanie"');
                          unset($tablica);
                          ?>                              
                        </p>               

                        <p>
                          <label for="tlo_przewijanie">Sposób wyświetlania tła:</label>
                          <?php
                          $tablica = array( array('id' => 'scroll', 'text' => 'tła przewijane razem z oknem przeglądarki'),
                                            array('id' => 'fixed', 'text' => 'tło nieruchome względem okna przeglądarki') );
                                            
                          echo Funkcje::RozwijaneMenu('tlo_przewijanie', $tablica, $info['modul_background_attachment'], ' id="tlo_przewijanie"');
                          unset($tablica);
                          ?>                              
                        </p>                               
                        
                    </div>                    
                    
                    <div class="SzerokoscModulu" id="tlo_modulu_rodzaj" <?php echo (($info['modul_background_type'] == 'brak') ? ' style="display:none"' : ''); ?>>
                        <p>
                            <label for="tlo_szerokosc">Tło modułu szerokość:</label>
                            <?php
                            $tablica = array( array('id' => 'szerokosc_sklep', 'text' => 'na szerokość sklepu'),
                                              array('id' => 'szerokosc_sto', 'text' => 'na 100% szerokości ekranu') );
                                              
                            echo Funkcje::RozwijaneMenu('tlo_szerokosc', $tablica, $info['modul_background_width'], ' id="tlo_szerokosc"');
                            unset($tablica);
                            ?>
                            <span class="maleInfo">100% szerokości ekranu będzie wyświetlane tylko w sklepach z wyłączoną lewą i prawą kolumną z boxami - aktywna tylko kolumna środkowa z modułami</span>
                        </p>                   
                    </div>
                    
                    <script> 
                    $(document).ready(function() {                     
                        if ($('#szerokosc_modulu option:selected').val() == 'szerokosc_sto') {
                            $('#tlo_szerokosc option[value=szerokosc_sklep]').attr('disabled','disabled');
                        } else {
                            $('#tlo_szerokosc option[value=szerokosc_sklep]').prop('disabled',false);                        
                        }
                    });                     
                    </script> 

                    <table class="MarginesyOdstep">
                        <tr><td><label>Dodatkowe marginesy:</label></td>
                        <td>
                            <div class="TloMarginesy">
                                <div class="MargGora"><input type="number" min="0" max="150" name="margines_gorny" class="zero" id="margines_gorny" value="<?php echo $info['modul_margines_top']; ?>" size="4" /> px</div>
                                <div class="MargDol"><input type="number" min="0" max="150" name="margines_dolny" class="zero" id="margines_dolny" value="<?php echo $info['modul_margines_bottom']; ?>" size="4" /> px</div>
                                <div class="MargLewy"><input type="number" min="0" max="150" name="margines_lewy" class="zero" id="margines_lewy" value="<?php echo $info['modul_margines_left']; ?>" size="4" /> px</div>
                                <div class="MargPrawy"><input type="number" min="0" max="150" name="margines_prawy" class="zero" id="margines_prawy" value="<?php echo $info['modul_margines_right']; ?>" size="4" /> px</div>
                            </div>
                        </td></tr>
                    </table>   
                        
                    <table class="MarginesyOdstep">
                        <tr><td><label>Dodatkowe odstępy:</label></td>
                        <td>
                            <div class="TloOdstep">
                                <div class="MargGora"><input type="number" min="0" max="150" name="odstep_gorny" class="zero" id="odstep_gorny" value="<?php echo $info['modul_padding_top']; ?>" size="4" /> px</div>
                                <div class="MargDol"><input type="number" min="0" max="150" name="odstep_dolny" class="zero" id="odstep_dolny" value="<?php echo $info['modul_padding_bottom']; ?>" size="4" /> px</div>
                                <div class="MargLewy"><input type="number" min="0" max="150" name="odstep_lewy" class="zero" id="odstep_lewy" value="<?php echo $info['modul_padding_left']; ?>" size="4" /> px</div>
                                <div class="MargPrawy"><input type="number" min="0" max="150" name="odstep_prawy" class="zero" id="odstep_prawy" value="<?php echo $info['modul_padding_right']; ?>" size="4" /> px</div>
                            </div>
                        </td></tr>
                    </table>   
                    
                    <?php if ( Wyglad::TypSzablonu() != true ) { echo '</div>'; } ?>

                    <div class="TytulDzialu">Ustawienia wyglądu i miejsca wyświetlania modułu</div>

                    <p>
                        <label>Wygląd modułu:</label>
                        <input type="radio" value="0" name="modul_wyglad" id="szablon_standardowy" onclick="zmien_wyglad(0)" <?php echo (($info['modul_theme'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_standardowy">standardowy<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w standardowym wyglądzie</b></em></label>
                        <input type="radio" value="1" name="modul_wyglad" id="szablon_indywidualny" onclick="zmien_wyglad(1)" <?php echo (($info['modul_theme'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_indywidualny">indywidualny<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w indywidualnym wyglądzie</b></em></label>
                        <input type="radio" value="2" name="modul_wyglad" id="szablon_brak" onclick="zmien_wyglad(2)" <?php echo (($info['modul_theme'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_brak">sama treść<em class="TipIkona"><b>Bezpośrednie wyświetlanie ustawionej treści - bez żadnych dodatkowych elementów (tytułu, ramki etc)</b></em></label>
                    </p>   

                    <div id="wyglad" <?php echo (($info['modul_theme'] == 1) ? '' : 'style="display:none"'); ?>>
                        <p>
                            <label class="required" for="plik_wyglad">Nazwa pliku w szablonie:</label>
                            <input type="text" name="plik_wyglad" id="plik_wyglad" value="<?php echo $info['modul_theme_file']; ?>" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd w szablonie np. moj_modul.tp (w katalogu /moduly_wyglad w szablonie)</b></em>
                        </p>
                    </div>   

                    <p>
                        <label>Wyświetlanie modułu:</label>
                        <input type="radio" value="1" name="polozenie" id="polozenie_wszystkie" onclick="zmien_polozenie(1)" <?php echo (($info['modul_localization'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_wszystkie">wszystkie strony<em class="TipIkona"><b>Moduł będzie wyświetlany na wszystkich stronach</b></em></label>
                        <input type="radio" value="3" name="polozenie" id="polozenie_podstrony" onclick="zmien_polozenie(3)" <?php echo (($info['modul_localization'] == 3) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_podstrony">tylko podstrony<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na podstronach (bez strony głównej)</b></em></label>
                        <input type="radio" value="4" name="polozenie" id="polozenie_linki" onclick="zmien_polozenie(4)" <?php echo (($info['modul_localization'] == 4) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_linki">tylko podstrony wg linków<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na określonych podstronach wg podanych linków (bez strony głównej)</b></em></label>
                        <input type="radio" value="2" name="polozenie" id="polozenie_glowna" onclick="zmien_polozenie(2)" <?php echo (($info['modul_localization'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_glowna">tylko strona główna<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na stronie głównej sklepu</b></em></label>
                    </p>         

                    <div id="lista_podstron" <?php echo (($info['modul_localization'] != 3) ? 'style="display:none"' : ''); ?>>                    
                    
                        <table class="WyborStrony" style="margin-top:10px;margin-bottom:0px">
                            <tr><td><label>Wybierz strony na których ma się wyświetlać moduł:</label></td>
                            <td>
                                <div>
                                <?php
                                $ZapisaneStrony = explode(';', (string)$info['modul_localization_site']);
                                //
                                foreach ( Funkcje::TablicaPodstronSklepu(true) as $Strona => $Nazwa ) {
                                    //
                                    echo '<input type="checkbox" value="' . $Strona . '" name="strony[]" id="' . $Strona . '" ' . ((in_array((string)$Strona, $ZapisaneStrony)) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="' . $Strona . '">' . $Nazwa . '</label><br />';
                                    //
                                }
                                //
                                unset($ZapisaneStrony);
                                ?>
                                </div>
                                <span class="maleInfo" style="margin:5px 0px 0px 7px">Jeżeli nie zostanie wybrana żadna podstrona moduł będzie wyświetlany na wszystkich podstronach</span>
                            </td></tr>
                        </table>                    
                    
                    </div>

                    <div id="lista_linki" <?php echo (($info['modul_localization'] != 4) ? 'style="display:none"' : ''); ?>>          
                    
                        <table class="WyborStrony" style="margin-top:10px;margin-bottom:0px">
                            <tr><td><label>Podaj adresy stron na jakich ma wyświetlać się moduł:</label></td>
                            <td>
                                <textarea name="linki_strony" id="linki_strony" rows="10" cols="100"><?php echo $info['modul_localization_link']; ?></textarea>
                                <span class="maleInfo" style="margin:5px 0px 0px 7px">Adresy trzeba wpisywać każdy w osobnej linii - bez adresu sklepu. Tylko sam link np. adres-strony-p-1.html <br /> Jeżeli nie zostanie wpisany żaden adres moduł będzie wyświetlany na wszystkich podstronach</span>
                            </td></tr>
                        </table>                    
                    
                    </div>    
                  
                    <div id="podstrony" class="PodstronyWyswietlanie" <?php echo (($info['modul_localization'] == 2) ? 'style="display:none"' : ''); ?>>
                        <p>
                            <label>W którym miejscu wyświetlać moduł na podstronach sklepu ?</label>
                            <input type="radio" value="gora" name="modul_podstrony_polozenie" id="modul_podstrony_polozenie_gora" <?php echo (($info['modul_localization_position'] == 'gora') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="modul_podstrony_polozenie_gora">w części górnej sklepu nad główną treścią<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w części górnej sklepu</b></em></label>
                            <input type="radio" value="dol" name="modul_podstrony_polozenie" id="modul_podstrony_polozenie_dol" <?php echo (($info['modul_localization_position'] == 'dol') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="modul_podstrony_polozenie_dol">w części dolnej sklepu pod główną treścią<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w części dolnej sklepu</b></em></label>
                            <span class="maleInfo">Powyższe ustawienie jest używane jeżeli moduł jest wyświetlany na podstronach sklepu i jest dodany w menu Wygląd / Ustawienia wyglądu / Moduły środkowe - do wyświetlania w <b>części głównej sklepu</b> (w części gdzie są wyświetlane boxy)</span>
                        </p>                       
                    </div>      

                    <div class="TytulDzialu">Opis modułu (informacja tylko dla administratora sklepu)</div>

                    <p>
                        <label for="opis">Opis modułu:</label>
                        <textarea name="opis" id="opis" rows="2" cols="70"><?php echo $info['modul_description']; ?></textarea><em class="TipIkona"><b>Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                    </p>

                    <p>
                        <label for="wyswietla">Co wyświetla ?</label>
                        <input name="wyswietla" id="wyswietla" type="text" size="40" value="<?php echo $info['modul_display']; ?>" /><em class="TipIkona"><b>Co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                    </p> 

                    <div class="TytulDzialu"><div class="TytulRwd">Ustawienia dodatkowe</div></div>
      
                    <p>
                        <label>Czy moduł działa w szablonach wersja v2 ?</label>
                        <input type="radio" value="0" name="v2" id="v2_nie" <?php echo (($info['modul_v2'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="v2_nie">nie<em class="TipIkona"><b>Moduł nie jest przystosowany do wyświetlania w szablonach wersja v2</b></em></label>
                        <input type="radio" value="1" name="v2" id="v2_tak" <?php echo (($info['modul_v2'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="v2_tak">tak<em class="TipIkona"><b>Moduł jest przystosowany do wyświetlania w szablonach wersja v2</b></em></label>
                        <input type="radio" value="2" name="v2" id="v2_wszystkie" <?php echo (($info['modul_v2'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="v2_wszystkie">tak - w każdym szablonie<em class="TipIkona"><b>Moduł jest przystosowany do wyświetlania we wszystkich wersjach szablonów</b></em></label>
                    </p>    

                    <p>
                        <label>Wygląd modułu przy małych rozdzielczościach (na urządzeniach mobilnych):</label>
                        <input type="radio" value="0" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_bezzmian" <?php echo (($info['modul_rwd_resolution'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_bezzmian">bez zmian<em class="TipIkona"><b>Moduł będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                        <input type="radio" value="1" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_ukrywanie" <?php echo (($info['modul_rwd_resolution'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_ukrywanie">ma być <u>niewidoczny</u> na urządzeniach mobilnych<em class="TipIkona"><b>Moduł nie będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                        <input type="radio" value="2" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_widoczny" <?php echo (($info['modul_rwd_resolution'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_widoczny">ma być <u>widoczny</u> tylko na urządzeniach mobilnych<em class="TipIkona"><b>Moduł będzie widoczny tylko przy małych rozdzielczościach ekranu</b></em></label>
                    </p>

                    <script>
                    gold_tabs('0');
                    </script>                         
                
                </div>

                <div class="przyciski_dolne">
                  
                  <input type="hidden" name="powrot" id="powrot" value="0" />
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />
                
                  <?php 
                  // jezeli jest get zakladka wraca do ustawien wygladu
                  if (isset($_GET['zakladka']) ) { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } else if (isset($_GET['id_modul']) && isset($_GET['id_strony']) ) { 
                  
                  $_GET['id_poz'] = $_GET['id_strony'];
                  ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button> 
                  
                  <?php } else { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('srodek','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } ?>
                  
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div> 
          
       </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
