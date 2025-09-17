<?php
if ( !isset($ImportZewnetrzny) ) {

    chdir('../');
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
}

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
      
$WskaznikPrzeskoku = (((isset($_POST['zakres']) == 'cechy') & ($_POST['zakres'] == 'cechy' || $_POST['zakres'] == 'cena_ilosc')) ? 500 : 50 );

if ((isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) || isset($ImportZewnetrzny) ) {

    // ----------------------- ogolna tablica dostepnosci
    $Dostepnosci = array();
    //
    $zapytanieDostepnosc = "select products_availability_name, products_availability_id, language_id from products_availability_description";
    $sqlc = $db->open_query($zapytanieDostepnosc);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Dostepnosci[$infs['products_availability_id']][$infs['language_id']] = $infs['products_availability_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieDostepnosc);
    // dostepnosci koniec
    
    // ----------------------- ogolna tablica terminow wysylek
    $TerminyWysylek = array();
    //
    $zapytanieTerminyWysylek = "select products_shipping_time_name, products_shipping_time_id, language_id from products_shipping_time_description";
    $sqlc = $db->open_query($zapytanieTerminyWysylek);  
    while ($infs = $sqlc->fetch_assoc()) {
        $TerminyWysylek[$infs['products_shipping_time_id']][$infs['language_id']] = $infs['products_shipping_time_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieTerminyWysylek);
    // terminy wysylek koniec    
    
    // ----------------------- ogolna tablica stanu produktow
    $StanProduktow = array();
    //
    $zapytanieStanProduktow = "select products_condition_name, products_condition_id, language_id from products_condition_description";
    $sqlc = $db->open_query($zapytanieStanProduktow);  
    while ($infs = $sqlc->fetch_assoc()) {
        $StanProduktow[$infs['products_condition_id']][$infs['language_id']] = $infs['products_condition_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieStanProduktow);
    // stan produktow koniec    

    // ----------------------- ogolna tablica gwarancji produktow
    $Gwarancje = array();
    //
    $zapytanieGwarancje = "select products_warranty_name, products_warranty_id, language_id from products_warranty_description";
    $sqlc = $db->open_query($zapytanieGwarancje);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Gwarancje[$infs['products_warranty_id']][$infs['language_id']] = $infs['products_warranty_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieGwarancje);
    // stan gwarancji produktow

    // ----------------------- ogolna tablica jednostek miar
    $JednostkiMiary = array();
    //
    $zapytanieJednostkiMiary = "select products_jm_name, products_jm_id, language_id from products_jm_description";
    $sqlc = $db->open_query($zapytanieJednostkiMiary);  
    while ($infs = $sqlc->fetch_assoc()) {
        $JednostkiMiary[$infs['products_jm_id']][$infs['language_id']] = $infs['products_jm_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieJednostkiMiary);
    // jednostki miary koniec

    // ----------------------- ogolna tablica podatku vat
    $Vat = array();
    //
    $zapytanieVat = "select tax_rates_id, tax_rate from tax_rates";
    $sqlc = $db->open_query($zapytanieVat);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Vat[$infs['tax_rates_id']] = $infs['tax_rate'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieVat);
    // vat koniec

    // ----------------------- ogolna tablica producentow
    $Producenci = array();
    //
    $Producenci[0] = array('nazwa' => '',
                           'producent_pelna_nazwa' => '',
                           'producent_ulica' => '',
                           'producent_kod_pocztowy' => '',
                           'producent_miasto' => '',
                           'producent_kraj' => '',
                           'producent_email' => '',
                           'producent_telefon' => '',
                           'importer_nazwa' => '',
                           'importer_ulica' => '',
                           'importer_kod_pocztowy' => '',
                           'importer_miasto' => '',
                           'importer_kraj' => '',
                           'importer_email' => '',
                           'importer_telefon' => '');     
    //
    $zapytanieProducent = "select * from manufacturers";
    $sqlc = $db->open_query($zapytanieProducent);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Producenci[$infs['manufacturers_id']] = array('nazwa' => $infs['manufacturers_name'],
                                                       'producent_pelna_nazwa' => ((isset($infs['manufacturers_full_name'])) ? $infs['manufacturers_full_name'] : $infs['manufacturers_name']),
                                                       'producent_ulica' => $infs['manufacturers_street'],
                                                       'producent_kod_pocztowy' => $infs['manufacturers_post_code'],
                                                       'producent_miasto' => $infs['manufacturers_city'],
                                                       'producent_kraj' => ((!empty($infs['manufacturers_city'])) ? $infs['manufacturers_country'] : ''),
                                                       'producent_email' => $infs['manufacturers_email'],
                                                       'producent_telefon' => $infs['manufacturers_phone'],
                                                       'importer_nazwa' => $infs['importer_name'],
                                                       'importer_ulica' => $infs['importer_street'],
                                                       'importer_kod_pocztowy' => $infs['importer_post_code'],
                                                       'importer_miasto' => $infs['importer_city'],
                                                       'importer_kraj' => ((!empty($infs['importer_city'])) ? $infs['importer_country'] : ''),
                                                       'importer_email' => $infs['manufacturers_email'],
                                                       'importer_telefon' => $infs['manufacturers_phone']);                                                                  
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieProducent);
    // producenci koniec

    // ----------------------- ogolna tablica walut
    $Walut = array();
    //
    $zapytanieWaluta = "select currencies_id, code from currencies";
    $sqlc = $db->open_query($zapytanieWaluta);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Walut[$infs['currencies_id']] = $infs['code'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieWaluta);
    // waluty koniec
    
    // ----------------------- ogolna tablica starych adresow
    $StareUrlProduktow = array();
    //
    $zapytanieUrl = "select urlf, products_id from location where products_id > 0 and url_type = 'produkt'";
    $sqlc = $db->open_query($zapytanieUrl);  
    while ($infs = $sqlc->fetch_assoc()) {
        $StareUrlProduktow[$infs['products_id']] = $infs['urlf'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieUrl);    
    
    // pobieranie danych konfiguracji exportu
    $zapytanie_konfig = "select code, status from export_configuration";
    $sql_konfig = $db->open_query($zapytanie_konfig);  

    $Konfiguracja = array();
    while ( $info_konfig = $sql_konfig->fetch_assoc() ) {
        //
        $Konfiguracja[ $info_konfig['code'] ] = $info_konfig['status'];
        //
    }
    
    $db->close_query($sql_konfig);
    unset($info_konfig);         

    // jezeli jest eksport produktu w jezyku pl lub wszystkich jezykach
    if (isset($_POST['zakres']) && ($_POST['zakres'] == 'pl' || $_POST['zakres'] == 'wszystkie' || $_POST['zakres'] == 'pl_bez_kategorii' || $_POST['zakres'] == 'wszystkie_bez_kategorii')) {

        // jezeli sa warunki statusu
        $pr_status = '';

        if (isset($_POST['status_produktow']) && (int)$_POST['status_produktow'] == 1) {
            $pr_status = 'products_status = 1';
        }
        if (isset($_POST['status_produktow']) && (int)$_POST['status_produktow'] == 0) {
            $pr_status = 'products_status = 0';
        }        
        if (isset($_POST['status_produktow']) && (int)$_POST['status_produktow'] == 4) {
            $pr_status = 'products_status = 1 and listing_status = 0';
        }
        
        $zapytanie = "select distinct * from products where products_set = 0 " . (($pr_status != '') ? ' and ' . $pr_status : '') . " order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        // jezeli sa warunki
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
            $zapytanie = "select distinct * from products where products_set = 0 and manufacturers_id = '" . (int)$_POST['filtr'] . "'" . (($pr_status != '') ? ' and ' . $pr_status : '') . " order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
            $zapytanie = "select distinct * from products p, products_to_categories pc where p.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "' and p.products_set = 0 " . (($pr_status != '') ? ' and p.' . $pr_status : '') . " order by p.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }    
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'fraza') {
            $fraza = $filtr->process($_POST['filtr']);
            $zapytanie = "select distinct * from products where products_set = 0 and (products_model like '%".$fraza."%' || products_man_code like '%".$fraza."%') " . (($pr_status != '') ? ' and ' . $pr_status : '') . " order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
            unset($fraza);
        } 
        
        // export z tablicy products
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $CoDoZapisania = '';
            $DoZapisaniaXML = '';

            // uchwyt pliku, otwarcie do dopisania
            $fp = fopen($filtr->process($_POST['plik']), "a");
            // blokada pliku do zapisu
            flock($fp, 2);
            
            $Suma = $_POST['limit'];

            // jezeli tylko jezyk polski to tworzy tablice tylko z id polski
            if ((isset($_POST['zakres']) && ($_POST['zakres'] == 'pl' || $_POST['zakres'] == 'pl_bez_kategorii')) || $_POST['format'] == 'xml') {
                $ile_jezykow = array( array('id' => '1','kod' => 'pl') ); 
              } else {            
                $ile_jezykow = Funkcje::TablicaJezykow();
            }
            
            // tablica z nazwy pol dodatkowych  
            $TablicaDodatkowePola = array();
            //
            // jezeli jest tylko jeden jezyk - polski
            if (count($ile_jezykow) == 1) {
                $zapytaniePolaNazwa = "select languages_id, products_extra_fields_id, products_extra_fields_name, products_extra_fields_image from products_extra_fields where products_extra_fields_id in (select distinct products_extra_fields_id from products_to_products_extra_fields where products_extra_fields_value != '') and (languages_id = '0' or languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."') order by languages_id";
               } else {
                $zapytaniePolaNazwa = "select languages_id, products_extra_fields_id, products_extra_fields_name, products_extra_fields_image from products_extra_fields where products_extra_fields_id in (select distinct products_extra_fields_id from products_to_products_extra_fields where products_extra_fields_value != '') order by languages_id";       
            }
            $sqlw = $db->open_query($zapytaniePolaNazwa);                      
            while ($infoPoleNazwa = $sqlw->fetch_assoc()) {
                $TablicaDodatkowePola[ $infoPoleNazwa['products_extra_fields_id'] ] = array( 'jezyk_id' => $infoPoleNazwa['languages_id'],
                                                                                             'pole_id' => $infoPoleNazwa['products_extra_fields_id'],
                                                                                             'nazwa_pola' => $infoPoleNazwa['products_extra_fields_name'],
                                                                                             'pole_foto' => (int)$infoPoleNazwa['products_extra_fields_image'] );
            }
            //
            $db->close_query($sqlw);
            unset($infoPoleNazwa, $zapytaniePolaNazwa);                    
            
            // ---------------------------------------------------------------------------
            // okresla ile jest cech w sklepie zeby nie robic pustych pol - ile maksymalnie maja przypisane produkty cech
            $zapytanieCechy = "select products_id, count(options_id) as ilosc_cech from products_attributes group by products_id order by ilosc_cech desc limit 0,1";        
            $sqlc = $db->open_query($zapytanieCechy);
            $infoCechy = $sqlc->fetch_assoc();
            $ileCech = $infoCechy['ilosc_cech'] + 1; 
            //
            $db->close_query($sqlc);
            unset($infoCechy, $zapytaniePola);             
            // ---------------------------------------------------------------------------            

            while ($info = $sql->fetch_assoc()) {
            
                $DoZapisaniaXML .= '  <Produkt>' . "\r\n";
                $NaglowekCsv = '';
            
                // generowanie kategorii     
                if ( isset($Konfiguracja['Kategoria']) && $Konfiguracja['Kategoria'] == 1 ) {
                    //
                    // do jakiej kategorii nalezy produkt
                    $zapytanieKategoria = "select * from products_to_categories where products_id = '" . (int)$info['products_id'] . "' order by categories_default desc";
                    $sqlc = $db->open_query($zapytanieKategoria);  
                    $infs = $sqlc->fetch_assoc();
                    //
                    if (isset($infs['categories_id']) && (int)$infs['categories_id'] > 0) {
                        $sCiezka = Kategorie::SciezkaKategoriiId((int)$infs['categories_id'], 'categories');
                        $sciezka = explode("_", (string)$sCiezka);          
                      } else {
                        $sciezka = array();
                    }
                    //
                    $db->close_query($sqlc);
                    unset($infs, $zapytanieKategoria);
                    
                    $DoZapisaniaXMLKategorie = '';

                    for ($c = 1; $c < 11; $c++) {
                        //
                        // sprawdza czy jest id
                        if (isset($sciezka[$c - 1])) {
                            $ids = $sciezka[$c - 1];
                          } else {
                            $ids = 999999999;
                        }
                        //
                        for ($w = 0, $cl = count($ile_jezykow); $w < $cl; $w++) {
                            //
                            $zapytanieKategoria = "select * from categories_description cd, categories c where c.categories_id = cd.categories_id and c.categories_id = '" . $ids . "' and cd.language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqlc = $db->open_query($zapytanieKategoria);  
                            $infs = $sqlc->fetch_assoc();                
                            //            
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }
                            
                            $NaglowekCsv .= 'Kategoria_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ( isset($infs['categories_name']) ? Funkcje::CzyszczenieTekstu($infs['categories_name']) : '' ) . '";';
                            
                            if (isset($infs['categories_name']) && !empty($infs['categories_name'])) {
                                $DoZapisaniaXMLKategorie .= Funkcje::CzyszczenieTekstu($infs['categories_name']).'/';
                            }                    

                            // dodatkowe dane do kategorii exportuje tylko jak sa wybrane wszystkie opcje
                            if ($_POST['zakres'] == 'pl' || $_POST['zakres'] == 'wszystkie') {
                                //
                                $NaglowekCsv .= 'Kategoria_'.$c.'_zdjecie;';
                                $CoDoZapisania .= '"' . ((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($infs['categories_image'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . ( isset($infs['categories_image']) ? $infs['categories_image'] : '' ) . '";';               
                      
                                $NaglowekCsv .= 'Kategoria_'.$c.'_opis' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . ( isset($infs['categories_description']) ? Funkcje::CzyszczenieTekstu($infs['categories_description'], false) : '' ) . '";';               
                                
                                $NaglowekCsv .= 'Kategoria_'.$c.'_opis_dol' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . ( isset($infs['categories_description_bottom']) ? Funkcje::CzyszczenieTekstu($infs['categories_description_bottom'], false) : '' ) . '";';                                  
                        
                                $NaglowekCsv .= 'Kategoria_'.$c.'_meta_tytul' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . ( isset($infs['categories_meta_title_tag']) ? Funkcje::CzyszczenieTekstu($infs['categories_meta_title_tag']) : '' ) . '";';                
                         
                                $NaglowekCsv .= 'Kategoria_'.$c.'_meta_opis' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . ( isset($infs['categories_meta_desc_tag']) ? Funkcje::CzyszczenieTekstu($infs['categories_meta_desc_tag']) : '' ) . '";';               
                      
                                $NaglowekCsv .= 'Kategoria_'.$c.'_meta_slowa' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . (  isset($infs['categories_meta_keywords_tag']) ? Funkcje::CzyszczenieTekstu($infs['categories_meta_keywords_tag']) : '' ). '";';
                                
                                $NaglowekCsv .= 'Kategoria_'.$c.'_adres_url' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . ( isset($infs['categories_seo_url']) ? Funkcje::CzyszczenieTekstu($infs['categories_seo_url']) : '' ) . '";';   

                                $NaglowekCsv .= 'Kategoria_'.$c.'_link_kanoniczny' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . ( isset($infs['categories_link_canonical']) ? ADRES_URL_SKLEPU.'/'.Funkcje::CzyszczenieTekstu($infs['categories_link_canonical']) : '' ) . '";';                                   
                                //
                            }

                            $db->close_query($sqlc);
                            unset($infs, $zapytanieKategoria);                
                        }
                    }
                    
                    if ($DoZapisaniaXMLKategorie != '') {
                        //
                        $DoZapisaniaXML .= '      <Kategoria><![CDATA[';
                        $DoZapisaniaXML .= substr((string)$DoZapisaniaXMLKategorie, 0, strlen((string)$DoZapisaniaXMLKategorie)-1) . ']]></Kategoria>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLKategorie);
                    //

                    // url kategorii
                    if ( isset($Konfiguracja['Url_linku_kategorii']) && $Konfiguracja['Url_linku_kategorii'] == 1 && count($sciezka) > 0 ) {
                        //
                        $OstatniaKategoria = $sciezka[ count($sciezka) - 1 ];
                        //
                        $zapytanieKategoria = "select * from categories_description cd, categories c where c.categories_id = cd.categories_id and c.categories_id = '" . $OstatniaKategoria . "' and cd.language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";
                        $sqlc = $db->open_query($zapytanieKategoria);  
                        $infs = $sqlc->fetch_assoc();                          
                        //
                        $LinkSeo = ((isset($infs['categories_seo_url']) && !empty($infs['categories_seo_url'])) ? $infs['categories_seo_url'] : $infs['categories_name']); 
                        //
                        $NaglowekCsv .= 'Url_kategorii;';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(Seo::link_SEO( $LinkSeo, ((SEO_KATEGORIE == 'tak') ? $infs['categories_id'] : implode('_', (array)$sciezka)), 'kategoria', '', false )) . '";';
                        $DoZapisaniaXML .= '      <Url_kategorii>'.Funkcje::CzyszczenieTekstu(Seo::link_SEO( $LinkSeo, ((SEO_KATEGORIE == 'tak') ? $infs['categories_id'] : implode('_', (array)$sciezka)), 'kategoria', '', false ), false).'</Url_kategorii>' . "\r\n";                 
                        //
                        unset($LinkSeo);
                        //
                        $db->close_query($sqlc);
                        unset($infs, $zapytanieKategoria);                           
                        //
                    }                               
                    //
                }
                
                // id produktu
                if ( isset($Konfiguracja['Id_produktu']) && $Konfiguracja['Id_produktu'] == 1 ) {
                    //
                    $NaglowekCsv .= 'Id_produktu;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_id']) . '";';
                    $DoZapisaniaXML .= '      <Id_produktu>'.Funkcje::CzyszczenieTekstu($info['products_id']).'</Id_produktu>' . "\r\n";                 
                    //
                }
                
                // nr katalogowy
                $NaglowekCsv .= 'Nr_katalogowy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_model']) . '";';
                if (!empty($info['products_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_model'], false).']]></Nr_katalogowy>' . "\r\n";
                }      
                
                // generowanie kategorii id  
                if ( isset($Konfiguracja['Kategorie_id']) && $Konfiguracja['Kategorie_id'] == 1 ) {
                    //
                    // do jakiej kategorii nalezy produkt
                    $zapytanieKategoria = "select categories_id from products_to_categories where products_id = '" . (int)$info['products_id'] . "' order by categories_default desc";
                    $sqlc = $db->open_query($zapytanieKategoria);  
                    //
                    $TabKategoriiProduktu = array();
                    //
                    while ($infs = $sqlc->fetch_assoc() ) {
                        //
                        $TabKategoriiProduktu[] = $infs['categories_id'];
                        //
                    }
                    //
                    $db->close_query($sqlc);
                    unset($infs, $zapytanieKategoria);
                    
                    $NaglowekCsv .= 'Kategorie_id;';
                    $CoDoZapisania .= '"' . implode(',', (array)$TabKategoriiProduktu)  . '";';
                    $DoZapisaniaXML .= '      <Kategorie_id>' . implode(',', (array)$TabKategoriiProduktu) . '</Kategorie_id>' . "\r\n";                    
                    //
                    unset($TabKategoriiProdukt);
                    //
                }                
                
                // sortowanie
                if ( isset($Konfiguracja['Sortowanie']) && $Konfiguracja['Sortowanie'] == 1 ) {
                    //
                    $NaglowekCsv .= 'Sortowanie;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['sort_order']) . '";';
                    if (!empty($info['sort_order'])) {
                        $DoZapisaniaXML .= '      <Sortowanie>' . (int)$info['sort_order'] . '</Sortowanie>' . "\r\n";
                    }                   
                    //
                }
                
                // ilosc produktow
                if ( isset($Konfiguracja['Ilosc_produktow']) && $Konfiguracja['Ilosc_produktow'] == 1 ) {
                    //                
                    $NaglowekCsv .= 'Ilosc_produktow;';
                    $CoDoZapisania .= '"' . $info['products_quantity'] . '";';
                    $DoZapisaniaXML .= '      <Ilosc_produktow>'.$info['products_quantity'].'</Ilosc_produktow>' . "\r\n";    
                    //
                }
                
                // alarm magazynowy
                if ( isset($Konfiguracja['Alarm_magazynowy']) && $Konfiguracja['Alarm_magazynowy'] == 1 ) {
                    //                
                    $NaglowekCsv .= 'Alarm_magazynowy;';
                    $CoDoZapisania .= '"' . $info['products_quantity_alarm'] . '";';
                    $DoZapisaniaXML .= '      <Alarm_magazynowy>'.$info['products_quantity'].'</Alarm_magazynowy>' . "\r\n";    
                    //
                }                
                
                // min ilosc zakupow
                if ( isset($Konfiguracja['Min_ilosc_zakupu']) && $Konfiguracja['Min_ilosc_zakupu'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Min_ilosc_zakupu;';
                    $CoDoZapisania .= '"' . $info['products_minorder'] . '";';
                    $DoZapisaniaXML .= '      <Min_ilosc_zakupu>'.$info['products_minorder'].'</Min_ilosc_zakupu>' . "\r\n";     
                    //
                }
                
                // max ilosc zakupow
                if ( isset($Konfiguracja['Max_ilosc_zakupu']) && $Konfiguracja['Max_ilosc_zakupu'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Max_ilosc_zakupu;';
                    $CoDoZapisania .= '"' . $info['products_maxorder'] . '";';
                    $DoZapisaniaXML .= '      <Max_ilosc_zakupu>'.$info['products_maxorder'].'</Max_ilosc_zakupu>' . "\r\n";
                    //
                }
                
                // przyrost ilosci
                if ( isset($Konfiguracja['Przyrost_ilosci']) && $Konfiguracja['Przyrost_ilosci'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Przyrost_ilosci;';
                    $CoDoZapisania .= '"' . $info['products_quantity_order'] . '";';
                    $DoZapisaniaXML .= '      <Przyrost_ilosci>'.$info['products_quantity_order'].'</Przyrost_ilosci>' . "\r\n";                 
                    //
                }
                                
                // waga
                if ( isset($Konfiguracja['Waga']) && $Konfiguracja['Waga'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Waga;';
                    $CoDoZapisania .= '"' . $info['products_weight'] . '";';
                    $DoZapisaniaXML .= '      <Waga>'.$info['products_weight'].'</Waga>' . "\r\n"; 
                    //
                }
                
                // Waga wolumetryczna szerokosc
                if ( isset($Konfiguracja['Waga_wolumetryczna']) && $Konfiguracja['Waga_wolumetryczna'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Waga_wolumetryczna_szerokosc;';
                    $CoDoZapisania .= '"' . $info['products_weight_width'] . '";';
                    $DoZapisaniaXML .= '      <Waga_wolumetryczna_szerokosc>'.$info['products_weight_width'].'</Waga_wolumetryczna_szerokosc>' . "\r\n"; 
                    //
                }                
                
                // Waga wolumetryczna wysokosc
                if ( isset($Konfiguracja['Waga_wolumetryczna']) && $Konfiguracja['Waga_wolumetryczna'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Waga_wolumetryczna_wysokosc;';
                    $CoDoZapisania .= '"' . $info['products_weight_height'] . '";';
                    $DoZapisaniaXML .= '      <Waga_wolumetryczna_wysokosc>'.$info['products_weight_height'].'</Waga_wolumetryczna_wysokosc>' . "\r\n"; 
                    //
                }      
                
                // Waga wolumetryczna dlugosc
                if ( isset($Konfiguracja['Waga_wolumetryczna']) && $Konfiguracja['Waga_wolumetryczna'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Waga_wolumetryczna_dlugosc;';
                    $CoDoZapisania .= '"' . $info['products_weight_length'] . '";';
                    $DoZapisaniaXML .= '      <Waga_wolumetryczna_dlugosc>'.$info['products_weight_length'].'</Waga_wolumetryczna_dlugosc>' . "\r\n"; 
                    //
                }    
                
                // dostepnosc produktu
                if ( isset($Konfiguracja['Data_dostepnosci']) && $Konfiguracja['Data_dostepnosci'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Data_dostepnosci;';
                    //
                    $DataDostepnosci = '';
                    //
                    if ( !empty($info['products_date_available']) && $info['products_date_available'] != '0000-00-00' ) {
                         $DataDostepnosci = date('Y-m-d',FunkcjeWlasnePHP::my_strtotime($info['products_date_available']));
                    }
                    //
                    $CoDoZapisania .= '"' . $DataDostepnosci . '";';
                    //
                    if ( $DataDostepnosci != '' ) {
                         $DoZapisaniaXML .= '      <Data_dostepnosci><![CDATA['.$DataDostepnosci.']]></Data_dostepnosci>' . "\r\n"; 
                    }
                    //
                    unset($DataDostepnosci);
                    //
                }                
                     
                // kod producenta
                if ( isset($Konfiguracja['Kod_producenta']) && $Konfiguracja['Kod_producenta'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Kod_producenta;';
                    $CoDoZapisania .= '"' . $info['products_man_code'] . '";';
                    $DoZapisaniaXML .= '      <Kod_producenta><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_man_code'], false).']]></Kod_producenta>' . "\r\n";
                    //
                }
                
                // informacja o bezpieczenstwie
                if ( isset($Konfiguracja['Informacja_o_bezpieczenstwie']) && $Konfiguracja['Informacja_o_bezpieczenstwie'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Informacja_o_bezpieczenstwie;';
                    $CoDoZapisania .= '"' . $info['products_safety_information'] . '";';
                    $DoZapisaniaXML .= '      <Informacja_o_bezpieczenstwie><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_safety_information'], false).']]></Informacja_o_bezpieczenstwie>' . "\r\n";
                    //
                }
                
                // id produktu programu magazynowego
                if ( isset($Konfiguracja['Id_produktu_magazyn']) && $Konfiguracja['Id_produktu_magazyn'] == 1 ) {
                    //                
                    $NaglowekCsv .= 'Id_produktu_magazyn;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_id_private']) . '";';
                    if (!empty($info['products_id_private'])) {
                        $DoZapisaniaXML .= '      <Id_produktu_magazyn><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_id_private'], false).']]></Id_produktu_magazyn>' . "\r\n";
                    }                
                    //
                }
                
                // nr referencyjne
                if ( isset($Konfiguracja['Nr_referencyjne']) && $Konfiguracja['Nr_referencyjne'] == 1 ) {
                    //                
                    for ( $r = 1; $r < 6; $r++ ) {
                          //
                          $NaglowekCsv .= 'Nr_referencyjny_' . $r . ';';
                          $CoDoZapisania .= '"' . $info['products_reference_number_' . $r] . '";';
                          
                          if ( !empty($info['products_reference_number_' . $r]) ) {
                               $DoZapisaniaXML .= '      <Nr_referencyjny_' . $r . '>'.$info['products_reference_number_' . $r].'</Nr_referencyjny_' . $r . '>' . "\r\n";     
                          }
                          //
                    }
                    //
                }                  
                
                // ean
                if ( isset($Konfiguracja['Kod_ean']) && $Konfiguracja['Kod_ean'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Kod_ean;';
                    $CoDoZapisania .= '"' . $info['products_ean'] . '";';
                    if (!empty($info['products_ean'])) {
                        $DoZapisaniaXML .= '      <Kod_ean><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_ean'], false).']]></Kod_ean>' . "\r\n";
                    }            
                    //
                }

                // kod GTU
                if ( isset($Konfiguracja['Kod_GTU']) && $Konfiguracja['Kod_GTU'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Kod_GTU;';
                    $CoDoZapisania .= '"' . (($info['products_gtu'] != 'brak') ? $info['products_gtu'] : '') . '";';
                    if ($info['products_gtu'] != '') {
                        $DoZapisaniaXML .= '      <Kod_GTU><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_gtu']).']]></Kod_GTU>' . "\r\n";
                    }            
                    //
                }                
                
                // gabaryt
                if ( isset($Konfiguracja['Gabaryt']) && $Konfiguracja['Gabaryt'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Gabaryt;';
                    if ($info['products_pack_type'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Gabaryt>tak</Gabaryt>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Gabaryt>nie</Gabaryt>' . "\r\n";
                    }
                    //
                }
                
                // osobna paczka
                if ( isset($Konfiguracja['Osobna_paczka']) && $Konfiguracja['Osobna_paczka'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Osobna_paczka;';
                    if ($info['products_separate_package'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Osobna_paczka>tak</Osobna_paczka>' . "\r\n";
                    } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Osobna_paczka>nie</Osobna_paczka>' . "\r\n";
                    }
                    //                 
                    $NaglowekCsv .= 'Osobna_paczka_ilosc;';
                    $CoDoZapisania .= '"' . $info['products_separate_package_quantity'] . '";';
                    $DoZapisaniaXML .= '      <Osobna_paczka_ilosc>' . $info['products_separate_package_quantity'] . '</Osobna_paczka_ilosc>' . "\r\n";
                    //
                }                
                                
                // podatek vat
                //
                $StawkaPodatku = '';
                if ( isset($Vat[(int)$info['products_tax_class_id']]) ) {
                    $StawkaPodatku = $Vat[(int)$info['products_tax_class_id']];
                }
                //
                if ( isset($Konfiguracja['Podatek_Vat']) && $Konfiguracja['Podatek_Vat'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Podatek_Vat;';
                    $CoDoZapisania .= '"' . $StawkaPodatku . '";'; 
                    $DoZapisaniaXML .= '      <Podatek_Vat>'.$StawkaPodatku.'</Podatek_Vat>' . "\r\n";
                    //
                }
                //
                unset($StawkaPodatku);
                
                // cena brutto
                if ( isset($Konfiguracja['Cena_brutto']) && $Konfiguracja['Cena_brutto'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Cena_brutto;';
                    $CoDoZapisania .= '"' . $info['products_price_tax'] . '";'; 
                    $DoZapisaniaXML .= '      <Cena_brutto>'.$info['products_price_tax'].'</Cena_brutto>' . "\r\n";
                    //
                }
                  
                // ceny brutto hurtowe
                if ( isset($Konfiguracja['Cena_brutto_x']) && $Konfiguracja['Cena_brutto_x'] == 1 ) {
                    //                     
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_brutto_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_price_tax_'.$x]. '";';
                            if ($info['products_price_tax_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_brutto_'.$x.'>'.$info['products_price_tax_'.$x].'</Cena_brutto_'.$x.'>' . "\r\n";
                            }
                        }
                    }
                    //
                }
                
                // cena netto
                if ( isset($Konfiguracja['Cena_netto']) && $Konfiguracja['Cena_netto'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Cena_netto;';
                    $CoDoZapisania .= '"' . $info['products_price'] . '";'; 
                    $DoZapisaniaXML .= '      <Cena_netto>'.$info['products_price'].'</Cena_netto>' . "\r\n";
                    //
                }
                  
                // ceny netto hurtowe
                if ( isset($Konfiguracja['Cena_netto_x']) && $Konfiguracja['Cena_netto_x'] == 1 ) {
                    //                     
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_netto_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_price_'.$x]. '";';
                            if ($info['products_price_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_netto_'.$x.'>'.$info['products_price_'.$x].'</Cena_netto_'.$x.'>' . "\r\n";
                            }
                        }
                    }
                    //
                }                
                                    
                // cena poprzednia
                if ( isset($Konfiguracja['Cena_poprzednia']) && $Konfiguracja['Cena_poprzednia'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Cena_poprzednia;';
                    $CoDoZapisania .= '"' . $info['products_old_price'] . '";'; 
                    if ($info['products_old_price'] > 0) {
                        $DoZapisaniaXML .= '      <Cena_poprzednia>'.$info['products_old_price'].'</Cena_poprzednia>' . "\r\n";
                    }
                    //
                }
                                  
                // ceny poprzednie hurtowe
                if ( isset($Konfiguracja['Cena_poprzednia_x']) && $Konfiguracja['Cena_poprzednia_x'] == 1 ) {
                    //                      
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_poprzednia_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_old_price_'.$x]. '";';
                            if ($info['products_old_price_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_poprzednia_'.$x.'>'.$info['products_old_price_'.$x].'</Cena_poprzednia_'.$x.'>' . "\r\n";
                            }
                        }
                    }     
                    //
                }
                  
                // cena katalogowa
                if ( isset($Konfiguracja['Cena_katalogowa']) && $Konfiguracja['Cena_katalogowa'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Cena_katalogowa;';
                    $CoDoZapisania .= '"' . $info['products_retail_price'] . '";'; 
                    if ($info['products_retail_price'] > 0) {
                        $DoZapisaniaXML .= '      <Cena_katalogowa>'.$info['products_retail_price'].'</Cena_katalogowa>' . "\r\n";
                    }
                    //
                }
                                  
                // ceny katalogowe hurtowe
                if ( isset($Konfiguracja['Cena_katalogowa_x']) && $Konfiguracja['Cena_katalogowa_x'] == 1 ) {
                    //                      
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_katalogowa_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_retail_price_'.$x]. '";';
                            if ($info['products_retail_price_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_katalogowa_'.$x.'>'.$info['products_retail_price_'.$x].'</Cena_katalogowa_'.$x.'>' . "\r\n";
                            }
                        }
                    }                   
                    //
                }
                
                // cena zakupu
                if ( isset($Konfiguracja['Cena_zakupu']) && $Konfiguracja['Cena_zakupu'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Cena_zakupu;';
                    $CoDoZapisania .= '"' . $info['products_purchase_price'] . '";'; 
                    if ($info['products_purchase_price'] > 0) {
                        $DoZapisaniaXML .= '      <Cena_zakupu>'.$info['products_purchase_price'].'</Cena_zakupu>' . "\r\n";
                    }
                    //
                }                
                  
                // nowosc
                if ( isset($Konfiguracja['Nowosc']) && $Konfiguracja['Nowosc'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Nowosc;';
                    if ($info['new_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Nowosc>tak</Nowosc>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Nowosc>nie</Nowosc>' . "\r\n";
                    }   
                    //
                }
                  
                // nasz hit
                if ( isset($Konfiguracja['Nasz_hit']) && $Konfiguracja['Nasz_hit'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Nasz_hit;';
                    if ($info['star_status'] == 1) {                
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Nasz_hit>tak</Nasz_hit>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Nasz_hit>nie</Nasz_hit>' . "\r\n";
                    }   
                    //
                }
                  
                // polecany
                if ( isset($Konfiguracja['Polecany']) && $Konfiguracja['Polecany'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Polecany;';
                    if ($info['featured_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Polecany>tak</Polecany>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Polecany>nie</Polecany>' . "\r\n";
                    } 
                    //
                }
                  
                // promocja
                if ( isset($Konfiguracja['Promocja']) && $Konfiguracja['Promocja'] == 1 ) {
                    //                    
                    $NaglowekCsv .= 'Promocja;';
                    if ($info['specials_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Promocja>tak</Promocja>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Promocja>nie</Promocja>' . "\r\n";
                    } 
                    //
                    $NaglowekCsv .= 'Promocja_czas_rozpoczecia;';
                    if ($info['specials_status'] == 1) {
                        if ( !empty($info['specials_date_end']) && (int)date('Y', FunkcjeWlasnePHP::my_strtotime($info['specials_date'])) > 2000 ) {
                             $CoDoZapisania .= '"' . date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($info['specials_date'])) . '";';
                             $DoZapisaniaXML .= '      <Promocja_czas_rozpoczecia><![CDATA[' . date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($info['specials_date'])) . ']]></Promocja_czas_rozpoczecia>' . "\r\n";
                          } else {
                             $CoDoZapisania .= '"";';
                        }
                      } else {
                        $CoDoZapisania .= '"";';
                    } 
                    $NaglowekCsv .= 'Promocja_czas_zakonczenia;';
                    if ($info['specials_status'] == 1) {
                        if ( !empty($info['specials_date_end']) && (int)date('Y', FunkcjeWlasnePHP::my_strtotime($info['specials_date_end'])) > 2000 ) {
                             $CoDoZapisania .= '"' . date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($info['specials_date_end'])) . '";';
                             $DoZapisaniaXML .= '      <Promocja_czas_zakonczenia><![CDATA[' . date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($info['specials_date_end'])) . ']]></Promocja_czas_zakonczenia>' . "\r\n";
                          } else {
                             $CoDoZapisania .= '"";';
                        }
                      } else {
                        $CoDoZapisania .= '"";';
                    }                     
                    //                    
                }
                
                // wyprzedaz
                if ( isset($Konfiguracja['Wyprzedaz']) && $Konfiguracja['Wyprzedaz'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Wyprzedaz;';
                    if ($info['sale_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Wyprzedaz>tak</Wyprzedaz>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Wyprzedaz>nie</Wyprzedaz>' . "\r\n";
                    } 
                    //
                }                
                  
                // do porownywarek
                if ( isset($Konfiguracja['Do_porownywarek']) && $Konfiguracja['Do_porownywarek'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Do_porownywarek;';
                    if ($info['export_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Do_porownywarek>tak</Do_porownywarek>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Do_porownywarek>nie</Do_porownywarek>' . "\r\n";
                    }  
                    //
                }
                
                // id porownywarek
                if ( isset($Konfiguracja['Porownywarki_id']) && $Konfiguracja['Porownywarki_id'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Porownywarki_id;';
                    if ($info['export_id'] != '') {
                        //
                        $tmp = explode(',', (string)$info['export_id']);
                        $tab_tmp = array();
                        foreach ( $tmp as $porownywarka ) {
                            //
                            if ( !empty($porownywarka) ) {
                                 $tab_tmp[] = $porownywarka;
                            }
                            //
                        }
                        //
                        $CoDoZapisania .= '"' . implode(',', (array)$tab_tmp) . '";';
                        $DoZapisaniaXML .= '      <Porownywarki_id>' . implode(',', (array)$tab_tmp) . '</Porownywarki_id>' . "\r\n";
                        //
                        unset($tmp, $tab_tmp);
                      } else {
                        $CoDoZapisania .= '"";';
                    }  
                    //
                }             

                // id wysylek
                if ( isset($Konfiguracja['Wysylki_id']) && $Konfiguracja['Wysylki_id'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Wysylki_id;';
                    if ($info['shipping_method'] != '') {
                        //
                        $tmp = explode(';', (string)$info['shipping_method']);
                        $tab_tmp = array();
                        foreach ( $tmp as $wysylka ) {
                            //
                            if ( !empty($wysylka) ) {
                                 $tab_tmp[] = $wysylka;
                            }
                            //
                        }
                        //
                        $CoDoZapisania .= '"' . implode(',', (array)$tab_tmp) . '";';
                        $DoZapisaniaXML .= '      <Wysylki_id>' . implode(',', (array)$tab_tmp) . '</Wysylki_id>' . "\r\n";
                        //
                        unset($tmp, $tab_tmp);
                      } else {
                        $CoDoZapisania .= '"";';
                    }  
                    //
                }                        
                 
                // negocjacja
                if ( isset($Konfiguracja['Negocjacja']) && $Konfiguracja['Negocjacja'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Negocjacja;';
                    if ($info['products_make_an_offer'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Negocjacja>tak</Negocjacja>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Negocjacja>nie</Negocjacja>' . "\r\n";
                    }
                    //
                }
                                 
                // darmowa dostawa
                if ( isset($Konfiguracja['Darmowa_dostawa']) && $Konfiguracja['Darmowa_dostawa'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Darmowa_dostawa;';
                    if ($info['free_shipping_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Darmowa_dostawa>tak</Darmowa_dostawa>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Darmowa_dostawa>nie</Darmowa_dostawa>' . "\r\n";
                    }                
                    //
                }
                
                // Kup teraz do CENEO
                if ( isset($Konfiguracja['Ceneo_kup_teraz']) && $Konfiguracja['Ceneo_kup_teraz'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Ceneo_kup_teraz;';
                    if ($info['export_ceneo_buy_now'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Ceneo_kup_teraz>tak</Ceneo_kup_teraz>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Ceneo_kup_teraz>nie</Ceneo_kup_teraz>' . "\r\n";
                    }                
                    //
                }

                // wykluczona darmowa dostawa
                if ( isset($Konfiguracja['Wykluczona_darmowa_dostawa']) && $Konfiguracja['Wykluczona_darmowa_dostawa'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Wykluczona_darmowa_dostawa;';
                    if ($info['free_shipping_excluded'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Wykluczona_darmowa_dostawa>tak</Wykluczona_darmowa_dostawa>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Wykluczona_darmowa_dostawa>nie</Wykluczona_darmowa_dostawa>' . "\r\n";
                    }                
                    //
                }       
                
                // wykluczony odbior w punkcie
                if ( isset($Konfiguracja['Wykluczony_punkt_odbioru']) && $Konfiguracja['Wykluczony_punkt_odbioru'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Wykluczony_punkt_odbioru;';
                    if ($info['pickup_excluded'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Wykluczony_punkt_odbioru>tak</Wykluczony_punkt_odbioru>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Wykluczony_punkt_odbioru>nie</Wykluczony_punkt_odbioru>' . "\r\n";
                    }                
                    //
                }                   

                // kupowanie
                if ( isset($Konfiguracja['Kupowanie']) && $Konfiguracja['Kupowanie'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Kupowanie;';
                    if ($info['products_buy'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Kupowanie>tak</Kupowanie>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Kupowanie>nie</Kupowanie>' . "\r\n";
                    }   
                    //
                }                

                // dodatkowe ikonki
                if ( isset($Konfiguracja['Dodatkowe_ikonki_zdjec']) && $Konfiguracja['Dodatkowe_ikonki_zdjec'] == 1 ) {
                    //
                    for ( $e = 1; $e < 6; $e++ ) {
                          //
                          $NaglowekCsv .= 'Ikona_' . $e . ';';
                          //
                          if ($info['icon_' . $e . '_status'] == 1) {
                              $CoDoZapisania .= '"tak";';
                              $DoZapisaniaXML .= '      <Ikona_' . $e . '>tak</Ikona_' . $e . '>' . "\r\n";
                            } else {
                              $CoDoZapisania .= '"nie";';
                              $DoZapisaniaXML .= '      <Ikona_' . $e . '>nie</Ikona_' . $e . '>' . "\r\n";
                          }                
                          //
                    }
                    //
                }                  
                
                // kontrola magazynu
                if ( isset($Konfiguracja['Kontrola_magazynu']) && $Konfiguracja['Kontrola_magazynu'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Kontrola_magazynu;';
                    if ($info['products_control_storage'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Kontrola_magazynu>tak</Kontrola_magazynu>' . "\r\n";
                    }
                    if ($info['products_control_storage'] == 0) {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Kontrola_magazynu>nie</Kontrola_magazynu>' . "\r\n";
                    } 
                    if ($info['products_control_storage'] == 2) {
                        $CoDoZapisania .= '"ograniczona";';
                        $DoZapisaniaXML .= '      <Kontrola_magazynu>ograniczona</Kontrola_magazynu>' . "\r\n";
                    }                     
                    //
                }                
                  
                // zdjecie glowne
                if ( isset($Konfiguracja['Zdjecie_glowne']) && $Konfiguracja['Zdjecie_glowne'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Zdjecie_glowne;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($info['products_image'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . $info['products_image']) . '";'; 
                    if (!empty($info['products_image'])) {
                        $DoZapisaniaXML .= '      <Zdjecie_glowne>'.Funkcje::CzyszczenieTekstu(((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($info['products_image'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . $info['products_image']).'</Zdjecie_glowne>' . "\r\n";
                    }            
                    //
                }
                
                // zdjecie glowne - alt
                if ( isset($Konfiguracja['Zdjecia_opis']) && $Konfiguracja['Zdjecia_opis'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Zdjecie_glowne_opis;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_image_description']) . '";'; 
                    if (!empty($info['products_image_description'])) {
                        $DoZapisaniaXML .= '      <Zdjecie_glowne_opis><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_image_description']).']]></Zdjecie_glowne_opis>' . "\r\n";
                    }            
                    //
                }                
                  
                // status
                if ( isset($Konfiguracja['Status']) && $Konfiguracja['Status'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Status;';
                    if ($info['products_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Status>tak</Status>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Status>nie</Status>' . "\r\n";
                    }               
                    //
                }
                
                // notatki produktu
                if ( isset($Konfiguracja['Notatki_produktu']) && $Konfiguracja['Notatki_produktu'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Notatki_produktu;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_adminnotes']) . '";';
                    //
                    if ( trim((string)$info['products_adminnotes']) != '' ) {
                         $DoZapisaniaXML .= '      <Notatki_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_adminnotes'], false).']]></Notatki_produktu>' . "\r\n"; 
                    }
                    //
                    unset($DataDostepnosci);
                    //
                }                  
                
                // export z tablicy products description additional
                
                if ( isset($Konfiguracja['Dodatkowe_opisy_produktu']) && $Konfiguracja['Dodatkowe_opisy_produktu'] == 1 ) {
                
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $zapytanieOpisy = "select distinct * from products_description_additional where products_id = '".$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                        $sqlo = $db->open_query($zapytanieOpisy); 

                        $infoOpisy = $sqlo->fetch_assoc();
                    
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                    
                        $NaglowekCsv .= 'Opis_dodatkowy_1' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_info_description_1']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_info_description_1'], false) : '' ) . '";';
                        if (!empty($infoOpisy['products_info_description_1'])) {
                            $DoZapisaniaXML .= '      <Opis_dodatkowy_1><![CDATA['.$infoOpisy['products_info_description_1'].']]></Opis_dodatkowy_1>' . "\r\n";
                        }                 
                        //                      
                        $NaglowekCsv .= 'Opis_dodatkowy_2' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_info_description_2']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_info_description_2'], false) : '' ) . '";';
                        if (!empty($infoOpisy['products_info_description_2'])) {
                            $DoZapisaniaXML .= '      <Opis_dodatkowy_2><![CDATA['.$infoOpisy['products_info_description_2'].']]></Opis_dodatkowy_2>' . "\r\n";
                        }                 
                        //
                    
                        $db->close_query($sqlo);
                        unset($infoOpisy);        
                        //
                    }
                    
                }
                 
                // export z tablicy products description
                
                for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                    //
                    $zapytanieOpisy = "select distinct * from products_description where products_id = '".$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqlo = $db->open_query($zapytanieOpisy); 

                    $infoOpisy = $sqlo->fetch_assoc();
                    
                    $Przedrostek = '';
                    if ($ile_jezykow[$w]['kod'] != 'pl') {
                        $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                    }
                    
                    // url produktu
                    if ( isset($Konfiguracja['Url_linku_produktu']) && $Konfiguracja['Url_linku_produktu'] == 1 && $Przedrostek == '' ) {
                        //
                        $LinkSeo = ((!empty($infoOpisy['products_seo_url'])) ? $infoOpisy['products_seo_url'] : $infoOpisy['products_name']); 
                        //
                        $NaglowekCsv .= 'Url_produktu;';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(Seo::link_SEO( $LinkSeo, $info['products_id'], 'produkt', '', false )) . '";';
                        $DoZapisaniaXML .= '      <Url_produktu>'.Funkcje::CzyszczenieTekstu(Seo::link_SEO( $LinkSeo, $info['products_id'], 'produkt', '', false )).'</Url_produktu>' . "\r\n";                 
                        //
                        unset($LinkSeo);
                        //
                    }                
                    
                    // nazwa produktu
                    if ( isset($Konfiguracja['Nazwa_produktu']) && $Konfiguracja['Nazwa_produktu'] == 1 ) {
                        //                        
                        $NaglowekCsv .= 'Nazwa_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_name']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_name']) : '' ) . '";';
                        if (!empty($infoOpisy['products_name'])) {
                            $DoZapisaniaXML .= '      <Nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_name'], false).']]></Nazwa_produktu>' . "\r\n";
                        }   
                        //
                    }

                    // nazwa produktu dodatkowa
                    if ( isset($Konfiguracja['Dodatkowa_nazwa_produktu']) && $Konfiguracja['Dodatkowa_nazwa_produktu'] == 1 ) {
                        //                    
                        $NaglowekCsv .= 'Dodatkowa_nazwa_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_name_info']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_name_info']) : '' ) . '";';
                        if (!empty($infoOpisy['products_name_info'])) {
                            $DoZapisaniaXML .= '      <Dodatkowa_nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_name_info'], false).']]></Dodatkowa_nazwa_produktu>' . "\r\n";
                        }                        
                        //
                    }

                    // nazwa produktu dodatkowa
                    if ( isset($Konfiguracja['Krotka_nazwa_produktu']) && $Konfiguracja['Krotka_nazwa_produktu'] == 1 ) {
                        //                    
                        $NaglowekCsv .= 'Krotka_nazwa_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_name_short']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_name_short']) : '' ) . '";';
                        if (!empty($infoOpisy['products_name_short'])) {
                            $DoZapisaniaXML .= '      <Krotka_nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_name_short'], false).']]></Krotka_nazwa_produktu>' . "\r\n";
                        }                        
                        //
                    }
                    
                    // opis
                    if ( isset($Konfiguracja['Opis']) && $Konfiguracja['Opis'] == 1 ) {
                        //                     
                        $NaglowekCsv .= 'Opis' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_description']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_description'], false) : '' ) . '";';
                        if (!empty($infoOpisy['products_description'])) {
                            $DoZapisaniaXML .= '      <Opis><![CDATA['.$infoOpisy['products_description'].']]></Opis>' . "\r\n";
                        }                
                        //
                    }

                    // opis krotki
                    if ( isset($Konfiguracja['Opis_krotki']) && $Konfiguracja['Opis_krotki'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Opis_krotki' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_short_description']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_short_description'], false) : '' ) . '";';
                        if (!empty($infoOpisy['products_short_description'])) {
                            $DoZapisaniaXML .= '      <Opis_krotki><![CDATA['.$infoOpisy['products_short_description'].']]></Opis_krotki>' . "\r\n";
                        }                 
                        //
                    }

                    // meta tytul
                    if ( isset($Konfiguracja['Meta_tytul']) && $Konfiguracja['Meta_tytul'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Meta_tytul' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_meta_title_tag']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_title_tag']) : '' ) . '";';
                        if (!empty($infoOpisy['products_meta_title_tag'])) {
                            $DoZapisaniaXML .= '      <Meta_tytul><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_title_tag'], false).']]></Meta_tytul>' . "\r\n";
                        }                
                        //
                    }

                    // meta opis
                    if ( isset($Konfiguracja['Meta_opis']) && $Konfiguracja['Meta_opis'] == 1 ) {
                        //                     
                        $NaglowekCsv .= 'Meta_opis' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_meta_desc_tag']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_desc_tag']) : '' ) . '";';   
                        if (!empty($infoOpisy['products_meta_desc_tag'])) {
                            $DoZapisaniaXML .= '      <Meta_opis><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_desc_tag'], false).']]></Meta_opis>' . "\r\n";
                        } 
                        //
                    }
                    
                    // meta slowa
                    if ( isset($Konfiguracja['Meta_slowa']) && $Konfiguracja['Meta_slowa'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Meta_slowa' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_meta_keywords_tag']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_keywords_tag']) : '' ) . '";'; 
                        if (!empty($infoOpisy['products_meta_keywords_tag'])) {
                            $DoZapisaniaXML .= '      <Meta_slowa><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_keywords_tag'], false).']]></Meta_slowa>' . "\r\n";
                        }
                        //
                    }
                    
                    // tagi szukania
                    if ( isset($Konfiguracja['Tagi_szukania']) && $Konfiguracja['Tagi_szukania'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Tagi_szukania' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_search_tag']) ? Funkcje::CzyszczenieTekstu($infoOpisy['products_search_tag']) : '' ) . '";'; 
                        if (!empty($infoOpisy['products_search_tag'])) {
                            $DoZapisaniaXML .= '      <Tagi_szukania><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_search_tag'], false).']]></Tagi_szukania>' . "\r\n";
                        }
                        //
                    }                     
                    
                    // link kanoniczny
                    if ( isset($Konfiguracja['Link_kanoniczny']) && $Konfiguracja['Link_kanoniczny'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Link_kanoniczny' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . ( isset($infoOpisy['products_link_canonical']) ? ADRES_URL_SKLEPU.'/'.Funkcje::CzyszczenieTekstu($infoOpisy['products_link_canonical']) : '' ) . '";'; 
                        if (!empty($infoOpisy['products_link_canonical'])) {
                            $DoZapisaniaXML .= '      <Link_kanoniczny><![CDATA['.ADRES_URL_SKLEPU.'/'.Funkcje::CzyszczenieTekstu($infoOpisy['products_link_canonical']).']]></Link_kanoniczny>' . "\r\n";
                        }
                        //
                    }                    
                                        
                    $db->close_query($sqlo);
                    unset($infoOpisy);        
                    //
                }
                
                // poprzedni adres w innym sklepie
                if ( isset($Konfiguracja['Stary_URL']) && $Konfiguracja['Stary_URL'] == 1 ) {
                    //                      
                    $NaglowekCsv .= 'Stary_URL;';
                    $CoDoZapisania .= '"' . ( isset($StareUrlProduktow[(int)$info['products_id']]) ? Funkcje::CzyszczenieTekstu($StareUrlProduktow[(int)$info['products_id']]) : '' ) . '";'; 
                    if (isset($StareUrlProduktow[(int)$info['products_id']])) {
                        $DoZapisaniaXML .= '      <Stary_URL><![CDATA['.Funkcje::CzyszczenieTekstu($StareUrlProduktow[(int)$info['products_id']]).']]></Stary_URL>' . "\r\n";
                    }
                    //
                }              

                // jednostka miary
                if ( isset($Konfiguracja['Jednostka_miary']) && $Konfiguracja['Jednostka_miary'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $NazwaJednostkiMiary = '';
                        if ( isset($JednostkiMiary[(int)$info['products_jm_id']][$ile_jezykow[$w]['id']]) ) {
                            //
                            $NazwaJednostkiMiary = $JednostkiMiary[(int)$info['products_jm_id']][1];
                            //
                        }                       
                        
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                        
                        $NaglowekCsv .= 'Jednostka_miary' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . $NazwaJednostkiMiary . '";';
                        if (!empty($NazwaJednostkiMiary)) {
                            $DoZapisaniaXML .= '      <Jednostka_miary><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaJednostkiMiary, false).']]></Jednostka_miary>' . "\r\n";
                        }                
                        
                        unset($NazwaJednostkiMiary);
                        //
                    }
                    //
                }
                
                // Rozmiar / pojemność
                if ( isset($Konfiguracja['Rozmiar_pojemnosc']) && $Konfiguracja['Rozmiar_pojemnosc'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Rozmiar_pojemnosc;';
                    $CoDoZapisania .= '"' . ($info['products_size'] != '' ? $info['products_size'] : '') . '";';
                    if ($info['products_size'] != '') {
                        $DoZapisaniaXML .= '      <Rozmiar_pojemnosc><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_size'], false).']]></Rozmiar_pojemnosc>' . "\r\n";
                    }            
                    //
                }                
                
                // Jednostka rozmiaru
                if ( isset($Konfiguracja['Jednostka_rozmiaru']) && $Konfiguracja['Jednostka_rozmiaru'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Jednostka_rozmiaru;';
                    $CoDoZapisania .= '"' . ($info['products_size_type'] != '' ? $info['products_size_type'] : '') . '";';
                    if ($info['products_size_type'] != '') {
                        $DoZapisaniaXML .= '      <Jednostka_rozmiaru><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_size_type'], false).']]></Jednostka_rozmiaru>' . "\r\n";
                    }            
                    //
                }                

                // termin wysylki
                if ( isset($Konfiguracja['Termin_wysylki']) && $Konfiguracja['Termin_wysylki'] == 1 ) {
                    //                 
                    $NazwaTerminuWysylki = '';
                    if ( isset($TerminyWysylek[(int)$info['products_shipping_time_id']][ $_SESSION['domyslny_jezyk']['id'] ]) ) {
                        //
                        $NazwaTerminuWysylki = $TerminyWysylek[(int)$info['products_shipping_time_id']][ $_SESSION['domyslny_jezyk']['id'] ];
                        //
                    }                       

                    $NaglowekCsv .= 'Termin_wysylki;';
                    $CoDoZapisania .= '"' . $NazwaTerminuWysylki . '";';
                    if (!empty($NazwaTerminuWysylki)) {
                        $DoZapisaniaXML .= '      <Termin_wysylki><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaTerminuWysylki, false).']]></Termin_wysylki>' . "\r\n";
                    }                
                    
                    unset($NazwaTerminuWysylki);
                    //
                }   

                // stan produktow
                if ( isset($Konfiguracja['Stan_produktu']) && $Konfiguracja['Stan_produktu'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $NazwaStanProduktu = '';
                        if ( isset($StanProduktow[(int)$info['products_condition_products_id']][$ile_jezykow[$w]['id']]) ) {
                            //
                            $NazwaStanProduktu = $StanProduktow[(int)$info['products_condition_products_id']][1];
                            //
                        }                       
                        
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                        
                        $NaglowekCsv .= 'Stan_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . $NazwaStanProduktu . '";';
                        if (!empty($NazwaStanProduktu)) {
                            $DoZapisaniaXML .= '      <Stan_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaStanProduktu, false).']]></Stan_produktu>' . "\r\n";
                        }                
                        
                        unset($NazwaStanProduktu);
                        //
                    }
                    //
                }     

                // gwarancje
                if ( isset($Konfiguracja['Gwarancja']) && $Konfiguracja['Gwarancja'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $NazwaGwarancja = '';
                        if ( isset($Gwarancje[(int)$info['products_warranty_products_id']][$ile_jezykow[$w]['id']]) ) {
                            //
                            $NazwaGwarancja = $Gwarancje[(int)$info['products_warranty_products_id']][1];
                            //
                        }                       
                        
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                        
                        $NaglowekCsv .= 'Gwarancja' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . $NazwaGwarancja . '";';
                        if (!empty($NazwaGwarancja)) {
                            $DoZapisaniaXML .= '      <Gwarancja><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaGwarancja, false).']]></Gwarancja>' . "\r\n";
                        }                
                        
                        unset($NazwaGwarancja);
                        //
                    }
                    //
                }    

                // rodzaj produktu
                if ( isset($Konfiguracja['Rodzaj_produktu']) && $Konfiguracja['Rodzaj_produktu'] == 1 ) {
                    //
                    $NaglowekCsv .= 'Rodzaj_produktu;';
                    $CoDoZapisania .= '"' . $info['products_type'] . '";';
                    $DoZapisaniaXML .= '      <Rodzaj_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_type'], false).']]></Rodzaj_produktu>' . "\r\n";              
                    //
                }                  
                
                // dostepnosc
                if ( isset($Konfiguracja['Dostepnosc']) && $Konfiguracja['Dostepnosc'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }            
                        //
                        if ((int)$info['products_availability_id'] != '99999') {
                            //        
                            $NazwaDostepnosci = '';
                            if ( isset($Dostepnosci[(int)$info['products_availability_id']][$ile_jezykow[$w]['id']]) ) {
                                //
                                $NazwaDostepnosci = $Dostepnosci[(int)$info['products_availability_id']][1];
                                //
                            }                        

                            $NaglowekCsv .= 'Dostepnosc' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaDostepnosci) . '";';
                            if (!empty($NazwaDostepnosci)) {
                                $DoZapisaniaXML .= '      <Dostepnosc><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaDostepnosci, false).']]></Dostepnosc>' . "\r\n";
                            }                     
                            
                            unset($NazwaDostepnosci);
                            //
                           } else {
                            //
                            $NaglowekCsv .= 'Dostepnosc' . $Przedrostek . ';';
                            $CoDoZapisania .= '"AUTOMATYCZNY";';  
                            $DoZapisaniaXML .= '      <Dostepnosc><![CDATA[AUTOMATYCZNY]]></Dostepnosc>' . "\r\n";
                            //
                        }                
                    }       
                    //
                }
                
                // producent
                if ( isset($Konfiguracja['Producent']) && $Konfiguracja['Producent'] == 1 ) {
                    //                  
                    $NazwaProducenta = '';
                    if ( isset($Producenci[(int)$info['manufacturers_id']]) ) {
                         $NazwaProducenta = $Producenci[(int)$info['manufacturers_id']]['nazwa'];
                    }
                    //
                    $NaglowekCsv .= 'Producent;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaProducenta) . '";'; 
                    if (!empty($NazwaProducenta)) {
                        $DoZapisaniaXML .= '      <Producent><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaProducenta, false).']]></Producent>' . "\r\n";
                    }            
                    //
                    unset($NazwaProducenta);
                    //
                }
                
                // url producenta
                if ( isset($Konfiguracja['Url_linku_producenta']) && $Konfiguracja['Url_linku_producenta'] == 1 ) {
                    //                  
                    $NazwaProducenta = '';
                    if ( isset($Producenci[(int)$info['manufacturers_id']]) ) {
                         $NazwaProducenta = $Producenci[(int)$info['manufacturers_id']]['nazwa'];
                    }
                    //
                    $NaglowekCsv .= 'Url_producenta;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(Seo::link_SEO( $NazwaProducenta, $info['manufacturers_id'], 'producent', '', false )) . '";';
                    $DoZapisaniaXML .= '      <Url_producenta>'.Funkcje::CzyszczenieTekstu(Seo::link_SEO( $NazwaProducenta, $info['manufacturers_id'], 'producent', '', false )).'</Url_producenta>' . "\r\n";                 
                    //
                    unset($NazwaProducenta);
                    //
                }            

                // dane gpsr
                if ( isset($Konfiguracja['Informacja_o_GPSR']) && $Konfiguracja['Informacja_o_GPSR'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'GPSR_producent_nazwa;GPSR_producent_ulica;GPSR_producent_kod_pocztowy;GPSR_producent_miasto;GPSR_producent_kraj;GPSR_producent_email;GPSR_producent_telefon;GPSR_importer_nazwa;GPSR_importer_ulica;GPSR_importer_kod_pocztowy;GPSR_importer_miasto;GPSR_importer_kraj;GPSR_importer_email;GPSR_importer_telefon;';
                    //
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_pelna_nazwa']) . '";'; 
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_ulica']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_kod_pocztowy']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_miasto']) . '";'; 
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_kraj']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_email']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_telefon']) . '";'; 
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_nazwa']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_ulica']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_kod_pocztowy']) . '";'; 
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_miasto']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_kraj']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_email']) . '";';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_telefon']) . '";';
                    //
                    $DoZapisaniaXML .= '      <Dane_GPSR>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_nazwa><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_pelna_nazwa'], false).']]></Producent_nazwa>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_ulica><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_ulica'], false).']]></Producent_ulica>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_kod_pocztowy><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_kod_pocztowy'], false).']]></Producent_kod_pocztowy>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_miasto><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_miasto'], false).']]></Producent_miasto>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_kraj><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_kraj'], false).']]></Producent_kraj>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_email><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_email'], false).']]></Producent_email>' . "\r\n";
                    $DoZapisaniaXML .= '          <Producent_telefon><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['producent_telefon'], false).']]></Producent_telefon>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_nazwa><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_nazwa'], false).']]></Importer_nazwa>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_ulica><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_ulica'], false).']]></Importer_ulica>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_kod_pocztowy><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_kod_pocztowy'], false).']]></Importer_kod_pocztowy>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_miasto><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_miasto'], false).']]></Importer_miasto>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_kraj><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_kraj'], false).']]></Importer_kraj>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_email><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_email'], false).']]></Importer_email>' . "\r\n";
                    $DoZapisaniaXML .= '          <Importer_telefon><![CDATA['.Funkcje::CzyszczenieTekstu($Producenci[$info['manufacturers_id']]['importer_telefon'], false).']]></Importer_telefon>' . "\r\n";
                    $DoZapisaniaXML .= '      </Dane_GPSR>' . "\r\n";         
                    //
                }

                // waluta
                if ( isset($Konfiguracja['Waluta']) && $Konfiguracja['Waluta'] == 1 ) {
                    //                  
                    $KodWaluty = '';
                    if ( isset($Walut[(int)$info['products_currencies_id']]) ) {
                         $KodWaluty = $Walut[(int)$info['products_currencies_id']];
                    }
                    //
                    $NaglowekCsv .= 'Waluta;';
                    $CoDoZapisania .= '"' . $KodWaluty . '";';
                    $DoZapisaniaXML .= '      <Waluta><![CDATA['.$KodWaluty.']]></Waluta>' . "\r\n";            
                    //
                    unset($KodWaluty);
                    //
                }
                
                // klasa energetyczna
                if ( isset($Konfiguracja['Klasa_energetyczna']) && $Konfiguracja['Klasa_energetyczna'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Klasa_energetyczna;';
                    $CoDoZapisania .= '"' . $info['products_energy'] . '";';                    
                    if ( $info['products_energy'] != '' ) {
                         $DoZapisaniaXML .= '      <Klasa_energetyczna><![CDATA[' . $info['products_energy'] . ']]></Klasa_energetyczna>' . "\r\n";            
                    }
                    //                  
                    $NaglowekCsv .= 'Klasa_energetyczna_min;';
                    $CoDoZapisania .= '"' . $info['products_min_energy'] . '";';                    
                    if ( $info['products_energy'] != '' && $info['products_min_energy'] != '' ) {
                         $DoZapisaniaXML .= '      <Klasa_energetyczna_min><![CDATA[' . $info['products_min_energy'] . ']]></Klasa_energetyczna_min>' . "\r\n";            
                    }
                    //                  
                    $NaglowekCsv .= 'Klasa_energetyczna_max;';
                    $CoDoZapisania .= '"' . $info['products_max_energy'] . '";';                    
                    if ( $info['products_energy'] != '' && $info['products_max_energy'] != '' ) {
                         $DoZapisaniaXML .= '      <Klasa_energetyczna_max><![CDATA[' . $info['products_max_energy'] . ']]></Klasa_energetyczna_max>' . "\r\n";            
                    }
                    //                  
                    $NaglowekCsv .= 'Klasa_energetyczna_grafika;';
                    $CoDoZapisania .= '"' . $info['products_energy_img'] . '";';                    
                    if ( $info['products_energy'] != '' && $info['products_energy_img'] != '' ) {
                         $DoZapisaniaXML .= '      <Klasa_energetyczna_grafika><![CDATA[' . $info['products_energy_img'] . ']]></Klasa_energetyczna_grafika>' . "\r\n";            
                    }
                    //                  
                    $NaglowekCsv .= 'Klasa_energetyczna_pdf;';
                    $CoDoZapisania .= '"' . $info['products_energy_pdf'] . '";';                    
                    if ( $info['products_energy'] != '' && $info['products_energy_pdf'] != '' ) {
                         $DoZapisaniaXML .= '      <Klasa_energetyczna_pdf><![CDATA[' . $info['products_energy_pdf'] . ']]></Klasa_energetyczna_pdf>' . "\r\n";            
                    }
                    //
                }     

                // inne warianty
                if ( isset($Konfiguracja['Inne_warianty']) && $Konfiguracja['Inne_warianty'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Wariant_tekst;';
                    $CoDoZapisania .= '"' . $info['products_other_variant_text'] . '";';                    
                    if ( $info['products_other_variant_text'] != '' ) {
                         $DoZapisaniaXML .= '      <Wariant_tekst><![CDATA[' . $info['products_other_variant_text'] . ']]></Wariant_tekst>' . "\r\n";            
                    }
                    //                  
                    $NaglowekCsv .= 'Wariant_opcja;';
                    $CoDoZapisania .= '"' . $info['products_other_variant_range'] . '";';                    
                    if ( $info['products_other_variant_text'] != '' && $info['products_other_variant_range'] != '' ) {
                         $DoZapisaniaXML .= '      <Wariant_opcja><![CDATA[' . $info['products_other_variant_range'] . ']]></Wariant_opcja>' . "\r\n";            
                    }
                    //                  
                    $NaglowekCsv .= 'Wariant_sposob;';
                    $CoDoZapisania .= '"' . $info['products_other_variant_method'] . '";';                    
                    if ( $info['products_other_variant_text'] != '' && $info['products_other_variant_method'] != '' ) {
                         $DoZapisaniaXML .= '      <Wariant_sposob><![CDATA[' . $info['products_other_variant_method'] . ']]></Wariant_sposob>' . "\r\n";            
                    }
                    //
                }                    
                
                // dodatkowe zdjecia
                if ( isset($Konfiguracja['Zdjecia_dodatkowe']) && $Konfiguracja['Zdjecia_dodatkowe'] == 1 ) {
                    //                 
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($w = 1; $w < 11; $w++) {
                        //
                        $zapytanieZdjecie = "select popup_images, images_description from additional_images where products_id = '" . (int)$info['products_id'] . "' limit ".($w-1).",1";
                        $sqlc = $db->open_query($zapytanieZdjecie);  
                        $infs = $sqlc->fetch_assoc();
                        
                        $NaglowekCsv .= 'Zdjecie_dodatkowe_' . $w . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($infs['popup_images'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . ( isset($infs['popup_images']) ? $infs['popup_images'] : '' )) . '";';
                        
                        if ( isset($Konfiguracja['Zdjecia_opis']) && $Konfiguracja['Zdjecia_opis'] == 1 ) {
                          
                            $NaglowekCsv .= 'Zdjecie_dodatkowe_opis_' . $w . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['images_description'])) ? Funkcje::CzyszczenieTekstu($infs['images_description'], false) : '') . '";';
                            
                        }
                        
                        if (!empty($infs['popup_images'])) {
                          
                            $DoZapisaniaXMLTmp .= '          <Zdjecie>' . "\r\n";
                            
                            $DoZapisaniaXMLTmp .= '              <Zdjecie_link>' . Funkcje::CzyszczenieTekstu(((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($infs['popup_images'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . $infs['popup_images']).'</Zdjecie_link>' . "\r\n";
                            
                            if ( isset($Konfiguracja['Zdjecia_opis']) && $Konfiguracja['Zdjecia_opis'] == 1 ) {
                              
                                if (isset($infs['images_description']) && !empty($infs['images_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Zdjecie_opis><![CDATA['.((isset($infs['images_description'])) ? Funkcje::CzyszczenieTekstu($infs['images_description'], false) : '').']]></Zdjecie_opis>' . "\r\n";
                                }                                 
                              
                            }
                            
                            $DoZapisaniaXMLTmp .= '          </Zdjecie>' . "\r\n";
                            
                        }                
                        
                        $db->close_query($sqlc);
                        unset($infs, $zapytanieZdjecie);
                        //
                    }                       
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Zdjecia_dodatkowe>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Zdjecia_dodatkowe>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);            
                    //
                }
                
                // dodatkowe zakladki
                if ( isset($Konfiguracja['Dodatkowe_zakladki']) && $Konfiguracja['Dodatkowe_zakladki'] == 1 ) {
                    //                  
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieZakladka = "select distinct * from products_info where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_info_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieZakladka);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Dodatkowa_zakladka_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ( isset($infs['products_info_name']) ? Funkcje::CzyszczenieTekstu($infs['products_info_name']) : '' ) . '";'; 
                            
                            $NaglowekCsv .= 'Dodatkowa_zakladka_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ( isset($infs['products_info_description']) ? Funkcje::CzyszczenieTekstu($infs['products_info_description'], false) : '' ) . '";';
                            
                            if (isset($infs['products_info_name']) && !empty($infs['products_info_name']) && isset($infs['products_info_description']) && !empty($infs['products_info_description'])) {
                                $DoZapisaniaXMLTmp .= '          <Dodatkowa_zakladka>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_info_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_info_description'].']]></Opis>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '          </Dodatkowa_zakladka>' . "\r\n";
                            }           

                            $db->close_query($sqlc);
                            unset($infs, $zapytanieZakladka);
                            //          
                        }
                    }
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Dodatkowe_zakladki>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Dodatkowe_zakladki>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);            
                    //
                }
                
                // linki
                if ( isset($Konfiguracja['Linki']) && $Konfiguracja['Linki'] == 1 ) {
                    //                 
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieLinki = "select distinct * from products_link where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_link_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieLinki);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Link_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_link_name'])) ? Funkcje::CzyszczenieTekstu($infs['products_link_name']) : '') . '";'; 
                            
                            $NaglowekCsv .= 'Link_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_link_description'])) ? Funkcje::CzyszczenieTekstu($infs['products_link_description']) : '') . '";';                      
                            
                            // adres url tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Link_'.$c.'_url;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_link_url'])) ? Funkcje::CzyszczenieTekstu($infs['products_link_url']) : '') . '";';                 
                            }
                            
                            if (isset($infs['products_link_name']) && !empty($infs['products_link_name']) && isset($infs['products_link_url']) && !empty($infs['products_link_url'])) {
                                $DoZapisaniaXMLTmp .= '          <Link>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_link_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Url><![CDATA['.$infs['products_link_url'].']]></Url>' . "\r\n";
                                
                                if (!empty($infs['products_link_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_link_description'].']]></Opis>' . "\r\n";
                                }                        
                                
                                $DoZapisaniaXMLTmp .= '          </Link>' . "\r\n";
                            }                    
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytanieLinki);
                            //          
                        }
                    }    
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Linki>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Linki>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);        
                    //
                }

                // filmy youtube
                if ( isset($Konfiguracja['Youtube']) && $Konfiguracja['Youtube'] == 1 ) {
                    //                    
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieYoutube = "select distinct * from products_youtube where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieYoutube);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Youtube_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_film_name'])) ? Funkcje::CzyszczenieTekstu($infs['products_film_name']) : '') . '";'; 
                            
                            // adres url tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Youtube_'.$c.'_url;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_film_url'])) ? Funkcje::CzyszczenieTekstu($infs['products_film_url']) : '') . '";';                 
                            }
                            
                            $NaglowekCsv .= 'Youtube_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_film_description'])) ? Funkcje::CzyszczenieTekstu($infs['products_film_description']) : '') . '";';  

                            // szerokosc i wysokosc tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Youtube_'.$c.'_szerokosc;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_film_width'])) ? (int)$infs['products_film_width'] : '') . '";';  
                                $NaglowekCsv .= 'Youtube_'.$c.'_wysokosc;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_film_width'])) ? (int)$infs['products_film_height'] : '') . '";';                         
                            }                    
                            
                            if (isset($infs['products_film_name']) && !empty($infs['products_film_name']) && isset($infs['products_film_url']) && !empty($infs['products_film_url'])) {
                                $DoZapisaniaXMLTmp .= '          <Youtube>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_film_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Url><![CDATA['.$infs['products_film_url'].']]></Url>' . "\r\n";
                                
                                if (!empty($infs['products_film_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_film_description'].']]></Opis>' . "\r\n";
                                }
                                if (!empty($infs['products_film_width'])) {
                                    $DoZapisaniaXMLTmp .= '              <Szerokosc>'.$infs['products_film_width'].'</Szerokosc>' . "\r\n";
                                }
                                if (!empty($infs['products_film_height'])) {
                                    $DoZapisaniaXMLTmp .= '              <Wysokosc>'.$infs['products_film_height'].'</Wysokosc>' . "\r\n";
                                }
                                $DoZapisaniaXMLTmp .= '          </Youtube>' . "\r\n";
                            }                    
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytanieYoutube);
                            //          
                        }
                    }    
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Filmy_youtube>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Filmy_youtube>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);                 
                    //
                }
                
                // filmy flv
                if ( isset($Konfiguracja['Filmy_FLV']) && $Konfiguracja['Filmy_FLV'] == 1 ) {
                    //                     
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieFlv = "select distinct * from products_film where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieFlv);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Film_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_film_name'])) ? Funkcje::CzyszczenieTekstu($infs['products_film_name']) : '') . '";'; 
                            
                            // adres pliku tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Film_'.$c.'_plik;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_film_file'])) ? Funkcje::CzyszczenieTekstu($infs['products_film_file']) : '') . '";';                 
                            }
                            
                            $NaglowekCsv .= 'Film_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_film_description'])) ? Funkcje::CzyszczenieTekstu($infs['products_film_description']) : '') . '";';  

                            // szerokosc i wysokosc tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {                                            
                                $NaglowekCsv .= 'Film_'.$c.'_szerokosc;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_film_width'])) ? (int)$infs['products_film_width'] : 0) . '";';  
                                $NaglowekCsv .= 'Film_'.$c.'_wysokosc;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_film_height'])) ? (int)$infs['products_film_height'] : 0) . '";';                         
                            }                    
                            
                            if (isset($infs['products_film_name']) && !empty($infs['products_film_name']) && isset($infs['products_film_file']) && !empty($infs['products_film_file'])) {
                                $DoZapisaniaXMLTmp .= '          <Film>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_film_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_film_file'].'</Plik>' . "\r\n";
                                
                                if (!empty($infs['products_film_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_film_description'].']]></Opis>' . "\r\n";
                                }                 
                                if (!empty($infs['products_film_width'])) {
                                    $DoZapisaniaXMLTmp .= '              <Szerokosc>'.$infs['products_film_width'].'</Szerokosc>' . "\r\n";
                                }
                                if (!empty($infs['products_film_height'])) {
                                    $DoZapisaniaXMLTmp .= '              <Wysokosc>'.$infs['products_film_height'].'</Wysokosc>' . "\r\n";
                                }
                                $DoZapisaniaXMLTmp .= '          </Film>' . "\r\n";
                            }                    
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytanieFlv);
                            //          
                        }
                    }              
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Filmy>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Filmy>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);              
                    //
                }
                
                // pliki
                if ( isset($Konfiguracja['Pliki']) && $Konfiguracja['Pliki'] == 1 ) {
                    //                  
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 11; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytaniePliki = "select distinct * from products_file where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_file_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytaniePliki);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Plik_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_file_name'])) ? Funkcje::CzyszczenieTekstu($infs['products_file_name']) : '') . '";'; 
                            
                            $NaglowekCsv .= 'Plik_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_file_description'])) ? Funkcje::CzyszczenieTekstu($infs['products_file_description']) : '') . '";';                 
                            
                            // plik i logowanie tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Plik_'.$c.'_plik;';
                                $CoDoZapisania .= '"' . ((isset($infs['products_file'])) ? Funkcje::CzyszczenieTekstu($infs['products_file']) : '') . '";'; 
                                //
                                $NaglowekCsv .= 'Plik_'.$c.'_logowanie;';
                                if (isset($infs['products_file_login']) && $infs['products_file_login'] == 1) {
                                    $CoDoZapisania .= '"tak";';
                                  } else {
                                    $CoDoZapisania .= '"nie";';
                                }                   
                            }
                            
                            if (isset($infs['products_file']) && !empty($infs['products_file']) && isset($infs['products_file_name']) && !empty($infs['products_file_name'])) {
                                $DoZapisaniaXMLTmp .= '          <Plik>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_file_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_file'].'</Plik>' . "\r\n";
                                
                                if (!empty($infs['products_file_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_file_description'].']]></Opis>' . "\r\n";
                                }
         
                                if ($infs['products_file_login'] == 1 && !empty($infs['products_file_name'])) {
                                    $DoZapisaniaXMLTmp .= '              <Logowanie>tak</Logowanie>' . "\r\n";
                                  } else if (!empty($infs['products_file_name'])) {
                                    $DoZapisaniaXMLTmp .= '              <Logowanie>nie</Logowanie>' . "\r\n";
                                }     

                                $DoZapisaniaXMLTmp .= '          </Plik>' . "\r\n";
                            }
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytaniePliki);
                            //          
                        }
                    } 
                    //

                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Pliki>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Pliki>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);  
                    //                    
                }
                
                // pliki elektroniczne
                if ( isset($Konfiguracja['Pliki_elektroniczne']) && $Konfiguracja['Pliki_elektroniczne'] == 1 ) {
                    //                    
                    $DoZapisaniaXMLTmp = '';
                    //
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        for ($t = 1; $t < 11; $t++) {
                            //
                            $zapytaniePliki = "select distinct products_file_shopping_name, products_file_shopping, language_id from products_file_shopping where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' order by 	products_file_shopping_unique_id limit ".($t-1).",1";       
                            $sqlc = $db->open_query($zapytaniePliki);  
                            $infs = $sqlc->fetch_assoc();      
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Plik_elektroniczny_'.$t.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_file_shopping_name'])) ? Funkcje::CzyszczenieTekstu($infs['products_file_shopping_name']) : '') . '";'; 
                            
                            $NaglowekCsv .= 'Plik_elektroniczny_'.$t.'_plik' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . ((isset($infs['products_file_shopping'])) ? Funkcje::CzyszczenieTekstu($infs['products_file_shopping']) : '') . '";';                 
                            
                            if (isset($infs['products_file_shopping']) && !empty($infs['products_file_shopping']) && isset($infs['products_file_shopping_name']) && !empty($infs['products_file_shopping_name'])) {
                                $DoZapisaniaXMLTmp .= '          <Plik_elektroniczny>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_file_shopping_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_file_shopping'].'</Plik>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '          </Plik_elektroniczny>' . "\r\n";
                            }
                            //
                        
                            $db->close_query($sqlc);
                            unset($infs, $zapytaniePliki);
                            //  
                        }
                        //
                    }           

                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Pliki_elektroniczne>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Pliki_elektroniczne>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);  
                    //
                }

                // pliki mp3
                if ( isset($Konfiguracja['Pliki_mp3']) && $Konfiguracja['Pliki_mp3'] == 1 ) {
                    //                  
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($w = 1; $w < 16; $w++) {
                        //
                        $zapytanieZdjecie = "select * from products_mp3 where products_id = '" . (int)$info['products_id'] . "' order by products_mp3_id limit ".($w-1).",1";
                        $sqlc = $db->open_query($zapytanieZdjecie);  
                        $infs = $sqlc->fetch_assoc();
                        
                        $NaglowekCsv .= 'Plik_mp3_' . $w . ';';
                        $CoDoZapisania .= '"' . ((isset($infs['products_mp3_file'])) ? $infs['products_mp3_file'] : '') . '";';
                        $NaglowekCsv .= 'Nazwa_mp3_' . $w . ';';
                        $CoDoZapisania .= '"' . ((isset($infs['products_mp3_name'])) ? $infs['products_mp3_name'] : '') . '";';                
                        
                        if (isset($infs['products_mp3_file']) && !empty($infs['products_mp3_file']) && isset($infs['products_mp3_name']) && !empty($infs['products_mp3_name'])) {
                            $DoZapisaniaXMLTmp .= '          <Plik_mp3>' . "\r\n";
                            $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_mp3_name'].']]></Nazwa>' . "\r\n";
                            $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_mp3_file'].'</Plik>' . "\r\n";
                            $DoZapisaniaXMLTmp .= '          </Plik_mp3>' . "\r\n";
                        }               
                        
                        $db->close_query($sqlc);
                        unset($infs, $zapytanieZdjecie);
                        //
                    }                         
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Pliki_mp3>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Pliki_mp3>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);             
                    //
                }
                
                // dodatkowe pola
                if ( isset($Konfiguracja['Dodatkowe_pola']) && $Konfiguracja['Dodatkowe_pola'] == 1 ) {
                    //                    
                    $DoZapisaniaXMLTmp = '';
                    
                    $nr_c = 1;
                    
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        } 
                        //
                        foreach ( $TablicaDodatkowePola as $DodatkowePole ) {
                            //
                            if ( $DodatkowePole['jezyk_id'] == $ile_jezykow[$w]['id'] || ($DodatkowePole['jezyk_id'] == 0 && $w == 0) ) {
                                //
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_nazwa' . $Przedrostek . ';';
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_wartosc' . $Przedrostek . ';';   
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_wartosc_2' . $Przedrostek . ';';
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_wartosc_3' . $Przedrostek . ';';
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_link;';                                
                                //
                                $zapytaniePola = "select products_extra_fields_id, products_extra_fields_value, products_extra_fields_value_1, products_extra_fields_value_2, products_extra_fields_link from products_to_products_extra_fields where products_id = '".(int)$info['products_id']."' and products_extra_fields_id = '" . $DodatkowePole['pole_id'] . "'";        
                                $sqlc = $db->open_query($zapytaniePola);
                                $infoPole = $sqlc->fetch_assoc();
                                //
                                if ((int)$db->ile_rekordow($sqlc) > 0) {
                                    //
                                    $CoDoZapisania .= '"' . $DodatkowePole['nazwa_pola'] . '";'; 
                                    $CoDoZapisania .= '"' . $infoPole['products_extra_fields_value'] . '";';
                                    $CoDoZapisania .= '"' . $infoPole['products_extra_fields_value_1'] . '";';
                                    $CoDoZapisania .= '"' . $infoPole['products_extra_fields_value_2'] . '";';
                                    $CoDoZapisania .= '"' . $infoPole['products_extra_fields_link'] . '";';
                                    //
                                    if (!empty($DodatkowePole['nazwa_pola']) && !empty($infoPole['products_extra_fields_value'])) {
                                        $DoZapisaniaXMLTmp .= '          <Dodatkowe_pole>' . "\r\n";
                                        $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$DodatkowePole['nazwa_pola'].']]></Nazwa>' . "\r\n";
                                        $DoZapisaniaXMLTmp .= '              <Wartosc><![CDATA['.$infoPole['products_extra_fields_value'].']]></Wartosc>' . "\r\n";
                                        
                                        if ( $DodatkowePole['pole_foto'] == 0 ) {  
                                             $DoZapisaniaXMLTmp .= '              <Wartosc_2><![CDATA['.$infoPole['products_extra_fields_value_1'].']]></Wartosc_2>' . "\r\n";
                                             $DoZapisaniaXMLTmp .= '              <Wartosc_3><![CDATA['.$infoPole['products_extra_fields_value_2'].']]></Wartosc_3>' . "\r\n";
                                        }
                                        
                                        if ( trim((string)$infoPole['products_extra_fields_link']) != '' ) {
                                             $DoZapisaniaXMLTmp .= '              <Link><![CDATA['.$infoPole['products_extra_fields_link'].']]></Link>' . "\r\n";
                                        }
                                        
                                        $DoZapisaniaXMLTmp .= '          </Dodatkowe_pole>' . "\r\n"; 
                                    }                                     
                                    //
                                  } else {
                                    //
                                    $CoDoZapisania .= '"";'; 
                                    $CoDoZapisania .= '"";';
                                    $CoDoZapisania .= '"";';
                                    $CoDoZapisania .= '"";';
                                    $CoDoZapisania .= '"";';                                    
                                    //
                                }
                                //
                                $db->close_query($sqlc);
                                unset($zapytaniePola); 
                                //
                                $nr_c++;                            
                                //                                
                            }
                            //
                        }
                        //
                    }
                    
                    unset($nr_c);
                        
                    if ($DoZapisaniaXMLTmp != '') {
                        // 
                        $DoZapisaniaXML .= '      <Dodatkowe_pola>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Dodatkowe_pola>' . "\r\n";
                        //
                    }            
                    unset($DoZapisaniaXMLTmp);  
                    //
                }
                
                // cechy produktu
                if ( isset($Konfiguracja['Cechy_produktu']) && $Konfiguracja['Cechy_produktu'] == 1 ) {
                    //                   
                    $DoZapisaniaXMLTmp = '';
                    
                    //
                    for ($c = 1; $c < $ileCech; $c++) {
                        //
                        $zapytanieCechy = "select distinct * from products_attributes where products_id = '".(int)$info['products_id']."' order by options_id limit ".($c-1).",1";        
                        $sqlc = $db->open_query($zapytanieCechy); 

                        if ((int)$db->ile_rekordow($sqlc) > 0 || $_POST['format'] == 'csv') {                          

                            $infoCecha = $sqlc->fetch_assoc();            

                            for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                                //
                                $Przedrostek = '';
                                if ($ile_jezykow[$w]['kod'] != 'pl') {
                                    $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                                }        

                                // nazwa cechy
                                $NaglowekCsv .= 'Cecha_nazwa_' . $c . $Przedrostek . ';';
                                if ( isset($infoCecha['options_id']) ) {
                                    $nazwa_cechy = Funkcje::NazwaCechy((int)$infoCecha['options_id'], $ile_jezykow[$w]['id']);
                                } else {
                                    $nazwa_cechy = '';
                                }
                                $CoDoZapisania .= '"' . $nazwa_cechy . '";'; 
                                
                                //
                                // wartosc cechy
                                $Przedrostek = '';
                                if ($ile_jezykow[$w]['kod'] != 'pl') {
                                    $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                                }        

                                $NaglowekCsv .= 'Cecha_wartosc_' . $c . $Przedrostek . ';';
                                if ( isset($infoCecha['options_values_id']) ) {
                                    $wartosc_cechy = Funkcje::WartoscCechy($infoCecha['options_values_id'], $ile_jezykow[$w]['id']);
                                } else {
                                    $wartosc_cechy = '';
                                }
                                $CoDoZapisania .= '"' . $wartosc_cechy . '";';
                                
                                if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                    //
                                    $DoZapisaniaXMLTmp .= '          <Cecha>' . "\r\n";
                                    $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$nazwa_cechy.']]></Nazwa>' . "\r\n";
                                    $DoZapisaniaXMLTmp .= '              <Wartosc><![CDATA['.$wartosc_cechy.']]></Wartosc>' . "\r\n";
                                    //   
                                }             
                                //          
                            }

                            if ( isset($infoCecha['options_values_id']) ) {
                                $zapytanieCechyGlobal = "select global_options_values_price_tax, global_price_prefix, global_options_values_weight from products_options_values where products_options_values_id = '" . (int)$infoCecha['options_values_id'] . "' and language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";        
                                $sqlg = $db->open_query($zapytanieCechyGlobal);

                                $infoCechaGlobal = $sqlg->fetch_assoc();                                

                                if ( isset($infoCechaGlobal['global_options_values_price_tax']) && $infoCechaGlobal['global_options_values_price_tax'] > 0 && $infoCecha['options_values_price_tax'] == 0 ) {
                                     //
                                     $infoCecha['options_values_price_tax'] = $infoCechaGlobal['global_options_values_price_tax'];
                                     $infoCecha['price_prefix'] = $infoCechaGlobal['global_price_prefix'];
                                     $infoCecha['options_values_weight'] = $infoCechaGlobal['global_options_values_weight'];
                                     //
                                }         

                                $db->close_query($sqlg);
                                unset($zapytanieCechyGlobal);
                            } else {
                                $infoCecha['options_values_price_tax'] = '';
                                $infoCecha['price_prefix'] = '';
                                $infoCecha['options_values_weight'] = '';
                            }
                            
                            // waga cechy
                            if ( !isset($infoCecha['options_values_weight']) ) {
                                 $infoCecha['options_values_weight'] = 0;
                            }
                            $NaglowekCsv .= 'Cecha_waga_' . $c . ';';
                            $CoDoZapisania .= '"' . $infoCecha['options_values_weight'] . '";';
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                //
                                $DoZapisaniaXMLTmp .= '              <Waga>'.$infoCecha['options_values_weight'].'</Waga>' . "\r\n";
                                //   
                            }         

                            // foto cechy
                            if ( !isset($infoCecha['options_values_image']) ) {
                                 $infoCecha['options_values_image'] = '';
                            }                            
                            $NaglowekCsv .= 'Cecha_foto_' . $c . ';';
                            $CoDoZapisania .= '"' . $infoCecha['options_values_image'] . '";';
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                //
                                $DoZapisaniaXMLTmp .= '              <Foto>'.$infoCecha['options_values_image'].'</Foto>' . "\r\n";
                                //   
                            }   
                            
                            // domyslna
                            if ( !isset($infoCecha['options_default']) ) {
                                 $infoCecha['options_default'] = '';
                            }                              
                            $NaglowekCsv .= 'Cecha_domyslna_' . $c . ';';
                            $CoDoZapisania .= '"' . (((int)$infoCecha['options_default'] == 1) ? 'tak' : '') . '";';
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                //
                                if ( (int)$infoCecha['options_default'] == 1 ) {
                                      $DoZapisaniaXMLTmp .= '              <Domyslna>tak</Domyslna>' . "\r\n";
                                }
                                //   
                            }      
                            
                            // cena cechy
                            $NaglowekCsv .= 'Cecha_cena_' . $c . ';';
                            //
                            $PrefixCeny = $infoCecha['price_prefix'];
                            //if ( $infoCecha['price_prefix'] == '-' ) {
                            //     $PrefixCeny = '-';
                            //}
                            //
                            $CoDoZapisania .= '"' . $PrefixCeny . $infoCecha['options_values_price_tax'] . '";';
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy) && $info['options_type'] == 'cechy') {
                                //
                                $DoZapisaniaXMLTmp .= '              <Cena>'.(($infoCecha['options_values_price_tax'] > 0) ? $PrefixCeny : ''). $infoCecha['options_values_price_tax'].'</Cena>' . "\r\n";
                                //   
                            }                  
                            //
                            
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                $DoZapisaniaXMLTmp .= '          </Cecha>' . "\r\n";
                            }
                            
                            unset($wartosc_cechy, $nazwa_cechy, $infoCecha); 

                        }
                        
                        $db->close_query($sqlc);
                        unset($zapytanieCechy);
                                    
                    }   

                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Cechy>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Cechy>' . "\r\n";
                        //
                    }            
                    unset($DoZapisaniaXMLTmp);              
                    //
                }
                
                // akcesoria dodatkowe
                if ( isset($Konfiguracja['Akcesoria']) && $Konfiguracja['Akcesoria'] == 1 ) {
                    //                 
                    $DoZapisaniaXMLTmp = '';
                    $TablicaAkc = array();
                    
                    $zapytanieAkcesoria = "select distinct pa.pacc_products_id_slave, p.products_model from products_accesories pa, products p where pa.pacc_products_id_master = '".(int)$info['products_id']."' and pa.pacc_products_id_slave = p.products_id AND pa.pacc_type = 'produkt'";  
                    $sqla = $db->open_query($zapytanieAkcesoria);  
                    
                    while ( $infs = $sqla->fetch_assoc() ) {
                      
                        if ( trim((string)$infs['products_model']) != '' ) {
                             $TablicaAkc[] = $infs['products_model'];
                        }
                      
                    }       

                    for ($t = 0; $t < 50; $t++) {
                      
                        $NaglowekCsv .= 'Akcesoria_' . ($t + 1) . '_nr_katalogowy;';
                        $CoDoZapisania .= '"' . ((isset($TablicaAkc[$t])) ? $TablicaAkc[$t] : '') . '";';                    
                      
                    }
                    
                    if ( count($TablicaAkc) > 0 ) {

                        for ($t = 0; $t < count($TablicaAkc); $t++) {
                            //
                            $DoZapisaniaXMLTmp .= '          <Akcesoria_nr_katalogowy><![CDATA[' . ((isset($TablicaAkc[$t])) ? $TablicaAkc[$t] : '') . ']]></Akcesoria_nr_katalogowy>' . "\r\n";
                            //
                        }
                    
                        if ($DoZapisaniaXMLTmp != '') {
                            //
                            $DoZapisaniaXML .= '      <Akcesoria>' . "\r\n";
                            $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                            $DoZapisaniaXML .= '      </Akcesoria>' . "\r\n";
                            //
                        }
                    
                    }
                    
                    unset($DoZapisaniaXMLTmp);        
                        
                    $db->close_query($sqla);
                    unset($infs, $zapytanieAkcesoria);                    

                } 

                // gabaryty paczkomaty
                if ( isset($Konfiguracja['Paczkomaty']) && $Konfiguracja['Paczkomaty'] == 1 ) {
                    //                 
                    $GabarytPaczkomaty = '';
                    $IloscPaczkomaty = '';
                    if ( $info['inpost_size'] != 'x' ) {
                        //
                        $GabarytPaczkomaty = strtoupper((string)$info['inpost_size']);
                        $IloscPaczkomaty = (int)$info['inpost_quantity'];
                        //
                    }                       

                    $NaglowekCsv .= 'Paczkomaty_gabaryt;';
                    $CoDoZapisania .= '"' . $GabarytPaczkomaty . '";';
                    //
                    $NaglowekCsv .= 'Paczkomaty_ilosc;';
                    $CoDoZapisania .= '"' . $IloscPaczkomaty . '";';                    
                    if (!empty($GabarytPaczkomaty)) {
                        $DoZapisaniaXML .= '      <Paczkomaty_gabaryt><![CDATA['.Funkcje::CzyszczenieTekstu($GabarytPaczkomaty).']]></Paczkomaty_gabaryt>' . "\r\n";
                        $DoZapisaniaXML .= '      <Paczkomaty_ilosc>'.$IloscPaczkomaty.'</Paczkomaty_ilosc>' . "\r\n";
                    }                
                    
                    unset($GabarytPaczkomaty, $IloscPaczkomaty);
                    //
                }     

                // dane ALLEGRO
                // id kategorii allegro
                if ( isset($Konfiguracja['Allegro']) && $Konfiguracja['Allegro'] == 1 ) {
                    //
                    $DoZapisaniaXMLTmp = '';
                    //
                    $zapytanieAllegro = "select * from products_allegro_info where products_id = '".(int)$info['products_id']."'";  
                    $sqla = $db->open_query($zapytanieAllegro);   
                    //
                    $infs = $sqla->fetch_assoc();
                    //                    
                    $AllegroIdKategorii = '';
                    $AllegroNazwaProduktu = '';
                    $AllegroZdjecie = '';
                    $AllegroCena = '';
                    $AllegroWaga = '';
                    if ( isset($infs['products_cat_id_allegro']) && (int)$infs['products_cat_id_allegro'] > 0 ) {
                         $AllegroIdKategorii = (int)$infs['products_cat_id_allegro'];
                    }
                    if (!empty($infs['products_name_allegro'])) {
                         $AllegroNazwaProduktu = $infs['products_name_allegro'];
                    }   
                    if (!empty($infs['products_image_allegro'])) {
                         $AllegroZdjecie = ((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($infs['products_image_allegro'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . $infs['products_image_allegro'];
                    } 
                    if ( isset($infs['products_price_allegro']) && (float)$infs['products_price_allegro'] > 0 ) {
                         $AllegroCena = $infs['products_price_allegro'];
                    }                      
                    if ( isset($infs['products_weight_allegro']) && (float)$infs['products_weight_allegro'] > 0 ) {
                         $AllegroWaga = $infs['products_weight_allegro'];
                    }                                          
                    //Funkcje::CzyszczenieTekstu($info['products_adminnotes'])
                    $NaglowekCsv .= 'Allegro_id_kategoria;';
                    $CoDoZapisania .= '"' . $AllegroIdKategorii . '";'; 
                    if ($AllegroIdKategorii > 0) {
                        $DoZapisaniaXMLTmp .= '          <Allegro_id_kategoria>'.$AllegroIdKategorii.'</Allegro_id_kategoria>' . "\r\n";
                    } 
                    //
                    $NaglowekCsv .= 'Allegro_nazwa_produktu;';
                    $CoDoZapisania .= '"' . $AllegroNazwaProduktu . '";'; 
                    if (!empty($AllegroNazwaProduktu)) {
                        $DoZapisaniaXMLTmp .= '          <Allegro_nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($AllegroNazwaProduktu, false).']]></Allegro_nazwa_produktu>' . "\r\n";
                    }                     
                    //
                    $NaglowekCsv .= 'Allegro_zdjecie;';
                    $CoDoZapisania .= '"' . $AllegroZdjecie . '";'; 
                    if (!empty($AllegroZdjecie)) {
                        $DoZapisaniaXMLTmp .= '          <Allegro_zdjecie>'.Funkcje::CzyszczenieTekstu($AllegroZdjecie).'</Allegro_zdjecie>' . "\r\n";
                    }      
                    //
                    $NaglowekCsv .= 'Allegro_cena;';
                    $CoDoZapisania .= '"' . $AllegroCena . '";'; 
                    if (!empty($AllegroCena)) {
                        $DoZapisaniaXMLTmp .= '          <Allegro_cena>'.$AllegroCena.'</Allegro_cena>' . "\r\n";
                    }  
                    //
                    $NaglowekCsv .= 'Allegro_waga;';
                    $CoDoZapisania .= '"' . $AllegroWaga . '";'; 
                    if (!empty($AllegroWaga)) {
                        $DoZapisaniaXMLTmp .= '          <Allegro_waga>'.$AllegroWaga.'</Allegro_waga>' . "\r\n";
                    }                      
                    //                   
                    //                    
                    unset($AllegroIdKategorii, $AllegroNazwaProduktu, $AllegroZdjecie, $AllegroCena);
                    //
                    $db->close_query($sqla);
                    unset($infs, $zapytanieAllegro);   
                    //
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Allegro>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Allegro>' . "\r\n";
                        //
                    }    
                    //
                    unset($DoZapisaniaXMLTmp);
                }

                // indywidualny koszt wysylki
                if ( isset($Konfiguracja['Indywidualny_koszt_wysylki']) && $Konfiguracja['Indywidualny_koszt_wysylki'] == 1 ) {
                     //
                     $NaglowekCsv .= 'Indywidualny_koszt_wysylki;';
                     $CoDoZapisania .= '"' . $info['shipping_cost'] . '";'; 
                     if ( $info['shipping_cost'] > 0 ) {
                          $DoZapisaniaXML .= '      <Indywidualny_koszt_wysylki>'.$info['shipping_cost'].'</Indywidualny_koszt_wysylki>' . "\r\n";
                     }
                     //
                }
                
                // indywidualny koszt wysylki - pobranie
                if ( isset($Konfiguracja['Indywidualny_koszt_wysylki_pobranie']) && $Konfiguracja['Indywidualny_koszt_wysylki_pobranie'] == 1 ) {
                     //
                     $NaglowekCsv .= 'Indywidualny_koszt_wysylki_pobranie;';
                     $CoDoZapisania .= '"' . $info['shipping_cost_delivery'] . '";'; 
                     if ( $info['shipping_cost_delivery'] > 0 ) {
                          $DoZapisaniaXML .= '      <Indywidualny_koszt_wysylki_pobranie>'.$info['shipping_cost_delivery'].'</Indywidualny_koszt_wysylki_pobranie>' . "\r\n";
                     }
                     //
                }   

                // id podobnych
                if ( isset($Konfiguracja['Podobne_id']) && $Konfiguracja['Podobne_id'] == 1 ) {
                     //
                     $zapytanieProd = "select distinct * from products_options_products where pop_products_id_master = '".$info['products_id']."'";
                     $sqlo = $db->open_query($zapytanieProd); 
                     
                     $NaglowekCsv .= 'Podobne_id;';

                     if ((int)$db->ile_rekordow($sqlo) > 0) {
                        
                         $idp = array();
                         
                         while ($infp = $sqlo->fetch_assoc()) {
                            //
                            $idp[] = $infp['pop_products_id_slave'];
                            //
                         }
                         
                         $CoDoZapisania .= '"' . implode(',',$idp) . '";';
                         $DoZapisaniaXML .= '      <Podobne_id><![CDATA['.implode(',',$idp).']]></Podobne_id>' . "\r\n";
                         
                     } else {
                       
                         $CoDoZapisania .= '"";';
                       
                     }

                     $db->close_query($sqlo);        
                    
                }       

                // id powiazanych
                if ( isset($Konfiguracja['Powiazane_id']) && $Konfiguracja['Powiazane_id'] == 1 ) {
                     //
                     $zapytanieProd = "select distinct * from products_related_products where prp_products_id_master = '".$info['products_id']."'";
                     $sqlo = $db->open_query($zapytanieProd); 
                     
                     $NaglowekCsv .= 'Powiazane_id;';

                     if ((int)$db->ile_rekordow($sqlo) > 0) {
                        
                         $idp = array();
                         
                         while ($infp = $sqlo->fetch_assoc()) {
                            //
                            $idp[] = $infp['prp_products_id_slave'];
                            //
                         }
                         
                         $CoDoZapisania .= '"' . implode(',',$idp) . '";';
                         $DoZapisaniaXML .= '      <Powiazane_id><![CDATA['.implode(',',$idp).']]></Powiazane_id>' . "\r\n";
                         
                     } else {
                       
                         $CoDoZapisania .= '"";';
                       
                     }

                     $db->close_query($sqlo);       
                    
                }                  
                
                $CoDoZapisania .= '"KONIEC"' . "\r\n";

                $DoZapisaniaXML .= '  </Produkt>' . "\r\n" . "\r\n";
                
                $Suma++;

            }
              
            if ($_POST['format'] == 'csv') {
              
                // zmiana " na ""
                $Linie = explode("\r\n", $CoDoZapisania);
                $PrzetwarzaneLinie = array();
                
                foreach ( $Linie as $Linia ) {
                  
                    if ( strpos($Linia, ';"KONIEC"') > -1 ) {
                  
                        $LiniaTab = array();
                        
                        $polaCsv = explode('";"', substr($Linia, 1, -1));
                        
                        foreach ( $polaCsv as $pozycjaCsv ) {
                        
                            if ( substr($pozycjaCsv, 0, 1) == '"' ) {
                                 //
                                 $pozycjaCsv = trim($pozycjaCsv, '"');
                                 //
                            }
                            
                            $pozycjaCsv = str_replace('"', '""', $pozycjaCsv);
                            $LiniaTab[] = '"' . $pozycjaCsv . '"';
                            
                        }
                        
                        $PrzetwarzaneLinie[] = implode(';', $LiniaTab);
                        
                    }
                    
                }
                           
                $CoDoZapisania = implode("\r\n", $PrzetwarzaneLinie) . "\r\n";
                            
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
                }
                //
            }      

            // jezeli jest do zapisu xml
            if ($_POST['format'] == 'xml') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    ///
                    $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                    $CoDoZapisania .= '<Produkty>' . "\r\n" . "\r\n";
                    $CoDoZapisania .= $DoZapisaniaXML;
                    //
                } else {
                    //
                    $CoDoZapisania = $DoZapisaniaXML;
                    //
                }

                //
                // koniec pliku
                if ( !isset($ImportZewnetrzny) ) {
                    if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= $Suma) {
                        $CoDoZapisania .= '</Produkty>' . "\r\n";
                    }
                }
                unset($Suma);

            }            

            fwrite($fp, $CoDoZapisania);
            
            // zapisanie danych do pliku
            flock($fp, 3);
            // zamkniecie pliku
            fclose($fp);  

            unset($DoZapisaniaXML, $ileDodatkowychPol, $ileCech);
        
        }
    }
    
    
    // jezeli jest eksport cech
    if (isset($_POST['zakres']) && $_POST['zakres'] == 'cechy') {  
    
        // pobieranie danych konfiguracji exportu
        $zapytanie_konfig = "select code, status from export_configuration";
        $sql_konfig = $db->open_query($zapytanie_konfig);  

        $Konfiguracja = array();
        while ( $info_konfig = $sql_konfig->fetch_assoc() ) {
            //
            $Konfiguracja[ $info_konfig['code'] ] = $info_konfig['status'];
            //
        }
        
        $db->close_query($sql_konfig);
        unset($info_konfig);            

        $zapytanie = "select distinct * from products_stock order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        // jezeli sa warunki
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
            //
            $DodatkoweCeny = '';
            if ( (int)ILOSC_CEN > 1 ) {
                //
                for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                    //
                    $DodatkoweCeny .= 'ps.products_stock_price_' . $n . ', ps.products_stock_price_tax_' . $n . ', ps.products_stock_old_price_' . $n . ', ps.products_stock_retail_price_' . $n . ',';
                    //
                }
                //
            }         
            $zapytanie = "select distinct ps.*, " . $DodatkoweCeny . " p.products_id, p.manufacturers_id, ps.products_id from products p, products_stock ps where p.products_id = ps.products_id and p.manufacturers_id = '" . (int)$_POST['filtr'] . "' order by ps.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
            unset($DodatkoweCeny);
            //
        }
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
            $zapytanie = "select distinct * from products_stock ps, products_to_categories pc where ps.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "' order by ps.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }        
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'fraza') {
            $fraza = $filtr->process($_POST['filtr']);
            $zapytanie = "select distinct * from products_stock where products_stock_model like '%".$fraza."%' order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
            unset($fraza);
        }         
        
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $CoDoZapisania = '';
            $DoZapisaniaXML = '';

            // uchwyt pliku, otwarcie do dopisania
            $fp = fopen($filtr->process($_POST['plik']), "a");
            // blokada pliku do zapisu
            flock($fp, 2);
            
            $Suma = $_POST['limit'];
        
            while ($info = $sql->fetch_assoc()) {
            
                $NaglowekCsv = '';
                $DoZapisaniaXML .= '  <Produkt>' . "\r\n";
            
                $zapytanieNazwaProduktu = "select distinct p.products_id, p.products_model, pd.products_id, pd.products_name from products p, products_description pd where p.products_id = '".(int)$info['products_id']."' and p.products_id = pd.products_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                $sqlNazwa = $db->open_query($zapytanieNazwaProduktu);
                $infc = $sqlNazwa->fetch_assoc();
                
                // id produktu
                if ( isset($Konfiguracja['Id_produktu']) && $Konfiguracja['Id_produktu'] == 1 ) {
                    //
                    $NaglowekCsv .= 'Id_produktu;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_id']) . '";';
                    $DoZapisaniaXML .= '      <Id_produktu>'.Funkcje::CzyszczenieTekstu($infc['products_id']).'</Id_produktu>' . "\r\n";                 
                    //
                }                
                
                $NaglowekCsv .= 'Nr_katalogowy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_model']) . '";';   
                if (!empty($infc['products_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_model'], false).']]></Nr_katalogowy>' . "\r\n";
                }      

                $NaglowekCsv .= 'Nazwa_produktu;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_name']) . '";'; 
                if (!empty($infc['products_name'])) {
                    $DoZapisaniaXML .= '      <Nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_name'], false).']]></Nazwa_produktu>' . "\r\n";
                }            

                $db->close_query($sqlNazwa);
                unset($infc, $zapytanieNazwaProduktu); 

                $NaglowekCsv .= 'Nr_katalogowy_cechy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_stock_model']) . '";';
                if (!empty($info['products_stock_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy_cechy><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_stock_model'], false).']]></Nr_katalogowy_cechy>' . "\r\n";
                }            

                $NaglowekCsv .= 'Kod_ean_cechy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_stock_ean']) . '";';   
                if (!empty($info['products_stock_ean'])) {
                    $DoZapisaniaXML .= '      <Kod_ean_cechy><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_stock_ean'], false).']]></Kod_ean_cechy>' . "\r\n";
                }                   
                
                // teraz rozpisuje cechy
                $NaglowekCsv .= 'Nazwa_wartosc_cechy;';
                $tablica_Cech = explode(',', (string)$info['products_stock_attributes']);
                //
                $ciagCechy = '';
                for ($q = 0, $cq = count($tablica_Cech); $q < $cq; $q++) {
                    //
                    $NazwaWartosc = explode('-', (string)$tablica_Cech[$q]);
                    $ciagCechy .= Funkcje::NazwaCechy( $NazwaWartosc[0] , '1' ) . ': ' . Funkcje::WartoscCechy( $NazwaWartosc[1] , '1' ) . ', ';
                    //
                }        
                $CoDoZapisania .= '"' . substr((string)$ciagCechy, 0, strlen((string)$ciagCechy)-2) . '";';  
                $DoZapisaniaXML .= '      <Nazwa_wartosc_cechy><![CDATA['.substr((string)$ciagCechy, 0, strlen((string)$ciagCechy)-2).']]></Nazwa_wartosc_cechy>' . "\r\n";           
                //

                $NaglowekCsv .= 'Ilosc_produktow;';
                $CoDoZapisania .= '"' . $info['products_stock_quantity'] . '";'; 
                $DoZapisaniaXML .= '      <Ilosc_produktow>'.$info['products_stock_quantity'].'</Ilosc_produktow>' . "\r\n"; 
                
                $NaglowekCsv .= 'Zdjecie;';
                $CoDoZapisania .= '"' . ((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1 && !empty($info['products_stock_image'])) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . $info['products_stock_image'] . '";'; 
                
                if ( !empty($info['products_stock_image']) ) {
                     $DoZapisaniaXML .= '      <Zdjecie>' . ((isset($Konfiguracja['Zdjecia_url']) && $Konfiguracja['Zdjecia_url'] == 1) ? ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' : '') . $info['products_stock_image'] . '</Zdjecie>' . "\r\n";                 
                }
                
                // ceny produktu z kombinacja cech
                $NaglowekCsv .= 'Cena_brutto_cechy;';
                $CoDoZapisania .= '"' . $info['products_stock_price_tax'] . '";'; 

                if ( $info['products_stock_price_tax'] > 0 ) {
                     $DoZapisaniaXML .= '      <Cena_brutto_cechy>'.$info['products_stock_price_tax'].'</Cena_brutto_cechy>' . "\r\n";   
                }

                // ceny poprzednia
                $NaglowekCsv .= 'Cena_poprzednia_cechy;';
                $CoDoZapisania .= '"' . $info['products_stock_old_price'] . '";';                 
                                
                if ( $info['products_stock_old_price'] > 0 ) {
                     $DoZapisaniaXML .= '      <Cena_poprzednia_cechy>'.$info['products_stock_old_price'].'</Cena_poprzednia_cechy>' . "\r\n";   
                }                 
                
                // ceny katalogowa
                $NaglowekCsv .= 'Cena_katalogowa_cechy;';
                $CoDoZapisania .= '"' . $info['products_stock_retail_price'] . '";';                 
                                
                if ( $info['products_stock_retail_price'] > 0 ) {
                     $DoZapisaniaXML .= '      <Cena_katalogowa_cechy>'.$info['products_stock_retail_price'].'</Cena_katalogowa_cechy>' . "\r\n";   
                }   

                if ( (int)ILOSC_CEN > 1 ) {    
                
                    for ( $x = 2; $x < ILOSC_CEN + 1; $x++ ) {

                        $NaglowekCsv .= 'Cena_brutto_cechy_' . $x . ';';
                        $CoDoZapisania .= '"' . $info['products_stock_price_tax_' . $x] . '";'; 
                                
                        if ( $info['products_stock_price_tax_' . $x] > 0 ) {
                             $DoZapisaniaXML .= '      <Cena_brutto_cechy_' . $x . '>'.$info['products_stock_price_tax_' . $x].'</Cena_brutto_cechy_' . $x . '>' . "\r\n";   
                        }
                                                
                        // ceny poprawnia
                        $NaglowekCsv .= 'Cena_poprzednia_cechy_' . $x . ';';
                        $CoDoZapisania .= '"' . $info['products_stock_old_price_' . $x] . '";';                          

                        if ( $info['products_stock_retail_price_' . $x] > 0 ) {
                             $DoZapisaniaXML .= '      <Cena_poprzednia_cechy_' . $x . '>'.$info['products_stock_old_price_' . $x].'</Cena_poprzednia_cechy_' . $x . '>' . "\r\n";   
                        }  

                        // ceny katalogowa
                        $NaglowekCsv .= 'Cena_katalogowa_cechy_' . $x . ';';
                        $CoDoZapisania .= '"' . $info['products_stock_retail_price_' . $x] . '";';                          

                        if ( $info['products_stock_retail_price_' . $x] > 0 ) {
                             $DoZapisaniaXML .= '      <Cena_katalogowa_cechy_' . $x . '>'.$info['products_stock_retail_price_' . $x].'</Cena_katalogowa_cechy_' . $x . '>' . "\r\n";   
                        }                            

                    }

                }                    

                // dostepnosc
                //
                if ((int)$info['products_stock_availability_id'] != '99999') {
                    //
                    $NazwaDostepnosci = '';
                    if ( isset($Dostepnosci[(int)$info['products_stock_availability_id']][1]) ) {
                        //
                        $NazwaDostepnosci = $Dostepnosci[(int)$info['products_stock_availability_id']][1];
                        //
                    }

                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaDostepnosci) . '";';
                    if (!empty($NazwaDostepnosci)) {
                        $DoZapisaniaXML .= '      <Dostepnosc><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaDostepnosci, false).']]></Dostepnosc>' . "\r\n";
                    }  

                    unset($NazwaDostepnosci);

                } else if ((int)$info['products_stock_availability_id'] == '99999') {
                    //
                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"AUTOMATYCZNY";';
                    $DoZapisaniaXML .= '      <Dostepnosc><![CDATA[AUTOMATYCZNY]]></Dostepnosc>' . "\r\n";                
                    //
                }
                
                // termin wysylki
                if ((int)$info['products_stock_shipping_time_id'] > 0) {
                    //                 
                    $NazwaTerminuWysylki = '';
                    if ( isset($TerminyWysylek[(int)$info['products_stock_shipping_time_id']][ $_SESSION['domyslny_jezyk']['id'] ]) ) {
                        //
                        $NazwaTerminuWysylki = $TerminyWysylek[(int)$info['products_stock_shipping_time_id']][ $_SESSION['domyslny_jezyk']['id'] ];
                        //
                    }                       

                    $NaglowekCsv .= 'Termin_wysylki;';
                    $CoDoZapisania .= '"' . $NazwaTerminuWysylki . '";';
                    if (!empty($NazwaTerminuWysylki)) {
                        $DoZapisaniaXML .= '      <Termin_wysylki><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaTerminuWysylki, false).']]></Termin_wysylki>' . "\r\n";
                    }                
                    
                    unset($NazwaTerminuWysylki);
                    //
                } else {
                    $NaglowekCsv .= 'Termin_wysylki;';
                    $CoDoZapisania .= '"";';
                    $DoZapisaniaXML .= '      <Termin_wysylki><![CDATA[]]></Termin_wysylki>' . "\r\n";                
                }

                $CoDoZapisania .= '"KONIEC"' . "\r\n";

                $DoZapisaniaXML .= '  </Produkt>' . "\r\n" . "\r\n";
                
                $Suma++;
                
            }
            
            if ($_POST['format'] == 'csv') {

                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
                }

            }      

            // jezeli jest do zapisu xml
            if ($_POST['format'] == 'xml') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    ///
                    $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                    $CoDoZapisania .= '<Produkty>' . "\r\n" . "\r\n";
                    $CoDoZapisania .= $DoZapisaniaXML;
                    //
                  } else {
                    //
                    $CoDoZapisania = $DoZapisaniaXML;
                    //
                }
                //
                // koniec pliku
                if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= $Suma) {
                    $CoDoZapisania .= '</Produkty>' . "\r\n";
                }
                unset($Suma);
            }            
            
            fwrite($fp, $CoDoZapisania);
            
            // zapisanie danych do pliku
            flock($fp, 3);
            // zamkniecie pliku
            fclose($fp);    

            unset($DoZapisaniaXML);
            
        }    

    }
    
    
    // jezeli jest ilosc, dostepnosc i cena
    if (isset($_POST['zakres']) && $_POST['zakres'] == 'cena_ilosc') {  

        // jezeli sa warunki statusu
        $pr_status = '';

        if (isset($_POST['status_produktow']) && (int)$_POST['status_produktow'] == 1) {
            $pr_status = 'products_status = 1';
        }
        if (isset($_POST['status_produktow']) && (int)$_POST['status_produktow'] == 0) {
            $pr_status = 'products_status = 0';
        }        
        if (isset($_POST['status_produktow']) && (int)$_POST['status_produktow'] == 4) {
            $pr_status = 'products_status = 1 and listing_status = 0';
        }
        
        $zapytanie = "select distinct * from products " . (($pr_status != '') ? ' where ' . $pr_status : '') . " order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        // jezeli sa warunki
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
            $zapytanie = "select distinct * from products where manufacturers_id = '" . (int)$_POST['filtr'] . "'" . (($pr_status != '') ? ' and ' . $pr_status : '') . " order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
            $zapytanie = "select distinct * from products p, products_to_categories pc where p.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "'" . (($pr_status != '') ? ' and ' . $pr_status : '') . " order by p.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }   
        
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $CoDoZapisania = '';
            $DoZapisaniaXML = '';

            // uchwyt pliku, otwarcie do dopisania
            $fp = fopen($filtr->process($_POST['plik']), "a");
            // blokada pliku do zapisu
            flock($fp, 2);
            
            $Suma = $_POST['limit'];
        
            while ($info = $sql->fetch_assoc()) {
            
                $NaglowekCsv = '';
                $DoZapisaniaXML .= '  <Produkt>' . "\r\n";
            
                $zapytanieNazwaProduktu = "select distinct p.products_id, p.products_model, pd.products_id, pd.products_name from products p, products_description pd where p.products_id = '".(int)$info['products_id']."' and p.products_id = pd.products_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                $sqlNazwa = $db->open_query($zapytanieNazwaProduktu);
                $infc = $sqlNazwa->fetch_assoc();
                
                // id produktu
                if ( isset($Konfiguracja['Id_produktu']) && $Konfiguracja['Id_produktu'] == 1 ) {
                    //
                    $NaglowekCsv .= 'Id_produktu;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_id']) . '";';
                    $DoZapisaniaXML .= '      <Id_produktu>'.Funkcje::CzyszczenieTekstu($infc['products_id']).'</Id_produktu>' . "\r\n";                 
                    //
                }                    
                
                $NaglowekCsv .= 'Nr_katalogowy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_model']) . '";';
                if (!empty($infc['products_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_model'], false).']]></Nr_katalogowy>' . "\r\n";
                }             
                
                $NaglowekCsv .= 'Nazwa_produktu;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_name']) . '";'; 
                if (!empty($infc['products_name'])) {
                    $DoZapisaniaXML .= '      <Nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_name'], false).']]></Nazwa_produktu>' . "\r\n";
                }             

                $db->close_query($sqlNazwa);
                unset($infc, $zapytanieNazwaProduktu); 

                $NaglowekCsv .= 'Ilosc_produktow;';
                $CoDoZapisania .= '"' . $info['products_quantity'] . '";';
                $DoZapisaniaXML .= '      <Ilosc_produktow>'.$info['products_quantity'].'</Ilosc_produktow>' . "\r\n";           
                
                // dostepnosc
                //
                if ((int)$info['products_availability_id'] != '99999') {
                    //
                    $NazwaDostepnosci = '';
                    if ( isset($Dostepnosci[(int)$info['products_availability_id']][1]) ) {
                        //
                        $NazwaDostepnosci = $Dostepnosci[(int)$info['products_availability_id']][1];
                        //
                    }                        
                    
                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaDostepnosci) . '";';
                    if (!empty($NazwaDostepnosci)) {
                        $DoZapisaniaXML .= '      <Dostepnosc><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaDostepnosci, false).']]></Dostepnosc>' . "\r\n";
                    }                
                    
                    unset($NazwaDostepnosci);
                    //
                   } else {
                    //
                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"AUTOMATYCZNY";';
                    $DoZapisaniaXML .= '      <Dostepnosc><![CDATA[AUTOMATYCZNY]]></Dostepnosc>' . "\r\n";                 
                    //
                }
                
                // cena brutto
                $NaglowekCsv .= 'Cena_brutto;';
                $CoDoZapisania .= '"' . $info['products_price_tax'] . '";'; 
                $DoZapisaniaXML .= '      <Cena_brutto>'.$info['products_price_tax'].'</Cena_brutto>' . "\r\n";

                // ceny brutto hurtowe
                for ($x = 1; $x <= ILOSC_CEN; $x++) {
                    if ($x > 1) {
                        $NaglowekCsv .= 'Cena_brutto_'.$x.';';
                        $CoDoZapisania .= '"' . $info['products_price_tax_'.$x]. '";'; 
                        if ($info['products_price_'.$x] > 0) {
                            $DoZapisaniaXML .= '      <Cena_brutto_'.$x.'>'.$info['products_price_tax_'.$x].'</Cena_brutto_'.$x.'>' . "\r\n";
                        }                    
                    }
                }

                $CoDoZapisania .= '"KONIEC"' . "\r\n";

                $DoZapisaniaXML .= '  </Produkt>' . "\r\n" . "\r\n";
                
                $Suma++;

            }
            
            if ($_POST['format'] == 'csv') {
      
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
                }

            }      

            // jezeli jest do zapisu xml
            if ($_POST['format'] == 'xml') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    ///
                    $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                    $CoDoZapisania .= '<Produkty>' . "\r\n" . "\r\n";
                    $CoDoZapisania .= $DoZapisaniaXML;
                    //
                  } else {
                    //
                    $CoDoZapisania = $DoZapisaniaXML;
                    //
                }
                //
                // koniec pliku
                if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= $Suma) {
                    $CoDoZapisania .= '</Produkty>' . "\r\n";
                }
                unset($Suma);
            }            
            
            fwrite($fp, $CoDoZapisania);
            
            // zapisanie danych do pliku
            flock($fp, 3);
            // zamkniecie pliku
            fclose($fp);       

            unset($DoZapisaniaXML);
            
        }    

    } 
    
    // jezeli jest eksport allegro
    if (isset($_POST['zakres']) && $_POST['zakres'] == 'allegro') {      
        //
        include('import_danych/export_allegro.php');
        //
    }

    unset($JednostkiMiary, $Dostepnosci, $Vat, $Producenci, $Walut);


    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
        //
        $fp = fopen($filtr->process($_POST['plik']), "a");
        // blokada pliku do zapisu
        flock($fp, 2);

        $CoDoZapisania = "</Produkty>\r\n";
        fwrite($fp, $CoDoZapisania);
            
        // zapisanie danych do pliku
        flock($fp, 3);
        // zamkniecie pliku
        fclose($fp);       

        //
    }   

}
?>