<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_STATYSTYKI_ILOSC_PRODUKTOW;Czy wyświetlać ilość produktów;tak;tak,nie}}
// {{BOX_STATYSTYKI_ILOSC_KATEGORII;Czy wyświetlać ilość kategorii;tak;tak,nie}}
// {{BOX_STATYSTYKI_ILOSC_PROMOCJI;Czy wyświetlać ilość promocji;tak;tak,nie}}
// {{BOX_STATYSTYKI_ILOSC_NOWOSCI;Czy wyświetlać ilość nowości;tak;tak,nie}}
// {{BOX_STATYSTYKI_ILOSC_KLIENCI_ONLINE;Czy wyświetlać ilość klientów online;tak;tak,nie}}
// {{BOX_STATYSTYKI_ILOSC_KLIENCI_ZALOGOWANI;Czy wyświetlać ilość klientów zalogowanych;tak;tak,nie}}
// {{BOX_STATYSTYKI_ILOSC_ODWIEDZIN;Czy wyświetlać ilość odwiedzin sklepu;tak;tak,nie}}
// {{BOX_STATYSTYKI_DATA_SKLEPU;Czy wyświetlać datę działania sklepu;tak;tak,nie}}
//

if ( defined('BOX_STATYSTYKI_ILOSC_PRODUKTOW') ) { $IloscProduktow = BOX_STATYSTYKI_ILOSC_PRODUKTOW; } else { $IloscProduktow = 'tak'; }
if ( defined('BOX_STATYSTYKI_ILOSC_KATEGORII') ) { $IloscKategorii = BOX_STATYSTYKI_ILOSC_KATEGORII; } else { $IloscKategorii = 'tak'; }
if ( defined('BOX_STATYSTYKI_ILOSC_PROMOCJI') ) { $IloscPromocji = BOX_STATYSTYKI_ILOSC_PROMOCJI; } else { $IloscPromocji = 'tak'; }
if ( defined('BOX_STATYSTYKI_ILOSC_NOWOSCI') ) { $IloscNowosci = BOX_STATYSTYKI_ILOSC_NOWOSCI; } else { $IloscNowosci = 'tak'; }
if ( defined('BOX_STATYSTYKI_ILOSC_KLIENCI_ONLINE') ) { $IloscKlientow = BOX_STATYSTYKI_ILOSC_KLIENCI_ONLINE; } else { $IloscKlientow = 'tak'; }
if ( defined('BOX_STATYSTYKI_ILOSC_KLIENCI_ZALOGOWANI') ) { $IloscZalogowanych = BOX_STATYSTYKI_ILOSC_KLIENCI_ZALOGOWANI; } else { $IloscZalogowanych = 'tak'; }
if ( defined('BOX_STATYSTYKI_ILOSC_ODWIEDZIN') ) { $IloscOdwiedzin = BOX_STATYSTYKI_ILOSC_ODWIEDZIN; } else { $IloscOdwiedzin = 'tak'; }
if ( defined('BOX_STATYSTYKI_DATA_SKLEPU') ) { $DataSklepu = BOX_STATYSTYKI_DATA_SKLEPU; } else { $DataSklepu = 'tak'; }

echo '<ul class="Stat">';
//
// ilosc produktow
    //
    if ( $IloscProduktow == 'tak' ) {
         echo '<li>{__TLUMACZ:STATYSTYKA_ILOSC_PRODUKTOW} <b>' . count(Produkty::ProduktyModulowe(1000000, 'produkty')) . '</b></li>';
    }
    //
//
// ilosc kategorii
    //
    if ( $IloscKategorii == 'tak' ) {
         echo '<li>{__TLUMACZ:STATYSTYKA_ILOSC_KATEGORII} <b>' . count($GLOBALS['tablicaKategorii']) . '</b></li>';
    }
    //
// ilosc promocji
    //
    if ( $IloscPromocji == 'tak' ) {
         echo '<li>{__TLUMACZ:STATYSTYKA_ILOSC_PROMOCJI} <a href="promocje.html"><b>' . count(Produkty::ProduktyModulowe(1000000, 'promocje')) . '</b></a></li>';
    }
    //
// ilosc nowosci
    //
    if ( $IloscNowosci == 'tak' ) {
         echo '<li>{__TLUMACZ:STATYSTYKA_ILOSC_NOWOSCI} <a href="nowosci.html"><b>' . count(Produkty::ProduktyModulowe(1000000, 'nowosci')) . '</b></a></li>';
    }
    //    
    
if ( $IloscKlientow == 'tak' || $IloscZalogowanych == 'tak' ) {
    
    $TablicaKlientow = SklepOnline::IloscKlientowOnline();    

    // ilosc klientow na stronie
        //
        if ( $IloscKlientow == 'tak' ) {
             echo '<li>{__TLUMACZ:STATYSTYKA_KLIENCI_ONLINE} <b>' . $TablicaKlientow['klienci_online'] . '</b></li>';
        }
        //
    //
    // ilosc klientow zalogowanych na stronie
        //
        if ( $IloscZalogowanych == 'tak' && $TablicaKlientow['klienci_zalogowani'] > 0 ) {
             echo '<li>{__TLUMACZ:STATYSTYKA_KLIENCI_ZALOGOWANI} <b>' . $TablicaKlientow['klienci_zalogowani'] . '</b></li>';
        }
        //
    //

    unset($TablicaKlientow);
    
}

// ilosc odwiedzin
    //
    if ( $IloscOdwiedzin == 'tak' ) {
         echo '<li>{__TLUMACZ:STATYSTYKA_SKLEP_ODWIEDZILO} <b>{__ILOSC_ODWIEDZIN}</b> {__TLUMACZ:STATYSTYKA_SKLEP_ODWIEDZILO_KLIENTOW}</li>';
    }
    //
// sklep dziala od
    //
    if ( $DataSklepu == 'tak' ) {
         echo '<li>{__TLUMACZ:STATYSTYKA_DATA_SKLEPU} <b>{__DATA_LICZNIKA_ODWIEDZIN}</b></li>';
    }
    //
echo '</ul>';
//

?>