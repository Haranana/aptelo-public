<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // Aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["NAZWA_MODULU"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])),
        );
        //
        $pola[] = array('skrypt',$filtr->process($_POST["SKRYPT"]));
        $pola[] = array('klasa',$filtr->process($_POST["KLASA"]));

        $db->update_query('modules_payment' , $pola, " id = '".(int)$_POST["id"]."'");
        unset($pola);
        
        //aktualizacja nazwy modulu w tablicy orders
        //$pola = array(
        //        array('payment_method',$filtr->process($_POST["NAZWA"])),
        //);
        //$db->update_query('orders' , $pola, " payment_method = '".$_POST["STARA_NAZWA"]."'");	
        //unset($pola);

        // aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".(int)$_POST["id"]."_TYTUL'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.(int)$_POST["id"].'_TYTUL'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['NAZWA_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_'.$w])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        // ##############
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".(int)$_POST["id"]."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.(int)$_POST["id"].'_OBJASNIENIE'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA_OBJASNIENIA"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['OBJASNIENIE_'.$w])),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id'])
            );
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }

        // ##############
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".(int)$_POST["id"]."_TEKST'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.(int)$_POST["id"].'_TEKST'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA_TEKST_INFO"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['TEKST_INFO_'.$w])),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id'])
            );
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }


        reset($_POST['PARAMETRY']);
        $pola = array(
                array('wartosc',''),
        );
        $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'PLATNOSC_PRZELEWY24_CHANNEL'");	
        $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'TYLKO_WALUTA'");	
        unset($pola);

        foreach ( $_POST['PARAMETRY'] as $key => $value ) {
          if (is_array($value)) $value = implode(";", (array)$value);
          $pola = array(
                  array('wartosc',$filtr->process($value)),
                 );
          $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");	
          unset($pola);
        }

        //aktualizacja listy kategorii dla Lukas Raty
        if ( isset($_POST['id_kat']) && is_array($_POST['id_kat']) ) {
            $kategorie_wykluczone = implode(',', (array)$_POST['id_kat']);
            $pola = array(
                    array('wartosc',$kategorie_wykluczone),
                 );
            $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'PLATNOSC_LUKAS_KATEGORIE'");	
           unset($pola);
        } else {
            $kategorie_wykluczone = '';
            $pola = array(
                    array('wartosc',$kategorie_wykluczone),
                 );
            $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'PLATNOSC_LUKAS_KATEGORIE'");	
           unset($pola);
        }

        //
        Funkcje::PrzekierowanieURL('platnosc.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <script type="text/javascript" src="moduly/moduly.js"></script>

          <script>
          $(document).ready(function() {
            $("#modulyForm").validate({
              rules: {
                NAZWA_MODULU: {
                  required: true
                },
                NAZWA_0: {
                  required: true
                },
                SKRYPT: {
                  required: true
                },
                KLASA: {
                  required: true
                },
                SORT: {
                  required: true
                }
              },
              messages: {
                NAZWA_0: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          </script>        

          <form action="moduly/platnosc_edytuj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_payment WHERE id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">

                <input type="hidden" name="akcja" value="zapisz" />
            
                <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                
                <input type="hidden" name="STARA_NAZWA" value="<?php echo $info['nazwa']; ?>" />

                <p>
                  <label class="required" for="nazwa">Nazwa modułu:</label>
                  <input type="text" name="NAZWA_MODULU" size="73" value="<?php echo $info['nazwa']; ?>" id="nazwa" /><em class="TipIkona"><b>Robocza nazwa widoczna w panelu administracyjnym sklepu</b></em>
                </p>

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';

                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',200, \'\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                    
                ?>                   
                </div>
                
                <div style="clear:both"></div>
                
                    <div class="info_tab_content">
                      <?php
                      $tlumaczenie = 'PLATNOSC_' . (int)$_GET['id_poz'] . '_TYTUL';
                      $objasnienie = 'PLATNOSC_' . (int)$_GET['id_poz'] . '_OBJASNIENIE';
                      $tekstInfoPotwierdzenie = 'PLATNOSC_' . (int)$_GET['id_poz'] . '_TEKST';

                      for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                      ?>
                      
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                          <?php
                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$tlumaczenie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $nazwa = $sqls->fetch_assoc();
                          if ( count((array)$nazwa) > 0 ) {
                              $id_tlumaczenia = $nazwa['translate_constant_id'];
                          }
                          ?>

                          <p>
                            <?php if ($w == '0') { ?>
                              <label class="required" for="nazwa_0">Treść wyświetlana w sklepie:</label>
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_0"><?php echo (isset($nazwa['translate_value']) ? $nazwa['translate_value'] : '' ); ?></textarea>
                            <?php } else { ?>
                              <label for="NAZWA_<?php echo $w; ?>">Treść wyświetlana w sklepie:</label>
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="NAZWA_<?php echo $w; ?>"><?php echo (isset($nazwa['translate_value']) ? $nazwa['translate_value'] : ''); ?></textarea>
                            <?php } ?>
                          </p> 

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk);

                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$objasnienie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $objasn = $sqls->fetch_assoc();   
                          if ( count((array)$objasn) > 0 ) {
                              $id_tlumaczenia_objasnienia = $objasn['translate_constant_id'];
                          }
                          ?>

                          <p>
                            <label for="OBJASNIENIE_<?php echo $w; ?>">Treść objaśnienia w sklepie:</label>
                            <textarea cols="80" rows="3" name="OBJASNIENIE_<?php echo $w; ?>" id="OBJASNIENIE_<?php echo $w; ?>"><?php echo (isset($objasn['translate_value']) ? $objasn['translate_value'] : ''); ?></textarea>
                          </p> 

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk);

                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$tekstInfoPotwierdzenie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $tekst = $sqls->fetch_assoc();   
                          if ( count((array)$tekst) > 0 ) {
                              $id_tlumaczenia_tekst_info = $tekst['translate_constant_id'];
                          }
                          ?>

                          <label style="margin-left:10px;">Informacja wysyłana w treści wiadomości email po złożeniu zamówienia:</label>
                          <div class="odlegloscRwdEdytor" style="margin-top:-55px">
                            <textarea cols="60" rows="30" id="edytor_<?php echo $w; ?>" name="TEKST_INFO_<?php echo $w; ?>"><?php echo (isset($tekst['translate_value']) ? $tekst['translate_value'] : ''); ?></textarea>
                          </div>   

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk);
                          ?>
                        </div>
                        
                      <?php
                      }
                      ?>
                      
                    </div>

                    <input type="hidden" name="ID_TLUMACZENIA" value="<?php echo (isset($id_tlumaczenia) ? $id_tlumaczenia : ''); ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_OBJASNIENIA" value="<?php echo (isset($id_tlumaczenia_objasnienia) ? $id_tlumaczenia_objasnienia : '' ); ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_TEKST_INFO" value="<?php echo (isset($id_tlumaczenia_tekst_info) ? $id_tlumaczenia_tekst_info : ''); ?>" />

                    <p>
                      <label class="required" for="sort">Kolejność wyswietlania:</label>
                      <input type="text" name="SORT" size="5" value="<?php echo $info['sortowanie']; ?>" id="sort" /><em class="TipIkona"><b>Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania</b></em>
                    </p>

                    <p>
                      <label>Status:</label>
                      <input type="radio" value="1" name="STATUS" id="status_tak" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_tak">włączony</label>
                      <input type="radio" value="0" name="STATUS" id="status_nie" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_nie">wyłączony</label>
                    </p>

                    <?php

                    $zapytanie_parametry = "SELECT * FROM modules_payment_params WHERE modul_id = '" . (int)$_GET['id_poz'] . "' ORDER BY sortowanie";
                    $sql_parametry = $db->open_query($zapytanie_parametry);
                    
                    $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                    
                    $nrPola = 1;
                                
                    if ((int)$db->ile_rekordow($sql_parametry) > 0) {
                      
                      while ( $info_parametry = $sql_parametry->fetch_assoc() ) {
                        if ( strpos((string)$info_parametry['kod'], 'PLATNOSC_BLIK_SYSTEM_PLATNOSCI' ) !== false ) {

                            $tablica = array();
                            $tablica[] = array('id' => 'platnosc_przelewy24', 'text' => 'Płatności Przelewy24');
                            $tablica[] = array('id' => 'platnosc_tpay', 'text' => 'Płatności Tpay OpenAPI');
                            $tablica[] = array('id' => 'platnosc_payu_rest', 'text' => 'Płatności PayU REST');
                            $tablica[] = array('id' => 'platnosc_payu', 'text' => 'Płatności PayU Classic API');
                            $tablica[] = array('id' => 'platnosc_paynow', 'text' => 'Płatności PayNow');

                            echo '<p>' . "\n";
                            echo '<label for="system_platnosci_blik">'.$info_parametry['nazwa'].':</label>' . "\n";
                            echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="system_platnosci_blik"') . "<em class='TipIkona'><b>Wybrany system płatności musi być włączony i skonfigurowany</b></em>\n";
                            echo '</p>' . "\n";
                            unset($tablica);

                        } else {

                          if ( strpos((string)$info_parametry['kod'], '_STATUS_ZAMOWIENIA_START' ) !== false ) {
                            
                              $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- domyślny ---');
                              echo '<p>' . "\n";
                              echo '<label for="statusy_zamowienia_start">'.$info_parametry['nazwa'].':</label>' . "\n";
                              echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="statusy_zamowienia_start"') . "\n";
                              echo '</p>' . "\n";
                              unset($tablica);                               
                              
                          } elseif ( strpos((string)$info_parametry['kod'], '_STATUS_ZAMOWIENIA' ) !== false ) {
                            
                              $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- domyślny ---');
                              echo '<p>' . "\n";
                              echo '<label for="statusy_zamowienia">'.$info_parametry['nazwa'].':</label>' . "\n";
                              echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="statusy_zamowienia"') . "\n";
                              echo '</p>' . "\n";
                              unset($tablica);                           
                              
                          } elseif ( $info_parametry['kod'] == 'PLATNOSC_IKONA' ) {

                            echo '<p>' . "\n";
                            echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                            echo '<input type="text" size="50" name="PARAMETRY['.$info_parametry['kod'].']" value="' . $info_parametry['wartosc'] . '" ondblclick="openFileBrowser(\'foto\',\'\',\'' . KATALOG_ZDJEC . '\')" id="foto" autocomplete="off" />' . "\n";
                            echo '<em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>' . "\n";
                            echo '<span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>' . "\n";
                            echo '<span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser(\'foto\',\'\',\'' . KATALOG_ZDJEC . '\')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>' . "\n";
                            echo '</p>' . "\n"; 

                            ?>
                            <div id="divfoto" style="padding-left:10px;display:none">
                                <label>Grafika:</label>
                                <span id="fofoto">
                                    <span class="zdjecie_tbl">
                                        <img src="obrazki/_loader_small.gif" alt="" />
                                    </span>
                                </span> 

                                <?php if (!empty($info_parametry['wartosc'])) { ?>
                                
                                <script>         
                                pokaz_obrazek_ajax('foto', '<?php echo $info_parametry['wartosc']; ?>')
                                </script>  
                                
                                <?php } ?>   
                                
                            </div>                          
                            <?php                              
                              
                          } elseif ( strpos((string)$info_parametry['kod'], '_GRUPA_KLIENTOW' ) !== false ) {
                            
                              echo '<div>' . "\n";
                              echo '<table class="WyborCheckbox"><tr>' . "\n";
                          
                              echo '<td><label>'.$info_parametry['nazwa'].':</label></td>' . "\n";
                              echo '<td>' . "\n";
                              foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                  echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" id="grupa_klienta_' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array((string)$GrupaKlienta['id'], explode(';', (string)$info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="grupa_klienta_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />' . "\n";
                              }               
                              echo '</td>' . "\n";  

                              echo '</tr></table></div>' . "\n";
                            
                          } elseif ( strpos((string)$info_parametry['kod'], '_LOGO' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label for="foto_logo">'.$info_parametry['nazwa'].':</label>' . "\n";
                              echo '<input id="foto_logo" type="text" size="65" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" ondblclick="openFileBrowser(\'foto_logo\',\'\',\'' . KATALOG_ZDJEC . '\')" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em><span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser(\'foto_logo\',\'\',\'' . KATALOG_ZDJEC . '\')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>' . "\n";
                              echo '</p>' . "\n";
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_PAYU_RATY_WLACZONE' ) !== false || strpos((string)$info_parametry['kod'], 'PLATNOSC_PAYU_REST_RATY_WLACZONE' ) !== false) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">włączone</label>
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">wyłączone</label>
                              <?php
                              echo '</p>' . "\n";
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_PAYU_RATY_KALKULATOR' ) !== false || strpos((string)$info_parametry['kod'], 'PLATNOSC_PAYU_REST_RATY_KALKULATOR' ) !== false) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">włączone</label>
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">wyłączone</label>
                              <?php
                              echo '</p>' . "\n";   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_LEASELINK_KALKULATOR' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">włączone</label>
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">wyłączone</label>
                              <?php
                              echo '</p>' . "\n";   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_TPAY_RATY_KALKULATOR' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">włączone</label>
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">wyłączone</label>
                              <?php
                              echo '</p>' . "\n";   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_TPAY_RATY_RODZAJ' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="standardowe" id="pole_<?php echo $nrPola; ?>_standardowe" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'standardowe') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_standardowe">standardowe</label>
                              <input type="radio" value="zero_procent" id="pole_<?php echo $nrPola; ?>_zero_procent" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'zero_procent') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_zero_procent">3 x 0%</label>
                              <input type="radio" value="dziesiec_zero_procent" id="pole_<?php echo $nrPola; ?>_dziesiec_zero_procent" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'dziesiec_zero_procent') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_dziesiec_zero_procent">10 x 0%</label>
                              <?php
                              echo '</p>' . "\n";   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_TPAY_REST_RATY_KALKULATOR' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">włączone</label>
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">wyłączone</label>
                              <?php
                              echo '</p>' . "\n";   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_TPAY_REST_RATY_RODZAJ' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="standardowe" id="pole_<?php echo $nrPola; ?>_standardowe" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'standardowe') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_standardowe">standardowe</label>
                              <input type="radio" value="zero_procent" id="pole_<?php echo $nrPola; ?>_zero_procent" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'zero_procent') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_zero_procent">3 x 0%</label>
                              <input type="radio" value="dziesiec_zero_procent" id="pole_<?php echo $nrPola; ?>_dziesiec_zero_procent" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'dziesiec_zero_procent') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_dziesiec_zero_procent">10 x 0%</label>
                              <?php
                              echo '</p>' . "\n";   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_TRANSFERUJ_ONLINE' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="1" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">tak</label>
                              <input type="radio" value="0" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">nie</label>
                              <?php
                              echo '</p>' . "\n";
                              
                          } elseif ( strpos((string)$info_parametry['kod'], '_KATEGORIE' ) !== false ) {
                            
                              echo '<div style="padding-left:10px;padding-top:4px;">' . "\n";
                              
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              
                              echo '<div id="DrzewoPlatnosci"><table class="pkc">' . "\n";
                              //
                              $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                              for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                $check = '';
                                if ( in_array((string)$tablica_kat[$w]['id'], explode(',', (string)$info_parametry['wartosc'])) ) {
                                    $check = 'checked="checked"';
                                }
                                //  
                                echo '<tr>
                                        <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="kat_nr_'.$tablica_kat[$w]['id'].'" '.$check.' /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].'</label></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                              }
                              echo '</table></div>' . "\n";
                              unset($tablica_kat,$podkategorie);

                              if ( $info_parametry['wartosc'] != '' ) {
                                // pobieranie id kategorii do jakich jest przypisany produkt
                                $przypisane_kategorie = $info_parametry['wartosc'];
                                $kate = explode(',', (string)$przypisane_kategorie);

                                foreach ( $kate as $val ) {

                                      $sciezka = Kategorie::SciezkaKategoriiId($val, 'categories');
                                      $cSciezka = explode("_", (string)$sciezka);                    
                                      if (count($cSciezka) > 1) {
                                          //
                                          $ostatnie = strRpos($sciezka,'_');
                                          $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                          ?>
                                          
                                          <script>       
                                          podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo $przypisane_kategorie; ?>');
                                          </script>
                                          
                                      <?php
                                      unset($sciezka,$cSciezka);
                                      }

                                }

                                unset($przypisane_kategorie);  
                              }
                              echo '</div>';
                              
                          } elseif ( $info_parametry['kod'] == 'STATUS_PUNKTY' ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">tak</label>
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">nie</label>
                              <?php
                              echo '</p>' . "\n";
                              
                          } elseif ( $info_parametry['kod'] == 'TYLKO_WALUTA' ) {
                            
                              echo '<div>' . "\n";
                              echo '<table class="WyborCheckbox"><tr>' . "\n";
                          
                              echo '<td><label>'.$info_parametry['nazwa'].':</label></td>' . "\n";
                              echo '<td>' . "\n";
                              foreach ( $waluty->waluty as $tmp ) {
                                  echo '<input type="checkbox" value="' . $tmp['id'] . '" id="waluta_' . $tmp['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array($tmp['id'], explode(';',(string)$info_parametry['wartosc']))) ? 'checked="checked"' : '') .' /><label class="OpisFor" for="waluta_' . $tmp['id'] . '">' . $tmp['nazwa'] . '</label><br />' . "\n";
                              }               
                              echo '</td>' . "\n";  

                              echo '</tr></table></div>' . "\n";                            

                              echo '<div class="ostrzezenie odlegloscRwdDiv" style="padding:10px 10px 10px 22px">Jeżeli nie zostanie wybrana żadna waluta - płatność będzie dostępna dla wszystkich walut dostępnych w sklepie.</div>';
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_PRZELEWY24_CHANNEL' ) !== false ) {

                              $tablica[] = array('id' => '1', 'text' => 'karty + ApplePay + GooglePay');
                              $tablica[] = array('id' => '2', 'text' => 'przelewy');
                              $tablica[] = array('id' => '4', 'text' => 'przelew tradycyjny');
                              $tablica[] = array('id' => '16', 'text' => 'wszystkie 24/7 – udostępnia wszystkie metody płatności');
                              $tablica[] = array('id' => '64', 'text' => 'tylko metody pay-by-link');
                              $tablica[] = array('id' => '128', 'text' => 'formy ratalne');
                              $tablica[] = array('id' => '256', 'text' => 'wallety');
                              $tablica[] = array('id' => '4096', 'text' => 'karty');

                              echo '<div>' . "\n";
                              echo '<table class="WyborCheckbox"><tr>' . "\n";
                          
                              echo '<td><label>'.$info_parametry['nazwa'].':</label></td>' . "\n";
                              echo '<td>' . "\n";
                              foreach ( $tablica as $KanalPlatnosci ) {
                                  echo '<input type="checkbox" value="' . $KanalPlatnosci['id'] . '" id="kanaly_platnosci_' . $KanalPlatnosci['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array((string)$KanalPlatnosci['id'], explode(';', (string)$info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="kanaly_platnosci_' . $KanalPlatnosci['id'] . '">' . $KanalPlatnosci['text'] . '</label><br />' . "\n";
                              }               
                              echo '</td>' . "\n";  

                              echo '</tr></table></div>' . "\n";

                              unset($tablica);

                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_PAYPO_GRAFIKA' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="tak" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">tak <em class="TipIkona"><b>Jeżeli opcja zostanie włączona na karcie produktu będzie wyświetlany banner o płatności PayPo</b></em></label> 
                              <input type="radio" value="nie" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">nie</label>
                              <?php
                              echo '</p>' . "\n";                                   
                              
                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_DOSTAWCA' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label for="'.$info_parametry['kod'].'">'.$info_parametry['nazwa'].':</label>' . "\n";

                              echo '<input type="text" name="PARAMETRY['.$info_parametry['kod'].']" size="65" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" /><em class="TipIkona"><b>Nazwa dostawcy - nazwa firmy lub sklepu w którym dokonywany jest zakup. Należy to pole wypełnić w przypadku jeżeli są inne dane niż ustawieniach danych firmy prowadzącej sklep</b></em>';

                              echo '</p>' . "\n"; 

                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_BLUEMEDIA_WALUTA' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label for="waluta_platnosci">'.$info_parametry['nazwa'].':</label>' . "\n";

                              $tablica[] = array('id' => 'PLN', 'text' => 'PLN');
                              $tablica[] = array('id' => 'EUR', 'text' => 'EUR');
                              $tablica[] = array('id' => 'GBP', 'text' => 'GBP');
                              $tablica[] = array('id' => 'USD', 'text' => 'USD');

                              echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="waluta_platnosci"') . "\n";
                              unset($tablica);

                              echo '</p>' . "\n"; 

                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_COMFINO_WIDGET_TYP' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label for="comfino_widget_typ">'.$info_parametry['nazwa'].':</label>' . "\n";

                              $tablica[] = array('id' => 'simple', 'text' => 'Tekstowy');
                              $tablica[] = array('id' => 'mixed', 'text' => 'Graficzny z banerem');
                              $tablica[] = array('id' => 'with-modal', 'text' => 'Graficzny z kalkulatorem rat');
                              $tablica[] = array('id' => 'extended-modal', 'text' => 'Graficzny rozszerzony z kalkulatorem rat');

                              echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="comfino_widget_typ"') . "\n";
                              unset($tablica);

                              echo '</p>' . "\n"; 

                          } elseif ( strpos((string)$info_parametry['kod'], 'PLATNOSC_COMFINO_PLATNOSC_TYP' ) !== false ) {
                            
                              echo '<p>' . "\n";
                              echo '<label for="comfino_platnosc_typ">'.$info_parametry['nazwa'].':</label>' . "\n";

                              $tablica[] = array('id' => 'INSTALLMENTS_ZERO_PERCENT', 'text' => 'Raty 0%');
                              $tablica[] = array('id' => 'CONVENIENT_INSTALLMENTS', 'text' => 'Niskie raty');
                              $tablica[] = array('id' => 'PAY_LATER', 'text' => 'Zapłać później');
                              $tablica[] = array('id' => 'COMPANY_BNPL', 'text' => 'Odroczona płatność dla firm');

                              echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="comfino_platnosc_typ"') . "\n";
                              unset($tablica);

                              echo '</p>' . "\n"; 

                          } elseif ( strpos((string)$info_parametry['kod'], '_TERMIN_PATNOSCI' ) !== false ) {

                              $tablica = array();
                              for ( $i = 1, $c = 31; $i <= $c; $i++ ) { 
                                $tablica[] = array('id' => $i, 'text' => $i);
                              }

                            echo '<p>' . "\n";
                            echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                            echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], '') . "<em class='TipIkona'><b>Ilość dni przez które klient może wykonać płatność. Po tym okresie płatnosć nie będzie już możliwa.</b></em>\n";
                            echo '</p>' . "\n";
                            unset($tablica);

                          } else {
                            
                            if ( strpos((string)$info_parametry['kod'], '_SANDBOX' ) !== false ) {
                              
                              echo '<p>' . "\n";
                              echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                              ?>
                              <input type="radio" value="1" id="pole_<?php echo $nrPola; ?>_tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_tak">tak</label>
                              <input type="radio" value="0" id="pole_<?php echo $nrPola; ?>_nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pole_<?php echo $nrPola; ?>_nie">nie</label>
                              <?php
                              echo '</p>' . "\n";

                            } else {
                              
                              $krotki = false;
                              $wartosc = $info_parametry['wartosc'];
                              
                              if ( $info_parametry['kod'] == 'PLATNOSC_WARTOSC_ZAMOWIENIA_MIN' || $info_parametry['kod'] == 'PLATNOSC_WARTOSC_ZAMOWIENIA_MAX' || $info_parametry['kod'] == 'PLATNOSC_KOSZT' || $info_parametry['kod'] == 'PLATNOSC_KOSZT_MINIMUM' || $info_parametry['kod'] == 'PLATNOSC_DARMOWA_PLATNOSC' ) {
                                   $krotki = true;
                                   //
                                   if ( $wartosc == 0 && $info_parametry['kod'] != 'PLATNOSC_KOSZT' ) {
                                        $wartosc = '';
                                   }
                                   //
                              }
                              
                              echo '<p ' . (($info['klasa'] == 'platnosc_pobranie_indywid') ? 'style="display:none"' : '') . '>' . "\n";
                              echo '<label for="'.$info_parametry['kod'].'" '.($info_parametry['sortowanie'] > 10 && ( stripos((string)$info_parametry['kod'], '_EUR') === false && stripos((string)$info_parametry['kod'], '_WALUTA') === false ) ? 'class="required"' : '' ).'>'.$info_parametry['nazwa'].':</label>' . "\n";
                              echo '<input type="text" size="' . (($krotki) ? '35' : '65') . '" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$wartosc.'" id="'.$info_parametry['kod'].'" ';

                              if ($info_parametry['sortowanie'] < 10 ) {
                                  if ( $info_parametry['kod'] != 'PLATNOSC_KOSZT' ) {
                                    echo 'class="kropka"';
                                  } else {
                                      if ( $info['klasa'] != 'platnosc_pobranie_indywid' ) {
                                          echo 'class="PlatnoscKoszt required"';
                                      }
                                  }
                              }
                              if ( $info_parametry['sortowanie'] > 10 && ( stripos((string)$info_parametry['kod'], '_EUR') === false && stripos((string)$info_parametry['kod'], '_WALUTA') === false ) ) {
                                   echo 'class="required"';
                              }
                              echo' />' . "\n";
                              
                              unset($wartosc);
                              
                              if ( $info_parametry['kod'] == 'PLATNOSC_WARTOSC_ZAMOWIENIA_MIN' ) {
                                   echo ' ' . $_SESSION['domyslna_waluta']['symbol'] . '<em class="TipIkona"><b>Forma płatności będzie dostępna dla zamówień powyżej podanej kwoty - jeżeli pole ma być nieuwzględniane należy wpisać wartość większą od 0</b></em>';
                              }
                              if ( $info_parametry['kod'] == 'PLATNOSC_WARTOSC_ZAMOWIENIA_MAX' ) {
                                   echo ' ' . $_SESSION['domyslna_waluta']['symbol'] . '<em class="TipIkona"><b>Forma płatności będzie NIE dostępna dla zamówień powyżej podanej kwoty - jeżeli pole ma być nieuwzględniane należy pozostawić pole puste</b></em>';
                              } 
                              if ( $info_parametry['kod'] == 'PLATNOSC_KOSZT_MINIMUM' ) {
                                   echo ' ' . $_SESSION['domyslna_waluta']['symbol'] . '<em class="TipIkona"><b>Koszt płatności nie może być niższy od podanej wartości - jeżeli pole ma być nieuwzględniane należy pozostawić pole puste</b></em>';
                              }                                      
                              if ( $info_parametry['kod'] == 'PLATNOSC_KOSZT' ) {
                                   echo ' ' . $_SESSION['domyslna_waluta']['symbol'] . '<em class="TipIkona"><b>Koszt płatności podawany jako wartość kwotowa (brutto) lub jako wzór - jeżeli pole ma być nieuwzględniane należy wpisać wartość 0</b></em>';
                              }  
                              if ( $info_parametry['kod'] == 'PLATNOSC_DARMOWA_PLATNOSC' ) {
                                   echo ' ' . $_SESSION['domyslna_waluta']['symbol'] . '<em class="TipIkona"><b>Powyżej wpisanej kwoty (wartości zamówienia - niezależnie od tego czy w koszyku znajdują się produkty w wykluczoną darmową dostawą !) koszt płatności będzie wynosił 0 - jeżeli pole ma być nieuwzględniane należy pozostawić pole puste</b></em>';
                              }                              
                              
                              echo '</p>' . "\n";
                              
                              if ( $info['skrypt'] == 'platnosc_paypo.php' && $info_parametry['kod'] == 'PLATNOSC_WARTOSC_ZAMOWIENIA_MAX' ) {
                                   echo '<p>
                                            <label></label>
                                            <span class="maleInfo" style="display:inline-block;margin:0px;padding:5px 0px 5px 22px">Dla płatności PayPo maksymalna wartość zamówienia jaką można opłacić to 1000 zł. Taką wartość należy wpisac w w/w polu.</span>
                                         </p>';
                              }
                              
                            }
                            
                          }
                        }
                        $nrPola++;

                      }

                    }

                    $db->close_query($sql_parametry);
                    unset($zapytanie_parametry, $info_parametry, $nrPola);
                    
                    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                    ?>

                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                        <p>
                          <label class="required">Skrypt:</label>   
                          <input type="text" name="SKRYPT" id="skrypt" size="65" value="<?php echo $info['skrypt']; ?>" onkeyup="updateKeySkrypt();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> /><em class="TipIkona"><b>Nazwa skryptu realizującego funkcje modułu</b></em>
                        </p>

                        <p>
                          <label class="required">Nazwa klasy:</label>   
                          <input type="text" name="KLASA" id="klasa" size="65" value="<?php echo $info['klasa']; ?>" onkeyup="updateKeyKlasa();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> /><em class="TipIkona"><b>Nazwa klasy realizującej funkcje modułu</b></em>
                        </p>
                        
                    <?php } else { ?>
                    
                      <input type="hidden" name="SKRYPT" value="<?php echo $info['skrypt']; ?>" />
                      <input type="hidden" name="KLASA" value="<?php echo $info['klasa']; ?>" />
                      
                    <?php } ?>

                    <script>
                    gold_tabs('0','edytor_',200, '');
                    </script>        
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('platnosc','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>
          
        <div class="objasnienia">
            <div class="objasnieniaTytul">Koszt płatności</div>
            <div class="objasnieniaTresc">W polu można zastosować wzór stosowany do obliczania prowizji od danej formy płatności, w miejsce x zostanie wstawiona suma wartości produktów i kosztu dostawy<br />Przykłady:<br /><br />

            <div class="OpisPlatnosciRamka">
            
                <table class="OpisPlatnosci">
                
                  <tr class="OpisPlatnosciNaglowek">
                    <td><span>Wartość pola</span></td>
                    <td><span>Format</span></td>
                    <td><span>Opis</span></td>
                  </tr>
                  
                  <tr>
                    <td>&nbsp;</td>
                    <td>pole puste lub 0</td>
                    <td>koszt płatności wynosi 0</td>
                  </tr>
                  
                  <tr>
                    <td><code>11.50</code></td>
                    <td>liczba</td>
                    <td>koszt płatności wynosi 11,50, niezależnie od wartości zamówienia</td>
                  </tr>
                  
                  <tr>
                    <td><code>x*0.035</code></td>
                    <td>x, znak mnożenia, liczba</td>
                    <td>koszt płatności zostanie wyliczony wg wzoru:<br /><code>(wartosc_produktow + koszt_dostawy) * 0,035</code></td>
                  </tr>
                  
                  <tr>
                    <td><code>x*0.035+11.50</code></td>
                    <td>x, znak mnożenia, liczba, znak plus, liczba</td>
                    <td>koszt płatności zostanie wyliczony wg wzoru:<br /><code>(wartosc_produktow + koszt_dostawy) * 0,035 + 11,50</code></td>
                  </tr>
                  
                </table>
                
            </div>
            
            </div>
        </div>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>