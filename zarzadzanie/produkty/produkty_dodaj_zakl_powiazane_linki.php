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
    
    function dodaj_grupe_linkow(id_jezyk) {
        //
        $('#BrakLinkowPowiazanych_' + id_jezyk).hide();
        //
        ile_grup_linkow = parseInt($("#ile_grup_linkow_"+id_jezyk).val()) + 1;
        //
        $('#linki_powiazane_'+id_jezyk).append('<div id="div_grupa_linkow_powiazanych_'+ile_grup_linkow+'_'+id_jezyk+'" style="padding-bottom:8px" class="PoleLinkowPowiazanych"></div>');
        $('#div_grupa_linkow_powiazanych_'+ile_grup_linkow+'_'+id_jezyk).css('display','none');
        //
        $.get('ajax/dodaj_grupe_linkow_powiazanych.php?tok=<?php echo Sesje::Token(); ?>', { ilosc: ile_grup_linkow, id: id_jezyk, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            $('#div_grupa_linkow_powiazanych_'+ile_grup_linkow+'_'+id_jezyk).html(data);
            $('#div_grupa_linkow_powiazanych_'+ile_grup_linkow+'_'+id_jezyk).find('.NaglowekLinki span').html( $('#linki_powiazane_'+id_jezyk).find('.PoleLinkowPowiazanych').length );
            $("#ile_grup_linkow_"+id_jezyk).val(ile_grup_linkow);
            
            $('#div_grupa_linkow_powiazanych_'+ile_grup_linkow+'_'+id_jezyk).slideDown("fast");
        });
    }  
    
    function usun_grupe_linkow_powiazanych(id, id_jezyk) {
        $('#div_grupa_linkow_powiazanych_' + id).remove();
        //
        ir = 1;
        $('#linki_powiazane_'+id_jezyk).find('.PoleLinkowPowiazanych').each(function() {
            $(this).find('.NaglowekLinki span').html(ir);
            ir++;
        });
        if ( $('#linki_powiazane_'+id_jezyk).find('.PoleLinkowPowiazanych').length == 0 ) {
             $('#BrakLinkowPowiazanych_' + id_jezyk).show();
        } else {
             $('#BrakLinkowPowiazanych_' + id_jezyk).hide();
        }        
    }     

    function usun_link_powiazany(id) {
        //
        $('#link_powiazany_'+id).find('input').val('');
        //       
    }      
    </script>     
    
    <div class="info_tab_content">
    
        <?php for ($w = 0; $w < $jezyk_szt; $w++) { ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
                <div id="linki_powiazane_<?php echo $w; ?>">

                    <?php
                    //
                    $grupa_linkow_powiazanych = array();
                    //
                    if ($id_produktu > 0) {
                      
                        // pobieranie danych jezykowych
                        $zapytanie_powiazane_linki = "select distinct * from products_related_links_group where products_id = '" . $id_produktu . "' and language_id = '" . $ile_jezykow[$w]['id'] . "'";
                        $sqls = $db->open_query($zapytanie_powiazane_linki);
                        //
                        $link_powiazane = array();
                        //
                        while ($link = $sqls->fetch_assoc()) {
                            //
                            $zapytanie_powiazane_linki_tmp = "select distinct * from products_related_links where products_related_links_group_id = '" . $link['products_related_links_group_id'] . "' and language_id = '" . $ile_jezykow[$w]['id'] . "'";
                            $sqls_tmp = $db->open_query($zapytanie_powiazane_linki_tmp);
                            //
                            while ($link_tmp = $sqls_tmp->fetch_assoc()) {
                                //
                                $link_powiazane[] = array( 'nazwa' => $link_tmp['products_related_links_name'],
                                                           'foto' => $link_tmp['products_related_links_foto'],
                                                           'url' => $link_tmp['products_related_links_url'] );
                                //
                            }
                            //
                            $db->close_query($sqls_tmp); 
                            unset($zapytanie_powiazane_linki_tmp, $link_tmp);                            
                            //
                            $grupa_linkow_powiazanych[ $link['products_related_links_group_id'] ] = array( 'id' => $link['products_related_links_group_id'],
                                                                                                           'nazwa' => $link['products_related_links_group_name'],
                                                                                                           'opis' => $link['products_related_links_group_description'],
                                                                                                           'linki' => $link_powiazane);
                            //
                            unset($link_powiazane);
                            //
                        } 
                        //
                        $db->close_query($sqls); 
                        unset($zapytanie_powiazane_linki, $link);

                    }      

                    if ( count($grupa_linkow_powiazanych) > 0 ) {
                         //
                         $l = 1;
                         foreach ( $grupa_linkow_powiazanych as $grupa ) {
                             //                         
                             ?>
                             
                             <div id="div_grupa_linkow_powiazanych_<?php echo $l . '_' . $w; ?>" style="padding-bottom:8px" class="PoleLinkowPowiazanych">
                          
                                 <div class="NaglowekLinki">    
                                      Grupa linków powiązanych nr <span><?php echo $l; ?></span>
                                      <em style="float:right" class="TipChmurka"><b>Skasuj</b><img onclick="usun_grupe_linkow_powiazanych('<?php echo $l . '_' . $w; ?>', '<?php echo $w; ?>')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></em>          
                                 </div>
                                
                                 <p>
                                   <label for="grupa_linkow_powiazanych_nazwa_<?php echo $l . '_' . $w; ?>">Nazwa grupy linków:</label>
                                   <input type="text" name="grupa_linkow_powiazanych_nazwa_<?php echo $l . '_' . $w; ?>" id="grupa_linkow_powiazanych_nazwa_<?php echo $l . '_' . $w; ?>" size="80" value="<?php echo $grupa['nazwa']; ?>" />
                                 </p> 

                                 <p>
                                   <label for="grupa_linkow_powiazanych_opis_<?php echo $l . '_' . $w; ?>">Opis grupy linków:</label>
                                   <textarea name="grupa_linkow_powiazanych_opis_<?php echo $l . '_' . $w; ?>" id="grupa_linkow_powiazanych_opis_<?php echo $l . '_' . $w; ?>" rows="3" cols="50" style="width:80%"><?php echo $grupa['opis']; ?></textarea>
                                 </p>    
                                
                                 <div class="RamkaFoto">
                                
                                    <table class="TabelaLinkiPowiazane" style="margin-bottom:5px">
                                    
                                        <tr class="TabelaLinkiPowiazaneNaglowek">
                                            <td style="width:33%"><span>Nazwa linku</span></td>
                                            <td style="width:32%"><span>Zdjęcie linku</span></td>
                                            <td style="width:32%"><span>Adres URL linku</span></td>
                                            <td style="width:2%"><span>Usuń</span></td>
                                        </tr> 
                                        
                                        <?php for ( $s = 0; $s < 20; $s++ ) { ?>
                                        
                                        <tr id="link_powiazany_<?php echo $l . '_' . $w . '_' . $s; ?>">    
                                            <td>                              
                                                <input type="text" name="link_powiazany_nazwa_<?php echo $l . '_' . $w . '_' . $s; ?>" value="<?php echo ((isset($grupa['linki'][$s])) ? $grupa['linki'][$s]['nazwa'] : ''); ?>" />                 
                                            </td> 
                                            <td>                              
                                                <input type="text" name="link_powiazany_foto_<?php echo $l . '_' . $w . '_' . $s; ?>" value="<?php echo ((isset($grupa['linki'][$s])) ? $grupa['linki'][$s]['foto'] : ''); ?>" ondblclick="openFileBrowser('link_powiazany_foto_<?php echo $l . '_' . $w . '_' . $s; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="link_powiazany_foto_<?php echo $l . '_' . $w . '_' . $s; ?>" autocomplete="off" />                 
                                                <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                                <em class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('link_powiazany_foto_<?php echo $l . '_' . $w . '_' . $s; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></em>                                        
                                            </td> 
                                            <td>                              
                                                <input type="text" name="link_powiazany_adres_<?php echo $l . '_' . $w . '_' . $s; ?>" value="<?php echo ((isset($grupa['linki'][$s])) ? $grupa['linki'][$s]['url'] : ''); ?>" />                 
                                            </td> 
                                            <td>
                                                <em class="TipChmurka"><b>Skasuj</b><img onclick="usun_link_powiazany('<?php echo $l . '_' . $w . '_' . $s; ?>')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                            </td>                
                                        </tr>    

                                        <?php } ?>

                                    </table>
                                    
                                 </div>

                             </div>
                          
                             <?php
                             //
                             $l++;
                         }
                         //
                         unset($l);
                         //
                    }
                    ?>
                    
                    <div class="maleInfo" id="BrakLinkowPowiazanych_<?php echo $w; ?>" <?php echo ((count($grupa_linkow_powiazanych) > 0) ? 'style="display:none"' : ''); ?>>Brak przypisanych linków powiązanych do produktu ...</div>   

                </div>
                                    
                <input value="<?php echo count($grupa_linkow_powiazanych); ?>" type="hidden" name="ile_grup_linkow_<?php echo $w; ?>" id="ile_grup_linkow_<?php echo $w; ?>" />
                
                <div style="padding:10px">
                    <span class="dodaj" onclick="dodaj_grupe_linkow(<?php echo $w; ?>)" style="cursor:pointer">dodaj grupę linków powiązanych</span>
                </div>                   
                    
            </div>
            
        <?php } ?> 
        
    </div>
    
    <script>
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>', '', 400);
    </script>          

<?php } ?>