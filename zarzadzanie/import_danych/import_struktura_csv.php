<?php
$linia = (int)$_POST['limit'];

// przejescie do wybranej linii
$file->seek( $linia );
$DaneCsv = $file->current(); 

$TabDaneCsv = array();

// tworzenie tablicy poszczegolnych pol
$TabDaneCsv = str_getcsv($DaneCsv, $_POST['separator']);
$TablicaDane = array();

// przypisanie danych do tablicy
// tablica bedzie miala postac np
// $TablicaDane[Nr_katalogowy] = jakas wartosc
//

if (count($TabDaneCsv) > 0) {
    //
    for ($q = 0, $c = count($TablicaDef); $q < $c; $q++) {
        
        if (isset($TabDaneCsv[$q])) {
            //
            $TablicaDane[$TablicaDef[$q]] = trim((string)$TabDaneCsv[$q]);
            
            if ( trim((string)$TablicaDef[$q]) == 'Nazwa_produktu' ) {
                 $TablicaDane['Nazwa_produktu_struktura'] = trim((string)$TabDaneCsv[$q]); 
            }            
            
        }
        
    }
    //
}
?>