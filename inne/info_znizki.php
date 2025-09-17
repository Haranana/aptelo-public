<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        //
        $Produkt = new Produkt( (int)$_POST['id'] );
        //
        // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
        $Przecinek = 2;
        // jezeli sa wartosci calkowite to dla pewnosci zrobi int
        if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
            $Przecinek = 0;
        }
        //         
        
        if ($Produkt->CzyJestProdukt == true) {

            echo '<div id="PopUpInfo" class="PopUpOpisInfoZnizki" aria-live="assertive" aria-atomic="true">';
            
            echo str_replace('{PRODUKT}', (string)$Produkt->info['nazwa'], (string)$GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_INFO_POPUP']);
            
            echo '<table class="ZnizkiInfo" cellspacing="5" cellpadding="5">';

            echo '<tr class="Naglowek">
                    <td>' . $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_ILOSC_SZTUK_POPUP'] . ' ' . $Produkt->info['jednostka_miary'] . '</td>
                    <td>' . (($Produkt->znizkiZalezneOdIlosciTyp == 'procent') ? $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_ZNIZKA_POPUP'] : $GLOBALS['tlumacz']['CENA']) . '</td>
                  </tr>';

                  foreach ( $Produkt->ProduktZnizkiZalezneOdIlosciTablica() As $Znizki ) {
                  
                    $ZakresZnizek = str_replace('{ZNIZKA_OD}', number_format($Znizki['od'], $Przecinek, '.', '' ) . ' ' . (string)$Produkt->info['jednostka_miary'], $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_ZAKRES_POPUP']);
                    $ZakresZnizek = str_replace('{ZNIZKA_DO}', number_format($Znizki['do'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary'], (string)$ZakresZnizek);

                    echo '<tr><td>' . $ZakresZnizek . '</td><td>';
                    
                    if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {
                      
                        echo $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH'];
                      
                    } else {

                        if ( $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {
                          
                            if ( $Znizki['znizka'] < 1 ) {
                                 //
                                 echo number_format($Znizki['znizka'], 2, '.', '' ) . '%';
                                 //
                            } else {
                                 //
                                 echo number_format($Znizki['znizka'], 0, '.', '' ) . '%';
                                 //
                            }
                            
                          } else {
                          
                            if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                              
                                 echo $Znizki['znizka'] . ' ' . $GLOBALS['tlumacz']['NETTO'];
                                 
                            } else {

                                 echo $Znizki['znizka'];
                                 
                                 if ( CENY_BRUTTO_NETTO == 'tak' ) {
                                   
                                      echo ' ' . $GLOBALS['tlumacz']['BRUTTO'];
                                   
                                 }                         
                              
                            }
                          
                        }
                        
                    }
                    
                    echo '</td></tr>';
                    
                    unset($ZakresZnizek);
                    
                  }    

            echo '</table>';     
            
            if ( $Produkt->znizkiZalezneOdIlosciTyp == 'procent' ) {

                echo '<br /><span class="Informacja">';    

                echo str_replace('{SKLADNIA}', ((ZNIZKI_OD_ILOSCI_PROMOCJE == 'tak') ? '' : '<b>'.$GLOBALS['tlumacz']['NIE'].'</b>'), (string)$GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_PROMOCJE_POPUP']) . ' ';
                
                echo str_replace('{SKLADNIA}', ((ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'tak') ? '' : '<b>'.$GLOBALS['tlumacz']['NIE'].'</b>'), (string)$GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_SUMOWANIE_POPUP']);

                echo '</span>';
                
            }
            
            echo '</div>';
        
        }
        
        //
        unset($Produkt);
        //

    }
    
}
?>