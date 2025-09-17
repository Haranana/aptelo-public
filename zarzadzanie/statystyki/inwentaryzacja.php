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
    
    <div id="naglowek_cont">Inwentaryzacja produktów</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Inwentaryzacja</div>

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

                    <span class="maleInfo">Raport prezentuje wszystkie produkty sklepu wraz ze stanem magazynowym</span>
                    
                    <?php
                    if ( MAGAZYN_SPRAWDZ_STANY == 'nie' ) {
                    ?>
                    
                    <span class="ostrzezenie" style="margin:10px">W sklepie nie jest włączona kontrola stanów magazynowych produktów - raport nie może być wygenerowany.</span>
                    
                    <?php } else { ?>     

                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                     
                    
                    <form action="statystyki/inwentaryzacja.php" method="get" id="statForm" class="cmxform">
                    
                    <div id="wyszukaj" style="margin:10px">
                    
                        <?php
                        if (!isset($_GET['kategoria_id']) && !isset($_GET['producent_id'])) {
                        ?>
                        
                        <div class="WyszukajRadio">                            
                            <input type="radio" name="tryb" id="tryb_wszystkie" value="wszystkie" onclick="tryb_wyswietl(0)" checked="checked" /> <label class="OpisFor" for="tryb_wszystkie"><span>wszystkie produkty</span></label>
                        </div>                        
                        
                        <div class="WyszukajRadio">
                            <input type="radio" name="tryb" id="tryb_kat" value="kat" onclick="tryb_wyswietl(1)" /> <label class="OpisFor" for="tryb_kat"><span>tylko z wybranej kategorii</span></label>
                        </div>

                        <div class="WyszukajRadio">
                            <input type="radio" name="tryb" id="tryb_prd" value="prd" onclick="tryb_wyswietl(2)" /> <label class="OpisFor" for="tryb_prd"><span>tylko producenta</span></label>
                        </div>

                        <?php } else {

                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { 
                            // pobieranie informacji o nazwie kategorii
                            $zapytanie_tmp = "select distinct categories_name from categories_description where categories_id = '" . (int)$_GET['kategoria_id'] . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                            $sqls = $db->open_query($zapytanie_tmp);            
                            $infs = $sqls->fetch_assoc()
                            ?>
                            <div class="wyszukaj_select" style="margin-right:20px;margin-top:2px">
                                <span>Kategoria:</span>
                                <input type="text" name="tryb" style="width:300px" value="<?php echo $infs['categories_name']; ?>" disabled="disabled" />
                                <input type="hidden" name="kategoria_id" value="<?php echo (int)$_GET['kategoria_id']; ?>" />
                            </div>                        
                            <?php 
                            $db->close_query($sqls); 
                            unset($zapytanie_tmp, $infs);
                            } 

                            if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
                            // pobieranie informacji o nazwie producenta
                            $zapytanie_tmp = "select distinct manufacturers_name from manufacturers where manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
                            $sqls = $db->open_query($zapytanie_tmp);            
                            $infs = $sqls->fetch_assoc()
                            ?>
                            <div class="wyszukaj_select" style="margin-right:20px;margin-top:2px">
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
                          echo '<div id="wyszukaj_ikona" style="margin:2px 0px 0px 0px;float:left"><a href="statystyki/inwentaryzacja.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                        }                             
                        ?>
                        
                        <div class="cl"></div> 
                        
                        <div class="rg">
                            <a class="Export" href="statystyki/inwentaryzacja_utworz.php<?php echo Funkcje::Zwroc_Get(array('str')); ?>">eksportuj dane do pliku</a>
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
                                            <a href="statystyki/inwentaryzacja.php?kategoria_id='.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<em class="TipChmurka"><b>Kategoria jest nieaktywna</b><span class="wylKat"></span></em>' : '').'</a>
                                        </td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'inwentaryzacja\',\'statystyki\')" />' : '').'</td>
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
                                echo '<a href="statystyki/inwentaryzacja.php?producent_id='.$Prd[$b]['id'].'">'.$Prd[$b]['text'].'</a>';
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
                        $warunki_szukania = " AND pc.categories_id = '" . (int)$_GET['kategoria_id'] . "'";
                    }
                    if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
                        $warunki_szukania = " AND p.manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
                    }                    
                    
                    $zapytanie = 'SELECT DISTINCT
                                         p.products_id, 
                                         p.products_image,
                                         p.products_status,
                                         p.products_model,
                                         p.products_price,
                                         p.products_price_tax,
                                         p.products_old_price,
                                         p.products_purchase_price,
                                         p.products_quantity,
                                         p.manufacturers_id,
                                         pd.products_id, 
                                         p.products_currencies_id,        
                                         p.products_points_only,
                                         p.products_points_value,       
                                         p.products_points_value_money,
                                         pd.language_id, 
                                         pd.products_name
                                  FROM products p, products_to_categories pc, products_description pd
                                  WHERE pd.products_id = p.products_id AND pc.products_id = p.products_id AND p.products_quantity > 0
                                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" ' . $warunki_szukania . ' order by p.products_quantity, pd.products_name  asc';                    
                    $sql = $db->open_query($zapytanie);
                    $OgolnaIlosc = (int)$db->ile_rekordow($sql);
                    
                    $db->close_query($sql);
                    unset($zapytanie);                                         

                    echo '<div id="tabWynik">';
                    
                    echo '<div class="RamkaStatystyki"><table class="TabelaStatystyki">';

                    $zapytanie = 'SELECT DISTINCT
                                         p.products_id, 
                                         p.products_image,
                                         p.products_status,
                                         p.products_model,
                                         p.products_price,
                                         p.products_price_tax,
                                         p.products_old_price,
                                         p.products_purchase_price,
                                         p.products_quantity,
                                         p.manufacturers_id,
                                         pd.products_id, 
                                         p.products_currencies_id,     
                                         p.products_points_only,
                                         p.products_points_value,
                                         p.products_points_value_money,
                                         pd.language_id, 
                                         pd.products_name
                                  FROM products p, products_to_categories pc, products_description pd
                                  WHERE pd.products_id = p.products_id AND pc.products_id = p.products_id AND p.products_quantity > 0
                                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" ' . $warunki_szukania . ' order by p.products_quantity, pd.products_name asc limit ' . $PoczatekLimit . ',' . $IleNaStronie;
    
                    $sql = $db->open_query($zapytanie);

                    if ((int)$db->ile_rekordow($sql) > 0) {

                        echo '<tr class="TyNaglowek">';
                        echo '<td>Id</td>';
                        echo '<td>Zdjęcie</td>';
                        echo '<td>Nr katalogowy</td>';
                        echo '<td>Nazwa produktu</td>';
                        echo '<td style="text-align:center;">Cena zakupu</td>';
                        echo '<td style="text-align:center">Cena netto</td>';
                        echo '<td style="text-align:center">Cena brutto</td>';
                        echo '<td>Ilość</td>';
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
                                 '<div class="cena">'.( $info['products_purchase_price'] > 0 ? $waluty->FormatujCene($info['products_purchase_price'], false, $info['products_currencies_id']) : '' ).'</div>
                                 </td>';                             

                            echo '<td style="white-space: nowrap" class="inne">' .
                                 '<div class="cena">'.$waluty->FormatujCene($info['products_price'], false, $info['products_currencies_id']).'</div>
                                 </td>';                             
                            
                            echo '<td style="white-space: nowrap" class="inne">' .
                                 (((float)$info['products_old_price'] == 0) ? '' : '<div class="cena_promocyjna">' . $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . '</div>') . 
                                 '<div class="cena">'.$waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']).'</div>'.
                                 (($info['products_points_only'] == 1) ? '<div class="TylkoPkt">' . $info['products_points_value'] . ' pkt + ' . $waluty->FormatujCene($info['products_points_value_money'],false) . '</div>' : '').'
                                 </td>'; 

                            echo '<td class="wynikStat">'.$info['products_quantity'].'</td>';

                            if ($info['products_status'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten produkt jest aktywny" />'; $tekst_opisu = 'Ten produkt jest aktywny'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Ten produkt jest nieaktywny" />'; $tekst_opisu = 'Ten produkt jest nieaktywny'; }
                            echo '<td class="inne"><em class="TipChmurka">'.$obraz.'<b>'.$tekst_opisu.'</b></em></td>';

                            echo '</tr>';
                            //
                            
                            if (CECHY_MAGAZYN == 'tak') {
                            
                                // sprawdzanie cech produktu
                                $zapytanieCechy = "SELECT DISTINCT products_stock_quantity, products_stock_model, products_stock_attributes FROM products_stock WHERE products_id = '" . (int)$info['products_id'] . "' and products_stock_quantity > 0";
                                $sqlCecha = $db->open_query($zapytanieCechy);
                                while ($infc = $sqlCecha->fetch_assoc()) {
                                    //
                                    $NazwaWartosciCech = explode(',', (string)$infc['products_stock_attributes']);
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
                                    echo '<td class="nrKat">'.$infc['products_stock_model'].'</td>';
                                    echo '<td class="nrKat" colspan="4">'.$CiagCech.'</td>';
                                    echo '<td class="wynikStat">'.$infc['products_stock_quantity'].'</td>';
                                    echo '<td>&nbsp;</td>';
                                    echo '</tr>';                               
                                    //                
                                    unset($CiagCech, $NazwaWartosciCech);
                                    //
                                }
                                
                            }
                            
                        }

                        unset($info);

                      } else {
                      
                        echo '<tr><td style="padding:10px; border:0px;">Brak wyników ...</td></tr>';
                     
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
                                echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/inwentaryzacja.php">1</a>';
                              } else {
                                echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/inwentaryzacja.php?str='.$c.'">' . ($c + 1) . '</a>';
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