<?php

if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {

  if ( Klient::IloscZamowien($_SESSION['customer_id']) > 0) {

      //
      echo '<div class="BoxReklamacja">';
     
      echo '<ul>';
      echo '<li><a href="' . ((WLACZENIE_SSL == 'tak') ? ADRES_URL_SKLEPU_SSL . '/' : '') . Seo::link_SEO( 'reklamacje_napisz.php', '', 'inna' ) . '">{__TLUMACZ:NAPISZ_REKLAMACJE}</a></li>';
      echo '<li><a href="' . ((WLACZENIE_SSL == 'tak') ? ADRES_URL_SKLEPU_SSL . '/' : '') . Seo::link_SEO( 'reklamacje_przegladaj.php', '', 'inna' ) . '">{__TLUMACZ:PRZEGLADAJ_REKLAMACJE}</a></li>';
      echo '</ul>';

      echo '</div>';
      //
  }

}
?>