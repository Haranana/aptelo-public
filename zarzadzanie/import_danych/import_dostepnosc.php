<?php
// termin wysylki
if (isset($TablicaDane['Termin_wysylki']) && trim((string)$TablicaDane['Termin_wysylki']) != '') {
  
    // sprawdza czy termin wysylki jest w bazie
    $zapytanieWysylka = "select p.products_shipping_time_id, pd.products_shipping_time_name from products_shipping_time p, products_shipping_time_description pd where p.products_shipping_time_id = pd.products_shipping_time_id and pd.products_shipping_time_name = '" . addslashes((string)$filtr->process($TablicaDane['Termin_wysylki'])) . "' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
    $sqlc = $db->open_query($zapytanieWysylka);
    //
    if ((int)$db->ile_rekordow($sqlc) > 0) {
        //
        $info = $sqlc->fetch_assoc();
        $pola[] = array('products_shipping_time_id',$info['products_shipping_time_id']);
        //           
        unset($info);
        //
    }
    //
    $db->close_query($sqlc);
    unset($zapytanieWysylka);

}

// dostepnosc produktu
if (isset($TablicaDane['Dostepnosc']) && trim((string)$TablicaDane['Dostepnosc']) != '') {
    //
    if ($filtr->process($TablicaDane['Dostepnosc']) == 'AUTOMATYCZNY') {
        //
        $pola[] = array('products_availability_id','99999');       
        //
      } else {
        //
        // sprawdza czy dostepnosc jest juz w bazie
        $zapytanieDostepnosc = "select p.products_availability_id, p.mode, pd.products_availability_id, pd.products_availability_name from products_availability p, products_availability_description pd where p.products_availability_id = pd.products_availability_id and p.mode = '0' and pd.products_availability_name = '" . addslashes((string)$filtr->process($TablicaDane['Dostepnosc'])) . "' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sqlc = $db->open_query($zapytanieDostepnosc);
        //    
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            //
            $info = $sqlc->fetch_assoc();
            $pola[] = array('products_availability_id',$info['products_availability_id']);
            unset($info);
            //
         } else {
            //
            // jezeli nie ma dostepnosci to doda ja do bazy
            $pole = array(array('quantity','0')); 
            $pole[] = array('mode','0');            
            $db->insert_query('products_availability' , $pole); 
            //
            $id_dodanej_dostepnosci = $db->last_id_query();
            //
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
            for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
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
            $pola[] = array('products_availability_id',$id_dodanej_dostepnosci);
            // 
            unset($id_dodanej_dostepnosci);
        }
        
        $db->close_query($sqlc);
        unset($zapytanieDostepnosc);
        //
    }
} 
?>