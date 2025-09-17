<?php
if (( isset($_GET['css']) && !empty($_GET['css']) ) || ( isset($_GET['ncss']) && !empty($_GET['ncss']) )) {

    function compress( $bufor ) {
        //
        // tablica znakow
        $znaki = array(', '    => ',',
                       ' , '   => ',',
                       ';}'    => '}',
                       '; }'   => '}',
                       ' ; }'  => '}',
                       ' :'    => ':',
                       ': '    => ':',
                       ' {'    => '{',
                       '{ '    => '{',
                       '; '    => ';');
                       
        /* usuwa komentarze */
        $bufor = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', (string)$bufor );
        
        /* usuwa tabulatory, spacje, entery etc. */
        $bufor = str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', (string)$bufor );
        
        // wzorce reg:
        // 1 => minimalizuj wartości HEX kolorów
        // 2 => wywal wszystkie nawiasy z adresów url
        // 3 => skróć wartości reguły 'font-weight' do wartości liczbowych
        $szukaj = array(
            1 => '/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i',
            2 => '/url\([\'"](.*?)[\'"]\)/s',
        );

        $zamien = array(
            1 => '$1#$2$3$4$5',
            2 => 'url($1)',
        );
        $bufor = preg_replace($szukaj, $zamien, (string)$bufor);
        
        $bufor = str_ireplace( array_keys($znaki), array_values($znaki), (string)$bufor );    
        
        return $bufor;
    }

    if ( (isset($_GET['css']) && is_array($_GET['css'])) || (isset($_GET['ncss']) && is_array($_GET['ncss'])) ) {
         exit;
    }
    
    if ( isset($_GET['css']) ) {
         //
         $plikiCss = explode(',', (string)$_GET['css']);
         //
       } else if ( isset($_GET['ncss']) ) { 
         //
         $plikiCss = explode(',', (string)$_GET['ncss']);
         //
    }
    
    if ( is_array($plikiCss) ) {
    
        header('Content-type: text/css; charset=utf-8');
        ob_start();
    
        foreach ( $plikiCss as $plik ) {
            //
            // css zebra kalendarz
            if ( $plik == 'zebra_datepicker' ) {
                include('../../../programy/zebraDatePicker/css/zebra_datepicker.css');
            } else {
                //
                $plik = preg_replace("/[^A-Za-z0-9_]/", "", (string)$plik);
                
                if ( file_exists($plik . '.css') ) {
                     include( $plik . '.css' );
                }
            }
            //
        }
        include('../../../programy/slickSlider/slick.css');
        include('../../../programy/slickSlider/slick-theme.css');
        include('../../../programy/jBox/jBox.all.css');

        $wynikCss = ob_get_contents();
        
        ob_end_clean(); 
        
        // jezeli jest wlaczona kompresja
        if ( isset($_GET['css']) ) {
             //
             echo compress($wynikCss);
             //
           } else { 
             //
             echo $wynikCss;
             //
        }
    
    }
    
}
?>