<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (Sesje::TokenSpr()) {
  
    if ( INTEGRACJA_EASYPROTECT_WLACZONY == 'tak' && INTEGRACJA_EASYPROTECT_API != '' ) {
  
        if (isset($_POST['id'])) {
          
            if ( isset($_POST['akcja']) ) {
              
                 if ( $_POST['akcja'] == 'dodaj' && isset($_POST['dane']) ) {
              
                      $Podzial = explode('|', (string)$_POST['dane']);
                     
                      if ( count($Podzial) == 2 ) {
                           //
                           $IleLat = (float)$Podzial[0];
                           $Cena = (float)$Podzial[1];
                           //
                           $GLOBALS['koszykKlienta']->DodajDoKoszyka( $filtr->process($_POST['id']), 1, $IleLat, '', 'wariant-ubezpieczenie', $Cena ); 
                           //
                           unset($Cena, $IleLat);
                           //
                           echo '<div id="PopUpDodaj">';
                           //       
                           echo $GLOBALS['tlumacz']['EASYPROTECT_DODANO'];
                           
                           echo '</div>';    
     
                           echo '<div id="PopUpPrzyciski">';
                           
                               echo '<a href="' . Seo::link_SEO('koszyk.php', '', 'inna') . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'] . '</a>';
                               
                           echo '</div>';   
                           
                      }
                      
                 }
                 
                 if ( $_POST['akcja'] == 'usun' ) {
                   
                      foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                   
                          if ( $TablicaZawartosci['id'] == $_POST['id'] && $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) {
                       
                               $_SESSION['koszyk'][$TablicaZawartosci['id']]['wariant']['ubezpieczenie'] = array();        
                       
                               echo '<div id="PopUpUsun">';
                               //       
                               echo $GLOBALS['tlumacz']['EASYPROTECT_USUNIETE'];
                             
                               echo '</div>';    
         
                               echo '<div id="PopUpPrzyciski">';
                             
                                   echo '<a href="' . Seo::link_SEO('koszyk.php', '', 'inna') . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'] . '</a>';
                                 
                               echo '</div>';   
                               
                          }
                          
                      }

                 }                 

            } else {

                foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                  
                    if ( $TablicaZawartosci['id'] == $_POST['id'] && $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) {
                      
                         $IdProduktuKoszyka = $TablicaZawartosci['id'];
                         
                         // sprawdzi czy nie jest tylko dla okreslonych kategorii
                         
                         $WyswietlOchrone = false;
                         
                         if ( INTEGRACJA_EASYPROTECT_PRODUKTY == 'kategorie' ) {
                              //
                              $TablicaKategoriiEasyProtect = explode(',', (string)INTEGRACJA_EASYPROTECT_KATEGORIE);
                              //
                              $JakieKategorieMaProdukt = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $IdProduktuKoszyka ) );
                              // 
                              foreach ( $JakieKategorieMaProdukt as $Tmp ) {
                                 //
                                 if ( in_array($Tmp, $TablicaKategoriiEasyProtect) ) {
                                      //
                                      $WyswietlOchrone = true;
                                      break;
                                      //
                                 }
                                 //
                              }
                              //
                              unset($TablicaKategoriiEasyProtect, $JakieKategorieMaProdukt);
                              //
                         } else {
                              //
                              $WyswietlOchrone = true;
                              //
                         }

                         // tylko dla produktow w kwocie powyzej 199 i ponizej 21999, tylko w walucie PLN i produktow z vatem 23%
                      
                         if ( $WyswietlOchrone == true && $TablicaZawartosci['cena_brutto_bez_wariantow'] > 199 && $TablicaZawartosci['cena_brutto_bez_wariantow'] < 22000 && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' && $TablicaZawartosci['vat_stawka'] == 23 && $TablicaZawartosci['zestaw'] == 'nie' ) {
                           
                              // sprawdzi czy jest dodawna gwarancja
                              
                              if ( isset($TablicaZawartosci['wariant']['ubezpieczenie']) && count($TablicaZawartosci['wariant']['ubezpieczenie']) > 0 ) {
                                
                                  $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
                                  
                                  // tylko dla produktow z jednostkami miary calkowitymi
                                  
                                  if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {

                                      $WartoscUbezpieczenia = $GLOBALS['waluty']->WyswietlFormatCeny($TablicaZawartosci['wariant']['ubezpieczenie']['cena_brutto'], $_SESSION['domyslnaWaluta']['id'], true, false);
                                      $WartoscProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($TablicaZawartosci['cena_brutto_bez_wariantow'], $_SESSION['domyslnaWaluta']['id'], true, false);

                                      echo '<td>';
                                      
                                                if ( file_exists(KATALOG_ZDJEC . '/platnosci/easyprotect_logo.png') ) {
                                                     echo '<img src="' . KATALOG_ZDJEC . '/platnosci/easyprotect_logo.png" alt="EasyProtect" />';
                                                }                              
                                                
                                      echo '</td>'; 
                                                                    
                                      echo '<td colspan="6">
                                      
                                                <div class="OchronaEasyProtectTbl">
                                                
                                                    <div class="OchronaEasyProtectOpis OchronaProduktOpis">
                                                    
                                                        <b>' . $GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . ': ' . $Produkt->info['nazwa'] . '</b>
                                                                                                                
                                                        <div class="UsunOchrone">
                                                           <span class="przycisk" data-id="' . $IdProduktuKoszyka . '" onclick="UsunEasyProtect(\'' . $IdProduktuKoszyka . '\')">' . $GLOBALS['tlumacz']['PRZYCISK_USUN'] . '</span>
                                                        </div>                                        
                                                    
                                                    </div>    

                                                    <div class="OchronaEasyProtectWybor OchronaProduktWybor">
                                                    
                                                        ' . $GLOBALS['tlumacz']['CENA_ZAWIERA'] . ' ' . $GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . ' <br />
                                                        
                                                        <ul>
                                                            <li>' . $GLOBALS['tlumacz']['CENA_PRODUKTU'] . ': <b>' . $WartoscProduktu . '</b></li>
                                                            <li>' . $GLOBALS['tlumacz']['KOSZT_OCHRONY'] . ': <b>' . $WartoscUbezpieczenia . '</b></li>
                                                            <li>' . $GLOBALS['tlumacz']['OKRES_OCHRONY'] . ': <b>' . $TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] . ' ' . (($TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 1) ? $GLOBALS['tlumacz']['ROK'] : $GLOBALS['tlumacz']['LATA']) . '</b></li>
                                                        </ul>

                                                    </div>
                                                    
                                                </div>
                                      
                                            </td>';
                                            
                                      unset($Produkt, $WartoscUbezpieczenia, $WartoscProduktu);
                                      
                                  }
                                
                              } else {
                                
                                  $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
                                  
                                  // tylko dla produktow z jednostkami miary calkowitymi
                                  
                                  if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {                                

                                      $TablicaKosztow = IntegracjeZewnetrzne::EasyProtectKoszyk( $TablicaZawartosci['cena_brutto_bez_wariantow'] );
                              
                                      if ( count($TablicaKosztow) > 0 ) {

                                          echo '<td colspan="7" class="OchronaEasyProtect">
                                                  
                                                    <div class="OchronaEasyProtectTbl">
                                                    
                                                        <div class="OchronaEasyProtectOpis">';
                                                                                                                    
                                                            if ( file_exists(KATALOG_ZDJEC . '/platnosci/easyprotect.png') ) {
                                                                 echo '<div class="OchronaEasyProtectImg"><img src="' . KATALOG_ZDJEC . '/platnosci/easyprotect.png" alt="EasyProtect" /></div>';
                                                            }
                                                            
                                                            echo '<div class="OchronaEasyProtectTxt">
                                                            
                                                                <b>' . $GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . '</b>

                                                                <p>' . $GLOBALS['tlumacz']['EASYPROTECT_OPIS'] . '</p>
                                                                
                                                                <a href="https://easyprotect.pl/informacja-o-produkcie/" target="_blank">' . $GLOBALS['tlumacz']['EASYPROTECT_LINK'] . '</a>
                                                            
                                                            </div>
                                                            
                                                        </div>
                                                        
                                                        <div class="OchronaEasyProtectWybor cmxform">
                                                        
                                                             <ul>';
                                                             
                                                             echo '<li><label for="wybor_ochrona_' . $IdProduktuKoszyka . '_0">' . $GLOBALS['tlumacz']['EASYPROTECT_GWARANCJA'] . ' <b>' . $GLOBALS['tlumacz']['EASYPROTECT_BRAK'] . '</b><span>(' . $GLOBALS['tlumacz']['EASYPROTECT_ZERO'] . ')</label><input type="radio" value="x" checked="checked" name="wybor_ochrona_' . $IdProduktuKoszyka . '" id="wybor_ochrona_' . $IdProduktuKoszyka . '_0" /></li>';
                                                             
                                                             $r = 0;
                                                             foreach ( $TablicaKosztow as $Miesiace => $Tmp ) {
                                                               
                                                                $IleLat = ((int)$Miesiace / 12);    
                                                                $WartoscUbezpieczenia = $GLOBALS['waluty']->WyswietlFormatCeny((float)$Tmp, $_SESSION['domyslnaWaluta']['id'], true, false);
                                                                
                                                                echo '<li>                                                                          
                                                                          <label for="wybor_ochrona_' . $IdProduktuKoszyka . '_' . $IleLat . '">' . $GLOBALS['tlumacz']['EASYPROTECT_RADIO'] . ' <b>' . $IleLat . ' ' . (($IleLat == 1) ? $GLOBALS['tlumacz']['ROK'] : $GLOBALS['tlumacz']['LATA']) . '</b> <span>(' . $WartoscUbezpieczenia . ')</span></label>
                                                                          <input onchange="AktywujEasyProtect(\'' . $IdProduktuKoszyka . '\')" type="radio" value="' . $IleLat . '|' . (float)$Tmp . '" name="wybor_ochrona_' . $IdProduktuKoszyka . '" id="wybor_ochrona_' . $IdProduktuKoszyka . '_' . $IleLat . '" />
                                                                      </li>';
                                                                
                                                                unset($IleLat, $WartoscUbezpieczenia);
                                                                
                                                                $r++;
                                                               
                                                             }

                                                             echo '</ul>

                                                        </div>
                                                        
                                                    </div>
                                                
                                                </td>';

                                      }
                                      
                                      unset($TablicaKosztow);
                                      
                                  }
                                  
                                  unset($Produkt);
                                  
                              }
                              
                         }
                        
                         unset($IdProduktuKoszyka);
                         
                    }
                    
                }
                  
            }

        }
        
    }
          
} else {
  
    Funkcje::PrzekierowanieURL('brak-strony.html');

}
?>