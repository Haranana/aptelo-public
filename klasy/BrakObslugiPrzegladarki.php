<?php

class BrakObslugiPrzegladarki {

    public static function BrakObslugiPrzegladarkiSzablon() {
      
        if ( Wyglad::TypSzablonu() == true && isset($_SERVER['HTTP_USER_AGENT']) ) {
      
            $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
            
            $BrakObslugi = false;
            
            if ( preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos((string)$ua, 'Trident/7.0') !== false && strpos((string)$ua, 'rv:11.0') !== false) ) {
              
                $BrakObslugi = true;

            }
            
            if ( preg_match('~Safari~i', $ua) ) {
                
                 $known = array('Version', 'Safari', 'other');
                 $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
                 
                 if (!preg_match_all($pattern, $ua, $matches)) { }
                 
                 $wersja = '';
                
                 $i = count($matches['browser']);
                
                 if ( $i != 1 ) {
                     //
                     if ( strripos($ua,"Version") < strripos($ua,'Safari') ) {
                         $wersja = $matches['version'][0];
                     } else {
                         $wersja = $matches['version'][1];
                     }
                     //
                 } else {
                     //
                     $wersja = $matches['version'][0];
                     //
                 }     

                 $wersja = (int)$wersja;
                
                 if ( $wersja < 10 ) {
                   
                      if ( Wyglad::RodzajUrzadzania() == 'Komputer stacjonarny / laptop' ) {
                      
                           $BrakObslugi = true;
                           
                      }
                       
                 }
                
            }
            
            // sprawdzenie platnosci
            if ( isset($_SERVER['SCRIPT_NAME']) ) {
              
                $aktualnySkryptSciezka = explode('/', (string)$_SERVER['SCRIPT_NAME']);

                if ( in_array('platnosc', $aktualnySkryptSciezka) && in_array('raporty', $aktualnySkryptSciezka) ) {
                     //
                     $BrakObslugi = false;
                     //
                }
                
                unset($aktualnySkryptSciezka);
            
            }
            
            if ( $BrakObslugi == true ) {

                 // domyslne meta tagi
                 $Meta = MetaTagi::ZwrocMetaTagi();
                 // obsluga pliku wylaczenia
                 //
                 if (file_exists('szablony/'.DOMYSLNY_SZABLON.'/tresc/brak_obslugi_przegladarki.tp')) {
                     //
                     $tpl = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/tresc/brak_obslugi_przegladarki.tp');
                     //
                   } else {
                     //
                     $tpl = new Szablony('szablony/__tresc/brak_obslugi_przegladarki.tp');
                     //
                 }
                 $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
                 $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
                 $tpl->dodaj('__META_OPIS', $Meta['opis']);
                 $tpl->dodaj('__TEKST_WYLACZENIA', nl2br(INFO_WYLACZ_SKLEP_INFO));
                 echo $tpl->uruchom();
                 //
                 unset($Meta);
                 //
                 exit;
                
            }
            
        }
            
    }

}
?>