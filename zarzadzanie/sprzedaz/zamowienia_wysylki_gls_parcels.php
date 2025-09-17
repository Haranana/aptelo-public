<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_protocol = '".$_GET['protokol_numer']."'";    

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
            
            $tablica_naglowek = array(array('Numer zamowienia', 'center'),
                                      array('Numer dokumentu', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Ilość paczek', 'center'),
                                      array('Waga paczek', 'center'),
                                      array('Status', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();

                  $tekst .= '<tr class="pozycja_off">';

                  if ( $info['orders_shipping_status'] == '1' ) {
                        $status = 'W przygotowalni';
                  }
                  if ( $info['orders_shipping_status'] == '2' ) {
                        $status = 'Potwierdzona';
                  }

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array(date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array($info['orders_parcels_quantity'],'center');
                  $tablica[] = array($info['orders_shipping_weight'],'center');
                  $tablica[] = array($status,'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['gls_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);

                  
                  $tekst .= '<td></td></tr>';
                  unset($info_tmp);        
                 
                  
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
        
            <div id="naglowek_cont">Paczki zawarte w protokole GLS - <?php echo $_GET['protokol_numer']; ?></div>

            <div id="ajax"></div>

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>

            <div id="page"></div>

            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <?php
            if ( isset($_GET['protokol_id']) && (int)$_GET['protokol_id'] > 0 ) {
            ?>
            <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_wysylki_gls_protocol','<?php echo '?id_poz='.(int)$_GET['protokol_id']; ?>','sprzedaz');">Powrót</button>   
            <?php
            }
            ?>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_gls_parcels.php', $zapytanie, $ile_licznika, $ile_pozycji, 'gls_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
