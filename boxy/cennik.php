<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_CENNIK_PDF;Czy ma być aktywny cennik w formacie PDF;tak;tak,nie}}
// {{BOX_CENNIK_HTML;Czy ma być aktywny cennik w formacie HTML;tak;tak,nie}}
// {{BOX_CENNIK_XLS;Czy ma być aktywny cennik w formacie Excel;tak;tak,nie}}
//
//

// jezeli ceny sa tylko widoczne dla klientow zalogowanych
if ( (CENY_DLA_WSZYSTKICH == 'tak' || ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] > 0 || $_SESSION['gosc'] == '0') )) && UKRYJ_CENY == 'tak' ) {
            
    $pdf = 'tak';
    $html = 'tak';
    $xls = 'tak';
    //
    if ( defined('BOX_CENNIK_PDF') ) {
       $pdf = BOX_CENNIK_PDF;
    }
    if ( defined('BOX_CENNIK_HTML') ) {
       $html = BOX_CENNIK_HTML;
    }
    if ( defined('BOX_CENNIK_XLS') ) {
       $xls = BOX_CENNIK_XLS;
    }
    //
    echo '<div class="Cennik">';
    //
    if ( $pdf == 'tak' ) {
         //
         if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/pdf.svg') ) {
              //
              echo '<a class="CennikPdf" href="cennik.html/typ=pdf">{__TLUMACZ:POBIERZ_CENNIK} PDF</a>';
              //
         } else {
              //
              echo '<a href="cennik.html/typ=pdf"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/pdf.png" alt="{__TLUMACZ:POBIERZ_CENNIK} PDF" title="{__TLUMACZ:POBIERZ_CENNIK} PDF" /></a>';
              //
         }
         //
    }
    if ( $html == 'tak' ) {
         //
         if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/html.svg') ) {
              //
              echo '<a class="CennikHtml" href="cennik.html/typ=html">{__TLUMACZ:POBIERZ_CENNIK} HTML</a>';
              //
         } else {
              //
              echo '<a href="cennik.html/typ=html"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/html.png" alt="{__TLUMACZ:POBIERZ_CENNIK} HTML" title="{__TLUMACZ:POBIERZ_CENNIK} HTML" /></a>';
              //
         }
         //
    }
    if ( $xls == 'tak' ) {
         //
         if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/xls.svg') ) {
              //
              echo '<a class="CennikXls" href="cennik.html/typ=xls">{__TLUMACZ:POBIERZ_CENNIK} XLS</a>';
              //
         } else {
              //
              echo '<a href="cennik.html/typ=xls"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/xls.png" alt="{__TLUMACZ:POBIERZ_CENNIK} XLS" title="{__TLUMACZ:POBIERZ_CENNIK} XLS" /></a>';
              //
         }
         //
    }
    //
    echo '</div>';

    unset($pdf, $html, $xls);
    
}    
//
?>