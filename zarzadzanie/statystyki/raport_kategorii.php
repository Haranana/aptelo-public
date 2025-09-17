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
            <div class="naglowek">Raport sprzedaży produktów dla poszczególnych kategorii</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje sprzedaż produktów dla poszczególnych kategorii w określonym przedziale czasowym</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                     
                    
                    <form action="statystyki/raport_kategorii.php" method="post" id="statForm" class="cmxform">
                    
                    <?php
                    /*
                    jezeli data poczatkowa ma byc 10 dni wstecz od daty dzisiejszej
                    direction: ['<?php echo date('d-m-Y', time() - (86400 * 10)); ?>', '<?php echo date('d-m-Y', time()); ?>'],
                    */
                    ?>
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        direction: false,
                        readonly_element: true
                      });                
                    });
                    </script>                    
                    
                    <div id="zakresDat" style="margin-top:10px">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : date('d-m-Y', time())); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : date('d-m-Y', time())); ?>" size="10" class="datepicker" />

                        <span style="margin-left:20px">Status:</span>
                        <?php
                        $tablia_status= Array();
                        $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:170px"'); ?>
                    </div>                     

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/raport_kategorii.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?> 

                    <div class="cl"></div>

                    </form>

                    <?php
                    //
                    $DrzewoKategorii = Kategorie::DrzewoKategorii();
                    ?>
                    
                    <input type="hidden" id="idk" value="1" />                    
                    <input type="hidden" id="brak" value="1" />
                    
                    <div id="prel">Trwa generowanie raportu (<span id="procent"></span>%)<img src="obrazki/_loader.gif" alt="Generowanie danych" /></div>

                    <div class="RamkaStatystyki" style="display:none;margin-top:15px">
                    
                        <table class="TabelaStatystyki" id="wynikStatystyka">
                            <tr class="TyNaglowek">
                            <td>Nr katalogowy</td>
                            <td>Nazwa produktu</td>
                            <td style="text-align:center">Ilość sprzedanych</td>
                            <td style="text-align:center">Wartość netto</td>
                            <td style="text-align:center">Wartość brutto</td>
                            </tr>                    
                        </table>  
                        
                    </div>
                    
                    <script>
                    <?php
                    echo 'kategorie = new Array(';
                    //
                    $KatId = '';
                    for ($f = 0, $c = count($DrzewoKategorii); $f < $c; $f++) { 
                         $KatId .= $DrzewoKategorii[$f]['id'] .',';
                    }
                    echo substr((string)$KatId,0,-1);
                    unset($KatId);
                    //
                    echo ');' . "\n";
                    ?>
                    
                    function wyswietlRaport() {
                        //
                        var pozycja = $('#idk').val();
                        $('#procent').html(parseInt( (pozycja / kategorie.length) * 100 ));
                        //
                        $.post( "ajax/raport_kategorii.php?tok=<?php echo Sesje::Token(); ?>", 
                          { 
                            <?php echo ((isset($_GET['data_od']) && $_GET['data_od'] != '') ? 'data_od: \'' . $_GET['data_od'] . '\',' : ''); ?>
                            <?php echo ((isset($_GET['data_do']) && $_GET['data_do'] != '') ? 'data_do: \'' . $_GET['data_do'] . '\',' : ''); ?>
                            <?php echo ((isset($_GET['szukaj_status']) && (int)$_GET['szukaj_status'] > 0) ? 'szukaj_status: ' . (int)$_GET['szukaj_status'] . ',' : ''); ?>
                            id: kategorie[pozycja - 1]
                          },
                          function(data) {
                            $('#wynikStatystyka').append(data);
                            //
                            if ( data != '' ) {
                                 $('#brak').val('0');
                            }
                            //
                            if ( parseInt(pozycja) < kategorie.length ) {
                                 $('#idk').val( parseInt(pozycja) + 1 );
                                 wyswietlRaport();
                               } else {
                                 $('#prel').slideUp( function() {
                                     //
                                     if ( $('#brak').val() == '1' ) {
                                          $('#prel').html('<div class="RamkaStatystyki" style="padding:10px">Brak wyników ...</div>');
                                          $('#prel').slideDown();
                                        } else { 
                                          $('.RamkaStatystyki').slideDown();
                                     }
                                     //
                                 });
                            }
                            //
                          }
                        );
                        //
                    }
                    
                    wyswietlRaport();
                    </script>               

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}