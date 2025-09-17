<?php

class Przekierowania {

    public static function SprawdzPrzekierowania( $produkt = false ) {

        if ( PRZEKIEROWANIA == 'tak' ) {
            //

            $Przekierowania = array();
            
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('Przekierowania', CACHE_INNE);
            
            if ( !$WynikCache ) {
                 //
                 $zapUrl = "select distinct forwarding, urlf, urlt, type from location";
                 $sql = $GLOBALS['db']->open_query($zapUrl);
                 //
                 if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                       //
                       while ($info = $sql->fetch_assoc()) {
                             //
                             $Przekierowania[ $info['urlf'] ] = array('adres' => $info['urlt'],
                                                                      'typ' => $info['type'],
                                                                      'przekierowanie_produkt' => $info['forwarding']);   
                             //
                       }
                       //
                       unset($info);
                       //
                 }
                 //
                 $GLOBALS['db']->close_query($sql);
                 $GLOBALS['cache']->zapisz('Przekierowania', $Przekierowania, CACHE_INNE);
                 //
                 unset($zapUrl, $sql);
                 //
            } else {
                 //
                 $Przekierowania = $WynikCache;
                 //
            }   
            
            $Przekierowanie = false;

            $urlAktualny = trim((string)$_SERVER['REQUEST_URI'], '/'); 
            
            foreach ( $Przekierowania as $Poprzedni => $Url ) {
            
                if ( $urlAktualny == trim((string)$Poprzedni, '/') && $urlAktualny != trim((string)$Url['adres'], '/') && (($Url['przekierowanie_produkt'] == 0 && $produkt == false) || ($Url['przekierowanie_produkt'] == 1 && $produkt == true)) ) {
                     //
                     if ( $Url['typ'] != '302' ) {
                          //
                          $Przekierowanie = true;
                          header('HTTP/1.1 301 Moved Permanently');
                          header('Location: ' . ADRES_URL_SKLEPU . '/' . $Url['adres']);
                          header('Connection: close');
                          exit;
                          //
                     } else {
                          //
                          $Przekierowanie = true;
                          header('HTTP/1.1 302 Found');
                          header('Location: ' . ADRES_URL_SKLEPU . '/' . $Url['adres']);
                          header('Connection: close');
                          exit;
                          //
                     }
                     //
                }
                
            }
            
            unset($Przekierowania, $urlAktualny);
            
            return $Przekierowanie;
            
        }
            
    }

}
?>