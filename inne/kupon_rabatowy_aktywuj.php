<?php
chdir('../');            

if (isset($_POST['id']) && isset($_POST['akcja']) && $_POST['akcja'] == 'aktywuj') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KUPONY_RABATOWE') ), $GLOBALS['tlumacz'] );

        $kod = str_replace("'", '', (string)$filtr->process($_POST['id']));
        //

        if ( $kod != '' ) {

            $kupon = new Kupony($kod);
            $tablica_kuponu = $kupon->kupon;

            if ( count($tablica_kuponu) > 0 ) {

              if ( isset($tablica_kuponu['kupon_status']) && $tablica_kuponu['kupon_status'] ) {
              
                   echo '<div id="PopUpInfo" class="PopUpKuponAktywowanie" aria-live="assertive" aria-atomic="true">';

                       echo $GLOBALS['tlumacz']['KUPON_AKTYWOWANY'] . '<br />';
                       
                       echo '<h3>' . $kod . '</h3>';
                       
                       // jezeli kupon nie obejmuje wszystkich produktow
                       if ( $tablica_kuponu['mniejsza_wartosc'] ) {
                            echo '<br />' . $GLOBALS['tlumacz']['KUPON_WYBRANE_PRODUKTY'];
                       }
                       
                       // info ze kuponem nie sa objete produkty promocyjne
                       if ( $tablica_kuponu['warunek_promocja'] ) {
                            echo $GLOBALS['tlumacz']['KUPON_TYLKO_PROMOCJE']; 
                       }

                       if ( $tablica_kuponu['mniejsza_wartosc'] ) {
                            echo '. ' . $GLOBALS['tlumacz']['KUPON_CZESCIOWO'] . '<br />';
                       }             
              
                       if ( $tablica_kuponu['dostepne_wysylki'] != '' ) {
                            echo '<br />' . $GLOBALS['tlumacz']['KUPON_WYBRANE_WYSYLKI'] . '<br />';
                       }
                    
                   echo '</div>';                
                    
                   $_SESSION['kuponRabatowy'] = $filtr->process($tablica_kuponu);
                
              } else {
              
                   echo '<div id="PopUpUsun" class="PopUpKuponTylkoZalogowani" aria-live="assertive" aria-atomic="true">';

                   if ( isset($tablica_kuponu['tylko_zalogowani']) && $tablica_kuponu['tylko_zalogowani'] == true ) {
                    
                        echo $GLOBALS['tlumacz']['KUPON_ZALOGOWANI'];
                    
                   } else {
                  
                      if ( !$tablica_kuponu['grupa_klientow'] ) {
                        
                          // info ze kupon nie jest zgodny z email dla jakiego byl generowany
                          if ( $tablica_kuponu['warunek_popup'] || isset($tablica_kuponu['warunek_jedno_uzycie'])  || isset($tablica_kuponu['warunek_pierwsze_zakupy']) ) {
                            
                              echo $GLOBALS['tlumacz']['KUPON_NIE_MOZNA_UZYC'] . ' <br />';
                              
                          } else {
                            
                              if ( !$tablica_kuponu['niedostepna_forma_platnosci'] ) {
                          
                                    echo $GLOBALS['tlumacz']['KUPON_NIE_SPELNIA_WARUNKOW'] . ' <br />';
                              
                                    // info ze kuponem nie sa objete produkty promocyjne
                                    if ( $tablica_kuponu['warunek_promocja'] ) {
                                         echo $GLOBALS['tlumacz']['KUPON_WYBRANE_PRODUKTY'] . $GLOBALS['tlumacz']['KUPON_TYLKO_PROMOCJE'] . '. <br />';
                                    }
                              
                              } else {
                                
                                    echo $GLOBALS['tlumacz']['KUPON_FORMA_PLATNOSCI'] .' <br />';
                                
                              }

                          }                        
                          
                        } else {
                        
                          echo $GLOBALS['tlumacz']['KUPON_TYLKO_GRUPA_KLIENTOW'];
                        
                      }
                      
                   }
                  
                   echo '</div>';
                  
                   unset($_SESSION['kuponRabatowy']);
                
              }

            } else {

                echo '<div id="PopUpUsun" class="PopUpKuponBrakKuponu">';
                echo $GLOBALS['tlumacz']['KUPON_NIE_ISTNIEJE'] . ' <br />';
                echo '</div>';

                unset($_SESSION['kuponRabatowy']);

            }
        }

        echo '<div id="PopUpPrzyciski" class="PopUpKuponPrzyciski">';

        echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';

    }
    
}
?>