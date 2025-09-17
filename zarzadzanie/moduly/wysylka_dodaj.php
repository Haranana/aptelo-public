<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["NAZWA"])),
                array('skrypt',$filtr->process($_POST["SKRYPT"])),
                array('klasa',$filtr->process($_POST["KLASA"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])));
                
        //
        $db->insert_query('modules_shipping' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);


        // aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".$id_dodanej_pozycji."_TYTUL'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.$id_dodanej_pozycji.'_TYTUL'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['NAZWA_'.$w])) {
            
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_'.$w])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id']));
                        
            } else {
            
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id']));
            }
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);

        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".$id_dodanej_pozycji."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.$id_dodanej_pozycji.'_OBJASNIENIE'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['OBJASNIENIE_'.$w])),
                    array('translate_constant_id',$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);


        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".$id_dodanej_pozycji."_INFORMACJA'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.$id_dodanej_pozycji.'_INFORMACJA'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['INFORMACJA_'.$w])),
                    array('translate_constant_id',$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Przesyłka gabarytowa'),
                array('kod','WYSYLKA_GABARYT'),
                array('sortowanie','1'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_GABARYT'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Grafika wysyłki (wyświetlana na stronie koszyka):'),
                array('kod','WYSYLKA_IKONA'),
                array('sortowanie','2'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_IKONA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);      

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Podzielnik dla obliczenia wagi wolumetrycznej:'),
                array('kod','WYSYLKA_WAGA_WOLUMETRYCZNA'),
                array('sortowanie','9'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_WAGA_WOLUMETRYCZNA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);                

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Stawka VAT'),
                array('kod','WYSYLKA_STAWKA_VAT'),
                array('sortowanie','3'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_STAWKA_VAT'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','PKWIU'),
                array('kod','WYSYLKA_PKWIU'),
                array('sortowanie','4'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_PKWIU'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Maksymalna waga przesyłki'),
                array('kod','WYSYLKA_MAKSYMALNA_WAGA'),
                array('sortowanie','5'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MAKSYMALNA_WAGA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Minimalna waga przesyłki'),
                array('kod','WYSYLKA_MINIMALNA_WAGA'),
                array('sortowanie','5'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MINIMALNA_WAGA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Jeżeli waga zamówienia przekracza maksymalną wagę przesyłki to'),
                array('kod','WYSYLKA_MAKSYMALNA_WAGA_TRYB'),
                array('sortowanie','5'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MAKSYMALNA_WAGA_TRYB'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);      

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Darmowa wysyłka niezależnie od ilości paczek'),
                array('kod','WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK'),
                array('sortowanie','7'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);            

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Maksymalna wartość zamówienia'),
                array('kod','WYSYLKA_MAKSYMALNA_WARTOSC'),
                array('sortowanie','6'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MAKSYMALNA_WARTOSC'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Minimalna wartość zamówienia'),
                array('kod','WYSYLKA_MINIMALNA_WARTOSC'),
                array('sortowanie','6'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MINIMALNA_WARTOSC'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Minimalna wartość zamówienia'),
                array('kod','WYSYLKA_MINIMALNA_WARTOSC'),
                array('sortowanie','6'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MINIMALNA_WARTOSC'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Maksymalna ilość produktów w paczce'),
                array('kod','WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'),
                array('sortowanie','6'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Darmowa wysyłka od kwoty'),
                array('kod','WYSYLKA_DARMOWA_WYSYLKA'),
                array('sortowanie','7'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_DARMOWA_WYSYLKA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Darmowa wysyłka do wagi'),
                array('kod','WYSYLKA_DARMOWA_WYSYLKA_WAGA'),
                array('sortowanie','7'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_DARMOWA_WYSYLKA_WAGA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        
        
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Promocje wliczane do darmowej wysyłki'),
                array('kod','WYSYLKA_DARMOWA_PROMOCJE'),
                array('sortowanie','8'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_DARMOWA_PROMOCJE'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);  

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Wyłączona darmowa wysyłka dla produktów ustawionych jako darmowa wysyłka'),
                array('kod','WYSYLKA_DARMOWA_WYKLUCZONA'),
                array('sortowanie','9'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_DARMOWA_WYKLUCZONA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);             

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Rodzaj opłaty'),
                array('kod','WYSYLKA_RODZAJ_OPLATY'),
                array('sortowanie','10'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        switch ($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY']) {
          case '1':
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',$filtr->process($_POST['parametry_stale_przedzial']['0']).':'.$filtr->process($_POST['parametry_stale_wartosc']['0'])));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
          case '2':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_waga_przedzial'], $_POST['parametry_waga_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',implode(";", (array)$koszt_wysylki)));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
          case '3':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_cena_przedzial'], $_POST['parametry_cena_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',implode(";", (array)$koszt_wysylki)));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
          case '4':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_sztuki_przedzial'], $_POST['parametry_sztuki_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',implode(";", (array)$koszt_wysylki)));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
        }

        $grupy_klientow = implode(";", (array)$_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW']);
        $grupy_klientow = str_replace("\r\n",", ", (string)$grupy_klientow);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Dostępna dla grup klientów'),
                array('kod','WYSYLKA_GRUPA_KLIENTOW'),
                array('sortowanie','14'),
                array('wartosc',$grupy_klientow));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);
        
        $grupy_klientow = implode(";", (array)$_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE']);
        $grupy_klientow = str_replace("\r\n",", ", (string)$grupy_klientow);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Niedostępna dla grup klientów'),
                array('kod','WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'),
                array('sortowanie','14'),
                array('wartosc',$grupy_klientow));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        

        $kraje_dostawy = implode(";", (array)$_POST['PARAMETRY']['WYSYLKA_KRAJE_DOSTAWY']);
        $kraje_dostawy = str_replace("\r\n",", ", (string)$kraje_dostawy);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Kraje dostawy'),
                array('kod','WYSYLKA_KRAJE_DOSTAWY'),
                array('sortowanie','20'),
                array('wartosc',$kraje_dostawy));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $dostepne_platnosci = implode(";", (array)$_POST['PARAMETRY']['WYSYLKA_DOSTEPNE_PLATNOSCI']);
        $dostepne_platnosci = str_replace("\r\n",", ", (string)$dostepne_platnosci);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Dostępne płatności'),
                array('kod','WYSYLKA_DOSTEPNE_PLATNOSCI'),
                array('sortowanie','15'),
                array('wartosc',$dostepne_platnosci));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('wysylka.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('wysylka.php');
        }
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="moduly/wysylka_dodaj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                  <p>
                    <label class="required" for="nazwa">Nazwa modułu:</label>
                    <input type="text" name="NAZWA" size="73" value="" id="nazwa" /><em class="TipIkona"><b>Robocza nazwa widoczna w panelu administracyjnym sklepu</b></em>
                  </p>

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',200, \'\')">'.$ile_jezykow[$w]['text'].'</span>'  . "\n";
                }                    
                ?>                   
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                    <?php

                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                          
                      // pobieranie danych jezykowych
                      ?>     
                      <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                      
                          <p>
                             <?php if ($w == '0') { ?>
                              <label class="required" for="nazwa_0">Treść wyświetlana w sklepie:</label>
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_0"></textarea>
                             <?php } else { ?>
                              <label for="nazwa_<?php echo $w; ?>">Treść wyświetlana w sklepie:</label>
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>"></textarea>
                             <?php } ?>
                          </p> 
                                      
                          <p>
                            <label for="objasnienie_<?php echo $w; ?>">Treść objaśnienia w sklepie:</label>
                            <textarea cols="80" rows="3" name="OBJASNIENIE_<?php echo $w; ?>" id="objasnienie_<?php echo $w; ?>"></textarea>
                          </p> 

                          <p>
                            <label for="INFORMACJA_<?php echo $w; ?>">Informacja wysyłana w treści wiadomości email po złożeniu zamówienia:</label>
                            <div class="odlegloscRwdEdytor" style="margin-top:-55px">
                                <textarea cols="60" rows="30" id="edytor_<?php echo $w; ?>" name="INFORMACJA_<?php echo $w; ?>"></textarea>
                            </div>
                          </p> 

                      </div>
                      <?php                    
                       }                    
                       ?>
                </div>

                <p>
                  <label class="required" for="sort">Kolejność wyswietlania:</label>
                  <input type="text" name="SORT" size="5" value="" id="sort" class="calkowita" />
                </p>
                
                <div class="maleInfo odlegloscRwdDiv">Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania.</div> 

                <p>
                  <label>Status:</label>
                  <input type="radio" value="1" name="STATUS" id="status_tak" checked="checked" /><label class="OpisFor" for="status_tak">włączony</label>
                  <input type="radio" value="0" name="STATUS" id="status_nie" /><label class="OpisFor" for="status_nie">wyłączony</label>
                </p>

                <?php
                echo '<p>' . "\n";
                echo '<label>Przesyłka gabarytowa:</label>' . "\n";
                echo '<input type="radio" value="1" id="gabaryt_tak" name="PARAMETRY[WYSYLKA_GABARYT]" /> <label class="OpisFor" for="gabaryt_tak">tak<em class="TipIkona"><b>Czy wysyłka ma być dostępna dla produktów gabarytowych</b></em></label>' . "\n";
                echo '<input type="radio" value="0" id="gabaryt_nie" name="PARAMETRY[WYSYLKA_GABARYT]" checked="checked" /> <label class="OpisFor" for="gabaryt_nie">nie<em class="TipIkona"><b>Czy wysyłka ma być nie dostępna dla produktów gabarytowych</b></em></label>' . "\n";
                echo '</p>' . "\n";
                
                echo '<p>' . "\n";
                echo '<label>Grafika wysyłki (wyświetlana na stronie koszyka):</label>' . "\n";
                echo '<input type="text" size="50" name="PARAMETRY[WYSYLKA_IKONA]" value="" ondblclick="openFileBrowser(\'foto\',\'\',\'' . KATALOG_ZDJEC . '\')" id="foto" autocomplete="off" />' . "\n";
                echo '<em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>' . "\n";
                echo '<span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>' . "\n";
                echo '<span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser(\'foto\',\'\',\'' . KATALOG_ZDJEC . '\')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>' . "\n";
                echo '</p>' . "\n";                 
                
                echo '<p>' . "\n";
                echo '<label for="vat">Podatek VAT:</label>' . "\n";
                
                $vat = Produkty::TablicaStawekVat('', true, true);
                $domyslny_vat = $vat[1];

                echo Funkcje::RozwijaneMenu('PARAMETRY[WYSYLKA_STAWKA_VAT]', $vat[0], $domyslny_vat, 'id="vat"');                          
                echo '</p>' . "\n";
                
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
                
                echo Funkcje::RozwijaneMenu('PARAMETRY[WYSYLKA_KOD_GTU]', $tablica, '', 'id="gtu"') . "\n"; 
                  
                echo '</p>' . "\n";                
                
                unset($vat, $domyslny_vat);                  

                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_PKWIU">PKWIU:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_PKWIU]" value="" id="WYSYLKA_PKWIU" />'  . "\n";
                echo '</p>'  . "\n";

                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_MAKSYMALNA_WAGA">Maksymalna waga przesyłki:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MAKSYMALNA_WAGA]" value="" id="WYSYLKA_MAKSYMALNA_WAGA" class="kropka" /> kg' . "\n";
                echo '</p>'  . "\n";
                
                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_MINIMALNA_WAGA">Minimalna waga przesyłki:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MINIMALNA_WAGA]" value="" id="WYSYLKA_MINIMALNA_WAGA" class="kropka" />  kg' . "\n";
                echo '</p>'  . "\n";                
                
                echo '<p>' . "\n";
                echo '<label for="WYSYLKA_MAKSYMALNA_WAGA_TRYB">Jeżeli waga zamówienia przekracza maksymalną wagę przesyłki to:</label>' . "\n";
                echo '<input type="radio" value="wylacz" id="wysylka_maksymalna_waga_tryb_tak" name="PARAMETRY[WYSYLKA_MAKSYMALNA_WAGA_TRYB]" checked="checked" /><label class="OpisFor" for="wysylka_maksymalna_waga_tryb_tak">wyłącz wysyłkę<em class="TipIkona"><b>Jeżeli waga zamówienia przekracza maksymalną wagę wysyłki to przesyłka nie będzie dostępna</b></em></label>'  . "\n";
                echo '<input type="radio" value="paczki" id="wysylka_maksymalna_waga_tryb_nie" name="PARAMETRY[WYSYLKA_MAKSYMALNA_WAGA_TRYB]" /><label class="OpisFor" for="wysylka_maksymalna_waga_tryb_nie">podziel na paczki<em class="TipIkona"><b>Jeżeli waga zamówienia przekracza maksymalną wagę wysyłki sklep obliczy koszty wysyłki jako kolejne paczki</b></em></label>'  . "\n";
                echo '</p>' . "\n";                   

                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_MAKSYMALNA_WARTOSC">Maksymalna wartość zamówienia:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MAKSYMALNA_WARTOSC]" value="" id="WYSYLKA_MAKSYMALNA_WARTOSC" class="kropka" /> ' . $_SESSION['domyslna_waluta']['symbol'] . "\n";
                echo '</p>'  . "\n";
                
                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_MINIMALNA_WARTOSC">Minimalna wartość zamówienia:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MINIMALNA_WARTOSC]" value="" id="WYSYLKA_MINIMALNA_WARTOSC" class="kropka" /> ' . $_SESSION['domyslna_waluta']['symbol'] . "\n";
                echo '</p>'  . "\n";
                
                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE">Maksymalna ilość produktów w paczce:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE]" value="" id="WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE" class="kropka" /><em class="TipIkona"><b>Jeżeli w zamówieniu będzie większa ilość produktów sklep podzieli zamówienie na paczki</b></em>' . "\n";
                echo '</p>'  . "\n";                

                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_DARMOWA_WYSYLKA">Darmowa wysyłka od kwoty:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_DARMOWA_WYSYLKA]" value="" id="WYSYLKA_DARMOWA_WYSYLKA" class="kropka" /> ' . $_SESSION['domyslna_waluta']['symbol'] . "\n";
                echo '</p>'  . "\n";
                
                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_DARMOWA_WYSYLKA_WAGA">Darmowa wysyłka do wagi:</label>'  . "\n";
                echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_DARMOWA_WYSYLKA_WAGA]" value="" id="WYSYLKA_DARMOWA_WYSYLKA_WAGA" class="kropka" /> kg' . "\n";
                echo '</p>'  . "\n";                

                echo '<p>' . "\n";
                echo '<label for="WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK">Darmowa wysyłka niezależnie od ilości paczek:</label>' . "\n";
                echo '<input type="radio" value="wylacz" id="wysylka_darmowa_wysylka_ile_paczek_tak" name="PARAMETRY[WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK]" checked="checked" /><label class="OpisFor" for="wysylka_darmowa_wysylka_ile_paczek_tak">tak<em class="TipIkona"><b>Darmowa wysyłka powyżej określonej kwoty niezależnie od ilości paczek</b></em></label>' . "\n";
                echo '<input type="radio" value="paczki" id="wysylka_darmowa_wysylka_ile_paczek_nie" name="PARAMETRY[WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK]" /><label class="OpisFor" for="wysylka_darmowa_wysylka_ile_paczek_nie">nie<em class="TipIkona"><b>Darmowa wysyłka powyżej określonej kwoty nie będzie obowiązywała jeżeli będzie więcej niż jedna paczka</b></em></label>' . "\n";
                echo '</p>' . "\n"; 
                
                echo '<p>' . "\n";
                echo '<label for="WYSYLKA_DARMOWA_PROMOCJE">Promocje wliczane do darmowej wysyłki:</label>' . "\n";
                echo '<input type="radio" value="tak" id="darmowa_promocje_tak" name="PARAMETRY[WYSYLKA_DARMOWA_PROMOCJE]" checked="checked" /><label class="OpisFor" for="darmowa_promocje_tak">tak<em class="TipIkona"><b>Do obliczania wartości zamówienia dla darmowej wysyłki będą uwzględniane produkty będące w promocji</b></em></label>'  . "\n";
                echo '<input type="radio" value="nie" id="darmowa_promocje_nie" name="PARAMETRY[WYSYLKA_DARMOWA_PROMOCJE]" /><label class="OpisFor" for="darmowa_promocje_nie">nie<em class="TipIkona"><b>Do obliczania wartości zamówienia dla darmowej wysyłki nie będą uwzględniane produkty będące w promocji</b></em></label>'  . "\n";
                echo '</p>' . "\n";     

                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_WAGA_WOLUMETRYCZNA">Podzielnik dla obliczenia wagi wolumetrycznej:</label>'  . "\n";
                echo '<input type="text" size="8" name="PARAMETRY[WYSYLKA_WAGA_WOLUMETRYCZNA]" value="" id="WYSYLKA_WAGA_WOLUMETRYCZNA" class="calkowita" />' . "\n";
                echo '</p>'  . "\n";  
                
                echo '<p>' . "\n";
                echo '<label for="WYSYLKA_DARMOWA_WYKLUCZONA">Wyłączona darmowa wysyłka dla produktów ustawionych jako darmowa wysyłka:</label>' . "\n";
                echo '<input type="radio" value="tak" id="darmowa_wykluczona_tak" name="PARAMETRY[WYSYLKA_DARMOWA_WYKLUCZONA]" checked="checked" /><label class="OpisFor" for="darmowa_wykluczona_tak">tak<em class="TipIkona"><b>Jeżeli do koszyka będą dodane produkty z ustawioną darmową wysyłką - dla tej formy wysyłki będzie obliczany normalny koszt wysyłki (nie będzie zerowany)</b></em></label>'  . "\n";
                echo '<input type="radio" value="nie" id="darmowa_wykluczona_nie" name="PARAMETRY[WYSYLKA_DARMOWA_WYKLUCZONA]" /><label class="OpisFor" for="darmowa_wykluczona_nie">nie<em class="TipIkona"><b>Jeżeli do koszyka będą dodane produkty z ustawioną darmową wysyłką - koszt wysyłki będzie wynosił 0</b></em></label>'  . "\n";
                echo '</p>' . "\n";                                 

                echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />'  . "\n";

                $tablica_oplat[] = array('id' => 1, 'text' => 'Opłata stała');
                $tablica_oplat[] = array('id' => 2, 'text' => 'Opłata zależna od wagi zamówienia');
                $tablica_oplat[] = array('id' => 3, 'text' => 'Opłata zależna od wartości zamówienia');
                $tablica_oplat[] = array('id' => 4, 'text' => 'Opłata zależna od ilości produktów');

                echo '<p>'  . "\n";
                echo '<label for="WYSYLKA_RODZAJ_OPLATY">Rodzaj opłaty:</label>'  . "\n";
                echo Funkcje::RozwijaneMenu('PARAMETRY[WYSYLKA_RODZAJ_OPLATY]', $tablica_oplat, '', ' id="WYSYLKA_RODZAJ_OPLATY" onclick="zmien_pola()" style="width:350px"');
                echo '</p>'  . "\n";

                echo '<div>'  . "\n";
                echo '<label style="margin-left:10px;margin-top:5px;">Koszt wysyłki (brutto):</label>'  . "\n";
                echo '</div>'  . "\n";

                // koszty stale
                echo '<div id="kosztyStale" class="RodzajKosztow">'  . "\n";
                echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="stale1"><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />'  . "\n";
                echo '<input class="kropka" type="text" name="parametry_stale_wartosc[]" value="0" /></div>'  . "\n";
                echo '</div>'  . "\n";

                // koszty zalezne od wagi zamowienia
                echo '<div id="kosztyWaga" class="RodzajKosztow" style="display:none">'  . "\n";
                  echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="waga1">do &nbsp; <input class="Waga" type="text" size="10" name="parametry_waga_przedzial[]" value="0" /> kg &nbsp; ';
                  echo '<input class="kropka" type="text" name="parametry_waga_wartosc[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>'  . "\n";
                  echo '<div class="odlegloscRwdTab" style="padding-top:10px">'  . "\n";
                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyWaga\',\'waga\', \'kg\', \'do\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyWaga\',\'waga\')" style="cursor:pointer;">usuń pozycję</span>'  . "\n";
                  echo '</div>'  . "\n";
                echo '</div>'  . "\n";

                // koszty zalezne od wartosci zamowienia
                echo '<div id="kosztyCena" class="RodzajKosztow" style="display:none">'  . "\n";
                  echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="cena1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_cena_przedzial[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . ' &nbsp; ';
                  echo '<input class="kropka" type="text" name="parametry_cena_wartosc[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>'  . "\n";
                  echo '<div class="odlegloscRwdTab" style="padding-top:10px">'  . "\n";
                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyCena\',\'cena\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\', \'do\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyCena\',\'cena\')" style="cursor:pointer;">usuń pozycję</span>'  . "\n";
                  echo '</div>'  . "\n";
                echo '</div>'  . "\n";

                // koszty zalezne od ilosci sztuk produktow
                echo '<div id="kosztySztuki" class="RodzajKosztow" style="display:none">'  . "\n";
                  echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="sztuki1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_sztuki_przedzial[]" value="0" /> szt. &nbsp; ';
                  echo '<input class="kropka" type="text" name="parametry_sztuki_wartosc[]" value="0" /> ' . $_SESSION['domyslna_waluta']['symbol'] . '</div>'  . "\n";
                  echo '<div class="odlegloscRwdTab" style="padding-top:10px">'  . "\n";
                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztySztuki\',\'sztuki\', \'szt.\', \'do\', \'' . $_SESSION['domyslna_waluta']['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztySztuki\',\'sztuki\')" style="cursor:pointer;">usuń pozycję</span>'  . "\n";
                  echo '</div>'  . "\n";
                echo '</div>'  . "\n";
                
                echo '<div class="maleInfo odlegloscRwdDiv" style="margin-top:10px">Koszty wysyłek należy podawać w kwotach brutto.</div>'  . "\n";

                echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />'  . "\n";

                echo '<div>'  . "\n";
                echo '<table class="WyborCheckbox"><tr>'  . "\n";
                
                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);

                echo '<td><label>Dostępna dla grup klientów:</label></td>'  . "\n";                  
                echo '<td>'  . "\n";
                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" id="grupa_1_' . $GrupaKlienta['id'] . '" name="PARAMETRY[WYSYLKA_GRUPA_KLIENTOW][]" /> <label class="OpisFor" for="grupa_1_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />'  . "\n";
                }               
                echo '</td>'  . "\n";
                
                echo '</tr></table>'  . "\n";
                echo '</div>'  . "\n";
                
                echo '<div class="maleInfo odlegloscRwdDiv" style="margin-top:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>'  . "\n";
                
                echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />'  . "\n";                  
                
                echo '<div>'  . "\n";
                echo '<table class="WyborCheckbox"><tr>'  . "\n";                  

                echo '<td><label>Niedostępna dla grup klientów:</label></td>'  . "\n";
                echo '<td>'  . "\n";
                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" id="grupa_2_' . $GrupaKlienta['id'] . '" name="PARAMETRY[WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE][]" /> <label class="OpisFor" for="grupa_2_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />'  . "\n";
                }               
                echo '</td>'  . "\n";

                unset($TablicaGrupKlientow);                  

                echo '</tr></table>'  . "\n";
                echo '</div>'  . "\n";
                
                echo '<div class="maleInfo odlegloscRwdDiv" style="margin-top:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>'  . "\n";
                
                echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />'  . "\n";
                echo '<p>'  . "\n";
                echo '<label>Dostępne płatności:</label>'  . "\n";

                $wszystkie_platnosci_tmp = Array();
                $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciId();

                echo '<select name="PARAMETRY[WYSYLKA_DOSTEPNE_PLATNOSCI][]" multiple="multiple" id="multipleHeaders1">'  . "\n";
                foreach ( $wszystkie_platnosci_tmp as $value ) {
                  echo '<option value="'.$value['id'].'" >'.$value['text'].'</option>'  . "\n";
                }
                echo '</select>'  . "\n";
                echo '</p>'  . "\n";

                echo '<div class="ostrzezenie odlegloscRwdTab BrakMarginesuRwd" style="margin-top:10px; margin-bottom:10px">Do wysyłki musi być przypisana minimum jedna forma płatności.</div>'  . "\n";

                $zapytanie_kraje = "SELECT DISTINCT c.countries_iso_code_2, cd.countries_name  
                                    FROM countries c
                                    LEFT JOIN countries_description cd ON c.countries_id = cd. countries_id AND cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                    ORDER BY cd.countries_name";
                $sqlc = $db->open_query($zapytanie_kraje);
                //

                echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />'  . "\n";
                echo '<p>'  . "\n";
                echo '<label>Kraje dostawy:</label>'  . "\n";
                echo '<select name="PARAMETRY[WYSYLKA_KRAJE_DOSTAWY][]" multiple="multiple" id="multipleHeaders">'  . "\n";

                while ($infc = $sqlc->fetch_assoc()) { 
                  echo '<option value="'.$infc['countries_iso_code_2'].'" >'.$infc['countries_name'].'</option>'  . "\n";
                }
                echo '</select>'  . "\n";
                echo '</p>'  . "\n";

                echo '<div class="ostrzezenie odlegloscRwdTab BrakMarginesuRwd" style="margin-top:10px; margin-bottom:10px">Do wysyłki musi być przypisany minimum jednen kraj.</div>'  . "\n";

                $db->close_query($sqlc);
                unset($zapytanie_kraje, $infc);  

                if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                
                    ?>
                      
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                    <p>
                     <label class="required">Skrypt:</label>   
                     <input type="text" name="SKRYPT" id="skrypt" size="53" value="" onkeyup="updateKeySkrypt();" /><em class="TipIkona"><b>Nazwa skryptu realizującego funkcje modułu</b></em>
                    </p>

                    <p>
                      <label class="required">Nazwa klasy:</label>   
                      <input type="text" name="KLASA" id="klasa" size="53" value="" onkeyup="updateKeyKlasa();" /><em class="TipIkona"><b>Nazwa klasy realizującej funkcje modułu</b></em>
                    </p>
                    
                <?php } else { ?>
                
                    <input type="hidden" name="SKRYPT" value="wysylka_standard.php" />
                    <input type="hidden" name="KLASA" value="wysylka_standard" />
                    
                <?php } ?>

                <script>
                gold_tabs('0','edytor_',200, '');
                </script>                    

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('wysylka','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}