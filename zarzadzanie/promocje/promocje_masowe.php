<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['napraw']) ) {
    //
    $_POST['akcja'] = 'zmiana_cen';
    $_POST['cena_1'] = 'x*1';
    for ($x = 2; $x <= ILOSC_CEN; $x++) {
         $_POST['cena_' . $x] = 'x*1';
    }
    //
}

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_promocji']) && ($_POST['akcja_promocji'] == 'dodaj' || $_POST['akcja_promocji'] == 'usun')) {
    
        // pobieranie informacji o vat
        $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
        $sqls = $db->open_query($zapytanie_vat);
        //
        $tablicaVat = array();
        while ($infs = $sqls->fetch_assoc()) { 
            $tablicaVat[$infs['tax_rates_id']] = $infs['tax_rate'];
        }
        $db->close_query($sqls);
        unset($zapytanie_vat, $infs);  
        //             

        //
        $DodatkoweCeny = '';
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            //
            $DodatkoweCeny .= 'p.products_price_'.$x.', p.products_price_tax_'.$x.', p.products_tax_'.$x.', p.products_old_price_'.$x . ',';
            //
        }        
        //
        if (isset($_POST['id_kat']) && count($_POST['id_kat']) > 0) {
            //
            $zapytanie = "select distinct p.products_id, 
                                          p.products_price, 
                                          p.products_price_tax, 
                                          p.products_tax, 
                                          p.products_old_price, 
                                          p.specials_status,
                                          p.products_tax_class_id,
                                          p.options_type,
                                          " . $DodatkoweCeny . "
                                          pc.products_id,
                                          pc.categories_id,
                                          pd.products_name
                                     from products p
                                left join products_to_categories pc ON pc.products_id = p.products_id
                                left join products_description pd ON pd.products_id = p.products_id
                                      and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ";

            $zapytanie .= " where pc.categories_id in (";
            //
            $tablica_kat = array();
            $tablica_kat = $_POST['id_kat'];
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                $zapytanie .= $tablica_kat[$q] . ',';
                //       
            } 
            unset($tablica_kat);
            //
            $zapytanie = substr((string)$zapytanie,0,-1) . ')';
            //
        } else {
            //
            $zapytanie = "select distinct p.products_id, 
                                          p.products_price, 
                                          p.products_price_tax, 
                                          p.products_tax, 
                                          p.products_old_price, 
                                          p.specials_status,
                                          p.products_tax_class_id,
                                          p.options_type,
                                          " . $DodatkoweCeny . "
                                          pd.products_name
                                     from products p, products_description pd 
                                    where p.products_id = pd.products_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";       
            //
        }

        // jezeli jest wybrany producent
        if (isset($_POST['id_producent']) && count($_POST['id_producent']) > 0) {
            //
            $zapytanie .= " and p.manufacturers_id in (";
            //
            $tablica_producent = $_POST['id_producent'];
            for ($q = 0, $c = count($tablica_producent); $q < $c; $q++) {
                //
                $zapytanie .= $tablica_producent[$q] . ',';
                //       
            } 
            unset($tablica_producent);
            //
            $zapytanie = substr((string)$zapytanie,0,-1) . ')';
            //
        }
        
        // jezeli jest promocja
        if (isset($_POST['akcja_promocji']) && $_POST['akcja_promocji'] == 'dodaj') {
            //
            if (isset($_POST['pomin_promocje']) && (int)$_POST['pomin_promocje'] == 1) {     
                $zapytanie .= " and p.specials_status = '0'";
            } 
            if (isset($_POST['pomin_wyprzedaz']) && (int)$_POST['pomin_wyprzedaz'] == 1) {     
                $zapytanie .= " and p.sale_status = '0'";
            }                
            //
        }
        
        // jezeli jest cena od
        if (isset($_POST['cena_od']) && (int)$_POST['cena_od'] > 0) {     
            $zapytanie .= " and p.products_price_tax >= " . (int)$_POST['cena_od'];
        }
        // jezeli jest cena do
        if (isset($_POST['cena_do']) && (int)$_POST['cena_do'] > 0) {     
            $zapytanie .= " and p.products_price_tax <= " . (int)$_POST['cena_do'];
        }        
        
        if (isset($_POST['nazwa']) && !empty($_POST['nazwa'])) {
            $szukana_wartosc = $filtr->process($_POST['nazwa']);
            $zapytanie .= " and pd.products_name like '%".$szukana_wartosc."%'";
            unset($szukana_wartosc);
        }
        
        // jezeli jest status
        if (isset($_POST['status']) && (int)$_POST['status'] == 1) {     
            $zapytanie .= " and p.products_status = '1'";
        }    
        // jezeli jest status
        if (isset($_POST['status_wylaczone']) && (int)$_POST['status_wylaczone'] == 1) {     
            $zapytanie .= " and p.products_status = '0'";
        } 
        // jezeli tylko ze stanem magazynowym wiekszym o 0
        if (isset($_POST['status_magazyn']) && (int)$_POST['status_magazyn'] == 1) {     
            $zapytanie .= " and p.products_quantity > 0";
        }         
        // jezeli tylko ze stanem magazynowym wiekszym od - do
        if (isset($_POST['status_magazyn_od_do']) && (int)$_POST['status_magazyn_od_do'] == 1) { 
            if ( $_POST['ilosc_magazyn_od'] != '' ) {
                 $zapytanie .= " and p.products_quantity >= " . (float)$_POST['ilosc_magazyn_od'];
            }
            if ( $_POST['ilosc_magazyn_do'] != '' ) {
                 $zapytanie .= " and p.products_quantity <= " . (float)$_POST['ilosc_magazyn_do'];
            }            
        }  
        
        // jezeli jest vat
        if (isset($_POST['vat']) && $_POST['vat'] != 'x') {     
            $zapytanie .= " and p.products_tax_class_id = " . (int)$_POST['vat'];
        }        
        // jezeli jest waluta
        if (isset($_POST['waluta']) && $_POST['waluta'] != 'x') {     
            $zapytanie .= " and p.products_currencies_id  = " . (int)$_POST['waluta'];
        }  
        
        // jezeli jest usuwanie promocji
        if (isset($_POST['akcja_promocji']) && $_POST['akcja_promocji'] == 'usun') {
          
            $zapytanie .= " and p.specials_status = '1'";

            // jezeli jest usuwanie promocji i tylko dla nieaktywnych
            if (isset($_POST['status_promocje_nieaktywne']) && $_POST['status_promocje_nieaktywne'] == '1') {
                //
                // wyszuka produkty z nieaktywnymi promocjami
                $do_usuniecia = array(0);
                $sql_promocje = $db->open_query("select distinct products_id, specials_date, specials_date_end from products where specials_status = '1'");   
                while ($info_promocje = $sql_promocje->fetch_assoc()) {
                    //
                    if ( ((FunkcjeWlasnePHP::my_strtotime($info_promocje['specials_date']) > time() && $info_promocje['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info_promocje['specials_date_end']) < time() && $info_promocje['specials_date_end'] != '0000-00-00 00:00:00') ) ) {                             
                          $do_usuniecia[] = $info_promocje['products_id'];
                    }                 
                    //
                }
                $db->close_query($sql_promocje);
                unset($info_promocje);                
                    
                if ( count($do_usuniecia) > 0 ) {
                     //
                     $zapytanie .= " and p.products_id in (" . implode(',', (array)$do_usuniecia) . ")";
                     //
                }
                //
                unset($do_usuniecia);
                //
            }

        }
        
        // grupowanie produktow
        $zapytanie .= ' group by p.products_id ';

        // wykonanie zapytania
        $sql = $db->open_query($zapytanie);
        $Przetworzono = 0;
        //
        $BylaAktualizacja = false;
        //
        
        $TablicaProduktow = array();

        while ($info = $sql->fetch_assoc()) {
            //
            $pola = array();
            //
            // tablica id produktow do aktualizacji products_stock
            if ( $info['options_type'] == 'ceny' ) {
                 //
                 $TablicaProduktow[] = array('id' => $info['products_id'], 'vat' => $info['products_tax_class_id']);
                 //
            }
            //
            // jezeli dodawanie promocji
            if ( $_POST['akcja_promocji'] == 'dodaj' ) {
                //
                $iloscCen = 1;
                if (isset($_POST['ilosc_cen']) && (int)$_POST['ilosc_cen'] == 1) {   
                    $iloscCen = ILOSC_CEN;
                }
                //
                for ($x = 1; $x <= $iloscCen; $x++) {
                    //
                    $bruttoPoprzednia = 0;
                    $cenaBruttoBaza = $info['products_price_tax' . (($x == 1) ? '' : '_'.$x)];
                    //
                    //
                    // jezeli obnizona zostaje cena glowna i przeniesione do poprzedniej
                    if ( $_POST['cena_promocji'] == 'cena_glowna' ) {
                    
                        //
                        $wielkoscObnizki = (float)$_POST['rabat_ceny_glownej'];
                        $brutto = 0;
                        $netto = 0;
                        $podatek = 0;
                        //
                        if ( $wielkoscObnizki > 0 && $cenaBruttoBaza > 0 ) {
                             //
                             switch ($_POST['rabat_ceny_glownej_rodzaj']) {
                                case 'liczba':
                                    //
                                    $brutto = $cenaBruttoBaza - $wielkoscObnizki;
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $brutto = ceil($brutto);
                                    }                            
                                    //
                                    $netto = round(($brutto / (1 + ($tablicaVat[$info['products_tax_class_id']]/100))), 2);
                                    $podatek = $brutto - $netto;
                                    //
                                    break;
                                case 'procent':
                                    //
                                    $brutto = $cenaBruttoBaza - ( $cenaBruttoBaza * ($wielkoscObnizki / 100) );
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $brutto = ceil($brutto);
                                    }                            
                                    //
                                    $netto = round(($brutto / (1 + ($tablicaVat[$info['products_tax_class_id']]/100))), 2);
                                    $podatek = $brutto - $netto;
                                    //
                                    break;
                             }
                             //
                        }
                        //
                        if ( $brutto > 0 && $netto > 0 ) {
                            //
                            $pola[] = array('products_price_tax' . (($x == 1) ? '' : '_'.$x), (float)$brutto);
                            $pola[] = array('products_price' . (($x == 1) ? '' : '_'.$x), (float)$netto);
                            $pola[] = array('products_tax' . (($x == 1) ? '' : '_'.$x), (float)$podatek);     
                            //
                            $pola[] = array('specials_status','1'); 
                            $pola[] = array('products_old_price' . (($x == 1) ? '' : '_'.$x), (float)$cenaBruttoBaza);
                            //
                            if ( $x == 1 ) {
                                 $BylaAktualizacja = true;
                            }
                            //
                        }
                        //
                        unset($brutto, $netto, $podatek, $wielkoscObnizki);
                        //
                    }
                    
                    // jezeli obnizona zostaje cena glowna i przeniesione do poprzedniej
                    if ( $_POST['cena_promocji'] == 'cena_poprzednia' ) {
                    
                        //
                        $wielkoscPodwyzki = (float)$_POST['narzut_ceny_poprzedniej'];
                        //
                        if ( $wielkoscPodwyzki > 0 && $cenaBruttoBaza > 0 ) {
                             //
                             switch ($_POST['narzut_ceny_poprzedniej_rodzaj']) {
                                case 'liczba':
                                    //
                                    $bruttoPoprzednia = $cenaBruttoBaza + $wielkoscPodwyzki;
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $bruttoPoprzednia = ceil($bruttoPoprzednia);
                                    }                            
                                    //
                                    break;
                                case 'procent':
                                    //
                                    $bruttoPoprzednia = $cenaBruttoBaza + ( $cenaBruttoBaza * ($wielkoscPodwyzki / 100) );
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $bruttoPoprzednia = ceil($bruttoPoprzednia);
                                    }                            
                                    //
                                    break;
                             }
                             //
                        }
                        //
                        if ( $bruttoPoprzednia > 0 && $bruttoPoprzednia > $cenaBruttoBaza ) {    
                            //
                            $pola[] = array('specials_status', '1'); 
                            $pola[] = array('products_old_price' . (($x == 1) ? '' : '_'.$x), (float)$bruttoPoprzednia);
                            //
                            if ( $x == 1 ) {
                                 $BylaAktualizacja = true;
                            }
                            //
                        }
                        //
                        unset($bruttoPoprzednia, $wielkoscPodwyzki);
                        //
                    }  
                    
                }
                //
                if ( count($pola) > 0 ) {
                    //
                    // daty rozpoczecia i zakonczenia
                    if (!empty($_POST['data_promocja_od'])) {
                        $pola[] = array('specials_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_od']))));
                      } else {
                        $pola[] = array('specials_date','0000-00-00');            
                    }
                    if (!empty($_POST['data_promocja_do'])) {
                        $pola[] = array('specials_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_do']))));
                      } else {
                        $pola[] = array('specials_date_end','0000-00-00');            
                    } 
                    //
                }
                //
            }
            
            // jezeli usuwanie promocji
            if ( $_POST['akcja_promocji'] == 'usun' ) {
                //
                // jezeli bez zmian cen tylko usuniecie promocji
                if ( $_POST['usuwanie_tryb'] == '0' ) { 
                    //
                    $pola[] = array('specials_status','0'); 
                    $pola[] = array('products_old_price','0');
                    $pola[] = array('specials_date','0000-00-00 00:00:00');
                    $pola[] = array('specials_date_end','0000-00-00 00:00:00');
                    //
                    for ($x = 2; $x <= ILOSC_CEN; $x++) {
                        //
                        $pola[] = array('products_old_price_'.$x,'');
                        //
                    }
                    //
                    // jezeli przywrocenie ceny z poprzedniej
                  } else {
                    //            
                    Funkcje::AktualizujHistorieCenProduktowPromocji($info['products_id']);
                    //
                    $pola = array(array('specials_status','0'),
                                  array('products_old_price','0'),
                                  array('specials_date','0000-00-00 00:00:00'),
                                  array('specials_date_end','0000-00-00 00:00:00'));            
                    //                         
                    $wartosc = $info['products_old_price'];
                    $netto = round(($wartosc / (1 + ($tablicaVat[$info['products_tax_class_id']]/100))), 2);
                    $podatek = $wartosc - $netto;
                    //
                    $pola[] = array('products_price_tax',(float)$wartosc);
                    $pola[] = array('products_price',(float)$netto);
                    $pola[] = array('products_tax',(float)$podatek);  
                    //
                    unset($wartosc, $netto, $podatek);
                    //
                    // ceny dla pozostalych poziomow cen
                    for ($x = 2; $x <= ILOSC_CEN; $x++) {
                        //
                        // cena poprzednia
                        if ( $info['products_old_price_'.$x] > 0 ) {
                            //
                            $wartosc = $info['products_old_price_'.$x];
                            $netto = round(($wartosc / (1 + ($tablicaVat[$info['products_tax_class_id']]/100))), 2);
                            $podatek = $wartosc - $netto;    
                            //
                            $pola[] = array('products_old_price_'.$x,'0');
                            $pola[] = array('products_price_tax_'.$x,(float)$wartosc);
                            $pola[] = array('products_price_'.$x,(float)$netto);
                            $pola[] = array('products_tax_'.$x,(float)$podatek);
                            //    
                            unset($wartosc, $netto, $podatek); 
                            //                
                        }
                        //
                    }             
                    //                     
                }
                //
                $BylaAktualizacja = true;                
                //
            }
            //
            if (count($pola) > 0) {
                $db->update_query('products' , $pola, 'products_id = ' . $info['products_id']);
                $Przetworzono++;
            }
            unset($pola);
            //            
          
        }
               
        // aktualizacja cen kombinacji cech - products_stock

        foreach ( $TablicaProduktow as $IdProduktu ) {
            //
            // jezeli dodawanie promocji
            if ( $_POST['akcja_promocji'] == 'dodaj' ) {
                //                            
                $iloscCen = 1;
                if (isset($_POST['ilosc_cen']) && (int)$_POST['ilosc_cen'] == 1) {   
                    $iloscCen = ILOSC_CEN;
                }
                //
                $zapytanie = "select distinct * from products_stock where products_id = '" . $IdProduktu['id'] . "'";
                $sql = $db->open_query($zapytanie);   
                //
                while ( $info = $sql->fetch_assoc() ) {
                    //
                    $pola = array();
                    //
                    for ($x = 1; $x <= $iloscCen; $x++) {
                        //
                        $cenaBruttoBaza = $info['products_stock_price_tax' . (($x == 1) ? '' : '_'.$x)];
                        //
                        if ( $cenaBruttoBaza > 0 ) {
                            //
                            // jezeli obnizona zostaje cena glowna i przeniesione do poprzedniej
                            if ( $_POST['cena_promocji'] == 'cena_glowna' ) {
                                //
                                $wielkoscObnizki = (float)$_POST['rabat_ceny_glownej'];
                                $brutto = 0;
                                $netto = 0;
                                $podatek = 0;
                                //
                                if ( $wielkoscObnizki > 0 && $cenaBruttoBaza > 0 ) {
                                     //
                                     switch ($_POST['rabat_ceny_glownej_rodzaj']) {
                                        case 'liczba':
                                            //
                                            $brutto = $cenaBruttoBaza - $wielkoscObnizki;
                                            //
                                            // zaokraglanie do pelnych kwot
                                            if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                                $brutto = ceil($brutto);
                                            }                            
                                            //
                                            $netto = round(($brutto / (1 + ($tablicaVat[$IdProduktu['vat']]/100))), 2);
                                            $podatek = $brutto - $netto;
                                            //
                                            break;
                                        case 'procent':
                                            //
                                            $brutto = $cenaBruttoBaza - ( $cenaBruttoBaza * ($wielkoscObnizki / 100) );
                                            //
                                            // zaokraglanie do pelnych kwot
                                            if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                                $brutto = ceil($brutto);
                                            }                            
                                            //
                                            $netto = round(($brutto / (1 + ($tablicaVat[$IdProduktu['vat']]/100))), 2);
                                            $podatek = $brutto - $netto;
                                            //
                                            break;
                                     }
                                     //
                                }
                                //
                                if ( $brutto > 0 && $netto > 0 ) {
                                    //
                                    $pola[] = array('products_stock_price_tax' . (($x == 1) ? '' : '_'.$x), (float)$brutto);
                                    $pola[] = array('products_stock_price' . (($x == 1) ? '' : '_'.$x), (float)$netto);
                                    $pola[] = array('products_stock_tax' . (($x == 1) ? '' : '_'.$x), (float)$podatek);     
                                    //
                                    $pola[] = array('products_stock_old_price' . (($x == 1) ? '' : '_'.$x), (float)$cenaBruttoBaza);
                                    //
                                }
                                //
                                unset($brutto, $netto, $podatek, $wielkoscObnizki);
                                //
                            }
                        
                            // jezeli obnizona zostaje cena glowna i przeniesione do poprzedniej
                            if ( $_POST['cena_promocji'] == 'cena_poprzednia' ) {
                            
                                //
                                $wielkoscPodwyzki = (float)$_POST['narzut_ceny_poprzedniej'];
                                //
                                if ( $wielkoscPodwyzki > 0 && $cenaBruttoBaza > 0 ) {
                                     //
                                     switch ($_POST['narzut_ceny_poprzedniej_rodzaj']) {
                                        case 'liczba':
                                            //
                                            $bruttoPoprzednia = $cenaBruttoBaza + $wielkoscPodwyzki;
                                            //
                                            // zaokraglanie do pelnych kwot
                                            if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                                $bruttoPoprzednia = ceil($bruttoPoprzednia);
                                            }                            
                                            //
                                            break;
                                        case 'procent':
                                            //
                                            $bruttoPoprzednia = $cenaBruttoBaza + ( $cenaBruttoBaza * ($wielkoscPodwyzki / 100) );
                                            //
                                            // zaokraglanie do pelnych kwot
                                            if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                                $bruttoPoprzednia = ceil($bruttoPoprzednia);
                                            }                            
                                            //
                                            break;
                                     }
                                     //
                                }
                                //
                                if ( $bruttoPoprzednia > $cenaBruttoBaza ) {    
                                    //
                                    $pola[] = array('products_stock_old_price' . (($x == 1) ? '' : '_'.$x), (float)$bruttoPoprzednia);
                                    //
                                }
                                //
                                unset($bruttoPoprzednia, $wielkoscPodwyzki);
                                //
                            }  
                        
                        }
                        
                    }

                    if ( count($pola) > 0 ) {
                      
                         $sql_wynik = $db->update_query('products_stock' , $pola, " products_id = '" . $info['products_id'] . "' and products_stock_id = '" . $info['products_stock_id'] . "'");
                         
                    }
                    
                    unset($pola);

                }
                
                $db->close_query($sql);
                unset($info);                

            }
            
            // jezeli usuwanie promocji
            if ( $_POST['akcja_promocji'] == 'usun' ) {
                //
                //
                $zapytanie = "select distinct * from products_stock where products_id = '" . $IdProduktu['id'] . "'";
                $sql = $db->open_query($zapytanie);   
                //
                while ( $info = $sql->fetch_assoc() ) {
                  
                    $pola = array();

                    // jezeli bez zmian cen tylko usuniecie promocji
                    if ( $_POST['usuwanie_tryb'] == '0' ) { 
                        //
                        $pola[] = array('products_stock_old_price','');
                        //
                        for ($x = 2; $x <= ILOSC_CEN; $x++) {
                            //
                            $pola[] = array('products_stock_old_price_'.$x,'0');
                            //
                        }
                        //
                        // jezeli przywrocenie ceny z poprzedniej
                      } else {
                        //
                        $pola = array(array('products_stock_old_price','0'));       
                        //                         
                        if ( $info['products_stock_old_price'] > 0 ) {
                             //
                             $wartosc = $info['products_stock_old_price'];
                             $netto = round(($wartosc / (1 + ($tablicaVat[$IdProduktu['vat']]/100))), 2);
                             $podatek = $wartosc - $netto;
                             //
                             $pola[] = array('products_stock_price_tax',(float)$wartosc);
                             $pola[] = array('products_stock_price',(float)$netto);
                             $pola[] = array('products_stock_tax',(float)$podatek);  
                             //
                             unset($wartosc, $netto, $podatek);                  
                        }
                        //
                        // ceny dla pozostalych poziomow cen
                        for ($x = 2; $x <= ILOSC_CEN; $x++) {
                            //
                            // cena poprzednia
                            if ( $info['products_stock_old_price_'.$x] > 0 ) {
                                //
                                $wartosc = $info['products_stock_old_price_'.$x];
                                $netto = round(($wartosc / (1 + ($tablicaVat[$IdProduktu['vat']]/100))), 2);
                                $podatek = $wartosc - $netto;    
                                //
                                $pola[] = array('products_stock_old_price_'.$x,'0');
                                $pola[] = array('products_stock_price_tax_'.$x,(float)$wartosc);
                                $pola[] = array('products_stock_price_'.$x,(float)$netto);
                                $pola[] = array('products_stock_tax_'.$x,(float)$podatek);
                                //    
                                unset($wartosc, $netto, $podatek); 
                                //                
                            }
                            //
                        }             
                        //
                    }
                    
                    if ( count($pola) > 0 ) {
                      
                         $sql_wynik = $db->update_query('products_stock' , $pola, " products_id = '" . $info['products_id'] . "' and products_stock_id = '" . $info['products_stock_id'] . "'");
                         
                    }

                    unset($pola);
                    
                }
                
                $db->close_query($sql);
                unset($info);   
                
            }            

        }        

        if ($BylaAktualizacja == true) {
            //
            Funkcje::PrzekierowanieURL('promocje_masowe.php?suma=' . $Przetworzono);
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('promocje_masowe.php?suma=0');          
            //
        }
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php'); 
    ?>

    <div id="naglowek_cont">Masowe zarządzanie promocjami produktów</div>
    <div id="cont">
    
          <div class="poleForm">
            <div class="naglowek">Wybierz zakres tworzenia lub usuwania promocji</div>
            
                <?php
                if (isset($_GET['suma'])) {
                ?>
                
                <?php if ((int)$_GET['suma'] > 0) { ?>
                
                    <div id="SukcesAktualizacji">
                        Dane zostały przetworzone. <br />
                        Ilość zaktualizowanych produktów: <strong><?php echo (int)$_GET['suma']; ?></strong>
                    </div>
                    
                    <?php } else { ?>
                    
                    <div id="SukcesAktualizacji">
                        Brak danych przetworzenia ...
                    </div>

                <?php } ?>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('promocje','','promocje');">Powrót</button>    
                </div>                 
                
                <?php
                
                } else { 
                
                ?>
                
                <script>
                $(document).ready(function() {
                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y H:i',
                       inside: false,
                       readonly_element: true,
                       enabled_minutes: [00, 10, 20, 30, 40, 50]
                    });   
                });
                
                function promocje(wartosc) {
                    if (wartosc == 'usun') {
                        $('#PromocjeDodawanie').slideUp();
                        $('#Pominiecie').slideUp();
                        $('#StatusyPromocji').slideDown();
                        $('#PromocjeUsuwanie').slideDown();
                    }
                    if (wartosc == 'dodaj') {
                        $('#PromocjeDodawanie').slideDown();
                        $('#Pominiecie').slideDown();
                        $('#PromocjeUsuwanie').slideUp();
                        $('#StatusyPromocji').slideUp();
                    }  
                    if (wartosc == 'glowna') {
                        $('#ObnizenieCenyGlownej').slideDown();
                        $('#ObnizenieCenyPoprzedniej').slideUp();
                    }
                    if (wartosc == 'poprzednia') {
                        $('#ObnizenieCenyGlownej').slideUp();
                        $('#ObnizenieCenyPoprzedniej').slideDown();
                    }                    
                }
                </script> 
                
                <form action="promocje/promocje_masowe.php" method="post" id="zmiana_ceny" class="cmxform">                 
            
                <div class="TabelaMasowePromocje">
                
                    <div class="maleInfo" style="padding-left:25px; border:0px; margin-left:5px">Operacje na cenach będą przeprowadzone także na cenach przypisanych do kombinacji cech produktów (dla produktów które mają taką opcję)</div>

                    <div>
                        <input type="radio" onclick="promocje('dodaj')" value="dodaj" name="akcja_promocji" id="akcja_utworz" checked="checked" /><label class="OpisFor" for="akcja_utworz">utwórz promocje</label> <br />
                        <input type="radio" onclick="promocje('usun')" value="usun" name="akcja_promocji" id="akcja_usun" /><label class="OpisFor" for="akcja_usun">usuń promocje</label>
                    </div>
                    
                    <div id="PromocjeUsuwanie">
                        <input type="radio" name="usuwanie_tryb" id="usuwanie_tryb_1" value="0" checked="checked" /><label class="OpisFor" for="usuwanie_tryb_1">usuń promocje bez zmian cen produktów (zostanie usunięty atrybut promocji oraz cena poprzednia)</label> <br />   
                        <input type="radio" name="usuwanie_tryb" id="usuwanie_tryb_2" value="1" /> <label class="OpisFor" for="usuwanie_tryb_2">usuń promocje i zmień cenę na poprzednią (zostanie usunięty atrybut promocji i w jako cena produktu przypisana cena poprzednia)</label>
                    </div>
                    
                    <div id="PromocjeDodawanie">
                    
                        <div>
                            <input type="radio" onclick="promocje('glowna')" value="cena_glowna" name="cena_promocji" id="cena_promocji_1" checked="checked" /><label class="OpisFor" for="cena_promocji_1">z obniżeniem ceny produktów (aktualna cena produktu zostanie przeniesienia do <b>ceny poprzedniej</b> i cena produktu zostanie obniżona o ustaloną wartość)</label><br />
                            <input type="radio" onclick="promocje('poprzednia')" value="cena_poprzednia" name="cena_promocji" id="cena_promocji_2" /><label class="OpisFor" for="cena_promocji_2">bez obniżania ceny produktów (zostanie tylko dodana <b>cena poprzednia</b> produktu o ustalonej wartości)</label>                           
                        </div> 

                        <div>
                            <p id="ObnizenieCenyGlownej">
                                <span>obniż cenę produktów o &nbsp; <input type="text" value="" size="5" name="rabat_ceny_glownej" /></span>
                                <span><input type="radio" value="liczba" name="rabat_ceny_glownej_rodzaj" id="rabat_1" checked="checked" /> <label class="OpisFor" for="rabat_1">stała liczba</label></span>
                                <span><input type="radio" value="procent" name="rabat_ceny_glownej_rodzaj" id="rabat_2" /> <label class="OpisFor" for="rabat_2">wartość procentowa</label></span>                
                            </p>
                            
                            <p id="ObnizenieCenyPoprzedniej" style="display:none">
                                <span>dodaj do ceny poprzedniej &nbsp; <input type="text" value="" size="5" name="narzut_ceny_poprzedniej" /></span>
                                <span><input type="radio" style="border:0px" value="liczba" name="narzut_ceny_poprzedniej_rodzaj" id="narzut_1" checked="checked" /><label class="OpisFor" for="narzut_1">stała liczba</label></span>
                                <span><input type="radio" style="border:0px" value="procent" name="narzut_ceny_poprzedniej_rodzaj" id="narzut_2" /><label class="OpisFor" for="narzut_2">wartość procentowa</label></span>
                            </p>
                            
                            <input type="checkbox" name="pelne" id="pelne" value="1" /> <label class="OpisFor" for="pelne">zaokrąglij nowe ceny brutto do pełnych kwot</label> <br />   

                            <input type="checkbox" name="ilosc_cen" id="ilosc_cen" value="1" /> <label class="OpisFor" for="ilosc_cen">jeżeli produkt ma kilka poziomów cen ustaw cenę promocyjną dla wszystkich poziomów cen (przy wyłączonej opcji przeliczona będzie tylko cena nr 1)</label>
                        </div> 

                        <div>
                            <label for="data_promocja_od">data rozpoczęcia</label><input type="text" id="data_promocja_od" name="data_promocja_od" value="" size="20"  class="datepicker" />
                        </div>    

                        <div>
                            <label for="data_promocja_do">data zakończenia</label><input type="text" id="data_promocja_do" name="data_promocja_do" value="" size="20" class="datepicker" />
                        </div>

                    </div>

                </div>
                
                <div class="naglowek" style="margin:10px;">Dodatkowe parametry dla tworzonych <b>promocji</b></div>
                
                <div class="DodatkoweWarunkiKontener">
                
                    <div id="DodatkoweWarunki">
                        
                        <div class="PromocjeMasoweEdycja">
                            
                            <span class="maleInfo">Promocje tylko dla wybranych kategorii</span>

                            <div id="drzewo" class="OknoKategorie">
                                <?php
                                //
                                echo '<table class="pkc">';
                                //
                                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                    $podkategorie = false;
                                    if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                    //
                                    echo '<tr>
                                            <td class="lfp">
                                                <input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="kat_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].'</label>
                                            </td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                          </tr>
                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                }
                                echo '</table>';
                                unset($tablica_kat,$podkategorie);                     
                                ?> 
                            </div>  
                                
                        </div>
                       
                        <div class="PromocjeMasoweEdycja">
                                
                            <span class="maleInfo">Promocje tylko dla wybranych producentów</span>
                        
                            <div id="producent" class="OknoProducenci">
                        
                            <?php
                            $Prd = Funkcje::TablicaProducenci();
                            //
                            if (count($Prd) > 0) {
                                //
                                echo '<table class="pkc">';
                                //
                                for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                    echo '<tr>                                
                                            <td class="lfp">
                                                <input type="checkbox" value="'.$Prd[$b]['id'].'" name="id_producent[]" id="id_producent_'.$Prd[$b]['id'].'" /><label class="OpisFor" for="id_producent_'.$Prd[$b]['id'].'">'.$Prd[$b]['text'].'</label>
                                            </td>                                
                                          </tr>';
                                }
                                echo '</table>';
                                //
                            }
                            unset($Prd);
                            ?> 
                            
                            </div>
                                
                        </div> 

                        <div class="PromocjeMasoweEdycja">
                                
                            <span class="maleInfo">Dodatkowe parametry</span>
                        
                            <div id="inne" class="OknoInne">
                            
                                <ul>
                                    <li><input type="checkbox" value="1" name="status" id="status_tak" /><label class="OpisFor" for="status_tak"><b>tylko aktywne produkty</b></label></li>
                                </ul>
                                
                                <ul>
                                    <li><input type="checkbox" value="1" name="status_wylaczone" id="status_wylaczone" /><label class="OpisFor" for="status_wylaczone">tylko nieaktywne produkty</label></li>
                                </ul>
                                
                                <ul>
                                    <li><input type="checkbox" value="1" name="status_magazyn" id="status_magazyn" /><label class="OpisFor" for="status_magazyn">tylko ze stanem magazynowym większym od 0</label></li>
                                </ul>                                
                                                                
                                <ul>
                                    <li><input type="checkbox" value="1" name="status_magazyn_od_do" id="status_magazyn_od_do" /><label class="OpisFor" for="status_magazyn_od_do">tylko ze stanem magazynowym od </label><input type="text" size="4" class="kropkaPusta" value="" name="ilosc_magazyn_od" id="ilosc_magazyn_od" /> do <input type="text" size="4" class="kropkaPusta" value="" name="ilosc_magazyn_do" id="ilosc_magazyn_do" /></li>
                                </ul>  
                                
                                <div id="Pominiecie">
                                    <ul>
                                        <li><input type="checkbox" value="1" name="pomin_promocje" id="pomin_promocje" /> <label class="OpisFor" for="pomin_promocje"><span style="color:#ff0000">pomiń produkty które już są w promocji</span></label></li>
                                    </ul>
                                    <ul>
                                        <li><input type="checkbox" value="1" name="pomin_wyprzedaz" id="pomin_wyprzedaz" /> <label class="OpisFor" for="pomin_wyprzedaz">pomiń produkty które są w wyprzedaży</label></li>
                                    </ul>                                    
                                </div>
                                
                                <div id="StatusyPromocji" style="display:none">
                                    <ul>
                                        <li><input type="checkbox" value="1" name="status_promocje_nieaktywne" id="status_promocje_nieaktywne" /> <label class="OpisFor" for="status_promocje_nieaktywne"><span style="color:#1bbc00">usuń tylko z promocji <b>nieaktywnych</b></span></label></li>
                                    </ul>
                                </div>                                
                                
                                <br />

                                <label for="cena_od" class="BezDlugosci">Produkty z ceną brutto od:</label><input type="text" size="5" class="calkowita" name="cena_od" id="cena_od" /><label for="cena_do" class="BezDlugosci" style="margin-left:5px;">do:</label><input type="text" size="5" class="calkowita" name="cena_do" id="cena_do" />
                                
                                <br /><br />
                                
                                <table id="ciagZnakow">
                                    <tr>
                                        <td style="padding-right:3px">
                                            <label for="nazwa" class="BezDlugosci">Produkt ma mieć w nazwie ciąg znaków (tylko w języku polskim):</label> 
                                        </td>
                                        <td>
                                            <input type="text" size="25" name="nazwa" id="nazwa" />               
                                        </td>
                                    </tr>
                                </table>
                                
                                <br />
                                
                                <label for="vat" class="BezDlugosci">Produkty ze stawką VAT:</label>
                                
                                <?php
                                // pobieranie informacji o vat
                                $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
                                $sqls = $db->open_query($zapytanie_vat);
                                //
                                $tablica = array();
                                $tablica[] = array('id' => 'x', 'text' => '-');
                                //
                                while ($infs = $sqls->fetch_assoc()) { 
                                    $tablica[] = array('id' => $infs['tax_rates_id'], 'text' => $infs['tax_description']);
                                }
                                $db->close_query($sqls);
                                unset($zapytanie_vat, $infs);  
                                //             
                                echo Funkcje::RozwijaneMenu('vat', $tablica, 'x', 'id="vat"'); 
                                unset($tablica);
                                ?>
                                
                                <br /><br />
                                
                                <label for="waluta" class="BezDlugosci">Produkty z cenami w walucie:</label>
                                
                                <?php
                                $sqls = $db->open_query("select * from currencies");  
                                //
                                $tablica = array();
                                $tablica[] = array('id' => 'x', 'text' => '-');
                                //
                                while ($infs = $sqls->fetch_assoc()) { 
                                    $tablica[] = array('id' => $infs['currencies_id'], 'text' => $infs['title']);
                                }
                                $db->close_query($sqls);
                                unset($infs);  
                                //             
                                echo Funkcje::RozwijaneMenu('waluta', $tablica, 'x', 'id="waluta"'); 
                                unset($tablica);
                                ?> 
                                
                            </div>
                                
                        </div>
                        
                    </div>
                    
                </div>
                
                <div class="ostrzezenie" style="margin:5px 0px 5px 20px">
                    Zatwierdzenie aktualizacji danych spowoduje zmianę cen produktów. Operacji nie można cofnąć ! 
                    Zalecane jest wykonanie kopii bazy danych przed dokonaniem zmian.
                </div>
                    
                <div style="padding:10px">
                     <input type="submit" class="przyciskBut" value="Wykonaj operację" />
                     <button type="button" class="przyciskNon" onclick="cofnij('promocje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','promocje');">Powrót</button>   
                </div>                
                
                </form>
                
                <?php } ?>
            
          </div>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>