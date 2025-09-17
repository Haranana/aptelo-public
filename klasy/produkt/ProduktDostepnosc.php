<?php

if ( isset($pobierzFunkcje) ) {

    // jezeli dostepnosc nie jest zdefiniowana przyjmuje domyslna
    if ( $idDostepnosci == '' ) {
        $idDostepnosci = $this->info['id_dostepnosci'];
    }
    
    // jezeli nie jest zdefiniowana ilosc produktow przyjmuje domyslna produktu
    if ( $iloscProduktu == '' ) {
        $iloscProduktu = $this->info['ilosc'];
    }
    
    // 
    $Obrazek = 'nie';
    
    if ( Funkcje::czyNiePuste( $idDostepnosci ) ) {
    
        //
        // jezeli jest automatyczna dostepnosc
        if ( $idDostepnosci == '99999' ) {
        
            $TmpId = $this->PokazIdDostepnosciAutomatycznych( $iloscProduktu );
            
            if ( $TmpId != '0' ) {    
                // jezeli dostepnosc jest w formie obrazka
                if ( !empty($GLOBALS['dostepnosci'][$TmpId]['foto']) ) {
                    $IdDost = '<img src="' . KATALOG_ZDJEC . '/' . $GLOBALS['dostepnosci'][$TmpId]['foto'] . '" alt="' . $GLOBALS['dostepnosci'][$TmpId]['dostepnosc'] . '" />';
                    $Obrazek = 'tak';
                  } else {
                    $IdDost = $GLOBALS['dostepnosci'][$TmpId]['dostepnosc'];
                }                    
                $Kupowanie = $GLOBALS['dostepnosci'][$TmpId]['kupowanie'];
            } else {
                $IdDost = '';
                $Kupowanie = 'tak';                
            }
            
            unset($TmpId);
            
           } else {
           
            // jezeli dostepnosc jest w formie obrazka
            $IdDost = '';
            if ( isset($GLOBALS['dostepnosci'][$idDostepnosci]) && !empty($GLOBALS['dostepnosci'][$idDostepnosci]['foto']) ) {
                $IdDost = '<img src="' . KATALOG_ZDJEC . '/' . $GLOBALS['dostepnosci'][$idDostepnosci]['foto'] . '" alt="' . $GLOBALS['dostepnosci'][$idDostepnosci]['dostepnosc'] . '" />';
                $Obrazek = 'tak';
              } else {                
                if ( isset($GLOBALS['dostepnosci'][$idDostepnosci]) ) {
                     $IdDost = $GLOBALS['dostepnosci'][$idDostepnosci]['dostepnosc'];
                }
            }
            
            $Kupowanie = 'nie';    
            if ( isset($GLOBALS['dostepnosci'][$idDostepnosci]) ) {
                 $Kupowanie = $GLOBALS['dostepnosci'][$idDostepnosci]['kupowanie'];
            }
        }
        
      } else {
      
        $IdDost = '';
        $Kupowanie = 'tak';
        
    }
    
    $this->dostepnosc = array('dostepnosc' => $IdDost, 'obrazek' => $Obrazek, 'kupowanie' => $Kupowanie);
    
    unset($IdDost, $iloscProduktu, $idDostepnosci, $Kupowanie);
    //

}
       
?>