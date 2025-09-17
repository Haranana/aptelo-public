<?php
// jednostka miary
if (isset($TablicaDane['Jednostka_miary']) && trim((string)$TablicaDane['Jednostka_miary']) != '') {
  
    // sprawdza czy jednostka jest juz w bazie
    $zapytanieJm = "select p.products_jm_id, pd.products_jm_id, pd.products_jm_name from products_jm p, products_jm_description pd where p.products_jm_id = pd.products_jm_id and pd.products_jm_name = '" . addslashes((string)$filtr->process($TablicaDane['Jednostka_miary'])) . "' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
    $sqlc = $db->open_query($zapytanieJm);
    //    
    if ((int)$db->ile_rekordow($sqlc) > 0) {
        //
        $info = $sqlc->fetch_assoc();
        $pola[] = array('products_jm_id',$info['products_jm_id']);
        unset($info);
        //
     } else {
       
        // jezeli nie ma jednostki to doda ja do bazy
        $pole = array(array('products_jm_quantity_type','1'));   
        $db->insert_query('products_jm' , $pole); 
        $id_dodanej_jm = $db->last_id_query();
        unset($pole);
        //
        $pole = array(
                array('products_jm_id',$id_dodanej_jm),
                array('language_id',$_SESSION['domyslny_jezyk']['id']),
                array('products_jm_name',$filtr->process($TablicaDane['Jednostka_miary'])));           
        $db->insert_query('products_jm_description' , $pole);  
        unset($pole);
        
        // ---------------------------------------------------------------
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
            //
            $kod_jezyka = $ile_jezykow[$j]['kod'];
            //
            $NazwaTmp = $filtr->process($TablicaDane['Jednostka_miary']);
            if (isset($TablicaDane['Jednostka_miary_' . $kod_jezyka]) && trim((string)$TablicaDane['Jednostka_miary_' . $kod_jezyka]) != '') {
                $NazwaTmp = $filtr->process($TablicaDane['Jednostka_miary_' . $kod_jezyka]);
            }
            //
            $pole = array(
                    array('products_jm_id',$id_dodanej_jm),
                    array('language_id',$ile_jezykow[$j]['id']),
                    array('products_jm_name',$NazwaTmp));
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $sql = $db->insert_query('products_jm_description' , $pole);
            }
            unset($pole);                 
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }        
        
        //
        // dodanie id jednostki do bazy produktu
        $pola[] = array('products_jm_id',$id_dodanej_jm);
        // 
        unset($id_dodanej_jm);
    }
    
    $db->close_query($sqlc);
    unset($zapytanieJm);
    
} else {
  
    // jezeli jest dodawanie przyjmie domyslna jednostke miary
    if ($CzyDodawanie == true) {
        //
        $pola[] = array('products_jm_id',Funkcje::domyslnaJednostkaMiary());
        //
    }
  
}
?>