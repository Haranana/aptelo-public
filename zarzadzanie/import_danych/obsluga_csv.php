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
    
    <div id="naglowek_cont">Import / eksport danych z plików CSV</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików CSV</div>

                <div class="pozycja_edytowana">  

                    <script>           
                    function wybierz_eksport(id) {
                        for (x = 2; x < 5; x++) {
                            $('#tryb_'+x).css('display','none');                               
                        }
                        if (id != 1) {
                            $('#tryb_'+id).slideDown();      
                        }
                    } 
                    function wybierz_zakres(id) {
                        if (id == 1) {
                            $('#ImportZdjec').slideDown();
                        }                        
                        if (id == 2) {
                            $('#ImportZdjec').slideUp();
                        }
                        if (id == 3) {
                            $('#ImportZdjec').slideUp();
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
                                document.location = '/zarzadzanie/import_danych/obsluga_csv.php';
                            } else {
                                document.location = '/zarzadzanie/import_danych/obsluga_csv.php';
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

                    <span class="maleInfo">Obsługa plików CSV dotyczy formatu CSV (struktury pliku) sklepu shopGold. <b>Nie można</b> przy pomocy tego modułu zaimportować dowolnego pliku CSV - plik musi posiadać
                    odpowiednią strukturę (nagłówki) opisaną w instrukcji do sklepu. Moduł obsługi CSV służy do wymiany danych pomiędzy sklepami shopGold lub dostawcami oferującymi CSV zgodny ze strukturą sklepu.</span>

                    <div class="TabelaCsv">
                        
                        <div class="OknoImportu">

                            <form action="import_danych/obsluga_csv_import.php" method="post" class="cmxform">   

                            <div class="poleForm">
                            
                                <input type="hidden" name="akcja" value="import" />
                            
                                <div class="naglowek">Import i aktualizacja danych - produkty i kategorie</div>
                        
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
                                                                 <td><span><input type="radio" name="plik" id="' . $licznik . '" value="' . $file . '" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><label class="OpisFor" for="' . $licznik . '">' . Funkcje::PodzielNazwe($file) . '</label></span></td>
                                                                 
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
                                                                 <td><a class="TipChmurka" href="narzedzia/przegladarka_usun.php?csv=' . base64_encode((string)$file) . '"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></td>
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
                                    <input type="radio" value="," name="sep" id="przecinek" /><label class="OpisFor" for="przecinek">, (przecinek)<em class="TipIkona"><b>Pola w importowanym pliku są rozdzielone przecinkiem</b></em></label>
                                    <input type="radio" value="#" name="sep" id="plotek" /><label class="OpisFor" for="plotek"># (płotek)<em class="TipIkona"><b>Pola w importowanym pliku są rozdzielone płotkiem</b></em></label>
                                
                                </div>

                                <div class="NaglowekCsv">Zakres importu</div>
                                
                                <div class="PolaWyboru">
                                
                                    <input id="zakres1" type="radio" onclick="wybierz_zakres(1)" checked="checked" value="wszystkie" name="typ" /><label class="OpisFor" for="zakres1"><b>produkty i kategorie</b> lub <b>same produkty</b><em class="TipIkona"><b>Importowane będą kategorie oraz produkty</b></em></label><br />
                                    <input id="zakres2" type="radio" onclick="wybierz_zakres(2)" name="typ" value="kategorie" /><label class="OpisFor" for="zakres2">tylko <b>kategorie</b><em class="TipIkona"><b>Importowane będą wyłącznie kategorie</b></em></label><br />
                                    <input id="zakres3" type="radio" onclick="wybierz_zakres(3)" name="typ" value="cechy" /><label class="OpisFor" for="zakres3">tylko dane <b>cech produktów</b><em class="TipIkona"><b>Importowane będą wyłącznie dane stanów magazynowych, dostępności, czasu wysyłki oraz cen dla kombinacji cech produktów</b></em></label>
                                
                                </div>                          

                                <div id="ImportZdjec">                                
                                
                                    <div class="NaglowekCsv">Import zdjęć produktów</div>
                                    
                                    <div class="PolaWyboru">
                                    
                                        <div class="maleInfo" style="margin-left:0px">Import zdjęć produktów będzie wykonany tylko w przypadku jeżeli w pliku CSV adres zdjęcia będzie zawierał pełen adres (z domeną) oraz nie będzie to domena sklepu <?php echo ADRES_URL_SKLEPU; ?></div>
                                        
                                        <div id="import_zdjec">
                                            <input id="import_zdjec_tak" type="radio" value="tak" name="import_zdjec" /><label class="OpisFor" for="import_zdjec_tak">importuj zdjęcia produktów</label>
                                            <input id="import_zdjec_nie" type="radio" checked="checked" value="nie" name="import_zdjec" /><label class="OpisFor" for="import_zdjec_nie">nie importuj zdjęć produktów</label>
                                        </div>                                   
                                        
                                    </div>  

                                </div>
                                
                                <div class="NaglowekCsv">Rodzaj importu</div>
                                
                                <div class="PolaWyboru">
                                
                                    <div id="rodzaj_import_wszystkie">
                                        <input id="dodawanie" type="radio" checked="checked" value="dodawanie" name="rodzaj_import" /><label class="OpisFor" for="dodawanie">dodawanie danych<em class="TipIkona"><b>Dane będą tylko dodawane, nie będą aktualizowane istniejące dane</b></em></label>
                                        <input id="aktualizacja" type="radio" value="aktualizacja" name="rodzaj_import" /><label class="OpisFor" for="aktualizacja">aktualizacja danych<em class="TipIkona"><b>Dane będą tylko aktualizowane, nie będą dodawane nowe dane</b></em></label>
                                    </div>
                                
                                </div>
                                
                                <div class="przyciski_dolne" style="padding-left:0px">
                                  <input type="submit" class="przyciskNon" value="Importuj dane CSV" />
                                </div>                                    

                            </div>
                            
                            </form>
                            
                        </div>
                        
                        <div class="OknoImportu">
                        
                            <form action="import_danych/obsluga_csv_xml_export.php" method="post" class="cmxform">

                            <div class="poleForm">
                            
                                <input type="hidden" name="akcja" value="export" />
                                <input type="hidden" name="format" value="csv" />                                  
                            
                                <div class="naglowek">Eksport danych - produkty i kategorie</div>
                                
                                <div class="ListaExport">
                                
                                    <table class="InputExport">
                                        <tr><td><input type="radio" checked="checked" value="wszystkie" name="zakres" id="wszystkie" /><label class="OpisForPustyLabel" for="wszystkie"></label></td><td><span>pobierz <b>wszystkie dane</b> we <b>wszystkich językach</b> *</span></td></tr>
                                        <tr><td><input type="radio" value="pl" name="zakres" id="pl" /><label class="OpisForPustyLabel" for="pl"></label></td><td><span>pobierz <b>wszystkie dane</b> tylko w <b>języku polskim</b> *</span></td></tr>
                                        <tr><td><input type="radio" value="wszystkie_bez_kategorii" id="wszystkie_bez_kategorii" name="zakres" /><label class="OpisForPustyLabel" for="wszystkie_bez_kategorii"></label></td><td><span>pobierz <b>wszystkie dane</b> we <b>wszystkich językach</b> tylko z nazwami kategorii (bez opisów i szczegółów kategorii) *</span></td></tr>
                                        <tr><td><input type="radio" value="pl_bez_kategorii" id="pl_bez_kategorii" name="zakres" /><label class="OpisForPustyLabel" for="pl_bez_kategorii"></label></td><td><span>pobierz <b>wszystkie</b> dane tylko w <b>języku polskim</b> tylko z nazwami kategorii (bez opisów i szczegółów kategorii) *</span></td></tr>                                    
                                        <tr><td><input type="radio" value="cechy" id="cechy" name="zakres" /><label class="OpisForPustyLabel" for="cechy"></label></td><td><span>pobierz tylko <b>cechy produktów</b> - stany magazynowe, dostępności, zdjęcia i ceny produktu wg kombinacji cech w języku polskim</span></td></tr>
                                        <tr><td><input type="radio" value="cena_ilosc" id="cena_ilosc" name="zakres" /><label class="OpisForPustyLabel" for="cena_ilosc"></label></td><td><span>pobierz tylko <b>ceny, dostępność i ilość produktów</b> w języku polskim</span></td></tr>
                                        <tr><td><input type="radio" value="allegro" id="allegro" name="zakres" /><label class="OpisForPustyLabel" for="allegro"></label></td><td><span>pobierz dane o aukcjach <b>Allegro</b></span></td></tr>
                                    </table>
                                    
                                    <div class="maleInfo">* wszystkie dane z zakresu jaki został zaznaczony w menu Narzędzia / Import i eksport danych / Konfiguracja eksportu CSV i XML</div>
                                    
                                    <div class="NaglowekCsv">Dane do eksportu</div>
                                    
                                    <div class="PolaWyboru">
                                    
                                        <input type="radio" onclick="wybierz_eksport(1)" checked="checked" value="wszystkie" id="wybierz_wszystkie" name="export_dane" /><label class="OpisFor" for="wybierz_wszystkie">wszystkie produkty</label>
                                        <input type="radio" onclick="wybierz_eksport(2)" value="producent" id="wybierz_producent" name="export_dane" /><label class="OpisFor" for="wybierz_producent">tylko producenta</label>
                                        <input type="radio" onclick="wybierz_eksport(3)" value="kategoria" id="wybierz_kategoria" name="export_dane" /><label class="OpisFor" for="wybierz_kategoria">tylko z kategorii</label>
                                        <input type="radio" onclick="wybierz_eksport(4)" value="fraza" id="wybierz_fraza" name="export_dane" /><label class="OpisFor" for="wybierz_fraza">tylko z frazą</label>
                                    
                                    </div>  

                                    <div id="tryb_2" style="display:none">
                                    
                                        <div id="producent" class="WyborProducenta">
                                            <label for="producent_lista" class="BezDlugosci">Producent</label><?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci(), '', 'id="producent_lista"'); ?>
                                        </div>
                                        
                                    </div>     

                                    <div id="tryb_3" style="display:none">
                                    
                                        <div style="padding-top:10px;max-width:95%">
                                        
                                            <div id="drzewo" style="margin-left:10px;">
                                                <?php
                                                //
                                                echo '<table class="pkc">';
                                                //
                                                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                                    $podkategorie = false;
                                                    if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                                    //
                                                    echo '<tr>
                                                            <td class="lfp"><input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="kat_nr_'.$tablica_kat[$w]['id'].'" /><label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'"> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                                          </tr>
                                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                                }
                                                echo '</table>';
                                                unset($tablica_kat,$podkategorie);
                                                ?> 
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                    <div id="tryb_4" style="display:none">
                                    
                                        <div style="padding:10px 10px 0px 10px">
                                        
                                            <input type="text" value="" name="fraza_eksport" size="40" style="width:70%" />
                                            
                                            <div class="maleInfo" style="margin-left:0px">wyszukiwane będą produkty zawierające w/w frazę w nr katalogowym lub kodzie producenta</div>
                                            
                                        </div>
                                        
                                    </div>                                        
                                    
                                    <div class="NaglowekCsv">Status produktów do eksportu</div>
                                    
                                    <div class="PolaWyboru">
                                    
                                        <input type="radio" checked="checked" value="wszystkie" name="export_status" id="export_wszystkie" /><label class="OpisFor" for="export_wszystkie">wszystkie produkty</label>
                                        <input type="radio" value="aktywne" name="export_status" id="export_aktywne" /><label class="OpisFor" for="export_aktywne">tylko aktywne produkty</label>
                                        <input type="radio" value="aktywne_listing" name="export_status" id="export_aktywne_listing" /><label class="OpisFor" for="export_aktywne_listing">tylko aktywne produkty wyświetlane w listingach</label>
                                        <input type="radio" value="nieaktywne" name="export_status" id="export_nieaktywne" /><label class="OpisFor" for="export_nieaktywne">tylko nieaktywne produkty</label>
                                    
                                    </div>                                     
                                    
                                    <div class="NaglowekCsv">Nazwa pliku wynikowego</div>
                                    
                                    <div class="PolaWyboru">                                    
                                    
                                        <input type="text" value="" name="plik_wynik" size="40" style="width:70%" /> .csv <em class="TipIkona"><b>Pozostawienie pustej nazwy spowoduje wygenerowanie pliku o losowej nazwie</b></em>
                                        
                                        <div class="maleInfo" style="margin-left:0px">plik zostanie zapisany na serwerze w katalogu /export</div>
                                        
                                    </div>                                    
                                    
                                    <div class="przyciski_dolne" style="padding-left:0px">
                                      <input type="submit" class="przyciskNon" value="Eksportuj dane CSV" />
                                    </div>                                         
                                    
                                </div>
                            </div>
                            
                            </form>
                        
                            <br />
                            
                            <form action="import_danych/obsluga_csv.php" method="post" class="cmxform" id="plikForm" enctype="multipart/form-data"> 
                        
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