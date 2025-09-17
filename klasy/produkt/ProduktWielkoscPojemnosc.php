<?php

$Wynik = '';

if ( isset($pobierzFunkcje) && $this->infoSql['products_size_type'] != '' && (float)$this->infoSql['products_size'] > 0 ) {
       
     $jmtab = array('g' => array($GLOBALS['tlumacz']['JM_GRAM'],$GLOBALS['tlumacz']['JM_KG']),
                    'ml' => array($GLOBALS['tlumacz']['JM_ML'],$GLOBALS['tlumacz']['JM_LITR']),
                    'cb' => array($GLOBALS['tlumacz']['JM_CB'],$GLOBALS['tlumacz']['JM_MB']),
                    'c2' => array($GLOBALS['tlumacz']['JM_C2'],$GLOBALS['tlumacz']['JM_M2']),
                    'c3' => array($GLOBALS['tlumacz']['JM_C3'],$GLOBALS['tlumacz']['JM_M3']),
                    'szt' => array($GLOBALS['tlumacz']['JM_SZT'],$GLOBALS['tlumacz']['JM_SZT']),
                    'pkg' => array($GLOBALS['tlumacz']['JM_PKG'],$GLOBALS['tlumacz']['JM_PKG']),
                    'tbl' => array($GLOBALS['tlumacz']['JM_TBL'],$GLOBALS['tlumacz']['JM_TBL']),
         );
      
     if ( $rozmiar_inny == 0 && $cena_inna == 0 ) {
      
          $rozmiar = $this->infoSql['products_size'];
          $cena_produkt = $this->infoSql['products_price_tax'];
          
     } else {

          $rozmiar = $rozmiar_inny;
          $cena_produkt = $cena_inna;
       
     }

     if ( $rozmiar > 0 ) {
       
          // przelicznik
          $przelicznik = ($cena_produkt / $rozmiar);

          if ( $rozmiar < 500 && ( $this->infoSql['products_size_type'] == 'g' || $this->infoSql['products_size_type'] == 'ml' ) ) {

               if ( $rozmiar < 100 ) {

                    $cena = $GLOBALS['waluty']->FormatujCene( $przelicznik, 0, 0, $this->infoSql['products_currencies_id'] );        
           
                    $Wynik = '( ' . $cena['brutto'] . ' / 1 ' . ((isset($jmtab[$this->infoSql['products_size_type']])) ? $jmtab[$this->infoSql['products_size_type']][0] : '') . ')';                 
               
               } else {
                 
                    $cena = $GLOBALS['waluty']->FormatujCene( 100 * $przelicznik, 0, 0, $this->infoSql['products_currencies_id'] );        
           
                    $Wynik = '( ' . $cena['brutto'] . ' / 100 ' . ((isset($jmtab[$this->infoSql['products_size_type']])) ? $jmtab[$this->infoSql['products_size_type']][0] : '') . ')';
                    
               }
               
          } else {
            
               // jezeli jest g, ml lub mb to wchodzi 1000
               if ( $this->infoSql['products_size_type'] == 'g' || $this->infoSql['products_size_type'] == 'ml' ) {
                    //
                    $wskaznik = 1000;
                    //
               }
               
               // jezeli jest cb to wchodzi 100
               if ( $this->infoSql['products_size_type'] == 'cb' ) {
                    //
                    $wskaznik = 100;
                    //
               } 

               // jezeli jest c2 to wchodzi 10000
               if ( $this->infoSql['products_size_type'] == 'c2' ) {
                    //
                    $wskaznik = 10000;
                    //
               }           

               // jezeli jest c3 to wchodzi 1000000
               if ( $this->infoSql['products_size_type'] == 'c3' ) {
                    //
                    $wskaznik = 1000000;
                    //
               }    

               // jezeli jest szt to wchodzi 1
               if ( $this->infoSql['products_size_type'] == 'szt' ) {
                    //
                    $wskaznik = 1;
                    //
               }                
            
               // jezeli jest pkg to wchodzi 1
               if ( $this->infoSql['products_size_type'] == 'pkg' ) {
                    //
                    $wskaznik = 1;
                    //
               }                

               // jezeli jest pkg to wchodzi 1
               if ( $this->infoSql['products_size_type'] == 'tbl' ) {
                    //
                    $wskaznik = 1;
                    //
               }                

               $cena = $GLOBALS['waluty']->FormatujCene( $wskaznik * $przelicznik, 0, 0, $this->infoSql['products_currencies_id'] );        
           
               $Wynik = '( ' . $cena['brutto'] . ' / ' . ((isset($jmtab[$this->infoSql['products_size_type']])) ? $jmtab[$this->infoSql['products_size_type']][1] : '') . ')';
               
          }
       
     }
     
     unset($rozmiar, $cena_produkt, $jmtab);

}
       
?>