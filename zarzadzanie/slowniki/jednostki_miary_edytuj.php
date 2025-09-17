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
            $pola = array(array('products_jm_default','0'));
            $db->update_query('products_jm' , $pola);	        
        }
        //    
        $pola = array(
                array('products_jm_quantity_type',(int)$_POST["format_ilosci"]),
                array('products_jm_default',(int)$_POST["domyslny"]));
                
        //			
        $db->update_query('products_jm' , $pola, " products_jm_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_jm_description' , " products_jm_id = '".$filtr->process($_POST["id"])."'");        
        
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
                    array('products_jm_id',$filtr->process($_POST["id"])),
                    array('products_jm_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id']));           
            $db->insert_query('products_jm_description' , $pola);
            unset($pola);
            //          
        }              
        //
        Funkcje::PrzekierowanieURL('jednostki_miary.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
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

          <form action="slowniki/jednostki_miary_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_jm where products_jm_id = '" . (int)$_GET['id_poz'] . "'";
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
                            $zapytanie_jezyk = "select distinct * from products_jm_description where products_jm_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_jm_name']) ? $nazwa['products_jm_name'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_jm_name']) ? $nazwa['products_jm_name'] : ''); ?>" id="nazwa_<?php echo $w; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk, $nazwa);
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0');
                    </script>  
                    
                    <p>
                      <label>Format liczby ilości:</label>
                      <input type="radio" name="format_ilosci" id="format_calkowita" value="1" <?php echo (($info['products_jm_quantity_type'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="format_calkowita">liczba całkowita<em class="TipIkona"><b>Klient będzie mógł kupić tylko całe produkty</b></em></label>
                      <input type="radio" name="format_ilosci" id="format_ulamek" value="0" <?php echo (($info['products_jm_quantity_type'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="format_ulamek">liczba ułamkowa<em class="TipIkona"><b>Klient będzie mógł kupić np 0,5 kg produktu</b></em></label>
                    </p>                    

                    <?php if ($info['products_jm_default'] == '0') { ?>
                    
                    <p>
                      <label>Czy jednostka jest domyślna:</label>
                      <input type="radio" value="0" name="domyslny" id="domyslna_nie" checked="checked" /><label class="OpisFor" for="domyslna_nie">nie</label>
                      <input type="radio" value="1" name="domyslny" id="domyslna_tak" /><label class="OpisFor" for="domyslna_tak">tak</label>                      
                    </p>
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('jednostki_miary','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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
