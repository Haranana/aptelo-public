<?php
// gwarancja
if (isset($TablicaDane['Gwarancja']) && trim((string)$TablicaDane['Gwarancja']) != '') {
  
    // sprawdza czy jednostka jest juz w bazie
    $zapytanieGwarancja = "select wp.products_warranty_id, wpd.products_warranty_name from products_warranty wp, products_warranty_description wpd where wp.products_warranty_id = wpd.products_warranty_id and wpd.products_warranty_name = '" . addslashes((string)$filtr->process($TablicaDane['Gwarancja'])) . "' and wpd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
    $sqlc = $db->open_query($zapytanieGwarancja);
 
    if ((int)$db->ile_rekordow($sqlc) > 0) {
        //
        $info = $sqlc->fetch_assoc();
        $pola[] = array('products_warranty_products_id',$info['products_warranty_id']);
        unset($info);
        //
     } else {
       
        // jezeli nie ma gwarancji to doda ja do bazy
        $pole = array();   
        $db->insert_query('products_warranty' , $pole); 
        $id_dodanej_gwarancji = $db->last_id_query();
        unset($pole);
        //
        $pole = array(
                array('products_warranty_id',$id_dodanej_gwarancji),
                array('language_id',$_SESSION['domyslny_jezyk']['id']),
                array('products_warranty_name',$filtr->process($TablicaDane['Gwarancja'])));           
        $db->insert_query('products_warranty_description' , $pole);  
        unset($pole);
        
        // ---------------------------------------------------------------
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
            //
            $kod_jezyka = $ile_jezykow[$j]['kod'];
            //
            $NazwaTmp = $filtr->process($TablicaDane['Gwarancja']);
            if (isset($TablicaDane['Gwarancja_' . $kod_jezyka]) && trim((string)$TablicaDane['Gwarancja_' . $kod_jezyka]) != '') {
                $NazwaTmp = $filtr->process($TablicaDane['Gwarancja_' . $kod_jezyka]);
            }
            //
            $pole = array(
                    array('products_warranty_id',$id_dodanej_gwarancji),
                    array('language_id',$ile_jezykow[$j]['id']),
                    array('products_warranty_name',$NazwaTmp));
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('products_warranty_description' , $pole);
            }
            unset($pole);                 
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }        
        
        //
        // dodanie do bazy produktu
        $pola[] = array('products_warranty_products_id',$id_dodanej_gwarancji);
        // 
        unset($id_dodanej_gwarancji);
    }
    
    $db->close_query($sqlc);
    unset($zapytanieGwarancja);
}  
?>