<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_1" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_1;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\',\'opis_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
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
                $products_description = (isset($opis['products_description']) ? $opis['products_description'] : '' );
                //
              } else {
                //
                $products_description = '';
                //
            }
            ?>           

            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
                <?php if ( $w == 0 && INTEGRACJA_OPENAI_WLACZONY == 'tak' && $id_produktu > 0 ) { ?>
                
                <div class="OknoAIOpis">
                
                    <div class="RozwinAI OpisAI">Opis AI</div>

                </div>
                
                <script>
                $(document).ready(function() {
                  
                   $('.OpisAI').click(function() {
                       //
                       $('#ekr_preloader').css('display','block');
                       //
                       $.get('ajax/skryptai.php', { tok: $('#tok').val(), id: <?php echo $id_produktu; ?>, nr_edytora: '<?php echo $w + $liczba; ?>' }, function(data) {
                           //
                           $('#ekr_preloader').css('display','none');
                           $(".OknoAIOpis").html(data);
                           $(".OknoAIOpis").hide().stop().slideDown();                           
                           //
                       });
                       //
                   });     
                   
                });
                </script>
                
                <?php } ?>
            
                <div class="edytor">
                  <textarea cols="110" style="width:100%; max-width:500px" rows="20" id="opis_<?php echo $w + $liczba; ?>" name="opis_<?php echo $w; ?>"><?php echo $products_description; ?></textarea>
                </div>
                
                <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>
              
            </div>
            <?php    

            if ($id_produktu > 0) {    
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis); 
            }

            unset($products_description); 
            
        }                    
        ?>                      
    </div>

</div>

<?php } ?>