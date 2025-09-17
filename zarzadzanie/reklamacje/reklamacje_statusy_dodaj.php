<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('complaints_status_default','0'));
            $db->update_query('complaints_status' , $pola);	        
        }
        //
        $pola = array(
                array('complaints_status_default',$filtr->process($_POST['domyslny'])),
                array('complaints_status_type',$filtr->process($_POST['typ'])),
                array('complaints_status_color',$filtr->process($_POST['kolor']))
                );  
        //	
        $db->insert_query('complaints_status' , $pola);	
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
                    array('complaints_status_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('complaints_status_name',$filtr->process($_POST['nazwa_'.$w])));           
            $sql = $db->insert_query('complaints_status_description' , $pola);
            unset($pola);
        }        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('reklamacje_statusy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('reklamacje_statusy.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#reklamForm").validate({
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
          
          <script type="text/javascript" src="programy/jscolor/jscolor.js"></script> 

          <form action="reklamacje/reklamacje_statusy_dodaj.php" method="post" id="reklamForm" class="cmxform">          

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
                                <label class="required" for="nazwa_0">Nazwa:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label id="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>

                <p>
                    <label for="typ">Typ statusu:</label>
                    <select name="typ" id="typ">
                        <option value="1">Nowe</option>
                        <option value="2">W realizacji</option>
                        <option value="3">Zamknięte (zrealizowane)</option>
                        <option value="4">Zamknięte (niezrealizowane)</option>
                    </select>
                </p>
                
                <p>
                  <label>Czy status jest domyślnym:</label>
                  <input type="radio" value="1" name="domyslny" id="status_tak" /> <label class="OpisFor" for="status_tak">tak</label>
                  <input type="radio" value="0" name="domyslny" id="status_nie" checked="checked" /> <label class="OpisFor" for="status_nie">nie</label>
                </p>                 

                <p>
                  <label for="kolor">Kolor (widoczny w liście reklamacji):</label>
                  <input name="kolor" id="kolor" class="color" style="-moz-box-shadow:none" value="474747" size="8" />                    
                </p>                 
                
                <script>
                gold_tabs('0');
                </script>                    
               
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('reklamacje_statusy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','reklamacje');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
