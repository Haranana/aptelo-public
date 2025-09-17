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
    
    <div id="naglowek_cont">Import / eksport danych klientów z plików CSV</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa importu i eksportu klientów</div>

                <div class="pozycja_edytowana">  
                
                    <script>             
                    var options = { 
                        target: '#ladowanie', 
                        url: 'ajax/ajax_plik_wgraj.php?tok=<?php echo Sesje::Token(); ?>',
                        beforeSend:function() {
                            $("#ladowanie").show();
                        },
                        complete:function() {
                            $("#ladowanie").hide();
                            if ( $("#ladowanie").html() != '' ) {
                                alert( $("#ladowanie").html() );
                                document.location = '/zarzadzanie/import_danych/obsluga_klientow.php';
                            } else {
                                document.location = '/zarzadzanie/import_danych/obsluga_klientow.php';
                            }
                        }
                    };

                    $('#plikForm').ajaxForm(options);
                    
                    $(function() {
                        $('#wgraj').MultiFile({
                            max: 1,
                            accept: 'csv',
                            STRING: {
                                denied: 'Nie można przesłać pliku w tym formacie $ext!',
                                duplicate: 'Taki plik jest już dodany:\n$file!',
                                selected: 'Wybrany plik: $file',
                            }
                        }); 
                    });
                    </script>                  

                    <div class="TabelaCsv">
                        
                        <div class="OknoImportu">
                            
                            <form action="import_danych/obsluga_klientow_import.php" method="post" class="cmxform">   

                            <div class="poleForm">
                            
                                <input type="hidden" name="akcja" value="import" />
                            
                                <div class="naglowek">Import danych</div>
                        
                                <div class="NaglowekCsv">Wybierz plik do importu</div>
                            
                                <div class="ListaPlikow">

                                    <table class="TabelaPlikow">
                                    
                                    <tr class="Naglowek">
                                        <td>Plik</td>
                                        <td>Rozmiar</td>
                                        <td>Data</td>
                                        <td style="width:6%">Usuń</td>
                                    </tr>
                                    
                                    <?php
                                    $dir = '../import/';
                                    
                                    $ilosc_plikow = false;

                                    $licznik = 1;
                                    if (is_dir($dir)) {
                                        if ($dh = opendir($dir)) {
                                            while (($file = readdir($dh)) !== false) {
                                                if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                                    //
                                                    if (preg_match('@(.*)\.(csv)@i',$file)) {
                                                        //
                                                        echo '<tr>
                                                                 <td><span><input type="radio" id="' . $licznik . '" name="plik" value="' . $file . '" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><label class="OpisFor" for="' . $licznik . '">' . $file . '</label></span></td>
                                                                
                                                                 <td>';
                                                                 
                                                                 // wielkosc pliku
                                                                 $wielkosc_pliku = filesize($dir . $file);
                                                                 if ($wielkosc_pliku > 1048576) {
                                                                     echo number_format(round(($wielkosc_pliku/1048576), 1), 1, '.', '') . ' MB';
                                                                 } elseif ($wielkosc_pliku > 1024) {
                                                                     echo number_format(round(($wielkosc_pliku/1024), 0), 2, '.', '') . ' kB';
                                                                 } else  {
                                                                     echo number_format($wielkosc_pliku, 0, '.', '') . ' B';
                                                                 }    
                                                                 unset($wielkosc_pliku);
                                                                 
                                                                 echo '</td>
                                                                 
                                                                 <td>' . date('d-m-Y H:i',filemtime($dir . $file)) . '</td>
                                                                 <td><a class="TipChmurka" href="narzedzia/przegladarka_usun.php?csv_klient=' . base64_encode((string)$file) . '"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></td>                                                                
                                                                
                                                              </tr>';
                                                              $ilosc_plikow = true;
                                                              $licznik++;
                                                        //
                                                    }
                                                    //
                                                }                                                
                                            }
                                            closedir($dh);
                                        }
                                    }                        

                                    if ( $licznik == 1 ) {
                                          echo '<tr><td colspan="4" style="padding:20px;text-align:center;color:#a1a1a1">Brak plików do importu ...</td></tr>';
                                    }                                      
                                                 
                                    ?>
                                    </table>
                                </div>
                                
                                <div class="NaglowekCsv">Separator pól</div>
                                
                                <div class="PolaWyboru">
                                
                                    <input type="radio" checked="checked" value=";" name="sep" id="srednik" /><label class="OpisFor" for="srednik">; (średnik)<em class="TipIkona"><b>Pola w importowanym pliku są rozdzielone średnikiem</b></em></label>
                                    <input type="radio" value=":" name="sep" id="dwukropek" /><label class="OpisFor" for="dwukropek">: (dwukropek)<em class="TipIkona"><b>Pola w importowanym pliku są rozdzielone dwukropkiem</b></em></label>
                                    <input type="radio" value="," name="sep" id="przecinek" /><label class="OpisFor" for="przecinek">, (przecinek)<em class="TipIkona"><b>ola w importowanym pliku są rozdzielone przecinkiem</b></em></label>
                                    <input type="radio" value="#" name="sep" id="plotek" /><label class="OpisFor" for="plotek"># (płotek)<em class="TipIkona"><b>Pola w importowanym pliku są rozdzielone płotkiem</b></em></label>
                                
                                </div>
                                
                                <div class="przyciski_dolne" style="padding-left:0px">
                                  <input type="submit" class="przyciskNon" value="Importuj dane CSV" />
                                </div>                                    

                            </div>
                            
                            </form>
                            
                        </div>
                        
                        <div class="OknoImportu">
                        
                            <form action="import_danych/obsluga_klientow_export.php" method="post" class="cmxform">

                            <div class="poleForm">
                            
                                <input type="hidden" name="akcja" value="export" />    
                            
                                <div class="naglowek">Eksport danych</div>
                                
                                <div class="ListaExport" style="margin-top:0px">
                                
                                    <table class="InputExport">
                                        <tr>
                                          <td>
                                            <input type="radio" checked="checked" value="wszystkie" name="zakres" id="wszystkie" /><label class="OpisForPustyLabel" for="wszystkie"></label>
                                          </td>
                                          <td>
                                              <span style="display:block">
                                              
                                                  <div><input type="checkbox" value="1" name="rodzaj_klientow" id="rodzaj_klientow" /><label class="OpisFor" for="rodzaj_klientow">eksportuj konta klientów z rejestracją i bez rejestracji</label></div>
                                                  
                                                  <br />pobierz <b>wszystkie dane</b> klientów plus dodatkowo
                                                  <div style="margin-top:10px"><input type="checkbox" value="1" name="punkty" id="punkty" /><label class="OpisFor" for="punkty">pobierz dane ilości punktów przypisanych do konta klienta</label></div>
                                                  <div style="margin-top:2px"><input type="checkbox" value="1" name="pola_klientow" id="pola_klientow" /><label class="OpisFor" for="pola_klientow">pobierz dane dodatkowych pól klientów</label></div>
                                                  <div style="margin-top:2px"><input type="checkbox" value="1" name="kod_pp" id="kod_pp" /><label class="OpisFor" for="kod_pp">pobierz kod Programu Partnerskiego klienta</label></div>
                                                  
                                                  <br />pobierz <b>wybrane dane</b> klientów
                                                  <div style="margin-top:10px"><input type="checkbox" value="1" name="telefon" id="telefon" /><label class="OpisFor" for="telefon" checked="checked">pobierz numery telefonów</label></div>
                                                  <div style="margin-top:2px"><input type="checkbox" value="1" name="email" id="email" /><label class="OpisFor" for="email" checked="checked">pobierz adresy e-mail</label></div>
                                                  
                                                  <br />pobierz <b>tylko</b> klientów z kraju
                                                  <div style="margin-top:10px">
                                                  <?php
                                                  $tablicaPanstw = Klienci::ListaPanstw();
                                                  $tablicaPanstw[0] = array('id' => 0, 'text' => '-- dowolny --');
                                                  echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw); 
                                                  ?>
                                                  </div>
                                                  
                                              </span>
                                          </td>
                                        </tr>
                                    </table>

                                    <div class="przyciski_dolne" style="padding-left:0px">
                                      <input type="submit" class="przyciskNon" value="Eksportuj dane CSV" />
                                    </div>                                         
                                    
                                </div>
                            </div>
                            
                            </form>
                            
                            <br />
                            
                            <form action="import_danych/obsluga_klientow.php" method="post" class="cmxform" id="plikForm" enctype="multipart/form-data"> 
                        
                            <div class="poleForm">
                                <div class="naglowek">Wgrywanie plików csv do importu</div>
                                
                                <div class="ListaWgraj">
                                
                                    <span class="ostrzezenie">
                                        Maksymalna ilość plików: 1, maksymalna wielkość pliku: <?php echo ((Funkcje::MaxUpload() < 150) ? Funkcje::MaxUpload() : '150' ); ?>MB
                                    </span>
                                    
                                    <input type="file" name="file[]" id="wgraj" size="45" />
                                    
                                    <div class="cl"></div>
                                    
                                    <input id="form_submit" style="margin-left:0px" type="submit" class="przyciskNon" value="Wgraj wybrany plik" />
                                    <input type="hidden" name="katalog" value="import/" />
                                    <input type="hidden" name="dozwolone" value="<?php echo PLIKI_IMPORT_CSV; ?>" />
                                    <div id="ladowanie" style="display:none;"><img src="obrazki/_loader.gif" alt="przetwarzanie..." /></div>
                                
                                </div>                          
                            </div>                           
                            
                            </form>                                  
                            
                        </div>
                            
                    </div>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}