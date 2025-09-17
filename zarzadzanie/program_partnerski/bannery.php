<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT * FROM pp_banners";
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

            $zapytanie .= " order by id limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Opis banneru', 'center'),
                                      array('Obrazek', 'center', '', 'class="ListingSchowaj"'),
                                      array('Rozdzielczość', 'center', '', 'class="ListingSchowaj"'),
                                      array('Opis obrazka', 'center'),
                                      array('Kod html', 'center', 'width:40%'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id'].'">';
                  }      

                  $tablica = array();
                  
                  $tablica[] = array($info['id'],'center');
                  
                  $tablica[] = array($info['pp_description'],'center');

                  if (!empty($info['pp_image'])) {
                    $tgm = Funkcje::pokazObrazek($info['pp_image'], $info['pp_image_alt'], '200', '200', '', false, '');
                   } else { 
                    $tgm = '-';
                  }
                  $tablica[] = array($tgm, 'center', '', 'class="ListingSchowaj"');  
                  
                  $tgm = '-';
                  if (!empty($info['pp_image'])) {
                    // wielkosc pliku
                    $Kb = filesize('../'.$info['pp_image']);
                    
                    // ustalenie czy plik jest obrazkiem
                    //
                    $Rodzielczosc = '-';
                    if ( $Kb > 0 ) {
                        //
                        
                        // czy plik jest obrazkiem
                        if (getimagesize('../'.$info['pp_image']) != false) {
                            //
                            list($szerokosc, $wysokosc) = getimagesize('../'.$info['pp_image']);
                            $tgm = $szerokosc . ' x ' . $wysokosc;
                            //
                        }
                            
                        
                    }                                            
                    // 
                   } else { 
                    $tgm = '-';
                  }
                  $tablica[] = array($tgm,'center', '', 'class="ListingSchowaj"');  
                  unset($szerokosc, $wysokosc, $Kb);

                  $tablica[] = array($info['pp_image_alt'],'center');

                  $Litery = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 's');
                  $Cyfry = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ',');                  

                  $kodHtml = htmlspecialchars('<a href="'.ADRES_URL_SKLEPU.'/pp-sklep-{CIAG_Z_ID_KLIENTA}.html"><img src="'.ADRES_URL_SKLEPU.'/'.$info['pp_image'].'" alt="'.$info['pp_image_alt'].'" /></a>');
                  
                  $tablica[] = array($kodHtml,'center','font-family:Courier;line-height:1.8;');                 

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="program_partnerski/bannery_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="program_partnerski/bannery_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Bannery programu partnerskiego</div>

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="program_partnerski/bannery_dodaj.php">dodaj nowy banner</a>
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
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('program_partnerski/bannery.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
