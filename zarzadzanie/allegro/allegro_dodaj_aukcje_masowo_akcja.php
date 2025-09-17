<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr() && Funkcje::SprawdzAktywneAllegro() && isset($_POST['id_aukcji'])) { 

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    $ile_jezykow = Funkcje::TablicaJezykow();

    $domyslnaJm = 0;
    
    $sqle = $db->open_query("SELECT * FROM products_jm WHERE products_jm_default = '1'");  
    while ($jm = $sqle->fetch_assoc()) {
        $domyslnaJm = $jm['products_jm_id'];
    }
    $db->close_query($sqle);

    $domyslna_stawka_vat = '23';
    $domyslny_id_vat = '3';
    
    $sqlt = $db->open_query("SELECT * FROM tax_rates WHERE tax_default = '1'");  
    while ($tax = $sqlt->fetch_assoc()) {
        $domyslna_stawka_vat = $tax['tax_rate'];
        $domyslny_id_vat = $tax['tax_rates_id'];
    }
    $db->close_query($sqlt);

    $IdPLN = '1';
    
    $sqlw = $db->open_query("SELECT * FROM currencies WHERE code = 'PLN'");  
    while ($waluta = $sqlw->fetch_assoc()) {
        $IdPLN = $waluta['currencies_id'];
    }
    $db->close_query($sqlw);
    
    $IdPL = '1';

    $sqlj = $db->open_query("SELECT languages_id, name, code FROM languages WHERE code = 'pl'");  
    while ($jezyk = $sqlj->fetch_assoc()) {
        $IdPL = $jezyk['languages_id'];
    }
    $db->close_query($sqlj);
    
    $TablicaKategoriiZAllegro = array();
    
    $aukcjaID = $_POST['id_aukcji'];
    
    $PrzetwarzanaAukcja = $AllegroRest->commandRequest('sale/product-offers', $aukcjaID, '' );

    if ( is_object($PrzetwarzanaAukcja) && count((array)$PrzetwarzanaAukcja) > 0 ) {

        $TablicaZdjecProduktu = array();
        $MapaZdjec = array();
        $TablicaZdjec = $PrzetwarzanaAukcja->images;

        // czy jest glowny katalog zdjec allegro
        if ( is_dir( KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro' ) == false ) {
            //
            $old_mask = umask(0);
            mkdir(KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro', 0777, true);
            umask($old_mask);
            //
        }      
        // czy jest katalog dla zdjec konkretnej aukcji
        if ( is_dir( KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) ) == false ) {
            //
            $old_mask = umask(0);
            mkdir(KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID), 0777, true);
            umask($old_mask);
            //
        }

        if ( count($TablicaZdjec) > 0 ) {

            $licznik = 1;

            foreach ( $TablicaZdjec as $key => $AdresZdjecia ) {

                if ( $licznik == '1' ) {
                    $NazwaPliku = KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . floatval($aukcjaID) . '.jpg';
                    $NazwaPlikuDoBazy = '/' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . floatval($aukcjaID) . '.jpg';
                } else {
                    $NazwaPliku = KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . $licznik . '_' . floatval($aukcjaID) . '.jpg';
                    $NazwaPlikuDoBazy = '/' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . $licznik . '_' . floatval($aukcjaID) . '.jpg';
                }

                $TablicaZdjecProduktu[] = 'produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . $licznik . '_' . floatval($aukcjaID) . '.jpg';

                // jezeli jest plik to go usunie
                if (file_exists($NazwaPliku)) { 
                    //
                    unlink($NazwaPliku);
                    //
                    if (is_dir(KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/mini/')) {
                        //

                        $KatalogZdjecAllegro = glob(KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/mini/*.*');

                        if ( !empty($KatalogZdjecAllegro) ) {
                            foreach ($KatalogZdjecAllegro as $plik) {
                                if (is_file($plik)) {
                                    unlink($plik);
                                }
                            }
                        }
                        //
                        rmdir(KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/mini/');
                    }
                    //
                }                  
                //

                $fp = fopen($NazwaPliku, 'wb');

                $ch = curl_init($AdresZdjecia);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_VERBOSE,true);
                curl_exec($ch);

                if(curl_exec($ch) === false) {
                    if (filesize($NazwaPliku) === 0){
                        unlink ($NazwaPliku);
                    }
                } else {
                    fclose($fp);
                }

                curl_close($ch);
    
                $MapaZdjec[$key] = array('allegro' => $AdresZdjecia,
                                         'sklep' => $NazwaPlikuDoBazy);
                                         
                unset($NazwaPliku, $NazwaPlikuDoBazy);

                $licznik++;

            }
        }

        if ( isset($TablicaKategoriiZAllegro[$PrzetwarzanaAukcja->category->id]) ) {
          
            $SciezkaKategorii_allegro = $TablicaKategoriiZAllegro[$PrzetwarzanaAukcja->category->id];
            
        } else {
          
            $KategorieAllegro = $AllegroRest->commandRequest('sale/categories', $PrzetwarzanaAukcja->category->id, '');
            $SciezkaKategorii = array(); 

            if ( isset($KategorieAllegro->leaf) && $KategorieAllegro->leaf == '1' ) {
                //
                $SciezkaKategorii[0] = str_replace("'", '', $KategorieAllegro->name); 
                //
                $parent = $KategorieAllegro->parent->id;
                           
                for ( $x = 1; $x < 9; $x++ ) {
                    //
                    // kolejne pozycje
                    $KolejnaPozycjaParent = $AllegroRest->commandRequest('sale/categories', $parent, '');
                    $SciezkaKategorii[ $x ] = str_replace("'", '', $KolejnaPozycjaParent->name);
                    //
                    if ( isset($KolejnaPozycjaParent->parent->id) ) {
                        //
                        $parent = $KolejnaPozycjaParent->parent->id;
                        //
                    } else {
                        //
                        break;
                        //
                    }
                    //
                }
                unset($parent);
                krsort($SciezkaKategorii);

            }
            $SciezkaKategorii_allegro = implode(';', $SciezkaKategorii);

            $TablicaKategoriiZAllegro[$PrzetwarzanaAukcja->category->id] = $SciezkaKategorii_allegro;
            
        }

        $KodEan = '';
        $KodProducenta = '';
        
        foreach ( $PrzetwarzanaAukcja->productSet as $ProduktAukcji ) {
          
            foreach ( $ProduktAukcji->product->parameters as $ParametryProduktu ) {
              
                if ( $ParametryProduktu->name == 'EAN (GTIN)' ) {
                    if ( isset($ParametryProduktu->values[0]) ) {
                         $KodEan = $ParametryProduktu->values[0];
                    }
                }
                
                if ( $ParametryProduktu->name == 'Kod producenta' ) {
                    if ( isset($ParametryProduktu->values[0]) ) {
                         $KodProducenta = $ParametryProduktu->values[0];
                    }
                }
                
            }

        }

        // ---------------------------------- products

        $CenaBrutto = $PrzetwarzanaAukcja->sellingMode->price->amount;
        //
        $CenaNetto = round(($CenaBrutto / ((100 + $domyslna_stawka_vat) / 100)), CENY_MIEJSCA_PO_PRZECINKU);
        $Vat = $CenaBrutto - $CenaNetto;
        //
        $pola = array(
                array('products_status','1'),
                array('products_ean',$KodEan),
                array('products_model',$KodProducenta),
                array('products_man_code',$KodProducenta),
                array('products_date_added','now()'),
                array('products_condition_products_id','1'),
                array('products_type',''),
                array('products_quantity',(float)$PrzetwarzanaAukcja->stock->available),
                array('products_jm_id',(int)$domyslnaJm),
                array('products_price',(float)$CenaNetto),
                array('products_tax',(float)$Vat),
                array('products_price_tax',(float)$CenaBrutto),
                array('products_currencies_id',(int)$IdPLN),
                array('products_tax_class_id',(int)$domyslny_id_vat),
                array('products_id_private',( isset($PrzetwarzanaAukcja->external->id) ? $PrzetwarzanaAukcja->external->id : '' )));
                
                    
        unset($CenaBrutto, $CenaNetto, $Vat, $KodEan, $KodProducenta );
        //
        if ( isset($TablicaZdjecProduktu[0]) ) {
            //
            $pola[] = array('products_image',str_replace('1_', '', $TablicaZdjecProduktu[0]));
            //
        }

        $id_dodanej_pozycji = $db->insert_query('products' , $pola, '', false, true);

        unset($pola);    

        // ---------------------------------- description

        $Opis = '<style>
          .AllegroKolumny { margin:10px 0px 10px 0px; clear:both; }
          .AllegroKolumna { padding:0px 15px 0px 15px; -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box; -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box; }
          .AllegroJednaKolumnaText, .AllegroJednaKolumnaZdjecie, .AllegroDwieKolumnyZdjecie, .AllegroDwieKolumnyText { width:100%; }
          @media only screen and (min-width:1024px) {
            .AllegroKolumny { display:flex; align-items:center; }
            .AllegroDwieKolumnyZdjecie, .AllegroDwieKolumnyText { float:left; width:50%; }
          }
          .AllegroDwieKolumnyZdjecie img, .AllegroJednaKolumnaZdjecie img { max-width:100%; height:auto; }                  
          .AllegroKolumny h1 { font-size:120%; margin:20px 0px 20px 0px; padding:0px; }
          .AllegroKolumny ul { margin:15px 0px 15px 0px; padding:0px 0px 0px 15px; }
          .AllegroKolumny p { margin:15px 0px 15px 0px; }
          </style>';         

        for ( $w = 0, $c = count($PrzetwarzanaAukcja->description->sections); $w < $c; $w++) {

            // jezeli w formie 2 kolumn
            if ( isset($PrzetwarzanaAukcja->description->sections[$w]->items[0]) && isset($PrzetwarzanaAukcja->description->sections[$w]->items[1]) ) {
                //
                // zdjecie_listing
                if ( $PrzetwarzanaAukcja->description->sections[$w]->items[0]->type == 'IMAGE' && $PrzetwarzanaAukcja->description->sections[$w]->items[1]->type == 'TEXT' ) {
                    //
                    foreach ( $MapaZdjec as $Zdjecie ) {
                        if ( $Zdjecie['allegro'] == $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url ) {
                            $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url = $Zdjecie['sklep'];
                        }
                    }
                    $Opis .= '<section class="AllegroKolumny">';
                    $Opis .= '<div class="AllegroDwieKolumnyZdjecie AllegroKolumna"><img src="' . $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url . '" /></div>';
                    $Opis .= '<div class="AllegroDwieKolumnyText AllegroKolumna">' . $PrzetwarzanaAukcja->description->sections[$w]->items[1]->content . '</div>';
                    $Opis .= '</section>';
                    //
                }
                //
                // listing_zdjecie
                if ( $PrzetwarzanaAukcja->description->sections[$w]->items[0]->type == 'TEXT' && $PrzetwarzanaAukcja->description->sections[$w]->items[1]->type == 'IMAGE' ) {
                    //
                    foreach ( $MapaZdjec as $Zdjecie ) {
                        if ( $Zdjecie['allegro'] == $PrzetwarzanaAukcja->description->sections[$w]->items[1]->url ) {
                            $PrzetwarzanaAukcja->description->sections[$w]->items[1]->url = $Zdjecie['sklep'];
                        }
                    }
                    $Opis .= '<section class="AllegroKolumny">';
                    $Opis .= '<div class="AllegroDwieKolumnyText AllegroKolumna">' . $PrzetwarzanaAukcja->description->sections[$w]->items[0]->content . '</div>';
                    $Opis .= '<div class="AllegroDwieKolumnyZdjecie AllegroKolumna"><img src="' . $PrzetwarzanaAukcja->description->sections[$w]->items[1]->url . '" /></div>';                        
                    $Opis .= '</section>';
                    //
                }
                //
                // zdjecie_zdjecie
                if ( $PrzetwarzanaAukcja->description->sections[$w]->items[0]->type == 'IMAGE' && $PrzetwarzanaAukcja->description->sections[$w]->items[1]->type == 'IMAGE' ) {
                    //
                    foreach ( $MapaZdjec as $Zdjecie ) {
                        if ( $Zdjecie['allegro'] == $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url ) {
                            $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url = $Zdjecie['sklep'];
                        }
                        if ( $Zdjecie['allegro'] == $PrzetwarzanaAukcja->description->sections[$w]->items[1]->url ) {
                            $PrzetwarzanaAukcja->description->sections[$w]->items[1]->url = $Zdjecie['sklep'];
                        }
                    }
                    $Opis .= '<section class="AllegroKolumny">';
                    $Opis .= '<div class="AllegroDwieKolumnyZdjecie AllegroKolumna"><img src="' . $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url . '" /></div>'; 
                    $Opis .= '<div class="AllegroDwieKolumnyZdjecie AllegroKolumna"><img src="' . $PrzetwarzanaAukcja->description->sections[$w]->items[1]->url . '" /></div>'; 
                    $Opis .= '</section>';
                    //
                }
                //                   
            } else if ( !isset($PrzetwarzanaAukcja->description->sections[$w]->items[1]) ) {
                //
                // listing
                if ( $PrzetwarzanaAukcja->description->sections[$w]->items[0]->type == 'TEXT' ) {
                    //
                    $Opis .= '<section class="AllegroKolumny ">';
                    $Opis .= '<div class="AllegroJednaKolumnaText AllegroKolumna">' . $PrzetwarzanaAukcja->description->sections[$w]->items[0]->content . '</div>';                        
                    $Opis .= '</section>';
                    //
                }                   
                //
                // zdjecie
                if ( $PrzetwarzanaAukcja->description->sections[$w]->items[0]->type == 'IMAGE' ) {
                    //
                    foreach ( $MapaZdjec as $Zdjecie ) {
                        if ( $Zdjecie['allegro'] == $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url ) {
                            $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url = $Zdjecie['sklep'];
                        }
                    }
                    $Opis .= '<section class="AllegroKolumny">';
                    $Opis .= '<div class="AllegroJednaKolumnaZdjecie AllegroKolumna"><img src="' . $PrzetwarzanaAukcja->description->sections[$w]->items[0]->url . '" /></div>';
                    $Opis .= '</section>';
                    //
                }                     
            }

        }

        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //       
            $pola = array(
                    array('products_id',$id_dodanej_pozycji),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
                    array('products_name',$PrzetwarzanaAukcja->name),
                    array('products_description',$Opis),       
                    array('products_meta_title_tag',$PrzetwarzanaAukcja->name),
                    array('products_meta_desc_tag',$PrzetwarzanaAukcja->name),
                    array('products_meta_keywords_tag',$PrzetwarzanaAukcja->name));  

            $sql = $db->insert_query('products_description' , $pola);
            unset($pola);
            //            
        }
        //

        // ---------------------------------- additional_images
        
        if ( isset($TablicaZdjecProduktu) && count($TablicaZdjecProduktu) > 1 ) {
            //
            for ($x = 1; $x < count($TablicaZdjecProduktu); $x++ ) {
                //
                $pola = array(
                     array('products_id',(int)$id_dodanej_pozycji),
                     array('popup_images',$TablicaZdjecProduktu[$x]));                      
                     //
                $sql = $db->insert_query('additional_images' , $pola);
                unset($pola);
                //
            }
            //
        }

        // ---------------------------------- products_extra_fields
        
        if ( isset($PrzetwarzanaAukcja->productSet[0]->product->parameters) && count($PrzetwarzanaAukcja->productSet[0]->product->parameters) > 0 ) {

            foreach ( $PrzetwarzanaAukcja->productSet[0]->product->parameters as $PoleDodatkowe ) {

                // sprawdza czy dodatkowe pole jest juz w bazie
                if ( isset($PoleDodatkowe->values[0]) && $PoleDodatkowe->values[0] != '' ) {
                  
                    $zapytanieDodPole = "select products_extra_fields_id, products_extra_fields_name from products_extra_fields where products_extra_fields_name = '" . $PoleDodatkowe->name . "' and languages_id = '".$IdPL."'";
                    $sqlc = $db->open_query($zapytanieDodPole);
                    //    
                    if ((int)$db->ile_rekordow($sqlc) > 0) {
                        //
                        $info = $sqlc->fetch_assoc();
                        $IdPolaDodatkowego = $info['products_extra_fields_id'];
                        //   
                        $db->close_query($sqlc);
                        unset($info);
                        //
                     } else {
                        // jezeli nie ma dodatkowego pola to doda je do bazy
                        $pole = array(
                                array('products_extra_fields_name',$PoleDodatkowe->name),
                                array('products_extra_fields_status','1'),
                                array('languages_id',(int)$IdPL));   
                        $db->insert_query('products_extra_fields' , $pole); 
                        //
                        $IdPolaDodatkowego = $db->last_id_query();
                        unset($pole);
                        //
                    }                      
                    //
                    $pole = array(
                            array('products_id', (int)$id_dodanej_pozycji),
                            array('products_extra_fields_id', (int)$IdPolaDodatkowego),
                            array('products_extra_fields_value', $PoleDodatkowe->values[0]));  

                    $db->insert_query('products_to_products_extra_fields' , $pole);  
                    //
                    unset($pole, $IdPolaDodatkowego);                           

                }
              
            }

          //
        }

        // ---------------------------------- categories
        
        $tablica_kat = explode(';', $SciezkaKategorii_allegro);

        $parent = 0;

        for ($w = 0; $w < count($tablica_kat); $w++) {
            
            $zapytanie_kategorie = "select c.categories_id, cd.categories_name from categories c, categories_description cd where cd.language_id = '".$IdPL."' and c.categories_id = cd.categories_id and categories_name = '" . $tablica_kat[$w] . "' and parent_id = '" . (int)$parent . "'";
            $sql_kategorie = $db->open_query($zapytanie_kategorie);
              
            if ((int)$db->ile_rekordow($sql_kategorie) == 0) {
                //
                $pola = array(
                    array('parent_id', $parent),
                    array('sort_order', '1'),
                    array('categories_status', '1'));
                $db->insert_query('categories' , $pola);
                
                $id_dodanej_kategorii = $db->last_id_query();
                
                unset($pola);
                //
                $pola = array(
                    array('categories_id', $id_dodanej_kategorii),
                    array('language_id', (int)$IdPL),
                    array('categories_name', $tablica_kat[$w])); 
                    
                $db->insert_query('categories_description' , $pola);
                
                unset($pola);    
                //
                $parent = $id_dodanej_kategorii;
                  
                // ---------------------------------------------------------------
                // dodawanie do innych jezykow jak sa inne jezyki
                for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                    //
                    $pola = array(
                            array('categories_id', $id_dodanej_kategorii),
                            array('language_id', (int)$ile_jezykow[$j]['id']),
                            array('categories_name', $tablica_kat[$w]));    

                    if ($ile_jezykow[$j]['id'] != '1') {
                        $db->insert_query('categories_description' , $pola);
                    }
                    unset($pola);              
                    //
                }
                  
            } else {
              
                // jezeli znaleziono taka kategorie
                $infg = $sql_kategorie->fetch_assoc();
                $parent = $infg['categories_id'];
                $db->close_query($sql_kategorie);
                unset($infg);             
              
            }
              
            unset($zapytanie_kategorie);
              
        }        
          
        if ( (int)$parent > 0 ) {
            //
            $pola = array(
                    array('products_id',(int)$id_dodanej_pozycji),
                    array('categories_id',(int)$parent)); 
            //                        
            $sql = $db->insert_query('products_to_categories' , $pola);
            unset($pola);
        }
              
        unset($tablica_kat);
        //

        $DataRozpoczecia = date("Y-m-d H:i:s");
        
        if ( isset($PrzetwarzanaAukcja->publication->startedAt) ) {
             $DataRozpoczecia = $PrzetwarzanaAukcja->publication->startedAt;
        }
        
        if ( isset($PrzetwarzanaAukcja->publication->startingAt) ) {
             $DataRozpoczecia = $PrzetwarzanaAukcja->publication->startingAt;
        }
        
        $DataStart = date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataRozpoczecia));

        if ( isset($PrzetwarzanaAukcja->publication->endingAt) && $PrzetwarzanaAukcja->publication->endingAt != '' ) {
            $DataKoniec = date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endingAt));
        } else {
            $DataKoniec = '1970-01-01 01:00:00';
        }

        $Status = $PrzetwarzanaAukcja->publication->status;
        $IloscObserwujacych = '0';
        $IloscWyswietlen = '0';
        $IloscDostepnych = $PrzetwarzanaAukcja->stock->available;
        $IloscSprzedanych = '0';

        $DanePromowania = $AllegroRest->commandGet('sale/offers/' . $aukcjaID . '/promo-options');

        $JakiePromowania = array();

        if ( isset($DanePromowania->basePackage) ) {
            //
            if ( isset($DanePromowania->basePackage->id) && $DanePromowania->basePackage->id == 'emphasized10d' ) { $JakiePromowania[] = 'emphasized10d'; }
            if ( isset($DanePromowania->basePackage->id) && $DanePromowania->basePackage->id == 'emphasized1d' ) { $JakiePromowania[] = 'emphasized1d'; }
            if ( isset($DanePromowania->basePackage->id) && $DanePromowania->basePackage->id == 'promoPackage' ) { $JakiePromowania[] = 'promoPackage'; }
            //
        }

        if ( isset($DanePromowania->extraPackages[0]) ) {
            //
            if ( isset($DanePromowania->extraPackages->id) && $DanePromowania->extraPackages->id == 'departmentPage' ) { $JakiePromowania[] = 'departmentPage'; }                                            
            //
        }                                           

        $pola = array(
                array('auction_id',floatval($aukcjaID)),
                array('products_id',(int)$id_dodanej_pozycji),
                array('products_name',$PrzetwarzanaAukcja->name),
                array('allegro_category',(int)$PrzetwarzanaAukcja->category->id),
                array('allegro_category_name',$SciezkaKategorii_allegro),
                array('allegro_category_shop',(int)$parent),
                array('allegro_options',( count($JakiePromowania) > 0 ? implode(',', (array)$JakiePromowania) : '')),
                array('products_quantity',(int)$IloscDostepnych), 
                array('products_stock_attributes', ''),
                array('products_date_start',$DataStart),
                array('products_date_end',$DataKoniec),
                array('allegro_server',$AllegroRest->polaczenie['CONF_COUNTRY']),
                array('allegro_sandbox',( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'tak' ? '1' : '0' )),
                array('auction_source','1'),
                array('auction_type',$PrzetwarzanaAukcja->sellingMode->format),
                array('auction_date_start',$DataStart),
                array('auction_date_end',$DataKoniec),                
                array('auction_price',(float)$PrzetwarzanaAukcja->sellingMode->price->amount),
                array('auction_seller',(int)$_SESSION['domyslny_uzytkownik_allegro']),
                array('auction_quantity',(int)$IloscDostepnych),
                array('auction_status',$Status),
                array('auction_buy_now','1'),
                array('products_buy_now_price',(float)$PrzetwarzanaAukcja->sellingMode->price->amount),
                array('auction_hits',(int)$IloscWyswietlen),
                array('products_sold',(int)$IloscSprzedanych),
                array('auction_watching',(int)$IloscObserwujacych),
                array('synchronization','0'),
                array('external_id',( isset($PrzetwarzanaAukcja->external->id) ? $PrzetwarzanaAukcja->external->id : '' )),
         );

        if ( file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . floatval($aukcjaID) . '.jpg') ) {
             //
             $pola[] = array('products_image','produkty_allegro/' . floatval($aukcjaID) . '/aukcja_' . floatval($aukcjaID) . '.jpg');
             //
        }
        $db->insert_query('allegro_auctions' , $pola);	
        
        unset($pola, $DataStart, $DataKoniec, $Status, $IloscObserwujacych, $IloscWyswietlen, $IloscDostepnych, $IloscSprzedanych, $JakiePromowania, $SciezkaKategorii_allegro, $parent);
        
        echo '<b>' . $PrzetwarzanaAukcja->id . '</b> - aukcja została przetworzona' . '<br />';

    } else {
      
        echo '<b style="color:#ff0000">' . $aukcjaID . '</b> - aukcja NIE została przetworzona' . '<br />';
      
    }    

}
?>