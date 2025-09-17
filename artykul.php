<?php

// plik
$WywolanyPlik = 'artykul';

include('start.php');

$Artykul = Aktualnosci::AktualnoscId( (int)$_GET['idartykul'] );

if (!empty($Artykul)) { 
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr($Artykul['seo']);
    //
    // aktualizacja informacji o wyswietlaniach artykulu
    $pola = array(array('newsdesk_article_viewed', $Artykul['wyswietlenia'] + 1));		
    $GLOBALS['db']->update_query('newsdesk_description' , $pola, " newsdesk_id = '".(int)$_GET['idartykul']."' AND language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'");	
    unset($pola); 
    $Artykul['wyswietlenia'] = $Artykul['wyswietlenia'] + 1;
    //
    // poniewaz jest aktualizowany licznik wyswietlen musi usunac cache aktualnosci
    $GLOBALS['cache']->UsunCacheAktualnosci();

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    
    // meta tagi
    if ( $Meta['nazwa_pliku'] != null ) { 
         //     
         $tpl->dodaj('__META_TYTUL', MetaTagi::MetaTagiArtykulPodmien('tytul', $Artykul, $Meta));
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', MetaTagi::MetaTagiArtykulPodmien('slowa', $Artykul, $Meta));
         $tpl->dodaj('__META_OPIS', MetaTagi::MetaTagiArtykulPodmien('opis', $Artykul, $Meta));
         //
      } else {
         //
         $tpl->dodaj('__META_TYTUL', ((empty($Artykul['meta_tytul'])) ? $Meta['tytul'] : $Artykul['meta_tytul']));
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Artykul['meta_slowa'])) ? $Meta['slowa'] : $Artykul['meta_slowa']));
         $tpl->dodaj('__META_OPIS', ((empty($Artykul['meta_opis'])) ? $Meta['opis'] : $Artykul['meta_opis']));
         //         
    }
    unset($Meta);
    
    $TytulOpenGraph = '';
    $OpisOpenGraph = '';
    $ZdjecieOpenGraph = '';
    
    if ( !empty($Artykul['og_title']) ) {
         //
         $TytulOpenGraph = trim((string)$Artykul['og_title']);
         //
    }
    if ( !empty($Artykul['og_description']) ) {
         //
         $OpisOpenGraph = trim((string)$Artykul['og_description']);
         //
    }
    if ( !empty($Artykul['og_image']) ) {
         //
         $ZdjecieOpenGraph = trim((string)$Artykul['og_image']);
         //
    }    
    
    if ( $TytulOpenGraph != '' && $ZdjecieOpenGraph != '' ) {
    
        // tagi Open Graph
        $TagiOpenGraph = '<meta property="og:title" content="' . $TytulOpenGraph . '" />' . "\n";
        
        if ( !empty($OpisOpenGraph) ) {
             $TagiOpenGraph .= '<meta property="og:description" content="' . $OpisOpenGraph . '" />' . "\n";
        }

        $TagiOpenGraph .= '<meta property="og:type" content="article" />' . "\n";
        
        if (!empty($Artykul['nazwa_kategorii'])) {
             $TagiOpenGraph .= '<meta property="article:section" content="' . $Artykul['nazwa_kategorii'] . '" />' . "\n";
        }
        
        if ( AKTUALNOSCI_DATA == 'tak' ) {
             $TagiOpenGraph .= '<meta property="article:published_time" content="' . date('c', FunkcjeWlasnePHP::my_strtotime($Artykul['data'])) . '" />' . "\n";
        }
        
        $TagiOpenGraph .= '<meta property="og:url" content="' . ADRES_URL_SKLEPU . '/' . $Artykul['seo'] . '" />' . "\n";
        $TagiOpenGraph .= '<meta property="og:image" content="' . ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $ZdjecieOpenGraph . '" />' . "\n";    
        
        $tpl->dodaj('__TAGI_OPEN_GRAPH', $TagiOpenGraph); 

    }
    
    // link kanoniczny dla artykułu
    if ( $Artykul['link_kanoniczny'] != '' ) {
         //
         $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $Artykul['link_kanoniczny'] . '" />');    
         //
    }    

    // Breadcrumb dla kategorii i artykulu
    if (!empty($Artykul['nazwa_kategorii'])) {
        if ( $Artykul['parent'] > 0 ) {
             //    
             $KategoriaNadrzedna = Aktualnosci::KategoriaAktualnoscId( $Artykul['parent'] );
             $nawigacja->dodaj($KategoriaNadrzedna['nazwa'], $KategoriaNadrzedna['seo']);
             unset($KategoriaNadrzedna);
             //
        }      
        $nawigacja->dodaj($Artykul['nazwa_kategorii'], Seo::link_SEO($Artykul['nazwa_kategorii'], $Artykul['id_kategorii'], 'kategoria_aktualnosci'));
    }
    $nawigacja->dodaj($Artykul['tytul'], $Artykul['seo']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI') ), $GLOBALS['tlumacz'] );

    // dane kategorii do jakiej nalezy artykul
    $KategoriaArtykulu = array();
    if (!empty($Artykul['id_kategorii'])) {
         //    
         $KategoriaArtykulu = Aktualnosci::KategoriaAktualnoscId( $Artykul['id_kategorii'] );
         //
    }    
    
    // aktywne podziel sie
    $PodzielSiePortale = explode(',', (string)INTEGRACJA_PODZIEL_SIE_PORTALE);
    $Artykul['podziel_sie'] = array();
    //
    foreach ( $PodzielSiePortale as $Tmp ) {
        //
        $Artykul['podziel_sie'][$Tmp] = $Tmp;
        //
    }
    //
    unset($PodzielSiePortale, $Tmp);     

    // wyglad srodkowy
    $srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik), $Artykul, $KategoriaArtykulu ); 
    unset($KategoriaArtykulu);
    //
    $srodek->dodaj('__NAGLOWEK_ARTYKULU',$Artykul['tytul']);
    //
    // czy pokazywac skrocony opis strony
    if ( AKTUALNOSCI_INFO_SKROCONY_OPIS == 'tak' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_KROTKI',$Artykul['opis_krotki'] . '<br /><br />');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_KROTKI','');
    }
    // czy pokazywac autora
    if ( AKTUALNOSCI_AUTOR == 'tak' && $Artykul['autor'] != '' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_AUTOR','<em class="AutorArtykulu">' . $GLOBALS['tlumacz']['AUTOR'] . ' ' . $Artykul['autor'] . '</em>');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_AUTOR','');
    }    
    // czy pokazywac date dodania artykulu
    if ( AKTUALNOSCI_DATA == 'tak' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_DATA_DODANIA', '<em class="DataDodania">' . $GLOBALS['tlumacz']['DATA_DODANIA_ARTYKULU'] . ' ' . $Artykul['data'] . '</em>');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_DATA_DODANIA','');
    }    
    // czy pokazywac ilosc odslon
    if ( AKTUALNOSCI_ILOSC_ODSLON == 'tak' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_ILOSC_ODSLON', '<em class="IloscOdslon">' . $GLOBALS['tlumacz']['ILOSC_WYSWIETLEN'] . ' ' . $Artykul['wyswietlenia'] . '</em>');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_ILOSC_ODSLON','');
    }    
    //
    $srodek->dodaj('__TRESC_ARTYKULU',$Artykul['opis']);
    //
    // komentarze
    $srodek->dodaj('__KOMENTARZE_ILOSC',count($Artykul['komentarze']));
    $srodek->dodaj('__KOD_JS_FORMULARZA','<script>$.post("inne/artykul_komentarz.php?tok=' . Sesje::Token() . '", { id: ' . $Artykul['id'] . ' }, function(data) { $(\'#NapiszKomentarz\').html(data); }); </script>');
    //
    // podziel sie
    $srodek->dodaj('__ADRES_STRONY_ARTYKULU', urlencode(ADRES_URL_SKLEPU . '/' . $Artykul['seo']));
    $srodek->dodaj('__NAZWA_ARTYKULU_PODZIEL_SIE', urlencode($Artykul['tytul'])); 
    
    if ( file_exists(KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']) && $Artykul['foto_artykulu'] != '' ) {
         $srodek->dodaj('__URL_ZDJECIE_DUZE', urlencode(ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']));
    } else {
         $srodek->dodaj('__URL_ZDJECIE_DUZE', '');
    }
    
    // dane strukturalne
    if ( $Artykul['dane_strukturalne_status'] == 'tak' ) {
         //
         if ( $Artykul['dane_strukturalne_typ'] == 'artykuł' ) {
              $srodek->dodaj('__DANE_STRUKTURALNE_ITEMPROP_SCHEMA_ORG', 'itemscope itemtype="http://schema.org/Article"');
         } else {
              $srodek->dodaj('__DANE_STRUKTURALNE_ITEMPROP_SCHEMA_ORG', 'itemscope itemtype="http://schema.org/BlogPosting"');
         }
         //
         $srodek->dodaj('__DANE_STRUKTURALNE_NAZWA', 'itemprop="headline"');
         $srodek->dodaj('__DANE_STRUKTURALNE_TRESC', 'itemprop="articleBody"');
         
         $srodek->dodaj('__DANE_STRUKTURALNE_POZOSTALE', '<div itemprop="author" itemscope itemtype="https://schema.org/Person" style="display:none">
                                                              <meta itemprop="name" content="' . ((!empty($Artykul['autor'])) ? $Artykul['autor'] : DANE_NAZWA_FIRMY_SKROCONA) . '" />
                                                              <meta itemprop="url" content="' . ADRES_URL_SKLEPU . '" />
                                                          </div>
                                                          
                                                          <meta itemprop="datePublished" content="' . date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($Artykul['data'])) . '" />
                                                          <meta itemprop="dateModified" content="' . date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($Artykul['data'])) . '" />

                                                          <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="display:none">
                                                               <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                                                                    <meta itemprop="url" content="' . $Artykul['dane_strukturalne_wydawca_foto'] . '" />
                                                               </div>
                                                               <meta itemprop="name" content="' . $Artykul['dane_strukturalne_wydawca_nazwa'] . '" />
                                                          </div>
                                                       
                                                          <link itemprop="mainEntityOfPage" href="' . ADRES_URL_SKLEPU . '/' . $Artykul['seo'] . '" />

                                                          <div itemprop="image" itemscope itemtype="https://schema.org/ImageObject" style="display:none">
                                                               <meta itemprop="url" content="' . KATALOG_ZDJEC . '/' . ((!empty($Artykul['foto_artykulu'])) ? $Artykul['foto_artykulu'] : 'domyslny.webp') . '" />
                                                          </div>');

    } else {

         $srodek->dodaj('__DANE_STRUKTURALNE_ITEMPROP_SCHEMA_ORG', '');

         $srodek->dodaj('__DANE_STRUKTURALNE_NAZWA', '');
         $srodek->dodaj('__DANE_STRUKTURALNE_TRESC', '');
         
         $srodek->dodaj('__DANE_STRUKTURALNE_POZOSTALE', '');
      
    }
    //
  } else {
    //
    unset($WywolanyPlik);
    //
    Funkcje::PrzekierowanieURL('brak-strony.html', true); 
    //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik, $Artykul);

include('koniec.php');

?>