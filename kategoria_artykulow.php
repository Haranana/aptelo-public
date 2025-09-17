<?php

// plik
$WywolanyPlik = 'kategoria_artykulow';

include('start.php');

$KategoriaArtykulu = Aktualnosci::KategoriaAktualnoscId( (int)$_GET['idkatart'] );

if (!empty($KategoriaArtykulu)) {    
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    
    $LinkDoPrzenoszenia = $KategoriaArtykulu['seo'];
    
    Seo::link_Spr($KategoriaArtykulu['seo']);
    
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', ((empty($KategoriaArtykulu['meta_tytul'])) ? $Meta['tytul'] : $KategoriaArtykulu['meta_tytul']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($KategoriaArtykulu['meta_slowa'])) ? $Meta['slowa'] : $KategoriaArtykulu['meta_slowa']));
    $tpl->dodaj('__META_OPIS', ((empty($KategoriaArtykulu['meta_opis'])) ? $Meta['opis'] : $KategoriaArtykulu['meta_opis']));
    unset($Meta);

    $KategoriaNadrzedna = array();

    // Breadcrumb dla kategorii artykulow
    if ( $KategoriaArtykulu['parent'] > 0 ) {
         //    
         $KategoriaNadrzedna = Aktualnosci::KategoriaAktualnoscId( $KategoriaArtykulu['parent'] );
         $nawigacja->dodaj($KategoriaNadrzedna['nazwa'], $KategoriaNadrzedna['seo']);
         //
    }
    $nawigacja->dodaj($KategoriaArtykulu['nazwa'], $KategoriaArtykulu['seo']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    // style css
    $tpl->dodaj('__CSS_PLIK', ',listingi');   
    if (isset($_GET['szukaj']) && trim((string)$_GET['szukaj']) != '' && $KategoriaArtykulu['wyszukiwanie'] == 'tak') {    
        //
        $_GET['szukaj'] = rawurldecode(strip_tags((string)$filtr->process($_GET['szukaj'])));    
        //
        // zamienia zmienne na poprawne znaki
        $_GET['szukaj'] = str_replace(array('[back]', '[proc]'), array('/', '%'), (string)$_GET['szukaj']);
        //
        // zabezpieczenie przez hackiem
        $_GET['szukaj'] = str_replace(array('_', '%'), array('\\_','\\%'), (string)$_GET['szukaj']);
        //
        // usuwanie '
        $_GET['szukaj'] = str_replace("'", "\'", (string)$_GET['szukaj']);
    }

    if (isset($_GET['szukaj']) && trim((string)$_GET['szukaj']) == '' && $KategoriaArtykulu['wyszukiwanie'] == 'tak') {
      Funkcje::PrzekierowanieURL('brak-strony.html');
    }
    // wyszukiwanie artykulow
    $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria( (int)$_GET['idkatart'], 9999, array(), ((isset($_GET['szukaj']) && $KategoriaArtykulu['wyszukiwanie'] == 'tak') ? $_GET['szukaj'] : '') );
    
    $IloscArtykulow = count($TablicaArtykulow);
 
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $IloscArtykulow, $KategoriaArtykulu['wyszukiwanie'], $KategoriaNadrzedna);    
    
    unset($KategoriaNadrzedna);
    //
    $srodek->dodaj('__NAGLOWEK_KATEGORII_ARTYKULU', $KategoriaArtykulu['nazwa']);
    $srodek->dodaj('__OPIS_KATEGORII_ARTYKULU', $KategoriaArtykulu['opis']);
    $srodek->dodaj('__CSS_OPIS_KATEGORII_ARTYKULU', '');
    
    $srodek->dodaj('__ZDJECIE_KATEGORII_ARTYKULU', '');
    $srodek->dodaj('__OPIS_BEZ_ZDJECIA', ' OpisListingGornyBezZdjecia');
    
    if ( strlen((string)$KategoriaArtykulu['opis']) > 40 ) {
      
         if ( $KategoriaArtykulu['foto'] != '' && file_exists(KATALOG_ZDJEC . '/' . $KategoriaArtykulu['foto']) ) {
              //
              $srodek->dodaj('__ZDJECIE_KATEGORII_ARTYKULU', '<div class="ZdjecieListing">' . Funkcje::pokazObrazek($KategoriaArtykulu['foto'], $KategoriaArtykulu['nazwa'], SZEROKOSC_ZDJECIA_KATEGORII_PRODUCENTA, WYSOKOSC_ZDJECIA_KATEGORII_PRODUCENTA, array(), 'class="ZdjecieKategAktualnosci"', 'maly', true, false, false) . '</div>');   
              //
              if ( Wyglad::TypSzablonu() == true ) {
                   //
                   $srodek->dodaj('__OPIS_BEZ_ZDJECIA', '');
                   //
              }
              //              
         }
         
    } else {
      
         $srodek->dodaj('__CSS_OPIS_KATEGORII_ARTYKULU', ' style="display:none"');
         
    }
    // 

    $srodek->dodaj('__ILOSC_WYNIKOW_WYSZUKIWANIA',$IloscArtykulow);
    $srodek->dodaj('__WARUNKI_SZUKANIA','<p><span>' . $GLOBALS['tlumacz']['NAGLOWEK_WYNIKI_SZUKANIA'] . ':</span> <b>' . ((isset($_GET['szukaj'])) ? $_GET['szukaj'] : '') . '</b></p>');
    $srodek->dodaj('__LINK_STRONY',$LinkDoPrzenoszenia);

    // podkategorie lista - do wyswietlenia podkategorii
    $PodkatLista = '';
    //

    $ListaKategorii = Aktualnosci::TablicaKategorieAktualnosci();
    
    $TablicaPodkategorii = array();
    foreach ( $ListaKategorii as $KategoriaAktualnosci ) {
         //
         if ( $KategoriaAktualnosci['parent'] == $KategoriaArtykulu['id'] ) {    
              $TablicaPodkategorii[] = $KategoriaAktualnosci;
         }
         //
    }    
    
    $srodek->dodaj('__CSS_PODKATEGORIE_ARTYKULU', '');

    foreach ( $ListaKategorii as $KategoriaAktualnosci ) {
         //
         if ( $KategoriaAktualnosci['parent'] == $KategoriaArtykulu['id'] ) {
              //
              $PodkatLista .= '<li class="OknoRwd">';
              //
              $PodkatLista .= '<h2><a href="' . $KategoriaAktualnosci['seo'] . '">';
              //
              if (LISTING_PODKATEGORIE_AKTUALNOSCI_ZDJECIA == 'tak') {
                  $PodkatLista .= Funkcje::pokazObrazek($KategoriaAktualnosci['foto'], $KategoriaAktualnosci['nazwa'], SZEROKOSC_MINIATUREK_PODKATEGORII, WYSOKOSC_MINIATUREK_PODKATEGORII, array(), '', 'maly', true, false, false) . '<br />';
              }
              //
              $PodkatLista .= '<span>' . $KategoriaAktualnosci['nazwa'] . '</span>';
              //
              if (LISTING_AKTUALNOSCI_ILOSC_PRODUKTOW == 'tak') {
                  $PodkatLista .= '<em>(' . $KategoriaAktualnosci['ile_artykulow'] . ')</em>';
              }            
              //
              $PodkatLista .= '</a></h2>';
              
              $PodkatLista .= '</li>';          
              //
         }
         //
    }
    
    unset($ListaKategorii, $TablicaPodkategorii);
    
    $srodek->dodaj('__PODKATEGORIE', '');
    
    if ( LISTING_PODKATEGORIE_AKTUALNOSCI == 'tak' ) {
        
        // jezeli sa podkategorie
        if ( !empty($PodkatLista) ) {
            //
            $PodkatLista = '<ul' . ((LISTING_PODKATEGORIE_AKTUALNOSCI_ZDJECIA == 'tak') ? ' class="KategoriaZdjecie OknaRwd Kol-' . LISTING_PODKATEGORIE_AKTUALNOSCI_KOLUMNY . '"' : ' class="KategoriaBezZdjecia OknaRwd Kol-' . LISTING_PODKATEGORIE_AKTUALNOSCI_KOLUMNY . '"') . '>' . $PodkatLista . '</ul>';
            $srodek->dodaj('__PODKATEGORIE', '<strong>' . $GLOBALS['tlumacz']['LISTING_PODKATEGORIE'] . '</strong>' . $PodkatLista);
            //
        } else {
            //
            $srodek->dodaj('__CSS_PODKATEGORIE_ARTYKULU', ' style="display:none"');
            //
        }
    
    } else {
      
        $srodek->dodaj('__CSS_PODKATEGORIE_ARTYKULU', ' style="display:none"');
        
    }
    
    unset($PodkatLista);    
    
    // stronicowanie
    $srodek->dodaj('__STRONICOWANIE', '');
    //
    $LinkPrev = '';
    $LinkNext = '';
    //  
    
    // wywolanie i sprawdzenie stron - zabezpieczenie przed wpisaniem wartosci s ktorej nie ma
    $Strony = Stronicowanie::PokazStrony($IloscArtykulow, $LinkDoPrzenoszenia, (int)AKTUALNOSCI_ILOSC_NA_STRONIE);
    
    if ( AKTUALNOSCI_STRONICOWANIE == 'tak' && $IloscArtykulow > (int)AKTUALNOSCI_ILOSC_NA_STRONIE ) {

        if ($IloscArtykulow > 0) { 
            //
            $LinkPrev = ((!empty($Strony[2])) ? "\n" . $Strony[2] : '');
            $LinkNext = ((!empty($Strony[3])) ? "\n" . $Strony[3] : '');    
            //    
            $LinkiDoStron = $Strony[0];
            $LimitSql = $Strony[1];
            //
            $srodek->dodaj('__STRONICOWANIE', $LinkiDoStron);
            //
            $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria( (int)$_GET['idkatart'], 9999, array($LimitSql, (int)AKTUALNOSCI_ILOSC_NA_STRONIE), ((isset($_GET['szukaj']) && $KategoriaArtykulu['wyszukiwanie'] == 'tak') ? $_GET['szukaj'] : '') );
            //
            unset($Strony, $LinkiDoStron, $LimitSql);
        }
        //       
    
    }
    
    ob_start();
    
    if (in_array( 'listing_artykuly_kategorii.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_artykuly_kategorii.php');
      } else {
        require('listingi/listing_artykuly_kategorii.php');
    }     

    $ListaArtykulow = ob_get_contents();
    ob_end_clean(); 

    //
    $srodek->dodaj('__ARTYKULY_KATEGORII', $ListaArtykulow);
    
    // czy jest kolejna strona
    $ParametrStrony = '';
    if ( isset($_GET['s']) && (int)$_GET['s'] > 1 ) {
         //
         $ParametrStrony = '/s=' . (int)$_GET['s'];
         //
    }

    $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $LinkDoPrzenoszenia . $ParametrStrony . '" />' . $LinkPrev . $LinkNext);
    //
    unset($ParametrStrony, $IloscArtykulow, $ListaArtykulow, $TablicaArtykulow, $LinkDoPrzenoszenia);    
    //
  } else {
    //
    unset($WywolanyPlik);
    //
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
    //    
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik, $KategoriaArtykulu);

include('koniec.php');

?>