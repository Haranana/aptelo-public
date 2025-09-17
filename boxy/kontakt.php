<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_KONTAKT_DANE_FIRMY;Czy wyświetlać dane firmy, dane definiowane w menu Dane firmy;tak;tak,nie}}
// {{BOX_KONTAKT_DANE_FIRMY_NIP;Czy wyświetlać NIP firmy, dane definiowane w menu Dane firmy;tak;tak,nie}}
// {{BOX_KONTAKT_EMAIL;Czy wyświetlać adres EMAIL, dane definiowane w menu Ustawienia email;tak;tak,nie}}
// {{BOX_KONTAKT_DANE_STRUKTURALNE;Czy generować dane strukturalne na podstawie danych firmy, dane definiowane w menu Dane teleadresowe;tak;tak,nie}}
// {{BOX_KONTAKT_PORTALE;Czy wyświetlać ikony portali społecznościowych;tak;tak,nie}}
//

$PokazNazweSklepu = 'tak';
$PokazDaneFirmy = 'tak';
$PokazNip = 'tak';
$PokazMail = 'tak';
$PokazDaneStrukturalne = 'tak';
$PokazDanePortale = 'tak';

if ( defined('BOX_KONTAKT_NAZWA_SKLEPU') ) {
   $PokazNazweSklepu = BOX_KONTAKT_NAZWA_SKLEPU;
}
if ( defined('BOX_KONTAKT_DANE_FIRMY') ) {
   $PokazDaneFirmy = BOX_KONTAKT_DANE_FIRMY;
}
if ( defined('BOX_KONTAKT_DANE_FIRMY_NIP') ) {
   $PokazNip = BOX_KONTAKT_DANE_FIRMY_NIP;
}
if ( defined('BOX_KONTAKT_EMAIL') ) {
   $PokazMail = BOX_KONTAKT_EMAIL;
}
if ( defined('BOX_KONTAKT_DANE_STRUKTURALNE') ) {
   $PokazDaneStrukturalne = BOX_KONTAKT_DANE_STRUKTURALNE;
}
if ( defined('BOX_KONTAKT_PORTALE') ) {
   $PokazDanePortale = BOX_KONTAKT_PORTALE;
}

// dane strukturalne    
if ( $PokazDaneStrukturalne == 'tak' ) { 

    echo '<meta itemprop="name" content="' . DANE_NAZWA_FIRMY_SKROCONA . '" />';
    
    echo '<div style="display:none" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
            <meta itemprop="name" content="' . str_replace('"', '', strip_tags((string)DANE_NAZWA_FIRMY_PELNA)) . '" />
            <meta itemprop="streetAddress" content="' . DANE_ADRES_LINIA_1 . ' ' . DANE_ADRES_LINIA_2 . '" />
            <meta itemprop="postalCode" content="' . DANE_KOD_POCZTOWY . '" />
            <meta itemprop="addressLocality" content="' . DANE_MIASTO . '" />
          </div>';
      
}

echo '<ul class="BoxKontakt" '. ( $PokazDaneStrukturalne == 'tak' ? 'itemscope itemtype="http://schema.org/LocalBusiness"' : '' ) . '>';

    // logo
    if ( LOGO_FIRMA != '' ) {
         //
         if ( file_exists(KATALOG_ZDJEC . '/' . LOGO_FIRMA) ) {
              //
              list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . LOGO_FIRMA);
              //
              echo '<li style="text-align:center"><img' . (($PokazDaneStrukturalne == 'tak') ? ' itemprop="image"' : '') . ' src="' . KATALOG_ZDJEC . '/' . LOGO_FIRMA . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . ( DANE_NAZWA_FIRMY_PELNA != '' ? str_replace('"', '', strip_tags((string)DANE_NAZWA_FIRMY_PELNA)) : '' ) . '" style="max-width:100%" /></li>';
              //
         }
         //
    }  
    
    // dane sklepu
    if ( $PokazDaneFirmy == 'tak' ) {
         //
         $DaneFirmy = '';
         //
         if ( DANE_FIRMY_BOX_KONTAKT != '' ) { 
              //
              $DaneFirmy .= nl2br(DANE_FIRMY_BOX_KONTAKT)  . '<br />';
              //
         }
         //
         if ( DANE_NIP != '' && $PokazNip == 'tak' && $DaneFirmy != '' ) { 
              //
              $DaneFirmy .= '{__TLUMACZ:KONTAKT_NIP}: ' . DANE_NIP . '<br />';
              //
         }
         //
         if ( $DaneFirmy != '' ) {
              //
              echo '<li class="Iko Firma">' . $DaneFirmy . '</li>';;
              //
         }
         //
         unset($DaneFirmy);
         //
    }

    // email sklepu
    if ( INFO_EMAIL_SKLEPU != '' && $PokazMail == 'tak' ) {
         //
         echo '<li class="Iko Mail"><b>{__TLUMACZ:EMAIL}</b>';
         //
         if ( isset($this->Formularze[1]) ) {
            echo '<a href="' . Seo::link_SEO( $this->Formularze[1], 1, 'formularz' ) . '">';
         }
         //
         echo '<span' . (($PokazDaneStrukturalne == 'tak') ? ' itemprop="email"' : '') . '>' . INFO_EMAIL_SKLEPU . '</span>';
         //
         if ( isset($this->Formularze[1]) ) {
            echo '</a>';
         }
         //
         echo '</li>';
         //
    } 

    // telefon
    if ( DANE_TELEFON_1 != '' || DANE_TELEFON_2 != '' || DANE_TELEFON_3 != '' ) {
         //
         echo '<li class="Iko Tel"><b>{__TLUMACZ:TELEFON}</b>';
         //
         if ( DANE_TELEFON_1 != '' ) { echo '<a rel="nofollow" href="tel:' . preg_replace("/[^+0-9]/", "", (string)DANE_TELEFON_1) . '"><span' . (($PokazDaneStrukturalne == 'tak') ? ' itemprop="telephone"' : '') . '>' . DANE_TELEFON_1 . '</span></a><br />'; }
         if ( DANE_TELEFON_2 != '' ) { echo '<a rel="nofollow" href="tel:' . preg_replace("/[^+0-9]/", "", (string)DANE_TELEFON_2) . '">' . DANE_TELEFON_2 . '</a><br />'; }
         if ( DANE_TELEFON_3 != '' ) { echo '<a rel="nofollow" href="tel:' . preg_replace("/[^+0-9]/", "", (string)DANE_TELEFON_3) . '">' . DANE_TELEFON_3 . '</a><br />'; }
         //
         echo '</li>';
         //
    }

    // fax
    if ( DANE_FAX_1 != '' ) {
         echo '<li class="Iko Fax"><b>{__TLUMACZ:KONTAKT_FAX}</b>' . DANE_FAX_1 . '</li>';
    }    

    // nr gg
    if ( DANE_GG_1 != '' || DANE_GG_2 != '' || DANE_GG_3 != '' ) {
         echo '<li class="Iko Gg"><b>Gadu Gadu</b>';
         //
         if ( DANE_GG_1 != '' ) { echo '<a rel="nofollow" href="gg:' . DANE_GG_1 . '">' . DANE_GG_1 . '</a><br />'; }
         if ( DANE_GG_2 != '' ) { echo '<a rel="nofollow" href="gg:' . DANE_GG_2 . '">' . DANE_GG_2 . '</a><br />'; }
         if ( DANE_GG_3 != '' ) { echo '<a rel="nofollow" href="gg:' . DANE_GG_3 . '">' . DANE_GG_3 . '</a><br />'; }
         //
         echo '</li>';
    }    

    // godziny dzialania
    if ( GODZINY_DZIALANIA != '' ) {
         echo '<li class="Iko Godziny"><b>{__TLUMACZ:GODZINY_OTWARCIA}</b>' . GODZINY_DZIALANIA . '</li>';
    }    
    
    // kod QR
    if ( KOD_QR != '' ) {
         //
         if ( file_exists(KATALOG_ZDJEC . '/' . KOD_QR) ) {
              //
              echo '<li style="text-align:center"><img src="' . KATALOG_ZDJEC . '/' . KOD_QR . '" alt="Kod QR - ' . ( DANE_NAZWA_FIRMY_PELNA != '' ? str_replace('"', '', strip_tags((string)DANE_NAZWA_FIRMY_PELNA)) : '' ) . '" style="max-width:100%" /></li>';
              //
         }
         //
    }  
    
    // portale spolecznosciowe
    if ( $PokazDanePortale == 'tak' && (DANE_PROFIL_FACEBOOK != '' || DANE_PROFIL_YOUTUBE != '' || DANE_PROFIL_INSTAGRAM != '' || DANE_PROFIL_TWITTER != '' || DANE_PROFIL_PINTEREST != '' || DANE_PROFIL_LINKEDIN != '' || DANE_PROFIL_TIKTOK != '') ) {
         //
         echo '<ul class="PortaleSpolecznoscioweKontakt">';
         //
         if ( DANE_PROFIL_FACEBOOK != '' ) {
              //
              echo '<li class="PortaleFacebook" title="Facebook"><a target="_blank" href="' . DANE_PROFIL_FACEBOOK . '">Facebook</a></li>';
              //
         }
         if ( DANE_PROFIL_YOUTUBE != '' ) {
              //
              echo '<li class="PortaleYoutube" title="Youtube"><a target="_blank" href="' . DANE_PROFIL_YOUTUBE . '">Youtube</a></li>';
              //
         }    
         if ( DANE_PROFIL_INSTAGRAM != '' ) {
              //
              echo '<li class="PortaleInstagram" title="Instagram"><a target="_blank" href="' . DANE_PROFIL_INSTAGRAM . '">Instagram</a></li>';
              //
         }  
         if ( DANE_PROFIL_LINKEDIN != '' ) {
              //
              echo '<li class="PortaleLinkedIn" title="LinkedIn"><a target="_blank" href="' . DANE_PROFIL_LINKEDIN . '">LinkedIn</a></li>';
              //
         }   
         if ( DANE_PROFIL_TWITTER != '' ) {
              //
              echo '<li class="PortaleTwitter" title="Twitter"><a target="_blank" href="' . DANE_PROFIL_TWITTER . '">Twitter</a></li>';
              //
         } 
         if ( DANE_PROFIL_PINTEREST != '' ) {
              //
              echo '<li class="PortalePinterest" title="Pinterest"><a target="_blank" href="' . DANE_PROFIL_PINTEREST . '">Pinterest</a></li>';
              //
         }  
         if ( DANE_PROFIL_TIKTOK != '' ) {
              //
              echo '<li class="PortaleTiktok" title="TikTok"><a target="_blank" href="' . DANE_PROFIL_TIKTOK . '">TikTok</a></li>';
              //
         }           
         //
         echo '</ul>';
         //
    }

//
echo '</ul>';
//
unset($PokazNazweSklepu, $PokazDaneFirmy, $PokazNip, $PokazMail, $PokazDaneStrukturalne, $PokazDanePortale);
?>