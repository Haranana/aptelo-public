<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from languages order by sort_order";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    
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
                                      array('Ikona','center'),
                                      array('Domyślny','center'),
                                      array('Domyślna waluta','center'),
                                      array('Sort','center'),
                                      array('Status','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['languages_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica = array(array($info['languages_id'],'center'),
                                   array($info['name'],'center'),
                                   array($info['code'],'center'));  

                  $tgm = '<img src="' . '/' . KATALOG_ZDJEC . '/' . $info['image'] . '" alt="' . $info['name'] . '" />';
                  $tablica[] = array($tgm,'center');            

                  // domyslna
                  if ($info['languages_default'] == '1') { $obraz = '<em class="TipChmurka"><b>Ten język jest domyślny</b><img src="obrazki/aktywny_on.png" alt="Ten język jest domyślny" /></em>'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');    
                  
                  // jaka waluta
                  $waluta = $db->open_query("select * from currencies where currencies_id = '".(int)$info['currencies_default']."'");
                  $nazwa_waluty = $waluta->fetch_assoc();
                  $tablica[] = array($nazwa_waluty['title'],'center'); 
                  $db->close_query($waluta);                              

                  $tablica[] = array($info['sort_order'],'center'); 
                  
                  // status
                  if ($info['status'] == '1') { $obraz = '<em class="TipChmurka"><b>Ten język jest aktywny</b><img src="obrazki/aktywny_on.png" alt="Ten język jest aktywny" /></em>'; } else { $obraz = '<em class="TipChmurka"><b>Ten język jest nieaktywny</b><img src="obrazki/aktywny_off.png" alt="Ten język jest nieaktywny" /></em>'; }              
                  $tablica[] = array($obraz,'center');                    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['languages_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/jezyki_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  if ( $info['languages_id'] != '1' ) {
                    $tekst .= '<a class="TipChmurka" href="slowniki/jezyki_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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
            
            <div id="naglowek_cont">Języki</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/jezyki_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('slowniki/jezyki.php', $zapytanie, $ile_licznika, $ile_pozycji, 'languages_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
