<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['wszystkie']) ) {
     //
     unset($_SESSION['filtry']['zestawy_produktow.php']);
     //
     Funkcje::PrzekierowanieURL('zestawy_produktow.php');
}
if ( isset($_GET['aktywne']) ) {
     //
     unset($_SESSION['filtry']['zestawy_produktow.php']);
     $_SESSION['filtry']['zestawy_produktow.php']['status'] = 'tak';
     //
     Funkcje::PrzekierowanieURL('zestawy_produktow.php');
}
if ( isset($_GET['nieaktywne']) ) {
     //
     unset($_SESSION['filtry']['zestawy_produktow.php']);
     $_SESSION['filtry']['zestawy_produktow.php']['status'] = 'nie';
     //
     Funkcje::PrzekierowanieURL('zestawy_produktow.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $zestawy = true;

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        //
        if ( isset($_SESSION['filtry']['zestawy_produktow.php']['opcja_numer']) && $_SESSION['filtry']['zestawy_produktow.php']['opcja_numer'] == 'nazwa' ) {
             $_GET['szukaj'] = rawurldecode($_GET['szukaj']);
             //
             $_SESSION['filtry']['zestawy_produktow.php']['szukaj'] = Listing::podmienMagic($_GET['szukaj'], 'wlacz');             
             //
             unset($_SESSION['filtry']['zestawy_produktow.php']['opcja_numer']);
        }
        //
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and pd.products_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }   
    
    // jezeli jest nr kat lub id
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
        //
        if ( isset($_SESSION['filtry']['zestawy_produktow.php']['opcja_numer']) && $_SESSION['filtry']['zestawy_produktow.php']['opcja_numer'] == 'nr_katalogowy' ) {
             $_GET['nrkat'] = rawurldecode($_GET['nrkat']); 
             //
             $_SESSION['filtry']['zestawy_produktow.php']['nrkat'] = Listing::podmienMagic($_GET['nrkat'], 'wlacz');            
             //
             unset($_SESSION['filtry']['zestawy_produktow.php']['opcja_numer']);
        }
        //    
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_man_code like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
        unset($szukana_wartosc);
    }

    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . $id_klienta . ", p.customers_group_id) ";        
        unset($id_klienta);
    }    
    
    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }

    // jezeli jest wybrany status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        if ( $_GET['status'] != 'tak_listing' && $_GET['status'] != 'tak_nie_listing' ) {
             $warunki_szukania .= " and p.products_status = '".(($_GET['status'] == 'tak') ? '1' : '0')."'";
        } else if ( $_GET['status'] == 'tak_listing' ) { 
             $warunki_szukania .= " and p.products_status = '1' and p.listing_status = '0'";
        } else if ( $_GET['status'] == 'tak_nie_listing' ) { 
             $warunki_szukania .= " and p.products_status = '1' and p.listing_status = '1'";
        }
    }      
    
    // data dodania
    if ( isset($_GET['szukaj_data_dodania_od']) && $_GET['szukaj_data_dodania_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_od'] . ' 00:00:00')));
        $warunki_szukania .= " and p.products_date_added >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dodania_do']) && $_GET['szukaj_data_dodania_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_do'] . ' 23:59:59')));
        $warunki_szukania .= " and p.products_date_added <= '".$szukana_wartosc."'";
    }    
    
    // data dostepnosci
    if ( isset($_GET['szukaj_data_dostepnosci_od']) && $_GET['szukaj_data_dostepnosci_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dostepnosci_od'])));
        $warunki_szukania .= " and p.products_date_available >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dostepnosci_do']) && $_GET['szukaj_data_dostepnosci_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dostepnosci_do'])));
        $warunki_szukania .= " and p.products_date_available <= '".$szukana_wartosc."'";
    }   

    // dostepnosc produktu
    if (isset($_GET['dostep']) && (int)$_GET['dostep'] > 0) {
        $id_dostepnosci = (int)$_GET['dostep'];
        $warunki_szukania .= " and p.products_availability_id = '".$id_dostepnosci."'";
        unset($id_dostepnosci);
    }   

    // jezeli jest opcja
    if (isset($_GET['opcja']) && !empty($_GET['opcja'])) {
        switch ($_GET['opcja']) {
            case "nowosc":
                $warunki_szukania .= " and p.new_status = '1'";
                break;
            case "promocja":
                $warunki_szukania .= " and p.specials_status = '1'";
                break;
            case "wyprzedaz":
                $warunki_szukania .= " and p.sale_status = '1'";
                break;                                
            case "hit":
                $warunki_szukania .= " and p.star_status = '1'";
                break; 
            case "polecany":
                $warunki_szukania .= " and p.featured_status = '1'";
                break;   
            case "export":
                $warunki_szukania .= " and p.export_status = '1'";
                break; 
            case "negoc":
                $warunki_szukania .= " and p.products_make_an_offer = '1'";
                break;     
            case "wysylka_gratis":
                $warunki_szukania .= " and p.free_shipping_status = '1'";
                break;             
            case "wykluczona_darmowa_wysylka":
                $warunki_szukania .= " and p.free_shipping_excluded = '1'";
                break;       
            case "ikona_1":
                $warunki_szukania .= " and p.icon_1_status = '1'";
                break; 
            case "ikona_2":
                $warunki_szukania .= " and p.icon_2_status = '1'";
                break;     
            case "ikona_3":
                $warunki_szukania .= " and p.icon_3_status = '1'";
                break;     
            case "ikona_4":
                $warunki_szukania .= " and p.icon_4_status = '1'";
                break;     
            case "ikona_5":
                $warunki_szukania .= " and p.icon_5_status = '1'";
                break;                 
        }     
    } 
    
    // jezeli sa dodatkowe opcje
    if (isset($_GET['dodatkowe_opcje']) && !empty($_GET['dodatkowe_opcje'])) {
        switch ($_GET['dodatkowe_opcje']) {
            case "bez_magazynu":
                $warunki_szukania .= " and p.products_control_storage = 0";
                break;                         
        }     
    }    
    
    $warunki_dod_pol = '';
    if ( isset($_GET['dod_pole_nazwa']) ) {
         $warunki_dod_pol .= ' and ptpef.products_extra_fields_id = "' . (int)$_GET['dod_pole_nazwa'] . '"';
    }    
 
    $zapytanie = 'SELECT p.products_id, 
                         p.products_price_tax, 
                         p.products_tax,
                         p.products_old_price,
                         p.products_quantity,
                         p.sort_order,
                         p.customers_group_id,
                         p.manufacturers_id,
                         p.products_image, 
                         p.products_model,
                         p.products_ean,
                         p.products_man_code,
                         p.products_date_added, 
                         p.products_status, 
                         p.products_buy,
                         p.products_make_an_offer, 
                         p.new_status,
                         p.star_status,
                         p.star_date,
                         p.star_date_end,                         
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,
                         p.sale_status,
                         p.featured_status,
                         p.featured_date,
                         p.featured_date_end,                         
                         p.export_status,
                         p.free_shipping_status,
                         p.free_shipping_excluded,
                         p.listing_status,
                         p.icon_1_status,
                         p.icon_2_status,
                         p.icon_3_status,
                         p.icon_4_status,
                         p.icon_5_status,                         
                         p.products_points_only,
                         p.products_points_value,
                         p.products_points_value_money,
                         p.products_currencies_id,
                         p.products_tax_class_id,
                         p.products_control_storage,
                         p.products_purchase_price,
                         p.products_set_products,
                         p.products_id_private,
                         pd.language_id, 
                         pd.products_name, 
                         pd.products_seo_url,
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && $_GET['blad'] == 'kategoria')) ? 'pc.categories_id,' : '').'
                         m.manufacturers_id,
                         m.manufacturers_name,
                         pj.products_jm_quantity_type
                  FROM products p
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && $_GET['blad'] == 'kategoria')) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id
                         LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                         ' . (( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'pola') || isset($_GET['dod_pole_nazwa']) ) ? 'RIGHT JOIN products_to_products_extra_fields ptpef ON p.products_id = ptpef.products_id' . $warunki_dod_pol : '') . '
                         WHERE p.products_set = 1 ' . $warunki_szukania . '
                         ' . ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'bez_pola') ? ' AND p.products_id not in ( SELECT products_id FROM products_to_products_extra_fields )' : '') . '
                         GROUP BY p.products_id'; 

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = 'SELECT p.products_id
                         FROM products p
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && $_GET['blad'] == 'kategoria')) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id';
                         if ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'pola') || isset($_GET['dod_pole_nazwa']) ) {
                            $ZapytanieDlaPozycji .= ' RIGHT JOIN products_to_products_extra_fields ptpef ON p.products_id = ptpef.products_id' . $warunki_dod_pol;
                         } 
    $ZapytanieDlaPozycji .= ' WHERE p.products_set = 1 ' . $warunki_szukania . 
                         ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'bez_pola') ? ' AND p.products_id not in ( SELECT products_id FROM products_to_products_extra_fields )' : '') .
                         ' GROUP BY p.products_id ';

    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    $sortowanie = $GLOBALS['DomyslneSortowanie'];
    //
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a17":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;  
            case "sort_a9":
                $sortowanie = 'p.products_price_tax asc, p.products_id';
                break;
            case "sort_a10":
                $sortowanie = 'p.products_price_tax desc, p.products_id';
                break;                        
            case "sort_a3":
                $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
                break;
            case "sort_a5":
                $sortowanie = 'p.products_date_added asc, pd.products_name, p.products_id';
                break; 
            case "sort_a6":
                $sortowanie = 'p.products_date_added desc, pd.products_name, p.products_id';
                break; 
            case "sort_a13":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a14":
                $sortowanie = 'p.products_id asc';
                break;    
            case "sort_a15":
                $sortowanie = 'p.sort_order desc, p.products_id';
                break;
            case "sort_a16":
                $sortowanie = 'p.sort_order asc, p.products_id';
                break;                        
        }            
    }  
    
    $zapytanie .= (($sortowanie != '') ? " order by ".$sortowanie : '');    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa zestawu');
            $tablica_naglowek[] = array('Cena');
            $tablica_naglowek[] = array('Ilość','center');
            $tablica_naglowek[] = array('Sort','center');
            $tablica_naglowek[] = array('Status zestawu','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" name="opcja[]" value="'.$info['products_id'].'" id="opcja_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['products_id'].'"></label><input type="hidden" name="id[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['products_id'],'center'); 

                  // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                  $info['products_name'] = Funkcje::PodzielNazwe($info['products_name']);
                  $info['products_model'] = Funkcje::PodzielNazwe($info['products_model']);

                  if ( !empty($info['products_image']) ) {
                       //
                       $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                       $tgm .= '<div class="zoom" id="duze_foto_' . $info['products_id'] . '">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                       $tgm .= '<div id="male_foto_' . $info['products_id'] . '">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true) . '</div>';
                       $tgm .= '</div>';
                       //
                     } else { 
                       //
                       $tgm = '-';
                       //
                  }
                  
                  $tablica[] = array($tgm, 'center');    
                  
                  // ladowanie info o produkcie z zew pliku
                  include('produkty/produkt_info_nazwa.php');
                  $tablica[] = array($tgm);
                  unset($tgm, $tgm_ajax);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_producenta, $prd);
                  
                  $IkonaWyprzedaz = '';
                  if ( $info['sale_status'] == '1' && $info['specials_status'] == '0'  ) {
                     $IkonaWyprzedaz = '<em class="TipChmurka"><b>Cena jest wyprzedażą</b><img src="obrazki/wyprzedaz.png" alt="Wyprzedaż" /></em>';
                  }
                  
                  if ( ((FunkcjeWlasnePHP::my_strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) ) {
                     $IkonaPromocja = '<em class="TipChmurka"><b>Promocja nie jest wyświetlana ze względu na datę rozpoczęcia lub zakończenia promocji</b><img src="obrazki/promocja_wylaczona.png" alt="Promocja nieaktywna" /></em>';
                   } else {
                     $IkonaPromocja = '<em class="TipChmurka"><b>Cena jest promocyjna</b><img src="obrazki/promocja.png" alt="Cena jest promocyjna" /></em>';
                  }
                   
                  $tablica[] = array('<div class="cena" style="white-space:nowrap">Cena brutto zestawu: '.(($info['specials_status'] == '1' || Funkcje::czyNiePuste($info['specials_date']) || Funkcje::czyNiePuste($info['specials_date_end'])) ? $IkonaPromocja : '') . $IkonaWyprzedaz . '
                                      <input type="text" name="cena_'.$info['products_id'].'" value="'.$info['products_price_tax'].'" class="CenaProduktuPole" disabled="disabled" />                                     
                                      Cena poprzednia:
                                      <input type="text" name="cenaold_'.$info['products_id'].'" value="'.(((float)$info['products_old_price'] == 0) ? '' : $info['products_old_price']).'" class="CenaProduktuPole" onchange="zamien_krp($(this))" />                                      
                                      </div>');                  
                                      
                  unset($IkonaPromocja, $IkonaWyprzedaz);

                  // ilosc  
                  // jezeli jednostka miary calkowita
                  if ( $info['products_jm_quantity_type'] == 1 ) {
                       $info['products_quantity'] = (int)$info['products_quantity'];
                  }                     
                  $tablica[] = array((($info['products_quantity'] <= 0) ? '<span class="NiskiStan">'.$info['products_quantity'].'</span>' : $info['products_quantity']),'center');                                                         
                  
                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['products_id'].'" value="'.$info['sort_order'].'" class="sort_prod" />','center');                    

                  $bezMagazynu = '';
                  if ($info['products_control_storage'] == '0') {
                      $bezMagazynu = '<div class="BezMagazynu TipChmurka"><b>Zestaw ma wyłączoną kontrolę stanu magazynowego</b></div>';
                  }                        
                  $tablica[] = array( (($wylacz_status == true) ? '<div class="wylKat TipChmurka"><b>Kategoria do której należy produkt jest wyłączona</b>' : '') . '<input type="checkbox" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' id="status_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="status_'.$info['products_id'].'"></label>' . (($wylacz_status == true) ? '</div>' : '') . $bezMagazynu,'center');
                  unset($bezMagazynu);
                  
                  $tekst .= $listing_danych->pozycje($tablica);

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'].'&zestaw';   

                  // ustala jaka ma byc tresc linku
                  $linkSeo = ((!empty($info['products_seo_url'])) ? $info['products_seo_url'] : $info['products_name']);                  
                                      
                  $tekst .= '<td class="rg_right IkonyPionowo">';                 
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a><br />'; 
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj</b><img src="obrazki/duplikuj.png" alt="Duplikuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" target="_blank" href="' . Seo::link_SEO( $linkSeo, $info['products_id'], 'produkt', '', false ) . '"><b>Zobacz w sklepie</b><img src="obrazki/zobacz.png" alt="Zobacz w sklepie" /></a>';
                  $tekst .= '</td></tr>';   

                  $tekst .= '<tr class="pozycjaRwd"><td colspan="9" style="background:#efefef">';
                  
                      // nowosci - automatyczne czy reczne
                      $InputNowosci = '<li><input type="checkbox" name="nowosc_'.$info['products_id'].'" id="nowosc_'.$info['products_id'].'" value="1" '.(($info['new_status'] == '1') ? 'checked="checked"' : '').' /><label class="OpisFor" for="nowosc_'.$info['products_id'].'">nowość</label></li>';
                      if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
                           $InputNowosci = '<li><input type="checkbox" disabled="disabled" name="nowosc_'.$info['products_id'].'" id="nowosc_'.$info['products_id'].'" value="1" '.(($info['new_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="nowosc_'.$info['products_id'].'"> <span class="wylaczony">nowość</span> <em class="TipIkona"><b>Opcja nieaktywna - nowości określane na podstawie daty dodania</b></em></label></li>';
                      }
                      
                     $tekst .= '<ul class="opcje">
                                          ' . $InputNowosci . '
                                          <li><input type="checkbox" name="hit_'.$info['products_id'].'" id="hit_'.$info['products_id'].'" value="1" '.(($info['star_status'] == '1' || Funkcje::czyNiePuste($info['star_date']) || Funkcje::czyNiePuste($info['star_date_end'])) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="hit_'.$info['products_id'].'">nasz hit</label></li>
                                          <li><input type="checkbox" name="promocja_'.$info['products_id'].'" id="promocja_'.$info['products_id'].'" value="1" '.(($info['specials_status'] == '1' || Funkcje::czyNiePuste($info['specials_date']) || Funkcje::czyNiePuste($info['specials_date_end'])) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="promocja_'.$info['products_id'].'">promocja</label></li>
                                          <li><input type="checkbox" name="wyprzedaz_'.$info['products_id'].'" id="wyprzedaz_'.$info['products_id'].'" value="1" '.(($info['sale_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="wyprzedaz_'.$info['products_id'].'">wyprzedaż</label></li>
                                          <li><input type="checkbox" name="polecany_'.$info['products_id'].'" id="polecany_'.$info['products_id'].'" value="1" '.(($info['featured_status'] == '1' || Funkcje::czyNiePuste($info['featured_date']) || Funkcje::czyNiePuste($info['featured_date_end'])) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="polecany_'.$info['products_id'].'">polecany</label></li>
                                          <li><input type="checkbox" name="export_'.$info['products_id'].'" id="export_'.$info['products_id'].'" value="1" '.(($info['export_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="export_'.$info['products_id'].'">do porównywarek </label></li>
                                          <li><input type="checkbox" name="negocjacja_'.$info['products_id'].'" id="negocjacja_'.$info['products_id'].'" value="1" '.(($info['products_make_an_offer'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="negocjacja_'.$info['products_id'].'"><span style="color:#ff0000">negocjacja ceny</span></li>
                                          <li class="OpcjeDarmowaWysylka' . (($info['free_shipping_excluded'] == 1) ? ' DarmowaUkryj' : '') . '"><input type="checkbox" name="wysylka_'.$info['products_id'].'" id="wysylka_'.$info['products_id'].'" value="1" '.(($info['free_shipping_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="wysylka_'.$info['products_id'].'"><span>darmowa wysyłka</span></label></li>' .
                                          (($info['free_shipping_excluded'] == 1) ? '<em class="TipChmurka WykluczonaWysylka"><b>Ten produkt jest wykluczony z darmowej wysyłki</b><img src="obrazki/uwaga.png" alt="Wykluczenie" /></em>' : '') . '
                                          <li><input type="checkbox" name="listing_'.$info['products_id'].'" id="listing_'.$info['products_id'].'" value="1" '.(($info['listing_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="listing_'.$info['products_id'].'">nie wyświetlaj w listingach</label></li> 
                                          <li><input type="hidden" name="kupowanie_'.$info['products_id'].'" value="1"  /></li>';
                      
                      $TablicaOpcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_1),
                                            array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_2),
                                            array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_3),
                                            array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_4),
                                            array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_5));
                      
                      foreach ( $TablicaOpcje as $Tmp ) {
                          //
                          if ( $Tmp['aktywne'] == 'tak' ) {
                               //
                               $NazwaIkonki = @unserialize($Tmp['nazwa']);
                               if ( is_array($NazwaIkonki) ) {
                                    if ( isset($NazwaIkonki[$_SESSION['domyslny_jezyk']['id']]) ) {                          
                                         $tekst .= '<li><input type="checkbox" name="ikona_' . $Tmp['nr'] . '_' . $info['products_id'] . '" id="ikona_' . $Tmp['nr'] . '_' . $info['products_id'] . '" value="1" '. (($info['icon_' . $Tmp['nr'] . '_status'] == '1') ? 'checked="checked"' : '') . ' /> <label class="OpisFor" for="ikona_' . $Tmp['nr'] . '_' . $info['products_id'] . '">' . $NazwaIkonki[$_SESSION['domyslny_jezyk']['id']] . '</label></li>';
                                    }
                               }
                                unset($NazwaIkonki);
                               //
                          }    
                          //
                      }              
                      $tekst .= '</ul>';                                      
                                          
                      unset($InputNowosci, $TablicaOpcje);

                  $tekst .= '</td></tr>';

                  unset($tablica, $linkSeo);
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
          $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php?typ=zestawy', 50, 350 );
          
          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: false
          });     
          
          $('#pamietaj').click(function() {
             if ($(this).prop('checked') == true) {
                 createCookie("kategoria","tak");                 
               } else {
                 createCookie("kategoria","",-1);
             }
          });            
        });
        </script>     

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Zestawy produktów</div>
            
            <div id="wyszukaj">
                <form action="produkty/zestawy_produktow.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
                </div>  

                <div class="wyszukaj_select">
                    <span>Opcja:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'nowosc', 'text' => 'Nowość');
                    $tablica[] = array('id' => 'hit', 'text' => 'Nasz hit');
                    $tablica[] = array('id' => 'promocja', 'text' => 'Promocja');
                    $tablica[] = array('id' => 'wyprzedaz', 'text' => 'Wyprzedaż');
                    $tablica[] = array('id' => 'polecany', 'text' => 'Polecany');
                    $tablica[] = array('id' => 'export', 'text' => 'Do porównywarek');
                    $tablica[] = array('id' => 'negoc', 'text' => 'Negocjacja ceny');
                    $tablica[] = array('id' => 'wysylka_gratis', 'text' => 'Darmowa wysyłka');
                    $tablica[] = array('id' => 'wykluczona_darmowa_wysylka', 'text' => 'Wykluczona darmowa wysyłka');
                    
                    $tab_opcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_1),
                                       array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_2),
                                       array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_3),
                                       array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_4),
                                       array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_5));
                    
                    foreach ( $tab_opcje as $tmp ) {
                        //
                        if ( $tmp['aktywne'] == 'tak' ) {
                             //
                             $nazwa = @unserialize($tmp['nazwa']);
                             if ( is_array($nazwa) ) {
                                  if ( isset($nazwa[$_SESSION['domyslny_jezyk']['id']]) ) {                          
                                       $tablica[] = array('id' => 'ikona_' . $tmp['nr'], 'text' => $nazwa[$_SESSION['domyslny_jezyk']['id']]);
                                  }
                             }
                              unset($nazwa);
                             //
                        }    
                        //
                    }                                  
                    unset($tab_opcje, $tmp);                    
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('opcja', $tablica, ((isset($_GET['opcja'])) ? $filtr->process($_GET['opcja']) : '')); ?>
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
                    unset($tablica);
                    ?>
                </div>                 

                <div class="wyszukaj_select">
                    <span>ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['nrkat'])) : ''); ?>" size="20" />
                </div>                 

                <?php  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolny --');
                $tablica[] = array('id' => 'tak', 'text' => 'aktywne');
                $tablica[] = array('id' => 'tak_listing', 'text' => 'aktywne - wyświetlane w listingach');
                $tablica[] = array('id' => 'tak_nie_listing', 'text' => 'aktywne - nie wyświetlane w listingach');
                $tablica[] = array('id' => 'nie', 'text' => 'nieaktywne');
                //               
                ?>
                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('status', $tablica, ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : ''), ' style="max-width:180px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>     

                <div class="wyszukaj_select">
                    <span>Data dodania:</span>
                    <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="8" class="datepicker" />
                </div>   

                <div class="wyszukaj_select">
                    <span>Data dostępności:</span>
                    <input type="text" id="data_dostepnosci_od" name="szukaj_data_dostepnosci_od" value="<?php echo ((isset($_GET['szukaj_data_dostepnosci_od'])) ? $filtr->process($_GET['szukaj_data_dostepnosci_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dostepnosci_do" name="szukaj_data_dostepnosci_do" value="<?php echo ((isset($_GET['szukaj_data_dostepnosci_do'])) ? $filtr->process($_GET['szukaj_data_dostepnosci_do']) : ''); ?>" size="8" class="datepicker" />
                </div> 

                <div class="wyszukaj_select">
                    <span>Stan dostępności:</span>                                         
                    <?php 
                    echo Funkcje::RozwijaneMenu('dostep', Produkty::TablicaDostepnosci('-- brak --'), ((isset($_GET['dostep'])) ? $filtr->process($_GET['dostep']) : '')); 
                    ?>
                </div>

                <div class="wyszukaj_select">
                    <span>Dodatkowe opcje:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'pola', 'text' => 'zestawy z dodatkowymi polami');
                    $tablica[] = array('id' => 'bez_pola', 'text' => 'zestawy bez dodatkowych pól');                    
                    $tablica[] = array('id' => 'bez_magazynu', 'text' => 'zestawy z wyłączoną obsługą magazynu');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('dodatkowe_opcje', $tablica, ((isset($_GET['dodatkowe_opcje'])) ? $filtr->process($_GET['dodatkowe_opcje']) : ''), ' style="max-width:200px"'); ?>                    
                </div>     

                <div class="wyszukaj_select">
                    <span>Z dodatkowym polem opisowym:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolna nazwa pola --');                    
                    //
                    $sql = $db->open_query("select products_extra_fields_name, products_extra_fields_id from products_extra_fields order by products_extra_fields_name");                        
                    while ($info = $sql->fetch_assoc()) {
                        $tablica[] = array('id' => $info['products_extra_fields_id'], 'text' => $info['products_extra_fields_name']);                    
                    }                    
                    $db->close_query($sql);
                    unset($info);
                    //
                    echo Funkcje::RozwijaneMenu('dod_pole_nazwa', $tablica, ((isset($_GET['dod_pole_nazwa'])) ? $filtr->process($_GET['dod_pole_nazwa']) : ''), ' style="max-width:230px"');
                    //
                    ?>
                </div>                     

                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
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
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="produkty/zestawy_produktow.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
                
            </div>        
            
            <form action="produkty/produkty_akcja.php?zestaw" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a1">brak</a>
                <a id="sort_a17" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a17">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a7" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a7">nr katalogowy rosnąco</a>
                <a id="sort_a8" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a8">nr katalogowy malejąco</a> 
                <a id="sort_a9" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a9">cena rosnąco</a>
                <a id="sort_a10" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a10">cena malejąco</a>             
                <a id="sort_a3" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a4">nieaktywne</a>
                <a id="sort_a5" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a5">daty dodania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a6">daty dodania malejąco</a> 
                <a id="sort_a11" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a11">ID malejąco</a>
                <a id="sort_a12" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a12">ID rosnąco</a>
                <a id="sort_a13" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a13">sortowanie malejąco</a>
                <a id="sort_a14" class="sortowanie" href="produkty/zestawy_produktow.php?sort=sort_a14">sortowanie rosnąco</a>
                
            </div>        
            
            <div style="clear:both;"></div>               
            
            <?php 
            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                $cSciezka = explode("_", (string)$sciezka);
               } else {
                $cSciezka = array();
            }
            ?>

            <?php
            // przycisk dodania nowego zestawu
            ?>
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="produkty/produkty_dodaj.php?zestaw">dodaj nowy zestaw</a>                    
                </div>         
                <?php if (isset($_GET['kategoria_id'])) { ?>
                <div>
                    <input type="checkbox" id="pamietaj" value="<?php echo (int)$_GET['kategoria_id']; ?>" <?php echo ((isset($_COOKIE['kategoria'])) ? 'checked="checked"' : ''); ?>/><label class="OpisFor" for="pamietaj" style="margin-top:-3px;">zaznaczaj automatycznie wybraną kategorię przy dodawaniu nowego zestawu</label>
                </div>
                <?php } ?>
            </div>
            
            <div style="clear:both;"></div>
            
            <div class="GlownyListing">

                <div class="GlownyListingKategorie">
                
                    <div class="OknoKategoriiKontener">
                    
                        <div class="OknoNaglowek"><span class="RozwinKategorie">Kategorie</span></div>
                        <?php
                        echo '<div class="OknoKategorii"><table class="pkc">';
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
                                    <td class="lfp"><a href="produkty/zestawy_produktow.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'zestawy_produktow\',\'produkty\')" />' : '').'</td>
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
                                podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','zestawy_produktow','produkty');
                                </script>
                                
                            <?php
                            unset($sciezka,$cSciezka);
                            }
                        } ?>
                        
                    </div>
                    
                </div>
                
                <script>
                $(document).ready(function() {
                  
                    $('#akcja_dolna').change(function() {

                       $('#potwierdzenie_usuniecia').hide();

                       if ( this.value == '3' || this.value == '4' ) {
                            $('#usuniecie_zamowien_nie').prop('checked', true);
                            $('#usuniecie_zamowien_tak').prop('checked', false);
                            $('#potwierdzenie_usuniecia').show();
                       }
                       
                    });

                });
                </script>

                <div style="GlownyListingProdukty">
                
                    <div id="wynik_zapytania" class="WynikZapytania"></div>
                    <div id="aktualna_pozycja">1</div>
                    
                    <div id="akcja" class="AkcjaOdstep">
                    
                        <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                        
                        <div class="lf" style="padding-right:20px">
                            <span onclick="akcja(1)">zaznacz wszystkie</span>
                            <span onclick="akcja(2)">odznacz wszystkie</span>
                        </div>
                        
                        <div id="akc">
                        
                            Wykonaj akcje: 
                            
                            <select name="akcja_dolna" id="akcja_dolna">
                                <option value="0"></option>
                                <option value="3">usuń zaznaczone zestawy</option>
                            </select>
                            
                        </div>
                        
                        <div style="clear:both;"></div>
                        
                    </div>                        
                    
                    <div id="dolny_pasek_stron" class="AkcjaOdstep"></div>
                    <div id="pokaz_ile_pozycji" class="AkcjaOdstep"></div>
                    <div id="ile_rekordow" class="AkcjaOdstep"><?php echo $ile_pozycji; ?></div>
                    
                    <?php if ($ile_pozycji > 0) { ?>
                    
                    <div id="potwierdzenie_usuniecia" style="padding-bottom:10px;display:none">
                    
                        <div class="RamkaAkcji" style="display:block;padding:15px">
                    
                            <div class="rg">
                              
                                <p style="padding-right:0px">
                                    <label style="width:auto">Czy na pewno chcesz usunąć wybrane zestawy ?</label>
                                    <input type="radio" value="0" name="usuniecie_produktow" id="usuniecie_produktow_nie" checked="checked" /><label class="OpisFor" for="usuniecie_produktow_nie">nie</label>
                                    <input type="radio" value="1" name="usuniecie_produktow" id="usuniecie_produktow_tak" /><label class="OpisFor" style="padding-right:0px !important" for="usuniecie_produktow_tak">tak</label>
                                </p>
                                
                            </div>
                            
                            <div class="cl"></div>
                            
                            <div class="ostrzezenie rg">Operacja usunięcia jest nieodracalna ! Zestawów po usunięciu nie będzie można przywrócić !</div>
                            
                            <div class="cl"></div>
                       
                        </div>
                        
                    </div>
                
                    <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                    <?php } ?>                        
                    
                </div>

            </div>
            
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('produkty/zestawy_produktow.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id', ILOSC_WYNIKOW_NA_STRONIE, ADMIN_DOMYSLNE_SORTOWANIE); ?>
            </script>                     
                
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
