<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( !isset($_POST['plik']) ) {
         Funkcje::PrzekierowanieURL('obsluga_csv.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import danych z plików CSV</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików CSV</div>

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'import') { ?>
            
                <div class="pozycja_edytowana">    

                    <div id="import">
                    
                        <div id="postep">Postęp importu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent"></div>  
                        
                        <div class="liczniki" <?php echo (($_POST['rodzaj_import'] == 'dodawanie') ? '' : 'style="display:none"'); ?>>
                            Dodano: <span id="licz_dodane">0</span>
                        </div>
                        <div class="liczniki" <?php echo ((($_POST['rodzaj_import'] == 'aktualizacja')) ? '' : 'style="display:none"'); ?>>
                            Zaktualizowano: <span id="licz_aktualizowane">0</span>
                        </div>                               

                    </div>   
                    
                    <div id="zaimportowano" style="display:none">
                        Dane z pliku <b><?php echo $filtr->process($_POST['plik']); ?></b> zostały wczytane do sklepu ...
                    </div>   

                    <div id="zobacz_raport" onclick="$('#raport').show()">zobacz jakie produkty zaimportowano</div>
                    <div style="margin:17px">
                        <ol id="raport"></ol>
                    </div>                    

                    <script>
                    //
                    <?php
                    $plik = '../import/' . $filtr->process($_POST['plik']);
                    $liczba_linii = Funkcje::IloscLinii($plik);               
                    ?>
                    var ilosc_linii = <?php echo $liczba_linii; ?>;
                    //
                    function import_csv(limit) {

                        $.post( "import_danych/import<?php echo ((isset($_POST['typ']) && $_POST['typ'] == 'cechy') ? '_aktualizacja_cech' : ''); ?>.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                format_importu: 'csv',
                                struktura: 'csv',
                                plik: '<?php echo $filtr->process($_POST['plik']); ?>',
                                separator: '<?php echo $filtr->process($_POST['sep']); ?>',
                                rodzaj_import: '<?php echo $filtr->process($_POST['rodzaj_import']); ?>',
                                typ: '<?php echo $filtr->process($_POST['typ']); ?>',
                                import_zdjec: '<?php echo $filtr->process($_POST['import_zdjec']); ?>',
                                ilosc_linii: ilosc_linii,
                                limit: limit
                              },
                              function(data) {

                                 if (ilosc_linii > 0) {
                                     procent = parseInt((parseInt(data.suma) / ilosc_linii) * 100);
                                     if (procent > 100) {
                                         procent = 100;
                                     }
                                    } else {
                                     procent = 0;
                                 }
                                 
                                 <?php if ($_POST['typ'] == 'kategorie') { ?>
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><span id="licz_produkty" style="display:none"></span>');                              
                                 <?php } else { ?>
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + data.suma + '</span>');         
                                 <?php } ?>
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                                 
                                 // aktualizacja licznika dodawania i aktualizacji
                                 var dodane = parseInt($('#licz_dodane').html()) + parseInt(data.dodane);
                                 var zaktualizowane = parseInt($('#licz_aktualizowane').html()) + parseInt(data.aktualizacja);  

                                 $('#licz_dodane').html(dodane);                                  
                                 $('#licz_aktualizowane').html(zaktualizowane);                                  
                                 $('#raport').html( $('#raport').html() + data.nazwy );                                   
                                 
                                 if (ilosc_linii - 1 > limit) {
                                    import_csv(parseInt(data.suma));
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#zaimportowano').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                    
                                    if ( parseInt($('#licz_dodane').html()) > 0 || parseInt($('#licz_aktualizowane').html()) > 0 ) {
                                         $('#zobacz_raport').slideDown("fast");
                                    }                                    
                                                                        
                                 }   
                                 
                                 if (parseInt(data.suma) > ilosc_linii - 1) {
                                    data.suma = ilosc_linii - 1;
                                 }
                                 $('#licz_produkty').html(data.suma);   
                                 
                              },
                              "json" 
                              
                        );
                        
                    }; 
                    //            
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_csv','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
                    </div> 

                    <script>    
                    if (ilosc_linii > 1) {
                        import_csv(1);              
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').css('display','none');
                        $('#procent').html('Dane nie zostały wczytane - nie można odczytać pliku, plik ma zły format danych lub plik jest pusty ...');
                        $('#procent').slideDown("fast");
                        $('#przyciski').slideDown("fast"); 
                        $('.liczniki').hide();                    
                    }
                    </script>                      

                </div>
                
                <?php } ?>
 
          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}