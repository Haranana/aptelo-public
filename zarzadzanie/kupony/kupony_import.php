<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if (isset($_FILES) && count($_FILES) > 0 && isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {
          
            $nazwa_plik = $_FILES['file']['tmp_name']; 
            $dane = file($nazwa_plik);
            
            //
            $separator = $filtr->process($_POST['sep']);
            //
            // kolejnosc w pliku
            $sort_kod_kuponu = $filtr->process($_POST['kod_sort']);
            $sort_opis = $filtr->process($_POST['opis_sort']);
            $sort_rodzaj = $filtr->process($_POST['rodzaj_sort']);
            $sort_znizka = $filtr->process($_POST['znizka_sort']);
            $sort_waznosc_od = $filtr->process($_POST['waznosc_od_sort']);
            $sort_waznosc_do = $filtr->process($_POST['waznosc_do_sort']);
            $sort_grupa_klientow = $filtr->process($_POST['grupa_klientow_sort']);
            $sort_moduly_platnosci = $filtr->process($_POST['moduly_platnosci_sort']);
            $sort_kraj = $filtr->process($_POST['kraj_sort']);
            $sort_ilosc_produktow = $filtr->process($_POST['ilosc_produktow_sort']);
            $sort_max_ilosc_produktow = $filtr->process($_POST['ilosc_produktow_max_sort']);
            $sort_wartosc_zamowienia = $filtr->process($_POST['wartosc_zamowienia_sort']);
            $sort_max_wartosc_zamowienia = $filtr->process($_POST['wartosc_zamowienia_max_sort']);
            $sort_ilosc_kuponow = $filtr->process($_POST['ilosc_kuponow_sort']);  
            $sort_promocje = $filtr->process($_POST['promocje_sort']);    
            $sort_uzycie_kuponu = $filtr->process($_POST['uzycie_kuponu_sort']);        
            $sort_warunki = $filtr->process($_POST['warunki_sort']);
            $sort_warunki_id = $filtr->process($_POST['warunki_id_sort']);
            $sort_widoczny = $filtr->process($_POST['widoczny_id_sort']);
            $sort_produkty_id = $filtr->process($_POST['produkty_id_sort']);
            $sort_waluta = $filtr->process($_POST['rodzaj_waluta_sort']);
            //
            for($i = 0, $c = count($dane); $i < $c; $i++) {                
                $linia = explode((string)$separator, (string)$dane[$i]);     
                //
                // jezeli jest kod kuponu
                if (isset($_POST['kod']) && $filtr->process($_POST['kod']) == '1' && (int)$sort_kod_kuponu > 0 && !empty($linia[(int)$sort_kod_kuponu-1])) {
                    //
                    // trzeba sprawdzic czy takiego kodu juz nie ma w bazie
                    $zapytanie = "select coupons_name from coupons where coupons_name = '" . $filtr->process($linia[(int)$sort_kod_kuponu-1]) . "'";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) == 0) {
                        //
                        $pola = array();
                        //
                        $pola[] = array('coupons_name',$filtr->process($linia[(int)$sort_kod_kuponu-1]));    
                        
                        // jezeli jest opis
                        if ( isset($linia[(int)$sort_opis-1]) ) {
                            if (isset($_POST['opis']) && $filtr->process($_POST['opis']) == '1' && (int)$sort_opis > 0) {
                                $pola[] = array('coupons_description',$filtr->process($linia[(int)$sort_opis-1]));     
                            }
                        }
                        
                        $typ = 'fixed';
                        
                        // jezeli jest typ
                        if ( isset($linia[(int)$sort_rodzaj-1]) ) {
                            if (isset($_POST['rodzaj']) && $filtr->process($_POST['rodzaj']) == '1' && (int)$sort_rodzaj > 0 && ($linia[(int)$sort_rodzaj-1] == 'procent' || $linia[(int)$sort_rodzaj-1] == 'kwota' || $linia[(int)$sort_rodzaj-1] == 'wysylka')) {
                                switch ($linia[(int)$sort_rodzaj-1]) {
                                    case "procent":
                                        $typ = 'percent';
                                        break;
                                    case "kwota":
                                        $typ = 'fixed';
                                        break;  
                                    case "wysylka":
                                        $typ = 'shipping';
                                        break;                                       
                                    default:
                                        $typ = 'fixed';                         
                                }                        
                                $pola[] = array('coupons_discount_type',$typ); 
                              } else {
                                $pola[] = array('coupons_discount_type','fixed');
                            }
                        }
                        
                        // jezeli jest wartosc znizki
                        if ( isset($linia[(int)$sort_znizka-1]) ) {
                            if (isset($_POST['znizka']) && $filtr->process($_POST['znizka']) == '1' && (int)$sort_znizka > 0 && (float)$linia[(int)$sort_znizka-1] > 0) {
                                if ( $typ != 'shipping' ) {
                                     $pola[] = array('coupons_discount_value',(float)$linia[(int)$sort_znizka-1]); 
                                }
                            }
                        }
                        
                        unset($typ);
                        
                        // jezeli jest waznosc od
                        if ( isset($linia[(int)$sort_waznosc_od-1]) ) {
                            if (isset($_POST['waznosc_od']) && $filtr->process($_POST['waznosc_od']) == '1' && (int)$sort_waznosc_od > 0 && FunkcjeWlasnePHP::my_strtotime($filtr->process($linia[(int)$sort_waznosc_od-1])) > 0) {
                                $pola[] = array('coupons_date_start',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($linia[(int)$sort_waznosc_od-1]))));  
                              } else {
                                $pola[] = array('coupons_date_start','0000-00-00');  
                            }
                          } else {
                            $pola[] = array('coupons_date_start','0000-00-00');  
                        }
                        
                        // jezeli jest waznosc do
                        if ( isset($linia[(int)$sort_waznosc_do-1]) ) {
                            if (isset($_POST['waznosc_do']) && $filtr->process($_POST['waznosc_do']) == '1' && (int)$sort_waznosc_do > 0 && FunkcjeWlasnePHP::my_strtotime($filtr->process($linia[(int)$sort_waznosc_do-1])) > 0) {
                                $pola[] = array('coupons_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($linia[(int)$sort_waznosc_do-1])))); 
                              } else {
                                $pola[] = array('coupons_date_end','0000-00-00');  
                            }                    
                          } else {
                            $pola[] = array('coupons_date_end','0000-00-00');  
                        }
                        
                        // jezeli jest minimalne zamowienie
                        if ( isset($linia[(int)$sort_wartosc_zamowienia-1]) ) {
                            if (isset($_POST['wartosc_zamowienia']) && $filtr->process($_POST['wartosc_zamowienia']) == '1' && (int)$sort_wartosc_zamowienia > 0 && (float)$linia[(int)$sort_wartosc_zamowienia-1] > 0) {
                                $pola[] = array('coupons_min_order',(float)$linia[(int)$sort_wartosc_zamowienia-1]); 
                            }
                        }
                        
                        // jezeli jest maksymalne zamowienie
                        if ( isset($linia[(int)$sort_max_wartosc_zamowienia-1]) ) {
                            if (isset($_POST['wartosc_zamowienia_max']) && $filtr->process($_POST['wartosc_zamowienia_max']) == '1' && (int)$sort_max_wartosc_zamowienia > 0 && (float)$linia[(int)$sort_max_wartosc_zamowienia-1] > 0) {
                                $pola[] = array('coupons_max_order',(float)$linia[(int)$sort_max_wartosc_zamowienia-1]); 
                            }
                        }                    
                        
                        // jezeli jest grupa klientow
                        if ( isset($linia[(int)$sort_grupa_klientow-1]) ) {
                            if (isset($_POST['grupa_klientow']) && $filtr->process($_POST['grupa_klientow']) == '1' && (int)$sort_grupa_klientow > 0 && (int)$linia[(int)$sort_grupa_klientow-1] > 0) {
                                $pola[] = array('coupons_customers_groups_id',(int)$linia[(int)$sort_grupa_klientow-1]); 
                            }  
                        }         
                        
                        // jezeli sa moduly platnosci
                        if ( isset($linia[(int)$sort_moduly_platnosci-1]) ) {
                            if (isset($_POST['moduly_platnosci']) && $filtr->process($_POST['moduly_platnosci']) == '1' && !empty($linia[(int)$sort_moduly_platnosci-1])) {
                                //
                                $podzial = explode(',', trim((string)$filtr->process($linia[(int)$sort_moduly_platnosci-1])));
                                //
                                if ( count($podzial) > 0 ) {
                            
                                    $pola[] = array('coupons_customers_payment_id',implode(',', $podzial));  
                                  
                                } else {
                                  
                                    $pola[] = array('coupons_customers_payment_id','');  
                                    
                                }
                                //
                                unset($podzial);
                                //
                            }  
                        }                           

                        // jezeli jest kraj
                        if ( isset($linia[(int)$sort_kraj-1]) ) {
                            if (isset($_POST['kraj']) && $filtr->process($_POST['kraj']) == '1' && !empty($linia[(int)$sort_kraj-1])) {
                                //
                                $podzial = explode(',', trim((string)$filtr->process($linia[(int)$sort_kraj-1])));
                                $kraje = array();
                                //
                                foreach ( $podzial as $kraj_id ) {
                                    
                                    $sqlKraj = $db->open_query("SELECT s.countries_id FROM countries s, countries_description sd WHERE s.countries_id = sd.countries_id AND sd.language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "' AND s.countries_id = " . (int)$kraj_id . "");
                                    //
                                    if ((int)$db->ile_rekordow($sqlKraj) > 0) {
                                          
                                        $kraje[] = (int)$kraj_id;

                                    }
                                    //
                                    $db->close_query($sqlKraj);                                
                                    
                                }
                                //
                                if ( count($kraje) > 0 ) {
                            
                                    $pola[] = array('coupons_countries',implode(',', $kraje));  
                                  
                                } else {
                                  
                                    $pola[] = array('coupons_countries','');  
                                    
                                }
                              
                                unset($podzial, $kraje);
                            }  
                        }                        
                        
                        // jezeli jest minimalna ilosc produktow
                        if ( isset($linia[(int)$sort_ilosc_produktow-1]) ) {
                            if (isset($_POST['ilosc_produktow']) && $filtr->process($_POST['ilosc_produktow']) == '1' && (int)$sort_ilosc_produktow > 0 && (int)$linia[(int)$sort_ilosc_produktow-1] > 0) {
                                $pola[] = array('coupons_min_quantity',(int)$linia[(int)$sort_ilosc_produktow-1]); 
                            }  
                        }
                        
                        // jezeli jest maksymalna ilosc produktow
                        if ( isset($linia[(int)$sort_max_ilosc_produktow-1]) ) {
                            if (isset($_POST['ilosc_produktow_max']) && $filtr->process($_POST['ilosc_produktow_max']) == '1' && (int)$sort_max_ilosc_produktow > 0 && (int)$linia[(int)$sort_max_ilosc_produktow-1] > 0) {
                                $pola[] = array('coupons_max_quantity',(int)$linia[(int)$sort_max_ilosc_produktow-1]); 
                            }  
                        }                    
                        
                        // jezeli jest ilosc kuponow
                        if ( isset($linia[(int)$sort_ilosc_kuponow-1]) ) {
                            if (isset($_POST['ilosc_kuponow']) && $filtr->process($_POST['ilosc_kuponow']) == '1' && (int)$sort_ilosc_kuponow > 0 && (int)$linia[(int)$sort_ilosc_kuponow-1] > 0) {
                                $pola[] = array('coupons_quantity',(int)$linia[(int)$sort_ilosc_kuponow-1]); 
                              } else {
                                $pola[] = array('coupons_quantity','1'); 
                            }   
                        }
                        
                        // jezeli jest info o promocji
                        if ( isset($linia[(int)$sort_promocje-1]) ) {
                            if (isset($_POST['promocje']) && $filtr->process($_POST['promocje']) == '1' && $linia[(int)$sort_promocje-1] == 'nie' && (int)$sort_promocje > 0) {
                                $pola[] = array('coupons_specials','0'); 
                              } else {
                                $pola[] = array('coupons_specials','1'); 
                            }      
                        }
                        
                        // jezeli jest info o uzyciu
                        if ( isset($linia[(int)$sort_uzycie_kuponu-1]) ) {
                            if (isset($_POST['uzycie_kuponu']) && $filtr->process($_POST['uzycie_kuponu']) == '1' && $linia[(int)$sort_uzycie_kuponu-1] == 'nie' && (int)$sort_uzycie_kuponu > 0) {
                                $pola[] = array('coupons_customers_use','0'); 
                              } else {
                                $pola[] = array('coupons_customers_use','1'); 
                            }      
                        }                    
                        
                        /* ograniczenia */
                        
                        $byloOgraniczenie = false;
                        
                        $rodzajOgraniczenia = '';
                        $IdOgraniczenia = '';

                        // jezeli jest ograniczenie
                        if ( isset($linia[(int)$sort_warunki-1]) ) {
                            if (isset($_POST['warunki']) && $filtr->process($_POST['warunki']) == '1' && (int)$sort_warunki > 0 && ($linia[(int)$sort_warunki-1] == 'kategorie_producenci' || $linia[(int)$sort_warunki-1] == 'kategorie' || $linia[(int)$sort_warunki-1] == 'producenci' || $linia[(int)$sort_warunki-1] == 'produkty')) {                      
                                $rodzajOgraniczenia = $linia[(int)$sort_warunki-1]; 
                            }    
                        }
                        
                        // jezeli jest ograniczenie
                        if ( isset($linia[(int)$sort_warunki_id-1]) ) {
                            if (isset($_POST['warunki_id']) && $filtr->process($_POST['warunki_id']) == '1' && !empty($linia[(int)$sort_warunki_id-1])) {   
                                //
                                if ( strpos((string)$linia[(int)$sort_warunki_id-1],'#') > -1 ) {
                                     //
                                     $podzielTmp = explode('#', (string)$linia[(int)$sort_warunki_id-1]);
                                     //
                                } else {
                                     //
                                     $podzielTmp = array((string)$linia[(int)$sort_warunki_id-1]);
                                     //
                                }
                                //
                                $tablicaSpr = array();
                                //
                                foreach ( $podzielTmp as $Tbs ) {
                                    //            
                                    $tablicaSprTmp = array();
                                    $podzial = explode(',', (string)$Tbs);
                                    foreach ($podzial as $id) {
                                        if ( (int)$id > 0 ) {
                                             $tablicaSprTmp[] = $id;
                                        }
                                    }
                                    //
                                    $tablicaSpr[] = implode(',', (array)$tablicaSprTmp);
                                    //
                                }
                                //
                                if ( count($tablicaSpr) > 0 ) {
                                     $IdOgraniczenia = implode('#', (array)$tablicaSpr);
                                }
                            }  
                        }
                        
                        if ( $rodzajOgraniczenia != '' && $IdOgraniczenia != '' ) {
                             //
                             $pola[] = array('coupons_exclusion',$rodzajOgraniczenia);
                             $pola[] = array('coupons_exclusion_id',$IdOgraniczenia);
                             //
                        }
                        
                        unset($byloOgraniczenie, $tablicaSpr, $rodzajOgraniczenia, $IdOgraniczenia);
                          
                        // jezeli jest info o widocznosci
                        if ( isset($linia[(int)$sort_widoczny-1]) ) {
                            if (isset($_POST['widoczny']) && $filtr->process($_POST['widoczny']) == '1' && $linia[(int)$sort_widoczny-1] == 'nie' && (int)$sort_widoczny > 0) {
                                $pola[] = array('coupons_hidden','1'); 
                              } else {
                                $pola[] = array('coupons_hidden','0'); 
                            }      
                        }
                        
                        // jezeli jest id produktow powiazanych
                        if ( isset($linia[(int)$sort_produkty_id-1]) ) {
                            if (isset($_POST['produkty_id']) && $filtr->process($_POST['produkty_id']) == '1' && !empty($linia[(int)$sort_produkty_id-1])) {
                                //
                                $podzial = explode(',', trim((string)$filtr->process($linia[(int)$sort_produkty_id-1])));
                                //
                                if ( count($podzial) > 0 ) {
                            
                                    $pola[] = array('coupons_products_only','1');  
                                    $pola[] = array('coupons_products_only_id',implode(',', $podzial));  
                                  
                                } else {
                                  
                                    $pola[] = array('coupons_products_only','0');  
                                    $pola[] = array('coupons_products_only_id',''); 
                                    
                                }
                                //
                                unset($podzial);
                                //
                            } else {
                                //
                                $pola[] = array('coupons_products_only','0');  
                                $pola[] = array('coupons_products_only_id',''); 
                                //
                            }
                        }                            

                        // jezeli jest info o walucie
                        if ( isset($linia[(int)$sort_waluta-1]) ) {
                            if (isset($_POST['rodzaj_waluta']) && $filtr->process($_POST['rodzaj_waluta']) != '' && (int)$sort_waluta > 0) {
                                $pola[] = array('coupons_currency',$filtr->process($_POST['rodzaj_waluta'])); 
                              } else {
                                $pola[] = array('coupons_currency',''); 
                            }      
                        }                        

                        $pola[] = array('coupons_date_added','now()');
                        $pola[] = array('coupons_status','1');
                        //
                        $db->insert_query('coupons' , $pola); 
                        //
                        unset($pola);                    
                    }
                    
                    $db->close_query($sql);

                }

            }        

        }
        
        Funkcje::PrzekierowanieURL('kupony.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import danych</div>
    <div id="cont">
    
          <form action="kupony/kupony_import.php" method="post" id="kuponyForm" class="cmxform" enctype="multipart/form-data">   

          <script>
          $(function(){
             $('#upload').MultiFile({
              max: 1,
              accept:'txt|csv',
              STRING: {
               denied:'Nie można przesłać pliku w tym formacie $ext!',
               selected:'Wybrany plik: $file',
              }
             }); 
          });
          </script>          

          <div class="poleForm">
            <div class="naglowek">Import danych</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <div class="NaglowekEksport">Jakie dane będą importowane ?</div>
                
                <div class="RamkaKupony">
                
                    <table class="TablicaPozycji">
                    
                        <tr class="PoziomNaglowek">
                            <td style="text-align:left"><span>Pole do importu</span></td>
                            <td><span>Kolejność pola w pliku</span></td>
                        </tr>                 
                    
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="kod" id="par_kod" value="1" checked="checked" /><label class="OpisFor" for="par_kod">kod kuponu<em class="TipIkona"><b>Kod kuponu - przekazywany klientom sklepu</b></em></label></td>
                            <td><input type="text" size="2" name="kod_sort" value="1" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="opis" id="par_opis" value="1" /><label class="OpisFor" for="par_opis">opis<em class="TipIkona"><b>Opis kuponu - widoczny tylko dla administratora sklepu</b></em></label></td>
                            <td><input type="text" size="2" name="opis_sort" value="2" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="rodzaj" id="par_rodzaj" value="1" /><label class="OpisFor" for="par_rodzaj">rodzaj kuponu (kwota/procent/wysylka)<em class="TipIkona"><b>Dopuszczalne wartości - słowo: kwota, procent lub wysylka - jeżeli pole będzie zawierało inny zapis kupon nie zostanie dodany</b></em></label></td>
                            <td><input type="text" size="2" name="rodzaj_sort" value="3" /></td>
                        </tr>  
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="znizka" id="par_znizka" value="1" /><label class="OpisFor" for="par_znizka">zniżka<em class="TipIkona"><b>Zapis cyfrowy powyżej 0.01</b></em></label></td>
                            <td><input type="text" size="2" name="znizka_sort" value="4" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="waznosc_od" id="par_waznosc_od" value="1" /><label class="OpisFor" for="par_waznosc_od">ważność od (format dd-mm-rrrr)<em class="TipIkona"><b>Data w formacie dzien-miesiąc-rok np 15-04-2012</b></em></label></td>
                            <td><input type="text" size="2" name="waznosc_od_sort" value="5" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="waznosc_do" id="par_waznosc_do" value="1" /><label class="OpisFor" for="par_waznosc_do">ważność do (format dd-mm-rrrr)<em class="TipIkona"><b>Data w formacie dzien-miesiąc-rok np 15-04-2012</b></em></label></td>
                            <td><input type="text" size="2" name="waznosc_do_sort" value="6" /></td>
                        </tr> 
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="grupa_klientow" id="par_grupa_klientow" value="1" /><label class="OpisFor" for="par_grupa_klientow">id grupy klientów<em class="TipIkona"><b>Zapis cyfrowy powyżej 1 (jeżeli jest dodawane kilka grup poszczególne id muszą być rozdzielone przecinkami)</b></em></label></td>
                            <td><input type="text" size="2" name="grupa_klientow_sort" value="7" /></td>
                        </tr> 

                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="kraj" id="par_kraj" value="1" /><label class="OpisFor" for="par_kraj">id państw do wysyłek<em class="TipIkona"><b>Zapis cyfrowy powyżej 1 (jeżeli jest dodawane kilka państw poszczególne id muszą być rozdzielone przecinkami) - kupon dostępny tylko dla wysyłek do określonych państw</b></em></label></td>
                            <td><input type="text" size="2" name="kraj_sort" value="8" /></td>
                        </tr>                         
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="ilosc_produktow" id="par_ilosc_produktow" value="1" /><label class="OpisFor" for="par_ilosc_produktow">minimalna ilość produktów<em class="TipIkona"><b>Zapis cyfrowy powyżej 1</b></em></label></td>
                            <td><input type="text" size="2" name="ilosc_produktow_sort" value="9" /></td>
                        </tr> 
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="ilosc_produktow_max" id="par_ilosc_produktow_max" value="1" /><label class="OpisFor" for="par_ilosc_produktow_max">maksymalna ilość produktów<em class="TipIkona"><b>Zapis cyfrowy powyżej 1</b></em></label></td>
                            <td><input type="text" size="2" name="ilosc_produktow_max_sort" value="10" /></td>
                        </tr>                         
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="wartosc_zamowienia" id="par_wartosc_zamowienia" value="1" /><label class="OpisFor" for="par_wartosc_zamowienia">minimalna wartość zamówienia<em class="TipIkona"><b>Zapis cyfrowy powyżej 0.01</b></em></label></td>
                            <td><input type="text" size="2" name="wartosc_zamowienia_sort" value="11" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="wartosc_zamowienia_max" id="par_wartosc_zamowienia_max" value="1" /><label class="OpisFor" for="par_wartosc_zamowienia_max">maksymalna wartość zamówienia<em class="TipIkona"><b>Zapis cyfrowy powyżej 0.01</b></em></label></td>
                            <td><input type="text" size="2" name="wartosc_zamowienia_max_sort" value="12" /></td>
                        </tr>                        
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="ilosc_kuponow" id="par_ilosc_kuponow" value="1" /> <label class="OpisFor" for="par_ilosc_kuponow">ilość dostępnych kuponów<em class="TipIkona"><b>Zapis cyfrowy powyżej 1</b></em></label></td>
                            <td><input type="text" size="2" name="ilosc_kuponow_sort" value="13" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="promocje" id="par_promocje" value="1" /><label class="OpisFor" for="par_promocje">produkty promocyjne / wyprzedaż / produkt dnia<em class="TipIkona"><b>Dopuszczalne wartości - słowo: tak lub nie</b></em></label></td>
                            <td><input type="text" size="2" name="promocje_sort" value="14" /></td>
                        </tr> 
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="uzycie_kuponu" id="par_uzycie_kuponu" value="1" /><label class="OpisFor" for="par_uzycie_kuponu">użycie przez klienta tylko 1 raz<em class="TipIkona"><b>Dopuszczalne wartości - słowo: tak lub nie</b></em></label></td>
                            <td><input type="text" size="2" name="uzycie_kuponu_sort" value="15" /></td>
                        </tr> 
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="warunki" id="par_warunki" value="1" /><label class="OpisFor" for="par_warunki">ograniczenia typ (kategorie/producenci/produkty)<em class="TipIkona"><b>Dopuszczalne wartości - słowo: kategorie, producenci, kategorie_producenci lub produkty</b></em></label></td>
                            <td><input type="text" size="2" name="warunki_sort" value="16" /></td>
                        </tr>
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="warunki_id" id="par_warunki_id" value="1" /><label class="OpisFor" for="par_warunki_id">ograniczenia id (id muszą być oddzielone przecinkiem)<em class="TipIkona"><b>Id kategorii, producentów lub produktów - rozdzielone przecinkami - UWAGA! w przypadku kategorie_producenci należy najpierw podać id producentów x,x,x następnie znak # i dalej id kategorii w postaci x,x,x - przykład 1,3#5,6,12</b></em></label></td>
                            <td><input type="text" size="2" name="warunki_id_sort" value="17" /></td>
                        </tr>   
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="widoczny" id="par_widoczny" value="1" /><label class="OpisFor" for="par_widoczny">wyświetlanie kuponu na karcie produktu<em class="TipIkona"><b>Czy kupon ma być wyświetlany na karcie produktu ?</b></em></label></td>
                            <td><input type="text" size="2" name="widoczny_id_sort" value="18" /></td>
                        </tr>   
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="moduly_platnosci" id="par_moduly_platnosci" value="1" /><label class="OpisFor" for="par_moduly_platnosci">id modułów płatności<em class="TipIkona"><b>Zapis cyfrowy powyżej 1 (jeżeli jest dodawane kilka pozycji poszczególne id muszą być rozdzielone przecinkami)</b></em></label></td>
                            <td><input type="text" size="2" name="moduly_platnosci_sort" value="19" /></td>
                        </tr> 
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="produkty_id" id="par_produkty_id" value="1" /><label class="OpisFor" for="par_produkty_id">id produktów powiązanych<em class="TipIkona"><b>Zapis cyfrowy powyżej 1 (jeżeli jest dodawane kilka pozycji poszczególne id muszą być rozdzielone przecinkami)</b></em></label></td>
                            <td><input type="text" size="2" name="produkty_id_sort" value="20" /></td>
                        </tr> 
                        
                        <tr class="Inputy">
                            <td style="text-align:left"><input type="checkbox" name="rodzaj_waluta" id="par_rodzaj_waluta" value="1" /><label class="OpisFor" for="par_rodzaj_waluta">kod waluty<em class="TipIkona"><b>Zapis w formie PLN, EUR, USD</b></em></label></td>
                            <td><input type="text" size="2" name="rodzaj_waluta_sort" value="21" /></td>
                        </tr> 
                        
                    </table>
                
                    <p style="padding:12px;">
                        <label>Separator pól:</label>
                        <input type="radio" name="sep" id="sep_srednik" value=";" checked="checked" /><label class="OpisFor" for="sep_srednik">; (średnik) &nbsp;</label>
                        <input type="radio" name="sep" id="sep_dwukropek" value=":" /><label class="OpisFor" for="sep_dwukropek">: (dwukropek) &nbsp;</label>
                        <input type="radio" name="sep" id="sep_plotek" value="#" /><label class="OpisFor" for="sep_plotek"># (płotek)</label>
                    </p>
                    
                    <p style="padding:12px;">
                      <label>Plik do importu:</label>
                      <input type="file" name="file" id="upload" size="53" />
                    </p>

                </div>
                                
                <div class="LegnedaKuponow">
                
                    <span class="maleInfo" style="margin-left:0px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>
                
                    <div class="ostrzezenie">Jeżeli w bazie będzie istniał kupon o importowanym numerze import danego kuponu nie zostanie wykonany.</div> <br />
                    <div class="ostrzezenie">Jeżeli importowany kupon nie będzie miał numeru nie zostanie dodany.</div> <br />
                    <div class="ostrzezenie">Jeżeli nie będzie podany rodzaj kuponu sklep przyjmie domyślnie wartość kwotową.</div> <br />
                    <div class="ostrzezenie">Jeżeli nie będzie podana ilość dostępnych kuponów system wstawi domyślne 1.</div> <br />
                    <div class="ostrzezenie">Jako data dodania zostanie wstawiona dzisiejsza data.</div> <br />
                    <div class="ostrzezenie">Jeżeli jest dodawany typ ograniczenia użycia kuponu (kategorie, producenci lub produkty) muszą być podane również id (kategorii, producentów lub produktów) dla ograniczenia.</div>
                </div>
                
                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Importuj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
            </div>                 


          </div>                      
          </form>

    </div>    

    <?php
    include('stopka.inc.php');

}
?>