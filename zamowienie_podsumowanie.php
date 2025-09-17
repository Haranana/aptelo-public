<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'zamowienie_podsumowanie';

include('start.php');

if ( !isset($_SESSION['zamowienie_id']) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 
    
}

$blad = false;
$zapytanie = "SELECT customers_id FROM orders WHERE orders_id = '" . (int)$_SESSION['zamowienie_id'] . "' LIMIT 1";
$sql = $GLOBALS['db']->open_query($zapytanie);

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    //
    $info = $sql->fetch_assoc();
    //
    if ( (int)$info['customers_id'] == (int)$_SESSION['customer_id'] ) {
         //
         $blad = false;
         //
    } else {
         //
         $blad = true;
         //
    }
    //
    unset($info);
    //
} else {
    //
    $blad = true;
    //
}

$GLOBALS['db']->close_query($sql);
unset($zapytanie);

if ( $blad ) {
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
}

unset($blad); 

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA','LOGOWANIE','REJESTRACJA','KLIENCI', 'KLIENCI_PANEL', 'PLATNOSCI', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA') ), $GLOBALS['tlumacz'] );

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZAMOWIENIE_PODSUMOWANIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// pobranie z bazy informacji o zamowieniu
$zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);

if ( count($zamowienie->info) == 0 ) {
     //
     Funkcje::PrzekierowanieURL('brak-strony.html'); 
     //
}

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $zamowienie);

$srodek->dodaj('__ADRES_EMAIL', $zamowienie->klient['adres_email']);
$srodek->dodaj('__WARTOSC_ZAMOWIENIA', $zamowienie->info['wartosc_zamowienia']);
$srodek->dodaj('__WARTOSC_ZAMOWIENIA_LICZBA', $zamowienie->info['wartosc_zamowienia_val']);
$srodek->dodaj('__ZAMOWIENIE_WALUTA', $zamowienie->info['waluta']);
$srodek->dodaj('__NUMER_ZAMOWIENIA', (int)$_SESSION['zamowienie_id']);
$srodek->dodaj('__METODA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
$srodek->dodaj('__WYSYLKA_MODUL', $zamowienie->info['wysylka_modul'] . ($zamowienie->info['wysylka_info'] != '' ? ' ('.$zamowienie->info['wysylka_info'].')' : '' ) );
$srodek->dodaj('__DATA_ZAMOWIENIA', date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])));
$srodek->dodaj('__STATUS_ZAMOWIENIA', Funkcje::pokazNazweStatusuZamowienia($zamowienie->info['status_zamowienia'],(int)$_SESSION['domyslnyJezyk']['id']));

$srodek->dodaj('__PDF_ZAMOWIENIE', '<a class="pdfIkona" href="zamowienia-szczegoly-pdf-'.(int)$_SESSION['zamowienie_id'] . '.html">' . $GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE'] . '</a>');

$srodek->dodaj('__PDF_FAKTURA', '<a class="pdfIkona" href="zamowienia-faktura-pdf-'.(int)$_SESSION['zamowienie_id'] . '.html">' . $GLOBALS['tlumacz']['DRUKUJ_FAKTURE_PROFORMA'] . '</a>');

$platnoscElektroniczna = '';
$platnoscInformacja = '';

if ( $_SESSION['gosc'] == '1' ) {
     $_SESSION['gosc_id_pdf'] = $_SESSION['customer_id'];
}

if ( 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_payu' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_payu_rest' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_blik' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_dotpay' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_przelewy24' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_pbn' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_payeezy' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_santander' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_lukas' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_mbank' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_paypal' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_cashbill' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_transferuj' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_tpay' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_imoje' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_ileasing' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_iraty' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_eservice' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_paynow' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_hotpay' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_bluemedia' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_comfino' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_leaselink' &&
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_paypo' ) {

    $platnoscInformacja = $zamowienie->info['platnosc_info'];

    if ( $_SESSION['gosc'] == '1' ) {
         $_SESSION['gosc_id'] = $_SESSION['customer_id'];
         unset($_SESSION['adresDostawy'], $_SESSION['adresFaktury'], $_SESSION['customer_firstname'], $_SESSION['customer_default_address_id'], $_SESSION['customer_id']);
    }

} else {

    $platnosci = new Platnosci($_SESSION['rodzajDostawy']['wysylka_id']);
    $platnoscElektroniczna = $platnosci->Podsumowanie( $_SESSION['rodzajPlatnosci']['platnosc_id'], $_SESSION['rodzajPlatnosci']['platnosc_klasa'] );
    
}

$srodek->dodaj('__PLATNOSC_INFORMACJA', $platnoscInformacja);
$srodek->dodaj('__PLATNOSC_ELEKTRONICZNA', $platnoscElektroniczna);

$srodek->dodaj('__ZEGAR_ODLICZANIE', '');

if ( PLATNOSCI_ELEKTRONICZNE_LICZNIK == 'tak' ) {

    $ileSekund = 20;

    $zegarOdliczanie = '<div class="OdliczajPlatnoscKontener" style="display:none">
                            <strong>' . $GLOBALS['tlumacz']['LICZNIK_INFO'] . '</strong>
                            <div class="OdliczajPlatnosc">
                                <div class="LicznikZegara">
                                    <b>' . $ileSekund . '</b><small>' . $GLOBALS['tlumacz']['LICZNIK_SEKUNDY'] . '</small>
                                    <div class="OdliczajPlatnoscPostep"></div>
                                </div>
                            </div>
                            <div class="ZatrzymajZegar">' . $GLOBALS['tlumacz']['LICZNIK_ZATRZYMAJ_ZEGAR'] . '</div>
                        </div>';
                                          
    $zegarOdliczanie .= '<script>
                         $(document).ready(function() {
                            if ( $(\'#PlatnoscElektronicznaPodsumowanie .przyciskZaplac\').length ) {
                                 if ( $(\'#check_regulamin_przelewy24\').length == 0 && $(\'#check_regulamin_mbank\').length == 0 && $(\'#check_regulamin_santander\').length == 0 ) {
                                     $(\'.OdliczajPlatnoscKontener\').show();
                                     $(\'.OdliczajPlatnosc\').click(function() {
                                         clearTimeout(zegarPlatnosci);
                                         $(\'.OdliczajPlatnoscKontener\').stop().slideUp();
                                     });
                                     odliczaj_platnosc(' . $ileSekund . ');
                                     function odliczaj_platnosc(sekundy){
                                         $(".OdliczajPlatnosc b").html(sekundy);            
                                         $(".OdliczajPlatnoscPostep").css({ \'height\' : (((' . $ileSekund . ' - sekundy) / ' . $ileSekund . ') * 100) + \'%\' });
                                         if (sekundy > 0) { 
                                             zegarPlatnosci = setTimeout(function(){odliczaj_platnosc(--sekundy)},1e3);
                                           } else {
                                             if ( $(\'#PlatnoscElektronicznaPodsumowanie form\').length ) {
                                                  $(\'#PlatnoscElektronicznaPodsumowanie form\').submit();
                                             } else {
                                                  PreloadWlacz(); document.location = $(\'.przyciskZaplac\').attr(\'href\');
                                             }
                                         }
                                     }
                                 }
                            }
                        });
                        </script>';  
    
    $srodek->dodaj('__ZEGAR_ODLICZANIE', $zegarOdliczanie);
    unset($zegarOdliczanie);
                        
}

// integracja z CRITEO
$tpl->dodaj('__CRITEO', IntegracjeZewnetrzne::CriteoZamowieniePodsumowanie( $zamowienie ));

// integracja z NOKAUT sledzenie konwersji
$tpl->dodaj('__NOKAUT_SLEDZENIE', IntegracjeZewnetrzne::NokautKonwersjaZamowieniePodsumowanie( $zamowienie ));

// integracja z ALLANI I DOMODI
$tpl->dodaj('__ALLANIDOMODI_SLEDZENIE', IntegracjeZewnetrzne::AllaniDomodiZamowieniePodsumowanie( $zamowienie ));

// integracja z Google remarketing dynamiczny i Google Analytics
$wynikGoogle = IntegracjeZewnetrzne::GoogleAnalyticsRemarketingZamowieniePodsumowanie( $zamowienie );
$tpl->dodaj('__GOOGLE_KONWERSJA', $wynikGoogle['konwersja']);
$tpl->dodaj('__GOOGLE_ANALYTICS', $wynikGoogle['analytics']);
unset($wynikGoogle);

// integracja dla edrone
$tpl->dodaj('__EDRONE', IntegracjeZewnetrzne::EdroneZamowieniePodsumowanie( $zamowienie ));    

// integracja z SALESmanago
IntegracjeZewnetrzne::SalesManagoZamowieniePodsumowanie( $zamowienie );

// integracja z Klaviyo
IntegracjeZewnetrzne::KlaviyoZamowieniePodsumowanie( $zamowienie );

$skrypty_afiliacji = '';

// integracja z programem WebePartners
$skrypty_afiliacji .= IntegracjeZewnetrzne::WebePartnersZamowieniePodsumowanie( $zamowienie );

// integracja z shopeneo.network
$skrypty_afiliacji .= IntegracjeZewnetrzne::ShopeneoNetworkZamowieniePodsumowanie( $zamowienie );

// integracja z programem Zaufane Opinie CENEO
$skrypty_afiliacji .= IntegracjeZewnetrzne::ZaufaneOpinieCeneoZamowieniePodsumowanie( $zamowienie );

// integracja z programem okazje.info
$skrypty_afiliacji .= IntegracjeZewnetrzne::OkazjeInfoZamowieniePodsumowanie( $zamowienie );

// integracja z Zaufane opinie - OPINEO
$skrypty_afiliacji .= IntegracjeZewnetrzne::ZaufaneOpinieOpineoZamowieniePodsumowanie( $zamowienie );

// integracja z salesmedia.pl
$skrypty_afiliacji .= IntegracjeZewnetrzne::SalesmediaZamowieniePodsumowanie( $zamowienie );

// integracja z programem Opinie konsumenckie Google
$skrypty_afiliacji .= IntegracjeZewnetrzne::OpinieGoogleZamowieniePodsumowanie( $zamowienie );

// integracja z programem TRUSTISTO
$skrypty_afiliacji .= IntegracjeZewnetrzne::TrustistoZamowieniePodsumowanie( $zamowienie );

// integracja z DomodiPixel
$skrypty_afiliacji .= IntegracjeZewnetrzne::DomodiPixelZamowieniePodsumowanie( $zamowienie );

// integracja z WP Pixel
$skrypty_afiliacji .= IntegracjeZewnetrzne::WpPixelZamowieniePodsumowanie( $zamowienie );

// integracja z TrustPilot
$skrypty_afiliacji .= IntegracjeZewnetrzne::TrusPilotZamowieniePodsumowanie( $zamowienie );

// integracja z Pinterest Tag
$skrypty_afiliacji .= IntegracjeZewnetrzne::PinterestTagZamowieniePodsumowanie( $zamowienie );

// integracja z Trustpmate.io
$skrypty_afiliacji .= IntegracjeZewnetrzne::TrustmateZamowieniePodsumowanie( $zamowienie );

$srodek->dodaj('__SKRYPTY_AFILIACJA', $skrypty_afiliacji);

// integracja z Trusted Shops
$srodek->dodaj('__INTEGRACJA_TRUSTEDSHOPS', IntegracjeZewnetrzne::TrustedShopsZamowieniePodsumowanie( $zamowienie ));

// integracja pixel fb
$srodek->dodaj('__INTEGRACJA_PIKSEL_FB', IntegracjeZewnetrzne::PixelFacebookZamowieniePodsumowanie( $zamowienie ));

// integracje wlasne
$srodek->dodaj('__INTEGRACJE_WLASNE_ZAMOWIENIE_PODSUMOWANIE', ((KOD_BODY_PODSUMOWANIE_ZAMOWIENIA != '') ? IntegracjeZewnetrzne::KodZewnetrznyPodsumowanieZamowienia($zamowienie, base64_decode(KOD_BODY_PODSUMOWANIE_ZAMOWIENIA)) : ''));

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

if ( isset($_SESSION['zamowienie_id']) ) unset($_SESSION['zamowienie_id']);
if ( isset($_SESSION['rodzajPlatnosci']) ) unset($_SESSION['rodzajPlatnosci']);
if ( isset($_SESSION['rodzajDostawy']) ) unset($_SESSION['rodzajDostawy']);
if ( isset($_SESSION['rodzajDostawyKoszyk']) ) unset($_SESSION['rodzajDostawyKoszyk']);
if ( isset($_SESSION['koszyk']) ) unset($_SESSION['koszyk']);
if ( isset($_SESSION['KanalyPlatnosciComfino']) ) unset($_SESSION['KanalyPlatnosciComfino']);
if ( isset($_SESSION['blik_status']) ) unset($_SESSION['blik_status']);

if ( isset($_COOKIE['regulamin']) ) {
     setcookie("regulamin", '', time() - 86400, '/');          
}
if ( isset($_COOKIE['opinie']) ) {
     setcookie("opinie", '', time() - 86400, '/');          
}
if ( isset($_COOKIE['koszykGoldID']) ) {
     setcookie("koszykGoldID", '', time() - 86400, '/');          
}

if ( !isset($_SESSION['koszyk']) ) {
    $_SESSION['koszyk'] = array();   
} 

if ( isset($_SESSION['podsumowanieZamowienia']) ) unset($_SESSION['podsumowanieZamowienia']);
if ( isset($_SESSION['platnoscElektroniczna']) ) unset($_SESSION['platnoscElektroniczna']);
if ( isset($_SESSION['zgodaNaPrzekazanieDanych']) ) unset($_SESSION['zgodaNaPrzekazanieDanych']);

if ( $_SESSION['gosc'] == 1 ) {

     //if ( isset($_SESSION['adresDostawy']) ) unset($_SESSION['adresDostawy']);
     //if ( isset($_SESSION['adresFaktury']) ) unset($_SESSION['adresFaktury']);
     //if ( isset($_SESSION['customer_firstname']) ) unset($_SESSION['customer_firstname']);
     //if ( isset($_SESSION['customer_default_address_id']) ) unset($_SESSION['customer_default_address_id']);

     if ( isset($_SESSION['netto']) ) unset($_SESSION['netto']);
     // if ( isset($_SESSION['customer_email']) ) unset($_SESSION['customer_email']);
     // if ( isset($_SESSION['gosc_id']) ) unset($_SESSION['gosc_id']);

     //$_SESSION['customer_id'] = 0;
     $_SESSION['gosc_id'] = 0;
     $_SESSION['gosc'] = 1;
        
}

include('koniec.php');

?>