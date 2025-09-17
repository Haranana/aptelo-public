<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'koszyk';

include('start.php');

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

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('WYSYLKI', 'PLATNOSCI', 'PRZYCISKI', 'KOSZYK','KUPONY_RABATOWE','PUNKTY','ZAMOWIENIE_REALIZACJA', 'PODSUMOWANIE_ZAMOWIENIA') ), $GLOBALS['tlumacz'] );

// wczytanie zapisane koszyka z linku
if ( isset($_GET['id_koszyka']) ) {
     //
     $GLOBALS['koszykKlienta']->WczytajKoszykLinku( $filtr->process($_GET['id_koszyka']) );
     //
}

// produkty koszyka
$ProduktyKoszyka = array();

// dodatkowe parametry zamowienia
$DodatkoweInformacje = array();

$BrakMagazyn = false;

if ( isset($GLOBALS['koszykKlienta']) && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {

    // wartosc produktow w promocji - potrzebne do wysylek
    $WartoscProduktowPromocje = 0;

    // przelicza dodatkowo koszyk
    $GLOBALS['koszykKlienta']->PrzeliczKoszyk(); 

    // okreslenia maksymalnego czasu wysylki
    $MaksymalnyCzasWysylki = 0;
    $MaksymalnyCzasWysylkiProdukt = true;

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();     
    //
    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ), 40, 40 );
        //  
        // czas wysylki
        $Produkt->ProduktCzasWysylki();
    
        // sprawdzi jest produkt
        if ($Produkt->CzyJestProdukt == false) {
             //
             $GLOBALS['koszykKlienta']->UsunZKoszyka( $TablicaZawartosci['id'] );
             Funkcje::PrzekierowanieSSL('koszyk.html'); 
             //
        }        
        // sumuje wartosc produktow w promocji
        if ( $TablicaZawartosci['promocja'] == 'tak' ) {
             $WartoscProduktowPromocje += $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
        }
        //
        // jezeli jest kupowanie na wartosci ulamkowe to sformatuje liczbe
        if ( $Produkt->info['jednostka_miary_typ'] == '0' ) {
             $TablicaZawartosci['ilosc'] = number_format( (float)$TablicaZawartosci['ilosc'] , 2, '.', '' );
        }
        //
        // czy produkt ma cechy
        $CechaPrd = Funkcje::CechyProduktuPoId( $TablicaZawartosci['id'] );
        $JakieCechyTablica = array();
        if ( count($CechaPrd) > 0 ) {
            //
            for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
                $JakieCechyTablica[ $GLOBALS['NazwyCech'][ $CechaPrd[$a]['id_cechy'] ]['sort'] . '_' . $CechaPrd[$a]['id_cechy'] ] = '<span class="Cecha">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></span>';
            } 
            //
        }
        //
        ksort($JakieCechyTablica);
        //
        $JakieCechy = implode('', (array)$JakieCechyTablica);
        unset($JakieCechyTablica);
        //
        // czy produkt ma komentarz
        $KomentarzProduktu = '';
        if ( $TablicaZawartosci['komentarz'] != '' ) {
            //
            $KomentarzProduktu = '<span class="Komentarz"><img id="img_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '" onclick="EdytujKomentarz(\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\')" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/nawigacja/edytuj.png" alt="" title="' . $GLOBALS['tlumacz']['EDYTUJ_KOMENTARZ'] . '" />' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' <b id="komentarz_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '">' . $TablicaZawartosci['komentarz'] . '</b></span>';
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
        //
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
        //
        // sprawdzi dodatkowo czy nie zostalo wylaczone kupowanie produktu
        if ( $Produkt->zakupy['mozliwe_kupowanie'] == 'nie' ) {
             //
             $GLOBALS['koszykKlienta']->UsunZKoszyka( $TablicaZawartosci['id'] );
             Funkcje::PrzekierowanieSSL('koszyk.html'); 
             //
        }
        //
        //
        $InfoMagazyn = '';
        if ( $TablicaZawartosci['ilosc'] > $IloscMagazyn && MAGAZYN_SPRZEDAJ_MIMO_BRAKU_INFORMACJA == 'tak' && MAGAZYN_SPRAWDZ_STANY == 'tak' && $Produkt->info['kontrola_magazynu'] > 0 ) {
             //
             $InfoMagazyn = '<div class="InformacjaBrakMagazynProdukt">' . $GLOBALS['tlumacz']['BRAK_W_MAGAZYNIE'] . '</div>';
             $BrakMagazyn = true;
             //
        }
        //

        $PoleIlosc = ''; 
        if ( $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) {
            if ( Wyglad::TypSzablonu() == true ) {
                $PoleIlosc = '
                <div class="PoleIlosc"><span class="minus ilosc_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '">-</span><input type="number" class="InputPrzeliczKoszyk" aria-label="' . $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . '" id="ilosc_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '" value="' . $TablicaZawartosci['ilosc'] . '" lang="en_EN" pattern="[0-9]+([\.][0-9]+)?" min="' . ( $Produkt->zakupy['minimalna_ilosc'] > 0 ? $Produkt->zakupy['minimalna_ilosc'] : '1' ). '" step="' . ( $Produkt->info['przyrost'] > '0' ? $Produkt->info['przyrost'] : '1' ) . '" onchange="SprIlosc(this,' . $Produkt->zakupy['minimalna_ilosc'] . ',' . $Produkt->info['jednostka_miary_typ'] . ',\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\',\'' . $Produkt->info['przyrost'] . '\')" /><span class="plus ilosc_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '">+</span></div>';
            } else {
                $PoleIlosc = '
                <input type="number" class="InputPrzeliczKoszyk" aria-label="' . $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . '" id="ilosc_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '" value="' . $TablicaZawartosci['ilosc'] . '" lang="en_EN" pattern="[0-9]+([\.][0-9]+)?" min="' . ( $Produkt->zakupy['minimalna_ilosc'] > 0 ? $Produkt->zakupy['minimalna_ilosc'] : '1' ). '" step="' . ( $Produkt->info['przyrost'] > '0' ? $Produkt->info['przyrost'] : '1' ) . '" onchange="SprIlosc(this,' . $Produkt->zakupy['minimalna_ilosc'] . ',' . $Produkt->info['jednostka_miary_typ'] . ',\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\',\'' . $Produkt->info['przyrost'] . '\')" />';
            }

            $PoleIlosc .= '<div class="Przelicz"><a role="button" tabindex="0" onclick="return DoKoszyka(\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\',\'przelicz\',0)" href="/" class="przycisk">' . $GLOBALS['tlumacz']['PRZELICZ'] . '</a></div>';

        } else {

            $PoleIlosc = $TablicaZawartosci['ilosc'];

        }

        // okreslenie czasu wysylki
        $CzasWysylkiProduktu = '';
        $IloscDniWysylkiProduktu = 0;
        //
        if ( KOSZYK_POKAZ_CZAS_WYSYLKI == 'tak' ) {
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
             
        }
        
        unset($IloscDniWysylkiProduktu);
    
        $ProduktyKoszyka[$TablicaZawartosci['id']] = array('id'              => $TablicaZawartosci['id'],
                                                           'zdjecie'         => '<a class="Zoom" href="' . $Produkt->info['adres_seo'] . '">' . Funkcje::pokazObrazek($TablicaZawartosci['zdjecie'], '', 40, 40, array(), ' data-cechy="' . $TablicaZawartosci['id'] . '" id="fot_' . $Produkt->idUnikat . $Produkt->id_produktu . '" class="Zdjecie"') . '</a>',
                                                           'nazwa'           => $Produkt->info['link'] . $JakieCechy,
                                                           'komentarz'       => $KomentarzProduktu,                                                           
                                                           'pola_txt'        => $PolaTekstowe,
                                                           'usun'            => ((KOSZYK_SPOSOB_USUWANIA == 'pojedyncze usuwanie') ? '<span class="UsunKoszyk" title="{__TLUMACZ:PRZYCISK_USUN}" aria-label="{__TLUMACZ:PRZYCISK_USUN}" onclick="UsunZKoszyka(\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\')"></span>' : '<label><input type="checkbox" class="InputUsunKoszyk" name="usun[]" value="' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '" /><span class="check" id="check_'.$TablicaZawartosci['id'].'"></span></label>'),
                                                           'ilosc'           => $PoleIlosc,
                                                           'tylko_ilosc'     => $TablicaZawartosci['ilosc'],
                                                           'stan_magazynowy' => $IloscMagazyn,
                                                           'brak_magazyn'    => $InfoMagazyn,                                                           
                                                           'cena'            => $CenaProduktu,
                                                           'wartosc'         => $WartoscProduktu,
                                                           'nr_katalogowy'   => $TablicaZawartosci['nr_katalogowy'],
                                                           'producent'       => (( !empty($Produkt->info['nazwa_producenta']) && KOSZYK_POKAZ_PRODUCENT == 'tak' ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['PRODUCENT'] . ': <b>' . $Produkt->info['nazwa_producenta'] . '</b></span>' : ''),
                                                           'czas_wysylki'    => $CzasWysylkiProduktu);
        //
        unset($Produkt, $CenaProduktu, $WartoscProduktu, $KomentarzProduktu, $PolaTekstowe, $InfoMagazyn, $IloscMagazyn, $PoleIlosc, $CzasWysylkiProduktu);
        //
    }
    //
    // parametry do ustalenia dostepnych wysylek
    $wysylki = new Wysylki($_SESSION['krajDostawy']['kod']);
    $TablicaWysylek = $wysylki->wysylki;

    if ( isset($_SESSION['rodzajDostawy']['wysylka_id']) && !array_key_exists($_SESSION['rodzajDostawy']['wysylka_id'], $TablicaWysylek) ) {
    
      unset($_SESSION['rodzajDostawy']);
      
    }
    
    // select z panstwami
    $ListaRozwijanaPanstw = Funkcje::RozwijaneMenu('kraj_dostawy',Klient::ListaPanstw('countries_iso_code_2'), $_SESSION['krajDostawy']['kod'], 'id="kraj_dostawy"');    
    $DarmowaWysylkaPromocje = 'nie';
    
    if ( !isset($_SESSION['rodzajDostawy']) ) {
    
      $PierwszaWysylka = array_slice((array)$TablicaWysylek,0,1);
      
      $_SESSION['rodzajDostawy'] = array(
                                         'wysylka_id' => $PierwszaWysylka['0']['id'],
                                         'wysylka_klasa' => $PierwszaWysylka['0']['klasa'],
                                         'wysylka_koszt' => $PierwszaWysylka['0']['wartosc'],
                                         'wysylka_nazwa' => $PierwszaWysylka['0']['text'],
                                         'wysylka_vat_id' => $PierwszaWysylka['0']['vat_id'],
                                         'wysylka_vat_stawka' => $PierwszaWysylka['0']['vat_stawka'], 
                                         'wysylka_kod_gtu' => $PierwszaWysylka['0']['kod_gtu'],
                                         'dostepne_platnosci' => $PierwszaWysylka['0']['dostepne_platnosci']);
                                         
      $KosztWysylki = $PierwszaWysylka['0']['wartosc'];
      $ProgBezplatnejWysylki = $PierwszaWysylka['0']['wysylka_free'];
      if ( isset($PierwszaWysylka['0']['free_promocje']) ) {
           $DarmowaWysylkaPromocje = $PierwszaWysylka['0']['free_promocje'];
      }
                                         
    } else {
    
      $IdBiezace = $_SESSION['rodzajDostawy']['wysylka_id'];
      unset($_SESSION['rodzajDostawy']);
      $_SESSION['rodzajDostawy'] = array(
                                         'wysylka_id' => $TablicaWysylek[$IdBiezace]['id'],
                                         'wysylka_klasa' => $TablicaWysylek[$IdBiezace]['klasa'],
                                         'wysylka_koszt' => $TablicaWysylek[$IdBiezace]['wartosc'],
                                         'wysylka_nazwa' => $TablicaWysylek[$IdBiezace]['text'],
                                         'wysylka_vat_id' => $TablicaWysylek[$IdBiezace]['vat_id'],
                                         'wysylka_vat_stawka' => $TablicaWysylek[$IdBiezace]['vat_stawka'],  
                                         'wysylka_kod_gtu' => $TablicaWysylek[$IdBiezace]['kod_gtu'],
                                         'dostepne_platnosci' => $TablicaWysylek[$IdBiezace]['dostepne_platnosci'] );

      $KosztWysylki = $TablicaWysylek[$_SESSION['rodzajDostawy']['wysylka_id']]['wartosc'];
      $ProgBezplatnejWysylki = $TablicaWysylek[$_SESSION['rodzajDostawy']['wysylka_id']]['wysylka_free'];
      if ( isset($TablicaWysylek[$_SESSION['rodzajDostawy']['wysylka_id']]['free_promocje']) ) {
           $DarmowaWysylkaPromocje = $TablicaWysylek[$_SESSION['rodzajDostawy']['wysylka_id']]['free_promocje'];
      }
      
    }

    // radio z wysylkami
    $ListaRadioWysylek = '<div id="rodzaj_wysylki">'.Funkcje::ListaRadioKoszyk('rodzaj_wysylki', $TablicaWysylek, $_SESSION['rodzajDostawy']['wysylka_id'], '').'</div>';

    // parametry do ustalenia dostepnych platnosci
    $platnosci = new Platnosci($_SESSION['rodzajDostawy']['wysylka_id']);
    $TablicaPlatnosci = $platnosci->platnosci;

    if ( isset($_SESSION['rodzajPlatnosci']['platnosc_id']) && !array_key_exists($_SESSION['rodzajPlatnosci']['platnosc_id'], $TablicaPlatnosci) ) {
    
      unset($_SESSION['rodzajPlatnosci']);
      
      if ( isset($_SESSION['KanalyPlatnosciComfino']) ) {
          unset($_SESSION['KanalyPlatnosciComfino']);
      }
    }
    
    if ( !isset($_SESSION['rodzajPlatnosci']) ) {
      $PierwszaPlatnosc = array_slice((array)$TablicaPlatnosci,0,1);
      $KosztPlatnosci = $PierwszaPlatnosc['0']['wartosc'];
      $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $PierwszaPlatnosc['0']['id'],
                                           'platnosc_klasa' => $PierwszaPlatnosc['0']['klasa'],
                                           'platnosc_koszt' => $PierwszaPlatnosc['0']['wartosc'],
                                           'platnosc_nazwa' => $PierwszaPlatnosc['0']['text'],
                                           'platnosc_punkty' => ( isset($PierwszaPlatnosc['0']['punkty']) ? $PierwszaPlatnosc['0']['punkty'] : 'nie' ),
                                           'platnosc_kanal' => ( isset($PierwszaPlatnosc['0']['kanal_platnosci']) ? $PierwszaPlatnosc['0']['kanal_platnosci'] : '' ),
      );
                                         
    } else {
    
      $KosztPlatnosci = $TablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['wartosc'];
      
    }

    $CalkowityKoszt = 0;
    if ( is_numeric($KosztWysylki) && is_numeric($KosztPlatnosci)) {
         $CalkowityKoszt = $KosztWysylki + $KosztPlatnosci;
    }    
    $CalkowityKosztWysylki = $GLOBALS['waluty']->PokazCene($CalkowityKoszt, 0, 0, $_SESSION['domyslnaWaluta']['id']);

    // radio z platnosciami
    $ListaRadioPlatnosci = '<div id="rodzaj_platnosci">'.Funkcje::ListaRadioKoszyk('rodzaj_platnosci', $TablicaPlatnosci, $_SESSION['rodzajPlatnosci']['platnosc_id'], '').'</div>';

    $UkryjPrzycisk = '';
    if ( $_SESSION['rodzajPlatnosci']['platnosc_id'] == '0' || $_SESSION['rodzajDostawy']['wysylka_id'] == '0' ) {
      $UkryjPrzycisk = 'style="display:none;"';
    }
    
    // sprawdza czy jest wlaczony modul kuponu rabatowego
    $zapytanie = "SELECT skrypt, status FROM modules_total WHERE skrypt = 'kupon_rabatowy.php'"; 
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    $UkryjKupon = '';
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        //
        if ( $info['status'] == '0' ) {
             $UkryjKupon = 'style="display:none;"';
        }
        //
        unset($info);
        //
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

    // sprawdzenie czy jest wpisany kupon rabatowy i czy nadal spelnia warunki przyznania
    if ( isset($_SESSION['kuponRabatowy']) ) {
      $kupon = new Kupony($_SESSION['kuponRabatowy']['kupon_kod']);
      $TablicaKuponu = $kupon->kupon;
      if ( $_SESSION['kuponRabatowy'] != $TablicaKuponu ) {
          unset($_SESSION['kuponRabatowy']);
          $_SESSION['kuponRabatowy'] = $TablicaKuponu;
      }
      if ( isset($TablicaKuponu['kupon_status']) && $TablicaKuponu['kupon_status'] ) {
      } else {
        unset($_SESSION['kuponRabatowy']);
      }
    }
    
    $UkryjOzdobneOpakowanie = '';
    $KosztOzdobnegoOpakowania = 0;
    
    // sprawdza czy jest wlaczony modul opakowania ozdobnego
    $zapytanie = "SELECT skrypt, status FROM modules_total WHERE skrypt = 'ozdobne_opakowanie.php'"; 
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        $info = $sql->fetch_assoc();
        
        if ( $info['status'] == '0' ) {
             //
             $UkryjOzdobneOpakowanie = 'style="display:none;"';        
             //
        } else {
             //
             // ustali wartosc opakowania
             $sqlOpakowanie = $GLOBALS['db']->open_query("SELECT wartosc FROM modules_total_params WHERE kod = 'OPAKOWANIE_OZDOBNE_KOSZT'"); 
             $infp = $sqlOpakowanie->fetch_assoc();
             //
             if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                 //
                 $sqlOpakowanieVat = $GLOBALS['db']->open_query("SELECT wartosc FROM modules_total_params WHERE kod = 'OPAKOWANIE_OZDOBNE_STAWKA_VAT'"); 
                 $infv = $sqlOpakowanieVat->fetch_assoc();
                 //
                 $vatOpakowanie = explode('|', (string)$infv['wartosc']);
                 //
                 // obliczy netto wysylki
                 if ( $infv['wartosc'] > 0 && $vatOpakowanie[0] > 0 ) {
                      $infp['wartosc'] = $infp['wartosc'] / ((100 + $vatOpakowanie[0]) / 100);
                 }
                 //
                 $GLOBALS['db']->close_query($sqlOpakowanieVat);
                 unset($infv); 
                 
             }           
             //
             $przelicznik = 1 / $_SESSION['domyslnaWaluta']['przelicznik'];
             $marza = 1 + ( $_SESSION['domyslnaWaluta']['marza']/100 );         
             //
             $KosztOzdobnegoOpakowania = number_format( round((((float)$infp['wartosc'] / $przelicznik) * $marza), CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, '.', '');
             //
             $GLOBALS['db']->close_query($sqlOpakowanie);
             unset($infp, $przelicznik, $marza); 
             //
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);    

    // parametry do ustalenia podsumowania zamowienia
    $podsumowanie = new Podsumowanie();
    
    // znizki od ilosci w koszyku
    $ZnizkiIlosciKoszyk = '';
    foreach ( $podsumowanie->WlaczonePodsumowanie() as $znizkaTmp ) {
        //
        if ( $znizkaTmp['klasa'] == 'ot_shopping_discount' ) {
             //
             $ZnizkiIlosciKoszyk = $znizkaTmp['parametry'];
             //
        }
        //
    }
    if ( isset($ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_KUPON']) && $ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_KUPON'] == 'nie' && isset($_SESSION['kuponRabatowy']) ) {
         //
         $ZnizkiIlosciKoszyk = '';
         //
    } else {
         //
         if ( is_array($ZnizkiIlosciKoszyk) ) {
          
             $TablicaZnizek = preg_split("/[:;]/" , (string)$ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_PROGI_ZNIZEK']);

             // jezeli sa grupy klientow
             if ( $ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW'] != '' ) {
                  //
                  $PodzielGrupe = explode(';', (string)$ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW']);
                  //
                  if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                      //
                      if ( !in_array((int)$_SESSION['customers_groups_id'], $PodzielGrupe) ) {
                          //
                          $TablicaZnizek = array();
                          //
                      }
                      //
                 } else {
                      //
                      if ( !in_array('0', $PodzielGrupe) ) {
                          //
                          $TablicaZnizek = array();
                          //
                      }
                      //                 
                 }
                 //
                 unset($PodzielGrupe);
                 //
             }
             //
             // ustalenie wartosci lub ilosci produktow w zamowieniu
             $WartoscKoszykaZnizki = 0;
             $IloscKoszykaZnizki = 0;
             //         
             if ( count($TablicaZnizek) > 0 ) {

                 foreach ( $_SESSION['koszyk'] as $ProduktZnizki ) {
                      //
                      $WylaczoneRabaty = false;
                      //
                      // czy produkt nie jest wylaczony z rabatow
                      $ProduktTmp = new Produkt( Funkcje::SamoIdProduktuBezCech( $ProduktZnizki['id'] ) );
                      //
                      if ( $ProduktTmp->info['wylaczone_rabaty'] == 'tak' ) {
                           $WylaczoneRabaty = true;
                      }
                      //
                      unset($ProduktTmp);
                      //
                      if ( $ProduktZnizki['promocja'] == 'nie' || ( $ProduktZnizki['promocja'] == 'tak' && $ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_PROMOCJE'] == 'tak' ) ) {
                          //
                          if ( $WylaczoneRabaty == false ) {
                               //
                               $WartoscKoszykaZnizki += $ProduktZnizki['cena_brutto'] * $ProduktZnizki['ilosc'];
                               $IloscKoszykaZnizki += $ProduktZnizki['ilosc'];
                               //
                          }
                      }
                      //
                      unset($WylaczoneRabaty);
                      //
                 }         

                 for ( $i = 0, $c = count($TablicaZnizek); $i < $c; $i+=2 ) {
                      //
                      $TablicaZnizek[$i] = $GLOBALS['waluty']->PokazCeneBezSymbolu($TablicaZnizek[$i],'',true);

                      // jezeli znizka jest zalezna od wartosci koszyka
                      if ( $ZnizkiIlosciKoszyk['ZNIZKI_KOSZYKA_SPOSOB'] == 'kwota' ) {
                           //
                           if ( $WartoscKoszykaZnizki <= $TablicaZnizek[$i] ) {
                                //
                                $IleBrakuje = ( $TablicaZnizek[$i] - $WartoscKoszykaZnizki ) + 0.01;
                                $IleBrakujeWartosc = $GLOBALS['waluty']->WyswietlFormatCeny( $IleBrakuje, $_SESSION['domyslnaWaluta']['id'], true, false );
                                //
                                $TxtTmp = str_replace('{KWOTA}', (string)$IleBrakujeWartosc, (string)$GLOBALS['tlumacz']['INFO_ZNIZKA_KOSZYK_OD_WARTOSCI']);
                                $TxtTmp = str_replace('{RABAT}', (string)$TablicaZnizek[$i+1], (string)$TxtTmp);
                                //
                                $DodatkoweInformacje['ZnizkaOdIlosciWartosci'] = $TxtTmp;
                                //
                                unset($TxtTmp, $IleBrakuje, $IleBrakujeWartosc);
                                //
                                break;
                           }
                           //
                        } else {
                          //
                          if ( $IloscKoszykaZnizki <= $TablicaZnizek[$i] ) {
                               //
                               $IleBrakuje = ( $TablicaZnizek[$i] - $IloscKoszykaZnizki ) + 1;
                               //
                               $TxtTmp = str_replace('{ILOSC}', (string)$IleBrakuje, (string)$GLOBALS['tlumacz']['INFO_ZNIZKA_KOSZYK_OD_ILOSCI']);
                               $TxtTmp = str_replace('{RABAT}', (string)$TablicaZnizek[$i+1], (string)$TxtTmp);
                               //
                               $DodatkoweInformacje['ZnizkaOdIlosciWartosci'] = $TxtTmp;
                               //
                               unset($TxtTmp, $IleBrakuje);
                               //                       
                               break;
                               //
                          }
                          //
                      }
                      //
                 }   
             }
             //
             unset($TablicaZnizek, $WartoscKoszykaZnizki, $IloscKoszykaZnizki);
             //
        }
         //
    }
    
    $PodsumowanieZamowienia = $podsumowanie->Generuj();

    // punkty klienta
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
    
      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        
        $punkty = new Punkty((int)$_SESSION['customer_id'], true);
        
        // jezeli jest wylaczone realizacja punktow jezeli w koszyku sa produkty za PUNKTY
        if ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() == 0 || ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() > 0 && SYSTEM_PUNKTOW_KUPOWANIE_PRODUKTOW == 'tak' ) ) {
          
          $IloscPunktowDoWykorzystania = 0;
          
          if ( $punkty->suma >= SYSTEM_PUNKTOW_MIN_ZAMOWIENIA && $GLOBALS['koszykKlienta']->KoszykWartoscProduktow() >= $GLOBALS['waluty']->PokazCeneBezSymbolu(SYSTEM_PUNKTOW_MIN_WARTOSC_ZAMOWIENIA,'',true) ) {
          
            $DodatkoweInformacje['WartoscPunktowKlienta'] = $punkty->suma;
            $DodatkoweInformacje['InfoPunktyKlienta'] = true;
            $DodatkoweInformacje['WartoscPunktowKlientaKwota'] = $punkty->wartosc;
            $DodatkoweInformacje['WartoscMaksymalnaPunktowKwota'] = $punkty->wartosc_maksymalna_kwota;

            $InfoPunkty = str_replace( '{WARTOSC_LACZNA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($punkty->wartosc, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', (string)$GLOBALS['tlumacz']['INFO_PUNKTY'] );
            $InfoPunkty = str_replace( '{WARTOSC_MAKSYMALNA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($punkty->wartosc_maksymalna_kwota, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', (string)$InfoPunkty );

            $WartoscZamowieniaDoPunktow = 0;
            foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {
              if ( $podsumowanie['prefix'] == '1' ) {
                if ( $podsumowanie['klasa'] == 'ot_shipping' ) {
                  $WartoscZamowieniaDoPunktow;
                } else {
                  $WartoscZamowieniaDoPunktow += $podsumowanie['wartosc'];
                }
              } elseif ( $podsumowanie['prefix'] == '0' ) {
                $WartoscZamowieniaDoPunktow -= $podsumowanie['wartosc'];
              }
            }
            
            // wykluczenie produktow z zakupu
            $WartoscWykluczenia = 0;
            foreach ( $_SESSION['koszyk'] as $rekord ) {
              if (isset($rekord['zakup_za_punkty']) && $rekord['zakup_za_punkty'] == 'nie') {
                  $WartoscWykluczenia += $rekord['cena_brutto']*$rekord['ilosc'];
              }
            }        
            $WartoscZamowieniaDoPunktow = $WartoscZamowieniaDoPunktow - $WartoscWykluczenia;

            // wartosc punktow klienta
            $WartoscPunktowDoWykorzystania = $punkty->wartosc;

            // jezeli wartosc punktow klienta jest wieksza niz wartosc zamawianych produktow
            if ( $WartoscPunktowDoWykorzystania > $WartoscZamowieniaDoPunktow ) {
              $WartoscPunktowDoWykorzystania = $WartoscZamowieniaDoPunktow;
            }

            // jezeli wartosc punktow klienta jest wieksza niz maks wartosc punktow do wykorzystania w jednym zamowieniu
            if ( $WartoscPunktowDoWykorzystania > $punkty->wartosc_maksymalna_kwota ) {
              $WartoscPunktowDoWykorzystania = $punkty->wartosc_maksymalna_kwota;
            }

            $InfoPunktyDoWykorzystania = str_replace( '{KWOTA_PUNKTOW_W_ZAMOWIENIU}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($WartoscPunktowDoWykorzystania, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', (string)$GLOBALS['tlumacz']['INFO_PUNKTY_DO_WYKORZYSTANIA'] );

            // ilosc punktow klienta
            $IloscPunktowDoWykorzystania = $punkty->suma;

            // jezeli przeliczona ilosc punktow klienta jest wieksza niz wylicona z wartosci zamowienia
            $TmpMarza = 1;
            if ( $_SESSION['domyslnyJezykStaly']['id'] != $_SESSION['domyslnaWaluta']['id'] ) {
                 $TmpMarza = (100 + $_SESSION['domyslnaWaluta']['marza']) / 100;
            }
            //
            $WartoscPktTmp = ((($WartoscZamowieniaDoPunktow/$_SESSION['domyslnaWaluta']['przelicznik']) / $TmpMarza) * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU);            
            if ( $IloscPunktowDoWykorzystania > $WartoscPktTmp ) {
              $IloscPunktowDoWykorzystania = ceil($WartoscPktTmp);
            }
            unset($WartoscPktTmp);

            // jezeli ilosc punktow klienta jest wieksza niz maks ilosc punktow do wykorzystania w jednym zamowieniu
            if ( $IloscPunktowDoWykorzystania > SYSTEM_PUNKTOW_MAX_ZAMOWIENIA ) {
              $IloscPunktowDoWykorzystania = SYSTEM_PUNKTOW_MAX_ZAMOWIENIA;
            }

            // jezeli ilosc punktow jest wieksza niz maksymalna procentowa wartosc zamowienia 
            if ( (float)SYSTEM_PUNKTOW_MAX_ZAMOWIENIA_PROCENT < 100 ) {
              //            
              $ZawartoscKoszykaPkt = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
              //
              $MaksymalnaWartoscPktProcent = $ZawartoscKoszykaPkt['brutto'] * ((float)SYSTEM_PUNKTOW_MAX_ZAMOWIENIA_PROCENT / 100);
              if ( $WartoscPunktowDoWykorzystania > $MaksymalnaWartoscPktProcent ) {
                 $IloscPunktowDoWykorzystania = ceil((($MaksymalnaWartoscPktProcent/$_SESSION['domyslnaWaluta']['przelicznik']) / $TmpMarza) * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU);
                 //
                 $WartoscTmp = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)($IloscPunktowDoWykorzystania / SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU), '' ,true);
                 $InfoPunktyDoWykorzystania = str_replace( '{KWOTA_PUNKTOW_W_ZAMOWIENIU}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($WartoscTmp, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', (string)$GLOBALS['tlumacz']['INFO_PUNKTY_DO_WYKORZYSTANIA'] );
                 unset($WartoscTmp);
                 //
              }
              //
              unset($ZawartoscKoszykaPkt);
              //
              if ( $IloscPunktowDoWykorzystania < (float)SYSTEM_PUNKTOW_MIN_ZAMOWIENIA ) {
                   $DodatkoweInformacje['InfoPunktyKlienta'] = false;
              }
            }
              
            $InfoPunktyDoWykorzystania = str_replace( '{ILOSC_PUNKTOW_W_ZAMOWIENIU}', '<b>'.$IloscPunktowDoWykorzystania.'</b>', (string)$InfoPunktyDoWykorzystania );

            $DodatkoweInformacje['WartoscPunktowZamowienia'] = $IloscPunktowDoWykorzystania;
            
          }
          
          // wlaczenie informacji o zrezygnowaniu z punktow jezeli ilosc dostepnych punktow jest ponizej 0 a byly wczesniej aktywowane
          if ( $punkty->suma <= 0 && isset($_SESSION['punktyKlienta']) ) {
               $DodatkoweInformacje['InfoPunktyKlienta'] = true;
          }          
          if ( $IloscPunktowDoWykorzystania <= 0 && !isset($_SESSION['punktyKlienta']) ) {
               $DodatkoweInformacje['InfoPunktyKlienta'] = false;
          }  
          
        }
        
      }
      
    }

    $BylKalkulator = false;

    // kalkulator ratalny Santander Consumer
    $KalkulatorSantander = '<div id="RataSantander"></div>';
    if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_santander', $TablicaPlatnosci) ) {
      $KalkulatorSantander = '<div id="RataSantander"><a onclick="PoliczRateSantander('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_koszyk.png" alt="Raty" /></a></div>';
      $BylKalkulator = true;
    }

    // kalkulator ratalny Lukas

    $KalkulatorLukas = '<div id="RataLukas"></div>';
    if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_lukas', $TablicaPlatnosci) ) {

       $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_LUKAS_NUMER_SKLEPU'";
       $sqlp = $GLOBALS['db']->open_query($zap);
       //
       if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
        //
        $infop = $sqlp->fetch_assoc();
        //
        $KalkulatorLukas = '<div id="RataLukas"><a onclick="PoliczRateLukas('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="https://ewniosek.credit-agricole.pl/eWniosek/button/img.png?creditAmount='.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].'&posId='.$infop['wartosc'].'&imgType=1" alt="Raty" /></a></div>';
        $BylKalkulator = true;
       }
       //
       unset($infop);
       //
       //
       $GLOBALS['db']->close_query($sqlp); 
       unset($zap);    

    }

    // kalkulator ratalny MBANK
    $KalkulatorMbank = '<div id="RataMbank"></div>';
    if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_mbank', $TablicaPlatnosci) ) {
      $KalkulatorMbank = '<div id="RataMbank"><a onclick="PoliczRateMbank('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_mbank_koszyk.png" alt="MBank" /></a></div>';
      $BylKalkulator = true;  
    }
    
    // kalkulator ratalny TPay
    $KalkulatorTPay = '<div id="RataTPay"></div>';    
    if ( isset($_SESSION['podsumowanieZamowienia']) && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] >= 300 && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] < 20000 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {      
         //
         if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_transferuj', $TablicaPlatnosci) ) {
            //
            $zap = "SELECT kod, wartosc FROM modules_payment_params";
            $sqlp = $GLOBALS['db']->open_query($zap);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                //
                $raty_tpay = 'nie';
                $rodzaj = '';
                //
                while ($infop = $sqlp->fetch_assoc()) {
                    //
                    if ( $infop['kod'] == 'PLATNOSC_TPAY_RATY_KALKULATOR' ) {
                         $raty_tpay = $infop['wartosc'];
                    }
                    if ( $infop['kod'] == 'PLATNOSC_TPAY_RATY_RODZAJ' ) {
                         $rodzaj = $infop['wartosc'];
                    }                    
                    //
                }
                //
                if ( $rodzaj != '' && $raty_tpay == 'tak' ) {
                     //
                     $KalkulatorTPay = '<div id="RataTPay"><a onclick="PoliczRateTPay(\'' . $rodzaj . '\',' . ((float)$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] * 100) . ');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/tpay_raty.png" alt="TPAY Raty" /></a></div>';
                     $BylKalkulator = true;   
                     //
                }
                //
            }
            //
            $GLOBALS['db']->close_query($sqlp); 
            unset($zap);   
         }
         //
    }    

    // kalkulator ratalny TPay REST
    if ( isset($_SESSION['podsumowanieZamowienia']) && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] >= 300 && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] < 20000 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {      
         //
         if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_tpay', $TablicaPlatnosci) ) {
            //
            $zap = "SELECT kod, wartosc FROM modules_payment_params";
            $sqlp = $GLOBALS['db']->open_query($zap);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                //
                $raty_tpay = 'nie';
                $rodzaj = '';
                //
                while ($infop = $sqlp->fetch_assoc()) {
                    //
                    if ( $infop['kod'] == 'PLATNOSC_TPAY_REST_RATY_KALKULATOR' ) {
                         $raty_tpay = $infop['wartosc'];
                    }
                    if ( $infop['kod'] == 'PLATNOSC_TPAY_REST_RATY_RODZAJ' ) {
                         $rodzaj = $infop['wartosc'];
                    }                    
                    //
                }
                //
                if ( $rodzaj != '' && $raty_tpay == 'tak' ) {
                     //
                     $KalkulatorTPay = '<div id="RataTPay"><a onclick="PoliczRateTPay(\'' . $rodzaj . '\',' . ((float)$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] * 100) . ');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/tpay_raty.png" alt="TPAY Raty" /></a></div>';
                     $BylKalkulator = true;   
                     //
                }
                //
            }
            //
            $GLOBALS['db']->close_query($sqlp); 
            unset($zap);   
         }
         //
    }    

    // kalkulator ratalny iLeasing
    $KalkulatorIleasing = '<div id="RataIleasing"></div>';
    if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_ileasing', $TablicaPlatnosci) ) {
      $WartoscKoszykaNetto = 0;
      foreach ( $_SESSION['koszyk'] as $ProduktLeasing ) {
        //
        $WartoscKoszykaNetto += $ProduktLeasing['cena_netto'] * $ProduktLeasing['ilosc'];
        //
      }         
      $KalkulatorIleasing = '<div id="RataIleasing"><a onclick="PoliczRateIleasing('.$WartoscKoszykaNetto.');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_ileasing_koszyk.png" alt="iLeasing" /></a></div>';
      $BylKalkulator = true;
      unset($WartoscKoszykaNetto);
    }

    // kalkulator ratalny iRaty
    $KalkulatorIraty = '<div id="RataIraty"></div>';
    if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_iraty', $TablicaPlatnosci) ) {
      $WartoscKoszykaBrutto = 0;
      foreach ( $_SESSION['koszyk'] as $ProduktIraty ) {
        //
        $WartoscKoszykaBrutto += $ProduktIraty['cena_brutto'] * $ProduktIraty['ilosc'];
        //
      }         
      $KalkulatorIraty = '<div id="RataIraty"><a onclick="PoliczRateIraty('.$WartoscKoszykaBrutto.');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_iraty_koszyk.png" alt="iRaty" /></a></div>';
      $BylKalkulator = true;
      unset($WartoscKoszykaBrutto);
    }

    // kalkulator ratalny PayU Raty
    $KalkulatorPayuRaty = '<div id="RataPayU"></div>';
    if ( isset($_SESSION['podsumowanieZamowienia']) && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] >= 300 && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] < 20000 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {
        if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu', $TablicaPlatnosci) ) {
            //
            $Wiget = false;
            $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_RATY_WLACZONE'";
            $sqlp = $GLOBALS['db']->open_query($zap);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                //
                $infop = $sqlp->fetch_assoc();
                //
                if ( $infop['wartosc'] == 'tak' ) {

                    $zap_widget = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_RATY_KALKULATOR'";
                    $sqlp_widget = $GLOBALS['db']->open_query($zap_widget);

                    if ((int)$GLOBALS['db']->ile_rekordow($sqlp_widget) > 0) {

                        $infop_widget = $sqlp_widget->fetch_assoc();

                        if ( $infop_widget['wartosc'] == 'tak' ) {
                            $Wiget = true;
                        }
                    }
                    $GLOBALS['db']->close_query($sqlp_widget); 
                    unset($zap_widget);    

                    if ( $Wiget == true ) {
                        $KalkulatorPayuRaty = '<script src="https://static.payu.com/res/v2/widget-mini-installments.js"></script>
                                               <style>#PayuKoszykImg, #RatyPayuWidget { display:none !important; } .RatyP span { display:inline-block !important; margin:0px !important; } .RatyP a { line-height:1.7 !important; padding:0px !important; }</style>                                     
                                               <script> $(document).ready(function() { PoliczRatePauYRaty('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].'); }); </script>
                                               <div id="RataPayU" style="margin:20px 10px 10px 10px"><div style="margin-bottom:5px;display:block"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_payu_koszyk.png" alt="PayU" /></div><div id="RataPayUKoszyk"></div></div>';
                        $BylKalkulator = true;
                    }
                }
                //
                unset($infop);
                //
            }
            //
            $GLOBALS['db']->close_query($sqlp); 
            unset($zap);    
        }
    }

    // kalkulator ratalny PayU Raty REST
    if ( isset($_SESSION['podsumowanieZamowienia']) && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] >= 300 && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] < 20000 && $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {

        if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu_rest', $TablicaPlatnosci) ) {
            //
            $Wiget = false;
            $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_REST_RATY_WLACZONE'";
            $sqlp = $GLOBALS['db']->open_query($zap);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
                //
                $infop = $sqlp->fetch_assoc();
                //
                if ( $infop['wartosc'] == 'tak' ) {

                    $zap_widget = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_REST_RATY_KALKULATOR'";
                    $sqlp_widget = $GLOBALS['db']->open_query($zap_widget);

                    if ((int)$GLOBALS['db']->ile_rekordow($sqlp_widget) > 0) {

                        $infop_widget = $sqlp_widget->fetch_assoc();

                        if ( $infop_widget['wartosc'] == 'tak' ) {
                            $Wiget = true;
                        }
                    }
                    $GLOBALS['db']->close_query($sqlp_widget); 
                    unset($zap_widget);    

                    if ( $Wiget == true ) {

                        $KalkulatorPayuRaty = '<script src="https://static.payu.com/res/v2/widget-mini-installments.js"></script>
                                               <style>#PayuKoszykImg, #RatyPayuWidget { display:none !important; } .RatyP span { display:inline-block !important; margin:0px !important; } .RatyP a { line-height:1.7 !important; padding:0px !important; }</style>                                     
                                               <script> $(document).ready(function() { PoliczRatePauYRaty('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].'); }); </script>
                                               <div id="RataPayU" style="margin:20px 10px 10px 10px"><div style="margin-bottom:5px;display:block"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_payu_koszyk.png" alt="PayU" /></div><div id="RataPayUKoszyk"></div></div>';
                        $BylKalkulator = true;
                    }
                }
                //
                unset($infop);
                //
            }
            //
            $GLOBALS['db']->close_query($sqlp); 
            unset($zap);    
        }
    }

    // kalkulator ratalny BGZ
    $KalkulatorBgz = '<div id="RataBgz"></div>';
    if ( isset($TablicaPlatnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_bgz', $TablicaPlatnosci) ) {
      $KalkulatorBgz = '<div id="RataBgz"><a onclick="PoliczRateBgz('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_bgz_koszyk.png" alt="BNP Paribas" /></a></div>';
      $BylKalkulator = true;
    }

    if ( isset($ProgBezplatnejWysylki) && $ProgBezplatnejWysylki > 0 ) {
        //
        if ( isset($_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc']) ) {
             //
             $WartoscZamowienia = $_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc'];
             //       
             // jezeli musi pominac promocje
             if ( $DarmowaWysylkaPromocje == 'nie' ) {
                 //
                 $WartoscZamowienia -= $WartoscProduktowPromocje;
                 //
             }
             //
             if ( $WartoscZamowienia > $ProgBezplatnejWysylki ) {
                //
                $BezplatnaDostawa = '';
                //
             } else { 
                //
                $BezplatnaDostawa = str_replace( '{KWOTA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($ProgBezplatnejWysylki, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', (string)$GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA'] );
                //
                if ( $DarmowaWysylkaPromocje == 'nie' ) {
                     $BezplatnaDostawa .= ' ' . $GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA_BEZ_PROMOCJI'];
                }
                //
                // ile brakuje
                $IleBrakuje = $ProgBezplatnejWysylki - $WartoscZamowienia;
                //
                if ( $IleBrakuje > 0 ) {
                     //
                     $ProcentSuwak = (int)(($WartoscZamowienia/$ProgBezplatnejWysylki) * 100);
                     //
                     $BezplatnaDostawa .= '<div class="WysylkaSuwak"><div class="WysylkaSuwakTlo"><div class="WysylkaSuwakWartosc" style="width:' . $ProcentSuwak . '%"></div></div></div>';
                     $BezplatnaDostawa .= '<div class="WysylkaIleBrakuje">' . (string)$GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA_BRAKUJE'] . ' <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($IleBrakuje, $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                     //
                     unset($ProcentSuwak);
                     //
                     $DodatkoweInformacje['InfoWysylkaDarmo'] = true;
                     //
                }
                //
                unset($IleBrakuje);
                //
             }
             //
             unset($WartoscZamowienia);
             //
          } else {
             //
             $BezplatnaDostawa = '';
             //       
        }
    } else {
        //
        $BezplatnaDostawa = '';
        //
    }
    
    unset($WartoscProduktowPromocje);

}
    
$Zalogowany = 'nie';
if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
     $Zalogowany = 'tak';
}

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $ProduktyKoszyka, $DodatkoweInformacje, $Zalogowany, $BrakMagazyn);
//
unset($ProduktyKoszyka, $Zalogowany, $BrakMagazyn);

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_KOSZYK']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$tpl->dodaj('__CSS_PLIK', ',listingi' . ((INTEGRACJA_EASYPROTECT_WLACZONY == 'tak') ? ',easyprotect' : ''));

if ( isset($GLOBALS['koszykKlienta']) && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {
  
    // modul wysylek i platnosci
    
    $srodek->dodaj('__WYBOR_PANSTWA', $ListaRozwijanaPanstw);

    $srodek->dodaj('__WYBOR_WYSYLKI', $ListaRadioWysylek);

    $srodek->dodaj('__WYBOR_PLATNOSCI', $ListaRadioPlatnosci);

    $srodek->dodaj('__KOSZT_WYSYLKI', $CalkowityKosztWysylki);

    $srodek->dodaj('__PODSUMOWANIE_ZAMOWIENIA', $PodsumowanieZamowienia);

    $srodek->dodaj('__PODSUMOWANIE_INFORMACJA', $GLOBALS['tlumacz']['INFO_WARTOSC_ZAMOWIENIA_PO_ZALOGOWANIU']);

    $srodek->dodaj('__DISPLAY_NONE', $UkryjPrzycisk);
    
    $srodek->dodaj('__WYSWIETL_KUPON', $UkryjKupon);
    
    $srodek->dodaj('__WYSWIETL_KUPON_AKTYWCJA', 'style="display:none"');
    $srodek->dodaj('__WYSWIETL_KUPON_USUNIECIE', 'style="display:none"');
    if ( !isset($_SESSION['kuponRabatowy']) ) {
         $srodek->dodaj('__WYSWIETL_KUPON_AKTYWCJA', '');
    } else {
         $srodek->dodaj('__WYSWIETL_KUPON_USUNIECIE', '');
    }
    
    $srodek->dodaj('__WYSWIETL_OZDOBNE_OPAKOWANIE', $UkryjOzdobneOpakowanie);
    
    $KosztOpakowania = $GLOBALS['waluty']->FormatujCene($KosztOzdobnegoOpakowania, 0, 0, $_SESSION['domyslnaWaluta']['id'], true);
    $srodek->dodaj('__WARTOSC_OPAKOWANIA_OZDOBNEGO', $KosztOpakowania['brutto']);
    unset($KosztOpakowania);
    
    $srodek->dodaj('__KALKULATOR_SANTANDER', $KalkulatorSantander);
    $srodek->dodaj('__KALKULATOR_LUKAS', $KalkulatorLukas);
    $srodek->dodaj('__KALKULATOR_MBANK', $KalkulatorMbank);
    $srodek->dodaj('__KALKULATOR_TPAY', $KalkulatorTPay);
    $srodek->dodaj('__KALKULATOR_PAYURATY', $KalkulatorPayuRaty);
    $srodek->dodaj('__KALKULATOR_BGZ', $KalkulatorBgz);
    $srodek->dodaj('__KALKULATOR_ILEASING', $KalkulatorIleasing);
    $srodek->dodaj('__KALKULATOR_IRATY', $KalkulatorIraty);

    $srodek->dodaj('__KALKULATOR_CSS','');
    if ( $BylKalkulator == false ) {
         $srodek->dodaj('__KALKULATOR_CSS',' style="display:none"');
    }
    
    $srodek->dodaj('__CSS_PDF_KOSZYK','');
    if ( PDF_KOSZYK_POBRANIE_PDF == 'nie' ) {
         $srodek->dodaj('__CSS_PDF_KOSZYK',' style="display:none"');
    }  

    $srodek->dodaj('__CSS_KRAJ_DOSTAWY','');
    $srodek->dodaj('__CSS_WYBOR_PANSTWA','');
    if ( WYBOR_KRAJU_DOSTAWY == 'nie' ) {
         $srodek->dodaj('__CSS_KRAJ_DOSTAWY',' style="display:none"');
         $srodek->dodaj('__CSS_WYBOR_PANSTWA',' style="display:none"');
    }

    $srodek->dodaj('__BEZPLATNA_DOSTAWA', $BezplatnaDostawa);
    
    // nastepna strona zamowienia
    $isHTTPS = false;
    if ( WLACZENIE_SSL == 'tak' ) {
        $isHTTPS = true;
    }

    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) {
      $ZamowienieNastepnyKrok = ( $isHTTPS ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/zamowienie-potwierdzenie.html';
    } else {
      $ZamowienieNastepnyKrok = ( $isHTTPS ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/zamowienie-logowanie.html';
    }  

    $srodek->dodaj('__ZAMOWIENIE_NASTEPNY_KROK', $ZamowienieNastepnyKrok);
    
    unset($ZamowienieNastepnyKrok, $isHTTPS);
    
    // produkty gratisowe
    $ListaProduktowGratisowych = '';
    //
    $Gratisy = Gratisy::TablicaGratisow( 'tak' );
    //
    if ( count($Gratisy) > 0 ) {
        ob_start();
        
        if (in_array( 'listing_gratisy.php', $Wyglad->PlikiListingiLokalne )) {
              require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_gratisy.php');
            } else {
              require('listingi/listing_gratisy.php');
        }

        $ListaProduktowGratisowych = ob_get_contents();
        ob_end_clean();    
    }
    //
    $srodek->dodaj('__LISTING_PRODUKTY_GRATISOWE', $ListaProduktowGratisowych);  
    unset($ListaProduktowGratisowych, $Gratisy);
    
    //  
    
    // minimalne zamowienie dla grupy klientow
    $srodek->dodaj('__MINIMALNE_ZAMOWIENIE', '');

    $MinimalneZamowienieGrupy = Klient::MinimalneZamowienie();

    if ( $MinimalneZamowienieGrupy > 0 ) {

        $MinZamowienie = $GLOBALS['waluty']->PokazCeneBezSymbolu($MinimalneZamowienieGrupy,'',true);
        $WartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

        if ( $WartoscKoszyka['brutto'] < $MinZamowienie ) {
             //
             $srodek->dodaj('__MINIMALNE_ZAMOWIENIE', '<strong>' .  $GLOBALS['tlumacz']['MINIMALNE_ZAMOWIENIE'] . ' <span>' . $GLOBALS['waluty']->WyswietlFormatCeny($MinZamowienie, $_SESSION['domyslnaWaluta']['id'], true, false) . '</span></strong>');
             $srodek->dodaj('__DISPLAY_NONE', 'style="display:none"');
             //
        }
        unset($MinZamowienie, $WartoscKoszyka);
        
    }   

    unset($MinimalneZamowienieGrupy); 

    // link uzywany w koszyku do przycisku kontynuuj zakupy
    $srodek->dodaj('__LINK_POPRZEDNIEJ_STRONY', $_SESSION['stat']['przed_koszykiem']);
    
    // informacja o zbyt malej ilosci punktow do zlozenia zamowienia
    $srodek->dodaj('__ZBYT_MALA_ILOSC_PUNKTOW', '');
    
    // zabezpieczenie przejscia do zamowienie-potwierdzenie
    $_SESSION['zloz_zamowienie_pkt'] = true;

    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
      
        if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
      
            // informacja (okno) z mozliwoscia aktywowania punktow za zamowienie
            
            // jezeli jest wylaczone realizacja punktow jezeli w koszyku sa produkty za PUNKTY
            if ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() == 0 || ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() > 0 && SYSTEM_PUNKTOW_KUPOWANIE_PRODUKTOW == 'tak' ) ) {

                if ( $punkty->suma >= SYSTEM_PUNKTOW_MIN_ZAMOWIENIA ) {
                  
                  if ( isset($DodatkoweInformacje['InfoPunktyKlienta']) && $DodatkoweInformacje['InfoPunktyKlienta'] ) {
                    
                    $srodek->dodaj('__INFO_PUNKTY', $InfoPunkty);
                    $srodek->dodaj('__INFO_PUNKTY_DO_WYKORZYSTANIA', $InfoPunktyDoWykorzystania);
                    
                  }
                  
                }
                
                if ( isset($_SESSION['punktyKlienta']) ) {
                  
                  $InfoPunktyWykorzystane = str_replace( '{ILOSC_PUNKTOW}', '<b>' . (($_SESSION['punktyKlienta']['punkty_ilosc'] > $DodatkoweInformacje['WartoscPunktowKlienta']) ? $DodatkoweInformacje['WartoscPunktowKlienta'] : $_SESSION['punktyKlienta']['punkty_ilosc']) . '</b>', (string)$GLOBALS['tlumacz']['INFO_PUNKTY_WYKORZYSTANE'] );
                  $srodek->dodaj('__INFO_PUNKTY_WYKORZYSTANE', $InfoPunktyWykorzystane);
                  unset($InfoPunktyWykorzystane);
                  
                }
                
            }      
        
            // sprawdza czy jest wystarczajac ilosc punktow do zlozenia zamowienia
      
            // punkty wykorzystane do zamowienia
            $PktWykorzystane = 0;
            
            if ( isset($_SESSION['punktyKlienta']['punkty_ilosc']) ) {
                 $PktWykorzystane = $_SESSION['punktyKlienta']['punkty_ilosc'];
            }
                 
            if ( $punkty->suma_punktow_klienta < $PktWykorzystane + $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() ) {

                 $srodek->dodaj('__ZBYT_MALA_ILOSC_PUNKTOW', '<strong>' . $GLOBALS['tlumacz']['ZBYT_MALA_ILOSC_PUNKTOW'] . '</strong>');
                 $srodek->dodaj('__DISPLAY_NONE', 'style="display:none"');
                 
                 $_SESSION['zloz_zamowienie_pkt'] = false;

            }             
            
            unset($PktWykorzystane);
        
        }
        
    }
    
    // przycisk zapisania do koszyka
    if ((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') || KOSZYK_ZAPIS_ZALOGOWANI == 'nie') {
        //
        $srodek->dodaj('__CSS_ZAPISZ_KOSZYK', '');
        //
      } else {
        //
        $srodek->dodaj('__CSS_ZAPISZ_KOSZYK', 'style="display:none"');
        //
    }  
    
    $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

    // wartosc koszyka
    $srodek->dodaj('__WARTOSC_KOSZYKA', $GLOBALS['waluty']->PokazCene($ZawartoscKoszyka['brutto'], $ZawartoscKoszyka['netto'], 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false));

    // waga produktow koszyka
    $srodek->dodaj('__WAGA_KOSZYKA', number_format((float)$ZawartoscKoszyka['waga'], 4, ',', ''));
    
    // ukrywanie wagi o wartosci 0
    $srodek->dodaj('__WAGA_KOSZYKA_CSS',''); 
    if ( (float)$ZawartoscKoszyka['waga'] == 0 ) {
          $srodek->dodaj('__WAGA_KOSZYKA_CSS','style="display:none"'); 
    }   
    
    // znizki od ilosci w koszyku
    $srodek->dodaj('__INFO_ZNIZKA_OD_WARTOSCI_ILOSCI', '');
    if ( isset($DodatkoweInformacje['ZnizkaOdIlosciWartosci']) ) {
         //
         $srodek->dodaj('__INFO_ZNIZKA_OD_WARTOSCI_ILOSCI', $DodatkoweInformacje['ZnizkaOdIlosciWartosci']);
         //
    }

    if ( KOSZYK_POKAZ_CZAS_WYSYLKI == 'tak' ) {

        // jezeli wszystkie produkty mialy czas wysylki
        if ( $MaksymalnyCzasWysylkiProdukt == true ) {
             $MaksymalnyCzasWysylki = str_replace('{0}', $MaksymalnyCzasWysylki, $GLOBALS['tlumacz']['SZACOWANY_CZAS_WYSYLKI']);
        }
        // maksymalny czas wysylki
        $srodek->dodaj('__MAKSYMALNY_CZAS_WYSYLKI', '');
        if ( $MaksymalnyCzasWysylkiProdukt == true ) {
             $srodek->dodaj('__MAKSYMALNY_CZAS_WYSYLKI', $MaksymalnyCzasWysylki);
        }
        unset($MaksymalnyCzasWysylki, $MaksymalnyCzasWysylkiProdukt);

    }
    
    unset($ZawartoscKoszyka);

}

// integracja z kod Google remarketing dynamiczny ORAZ modul Google Analytics
$wynikGoogle = IntegracjeZewnetrzne::GoogleAnalyticsRemarketingKoszyk();
$tpl->dodaj('__GOOGLE_KONWERSJA', $wynikGoogle['konwersja']);
$tpl->dodaj('__GOOGLE_ANALYTICS', $wynikGoogle['analytics']);
unset($wynikGoogle);

// integracja z TRUSTISTO
$tpl->dodaj('__TRUSTISTO', IntegracjeZewnetrzne::TrustistoStart('koszyk') . IntegracjeZewnetrzne::TrustistoKoszyk());

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>