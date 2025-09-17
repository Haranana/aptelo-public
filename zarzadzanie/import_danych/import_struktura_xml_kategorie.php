<?php
// funkcja tworzy ciag do zamiany z xml na csv
Function DrzewoKatXml($idPola = '0', $a = -1, $b = -1, $c = -1, $d = -1, $e = -1, $f = -1, $g = -1, $h = -1, $i = -1, $j = -1) {
    global $dane_produktow;
    //
    $TworzenieCiagu = '$dane_produktow';
    if ($a > -1) { $TworzenieCiagu .= '->Kategoria['.$a.']'; }  
    if ($b > -1) { $TworzenieCiagu .= '->Kategoria['.$b.']'; }  
    if ($c > -1) { $TworzenieCiagu .= '->Kategoria['.$c.']'; }  
    if ($d > -1) { $TworzenieCiagu .= '->Kategoria['.$d.']'; }  
    if ($e > -1) { $TworzenieCiagu .= '->Kategoria['.$e.']'; }  
    if ($f > -1) { $TworzenieCiagu .= '->Kategoria['.$f.']'; }  
    if ($g > -1) { $TworzenieCiagu .= '->Kategoria['.$g.']'; }  
    if ($h > -1) { $TworzenieCiagu .= '->Kategoria['.$h.']'; }  
    if ($i > -1) { $TworzenieCiagu .= '->Kategoria['.$i.']'; }  
    if ($j > -1) { $TworzenieCiagu .= '->Kategoria['.$j.']'; }   

    eval('$EvalKod = ' . $TworzenieCiagu . ';');
    //
    $Wynik = '';
    //
    if (count($EvalKod) > 0) {
       if (trim((string)$EvalKod->Nazwa) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Nazwa) . ';';
         } else { 
          $Wynik .= ';';
       }
       if (trim((string)$EvalKod->Opis) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Opis) . ';';
         } else { 
          $Wynik .= ';';
       }
       if (trim((string)$EvalKod->Opis_dol) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Opis_dol) . ';';
         } else { 
          $Wynik .= ';';
       }       
       if (trim((string)$EvalKod->Zdjecie) != '') {
          $Wynik .= $EvalKod->Zdjecie . ';';
         } else { 
          $Wynik .= ';';
       }
       if (trim((string)$EvalKod->Meta_Tytul) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Meta_Tytul) . ';';
         } else { 
          $Wynik .= ';';
       }
       if (trim((string)$EvalKod->Meta_Opis) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Meta_Opis) . ';';
         } else { 
          $Wynik .= ';';
       }
       if (trim((string)$EvalKod->Meta_Slowa) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Meta_Slowa) . ';';
         } else { 
          $Wynik .= ';';
       }
       if (trim((string)$EvalKod->Adres_Url) != '') {
          $Wynik .= Funkcje::CzyszczenieTekstu($EvalKod->Adres_Url) . ';';
         } else { 
          $Wynik .= ';';
       }       
       return $Wynik;
    }     
    //
}

// tworzy tablice z nazwami naglowkow i danymi z pliku xml
if ($_POST['plik'] == 'url' && strpos((string)$_POST['adres_url'], '.xml') > -1) {
    // 
    $dane_produktow = simplexml_load_file($_POST['adres_url']); 
    //
  } else if ($_POST['plik'] != 'url') {
    //
    $dane_produktow = simplexml_load_file("../import/" . $_POST['plik']); 
    //
}

// tutaj ta czesc zamienia dane z xml na format podobny do csv zeby mozna bylo utworzyc tablice

$TablicaKategorii = array();

// pobierane z ajaxa
$a = (int)$_POST['limit'];

$Text_1 = DrzewoKatXml('1', $a);
$TablicaKategorii[] = $Text_1;
//
for ($b = 0, $cb = count($dane_produktow->Kategoria[$a]->Kategoria); $b < $cb; $b++) {
    //
    $Text_2 = $Text_1 . DrzewoKatXml('2', $a, $b);
    $TablicaKategorii[] = $Text_2;
    //
    for ($c = 0, $cc = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria); $c < $cc; $c++) {
        //
        $Text_3 = $Text_2 . DrzewoKatXml('3', $a, $b, $c);
        $TablicaKategorii[] = $Text_3;
        //
        for ($d = 0, $cd = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria); $d < $cd; $d++) {
            //
            $Text_4 = $Text_3 . DrzewoKatXml('4', $a, $b, $c, $d);
            $TablicaKategorii[] = $Text_4;
            //
            for ($e = 0, $ce = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria[$d]->Kategoria); $e < $ce; $e++) {
                //
                $Text_5 = $Text_4 . DrzewoKatXml('5', $a, $b, $c, $d, $e);
                $TablicaKategorii[] = $Text_5;
                //
                for ($f = 0, $cf = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria[$d]->Kategoria[$e]->Kategoria); $f < $cf; $f++) {
                    //
                    $Text_6 = $Text_5 . DrzewoKatXml('6', $a, $b, $c, $d, $e, $f);
                    $TablicaKategorii[] = $Text_6;
                    //
                    for ($g = 0, $cg = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria[$d]->Kategoria[$e]->Kategoria[$f]->Kategoria); $g < $cg; $g++) {
                        //
                        $Text_7 = $Text_6 . DrzewoKatXml('7', $a, $b, $c, $d, $e, $f, $g);
                        $TablicaKategorii[] = $Text_7;
                        //
                        for ($h = 0; $h < count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria[$d]->Kategoria[$e]->Kategoria[$f]->Kategoria[$g]->Kategoria); $h++) {
                            //
                            $Text_8 = $Text_7 . DrzewoKatXml('8', $a, $b, $c, $d, $e, $f, $g, $h);
                            $TablicaKategorii[] = $Text_8;
                            //
                            for ($i = 0, $ci = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria[$d]->Kategoria[$e]->Kategoria[$f]->Kategoria[$g]->Kategoria[$h]->Kategoria); $i < $ci; $i++) {
                                //
                                $Text_9 = $Text_8 . DrzewoKatXml('9', $a, $b, $c, $d, $e, $f, $g, $h, $i);
                                $TablicaKategorii[] = $Text_9;
                                //
                                for ($j = 0, $cj = count($dane_produktow->Kategoria[$a]->Kategoria[$b]->Kategoria[$c]->Kategoria[$d]->Kategoria[$e]->Kategoria[$f]->Kategoria[$g]->Kategoria[$h]->Kategoria[$i]->Kategoria); $j < $cj; $j++) {
                                    //
                                    $Text_10 = $Text_9 . DrzewoKatXml('10', $a, $b, $c, $d, $e, $f, $g, $h, $i, $j);
                                    $TablicaKategorii[] = $Text_10;
                                    //
                                }                                    
                            }                                
                        }                            
                    }                        
                }                    
            }                
        }            
    }        
}
unset($Text_1, $Text_2, $Text_3, $Text_4, $Text_5, $Text_6, $Text_7, $Text_8, $Text_9, $Text_10);


for ($w = 0, $c = count($TablicaKategorii); $w < $c; $w++) {
    if (isset($TablicaKategorii[$w+1])) {
        if (strpos((string)$TablicaKategorii[$w+1], $TablicaKategorii[$w] ) > -1) {
            $TablicaKategorii[$w] = '';
        }
    }
}

// tablica do przygotowanie danych TablicaDane
$DefDoCsv = array();
$DefDoCsv[0] = '_nazwa';
$DefDoCsv[1] = '_opis';
$DefDoCsv[2] = '_opis_dol';
$DefDoCsv[3] = '_zdjecie';
$DefDoCsv[4] = '_meta_tytul';
$DefDoCsv[5] = '_meta_opis';
$DefDoCsv[6] = '_meta_slowa';
$DefDoCsv[7] = '_adres_url';

for ($s = 0, $c = count($TablicaKategorii); $s < $c; $s++) {
    if ($TablicaKategorii[$s] != '') {
        //
        // stworzenie tablicy z definicjami
        $TablicaDane = array();        
        //
        $PodzielNaCSV = explode(';', (string)$TablicaKategorii[$s]);
        //
        $Podlicznik = 0;
        for ($r = 1; $r < 11; $r++) {
            //
            for ($q = 0; $q < 8; $q++) {
                //
                if (isset($PodzielNaCSV[$Podlicznik])) {
                    $TablicaDane['Kategoria_'.$r.$DefDoCsv[$q]] = $PodzielNaCSV[$Podlicznik];
                }
                $Podlicznik++;
                //
            }
            //
        }
        //
        include('import_kategorie.php');

        unset($TablicaDane);
    }
}
//


echo json_encode( array("suma" => ($a + 1), "dodane" => 0, 'aktualizacja' => 0, 'nazwy' => '' ) );

?>