<?php

if ( isset($pobierzFunkcje) ) {

    $ZnizkaTablica = array();

    if ($this->znizkiZalezneOdIlosci != '') {
        //
        $JakieZnizki = explode(';', (string)$this->znizkiZalezneOdIlosci);
        //
        for ($k = 0, $l = count($JakieZnizki); $k < $l; $k++) {
            //
            $Znizka = '';
            $ZnizkaNetto = '';
            
            $PodzialZnizki = explode(':', (string)$JakieZnizki[$k]);
            //
            if ( $this->znizkiZalezneOdIlosciTyp == 'cena' ) {
                 //
                 if ( isset($_SESSION['poziom_cen']) ) {
                      $Znizka = $PodzialZnizki[ $_SESSION['poziom_cen'] + 1 ];
                      
                      if ( ZNIZKI_OD_ILOSCI_RABATY_DLA_CEN == 'tak' && $this->info['rabat_produktu'] > 0 ) {
                           $Znizka = $Znizka - ($Znizka * $this->info['rabat_produktu']/100);
                      }
                      
                      // jezeli w cenach netto
                      if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                        
                           $Znizka = round(($Znizka / (1 + ($this->vat_podstawa/100))), 2);
                      
                      }
                      
                      $ZnizkaNetto = round(($Znizka / (1 + ($this->vat_podstawa/100))), 2);
                      
                      //
                      $CenaZnizka = $GLOBALS['waluty']->FormatujCene( $Znizka, 0, 0, $this->infoSql['products_currencies_id'], false );
                      $CenaZnizkaNetto = $GLOBALS['waluty']->FormatujCene( 0, $ZnizkaNetto, 0, $this->infoSql['products_currencies_id'], false );
                      //
                      $Znizka = $GLOBALS['waluty']->WyswietlFormatCeny( $CenaZnizka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false );
                      $ZnizkaNetto = $GLOBALS['waluty']->WyswietlFormatCeny( $CenaZnizkaNetto['netto'], $_SESSION['domyslnaWaluta']['id'], true, false ) . ' ' . $GLOBALS['tlumacz']['NETTO'];
                 }
            }
            if ( $this->znizkiZalezneOdIlosciTyp == 'procent' ) {
                 //
                 if ( isset($_SESSION['poziom_cen']) ) {
                      $Znizka = $PodzialZnizki[2];
                 }
            }            
            //
            if ( $Znizka != '' ) {
                 //
                 $ZnizkaTablica[] = array('od'           => $PodzialZnizki[0],
                                          'do'           => $PodzialZnizki[1],
                                          'znizka'       => $Znizka,
                                          'znizka_netto' => $ZnizkaNetto);
                 //
            }
            //
            unset($PodzialZnizki, $Znizka);
            //
        }
        //
        unset($JakieZnizki);
        //         
    }

}
       
?>