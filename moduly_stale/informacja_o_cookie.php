<?php
if (!isset($_COOKIE['akceptCookie'])) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

    echo "\n\n";
    
    echo '<div id="InfoCookie" class="DolnaInformacjaCookie"><div id="InfoCookieProste"><div class="CookieTekst">' . trim((string)preg_replace('/\s\s+/', ' ', (string)nl2br($GLOBALS['tlumacz']['INFO_COOKIE_TEKST']))) . '</div>';
    echo '<div class="InfoCookiePrzycisk"><span id="InfoAkcept" tabindex="0" role="button" class="przyciskCookie">' . trim((string)preg_replace('/\s\s+/', ' ', (string)nl2br($GLOBALS['tlumacz']['INFO_COOKIE_ZAMKNIJ']))) . '</span></div></div></div>';
    
    $KodJs = "<script>
              $(document).ready(function() {
                
                  $('#InfoAkcept').click( function() {
                      //
                      var data = new Date();
                      data.setTime(data.getTime() + (999*24*60*60*1000));               
                      //
                      document.cookie = 'akceptCookie=tak;expires=\"' + data.toGMTString() + '\";path=/';
                      //
                      $('#InfoCookieProste').stop().fadeOut();
                      $('#InfoCookie').stop().slideUp( function() {
                          $('#InfoCookie').remove();
                      });
                  }); 
                  
                  if ($('.InfoCookiePrzycisk').length) {
                      //
                      const clickable = $(\".InfoCookiePrzycisk\").find(\"a, button, input[type='submit'], [tabindex]:not([tabindex='-1'])\").filter(\":visible\").first();
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