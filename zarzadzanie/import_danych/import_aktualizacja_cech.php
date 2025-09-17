<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && Sesje::TokenSpr()) {
  
    // dodawanie czy aktualizacja
    $CzyDodawanie = false;
    if ($_POST['rodzaj_import'] == 'dodawanie') {
       $CzyDodawanie = true;
    }  
        
    if (isset($_POST['format_importu']) && $_POST['format_importu'] == 'csv') {
        //
        $TablicaProducts = array();
        $TablicaProducts[] = array('products_stock_model','Nr_katalogowy_cechy');
        $TablicaProducts[] = array('products_stock_ean','Kod_ean_cechy');
        $TablicaProducts[] = array('products_stock_quantity','Ilosc_produktow');
        $TablicaProducts[] = array('products_stock_image','Zdjecie');

        $file = new SplFileObject("../import/" . $_POST['plik']);
        $file->seek( 0 );
        $DefinicjeCSV = $file->current(); 
        
        $TabDefinicji = array();

        // stworzenie tablicy z definicjami
        $TabDefinicji = str_getcsv($DefinicjeCSV, $_POST['separator']);
        $TablicaDef = array();

        foreach ($TabDefinicji as $Definicja) {

            $TablicaDef[] = trim((string)$Definicja);

        }        
        //
      } else if (isset($_POST['format_importu']) && $_POST['format_importu'] == 'xml') {
        //
        // tworzy tablice z nazwami naglowkow i danymi z pliku xml
        if ($_POST['plik'] == 'url' && isset($_POST['adres_url']) && strpos((string)$_POST['adres_url'], '.xml') > -1) {
            // 
            $dane_produktow = simplexml_load_file($_POST['adres_url']); 
            //
          } else if ($_POST['plik'] != 'url') {
            //
            $dane_produktow = simplexml_load_file("../import/" . $_POST['plik']); 
            //
        }   
        //
    }
    
    $ile_jezykow = Funkcje::TablicaJezykow();

    // ------------------------------- *************** -----------------------------
    // aktualizowanie danych
    // ------------------------------- *************** -----------------------------
    
    $poczatekPetli = (int)$_POST['limit'];
    $koniecPetli = $poczatekPetli + 50;
    
    if ($koniecPetli > (int)$_POST['ilosc_linii']) {
        if ($_POST['struktura'] == 'csv') {
            $koniecPetli = (int)$_POST['ilosc_linii'];
          } else {
            $koniecPetli = (int)$_POST['ilosc_linii'] + 1;
        }
    }    
    
    $AktualizowanaIlosc = 0;
    $DodanaIlosc = 0;
    $NrKatalogoweCech = '';    
    
    for ($imp = $poczatekPetli; $imp < $koniecPetli; $imp++) {
    
        $_POST['limit'] = $imp;
        
        if (isset($_POST['format_importu']) && $_POST['format_importu'] == 'csv') {       
            //
            // plik do przypisania danych do tablic z pliku csv
            include('import_danych/import_struktura_csv.php');  
            //
          } else if (isset($_POST['format_importu']) && $_POST['format_importu'] == 'xml') {
            //
            // plik do przypisania danych do tablic z pliku xml
            include('import_danych/import_struktura_xml.php');  
            //
        }        

        // jezeli jest numer katalogowy
        if (isset($TablicaDane['Nr_katalogowy_cechy']) && trim((string)$TablicaDane['Nr_katalogowy_cechy']) != '') {
          
            $IdProduktuDlaCechy = 0;
            $IdKombinacjiCechy = 0;
            $IleCechProduktu = 0;
          
            if ($CzyDodawanie == false) {
        
                // sprawdza czy nr kat jest w bazie
                $zapytanieNrKatProdukt = "select distinct * from products_stock where products_stock_model = '" . addslashes((string)$filtr->process($TablicaDane['Nr_katalogowy_cechy'])) . "'";
                $sqlModel = $db->open_query($zapytanieNrKatProdukt);
                //            
                if ((int)$db->ile_rekordow($sqlModel) > 0) {
                    //
                    $info = $sqlModel->fetch_assoc();
                    $IdProduktuDlaCechy = $info['products_id'];
                    $IdKombinacjiCechy = $info['products_stock_id'];
                    unset($info);
                    //
                }
                
                $db->close_query($sqlModel);
                unset($zapytanieNrKatProdukt); 
       
            } else {
              
                // ustala id produktu
                $zapytanieIdProdukt = "select distinct products_id from products where products_model = '" . addslashes((string)$filtr->process($TablicaDane['Nr_katalogowy'])) . "'";
                $sqlProdukt = $db->open_query($zapytanieIdProdukt);
                //            
                if ((int)$db->ile_rekordow($sqlProdukt) > 0) {
                    //
                    $info = $sqlProdukt->fetch_assoc();
                    $IdProduktuDlaCechy = $info['products_id'];
                    unset($info);
                    //
                    // sprawdzi jakie produkt ma cechy
                    $zapytanieCechyProdukt = "select distinct options_id from products_attributes where products_id = '" . $IdProduktuDlaCechy . "'";
                    $sqlCechyProdukt = $db->open_query($zapytanieCechyProdukt);
                    //
                    while ($info = $sqlCechyProdukt->fetch_assoc()) {
                        //
                        $IleCechProduktu++;
                        //
                    }
                    //
                    $db->close_query($sqlCechyProdukt);
                    unset($zapytanieCechyProdukt);                          
                    //
                }

                $db->close_query($sqlProdukt);
                unset($zapytanieIdProdukt);               
              
                if (isset($TablicaDane['Nazwa_wartosc_cechy']) && trim((string)$TablicaDane['Nazwa_wartosc_cechy']) != '' && $IleCechProduktu > 0) {
                    //
                    $IdKombinacjiCech = array();
                    //
                    $PodzielCechy = explode(',', trim((string)$TablicaDane['Nazwa_wartosc_cechy']));
                    //
                    if (count($PodzielCechy) == $IleCechProduktu) {
                        //
                        foreach ($PodzielCechy as $TmpCecha) {
                            //
                            // zabezpieczenie przed dwoma ::
                            if (strpos($TmpCecha, '::') > -1 ) {
                                $TmpCecha = str_replace('::',':#$#', $TmpCecha);
                            } else {
                                $TmpCecha = str_replace(':','#$#', $TmpCecha);
                            }
                            //
                            $PodzielCeche = explode('#$#', $TmpCecha);
                            //
                            if (count($PodzielCeche) == 2) {
                                //
                                // sprawdza czy nazwa cechy jest juz w bazie
                                $zapytanieCecha = "select products_options_id from products_options where products_options_name = '" . addslashes((string)$filtr->process(trim((string)$PodzielCeche[0]))) . "' and language_id = '" . (int)$_SESSION['domyslny_jezyk']['id'] . "'";
                                $sqlc = $db->open_query($zapytanieCecha);
                                //    
                                if ((int)$db->ile_rekordow($sqlc) > 0) {  
                                    //
                                    $info = $sqlc->fetch_assoc();
                                    //
                                    // sprawdza czy wartosc dla danej cechy w bazie
                                    $zapytanieCecha = "select pvp.products_options_values_id 
                                                         from products_options_values pv, products_options_values_to_products_options pvp
                                                        where pv.products_options_values_id = pvp.products_options_values_id and pvp.products_options_id = '" . $info['products_options_id'] . "' and pv.products_options_values_name = '" . addslashes((string)$filtr->process(trim((string)$PodzielCeche[count($PodzielCeche) - 1]))) . "' and language_id = '" . (int)$_SESSION['domyslny_jezyk']['id'] . "'";

                                    $sqlp = $db->open_query($zapytanieCecha);
                                    //
                                    if ((int)$db->ile_rekordow($sqlp) > 0) {  
                                        //
                                        $infq = $sqlp->fetch_assoc();             
                                        $IdKombinacjiCech[] = $info['products_options_id'] . '-' . $infq['products_options_values_id'];
                                        unset($infq);
                                        //  
                                    }
                                    //
                                    $db->close_query($sqlp);
                                    unset($info);                
                                    //
                                }
                                //
                                $db->close_query($sqlc);
                                //  
                            }
                            //
                            unset($PodzielCeche);
                            //
                        }
                        //
                        sort($IdKombinacjiCech);
                        //
                    } else {
                        //
                        $IdProduktuDlaCechy = 0;
                        //
                    }
                    //
                } else {
                    //
                    $IdProduktuDlaCechy = 0;
                    //
                }
                
            }

            if ($IdProduktuDlaCechy > 0 && isset($TablicaDane['Nr_katalogowy_cechy']) && trim((string)$TablicaDane['Nr_katalogowy_cechy'])) {

                if ($CzyDodawanie == false) {
                    //
                    $NrKatalogoweCech .= '<li><span>nr katalogowy:</span> ' . trim((string)$TablicaDane['Nr_katalogowy_cechy']) . '</li>';
                    //
                    $AktualizowanaIlosc++;
                    //
                }              

                $pola = array();
                
                if (isset($TablicaDane['Ilosc_produktow']) && trim((string)$TablicaDane['Ilosc_produktow']) != '' && CECHY_MAGAZYN == 'tak') {
                    //
                    $pola[] = array('products_stock_quantity',(float)$TablicaDane['Ilosc_produktow']);
                    //
                }
                
                if (isset($TablicaDane['Kod_ean_cechy']) && trim((string)$TablicaDane['Kod_ean_cechy'])) {
                    //
                    $pola[] = array('products_stock_ean',$filtr->process($TablicaDane['Kod_ean_cechy']));
                    //
                }                
                
                // stawka podatku vat
                $podatekVat = 0;
                //
                $zapytaniePodatek = "select t.tax_rate from tax_rates t, products p where p.products_tax_class_id = t.tax_rates_id and p.products_id = '" . $IdProduktuDlaCechy . "'";
                $sqlp = $db->open_query($zapytaniePodatek); 
                if ((int)$db->ile_rekordow($sqlp) > 0) {
                    //
                    $infp = $sqlp->fetch_assoc();
                    $podatekVat = $infp['tax_rate'];
                    //   
                    $db->close_query($sqlp);
                }   
                //                
                
                // ceny brutto kombinacji cech
                if (isset($TablicaDane['Cena_brutto_cechy']) && (float)($TablicaDane['Cena_brutto_cechy'] > 0)) {
                    //
                    // zaokraglanie cen
                    if (isset($_POST['zaokraglanie']) && trim((string)$_POST['zaokraglanie']) != '') {    
                        //
                        if ($_POST['zaokraglanie'] == 'zaokraglanie_cen_zero') {   
                            $TablicaDane['Cena_brutto_cechy'] = ceil((float)$TablicaDane['Cena_brutto_cechy']);
                        }
                        if ($_POST['zaokraglanie'] == 'zaokraglanie_cen_ulamek') {     
                            $TablicaDane['Cena_brutto_cechy'] = round((float)$TablicaDane['Cena_brutto_cechy'], 1);
                        }            
                        //
                    }
                    //
                    $pola[] = array('products_stock_price_tax',(float)$TablicaDane['Cena_brutto_cechy']);
                    //                
                    $netto = round(((float)$TablicaDane['Cena_brutto_cechy'] / (1 + ($podatekVat/100))), 2);
                    $podatek = (float)$TablicaDane['Cena_brutto_cechy'] - $netto;
                    //
                    $pola[] = array('products_stock_price',(float)$netto);
                    $pola[] = array('products_stock_tax',(float)$podatek);
                    //
                    unset($netto, $podatek);
                    //
                }
                
                // cena poprzednia
                if (isset($TablicaDane['Cena_poprzednia_cechy'])) {
                    //
                    $pola[] = array('products_stock_old_price',(float)$TablicaDane['Cena_poprzednia_cechy']);
                    //
                }
                
                // cena katalogowa
                if (isset($TablicaDane['Cena_katalogowa_cechy'])) {
                    //
                    $pola[] = array('products_stock_retail_price',(float)$TablicaDane['Cena_katalogowa_cechy']);   
                    //
                }                

                for ($w = 2; $w <= ILOSC_CEN ; $w++) {
                    
                    if (isset($TablicaDane['Cena_brutto_cechy_' . $w]) && (float)($TablicaDane['Cena_brutto_cechy_' . $w] > 0)) {
                        //
                        $pola[] = array('products_stock_price_tax_' . $w,(float)$TablicaDane['Cena_brutto_cechy_' . $w]);
                        //                
                        $netto = round(((float)$TablicaDane['Cena_brutto_cechy_' . $w] / (1 + ($podatekVat/100))), 2);
                        $podatek = (float)$TablicaDane['Cena_brutto_cechy_' . $w] - $netto;
                        //
                        $pola[] = array('products_stock_price_' . $w,(float)$netto);
                        $pola[] = array('products_stock_tax_' . $w,(float)$podatek);
                        //
                        unset($netto, $podatek);
                        //                        
                    }                
                
                    // cena poprzednia
                    if (isset($TablicaDane['Cena_poprzednia_cechy_' . $w])) {
                        //
                        $pola[] = array('products_stock_old_price_' . $w,(float)$TablicaDane['Cena_poprzednia_cechy_' . $w]);
                        //
                    }
                    
                    // cena katalogowa
                    if (isset($TablicaDane['Cena_katalogowa_cechy_' . $w])) {
                        //
                        $pola[] = array('products_stock_retail_price_' . $w,(float)$TablicaDane['Cena_katalogowa_cechy_' . $w]);   
                        //
                    } 
                
                }
                
                unset($podatekVat, $infp);
                
                if (isset($TablicaDane['Dostepnosc']) && trim((string)$TablicaDane['Dostepnosc']) != '') {
                    //
                    if ($filtr->process($TablicaDane['Dostepnosc']) == 'AUTOMATYCZNY') {
                        //
                        $pola[] = array('products_stock_availability_id','99999');       
                        //
                      } else {
                        //
                        // sprawdza czy dostepnosc jest juz w bazie
                        $zapytanieDostepnosc = "select p.products_availability_id, p.mode, pd.products_availability_id, pd.products_availability_name from products_availability p, products_availability_description pd where p.products_availability_id = pd.products_availability_id and p.mode = '0' and products_availability_name = '" . addslashes((string)$filtr->process($TablicaDane['Dostepnosc'])) . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                        $sqlc = $db->open_query($zapytanieDostepnosc);
                        //    
                        if ((int)$db->ile_rekordow($sqlc) > 0) {
                            //
                            $infe = $sqlc->fetch_assoc();
                            $pola[] = array('products_stock_availability_id',(int)$infe['products_availability_id']);
                            unset($infe);
                            //
                         } else {
                            //
                            // jezeli nie ma dostepnosci to doda ja do bazy
                            $pole = array(array('quantity','0')); 
                            $pole[] = array('mode','0');                        
                            $db->insert_query('products_availability' , $pole); 
                            $id_dodanej_dostepnosci = $db->last_id_query();
                            unset($pole);
                            //
                            $pole = array(
                                    array('products_availability_id',$id_dodanej_dostepnosci),
                                    array('language_id',$_SESSION['domyslny_jezyk']['id']),
                                    array('products_availability_name',$filtr->process($TablicaDane['Dostepnosc'])));           
                            $db->insert_query('products_availability_description' , $pole);  
                            unset($pole);
                            
                            // ---------------------------------------------------------------
                            // dodawanie do innych jezykow jak sa inne jezyki
                            for ($j = 0, $c = count($ile_jezykow); $j < $c; $j++) {
                                //
                                $kod_jezyka = $ile_jezykow[$j]['kod'];
                                //
                                $NazwaTmp = $filtr->process($TablicaDane['Dostepnosc']);
                                if (isset($TablicaDane['Dostepnosc_' . $kod_jezyka]) && trim((string)$TablicaDane['Dostepnosc_' . $kod_jezyka]) != '') {
                                    $NazwaTmp = $filtr->process($TablicaDane['Dostepnosc_' . $kod_jezyka]);
                                }
                                //
                                $pole = array(
                                        array('products_availability_id',$id_dodanej_dostepnosci),
                                        array('language_id',$ile_jezykow[$j]['id']),
                                        array('products_availability_name',$NazwaTmp));
                                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                    $sql = $db->insert_query('products_availability_description' , $pole);
                                }
                                unset($pole);              
                                //
                                unset($kod_jezyka, $NazwaTmp);
                                //
                            }  
                            
                            //
                            // dodanie id dostepnosci do bazy produktu
                            $pola[] = array('products_stock_availability_id',(int)$id_dodanej_dostepnosci);
                            // 
                            unset($id_dodanej_dostepnosci);
                        }
                        
                        $db->close_query($sqlc);
                        unset($zapytanieDostepnosc);
                        //
                    }
                    
                }     
                
                // termin wysylki
                if (isset($TablicaDane['Termin_wysylki']) && trim((string)$TablicaDane['Termin_wysylki']) != '') {
                  
                    // sprawdza czy termin wysylki jest w bazie
                    $zapytanieWysylka = "select p.products_shipping_time_id, pd.products_shipping_time_name from products_shipping_time p, products_shipping_time_description pd where p.products_shipping_time_id = pd.products_shipping_time_id and pd.products_shipping_time_name = '" . addslashes((string)$filtr->process($TablicaDane['Termin_wysylki'])) . "' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                    $sqlc = $db->open_query($zapytanieWysylka);
                    //
                    if ((int)$db->ile_rekordow($sqlc) > 0) {
                        //
                        $infe = $sqlc->fetch_assoc();
                        $pola[] = array('products_stock_shipping_time_id',(int)$infe['products_shipping_time_id']);
                        unset($infe);
                        //
                    }
                    
                    $db->close_query($sqlc);
                    unset($zapytanieWysylka);
                    
                }                

                if (isset($TablicaDane['Zdjecie']) && trim((string)$TablicaDane['Zdjecie']) != '') {
                    //
                    $pola[] = array('products_stock_image',$filtr->process($TablicaDane['Zdjecie']));
                    //
                }                
                
                if ($CzyDodawanie == false) {
                    //
                    $db->update_query('products_stock' , $pola, "products_stock_id = '" . $IdKombinacjiCechy . "'");
                    //
                } else {
                    //
                    if (count($IdKombinacjiCech) > 0) {
                         //
                         // sprawdza czy nazwa cechy jest juz w bazie
                         $zapytanieCecha = "select products_stock_attributes from products_stock where products_id = " . $IdProduktuDlaCechy . " and products_stock_attributes = '" . implode(',', $IdKombinacjiCech) . "'";
                         $sqlc = $db->open_query($zapytanieCecha);
                         //    
                         if ((int)$db->ile_rekordow($sqlc) == 0) {   
                            //
                            $pola[] = array('products_id',$IdProduktuDlaCechy);
                            $pola[] = array('products_stock_attributes',implode(',', $IdKombinacjiCech));
                            $pola[] = array('products_stock_model',$filtr->process(trim((string)$TablicaDane['Nr_katalogowy_cechy'])));
                            $db->insert_query('products_stock' , $pola);
                            //
                            $NrKatalogoweCech .= '<li><span>nr katalogowy:</span> ' . trim((string)$TablicaDane['Nr_katalogowy_cechy']) . '</li>';
                            //
                            $DodanaIlosc++;
                            //                            
                         }
                         //
                         $db->close_query($sqlc);
                         unset($zapytanieCecha);
                         //
                    }
                    //
                }
                
                unset($pola);
                
                if ( CECHY_MAGAZYN == 'tak' ) {
                    //
                    // trzeba takze zakualizowac ogolna ilosc stanu magazynowego produktu
                    $zapytanieIloscMagazynowa = "select products_stock_quantity from products_stock where products_id = '" . $IdProduktuDlaCechy . "'";
                    $sql_ilosc = $db->open_query($zapytanieIloscMagazynowa);
                    //
                    $iloscMag = 0;
                    while ($infp = $sql_ilosc->fetch_assoc()) {
                           //
                           $iloscMag = $iloscMag + $infp['products_stock_quantity'];
                           //
                    }
                    //
                    $pole = array(array('products_quantity',$iloscMag));
                    //
                    $db->update_query('products' , $pole, "products_id = '" . $IdProduktuDlaCechy . "'");                    
                    //
                    $db->close_query($sql_ilosc);
                    //
                    unset($infp, $zapytanieIloscMagazynowa, $iloscMag);             
                    //
                }
                //

            }

        }
    
    }
    
    echo json_encode( array("suma" => $imp, "dodane" => 0, 'aktualizacja' => $AktualizowanaIlosc, 'dodane' => $DodanaIlosc, 'nazwy' => $NrKatalogoweCech ) );

    unset( $imp, $AktualizowanaIlosc, $NrKatalogoweCech );
}
?>