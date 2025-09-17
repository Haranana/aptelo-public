<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_4" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php                 
    $licznik_zakladek = $tab_4;                       
    echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">Wszystkie języki</span>';
    $licznik_zakladek++;
    //
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                    
    ?>                      
    </div>
    
    <div style="clear:both"></div>
    
    <script>
    var katalogZdjec = '<?php echo KATALOG_ZDJEC; ?>';
    </script>
    
    <script type="text/javascript" src="produkty/dodatkowe_pola.js"></script>       
    
    <div class="info_tab_content">
    
        <?php for ($w = -1; $w < $jezyk_szt; $w++) { ?>    
    
        <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
          
            <br />

            <div class="CechyNaglowek">Wybierz pole do dodania</div>
            
            <table class="CechyProd">
                <tr>
                    <td>Nazwa pola:</td>
                    
                    <?php $jest_pole = false; ?>
                    
                    <td id="WyborPola_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>">
                        <?php
                        $dod_pola = "select products_extra_fields_name, products_extra_fields_id from products_extra_fields where languages_id = '".(($w > -1) ? $ile_jezykow[$w]['id'] : 0)."' order by products_extra_fields_order, products_extra_fields_name";
                        $sqlc = $db->open_query($dod_pola);
                        //
                        $id_domyslne = 0;
                        $tablica = array();
                        //
                        while ($pole = $sqlc->fetch_assoc()) {
                            if ($id_domyslne == 0) {
                                $id_domyslne = $pole['products_extra_fields_id'];
                            }
                            $tablica[] = array('id' => $pole['products_extra_fields_id'], 'text' => $pole['products_extra_fields_name']);
                            //
                            $jest_pole = true;
                        }
                        $db->close_query($sqlc);
                        
                        echo Funkcje::RozwijaneMenu('dodatkowe_pole', $tablica, $id_domyslne, 'style="width:230px" id="id_dod_pola_' . (($w > -1) ? $ile_jezykow[$w]['id'] : 0) . '"' . (($jest_pole == false) ? ' disabled="disabled"' : ''));

                        unset($dod_pola, $tablica, $id_domyslne);
                        ?>
                    </td>

                    <td class="InfoPoleDodaj" <?php echo (($jest_pole == false) ? 'style="display:none"' : ''); ?>>
                        <em class="TipChmurka"><b>Dodaj pole do produktu</b><img src="obrazki/rozwin.png" style="cursor:pointer" onclick="dodaj_dodatkowe_pole(<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>)" alt="Dodaj pole do produktu" /></em>
                    </td>
                    
                    <?php unset($jest_pole); ?>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td class="DodaniePola"><span class="dodaj" onclick="dodaj_nowe_pole(<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>)">dodaj pole</span></td>
                    <td>&nbsp;</td>
                </tr>
            </table>            

            <br />
            
            <div id="OknoDodawaniaNowegoPola_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>"></div>

            <?php
            if ($id_produktu > 0) {
            
                $dod_znacznik = '';
                if ( $w < 0 ) {
                     $dod_znacznik = '999_';
                }
            
                $zapytanie_pola = "select * from products_to_products_extra_fields pepf
                                      right join products_extra_fields pef on pepf.products_extra_fields_id = pef.products_extra_fields_id
                                           where pepf.products_id = '" . $id_produktu . "' and 
                                                 pef.languages_id = '" . (($w > -1) ? $ile_jezykow[$w]['id'] : 0) . "'
                                        order by pef.products_extra_fields_order, pef.products_extra_fields_name";

                $sqls = $db->open_query($zapytanie_pola);
                //
                if ($db->ile_rekordow($sqls) > 0) {
                    //
                    while ($infs = $sqls->fetch_assoc()) {
                        //
                        if ( $infs['products_extra_fields_image'] == 0 ) {
                            //
                            ?>
                            
                            <div class="pole_dodatkowe_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>" id="pole_nazwa_<?php echo $infs['products_extra_fields_id']; ?>">
                            
                                <div class="NaglowekLinki">
                                    <span class="PoleTekst"><?php echo $infs['products_extra_fields_name']; ?></span><em class="TipIkona"><b>Dodatkowe pole w formie <?php echo (($infs['products_extra_fields_number'] == '0') ? 'tekstu' : 'numerycznej'); ?></b></em>
                                    <span class="UsunPole rg TipChmurka" onclick="usun_pole(<?php echo $infs['products_extra_fields_id']; ?>)"><b>Usuń pole</b></span>
                                </div>
                                
                                <p>

                                  <?php if ( $infs['products_extra_fields_number'] == '0' ) { ?>
                                  
                                  <label for="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_1">Wartość nr 1:</label>
                                  <input type="text" size="80" onchange="usun_slownik('<?php echo $infs['products_extra_fields_id']; ?>_1')" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_1" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>_1" value="<?php echo Funkcje::formatujTekstInput($infs['products_extra_fields_value']); ?>" />
                                  
                                  <?php } else { ?>
                                  
                                  <label for="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_1">Wartość:</label>
                                  <input type="text" size="20" class="kropka" onchange="usun_slownik('<?php echo $infs['products_extra_fields_id']; ?>_1')" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_1" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>_1" value="<?php echo number_format((float)$infs['products_extra_fields_value'],2,'.',''); ?>" />
                                  
                                  <?php } ?>
                                  
                                  <span class="SlownikPola TipChmurka" onclick="pokaz_slownik('<?php echo $infs['products_extra_fields_id']; ?>_1')"><b>Wyświetl / ukryj słownik dla pola opisowego</b></span> <br />
                                  <span class="PozycjeSlownika" id="slownik_<?php echo $infs['products_extra_fields_id']; ?>_1"></span>
                                </p>
                                
                                <?php if ( $infs['products_extra_fields_number'] == '0' ) { ?>
                                
                                <p>
                                  <label for="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_2">Wartość nr 2:</label>                                  
                                  <input type="text" size="80" onchange="usun_slownik('<?php echo $infs['products_extra_fields_id']; ?>_2')" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_2" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>_2" value="<?php echo Funkcje::formatujTekstInput($infs['products_extra_fields_value_1']); ?>" />
                                  <span class="PozycjeSlownika" id="slownik_<?php echo $infs['products_extra_fields_id']; ?>_2"></span>
                                </p>                                
                                
                                <?php } else { ?>
                                  
                                <input type="hidden" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_2" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>_2" value="" />
                                
                                <?php } ?>
                                
                                <?php if ( $infs['products_extra_fields_number'] == '0' ) { ?>
                                
                                <p>
                                  <label for="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_3">Wartość nr 3:</label>
                                  <input type="text" size="80" onchange="usun_slownik('<?php echo $infs['products_extra_fields_id']; ?>_3')" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_3" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>_3" value="<?php echo Funkcje::formatujTekstInput($infs['products_extra_fields_value_2']); ?>" />
                                  <span class="SlownikPola TipChmurka" onclick="pokaz_slownik('<?php echo $infs['products_extra_fields_id']; ?>_3')"><b>Wyświetl / ukryj słownik dla pola opisowego</b></span> <br />
                                  <span class="PozycjeSlownika" id="slownik_<?php echo $infs['products_extra_fields_id']; ?>_3"></span>
                                </p>   
                                
                                <?php } else { ?>
                                  
                                <input type="hidden" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>_3" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>_3" value="" />
                                
                                <?php } ?>
                                
                                <?php if ( $infs['products_extra_fields_number'] == '0' ) { ?>
                                
                                <p>
                                  <label for="pole_url_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>">Adres URL:</label>
                                  <input type="text" name="pole_url_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>" id="pole_url_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_link']; ?>" /><em class="TipIkona"><b>Wpisz adres www jeżeli dodatkowe pole ma być linkiem</b></em>
                                </p>
                                
                                <?php } else { ?>
                                
                                <input type="hidden" name="pole_url_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>" value="" />
                                
                                <?php } ?>
                                
                            </div>
                            
                          <?php } else { ?>
                          
                            <div class="pole_dodatkowe_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>" id="pole_nazwa_<?php echo $infs['products_extra_fields_id']; ?>">
                          
                                <div class="NaglowekLinki">
                                    <span class="PoleObrazek"><?php echo $infs['products_extra_fields_name']; ?></span><em class="TipIkona"><b>Dodatkowe pole w formie grafiki</b></em>
                                    <span class="UsunPole rg TipChmurka" onclick="usun_pole(<?php echo $infs['products_extra_fields_id']; ?>)"><b>Usuń pole</b></span>
                                </div>
                                
                                <p>
                                  <label for="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>">Grafika / zdjęcie:</label>      
                                  <input type="text" onchange="usun_slownik(<?php echo $infs['products_extra_fields_id']; ?>)" name="pole_<?php echo $dod_znacznik; ?>zdjecie_<?php echo $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_value']; ?>" class="ObrazekPole" ondblclick="openFileBrowser('foto_pole_<?php echo $infs['products_extra_fields_id']; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>" autocomplete="off" /> <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="UsunZdjeciePola TipChmurka" data-foto="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>"><b>Usuń przypisane zdjęcie</b></span>
                                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto_pole_<?php echo $infs['products_extra_fields_id']; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                  <span class="SlownikPola TipChmurka" onclick="pokaz_slownik(<?php echo $infs['products_extra_fields_id']; ?>)"><b>Wyświetl / ukryj słownik dla pola opisowego</b></span> <br />
                                  <span class="PozycjeSlownika" id="slownik_<?php echo $infs['products_extra_fields_id']; ?>"></span>                                  
                                </p>
                                
                                <p>
                                  <label for="pole_url_<?php echo $dod_znacznik . 'zdjecie_' . $infs['products_extra_fields_id']; ?>">Adres URL:</label>
                                  <input type="text" name="pole_url_<?php echo $dod_znacznik . 'zdjecie_' . $infs['products_extra_fields_id']; ?>" id="pole_url_<?php echo $dod_znacznik . 'zdjecie_' . $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_link']; ?>" /> <em class="TipIkona"><b>Wpisz adres www jeżeli dodatkowe pole ma być linkiem</b></em>                     
                                </p>      

                                <div id="divfoto_pole_<?php echo $infs['products_extra_fields_id']; ?>" style="padding-left:10px; display:none">
                                  <label>Zdjęcie:</label>
                                  <span id="fofoto_pole_<?php echo $infs['products_extra_fields_id']; ?>">
                                      <span class="zdjecie_tbl">
                                          <img src="obrazki/_loader_small.gif" alt="" />
                                      </span>
                                  </span> 

                                  <?php if (!empty($infs['products_extra_fields_value'])) { ?>
                                  
                                  <script>           
                                  pokaz_obrazek_ajax('foto_pole_<?php echo $infs['products_extra_fields_id']; ?>', '<?php echo $infs['products_extra_fields_value']; ?>')
                                  </script>       
                                  
                                  <?php } ?> 
                                  
                                </div> 

                            </div>

                        <?php }
                        //
                    }
                    //
                }
                //
                $db->close_query($sqls);
                unset($zapytanie_pola);                                        
                
            }
            ?> 
            
            <span class="maleInfo" id="brak_pol_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>">Brak przypisanych dodatkowych pól dla tego języka</span>
            
            <script> 
            if ( $('.pole_dodatkowe_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>').length > 0 ) {
                 $('#brak_pol_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>').hide();
               } else {
                 $('#brak_pol_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>').show();
            }        
            </script> 
    
            <div id="nowe_pola_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>"></div>

        </div>
        
        <?php } ?>
    
    </div>

</div>      

<?php } ?>