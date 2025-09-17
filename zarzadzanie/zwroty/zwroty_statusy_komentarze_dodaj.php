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
                array('status_id',(int)$_POST["status_id"]),
                array('comments_name',$filtr->process($_POST["nazwa"])),
                array('sort_order',$filtr->process($_POST["sort"]))
        );
        //	
        $db->insert_query('standard_return_comments' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();

        unset($pola);

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('comments_id',$id_dodanej_pozycji),
                    array('comments_text',$filtr->process($_POST['edytor_'.$w])),
                    array('languages_id',$ile_jezykow[$w]['id']));        
            //
            $sql = $db->insert_query('standard_return_comments_description' , $pola);  
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        
        //
        Funkcje::PrzekierowanieURL('zwroty_statusy_komentarze.php?id_poz='.(int)$id_dodanej_pozycji.'&status_id='.(int)$_POST["status_id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( !isset($_GET['status_id']) ) {
         $_GET['status_id'] = 0;
    }     
    ?>
    
    <div id="naglowek_cont">Dodanie komentarza do zwrotu dla statusu - <?php echo Zwroty::pokazNazweStatusuZwrotu((int)$_GET['status_id']); ?></div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#zwrotForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                sort: {
                  required: true,
                  range: [0, 999],
                  number: true
                }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                },               
                sort: {
                  required: "Pole jest wymagane.",
                  range: "Pole musi być liczbą większą od 0.",
                  number: "Pole musi być liczbą."
                }
              }
            });
          });
          </script>     

          <form action="zwroty/zwroty_statusy_komentarze_dodaj.php" method="post" id="zwrotForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
                <div class="pozycja_edytowana">

                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="status_id" value="<?php echo ((isset($_GET["status_id"])) ? (int)$_GET["status_id"] : '0'); ?>" />

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
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
                        
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <div class="edytor">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                                </div>   
                                            
                            </div>
                            <?php                    
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0','edytor_');
                    </script> 

                    <p>
                        <label class="required" for="nazwa">Nazwa:</label>
                        <input type="text" name="nazwa" size="60" value="" id="nazwa" /><em class="TipIkona"><b>Nazwa wyświetlana na liście wyboru komentarzy</b></em>
                    </p>

                    <p>
                        <label class="required" for="sort">Kolejność wyświetlnia:</label>
                        <input type="text" name="sort" id="sort" size="8" value="" /><em class="TipIkona"><b>Kolejność wyświetlania na liście wyboru komentarzy</b></em>
                    </p>

                </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('zwroty_statusy_komentarze','<?php echo Funkcje::Zwroc_Wybrane_Get(array('status_id')); ?>','zwroty');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
