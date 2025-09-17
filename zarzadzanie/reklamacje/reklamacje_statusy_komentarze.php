<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    if (!isset($_GET['status_id']) || (int)$_GET['status_id'] == 0) {
        $_GET['status_id'] = '0';
    }    

    $zapytanie = "
      SELECT s.comments_id, s.sort_order, s.comments_name, s.status_id, sd.languages_id, sd.comments_text
        FROM standard_complaints_comments s 
        LEFT JOIN standard_complaints_comments_description sd ON s.comments_id = sd.comments_id AND sd.languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
        WHERE s.status_id = '".(int)$_GET['status_id']."' ORDER BY sort_order";
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
                                      array('Treść','center'),
                                      array('Sort','center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['comments_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['comments_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['comments_id'].'">';
                  }    

                  $tablica = array(array($info['comments_id'],'center'),
                                   array($info['comments_name'],'center'),
                                   array($info['comments_text'],'left'),
                                   array($info['sort_order'],'center')
                  );  

                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['comments_id'];

                  $tekst .= '<a class="TipChmurka" href="reklamacje/reklamacje_statusy_komentarze_edytuj.php'.$zmienne_do_przekazania.'&status_id='.(int)$_GET['status_id'].'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="reklamacje/reklamacje_statusy_komentarze_usun.php'.$zmienne_do_przekazania.'&status_id='.(int)$_GET['status_id'].'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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

            <?php
            if ( (int)$_GET['status_id'] > 0 ) {
            ?>            
                       
                <div id="naglowek_cont">Standardowe komentarze do reklamacji - <?php echo Reklamacje::pokazNazweStatusuReklamacji((int)$_GET['status_id']); ?></div> 

                <div id="PozycjeIkon">
                    <div>
                        <a class="dodaj" href="reklamacje/reklamacje_statusy_komentarze_dodaj.php?status_id=<?php echo (int)$_GET['status_id']; ?>">dodaj nową pozycję</a>
                    </div>            
                </div>
                
                <div style="clear:both;"></div>   
            
              <?php } else { ?>
              
                <div id="naglowek_cont">Standardowe komentarze do reklamacji</div>     
              
            <?php } ?>           
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <?php
            if ( (int)$_GET['status_id'] > 0 ) {
            ?>
            <button type="button" class="przyciskNon" onclick="cofnij('reklamacje_statusy','<?php echo '?id_poz='.(int)$_GET['status_id']; ?>','reklamacje');">Powrót</button>  
            <?php
            }
            ?>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('reklamacje/reklamacje_statusy_komentarze.php', $zapytanie, $ile_licznika, $ile_pozycji, 'comments_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
