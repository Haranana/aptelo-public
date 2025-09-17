<?php
// {{format StarCode}}
// tylko dane do pobierania
if ( isset($_GET['kategorie']) ) {
    //
    $dane_produktow_kategorie = simplexml_load_file("../../../import/" . $_GET['plik']);
    $tablica_kategorii = array();
    //
    $dane_produktow = $dane_produktow_kategorie->product;  
    //
    for ($x = 0; $x < sizeof($dane_produktow); $x++ ) {
        //
        $produkt = $dane_produktow[$x];
        //
        if ( isset($produkt->category) && !in_array(trim((string)$produkt->category), $tablica_kategorii) ) {
             //
             if ( !empty(trim((string)$produkt->category)) ) {
                  $tablica_kategorii[] = trim((string)$produkt->category);
             }
             //
        }
        //
        unset($produkt);
        //
    }
    //
    if ( sizeof($dane_produktow) > 0 ) {
         //
         sort($tablica_kategorii);
         //
         for ($x = 0; $x < sizeof($tablica_kategorii); $x++ ) {
             //
             echo '<p id="k' . ($x + 1) . '">
                     <label for="kategoria_' . ($x + 1) . '">Nazwa/ścieżka kategorii:</label>
                     <input type="text" class="PoleKategorii" name="kategoria_' . ($x + 1) . '" id="kategoria_' . ($x + 1) . '" value="' . $tablica_kategorii[$x] . '" size="45" /> &nbsp;
                     <span id="ks' . ($x + 1) . '" class="KategorieSklepu"><select data-nr="' . ($x + 1) . '" style="color:#999" name="kategoria_sklepu_' . ($x + 1) . '"><option>... przypisz kategorię sklepu ...</option></select></span> &nbsp;
                     &nbsp; Marża:
                     <input type="text" name="marza_' . ($x + 1) . '" value="" size="5" /> %  
                     &nbsp; <span class="usun TipChmurka" onclick="usun_pozycje(' . ($x + 1) . ')" style="cursor:pointer"><b>Skasuj</b>&nbsp;</span> 
                  </p>';
             //            
         }
         //
         echo '<input type="hidden" name="ile_input_plik" id="ile_input_plik" value="' . sizeof($tablica_kategorii) . '" />';
         //
    } else {
         //
         echo 'blad';
         //
    }
    //
    unset($dane_produktow_kategorie);
    //
    exit;
}

if ( isset($_GET['tylko_rekordy']) ) {
    ?>
    <div id="dodXML">
    
        <span>Przy dodawaniu z pliku starcode zostaną pobrane i dodane do sklepu:</span>
        
        <ul>
            <li><input style="margin-left:0px" type="checkbox" name="dod_zakres_kategoria" id="opcja_1" value="1" checked="checked" /> <label class="OpisFor" for="opcja_1">kategoria</label></li> 
            <li><input type="checkbox" name="dod_zakres_nazwa_produktu" id="opcja_2" value="1" checked="checked" disabled="disabled" /> <label class="OpisFor" for="opcja_2">nazwa produktu</label></li>  
            <li><input type="checkbox" name="dod_zakres_nr_kat" id="opcja_3" value="1" checked="checked" disabled="disabled" /> <label class="OpisFor" for="opcja_3">nr katalogowy</label> </li>  
            <li><input type="checkbox" name="dod_zakres_ilosc" id="opcja_5" value="1" checked="checked" /> <label class="OpisFor" for="opcja_5">stan magazynowy</label></li>  
            <li><input type="checkbox" name="dod_zakres_cena" id="opcja_6" value="1" checked="checked" disabled="disabled" /> <label class="OpisFor" for="opcja_6">cena brutto</label></li>  
            <li><input type="checkbox" name="dod_zakres_dostepnosc" id="opcja_7" value="1" checked="checked" /> <label class="OpisFor" for="opcja_7">dostępność produktu</label></li>  
            <li><input type="checkbox" name="dod_zakres_waga" id="opcja_8" value="1" checked="checked" /> <label class="OpisFor" for="opcja_8">waga</label></li>  
            <li><input type="checkbox" name="dod_zakres_opis" id="opcja_9" value="1" checked="checked" /> <label class="OpisFor" for="opcja_9">opis</label></li>  
            <li><input type="checkbox" name="dod_zakres_producent" id="opcja_10" value="1" checked="checked" /> <label class="OpisFor" for="opcja_10">producent</label></li>  
            <li><input type="checkbox" name="dod_zakres_zdjecie" id="opcja_11" value="1" checked="checked" /> <label class="OpisFor" for="opcja_11">zdjęcie</label></li>  
            <li><input type="checkbox" name="dod_zakres_parametry" id="opcja_12" value="1" checked="checked" /> <label class="OpisFor" for="opcja_12">dodatkowe parametry</label></li>
        </ul>        
        
    </div>
    
    <div id="aktXML" style="display:none">
    
        <span>Przy aktualizacji z pliku starcode zostaną pobrane i zaktualizowane:</span>

        <ul>
            <li><input style="margin-left:0px" type="checkbox" id="opcja_20" name="akt_zakres_nazwa_produktu" value="1" /> <label class="OpisFor" for="opcja_20">nazwa produktu</label></li>
            <li><input type="checkbox" name="akt_zakres_ilosc" id="opcja_22" value="1" checked="checked" /> <label class="OpisFor" for="opcja_22">stan magazynowy</label></li>
            <li><input type="checkbox" name="akt_zakres_cena" id="opcja_23" value="1" checked="checked" /> <label class="OpisFor" for="opcja_23">cena brutto</label></li>
            <li><input type="checkbox" name="akt_zakres_dostepnosc" id="opcja_24" value="1" checked="checked" /> <label class="OpisFor" for="opcja_24">dostępność produktu</label></li>
            <li><input type="checkbox" name="akt_zakres_waga" id="opcja_25" value="1" /> <label class="OpisFor" for="opcja_25">waga</label></li>
            <li><input type="checkbox" name="akt_zakres_opis" id="opcja_26" value="1" /> <label class="OpisFor" for="opcja_26">opis</label></li>
        </ul>     
        
    </div>
    <?php
    exit;
}

// stworzenie tablicy z definicjami i struktura importu
$TablicaDane = array();

$produkt = $dane_produktow->product[(int)$_POST['limit']];

if (isset($produkt)) {

    $Importuj = false;
    
    if ( $CzyWszystkieKategorie == true ) {
         $Importuj = true;
    }
    
    // sprawdza czy jest nazwa produktu
    if (isset($produkt->name) && $produkt->name != '') {

        // kategorie
        if (isset($produkt->category)) {
            //
            // bedzie szukal po tablicy kategorii czy dana kategoria z starcode jest w tablicy szablonu
            // ustali marze dla ceny jezeli jest
            $IdKategoriiSklepu = 0;
            if (isset($TablicaKategoriiXml) && count($TablicaKategoriiXml) > 0) {
                //
                for ($d = 0, $c = count($TablicaKategoriiXml); $d < $c; $d++) {
                    if (trim((string)strtoupper((string)$TablicaKategoriiXml[$d][0])) == trim(strtoupper((string)$produkt->category))) {
                        $Importuj = true;
                        $_POST['marza'] = (float)$TablicaKategoriiXml[$d][1];
                        //
                        // id kategorii sklepu do przypisania do produktu
                        if ( isset($TablicaKategoriiXml[$d][2]) && (int)$TablicaKategoriiXml[$d][2] > 0 ) {
                             $IdKategoriiSklepu = (int)$TablicaKategoriiXml[$d][2];
                        }                        
                    }
                }
                //
            }
            
            // tylko jezeli jest dodawanie
            if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_kategoria'])) {
                //
                if ($Importuj == true) {
                    //
                    if ( $IdKategoriiSklepu > 0 ) {
                         //
                         $TablicaDane['Kategorie_id'] = $IdKategoriiSklepu;
                         //
                    } else {
                         //
                         $ZawartoscKat = explode('/', (string)$produkt->category);
                         for ($p = 0, $cp = count($ZawartoscKat); $p < $cp; $p++) {
                             //
                             $TablicaDane['Kategoria_'.($p + 1).'_nazwa'] = $ZawartoscKat[$p];                
                             //
                         }
                         //
                         unset($ZawartoscKat);
                         //
                    }                  
                    //
                }
                //
            }
            
        } 

        if ($Importuj == true) {

            // cena brutto
            if ( isset($produkt->price) && isset($_POST['zakres_cena']) ) {
                 $TablicaDane['Cena_brutto'] = $produkt->price;
            }
            
            // podatek vat
            if ( $_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_cena']) ) {
                 $podziel_vat = explode(',', $_POST['vat']);
                 if ( isset($podziel_vat[1]) ) {
                      $TablicaDane['Podatek_Vat'] = $podziel_vat[1];            
                 }
                 unset($podziel_vat);
            }
            
            // waga produktu
            if ( isset($produkt->weight) && isset($_POST['zakres_waga']) ) {
                $TablicaDane['Waga'] = $produkt->weight;
            }            

            // dostepnosc produktu
            if ( isset($produkt->availability) && isset($_POST['zakres_dostepnosc']) ) {
                $TablicaDane['Dostepnosc'] = $produkt->availability;
            }

            // nazwa produktu
            if ( isset($produkt->name) ) {
                 //
                 $TablicaDane['Nazwa_produktu_struktura'] = $produkt->name; 
                 if ( isset($_POST['zakres_nazwa_produktu']) ) {
                    $TablicaDane['Nazwa_produktu'] = $produkt->name; 
                 }
            } else {
                 $TablicaDane['Nazwa_produktu_struktura'] = 'Brak nazwy';
                 $TablicaDane['Nazwa_produktu'] = 'Brak nazwy';
            }                

            // opis produktu
            if ( isset($produkt->description) && isset($_POST['zakres_opis']) ) {
                $TablicaDane['Opis'] = $produkt->description;
            }
            
            // producent
            if ( isset($produkt->producer) && isset($_POST['zakres_producent']) ) {
                $TablicaDane['Producent'] = $produkt->producer;
            }
            
            // ilosc produktow
            if ( isset($produkt->quantity) && isset($_POST['zakres_ilosc']) ) {
                $TablicaDane['Ilosc_produktow'] = $produkt->quantity;    
            }            

            if ( isset($produkt->parms->parm_name) ) {
                //
                // atrybuty z pliku
                $LicznikPola = 1;
                //            
                for ($s = 0, $cs = count($produkt->parms->parm_name); $s < $cs; $s++) {
                    //
                    $starcode_klucz = $produkt->parms->parm_name[$s];
                    $starcode_wartosc = $produkt->parms->parm_value[$s];
                    //
                    if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_parametry']) && $starcode_klucz != '' && $starcode_wartosc  != '') {
                        $TablicaDane['Dodatkowe_pole_' . $LicznikPola . '_nazwa'] = $starcode_klucz;
                        $TablicaDane['Dodatkowe_pole_' . $LicznikPola . '_wartosc'] = $starcode_wartosc;
                        $LicznikPola++;
                    }     
                    //
                    unset($starcode_klucz, $starcode_wartosc);
                }
                unset($LicznikPola);
            }
            
            if ( isset($produkt->code) ) {
                 $TablicaDane['Nr_katalogowy'] = $produkt->code;
            }

            // jezeli nr katalogowy jest pusty to utworzy numer katalogowy na podstawie id
            if (trim((string)$TablicaDane['Nr_katalogowy']) == '') {
                if (isset($produkt->id) && $produkt->id != '') {
                    $TablicaDane['Nr_katalogowy'] = $produkt->id;
                  } else {
                    // tylko jezeli jest dodawanie
                    if ($_POST['rodzaj_import'] == 'dodawanie') {
                        //                  
                        $TablicaDane['Nr_katalogowy'] = rand(21212,99999999);
                        //
                    }
                }
            }

            $photoUrls = array();

            if ( isset($produkt->image) && $_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_zdjecie']) ) {
            
                // zapisywanie zdjecia na serwerze
                $produkt->image = str_replace('https', 'http', (string)$produkt->image);
                $url = $produkt->image;                
                
                if ( $url != '' ) {

                    $SciezkaPliku = PobieranieCurl::SciezkaPliku($url);

                    if ( $SciezkaPliku != '' ) {

                        $photoUrls[] = $url;

                        $TablicaDane['Zdjecie_glowne'] = $SciezkaPliku;

                    }
                    
                } else {
                    
                    $TablicaDane['Zdjecie_glowne'] = '';
                    
                }

                unset($url);
            }

            if ( isset($photoUrls) && count((array)$photoUrls) > 0 ) {
                PobieranieCurl::ZapiszObrazyMulti($photoUrls);
            }

            unset($photoUrls);

            // czyszczenie tablicy trim
            foreach ($TablicaDane as $Klucz => $Wartosc) {
                $TablicaDane[$Klucz] = trim((string)preg_replace('/[\r\n]+/', '', (string)$Wartosc));
            }            
            
            // status
            $TablicaDane['Status'] = 'tak';
            
        }
    }
}
?>