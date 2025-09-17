<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

$LimitPobierania = 40;

if ( isset($_POST['cron']) && $_POST['cron'] == '1' && $_POST['limit_danych'] > 0 ) {
    $LimitPobierania = $_POST['limit_danych'];
}

if (isset($_POST['plik']) && ( Sesje::TokenSpr() || (isset($_POST['cron']) && $_POST['cron'] == '1' ))) {

    if (isset($_POST['typ']) && $_POST['typ'] == 'kategorie' && isset($_POST['format_importu']) && $_POST['format_importu'] == 'xml') {
        //
        // dodawanie czy aktualizacja
        $CzyDodawanie = false;
        if ($_POST['rodzaj_import'] == 'dodawanie') {
           $CzyDodawanie = true;
        }        
        // tylko jezyk polski
        $ile_jezykow = array( array('id' => '1','kod' => 'pl') ); 
        //
        include('import_danych/import_struktura_xml_kategorie.php');  
        //
    }
    
    
    if (isset($_POST['typ']) && $_POST['typ'] == 'kategorie' && isset($_POST['format_importu']) && $_POST['format_importu'] == 'csv') {
        //
        // dodawanie czy aktualizacja
        $CzyDodawanie = false;
        if ($_POST['rodzaj_import'] == 'dodawanie') {
           $CzyDodawanie = true;
        }        
        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        //
        // tworzy tablice z nazwami naglowkow i danymi z pliku csv
        $file = new SplFileObject("../import/" . $_POST['plik']);
        $file->seek( 0 );
        $DefinicjeCSV = $file->current();
        //
        
        $TabDefinicji = array();
        
        // stworzenie tablicy z definicjami
        $TabDefinicji = str_getcsv($DefinicjeCSV, $_POST['separator']);   
        $TablicaDef = array();

        foreach ($TabDefinicji as $Definicja) {

            $TablicaDef[] = trim((string)$Definicja);

        }            
        //
        // plik do przypisania danych do tablic z pliku csv
        include('import_danych/import_struktura_csv.php');      
        // kategorie i podkategorie
        include('import_danych/import_kategorie.php');              
        //
        echo json_encode( array("suma" => ((int)$_POST['limit'] + 1), "dodane" => 0, 'aktualizacja' => 0, 'nazwy' => '' ) );
    }    


    if (isset($_POST['typ']) && $_POST['typ'] != 'kategorie' && (isset($_POST['format_importu']) && ($_POST['format_importu'] == 'xml' || $_POST['format_importu'] == 'csv'))) {
        //
        include('import_danych/definicja_pol.php'); 
        
        // odpowiednie ladowanie danych
        if ($_POST['format_importu'] == 'xml') {
            //
            // tworzy tablice z nazwami naglowkow i danymi z pliku xml
            if ($_POST['plik'] == 'url' && strpos((string)$_POST['adres_url'], '.xml') > -1) {
                // 
                $dane_produktow = simplexml_load_file($_POST['adres_url']); 
                //
              } else if ($_POST['plik'] != 'url') {
                //
                $dane_produktow = simplexml_load_file("../import/" . $_POST['plik']); 
                //
            } 
            //
          } else if ($_POST['format_importu'] == 'csv') {
            //
            // tworzy tablice z nazwami naglowkow i danymi z pliku csv
            $file = new SplFileObject("../import/" . $_POST['plik']);
            $file->seek( 0 );
            $DefinicjeCSV = $file->current();
            //

            $TabDefinicji = array();
            
            // stworzenie tablicy z definicjami
            $TabDefinicji = str_getcsv($DefinicjeCSV, $_POST['separator']);
            $TablicaDef = array();

            foreach ($TabDefinicji as $Definicja) {

                $TablicaDef[] = trim((string)$Definicja);

            }            
            //
        }
        
        // dodatkowe zapytania do wyciagniecia danych szablonu xml - uzywane np przy imporcie z zewnetrznych xml - np ceneo
        if (isset($_POST['format_importu']) && $_POST['format_importu'] == 'xml' && isset($_POST['struktura']) && $_POST['struktura'] != 'xml') {
            //
            // dane do szablonu
            $CzyWszystkieKategorie = true;
            $TablicaKategoriiXml = array();
            $TablicaMnoznikow = array();
            
            if (isset($_POST['marza']) && (float)$_POST['marza'] == 0) {
            
                if (isset($_POST['szablon']) && (int)$_POST['szablon'] > 0) {
                    //
                    $zapytanie = "select * from tpl_xml where tpl_xml_id = '".(int)$_POST['szablon']."'";
                    $sqls = $db->open_query($zapytanie);
                    //
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        //
                        $info = $sqls->fetch_assoc();
                        //
                        if ($info['tpl_xml_range'] == '0') { $CzyWszystkieKategorie = false; }
                        //
                        // kategorie
                        if ( !empty((string)$info['tpl_xml_categories_text']) ) {
                             //
                             $podzielKategorie = explode('#', (string)$info['tpl_xml_categories_text']);
                             for ($c = 0, $cnt = count($podzielKategorie); $c < $cnt; $c++) {
                                 //
                                 $DodatkowyPodzial = explode(':', (string)$podzielKategorie[$c]);
                                 $TablicaKategoriiXml[] = array( $DodatkowyPodzial[0], $DodatkowyPodzial[1], ((isset($DodatkowyPodzial[2])) ? $DodatkowyPodzial[2] : 0) );
                                 unset($DodatkowyPodzial);
                                 //
                             }
                             //
                        }
                        //
                        // mnozniki
                        if ( !empty((string)$info['tpl_xml_price']) ) {
                             //
                             $podzielMnozniki = explode('#', (string)$info['tpl_xml_price']);
                             for ($c = 0, $cnt = count($podzielMnozniki); $c < $cnt; $c++) {
                                 //
                                 $DodatkowyPodzial = explode(':', (string)$podzielMnozniki[$c]);
                                 $TablicaMnoznikow[] = array( $DodatkowyPodzial[0], $DodatkowyPodzial[1], $DodatkowyPodzial[2], $DodatkowyPodzial[3] );
                                 unset($DodatkowyPodzial);
                                 //
                             }
                             //
                        }
                        //
                    }
                    //
                    $db->close_query($sqls);
                    unset($zapytanie);
                    //
                }      

            }
            
            //
            if ( isset($_POST['zakres_importu']) ) {
                //
                $nowaPost = unserialize(stripslashes((string)$_POST['zakres_importu']));
                foreach ( $nowaPost as $pol ) {
                    $_POST[$pol] = '1';
                }
                unset($nowaPost);
                //
            }
            //
        }
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        
        $poczatekPetli = (int)$_POST['limit'];
        $koniecPetli = $poczatekPetli + $LimitPobierania;
        
        if ($koniecPetli > (int)$_POST['ilosc_linii']) {
            if ($_POST['struktura'] == 'csv') {
                $koniecPetli = (int)$_POST['ilosc_linii'];
              } else {
                $koniecPetli = (int)$_POST['ilosc_linii'] + 1;
            }
        }
        
        $DodanaIlosc = 0;
        $AktualizowanaIlosc = 0;
        $NazwyProduktow = '';
        
        for ($imp = $poczatekPetli; $imp < $koniecPetli; $imp++) {
        
            $_POST['limit'] = $imp;

            // wczytywanie odpowiedniej struktury plikow
            switch ($_POST['struktura']) {
                case 'csv':
                    // plik do przypisania danych do tablic z pliku csv
                    include('import_danych/import_struktura_csv.php'); 
                    break;
                case 'xml':
                    // plik do przypisania danych do tablic z pliku xml
                    include('import_danych/import_struktura_xml.php'); 
                    break;
                default:
                    // plik do przypisania danych do tablic z pliku xml w formacie zewnetrznym np ceneo
                    if ( is_file('import_danych/plugin/' . $filtr->process($_POST['struktura']) . '.php') ) {
                         include('import_danych/plugin/' . $filtr->process($_POST['struktura']) . '.php'); 
                    }
                    break;                  
            }    
            
            // jezeli wogole jest cos do importu
            if (isset($TablicaDane) && count($TablicaDane) > 0) {

                // czyszczenie ze spacji
                foreach ( $TablicaDane as $Klucz => $Wartosc ) {
                    //
                    $Wartosc = trim((string)$Wartosc);
                    //
                    $TablicaDane[ $Klucz ] = $Wartosc;
                    //
                    // sprawdzenie zdjec
                    if ( strpos((string)$Klucz, 'Zdjecie_') > -1 && $Wartosc != '' && strpos((string)$Klucz,'_opis') == false ) { 
                         //
                         if ( isset($_POST['import_zdjec']) && $_POST['import_zdjec'] == 'tak' ) {
                             //
                             // pobieranie rozszerzenia
                             $infr = pathinfo($Wartosc);
                             //
                             if ( !isset($infr['extension']) ) {
                                  //
                                  $tmp = explode('.', (string)$Wartosc);
                                  $rozszerzenie = $tmp[ count($tmp) - 1];
                                  //
                             } else {
                                  //
                                  $rozszerzenie = $infr["extension"];
                                  //
                             }                         
                             //
                             $rozszerzenie = strtolower((string)$rozszerzenie);
                             //
                             if ( $rozszerzenie != 'jpeg' && $rozszerzenie != 'jpg' && $rozszerzenie != 'png' && $rozszerzenie != 'webp' && $rozszerzenie != 'gif' && $rozszerzenie != 'svg'  && $rozszerzenie != 'tiff' ) {
                                  //
                                  $TablicaDane[ $Klucz ] = 'foto-' . rand(1,99999999) . '-' . time() . '.jpg';
                                  $TablicaDane[ $Klucz . '_zrodlo' ] = $Wartosc;
                                  //
                             }
                             //
                             unset($infr, $rozszerzenie);
                             //
                         }
                         //
                    }
                    //
                }

                // dodawanie czy aktualizacja
                $CzyDodawanie = false;
                if ($_POST['rodzaj_import'] == 'dodawanie') {
                   $CzyDodawanie = true;
                }
                
                // kategorie i podkategorie
                include('import_danych/import_kategorie.php');    
                
                // ------------------------------- *************** -----------------------------
                // dodawanie lub aktualizowanie produktu
                // ------------------------------- *************** -----------------------------
              
                // wylaczenie produktow przy aktualizacji
                if (isset($_POST['wylaczenie_produktow']) && (int)$_POST['wylaczenie_produktow'] == 1 && $CzyDodawanie == false && $poczatekPetli == 0 && $imp == 0) {
                    //
                    if (isset($_POST['prefix_nr_kat']) && trim((string)$_POST['prefix_nr_kat']) != '') {
                        //
                        $pola = array(array('products_status','0'));
                        $db->update_query('products' , $pola, "products_model LIKE '" . $filtr->process(trim((string)$_POST['prefix_nr_kat'])) . "%'");
                        unset($pola);
                        //
                    }
                    if (isset($_POST['id_zewnetrzne']) && trim((string)$_POST['id_zewnetrzne']) != '') {
                        //
                        $pola = array(array('products_status','0'));
                        $db->update_query('products' , $pola, "products_id_private = '" . $filtr->process(trim((string)$_POST['id_zewnetrzne'])) . "'");
                        unset($pola);
                        //
                    }                    
                    //
                }                       
              
                // dodawanie prefixu do nr katalogowego
                if (isset($_POST['prefix_nr_kat']) && trim((string)$_POST['prefix_nr_kat']) != '') {
                    //
                    if (isset($TablicaDane['Nr_katalogowy']) && trim((string)$TablicaDane['Nr_katalogowy']) != '') {
                       //
                       $TablicaDane['Nr_katalogowy'] = $filtr->process(trim((string)$_POST['prefix_nr_kat'])) . $TablicaDane['Nr_katalogowy'];
                       //
                    }
                    //
                }
                
                // zmiana id zewnetrznego
                if (isset($_POST['id_zewnetrzne']) && trim((string)$_POST['id_zewnetrzne']) != '') {
                    //
                    $TablicaDane['Id_produktu_magazyn'] = $filtr->process(trim((string)$_POST['id_zewnetrzne']));
                    //
                }                

                // jezeli jest numer katalogowy
                if ((isset($TablicaDane['Nr_katalogowy']) && trim((string)$TablicaDane['Nr_katalogowy']) != '' || 
                     isset($TablicaDane['Id_produktu']) && trim((string)$TablicaDane['Id_produktu']) != '' ||
                     isset($TablicaDane['Kod_ean']) && trim((string)$TablicaDane['Kod_ean']) != '') && 
                     $_POST['typ'] != 'kategorie') {

                    //
                    $wBazieJestProdukt = false;
                    //

                    // sprawdza czy jest produkt w bazie
                    if ( isset($TablicaDane['Id_produktu']) && $CzyDodawanie == false ) {
                         //
                         $zapytanieDaneProduktu = "select products_id, products_model from products where products_id = '" . (int)$TablicaDane['Id_produktu'] . "'";
                         $sqDaneProduktu = $db->open_query($zapytanieDaneProduktu);
                         //
                    } else if ( isset($TablicaDane['Nr_katalogowy']) ) {
                         //
                         $zapytanieDaneProduktu = "select products_id, products_model from products where products_model = '" . addslashes((string)$filtr->process($TablicaDane['Nr_katalogowy'])) . "'";
                         $sqDaneProduktu = $db->open_query($zapytanieDaneProduktu);
                         //
                    } else if ( isset($TablicaDane['Kod_ean']) ) {
                         //
                         $zapytanieDaneProduktu = "select products_id, products_model from products where	products_ean = '" . addslashes((string)$filtr->process($TablicaDane['Kod_ean'])) . "'";
                         $sqDaneProduktu = $db->open_query($zapytanieDaneProduktu);
                         //
                    }                     
                    //            
                    
                    if ((int)$db->ile_rekordow($sqDaneProduktu) > 0) {
                        //
                        $wBazieJestProdukt = true;
                        //
                        $info = $sqDaneProduktu->fetch_assoc();
                        $id_aktualizowanej_pozycji = $info['products_id'];
                        unset($info);  
                        //
                    }
                    
                    $db->close_query($sqDaneProduktu);
                    
                    // sprawdza czy jezeli jest aktualizacja to jest nr katalogoway lub jak jest dodawanie to czy nie ma nr kat
                    if (($wBazieJestProdukt == true && $CzyDodawanie == false) || ($wBazieJestProdukt == false && $CzyDodawanie == true)) {
                    
                        // licznik 
                        if ($CzyDodawanie == true) {
                            $DodanaIlosc++;
                          } else {
                            $AktualizowanaIlosc++;
                        }
                        
                        if (isset($TablicaDane['Nazwa_produktu_struktura']) && trim((string)$TablicaDane['Nazwa_produktu_struktura']) != '') {
                            $NazwyProduktow .= '<li>' . trim((string)$TablicaDane['Nazwa_produktu_struktura']) . '</li>';
                           } else {
                            if ( isset($TablicaDane['Id_produktu']) && $CzyDodawanie == false ) {
                                 $NazwyProduktow .= '<li><span>id produktu:</span> ' . trim((string)$TablicaDane['Id_produktu']) . '</li>';
                            } else if ( isset($TablicaDane['Nr_katalogowy']) ) {
                                 $NazwyProduktow .= '<li><span>nr katalogowy:</span> ' . trim((string)$TablicaDane['Nr_katalogowy']) . '</li>';
                            } else if ( isset($TablicaDane['Kod_ean']) ) {
                                 $NazwyProduktow .= '<li><span>kod EAN:</span> ' . trim((string)$TablicaDane['Kod_ean']) . '</li>';
                            }                            
                        }                      
                    
                        // podwyzszanie procentowe cen dla xml
                        if ($_POST['format_importu'] == 'xml') {
                            //
                            $DodatekDoCeny = 0;
                            $WartoscMarza = 0;
                            //
                            // jezeli marza jest 0 sprawdzi czy nie ma mnoznikow
                            if (isset($_POST['marza']) && (float)$_POST['marza'] == 0) {
                                //
                                if (isset($TablicaMnoznikow) && count($TablicaMnoznikow) > 0) {
                                    //
                                    foreach ( $TablicaMnoznikow as $Mnoznik ) {
                                        //
                                        if ((float)$TablicaDane['Cena_brutto'] >= (float)$Mnoznik[0] && (float)$TablicaDane['Cena_brutto'] <= (float)$Mnoznik[1]) {
                                            //
                                            if ( (float)$Mnoznik[2] != 0 ) {
                                                  //
                                                  $WartoscMarza = (float)$Mnoznik[2];
                                                  //
                                            }
                                            if ( (float)$Mnoznik[3] != 0 ) {
                                                  //
                                                  $DodatekDoCeny = (float)$Mnoznik[3];
                                                  //
                                            }                                        
                                            //
                                        }
                                        //
                                    }
                                    //
                                }
                                //
                            }
                            //                            
                            if ((isset($_POST['marza']) && (float)$_POST['marza'] != 0) || $WartoscMarza != 0 || (float)$DodatekDoCeny != 0) {
                                //
                                if ( $WartoscMarza != 0 ) {
                                     //
                                     $marza = ((100 + $WartoscMarza) / 100);
                                     //
                                } else {
                                     //
                                     $marza = ((100 + (float)$_POST['marza']) / 100);
                                     //
                                }
                                //
                                if (isset($TablicaDane['Cena_brutto']) && (float)$TablicaDane['Cena_brutto'] > 0) {
                                    $TablicaDane['Cena_brutto'] = ((float)$TablicaDane['Cena_brutto'] * $marza) + $DodatekDoCeny;
                                }
                                if (isset($TablicaDane['Cena_poprzednia']) && (float)$TablicaDane['Cena_poprzednia'] > 0) {
                                    $TablicaDane['Cena_poprzednia'] = ((float)$TablicaDane['Cena_poprzednia'] * $marza) + $DodatekDoCeny;
                                }                    
                                //
                                for ($w = 2; $w <= ILOSC_CEN ; $w++) {
                                    //
                                    if (isset($TablicaDane['Cena_brutto_'.$w]) && (float)$TablicaDane['Cena_brutto_'.$w] > 0) {
                                        //
                                        $TablicaDane['Cena_brutto_'.$w] = ((float)$TablicaDane['Cena_brutto_'.$w] * $marza) + $DodatekDoCeny;
                                        //
                                    }
                                    if (isset($TablicaDane['Cena_poprzednia_'.$w]) && (float)$TablicaDane['Cena_poprzednia_'.$w] > 0) {
                                        //
                                        $TablicaDane['Cena_poprzednia_'.$w] = ((float)$TablicaDane['Cena_poprzednia_'.$w] * $marza) + $DodatekDoCeny;
                                        //
                                    }                                    
                                    // 
                                }  
                                //
                                unset($marza);
                                //
                            }
                            //
                        }        
                        
                        // zaokraglanie cen
                        if (isset($_POST['zaokraglanie']) && trim((string)$_POST['zaokraglanie']) != '') {    
                            //
                            if ( isset($TablicaDane['Cena_brutto']) ) {
                                 //
                                 if ($_POST['zaokraglanie'] == 'zaokraglanie_cen_zero') {   
                                     $TablicaDane['Cena_brutto'] = ceil((float)$TablicaDane['Cena_brutto']);
                                 }
                                 if ($_POST['zaokraglanie'] == 'zaokraglanie_cen_ulamek') {     
                                     $TablicaDane['Cena_brutto'] = round((float)$TablicaDane['Cena_brutto'], 1);
                                 }            
                                 //
                            }
                            //
                        }                        

                        // dodawanie do tablicy Products
                        $pola = array();
                        for ($pol = 0, $cn = count($TablicaProducts); $pol < $cn; $pol++) {
                        
                            if (isset($TablicaDane[$TablicaProducts[$pol][1]]) && trim((string)$TablicaDane[$TablicaProducts[$pol][1]]) != '') {
                                //
                                $poleCsv = $filtr->process($TablicaProducts[$pol][1]);
                                //
                                $byl_zapis = false;
                                //
                                // jezeli pole to cena sprawdza czy jest taka ilosc cen w bazie
                                if (strpos((string)$poleCsv,'Cena_brutto_') > -1) {
                                    $jakiNrCeny = explode('_', (string)$poleCsv);
                                    if ((int)$jakiNrCeny[2] <= ILOSC_CEN) {
                                        //
                                        // jezeli jest aktualizacja nie mozna zmienic nr katalogowego
                                        if ($CzyDodawanie == false && $TablicaProducts[$pol][0] == 'products_model') {
                                            echo '';
                                          } else {
                                            $pola[] = array($TablicaProducts[$pol][0],$filtr->process($TablicaDane[$TablicaProducts[$pol][1]]));
                                            $byl_zapis = true;
                                        }
                                        //
                                    }
                                }

                                // jezeli sa to pola tak/nie to zamiast tak lub nie trzeba wstawic 1 lub 0
                                if ($poleCsv == 'Nowosc' || $poleCsv == 'Nasz_hit' || $poleCsv == 'Polecany' || $poleCsv == 'Promocja' || $poleCsv == 'Wyprzedaz' || $poleCsv == 'Do_porownywarek' ||
                                    $poleCsv == 'Negocjacja' || $poleCsv == 'Status' || $poleCsv == 'Gabaryt' || $poleCsv == 'Osobna_paczka' || $poleCsv == 'Darmowa_dostawa' || $poleCsv == 'Ceneo_kup_teraz' || $poleCsv == 'Wykluczona_darmowa_dostawa' || $poleCsv == 'Kupowanie' ||
                                    $poleCsv == 'Ikona_1' || $poleCsv == 'Ikona_2' || $poleCsv == 'Ikona_3' || $poleCsv == 'Ikona_4' || $poleCsv == 'Ikona_5' ||
                                    $poleCsv == 'Wykluczony_punkt_odbioru' || $poleCsv == 'Ceneo_kup_teraz') {
                                    //
                                    if (strtolower((string)$TablicaDane[$poleCsv]) == 'tak') {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'1');
                                        $byl_zapis = true;
                                        //
                                      } else {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'0');
                                        $byl_zapis = true;
                                        //                  
                                    }
                                } 

                                // kontrola magazynu
                                if ($poleCsv == 'Kontrola_magazynu') {
                                    //
                                    if (strtolower((string)$TablicaDane[$poleCsv]) == 'tak') {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'1');
                                        $byl_zapis = true;
                                        //
                                    }
                                    if (strtolower((string)$TablicaDane[$poleCsv]) == 'nie') {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'0');
                                        $byl_zapis = true;
                                        //
                                    } 
                                    if (strtolower((string)$TablicaDane[$poleCsv]) == 'ograniczona') {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'2');
                                        $byl_zapis = true;
                                        //
                                    }                                      
                                    //
                                }                                
                                
                                if ( $poleCsv == 'Promocja_czas_rozpoczecia' || $poleCsv == 'Promocja_czas_zakonczenia' ) {
                                     //
                                     if ( date('Y', FunkcjeWlasnePHP::my_strtotime($TablicaDane[$TablicaProducts[$pol][1]])) < 2000 ) {
                                          $pola[] = array($TablicaProducts[$pol][0],'0000-00-00 00:00');
                                      } else {
                                          $pola[] = array($TablicaProducts[$pol][0],date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($TablicaDane[$TablicaProducts[$pol][1]])));
                                     }
                                     $byl_zapis = true;
                                     //
                                }                            

                                // rodzaj produktu
                                if ($poleCsv == 'Rodzaj_produktu') {
                                    //
                                    $tmpRodzaj = array('standard', 'indywidualny', 'usluga', 'online');
                                    //
                                    if ( !in_array(strtolower((string)$TablicaDane[$poleCsv]), $tmpRodzaj) ) {
                                         $byl_zapis = true;                                      
                                    }
                                    //
                                }
                                
                                // jezeli jest zdjecie i ma adres w linku
                                if ($poleCsv == 'Zdjecie_glowne') {
                                    //
                                    $urlPat = parse_url($TablicaDane[$TablicaProducts[$pol][1]]);
                                    //
                                    $link_zdjecia = $TablicaDane[$TablicaProducts[$pol][1]];
                                    //
                                    if ( isset($urlPat['host']) && isset($urlPat['path'])) {
                                         //
                                         $scie = $urlPat['path'];
                                         if ( substr((string)$scie, 0, 1) == '/' ) {
                                              $scie = substr((string)$scie, 1, strlen((string)$scie));
                                         }
                                         //
                                         $podziel = explode('/', (string)$scie);
                                         $podziel_wynik = array();
                                         //
                                         for ($x = 0; $x < count($podziel); $x++) {
                                              $podziel_wynik[] = $podziel[$x];
                                         }
                                         //
                                         $link_zdjecia = implode('/', (array)$podziel_wynik);
                                         //
                                    }
                                    //
                                    $pola[] = array($TablicaProducts[$pol][0],$filtr->process($link_zdjecia));
                                    $byl_zapis = true;
                                    //
                                    unset($link_zdjecia, $urlPat);
                                    //
                                }                                
                                
                                // jezeli nie bylo zapisu i nie jest to cena hurtowa to robi normalny zapis
                                if ($byl_zapis == false && strpos((string)$poleCsv,'Cena_brutto_') === false) {
                                    //
                                    $pola[] = array($TablicaProducts[$pol][0],$filtr->process($TablicaDane[$TablicaProducts[$pol][1]]));
                                    //
                                }
                                //
                                unset($poleCsv);
                                //

                            }
                            
                            // id porownywarek do eksportu
                            if ($TablicaProducts[$pol][1] == 'Porownywarki_id') {
                                //
                                foreach ($pola as $key => $tmp ) {
                                    //
                                    if ( $tmp[0] == 'export_id' ) {
                                         //
                                         if ($TablicaDane['Porownywarki_id'] != '') {
                                             $pola[$key] = array($TablicaProducts[$pol][0],',' . $TablicaDane['Porownywarki_id'] . ',');
                                         } else {
                                             $pola[$key] = array($TablicaProducts[$pol][0],'');
                                         }
                                         //
                                    }
                                    //
                                }
                                //
                            }
                            
                            // wysylki id
                            if ($TablicaProducts[$pol][1] == 'Wysylki_id') {
                                //
                                foreach ($pola as $key => $tmp ) {
                                    //
                                    if ( $tmp[0] == 'shipping_method' ) {
                                         //
                                         if ($TablicaDane['Wysylki_id'] != '') {
                                             $pola[$key] = array($TablicaProducts[$pol][0], implode(';', explode(',', (string)$TablicaDane['Wysylki_id'])));
                                         } else {
                                             $pola[$key] = array($TablicaProducts[$pol][0],'');
                                         }
                                         //
                                    }
                                    //
                                }
                                //
                            }                                
                            
                        }
                        
                        // aktualizacja vat i netto oraz cen hurtowych
                        include('import_danych/import_ceny.php');        

                        // dostepnosc produktow
                        include('import_danych/import_dostepnosc.php');             
                        
                        // jednostka miary
                        include('import_danych/import_jm.php'); 
                        
                        // stan produktu
                        include('import_danych/import_stan_produktu.php');                         
                        
                        // gwarancja
                        include('import_danych/import_gwarancja.php');                          
                        
                        // producent
                        include('import_danych/import_producent.php');         
                  
                        // waluta
                        include('import_danych/import_waluta.php'); 
                        
                        // warianty
                        include('import_danych/import_inne_warianty.php');                             
                        
                        // klasa energetyczna
                        include('import_danych/import_klasa_energetyczna.php');   
                        
                        if ($CzyDodawanie == true) {
                            //
                            // data dodania produktu
                            $pola[] = array('products_date_added','now()');
                            $pola[] = array('customers_group_id','0');
                            //
                            // dodawanie do tablicy Products
                            $id_dodanej_pozycji = $db->insert_query('products' , $pola, '', false, true);
                            unset($pola);
                            //
                          } else {
                            //
                            // aktualizowanie tablicy Products
                            if ( count($pola) > 0 ) {
                                 $db->update_query('products' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "'");
                            }
                            unset($pola);
                            //
                        }

                        // dodatkowe zdjecia
                        include('import_danych/import_foto.php');      
                        
                        // dodatkowe zakladki
                        include('import_danych/import_zakladki.php');           

                        // linki
                        include('import_danych/import_linki.php'); 
                        
                        // pliki
                        include('import_danych/import_pliki.php');   

                        // youtube
                        include('import_danych/import_youtube.php');    

                        // filmy flv
                        include('import_danych/import_filmy.php');                          
                        
                        // pliki mp3
                        include('import_danych/import_mp3.php');    

                        // pliki elektroniczne
                        include('import_danych/import_pliki_elektroniczne.php');                           
                        
                        // dodatkowe pola
                        include('import_danych/import_dodatkowe_pola.php');  
                        
                        // akcesoria dodatkowe
                        include('import_danych/import_akcesoria.php');
                        
                        // paczkomaty
                        include('import_danych/import_paczkomaty.php');                        
                        
                        // dane allegro
                        include('import_danych/import_allegro.php');     

                        // podobne i powiazane 
                        include('import_danych/import_podobne_powiazane.php');     
                        
                        // dodawanie do tablicy Products description
                        $pola = array();
                        if ($CzyDodawanie == true) {
                            $pola[] = array('products_id',$id_dodanej_pozycji);
                            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
                        }
                        //
                        $ByloJakiesPole = false;
                        //
                        for ($pol = 0, $cn1 = count($TablicaProductsDescription); $pol < $cn1; $pol++) {
                        
                            if (isset($TablicaDane[$TablicaProductsDescription[$pol][1]]) && trim((string)$TablicaDane[$TablicaProductsDescription[$pol][1]]) != '') {
                                //
                                $pola[] = array($TablicaProductsDescription[$pol][0],$filtr->process($TablicaDane[$TablicaProductsDescription[$pol][1]]));
                                //
                                $ByloJakiesPole = true;
                            }
                        
                        }
                        
                        // usuwa adres z linku kanonicznego
                        if ( count($pola) > 0 ) {
                             foreach ( $pola as $klucz => $wartosc ) {
                                if (strpos($wartosc[0], 'link_canonical') > -1) {
                                    //
                                    $pola[$klucz] = array($wartosc[0], str_replace(ADRES_URL_SKLEPU . '/', '',$wartosc[1]));
                                    //
                                }
                             }
                        }                            
                        
                        if ($CzyDodawanie == true) {
                            $db->insert_query('products_description', $pola); 
                          } else if ($ByloJakiesPole == true) {
                            $db->update_query('products_description' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                        }                  
                        unset($pola); 
                        
                        // ---------------------------------------------------------------
                        // dodawanie do innych jezykow jak sa inne jezyki
                        for ($j = 0, $cn2 = count($ile_jezykow); $j < $cn2; $j++) {
                            //
                            $kod_jezyka = $ile_jezykow[$j]['kod'];
                            //
                            // dodawanie do tablicy Products description
                            $pola = array();
                            if ($CzyDodawanie == true) {
                                $pola[] = array('products_id',$id_dodanej_pozycji);
                                $pola[] = array('language_id',$ile_jezykow[$j]['id']);
                            }
                            $ByloJakiesPole = false;
                            //
                            for ($pol = 0, $cn3 = count($TablicaProductsDescription); $pol < $cn3; $pol++) {
                            
                                if (isset($TablicaDane[$TablicaProductsDescription[$pol][1] . '_' . $kod_jezyka]) && trim((string)$TablicaDane[$TablicaProductsDescription[$pol][1] . '_' . $kod_jezyka]) != '') {
                                    //
                                    $pola[] = array($TablicaProductsDescription[$pol][0],$filtr->process($TablicaDane[$TablicaProductsDescription[$pol][1] . '_' . $kod_jezyka]));
                                    //
                                    $ByloJakiesPole = true;
                                }
                            
                            }
                            //
                            if ($CzyDodawanie == true && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                $db->insert_query('products_description', $pola); 
                              } else if ($ByloJakiesPole == true && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                $db->update_query('products_description' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and language_id = '".$ile_jezykow[$j]['id']."'");
                            }                       
                            unset($pola);   
                            //
                            unset($kod_jezyka);
                            //
                        }        
                        
                        // dodawanie do tablicy Products description additional
                        $pola = array();
                        if ($CzyDodawanie == true) {
                            $pola[] = array('products_id',$id_dodanej_pozycji);
                            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
                        }
                        //
                        $ByloJakiesPole = false;
                        //
                        for ($pol = 0, $cn1 = count($TablicaProductsDescriptionAdditional); $pol < $cn1; $pol++) {
                        
                            if (isset($TablicaDane[$TablicaProductsDescriptionAdditional[$pol][1]]) && trim((string)$TablicaDane[$TablicaProductsDescriptionAdditional[$pol][1]]) != '') {
                                //
                                $pola[] = array($TablicaProductsDescriptionAdditional[$pol][0],$filtr->process($TablicaDane[$TablicaProductsDescriptionAdditional[$pol][1]]));
                                //
                                $ByloJakiesPole = true;
                            }
                        
                        }
                        
                        if ($CzyDodawanie == true) {
                            $db->insert_query('products_description_additional', $pola); 
                          } else if ($ByloJakiesPole == true) {
                            $db->update_query('products_description_additional' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                        }                  
                        unset($pola);                         
                        
                        // dodawanie do innych jezykow jak sa inne jezyki
                        for ($j = 0, $cn2 = count($ile_jezykow); $j < $cn2; $j++) {
                            //
                            $kod_jezyka = $ile_jezykow[$j]['kod'];
                            //
                            // dodawanie do tablicy Products description additional
                            $pola = array();
                            if ($CzyDodawanie == true) {
                                $pola[] = array('products_id',$id_dodanej_pozycji);
                                $pola[] = array('language_id',$ile_jezykow[$j]['id']);
                            }
                            $ByloJakiesPole = false;
                            //
                            for ($pol = 0, $cn3 = count($TablicaProductsDescriptionAdditional); $pol < $cn3; $pol++) {
                            
                                if (isset($TablicaDane[$TablicaProductsDescriptionAdditional[$pol][1] . '_' . $kod_jezyka]) && trim((string)$TablicaDane[$TablicaProductsDescriptionAdditional[$pol][1] . '_' . $kod_jezyka]) != '') {
                                    //
                                    $pola[] = array($TablicaProductsDescriptionAdditional[$pol][0],$filtr->process($TablicaDane[$TablicaProductsDescriptionAdditional[$pol][1] . '_' . $kod_jezyka]));
                                    //
                                    $ByloJakiesPole = true;
                                }
                            
                            }
                            //
                            if ($CzyDodawanie == true && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                $db->insert_query('products_description_additional', $pola); 
                              } else if ($ByloJakiesPole == true && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                $db->update_query('products_description_additional' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and language_id = '".$ile_jezykow[$j]['id']."'");
                            }                       
                            unset($pola);   
                            //
                            unset($kod_jezyka);
                            //
                        }  

                        // jezeli importuje kategorie i produkty
                        if ( (($BylyKategorie == true && $CzyDodawanie == false) || $CzyDodawanie == true) && !isset($TablicaDane['Kategorie_id']) ) {        

                            // jezeli jest aktualizacja i sa w pliku kategorie to czyscie tablice powiazan produktu z kategoriami
                            if ($CzyDodawanie == false && $BylyKategorie == true) {
                                // kasuje rekordy w tablicy
                                $db->delete_query('products_to_categories' , " products_id = '".$id_aktualizowanej_pozycji."'");                  
                            }
                            
                            // dodawanie do tablicy Products to Categories
                            $pola = array();
                            $pola[] = array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                            $pola[] = array('categories_id',$parent);
                            $db->insert_query('products_to_categories' , $pola);
                            unset($pola); 

                        }
                        
                        // jezeli sa kategorie po id
                        if ( isset($TablicaDane['Kategorie_id']) ) {
                          
                            $TablicaKategoriiProduktu = explode(',', (string)$TablicaDane['Kategorie_id']);
                            
                            if ( count($TablicaKategoriiProduktu) > 0 ) {
                          
                                // jezeli jest aktualizacja i sa w pliku kategorie to czyscie tablice powiazan produktu z kategoriami
                                if ($CzyDodawanie == false) {
                                    // kasuje rekordy w tablicy
                                    $db->delete_query('products_to_categories' , " products_id = '".$id_aktualizowanej_pozycji."'");                  
                                }   

                                foreach ( $TablicaKategoriiProduktu as $KatProd ) {
                                  
                                    if ( isset($TablicaKategorii[(int)$KatProd]) ) {
                                  
                                        // dodawanie do tablicy Products to Categories
                                        $pola = array();
                                        $pola[] = array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                                        $pola[] = array('categories_id',(int)$KatProd);
                                        $db->insert_query('products_to_categories' , $pola);
                                        unset($pola);

                                    }
                                  
                                }
                                
                            }
                            
                            unset($TablicaKategoriiProduktu);
                          
                        }
                        
                        include('import_danych/import_cechy.php');
                        
                        // stary link sklepu
                        if (isset($TablicaDane['Stary_URL']) && trim((string)$TablicaDane['Stary_URL']) != '') {  
                            //
                            if ($CzyDodawanie != true) {
                                $db->delete_query('location' , " products_id = '".$id_aktualizowanej_pozycji."' and url_type = 'produkt'");
                            }
                            //
                            // ustala link seo
                            $zapytanie_seo = "select products_seo_url, products_name from products_description where products_id = '".(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                            $sqlseo = $db->open_query($zapytanie_seo);
                            //
                            if ((int)$db->ile_rekordow($sqlseo) > 0) {
                                //
                                $seo = $sqlseo->fetch_assoc();
                                //
                                $linkSeo = ((!empty($seo['products_seo_url'])) ? $seo['products_seo_url'] : $seo['products_name']);
                                //
                                // usuniecie domeny
                                $parseUrl = parse_url($filtr->process($TablicaDane['Stary_URL']));
                                $host = '';
                                $path = '';
                                if ( isset($parseUrl['host']) ) {
                                     $host = $parseUrl['host'];
                                }
                                if ( isset($parseUrl['path']) ) {
                                     $path = $parseUrl['path']; 
                                }
                                //
                                if ( $host != '' && $path != '' ) {
                                     $staryAdres = str_replace($host, '', $path);
                                } else {
                                     $staryAdres = $filtr->process($TablicaDane['Stary_URL']);
                                }
                                //                                
                                if (substr($staryAdres, 0, 1) == "/") {
                                    $staryAdres = substr($staryAdres, 1);
                                }                                
                                //
                                $pola = array(
                                        array('urlf',$staryAdres),
                                        array('urlt',Seo::link_SEO( $linkSeo, (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji), 'produkt', '', false, false )),
                                        array('url_type','produkt'),
                                        array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)));
                                $sql = $db->insert_query('location' , $pola);
                                //
                                unset($pola, $linkSeo);             
                                //
                                unset($seo);
                                //                                                
                            }
                            //
                            $db->close_query($sqlseo);
                            unset($zapytanie_seo);                            
                            //
                        }

                    }
              
                }

            }
        
        }
        
        echo json_encode( array("suma" => $imp, "dodane" => $DodanaIlosc, 'aktualizacja' => $AktualizowanaIlosc, 'nazwy' => $NazwyProduktow ) );
        
        unset($imp, $DodanaIlosc, $AktualizowanaIlosc, $NazwyProduktow);

    }
}
?>