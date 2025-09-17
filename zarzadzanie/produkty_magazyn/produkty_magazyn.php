<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    include('produkty_magazyn/produkty_magazyn_filtry.php');

    $zapytanie = 'SELECT p.products_id, 
                         p.products_price_tax, 
                         p.products_old_price,
                         p.products_quantity, 
                         p.products_quantity_alarm,
                         p.products_quantity_max_alarm,
                         p.manufacturers_id,
                         p.products_image, 
                         p.products_price_tax,
                         p.products_model, 
                         p.products_man_code,
                         p.products_ean,
                         p.products_date_added, 
                         p.products_status,
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,                          
                         p.products_availability_id,
                         p.products_shipping_time_id,
                         p.products_shipping_time_zero_quantity_id,
                         p.products_currencies_id,
                         p.products_points_only,
                         p.products_points_value,  
                         p.products_points_value_money,
                         p.products_control_storage,
                         pd.products_id, 
                         pd.language_id, 
                         pd.products_name, 
                         m.manufacturers_id,
                         m.manufacturers_name,
                         pj.products_jm_quantity_type
                  FROM products p
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                         ' . (( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'cechy') || isset($_GET['cecha_nazwa']) ) ? 'RIGHT JOIN products_attributes pa ON p.products_id = pa.products_id' . $warunki_cech : '') . '
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id' . $warunki_szukania . ' GROUP BY p.products_id '; 

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = 'SELECT p.products_id 
                         FROM products p 
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id';
                         if ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'cechy')  || isset($_GET['cecha_nazwa']) ) {
                            $ZapytanieDlaPozycji .= ' RIGHT JOIN products_attributes pa ON p.products_id = pa.products_id' . $warunki_cech;
                         }                         
                         
    $ZapytanieDlaPozycji .= $warunki_szukania . ' GROUP BY p.products_id ';
    
    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    include('produkty_magazyn/produkty_magazyn_sortowanie.php');  

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];

            $sql = $db->open_query($zapytanie);
            
            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu');
            $tablica_naglowek[] = array('Magazyn','center');
            $tablica_naglowek[] = array('Max stan magazynowy','center');
            $tablica_naglowek[] = array('Stan dostępności', 'center', '', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Wysyłka','center', 'center', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Cena','center');
            $tablica_naglowek[] = array('Status','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['products_id'].'" id="opcja_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['products_id'].'"></label><input type="hidden" name="id[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['products_id'],'center');
                  
                  // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                  $info['products_name'] = Funkcje::PodzielNazwe($info['products_name']);
                  $info['products_model'] = Funkcje::PodzielNazwe($info['products_model']);
                  
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

                  // dodatkowa zmienna do wylaczania mozliwosci zmiany statusu produktu jezeli kategoria
                  // do ktorej nalezy jest wylaczona
                  $wylacz_status = true;
                  
                  // nazwa produktu i kategorie do jakich jest przypisany
                  $do_jakich_kategorii_przypisany = '<span class="MaleInfoKat">';
                  $kategorie = $db->open_query("select distinct categories_id from products_to_categories where products_id = '".(int)$info['products_id']."'");
                  //
                  if ( (int)$db->ile_rekordow($kategorie) > 0 ) {
                      while ($id_kategorii = $kategorie->fetch_assoc()) {
                          // okreslenie nazwy kategorii
                          if ((int)$id_kategorii['categories_id'] == '0') {
                              $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
                              $wylacz_status = false;
                            } else {
                              //
                              if ( isset($TablicaKategorii[(int)$id_kategorii['categories_id']]) ) {
                                  //
                                  $do_jakich_kategorii_przypisany .= '<span style="color:#ff0000">'.$TablicaKategorii[(int)$id_kategorii['categories_id']]['text'].'</span>, ';
                                  //
                                  if ($TablicaKategorii[(int)$id_kategorii['categories_id']]['status'] == '1') {
                                     $wylacz_status = false;
                                  }
                                  //
                              }
                              //
                          }
                      }
                    } else {
                      $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
                      $wylacz_status = false;
                  }
                  $do_jakich_kategorii_przypisany = substr((string)$do_jakich_kategorii_przypisany,0,-2);
                  $do_jakich_kategorii_przypisany .= '</span>';
                  
                  $db->close_query($kategorie);
                  unset($kategorie);
                  
                  $nr_kat = '';
                  if (trim((string)$info['products_model']) != '') {
                      $nr_kat = '<span class="MaleNrKatalogowy">Nr kat: <b>'.$info['products_model'].'</b></span>';
                  }
                  
                  $kod_producenta = '';
                  if (trim((string)$info['products_man_code']) != '') {
                      $kod_producenta = '<span class="MaleNrKatalogowy">Kod prod: <b>'.$info['products_man_code'].'</b></span>';
                  }
                  
                  $kod_ean = '';
                  if (trim((string)$info['products_ean']) != '') {
                      $kod_ean = '<span class="MaleNrKatalogowy">EAN: <b>'.$info['products_ean'].'</b></span>';
                  }                  

                  // pobieranie danych o producencie
                  $prd = '';
                  if (trim((string)$info['manufacturers_name']) != '') {                     
                      //
                      $prd = '<span class="MaleProducent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
                      //
                  }                    
                  
                  $tgm = '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $kod_ean . $prd;
                  $tablica[] = array($tgm);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_producenta, $kod_ean, $prd);
                  
                  // ilosc
                  // jezeli jednostka miary calkowita
                  if ( $info['products_jm_quantity_type'] == 1 ) {
                       $info['products_quantity'] = (int)$info['products_quantity'];
                       $info['products_quantity_max_alarm'] = (int)$info['products_quantity_max_alarm'];
                  }                    
                  // musi sprawdzic czy nie jest wlaczony stan magazynowy cech i produkt nie ma cech
                  $InputIlosc = '<input type="text" name="ilosc_'.$info['products_id'].'" value="'.$info['products_quantity'].'" class="PoleEdycja" onchange="zamien_krp($(this),0,' . $info['products_jm_quantity_type'] . ')" />';
                  if (CECHY_MAGAZYN == 'tak' && ($info['products_control_storage'] == '1' || $info['products_control_storage'] == '2')) {
                      $cechy = "select distinct * from products_attributes where products_id = '".$info['products_id']."'";
                      $sqlc = $db->open_query($cechy); 
                      //
                      if ($db->ile_rekordow($sqlc) > 0) {
                          $InputIlosc = '<div class="IloscCechy"><input type="text" name="ilosc_'.$info['products_id'].'" value="'.$info['products_quantity'].'" class="PoleEdycja" disabled="disabled" /><em class="TipIkona"><b>Ilość określana na podstawie sumy stanów magazynowych cech</b></em></div>';
                      }
                      //
                      $db->close_query($sqlc);
                  }
                  
                  if ( $info['products_quantity_alarm'] > 0 && $info['products_quantity'] <= $info['products_quantity_alarm'] && MAGAZYN_SPRAWDZ_STANY == 'tak' ) {
                       //
                       $InputIloscTmp = '<div class="IloscAlarm"><em class="TipChmurka"><b>';
                       if ( $info['products_quantity'] < $info['products_quantity_alarm'] ) {
                            $InputIloscTmp .= 'Stan magazynowy produktu poniżej stanu alarmowego: ';
                       } else {
                            $InputIloscTmp .= 'Stan magazynowy równy ilości stanu alarmowego: ';
                       }
                       $InputIloscTmp .= (($info['products_jm_quantity_type'] == 1) ? (int)$info['products_quantity_alarm'] : $info['products_quantity_alarm']) . '</b><img src="obrazki/awaria.png" alt="Alarm magazynowy" /></em></div>' . $InputIlosc;
                       $InputIlosc = $InputIloscTmp;
                       unset($InputIloscTmp);
                       //
                  } else if ( $info['products_quantity_alarm'] == 0 && $info['products_quantity'] <= MAGAZYN_STAN_MINIMALNY && MAGAZYN_SPRAWDZ_STANY == 'tak' ) {
                       //
                       $InputIloscTmp = '<div class="IloscAlarmUwaga"><em class="TipChmurka"><b>';
                       if ( $info['products_quantity'] <= MAGAZYN_STAN_MINIMALNY ) {
                            $InputIloscTmp .= 'Stan magazynowy produktu poniżej domyślnego stanu alarmowego: ';
                       } else {
                            $InputIloscTmp .= 'Stan magazynowy równy ilości domyślnego stanu alarmowego: ';
                       }
                       $InputIloscTmp .= (($info['products_jm_quantity_type'] == 1) ? (int)MAGAZYN_STAN_MINIMALNY : MAGAZYN_STAN_MINIMALNY) . '</b><img src="obrazki/awaria.png" alt="Alarm magazynowy" /></em></div>' . $InputIlosc;
                       $InputIlosc = $InputIloscTmp;
                       unset($InputIloscTmp);
                       //
                  } else if ( $info['products_quantity_alarm'] > 0 && $info['products_quantity'] > $info['products_quantity_alarm'] && MAGAZYN_SPRAWDZ_STANY == 'tak' ) {
                       //
                       $InputIlosc = '<div class="IloscAlarm"><em class="TipChmurka"><b>Stan alarmowy: ' . (($info['products_jm_quantity_type'] == 1) ? (int)$info['products_quantity_alarm'] : $info['products_quantity_alarm']) . '</b><img src="obrazki/tip.png" alt="Alarm magazynowy" /></em></div>' . $InputIlosc;
                       //
                  }
                  
                  $bezMagazynu = '';
                  if ($info['products_control_storage'] == '0') {
                      $bezMagazynu = '<div class="BezMagazynu TipChmurka"><b>Produktu ma wyłączoną kontrolę stanu magazynowego</b></div>';
                  }                        
                  
                  $tablica[] = array($InputIlosc . $bezMagazynu,'center','white-space:nowrap');                   
                  unset($bezMagazynu);
                  
                  $brakuje = '';
                  if ( $info['products_quantity_max_alarm'] > 0 && $info['products_quantity'] < $info['products_quantity_max_alarm'] ) {
                       $brakuje = '<div class="DoZamowienia">brakuje: ' . ($info['products_quantity_max_alarm'] - $info['products_quantity']) . '</div>';
                  }
                  
                  $tablica[] = array((($info['products_quantity_max_alarm'] > 0) ? $info['products_quantity_max_alarm'] . $brakuje : '-'),'center');    
                  unset($brakuje);
                  
                  // stan dostepnosci
                  $tablica[] = array(Funkcje::RozwijaneMenu('dostepnosc_'.$info['products_id'], Produkty::TablicaDostepnosci('-- brak --'), $info['products_availability_id'], 'style="width:120px"'), 'center','', 'class="ListingSchowaj"');
                  
                  // termin wysyłki
                  $tgm = Funkcje::RozwijaneMenu('wysylka_'.$info['products_id'], Produkty::TablicaCzasWysylki('-- brak --'), $info['products_shipping_time_id'], 'style="width:90px"');
                  
                  $tgm .= '<div style="margin:10px 0px 10px 0px;color:#666;font-size:90%">Wysyłka dla stanu <= 0</div>';
                  
                  $tgm .= Funkcje::RozwijaneMenu('wysylka_zero_'.$info['products_id'], Produkty::TablicaCzasWysylki('-- brak --'), $info['products_shipping_time_zero_quantity_id'], 'style="width:90px"');
                  
                  
                  $tablica[] = array($tgm, 'center','', 'class="ListingSchowaj"');     
                  
                  $status_promocja = '';
                  if ( ((FunkcjeWlasnePHP::my_strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['specials_status'] == '1' ) {                             
                      $status_promocja = '<div class="wylaczonaPromocja TipChmurka"><b>Produkt nie jest wyświetlany jako promocja ze względu na datę rozpoczęcia lub zakończenia promocji</b></div>';
                  }                   

                  $tablica[] = array( $status_promocja . (((float)$info['products_old_price'] == 0) ? '' : '<div class="StaraCena">' . $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . '</div>') . 
                                     '<div class="cena">'.$waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']).'</div>'.
                                     (($info['products_points_only'] == 1) ? '<div class="TylkoPkt">' . $info['products_points_value'] . ' pkt + ' . $waluty->FormatujCene($info['products_points_value_money'],false) . '</div>' : ''),'center', 'white-space: nowrap'); 
                                     
                  unset($status_promocja);

                  // aktywany czy nieaktywny
                  $tablica[] = array((($wylacz_status == true) ? '<div class="wylKat TipChmurka"><b>Kategoria do której należy produkt jest wyłączona</b>' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' id="status_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="status_'.$info['products_id'].'"></label>' . (($wylacz_status == true) ? '</div>' : ''),'center');                                     

                  $tekst .= $listing_danych->pozycje($tablica);
                    
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'];      
                                      
                  $tekst .= '<td class="rg_right IkonyPionowo">';  
                  $tekst .= '<a class="TipChmurka" href="produkty_magazyn/produkty_magazyn_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'"><b>Przejdź do edycji produktu</b><img src="obrazki/domek.png" alt="Przejdź do edycji produktu" /></a>';
                  $tekst .= '</td></tr>';                  

                  unset($tablica);
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
          $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php', 50, 350 );
          
          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: false
          });              
        });
        </script>   

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Magazyn produktów</div>
            
            <div id="wyszukaj">
                <form action="produkty_magazyn/produkty_magazyn.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="25" /> &nbsp;
                    <input type="checkbox" name="szukaj_opis" id="szukaj_opis" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_opis'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_opis" style="margin-top:-3px;"> opis</label>
                    <input type="checkbox" name="szukaj_nrkat" id="szukaj_nrkat" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_nrkat'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_nrkat" style="margin-top:-3px;"> nr kat</label>
                    <input type="checkbox" name="szukaj_kodprod" id="szukaj_kodprod" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_kodprod'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_kodprod" style="margin-top:-3px;"> kod producenta</label>                    
                </div>  
                
                <div class="wyszukaj_select">
                    <span>ID produktu:</span>
                    <input type="text" name="id_produktu" class="calkowita" value="<?php echo ((isset($_GET['id_produktu'])) ? (int)$_GET['id_produktu'] : ''); ?>" size="10" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span>Kod EAN:</span>
                    <input type="text" name="ean" value="<?php echo ((isset($_GET['ean'])) ? $filtr->process($_GET['ean']) : ''); ?>" size="20" />
                </div>                      
                
                <div class="wyszukaj_select">
                    <span>Producent:</span>                                     
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : '')); ?>
                </div>
                
                <div class="wyszukaj_select">
                    <span>Stan dostępności:</span>                                         
                    <?php 
                    echo Funkcje::RozwijaneMenu('dostep', Produkty::TablicaDostepnosci('-- brak --'), ((isset($_GET['dostep'])) ? $filtr->process($_GET['dostep']) : '')); 
                    ?>
                </div>  

                <div class="wyszukaj_select">
                    <span>Cena brutto:</span>
                    <input type="text" name="cena_od" value="<?php echo ((isset($_GET['cena_od'])) ? $filtr->process($_GET['cena_od']) : ''); ?>" size="10" /> do
                    <input type="text" name="cena_do" value="<?php echo ((isset($_GET['cena_do'])) ? $filtr->process($_GET['cena_do']) : ''); ?>" size="10" />
                </div>
                
                <div class="wyszukaj_select">
                    <span>Termin wysyłki:</span>
                    <?php
                    echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), ((isset($_GET['wysylka'])) ? $filtr->process($_GET['wysylka']) : '')); 
                    ?>
                </div>

                <?php  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolny --');
                $tablica[] = array('id' => 'tak', 'text' => 'aktywne');
                $tablica[] = array('id' => 'tak_listing', 'text' => 'aktywne - wyświetlane w listingach');
                $tablica[] = array('id' => 'tak_nie_listing', 'text' => 'aktywne - nie wyświetlane w listingach');
                $tablica[] = array('id' => 'nie', 'text' => 'nieaktywne');
                //         
                ?>
                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('status', $tablica, ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : ''), ' style="max-width:180px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>        

                <div class="wyszukaj_select">
                    <span>Ilość magazynu:</span>
                    <input type="text" name="ilosc_od" class="calkowita" value="<?php echo ((isset($_GET['ilosc_od'])) ? $filtr->process($_GET['ilosc_od']) : ''); ?>" size="4" /> do
                    <input type="text" name="ilosc_do" class="calkowita" value="<?php echo ((isset($_GET['ilosc_do'])) ? $filtr->process($_GET['ilosc_do']) : ''); ?>" size="4" />
                </div> 
                
                <?php  
                //
                $tablica = array();
                $tablica[] = array('id' => '0', 'text' => '-- brak --');
                $tablica[] = array('id' => '1', 'text' => 'stan magazynowy równy lub mniejszy od stanu alarmowego');
                $tablica[] = array('id' => '2', 'text' => 'stan magazynowy powyżej stanu alarmowego');
                $tablica[] = array('id' => '3', 'text' => 'stan magazynowy poniżej maksymalnego stanu magazynowego');
                //             
                ?>
                <div class="wyszukaj_select">
                    <span>Alarm magazynowy:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('ilosc_alarm', $tablica, ((isset($_GET['ilosc_alarm'])) ? $filtr->process($_GET['ilosc_alarm']) : ''), ' style="max-width:200px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>                      

                <div class="wyszukaj_select">
                    <span>Data dodania:</span>
                    <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="8" class="datepicker" />
                </div>             

                <div class="wyszukaj_select">
                    <span>Dodatkowe opcje:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'cechy', 'text' => 'produkty z cechami');
                    $tablica[] = array('id' => 'bez_magazynu', 'text' => 'produkty z wyłączoną obsługą magazynu');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('dodatkowe_opcje', $tablica, ((isset($_GET['dodatkowe_opcje'])) ? $filtr->process($_GET['dodatkowe_opcje']) : ''), ' style="max-width:200px"'); ?>
                </div>                   

                <script>
                function FiltrWartoscCecha(id) {
                  var id_wart = '<?php echo ((isset($_GET['cecha_wartosc'])) ? $filtr->process($_GET['cecha_wartosc']) : ''); ?>';
                  $("#FiltrCechy").html('<img src="obrazki/_loader_small.gif" alt="" />');
                  $.get('ajax/produkt_filtr_cechy.php', { tok: '<?php echo Sesje::Token(); ?>', id: id, idwartosc: id_wart }, function(data) {
                      $("#FiltrCechy").html(data);
                  });          
                }
                </script>                
                
                <div class="wyszukaj_select">
                    <span>Z cechą:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolna nazwa cechy --');                    
                    //
                    $sql = $db->open_query("select distinct products_options_id, products_options_name from products_options where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by products_options_name asc");                        
                    while ($info = $sql->fetch_assoc()) {
                        $tablica[] = array('id' => $info['products_options_id'], 'text' => $info['products_options_name']);                    
                    }                    
                    $db->close_query($sql);
                    unset($info);
                    //
                    echo Funkcje::RozwijaneMenu('cecha_nazwa', $tablica, ((isset($_GET['cecha_nazwa'])) ? $filtr->process($_GET['cecha_nazwa']) : ''), ' style="max-width:200px" onchange="FiltrWartoscCecha(this.value)"') . ' &nbsp; ';
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolna wartość cechy --');                    
                    //                    
                    echo '<div id="FiltrCechy" style="display:inline-block">' . Funkcje::RozwijaneMenu('cecha_wartosc', $tablica, '', ' style="max-width:200px"') . '</div>';
                    unset($tablica);
                    //
                    if ( isset($_GET['cecha_nazwa']) ) {
                         echo '<script>FiltrWartoscCecha(' . (int)$_GET['cecha_nazwa'] . ');</script>';
                    }
                    ?>
                </div>                   
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['kategoria_id'])) { 
                    echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                }   
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="produkty_magazyn/produkty_magazyn.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>            
                
                <div style="clear:both"></div>
                
            </div>        
            
            <form action="produkty_magazyn/produkty_magazyn_akcja.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a1">brak</a>
                <a id="sort_a17" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a17">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a7" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a7">nr katalogowy rosnąco</a>
                <a id="sort_a8" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a8">nr katalogowy malejąco</a> 
                <a id="sort_a9" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a9">cena rosnąco</a>
                <a id="sort_a10" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a10">cena malejąco</a>             
                <a id="sort_a3" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a4">nieaktywne</a>
                <a id="sort_a5" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a5">daty dodania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a6">daty dodania malejąco</a> 
                <a id="sort_a11" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a11">ilość rosnąco</a>
                <a id="sort_a12" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a12">ilość malejąco</a>  
                <a id="sort_a13" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a13">ID malejąco</a>
                <a id="sort_a14" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a14">ID rosnąco</a> 
                <a id="sort_a15" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a15">sortowanie malejąco</a>
                <a id="sort_a16" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a16">sortowanie rosnąco</a>                
                
            </div>        
            
            <div style="clear:both;"></div>       

            <div id="PozycjeIkon">
            
                <div class="rg">
                    <a class="Export" href="produkty_magazyn/produkty_magazyn_export.php">eksportuj dane do pliku</a>
                </div>
                
            </div>
            
            <div style="clear:both;"></div>               
            
            <?php 
            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                $cSciezka = explode("_", (string)$sciezka);
               } else {
                $cSciezka = array();
            }
            ?>

            <div class="GlownyListing">

                <div class="GlownyListingKategorie">
                    
                    <div class="OknoKategoriiKontener">
                    
                        <div class="OknoNaglowek"><span class="RozwinKategorie">Kategorie</span></div>
                        <?php
                        echo '<div class="OknoKategorii"><table class="pkc">';
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                            $podkategorie = false;
                            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                            // sprawdza czy nie jest wybrana
                            $style = '';
                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                if ((int)$_GET['kategoria_id'] == $tablica_kat[$w]['id']) {
                                    $style = ' style="color:#ff0000"';
                                }
                            }
                            //
                            echo '<tr>
                                    <td class="lfp"><a href="produkty_magazyn/produkty_magazyn.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'produkty_magazyn\')" />' : '').'</td>
                                  </tr>
                                  '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                        }
                        if ( count($tablica_kat) == 0 ) {
                             echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                        }                            
                        echo '</table></div>';
                        unset($tablica_kat,$podkategorie,$style);
                        ?>        

                        <?php 
                        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                            $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                            $cSciezka = explode("_", (string)$sciezka);                    
                            if (count($cSciezka) > 1) {
                                //
                                $ostatnie = strRpos($sciezka,'_');
                                $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                ?>
                                
                                <script>           
                                podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','produkty_magazyn');
                                </script>
                                
                            <?php
                            unset($sciezka,$cSciezka);
                            }
                        } ?>
                        
                    </div>
                        
                </div>
                
                <div style="GlownyListingProdukty">
                    
                    <div id="wynik_zapytania" class="WynikZapytania"></div>
                    <div id="aktualna_pozycja">1</div>
                    
                    <script>
                    $(document).ready(function() {
                        $("#akcja_dolna").change( function () {
                            var va = $("#akcja_dolna").val();
                            if (va == '4') {
                                $("#WartDostepnosc").css('display','block');
                               } else {
                                $("#WartDostepnosc").css('display','none');
                            }
                            if (va == '5') {
                                $("#WartWysylka").css('display','block');
                               } else {
                                $("#WartWysylka").css('display','none');
                            }   
                            if (va == '6') {
                                $("#WartWysylkaZero").css('display','block');
                               } else {
                                $("#WartWysylkaZero").css('display','none');
                            }                              
                        });
                    });
                    </script>                         
                    
                    <div id="akcja" class="AkcjaOdstep">
                    
                        <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                        
                        <div class="lf" style="padding-right:20px">
                            <span onclick="akcja(1)">zaznacz wszystkie</span>
                            <span onclick="akcja(2)">odznacz wszystkie</span>
                        </div>
                    
                        <div id="akc">
                        
                            Wykonaj akcje: 
                            
                            <select name="akcja_dolna" id="akcja_dolna">
                                <option value="0"></option>
                                <?php
                                /*
                                <option value="1">zmień status zaznaczonych na nieaktywne</option>
                                <option value="2">zmień status zaznaczonych na aktywne</option>
                                <option value="3">usuń zaznaczone produkty</option>
                                */
                                ?>
                                <option value="4">zmień stan dostępności zaznaczonych</option>
                                <option value="5">zmień termin wysyłki zaznaczonych</option>
                                <option value="6">zmień termin wysyłki (dla stanu poniżej 0) zaznaczonych</option>
                            </select>
                            
                        </div>
                        
                        <div style="clear:both;"></div>
                        
                        <div id="WartDostepnosc" style="display:none">
                            <label for="dostepnosc" style="width:auto">Stan dostępności:</label><?php echo Funkcje::RozwijaneMenu('dostepnosc', Produkty::TablicaDostepnosci('-- brak --'), '', 'id="dostepnosc"'); ?>
                        </div>
                        
                        <div id="WartWysylka" style="display:none">
                            <label for="wysylka" style="width:auto">Termin wysyłki:</label><?php echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), '', 'id="wysylka"'); ?>
                        </div>                             
                        
                        <div id="WartWysylkaZero" style="display:none">
                            <label for="wysylka_zero" style="width:auto">Termin wysyłki:</label><?php echo Funkcje::RozwijaneMenu('wysylka_zero', Produkty::TablicaCzasWysylki('-- brak --'), '', 'id="wysylka"'); ?>
                        </div>                         
                        
                    </div>                           
                    
                    <div id="dolny_pasek_stron" class="AkcjaOdstep"></div>
                    <div id="pokaz_ile_pozycji" class="AkcjaOdstep"></div>
                    <div id="ile_rekordow" class="AkcjaOdstep"><?php echo $ile_pozycji; ?></div>
                    
                    <?php if ($ile_pozycji > 0) { ?>
                    <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                    <?php } ?>

                </div>
                        
            </div>
            
            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('produkty_magazyn/produkty_magazyn.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id', ILOSC_WYNIKOW_NA_STRONIE, ADMIN_DOMYSLNE_SORTOWANIE); ?>
            </script>        
                
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
