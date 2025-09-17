<?php

// plik
$WywolanyPlik = 'panel_klienta';

include('start.php');

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'PUNKTY', 'KOSZYK') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    $DodatkoweInformacje = array();
    
    // dodatkowe dane klienta
    $zapytanie = "SELECT * FROM customers c, customers_info ci where c.customers_id = '" . (int)$_SESSION['customer_id'] . "' and c.customers_id = ci.customers_info_id";
    $sql = $db->open_query($zapytanie);  

    $DaneKlienta = $sql->fetch_assoc();

    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);     
    
    // dodatkowe informacje w panelu klienta
    $zapytanie = "SELECT cafd.customers_account_fields_id,
                         cafd.customers_account_fields_name, 
                         cafd.customers_account_fields_text, 
                         caf.customers_account_fields_type
                    FROM customers_account_fields caf, 
                         customers_account_fields_description cafd 
                   WHERE caf.customers_id = '" . (int)$_SESSION['customer_id'] . "' AND caf.customers_account_fields_id = cafd.customers_account_fields_id AND cafd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
     
    $sql = $db->open_query($zapytanie);  

    while ( $info = $sql->fetch_assoc() ) {    
        //
        $Tytul = '<b>' . $info['customers_account_fields_name'] . '</b>';
        $Wartosc = $info['customers_account_fields_text'];
        //
        if ( $info['customers_account_fields_type'] == 1 ) {
             //
             $Tytul = '';
             $Wartosc = '<a href="' . $Wartosc . '" target="_blank"><b>' . $info['customers_account_fields_name'] . '</b></a>';
             //
        }
        if ( $info['customers_account_fields_type'] == 2 ) {
             //
             $Tytul = '';
             //
             $UniqId = ($info['customers_account_fields_id'] * $info['customers_account_fields_id']);
             //
             $KluczSesji = base64_encode(serialize(array('tok' => Sesje::Token(), 'data' => $DaneKlienta['customers_info_date_account_created'], 'id' => $DaneKlienta['customers_id'], 'nr' => $UniqId)));
             //
             $LinkPobierz = 'panel-klienta-pobierz-' . $KluczSesji . '.html';
             //
             unset($UniqId, $KluczSesji);             
             //
             $Wartosc = '<a href="' . $LinkPobierz . '" target="_blank"><b>' . $info['customers_account_fields_name'] . '</b></a>';
             //
             unset($LinkPobierz);
             //
        }
        //
        $DodatkoweInformacje[] = array('tytul' => $Tytul,
                                       'wartosc' => $Wartosc);
        //
        unset($Tytul, $Wartosc);
        //
    }

    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie, $info);    

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), ( isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : '0' ), $DodatkoweInformacje, ( isset($DaneKlienta['pp_code']) ? $DaneKlienta['pp_code'] : '' ));
    
    $srodek->dodaj('__INFO_SUMOWANIE_RABATOW',  str_replace('{SKLADNIA}', ((RABAT_SUMOWANIE == 'tak') ? '' : '<b>nie</b>'), (string)$GLOBALS['tlumacz']['INFO_SUMOWANIE_RABATOW']));
    
    $srodek->dodaj('__INFO_MAKSYMALNA_WARTOSC_RABATOW', $GLOBALS['tlumacz']['INFO_MAKSYMALNA_WARTOSC_RABATOW'] . ' <b>' . RABAT_MAKSYMALNA_WARTOSC . '%</b>');
    
    $srodek->dodaj('__INFO_PRODUKTY_PROMOCYJNE_RABATY',  str_replace('{SKLADNIA}', ((RABATY_PROMOCJE == 'tak') ? '' : '<b>nie</b>'), (string)$GLOBALS['tlumacz']['INFO_PRODUKTY_PROMOCYJNE_RABATY']));
    
    $srodek->dodaj('__ILOSC_WEJSC_BANNERY', ((isset($_SESSION['pp_statystyka'])) ? $_SESSION['pp_statystyka'] : 0));
    
    $srodek->dodaj('__NAZWA_GRUPY_KLIENTA', ((isset($_SESSION['customers_groups_name'])) ? $_SESSION['customers_groups_name'] : ''));
    
    // integracja z Klaviyo
    $wynikKlaviyo = IntegracjeZewnetrzne::KlaviyoIdentyfikacjaKlienta($DaneKlienta['customers_email_address'], $DaneKlienta['customers_firstname'], $DaneKlienta['customers_lastname'], basename($_SERVER['HTTP_REFERER']));
    $srodek->dodaj('__INTEGRACJA_KLAVIYO', $wynikKlaviyo);
    unset($wynikKlaviyo);

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($DaneKlienta);
    
    unset($srodek, $WywolanyPlik, $DodatkoweInformacje);

    include('koniec.php');

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );
    
}
?>