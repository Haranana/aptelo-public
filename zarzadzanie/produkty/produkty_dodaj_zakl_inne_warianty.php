<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_23" style="display:none;">

    <div class="info_content">
    
        <div class="CechyNaglowek">Wpisz tekst po jakim mają być powiązane produkty</div>
        
        <table class="InneWarianty">
        
            <tr>
                <td>                    
                    <label for="inny_wariant_text">Ciąg znaków po jakim ma następować powiązanie:</label>
                    <input type="text" size="50" name="inny_wariant_text" id="inny_wariant_text" value="<?php echo ((isset($prod['products_other_variant_text'])) ? $prod['products_other_variant_text'] : ''); ?>" />          
                </td>
            </tr>     

            <tr>
                <td>            
                    <div class="maleInfo" style="margin:0">
                        Brak wpisanej wartości powoduje brak wyszukiwania powiązań z innymi produktami.
                    </div>            
                </td>
            </tr>               

        </table>
        
        <br />
        
        <div class="CechyNaglowek">Wybierz opcję po jakiej mają być powiązane produkty:</div>
        
        <table class="InneWarianty">
        
            <?php
            $tab_warianty = array( array('wartosc' => 'nazwa_produktu', 'tekst' => 'ciąg znaków w nazwie produktu'),
                                   array('wartosc' => 'ean', 'tekst' => 'ciąg znaków w kodzie EAN'),
                                   array('wartosc' => 'id_zewnetrzne', 'tekst' => 'ciąg znaków w ID zewnętrznym'),
                                   array('wartosc' => 'nr_katalogowy', 'tekst' => 'ciąg znaków w numerze katalogowym'),
                                   array('wartosc' => 'kod_producenta', 'tekst' => 'ciąg znaków w kodzie producenta'),
                                   array('wartosc' => 'nr_referencyjny_1', 'tekst' => 'ciąg znaków w numerze referencyjnym nr 1'),
                                   array('wartosc' => 'nr_referencyjny_2', 'tekst' => 'ciąg znaków w numerze referencyjnym nr 2'),
                                   array('wartosc' => 'nr_referencyjny_3', 'tekst' => 'ciąg znaków w numerze referencyjnym nr 3'),
                                   array('wartosc' => 'nr_referencyjny_4', 'tekst' => 'ciąg znaków w numerze referencyjnym nr 4'),
                                   array('wartosc' => 'nr_referencyjny_5', 'tekst' => 'ciąg znaków w numerze referencyjnym nr 5') );
              
            $domyslna = 'ean';
            if ( isset($prod['products_other_variant_range']) && !empty($prod['products_other_variant_range']) ) {
                 $domyslna = $prod['products_other_variant_range'];
            }
            
            foreach ( $tab_warianty as $pozycja ) {
                
                echo '<tr>
                          <td>
                              <input type="radio" name="inny_wariant" id="inny_wariant_' . $pozycja['wartosc'] . '" value="' . $pozycja['wartosc'] . '" ' . (($domyslna == $pozycja['wartosc']) ? 'checked="checked"' : '') . ' />          
                              <label class="OpisFor" for="inny_wariant_' . $pozycja['wartosc'] . '">' . $pozycja['tekst'] . '</label>
                          </td>
                      </tr>';
              
            }
            
            unset($domyslna);
            ?>

        </table>
        
        <br />
        
        <div class="CechyNaglowek">Wybierz sposób powiązania:</div>
        
        <table class="InneWarianty">
        
            <tr>
                <td>
                    <input type="radio" name="inny_wariant_sposob" id="inny_wariant_dokladnie" value="dokladnie" <?php echo (((isset($prod['products_other_variant_method']) && $prod['products_other_variant_method'] == 'dokladnie') || !isset($prod['products_other_variant_method']) || empty($prod['products_other_variant_method'])) ? 'checked="checked"' : ''); ?> />          
                    <label class="OpisFor" for="inny_wariant_dokladnie">dokładnie taki sam ciąg znaków</label>
                </td>
            </tr>      
            
            <tr>
                <td>            
                    <div class="maleInfo" style="margin:0">
                        Jeżeli zostanie wybrana ta opcja to będą wyświetlane produkty, które mają dokładnie taki sam ciąg znaków. Np. jeżeli zostanie podany nr katalogowy XX-11 to będą wyszukiwane i wyświetlane produkty, które mają dokładnie taki sam numer katalogowy.
                    </div>            
                </td>
            </tr>    
            
            <tr>
                <td>
                    <input type="radio" name="inny_wariant_sposob" id="inny_wariant_fragment" value="fragment" <?php echo ((isset($prod['products_other_variant_method']) && $prod['products_other_variant_method'] == 'fragment') ? 'checked="checked"' : ''); ?> />          
                    <label class="OpisFor" for="inny_wariant_fragment">ciąg znaków jako fragment innego ciągu znaków</label>
                </td>
            </tr>      
        
            <tr>
                <td>            
                    <div class="maleInfo" style="margin:0">
                        Jeżeli zostanie wybrana ta opcja to będą wyświetlane produkty, które mają w wybranym ciągu znaków wyszukiwaną wartość. Np. jeżeli zostanie podany nr katalogowy XX-11 to będą wyświetlane produkty, które mają w numerze katalogowym ciąg znaków XX-11 - czyli np. nr kat: XX-112, YXX-11, XX-11, UXX-11322 itd.
                    </div>            
                </td>
            </tr>  
            
        </table>
        
        <br />
        
        <div class="CechyNaglowek">Zakres wyświetlanych danych:</div>
        
        <script>
        $(document).ready(function() {
          $('.InneWarianty input').change(function() {
              if ( $('#inny_wariant_nazwa').prop('checked') == false && $('#inny_wariant_cena').prop('checked') == false ) {
                   $('#inny_wariant_foto').prop('checked', true);
              }
          });          
        })
        </script>
        
        <table class="InneWarianty">
        
            <tr>
                <td>
                    <input type="checkbox" name="inny_wariant_foto" id="inny_wariant_foto" value="1" <?php echo (((isset($prod['products_other_variant_image']) && $prod['products_other_variant_image'] == '1') || !isset($prod['products_other_variant_image'])) ? 'checked="checked"' : ''); ?> />          
                    <label class="OpisFor" for="inny_wariant_foto">wyświetlaj zdjęcie produktu</label>
                </td>
            </tr>
            
            <tr>
                <td>
                    <input type="checkbox" name="inny_wariant_nazwa" id="inny_wariant_nazwa" value="1" <?php echo ((isset($prod['products_other_variant_name']) && $prod['products_other_variant_name'] == '1') ? 'checked="checked"' : ''); ?> />          
                    <label class="OpisFor" for="inny_wariant_nazwa">wyświetlaj nazwę produktu</label>
                </td>
            </tr>   

            <tr>
                <td style="padding-left:35px">
                    <input type="radio" name="inny_wariant_nazwa_typ" value="1" id="inny_wariant_nazwa_typ_pelna" <?php echo ((isset($prod['products_other_variant_name_type']) && $prod['products_other_variant_name_type'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="inny_wariant_nazwa_typ_pelna">pełna nazwa</label>
                    <input type="radio" name="inny_wariant_nazwa_typ" value="0" id="inny_wariant_nazwa_typ_krotka" <?php echo (((isset($prod['products_other_variant_name_type']) && $prod['products_other_variant_name_type'] == '0') || !isset($prod['products_other_variant_name_type'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="inny_wariant_nazwa_typ_krotka">krótka nazwa (jeżeli jest uzupełniona)</label>
                </td>
            </tr>   
            
            <tr>
                <td>
                    <input type="checkbox" name="inny_wariant_cena" id="inny_wariant_cena" value="1" <?php echo ((isset($prod['products_other_variant_price']) && $prod['products_other_variant_price'] == '1') ? 'checked="checked"' : ''); ?> />          
                    <label class="OpisFor" for="inny_wariant_cena">wyświetlaj cenę produktu</label>
                </td>
            </tr>   
            
        </table>
        
    </div>

</div>      

<?php } ?>