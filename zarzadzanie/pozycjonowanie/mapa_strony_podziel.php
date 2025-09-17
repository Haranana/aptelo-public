<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

$IloscProduktow = 20000;

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['podziel']) && $_POST['podziel'] == 'tak' && Sesje::TokenSpr()) {

    $LicznikPozycji = 0;

    $z = new XMLReader;
    $z->open($filtr->process($_POST['plik']));

    $doc = new DOMDocument;

    while ($z->read() && $z->name !== 'url');

    while ($z->name === 'url') {
      
        $node = simplexml_import_dom($doc->importNode($z->expand(), true));

        $LicznikPozycji++;
      
        $z->next('url');
      
    }

    $z->close(); 

    if ( $LicznikPozycji > $IloscProduktow ) {
         //
         $Postep = 0;
         $OgolnyLicznik = 0;
         $NrPliku = 1;

         $z = new XMLReader;
         $z->open($filtr->process($_POST['plik']));

         $doc = new DOMDocument;     
         
         while ($z->read() && $z->name !== 'url');

         while ($z->name === 'url') {
              
              $CoDoZapisania = '';
              
              // uchwyt pliku, otwarcie do dopisania
              if ($Postep == 0) {
                  //
                  $fp = fopen(str_replace('.xml', '_' . $NrPliku . '.xml', (string)$filtr->process($_POST['plik'])), "w");
                  //
                  $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n" .
                                  ' <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\r\n";                  
                  //
                } else {
                  //
                  $fp = fopen(str_replace('.xml', '_' . $NrPliku . '.xml', (string)$filtr->process($_POST['plik'])), "a");
                  //
              }
              
              // blokada pliku do zapisu
              flock($fp, 2);     
         
              $node = simplexml_import_dom($doc->importNode($z->expand(), true));
              
              $CoDoZapisania .= '  <url>' . "\r\n";
              $CoDoZapisania .= '    <loc>' . $node->loc . '</loc>' . "\r\n";
              
              if ( isset($node->priority) ) {
                   //
                   $CoDoZapisania .= '    <priority>' . $node->priority . '</priority>' . "\r\n";
                   //
              }
              
              if ( isset($node->changefreq) ) {
                   //
                   $CoDoZapisania .= '    <changefreq>' . $node->changefreq . '</changefreq>' . "\r\n";
                   //
              }
              
              if ( isset($node->lastmod) ) {
                   //
                   $CoDoZapisania .= '    <lastmod>' . $node->lastmod . '</lastmod>' . "\r\n";
                   //
              }
              
              if ( isset($node->image) ) {
                   //
                   $CoDoZapisania .= '    <image:image>' . "\r\n";
                   $CoDoZapisania .= '       <image:loc>' . $node->image . '</image:loc>'."\r\n";
                   
                   if ( isset($node->image_title) ) {
                        $CoDoZapisania .= '       <image:title>' . $node->image_title . '</image:title>'."\r\n";
                   }
                   
                   $CoDoZapisania .= '    </image:image>' . "\r\n";                  
                   //
              }
              
              $CoDoZapisania .= '  </url>' . "\r\n";

              $ByloZamkniecie = false;
              
              if ( $Postep == $IloscProduktow - 1 ) {
                
                   $NrPliku++;
                
                   $Postep = 0;
                   
                   $CoDoZapisania .= '</urlset>' . "\r\n";
                   
                   $ByloZamkniecie = true;
                   
              } else {
                
                   $Postep++;
                   
              }
              
              $OgolnyLicznik++;

              if ( $OgolnyLicznik == $LicznikPozycji && $ByloZamkniecie == false ) {
                   //
                   $CoDoZapisania .= '</urlset>' . "\r\n";
                   //
              }
              
              fwrite($fp, $CoDoZapisania);
              
              // zapisanie danych do pliku
              flock($fp, 3);
              // zamkniecie pliku
              fclose($fp);            

              $z->next('url');
            
         } 
         //
         $z->close();
         //
         
         // zapis pliku indeksu
         
         //unlink('../sitemap.xml');
         
         //
         $fpc = fopen($filtr->process($_POST['plik']), "w");
         // blokada pliku do zapisu
         flock($fpc, 2);     
         //
         $CiagTmp = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
         $CiagTmp .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
        
         for ( $x = 1; $x <= ceil($LicznikPozycji / $IloscProduktow); $x++ ) {
        
             $CiagTmp .= '   <sitemap>' . "\r\n";
             $CiagTmp .= '       <loc>' . ADRES_URL_SKLEPU . '/' . str_replace('../','',str_replace('.xml', '_' . $x . '.xml', (string)$filtr->process($_POST['plik']))) . '</loc>' . "\r\n";
             $CiagTmp .= '       <lastmod>' . date('Y-m-d', time()) . 'T' . date('G:i:s', time()) . '+00:00</lastmod>' . "\r\n";
             $CiagTmp .= '   </sitemap>' . "\r\n";             
        
         }
         
         $CiagTmp .= '</sitemapindex>' . "\r\n"; 
         
         fwrite($fpc, $CiagTmp);
        
         // zapisanie danych do pliku
         flock($fpc, 3);
         // zamkniecie pliku
         fclose($fpc);  
         //     
    }

}

?>