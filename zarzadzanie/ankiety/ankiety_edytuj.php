<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_ankiety = (int)$_POST['id'];
        
        // kasuje rekordy w tablicy
        $db->delete_query('poll_description' , " id_poll = '".(int)$id_ankiety."'");   
        $db->delete_query('poll_field' , " id_poll = '".(int)$id_ankiety."'");          
        
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
                    array('id_poll',(int)$id_ankiety),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('poll_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('poll_description',$filtr->process($_POST['edytor_'.$w])),
                    array('poll_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('poll_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('poll_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));                     
            $sql = $db->insert_query('poll_description' , $pola);
            unset($pola);
            
        }

        // dodawanie pol ankiety
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
        
            for ($q = 1; $q < 11; $q++) {
                
                if (!empty($_POST['odpowiedz_'.$q.'_'.$w])) {
                    $pola = array(
                            array('id_poll',(int)$id_ankiety),
                            array('language_id',(int)$ile_jezykow[$w]['id']),
                            array('poll_field',$filtr->process($_POST['odpowiedz_'.$q.'_'.$w])),
                            array('poll_field_sort',$q),
                            array('poll_result',$filtr->process($_POST['wynik_'.$q.'_'.$w])));           
                    $sql = $db->insert_query('poll_field' , $pola);
                    unset($pola);
                }
                
            }
        
        }        

        unset($ile_jezykow);    

        Funkcje::PrzekierowanieURL('ankiety.php?id_poz='.$id_ankiety);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="ankiety/ankiety_edytuj.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from poll where id_poll = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['id_poll']; ?>" />

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                    <script>
                    $(document).ready(function() {
                      $("#poForm").validate({
                        rules: {
                          nazwa_0: {
                            required: true
                          }             
                        },
                        messages: {
                          nazwa_0: {
                            required: "Pole jest wymagane."
                          }              
                        }
                      });
                    });
                    </script>  
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from poll_description where id_poll = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $ankieta = $sqls->fetch_assoc();                           
                        
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_1">Nazwa ankiety:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($ankieta['poll_name']) ? Funkcje::formatujTekstInput($ankieta['poll_name']): ''); ?>" id="nazwa_1" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa ankiety:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($ankieta['poll_name']) ? Funkcje::formatujTekstInput($ankieta['poll_name']): ''); ?>" />
                                   <?php } ?>
                                </p> 

                                <p>
                                  <label for="tytul_meta_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                                  <input type="text" name="tytul_meta_<?php echo $w; ?>" id="tytul_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo (isset($ankieta['poll_meta_title_tag']) ? $ankieta['poll_meta_title_tag'] : ''); ?>" />
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo (isset($ankieta['poll_meta_title_tag']) ? strlen(mb_convert_encoding((string)$ankieta['poll_meta_title_tag'], 'ISO-8859-1', 'UTF-8')): '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                
                                
                                <p>
                                  <label for="opis_meta_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                                  <input type="text" name="opis_meta_<?php echo $w; ?>" id="opis_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="<?php echo (isset($ankieta['poll_meta_desc_tag']) ? $ankieta['poll_meta_desc_tag'] : ''); ?>" />
                                </p>   
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo (isset($ankieta['poll_meta_desc_tag']) ? strlen(mb_convert_encoding((string)$ankieta['poll_meta_desc_tag'], 'ISO-8859-1', 'UTF-8')) : '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                    
                                
                                <p>
                                  <label for="slowa_meta_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                                  <input type="text" name="slowa_meta_<?php echo $w; ?>" id="slowa_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="<?php echo (isset($ankieta['poll_meta_keywords_tag']) ? $ankieta['poll_meta_keywords_tag'] : ''); ?>" />
                                </p>

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo (isset($ankieta['poll_meta_keywords_tag']) ? strlen(mb_convert_encoding((string)$ankieta['poll_meta_keywords_tag'], 'ISO-8859-1', 'UTF-8')): '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                 
                                
                                <div class="edytor" style="margin-bottom:10px">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($ankieta['poll_description']) ? $ankieta['poll_description'] : ''); ?></textarea>
                                </div>                                 

                                <?php for ($q = 1; $q < 11; $q++) { ?>
                                
                                <?php
                                // pobieranie poszczegolnych odpowidzi
                                $zapytanie_odpowiedz = "select distinct * from poll_field where id_poll = '".(int)$_GET['id_poz']."' and language_id = '" .(int)$ile_jezykow[$w]['id']."' and poll_field_sort = '" . $q . "'";
                                $sqlsp = $db->open_query($zapytanie_odpowiedz);
                                //
                                if ((int)$db->ile_rekordow($sqlsp) > 0) {
                                    $odpowiedz = $sqlsp->fetch_assoc();      
                                } else {
                                    $odpowiedz['poll_field'] = '';
                                    $odpowiedz['poll_result']  = 0;
                                }
                                ?>
                            
                                <p>
                                  <label for="odpowiedz_<?php echo $q; ?>_<?php echo $w; ?>">Odpowiedź nr <?php echo $q; ?>:</label>
                                  <input type="text" name="odpowiedz_<?php echo $q; ?>_<?php echo $w; ?>" id="odpowiedz_<?php echo $q; ?>_<?php echo $w; ?>" size="50" value="<?php echo (isset($odpowiedz['poll_field']) ? Funkcje::formatujTekstInput($odpowiedz['poll_field']): ''); ?>" />
                                </p>  

                                <p>
                                  <label for="wynik_<?php echo $q; ?>_<?php echo $w; ?>">Ilość oddanych głosów:</label>
                                  <input type="text" name="wynik_<?php echo $q; ?>_<?php echo $w; ?>" id="wynik_<?php echo $q; ?>_<?php echo $w; ?>" size="5" value="<?php echo ((isset($odpowiedz['poll_result']) && $odpowiedz['poll_result'] > 0) ? $odpowiedz['poll_result'] : ''); ?>" />
                                </p>     

                                <?php
                                $db->close_query($sqlsp);
                                unset($odpowiedz);                                
                                ?>
                            
                                <?php } ?>
                                
                            </div>
                            <?php     

                            $db->close_query($sqls);
                            unset($ankieta);                            
                            
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0','edytor_');
                    </script> 
                
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('ankiety','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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
    
} ?>