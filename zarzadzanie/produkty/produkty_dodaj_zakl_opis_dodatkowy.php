<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_24" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_24;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'opis_dodatkowy_\',100,\'\',\'opis_dodatkowy_2_\')">'.$ile_jezykow[$w]['text'].'</span>';
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
                $zapytanie_tmp = "select distinct * from products_description_additional where products_id = '" . $id_produktu . "' and language_id = '" . $ile_jezykow[$w]['id'] . "'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc();
                //
                $products_info_description_1 = (isset($opis['products_info_description_1']) ? $opis['products_info_description_1'] : '' );
                $products_info_description_2 = (isset($opis['products_info_description_2']) ? $opis['products_info_description_2'] : '' );
                //
              } else {
                //
                $products_info_description_1 = '';
                $products_info_description_2 = '';
                //
            }
            ?> 
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
                <div class="CechyNaglowek" style="padding:15px 10px 0 10px">Opis wyświetlany pod nazwą produktu na karcie produktu</div>

                <div class="edytor" style="margin-top:10px">
                  <textarea cols="110" style="width:100%; max-width:500px" rows="20" id="opis_dodatkowy_<?php echo $w + $liczba; ?>" name="opis_dodatkowy_1_<?php echo $w; ?>"><?php echo $products_info_description_1; ?></textarea>
                </div>
                
                <div class="CechyNaglowek" style="padding:25px 10px 0 10px">Opis wyświetlany pod przyciskami na karcie produktu</div>
                
                <div class="edytor" style="margin-top:10px">
                  <textarea cols="110" style="width:100%; max-width:500px" rows="20" id="opis_dodatkowy_2_<?php echo $w + $liczba; ?>" name="opis_dodatkowy_2_<?php echo $w; ?>"><?php echo $products_info_description_2; ?></textarea>
                </div>                
                
            </div>
            <?php

            if ($id_produktu > 0) {    
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis); 
            }

            unset($products_info_description_1, $products_info_description_2);   
            
        }                    
        ?>                      
    </div>

</div> 

<?php } ?>