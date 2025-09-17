<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php'); 
    
    if (Sesje::TokenSpr()) {
    
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
    
            // sprawdzi zgodnosc id koszyka i klienta
            $zapytanie = "SELECT * FROM basket_save";
            $sql = $GLOBALS['db']->open_query($zapytanie);    

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                while ($info = $sql->fetch_assoc()) {
                  
                       if ( $info['basket_id'] == (int)$_POST['id'] && $info['customers_id'] == (int)$_SESSION['customer_id'] ) {
              
                            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI_PANEL') ), $GLOBALS['tlumacz'] );
                          
                            $WczytanyKoszyk = $GLOBALS['koszykKlienta']->WczytajKoszyk( (int)$_POST['id'] );  

                            echo '<div id="PopUpInfo" class="PopUpKoszykWczytanie" aria-live="assertive" aria-atomic="true">';

                            echo $GLOBALS['tlumacz']['KOSZYK_WCZYTANY'] . ' ';
                            
                            if ( $WczytanyKoszyk == false ) {
                            
                               echo $GLOBALS['tlumacz']['KOSZYK_WCZYTANY_PROBLEM'];
                               
                            }

                            echo '<br /> </div>';
                            
                            echo '<div id="PopUpPrzyciski" class="PopUpKoszykWczytaniePrzyciski">';
                            
                                echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_ZAMKNIJ'].'</span>';
                                
                                echo '<a href="koszyk.html" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_KOSZYKA'].'</a>';
                                
                            echo '</div>';
                            
                       }
                      
                }
                
                unset($info);
                
            }
            
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);                        

        }
        
    }
    
}
?>