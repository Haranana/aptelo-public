<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
    
    $ile_jezykow = Funkcje::TablicaJezykow();
    $jezyk_szt = count($ile_jezykow);
 
    ?>
    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = (int)$_GET['id_tab'];
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                      
    ?>                   
    </div>
    
    <div style="clear:both"></div>
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {  
            ?>   
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
            <?php for ($l = 1; $l < 5; $l++) { ?>
            
                <?php
                if ($id_produktu > 0) {                
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from products_link where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_link_id = '" . $l . "'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        //
                        $opis = $sqls->fetch_assoc();
                        //
                        $products_link_name = $opis['products_link_name'];
                        $products_link_description = $opis['products_link_description'];
                        $products_link_url = $opis['products_link_url'];  
                        //
                    } else {
                        //
                        $products_link_name = '';
                        $products_link_description = '';
                        $products_link_url = '';    
                        //
                    }
                    //
                  } else {
                    //
                    $products_link_name = '';
                    $products_link_description = '';
                    $products_link_url = '';                
                    //
                }
                ?>
                
                <div class="NaglowekLinki">Link nr <span><?php echo $l; ?></span></div>

                <p>
                  <label for="link_<?php echo $l; ?>_<?php echo $w; ?>">Nazwa linku:</label>
                  <input type="text" name="link_<?php echo $l; ?>_<?php echo $w; ?>" id="link_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $products_link_name; ?>" />
                </p> 
                
                <?php if ($w == 0) { ?>                
                
                <p>
                  <label for="link_url_<?php echo $l; ?>">Adres URL:</label>
                  <input type="text" name="link_url_<?php echo $l; ?>" id="link_url_<?php echo $l; ?>" size="80" value="<?php echo $products_link_url; ?>" />
                </p>              
                <?php } ?>
                
                <p>
                  <label for="link_opis_<?php echo $l; ?>_<?php echo $w; ?>">Opis linku:</label>
                  <textarea cols="70" rows="3" name="link_opis_<?php echo $l; ?>_<?php echo $w; ?>" id="link_opis_<?php echo $l; ?>_<?php echo $w; ?>"><?php echo $products_link_description; ?></textarea>
                </p>                 

                <?php
                if ($id_produktu > 0) {  
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $opis);
                }
                unset($products_link_name, $products_link_url, $products_link_target);
                
            } ?>
                
            </div>
            <?php 
        }                    
        ?>                      
    </div>
    
    <script>
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>', '');
    </script>      

<?php } ?>