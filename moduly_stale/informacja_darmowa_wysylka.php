<?php
// modul wyswietlany jezeli jest cos dodane do koszyka
if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {

     $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

     $zapytanieTmp = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'informacja_darmowa_wysylka.php'";
     $sqlTmp = $GLOBALS['db']->open_query($zapytanieTmp);
     //
     while ( $infm = $sqlTmp->fetch_assoc() ) {
         //
         if ( !defined($infm['modul_settings_code']) ) {
              define( $infm['modul_settings_code'], $infm['modul_settings_value'] );
         }
         //
     } 
     //
     $GLOBALS['db']->close_query($sqlTmp);
     unset($infm, $zapytanieTmp);  

     // czy w koszyku sa produkty wykluczone z darmowej dostawy
     $SaWykluczone = false;
     
     // prog darmowej wysylki
     $ProgWysylki = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)DARMOWA_WYSYLKA_KWOTA, '', true);
     
     foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        if ( $TablicaZawartosci['wykluczona_darmowa_wysylka'] == 'tak' ) {
             //
             $SaWykluczone = true;
             //
        }
        //
     }

     if ( $ProgWysylki > 0 ) {

          $ZawartoscKoszykaTmp = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

          if ( $SaWykluczone == false ) {
            
               // sprawdzi czy dostepne wysylki maja darmowa opcje wysylki
               $CzyJestDarmowa = false;
               
               $WysylkiTablica = new Wysylki($_SESSION['krajDostawy']['kod']);
               $WysylkiKoszty = $WysylkiTablica->wysylki;
               
               foreach ( $WysylkiKoszty as $Tmp ) {
                    //
                    if ( isset($Tmp['wysylka_free']) && (float)$Tmp['wysylka_free'] > 0 ) {
                         //
                         $CzyJestDarmowa = true;
                         //
                    }          
                    //
               }

               if ( $CzyJestDarmowa == true ) {
              
                   if ( $ProgWysylki - $ZawartoscKoszykaTmp['brutto'] > 0 ) {
                       //
                       $CiagTxt = '<span>' . $GLOBALS['tlumacz']['DARMOWA_WYSYLKA_TEKST_BRAKUJE'] . '</span>';
                       //
                   } else {
                       //
                       $CiagTxt = '<span>' . $GLOBALS['tlumacz']['DARMOWA_WYSYLKA_TEKST_WYSYLKA_GRATIS'] . '</span>';
                       //
                   }
                   //

                   if ( trim((string)$GLOBALS['tlumacz']['DARMOWA_WYSYLKA_TEKST_DODATKOWY']) != '' ) {
                        //
                        $CiagTxt .= '<small>' . $GLOBALS['tlumacz']['DARMOWA_WYSYLKA_TEKST_DODATKOWY'] . '</small>';
                        //
                   }
     
                   $CiagTxt = str_replace('{WARTOSC_ILE_BRAKUJE}', $GLOBALS['waluty']->WyswietlFormatCeny(($ProgWysylki - $ZawartoscKoszykaTmp['brutto']), $_SESSION['domyslnaWaluta']['id'], true, false), (string)$CiagTxt);
                   $CiagTxt = str_replace('{WARTOSC_PROGU_DARMOWEJ_WYSYLKI}', $GLOBALS['waluty']->WyswietlFormatCeny($ProgWysylki, $_SESSION['domyslnaWaluta']['id'], true, false), (string)$CiagTxt);
                   
                   $CiagTxt = trim((string)preg_replace('/\s\s+/', ' ', (string)nl2br( str_replace("'", '', (string)$CiagTxt))));
                   
                   ?>
                   <script>$(document).ready(function(){$('body').prepend('<div id="InfoTopDarmowaDostawa"><?php echo $CiagTxt; ?></div>')})</script>          
                   <?php
                   
               }
               
               unset($CzyJestDarmowa, $WysylkiTablica, $WysylkiKoszty);

          }
          
          unset($ZawartoscKoszykaTmp);          

     }
     
     unset($SaWykluczone, $ProgWysylki, $CiagTxt);
        
}
?>