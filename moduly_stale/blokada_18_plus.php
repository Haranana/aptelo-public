<?php
if (!isset($_COOKIE['akcept18plus'])) {  

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );
    
    $TablicaKategoriiBlokada = array();
    $WyswietlOkno = false;
    
    // sprawdzenie konfiguracji
    $zapytanieBlokada = "SELECT modul_settings_value FROM theme_modules_fixed_settings WHERE modul_settings_code = 'WYSWIETLANIE_W_KATEGORIACH'";
    $sqlBlokada = $GLOBALS['db']->open_query($zapytanieBlokada);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sqlBlokada) > 0) {
        //
        $infz = $sqlBlokada->fetch_assoc();
        //
        if ( $infz['modul_settings_value'] != '0' ) {
             //
             $TablicaKategoriiBlokada = explode(',', (string)$infz['modul_settings_value']);
             //
        }
        //
        unset($infz);
        //
    }
    //
    $GLOBALS['db']->close_query($sqlBlokada); 
    unset($zapytanieBlokada); 
    
    if ( count($TablicaKategoriiBlokada) == 0 ) {
         //
         $WyswietlOkno = true;
         //
    }
    
    if ( isset($_GET['idkat']) ) {       
         //
         $PodzielIdKat = explode('_', (string)Kategorie::WyczyscPath($_GET['idkat'],''));
         //
         if ( in_array($PodzielIdKat[ count($PodzielIdKat) - 1], $TablicaKategoriiBlokada) ) {
              //
              $WyswietlOkno = true;
              //
         }
         //
         unset($PodzielIdKat);
         //
    }
    if ( isset($_GET['idprod']) && (int)Funkcje::SamoIdProduktuBezCech($_GET['idprod']) > 0 ) {
         //
         $JakieKategorieMaProdukt = Kategorie::ProduktKategorie( (int)Funkcje::SamoIdProduktuBezCech($_GET['idprod']) );
         // 
         foreach ( $JakieKategorieMaProdukt as $Tmp ) {
             //
             if ( in_array($Tmp, $TablicaKategoriiBlokada) ) {
                  //
                  $WyswietlOkno = true;
                  break;
                  //
             }
             //
         }
         //
         unset($JakieKategorieMaProdukt);
         //
    }

    if ( $WyswietlOkno == true ) {

        echo "\n\n";
        
        echo '<div id="Blokada18"></div>';
        
        echo '<div id="BlokadaOkno">
        
                <strong>{__TLUMACZ:BLOKADA_18_NAGLOWEK}</strong>
                
                <div class="BlokadaTekst">
                    {__TLUMACZ:BLOKADA_18_INFO}
                </div>
                
                <div class="BlokadaPrzyciski">
                    <span class="przycisk18plus wejdz" tabindex="0" role="button">{__TLUMACZ:BLOKADA_18_WEJDZ}</span>';
                    
                    if ( count($TablicaKategoriiBlokada) == 0 ) {
                      
                         echo '<span class="przycisk18plus zrezygnuj" tabindex="0" role="button">{__TLUMACZ:BLOKADA_18_REZYGNACJA}</span>';
                         
                    } else {
                      
                         echo '<span><a href="/" class="przycisk18plus zrezygnuj">{__TLUMACZ:BLOKADA_18_REZYGNACJA}</a></span>';
                      
                    }
                    
                echo '</div>
                
              </div>';
        
        $KodJs = "<script>
                  $(document).ready(function() {

                    const clickable = $(\"#BlokadaOkno\").find(\"a, button, input[type='submit'], [tabindex]:not([tabindex='-1'])\").filter(\":visible\").first();
                    //
                    if (clickable.length) {
                        clickable.focus();
                    }

                    $('.BlokadaPrzyciski .wejdz').click( function() {
                        document.cookie = 'akcept18plus=tak';
                        $('#BlokadaOkno').stop().slideUp( 'fast', function() {
                            $('#Blokada18').fadeOut( function() {
                                window.location.reload();
                            });
                        });
                    });        
                    
                    $('.BlokadaPrzyciski .zrezygnuj').click( function() {
                        history.back(1);
                    }); 
                    
                    BylModulStaly = true;
               
                 });
                 </script>";
        
        echo jsMin::minify($KodJs);
        
        unset($KodJs);  
        
    }
    
    unset($TablicaKategoriiBlokada, $WyswietlOkno);
        
}
?>