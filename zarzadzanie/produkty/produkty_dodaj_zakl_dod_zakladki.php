<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_7" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    //
    $licznik_zakladek = $tab_7;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'dod_zakladka_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
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
                $zapytanie_tmp = "select distinct * from products_info where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_info_id = '1'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc(); 
                //
                $products_info_name = ( isset($opis['products_info_name']) ? $opis['products_info_name'] : '' );
                $products_info_description = ( isset($opis['products_info_description']) ? $opis['products_info_description'] : '' );
                //
              } else {
                //
                $products_info_name = '';
                $products_info_description = '';
                //
            }
            ?> 
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
              <p>
                <label for="nazwa_zakladki_1_<?php echo $w; ?>">Nazwa dodatkowej zakładki zakładki:</label>
                <input type="text" name="nazwa_zakladki_1_<?php echo $w; ?>" id="nazwa_zakladki_1_<?php echo $w; ?>" size="80" value="<?php echo $products_info_name; ?>" />
              </p>                                       

              <div class="edytor" style="margin-left:0px">
                <p>
                    <textarea cols="110" style="width:750px" rows="20" id="dod_zakladka_<?php echo $w + $liczba; ?>" name="dod_zakladka_1_<?php echo $w; ?>"><?php echo $products_info_description; ?></textarea>
                </p>
              </div>
                
            </div>
            <?php 

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis);
            }
            unset($products_info_name, $products_info_description);      
                        
        }                    
        ?>                      
    </div>
    
</div>                        

<div id="zakl_id_8" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    //
    $licznik_zakladek = $tab_8;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'dod_zakladka_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
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
                $zapytanie_tmp = "select distinct * from products_info where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_info_id = '2'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc(); 
                //
                $products_info_name = ( isset($opis['products_info_name']) ? $opis['products_info_name'] : '' );
                $products_info_description = ( isset($opis['products_info_description']) ? $opis['products_info_description'] : '' );
                //
              } else {
                //
                $products_info_name = '';
                $products_info_description = '';
                //
            }
            ?> 
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
              <p>
                <label for="nazwa_zakladki_2_<?php echo $w; ?>">Nazwa dodatkowej zakładki zakładki:</label>
                <input type="text" name="nazwa_zakladki_2_<?php echo $w; ?>" id="nazwa_zakladki_2_<?php echo $w; ?>" size="80" value="<?php echo $products_info_name; ?>" />
              </p>                                       

              <div class="edytor" style="margin-left:0px">
                <p>
                    <textarea cols="110" style="width:750px" rows="20" id="dod_zakladka_<?php echo $w + $liczba; ?>" name="dod_zakladka_2_<?php echo $w; ?>"><?php echo $products_info_description; ?></textarea>
                </p>
              </div>
                
            </div>
            <?php   

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis);
            }
            unset($products_info_name, $products_info_description);             
                                    
        }                    
        ?>                      
    </div>
    
</div>

<div id="zakl_id_9" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    //
    $licznik_zakladek = $tab_9;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'dod_zakladka_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
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
                $zapytanie_tmp = "select distinct * from products_info where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_info_id = '3'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc(); 
                //
                $products_info_name = ( isset($opis['products_info_name']) ? $opis['products_info_name'] : '' );
                $products_info_description = ( isset($opis['products_info_description']) ? $opis['products_info_description'] : '' );
                //
              } else {
                //
                $products_info_name = '';
                $products_info_description = '';
                //
            }
            ?> 
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
              <p>
                <label for="nazwa_zakladki_3_<?php echo $w; ?>">Nazwa dodatkowej zakładki zakładki:</label>
                <input type="text" name="nazwa_zakladki_3_<?php echo $w; ?>" id="nazwa_zakladki_3_<?php echo $w; ?>" size="80" value="<?php echo $products_info_name; ?>" />
              </p>                                       

              <div class="edytor" style="margin-left:0px">
                <p>
                    <textarea cols="110" style="width:750px" rows="20" id="dod_zakladka_<?php echo $w + $liczba; ?>" name="dod_zakladka_3_<?php echo $w; ?>"><?php echo $products_info_description; ?></textarea>
                </p>
              </div>
                
            </div>
            <?php   

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis);
            }
            unset($products_info_name, $products_info_description);             
                                    
        }                    
        ?>                      
    </div>
    
</div>

<div id="zakl_id_10" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    //
    $licznik_zakladek = $tab_10;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'dod_zakladka_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
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
                $zapytanie_tmp = "select distinct * from products_info where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_info_id = '4'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc(); 
                //
                $products_info_name = ( isset($opis['products_info_name']) ? $opis['products_info_name'] : '' );
                $products_info_description = ( isset($opis['products_info_description']) ? $opis['products_info_description'] : '' );
                //
              } else {
                //
                $products_info_name = '';
                $products_info_description = '';
                //
            }
            ?> 
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
              <p>
                <label for="nazwa_zakladki_4_<?php echo $w; ?>">Nazwa dodatkowej zakładki zakładki:</label>
                <input type="text" name="nazwa_zakladki_4_<?php echo $w; ?>" id="nazwa_zakladki_4_<?php echo $w; ?>" size="80" value="<?php echo $products_info_name; ?>" />
              </p>                                       

              <div class="edytor" style="margin-left:0px">
                <p>
                    <textarea cols="110" style="width:750px" rows="20" id="dod_zakladka_<?php echo $w + $liczba; ?>" name="dod_zakladka_4_<?php echo $w; ?>"><?php echo $products_info_description; ?></textarea>
                </p>
              </div>
                
            </div>
            <?php   

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis);
            }
            unset($products_info_name, $products_info_description);             
                                    
        }                    
        ?>                      
    </div>
    
</div>

<?php } ?>
