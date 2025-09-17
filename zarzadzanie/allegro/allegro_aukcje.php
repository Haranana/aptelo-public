<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
     
    // id waluty PLN
    $IdPLN = 1;
    //

    if ( isset($_POST['zmiana_logowania']) && $_POST['zmiana_logowania'] == 'zmiana' ) {
        //
        $_SESSION['domyslny_uzytkownik_allegro'] = $_POST['login_allegro'];
        $zapytanie_user = "SELECT * FROM allegro_users WHERE allegro_user_id = '".$_POST['login_allegro']."'";
        $sql_user = $db->open_query($zapytanie_user);

        if ((int)$db->ile_rekordow($sql_user) > 0) {
            //
            while ($info_user = $sql_user->fetch_assoc()) {
                $_SESSION['domyslny_login_allegro'] = $info_user['allegro_user_login'];
            }
            //
        }
        $db->close_query($sql_user);
        //
        unset($zapytanie_user, $info_user);
        //
    }
    
    if ( isset($_POST['zmiana_logowania']) ) {
         unset($_POST['zmiana_logowania']);
    }
    if ( isset($_POST['login_allegro']) ) {
         unset($_POST['login_allegro']);
    }    

    if ( isset($_SESSION['tablica_walut_kod']) ) {
        //
        $IdWalut = $_SESSION['tablica_walut_kod'];
        foreach ( $IdWalut as $WalutaSklepu ) {
            //
            if ( $WalutaSklepu['kod'] == 'PLN' ) {
                 $IdPLN = $WalutaSklepu['id'];
            }
            //
        }
        unset($IdWalut);  
        //
    }
    
    if (isset($_GET['kategoria_allegro_usun']) && $_GET['kategoria_allegro_usun'] == 'tak') {
        unset($_SESSION['filtry']['allegro_aukcje.php']['kategoria_allegro']);
        Funkcje::PrzekierowanieURL('allegro_aukcje.php');
    }    
    
    if (isset($_GET['kategoria_sklep_usun']) && $_GET['kategoria_sklep_usun'] == 'tak') {
        unset($_SESSION['filtry']['allegro_aukcje.php']['kategoria_id']);
        Funkcje::PrzekierowanieURL('allegro_aukcje.php');
    }        

    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        unset($_SESSION['filtry']['allegro_aukcje.php']['kategoria_allegro']);
    }
    
    if (isset($_GET['kategoria_allegro']) && (int)$_GET['kategoria_allegro'] > 0) {
        unset($_SESSION['filtry']['allegro_aukcje.php']['kategoria_id']);
    }
    
    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $TablicaUzytkownikow = array();
    $TablicaUzytkownikowFiltry = array(array('id' => '0', 'text' => '-- dowolne --'));
    
    $zapytanieUser = "SELECT * FROM allegro_users";
    $sqlUser = $db->open_query($zapytanieUser);
                      
    if ((int)$db->ile_rekordow($sqlUser) > 0) {
                      
      while ($infoUser = $sqlUser->fetch_assoc()) {

          $TablicaUzytkownikow[$infoUser['allegro_user_id']] = $infoUser['allegro_user_login'];
          $TablicaUzytkownikowFiltry[] = array('id' => $infoUser['allegro_user_id'], 'text' => $infoUser['allegro_user_login']);

      }
      
    }
    
    $db->close_query($sqlUser);
    unset($zapytanieUser, $infoUser);

    if ( Funkcje::SprawdzAktywneAllegro() ) {
      
        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
        
    }

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " AND ( ap.auction_id LIKE '%".$szukana_wartosc."%' OR ap.products_name LIKE '%".$szukana_wartosc."%' ) ";
        unset($szukana_wartosc);
    }
    
    // jezeli jest nr kat 
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {   
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_man_code like '%".$szukana_wartosc."%' or ps.products_stock_model like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }   

    // jezeli jest nr ean
    if (isset($_GET['ean']) && !empty($_GET['ean'])) {   
        $szukana_wartosc = $filtr->process($_GET['ean']);
        $warunki_szukania = " and (p.products_ean like '%".$szukana_wartosc."%' or ps.products_stock_ean like '%".$szukana_wartosc."%' or ap.products_ean_allegro like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }       
    
    // jezeli jest sygnatura z Allegro
    if (isset($_GET['sygnatura']) && !empty($_GET['sygnatura'])) {   
        $szukana_wartosc = $filtr->process($_GET['sygnatura']);
        $warunki_szukania = " and ap.external_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }       
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = (int)$_GET['producent'];
        $warunki_szukania .= " and p.manufacturers_id = '".$id_producenta."'";
        unset($id_producenta);
    } 

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' && $_GET['szukaj_status'] != 'ARCHIVE' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and ap.auction_status = '".$szukana_wartosc."' and ap.archiwum_allegro != '1'";
        unset($szukana_wartosc);
    }
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' && $_GET['szukaj_status'] == 'ARCHIVE' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and ap.archiwum_allegro = '1'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_status_produktu']) && $_GET['szukaj_status_produktu'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status_produktu']);
        if ( $szukana_wartosc == '2' ) $szukana_wartosc = '0';
        $warunki_szukania .= " and p.products_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_data_rozpoczecia_od']) && $_GET['szukaj_data_rozpoczecia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_rozpoczecia_od'] . ' 00:00:00')));
        $warunki_szukania .= " and ap.auction_date_start >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_rozpoczecia_do']) && $_GET['szukaj_data_rozpoczecia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_rozpoczecia_do'] . ' 23:59:59')));
        $warunki_szukania .= " and ap.auction_date_start <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    if ( isset($_GET['szukaj_data_zakonczenia_od']) && $_GET['szukaj_data_zakonczenia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zakonczenia_od'] . ' 00:00:00')));
        $warunki_szukania .= " and ap.auction_date_end >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_zakonczenia_do']) && $_GET['szukaj_data_zakonczenia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zakonczenia_do'] . ' 23:59:59')));
        $warunki_szukania .= " and ap.auction_date_end <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_opcja']) && $_GET['szukaj_opcja'] != '0' ) {
        $szukana_wartosc = $_GET['szukaj_opcja'];
        $warunki_szukania .= " and ap.allegro_options LIKE '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }    

    // jezeli jest wybrana kategoria allegro
    if (isset($_GET['kategoria_allegro']) && (int)$_GET['kategoria_allegro'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_allegro'];
        $warunki_szukania .= " and ap.allegro_category = '".$id_kategorii."'";
        unset($id_kategorii);
    }
    
    // jezeli jest wybrana kategoria sklepu
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and ptc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }    

    // jezeli jest wybrane konto allegro
    if (isset($_GET['login_aukcji']) && (int)$_GET['login_aukcji'] > 0) {
        $warunki_szukania .= " and ap.auction_seller = '" . (int)$_GET['login_aukcji'] . "'";
    }    
    
    // jezeli jest rabat ilosciowy
    if (isset($_GET['szukaj_rabat']) && (int)$_GET['szukaj_rabat'] > 0) {
        if ( (int)$_GET['szukaj_rabat'] == 1 ) {
             $warunki_szukania .= " and ap.allegro_benefits != ''";
        }      
    }  

    // jezeli jest zestaw
    if (isset($_GET['szukaj_zestaw']) && (int)$_GET['szukaj_zestaw'] > 0) {
        if ( (int)$_GET['szukaj_zestaw'] == 1 ) {
             $warunki_szukania .= " and abs.allegro_benefits_set_id_set IS NULL";
        }         
        if ( (int)$_GET['szukaj_zestaw'] == 2 ) {
             $warunki_szukania .= " and abs.allegro_benefits_set_id_set != ''";
        }     
    }      

    // jezeli jest do synchronizacji
    if (isset($_GET['synchronizacja']) && $_GET['synchronizacja'] == '1') {
        $warunki_szukania .= " and ap.auction_uuid != ''";
    }  

    // jezeli jest wybrana roznica magazynow
    if (isset($_GET['szukaj_stan_mag'])) {
        if ((int)$_GET['szukaj_stan_mag'] == 1) {
           $warunki_szukania .= " and ( IF((ap.products_stock_attributes != ''), ap.auction_quantity < ps.products_stock_quantity, ap.auction_quantity < p.products_quantity) )";
        }
        if ((int)$_GET['szukaj_stan_mag'] == 2) {
           $warunki_szukania .= " and ( IF((ap.products_stock_attributes != ''), ap.auction_quantity > ps.products_stock_quantity, ap.auction_quantity > p.products_quantity) )";
        }
        if ((int)$_GET['szukaj_stan_mag'] == 3) {
           $warunki_szukania .= " and ( IF((ap.products_stock_attributes != ''), ps.products_stock_quantity <= 0, p.products_quantity <= 0) )";
        } 
        if ((int)$_GET['szukaj_stan_mag'] == 4) {
           $warunki_szukania .= " and ( IF((ap.products_stock_attributes != ''), ps.products_stock_quantity > 0, p.products_quantity > 0) )";
        }           
    }  

    // jezeli jest wybrana roznica cen
    if (isset($_GET['szukaj_stan_cen']) && ((int)$_GET['szukaj_stan_cen'] == 1 || (int)$_GET['szukaj_stan_cen'] == 2)) {
        //
        $tab_aukcji = array(0);
        //
        $zapytanieTmp = "SELECT ap.allegro_id, ap.auction_id, ap.products_id, ap.products_buy_now_price, ap.products_stock_attributes, ap.external_id,
                                ps.products_stock_price_tax,
                                p.products_price_tax,
                                p.products_currencies_id, 
                                p.products_points_only,     
                                p.options_type
                           FROM allegro_auctions ap 
                      LEFT JOIN products p ON p.products_id = ap.products_id 
                      LEFT JOIN products_stock ps ON ps.products_id = ap.products_id AND ps.products_stock_attributes = replace(ap.products_stock_attributes,'x', ',')
                       GROUP BY ap.auction_id";
                      
        $sqlTmp = $db->open_query($zapytanieTmp);              
        
        while ($info = $sqlTmp->fetch_assoc()) {
            //
            if ( $info['products_points_only'] == 0 ) {
              
                $cena_allegro = 0;
                $cena_sklep = 0;
                          
                if ( $info['products_buy_now_price'] > 0 ) {
                     $cena_allegro = $waluty->FormatujCeneBezSymbolu($info['products_buy_now_price'], false, '', '', 2, $IdPLN);
                }
            
                if ( $info['options_type'] == 'ceny' && $info['products_stock_price_tax'] > 0 ) {
                     $cena_sklep = $waluty->FormatujCeneBezSymbolu($info['products_stock_price_tax'], true, '', '', 2, $info['products_currencies_id']);
                  } else {
                     $cena_sklep = $waluty->FormatujCeneBezSymbolu(Produkt::ProduktCenaCechy($info['products_id'], $info['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), true, '', '', 2, $info['products_currencies_id']);
                }
                
                // jezeli cena w sklepie wieksza od allegro
                if ( (int)$_GET['szukaj_stan_cen'] == 1 ) {
            
                    if ($cena_sklep > $cena_allegro && $cena_allegro > 0 && $cena_sklep > 0) {
                        $tab_aukcji[$info['auction_id']] = $info['auction_id'];
                    }
                    
                }
                // jezeli cena w sklepie mniejsza od allegro
                if ( (int)$_GET['szukaj_stan_cen'] == 2 ) {
            
                    if ($cena_sklep < $cena_allegro && $cena_allegro > 0 && $cena_sklep > 0) {
                        $tab_aukcji[$info['auction_id']] = $info['auction_id'];
                    }
                    
                }                
                            
                unset($cena_allegro, $cena_sklep);
                
            }
            //
        }

        $db->close_query($sqlTmp);
        unset($infoTmp, $zapytanieTmp);
              
        if ( count($tab_aukcji) > 0 ) {
             $warunki_szukania .= " and ap.auction_id in (" . implode(',', (array)$tab_aukcji) . ")";
        }
        unset($tab_aukcji);
    }     
    
    // jezeli jest wybrana roznica cen z zakladki allegro
    if (isset($_GET['szukaj_stan_cen']) && ((int)$_GET['szukaj_stan_cen'] == 3 || (int)$_GET['szukaj_stan_cen'] == 4)) {
        //
        $tab_aukcji = array(0);
        //
        $zapytanieTmp = "SELECT ap.allegro_id, ap.auction_id, ap.products_id, ap.products_buy_now_price, ap.products_stock_attributes, pai.products_price_allegro,
                                p.products_currencies_id      
                           FROM allegro_auctions ap 
                      LEFT JOIN products p ON p.products_id = ap.products_id 
                      LEFT JOIN products_allegro_info pai ON pai.products_id = ap.products_id 
                       GROUP BY ap.auction_id";
                      
        $sqlTmp = $db->open_query($zapytanieTmp);              
        
        while ($info = $sqlTmp->fetch_assoc()) {
            //
            $cena_allegro = 0;
            $cena_zakladka_sklep = 0;
                      
            if ( $info['products_buy_now_price'] > 0 ) {
                 $cena_allegro = $waluty->FormatujCeneBezSymbolu($info['products_buy_now_price'], false, '', '', 2, $IdPLN);
            }
        
            if ( $info['products_price_allegro'] > 0 ) {
                 $cena_zakladka_sklep = $waluty->FormatujCeneBezSymbolu($info['products_price_allegro'], true, '', '', 2, $info['products_currencies_id']);
            }
            
            // jezeli cena w sklepie wieksza od allegro
            if ( (int)$_GET['szukaj_stan_cen'] == 3 ) {
        
                if ($cena_zakladka_sklep > $cena_allegro && $cena_allegro > 0 && $cena_zakladka_sklep > 0) {
                    $tab_aukcji[$info['auction_id']] = $info['auction_id'];
                }
                
            }
            // jezeli cena w sklepie mniejsza od allegro
            if ( (int)$_GET['szukaj_stan_cen'] == 4 ) {
        
                if ($cena_zakladka_sklep < $cena_allegro && $cena_allegro > 0 && $cena_zakladka_sklep > 0) {
                    $tab_aukcji[$info['auction_id']] = $info['auction_id'];
                }
                
            }                
                        
            unset($cena_allegro, $cena_zakladka_sklep);

        }

        $db->close_query($sqlTmp);
        unset($infoTmp, $zapytanieTmp);
              
        if ( count($tab_aukcji) > 0 ) {
             $warunki_szukania .= " and ap.auction_id in (" . implode(',', (array)$tab_aukcji) . ")";
        }
        unset($tab_aukcji);
    }       

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    /*
    $zapytanie = "SELECT ap.*, p.products_image, p.products_model, p.products_quantity AS iloscMagazyn, count(distinct oa.orders_id) as iloscZamowienSprzedane, count(distinct at.orders_id) as iloscZamowienTransakcje, p.products_control_storage, p.products_jm_id, p.products_status, pj.products_jm_quantity_type, m.manufacturers_name 
                  FROM allegro_auctions ap 
                  LEFT JOIN products p ON p.products_id = ap.products_id 
                  LEFT JOIN products_to_categories ptc ON p.products_id = ptc.products_id 
                  LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                  LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                  LEFT JOIN allegro_auctions_sold oa ON oa.orders_id != '0' AND oa.auction_id = ap.auction_id
                  LEFT JOIN allegro_transactions at ON at.orders_id != '0' AND at.auction_id = ap.auction_id
                  " . $warunki_szukania . ' GROUP BY ap.auction_id';
    */
    
    $zapytanie = "SELECT ap.*, 
                         ps.products_stock_quantity as iloscMagazynCech, 
                         ps.products_stock_price_tax,
                         ps.products_stock_model,
                         ps.products_stock_ean,
                         p.products_image as zdjecieOryginal, 
                         p.products_model, 
                         p.products_ean,
                         p.products_quantity AS iloscMagazyn, 
                         p.products_price_tax,
                         p.products_old_price,                          
                         p.products_currencies_id, 
                         p.products_points_only,     
                         p.options_type,
                         p.products_man_code,
                         p.products_control_storage, 
                         p.products_jm_id, 
                         p.products_status, 
                         pj.products_jm_quantity_type, 
                         m.manufacturers_name,
                         pai.products_price_allegro,
                         abs.allegro_benefits_set_id_set
                   FROM allegro_auctions ap 
              LEFT JOIN products p ON p.products_id = ap.products_id 
              LEFT JOIN products_allegro_info pai ON pai.products_id = ap.products_id 
              LEFT JOIN products_to_categories ptc ON p.products_id = ptc.products_id 
              LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
              LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
              LEFT JOIN allegro_benefits_set abs ON abs.allegro_benefits_set_auction_id = ap.auction_id
              LEFT JOIN products_stock ps ON ps.products_id = ap.products_id AND ps.products_stock_attributes = replace(ap.products_stock_attributes,'x', ',')
              " . $warunki_szukania . ' GROUP BY ap.auction_id';                 

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'ap.auction_id desc';
                break;
            case "sort_a2":
                $sortowanie = 'ap.auction_id asc';
                break;     
            case "sort_a3":
                $sortowanie = 'ap.auction_date_end DESC';
                break;
            case "sort_a4":
                $sortowanie = 'ap.auction_date_end ASC';
                break;                 
            case "sort_a5":
                $sortowanie = 'ap.auction_hits desc';
                break;
            case "sort_a6":
                $sortowanie = 'ap.auction_hits asc';
                break; 
            case "sort_a7":
                $sortowanie = 'ap.products_sold desc';
                break;
            case "sort_a8":
                $sortowanie = 'ap.products_sold asc';
                break;  
            case "sort_a9":
                $sortowanie = 'ap.auction_date_start DESC';
                break;
            case "sort_a10":
                $sortowanie = 'ap.auction_date_start ASC';
                break; 
            case "sort_a11":
                $sortowanie = 'ap.auction_quantity DESC';
                break;
            case "sort_a12":
                $sortowanie = 'ap.auction_quantity ASC';
                break;                   
        }            
    } else { $sortowanie = 'ap.auction_id DESC'; }    

    // informacje o produktach - zakres
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID aukcji','center'),
                                      array('Foto','center', '', 'class="ListingSchowaj"'),
                                      array('Nazwa produktu' ),
                                      array('Format','center'),
                                      array('Data rozpoczęcia','center', '', 'class="ListingSchowaj"'),
                                      array('Data zakończenia','center', '', 'class="ListingSchowaj"'),
                                      array('Ilość wystawiona / magazyn / cena','center'),
                                      array('Status produktu','center', '', 'class="ListingSchowaj"'),
                                      array('Sprzedaż<br />(30dni)','center'),
                                      array('Obserwuje','center', '', 'class="ListingRwdSzeroki"'),
                                      array('Status','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
          
                $status_img = '';
                $ilosc_magazyn = $info['iloscMagazyn'];

                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['allegro_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['allegro_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['allegro_id'].'">';
                }      

                $link = '';
                if ( Funkcje::SprawdzAktywneAllegro() ) {
                  
                    if ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ) {
                         $link = 'href="http://allegro.pl/i' . $info['auction_id'] . '.html"';
                    } else {
                         $link = 'href="http://allegro.pl.allegrosandbox.pl/i' . $info['auction_id'] . '.html"';
                    }
                  
                }

                $nazwa_produktu = '<b style="display:block;margin-bottom:5px"><a href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'">'.$info['products_name'].'</a></b>';
                if (trim((string)$info['products_model']) != '' || trim((string)$info['products_stock_model']) != '') {
                  if ( trim((string)$info['products_stock_model']) != '' ) {
                       $nazwa_produktu .= '<span class="MaleNrKatalogowy">Nr kat: <b>'.$info['products_stock_model'].'</b></span>';
                  } else {
                       $nazwa_produktu .= '<span class="MaleNrKatalogowy">Nr kat: <b>'.$info['products_model'].'</b></span>';
                  }
                }
                if (trim((string)$info['products_man_code']) != '') {
                  $nazwa_produktu .= '<span class="MaleNrKatalogowy">Kod prod: <b>'.$info['products_man_code'].'</b></span>';
                }                
                //
                $inny_ean = '';
                if (trim((string)$info['products_ean']) != '' || trim((string)$info['products_stock_ean']) != '') {
                  if ( trim((string)$info['products_stock_ean']) != '' ) {
                       $nazwa_produktu .= '<span class="MaleNrKatalogowy">EAN: <b>'.$info['products_stock_ean'].'</b></span>';
                       $inny_ean = $info['products_stock_ean'];
                  } else {
                       $nazwa_produktu .= '<span class="MaleNrKatalogowy">EAN: <b>'.$info['products_ean'].'</b></span>';
                       $inny_ean = $info['products_ean'];
                  }
                } 
                if (trim((string)$info['products_ean_allegro']) != '' && $inny_ean != trim((string)$info['products_ean_allegro'])) {
                    $nazwa_produktu .= '<span class="MaleNrKatalogowy">EAN (na allegro): <b>'.$info['products_ean_allegro'].'</b></span>';
                }                    
                // pobieranie danych o producencie
                if (trim((string)$info['manufacturers_name']) != '') {                     
                  $nazwa_produktu .= '<span class="MaleProducent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
                }                  

                $wyswietl_cechy = '';

                if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {

                  $tablica_kombinacji_cech = explode('x', (string)$info['products_stock_attributes']);
                  
                  $wyswietl_cechy .= '<div class="ListaCechy">';
                  
                  for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {
                  
                    $tablica_wartosc_cechy = explode('-', (string)$tablica_kombinacji_cech[$t]);

                    $nazwa_cechy = Funkcje::NazwaCechy( (int)$tablica_wartosc_cechy['0'] );
                    $nazwa_wartosci_cechy = Funkcje::WartoscCechy( (int)$tablica_wartosc_cechy['1'] );

                    $wyswietl_cechy .= '<span class="MaleInfoCecha">'.$nazwa_cechy . ': <b>' . $nazwa_wartosci_cechy . '</b></span>';
                    
                    unset($tablica_wartosc_cechy);
                    
                  }
                  
                  $wyswietl_cechy .= '</div>';
                  
                  unset($tablica_kombinacji_cech);
                  
                  // jezeli jest powiazanie cech z magazynem
                  if ( CECHY_MAGAZYN == 'tak' ) {
  
                      /*
                      $cechy_produktu = str_replace('x', ',' , $info['products_stock_attributes']);
                      
                      $zapytanie_ilosc_cechy = "SELECT products_stock_quantity 
                                                  FROM products_stock
                                                 WHERE products_id = '" . (int)$info['products_id']. "' 
                                                   AND products_stock_attributes = '".$cechy_produktu."'";
   
                      $sql_ilosc_cechy = $db->open_query($zapytanie_ilosc_cechy);

                      if ((int)$db->ile_rekordow($sql_ilosc_cechy) > 0) {
                      
                          $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                          $ilosc_magazyn = $info_ilosc_cechy['products_stock_quantity'];
                          
                      }
                      
                      $db->close_query($sql_ilosc_cechy);
                      
                      unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);
                      */
                      
                      if ( $info['products_stock_attributes'] != '' ) {
                        
                           $ilosc_magazyn = $info['iloscMagazynCech'];
                           
                      }
                      
                  }

                }
                
                if (!empty($wyswietl_cechy)) {                     
                  $nazwa_produktu .= $wyswietl_cechy;
                }

                if ( $info['auction_uuid'] == '' ) {
                    if ( $info['auction_status'] == 'ACTIVE' ) {
                      $status_img = '<em class="TipChmurka"><b>Aukcja trwa</b><img src="obrazki/allegro_trwa.png" alt="Aukcja trwa" /></em>';
                    } elseif ( $info['auction_status'] == 'ENDED' ) {
                      $status_img = '<em class="TipChmurka"><b>Aukcja zakończona</b><img src="obrazki/allegro_zakonczona.png" alt="Aukcja zakończona" /></em>';
                    } elseif ( $info['auction_status'] == 'ACTIVATING' ) {
                      $status_img = '<em class="TipChmurka"><b>Aukcja czeka na wystawienie</b><img src="obrazki/allegro_czeka.png" alt="Aukcja czeka na wystawienie" /></em>';
                    } elseif ( $info['auction_status'] == 'ARCHIVED' ) {
                      $status_img = '<em class="TipChmurka"><b>Aukcja przeniesiona do archwimum Allegro</b><img src="obrazki/allegro_archiwum.png" alt="Aukcja przeniesiona do archwimum Allegro" /></em>';
                    } elseif ( $info['auction_status'] == 'NOT_FOUND' ) {
                      $status_img = '<em class="TipChmurka"><b>Aukcja nie odnaleziona w Allegro</b><img src="obrazki/blad.png" alt="Aukcja nie odnaleziona w Allegro" /></em>';
                    }
                } else {
                      $status_img = '<em class="TipChmurka"><b>Aukcja czeka aktualizację zlecenia</b><img src="obrazki/allegro_czeka.png" alt="Aukcja czeka aktualizację zlecenia" /></em>';
                }

                if ( $info['archiwum_allegro'] == '1' ) {
                  $status_img = '<em class="TipChmurka"><b>Aukcja przeniesiona do archiwum Allegro</b><img src="obrazki/allegro_archiwum.png" alt="Aukcja przeniesiona do archiwum Allegro" /></em>';
                  if ( $info['auction_status'] != 'ARCHIVED' && $info['auction_status'] != 'NOT_FOUND' ) {
                    $status_img .= '<br /><a class="TipChmurka" href="allegro/allegro_akcja_status.php?id_poz='.$info['allegro_id'].'"><b>Zmień status na AUKCJA TRWA</b><img src="obrazki/kasuj_male.png" alt="Zmień status" /></a>';
                  } elseif ( $info['auction_status'] == 'ARCHIVED' ) {
                    $status_img = '<em class="TipChmurka"><b>Aukcja przeniesiona do archwimum Allegro</b><img src="obrazki/allegro_archiwum.png" alt="Aukcja przeniesiona do archwimum Allegro" /></em>';
                  } elseif ( $info['auction_status'] == 'NOT_FOUND' ) {
                    $status_img = '<em class="TipChmurka"><b>Aukcja nie odnaleziona w Allegro</b><img src="obrazki/blad.png" alt="Aukcja nie odnaleziona w Allegro" /></em>';
                  }
                }
                if ( $info['auction_type'] == 'BUY_NOW' ) {
                  $format_img = '<em class="TipChmurka"><b>Aukcja Kup teraz</b><img src="obrazki/allegro_kup_teraz.png" alt="Aukcja Kup teraz" /></em>';
                } else {
                  $format_img = '<em class="TipChmurka"><b>Aukcja z Licytacją</b><img src="obrazki/allegro_licytacja.png" alt="Aukcja z Licytacją" /></em>';
                }
                
                if ( empty($info['auction_uuid']) && $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'] ) {
                  $akcja = '<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['allegro_id'].'" id="opcja_'.$info['allegro_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['allegro_id'].'"></label><input type="hidden" name="id[]" value="'.$info['allegro_id'].'" />';
                } else {
                  $akcja = '<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['allegro_id'].'" disabled="disabled" id="opcja_'.$info['allegro_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['allegro_id'].'"></label><input type="hidden" name="id[]" value="'.$info['allegro_id'].'" />';
                }
                
                // nr aukcji rzeczywisty
                $nr_aukcji = $info['auction_id'];
                if ( $info['auction_id'] < (time() + 100 *86400) ) {
                     $nr_aukcji = '';
                }
                
                // jezeli jednostka miary calkowita
                if ( $info['products_jm_quantity_type'] == 1 ) {
                     $ilosc_magazyn = (int)$ilosc_magazyn;
                }           

                if ( !empty($info['zdjecieOryginal']) || !empty($info['products_image']) ) {
                     //
                     $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                     //
                     if ( $info['products_image'] != '' ) {
                          //
                          $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                          $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true); 
                          //
                     } else {                          
                          //
                          $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['zdjecieOryginal'], $info['products_name'], '250', '250') . '</div>';
                          $tgm .= Funkcje::pokazObrazek($info['zdjecieOryginal'], $info['products_name'], '40', '40', ' class="Reload"', true); 
                          //
                     }
                     //
                     $tgm .= '</div>';
                     //
                   } else { 
                     //
                     $tgm = '-';
                     //
                }              
                
                // sprawdzanie czy ilosc w magazynie nie jest mniejsza niz na allegro
                $magazyn_cena = $info['auction_quantity'] . ' / ' . $ilosc_magazyn;
                if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && $info['auction_quantity'] > $ilosc_magazyn && ($info['products_control_storage'] == 1 || $info['products_control_storage'] == 2)) {
                    $magazyn_cena = '<em class="TipChmurka"><b>Ilość produktów na Allegro jest większa niż stan magazynowy w sklepie</b><span class="MagazynBlad">' . $magazyn_cena . '</span></em>';
                }
                
                $cena_txt_allegro = '';
                $cena_txt_sklep = '';
                $cena_txt_zakladka_allegro = '';
                $cena_allegro = 0;
                $cena_sklep = 0;
                $cena_zakladka_allegro = 0;

                if ( $info['products_buy_now_price'] > 0 ) {
                     $cena_txt_allegro = '<div class="CenaAllegro">' . $waluty->FormatujCene($info['products_buy_now_price'], false, $IdPLN) . '</div>';
                     $cena_allegro = $waluty->FormatujCeneBezSymbolu($info['products_buy_now_price'], false, '', '', 2, $IdPLN);
                }
                
                if ( $info['products_price_allegro'] > 0 ) {
                     $cena_txt_zakladka_allegro = '<div class="CenaAllegroZakladka">' . $waluty->FormatujCene($info['products_price_allegro'], false, $IdPLN) . '</div>';
                     $cena_zakladka_allegro = $waluty->FormatujCeneBezSymbolu($info['products_price_allegro'], false, '', '', 2, $IdPLN);
                }                
                
                if ( $info['products_points_only'] == 0 ) {
                     //
                     if ( $info['options_type'] == 'ceny' && $info['products_stock_price_tax'] > 0 ) {
                          $cena_txt_sklep = '<div class="CenaSklep">'.$waluty->FormatujCene($info['products_stock_price_tax'], true, $IdPLN, '', 2, $info['products_currencies_id']).'</div>';                                        
                          $cena_sklep = $waluty->FormatujCeneBezSymbolu($info['products_stock_price_tax'], true, '', '', 2, $info['products_currencies_id']);
                       } else {
                          $cena_txt_sklep = '<div class="CenaSklep">'.$waluty->FormatujCene(Produkt::ProduktCenaCechy($info['products_id'], $info['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), true, $IdPLN, '', 2, $info['products_currencies_id']).'</div>';              
                          $cena_sklep = $waluty->FormatujCeneBezSymbolu(Produkt::ProduktCenaCechy($info['products_id'], $info['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), true, '', '', 2, $info['products_currencies_id']);
                     }
                     //
                }
                
                if ($cena_sklep > $cena_allegro && $cena_allegro > 0 && $cena_sklep > 0) {
                    $cena_txt_sklep = '<div class="cl"></div><div class="RoznicaCenSklep TipChmurka"><b>Cena produktu w sklepie jest wyższa niż cena na Allegro</b>' . $cena_txt_sklep . '</div>';
                }
                
                if ($cena_zakladka_allegro > $cena_allegro && $cena_zakladka_allegro > 0 && $cena_sklep > 0) {
                    $cena_txt_zakladka_allegro = '<div class="cl"></div><div class="RoznicaCenSklep TipChmurka"><b>Cena produktu dla Allegro jest wyższa niż cena na Allegro</b>' . $cena_txt_zakladka_allegro . '</div>';
                }          

                // prowizja allegro
                $prowizja_allegro = '';
                if ($info['auction_cost'] > -1) {
                    //
                    $prowizja_procent = '';
                    if ( $cena_allegro > 0 ) {
                          $prowizja_procent = '<b>' . number_format((($info['auction_cost'] / $cena_allegro) * 100), 2, ',', '') . '%</b>';
                    }
                    $prowizja_allegro = '<div class="cl"></div><div class="ProwizjaAllegro"><span>Prowizja Allegro:</span>' . number_format($info['auction_cost'], 2, ',', '') . ' zł' . $prowizja_procent . '</div>';
                    unset($prowizja_procent);
                    //
                }
                                
                $magazyn_cena = $magazyn_cena . (($cena_txt_allegro != '' || $cena_txt_sklep != '' || $cena_txt_zakladka_allegro != '' || $prowizja_allegro != '') ? '<br /><div class="CenyProdukty">' . $cena_txt_allegro . $cena_txt_sklep . $cena_txt_zakladka_allegro . $prowizja_allegro . '</div>' : '');
                
                // znizka od ilosci
                if ( $info['allegro_benefits'] != '' ) {
                     //
                     $ils = 'drugą';
                     if ( $info['allegro_benefits_quantity'] == 3 ) {
                          $ils = 'trzecią';
                     }
                     if ( $info['allegro_benefits_quantity'] == 4 ) {
                          $ils = 'czwartą';
                     }
                     if ( $info['allegro_benefits_quantity'] == 5 ) {
                          $ils = 'piątą';
                     }                     
                     //
                     $magazyn_cena .= '<div class="RabatAllegro">' . (($info['allegro_benefits_discount'] == 100) ? '<span>gratis</span> za ' : '<span>-' . $info['allegro_benefits_discount'] . '%</span> na ') . ' ' . $info['allegro_benefits_quantity'] . ' sztukę';
                     
                     if ( $info['allegro_benefits_status'] == 0 ) {
                           $magazyn_cena .= '<div class="RoznicaCenSklep TipChmurka"><b>Rabat jest nieaktywny. <br /> Zwiększ liczbę sztuk dostępnych w ofercie !</b>&nbsp;</div>';
                     }
                     
                     $magazyn_cena .= '</div>';
                     //
                     unset($ils);
                     //
                }
                
                // czy jest w zestawie
                if ( $info['allegro_benefits_set_id_set'] != '' ) {
                     //
                     if ((isset($_SESSION['domyslny_uzytkownik_allegro']) && $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'])) {
                          $magazyn_cena .= '<div class="ZestawAllegro"><span class="ZestawAllegroRozwin" id="but_zestaw_' . $info['allegro_id'] . '" onclick="zestaw(' . $info['allegro_id'] . ',\'' . $info['auction_id'] . '\')">Zestaw</span></div>';
                     } else {
                          $magazyn_cena .= '<div class="ZestawAllegro"><span class="ZestawAllegroBrak">Zestaw</span></div>';
                     }
                     //
                }                                

                unset($cena_txt_allegro, $cena_txt_sklep, $cena_allegro, $cena_sklep);
                
                $data_zakonczenia_allegro = '-';
                if ( !empty($info['auction_date_end']) && FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) > 0 ) {
                    $data_zakonczenia_allegro = date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']));
                    //
                    $czas_koniec = time();
                    if ( FunkcjeWlasnePHP::my_strtotime($info['auction_date_start']) > time() ) {
                         $czas_koniec = FunkcjeWlasnePHP::my_strtotime($info['auction_date_start']);
                    }
                    //
                    // za ile dni
                    $zaIleDni = (FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) - $czas_koniec) / 86400;
                    if ( $zaIleDni > 0 && $info['auction_status'] != 'ENDED' && $info['auction_status'] != 'ARCHIWUM' ) {
                         if ( $zaIleDni < 1 ) {
                              if ( date('G',(FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) - $czas_koniec)) > 1 ) {
                                   $zaIleGodzin = date('G',(FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) - $czas_koniec)) . ' godz ' . date('i',(FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) - $czas_koniec)) . ' min';
                                } else {
                                   $zaIleGodzin = date('i',(FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) - $czas_koniec)) . ' min';
                              }
                              $data_zakonczenia_allegro .= '<div class="KoniecAukcjiNiedlugo">koniec za: <b>' . $zaIleGodzin . '</b></div>';
                              unset($zaIleGodzin);
                         } else {
                             $zaIleDni = ceil($zaIleDni);
                             if ( $zaIleDni < 4 ) {
                                  $data_zakonczenia_allegro .= '<div class="KoniecAukcjiNiedlugo">koniec za: <b>' . $zaIleDni . ((($zaIleDni) == 1) ? ' dzień' : ' dni') . '</b></div>';
                              } else {
                                  $data_zakonczenia_allegro .= '<div class="KoniecAukcji">koniec za: <b>' . $zaIleDni . ((($zaIleDni) == 1) ? ' dzień' : ' dni') . '</b></div>';
                             }
                         }
                    } else {
                         $data_zakonczenia_allegro .= '<div class="KoniecAukcjiZakonczona">zakończona</div>';
                    }
                    //
                    unset($czas_koniec);
                    //
                } else {
                    if ( date('Y',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])) > 1970 ) {
                         $data_zakonczenia_allegro = 'do wyczerpania';
                    } else {
                         $data_zakonczenia_allegro = '-';
                    }
                }
                
                $status_prd = '-';

                if ( $info['products_status'] == '1' ) {
                  $status_prd = '<em class="TipChmurka"><b>Produkt w sklepie jest aktywny</b><img src="obrazki/aktywny_on.png" alt="Produkt w sklepie jest aktywny" /></em>';
                } elseif ( $info['products_status'] == '0' ) {
                  $status_prd = '<em class="TipChmurka"><b>Produkt w sklepie jest nieaktywny</b><img src="obrazki/aktywny_off.png" alt="Produkt w sklepie jest nieaktywny" /></em>';
                }

                // opcje aukcji
                $tablica_opcji = explode(',', (string)$info['allegro_options']);
                
                if ( count($tablica_opcji) > 0 ) {
                  
                    $opcje = '<div class="Opcje">';
                    foreach ( $tablica_opcji as $opcja ) {
                        if ( $opcja != '' ) {
                              $opcje .= '<div class="Opcja_' . $opcja . ' OpcjaTlo">';
                              switch ($opcja) {           
                                  case 'promoPackage':
                                      $opcje .= 'P';
                                      break;
                                  case 'emphasized10d':
                                      $opcje .= 'W 10d';
                                      break;
                                  case 'emphasized1d':
                                      $opcje .= 'W 1d';
                                      break;                                      
                                  case 'departmentPage':
                                      $opcje .= 'D';
                                      break;
                              }                            
                              $opcje .= '</div>';
                        }
                    }
                    $opcje .= '</div>';
                    
                }
                unset($tablica_opcji);

                $tablica = array(array($akcja,'center'),
                                 array(((!empty($info['auction_uuid'])) ? '<em class="TipChmurka"><b>Nie zostały pobrane dane aukcji z Allegro</b><span class="brakSynch"></span></em><br />' : '') . (($nr_aukcji != '') ? '<a '.$link.' target="_blank">'.$info['auction_id'].'</a>' : '') . $opcje . (($info['auction_seller'] != '') ? '<div class="LoginKonta">' . ((isset($TablicaUzytkownikow[$info['auction_seller']])) ? $TablicaUzytkownikow[$info['auction_seller']] : '') . '</div>' : ''),'center'),
                                 array($tgm, 'center', '', 'class="ListingSchowaj"'),
                                 array($nazwa_produktu,'left'),
                                 array( $format_img,'center'),
                                 array(((!empty($info['auction_date_start']) && date('Y',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])) > 1970) ? date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])) : '-'),'center', '', 'class="ListingSchowaj"'),
                                 array($data_zakonczenia_allegro,'center', '', 'class="ListingSchowaj"'),
                                 array($magazyn_cena,'center'),
                                 array($status_prd,'center', '', 'class="ListingSchowaj"'),
                                 array($info['products_sold'],'center'),
                                 array($info['auction_watching'],'center', '', 'class="ListingRwdSzeroki"'),
                                 array($status_img,'center'));
                                 
                unset($nr_aukcji, $tgm, $magazyn_cena, $opcje);
                
                $tekst .= $listing_danych->pozycje($tablica);
                
                $tekst .= '<td class="rg_right IkonyPionowo">';
                
                $zmienne_do_przekazania = '?id_poz='.$info['allegro_id'];

                $dodaj_br = false;

                if ( $info['auction_uuid'] == '' ) {
                    $tekst .= '<em class="TipChmurka" style="cursor:pointer" id="widok_' . $info['allegro_id'] . '" onclick="szczegoly(' . $info['allegro_id'] . ')"><b>Szczegóły aukcji</b><img src="obrazki/rozwin.png" alt="Szczegóły aukcji" /></em>'; 
                } elseif ( $info['auction_uuid'] != '' && ((isset($_SESSION['domyslny_uzytkownik_allegro']) && $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'])) ) {
                    $tekst .= '<em class="TipChmurka" style="cursor:pointer" id="widok_' . $info['allegro_id'] . '" onclick="szczegoly(' . $info['allegro_id'] . ')"><b>Szczegóły aukcji</b><img src="obrazki/rozwin.png" alt="Szczegóły aukcji" /></em><br />'; 
                    $tekst .= '<a class="TipChmurka" href="allegro/allegro_zadanie_aktualizuj.php'.$zmienne_do_przekazania.'"><b>Sprawdź status zadania</b><img src="obrazki/allegro_warianty.png" alt="Sprawdź status zadania" /></a>';
                }
                $tekst .= '<a class="TipChmurka" href="allegro/allegro_sprzedaz_tranzakcja.php?aukcja_id='.$info['allegro_id'].'&aukcja_aukcja_id='.$info['auction_id'].'"><b>Lista ofert</b><img src="obrazki/historia.png" alt="Lista ofert" /></a>';
                
                if ( Funkcje::SprawdzAktywneAllegro() ) {
                
                    if (empty($info['auction_uuid']) && ((isset($_SESSION['domyslny_uzytkownik_allegro']) && $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller']))) {

                        if ( $info['archiwum_allegro'] == '0' ) {
                          
                          // usuniecie tylko dla zakonczonych
                          if ( $info['auction_status'] == 'ENDED' && $info['auction_quantity'] > 0 ) {
                              $tekst .= '<a class="TipChmurka" href="allegro/allegro_aukcja_wznow.php'.$zmienne_do_przekazania.'"><b>Wznów aukcję</b><img src="obrazki/allegro_lapka.png" alt="Wznów aukcję" /></a>';                      
                          }
                          
                        } else {

                          $tekst .= '<em class="TipChmurka"><b>Aukcja jest przeniesiona do archiwum Allegro</b><img src="obrazki/allegro_lapka_off.png" alt="Aukcja jest przeniesiona do archiwum Allegro" /></em>';

                        }

                        if ( $info['auction_status'] == 'ACTIVE' && $info['auction_uuid'] == '' && $info['archiwum_allegro'] == '0' ) {
                            $tekst .= '<a class="TipChmurka" href="allegro/allegro_aukcja_zakoncz.php'.$zmienne_do_przekazania.'"><b>Zakończ aukcje na Allegro</b><img src="obrazki/wyloguj.png" alt="Zakończ aukcje na Allegro" /></a>';
                        }
                    
                         $tekst .= '<br /><br />';
                        

                        if ( ( $info['auction_status'] == 'ACTIVE' || $info['auction_status'] == 'ENDED' ) && $info['auction_type'] == 'BUY_NOW' && $info['auction_uuid'] == '' && $info['archiwum_allegro'] == '0' && $ilosc_magazyn > 0 ) {
                          $tekst .= '<a class="TipChmurka" href="allegro/allegro_aukcja_zaktualizuj_ilosc.php'.$zmienne_do_przekazania.'&ilosc='.$ilosc_magazyn.'"><b>Zaktualizuj ilość przedmiotów na aukcji</b><img src="obrazki/powrot.png" alt="Zaktualizuj ilość przedmiotów na aukcji" /></a>';
                        }

                        if ( ( $info['auction_status'] == 'ACTIVE' || $info['auction_status'] == 'ENDED' ) && $info['auction_type'] == 'BUY_NOW' && $info['auction_uuid'] == '' && $info['archiwum_allegro'] == '0' ) {
                          $tekst .= '<a class="TipChmurka" href="allegro/allegro_aukcja_zaktualizuj_parametry.php'.$zmienne_do_przekazania.'"><b>Zaktualizuj parametry aukcji</b><img src="obrazki/edytuj.png" alt="Zaktualizuj parametry aukcji" /></a>';
                        }
       
                    }
                    
                }

                if ( $info['archiwum_allegro'] == '1' || $info['auction_bids'] == '0' || $info['auction_status'] == 'NOT_FOUND' ) {
                    $tekst .= '<a class="TipChmurka" href="allegro/allegro_aukcja_usun.php'.$zmienne_do_przekazania.'"><b>Usuń aukcję z bazy danych</b><img src="obrazki/kasuj.png" alt="Usuń aukcję z bazy danych" /></a>';
                }
                
                if ( Funkcje::SprawdzAktywneAllegro() ) {
                
                    if ((isset($_SESSION['domyslny_uzytkownik_allegro']) && $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'])) {
                    
                        if ( $info['auction_status'] == 'ACTIVE' && $info['auction_uuid'] == '' && $info['archiwum_allegro'] == '0' ) {
                            $tekst .= '<br /><br /><a class="TipChmurka" href="allegro/allegro_aukcja_rabat.php'.$zmienne_do_przekazania.'"><b>Rabat ilościowy</b><img src="obrazki/rabat.png" alt="Rabat ilościowy" /></a>';
                        }                

                    }
                    
                }
                
                $tekst .= '<a class="TipChmurka" href="allegro/allegro_przypisz_produkt.php?id_poz='.$info['allegro_id'].'"><b>Zmień przypisany do aukcji produkt</b><img src="obrazki/wczytaj.png" alt="Zmień przypisany produkt" /></a>';
                
                $tekst .= '</td></tr>';
                
                $tekst .= '<tr><td colspan="13"><div id="szczegoly_' . $info['allegro_id'] . '"></div></td></tr>';
                
                $tekst .= '<tr><td colspan="13"><div id="zestaw_' . $info['allegro_id'] . '"></div></td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);        

        }
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <script>
        $(document).ready(function() {
          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: false
          });                
        });

        function szczegoly(id) {
            //
            if ( $('#szczegoly_' + id).html() != '' ) {
                 //
                 $('#widok_' + id).find('img').attr('src','obrazki/rozwin.png');
                 $('#widok_' + id).find('b').html('Rozwiń szczegóły <br /> aukcji');
                 //              
                 $('#szczegoly_' + id).slideUp('fast', function() {
                    $('#szczegoly_' + id).html('');
                 });
              } else {
                $('#szczegoly_' + id).html('<div class="TloObramowania"><img src="obrazki/_loader_small.gif" alt="" /></div>');
                $.post("ajax/aukcja_szczegoly.php?tok=" + $('#tok').val(),
                    { id: id },
                    function(data) { 
                      //
                      $('#widok_' + id).find('img').attr('src','obrazki/zwin.png');
                      $('#widok_' + id).find('b').html('Zwiń szczegóły <br /> aukcji');
                      //
                      $('#szczegoly_' + id).hide()
                      $('#szczegoly_' + id).html(data);
                      $('#szczegoly_' + id).slideDown('fast');
                      //
                      $(".ZdjecieProduktu").colorbox({ maxWidth:'90%', maxHeight:'90%' });
                    }           
                );  
            }
            //
        }          
        
        function zestaw(id, nr_aukcji) {
            //
            if ( $('#zestaw_' + id).html() != '' ) {
                 //
                 $('#but_zestaw_' + id).addClass('ZestawAllegroRozwin').removeClass('ZestawAllegroZwin');
                 //              
                 $('#zestaw_' + id).slideUp('fast', function() {
                    $('#zestaw_' + id).html('');
                 });
              } else {
                $('#zestaw_' + id).html('<div class="TloObramowania"><img src="obrazki/_loader_small.gif" alt="" /></div>');
                $.post("ajax/aukcja_zestaw_szczegoly.php?tok=" + $('#tok').val(),
                    { id: nr_aukcji },
                    function(data) { 
                      //
                      $('#but_zestaw_' + id).removeClass('ZestawAllegroRozwin').addClass('ZestawAllegroZwin');
                      //
                      $('#zestaw_' + id).hide()
                      $('#zestaw_' + id).html(data);
                      $('#zestaw_' + id).slideDown('fast');
                      //
                    }           
                );  
            }
            //
        }                

        </script>

       <div id="caly_listing">
        
            <div class="poleForm cmxform" style="margin-bottom:10px">
            
              <div class="naglowek">Ustawienia konfiguracji połączenia z Allegro</div>

              <div class="pozycja_edytowana">
              
                <?php require_once('allegro_naglowek.php'); ?>
                
              </div>   
              
            </div>
        
            <div id="ajax"></div>
            
            <?php
            $NazwaKonta = '';
            if ( isset($_SESSION['domyslny_uzytkownik_allegro']) ) {
                 $NazwaKonta = $_SESSION['domyslny_login_allegro'];
            }
                
            // sprawdza czy sa jakies aukcjie niezsynchronizowane
            $zapytanieSynch = "SELECT allegro_id, auction_id, auction_uuid FROM allegro_auctions WHERE auction_uuid != ''";
            $sqlSynch = $db->open_query($zapytanieSynch);

            if ( Funkcje::SprawdzAktywneAllegro() ) {
                ?>
                    
                <div id="BrakSynchronizacji">      
                    
                    <form action="allegro/allegro_synchronizuj_aukcje.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post">
                        <input type="hidden" name="akcja" value="synchronizuj" />
                        <input type="hidden" name="powrot" value="allegro_aukcje" />                        
                        <input type="submit" class="przyciskSynchronizacja" style="margin-right:10px" value="Synchronizuj dane z Allegro<?php echo (($NazwaKonta != '') ? ' dla konta ' . $NazwaKonta : ''); ?>" />
                        <div style="margin:2px 0px 10px 0px" class="cmxform">
                            <input type="checkbox" id="synch_zero" name="stan_zero" value="1" /> <label class="OpisFor" for="synch_zero">przy synchronizacji wyłącz aukcje dla produktów ze stanem magazynowym 0 szt</label> <br />
                            <input type="checkbox" id="synch_promowanie" name="stan_promowanie" value="1" /> <label class="OpisFor" for="synch_promowanie">przy synchronizacji pobierz informacje o opcjach promowania poszczególnych ofert</label> <br />
                            <div style="height:5px"></div>
                            <b style="display:inline-block;margin-right:10px;color:#3f5d6b">Informacje o prowizji:</b>
                            <input type="radio" id="synch_prowizja_brak" name="stan_prowizja" value="0" checked="checked" /> <label class="OpisFor" for="synch_prowizja_brak">nie pobieraj</label>
                            <input type="radio" id="synch_prowizja_zero" name="stan_prowizja" value="1" /> <label class="OpisFor" for="synch_prowizja_zero">wyzeruj dane</label>
                            <input type="radio" id="synch_prowizja_prognoza" name="stan_prowizja" value="2" /> <label class="OpisFor" for="synch_prowizja_prognoza">pobierz informacje o prognozowanej prowizji od sprzedaży jednego przedmiotu</label>
                        </div>
                    </form>     

                    <?php if ((int)$db->ile_rekordow($sqlSynch) > 0) { ?>

                        <span style="margin-top:5px">Należy wykonać synchronizację z Allegro - nie wszystkie aukcje mają aktualne dane</span>
                        
                    <?php } ?>
                        
                </div>
 
                <?php
            }

            $db->close_query($sqlSynch);
            unset($zapytanieSynch);        
            ?>              
            
            <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
            
                <div id="naglowek_cont">Obsługa aukcji - data ostatniej synchronizacji : <?php echo date("d-m-Y H:i:s", $AllegroRest->polaczenie['CONF_LAST_SYNCHRONIZATION']); ?></div> 
                
            <?php } else { ?>
            
                <div id="naglowek_cont">Obsługa aukcji - brak połączenia z Allegro</div> 
                
            <?php } ?>

            <div class="cl"></div>

            <div id="wyszukaj">
                <form action="allegro/allegro_aukcje.php" method="post" id="allegroForm" class="cmxform">

                    <div id="wyszukaj_text">
                        <span>Wyszukaj aukcje:</span>
                        <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                    </div>  
                    
                    <div class="wyszukaj_select">
                        <span>Nr kat produktu:</span>
                        <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['nrkat'])) : ''); ?>" size="20" />
                    </div>                           
                    
                    <div class="wyszukaj_select">
                        <span>Kod EAN:</span>
                        <input type="text" name="ean" value="<?php echo ((isset($_GET['ean'])) ? $filtr->process($_GET['ean']) : ''); ?>" size="20" />
                    </div>                      
                    
                    <div class="wyszukaj_select">
                        <span>Sygnatura:</span>
                        <input type="text" name="sygnatura" value="<?php echo ((isset($_GET['sygnatura'])) ? $filtr->process($_GET['sygnatura']) : ''); ?>" size="20" />
                    </div>                      

                    <div class="wyszukaj_select">
                        <span>Producent:</span>                                        
                        <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : '')); ?>
                    </div>                    
                    
                    <div class="wyszukaj_select">
                        <span>Status:</span>
                        <?php
                        $tablica_status = Array();
                        $tablica_status[] = array('id' => '0', 'text' => '-- dowolny --');
                        $tablica_status[] = array('id' => 'ACTIVE', 'text' => 'trwająca');
                        $tablica_status[] = array('id' => 'ENDED', 'text' => 'zakończona');
                        $tablica_status[] = array('id' => 'ACTIVATING', 'text' => 'oczekująca');
                        $tablica_status[] = array('id' => 'ARCHIVE', 'text' => 'archiwum');
                        $tablica_status[] = array('id' => 'NOT_FOUND', 'text' => 'nie znalezione w Allegro');
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); 
                        unset($tablica_status);
                        ?>
                    </div>  

                    <div class="wyszukaj_select">
                        <span>Status produktu:</span>
                        <?php   
                        $tablica_status_produktu = array();
                        $tablica_status_produktu[] = array('id' => '0', 'text' => '-- dowolny --');
                        $tablica_status_produktu[] = array('id' => '1', 'text' => 'aktywne');
                        $tablica_status_produktu[] = array('id' => '2', 'text' => 'nieaktywne');                        
                        echo Funkcje::RozwijaneMenu('szukaj_status_produktu', $tablica_status_produktu, ((isset($_GET['szukaj_status_produktu'])) ? $filtr->process($_GET['szukaj_status_produktu']) : '')); 
                        unset($tablica_status_produktu)
                        ?>
                    </div>  

                    <div class="wyszukaj_select">
                        <span>Data rozpoczęcia:</span>
                        <input type="text" id="data_rozpoczecia_od" name="szukaj_data_rozpoczecia_od" value="<?php echo ((isset($_GET['szukaj_data_rozpoczecia_od'])) ? $filtr->process($_GET['szukaj_data_rozpoczecia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_rozpoczecia_do" name="szukaj_data_rozpoczecia_do" value="<?php echo ((isset($_GET['szukaj_data_rozpoczecia_do'])) ? $filtr->process($_GET['szukaj_data_rozpoczecia_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>                      

                    <div class="wyszukaj_select">
                        <span>Data końca:</span>
                        <input type="text" id="data_zakonczenia_od" name="szukaj_data_zakonczenia_od" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_od'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_zakonczenia_do" name="szukaj_data_zakonczenia_do" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_do'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>  

                    <div class="wyszukaj_select">
                        <span>Opcja dodatkowa:</span>
                        <?php
                        $tablica_opcja = Array();
                        $tablica_opcja[] = array('id' => '0', 'text' => '-- dowolna --');
                        $tablica_opcja[] = array('id' => 'emphasized10d', 'text' => 'wyróżnienie (10 dni)');
                        $tablica_opcja[] = array('id' => 'emphasized1d', 'text' => 'wyróżnienie (1 dzień)');
                        $tablica_opcja[] = array('id' => 'promoPackage', 'text' => 'pakiet Promo');                        
                        $tablica_opcja[] = array('id' => 'departmentPage', 'text' => 'strona działu');
                        echo Funkcje::RozwijaneMenu('szukaj_opcja', $tablica_opcja, ((isset($_GET['szukaj_opcja'])) ? $filtr->process($_GET['szukaj_opcja']) : '')); 
                        unset($tablica_opcja);
                        ?>
                    </div> 
                    
                    <div class="wyszukaj_select">
                        <span>Rabat ilościowy:</span>
                        <?php      
                        $tablica_rabat_allegro = array();
                        $tablica_rabat_allegro[] = array('id' => '0', 'text' => '-- wszystkie --');
                        $tablica_rabat_allegro[] = array('id' => '1', 'text' => 'tak');                
                        echo Funkcje::RozwijaneMenu('szukaj_rabat', $tablica_rabat_allegro, ((isset($_GET['szukaj_rabat'])) ? $filtr->process($_GET['szukaj_rabat']) : '')); 
                        unset($tablica_rabat_allegro);
                        ?>
                    </div> 

                    <div class="wyszukaj_select">
                        <span>Aukcje w zestawach:</span>
                        <?php      
                        $tablica_zestaw_allegro = array();
                        $tablica_zestaw_allegro[] = array('id' => '0', 'text' => '-- wszystkie --');
                        $tablica_zestaw_allegro[] = array('id' => '1', 'text' => 'nie');                       
                        $tablica_zestaw_allegro[] = array('id' => '2', 'text' => 'tak');                       
                        echo Funkcje::RozwijaneMenu('szukaj_zestaw', $tablica_zestaw_allegro, ((isset($_GET['szukaj_zestaw'])) ? $filtr->process($_GET['szukaj_zestaw']) : '')); 
                        unset($tablica_zestaw_allegro);
                        ?>
                    </div>                    

                    <div class="wyszukaj_select">
                        <span>Konto:</span>
                        <?php
                        echo Funkcje::RozwijaneMenu('login_aukcji', $TablicaUzytkownikowFiltry, ((isset($_GET['login_aukcji'])) ? $filtr->process($_GET['login_aukcji']) : '')); 
                        ?>
                    </div>                     
                    
                    <div class="wyszukaj_select">
                        <span>Do synchronizacji:</span>
                        <?php
                        $tablica_synchro[] = array('id' => '0', 'text' => '-- dowolne --');
                        $tablica_synchro[] = array('id' => '1', 'text' => 'tak');                       
                        //
                        echo Funkcje::RozwijaneMenu('synchronizacja', $tablica_synchro, ((isset($_GET['synchronizacja'])) ? $filtr->process($_GET['synchronizacja']) : '')); 
                        //
                        unset($tablica_opcja);
                        ?>
                    </div>                     

                    <div class="wyszukaj_select">
                        <span style="color:#ff0000">Aukcje z różnicą magazynów:</span>
                        <?php      
                        $tablica_stan_mag = array();
                        $tablica_stan_mag[] = array('id' => '0', 'text' => '-- wszystkie --');
                        $tablica_stan_mag[] = array('id' => '1', 'text' => 'ilość na aukcji mniejsza od stanu w sklepie');                      
                        $tablica_stan_mag[] = array('id' => '2', 'text' => 'ilość na aukcji większa od stanu w sklepie');                      
                        $tablica_stan_mag[] = array('id' => '3', 'text' => 'stan magazynowy produktów w sklepie równy lub mniejszy od 0'); 
                        $tablica_stan_mag[] = array('id' => '4', 'text' => 'stan magazynowy produktów w sklepie większy od 0'); 
                        echo Funkcje::RozwijaneMenu('szukaj_stan_mag', $tablica_stan_mag, ((isset($_GET['szukaj_stan_mag'])) ? $filtr->process($_GET['szukaj_stan_mag']) : ''), ' style="width:180px"'); 
                        unset($tablica_stan_mag);
                        ?>
                    </div>  

                    <div class="wyszukaj_select">
                        <span style="color:#ff0000">Aukcje z różnicą cen:</span>
                        <?php      
                        $tablica_stan_cen = array();
                        $tablica_stan_cen[] = array('id' => '0', 'text' => '-- wszystkie --');
                        $tablica_stan_cen[] = array('id' => '1', 'text' => 'cena w sklepie większa niż na aukcji');                      
                        $tablica_stan_cen[] = array('id' => '2', 'text' => 'cena w sklepie mniejsza niż na aukcji');
                        $tablica_stan_cen[] = array('id' => '3', 'text' => 'cena produktu w zakładce danych dla Allegro większa niż na aukcji');
                        $tablica_stan_cen[] = array('id' => '4', 'text' => 'cena produktu w zakładce danych dla Allegro mniejsza niż na aukcji');
                        echo Funkcje::RozwijaneMenu('szukaj_stan_cen', $tablica_stan_cen, ((isset($_GET['szukaj_stan_cen'])) ? $filtr->process($_GET['szukaj_stan_cen']) : ''), ' style="width:180px"'); 
                        unset($tablica_stan_cen);
                        ?>
                    </div>   

                    <?php                       
                    // tworzy ukryte pola hidden do wyszukiwania - filtra 
                    if (isset($_GET['kategoria_allegro'])) { 
                        echo '<div><input type="hidden" name="kategoria_allegro" value="'.(int)$_GET['kategoria_allegro'].'" /></div>';
                    } 
                    if (isset($_GET['kategoria_id'])) { 
                        echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                    }                         
                    if (isset($_GET['sort'])) { 
                        echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                    }                
                    ?>                

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                // jezeli tylko filtr kategorii to nie wyswietla filtrow
                $filtry_poza_kategoriami = false;
                //
                if ( isset($_SESSION['filtry']['allegro_aukcje.php']) ) {
                     //
                     foreach ($_SESSION['filtry']['allegro_aukcje.php'] as $klucz => $wartosc) {
                        //
                        if ( $klucz != 'kategoria_allegro' && $klucz != 'kategoria_id' ) {
                             $filtry_poza_kategoriami = true;
                        }
                        //
                     }                      
                     //
                }
                //
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true && $filtry_poza_kategoriami == true ) {
                     echo '<div id="wyszukaj_ikona"><a href="allegro/allegro_aukcje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                //
                unset($filtry_poza_kategoriami);
                ?>                 

                <div style="clear:both"></div>
                
            </div> 

            <div style="clear:both"></div>

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a1">numeru malejąco</a>
                <a id="sort_a2" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a2">numeru rosnąco</a>
                <a id="sort_a9" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a9">daty rozpoczęcia malejąco</a>
                <a id="sort_a10" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a10">daty rozpoczęcia rosnąco</a>                                
                <a id="sort_a3" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a3">daty końca malejąco</a>
                <a id="sort_a4" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a4">daty końca rosnąco</a>
                <a id="sort_a5" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a5">wyświetleń malejąco</a>
                <a id="sort_a6" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a6">wyświetleń rosnąco</a>
                <a id="sort_a7" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a7">sprzedaż malejąco</a>
                <a id="sort_a8" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a8">sprzedaż rosnąco</a>
                <a id="sort_a11" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a11">ilość produktów na Allegro malejąco</a>
                <a id="sort_a12" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a12">ilość produktów na Allegro rosnąco</a>
                                
            </div> 
            
            <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
            
            <div id="PrzyciskiAukcji">
            
                <div class="lf">
                    <a class="dodaj" href="allegro/allegro_dodaj_aukcje.php">dodaj nową aukcję spoza sklepu</a>                         
                    <a style="margin-left:15px" class="dodaj" href="allegro/allegro_dodaj_aukcje_masowo.php">dodaj wiele aukcji spoza sklepu</a>   
                    <?php if ($ile_pozycji > 0) { ?>
                    <a class="UtworzZestaw" href="allegro/allegro_aukcja_zestaw.php">utwórz zestaw produktów</a>  
                    <?php } ?>
                    <a style="margin-left:15px" class="usun" href="allegro/allegro_aukcje_usun_masowe.php">usuń aukcje z bazy sklepu</a>
                </div>  

                <div style="clear:both"></div>
            
            </div>
            
            <?php } ?>
                
            <form action="allegro/allegro_aukcje_akcja.php" method="post" class="cmxform">
                
                <script>
                $(document).ready(function() {
                  
                    $('#RozwijanieKategorii').off('click').click(function() {
                        //
                        if ($('#KategorieAllegro').css('display') != 'none') { 
                            //
                            $('#KategorieAllegro').slideUp();
                            $('#KategorieSklepu').slideUp();
                            //
                          } else {
                            //
                            $('#KategorieAllegro').slideDown();
                            $('#KategorieSklepu').slideDown();
                            //
                        }     
                        //   
                    }); 

                });
                </script>
                
                <div id="RozwijanieKategorii">
                
                    <span>FILTRY KATEGORII</span>
                
                </div>
                
                <div id="KategorieAllegro" <?php echo (((isset($_GET['kategoria_id']) && (int)isset($_GET['kategoria_id']) > 0) || (isset($_GET['kategoria_allegro']) && (int)isset($_GET['kategoria_allegro']) > 0)) ? 'style="display:block"' : ''); ?>>
                
                    <div class="OknoNaglowek">
                        
                        Kategorie Allegro
                        
                        <?php if ( isset($_GET['kategoria_allegro']) && (int)isset($_GET['kategoria_allegro']) > 0 ) { ?>
                        
                        <a class="WylaczFiltrKategorie" href="allegro/allegro_aukcje.php?kategoria_allegro_usun=tak">wyłącz filtr kategorii</a>

                        <?php } ?>
                        
                    </div>
                    
                    <?php                    
                    $zapytanieKat = "SELECT DISTINCT ac.allegro_category, ac.allegro_category_name, (SELECT count(acs.allegro_category_name) FROM allegro_auctions acs WHERE ac.allegro_category_name = acs.allegro_category_name) as ilosc FROM allegro_auctions ac ORDER BY ac.allegro_category_name";
                    $sqlKat = $db->open_query($zapytanieKat); 
                    //
                    echo '<ul>'; 
                    //
                    while ( $info = $sqlKat->fetch_assoc() ) {
                        //
                        $nazwa_kat_tablica = explode(';', substr((string)$info['allegro_category_name'], 0, -1));
                        $nazwa_kat = '';
                        //
                        for ( $x = 0; $x < count($nazwa_kat_tablica); $x++ ) {
                            //
                            if ( $x == count($nazwa_kat_tablica) - 1 ) {
                                 $nazwa_kat .= '<b>' . $nazwa_kat_tablica[$x] . '</b>';                                 
                            } else {
                                 $nazwa_kat .= $nazwa_kat_tablica[$x] . ' > ';
                            }
                            //
                        }
                        //
                        if ( $nazwa_kat != '' && $info['ilosc'] > 0 ) {
                             //
                             if (isset($_GET['kategoria_allegro']) && (int)$_GET['kategoria_allegro'] > 0 && (int)$_GET['kategoria_allegro'] == $info['allegro_category']) {
                                 echo '<li class="AktywnaKategoria">';
                               } else {
                                 echo '<li>';
                             }
                             echo '<a href="allegro/allegro_aukcje.php?kategoria_allegro=' . $info['allegro_category'] . '">' . $nazwa_kat . (($nazwa_kat != '<b>BRAK</b>') ? ' <span>(' . $info['ilosc'] . ')</span>' : ''). '</a></li>';
                             //
                        }
                        //
                        unset($nazwa_kat_tablica, $nazwa_kat);
                        //
                    }
                    //
                    echo '</ul>'; 
                    //
                    $db->close_query($sqlKat);
                    unset($zapytanieKat, $info);  
                    ?>

                </div>
                
                <div id="KategorieSklepu" <?php echo (((isset($_GET['kategoria_id']) && (int)isset($_GET['kategoria_id']) > 0) || (isset($_GET['kategoria_allegro']) && (int)isset($_GET['kategoria_allegro']) > 0)) ? 'style="display:block"' : ''); ?>>
                
                    <div class="OknoNaglowek">
                    
                        Kategorie wg sklepu
                    
                        <?php if ( isset($_GET['kategoria_id']) && (int)isset($_GET['kategoria_id']) > 0 ) { ?>
                        
                        <a class="WylaczFiltrKategorie" href="allegro/allegro_aukcje.php?kategoria_sklep_usun=tak">wyłącz filtr kategorii</a>

                        <?php } ?>                    
                        
                    </div>
                    
                    <?php
                    echo '<div class="OknoKategoriiAllegro"><table class="pkc">';
                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                        $podkategorie = false;
                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                        // sprawdza czy nie jest wybrana
                        $style = '';
                        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                            if ((int)$_GET['kategoria_id'] == $tablica_kat[$w]['id']) {
                                $style = ' style="color:#ff0000"';
                            }
                        }
                        //
                        echo '<tr>
                                <td class="lfp"><a href="allegro/allegro_aukcje.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'allegro\')" />' : '').'</td>
                              </tr>
                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                    }
                    if ( count($tablica_kat) == 0 ) {
                         echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                    }
                    echo '</table></div>';
                    unset($tablica_kat,$podkategorie,$style);
                    ?>        

                    <?php 
                    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                        $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                        $cSciezka = explode("_", (string)$sciezka);                    
                        if (count($cSciezka) > 1) {
                            //
                            $ostatnie = strRpos($sciezka,'_');
                            $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                            ?>
                            
                            <script>         
                            podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','allegro');
                            </script>
                            
                        <?php
                        unset($sciezka,$cSciezka);
                        }
                    } 
                    ?>                    

                </div>
            
                <div class="cl"></div>

                <div id="wynik_zapytania"></div>
                <div id="aktualna_pozycja">1</div>
                
                <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>

                <div id="akcja">
                
                  <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                  
                  <div class="lf" style="padding-right:20px">
                  
                    <span onclick="akcja(1)">zaznacz wszystkie</span>
                    <span onclick="akcja(2)">odznacz wszystkie</span>
                  </div>
                  
                  <div id="akc">
                  
                    Wykonaj akcje: 
                    
                    <select name="akcja_dolna" id="akcja_dolna">
                      <option value="0"></option>
                      <option value="2">wznów zaznaczone aukcje</option>
                      <option value="4">zakończ zaznaczone aukcje</option>
                      <option value="5">zaktualizuj ilość produktów w zaznaczonych aukcjach</option>
                      <option value="6">zaktualizuj ceny produktów w zaznaczonych aukcjach</option>
                      <option value="11">zaktualizuj ilość produktów we wszystkich aukcjach</option>
                      <option value="10">zaktualizuj ceny produktów we wszystkich aukcjach</option>
                      <option value="12">usuń zaznaczone aukcje z bazy danych</option>
                    </select>
                    
                  </div>
                  
                  <div style="clear:both;"></div>
                  
                </div>
                
                <?php } ?>

                <div id="page"></div>

                <div id="dolny_pasek_stron"></div>
                <div id="pokaz_ile_pozycji"></div>
                <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
                <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
                
                <?php if ($ile_pozycji > 0) { ?>
                <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Wykonaj" /></div>
                <?php } ?>                
                
                <?php } ?>

            </form>

            <div id="LegendaOpcji">
                            
                <span class="Opcja_emphasized10d OpcjaTlo">W 10d</span> wyróżnienie 10 dni &nbsp; 
                <span class="Opcja_emphasized1d OpcjaTlo">W 1d</span> wyróżnienie 1 dzień &nbsp;  
                <span class="Opcja_promoPackage OpcjaTlo">P</span> pakiet Promo &nbsp;
                <span class="Opcja_departmentPage OpcjaTlo">D</span> strona działu
            
            </div>

            <div style="clear:both;"></div>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('allegro/allegro_aukcje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'allegro_id'); ?>
            </script>                

        </div>
        
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
