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
            $pola = array(array('return_status_default','0'));
            $db->update_query('return_status' , $pola);	        
        }
        //
        $pola = array(
                array('return_status_default',$filtr->process($_POST['domyslny'])),
                array('return_status_type',$filtr->process($_POST['typ'])),
                array('return_status_color',$filtr->process($_POST['kolor']))
                );  
        //	
        $db->update_query('return_status' , $pola, " return_status_id = '".(int)$_POST["id"]."'");		
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('return_status_description' , " return_status_id = '".$filtr->process($_POST["id"])."'");        
        
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
                    array('return_status_id',$filtr->process($_POST["id"])),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('return_status_name',$filtr->process($_POST['nazwa_'.$w])));           
            $sql = $db->insert_query('return_status_description' , $pola);
            unset($pola);             
        }              
        //        
        //
        Funkcje::PrzekierowanieURL('zwroty_statusy.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#zwrotForm").validate({
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

          <form action="zwroty/zwroty_statusy_edytuj.php" method="post" id="zwrotForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from return_status where return_status_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
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
                            $zapytanie_jezyk = "select distinct * from return_status_description where return_status_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['return_status_name']) ? $nazwa['return_status_name'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['return_status_name']) ? $nazwa['return_status_name'] : ''); ?>" id="nazwa_<?php echo $w; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0');
                    </script>                    

                    <p>
                        <label for="typ">Typ statusu:</label>
                        <select name="typ" id="typ">
                            <option value="1" <?php echo (($info['return_status_type'] == '1') ? 'selected="selected"' : ''); ?>>Nowe</option>
                            <option value="2" <?php echo (($info['return_status_type'] == '2') ? 'selected="selected"' : ''); ?>>W realizacji</option>
                            <option value="3" <?php echo (($info['return_status_type'] == '3') ? 'selected="selected"' : ''); ?>>Zamknięte (zrealizowane)</option>
                            <option value="4" <?php echo (($info['return_status_type'] == '4') ? 'selected="selected"' : ''); ?>>Zamknięte (niezrealizowane)</option>
                        </select>
                    </p>
                    
                    <?php if ($info['return_status_default'] == '0') { ?>
                    
                    <p>
                      <label>Czy status jest domyślnym:</label>
                      <input type="radio" value="1" name="domyslny" id="status_tak" /><label class="OpisFor" for="status_tak">tak</label>                  
                      <input type="radio" value="0" name="domyslny" id="status_nie" checked="checked" /><label class="OpisFor" for="status_nie">nie</label>
                    </p>
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>               

                    <p>
                      <label for="kolor">Kolor (widoczny w liście zwrotów):</label>
                      <input name="kolor" id="kolor" class="color" style="-moz-box-shadow:none" value="<?php echo $info['return_status_color']; ?>" size="8" />                    
                    </p> 

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zwroty_statusy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','zwroty');">Powrót</button>           
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
