<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( !isset($_GET['oferta_id']) || (int)$_GET['oferta_id'] == 0 ) {
         $_GET['oferta_id'] = 0;
    }

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    if (!isset($_GET['oferta_id']) || (int)$_GET['oferta_id'] == 0) {
        $_GET['oferta_id'] = '0';
    }    

    $zapytanie = "SELECT * FROM offers_products WHERE offers_id = '".(int)$_GET['oferta_id']."' ORDER BY sort, products_name";
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
                                      array('ID produktu','center'),
                                      array('Zdjęcie','center'),
                                      array('Nazwa produktu'),
                                      array('Sort','center'),
                                      array('Cena netto','center'),
                                      array('Cena brutto','center'),
                                      array('Ilość produktów','center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id_products_offers']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  }      

                  $tablica = array(array($info['id_products_offers'],'center'),
                                   array($info['products_id'],'center'));
                  
                  if ( !empty($info['products_image']) ) {
                       //
                       $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                       $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                       $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true);
                       $tgm .= '</div>';
                       //
                     } else { 
                       //
                       $tgm = '-';
                       //
                  }

                  $tablica[] = array($tgm,'center');    
                  
                  $tablica[] = array($info['products_name']);
                  
                  $tablica[] = array($info['sort'],'center');    
                  
                  if ( $info['products_price'] > 0 ) {
                       $tablica[] = array($waluty->FormatujCene($info['products_price'], false, $_SESSION['domyslna_waluta']['id']), 'center');
                     } else {
                       $tablica[] = array('-', 'center');
                  }
                  
                  if ( $info['products_price_tax'] > 0 ) {
                      $tablica[] = array($waluty->FormatujCene($info['products_price_tax'], false, $_SESSION['domyslna_waluta']['id']), 'center');
                     } else {
                       $tablica[] = array('-', 'center');
                  }
                  
                  $tablica[] = array((((float)$info['products_quantity'] > 0) ? (((int)$info['products_quantity'] == $info['products_quantity']) ? (int)$info['products_quantity'] : $info['products_quantity']) : '-'),'center'); 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['id_products_offers'];

                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_produkty_edytuj.php'.$zmienne_do_przekazania.'&oferta_id='.(int)$_GET['oferta_id'].'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_produkty_usun.php'.$zmienne_do_przekazania.'&oferta_id='.(int)$_GET['oferta_id'].'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <?php if ((int)$_GET['oferta_id'] > 0) { ?>
            
                <?php
                $zapytanieNazwaoferty = "SELECT offers_nr FROM offers WHERE offers_id = '".(int)$_GET['oferta_id']."'";
                $sqlNazwa = $db->open_query($zapytanieNazwaoferty);
                $infn = $sqlNazwa->fetch_assoc();
                ?>
            
                <div id="naglowek_cont">Produkty do oferty - <?php echo $infn['offers_nr']; ?></div> 
                
                <?php
                $db->close_query($sqlNazwa);                
                
                if ( $infn['offers_nr'] != '' ) {
                ?>

                <div id="PozycjeIkon">
                    <div>
                        <a class="dodaj" href="oferty/oferty_produkty_dodaj.php?oferta_id=<?php echo (int)$_GET['oferta_id']; ?>">dodaj nowy produkt</a>
                    </div>            
                </div>
                
                <?php 
                } 
                
                unset($infn, $zapytanieNazwaoferty);
                ?>
                
                <div style="clear:both;"></div>                  
              
              <?php } else { ?>
              
                <div id="naglowek_cont">Produkty do oferty</div>     
              
            <?php } ?>

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <?php
            if ( isset($_GET['oferta_id']) && (int)$_GET['oferta_id'] > 0 ) {
            ?>
            <button type="button" class="przyciskNon" onclick="cofnij('oferty','<?php echo '?id_poz='.(int)$_GET['oferta_id']; ?>','oferty');">Powrót</button>   
            <?php
            }
            ?>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('oferty/oferty_produkty.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id_products_offers'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
