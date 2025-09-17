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
    
    <div id="naglowek_cont">Eksport zamówień do pliku CSV - Zaufane.pl</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików CSV</div>

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'export') { ?>
            
                <div class="pozycja_edytowana">    
                
                    <?php
                    $plikDoZapisu = '../export/export_csv_zaufane_' . date('d_m_Y', time()) . '_' . rand(1,1000000) . '.csv';
                    ?>
                    
                    <input type="hidden" id="plik" value="<?php echo $plikDoZapisu; ?>" />
                    
                    <?php
                    $warunki_szukania = '';

                    if ( isset($_POST['status']) && $_POST['status'] != '0' ) {
                        $szukana_wartosc = implode(',', (array)$_POST['status']);
                        $warunki_szukania .= " and o.orders_status IN(".$szukana_wartosc.") and osh.orders_status_id IN(".$szukana_wartosc.")";
                    }

                    if ( $_POST['zamowienie_start'] != '' && $_POST['zamowienie_koniec'] == '') {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['zamowienie_start'] . ' 00:00:00')));
                        $warunki_szukania .= " and osh.date_added >= '".$szukana_wartosc."'";
                    }

                    if ( $_POST['zamowienie_start'] == '' && $_POST['zamowienie_koniec'] != '') {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['zamowienie_koniec'] . ' 23:59:59')));
                        $warunki_szukania .= " and osh.date_added <= '".$szukana_wartosc."'";
                    }

                    if ( $_POST['zamowienie_start'] != '' && $_POST['zamowienie_koniec'] != '') {
                        $szukana_wartosc_start = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['zamowienie_start'] . ' 00:00:00')));
                        $szukana_wartosc_koniec = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['zamowienie_koniec'] . ' 23:59:59')));
                        $warunki_szukania .= " and osh.date_added >= '".$szukana_wartosc_start."' and osh.date_added <= '".$szukana_wartosc_koniec."'";
                    }


                    if ( $warunki_szukania != '' ) {
                      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
                    }

                    $zapytanie = "SELECT * FROM orders o
                    LEFT JOIN orders_status_history osh ON osh.orders_id = o.orders_id
                    ".$warunki_szukania." GROUP BY o.orders_id";

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
                        $tablicaUsuniecia = array('../export/','','.csv');
                        $ciagDekod = $plikDoZapisu;
                        for ($q = 0, $c = count($tablicaUsuniecia); $q < $c; $q++) {
                            $ciagDekod = str_replace($tablicaUsuniecia[$q], '', (string)$ciagDekod);
                        }
                        ?>
                        Dane zostały zapisane w pliku <?php echo '<a href="import_danych/pobieranie.php?typ=csv&plik='.$ciagDekod.'">'.str_replace('../export/', '', (string)$plikDoZapisu).'</a>'; ?>
                    </div>                             

                    <script>
                    //
                    var ilosc_linii = <?php echo (int)$db->ile_rekordow($sql_ilosc); ?>;                    
                    var start = <?php echo ( $_POST['zamowienie_start'] != '' ? FunkcjeWlasnePHP::my_strtotime($_POST['zamowienie_start']) : '0' ); ?>;
                    var koniec = <?php echo ( $_POST['zamowienie_koniec'] != '' ? FunkcjeWlasnePHP::my_strtotime($_POST['zamowienie_koniec']) : '0' ); ?>;
                    var status = '<?php echo ( isset($_POST['status']) ? implode(',', (array)$_POST['status']) : '0' ); ?>';
                    //
                    function export_csv(limit) {

                        $.post( "import_danych/export_zamowienia_zaufane.php?tok=<?php echo Sesje::Token(); ?>", 
                            { 
                              plik: $('#plik').val(),
                              limit: limit,
                              start: start,
                              koniec: koniec,
                              status: status,
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
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_zamowien_zaufane','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
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

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}