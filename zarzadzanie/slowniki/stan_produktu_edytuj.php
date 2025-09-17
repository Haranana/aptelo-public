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
            $pola = array(array('products_condition_default','0'));
            $db->update_query('products_condition' , $pola);
        }
        $pola = array(
                array('products_condition_default',(int)$_POST["domyslny"]),
                array('products_condition_googleshopping',$filtr->process($_POST["stan_google"])),
                array('products_condition_empik',$filtr->process($_POST["stan_empik"]))
        );
        //
        $db->update_query('products_condition' , $pola, " products_condition_id = '".(int)$_POST["id"]."'");	
        unset($pola);


        // kasuje rekordy w tablicy
        $db->delete_query('products_condition_description' , " products_condition_id = '".(int)$_POST["id"]."'");        
        
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
                    array('products_condition_id',(int)$_POST["id"]),
                    array('products_condition_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',(int)$ile_jezykow[$w]['id']));           
            $db->insert_query('products_condition_description' , $pola);
            unset($pola);
            //           
        }              
        //
        Funkcje::PrzekierowanieURL('stan_produktu.php?id_poz='.(int)$_POST["id"]);
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

          <form action="slowniki/stan_produktu_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_condition where products_condition_id = '" . (int)$_GET['id_poz'] . "'";
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
                            $zapytanie_jezyk = "select distinct * from products_condition_description where products_condition_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_condition_name']) ? $nazwa['products_condition_name'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_condition_name']) ? $nazwa['products_condition_name'] : ''); ?>" id="nazwa_<?php echo $w; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk);
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0');
                    </script>  
                    <?php if ($info['products_condition_default'] == '0') { ?>
                    
                    <p>
                      <label>Czy stan produktu domyślny:</label>
                      <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie</label>
                      <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak</label>
                    </p>
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>

                    <div style="margin:3px 10px 4px 10px">
                        <table>
                            <tr>
                                <td><label>Stan produktu dla Google:</label></td>
                                <td style="padding:3px">
                                    <input type="radio" value="new" name="stan_google" id="stan_nowy" <?php echo (($info['products_condition_googleshopping'] == 'new') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_nowy">NewCondition</label><br>
                                    <input type="radio" value="refurbished" name="stan_google" id="stan_refurbished" <?php echo (($info['products_condition_googleshopping'] == 'refurbished') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_refurbished">RefurbishedCondition</label><br>
                                    <input type="radio" value="used" name="stan_google" id="stan_used" <?php echo (($info['products_condition_googleshopping'] == 'used') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_used">DamagedCondition, UsedCondition</label>
                                 </td>
                             </tr>
                         </table>
                    </div>

                    <div style="margin:3px 10px 4px 10px">
                        <table>
                            <tr>
                                <td><label>Stan produktu dla EMPIK:</label></td>
                                <td style="padding:3px">
                                    <input type="radio" value="11" name="stan_empik" id="stan_nowy11" <?php echo (($info['products_condition_empik'] == '11') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_nowy11">Nowy</label><br>
                                    <input type="radio" value="1" name="stan_empik" id="stan_uzywany1" <?php echo (($info['products_condition_empik'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany1">Używany – stan bardzo dobry</label><br>
                                    <input type="radio" value="2" name="stan_empik" id="stan_uzywany2" <?php echo (($info['products_condition_empik'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany2">Używany – stan dobry</label><br>
                                    <input type="radio" value="3" name="stan_empik" id="stan_uzywany3" <?php echo (($info['products_condition_empik'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany3">Używany – stan zadowalający</label><br>
                                    <input type="radio" value="4" name="stan_empik" id="stan_uzywany4" <?php echo (($info['products_condition_empik'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany4">Odnowiony - stan bardzo dobry</label><br>
                                    <input type="radio" value="5" name="stan_empik" id="stan_uzywany5" <?php echo (($info['products_condition_empik'] == '5') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany5">Odnowiony - stan dobry</label><br>
                                    <input type="radio" value="6" name="stan_empik" id="stan_uzywany6" <?php echo (($info['products_condition_empik'] == '6') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany6">Odnowiony - stan zadowalający</label><br>
                                    <input type="radio" value="7" name="stan_empik" id="stan_uzywany7" <?php echo (($info['products_condition_empik'] == '7') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany7">Uszkodzone opakowanie</label>
                                 </td>
                             </tr>
                         </table>
                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('stan_produktu','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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
