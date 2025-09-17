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
    if (isset($_GET['grupa']) && !empty($_GET['grupa'])) {
        $szukana_wartosc = $filtr->process($_GET['grupa']);
        $warunki_szukania = " and b.banners_group = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if (isset($_GET['status']) && $_GET['status'] != '0') {
        $szukana_wartosc = ( $_GET['status'] == '1' ? $filtr->process($_GET['status']) : '0');
        $warunki_szukania .= " and b.status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . $id_klienta . ", b.banners_customers_group_id) ";        
        unset($id_klienta);
    }          

    $zapytanie = "select * from banners b, banners_group bg where b.banners_group = bg.banners_group_code " . $warunki_szukania;
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    
    $db->close_query($sql);
    
    $zapytanie .= " order by banners_group, sort_order";
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID','center'),
                                      array('Nazwa grupy','center'),
                                      array('Obrazek', 'center'),
                                      array('Rozdzielczość', 'center', '', 'class="ListingSchowaj"'),
                                      array('Dostępny dla języka','center', '', 'class="ListingSchowaj"'),
                                      array('Data dodania','center', '', 'class="ListingSchowaj"'),
                                      array('Data rozpoczęcia wyświetlania','center'),
                                      array('Data zakończenia wyświetlania','center'),                                      
                                      array('Ilość kliknięć','center'),
                                      array('Sort','center'),
                                      array('Grupa klientów', 'center', '', 'class="ListingSchowaj"'),
                                      array('Status','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['banners_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['banners_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['banners_id'].'">';
                  }       
                  
                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['banners_id'].'" id="opcja_'.$info['banners_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['banners_id'].'"></label><input type="hidden" name="id[]" value="'.$info['banners_id'].'" />','center');
                  
                  $tablica[] = array($info['banners_id'] . '<input type="hidden" name="id[]" value="'.$info['banners_id'].'" />','center');
                  $tablica[] = array($info['banners_group'] . '<div class="OpisGrupyBannerow">' . $info['banners_group_title'] . '</div>','center');
                  
                  $ex = pathinfo('../' . KATALOG_ZDJEC . '/' . $info['banners_image']);
                  //
                  if ( !isset($ex['extension']) ) {
                       //
                       $roz = explode('.', (string)$info['banners_image']);
                       $ex['extension'] = $roz[ count($roz) - 1];
                       //
                  }                          
                  //
                  $rozszerzenie = strtolower((string)$ex['extension']);  
                          
                  if ( !empty($info['banners_image']) && is_file('../' . KATALOG_ZDJEC . '/' . $info['banners_image']) ) {
                      if (getimagesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']) != false || $rozszerzenie == 'svg') {
                          $tgm = Funkcje::pokazObrazek($info['banners_image'], $info['banners_image'], '80', '80');
                      } else {
                          $tgm = '';
                      }                          
                   } else { 
                      $tgm = '';
                  }
                  
                  // nazwa banneru
                  $tgm .= '<div style="margin:10px;font-size:11px">' . $info['banners_title'] . '</div>';
                  
                  $tablica[] = array($tgm,'center');

                  $tgm = '';

                  if (!empty($info['banners_image']) && is_file('../' . KATALOG_ZDJEC . '/'.$info['banners_image'])) {
                    if ( file_exists('../' . KATALOG_ZDJEC . '/'.$info['banners_image']) ) {
                      // wielkosc pliku
                      $kb = filesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']);
                      
                      // ustalenie czy plik jest obrazkiem
                      //
                      $Rodzielczosc = '-';
                      if ( $kb > 0 ) {
                          //
                          // czy plik jest obrazkiem
                          if (getimagesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']) != false) {
                              //
                              list($szerokosc, $wysokosc) = getimagesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']);
                              $tgm = $szerokosc . ' x ' . $wysokosc;
                              //
                          }

                          // wielkosc pliku
                          $wielkosc_pliku = $kb;
                          if ($wielkosc_pliku > 1048576) {
                             $wielkosc_pliku = number_format(round(($wielkosc_pliku/1048576), 1), 1, '.', '') . ' MB';
                          } elseif ($wielkosc_pliku > 1024) {
                             $wielkosc_pliku = number_format(round(($wielkosc_pliku/1024), 0), 2, '.', '') . ' kB';
                          } else  {
                             $wielkosc_pliku = number_format($wielkosc_pliku, 0, '.', '') . ' B';
                          }    
                   
                          $tgm .= '<small class="FormatPlikuLista">format: ' . $rozszerzenie . '</small><small class="RozmiarPlikuLista">rozmiar: ' . $wielkosc_pliku . '</small>';
                          
                          unset($wielkosc_pliku);
                          //
                      }                                            
                      // 
                    } else {
                      $tgm = 'brak pliku';
                    }
                  } else { 
                    $tgm = '-';
                  }
                  $tablica[] = array((($tgm == '') ? '-' : $tgm),'center', '', 'class="ListingSchowaj"');  

                  unset($szerokosc, $wysokosc, $kb);                  
                  
                  $jaki_jezyk = 'wszystkie dostępne';
                  $jezyki = Funkcje::TablicaJezykow();
                  for ($w = 0, $c = count($jezyki); $w < $c; $w++) {
                       if ($jezyki[$w]['id'] == $info['languages_id']) {
                           $jaki_jezyk = $jezyki[$w]['text'];
                       }
                  }
                  $tablica[] = array($jaki_jezyk,'center', '', 'class="ListingSchowaj"');                  

                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-'),'center','', 'class="ListingSchowaj"');
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['banners_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['banners_date'])) : '-'),'center',''); 

                  $tablica[] = array(((Funkcje::czyNiePuste($info['banners_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['banners_date_end'])) : '-'),'center','');                     
                  
                  $tablica[] = array($info['banners_clicked'],'center');
                  
                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['banners_id'].'" value="'.$info['sort_order'].'" class="sort_prod" />','center');
                  
                  $tgm = '';
                  $tabGrup = explode(',', (string)$info['banners_customers_group_id']);
                  if ( count($tabGrup) > 0 && $info['banners_customers_group_id'] != 0 ) {
                       foreach ( $tabGrup as $idGrupy ) {
                          $tgm .= '<span class="grupa_klientow">' . Klienci::pokazNazweGrupyKlientow($idGrupy) . '</span><br />';
                       }
                  }      
                  $tablica[] = array( (($tgm != '') ? $tgm : '-'),'center', '', 'class="ListingSchowaj"');
                  unset($tabGrup, $tgm);
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['banners_id'];      
                  
                  // aktywana czy nieaktywna
                  if ($info['status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten banner jest aktywny'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten banner jest nieaktywny'; }              
                  $tablica[] = array('<a class="TipChmurka" href="wyglad/bannery_zarzadzanie_status.php'. $zmienne_do_przekazania .'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                          

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<a class="TipChmurka" href="wyglad/bannery_zarzadzanie_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="wyglad/bannery_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj banner</b><img src="obrazki/duplikuj.png" alt="Duplikuj banner" /></a>';                   
                  $tekst .= '<a class="TipChmurka" href="wyglad/bannery_zarzadzanie_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
                  if ( $rozszerzenie == 'jpg' || $rozszerzenie == 'webp' || $rozszerzenie == 'png' ) {
                       $tekst .= '<br /><a class="TipChmurka" href="narzedzia/konwersja_grafiki.php?plik='.base64_encode((string)$info['banners_image']).'&id_banner='.$info['banners_id'].'"><b>Konwertuj format obrazka</b><img src="obrazki/zdjecie_konwersja.png" alt="Konwertuj format obrazka" /></a>';
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
            
            <div id="naglowek_cont">Bannery</div>     
            
            <div id="wyszukaj">
                <form action="wyglad/bannery_zarzadzanie.php" method="post" id="poForm" class="cmxform"> 
                  
                <div class="wyszukaj_select">
                    <span>Wyświetl bannery tylko dla grupy:</span>                
                    <?php
                    $zapytanie_tmp = "select distinct * from banners_group order by banners_group_code asc";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica = array();
                    $tablica[] = array('id' => 0, 'text' => '-- dowolna --');
                    while ($infs = $sqls->fetch_assoc()) { 
                        $tablica[] = array('id' => $infs['banners_group_code'], 'text' => $infs['banners_group_code'] . ' - ' . $infs['banners_group_title']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);                   

                    echo Funkcje::RozwijaneMenu('grupa', $tablica, '', 'style="width:400px"');                    
                    ?>                    
                </div> 

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    //
                    $tablica_statusow = array();
                    $tablica_statusow[] = array('id' => '0', 'text' => '-- dowolny --');
                    $tablica_statusow[] = array('id' => '1', 'text' => 'Aktywne');
                    $tablica_statusow[] = array('id' => '2', 'text' => 'Wyłączone');
                    echo Funkcje::RozwijaneMenu('status', $tablica_statusow, ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : '0'));
                    ?>
                </div> 

                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
                    ?>
                </div>     
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="wyglad/bannery_zarzadzanie.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
            </div>

            <?php         
            if (count($tablica) > 0) {
            ?>
                <div id="PozycjeIkon">
                
                    <div class="lf">
                    
                        <a class="dodaj" href="wyglad/bannery_zarzadzanie_dodaj.php">dodaj nową pozycję</a>           
                    
                    </div>
                    
                    <?php if ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' )) { ?>
                    <div class="rg">  
                    
                        <a class="Import" href="wyglad/bannery_zarzadzanie_import.php">importuj dane bannerów</a>
                        
                    </div>    
                    <?php } ?>  

                </div>
            <?php
            } else {
                ?>
                <div id="PozycjeIkon">
                    <div>
                        <span class="ostrzezenie">Nie można dodać nowego banneru - nie są zdefiniowane grupy bannerów</span>
                    </div>
                </div>
                <?php
            }
            unset($tablica);
            ?>
            
            <div style="clear:both;"></div> 

            <form action="wyglad/bannery_zarzadzanie_akcja.php" method="post" class="cmxform">            
        
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
                        <option value="1">usuń zaznaczone bannery</option>
                        <option value="2">wyzeruj licznik odwiedzin</option>
                        <option value="3">eksportuj wybrane bannery</option>
                    </select>
                </div>
                
                <div style="clear:both;"></div>
                
            </div>              
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('wyglad/bannery_zarzadzanie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'banners_id'); ?>
            </script>             

            <?php if ($ile_pozycji > 0) { ?>
            <div><input type="submit" style="float:right" class="przyciskNon" value="Zapisz zmiany" /></div>
            <?php } ?> 
            
            <div class="cl"></div>

            </form>            
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
