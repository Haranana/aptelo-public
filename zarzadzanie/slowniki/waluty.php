<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from currencies";
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
                                      array('Nazwa','center'),
                                      array('Kod','center'),
                                      array('Symbol','center'),
                                      array('Seperator dziesiętny','center'),
                                      array('Przelicznik','center'),
                                      array('Kurs w sklepie<br />z marżą','center'),
                                      array('Prowizja %','center'),
                                      array('Domyślna<br />(na podstawie<br />domyślnego języka)','center'),
                                      array('Ostatnia aktualizacja','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['currencies_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica = array(array($info['currencies_id'],'center'),
                                   array($info['title'],'center'),
                                   array($info['code'],'center'),
                                   array($info['symbol'],'center'),
                                   array($info['decimal_point'],'center'),
                                   array($info['value'],'center'));
                                   
                  if ( !empty($info['currencies_marza']) ) {                    
                       $tgm = '1 ' . $info['symbol'] . ' = ' . $waluty->FormatujCene(1 / $info['value'], true, '', $info['code'] , 4) . '<br /><small>(' . $info['symbol'] . ' na domyślną walutę)</small><br /><br />';
                       $tgm .= '1 ' . $info['symbol'] . ' = ' . $waluty->FormatujCene((1 / $info['value']) * ((1 / $info['value']) / ((1 / $info['value']) * ((100 + $info['currencies_marza'])/100))), false, '', $info['code'] , 4) . '<br /><small>(domyślna waluta na ' . $info['symbol'] . ')</small>';
                    } else {
                       $tgm = '1 ' . $info['symbol'] . ' = ' . $waluty->FormatujCene(1 / $info['value'], true, '', $info['code'] , 4);
                  }
                  
                  $tablica[] = array($tgm,'center'); 
                  unset($tgm);
                  
                  $tablica[] = array(((empty($info['currencies_marza'])) ? '-' : $info['currencies_marza'] . '%'),'center');
                                   
                  // jaka waluta
                  if ((int)$info['currencies_id'] == $_SESSION['domyslna_waluta']['id']) {
                      $obraz = '<em class="TipChmurka"><b>Ta waluta jest domyślna</b><img src="obrazki/aktywny_on.png" alt="Ta waluta jest domyślna" /></em>';
                      $tablica[] = array($obraz,'center'); 
                    } else {
                      $tablica[] = array('-','center'); 
                  }                                   
                                   
                  $tablica[] = array($info['last_updated'],'center');  

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['currencies_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/waluty_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  if ( $info['currencies_id'] != '1' ) {
                    $tekst .= '<a class="TipChmurka" href="slowniki/waluty_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  } else {
                    $tekst .= '<em class="TipChmurka"><b>Nie można usunąć tej pozycji</b><img src="obrazki/kasuj_off.png" alt="Nie można usunąć tej pozycji" /></em>';
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
            
            <div id="naglowek_cont">Waluty</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/waluty_dodaj.php">dodaj nową pozycję</a>
                </div>  
            </div>
            
            <div style="clear:both;"></div>                  

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <div style="clear:both;"></div> 
            
            <div style="text-align:right">
                <a class="Nbp" href="slowniki/waluty_aktualizacja.php?wroc=tak">zaktualizuj kursy w oparciu o dane NBP</a>
            </div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('slowniki/waluty.php', $zapytanie, $ile_licznika, $ile_pozycji, 'currencies_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
