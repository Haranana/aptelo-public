<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_19" style="display:none;">

    <div class="info_content">
    
        <div class="CechyNaglowek">Wybierz dodatkowe pola tekstowe jakie mają być dostępne w <?php echo (($zestaw) ? 'zestawie' : 'produkcie'); ?></div>
        
        <div class="CechyInfo">
            <div class="ostrzezenie">
                Pola będą wyświetlane we wszystkich wersjach językowych sklepu.
            </div>
        </div>    
    
        <?php
        // utworzy tablice z polami jakie sa przypisane do produktu
        $przypisanePolaProduktu = array();
        
        if ($id_produktu > 0) {
        
            $zapytanie_pola = "select products_text_fields_id from products_to_text_fields where products_id = '" . $id_produktu . "'";
            $sqls = $db->open_query($zapytanie_pola);
            //
            while ($infs = $sqls->fetch_assoc()) {
                $przypisanePolaProduktu[] = $infs['products_text_fields_id'];
            }
            $db->close_query($sqls);
            unset($zapytanie_pola);    
        
        }
        
        // lista dostepnych pol
        $zapytanie_pola = "select * from products_text_fields pt, products_text_fields_info ptd where pt.products_text_fields_id = ptd.products_text_fields_id and ptd.languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by pt.products_text_fields_order";
        $sqls = $db->open_query($zapytanie_pola);
        //
        echo '<div id="PolaTekstoweLista">';
        //
        while ($infs = $sqls->fetch_assoc()) {
            //
            ?>
            <table class="PolaTxt"><tr>

              <?php
              // typ pola
              switch( $infs['products_text_fields_type'] ) {
                  case 0: $typ_pola = 'pole tekstowe <b>Input</b>'; break;
                  case 1: $typ_pola = 'pole tekstowe <b>Textarea</b>'; break;
                  case 2: $typ_pola = 'pole z możliwością <b>Wgrania pliku</b>'; break;
                  case 3: $typ_pola = 'pole z możliwością <b>Wyboru daty</b>'; break;
              }              
              ?>
              
              <td><input type="checkbox" name="pole_txt_<?php echo $infs['products_text_fields_id']; ?>" id="pole_txt_<?php echo $infs['products_text_fields_id']; ?>" value="<?php echo $infs['products_text_fields_id']; ?>" <?php echo ((in_array((string)$infs['products_text_fields_id'], $przypisanePolaProduktu)) ? 'checked="checked"' : ''); ?> />
              
              <label class="OpisFor" for="pole_txt_<?php echo $infs['products_text_fields_id']; ?>"><?php echo $infs['products_text_fields_name'] . ' - <span>' . $typ_pola . '</span>'; ?></label></td>
              
              <?php
              unset($typ_pola);
              ?>

            </tr></table>
            <?php
            //
        }
        //
        echo '</div>';
        //
        $db->close_query($sqls);
        unset($zapytanie_pola, $przypisanePolaProduktu);                                                                          
        ?>             

        <div class="DodaniePolaTekstowego"><span class="dodaj" onclick="dodaj_nowe_pole_tekstowe(<?php echo $id_produktu; ?>)">dodaj pole</span></div>
        
        <div id="OknoDodawaniaNowegoPolaTekstowego"></div>
        
    </div>

</div>      

<?php } ?>