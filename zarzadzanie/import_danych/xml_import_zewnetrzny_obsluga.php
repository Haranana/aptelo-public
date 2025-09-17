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
    
    <div id="naglowek_cont">Import danych z zewnętrznych struktur XML</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików XML</div>

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
                        <div class="liczniki" <?php echo (($_POST['rodzaj_import'] == 'dodawanie') ? 'style="display:none"' : ''); ?>>
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
                    $JestPlik = false;
                    if ($_POST['plik'] == 'url' && strpos((string)$_POST['adres_url'], '.xml') > -1 && Funkcje::CzyJestPlik($_POST['adres_url'])) {
                        // 
                        $plik_z_produktami = $_POST['adres_url'];
                        $JestPlik = true;
                        //
                      } else if ($_POST['plik'] != 'url') {
                        //
                        $plik_z_produktami = "../import/" . $_POST['plik'];
                        $JestPlik = true;
                        //
                    }        

                    if ($JestPlik == true) {
                        $dane_produktow = simplexml_load_file($plik_z_produktami);
                        $liczba_linii = 0;
                        if ( is_file('import_danych/plugin/' . $filtr->process($_POST['struktura']) . '_ilosc.php') ) {
                             include('import_danych/plugin/' . $filtr->process($_POST['struktura']) . '_ilosc.php'); 
                        }
                       } else {
                        $liczba_linii = 0;
                    }
                    
                    // zakres importu
                    $zakres = array();
                    $przedrostek = '';
                    if ( $_POST['rodzaj_import'] == 'dodawanie' ) {
                         $przedrostek = 'dod_';
                       } else {
                         $przedrostek = 'akt_';
                    }
                    //
                    foreach ( $_POST as $klucz => $pole ) {
                      //
                      if ( strpos((string)$klucz, $przedrostek) > -1 ) {
                           $zakres[] = str_replace($przedrostek, '', (string)$klucz);
                      }
                      //
                    }
                    //
                    ?>
                    var ilosc_linii = <?php echo $liczba_linii; ?>;
                    //
                    function import_xml(limit) {

                        $.post( "import_danych/import.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                format_importu: 'xml',
                                struktura: '<?php echo $filtr->process($_POST['struktura']); ?>',
                                plik: '<?php echo $filtr->process($_POST['plik']); ?>',
                                rodzaj_import: '<?php echo $filtr->process($_POST['rodzaj_import']); ?>',
                                adres_url: '<?php echo $filtr->process($_POST['adres_url']); ?>',
                                marza: '<?php echo $filtr->process($_POST['marza_wlasna']); ?>',
                                prefix_nr_kat: '<?php echo $filtr->process($_POST['prefix_nr_kat']); ?>',
                                id_zewnetrzne: '<?php echo $filtr->process($_POST['id_zewnetrzne']); ?>',
                                kategoria_glowna: '<?php echo $filtr->process($_POST['kategoria_glowna']); ?>',
                                wylaczenie_produktow: '<?php echo (int)$_POST['wylaczenie_produktow']; ?>',
                                typ: '<?php echo $filtr->process($_POST['typ']); ?>',
                                szablon: '<?php echo $filtr->process($_POST['szablon']); ?>',
                                vat: '<?php echo $filtr->process($_POST['vat']); ?>',
                                zaokraglanie: '<?php echo $filtr->process($_POST['zaokraglanie_cen']); ?>',
                                ilosc_linii: ilosc_linii,
                                zakres_importu: '<?php echo serialize($zakres); ?>',
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
  
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + data.suma + '</span>'); 
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                                 
                                 // aktualizacja licznika dodawania i aktualizacji
                                 var dodane = parseInt($('#licz_dodane').html()) + parseInt(data.dodane);
                                 var zaktualizowane = parseInt($('#licz_aktualizowane').html()) + parseInt(data.aktualizacja);  

                                 $('#licz_dodane').html(dodane);                                  
                                 $('#licz_aktualizowane').html(zaktualizowane);                                  
                                 $('#raport').append(data.nazwy);
                                                                            
                                 if (ilosc_linii-1 > limit) {
                                    setTimeout(() => import_xml(parseInt(data.suma)), 50);  // 50 ms pauzy
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#zaimportowano').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                    
                                    if ( parseInt($('#licz_dodane').html()) > 0 || parseInt($('#licz_aktualizowane').html()) > 0 ) {
                                         $('#zobacz_raport').slideDown("fast");
                                    }

                                 } 
                                 
                                 if (parseInt(data.suma) > ilosc_linii) {
                                    data.suma = ilosc_linii;
                                 }
                                 $('#licz_produkty').html(data.suma); 
                                 
                              },
                              "json"                        
                        );
                        
                    }; 
                    //
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('xml_import_zewnetrzny','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
                    </div> 

                    <script>       
                    if (ilosc_linii > 0) {
                        import_xml(0);              
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
                
                <?php 
                unset($zakres, $przedrostek, $liczba_linii);
                } ?>
 
          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}