<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_25" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_25;
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
        
    function dodaj_faq(id_jezyk) {
        //
        $('#BrakFaq_' + id_jezyk).hide();
        //
        ile_faq = parseInt($("#ile_faq_"+id_jezyk).val()) + 1;
        //
        $('#pytania_faq_'+id_jezyk).append('<div id="div_pytania_faq_'+ile_faq+'_'+id_jezyk+'" style="padding-bottom:8px" class="PoleFaq"></div>');
        $('#div_pytania_faq_'+ile_faq+'_'+id_jezyk).css('display','none');
        //
        $.get('ajax/dodaj_faq.php?tok=<?php echo Sesje::Token(); ?>', { ilosc: ile_faq, id: id_jezyk }, function(data) {
            $('#div_pytania_faq_'+ile_faq+'_'+id_jezyk).html(data);
            $('#div_pytania_faq_'+ile_faq+'_'+id_jezyk).find('.NaglowekLinki span.FaqIle').html( $('#pytania_faq_'+id_jezyk).find('.PoleFaq').length );
            $("#ile_faq_"+id_jezyk).val(ile_faq);
            
            $('#div_pytania_faq_'+ile_faq+'_'+id_jezyk).slideDown("fast");
        });
    }    
    
    function usun_faq(id, id_jezyk) {
        $('#div_' + id).remove();
        //
        ir = 1;
        $('#pytania_faq_'+id_jezyk).find('.PoleFaq').each(function() {
            $(this).find('.NaglowekLinki span.FaqIle').html(ir);
            ir++;
        });
        if ( $('#pytania_faq_'+id_jezyk).find('.PoleFaq').length == 0 ) {
             $('#BrakFaq_' + id_jezyk).show();
        } else {
             $('#BrakFaq_' + id_jezyk).hide();
        }        
    }       
    </script>       
    
    <div class="info_tab_content">
    
        <?php for ($w = 0; $w < $jezyk_szt; $w++) { ?>  
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
                <div id="pytania_faq_<?php echo $w; ?>">
                
                    <?php
                    $faq = array();
                
                    if ($id_produktu > 0) {  
                    
                        // pobieranie danych 
                        $zapytanie_tmp = "select distinct * from faq where faq_type = 'produkt' and faq_type_id = '" . $id_produktu . "' and language_id = '" . $ile_jezykow[$w]['id'] . "'";
                        $sqls = $db->open_query($zapytanie_tmp);
                        //
                        while ($infq = $sqls->fetch_assoc()) {
                            //
                            $faq[] = array('pytanie' => $infq['faq_question'], 'odpowiedz' => $infq['faq_reply'], 'sort' => $infq['sort']);
                            //
                        }

                    } 
                    
                    if ( count($faq) > 0 ) {
                        //
                        $l = 1;
                        foreach ( $faq as $pozycja_faq ) {
                          //
                          ?>
                          
                          <div id="div_pytania_faq_<?php echo $l; ?>_<?php echo $w; ?>" style="padding-bottom:8px" class="PoleFaq">
                          
                              <div class="NaglowekLinki">
                                  Pytanie i odpowiedź nr <span class="FaqIle"><?php echo $l; ?></span>
                                  <span style="float:right;margin:1px" class="usun_plik TipIkona" onclick="usun_faq('pytania_faq_<?php echo $l; ?>_<?php echo $w; ?>','<?php echo $w; ?>')" data-plik="pytania_faq_<?php echo $l; ?>_<?php echo $w; ?>"><b>Usuń pozycję</b></span>
                              </div>
                              
                              <p>
                                <label for="pytanie_<?php echo $l; ?>_<?php echo $w; ?>">Pytanie:</label>
                                <input type="text" name="pytanie_<?php echo $l; ?>_<?php echo $w; ?>" id="pytanie_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $pozycja_faq['pytanie']; ?>" />
                              </p> 

                              <p>
                                <label for="pytanie_sort_<?php echo $l; ?>_<?php echo $w; ?>">Sortowanie:</label>
                                <input type="text" name="pytanie_sort_<?php echo $l; ?>_<?php echo $w; ?>" id="pytanie_sort_<?php echo $l; ?>_<?php echo $w; ?>" class="calkowita" size="5" value="<?php echo $pozycja_faq['sort']; ?>" />
                              </p> 
                              
                              <p>
                                <label for="odpowiedz_<?php echo $l; ?>_<?php echo $w; ?>">Odpowiedź:</label>
                                <textarea name="odpowiedz_<?php echo $l; ?>_<?php echo $w; ?>" id="odpowiedz_<?php echo $l; ?>_<?php echo $w; ?>" rows="3" cols="80"><?php echo $pozycja_faq['odpowiedz']; ?></textarea>
                              </p> 

                          </div>
                          
                          <?php
                          //   
                          $l++;
                        }
                        //
                        unset($l);
                        //
                    } ?>                    
                    
                    <div class="maleInfo" id="BrakFaq_<?php echo $w; ?>" <?php echo ((count($faq) > 0) ? 'style="display:none"' : ''); ?>>Brak pytań i odpowiedzi do produktu ...</div>    
                
                </div>
                
                <input value="<?php echo count($faq); ?>" type="hidden" name="ile_faq_<?php echo $w; ?>" id="ile_faq_<?php echo $w; ?>" />
                
                <div style="padding:10px">
                    <span class="dodaj" onclick="dodaj_faq(<?php echo $w; ?>)" style="cursor:pointer">dodaj nową pozycję</span>
                </div>                   

            </div>
            
            <?php

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp);
            }    
                        
        }          
        ?>
        
    </div>

</div>  

<?php } ?>