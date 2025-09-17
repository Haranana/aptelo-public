<?php

class IntegracjeZewnetrzne {
  
    // ========== kod weryfikacyjny Google dla webmasterow
    
    public static function GoogleDlaWebmasterowStart() {

        $wynik = '';
        
        if ( INTEGRACJA_GOOGLE_WERYFIKACJA != '' ) {

             $wynik = '<meta name="google-site-verification" content="' . INTEGRACJA_GOOGLE_WERYFIKACJA . '" />' . "\n";
             
        }
        
        return $wynik;

    }
    
    // ========== modul Google Analytics
    
    /* plik start.php */
    
    // Google Tag Manager - sekcja HEAD
    public static function Google_GTM_HeadStart() {
      
        $wynik = '';

        if ( INTEGRACJA_GOOGLE_GTM_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_GTM_ID != '' ) { //  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje'])

            $wynik .= "<!-- Google Tag Manager -->\n";
            $wynik .= "<script>\n";
            $wynik .= "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','". INTEGRACJA_GOOGLE_GTM_ID ."');\n";
            $wynik .= "</script>\n";
            $wynik .= "<!-- End Google Tag Manager -->\n";

        }

        return $wynik;

    }    
    
    // Google Tag Manager - sekcja BODY
    public static function Google_GTM_NoscriptStart() {
      
        $wynik = '';

        if ( INTEGRACJA_GOOGLE_GTM_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_GTM_ID != '' ) { //  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje'])

            /*
            $wyswietl = false;
            
            if ( $_SESSION['cookie_rozszerzone'] == 'tak' ) {
                 //
                 if ( !isset($_COOKIE['akceptCookie']) ) {
                      $wyswietl = true;
                 }
                 if ( isset($_COOKIE['akceptCookie']) && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                      $wyswietl = true;
                 }
            } else {
                 //
                 if ( !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                      $wyswietl = true;
                 }
                 //
            }
            */
            
            // if ( $wyswietl == true ) {
            
                $wynik .= "<!-- Google Tag Manager (noscript) -->\n";
                $wynik .= "<noscript><iframe src='https://www.googletagmanager.com/ns.html?id=". INTEGRACJA_GOOGLE_GTM_ID ."' height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>\n";
                $wynik .= "<!-- End Google Tag Manager (noscript) -->\n";
                
            // }

        }

        return $wynik;

    }    
    
    public static function GoogleAnalyticsStart() {
      
        $wynik = '';

        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {

            $ex = pathinfo($_SERVER['PHP_SELF']);
            
            if ( !isset($ex['extension']) ) {
                 //
                 $roz = explode('.', (string)$_SERVER['PHP_SELF']);
                 $ex['extension'] = $roz[ count($roz) - 1];
                 //
            }                
            
            if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) != 'zamowienie_podsumowanie' ) {

                if ( INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {

                    $wynik .= "<script>\n";
                    $wynik .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";

                    $wynik .= "ga('create', '" . INTEGRACJA_GOOGLE_ID . "', 'auto');\n";
                    $wynik .= "ga('require', 'displayfeatures');\n";
                    $wynik .= "ga('send', 'pageview');\n";

                    $wynik .= "</script>\n";
                
                }
                
                if ( INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) {

                    $wynik .= "<script>\n";
                    $wynik .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";

                    $wynik .= "ga('create', '" . INTEGRACJA_GOOGLE_ID . "', 'auto');\n";
                    $wynik .= "ga('require', 'ec');\n";
                    $wynik .= "ga('require', 'displayfeatures');\n";
                    $wynik .= "ga('send', 'pageview');\n";

                    $wynik .= "</script>\n";
                
                }        

            }
            
            unset($ex);

        }

        return $wynik;

    }    
    
    public static function GoogleZgoda( $ajax = false ) {
      
        $wynik = '';

        if ( ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' ) || ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' ) || ( INTEGRACJA_GOOGLE_GTM_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_GTM_ID != '' ) ) {
        
            if ( $_SESSION['cookie_rozszerzone'] == 'tak' ) {
              
                  if ( $ajax == false ) {

                      $wynik .= "<script>\n";
                      $wynik .= "window.dataLayer = window.dataLayer || [];\n";
                      $wynik .= "function gtag(){dataLayer.push(arguments);}\n";

                      $wynik .= "gtag('consent', 'default', {\n";
                      $wynik .= "  'ad_storage': 'denied',\n";
                      $wynik .= "  'ad_user_data': 'denied',\n";
                      $wynik .= "  'ad_personalization': 'denied',\n";
                      $wynik .= "  'analytics_storage': 'denied',\n";
                      $wynik .= "  'functionality_storage': 'denied',\n";
                      $wynik .= "  'personalization_storage': 'denied',\n";
                      $wynik .= "  'security_storage': 'denied'\n";
                      $wynik .= "});\n";
                      $wynik .= "</script>\n";
                      
                  }
             
                  if ( !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) || !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) {
               
                      if ( $ajax == false ) {
                        
                           $wynik .= "<script>\n";
                           $wynik .= "    gtag('consent', 'update', {\n"; 
                           
                      }

                      if ( !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) {
                           $wynik .= "      'ad_storage': 'granted',\n"; 
                      }
                      
                      $wynik .= "      'ad_user_data': 'granted',\n"; 
                      $wynik .= "      'ad_personalization': 'granted',\n";
                      $wynik .= "      'functionality_storage': 'granted',\n";        
                      $wynik .= "      'personalization_storage': 'granted',\n";   
                      $wynik .= "      'security_storage': 'granted',\n"; 
                      
                      if ( !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                           $wynik .= "      'analytics_storage': 'granted'\n"; 
                      }

                      if ( $ajax == false ) {
                        
                           $wynik .= "    });\n";  
                           $wynik .= "window.dataLayer.push({ 'event': 'consentUpdate' });\n"; 
                           $wynik .= "</script>\n";
                           
                      }

                  }
               
            }
            
        }

        return $wynik;

    }     

    public static function GoogleAnalytics_4_Start() {
      
        $wynik = '';

        // Google Analytics wersja 4
        
        if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' ) { //  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje'])
          
            $wynik .= "<!-- Globalny tag witryny (gtag.js) - Google Analytics -->\n";
            $wynik .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . INTEGRACJA_GOOGLE4_ID . "\"></script>\n";
            $wynik .= "<script>\n";
            $wynik .= "  window.dataLayer = window.dataLayer || [];\n";
            $wynik .= "  function gtag(){dataLayer.push(arguments);}\n";
            $wynik .= "  gtag('js', new Date());\n";
            $wynik .= "  gtag('config', '" . INTEGRACJA_GOOGLE4_ID . "');\n";

            if ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' ) {
                $wynik .= "  gtag('config', '" . INTEGRACJA_GOOGLE_KONWERSJA . "');\n";
            }

            $wynik .= "</script>\n";               


        }
        
        return $wynik;

    }     
    
    /* plik zamowienie_logowanie.php */
    
    public static function GoogleAnalyticsZamowienieLogowanie() {
      
        $wynik = '';
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {
          
             $wynik = IntegracjeZewnetrzne::GoogleAnalyticsStart();
          
        }          

        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {

            // google analytics
            
            $wynik = "<script>\n";
            $wynik .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
            $wynik .= "ga('create', '" . INTEGRACJA_GOOGLE_ID . "', 'auto');\n";
            $wynik .= "ga('require', 'ec');\n";
            $wynik .= "ga('set', 'currencyCode', '" . $_SESSION['domyslnaWaluta']['kod'] . "');\n";
            $wynik .= "ga('require', 'displayfeatures');\n\n";

            $GoogleProduktyAnalytics = array();
            
            foreach ( $_SESSION['koszyk'] as $produkt ) {

                $SciezkaKategoriiGoogle = Kategorie::SciezkaKategoriiId($produkt['id_kategorii'], 'nazwy', '/');

                // google analytics
                
                $GoogleProduktyAnalytics[] = "ga('ec:addProduct', {" . "\n" .
                                             "  'id': '" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "'," . "\n" .
                                             "  'name': '" . str_replace("'", "", (string)$produkt['nazwa']) . "'," . "\n" .
                                             "  'category': '" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "'," . "\n" .
                                             (($produkt['cena_punkty'] == 0) ? "  'price': " . number_format($produkt['cena_brutto'], 2, '.', '') . ",\n" : "") .
                                             "  'quantity': " . $produkt['ilosc'] . "\n" .
                                             "});\n";

                unset($SciezkaKategoriiGoogle);
                
            }

            // google analytics
            
            $wynik .= implode("\n", (array)$GoogleProduktyAnalytics);
            $wynik .= "ga('ec:setAction', 'checkout', {\n";
            $wynik .= "  'step': 2\n";
            $wynik .= "});\n";
            $wynik .= "ga('send', 'pageview');\n";
            $wynik .= "</script>\n";       

            unset($GoogleProduktyAnalytics);

        }      
        
        // google analytics 4 
        
        $wynik .= IntegracjeZewnetrzne::GoogleAnalytics_4_Start();
                
        return $wynik;

    }      
    
    // ========== integracja z kod Google remarketing dynamiczny
    
    public static function GoogleRemarketingStart() {

        $wynik = '';
        
        if ( INTEGRACJA_GOOGLE4_WLACZONY == 'nie' && INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' ) { //  && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje'])

            $wynik .= "<!-- Globalny tag witryny (gtag.js) - Google Ads && Google Analytics -->\n";
            $wynik .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . INTEGRACJA_GOOGLE_KONWERSJA . "\"></script>\n";
            $wynik .= "<script>\n";
            $wynik .= "  window.dataLayer = window.dataLayer || [];\n";
            $wynik .= "  function gtag(){dataLayer.push(arguments);}\n";
            $wynik .= "  gtag('js', new Date());\n";

            if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' ) {
                $wynik .= "  gtag('config', '" . INTEGRACJA_GOOGLE4_ID . "');\n";
            }
            
            $wynik .= "  gtag('config', '" . INTEGRACJA_GOOGLE_KONWERSJA . "');\n";
            $wynik .= "</script>\n";

        }
        
        return $wynik;

    }     
    
    // integracja z kod Google remarketing dynamiczny ORAZ modul Google Analytics
    
    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function GoogleAnalyticsRemarketingDoKoszykaDodanie( $Produkt, $id, $cechy, $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
             ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) )  {
          
            $CenaKoncowa = $Produkt->info['cena_brutto_bez_formatowania'];
          
            foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                //
                if ( $TablicaZawartosci['id'] == $id ) {
                     //
                     $CenaKoncowa = $TablicaZawartosci['cena_brutto'];
                     //
                }
                //
            }

            // google tag
            
            $DataLayer = array();
            $DataLayer['event'] = 'add_to_cart';            
            
            $GoogleTag = "<script>\n";
            $GoogleTag .= "gtag('event', 'add_to_cart', {\n";
            $GoogleTag .= "  \"value\": " . number_format($CenaKoncowa, 2, '.', '') . ",\n";
            $GoogleTag .= "  \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\",\n";
            $GoogleTag .= "  \"items\": [\n";
            $GoogleTag .= "    {\n";
            
            $DataLayer['ecommerce'] = array('currency' => $_SESSION['domyslnaWaluta']['kod'],
                                            'value' => number_format($CenaKoncowa, 2, '.', ''));
            
            if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
            
                $GoogleTag .= "      \"item_id\": \"" . $Produkt->info['id'] . "\",\n";
                $DataLayer['item'][0]['item_id'] = $Produkt->info['id'];
                
                $DataLayer['item'][0]['index'] = 0;
                
                $GoogleTag .= "      \"item_name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";
                $DataLayer['item'][0]['item_name'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']);
                
                if ( $Produkt->info['nazwa_producenta'] != '' ) {
                     $GoogleTag .= "      \"item_brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
                     $DataLayer['item'][0]['item_brand'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']);
                }
            
            }
            
            $GoogleTag .= "      \"id\": \"" . $Produkt->info['id'] . "\",\n";
            $GoogleTag .= "      \"name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";
            
            if ( $Produkt->info['nazwa_producenta'] != '' ) {
                 $GoogleTag .= "      \"brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
            }
            
            $SciezkaTmp = explode('/', (string)Kategorie::SciezkaKategoriiId((int)$Produkt->info['id_kategorii'], 'nazwy', '/'));
            //
            for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                  //
                  $DataLayer['item'][0]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                  //
            }
            //
            unset($SciezkaTmp);            
            
            $GoogleVariant = array();
            
            if ( count($cechy) > 0 ) {
                 //
                 if ( !isset($GLOBALS['NazwyCech']) ) {
                      Funkcje::TabliceCech(); 
                 }
                 //
                 $CechyTmp = Funkcje::CechyProduktuPoId( $filtr->process($TablicaPost['cechy']) );
                 //
                 foreach ($CechyTmp as $Tmp) {
                     //
                     $GoogleVariant[] = str_replace(array("'",'"'), "", (string)$Tmp['nazwa_cechy']) . ": " . str_replace(array("'",'"'), "", (string)$Tmp['wartosc_cechy']);
                     //
                 }
                 //
                 $GoogleTag .= "      \"variant\": \"" . implode(', ', (array)$GoogleVariant) . "\",\n";
                 $DataLayer['item'][0]['item_variant'] = implode(', ', (array)$GoogleVariant);
                 //
                 unset($CechyTmp);
                 //
                 
            }                
            
            $GoogleTag .= "      \"quantity\": " . (float)$TablicaPost['ilosc'] . ",\n";
            $DataLayer['item'][0]['quantity'] = (float)$TablicaPost['ilosc'];
            
            $GoogleTag .= "      \"price\": " . number_format($CenaKoncowa, 2, '.', '') . ",\n";
            $DataLayer['item'][0]['price'] = number_format($CenaKoncowa, 2, '.', '');
            
            $GoogleTag .= "      \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\"" . ",\n";
            $DataLayer['item'][0]['currency'] = $_SESSION['domyslnaWaluta']['kod'];
            
            $GoogleTag .= "      \"google_business_vertical\": \"retail\"\n"; 
            $GoogleTag .= "    }\n";
            $GoogleTag .= "  ]\n";
            $GoogleTag .= "});\n";
            $GoogleTag .= "</script>\n";
            
            // google analytics

            $GoogleAnalytics = "<script>\n";
            $GoogleAnalytics .= "ga('ec:addProduct', {\n";
            $GoogleAnalytics .= "  'id': '" . $Produkt->info['id'] . "',\n";
            $GoogleAnalytics .= "  'name': '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "',\n";
            
            if ( $Produkt->info['nazwa_producenta'] != '' ) {
                 $GoogleAnalytics .= "  'brand': '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "',\n";
            }
                   
            if ( count($GoogleVariant) > 0 ) {                     
                 $GoogleAnalytics .= "  'variant': '" . implode(', ', (array)$GoogleVariant) . "',\n";
            } else {
                 $GoogleAnalytics .= "  'variant': '',\n";
            }
            
            $GoogleAnalytics .= "  'price': '" . number_format($CenaKoncowa, 2, '.', '') . "',\n";
            $GoogleAnalytics .= "  'quantity': " . (float)$TablicaPost['ilosc'] . "\n";
            $GoogleAnalytics .= "});\n";
            $GoogleAnalytics .= "ga('ec:setAction', 'add');\n"; 
            $GoogleAnalytics .= "ga('send', 'event', 'Koszyk', 'click', 'Dodanie z koszyka');\n"; 
            $GoogleAnalytics .= "</script>\n";                
            
            unset($GoogleVariant, $CenaKoncowa);
            
            if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
                 //
                 echo $GoogleTag;
                 //
            }
            if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
                 //
                 echo $GoogleAnalytics;
                 //
            }                     
            
            unset($GoogleTag, $GoogleAnalytics);
            
            echo IntegracjeZewnetrzne::FormatDataLayer($DataLayer);
        
        }       
      
    }

    public static function FormatDataLayer( $tablica ) {
      
        $ciag = '';
      
        if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && INTEGRACJA_GOOGLE4_DATALAYER == 'tak' ) { // && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) 
      
            $ciag = '<script>';
            $ciag .= 'dataLayer.push({ ecommerce: null });' . "\n";
            $ciag .= 'dataLayer.push({' . "\n";
            $ciag .= 'event: "' . $tablica['event'] . '",'. "\n";
            $ciag .= 'ecommerce: { ' . "\n";
          
            foreach ( $tablica['ecommerce'] as $klucz => $wartosc ) {
                //
                $ciag .= $klucz . ': "' . $wartosc . '",' . "\n";
                //
            }
            
            $ciag .= 'items: [' . "\n";
            
            foreach ( $tablica['item'] as $item ) {
                //
                $ciag .= '{' . "\n";
                //
                foreach ( $item as $klucz => $wartosc ) {              
                    //
                    $ciag .= $klucz . ': "' . $wartosc . '",' . "\n";
                    //
                }
                //
                $ciag .= '},' . "\n";
                //
            }        
            
            $ciag .= ']' . "\n";
          
            $ciag .= '}' . "\n";
            $ciag .= '});' . "\n";
            $ciag .= '</script>';

        }
        
        return $ciag;
        
    }    
    
    /* plik koszyk.php */
    
    public static function GoogleAnalyticsRemarketingKoszyk() {    
    
        $wynik = array( 'analytics' => '', 'konwersja' => '' );
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {
          
             $wynik['analytics'] = IntegracjeZewnetrzne::GoogleAnalyticsStart();
          
        }        

        if ( ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
               ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
               ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) 
                 && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {

            $WartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

            // google analytics
            
            $GoogleAnalyticsKod = "<script>\n";
            $GoogleAnalyticsKod .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
            $GoogleAnalyticsKod .= "ga('create', '".INTEGRACJA_GOOGLE_ID."', 'auto');\n";
            $GoogleAnalyticsKod .= "ga('require', 'ec');\n";
            $GoogleAnalyticsKod .= "ga('set', 'currencyCode', '" . $_SESSION['domyslnaWaluta']['kod'] . "');\n";
            $GoogleAnalyticsKod .= "ga('set', 'ecomm_pagetype', 'cart');\n";
            $GoogleAnalyticsKod .= "ga('require', 'displayfeatures');\n\n";
                    
            // google tag
            
            $DataLayer = array();
            $DataLayer['event'] = 'view_cart';            
            
            $GoogleTagKod = '<script>' . "\n";
            $GoogleTagKod .= "gtag('event', 'view_cart', {\n";
            $GoogleTagKod .= "currency: '" . $_SESSION['domyslnaWaluta']['kod'] . "',\n";
            $GoogleTagKod .= "value: " . number_format($WartoscKoszyka['brutto'], 2, '.', '') . ",\n";
            
            $DataLayer['ecommerce'] = array('currency' => $_SESSION['domyslnaWaluta']['kod'],
                                            'value' => number_format($WartoscKoszyka['brutto'], 2, '.', ''));            

            // koszt wysylki i platnosci

            if ( isset($GLOBALS['KosztWysylki']) && is_numeric($GLOBALS['KosztWysylki']) ) {
                $GoogleTagKod .= "shipping: " . number_format($GLOBALS['KosztWysylki'], 2, '.', '') . ",\n";
            }

            $GoogleTagKod .= "items: [\n";  
            
            $GoogleProduktyTag = array();
            $GoogleProduktyAnalytics = array();
            $GoogleProduktyEcomm = array();
            
            $u = 0;
            
            foreach ( $_SESSION['koszyk'] as $produkt ) {
              
                $GoogleProduktyEcomm[] = Funkcje::SamoIdProduktuBezCech($produkt['id']);

                $SciezkaKategoriiGoogle = Kategorie::SciezkaKategoriiId($produkt['id_kategorii'], 'nazwy', '/');

                // google tag
                
                $GoogleProduktyTagTmp = "{\n";
                
                if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                  
                     $DataLayer['item'][$u]['item_id'] = Funkcje::SamoIdProduktuBezCech($produkt['id']);
                     $DataLayer['item'][$u]['item_name'] = str_replace(array("'",'"'), "", (string)$produkt['nazwa']);
                                       
                     $GoogleProduktyTagTmp .= "\"item_id\": \"" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "\",\n" . 
                                              "\"item_name\": \"" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "\",\n";
                                                          
                     $SciezkaTmp = explode('/', (string)$SciezkaKategoriiGoogle);
                     //
                     for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                           //
                           $GoogleProduktyTagTmp .= "\"item_category" . (($w > 1) ? $w : '') . "\": \"" . $SciezkaTmp[$w - 1] . "\",\n";
                           $DataLayer['item'][$u]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                           //
                     }
                     //
                     unset($SciezkaTmp);
                   
                }
                
                $DataLayer['item'][$u]['index'] = $u;
                $DataLayer['item'][$u]['quantity'] = $produkt['ilosc'];
                $DataLayer['item'][$u]['price'] = number_format($produkt['cena_brutto'], 2, '.', '');
                $DataLayer['item'][$u]['currency'] = $_SESSION['domyslnaWaluta']['kod'];

                if ( strpos($produkt['id'], 'x') > 0 ) {
                  
                     $GoogleVariant = array();
                    
                     if ( !isset($GLOBALS['NazwyCech']) ) {
                           Funkcje::TabliceCech(); 
                     }
                     //
                     $CechyTmp = Funkcje::CechyProduktuPoId( $produkt['id'] );
                     //
                     foreach ($CechyTmp as $Tmp) {
                          //
                          $GoogleVariant[] = str_replace(array("'",'"'), "", (string)$Tmp['nazwa_cechy']) . ": " . str_replace(array("'",'"'), "", (string)$Tmp['wartosc_cechy']);
                          //
                     }
                     //
                     $DataLayer['item'][$u]['item_variant'] = implode(', ', (array)$GoogleVariant);

                     unset($CechyTmp, $GoogleVariant);
                     
                }
              
                $GoogleProduktyTagTmp .= "\"id\": \"" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "\",\n" . 
                                         "\"name\": \"" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "\",\n" . 
                                         (($produkt['cena_punkty'] == 0) ?"\"price\": " . number_format($produkt['cena_brutto'], 2, '.', '') . ",\n" : "") .
                                         "\"quantity\": " . $produkt['ilosc'] . ",\n" .
                                         "\"category\": \"" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "\",\n" .
                                         "\"google_business_vertical\": \"retail\" }\n";
                                
                $GoogleProduktyTag[] = $GoogleProduktyTagTmp;
                unset($GoogleProduktyTagTmp);
                
                // google analytics
                
                $CechyProduktu = Funkcje::CechyProduktuPoId( $produkt['id'] );
                $CechyTablica = array();
                
                if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) {
                  
                    if ( count($CechyProduktu) > 0 ) {
                         //
                         if ( !isset($GLOBALS['NazwyCech']) ) {
                              Funkcje::TabliceCech(); 
                         }
                         //
                         foreach ($CechyProduktu as $tmp) {
                             //
                             $CechyTablica[] = str_replace(array("'",'"'), "", (string)$tmp['nazwa_cechy']) . ": " . str_replace(array("'",'"'), "", (string)$tmp['wartosc_cechy']);
                             //
                         }
                         //
                    }     

                }            

                $GoogleProduktyAnalytics[] = "ga('ec:addProduct', {" . "\n" .
                                             "  'id': '" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "'," . "\n" .
                                             "  'name': '" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "'," . "\n" .
                                             "  'category': '" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "'," . "\n" .
                                             (($produkt['cena_punkty'] == 0) ? "  'price': '" . number_format($produkt['cena_brutto'], 2, '.', '') . "',\n" : "") .
                                             ((count($CechyTablica) > 0) ? "  'variant': '" . implode(', ', (array)$CechyTablica)  . "'," . "\n" : "") . 
                                             "  'quantity': " . $produkt['ilosc'] . "\n" .
                                             "});\n";

                unset($SciezkaKategoriiGoogle, $CechyProduktu, $CechyTablica);
                
                $u++;
                
            }

            // google tag
            
            $GoogleTagKod .= implode(',', (array)$GoogleProduktyTag) . "]\n";
            $GoogleTagKod .= "});\n";    
            $GoogleTagKod .= "</script>\n";
            
            // google analytics
            
            $GoogleAnalyticsKod .= implode("\n", (array)$GoogleProduktyAnalytics);
            $GoogleAnalyticsKod .= "ga('ec:setAction', 'checkout', {\n";
            $GoogleAnalyticsKod .= "  'step': 1\n";
            $GoogleAnalyticsKod .= "});\n";
            
            $GoogleAnalyticsKod .= "ga('set', 'ecomm_prodid', '" . implode(',', (array)$GoogleProduktyEcomm) . "');\n";
            
            $GoogleAnalyticsKod .= "ga('send', 'pageview');\n";
            $GoogleAnalyticsKod .= "</script>\n";       
            
            if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
                 //
                 $wynik['analytics'] = $GoogleAnalyticsKod;
                 //
            }
            //
            if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
                 //
                 $wynik['konwersja'] = $GoogleTagKod;
                 //
            }

            unset($GoogleProduktyTag, $GoogleProduktyAnalytics, $GoogleProduktyEcomm, $GoogleTagKod, $GoogleAnalyticsKod);

        }  
        
        // google analytics 4 
        
        $wynik['analytics'] .= IntegracjeZewnetrzne::GoogleAnalytics_4_Start() . ((isset($DataLayer)) ? IntegracjeZewnetrzne::FormatDataLayer($DataLayer) : '');
        
        return $wynik;
    
    }
    
    /* plik listing_dol.php */
    
    public static function GoogleAnalyticsRemarketingListingDol( $WyswietlaneProdukty, $WywolanyPlik = '' ) {
        
        $wynik = array( 'analytics' => '', 'konwersja' => '' );
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {
          
             $wynik['analytics'] = IntegracjeZewnetrzne::GoogleAnalyticsStart();
          
        }

        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
             ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
            //
            if ( count($WyswietlaneProdukty) > 0 ) {
              
                //if ( $WywolanyPlik != 'szukaj' ) {

                    $SciezkaWyswietlanejKategorii = '';
                    $IdWyswietlanejKategorii = '';
                    
                    if ( isset($_GET['idkat']) ) {
                         //
                         $TabCPath = Kategorie::WyczyscPath($_GET['idkat']);
                         $IdWyswietlanejKategorii = $TabCPath[ count($TabCPath) - 1 ];         
                         //

                         if ( isset($GLOBALS['tablicaKategorii'][$IdWyswietlanejKategorii]) ) {
                              //
                              $SciezkaWyswietlanejKategorii = Kategorie::SciezkaKategoriiId($IdWyswietlanejKategorii, 'nazwy', '/');
                              //
                         }
                         
                    }
                    
                    // google analytics
                    
                    $GoogleAnalyticsKod = "<script>\n";
                    $GoogleAnalyticsKod .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
                    $GoogleAnalyticsKod .= "ga('create', '".INTEGRACJA_GOOGLE_ID."', 'auto');\n";
                    $GoogleAnalyticsKod .= "ga('require', 'ec');\n";
                    $GoogleAnalyticsKod .= "ga('set', 'currencyCode', '" . $_SESSION['domyslnaWaluta']['kod'] . "');\n";
                    
                    $GoogleAnalyticsKod .= "ga('set', 'ecomm_pagetype', 'category');\n";
                    
                    $GoogleAnalyticsKod .= "ga('require', 'displayfeatures');\n";
                    
                    // google tag
                    
                    $DataLayer = array();
                    $DataLayer['event'] = 'view_item_list';                    
                 
                    $GoogleTagKod = "<script>\n";
                    $GoogleTagKod .= "gtag('event', 'view_item_list', {\n";
                    $GoogleTagKod .= "   \"items\": [\n";
                       
                    $NazwaListy = $WywolanyPlik;
                    $SciezkaListy = $WywolanyPlik;
                    
                    if ( isset($_GET['idkat']) ) {
                         
                         $NazwaListy = $IdWyswietlanejKategorii;
                         $SciezkaListy = $SciezkaWyswietlanejKategorii;
                         
                    }
                    
                    $DataLayer['ecommerce'] = array('item_list_id' => $NazwaListy,
                                                    'item_list_name' => $SciezkaListy);
                                            
                    //
                    $GoogleProduktyTag = array();
                    $GoogleProduktyAnalytics = array();
                    $GoogleProduktyEcomm = array();
                    
                    $s = 0;
                    
                    foreach ( $WyswietlaneProdukty as $ProduktTmp ) {
                        //
                        $ProduktGoogle = $ProduktTmp; 

                        $GoogleProduktyEcomm[] = $ProduktGoogle->info['id'];
                        
                        // google tag
                        
                        $GoogleProduktyTagTmp = '{' . "\n";
                        
                        if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                          
                            $GoogleProduktyTagTmp .= '"item_id": "' . $ProduktGoogle->info['id'] . '",' . "\n";
                            $DataLayer['item'][$s]['item_id'] = $ProduktGoogle->info['id'];
                            
                            $DataLayer['item'][$s]['index'] = $s;
                            
                            $GoogleProduktyTagTmp .= '"item_name": "' . str_replace(array("'",'"'), "", (string)$ProduktGoogle->info['nazwa']) . '",' . "\n";                          
                            $DataLayer['item'][$s]['item_name'] = str_replace(array("'",'"'), "", (string)$ProduktGoogle->info['nazwa']);
                            
                            if ( $ProduktGoogle->info['nazwa_producenta'] != '' ) {
                                 //
                                 $GoogleProduktyTagTmp .= '"item_brand": "' . $ProduktGoogle->info['nazwa_producenta'] . '",' . "\n";
                                 $DataLayer['item'][$s]['item_brand'] = str_replace(array("'",'"'), "", (string)$ProduktGoogle->info['nazwa_producenta']);
                                 //
                            }  
                            
                            $SciezkaTmp = explode('/', (string)$SciezkaWyswietlanejKategorii);
                            //
                            for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                                  //
                                  $GoogleProduktyTagTmp .= "\"item_category" . (($w > 1) ? $w : '') . "\": \"" . $SciezkaTmp[$w - 1] . "\",\n";
                                  $DataLayer['item'][$s]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                                  //
                            }
                            //
                            unset($SciezkaTmp);                            
                        
                        }
                        
                        $GoogleProduktyTagTmp .= '"id": "' . $ProduktGoogle->info['id'] . '",' . "\n";
                        $GoogleProduktyTagTmp .= '"name": "' . str_replace(array("'",'"'), "", (string)$ProduktGoogle->info['nazwa']) . '",' . "\n";
                                                
                        if ( $ProduktGoogle->info['nazwa_producenta'] != '' ) {
                             //
                             $GoogleProduktyTagTmp .= '"brand": "' . $ProduktGoogle->info['nazwa_producenta'] . '",' . "\n";
                             //
                        }     

                        if ( $SciezkaWyswietlanejKategorii != '' ) {
                             //
                             $GoogleProduktyTagTmp .= '"category": "' . $SciezkaWyswietlanejKategorii . '",' . "\n";
                             //
                        }              

                        if ( $ProduktGoogle->info['tylko_za_punkty'] == 'nie' ) {
                             //
                             $GoogleProduktyTagTmp .= '"price": ' . number_format($ProduktGoogle->info['cena_brutto_bez_formatowania'], 2, '.', '') . ',' . "\n";    
                             $DataLayer['item'][$s]['price'] = number_format($ProduktGoogle->info['cena_brutto_bez_formatowania'], 2, '.', '');
                             //
                        }
                        
                        $GoogleProduktyTagTmp .= '"currency": "' . $_SESSION['domyslnaWaluta']['kod'] . '",' . "\n";
                        $DataLayer['item'][$s]['currency'] = $_SESSION['domyslnaWaluta']['kod'];
                        
                        $GoogleProduktyTagTmp .= '"google_business_vertical": "retail",' . "\n";         
                        $GoogleProduktyTagTmp .= '}';
                        
                        $GoogleProduktyTag[] = $GoogleProduktyTagTmp; 
                        
                        // google analytics
                        
                        $GoogleProduktyAnalyticsTmp = "ga('ec:addImpression', {" . "\n";
                        $GoogleProduktyAnalyticsTmp .= "  'id': '" . $ProduktGoogle->info['id'] . "'," . "\n";
                        $GoogleProduktyAnalyticsTmp .= "  'name': '" . str_replace(array("'",'"'), "", (string)$ProduktGoogle->info['nazwa']) . "'," . "\n";
                        
                        if ( $SciezkaWyswietlanejKategorii != '' ) {
                             //
                             $GoogleProduktyAnalyticsTmp .= "  'category': '" . $SciezkaWyswietlanejKategorii . "'," . "\n";
                             //
                        }
                        
                        $GoogleProduktyAnalyticsTmp .= "  'brand': '" . $ProduktGoogle->info['nazwa_producenta'] . "'," . "\n";
                        
                        if ( $ProduktGoogle->info['tylko_za_punkty'] == 'nie' ) {
                             //
                             $GoogleProduktyAnalyticsTmp .= "  'price': '" . number_format($ProduktGoogle->info['cena_brutto_bez_formatowania'], 2, '.', '') . "'\n";              
                             //
                        }
                        
                        $GoogleProduktyAnalyticsTmp .= "});";
                        
                        $GoogleProduktyAnalytics[] = $GoogleProduktyAnalyticsTmp; 
                        
                        unset($ProduktGoogle);
                        //
                        $s++;
                        //
                    }
                    //
                    $GoogleTagKod .= implode(",\n", (array)$GoogleProduktyTag);
                    $GoogleAnalyticsKod .= implode("\n", (array)$GoogleProduktyAnalytics);
                    //
                    unset($GoogleProduktyTag, $GoogleProduktyAnalytics);
                    //
                    $GoogleTagKod .= "  ]" . "\n";   
                    $GoogleTagKod .= "});" . "\n";   
                    $GoogleTagKod .= "</script>" . "\n";   
                    
                    $GoogleAnalyticsKod .= "ga('set', 'ecomm_prodid', '" . implode(',', (array)$GoogleProduktyEcomm) . "');\n";
                    
                    $GoogleAnalyticsKod .= "ga('send', 'pageview');\n";
                    $GoogleAnalyticsKod .= "</script>\n";               
                    //
                    if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
                         //
                         $wynik['analytics'] = $GoogleAnalyticsKod;
                         //
                    }
                    //
                    if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
                         //
                         $wynik['konwersja'] = $GoogleTagKod;
                         //
                    }                    
                    //
                    unset($TabCPath, $SciezkaWyswietlanejKategorii);
                    //   
                
               // }

            }
            //
        }    
        
        // google analytics 4 

        $wynik['analytics'] .= IntegracjeZewnetrzne::GoogleAnalytics_4_Start() . ((isset($DataLayer)) ? IntegracjeZewnetrzne::FormatDataLayer($DataLayer) : '');   
        
        return $wynik;
        
    }
    
    /* plik produkt.php */
    
    public static function GoogleAnalyticsRemarketingProdukt( $Produkt, $WyswietlanaKategoriaProduktu ) {
      
        $wynik = array( 'analytics' => '', 'konwersja' => '' );
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {
          
             $wynik['analytics'] = IntegracjeZewnetrzne::GoogleAnalyticsStart();
          
        }        

        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
             ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
          
            if ( $WyswietlanaKategoriaProduktu > 0 && isset($GLOBALS['tablicaKategorii'][$WyswietlanaKategoriaProduktu]) ) {
                 //
                 $SciezkaKategoriiGoogle = Kategorie::SciezkaKategoriiId($WyswietlanaKategoriaProduktu, 'nazwy', '/');             
                 //
            } else {
                 //
                 $IdKategoriiProduktuWyswietlanego = $Produkt->ProduktKategoriaGlowna();
                 $SciezkaKategoriiGoogle = Kategorie::SciezkaKategoriiId($IdKategoriiProduktuWyswietlanego['id'], 'nazwy', '/');
                 unset($IdKategoriiProduktuWyswietlanego);
                 //
            }
            
            // google tag
            
            if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {

                $DataLayer = array();
                $DataLayer['event'] = 'view_item';
            
                $GoogleTagKod = "<script>\n";
                $GoogleTagKod .= "gtag('event', 'view_item', {\n";
                $GoogleTagKod .= "  \"value\": " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n";
                $GoogleTagKod .= "  \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\",\n";        
                $GoogleTagKod .= "  \"items\": [\n";
                $GoogleTagKod .= "    {\n";
                
                $DataLayer['ecommerce'] = array('currency' => $_SESSION['domyslnaWaluta']['kod'],
                                                'value' => number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', ''));                
                
                if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                  
                     $GoogleTagKod .= "      \"item_id\": \"" . $Produkt->info['id'] . "\",\n";
                     $DataLayer['item'][0]['item_id'] = $Produkt->info['id'];
                     
                     $DataLayer['item'][0]['index'] = 0;
                     
                     $GoogleTagKod .= "      \"item_name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";
                     $DataLayer['item'][0]['item_name'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']);
                     
                     if ( $Produkt->info['nazwa_producenta'] != '' ) {
                          //
                          $GoogleTagKod .= "      \"item_brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
                          $DataLayer['item'][0]['item_brand'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']);
                          //
                     }   

                     $SciezkaTmp = explode('/', (string)$SciezkaKategoriiGoogle);
                     //
                     for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                           //
                           $GoogleTagKod .= "      \"item_category" . (($w > 1) ? $w : '') . "\": \"" . $SciezkaTmp[$w - 1] . "\",\n";
                           $DataLayer['item'][0]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                           //
                     }
                     //
                     unset($SciezkaTmp);                     
                  
                }
                
                $GoogleTagKod .= "      \"id\": \"" . $Produkt->info['id'] . "\",\n";
                $GoogleTagKod .= "      \"name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";
                
                if ( $Produkt->info['nazwa_producenta'] != '' ) {
                     //
                     $GoogleTagKod .= "      \"brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
                     //
                }
                
                $DataLayer['item'][0]['quantity'] = 1;
                $DataLayer['item'][0]['price'] = number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '');
                $DataLayer['item'][0]['currency'] = $_SESSION['domyslnaWaluta']['kod'];
                
                $GoogleTagKod .= "      \"category\": \"" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "\",\n";
                $GoogleTagKod .= "      \"quantity\": 1,\n";
                $GoogleTagKod .= "      \"price\": " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n";       
                $GoogleTagKod .= "      \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\"" . ",\n";
                $GoogleTagKod .= "      \"google_business_vertical\": \"retail\"\n";  
                $GoogleTagKod .= "    }\n"; 
                $GoogleTagKod .= "  ]\n"; 
                $GoogleTagKod .= "});\n"; 
                $GoogleTagKod .= "</script>\n";
                
                $wynik['konwersja'] = $GoogleTagKod . IntegracjeZewnetrzne::FormatDataLayer($DataLayer);
                
                unset($GoogleTagKod);
                
            }
            
            // google analytics
            
            if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
            
                $GoogleAnalyticsKod = "<script>\n";
                $GoogleAnalyticsKod .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
                $GoogleAnalyticsKod .= "ga('create', '" . INTEGRACJA_GOOGLE_ID . "', 'auto');\n";
                $GoogleAnalyticsKod .= "ga('require', 'ec');\n";
                $GoogleAnalyticsKod .= "ga('set', 'currencyCode', '" . $_SESSION['domyslnaWaluta']['kod'] . "');\n";
                
                $GoogleAnalyticsKod .= "ga('set', 'ecomm_prodid', '" . $Produkt->info['id'] . "');\n";
                $GoogleAnalyticsKod .= "ga('set', 'ecomm_pagetype', 'product');\n";
                $GoogleAnalyticsKod .= "ga('set', 'ecomm_pname', '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "');\n";
                $GoogleAnalyticsKod .= "ga('set', 'ecomm_pcat', '" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "');\n";     
                
                $GoogleAnalyticsKod .= "ga('require', 'displayfeatures');\n";

                $GoogleAnalyticsKod .= "ga('ec:addProduct', {\n";
                $GoogleAnalyticsKod .= "  'id': '" . $Produkt->info['id'] . "',\n";
                $GoogleAnalyticsKod .= "  'name': '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "',\n";
                $GoogleAnalyticsKod .= "  'category': '" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "',\n";
                
                if ( $Produkt->info['nazwa_producenta'] != '' ) {
                     //
                     $GoogleAnalyticsKod .= "  'brand': '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "',\n";
                     //
                }
                
                if ( $Produkt->info['tylko_za_punkty'] == 'nie' ) {
                     //
                     $GoogleAnalyticsKod .= "  'price': '" . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . "'\n";        
                     //
                }
                
                $GoogleAnalyticsKod .= "});\n";

                $GoogleAnalyticsKod .= "ga('ec:setAction', 'detail');\n";

                $GoogleAnalyticsKod .= "ga('send', 'pageview');\n";
                $GoogleAnalyticsKod .= "</script>\n";   

                $wynik['analytics'] = $GoogleAnalyticsKod;
                
                unset($GoogleAnalyticsKod);            
            
            }
            
            unset($SciezkaKategoriiGoogle);
          
        }
        
        // google analytics 4 
        
        $wynik['analytics'] .= IntegracjeZewnetrzne::GoogleAnalytics_4_Start();   
        
        return $wynik;

    }      
    
    /* plik zamowienie_potwierdzenie.php */
    
    public static function GoogleAnalyticsRemarketingZamowieniePotwierdzenie() {
      
        $wynik = array( 'analytics' => '', 'konwersja' => '' );
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {
          
             $wynik['analytics'] = IntegracjeZewnetrzne::GoogleAnalyticsStart();
          
        }              

        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
             ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {

            // google analytics
            
            $GoogleAnalyticsKod = "<script>\n";
            $GoogleAnalyticsKod .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
            $GoogleAnalyticsKod .= "ga('create', '" . INTEGRACJA_GOOGLE_ID . "', 'auto');\n";
            $GoogleAnalyticsKod .= "ga('require', 'ec');\n";
            $GoogleAnalyticsKod .= "ga('set', 'currencyCode', '" . $_SESSION['domyslnaWaluta']['kod'] . "');\n";
            $GoogleAnalyticsKod .= "ga('require', 'displayfeatures');\n\n";
            
            // google tag
            
            $GoogleTagKod = "\n";
            $GoogleTagKod .= '<script>' . "\n";       
            $GoogleTagKod .= "gtag('event', 'checkout_progress', {\n";
            $GoogleTagKod .= "\"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\",\n";
            $GoogleTagKod .= "\"value\": " . number_format($_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc'], 2, '.', '') . ",\n";

            $GoogleTagKod .= "items: [\n";  
                
            $GoogleProduktyTag = array();
            $GoogleProduktyAnalytics = array();

            foreach ( $_SESSION['koszyk'] as $produkt ) {

                $SciezkaKategoriiGoogle = Kategorie::SciezkaKategoriiId($produkt['id_kategorii'], 'nazwy', '/');

                // google tag
                
                $GoogleProduktyTagTmp = "{\n";
                
                if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                                       
                     $GoogleProduktyTagTmp .= "\"item_id\": \"" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "\",\n" . 
                                              "\"item_name\": \"" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "\",\n";
                                              
                     $SciezkaTmp = explode('/', (string)$SciezkaKategoriiGoogle);
                     //
                     for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                           //
                           $GoogleProduktyTagTmp .= "\"item_category" . (($w > 1) ? $w : '') . "\": \"" . $SciezkaTmp[$w - 1] . "\",\n";
                           //
                     }
                     //
                     unset($SciezkaTmp);
                   
                }

                $GoogleProduktyTagTmp .= "\"id\": \"" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "\",\n" . 
                                         "\"name\": \"" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "\",\n" . 
                                         (($produkt['cena_punkty'] == 0) ?"\"price\": " . number_format($produkt['cena_brutto'], 2, '.', '') . ",\n" : "") .
                                         "\"quantity\": " . $produkt['ilosc'] . ",\n" .
                                         "\"category\": \"" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "\",\n" .
                                         "\"google_business_vertical\": \"retail\" }\n";
                                
                $GoogleProduktyTag[] = $GoogleProduktyTagTmp;
                unset($GoogleProduktyTagTmp);

                // google analytics
                
                $CechyProduktu = Funkcje::CechyProduktuPoId( $produkt['id'] );
                $CechyTablica = array();
                
                if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) {
                  
                    if ( count($CechyProduktu) > 0 ) {
                         //
                         if ( !isset($GLOBALS['NazwyCech']) ) {
                              Funkcje::TabliceCech(); 
                         }
                         //
                         foreach ($CechyProduktu as $tmp) {
                             //
                             $CechyTablica[] = str_replace(array("'",'"'), "", (string)$tmp['nazwa_cechy']) . ": " . str_replace(array("'",'"'), "", (string)$tmp['wartosc_cechy']);
                             //
                         }
                         //
                    }     

                }    
                
                $GoogleProduktyAnalytics[] = "ga('ec:addProduct', {" . "\n" .
                                             "  'id': '" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "'," . "\n" .
                                             "  'name': '" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "'," . "\n" .
                                             "  'category': '" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "'," . "\n" .
                                             (($produkt['cena_punkty'] == 0) ? "  'price': '" . number_format($produkt['cena_brutto'], 2, '.', '') . "',\n" : "") .
                                             ((count($CechyTablica) > 0) ? "  'variant': '" . implode(', ', (array)$CechyTablica)  . "'," . "\n" : "") . 
                                             "  'quantity': " . $produkt['ilosc'] . "\n" .
                                             "});\n";                                 

                unset($SciezkaKategoriiGoogle, $CechyProduktu, $CechyTablica);
                
            }
             
            // google tag     
            
            $GoogleTagKod .= implode(',', (array)$GoogleProduktyTag) . "]\n";
            $GoogleTagKod .= "});\n";        
            $GoogleTagKod .= "</script>\n";
            
            // google analytics
            
            $GoogleAnalyticsKod .= implode("\n", (array)$GoogleProduktyAnalytics);
            $GoogleAnalyticsKod .= "ga('ec:setAction', 'checkout', {\n";
            $GoogleAnalyticsKod .= "  'step': 3\n";
            $GoogleAnalyticsKod .= "});\n";
            $GoogleAnalyticsKod .= "ga('send', 'pageview');\n";
            $GoogleAnalyticsKod .= "</script>\n";     
                
            if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
                 //
                 $wynik['analytics'] = $GoogleAnalyticsKod;
                 //
            }
            //
            if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
                 //
                 $wynik['konwersja'] = $GoogleTagKod;
                 //
            }

            unset($GoogleProduktyTag, $GoogleProduktyAnalytics, $GoogleTagKod, $GoogleAnalyticsKod);

        }

        // google analytics 4 
        
        $wynik['analytics'] .= IntegracjeZewnetrzne::GoogleAnalytics_4_Start();   
        
        return $wynik;

    }

    /* plik zamowienie_podsumowanie.php */
    
    public static function GoogleAnalyticsRemarketingZamowieniePodsumowanie( $zamowienie ) {    
    
        $wynik = array( 'analytics' => '', 'konwersja' => '' );
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {
          
             $wynik['analytics'] = IntegracjeZewnetrzne::GoogleAnalyticsStart();
          
        }              

        // TYLKO dla enhanced ecommerce
        
        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
             ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
          
            $wartosc_zamowienia = 0;
            $wartosc_wysylki = 0;
            $kupon_kod = '';
            
            foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
                //
                if ($podsumowanie['klasa'] == 'ot_total') {
                    $wartosc_zamowienia = $podsumowanie['wartosc'];
                } elseif ($podsumowanie['klasa'] == 'ot_shipping' || $podsumowanie['klasa'] == 'ot_payment') {
                    $wartosc_wysylki += $podsumowanie['wartosc'];
                } elseif ($podsumowanie['klasa'] == 'ot_discount_coupon' ) {
                    $kupon_kod = $podsumowanie['tytul'];
                }
                //
            }  

            // google analytics
            
            $GoogleAnalyticsKod = "<script>\n";
            $GoogleAnalyticsKod .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
            $GoogleAnalyticsKod .= "ga('create', '".INTEGRACJA_GOOGLE_ID."', 'auto');\n";
            $GoogleAnalyticsKod .= "ga('require', 'ec');\n";
            $GoogleAnalyticsKod .= "ga('set', 'currencyCode', '" . $_SESSION['domyslnaWaluta']['kod'] . "');\n";
            
            $GoogleAnalyticsKod .= "ga('set', 'ecomm_pagetype', 'purchase');\n";
            
            $GoogleAnalyticsKod .= "ga('require', 'displayfeatures');\n\n";       
            
            // google tag
            
            $DataLayer = array();
            $DataLayer['event'] = 'purchase';            
            
            $GoogleTagKod = "\n";
            $GoogleTagKod .= '<script>' . "\n";
            $GoogleTagKod .= "gtag('event', 'purchase', {\n";
            $GoogleTagKod .= "transaction_id: '" . $_SESSION['zamowienie_id'] . "',\n";
            $GoogleTagKod .= "email: '" . $zamowienie->klient['adres_email'] . "',\n";
            $GoogleTagKod .= "currency: '" . $zamowienie->info['waluta'] . "',\n";
            $GoogleTagKod .= "value: " . $wartosc_zamowienia . ",\n";
            $GoogleTagKod .= "shipping: " . $wartosc_wysylki . ",\n";
            
            $DataLayer['ecommerce'] = array('transaction_id' => $_SESSION['zamowienie_id'],
                                            'email' => $zamowienie->klient['adres_email'],
                                            'value' => $wartosc_zamowienia,
                                            'shipping' => $wartosc_wysylki,
                                            'currency' => $zamowienie->info['waluta']); 

            if ( $kupon_kod != '' ) {      
                 $GoogleTagKod .= "coupon: '" . str_replace(array("'",'"'), "", (string)$kupon_kod) . "',\n";
                 $DataLayer['ecommerce']['coupon'] = str_replace(array("'",'"'), "", (string)$kupon_kod);
            }             
            
            $GoogleTagKod .= "items: [\n";  
            
            $GoogleProduktyTag = array();
            $GoogleProduktyAnalytics = array();
            $GoogleProduktyEcomm = array();
            
            $u = 0;
            
            foreach ( $zamowienie->produkty as $produkt ) {

                $SciezkaKategoriiGoogle = Kategorie::SciezkaKategoriiId($produkt['id_kategorii'], 'nazwy', '/');
                
                $GoogleProduktyEcomm[] = $produkt['id_produktu'];

                // google tag
                
                $GoogleProduktyTagTmp = "{\n";
                
                if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {
                  
                     $DataLayer['item'][$u]['item_id'] = $produkt['id_produktu'];
                     $DataLayer['item'][$u]['index'] = $u;
                     $DataLayer['item'][$u]['item_name'] =  str_replace(array("'",'"'), "", (string)$produkt['nazwa']);
                                       
                     $GoogleProduktyTagTmp .= "\"item_id\": \"" . $produkt['id_produktu'] . "\",\n" . 
                                              "\"item_name\": \"" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "\",\n";
                                              
                     $SciezkaTmp = explode('/', (string)$SciezkaKategoriiGoogle);
                     //
                     for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                           //
                           $GoogleProduktyTagTmp .= "\"item_category" . (($w > 1) ? $w : '') . "\": \"" . $SciezkaTmp[$w - 1] . "\",\n";
                           $DataLayer['item'][$u]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                           //
                     }
                     //
                     unset($SciezkaTmp);
                     
                     $GoogleProduktyTagTmp .= "\"item_brand\": \"" . str_replace(array("'",'"'), "", (string)$produkt['producent']) . "\",\n";
                     $DataLayer['item'][$u]['item_brand'] = str_replace(array("'",'"'), "", (string)$produkt['producent']);
                   
                }
                  
                $GoogleProduktyTagTmp .= "\"id\": \"" . $produkt['id_produktu'] . "\",\n" . 
                                         "\"name\": \"" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "\",\n" . 
                                         (($produkt['cena_punkty'] == 0) ?"\"price\": " . number_format($produkt['cena_koncowa_brutto'], 2, '.', '') . ",\n" : "") .
                                         "\"quantity\": " . $produkt['ilosc'] . ",\n" .
                                         "\"category\": \"" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "\",\n" .
                                         "\"brand\": \"" . str_replace(array("'",'"'), "", (string)$produkt['producent']) . "\",\n" .
                                         "\"google_business_vertical\": \"retail\" }\n";
                                
                $GoogleProduktyTag[] = $GoogleProduktyTagTmp;
                unset($GoogleProduktyTagTmp);
                
                $DataLayer['item'][$u]['quantity'] = $produkt['ilosc'];
                $DataLayer['item'][$u]['price'] = number_format($produkt['cena_koncowa_brutto'], 2, '.', '');
                $DataLayer['item'][$u]['currency'] = $zamowienie->info['waluta'];
                
                // google analytics
                
                $CechyTablica = array();

                if ( isset($produkt['attributes']) && count($produkt['attributes']) > 0 ) {
                     //
                     foreach ($produkt['attributes'] as $cecha ) {
                          //
                          $CechyTablica[] = str_replace(array("'",'"'), "", (string)$cecha['cecha']) . ': ' . str_replace(array("'",'"'), "", (string)$cecha['wartosc']);
                          //
                     }
                     //
                }     
                
                if ( count($CechyTablica) > 0 ) {
                     
                     $DataLayer['item'][$u]['item_variant'] = implode(', ', (array)$CechyTablica);
                     
                }
                
                $GoogleProduktyAnalytics[] = "ga('ec:addProduct', {" . "\n" .
                                             "  'id': '" . $produkt['id_produktu'] . "'," . "\n" .
                                             "  'name': '" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "'," . "\n" .
                                             "  'category': '" . str_replace(array("'",'"'), "", (string)$SciezkaKategoriiGoogle) . "'," . "\n" .
                                             (($produkt['cena_punkty'] == 0) ? "  'price': '" . number_format($produkt['cena_koncowa_brutto'], 2, '.', '') . "',\n" : "") .
                                             ((count($CechyTablica) > 0) ? "  'variant': '" . implode(', ', (array)$CechyTablica) . "'," . "\n" : "") . 
                                             "  'quantity': " . $produkt['ilosc'] . "\n" .
                                             "});\n";                                      

                unset($SciezkaKategoriiGoogle, $CechyTablica);
                
                $u++;
                
            }

            // google tag  
            
            $GoogleTagKod .= implode(',', (array)$GoogleProduktyTag) . "]\n";
            $GoogleTagKod .= "});\n";
            $GoogleTagKod .= "</script>\n";
            
            // google analytics
            
            $GoogleAnalyticsKod .= implode("\n", (array)$GoogleProduktyAnalytics);
            $GoogleAnalyticsKod .= "ga('ec:setAction', 'purchase', {\n";
            $GoogleAnalyticsKod .= "  'id': '" . $_SESSION['zamowienie_id'] . "'," . "\n";
            $GoogleAnalyticsKod .= "  'affiliation': '" . str_replace(array("'",'"'), "", (string)DANE_NAZWA_FIRMY_PELNA) . "'," . "\n";
            
            if ( $kupon_kod != '' ) {      
                 $GoogleAnalyticsKod .= "  'coupon': '" . str_replace(array("'",'"'), "", (string)$kupon_kod) . "'," . "\n";
            }    
            
            $GoogleAnalyticsKod .= "  'revenue': '" . number_format($wartosc_zamowienia, 2, '.', '') . "'," . "\n";
            $GoogleAnalyticsKod .= "  'shipping': '" . number_format($wartosc_wysylki, 2, '.', '') . "'" . "\n";
            $GoogleAnalyticsKod .= "});\n";
            
            $GoogleAnalyticsKod .= "ga('set', 'ecomm_prodid', '" . implode(',', (array)$GoogleProduktyEcomm) . "');\n";
            
            $GoogleAnalyticsKod .= "ga('send', 'pageview');\n";
            $GoogleAnalyticsKod .= "</script>\n";     
                
            if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
                 //
                 $wynik['analytics'] = $GoogleAnalyticsKod;
                 //
            }
            //
            if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
                 //
                 $wynik['konwersja'] = $GoogleTagKod;
                 //
            }

            unset($GoogleProduktyTag, $GoogleProduktyAnalytics, $GoogleProduktyEcomm, $wartosc_wysylki, $wartosc_zamowienia, $kupon_kod, $GoogleTagKod, $GoogleAnalyticsKod);
            
        }        
        
        // Google Analytcs - INNE NIZ enhanced ecommerce
        
        if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ != 'enhanced ecommerce' ) {

            $uzytkownik = str_replace(array("'",'"'), "", (string)DANE_NAZWA_FIRMY_PELNA);
            $wartosc_zamowienia = 0;
            $wartosc_wysylki = 0;
            $wartosc_vat = "";
            
            $wynikTmp = '';

            foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
                //
                if ($podsumowanie['klasa'] == 'ot_total') {
                    $wartosc_zamowienia = $podsumowanie['wartosc'];
                } elseif ($podsumowanie['klasa'] == 'ot_shipping' || $podsumowanie['klasa'] == 'ot_payment') {
                    $wartosc_wysylki += $podsumowanie['wartosc'];
                }
                //
            }

            if ( INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {

                $wynikTmp .= "<script>\n";
                $wynikTmp .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
                $wynikTmp .= "ga('create', '".INTEGRACJA_GOOGLE_ID."', 'auto');\n";
                $wynikTmp .= "ga('require', 'displayfeatures');\n";
                $wynikTmp .= "ga('send', 'pageview');\n";
                $wynikTmp .= "ga('require', 'ecommerce', 'ecommerce.js');\n";
                $wynikTmp .= "ga('ecommerce:addTransaction', {\n";
                $wynikTmp .= "'id': '".$_SESSION['zamowienie_id']."',\n";
                $wynikTmp .= "'affiliation': '".$uzytkownik."',\n";
                $wynikTmp .= "'revenue': '".number_format($wartosc_zamowienia, 2, '.', '')."',\n";
                $wynikTmp .= "'shipping': '".number_format($wartosc_wysylki, 2, '.', '')."',\n";
                $wynikTmp .= "'currency': '".$_SESSION['domyslnaWaluta']['kod']."'\n";
                $wynikTmp .= "});\n";
                
                foreach ( $zamowienie->produkty as $produkt ) {
                    //
                    $wynikTmp .= "ga('ecommerce:addItem', {\n";
                    $wynikTmp .= "  'id': '".$_SESSION['zamowienie_id']."',\n";
                    $wynikTmp .= "  'name': '".$produkt['nazwa']."',\n";
                    $wynikTmp .= "  'sku': '".$produkt['id_produktu']."',\n";
                    $wynikTmp .= "  'category': '".Produkty::pokazKategorieProduktu($produkt['id_produktu'])."',\n";
                    $wynikTmp .= "  'price': '".number_format($produkt['cena_koncowa_brutto'], 2, '.', '')."',\n";
                    $wynikTmp .= "  'quantity': '".$produkt['ilosc']."'\n";
                    $wynikTmp .= "});\n";
                    //
                }
                
                $wynikTmp .= "ga('ecommerce:send');\n";
                $wynikTmp .= "</script>\n";

            }
            
            $wynik['analytics'] = $wynikTmp;

            unset($wynikTmp, $uzytkownik, $wartosc_zamowienia, $wartosc_wysylki, $wartosc_vat);

        }        
        
        // google analytics 4 
        
        $wynik['analytics'] .= IntegracjeZewnetrzne::GoogleAnalytics_4_Start() . ((isset($DataLayer)) ? IntegracjeZewnetrzne::FormatDataLayer($DataLayer) : '');
        
        return $wynik;

    }         

    // integracja z kod Google remarketing dynamiczny 
    
    /* plik inne/do_schowka.php - dodanie do schowka */
    
    public static function GoogleRemarketingDoSchowkaDodanie( $Produkt ) {

        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
          
            $DataLayer = array();
            $DataLayer['event'] = 'add_to_wishlist';          
          
            echo "<script>\n";
            echo "gtag('event', 'add_to_wishlist', {\n";
            echo "  \"value\": " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n";
            echo "  \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\",\n";
            echo "  \"items\": [\n";
            echo "    {\n";
            
            $DataLayer['ecommerce'] = array('currency' => $_SESSION['domyslnaWaluta']['kod'],
                                            'value' => number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', ''));            
            
            if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {

                 echo "      \"item_id\": \"" . $Produkt->info['id'] . "\",\n";
                 $DataLayer['item'][0]['item_id'] = $Produkt->info['id'];
                 
                 $DataLayer['item'][0]['index'] = 0;
                 
                 echo "      \"item_name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";             
                 $DataLayer['item'][0]['item_name'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']);
             
                 if ( $Produkt->info['nazwa_producenta'] != '' ) {
                      echo "      \"item_brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
                      $DataLayer['item'][0]['item_brand'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']);
                 }             
             
            }
            
            echo "      \"id\": \"" . $Produkt->info['id'] . "\",\n";
            echo "      \"name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";
            
            if ( $Produkt->info['nazwa_producenta'] != '' ) {
                 echo "      \"brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
            }
            
            $SciezkaTmp = explode('/', (string)Kategorie::SciezkaKategoriiId((int)$Produkt->info['id_kategorii'], 'nazwy', '/'));
            //
            for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                  //
                  $DataLayer['item'][0]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                  //
            }
            //
            unset($SciezkaTmp);            
            
            $DataLayer['item'][0]['quantity'] = 1;
            $DataLayer['item'][0]['price'] = number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '');
            $DataLayer['item'][0]['currency'] = $_SESSION['domyslnaWaluta']['kod'];
            
            echo "      \"quantity\": 1,\n";
            echo "      \"price\": " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n";
            echo "      \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\"" . ",\n";
            echo "      \"google_business_vertical\": \"retail\"\n"; 
            echo "    }\n";
            echo "  ]\n";
            echo "});\n";
            echo "</script>\n";
            
            echo IntegracjeZewnetrzne::FormatDataLayer($DataLayer);

        }  

    }   

    // integracja z kod Google remarketing dynamiczny ORAZ modul Google Analytics
    
    /* plik inne/usun_z_koszyka.php */
    
    public static function GoogleAnalyticsRemarketingUsunZKoszyka( $Produkt, $id ) {
    
        if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || 
             ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) ||
             ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
          
            $CenaKoncowa = $Produkt->info['cena_brutto_bez_formatowania'];
            $IloscUsuwana = 1;
          
            foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                //
                if ( $TablicaZawartosci['id'] == $id ) {
                     //
                     $CenaKoncowa = $TablicaZawartosci['cena_brutto'];
                     $IloscUsuwana = $TablicaZawartosci['ilosc'];
                     //
                }
                //
            }

            // google tag
            
            $DataLayer = array();
            $DataLayer['event'] = 'remove_from_cart';            
            
            $GoogleTag = "<script>\n";
            $GoogleTag .= "gtag('event', 'remove_from_cart', {\n";
            $GoogleTag .= "  \"value\": " . number_format($CenaKoncowa, 2, '.', '') . ",\n";
            $GoogleTag .= "  \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\",\n";
            $GoogleTag .= "  \"items\": [\n";
            $GoogleTag .= "    {\n";
            
            $DataLayer['ecommerce'] = array('currency' => $_SESSION['domyslnaWaluta']['kod'],
                                            'value' => number_format($CenaKoncowa, 2, '.', ''));            
            
            if ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) {

                 $GoogleTag .= "      \"item_id\": \"" . $Produkt->info['id'] . "\",\n";
                 $DataLayer['item'][0]['item_id'] = $Produkt->info['id'];
                 
                 $DataLayer['item'][0]['index'] = 0;
                 
                 $GoogleTag .= "      \"item_name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";             
                 $DataLayer['item'][0]['item_name'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']);
             
                 if ( $Produkt->info['nazwa_producenta'] != '' ) {
                      $GoogleTag .= "      \"item_brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
                      $DataLayer['item'][0]['item_brand'] = str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']);
                 }             
             
            }            
            
            $GoogleTag .= "      \"id\": \"" . $Produkt->info['id'] . "\",\n";
            $GoogleTag .= "      \"name\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "\",\n";
            
            if ( $Produkt->info['nazwa_producenta'] != '' ) {
                 $GoogleTag .= "      \"brand\": \"" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "\",\n";
            }
            
            $SciezkaTmp = explode('/', (string)Kategorie::SciezkaKategoriiId((int)$Produkt->info['id_kategorii'], 'nazwy', '/'));
            //
            for ( $w = 1; $w <= count($SciezkaTmp); $w++ ) {
                  //
                  $DataLayer['item'][0]['item_category' . (($w > 1) ? $w : '')] = str_replace(array("'",'"'), "", (string)$SciezkaTmp[$w - 1]);
                  //
            }
            //
            unset($SciezkaTmp);            

            $GoogleTag .= "      \"quantity\": " . $IloscUsuwana . ",\n";
            $DataLayer['item'][0]['quantity'] = $IloscUsuwana;
            
            $GoogleTag .= "      \"price\": " . number_format($CenaKoncowa, 2, '.', '') . ",\n";
            $DataLayer['item'][0]['price'] = number_format($CenaKoncowa, 2, '.', '');
            
            $GoogleTag .= "      \"currency\": \"" . $_SESSION['domyslnaWaluta']['kod'] . "\"" . ",\n";
            $DataLayer['item'][0]['currency'] = $_SESSION['domyslnaWaluta']['kod'];
            
            $GoogleTag .= "      \"google_business_vertical\": \"retail\"\n"; 
            $GoogleTag .= "    }\n";
            $GoogleTag .= "  ]\n";
            $GoogleTag .= "});\n";
            $GoogleTag .= "</script>\n";
            
            // google analytics
            
            $GoogleAnalytics = "<script>\n";
            $GoogleAnalytics .= "ga('ec:addProduct', {\n";
            $GoogleAnalytics .= "  'id': '" . $Produkt->info['id'] . "',\n";
            $GoogleAnalytics .= "  'name': '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "',\n";
            
            if ( $Produkt->info['nazwa_producenta'] != '' ) {
                 $GoogleAnalytics .= "  'brand': '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa_producenta']) . "',\n";
            }        

            $GoogleAnalytics .= "  'price': '" . number_format($CenaKoncowa, 2, '.', '') . "',\n";
            $GoogleAnalytics .= "  'quantity': " . $IloscUsuwana . "\n";
            $GoogleAnalytics .= "});\n";
            $GoogleAnalytics .= "ga('ec:setAction', 'remove');\n";       
            $GoogleAnalytics .= "ga('send', 'event', 'Koszyk', 'click', 'Usunięcie z koszyka');\n"; 
            $GoogleAnalytics .= "</script>\n";              
            
            if ( ( INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_KONWERSJA != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_KONWERSJA', $GLOBALS['wykluczeniaIntegracje']) ) || ( INTEGRACJA_GOOGLE4_WLACZONY == 'tak' && INTEGRACJA_GOOGLE4_ID != ''  && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) ) ) {
                 //
                 echo $GoogleTag;
                 //
            }
            if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' && !in_array('COOKIE_INTEGRACJA_GOOGLE_ANALYTICS', $GLOBALS['wykluczeniaIntegracje']) && INTEGRACJA_GOOGLE_RODZAJ == 'enhanced ecommerce' ) { 
                 //
                 echo $GoogleAnalytics;
                 //
            }                
            
            unset($GoogleTag, $GoogleAnalytics, $CenaKoncowa);
            
            echo IntegracjeZewnetrzne::FormatDataLayer($DataLayer);
        
        } 
        
    }    

    // ========== integracja z Opinie konsumenckie Google
    
    public static function OpinieKonsumenckieGoogleStart() {

        $wynik = '';
        
        if ( INTEGRACJA_GOOGLE_OPINIE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_OPINIE_MERCHANT_ID != '' && INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA == 'tak' ) {
            
             $wynik .= '<script src="https://apis.google.com/js/platform.js?onload=renderBadge" async defer></script>' . "\n";
             
             $wynik .= '<script>' . "\n";
             $wynik .= '  window.renderBadge = function() {' . "\n";
             $wynik .= '    var ratingBadgeContainer = document.createElement("div");' . "\n";
             $wynik .= '      document.body.appendChild(ratingBadgeContainer);' . "\n";
             $wynik .= '      window.gapi.load(\'ratingbadge\', function() {' . "\n";
             $wynik .= '        window.gapi.ratingbadge.render(' . "\n";
             $wynik .= '          ratingBadgeContainer, {' . "\n";
             $wynik .= '            "merchant_id": "' . INTEGRACJA_GOOGLE_OPINIE_MERCHANT_ID . '",' . "\n";
             $wynik .= '            "position": "' . ((INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA_POLOZENIE == 'prawy dolny narożnik') ? 'BOTTOM_RIGHT' : 'BOTTOM_LEFT') . '"' . "\n";
             $wynik .= '          });' . "\n";           
             $wynik .= '     });' . "\n";   
             $wynik .= '  }' . "\n";
             $wynik .= '</script>' . "\n";    

        }
        
        return $wynik;

    }    
    
    // ========== widget Trusted Shops
    
    /* plik start.php */
    
    public static function TrustedShopsStart() {

        $wynik = '';
        
        if ( INTEGRACJA_TRUSTEDSHOPS_WLACZONY == 'tak' && INTEGRACJA_TRUSTEDSHOPS_PARTNERID != '' && !in_array('COOKIE_INTEGRACJA_TRUSTEDSHOPS', $GLOBALS['wykluczeniaIntegracje']) ) {

             $wynik .= "<script type=\"text/javascript\">\n";
             $wynik .= "(function () {\n"; 
             $wynik .= "var _tsid = '" . INTEGRACJA_TRUSTEDSHOPS_PARTNERID . "';\n"; 
             $wynik .= "_tsConfig = {\n"; 
             $wynik .= "'yOffset': '" . INTEGRACJA_TRUSTEDSHOPS_PRZESUNIECIE . "',\n";
             $wynik .= "'variant': '" . INTEGRACJA_TRUSTEDSHOPS_FORMAT . "',\n";
             $wynik .= "'responsive': {'variant':'floating', 'position':'" . ((INTEGRACJA_TRUSTEDSHOPS_MOBILE == 'prawa strona') ? 'right' : 'left') . "', 'yOffset':'" . INTEGRACJA_TRUSTEDSHOPS_PRZESUNIECIE . "'},\n";
             $wynik .= "'customElementId': '',\n";
             $wynik .= "'trustcardDirection': '',\n";
             $wynik .= "'customBadgeWidth': '',\n";
             $wynik .= "'customBadgeHeight': '',\n";
             $wynik .= "'disableResponsive': 'false',\n";
             $wynik .= "'disableTrustbadge': 'false',\n";
             $wynik .= "'trustCardTrigger': 'mouseenter'\n";
             $wynik .= "};\n";
             $wynik .= "var _ts = document.createElement('script');\n";
             $wynik .= "_ts.type = 'text/javascript'; \n";
             $wynik .= "_ts.charset = 'utf-8'; \n";
             $wynik .= "_ts.async = true; \n";
             $wynik .= "_ts.src = 'https://widgets.trustedshops.com/js/' + _tsid + '.js'; \n";
             $wynik .= "var __ts = document.getElementsByTagName('script')[0];\n";
             $wynik .= "__ts.parentNode.insertBefore(_ts, __ts);\n";
             $wynik .= "})();\n";
             $wynik .= "</script>\n";

        }
        
        return $wynik;

    }    
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function TrustedShopsZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                        
        if ( INTEGRACJA_TRUSTEDSHOPS_WLACZONY == 'tak' && INTEGRACJA_TRUSTEDSHOPS_PARTNERID != '' && !in_array('COOKIE_INTEGRACJA_TRUSTEDSHOPS', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $uzytkownik = '';
             $wartosc_zamowienia = 0;
             
             $wynik .= IntegracjeZewnetrzne::TrustedShopsStart();

             foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
               if ($podsumowanie['klasa'] == 'ot_total') {
                   $wartosc_zamowienia = number_format($podsumowanie['wartosc'], 2, '.', '');
               }
             }

             $wynik .= "<div id=\"trustedShopsCheckout\" style=\"display: none;\">\n";
             $wynik .= "<span id=\"tsCheckoutOrderNr\">" . $zamowienie->info['id_zamowienia'] . "</span>\n";
             $wynik .= "<span id=\"tsCheckoutBuyerEmail\">" . $zamowienie->klient['adres_email'] . "</span>\n";
             $wynik .= "<span id=\"tsCheckoutOrderAmount\">" . $wartosc_zamowienia . "</span>\n";
             $wynik .= "<span id=\"tsCheckoutOrderCurrency\">" . $zamowienie->info['waluta'] . "</span>\n";
             $wynik .= "<span id=\"tsCheckoutOrderPaymentType\">" . $zamowienie->info['metoda_platnosci'] . "</span>\n";
             
             if ( Funkcje::czyNiePuste($zamowienie->info['data_wysylki']) ) {
                  $wynik .= "<span id=\"tsCheckoutOrderEstDeliveryDate\">" . $zamowienie->info['data_wysylki'] . "</span>\n";  
             }
 
             foreach ( $zamowienie->produkty as $produkt ) {
               
                 $wynik .= "<span class=\"tsCheckoutProductItem\">\n";
                 $wynik .= "<span class=\"tsCheckoutProductUrl\">" . ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt' ) . "</span>\n";
                 $wynik .= "<span class=\"tsCheckoutProductName\">" . $produkt['nazwa'] . "</span>\n";
                 $wynik .= "<span class=\"tsCheckoutProductSKU\">" . $produkt['id_produktu'] . "</span>\n";
                 $wynik .= "<span class=\"tsCheckoutProductImageUrl\">" . ADRES_URL_SKLEPU . "/". KATALOG_ZDJEC . "/" . $produkt['zdjecie'] . "</span>\n";
                 // $wynik .= "<span class=\"tsCheckoutProductSKU\">" . $produkt['kod_producenta'] . "</span>\n";
                 $wynik .= "<span class=\"tsCheckoutProductGTIN\">" . $produkt['ean'] . "</span>\n";
                 $wynik .= "<span class=\"tsCheckoutProductMPN\">" . $produkt['model'] . "</span>\n";
                 $wynik .= "<span class=\"tsCheckoutProductBrand\">" . $produkt['producent'] . "</span>\n";
                 
                 $wynik .= "</span>\n";
                 
             }
 
             $wynik .= "</div>\n";

        } 

        return $wynik;
        
    }     
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function TrusPilotZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';
        $skus = array();
        $TrustProdukty = array();
                        
        if ( INTEGRACJA_TRUSTPILOT_WLACZONY == 'tak' && INTEGRACJA_TRUSTPILOT_KEY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTPILOT', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             
             $wynik .= "<script>\n";
             $wynik .= "document.addEventListener('DOMContentLoaded', function() {\n";
             $wynik .= "  const trustpilot_invitation = {\n";
             $wynik .= "    recipientEmail: '" . $zamowienie->klient['adres_email'] . "',\n";
             $wynik .= "    recipientName: '" . $zamowienie->klient['nazwa'] . "',\n";
             $wynik .= "    referenceId: '" . $zamowienie->info['id_zamowienia'] . "',\n";
             $wynik .= "    source: 'InvitationScript',\n";
 
             foreach ( $zamowienie->produkty as $produkt ) {

                    $skus[] = "'".$produkt['id_produktu']."'";
                    //
                    $TrustProdukty[] .= "{\n" .
                                        "sku: \"" . $produkt['id_produktu']."\",\n" .
                                        "productUrl: \"" . ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt' )."\",\n" .
                                        "imageUrl: \"" . ADRES_URL_SKLEPU . "/". KATALOG_ZDJEC . "/" . $produkt['zdjecie']."\",\n" .
                                        "name: \"" . $produkt['nazwa']."\" }\n";
                    //
             }
             $wynik .= "    productSkus: [".implode(',', (array)$skus)."],\n";

             $wynik .= "    products: [\n";
             $wynik .= implode(',', (array)$TrustProdukty) . "],\n";

             $wynik .= "  };\n";
             $wynik .= "  tp('createInvitation', trustpilot_invitation);\n";
             $wynik .= "});\n";
             $wynik .= "</script>\n";

        } 
        unset($skus); 

        return $wynik;
        
    }     
    


    // ========== integracja z pixel Facebook
    
    /* plik start.php */
    
    public static function PixelFacebookStart() {

        $wynik = '';
        
        if ( INTEGRACJA_FB_PIXEL_WLACZONY == 'tak' && INTEGRACJA_FB_PIXEL_ID != '' ) {
          
            $ex = pathinfo($_SERVER['PHP_SELF']);
            
            if ( !isset($ex['extension']) ) {
                 //
                 $roz = explode('.', (string)$_SERVER['PHP_SELF']);
                 $ex['extension'] = $roz[ count($roz) - 1];
                 //
            }                

            $wynik .= "<script>\n";
            $wynik .= "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n";
            $wynik .= "n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;\n";
            $wynik .= "n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;\n";
            $wynik .= "t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,\n";
            $wynik .= "document,'script','https://connect.facebook.net/en_US/fbevents.js');\n";
            
            if ( !in_array('COOKIE_INTEGRACJA_FB_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {
                 $wynik .= "fbq('consent', 'grant');\n";
            } else {
                 $wynik .= "fbq('consent', 'revoke');\n";
            }
            
            $wynik .= "fbq('init', '" . INTEGRACJA_FB_PIXEL_ID . "');\n";
            $wynik .= "fbq('track', 'PageView');\n";
            
            // koszyk - rozpoczęcie realizacji transakcji zakupu
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'koszyk' ) {
                 //
                 $wynik .= "fbq('track', \"InitiateCheckout\");\n";
                 //
            }
            
            // produkt
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'produkt' ) {
                 //
                 $wynik .= "fbq('track', \"ViewContent\", {\n";
                 $wynik .= "content_type: 'product'";
                 //
                 if ( isset($_GET['idprod']) && (int)$_GET['idprod'] > 0 ) {
                      $wynik .= ", content_ids: ['" . (int)$_GET['idprod'] . "']\n";
                 }
                 //
                 $wynik .= "});\n";
                 //
            } 
            
            // wyszukiwanie
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'szukaj' || basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'wyszukiwanie_zaawansowane' ) {
                 //
                 $wynik .= "fbq('track', \"Search\");\n";
                 //
            }
            
            $wynik .= "</script>\n";    

            // dodanie do koszyka
            if ( isset($_SESSION['facebook_dodanie_koszyk']) && is_array($_SESSION['facebook_dodanie_koszyk']) ) {
                 //
                 if ( isset($_SESSION['facebook_dodanie_koszyk']['id']) ) {

                      foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                          //
                          if ( $TablicaZawartosci['id'] == $_SESSION['facebook_dodanie_koszyk']['id'] ) {
                               //
                               $wynik .= IntegracjeZewnetrzne::PixelFacebookDoKoszykaDodanieAkcja($_SESSION['facebook_dodanie_koszyk'], $TablicaZawartosci['cena_brutto']);
                               //
                          }
                          //
                      }                    

                 }
                 //
                 unset($_SESSION['facebook_dodanie_koszyk']);
                 //
            }     

            // dodanie do schowka
            if ( isset($_SESSION['facebook_dodanie_schowek']) && is_array($_SESSION['facebook_dodanie_schowek']) ) {
                 //
                 if ( isset($_SESSION['facebook_dodanie_schowek']['id']) ) {

                      $wynik .= IntegracjeZewnetrzne::PixelFacebookDoSchowkaDodanieAkcja($_SESSION['facebook_dodanie_schowek']);

                 }
                 //
                 unset($_SESSION['facebook_dodanie_schowek']);
                 //
            }               
            
            // integracja po API
            if ( INTEGRACJA_FB_PIXEL_TOKEN != '' ) {
              
                $serwer = 'https://graph.facebook.com/v10.0/' . INTEGRACJA_FB_PIXEL_ID . '/events?access_token=' . INTEGRACJA_FB_PIXEL_TOKEN;
                
                $ip = null;
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }                
                
                $data = array();
            
                // widok ogolny
                $data[] = array( "event_name" => "PageView",
                                 "event_time" => time(),
                                 "action_source" => "website",
                                 "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                 "user_data" => array(
                                      "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                      "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                      "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                      "client_ip_address" => $ip,
                                      "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                 ));   
                                 
                // produkt
                if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'produkt' ) {
                     //
                     $data[] = array( "event_name" => "ViewContent",
                                      "event_time" => time(),
                                      "action_source" => "website",
                                      "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                      "user_data" => array(
                                           "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                           "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                           "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                           "client_ip_address" => $ip,
                                           "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                      ),
                                      "custom_data" => array(
                                          "content_type" => "product",
                                          "content_ids" => (int)$_GET['idprod']
                                      ));
                     //
                }        

                // koszyk - rozpoczęcie realizacji transakcji zakupu
                if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'koszyk' ) {
                     //
                     $data[] = array( "event_name" => "InitiateCheckout",
                                      "event_time" => time(),
                                      "action_source" => "website",
                                      "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                      "user_data" => array(
                                           "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                           "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                           "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                           "client_ip_address" => $ip,
                                           "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                      ));
                     //
                }             

                // wyszukiwanie
                if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'szukaj' || basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'wyszukiwanie_zaawansowane' ) {
                     //
                     $data[] = array( "event_name" => "Search",
                                      "event_time" => time(),
                                      "action_source" => "website",
                                      "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                      "user_data" => array(
                                           "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                           "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                           "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                           "client_ip_address" => $ip,
                                           "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                      ));
                     //
                }              

                $tablica = array( 'data' => $data );

                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $serwer);
                $head[] ='Accept: application/json';
                $head[] ='Content-Type: application/json';
                curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($c, CURLOPT_HTTPHEADER, $head);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($tablica));
                $data = curl_exec($c);

                $data_json = json_decode($data);                               

            }                                 
            
            unset($ex);
            
        }
        
        return $wynik;

    }    
    
    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function PixelFacebookDoKoszykaDodanie( $Produkt, $id = '' ) {
      
        if ( INTEGRACJA_FB_PIXEL_WLACZONY == 'tak' && INTEGRACJA_FB_PIXEL_ID != '' ) {
          
             if ( PRODUKT_OKNO_POPUP != 'okno popup' ) {
               
                  $_SESSION['facebook_dodanie_koszyk'] = array('id' => $id,
                                                               'id_produktu' => $Produkt->info['id'],
                                                               'cena' => $Produkt->info['cena_brutto_bez_formatowania'],
                                                               'tylko_za_punkty' => $Produkt->info['tylko_za_punkty']);
                                                               
             } else {
               
                  $CenaBruttoProdukt = 0;
               
                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                      //
                      if ( $TablicaZawartosci['id'] == $id ) {
                           //
                           $CenaBruttoProdukt = $TablicaZawartosci['cena_brutto'];
                           //
                      }
                      //
                  }                   
               
                  echo IntegracjeZewnetrzne::PixelFacebookDoKoszykaDodanieAkcja( array('id' => $id,
                                                                                       'id_produktu' => $Produkt->info['id'],
                                                                                       'cena' => $Produkt->info['cena_brutto_bez_formatowania'],
                                                                                       'tylko_za_punkty' => $Produkt->info['tylko_za_punkty']), $CenaBruttoProdukt );
                                                                                       
                  unset($CenaBruttoProdukt);
               
             }

        }
        
    }      
    
    public static function PixelFacebookDoKoszykaDodanieAkcja( $ProduktTablica, $CenaBruttoProdukt = 0 ) {
      
        $wynik = '';
  
        if ( INTEGRACJA_FB_PIXEL_WLACZONY == 'tak' && INTEGRACJA_FB_PIXEL_ID != '' ) {
          
            // integracja po API
            if ( INTEGRACJA_FB_PIXEL_TOKEN != '' ) {
              
                $serwer = 'https://graph.facebook.com/v10.0/' . INTEGRACJA_FB_PIXEL_ID . '/events?access_token=' . INTEGRACJA_FB_PIXEL_TOKEN;
                
                $ip = null;
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }                
                
                $data = array();

                $data[] = array( "event_name" => "AddToCart",
                                 "event_time" => time(),
                                 "action_source" => "website",
                                 "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                 "user_data" => array(
                                      "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                      "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                      "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                      "client_ip_address" => $ip,
                                      "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                 ),
                                 "custom_data" => array(
                                      "currency" => $_SESSION['domyslnaWaluta']['kod'],
                                      "value" => $CenaBruttoProdukt,
                                      "content_type" => "product",
                                      "content_ids" => $ProduktTablica['id_produktu']
                                 ));             

                $tablica = array( 'data' => $data );

                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $serwer);
                $head[] ='Accept: application/json';
                $head[] ='Content-Type: application/json';
                curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($c, CURLOPT_HTTPHEADER, $head);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($tablica));
                $data = curl_exec($c);

                $data_json = json_decode($data);  

            }              
        
            $wynik .= "<script>";
            if ( $ProduktTablica['tylko_za_punkty'] == 'nie' ) {
                 //
                 $wynik .= "fbq('track', 'AddToCart', { content_ids: ['" . $ProduktTablica['id_produktu'] . "'], content_type: 'product', value: " . $CenaBruttoProdukt . ", currency: '" . $_SESSION['domyslnaWaluta']['kod'] . "'})";
                 //
              } else {
                 //
                 $wynik .= "fbq('track', 'AddToCart');";
                 //
            }
            $wynik .= "</script>";
            
        }  
        
        return $wynik;
 
    }  

    /* plik inne/do_schowka.php - dodanie do schowka */
    
    public static function PixelFacebookDoSchowkaDodanie( $Produkt ) {
      
        if ( INTEGRACJA_FB_PIXEL_WLACZONY == 'tak' && INTEGRACJA_FB_PIXEL_ID != '' ) {
          
             if ( PRODUKT_OKNO_SCHOWEK_POPUP != 'okno popup' ) {
          
                  $_SESSION['facebook_dodanie_schowek'] = array('id' => $Produkt->info['id'] ,
                                                                'cena' => $Produkt->info['cena_brutto_bez_formatowania'],
                                                                'tylko_za_punkty' => $Produkt->info['tylko_za_punkty']);
                                                                
             } else {
               
                  echo IntegracjeZewnetrzne::PixelFacebookDoSchowkaDodanieAkcja( array('id' => $Produkt->info['id'] ,
                                                                                       'cena' => $Produkt->info['cena_brutto_bez_formatowania'],
                                                                                       'tylko_za_punkty' => $Produkt->info['tylko_za_punkty']) );
               
             }                                                             

        }
        
    }      
    
    public static function PixelFacebookDoSchowkaDodanieAkcja( $ProduktTablica ) {
      
        $wynik = '';

        if ( INTEGRACJA_FB_PIXEL_WLACZONY == 'tak' && INTEGRACJA_FB_PIXEL_ID != '' ) {
        
            // integracja po API
            if ( INTEGRACJA_FB_PIXEL_TOKEN != '' ) {
              
                $serwer = 'https://graph.facebook.com/v10.0/' . INTEGRACJA_FB_PIXEL_ID . '/events?access_token=' . INTEGRACJA_FB_PIXEL_TOKEN;
                
                $ip = null;
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }                
                
                $data = array();
            
                $data[] = array( "event_name" => "AddToWishlist",
                                 "event_time" => time(),
                                 "action_source" => "website",
                                 "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                 "user_data" => array(
                                      "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                      "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                      "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                      "client_ip_address" => $ip,
                                      "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                 ),
                                 "custom_data" => array(
                                      "currency" => $_SESSION['domyslnaWaluta']['kod'],
                                      "value" => $ProduktTablica['cena'],
                                      "content_type" => "product",
                                      "content_ids" => $ProduktTablica['id']
                                 ));             

                $tablica = array( 'data' => $data );

                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $serwer);
                $head[] ='Accept: application/json';
                $head[] ='Content-Type: application/json';
                curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($c, CURLOPT_HTTPHEADER, $head);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($tablica));
                $data = curl_exec($c);

                $data_json = json_decode($data);                               

            } 
            
            $wynik .= "<script>";
            if ( $ProduktTablica['tylko_za_punkty'] == 'nie' ) {
                 //
                 $wynik .= "fbq('track', 'AddToWishlist', { content_ids: ['" . $ProduktTablica['id'] . "'], content_type: 'product', value: " . $ProduktTablica['cena'] . ", currency: '" . $_SESSION['domyslnaWaluta']['kod'] . "'})";
                 //
              } else {
                 //
                 $wynik .= "fbq('track', 'AddToWishlist');";
                 //
            }                
            $wynik .= "</script>";            
            
        }
        
        return $wynik;
 
    }   

    /* plik zamowienie_podsumowanie.php */
    
    public static function PixelFacebookZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_FB_PIXEL_WLACZONY == 'tak' && INTEGRACJA_FB_PIXEL_ID != '' ) {
          
            foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
              
                if ($podsumowanie['klasa'] == 'ot_total') {
                    $wartosc_zamowienia = number_format($podsumowanie['wartosc'], 2, '.', '');
                }
                
            }  
            
            $id_produktow = array();
            $id_produktow_api = array();
            
            foreach ( $zamowienie->produkty as $prod ) {
              
                $id_produktow[] = "'" . $prod['id_produktu'] . "'";
                $id_produktow_api[] = $prod['id_produktu'];
            }
            
            $wynik .= "<script>\n";
            $wynik .= "fbq('track', 'Purchase', { content_ids: [" . implode(',', (array)$id_produktow) . "], content_type: 'product', value: " . $wartosc_zamowienia . ", currency: '" . $zamowienie->info['waluta'] . "'})\n";
            $wynik .= "</script>\n";

            // integracja po API
            if ( INTEGRACJA_FB_PIXEL_TOKEN != '' ) {
              
                $Podziel = explode(' ', (string)$zamowienie->klient['nazwa']);
                $ImieZamowienie = '';
                $NazwiskoZamowienie = array();
                //
                for ($x = 0; $x < count($Podziel); $x++) {
                    //
                    if ( $x == 0 ) {
                         $ImieZamowienie = strtolower($Podziel[0]);
                    } else {
                         $NazwiskoZamowienie[] = strtolower($Podziel[$x]);
                    }
                    //
                }

                $Miasto = strtolower($zamowienie->klient['miasto']);
                $KodPocztowy = strtolower(preg_replace("/[^0-9]/", "", (string)$zamowienie->klient['kod_pocztowy']));
                $Kraj = strtolower(Klient::pokazISOPanstwa($zamowienie->klient['kraj'], '1'));
                $KlientID = $zamowienie->klient['id'];

                $serwer = 'https://graph.facebook.com/v10.0/' . INTEGRACJA_FB_PIXEL_ID . '/events?access_token=' . INTEGRACJA_FB_PIXEL_TOKEN;
                
                $ip = null;
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }                
                
                $data = array();
            
                $data[] = array( "event_name" => "Purchase",
                                 "event_time" => time(),
                                 "action_source" => "website",
                                 "event_source_url" => str_replace('//', '/', ADRES_URL_SKLEPU . '/' . $_SERVER['REQUEST_URI']),
                                 "user_data" => array(
                                      "em" => ((isset($_SESSION['customer_email'])) ? hash('sha256', strtolower($_SESSION['customer_email'])) : null),
                                      "fn" => ( $NazwiskoZamowienie != '' ? hash('sha256', $ImieZamowienie) : null),
                                      "ln" => ( count($NazwiskoZamowienie) > 0 ? hash('sha256', implode(' ', (array)$NazwiskoZamowienie)) : null),
                                      "ct" => ( $Miasto != '' ? hash('sha256', $Miasto) : null),
                                      "zp" => ( $KodPocztowy != '' ? hash('sha256', $KodPocztowy) : null),
                                      "country" => ( $Kraj != '' ? hash('sha256', $Kraj) : null),
                                      "external_id" => ( $KlientID != '' ? hash('sha256', $KlientID) : null),
                                      "fbc" => ((isset($_COOKIE['_fbc'])) ? $_COOKIE['_fbc'] : null),
                                      "fbp" => ((isset($_COOKIE['_fbp'])) ? $_COOKIE['_fbp'] : null),
                                      "client_ip_address" => $ip,
                                      "client_user_agent" => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null)
                                 ),
                                 "custom_data" => array(
                                      "currency" => $zamowienie->info['waluta'],
                                      "value" => $wartosc_zamowienia,
                                      "content_type" => "product",
                                      "content_ids" => $id_produktow_api
                                 ));             

                $tablica = array( 'data' => $data );

                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $serwer);
                $head[] ='Accept: application/json';
                $head[] ='Content-Type: application/json';
                curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($c, CURLOPT_HTTPHEADER, $head);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($tablica));
                $data = curl_exec($c);

                $data_json = json_decode($data);                               

            }             
            
            unset($wartosc_zamowienia, $id_produktow, $id_produktow_api);
            
        }  

        return $wynik;
        
    }     
    
    // ========== integracja z CRITEO
    
    /* plik start.php */
    
    public static function CriteoStart( $WywolanyPlik ) {

        $wynik = '';
        
        if ( INTEGRACJA_CRITEO_WLACZONY == 'tak' && INTEGRACJA_CRITEO_ID != '' && !in_array('COOKIE_INTEGRACJA_CRITEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $ex = pathinfo($_SERVER['PHP_SELF']);
          
            if ( !isset($ex['extension']) ) {
                 //
                 $roz = explode('.', (string)$_SERVER['PHP_SELF']);
                 $ex['extension'] = $roz[ count($roz) - 1];
                 //
            }             
          
            if ( $GLOBALS['stronaGlowna'] == true || basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'produkt' || basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'koszyk' ) {

                $Typ = 'd';
                $AdresEmail = '';

                if ( isset($_SESSION['customer_email']) ) {
                    $AdresEmail = $_SESSION['customer_email'];
                }

                $detect = new MobileDetect;
                 
                if ( $detect->isMobile() ) {
                    $Typ = 'm';
                } elseif ( $detect->isTablet() ) {
                    $Typ = 't';
                } else {
                    $Typ = 'd';
                }

                $wynik .= "<script type=\"text/javascript\" src=\"//static.criteo.net/js/ld/ld.js\" async=\"true\"></script>\n";
                $wynik .= "<script type=\"text/javascript\">\n";
                $wynik .= "window.criteo_q = window.criteo_q || [];\n";
                $wynik .= "window.criteo_q.push(\n";
                $wynik .= "{ event: \"setAccount\", account: " . INTEGRACJA_CRITEO_ID . " },\n";
                $wynik .= "{ event: \"setEmail\", email: \"" . $AdresEmail . "\" },\n";
                $wynik .= "{ event: \"setSiteType\", type: \"" . $Typ . "\" },\n";

                // produkt
                if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'produkt' ) {
                     //
                     $wynik .= "{ event: \"viewItem\", item: \"" . (isset($_GET['idprod']) ? $_GET['idprod'] : '') . "\" },\n";
                     //
                }

                // koszyk - rozpoczęcie realizacji transakcji zakupu
                if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'koszyk' ) {
                     //
                     if ( isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) > 0 && $WywolanyPlik == 'koszyk' ) {

                          $wynik .= "{ event: \"viewBasket\", item: [\n";

                          $i = 1;
                          foreach ($_SESSION['koszyk'] as $ProduktKoszyka) {

                              if ( strpos((string)$ProduktKoszyka['id'], 'x') === false ) {
                                  $IdProduktu = $ProduktKoszyka['id'];
                              } else {
                                  $IdProduktu = strstr($ProduktKoszyka['id'], 'x', true);
                              }

                              $wynik .= "{ id: \"" . $IdProduktu . "\", price: " . $ProduktKoszyka['cena_brutto'] . ", quantity: " . $ProduktKoszyka['ilosc'] . " }" . ($i < count($_SESSION['koszyk']) ? ',' : '' ) . "\n";
                              $i++;
                          }

                          $wynik .= "]}\n";

                    }
                    //
                }

                // strona glowna
                if ( $GLOBALS['stronaGlowna'] == true ) {
                     //
                     $wynik .= "{ event: \"viewHome\" }\n";
                     //
                }
                
                $wynik .= ");\n";
                $wynik .= "</script>\n";

                unset($AdresEmail, $Typ, $ex);
            }
        }
        
        return $wynik;

    }    
    
    /* plik listing.php */
    
    public static function CriteoListing( $sql ) {

        $wynik = '';
        
        if ( INTEGRACJA_CRITEO_WLACZONY == 'tak' && INTEGRACJA_CRITEO_ID != '' && !in_array('COOKIE_INTEGRACJA_CRITEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $Typ = 'd';
            $AdresEmail = '';

            if ( isset($_SESSION['customer_email']) ) {
                $AdresEmail = $_SESSION['customer_email'];
            }

            $detect = new MobileDetect;
                
            if ( $detect->isMobile() ) {
                $Typ = 'm';
            } elseif ( $detect->isTablet() ) {
                $Typ = 't';
            } else {
                $Typ = 'd';
            }

            $wynik .= "<script type=\"text/javascript\" src=\"//static.criteo.net/js/ld/ld.js\" async=\"true\"></script>\n";
            $wynik .= "<script type=\"text/javascript\">\n";
            $wynik .= "window.criteo_q = window.criteo_q || [];\n";
            $wynik .= "window.criteo_q.push(\n";
            $wynik .= "{ event: \"setAccount\", account: " . INTEGRACJA_CRITEO_ID . " },\n";
            $wynik .= "{ event: \"setEmail\", email: \"" . $AdresEmail . "\" },\n";
            $wynik .= "{ event: \"setSiteType\", type: \"" . $Typ . "\" },\n";

            $i = 1;
            $produkty_id = '';
            while ( $info = $sql->fetch_assoc() ) {
                    //
                    if ( $i < 4 ) {
                        $produkty_id .= '"' . $info['products_id'] . '",';
                    }
                    //
                    $i++;
                    //
            }
            $wynik .= "{ event: \"viewList\", item: [" . substr((string)$produkty_id, 0, -1) . "]}\n";

            $wynik .= ");\n";
            $wynik .= "</script>\n";

            unset($AdresEmail, $Typ);
        }        
        
        return $wynik;

    }        
    
    /* plik szukaj.php */
    
    public static function CriteoSzukaj( $id_produktow = array() ) {

        $wynik = '';
        
        if ( INTEGRACJA_CRITEO_WLACZONY == 'tak' && INTEGRACJA_CRITEO_ID != '' && !in_array('COOKIE_INTEGRACJA_CRITEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            if ( count($id_produktow) > 0 ) {

                $Typ = 'd';
                $AdresEmail = '';

                if ( isset($_SESSION['customer_email']) ) {
                    $AdresEmail = $_SESSION['customer_email'];
                }

                $detect = new MobileDetect;
                     
                if ( $detect->isMobile() ) {
                    $Typ = 'm';
                } elseif ( $detect->isTablet() ) {
                    $Typ = 't';
                } else {
                    $Typ = 'd';
                }

                $wynik .= "<script type=\"text/javascript\" src=\"//static.criteo.net/js/ld/ld.js\" async=\"true\"></script>\n";
                $wynik .= "<script type=\"text/javascript\">\n";
                $wynik .= "window.criteo_q = window.criteo_q || [];\n";
                $wynik .= "window.criteo_q.push(\n";
                $wynik .= "{ event: \"setAccount\", account: " . INTEGRACJA_CRITEO_ID . " },\n";
                $wynik .= "{ event: \"setEmail\", email: \"" . $AdresEmail . "\" },\n";
                $wynik .= "{ event: \"setSiteType\", type: \"" . $Typ . "\" },\n";

                $i = 1;
                $produkty_id = '';
                foreach ( $id_produktow as $id_produktu ) {
                    if ( $i < 4 ) {
                        $produkty_id .= '"' . $id_produktu . '",';
                    }
                    $i++;
                }
                $wynik .= "{ event: \"viewList\", item: [" . substr((string)$produkty_id, 0, -1) . "]}\n";

                $wynik .= ");\n";
                $wynik .= "</script>\n";

                unset($AdresEmail, $Typ);
            }

        }        
        
        return $wynik;

    }      

    /* plik zamowienie_podsumowanie.php */
    
    public static function CriteoZamowieniePodsumowanie( $zamowienie ) {

        $wynik = '';
        
        if ( INTEGRACJA_CRITEO_WLACZONY == 'tak' && INTEGRACJA_CRITEO_ID != '' && !in_array('COOKIE_INTEGRACJA_CRITEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $Typ = 'd';
            $AdresEmail = '';

            if ( isset($_SESSION['customer_email']) ) {
                $AdresEmail = $_SESSION['customer_email'];
            }

            $detect = new MobileDetect;
                 
            if ( $detect->isMobile() ) {
                $Typ = 'm';
            } elseif ( $detect->isTablet() ) {
                $Typ = 't';
            } else {
                $Typ = 'd';
            }

            $wynik .= "<script type=\"text/javascript\" src=\"//static.criteo.net/js/ld/ld.js\" async=\"true\"></script>\n";
            $wynik .= "<script type=\"text/javascript\">\n";
            $wynik .= "window.criteo_q = window.criteo_q || [];\n";
            $wynik .= "window.criteo_q.push(\n";
            $wynik .= "{ event: \"setAccount\", account: " . INTEGRACJA_CRITEO_ID . " },\n";
            $wynik .= "{ event: \"setEmail\", email: \"" . $AdresEmail . "\" },\n";
            $wynik .= "{ event: \"setSiteType\", type: \"" . $Typ . "\" },\n";

            $wynik .= "{ event: \"trackTransaction\", id: \"" . $_SESSION['zamowienie_id'] . "\", item: [\n";

            $i = 1;

            foreach ( $zamowienie->produkty as $produkt ) {

                $wynik .= "{ id: \"" . $produkt['id_produktu']."\", price: " . $produkt['cena_koncowa_brutto'] . ", quantity: " . $produkt['ilosc'] . " }" . ($i < count($zamowienie->produkty) ? ',' : '' ) . "\n";
                $i++;

            }

            $wynik .= "]}\n";

            $wynik .= ");\n";
            $wynik .= "</script>\n";

            unset($AdresEmail, $Typ);
            
        }        

        return $wynik;

    }       
    
    // ========== integracja z edrone
    
    /* plik start.php */
    
    public static function EdroneStart( $WywolanyPlik ) {

        $wynik = '';
        
        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wynik .= '<script type="text/javascript">' . "\n";
            $wynik .= '   (function (srcjs) {' . "\n";
            $wynik .= '   window._edrone = window._edrone || {};' . "\n";
            $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
            $wynik .= '   var doc = document.createElement(\'script\');' . "\n";
            $wynik .= '   doc.type = \'text/javascript\';' . "\n";
            $wynik .= '   doc.async = true;' . "\n";
            $wynik .= '   doc.src = (\'https:\' == document.location.protocol ? \'https:\' : \'http:\') + srcjs;' . "\n";
            $wynik .= '   var s = document.getElementsByTagName(\'script\')[0];' . "\n";
            $wynik .= '   s.parentNode.insertBefore(doc, s);' . "\n";
            $wynik .= '   })("//d3bo67muzbfgtl.cloudfront.net/edrone_2_0.js");' . "\n";
            $wynik .= '</script>' . "\n"; 

            if ( isset($_SESSION['edrone']) ) {

                 $AkcjaEdrone = explode('-', str_replace("'", "", (string)$_SESSION['edrone']));

                 // dodanie produktu do koszyka
                 
                 if ( $AkcjaEdrone[0] == 'add_to_cart' ) {
                      //
                      $Produkt = new Produkt( $AkcjaEdrone[1] );
                      //
                      if ($Produkt->CzyJestProdukt == true) {
                          //
                          $IdKategoriiProduktuWyswietlanego = $Produkt->ProduktKategoriaGlowna();
         
                          $wynik .= '<script type="text/javascript">' . "\n";
                          $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
                          $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
                          $wynik .= '   _edrone.action_type = \'' . $AkcjaEdrone[0] . '\';' . "\n";
                          $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
                          $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
                          $wynik .= '   _edrone.product_skus = \'' . $Produkt->info['nr_katalogowy'] . '\';' . "\n";
                          $wynik .= '   _edrone.product_ids = \'' . $Produkt->info['id'] . '\';' . "\n";
                          $wynik .= '   _edrone.product_titles = \'' . $Produkt->info['nazwa'] . '\';' . "\n";
                          $wynik .= '   _edrone.product_images = \'' . ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . str_replace("'","\'", $Produkt->fotoGlowne['plik_zdjecia']) . '\';' . "\n";
                          $wynik .= '   _edrone.product_urls = \'' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '\';' . "\n";
                          $wynik .= '   _edrone.product_category_ids = \'' . Kategorie::SciezkaKategoriiId($IdKategoriiProduktuWyswietlanego['id'], 'id', '~') . '\';' . "\n";
                          $wynik .= '   _edrone.product_category_names = \'' . Kategorie::SciezkaKategoriiId($IdKategoriiProduktuWyswietlanego['id'], 'nazwy', '~') . '\';' . "\n";
                          $wynik .= '   _edrone.shop_lang = \'' . $_SESSION['domyslnyJezyk']['nazwa'] . '\';' . "\n";
                          
                          if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                               //
                               $TablicaKlienta = Klient::daneKlienta((int)$_SESSION['customer_id']);
                               //
                               if ( isset($TablicaKlienta['Imie']) && !empty($TablicaKlienta['Imie']) ) {
                                    $wynik .= '   _edrone.first_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Imie']) . '\';' . "\n";
                               }
                               if ( isset($TablicaKlienta['Nazwisko']) && !empty($TablicaKlienta['Nazwisko']) ) {
                                    $wynik .= '   _edrone.last_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Nazwisko']) . '\';' . "\n";
                               }   
                               if ( isset($TablicaKlienta['Email']) && !empty($TablicaKlienta['Email']) ) {
                                    $wynik .= '   _edrone.email = \'' . str_replace("'", "", (string)$TablicaKlienta['Email']) . '\';' . "\n";
                               }     
                               if ( isset($TablicaKlienta['Kraj']) && !empty($TablicaKlienta['Kraj']) ) {
                                    $wynik .= '   _edrone.country = \'' . str_replace("'", "", (string)$TablicaKlienta['Kraj']) . '\';' . "\n";
                               }    
                               if ( isset($TablicaKlienta['Miasto']) && !empty($TablicaKlienta['Miasto']) ) {
                                    $wynik .= '   _edrone.city = \'' . str_replace("'", "", (string)$TablicaKlienta['Miasto']) . '\';' . "\n";
                               }      
                               if ( isset($TablicaKlienta['Telefon']) && !empty($TablicaKlienta['Telefon']) && KLIENT_POKAZ_TELEFON == 'tak' ) {
                                    $wynik .= '   _edrone.phone = \'' . str_replace("'", "", (string)$TablicaKlienta['Telefon']) . '\';' . "\n";
                               }    
                               //
                               unset($TablicaKlienta);
                               //                    
                          }
                          
                          $wynik .= '</script>' . "\n";
                        
                          unset($IdKategoriiProduktuWyswietlanego);         
         
                      }
                      //
                      unset($Produkt);
                      //
                 }
                 
                 // newsletter
                 
                 if ( $AkcjaEdrone[0] == 'subscribe' ) {    
                      //
                      $wynik .= '<script type="text/javascript">' . "\n";
                      $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
                      $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
                      $wynik .= '   _edrone.action_type = \'subscribe\';' . "\n";
                      $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
                      $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
                      $wynik .= '   _edrone.email = \'' . $AkcjaEdrone[2] . '\';' . "\n";
                      $wynik .= '   _edrone.subscriber_status = \'' . $AkcjaEdrone[1] . '\';' . "\n";
                      $wynik .= '   _edrone.shop_lang = \'' . $_SESSION['domyslnyJezyk']['nazwa'] . '\';' . "\n";
                      
                      if ( isset($AkcjaEdrone[3]) ) {
                           $wynik .= '   _edrone.customer_tags = \'' . $AkcjaEdrone[3] . '\';' . "\n";
                      }
                      
                      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                           //
                           $TablicaKlienta = Klient::daneKlienta((int)$_SESSION['customer_id']);
                           //
                           if ( isset($TablicaKlienta['Imie']) && !empty($TablicaKlienta['Imie']) ) {
                                $wynik .= '   _edrone.first_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Imie']) . '\';' . "\n";
                           }
                           if ( isset($TablicaKlienta['Nazwisko']) && !empty($TablicaKlienta['Nazwisko']) ) {
                                $wynik .= '   _edrone.last_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Nazwisko']) . '\';' . "\n";
                           }    
                           if ( isset($TablicaKlienta['Kraj']) && !empty($TablicaKlienta['Kraj']) ) {
                                $wynik .= '   _edrone.country = \'' . str_replace("'", "", (string)$TablicaKlienta['Kraj']) . '\';' . "\n";
                           }    
                           if ( isset($TablicaKlienta['Miasto']) && !empty($TablicaKlienta['Miasto']) ) {
                                $wynik .= '   _edrone.city = \'' . str_replace("'", "", (string)$TablicaKlienta['Miasto']) . '\';' . "\n";
                           }      
                           if ( isset($TablicaKlienta['Telefon']) && !empty($TablicaKlienta['Telefon']) && KLIENT_POKAZ_TELEFON == 'tak' ) {
                                $wynik .= '   _edrone.phone = \'' . str_replace("'", "", (string)$TablicaKlienta['Telefon']) . '\';' . "\n";
                           }  
                           //
                           unset($TablicaKlienta);
                           //                    
                      }              
                      
                      $wynik .= '</script>' . "\n";           
                      //
                 }  

                 // zamowienie bez rejestracji - newsletter - przekazanie maila
                 
                 if ( $AkcjaEdrone[0] == 'newsletter_bez_rejestracji' ) {    
                      //
                      $wynik .= '<script type="text/javascript">' . "\n";
                      $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
                      $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
                      $wynik .= '   _edrone.action_type = \'subscribe\';' . "\n";
                      $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
                      $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
                      $wynik .= '   _edrone.email = \'' . $AkcjaEdrone[2] . '\';' . "\n";
                      
                      if ( (float)$AkcjaEdrone[1] == 1 ) {
                           $wynik .= '   _edrone.subscriber_status = \'' . $AkcjaEdrone[1] . '\';' . "\n";
                      }
                      
                      $wynik .= '   _edrone.shop_lang = \'' . $_SESSION['domyslnyJezyk']['nazwa'] . '\';' . "\n";
                      
                      if ( isset($AkcjaEdrone[3]) ) {
                           $wynik .= '   _edrone.customer_tags = \'' . $AkcjaEdrone[3] . '\';' . "\n";
                      }            
                      
                      $wynik .= '</script>' . "\n";           
                      //
                 }         

                 // zamowienie z rejestracja lub rejestracja - newsletter - przekazanie maila
                 
                 if ( $AkcjaEdrone[0] == 'newsletter_z_rejestracja' ) {    
                      //
                      $wynik .= '<script type="text/javascript">' . "\n";
                      $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
                      $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
                      $wynik .= '   _edrone.action_type = \'subscribe\';' . "\n";
                      $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
                      $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
                      $wynik .= '   _edrone.email = \'' . $AkcjaEdrone[2] . '\';' . "\n";
                      
                      if ( (float)$AkcjaEdrone[1] == 1 ) {
                           $wynik .= '   _edrone.subscriber_status = \'' . $AkcjaEdrone[1] . '\';' . "\n";
                      }
                      
                      $wynik .= '   _edrone.shop_lang = \'' . $_SESSION['domyslnyJezyk']['nazwa'] . '\';' . "\n";
                      
                      if ( isset($AkcjaEdrone[3]) ) {
                           $wynik .= '   _edrone.customer_tags = \'' . $AkcjaEdrone[3] . '\';' . "\n";
                      }
                      
                      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                           //
                           $TablicaKlienta = Klient::daneKlienta((int)$_SESSION['customer_id']);
                           //
                           if ( isset($TablicaKlienta['Imie']) && !empty($TablicaKlienta['Imie']) ) {
                                $wynik .= '   _edrone.first_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Imie']) . '\';' . "\n";
                           }
                           if ( isset($TablicaKlienta['Nazwisko']) && !empty($TablicaKlienta['Nazwisko']) ) {
                                $wynik .= '   _edrone.last_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Nazwisko']) . '\';' . "\n";
                           }   
                           if ( isset($TablicaKlienta['Kraj']) && !empty($TablicaKlienta['Kraj']) ) {
                                $wynik .= '   _edrone.country = \'' . str_replace("'", "", (string)$TablicaKlienta['Kraj']) . '\';' . "\n";
                           }    
                           if ( isset($TablicaKlienta['Miasto']) && !empty($TablicaKlienta['Miasto']) ) {
                                $wynik .= '   _edrone.city = \'' . str_replace("'", "", (string)$TablicaKlienta['Miasto']) . '\';' . "\n";
                           }      
                           if ( isset($TablicaKlienta['Telefon']) && !empty($TablicaKlienta['Telefon']) && KLIENT_POKAZ_TELEFON == 'tak' ) {
                                $wynik .= '   _edrone.phone = \'' . str_replace("'", "", (string)$TablicaKlienta['Telefon']) . '\';' . "\n";
                           } 
                           //
                           unset($TablicaKlienta);
                           //                    
                      }              
                      
                      $wynik .= '</script>' . "\n";           
                      //
                 }      

            } else {
              
                 if ( $WywolanyPlik != 'zamowienie_podsumowanie' ) {

                     $wynik .= '<script type="text/javascript">' . "\n";
                     $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
                     $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
                     $wynik .= '   _edrone.action_type = \'other\';' . "\n";
                     $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
                     $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
                     $wynik .= '   _edrone.shop_lang = \'' . $_SESSION['domyslnyJezyk']['nazwa'] . '\';' . "\n";
                     
                     if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                         
                         $TablicaKlienta = Klient::daneKlienta((int)$_SESSION['customer_id']);
                         //
                         if ( isset($TablicaKlienta['Email']) && !empty($TablicaKlienta['Email']) ) {
                              $wynik .= '   _edrone.email = \'' . str_replace("'", "", (string)$TablicaKlienta['Email']) . '\';' . "\n";
                         }                   
                         if ( isset($TablicaKlienta['Newsletter']) && (int)$TablicaKlienta['Newsletter'] == 1 ) {
                              $wynik .= '   _edrone.subscriber_status = \'' . (int)$TablicaKlienta['Newsletter'] . '\';' . "\n";
                         }                     
                         if ( isset($TablicaKlienta['Imie']) && !empty($TablicaKlienta['Imie']) ) {
                              $wynik .= '   _edrone.first_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Imie']) . '\';' . "\n";
                         }
                         if ( isset($TablicaKlienta['Nazwisko']) && !empty($TablicaKlienta['Nazwisko']) ) {
                              $wynik .= '   _edrone.last_name = \'' . str_replace("'", "", (string)$TablicaKlienta['Nazwisko']) . '\';' . "\n";
                         }   
                         if ( isset($TablicaKlienta['Kraj']) && !empty($TablicaKlienta['Kraj']) ) {
                              $wynik .= '   _edrone.country = \'' . str_replace("'", "", (string)$TablicaKlienta['Kraj']) . '\';' . "\n";
                         }    
                         if ( isset($TablicaKlienta['Miasto']) && !empty($TablicaKlienta['Miasto']) ) {
                              $wynik .= '   _edrone.city = \'' . str_replace("'", "", (string)$TablicaKlienta['Miasto']) . '\';' . "\n";
                         }      
                         if ( isset($TablicaKlienta['Telefon']) && !empty($TablicaKlienta['Telefon']) && KLIENT_POKAZ_TELEFON == 'tak' ) {
                              $wynik .= '   _edrone.phone = \'' . str_replace("'", "", (string)$TablicaKlienta['Telefon']) . '\';' . "\n";
                         }    
                         //
                         unset($TablicaKlienta);
                         //                
                    }        

                    $wynik .= '</script>' . "\n"; 
                    
               }

            }
         
        }
        
        return $wynik;

    }    
    
    /* plik aktywacja_konta.php */
    
    public static function EdroneAktywacjaKonta( $info_klient ) {

        if (INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) && $info_klient['customers_newsletter'] == 1 ) {        
            //
            $_SESSION['edrone'] = 'newsletter_z_rejestracja-1-' . $info_klient['customers_email_address'] . '-Zapis do newslettera, rejestracja konta';  
            //
        }     

    }         
    
    /* plik dane_adresowe.php */
    
    public static function EdroneDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $_SESSION['edrone'] = 'subscribe-' . ((isset($TablicaPost['biuletyn'])) ? 1 : 0) . '-' . $filtr->process($TablicaPost['email']);
             //
        }   

    }    
    
    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function EdroneDoKoszykaDodanie( $Produkt ) {

        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $_SESSION['edrone'] = 'add_to_cart-' . $Produkt->info['id'];
             //
        }
 
    }        
    
    /* plik inne/do_rejestracji.php */
    
    public static function EdroneDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if (INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) && isset($TablicaPost['biuletyn'])) {        
            //
            if ( $plik == 'do_rejestracji' ) {
                 //
                 $_SESSION['edrone'] = 'newsletter_z_rejestracja-1-' . $filtr->process($TablicaPost['email']) . '-Zapis do newslettera, rejestracja konta';
                 //
            }
            if ( $plik == 'do_rejestracji_zamowienie' ) {
                 //
                 // integracja z edrone - jezeli klient bez rejestracji
                 if (!isset($TablicaPost['gosc']) && isset($TablicaPost['biuletyn'])) {
                     //
                     $_SESSION['edrone'] = 'newsletter_bez_rejestracji-1-' . $filtr->process($TablicaPost['email_nowy']) . '-Zapis do newslettera, zakupy bez rejestracji';
                     //
                 }
                 // integracja z edrone - jezeli klient z rejestracja
                 if (isset($TablicaPost['gosc']) && $TablicaPost['gosc'] == '0' && isset($TablicaPost['biuletyn'])) {        
                     //
                     $_SESSION['edrone'] = 'newsletter_z_rejestracja-1-' . $filtr->process($TablicaPost['email_nowy']) . '-Zapis do newslettera, zakup, rejestracja konta';
                     //
                 }                 
                 //
            }            
            //
        }      

    } 

    /* plik inne/do_newslettera.php - usuniecie */
    
    public static function EdroneDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $_SESSION['edrone'] = 'subscribe-0-' . $filtr->process($mail);
             //
        }      
    
    }
    
    /* plik inne/do_newslettera.php - dodanie */
    
    public static function EdroneDoNewsletteraDodanie( $mail = '', $zapis = 'box_modul' ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             if ( $zapis == 'box_modul' ) {
                  //
                  $_SESSION['edrone'] = 'subscribe-1-' . $filtr->process($mail) . '-Zapis do newslettera z boxu lub modulu';
                  //
             } else {
                  //
                  $_SESSION['edrone'] = 'subscribe-1-' . $filtr->process($mail) . '-From PopUp';
                  //
             }
             //
        }         
    
    }     
    
    /* plik klasy/Szablony.php */
    
    public static function EdroneSzablony() {

        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             if ( isset($_SESSION['edrone']) ) {
                 //
                 unset($_SESSION['edrone']);
                 //
             }     
             //
        }
 
    }       
    
    /* plik moduly_stale/newsletter_popup.php */
    
    public static function EdroneNewsletterPopup() {

        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
          
            echo '<input type="hidden" id="edrone_kod" value="1" />';
            
        }  

    }       
    
    /* plik produkt.php */
    
    public static function EdroneProdukt( $Produkt ) {
      
        $wynik = '';

        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) && !isset($_SESSION['edrone']) ) {
            //
            $wynik = '<script type="text/javascript">' . "\n";
            $wynik .= '   (function (srcjs) {' . "\n";
            $wynik .= '   window._edrone = window._edrone || {};' . "\n";
            $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
            $wynik .= '   var doc = document.createElement(\'script\');' . "\n";
            $wynik .= '   doc.type = \'text/javascript\';' . "\n";
            $wynik .= '   doc.async = true;' . "\n";
            $wynik .= '   doc.src = (\'https:\' == document.location.protocol ? \'https:\' : \'http:\') + srcjs;' . "\n";
            $wynik .= '   var s = document.getElementsByTagName(\'script\')[0];' . "\n";
            $wynik .= '   s.parentNode.insertBefore(doc, s);' . "\n";
            $wynik .= '   })("//d3bo67muzbfgtl.cloudfront.net/edrone_2_0.js");' . "\n";
            $wynik .= '</script>' . "\n";         
            //
            $IdKategoriiProduktuWyswietlanego = $Produkt->ProduktKategoriaGlowna();
            //
            $wynik .= '<script type="text/javascript">' . "\n";
            $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
            $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
            $wynik .= '   _edrone.action_type = \'product_view\';' . "\n";
            $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
            $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
            $wynik .= '   _edrone.product_skus = \'' . $Produkt->info['nr_katalogowy'] . '\';' . "\n";
            $wynik .= '   _edrone.product_ids = \'' . $Produkt->info['id'] . '\';' . "\n";
            $wynik .= '   _edrone.product_titles = \'' . $Produkt->info['nazwa'] . '\';' . "\n";
            $wynik .= '   _edrone.product_images = \'' . ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . str_replace("'","\'", $Produkt->fotoGlowne['plik_zdjecia']) . '\';' . "\n";
            $wynik .= '   _edrone.product_urls = \'' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '\';' . "\n";
            $wynik .= '   _edrone.product_category_ids = \'' . Kategorie::SciezkaKategoriiId($IdKategoriiProduktuWyswietlanego['id'], 'id', '~') . '\';' . "\n";
            $wynik .= '   _edrone.product_category_names = \'' . Kategorie::SciezkaKategoriiId($IdKategoriiProduktuWyswietlanego['id'], 'nazwy', '~') . '\';' . "\n";
            $wynik .= '   _edrone.shop_lang = \'' .  $_SESSION['domyslnyJezyk']['nazwa'] . '\';' . "\n";
            
            if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                 //
                 $TablicaKlienta = Klient::daneKlienta((int)$_SESSION['customer_id']);
                 //
                 if ( isset($TablicaKlienta['Imie']) && !empty($TablicaKlienta['Imie']) ) {
                      $wynik .= '   _edrone.first_name = \'' .  $TablicaKlienta['Imie'] . '\';' . "\n";
                 }
                 if ( isset($TablicaKlienta['Nazwisko']) && !empty($TablicaKlienta['Nazwisko']) ) {
                      $wynik .= '   _edrone.last_name = \'' .  $TablicaKlienta['Nazwisko'] . '\';' . "\n";
                 }   
                 if ( isset($TablicaKlienta['Email']) && !empty($TablicaKlienta['Email']) ) {
                      $wynik .= '   _edrone.email = \'' .  $TablicaKlienta['Email'] . '\';' . "\n";
                 }     
                 if ( isset($TablicaKlienta['Kraj']) && !empty($TablicaKlienta['Kraj']) ) {
                      $wynik .= '   _edrone.country = \'' .  $TablicaKlienta['Kraj'] . '\';' . "\n";
                 }    
                 if ( isset($TablicaKlienta['Miasto']) && !empty($TablicaKlienta['Miasto']) ) {
                      $wynik .= '   _edrone.city = \'' .  $TablicaKlienta['Miasto'] . '\';' . "\n";
                 }      
                 if ( isset($TablicaKlienta['Telefon']) && !empty($TablicaKlienta['Telefon']) ) {
                      $wynik .= '   _edrone.phone = \'' .  $TablicaKlienta['Telefon'] . '\';' . "\n";
                 }    
                 //
                 unset($TablicaKlienta);
                 //                    
            }        
            
            $wynik .= '</script>' . "\n";
            
            unset($IdKategoriiProduktuWyswietlanego);

        }

        return $wynik;        
 
    }        
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function EdroneZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' && INTEGRACJA_EDRONE_API != '' && !in_array('COOKIE_INTEGRACJA_EDRONE', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            $wynik = '<script type="text/javascript">' . "\n";
            $wynik .= '   (function (srcjs) {' . "\n";
            $wynik .= '   window._edrone = window._edrone || {};' . "\n";
            $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
            $wynik .= '   var doc = document.createElement(\'script\');' . "\n";
            $wynik .= '   doc.type = \'text/javascript\';' . "\n";
            $wynik .= '   doc.async = true;' . "\n";
            $wynik .= '   doc.src = (\'https:\' == document.location.protocol ? \'https:\' : \'http:\') + srcjs;' . "\n";
            $wynik .= '   var s = document.getElementsByTagName(\'script\')[0];' . "\n";
            $wynik .= '   s.parentNode.insertBefore(doc, s);' . "\n";
            $wynik .= '   })("//d3bo67muzbfgtl.cloudfront.net/edrone_2_0.js");' . "\n";
            $wynik .= '</script>' . "\n";         
            //  
            $wynik .= '<script type="text/javascript">' . "\n";
            $wynik .= '   _edrone.app_id = \'' . INTEGRACJA_EDRONE_API . '\';' . "\n";
            $wynik .= '   _edrone.version = \'1.0.0\';' . "\n";
            $wynik .= '   _edrone.action_type = \'order\';' . "\n";
            $wynik .= '   _edrone.platform_version = \'' . WERSJA . '\';' . "\n";
            $wynik .= '   _edrone.platform = \'shopgold\';' . "\n";
            $wynik .= '   _edrone.email = \'' . $zamowienie->klient['adres_email'] . '\';' . "\n";
            //
            $Podziel = explode(' ', (string)$zamowienie->klient['nazwa']);
            $ImieZamowienie = '';
            $NazwiskoZamowienie = array();
            //
            for ($x = 0; $x < count($Podziel); $x++) {
                //
                if ( $x == 0 ) {
                     $ImieZamowienie = $Podziel[0];
                } else {
                     $NazwiskoZamowienie[] = $Podziel[$x];
                }
                //
            }
            //
            $wynik .= '   _edrone.first_name = \'' . $ImieZamowienie . '\';' . "\n";
            $wynik .= '   _edrone.last_name = \'' . implode(' ', (array)$NazwiskoZamowienie) . '\';' . "\n";
            $wynik .= '   _edrone.phone = \'' . $zamowienie->klient['telefon'] . '\';' . "\n";
            
            unset($Podziel, $ImieZamowienie, $NazwiskoZamowienie);
            
            $skus = array();
            $ids = array();
            $titles = array();
            $images = array();
            $urls = array();
            $category_ids = array();
            $category_names = array();
            $counts = array();
            //
            foreach ( $zamowienie->produkty as $produkt ) {
                //
                $skus[] = $produkt['model'];
                $ids[] = $produkt['id_produktu'];
                $titles[] = $produkt['nazwa'];
                $images[] = ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' .$produkt['zdjecie'];
                $urls[] = ADRES_URL_SKLEPU . '/' . $produkt['adres_url'];
                //
                $KategorieProduktu = Kategorie::ProduktKategorie($produkt['id_produktu'], true);
                //
                $DomyslnaGlowna = $KategorieProduktu[0];
                //
                foreach ( $KategorieProduktu as $Kategoria ) {
                    //
                    if ( $Kategoria['domyslna'] == 1 ) {
                         //
                         $DomyslnaGlowna = $Kategoria;
                         break;
                         //
                    }
                    //          
                }
                //
                $category_ids[] = Kategorie::SciezkaKategoriiId($DomyslnaGlowna['id'], 'id', '~');
                $category_names[] = Kategorie::SciezkaKategoriiId($DomyslnaGlowna['id'], 'nazwy', '~');
                //
                unset($DomyslnaGlowna, $KategorieProduktu);
                //
                $counts[] = (int)$produkt['ilosc'];
                //
            }
            
            $wynik .= '   _edrone.product_skus = \'' . implode('|', (array)$skus) . '\';' . "\n";
            $wynik .= '   _edrone.product_ids = \'' . implode('|', (array)$ids) . '\';' . "\n";
            $wynik .= '   _edrone.product_titles = \'' . implode('|', (array)$titles) . '\';' . "\n";
            $wynik .= '   _edrone.product_images = \'' . implode('|', (array)$images) . '\';' . "\n";
            $wynik .= '   _edrone.product_urls = \'' . implode('|', (array)$urls) . '\';' . "\n";
            $wynik .= '   _edrone.product_category_ids = \'' . implode('|', (array)$category_ids) . '\';' . "\n";
            $wynik .= '   _edrone.product_category_names = \'' . implode('|', (array)$category_names) . '\';' . "\n";
            $wynik .= '   _edrone.product_counts = \'' . implode('|', (array)$counts) . '\';' . "\n";
            
            unset($skus, $ids, $titles, $images, $urls, $category_ids, $category_names, $counts);
            
            $wynik .= '   _edrone.order_id = \'' . $zamowienie->info['id_zamowienia'] . '\';' . "\n";
            $wynik .= '   _edrone.country = \'' . $zamowienie->klient['kraj'] . '\';' . "\n";
            $wynik .= '   _edrone.city = \'' . $zamowienie->klient['miasto'] . '\';' . "\n";
            $wynik .= '   _edrone.base_currency = \'' . $_SESSION['domyslnaWaluta']['kod'] . '\';' . "\n";
            $wynik .= '   _edrone.order_currency = \'' . $zamowienie->info['waluta'] . '\';' . "\n";
            $wynik .= '   _edrone.base_payment_value = \'' . $zamowienie->info['wartosc_zamowienia_val'] . '\';' . "\n";
            $wynik .= '   _edrone.order_payment_value = \'' . $zamowienie->info['wartosc_zamowienia_val'] . '\';' . "\n";
            $wynik .= '</script>' . "\n";  

        }

        return $wynik;
        
    }        
    
    // ========== integracja z NOKAUT sledzenie konwersji
    
    /* plik start.php */
    
    public static function NokautKonwersjaStart() {

        $wynik = '';
        
        if ( INTEGRACJA_NOKAUT_SLEDZENIE_WLACZONY == 'tak' && INTEGRACJA_NOKAUT_SLEDZENIE_ID != '' && !in_array('COOKIE_INTEGRACJA_NOKAUT_SLEDZENIE', $GLOBALS['wykluczeniaIntegracje']) ) {

            $ex = pathinfo($_SERVER['PHP_SELF']);
            
            if ( !isset($ex['extension']) ) {
                 //
                 $roz = explode('.', (string)$_SERVER['PHP_SELF']);
                 $ex['extension'] = $roz[ count($roz) - 1];
                 //
            }               
          
            if ( $GLOBALS['stronaGlowna'] == true || basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'produkt' || basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'listing' ) {

                $wynik .= "<script type=\"text/javascript\">\n";
                $wynik .= "    //  Nokaut.pl Conversion Tracker v2\n";
                $wynik .= "    (function () {\n";
                $wynik .= "        var ns = document.createElement('script'), s = null, stamp = parseInt(new Date().getTime() / 86400, 10);\n";
                $wynik .= "        ns.type = 'text/javascript';\n";
                $wynik .= "        ns.async = true;\n";
                $wynik .= "        ns.src = ('https:' == document.location.protocol ? 'https://nokaut.link/js/' : 'http://nokaut.link/js/') + 'conversion.js?' + stamp;\n";
                $wynik .= "        s = document.getElementsByTagName('script')[0];\n";
                $wynik .= "        s.parentNode.insertBefore(ns, s);\n";
                $wynik .= "    })();\n";
                $wynik .= "</script>\n";

            }
        }
        
        return $wynik;

    } 

    /* plik zamowienie_podsumowanie.php */
    
    public static function NokautKonwersjaZamowieniePodsumowanie( $zamowienie ) {

        $wynik = '';
        
        if ( INTEGRACJA_NOKAUT_SLEDZENIE_WLACZONY == 'tak' && INTEGRACJA_NOKAUT_SLEDZENIE_ID != '' && !in_array('COOKIE_INTEGRACJA_NOKAUT_SLEDZENIE', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wynik .= "<script type=\"text/javascript\">\n";
            $wynik .= "    //  Nokaut.pl Conversion Tracker v2\n";
            $wynik .= "    var _ntrack = _ntrack || [];\n";
            $wynik .= "    _ntrack.push(['trackTransaction', " . INTEGRACJA_NOKAUT_SLEDZENIE_ID . ", '" . $zamowienie->info['wartosc_zamowienia_val'] . "', '" . $_SESSION['zamowienie_id'] . "']);\n";

            $wynik .= "    (function () {\n";
            $wynik .= "        var ns = document.createElement('script'), s = null, stamp = parseInt(new Date().getTime() / 86400, 10);\n";
            $wynik .= "        ns.type = 'text/javascript';\n";
            $wynik .= "        ns.async = true;\n";
            $wynik .= "        ns.src = ('https:' == document.location.protocol ? 'https://nokaut.link/js/' : 'http://nokaut.link/js/') + 'conversion.js?' + stamp;\n";
            $wynik .= "        s = document.getElementsByTagName('script')[0];\n";
            $wynik .= "        s.parentNode.insertBefore(ns, s);\n";
            $wynik .= "    })();\n";
            $wynik .= "</script>\n";

        }        
        
        return $wynik;

    }        
    
    // ========== integracja z ALLANI I DOMODI i integracja z DomodiPixel
    
    /* plik start.php */
    
    public static function AllaniDomodiStart() {

        $wynik = '';
        
        // integracja z ALLANI I DOMODI
        
        if ( INTEGRACJA_ALLANI_SLEDZENIE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ALLANI_SLEDZENIE', $GLOBALS['wykluczeniaIntegracje']) ) {

             $wynik .= "<script async src=\"//allani.pl/assets/tracker_async.js\"></script>\n";

        }

        // integracja z DomodiPixel
        $wynik = "";

        if ( INTEGRACJA_DOMODI_PIXEL_WLACZONY == 'tak' && INTEGRACJA_DOMODI_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_DOMODI_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {

             $wynik .= "<script>\n";
             $wynik .= "!function(d,m,e,v,n,t,s){d['DomodiTrackObject'] = n;\n";
             $wynik .= "d[n] = window[n] || function() {(d[n].queue=d[n].queue||[]).push(arguments)},\n";
             $wynik .= "d[n].l = 1 * new Date(), t=m.createElement(e), s=m.getElementsByTagName(e)[0],\n";
             $wynik .= "t.async=1;t.src=v;s.parentNode.insertBefore(t,s)}(window,document,'script',\n";
             $wynik .= "'https://pixel.wp.pl/w/tr.js','dmq');\n";
             $wynik .= "dmq('init', '" . INTEGRACJA_DOMODI_PIXEL_ID . "');\n";
             $wynik .= "</script>\n";     

        }
        
        return $wynik;

    }   

    /* plik start.php */
    
    public static function AllaniDomodiZamowieniePodsumowanie( $zamowienie ) {

        $wynik = '';
        
        if ( INTEGRACJA_ALLANI_SLEDZENIE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ALLANI_SLEDZENIE', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wynik .= IntegracjeZewnetrzne::AllaniDomodiStart();

            $ProduktyZamowienia = '';

            $i = 1;

            foreach ( $zamowienie->produkty as $produkt ) {

                $ProduktyZamowienia .= $produkt['id_produktu'] . ($i < count($zamowienie->produkty) ? ',' : '' );
                $i++;

            }

            $wynik .= "<script type=\"text/javascript\">\n";
            $wynik .= "window.AllaniTransactions = window.AllaniTransactions || [];\n";
            $wynik .= "window.AllaniTransactions.push([\"" . $ProduktyZamowienia . "\"], \"" . $zamowienie->info['wartosc_zamowienia_val'] . "\", \"" . $_SESSION['zamowienie_id'] . "\");\n";
            $wynik .= "</script>\n";

        }        

        return $wynik;

    }     
    
    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function DomodiPixelDoKoszykaDodanie( $Produkt, $TablicaPost = array() ) {

        if ( INTEGRACJA_DOMODI_PIXEL_WLACZONY == 'tak' && INTEGRACJA_DOMODI_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_DOMODI_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {
        
            echo "<script>";
            if ( $Produkt->info['tylko_za_punkty'] == 'nie' ) {
                 //
                 echo "dmq('track', 'AddToCart', { id: '" . $Produkt->info['id'] . "', name: '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "', price: " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ", quantity: " . (float)$TablicaPost['ilosc'] . " });";
                 //
            }
            echo "</script>";
            
        }  
 
    }   

    /* plik listing_dol.php */
    
    public static function DomodiPixelListingDol( $WyswietlaneProdukty ) {

        $wynik = '';
        
        if ( INTEGRACJA_DOMODI_PIXEL_WLACZONY == 'tak' && INTEGRACJA_DOMODI_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_DOMODI_PIXEL', $GLOBALS['wykluczeniaIntegracje']) && isset($_GET['idkat']) ) {
          
            if ( count($WyswietlaneProdukty) > 0 ) {

                $TabCPath = Kategorie::WyczyscPath($_GET['idkat']);
                $IdWyswietlanejKategorii = $TabCPath[ count($TabCPath) - 1 ];         
                //
                $wynik = "<script>\n";
                $wynik .= "dmq('track', 'ViewContent', {\n";
                $wynik .= "content_type: 'category',\n";

                if ( isset($GLOBALS['tablicaKategorii'][$IdWyswietlanejKategorii]) ) {
                    $wynik .= "name: '" . $GLOBALS['tablicaKategorii'][$IdWyswietlanejKategorii]['Nazwa'] . "',\n";
                }

                $wynik .= "contents: [\n";             
                //
                $domodi_produkty = array();

                foreach ( $WyswietlaneProdukty as $ProduktTmp ) {
                    //
                    $ProduktDomodi = $ProduktTmp; 
                    //
                    if ( !isset($ProduktDomodi->zakupy['mozliwe_kupowanie']) ) {                      
                         $ProduktDomodi->ProduktKupowanie();     
                    }
                    
                    $domodi_produkty[] = "{ id: '" . $ProduktDomodi->info['id'] . "',\n" . 
                                         "name: '" . str_replace(array("'",'"'), "", (string)$ProduktDomodi->info['nazwa']) . "',\n" . 
                                         "price: " . number_format($ProduktDomodi->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n" .
                                         "in_stock: " . (($ProduktDomodi->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktDomodi->zakupy['pokaz_koszyk'] == 'tak') ? 'true' : 'false') . " }\n";            
                    
                    unset($ProduktDomodi);
                    //
                }
                //
                $wynik .= implode(',', (array)$domodi_produkty) . "]";
                unset($domodi_produkty);
                //
                $wynik .= "});\n";
                $wynik .= "</script>\n";
                //
                unset($TabCPath, $IdWyswietlanejKategorii);
                //         

            }

        } 
        
        return $wynik;
 
    }       
    
    /* plik produkt.php */
    
    public static function DomodiPixelProdukt( $Produkt ) {

        $wynik = '';

        if ( INTEGRACJA_DOMODI_PIXEL_WLACZONY == 'tak' && INTEGRACJA_DOMODI_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_DOMODI_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {
            
            $wynik = "<script>\n";
            $wynik .= "dmq('track', 'ViewContent', {\n";
            $wynik .= "content_type: 'product',\n";
            $wynik .= "id: '" . $Produkt->info['id'] . "',\n";
            $wynik .= "name: '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "',\n";
            $wynik .= "price: " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n";
            $wynik .= "in_stock: " . (($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') ? 'true' : 'false') . "\n";
            $wynik .= "});\n";
            $wynik .= "</script>\n";

        }
        
        return $wynik;
    
    }     
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function DomodiPixelZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_DOMODI_PIXEL_WLACZONY == 'tak' && INTEGRACJA_DOMODI_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_DOMODI_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {
          
            $wynik .= IntegracjeZewnetrzne::AllaniDomodiStart();

            $wynik .= "\n";
            $wynik .= '<script>' . "\n";
            
            // wartosc zamowienia - same produkty
            $wartosc_zam = 0;
            foreach ( $zamowienie->produkty as $produkt ) {
                //
                $wartosc_zam += ($produkt['cena_koncowa_netto'] * $produkt['ilosc']);
                //
            }
                
            $wynik .= "dmq('track', 'Purchase', {\n";
            $wynik .= "transaction_id: '" . $_SESSION['zamowienie_id'] . "',\n";
            $wynik .= "currency: '" . $zamowienie->info['waluta'] . "',\n";
            $wynik .= "value: " . number_format($wartosc_zam, 2, '.', '') . ",\n";

            // koszt wysylki i platnosci
            
            $koszt_wysylka = 0;
            $kupon_kod = '';
            
            foreach ( $zamowienie->podsumowanie as $tmp ) {
                 //
                 if ( $tmp['klasa'] == 'ot_shipping' || $tmp['klasa'] == 'ot_payment' ) {
                      $koszt_wysylka += $tmp['wartosc'];
                 }
                 if ( $tmp['klasa'] == 'ot_discount_coupon' ) {
                      $kupon_kod = $tmp['tytul'];
                 }         
                 //
            }
            
            $wynik .= "shipping_cost: " . number_format($koszt_wysylka, 2, '.', '') . ",\n";
            
            if ( $kupon_kod != '' ) {
                 $wynik .= "discount_code: '" . $kupon_kod . "',\n";  
            }
            
            unset($koszt_wysylka, $kupon_kod);
            
            $wynik .= "contents: [\n";  
            
            $domodi_produkty = array();

            foreach ( $zamowienie->produkty as $produkt ) {
              
                $domodi_produkty[] = "{ id: '" . $produkt['id_produktu'] . "',\n" . 
                                     "name: '" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "',\n" . 
                                     "price: " . number_format($produkt['cena_koncowa_brutto'], 2, '.', '') . ",\n" .
                                     "quantity: " . $produkt['ilosc'] . " }\n";            
            }
            
            $wynik .= implode(',', (array)$domodi_produkty) . "]";
            $wynik .= "});\n";
            
            $wynik .= "</script>\n";
            
            unset($domodi_produkty, $wartosc_zam);

        }  

        return $wynik;
        
    }        
    
    // ========== integracja z Pixel WP
    
    /* plik start.php */
    
    public static function WpPixelStart() {

        // integracja z Wp
        $wynik = "";

        if ( INTEGRACJA_WP_PIXEL_WLACZONY == 'tak' && INTEGRACJA_WP_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_WP_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {

             $wynik .= "<script>\n";
             $wynik .= "!function(d,m,e,v,n,t,s){d['WphTrackObject'] = n;\n";
             $wynik .= "d[n] = window[n] || function() {(d[n].queue=d[n].queue||[]).push(arguments)},\n";
             $wynik .= "d[n].l = 1 * new Date(), t=m.createElement(e), s=m.getElementsByTagName(e)[0],\n";
             $wynik .= "t.async=1;t.src=v;s.parentNode.insertBefore(t,s)}(window,document,'script',\n";
             $wynik .= "'https://pixel.wp.pl/w/tr.js', 'wph');\n";
             $wynik .= "wph('init', '" . INTEGRACJA_WP_PIXEL_ID . "');\n";
             $wynik .= "</script>\n";     

        }
        
        return $wynik;

    }   

    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function WpPixelDoKoszykaDodanie( $Produkt, $TablicaPost = array(), $Kategorie = array() ) {

        if ( INTEGRACJA_WP_PIXEL_WLACZONY == 'tak' && INTEGRACJA_WP_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_WP_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {

            echo "<script>";
            if ( $Produkt->info['tylko_za_punkty'] == 'nie' ) {
                 //
                 echo "wph('track', 'AddToCart', { id: '" . $Produkt->info['id'] . "', name: '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "', price: " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ", quantity: " . (float)$TablicaPost['ilosc'] . ", category: '" . str_replace(array("'",'"'), "", Produkty::pokazKategorieProduktu((int)$Produkt->info['id'])) . "', ean: '" . $Produkt->info['ean'] . "', weight: " . number_format($Produkt->info['waga'], 2, '.', '') . " });";
                 //
            }
            echo "</script>";
            
        }  
 
    }   

    /* plik listing_dol.php */
    
    public static function WpPixelListingDol( $WyswietlaneProdukty ) {

        $wynik = '';
        
        if ( INTEGRACJA_WP_PIXEL_WLACZONY == 'tak' && INTEGRACJA_WP_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_WP_PIXEL', $GLOBALS['wykluczeniaIntegracje']) && isset($_GET['idkat']) ) {
          
            if ( count($WyswietlaneProdukty) > 0 ) {

                $TabCPath = Kategorie::WyczyscPath($_GET['idkat']);
                $IdWyswietlanejKategorii = $TabCPath[ count($TabCPath) - 1 ];         
                //
                $wynik = "<script>\n";
                $wynik .= "wph('track', 'ViewContent', {\n";
                $wynik .= "content_name: 'ProductList',\n";
                $wynik .= "contents: [\n";             
                //
                $wp_produkty = array();

                foreach ( $WyswietlaneProdukty as $ProduktTmp ) {
                    //
                    $ProduktWp = $ProduktTmp; 
                    //
                    if ( !isset($ProduktWp->zakupy['mozliwe_kupowanie']) ) {                      
                         $ProduktWp->ProduktKupowanie();     
                    }
                    
                    $wp_produkty[] = "{ id: '" . $ProduktWp->info['id'] . "',\n" . 
                                       "name: '" . str_replace(array("'",'"'), "", (string)$ProduktWp->info['nazwa']) . "',\n" . 
                                       (( isset($GLOBALS['tablicaKategorii'][$ProduktWp->info['id_kategorii']]) ) ? "category: '" . str_replace(array("'",'"'), "",$GLOBALS['tablicaKategorii'][$ProduktWp->info['id_kategorii']]['Nazwa']) . "',\n" : '') .
                                       "price: " . number_format($ProduktWp->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n" .
                                       "in_stock: " . (($ProduktWp->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktWp->zakupy['pokaz_koszyk'] == 'tak') ? 'true' : 'false') . ",\n" .
                                       "ean: '" . (string)$ProduktWp->info['ean'] . "',\n" .
                                       "weight: " . number_format($ProduktWp->info['waga'], 2, '.', '') . " }\n";
                    
                    unset($ProduktWp);
                    //
                }
                //
                $wynik .= implode(',', (array)$wp_produkty) . "]";
                unset($wp_produkty);
                //
                $wynik .= "});\n";
                $wynik .= "</script>\n";
                //
                unset($TabCPath, $IdWyswietlanejKategorii);
                //         

            }

        } 
        
        return $wynik;
 
    }       
    
    /* plik produkt.php */
    
    public static function WpPixelProdukt( $Produkt ) {

        $wynik = '';

        if ( INTEGRACJA_WP_PIXEL_WLACZONY == 'tak' && INTEGRACJA_WP_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_WP_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {
            
            $wynik = "<script>\n";
            $wynik .= "wph('track', 'ViewContent', {\n";
            $wynik .= "content_name: 'ViewProduct',\n";
            $wynik .= "contents: [{\n";           
            $wynik .= "id: '" . $Produkt->info['id'] . "',\n";
            $wynik .= "name: '" . str_replace(array("'",'"'), "", (string)$Produkt->info['nazwa']) . "',\n";
            $wynik .= (( isset($GLOBALS['tablicaKategorii'][$Produkt->info['id_kategorii']]) ) ? "category: '" . str_replace(array("'",'"'), "",$GLOBALS['tablicaKategorii'][$Produkt->info['id_kategorii']]['Nazwa']) . "',\n" : '');
            $wynik .= "price: " . number_format($Produkt->info['cena_brutto_bez_formatowania'], 2, '.', '') . ",\n";
            $wynik .= "in_stock: " . (($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') ? 'true' : 'false') . ",\n";
            $wynik .= "ean: '" . (string)$Produkt->info['ean'] . "',\n";
            $wynik .= "weight: " . number_format($Produkt->info['waga'], 2, '.', '') . "\n";
            $wynik .= "}]\n"; 
            $wynik .= "});\n";
            $wynik .= "</script>\n";

        }
        
        return $wynik;
    
    }     
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function WpPixelZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_WP_PIXEL_WLACZONY == 'tak' && INTEGRACJA_WP_PIXEL_ID != '' && !in_array('COOKIE_INTEGRACJA_WP_PIXEL', $GLOBALS['wykluczeniaIntegracje']) ) {
          
            $wynik .= IntegracjeZewnetrzne::WpPixelStart();

            $wynik .= "\n";
            $wynik .= '<script>' . "\n";
            
            // wartosc zamowienia - same produkty
            $wartosc_zam = 0;
            $wartosc_zam_netto = 0;
            foreach ( $zamowienie->produkty as $produkt ) {
                //
                $wartosc_zam += ($produkt['cena_koncowa_brutto'] * $produkt['ilosc']);
                $wartosc_zam_netto += ($produkt['cena_koncowa_netto'] * $produkt['ilosc']);
                //
            }
                
            $wynik .= "wph('track', 'Purchase', {\n";
            $wynik .= "transaction_id: '" . $_SESSION['zamowienie_id'] . "',\n";
            $wynik .= "currency: '" . $zamowienie->info['waluta'] . "',\n";
            $wynik .= "value: " . number_format($wartosc_zam_netto, 2, '.', '') . ",\n";
            $wynik .= "value_gross: " . number_format($wartosc_zam, 2, '.', '') . ",\n";

            // koszt wysylki i platnosci
            
            $koszt_wysylka = 0;
            $kupon_kod = '';
            
            foreach ( $zamowienie->podsumowanie as $tmp ) {
                 //
                 if ( $tmp['klasa'] == 'ot_shipping' || $tmp['klasa'] == 'ot_payment' ) {
                      $koszt_wysylka += $tmp['wartosc'];
                 }
                 if ( $tmp['klasa'] == 'ot_discount_coupon' ) {
                      $kupon_kod = $tmp['tytul'];
                 }         
                 //
            }
            
            $wynik .= "shipping_cost: " . number_format($koszt_wysylka, 2, '.', '') . ",\n";
            
            if ( $kupon_kod != '' ) {
                 $wynik .= "discount_code: '" . $kupon_kod . "',\n";  
            }
            
            unset($koszt_wysylka, $kupon_kod);
            
            $wynik .= "contents: [\n";  
            
            $wp_produkty = array();

            foreach ( $zamowienie->produkty as $produkt ) {
              
                $wp_produkty[] = "{ id: '" . $produkt['id_produktu'] . "',\n" . 
                                   "name: '" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "',\n" . 
                                   "price: " . number_format($produkt['cena_koncowa_brutto'], 2, '.', '') . ",\n" .
                                   "quantity: " . $produkt['ilosc'] . ",\n" .
                                   (( isset($GLOBALS['tablicaKategorii'][$produkt['id_kategorii']]) ) ? "category: '" . str_replace(array("'",'"'), "",$GLOBALS['tablicaKategorii'][$produkt['id_kategorii']]['Nazwa']) . "',\n" : '') .
                                   "ean: '" . (string)$produkt['ean'] . "',\n" .
                                   "weight: " . number_format($produkt['weight'], 2, '.', '') . " }\n";                                     
            }
            
            $wynik .= implode(',', (array)$wp_produkty) . "]";
            $wynik .= "});\n";
            
            $wynik .= "</script>\n";
            
            unset($wp_produkty, $wartosc_zam);

        }  

        return $wynik;
        
    }      
    
    // ========== integracja z SALESmanago
    
    /* plik start.php */
    
    public static function SalesManagoStart() {

        $wynik = '';
        
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
            
             $wynik .= '<script type="text/javascript">' . "\n";
             $wynik .= 'var _smid = "' . INTEGRACJA_SALESMANAGO_ID_KLIENTA . '";' . "\n";
             $wynik .= '(function(w, r, a, sm, s ) {' . "\n";
             $wynik .= 'w[\'SalesmanagoObject\'] = r;' . "\n";
             $wynik .= 'w[r] = w[r] || function () {( w[r].q = w[r].q || [] ).push(arguments)};' . "\n";
             $wynik .= 'sm = document.createElement(\'script\'); sm.type = \'text/javascript\'; sm.async = true; sm.src = a;' . "\n";
             $wynik .= 's = document.getElementsByTagName(\'script\')[0];' . "\n";
             $wynik .= 's.parentNode.insertBefore(sm, s);' . "\n";
             $wynik .= '})(window, \'sm\', (\'https:\' == document.location.protocol ? \'https://\' : \'http://\') + \'' .  INTEGRACJA_SALESMANAGO_ENDPOINT. '/static/sm.js\');' . "\n";
             $wynik .= '</script>' . "\n";  

        }
        
        return $wynik;

    }  
    
    /* plik aktywacja_konta.php */

    public static function SalesManagoAktywacjaKonta( $info_klient ) {

        $wynik = '';
        
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {

             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient
             $SmKlient = $salesmanago->CzyJestKlient( array('email' => $info_klient['customers_email_address']) );
             //
             $dane = array('nazwa' => $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'], 
                           'email' => $info_klient['customers_email_address'], 
                           'firma' => $info_klient['entry_company'],
                           'telefon' => $info_klient['customers_telephone'],
                           'ulica' => $info_klient['entry_street_address'],
                           'kod_pocztowy' => $info_klient['entry_postcode'],
                           'miasto' => $info_klient['entry_city'],
                           'kraj' => Klient::pokazNazwePanstwa((int)$info_klient['entry_country_id']),
                           'tags' => 'Klient zarejestrowany w sklepie');
             //
             if ( $info_klient['customers_newsletter'] == 1 ) {
                  //
                  $dane['newsletter'] = 'tak';
                  //
             } else {
                  //
                  $dane['newsletter'] = 'nie';
                  //
             }
             //          
             if ( $SmKlient != '' ) {
                  //
                  $dane['smclient'] = $SmKlient;
                  //
             }
             //                         
             $sm = $salesmanago->ZapiszKlienta( $dane, (($SmKlient != '') ? false : true) );
             //
             unset($dane);

        }  
        
        echo $wynik;

    }

    /* plik dane_adresowe.php */
    
    public static function SalesManagoDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient
             if ( !isset($_COOKIE['smclient'])) {
                  $SmKlient = $salesmanago->CzyJestKlient( array('email' => $filtr->process($TablicaPost['email'])) );
             } else {
                  $SmKlient = $_COOKIE['smclient'];
             }
             //
             $dane = array('nazwa' => $filtr->process($TablicaPost['imie']) . ' ' . $filtr->process($TablicaPost['nazwisko']), 
                           'email' => $filtr->process($TablicaPost['email']), 
                           'firma' => $filtr->process($TablicaPost['nazwa_firmy']), 
                           'telefon' => $filtr->process($TablicaPost['telefon']),
                           'ulica' => $filtr->process($TablicaPost['ulica']),
                           'kod_pocztowy' => $filtr->process($TablicaPost['kod_pocztowy']),
                           'miasto' => $filtr->process($TablicaPost['miasto']),
                           'kraj' => Klient::pokazNazwePanstwa((int)$TablicaPost['panstwo']));
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                  //
                  $dane['newsletter'] = 'tak';
                  //
             } else {
                  //
                  $dane['newsletter'] = 'nie';
                  //
             }
             //
             if ( $SmKlient != '' ) {
                  //
                  $dane['smclient'] = $SmKlient;
                  //
             }
             //                         
             $sm = $salesmanago->ZapiszKlienta( $dane, (($SmKlient != '') ? false : true) );
             //
             unset($dane);
             //
        }  

    }   

    /* plik dane_adresowe.php - usuniecie konta */ 
    
    public static function SalesManagoDaneAdresoweUsuniecie() {

        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);     
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                 $info = $sql->fetch_assoc();    
                 
                 $salesmanago = new SalesManago();
                 // sprawdzi czy jest klient                             
                 $SmKlient = $salesmanago->CzyJestKlient( array('email' => $info['subscribers_email_address']), 'tak' );
                 //
                 if ( $SmKlient != '' ) {
                      //
                      $dane = array('email' => $info['subscribers_email_address']);
                      //
                      $sm = $salesmanago->UsunKlienta( $dane );
                      //
                      unset($dane);
                      //
                 }
                 //
                 
                 unset($info);
                 
             }
             
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);               
        }   

    }     

    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function SalesManagoDoKoszykaDodanie( $Produkt, $Ilosc = 0 ) {

        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             if ( isset($_COOKIE['smclient']) && $_COOKIE['smclient'] != '' ) {
                  //
                  $salesmanago = new SalesManago();
                  //
                  $dane = array('smclient' => $_COOKIE['smclient'], 
                                'opis' => 'Dodanie produktu do koszyka',
                                'typ' => 'CART');
                  
                  if ( isset($_SESSION['customer_email']) ) {
                       $dane['email'] = $_SESSION['customer_email'];
                  }
                  
                  $IdProduktow = array();
                  $IdProduktow[ $Produkt->info['id'] ] = $Produkt->info['id'];
                  //
                  $NazwyProduktow = array();
                  $NazwyProduktow[ $Produkt->info['id'] ] = $Produkt->info['nazwa'];

                  $SumaKoszyka = 0;

                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                      //
                      $IdTmp = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] );
                      //
                      $IdProduktow[ $IdTmp ] = $IdTmp;
                      $NazwyProduktow[ $IdTmp ] = $TablicaZawartosci['nazwa'];
                      //
                      $SumaKoszyka += $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
                      //
                      unset($IdTmp);
                      //
                  } 
                  //
                  $dane['wartosc'] = $SumaKoszyka;  
                  //
                  $dane['produkty_id'] = $IdProduktow;
                  $dane['produkty_nazwy'] = $NazwyProduktow;
                  //
                  $sm = $salesmanago->DodajZdarzenieKlienta( $dane );
                  //
                  unset($dane, $IdProduktow, $NazwyProduktow);
                  //
             }
             
        } 

    }     
    
    /* plik inne/do_logowania.php i inne/do_logowanie_zamowienie.php */
    
    public static function SalesManagoDoLogowania( $info, $firma, $info_adres ) {

        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient
             $SmKlient = $salesmanago->CzyJestKlient( array('email' => $info['customers_email_address']) );
             //
             $dane = array('nazwa' => $info['customers_firstname'] . ' ' . $info['customers_lastname'], 
                           'email' => $info['customers_email_address'], 
                           'firma' => $firma, 
                           'telefon' => $info_adres['customers_telephone'],
                           'ulica' => $info_adres['entry_street_address'],
                           'kod_pocztowy' => $info_adres['entry_postcode'],
                           'miasto' => $info_adres['entry_city'],
                           'kraj' => Klient::pokazNazwePanstwa($info_adres['entry_country_id']));
             //
             if ( $SmKlient != '' ) {
                  //
                  $dane['smclient'] = $SmKlient;
                  //
             }
             //                         
             $sm = $salesmanago->ZapiszKlienta( $dane, (($SmKlient != '') ? false : true) );
             //
             unset($dane);
             //
        }   

    }   

    /* plik inne/do_rejestracji.php i inne/do_rejestracji_zamowienie.php */
    
    public static function SalesManagoDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient
             //
             $emailKlient = (($plik == 'do_rejestracji' ) ? $filtr->process($TablicaPost['email']) : $filtr->process($TablicaPost['email_nowy']));
             //
             $SmKlient = $salesmanago->CzyJestKlient( array('email' => $emailKlient) );
             //
             $dane = array('nazwa' => $filtr->process($TablicaPost['imie']) . ' ' . $filtr->process($TablicaPost['nazwisko']), 
                           'email' => $emailKlient, 
                           'firma' => $filtr->process($TablicaPost['nazwa_firmy']), 
                           'telefon' => $filtr->process($TablicaPost['telefon']),
                           'ulica' => $filtr->process($TablicaPost['ulica']),
                           'kod_pocztowy' => $filtr->process($TablicaPost['kod_pocztowy']),
                           'miasto' => $filtr->process($TablicaPost['miasto']),
                           'kraj' => Klient::pokazNazwePanstwa((int)$TablicaPost['panstwo']));
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                  //
                  $dane['newsletter'] = 'tak';
                  //
             } else {
                  //
                  $dane['newsletter'] = 'nie';
                  //
             }
             
             if ( $plik == 'do_rejestracji' ) {
                  //
                  $dane['tags'] = 'Klient zarejestrowany w sklepie';
                  //
             } else {
                  //
                  if ( isset($TablicaPost['gosc']) && $TablicaPost['gosc'] == '0' ) {
                       //
                       $dane['tags'] = 'Klient zarejestrowany w sklepie';
                       //
                  } else {
                       //
                       $dane['tags'] = 'Klient bez rejestracji konta';
                       //
                  }             
                  //
             }
         
             if ( $SmKlient != '' ) {
                  //
                  $dane['smclient'] = $SmKlient;
                  //
             }
             //                         
             $sm = $salesmanago->ZapiszKlienta( $dane, (($SmKlient != '') ? false : true) );
             //
             unset($dane, $emailKlient);
             //
        }   

    }    
    
    /* plik inne/do_newslettera.php - usuniecie */
    
    public static function SalesManagoDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient
             $zalogowanySm = false;
             if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) {
                  //
                  $zalogowanySm = 'tak';
                  //
             }
             //                               
             $SmKlient = $salesmanago->CzyJestKlient( array('email' => $filtr->process($mail)), $zalogowanySm );
             //
             $dane = array('email' => $filtr->process($mail),
                           'newsletter' => 'nie');
             //
             if ( $SmKlient != '' ) {
                  //
                  $dane['smclient'] = $SmKlient;
                  //
             }
             //                       
             $sm = $salesmanago->ZapiszKlienta( $dane, (($SmKlient != '') ? false : true), $zalogowanySm );
             //
             unset($dane, $zalogowanySm);
             //
        }  

    }  

    /* plik inne/do_newslettera.php oraz newsletter.php - dodanie */
    
    public static function SalesManagoDoNewsletteraDodanie( $mail = '', $rejestracja = true ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient
             $zalogowanySm = false;
             if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) {
                  //
                  $zalogowanySm = 'tak';
                  //
             }
             //                               
             $SmKlient = $salesmanago->CzyJestKlient( array('email' => $filtr->process($mail)), $zalogowanySm );
             //
             $dane = array('email' => $filtr->process($mail),
                           'newsletter' => 'tak');
             //
             if ( $SmKlient != '' ) {
                  //
                  $dane['smclient'] = $SmKlient;
                  //
             }
             //
             if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $rejestraca == true ) {
                  //
                  $dane['zalogowany'] = 'tak';
                  //
             }
             //                   
             $sm = $salesmanago->ZapiszKlienta( $dane, (($SmKlient != '') ? false : true), $zalogowanySm );
             //
             unset($dane, $zalogowanySm);
             //
        }           
    
    }      
    
    /* plik inne/usun_z_koszyka.php */
    
    public static function SalesManagoUsunZKoszyka( $Produkt ) {

        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             if ( isset($_COOKIE['smclient']) && $_COOKIE['smclient'] != '' ) {
                  //
                  $salesmanago = new SalesManago();
                  //
                  $dane = array('smclient' => $_COOKIE['smclient'], 
                                'opis' => 'Usunięcie produktu z koszyka',
                                'typ' => 'CART');
                  
                  if ( isset($_SESSION['customer_email']) ) {
                       $dane['email'] = $_SESSION['customer_email'];
                  }
                  
                  $IdProduktow = array();
                  $NazwyProduktow = array();
                  
                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                      //
                      $IdTmp = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] );
                      //
                      $IdProduktow[ $IdTmp ] = $IdTmp;
                      $NazwyProduktow[ $IdTmp ] = $TablicaZawartosci['nazwa'];
                      //
                      unset($IdTmp);
                      //
                  }            
                  //
                  unset( $IdProduktow[ $Produkt->info['id'] ] );
                  unset( $NazwyProduktow[ $Produkt->info['id'] ] );
                  //
                  $dane['produkty_id'] = $IdProduktow;
                  $dane['produkty_nazwy'] = $NazwyProduktow;
                  //
                  $sm = $salesmanago->DodajZdarzenieKlienta( $dane );
                  //
                  unset($dane, $IdProduktow, $NazwyProduktow);
                  //
             }
             
        } 

    }     
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function SalesManagoZamowieniePodsumowanie( $zamowienie ) {
       
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESMANAGO', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             if ( isset($_COOKIE['smclient']) && $_COOKIE['smclient'] != '' ) {
                  //
                  $salesmanago = new SalesManago();
                  //
                  $dane = array('smclient' => $_COOKIE['smclient'], 
                                'opis' => 'Złożenie zamówienia nr ' . (int)$_SESSION['zamowienie_id'],
                                'typ' => 'PURCHASE');
                  
                  if ( isset($_SESSION['customer_email']) ) {
                       $dane['email'] = $_SESSION['customer_email'];
                  }
                  
                  $IdProduktow = array();
                  $NazwyProduktow = array();
                  
                  foreach( $zamowienie->produkty as $KupionyProdukt ) {
                      //
                      $IdProduktow[ $KupionyProdukt['id_produktu'] ] = $KupionyProdukt['id_produktu'];
                      $NazwyProduktow[ $KupionyProdukt['id_produktu'] ] = $KupionyProdukt['nazwa'];
                      //
                  }            
                  //
                  $dane['produkty_id'] = $IdProduktow;
                  $dane['produkty_nazwy'] = $NazwyProduktow;
                  $dane['wartosc'] = $zamowienie->info['wartosc_zamowienia_val'];
                  //
                  $sm = $salesmanago->DodajZdarzenieKlienta( $dane );
                  //
                  setcookie("sm_cart", '', time() - 86400, '/');          
                  //
                  unset($dane, $IdProduktow, $NazwyProduktow);
                  //
             }
             
        }  

    }        
    
    // ========== integracja z TRUSTISTO
    
    /* plik start.php */
    
    public static function TrustistoStart( $WywolanyPlik ) {

        global $filtr;

        $wynik = '';
        
        if ( INTEGRACJA_TRUSTISTO_WLACZONY == 'tak' && INTEGRACJA_TRUSTISTO_KODWITRYNY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTISTO', $GLOBALS['wykluczeniaIntegracje']) ) {


            $wynik .= "<script>\n";
            $wynik .= "  (function(a,b,c,d,e,f,g,h,i){\n";
            $wynik .= "    h=a.SPT={u:d},a.SP={init:function(a,b){h.ai=a;h.cb=b},\n";
            $wynik .= "    go:function(){(h.eq=h.eq||[]).push(arguments)}},\n";
            $wynik .= "    g=b.getElementsByTagName(c)[0],f=b.createElement(c),\n";
            $wynik .= "    f.async=1,f.src=\"//js\"+d+e,i=g.parentNode.insertBefore(f,g)\n";
            $wynik .= "  })(window,document,\"script\",\".trustisto.com\",\"/socialproof.js\");\n";
            $wynik .= "  SP.init(\"" . INTEGRACJA_TRUSTISTO_KODWITRYNY . "\");\n";
            $wynik .= "</script>\n";

            if (isset($WywolanyPlik) && ( $WywolanyPlik == 'strona_glowna' || $WywolanyPlik == 'produkt' || $WywolanyPlik == 'listing' || $WywolanyPlik == 'szukaj' || $WywolanyPlik == 'koszyk' || $WywolanyPlik == 'zamowienie_podsumowanie' ) ) {


                switch( $WywolanyPlik ) {

                    case 'strona_glowna':
                        $wynik .= "<script>\n";
                        $wynik .= "  SP.go('startPage');\n";
                        $wynik .= "</script>\n";
                        break;
                    case 'listing':
                        $IdKat = '';
                        if ( isset($_GET['idkat']) ) {
                            //
                            $TabCPath = Kategorie::WyczyscPath($_GET['idkat']);
                            $IdWyswietlanejKategorii = $TabCPath[ count($TabCPath) - 1 ];
                            $IdKat = $GLOBALS['tablicaKategorii'][$IdWyswietlanejKategorii]['IdKat'];
                            unset($TabCPath, $IdWyswietlanejKategorii);
                        }
                        $wynik .= "<script>\n";
                        $wynik .= "  SP.go('categoryPage', {\n";
                        $wynik .= "  categoryId: '".$IdKat."'\n";
                        $wynik .= "  });\n";
                        $wynik .= "</script>\n";
                        unset($IdKat);
                        break;
                    case 'szukaj':
                        if ( isset($_GET['szukaj']) ) {
                             //
                             $wynik .= "<script>\n";
                             $wynik .= "  SP.go('searchPage', {\n";
                             $wynik .= "  searchQuery: '".str_replace("'", "", (string)$filtr->process($_GET['szukaj']))."'\n";
                             $wynik .= "  });\n";
                             $wynik .= "</script>\n";
                        }
                        break;
                }
            }

        }
        
        return $wynik;

    }    

    /* plik produkt.php */
    public static function TrustistoProdukt( $Produkt ) {

        $wynik = '';

        if ( INTEGRACJA_TRUSTISTO_WLACZONY == 'tak' && INTEGRACJA_TRUSTISTO_KODWITRYNY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTISTO', $GLOBALS['wykluczeniaIntegracje']) ) {
            
            $wynik = '';
            $wynik .= "<script>\n";
            $wynik .= "  SP.go('productPage',{\n";
            $wynik .= "    productId: \"" . $Produkt->info['id'] . "\",\n";
            $wynik .= "    product: \"".$Produkt->info['nazwa'] . "\",\n";
            $wynik .= "    link: \"" . ADRES_URL_SKLEPU  .  '/' . $Produkt->info['adres_seo'] . "\",\n";
            $wynik .= "    image: \"" . ADRES_URL_SKLEPU  .  '/' . KATALOG_ZDJEC . '/' . str_replace('"','\"', $Produkt->fotoGlowne['plik_zdjecia']) . "\"\n";
            $wynik .= "  });\n";
            $wynik .= "</script>\n";

        }
        
        return $wynik;
    
    }    

    /* plik zamowienie_podsumowanie.php */
    
    public static function TrustistoZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_TRUSTISTO_WLACZONY == 'tak' && INTEGRACJA_TRUSTISTO_KODWITRYNY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTISTO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $Podziel = explode(' ', (string)$zamowienie->klient['nazwa']);
            $ImieZamowienie = '';
            $NazwiskoZamowienie = array();
            //
            for ($x = 0; $x < count($Podziel); $x++) {
                //
                if ( $x == 0 ) {
                     $ImieZamowienie = $Podziel[0];
                } else {
                     $NazwiskoZamowienie[] = $Podziel[$x];
                }
                //
            }

            $wynik .= "\n";
            $wynik .= '<script>' . "\n";

            $wynik .= '  SP.go(\'thankYouPage\', {' . "\n";
            $wynik .= '  order: {' . "\n";
            $wynik .= '    id: "'.(int)$_SESSION['zamowienie_id'].'",' . "\n";
            $wynik .= '    total: "'.number_format($zamowienie->info['wartosc_zamowienia_val'], 2, '.', '').'"' . "\n";
            $wynik .= '  },' . "\n";

            $wynik .= '  client: {' . "\n";
            $wynik .= '    firstname: "'.$ImieZamowienie.'",' . "\n";
            $wynik .= '    lastname: "'.implode(' ', (array)$NazwiskoZamowienie).'",' . "\n";
            $wynik .= '    city: "'.$zamowienie->klient['miasto'].'",' . "\n";
            $wynik .= '    email: "'.$zamowienie->klient['adres_email'].'",' . "\n";
            $wynik .= '    phone: "'.$zamowienie->klient['telefon'].'"' . "\n";
            $wynik .= '  },' . "\n";

            $wynik .= '  basket: [' . "\n";

            foreach ( $zamowienie->produkty as $produkt ) {

                $wynik .= '  {' . "\n";
                $wynik .= '    productId: "' . $produkt['id_produktu'] . '",' . "\n";
                $wynik .= '    product: "' . $produkt['nazwa'] . '",' . "\n";
                $wynik .= '    link: "' . ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt' ) . '",' . "\n";
                $wynik .= '    image: "' . ADRES_URL_SKLEPU . "/" . KATALOG_ZDJEC . "/" . $produkt['zdjecie'] . '",' . "\n";
                $wynik .= '    quantity: "' . $produkt['ilosc'] . '",' . "\n";
                $wynik .= '    price: "' . $produkt['cena_koncowa_brutto'] . '",' . "\n";
                $wynik .= '    sum: "' . round(($produkt['cena_koncowa_brutto'] * $produkt['ilosc']),2) . '",' . "\n";
                $wynik .= '  },' . "\n";

            }

            $wynik .= '  ]' . "\n";
            $wynik .= '});' . "\n";

            $wynik .= '</script>' . "\n";

            unset($ImieZamowienie, $NazwiskoZamowienie);

        }

        return $wynik;
        
    }        

    /* plik koszyk.php */
    
    public static function TrustistoKoszyk() {    

        $wynik = '';

        if ( INTEGRACJA_TRUSTISTO_WLACZONY == 'tak' && INTEGRACJA_TRUSTISTO_KODWITRYNY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTISTO', $GLOBALS['wykluczeniaIntegracje']) ) {
            $wynik .= "<script>\n";
            $wynik .= "SP.go('basketPage', [\n";

            foreach ( $_SESSION['koszyk'] as $produkt ) {

                $wynik .= "{" . "\n" .
                          "  productId: '" . Funkcje::SamoIdProduktuBezCech($produkt['id']) . "'," . "\n" .
                          "  product: '" . str_replace(array("'",'"'), "", (string)$produkt['nazwa']) . "'," . "\n" .
                          "  link: '" . ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id'], 'produkt' ) . "'," . "\n" .
                          "  image: '" . ADRES_URL_SKLEPU . "/" . KATALOG_ZDJEC . "/" . $produkt['zdjecie'] . "'," . "\n" .
                          "  quantity: '" . $produkt['ilosc'] . "'," . "\n" .
                          "  price: '" . $produkt['cena_bazowa_brutto'] . "'," . "\n" .
                          "  sum: '" . round(($produkt['cena_bazowa_brutto'] * $produkt['ilosc']),2) . "'," . "\n" .
                          "},\n";
                    
            }

            $wynik .= "])\n";
            $wynik .= "</script>\n";
        }

        return $wynik;
    
    }
    

    // ========== integracja z shopeneo.network
    
    public static function ShopeneoNetworkStart() {

        $wynik = '';
        
        if ( INTEGRACJA_SHOPENEO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SHOPENEO', $GLOBALS['wykluczeniaIntegracje']) ) {

             $wynik .= "<script type=\"application/javascript\">\n";
             $wynik .= "    (function () {\n";
             $wynik .= "        const head = document.head;\n";
             $wynik .= "        const script = document.createElement('script');\n";
             $wynik .= "        script.type = 'text/javascript';\n";
             $wynik .= "        script.src = 'https://app.shopeneo.network/js/shopeneo.js';\n";
             $wynik .= "        head.appendChild(script);\n";
             $wynik .= "    })();\n";
             $wynik .= "</script>\n";

        }
        
        return $wynik;

    }

    // ========== integracja z Zaufane Opinie CENEO
    
    public static function CeneoOpinieStart() {

        $wynik = '';
        
        if ( INTEGRACJA_CENEO_OPINIE_WLACZONY == 'tak' && INTEGRACJA_CENEO_OPINIE_ID != '' && !in_array('COOKIE_INTEGRACJA_CENEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wynik .= "<script>(function(w,d,s,i,dl){w._ceneo = w._ceneo || function () {\n";
            $wynik .= "w._ceneo.e = w._ceneo.e || []; w._ceneo.e.push(arguments); };\n";
            $wynik .= "w._ceneo.e = w._ceneo.e || [];dl=dl===undefined?\"dataLayer\":dl;\n";
            $wynik .= "const f = d.getElementsByTagName(s)[0], j = d.createElement(s); j.defer = true; j.src = \"https://ssl.ceneo.pl/ct/v5/script.js?accountGuid=\" + i + \"&t=\" + Date.now() + (dl ? \"&dl=\" + dl : '');\n";
            $wynik .= "f.parentNode.insertBefore(j, f);\n";
            $wynik .= "})(window, document, \"script\", \"".INTEGRACJA_CENEO_OPINIE_ID."\");</script>\n";

        }
        
        return $wynik;

    }

    // ========== integracja z TrustPilot
    
    public static function TrustPilotStart() {

        $wynik = '';
        
        if ( INTEGRACJA_TRUSTPILOT_WLACZONY == 'tak' && INTEGRACJA_TRUSTPILOT_KEY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTPILOT', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wynik .= "<script>\n";
            $wynik .= "     (function(w,d,s,r,n){w.TrustpilotObject=n;w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)};\n";
            $wynik .= "     a=d.createElement(s);a.async=1;a.src=r;a.type='text/java'+s;f=d.getElementsByTagName(s)[0];\n";
            $wynik .= "     f.parentNode.insertBefore(a,f)})(window,document,'script', 'https://invitejs.trustpilot.com/tp.min.js', 'tp');\n";
            $wynik .= "     tp('register', '".INTEGRACJA_TRUSTPILOT_KEY."');\n";
            $wynik .= "</script>\n";

        }
        
        return $wynik;

    }

    // ========== integracja z Callback24
    
    public static function Callback24Start() {

        $wynik = '';
        
        if ( INTEGRACJA_CALLBACK24_WLACZONY == 'tak' && INTEGRACJA_CALLBACK24_KOD != '' ) {

            $wynik .= INTEGRACJA_CALLBACK24_KOD;

        }
        
        return $wynik;

    }

    // ========== integracja z Freshmail
    
    /* plik aktywacja_konta.php */
    
    public static function FreshmailAktywacjaKonta( $info_klient ) {

        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) && $info_klient['customers_newsletter'] == 1 ) {
             //
             // doda maila do grupy domyslnej
             $freshMail = new FreshMail();
             $freshMail->ZapiszSubskrybenta( $info_klient['customers_email_address'], 1, INTEGRACJA_DOMYSLNA_LISTA );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $freshMail->ZapiszSubskrybenta( $info_klient['customers_email_address'], 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($freshMail);
             //
        }
        
    }    
    
    /* plik dane_adresowe.php */
    
    public static function FreshmailDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $freshMail = new FreshMail();
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                 //
                 // doda maila do grupy domyslnej
                 $freshMail->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), 1, INTEGRACJA_DOMYSLNA_LISTA );
                 //
                 // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
                 if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                      //
                      $freshMail->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                      //
                 }
                 //
             } else {
                 //
                 // usunie z list
                 $freshMail->UsunSubskrybenta( $filtr->process($TablicaPost['email']) );
                 //
             }
             //
             unset($freshMail);
             //
        }  

    }    
    
    /* plik dane_adresowe.php - usuniecie konta */
    
    public static function FreshmailDaneAdresoweUsuniecie() {

        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);     
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                 $info = $sql->fetch_assoc();              
                 //
                 $freshMail = new FreshMail();
                 $freshMail->UsunSubskrybenta($info['subscribers_email_address']);
                 unset($freshMail);
                 //
                 unset($info);
                 //
             }
             
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);             
             
        } 

    }    
    
    /* plik inne/do_rejestracji.php */
    
    public static function FreshmailDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) && isset($TablicaPost['biuletyn']) ) {
             //
             // doda maila do grupy domyslnej
             $freshMail = new FreshMail();
             
             $emailKlient = (($plik == 'do_rejestracji' ) ? $filtr->process($TablicaPost['email']) : $filtr->process($TablicaPost['email_nowy']));
             
             $freshMail->ZapiszSubskrybenta( $emailKlient, 1, INTEGRACJA_DOMYSLNA_LISTA );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $freshMail->ZapiszSubskrybenta( $emailKlient, 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($freshMail, $emailKlient);
             //
        }

    }      
    
    /* plik inne/do_newslettera.php oraz newsletter.php - usuniecie */
    
    public static function FreshmailDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $freshMail = new FreshMail();
             $freshMail->UsunSubskrybenta( $filtr->process($mail) );                         
             unset($freshMail);
             //
        }    

    } 
        
    /* plik inne/do_newslettera.php oraz newsletter.php - dodanie */
    
    public static function FreshmailDoNewsletteraDodanie( $mail = '', $rejestracja = true ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $freshMail = new FreshMail();
             $freshMail->ZapiszSubskrybenta( $filtr->process($mail), 1, INTEGRACJA_DOMYSLNA_LISTA );             
             //
             if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' && $rejestracja == true ) {
                  //
                  $freshMail->ZapiszSubskrybenta( $filtr->process($mail), 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                  //
             }                                 
             //
             unset($freshMail);
             //
        }        
    
    }      
    
    /* plik inne/do_zamowienie_realizacja.php */
    
    public static function FreshmailDoZamowienieRealizacja() {
      
        global $filtr;       

        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_FRESHMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            if ( INTEGRACJA_FRESHMAIL_WLACZONY_PRODUKTY == 'tak' || INTEGRACJA_FRESHMAIL_WLACZONY_KUPUJACY == 'tak' ) {
                 //
                 $EmailDoNewslettera = $filtr->process($_SESSION['customer_email']);
                 //
                 // sprawdzi czy klient jest zapisany do newslettera
                 $zapytanie = "SELECT subscribers_email_address FROM subscribers WHERE subscribers_email_address = '" . $EmailDoNewslettera . "' and customers_newsletter = '1'";
                 $sql = $GLOBALS['db']->open_query($zapytanie); 
                
                 if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {  
                     //
                     $freshMail = new FreshMail();
                     //    
                     // dodawanie do list z zakupionymi produktami
                     if ( INTEGRACJA_FRESHMAIL_WLACZONY_PRODUKTY == 'tak' ) {
                          //
                          foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                             //
                             $freshMail->ZapiszSubskrybenta( $EmailDoNewslettera, 1, INTEGRACJA_FRESHMAIL_PRODUKTY_PREFIX . ' ' . Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );                           
                             //               
                          }
                          //
                     }
                     // 
                     // dodawanie do listy klientow ktorzy zrobili zakupy
                     if ( INTEGRACJA_FRESHMAIL_WLACZONY_KUPUJACY == 'tak' ) {
                          //
                          $freshMail->ZapiszSubskrybenta( $EmailDoNewslettera, 1, INTEGRACJA_FRESHMAIL_KUPUJACY_PREFIX ); 
                          //
                     }
                     //
                     unset($freshMail); 
                     //
                 }
                 //
                 $GLOBALS['db']->close_query($sql);
                 unset($zapytanie);    
                 //
                 unset($EmailDoNewslettera);
                 //
            }
            //
        }
        
    }    

    // ========== integracja z MailerLite
    
    /* plik aktywacja_konta.php */
    
    public static function MailerLiteAktywacjaKonta( $info_klient ) {

        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) && $info_klient['customers_newsletter'] == 1 ) {
             //
             // doda maila do grupy domyslnej
             $mailerLite = new MailerLite();
             $mailerLite->ZapiszSubskrybenta( $info_klient['customers_email_address'], INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $mailerLite->ZapiszSubskrybenta( $info_klient['customers_email_address'], INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($mailerLite);
             //
        }
        
    }    
    
    /* plik dane_adresowe.php */
    
    public static function MailerLiteDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $mailerLite = new MailerLite();
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                 //
                 // doda maila do grupy domyslnej
                 $mailerLite->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );
                 //
                 // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
                 if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' ) {
                      //
                      $mailerLite->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                      //
                 }
                 //
             } else {
                 //
                 // usunie z list
                 $mailerLite->UsunSubskrybenta( $filtr->process($TablicaPost['email']) );
                 //
             }
             //
             unset($mailerLite);
             //
        }  

    }    
    
    /* plik dane_adresowe.php - usuniecie konta */
    
    public static function MailerLiteDaneAdresoweUsuniecie() {

        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);     
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                 $info = $sql->fetch_assoc();              
                 //
                 $mailerLite = new MailerLite();
                 $mailerLite->UsunSubskrybenta($info['subscribers_email_address']);
                 unset($mailerLite);
                 //
                 unset($info);
                 //
             }
             
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);             
             
        } 

    }    
    
    /* plik inne/do_rejestracji.php */
    
    public static function MailerLiteDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) && isset($TablicaPost['biuletyn']) ) {
             //
             // doda maila do grupy domyslnej
             $mailerLite = new MailerLite();
             
             $emailKlient = (($plik == 'do_rejestracji' ) ? $filtr->process($TablicaPost['email']) : $filtr->process($TablicaPost['email_nowy']));
             
             $mailerLite->ZapiszSubskrybenta( $emailKlient, INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $mailerLite->ZapiszSubskrybenta( $emailKlient, INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($mailerLite, $emailKlient);
             //
        }

    }      
    
    /* plik inne/do_newslettera.php oraz newsletter.php - usuniecie */
    
    public static function MailerLiteDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $mailerLite = new MailerLite();
             $mailerLite->UsunSubskrybenta( $filtr->process($mail) );                         
             unset($mailerLite);
             //
        }    

    } 
        
    /* plik inne/do_newslettera.php oraz newsletter.php - dodanie */
    
    public static function MailerLiteDoNewsletteraDodanie( $mail = '', $rejestracja = true ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $mailerLite = new MailerLite();
             $mailerLite->ZapiszSubskrybenta( $filtr->process($mail), INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );             
             //
             if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' && $rejestracja == true ) {
                  //
                  $mailerLite->ZapiszSubskrybenta( $filtr->process($mail), INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                  //
             }                                 
             //
             unset($mailerLite);
             //
        }        
    
    }      
    
    /* plik inne/do_zamowienie_realizacja.php */
    
    public static function MailerLiteDoZamowienieRealizacja() {
      
        global $filtr;       

        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILERLITE', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            if ( INTEGRACJA_MAILERLITE_WLACZONY_PRODUKTY == 'tak' || INTEGRACJA_MAILERLITE_WLACZONY_KUPUJACY == 'tak' ) {
                 //
                 $EmailDoNewslettera = $filtr->process($_SESSION['customer_email']);
                 //
                 // sprawdzi czy klient jest zapisany do newslettera
                 $zapytanie = "SELECT subscribers_email_address FROM subscribers WHERE subscribers_email_address = '" . $EmailDoNewslettera . "' and customers_newsletter = '1'";
                 $sql = $GLOBALS['db']->open_query($zapytanie); 
                
                 if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {  
                     //
                     $mailerLite = new MailerLite();
                     //    
                     // dodawanie do list z zakupionymi produktami
                     if ( INTEGRACJA_MAILERLITE_WLACZONY_PRODUKTY == 'tak' ) {
                          //
                          foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                             //
                             $mailerLite->ZapiszSubskrybenta( $EmailDoNewslettera, INTEGRACJA_MAILERLITE_PRODUKTY_PREFIX . ' ' . Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );                           
                             //               
                          }
                          //
                     }
                     // 
                     // dodawanie do listy klientow ktorzy zrobili zakupy
                     if ( INTEGRACJA_MAILERLITE_WLACZONY_KUPUJACY == 'tak' ) {
                          //
                          $mailerLite->ZapiszSubskrybenta( $EmailDoNewslettera, INTEGRACJA_MAILERLITE_KUPUJACY_PREFIX ); 
                          //
                     }
                     //
                     unset($mailerLite); 
                     //
                 }
                 //
                 $GLOBALS['db']->close_query($sql);
                 unset($zapytanie);    
                 //
                 unset($EmailDoNewslettera);
                 //
            }
            //
        }
        
    }  
    
    // ========== integracja z Ecomail
    
    /* plik aktywacja_konta.php */
    
    public static function EcomailAktywacjaKonta( $info_klient ) {

        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) && $info_klient['customers_newsletter'] == 1 ) {
             //
             // doda maila do grupy domyslnej
             $ecomail = new Ecomail();
             $ecomail->ZapiszSubskrybenta( $info_klient['customers_email_address'], INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $ecomail->ZapiszSubskrybenta( $info_klient['customers_email_address'], INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($ecomail);
             //
        }
        
    }    
    
    /* plik dane_adresowe.php */
    
    public static function EcomailDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $ecomail = new Ecomail();
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                 //
                 // doda maila do grupy domyslnej
                 $ecomail->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );
                 //
                 // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
                 if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                      //
                      $ecomail->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                      //
                 }
                 //
             } else {
                 //
                 // usunie z list
                 $ecomail->UsunSubskrybenta( $filtr->process($TablicaPost['email']) );
                 //
             }
             //
             unset($ecomail);
             //
        }  

    }    
    
    /* plik dane_adresowe.php - usuniecie konta */
    
    public static function EcomailDaneAdresoweUsuniecie() {

        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);     
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                 $info = $sql->fetch_assoc();              
                 //
                 $ecomail = new Ecomail();
                 $ecomail->UsunSubskrybenta($info['subscribers_email_address']);
                 unset($ecomail);
                 //
                 unset($info);
                 //
             }
             
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);             
             
        } 

    }    
    
    /* plik inne/do_rejestracji.php */
    
    public static function EcomailDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) && isset($TablicaPost['biuletyn']) ) {
             //
             // doda maila do grupy domyslnej
             $ecomail = new Ecomail();
             
             $emailKlient = (($plik == 'do_rejestracji' ) ? $filtr->process($TablicaPost['email']) : $filtr->process($TablicaPost['email_nowy']));
             
             $ecomail->ZapiszSubskrybenta( $emailKlient, INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $ecomail->ZapiszSubskrybenta( $emailKlient, INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($ecomail, $emailKlient);
             //
        }

    }      
    
    /* plik inne/do_newslettera.php oraz newsletter.php - usuniecie */
    
    public static function EcomailDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $ecomail = new Ecomail();
             $ecomail->UsunSubskrybenta( $filtr->process($mail) );                         
             unset($ecomail);
             //
        }    

    } 
        
    /* plik inne/do_newslettera.php oraz newsletter.php - dodanie */
    
    public static function EcomailDoNewsletteraDodanie( $mail = '', $rejestracja = true ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $ecomail = new Ecomail();
             $ecomail->ZapiszSubskrybenta( $filtr->process($mail), INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );             
             //
             if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' && $rejestracja == true ) {
                  //
                  $ecomail->ZapiszSubskrybenta( $filtr->process($mail), INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                  //
             }                                 
             //
             unset($ecomail);
             //
        }        
    
    }      
    
    /* plik inne/do_zamowienie_realizacja.php */
    
    public static function EcomailDoZamowienieRealizacja() {
      
        global $filtr;       

        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_ECOMAIL', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            if ( INTEGRACJA_ECOMAIL_WLACZONY_PRODUKTY == 'tak' || INTEGRACJA_ECOMAIL_WLACZONY_KUPUJACY == 'tak' ) {
                 //
                 $EmailDoNewslettera = $filtr->process($_SESSION['customer_email']);
                 //
                 // sprawdzi czy klient jest zapisany do newslettera
                 $zapytanie = "SELECT subscribers_email_address FROM subscribers WHERE subscribers_email_address = '" . $EmailDoNewslettera . "' and customers_newsletter = '1'";
                 $sql = $GLOBALS['db']->open_query($zapytanie); 
                
                 if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {  
                     //
                     $ecomail = new Ecomail();
                     //    
                     // dodawanie do list z zakupionymi produktami
                     if ( INTEGRACJA_ECOMAIL_WLACZONY_PRODUKTY == 'tak' ) {
                          //
                          foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                             //
                             $ecomail->ZapiszSubskrybenta( $EmailDoNewslettera, INTEGRACJA_ECOMAIL_PRODUKTY_PREFIX . ' ' . Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );                           
                             //               
                          }
                          //
                     }
                     // 
                     // dodawanie do listy klientow ktorzy zrobili zakupy
                     if ( INTEGRACJA_ECOMAIL_WLACZONY_KUPUJACY == 'tak' ) {
                          //
                          $ecomail->ZapiszSubskrybenta( $EmailDoNewslettera, INTEGRACJA_ECOMAIL_KUPUJACY_PREFIX ); 
                          //
                     }
                     //
                     unset($ecomail); 
                     //
                 }
                 //
                 $GLOBALS['db']->close_query($sql);
                 unset($zapytanie);    
                 //
                 unset($EmailDoNewslettera);
                 //
            }
            //
        }
        
    }  
    
    // ========== integracja z Mailjet
    
    /* plik aktywacja_konta.php */
    
    public static function MailjetAktywacjaKonta( $info_klient ) {

        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) && $info_klient['customers_newsletter'] == 1 ) {
             //
             // doda maila do grupy domyslnej
             $mailjet = new Mailjet();
             $mailjet->ZapiszSubskrybenta( $info_klient['customers_email_address'], INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $mailjet->ZapiszSubskrybenta( $info_klient['customers_email_address'], INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                  //
             }
             //
             unset($mailjet);
             //
        }
        
    }    
    
    /* plik dane_adresowe.php */
    
    public static function MailjetDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $mailjet = new Mailjet();
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                 //
                 // doda maila do grupy domyslnej
                 $mailjet->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );
                 //
                 // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
                 if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' ) {
                      //
                      $mailjet->ZapiszSubskrybenta( $filtr->process($TablicaPost['email']), INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                      //
                 }
                 //
             } else {
                 //
                 // usunie z list
                 $mailjet->UsunSubskrybenta( $filtr->process($TablicaPost['email']) );
                 //
             }
             //
             unset($mailjet);
             //
        }  

    }    
    
    /* plik dane_adresowe.php - usuniecie konta */
    
    public static function MailjetDaneAdresoweUsuniecie() {

        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);     
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                 $info = $sql->fetch_assoc();              
                 //
                 $mailjet = new Mailjet();
                 $mailjet->UsunSubskrybenta($info['subscribers_email_address']);
                 unset($mailjet);
                 //
                 unset($info);
                 //
             }
             
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);             
             
        } 

    }    
    
    /* plik inne/do_rejestracji.php */
    
    public static function MailjetDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) && isset($TablicaPost['biuletyn']) ) {
             //
             // doda maila do grupy domyslnej
             $mailjet = new Mailjet();
             
             $emailKlient = (($plik == 'do_rejestracji' ) ? $filtr->process($TablicaPost['email']) : $filtr->process($TablicaPost['email_nowy']));
             
             $mailjet->ZapiszSubskrybenta( $emailKlient, INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $mailjet->ZapiszSubskrybenta( $emailKlient, INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                  //
             }
             //
             unset($mailjet, $emailKlient);
             //
        }

    }      
    
    /* plik inne/do_newslettera.php oraz newsletter.php - usuniecie */
    
    public static function MailjetDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $mailjet = new Mailjet();
             $mailjet->UsunSubskrybenta( $filtr->process($mail) );                         
             unset($mailjet);
             //
        }    

    } 
        
    /* plik inne/do_newslettera.php oraz newsletter.php - dodanie */
    
    public static function MailjetDoNewsletteraDodanie( $mail = '', $rejestracja = true ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $mailjet = new Mailjet();
             $mailjet->ZapiszSubskrybenta( $filtr->process($mail), INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );             
             //
             if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' && $rejestracja == true ) {
                  //
                  $mailjet->ZapiszSubskrybenta( $filtr->process($mail), INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                  //
             }                                 
             //
             unset($mailjet);
             //
        }        
    
    }      
    
    /* plik inne/do_zamowienie_realizacja.php */
    
    public static function MailjetDoZamowienieRealizacja() {
      
        global $filtr;       

        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_MAILJET', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            if ( INTEGRACJA_MAILJET_WLACZONY_PRODUKTY == 'tak' || INTEGRACJA_MAILJET_WLACZONY_KUPUJACY == 'tak' ) {
                 //
                 $EmailDoNewslettera = $filtr->process($_SESSION['customer_email']);
                 //
                 // sprawdzi czy klient jest zapisany do newslettera
                 $zapytanie = "SELECT subscribers_email_address FROM subscribers WHERE subscribers_email_address = '" . $EmailDoNewslettera . "' and customers_newsletter = '1'";
                 $sql = $GLOBALS['db']->open_query($zapytanie); 
                
                 if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {  
                     //
                     $mailjet = new Mailjet();
                     //    
                     // dodawanie do list z zakupionymi produktami
                     if ( INTEGRACJA_MAILJET_WLACZONY_PRODUKTY == 'tak' ) {
                          //
                          foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                             //
                             $mailjet->ZapiszSubskrybenta( $EmailDoNewslettera, INTEGRACJA_MAILJET_PRODUKTY_PREFIX . ' ' . Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ), true );                           
                             //               
                          }
                          //
                     }
                     // 
                     // dodawanie do listy klientow ktorzy zrobili zakupy
                     if ( INTEGRACJA_MAILJET_WLACZONY_KUPUJACY == 'tak' ) {
                          //
                          $mailjet->ZapiszSubskrybenta( $EmailDoNewslettera, INTEGRACJA_MAILJET_KUPUJACY_PREFIX, true ); 
                          //
                     }
                     //
                     unset($mailjet); 
                     //
                 }
                 //
                 $GLOBALS['db']->close_query($sql);
                 unset($zapytanie);    
                 //
                 unset($EmailDoNewslettera);
                 //
            }
            //
        }
        
    }  
    
    // ========== integracja z Getall
    
    /* plik aktywacja_konta.php */
    
    public static function GetallAktywacjaKonta( $info_klient ) {

        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) && $info_klient['customers_newsletter'] == 1 ) {
             //
             // doda maila do grupy domyslnej
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             $getall->DodajSubskrybenta( $info_klient['customers_email_address'], $info_klient['customers_firstname'], INTEGRACJA_GETALL_DOMYSLNA_LISTA );  
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $getall->DodajSubskrybenta( $info_klient['customers_email_address'], $info_klient['customers_firstname'], INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($getall);
             //
        }    

    }  

    /* plik dane_adresowe.php */
    
    public static function GetallDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;
      
        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             //
             if ( isset($TablicaPost['biuletyn']) ) {
                 //
                 // doda maila do grupy domyslnej
                 $getall->DodajSubskrybenta( $filtr->process($TablicaPost['email']), $filtr->process($TablicaPost['imie']), INTEGRACJA_GETALL_DOMYSLNA_LISTA );  
                 //
                 // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
                 if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' ) {
                      //
                      $getall->DodajSubskrybenta( $filtr->process($TablicaPost['email']), $filtr->process($TablicaPost['imie']), INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                      //
                 }  
                 //
             } else {
                 //
                 // usunie z list
                 $getall->UsunSubskrybenta( $filtr->process($TablicaPost['email']) );     
                 //
             }
             //
             unset($getall);
             //
        }  

    }    
    
    /* plik dane_adresowe.php - usuniecie konta */
    
    public static function GetallDaneAdresoweUsuniecie() {

        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);     
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                 $info = $sql->fetch_assoc();              
                 //
                 $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
                 $getall->UsunSubskrybenta($info['subscribers_email_address']);
                 unset($getall);
                 //
                 unset($info);
                 //
             }
             
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);             
             
        }  

    }    

    /* plik inne/do_rejestracji.php */
    
    public static function GetallDoRejestracji( $TablicaPost = array(), $plik = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) && isset($TablicaPost['biuletyn']) ) {
             //
             // doda maila do grupy domyslnej
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             
             $emailKlient = (($plik == 'do_rejestracji' ) ? $filtr->process($TablicaPost['email']) : $filtr->process($TablicaPost['email_nowy']));
             
             $getall->DodajSubskrybenta( $emailKlient, $filtr->process($TablicaPost['imie']), INTEGRACJA_GETALL_DOMYSLNA_LISTA );  
             //
             // jezeli jest wlaczona opcja dodatkowej listy dla rejestracji to doda do drugiej grupy
             if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $getall->DodajSubskrybenta( $emailKlient, $filtr->process($TablicaPost['imie']), INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                  //
             }
             //
             unset($getall, $emailKlient);
             //
        }  

    }      
    
    /* plik inne/do_newslettera.php - dodanie */
    
    public static function GetallDoNewsletteraUsuniecie( $mail = '' ) {
      
        global $filtr;

        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             $getall->UsunSubskrybenta( $filtr->process($mail) );                         
             unset($getall);
             //
        }  

    }      
    
    /* plik inne/do_newslettera.php oraz newsletter.php - dodanie */
    
    public static function GetallDoNewsletteraDodanie( $mail = '', $rejestracja = true ) {
      
        global $filtr;    
    
        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             $getall->DodajSubskrybenta( $filtr->process($mail), '', INTEGRACJA_GETALL_DOMYSLNA_LISTA );                      
             unset($getall);
             //
             if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' && $rejestracja == true ) {
                  //
                  $getall->DodajSubskrybenta( $filtr->process($mail), '', INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                  //
             }                                 
             //
        }           
    
    }        

    /* plik inne/do_zamowienie_realizacja.php */
    
    public static function GetallZamowienieRealizacja() {

       global $filtr;    

       if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_GETALL', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            if ( INTEGRACJA_GETALL_WLACZONY_PRODUKTY == 'tak' || INTEGRACJA_GETALL_WLACZONY_KUPUJACY == 'tak' ) {
                 //
                 $EmailDoNewslettera = $filtr->process($_SESSION['customer_email']);
                 //
                 // sprawdzi czy klient jest zapisany do newslettera
                 $zapytanie = "SELECT subscribers_email_address FROM subscribers WHERE subscribers_email_address = '" . $EmailDoNewslettera . "' and customers_newsletter = '1'";
                 $sql = $GLOBALS['db']->open_query($zapytanie); 
                
                 if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {  
                     //
                     $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
                     //    
                     // dodawanie do list z zakupionymi produktami
                     if ( INTEGRACJA_GETALL_WLACZONY_PRODUKTY == 'tak' ) {
                          //
                          $getall->DodajSubskrybenta( $EmailDoNewslettera, '', INTEGRACJA_GETALL_PRODUKTY_PREFIX );                           
                          //
                     }
                     // 
                     // dodawanie do listy klientow ktorzy zrobili zakupy
                     if ( INTEGRACJA_GETALL_WLACZONY_KUPUJACY == 'tak' ) {
                          //
                          $getall->DodajSubskrybenta( $EmailDoNewslettera, '', INTEGRACJA_GETALL_KUPUJACY_PREFIX ); 
                          //
                     }
                     //
                     unset($getall); 
                     //
                 }
                 //
                 unset($EmailDoNewslettera);
                 //
            }
            //
        }   

    }      
    
    
    // ========== integracja z WebePartners
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function WebePartnersZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
        
        if ( INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY == 'tak' && INTEGRACJA_WEBEPARTNERS_MID != '' && !in_array('COOKIE_INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA', $GLOBALS['wykluczeniaIntegracje']) ) {

            $id_produktu    = '';
            $ilosc_produktu = '';
            $cena_produktu  = '';
            $wartosc_zamowienia = 0;
            $wartosc_wysylki = 0;

            foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
              if ($podsumowanie['klasa'] == 'ot_total') {
                $wartosc_zamowienia = $podsumowanie['wartosc'];
              } elseif ($podsumowanie['klasa'] == 'ot_shipping') {
                $wartosc_wysylki = $podsumowanie['wartosc'];
              }
            }
            $wartosc_przekazana = $wartosc_zamowienia - $wartosc_wysylki;

            foreach ( $zamowienie->produkty as $produkt ) {
                $id_produktu    .= $produkt['id_produktu'] . ':';
                $ilosc_produktu .= $produkt['ilosc'] . ':';
                $cena_produktu  .= $produkt['cena_koncowa_brutto'] . ':';
            }

            $wynik .= "\n";
            $wynik .= "<script type=\"text/javascript\">\n";

            $wynik .= "var webeOrder = {\n";

            $wynik .= "\"mid\": ".INTEGRACJA_WEBEPARTNERS_MID.",\n";
            $wynik .= "\"refer\": \"".$_SESSION['zamowienie_id']."\",\n";
            $wynik .= "\"pid\": \"".substr((string)$id_produktu,0,-1)."\",\n";
            $wynik .= "\"q\": \"".substr((string)$ilosc_produktu,0,-1)."\",\n";
            $wynik .= "\"price\": \"".substr((string)$cena_produktu,0,-1)."\",\n";
            $wynik .= "\"sum\": ".number_format($wartosc_przekazana, 2, '.', '')."\n";

            $wynik .= "};\n";

            $wynik .= "</script>\n";

            $wynik .= "<script type=\"text/javascript\" src=\"https://webetech.pl/js/webeconfirm.js\"></script>"."\n";
            $wynik .= "<script type=\"text/javascript\" src=\"https://webep1.com/js/webeorder.js\"></script>"."\n";

            unset($id_produktu, $ilosc_produktu ,$cena_produktu, $wartosc_zamowienia, $wartosc_wysylki, $wartosc_przekazana);
            
        }        

        return $wynik;
        
    }
    
    // ========== integracja z shopeneo.network
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function ShopeneoNetworkZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_SHOPENEO_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SHOPENEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wynik = "\n<span id=\"shopeneo-conversion\" data-order=\"" . $zamowienie->info['id_zamowienia'] . "\" data-value=\"" . $zamowienie->info['wartosc_zamowienia_val'] . "\"></span>\n";

        }   

        return $wynik;
        
    }    

    // ========== integracja z programem Zaufane Opinie CENEO
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function ZaufaneOpinieCeneoZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_CENEO_OPINIE_WLACZONY == 'tak' && INTEGRACJA_CENEO_OPINIE_ID != '' && !in_array('COOKIE_INTEGRACJA_CENEO', $GLOBALS['wykluczeniaIntegracje']) ) {

            if (!empty($zamowienie->klient['adres_email'])) {
                $string_ceneo = '';
                $wartoscZamowienia = 0;
                $products_array_ceneo = array();
                $wartosc_zamowienia = 0;


                foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
                    //
                    if ($podsumowanie['klasa'] == 'ot_total') {
                        $wartosc_zamowienia = $podsumowanie['wartosc'];
                    }
                    //
                }  

                foreach ( $zamowienie->produkty as $produkt ) {

                    $wartosc_produktu = $produkt['cena_koncowa_brutto'] * $produkt['ilosc'];
                    $wartoscZamowienia += $wartosc_produktu;

                    if ( $produkt['ilosc'] > 1 ) {
                        for ($y = 0, $z = $produkt['ilosc']; $y < $z; $y++) {
                            $string_ceneo .= '#'.$produkt['id_produktu'];
                        }
                    } else {
                        $string_ceneo .= '#'.$produkt['id_produktu'];
                    }

                    $products_array_ceneo[] = "\n{\n" . 
                                               "id: '" . $produkt['id_produktu'] . "',\n" . 
                                               "price: " . number_format($produkt['cena_koncowa_brutto'], 2, '.', '') . ",\n" . 
                                               "quantity: " . $produkt['ilosc'] . ",\n" . 
                                               "currency: '" . $_SESSION['domyslnaWaluta']['kod'] . "'\n}";
                }

                $wynik .= "\n";
                
                $wynik .= "<script>\n";
                $wynik .= "_ceneo('transaction', {\n";
                $wynik .= "client_email: '".(($_SESSION['zgodaNaPrzekazanieDanych'] == '1') ? $zamowienie->klient['adres_email'] : '' )."',\n";
                $wynik .= "order_id: '" . $_SESSION['zamowienie_id'] . "',\n";

                $wynik .= "shop_products: [";
                $wynik .= implode(',', (array)$products_array_ceneo) . "],\n";

                $wynik .= "work_days_to_send_questionnaire: " . INTEGRACJA_CENEO_OPINIE_CZAS . ",\n";
                $wynik .= "amount: ".number_format($wartosc_zamowienia, 2, '.', '')."\n";
                $wynik .= "});\n";
                $wynik .= "</script>\n";

                unset($string_ceneo, $wartoscZamowienia, $wartosc_zamowienia);
            }
            
        } 

        return $wynik;
        
    }     
    
    // ========== integracja z programem okazje.info
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function OkazjeInfoZamowieniePodsumowanie( $zamowienie ) {
       
        if ( INTEGRACJA_OKAZJE_WLACZONY == 'tak' && INTEGRACJA_OKAZJE_ID != '' && $_SESSION['zgodaNaPrzekazanieDanych'] == '1' ) {

            $wartosc_zamowienia = 0;
            $products_array_okazje = array();

            foreach ( $zamowienie->produkty as $produkt ) {
              
                $products_array_okazje[] = array($produkt['id_produktu'],$produkt['ilosc']);
                $wartosc_zamowienia += ($produkt['cena_koncowa_brutto'] * $produkt['ilosc']);
                
            }
            $dane = array(
                   'mail' => $zamowienie->klient['adres_email'],
                   'orderId' => $_SESSION['zamowienie_id'],
                   'orderAmount' => $wartosc_zamowienia,
                   'products' => $products_array_okazje
            );

            include_once 'inne/oiTracker.php';
            $oiTracker = new oiTracker(INTEGRACJA_OKAZJE_ID);
            $r = $oiTracker->eOrder($dane);

            unset($dane, $products_array_okazje, $wartosc_zamowienia, $r);
            
        }
        
    }    
    
    // ========== integracja z Zaufane opinie - OPINEO
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function ZaufaneOpinieOpineoZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_OPINEO_OPINIE_WLACZONY == 'tak' && INTEGRACJA_OPINEO_OPINIE_LOGIN != '' && $_SESSION['zgodaNaPrzekazanieDanych'] == '1' ) {

            $products_array_opineo = array();

            foreach ( $zamowienie->produkty as $produkt ) {

                $products_array_opineo[] = "{\n" . 
                                           "shopInternalProductId: " . $produkt['id_produktu'] . ",\n" . 
                                           "brand: '" . $produkt['producent'] . "',\n" . 
                                           "model: '" . $produkt['model'] . "',\n" . 
                                           "ean: '" . $produkt['ean'] . "',\n" . 
                                           "partNumber: '" . $produkt['kod_producenta'] . "'}\n";
            }

            $wynik .= "<script src=\"https://developer.opineo.pl/sdk.js\"></script>\n";
            $wynik .= "<script>\n";
            $wynik .= "opineoSDK.credibleOpinion.setCompanyPublicIdentifier('" . INTEGRACJA_OPINEO_OPINIE_LOGIN . "');\n";
            $wynik .= "opineoSDK.credibleOpinion.sendPurchase({\n";
            $wynik .= "email: '" . $zamowienie->klient['adres_email'] . "',\n";
            $wynik .= "orderNumber: '" . $_SESSION['zamowienie_id'] . "',\n";
            $wynik .= "sendAfterDays: " . INTEGRACJA_OPINEO_OPINIE_CZAS . ",\n";
            $wynik .= "products: [\n";
            $wynik .= implode(',', (array)$products_array_opineo) . "]\n";
            $wynik .= "}, function onSuccess() {\n";
            $wynik .= "// Transakcja została przyjęta przez Opineo\n";
            $wynik .= "}, function onError(error) {\n";
            $wynik .= "// console.error('Nie udało się wysłać informacji o transakcji do Opineo.');\n";
            $wynik .= "});\n";
            $wynik .= "</script>\n";

            unset($products_array_opineo);
            
        }

        return $wynik;
        
    }       

    // ========== integracja z salesmedia.pl
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function SalesmediaZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_SALESMEDIA_WLACZONY == 'tak' && INTEGRACJA_SALESMEDIA_ID != '' && !in_array('COOKIE_INTEGRACJA_SALESMEDIA', $GLOBALS['wykluczeniaIntegracje']) ) {

            $wartosc_zamowienia = 0;

            foreach ( $zamowienie->produkty as $produkt ) {
                $wartosc_zamowienia += ($produkt['cena_koncowa_brutto'] * $produkt['ilosc']);
            }

            $wynik .= "\n";
            $wynik .= "<iframe src=\"http://go.salesmedia.pl/aff_l?offer_id=" . INTEGRACJA_SALESMEDIA_ID . "&adv_sub=" . $_SESSION['zamowienie_id'] . "&amount=" . $wartosc_zamowienia . "\" scrolling=\"no\" frameborder=\"0\" width=\"1\" height=\"1\"></iframe>\n";

            unset($wartosc_zamowienia);
            
        }  

        return $wynik;
        
    }    

    // ========== integracja z programem Opinie konsumenckie Google
    
    /* plik zamowienie_podsumowanie.php */
    
    public static function OpinieGoogleZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_GOOGLE_OPINIE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_OPINIE_MERCHANT_ID != '' && $_SESSION['zgodaNaPrzekazanieDanych'] == '1' ) {

            $wynik .= "\n";
            $wynik .= '<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>' . "\n";
            
            $wynik .= '<script>' . "\n";
            $wynik .= '  window.renderOptIn = function() {' . "\n";
            $wynik .= '    window.gapi.load(\'surveyoptin\', function() {' . "\n";
            $wynik .= '      window.gapi.surveyoptin.render(' . "\n";
            $wynik .= '        {' . "\n";
            $wynik .= '          "merchant_id":"' . INTEGRACJA_GOOGLE_OPINIE_MERCHANT_ID . '",' . "\n";
            $wynik .= '          "order_id": "' . $zamowienie->info['id_zamowienia'] . '",' . "\n";
            $wynik .= '          "email": "' . $zamowienie->klient['adres_email'] . '",' . "\n";
            
            $zapytanieKraj = "SELECT c.countries_iso_code_2, cd.countries_name FROM countries c, countries_description cd WHERE c.countries_id = cd.countries_id AND cd.countries_name = '" . $zamowienie->klient['kraj'] . "'";
            $sqlKraj = $GLOBALS['db']->open_query($zapytanieKraj);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlKraj) > 0) {
                //
                $wynikKraj = $sqlKraj->fetch_assoc();
                //
                $wynik .= '          "delivery_country": "' . $wynikKraj['countries_iso_code_2'] . '",' . "\n";
                //
                unset($wynikKraj);
                //
            }
            //
            $GLOBALS['db']->close_query($sqlKraj);
            unset($zapytanieKraj);     
            
            
            $wynik .= '          "estimated_delivery_date": "' . date('Y-m-d', (time() + (86400 * (int)INTEGRACJA_GOOGLE_OPINIE_CZAS))) . '",' . "\n";
            
            $produkty_ean = array();
            foreach ( $zamowienie->produkty as $produkt ) {
                if ( $produkt['ean'] != '' ) {
                     $produkty_ean[] = '{"gtin":"' . $produkt['ean'] . '"}';
                }
            }    
            
            if ( count($produkty_ean) > 0 ) {
                 $wynik .= '          "products": [' . implode(',', (array)$produkty_ean) . ']' . "\n";
            }
            
            unset($produkty_ean);
            
            $wynik .= '        });' . "\n"; 
            $wynik .= '     });' . "\n";
            $wynik .= '  }' . "\n";
            $wynik .= '</script>' . "\n";   

        }  

        return $wynik;
        
    }        
    
    // ========== integracja z easyprotect.pl
    
    /* plik koszyk.php */
    
    public static function EasyProtectTabela( $wartosc = 0 ) {
      
        $wynik = array();
      
        if ( INTEGRACJA_EASYPROTECT_WLACZONY == 'tak' && INTEGRACJA_EASYPROTECT_API != '' ) {
      
            if ( !isset($_SESSION['progi_easyprotect']) ) {

                 $WynikCache = $GLOBALS['cache']->odczytaj('ProgiEasyProtect', CACHE_INNE, true);
                 
                 if ( !$WynikCache ) {
                   
                     $wynikProgi = array();

                     $Progi = array(699, 1099, 1599, 1899, 2299, 2699, 3199, 3699, 4299, 4999, 5999, 6999, 7999, 9999, 11999, 13999, 15999, 17999, 19999, 21999);
                  
                     foreach ( $Progi as $x ) {  

                        $url = 'https://api.easyprotect.pl/certificate/available-policies?price=' . ($x * 100);
                        $headers = [
                                'Content-Type: application/json',
                                'Authorization: ' . INTEGRACJA_EASYPROTECT_API
                        ];

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 55);

                        $wynikCurl = curl_exec($ch);
                        $wynikTablica = @json_decode($wynikCurl, true);
                        
                        if ( count($wynikTablica) > 0 ) {
                             //
                             foreach ( $wynikTablica as $Tmp ) {
                                 //
                                 if ( isset($Tmp['policy']) ) {
                                      //
                                      $wynikProgi[ $x ][ $Tmp['policy'] ] = $Tmp['price']['amount'] / 100;
                                      //
                                 }
                                 //
                             }
                             //
                        }
                        
                        unset($wynikCurl, $wynikTablica);

                     }
                     
                     $GLOBALS['cache']->zapisz('ProgiEasyProtect', $wynikProgi, CACHE_INNE, true);
                     
                 } else {
                   
                     $wynikProgi = $WynikCache;
                   
                 }

                 $_SESSION['progi_easyprotect'] = $wynikProgi;

            } else {
            
                 $wynikProgi = $_SESSION['progi_easyprotect'];
                 
            }

            $wynik = array(); 

            if ( count($wynikProgi) == 0 ) {
              
                 $wynik = array(); 

                 $url = 'https://api.easyprotect.pl/certificate/available-policies?price=' . ((int)$wartosc * 100);
                 $headers = [
                         'Content-Type: application/json',
                         'Authorization: ' . INTEGRACJA_EASYPROTECT_API
                 ];

                 $ch = curl_init();
                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 curl_setopt($ch, CURLOPT_TIMEOUT, 55);
     
                 $wynikCurl = curl_exec($ch);
                 $wynikTablica = @json_decode($wynikCurl, true);
                 
                 foreach ( $wynikTablica as $Tmp ) {
                      //
                      if ( isset($Tmp['policy']) ) {
                           $wynik[ $Tmp['policy'] ] = (float)$Tmp['price']['amount'] / 100;
                      }
                      //
                 }

            } else {

                 foreach ($wynikProgi as $Prog => $Tmp ) {

                      if ( $wartosc <= $Prog ) {
                           $wynik = $Tmp;
                           break;
                      }
                  
                 }             
                 
            }
            
        }
  
        return $wynik;
        
    }    
    
    public static function EasyProtectKoszyk( $wartosc ) {
      
        $wynik = array();
      
        if ( INTEGRACJA_EASYPROTECT_WLACZONY == 'tak' && INTEGRACJA_EASYPROTECT_API != '' ) {
      
            $KosztyCache = IntegracjeZewnetrzne::EasyProtectTabela( $wartosc );
            
            if ( count($KosztyCache) > 0 ) {
              
                 $wynik = $KosztyCache;
                 
            } else {
          
                 $wynik = array();   
                        
                 $url = 'https://api.easyprotect.pl/certificate/available-policies?price=' . ((int)$wartosc * 100);
                 $headers = [
                         'Content-Type: application/json',
                         'Authorization: ' . INTEGRACJA_EASYPROTECT_API
                 ];
     
                 $ch = curl_init();
                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 curl_setopt($ch, CURLOPT_TIMEOUT, 55);
     
                 $wynikCurl = curl_exec($ch);
                 $wynikTablica = @json_decode($wynikCurl, true);
                
                 foreach ( $wynikTablica as $Tmp ) {
                     //
                     if ( isset($Tmp['policy']) ) {
                          $wynik[ $Tmp['policy'] ] = (float)$Tmp['price']['amount'] / 100;
                     }
                     //
                 }
                 
            }
            
        }

        return $wynik;
        
    }   

    public static function EasyProtectKoszykKwota( $wartosc, $miesiace = 12 ) {
      
        $wynik = array();
      
        if ( INTEGRACJA_EASYPROTECT_WLACZONY == 'tak' && INTEGRACJA_EASYPROTECT_API != '' ) {
      
            $KosztyCache = IntegracjeZewnetrzne::EasyProtectTabela( $wartosc );
            
            $wynik = array();
            
            if ( count($KosztyCache) > 0 ) {
              
                 $wynikTmp = $KosztyCache;
                 
                 if ( isset($wynikTmp[ $miesiace ]) ) {
                      
                      $wynik = $wynikTmp[ $miesiace ];
                      
                 }
                 
            } else {
              
                $wynik = array();   
                    
                $url = 'https://api.easyprotect.pl/certificate/calculate-price?price=' . ((int)$wartosc * 100) . '&policy=' . $miesiace;

                $headers = [
                        'Content-Type: application/json',
                        'Authorization: ' . INTEGRACJA_EASYPROTECT_API
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 55);

                $wynikCurl = curl_exec($ch);
                $wynikTablica = @json_decode($wynikCurl, true);
                
                $wynik[ $miesiace ] = (float)$wynikTablica['amount'] / 100;
                
            }
            
        }
        
        return $wynik;
        
    }     
    
    public static function KodZewnetrznyPodsumowanieZamowienia( $zamowienie, $ciag_kodu = '' ) {
      
        $Podziel = explode(' ', (string)$zamowienie->klient['nazwa']);
        $ImieZamowienie = '';
        $NazwiskoZamowienie = array();
        //
        for ($x = 0; $x < count($Podziel); $x++) {
            //
            if ( $x == 0 ) {
                $ImieZamowienie = $Podziel[0];
            } else {
                $NazwiskoZamowienie[] = $Podziel[$x];
            }
            //
        }
        //
        
        // numer zamowienia        
        $ciag_kodu = str_replace('{NUMER_ZAMOWIENIA}', (string)$zamowienie->info['id_zamowienia'], (string)$ciag_kodu);

        // email kupujacego      
        $ciag_kodu = str_replace('{EMAIL_KUPUJACEGO}', (string)$zamowienie->klient['adres_email'], (string)$ciag_kodu);
        
        // imie kupujacego      
        $ciag_kodu = str_replace('{IMIE_KUPUJACEGO}', (string)$ImieZamowienie, (string)$ciag_kodu);
        
        // nazwisko kupujacego      
        $ciag_kodu = str_replace('{NAZWISKO_KUPUJACEGO}', implode(' ', (array)$NazwiskoZamowienie), (string)$ciag_kodu);
        
        // data zamowienia   
        $ciag_kodu = str_replace('{DATA_ZAMOWIENIA}', date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])), (string)$ciag_kodu);
        
        // lista produktow 
        $id = array();
        
        foreach ( $zamowienie->produkty as $produkt ) {
          
            $id[] = $produkt['id_produktu'];
            
        }
        
        // lista produktow przecinek
        $ciag_kodu = str_replace('{LISTA_PRODUKTOW_ID_PRZECINEK}', implode(',', (array)$id), (string)$ciag_kodu);

        // lista produktow srednik
        $ciag_kodu = str_replace('{LISTA_PRODUKTOW_ID_SREDNIK}', implode(';', (array)$id), (string)$ciag_kodu);
        
        // wartosc zamowienia brutto
        $ciag_kodu = str_replace('{WARTOSC_ZAMOWIENIA}', number_format($zamowienie->info['wartosc_zamowienia_val'], 2, '.', ''), (string)$ciag_kodu);
        
        $wartosc_wysylki = 0;
        $kupon_kod = '';
        $kupon_wartosc = 0;
        
        foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
            //
            if ($podsumowanie['klasa'] == 'ot_shipping' || $podsumowanie['klasa'] == 'ot_payment') {
                $wartosc_wysylki += $podsumowanie['wartosc'];
            } elseif ($podsumowanie['klasa'] == 'ot_discount_coupon' ) {
                $kupon_kod = $podsumowanie['tytul'];
                $kupon_wartosc = $podsumowanie['wartosc'];
            }
            //
        }          

        // wartosc wysylki
        $ciag_kodu = str_replace('{KOSZT_PRZESYLKI}', number_format($wartosc_wysylki, 2, '.', ''), (string)$ciag_kodu);

        // kupon rabatowy nazwa
        $ciag_kodu = str_replace('{KUPON_RABATOWY_NAZWA}', str_replace(array("'",'"'), "", (string)$kupon_kod), $ciag_kodu);

        // kupon rabatowy wartosc
        $ciag_kodu = str_replace('{KUPON_RABATOWY_WARTOSC}', number_format($kupon_wartosc, 2, '.', ''), (string)$ciag_kodu);
        
        // waluta zamowienia
        $ciag_kodu = str_replace('{WALUTA_ZAMOWIENIA}', (string)$zamowienie->info['waluta'], (string)$ciag_kodu);

        // forma platnosci
        $ciag_kodu = str_replace('{FORMA_PLATNOSCI}', str_replace(array("'",'"'), "", (string)$zamowienie->info['metoda_platnosci']), (string)$ciag_kodu);

        // forma wysylki
        $ciag_kodu = str_replace('{FORMA_WYSYLKI}', str_replace(array("'",'"'), "", (string)$zamowienie->info['wysylka_modul']), (string)$ciag_kodu);

        // znacznik czasu
        $ciag_kodu = str_replace('{TIMESTAMP}', time(), (string)$ciag_kodu);

        // ID sesji
        $ciag_kodu = str_replace('{SESSION_ID}', session_id(), (string)$ciag_kodu);

        return $ciag_kodu;

    }

    // ========== integracja z Pinterest Tag
    
    /* plik start.php */
    
    public static function PinterestTagStart( $email = '' ) {
      
        global $filtr;

        $wynik = '';
        
        if ( INTEGRACJA_PINTEREST_TAG_WLACZONY == 'tak' && INTEGRACJA_PINTEREST_TAG_ID != '' && !in_array('COOKIE_INTEGRACJA_PINTEREST_TAG', $GLOBALS['wykluczeniaIntegracje']) ) {
          
            $ex = pathinfo($_SERVER['PHP_SELF']);
            
            if ( !isset($ex['extension']) ) {
                 //
                 $roz = explode('.', (string)$_SERVER['PHP_SELF']);
                 $ex['extension'] = $roz[ count($roz) - 1];
                 //
            }                

            $emailPinterest = '';
          
            if ( isset($_SESSION['customer_email']) ) {
                 $emailPinterest = $_SESSION['customer_email'];
            }          
            if ( isset($_SESSION['pinterest_mail']) && $emailPinterest == '' ) {
                 $emailPinterest = $_SESSION['pinterest_mail'];
            }
            if ( $email != '' ) {
                 $emailPinterest = $_SESSION['pinterest_mail'];
            }
            
            // email tymczasowy z newslettera
            if ( isset($_SESSION['pinterest_tmp']) && $_SESSION['pinterest_tmp'] != '' ) {
                 $emailPinterest = $_SESSION['pinterest_tmp'];
            }
                        
            
            if ( $emailPinterest != '' ) { 
                 $_SESSION['pinterest_mail'] = $emailPinterest;
            }
            
            $wynik .= "<!-- Pinterest Tag -->\n";
            $wynik .= "<script>\n";
            $wynik .= "!function(e){if(!window.pintrk){window.pintrk = function () {\n";
            $wynik .= "window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var\n";
            $wynik .= "  n=window.pintrk;n.queue=[],n.version=\"3.0\";var\n";
            $wynik .= "  t=document.createElement(\"script\");t.async=!0,t.src=e;var\n";
            $wynik .= "  r=document.getElementsByTagName(\"script\")[0];\n";
            $wynik .= "  r.parentNode.insertBefore(t,r)}}(\"https://s.pinimg.com/ct/core.js\");\n";
            $wynik .= "pintrk('load', '" . INTEGRACJA_PINTEREST_TAG_ID . "'" . (($emailPinterest != '') ? ", {em: '" . $emailPinterest . "'}" : '') . ");\n";
            $wynik .= "pintrk('page');\n";
            $wynik .= "</script>\n";
            $wynik .= "<noscript>\n";
            $wynik .= "<img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?event=init&tid=" . INTEGRACJA_PINTEREST_TAG_ID . (($emailPinterest != '') ? "&pd[em]=" . $emailPinterest . "&noscript=1\"" : '') . " />\n";
            $wynik .= "</noscript>\n";
            $wynik .= "<!-- end Pinterest Tag -->\n"; 
            
            // zapisanie do newslettera
            if ( isset($_SESSION['pinterest_tmp']) && $_SESSION['pinterest_tmp'] != '' ) {
                 //
                 $wynik .= "<script>pintrk('track', 'lead', { lead_type: 'Newsletter' });</script>\n";
                 $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=lead&ed[lead_type]=Newsletter&noscript=1\" /></noscript>\n";
                 //
                 unset($_SESSION['pinterest_tmp']);
                 //
            }

            // produkt
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'produkt' ) {
                 //
                 if ( isset($_GET['idprod']) && (int)$_GET['idprod'] > 0 ) {
                      //
                      $Produkt = new Produkt( (int)$_GET['idprod'] );
                      //
                      if ($Produkt->CzyJestProdukt == true) {
                          //
                          $IdKategoriiProduktuWyswietlanego = $Produkt->ProduktKategoriaGlowna();
                          $SciezkaKategorii = Kategorie::SciezkaKategoriiId($IdKategoriiProduktuWyswietlanego['id'], 'nazwy', '/');                   
                          //
                          $wynik .= "<script>pintrk('track', 'pagevisit', { line_items: [{ product_id: '" . (int)$_GET['idprod'] . "', product_category: '" . str_replace("'", "", (string)$SciezkaKategorii) . "' }] });</script>\n";
                          $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=pagevisit&noscript=1\" /></noscript>\n";
                          //
                          unset($IdKategoriiProduktuWyswietlanego, $SciezkaKategorii);
                          //
                      }
                      //
                      unset($Produkt);
                      //
                 }
                 //
            } 
            
            // rejestracja
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'rejestracja' ) {
                 //
                 $wynik .= "<script>pintrk('track', 'signup');</script>\n";
                 $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=signup&noscript=1\" /></noscript>\n";
                 //
            }             
            
            // wyszukiwanie
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'szukaj' ) {
                 //
                 if ( isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
                      //
                      $wynik .= "<script>pintrk('track', 'search', { search_query: '" . str_replace("'", "", (string)$filtr->process($_GET['szukaj'])) . "' });</script>\n";
                      $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=search&ed[search_query]=" . str_replace("'", "", (string)$filtr->process($_GET['szukaj'])) . "&noscript=1\" /></noscript>\n";
                      //
                 }
                 //
            }
            
            // listing produktow
            if ( basename($_SERVER['PHP_SELF'],'.' . $ex['extension']) == 'listing' ) {
                 //
                 $wynik .= "<script>pintrk('track', 'viewcategory');</script>\n";
                 $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=viewcategory&noscript=1\" /></noscript>\n";
                 //
            }       

            // dodanie do koszyka
            if ( isset($_SESSION['pinterest_dodanie_koszyk']) && is_array($_SESSION['pinterest_dodanie_koszyk']) ) {
                 //
                 if ( isset($_SESSION['pinterest_dodanie_koszyk']['id']) ) {

                      $wynik .= IntegracjeZewnetrzne::PinterestTagDoKoszykaDodanieAkcja( $_SESSION['pinterest_dodanie_koszyk'], $_SESSION['pinterest_dodanie_koszyk']['id'] );         

                 }
                 //
                 unset($_SESSION['pinterest_dodanie_koszyk']);
                 //
            }            

            unset($ex);
            
        }
        
        return $wynik;

    }    
      
    public static function PinterestTagMailDodanie( $email = '' ) {
      
        if ( INTEGRACJA_PINTEREST_TAG_WLACZONY == 'tak' && INTEGRACJA_PINTEREST_TAG_ID != '' && !in_array('COOKIE_INTEGRACJA_PINTEREST_TAG', $GLOBALS['wykluczeniaIntegracje']) ) {

            $_SESSION['pinterest_tmp'] = $email;

        }
        
    }
      
    public static function PinterestTagDoKoszykaDodanie( $Produkt, $id = '' ) {
      
        if ( INTEGRACJA_PINTEREST_TAG_WLACZONY == 'tak' && INTEGRACJA_PINTEREST_TAG_ID != '' && !in_array('COOKIE_INTEGRACJA_PINTEREST_TAG', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $IdKategoriiProduktuWyswietlanego = $Produkt->ProduktKategoriaGlowna(); 
             //
             if ( PRODUKT_OKNO_POPUP != 'okno popup' ) {

                  $_SESSION['pinterest_dodanie_koszyk'] = array('id' => $id, 'sciezka_kategorii' => $IdKategoriiProduktuWyswietlanego['id']);
                  
             } else {
               
                  echo IntegracjeZewnetrzne::PinterestTagDoKoszykaDodanieAkcja( array('id' => $id, 'sciezka_kategorii' => $IdKategoriiProduktuWyswietlanego['id']), $id );
               
             }
             //
             unset($IdKategoriiProduktuWyswietlanego);
             //
        }
        
    } 

    public static function PinterestTagDoKoszykaDodanieAkcja( $ProduktTablica, $id ) {
      
        $wynik = '';

        if ( INTEGRACJA_PINTEREST_TAG_WLACZONY == 'tak' && INTEGRACJA_PINTEREST_TAG_ID != '' && !in_array('COOKIE_INTEGRACJA_PINTEREST_TAG', $GLOBALS['wykluczeniaIntegracje']) ) {
          
             foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                 //
                 if ( $TablicaZawartosci['id'] == $id ) {
                      //
                      $wynik .= "<script>pintrk('track', 'addtocart', { value: " . $TablicaZawartosci['cena_brutto'] . ", order_quantity: " . $TablicaZawartosci['ilosc'] . ", currency: '" . $_SESSION['domyslnaWaluta']['kod'] . "', line_items: [{ product_id: '" . Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) . "', product_category: '" . str_replace("'", "", (string)Kategorie::SciezkaKategoriiId($ProduktTablica['sciezka_kategorii'], 'nazwy', '/')) . "' }]});</script>\n";
                      $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=AddToCart&ed[value]=" . $TablicaZawartosci['cena_brutto'] . "&ed[order_quantity]=" . $TablicaZawartosci['ilosc'] . "&noscript=1\" /></noscript>\n";          
                      //
                 }
                 //
             }

        }  
        
        return $wynik;
 
    }      
    
    public static function PinterestTagZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';
                        
        if ( INTEGRACJA_PINTEREST_TAG_WLACZONY == 'tak' && INTEGRACJA_PINTEREST_TAG_ID != '' && !in_array('COOKIE_INTEGRACJA_PINTEREST_TAG', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             
             $wynik .= "<script>\n";
             $wynik .= "pintrk('track', 'checkout', {\n";
             $wynik .= "  order_id:  '" . $zamowienie->info['id_zamowienia'] . "',\n";
             $wynik .= "  value: " . $zamowienie->info['wartosc_zamowienia_val'] . ",\n";
             $wynik .= "  order_quantity: " . count($zamowienie->produkty) . ",\n";
             $wynik .= "  currency: '" . $_SESSION['domyslnaWaluta']['kod'] . "',\n";
             $wynik .= "  line_items: [\n";
 
             $tmps = array();
 
             foreach ( $zamowienie->produkty as $produkt ) {
               
                    $SciezkaKategorii = Kategorie::SciezkaKategoriiId($produkt['id_kategorii'], 'nazwy', '/');

                    $tmps[] = "{ product_name: '" . str_replace("'",'',(string)$produkt['nazwa']) . "', product_id: '" . $produkt['id_produktu'] . "', product_price: " . $produkt['cena_koncowa_brutto'] . ", product_quantity: " . $produkt['ilosc'] . ", product_category: '" . str_replace("'", "", (string)$SciezkaKategorii) . "' }";

                    unset($SciezkaKategorii);
                    
             }
             
             $wynik .= implode(',', (array)$tmps);
             
             unset($tmps);

             $wynik .= "  ]})\n";
             $wynik .= "</script>\n";
             
             $wynik .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" src=\"https://ct.pinterest.com/v3/?tid=" . INTEGRACJA_PINTEREST_TAG_ID . "&event=checkout&ed[value]=" . $zamowienie->info['wartosc_zamowienia_val'] . "&ed[order_quantity]=" . count($zamowienie->produkty) . "&noscript=1\" /></noscript>\n";

        } 

        return $wynik;
        
    }       
    

    // ========== integracja z SARE

    /* plik dane_adresowe.php */
    public static function SareDaneAdresowe( $TablicaPost = array() ) {
        
        global $filtr;

        $Subskrybent = false;
        $Klient      = true;
        $Status      = '8';

        if ( INTEGRACJA_SARE_WLACZONY == 'tak' && INTEGRACJA_SARE_UID != '' && INTEGRACJA_SARE_API_KEY != '' && !in_array('COOKIE_INTEGRACJA_SARE', $GLOBALS['wykluczeniaIntegracje']) ) {
            //
            if ( isset($TablicaPost['email']) ) {
                $email   = $filtr->process($TablicaPost['email']);
            } elseif ( isset($TablicaPost['email_nowy']) ) {
                $email   = $filtr->process($TablicaPost['email_nowy']);
            }
            $telefon = $filtr->process($TablicaPost['telefon']);
            $nazwa   = $filtr->process($TablicaPost['imie']) . ' ' . $filtr->process($TablicaPost['nazwisko']);
            $kodKraju =  Klient::pokazKodPanstwa((int)$TablicaPost['panstwo']);

            if ( isset($TablicaPost['biuletyn']) && $TablicaPost['biuletyn'] == '1' ) {
                $Subskrybent = true;
            }
            if ( !isset($TablicaPost['biuletyn']) ) {
                $Status = '6';
            }

            IntegracjeZewnetrzne::SareWyslijDane($email, $Subskrybent, $Klient, $Status, $telefon, $nazwa, $kodKraju);
             //
        }   

    }    

    /* plik inne/do_newslettera.php - dodanie nie klient */
    
    public static function SareDoNewsletteraDodanie( $email = '', $Status = '8' ) {
      
        global $filtr;    
    
        $Subskrybent = true;
        $Klient      = false;

        if ( INTEGRACJA_SARE_WLACZONY == 'tak' && INTEGRACJA_SARE_UID != '' && INTEGRACJA_SARE_API_KEY != '' && !in_array('COOKIE_INTEGRACJA_SARE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             IntegracjeZewnetrzne::SareWyslijDane($email, $Subskrybent, $Klient, $Status, '', '', '' );
             //
        }         
    
    }     
    
    /* plik inne/do_newslettera.php - klient */
    
    public static function SareDoNewsletteraDodanieKlient( $email = '', $Status = '8' ) {
      
        global $filtr;    
    
        $Subskrybent = true;
        $Klient      = true;

        if ( INTEGRACJA_SARE_WLACZONY == 'tak' && INTEGRACJA_SARE_UID != '' && INTEGRACJA_SARE_API_KEY != '' && !in_array('COOKIE_INTEGRACJA_SARE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             IntegracjeZewnetrzne::SareWyslijDane($email, $Subskrybent, $Klient, $Status, '', '', '' );
             //
        }         
    
    }     

    /* plik inne/do_newslettera.php - usuniecie */
    
    public static function SareDoNewsletteraUsuniecie( $email = '' ) {

        $Subskrybent = true;
        $Klient      = false;
        $Status = '6';

        IntegracjeZewnetrzne::SareWyslijDane($email, $Subskrybent, $Klient, $Status, '', '', '' );

    }     

    /* plik aktywacja_konta.php */
    public static function SareAktywacjaKonta( $TablicaPost = array() ) {
        
        global $filtr;

        $Subskrybent = false;
        $Klient      = true;
        $Status      = '8';

        if ( INTEGRACJA_SARE_WLACZONY == 'tak' && INTEGRACJA_SARE_UID != '' && INTEGRACJA_SARE_API_KEY != '' && !in_array('COOKIE_INTEGRACJA_SARE', $GLOBALS['wykluczeniaIntegracje']) ) {
            //

            $email   = $filtr->process($TablicaPost['customers_email_address']);
            $telefon = $filtr->process($TablicaPost['customers_telephone']);
            $nazwa   = $filtr->process($TablicaPost['customers_firstname']) . ' ' . $filtr->process($TablicaPost['customers_lastname']);
            $kodKraju =  Klient::pokazKodPanstwa((int)$TablicaPost['entry_country_id']);

            if ( isset($TablicaPost['customers_newsletter']) && $TablicaPost['customers_newsletter'] == '1' ) {
                $Subskrybent = true;
            }
            if ( !isset($TablicaPost['customers_newsletter']) ) {
                $Status = '6';
            }

            IntegracjeZewnetrzne::SareWyslijDane($email, $Subskrybent, $Klient, $Status, $telefon, $nazwa, $kodKraju);

             //
        }   

    }

    public static function SareWyslijDane( $email = '', $Subskrybent = false, $Klient = false, $Status = '8', $telefon = '', $nazwa = '', $kodKraju = 'PL') {
      
        $uid          = INTEGRACJA_SARE_UID;
        $KluczApi     = INTEGRACJA_SARE_API_KEY;

        $GrupyTablica = array();

        if ( $Subskrybent == true ) {
            $GrupyTablica[] = (int)INTEGRACJA_SARE_GROUP_SUBSKRYBENCI;
        }
        if ( $Klient == true ) {
            $GrupyTablica[] = (int)INTEGRACJA_SARE_GROUP_KLIENCI;
        }

        $Url =  'https://s.enewsletter.pl/api/v1/'.$uid.'/email/add';

        $headers = [
            'Content-Type: application/json',
            'ApiKey: ' . $KluczApi
        ];

        $body = [
                 "only_add_to_groups" => ( INTEGRACJA_SARE_ONLY_ADD_TO_GROUPS == 'tak' ? true : false ),
                 "update_status_on_duplicate" => ( INTEGRACJA_SARE_UPDATE_STATUS_ON_DUPLICATE == 'tak' ? true : false ),
                 "update_on_duplicate" => ( INTEGRACJA_SARE_UPDATE_ON_DUPLICATE == 'tak' ? true : false ),
                 "send_confirmation" => false,
                 "update_user_data_after_confirmation" => false,
                 "emails" => [[
                        "email" => $email,
                        "status" => (int)$Status,
                        "mail_type" => [
                            "text",
                            "HTML"
                        ],
                  ]]
        ];

        if ( count($GrupyTablica) > 0 ) {
            $body['emails'][0]['groups'] = $GrupyTablica;
        }
        if ( $telefon != '' && $kodKraju == 'PL' ) {
            if (preg_match('[^\+48]', $telefon)) {
                $body['emails'][0]['gsm'] = $telefon;
            } else {
                $body['emails'][0]['gsm'] = '+48' . $telefon;
            }
        }
        if ( $nazwa != '' ) {
            $body['emails'][0]['name'] = $nazwa;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));    
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $WynikJson = curl_exec($ch);

        $Wynik = json_decode($WynikJson);
        curl_close($ch);

        return;
                
    }       

    // ========== integracja z SALESFORCE

    /* plik inne/do_newslettera.php - dodanie nie klient */
    public static function SalesForceDoNewsletteraDodanie( $email = '', $imie = '', $nazwisko =  '' ) {
        global $filtr;
    
        if ( INTEGRACJA_SALESFORCE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESFORCE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $SalesForce = new SalesForce();
             $SalesForce->ZapiszSubskrybenta($email, $imie, $nazwisko);
             //
        }         
    
    }     
    
    /* plik dane_adresowe.php - zmiana mail klienta */
    public static function SalesForceDaneAdresoweZmianaEmail( $StaryEmail, $NowyEmail, $Imie, $Nazwisko ) {

        if ( INTEGRACJA_SALESFORCE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESFORCE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $SalesForce = new SalesForce();
             $SalesForce->ZmienEmailKlienta( $StaryEmail, $NowyEmail, $Imie, $Nazwisko );
             //
        }   
    }


    /* plik dane_adresowe.php - usuniecie subskrypcji klienta */
    public static function SalesForceDoNewsletteraUsuniecie( $Email ) {
        if ( INTEGRACJA_SALESFORCE_WLACZONY == 'tak' && !in_array('COOKIE_INTEGRACJA_SALESFORCE', $GLOBALS['wykluczeniaIntegracje']) ) {
             //
             $SalesForce = new SalesForce();
             $SalesForce->UsunEmailKlienta( $Email );
             //
        }   

    }

    // ========== integracja z Trustmate.io

    /* plik produkt.php */
    
    public static function TrusmateProdukt( $Produkt, $Widget = '' ) {

        $wynik = '';

        if ( INTEGRACJA_TRUSTMATE_WLACZONY == 'tak' && INTEGRACJA_TRUSTMATE_KEY != '' ) {
            
            if ( $Widget == 'badger' ) {
                // Wiget BADGER
                $WidgetBadger = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_BADGER_ID);
                if ( $WidgetBadger['0'] == '1' && $WidgetBadger['1'] != '' ) {
                    $wynik .= '<div id="'.$WidgetBadger['1'].'"></div>'."\n";
                    $wynik .= '<script defer src="https://trustmate.io/widget/api/'.$WidgetBadger['1'].'/script?product='.$Produkt->info['id'].'"></script>'."\n";
                }
                unset($WidgetBadger);
            }

            if ( $Widget == 'ferret' ) {
                // Wiget PRODUCTFERRET
                $WidgetProductFerret = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_FERRET_ID);
                if ( $WidgetProductFerret['0'] == '1' && $WidgetProductFerret['1'] != '' ) {
                    $wynik .= '<div id="'.$WidgetProductFerret['1'].'"></div>'."\n";
                    $wynik .= '<script defer src="https://trustmate.io/widget/api/'.$WidgetProductFerret['1'].'/script?product='.$Produkt->info['id'].'"></script>'."\n";
                }
                unset($WidgetProductFerret);
            }

            if ( $Widget == 'hornet' ) {
                // Wiget HORNET
                $WidgetProductHornet = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_HORNET_ID);
                if ( $WidgetProductHornet['0'] == '1' && $WidgetProductHornet['1'] != '' ) {
                    $wynik .= '<div id="'.$WidgetProductHornet['1'].'"></div>'."\n";
                    $wynik .= '<script defer src="https://trustmate.io/widget/api/'.$WidgetProductHornet['1'].'/script?product='.$Produkt->info['id'].'"></script>'."\n";
                }
                unset($WidgetProductHornet);
            }
        }
        
        return $wynik;
    
    }     


    /* plik zamowienie_podsumowanie.php */
    
    public static function TrustmateZamowieniePodsumowanie( $zamowienie ) {
      
        $wynik = '';   
                
        if ( INTEGRACJA_TRUSTMATE_WLACZONY == 'tak' && INTEGRACJA_TRUSTMATE_KEY != '' && !in_array('COOKIE_INTEGRACJA_TRUSTMATE', $GLOBALS['wykluczeniaIntegracje']) ) {
          
            $TrustProdukty = array();

            $KluczApi = INTEGRACJA_TRUSTMATE_KEY;
            $Url = '/integration/api/v1/invitation';
            $headers = [
                        'Content-Type: application/json',
                        'x-api-key: ' . $KluczApi
                       ];

            $Podziel = explode(' ', (string)$zamowienie->klient['nazwa']);
            $ImieZamowienie = '';
            $NazwiskoZamowienie = array();
            //
            for ($x = 0; $x < count($Podziel); $x++) {
                //
                if ( $x == 0 ) {
                    $ImieZamowienie = $Podziel[0];
                }
                //
            }
            //

            foreach ( $zamowienie->produkty as $produkt ) {

                $TrustProdukty[] .= "{\n
                                        \"brand\": \"". str_replace(array("'",'"'), "", (string)$produkt['producent'])."\",
                                        \"gtin\": \"" . $produkt['ean'] . "\",
                                        \"mpn\": \"" . $produkt['model'] . "\",
                                        \"image_url\": \"" . ADRES_URL_SKLEPU . "/". KATALOG_ZDJEC . "/" . $produkt['zdjecie']."\",
                                        \"id\": \"" . $produkt['id_produktu']."\",
                                        \"product_url\": \"" . ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt' )."\",
                                        \"name\": \"" . $produkt['nazwa']."\",
                                        \"price\": \"" . number_format($produkt['cena_koncowa_brutto'], 2, '.', '')."\",
                                        \"currency\": \"" . $zamowienie->info['waluta']."\",
                                        \"priority\": 1\n}\n";

            }

            $input_xml = '
             {
              "customer_name": "'.$ImieZamowienie.'",
              "send_to": "'.$zamowienie->klient['adres_email'].'",
              "order_number": "'.$zamowienie->info['id_zamowienia'].'",
              "order_created_at": "'.date("Y-m-d").'",'."\n";
            $input_xml .= "    \"products\": [\n";
            $input_xml .= implode(',', (array)$TrustProdukty) . "]\n";
            $input_xml .= "  }\n";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, 'https://trustmate.io'.$Url.'');
            curl_setopt($ch, CURLOPT_POSTFIELDS,  $input_xml );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);

            curl_exec($ch);
            //$error_msg = curl_error($ch);
            curl_close($ch);
            
            unset($input_xml, $ImieZamowienie, $TrustProdukty);

        }  

        return;
        
    }      

    // ========== integracja z KLAVIYO
    
    /* plik start.php */
    
    public static function KlaviyoStart() {

        $wynik = '';
        
        if ( INTEGRACJA_KLAVIYO_WLACZONY == 'tak' && INTEGRACJA_KLAVIYO_PUBLIC_KEY != '' && !in_array('COOKIE_INTEGRACJA_KLAVIYO', $GLOBALS['wykluczeniaIntegracje']) ) {

                $wynik .= "<script type=\"text/javascript\" async=\"\" src=\"https://static.klaviyo.com/onsite/js/".INTEGRACJA_KLAVIYO_PUBLIC_KEY."/klaviyo.js\"></script>\n";
                $wynik .= "<script>
                        !function(){if(!window.klaviyo){window._klOnsite=window._klOnsite||[];try{window.klaviyo=new Proxy({},{get:function(n,i){return\"push\"===i?function(){var n;(n=window._klOnsite).push.apply(n,arguments)}:function(){for(var n=arguments.length,o=new Array(n),w=0;w<n;w++)o[w]=arguments[w];var t=\"function\"==typeof o[o.length-1]?o.pop():void 0,e=new Promise((function(n){window._klOnsite.push([i].concat(o,[function(i){t&&t(i),n(i)}]))}));return e}}})}catch(n){window.klaviyo=window.klaviyo||[],window.klaviyo.push=function(){var n;(n=window._klOnsite).push.apply(n,arguments)}}}}();
                </script>";
        }
        
        return $wynik;

    } 

    // ========== integracja z KLAVIYO
    
    /* plik produkt.php */
    
    public static function KlaviyoProdukt($Produkt) {

        $wynik = '';

        if ( INTEGRACJA_KLAVIYO_WLACZONY == 'tak' && INTEGRACJA_KLAVIYO_PUBLIC_KEY != '' && !in_array('COOKIE_INTEGRACJA_KLAVIYO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $KategorieProduktu = array();
            foreach ( Kategorie::ProduktKategorie($Produkt->info['id']) as $Tmp ) {
                //
                if ( isset($GLOBALS['tablicaKategorii'][$Tmp]) ) {
                     //
                     $KategorieProduktu[] = '"' . addslashes($GLOBALS['tablicaKategorii'][$Tmp]['Nazwa']) . '"';
                     //
                }
                //
            }        
            
            $wynik = '<script type="text/javascript">' . "\n";
            $wynik .= ' var item = {' . "\n";
            $wynik .= '   "ProductName": "' . addslashes($Produkt->info['nazwa']) . '",' . "\n";
            $wynik .= '   "ProductID": "' . $Produkt->info['id'] . '",' . "\n";
            $wynik .= '   "SKU": "' . $Produkt->info['nr_katalogowy'] . '",' . "\n";
            $wynik .= '   "Categories": [' . implode(', ', $KategorieProduktu) . '],' . "\n";
            $wynik .= '   "ImageURL": "' . ADRES_URL_SKLEPU . '/'. KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] . '",' . "\n";
            $wynik .= '   "URL": "' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '",' . "\n";
            $wynik .= '   "Brand": "' . $Produkt->info['nazwa_producenta'] . '",' . "\n";
            $wynik .= '   "Price": ' . $Produkt->info['cena_brutto_bez_formatowania'] . ',' . "\n";
            $wynik .= '   "CompareAtPrice": ' . $Produkt->info['cena_brutto_bez_formatowania'] . "\n";
            $wynik .= ' };' . "\n";

            $wynik .= ' klaviyo.push(["track", "Viewed Product", item]);' . "\n\n";
            $wynik .= ' </script>' . "\n";  

            $wynik .= '<script type="text/javascript">' . "\n";
            $wynik .= ' klaviyo.push(["trackViewedItem", {' . "\n";
            $wynik .= '   "Title": "' . addslashes($Produkt->info['nazwa']) . '",' . "\n";
            $wynik .= '   "ItemId": "' . $Produkt->info['id'] . '",' . "\n";
            $wynik .= '   "Categories": [' . implode(', ', $KategorieProduktu) . '],' . "\n";
            $wynik .= '   "ImageUrl": "' . ADRES_URL_SKLEPU . '/'. KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] . '",' . "\n";
            $wynik .= '   "Url": "' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '",' . "\n";
            $wynik .= '   "Metadata": {' . "\n";
            $wynik .= '   "Brand": "' . $Produkt->info['nazwa_producenta'] . '",' . "\n";
            $wynik .= '   "Price": ' . $Produkt->info['cena_brutto_bez_formatowania'] . ',' . "\n";
            $wynik .= '   "CompareAtPrice": ' . $Produkt->info['cena_brutto_bez_formatowania'] . "\n";
            $wynik .= '   }' . "\n";
            $wynik .= ' }]);' . "\n";
            $wynik .= ' </script>' . "\n";  

            unset($KategorieProduktu);
        }

        return $wynik;

    }

    // ========== integracja z KLAVIYO

    /* plik inne/do_koszyka.php - dodanie do koszyka */
    
    public static function KlaviyoDoKoszykaDodanie( $Produkt, $id, $TablicaPost = array() ) {

        if ( INTEGRACJA_KLAVIYO_WLACZONY == 'tak' && INTEGRACJA_KLAVIYO_PUBLIC_KEY != '' && !in_array('COOKIE_INTEGRACJA_KLAVIYO', $GLOBALS['wykluczeniaIntegracje']) ) {
            
            $KategorieProduktow = array();
            $KategorieProduktow[] = '"' . addslashes(Produkty::pokazKategorieProduktu((int)$Produkt->info['id'])) . '"';

            $ProduktyKoszyka = array();
            $ProduktyKoszykaNazwy = array();
            $WartoscProduktow = 0;

            foreach ( $_SESSION['koszyk'] as $produkt ) {
                $WartoscProduktow = $WartoscProduktow + ( $produkt['cena_brutto'] * $produkt['ilosc'] );
                $IdProduktu = Funkcje::SamoIdProduktuBezCech($produkt['id']);

                $ProduktyKoszykaNazwy[] = '"' . addslashes($produkt['nazwa']) . '"';

                $ProduktyKoszyka[] = '{
                        "ProductID": "' . $IdProduktu . '",
                        "SKU": "' . $IdProduktu . '",
                        "ProductName": "' . addslashes($produkt['nazwa']) . '",
                        "Quantity": '.$produkt['ilosc'].',
                        "ItemPrice": ' . $produkt['cena_brutto'] . ',
                        "RowTotal": ' . $produkt['cena_brutto'] * $produkt['ilosc'] . ',
                        "ProductURL": "' . ADRES_URL_SKLEPU . '/' . Seo::link_SEO( $produkt['nazwa'], $IdProduktu, 'produkt' ) . '",
                        "ImageURL": "' . ADRES_URL_SKLEPU . '/'. KATALOG_ZDJEC . '/' . $produkt['zdjecie'] . '",
                        "ProductCategories": ["' . Produkty::pokazKategorieProduktu((int)$IdProduktu) . '"]
                        }' . "\n";

                unset($IdProduktu);
            }

            echo '<script type="text/javascript">' . "\n";
            echo ' klaviyo.push(["track", "Added to Cart", {' . "\n";
            echo ' "$value": '.$WartoscProduktow.',' . "\n";
            echo ' "AddedItemProductName": "' . addslashes($Produkt->info['nazwa']) . '",' . "\n";
            echo ' "AddedItemProductID": "' . $Produkt->info['id'] . '",' . "\n";
            echo ' "AddedItemSKU": "' . $Produkt->info['id'] . '",' . "\n";
            echo ' "AddedItemCategories": [' . implode(', ', $KategorieProduktow) . '],' . "\n";
            echo ' "AddedItemImageURL": "' . ADRES_URL_SKLEPU . '/'. KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] . '",' . "\n";
            echo ' "AddedItemURL": "' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '",' . "\n";
            echo ' "AddedItemPrice": ' . $Produkt->info['cena_brutto_bez_formatowania'] . ',' . "\n";
            echo ' "AddedItemQuantity": '.$TablicaPost['ilosc'].',' . "\n";
            echo ' "ItemNames": [' . implode(', ', $ProduktyKoszykaNazwy) . '],' . "\n";
            echo ' "CheckoutURL": "' . ADRES_URL_SKLEPU . '/zamowienie-logowanie.html",' . "\n";
            echo '     "Items": [' . "\n";
            echo implode(',', $ProduktyKoszyka) . "\n";
            echo '     ]' . "\n";
            echo ' }]);' . "\n";
            echo '</script>' . "\n";

            unset($KategorieProduktow, $ProduktyKoszyka, $WartoscProduktow, $ProduktyKoszykaNazwy);
        }  
 
        return;

    }   

    // ========== integracja z KLAVIYO

    /* plik zamowienie_potwierdzenie.php - rozpoczecie zamowienia */
    
    public static function KlaviyoZamowieniePotwierdzenie() {

        $wynik = '';

        if ( INTEGRACJA_KLAVIYO_WLACZONY == 'tak' && INTEGRACJA_KLAVIYO_PUBLIC_KEY != '' && !in_array('COOKIE_INTEGRACJA_KLAVIYO', $GLOBALS['wykluczeniaIntegracje']) ) {
            
            $ProduktyKoszykaKategorie = array();
            $ProduktyKoszykaNazwy = array();
            $WartoscProduktow = 0;
            $Czas = time();
            if (isset($_COOKIE['koszykGoldID']) ) {
                $EventId = (string)$_COOKIE['koszykGoldID'].'_'.$Czas;
                //$EventId = (string)$_COOKIE['koszykGoldID'];
            } else {
                @setcookie("koszykGoldID", $Czas, time() + (5 * 86400), '/');
                $EventId = $Czas.'_'.$Czas;
                //$EventId = $Czas;
            }

            foreach ( $_SESSION['koszyk'] as $produkt ) {
                $WartoscProduktow = $WartoscProduktow + ( $produkt['cena_brutto'] * $produkt['ilosc'] );
                $IdProduktu = Funkcje::SamoIdProduktuBezCech($produkt['id']);

                $ProduktyKoszykaNazwy[] = '"' . addslashes($produkt['nazwa']) . '"';
                $ProduktyKoszykaKategorie[] = '"' . addslashes(Produkty::pokazKategorieProduktu((int)$IdProduktu)) . '"';

                $ProduktyKoszyka[] = '{
                        "ProductID": "' . $IdProduktu . '",
                        "SKU": "' . $IdProduktu . '",
                        "ProductName": "' . addslashes($produkt['nazwa']) . '",
                        "Quantity": '.$produkt['ilosc'].',
                        "ItemPrice": ' . $produkt['cena_brutto'] . ',
                        "RowTotal": ' . $produkt['cena_brutto'] * $produkt['ilosc'] . ',
                        "ProductURL": "' . ADRES_URL_SKLEPU . '/' . Seo::link_SEO( $produkt['nazwa'], $IdProduktu, 'produkt' ) . '",
                        "ImageURL": "' . ADRES_URL_SKLEPU . '/'. KATALOG_ZDJEC . '/' . $produkt['zdjecie'] . '",
                        "ProductCategories": ["' . Produkty::pokazKategorieProduktu((int)$IdProduktu) . '"]
                        }' . "\n";

                unset($IdProduktu);
            }

            $wynik .= '<script type="text/javascript">' . "\n";
            $wynik .= ' klaviyo.track("Started Checkout", {' . "\n";
            $wynik .= ' "$event_id": '.$EventId.',' . "\n";
            $wynik .= ' "$value": '.$WartoscProduktow.',' . "\n";
            $wynik .= ' "ItemNames": [' . implode(', ', $ProduktyKoszykaNazwy) . '],' . "\n";
            $wynik .= ' "CheckoutURL": "' . ADRES_URL_SKLEPU . '/zamowienie-potwierdzenie.html",' . "\n";
            $wynik .= ' "Categories": [' . implode(', ', $ProduktyKoszykaKategorie) . '],' . "\n";
            $wynik .= '     "Items": [' . "\n";
            $wynik .= implode(',', $ProduktyKoszyka) . "\n";
            $wynik .= '     ]' . "\n";
            $wynik .= ' });' . "\n";
            $wynik .= '</script>' . "\n";

            $wynik .= '<script>' . "\n";
            $wynik .= ' klaviyo.identify({' . "\n";
            $wynik .= ' "email": "'.$_SESSION['customer_email'].'",' . "\n";
            $wynik .= ' "first_name": "' . $_SESSION['customer_firstname'] . '",' . "\n";
            $wynik .= ' });' . "\n";
            $wynik .= '</script>' . "\n";

            unset($ProduktyKoszykaKategorie, $ProduktyKoszyka, $WartoscProduktow, $ProduktyKoszykaNazwy);
        }  
 
        return $wynik;

    }   

    // ========== integracja z KLAVIYO

    /* identyfikacja klienta */
    
    public static function KlaviyoIdentyfikacjaKlienta($Email, $Imie = '', $Nazwisko = '', $Referer = '') {

        $wynik = '';

        if ( INTEGRACJA_KLAVIYO_WLACZONY == 'tak' && INTEGRACJA_KLAVIYO_PUBLIC_KEY != '' && !in_array('COOKIE_INTEGRACJA_KLAVIYO', $GLOBALS['wykluczeniaIntegracje']) ) {
            
            if ( $Referer == 'dane-adresowe.html' ) {
                $wynik .= '<script>' . "\n";
                $wynik .= ' klaviyo.identify({' . "\n";
                $wynik .= ' "email": "'.$Email.'",' . "\n";
                $wynik .= ' "first_name": "' . $Imie . '",' . "\n";
                $wynik .= ' "last_name": "' . $Nazwisko . '",' . "\n";
                $wynik .= ' });' . "\n";
                $wynik .= '</script>' . "\n";
            } else {
                echo '<script>' . "\n";
                echo ' klaviyo.identify({' . "\n";
                echo ' "email": "'.$Email.'",' . "\n";
                echo ' "first_name": "' . $Imie . '",' . "\n";
                echo ' "last_name": "' . $Nazwisko . '",' . "\n";
                echo ' });' . "\n";
                echo '</script>' . "\n";
            }

        }  
 
        return $wynik;
    }   

    // ========== integracja z KLAVIYO

    /* zlozenie zamowienia */
    
    public static function KlaviyoZamowieniePodsumowanie( $zamowienie ) {

        $wynik = '';

        if ( INTEGRACJA_KLAVIYO_WLACZONY == 'tak' && INTEGRACJA_KLAVIYO_PRIVATE_KEY != '' && !in_array('COOKIE_INTEGRACJA_KLAVIYO', $GLOBALS['wykluczeniaIntegracje']) ) {

            $PrivKey = INTEGRACJA_KLAVIYO_PRIVATE_KEY;
            $URL     = 'https://a.klaviyo.com/api/events/';
            $Produkty = array();

            $KodKraju = Klient::pokazISOPanstwa($zamowienie->klient['kraj'], '1');
            $NumerTelefonu = Klient::pokazPrefixPanstwa($KodKraju, $zamowienie->klient['telefon']);

            $KategorieProduktu = array();
            $NazwyProduktow = array();
            $NazwyProducenci = array();

            $headers = [
                      'Authorization: Klaviyo-API-Key ' . $PrivKey,
                      'accept: application/vnd.api+json',
                      'content-type: application/vnd.api+json',
                      'revision: 2024-10-15'
            ];

            $Podziel = explode(' ', (string)$zamowienie->klient['nazwa']);
            $ImieZamowienie = '';
            $NazwiskoZamowienie = array();
            //
            for ($x = 0; $x < count($Podziel); $x++) {
                //
                if ( $x == 0 ) {
                    $ImieZamowienie = $Podziel[0];
                } else {
                    $NazwiskoZamowienie[] = $Podziel[$x];
                }
                //
            }

            //

            foreach ( $zamowienie->produkty as $produkt ) {

                $Produkty[] = array("ProductID" => $produkt['id_produktu'],
                        "SKU" => $produkt['id_produktu'],
                        "ProductName" => $produkt['nazwa'],
                        "Quantity" => number_format($produkt['ilosc'], 2, '.', ''),
                        "ItemPrice" => number_format($produkt['cena_koncowa_brutto'], 2, '.', ''),
                        "RowTotal" => number_format(($produkt['ilosc'] * $produkt['cena_koncowa_brutto']), 2, '.', ''),
                        "ProductURL" => ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt' ),
                        "ImageURL" => ADRES_URL_SKLEPU . "/" . KATALOG_ZDJEC . "/" . $produkt['zdjecie'],
                        "Categories" => Produkty::pokazKategorieProduktu($produkt['id_kategorii']),
                        "Brand" => str_replace(array("'",'"'), "", (string)$produkt['producent']));

                $KategorieProduktu[] = Produkty::pokazKategorieProduktu($produkt['id_kategorii']);
                $NazwyProduktow[] = $produkt['nazwa'];
                $NazwyProducenci[] = str_replace(array("'",'"'), "", (string)$produkt['producent']);

                $daneProduktu = array("data" => array(
                        "type" => "event",
                        "attributes" => array(
                           "properties" => array(
                                "OrderId" => $zamowienie->info['id_zamowienia'],
                                "ProductID" => $produkt['id_produktu'],
                                "SKU" => $produkt['id_produktu'],
                                "ProductName" => $produkt['nazwa'],
                                "Quantity" => number_format($produkt['ilosc'], 2, '.', ''),
                                "ProductURL" => ADRES_URL_SKLEPU . "/". Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt' ),
                                "ImageURL" => ADRES_URL_SKLEPU . "/". KATALOG_ZDJEC . "/" . $produkt['zdjecie'],
                                "Categories" => '['.Produkty::pokazKategorieProduktu($produkt['id_kategorii']) .']',
                                "ProductBrand" => str_replace(array("'",'"'), "", (string)$produkt['producent'])
                            ),
                            "time" => date('Y-m-d\TH:i:s'),
                            "value" => number_format($produkt['cena_koncowa_brutto'], 2, '.', ''),
                            "value_currency" => $_SESSION['domyslnaWaluta']['kod'],
                            "unique_id" => $zamowienie->info['id_zamowienia'] . '_' . time(),
                            "metric" => array(
                                "data" => array(
                                    "type" => "metric",
                                    "attributes" => array(
                                        "name" => "Ordered Product"
                                    )
                                )
                            ),
                            "profile" => array(
                                "data" => array(
                                    "type" => "profile",
                                    "attributes" => array(
                                        "email" => $zamowienie->klient['adres_email'],
                                        "phone_number" => $NumerTelefonu
                                    )
                                )
                            )
                        )
                     )
                  )
                ;
                $daneProduktu_json = json_encode($daneProduktu);

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $URL);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $daneProduktu_json);    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                curl_exec($ch);
                curl_close($ch);

            }

            $dane = array("data" => array(
                    "type" => "event",
                    "attributes" => array(
                        "properties" => array(
                            "OrderId" => $zamowienie->info['id_zamowienia'],
                            "Categories" => array(implode(', ', $KategorieProduktu)),
                            "ItemNames" => array(implode(', ', $NazwyProduktow)),
                            "Brands" => array(implode(', ', $NazwyProducenci)),
                            "Items" => $Produkty,
                            "BillingAddress" => array(
                                "FirstName" => $ImieZamowienie,
                                "LastName" => implode(' ', (array)$NazwiskoZamowienie),
                                "Address1" => $zamowienie->klient['ulica'],
                                "City" => $zamowienie->klient['miasto'],
                                "CountryCode" => Klient::pokazISOPanstwa($zamowienie->klient['kraj'], '1'),
                                "Zip" => (string)$zamowienie->klient['kod_pocztowy'],
                                "Phone" => $NumerTelefonu
                            ),
                            "ShippingAddress" => array(
                                "Address1" => $zamowienie->klient['ulica']
                            )
                        ),
                        "time" => date('Y-m-d\TH:i:s'),
                        "value" => number_format($zamowienie->info['wartosc_zamowienia_val'], 2, '.', ''),
                        "value_currency" => $_SESSION['domyslnaWaluta']['kod'],
                        "unique_id" => $zamowienie->info['id_zamowienia'],
                        "metric" => array(
                            "data" => array(
                                "type" => "metric",
                                "attributes" => array(
                                    "name" => "Placed Order"
                                )
                            )
                        ),
                        "profile" => array(
                            "data" => array(
                                "type" => "profile",
                                "attributes" => array(
                                    "email" => $zamowienie->klient['adres_email'],
                                    "phone_number" => $NumerTelefonu
                                )
                            )
                        )
                    )
                 )
            )
            ;

            $data_json = json_encode($dane);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $wynik = curl_exec($ch);
            $data = json_decode($wynik);
            curl_close($ch);

        }  
 
        return;
    }   


}

?>