<?php

class CacheJs {
  
    function CacheJsFunc() {
      
        global $db;
        //
        $WynikCache = $this->OdczytajCacheJs();
                 
        if ( !$WynikCache ) {
            //
            $zapytanie = 'select code, value from settings where js_type = "tak"';
            $sql = $db->open_query($zapytanie);
            
            $Wynik = array();
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            
                while ($info = $sql->fetch_assoc()) { 
                    //
                    $Wynik[] = array('kod' => base64_encode((string)$info['code']), 'wartosc' => base64_encode((string)$info['value']));
                    //
                }
                
                unset($info);
                
            }
            
            $db->close_query($sql);
            unset($zapytanie); 
            
            //
            $this->ZapiszCacheJs($Wynik);
            //
            
        } else {
        
            $Wynik = $WynikCache;
            
        }         
        
        foreach ( $Wynik as $Definicja ) {
         
            if ( isset($Definicja['kod']) && isset($Definicja['wartosc']) ) {
                 //
                 if ( !defined(base64_decode((string)$Definicja['kod'])) ) {
                      //
                      define(base64_decode((string)$Definicja['kod']), base64_decode((string)$Definicja['wartosc']));
                      //
                 }
                 //
            }
          
        }
        
        $WynikReturn = array();
        
        foreach ( $Wynik as $Definicja ) {
          
            $WynikReturn[] = array('kod' => base64_decode((string)$Definicja['kod']), 'wartosc' => base64_decode((string)$Definicja['wartosc']));
          
        }
        
        return $WynikReturn;
    
    }
    
    private function ZapiszCacheJs($dane) {

        $plikKlucz = fopen('cache/Cache_CacheJs','a+');
        if (!$plikKlucz) throw new Exception('Nie moge zapisac cache');

        flock($plikKlucz,LOCK_EX);

        fseek($plikKlucz,0);

        ftruncate($plikKlucz,0);

        $dane = serialize(array(time() + (86400), $dane));
        if (fwrite($plikKlucz,$dane)===false) {
            throw new Exception('Nie moge zapisac cache');
        }
        fclose($plikKlucz);

    }

    private function OdczytajCacheJs() {
    
        $filename = 'cache/Cache_CacheJs';
        if (!file_exists($filename)) return false;
        $plikKlucz = fopen($filename,'r');

        if (!$plikKlucz) return false;

        flock($plikKlucz,LOCK_SH);
        
        // Instrukcja warunkowa ze zmienną 'HTTP_IF_MODIFIED_SINCE' zwraca true, gdy w cache przeglądarki 
        // znajduje się zapamiętany skrypt – wtedy wysyłany jest nagłówek NOT MODIFIED i czas wykonywania drastycznie spada.
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ){
            header ("HTTP/1.0 304 Not Modified");
            exit;
        }            

        $dane = file_get_contents($filename);
        fclose($plikKlucz);

        $dane = @unserialize($dane);
        if (!$dane) {

            if ( is_file($filename) ) {
                unlink($filename);
            }
            return false;

        }

        if (time() > $dane[0]) {

            if ( is_file($filename) ) {
                unlink($filename);
            }
            return false;

        }

        return $dane[1];
        
    }
    
}
?>