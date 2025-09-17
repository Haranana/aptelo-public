<?php

// plik
$WywolanyPlik = 'galeria';

include('start.php');

// dodatkowy warunek dla grup klientow
$warunekTmp = " and (g.gallery_customers_group_id = '0'";
if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
    $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", g.gallery_customers_group_id)";
}
$warunekTmp .= ") "; 
//    
$zapytanie = "SELECT * FROM gallery g, gallery_description gd WHERE g.id_gallery = gd.id_gallery AND g.id_gallery = '" . (int)$_GET['id'] . "' AND g.gallery_status = 1 AND gd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'" . $warunekTmp;

unset($warunekTmp);

$sql = $GLOBALS['db']->open_query($zapytanie);

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
    //
    $info = $sql->fetch_assoc();
    
    $LinkDoPrzenoszenia = Seo::link_SEO($info['gallery_name'], $info['id_gallery'], 'galeria');
    
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr(Seo::link_SEO($info['gallery_name'], $info['id_gallery'], 'galeria'));
    
    //
    $IloscKolumn = $info['gallery_cols'];
    $WysImg     = $info['gallery_height_image'];
    $SzeImg     = $info['gallery_width_image'];
    $Kadrowanie = $info['gallery_crop_image'];
    $Miniatury  = $info['gallery_image_thumbnail'];

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', ((empty($info['gallery_meta_title_tag'])) ? $Meta['tytul'] : $info['gallery_meta_title_tag']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['gallery_meta_keywords_tag'])) ? $Meta['slowa'] : $info['gallery_meta_keywords_tag']));
    $tpl->dodaj('__META_OPIS', ((empty($info['gallery_meta_desc_tag'])) ? $Meta['opis'] : $info['gallery_meta_desc_tag']));
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($info['gallery_name']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    // style css
    $tpl->dodaj('__CSS_PLIK', ',listingi');       

    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie); 
    
    // wyszukiwanie poszczegolnych pozycji galerii
    $zapytanie = "SELECT * FROM gallery_image WHERE id_gallery = '" . (int)$_GET['id'] . "' AND language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."' ORDER BY gallery_image_sort";
    $sql = $GLOBALS['db']->open_query($zapytanie);    
    
    $IloscObrazkow = (int)$GLOBALS['db']->ile_rekordow($sql);

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), (($info['gallery_pages'] == '1') ? 'tak' : 'nie'), $IloscObrazkow, (int)$info['gallery_pages_quantity']);    
    //
    $srodek->dodaj('__NAGLOWEK_GALERII', $info['gallery_name']);
    $srodek->dodaj('__OPIS_GALERII', '<div class="FormatEdytor">' . $info['gallery_description'] . (($info['gallery_pages'] == '1') ? '<br />' : '') . '</div>');
    //    
    
    // stronicowanie
    $srodek->dodaj('__STRONICOWANIE', '');
    //
    $LinkPrev = '';
    $LinkNext = '';
    //  

    if ( $info['gallery_pages'] == '1' && $IloscObrazkow > (int)$info['gallery_pages_quantity'] ) {

        if ($IloscObrazkow > 0) { 
            //
            $Strony = Stronicowanie::PokazStrony($IloscObrazkow, $LinkDoPrzenoszenia, (int)$info['gallery_pages_quantity']);
            //
            $LinkPrev = ((!empty($Strony[2])) ? "\n" . $Strony[2] : '');
            $LinkNext = ((!empty($Strony[3])) ? "\n" . $Strony[3] : '');    
            //    
            $LinkiDoStron = $Strony[0];
            $LimitSql = $Strony[1];
            //
            $srodek->dodaj('__STRONICOWANIE', $LinkiDoStron);
            //
            $zapytanie = $zapytanie . " LIMIT " . $LimitSql . "," .  (int)$info['gallery_pages_quantity'];
            $GLOBALS['db']->close_query($sql);
            //
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            unset($Strony, $LinkiDoStron, $LimitSql);
        }
        //       
    
    }    
    
    unset($info);
    
    ob_start();

    if (in_array( 'listing_galeria.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_galeria.php');
      } else {
        require('listingi/listing_galeria.php');
    }

    $ListaObrazkow = ob_get_contents();
    ob_end_clean();     
    
    //
    $srodek->dodaj('__ZDJECIA_GALERII', $ListaObrazkow);
    
    $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $LinkDoPrzenoszenia . '" />' . $LinkPrev . $LinkNext);    
    //
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie, $IloscObrazkow, $ListaObrazkow, $LinkDoPrzenoszenia);    
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
unset($srodek);

include('koniec.php');

?>