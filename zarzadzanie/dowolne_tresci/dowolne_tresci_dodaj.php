<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('any_content_status','1'),
                array('any_content_css',$filtr->process($_POST['css'])));
        
        $sql = $db->insert_query('any_content' , $pola);
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
                    array('id_any_content',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('any_content_name',$filtr->process($_POST['nazwa_'.$w])),                
                    array('any_content_description',$filtr->process($_POST['edytor_'.$w])));                        

            $sql = $db->insert_query('any_content_description' , $pola);
            unset($pola);
            
        }

        unset($ile_jezykow); 

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
          
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('dowolne_tresci_edytuj.php?id_poz='.$id_dodanej_pozycji);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('dowolne_tresci.php?id_poz='.$id_dodanej_pozycji);
                //
            }             

        } else {
          
            Funkcje::PrzekierowanieURL('dowolne_tresci.php');
            
        }        

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="dowolne_tresci/dowolne_tresci_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

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
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">

                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required" for="nazwa_0">Nazwa / tytuł:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa / tytuł:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p> 

                            <div class="edytor" style="margin-bottom:10px">
                              <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </div>                                 

                        </div>
                        
                    <?php } ?> 
                    
                </div>
                
                <script>
                gold_tabs('0','edytor_');
                </script> 
                
                <script src="programy/ace/ace.js"></script>
                
                <div style="margin:20px 0 0 0">
                  <label style="width:100%;padding-left:7px" for="css_kolumna_">Dodatkowy kod CSS dla treści:</label>
                  <textarea hidden name="css" id="css" rows="5" cols="50" style="width:50%"></textarea>  
                  <span class="maleInfo" style="display:block;margin-left:7px">Sam kod css bez znacznika style. Każdą linie trzeba zacząć od znacznika {KLASA_CSS_TRESCI} w miejsce którego zostanie podstawiona klasa css treści - kod będzie działał tylko w obrębie tej treści.</span>
                </div> 
                
                <div style="padding:3px;margin:0 5px 10px 7px;border:1px solid #dbdbdb;border-radius:3px;">
                
                    <div id="ace_css_kolumna"></div>
                    
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
              <button type="button" class="przyciskNon" onclick="cofnij('dowolne_tresci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
            </div>            
            
          </div>

          </form>

    </div>

    <?php
    include('stopka.inc.php');    
    
} ?>