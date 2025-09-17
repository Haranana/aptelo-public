<?php
// pliki
for ($w = 1; $w < 5 ; $w++) {
    //
    if ((isset($TablicaDane['Youtube_'.$w.'_nazwa']) && trim((string)$TablicaDane['Youtube_'.$w.'_nazwa']) != '') && (isset($TablicaDane['Youtube_'.$w.'_url']) && trim((string)$TablicaDane['Youtube_'.$w.'_url']) != '')) {

        // sprawdza czy takie pole jest juz w bazie
        $dodajBoNieMa = false;
        //
        if ( $CzyDodawanie == false ) {
            //
            $zapytanieSpr = "select products_film_id from products_youtube where products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
            $sqlSpr = $db->open_query($zapytanieSpr);  
            if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                $dodajBoNieMa = true;
            }
            $db->close_query($sqlSpr);
            //
        }
        
        $pola = array(
                array('products_film_id',$w),
                array('products_film_name',$filtr->process($TablicaDane['Youtube_'.$w.'_nazwa'])),
                array('products_film_url',$filtr->process($TablicaDane['Youtube_'.$w.'_url'])));
                
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
        }                   
                
        if (isset($TablicaDane['Youtube_'.$w.'_opis']) && trim((string)$TablicaDane['Youtube_'.$w.'_opis']) != '') {
            //
            $pola[] = array('products_film_description',$filtr->process($TablicaDane['Youtube_'.$w.'_opis']));
            //
        }  

        if (isset($TablicaDane['Youtube_'.$w.'_szerokosc']) && trim((string)$TablicaDane['Youtube_'.$w.'_szerokosc']) != '') {
            //
            $pola[] = array('products_film_width',(int)$TablicaDane['Youtube_'.$w.'_szerokosc']);
            //
        }         
        
        if (isset($TablicaDane['Youtube_'.$w.'_wysokosc']) && trim((string)$TablicaDane['Youtube_'.$w.'_wysokosc']) != '') {
            //
            $pola[] = array('products_film_height',(int)$TablicaDane['Youtube_'.$w.'_wysokosc']);
            //
        }         
  
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $db->insert_query('products_youtube', $pola); 
          } else {
            $db->update_query('products_youtube' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        }        
        unset($pola, $dodajBoNieMa, $zapytanieSpr);
                
        // ---------------------------------------------------------------
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
        
            // sprawdza czy takie pole jest juz w bazie
            $dodajBoNieMa = false;
            //
            if ( $CzyDodawanie == false ) {
                //
                $zapytanieSpr = "select products_film_id from products_youtube where products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'";
                $sqlSpr = $db->open_query($zapytanieSpr);  
                if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                    $dodajBoNieMa = true;
                }
                $db->close_query($sqlSpr);
                //
            }        
        
            //
            $kod_jezyka = $ile_jezykow[$j]['kod'];
            //
            $NazwaTmp = '';
            if (isset($TablicaDane['Youtube_'.$w.'_nazwa_' . $kod_jezyka]) && trim((string)$TablicaDane['Youtube_'.$w.'_nazwa_' . $kod_jezyka]) != '') {
                $NazwaTmp = $filtr->process($TablicaDane['Youtube_'.$w.'_nazwa_' . $kod_jezyka]);
            }
            //
            $pola = array(
                    array('products_film_id',$w),
                    array('products_film_name',$NazwaTmp),
                    array('products_film_url',$filtr->process($TablicaDane['Youtube_'.$w.'_url']))); 

            if ($CzyDodawanie == true || $dodajBoNieMa == true) {
                $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                $pola[] = array('language_id',$ile_jezykow[$j]['id']); 
            }                           
                    
            if (isset($TablicaDane['Youtube_'.$w.'_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Youtube_'.$w.'_opis_' . $kod_jezyka]) != '') {
                //
                $pola[] = array('products_film_description',$filtr->process($TablicaDane['Youtube_'.$w.'_opis_' . $kod_jezyka]));
                //
            }
            
            if (isset($TablicaDane['Youtube_'.$w.'_szerokosc']) && trim((string)$TablicaDane['Youtube_'.$w.'_szerokosc']) != '') {
                //
                $pola[] = array('products_film_width',(int)$TablicaDane['Youtube_'.$w.'_szerokosc']);
                //
            }         
            
            if (isset($TablicaDane['Youtube_'.$w.'_wysokosc']) && trim((string)$TablicaDane['Youtube_'.$w.'_wysokosc']) != '') {
                //
                $pola[] = array('products_film_height',(int)$TablicaDane['Youtube_'.$w.'_wysokosc']);
                //
            }              

            if (($CzyDodawanie == true || $dodajBoNieMa == true) && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('products_youtube', $pola); 
              } else if ($NazwaTmp != '') {
                $db->update_query('products_youtube' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'");
            }                     
            unset($pola);                
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }           
    }
    // 
}

?>