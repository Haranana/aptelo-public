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
    
    <div id="naglowek_cont">Eksport danych do pliku <?php echo (($_POST['format'] == 'xml') ? 'XML' : 'CSV'); ?></div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików <?php echo (($_POST['format'] == 'xml') ? 'XML' : 'CSV'); ?></div>

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'export') { ?>
            
                <div class="pozycja_edytowana">    
                
                    <?php
                    if (isset($_POST['plik_wynik']) && !empty($_POST['plik_wynik'])) {
                        $plikDoZapisu = '../export/' . $filtr->process($_POST['plik_wynik']) . '.' . (($_POST['format'] == 'xml') ? 'xml' : 'csv');
                    } else {                        
                        $plikDoZapisu = '../export/export_'.(($_POST['format'] == 'xml') ? 'xml' : 'csv').'_' . date('d_m_Y', time()) . '_' . rand(1,1000000) . '.' . (($_POST['format'] == 'xml') ? 'xml' : 'csv');
                    }
                    if (file_exists($plikDoZapisu)) {
                        unlink($plikDoZapisu);
                    }
                    ?>

                    <input type="hidden" id="plik" value="<?php echo $plikDoZapisu; ?>" />
                    <input type="hidden" id="zakres" value="<?php echo ((isset($_POST['zakres'])) ? $filtr->process($_POST['zakres']) : 'wszystkie'); ?>" />
                    <?php
                    //
                    if (isset($_POST['zakres']) && ($_POST['zakres'] == 'pl_bez_kategorii' || $_POST['zakres'] == 'wszystkie_bez_kategorii' || $_POST['zakres'] == 'pl' || $_POST['zakres'] == 'wszystkie' || $_POST['zakres'] == 'cena_ilosc')) {
                        //
                        // trzeba okreslic ile produktow bedzie do eksportu
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'wszystkie') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="wszystkie" />';
                            echo '<input type="hidden" id="filtr" value="" />';
                            $zapytanie = "select distinct products_id from products where products_set = 0";
                        }
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'producent') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="producent" />';
                            echo '<input type="hidden" id="filtr" value="'.(int)$_POST['producent'].'" />';
                            $zapytanie = "select distinct products_id from products where manufacturers_id = '" . (int)$_POST['producent'] . "' and products_set = 0";
                        } 
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'kategoria') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="kategoria" />';
                            echo '<input type="hidden" id="filtr" value="'.(int)$_POST['id_kat'].'" />';
                            $zapytanie = "select distinct p.products_id from products p, products_to_categories pc where p.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['id_kat'] . "' and p.products_set = 0";
                        } 
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'fraza') {
                            $fraza = $filtr->process($_POST['fraza_eksport']);
                            echo '<input type="hidden" id="filtr_rodzaj" value="fraza" />';
                            echo '<input type="hidden" id="filtr" value="'.$fraza.'" />';
                            $zapytanie = "select distinct products_id from products where products_set = 0 and (products_model like '%".$fraza."%' || products_man_code like '%".$fraza."%')";
                            unset($fraza);
                        } 
                        
                        // dodatkowo status produktow
                        if (isset($_POST['export_status']) && $_POST['export_status'] == 'wszystkie') {
                            echo '<input type="hidden" id="status_produktow" value="2" />';
                        }                         
                        if (isset($_POST['export_status']) && $_POST['export_status'] == 'aktywne') {
                            echo '<input type="hidden" id="status_produktow" value="1" />';
                            $zapytanie .= " and products_status = '1'";
                        }
                        if (isset($_POST['export_status']) && $_POST['export_status'] == 'aktywne_listing') {
                            echo '<input type="hidden" id="status_produktow" value="4" />';
                            $zapytanie .= " and products_status = '1' and listing_status = '0'";
                        }                         
                        if (isset($_POST['export_status']) && $_POST['export_status'] == 'nieaktywne') {
                            echo '<input type="hidden" id="status_produktow" value="0" />';
                            $zapytanie .= " and products_status = '0'";
                        }                         
                    }
                    
                    if (isset($_POST['zakres']) && $_POST['zakres'] == 'cechy') {
                        //
                        // trzeba okreslic ile produktow bedzie do eksportu
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'wszystkie') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="wszystkie" />';
                            echo '<input type="hidden" id="filtr" value="" />';
                            $zapytanie = "select distinct * from products_stock";
                        }
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'producent') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="producent" />';
                            echo '<input type="hidden" id="filtr" value="'.(int)$_POST['producent'].'" />';
                            $zapytanie = "select distinct p.products_id, p.manufacturers_id, ps.products_id, ps.products_stock_attributes, ps.products_stock_quantity, ps.products_stock_availability_id, ps.products_stock_model from products p, products_stock ps where p.products_id = ps.products_id and p.manufacturers_id = '" . (int)$_POST['producent'] . "'";
                        } 
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'kategoria') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="kategoria" />';
                            echo '<input type="hidden" id="filtr" value="'.(int)$_POST['id_kat'].'" />';
                            $zapytanie = "select distinct * from products_stock ps, products_to_categories pc where ps.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['id_kat'] . "'";
                        }       
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'fraza') {
                            $fraza = $filtr->process($_POST['fraza_eksport']);
                            echo '<input type="hidden" id="filtr_rodzaj" value="fraza" />';
                            echo '<input type="hidden" id="filtr" value="'.$fraza.'" />';
                            $zapytanie = "select distinct distinct * from products_stock where products_stock_model like '%".$fraza."%'";
                            unset($fraza);
                        }                         
                    }
                    
                    if (isset($_POST['zakres']) && $_POST['zakres'] == 'allegro') {
                        //
                        // trzeba okreslic ile produktow bedzie do eksportu
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'wszystkie') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="wszystkie" />';
                            echo '<input type="hidden" id="filtr" value="" />';
                            $zapytanie = "select ap.allegro_id, ap.auction_id from allegro_auctions ap left join products p ON p.products_id = ap.products_id where ap.auction_uuid = ''";                               
                        }
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'producent') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="producent" />';
                            echo '<input type="hidden" id="filtr" value="'.(int)$_POST['producent'].'" />';
                            $zapytanie = "select ap.allegro_id, ap.auction_id from allegro_auctions ap left join products p ON p.products_id = ap.products_id where ap.auction_uuid = '' and p.manufacturers_id = '" . (int)$_POST['producent'] . "'";
                        } 
                        if (isset($_POST['export_dane']) && $_POST['export_dane'] == 'kategoria') {
                            echo '<input type="hidden" id="filtr_rodzaj" value="kategoria" />';
                            echo '<input type="hidden" id="filtr" value="'.(int)$_POST['id_kat'].'" />';
                            $zapytanie = "select ap.allegro_id, ap.auction_id from allegro_auctions ap, products_to_categories pc where ap.auction_uuid = '' and ap.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['id_kat'] . "'";
                        }                     
                    }                    
                    //
                    $sql_ilosc = $db->open_query($zapytanie);
                    ?>

                    <div id="import">
                    
                        <div id="postep">Postęp exportu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent"></div>  

                    </div>   
                    
                    <div id="zaimportowano" style="display:none">
                        <?php
                        $tablicaUsuniecia = array('../export/', '.' . (($_POST['format'] == 'xml') ? 'xml' : 'csv'));
                        $ciagDekod = $plikDoZapisu;
                        for ($q = 0, $c = count($tablicaUsuniecia); $q < $c; $q++) {
                            $ciagDekod = str_replace($tablicaUsuniecia[$q], '', (string)$ciagDekod);
                        }
                        ?>
                        Dane zostały zapisane w pliku <?php echo '<a href="import_danych/pobieranie.php?typ='.(($_POST['format'] == 'xml') ? 'xml' : 'csv').'&plik='.$ciagDekod.'">'.str_replace('../export/', '', (string)$plikDoZapisu).'</a>'; ?><br />
                        Ścieżka do pliku: <a target="_blank" href="<?php echo ADRES_URL_SKLEPU; ?>/<?php echo str_replace('../', '', (string)$plikDoZapisu); ?>"><?php echo ADRES_URL_SKLEPU; ?>/<?php echo str_replace('../', '', (string)$plikDoZapisu); ?></a>
                    </div>                             

                    <script>
                    var ilosc_linii = <?php echo (int)$db->ile_rekordow($sql_ilosc); ?>;                    
                    //
                    function export_csv(limit) {

                        $.post( "import_danych/export.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                format: '<?php echo $_POST['format']; ?>',
                                plik: $('#plik').val(),
                                zakres: $('#zakres').val(),
                                filtr: $('#filtr').val(),
                                filtr_rodzaj: $('#filtr_rodzaj').val(),
                                status_produktow: $('#status_produktow').val(),
                                limit_max: ilosc_linii,
                                limit: limit
                              },
                              function(data) {

                                 if (ilosc_linii == 1) {
                                     procent = 100;
                                   } else {
                                     procent = parseInt((limit / (ilosc_linii - 1)) * 100);
                                     if (procent > 100) {
                                         procent = 100;
                                     }
                                 }

                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span>');
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                                 
                                 if (ilosc_linii - 1 > limit) {
                                    export_csv(limit + <?php echo (((isset($_POST['zakres']) == 'cechy') & ($_POST['zakres'] == 'cechy' || $_POST['zakres'] == 'cena_ilosc')) ? 500 : 50 ); ?>);
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#zaimportowano').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                 } 

                              }                          
                        );
                        
                    }; 
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_<?php echo (($_POST['format'] == 'xml') ? 'xml' : 'csv'); ?>','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
                    </div>    

                    <script>             
                    // sprawdza czy wogole jest cos do exportu
                    if (ilosc_linii > 0) {
                        export_csv(0);
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').css('display','none');                     
                        $('#procent').html('Brak danych do eksportu ...');
                        $('#procent').css('display','block'); 
                        $('#przyciski').css('display','block');                    
                    }
                    </script>                    

                </div>
          
                <?php } ?>
                

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'export_kategorie') { ?>
            
                <div class="pozycja_edytowana">    
                
                    <?php
                    if (isset($_POST['plik_wynik']) && !empty($_POST['plik_wynik'])) {
                        $plikDoZapisu = '../export/' . $filtr->process($_POST['plik_wynik']) . '.' . (($_POST['format'] == 'xml') ? 'xml' : 'csv');
                    } else {                        
                        $plikDoZapisu = '../export/export_'.(($_POST['format'] == 'xml') ? 'xml' : 'csv').'_' . date('d_m_Y', time()) . '_' . rand(1,1000000) . '.' . (($_POST['format'] == 'xml') ? 'xml' : 'csv');
                    }
                    if (file_exists($plikDoZapisu)) {
                        unlink($plikDoZapisu);
                    }                    
                    ?>

                    <input type="hidden" id="plik" value="<?php echo $plikDoZapisu; ?>" />
                    <input type="hidden" id="jezyk" value="<?php echo ((isset($_POST['zakres'])) ? $filtr->process($_POST['zakres']) : 'wszystkie'); ?>" />
                    <?php
                    // trzeba okreslic ile bedzie do eksportu
                    $zapytanie = "select distinct * from categories where parent_id = '0'";           
                    $sql_ilosc = $db->open_query($zapytanie);
                    ?>

                    <div id="import">
                    
                        <div id="postep">Postęp exportu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent"></div>  

                    </div>   
                    
                    <div id="zaimportowano" style="display:none">
                        <?php
                        $tablicaUsuniecia = array('../export/','export_'.(($_POST['format'] == 'xml') ? 'xml' : 'csv').'_','.'.(($_POST['format'] == 'xml') ? 'xml' : 'csv'));
                        $ciagDekod = $plikDoZapisu;
                        for ($q = 0, $c = count($tablicaUsuniecia); $q < $c; $q++) {
                            $ciagDekod = str_replace($tablicaUsuniecia[$q], '', (string)$ciagDekod);
                        }
                        ?>
                        Dane zostały zapisane w pliku <?php echo '<a href="import_danych/pobieranie.php?typ='.(($_POST['format'] == 'xml') ? 'xml' : 'csv').'&plik='.$ciagDekod.'">'.str_replace('../export/', '', (string)$plikDoZapisu).'</a>'; ?><br />
                        Ścieżka do pliku: <a target="_blank" href="<?php echo ADRES_URL_SKLEPU; ?>/<?php echo str_replace('../', '', (string)$plikDoZapisu); ?>"><?php echo ADRES_URL_SKLEPU; ?>/<?php echo str_replace('../', '', (string)$plikDoZapisu); ?></a>                        
                    </div>                             

                    <script>
                    var ilosc_linii = <?php echo (int)$db->ile_rekordow($sql_ilosc); ?>;                    
                    //
                    function export_csv(limit) {

                        $.post( "import_danych/export_kategorie.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                format: '<?php echo $_POST['format']; ?>',
                                plik: $('#plik').val(),
                                jezyk: $('#jezyk').val(),
                                limit_max: ilosc_linii - 1,
                                limit: limit
                              },
                              function(data) {

                                 if (ilosc_linii == 1) {
                                     procent = 100;
                                   } else {
                                     procent = parseInt((limit / (ilosc_linii - 1)) * 100);
                                     if (procent > 100) {
                                         procent = 100;
                                     }
                                 }
                                 
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span>');
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                                 
                                 if (ilosc_linii - 1 > limit) {
                                    export_csv(limit + 1);
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#zaimportowano').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                 } 
                                             
                              }                          
                        );
                        
                    }; 
                    //
                    // sprawdza czy wogole jest cos do exportu
                    if (ilosc_linii > 0) {
                        export_csv(0);
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').css('display','none');                     
                        $('#procent').html('Brak danych do eksportu ...');
                        $('#procent').css('display','block'); 
                        $('#przyciski').css('display','block');                    
                    }
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_<?php echo (($_POST['format'] == 'xml') ? 'xml' : 'csv'); ?>','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
                    </div>                    

                </div>
          
                <?php } ?>    

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}