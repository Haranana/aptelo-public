<?php
if ( $_SESSION['produktyPoprzednioOgladane'] !== null && count((array)$_SESSION['produktyPoprzednioOgladane']) > 0 && !isset($_COOKIE['oknoPoprzednie'])) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );
    
    //
    $licz = 1;
    //
    
    echo "\n\n";
    
    echo '<div id="PrzyklejOstatnieProd">
            <span class="Zamknij" title="{__TLUMACZ:PRZYCISK_ZAMKNIJ}"></span>
            <span class="Rozwiniecie">{__TLUMACZ:OSTATNIO_OGLADANE_PRODUKTY}</span>';
    
    echo '<ul>';
    //
    $OstatnioOgladane = array_reverse((array)$_SESSION['produktyPoprzednioOgladane']);
    //    
    foreach ($OstatnioOgladane AS $Id) {
        //
        if ( $licz < 11 ) {

            $Produkt = new Produkt( $Id );

            if ( isset($Produkt->info['link']) ) {
                 echo '<li>' . $Produkt->info['link'] . '</li>';
            }

            unset($Produkt);
            //
            $licz++;
            //
        }
    }
    
    echo '</ul>';
    
    echo '</div>';
    
    $KodJs = "<script>
              $(document).ready(function() {
                
                  // jezeli jest info o cookie to nie moze sie wyswietlac bo beda sie przykrywaly
                  if ( $('.DolnaInformacjaCookie').length ) {
                      $('#PrzyklejOstatnieProd').hide();
                  }
                  //
                  // animacja produktow poprzednio ogladanych - przyklejone na dole strony
                  $('#PrzyklejOstatnieProd').click( function() {
                      if ( $('#PrzyklejOstatnieProd ul').css('display') == 'none' ) {
                           $('#PrzyklejOstatnieProd ul').stop().slideDown( function() {
                              $('#PrzyklejOstatnieProd').fadeTo('fast', 0.9);
                              $('#PrzyklejOstatnieProd .Rozwiniecie').fadeTo('fast', 0.1);
                           });
                         } else {
                           $('#PrzyklejOstatnieProd ul').stop().slideUp( function() {
                              $('#PrzyklejOstatnieProd').fadeTo('fast', 0.5);
                              $('#PrzyklejOstatnieProd .Rozwiniecie').fadeTo('fast', 1);
                           });
                      } 
                  });
                  
                  $('#PrzyklejOstatnieProd .Zamknij').click( function() {
                      document.cookie = 'oknoPoprzednie=\"nie\"';
                      $('#PrzyklejOstatnieProd').stop().slideUp( function() {
                          $('#PrzyklejOstatnieProd').remove();
                      });
                  });
             
              });             
              </script>";
    
    echo jsMin::minify($KodJs);
    
    unset($KodJs);    
    
    unset($licz, $OstatnioOgladane);
    //    

}
?>