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
            <div class="naglowek">Obsługa plików zewnętrznych struktur XML</div>

                <div class="pozycja_edytowana">  
                            
                    <script>
                    function zazn_plik() {
                        $('#plik_zew').prop('checked', true); 
                    }
                    
                    function doda(id) {
                        if (id == 1) {
                            $('#vat').slideDown('fast'); 
                            $('#kategoria_glowna').slideDown('fast'); 
                            $('#aktXML').hide();
                            $('#dodXML').slideDown('fast');   
                            $('#wylaczenie_produktow').slideUp('fast');
                        }
                        if (id == 0) {
                            $('#vat').slideUp('fast');
                            $('#kategoria_glowna').slideUp('fast'); 
                            $('#dodXML').hide();
                            $('#aktXML').slideDown('fast');                                           
                            $('#wylaczenie_produktow').slideDown('fast');
                        }                                        
                    }                                  
                    
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
                                document.location = '/zarzadzanie/import_danych/xml_import_zewnetrzny.php';
                            } else {
                                document.location = '/zarzadzanie/import_danych/xml_import_zewnetrzny.php';
                            }
                        }
                    };

                    $('#plikForm').ajaxForm(options);
                    
                    $(function() {
                        $('#wgraj').MultiFile({
                            max: 1,
                            accept: 'xml',
                            STRING: {
                                denied: 'Nie można przesłać pliku w tym formacie $ext!',
                                duplicate: 'Taki plik jest już dodany:\n$file!',
                                selected: 'Wybrany plik: $file',
                            }
                        }); 
                    });     

                    function wlaczInp() {
                        $("#importXMLform :input").attr('disabled', false);
                    }
                    
                    function ZmienStrukture(wartosc) {
                        if ( wartosc != '' ) {
                            $.get("import_danych/plugin/" + wartosc + ".php",
                                { tylko_rekordy: 'tak' },
                                function(data) { 
                                    $("#opisImportu").html(data);
                                    $("#calaObsluga").slideDown();
                                    $("#rd").prop('checked', true); 
                                    $("#ra").prop('checked', false); 
                            });                                        
                          } else {
                            $("#calaObsluga").slideUp();
                        }
                    }
                    </script>                                
                
                    <form action="import_danych/xml_import_zewnetrzny_obsluga.php" method="post" class="cmxform" id="importXMLform">   
                
                    <div>
                        <input type="hidden" name="akcja" value="import" />
                        <input type="hidden" name="typ" value="wszystkie" />
                    </div>
                    
                    <div class="poleForm">
                        <div class="naglowek">Import i aktualizacja danych</div>
                
                        <div class="NaglowekCsv">Wybierz plik do importu</div>
                    
                        <div class="ListaPlikowXml">
                            
                            <table>
                            
                            <tr class="TyNaglowek">
                                <td>Nazwa pliku</td>
                                <td>Rozmiar</td>
                                <td>Data</td>
                                <td style="width:4%">Usuń</td>
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
                                            // czas pliku
                                            $czas_pliku = filemtime($dir . $file);
                                            // wielkosc pliku
                                            $wielkosc_pliku = filesize($dir . $file);
                                            if ($wielkosc_pliku > 1048576) {
                                                $kb = number_format(round(($wielkosc_pliku/1048576), 1), 1, '.', '') . ' MB';
                                            } elseif ($wielkosc_pliku > 1024) {
                                                $kb = number_format(round(($wielkosc_pliku/1024), 0), 2, '.', '') . ' kB';
                                            } else  {
                                                $kb = number_format($wielkosc_pliku, 0, '.', '') . ' B';
                                            }                                                 
                                            //                                            
                                            if (preg_match('@(.*)\.(xml)@i',$file)) {
                                                //
                                                echo '<tr>
                                                          <td><span><input type="radio" name="plik" id="' . $licznik . '" value="' . $file . '" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><label class="OpisFor" for="' . $licznik . '">' . $file . '</label></span></td>
                                                          <td>' . $kb . '</td>
                                                          <td>' . date('d-m-Y H:i', $czas_pliku) . '</td>
                                                          <td><a class="TipChmurka" href="narzedzia/przegladarka_usun.php?xml_zew=' . base64_encode((string)$file) . '"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></td>
                                                      </tr>';
                                                      $ilosc_plikow = true;
                                                      $licznik++;
                                                //
                                            }
                                            //
                                            unset($kb, $rozszerzenie);
                                            //
                                        }                                        
                                    }
                                    closedir($dh);
                                }
                            }  
                            
                            if ( $licznik == 1 ) {
                                  echo '<tr><td colspan="4" style="padding:20px;text-align:center;color:#a1a1a1">Brak plików do importu ...</td></tr>';
                            }                              

                            echo '<tr><td colspan="4" style="padding-top:8px;padding-bottom:5px"><span><input type="radio" id="plik_zew" name="plik" value="url" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="plik_zew">zewnętrzny adres pliku:</label></span><input onclick="zazn_plik()" type="text" size="40" value="" name="adres_url" /><em class="TipIkona"><b>Należy podać pełen adres pliku z http://</b></em></td></tr>';
                            unset($ilosc_plikow);             
                            ?>
                            
                            </table>
                            
                        </div>
                        
                        <div class="NaglowekCsv">Wybierz strukturę pliku</div>
                        
                        <div class="PolaWyboru WybierzStrukture">
                        
                            <select name="struktura" onchange="ZmienStrukture(this.value)">
                                <option value="">-- wybierz strukturę --</option>
                                <?php
                                $dir = 'import_danych/plugin/';

                                if (is_dir($dir)) {
                                    if ($dh = opendir($dir)) {
                                        while (($file = readdir($dh)) !== false) {
                                            if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                                //
                                                if (preg_match('@(.*)\.(php)@i',$file)) {
                                                    //
                                                    if ( !strpos((string)$file, '_ilosc') ) {
                                                        //
                                                        $plikZawartosc = file_get_contents($dir . $file);
                                                        if ( strpos((string)$plikZawartosc, "_GET['tylko_rekordy']") > -1 ) {
                                                            //
                                                            $file = str_replace('.php', '', (string)$file);
                                                            $opis = strtoupper(substr((string)$file,0,1)) . substr((string)$file,1);
                                                            //
                                                            if (strpos((string)$plikZawartosc,'{{') > -1) {
                                                                $preg = preg_match('|{{([0-9A-Za-ząćęłńóśźż _,;:-?()]+?)}}|', $plikZawartosc, $matches);
                                                                $opis = $matches[1];
                                                            }
                                                            //
                                                            echo '<option value="' . $file . '">' . $opis . '</option>';
                                                            //
                                                            unset($file, $opis);
                                                        }
                                                        unset($plikZawartosc);
                                                        //
                                                    }
                                                    //
                                                }
                                                //
                                            }
                                        }
                                        closedir($dh);
                                    }
                                }  

                                unset($div);             
                                ?>                                            
                            </select>
                            
                        </div>
                        
                        <div id="calaObsluga" style="display:none">
                        
                            <div class="NaglowekCsv">Rodzaj importu</div>
                            
                            <div class="PolaWyboru">
                            
                                <input type="radio" onclick="doda(1)" checked="checked" id="rd" value="dodawanie" name="rodzaj_import" /> <label class="OpisFor" for="rd">dodawanie danych</label>
                                <input type="radio" onclick="doda(0)" value="aktualizacja" id="ra" name="rodzaj_import" /> <label class="OpisFor" for="ra">aktualizacja danych</label>     

                            </div>
                            
                            <div id="opisImportu"></div>
                            
                            <?php
                            $zapytanie = "select * from tpl_xml order by tpl_xml_name";
                            $sql = $db->open_query($zapytanie); 

                            if ((int)$db->ile_rekordow($sql) > 0) {
                            ?>
                            
                            <div class="NaglowekCsv">Wybierz schemat importu pliku</div>
                            
                            <div class="PolaWyboru">
                            
                                <?php 
                                $tablica = array();
                                $tablica[] = array('id' => 0, 'text' => '-- brak --');
                                while ($info = $sql->fetch_assoc()) { 
                                    $tablica[] = array('id' => $info['tpl_xml_id'], 'text' => $info['tpl_xml_name']);
                                }
                                echo Funkcje::RozwijaneMenu('szablon', $tablica, '', ' id="WyborSchematu"'); 
                                ?>

                            </div>

                            <?php } else { ?>
                            
                                <input type="hidden" name="szablon" value="0" />
                            
                            <?php }
                            $db->close_query($sql);
                            unset($zapytanie, $info);

                            ?>
                            
                            <script>
                            $(document).ready(function() {
                                $('#WyborSchematu').change(function() {
                                    if ( parseInt($(this).val()) > 0 ) {
                                         $('#WpisanieMarzy').stop().slideUp();
                                    } else {
                                         $('#WpisanieMarzy').stop().slideDown();
                                    }
                                });
                            });
                            </script>                              
                            
                            <div id="WpisanieMarzy">
                            
                                <div class="NaglowekCsv" style="padding-top:2px"><span style="color:#ff0000">lub</span> ustaw taką samą marżę dla wszystkich importowanych produktów</div>
                                
                                <div class="PolaWyboru">
                                
                                    Dolicz do ceny: &nbsp; <input type="text" size="5" value="" name="marza_wlasna" /> % <em class="TipIkona"><b>Zmiana cen będzie dotyczyć wszystkich cen (również hurtowych) oraz ceny poprzedniej dla promocji</b></em>
                                    
                                </div> 

                            </div>
                            
                            <div id="vat">
                            
                                <div style="display:flex;flex-wrap:wrap;align-items:center">
                                
                                    <div class="NaglowekCsv" style="padding:8px">Wybierz podatek VAT produktów</div>
                                    
                                    <div class="PolaWyboru" style="margin:5px">
                                    
                                        <?php
                                        // pobieranie informacji o vat
                                        $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
                                        $sqls = $db->open_query($zapytanie_vat);
                                        //
                                        $tablica = array();
                                        //
                                        while ($infs = $sqls->fetch_assoc()) { 
                                            $tablica[] = array('id' => $infs['tax_rate'] . ',' . $infs['tax_short_description'], 'text' => $infs['tax_description']);
                                        }
                                        $db->close_query($sqls);
                                        unset($zapytanie_vat, $infs);  
                                        //             
                                        echo Funkcje::RozwijaneMenu('vat', $tablica, 'x'); 
                                        unset($tablica);
                                        ?>

                                    </div>     
                                 
                                </div> 

                            </div>
                            
                            <div style="display:flex;align-items:center">
                            
                                <div class="NaglowekCsv" style="padding:8px">Zaokrąglanie cen</div>
                            
                                <div class="PolaWyboru" style="margin:5px">
                            
                                    <input type="radio" id="zaokraglanie_cen_brak" name="zaokraglanie_cen" value="zaokraglanie_cen_brak" checked="checked" /> <label class="OpisFor" for="zaokraglanie_cen_brak">nie zaokrąglaj ceny brutto do pełnych kwot</label><br />
                                    <input type="radio" id="zaokraglanie_cen_zero" name="zaokraglanie_cen" value="zaokraglanie_cen_zero" /> <label class="OpisFor" for="zaokraglanie_cen_zero">zaokrąglij cenę brutto do pełnej kwoty</label><br />
                                    <input type="radio" id="zaokraglanie_cen_ulamek" name="zaokraglanie_cen" value="zaokraglanie_cen_ulamek" /> <label class="OpisFor" for="zaokraglanie_cen_ulamek">zaokrąglij cenę brutto do pełnej kwoty z jednym miejscem po przecinku (np 3,44 na 3,40 lub 3,47 na 3,50)</label>
                                
                                </div>
                            
                            </div>                                
                            
                            <div style="display:flex;flex-wrap:wrap;align-items:center">
                            
                                <div class="NaglowekCsv" style="padding:8px">Prefix do nr katalogowego</div>
                            
                                <div class="PolaWyboru" style="margin:5px">
                            
                                    <input type="text" size="25" value="" name="prefix_nr_kat" id="prefix_nr_kat" /> <em class="TipIkona"><b>Do nr katalogowego zostanie dodany wposany prefix</b></em>
                                
                                </div>
                            
                            </div>
                            
                            <div style="display:flex;flex-wrap:wrap;align-items:center">
                            
                                <div class="NaglowekCsv" style="padding:8px">Id zewnętrzne</div>
                            
                                <div class="PolaWyboru" style="margin:5px">
                            
                                    <input type="text" size="25" value="" name="id_zewnetrzne" id="id_zewnetrzne" /> <em class="TipIkona"><b>Do importowanych produktów zostanie dodane Id zewnętrzne - np nazwa dostawcy od którego pochodzi plik</b></em>
                                
                                </div>
                            
                            </div>      

                            <div id="kategoria_glowna">
                            
                                <div style="display:flex;flex-wrap:wrap;align-items:center">
                                
                                    <div class="NaglowekCsv" style="padding:8px">Nazwa kategorii głównej</div>
                                
                                    <div class="PolaWyboru" style="margin:5px">
                                
                                        <input type="text" size="55" value="" name="kategoria_glowna" /> <em class="TipIkona"><b>Importowane produkty zostaną przypisane do podanej kategorii. Jeżeli kategoria nie istnieje - zostnaie utworzona.</b></em>
                                    
                                    </div>
                                
                                </div>   

                            </div> 
                            
                            <div id="wylaczenie_produktow" style="display:none">
                            
                                <div class="NaglowekCsv">Zmień status produktów z w/w prefixem lub id zewnętrznym na <b>nieaktywny</b> przed dokonaniem aktualizacji i włącz tylko produkty, które są w pliku xml</div>
                            
                                <div class="PolaWyboru">
                                
                                    <input type="radio" id="wylaczenie_produktow_tak" name="wylaczenie_produktow" value="1" /> <label class="OpisFor" for="wylaczenie_produktow_tak">zmień status na nieaktywny</label>
                                    <input type="radio" id="wylaczenie_produktow_nie" name="wylaczenie_produktow" value="0" checked="checked" /> <label class="OpisFor" for="wylaczenie_produktow_nie">nie zmieniaj</label>

                                </div>
                                
                                <div style="margin:8px">
                                    <div class="ostrzezenie">Opcja zadziała tylko jeżeli będzie w/w pozycjach podany prefix lub id zewnętrzne - zadziała tylko dla produktów, które mają uzupełnione te parametry</div>
                                </div>

                            </div>                             
                            
                            <div class="przyciski_dolne" style="padding-left:0px">
                              <input type="submit" class="przyciskNon" onclick="wlaczInp()" value="Importuj dane XML" />
                            </div>  
                             
                        </div>

                    </div>
                    
                    </form>
                    
                    <br />
                    
                    <form action="import_danych/xml_import_zewnetrzny.php" method="post" class="cmxform" id="plikForm" enctype="multipart/form-data"> 
                
                    <div class="poleForm">
                        <div class="naglowek">Wgrywanie plików xml do importu</div>
                        
                        <div class="ListaWgraj">
                        
                            <span class="ostrzezenie">
                                Maksymalna ilość plików: 1, maksymalna wielkość pliku: <?php echo ((Funkcje::MaxUpload() < 150) ? Funkcje::MaxUpload() : '150' ); ?>MB
                            </span>
                            
                            <input type="file" name="file[]" id="wgraj" size="45" />
                            
                            <div class="cl"></div>
                            
                            <input id="form_submit" style="margin-left:0px" type="submit" class="przyciskNon" value="Wgraj wybrany plik" />
                            <input type="hidden" name="katalog" value="import/" />
                            <input type="hidden" name="dozwolone" value="<?php echo PLIKI_IMPORT_XML; ?>" />
                            <div id="ladowanie" style="display:none;"><img src="obrazki/_loader.gif" alt="przetwarzanie..." /></div>
                        
                        </div>                          
                    </div>                           
                    
                    </form>                                  
                                
                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}