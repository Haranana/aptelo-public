<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'zamowienie_potwierdzenie';

include('start.php');

$Blad = '';

// sprawdzenie czy kraj wysylki jest taki sam jak kraj kupujacego - dla cen netto
if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
  
    if ( !isset($_SESSION['netto_wymuszone']) || (isset($_SESSION['netto_wymuszone']) && $_SESSION['netto_wymuszone'] == 'nie') ) {
    
        if ( !isset($_SESSION['adresDostawy']['panstwo']) || !isset($_SESSION['adresFaktury']['panstwo']) ) {
             //
             $_SESSION['netto'] = 'nie';
             Funkcje::PrzekierowanieURL('koszyk.html'); 
             //
        }
        
        if ( $_SESSION['adresDostawy']['panstwo'] != $_SESSION['adresFaktury']['panstwo'] ) {
             //
             $_SESSION['netto'] = 'nie';
             Funkcje::PrzekierowanieURL('koszyk.html'); 
             //
        }
        
    }
    
}

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() == 0 || (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// przekierowanie do koszyka jezeli nie ma zadnej ustawionej metody wyslki
if ( !isset($_SESSION['rodzajDostawy']['wysylka_id']) || (isset($_SESSION['rodzajDostawy']['wysylka_id']) && (int)$_SESSION['rodzajDostawy']['wysylka_id'] == 0) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// przekierowanie do koszyka jezeli nie ma zadnej ustawionej metody platnosci
if ( !isset($_SESSION['rodzajPlatnosci']['platnosc_id']) || (isset($_SESSION['rodzajPlatnosci']['platnosc_id']) && (int)$_SESSION['rodzajPlatnosci']['platnosc_id'] == 0) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// jezeli kraj dostawy nie jest rowny zapisanemu w sesji - powraca do koszyka
if ( $_SESSION['krajDostawy']['id'] != $_SESSION['adresDostawy']['panstwo'] ) {
 
    Funkcje::PrzekierowanieSSL('zamowienie-zmien-dane.html'); 

}

// sprawdzi czy nie zmienila sie wartosc koszyka 
if ( !isset($_SESSION['podsumowanieZamowienia']['ot_subtotal']) ) {
    
    Funkcje::PrzekierowanieURL('koszyk.html'); 
    
}

// 
$GLOBALS['koszykKlienta']->PrzeliczKoszyk(false);            
//

if ( isset($_SESSION['podsumowanieZamowienia']['ot_subtotal']) && $_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc'] != $GLOBALS['koszykKlienta']->KoszykWartoscProduktow() ) {
    
    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// sprawdzenie czy jest wpisany kupon rabatowy i czy nadal spelnia warunki przyznania
if ( isset($_SESSION['kuponRabatowy']) ) {
     //
     $kupon = new Kupony($_SESSION['kuponRabatowy']['kupon_kod']);
     //
     $TablicaKuponu = $kupon->kupon;
     //
     if ( count($TablicaKuponu) > 0 ) {
          //
          if ( !$TablicaKuponu['kupon_status'] ) {
               unset($_SESSION['kuponRabatowy']);
               Funkcje::PrzekierowanieURL('koszyk.html'); 
          }
          //
     } else {
          //
          unset($_SESSION['kuponRabatowy']);
          Funkcje::PrzekierowanieURL('koszyk.html'); 
          //
     }
     //
     unset($kupon, $TablicaKuponu);
     //
}  

// sprawdza czy jest dostepna wczesniej wybrana w koszyku forma wysylki
$wysylki = new Wysylki($_SESSION['krajDostawy']['kod']);

if ( isset($_SESSION['rodzajDostawy']['wysylka_id']) && !array_key_exists($_SESSION['rodzajDostawy']['wysylka_id'], $wysylki->wysylki) ) {

  unset($_SESSION['rodzajDostawy']);
  Funkcje::PrzekierowanieURL('koszyk.html'); 
  
}

// sprawdzi pkt za produkty
if ( isset($_SESSION['zloz_zamowienie_pkt']) && $_SESSION['zloz_zamowienie_pkt'] == false ) {
  
    Funkcje::PrzekierowanieURL('koszyk.html'); 
  
}

// czy wartosc zamowienia nie jest mniejsza niz koszyk
$MinimalneZamowienieGrupy = Klient::MinimalneZamowienie();
if ( $MinimalneZamowienieGrupy > 0 ) {

    $MinZamowienie = $GLOBALS['waluty']->PokazCeneBezSymbolu($MinimalneZamowienieGrupy,'',true);
    $WartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

    if ( $WartoscKoszyka['brutto'] < $MinZamowienie ) {
         //
         Funkcje::PrzekierowanieURL('koszyk.html'); 
         //
    }
    unset($MinZamowienie, $WartoscKoszyka);
    
}  
unset($MinimalneZamowienieGrupy);

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK','ZAMOWIENIE_REALIZACJA', 'WYSYLKI', 'PLATNOSCI', 'PRZYCISKI', 'PODSUMOWANIE_ZAMOWIENIA', 'REJESTRACJA', 'PRODUKT', 'KLIENCI') ), $GLOBALS['tlumacz'] );

// produkty koszyka
$ProduktyKoszyka = array();

//
// generuje tablice globalne z nazwami cech
Funkcje::TabliceCech();         
//
$MaksymalnyCzasWysylki = 0;
$MaksymalnyCzasWysylkiProdukt = true;

// sprawdzi czy w zamowieniu sa produkty w formie uslugi
$ProduktUsluga = false;
// sprawdzi czy w zamowieniu sa produkty elektroniczne
$ProduktOnline = false;
// sprawdzi czy w zamowieniu sa produkty niestandardowe, indywidualne
$ProduktNiestandardowy = false;
// sprawdzi czy w zamowieniu sa produkty ze zwrotu
$ProduktZeZwrotu = false;
// sprawdzi czy w zamowieniu sa produkty niekompletne
$ProduktNiekompletny = false;
// sprawdzi czy w zamowieniu sa produkty z demontazu
$ProduktZDemontazu = false;
// sprawdzi czy w zamowieniu sa produkty refabrykowane
$ProduktRefabrykowany = false;
// sprawdzi czy w zamowieniu sa produkty z odzysku
$ProduktZOdzysku = false;

foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //
    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ), 40, 40 );

    // stan magazynowy produktu i elementy kupowania
    if ( strpos((string)$TablicaZawartosci['id'], "x") > -1 && MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && $Produkt->info['kontrola_magazynu'] > 0 ) {
         //
         $SameCechy = substr((string)$TablicaZawartosci['id'], strpos((string)$TablicaZawartosci['id'], "x"), strlen((string)$TablicaZawartosci['id']) ); 
         //        
         $Produkt->ProduktKupowanie( $SameCechy ); 
         //
         unset($SameCechy);
         //
         $IloscMagazyn = $Produkt->zakupy['ilosc_magazyn'];
         //
    } else {
         //
         $Produkt->ProduktKupowanie();
         //
         $IloscMagazyn = $Produkt->zakupy['ilosc_magazyn'];             
         //
    }
        
    // czas wysylki
    $Produkt->ProduktCzasWysylki();
    // stan produktu
    if ( ZAMOWIENIE_POKAZ_STAN_PRODUKTU == 'tak' ) {
         $Produkt->ProduktStanProduktu();
    }  
    // gwarancja produktu
    if ( ZAMOWIENIE_POKAZ_GWARANCJA == 'tak' ) {
         $Produkt->ProduktGwarancja();
    }      
    //
    // jezeli jest kupowanie na wartosci ulamkowe to sformatuje liczbe
    if ( $Produkt->info['jednostka_miary_typ'] == '0' ) {
         $TablicaZawartosci['ilosc'] = number_format( $TablicaZawartosci['ilosc'] , 2, '.', '' );
    }
    //
    // czy produkt ma cechy
    $CechaPrd = Funkcje::CechyProduktuPoId( $TablicaZawartosci['id'] );
    $JakieCechy = '';
    if ( count($CechaPrd) > 0 ) {
        //
        for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
            $JakieCechy .= '<span class="Cecha">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></span>';
        }
        //
    }
    //
    // czy produkt ma komentarz
    $KomentarzProduktu = '';
    if ( $TablicaZawartosci['komentarz'] != '' ) {
        //
        $KomentarzProduktu = '<span class="Komentarz">' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' <b>' . $TablicaZawartosci['komentarz'] . '</b></span>';
        //
    }
    // czy sa pola tekstowe
    $PolaTekstowe = '';
    if ( $TablicaZawartosci['pola_txt'] != '' ) {
        //
        $TblPolTxt = Funkcje::serialCiag($TablicaZawartosci['pola_txt']);
        foreach ( $TblPolTxt as $WartoscTxt ) {
            //
            // jezeli pole to plik
            if ( $WartoscTxt['typ'] == 'plik' ) {
                $PolaTekstowe .= '<span class="Cecha">' . $WartoscTxt['nazwa'] . ': <a href="inne/wgranie.php?src=' . base64_encode(str_replace('.', ';', (string)$WartoscTxt['tekst'])) . '"><b>' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</b></a></span>';
              } else {
                $PolaTekstowe .= '<span class="Cecha">' . $WartoscTxt['nazwa'] . ': <b>' . $WartoscTxt['tekst'] . '</b></span>';
            }
        }
        unset($TblPolTxt);
        //
    }    
    // jezeli produkt jest tylko za PUNKTY - ilosc pkt w koszyku jest > 0
    if ( $Produkt->info['tylko_za_punkty'] == 'tak' ) {
         //
         $CenaProduktu = $GLOBALS['waluty']->PokazCenePunkty( $TablicaZawartosci['cena_punkty'], $TablicaZawartosci['cena_brutto'] );
         $WartoscProduktu = $GLOBALS['waluty']->PokazCenePunkty( $TablicaZawartosci['cena_punkty'] * $TablicaZawartosci['ilosc'], $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'] );
         //          
      } else {
         //
         $CenaProduktu = $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'], $TablicaZawartosci['cena_netto'], 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false);
         $WartoscProduktu = $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'], $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'], 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false);
         //
    }    

    // okreslenie czasu wysylki
    $CzasWysylkiProduktu = '';
    $IloscDniWysylkiProduktu = 0;
    //
    if ( !empty($TablicaZawartosci['czas_wysylki_nazwa']) ) {
         //
         $CzasWysylkiProduktu = '<span class="Cecha">' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': <b>' . $TablicaZawartosci['czas_wysylki_nazwa'] . '</b></span>';
         $IloscDniWysylkiProduktu = $TablicaZawartosci['czas_wysylki_dni'];
         //
         // jezeli jest czesciowa kontrola magazynu i ilosc w koszyku jest wieksza niz ilosc produktu
         if ( $Produkt->info['kontrola_magazynu'] == 2 && $TablicaZawartosci['ilosc'] > $IloscMagazyn ) {
              //
              if ( $Produkt->info['id_czasu_wysylki_stan_zero'] > 0 ) {
                   //
                   $CzasWysylkiProduktuTablica = $Produkt->ProduktCzasWysylki( $Produkt->info['id_czasu_wysylki_stan_zero'] );
                   //
                   $CzasWysylkiProduktu = '<span class="Cecha">' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': <b>' . $CzasWysylkiProduktuTablica['nazwa'] . '</b></span>';
                   $IloscDniWysylkiProduktu = (int)$CzasWysylkiProduktuTablica['dni'];
                   //
                   unset($CzasWysylkiProduktuTablica);
                   //
              }
              //
         }
         //
    }

    // maksymalny czas wysylki
    if ( $IloscDniWysylkiProduktu > $MaksymalnyCzasWysylki ) {
         $MaksymalnyCzasWysylki = $IloscDniWysylkiProduktu;
    }
    // sprawdza czy kazdy produkt ma czas wysylki
    if ( $IloscDniWysylkiProduktu == 0 ) {
         $MaksymalnyCzasWysylkiProdukt = false;
    }
    
    unset($IloscDniWysylkiProduktu);
    
    $ProduktyKoszyka[$TablicaZawartosci['id']] = array('id'            => $TablicaZawartosci['id'],
                                                       'zdjecie'       => $Produkt->fotoGlowne['zdjecie_link'],
                                                       'nazwa'         => $Produkt->info['link'] . $JakieCechy,
                                                       'link_opisu'    => '<a class="Informacja" href="' . $Produkt->info['adres_seo'] . '">' . $GLOBALS['tlumacz']['SZCZEGOLOWY_OPIS_PRODUKTU'] . '</a>',
                                                       'producent'     => (( !empty($Produkt->info['nazwa_producenta']) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['PRODUCENT'] . ': <b>' . $Produkt->info['nazwa_producenta'] . '</b></span>' : ''),
                                                       'czas_wysylki'  => $CzasWysylkiProduktu,
                                                       'stan_produktu' => (( !empty($Produkt->stan_produktu) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['STAN_PRODUKTU'] . ': <b>' . $Produkt->stan_produktu . '</b></span>' : ''),
                                                       'gwarancja'     => (( !empty($Produkt->gwarancja) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['GWARANCJA'] . ': <b>' . str_replace('<a ', '<a style="font-weight:bold" ', (string)$Produkt->gwarancja) . '</b></span>' : ''),
                                                       'komentarz'     => $KomentarzProduktu,
                                                       'ubezpieczenie' => (( isset($TablicaZawartosci['wariant']['ubezpieczenie']) && count($TablicaZawartosci['wariant']['ubezpieczenie']) > 0 ) ? '<b class="PodsumowanieEasyProtect">+ ' . $GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . ' - ' . $GLOBALS['tlumacz']['OKRES_OCHRONY'] . ': ' . $TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] . ' ' . (($TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 1) ? $GLOBALS['tlumacz']['ROK'] : $GLOBALS['tlumacz']['LATA']) . '</b>' : ''),
                                                       'pola_txt'      => $PolaTekstowe,
                                                       'ilosc'         => $TablicaZawartosci['ilosc'] . ' ' . $Produkt->info['jednostka_miary'],
                                                       'cena'          => $CenaProduktu,
                                                       'wartosc'       => $WartoscProduktu,
                                                       'nr_katalogowy' => (( !empty($TablicaZawartosci['nr_katalogowy']) ) ? '<span class="Cecha KoszykNrKatalogowy">' . $GLOBALS['tlumacz']['NUMER_KATALOGOWY'] . ': <b>' . $TablicaZawartosci['nr_katalogowy'] . '</b></span>' : ''));

    // sprawdzi czy w zamowieniu sa produkty w formie uslugi
    if ( $Produkt->info['typ_produktu'] == 'usluga' ) {
         $ProduktUsluga = true;
    }
    
    // sprawdzi czy w zamowieniu sa produkty elektroniczne
    if ( $Produkt->info['typ_produktu'] == 'online' ) {
         $ProduktOnline = true;
    }
    
    // sprawdzi czy w zamowieniu sa produkty niestandardowe, indywidualne
    if ( $Produkt->info['typ_produktu'] == 'indywidualny' ) {
         $ProduktNiestandardowy = true;
    }    
    
    // sprawdzi czy w zamowieniu sa produkty ze zwrotu
    if ( $Produkt->info['typ_produktu'] == 'zezwrotu' ) {
         $ProduktZeZwrotu = true;
    }    

    // sprawdzi czy w zamowieniu sa produkty niekompletne
    if ( $Produkt->info['typ_produktu'] == 'niekompletny' ) {
         $ProduktNiekompletny = true;
    }    

    // sprawdzi czy w zamowieniu sa produkty z demontazu
    if ( $Produkt->info['typ_produktu'] == 'demontaz' ) {
         $ProduktZDemontazu = true;
    }    

    // sprawdzi czy w zamowieniu sa produkty refabrykowane
    if ( $Produkt->info['typ_produktu'] == 'refabrykowany' ) {
         $ProduktRefabrykowany = true;
    }    

    // sprawdzi czy w zamowieniu sa produkty z odzysku
    if ( $Produkt->info['typ_produktu'] == 'zodzysku' ) {
         $ProduktZOdzysku = true;
    }    

    //
    unset($Produkt, $CenaProduktu, $WartoscProduktu, $KomentarzProduktu, $PolaTekstowe);
    //
}
//
// jezeli wszystkie produkty mialy czas wysylki
if ( $MaksymalnyCzasWysylkiProdukt == true ) {
     $MaksymalnyCzasWysylki = str_replace('{0}', $MaksymalnyCzasWysylki, $GLOBALS['tlumacz']['SZACOWANY_CZAS_WYSYLKI'] . '<input type="hidden" name="planowany_czas_wysylki" value="' . $MaksymalnyCzasWysylki . '" />');
}
//

// parametry do ustalenia podsumowania zamowienia
$podsumowanie = new Podsumowanie();
$PodsumowanieZamowienia = $podsumowanie->GenerujWPotwierdzeniu();

$CssDokumentSprzedazy = '';

if ( !isset($_SESSION['adresFaktury']['dokument']) ) {
  
    $_SESSION['adresFaktury']['dokument'] = '';
    
}

// jezeli jest wybrany do zaznaczenia paragon lub faktura
if ( $_SESSION['adresFaktury']['dokument'] == '' ) {
     //
     if ( KLIENT_DOMYSLNY_DOKUMENT == 'paragon' || KLIENT_DOMYSLNY_DOKUMENT == 'faktura' ) {

        if ( KLIENT_DOMYSLNY_DOKUMENT == 'faktura' ) {
            $_SESSION['adresFaktury']['dokument'] = '1';
        } elseif ( KLIENT_DOMYSLNY_DOKUMENT == 'paragon' ) {
            $_SESSION['adresFaktury']['dokument'] = '0';
        }
        
        // jezeli klient jest jako firma i ma byc faktura to ustawic domyslne fakture
        if ( KLIENT_DOMYSLNY_DOKUMENT_FIRMA == 'tak' && $_SESSION['adresDostawy']['firma'] != '' ) {
            $_SESSION['adresFaktury']['dokument'] = '1';
        }    
        
     }

}

// jezeli jest ukryty wybor dokumentu sprzedazy przyjmuje domyslnie faktura dla zamowienia
if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'nie' ) {
    //     
    $_SESSION['adresFaktury']['dokument'] = '1';
    //
}
     
// jezeli jest obsluga tylko firm to ustawi fakture jako dokument sprzedazy
if ( KLIENT_TYLKO_FIRMA == 'tylko firma' ) {
    //
    $_SESSION['adresFaktury']['dokument'] = '1';
    //
    // jezeli jest tylko firma to nie potrzebny jest wybor dokumentu sprzedazy i pozostaje tylko faktura
    $CssDokumentSprzedazy = 'style="display:none"';
    //
}
     
$DaneDoWysylki = '';

//echo $_SESSION['adresFaktury']['dokument'];

$DaneDoWysylki .= $_SESSION['adresDostawy']['imie'] . ' ' . $_SESSION['adresDostawy']['nazwisko'] . '<br />';

if ( $_SESSION['adresDostawy']['firma'] != '' ) {
    $DaneDoWysylki .= $_SESSION['adresDostawy']['firma'] . '<br />';
}

$DaneDoWysylki .= $_SESSION['adresDostawy']['ulica'] . '<br />';

$DaneDoWysylki .= $_SESSION['adresDostawy']['kod_pocztowy'] . ' ' . $_SESSION['adresDostawy']['miasto'] . '<br />';

if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    $DaneDoWysylkiTmp = Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo']);
    if ( !empty($DaneDoWysylkiTmp) ) {
         $DaneDoWysylki .= $DaneDoWysylkiTmp . '<br />';
    }
    unset($DaneDoWysylkiTmp);
}

$DaneDoWysylki .= Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo']) . '<br />';

if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
    $DaneDoWysylki .= $GLOBALS['tlumacz']['TELEFON_SKROCONY'] . ' ' . $_SESSION['adresDostawy']['telefon'] . '<br />';
}

$DaneDoFaktury = '';

if ( $_SESSION['adresFaktury']['imie'] != '' && $_SESSION['adresFaktury']['nazwisko'] != '' ) {
    $DaneDoFaktury .= $_SESSION['adresFaktury']['imie'] . ' ' . $_SESSION['adresFaktury']['nazwisko'] . '<br />';
}

if ( $_SESSION['adresFaktury']['firma'] != '' ) {
    $DaneDoFaktury .= $_SESSION['adresFaktury']['firma'] . '<br />';    
}
$DaneDoFaktury .= ((trim((string)$_SESSION['adresFaktury']['nip']) != '') ? $_SESSION['adresFaktury']['nip'] . '<br />' : '');

$DaneDoFaktury .= $_SESSION['adresFaktury']['ulica'] . '<br />';

$DaneDoFaktury .= $_SESSION['adresFaktury']['kod_pocztowy'] . ' ' . $_SESSION['adresFaktury']['miasto'] . '<br />';

if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    $DaneDoFakturyTmp = Klient::pokazNazweWojewodztwa($_SESSION['adresFaktury']['wojewodztwo']);
    if ( !empty($DaneDoFakturyTmp) ) {
         $DaneDoFaktury .= $DaneDoFakturyTmp . '<br />';
    }
    unset($DaneDoFakturyTmp);
}

$DaneDoFaktury .= Klient::pokazNazwePanstwa($_SESSION['adresFaktury']['panstwo']);

// przypisanie wartosci punktu dostawy z koszyka
if ( isset($_SESSION['rodzajDostawyKoszyk'][$_SESSION['rodzajDostawy']['wysylka_klasa']]['opis']) && isset($_SESSION['rodzajDostawyKoszyk'][$_SESSION['rodzajDostawy']['wysylka_klasa']]['opispunkt']) && isset($_SESSION['rodzajDostawyKoszyk'][$_SESSION['rodzajDostawy']['wysylka_klasa']]['punktodbioru']) ) { 
     $_SESSION['rodzajDostawy']['opis'] = $_SESSION['rodzajDostawyKoszyk'][$_SESSION['rodzajDostawy']['wysylka_klasa']]['opis'];
     $_SESSION['rodzajDostawy']['opispunkt'] = $_SESSION['rodzajDostawyKoszyk'][$_SESSION['rodzajDostawy']['wysylka_klasa']]['opispunkt'];
     $_SESSION['rodzajDostawy']['punktodbioru'] = $_SESSION['rodzajDostawyKoszyk'][$_SESSION['rodzajDostawy']['wysylka_klasa']]['punktodbioru'];
}

// parametry do ustalenia dostepnych punktow odbioru
$WysylkaPotwierdzenieZamowienia = $wysylki->Potwierdzenie( $_SESSION['rodzajDostawy']['wysylka_id'], $_SESSION['rodzajDostawy']['wysylka_klasa'] );
$WysylkaPotwierdzenieZamowieniaInfo = '';
if ( isset($GLOBALS['tlumacz']['WYSYLKA_'.$_SESSION['rodzajDostawy']['wysylka_id'].'_INFORMACJA']) ) {
    $WysylkaPotwierdzenieZamowieniaInfo = $GLOBALS['tlumacz']['WYSYLKA_'.$_SESSION['rodzajDostawy']['wysylka_id'].'_INFORMACJA'];
    $_SESSION['rodzajDostawy']['informacja'] = $WysylkaPotwierdzenieZamowieniaInfo;
}

// parametry do ustalenia danych do wplaty
$platnosci = new Platnosci($_SESSION['rodzajDostawy']['wysylka_id']);
$PlatnoscPotwierdzenieZamowienia = $platnosci->Potwierdzenie( $_SESSION['rodzajPlatnosci']['platnosc_id'], $_SESSION['rodzajPlatnosci']['platnosc_klasa'] );

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// css do kalendarza
$tpl->dodaj('__CSS_PLIK', ',zebra_datepicker' . ((INTEGRACJA_EASYPROTECT_WLACZONY == 'tak') ? ',easyprotect' : ''));
// dla wersji mobilnej
$tpl->dodaj('__CSS_KALENDARZ', ',zebra_datepicker');

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZAMOWIENIE_POTWIERDZENIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// zgoda na opinie
$ZgodaNaOpinie = false;
if ( INTEGRACJA_CENEO_OPINIE_WLACZONY == 'tak' || INTEGRACJA_OPINEO_OPINIE_WLACZONY == 'tak' || INTEGRACJA_OKAZJE_WLACZONY == 'tak' || INTEGRACJA_GOOGLE_OPINIE_WLACZONY == 'tak' || INTEGRACJA_TRUSTMATE_WLACZONY == 'tak' || ( OPINIE_STATUS == 'tak' && OPINIE_WYSYLAJ_MAILE == 'tak' ) || ( RECENZJE_STATUS == 'tak' && RECENZJE_WYSYLAJ_MAILE == 'tak' ) ) {
     //
     $ZgodaNaOpinie = true;
     //
}
if ( KLIENT_ZGODY_OPINIE == 'nie' ) {
     //
     $ZgodaNaOpinie = false;
     //
}

// integracja z Google remarketing dynamiczny i Google Analytics
$wynikGoogle = IntegracjeZewnetrzne::GoogleAnalyticsRemarketingZamowieniePotwierdzenie();
$tpl->dodaj('__GOOGLE_KONWERSJA', $wynikGoogle['konwersja']);
$tpl->dodaj('__GOOGLE_ANALYTICS', $wynikGoogle['analytics']);
unset($wynikGoogle);
    

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $ProduktyKoszyka, $ZgodaNaOpinie);
//

$srodek->parametr('ProduktyUsluga', $ProduktUsluga);
$srodek->parametr('ProduktyOnline', $ProduktOnline);
$srodek->parametr('ProduktyNiestandardowe', $ProduktNiestandardowy);
$srodek->parametr('ProduktyZeZwrotu', $ProduktZeZwrotu);
$srodek->parametr('ProduktyNiekompletne', $ProduktNiekompletny);
$srodek->parametr('ProduktyZDemontazu', $ProduktZDemontazu);
$srodek->parametr('ProduktyRefabrykowane', $ProduktRefabrykowany);
$srodek->parametr('ProduktyZOdzysku', $ProduktZOdzysku);

unset($ProduktyKoszyka, $ProduktUsluga, $ProduktOnline, $ProduktNiestandardowy, $ProduktZeZwrotu, $ProduktNiekompletny, $ProduktZDemontazu, $ProduktRefabrykowany, $ProduktZOdzysku);

// maksymalny czas wysylki
$srodek->dodaj('__MAKSYMALNY_CZAS_WYSYLKI', '');
if ( $MaksymalnyCzasWysylkiProdukt == true ) {
     $srodek->dodaj('__MAKSYMALNY_CZAS_WYSYLKI', '<div class="Informacja">' . $MaksymalnyCzasWysylki . '</div>');
}
unset($MaksymalnyCzasWysylki, $MaksymalnyCzasWysylkiProdukt);

// wartosc koszyka
$ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
$srodek->dodaj('__WARTOSC_KOSZYKA', $GLOBALS['waluty']->PokazCene($ZawartoscKoszyka['brutto'], $ZawartoscKoszyka['netto'], 0, $_SESSION['domyslnaWaluta']['id']));
unset($ZawartoscKoszyka);

// dodatkowe elementy do podsumowania zamowienia
$srodek->dodaj('__PODSUMOWANIE_ZAMOWIENIA', $PodsumowanieZamowienia);
$srodek->dodaj('__DANE_DO_WYSYLKI', $DaneDoWysylki);
$srodek->dodaj('__DANE_DO_FAKTURY', $DaneDoFaktury);
$srodek->dodaj('__WYSYLKA_W_POTWIERDZENIU', $WysylkaPotwierdzenieZamowienia);
$srodek->dodaj('__WYSYLKA_W_POTWIERDZENIU_INFORMACJA', $WysylkaPotwierdzenieZamowieniaInfo);
$srodek->dodaj('__PLATNOSC_W_POTWIERDZENIU', $PlatnoscPotwierdzenieZamowienia);

// jezeli jest wylaczony wybor dokumentu sprzedazy
if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'nie' ) {
     $CssDokumentSprzedazy = 'style="display:none"';
}

$srodek->dodaj('__CSS_DOKUMENT_SPRZEDAZY', $CssDokumentSprzedazy);

$DodatkowePolaZamowienia = Zamowienie::pokazDodatkowePolaZamowienia((int)$_SESSION['domyslnyJezyk']['id']);
if ( $DodatkowePolaZamowienia != '' ) {
     $DodatkowePolaZamowienia = '<div class="PolaZamowienie">' . $DodatkowePolaZamowienia . '</div>';
}

$srodek->dodaj('__DODATKOWE_POLA_ZAMOWIENIA', $DodatkowePolaZamowienia);

unset($DodatkowePolaZamowienia);

// dodatkowe adresy dostawy
$srodek->dodaj('__DODATKOWE_ADRESY_DOSTAWY', '');

$TablicaAdresow = array();
//
$zapytanie = "SELECT c.customers_id, 
                     a.address_book_id, 
                     a.entry_company, 
                     a.entry_nip, 
                     a.entry_pesel, 
                     a.entry_firstname, 
                     a.entry_lastname, 
                     a.entry_street_address, 
                     a.entry_postcode, 
                     a.entry_city, 
                     a.entry_country_id, 
                     a.entry_zone_id
                FROM customers c 
           LEFT JOIN address_book a ON a.customers_id = c.customers_id
               WHERE c.customers_id = '" . (int)$_SESSION['customer_id'] . "' AND c.customers_guest_account = '0' AND c.customers_status = '1'";

$sql = $GLOBALS['db']->open_query($zapytanie); 

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 1) {
  
    $TablicaAdresow[] = array( 'id' => 0,
                               'text' => $GLOBALS['tlumacz']['WYBIERZ_INNY_ADRES_DOSTAWY'] );  

    while ( $info = $sql->fetch_assoc() ) {
        
          $TablicaAdresow[] = array( 'id' => $info['address_book_id'],
                                     'text' => ((!empty($info['entry_company'])) ? $info['entry_company'] . ', ' : '') . 
                                                 $info['entry_firstname'] . ' ' . $info['entry_lastname'] . ', ' . 
                                                 $info['entry_street_address'] . ', ' . 
                                                 $info['entry_postcode'] . ' ' . 
                                                 $info['entry_city'] .
                                                 (($info['entry_country_id'] != $_SESSION['krajDostawy']['id']) ? ', ' . Klient::pokazNazwePanstwa($info['entry_country_id']) : ''));

    }
    
    $srodek->dodaj('__DODATKOWE_ADRESY_DOSTAWY', '<br />' . Funkcje::RozwijaneMenu('dodatkowe_adresy', $TablicaAdresow, '', ' id="wybor_adresu" style="width:80%"'));

    unset($TablicaAdresow, $info);
  
}

$GLOBALS['db']->close_query($sql);
unset($zapytanie);

$TekstZgody = str_replace('{INFO_NAZWA_SKLEPU}', (string)DANE_NAZWA_FIRMY_PELNA, (string)$GLOBALS['tlumacz']['ZGODA_NA_PRZEKAZANIE_DANYCH']);
$srodek->dodaj('__TEKST_ZGODY', $TekstZgody);
$srodek->dodaj('__CSS_TEKST_ZGODY', '');
$srodek->dodaj('__ZAZNACZENIE_TEKST_ZGODY', '');

if (isset($_COOKIE['opinie'])) {
    //
    $srodek->dodaj('__CSS_TEKST_ZGODY', 'style="display:none"'); 
    //
    if ($_COOKIE['opinie'] == 'tak') {
        $srodek->dodaj('__ZAZNACZENIE_TEKST_ZGODY', 'checked="checked"');
    }
    //
} else {
    //
    // czy klient wczesniej wyrazil zgode na wysylanie opinii
    $zapytanie = "SELECT customers_reviews FROM customers WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "' AND customers_reviews = '1'";
    $sql = $GLOBALS['db']->open_query($zapytanie); 

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
       //
       $srodek->dodaj('__CSS_TEKST_ZGODY', 'style="display:none"');
       $srodek->dodaj('__ZAZNACZENIE_TEKST_ZGODY', 'checked="checked"');     
       //
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);
    //
}

$srodek->dodaj('__UWAGI_KOSZYKA', '');
if (isset($_SESSION['uwagiKoszyka'])) {
    $srodek->dodaj('__UWAGI_KOSZYKA', $_SESSION['uwagiKoszyka']);
}

// integracja z Klaviyo
$wynikKlaviyo = IntegracjeZewnetrzne::KlaviyoZamowieniePotwierdzenie();
$srodek->dodaj('__INTEGRACJA_KLAVIYO', $wynikKlaviyo);
unset($wynikKlaviyo);
    

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik, $PodsumowanieZamowienia, $DaneDoWysylki, $DaneDoFaktury, $WysylkaPotwierdzenieZamowienia, $WysylkaPotwierdzenieZamowieniaInfo, $PlatnoscPotwierdzenieZamowienia, $TekstZgody, $CssDokumentSprzedazy);

//echo '<pre>';
//print_r($_SESSION['adresFaktury']);
//echo '</pre>';

include('koniec.php');

?>