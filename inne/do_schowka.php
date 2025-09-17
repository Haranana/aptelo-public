<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        //
        $Produkt = new Produkt( (int)$_POST['id'] );

        if ($Produkt->CzyJestProdukt == false) {
            exit;
        }
            
        // sprawdzi czy produkt nie byl dodany do schowka - jak to tak to go usunie
        if ( isset($GLOBALS['schowekKlienta']) ) {
          
             if ( $GLOBALS['schowekKlienta']->SprawdzCzyDodanyDoSchowka((int)$_POST['id']) ) { 

                  // zmiana na usuniecie schowka
                  $_POST['akcja'] = 'usun';
               
             }
          
        }

        if (!isset($_POST['akcja'])) {
                    
            // integracja z pixel Facebook
            IntegracjeZewnetrzne::PixelFacebookDoSchowkaDodanie( $Produkt );            

            // integracja z Google remarketing dynamiczny 
            IntegracjeZewnetrzne::GoogleRemarketingDoSchowkaDodanie( $Produkt );             

            echo '<div id="PopUpDodaj" class="PopUpSchowekDodany" aria-live="assertive" aria-atomic="true">';
        
            echo $GLOBALS['tlumacz']['INFO_DO_SCHOWKA_DODANY_PRODUKT'] . ' <br />';
            
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            
            echo '</div>';
            
            echo '<div id="PopUpPrzyciski" class="PopUpSchowekPrzyciskiDodanie">';
            
                echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                echo '<a href="' . Seo::link_SEO('schowek.php', '', 'inna') . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_SCHOWKA'].'</a>';
                
            echo '</div>';
                
            //
            if ( isset($GLOBALS['schowekKlienta']) ) {
                 $GLOBALS['schowekKlienta']->DodajDoSchowka( (int)$_POST['id'] );  
            }

        } else if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {
        
            echo '<div id="PopUpUsun" class="PopUpSchowekUsuniecie" aria-live="assertive" aria-atomic="true">';
        
            echo $GLOBALS['tlumacz']['INFO_DO_SCHOWKA_USUNIETY_PRODUKT'] . ' <br />';
            
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            
            echo '</div>';
            
            echo '<div id="PopUpPrzyciski" class="PopUpSchowekPrzyciskiUsuniecie">';
            
                echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                
            echo '</div>';
                
            //
            if ( isset($GLOBALS['schowekKlienta']) ) {
                 $GLOBALS['schowekKlienta']->UsunZeSchowka( (int)$_POST['id'] );    
            }
        
        }
        
        //
        unset($Produkt);
        //

    }
    
}
?>