<?php if ($prot->wyswietlStrone) { ?>
 
<div id="zakl_id_14" style="display:none;">

    <div class="info_content">
        
        <div class="maleInfo">Pierwsze pole wyboru przy kategoriach służy do zaznaczenia głównej kategorii dla <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> (taka kategoria będzie używana przy eksporcie do plików XML oraz porównywarek). Przypisanie do kategorii głównej kategorii nie jest obowiązkowe. Drugie pole wielokrotnego wyboru umożliwia przypisanie <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> do wielu kategorii.</div>

        <div class="CechyNaglowek" style="padding-top:10px">Kategorie do jakich będzie przypisany <?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?></div>                           

        <div id="drzewo" style="margin-left:10px; width:95%; max-width:650px">
            <?php
            //
            $przypisane_kategorie = array();
            $glowna_kategoria = 0;
            //
            // tablica kategori produktu jezeli jest edycja
            if ($id_produktu > 0) {  
                //
                // pobieranie id kategorii do jakich jest przypisany produkt
                $zapytanie_kateg = "select categories_id, categories_default from products_to_categories where products_id = '".$id_produktu."'";
                $sqlk = $db->open_query($zapytanie_kateg);
                
                while ($kate = $sqlk->fetch_assoc()) {
                    //
                    if ( $kate['categories_default'] == 1 ) {
                         $glowna_kategoria = $kate['categories_id'];
                    }
                    //
                    $przypisane_kategorie[] = $kate['categories_id'];
                    
                }
                //
                $db->close_query($sqlk);
                unset($kate, $zapytanie_kateg); 
                //
            }   
            
            if ($id_produktu == 0 && isset($_COOKIE['kategoria']) && isset($_SESSION['filtry']['produkty.php']['kategoria_id']) && $_SESSION['filtry']['produkty.php']['kategoria_id'] > 0) {
                $przypisane_kategorie = array( (int)$_SESSION['filtry']['produkty.php']['kategoria_id'] );
            }

            echo '<input type="hidden" id="glownaKategoria" value="' . $glowna_kategoria . '" />';
            
            //
            if ( count($przypisane_kategorie) > 10 || KATEGORIE_LISTING_EDYCJA == 'wszystkie' ) {
                //
                echo '<ul id="drzewoKategorii">';
                foreach(Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica) {
                    //
                    echo Kategorie::WyswietlDrzewoKategoriiCheckbox($IdKategorii, $Tablica, $przypisane_kategorie, 'tak', $glowna_kategoria);
                    //
                }    
                echo '</ul>';
                //
            } else {
                //
                echo '<table class="pkc">';
                //
                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                    $podkategorie = false;
                    if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                    //
                    // uzywane przy edycji - sprawdza czy produkt nalezy do kategorii
                    $check = '';
                    if ($id_produktu > 0 || (isset($_COOKIE['kategoria']) && isset($_SESSION['filtry']['produkty.php']['kategoria_id']) && $_SESSION['filtry']['produkty.php']['kategoria_id'] > 0)) { 
                        //
                        if ( in_array((string)$tablica_kat[$w]['id'], $przypisane_kategorie) ) {
                            $check = 'checked="checked"';
                        }
                        //  
                    }
                    //
                    echo '<tr>
                            <td class="lfp">
                                <input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_glowna" ' . (($tablica_kat[$w]['id'] == $glowna_kategoria) ? 'checked="checked"' : '') . ' id="id_glowna_'.$tablica_kat[$w]['id'].'" /><label class="OpisForPustyLabel OpisForPustyLabelLista TipChmurka" for="id_glowna_'.$tablica_kat[$w]['id'].'"><b>Przypisz tą kategorię jako główną</b></label>
                                <input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" '.$check.' id="id_kat_'.$tablica_kat[$w]['id'].'" /><label class="OpisFor OpisForPustyLabelLista" for="id_kat_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text']. (($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></span>' : '') . '</label> 
                            </td>
                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                          </tr>
                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                }
                if ( count($tablica_kat) == 0 ) {
                     echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                }            
                echo '</table>';
                unset($tablica_kat,$podkategorie);
            }
            //
            
            if ($id_produktu > 0 || (isset($_COOKIE['kategoria']) && isset($_SESSION['filtry']['produkty.php']['kategoria_id']) && $_SESSION['filtry']['produkty.php']['kategoria_id'] > 0)) {

                foreach ( $przypisane_kategorie as $kategoria ) {
                
                    $sciezka = Kategorie::SciezkaKategoriiId($kategoria);
                    $cSciezka = explode("_", (string)$sciezka);                    
                    if (count($cSciezka) > 1) {
                        //
                        $ostatnie = strRpos($sciezka,'_');
                        $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                        ?>
                        
                        <script>          
                        podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo implode(',', (array)$przypisane_kategorie); ?>');
                        </script>
                        
                    <?php
                    unset($sciezka,$cSciezka);
                    }
              
                } 

            }    
            ?>            
            
        </div>    
    
    </div>

</div>   

<?php } ?>
