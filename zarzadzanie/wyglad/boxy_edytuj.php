<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_boxu = (int)$_POST['id'];
        //
        $pola = array(
                array('box_description',$filtr->process($_POST['opis'])),
                array('box_display',$filtr->process($_POST['wyswietla'])),
                array('box_localization',(int)$_POST['polozenie']),
                array('box_rwd',1),
                array('box_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']),
                array('box_v2',(int)$_POST['v2']));
             
        // jezeli jest wyswietlany na podstronach (wybranych)
        if ( (int)$_POST['polozenie'] == 3 ) {
             if ( isset($_POST['strony']) && count($_POST['strony']) > 0 ) {
                  $pola[] = array('box_localization_site',implode(';', (array)$filtr->process($_POST['strony'])));
             } else {
                  $pola[] = array('box_localization_site','');
             }
        } else {
             $pola[] = array('box_localization_site','');
        }
        
        // jezeli wybrano plik php
        if ($_POST['tryb'] == 'plik') {
            $pola[] = array('box_file',$filtr->process($_POST['plik']));
        } else {
            $pola[] = array('box_file','');   
        }
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'strona') {
            $pola[] = array('box_pages_id',$filtr->process($_POST['stronainfo']));
        } else {
            $pola[] = array('box_pages_id','');   
        }         
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'java') {
            $pola[] = array('box_code',$_POST['kod']);
        } else {
            $pola[] = array('box_code','');   
        }           
        
        // jezeli jest indywidualny box
        if ($_POST['box_wyglad'] == '1') {
            $pola[] = array('box_theme',$filtr->process($_POST['box_wyglad']));
            $pola[] = array('box_theme_file',$filtr->process($_POST['plik_wyglad']));
            $pola[] = array('box_header',$filtr->process($_POST['naglowek']));
        }
        if ($_POST['box_wyglad'] == '0') {
            $pola[] = array('box_theme','0');
            $pola[] = array('box_theme_file','');
            $pola[] = array('box_header',$filtr->process($_POST['naglowek']));
        }          
        if ($_POST['box_wyglad'] == '2') {
            $pola[] = array('box_theme','2');
            $pola[] = array('box_theme_file','');
            $pola[] = array('box_header','0');
        }
        
        $sql = $db->update_query('theme_box' , $pola, " box_id = '".$id_boxu."'");
        unset($pola);
        
        $db->delete_query('theme_box_description' , " box_id = '".$id_boxu."'");
        
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
                    array('box_id',$id_boxu),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('box_title',$filtr->process($_POST['nazwa_'.$w]))); 

            // jezeli wybrano tresc w edytorze
            if ($_POST['tryb'] == 'txt') {
                $pola[] = array('box_text',$filtr->process($_POST['opis_'.$w]));
            } else {
                $pola[] = array('box_text','');
            }                        
                    
            $sql = $db->insert_query('theme_box_description' , $pola);
            unset($pola);
        }
        //
        //
        // jezeli jest strona informacyjna doda do strony info ze jest wyswietlana w boxie
        if ($_POST['tryb'] == 'strona') {
            //
            // sprawdzi czy zostala zmieniona strona - jezeli tak to usunie ze starej ikone ze jest przypisana do boxu
            if ( $_POST['stronainfo'] != $_POST['poprzednie_id'] ) {
                //
                $pola = array( array('pages_modul',0));
                $db->update_query('pages' , $pola, 'pages_id = ' . (int)$_POST['poprzednie_id']);
                unset($pola);
                //            
            }
            //        
            $pola = array( array('pages_modul',2));
            $db->update_query('pages' , $pola, 'pages_id = ' . $filtr->process($_POST['stronainfo']));
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
            Funkcje::PrzekierowanieURL('boxy_edytuj.php?id_poz=' . (int)$id_boxu . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka='.(int)$_POST['zakladka'] : ''));
            //
          } else {        
            //
            if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('wyglad.php?zakladka='.(int)$_POST['zakladka']);
              
              } else if ( isset($_POST['strona']) && (int)$_POST['strona'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('/zarzadzanie/strony_informacyjne/strony_informacyjne_edytuj.php?id_poz='.(int)$_POST['strona']);
              
              } else {
              
                Funkcje::PrzekierowanieURL('boxy.php?id_poz='.(int)$_POST["id"]);
                
            } 
            //
        }   

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">        

          <form action="wyglad/boxy_edytuj.php" method="post" id="boxyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    

            $zapytanie = "select * from theme_box where box_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <script>
                $(document).ready(function() {
                  $("#boxyForm").validate({
                    rules: {
                      <?php if ($info['box_type'] == 'plik') { ?>
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
                      <?php if ($info['box_type'] == 'plik') { ?>
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
                        $('#wyglad').slideUp();
                       } else {
                        $('#wyglad').slideDown();
                    }
                    if ( id == 2 ) {
                         $('#div_rwd_rozdzielczosc_mini').hide();
                         $('#rwd_rozdzielczosc_bezzmian').prop('checked',true);
                         $('#rwd_rozdzielczosc_ukrywanie').prop('checked',false);
                         $('#rwd_rozdzielczosc_mini').prop('checked',false);                    
                    } else {
                         $('#div_rwd_rozdzielczosc_mini').show();
                    }
                    if (id == 2) {
                        $('#BoxNaglowek').slideUp();
                        $('#naglowek_tak').prop('checked',false);
                        $('#naglowek_nie').prop('checked',true);
                    } else {
                        $('#BoxNaglowek').slideDown();
                        $('#naglowek_tak').prop('checked',true);
                        $('#naglowek_nie').prop('checked',false);                        
                    }
                }         

                function zmien_polozenie(id) {
                    if (id == 3) {
                        $('#lista_podstron').slideDown();
                    } else {
                        $('#lista_podstron').slideUp();
                    }
                }                  

                function zmien_naglowek(nr) {
                    if ( nr == 1 ) {
                         $('#div_rwd_rozdzielczosc_mini').show();
                         //
                         if ( $("input[name=box_wyglad]:checked").val() == '2' ) {
                              $('#div_rwd_rozdzielczosc_mini').hide();
                              $('#rwd_rozdzielczosc_bezzmian').prop('checked',true);
                              $('#rwd_rozdzielczosc_ukrywanie').prop('checked',false);
                              $('#rwd_rozdzielczosc_mini').prop('checked',false);
                         }
                         //
                    }
                    if ( nr == 0 ) {
                         $('#div_rwd_rozdzielczosc_mini').hide();
                         $('#rwd_rozdzielczosc_bezzmian').prop('checked',true);
                         $('#rwd_rozdzielczosc_ukrywanie').prop('checked',false);
                         $('#rwd_rozdzielczosc_mini').prop('checked',false);
                    }  
                }
                </script>                  
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
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
                            $zapytanie_jezyk = "select distinct * from theme_box_description where box_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa boxu:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['box_title']) ? $nazwa['box_title'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa boxu:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['box_title']) ? $nazwa['box_title'] : ''); ?>" id="nazwa_<?php echo $w; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk, $nazwa);
                        }                    
                        ?>                      
                    </div>   

                    <input type="hidden" value="<?php echo $info['box_type']; ?>" name="tryb" />
                    
                    <p>
                      <label>Rodzaj boxu:</label>
                      <input type="radio" value="txt" name="tryb_tmp" id="rodzaj_txt" disabled="disabled" <?php echo (($info['box_type'] == 'txt') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_txt">dowolna treść<em class="TipIkona"><b>Box będzie wyświetlał zawartość wpisaną w edytorze tekstu</b></em></label>
                      <input type="radio" value="strona" name="tryb_tmp" id="rodzaj_strona" disabled="disabled" <?php echo (($info['box_type'] == 'strona') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_strona">strona informacyjna<em class="TipIkona"><b>Box będzie wyświetlał treść wybranej strony informacyjnej</b></em></label>                      
                      <input type="radio" value="plik" name="tryb_tmp" id="rodzaj_plik" disabled="disabled" <?php echo (($info['box_type'] == 'plik') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_plik">plik php<em class="TipIkona"><b>Box będzie wyświetlał zawartość generowaną przez plik napisany w języku PHP</b></em></label>
                      <input type="radio" value="java" name="tryb_tmp" id="rodzaj_skrypt" disabled="disabled" <?php echo (($info['box_type'] == 'java') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_skrypt">dowolny skrypt np javascript<em class="TipIkona"><b>Box będzie wyświetlał wynik działania skryptu</b></em></label>
                    </p>                   

                    <?php if ($info['box_type'] == 'txt') { ?>
                    
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
                                CKEDITOR.instances[CKEDITOR.instances[i].name].destroy();
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
                                        $zapytanie_jezyk = "select distinct * from theme_box_description where box_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $opis_txt = $sqls->fetch_assoc();   
                                        ?>
                                        
                                        <div id="info_tab_id_<?php echo $w; ?>_1" class="PoleEdytora" style="display:none;">

                                            <div class="edytor">
                                                <textarea cols="80" rows="10" id="edytor_<?php echo $w; ?>_1" name="opis_<?php echo $w; ?>"><?php echo (isset($opis_txt['box_text']) ? $opis_txt['box_text'] : ''); ?></textarea>    
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
                    
                    <?php if ($info['box_type'] == 'plik') { ?>
                    
                        <div class="PoleTrybu">
                        
                            <p>
                                <label class="required" for="plik">Nazwa pliku:</label>
                                <input type="text" name="plik" id="plik" value="<?php echo $info['box_file']; ?>" size="40" />
                            </p>
                            
                            <?php
                            // jezeli jest plik boxu
                            if (is_file('../boxy/' . $info['box_file'])) {
                                //
                                $lines = file('../boxy/' . $info['box_file']);
                                for ($i = 0, $j = count($lines); $i < $j; $i++) {
                                    //
                                    if (strpos((string)$lines[$i],'{{') > -1) {
                                        //
                                        $preg = preg_match('|{{([0-9A-Za-ząćęłńóśźż _,;:-?()]+?)}}|', $lines[$i], $matches);
                                        //
                                        $PodzialOpis = explode(';', str_replace(array('{{','}}'), '', (string)$matches[0]));
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

                    <?php if ($info['box_type'] == 'strona') { ?>
                    
                        <div class="PoleTrybu">
                        
                            <p>
                            
                                <label for="stronainfo">Wybierz stronę informacyjną:</label>
                                <?php
                                // pobieranie danych o stronach informacyjnych
                                $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and (p.pages_modul = '0' or p.pages_id = '" . (int)$info['box_pages_id'] . "')";
                                $sqls = $db->open_query($zapytanie_tmp);
                                //
                                $tablica = array();
                                while ($infs = $sqls->fetch_assoc()) { 
                                    $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                                }
                                $db->close_query($sqls); 
                                unset($zapytanie_tmp, $infs);    
                                //                          
                                echo Funkcje::RozwijaneMenu('stronainfo', $tablica, $info['box_pages_id'], 'id="stronainfo"'); 
                                unset($tablica);
                                ?>
                                
                                <input name="poprzednie_id" type="hidden" value="<?php echo $info['box_pages_id']; ?>" />
                                
                            </p>
                            
                        </div>
                        
                    <?php } ?>

                    <?php if ($info['box_type'] == 'java') { ?>
                    
                        <div class="PoleTrybu">
                        
                            <table class="OknoTextareaModulBox">
                                <tr><td><label>Wstaw kod:</label></td>
                                <td class="TrybEdytor">
                                    <textarea cols="120" rows="15" name="kod" id="kod"><?php echo htmlspecialchars(html_entity_decode((string)$info['box_code'])); ?></textarea>
                                </td></tr>
                            </table> 
                            
                        </div>  

                    <?php } ?>
                    
                    <div id="BoxNaglowek" <?php echo (($info['box_theme'] == 2) ? 'style="display:none;margin-top:15px"' : 'style="margin-top:15px"'); ?>>
                    
                        <div class="TytulDzialu" style="margin-top:0px">Nagłówek boxu</div>

                        <p>
                            <label>Nagłówek boxu:</label>
                            <input type="radio" value="1" name="naglowek" onclick="zmien_naglowek(1)" id="naglowek_tak" <?php echo (($info['box_header'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_tak">tak<em class="TipIkona"><b>W boxie będzie wyświetał się nagłówek z nazwą boxu</b></em></label>
                            <input type="radio" value="0" name="naglowek" onclick="zmien_naglowek(0)" id="naglowek_nie" <?php echo (($info['box_header'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_nie">nie<em class="TipIkona"><b>W boxie nie będzie się wyświetał nagłówek z nazwą boxu - tylko sama treść boxu</b></em></label>
                        </p>
                    
                    </div>
                    
                    <div class="TytulDzialu">Ustawienia wyglądu i miejsca wyświetlania boxu</div>
                    
                    <p>
                        <label>Wygląd boxu:</label>
                        <input type="radio" value="0" name="box_wyglad" id="szablon_standardowy" onclick="zmien_wyglad(0)" <?php echo (($info['box_theme'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_standardowy">standardowy<em class="TipIkona"><b>Zawartość boxu będzie wyświetlana w standardowym wyglądzie boxu szablonu</b></em></label>
                        <input type="radio" value="1" name="box_wyglad" id="szablon_indywidualny" onclick="zmien_wyglad(1)" <?php echo (($info['box_theme'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_indywidualny">indywidualny<em class="TipIkona"><b>Zawartość boxu będzie wyświetlana w indywidualnym wyglądzie boxu szablonu - wymaga to napisania skryptu wyświetlającego zawartość boxu</b></em></label>
                        <input type="radio" value="2" name="box_wyglad" id="szablon_brak" onclick="zmien_wyglad(2)" <?php echo (($info['box_theme'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szablon_brak">sama treść<em class="TipIkona"><b>Bezpośrednie wyświetlanie ustawionej treści - bez żadnych dodatkowych elementów z pliku boxy_wyglad/box.tp w szablonie (tytułu, ramki etc)</b></em></label>
                    </p>                      

                    <div id="wyglad" <?php echo (($info['box_theme'] == 0 || $info['box_theme'] == 2) ? 'style="display:none"' : ''); ?>>
                        <p>
                            <label class="required" for="plik_wyglad">Nazwa pliku w szablonie:</label>
                            <input type="text" name="plik_wyglad" id="plik_wyglad" value="<?php echo $info['box_theme_file']; ?>" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd w szablonie np. moj_box.tp</b></em>
                        </p>
                    </div>  

                    <p>
                        <label>Wyświetlanie boxu:</label>
                        <input type="radio" value="1" name="polozenie" id="polozenie_wszystkie" onclick="zmien_polozenie(1)" <?php echo (($info['box_localization'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_wszystkie">wszystkie strony<em class="TipIkona"><b>Box będzie wyświetlany na wszystkich stronach sklepu</b></em></label>
                        <input type="radio" value="3" name="polozenie" id="polozenie_podstrony" onclick="zmien_polozenie(3)" <?php echo (($info['box_localization'] == 3) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_podstrony">tylko podstrony<em class="TipIkona"><b>Box będzie wyświetlany tylko na podstronach (bez strony głównej)</b></em></label>
                        <input type="radio" value="2" name="polozenie" id="polozenie_glowna" onclick="zmien_polozenie(2)" <?php echo (($info['box_localization'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_glowna">tylko strona główna<em class="TipIkona"><b>Box będzie wyświetlany tylko na stronie głównej sklepu</b></em></label>
                    </p>    

                    <div id="lista_podstron" <?php echo (($info['box_localization'] != 3) ? 'style="display:none"' : ''); ?>>                    
                    
                        <table class="WyborStrony" style="margin-top:10px">
                            <tr><td><label>Wybierz strony na których ma się wyświetlać box:<em class="TipIkona"><b>Box będzie wyświetlany tylko jeżeli będzie włączona kolumna z boxami</b></em></label></td>
                            <td>
                                <div>
                                <?php
                                $ZapisaneStrony = explode(';', (string)$info['box_localization_site']);
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
                                <span class="maleInfo" style="margin:5px 0px 0px 7px">Jeżeli nie zostanie wybrana żadna podstrona box będzie wyświetlany na wszystkich podstronach</span>
                            </td></tr>
                        </table>                    
                    
                    </div>                    
                    
                    <div class="TytulDzialu">Opis boxu (informacja tylko dla administratora sklepu)</div>

                    <p>
                        <label for="opis">Opis boxu:</label>
                        <textarea name="opis" id="opis" rows="2" cols="70"><?php echo $info['box_description']; ?></textarea><em class="TipIkona"><b>Opis co będzie wyświetlał box - informacja tylko dla administratora sklepu</b></em>
                    </p>

                    <p>
                        <label for="wyswietla">Co wyświetla ?</label>
                        <input name="wyswietla" id="wyswietla" type="text" size="40" value="<?php echo $info['box_display']; ?>" /><em class="TipIkona"><b>Co będzie wyświetlał box - informacja tylko dla administratora sklepu</b></em>
                    </p> 

                    <div class="TytulDzialu"><div class="TytulRwd">Ustawienia dodatkowe</div></div>
                    
                    <p>
                        <label>Czy box działa w szablonach wersja v2 ?</label>
                        <input type="radio" value="0" name="v2" id="v2_nie" <?php echo (($info['box_v2'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="v2_nie">nie<em class="TipIkona"><b>Box nie jest przystosowany do wyświetlania w szablonach wersja v2</b></em></label>
                        <input type="radio" value="1" name="v2" id="v2_tak" <?php echo (($info['box_v2'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="v2_tak">tak<em class="TipIkona"><b>Box jest przystosowany do wyświetlania w szablonach wersja v2</b></em></label>
                        <input type="radio" value="2" name="v2" id="v2_wszystkie" <?php echo (($info['box_v2'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="v2_wszystkie">tak - w każdym szablonie<em class="TipIkona"><b>Box jest przystosowany do wyświetlania we wszystkich wersjach szablonów</b></em></label>
                    </p>    

                    <p>
                        <label>Wygląd boxu przy małych rozdzielczościach (na urządzeniach mobilnych):</label>
                        <input type="radio" value="0" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_bezzmian" <?php echo (($info['box_rwd_resolution'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_bezzmian">bez zmian<em class="TipIkona"><b>Box będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                        <input type="radio" value="1" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_ukrywanie" <?php echo (($info['box_rwd_resolution'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_ukrywanie">ma być <u>niewidoczny</u> na urządzeniach mobilnych<em class="TipIkona"><b>Box nie będzie widoczny przy małych rozdzielczościach ekranu</b></em></label>
                        <span id="div_rwd_rozdzielczosc_mini" <?php echo (($info['box_header'] == 0) ? 'style="display:none"' : ''); ?>>
                            <input type="radio" value="2" name="rwd_mala_rozdzielczosc" id="rwd_rozdzielczosc_mini" <?php echo (($info['box_rwd_resolution'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rwd_rozdzielczosc_mini">ma być <u>zminimalizowany</u> na urządzeniach mobilnych<em class="TipIkona"><b>Box będzie zminimalizowany z możliwością rozwinięcia całej treści boxu</b></em></label>
                        </span>
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
                  
                  <?php } else { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('boxy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>  
                  
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
