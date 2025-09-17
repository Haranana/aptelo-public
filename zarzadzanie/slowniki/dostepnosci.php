<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT * FROM products_availability p, products_availability_description pd WHERE p.products_availability_id = pd.products_availability_id and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ORDER BY p.mode desc, p.quantity, pd.products_availability_name";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa'),
                                      array('Tryb działania','center'),
                                      array('Od jakiej ilości produktów dostępność jest widoczna','center'),
                                      array('Czy można kupować ?','center'),
                                      array('Obrazek','center'),
                                      array('Ceneo','center','','class="ListingSchowajMobile"'),
                                      array('Nokaut','center','','class="ListingSchowajMobile"'),
                                      array('Okazje','center','','class="ListingSchowajMobile"'),
                                      array('SmartBay','center','','class="ListingSchowajMobile"'),
                                      array('Google / Facebook','center','','class="ListingSchowajMobile"'),
                                      array('Domodi','center','','class="ListingSchowajMobile"'),
                                      array('Skąpiec','center','','class="ListingSchowajMobile"'),
                                      array('Favi','center','','class="ListingSchowajMobile"')
            );
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_availability_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_availability_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_availability_id'].'">';
                  }       

                  $tablica = array(array($info['products_availability_id'],'center'),
                                   array($info['products_availability_name']),
                                   array((($info['mode'] == '1') ? '<span style="color:#ff0000">automatyczny</span>' : 'ręczny'),'center'),
                                   array((($info['mode'] == '1') ? $info['quantity'] : '-'),'center'),
                                   array((($info['shipping_mode'] == '1') ? '<em class="TipChmurka"><b>Tak</b><img src="obrazki/aktywny_on.png" alt="Tak" /></em?' : '-'),'center'));
                                   
                  $tgm = Funkcje::pokazObrazek($info['image'], $info['products_availability_name'], '40', '40');

                  $tablica[] = array($tgm,'center');     

                  $tablica[] = array($info['ceneo'],'center','','class="ListingSchowajMobile"');
                  $tablica[] = array($info['nokaut'],'center','','class="ListingSchowajMobile"');
                  $tablica[] = array($info['okazje'],'center','','class="ListingSchowajMobile"');         
                  $tablica[] = array($info['smartbay'],'center','','class="ListingSchowajMobile"');   
                  $tablica[] = array($info['googleshopping'],'center','','class="ListingSchowajMobile"');   
                  $tablica[] = array($info['domodi'],'center','','class="ListingSchowajMobile"'); 
                  $tablica[] = array($info['skapiec'],'center','','class="ListingSchowajMobile"'); 
                  $tablica[] = array($info['favi'],'center','','class="ListingSchowajMobile"'); 

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['products_availability_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/dostepnosci_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="slowniki/dostepnosci_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);        

        }
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Dostępności produktów</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/dostepnosci_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <div style="margin-top:10px" class="ListingSchowajMobile">
            
                <div class="OknoPorownywarek">
                
                    <div>
                        <span>Dostępności NOKAUT:</span>
                        0 - dostępny od ręki<br />
                        1 - dostępny do tygodnia<br />
                        2 - dostępny powyżej tygodnia<br />
                        3 - dostępny na życzenie<br />
                        4 - Sprawdź w sklepie
                    </div>
                    <div>
                        <span>Dostępności CENEO:</span>
                        1 - dostępny<br />
                        3 - dostępny do 3 dni<br />
                        7 - dostępny do tygodnia<br />
                        14 - dostępny do 14 dni<br />
                        90 - na zamówienie<br />
                        99 - sprawdź w sklepie<br />
                        110 - w przedsprzedaży
                    </div>
                    <div>
                        <span>Dostępności Domodi:</span>
                        1 - produkt dostępny<br />
                        99 - niedostępny
                    </div>
                    <div>
                        <span>Dostępności OKAZJE.info:</span>
                        1 - produkt dostępny<br />
                        3 - produkt dostępny do 3 dni<br />
                        7 - produkt dostępny do 7 dni<br />
                        14 - dostępny nie wcześniej niż za tydzień<br />
                        0 - dostępność sprawdź w sklepie
                    </div>                    
                </div>
                <div class="OknoPorownywarek">

                    <div>
                        <span>Dostępności SMARTBAY:</span>
                        0 - dostępny od ręki<br />
                        5 - dostępny do 3 dni<br />
                        1 - dostępny do tygodnia<br />
                        2 - dostępny powyżej tygodnia<br />
                        3 - dostępny na życzenie<br />
                        4 - Sprawdź w sklepie
                    </div>
                    <div>
                        <span>Dostępności Google oraz Facebook reklamy:</span>
                        1 - w magazynie<br />
                        3 - niedostępny<br />
                        4 - zamówienie przedpremierowe
                    </div>
                    <div>
                        <span>Dostępności Skąpiec:</span>
                        1 - wysyłka w 1 godzinę<br />
                        24 - wysyłka następnego dnia,<br />
                        72 – wysyłka do 3 dni<br />   
                        168 – wysyłka do tygodnia<br />   
                        999 – wysyłka powyżej tygodnia<br />   
                        0 – sprawdź dostępnośćw sklepie
                    </div>
                    <div>
                        <span>Dostępności Favi.pl:</span>
                        1 - dostępny<br />
                        3 - dostępny do 3 dni<br />
                        7 - dostępny do tygodnia<br />
                        14 - dostępny do 14 dni<br />
                        90 - na zamówienie<br />
                        99 - sprawdź w sklepie<br />
                        110 - w przedsprzedaży
                    </div>
                    
                </div>
                
                <div class="cl"></div>
                
            </div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('slowniki/dostepnosci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_availability_id'); ?>
            </script>             
 
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
