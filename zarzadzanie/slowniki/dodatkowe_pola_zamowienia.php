<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT oe.fields_id, oe.fields_order, oe.fields_input_type, oe.fields_required_status, oei.fields_name, oe.fields_status, oe.fields_input_type, oe.fields_type FROM orders_extra_fields oe, orders_extra_fields_info oei WHERE oei.fields_id = oe.fields_id AND oei.languages_id ='".(int)$_SESSION['domyslny_jezyk']['id']."' ORDER BY oe.fields_order";

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
                                      array('Nazwa pola'),
                                      array('Typ pola','center'),
                                      array('Sort','center'), 
                                      array('Wymagane','center'),
                                      array('Status','center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['fields_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['fields_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['fields_id'].'">';
                  }       

                  //typ pola
                  switch( $info['fields_input_type'] ) {
                      case  0: $typ_pola = 'Input' . (($info['fields_type'] == 'kalendarz') ? ' (kalendarz)' : ''); break;
                      case  1: $typ_pola = 'Textarea'; break;
                      case  2: $typ_pola = 'Radio'; break;
                      case  3: $typ_pola = 'Checkbox'; break;
                      case  4: $typ_pola = 'Select'; break;
                      case  5: $typ_pola = 'Plik'; break;
                      default: $typ_pola = 'Input'; break;
                  }

                  $tablica = array(array($info['fields_id'] . '<input type="hidden" name="id[]" value="'.$info['fields_id'].'" />','center'),
                                   array($info['fields_name'],'left'),
                                   array($typ_pola,'center')
                  ); 

                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['fields_id'].'" value="'.$info['fields_order'].'" class="sort_prod" />','center');    

                  // wymagane czy nie wymagane
                  if ($info['fields_required_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Pole jest wymagane'; } else { $obraz = 'aktywny_off.png'; $alt = 'Pole nie jest wymagane'; }               
                  $tablica[] = array('<a class="TipChmurka" href="slowniki/dodatkowe_pola_zamowienia_wymagane.php?id_poz='.$info['fields_id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');

                  // aktywana czy nieaktywna
                  if ($info['fields_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole jest nieaktywne'; }               
                  $tablica[] = array('<a class="TipChmurka" href="slowniki/dodatkowe_pola_zamowienia_status.php?id_poz='.$info['fields_id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['fields_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/dodatkowe_pola_zamowienia_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="slowniki/dodatkowe_pola_zamowienia_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Dodatkowe pola do zamówień</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/dodatkowe_pola_zamowienia_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>      

            <form action="slowniki/dodatkowe_pola_zamowienia_akcja.php" method="post" class="cmxform">            
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('slowniki/dodatkowe_pola_zamowienia.php', $zapytanie, $ile_licznika, $ile_pozycji, 'fields_id'); ?>
            </script>             

            <?php if ($ile_pozycji > 0) { ?>
            <div>
            <input type="submit" style="float:right" class="przyciskNon" value="Zapisz zmiany" />
            </div>
            <?php } ?>
            
            <div class="cl"></div>
            
            </form>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
