<?php
// paczkomaty - gabaryty i ilosc

if ((isset($TablicaDane['Paczkomaty_gabaryt']) && trim((string)$TablicaDane['Paczkomaty_gabaryt']) != '') && (isset($TablicaDane['Paczkomaty_ilosc']) && (int)$TablicaDane['Paczkomaty_ilosc'] > 0)) {
    //
    if ( strtoupper(trim((string)$TablicaDane['Paczkomaty_gabaryt'])) == 'A' || strtoupper(trim((string)$TablicaDane['Paczkomaty_gabaryt'])) == 'B' || strtoupper(trim((string)$TablicaDane['Paczkomaty_gabaryt'])) == 'C' || strtoupper(trim((string)$TablicaDane['Paczkomaty_gabaryt'])) == 'D' ) {
         //
         $pola = array(
                 array('inpost_size',strtolower(trim((string)$TablicaDane['Paczkomaty_gabaryt']))),
                 array('inpost_quantity',(int)$TablicaDane['Paczkomaty_ilosc']));        
         $db->update_query('products' , $pola, ' products_id = ' . (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
         unset($pola);         
         //
    }
    //
}
// 

?>