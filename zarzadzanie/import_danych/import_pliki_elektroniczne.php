<?php
// pliki elektroniczne

// przy aktualizacji sprawdza czy sa jakies plikiw csv - jezeli tak to skasuje w bazie i doda z pliku csv
$nieMaPlikowElektronicznych = true;

if ($CzyDodawanie == false) {
    //
    for ($w = 1; $w < 101 ; $w++) {
        if ((isset($TablicaDane['Plik_elektroniczny_'.$w.'_nazwa']) && trim((string)$TablicaDane['Plik_elektroniczny_'.$w.'_nazwa']) != '') && (isset($TablicaDane['Plik_elektroniczny_'.$w.'_plik']) && trim((string)$TablicaDane['Plik_elektroniczny_'.$w.'_plik']) != '')) {
            $nieMaPlikowElektronicznych = false;
        }
    }
    //
    if ($nieMaPlikowElektronicznych == false) {
        // kasuje rekordy w tablicy
        $db->delete_query('products_file_shopping' , " products_id = '".$id_aktualizowanej_pozycji."'");      
    }
    //
}

for ($w = 1; $w < 101 ; $w++) {
    //
    if ((isset($TablicaDane['Plik_elektroniczny_'.$w.'_nazwa']) && trim((string)$TablicaDane['Plik_elektroniczny_'.$w.'_nazwa']) != '') && (isset($TablicaDane['Plik_elektroniczny_'.$w.'_plik']) && trim((string)$TablicaDane['Plik_elektroniczny_'.$w.'_plik']) != '')) {
        //
        $pola = array(
                array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                array('products_file_shopping_name',$filtr->process($TablicaDane['Plik_elektroniczny_'.$w.'_nazwa'])),
                array('products_file_shopping',$filtr->process($TablicaDane['Plik_elektroniczny_'.$w.'_plik'])),     
                array('language_id',$_SESSION['domyslny_jezyk']['id']));  
                
        $db->insert_query('products_file_shopping' , $pola);
        unset($pola);
        //
    }
    // 
}     

// ---------------------------------------------------------------
// dodawanie do innych jezykow jak sa inne jezyki
for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {

    $kod_jezyka = $ile_jezykow[$j]['kod'];

    for ($w = 1; $w < 101 ; $w++) {
        //
        if ((isset($TablicaDane['Plik_elektroniczny_'.$w.'_nazwa_' . $kod_jezyka]) && trim((string)$TablicaDane['Plik_elektroniczny_'.$w.'_nazwa_' . $kod_jezyka]) != '') && (isset($TablicaDane['Plik_elektroniczny_'.$w.'_plik_' . $kod_jezyka]) && trim((string)$TablicaDane['Plik_elektroniczny_'.$w.'_plik_' . $kod_jezyka]) != '')) {
            //
            $pola = array(
                    array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                    array('products_file_shopping_name',$filtr->process($TablicaDane['Plik_elektroniczny_'.$w.'_nazwa_' . $kod_jezyka])),
                    array('products_file_shopping',$filtr->process($TablicaDane['Plik_elektroniczny_'.$w.'_plik_' . $kod_jezyka])),     
                    array('language_id',$ile_jezykow[$j]['id']));  
                    
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {        
                $db->insert_query('products_file_shopping' , $pola);
            }
            unset($pola);
            //
        }
        // 
    } 
    
    unset($kod_jezyka);

}
// 
?>