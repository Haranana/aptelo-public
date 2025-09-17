<?php

// plik
$WywolanyPlik = 'pp_bannery';

include('start.php');

$Tablica = array();

$Litery = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 's');
$Cyfry = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ',');

if ( ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) && ( SYSTEM_PUNKTOW_STATUS == 'tak' && PP_STATUS == 'tak' ) ) {
  
    if ( !Punkty::PunktyAktywneDlaKlienta() ) {
        //
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        //      
    }

    //
    $zapytanie = "SELECT * FROM pp_banners";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ( $info = $sql->fetch_assoc() ) {
        
          $rodzielczosc = '';
          if (!empty($info['pp_image'])) {
            // wielkosc pliku
            $Kb = filesize($info['pp_image']);
            // ustalenie czy plik jest obrazkiem
            //
            if ( $Kb > 0 ) {
                // czy plik jest obrazkiem
                if (getimagesize($info['pp_image']) != false) {
                    //
                    list($szerokosc, $wysokosc) = getimagesize($info['pp_image']);
                    $rodzielczosc = $szerokosc . ' x ' . $wysokosc;
                    //
                }
            }                                            
            // 
          }

          $kodHtml = htmlspecialchars('<a href="'.ADRES_URL_SKLEPU.'/pp-sklep-' . str_replace($Cyfry, $Litery, (string)$_SESSION['customer_id'] . ',' . PP_ILOSC_DNI) . '.html"><img src="'.ADRES_URL_SKLEPU.'/'.$info['pp_image'].'" alt="'.$info['pp_image_alt'].'" /></a>');

          $Tablica[] = array('opis_banneru' => $info['pp_description'],
                             'obrazek' => $info['pp_image'],
                             'rozdzielczosc' => $rodzielczosc,
                             'kod_html' => $kodHtml);
                             
        }
        
        unset($info);
        
    }
                       
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $rozdzielczosc, $kodHtml);

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI','KLIENCI_PANEL','REJESTRACJA') ), $GLOBALS['tlumacz'] );

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO($GLOBALS['tlumacz']['PANEL_KLIENTA'], $WywolanyPlik, 'inna'));
$nawigacja->dodaj($GLOBALS['tlumacz']['PP_BANNERY']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $Tablica);

$srodek->dodaj('__PP_INFO_ILOSC_DNI', str_replace('{ILOSC_DNI}', PP_ILOSC_DNI, $GLOBALS['tlumacz']['PP_INFO_ILOSC_DNI']));

// sposob naliczania punktow
if ( PP_SPOSOB_NALICZANIA == 'procent' ) {
     $srodek->dodaj('__PP_INFO_ILOSC_PKT', str_replace('{ILOSC_PROCENT}', PP_PROWIZJA_PROCENT, $GLOBALS['tlumacz']['PP_INFO_ILOSC_PKT_PROCENT']));
   } else {
     $srodek->dodaj('__PP_INFO_ILOSC_PKT', str_replace('{ILOSC_PUNKTOW}', PP_PROWIZJA, $GLOBALS['tlumacz']['PP_INFO_ILOSC_PKT']));
}

// czy punkty za kazde zamowienie czy tylko za pierwsze
if ( PP_NALICZANIE == 'wszystkie' ) {
     $srodek->dodaj('__PP_INFO_ZA_JAKIE_ZAMOWIENIA', $GLOBALS['tlumacz']['PP_INFO_KAZDE_ZAMOWIENIE']);
   } else {
     $srodek->dodaj('__PP_INFO_ZA_JAKIE_ZAMOWIENIA', $GLOBALS['tlumacz']['PP_INFO_PIERWSZE_ZAMOWIENIE']);
}

$srodek->dodaj('__PP_LINK_DO_SKLEPU',ADRES_URL_SKLEPU.'/pp-sklep-' . str_replace($Cyfry, $Litery, (int)$_SESSION['customer_id'] . ',' . PP_ILOSC_DNI) . '.html');

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik, $Tablica, $Litery, $Cyfry);

include('koniec.php');

?>