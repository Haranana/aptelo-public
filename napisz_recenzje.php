<?php

// plik
$WywolanyPlik = 'napisz_recenzje';

include('start.php');

if ( RECENZJE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

if ( isset($_GET['produkt']) && (int)$_GET['produkt'] > 0 ) {
     //
     $_GET['id'] = (int)$_GET['produkt'];
     //
}

if ( isset($_GET['zamowienie']) && isset($_GET['recenzja']) && isset($_GET['id']) && (int)$_GET['id'] > 0 && isset($_GET['nr']) && (int)$_GET['nr'] > 0) {
  
    $zamowienie = new Zamowienie((int)$_GET['nr']);
    
    // token zabezpieczajacy
    
    $hashKod = '';
    
    if ( count($zamowienie->info) > 0 ) {
    
        $hashKod = hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
        
    }
    
    if ( $_GET['zamowienie'] != $hashKod ) {
         //
         $Produkt = new Produkt( (int)$_GET['id'] ); 
         //
         if ($Produkt->CzyJestProdukt == true) {
             //
             Funkcje::PrzekierowanieURL( $Produkt->info['adres_seo'] ); 
             //
         } else {
             //
             Funkcje::PrzekierowanieURL('brak-strony.html'); 
             //
         }
         //
         unset($Produkt);
         //
    }
    
    unset($hashKod);
    
    // dane recenzji
    
    $BladDanychRecenzji = false;
    $Tablica = @unserialize(base64_decode((string)$_GET['recenzja']));
    //
    if ( is_array($Tablica) ) {
        //
        if ( isset($Tablica['id']) && isset($Tablica['czas']) ) {
             //
             $zamowienie = new Zamowienie((int)$Tablica['id']);
             //
             if ( count($zamowienie->info) > 0 ) {
                  //
                  // sprawdzi czy zgadza sie nr id zamowienia oraz data zamowienia z danymi z linku
                  if ( isset($zamowienie->info['id_zamowienia']) && $zamowienie->info['id_zamowienia'] == $Tablica['id'] && 
                       isset($zamowienie->info['data_zamowienia']) && FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']) == $Tablica['czas'] &&
                       isset($zamowienie->klient['id']) && $zamowienie->klient['id'] == $Tablica['klient'] &&
                       (int)$_GET['id'] == $Tablica['produkt'] ) {
                       //
                       $Link = array('autor' => $zamowienie->klient['nazwa'],
                                     'id_klienta' => $zamowienie->klient['id'],
                                     'email' => $zamowienie->klient['adres_email'],
                                     'nr_zamowienia' => $zamowienie->info['id_zamowienia']);
                       //
                  } else {
                       //
                       $BladDanychRecenzji = true;
                       //
                  }
                  //
             } else {
                  //
                  $BladDanychRecenzji = true;
                  //
             }
             //
        } else {
             //
             $BladDanychRecenzji = true;
             //
        }
        //
    } else {
        //
        $BladDanychRecenzji = true;
        //
    }
    //
    if ( $BladDanychRecenzji == true ) {
         //
         $Produkt = new Produkt( (int)$_GET['id'] ); 
         //
         if ($Produkt->CzyJestProdukt == true) {
             //
             Funkcje::PrzekierowanieURL( $Produkt->info['adres_seo'] ); 
             //
         } else {
             //
             Funkcje::PrzekierowanieURL('brak-strony.html'); 
             //
         }
         //
         unset($Produkt);
         //
    }
    //
    unset($Tablica, $BladDanychRecenzji, $zamowienie);
  
} else {
 
    if ( isset($_GET['id']) && (int)$_GET['id'] > 0 ) {
         //
         $Produkt = new Produkt( (int)$_GET['id'] ); 
         //
         if ($Produkt->CzyJestProdukt == true) {
             //
             Funkcje::PrzekierowanieURL( $Produkt->info['adres_seo'] ); 
             //
         } else {
             //
             Funkcje::PrzekierowanieURL('brak-strony.html'); 
             //
         }
         //
         unset($Produkt);
         //
    } else {
         //
         Funkcje::PrzekierowanieURL('brak-strony.html'); 
         //
    }
    
}

// po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {

        $Autor = '';
        $Opinia = '';
        $Ocena = '';
        $IdProduktu = '';
        
        if ( isset($_POST['autor']) && isset($_POST['opinia']) && isset($_POST['ocena']) && isset($_POST['id_produkt']) ) {
        
            $Autor = $filtr->process($_POST['autor']);
            $Opinia = $filtr->process($_POST['opinia'], false, true);
            $Ocena = (int)$_POST['ocena'];
            $IdProduktu = (int)$_POST['id_produkt'];
            
            // jezeli klient jest zalogowany
            $IdRecenzenta = 0;
            if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0') {
                $IdRecenzenta = $_SESSION['customer_id'];
            }
            //
            if (isset($_POST['klient']) && (int)$_POST['klient'] > 0) {
                $IdRecenzenta = (int)$_POST['klient'];
            }            
            
            if (!empty($Autor) && !empty($Opinia) && $Ocena > 0 && $IdProduktu > 0) {
                //
                // jezeli jest zdjecie
                //
                $foto = '';
                if (isset($_FILES)) {
                    //
                    if (count($_FILES) > 0) {
                        //
                        if ( isset($_FILES['zdjecie_1']) ) {
                             //
                             $foto = Funkcje::WgrajPlik($_FILES['zdjecie_1']);
                             //
                        }
                        //
                    }
                    //
                }
                //
                $pola = array(array('products_id', (int)$IdProduktu),
                              array('customers_id', (int)$IdRecenzenta),
                              array('customers_name', $Autor),
                              array('reviews_rating', $Ocena),
                              array('date_added', 'now()'),
                              array('approved','0'),
                              array('reviews_confirm','1'),
                              array('reviews_image',$foto));
                //	
                $sql = $GLOBALS['db']->insert_query('reviews', $pola);
                $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();
                //
                unset($pola);        
                
                $pola = array(
                        array('reviews_id', (int)$id_dodanej_pozycji),
                        array('languages_id', (int)$_SESSION['domyslnyJezyk']['id']),
                        array('reviews_text', $Opinia));          
                $sql = $GLOBALS['db']->insert_query('reviews_description' , $pola);
                
                unset($pola, $Autor, $Opinia, $Ocena);  
                
                // dodawanie punktow za napisanie recenzji
                if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && $IdRecenzenta > 0 && Punkty::PunktyAktywneDlaKlienta( ((isset($_POST['klient'])) ? (int)$_POST['klient'] : 0) ) ) {        
                    //
                    $pola = array(array('customers_id', (int)$IdRecenzenta),
                                  array('reviews_id', (int)$id_dodanej_pozycji),
                                  array('points', (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE),
                                  array('date_added', 'now()'),
                                  array('points_status', '1'),
                                  array('points_type','RV'));
                    //	
                    $sql = $GLOBALS['db']->insert_query('customers_points', $pola);            
                    //
                }
                
                unset($IdRecenzenta);
                
                //
                Funkcje::PrzekierowanieURL('napisz-recenzje-sukces-rws-'. $id_dodanej_pozycji .'.html/produkt=' . $IdProduktu . '/recenzja=' . $_GET['recenzja'] . '/nr=' . $_GET['nr'] . '/zamowienie=' . $_GET['zamowienie'] . ((isset($_GET['wroc'])) ? '/wroc=' . $_GET['wroc'] : ''));        
            }
            
        } else {
            
            Funkcje::PrzekierowanieURL('brak-strony.html'); 
          
        }

    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        
    }    
    
}

$BylaRecenzjaKlienta = 'nie';

// sprawdza czy uzytkownik nie napisal juz recenzji o tym produkcie
if ( !isset($_GET['sukces']) ) {
    //
    $zapytanieRec = "SELECT reviews_id FROM reviews WHERE products_id = '" . ((isset($_GET['produkt']) && (int)$_GET['produkt'] > 0) ? (int)$_GET['produkt'] : (int)$_GET['id']) . "' AND customers_id = '" . ((isset($Link['id_klienta'])) ? (int)$Link['id_klienta'] : 0) . "'";
    $sqlRec = $GLOBALS['db']->open_query($zapytanieRec);
    //
    if ((int)$GLOBALS['db']->ile_rekordow($sqlRec) > 0) {
       //
       $BylaRecenzjaKlienta = 'tak';
       //
    }
    //
    $GLOBALS['db']->close_query($sqlRec);
    unset($zapytanieRec);
    //
}    

$sql = $GLOBALS['db']->open_query( Produkty::SqlNapiszRecenzje( ((isset($_GET['produkt']) && (int)$_GET['produkt'] > 0) ? (int)$_GET['produkt'] : (int)$_GET['id']) ) );

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAPISZ_OPINIE_O_PRODUKCIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

    // sprawdzenie linku SEO z linkiem w przegladarce
    if ( !isset($_GET['sukces']) && isset($_GET['id']) ) {
        //
        Seo::link_Spr('napisz-recenzje-rw-' . (int)$_GET['id'] . '.html');
        //
    }
    
    //
    $Zalogowany = 'nie';
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' ) {
         $Zalogowany = 'tak';
    }    

    //
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $BylaRecenzjaKlienta, ((isset($Link['id_klienta'])) ? (int)$Link['id_klienta'] : 0));
    //
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW') ), $GLOBALS['tlumacz'] );
    //
    $info = $sql->fetch_assoc();

    $Produkt = new Produkt( $info['products_id'] );
    
    if ($Produkt->CzyJestProdukt == false) {
        //
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        //
    }    

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    
    if ( $Meta['nazwa_pliku'] != null ) { 
         //     
         $tpl->dodaj('__META_TYTUL', MetaTagi::MetaTagiRecenzjeNapiszPodmien('tytul', $Produkt, $Meta['tytul']));
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', MetaTagi::MetaTagiRecenzjeNapiszPodmien('slowa', $Produkt, $Meta['slowa']));
         $tpl->dodaj('__META_OPIS', MetaTagi::MetaTagiRecenzjeNapiszPodmien('opis', $Produkt, $Meta['opis']));
         //
      } else {
         //
         $tpl->dodaj('__META_TYTUL', $GLOBALS['tlumacz']['NAPISZ_OPINIE_O_PRODUKCIE'] . ' ' . $Produkt->info['nazwa']);
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Produkt->meta_tagi['slowa'])) ? $Meta['slowa'] : $Produkt->meta_tagi['slowa']));
         $tpl->dodaj('__META_OPIS', ((empty($Produkt->meta_tagi['opis'])) ? $Meta['opis'] : $Produkt->meta_tagi['opis']));
         //         
    }    
    unset($Meta);  
    
    $srodek->dodaj('__NAZWA_PRODUKTU', $Produkt->info['nazwa']);
    $srodek->dodaj('__ID_PRODUKTU', (int)$_GET['id']);
    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
    $srodek->dodaj('__ZDJECIE_PRODUKTU', $Produkt->fotoGlowne['zdjecie_link_ikony']);
        
    if ( !isset($_GET['sukces']) ) {

        $srodek->dodaj('__LINK', 'napisz-recenzje-rw-' . (int)$_GET['id'] . '.html/recenzja=' . $_GET['recenzja'] . '/nr=' . $_GET['nr'] . '/zamowienie=' . $_GET['zamowienie'] . ((isset($_GET['wroc'])) ? '/wroc=' . $_GET['wroc'] . '_sukces' : ''));

        $KlientGosc = true;
        
        // szuka imienia klienta
        $sqlKlient = $GLOBALS['db']->open_query("SELECT customers_firstname, customers_guest_account FROM customers WHERE customers_id = '" . (int)$Link['id_klienta'] . "'");             
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sqlKlient) > 0) {
            //
            $infoKlient = $sqlKlient->fetch_assoc();
            $srodek->dodaj('__IMIE_AUTORA', $infoKlient['customers_firstname']);
            //
            if ( $infoKlient['customers_guest_account'] == 0 ) {
                 $KlientGosc = false;
            }
            //
            unset($infoKlient);
            //
        } else {
            //
            $srodek->dodaj('__IMIE_AUTORA', '');
            //
        }
        //
        $GLOBALS['db']->close_query($sqlKlient);            
        //
        $srodek->dodaj('__ID_KLIENTA', $Link['id_klienta']);
        
        // system punktow
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && Punkty::PunktyAktywneDlaKlienta((int)$Link['id_klienta']) && $KlientGosc == false ) {
            $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', str_replace('{ILOSC_PUNKTOW}', (string)SYSTEM_PUNKTOW_PUNKTY_RECENZJE, (string)$GLOBALS['tlumacz']['PUNKTY_RECENZJE']));
        }
        //
        $srodek->dodaj('__CSS_INFO_O_PUNKTACH_RECENZJI', '');
        if ( $KlientGosc == true || SYSTEM_PUNKTOW_STATUS == 'nie' || (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE == 0) {
             $srodek->dodaj('__CSS_INFO_O_PUNKTACH_RECENZJI', 'style="display:none"');
        }

        //
        $GLOBALS['db']->close_query($sql); 
        unset($info);    
        //
        $srodek->dodaj('__TOKEN',Sesje::Token());
        //
    }
    //
  } else {
    //
    $GLOBALS['db']->close_query($sql); 
    unset($WywolanyPlik);    
    //
    Funkcje::PrzekierowanieURL('brak-produktu.html', true); 
    //
}

$srodek->dodaj('__LINK_POWROTU', '');
if ( isset($_GET['wroc']) ) {
     //
     if ( $_GET['wroc'] == 'zamowienie' ) {
          //
          $srodek->dodaj('__LINK_POWROTU', '<a href="{__SSL:zamowienia-szczegoly-zs-' . (int)$_GET['nr'] . '.html}" class="przycisk Prawy">{__TLUMACZ:PRZYCISK_COFNIJ}</a>');
          //
     }
     if ( $_GET['wroc'] == 'produkty' ) {
          //
          $srodek->dodaj('__LINK_POWROTU', '<a href="{__SSL:produkty-przegladaj.html}" class="przycisk Prawy">{__TLUMACZ:PRZYCISK_COFNIJ}</a>');
          //
     }     
     if ( $_GET['wroc'] == 'zamowienie_sukces' ) {
          //
          $srodek->dodaj('__LINK_POWROTU', '<a href="{__SSL:zamowienia-szczegoly-zs-' . (int)$_GET['nr'] . '.html}" class="przycisk Prawy">{__TLUMACZ:PRZYCISK_DALEJ}</a>');
          //
     }    
     if ( $_GET['wroc'] == 'produkty_sukces' ) {
          //
          $srodek->dodaj('__LINK_POWROTU', '<a href="{__SSL:produkty-przegladaj.html}" class="przycisk Prawy">{__TLUMACZ:PRZYCISK_DALEJ}</a>');
          //
     }       
     //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>