<?php
// akcesoria dodatkowe

// przy aktualizacji sprawdza czy sa jakies pliki akcesoria w csv - jezeli tak to skasuje w bazie i doda z pliku csv
$nieMaAkcesor = true;
if ($CzyDodawanie == false) {
    //
    for ($w = 1; $w < 50 ; $w++) {
        if ((isset($TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) && trim((string)$TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) != '') && (isset($TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) && trim((string)$TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) != '')) {
            $nieMaAkcesor = false;
        }
    }
    //
    if ($nieMaAkcesor == false) {
        // kasuje rekordy w tablicy
        $db->delete_query('products_accesories' , " pacc_products_id_master = '".$id_aktualizowanej_pozycji."'");      
    }
    //
}

for ($w = 1; $w < 50 ; $w++) {
    //
    if ((isset($TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) && trim((string)$TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) != '') && (isset($TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) && trim((string)$TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) != '')) {
        //
        $zapytanieAkcesoria = "select distinct products_id from products where products_model = '" . $filtr->process($TablicaDane['Akcesoria_'.$w.'_nr_katalogowy']) . "'";  
        $sqla = $db->open_query($zapytanieAkcesoria); 
        
        if ( (int)$db->ile_rekordow($sqla) > 0 ) {
             //
             $infs = $sqla->fetch_assoc();
             //
             $pola = array(
                     array('pacc_products_id_master',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                     array('pacc_products_id_slave',$infs['products_id']),
                     array('pacc_type','produkt'));        
             $db->insert_query('products_accesories' , $pola);
             unset($pola);
             //
        }
        
        $db->close_query($sqla);
        unset($infs, $zapytanieAkcesoria);        
        
    }
    // 
}     
// 
?>