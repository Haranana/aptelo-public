<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;
    
    if ( KOSZYK_ZAPIS == 'tak' ) {
    
        if ((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') || KOSZYK_ZAPIS_ZALOGOWANI == 'nie') {
        
            if (Sesje::TokenSpr()) {
              
                $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK') ), $GLOBALS['tlumacz'] );
                
                $NazwaKoszyka = '';
                $OpisKoszyka = '';
                $UwagiKoszyka = '';
                
                if ( isset($_POST['nazwa_koszyka']) ) {
                     $NazwaKoszyka = $filtr->process($_POST['nazwa_koszyka']);
                }
                if ( isset($_POST['opis_koszyka']) ) {
                     $OpisKoszyka = $filtr->process($_POST['opis_koszyka']);
                }
                if ( isset($_POST['uwagi_koszyka']) ) {
                     $UwagiKoszyka = $filtr->process(trim((string)$_POST['uwagi_koszyka']));
                }
                
                $DaneZapisanegoKoszyka = $GLOBALS['koszykKlienta']->KoszykZapisz( $NazwaKoszyka, $OpisKoszyka, $UwagiKoszyka ); 

                unset($NazwaKoszyka, $OpisKoszyka);
                ?>
                
                <script>
                function copyToClipboard(element) {
                  var $temp = $("<input>");
                  $("body").append($temp);
                  $temp.val($(element).text()).select();
                  document.execCommand("copy");
                  $temp.remove();
                }            
                </script>
                
                <?php
                
                echo '<div class="Informacja" aria-live="assertive" aria-atomic="true">';
                
                if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                  
                     echo '<div><b style="font-size:120%">' . $GLOBALS['tlumacz']['KOSZYK_ZAPISANY'] . '</b></div>';
                     
                } else {
                  
                     echo '<div><b style="font-size:120%">' . $GLOBALS['tlumacz']['KOSZYK_ZAPISANY_NIEZALOGOWANY'] . '</b></div>';
                     
                }

                echo '<div style="padding:10px 0 10px 0">
                
                          <p id="LinkZapisanegoKoszyka" style="display:none">' . ADRES_URL_SKLEPU . '/koszyk.html/koszyk=' . $DaneZapisanegoKoszyka['id'] . '-' . $DaneZapisanegoKoszyka['czas'] . '</p>
                
                          ' . $GLOBALS['tlumacz']['LINK_DO_KOSZYKA'] . ' <input type="text" style="width:50%; font-size:120%" value="' . ADRES_URL_SKLEPU . '/koszyk.html/koszyk=' . $DaneZapisanegoKoszyka['id'] . '-' . $DaneZapisanegoKoszyka['czas'] . '" />
                      
                      </div>';

                echo '<div style="padding:10px 0 5px 0"><span role="button" tabindex="0" onclick="copyToClipboard(\'#LinkZapisanegoKoszyka\')" class="przycisk" style="user-select:none">' . $GLOBALS['tlumacz']['SKOPIUJ_DO_SCHOWKA'] . '</span></div>';

                echo '</div>';

            }
            
        }
        
    }
}
?>