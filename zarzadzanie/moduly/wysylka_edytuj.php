<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// cennik paczkomaty
$gabarytPaczkomat = array('11.28', '12.60', '15.92');
$gabarytKurierInpost = array('13.50', '16.99', '19.99', '28.99');

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if (!isset($_POST['PARAMETRY']['WYSYLKA_DOSTEPNE_PLATNOSCI']))
          $_POST['PARAMETRY']['WYSYLKA_DOSTEPNE_PLATNOSCI'] = array();
        if (!isset($_POST['PARAMETRY']['WYSYLKA_KRAJE_DOSTAWY']))
          $_POST['PARAMETRY']['WYSYLKA_KRAJE_DOSTAWY'] = array();
        if (!isset($_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW']))
          $_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW'] = array();
        if (!isset($_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE']))
          $_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'] = array();          
        //
        // aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["NAZWA"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])),
                array('integracja',(isset($_POST["INTEGRACJA"]) ? $filtr->process($_POST["INTEGRACJA"]) : '' )));
                
        //
        $pola[] = array('skrypt',$filtr->process($_POST["SKRYPT"]));
        $pola[] = array('klasa',$filtr->process($_POST["KLASA"]));

        $db->update_query('modules_shipping' , $pola, " id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        // aktualizacja nazwy modulu w tablicy orders
        //$pola = array(
        //        array('shipping_module',$filtr->process($_POST["NAZWA"])),
        //);
        //$db->update_query('orders' , $pola, " shipping_module = '".$_POST["STARA_NAZWA"]."'");	
        //unset($pola);

        // aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".(int)$_POST["id"]."_TYTUL'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$_POST["id"].'_TYTUL'),
            array('section_id', '4'));
            
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
                        array('language_id',$ile_jezykow[$w]['id']));
                        
            } else {
            
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id']));
                        
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        
        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".(int)$_POST["id"]."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$_POST["id"].'_OBJASNIENIE'),
            array('section_id', '4'));
            
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
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        

        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".(int)$_POST["id"]."_INFORMACJA'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$_POST["id"].'_INFORMACJA'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA_INFORMACJI"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['INFORMACJA_'.$w])),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        
        //INSERT INTO `modules_shipping_params` (`id`, `modul_id`, `nazwa`, `kod`, `wartosc`, `sortowanie`) VALUES('', 9, 'Punkt odbioru', 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1', 'Jakis tam sobie punkt', 20);
        if ( $_POST['KLASA'] == 'wysylka_odbior_osobisty' ) {

            $db->delete_query('modules_shipping_params', "kod LIKE '%WYSYLKA_ODBIOR_OSOBISTY_PUNKT%'");
        }

        reset($_POST['PARAMETRY']);

        foreach ( $_POST['PARAMETRY'] as $key => $value ) {

          if (is_array($value)) $value = implode(";", (array)$value);$value = str_replace("\r\n",", ", (string)$value);

          if ( $_POST['KLASA'] == 'wysylka_odbior_osobisty' ) {

            if ( strpos((string)$key, 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT' ) !== false && $value != '' ) {
                $pola = array(
                    array('modul_id',$_POST['id']),
                    array('nazwa','Punkt odbioru'),
                    array('kod',$key),
                    array('sortowanie','99'),
                    array('wartosc',$filtr->process($value)));
                
                $db->insert_query('modules_shipping_params' , $pola);

            } else {
                  if ( $key != 'WYSYLKA_GABARYT') {
                      $pola = array(
                              array('wartosc',($value != '0' ? $filtr->process($value) : '')));
                              
                  } else {
                      $pola = array(
                              array('wartosc',$filtr->process($value)));
                              
                  }
                  $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");
            }
          } else {
              if ( $key != 'WYSYLKA_GABARYT') {
                  $pola = array(
                          array('wartosc',($value != '0' ? $filtr->process($value) : '')));
                          
              } else {
                  $pola = array(
                          array('wartosc',$filtr->process($value)));
                          
              }
              $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");
          }
          unset($pola);
        }

        if ( isset($_POST['DOSTEPNOSC']) ) {
            $dostepnosc_wysylki = $_POST['DOSTEPNOSC']['od_dzien'].'-'.$_POST['DOSTEPNOSC']['od_godzina'].':'.$_POST['DOSTEPNOSC']['do_dzien'].'-'.$_POST['DOSTEPNOSC']['do_godzina'];
            $pola = array(
                    array('wartosc',$dostepnosc_wysylki));
                    
            $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_DOSTEPNOSC'");	
            unset($pola);

        }
        
        if ( isset($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY']) ) {
          
            switch ($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY']) {
              case '1':
                $pola = array(
                        array('wartosc',$filtr->process($_POST['parametry_stale_przedzial']['0']).':'.$filtr->process($_POST['parametry_stale_wartosc']['0'])));
                        
                $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
                unset($pola);
                break;
              case '2':
                $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_waga_przedzial'], $_POST['parametry_waga_wartosc']);
                foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                    unset($koszt_wysylki[$key]);
                }
                $pola = array(
                        array('wartosc',implode(";", (array)$koszt_wysylki)));
                        
                $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
                unset($pola);
                break;
              case '3':
                $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_cena_przedzial'], $_POST['parametry_cena_wartosc']);
                foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                    unset($koszt_wysylki[$key]);
                }
                $pola = array(
                        array('wartosc',implode(";", (array)$koszt_wysylki)));
                        
                $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
                unset($pola);
                break;
              case '4':
                $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_sztuki_przedzial'], $_POST['parametry_sztuki_wartosc']);
                foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                    unset($koszt_wysylki[$key]);
                }            
                $pola = array(
                        array('wartosc',implode(";", (array)$koszt_wysylki)));
                        
                $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
                unset($pola);
                break;
              case '5':
                if ( isset($_POST['parametry_paczkomaty_d_wartosc']) ) {
                     //
                     $koszty_wysylki = 'GABARYT;A:' . (((float)$_POST['parametry_paczkomaty_a_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_a_wartosc']) : $gabarytKurierInpost[0]) . 
                                              ';B:' . (((float)$_POST['parametry_paczkomaty_b_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_b_wartosc']) : $gabarytKurierInpost[1]) . 
                                              ';C:' . (((float)$_POST['parametry_paczkomaty_c_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_c_wartosc']) : $gabarytKurierInpost[2]) .
                                              ';D:' . (((float)$_POST['parametry_paczkomaty_d_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_d_wartosc']) : $gabarytKurierInpost[3]);
                } else {
                     //
                     $koszty_wysylki = 'GABARYT;A:' . (((float)$_POST['parametry_paczkomaty_a_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_a_wartosc']) : $gabarytPaczkomat[0]) . 
                                              ';B:' . (((float)$_POST['parametry_paczkomaty_b_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_b_wartosc']) : $gabarytPaczkomat[1]) . 
                                              ';C:' . (((float)$_POST['parametry_paczkomaty_c_wartosc'] > 0) ? $filtr->process($_POST['parametry_paczkomaty_c_wartosc']) : $gabarytPaczkomat[2]);
                }
                
                $pola = array(
                        array('wartosc',$koszty_wysylki));
                        
                $db->update_query('modules_shipping_params' , $pola, " modul_id = '" . (int)$_POST["id"] . "' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
                unset($pola);
                break;            
            }

        }
        //
        Funkcje::PrzekierowanieURL('wysylka.php?id_poz='.(int)$_POST["id"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
          <script type="text/javascript" src="javascript/jquery.application.js"></script>
          <script type="text/javascript" src="moduly/moduly.js"></script>

          <script>
          $(document).ready(function() {
            $("#modulyForm").validate({
              rules: {
                NAZWA: {
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

          <form action="moduly/wysylka_edytuj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_shipping WHERE id = '" . (int)$_GET['id_poz'] . "'";
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
                      <input type="text" name="NAZWA" size="73" value="<?php echo $info['nazwa']; ?>" id="nazwa" /><em class="TipIkona"><b>Robocza nazwa widoczna w panelu administracyjnym sklepu</b></em>
                    </p>

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',200, \'\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                
                    <div class="info_tab_content">
                    
                      <?php
                      $tlumaczenie = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_TYTUL';
                      $objasnienie = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_OBJASNIENIE';
                      $informacja  = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_INFORMACJA';

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
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_0"><?php echo (isset($nazwa['translate_value']) ? $nazwa['translate_value'] : ''); ?></textarea>
                            <?php } else { ?>
                              <label id="NAZWA_<?php echo $w; ?>">Treść wyświetlana w sklepie:</label>
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
                          unset($zapytanie_jezyk, $sqls);

                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$informacja."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $inform = $sqls->fetch_assoc();   
                          if ( count((array)$inform) > 0 ) {
                              $id_tlumaczenia_tekst_info = $inform['translate_constant_id'];
                          }
                          ?>

                          <p>
                            <label for="INFORMACJA_<?php echo $w; ?>">Informacja wysyłana w treści wiadomości email po złożeniu zamówienia:</label>
                            <div class="odlegloscRwdEdytor" style="margin-top:-55px">
                                <textarea cols="60" rows="30" id="edytor_<?php echo $w; ?>" name="INFORMACJA_<?php echo $w; ?>"><?php echo (isset($inform['translate_value']) ? $inform['translate_value'] : ''); ?></textarea>
                            </div>
                          </p> 

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk, $sqls);
                          ?>
                        </div>
                        <?php
                      }
                      ?>
                      
                    </div>

                    <input type="hidden" name="ID_TLUMACZENIA" value="<?php echo (isset($id_tlumaczenia) ? $id_tlumaczenia : ''); ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_OBJASNIENIA" value="<?php echo (isset($id_tlumaczenia_objasnienia) ? $id_tlumaczenia_objasnienia : ''); ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_INFORMACJI" value="<?php echo (isset($id_tlumaczenia_tekst_info) ? $id_tlumaczenia_tekst_info : ''); ?>" />

                    <p>
                      <label class="required" for="sort">Kolejność wyswietlania:</label>
                      <input type="text" name="SORT" size="5" value="<?php echo $info['sortowanie']; ?>" id="sort" class="calkowita" />                      
                    </p>
                    
                    <div class="maleInfo odlegloscRwdDiv">Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania.</div>                    

                    <p>
                      <label>Status:</label>
                      <input type="radio" value="1" name="STATUS" id="status_tak" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_tak">włączony</label>
                      <input type="radio" value="0" name="STATUS" id="status_nie" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_nie">wyłączony</label>
                    </p>

                    <?php

                    $zapytanie_parametry = "SELECT * FROM modules_shipping_params WHERE modul_id = '" . (int)$_GET['id_poz'] . "' ORDER BY sortowanie";
                    $sql_parametry = $db->open_query($zapytanie_parametry);
                                
                    if ((int)$db->ile_rekordow($sql_parametry) > 0) {
                    
                      $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                    
                      while ( $info_parametry = $sql_parametry->fetch_assoc() ) {

                        if ( $info_parametry['kod'] == 'WYSYLKA_GABARYT' ) {

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="1" id="gabaryt_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '1') ? 'checked="checked"' : '').' /><label class="OpisFor" for="gabaryt_tak">tak<em class="TipIkona"><b>Czy wysyłka ma być dostępna dla produktów gabarytowych</b></em></label>'  . "\n";
                          echo '<input type="radio" value="0" id="gabaryt_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '0') ? 'checked="checked"' : '').' /><label class="OpisFor" for="gabaryt_nie">nie<em class="TipIkona"><b>Czy wysyłka ma być nie dostępna dla produktów gabarytowych</b></em></label>'  . "\n";
                          echo '</p>' . "\n";

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_DOSTEPNOSC' ) {
                        
                          $Przedzial = array();
                          $PrzedzialStart = array();
                          $PrzedzialKoniec = array();
                          $Przedzial = explode(':', (string)$info_parametry['wartosc']);
                          $PrzedzialStart = explode('-', (string)$Przedzial['0']);
                          $PrzedzialKoniec = explode('-', (string)$Przedzial['1']);
                          $tablica = array();
                          $tablica[] = array('id' => '1', 'text' => 'poniedziałek');
                          $tablica[] = array('id' => '2', 'text' => 'wtorek');
                          $tablica[] = array('id' => '3', 'text' => 'środa');
                          $tablica[] = array('id' => '4', 'text' => 'czwartek');
                          $tablica[] = array('id' => '5', 'text' => 'piątek');
                          $tablica[] = array('id' => '6', 'text' => 'sobota');
                          $tablica[] = array('id' => '0', 'text' => 'niedziela');

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";

                          echo 'od: <select name="DOSTEPNOSC[od_dzien]">' . "\n";
                          foreach ($tablica as $dzien) { 
                            $chec = '';
                            if ($PrzedzialStart['0'] == $dzien['id']) { 
                               $chec = ' selected="selected"';
                            }
                            echo '<option value="'.$dzien['id'].'"'.$chec.'>'.$dzien['text'].'</option>'; 
                          } 
                          echo '</select>' . "\n";
                          echo ' : <select name="DOSTEPNOSC[od_godzina]">' . "\n";
                          for ($c = 0;$c < 24; $c++) { 
                            $chec = '';
                            if ($PrzedzialStart['1'] == $c) { 
                               $chec = ' selected="selected"';
                            }
                            echo '<option value="'.$c.'"'.$chec.'>'.$c.'</option>'; 
                          } 
                          echo '</select>' . "\n";

                          echo ' do: <select name="DOSTEPNOSC[do_dzien]">' . "\n";
                          foreach ($tablica as $dzien) { 
                            $chec = '';
                            if ($PrzedzialKoniec['0'] == $dzien['id']) { 
                               $chec = ' selected="selected"';
                            }
                            echo '<option value="'.$dzien['id'].'"'.$chec.'>'.$dzien['text'].'</option>'; 
                          } 
                          echo '</select>' . "\n";
                          echo ' : <select name="DOSTEPNOSC[do_godzina]">' . "\n";
                          for ($c = 0;$c < 24; $c++) { 
                            $chec = '';
                            if ($PrzedzialKoniec['1'] == $c) { 
                               $chec = ' selected="selected"';
                            }
                            echo '<option value="'.$c.'"'.$chec.'>'.$c.'</option>'; 
                          } 
                          echo '</select>' . "\n";

                          echo '<em class="TipIkona"><b>Przedział czasowy w jakim wysyłka będzie dostępna w sklepie</b></em>' . "\n";
                          echo '</p>' . "\n";
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_IKONA' ) {

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
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_DARMOWA_PROMOCJE' ) {

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="tak" id="darmowa_promocje_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /><label class="OpisFor" for="darmowa_promocje_tak">tak<em class="TipIkona"><b>Do obliczania wartości zamówienia dla darmowej wysyłki będą uwzględniane produkty będące w promocji</b></em></label>'  . "\n";
                          echo '<input type="radio" value="nie" id="darmowa_promocje_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="darmowa_promocje_nie">nie<em class="TipIkona"><b>Do obliczania wartości zamówienia dla darmowej wysyłki nie będą uwzględniane produkty będące w promocji</b></em></label>'  . "\n";
                          echo '</p>' . "\n";  
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_DARMOWA_WYKLUCZONA' ) {

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="tak" id="darmowa_wykluczona_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /><label class="OpisFor" for="darmowa_wykluczona_tak">tak<em class="TipIkona"><b>Jeżeli do koszyka będą dodane produkty z ustawioną darmową wysyłką - dla tej formy wysyłki będzie obliczany normalny koszt wysyłki (nie będzie zerowany)</b></em></label>'  . "\n";
                          echo '<input type="radio" value="nie" id="darmowa_wykluczona_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="darmowa_wykluczona_nie">nie<em class="TipIkona"><b>Jeżeli do koszyka będą dodane produkty z ustawioną darmową wysyłką - koszt wysyłki będzie wynosił 0</b></em></label>'  . "\n";
                          echo '</p>' . "\n";                            

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_MAKSYMALNA_WAGA_TRYB' ) {

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="wylacz" id="wysylka_maksymalna_waga_tryb_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'wylacz') ? 'checked="checked"' : '').' /><label class="OpisFor" for="wysylka_maksymalna_waga_tryb_tak">wyłącz wysyłkę<em class="TipIkona"><b>Jeżeli waga zamówienia przekracza maksymalną wagę wysyłki to przesyłka nie będzie dostępna</b></em></label>'  . "\n";
                          echo '<input type="radio" value="paczki" id="wysylka_maksymalna_waga_tryb_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'paczki') ? 'checked="checked"' : '').' /><label class="OpisFor" for="wysylka_maksymalna_waga_tryb_nie">podziel na paczki<em class="TipIkona"><b>Jeżeli waga zamówienia przekracza maksymalną wagę wysyłki sklep obliczy koszty wysyłki jako kolejne paczki</b></em></label>'  . "\n";
                          echo '</p>' . "\n";  

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK' ) {

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="tak" id="wysylka_darmowa_paczki_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /><label class="OpisFor" for="wysylka_darmowa_paczki_tak">tak<em class="TipIkona"><b>Darmowa wysyłka powyżej określonej kwoty niezależnie od ilości paczek</b></em></label>'  . "\n";
                          echo '<input type="radio" value="nie" id="wysylka_darmowa_paczki_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="wysylka_darmowa_paczki_nie">nie<em class="TipIkona"><b>Darmowa wysyłka powyżej określonej kwoty nie będzie obowiązywała jeżeli będzie więcej niż jedna paczka</b></em></label>'  . "\n";
                          echo '</p>' . "\n";                            
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_STAWKA_VAT' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="vat">Podatek VAT:</label>' . "\n";
                          
                          $vat = Produkty::TablicaStawekVat('', true, true);
                          $domyslny_vat = $vat[1];
                          $podzial_vat = explode('|', (string)$info_parametry['wartosc']);
                          
                          if (count($podzial_vat) == 2) {
                              //
                              $domyslny_vat = $info_parametry['wartosc'];
                              //
                          }            

                          echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $vat[0], $domyslny_vat, 'id="vat"') . "\n";                          
                          echo '</p>' . "\n";
                          
                          unset($vat, $domyslny_vat, $podzial_vat);

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KOD_GTU' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="gtu">Kod GTU:</label>' . "\n";
                          
                          $tablica = array();
                          $tablica[] = array('id' => '', 'text' => '-- brak --');
                          $tablica[] = array('id' => 'GTU 01', 'text' => 'GTU 01 - Napoje alkoholowe');
                          $tablica[] = array('id' => 'GTU 02', 'text' => 'GTU 02 - Paliwa');
                          $tablica[] = array('id' => 'GTU 03', 'text' => 'GTU 03 - Oleje opałowe i oleje smarowe');
                          $tablica[] = array('id' => 'GTU 04', 'text' => 'GTU 04 - Wyroby tytoniowe');
                          $tablica[] = array('id' => 'GTU 05', 'text' => 'GTU 05 - Odpady');
                          $tablica[] = array('id' => 'GTU 06', 'text' => 'GTU 06 - Urządzenia elektroniczne oraz części i materiałów do nich');
                          $tablica[] = array('id' => 'GTU 07', 'text' => 'GTU 07 - Pojazdy oraz części samochodowe');
                          $tablica[] = array('id' => 'GTU 08', 'text' => 'GTU 08 - Metale szlachetne oraz nieszlachetne');
                          $tablica[] = array('id' => 'GTU 09', 'text' => 'GTU 09 - Leki oraz wyroby medyczne');
                          $tablica[] = array('id' => 'GTU 10', 'text' => 'GTU 10 - Budynki, budowle i grunty');
                          $tablica[] = array('id' => 'GTU 11', 'text' => 'GTU 11 - Obrót uprawnieniami do emisji gazów cieplarnianych');
                          $tablica[] = array('id' => 'GTU 12', 'text' => 'GTU 12 - Usługi niematerialne m.in. marketingowe, reklamowe');
                          $tablica[] = array('id' => 'GTU 13', 'text' => 'GTU 13 - Usługi transportowe i gospodarki magazynowej'); 
                          
                          echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'], 'id="gtu"') . "\n"; 
                         
                          echo '</p>' . "\n";
                          
                          unset($vat, $domyslny_vat, $podzial_vat);
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_RODZAJ_OPLATY' ) {

                            if ( $info['klasa'] != 'wysylka_indywidualna' ) {
                                  $tablica_oplat = array();
                                  $rodzaj_oplaty = $info_parametry['wartosc'];

                                  $tablica_oplat[] = array('id' => 1, 'text' => 'Opłata stała');
                                  $tablica_oplat[] = array('id' => 2, 'text' => 'Opłata zależna od wagi zamówienia');
                                  $tablica_oplat[] = array('id' => 3, 'text' => 'Opłata zależna od wartości zamówienia');
                                  $tablica_oplat[] = array('id' => 4, 'text' => 'Opłata zależna od ilości produktów');
                                  
                                  if ( $info['klasa'] == 'wysylka_inpost_weekend' || $info['klasa'] == 'wysylka_inpost_international' || $info['klasa'] == 'wysylka_inpost_eko' || $info['klasa'] == 'wysylka_inpost' || $info['klasa'] == 'wysylka_inpost_kurier' ) {
                                       $tablica_oplat[] = array('id' => 5, 'text' => 'Wg rozmiaru paczki');
                                  }

                                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />' . "\n";
                                  echo '<p>' . "\n";
                                  echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                                  echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica_oplat, $info_parametry['wartosc'], ' id="'.$info_parametry['kod'].'" onclick="zmien_pola()"') . "\n";
                                  echo '</p>' . "\n";
                                  unset($tablica_oplat);
                            }

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_GRUPA_KLIENTOW' ) {

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />' . "\n";

                          //
                          echo '<div>' . "\n";
                          echo '<table class="WyborCheckbox"><tr>' . "\n";

                          echo '<td><label>'.$info_parametry['nazwa'].':</label></td>' . "\n";
                          echo '<td>' . "\n";
                          foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                              echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" id="grupa_1_' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array((string)$GrupaKlienta['id'], explode(';', (string)$info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="grupa_1_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />' . "\n";
                          }               
                          echo '</td>' . "\n";

                          echo '</tr></table>' . "\n";
                          echo '</div>' . "\n";
                          
                          echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE' ) {

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />' . "\n";

                          //
                          echo '<div>' . "\n";
                          echo '<table class="WyborCheckbox"><tr>' . "\n";

                          echo '<td><label>'.$info_parametry['nazwa'].':</label></td>' . "\n";
                          echo '<td>' . "\n";
                          foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                              echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" id="grupa_2_' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array((string)$GrupaKlienta['id'], explode(';', (string)$info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="grupa_2_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />' . "\n";
                          }               
                          echo '</td>' . "\n";

                          echo '</tr></table>' . "\n";
                          echo '</div>' . "\n";
                          
                          echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KRAJE_DOSTAWY' ) {

                          $tablica_krajow = explode(';', (string)$info_parametry['wartosc']);

                          $zapytanie_kraje = "SELECT DISTINCT c.countries_iso_code_2, cd.countries_name  
                                              FROM countries c
                                              LEFT JOIN countries_description cd ON c.countries_id = cd. countries_id AND cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                              ORDER BY cd.countries_name";
                          $sqlc = $db->open_query($zapytanie_kraje);
                          //

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />' . "\n";
                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<select name="PARAMETRY['.$info_parametry['kod'].'][]" multiple="multiple" id="multipleHeaders">';

                          while ($infc = $sqlc->fetch_assoc()) { 
                            $wybrany = '';
                            if ( in_array((string)$infc['countries_iso_code_2'], $tablica_krajow ) ) {
                              $wybrany = 'selected="selected"';
                            }
                            echo '<option value="'.$infc['countries_iso_code_2'].'" '.$wybrany.'>'.$infc['countries_name'].'</option>';
                          }
                          echo '</select>' . "\n";
                          echo '</p>' . "\n";
                          
                          echo '<div class="ostrzezenie odlegloscRwdTab BrakMarginesuRwd" style="margin-top:10px; margin-bottom:10px">Do wysyłki musi być przypisany minimum jednen kraj.</div>';
                          
                          $db->close_query($sqlc);
                          unset($zapytanie_kraje, $infc);
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_DOSTEPNE_PLATNOSCI' ) {

                          $tablica_platnosci = explode(';', (string)$info_parametry['wartosc']);

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />' . "\n";
                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";

                          $wszystkie_platnosci_tmp = Array();
                          $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciId();

                          echo '<select name="PARAMETRY['.$info_parametry['kod'].'][]" multiple="multiple" id="multipleHeaders1">';
                          foreach ( $wszystkie_platnosci_tmp as $value ) {
                            $wybrany = '';
                            if ( in_array((string)$value['id'], $tablica_platnosci ) ) {
                              $wybrany = 'selected="selected"';
                            }
                            echo '<option value="'.$value['id'].'" '.$wybrany.' >'.$value['text'].'</option>';
                          }
                          echo '</select>' . "\n";
                          echo '</p>' . "\n";
                          
                          echo '<div class="ostrzezenie odlegloscRwdTab BrakMarginesuRwd" style="margin-top:10px; margin-bottom:10px">Do wysyłki musi być przypisana minimum jedna forma płatności.</div>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KOSZT_WYSYLKI' ) {

                            if ( $info['klasa'] != 'wysylka_indywidualna' ) {
                                  $tablica_kosztow = explode(';', (string)$info_parametry['wartosc']);
                                  
                                  echo '<div>' . "\n";
                                  echo '<label style="margin-left:10px;margin-top:5px;">'.$info_parametry['nazwa'].' (brutto):</label>' . "\n";
                                  echo '</div>' . "\n";

                                  // koszty stale
                                  echo '<div id="kosztyStale" class="RodzajKosztow" '.($rodzaj_oplaty != '1' ? 'style="display:none"' : '').'>';
                                  if ( $rodzaj_oplaty == '1' ) {
                                    $koszt = explode(':', (string)$tablica_kosztow['0']);
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="stale1"><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />';
                                    echo '<input class="kropka" type="text" name="parametry_stale_wartosc[]" value="'.((isset($koszt['1'])) ? $koszt['1'] : '').'" /></div>';
                                  } else {
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="stale1"><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />';
                                    echo '<input class="kropka" type="text" name="parametry_stale_wartosc[]" value="0" /></div>';
                                  }
                                  echo '</div>';

                                  // koszty zalezne od wagi zamowienia
                                  echo '<div id="kosztyWaga" class="RodzajKosztow" '.($rodzaj_oplaty != '2' ? 'style="display:none"' : '').'>' . "\n";
                                  if ( $rodzaj_oplaty == '2' ) {
                                    for ( $i = 0, $c = count($tablica_kosztow); $i < $c; $i++ ) {
                                      $idDiv = $i+1;
                                      $koszt = explode(':', (string)$tablica_kosztow[$i]);
                                      echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="waga'.$idDiv.'">do &nbsp; <input class="Waga" type="text" size="10" name="parametry_waga_przedzial[]" value="'.((isset($koszt['0'])) ? number_format((float)$koszt['0'],4,'.','') : '').'" /> kg &nbsp; ' . "\n";
                                      echo '<input class="kropka" type="text" name="parametry_waga_wartosc[]" value="'.((isset($koszt['1'])) ? number_format((float)$koszt['1'],2,'.','') : '').'" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>' . "\n";
                                    }
                                  } else {
                                      echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="waga1">do &nbsp; <input class="Waga" type="text" size="10" name="parametry_waga_przedzial[]" value="0" /> kg &nbsp; ' . "\n";
                                      echo '<input class="kropka" type="text" name="parametry_waga_wartosc[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>' . "\n";
                                  }
                                  echo '<div class="odlegloscRwdTab" style="padding-top:10px">' . "\n";
                                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyWaga\',\'waga\', \'kg\', \'do\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\' )" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyWaga\',\'waga\')" style="cursor:pointer; '.(count($tablica_kosztow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>' . "\n";
                                  echo '</div>' . "\n";

                                  echo '</div>' . "\n";

                                  // koszty zalezne od wartosci zamowienia
                                  echo '<div id="kosztyCena" class="RodzajKosztow" '.($rodzaj_oplaty != '3' ? 'style="display:none"' : '').'>' . "\n";
                                  if ( $rodzaj_oplaty == '3' ) {
                                    for ( $i = 0, $c = count($tablica_kosztow); $i < $c; $i++ ) {
                                      $idDiv = $i+1;
                                      $koszt = explode(':', (string)$tablica_kosztow[$i]);
                                      echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="cena'.$idDiv.'">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_cena_przedzial[]" value="'.((isset($koszt['0'])) ? $koszt['0'] : '').'" /> ' . $_SESSION['domyslna_waluta']['symbol'] . ' &nbsp; ' . "\n";
                                      echo '<input class="kropka" type="text" name="parametry_cena_wartosc[]" value="'.((isset($koszt['1'])) ? number_format((float)$koszt['1'],2,'.','') : '').'" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>' . "\n";
                                    }
                                  } else {
                                      echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="cena1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_cena_przedzial[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . ' &nbsp; ' . "\n";
                                      echo '<input class="kropka" type="text" name="parametry_cena_wartosc[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>' . "\n";
                                  }
                                  echo '<div class="odlegloscRwdTab" style="padding-top:10px">' . "\n";
                                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyCena\',\'cena\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\', \'do\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyCena\',\'cena\')" style="cursor:pointer; '.(count($tablica_kosztow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>' . "\n";
                                  echo '</div>' . "\n";

                                  echo '</div>' . "\n";

                                  // koszty zalezne od ilosci sztuk produktow
                                  echo '<div id="kosztySztuki" class="RodzajKosztow" '.($rodzaj_oplaty != '4' ? 'style="display:none"' : '').'>' . "\n";
                                  if ( $rodzaj_oplaty == '4' ) {
                                    for ( $i = 0, $c = count($tablica_kosztow); $i < $c; $i++ ) {
                                      $idDiv = $i+1;
                                      $koszt = explode(':', (string)$tablica_kosztow[$i]);
                                      echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="sztuki'.$idDiv.'">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_sztuki_przedzial[]" value="'.((isset($koszt['0'])) ? $koszt['0'] : '').'" /> szt. &nbsp; ' . "\n";
                                      echo '<input class="kropka" type="text" name="parametry_sztuki_wartosc[]" value="'.((isset($koszt['1'])) ? number_format((float)$koszt['1'],2,'.','') : '').'" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>' . "\n";
                                    }
                                  } else {
                                      echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="sztuki1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_sztuki_przedzial[]" value="0" /> szt. &nbsp; ' . "\n";
                                      echo '<input class="kropka" type="text" name="parametry_sztuki_wartosc[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>' . "\n";
                                  }
                                  echo '<div class="odlegloscRwdTab" style="padding-top:10px">' . "\n";
                                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztySztuki\',\'sztuki\', \'szt.\', \'do\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztySztuki\',\'sztuki\')" style="cursor:pointer; '.(count($tablica_kosztow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>' . "\n";
                                  echo '</div>' . "\n";
                                  
                                  echo '</div>' . "\n";
                                  
                                  // koszty wg gabarytu
                                  echo '<div id="kosztyGabaryt" class="RodzajKosztow" '.($rodzaj_oplaty != '5' ? 'style="display:none"' : '').'>';

                                  if ( $rodzaj_oplaty == '5' ) {
                                    // gabaryt a
                                    if ( isset($tablica_kosztow['1']) ) {
                                         $koszt = explode(':', (string)$tablica_kosztow['1']);
                                    }
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt A</div>';
                                    echo '<input class="kropka" type="text" name="parametry_paczkomaty_a_wartosc" value="' . ((isset($tablica_kosztow['1']) && isset($koszt['1']) && $koszt['1'] > 0) ? $koszt['1'] : (($info['klasa'] == 'wysylka_inpost_kurier') ? $gabarytKurierInpost[0] : $gabarytPaczkomat[0]) ) . '" /></div>';
                                    // gabaryt b
                                    if ( isset($tablica_kosztow['2']) ) {
                                         $koszt = explode(':', (string)$tablica_kosztow['2']);
                                    }
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt B</div>';
                                    echo '<input class="kropka" type="text" name="parametry_paczkomaty_b_wartosc" value="' . ((isset($tablica_kosztow['2']) && isset($koszt['1']) && $koszt['1'] > 0) ? $koszt['1'] : (($info['klasa'] == 'wysylka_inpost_kurier') ? $gabarytKurierInpost[1] : $gabarytPaczkomat[1]) ) . '" /></div>';
                                    // gabaryt c
                                    if ( isset($tablica_kosztow['3']) ) {
                                         $koszt = explode(':', (string)$tablica_kosztow['3']);
                                    }
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt C</div>';
                                    echo '<input class="kropka" type="text" name="parametry_paczkomaty_c_wartosc" value="' . ((isset($tablica_kosztow['3']) && isset($koszt['1']) && $koszt['1'] > 0) ? $koszt['1'] : (($info['klasa'] == 'wysylka_inpost_kurier') ? $gabarytKurierInpost[2] : $gabarytPaczkomat[2]) ) . '" /></div>';
                                    //
                                    if ( $info['klasa'] == 'wysylka_inpost_kurier' ) {
                                         // gabaryt d
                                         if ( isset($tablica_kosztow['4']) ) {
                                              $koszt = explode(':', (string)$tablica_kosztow['4']);
                                         }
                                         echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt D</div>';
                                         echo '<input class="kropka" type="text" name="parametry_paczkomaty_d_wartosc" value="' . ((isset($tablica_kosztow['4']) && isset($koszt['1']) && $koszt['1'] > 0) ? $koszt['1'] : $gabarytKurierInpost[3] ) . '" /></div>';
                                         //                                   
                                    }
                                    //
                                    if ( $info['klasa'] == 'wysylka_inpost_kurier' ) {
                                         echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli paczka przekroczy rozmiar gabarytu D to wysyłka paczkomatami nie będzie dostępna.</div>' . "\n";
                                    } else {
                                         echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli paczka przekroczy rozmiar gabarytu C to wysyłka paczkomatami nie będzie dostępna.</div>' . "\n";
                                    }
                                    //
                                  } else {
                                    // gabaryt a
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt A</div>';
                                    echo '<input class="kropka" type="text" name="parametry_paczkomaty_a_wartosc" value="' . (($info['klasa'] == 'wysylka_inpost_kurier') ? $gabarytKurierInpost[0] : $gabarytPaczkomat[0]) . '" /></div>';
                                    // gabaryt b
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt B</div>';
                                    echo '<input class="kropka" type="text" name="parametry_paczkomaty_b_wartosc" value="' . (($info['klasa'] == 'wysylka_inpost_kurier') ? $gabarytKurierInpost[1] : $gabarytPaczkomat[1]) . '" /></div>';
                                    // gabaryt c
                                    echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt C</div>';
                                    echo '<input class="kropka" type="text" name="parametry_paczkomaty_c_wartosc" value="' . (($info['klasa'] == 'wysylka_inpost_kurier') ? $gabarytKurierInpost[2] : $gabarytPaczkomat[2]) . '" /></div>';
                                    //
                                    if ( $info['klasa'] == 'wysylka_inpost_kurier' ) {
                                         // gabaryt d
                                         echo '<div class="odlegloscRwdTab" style="padding-bottom:6px"><div style="width:65px;display:inline-block">Gabaryt D</div>';
                                         echo '<input class="kropka" type="text" name="parametry_paczkomaty_d_wartosc" value="' . $gabarytKurierInpost[3] . '" /></div>';                                    
                                    }
                                    //
                                    if ( $info['klasa'] == 'wysylka_inpost_kurier' ) {
                                         echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli paczka przekroczy rozmiar gabarytu D to wysyłka paczkomatami nie będzie dostępna.</div>' . "\n";
                                    } else {
                                         echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli paczka przekroczy rozmiar gabarytu C to wysyłka paczkomatami nie będzie dostępna.</div>' . "\n";
                                    }
                                    //
                                  }
                                  echo '</div>';                                  
                                  
                                  echo '<div class="maleInfo odlegloscRwdDiv" style="margin-top:10px">Koszty wysyłek należy podawać w kwotach brutto.</div>' . "\n";
                                  
                            } else {
                            
                                  echo '<div><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />' . "\n";
                                  echo '<input type="hidden" name="parametry_stale_wartosc[]" value="0" /></div>' . "\n";
                                  
                            }
                            
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1' ) {

                          $zapytanie_punkty = "SELECT * FROM modules_shipping_params WHERE modul_id = '" . (int)$_GET['id_poz'] . "' AND kod like '%WYSYLKA_ODBIOR_OSOBISTY_PUNKT%'";
                          $sql_punkty = $db->open_query($zapytanie_punkty);
                          $aktualna_ilosc_punktow = (int)$db->ile_rekordow($sql_punkty);

                          echo '<div id="PunktyOdbioru">';
                          $wymagany = false;
                          while ( $info_punkty = $sql_punkty->fetch_assoc() ) {

                              if ( $info_punkty['kod'] == 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1' ) {
                                  $wymagany = true;
                              }
                              echo '<p id="'.$info_punkty['kod'].'">' . "\n";
                              echo '<label for="PARAMETRY['.$info_punkty['kod'].']">'.$info_punkty['nazwa'].':</label>' . "\n";
                              echo '<textarea cols="80" rows="3" name="PARAMETRY['.$info_punkty['kod'].']" id="PARAMETRY['.$info_punkty['kod'].']" '.($wymagany ? 'class="required"' : '').'>'.$info_punkty['wartosc'].'</textarea>' . "\n";
                              echo '</p>' . "\n";
                              $wymagany = false;

                          }
                          
                          $db->close_query($sql_punkty);
                          unset($zapytanie_punkty, $info_punkty);                          

                          echo '</div>';
                          ?>

                          <div style="padding:10px;padding-top:20px;padding-left:20px;">
                            <span class="dodaj" onclick="dodaj_punkt('PunktyOdbioru', 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT')" style="cursor:pointer">dodaj kolejny punkt</span>
                            &nbsp;&nbsp;<span class="usun" onclick="usun_punkt('PunktyOdbioru', 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT')" style="cursor:pointer;">usuń punkt odbioru</span>

                          </div>

                          <?php
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_TOKEN_INPOST' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>';
                          echo '<textarea cols="80" rows="4" name="PARAMETRY['.$info_parametry['kod'].']" id="'.$info_parametry['kod'].'">'.$info_parametry['wartosc'].'</textarea>';
                          echo '<em class="TipIkona"><b>Token dostępny w menadżerze wysyłek w Inpost - wymagany do widgetu 5.0</b></em>' . "\n";
                          echo '</p>' . "\n";

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_TOKEN_INPOST_INTERNATIONAL' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>';
                          echo '<textarea cols="80" rows="4" name="PARAMETRY['.$info_parametry['kod'].']" id="'.$info_parametry['kod'].'">'.$info_parametry['wartosc'].'</textarea>';
                          echo '<em class="TipIkona"><b>Token dostępny w menadżerze wysyłek w Inpost - wymagany do widgetu 5.0</b></em>' . "\n";
                          echo '</p>' . "\n";

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_API_KEY_DPDPICKUP' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>';
                          echo '<textarea cols="80" rows="2" name="PARAMETRY['.$info_parametry['kod'].']" id="'.$info_parametry['kod'].'">'.$info_parametry['wartosc'].'</textarea>';
                          echo '<em class="TipIkona"><b>Klucz uwierzytelniający do mapy przekazywany jest klientom indywidualnie. Aby uzyskać klucz, należy przesłać zgłoszenie zawierające nazwę domeny, w której będzie używany, na adres itcustomer@dpd.com.pl</b></em>' . "\n";
                          echo '</p>' . "\n";

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_API_KEY_ORLEN' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>';
                          echo '<textarea cols="80" rows="2" name="PARAMETRY['.$info_parametry['kod'].']" id="'.$info_parametry['kod'].'">'.$info_parametry['wartosc'].'</textarea>';
                          echo '<em class="TipIkona"><b>Klucz uwierzytelniający do mapy przekazywany dosepny w serwisie ORLEN</b></em>' . "\n";
                          echo '</p>' . "\n";

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KLUCZ' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>';
                          echo '<textarea cols="80" rows="4" name="PARAMETRY['.$info_parametry['kod'].']" id="'.$info_parametry['kod'].'">'.$info_parametry['wartosc'].'</textarea>';
                          echo '<em class="TipIkona"><b>Należy wygenerować i wpisać klucz API dostępny w serwisie Google - https://developers.google.com/maps/documentation/javascript/get-api-key</b></em>' . "\n";
                          echo '</p>' . "\n";

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KLUCZ_API_BLISKAPACZKA' ) {
                        
                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="text" size="53" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" />' . "\n";
                          echo '<em class="TipIkona"><b>Klucz API dostępny w serwisie Bliskapaczka</b></em>' . "\n";
                          echo '</p>' . "\n";
                          
                        } elseif ( $info_parametry['kod'] == 'PUNKTY_ODBIORU_POCZTA' ) {

                          echo '<p>' . "\n";
                          echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="wszystkie" id="punkty_odbioru_poczta_wszystkie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'wszystkie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="punkty_odbioru_poczta_wszystkie">wszystkie placówki<em class="TipIkona"><b>Wszystkie punkty odbioru: Poczta Polska, Żabka, Społem, Biedronka</b></em></label>'  . "\n";
                          echo '<input type="radio" value="poczta" id="punkty_odbioru_poczta_poczta" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'poczta') ? 'checked="checked"' : '').' /><label class="OpisFor" for="punkty_odbioru_poczta_poczta">tylko placówki pocztowe<em class="TipIkona"><b>Tylko placówki Poczty Polskiej</b></em></label>'  . "\n";
                          echo '</p>' . "\n";     
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_WAGA_WOLUMETRYCZNA' ) {

                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="text" size="10" class="calkowita" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" />' . "\n";
                          echo '<em class="TipIkona"><b>Podzielnik - wartość ze wzoru: długość x szerokość x wysokość ÷ podzielnik</b></em>' . "\n";
                          echo '</p>' . "\n";    
                                              
                          echo '<div class="maleInfo odlegloscRwdDiv">Obliczanie wagi wolumetrycznej jest wykorzystywane jeżeli koszt wysyłki zależny jest od wagi produktu.</div>';      
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_INDYWIDUALNA_KOSZT_PRODUKTY' ) {

                          echo '<p>' . "\n";
                          echo '<label for="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['nazwa'].':</label>' . "\n";
                          echo '<input type="radio" value="tak" id="wysylka_indywidualna_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /><label class="OpisFor" for="wysylka_indywidualna_tak">tak<em class="TipIkona"><b>Jeżeli nie wszystkie produkty w koszyku będą miały wpisany indywidualny koszt wysyłki - wysyłka będzie niedostępna do wyboru</b></em></label>'  . "\n";
                          echo '<input type="radio" value="nie" id="wysylka_indywidualna_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="wysylka_indywidualna_nie">nie<em class="TipIkona"><b>W koszyku będzie wyświetlana tylko ta forma wysyłki jeżeli min 1 produkt będzie miał wpisany indywidualny koszt wysyłki</b></em></label>'  . "\n";
                          echo '</p>' . "\n";    
                                              
                          echo '<div class="maleInfo odlegloscRwdDiv">Jeżeli zostanie wybrana opcja NIE - w koszyku będzie dostępna tylko forma indywidualnego kosztu wysyłki. Inne wysyłki nie będą dostępne. Produkty, które nie będą miały wpisanego indywidualnego kosztu wysyłki będą przyjmowały koszt wysyłki 0. Koszt wysyłki będzie sumą indywidualnych kosztów wysyłek poszczególnych produktów w koszyku.</div>';                          
                       
                        } else {
                        
                          if ( strpos((string)$info_parametry['kod'], 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT' ) === false ) {
                              echo '<p>' . "\n";
                              echo '<label for="'.$info_parametry['kod'].'">'.$info_parametry['nazwa'].':</label>' . "\n";
                              if ( $info_parametry['kod'] == 'WYSYLKA_MAKSYMALNA_WAGA' || $info_parametry['kod'] == 'WYSYLKA_MINIMALNA_WAGA' ) {
                                echo '<input type="text" size="'.($info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ? '80' : '35').'" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" '.($info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ? '' : 'class="Waga"').' /> kg' . "\n";
                              } else {
                                echo '<input type="text" size="'.($info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ? '80' : '35').'" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" '.($info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ? '' : 'class="kropka"').' />' . "\n";
                              }
                              if ( $info_parametry['kod'] == 'WYSYLKA_MAKSYMALNA_WARTOSC' || $info_parametry['kod'] == 'WYSYLKA_MINIMALNA_WARTOSC' || $info_parametry['kod'] == 'WYSYLKA_DARMOWA_WYSYLKA' ) {
                                echo ' ' . $_SESSION['domyslna_waluta']['symbol'];
                              }
                              if ( $info_parametry['kod'] == 'WYSYLKA_DARMOWA_WYSYLKA_WAGA' ) {
                                echo ' kg' . "\n";
                              }                              
                              if ( $info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ) {
                                echo '<em class="TipIkona"><b>Adres url pod którym klient będzie mógł śledzić lokalizację przesyłki, po uzupełnieniu numeru przesyłki w edycji zamówienia</b></em>' . "\n";
                              }
                              if ( $info_parametry['kod'] == 'WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE' ) {
                                echo '<em class="TipIkona"><b>Jeżeli w zamówieniu będzie większa ilość produktów sklep podzieli zamówienie na paczki</b></em>' . "\n";
                              }                          
                              echo '</p>' . "\n";
                          }
                          
                        }
                       
                      }
                      
                      unset($TablicaGrupKlientow);

                    }

                    $db->close_query($sql_parametry);
                    unset($zapytanie_parametry, $info_parametry);

                    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                    
                        ?>
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                        <p>
                          <label class="required">Skrypt:</label>   
                          <input type="text" name="SKRYPT" id="skrypt" size="53" value="<?php echo $info['skrypt']; ?>" onkeyup="updateKeySkrypt();" /><em class="TipIkona"><b>Nazwa skryptu realizującego funkcje modułu</b></em>
                        </p>

                        <p>
                          <label class="required">Nazwa klasy:</label>   
                          <input type="text" name="KLASA" id="klasa" size="53" value="<?php echo $info['klasa']; ?>" onkeyup="updateKeyKlasa();" /><em class="TipIkona"><b>Nazwa klasy realizującej funkcje modułu</b></em>
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
                  <button type="button" class="przyciskNon" onclick="cofnij('wysylka','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>           
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

    </div>    
    
    <?php
    include('stopka.inc.php');

}