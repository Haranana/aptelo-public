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
            <div class="naglowek">Eksport danych produktów do pliku inwentaryzacji</div>

                <div class="pozycja_edytowana">  
                
                    <?php
                    $plikDoZapisu = '../export/export_inwentaryzacja_' . date('d_m_Y', time()) . '_' . rand(1,1000000) . '.csv';
                    ?>
                    
                    <input type="hidden" id="plik" value="<?php echo $plikDoZapisu; ?>" />                

                    <input type="hidden" id="kategoria" value="<?php echo ((isset($_GET['kategoria_id'])) ? (int)$_GET['kategoria_id'] : '0'); ?>" />
                    <input type="hidden" id="producent" value="<?php echo ((isset($_GET['producent_id'])) ? (int)$_GET['producent_id'] : '0'); ?>" />
                    
                    <?php
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
                                         pd.language_id, 
                                         pd.products_name
                                  FROM products p, products_to_categories pc, products_description pd
                                  WHERE pd.products_id = p.products_id AND pc.products_id = p.products_id AND p.products_quantity > 0
                                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" ' . $warunki_szukania . ' order by p.products_quantity, pd.products_name asc';  
                                         
                    $sql_ilosc = $db->open_query($zapytanie);
                    ?>                                         

                    <div id="import">
                    
                        <div id="postep">Postęp zapisu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>

                        <div id="procent"></div>  

                    </div>   
                    
                    <div id="wynik_dzialania" style="display:none">
                        <?php
                        $tablicaUsuniecia = array('../export/','export_inwentaryzacja_','.csv');
                        $ciagDekod = $plikDoZapisu;
                        for ($q = 0, $c = count($tablicaUsuniecia); $q < $c; $q++) {
                            $ciagDekod = str_replace($tablicaUsuniecia[$q], '', (string)$ciagDekod);
                        }
                        ?>
                        Dane zostały zapisane w pliku <?php echo '<a href="statystyki/pobieranie.php?plik='.$ciagDekod.'">'.str_replace('../export/', '', (string)$plikDoZapisu).'</a>'; ?>                    
                    </div>                             

                    <script>
                    var ilosc_linii = <?php echo (int)$db->ile_rekordow($sql_ilosc) - 1; ?>;                    
                    //
                    function export_inwentaryzacja(limit) {

                        $.post( "statystyki/inwentaryzacja_akcja.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                plik: $('#plik').val(),
                                kategoria: $('#kategoria').val(),
                                producent: $('#producent').val(),
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

                                 if (ilosc_linii > limit) {
                                    export_inwentaryzacja(limit + 20);
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#wynik_dzialania').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                 } 
                              }                          
                        );
                        
                    }; 
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('inwentaryzacja','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','statystyki');">Powrót</button> 
                    </div>    

                    <script>               
                    // sprawdza czy wogole jest cos do exportu
                    export_inwentaryzacja(0);
                    </script> 

                </div>
           
          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}