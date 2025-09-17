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
    
    <script>
    $(document).ready(function(){
        pokazChmurki();   
    });
    </script>     
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
            ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
            <?php for ($l = 1; $l < 11; $l++) { ?>
            
                <?php
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from products_file where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_file_id = '" . $l . "'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        //
                        $opis = $sqls->fetch_assoc();
                        //
                        $products_file_name = $opis['products_file_name'];
                        $products_file = $opis['products_file'];
                        $products_file_description = $opis['products_file_description'];
                        $products_file_login = $opis['products_file_login'];
                        //
                    } else {
                        //
                        $products_file_name = '';
                        $products_file = '';
                        $products_file_description = '';
                        $products_file_login = '';
                        //     
                    }
                    //
                  } else {
                    //
                    $products_file_name = '';
                    $products_file = '';
                    $products_file_description = '';
                    $products_file_login = '';
                    //                  
                }
                ?>            
            
                <div class="NaglowekLinki">Plik nr <span><?php echo $l; ?></span></div>
                
                <p>
                  <label for="plik_nazwa_<?php echo $l; ?>_<?php echo $w; ?>">Nazwa do wyświetlania:</label>
                  <input type="text" name="plik_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" id="plik_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $products_file_name; ?>" />
                </p>              

                <?php if ($w == 0) { ?>
                <p>
                  <label for="plik_<?php echo $l; ?>">Plik:</label>
                  <input type="text" name="plik_<?php echo $l; ?>" size="80" ondblclick="openFileAllBrowser('plik_<?php echo $l; ?>')" id="plik_<?php echo $l; ?>" value="<?php echo $products_file; ?>" autocomplete="off" />
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_plik TipChmurka" data-plik="plik_<?php echo $l; ?>"><b>Kliknij w ikonę żeby usunąć przypisany obrazek</b></span>
                </p>
                <?php } ?>
                
                <p>
                  <label for="plik_opis_<?php echo $l; ?>_<?php echo $w; ?>">Opis pliku:</label>
                  <textarea cols="70" rows="3" name="plik_opis_<?php echo $l; ?>_<?php echo $w; ?>" id="plik_opis_<?php echo $l; ?>_<?php echo $w; ?>"><?php echo $products_file_description; ?></textarea>
                </p>                
                
                <?php if ($w == 0) { ?>
                <p>
                  <label>Czy ma być widoczny dla klientów niezalogowanych:</label>
                  <input type="radio" value="1" name="plik_klient_<?php echo $l; ?>" id="plik_klient_<?php echo $l; ?>_tak" <?php echo (($products_file_login == '1' || empty($products_file_login)) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="plik_klient_<?php echo $l; ?>_tak">tak</label>
                  <input type="radio" value="0" name="plik_klient_<?php echo $l; ?>" id="plik_klient_<?php echo $l; ?>_nie" <?php echo (($products_file_login == '0') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="plik_klient_<?php echo $l; ?>_nie">nie</label>
                </p>              
                
                <?php }
               
                if ($id_produktu > 0) {
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $opis);
                }
                unset($products_file_name, $products_file, $products_file_description, $products_file_login);
                
            } ?>
                
            </div>
            <?php                    
        }   
        ?>                      
    </div>
    
    <script>
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>', '', 400);
    </script>          

<?php } ?>