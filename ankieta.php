<?php

// plik
$WywolanyPlik = 'ankieta';

include('start.php');

// dodatkowy warunek dla tylko zalogowanych
$TylkoZalogowani = " and p.poll_login = '0'";

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $TylkoZalogowani = "";
    
}

$zapytanie = "SELECT *
                FROM poll p
          INNER JOIN poll_description pd ON p.id_poll = pd.id_poll AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
               WHERE p.poll_status = '1' " . $TylkoZalogowani . " AND p.id_poll = '" . (int)$_GET['ida'] . "'";

$sql = $GLOBALS['db']->open_query($zapytanie);

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
    //
    $info = $sql->fetch_assoc();
   
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr(Seo::link_SEO($info['poll_name'], $info['id_poll'], 'ankieta'));

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', ((empty($info['poll_meta_title_tag'])) ? $Meta['tytul'] : $info['poll_meta_title_tag']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['poll_meta_keywords_tag'])) ? $Meta['slowa'] : $info['poll_meta_keywords_tag']));
    $tpl->dodaj('__META_OPIS', ((empty($info['poll_meta_desc_tag'])) ? $Meta['opis'] : $info['poll_meta_desc_tag']));
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($info['poll_name']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) );  
    //
    $srodek->dodaj('__NAGLOWEK_ANKIETY',$info['poll_name']);
    $srodek->dodaj('__OPIS_ANKIETY','<div class="FormatEdytor">' . $info['poll_description'] . '</div>');
    //
    $GLOBALS['db']->close_query($sql);
    unset($WywolanyPlik, $zapytanie, $info);
    //
    // ile jest w sumie glosow
    $ile_glosow = $GLOBALS['db']->open_query("select SUM(poll_result) as ile_glosow, COUNT('poll_result') as ile_pozycji from poll_field where id_poll = '" . (int)$_GET['ida'] . "' and language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'");
    $infr = $ile_glosow->fetch_assoc();
    $GLOBALS['db']->close_query($ile_glosow);     
    //
    // odpowiedzi
    $wyniki_ankiety = '<table class="Odpowiedzi">';
    $odpowiedzi = $GLOBALS['db']->open_query("select * from poll_field where id_poll = '" . (int)$_GET['ida'] . "' and language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' order by poll_field_sort");
    //
    $SumaGlosow = 0;
    //
    while ($infs = $odpowiedzi->fetch_assoc()) {
        //
        // szerokosc pixela w slupku
        $ilosc_procent = 0;
        if ($infs['poll_result'] > 0) {
            $ilosc_procent = (int)(($infs['poll_result'] / $infr['ile_glosow']) * 100);
        }
        //
        $wyniki_ankiety .= '<tr>
                                <td class="Odpowiedz">
                                    <strong>'.$infs['poll_field'].'</strong>
                                    '.$ilosc_procent.'% (' . $infs['poll_result'] . ')
                                </td>
                                <td class="Slupek"><div style="width:'.$ilosc_procent.'%"></div></td>
                            </tr>';

        $SumaGlosow = $SumaGlosow + (int)$infs['poll_result'];
        //
        unset($ilosc_procent);
        //
    }
    $GLOBALS['db']->close_query($odpowiedzi);
    $wyniki_ankiety .= '</table>'; 

    $srodek->dodaj('__POZYCJE_ANKIETY',$wyniki_ankiety);   
    $srodek->dodaj('__SUMA_GLOSOW',$SumaGlosow);
    //
    unset($ile_glosow, $infr, $wyniki_ankiety, $infs, $SumaGlosow);
    //
  } else {
    //
    $GLOBALS['db']->close_query($sql); 
    unset($WywolanyPlik, $zapytanie);
    //
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
    //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>