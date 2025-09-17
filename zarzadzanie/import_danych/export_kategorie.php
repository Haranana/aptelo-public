<?php
Function GenerujDrzewko($idKategorii, $ile_jezykow, $c, $IleSpacji = 0) {
    global $db;
    //
    $NaglowekCsv = '';
    $CoDoZapisania = '';
    $DoZapisaniaXML = '';
    //
    // dodawanie spacji na poczatku
    $Spacje = '';
    for ($spac = 0; $spac < $IleSpacji; $spac++) {
        $Spacje = $Spacje . ' ';
    }
    //
    for ($w = 0, $cl = count($ile_jezykow); $w < $cl; $w++) {
        //
        $zapytanieKategoria = "select * from categories_description cd, categories c where c.categories_id = cd.categories_id and c.categories_id = '" . $idKategorii . "' and cd.language_id = '" .$ile_jezykow[$w]['id']."'";
        $sqlc = $db->open_query($zapytanieKategoria);  
        $infs = $sqlc->fetch_assoc();                
        //            
        $Przedrostek = '';
        if ($ile_jezykow[$w]['kod'] != 'pl') {
            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
        }
        
        $NaglowekCsv .= 'Kategoria_'.$c.'_nazwa' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_name']) . '";';
        if (!empty($infs['categories_name'])) {
            $DoZapisaniaXML .= $Spacje . '      <Nazwa><![CDATA['.Funkcje::CzyszczenieTekstu($infs['categories_name'], false).']]></Nazwa>' . "\r\n";             
        }
        
        $NaglowekCsv .= 'Kategoria_'.$c.'_zdjecie;';
        $CoDoZapisania .= '"' . $infs['categories_image'] . '";';
        if (!empty($infs['categories_image'])) {
            $DoZapisaniaXML .= $Spacje . '      <Zdjecie>'.$infs['categories_image'].'</Zdjecie>' . "\r\n";
        }                 
        
        $NaglowekCsv .= 'Kategoria_'.$c.'_opis' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_description']) . '";';
        if (!empty($infs['categories_description'])) {
            $DoZapisaniaXML .= $Spacje . '      <Opis><![CDATA['.$infs['categories_description'].']]></Opis>' . "\r\n";
        }                
        
        $NaglowekCsv .= 'Kategoria_'.$c.'_opis_dol' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_description_bottom']) . '";';
        if (!empty($infs['categories_description_bottom'])) {
            $DoZapisaniaXML .= $Spacje . '      <Opis_dol><![CDATA['.$infs['categories_description_bottom'].']]></Opis_dol>' . "\r\n";
        }          

        $NaglowekCsv .= 'Kategoria_'.$c.'_meta_tytul' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_meta_title_tag']) . '";';
        if (!empty($infs['categories_meta_title_tag'])) {
            $DoZapisaniaXML .= $Spacje . '      <Meta_Tytul><![CDATA['.Funkcje::CzyszczenieTekstu($infs['categories_meta_title_tag'], false).']]></Meta_Tytul>' . "\r\n";
        }                 

        $NaglowekCsv .= 'Kategoria_'.$c.'_meta_opis' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_meta_desc_tag']) . '";';
        if (!empty($infs['categories_meta_desc_tag'])) {
            $DoZapisaniaXML .= $Spacje . '      <Meta_Opis><![CDATA['.Funkcje::CzyszczenieTekstu($infs['categories_meta_desc_tag'], false).']]></Meta_Opis>' . "\r\n";
        }                 

        $NaglowekCsv .= 'Kategoria_'.$c.'_meta_slowa' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_meta_keywords_tag']) . '";';
        if (!empty($infs['categories_meta_keywords_tag'])) {
            $DoZapisaniaXML .= $Spacje . '      <Meta_Slowa><![CDATA['.Funkcje::CzyszczenieTekstu($infs['categories_meta_keywords_tag'], false).']]></Meta_Slowa>' . "\r\n";
        }      

        $NaglowekCsv .= 'Kategoria_'.$c.'_adres_url' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_seo_url']) . '";';
        if (!empty($infs['categories_seo_url'])) {
            $DoZapisaniaXML .= $Spacje . '      <Adres_Url><![CDATA['.Funkcje::CzyszczenieTekstu($infs['categories_seo_url']).']]></Adres_Url>' . "\r\n";
        }     

        $NaglowekCsv .= 'Kategoria_'.$c.'_link_kanoniczny' . $Przedrostek . ';';
        $CoDoZapisania .= '"' . ADRES_URL_SKLEPU.'/'.Funkcje::CzyszczenieTekstu($infs['categories_link_canonical']) . '";';
        if (!empty($infs['categories_link_canonical'])) {
            $DoZapisaniaXML .= $Spacje . '      <Link_kanoniczny><![CDATA['.ADRES_URL_SKLEPU.'/'.Funkcje::CzyszczenieTekstu($infs['categories_link_canonical']).']]></Link_kanoniczny>' . "\r\n";
        }              

        $db->close_query($sqlc);
        unset($infs, $zapytanieKategoria);                
    }
    //
    return array( $NaglowekCsv, $CoDoZapisania, $DoZapisaniaXML );
    //
}

function generujXMLkat($tablica_kategorie, $parentId = 0, $odstepy = 0) {
    global $ile_jezykow;
    //
    $xml = '';
    //
    foreach ($tablica_kategorie as $sama_kategoria) {
      
        if ($sama_kategoria['parent'] == $parentId) {
          
            $xml .= "\r\n" . str_repeat("    ", $odstepy); 
            $xml .= "      <Kategoria>\r\n";
            
            $DaneXml = GenerujDrzewko($sama_kategoria['id'], $ile_jezykow, '1', ($odstepy + 1) * 4);
            
            $xml .= $DaneXml[2];
            
            $xml .= generujXMLkat($tablica_kategorie, $sama_kategoria['id'], $odstepy + 1);
            $xml .= str_repeat("    ", $odstepy); 
            $xml .= "      </Kategoria>\r\n\r\n";
            
        }      
      
    }
    //
    return $xml;
    //
}
 
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    $zapytanie = "select distinct * from categories where parent_id = '0' order by sort_order limit ".(int)$_POST['limit'].",1";
    
    $sql = $db->open_query($zapytanie);

    if ((int)$db->ile_rekordow($sql) > 0) {
    
        $NaglowekCsv = '';
        $CoDoZapisania = '';
        $DoZapisaniaXML = '';

        // uchwyt pliku, otwarcie do dopisania
        $fp = fopen($filtr->process($_POST['plik']), "a");
        // blokada pliku do zapisu
        flock($fp, 2);
    
        // jezeli tylko jezyk polski to tworzy tablice tylko z id polski
        if (isset($_POST['jezyk']) && $_POST['jezyk'] == 'pl') {
            $ile_jezykow = array( array('id' => '1','kod' => 'pl') ); 
          } else {            
            $ile_jezykow = Funkcje::TablicaJezykow();
        }
        
        $info = $sql->fetch_assoc();
        
        $DoZapisaniaXML .= '  <Kategoria>' . "\r\n";
        
        // pierwsze drzewo kategorii
        $Zapisz = GenerujDrzewko($info['categories_id'], $ile_jezykow, '1', 0);
        $DoZapisaniaXML .= $Zapisz[2];
        unset($Zapisz);
        
        $Podkategorie = Kategorie::DrzewoKategorii($info['categories_id'], '', '', '', true, true);
        
        $DoZapisaniaXML .= generujXMLkat($Podkategorie, $info['categories_id']);

        $DoZapisaniaXML .= '  </Kategoria>' . "\r\n" . "\r\n";
        
        $CoDoZapisania .= 'KONIEC' . "\r\n";
        
        if ($_POST['limit'] == 0) {
            $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
        }
        
        // jezeli jest do zapisu xml
        if ($_POST['format'] == 'xml') {
            // jezeli poczatek pliku
            if ((int)$_POST['limit'] == 0) {
                ///
                $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                $CoDoZapisania .= '<Kategorie>' . "\r\n";
                $CoDoZapisania .= $DoZapisaniaXML;
                //
              } else {
                //
                $CoDoZapisania = $DoZapisaniaXML;
                //
            }
            //
            // koniec pliku
            if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] == (int)$_POST['limit']) {
                $CoDoZapisania .= '</Kategorie>' . "\r\n";
            }
        }        
        
        fwrite($fp, str_replace("\r\n\r\n\r\n", "\r\n\r\n", (string)$CoDoZapisania));
        
        // zapisanie danych do pliku
        flock($fp, 3);
        // zamkniecie pliku
        fclose($fp);        
        
    }

}
?>