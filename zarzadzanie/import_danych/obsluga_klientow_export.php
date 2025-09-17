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
    
    <div id="naglowek_cont">Eksport danych klientów do pliku CSV</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików CSV</div>

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'export') { ?>
            
                <div class="pozycja_edytowana">    
                
                    <?php
                    $plikDoZapisu = '../export/export_csv_' . date('d_m_Y', time()) . '_' . rand(1,1000000) . '.csv';
                    ?>
                    
                    <input type="hidden" id="plik" value="<?php echo $plikDoZapisu; ?>" />
                    
                    <?php
                    $rodzaj_klienta = "and customers_guest_account = '0'";
                    //
                    if ( isset($_POST['rodzaj_klientow']) && (int)$_POST['rodzaj_klientow'] == 1 ) {
                         $rodzaj_klienta = '';
                    }
                    //
                    $zapytanie = "select distinct customers_id from customers where customers_id > 0 " . $rodzaj_klienta . " order by customers_firstname, customers_lastname";
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
                        $tablicaUsuniecia = array('../export/','.csv');
                        $ciagDekod = $plikDoZapisu;
                        for ($q = 0, $c = count($tablicaUsuniecia); $q < $c; $q++) {
                            $ciagDekod = str_replace($tablicaUsuniecia[$q], '', (string)$ciagDekod);
                        }
                        ?>
                        Dane zostały zapisane w pliku <?php echo '<a href="import_danych/pobieranie.php?typ=csv&plik='.$ciagDekod.'">'.str_replace('../export/', '', (string)$plikDoZapisu).'</a>'; ?>
                    </div>                             

                    <script>
                    var ilosc_linii = <?php echo (int)$db->ile_rekordow($sql_ilosc); ?>;                    
                    //
                    function export_csv(limit) {

                        $.post( "import_danych/export_klienci.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                plik: $('#plik').val(),
                                rodzaj_klientow: <?php echo (((isset($_POST['rodzaj_klientow']) && (int)$_POST['rodzaj_klientow'] == 1)) ? 1 : 0); ?>,
                                punkty: <?php echo (((isset($_POST['punkty']) && (int)$_POST['punkty'] == 1)) ? 1 : 0); ?>,
                                pola_klientow: <?php echo (((isset($_POST['pola_klientow']) && (int)$_POST['pola_klientow'] == 1)) ? 1 : 0); ?>,
                                kod_pp: <?php echo (((isset($_POST['kod_pp']) && (int)$_POST['kod_pp'] == 1)) ? 1 : 0); ?>,
                                telefon: <?php echo (((isset($_POST['telefon']) && (int)$_POST['telefon'] == 1)) ? 1 : 0); ?>,
                                email: <?php echo (((isset($_POST['email']) && (int)$_POST['email'] == 1)) ? 1 : 0); ?>,
                                panstwo: <?php echo ((isset($_POST['panstwo'])) ? (int)$_POST['panstwo'] : 0); ?>,
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
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_klientow','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
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