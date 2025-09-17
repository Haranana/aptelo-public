<?php
chdir('../'); 

if (isset($_POST['id']) && isset($_POST['cechy'])) {

    $PodzielId = explode('_', (string)$_POST['id']);

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (!Sesje::TokenSpr() && (int)$PodzielId[1] > 0) {
        echo 'false';
        exit;
    }
    
    $JestGrafika = false;
    
    // szuka w bazie obrazka cechy - products_stock
    $zapytanie = "select products_stock_image from products_stock where products_id = '" . (int)$PodzielId[1] . "' and products_stock_attributes = '" . substr(str_replace('x',',', (string)$filtr->process($_POST['cechy'])),1) . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);   

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
    
        $info = $sql->fetch_assoc(); 
        
        if ( isset($info['products_stock_image']) && $info['products_stock_image'] != '' && file_exists(KATALOG_ZDJEC . '/' . $info['products_stock_image']) ) {
            //
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML( Funkcje::pokazObrazek($info['products_stock_image'], '', SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'class="Zdjecie" id="Foto1"', 'maly') );
            $xpath = new DOMXPath($doc);
            $imgs = $xpath->query("//img");
            for ($i=0; $i < $imgs->length; $i++) {
                $img = $imgs->item($i);
                $src = $img->getAttribute("src");
            }
            //
            echo json_encode( array("male" => $src, 
                                    "srednie" => Funkcje::pokazObrazek($info['products_stock_image'], '', SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'class="Zdjecie"', 'sredni'), 
                                    "duze" => ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ? Funkcje::pokazObrazekWatermark($info['products_stock_image']) : KATALOG_ZDJEC . '/' . $info['products_stock_image'] ) ) );
                                    
            $JestGrafika = true;
                                    
        }
        
        unset($info);

    }
    
    $GLOBALS['db']->close_query($sql);   
    unset($zapytanie);
    
    // sprawdzi w products_attributes
    if ( $JestGrafika == false ) {
      
        $JakieCechy = explode('x', (string)$filtr->process($_POST['cechy']));
        
        $TablicaZdjecCech = array();
        
        $zapytanie = "select options_values_id, options_values_image from products_attributes where products_id = '" . (int)$PodzielId[1] . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);   

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
        
             while ($info = $sql->fetch_assoc()) {
               
                  if ( isset($info['options_values_image']) && $info['options_values_image'] != '' && file_exists(KATALOG_ZDJEC . '/' . $info['options_values_image']) ) {
                    
                       $TablicaZdjecCech[ $info['options_values_id'] ] = $info['options_values_image'];
                  
                  }
               
             }
             
        }
        
        $GLOBALS['db']->close_query($sql);   
        unset($zapytanie);
        
        foreach ( $JakieCechy as $TmpCecha ) {
          
            $PodzielTmp = explode('-', (string)$TmpCecha);
            
            if ( count($PodzielTmp) == 2 ) {
          
                $zapytanie = "select options_values_image from products_attributes where products_id = '" . (int)$PodzielId[1] . "' and options_values_id = '" . (int)$PodzielTmp[1] . "'";
                $sql = $GLOBALS['db']->open_query($zapytanie);   

                if ( isset($TablicaZdjecCech[ (int)$PodzielTmp[1] ]) ) {
                
                    $doc = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $doc->loadHTML( Funkcje::pokazObrazek($TablicaZdjecCech[ (int)$PodzielTmp[1] ], '', SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'class="Zdjecie" id="Foto1"', 'maly') );
                    $xpath = new DOMXPath($doc);
                    $imgs = $xpath->query("//img");
                    for ($i=0; $i < $imgs->length; $i++) {
                        $img = $imgs->item($i);
                        $src = $img->getAttribute("src");
                    }
                    //
                    echo json_encode( array("male" => $src, 
                                            "srednie" => Funkcje::pokazObrazek($TablicaZdjecCech[ (int)$PodzielTmp[1] ], '', SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'class="Zdjecie"', 'sredni'), 
                                            "duze" => ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ? Funkcje::pokazObrazekWatermark($TablicaZdjecCech[ (int)$PodzielTmp[1] ]) : KATALOG_ZDJEC . '/' . $TablicaZdjecCech[ (int)$PodzielTmp[1] ] ) ) );
                                            
                    $JestGrafika = true;
                    
                    break;
                                            
                }
                
                $GLOBALS['db']->close_query($sql);   
                unset($zapytanie);
        
            }
            
            unset($PodzielTmp);
            
        }
        
        unset($JakieCechy);
        
    }
        
    if ( $JestGrafika == false ) {
        
        $Produkt = new Produkt( (int)$PodzielId[1] );    
        //
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        //
        $fotoHtml = Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'class="Zdjecie" id="Foto1"', 'maly');
        //
        if ( $fotoHtml == '' ) {
             //
             $fotoHtml = '<img src="' . KATALOG_ZDJEC . '/domyslny.webp" alt="" />';
             //
        }
        //
        $doc->loadHTML( $fotoHtml );
        $xpath = new DOMXPath($doc);
        $imgs = $xpath->query("//img");
        for ($i=0; $i < $imgs->length; $i++) {
            $img = $imgs->item($i);
            $src = $img->getAttribute("src");
        }
        //
        echo json_encode( array("male" => $src, 
                                "srednie" => Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'class="Zdjecie"', 'sredni'), 
                                "duze" => ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ? Funkcje::pokazObrazekWatermark($Produkt->fotoGlowne['plik_zdjecia']) : KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] ) ) );
                                
        unset($Produkt);

    }

}

?>