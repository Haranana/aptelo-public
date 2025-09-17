<?php
// stan produktu
if (isset($TablicaDane['Stan_produktu']) && trim((string)$TablicaDane['Stan_produktu']) != '') {
  
    // sprawdza czy jednostka jest juz w bazie
    $zapytanieStan = "select cp.products_condition_id, cpd.products_condition_name from products_condition cp, products_condition_description cpd where cp.products_condition_id = cpd.products_condition_id and cpd.products_condition_name = '" . addslashes((string)$filtr->process($TablicaDane['Stan_produktu'])) . "' and cpd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
    $sqlc = $db->open_query($zapytanieStan);
    //    
    if ((int)$db->ile_rekordow($sqlc) > 0) {

        $info = $sqlc->fetch_assoc();
        $pola[] = array('products_condition_products_id',$info['products_condition_id']);
        unset($info);
        
     } else {
       
        // jezeli nie ma stanu produktu to doda go do bazy
        $pole = array();   
        $db->insert_query('products_condition' , $pole); 
        $id_dodanego_stanu_produktu = $db->last_id_query();
        unset($pole);
        //
        $pole = array(
                array('products_condition_id',$id_dodanego_stanu_produktu),
                array('language_id',$_SESSION['domyslny_jezyk']['id']),
                array('products_condition_name',$filtr->process($TablicaDane['Stan_produktu'])));           
        $db->insert_query('products_condition_description' , $pole);  
        unset($pole);
        
        // ---------------------------------------------------------------
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
            //
            $kod_jezyka = $ile_jezykow[$j]['kod'];
            //
            $NazwaTmp = $filtr->process($TablicaDane['Stan_produktu']);
            if (isset($TablicaDane['Stan_produktu_' . $kod_jezyka]) && trim((string)$TablicaDane['Stan_produktu_' . $kod_jezyka]) != '') {
                $NazwaTmp = $filtr->process($TablicaDane['Stan_produktu_' . $kod_jezyka]);
            }
            //
            $pole = array(
                    array('products_condition_id',$id_dodanego_stanu_produktu),
                    array('language_id',$ile_jezykow[$j]['id']),
                    array('products_condition_name',$NazwaTmp));
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $sql = $db->insert_query('products_condition_description' , $pole);
            }
            unset($pole);                 
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }        
        
        //
        // dodanie do bazy produktu
        $pola[] = array('products_condition_products_id',$id_dodanego_stanu_produktu);
        // 
        unset($id_dodanego_stanu_produktu);
    }
    
    $db->close_query($sqlc);
    unset($zapytanieStan);
}  
?>