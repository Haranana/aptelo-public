<?php

// plik
$WywolanyPlik = 'napisz_opinie';

include('start.php');

if ( OPINIE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI_PANEL') ), $GLOBALS['tlumacz'] );

//po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {
      
        // dodatkowe zabezpieczenia
        $blokuj = false;
        if ( ( isset($_POST['fields_996901112']) && trim((string)$_POST['fields_996901112']) != '' ) || !isset($_POST['fields_996901112']) ) {
             //
             $blokuj = true;
             //
        }
        if ( ( isset($_POST['fields_996903112']) && trim((string)$_POST['fields_996903112']) ) || !isset($_POST['fields_996903112']) ) {
             //
             $blokuj = true;
             //
        }
        if ( isset($_POST['czas']) ) { 
             //
             if ( (time() - ((int)$_POST['czas'] + 78242)) < 3 ) {
                  $blokuj = true;
             }
             //
        }
        if ( !isset($_POST['czas']) ) { 
             //
             $blokuj = true;
             //
        }        
        
        // zabezpieczenie przed chinskimi tekstami
        foreach ($_POST as $wartosc) {
            //
            if ( !is_array($wartosc) ) {
                if ( @preg_match('/\p{Han}+/iU',$wartosc) == 1 ) {
                     $blokuj = true;
                }
            }
            //
        }

        if ( $blokuj == true ) {
             //
             Funkcje::PrzekierowanieURL('brak-strony.html');
             //
        }      

        $Werfyfikacja = false;
        if ( isset($_SESSION['weryfikacja']) ) {
            $spr = explode(':', (string)$_SESSION['weryfikacja']);
            if (md5($spr[1] . $_POST['weryfikacja']) == $spr[0]) { $Werfyfikacja = true; }
        }


        if ( isset($_SESSION['weryfikacja']) && $Werfyfikacja ) {
            //
            $Autor = '';
            $Opinia = '';
            $Email = '';           
            //
            if ( isset($_POST['autor']) && isset($_POST['opinia']) && isset($_POST['email']) ) {
                 //
                 $Autor = $filtr->process($_POST['autor']);
                 $Opinia = $filtr->process($_POST['opinia'], false, true);
                 $Email = $filtr->process($_POST['email']);
                 //
            }
            
            if (!empty($Autor) && !empty($Opinia) && !empty($Email)) {
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
                $pola = array(array('customers_name', $Autor),
                              array('customers_id', ((isset($_POST['id_pozycji']) && (int)$_POST['id_pozycji'] > 0) ? (int)$_POST['id_pozycji'] : 0)),
                              array('customers_email', $Email),
                              array('orders_id',((isset($_POST['nr_zamowienia'])) ? (int)$_POST['nr_zamowienia'] : 0)),
                              array('handling_rating',((isset($_POST['ocena_jakosc'])) ? (int)$_POST['ocena_jakosc'] : 5)),
                              array('lead_time_rating',((isset($_POST['ocena_czas'])) ? (int)$_POST['ocena_czas'] : 5)),
                              array('price_rating',((isset($_POST['ocena_ceny'])) ? (int)$_POST['ocena_ceny'] : 5)),
                              array('quality_products_rating',((isset($_POST['ocena_produkty'])) ? (int)$_POST['ocena_produkty'] : 5)),
                              array('recommending',((isset($_POST['ocena_produkty'])) ? (int)$_POST['polecanie'] : 5)),
                              array('comments', $Opinia),
                              array('date_added','now()'),
                              array('approved','0'),
                              array('reviews_shop_image',$foto));
                              
                if ( isset($_POST['link']) && (int)$_POST['link'] == 1 && isset($_POST['produkty']) && isset($_POST['id_produktow']) && OPINIE_PRODUKTY == 'tak' ) {
                      //
                      $pola[] = array('products_approved',(int)$_POST['produkty']);
                      $pola[] = array('products_id',$filtr->process($_POST['id_produktow']));
                      //
                }

                // srednia ocena
                if ( isset($_POST['ocena_jakosc']) && isset($_POST['ocena_czas']) && isset($_POST['ocena_ceny']) && isset($_POST['ocena_produkty']) ) {
                     //
                     $pola[] = array('average_rating', (((int)$_POST['ocena_jakosc'] + (int)$_POST['ocena_czas'] + (int)$_POST['ocena_ceny'] + (int)$_POST['ocena_produkty']) * 5) / 20);                              
                     //
                }
                //	
                $sql = $GLOBALS['db']->insert_query('reviews_shop', $pola);
                $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();
                //
                unset($pola);        
               
                // doda informacje o dacie kiedy klient uzupelnil opinie
                if ( isset($_POST['nr_zamowienia']) && (int)$_POST['nr_zamowienia'] > 0 ) {
                     //
                     $pola = array(
                             array('review_date_customer', 'now()'));         
                     $GLOBALS['db']->update_query('orders' , $pola, " orders_id = '" . (int)$_POST['nr_zamowienia'] . "' and customers_email_address = '" . $Email . "'");                     
                     //
                     unset($pola); 
                     //
                }
                
                unset($_SESSION['weryfikacja']);
                
                if ( OPINIE_PRODUKTY_NAPISZ == 'tak' ) {
                
                    // recenzje produktu
                    if ( isset($_POST['recenzja']) && is_array($_POST['recenzja']) && isset($_POST['id_pozycji']) && (int)$_POST['id_pozycji'] > 0 ) {
                         //
                         foreach ( $_POST['recenzja'] as $IdProduktu ) {
                            //
                            if ( trim((string)$_POST['recenzja_produktu_' . $IdProduktu]) != '' ) {
                                 //
                                 $pola = array(array('products_id', (int)$IdProduktu),
                                               array('customers_id', (int)$_POST['id_pozycji']),
                                               array('customers_name', $Autor),
                                               array('reviews_rating', ((isset($_POST['ocena_produktu_' . $IdProduktu])) ? (int)$_POST['ocena_produktu_' . $IdProduktu] : 5)),
                                               array('date_added', 'now()'),
                                               array('approved','0'));
                                 //	
                                 $sql = $GLOBALS['db']->insert_query('reviews', $pola);
                                 $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();
                                 //
                                 unset($pola);        
                                 
                                 $pola = array(
                                         array('reviews_id', (int)$id_dodanej_pozycji),
                                         array('languages_id', (int)$_SESSION['domyslnyJezyk']['id']),
                                         array('reviews_text', ((isset($_POST['recenzja_produktu_' . $IdProduktu])) ? $filtr->process($_POST['recenzja_produktu_' . $IdProduktu], false, true) : '')));          
                                 $sql = $GLOBALS['db']->insert_query('reviews_description' , $pola);

                                 // dodawanie punktow za napisanie recenzji
                                 if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && (int)$_POST['id_pozycji'] > 0 && isset($_POST['id_grupa']) && (int)$_POST['id_grupa'] > 0 ) {        
                                      //
                                      $pola = array(array('customers_id', (int)$_POST['id_pozycji']),
                                                    array('reviews_id', (int)$id_dodanej_pozycji),
                                                    array('points', (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE),
                                                    array('date_added', 'now()'),
                                                    array('points_status', '1'),
                                                    array('points_type','RV'));
                                      //	
                                      $sql = $GLOBALS['db']->insert_query('customers_points', $pola);            
                                      //
                                 }                        
                                 //
                            }
                         }
                         //
                    }
                
                }
                
                Funkcje::PrzekierowanieURL('napisz-opinie-o-sklepie-sukces.html');   
                
            } else {
              
                Funkcje::PrzekierowanieURL('brak-strony.html'); 
              
            }
            
        } else {
        
            Funkcje::PrzekierowanieURL('opinie-o-sklepie.html');   
            
        }
    
    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        
    }    
    
}

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAPISZ_OPINIE_O_SKLEPIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW') ), $GLOBALS['tlumacz'] );
//

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta); 

//
$Zalogowany = 'nie';
if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
     $Zalogowany = 'tak';
}    

// jezeli jest link z maila to sprawdzi czy zgadza sie z danymi
$Link = false;
if ( isset($_GET['opinia']) ) {
     //
     $Tablica = @unserialize(base64_decode((string)$_GET['opinia']));
     //
     if ( is_array($Tablica) ) {
          //
          if ( isset($Tablica['id']) && isset($Tablica['czas']) ) {
               //
               $zamowienie = new Zamowienie((int)$Tablica['id']);
               //
               if ( count($zamowienie->info) > 0 ) {
                 
                   // sprawdzi czy zgadza sie nr id zamowienia oraz data zamowienia z danymi z linku
                   if ( isset($zamowienie->info['id_zamowienia']) && $zamowienie->info['id_zamowienia'] == $Tablica['id'] && isset($zamowienie->info['data_zamowienia']) && FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']) == $Tablica['czas'] ) {
                        //
                        $Zalogowany = 'tak';
                        //
                        // okresla grupe klienta - do pkt za recenzje
                        $GrupaKlienta = '';
                        //
                        if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && $zamowienie->klient['gosc'] == 0 ) {
                             //
                            $zapytanie = "select customers_groups_id from customers where customers_id = '" . $zamowienie->klient['id'] . "'";
                            $sql = $GLOBALS['db']->open_query($zapytanie);
                            
                            $GrupaKlienta = 999999;
                            
                            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                              
                                $info = $sql->fetch_assoc();
                                $GrupaKlienta = $info['customers_groups_id'];
                                unset($info);
                                
                            }
                            //
                            $GLOBALS['db']->close_query($sql);
                            unset($info, $zapytanie); 
                            //
                            // czy grupa klienta jest objeta pkt
                            if ( !in_array($GrupaKlienta, explode(',', (string)SYSTEM_PUNKTOW_GRUPY_KLIENTOW)) && SYSTEM_PUNKTOW_GRUPY_KLIENTOW != '' ) {
                                 //
                                 $GrupaKlienta = '';
                                 //
                            }
                            //
                        }
                        //
                        $Link = array('autor' => $zamowienie->klient['nazwa'],
                                      'id_klienta' => $zamowienie->klient['id'],
                                      'grupa_klienta' => $GrupaKlienta,
                                      'email' => $zamowienie->klient['adres_email'],
                                      'nr_zamowienia' => $zamowienie->info['id_zamowienia']);
                        //
                        $IdProduktow = array();
                        foreach ( $zamowienie->produkty as $zam_produkt ) {
                            //
                            $IdProduktow[] = $zam_produkt['id_produktu'];
                            //
                        }
                        //
                        $Link['id_produktow'] = $IdProduktow;
                        //
                        unset($IdProduktow, $GrupaKlienta);
                        //                    
                   }
                   
               }
               //
               unset($zamowienie);
               //
          }
          //
     }
     //
     unset($Tablica);
     //
}

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $Zalogowany, ((is_array($Link) && OPINIE_PRODUKTY_NAPISZ == 'tak') ? 'tak' : 'nie'));
//

$srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', '');

// jezeli klient jest zalogowany wstawi jego imie w pole autora
if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && $Link != true) {
    //
    $srodek->dodaj('__IMIE_AUTORA', $_SESSION['customer_firstname']);
    $srodek->dodaj('__EMAIL_AUTORA', $_SESSION['customer_email']);
    $srodek->dodaj('__NR_ZAMOWIENIA', '');
    $srodek->dodaj('__CSS_OPINIE', 'style="display:none"');
    $srodek->dodaj('__ID_PRODUKTOW', '');
    $srodek->dodaj('__ID_KLIENTA', $_SESSION['customer_id']);
    $srodek->dodaj('__GRUPA_KLIENTA', '');
    $srodek->dodaj('__Z_LINKU', '0');
    $srodek->dodaj('__LISTA_PRODUKTOW_DO_RECENZJI', '');
    $srodek->dodaj('__CSS_OPINIE_PRODUKTY', 'style="display:none"');
    //
  } else {    
    //
    if ( !is_array($Link) ) {
         //
         $srodek->dodaj('__IMIE_AUTORA', '');
         $srodek->dodaj('__EMAIL_AUTORA', ''); 
         $srodek->dodaj('__NR_ZAMOWIENIA', '');
         $srodek->dodaj('__CSS_OPINIE', '');
         $srodek->dodaj('__ID_PRODUKTOW', '');
         $srodek->dodaj('__ID_KLIENTA', '');
         $srodek->dodaj('__GRUPA_KLIENTA', '');
         $srodek->dodaj('__Z_LINKU', '0');
         $srodek->dodaj('__LISTA_PRODUKTOW_DO_RECENZJI', '');
         $srodek->dodaj('__CSS_OPINIE_PRODUKTY', 'style="display:none"');
         //
      } else {
         //
         // szuka imienia klienta
         $sqlKlient = $GLOBALS['db']->open_query("SELECT customers_firstname FROM customers WHERE customers_id = '" . (int)$Link['id_klienta'] . "'");
         //
         if ((int)$GLOBALS['db']->ile_rekordow($sqlKlient) > 0) {
            //
            $infoKlient = $sqlKlient->fetch_assoc();
            $srodek->dodaj('__IMIE_AUTORA', $infoKlient['customers_firstname']);
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
         $srodek->dodaj('__EMAIL_AUTORA', $Link['email']);
         $srodek->dodaj('__NR_ZAMOWIENIA', $Link['nr_zamowienia']);
         $srodek->dodaj('__CSS_OPINIE', 'style="display:none"');
         $srodek->dodaj('__ID_PRODUKTOW', implode(',', array_unique($Link['id_produktow'])));
         $srodek->dodaj('__ID_KLIENTA', (int)$Link['id_klienta']);
         $srodek->dodaj('__GRUPA_KLIENTA', $Link['grupa_klienta']);
         $srodek->dodaj('__Z_LINKU', '1');
         $srodek->dodaj('__CSS_OPINIE_PRODUKTY', 'style="display:none"');
         //
         $RecenzjeDoProduktow = array();
         //
         if ( OPINIE_PRODUKTY_NAPISZ == 'tak' ) {
             //
             foreach (array_unique($Link['id_produktow']) as $Id) {
                //
                $zapytanieProdukt = "SELECT p.products_id, pd.products_name 
                                       FROM products p
                                  LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                  LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                                 RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                                 RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                                      WHERE p.products_id = '" . $Id . "' and p.products_status = '1'";
                           
                $sqlProdukt = $GLOBALS['db']->open_query($zapytanieProdukt); 
                //
                if ((int)$GLOBALS['db']->ile_rekordow($sqlProdukt) > 0) {
                    //
                    $info = $sqlProdukt->fetch_assoc();
                    //
                    if ( $info['products_name'] != '' ) {
                        //
                        $CiagRecenzjiProduktu = '<div class="OpinieRecenzjaProduktu">
                                                 <input type="hidden" name="recenzja[]" value="' . $info['products_id'] . '" />
                                                 <b>' . $info['products_name'] . '</b>
                                                 <div class="OpinieRecenzjaProduktuOcena">
                                                    <div>';
                                                    if ( Wyglad::TypSzablonu() == true ) {
                                                         //
                                                         $rans = uniqid();
                                                         //
                                                         $CiagRecenzjiProduktu .= '
                                                         <label for="ocena_5_' . $rans . '"><span class="Gwiazdki Gwiazdka_5" id="radio_'.uniqid().'" style="--ocena: 5.0;"><input type="radio" id="ocena_5_' . $rans . '" value="5" name="ocena_produktu_' . $info['products_id'] . '" checked="checked" /><span class="radio" id="radio_ocena_5"></span></label>
                                                         <label for="ocena_4_' . $rans . '"><span class="Gwiazdki Gwiazdka_4" id="radio_'.uniqid().'" style="--ocena: 4.0;"></span><input type="radio" id="ocena_4_' . $rans . '" value="4" name="ocena_produktu_' . $info['products_id'] . '" /><span class="radio" id="radio_ocena_4"></span></label>
                                                         <label for="ocena_3_' . $rans . '"><span class="Gwiazdki Gwiazdka_3" id="radio_'.uniqid().'" style="--ocena: 3.0;"></span><input type="radio" id="ocena_3_' . $rans . '" value="3" name="ocena_produktu_' . $info['products_id'] . '" /><span class="radio" id="radio_ocena_3"></span></label>
                                                         <label for="ocena_2_' . $rans . '"><span class="Gwiazdki Gwiazdka_2" id="radio_'.uniqid().'" style="--ocena: 2.0;"></span><input type="radio" id="ocena_2_' . $rans . '" value="2" name="ocena_produktu_' . $info['products_id'] . '" /><span class="radio" id="radio_ocena_2"></span></label>
                                                         <label for="ocena_1_' . $rans . '"><span class="Gwiazdki Gwiazdka_1" id="radio_'.uniqid().'" style="--ocena: 1.0;"></span><input type="radio" id="ocena_1_' . $rans . '" value="1" name="ocena_produktu_' . $info['products_id'] . '" /><span class="radio" id="radio_ocena_1"></span></label>';
                                                         //
                                                    } else {
                                                         //
                                                         $CiagRecenzjiProduktu .= '
                                                         <input type="radio" value="5" name="ocena_produktu_' . $info['products_id'] . '" checked="checked" /> <img alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' 5/5" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/recenzje/ocena_5.png" />
                                                         <input type="radio" value="4" name="ocena_produktu_' . $info['products_id'] . '" /> <img alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' 4/5" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/recenzje/ocena_4.png" /> <br />
                                                         <input type="radio" value="3" name="ocena_produktu_' . $info['products_id'] . '" /> <img alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' 3/5" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/recenzje/ocena_3.png" /> <br />
                                                         <input type="radio" value="2" name="ocena_produktu_' . $info['products_id'] . '" /> <img alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' 2/5" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/recenzje/ocena_2.png" /> <br />
                                                         <input type="radio" value="1" name="ocena_produktu_' . $info['products_id'] . '" /> <img alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' 1/5" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/recenzje/ocena_1.png" /> <br />';
                                                         //
                                                    }
                                                    $CiagRecenzjiProduktu .= '</div>
                                                    <div>
                                                       <textarea rows="4" cols="85" name="recenzja_produktu_' . $info['products_id'] . '" placeholder="' . $GLOBALS['tlumacz']['NAPISZ_OPINIE_O_PRODUKCIE'] .  '"></textarea>
                                                    </div>
                                                  </div>
                                                  </div>';
                        
                        $RecenzjeDoProduktow[] = $CiagRecenzjiProduktu;
                        //
                    }
                    //
                    unset($info);
                    //
                }
                //
                $GLOBALS['db']->close_query($sqlProdukt); 
                //
                unset($zapytanieProdukt);
                //
             }       
             //
         }
         //
         $srodek->dodaj('__LISTA_PRODUKTOW_DO_RECENZJI', implode('', (array)$RecenzjeDoProduktow));
         //
         if ( count($RecenzjeDoProduktow) > 0 ) {
              $srodek->dodaj('__CSS_OPINIE_PRODUKTY', '');
         }
         //
         unset($RecenzjeDoProduktow);
         //
    }
    //
}

$srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
$srodek->dodaj('__TOKEN',Sesje::Token());
$srodek->dodaj('__CZAS',time() - 78242);
//

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik, $Link);

include('koniec.php');

?>