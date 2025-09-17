<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    if (isset($_GET['art_id']) && (int)$_GET['art_id'] > 0) {
        $zapytanie = 'SELECT * FROM newsdesk_comments WHERE newsdesk_id = ' . (int)$_GET['art_id'] . ' ORDER BY	date_added desc';
    } else {
        $zapytanie = 'SELECT * FROM newsdesk_comments WHERE status = 0 ORDER BY	date_added desc';
    }

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
                                      array('Data dodania','center'),
                                      array('Nick','center'),
                                      array('Email','center'),
                                      array('Telefon','center'),
                                      array('Komentarz','center'),
                                      array('Status','center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['newsdesk_comments_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['newsdesk_comments_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['newsdesk_comments_id'].'">';
                  }     
                  
                  $nazwa_artykulu = '';
                  if (!isset($_GET['art_id'])) {
                      //
                      $zapytanie_art = "SELECT newsdesk_article_name FROM newsdesk_description WHERE newsdesk_id = '" . (int)$info['newsdesk_id'] . "' AND language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";                
                      $sql_art = $db->open_query($zapytanie_art);
                      //
                      $infc = $sql_art->fetch_assoc();
                      $nazwa_artykulu = '<div style="margin-bottom:10px;color:#444">Dotyczy artykułu: <b>' . $infc['newsdesk_article_name'] . '</b></div>';
                      //
                      $db->close_query($sql_art);
                      unset($zapytanie_art,$infc);                      
                      //
                  }

                  // komentarz do opinii
                  $komentarz_odpowiedz = '';
                  if ( !empty($info['comments_answers']) ) {
                       $komentarz_odpowiedz = '<div style="font-style:italic;margin-top:10px">Odpowiedź: <br /> ' . $info['comments_answers'] . '</div>';
                  }                  
                  
                  $tablica = array(array($info['newsdesk_comments_id'],'center'),
                                   array(date('d-m-Y G:H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])),'center','white-space:nowrap'),
                                   array($info['nick'],'center'),
                                   array($info['email'],'center'),
                                   array($info['telefon'],'center'),
                                   array('<div class="Komentarz">' . $nazwa_artykulu . $info['comments'] . $komentarz_odpowiedz . '</div>')
                  );  
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['newsdesk_comments_id'] . ((isset($_GET['art_id']) && (int)$_GET['art_id'] > 0) ? '&art_id='.(int)$_GET['art_id'] : '');
                  
                  // zatwierdzony czy nie
                  if ($info['status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten komentarz nie jest zatwierdzony'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten komentarz nie jest zatwierdzony'; }               
                  $tablica[] = array('<a class="TipChmurka" href="aktualnosci/aktualnosci_komentarze_status.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  $tekst .= '<a class="TipChmurka" href="aktualnosci/aktualnosci_komentarze_odpowiedz.php'.$zmienne_do_przekazania.'"><b>Odpowiedz na komentarz</b><img src="obrazki/powrot.png" alt="Odpowiedz" /></a>';
                  $tekst .= '<a class="TipChmurka" href="aktualnosci/aktualnosci_komentarze_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="aktualnosci/aktualnosci_komentarze_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">
            
                <?php if (isset($_GET['art_id']) && (int)$_GET['art_id'] > 0) { ?>
            
                Komentarze klientów do artykułu: 
            
                <?php
                $zapytanie_art = "SELECT newsdesk_article_name FROM newsdesk_description WHERE newsdesk_id = '" . (int)$_GET['art_id'] . "' AND language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";                
                $sql_art = $db->open_query($zapytanie_art);
                //
                $infc = $sql_art->fetch_assoc();
                echo $infc['newsdesk_article_name'];
                //
                $db->close_query($sql_art);
                unset($zapytanie_art,$infc);
                
                } else { ?>
                  
                Niezatwierdzone komentarze klientów do artykułów
                  
                <?php } ?> 
            
            </div>     

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <?php if (isset($_GET['art_id']) && (int)$_GET['art_id'] > 0) { ?>
            <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo '?id_poz='.$_GET['art_id']; ?>','aktualnosci');">Powrót</button> 
            <?php } ?>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('aktualnosci/aktualnosci_komentarze.php', $zapytanie, $ile_licznika, $ile_pozycji, 'newsdesk_comments_id'); ?>
            </script>             
 
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
