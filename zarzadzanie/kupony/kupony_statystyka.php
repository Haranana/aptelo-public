<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( !isset($_GET['xxxs']) && !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    } else if ( isset($_GET['xxxs']) ) {
         $_GET['id_poz'] = $_GET['xxxs'];
    }
    if ( !isset($_GET['kod_kuponu']) ) {
         $_GET['kod_kuponu'] = 'brak kodu';
         $_GET['id_poz'] = 0;
    }     

    $zapytanie = "SELECT * FROM coupons_to_orders WHERE coupons_id = '" . (int)$_GET['id_poz'] . "' ORDER BY orders_id";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / 500);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        // informacje o produktach - zakres
        $zapytanie .= " limit ".$_GET['parametr'];
        $sql = $db->open_query($zapytanie);

        $listing_danych = new Listing();
        
        $tablica_naglowek = array(array('Nr zamówienia','center'),
                                  array('Data zamówienia','center'),
                                  array('Wartość zamówienia','center'),
                                  array('Wartość kuponu','center'),
                                  array('Klient','center'),
                                  array('Status zamówienia','center'));

        echo $listing_danych->naglowek($tablica_naglowek);
        
        $tekst = '';
        while ($info = $sql->fetch_assoc()) {

            $rabat = '';
            $zamowienie = new Zamowienie($info['orders_id']);

            if ( Funkcje::multiInArray('ot_discount_coupon', $zamowienie->podsumowanie) ) {
                foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
                    if ( $podsumowanie['klasa'] == 'ot_discount_coupon' ) {
                        $rabat = $podsumowanie['tekst'];
                    }
                }
            }

            if ( isset($zamowienie->info) && !empty($zamowienie->info) ) {

            $tekst .= '<tr class="pozycja_off">';

                $tablica = array(array($info['orders_id'],'center'),
                                 array($zamowienie->info['data_zamowienia'],'center'),
                                 array($zamowienie->info['wartosc_zamowienia'],'center'),
                                 array($rabat,'center'),
                                 array(((!empty($zamowienie->klient['firma'])) ? $zamowienie->klient['firma'] . ', ' : '') . 
                                               $zamowienie->klient['nazwa'] . '<br />'.
                                               $zamowienie->klient['ulica'] . '<br />'.
                                               $zamowienie->klient['kod_pocztowy'] . ' '. $zamowienie->klient['miasto'],'left'),
                                 array(Sprzedaz::pokazNazweStatusuZamowienia($zamowienie->info['status_zamowienia'], $_SESSION['domyslny_jezyk']['id']),'center'));

                $tekst .= $listing_danych->pozycje($tablica);
                      
                $tekst .= '<td class="rg_right">';
                $tekst .= '</td></tr>';

            }
        }

        $tekst .= '</table>';
        //
        echo $tekst;
        //
        $db->close_query($sql);
        unset($listing_danych,$tekst,$tablica,$tablica_naglowek);
            
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <?php
        if ( $ile_pozycji > 0 ) {
        ?>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Statystyka kuponu: <?php echo $_GET['kod_kuponu']; ?></div>     

            <div style="clear:both;"></div>                  

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <div style="clear:both;"></div>
            
            <a href="kupony/kupony_statystyka_export.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>" id="LinkCsv">pobierz dane statystyki w formacie csv <span>(separator pola: średnik)</span></a>
            
            <br />

            <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo '?id_poz='.$_GET['id_poz']; ?>','kupony');">Powrót</button> 

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('kupony/kupony_statystyka.php', $zapytanie, $ile_licznika, $ile_pozycji, 'coupons_id', '500'); ?>
            </script>              

        </div>
        
        <?php } else { ?>
        
        <div id="naglowek_cont">Statystyka kuponu: <?php echo $_GET['kod_kuponu']; ?></div>    
        
        <div class="poleForm">

            <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
        
        </div>
        
        <div class="przyciski_dolne">
          <button type="button" style="margin-left:0px" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
        </div>          
        
        <?php } ?>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
