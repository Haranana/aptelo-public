<?php
chdir('../');            

if (isset($_POST['id'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $id = $filtr->process($_POST['id']);
        //
        $Produkt = new Produkt( (int)$id );
        
        if ($Produkt->CzyJestProdukt == false) {
            exit;
        }
                
        $Produkt->ProduktKupowanie();   
        $Produkt->ProduktDodatkowePolaTekstowe();
        //
        $IloscDoDodaniaDoKoszyka = (float)$_POST['ilosc'];
        //
        
        $rodzaj_ceny = 'baza';
        $cena = '';
        if ( isset($_POST['akcja']) && !empty($_POST['akcja']) && isset($_POST['cena']) ) {
            $rodzaj_ceny = $filtr->process($_POST['akcja']);
            $cena = $filtr->process($_POST['cena']);
        }
        
        // ciag cech
        if ( isset($_POST['cechy']) ) {
            $cechy = Funkcje::CechyProduktuPoId( $filtr->process($_POST['cechy']), true );        
          } else {
            $cechy = array();
        }
        
        $id = (int)$_POST['id'] . $filtr->process($_POST['cechy']);
        
        // jezeli jest komentarz
        $komentarz = $filtr->process($_POST['komentarz']);
        
        // jezeli sa dodatkowe pola tekstowe
        $polaTxt = nl2br($filtr->process(strip_tags((string)$_POST['txt'])));     

        // miejsce dodania produktu - listing czy karta produktu
        $miejsce = 'karta';
        if ( isset($_POST['miejsce']) ) {
             if ( (int)$_POST['miejsce'] == 1 ) {
                   $miejsce = 'lista';
             }
        }
        
        // czy dodac do koszyka
        $DodajDoKoszyka = false;
        
        // czy akcesoria dodatkowe
        $InfoAkcesoria = false;
        
        // jezeli produkt nie ma cech lub do koszyka sa przekazane wszystkie cechy produktu 
        if ( (int)$Produkt->cechyIlosc == 0 || ( count($cechy) == (int)$Produkt->cechyIlosc && count($cechy) > 0 ) ) {
             $DodajDoKoszyka = true;
        }
        
        // jezeli jest dodawany do koszyka z listingu a ma dodatkowe pola tekstowe
        if ( $miejsce == 'lista' && count($Produkt->dodatkowePolaTekstowe) > 0 ) {
             $DodajDoKoszyka = false;
        }
        
        // sprawdzi czy produkt nie jest jako tylko akcesoria dodatkowe i czy jest w koszyku produkt z ktorym mozna go kupic
        if ( $Produkt->info['status_akcesoria'] == 'tak' ) {
             //
             // ustawia ze ma sie wyswietlic info ze trzeba kupic z innym produktem
             $InfoAkcesoria = true;
             $DodajDoKoszyka = false;
             //
             // tablica do id produktow i kategorii ktore maja akcesoria dodatkowe o danym id produktu
             $TablicaIdProduktow = array();
             $TablicaIdKategorie = array();
             //
             $zapytanie = "select distinct pacc_type, pacc_products_id_master from products_accesories where pacc_products_id_slave = '" . $Produkt->info['id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);    
             //
             if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                  //
                  while ($info = $sql->fetch_assoc()) {
                     if ( $info['pacc_type'] == 'produkt' ) {
                          $TablicaIdProduktow[] = $info['pacc_products_id_master'];
                     }
                     if ( $info['pacc_type'] == 'kategoria' ) {
                          $TablicaIdKategorie[] = $info['pacc_products_id_master'];
                     }                  
                  }
                  //
                  unset($info);
                  //
             }
             //
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);  
             //
             if ( count($TablicaIdProduktow) > 0 ) {
                  //
                  // sprawdzi czy w koszyku jest produkt z ktorym mozna kupic ten produkt
                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                     //
                     $IdProduktuKoszyka = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] );
                     //
                     if ( in_array($IdProduktuKoszyka, $TablicaIdProduktow) ) {
                          $InfoAkcesoria = false;
                          $DodajDoKoszyka = true;
                          break;
                     }
                     //
                     unset($IdProduktuKoszyka);
                     //
                  }
                  //
             }
             //
             if ( count($TablicaIdKategorie) > 0 ) {
                  //
                  // sprawdzi czy w koszyku jest produkt z ktorym mozna kupic ten produkt
                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                     //
                     $IdKategoriiProduktu = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
                     //
                     foreach ( $IdKategoriiProduktu as $TmpIdKat ) {
                          //
                          if ( in_array($TmpIdKat, $TablicaIdKategorie) ) {
                               $InfoAkcesoria = false;
                               $DodajDoKoszyka = true;
                               break;
                          }
                          //
                     }
                     //
                     unset($IdKategoriiProduktu);
                     //
                  }
                  //
             }
             unset($TablicaIdKategorie, $TablicaIdProduktow);
             //   
        }
        
        if ( $DodajDoKoszyka == true ) {
          
            // jezeli jest cos dodania
            if ( $IloscDoDodaniaDoKoszyka > 0 ) {
                $GLOBALS['koszykKlienta']->DodajDoKoszyka( $id, $IloscDoDodaniaDoKoszyka, $komentarz, $polaTxt, $rodzaj_ceny, $cena ); 
            }          
            
            // integracja z edrone
            IntegracjeZewnetrzne::EdroneDoKoszykaDodanie( $Produkt );
            
            // integracja z SALESmanago
            IntegracjeZewnetrzne::SalesManagoDoKoszykaDodanie( $Produkt );

            // integracja z kod Google remarketing dynamiczny ORAZ modul Google Analytics
            IntegracjeZewnetrzne::GoogleAnalyticsRemarketingDoKoszykaDodanie( $Produkt, $id, $cechy, $_POST );
                         
            // integracja z pixel Facebook
            IntegracjeZewnetrzne::PixelFacebookDoKoszykaDodanie( $Produkt, $id );
            
            // integracja z pixel Facebook
            IntegracjeZewnetrzne::PinterestTagDoKoszykaDodanie( $Produkt, $id );

            // integracja z DomodiPixel
            IntegracjeZewnetrzne::DomodiPixelDoKoszykaDodanie( $Produkt, $_POST );
            
            // integracja z WpPixel
            IntegracjeZewnetrzne::WpPixelDoKoszykaDodanie( $Produkt, $_POST );
            
            // integracja z Klaviyo
            IntegracjeZewnetrzne::KlaviyoDoKoszykaDodanie( $Produkt, $id, $_POST );

            echo '<div id="PopUpDodaj" class="PopUpKoszykDodanyDoKoszyka" aria-live="assertive" aria-atomic="true">';
            //       
            echo $GLOBALS['tlumacz']['INFO_DO_KOSZYKA_DODANY_PRODUKT'] . ' <br />';
            
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            
            echo $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . ': <b>' . $filtr->process($_POST['ilosc']) . '</b> ' . $Produkt->info['jednostka_miary'];

            echo '</div>';
            
            if ( PRODUKT_OKNO_PRODUKTY_POPUP == 'tak' && isset($_POST['lista_produktow']) && $_POST['lista_produktow'] == 'tak' ) {
            
                // ukryty div zeby nie animowalo koszyka
                echo '<div id="BrakAnimacjiKoszyka" style="display:none"></div>';
            
                $NaglowekPopUp = $GLOBALS['tlumacz']['LISTING_PRODUKTY_POPUP_AKCESORIA'];
                
                if ( PRODUKT_OKNO_PRODUKTY_POPUP_TYP == 'tylko produkty podobne' ) {
                     //
                     $NaglowekPopUp = $GLOBALS['tlumacz']['LISTING_PRODUKTY_POPUP_PODOBNE'];
                     //
                }
                
                if ( PRODUKT_OKNO_PRODUKTY_POPUP_TYP == 'tylko produkty powiązane' ) {
                     //
                     $NaglowekPopUp = $GLOBALS['tlumacz']['LISTING_PRODUKTY_POPUP_POWIAZANE'];
                     //
                }                

                $WybraneProdukty = array();

                if ( strpos((string)PRODUKT_OKNO_PRODUKTY_POPUP_TYP, 'podobne') > -1 ) {
                     //
                     $zapytanie = Produkty::SqlProduktyPodobne( $Produkt->info['id'], 30 );
                     $sql = $GLOBALS['db']->open_query($zapytanie);    
                     //
                     if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                          //
                          while ($info = $sql->fetch_assoc()) {
                               //
                               if ( !in_array($info['products_id'], $WybraneProdukty) ) {
                                    $WybraneProdukty[] = $info['products_id'];
                               }
                               //
                          }
                          //
                     }
                     //
                     $GLOBALS['db']->close_query($sql); 
                     unset($zapytanie);                                            
                     //
                }
                
                if ( strpos((string)PRODUKT_OKNO_PRODUKTY_POPUP_TYP, 'akcesoria') > -1 ) {
                     //
                     $zapytanie = Produkty::SqlProduktyAkcesoriaDodatkowe( $Produkt->info['id'], 30 );
                     $sql = $GLOBALS['db']->open_query($zapytanie);    
                     //
                     if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                          //
                          while ($info = $sql->fetch_assoc()) {
                               //
                               if ( !in_array($info['products_id'], $WybraneProdukty) ) {
                                    $WybraneProdukty[] = $info['products_id'];
                               }
                               //
                          }
                          //
                     }
                     //
                     $GLOBALS['db']->close_query($sql); 
                     unset($zapytanie);                       
                     //
                }
                
                if ( strpos((string)PRODUKT_OKNO_PRODUKTY_POPUP_TYP, 'powiązane') > -1 ) {
                     //
                     $zapytanie = Produkty::SqlProduktyPowiazane( $Produkt->info['id'], 30 );
                     $sql = $GLOBALS['db']->open_query($zapytanie);    
                     //
                     if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                          //
                          while ($info = $sql->fetch_assoc()) {
                               //
                               if ( !in_array($info['products_id'], $WybraneProdukty) ) {
                                    $WybraneProdukty[] = $info['products_id'];
                               }
                               //
                          }
                          //
                     }
                     //
                     $GLOBALS['db']->close_query($sql); 
                     unset($zapytanie);                                            
                     //
                }          

                if ( count($WybraneProdukty) > 0 ) {
                  
                     $IloscZapytania = PRODUKT_OKNO_PRODUKTY_ILOSC;
                     
                     if ( PRODUKT_OKNO_PRODUKTY_SPOSOB_WYSWIETLANIA == 'statyczny' ) {
                          //
                          if ( $IloscZapytania > 3 ) {
                               $IloscZapytania = 3;
                          }
                          //
                     }
                  
                     $zapytanie = "SELECT DISTINCT products_id FROM products WHERE products_id IN (" . implode(',', (array)$WybraneProdukty) . ") ORDER BY RAND() LIMIT " . $IloscZapytania;
                     $sql = $GLOBALS['db']->open_query($zapytanie); 
                     
                     $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);

                     if ( file_exists('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_popup.php') ) {
                          //
                          require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_popup.php');
                          //
                        } else {
                          //
                          require('listingi/listing_produkty_popup.php');
                          //
                     }
                     
                     unset($IloscZapytania);
                    
                }
                
            }

            echo '<div id="PopUpPrzyciski" class="PopUpKoszykPrzyciski">';
            
                // przeladowanie strony i ewentualny powrot do zakladki - akcesoria dodatkowe
                echo '<script>';
                echo 'function przeladuj() { ';

                if (isset($_POST['wroc']) && $_POST['wroc'] != '') {
                    echo "ustawCookie('zakladka','" . $filtr->process($_POST['wroc']) . "',1);";
                }
                
                echo 'stronaReload();';
                echo '}';
                echo '</script>';
            
                echo '<span role="button" tabindex="0" onclick="przeladuj()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                echo '<a href="' . Seo::link_SEO('koszyk.php', '', 'inna') . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_KOSZYKA'] . '</a>';
                
            echo '</div>';
            //

            unset($IloscDoDodaniaDoKoszyka);
            
        } else {
        
            if ( $InfoAkcesoria == true ) {
            
                echo '<div id="PopUpInfo" class="TylkoGratis" aria-live="assertive" aria-atomic="true">';

                echo str_replace('{PRODUKT}', (string)$Produkt->info['nazwa'], (string)$GLOBALS['tlumacz']['PRODUKT_INFO_AKCESORIA']) . ' <br />'; 
                
                echo '</div>';
                
                echo '<div id="PopUpPrzyciski" class="PopUpKoszykPrzyciski PopUpKoszykPrzyciskiTylkoGratis">';
                    echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                echo '</div>';            
            
            } else {
        
                echo '<div id="PopUpDodaj" class="KonieczneCechy" aria-live="assertive" aria-atomic="true">';

                echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
                
                echo $GLOBALS['tlumacz']['PRODUKT_INFO_CECHY'] . ' <br />'; 
                
                echo '</div>';
                
                echo '<div id="PopUpPrzyciski" class="PopUpKoszykPrzyciski PopUpKoszykPrzyciskiKonieczneCechy">';
                    echo '<a href="' . $Produkt->info['adres_seo'] . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_SZCZEGOLOW_PRODUKTU'] . '</a>';
                echo '</div>';
                
            }

        }

        //
        unset($Produkt, $cechy, $DodajDoKoszyka, $miejsce);
        //
 
    }
    
}
?>