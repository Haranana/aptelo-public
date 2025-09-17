<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_2" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_2;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'opis_krotki_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                    
    ?>                 
    </div>
    
    <div style="clear:both"></div>
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
        
            if ($id_produktu > 0) {    
                // pobieranie danych jezykowych
                $zapytanie_tmp = "select distinct * from products_description where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc();
                //
                $products_short_description = (isset($opis['products_short_description']) ? $opis['products_short_description'] : '' );
                //
              } else {
                //
                $products_short_description = '';
                //
            }
            ?> 
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">

                <div class="edytor">
                  <textarea cols="110" style="width:100%; max-width:500px" rows="20" id="opis_krotki_<?php echo $w + $liczba; ?>" name="opis_krotki_<?php echo $w; ?>"><?php echo $products_short_description; ?></textarea>
                </div>
                
            </div>
            <?php

            if ($id_produktu > 0) {    
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis); 
            }

            unset($products_short_description);   
            
        }                    
        ?>                      
    </div>

</div> 

<?php } ?>