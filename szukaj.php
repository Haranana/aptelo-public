<?php
// plik
$WywolanyPlik = 'szukaj';

include('start.php');

$LinkDoPrzenoszenia = Seo::link_SEO('szukaj.php', '', 'inna');

// *****************************
// jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
    Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s'), false, '/'));
}    
// *****************************   

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('WYSZUKIWANIE_ZAAWANSOWANE') ), $GLOBALS['tlumacz'] );

include('listing_gora.php');

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta); 

// tablica z id produktow 
$IdProduktow = array();
  
// teksty wyszukiwania
$TekstyDoWyswietlenia = array();      

// wyszukiwana fraza
$srodek->dodaj('__SZUKANA_FRAZA','');

if (isset($_GET['szukaj']) && trim((string)$_GET['szukaj']) != '') {
  
    // usuwanie fbclid facebook
    if ( isset($_GET['fbclid']) ) {
         //
         unset($_GET['fbclid']);
         //
         $_GET['szukaj'] = str_replace('?fbclid', '', (string)$_GET['szukaj']);
         //
    }
    // usuwanie gclid google
    if ( isset($_GET['gclid']) ) {
         //
         unset($_GET['gclid']);
         //
         $_GET['szukaj'] = str_replace('?gclid', '', (string)$_GET['szukaj']);
         //
    }
   
    // ustawienie parametrow jezeli klikniety link w chmurze tagow
    if ( strpos((string)$_SERVER['QUERY_STRING'], 'szukaj=') !== false ) {
        $_GET['fraza'] = 'tak';
        $_GET['opis'] = 'tak';
        $_GET['nrkat'] = 'tak';
    }

    // ustawienia zakresu wyszukiwania

    $Fraza = false;
    $Opis = false;
    $KrotkiOpis = false;
    $NrKat = false;
    $KodProd = false;
    $KodEan = false;

    // stale ustawienia wyszukiwania
    if ( WYSZUKIWANIE_DOKLADNA_FRAZA == 'tak' ) {
         $Fraza = true;
    }
    if ( WYSZUKIWANIE_OPIS == 'tak' ) {
         $Opis = true;
    }
    if ( WYSZUKIWANIE_KROTKI_OPIS == 'tak' ) {
         $KrotkiOpis = true;
    }    
    if ( WYSZUKIWANIE_NR_KAT == 'tak' ) {
         $NrKat = true;
    }
    if ( WYSZUKIWANIE_KOD_PRODUCENTA == 'tak' ) {
         $KodProd = true;
    }
    if ( WYSZUKIWANIE_KOD_EAN == 'tak' ) {
         $KodEan = true;
    }
        
    // jezeli sa zmienne get
    if ( isset($_GET['fraza']) ) {
         if ( $_GET['fraza'] == 'tak' ) {
              $Fraza = true;
           } else { 
              $Fraza = false;
         }
    }
    if ( isset($_GET['opis']) ) {
         if ( $_GET['opis'] == 'tak' ) {
              $Opis = true;
           } else { 
              $Opis = false;
         }
    }
    if ( isset($_GET['sopis']) ) {
         if ( $_GET['sopis'] == 'tak' ) {
              $KrotkiOpis = true;
           } else { 
              $KrotkiOpis = false;
         }
    }    
    if ( isset($_GET['nrkat']) ) {
         if ( $_GET['nrkat'] == 'tak' ) {
              $NrKat = true;
           } else { 
              $NrKat = false;
         }
    }
    if ( isset($_GET['kodprod']) ) {
         if ( $_GET['kodprod'] == 'tak' ) {
              $KodProd = true;
           } else { 
              $KodProd = false;
         }
    }
    if ( isset($_GET['ean']) ) {
         if ( $_GET['ean'] == 'tak' ) {
              $KodEan = true;
           } else { 
              $KodEan = false;
         }         
    }

    // czyszczenie szukanej wartosci
    
    // $_GET['szukaj'] = mb_strtolower(strip_tags(rawurldecode($_GET['szukaj'])), 'UTF-8');
    $_GET['szukaj'] = strip_tags(rawurldecode((string)$_GET['szukaj']));
    
    // zamienia zmienne na poprawne znaki
    $_GET['szukaj'] = str_replace(array('[back]', '[proc]'), array('/', '%'), (string)$_GET['szukaj']);
    
    // zabezpieczenie przez hackiem
    $_GET['szukaj'] = str_replace(array('">', '<"'), array('', ''), (string)$_GET['szukaj']);    
    
    if ( trim((string)$_GET['szukaj']) != '' && strlen((string)$_GET['szukaj']) > 1 ) {
        
         // jezeli ma szukac wszystkich fraz
         if ( $Fraza == false ) {

              // podzial fraz na wyrazy    
              $SzukaneFrazy = explode(' ', (string)$_GET['szukaj']);
              // tablica szukanych fraz
              $SzukaneFrazyWynik = array();
 
              // sprawdzanie czy dlugosc frazy wieksza od 1 znaku
              foreach ( $SzukaneFrazy as $FrazaTmp ) {
                  //
                  if ( strlen((string)$FrazaTmp) > 1 ) {
                       $SzukaneFrazyWynik[] = $FrazaTmp;
                  }
                  //
                  unset($FrazaTmp);
                  //
              }

         } else {
             
              $SzukaneFrazyWynik = array($_GET['szukaj']);
              
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_FRAZY'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
             
         }

         // zamiana na pl znaki
         $SzukaneFrazy = array();
 
         if ( WYSZUKIWANIE_PL_ZNAKI == 'tak' ) {
              //
              foreach ( $SzukaneFrazyWynik as $FrazaTmp ) {
                  //
                  $SzukaneFrazy[] = Funkcje::ZamienPlZnaki(preg_quote($FrazaTmp, '/'));
                  //
              }
              //
               unset($FrazaTmp);
              //
         } else {
              //
              $SzukaneFrazyWynikTmp = array();
              //
              foreach ( $SzukaneFrazyWynik as $Tmp ) {
                   //
                   $SzukaneFrazyWynikTmp[] = preg_quote($Tmp, '/');
                   //
              }
              //
              $SzukaneFrazy = $SzukaneFrazyWynikTmp;
              //
              unset($SzukaneFrazyWynikTmp);
              //
         }
    
         // tablice zapytania
         $TablicaZapytania = array();
        
         // dodatkowe warunki
         $TablicaWarunki = array();

         // podstawowe pola do wyszukiwania
         $ZapytaniePola = array('p.products_id',
                                'pd.products_name',
                                'pd.products_search_tag');
          
         // dodatkowe warunki do wyszukiwania
         if ( $NrKat == true ) {
              //
              $ZapytaniePola[] = 'p.products_model';
              $ZapytaniePola[] = 'ps.products_stock_model';
              //
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_NR_KATALOGOWY'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //
         }
         if ( $KodProd == true ) {
              //
              $ZapytaniePola[] = 'p.products_man_code';
              //
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_KOD_PRODUCENTA'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //
         }    
         if ( $KodEan == true ) {
              //
              $ZapytaniePola[] = 'p.products_ean';
              $ZapytaniePola[] = 'ps.products_stock_ean';
              //
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_EAN'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //
         }
         if ( $Opis == true ) {
              //
              $ZapytaniePola[] = 'pd.products_description';
              //
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_OPISACH'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //
         }
         if ( $KrotkiOpis == true ) {
              //
              $ZapytaniePola[] = 'pd.products_short_description';
              //
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_OPISACH'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //
         }
         if ( $NrKat == true || $KodEan == true ) {
              //
              $TablicaZapytania[] = "LEFT JOIN products_stock ps ON ps.products_id = p.products_id";
              //
         }
         if ( (isset($_GET['ceno']) && (float)$_GET['ceno'] > 0) || (isset($_GET['cend']) && (float)$_GET['cend'] > 0) ) {
               //
               $ZapytaniePola[] = 'p.products_price_tax';
               $ZapytaniePola[] = 'cu.value';
               $ZapytaniePola[] = 'cu.currencies_marza';
               $ZapytaniePola[] = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) AS cena';
               //
               $TablicaZapytania[] = "LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id";
               //
               $DodWarunekCen = '';
               //
               if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
                    //
                    if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
                        //
                        if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                             $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';     
                          } else {
                             $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
                        }
                      } else {
                        $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
                    }
                    //
                 } else {
                    //
                    $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
                    //              
               }        
               //
               $Przelicznik = $_SESSION['domyslnaWaluta']['przelicznik'] * ( 1 + ( $_SESSION['domyslnaWaluta']['marza']/100 ) );
               //
               // cena od
               $CenaOd = '0';
               if ( isset($_GET['ceno']) && (float)$_GET['ceno'] > 0 && $DodWarunekCen != '' ) {
                    //
                    $TablicaWarunki[] = " AND " . $DodWarunekCen . " >= " . (float)$_GET['ceno']/$Przelicznik;
                    $CenaOd = (float)$_GET['ceno'];
                    //
               }
               
               // cena do
               $CenaDo = '';
               if ( isset($_GET['cend']) && (float)$_GET['cend'] && $DodWarunekCen != '' ) {
                    //
                    $TablicaWarunki[] = " AND " . $DodWarunekCen . " <= " . (float)$_GET['cend']/$Przelicznik;
                    $CenaDo = ' ' . $GLOBALS['tlumacz']['LISTING_ZAKRES_CEN_DO'] . ' ' . (float)$_GET['cend'] . ' ' . $_SESSION['domyslnaWaluta']['symbol'];
                    //
               }   
               //
               $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_ZAKRES_CEN'] . '</span> <b>' . $CenaOd . ' ' . $_SESSION['domyslnaWaluta']['symbol'] . $CenaDo . '</b></p>';
               //
               unset($CenaOd, $CenaDo);
               //
         }
        
         // tylko promocje
         if ( isset($_GET['promocje']) && $_GET['promocje'] == 'tak' ) {
              //
              $TablicaWarunki[] = " AND p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_PROMOCJE'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //        
         }
        
         // tylko nowosci
         if ( isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak' ) {
              //
              $TablicaWarunki[] = " AND p.new_status = '1'";
              $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_NOWOSCI'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
              //
         }    
        
         // producent
         if ( isset($_GET['producent']) ) {
              //
              $TablicaGet = Filtry::WyczyscFiltr($_GET['producent']);
              //
              if ( count($TablicaGet) > 0 ) {
                  //
                  $IdProducentow = array();
                  $NazwyProducentow = array();
                  //
                  foreach ( $TablicaGet as $GetProducent ) {
                      //  
                      if ( $GetProducent > 0 )  {
                           //
                           $TablicaProducenta = Producenci::NazwaProducenta($GetProducent);
                           $NazwaProducenta = $TablicaProducenta['nazwa'];
                           //
                           if ( !empty($NazwaProducenta) ) {
                               //
                               $IdProducentow[] = $GetProducent;
                               $NazwyProducentow[] = $NazwaProducenta;
                               //
                           }
                           //
                           unset($NazwaProducenta, $TablicaProducenta);
                           //
                      }
                      //
                  }
                  if ( count($IdProducentow) > 0 ) {
                      //
                      $TablicaWarunki[] = " AND p.manufacturers_id in (" . implode(',', (array)$IdProducentow) . ")";
                      $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_PRODUCENT'] . '</span> <b>' . implode(', ', (array)$NazwyProducentow) . '</b></p>';
                      //
                  }
                  //
              }
              //
              unset($TablicaGet);
              //
         }    
        
         // kategoria
         if ( isset($_GET['kategoria']) ) {
              //
              $TablicaGet = Filtry::WyczyscFiltr($_GET['kategoria']);
              //
              if ( count($TablicaGet) > 0 ) {
                  //
                  $IdPodkategorii = array();
                  $NazwyKategorii = array();
                  //
                  foreach ( $TablicaGet as $GetKategoria ) {
                      //  
                      if ( $GetKategoria > 0 )  {
                           //
                           $NazwaKategorii = Kategorie::NazwaKategoriiId($GetKategoria);
                           //
                           if ( !empty($NazwaKategorii) ) {
                               //
                               $IdPodkategorii[] = $GetKategoria;
                               $NazwyKategorii[] = $NazwaKategorii;
                               //    
                               // musi znalezc podkategorie dla danej kategorii
                               if (isset($_GET['podkat']) && $_GET['podkat'] == 'tak') {
                                   //
                                   foreach(Kategorie::DrzewoKategorii((int)$GetKategoria) as $IdKategorii => $Tablica) {
                                      //
                                      $SzybkiPodzial = explode(',', (string)Kategorie::TablicaPodkategorie($Tablica));
                                      //
                                      foreach ( $SzybkiPodzial as $IdK ) {
                                          if ( (int)$IdK > 0 ) {
                                                $IdPodkategorii[] = $IdK;
                                          }
                                      }
                                      //
                                      unset($SzybkiPodzial);
                                      //
                                   }               
                               }
                               //
                           }
                           //
                           unset($NazwaKategorii);
                           //
                      }
                      //
                  }
                  if ( count($IdPodkategorii) > 0 ) {
                       //
                       $TablicaWarunki[] = " AND c.categories_id in (" . implode(',', (array)$IdPodkategorii) . ")";
                       $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_KATEGORIA'] . '</span> <b>' . implode(', ', (array)$NazwyKategorii) . '</b></p>';
                       //
                       if (isset($_GET['podkat']) && $_GET['podkat'] == 'tak') {
                           //
                           $TekstyDoWyswietlenia[] = '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_PODKATEGORIACH'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
                           //
                       }
                       //
                  }
                  //
                  unset($IdPodkategorii, $NazwyKategorii);
                  //
             }
             //
             unset($TablicaGet);
             //
         }    

         // sprawdzenie czy produkty sa z aktywnych kategorii
         //
         $TablicaZapytania[] = "RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id 
                                RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'";
         //
        
         // jezeli jest dodatkowo szukanie po dodatkowych polach
        
         $DodatkowePolaId = array();    
        
         foreach ( $_GET as $klucz => $wartosc ) {
             //
             if ( strpos((string)$klucz, 'dodatkowe' ) !== false ) {
                  //
                  if ( (int)substr((string)$klucz, strrpos($klucz, '_') + 1) > 0 ) {
                       //
                       $DodatkowePolaId[] = (int)substr((string)$klucz, strrpos((string)$klucz, '_') + 1);
                       //
                  }
                  //
             }
             //
         } 
        
         if ( count($DodatkowePolaId) > 0 ) {
              //
              $TablicaZapytania[] = "LEFT JOIN products_to_products_extra_fields p2pef ON p.products_id = p2pef.products_id AND products_extra_fields_id IN (" . implode(',', (array)$DodatkowePolaId) . ")";
              $ZapytaniePola[] = 'p2pef.products_extra_fields_value';
              //
              $zapytanie_pola = "SELECT products_extra_fields_id, products_extra_fields_name FROM products_extra_fields WHERE products_extra_fields_status = '1' AND products_extra_fields_id IN (" . implode(',', (array)$DodatkowePolaId) . ") ORDER BY products_extra_fields_order";
              $sql_pola = $GLOBALS['db']->open_query($zapytanie_pola);
              //
              while ( $info_pola = $sql_pola->fetch_assoc() ) {
                      //
                      $TekstyDoWyswietlenia[] = '<p><span>' . $info_pola['products_extra_fields_name'] . ':</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
                      //
              }
              //
              $GLOBALS['db']->close_query($sql_pola);
              unset($info_pola, $zapytanie_pola);
              //
         }    

         // zapytanie o produkty
         $zapytanie = "SELECT DISTINCT " . implode(', ', (array)$ZapytaniePola) . "
                       FROM products p 
                       LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                       " . implode(' ', (array)$TablicaZapytania) . "
                       WHERE p.products_status = '1' and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . implode(' ', (array)$TablicaWarunki) . $GLOBALS['warunekProduktu'];

         unset($TablicaZapytania, $TablicaWarunki);      

         $sql = $GLOBALS['db']->open_query($zapytanie);
 
         // wyszukiwanie produktow
         $WynikFraz = array();

         while ( $info = $sql->fetch_assoc() ) {
            //
            $TablicaSzukaniaWiersza = array();
            $PrzeszukiwanyTekst = '';

            $TmpId = array_map(function($v){
                return preg_replace( '%</?[a-z][a-z0-9]*[^<>]*>%sim', '', (string)$v );
                //return strip_tags((string)$v);
            }, $info);

            unset($TmpId['products_id']);
            $PrzeszukiwanyTekst = implode(' ', (array)$TmpId);

            $TablicaSzukaniaWiersza[$info['products_id']] = $PrzeszukiwanyTekst;

            unset($PrzeszukiwanyTekst, $TmpId);

            $IloscWystapien = 0;
            foreach ( $SzukaneFrazy as $Fraza ) {
                $TablicaTMP = array();
                $TablicaTMP = preg_grep('/' . $Fraza . '/iu',$TablicaSzukaniaWiersza);                
                if ( count($TablicaTMP) > 0 ) {
                    $IloscWystapien++;
                }
                unset($TablicaTMP);
            }
            unset($Fraza);
            
            if ( $IloscWystapien == count($SzukaneFrazy) ) {
                 $WynikFraz[$info['products_id']] = $info['products_id'];
            }
            
            unset($IloscWystapien, $TablicaSzukaniaWiersza);

         }

         $GLOBALS['db']->close_query($sql);
         unset($zapytanie, $info);

         // dodaje fraze do szablonu
         $srodek->dodaj('__SZUKANA_FRAZA', $_GET['szukaj']);

         $IdProduktow = $WynikFraz;
         array_unique($IdProduktow);

    }

}

$ZnalezionoProdukty = true;

if ( count($IdProduktow) < 1 ) {
     //
     $IdProduktow = array();
     $ZnalezionoProdukty = false;
     //
     $sql = $GLOBALS['db']->open_query("SELECT products_id FROM products WHERE products_id = 0");
     //
}

// aktualizowane tablic wyszukiwan

if (isset($_GET['szukaj']) && trim((string)$_GET['szukaj']) != '') {
  
    $_GET['szukaj'] = $filtr->process($_GET['szukaj']);

    if ( !isset($_SESSION['szukaneFrazy']) ) {
         //
         $_SESSION['szukaneFrazy'] = array();
         //
    }

    if ( $_GET['szukaj'] != '' && !in_array((string)$_GET['szukaj'], (array)$_SESSION['szukaneFrazy']) ) {
      
         $TablicaDoAktualizacji = 'customers_searches';
      
         if ( $ZnalezionoProdukty == false ) {
              //
              $TablicaDoAktualizacji = 'customers_searches_zero';
              //
         }

         $WyszukiwanaWczesniejFraza = array();

         // aktualizuje raport wyszukiwane frazy
         $zapytanie_raport = "SELECT search_id, search_key, freq FROM " . $TablicaDoAktualizacji . " where language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
         $sql_raport = $GLOBALS['db']->open_query($zapytanie_raport);

         while ( $info_raport = $sql_raport->fetch_assoc() ) {
                 //
                 if ( $info_raport['search_key'] == $_GET['szukaj'] ) {
                      //
                      $WyszukiwanaWczesniejFraza = array('id' => $info_raport['search_id'],
                                                         'wyszukan' => $info_raport['freq']);
                      //
                 }
                 //
         }

         $GLOBALS['db']->close_query($sql_raport);
         unset($zapytanie_raport, $info_raport);

         if ( count($WyszukiwanaWczesniejFraza) > 0 ) {
              //
              $pola = array(array('freq', (int)$WyszukiwanaWczesniejFraza['wyszukan'] + 1));    
              $GLOBALS['db']->update_query($TablicaDoAktualizacji, $pola, "search_id = '" . $WyszukiwanaWczesniejFraza['id'] . "'");	
              unset($pola);  
              //
         } else {
              //
              $pola = array(array('search_key', $_GET['szukaj']),
                            array('freq', '1'),
                            array('language_id', (int)$_SESSION['domyslnyJezyk']['id']));
                            
              $db->insert_query($TablicaDoAktualizacji, $pola);
              unset($pola);                                    
              //
         }
         
         $_SESSION['szukaneFrazy'][] = $_GET['szukaj'];

         unset($WyszukiwanaWczesniejFraza, $TablicaDoAktualizacji);
         
    }

}

//
// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_WYNIKI_SZUKANIA']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$srodek->dodaj('__ILOSC_WYNIKOW_WYSZUKIWANIA',count($IdProduktow));
$srodek->dodaj('__INNE_WARUNKI_SZUKANIA', implode('', (array)$TekstyDoWyswietlenia));

// usuwanie zbednych get - jezeli jest na nie to nie sa potrzebne w linku do dalszych stron w wynikach wyszukiwania
$NiepotrzebneWartosci = array('fraza', 'opis', 'nrkat');
//
foreach ( $_GET as $klucz => $wartosc ) {
      //
      if ( in_array((string)$klucz, $NiepotrzebneWartosci ) ) {
          //
          if ( $wartosc == 'nie' ) {
               unset($_GET[$klucz]);
          }
          //
      }
      //
}
//
unset($NiepotrzebneWartosci);

if ( $ZnalezionoProdukty == true ) {
     //
     // integracja z CRITEO
     $tpl->dodaj('__CRITEO', IntegracjeZewnetrzne::CriteoSzukaj( $IdProduktow ));
     //
}

// jezeli tablica jest pusta to wstawia 0 dla zapytania sql
if ( count($IdProduktow) == 0 ) {
     $IdProduktow = array(0);
}

$zapytanie = Produkty::SqlSzukajProdukty( " AND p.products_id IN (" . implode(',', (array)$IdProduktow) . ")", $Sortowanie ); 

// ilosc produktow do stronicowania
if ( $ZnalezionoProdukty == true ) {
     //
     $IloscProduktowSzukaj = count($IdProduktow);
     //
} else {
     //
     $IloscProduktowSzukaj = 0;
     //
}

include('listing_dol.php');

include('koniec.php');

?>