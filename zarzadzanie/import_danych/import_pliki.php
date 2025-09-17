<?php
// pliki
for ($w = 1; $w < 11 ; $w++) {
    //
    if ((isset($TablicaDane['Plik_'.$w.'_nazwa']) && trim((string)$TablicaDane['Plik_'.$w.'_nazwa']) != '') && (isset($TablicaDane['Plik_'.$w.'_plik']) && trim((string)$TablicaDane['Plik_'.$w.'_plik']) != '')) {
    
        // sprawdza czy takie pole jest juz w bazie
        $dodajBoNieMa = false;
        //
        if ( $CzyDodawanie == false ) {
            //
            $zapytanieSpr = "select products_file_id from products_file where products_id = '" . $id_aktualizowanej_pozycji . "' and products_file_id = '".$w."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
            $sqlSpr = $db->open_query($zapytanieSpr);  
            if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                $dodajBoNieMa = true;
            }
            $db->close_query($sqlSpr);
            //
        }
        
        //
        $pola = array(
                array('products_file_id',$w),
                array('products_file_name',$filtr->process($TablicaDane['Plik_'.$w.'_nazwa'])),
                array('products_file',$filtr->process($TablicaDane['Plik_'.$w.'_plik'])));
                
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
        }                   
                
        if (isset($TablicaDane['Plik_'.$w.'_opis']) && trim((string)$TablicaDane['Plik_'.$w.'_opis']) != '') {
            //
            $pola[] = array('products_file_description',$filtr->process($TablicaDane['Plik_'.$w.'_opis']));
            //
        }
        if (isset($TablicaDane['Plik_'.$w.'_logowanie']) && trim((string)$TablicaDane['Plik_'.$w.'_logowanie']) != '') {
            //
            if (strtolower((string)$TablicaDane['Plik_'.$w.'_logowanie']) == 'tak') {
                $pola[] = array('products_file_login','1');
              } else {
                $pola[] = array('products_file_login','0');
            }
            //
          } else {
            $pola[] = array('products_file_login','0');
        }                    
                   
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $db->insert_query('products_file', $pola); 
          } else {
            $db->update_query('products_file' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_file_id = '".$w."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        }        
        unset($pola, $dodajBoNieMa, $zapytanieSpr);
        
        // ---------------------------------------------------------------
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
        
            // sprawdza czy takie pole jest juz w bazie
            $dodajBoNieMa = false;
            //
            if ( $CzyDodawanie == false ) {
                //
                $zapytanieSpr = "select products_file_id from products_file where products_id = '" . $id_aktualizowanej_pozycji . "' and products_file_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'";
                $sqlSpr = $db->open_query($zapytanieSpr);  
                if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                    $dodajBoNieMa = true;
                }
                $db->close_query($sqlSpr);
                //
            }        
        
            //
            $kod_jezyka = $ile_jezykow[$j]['kod'];
            //
            $NazwaTmp = '';
            if (isset($TablicaDane['Plik_'.$w.'_nazwa_' . $kod_jezyka]) && trim((string)$TablicaDane['Plik_'.$w.'_nazwa_' . $kod_jezyka]) != '') {
                $NazwaTmp = $filtr->process($TablicaDane['Plik_'.$w.'_nazwa_' . $kod_jezyka]);
            }
            //
            $pola = array(
                    array('products_file_id',$w),
                    array('products_file_name',$NazwaTmp),
                    array('products_file',$filtr->process($TablicaDane['Plik_'.$w.'_plik']))); 

            if ($CzyDodawanie == true || $dodajBoNieMa == true) {
                $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                $pola[] = array('language_id',$ile_jezykow[$j]['id']); 
            }                             
                    
            if (isset($TablicaDane['Plik_'.$w.'_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Plik_'.$w.'_opis_' . $kod_jezyka]) != '') {
                //
                $pola[] = array('products_file_description',$filtr->process($TablicaDane['Plik_'.$w.'_opis_' . $kod_jezyka]));
                //
            }
            if (isset($TablicaDane['Plik_'.$w.'_logowanie']) && trim((string)$TablicaDane['Plik_'.$w.'_logowanie']) != '') {
                //
                if (strtolower((string)$TablicaDane['Plik_'.$w.'_logowanie']) == 'tak') {
                    $pola[] = array('products_file_login','1');
                  } else {
                    $pola[] = array('products_file_login','0');
                }
                //
              } else {
                $pola[] = array('products_file_login','0');
            } 
            
            if (($CzyDodawanie == true || $dodajBoNieMa == true) && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('products_file', $pola); 
              } else if ($NazwaTmp != '') {
                $db->update_query('products_file' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_file_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'");
            }                     
            unset($pola, $dodajBoNieMa, $zapytanieSpr);               
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }           
    }
    // 
}

?>