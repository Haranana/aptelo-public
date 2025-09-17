<?php
// podobne po id

if (isset($TablicaDane['Podobne_id'])) {
    //
    $tabTmp = explode(',', (string)$TablicaDane['Podobne_id']);
    //
    $db->delete_query('products_options_products' , " pop_products_id_master = '" . (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji) . "'");
    //    
    if ( count($tabTmp) > 0 ) {
         //
         foreach ( $tabTmp as $pTmp) {
            //
            if ( (int)$pTmp > 0 ) {
                  //
                  $pola = array(
                          array('pop_products_id_master',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                          array('pop_products_id_slave',(int)$pTmp));        
                  $db->insert_query('products_options_products' , $pola);
                  unset($pola);         
                  //
            }
            //
         }
         //
    }
    //
    unset($tabTmp);
    //
}

// powiazane po id

if (isset($TablicaDane['Powiazane_id'])) {
    //
    $db->delete_query('products_related_products' , " prp_products_id_master = '" . (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji) . "'");
    //    
    $tabTmp = explode(',', (string)$TablicaDane['Powiazane_id']);
    //
    if ( count($tabTmp) > 0 ) {
         //
         foreach ( $tabTmp as $pTmp) {
            //
            if ( (int)$pTmp > 0 ) {
                  //
                  $pola = array(
                          array('prp_products_id_master',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                          array('prp_products_id_slave',(int)$pTmp));        
                  $db->insert_query('products_related_products' , $pola);
                  unset($pola);         
                  //
            }
            //
         }
         //
    }
    //
    unset($tabTmp);
    //
}

?>