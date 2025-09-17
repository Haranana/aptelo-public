<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
    $TablicaUzytkownikow = $AllegroRest->TablicaUzytkownikow();

    $zapytanie = "
      SELECT aa.*, t.post_buy_form_created_date, t.transaction_id, t.buyer_name, t.buyer_email_address, t.buyer_phone FROM allegro_auctions_sold aa 
      LEFT JOIN allegro_transactions t ON aa.transaction_id = t.allegro_transaction_id
      WHERE aa.auction_id = '".$_GET['aukcja_aukcja_id']."'";

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    $zapytanie .= " ORDER BY auction_buy_date DESC";    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];   

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Numer transakcji','center', '', ''),
                                      array('Kupujący','center'),
                                      array('E-mail','center', '', ''),
                                      array('Telefon','center', '', ''),
                                      array('Ilość','center', '', ''),
                                      array('Cena zakupu','center', '', ''),
                                      array('Data zakupu','center', '', ''));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['allegro_auction_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['allegro_auction_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['allegro_auction_id'].'">';
                }        
                $link = '';

                $tablica = Array();

                $tablica[] = array($info['transaction_id'],'center');
                
                $tablica[] = array($info['buyer_name'],'center');

                $tablica[] = array('<a href="mailto:'.$info['buyer_email_address'].'" >'.$info['buyer_email_address'].'</a>','center', '', 'class="ListingSchowaj"');

                $tablica[] = array($info['buyer_phone'],'center');
                $tablica[] = array($info['auction_quantity'],'center');
                $tablica[] = array($waluty->FormatujCene($info['auction_price']),'center');

                $tablica[] = array( date('d-m-Y H:i:s',$info['auction_buy_date']),'center');

                                 
                $tekst .= $listing_danych->pozycje($tablica);
                
                $tekst .= '<td class="rg_right IkonyPionowo">';
                $postbuyform = $info['auction_postbuy_forms'];

                $zmienne_do_przekazania = '?id_poz='.$info['allegro_auction_id'].'&amp;aukcja_id='.$_GET['aukcja_id'];
                
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

        <script>
        $(document).ready(function() {
          $('#akcja_dolna').change(function() {
            if ( this.value == '0' || this.value == '2' ) {
              $("#page").load('allegro/blank.php');
            }
          });

          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: false
          });                
        });
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Oferty zakupu w aukcji</div>

            <div class="cl"></div>

            <form action="allegro/allegro_akcja.php" method="post" class="cmxform">

              <div class="sprzedazAllegro">

                  <div id="wynik_zapytania"></div>
                  <div id="aktualna_pozycja">1</div>

                  <div id="page"></div>

                  <div id="dolny_pasek_stron"></div>
                  <div id="pokaz_ile_pozycji"></div>
                  <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
              
              </div>
              
            </form>

            <?php if ((int)$_GET['aukcja_id'] > 0) { ?>
            <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo '?id_poz='.$_GET['aukcja_id']; ?>','allegro');">Powrót</button> 
            <?php } ?>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('allegro/allegro_sprzedaz_tranzakcja.php', $zapytanie, $ile_licznika, $ile_pozycji, 'allegro_auction_id'); ?>
            </script>                

        </div>

        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
