<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Raport o niskim stanie magazynowym cech produktów</div>

                <div class="pozycja_edytowana">  
                
                    <script>
                    function tryb_wyswietl(id) {
                        if (id == 0) {
                            $('#tabWynik').slideDown(); 
                        } else {
                            $('#tabWynik').css('display','none');  
                        }
                        for (x = 1; x < 3; x++) {
                            $('#tryb_'+x).css('display','none');                               
                        }
                        $('#tryb_'+id).slideDown();      
                    }
                    </script>                   

                    <span class="maleInfo">Raport prezentuje produkty które mają niski stan magazynowy cech</span>
                    
                    <?php
                    if ( MAGAZYN_SPRAWDZ_STANY == 'nie' || CECHY_MAGAZYN == 'nie' ) {
                    ?>
                    
                    <span class="ostrzezenie" style="margin:10px">W sklepie nie jest włączona kontrola stanów magazynowych produktów lub cechy produktów nie są powiązane z magazynem sklepu - raport nie może być wygenerowany.</span>
                    
                    <?php } else { ?>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/niskie_stany_cechy.php" method="post" id="statForm" class="cmxform">
                    
                    <?php
                    $MinIlosc = MAGAZYN_STAN_MINIMALNY;
                    if (isset($_GET['ilosc']) && (int)$_GET['ilosc'] >= 0) {
                        $MinIlosc = (int)$_GET['ilosc'];
                    }                    
                    ?>
                    
                    <div id="wyszukaj" style="margin:10px">
                    
                        <div id="wyszukaj_text" style="margin-top:6px">
                            <span>Pokaż z ilością mniejszą od:</span>
                            <input type="text" name="ilosc" value="<?php echo $MinIlosc; ?>" size="4" class="calkowita" />
                        </div>      

                        <div class="wyszukaj_przycisk" style="margin-right:40px;"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                        
                        <?php
                        if (!isset($_GET['kategoria_id']) && !isset($_GET['producent_id'])) {
                        ?>
                        
                        <div class="WyszukajRadio" style="margin-top:16px">                            
                            <input type="radio" name="tryb" id="tryb_wszystkie" value="wszystkie" onclick="tryb_wyswietl(0)" checked="checked" /> <label class="OpisFor" for="tryb_wszystkie"><span>wszystkie produkty</span></label>
                        </div>                        
                        
                        <div class="WyszukajRadio" style="margin-top:16px">    
                            <input type="radio" name="tryb" id="tryb_kat" value="kat" onclick="tryb_wyswietl(1)" /> <label class="OpisFor" for="tryb_kat"><span>tylko z wybranej kategorii</span></label>
                        </div>

                        <div class="WyszukajRadio" style="margin-top:16px">    
                            <input type="radio" name="tryb" id="tryb_prd" value="prd" onclick="tryb_wyswietl(2)" /> <label class="OpisFor" for="tryb_prd"><span>tylko producenta</span></label>
                        </div>

                        <?php } else {

                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { 
                            // pobieranie informacji o nazwie kategorii
                            $zapytanie_tmp = "select distinct categories_name from categories_description where categories_id = '" . (int)$_GET['kategoria_id'] . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                            $sqls = $db->open_query($zapytanie_tmp);            
                            $infs = $sqls->fetch_assoc()
                            ?>
                            <div class="wyszukaj_select" stylestyle="margin-right:20px;margin-top:8px">
                                <span>Kategoria:</span>
                                <input type="text" name="tryb" style="width:300px" value="<?php echo $infs['categories_name']; ?>" disabled="disabled" />
                                <input type="hidden" name="kategoria_id" value="<?php echo (int)$_GET['kategoria_id']; ?>" />
                            </div>                        
                            <?php 
                            $db->close_query($sqls); 
                            unset($zapytanie_tmp, $infs);
                            } 

                            if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
                            // pobieranie informacji o producentach
                            $zapytanie_tmp = "select distinct manufacturers_name from manufacturers where manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
                            $sqls = $db->open_query($zapytanie_tmp);            
                            $infs = $sqls->fetch_assoc()
                            ?>
                            <div class="wyszukaj_select" style="margin-right:20px;margin-top:8px">
                                <span>Producent:</span>
                                <input type="text" name="tryb" style="width:300px" value="<?php echo $infs['manufacturers_name']; ?>" disabled="disabled" />
                                <input type="hidden" name="producent_id" value="<?php echo (int)$_GET['producent_id']; ?>" />
                            </div>                        
                            <?php 
                            $db->close_query($sqls); 
                            unset($zapytanie_tmp, $infs);
                            } 
                        
                        }
                        
                        if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                          echo '<div id="wyszukaj_ikona"><a href="statystyki/niskie_stany_cechy.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                        }                        
                        ?>

                        <div class="cl"></div>      

                        <div class="WyszukajCheckbox">                            
                            <input type="checkbox" name="aktywne" id="tylko_aktywne" value="1" <?php echo ((isset($_GET['aktywne']) && (int)$_GET['aktywne'] == 1) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tylko_aktywne"><span>wyświetlaj tylko aktywne produkty</span></label>
                        </div>

                        <div class="cl"></div>                         
                    
                    </div>     

                    <div id="tryb_1" style="display:none">
                    
                        <div id="drzewo" style="margin-left:10px;max-width:650px;width:95%">
                            <?php
                            //
                            echo '<table class="pkc">';
                            //
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                echo '<tr>
                                        <td class="lfp">
                                            <a href="statystyki/niskie_stany_cechy.php?kategoria_id='.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<em class="TipChmurka"><b>Kategoria jest nieaktywna</b><span class="wylKat"></span></em>' : '').'</a>
                                        </td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'niskie_stany_cechy\',\'statystyki\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);
                            ?> 
                        </div>  
                        
                    </div>
                    
                    <div id="tryb_2" style="display:none">
                    
                        <div id="producent" class="WyborProducenta">
                            <?php
                            $Prd = Funkcje::TablicaProducenci();
                            for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                echo '<a href="statystyki/niskie_stany_cechy.php?producent_id='.$Prd[$b]['id'].'">'.$Prd[$b]['text'].'</a>';
                            }
                            unset($Prd);
                            ?>
                        </div>
                        
                    </div>
                    
                    </form>

                    <?php
                    // ile na stronie
                    $IleNaStronie = 20;
                    
                    $PoczatekLimit = 0;
                    if (isset($_GET['str']) && (int)$_GET['str'] > 0) {
                        $PoczatekLimit = (int)$_GET['str'] * $IleNaStronie;
                    }
                    
                    $warunki_szukania = '';
                    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                        $warunki_szukania .= " AND pc.categories_id = '" . (int)$_GET['kategoria_id'] . "'";
                    }
                    if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
                        $warunki_szukania .= " AND p.manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
                    }     
                    if (isset($_GET['aktywne']) && (int)$_GET['aktywne'] == 1) {
                        $warunki_szukania .= " AND p.products_status = '1'";
                    }                    
                    
                    $zapytanie = "SELECT DISTINCT p.products_id, 
                                            p.products_image, 
                                            p.products_status, 
                                            p.products_model, 
                                            p.products_price_tax, 
                                            p.products_old_price, 
                                            p.manufacturers_id, 
                                            p.products_quantity_alarm,
                                            pd.products_id, 
                                            p.products_currencies_id, 
                                            p.products_points_only,
                                            p.products_points_value,  
                                            p.products_points_value_money,
                                            p.products_control_storage,
                                            pd.language_id, 
                                            pd.products_name, 
                                            GROUP_CONCAT(DISTINCT CONVERT(ps.products_stock_attributes, CHAR(8)),';', CONVERT(ps.products_stock_quantity, CHAR(8)) SEPARATOR '|') AS cechy 
                                       FROM products p, 
                                            products_to_categories pc, 
                                            products_description pd, 
                                            products_stock ps 
                                      WHERE pd.products_id = p.products_id AND 
                                            pc.products_id = p.products_id AND 
                                            p.products_id = ps.products_id AND 
                                            pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' AND 
                                            ps.products_stock_quantity < " . $MinIlosc . $warunki_szukania . " AND
                                            (p.products_control_storage = '1' || p.products_control_storage = '2')
                                   GROUP BY p.products_id         
                                   ORDER BY pd.products_name, ps.products_stock_quantity asc";           
                            
                    $sql = $db->open_query($zapytanie);
                    $OgolnaIlosc = (int)$db->ile_rekordow($sql);
                    
                    $db->close_query($sql);
                    unset($zapytanie);                                         

                    echo '<div id="tabWynik">';
                    
                    echo '<div class="RamkaStatystyki"><table class="TabelaStatystyki">';
                    
                    $zapytanie = "SELECT DISTINCT p.products_id, 
                                            p.products_image, 
                                            p.products_status, 
                                            p.products_model, 
                                            p.products_price_tax, 
                                            p.products_old_price, 
                                            p.manufacturers_id, 
                                            p.products_quantity_alarm,
                                            pd.products_id, 
                                            p.products_currencies_id,
                                            p.products_points_only,
                                            p.products_points_value, 
                                            p.products_points_value_money,
                                            p.products_control_storage,
                                            pd.language_id, 
                                            pd.products_name, 
                                            GROUP_CONCAT(DISTINCT ps.products_stock_attributes,';', CONVERT(ps.products_stock_quantity, CHAR(8)) SEPARATOR '|') AS cechy 
                                       FROM products p, 
                                            products_to_categories pc, 
                                            products_description pd, 
                                            products_stock ps 
                                      WHERE pd.products_id = p.products_id AND 
                                            pc.products_id = p.products_id AND 
                                            p.products_id = ps.products_id AND 
                                            pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' AND 
                                            ps.products_stock_quantity < " . $MinIlosc . $warunki_szukania . " AND
                                            (p.products_control_storage = '1' || p.products_control_storage = '2')
                                   GROUP BY p.products_id         
                                   ORDER BY pd.products_name, ps.products_stock_quantity asc limit " . $PoczatekLimit . "," . $IleNaStronie;                                              

                    $sql = $db->open_query($zapytanie);

                    if ((int)$db->ile_rekordow($sql) > 0) {

                        echo '<tr class="TyNaglowek">';
                        echo '<td>Id</td>';
                        echo '<td>Zdjęcie</td>';
                        echo '<td>Nr katalogowy</td>';
                        echo '<td>Nazwa produktu</td>';
                        echo '<td>Cena brutto</td>';
                        echo '<td>Ilość</td>';
                        echo '<td style="text-align:center">Alarm magazynowy</td>';
                        echo '<td>Stan</td>';
                        echo '</tr>';                      
                        
                        while ($info = $sql->fetch_assoc()) {
                            //
                            echo '<tr>';
                            echo '<td class="inne">'.$info['products_id'].'</td>';
                            echo '<td class="inne">'.Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40').'</td>';
                            echo '<td class="nrKat">'.$info['products_model'].'</td>';
                            echo '<td class="linkProd"><a href="produkty_magazyn/produkty_magazyn_edytuj.php?id_poz=' . $info['products_id'] . '">'.$info['products_name'].'</a></td>';
                            
                            echo '<td style="white-space: nowrap" class="inne">' .
                                 (((float)$info['products_old_price'] == 0) ? '' : '<div class="cena_promocyjna">' . $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . '</div>') . 
                                 '<div class="cena">'.$waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']).'</div>'.
                                 (($info['products_points_only'] == 1) ? '<div class="TylkoPkt">' . $info['products_points_value'] . ' pkt + ' . $waluty->FormatujCene($info['products_points_value_money'],false) . '</div>' : '').'
                                 </td>'; 

                            echo '<td class="wynikStat">&nbsp;</td>';
                            echo '<td class="wynikStat">'.(($info['products_quantity_alarm'] > 0) ? $info['products_quantity_alarm'] : '-').'</td>';

                            if ($info['products_status'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten produkt jest aktywny" />'; $tekst_opisu = 'Ten produkt jest aktywny'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Ten produkt jest nieaktywny" />'; $tekst_opisu = 'Ten produkt jest nieaktywny'; }
                            echo '<td class="inne"><em class="TipChmurka">'.$obraz.'<b>'.$tekst_opisu.'</b></em></td>';

                            echo '</tr>';
                            //
                            // okresla ile jest cech dla produktu
                            $IleCech = explode('|', (string)$info['cechy']);
                            for ($i = 0, $ci = count($IleCech); $i < $ci; $i++) {
                                //
                                $PozycjeCech = explode(';', (string)$IleCech[$i]);
                                $NazwaWartosciCech = explode(',', (string)$PozycjeCech[0]);
                                //
                                $CiagCech = '';
                                for ($r = 0, $cr = count($NazwaWartosciCech); $r < $cr; $r++) {
                                    //
                                    $Podz = explode('-', (string)$NazwaWartosciCech[$r]);
                                    $CiagCech .= Funkcje::NazwaCechy($Podz[0]) . ': <b>' . Funkcje::WartoscCechy($Podz[1]) . '</b>, ';
                                    unset($Podz);
                                    //
                                }
                                $CiagCech = substr((string)$CiagCech, 0, strlen((string)$CiagCech)-2);
                                //
                                echo '<tr>';
                                echo '<td colspan="2">&nbsp;</td>';
                                echo '<td class="nrKat">'.Funkcje::NrKatCechy($PozycjeCech[0], $info['products_id']).'</td>';
                                echo '<td class="nrKat" colspan="2">'.$CiagCech.'</td>';
                                echo '<td class="wynikStat">'.$PozycjeCech[1].'</td>';
                                echo '<td>&nbsp;</td>';
                                echo '<td>&nbsp;</td>';
                                echo '</tr>';                                
                                //                
                                unset($CiagCech, $PozycjeCech, $NazwaWartosciCech);
                                //
                            }
                            //
                        }

                        unset($info);

                      } else {
                      
                        echo '<tr><td style="padding:10px; border:0px; ">Brak wyników ...</td></tr>';
                     
                    }

                    echo '</table></div>';     

                    if ((int)$db->ile_rekordow($sql) > 0) {
                    ?>
                    <div id="DolneStrony">
                        <?php
                        $limit = $OgolnaIlosc / $IleNaStronie;
                        if ($limit < ($OgolnaIlosc / $IleNaStronie)) {
                            $limit++;
                        }
                        //
                        for ($c = 0; $c < $limit; $c++) {
                            //
                            $Rozszerzenie = 'Nieaktywny';
                            if ((!isset($_GET['str']) || (int)$_GET['str'] == 0) && $c == 0) {
                                $Rozszerzenie = 'Aktywny';
                            }
                            if (isset($_GET['str']) && (int)$_GET['str'] == $c) {
                                $Rozszerzenie = 'Aktywny';
                            }
                            //
                            if ($c == 0) {
                                echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/niskie_stany_cechy.php">1</a>';
                              } else {
                                echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/niskie_stany_cechy.php?str='.$c.'">' . ($c + 1) . '</a>';
                            }
                        }
                        //
                        echo '<span id="IleStrona">Wyświetlanie: ' . ($PoczatekLimit + 1) . ' do ' . (($PoczatekLimit + $IleNaStronie) > $OgolnaIlosc ? $OgolnaIlosc : ($PoczatekLimit + $IleNaStronie)) . ' z ' . $OgolnaIlosc . '</span>';
                        ?>
                    </div> 
                    <?php
                    }
                    
                    unset($zapytanie);
                    $db->close_query($sql);
                    ?>
                    
                    <?php } ?>
                    
                    </div>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}