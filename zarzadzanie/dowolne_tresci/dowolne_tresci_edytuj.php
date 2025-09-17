<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
        $id_tresci = (int)$_POST['id'];
    
        $pola = array(
                array('any_content_css',$filtr->process($_POST['css'])));
        
        $sql = $db->update_query('any_content' , $pola, " id_any_content = '".$id_tresci."'");  
        
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('any_content_description' , " id_any_content = '".$id_tresci."'");   
        
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
                    array('id_any_content',$id_tresci),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('any_content_name',$filtr->process($_POST['nazwa_'.$w])),                
                    array('any_content_description',$filtr->process($_POST['edytor_'.$w])));                       

            $sql = $db->insert_query('any_content_description' , $pola);
            unset($pola);
            
        }

        unset($ile_jezykow); 

        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('dowolne_tresci_edytuj.php?id_poz=' . (int)$id_tresci . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka='.(int)$_POST['zakladka'] : ''));
            //
          } else {        
            //
            if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
              
              } else {
              
                Funkcje::PrzekierowanieURL('dowolne_tresci.php?id_poz='.$id_tresci);
                
            }  
            //
        }    

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="dowolne_tresci/dowolne_tresci_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from any_content where id_any_content = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['id_any_content']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>                      
                    
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
                       
                        <?php for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) { ?>
                        
                            <?php                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from any_content_description where id_any_content = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $tresc = $sqls->fetch_assoc();                           
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">

                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa / tytuł:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($tresc['any_content_name']); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa / tytuł:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($tresc['any_content_name']); ?>" id="nazwa_<?php echo $w; ?>" />
                                   <?php } ?>
                                </p> 

                                <div class="edytor" style="margin-bottom:10px">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $tresc['any_content_description']; ?></textarea>
                                </div>                                 

                            </div>

                            <?php
                            $db->close_query($sqls);
                            unset($pole);                                
                            ?>                              

                        <?php } ?> 
                        
                    </div>
                    
                    <script>
                    gold_tabs('0','edytor_');
                    </script> 
                    
                    <script src="programy/ace/ace.js"></script>
                    
                    <div style="margin:20px 0 0 0">
                      <label style="width:100%;padding-left:7px" for="css_kolumna_">Dodatkowy kod CSS dla treści:</label>
                      <textarea hidden name="css" id="css" rows="5" cols="50" style="width:50%"><?php echo $info['any_content_css']; ?></textarea>  
                      <span class="maleInfo" style="display:block;margin-left:7px">Sam kod css bez znacznika style. Każdą linie trzeba zacząć od znacznika {KLASA_CSS_TRESCI} w miejsce którego zostanie podstawiona klasa css treści - kod będzie działał tylko w obrębie tej treści.</span>
                    </div> 
                    
                    <div style="padding:3px;margin:0 5px 10px 7px;border:1px solid #dbdbdb;border-radius:3px;">
                    
                        <div id="ace_css_kolumna"><?php echo $info['any_content_css']; ?></div>
                        
                    </div>

                    <script>
                    var textarea = $('#css');
                    var editor = ace.edit("ace_css_kolumna", {
                        theme: "ace/theme/chrome",
                        mode: "ace/mode/css",
                        minLines: 10,
                        maxLines: 50,
                        tabSize: 2,
                        showPrintMargin: false,
                        showInvisibles: false,
                        fontSize: '13px',
                        useWorker: false
                    });

                    editor.getSession().on('change', function () {
                       textarea.val(editor.getSession().getValue());
                    });

                    textarea.val(editor.getSession().getValue());
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
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('dowolne_tresci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button> 
                  
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
    
} ?>