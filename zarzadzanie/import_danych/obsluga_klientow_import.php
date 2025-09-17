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
    
    <div id="naglowek_cont">Import danych klientów z plików CSV</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa importu klientów</div>

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'import') { ?>
            
                <div class="pozycja_edytowana">    

                    <input type="hidden" id="plik" value="<?php echo $filtr->process($_POST['plik']); ?>" />
                    <input type="hidden" id="separator" value="<?php echo $filtr->process($_POST['sep']); ?>" />
                    
                    <div id="import">
                    
                        <div id="postep">Postęp importu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent"></div>  

                    </div>   
                    
                    <div id="zaimportowano" style="display:none">
                        Dane z pliku <b><?php echo $filtr->process($_POST['plik']); ?></b> zostały wczytane do sklepu ...
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

                        $.post( "import_danych/import_klientow.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                plik: $('#plik').val(),
                                separator: $('#separator').val(),
                                ilosc_linii: ilosc_linii,
                                limit: limit
                              },
                              function(data) {

                                 if (ilosc_linii > 0) {
                                     procent = parseInt((parseInt(data) / ilosc_linii) * 100);
                                     if (procent > 100) {
                                         procent = 100;
                                     }
                                    } else {
                                     procent = 0;
                                 }
                                 
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + data + '</span>');         
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                                 
                                 if (ilosc_linii - 1 > limit) {
                                    import_csv(parseInt(data));
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#zaimportowano').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                 }   
                                 
                                 if (parseInt(data) > ilosc_linii - 1) {
                                    data = ilosc_linii - 1;
                                 }
                                 $('#licz_produkty').html(data);   
                                 
                              }                          
                        );
                        
                    }; 
                    //            
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_klientow','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
                    </div> 

                    <script>        
                    if (ilosc_linii > 1) {
                        import_csv(1);              
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').css('display','none');
                        $('#procent').html('Dane nie zostały wczytane - nie można odczytać pliku lub plik jest pusty ...');
                        $('#procent').slideDown("fast");
                        $('#przyciski').slideDown("fast");                      
                    }
                    </script>                      

                </div>
                
                <?php } ?>
 
          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}