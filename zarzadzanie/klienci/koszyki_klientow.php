<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $warunki_szukania = '';
    
    // jezeli jest szukanie
    if (isset($_GET['nr_ip']) && $_GET['nr_id'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['nr_ip']);
        $warunki_szukania .= " and customers_ip LIKE '%" . $szukana_wartosc . "%'";
    }
    
    $zapytanie = "SELECT cb.customers_basket_id, cb.customers_id, cb.customers_ip, cb.session_id, COUNT(*) as liczba_pozycji, c.customers_firstname, c.customers_lastname, c.customers_email_address, MAX(cb.customers_basket_date_added) as data_koszyka FROM customers_basket cb
               LEFT JOIN customers c ON c.customers_id = cb.customers_id " . $warunki_szukania . " where cb.customers_basket_date_added > '" . date('Y-m-d',time() - (30 * 86400)) . "' group by cb.customers_ip, cb.customers_id";

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
         
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'data_koszyka desc';
                break;
            case "sort_a2":
                $sortowanie = 'data_koszyka asc';
                break;                    
        }            
    } else { $sortowanie = 'data_koszyka desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {

            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('Klient', 'center'),
                                      array('Adres IP', 'center'),
                                      array('Ilość produktów', 'center'),
                                      array('Data ostatniej aktywności', 'center'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $LicznikWierszy = 0;
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
              
                $pattern = '/\b((25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\b/';
                $nrip = '';
                
                if (preg_match($pattern, $info['customers_ip'], $match)) {
                    $nrip = $match[0];
                }          

                $info['customers_ip'] = $nrip;
              
                if ( $info['customers_ip'] != '' ) {
                      
                    if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['customers_basket_id']) {
                       $tekst .= '<tr class="pozycja_on" id="sk_'.$info['customers_basket_id'].'">';
                     } else {
                       $tekst .= '<tr class="pozycja_off" id="sk_'.$info['customers_basket_id'].'">';
                    } 
                    
                    $tablica = array();

                    $tablica[] = array('<input type="checkbox" name="opcja[]" id="opcja_'.$info['customers_basket_id'].'" value="'.$info['customers_basket_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['customers_basket_id'].'"></label><input type="hidden" name="id[]" value="'.$info['customers_basket_id'].'" />','center');
                    
                    $wyswietlana_nazwa = '';
                    
                    if ( (int)$info['customers_id'] > 0 ) {
                          //
                          $wyswietlana_nazwa .= '<a href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '</a>';
                          //
                          // email
                          if (!empty($info['customers_email_address'])) {
                              $wyswietlana_nazwa .= '<span class="MalyMail ListingSchowaj" style="display:block">' . $info['customers_email_address'] . '</span>';
                          }
                          //
                    } else {
                          //
                          $wyswietlana_nazwa .= 'Gość';
                          //
                          // poszuka jeszcze ip
                          $sqlIp = $db->open_query("select customers_id, customers_firstname, customers_lastname, customers_email_address from customers where customers_ip = '" . $info['customers_ip'] . "'");
                          //
                          if ( (int)$db->ile_rekordow($sqlIp) > 0 ) {
                               //
                               $infi = $sqlIp->fetch_assoc();
                               //
                               $wyswietlana_nazwa .= '<div style="border-top:1px solid #dbdbdb;margin-top:10px;padding-top:8px"><div style="padding-bottom:5px;font-weight:bold">Wg nr IP klient:</div>';
                               $wyswietlana_nazwa .= '<a href="klienci/klienci_edytuj.php?id_poz=' . $infi['customers_id'] . '">' . $infi['customers_firstname'] . ' ' . $infi['customers_lastname'] . '</a>';
                               //
                               // email
                               if (!empty($infi['customers_email_address'])) {
                                   $wyswietlana_nazwa .= '<span class="MalyMail ListingSchowaj" style="display:block">' . $infi['customers_email_address'] . '</span>';
                               }
                               //
                               $wyswietlana_nazwa .= '</div>';
                               //
                          }
                          //
                          $db->close_query($sqlIp);                          
                          //
                    }
                    
                    $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');
                    
                    $tablica[] = array('<span class="NrIp SkadTrafil" onclick="szczegoly_ip(\'' . $info['customers_ip'] . '\',\'' . $LicznikWierszy . '\')">' . $info['customers_ip'] . '</span><div class="IdWyglad" id="ip_' . $LicznikWierszy . '">', 'center');
                    $tablica[] = array($info['liczba_pozycji'], 'center');
                    $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['data_koszyka'])),'center');

                    // zmienne do przekazania
                    $zmienne_do_przekazania = '?id_poz='.(int)$info['customers_basket_id']; 
                    
                    $tekst .= $listing_danych->pozycje($tablica);
                    
                    $tekst .= '<td class="rg_right IkonyPionowo">';
                    
                    $tekst .= '<em class="TipChmurka"><b>Pokaż zawartość koszyka</b><img onclick="podgladKoszyka(\'' . base64_encode($info['customers_ip']) . '\',\'' . (int)$info['customers_id'] . '\')" class="cur" style="cursor:pointer;" src="obrazki/edytuj.png" alt="Koszyk" /></em>';
                    $tekst .= '<a class="TipChmurka" href="klienci/koszyki_klientow_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                    
                    if ( (int)$info['customers_id'] > 0 ) {
                         $tekst .= '<a class="TipChmurka" href="klienci/klienci_wyslij_email.php?id_poz='.(int)$info['customers_id'].'&koszyk=tak"><b>Wyślij wiadomość e-mail</b><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail" /></a>';
                    }
                    
                    $tekst .= '</td></tr>';
                    
                    $LicznikWierszy++;
                    
                }
                  
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
               $('#potwierdzenie_usuniecia').hide();
               if ( this.value == '1' ) { 
                    $('#potwierdzenie_usuniecia').show();                  
               }               
            });            
        });

        function szczegoly_ip(ip, id) {
            $('.IdWyglad').hide();
            $('#ip_'+id).html('<div id="LadujeDane"><span>Ładuje dane ...</span></div>');
            $('#ip_'+id).css('display','block');
            $.get('ajax/ip_lokalizacja.php?tok=<?php echo Sesje::Token(); ?>', { ip: ip, id: id }, function(data) {
                $('#ip_'+id).css('display','none');
                $('#ip_'+id).slideDown("fast");
                $('#ip_'+id).html(data);
            });
        }    
        function podgladKoszyka(ip, id_klienta) {
            $.colorbox( { href:"ajax/koszyk_klienta.php?ip=" + ip + '&uzytkownik_id=' + id_klienta, maxHeight:'90%', open:true, initialWidth:50, initialHeight:50, onComplete : function() { $(this).colorbox.resize(); } }); 
        }       
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Koszyki klientów</div>

            <div id="wyszukaj">
                <form action="klienci/koszyki_klientow.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Adres IP:</span>
                    <input type="text" name="nr_ip" id="nr_ip" value="<?php echo ((isset($_GET['nr_ip'])) ? $filtr->process($_GET['nr_ip']) : ''); ?>" size="30" />
                </div>                  

                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra  
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="klienci/koszyki_klientow.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="klienci/koszyki_klientow_akcja.php" method="post" class="cmxform">

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="klienci/koszyki_klientow.php?sort=sort_a1">data koszyka malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/koszyki_klientow.php?sort=sort_a2">data koszyka rosnąco</a>
            
            </div>             

            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            
            <div id="akcja">
            
                <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                
                <div class="lf" style="padding-right:20px">
                  <span onclick="akcja(1)">zaznacz wszystkie</span>
                  <span onclick="akcja(2)">odznacz wszystkie</span>
                </div>
                
                <div id="akc">
                  Wykonaj akcje: 
                  <select name="akcja_dolna" id="akcja_dolna">
                    <option value="0"></option>
                    <option value="1">usuń wybrane koszyki</option>
                  </select>
                </div>
                
                <div class="cl"></div>
              
            </div>
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <?php if ($ile_pozycji > 0) { ?>
            <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
            <?php } ?>  
            
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('klienci/koszyki_klientow.php', $zapytanie, $ile_licznika, $ile_pozycji, 'customers_basket_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
