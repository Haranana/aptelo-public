<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // kasuje rekordy w tablicy
        $db->delete_query('products_options' , " products_options_id = '".$filtr->process($_POST["id"])."'");    
        //
        // jezeli cecha ma byc obrazkowa nie moze byc w formie selecta
        if ($_POST['obrazek'] == 'true') {
            $_POST['typ'] = 'radio';
        }
        if ($_POST['typ'] == 'lista') {
            $_POST['obrazek'] = 'false';
        }        
        //
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
                    array('products_options_id',$filtr->process($_POST["id"])),
                    array('products_options_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('products_options_description',$filtr->process($_POST['edytor_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('products_options_sort_order',(int)$_POST['sort']),
                    array('products_options_images_enabled',$filtr->process($_POST['obrazek'])),
                    array('products_options_type',$filtr->process($_POST['typ'])),
                    array('products_options_filter',$filtr->process($_POST['filtr'])),
                    array('products_options_value',$filtr->process($_POST['rodzaj'])));
                    
            $sql = $db->insert_query('products_options' , $pola);
            unset($pola);            
            
            // jezeli cecha nie jest obrazkowa to poczysci w wartosciach wpisy o obrazkach - a wczesniej byla obrazkowa
            if ( $_POST['obrazki'] == 'true' && $_POST['obrazek'] == 'false' ) {
                 //
                 // poszuka jakie id jej odpowiadaja
                 $zapytanie = "select products_options_values_id from products_options_values_to_products_options where products_options_id = '".$filtr->process($_POST["id"])."'";
                 $sql = $db->open_query($zapytanie);
                
                 while ($info = $sql->fetch_assoc()) {
                    //
                    $pola = array(array('products_options_values_thumbnail',''));
                    $db->update_query('products_options_values', $pola, 'products_options_values_id = ' . $info['products_options_values_id'] . ' and language_id = ' . $ile_jezykow[$w]['id']);
                    unset($pola);
                    //                   
                 }
                
                 $db->close_query($sql);
                 unset($info, $zapytanie);
            }
                
        }      

        Funkcje::PrzekierowanieURL('cechy.php?id_cechy='.$filtr->process($_POST["id"]));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#cechyForm").validate({
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

          <form action="cechy/cechy_nazwy_edytuj.php" method="post" id="cechyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <div class="pozycja_edytowana">
            
            <?php
            
            if ( !isset($_GET['id_cechy']) ) {
                 $_GET['id_cechy'] = 0;
            }    
            
            $zapytanie = "select * from products_options where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and products_options_id = '".(int)$_GET['id_cechy']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>             
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo (int)$_GET['id_cechy']; ?>" />
                
                <input type="hidden" name="obrazki" value="<?php echo $info['products_options_images_enabled']; ?>" />
                
                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',150)">'.$ile_jezykow[$w]['text'].'</span>';
                }                    
                ?>                   
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    
                        $zap = "select * from products_options where language_id = '" . $ile_jezykow[$w]['id'] . "' and products_options_id = '".$filtr->process($_GET['id_cechy'])."'";
                        $sqls = $db->open_query($zap);  
                        $nazwa = $sqls->fetch_assoc();
                    
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required" for="nazwa_0">Nazwa cechy:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_options_name']) ? Funkcje::formatujTekstInput($nazwa['products_options_name']) : ''); ?>" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa cechy:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_options_name']) ? Funkcje::formatujTekstInput($nazwa['products_options_name']) : ''); ?>" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p>
                            
                            <div class="edytor">
                              <textarea cols="50" rows="10" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($nazwa['products_options_description']) ? $nazwa['products_options_description'] : ''); ?></textarea>
                            </div>                                 
                                        
                        </div>
                        <?php 
                        
                        $db->close_query($sqls);
                        unset($nazwa); 
                        
                    }                    
                    ?>                      
                </div>
                
                <script>
                gold_tabs('0','edytor_',150);
                </script>    

                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" name="sort" class="calkowita" size="5" value="<?php echo $info['products_options_sort_order']; ?>" id="sort" />
                </p>             

                <div id="typ_obrazek" <?php echo (($info['products_options_type'] == 'lista') ? 'style="display:none"' : ''); ?>>

                    <p>
                      <label>Czy cecha ma być wyświetlana w formie obrazków:</label>
                      <input type="radio" value="false" onclick="$('#filtr').slideDown()" name="obrazek" id="obrazek_nie" <?php echo (($info['products_options_images_enabled'] == 'false') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="obrazek_nie">nie</label>
                      <input type="radio" value="true" onclick="$('#filtr').slideUp()" name="obrazek" id="obrazek_tak" <?php echo (($info['products_options_images_enabled'] == 'true') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="obrazek_tak">tak</label>
                    </p>
                    
                </div>
                
                <p>
                  <label>Czy cecha ma być wyświetlana w filtrach w listingu produktów:</label>
                  <input type="radio" value="0" name="filtr" id="filtr_nie" <?php echo (($info['products_options_filter'] == '0') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="filtr_nie">nie</label>
                  <input type="radio" value="1" name="filtr" id="filtr_tak" <?php echo (($info['products_options_filter'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="filtr_tak">tak</label>
                </p>               
                
                <div id="filtr" <?php echo (($info['products_options_images_enabled'] == 'true') ? 'style="display:none"' : ''); ?>>

                    <p>
                      <label>Rodzaj wyświetlania cechy:</label>
                      <input type="radio" value="lista" name="typ" onclick="$('#typ_obrazek').slideUp()" id="typ_drop" <?php echo (($info['products_options_type'] == 'lista') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="typ_drop">Drop down menu<em class="TipIkona"><b>Pole listy rozwijanej</b></em></label>
                      <input type="radio" value="radio" name="typ" onclick="$('#typ_obrazek').slideDown()" id="typ_radio" <?php echo (($info['products_options_type'] == 'radio') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="typ_radio">Radio Button<em class="TipIkona"><b>Pole jednokrotnego wyboru</b></em></label>
                    </p>          

                </div>

                <p>
                  <label>Rodzaj wartości cechy:</label>
                  <input type="radio" value="kwota" name="rodzaj" id="rodzaj_kwota" <?php echo (($info['products_options_value'] == 'kwota') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="rodzaj_kwota">kwota<em class="TipIkona"><b>Wartość cechy będzie w formie kwotowej - będzie dodawana lub odejmowana od ceny podstawowej produktu</b></em></label>
                  <input type="radio" value="procent" name="rodzaj" id="rodzaj_procent" <?php echo (($info['products_options_value'] == 'procent') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="rodzaj_procent">procent<em class="TipIkona"><b>Wartość cechy będzie w formie procentowej - będzie dodawana lub odejmowana od ceny podstawowej produktu i obliczona procentowo od ceny podstawowej</b></em></label>
                </p>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','cechy');">Powrót</button>   
                </div>  

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>   

            </div>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}