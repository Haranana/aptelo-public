<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('modul_status','0'),
                array('modul_type',$filtr->process($_POST['tryb'])),
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
        
        $sql = $db->insert_query('theme_modules' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
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
                    array('modul_id',$id_dodanej_pozycji),
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
            $pola = array( array('pages_modul',1));
            $db->update_query('pages' , $pola, 'pages_id = ' . $filtr->process($_POST['stronainfo']));
            //
            // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
            Funkcje::UsuwanieWygladu('strona',$filtr->process($_POST["stronainfo"]));
            //            
        }

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
          
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('srodek_edytuj.php?id_poz='.$id_dodanej_pozycji);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('srodek.php?id_poz='.$id_dodanej_pozycji);
                //
            }             

        } else {
          
            Funkcje::PrzekierowanieURL('srodek.php');
            
        }              
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    
    <div id="cont">
          
      <script>
      $(document).ready(function() {
        $("#modForm").validate({
          rules: {
            nazwa_0: {
              required: true
            },
            plik: {
              required: function(element) {
                if ($("#tryb_0").css('display') == 'block') {
                    return true;
                  } else {
                    return false;
                }
              }
            }              
          },
          messages: {
            nazwa_0: {
              required: "Pole jest wymagane."
            },
            plik: {
              required: "Pole jest wymagane."
            }                
          }
        });
      });
      
      function zmien_tryb(id) {
        if ($('#tryb_' + id).css('display') == 'none') {
            $('#tryb_0').css('display','none'); 
            $('#tryb_1').css('display','none');
            $('#tryb_2').css('display','none');
            $('#tryb_3').css('display','none');
            //
            for(var i in CKEDITOR.instances) {
                if (CKEDITOR.instances[CKEDITOR.instances[i].name]) {
                    if ( CKEDITOR.instances[i].name.indexOf('edytor_nazwa_') == -1 ) {
                         CKEDITOR.instances[CKEDITOR.instances[i].name].destroy();
                    }
                }
            }
            //
            if ( id == 3 ) {
                 zmien_edytor(0);
            }
            //
            $('#tryb_' + id).stop().slideDown();
        }
      }  

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
      
      <script type="text/javascript" src="programy/jscolor/jscolor.js"></script>

      <form action="wyglad/srodek_dodaj.php" method="post" id="modForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
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
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required" for="nazwa_0">Nazwa modułu:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" style="min-width:50%" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa modułu:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" style="min-width:50%" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p> 
                            
                            <?php if ( Wyglad::TypSzablonu() != true ) { echo '<div style="display:none">'; } ?>
                            
                            <p>
                                <label for="nazwa_linia_2_<?php echo $w; ?>">Wiersz nr 1:</label>   
                                <input type="text" name="nazwa_linia_2_<?php echo $w; ?>" style="min-width:50%" size="45" value="" id="nazwa_linia_2_<?php echo $w; ?>" />
                                <em class="TipIkona"><b>Dodatkowa nazwa wyświetlana pod głównym tytułem (wyświetlana mniejszą czcionką)</b></em>
                            </p>                                                
                            
                            <p>
                                <label for="nazwa_linia_3_<?php echo $w; ?>">Wiersz nr 2:</label>   
                                <input type="text" name="nazwa_linia_3_<?php echo $w; ?>" style="min-width:50%" size="45" value="" id="nazwa_linia_3_<?php echo $w; ?>" />
                                <em class="TipIkona"><b>Dodatkowa nazwa wyświetlana pod głównym tytułem (wyświetlana mniejszą czcionką)</b></em>
                            </p>     

                            <div class="OpisModulu">                                
                                <label>Opis modułu:</label> 
                                <textarea cols="80" rows="10" id="edytor_nazwa_<?php echo $w; ?>" name="opis_nazwy_<?php echo $w; ?>"></textarea>      
                            </div>   
                            
                            <?php if ( Wyglad::TypSzablonu() != true ) { echo '</div>'; } ?>

                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                            
                            <script>
                            ckedit('edytor_nazwa_<?php echo $w; ?>','99%','100');
                            </script>                               
                            
                            <?php } ?>
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>                
            
                <p>
                  <label>Rodzaj modułu:</label>
                  
                  <input type="radio" value="txt" name="tryb" id="rodzaj_txt" onclick="zmien_tryb(3)" checked="checked" /><label class="OpisFor" for="rodzaj_txt">dowolna treść<em class="TipIkona"><b>Moduł będzie wyświetlał zawartość wpisaną w edytorze tekstu</b></em></label>
                  
                  <?php
                  // pobieranie danych o stronach informacyjnych - sprawdza ile jest stron - jak nie ma to nie wyswietli nic
                  $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and p.pages_modul = '0'";
                  $sqls = $db->open_query($zapytanie_tmp); 
                  
                  if ((int)$db->ile_rekordow($sqls) > 0) {
                  ?>
                  <input type="radio" value="strona" name="tryb" id="rodzaj_strona" onclick="zmien_tryb(1)" /><label class="OpisFor" for="rodzaj_strona">strona informacyjna<em class="TipIkona"><b>Moduł będzie wyświetlał treść wybranej strony informacyjnej</b></em></label>
                  <?php
                  }
                  $db->close_query($sqls);
                  unset($zapytanie_tmp);                   
                  ?>
                                                      
                  <input type="radio" value="plik" name="tryb" id="rodzaj_plik" onclick="zmien_tryb(0)" /><label class="OpisFor" for="rodzaj_plik">plik php<em class="TipIkona"><b>Moduł będzie wyświetlał zawartość generowaną przez plik napisany w języku PHP</b></em></label>
                  <input type="radio" value="java" name="tryb" id="rodzaj_skrypt" onclick="zmien_tryb(2)" /><label class="OpisFor" for="rodzaj_skrypt">dowolny skrypt np javascript<em class="TipIkona"><b>Moduł będzie wyświetlał wynik działania skryptu</b></em></label>
                  
                </p> 
                
                <div id="tryb_3" class="PoleTrybuEdytor">
                
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
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>_1" class="PoleEdytora" style="display:none;">

                                        <div class="edytor">
                                            <textarea cols="80" rows="10" id="edytor_<?php echo $w; ?>_1" name="opis_<?php echo $w; ?>"></textarea>    
                                        </div>

                                    </div>
                                    <?php                    
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

                <div id="tryb_0" style="display:none" class="PoleTrybu">
                    <p>
                        <label class="required" for="plik">Nazwa pliku:</label>
                        <input type="text" name="plik" id="plik" value="" size="40" />
                    </p>               
                </div>

                <div id="tryb_1" style="display:none" class="PoleTrybu">
                    <p>
                        <label for="stronainfo">Wybierz stronę informacyjną:</label>
                        <?php
                        // pobieranie danych o stronach informacyjnych
                        $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and p.pages_modul = '0'";
                        $sqls = $db->open_query($zapytanie_tmp);
                        //
                        $tablica = array();
                        while ($infs = $sqls->fetch_assoc()) { 
                            $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                        }
                        $db->close_query($sqls); 
                        unset($zapytanie_tmp, $infs);    
                        //                          
                        echo Funkcje::RozwijaneMenu('stronainfo', $tablica, '', 'id="stronainfo"'); 
                        unset($tablica);
                        ?>
                    </p>
                </div>

                <div id="tryb_2" style="display:none" class="PoleTrybu">
                    <table class="OknoTextareaModulBox">
                        <tr><td><label>Wstaw kod:</label></td>
                        <td class="TrybEdytor">
                            <textarea cols="120" rows="15" name="kod" id="kod"></textarea>
                        </td></tr>
                    </table>                    
                </div>      

                <div id="ModulNaglowek" style="margin-top:15px">
                
                    <div class="TytulDzialu" style="margin-top:0px">Nagłówek modułu</div>

                    <p>
                        <label>Nagłówek modułu:</label>
                        <input type="radio" value="1" name="naglowek" id="naglowek_tak" checked="checked" /><label class="OpisFor" for="naglowek_tak" onclick="zmien_naglowek(1)">tak<em class="TipIkona"><b>W module będzie wyświetał się nagłówek z nazwą modułu</b></em></label>
                        <input type="radio" value="0" name="naglowek" id="naglowek_nie" /><label class="OpisFor" for="naglowek_nie" onclick="zmien_naglowek(0)">nie<em class="TipIkona"><b>W module nie będzie się wyświetał nagłówek z nazwą modułu - tylko sama treść</b></em></label>
                    </p>
                
                    <p id="naglowek_link_kont">
                        <label for="naglowek_link">Link nagłówka modułu:</label>
                        <input type="text" name="naglowek_link" id="naglowek_link" value="" size="50" /><em class="TipIkona"><b>Adres do jakiego ma prowadzić nagłówek modułu - np nowosci.html</b></em>
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
                                      
                    echo Funkcje::RozwijaneMenu('szerokosc_modul', $tablica, '', ' id="szerokosc_modulu" onchange="zmiana_szerokosci_modulu(this.value)"');
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
                    <input type="radio" value="brak" name="tlo_modulu" id="tlo_modulu_brak" onclick="zmien_tlo_modulu(0)" checked="checked" /> <label class="OpisFor" for="tlo_modulu_brak">brak<em class="TipIkona"><b>Moduł będzie miał tło jak reszta sklepu</b></em></label>
                    <input type="radio" value="kolor" name="tlo_modulu" id="tlo_modulu_jednolity" onclick="zmien_tlo_modulu(1)" /> <label class="OpisFor" for="tlo_modulu_jednolity">jednolity kolor</label>
                    <input type="radio" value="obrazek" name="tlo_modulu" id="tlo_modulu_obrazek" onclick="zmien_tlo_modulu(2)" /> <label class="OpisFor" for="tlo_modulu_obrazek">tło obrazkowe</label>
                </p>

                <div id="tlo_modulu_1" style="display:none">
                
                    <p>
                      <label for="color">Kolor:</label>
                      <input name="kolor" class="color {required:false}" id="color" style="-moz-box-shadow:none" value="" onchange="zmienGet(this.value,'tlo_modulu')" size="8" />                    
                    </p>
                    
                </div>
                
                <div id="tlo_modulu_2" style="display:none">
                
                    <p>
                      <label for="tlo_obrazkowe">Ścieżka zdjęcia:</label>           
                      <input type="text" name="tlo_obrazkowe" size="95" value="" class="obrazek" ondblclick="openFileBrowser('tlo_obrazkowe','','<?php echo KATALOG_ZDJEC; ?>')" id="tlo_obrazkowe" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
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
                                        
                      echo Funkcje::RozwijaneMenu('tlo_powtarzanie', $tablica, '', ' id="tlo_powtarzanie"');
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
                                        
                      echo Funkcje::RozwijaneMenu('tlo_skalowanie', $tablica, '', ' id="tlo_skalowanie"');
                      unset($tablica);
                      ?>                              
                    </p>                          
                    
                    <p>
                      <label for="tlo_przewijanie">Sposób wyświetlania tła:</label>
                      <?php
                      $tablica = array( array('id' => 'scroll', 'text' => 'tła przewijane razem z oknem przeglądarki'),
                                        array('id' => 'fixed', 'text' => 'tło nieruchome względem okna przeglądarki') );
                                        
                      echo Funkcje::RozwijaneMenu('tlo_przewijanie', $tablica, '', ' id="tlo_przewijanie"');
                      unset($tablica);
                      ?>                              
                    </p>                           
                    
                </div>                    
                
                <div class="SzerokoscModulu" id="tlo_modulu_rodzaj" style="display:none">
                    <p>
                        <label for="tlo_szerokosc">Tło modułu szerokość:</label>
                        <?php
                        $tablica = array( array('id' => 'szerokosc_sklep', 'text' => 'na szerokość sklepu'),
                                          array('id' => 'szerokosc_sto', 'text' => 'na 100% szerokości ekranu') );
                                          
                        echo Funkcje::RozwijaneMenu('tlo_szerokosc', $tablica, '', ' id="tlo_szerokosc"');
                        unset($tablica);
                        ?>
                        <span class="maleInfo">100% szerokości ekranu będzie wyświetlane tylko w sklepach z wyłączoną lewą i prawą kolumną z boxami - aktywna tylko kolumna środkowa z modułami</span>
                    </p>                   
                </div>

                <table class="MarginesyOdstep">
                    <tr><td><label>Dodatkowe marginesy:</label></td>
                    <td>
                        <div class="TloMarginesy">
                            <div class="MargGora"><input type="number" min="0" max="150" name="margines_gorny" class="zero" id="margines_gorny" value="0" size="4" /> px</div>
                            <div class="MargDol"><input type="number" min="0" max="150" name="margines_dolny" class="zero" id="margines_dolny" value="0" size="4" /> px</div>
                            <div class="MargLewy"><input type="number" min="0" max="150" name="margines_lewy" class="zero" id="margines_lewy" value="0" size="4" /> px</div>
                            <div class="MargPrawy"><input type="number" min="0" max="150" name="margines_prawy" class="zero" id="margines_prawy" value="0" size="4" /> px</div>
                        </div>
                    </td></tr>
                </table>   
                    
                <table class="MarginesyOdstep">
                    <tr><td><label>Dodatkowe odstępy:</label></td>
                    <td>
                        <div class="TloOdstep">
                            <div class="MargGora"><input type="number" min="0" max="150" name="odstep_gorny" class="zero" id="odstep_gorny" value="0" size="4" /> px</div>
                            <div class="MargDol"><input type="number" min="0" max="150" name="odstep_dolny" class="zero" id="odstep_dolny" value="0" size="4" /> px</div>
                            <div class="MargLewy"><input type="number" min="0" max="150" name="odstep_lewy" class="zero" id="odstep_lewy" value="0" size="4" /> px</div>
                            <div class="MargPrawy"><input type="number" min="0" max="150" name="odstep_prawy" class="zero" id="odstep_prawy" value="0" size="4" /> px</div>
                        </div>
                    </td></tr>
                </table>   
                
                <?php if ( Wyglad::TypSzablonu() != true ) { echo '</div>'; } ?>

                <div class="TytulDzialu">Ustawienia wyglądu i miejsca wyświetlania modułu</div>

                <p>
                    <label>Wygląd modułu:</label>
                    <input type="radio" value="0" name="modul_wyglad" id="szablon_standardowy" onclick="zmien_wyglad(0)" checked="checked" /><label class="OpisFor" for="szablon_standardowy">standardowy<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w standardowym wyglądzie</b></em></label>
                    <input type="radio" value="1" name="modul_wyglad" id="szablon_indywidualny" onclick="zmien_wyglad(1)" /><label class="OpisFor" for="szablon_indywidualny">indywidualny<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w indywidualnym wyglądzie</b></em></label>
                    <input type="radio" value="2" name="modul_wyglad" id="szablon_brak" onclick="zmien_wyglad(2)" /><label class="OpisFor" for="szablon_brak">sama treść<em class="TipIkona"><b>Bezpośrednie wyświetlanie ustawionej treści - bez żadnych dodatkowych elementów (tytułu, ramki etc)</b></em></label>
                </p>   

                <div id="wyglad" style="display:none">
                    <p>
                        <label class="required" for="plik_wyglad">Nazwa pliku w szablonie:</label>
                        <input type="text" name="plik_wyglad" id="plik_wyglad" value="" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd w szablonie np. moj_modul.tp"</b></em>
                    </p>
                </div> 

                <p>
                    <label>Wyświetlanie modułu:</label>
                    <input type="radio" value="1" name="polozenie" id="polozenie_wszystkie" onclick="zmien_polozenie(1)" /><label class="OpisFor" for="polozenie_wszystkie">wszystkie strony<em class="TipIkona"><b>Moduł będzie wyświetlany na wszystkich stronach</b></em></label>
                    <input type="radio" value="3" name="polozenie" id="polozenie_podstrony" onclick="zmien_polozenie(3)" /><label class="OpisFor" for="polozenie_podstrony">tylko podstrony<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na podstronach (bez strony głównej)</b></em></label>
                    <input type="radio" value="4" name="polozenie" id="polozenie_linki" onclick="zmien_polozenie(4)" /><label class="OpisFor" for="polozenie_linki">tylko podstrony wg linków<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na określonych podstronach wg podanych linków (bez strony głównej)</b></em></label>
                    <input type="radio" value="2" name="polozenie" id="polozenie_glowna" onclick="zmien_polozenie(2)" checked="checked" /><label class="OpisFor" for="polozenie_glowna">tylko strona główna<em class="TipIkona"><b>Moduł będzie wyświetlany tylko na stronie głównej sklepu</b></em></label>
                </p>                   

                <div id="lista_podstron" style="display:none">                    
                
                    <table class="WyborStrony" style="margin-top:10px;margin-bottom:0px">
                        <tr><td><label>Wybierz strony na których ma się wyświetlać moduł:</label></td>
                        <td>
                            <div>
                            <?php
                            //
                            foreach ( Funkcje::TablicaPodstronSklepu(true) as $Strona => $Nazwa ) {
                                //
                                echo '<input type="checkbox" value="' . $Strona . '" name="strony[]" id="' . $Strona . '" /><label class="OpisFor" for="' . $Strona . '">' . $Nazwa . '</label><br />';
                                //
                            }
                            //
                            ?>
                            </div>
                            <span class="maleInfo" style="margin:5px 0px 0px 7px">Jeżeli nie zostanie wybrana żadna podstrona moduł będzie wyświetlany na wszystkich podstronach</span>
                        </td></tr>
                    </table>                    
                
                </div>             

                <div id="lista_linki" style="display:none">                    
                
                    <table class="WyborStrony" style="margin-top:10px;margin-bottom:0px">
                        <tr><td><label>Podaj adresy stron na jakich ma wyświetlać się moduł:</label></td>
                        <td>
                            <textarea name="linki_strony" id="linki_strony" rows="10" cols="100"></textarea>
                            <span class="maleInfo" style="margin:5px 0px 0px 7px">Adresy trzeba wpisywać każdy w osobnej linii - bez adresu sklepu. Tylko sam link np. adres-strony-p-1.html <br /> Jeżeli nie zostanie wpisany żaden adres moduł będzie wyświetlany na wszystkich podstronach</span>
                        </td></tr>
                    </table>                    
                
                </div>                    

                <div id="podstrony" class="PodstronyWyswietlanie" style="display:none">
                    <p>
                        <label>W którym miejscu wyświetlać moduł na podstronach sklepu ?</label>
                        <input type="radio" value="gora" name="modul_podstrony_polozenie" id="modul_podstrony_polozenie_gora" checked="checked" /><label class="OpisFor" for="modul_podstrony_polozenie_gora">w części górnej sklepu nad główną treścią<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w części górnej sklepu</b></em></label>
                        <input type="radio" value="dol" name="modul_podstrony_polozenie" id="modul_podstrony_polozenie_dol" /><label class="OpisFor" for="modul_podstrony_polozenie_dol">w części dolnej sklepu pod główną treścią<em class="TipIkona"><b>Zawartość modułu będzie wyświetlana w części dolnej sklepu</b></em></label>
                        <span class="maleInfo">Powyższe ustawienie jest używane jeżeli moduł jest wyświetlany na podstronach sklepu i jest dodany w menu Wygląd / Ustawienia wyglądu / Moduły środkowe - do wyświetlania w <b>części głównej sklepu</b> (w części gdzie są wyświetlane boxy)</span>
                    </p>                       
                </div>        

                <div class="TytulDzialu">Opis modułu (informacja tylko dla administratora sklepu)</div>

                <p>
                    <label for="opis">Opis modułu:</label>
                    <textarea name="opis" id="opis" rows="2" cols="70"></textarea><em class="TipIkona"><b>Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                </p>

                <p>
                    <label for="wyswietla">Co wyświetla ?</label>
                    <input name="wyswietla" id="wyswietla" type="text" size="40" value="" /><em class="TipIkona"><b>Co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                </p>  

                <div class="TytulDzialu"><div class="TytulRwd">Ustawienia dodatkowe</div></div>
                
                <p>
                    <label>Czy moduł działa w szablonach wersja v2 ?</label>
                    <input type="radio" value="0" name="v2" id="v2_nie" /><label class="OpisFor" for="v2_nie">nie<em class="TipIkona"><b>Moduł nie jest przystosowany do wyświetlania w szablonach wersja v2</b></em></label>
                    <input type="radio" value="1" name="v2" id="v2_tak" /><label class="OpisFor" for="v2_tak">tak<em class="TipIkona"><b>Moduł jest przystosowany do wyświetlania w szablonach wersja v2</b></em></label>
                    <input type="radio" value="2" name="v2" id="v2_wszystkie" checked="checked" /><label class="OpisFor" for="v2_wszystkie">tak - w każdym szablonie<em class="TipIkona"><b>Moduł jest przystosowany do wyświetlania we wszystkich wersjach szablonów</b></em></label>
                </p>                
                
                <p>
                    <label>Wygląd modułu przy małych rozdzielczościach (na urządzeniach mobilnych):</label>
                    <input type="radio" value="0" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_bezzmian" checked="checked" /><label class="OpisFor" for="rwd_rozdzielczosc_bezzmian">bez zmian<em class="TipIkona"><b>Moduł będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                    <input type="radio" value="1" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_ukrywanie" /><label class="OpisFor" for="rwd_rozdzielczosc_ukrywanie">ma być <u>niewidoczny</u> na urządzeniach mobilnych<em class="TipIkona"><b>Moduł nie będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                    <input type="radio" value="2" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_widoczny" /><label class="OpisFor" for="rwd_rozdzielczosc_widoczny">ma być <u>widoczny</u> tylko na urządzeniach mobilnych<em class="TipIkona"><b>Moduł będzie widoczny tylko przy małych rozdzielczościach ekranu</b></em></label>
                </p>

                <script>
                gold_tabs('0');
                </script>                 
                
            </div>

            <div class="przyciski_dolne">
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />
              <button type="button" class="przyciskNon" onclick="cofnij('srodek','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
            </div>            

          </div>                      
       
      </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
