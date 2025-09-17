<?php
class Szablony {

    public $nazwa;
    public $pA;
    public $pB;
    public $pC;
    public $pD;
    public $pE;
    public $parametr;
    public $dane;
    public $tmpl;

    function __construct($nazwa = '', $__pA = null, $__pB = null, $__pC = null, $__pD = null, $__pE = null) {
        global $nawigacja;
        
        $this->nazwa = $nazwa;
        $this->pA = $__pA;
        $this->pB = $__pB;
        $this->pC = $__pC;
        $this->pD = $__pD;
        $this->pE = $__pE;
        
        $this->parametr = array();
     
        $this->dane = Array();

    }

    function dodaj($nazwa, $wartosc = '') {
        if (is_array($nazwa)) {
            $this->dane = array_merge($this->dane, $nazwa);
        } else {
            $this->dane[$nazwa] = $wartosc; 
        }
    }
    
    function dodaj_dodatkowo($nazwa, $wartosc = '') {
        if ( isset($this->dane[$nazwa]) ) {
             $this->dane[$nazwa] = $this->dane[$nazwa] . $wartosc; 
        }
    }    
    
    function parametr($nazwa, $wartosc) {
        $this->parametr[$nazwa] = $wartosc;
    }

    function uruchom($glowny = false, $noindex = false) {
        global $i18n, $nawigacja;

        $__pA = $this->pA;
        $__pB = $this->pB;
        $__pC = $this->pC;
        $__pD = $this->pD;
        $__pE = $this->pE;
        $__Parametr = $this->parametr;
    
        ob_start();
        
        $Szablon = '';
        
        // dodatkowe sprawdzanie czy jest plik na serwerze
        if ( $this->nazwa != '' ) {
          
            if ( !file_exists($this->nazwa) ) {
                 exit('<span style="font-family:Arial;font-size:100%;color:#000000">Blad odczytu pliku <b>' . $this->nazwa . '</b> z szablonu graficznego sklepu ...'); 
            }
        
            require($this->nazwa);
        
            $this->tmpl = ob_get_contents();
            ob_end_clean();       

            $Szablon = $this->tmpl;
            
        }
        
        foreach ( $this->dane as $Klucz => $Zamiana ) {
            //
            $Szablon = str_replace('{' . $Klucz . '}', (string)$Zamiana, (string)$Szablon);
            //
        }
        unset($Klucz, $Zamiana);
        
        $NazwaSkryptu = '';
        
        if ( isset($_SERVER['PHP_SELF']) ) {
             //
             $NazwaSkryptu = basename($_SERVER['PHP_SELF']);
             //
        }
        
        // dowolna tresc
        if ( strpos((string)$Szablon, '{TRESC_') > -1 ) {
          
             $IdTresci = array();
          
             $preg = preg_match_all('|{TRESC_([0-9]+)}|', $Szablon, $matches);
            
             foreach ($matches[1] as $dane) {
              
                 $IdTresci[ (int)$dane ] = (int)$dane;
              
             }
             
             if ( count($IdTresci) > 0 ) {
               
                 foreach ( $IdTresci as $IdTresc ) {

                     $TrescTmp = '';

                     $sqls = $GLOBALS['db']->open_query("select ac.id_any_content,
                                                               acd.any_content_name,
                                                               acd.any_content_description
                                                          from any_content ac left join any_content_description acd on ac.id_any_content = acd.id_any_content and acd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                                         where ac.id_any_content = '" . (int)$IdTresc . "' and ac.any_content_status = 1");   
                                              
                     if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) { 
                         //
                         $DowolnaTrescInfo = $sqls->fetch_assoc();
                         //
                         $TrescTmp = $DowolnaTrescInfo['any_content_description'];
                         //
                     }

                     $GLOBALS['db']->close_query($sqls);                     

                     $Szablon = str_replace('KontenerKreator', 'KontenerKreator TrescDowolna', str_replace('{TRESC_' . $IdTresc . '}', $TrescTmp, (string)$Szablon));
                     
                     unset($TrescTmp);
                     
                  }
                  
             }
             
             unset($WygladSzablon, $preg);
  
        }
        
        // moduly kreatora
        if ( strpos((string)$Szablon, '{MODUL_') > -1 ) {
          
             if ( $NazwaSkryptu == 'strona_informacyjna.php' || $NazwaSkryptu == 'artykul.php' || $NazwaSkryptu == 'produkt.php' || $NazwaSkryptu == 'formularz.php' ) {
          
                  $IdModulow = array();
                
                  $WygladSzablon = new Wyglad();
                
                  $preg = preg_match_all('|{MODUL_([0-9]+)}|', $Szablon, $matches);
                  
                  foreach ($matches[1] as $dane) {
                    
                      $IdModulow[ (int)$dane ] = (int)$dane;
                    
                  }
                   
                  if ( count($IdModulow) > 0 ) {
                     
                       foreach ( $IdModulow as $Modul ) {
                          
                           $Pattern = '/{MODUL_' . $Modul . '}/';
                           $ZamianaTxt = '{MODUL_' . $Modul . '}';
                           $PierwszWystapienie = true;
                          
                           $Szablon = preg_replace_callback($Pattern, function($matches) use (&$PierwszWystapienie, $ZamianaTxt) {
                               if ($PierwszWystapienie) {
                                   $PierwszWystapienie = false;
                                   return $ZamianaTxt;
                               }
                               return '';
                           }, $Szablon);

                           $TrescTmp = (string)$WygladSzablon->SrodekSklepu( 'srodek', array(), '', $Modul, false, true );
            
                           $Szablon = str_replace('KontenerKreator', 'KontenerKreator TrescModulKreator', str_replace('{MODUL_' . $Modul . '}', $TrescTmp, (string)$Szablon));
                           
                           unset($TrescTmp);
                           
                        }
                        
                  }
                   
                  unset($IdModulow, $WygladSzablon, $preg);
                  
             } else {

                  // usuwa znacznik
                  $preg = preg_match_all('|{MODUL_([0-9]+)}|', $Szablon, $matches);
                  
                  foreach ($matches[1] as $Znacznik) {
                      //
                      $Szablon = str_replace('{MODUL_' . $Znacznik . '}', '', (string)$Szablon);
                      //
                  }
               
             }
  
        }
        
        unset($NazwaSkryptu);

        // zamienia stale jezykowe
        $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $Szablon, $matches);
        foreach ($matches[1] as $WartoscJezykowa) {
            //
            if ( isset($GLOBALS['tlumacz'][$WartoscJezykowa]) ) {
                 $Szablon = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', (string)nl2br($GLOBALS['tlumacz'][$WartoscJezykowa]), (string)$Szablon);
            }
            //
        }
        
        // zamienia linki SSL
        $preg = preg_match_all('|{__SSL:([0-9a-zA-Z-._]+?)}|', $Szablon, $matches);
        foreach ($matches[1] as $Link) {
            //
            if ( WLACZENIE_SSL == 'tak' ) {
                $Szablon = str_replace('{__SSL:' . $Link . '}', ADRES_URL_SKLEPU_SSL . '/' . $Link, (string)$Szablon);
              } else {
                $Szablon = str_replace('{__SSL:' . $Link . '}', (string)$Link, (string)$Szablon);
            }
        }        
        
        // zmienia tylko adres aktualnej strony     
        $Szablon = str_replace('{__AKTUALNY_LINK}', (string)$_SERVER['REQUEST_URI'], (string)$Szablon);
        
        // zamienia linki w tlumaczeniach
        if ( strpos((string)$Szablon, '{__LINK') > -1 ) {
             //
             $preg = preg_match_all('|{__LINK:([0-9a-zA-Z-._/]+?)}|', $Szablon, $matches);
             foreach ($matches[1] as $WartoscLink) {
                 //
                 $Szablon = str_replace('{__LINK:' . $WartoscLink . '}', '<a href=\'' . $WartoscLink . '\'>', (string)$Szablon);
                 //
             }           
             $Szablon = str_replace('{/__LINK}', '</a>', (string)$Szablon);
             //
        }
 
        // czysci komentarze html, kompresuje etc - tylko dla glownego szablonu
        if ( $glowny == true ) {

            // zabezpiecza komentarze js
            $Szablon = str_replace('"><!--', '"><!js--', (string)$Szablon);
            $Szablon = str_replace('//-->', '/js/-->', (string)$Szablon);
            //
            $Szablon = preg_replace('/<!--(.*)-->/Uis', '', (string)$Szablon);
            //
            
            if ( KOMPRESJA_HTML == 'tak' ) {
                 $Szablon = $this->htmlCompress($Szablon);
            }
            
            // dodaje domene jezeli jest wlaczony ssl
            if ( WLACZENIE_SSL == 'tak' ) {
                 $Szablon = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http|mailto|gg|callto)([^\"'>]+)([\"'>]+)#", '$1' . ADRES_URL_SKLEPU . '/$2$3', (string)$Szablon);
                 // dodatkowy warunek zeby nie usuwalo podwojnych slashy np. z src="//
                 if ( preg_match('/href="https?:\/\//', $Szablon) || preg_match('/href="http?:\/\//', $Szablon) ) {
                    $Szablon = preg_replace('/([^(:\')])(\/{2,})/', '$1//', (string)$Szablon);
                 }
                 //
                 $Szablon = str_replace(ADRES_URL_SKLEPU . '/javascript:void(0)', 'javascript:void(0)', (string)$Szablon);
                 $Szablon = str_replace(ADRES_URL_SKLEPU . '/tel:', 'tel:', (string)$Szablon);
                 $Szablon = str_replace(ADRES_URL_SKLEPU . '/sms:', 'sms:', (string)$Szablon);
                 $Szablon = str_replace('//images' , '/images', (string)$Szablon);
                 $Szablon = str_replace(ADRES_URL_SKLEPU . '//', ADRES_URL_SKLEPU . '/', (string)$Szablon);
            }
            
            // odwraca zabezpieczenie js
            $Szablon = str_replace('<!js--', '<!--', (string)$Szablon);
            $Szablon = str_replace('/js/-->', '//-->', (string)$Szablon);  
            
            // podmiana meta index/noindex
            $TablicaNoIndex = array('koszyk',
                                    'schowek',
                                    'nowosci',
                                    'promocje',
                                    'hity',
                                    'oczekiwane',
                                    'bestsellery',
                                    'produkty',
                                    'polecane',
                                    'wyszukiwanie_zaawansowane',
                                    'szukaj',
                                    'rejestracja',
                                    'napisz_recenzje',
                                    'zamowienie_logowanie',
                                    'zamowienie_potwierdzenie',                                    
                                    'logowanie',
                                    'aktywacja_konta');
            
            $ex = pathinfo($_SERVER['PHP_SELF']);
            if ( in_array(basename($_SERVER['PHP_SELF'],'.' . $ex['extension']), $TablicaNoIndex) ) {
                 //
                 $Szablon = str_replace('content="index,follow"', 'content="noindex,follow"', (string)$Szablon);
                 //
            }      
            unset($TablicaNoIndex, $ex);

        }
        
        // no index dla stron informacyjnych
        if ( $noindex == true ) {
             //
             $Szablon = str_replace('content="index,follow"', 'content="noindex,follow"', (string)$Szablon);
             //
        }
        
        // integracja z edrone
        IntegracjeZewnetrzne::EdroneSzablony();
        
        return $Szablon;

    }
    
    function htmlCompress($html) {
        //
        preg_match_all('!(<(?:code|pre|script).*>[^<]+</(?:code|pre|script)>)!',$html,$pre);
        $html = preg_replace('!<(?:code|pre).*>[^<]+</(?:code|pre)>!', '#pre#', (string)$html);
        $html = preg_replace('#<!–[^\[].+–>#', '', (string)$html);
        $html = preg_replace('/[\r\n\t]+/', ' ', (string)$html);
        $html = preg_replace('/>[\s]+</', '><', (string)$html);
        $html = preg_replace('/[\s]+/', ' ', (string)$html);
        if (!empty($pre[0])) {
            foreach ($pre[0] as $tag) {
                $html = preg_replace('!#pre#!', (string)$tag, (string)$html,1);
            }
        }
        
        $html = str_replace('<script type="text/javascript">', '<script type="text/javascript">' . "\r\n", (string)$html);
        $html = str_replace('//<![CDATA[', '//<![CDATA[' . "\r\n", (string)$html);
        $html = str_replace('//]]>', '//]]>' . "\r\n", (string)$html);
        $html = str_replace('</script>', '</script>' . "\r\n", (string)$html);
        return $html;
       //
    }    
    
    function cssCompress($bufor, $kompresja = false) {
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
       $bufor = str_replace( array("\r\n", "\r", "\n", "\t"), '', (string)$bufor );
       
       if ( $kompresja == true ) {
         
           /* usuwa tabulatory, spacje, entery etc. */
           $bufor = str_replace( array('  ', '    ', '    '), '', (string)$bufor );
          
           // wzorce reg:
           // 1 => minimalizuj wartoœci HEX kolorów
           // 2 => wywal wszystkie nawiasy z adresów url
           // 3 => skróæ wartoœci regu³y 'font-weight' do wartoœci liczbowych
           $szukaj = array(
               1 => '/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i',
               2 => '/url\([\'"](.*?)[\'"]\)/s',
           );

           $zamien = array(
               1 => '$1#$2$3$4$5',
               2 => 'url($1)',
            );
           $bufor = preg_replace($szukaj, $zamien, (string)$bufor);
           
           $bufor = str_ireplace( array_keys($znaki), array_values($znaki), $bufor );    
          
       }
                      
       return $bufor;      
      
    }

}
?>