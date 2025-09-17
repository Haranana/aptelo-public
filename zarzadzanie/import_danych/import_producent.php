<?php
// producent
if (isset($TablicaDane['Producent']) && trim((string)$TablicaDane['Producent']) != '') {
  
    // sprawdza czy producent jest juz w bazie
    $zapytanieProducent = "select manufacturers_name, manufacturers_id from manufacturers where manufacturers_name = '" . addslashes((string)$filtr->process($TablicaDane['Producent'])) . "'";
    $sqlc = $db->open_query($zapytanieProducent);
    //
    $id_producenta_tmp = 0;
    //
    if ((int)$db->ile_rekordow($sqlc) > 0) {

        $info = $sqlc->fetch_assoc();
        $pola[] = array('manufacturers_id',$info['manufacturers_id']);

        $id_producenta_tmp = $info['manufacturers_id'];
        
        unset($info);
        
     } else {
       
        // jezeli nie ma producenta to doda go do bazy
        $pole = array(array('manufacturers_name',$filtr->process($TablicaDane['Producent'])));   
        $db->insert_query('manufacturers' , $pole); 
        $id_dodanego_producenta = $db->last_id_query();
        unset($pole);
        //
        $pole = array(
                array('manufacturers_id',$id_dodanego_producenta),
                array('languages_id',$_SESSION['domyslny_jezyk']['id']),
                array('manufacturers_meta_title_tag',$filtr->process($TablicaDane['Producent'])),
                array('manufacturers_meta_desc_tag',$filtr->process($TablicaDane['Producent'])),   
                array('manufacturers_meta_keywords_tag',$filtr->process($TablicaDane['Producent'])));        
        $db->insert_query('manufacturers_info' , $pole);  
        unset($pole);
        //
        // dodanie id producenta do bazy produktu
        $pola[] = array('manufacturers_id',$id_dodanego_producenta);

        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
            //
            $pole = array(
                    array('manufacturers_id',$id_dodanego_producenta),
                    array('languages_id',$ile_jezykow[$j]['id']),
                    array('manufacturers_meta_title_tag',$filtr->process($TablicaDane['Producent'])),
                    array('manufacturers_meta_desc_tag',$filtr->process($TablicaDane['Producent'])),   
                    array('manufacturers_meta_keywords_tag',$filtr->process($TablicaDane['Producent'])));                    
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('manufacturers_info' , $pole);
            }            
            unset($pole);            
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }         
        
        $id_producenta_tmp = $id_dodanego_producenta;
        
        unset($id_dodanego_producenta);
    }
    
    unset($zapytanieProducent);
    $db->close_query($sqlc);
    
    if ( $id_producenta_tmp > 0 ) {
      
         $pole = array();
      
         if (isset($TablicaDane['Producent_nazwa']) && trim((string)$TablicaDane['Producent_nazwa']) != '') {
             $pole[] = array('manufacturers_full_name', $filtr->process($TablicaDane['Producent_nazwa']));
         }
         if (isset($TablicaDane['Producent_ulica']) && trim((string)$TablicaDane['Producent_ulica']) != '') {
             $pole[] = array('manufacturers_street', $filtr->process($TablicaDane['Producent_ulica']));
         }
         if (isset($TablicaDane['Producent_kod_pocztowy']) && trim((string)$TablicaDane['Producent_kod_pocztowy']) != '') {
             $pole[] = array('manufacturers_post_code', $filtr->process($TablicaDane['Producent_kod_pocztowy']));
         }
         if (isset($TablicaDane['Producent_miasto']) && trim((string)$TablicaDane['Producent_miasto']) != '') {
             $pole[] = array('manufacturers_city', $filtr->process($TablicaDane['Producent_miasto']));
         }
         if (isset($TablicaDane['Producent_kraj']) && trim((string)$TablicaDane['Producent_kraj']) != '') {
             $pole[] = array('manufacturers_country', $filtr->process($TablicaDane['Producent_kraj']));
         }
         if (isset($TablicaDane['Producent_email']) && trim((string)$TablicaDane['Producent_email']) != '') {
             $pole[] = array('manufacturers_email', $filtr->process($TablicaDane['Producent_email']));
         }
         if (isset($TablicaDane['Producent_telefon']) && trim((string)$TablicaDane['Producent_telefon']) != '') {
             $pole[] = array('manufacturers_phone', $filtr->process($TablicaDane['Producent_telefon']));
         }         

         if (isset($TablicaDane['Importer_nazwa']) && trim((string)$TablicaDane['Importer_nazwa']) != '') {
             $pole[] = array('importer_name', $filtr->process($TablicaDane['Importer_nazwa']));
         }
         if (isset($TablicaDane['Importer_ulica']) && trim((string)$TablicaDane['Importer_ulica']) != '') {
             $pole[] = array('importer_street', $filtr->process($TablicaDane['Importer_ulica']));
         }
         if (isset($TablicaDane['Importer_kod_pocztowy']) && trim((string)$TablicaDane['Importer_kod_pocztowy']) != '') {
             $pole[] = array('importer_post_code', $filtr->process($TablicaDane['Importer_kod_pocztowy']));
         }
         if (isset($TablicaDane['Importer_miasto']) && trim((string)$TablicaDane['Importer_miasto']) != '') {
             $pole[] = array('importer_city', $filtr->process($TablicaDane['Importer_miasto']));
         }
         if (isset($TablicaDane['Importer_kraj']) && trim((string)$TablicaDane['Importer_kraj']) != '') {
             $pole[] = array('importer_country', $filtr->process($TablicaDane['Importer_kraj']));
         }
         if (isset($TablicaDane['Importer_email']) && trim((string)$TablicaDane['Importer_email']) != '') {
             $pole[] = array('importer_email', $filtr->process($TablicaDane['Importer_email']));
         }
         if (isset($TablicaDane['Importer_telefon']) && trim((string)$TablicaDane['Importer_telefon']) != '') {
             $pole[] = array('importer_phone', $filtr->process($TablicaDane['Importer_telefon']));
         }  
         
         if ( count($pole) > 0 ) {
           
              $db->update_query('manufacturers' , $pole, 'manufacturers_id = ' . (int)$id_producenta_tmp);  

         }
         
         unset($pole);
         
    }
    
}  
?>