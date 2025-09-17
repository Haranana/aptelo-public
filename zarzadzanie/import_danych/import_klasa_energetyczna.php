<?php
// klasa energetyczna
if (isset($TablicaDane['Klasa_energetyczna']) && trim((string)$TablicaDane['Klasa_energetyczna']) != '') {
  
    $dopuszczalneWartosci = array('A+++','A++','A+','A','B','C','D','E','F','G');
    
    if ( in_array($TablicaDane['Klasa_energetyczna'], $dopuszczalneWartosci) ) {
      
        // dodanie do bazy produktu
        $pola[] = array('products_energy',$TablicaDane['Klasa_energetyczna']);

        // min klasa energetyczna
        if (isset($TablicaDane['Klasa_energetyczna_min']) && trim((string)$TablicaDane['Klasa_energetyczna_min']) != '') {
          
            if ( in_array($TablicaDane['Klasa_energetyczna_min'], $dopuszczalneWartosci) ) {
              
                $pola[] = array('products_min_energy',$TablicaDane['Klasa_energetyczna_min']);
                
            }
            
        }
        
        // max klasa energetyczna
        if (isset($TablicaDane['Klasa_energetyczna_max']) && trim((string)$TablicaDane['Klasa_energetyczna_max']) != '') {
          
            if ( in_array($TablicaDane['Klasa_energetyczna_max'], $dopuszczalneWartosci) ) {
              
                $pola[] = array('products_max_energy',$TablicaDane['Klasa_energetyczna_max']);
                
            }
            
        }
        
        // etykieta energetyczna
        if (isset($TablicaDane['Klasa_energetyczna_grafika']) && trim((string)$TablicaDane['Klasa_energetyczna_grafika']) != '') {
          
            $pola[] = array('products_energy_img',$TablicaDane['Klasa_energetyczna_grafika']);
            
        }
        
        // karta informacyjna pdf
        if (isset($TablicaDane['Klasa_energetyczna_pdf']) && trim((string)$TablicaDane['Klasa_energetyczna_pdf']) != '') {
          
            $pola[] = array('products_energy_pdf',$TablicaDane['Klasa_energetyczna_pdf']);
            
        }
        
    }

}  
?>