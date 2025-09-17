<?php

// plik
$WywolanyPlik = 'opinie';

include('start.php');

if ( OPINIE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$LinkDoPrzenoszenia = Seo::link_SEO('opinie-o-sklepie.php', '', 'inna');

// *****************************
// jezeli byla zmiana sortowania
if (isset($_POST['sortowanie']) && (int)$_POST['sortowanie'] > 0) {
    $_SESSION['sortowanie_opinia'] = (int)$_POST['sortowanie'];
}
// jezeli jest zmiana ilosci recenzji na stronie
if (isset($_POST['ilosc_na_stronie']) && (int)$_POST['ilosc_na_stronie'] > 0) {
    $_SESSION['listing_produktow'] = (int)$_POST['ilosc_na_stronie'];
}
// *****************************


// *****************************
// jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
if (isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
    unset($WywolanyPlik);
    //
    Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia);
}    
// *****************************  

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
//

// style css
$tpl->dodaj('__CSS_PLIK', ',listingi');

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_OPINIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// klasa css dla ilosci recenzji na stronie
for ($k = 1; $k <= 3; $k++) {
    $srodek->dodaj('__CSS_PRODSTR_' . $k, '');
    $srodek->dodaj('__LISTA_ILOSC_PROD_' . $k, LISTING_PRODUKTOW_NA_STRONIE * $k);     
}
$srodek->dodaj('__CSS_PRODSTR_' . ( $_SESSION['listing_produktow'] / LISTING_PRODUKTOW_NA_STRONIE ), 'class="Tak"');

$TablicaSortowania = array( '1' => 'date_added desc',
                            '2' => 'date_added asc',
                            '3' => 'average_rating desc',
                            '4' => 'average_rating asc' );
                            
// dla aktualnego sortowania i dodawanie do zapytania sortowania
$NrSortowania = 1;

if (isset($_SESSION['sortowanie_opinia'])) {
    $Sortowanie = $TablicaSortowania[(int)$_SESSION['sortowanie_opinia']];
    $NrSortowania = $_SESSION['sortowanie_opinia'];
  } else {
    $Sortowanie = $TablicaSortowania[1]; 
}  

$srodek->dodaj('__WYBOR_SORTOWANIE', '<select name="sortowanie" id="sortowanie">
                                          <option value="1" ' . (($NrSortowania == 1) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_DATY'] . ' ' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_OD_NAJNOWSZEJ'] . '</option>
                                          <option value="2" ' . (($NrSortowania == 2) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_DATY'] . ' ' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_OD_NAJSTARSZEJ'] . '</option>                                                              
                                          <option value="3" ' . (($NrSortowania == 3) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_OCENY'] . ' ' . mb_strtolower((string)$GLOBALS['tlumacz']['LISTING_SORTOWANIE_MALEJACO']) . '</option>
                                          <option value="4" ' . (($NrSortowania == 4) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_OCENY'] . ' ' . mb_strtolower((string)$GLOBALS['tlumacz']['LISTING_SORTOWANIE_ROSNACO']) . '</option>                                          
                                      </select>');

unset($NrSortowania); 

$zapytanie = "SELECT rs.* FROM reviews_shop rs WHERE rs.approved = '1' ORDER BY " . $Sortowanie;

$sql = $GLOBALS['db']->open_query( $zapytanie );

// stronicowanie
$srodek->dodaj('__STRONICOWANIE', '');
//
$LinkPrev = '';
$LinkNext = '';
//
$IloscOpinii = (int)$GLOBALS['db']->ile_rekordow($sql);
if ($IloscOpinii > 0) { 
    //
    $Strony = Stronicowanie::PokazStrony($sql, $LinkDoPrzenoszenia);
    //
    $LinkPrev = ((!empty($Strony[2])) ? "\n" . $Strony[2] : '');
    $LinkNext = ((!empty($Strony[3])) ? "\n" . $Strony[3] : '');    
    //    
    $LinkiDoStron = $Strony[0];
    $LimitSql = $Strony[1];
    //
    $srodek->dodaj('__STRONICOWANIE', $LinkiDoStron);
    //
    // zabezpieczenie zeby nie mozna bylo wyswietlic wiecej niz ilosc na stronie x 3
    if ( $_SESSION['listing_produktow'] > LISTING_PRODUKTOW_NA_STRONIE * 3 ) {
         $_SESSION['listing_produktow'] = LISTING_PRODUKTOW_NA_STRONIE * 3;
    }
    //
    $zapytanie = $zapytanie . " LIMIT " . $LimitSql . "," . $_SESSION['listing_produktow'];
    $GLOBALS['db']->close_query($sql);
    //            
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    unset($Strony, $LinkiDoStron, $LimitSql);
}
//

ob_start();

if (in_array( 'listing_opinie.php', $Wyglad->PlikiListingiLokalne )) {
    require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_opinie.php');
  } else {
    require('listingi/listing_opinie.php');
}

$ListaOpinii = ob_get_contents();
ob_end_clean();        

$srodek->dodaj('__LISTA_OPINII', $ListaOpinii);   

// czy jest kolejna strona
$ParametrStrony = '';
if ( isset($_GET['s']) && (int)$_GET['s'] > 1 ) {
     //
     $ParametrStrony = '/s=' . (int)$_GET['s'];
     //
}

$tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $LinkDoPrzenoszenia . $ParametrStrony . '" />' . $LinkPrev . $LinkNext);

unset($ParametrStrony, $LinkDoPrzenoszenia, $IloscOpinii, $ListaOpinii); 

// podsumowanie opinii

$zapytanie = "SELECT * FROM reviews_shop WHERE approved = '1'";
$sql = $GLOBALS['db']->open_query($zapytanie); 

$SredniaOcena = 0;
$SredniaJakosc = 0;
$SredniCzas = 0;
$CenyOcena = 0;
$JakoscOcena = 0;

$SumaGlosow = 0;
$IluPoleca = 0;

while ( $info = $sql->fetch_assoc() ) {

    $SredniaJakosc += $info['handling_rating'];
    $SredniCzas += $info['lead_time_rating'];
    $CenyOcena += $info['price_rating'];
    $JakoscOcena += $info['quality_products_rating'];
    
    $SumaGlosow++;
    
    if ( $info['recommending'] == 1 ) {
         $IluPoleca++;
    }

}

if ( $SumaGlosow > 0 ) {
     $SredniaOcena = number_format(round((5 * (($SredniaJakosc + $SredniCzas + $CenyOcena + $JakoscOcena) / (20 * $SumaGlosow))),1), 1, ',', ' ');
  } else {
     $SredniaOcena = 0;
}

$GLOBALS['db']->close_query($sql); 
unset($zapytanie, $info);

$srodek->dodaj('__CSS_PODSUMOWANIE_OPINII', (($SumaGlosow > 0) ? '' : 'style="display:none"')); 

$srodek->dodaj('__ILOSC_OPINII', $SumaGlosow); 
$srodek->dodaj('__SREDNIA_OCENA', $SredniaOcena); 

$srodek->dodaj('__OCENA_JAKOSC_OBSLUGI', ($SumaGlosow > 0 ? round(($SredniaJakosc / $SumaGlosow),0) : '0')); 
$srodek->dodaj('__OCENA_CZAS_REALIZACJI', ($SumaGlosow > 0 ? round(($SredniCzas / $SumaGlosow),0) : '0')); 
$srodek->dodaj('__OCENA_CENY', ($SumaGlosow > 0 ? round(($CenyOcena / $SumaGlosow),0) : '0')); 
$srodek->dodaj('__OCENA_PRODUKTOW', ($SumaGlosow > 0 ? round(($JakoscOcena / $SumaGlosow),0) : '0')); 

$srodek->dodaj('__PROCENT_POLECA', (($SumaGlosow > 0) ? round((($IluPoleca / $SumaGlosow) * 100),0) : 0) . '%'); 

unset($SredniaOcena, $SredniaJakosc, $SredniCzas, $CenyOcena, $JakoscOcena, $SumaGlosow, $IluPoleca);

/* inne wyrownanie dla ilosci na stronie */
$srodek->dodaj('__CSS_LISTING_POZYCJI_NA_STRONIE', ' IloscProdStronieCalaLinia');

if ( LISTING_WYSWIETLAC_SORTOWANIE == 'nie' ) {
     $srodek->dodaj('__CSS_LISTING_POZYCJI_NA_STRONIE', ' SortowanieDoPrawej');  
}

/* inne wyrownanie dla sortowania */
$srodek->dodaj('__CSS_LISTING_SORTOWANIE', ' SortowanieDoPrawej');  

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>