<?php
// pliki muzyczne mp3

// przy aktualizacji sprawdza czy sa jakies pliki mp3 w csv - jezeli tak to skasuje w bazie i doda z pliku csv
$nieMaMp3 = true;

if ($CzyDodawanie == false) {
    //
    for ($w = 1; $w < 16 ; $w++) {
        if ((isset($TablicaDane['Plik_mp3_'.$w]) && trim((string)$TablicaDane['Plik_mp3_'.$w]) != '') && (isset($TablicaDane['Nazwa_mp3_'.$w]) && trim((string)$TablicaDane['Nazwa_mp3_'.$w]) != '')) {
            $nieMaMp3 = false;
        }
    }
    //
    if ($nieMaMp3 == false) {
        // kasuje rekordy w tablicy
        $db->delete_query('products_mp3' , " products_id = '".$id_aktualizowanej_pozycji."'");      
    }
    //
}

for ($w = 1; $w < 16 ; $w++) {
    //
    if ((isset($TablicaDane['Plik_mp3_'.$w]) && trim((string)$TablicaDane['Plik_mp3_'.$w]) != '') && (isset($TablicaDane['Nazwa_mp3_'.$w]) && trim((string)$TablicaDane['Nazwa_mp3_'.$w]) != '')) {
        //
        $pola = array(
                array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                array('products_mp3_id',$w),
                array('products_mp3_name',$filtr->process($TablicaDane['Nazwa_mp3_'.$w])),
                array('products_mp3_file',$filtr->process($TablicaDane['Plik_mp3_'.$w])));        
        $db->insert_query('products_mp3' , $pola);
        unset($pola);
        //
    }
    // 
}     
// 
?>