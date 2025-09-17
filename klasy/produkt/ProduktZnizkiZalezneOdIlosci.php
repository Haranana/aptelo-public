<?php

if ( isset($pobierzFunkcje) ) {

    $ZnizkaWynik = 0;

    if ($this->znizkiZalezneOdIlosci != '') {
        //
        $JakieZnizki = explode(';', (string)$this->znizkiZalezneOdIlosci);
        //
        for ($k = 0, $l = count($JakieZnizki); $k < $l; $k++) {
            //
            $Znizka = '';
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
                      //
                      $TabZnizka = $GLOBALS['waluty']->FormatujCene( $Znizka, 0, 0, $this->infoSql['products_currencies_id'], false );
                      $Znizka = $TabZnizka['brutto'];
                      //
                 }
            }
            if ( $this->znizkiZalezneOdIlosciTyp == 'procent' ) {
                 //
                 if ( isset($_SESSION['poziom_cen']) ) {
                      $Znizka = (float)$PodzialZnizki[2];
                 }
            }            
            //
            if ($ilosc >= $PodzialZnizki[0] && $ilosc <= $PodzialZnizki[1]) {
                $ZnizkaWynik = $Znizka;
            }
            unset($PodzialZnizki, $Znizka);
            //
        }
        //
        unset($JakieZnizki);
        //
    }

}
       
?>