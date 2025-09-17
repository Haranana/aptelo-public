<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $ilosc_wynikow = '100';

    $zapytanie = "SELECT * FROM comparisons";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / $ilosc_wynikow);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            // informacje o produktach - zakres
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array(''),
                                      array('Nazwa'),
                                      array('Typ eksportu','center'),
                                      array('Data eksportu','center'),
                                      array('Ilość wyeksportowanych produktów','center'),                                      
                                      array('Narzut / rabat','center'),
                                      array('Cena nr','center'),
                                      array('Aktywne dla Cron','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                
                  $wycofana = false;
                  if ( $info['comparisons_name'] == 'Cenuj.pl' ) {
                       $wycofana = true;
                  }
                  if ( $info['comparisons_name'] == 'Oferciak.pl' ) {
                       $wycofana = true;
                  }                  
                  if ( $info['comparisons_name'] == 'ToTu.pl' ) {
                       $wycofana = true;
                  }   
                  if ( $info['comparisons_name'] == 'Alejahandlowa.pl' ) {
                       $wycofana = true;
                  } 
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['comparisons_id']) {
                     $tekst .= '<tr class="pozycja_on' . (($wycofana == true) ? ' WycofanaKratka' : '') . '" id="sk_'.$info['comparisons_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off' . (($wycofana == true) ? ' WycofanaKratka' : '') . '" id="sk_'.$info['comparisons_id'].'">';
                  }    

                  if ( $info['comparisons_export_type'] == '1' ) {
                    $tryb_eksportu = 'tylko zaznaczone produkty';
                  } elseif (  $info['comparisons_export_type'] == '2' ) {
                    $tryb_eksportu = 'tylko zaznaczone kategorie';
                  } elseif (  $info['comparisons_export_type'] == '3' ) {
                    $tryb_eksportu = 'tylko wybrani producenci';                    
                  } else {
                    $tryb_eksportu = 'wszystkie produkty';
                  }
                  
                  // podzial dla duplikowanych
                  $podzial = explode('__', (string)$info['comparisons_plugin']);
                  $info['comparisons_plugin'] = $podzial[0];
                  
                  $tablica = array(array($info['comparisons_id'] . (($wycofana == true) ? '<br /><em class="WycofanaIkona TipChmurka"><b>Porównywarka zawiesiła działalność</b><img src="obrazki/uwaga.png" alt="Wycofana" /></em>' : ''),'center'),
                                   array((file_exists('obrazki/porownywarki/'.$info['comparisons_plugin'].'.png') ? '<img src="obrazki/porownywarki/'.$info['comparisons_plugin'].'.png" alt="" />' : ''),'center', 'padding:0px;'),
                                   array($info['comparisons_name'] . (($info['comparisons_description'] != '') ? '<div style="font-size:90%;margin-top:10px;opacity:0.8">' . $info['comparisons_description'] . '</div>' : '')),
                                   array($tryb_eksportu,'center'),
                                   array( ($info['comparisons_last_export'] != '' && $info['comparisons_last_export'] != '0000-00-00 00:00:00' ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['comparisons_last_export'])) : '---' ),'center'),
                                   array( ($info['comparisons_products_exported'] != '0' ? $info['comparisons_products_exported'] : '---'),'center'),
                                   array( ($info['comparisions_discount'] != 0 ? $info['comparisions_discount'] . ' %' : '-'),'center'),
                                   array( 'nr ' . $info['comparisons_price_level'],'center'));
                           
                  // czy aktywne dla cron
                  if ($info['comparisions_cron'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Możliwość generowania przez zewnętrzny skrypt'; } else { $obraz = 'aktywny_off.png'; $alt = 'Brak możliwości generowania przez zewnętrzny skrypt'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" />','center');                                     
                                   
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['comparisons_id'];
                  
                  $tekst .= '<a class="TipChmurka" href="porownywarki/porownywarki_eksport.php'.$zmienne_do_przekazania.'"><b>Wykonanie eksportu do porównywarki</b><img src="obrazki/xml_maly.png" alt="Wykonanie eksportu do porównywarki" /></a>';
                  $tekst .= '<a class="TipChmurka" href="porownywarki/porownywarki_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="porownywarki/porownywarki_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj porównywarkę</b><img src="obrazki/duplikuj.png" alt="Duplikuj porównywarkę" /></a>';                   
                  
                  if ( (int)$info['comparisons_duplicated'] == 1 ) {
                       $tekst .= '<a class="TipChmurka" href="porownywarki/porownywarki_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  }
                  
                  $nazwa_pliku = $info['comparisons_plugin'];
                  if ( !empty($info['comparisons_file_export']) ) {
                       $nazwa_pliku = $info['comparisons_file_export'];
                  }
                  
                  if ( file_exists('../xml/'.$nazwa_pliku.'.xml') ) {
                    $tekst .= '<a class="TipChmurka" href="../xml/'.$nazwa_pliku.'.xml" target="_blank"><b>Przejrzyj plik</b><img src="obrazki/zobacz.png" alt="Przejrzyj plik" /></a>';
                  } else if ( file_exists('../xml/'.$nazwa_pliku.'.csv') ) {
                    $tekst .= '<a class="TipChmurka" href="../xml/'.$nazwa_pliku.'.csv" target="_blank"><b>Przejrzyj plik</b><img src="obrazki/zobacz.png" alt="Przejrzyj plik" /></a>';
                  } else {
                    $tekst .= '';
                  }

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
            
            <div id="naglowek_cont">Porównywarki produktów</div>     

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('porownywarki/porownywarki.php', $zapytanie, $ile_licznika, $ile_pozycji, 'comparisons_id', $ilosc_wynikow); ?>
            </script>                

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
