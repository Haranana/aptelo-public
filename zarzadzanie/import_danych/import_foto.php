<?php
// dodatkowe zdjecia

// przy aktualizacji sprawdza czy sa jakies dod zdjecia w csv - jezeli tak to skasuje dod zdjecia w bazie i doda z pliku csv
$nieMaZdjec = true;
//
if ($CzyDodawanie == false) {
    //
    for ($w = 1; $w < 250 ; $w++) {
        if (isset($TablicaDane['Zdjecie_dodatkowe_'.$w]) && trim((string)$TablicaDane['Zdjecie_dodatkowe_'.$w]) != '') {
            $nieMaZdjec = false;
        }
    }
    //
    if ($nieMaZdjec == false) {
        // kasuje rekordy w tablicy
        $db->delete_query('additional_images' , " products_id = '".$id_aktualizowanej_pozycji."'");      
    }
    //
}

$re = 1;
for ($w = 1; $w < 250 ; $w++) {
    //
    if (isset($TablicaDane['Zdjecie_dodatkowe_'.$w]) && trim((string)$TablicaDane['Zdjecie_dodatkowe_'.$w]) != '') {
        //
        // jezeli jest zdjecie i ma adres w linku
        $urlPat = parse_url($TablicaDane['Zdjecie_dodatkowe_'.$w]);
        //
        $link_zdjecia = $TablicaDane['Zdjecie_dodatkowe_'.$w];
        //
        if ( isset($urlPat['host']) && isset($urlPat['path'])) {
             //
             $scie = $urlPat['path'];
             if ( substr((string)$scie, 0, 1) == '/' ) {
                  $scie = substr((string)$scie, 1, strlen((string)$scie));
             }
             //
             $podziel = explode('/', (string)$scie);
             $podziel_wynik = array();
             //
             for ($x = 0; $x < count($podziel); $x++) {
                  $podziel_wynik[] = $podziel[$x];
             }
             //
             $link_zdjecia = implode('/', (array)$podziel_wynik);
             //
        }
        //
        $pola = array(
                array('products_id',(($CzyDodawanie == true) ? (int)$id_dodanej_pozycji : (int)$id_aktualizowanej_pozycji)),
                array('popup_images',$filtr->process(trim((string)$link_zdjecia))),
                array('sort_order',(int)$re));   

        if (isset($TablicaDane['Zdjecie_dodatkowe_opis_'.$w]) && trim((string)$TablicaDane['Zdjecie_dodatkowe_opis_'.$w]) != '') {
            $pola[] = array('images_description',$filtr->process($TablicaDane['Zdjecie_dodatkowe_opis_'.$w]));  
        }
        
        $db->insert_query('additional_images' , $pola);
        unset($pola, $link_zdjecia, $urlPat);
        //
        $re++;
        //
    }
    // 
}     
//
unset($re);
// 

if ( isset($_POST['import_zdjec']) && $_POST['import_zdjec'] == 'tak' ) {
  
    $PlikZdjecia = $TablicaDane['Zdjecie_glowne'];
    $TylkoZdjecie = '';
    
    // sprawdza czy nie ma innego zrodla
    if ( isset($TablicaDane['Zdjecie_glowne_zrodlo']) && substr((string)$TablicaDane['Zdjecie_glowne_zrodlo'],0,4) == 'http' && strpos((string)$TablicaDane['Zdjecie_glowne_zrodlo'], ADRES_URL_SKLEPU) == false ) {
         //
         $PlikZdjecia = $TablicaDane['Zdjecie_glowne_zrodlo'];
         $TylkoZdjecie = $TablicaDane['Zdjecie_glowne'];
         //
    }
         
    // pobieranie zdjec z innego serwera
    if ( substr((string)$PlikZdjecia,0,4) == 'http' && strpos((string)$PlikZdjecia, ADRES_URL_SKLEPU) == false ) {
         //
         if ( PobieranieCurl::CzyJestPlikObrazka($PlikZdjecia) ) {
           
              if ( $TylkoZdjecie == '' ) {
           
                  $url = parse_url($TablicaDane['Zdjecie_glowne'], PHP_URL_PATH);
                  //$url = str_replace(" ", "-", (string)$url);

                  $sciezka = explode('/', (string)$url);
                  
                  $katalog = '../' . KATALOG_ZDJEC;
                  
                  if ( count($sciezka) > 1 ) {
                  
                      $samoZdjecie = implode('', array_slice((array)$sciezka, -1, 1));
                      
                      $podzialNa = array_slice((array)$sciezka, 0, -1);
                      $podzialNa = implode('/', (array)$podzialNa);
                      
                      $katalog = '../' . KATALOG_ZDJEC . $podzialNa;

                      if ( !file_exists( $katalog ) ) {
                           mkdir ( $katalog, 0777, true );
                      }
                      
                  }
                  
                  $katalog = $katalog . '/' . $samoZdjecie;
                  
              } else {
                
                  $katalog = '../' . KATALOG_ZDJEC . '/' . $TylkoZdjecie;
                  
              }

              if (!is_file($katalog)) {
                  //          
                  //zapisanie pobranego obrazka na serwerze
                  PobieranieCurl::ZapiszObraz($PlikZdjecia, $katalog);
                  //
              }

              unset($url, $sciezka, $samoZdjecie, $katalog);

         }     
         //
    }
    
    unset($PlikZdjecia, $TylkoZdjecie);

    for ($w = 1; $w < 250 ; $w++) {
        //
        if ( isset($TablicaDane['Zdjecie_dodatkowe_'.$w]) ) {
            //
            $PlikZdjecia = $TablicaDane['Zdjecie_dodatkowe_'.$w];
            $TylkoZdjecie = '';
            
            // sprawdza czy nie ma innego zrodla
            if ( isset($TablicaDane['Zdjecie_dodatkowe_'.$w.'_zrodlo']) && substr((string)$TablicaDane['Zdjecie_dodatkowe_'.$w.'_zrodlo'],0,4) == 'http' && strpos((string)$TablicaDane['Zdjecie_dodatkowe_'.$w.'_zrodlo'], ADRES_URL_SKLEPU) == false ) {
                 //
                 $PlikZdjecia = $TablicaDane['Zdjecie_dodatkowe_'.$w.'_zrodlo'];
                 $TylkoZdjecie = $TablicaDane['Zdjecie_dodatkowe_'.$w];
                 //
            }        
            //
            if ( substr((string)$PlikZdjecia,0,4) == 'http' && strpos((string)$PlikZdjecia, ADRES_URL_SKLEPU) == false ) {
                 //
                 if ( PobieranieCurl::CzyJestPlikObrazka($PlikZdjecia) ) {
                   
                      if ( $TylkoZdjecie == '' ) {
                       
                          $url = parse_url($TablicaDane['Zdjecie_dodatkowe_'.$w], PHP_URL_PATH);
                          //$url = str_replace(" ", "-", (string)$url);

                          $sciezka = explode('/', (string)$url);
                          
                          $katalog = '../' . KATALOG_ZDJEC;
                          
                          if ( count($sciezka) > 1 ) {
                          
                              $samoZdjecie = implode('', array_slice((array)$sciezka, -1, 1));
                              
                              $podzialNa = array_slice((array)$sciezka, 0, -1);
                              $podzialNa = implode('/', (array)$podzialNa);
                              
                              $katalog = '../' . KATALOG_ZDJEC . $podzialNa;

                              if ( !file_exists( $katalog ) ) {
                                   mkdir ( $katalog, 0777, true );
                              }
                              
                          }
                          
                          $katalog = $katalog . '/' . $samoZdjecie;
                          
                      } else {
                        
                          $katalog = '../' . KATALOG_ZDJEC . '/' . $TylkoZdjecie;
                          
                      }                      
              
                      if (!is_file($katalog)) {
                          //
                          //zapisanie pobranego obrazka na serwerze
                          PobieranieCurl::ZapiszObraz($PlikZdjecia, $katalog);
                          //
                      }

                      unset($url, $sciezka, $samoZdjecie, $katalog);

                 }     
                 //
            }
            //
            unset($PlikZdjecia, $TylkoZdjecie);
            //
        }
        //
    }  
    
}
?>