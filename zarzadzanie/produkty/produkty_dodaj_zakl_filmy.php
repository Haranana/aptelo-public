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
    
    <div class="info_tab">
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
            
            <?php for ($l = 1; $l < 5; $l++) { ?>
            
                <?php
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from products_film where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '" . $l . "'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        //
                        $opis = $sqls->fetch_assoc();
                        //
                        $products_flv_name = $opis['products_film_name'];
                        $products_flv_file = $opis['products_film_file'];
                        $products_flv_description = $opis['products_film_description'];
                        $products_flv_width = $opis['products_film_width'];
                        $products_flv_height = $opis['products_film_height'];
                        //
                    } else {
                        //
                        $products_flv_name = '';
                        $products_flv_file = '';
                        $products_flv_description = '';
                        $products_flv_width = '';
                        $products_flv_height = '';
                        // 
                    }
                    //
                  } else {
                    //
                    $products_flv_name = '';
                    $products_flv_file = '';
                    $products_flv_description = '';
                    $products_flv_width = '';
                    $products_flv_height = '';
                    //                  
                }
                ?>            
            
                <div class="NaglowekLinki">Klip filmowy nr <span><?php echo $l; ?></span></div>

                <p>
                  <label for="flv_nazwa_<?php echo $l; ?>_<?php echo $w; ?>">Nazwa filmu:</label>
                  <input type="text" name="flv_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" id="flv_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $products_flv_name; ?>" />
                </p> 

                <?php if ($w == 0) { ?>                
                <p>
                  <label for="flv_plik_<?php echo $l; ?>">Plik filmu:</label>
                  <input type="text" name="flv_plik_<?php echo $l; ?>" size="80" ondblclick="openFileBrowser('flv_plik_<?php echo $l; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="flv_plik_<?php echo $l; ?>" value="<?php echo $products_flv_file; ?>" autocomplete="off" />
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_plik TipChmurka" data-plik="flv_plik_<?php echo $l; ?>"><b>Usuń przypisany plik</b></span>
                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('flv_plik_<?php echo $l; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                </p>        
                <?php } ?>
                
                <p>
                  <label for="flv_opis_<?php echo $l; ?>_<?php echo $w; ?>">Opis filmu:</label>
                  <textarea cols="70" rows="4" name="flv_opis_<?php echo $l; ?>_<?php echo $w; ?>" id="flv_opis_<?php echo $l; ?>_<?php echo $w; ?>"><?php echo $products_flv_description; ?></textarea>
                </p> 

                <?php if ($w == 0) { ?>
                <p>
                  <label for="flv_szerokosc_<?php echo $l; ?>">Szerokość klipu w pikselach:</label>
                  <input type="text" name="flv_szerokosc_<?php echo $l; ?>" id="flv_szerokosc_<?php echo $l; ?>" class="calkowita" size="5" value="<?php echo $products_flv_width; ?>" />
                </p>   

                <p>
                  <label for="flv_wysokosc_<?php echo $l; ?>">Wysokość klipu w pikselach:</label>
                  <input type="text" name="flv_wysokosc_<?php echo $l; ?>" id="flv_wysokosc_<?php echo $l; ?>" class="calkowita" size="5" value="<?php echo $products_flv_height; ?>" />
                  
                  <span data-nr="<?php echo $l; ?>" class="PrzeliczProporcje PrzeliczProporcjeVideo">oblicz wysokość w stosunku do szerokości (propocja 16:9)</span>
                </p>
                <?php } ?>
                
                <?php
               
                if ($id_produktu > 0) {
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $opis);
                }
                unset($products_flv_name, $products_flv_url, $products_flv_description, $products_flv_width, $products_flv_height);
                
            } ?>
                
            </div>
            <?php                    
        }   
        ?>                      
    </div>

    <script>
    $(document).ready(function(){
        //
        $('.PrzeliczProporcjeVideo').click(function() {
            var nr = $(this).attr('data-nr');
            if ( parseInt($('#flv_szerokosc_' + nr).val()) > 0 ) {
                 propr = parseInt($('#flv_szerokosc_' + nr).val()) * 0.5625;
                 $('#flv_wysokosc_' + nr).val(parseInt(propr));
            }
        });
        //
    });
    //    
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>');
    </script>  

<?php } ?>