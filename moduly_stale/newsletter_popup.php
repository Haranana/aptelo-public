<?php
if (isset($_POST['data'])) {
    //
    chdir('../'); 
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');  

}    
    
if ( NEWSLETTER_WLACZONY == 'tak' ) {

    /* jezeli ma sie pokazywac tylko zakladka
    if ( !isset($_COOKIE['newsletterPopup']) ) {
         setcookie('newsletterPopup', 'zwin', time()+3600);
         $_COOKIE['newsletterPopup'] = 'zwin';
    }
    */

    if (!isset($_COOKIE['newsletterPopup']) || (isset($_COOKIE['newsletterPopup']) && $_COOKIE['newsletterPopup'] == 'zwin') || isset($_POST['data'])) {

        $zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'newsletter_popup.php'";
        $sqlPopup = $GLOBALS['db']->open_query($zapytanie);
        while ( $info = $sqlPopup->fetch_assoc() ) {
            //
            if ( !defined($info['modul_settings_code']) ) {
                 define( $info['modul_settings_code'], $info['modul_settings_value'] );
            }
            //
        }    
        $GLOBALS['db']->close_query($sqlPopup);
        unset($info, $zapytanie);  

        if ( (int)NEWSLETTER_KUPON_WARTOSC > 0 ) {
          
            if ( !isset($_COOKIE['newsletterPopup']) || isset($_POST['data']) ) {

                $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

                echo "\n\n";
                
                echo '<div id="InfoNewsletter"' . ((CZAS_OPOZNIENIE != '0 sek') ? ' style="display:none"' : '') . '></div>';
                
                echo '<div id="InfoNewsletterOkno"' . ((CZAS_OPOZNIENIE != '0 sek') ? ' style="display:none"' : '') . '>';
                
                    echo '<div id="NewsletterZamknij" tabindex="0" role="button"><span id="span_'.uniqid().'"></span></div>';
                    
                    echo '<h4>' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_NAGLOWEK'] . '</h4>';
                    
                    echo '<div class="cl"></div>';
                    
                    // rodzaj kuponu
                    if ( NEWSLETTER_KUPON_RODZAJ == 'procent' ) {
                         //
                         $Kupon = (int)NEWSLETTER_KUPON_WARTOSC . '%';
                         //
                    } else {
                         //
                         $WartoscPrzeliczona = $CenaProduktu = $GLOBALS['waluty']->FormatujCene( (int)NEWSLETTER_KUPON_WARTOSC );             
                         $Kupon = $WartoscPrzeliczona['brutto'];
                         unset($WartoscPrzeliczona);
                         //
                    }
                
                    echo $GLOBALS['tlumacz']['NEWSLETTER_POPUP_OPIS'] . ' <b>' . $Kupon . '</b>';
                    
                    unset($Kupon);
                    
                    echo '<form action="/" onsubmit="return sprNewsletterPopup(this)" method="post" class="cmxform" id="newsletterPopup">';

                        echo '<p>';

                            echo '<input type="text" name="email" id="emailNewsletterPopup" value="' . $GLOBALS['tlumacz']['TWOJ_ADRES_EMAIL'] . '" />';
                            
                        echo '</p>'; 
                        
                        echo '<div class="UkryjPopUpNewsletter">';
                              
                            echo '<div id="ZgodaPopup">';
                            
                                if ( NEWSLETTER_ZGODA_MARKETING_WYSWIETLAJ == 'tak' ) {
                                     //
                                     echo '<label for="zgoda_newsletter_marketing"><input type="checkbox" name="zgoda_newsletter_marketing" id="zgoda_newsletter_marketing" value="1" /> ' . $GLOBALS['tlumacz']['NEWSLETTER_ZGODA_MARKETING'] . '<span class="check" id="check_zgoda_newsletter_marketing"></span></label>';
                                     //
                                } else {
                                     //
                                     echo '<input style="display:none" type="checkbox" name="zgoda_newsletter_marketing" checked="checked" value="1" />';
                                     //
                                }
                                
                                if ( NEWSLETTER_ZGODA_HANDLOWE_WYSWIETLAJ == 'tak' ) {
                                     //
                                     echo '<label for="zgoda_newsletter_info_handlowa"><input type="checkbox" name="zgoda_newsletter_info_handlowa" id="zgoda_newsletter_info_handlowa" value="1" /> ' . $GLOBALS['tlumacz']['NEWSLETTER_ZGODA_INFO_HANDLOWA'] . '<span class="check" id="check_zgoda_newsletter_info_handlowa"></span></label>';
                                     //
                                } else {
                                     //
                                     echo '<input style="display:none" type="checkbox" name="zgoda_newsletter_info_handlowa" checked="checked" value="1" />';
                                     //
                                }                          

                            echo '</div>';
                        
                        echo '</div>';

                        echo '<div id="BladDanych"></div>';
                        
                        echo '<div id="ZakonczNewsletter" style="margin-top:10px;display:none"><span role="button" tabindex="0" onclick="zamknijNewsletter(0)" tabindex="0" role="button" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_ZAMKNIJ'] . '</span></div>';
                        
                        echo '<div class="UkryjPopUpNewsletter">';
                        
                            echo '<p>';        
                            
                                echo '<input type="submit" id="submitNewsletterPopup" class="przyciskWylaczony" value="' . $GLOBALS['tlumacz']['PRZYCISK_ZAPISZ'] . '" />';
                                echo '<input type="hidden" name="popup" value="1" />';
                                
                            echo '</p>';
                        
                        echo '</div>';

                        // integracja z edrone
                        IntegracjeZewnetrzne::EdroneNewsletterPopup();
                    
                    echo '</form>';
                    
                    if ( NEWSLETTER_KUPON_MIN_WARTOSC > 0 ) {
                        //
                        $WartoscPrzeliczona = $CenaProduktu = $GLOBALS['waluty']->FormatujCene( (int)NEWSLETTER_KUPON_MIN_WARTOSC );             
                        $KuponMinZamowienie = $WartoscPrzeliczona['brutto'];
                        //        
                        echo '<div class="UkryjPopUpNewsletter"><small>' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_MIN_ZAMOWIENIE'] . ' ' . $KuponMinZamowienie . '</small></div>';
                        //
                        unset($WartoscPrzeliczona, $KuponMinZamowienie);
                    }

                echo '</div>';
                
                if ( NEWSLETTER_ZAKLADKA == 'tak') {
                    //
                    echo '<div id="ZakladkaNewsletterOkno"></div>';
                    //
                }
                
                if ( !isset($_POST['data']) ) {
                
                    echo '<script>';
                    echo '$.NewsletterPopup();';
                    echo '</script>';
                    
                    if ( CZAS_OPOZNIENIE != '0 sek' ) {

                        ?>
                        <script>
                        setTimeout(function(){ PokazNewsPop(); }, <?php echo (int)str_replace(' sek', '', (string)CZAS_OPOZNIENIE); ?> * 1000);            
                        </script>
                        <?php 

                    } else {
                      
                        ?>
                        <script>
                        PokazNewsPop();      
                        </script>
                        <?php 

                    }                        
                    
                }
                
            }
            
            if (isset($_COOKIE['newsletterPopup']) && $_COOKIE['newsletterPopup'] == 'zwin' && !isset($_POST['data'])) {
              
                if ( NEWSLETTER_ZAKLADKA == 'tak' ) {
                    //
                    echo '<div id="ZakladkaNewsletterOkno"></div>';
                    //
                }
              
            }

        }

    }
    
}
?>