<?php
 
// przy karcie produktu moga byc tylko 2 kolumny - lewa i srodek lub prawa i srodek
$GLOBALS['kolumny'] = 'wszystkie_lewa';

// plik
$WywolanyPlik = 'produkt';

include('start.php');

$IdProduktuBezCech = Funkcje::SamoIdProduktuBezCech($_GET['idprod']);

$Produkt = new Produkt( $IdProduktuBezCech, SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, 'DoKoszykaKartaProduktu' );                

if ($Produkt->CzyJestProdukt == true) {

    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr($Produkt->info['adres_seo']);

    // elementy kupowania
    $Produkt->ProduktKupowanie();      
    $Produkt->ProduktDostepnosc();
    $Produkt->ProduktCzasWysylki();
    $Produkt->ProduktProducent();
    $Produkt->ProduktDodatkowePola();
    $Produkt->ProduktRecenzje();
    $Produkt->ProduktLinki();
    $Produkt->ProduktDodatkoweZakladki();
    $Produkt->ProduktPliki();
    $Produkt->ProduktYoutube();
    $Produkt->ProduktFilmyFLV();
    $Produkt->ProduktMp3();
    $Produkt->ProduktFaq();
    $Produkt->ProduktAllegro();
    $Produkt->ProduktStanProduktuMicroData();
    $Produkt->ProduktLinkiPowiazane();
    $Produkt->ProduktInneWarianty();

    // kupon rabatowy na karcie produktu - START
    //
    $Produkt->kupon = array();
    $Produkt->kupon_dostawa = array();
    $CssKuponu = '';
    $KodKuponu = '';
    $RodzajKuponu = '';
    $WartoscKuponu = '';
    //
    if ( KARTA_PRODUKTU_KUPON_RABATOWY == 'tak' ) {
         //
         $KuponProduktu = array();
         //
         if ( $Produkt->fotoGlowne['same_ikony']['promocja'] == '0' && $Produkt->fotoGlowne['same_ikony']['wyprzedaz'] == '0' && $Produkt->info['produkt_dnia'] == 'nie' && $Produkt->info['tylko_za_punkty'] == 'nie' ) {
              //
              $ListaKuponow = KuponyProduktu::ListaKuponow();
              $KuponProduktu = KuponyProduktu::ProduktKupon($Produkt, $ListaKuponow);
              //
              if ( count($KuponProduktu) > 0 ) {
                   //
                   if ( $KuponProduktu['typ'] != 'shipping' ) {
                        //
                        $Produkt->info['cena_poprzednia_bez_formatowania'] = $Produkt->info['cena_brutto_bez_formatowania'];
                        $Produkt->info['cena_netto_bez_formatowania'] = $KuponProduktu['cena_netto_po_rabacie'];
                        $Produkt->info['cena_brutto_bez_formatowania'] = $KuponProduktu['cena_brutto_po_rabacie'];
                        //
                        $Produkt->kupon = $KuponProduktu;
                        //
                        $CssKuponu = 'class="CenaZKuponem"';
                        $KodKuponu = $Produkt->kupon['kupon_kod'];
                        $RodzajKuponu = $Produkt->kupon['kupon_typ'];
                        $WartoscKuponu = $Produkt->kupon['kupon_wartosc'];                
                        //
                   } else {
                        //
                        $Produkt->kupon_dostawa = $KuponProduktu;
                        $KodKuponu = $Produkt->kupon_dostawa['kupon_kod'];
                        //
                   }
                   //                   
              }
              //
              unset($ListaKuponow, $KuponProduktu);
              //
         }
         //
    }

    if ( KARTA_PRODUKTU_ZAKLADKA_ZAKUPY == 'tak' || KARTA_PRODUKTU_ZAKUPY_HISTORIA == 'tak' ) {
         $Produkt->ProduktZakupy();
    }
    
    if ( KARTA_PRODUKTU_STAN_PRODUKTU == 'tak' ) {
         $Produkt->ProduktStanProduktu();
    }
    if ( KARTA_PRODUKTU_GWARANCJA == 'tak' ) {
         $Produkt->ProduktGwarancja();
    }    
    //

    // aktywne podziel sie
    $PodzielSiePortale = explode(',', (string)INTEGRACJA_PODZIEL_SIE_PORTALE);
    $Produkt->podziel_sie = array();
    //
    foreach ( $PodzielSiePortale as $Tmp ) {
        //
        $Produkt->podziel_sie[$Tmp] = $Tmp;
        //
    }
    //
    unset($PodzielSiePortale, $Tmp);      
    
    // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
    $Przecinek = 2;
    // jezeli sa wartosci calkowite to dla pewnosci zrobi int
    if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
        $Przecinek = 0;
    }    

    // aktualizacja informacji o wyswietlaniach produktu
    $sql = $GLOBALS['db']->open_query("SELECT products_viewed FROM products_description WHERE products_id = '" . $Produkt->info['id'] . "' AND language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'");  
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        $ile = $sql->fetch_assoc();
        //
        $pola = array(array('products_viewed', $ile['products_viewed'] + 1));		
        $GLOBALS['db']->update_query('products_description' , $pola, " products_id = '".$IdProduktuBezCech."' AND language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'");	
        unset($pola); 
        //
    }
    //
    $GLOBALS['db']->close_query($sql);  
    //
    
    // aktualizacja w sesji informacji o produktach poprzednio ogladanych
    if ( isset($_SESSION['produktyPoprzednioOgladane'][$IdProduktuBezCech]) ) {
        unset($_SESSION['produktyPoprzednioOgladane'][$IdProduktuBezCech]);
    }
    $_SESSION['produktyPoprzednioOgladane'][$IdProduktuBezCech] = $IdProduktuBezCech;

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    //
    // meta tagi
    if ( $Meta['nazwa_pliku'] != null ) { 
         //     
         $tpl->dodaj('__META_TYTUL', MetaTagi::MetaTagiProduktPodmien('tytul', $Produkt, $Meta));
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', MetaTagi::MetaTagiProduktPodmien('slowa', $Produkt, $Meta));
         $tpl->dodaj('__META_OPIS', MetaTagi::MetaTagiProduktPodmien('opis', $Produkt, $Meta));
         //
         $TytulOpenGraph = MetaTagi::MetaTagiProduktPodmien('tytul', $Produkt, $Meta);
         $OpisOpenGraph = MetaTagi::MetaTagiProduktPodmien('opis', $Produkt, $Meta);
         //
      } else {
         //
         $tpl->dodaj('__META_TYTUL', ((empty($Produkt->metaTagi['tytul'])) ? $Meta['tytul'] : $Produkt->metaTagi['tytul']));
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Produkt->metaTagi['slowa'])) ? $Meta['slowa'] : $Produkt->metaTagi['slowa']));
         $tpl->dodaj('__META_OPIS', ((empty($Produkt->metaTagi['opis'])) ? $Meta['opis'] : $Produkt->metaTagi['opis']));
         //
         $TytulOpenGraph = ((empty($Produkt->metaTagi['tytul'])) ? $Meta['tytul'] : $Produkt->metaTagi['tytul']);
         $OpisOpenGraph = ((empty($Produkt->metaTagi['opis'])) ? $Meta['opis'] : $Produkt->metaTagi['opis']);
         //       
    }
    
    if ( !empty($Produkt->metaTagi['og_title']) ) {
         //
         $TytulOpenGraph = $Produkt->metaTagi['og_title'];
         //
    }
    if ( !empty($Produkt->metaTagi['og_description']) ) {
         //
         $OpisOpenGraph = $Produkt->metaTagi['og_description'];
         //
    }

    // microdata
    
    // dostepnosc produktu
    $MicroDataAvialability = $Produkt->MicroDataAvailability($Produkt->info['ilosc']);

    // tagi Open Graph
    $TagiOpenGraph = '<meta property="og:title" content="' . trim((string)$TytulOpenGraph) . '" />' . "\n";
    $TagiOpenGraph .= '<meta property="og:description" content="' . trim((string)$OpisOpenGraph) . '" />' . "\n";
    $TagiOpenGraph .= '<meta property="og:type" content="product" />' . "\n";
    $TagiOpenGraph .= '<meta property="og:url" content="' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '" />' . "\n";

    if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
        $TagiOpenGraph .= '<meta property="og:image" content="' . ADRES_URL_SKLEPU . '/' . Funkcje::pokazObrazekWatermark($Produkt->fotoGlowne['plik_zdjecia']) . '" />' . "\n"; 
    } else {
        $TagiOpenGraph .= '<meta property="og:image" content="' . ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] . '" />' . "\n"; 
    }    
    if ( CENY_DLA_WSZYSTKICH == 'tak' && $Produkt->info['cena_dla_niezalogowanych'] == 'tak' ) {
        $TagiOpenGraph .= '<meta property="product:price:amount" content="' . $Produkt->info['cena_brutto_bez_formatowania'] . '" />' . "\n"; 
        $TagiOpenGraph .= '<meta property="product:price:currency" content="' . $_SESSION['domyslnaWaluta']['kod'] . '" />' . "\n";    
    }
  
    if ( $MicroDataAvialability['opengraph'] != '' ) {
         $TagiOpenGraph .= '<meta property="product:availability" content="' . $MicroDataAvialability['opengraph'] . '" />' . "\n";    
    }
    
    if ( $Produkt->stan_produktu_opengraph != '' ) {
         $TagiOpenGraph .= '<meta property="product:condition" content="' . $Produkt->stan_produktu_opengraph . '" />' . "\n";  
    }
    
    $TagiOpenGraph .= '<meta property="product:retailer_item_id" content="' . $Produkt->info['id'] . '" />' . "\n"; 

    $tpl->dodaj('__TAGI_OPEN_GRAPH', $TagiOpenGraph);
    
    // link kanoniczny dla produktu
    if ( $Produkt->metaTagi['link_kanoniczny'] != '' ) {
         //
         $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $Produkt->metaTagi['link_kanoniczny'] . '" />');    
         //
    } else {
         //
         $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '" />');  
         //
    }

    unset($Meta, $TagiOpenGraph, $OpisOpenGraph, $TytulOpenGraph);
    
    $WyswietlanaKategoriaProduktu = 0;
    
    // Breadcrumb dla kategorii produktow
    if ( $_SESSION['sciezka'] != '' ) {
        //
        $RodzajSciezka = explode('#', (string)$_SESSION['sciezka']);
        //
        if ($RodzajSciezka[0] == 'kategoria') {
            //
            $tablica_kategorii = explode('_', (string)$RodzajSciezka[1]); 
            //
            $sciezkaPath = '';
            for ( $i = 0, $n = count($tablica_kategorii); $i < $n; $i++ ) {
                if ( isset($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat']) ) {
                    //
                    $sciezkaPath .= $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat'] . '_';
                    //
                    if ( $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Widocznosc'] == '1' ) {
                         $nawigacja->dodaj($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Nazwa'], Seo::link_SEO($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['NazwaSeo'], substr((string)$sciezkaPath, 0, -1) , 'kategoria'));
                    }
                    //
                }
            }
            //
            $WyswietlanaKategoriaProduktu = (int)$tablica_kategorii[ count($tablica_kategorii) - 1];
            //
            unset($tablica_kategorii, $sciezkaPath);
            //
        }
        if ($RodzajSciezka[0] == 'producent') {
            //
            $nawigacja->dodaj($Produkt->producent['nazwa'], Seo::link_SEO($Produkt->producent['nazwa'], $Produkt->producent['id'], 'producent'));
            //$_SESSION['sciezka'] = '';
            //
        }
        //
        unset($RodzajSciezka);
        //
    }
    

    $nawigacja->dodaj($Produkt->info['nazwa']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    // style css
    $tpl->dodaj('__CSS_PLIK_GLOWNY', '');
    $tpl->dodaj('__CSS_PLIK', ',produkt,zebra_datepicker');
    // dla wersji mobilnej
    $tpl->dodaj('__CSS_KALENDARZ', ',zebra_datepicker');

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW','WYSYLKI','KOSZYK') ), $GLOBALS['tlumacz'] );
    
    // wyswietlanie informacji o wysylkach produktu
    $NajtanszaWysylka = '';
    if ( KARTA_PRODUKTU_KOSZTY_WYSYLKI == 'tak' || count($Produkt->kupon_dostawa) > 0 ) {
    
        // parametry do ustalenia dostepnych wysylek
        $tablicaWysylek = array();
        //
        $wysylki = new Wysylki($_SESSION['krajDostawy']['kod'], $Produkt->info['id'], $Produkt->info['waga'], $Produkt->info['cena_brutto_bez_formatowania'], $Produkt->info['dostepne_wysylki'], $Produkt->info['gabaryt'], $Produkt->info['koszt_wysylki'], $Produkt->info['wykluczona_darmowa_wysylka'], $Produkt->info['jest_promocja']);
        $tablicaWysylek = $wysylki->wysylki;
        //
        if ( KARTA_PRODUKTU_KOSZTY_WYSYLKI == 'tak' ) {
          
            $NajtanszaWysylka = array( 'koszt' => 10000, 'nazwa' => '' );
            $NajtanszaWysylkaOdbiorOsobisty = array( 'koszt' => 10000, 'nazwa' => '' );
            //
            $DostepneWysylki = array();
            $TylkoOsobisty = true;        
            //
            $przelicznik = 1 / $_SESSION['domyslnaWaluta']['przelicznik'];
            $marza = 1 + ( $_SESSION['domyslnaWaluta']['marza']/100 );
            //
            foreach ( $tablicaWysylek as $Wysylka ) {
                //
                // jezeli produkt ma darmowa wysylke
                if ( $Produkt->info['darmowa_wysylka'] == 'tak' && $Produkt->info['wykluczona_darmowa_wysylka'] == 'nie' ) {
                     //
                     if ( !isset($Wysylka['wykluczona_darmowa']) || $Wysylka['wykluczona_darmowa'] == 'nie' ) {
                          $Wysylka['wartosc'] = 0;
                     }
                     //
                }
                if ( isset($Wysylka['wartosc']) && is_numeric($Wysylka['wartosc']) ) {
                    $Wysylka['wartosc'] = number_format( round((($Wysylka['wartosc'] / $przelicznik) * $marza), CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, '.', '');
                }
                //
                // sprawdza nizszy koszt oraz pomija odbior osobisty
                if ( $Wysylka['wartosc'] < $NajtanszaWysylka['koszt'] && $Wysylka['id'] != '9' ) {
                     //
                     $NajtanszaWysylka = array( 'koszt' => $Wysylka['wartosc'], 'nazwa' => $Wysylka['text'] );            
                     // 
                }
                //
                if ( isset($Wysylka['wartosc']) && is_numeric($Wysylka['wartosc']) ) {
                     $CenaWysylki = $GLOBALS['waluty']->FormatujCene($Wysylka['wartosc'], 0, 0, $_SESSION['domyslnaWaluta']['id'], true);
                }

                // dodaje info do tablicy
                if ( isset($Wysylka['text']) && $Wysylka['text'] != '0' && isset($CenaWysylki['brutto']) ) {
                     $DostepneWysylki[] = '<span>'.$Wysylka['text'] . ' - ' . $CenaWysylki['brutto'] . '</span>';
                }
                
                // sprawdzi czy jest jakas wysylka poza odbiorem osobistym
                if ( $Wysylka['id'] != '9' ) {
                     $TylkoOsobisty = false;
                }
                if ( $Wysylka['id'] == '9' ) {
                     //
                     $NajtanszaWysylkaOdbiorOsobisty = array( 'koszt' => $Wysylka['wartosc'], 'nazwa' => $Wysylka['text'] );            
                     // 
                }
                
                unset($CenaWysylki);
                //
            }
            //
            if ( $NajtanszaWysylka['nazwa'] == '' || count($DostepneWysylki) == 0 ) {
                 $NajtanszaWysylka = '';
            }
            // jezeli jest tylko odbior osobisty
            if ( $TylkoOsobisty == true ) {
                 $NajtanszaWysylka = $NajtanszaWysylkaOdbiorOsobisty; 
            }

            unset($przelicznik, $marza, $NajtanszaWysylkaOdbiorOsobisty, $TylkoOsobisty);
            
        }
                
        // wysylki jezeli jest kupon na darmowa dostawe
        if ( count($Produkt->kupon_dostawa) > 0 ) {
          
            $dostepnyKupon = false;
          
            if ( $Produkt->kupon_dostawa['wysylki'] != '' ) {
              
                 $podzielWysylki = explode(',', (string)$Produkt->kupon_dostawa['wysylki']);

                 foreach ( $tablicaWysylek as $Wysylka ) {
                    //
                    if ( in_array($Wysylka['id'], $podzielWysylki) ) {
                         //
                         $dostepnyKupon = true;
                         //
                    }
                    //
                 }
                
            } else {
              
                 $dostepnyKupon = true;
              
            }
            
            if ( $dostepnyKupon == false ) {
              
                 $Produkt->kupon_dostawa = array();
              
            }
          
        }
        
        unset($tablicaWysylek);
        
    }

    $WyswietlaniePrzyciskuSzybkiZakup = 'nie';
    
    if ( KARTA_PRODUKTU_ZAMOWIENIE_KONTAKT == 'tak' && $Produkt->info['tylko_za_punkty'] == 'nie' && $Produkt->info['status_akcesoria'] == 'nie' && $Produkt->info['status_szybkie_kupowanie'] == 'tak' ) {
      
        $WyswietlaniePrzyciskuSzybkiZakup = 'tak';
      
        if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && KARTA_PRODUKTU_ZAMOWIENIE_KONTAKT_RODZAJ == 'tak' ) {
             $WyswietlaniePrzyciskuSzybkiZakup = 'nie';
        }
    
    }
    
    //
    $Zalogowany = 'nie';
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
         $Zalogowany = 'tak';
    }        
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $Produkt, $NajtanszaWysylka, $WyswietlaniePrzyciskuSzybkiZakup, $Zalogowany);   
    
    unset($WyswietlaniePrzyciskuSzybkiZakup);

    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);    
    
    // najtansza wysylka i tablica wysylek
    $srodek->dodaj('__NAJTANSZY_KOSZT_WYSYLKI', '');  
    $srodek->dodaj('__NAJTANSZY_SPOSOB_WYSYLKI', '');   
    $srodek->dodaj('__SPOSOB_WYSYLKI_TIP', '');
    //
    if ( KARTA_PRODUKTU_KOSZTY_WYSYLKI == 'tak' ) {
        //
        if ( is_array($NajtanszaWysylka) ) {
            //
            $CenaWysylki = $GLOBALS['waluty']->FormatujCene($NajtanszaWysylka['koszt'], 0, 0, $_SESSION['domyslnaWaluta']['id']);
            $srodek->dodaj('__NAJTANSZY_KOSZT_WYSYLKI', $CenaWysylki['brutto']);  
            unset($CenaWysylki);
            //
            $srodek->dodaj('__NAJTANSZY_SPOSOB_WYSYLKI', $NajtanszaWysylka['nazwa']);
            $srodek->dodaj('__SPOSOB_WYSYLKI_TIP', '<b>' . $GLOBALS['tlumacz']['KOSZT_WYSYLKI_INFO'] . '</b>' . implode('',$DostepneWysylki));
            //
            unset($DostepneWysylki);
        }
        //
    }

    // elementy karty produktu
    $srodek->dodaj('__ID_PRODUKTU_UNIKALNE', $Produkt->idUnikat . $Produkt->info['id']);
    
    // microdata
    
    // dostepnosc produktu
    $srodek->dodaj('__MICRODATA_AVAILABILITY', $MicroDataAvialability['google']);

    // stan produktu
    $srodek->dodaj('__MICRODATA_CONDITION', $Produkt->stan_produktu_microdata);     
    
    unset($MicroDataAvialability);  
    
    // zwroty hasMerchantReturnPolicy
    $MicroDataReturnPolicy = '<div itemprop="hasMerchantReturnPolicy" itemtype="https://schema.org/MerchantReturnPolicy" itemscope>' . "\n"; 
    
    if ( isset($_SESSION['krajDostawyDomyslny']) ) {
         $MicroDataReturnPolicy .= '  <meta itemprop="applicableCountry" content="' . strtoupper((string)$_SESSION['krajDostawyDomyslny']['kod']) . '" />' . "\n"; 
    } else {
         $MicroDataReturnPolicy .= '  <meta itemprop="applicableCountry" content="PL" />' . "\n"; 
    }    
    
    if ( $Produkt->info['typ_produktu'] == 'standard' ) {
         $MicroDataReturnPolicy .= '  <meta itemprop="returnPolicyCategory" content="https://schema.org/MerchantReturnFiniteReturnWindow" />' . "\n"; 
         $MicroDataReturnPolicy .= '  <meta itemprop="merchantReturnDays" content="14" />' . "\n"; 
         $MicroDataReturnPolicy .= '  <meta itemprop="returnMethod" content="https://schema.org/ReturnByMail" />' . "\n"; 
         $MicroDataReturnPolicy .= '  <meta itemprop="returnFees" content="https://schema.org/FreeReturn" />' . "\n"; 
    } else {
         $MicroDataReturnPolicy .= '  <meta itemprop="returnPolicyCategory" content="https://schema.org/MerchantReturnNotPermitted" />' . "\n"; 
    }
    
    $MicroDataReturnPolicy .= '</div>' . "\n"; 
    
    $srodek->dodaj('__MICRODATA_RETURN_POLICY', $MicroDataReturnPolicy);     
    unset($MicroDataReturnPolicy);
    
    // dostawa shippingDetails
    $MicroDataShippingDetails = '<div itemprop="shippingDetails" itemtype="https://schema.org/OfferShippingDetails" itemscope>' . "\n"; 
    
    if ( KARTA_PRODUKTU_KOSZTY_WYSYLKI == 'tak' && $_SESSION['krajDostawyDomyslny']['kod'] == $_SESSION['krajDostawy']['kod'] ) {
    
        if ( is_array($NajtanszaWysylka) ) {
    
            $MicroDataShippingDetails .= '  <div itemprop="shippingRate" itemtype="https://schema.org/MonetaryAmount" itemscope>' . "\n"; 
            $MicroDataShippingDetails .= '    <meta itemprop="value" content="' . $NajtanszaWysylka['koszt'] . '" />' . "\n"; 
            $MicroDataShippingDetails .= '    <meta itemprop="currency" content="' . $_SESSION['domyslnaWaluta']['kod'] . '" />' . "\n"; 
            $MicroDataShippingDetails .= '  </div>' . "\n";
            
        }

    }

    $MicroDataShippingDetails .= '  <div itemprop="shippingDestination" itemtype="https://schema.org/DefinedRegion" itemscope>' . "\n"; 
    
    if ( isset($_SESSION['krajDostawyDomyslny']) ) {
         $MicroDataShippingDetails .= '    <meta itemprop="addressCountry" content="' . strtoupper((string)$_SESSION['krajDostawyDomyslny']['kod']) . '" />' . "\n"; 
    } else {
         $MicroDataShippingDetails .= '    <meta itemprop="addressCountry" content="PL" />' . "\n"; 
    }        

    $MicroDataShippingDetails .= '  </div>' . "\n"; 
    
    // czas wysylki deliveryTime
    $MicroDataShippingDetails .= '  <div itemprop="deliveryTime" itemtype="https://schema.org/ShippingDeliveryTime" itemscope>' . "\n"; 
    $MicroDataShippingDetails .= '     <div itemprop="handlingTime" itemtype="https://schema.org/QuantitativeValue" itemscope>' . "\n"; 
    $MicroDataShippingDetails .= '        <meta itemprop="unitCode" content="d" />' . "\n"; 
    $MicroDataShippingDetails .= '        <meta itemprop="minValue" content="1" />' . "\n"; 
    $MicroDataShippingDetails .= '        <meta itemprop="maxValue" content="' . (($Produkt->czas_wysylki_dni != '') ? (int)$Produkt->czas_wysylki_dni : 10) . '" />' . "\n"; 
    $MicroDataShippingDetails .= '     </div>' . "\n";
    $MicroDataShippingDetails .= '     <div itemprop="transitTime" itemtype="https://schema.org/QuantitativeValue" itemscope>' . "\n"; 
    $MicroDataShippingDetails .= '        <meta itemprop="unitCode" content="d" />' . "\n"; 
    $MicroDataShippingDetails .= '        <meta itemprop="minValue" content="1" />' . "\n"; 
    $MicroDataShippingDetails .= '        <meta itemprop="maxValue" content="3" />' . "\n"; 
    $MicroDataShippingDetails .= '     </div>' . "\n";    
    $MicroDataShippingDetails .= '  </div>' . "\n";
        
    $MicroDataShippingDetails .= '</div>' . "\n"; 

    $srodek->dodaj('__MICRODATA_SHIPPING_DETAILS', $MicroDataShippingDetails);     
    unset($MicroDataShippingDetails, $NajtanszaWysylka);
    
    // ikonki na zdjeciu
    $TablicaIkonSortowania = explode(',',(string) IKONY_NA_ZDJECIACH_SORTOWANIE);
    $TablicaSort = array();
    //
    foreach ( $TablicaIkonSortowania as $Tmp ) {
        //
        $TmpPodzial = explode(':', (string)$Tmp);
        //
        if ( count($TmpPodzial) == 2 ) {
             //
             $TablicaSort[ $TmpPodzial[0] ] = $TmpPodzial[1];
             //
        }
        //
        unset($TmpPodzial);
        //
    }
    //
    unset($TablicaIkonSortowania);    

    $Ikonki = array();

    if ( $Produkt->ikonki['nowosc'] == '1' && IKONY_NA_ZDJECIACH_NOWOSCI == 'tak' ) {
        $Ikonki[ ((isset($TablicaSort['nowosc'])) ? (int)$TablicaSort['nowosc'] : '') ] = '<span class="IkonaNowosc Ikona"><b>'. $GLOBALS['tlumacz']['IKONKA_NOWOSC'] . '</b></span>';
    }
    if ( $Produkt->ikonki['promocja'] == '1' && IKONY_NA_ZDJECIACH_PROMOCJE == 'tak' ) {
        $ProcentPromocja = '';
        if ( IKONY_NA_ZDJECIACH_PROMOCJE_PLUS_PROCENT == 'tak' ) {
            $ProcentPromocja = ' -' . $Produkt->ikonki['promocja_procent'] . '%';
        }
        $Ikonki[ ((isset($TablicaSort['promocja'])) ? (int)$TablicaSort['promocja'] : '') ] = '<span class="IkonaPromocja Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_PROMOCJA'] . $ProcentPromocja . '</b></span>';
        unset($ProcentPromocja);
    }
    if ( $Produkt->ikonki['promocja'] == '1' && IKONY_NA_ZDJECIACH_PROMOCJE_PROCENT == 'tak' && isset($Produkt->ikonki['promocja_procent']) && $Produkt->ikonki['promocja_procent'] > 0) {
         $Ikonki[ ((isset($TablicaSort['promocja_ikona'])) ? (int)$TablicaSort['promocja_ikona'] : '') ] = '<span class="IkonaPromocjaProcent Ikona"><b>-' . $Produkt->ikonki['promocja_procent'] . '%</b></span>';
    }              
    if ( $Produkt->ikonki['polecany'] == '1' && IKONY_NA_ZDJECIACH_POLECANE == 'tak' ) {
         $Ikonki[ ((isset($TablicaSort['polecany'])) ? (int)$TablicaSort['polecany'] : '') ] = '<span class="IkonaPolecany Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_POLECANY'] . '</b></span>';
    }
    if ( $Produkt->ikonki['hit'] == '1' && IKONY_NA_ZDJECIACH_NASZ_HIT == 'tak' ) {
         $Ikonki[ ((isset($TablicaSort['hit'])) ? (int)$TablicaSort['hit'] : '') ] = '<span class="IkonaHit Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_HIT'] . '</b></span>';
    }     
    if ( $Produkt->ikonki['darmowa_dostawa'] == '1' && IKONY_NA_ZDJECIACH_DOSTAWA == 'tak' ) {
         $Ikonki[ ((isset($TablicaSort['wysylka_gratis'])) ? (int)$TablicaSort['wysylka_gratis'] : '') ] = '<span class="IkonaDostawa Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_DARMOWA_DOSTAWA'] . '</b></span>';
    }
    if ( $Produkt->ikonki['wyprzedaz'] == '1' && $Produkt->ikonki['promocja'] == '0' ) {
         $Ikonki[ ((isset($TablicaSort['wyprzedaz'])) ? (int)$TablicaSort['wyprzedaz'] : '') ] = '<span class="IkonaWyprzedaz Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_WYPRZEDAZ'] . '</b></span>';
    }     
    if ( $Produkt->ikonki['rabat'] == '1' && IKONY_NA_ZDJECIACH_RABAT == 'tak' ) {
         $Ikonki[ ((isset($TablicaSort['rabat'])) ? (int)$TablicaSort['rabat'] : '') ] = '<span class="IkonaRabat Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_RABAT'] . ' ' . (($Produkt->ikonki['rabat_wartosc'] != '0') ? '' . $Produkt->ikonki['rabat_wartosc'] . '%' : '') . '</b></span>';
    } 

    $TablicaOpcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_1, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_1),
                          array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_2, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_2),
                          array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_3, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_3),
                          array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_4, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_4),
                          array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_5, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_5));

    foreach ( $TablicaOpcje as $Tmp ) {
        //
        if ( $Tmp['aktywne'] == 'tak' ) {
             //
             if ( $Produkt->ikonki['ikona_' . $Tmp['nr']] == '1' ) {
                  //
                  if ( isset($GLOBALS['nazwy_ikonek'][$Tmp['nr']][$_SESSION['domyslnyJezyk']['id']]) ) {
                       $Ikonki[ ((isset($TablicaSort['ikona_' . $Tmp['nr']])) ? (int)$TablicaSort['ikona_' . $Tmp['nr']] : '') ] = '<span class="Ikona"><b style="background-color:#' . $Tmp['kolor'] . '">' . $GLOBALS['nazwy_ikonek'][$Tmp['nr']][$_SESSION['domyslnyJezyk']['id']] . '</b></span>';
                  }
                  //
             }           
             //
        }
        //
    }    
    
    ksort($Ikonki);
    
    $srodek->dodaj('__IKONKI', implode('', $Ikonki));
    unset($Ikonki, $TablicaOpcje);

    // dodatkowe zdjecia produktu
    $DodatkoweZdjecia = $Produkt->ProduktDodatkoweZdjecia();
    //
    $ZdjeciaDuze = '';
    $ZdjeciaMiniaturki = '';
    //
    $FotoDod = 1;
    //
    if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
        $zdjecie_glowne = Funkcje::pokazObrazekWatermark($Produkt->fotoGlowne['plik_zdjecia']);
    } else {
        $zdjecie_glowne = KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'];
    }
    
    // glowne zdjecie produktu
    $ZdjeciaDuze .= '<a data-jbox-image="galeria" id="DuzeFoto' . $FotoDod . '" ' . (($FotoDod == 1) ? 'class="PodmianaFotoCech"' : '') . ' href="'.$zdjecie_glowne.'" title="' . $Produkt->fotoGlowne['opis_zdjecia'] . '" data-caption="'.$Produkt->fotoGlowne['opis_zdjecia'].'">' . Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'itemprop="image" data-zoom-image="'.$zdjecie_glowne.'" class="FotoZoom"', 'sredni') . '</a>';

    $ZdjeciaMiniaturki .= '<div>' . Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'id="Foto' . $FotoDod . '"' . (($FotoDod == 1) ? 'class="PodmianaFotoCechMini"' : ''), 'maly', true, false, true) . '</div>';

    if ( count($DodatkoweZdjecia) > 0 ) {
        //
        foreach ($DodatkoweZdjecia As $DodFoto) {
            //
            $FotoDod++;
            //
            // generowanie alt zdjec
            $AltFoto = ((empty($DodFoto['alt'])) ? $Produkt->info['nazwa'] : $DodFoto['alt']);
            //
            if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                $zdjecie_dodatkowe = Funkcje::pokazObrazekWatermark($DodFoto['zdjecie']);
            } else {
                $zdjecie_dodatkowe = KATALOG_ZDJEC . '/' . $DodFoto['zdjecie'];
            }
            
            $ZdjeciaDuze .= '<a data-jbox-image="galeria" id="DuzeFoto' . $FotoDod . '" href="' . $zdjecie_dodatkowe . '" title="' . ((empty($DodFoto['alt'])) ? $Produkt->fotoGlowne['opis_zdjecia'] : $DodFoto['alt']) . '" data-caption="'.$AltFoto.'">' . Funkcje::pokazObrazek($DodFoto['zdjecie'], $AltFoto, SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'itemprop="image" data-zoom-image="' . $zdjecie_dodatkowe . '" class="FotoZoom"', 'sredni') . '</a>';
            $ZdjeciaMiniaturki .= '<div>' . Funkcje::pokazObrazek($DodFoto['zdjecie'], $AltFoto, SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'id="Foto' . $FotoDod . '"', 'maly', true, false, true) . '</div>';

            unset($AltFoto);
            //
        }
        //
        unset($TablicaMetaTagow, $FotoDod);
        //
    }

    // zdjecia duze
    $srodek->dodaj('__ZDJECIA_DUZE', $ZdjeciaDuze);
    // zdjecia miniaturki
    if ( count($DodatkoweZdjecia) > 0 ) {
        $srodek->dodaj('__MINIATURKI_UKRYJ', '');
        $srodek->dodaj('__ZDJECIA_MINIATURKI', $ZdjeciaMiniaturki);
      } else {
        $srodek->dodaj('__MINIATURKI_UKRYJ', 'display:none;');
        $srodek->dodaj('__ZDJECIA_MINIATURKI', '');
    }
    //
    unset($ZdjeciaDuze, $ZdjeciaMiniaturki);
    
    // nazwa produktu
    $srodek->dodaj('__NAZWA_PRODUKTU', $Produkt->info['nazwa']);
    
    // zdjecie do podziel sie
    if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
        $srodek->dodaj('__URL_ZDJECIE_DUZE', urlencode(ADRES_URL_SKLEPU . '/' . Funkcje::pokazObrazekWatermark($Produkt->fotoGlowne['plik_zdjecia'])));
    } else {
        $srodek->dodaj('__URL_ZDJECIE_DUZE', urlencode(ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia']));
    }       
    
    // nazwa produktu do podziel sie 
    $srodek->dodaj('__NAZWA_PRODUKTU_PODZIEL_SIE', (isset($Produkt->info['nazwa']) ? urlencode($Produkt->info['nazwa']) : '' ));    
    
    // data dostepnosci
    $srodek->dodaj('__DATA_DOSTEPNOSCI', $Produkt->info['data_dostepnosci']);
    $srodek->dodaj('__FUNKCJA_ZEGAR_DOSTEPNOSCI', '');
    if ( $Produkt->info['zegar_dostepnosci'] == 'tak' ) {
         $srodek->dodaj('__FUNKCJA_ZEGAR_DOSTEPNOSCI', Wyglad::PrzegladarkaJavaScript( 'odliczaj_zegar_prosty("sekundy_dostepnosci_'.$Produkt->info['id'].'",' . (FunkcjeWlasnePHP::my_strtotime($Produkt->info['data_od_kiedy_kupowac']) - time()) . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\',\'{__TLUMACZ:LICZNIK_PROMOCJI_JEDEN_DZIEN}\')' ));   
    }
    
    // srednia ocena produktu
    $srodek->dodaj('__SREDNIA_OCENA_GWIAZDKI', $Produkt->recenzjeSrednia['srednia_ocena_obrazek']);
    //
    $srodek->dodaj('__SREDNIA_OCENA_ILOSC_TEKST', $Produkt->recenzjeSrednia['srednia_ocena']);
    $srodek->dodaj('__SREDNIA_OCENA_ILOSC', $Produkt->recenzjeSrednia['ilosc_glosow']);
    $srodek->dodaj('__SREDNIA_OCENA_ILOSC_GLOSOW', '<span class="WszystkieRecenzje" tabindex="0" role="button">' . $Produkt->recenzjeSrednia['ilosc_glosow'] . '</span>');    
    
    // paski recenzji
    if ( count($Produkt->recenzje) > 0 ) {
         //
         $OcenaTab = array();
         //
         foreach ( $Produkt->recenzje as $Recenzja ) {
            //
            $OcenaTab[(int)$Recenzja['recenzja_ocena']] = ((isset($OcenaTab[(int)$Recenzja['recenzja_ocena']])) ? $OcenaTab[(int)$Recenzja['recenzja_ocena']] : 0) + 1;                        
            //
         }         
         //
         $PasekOcen = array();
         //
         for ( $x = 1; $x <= 5; $x++ ) {
               //
               $PasekOcen[$x] = ((((isset($OcenaTab[$x])) ? $OcenaTab[$x] : 0) / $Produkt->recenzjeSrednia['ilosc_glosow']) * 100);
               //
         }
         //
    }
    //
    for ( $x = 1; $x <= 5; $x++ ) {
          //
          $srodek->dodaj('__OCENA_PASEK_' . $x, ((isset($PasekOcen[$x])) ? $PasekOcen[$x] : 0));
          $srodek->dodaj('__OCENA_ILOSC_' . $x, ((isset($OcenaTab[$x])) ? $OcenaTab[$x] : 0));
          $srodek->dodaj('__OCENA_SELECT_' . $x, ((isset($OcenaTab[$x])) ? '' : 'disabled="disabled"'));
          //
    }

    // producent logo albo nazwa
    $srodek->dodaj('__PRODUCENT', '');
    if ( KARTA_PRODUKTU_PRODUCENT == 'tak' ) {
        //
        if ( trim((string)$Produkt->producent['foto']) != '' ) {
            $srodek->dodaj('__PRODUCENT', $Produkt->producent['foto_link']);
          } else {
            $srodek->dodaj('__PRODUCENT', $Produkt->producent['link']);
        }    
        //
    }
    
    // ceny
    
    if (( CENY_DLA_WSZYSTKICH == 'tak' || ( CENY_DLA_WSZYSTKICH == 'nie' && ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) ) ) && UKRYJ_CENY == 'tak' && $Produkt->info['jest_cena'] == 'tak' ) {
        
        // cena poprzednia
        $srodek->dodaj('__CENA_POPRZEDNIA', '');
        if ( $Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) {
            //
            $srodek->dodaj('__CENA_POPRZEDNIA', $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_poprzednia_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ));
            //
        }       
        
        // cena katalogowa
        $srodek->dodaj('__CENA_KATALOGOWA', '');
        $srodek->dodaj('__CENA_OSZCZEDZASZ', '');
        if ( $Produkt->info['cena_katalogowa_bez_formatowania'] > 0 ) {
            //
            $srodek->dodaj('__CENA_KATALOGOWA', $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_katalogowa_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ));
            //
            // oszczedzasz
            if ( KARTA_PRODUKTU_CENA_KATALOGOWA_TYP == 'procent' ) {
                //
                $oszczedzasz = ( 1 - ( $Produkt->info['cena_brutto_bez_formatowania'] / $Produkt->info['cena_katalogowa_bez_formatowania'] ) ) * 100;
                if ( KARTA_PRODUKTU_CENA_KATALOGOWA_TYP_ZAOKRAGLENIE == 'ułamek' ) {
                     $srodek->dodaj('__CENA_OSZCZEDZASZ', number_format($oszczedzasz,2, '.', '') . '%');
                   } else {
                     $srodek->dodaj('__CENA_OSZCZEDZASZ', number_format($oszczedzasz,0, '.', '') . '%');
                }
                unset($oszczedzasz);
                //
              } else {
                //
                $srodek->dodaj('__CENA_OSZCZEDZASZ', $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_katalogowa_bez_formatowania'] - $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ));
                //
            }
            //        
        }
        
        // cena netto i brutto
        if ( $Produkt->info['tylko_za_punkty'] == 'nie' ) {
             //
             $srodek->dodaj('__CENA_BRUTTO', $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ));
             $srodek->dodaj('__CENA_NETTO', $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_netto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ));
             //
          } else {
             // jezeli kupowanie tylko za punkty
             $srodek->dodaj('__CENA_BRUTTO', $GLOBALS['waluty']->PokazCenePunkty( $Produkt->info['cena_w_punktach'], $Produkt->info['cena_brutto_bez_formatowania'], false ));
             $srodek->dodaj('__CENA_NETTO', $GLOBALS['waluty']->PokazCenePunkty( $Produkt->info['cena_w_punktach'], $Produkt->info['cena_netto_bez_formatowania'], false ));
             //
        }
        
        // ceny do inputow - ukryte
        $srodek->dodaj('__CENA_BRUTTO_BEZ_FORMATOWANIA', $Produkt->info['cena_brutto_bez_formatowania']);
        $srodek->dodaj('__CENA_NETTO_BEZ_FORMATOWANIA', $Produkt->info['cena_netto_bez_formatowania']);
        $srodek->dodaj('__CENA_POPRZEDNIA_BEZ_FORMATOWANIA', $Produkt->info['cena_poprzednia_bez_formatowania']);
        $srodek->dodaj('__CENA_KATALOGOWA_BEZ_FORMATOWANIA', $Produkt->info['cena_katalogowa_bez_formatowania']);
        
        // cena w puktach jezeli produkt jest tylko za PUNKTY
        $srodek->dodaj('__CENA_PRODUKTU_PKT', $Produkt->info['cena_w_punktach']);
    
    } else {
    
        // cena poprzednia
        $srodek->dodaj('__CENA_POPRZEDNIA', '');
        
        // cena netto i brutto
        $srodek->dodaj('__CENA_BRUTTO', '');
        $srodek->dodaj('__CENA_NETTO', '');
        
        // ceny do inputow - ukryte
        $srodek->dodaj('__CENA_BRUTTO_BEZ_FORMATOWANIA', '0');
        $srodek->dodaj('__CENA_NETTO_BEZ_FORMATOWANIA', '0');
        $srodek->dodaj('__CENA_POPRZEDNIA_BEZ_FORMATOWANIA', '0'); 
        $srodek->dodaj('__CENA_KATALOGOWA_BEZ_FORMATOWANIA', '0');       

        // cena w puktach jezeli produkt jest tylko za PUNKTY
        $srodek->dodaj('__CENA_PRODUKTU_PKT', 0);        
    
    }
    
    // jezeli produkt nie ma ceny
    $srodek->dodaj('__INFO_BRAK_CENY_PRODUKTU','');
    if ( $Produkt->info['jest_cena'] == 'nie' ) {
        //
        if ( UKRYJ_CENY == 'tak' ) {
             //
             if ( ( CENY_DLA_WSZYSTKICH == 'tak' || ( CENY_DLA_WSZYSTKICH == 'nie' && ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) ) ) && $Produkt->info['cena_dla_niezalogowanych'] == 'tak' ) {
                 if ( isset($Wyglad->Formularze[2]) ) {
                    $srodek->dodaj('__INFO_BRAK_CENY_PRODUKTU', '<a class="przycisk" href="'.( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( $Wyglad->Formularze[2], 2, 'formularz' ) . '/produkt=' . Funkcje::SamoIdProduktuBezCech($Produkt->info['id']).'">'.$GLOBALS['tlumacz']['CENA_ZAPYTAJ_O_CENE'].'</a>');
                 } else {
                    $srodek->dodaj('__INFO_BRAK_CENY_PRODUKTU', $GLOBALS['tlumacz']['CENA_ZAPYTAJ_O_CENE']);
                 }
                } else {
                  $srodek->dodaj('__INFO_BRAK_CENY_PRODUKTU', $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH']);
             }
             //
        }
        //
    }
    
    // informacja o rabatach
    $srodek->dodaj('__INFO_O_RABATACH_PRODUKTU', '');
    if ( $Produkt->info['rabat_produktu'] > 0 ) {
        $srodek->dodaj('__INFO_O_RABATACH_PRODUKTU', $GLOBALS['tlumacz']['INFO_RABAT_CENY'] .' <strong>' . $Produkt->info['rabat_produktu'] . '%</strong>');
    }


    // ikonki parametrow produktu
    $ikonki = '';
    if ( IKONY_PARAMETRY_PRODUKTU == 'tak' || IKONY_DODATKOWE_POLA == 'tak' ) {
        $ikonki .= '<style>';
        if ( Wyglad::TypSzablonu() == false ) {
            $ikonki .= '.TbPoz { display:flex !important; align-items:center; }';
        }
    }
    if ( IKONY_PARAMETRY_PRODUKTU == 'tak' ) {
        $ikonki .= '.ListaOpisy p:not(.TbPozPoleDodatkowe) { position:relative; padding-left:'.(SZEROKOSC_IKONA_DODATKOWE_POLA+10).'px; min-height:'.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}
        .ListaOpisy p:not(.TbPozPoleDodatkowe)::before {content:""; position:absolute; left:0; width:'.SZEROKOSC_IKONA_DODATKOWE_POLA.'px; height:'.WYSOKOSC_IKONA_DODATKOWE_POLA.'px; background: url("'.KATALOG_ZDJEC.'/'.( IKONA_DOMYSLNA != '' ? IKONA_DOMYSLNA : "domyslny.webp" ).'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
    }

    if ( IKONY_DODATKOWE_POLA == 'tak' ) {
        $ikonki .= '.ListaOpisy p.TbPozPoleDodatkowe, .DodatkowePolaOpis p { position:relative; padding-left:'.(SZEROKOSC_IKONA_DODATKOWE_POLA+10).'px; min-height:'.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;} .ListaOpisy p.TbPozPoleDodatkowe::before, .DodatkowePolaOpis p::before {content:""; position:absolute; left:0; width:'.SZEROKOSC_IKONA_DODATKOWE_POLA.'px; height:'.WYSOKOSC_IKONA_DODATKOWE_POLA.'px; background: url("'.KATALOG_ZDJEC.'/'.( IKONA_DOMYSLNA != '' ? IKONA_DOMYSLNA : "domyslny.webp" ).'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
    }

    if ( IKONY_PARAMETRY_PRODUKTU == 'tak' ) {
        if ( !empty(IKONA_DOSTEPNOSC) ) {
            $ikonki .= '.ListaOpisy p#Dostepnosc::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_DOSTEPNOSC.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_CZAS_WYSYLKI) ) {
            $ikonki .= '.ListaOpisy p#CzasWysylki::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_CZAS_WYSYLKI.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_KOSZT_WYSYLKI) ) {
            $ikonki .= '.ListaOpisy p#KosztWysylki::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_KOSZT_WYSYLKI.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_NR_KATALOGOWY) ) {
            $ikonki .= '.ListaOpisy p#NrKatalogowy::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_NR_KATALOGOWY.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_KOD_PRODUCENTA) ) {
            $ikonki .= '.ListaOpisy p#KodProducenta::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_KOD_PRODUCENTA.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_STAN_MAGAZYNOWY) ) {
            $ikonki .= '.ListaOpisy p#StanMagazynowy::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_STAN_MAGAZYNOWY.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_EAN) ) {
            $ikonki .= '.ListaOpisy p#KodEan::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_EAN.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_PKWIU) ) {
            $ikonki .= '.ListaOpisy p#Pkwiu::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_PKWIU.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_STAN_PRODUKTU) ) {
            $ikonki .= '.ListaOpisy p#StanProduktu::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_STAN_PRODUKTU.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_GWARANCJA_PRODUKTU) ) {
            $ikonki .= '.ListaOpisy p#GwarancjaProduktu::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_GWARANCJA_PRODUKTU.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_WAGA_PRODUKTU) ) {
            $ikonki .= '.ListaOpisy p#WagaProduktu::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_WAGA_PRODUKTU.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }
        if ( !empty(IKONA_KLASAEN_PRODUKTU) ) {
            $ikonki .= '.ListaOpisy p#KlasaEnergetyczna::before { background: url("'.KATALOG_ZDJEC.'/'.IKONA_KLASAEN_PRODUKTU.'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
        }

    }

    // ikonki dodatkowych pol opisowych produktu
    if ( IKONY_DODATKOWE_POLA == 'tak' ) {
        foreach ( $Produkt->dodatkowePolaFoto as $Pole ) {
            if ( $Pole['ikona'] != '' ) {
                $ikonki .= '.ListaOpisy p#poleDodatkowe_'.$Pole['id'].'::before { background: url("'.KATALOG_ZDJEC.'/'.$Pole['ikona'].'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
            }
        }
        foreach ( $Produkt->dodatkowePolaOpis as $Pole ) {
            if ( $Pole['ikona'] != '' ) {
                $ikonki .= '.DodatkowePolaOpis p#poleDodatkowe_'.$Pole['id'].'::before { background: url("'.KATALOG_ZDJEC.'/'.$Pole['ikona'].'") no-repeat left center; background-size: '.SZEROKOSC_IKONA_DODATKOWE_POLA.'px '.WYSOKOSC_IKONA_DODATKOWE_POLA.'px;}';
            }
        }
    }

    if ( IKONY_PARAMETRY_PRODUKTU == 'tak' || IKONY_DODATKOWE_POLA == 'tak' ) {
        $ikonki .= '</style>';
    }
    $srodek->dodaj('__PARAMETRY_PRODUKTU_CSS', $ikonki);
    unset($ikonki);


    // dostepnosc
    $srodek->dodaj('__DOSTEPNOSC', $Produkt->dostepnosc['dostepnosc']);
    
    // czas wysylki
    $srodek->dodaj('__CZAS_WYSYLKI', $Produkt->czas_wysylki);
    
    // stan produktu
    $srodek->dodaj('__STAN_PRODUKTU', $Produkt->stan_produktu);    
    
    // gwarancja produktu
    $srodek->dodaj('__GWARANCJA', $Produkt->gwarancja);
    
    // nr katalogowy
    $srodek->dodaj('__NR_KATALOGOWY', $Produkt->info['nr_katalogowy']);
    
    // kod producenta
    $srodek->dodaj('__KOD_PRODUCENTA', $Produkt->info['kod_producenta']);
    
    // kod ean
    $srodek->dodaj('__KOD_EAN', $Produkt->info['ean']);
    
    // kod pkwiu
    $srodek->dodaj('__KOD_PKWIU', $Produkt->info['pkwiu']);    
    
    // waga produktu
    $srodek->dodaj('__WAGA_PRODUKTU', number_format($Produkt->info['waga'], 2, ',', '') . ' ' . $GLOBALS['tlumacz']['KOSZYK_WAGA_PRODUKTOW_JM']);      
    
    // rozporzadzenie gpsr
    if ( $Produkt->info['zestaw'] == 'tak' ) {
         //
         $srodek->dodaj('__INFORMACJA_O_BEZPIECZENSTWIE', '');  
         $srodek->dodaj('__INFORMACJE_O_PRODUCENCIE', '');
         //
    } else {
         //
         $srodek->dodaj('__INFORMACJA_O_BEZPIECZENSTWIE', $Produkt->info['informacja_o_bezpieczenstwie']);    
         //
         $gpsr = '';
         //
         if ( (int)$Produkt->info['id_producenta'] > 0 ) {
              //
              $TablicaGpsr = Producenci::TablicaProducentGpsr($Produkt->info['id_producenta']);
              //
              if ( !empty($TablicaGpsr['Url']) ) {
                  //
                  $gpsr .= '<div style="padding-bottom:20px" class="InfoProducentLink FormatEdytor"><h4>' . $GLOBALS['tlumacz']['STRONA_WWW'] . '</h4>';
                  //
                  $gpsr .= $TablicaGpsr['Url'];        
                  //
                  $gpsr .= '</div>';
                  //
             }
             //
             if ( !empty($TablicaGpsr['Producent']['ProducentUlica']) && !empty($TablicaGpsr['Producent']['ProducentMiasto']) && !empty($TablicaGpsr['Producent']['ProducentKraj']) && (!empty($TablicaGpsr['Producent']['ProducentEmail']) || !empty($TablicaGpsr['Producent']['ProducentTelefon'])) ) {
                 //
                 $gpsr .= '<div style="padding-bottom:20px" class="InfoProducentOpis FormatEdytor"><h4>' . $GLOBALS['tlumacz']['DANE_KONTAKTOWE_PRODUCENTA'] . '</h4>';
                 //
                 $gpsr .= $TablicaGpsr['Producent']['ProducentNazwa'] . ', ' . $TablicaGpsr['Producent']['ProducentUlica'];
                 //
                 if ( !empty($TablicaGpsr['Producent']['ProducentKodPocztowy']) ) {
                      //
                      $gpsr .= ', ' . $TablicaGpsr['Producent']['ProducentKodPocztowy'] . ' ' . $TablicaGpsr['Producent']['ProducentMiasto'];
                      //
                 } else {
                      //
                      $gpsr .= ', ' . $TablicaGpsr['Producent']['ProducentMiasto'];
                      //
                 }
                 //
                 foreach ( Funkcje::KrajeIso() as $kraj => $iso ) {
                      //
                      if ( $iso ==  $TablicaGpsr['Producent']['ProducentKraj'] ) {
                           //
                           $gpsr .= ', ' . $kraj;
                           //
                      }
                      //
                 }
                 //
                 if ( !empty($TablicaGpsr['Producent']['ProducentEmail']) ) {
                      //
                      $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['EMAIL']) . ' ' . $TablicaGpsr['Producent']['ProducentEmail'];
                      //
                 }    
                 if ( !empty($TablicaGpsr['Producent']['ProducentTelefon']) ) {
                      //
                      $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['TELEFON']) . ': ' . $TablicaGpsr['Producent']['ProducentTelefon'];
                      //
                 }              
                 //
                 $gpsr .= '</div>';
                 //
             }
             //
             if ( (int)$TablicaGpsr['TakieSame'] == 1 ) {
                  //
                  if ( !empty($TablicaGpsr['Importer']['ImporterUlica']) && !empty($TablicaGpsr['Importer']['ImporterMiasto']) && !empty($TablicaGpsr['Importer']['ImporterKraj']) && (!empty($TablicaGpsr['Importer']['ImporterEmail']) || !empty($TablicaGpsr['Importer']['ImporterTelefon'])) ) {
                       //
                       $gpsr .= '<div style="padding-bottom:20px" class="InfoImporterOpis FormatEdytor"><h4>' . $GLOBALS['tlumacz']['DANE_KONTAKTOWE_IMPORTERA'] . '</h4>';
                       //
                       $gpsr .= $TablicaGpsr['Importer']['ImporterNazwa'] . ', ' . $TablicaGpsr['Importer']['ImporterUlica'];
                       //
                       if ( !empty($TablicaGpsr['Importer']['ImporterKodPocztowy']) ) {
                            //
                            $gpsr .= ', ' . $TablicaGpsr['Importer']['ImporterKodPocztowy'] . ' ' . $TablicaGpsr['Importer']['ImporterMiasto'];
                            //
                       } else {
                            //
                            $gpsr .= ', ' . $TablicaGpsr['Importer']['ImporterMiasto'];
                            //
                       }
                       //
                       foreach ( Funkcje::KrajeIso() as $kraj => $iso ) {
                            //
                            if ( $iso ==  $TablicaGpsr['Importer']['ImporterKraj'] ) {
                                 //
                                 $gpsr .= ', ' . $kraj;
                                 //
                            }
                            //
                       }
                       //
                       if ( !empty($TablicaGpsr['Importer']['ImporterEmail']) ) {
                            //
                            $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['EMAIL']) . ' ' . $TablicaGpsr['Importer']['ImporterEmail'];
                            //
                       }    
                       if ( !empty($TablicaGpsr['Importer']['ImporterTelefon']) ) {
                            //
                            $gpsr .= ', ' . strtolower($GLOBALS['tlumacz']['TELEFON']) . ': ' . $TablicaGpsr['Importer']['ImporterTelefon'];
                            //
                       }              
                       //
                       $gpsr .= '</div>';
                       //
                  }
                  //
             }
             //       
         }   
         //
         $srodek->dodaj('__INFORMACJE_O_PRODUCENCIE', $gpsr);

    }        
        
    // ukrywanie wagi o wartosci 0
    $srodek->dodaj('__WAGA_PRODUKTU_CSS',''); 
    if ( (float)$Produkt->info['waga'] == 0 ) {
          $srodek->dodaj('__WAGA_PRODUKTU_CSS','style="display:none"'); 
    }    
    
    // jednostka miary
    $srodek->dodaj('__JEDNOSTKA_MIARY', (($Produkt->info['jednostka_miary'] != '' && PRODUKT_CENA_JM == 'tak') ? '<em class="JmCena"> / ' . $Produkt->info['jednostka_miary'] . '</em>' : ''));     
    
    // dostepna ilosc - stan magazynowy
    // wersja graficzna lub tekstowa
    if ( KARTA_PRODUKTU_MAGAZYN_FORMA == 'liczba' ) {
         $srodek->dodaj('__STAN_MAGAZYNOWY', number_format( $Produkt->info['ilosc'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary']);
       } else {
         $srodek->dodaj('__STAN_MAGAZYNOWY', Produkty::PokazPasekMagazynu($Produkt->info['ilosc'], $Produkt->info['alarm_magazyn']));   
    }
    
    // klasa energetyczna
    $srodek->dodaj('__KLASA_ENERGETYCZNA', $Produkt->info['klasa_energetyczna']);    
    
    // cechy produktu
    $srodek->dodaj('__OPCJE_PRODUKTU', $Produkt->ProduktCechyGeneruj());

    // kupowanie
    // jezeli jest sklep jako katalog produktow

    if ( PRODUKT_KUPOWANIE_STATUS == 'nie' ) {
        //
        $srodek->dodaj('__INPUT_ILOSC', '');
        $srodek->dodaj('__PRZYCISK_KUP', '');
        $srodek->dodaj('__ZAKUP_PRZEZ_ALLEGRO', '');
        $srodek->dodaj('__INFO_NIEDOSTEPNY', '');
        $srodek->dodaj('__RATY_SANTANDER', '');
        $srodek->dodaj('__SANTANDER_PARAMETRY', '');
        $srodek->dodaj('__RATY_LUKAS', '');
        $srodek->dodaj('__SANTANDER_PARAMETRY', '');
        $srodek->dodaj('__RATY_MBANK', '');
        $srodek->dodaj('__RATY_TPAY', '');
        $srodek->dodaj('__RATY_COMFINO', '');
        $srodek->dodaj('__MBANK_PARAMETRY', '');
        $srodek->dodaj('__RATY_PAYURATY', '');
        $srodek->dodaj('__PAYURATY_PARAMETRY', '');
        $srodek->dodaj('__RATY_BGZ', '');
        $srodek->dodaj('__BGZRATY_PARAMETRY', '');
        $srodek->dodaj('__PAYURATY_WIDGET', '');
        $srodek->dodaj('__RATY_ILEASING', '');
        $srodek->dodaj('__ILEASING_PARAMETRY', '');
        $srodek->dodaj('__RATY_IRATY', '');
        $srodek->dodaj('__IRATY_PARAMETRY', '');
        $srodek->dodaj('__RATY_LEASELINK', '');
        $srodek->dodaj('__LEASELINK_PARAMETRY', '');
        $srodek->dodaj('__COMFINO_PARAMETRY', '');
        $srodek->dodaj('__GRAFIKA_PAYPO', '');

        //
    } else {
        //
        if ( Wyglad::TypSzablonu() == true ) {
            $srodek->dodaj('__INPUT_ILOSC', $GLOBALS['tlumacz']['ZAMAWIANA_ILOSC'] . '<div class="PoleIlosc"><span class="minus">-</span><input type="number" lang="en_EN" pattern="[0-9]+([\.][0-9]+)?" step="' . ( $Produkt->info['przyrost'] > '0' ? $Produkt->info['przyrost'] : '1' ) . '" id="ilosc_' . $Produkt->idUnikat . $Produkt->id_produktu . '" value="' . $Produkt->zakupy['domyslna_ilosc'] . '" min="' . ( $Produkt->zakupy['minimalna_ilosc'] > 0 ? $Produkt->zakupy['minimalna_ilosc'] : '1' ) . '" onchange="SprIlosc(this,' . $Produkt->zakupy['minimalna_ilosc'] . ',' . $Produkt->info['jednostka_miary_typ'] . ',\'' . $Produkt->idUnikat . $Produkt->id_produktu . '\',\'' . $Produkt->info['przyrost'] . '\')" name="ilosc" aria-label="' . $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . '" /><span class="plus">+</span></div>' . $Produkt->info['jednostka_miary']);
        } else {
            $srodek->dodaj('__INPUT_ILOSC', $GLOBALS['tlumacz']['ZAMAWIANA_ILOSC'] . ' <input type="number" step="' . ( $Produkt->info['przyrost'] > '0' ? $Produkt->info['przyrost'] : '1' ) . '" id="ilosc_' . $Produkt->idUnikat . $Produkt->id_produktu . '" value="' . $Produkt->zakupy['domyslna_ilosc'] . '" min="' . ( $Produkt->zakupy['minimalna_ilosc'] > 0 ? $Produkt->zakupy['minimalna_ilosc'] : '1' ) . '" onchange="SprIlosc(this,' . $Produkt->zakupy['minimalna_ilosc'] . ',' . $Produkt->info['jednostka_miary_typ'] . ',\'' . $Produkt->idUnikat . $Produkt->id_produktu . '\',\'' . $Produkt->info['przyrost'] . '\')" name="ilosc" aria-label="' . $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . '" />' . $Produkt->info['jednostka_miary']);
        }
        $srodek->dodaj('__PRZYCISK_KUP', $Produkt->zakupy['przycisk_kup_karta']);
        //
        $srodek->dodaj('__ZAKUP_PRZEZ_ALLEGRO', '');
        //
        if ( count($Produkt->AukcjeAllegro) > 0 ) {
             //
             $srodek->dodaj('__ZAKUP_PRZEZ_ALLEGRO', '<a target="_blank" rel="nofollow" href="' . $Produkt->AukcjeAllegro[0]['link_aukcji'] . '">' . $GLOBALS['tlumacz']['ZAKUP_ALLEGRO'] . '</a>');
             //
        }
        //
        $srodek->dodaj('__PAYURATY_WIDGET', '');
        //
        if ( $Produkt->info['status_kupowania'] == 'tak' ) {
             //
             $srodek->dodaj('__INFO_NIEDOSTEPNY', '<span class="Info">' . $GLOBALS['tlumacz']['PRODUKT_NIEDOSTEPNY'] . '</span>');

             // Tworzenie tablicy systemow ratalnych
             $SystemyRatalne = Funkcje::AktywneSystemyRatalne();
             
             // Jezeli jest wlaczony modul ratalny Santander
             if ( isset($SystemyRatalne['platnosc_santander']) && count($SystemyRatalne['platnosc_santander']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                if ($Produkt->info['cena_brutto_bez_formatowania'] >= $SystemyRatalne['platnosc_santander']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'] && $Produkt->info['cena_brutto_bez_formatowania'] <= $SystemyRatalne['platnosc_santander']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX']) {
                    $srodek->dodaj('__SANTANDER_PARAMETRY', $SystemyRatalne['platnosc_santander']['PLATNOSC_SANTANDER_NUMER_SKLEPU'].';'.$SystemyRatalne['platnosc_santander']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'].';'.$SystemyRatalne['platnosc_santander']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'].';'.$SystemyRatalne['platnosc_santander']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] );
                    $srodek->dodaj('__RATY_SANTANDER', '<div id="RatySantander" style="margin-bottom:10px;"><a onclick="PoliczRateSantander();" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_produkt.png" alt="Raty Santander" /></a></div>');
                } else {
                    $srodek->dodaj('__SANTANDER_PARAMETRY', '');
                    $srodek->dodaj('__RATY_SANTANDER', '');
                }
             } else {
                $srodek->dodaj('__SANTANDER_PARAMETRY', '');
                $srodek->dodaj('__RATY_SANTANDER', '');
             }

             // Jezeli jest wlaczony modul ratalny MBANK
             if ( isset($SystemyRatalne['platnosc_mbank']) && count($SystemyRatalne['platnosc_mbank']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $srodek->dodaj('__MBANK_PARAMETRY', $SystemyRatalne['platnosc_mbank']['PLATNOSC_MBANK_NUMER_SKLEPU'].';'.$SystemyRatalne['platnosc_mbank']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'].';'.$SystemyRatalne['platnosc_mbank']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] );
                $srodek->dodaj('__RATY_MBANK', '<div id="RatyMbank" style="margin-bottom:10px;"><a onclick="PoliczRateMbank();" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/oblicz_rate_mbank_produkt.png" alt="mBank" /></a></div>');
             } else {
                $srodek->dodaj('__MBANK_PARAMETRY', '');
                $srodek->dodaj('__RATY_MBANK', '');
             }


             // Jezeli jest wlaczony modul ratalny TPay
             if ( isset($SystemyRatalne['platnosc_transferuj']) || isset($SystemyRatalne['platnosc_tpay']) ) {
                if ( isset($SystemyRatalne['platnosc_transferuj']) && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' && ($Produkt->info['cena_brutto_bez_formatowania'] >= 100 && $Produkt->info['cena_brutto_bez_formatowania'] <= 20000)) {
                    $srodek->dodaj('__RATY_TPAY', '<div id="RatyTPay" style="margin-bottom:10px;"><a onclick="PoliczRateTPay(\'' . $SystemyRatalne['platnosc_transferuj']['PLATNOSC_TPAY_RATY_RODZAJ'] . '\');" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/tpay_raty.png" alt="TPAY Raty" /></a></div>');
                } elseif ( isset($SystemyRatalne['platnosc_tpay']) && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' && ($Produkt->info['cena_brutto_bez_formatowania'] >= 100 && $Produkt->info['cena_brutto_bez_formatowania'] <= 20000)) {
                    $srodek->dodaj('__RATY_TPAY', '<div id="RatyTPay" style="margin-bottom:10px;"><a onclick="PoliczRateTPay(\'' . $SystemyRatalne['platnosc_tpay']['PLATNOSC_TPAY_REST_RATY_RODZAJ'] . '\');" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/tpay_raty.png" alt="TPAY Raty" /></a></div>');
                } else {
                    $srodek->dodaj('__RATY_TPAY', '');
                }
             } else {
                $srodek->dodaj('__RATY_TPAY', '');
             }             

             // Jezeli jest wlaczony modul ratalny COMFINO
             if ( isset($SystemyRatalne['platnosc_comfino']) && count($SystemyRatalne['platnosc_comfino']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $srodek->dodaj('__COMFINO_PARAMETRY', $SystemyRatalne['platnosc_comfino']['PLATNOSC_COMFINO_KLUCZ'].';'.$SystemyRatalne['platnosc_comfino']['PLATNOSC_COMFINO_WIDGET_KLUCZ'].';'.$SystemyRatalne['platnosc_comfino']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'].';'.$SystemyRatalne['platnosc_comfino']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] );

                $Widget = "<style>.cmxform .RatyComfino p span { display:inline; margin-bottom:0; }</style><script>
                    var comfino_script = document.createElement('script');
                    comfino_script.src = \"".( $SystemyRatalne['platnosc_comfino']['PLATNOSC_COMFINO_SANDBOX'] == '1' ? '//widget.craty.pl/comfino.min.js' : '//widget.comfino.pl/comfino.min.js' )."\";
                    comfino_script.async = true;
                    document.getElementsByTagName('head')[0].appendChild(comfino_script);
                    comfino_script.onload = function () {
                        ComfinoProductWidget.init({
                            widgetKey: '".$SystemyRatalne['platnosc_comfino']['PLATNOSC_COMFINO_WIDGET_KLUCZ']."',
                            priceSelector: '[itemprop=\"price\"]',
                            widgetTargetSelector: 'div.RatyComfino',
                            type: '".$SystemyRatalne['platnosc_comfino']['PLATNOSC_COMFINO_WIDGET_TYP']."',
                            offerType: '".$SystemyRatalne['platnosc_comfino']['PLATNOSC_COMFINO_PLATNOSC_TYP']."',
                            embedMethod: 'INSERT_INTO_LAST',
                            price: null,
                            priceObserverLevel: 0
                        });
                    };
                </script>";
                $Widget .= '<div class="RatyComfino" style="margin:10px 0;"></div>';

                $srodek->dodaj('__RATY_COMFINO', $Widget);
             } else {
                $srodek->dodaj('__COMFINO_PARAMETRY', '');
                $srodek->dodaj('__RATY_COMFINO', '');
             }

             // Jezeli jest wlaczony modul ratalny PayU
             if ( (isset($SystemyRatalne['platnosc_payu']) && count($SystemyRatalne['platnosc_payu']) > 0 && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_WLACZONE'] == 'tak') && ($Produkt->info['cena_brutto_bez_formatowania'] >= 300 && $Produkt->info['cena_brutto_bez_formatowania'] <= 20000) && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $srodek->dodaj('__PAYURATY_PARAMETRY', '300;50000');
                $srodek->dodaj('__RATY_PAYURATY', '');
             } else {
                $srodek->dodaj('__PAYURATY_PARAMETRY', '');
                $srodek->dodaj('__RATY_PAYURATY', '');
             }

             // Jezeli jest wlaczony modul ratalny PayU - REST
             if ( (isset($SystemyRatalne['platnosc_payu_rest']) && count($SystemyRatalne['platnosc_payu_rest']) > 0 && $SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_RATY_WLACZONE'] == 'tak') && ($Produkt->info['cena_brutto_bez_formatowania'] >= 300 && $Produkt->info['cena_brutto_bez_formatowania'] <= 20000) && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $srodek->dodaj('__PAYURATY_PARAMETRY', '300;50000');
                $srodek->dodaj('__RATY_PAYURATY', '');
             } else {
                $srodek->dodaj('__PAYURATY_PARAMETRY', '');
                $srodek->dodaj('__RATY_PAYURATY', '');
             }

             // Jezeli jest wlaczony modul ratalny iLeasing
             if ( isset($SystemyRatalne['platnosc_ileasing']) && count($SystemyRatalne['platnosc_ileasing']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $srodek->dodaj('__ILEASING_PARAMETRY', $SystemyRatalne['platnosc_ileasing']['PLATNOSC_ILEASING_PARTNERID'].';'.$Produkt->info['cena_netto_bez_formatowania'] );
                $srodek->dodaj('__RATY_ILEASING', '<div id="RatyIleasing" style="margin-bottom:10px;"><a onclick="PoliczRateiLeasing();" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/oblicz_rate_ileasing_produkt.png" alt="iLeasing" /></a></div>');
             } else {
                $srodek->dodaj('__ILEASING_PARAMETRY', '');
                $srodek->dodaj('__RATY_ILEASING', '');
             }

             // Jezeli jest wlaczony modul ratalny iRaty
             if ( isset($SystemyRatalne['platnosc_iraty']) && count($SystemyRatalne['platnosc_iraty']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $srodek->dodaj('__IRATY_PARAMETRY', $SystemyRatalne['platnosc_iraty']['PLATNOSC_IRATY_PARTNERID'].';'.$Produkt->info['cena_brutto_bez_formatowania'] );
                $srodek->dodaj('__RATY_IRATY', '<div id="RatyIraty" style="margin-bottom:10px;"><a onclick="PoliczRateiRaty();" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/oblicz_rate_iraty_produkt.png" alt="iRaty" /></a></div>');
             } else {
                $srodek->dodaj('__IRATY_PARAMETRY', '');
                $srodek->dodaj('__RATY_IRATY', '');
             }
             
             // Jezeli jest wlaczony modul LEASELINK
             if ( isset($SystemyRatalne['platnosc_leaselink']) && count($SystemyRatalne['platnosc_leaselink']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' && $Produkt->info['cena_brutto_bez_formatowania'] > $SystemyRatalne['platnosc_leaselink']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'] ) {
                if ( $SystemyRatalne['platnosc_leaselink']['PLATNOSC_LEASELINK_KALKULATOR'] == 'tak' ) {
                    if ( $SystemyRatalne['platnosc_leaselink']['PLATNOSC_LEASELINK_SANDBOX'] == '1' ) {
                        $Url = 'onlinetest.leaselink.pl';
                    } else {
                        $Url = 'online.leaselink.pl';
                    }
                    $link =  'https://'.$Url.'/RateCalculator/calculate?rate=999&amp;externalId='.$SystemyRatalne['platnosc_leaselink']['PLATNOSC_LEASELINK_EXTERNALID'].'&amp;tax='.$Produkt->info['stawka_vat'].'&amp;value='.$Produkt->info['cena_brutto_bez_formatowania'].'&amp;isNet=false&amp;productName='.rawurlencode($Produkt->info['nazwa']);
                    $srodek->dodaj('__LEASELINK_PARAMETRY', $SystemyRatalne['platnosc_leaselink']['PLATNOSC_LEASELINK_EXTERNALID'].';'.$Produkt->info['cena_brutto_bez_formatowania'] );
                    $srodek->dodaj('__RATY_LEASELINK', '<div id="Leaselink" style="margin-bottom:10px;"><a href="'.$link.'" target="_blank"><img src="'.KATALOG_ZDJEC . '/platnosci/leaselink-logo.svg" width="80px" height="20px" alt="Leaselink" /><span style="display:block;">Sprawdź ofertę leasingu dla firm</span></a></div>');
                } else {
                    $srodek->dodaj('__LEASELINK_PARAMETRY', '');
                    $srodek->dodaj('__RATY_LEASELINK', '');
                }
             
             } else {
                $srodek->dodaj('__LEASELINK_PARAMETRY', '');
                $srodek->dodaj('__RATY_LEASELINK', '');
             }

             // Jezeli jest wlaczona grafika Paypo
             if ( isset($SystemyRatalne['platnosc_paypo']) && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {
                $srodek->dodaj('__GRAFIKA_PAYPO', '<div style="margin-bottom:10px"><a onclick="InformacjePayPo()" style="cursor: pointer"><img src="'.KATALOG_ZDJEC . '/platnosci/paypo.png" alt="PayPo" /></a></div>');
             } else {
                $srodek->dodaj('__GRAFIKA_PAYPO', '');
             }                          

             // Jezeli jest wlaczony modul widgetu rat PayU
             if ( (isset($SystemyRatalne['platnosc_payu']) && count($SystemyRatalne['platnosc_payu']) > 0 && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_KALKULATOR'] == 'tak' && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_WLACZONE'] == 'tak') && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                //
                $srodek->dodaj('__PAYURATY_WIDGET', '
                <script src="https://static.payu.com/res/v2/widget-mini-installments.js"></script>
                <style>#RatyPayuWidget { display:none; } .RatyP span { display:inline-block !important; margin:0px !important; }</style>
                <div id="RatyPayuWidget">
                    <div style="margin-bottom:5px;display:block"><img src="'.KATALOG_ZDJEC . '/platnosci/raty_payu_small_grey.png" alt="Raty PayU" /></div>
                    <div class="RatyP"><p>Rata już od: <span id="installment-mini"></span> miesięcznie</p></div>
                </div>
                
                <script type="text/javascript">
                
                      var value = ' . $Produkt->info['cena_brutto_bez_formatowania'] . ';
                      if (value >= 300 && value <= 50000) {
                        var options = {
                          creditAmount: value, 
                          posId: \'' . $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_POS_ID'] . '\', 
                          key: \'' . substr((string)$SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_KEY_1'], 0, 2) . '\', 
                          showLongDescription: false
                        };
                        OpenPayU.Installments.miniInstallment(\'#installment-mini\', options)
                            .then(function(result) {
                                $(\'#RatyPayuWidget\').show(); 
                            });
                      } else {
                          $(\'#RatyPayuWidget\').hide(); 
                      } 
  
                </script>');
             }             

             // Jezeli jest wlaczony modul widgetu rat PayU - REST
             if ( (isset($SystemyRatalne['platnosc_payu_rest']) && count($SystemyRatalne['platnosc_payu_rest']) > 0 && $SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_RATY_KALKULATOR'] == 'tak' && $SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_RATY_WLACZONE'] == 'tak') && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                //
                $srodek->dodaj('__PAYURATY_WIDGET', '
                <script src="https://static.payu.com/res/v2/widget-mini-installments.js"></script>
                <style>#RatyPayuWidget { display:none; } .RatyP span { display:inline-block !important; margin:0px !important; }</style>
                <div id="RatyPayuWidget">
                    <div style="margin-bottom:5px;display:block"><img src="'.KATALOG_ZDJEC . '/platnosci/raty_payu_small_grey.png" alt="Raty PayU" /></div>
                    <div class="RatyP"><p>Rata już od: <span id="installment-mini"></span> miesięcznie</p></div>
                </div>
                
                <script type="text/javascript">
                
                      var value = ' . $Produkt->info['cena_brutto_bez_formatowania'] . ';
                      if (value >= 300 && value <= 50000) {
                        var options = {
                          creditAmount: value, 
                          posId: \'' . $SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_POS_ID'] . '\', 
                          key: \'' . substr((string)$SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_OAUTH_SECRET'], 0, 2) . '\', 
                          showLongDescription: false
                        };
                        OpenPayU.Installments.miniInstallment(\'#installment-mini\', options)
                            .then(function(result) {
                                $(\'#RatyPayuWidget\').show(); 
                            });
                      } else {
                          $(\'#RatyPayuWidget\').hide(); 
                      } 
  
                </script>');
             }             

             // Jezeli jest wlaczony modul ratalny Lukas
             if ( isset($SystemyRatalne['platnosc_lukas']) && count($SystemyRatalne['platnosc_lukas']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                $lukas_ok = true;

                if ($Produkt->info['cena_brutto_bez_formatowania'] < $SystemyRatalne['platnosc_lukas']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'] || $Produkt->info['cena_brutto_bez_formatowania'] > $SystemyRatalne['platnosc_lukas']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] ) {
                        $lukas_ok = false;
                }

                $wykluczoneKategorie = explode(',', (string)$SystemyRatalne['platnosc_lukas']['PLATNOSC_LUKAS_KATEGORIE']);
                for($i=0, $x=sizeof($wykluczoneKategorie); $i<$x; $i++){
                    if ( $wykluczoneKategorie[$i] == $Produkt->info['id_kategorii'] ) {
                        $lukas_ok = false;
                    }
                }
                if ( $lukas_ok ) {
                    $srodek->dodaj('__LUKAS_PARAMETRY', $SystemyRatalne['platnosc_lukas']['PLATNOSC_LUKAS_NUMER_SKLEPU'].';'.$SystemyRatalne['platnosc_lukas']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'].';'.$SystemyRatalne['platnosc_lukas']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] );
                    $srodek->dodaj('__RATY_LUKAS', '<div id="RatyLukas" style="margin-bottom:10px;"><a onclick="PoliczRateLukas();" style="cursor: pointer;"><img src="https://ewniosek.credit-agricole.pl/eWniosek/button/img.png?creditAmount='.$Produkt->info['cena_brutto_bez_formatowania'].'&posId='.$SystemyRatalne['platnosc_lukas']['PLATNOSC_LUKAS_NUMER_SKLEPU'].'&imgType=1" alt="Raty" /></a></div>');
                } else {
                    $srodek->dodaj('__LUKAS_PARAMETRY', '');
                    $srodek->dodaj('__RATY_LUKAS', '');
                }
             } else {
                $srodek->dodaj('__LUKAS_PARAMETRY', '');
                $srodek->dodaj('__RATY_LUKAS', '');
             }

             // Jezeli jest wlaczony modul ratalny BGZ
             if ( isset($SystemyRatalne['platnosc_bgz']) && count($SystemyRatalne['platnosc_bgz']) > 0 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN') {
                if ($Produkt->info['cena_brutto_bez_formatowania'] >= $SystemyRatalne['platnosc_bgz']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN']) {
                    $srodek->dodaj('__BGZRATY_PARAMETRY', $SystemyRatalne['platnosc_bgz']['PLATNOSC_BGZ_NUMER_SKLEPU'].';'.$SystemyRatalne['platnosc_bgz']['PLATNOSC_BGZ_NUMER_KREDYTU'].';'.$SystemyRatalne['platnosc_bgz']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'].';'.$SystemyRatalne['platnosc_bgz']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] );
                    $srodek->dodaj('__RATY_BGZ', '<div id="RatyBgz" style="margin-bottom:10px;"><a onclick="PoliczRateBgz();" style="cursor: pointer;"><img src="'.KATALOG_ZDJEC . '/platnosci/oblicz_rate_bgz_produkt.png" alt="BNP Paribas" /></a></div>');
                } else {
                    $srodek->dodaj('__BGZRATY_PARAMETRY', '');
                    $srodek->dodaj('__RATY_BGZ', '');
                }
             } else {
                $srodek->dodaj('__BGZRATY_PARAMETRY', '');
                $srodek->dodaj('__RATY_BGZ', '');
             }

             //
        } else {
             //
             $srodek->dodaj('__INPUT_ILOSC', '');
             $srodek->dodaj('__PRZYCISK_KUP', '<span class="Info">' . $GLOBALS['tlumacz']['PRODUKT_NIE_MOZNA_KUPIC'] . '</span>');
             $srodek->dodaj('__ZAKUP_PRZEZ_ALLEGRO', '');
             $srodek->dodaj('__INFO_NIEDOSTEPNY', '<span class="Info">' . $GLOBALS['tlumacz']['PRODUKT_NIE_MOZNA_KUPIC'] . '</span>');
             $srodek->dodaj('__RATY_SANTANDER', '');
             $srodek->dodaj('__SANTANDER_PARAMETRY', '');
             $srodek->dodaj('__RATY_LUKAS', '');
             $srodek->dodaj('__LUKAS_PARAMETRY', '');
             $srodek->dodaj('__RATY_MBANK', '');
             $srodek->dodaj('__RATY_TPAY', '');
             $srodek->dodaj('__RATY_COMFINO', '');
             $srodek->dodaj('__MBANK_PARAMETRY', '');
             $srodek->dodaj('__RATY_PAYURATY', '');
             $srodek->dodaj('__PAYURATY_PARAMETRY', '');
             $srodek->dodaj('__RATY_BGZ', '');
             $srodek->dodaj('__BGZRATY_PARAMETRY', '');
             $srodek->dodaj('__RATY_ILEASING', '');
             $srodek->dodaj('__ILEASING_PARAMETRY', '');
             $srodek->dodaj('__RATY_IRATY', '');
             $srodek->dodaj('__IRATY_PARAMETRY', '');
             $srodek->dodaj('__GRAFIKA_PAYPO', '');
             $srodek->dodaj('__LEASELINK_PARAMETRY', '');
             $srodek->dodaj('__RATY_LEASELINK', '');
             //
        }
    }
    
    // jezeli kupowanie tylko za PUNKTY to nie ma rat
    if ( $Produkt->info['tylko_za_punkty'] == 'tak' ) {
         //
         $srodek->dodaj('__RATY_SANTANDER', '');
         $srodek->dodaj('__SANTANDER_PARAMETRY', '');
         $srodek->dodaj('__RATY_LUKAS', '');
         $srodek->dodaj('__LUKAS_PARAMETRY', '');
         $srodek->dodaj('__RATY_MBANK', '');
         $srodek->dodaj('__RATY_TPAY', '');
         $srodek->dodaj('__RATY_COMFINO', '');
         $srodek->dodaj('__MBANK_PARAMETRY', '');
         $srodek->dodaj('__RATY_PAYURATY', '');
         $srodek->dodaj('__PAYURATY_PARAMETRY', '');      
         $srodek->dodaj('__RATY_BGZ', '');
         $srodek->dodaj('__BGZRATY_PARAMETRY', '');
         $srodek->dodaj('__RATY_ILEASING', '');
         $srodek->dodaj('__ILEASING_PARAMETRY', '');
         $srodek->dodaj('__RATY_IRATY', '');
         $srodek->dodaj('__IRATY_PARAMETRY', '');
         $srodek->dodaj('__GRAFIKA_PAYPO', '');
         $srodek->dodaj('__LEASELINK_PARAMETRY', '');
         $srodek->dodaj('__RATY_LEASELINK', '');
         //
    }
    
    //
    // css do kupowania - pokazuje albo przycisk kupowania albo info o tym ze nie mozna kupic
    if ( $Produkt->zakupy['mozliwe_kupowanie'] == 'tak' ) {
        $srodek->dodaj('__CSS_KOSZYK','');
        $srodek->dodaj('__CSS_INFO_KOSZYK','style="display:none"');
      } else {
        $srodek->dodaj('__CSS_KOSZYK','style="display:none"');
        $srodek->dodaj('__CSS_INFO_KOSZYK','');
    }
    
    $srodek->dodaj('__CSS_INFO_POWIADOMIENIE','');
    // jezeli produkt jest za punkty a klient nie jest zalogowany - nie moze kupic produktu i info ma sie nie wyswietlac
    if ( $Produkt->info['tylko_za_punkty'] == 'tak' && ((!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) || $_SESSION['gosc'] == '1') ) {
         //
         $srodek->dodaj('__INFO_NIEDOSTEPNY', '<span class="Info">' . $GLOBALS['tlumacz']['PRODUKT_TYLKO_ZALOGOWANI'] . '</span>');
         $srodek->dodaj('__CSS_INFO_POWIADOMIENIE','style="display:none"');
         //
    }
    
    // jezeli jest w ogole w sklepie wylaczone kupowanie - sklep jako katalog produktow
    if ( PRODUKT_KUPOWANIE_STATUS == 'nie' ) {
         $srodek->dodaj('__CSS_INFO_POWIADOMIENIE','style="display:none"');
    }
        
    // przycisk do schowka
    $srodek->dodaj('__PRZYCISK_SCHOWEK', '');
    $srodek->dodaj('__CSS_SCHOWKA', '');
    if (PRODUKT_SCHOWEK_STATUS == 'tak') {
        if ($GLOBALS['schowekKlienta']->SprawdzCzyDodanyDoSchowka($Produkt->info['id'])) { 
            $srodek->dodaj('__CSS_SCHOWKA','class="KartaSchowekDodany"');
            $srodek->dodaj('__PRZYCISK_SCHOWEK', '<span class="ToolTip" onclick="DoSchowka(' . $Produkt->info['id'] . ')" title="' . $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_SCHOWKA'] . '">' . $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_SCHOWKA'] . '</span>');
        } else {
            $srodek->dodaj('__CSS_SCHOWKA','class="KartaSchowekDoDodania"');
            $srodek->dodaj('__PRZYCISK_SCHOWEK', '<span class="ToolTip" onclick="DoSchowka(' . $Produkt->info['id'] . ')" title="' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_SCHOWKA'] . '">' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_SCHOWKA'] . '</span>');
        }
    }
    
    // dodatkowe linki produktu    
    $srodek->dodaj('__CSS_LINKI_PRODUKTU', '');
    $jestLink = false;
    
    // zapytanie o produkt
    $srodek->dodaj('__LINK_ZAPYTANIA_O_PRODUKT', '');
    if ( isset($Wyglad->Formularze[2]) && KARTA_PRODUKTU_LINK_ZAPYTAJ_O_PRODUKT == 'tak' ) {
         $srodek->dodaj('__LINK_ZAPYTANIA_O_PRODUKT', '<a class="ZapytanieProdukt" href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( $Wyglad->Formularze[2], 2, 'formularz' ) . '/produkt=' . $IdProduktuBezCech . '" rel="nofollow">' . $GLOBALS['tlumacz']['ZAPYTAJ_O_PRODUKT'] . '</a>');  
         $jestLink = true;
    }
    
    // polec produkt znajomemu
    $srodek->dodaj('__LINK_POLEC_PRODUKT', '');
    if ( isset($Wyglad->Formularze[3]) && KARTA_PRODUKTU_LINK_POLEC_ZNAJOMEMU == 'tak' ) {
         $srodek->dodaj('__LINK_POLEC_PRODUKT', '<a class="PolecProdukt" href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( $Wyglad->Formularze[3], 3, 'formularz' ) . '/produkt=' . $IdProduktuBezCech . '" rel="nofollow">' . $GLOBALS['tlumacz']['POLEC_PRODUKT'] . '</a>');
         $jestLink = true;
    }

    // link negocjacji ceny
    $srodek->dodaj('__LINK_NEGOCJUJ_CENE', '');
    if ( isset($Wyglad->Formularze[4]) && $Produkt->info['negocjacja'] == 'tak' ) {
         $srodek->dodaj('__LINK_NEGOCJUJ_CENE', '<a class="NegocjujCene" href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( $Wyglad->Formularze[4], 4, 'formularz' ) . '/produkt=' . $IdProduktuBezCech . '" rel="nofollow">' . $GLOBALS['tlumacz']['NEGOCUJ_CENE'] . '</a>');   
         $jestLink = true;
    }
    
    // link karty produktu pdf
    $srodek->dodaj('__LINK_PRODUKT_PDF', '');
    if ( KARTA_PRODUKTU_LINK_SPECYFIKACJA_PDF == 'tak' ) {
         $srodek->dodaj('__LINK_PRODUKT_PDF', '<a class="LinkPDF" href="' . Seo::link_SEO( $Produkt->info['nazwa_seo'], $Produkt->info['id'], 'produkt_pdf') . '" rel="nofollow">' . $GLOBALS['tlumacz']['KARTA_PRODUKTU_PDF'] . '</a>');   
         $jestLink = true;
    }
    
    if ( $jestLink == false ) {
         $srodek->dodaj('__CSS_LINKI_PRODUKTU', 'style="display:none"');
    }
    
    unset($jestLink);
    
    // zestaw produktow
    $srodek->dodaj('__ZESTAW_TANIEJ', '');
    if ( $Produkt->info['zestaw'] == 'tak' ) {
         //
         $srodek->dodaj('__ZESTAW_TANIEJ', $Produkt->zestawTaniej);
         //
    }

    // ile osob kupilo dany produkt
    $srodek->dodaj('__ZAKUPY_HISTORIA', '');
    if ( KARTA_PRODUKTU_ZAKUPY_HISTORIA == 'tak' ) {

        list($osoba,$kupno )= Funkcje::OdmianaPrzypadkowOsoby($Produkt->iloscKupionych);
        list($produkty )= Funkcje::OdmianaPrzypadkowProdukty($Produkt->iloscKupionychSztuk);

        $srodek->dodaj('__ZAKUPY_HISTORIA', $Produkt->iloscKupionych . ' ' . $osoba.' '.$kupno.' ' . $Produkt->iloscKupionychSztuk . ' ' . $produkty );     

    }
    
    // historia ceny
    $srodek->dodaj('__TEKST_HISTORIA_CENY', '');
    $srodek->dodaj('__CSS_HISTORIA_CENY', '');
    //
    if ( HISTORIA_CEN == 'tak' && $Produkt->info['tylko_za_punkty'] == 'nie' ) {
         //
         if ( $Produkt->info['cena_najnizsza_30_dni'] != '' ) {
              //
              $info_historia = $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT'];
              //
              $srodek->dodaj('__CSS_HISTORIA_CENY', 'style="display:none"');
              //
              if ( HISTORIA_CEN_CENY_KATALOGOWE == 'tak' && (float)$Produkt->info['cena_katalogowa_bez_formatowania'] > 0 ) {
                   $srodek->dodaj('__CSS_HISTORIA_CENY', '');
              }
              //
              if ( $Produkt->info['produkt_dnia'] == 'tak' || (float)$Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) {
                   $srodek->dodaj('__CSS_HISTORIA_CENY', '');
                   $info_historia = $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT_PROMOCJA'];
              }
              //
              if ( HISTORIA_CEN_PROMOCJE == 'nie' ) {
                  //
                  $srodek->dodaj('__CSS_HISTORIA_CENY', '');
                  //
              }
              //
              $srodek->dodaj('__TEKST_HISTORIA_CENY', str_replace('{DATA}', $Produkt->info['cena_najnizsza_30_dni_data'], str_replace('{CENA}', $Produkt->info['cena_najnizsza_30_dni'], $info_historia)));
              //
              unset($info_historia);
              //
         } else {
              //
              if ( HISTORIA_CEN_PROMOCJE == 'tak' || HISTORIA_CEN_CENY_KATALOGOWE == 'tak' ) {
                   //
                   $srodek->dodaj('__CSS_HISTORIA_CENY', 'style="display:none"');
                   //
                   if ( HISTORIA_CEN_PROMOCJE == 'tak' && ($Produkt->info['produkt_dnia'] == 'tak' || (float)$Produkt->info['cena_poprzednia_bez_formatowania'] > 0) ) {
                        $srodek->dodaj('__CSS_HISTORIA_CENY', '');
                   }
                   //
                   if ( HISTORIA_CEN_CENY_KATALOGOWE == 'tak' && (float)$Produkt->info['cena_katalogowa_bez_formatowania'] > 0 ) {
                        $srodek->dodaj('__CSS_HISTORIA_CENY', '');
                   }
                    //
              }
              //
              if ( HISTORIA_CEN_PROMOCJE == 'nie' ) {
                  //
                  $srodek->dodaj('__CSS_HISTORIA_CENY', '');
                  //
              }
              //
              $srodek->dodaj('__TEKST_HISTORIA_CENY', $GLOBALS['tlumacz']['HISTORIA_CENY_BRAK']);
              //
         }
         //
    } else {
         //
         $srodek->dodaj('__CSS_HISTORIA_CENY', 'style="display:none"');
         //
    }

    // integracja dla edrone
    $tpl->dodaj('__EDRONE', IntegracjeZewnetrzne::EdroneProdukt( $Produkt ));
    
    // kod fb dla komentarzy
    $srodek->dodaj('__FB_KOD_GLOWNY', '');
    if ( INTEGRACJA_FB_OPINIE_WLACZONY == 'tak' ) {
        //
        $srodek->dodaj('__FB_KOD_GLOWNY', '<div id="fb-root"></div><script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "https://connect.facebook.net/pl_PL/all.js#xfbml=1&version=v2.8"; fjs.parentNode.insertBefore(js, fjs); }(document, \'script\', \'facebook-jssdk\'));</script>');
        //
    }    

    $srodek->dodaj('__ADRES_STRONY_PRODUKTU', urlencode(ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'])); 

    // zakladki 
    // opis produktu
    $srodek->dodaj('__OPIS_PRODUKTU', $Produkt->info['opis']);    
    
    // filmy youtube - ciag do javascript
    $CiagJs = '';
    foreach ( $Produkt->Youtube as $Film ) {
        $CiagJs .= $Film['id_film'] . "," . $Film['film'] . "," . $Film['szerokosc'] . "," . $Film['wysokosc'] . ";";
    }
    $srodek->dodaj('__KOD_YOUTUBE', '');   
    //
    if ( count($Produkt->Youtube) > 0 ) {
         //
         $srodek->dodaj('__KOD_YOUTUBE', 'var youtube = "' . substr((string)$CiagJs, 0, -1) . '"; PokazYouTube();');
         //
    }
    unset($CiagJs);
    
    // filmy flv - ciag do javascript
    $CiagJs = '';
    foreach ( $Produkt->FilmyFlv as $Film ) {
        $CiagJs .= $Film['id_film'] . "," . strrev($Film['film']) . "," . $Film['szerokosc'] . "," . $Film['wysokosc'] .";";
    }
    $srodek->dodaj('__KOD_FLV', '');  
    //
    if ( count($Produkt->FilmyFlv) > 0 ) {
         //
         $srodek->dodaj('__KOD_FLV', 'var flv = "' . substr((string)$CiagJs, 0, -1) . '"; PokazFlv();');
         //
    }
    unset($CiagJs);   

    // pliki mp3 - ciag do javascript
    $CiagJs = '';
    foreach ( $Produkt->Mp3 as $Mp3 ) {
        $CiagJs .= $Mp3['id_mp3'] . "," . strrev($Mp3['plik']) . ";";
    }
    $srodek->dodaj('__KOD_MP3', '');  
    //
    if ( count($Produkt->Mp3) > 0 ) {
         //
         $srodek->dodaj('__KOD_MP3', 'var mp3 = "' . substr((string)$CiagJs, 0, -1) . '"; PokazMp3();');
         //
    }
    unset($CiagJs);

    // funkcja cech
    $srodek->dodaj('__FUNKCJA_CECH', ''); 
    //
    if ( $Produkt->cechyIlosc > 0 ) {
         //
         $srodek->dodaj('__FUNKCJA_CECH', "ZmienCeche('" . $Produkt->idUnikat . $Produkt->info['id'] . "', 1);");
         //
    }
    
    // info wysylka
    $srodek->dodaj('__INFO_TIP_WYSYLKA', ''); 
    //
    if ( KARTA_PRODUKTU_KOSZTY_WYSYLKI == 'tak' ) {
         //
         //$srodek->dodaj('__INFO_TIP_WYSYLKA', "PokazTip('#InfoOpisWysylka');");
         $srodek->dodaj('__INFO_TIP_WYSYLKA', "PokazTipWysylki('#InfoOpisWysylka');");
         //
    }
    
    // jezeli nikt nie napisal recenzji wyswietli informacje
    if ( $Produkt->recenzjeSrednia['ilosc_glosow'] == 0 ) {
         $srodek->dodaj('__INFO_O_BRAKU_RECENZJI',$GLOBALS['tlumacz']['RECENZJA_BADZ_PIERWSZY']);
    }
    
    // system punktow i recenzje    
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && Punkty::PunktyAktywneDlaKlienta() ) {
        $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', str_replace('{ILOSC_PUNKTOW}', (string)SYSTEM_PUNKTOW_PUNKTY_RECENZJE, (string)$GLOBALS['tlumacz']['PUNKTY_RECENZJE']));
    }    

    // informacja o systemie punktow
    $srodek->dodaj('__INFO_O_PUNKTACH_PRODUKTU', '');
    $srodek->dodaj('__CSS_INFO_PUNKTY', 'style="display:none"');
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_NALICZANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() && $Produkt->info['tylko_za_punkty'] == 'nie' ) {
        //
        if ( ($Produkt->ikonki['promocja'] == '1' && SYSTEM_PUNKTOW_PROMOCJE == 'tak') || $Produkt->ikonki['promocja'] == '0' ) {
              //
              if (( CENY_DLA_WSZYSTKICH == 'tak' || ( CENY_DLA_WSZYSTKICH == 'nie' && ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) ) ) && UKRYJ_CENY == 'tak' && $Produkt->info['jest_cena'] == 'tak' ) {
                  //
                  $iloscPunktow = ceil(($Produkt->info['cena_brutto_bez_formatowania']/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                  $srodek->dodaj('__INFO_O_PUNKTACH_PRODUKTU', str_replace('{ILOSC_PUNKTOW}', '<span>' . $iloscPunktow . '</span>', (string)$GLOBALS['tlumacz']['PUNKTY_PRODUKT']));
                  unset($iloscPunktow);  
                  //
                  $srodek->dodaj('__CSS_INFO_PUNKTY', '');
                  //
              }
              //
        }
        //
        // jezeli jest wylaczone naliczanie pkt za produkt
        if ( $Produkt->info['pkt_naliczanie'] == 'nie' ) {
             $srodek->dodaj('__CSS_INFO_PUNKTY', 'style="display:none"');
        }
        //
    }    

    // opinie facebook
    $srodek->dodaj('__KOMENTARZE_FACEBOOK', '');
    if ( INTEGRACJA_FB_OPINIE_WLACZONY == 'tak' ) {
        //
        $KomentarzeFb = '<br /><div class="fb-comments" data-href="' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '" data-width="100%" data-numposts="' . INTEGRACJA_FB_OPINIE_ILOSC_POSTOW . '"></div>';
        $srodek->dodaj('__KOMENTARZE_FACEBOOK', $KomentarzeFb);
        unset($KomentarzeFb);
        //    
    }
    
    // integracja TRUSTISTO
    $srodek->dodaj('__TRUSTISTO_KOD_PRODUKT', IntegracjeZewnetrzne::TrustistoProdukt( $Produkt ));

    // integracja z DomodiPixel
    $srodek->dodaj('__DOMODI_PIXEL', IntegracjeZewnetrzne::DomodiPixelProdukt( $Produkt ));

    // integracja z Wp Pixel
    $srodek->dodaj('__WP_PIXEL_KOD', IntegracjeZewnetrzne::WpPixelProdukt( $Produkt ));
    
    // integracja Trustmate.io
    $srodek->dodaj('__TRUSTMATE_KOD_PRODUKT_BADGER', IntegracjeZewnetrzne::TrusmateProdukt( $Produkt, 'badger' ));
    $srodek->dodaj('__TRUSTMATE_KOD_PRODUKT_FERRET', IntegracjeZewnetrzne::TrusmateProdukt( $Produkt, 'ferret' ));
    $srodek->dodaj('__TRUSTMATE_KOD_PRODUKT_HORNET', IntegracjeZewnetrzne::TrusmateProdukt( $Produkt, 'hornet' ));

    // integracja z Google remarketing dynamiczny i Google Analytics
    $wynikGoogle = IntegracjeZewnetrzne::GoogleAnalyticsRemarketingProdukt( $Produkt, $WyswietlanaKategoriaProduktu );
    $tpl->dodaj('__GOOGLE_KONWERSJA', $wynikGoogle['konwersja']);
    $tpl->dodaj('__GOOGLE_ANALYTICS', $wynikGoogle['analytics']);
    unset($wynikGoogle);
    
    // integracja z KLAVIYO
    $srodek->dodaj('__KLAVIYO_PRODUKT', IntegracjeZewnetrzne::KlaviyoProdukt( $Produkt ));
    
    // integracja Ceneo Zaufane Opinie
    $srodek->dodaj('__CENEO_ZAUFANE_OPINIE_RECENZJE', '');
    //
    if ( INTEGRACJA_CENEO_OPINIE_WLACZONY == 'tak' && INTEGRACJA_CENEO_OPINIE_ID != '' && INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW == 'tak' ) {
         //
         $kodCeneo = '<script>$(document).ready(function() {';
         //
         if ( INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE == 'pod zakładkami' ) {
              //
              $kodCeneo .= '$(\'#ZakladkiProduktu\').after(\'<div style="padding:20px 0 20px 0"><div id="ceneo_widget_product_reviews"></div>\')';
              //
         }
         if ( INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE == 'nad recenzjami produktu sklepu' ) {
              //
              $kodCeneo .= '$(\'.tz_recenzje\').prepend(\'<div style="padding:20px 0 20px 0"><div id="ceneo_widget_product_reviews"></div></div>\')';
              //
         }     
         if ( INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE == 'pod recenzjami produktu sklepu' ) {
              //
              $kodCeneo .= '$(\'.tz_recenzje\').append(\'<div style="padding:20px 0 20px 0"><div id="ceneo_widget_product_reviews"></div></div>\')';
              //
         }               
         //
         $kodCeneo .= '})</script>';
         //
         $srodek->dodaj('__CENEO_ZAUFANE_OPINIE_RECENZJE', $kodCeneo);
         //
         unset($kodCeneo);
         //
    }
    
    // akcesoria dodatkowe
    $zapytanie = Produkty::SqlProduktyAkcesoriaDodatkowe( $Produkt->info['id'] );
    $sql = $GLOBALS['db']->open_query($zapytanie);    
    //
    $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
    $srodek->parametr('AkcesoriaDodatkoweIlosc', $IloscProduktow); 
    //
    ob_start();
    
    if (in_array( 'listing_akcesoria_dodatkowe.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_akcesoria_dodatkowe.php');
      } else {
        require('listingi/listing_akcesoria_dodatkowe.php');
    }

    $ListaDodatkowychAkcesorii = ob_get_contents();
    ob_end_clean(); 
    
    $srodek->dodaj('__LISTING_AKCESORIA', $ListaDodatkowychAkcesorii); 
    unset($ListaDodatkowychAkcesorii);
    
    // produkty powiazane
    $zapytanie = Produkty::SqlProduktyPowiazane( $Produkt->info['id'] );
    $sql = $GLOBALS['db']->open_query($zapytanie);    
    //
    $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
    $srodek->parametr('ProduktyPowiazaneIlosc', $IloscProduktow); 
    //
    ob_start();
    
    if (in_array( 'listing_produkty_powiazane.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_powiazane.php');
      } else {
        require('listingi/listing_produkty_powiazane.php');
    }

    $ListaProduktowPowiazanych = ob_get_contents();
    ob_end_clean(); 
    
    $srodek->dodaj('__LISTING_PRODUKTY_POWIAZANE', $ListaProduktowPowiazanych); 
    unset($ListaProduktowPowiazanych);  

    // inne warianty
    ob_start();
    
    if (in_array( 'listing_produkty_inne_warianty.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_inne_warianty.php');
      } else {
        require('listingi/listing_produkty_inne_warianty.php');
    }

    $ListaProduktowInneWarianty = ob_get_contents();
    ob_end_clean(); 
    
    $srodek->dodaj('__LISTING_INNE_WARIANTY_PRODUKTU', $ListaProduktowInneWarianty); 
    unset($ListaProduktowInneWarianty);   

    // dodatkowe pola opisowe
    $PolaTekstowe = '';
    foreach ( $Produkt->dodatkowePolaTekstowe as $TxtPole ) {
        //
        $PolaTekstowe .= '<div class="PoleTekstowe"><span id="txt_' . $TxtPole['id_pola'] . '" ' . ((trim((string)$TxtPole['opis']) != '') ? 'class="TxtOpis"' : '') . '><label class="formSpan" for="pole_txt_' . $TxtPole['id_pola'] . '">' . $TxtPole['nazwa'] . (($TxtPole['wymagane']== 1) ? ' <em class="required" id="em_'.uniqid().'"></em>': '') . '</label></span><div>';
        //
        switch( $TxtPole['typ'] ) {
            case 'input': $PolaTekstowe .= '<input type="text" id="pole_txt_' . $TxtPole['id_pola'] . '" name="pole_txt_' . $TxtPole['id_pola'] . '" class="UsunTekst" value="" placeholder="'.$TxtPole['domyslny'] . '" data-text="" size="30" autocomplete="off" ' . (($TxtPole['wymagane']== 1) ? 'data-required="1"': 'data-required="0"') . ' />'; break;
            case 'textarea': $PolaTekstowe .= '<textarea id="pole_txt_' . $TxtPole['id_pola'] . '" name="pole_txt_' . $TxtPole['id_pola'] . '" rows="4" class="UsunTekst" cols="25" data-text=""  ' . (($TxtPole['wymagane']== 1) ? 'data-required="1"': 'data-required="0"') . ' placeholder="'.$TxtPole['domyslny'].'" ></textarea>'; break;
            case 'plik': $PolaTekstowe .= '<input type="hidden" id="pole_txt_' . $TxtPole['id_pola'] . '" name="pole_txt_' . $TxtPole['id_pola'] . '" value="" ' . (($TxtPole['wymagane']== 1) ? 'data-required="1"': 'data-required="0"') . ' />
                                           <input type="file" class="wgraniePliku" id="plik_' . $TxtPole['id_pola'] . '" name="plik_' . $TxtPole['id_pola'] . '" />
                                           <div id="wynik_plik_' . $TxtPole['id_pola'] . '"></div>'; break;
            case 'data': $PolaTekstowe .= '<input type="text" id="pole_txt_' . $TxtPole['id_pola'] . '" name="pole_txt_' . $TxtPole['id_pola'] . '" value="" data-text="" class="datepicker" placeholder="'.date('d-m-Y',time()).'" ' . (($TxtPole['wymagane']== 1) ? 'data-required="1"': 'data-required="0"') . ' />'; break;

        }  
        //
        $PolaTekstowe .= '</div></div>';
        //
    }
    $srodek->dodaj('__POLA_TEKSTOWE', $PolaTekstowe);
    //
    if ( $PolaTekstowe != '' ) {
         $srodek->dodaj('__PLIK_FORMULARZA', 'inne/wgranieForm.php?tok=' . Sesje::Token());
         $srodek->dodaj('__TRYB_FORMULARZA', 'enctype="multipart/form-data"');
       } else {
         $srodek->dodaj('__PLIK_FORMULARZA', '/');
         $srodek->dodaj('__TRYB_FORMULARZA', '');       
    }
    unset($PolaTekstowe);
    
    // tagi
    $srodek->dodaj('__TAGI', '');  
    $TagiLinki = '';
    //
    if ( KARTA_PRODUKTU_TAGI == 'tak' ) {
         //
         $TagiProduktu = $Produkt->ProduktTagiLinki();
         
         //
         if ( count($TagiProduktu) > 0 ) {
              //
              $TagiLinki = '<ul>';
              //
              foreach ( $TagiProduktu as $Tag ) {
                  //
                  $TagiLinki .= '<li><a href="wyszukiwanie-' . urlencode($Tag) . '.html">' . $Tag . '</a></li>';
                  //
              }
              //
              $TagiLinki .= '</ul>';
              //
              $srodek->dodaj('__TAGI', $TagiLinki);  
              //
         }
         //
    }
    //
    define('TAGI_PRODUKTU', (($TagiLinki != '') ? 'tak' : 'nie'));
    //    
    
    $srodek->dodaj('__FUNKCJA_ZEGAR_PROMOCJI', '');
    $srodek->dodaj('__CSS_ZEGAR_PROMOCJI', 'style="display:none"');
    // zegar dla promocji i produktu dnia
    if ( $Produkt->info['produkt_dnia'] == 'tak' || ( $Produkt->ikonki['promocja'] == 1 && $Produkt->ikonki['promocja_data_do'] > time() && $Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) ) {
         //
         // jezeli produkt jest produktem dnia
         if ( $Produkt->info['produkt_dnia'] == 'tak' ) {
              //
              $IloscSekund = FunkcjeWlasnePHP::my_strtotime(date('Y-m-d', time()) . ' 23:59:59') - time();                             
              //
         }
         // jezeli jest o promocja czasowa
         if ( $Produkt->ikonki['promocja'] == 1 && $Produkt->ikonki['promocja_data_do'] > time() ) {
              //
              $IloscSekund = ($Produkt->ikonki['promocja_data_do'] - time());    
              //
         }
         //
         $srodek->dodaj('__FUNKCJA_ZEGAR_PROMOCJI', Wyglad::PrzegladarkaJavaScript( 'odliczaj("sekundy_karta_' . $Produkt->info['id'] . '",' . $IloscSekund . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\')' ));     
         $srodek->dodaj('__CSS_ZEGAR_PROMOCJI', '');
         //
    }
    
    $srodek->dodaj('__FUNKCJA_ZEGAR_CZASU_WYSYLKI', '');
    $srodek->dodaj('__CSS_ZEGAR_CZASU_WYSYLKI', 'style="display:none"');    
    $srodek->dodaj('__CSS_ZEGAR_CZASU_WYSYLKI_NIEAKTYWNY', 'style="display:none"');    
    $srodek->dodaj('__MAKSYMALNA_GODZINA_WYSYLKI', ''); 
    $srodek->dodaj('__ZEGAR_CZASU_WYSYLKI_STATUS', '0');
    
    // zegar odliczania czasu wysylki
    if ( PRODUKT_ZEGAR_WYSYLKI == 'tak' ) {
         //
         $aktualny_czas = time();
         $dzien = (int)date('N', $aktualny_czas);  
         $godz = PRODUKT_ZEGAR_WYSYLKI_GODZINA;
         $czas = strtotime(date('Y-m-d ' . $godz . ':00')) - $aktualny_czas;
         $ilosc_sekund = (float)$czas;
             
         $wyswietl_zegar = true;
         //
         // sprawdzanie magazynu
         if ( (float)$Produkt->info['ilosc'] <= 0 && PRODUKT_ZEGAR_WYSYLKI_STAN_MAGAZYNOWY == 'tak' ) {
               $wyswietl_zegar = false;
         }

         // sprawdzanie dostepnosci
         if ( $Produkt->info['id_dostepnosci'] == '99999' ) {
              $id_dostepnosci = $Produkt->PokazIdDostepnosciAutomatycznych($Produkt->info['ilosc']);
         } else {
              $id_dostepnosci = $Produkt->info['id_dostepnosci'];
         }                  
         //                  
         if ( !in_array((int)$id_dostepnosci, (array)explode(',', (string)PRODUKT_ZEGAR_WYSYLKI_DOSTEPNOSCI)) ) {   
              $wyswietl_zegar = false;
         }
         unset($id_dostepnosci);
        
         // sprawdzanie czasu wysylki
         if ( !in_array((int)$Produkt->info['id_czasu_wysylki'], (array)explode(',', (string)PRODUKT_ZEGAR_CZAS_WYSYLKI)) ) {   
              $wyswietl_zegar = false;
         }

         if ( $ilosc_sekund > 0 && !in_array(date('d.m'), (array)explode(',', (string)PRODUKT_ZEGAR_WYSYLKI_DATY_WYKLUCZONE)) && in_array($dzien, (array)explode(',', (string)PRODUKT_ZEGAR_DNI)) ) { 
           
             $srodek->dodaj('__MAKSYMALNA_GODZINA_WYSYLKI', PRODUKT_ZEGAR_WYSYLKI_GODZINA); 
             $srodek->dodaj('__FUNKCJA_ZEGAR_CZASU_WYSYLKI', Wyglad::PrzegladarkaJavaScript( 'odliczaj_zegar_czas_wysylki("sekundy_czas_' . $Produkt->info['id'] . '",' . $ilosc_sekund . ',\'\',\'\')' ));
             $srodek->dodaj('__ZEGAR_CZASU_WYSYLKI_STATUS', '1');
             
             if ( $wyswietl_zegar == true && $Produkt->zakupy['mozliwe_kupowanie'] == 'tak' ) {
                  //             
                  $srodek->dodaj('__CSS_ZEGAR_CZASU_WYSYLKI', '');                          
                  //
             }                  

         } else {
           
            if ( PRODUKT_ZEGAR_NIEAKTYWNY == 'tak' ) {
                 //            
                 if ( $wyswietl_zegar == true ) {
                      //
                      $srodek->dodaj('__CSS_ZEGAR_CZASU_WYSYLKI_NIEAKTYWNY', '');                          
                      //
                 }
                 //
            }
           
         }
         
         unset($aktualny_czas, $dzien, $godz, $czas, $ilosc_sekund, $wyswietl_zegar);
         
    }

    // rozmiar/pojemnosc produktu
    $rozmiar_wartosc_jednostkowa = $Produkt->ProduktWielkoscPojemnosc();
    //
    $srodek->dodaj('__CSS_WARTOSC_JEDNOSTKOWA', 'style="display:none"');    
    $srodek->dodaj('__WARTOSC_JEDNOSTKOWA_INFORMACJA', '');   
    //
    if ( $rozmiar_wartosc_jednostkowa != '' && $Produkt->info['tylko_za_punkty'] == 'nie' ) {
         //
         $srodek->dodaj('__CSS_WARTOSC_JEDNOSTKOWA', '');  
         $srodek->dodaj('__WARTOSC_JEDNOSTKOWA_INFORMACJA', $rozmiar_wartosc_jednostkowa); 
         //
    }
    //
    unset($rozmiar_wartosc_jednostkowa);
    
    // dodatkowe opisy
    $TablicaDodatkoweOpisy = $Produkt->ProduktDodatkoweOpisy();
    
    $srodek->dodaj('__DODATKOWY_OPIS_1', '');    
    if ( isset($TablicaDodatkoweOpisy[1]) ) {
         $srodek->dodaj('__DODATKOWY_OPIS_1', '<div class="DodatkowyProduktuOpis DodatkowyProduktuOpis-1">' . $TablicaDodatkoweOpisy[1] . '</div>');
    }
    
    $srodek->dodaj('__DODATKOWY_OPIS_2', '');    
    if ( isset($TablicaDodatkoweOpisy[2]) ) {
         $srodek->dodaj('__DODATKOWY_OPIS_2', '<div class="DodatkowyProduktuOpis DodatkowyProduktuOpis-2">' . $TablicaDodatkoweOpisy[2] . '</div>');
    }    
    
    unset($TablicaDodatkoweOpisy);
    
    // do kuponu dla produktu
    $srodek->dodaj('__CSS_KUPON', $CssKuponu);
    $srodek->dodaj('__KOD_KUPONU', $KodKuponu);
    $srodek->dodaj('__RODZAJ_KUPONU', $RodzajKuponu);
    $srodek->dodaj('__WARTOSC_KUPONU', $WartoscKuponu);
    $srodek->dodaj('__STAWKA_VAT', $Produkt->info['stawka_vat']);
    
    $srodek->dodaj('__DLA_WYBRANYCH_WYSYLEK', '');
    if ( isset($Produkt->kupon_dostawa['wysylki']) && $Produkt->kupon_dostawa['wysylki'] != '' ) {
         $srodek->dodaj('__DLA_WYBRANYCH_WYSYLEK', $GLOBALS['tlumacz']['DLA_WYBRANYCH_WYSYLEK']);
    }
            
    unset($CssKuponu, $KodKuponu, $RodzajKuponu, $WartoscKuponu);
    
    // jakie zestawy
    if ( KARTA_PRODUKTU_ZESTAWY == 'tak' ) {
      
        $zestawyProduktu = $Produkt->ProduktJakieZestawy();
        
        if ( count($zestawyProduktu) > 0 ) {
        
            ob_start();
            if (in_array( 'listing_produkty_zestawu.php', $Wyglad->PlikiListingiLokalne )) {
                require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_zestawu.php');
              } else {
                require('listingi/listing_produkty_zestawu.php');
            }
            $ListaZestawow = ob_get_contents();
            ob_end_clean();    

            $srodek->dodaj('__LISTING_ZESTAWY_PRODUKTU', $ListaZestawow);   
            $srodek->parametr('Zestawy', 'tak');
            
            unset($ListaZestawow);
            
        } else {
            
            $srodek->dodaj('__LISTING_ZESTAWY_PRODUKTU', '');  
            $srodek->parametr('Zestawy', 'nie');
            
        }
    
    } else {
        
        $srodek->parametr('Zestawy', 'nie');
        
    }        
    
    // produkty podobne
    if ( KARTA_PRODUKTU_PODOBNE_PRODUKTY == 'tak') {
        //
        $zapytanie = Produkty::SqlProduktyPodobne( $Produkt->info['id'] );
        $sql = $GLOBALS['db']->open_query($zapytanie);    
        //
        $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
        $srodek->parametr('ProduktyPodobneIlosc', $IloscProduktow);
        //
        ob_start();
        if (in_array( 'listing_wiersze_karta_produktu.php', $Wyglad->PlikiListingiLokalne )) {
            require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_wiersze_karta_produktu.php');
          } else {
            require('listingi/listing_wiersze_karta_produktu.php');
        }
        $ListaProduktowPodobnych = ob_get_contents();
        ob_end_clean();    

        $srodek->dodaj('__LISTING_PRODUKTY_PODOBNE', $ListaProduktowPodobnych);   
        unset($ListaProduktowPodobnych);
        //
        unset($IloscProduktow);
        //
    } else {
        //
        $srodek->parametr('ProduktyPodobneIlosc', 0);
        //
    }
    
    // klienci kupili takze
    if ( KARTA_PRODUKTU_KLIENCI_KUPILI_TAKZE == 'tak' ) {
        //
        // wyszukiwanie nr zamowien w ktorych byl kupowany produkt
        $nrZamowien = array();
        $zapytanie = "select op.orders_id from orders_products op, orders o where products_id = '" . $Produkt->info['id'] . "' and op.orders_id = o.orders_id and DATE_SUB(CURDATE(), INTERVAL 720 DAY) <= o.date_purchased order by o.date_purchased desc limit 100";
        $sql = $GLOBALS['db']->open_query($zapytanie);    
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
          
            while ($info = $sql->fetch_assoc()) {
                $nrZamowien[] = $info['orders_id'];
            }
            
            unset($info);
            
        }
        
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie);    
        
        // szukanie id produktow
        $zapytanie = Produkty::SqlProduktyKlienciKupiliTakze( $Produkt->info['id'], $nrZamowien );
        $sql = $GLOBALS['db']->open_query($zapytanie);    
        unset($nrZamowien);
        //
        $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
        $srodek->parametr('KlienciKupiliTakzeIlosc', $IloscProduktow);
        //
        ob_start();
        if (in_array( 'listing_wiersze_karta_produktu.php', $Wyglad->PlikiListingiLokalne )) {
            require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_wiersze_karta_produktu.php');
          } else {
            require('listingi/listing_wiersze_karta_produktu.php');
        }
        $ListaKlienciZakupiliTakze = ob_get_contents();
        ob_end_clean();    

        $srodek->dodaj('__LISTING_KLIENCI_ZAKUPILI_TAKZE', $ListaKlienciZakupiliTakze);
        unset($ListaKlienciZakupiliTakze);
        //
        unset($IloscProduktow);
        //
    } else {
        //
        $srodek->parametr('KlienciKupiliTakzeIlosc', 0);
        //
    }
    
    // produkt nastepny / poprzedni oraz pozostale z kategorii
    //
    $RodzajSciezka = explode('#', (string)$_SESSION['sciezka']);
    $IdKategoriiProducenta = 0;
    $Typ = '';
    $TekstNaglowka = '';

    if ($RodzajSciezka[0] == 'kategoria') {
        //
        $tablica_kategorii = explode('_', (string)$RodzajSciezka[1]);
        if ( (int)$tablica_kategorii[ count($tablica_kategorii)-1 ] > 0 ) {
            $IdKategoriiProducenta = (int)$tablica_kategorii[ count($tablica_kategorii)-1 ];
            $Typ = 'kategoria';
            $TekstNaglowka = $GLOBALS['tlumacz']['NAGLOWEK_POZOSTALE_PRODUKTY_Z_KATEGORII'];
        }
        //
    }
    if ($RodzajSciezka[0] == 'producent') {
        //
        if ( $Produkt->producent['id'] > 0 ) {
            $IdKategoriiProducenta = (int)$Produkt->producent['id'];
            $Typ = 'producent';
            $TekstNaglowka = $GLOBALS['tlumacz']['NAGLOWEK_POZOSTALE_PRODUKTY_PRODUCENTA'];
        }
        //
    }    
    //
    
    // nastepny/poprzedni
    //###############################################################################
    /*
    if ( isset($_SESSION['sortowanie']) ) {
        $TablicaSortowania = array( '1' => 'p.sort_order desc, pd.products_name',
                                    '2' => 'p.sort_order asc, pd.products_name',
                                    '3' => 'p.products_price desc',
                                    '4' => 'p.products_price asc',
                                    '5' => 'pd.products_name desc',
                                    '6' => 'pd.products_name asc' );
        $Sortowanie = $TablicaSortowania[$_SESSION['sortowanie']];
    } else {
        $Sortowanie = 'p.sort_order asc, pd.products_name';
    }

    $Tbl = Produkty::ProduktyPoprzedniNastepny( $IdKategoriiProducenta, $Sortowanie, $Produkt->info['id'] );

    Przyklad do wykorzystania poprzedni/nastepny na karcie produktu
    if ( isset($Tbl['prev']) ){
        echo '<a href="' . Seo::link_SEO( $Tbl['prev']['nazwa'], $Tbl['prev']['id'], 'produkt' ) . '">Poprzedni</a>';
    }
    if ( isset($Tbl['next']) ){
        echo '<a href="' . Seo::link_SEO( $Tbl['next']['nazwa'], $Tbl['next']['id'], 'produkt' ) . '">Następny</a>';
    }
    */
    //###############################################################################

    //

    // pozostale produkty z kategorii
    if ( KARTA_PRODUKTU_POZOSTALE_PRODUKTY == 'tak' ) {

        $zapytanie = Produkty::SqlProduktyPozostaleKategorii( $IdKategoriiProducenta, $Typ, $Produkt->info['id'] );
        $sql = $GLOBALS['db']->open_query($zapytanie);    
        //
        $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
        $srodek->parametr('PozostaleProduktyIlosc', $IloscProduktow);     
        //
        ob_start();
        if (in_array( 'listing_wiersze_karta_produktu.php', $Wyglad->PlikiListingiLokalne )) {
            require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_wiersze_karta_produktu.php');
          } else {
            require('listingi/listing_wiersze_karta_produktu.php');
        }
        $ListaProduktowPozostalych = ob_get_contents();
        ob_end_clean();    

        $srodek->dodaj('NAGLOWEK_POZOSTALE_PRODUKTY_Z_KATEGORII_PRODUCENTA', $TekstNaglowka);   
        $srodek->dodaj('__LISTING_PRODUKTY_POZOSTALE_Z_KATEGORII_PRODUCENTA', $ListaProduktowPozostalych);     
        //
        //
    } else {
        //
        $srodek->parametr('PozostaleProduktyIlosc', 0);    
        //
    }

    unset($IdKategoriiProducenta, $Typ, $RodzajSciezka);
    unset($IloscProduktow, $TekstNaglowka, $ListaProduktowPozostalych, $WyswietlanaKategoriaProduktu);

    // ustawienie http - czy ssl
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') {
        $srodek->dodaj('__HTTP_LINK', 'https');
      } else {
        $srodek->dodaj('__HTTP_LINK', 'http');
    }

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik);

    // jezeli byl producent czysci sciezke sesji
    if ( strpos((string)$_SESSION['sciezka'], 'producent') > -1 ) {
        //
        $_SESSION['sciezka'] = '';
        //
    }
    //
  } else {
    //
    // najpierw sprawdzi czy nie ma przekierowania na 301 lub 302
    Przekierowania::SprawdzPrzekierowania(true);
    //
    header('HTTP/1.1 404 Not Found');
    //
    // plik
    $WywolanyPlik = 'brak_strony';

    // ponowne wywolanie modulow - czy sa jakies do wyswietlania na stronie braku-strony
 
    $tpl->dodaj('__MODULY_SRODKOWE_GORA', $Wyglad->SrodekSklepu( 'gora', array(1,3,4)));
    $tpl->dodaj('__MODULY_SRODKOWE_DOL', $Wyglad->SrodekSklepu( 'dol', array(1,3,4) ));

    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'gora' ));
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'dol' ));

    $Meta = MetaTagi::ZwrocMetaTagi( 'brak_strony.php' );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // wyglad srodkowy
    $srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 

    $srodek->dodaj('__NAGLOWEK_INFORMACJI', $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA']);

    $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUKTU']);
    $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUKTU']);

    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    $tpl->dodaj('__JS_PLIK', '');

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik);    
    //    
}

include('koniec.php');

?>