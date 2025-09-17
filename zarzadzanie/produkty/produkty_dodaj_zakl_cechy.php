<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_5" style="display:none;">

    <div class="info_content">

        <?php 
        if ($id_produktu > 0 && $zadanieDuplikacja == false) {
            $id_rand = $id_produktu;
          } else {
            $id_rand = 900000000 + rand(11111,99999); 
        }
        ?>
        <input type="hidden" id="id_unikalne" name="id_unikalne" value="<?php echo $id_rand; ?>" />
        
        <?php
        // czyszczenie cech ze starych porzuconych wartosci
        if (!isset($_POST['akcja'])) {
            $db->delete_query('products_attributes' , " products_id > 900000000");
            $db->delete_query('products_stock' , " products_id > 900000000");
        }

        // jezeli jest duplikacja to kopiuje tablice cech
        if ($zadanieDuplikacja == true && !isset($_POST['akcja'])) {
            //
            // kopiowanie tablicy products_attributes
            //
            $cechyKopiowane = "select distinct * from products_attributes where products_id = '" . (int)$id_produktu . "'";
            $sqlc = $db->open_query($cechyKopiowane); 
            while ($cecha = $sqlc->fetch_assoc()) {            
                $pola = array(
                        array('products_id',(int)$id_rand),
                        array('options_id',(int)$cecha['options_id']),
                        array('options_values_id',(int)$cecha['options_values_id']),
                        array('options_values_price',(float)$cecha['options_values_price']),
                        array('options_values_tax',(float)$cecha['options_values_tax']),
                        array('options_values_price_tax',(float)$cecha['options_values_price_tax']),
                        array('price_prefix',$cecha['price_prefix']),
                        array('options_values_weight',(float)$cecha['options_values_weight'])                       
                        );        
                $db->insert_query('products_attributes', $pola);
                unset($pola);            
            }
            //
            // kopiowanie tablicy products_stock
            //
            $cechyKopiowane = "select distinct * from products_stock where products_id = '" . (int)$id_produktu . "'";
            $sqlc = $db->open_query($cechyKopiowane); 
            while ($cecha = $sqlc->fetch_assoc()) {            
                $pola = array(
                        array('products_id',(int)$id_rand),
                        array('products_stock_attributes',$cecha['products_stock_attributes']),
                        array('products_stock_quantity',(float)$cecha['products_stock_quantity']),
                        array('products_stock_availability_id',(int)$cecha['products_stock_availability_id']),
                        array('products_stock_shipping_time_id',(int)$cecha['products_stock_shipping_time_id']),
                        array('products_stock_model',$cecha['products_stock_model']),
                        array('products_stock_ean',$cecha['products_stock_ean']),
                        array('products_stock_size',$cecha['products_stock_size']),
                        array('products_stock_price',(float)$cecha['products_stock_price']),
                        array('products_stock_tax',(float)$cecha['products_stock_tax']),
                        array('products_stock_price_tax',(float)$cecha['products_stock_price_tax']),
                        array('products_stock_old_price',(float)$cecha['products_stock_old_price']),
                        array('products_stock_retail_price',(float)$cecha['products_stock_retail_price']),
                        array('products_stock_image',$cecha['products_stock_image'])
                        );        
                        
                // ceny
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    //
                    $pola[] = array('products_stock_price_'.$x, (float)$cecha['products_stock_price_'.$x]);
                    $pola[] = array('products_stock_tax_'.$x, (float)$cecha['products_stock_tax_'.$x]);
                    $pola[] = array('products_stock_price_tax_'.$x, (float)$cecha['products_stock_price_tax_'.$x]);
                    $pola[] = array('products_stock_old_price_'.$x, (float)$cecha['products_stock_old_price_'.$x]);
                    $pola[] = array('products_stock_retail_price_'.$x, (float)$cecha['products_stock_retail_price_'.$x]);                    
                    //
                }
        
                $db->insert_query('products_stock', $pola);
                unset($pola);            
            }            
        }   
        ?>        

        <script type="text/javascript" src="produkty/cechy.js"></script> 

        <div class="CechyNaglowek">Sposób obliczania końcowej wartości produktu z cechami</div>
        
        <div class="RamkaCechRodzaj">
            <div id="WyborRodzajuCechy">
                <input type="radio" value="cechy" name="rodzaj_cechy" id="rodzajCechyCecha" onclick="typ_cechy('cechy')" <?php echo (((isset($prod['options_type']) && $prod['options_type'] == 'cechy') || !isset($prod['options_type'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzajCechyCecha">cena produktu obliczana wg wartości cech</label><br />
                <input type="radio" value="ceny" name="rodzaj_cechy" id="rodzajCechyCena" onclick="typ_cechy('ceny')" <?php echo ((isset($prod['options_type']) && $prod['options_type'] == 'ceny') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzajCechyCena">cena produktu przypisana na stałe do kombinacji cech</label>
            </div>        
        </div>
        
        <div class="CechyNaglowek">Wybierz cechę do dodania</div>
        
        <div class="CechyInfo" style="display:none">
            <div class="ostrzezenie">
                Dodanie nowej cechy spowoduje wyzerowanie wszystkich stanów magazynowych, dostępności, <b>cen produktów wg kombinacji cech</b>, nr katalogowych cech oraz indywidualnych zdjęć cech dla danego produktu.
            </div>
        </div>

        <table class="CechyProd">
            <tr>
                <td>Nazwa:</td>
                <td id="cech_nazwy">
                    <?php
                    $jest_cecha = false;
                    //
                    $cechy = "select distinct * from products_options where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by products_options_sort_order, products_options_name";
                    $sqlc = $db->open_query($cechy);
                    //
                    $id_domyslne = 0;
                    $tablica = array();
                    //
                    while ($cecha = $sqlc->fetch_assoc()) {
                        if ($id_domyslne == 0) {
                            $id_domyslne = $cecha['products_options_id'];
                        }
                        $tablica[] = array('id' => $cecha['products_options_id'], 'text' =>$cecha['products_options_name']);
                        //
                        $jest_cecha = true;
                    }
                    $db->close_query($sqlc);
                    
                    echo Funkcje::RozwijaneMenu('cecha', $tablica, $id_domyslne, 'style="width:130px" id="id_cecha" onchange="zmien_ceche()"');
                    
                    unset($cecha, $tablica);
                    ?>
                </td>
                <td class="DodanieCechy" <?php echo (($jest_cecha == false) ? 'style="display:none;padding-top:0px"' : 'style="padding-top:0px"'); ?>>Wartość:</td>
                <td id="cech_wartosc" <?php echo (($jest_cecha == false) ? 'style="display:none"' : ''); ?>>
                    <?php
                    $jest_wartosc_cechy = false;
                    //
                    $cechy = "select distinct po.products_options_values_name, pop.products_options_id, po.products_options_values_thumbnail, po.products_options_values_id, pop.products_options_values_id, pop.products_options_values_sort_order from products_options_values po, products_options_values_to_products_options pop where po.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and po.products_options_values_id = pop.products_options_values_id and pop.products_options_id = '".(int)$id_domyslne."' order by pop.products_options_values_sort_order, po.products_options_values_name";
                    $sqlc = $db->open_query($cechy);
                    //
                    $id_domyslna_wartosc = 0;
                    $tablica = array();
                    //
                    while ($cecha = $sqlc->fetch_assoc()) {
                        if ($id_domyslna_wartosc == 0) {
                            $id_domyslna_wartosc = $cecha['products_options_values_id'];
                        }
                        $tablica[] = array('id' => $cecha['products_options_values_id'], 'text' => $cecha['products_options_values_name']);
                        //
                        $jest_wartosc_cechy = true;
                    }
                    $db->close_query($sqlc);
                    
                    echo Funkcje::RozwijaneMenu('cecha', $tablica, $id_domyslna_wartosc, 'style="width:130px" id="id_wartosc"' . (($jest_wartosc_cechy == false) ? ' disabled="disabled"' : ''));
                    
                    unset($cecha, $tablica, $id_domyslne);
                    ?>
                </td>
                
                <td class="InfoCechyDodaj" <?php echo (($jest_cecha == false || $jest_wartosc_cechy == false) ? 'style="display:none"' : ''); ?>>
                    <em class="TipChmurka"><b>Dodaj cechę do produktu</b><img src="obrazki/rozwin.png" id="dodaj_ceche" style="cursor:pointer" onclick="lista_cech()" alt="Dodaj cechę do produktu" /></em>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td class="DodanieCechy">
                    <span class="dodaj" onclick="dodaj_nowa_ceche()">dodaj cechę</span>
                </td>
                <td>&nbsp;</td>
                <td class="DodanieCechy" <?php echo (($jest_cecha == false) ? 'style="display:none"' : ''); ?>>
                    <span class="dodaj" onclick="dodaj_nowa_wartosc()">dodaj wartość</span>
                </td>          
                <td>&nbsp;</td>                
            </tr>
        </table>

        <div id="OknoDodawaniaNowejCechy"></div>
        
        <div id="ListaCechProduktu"></div>
        
        <?php if ($id_produktu > 0) { ?>
        
        <script>            
        lista_cech('wyswietl'<?php echo ((isset($prod['options_type']) && $prod['options_type'] == 'ceny') ? ",'tak'" : ""); ?>);
        </script> 
        
        <?php } ?>        
        
        <?php unset($jest_cecha, $jest_wartosc_cechy); ?>

    </div>
    
</div>

<?php } ?>
