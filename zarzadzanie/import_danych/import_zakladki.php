<?php
// dodatkowe zakladki
for ($w = 1; $w < 5 ; $w++) {
    //
    if ((isset($TablicaDane['Dodatkowa_zakladka_'.$w.'_nazwa']) && trim((string)$TablicaDane['Dodatkowa_zakladka_'.$w.'_nazwa']) != '') && (isset($TablicaDane['Dodatkowa_zakladka_'.$w.'_opis']) && trim((string)$TablicaDane['Dodatkowa_zakladka_'.$w.'_opis']) != '')) {
    
        // sprawdza czy takie pole jest juz w bazie
        $dodajBoNieMa = false;
        //
        if ( $CzyDodawanie == false ) {
            //
            $zapytanieSpr = "select products_info_id from products_info where products_id = '" . $id_aktualizowanej_pozycji . "' and products_info_id = '".$w."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
            $sqlSpr = $db->open_query($zapytanieSpr);  
            if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                $dodajBoNieMa = true;
            }
            $db->close_query($sqlSpr);
            //
        }
        
        //
        $pola = array(
                array('products_info_id',$w),
                array('products_info_name',$filtr->process($TablicaDane['Dodatkowa_zakladka_'.$w.'_nazwa'])),
                array('products_info_description',$filtr->process($TablicaDane['Dodatkowa_zakladka_'.$w.'_opis'])));
                
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
        }                

        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $db->insert_query('products_info', $pola); 
          } else {
            $db->update_query('products_info' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_info_id = '".$w."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
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
                $zapytanieSpr = "select products_info_id from products_info where products_id = '" . $id_aktualizowanej_pozycji . "' and products_info_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'";
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
            $OpisTmp = '';
            if ((isset($TablicaDane['Dodatkowa_zakladka_'.$w.'_nazwa_' . $kod_jezyka]) && trim((string)$TablicaDane['Dodatkowa_zakladka_'.$w.'_nazwa_' . $kod_jezyka]) != '') && (isset($TablicaDane['Dodatkowa_zakladka_'.$w.'_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Dodatkowa_zakladka_'.$w.'_opis_' . $kod_jezyka]) != '')) {
                $NazwaTmp = $filtr->process($TablicaDane['Dodatkowa_zakladka_'.$w.'_nazwa_' . $kod_jezyka]);
                $OpisTmp = $filtr->process($TablicaDane['Dodatkowa_zakladka_'.$w.'_opis_' . $kod_jezyka]);
            }
            //
            $pola = array(
                    array('products_info_id',$w),
                    array('products_info_name',$NazwaTmp),
                    array('products_info_description',$OpisTmp)); 
                    
            if ($CzyDodawanie == true || $dodajBoNieMa == true) {
                $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                $pola[] = array('language_id',$ile_jezykow[$j]['id']); 
            }                        
                    
            if (($CzyDodawanie == true || $dodajBoNieMa == true) && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('products_info', $pola); 
              } else if ($NazwaTmp != '' && $OpisTmp != '') {
                $db->update_query('products_info' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_info_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'");
            }                        
            unset($pola, $dodajBoNieMa, $zapytanieSpr);             
            //
            unset($kod_jezyka,$NazwaTmp,$OpisTmp);
            //
        }        
    }
    // 
}

?>