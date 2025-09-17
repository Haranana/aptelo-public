<?php
if (!isset($_COOKIE['exitPopup']) || (isset($_COOKIE['exitPopup']) && $_COOKIE['exitPopup']) == 'wiele') {

    $zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value, tmfd.language_id from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'exit_popup.php'";
    $sqlPopup = $GLOBALS['db']->open_query($zapytanie);
    //
    $tresci = array();
    //
    while ( $info = $sqlPopup->fetch_assoc() ) {
        //
        if ( !defined($info['modul_settings_code']) && $info['modul_settings_code'] != 'EXIT_POPUP_TRESC_WIADOMOSCI' ) {
             define( $info['modul_settings_code'], $info['modul_settings_value'] );
        }
        //
        if ( $info['modul_settings_code'] == 'EXIT_POPUP_TRESC_WIADOMOSCI' ) {
             $tresci[(int)$info['language_id']] = $info['modul_settings_value'];
        }
        //
    } 
    //
    $GLOBALS['db']->close_query($sqlPopup);
    unset($info, $zapytanie);  
    
    // warunki
    $warunki = true;
    
    // dodany koszyk    
    if ( EXIT_POPUP_KOSZYK == 'tak' && isset($GLOBALS['koszykKlienta']) && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() == 0 ) {  
         $warunki = false;
    }
    
    // tylko raz
    if ( EXIT_POPUP_ILE_RAZY == 'tylko raz' && isset($_COOKIE['exitPopup']) ) {
         $warunki = false;
    }
    
    // zalogowany
    if ( EXIT_POPUP_ZALOGOWANY == 'tak' ) {
         //
         if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
              $warunki = true;              
         } else {
              $warunki = false;
         }
         //
    }
    
    // tylko okreslone strony
    if ( EXIT_POPUP_JAKIE_STRONY != 'wszystkie strony' ) {
         //
         if ( EXIT_POPUP_JAKIE_STRONY == 'tylko strona główna' && $GLOBALS['stronaGlowna'] != true ) {
              $warunki = false;
         }
         //
         $aktualnySkryptSciezka = explode('/',$_SERVER['SCRIPT_NAME']);
         $aktualnySkrypt = $aktualnySkryptSciezka[ count($aktualnySkryptSciezka)-1 ];
         //
         if ( EXIT_POPUP_JAKIE_STRONY == 'tylko karta produktu' && $aktualnySkrypt != 'produkt.php' ) {
              $warunki = false;
         }
         //
         if ( EXIT_POPUP_JAKIE_STRONY == 'tylko listing produktów' && $aktualnySkrypt != 'listing.php' ) {
              $warunki = false;
         }
         //
         if ( EXIT_POPUP_JAKIE_STRONY == 'tylko koszyk' && $aktualnySkrypt != 'koszyk.php' ) {
              $warunki = false;
         }
         //
    }
         
    if ( isset($tresci[(int)$_SESSION['domyslnyJezyk']['id']]) && !empty($tresci[(int)$_SESSION['domyslnyJezyk']['id']]) && $warunki == true ) { ?>
    
        <div id="TloExitPopUp">
        
            <div id="TloExitPopUpZamknij"></div>

            <div class="ExitPopUpOkno" style="max-width:calc(<?php echo EXIT_POPUP_SZEROKOSC; ?> + 40px)">

                <div class="ExitPopUpOknoKont FormatEdytor">
                
                    <div id="ExitPopUpZamknij" tabindex="0" role="button"><span id="span_<?php echo uniqid(); ?>"></span></div>   
                    
                    <?php echo $tresci[$_SESSION['domyslnyJezyk']['id']]; ?>

                </div>
                
            </div>
            
        </div>
    
        <?php
    
         $KodJs = "<script>
                  $(document).ready(function() {
                      
                      var opoznienie = false;
                      
                      $(document).on('mouseleave', function (e) {
                          //
                          if (e.clientY <= 0 && !opoznienie) {
                              //
                              opoznienie = true;
                              //
                              setTimeout(function() {
                                $('#TloExitPopUp').show();
                                $('.ExitPopUpOkno').addClass('AnimacjaExitPopUpOkno');
                                //
                                const clickable = $(\"#TloExitPopUp\").find(\"a, button, input[type='submit'], [tabindex]:not([tabindex='-1'])\").filter(\":visible\").first();
                                //
                                if (clickable.length) {
                                    clickable.focus();
                                }
                                //                                
                              }, " . ((float)str_replace('s', '', EXIT_POPUP_OPOZNIENIE) * 1000) . ");
                              //
                          }
                          //
                      });
      
                      $('#ExitPopUpZamknij, #TloExitPopUpZamknij').click(function() {
                          //
                          let data = new Date();
                          data.setTime(data.getTime() + (999*24*60*60*1000));    
                          //
                          $('#TloExitPopUp').stop().fadeOut();
                          //
                          document.cookie = 'exitPopup=" . ((EXIT_POPUP_ILE_RAZY == 'tylko raz') ? "raz" : "wiele") . ";expires=\"' + data.toGMTString() + '\";path=/';
                          //
                      });
                      
                      $(document).on('keydown', function(e) {
                          //
                          if (e.key === 'Escape' || e.keyCode === 27) {
                              if ($('#TloExitPopUp').is(':visible')) {
                                  $('#TloExitPopUp').stop().fadeOut();
                              }
                          }
                          //
                      });                

                  });
                  </script>";
        
         echo jsMin::minify($KodJs);
         
         echo $KodJs;
        
         unset($KodJs);  
         
    }
 
}
?>