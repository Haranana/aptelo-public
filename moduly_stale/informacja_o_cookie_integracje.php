<?php
if (!isset($_COOKIE['akceptCookie'])) {

    $zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'informacja_o_cookie_integracje.php'";
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
        
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

    echo "\n\n";
    
    // usuniecie cookies
    if ( isset($_COOKIE['cookieFunkcjonalne']) ) {
         setcookie('cookieFunkcjonalne', '', time() - 3600, '/');
    }
    if ( isset($_COOKIE['cookieAnalityczne']) ) {
         setcookie('cookieAnalityczne', '', time() - 3600, '/');
    }
    if ( isset($_COOKIE['cookieReklamowe']) ) {
         setcookie('cookieReklamowe', '', time() - 3600, '/');
    }    
    ?>
    
    <div id="CookieOknoUstawien">
    
        <div class="CookieOkno">
        
            <div class="CookieOknoKont cmxform">
            
                <div id="CookieOknoZamknij" tabindex="0" role="button"><span id="span_<?php echo uniqid(); ?>"></span></div>
                
                <div class="NaglowekCookieOkno">
                
                    <?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_NAGLOWEK']; ?> 
                    
                </div>          
                
                <div class="CookiesOknoInfo" style="padding:5px 0 10px 0">
                
                    <?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_OPIS']; ?> 

                </div>

                <div class="CookieZgody">
                
                    <div class="CookieOknoUstawienia">

                        <div class="CookieOknoUstawieniaInput">
                        
                            <label for="cookieNiezbedne" style="opacity:0.5"><input type="checkbox" name="cookie_niezbedne" id="cookieNiezbedne" checked="checked" disabled="disabled" /><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_NIEZBEDNE']; ?><span class="check" id="check_cookieNiezbedne"></span></label>
                            
                        </div>
                    
                        <div class="CookieOknoUstawieniaOpis"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_NIEZBEDNE_OPIS']; ?></div>
                    
                    </div>
                    
                    <div class="CookieOknoUstawienia">

                        <div class="CookieOknoUstawieniaInput">
                        
                            <label for="cookieFunkcjonalne"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_FUNKCJONALNE']; ?><input type="checkbox" name="cookie_funkcjonalne" id="cookieFunkcjonalne" /><span class="check" id="check_cookieFunkcjonalne"></span></label>
                            
                        </div>
                    
                        <div class="CookieOknoUstawieniaOpis"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_FUNKCJONALNE_OPIS']; ?></div>
                    
                    </div> 

                    <div class="CookieOknoUstawienia">

                        <div class="CookieOknoUstawieniaInput">
                        
                            <label for="cookieAnalityczne"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_ANALITYCZNE']; ?><input type="checkbox" name="cookie_analityczne" id="cookieAnalityczne" /><span class="check" id="check_cookieAnalityczne"></span></label>
                            
                        </div>
                    
                        <div class="CookieOknoUstawieniaOpis"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_ANALITYCZNE_OPIS']; ?></div>
                    
                    </div>      

                    <div class="CookieOknoUstawienia">

                        <div class="CookieOknoUstawieniaInput">
                        
                            <label for="cookieReklamowe"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_REKLAMOWE']; ?><input type="checkbox" name="cookie_reklamowe" id="cookieReklamowe" /><span class="check" id="check_cookieReklamowe"></span></label>
                            
                        </div>
                    
                        <div class="CookieOknoUstawieniaOpis"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_REKLAMOWE_OPIS']; ?></div>
                    
                    </div> 

                </div>
                
                <div class="GotoweCookieOkno">
                
                    <span class="przycisk" tabindex="0" role="button"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_PRZYCISK_ZATWIERDZAM']; ?></span>
                    
                </div>                   
            
            </div>
        
        </div>
    
    </div>
    
    <div id="InfoCookieTlo">
    
        <div id="InfoCookieIntegracje" class="DolnaInformacjaCookie">
        
            <div class="Strona">

                <div class="CookieTekstIntegracje"><?php echo trim((string)preg_replace('/\s\s+/', ' ', (string)nl2br($GLOBALS['tlumacz']['INFO_COOKIE_TEKST']))); ?></div>

                <div class="CookieIntegracjePrzyciski">
                
                    <div class="CookiePrzyciskUstawien">
                    
                        <span id="InfoUstawieniaIntegracje" tabindex="0" role="button"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_PRZYCISK_USTAWIENIA']; ?></span>
                        
                    </div>
                    
                    <?php if ( COOKIE_INTEGRACJA_WYSWIETL_TYLKO_NIEZBEDNE == 'tak' ) { ?>
                    
                    <div class="CookiePrzyciskUstawien">
                    
                        <span id="InfoTylkoNiezbedneIntegracje" tabindex="0" role="button"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_PRZYCISK_NIEZBEDNE']; ?></span>
                        
                    </div>
                    
                    <?php } ?>
                    
                    <div class="CookiePrzyciskAkceptacja">
                    
                        <span id="InfoAkceptIntegracje" class="przyciskCookie" tabindex="0" role="button"><?php echo $GLOBALS['tlumacz']['COOKIE_INTEGRACJE_PRZYCISK_ZGADZAM_SIE']; ?></span>
                        
                    </div>
                    
                    <div class="cl"></div>
                    
                </div>
                
                <div class="cl"></div>
                
            </div>
        
        </div>
        
    </div>

    <?php
    $KodJs = "<script>
    
              $(document).ready(function() {
                
                // sprawdzi czy nie jest wlaczony podstawowy modul cookie
                if ( $('#InfoCookie').length ) {
                     $('#InfoCookie').hide();
                }
                
                function KonwertujObiekt(text) {
                  var jsonString = '{' + text + '}';
                  jsonString = jsonString.replace(/'/g, '\"');
                  try {
                    var jsonObject = JSON.parse(jsonString);
                    return jsonObject;
                  } catch (e) {
                    return null;
                  }
                }                
                
                function AktualizacjaZgoda() {
                     PreloadWlacz();
                     $.post(\"inne/zgody_google.php?tok=" . (string)Sesje::Token() . "\", { id: " . rand(1,100000) . " }, function(data) { 
                        PreloadWylaczSzybko();                        
                        if ( data != '' ) {
                             var wynikDataText = data;
                             var wynikData = KonwertujObiekt(wynikDataText);
                             if (wynikData) {
                                gtag('consent', 'update', wynikData);
                                window.dataLayer.push({ 'event': 'consentUpdate' });
                             }
                        }
                    });
                }
              
                $('#InfoAkceptIntegracje').click( function() {
                    //
                    var data = new Date();
                    data.setTime(data.getTime() + (999*24*60*60*1000));               
                    //
                    document.cookie = 'akceptCookie=tak;expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    document.cookie = 'cookieFunkcjonalne=tak;expires=\"' + data.toGMTString() + '\";path=/';
                    document.cookie = 'cookieAnalityczne=tak;expires=\"' + data.toGMTString() + '\";path=/';
                    document.cookie = 'cookieReklamowe=tak;expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    $('#CookieOknoUstawien').remove();
                    $('#InfoCookieTlo').remove();
                    //
                    AktualizacjaZgoda();
                    //
                }); 
                
                " . ((COOKIE_INTEGRACJA_WYSWIETL_TYLKO_NIEZBEDNE == 'tak') ? "$('#InfoTylkoNiezbedneIntegracje').click( function() {
                    //
                    var data = new Date();
                    data.setTime(data.getTime() + (999*24*60*60*1000));               
                    //
                    document.cookie = 'akceptCookie=tak;expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    document.cookie = 'cookieFunkcjonalne=nie;expires=\"' + data.toGMTString() + '\";path=/';
                    document.cookie = 'cookieAnalityczne=nie;expires=\"' + data.toGMTString() + '\";path=/';
                    document.cookie = 'cookieReklamowe=nie;expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    $('#CookieOknoUstawien').remove();
                    $('#InfoCookieTlo').remove();
                    //
                    AktualizacjaZgoda();
                    //
                });" : "") . "              
                
                $('#InfoUstawieniaIntegracje').click( function() {
                    //
                    $('#InfoCookieIntegracje').hide();
                    $('#CookieOknoUstawien').stop().fadeIn(function() {
                        //
                        const clickable = $(\"#CookieOknoUstawien\").find(\"a, button, input[type='submit'], [tabindex]:not([tabindex='-1'])\").filter(\":visible\").first();
                        //
                        if (clickable.length) {
                            clickable.focus();
                        }
                        //
                    });
                    //
                });
                
                $('#CookieOknoZamknij').click(function() {
                    //
                    $('#CookieOknoUstawien').stop().fadeOut();
                    $('#InfoCookieIntegracje').stop().fadeIn();
                    //
                });   
                
                $(document).on('keydown', function(e) {
                    //
                    if (e.key === 'Escape' || e.keyCode === 27) {
                        if ($('#CookieOknoUstawien').is(':visible')) {
                            $('#CookieOknoUstawien').stop().fadeOut();
                            $('#InfoCookieIntegracje').stop().fadeIn();
                        }
                    }
                    //
                });                

                $('.GotoweCookieOkno span').click(function() {
                    //
                    var data = new Date();
                    data.setTime(data.getTime() + (999*24*60*60*1000));               
                    //
                    document.cookie = 'akceptCookie=tak;expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    var funkcjonalne = 'tak';
                    if ( $('#cookieFunkcjonalne').prop('checked') == false ) {
                         funkcjonalne = 'nie';
                    }
                    document.cookie = 'cookieFunkcjonalne=' + funkcjonalne + ';expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    var analityczne = 'tak';
                    if ( $('#cookieAnalityczne').prop('checked') == false ) {
                         analityczne = 'nie';
                    }
                    document.cookie = 'cookieAnalityczne=' + analityczne + ';expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    var reklamowe = 'tak';
                    if ( $('#cookieReklamowe').prop('checked') == false ) {
                         reklamowe = 'nie';
                    }
                    document.cookie = 'cookieReklamowe=' + reklamowe + ';expires=\"' + data.toGMTString() + '\";path=/';
                    //
                    $('#CookieOknoUstawien').remove();
                    $('#InfoCookieTlo').remove();
                    //
                    AktualizacjaZgoda();
                    //
                });
                
                if ($('#InfoCookieTlo').length) {
                    //
                    const clickable = $(\"#InfoCookieTlo\").find(\"a, button, input[type='submit'], [tabindex]:not([tabindex='-1'])\").filter(\":visible\").first();
                    //
                    if (clickable.length) {
                        clickable.focus();
                    }
                    //
                }    
    
            });
            
            </script>";

    echo jsMin::minify($KodJs);
    
    unset($KodJs); 

}
?>