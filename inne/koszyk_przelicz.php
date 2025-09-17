<?php
chdir('../');            

if (isset($_POST['id']) || isset($_POST['idwiele'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
      
        echo '<div id="PopUpInfo" class="PopUpKoszykAktualizacja" aria-live="assertive" aria-atomic="true">';
    
        echo $GLOBALS['tlumacz']['INFO_AKTUALIZACJA_ILOSCI_KOSZYKA'] . ' <br />';
      
        // dla pojedynczego produktu
        if ( isset($_POST['id']) ) {

            $id = $filtr->process($_POST['id']);
            //        
            $Produkt = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );
            //
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            //
            $GLOBALS['koszykKlienta']->ZmienIloscKoszyka( $id, (float)$_POST['ilosc'] ); 
            //
            unset($Produkt, $id);
            //
            
        }
        
        // dla wielu produktow
        if ( isset($_POST['idwiele']) ) {
          
              foreach ( $filtr->process($_POST['idwiele']) as $TablicaWieluId ) {
                
                  $id = $TablicaWieluId[0];
                  //        
                  $GLOBALS['koszykKlienta']->ZmienIloscKoszyka( $id, (float)$TablicaWieluId[1], false ); 
                  //
                  unset($id);
                  //                

              }          
              
              $GLOBALS['koszykKlienta']->PrzeliczKoszyk();
          
        }
        
        echo '</div>';
        
        echo '<div id="PopUpPrzyciski" class="PopUpKoszykAktualizacjaPrzyciski">';
        
            echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
            
        echo '</div>';

    }
    
}
?>