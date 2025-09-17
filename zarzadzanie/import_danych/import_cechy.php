<?php
// jezeli jest aktualizacja to sklep sprawdzi czy takie cechy sa juz w bazie, jezeli ich nie bedzie to przed dopisaniem cech skasuje wszystkie cechy dla danego produktu z bazy
if ($CzyDodawanie == false) {
    //
    $TablicaCechNazw = array();
    $TablicaCechWartosci = array();
    //
    for ($idCechy = 1; $idCechy < 100; $idCechy++) {
        //
        if ((isset($TablicaDane['Cecha_nazwa_'.$idCechy]) && trim((string)$TablicaDane['Cecha_nazwa_'.$idCechy]) != '') && (isset($TablicaDane['Cecha_wartosc_'.$idCechy]) && trim((string)$TablicaDane['Cecha_wartosc_'.$idCechy]) != '')) {
            //
            // sprawdza czy nazwa cechy jest juz w bazie
            $zapytanieCecha = "select products_options_id, products_options_name from products_options where products_options_name = '" . addslashes((string)$filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy])) . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
            $sqlc = $db->open_query($zapytanieCecha);
            //    
            if ((int)$db->ile_rekordow($sqlc) > 0) {  
                //
                $info = $sqlc->fetch_assoc();
                //
                $TablicaCechNazw[] = $info['products_options_id'];
                //
                // sprawdza czy wartosc dla danej cechy w bazie
                $zapytanieCecha = "select 
                                        pv.products_options_values_id, 
                                        pv.products_options_values_name, 
                                        pvp.products_options_id, 
                                        pvp.products_options_values_id 
                                   from products_options_values pv, products_options_values_to_products_options pvp
                                   where pv.products_options_values_id = pvp.products_options_values_id and pvp.products_options_id = '" . $info['products_options_id'] . "' and pv.products_options_values_name = '" . addslashes((string)$filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy])) . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";

                $sqlp = $db->open_query($zapytanieCecha);
                //
                if ((int)$db->ile_rekordow($sqlp) > 0) { 
                    //
                    $infq = $sqlp->fetch_assoc();             
                    $TablicaCechWartosci[] = $infq['products_options_values_id'];
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
    }
    //
    $TrzebaSkasowac = false;
    $licznikCech = 0;
    //
    for ($w = 0, $c = count($TablicaCechNazw); $w < $c; $w++) {
        //
        // sprawdza czy takie cechy sa przypisane do produktu
        $zapytanieCechaProdukt = "select products_attributes_id from products_attributes where options_id = '" . $TablicaCechNazw[$w] . "' and options_values_id = '" . $TablicaCechWartosci[$w] . "' and products_id = '" . $id_aktualizowanej_pozycji ."'";
        $sqlq = $db->open_query($zapytanieCechaProdukt);
        //    
        if ((int)$db->ile_rekordow($sqlq) == 0) {
            $TrzebaSkasowac = true;
           } else {
            $licznikCech++;
        }
        //
        $db->close_query($sqlq);
        unset($zapytanieCechaProdukt);
        //
    }
    //
    if ($licznikCech != count($TablicaCechNazw)) {
        $TrzebaSkasowac = true;
    }
    
    if ($TrzebaSkasowac == true) {
        // kasuje rekordy w tablicy jezeli aktualizacja
        $db->delete_query('products_attributes' , " products_id = '".(int)$id_aktualizowanej_pozycji."'");        
        $db->delete_query('products_stock' , " products_id = '".(int)$id_aktualizowanej_pozycji."'"); 
        //
    }
}    

// cechy produktu
for ($idCechy = 1; $idCechy < 100; $idCechy++) {
    //
    if ((isset($TablicaDane['Cecha_nazwa_'.$idCechy]) && trim((string)$TablicaDane['Cecha_nazwa_'.$idCechy]) != '') && (isset($TablicaDane['Cecha_wartosc_'.$idCechy]) && trim((string)$TablicaDane['Cecha_wartosc_'.$idCechy]) != '')) {
        //
        // sprawdza czy nazwa cechy jest juz w bazie
        $zapytanieCecha = "select products_options_id, products_options_name from products_options where products_options_name = '" . $filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy]) . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sqlc = $db->open_query($zapytanieCecha);
        //    
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            //
            $info = $sqlc->fetch_assoc();
            $idNazwyCechy = $info['products_options_id'];
            //               
            unset($info);
            //
         } else {
            // jezeli nie ma nazwy cechy to doda ja do bazy
            // okreslanie kolejnego nr ID
            $zapytanie_cechy = "select max(products_options_id) + 1 as next_id from products_options";
            $sqls = $db->open_query($zapytanie_cechy);
            $wynik = $sqls->fetch_assoc();    
            $kolejne_id = $wynik['next_id'];
            $db->close_query($sqls);  
            //
            if ( (int)$kolejne_id == 0 ) {
                 $kolejne_id = 1;
            }
            //            
            $pole = array(
                    array('products_options_id',(int)$kolejne_id),
                    array('products_options_name',$filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy])),
                    array('products_options_images_enabled','false'),
                    array('products_options_type','radio'),
                    array('products_options_value','kwota'),
                    array('language_id',(int)$_SESSION['domyslny_jezyk']['id'])
                    );   
            $db->insert_query('products_options' , $pole); 
            $idNazwyCechy = $kolejne_id;
            unset($pole,$wynik);
            
            // ---------------------------------------------------------------
            // dodawanie do innych jezykow jak sa inne jezyki
            for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                //
                $kod_jezyka = $ile_jezykow[$j]['kod'];
                //
                $NazwaTmp = $filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy]);
                if (isset($TablicaDane['Cecha_nazwa_'.$idCechy.'_' . $kod_jezyka]) && trim((string)$TablicaDane['Cecha_nazwa_'.$idCechy.'_' . $kod_jezyka]) != '') {
                    $NazwaTmp = $filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy.'_' . $kod_jezyka]);
                }
                //
                $pole = array(
                        array('products_options_id',(int)$kolejne_id),
                        array('products_options_name',$NazwaTmp),
                        array('products_options_images_enabled','false'),
                        array('products_options_type','radio'),
                        array('products_options_value','kwota'),
                        array('language_id',(int)$ile_jezykow[$j]['id'])
                        );       
                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                    $db->insert_query('products_options', $pole);  
                }
                unset($pole);               
                //
                unset($kod_jezyka, $NazwaTmp);
                //
            }      
            unset($pole,$kolejne_id);            
            
            //
        }
        
        $db->close_query($sqlc);
        unset($zapytanieCecha);        
        
        // bedzie szukal teraz czy jest wartosc dla danej cechy
        //
        $zapytanieCecha = "select 
                                pv.products_options_values_id, 
                                pv.products_options_values_name, 
                                pvp.products_options_id, 
                                pvp.products_options_values_id 
                           from products_options_values pv, products_options_values_to_products_options pvp
                           where pv.products_options_values_id = pvp.products_options_values_id and pvp.products_options_id = '" . $idNazwyCechy . "' and pv.products_options_values_name = '" . $filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy]) . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";

        $sqlc = $db->open_query($zapytanieCecha);
        //
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            //
            $info = $sqlc->fetch_assoc();
            $idWartoscCechy = $info['products_options_values_id'];
            //   
            unset($info);
            //
         } else {
            // jezeli nie ma wartosci cechy to doda je do bazy
            //
            // okreslanie kolejnego nr ID
            $zapytanie_cechy = "select max(products_options_values_id) + 1 as next_id from products_options_values";
            $sqls = $db->open_query($zapytanie_cechy);
            $wynik = $sqls->fetch_assoc();    
            $kolejne_id = $wynik['next_id'];
            $db->close_query($sqls);
            //
            if ( (int)$kolejne_id == 0 ) {
                 $kolejne_id = 1;
            }
            //    
            $pole = array(
                    array('products_options_values_id',(int)$kolejne_id),
                    array('language_id',(int)$_SESSION['domyslny_jezyk']['id']),
                    array('products_options_values_name',$filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy]))
                    );   
            $db->insert_query('products_options_values' , $pole); 
            $idWartoscCechy = $kolejne_id;
            //
            unset($pole,$wynik);
            //
            
            // ---------------------------------------------------------------
            // dodawanie do innych jezykow jak sa inne jezyki
            for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                //
                $kod_jezyka = $ile_jezykow[$j]['kod'];
                //
                $NazwaTmp = $filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy]);
                if (isset($TablicaDane['Cecha_wartosc_'.$idCechy.'_' . $kod_jezyka]) && trim((string)$TablicaDane['Cecha_wartosc_'.$idCechy.'_' . $kod_jezyka]) != '') {
                    $NazwaTmp = $filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy.'_' . $kod_jezyka]);
                }
                //
                $pole = array(
                        array('products_options_values_id',(int)$kolejne_id),
                        array('language_id',(int)$ile_jezykow[$j]['id']),
                        array('products_options_values_name',$NazwaTmp)
                        );
                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                    $db->insert_query('products_options_values', $pole);  
                }
                unset($pole);                 
                //
                unset($kod_jezyka, $NazwaTmp);
                //
            }  
            unset($pole,$kolejne_id);
            
        }
        
        $db->close_query($sqlc);
        unset($zapytanieCecha);
                
        // sprawdza czy jest juz bazie polaczenie nazwy cechy i wartosci cechy
        $zapytanieCecha = "select * from products_options_values_to_products_options 
                           where products_options_id = '" . $idNazwyCechy . "' and products_options_values_id = '" . $idWartoscCechy . "'";
        $sqlc = $db->open_query($zapytanieCecha);
        //
        if ((int)$db->ile_rekordow($sqlc) == 0) {        
            // wpis do bazy - polaczenie nazwy cechy i wartosci
            $pole = array(
                    array('products_options_id',(int)$idNazwyCechy),
                    array('products_options_values_id',(int)$idWartoscCechy));
            $db->insert_query('products_options_values_to_products_options', $pole);
            unset($pole);        
            //
        }
        
        $db->close_query($sqlc);
        unset($zapytanieCecha);
        
        // przypisanie cechy do produktu
        $pole = array(
                array('products_id',(($CzyDodawanie == true) ? (int)$id_dodanej_pozycji : (int)$id_aktualizowanej_pozycji)),
                array('options_id',(int)$idNazwyCechy),
                array('options_values_id',(int)$idWartoscCechy));
                
        // jezeli cecha ma cene
        if (isset($TablicaDane['Cecha_cena_'.$idCechy]) && (float)str_replace( array('-', '+', '*'), '', (string)$TablicaDane['Cecha_cena_'.$idCechy] ) != 0) {            
            //
            // ustalanie prefixu
            if (strpos((string)$TablicaDane['Cecha_cena_'.$idCechy], '-') > -1 ) {
                $pole[] = array('price_prefix','-');
            }
            if (strpos((string)$TablicaDane['Cecha_cena_'.$idCechy], '+') > -1 ) {
                $pole[] = array('price_prefix','+');
            }
            if (strpos((string)$TablicaDane['Cecha_cena_'.$idCechy], '*') > -1 ) {
                $pole[] = array('price_prefix','*');
            }         
            //
            $TablicaDane['Cecha_cena_'.$idCechy] = str_replace( array('-', '+', '*'), '', (string)$TablicaDane['Cecha_cena_'.$idCechy] );
            //
            $pole[] = array('options_values_price_tax',abs((float)$TablicaDane['Cecha_cena_'.$idCechy]));
            //
            // przeliczanie ceny na netto i vat
            //
            $netto = round((abs((float)$TablicaDane['Cecha_cena_'.$idCechy]) / (1 + ((float)$wartoscPodatkuDlaProduktu/100))), 2);
            $podatek = abs((float)$TablicaDane['Cecha_cena_'.$idCechy]) - $netto;
            //
            $pole[] = array('options_values_price',(float)$netto);
            $pole[] = array('options_values_tax',(float)$podatek);
            //
            unset($netto, $podatek);
            //            
        }
        
        // jezeli cecha ma wage
        if (isset($TablicaDane['Cecha_waga_'.$idCechy]) && (float)$TablicaDane['Cecha_waga_'.$idCechy] > 0) {
            $pole[] = array('options_values_weight',abs((float)$TablicaDane['Cecha_waga_'.$idCechy]));        
        }
        
        // jezeli cecha ma zdjecie
        if (isset($TablicaDane['Cecha_foto_'.$idCechy]) && $TablicaDane['Cecha_foto_'.$idCechy] != '') {
            $pole[] = array('options_values_image',$filtr->process($TablicaDane['Cecha_foto_'.$idCechy]));        
        }        
        
        // jezeli jest domyslna
        if (isset($TablicaDane['Cecha_domyslna_'.$idCechy]) && $TablicaDane['Cecha_domyslna_'.$idCechy] == 'tak') {
            $pole[] = array('options_default',1);        
        } else {
            $pole[] = array('options_default',0);
        }
        
        // sprawdza czy trzeba dopisac cechy do produkty czy tylko zaktualizowac
        if ($CzyDodawanie == false && $TrzebaSkasowac == false) {
            $db->update_query('products_attributes', $pole, " options_id = '" . $idNazwyCechy . "' and options_values_id = '" . $idWartoscCechy . "' and products_id = '" . $id_aktualizowanej_pozycji . "'");
          } else {
            $db->insert_query('products_attributes', $pole);
        }
        unset($pole);    

        // dodanie do tablicy stock 
        if ( isset($TablicaDane['Cecha_stock_id_'.$idCechy]) ) {
             //             
             $pole = array(
                     array('products_id',(($CzyDodawanie == true) ? (int)$id_dodanej_pozycji : (int)$id_aktualizowanej_pozycji)),
                     array('products_stock_model',$TablicaDane['Cecha_stock_id_'.$idCechy]), 
                     array('products_stock_attributes',$idNazwyCechy . '-' . $idWartoscCechy),
                     array('products_stock_quantity',(float)$TablicaDane['Cecha_stock_ilosc_'.$idCechy]),             
                     array('products_stock_ean',$TablicaDane['Cecha_stock_ean_'.$idCechy]),
                     array('products_stock_image',$TablicaDane['Cecha_stock_foto_'.$idCechy]));
             //
             // products_stock_price, products_stock_tax, products_stock_price_tax
             //
             if ( isset($TablicaDane['Cecha_stock_cena_brutto_'.$idCechy]) ) {
                  //
                  $marza = 1;
                  $dodatek = 0;
                  //
                  if ( isset($DodatekDoCeny) && (float)$DodatekDoCeny > 0 ) {
                       $dodatek = (float)$DodatekDoCeny;
                  }
                  //
                  if (isset($_POST['marza']) && (float)$_POST['marza'] != 0) {
                      //
                      $marza = ((100 + (float)$_POST['marza']) / 100);
                      //
                  }
                  if (isset($WartoscMarza) && $WartoscMarza != 0) {
                      //
                      $marza = ((100 + (float)$WartoscMarza) / 100);
                      //
                  }                  
                  //
                  $pole[] = array('products_stock_price_tax',abs(((float)$TablicaDane['Cecha_stock_cena_brutto_'.$idCechy] * $marza) + $dodatek));
                  //
                  // przeliczanie ceny na netto i vat
                  //
                  $netto = round((abs(((float)$TablicaDane['Cecha_stock_cena_brutto_' . $idCechy] * $marza) + $dodatek) / (1 + ($wartoscPodatkuDlaProduktu/100))), 2);
                  $podatek = abs(((float)$TablicaDane['Cecha_stock_cena_brutto_' . $idCechy] * $marza) + $dodatek) - $netto;
                  //
                  $pole[] = array('products_stock_price',(float)$netto);
                  $pole[] = array('products_stock_tax',(float)$podatek);
                  //
                  unset($netto, $podatek, $marza, $dodatek);
                  //
             } else {
                  //
                  $pole[] = array('products_stock_price_tax',0);
                  $pole[] = array('products_stock_price',0);
                  $pole[] = array('products_stock_tax',0);                  
                  //
             }
             //
             // jezeli jest aktualizacja
             if ($CzyDodawanie == false) {
                 //
                 // sprawdza czy takie cechy sa przypisane do produktu
                 $zapytanieCechaStock = "select products_stock_id from products_stock where products_stock_attributes = '" . $idNazwyCechy . '-' . $idWartoscCechy . "' and products_id = '" . $id_aktualizowanej_pozycji . "'";
                 $sqlq = $db->open_query($zapytanieCechaStock);
                 //    
                 if ((int)$db->ile_rekordow($sqlq) == 0) {             
                     //
                     $db->insert_query('products_stock', $pole);
                     //
                 } else {
                     //
                     $infc = $sqlq->fetch_assoc();
                     //
                     $db->update_query('products_stock', $pole, 'products_stock_id = ' . (int)$infc['products_stock_id']);
                     //
                     unset($infc);
                     //
                 }
                 //
                 $db->close_query($sqlq);
                 unset($zapytanieCechaStock);
                 //
             } else {
                 //
                 $db->insert_query('products_stock', $pole);
                 //
             }
             //
        }
  
    }  
    //
}

// wiecej cech - JEZELI JEST WIECEJ NIZ 1 CECHA !!!

for ($idKombinacji = 1; $idKombinacji < 1000; $idKombinacji++) {
  
    if ( isset($TablicaDane['Cecha_kombinacje_id_'.$idKombinacji]) ) {
         //
         $IdZapisuKombinacji = array();
         //
         $TablicaKombinacji = explode('#', (string)$TablicaDane['Cecha_kombinacje_id_'.$idKombinacji]);
         //
         foreach ( $TablicaKombinacji as $TmpKombinacja ) {
              //
              $PodzialTmpKombinacje = explode(';', (string)$TmpKombinacja);
              
              if ( count($PodzialTmpKombinacje) == 2 ) {
                  //
                  $idNazwyCechy = 0;
                  $idWartoscCechy = 0;
                  //                  
                  // id nazwy cechy
                  //
                  $zapytanieCecha = "select products_options_id from products_options where products_options_name = '" . $PodzialTmpKombinacje[0] . "' and language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";
                  $sqlc = $db->open_query($zapytanieCecha);
                  //
                  $idNazwyCechy = 0;
                  //
                  if ((int)$db->ile_rekordow($sqlc) > 0) {   
                      //
                      $info = $sqlc->fetch_assoc();
                      $idNazwyCechy = $info['products_options_id'];
                      //
                  }
                  //   
                  $db->close_query($sqlc);
                  unset($info);                  
                  //
                  // id wartosci cechy
                  //
                  $zapytanieCecha = "select pvp.products_options_values_id 
                                       from products_options_values pv, products_options_values_to_products_options pvp
                                      where pv.products_options_values_id = pvp.products_options_values_id and pvp.products_options_id = '" . $idNazwyCechy . "' and pv.products_options_values_name = '" . $PodzialTmpKombinacje[1] . "' and language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";

                  $sqlc = $db->open_query($zapytanieCecha);
                  //
                  $idWartoscCechy = 0;
                  //
                  if ((int)$db->ile_rekordow($sqlc) > 0) {            
                      //
                      $info = $sqlc->fetch_assoc();
                      $idWartoscCechy = $info['products_options_values_id'];
                      //
                  }
                  //   
                  $db->close_query($sqlc);
                  unset($info); 
                  //
                  if ( $idNazwyCechy > 0 && $idWartoscCechy > 0 ) {
                       //
                       $IdZapisuKombinacji[] = $idNazwyCechy . '-' . $idWartoscCechy;
                       //
                  }
                  //
                  unset($PodzialTmbKombinacje);
                  //
              }
              //
         }
         //
         if ( count($IdZapisuKombinacji) > 0 ) {
               
             natsort($IdZapisuKombinacji);
             $IdZapisuKombinacji = implode(',', (array)$IdZapisuKombinacji);
             //
             unset($TablicaKombinacji);
             //        
            
             $pole = array(
                     array('products_id',(($CzyDodawanie == true) ? (int)$id_dodanej_pozycji : (int)$id_aktualizowanej_pozycji)),
                     array('products_stock_model',$TablicaDane['Cecha_kombinacje_stock_id_'.$idKombinacji]), 
                     array('products_stock_attributes',$IdZapisuKombinacji),
                     array('products_stock_quantity',((isset($TablicaDane['Cecha_kombinacje_stock_ilosc_'.$idKombinacji])) ? (float)$TablicaDane['Cecha_kombinacje_stock_ilosc_'.$idKombinacji] : '')),             
                     array('products_stock_ean',((isset($TablicaDane['Cecha_kombinacje_stock_ean_'.$idKombinacji])) ? $TablicaDane['Cecha_kombinacje_stock_ean_'.$idKombinacji] : '')),
                     array('products_stock_image',((isset($TablicaDane['Cecha_kombinacje_stock_foto_'.$idKombinacji])) ? $TablicaDane['Cecha_kombinacje_stock_foto_'.$idKombinacji] : '')));                     
             //
             // products_stock_price, products_stock_tax, products_stock_price_tax
             //
             if ( isset($TablicaDane['Cecha_kombinacje_stock_cena_brutto_'.$idKombinacji]) ) {
                  //
                  $pole[] = array('products_stock_price_tax',abs((float)$TablicaDane['Cecha_kombinacje_stock_cena_brutto_'.$idKombinacji]));
                  //
                  // przeliczanie ceny na netto i vat
                  //
                  $netto = round((abs((float)$TablicaDane['Cecha_kombinacje_stock_cena_brutto_' . $idKombinacji]) / (1 + ((float)$wartoscPodatkuDlaProduktu/100))), 2);
                  $podatek = abs((float)$TablicaDane['Cecha_kombinacje_stock_cena_brutto_' . $idKombinacji]) - $netto;
                  //
                  $pole[] = array('products_stock_price',(float)$netto);
                  $pole[] = array('products_stock_tax',(float)$podatek);
                  //
                  unset($netto, $podatek);
                  //
                  if ( isset($TablicaDane['Cecha_kombinacje_stock_cena_poprzednia_'.$idKombinacji]) ) {
                       //
                       $pole[] = array('products_stock_old_price',abs((float)$TablicaDane['Cecha_kombinacje_stock_cena_poprzednia_'.$idKombinacji]));
                       //
                  }
                  //
             } else {
                  //
                  $pole[] = array('products_stock_price_tax',0);
                  $pole[] = array('products_stock_price',0);
                  $pole[] = array('products_stock_tax',0);                  
                  //
             }
             //
             // jezeli jest aktualizacja
             if ($CzyDodawanie == false) {
                 //
                 // sprawdza czy takie cechy sa przypisane do produktu
                 $zapytanieCechaStock = "select products_stock_id from products_stock where products_stock_attributes = '" . $IdZapisuKombinacji . "' and products_id = '" . $id_aktualizowanej_pozycji . "'";
                 $sqlq = $db->open_query($zapytanieCechaStock);
                 //    
                 if ((int)$db->ile_rekordow($sqlq) == 0) {             
                     //
                     $db->insert_query('products_stock', $pole);
                     //
                 } else {
                     //
                     $infc = $sqlq->fetch_assoc();
                     //
                     $db->update_query('products_stock', $pole, 'products_stock_id = ' . (float)$infc['products_stock_id']);
                     //
                     unset($infc);
                     //
                 }
                 //
                 $db->close_query($sqlq);
                 unset($zapytanieCechaStock);
                 //
             } else {
                 //
                 $db->insert_query('products_stock', $pole);
                 //
             }
             
         }
         
    }        

}

// rodzaj cech w produkcie
if ( isset($TablicaDane['rodzaj_cech']) ) {
  
    $pole = array(array('options_type',(($TablicaDane['rodzaj_cech'] == 'ceny') ? 'ceny' : 'cechy')));        
    $db->update_query('products', $pole, "products_id = '" . (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji) . "'");

}

?>