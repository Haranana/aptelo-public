<?php
chdir('../');            

if (isset($_POST['id']) && isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $id = $filtr->process($_POST['id']);
        //        
        $Produkt = new Produkt( $id );
        //

        // integracja z SALESmanago
        IntegracjeZewnetrzne::SalesManagoUsunZKoszyka( $Produkt ); 
        
        // integracja z kod Google remarketing dynamiczny ORAZ modul Google Analytics
        IntegracjeZewnetrzne::GoogleAnalyticsRemarketingUsunZKoszyka( $Produkt, $id );    
    
        echo '<div id="PopUpUsun" class="PopUpKoszykUsuniecie" aria-live="assertive" aria-atomic="true">';
    
        echo $GLOBALS['tlumacz']['INFO_DO_KOSZYKA_USUNIETY_PRODUKT'] . ' <br />';
        
        echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
        
        echo '</div>';
        
        echo '<div id="PopUpPrzyciski" class="PopUpKoszykUsunieciePrzyciski">';
        
            echo '<span role="button" tabindex="0" onclick="window.location.reload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
            
        echo '</div>';

        // koszyk w cookies - jezeli zostaje jeden produkt
        if ( isset($_COOKIE['koszykGold']) ) {
             //
             $IleKoszyku = 0;
             //
             if ( isset($_SESSION['koszyk']) ) {
                  //
                  // sprawdzi czy w koszyku nie ma produktow jako tylko akcesoria
                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                       //
                       if ( $TablicaZawartosci['status_akcesoria'] == 'nie' ) {
                            //
                            $IleKoszyku++;
                            //
                       }
                       //
                  } 
                  //                  
                  //
                  if ( $IleKoszyku == 1 ) {
                       //
                       $_SESSION['koszyk_usun'] = 'tak';
                       //
                  }
                  //
             }
             //
             unset($IleKoszyku);
             //             
        }
        
        $GLOBALS['koszykKlienta']->UsunZKoszyka( $id );    

        unset($Produkt);
        //

    }
    
}

if ( isset($_POST['idwiele']) && isset($_POST['akcja']) && $_POST['akcja'] == 'usun_zaznaczone') {
  
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {  
    
        // koszyk w cookies - jezeli zostaje jeden produkt
        if ( isset($_COOKIE['koszykGold']) ) {
             //
             if ( isset($_SESSION['koszyk']) && (count($_SESSION['koszyk']) == 1 || count($_SESSION['koszyk']) == count($_POST['idwiele'])) ) {
                  //
                  $_SESSION['koszyk_usun'] = 'tak';
                  //
             }
             //
        } 
        
        foreach ( $_POST['idwiele'] as $TablicaWieluId ) {
          
            $PodzielId = explode('_', (string)$TablicaWieluId[0]);

            $GLOBALS['koszykKlienta']->UsunZKoszyka($filtr->process($PodzielId[1]), false);          

            unset($PodzielId);

        }      
  
        $GLOBALS['koszykKlienta']->PrzeliczKoszyk(); 
    
    }
    
}
?>