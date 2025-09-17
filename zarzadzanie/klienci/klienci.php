<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    if (isset($_GET['zakladka']) && $_GET['zakladka'] != '' ) {
      unset($_GET['zakladka']);
    }
    if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) {
      $_GET['id_poz'] = $_GET['klient_id'];
      unset($_GET['klient_id']); 
    }
    
    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and CONCAT_WS(' ', c.customers_telephone, c.customers_firstname, c.customers_lastname, c.customers_email_address, a.entry_company, a.entry_nip, a.entry_city, a.entry_telephone) LIKE '%".$szukana_wartosc."%'";
    }

    if ( isset($_GET['szukaj_grupa']) && $_GET['szukaj_grupa'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_grupa']);
        $warunki_szukania .= " and c.customers_groups_id = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = ( $_GET['szukaj_status'] == '1' ? '1' : '0' );
        $warunki_szukania .= " and c.customers_status = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_typ']) && $_GET['szukaj_typ'] != '0' ) {
        $szukana_wartosc = ( $_GET['szukaj_typ'] == '2' ? '1' : '0' );
        $warunki_szukania .= " and c.customers_guest_account = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_punkty']) && $_GET['szukaj_punkty'] != '0' ) {
        $warunki_szukania .= " and c.customers_shopping_points > 0 ";
    }    

    if ( isset($_GET['szukaj_czarna_lista']) && $_GET['szukaj_czarna_lista'] != '0' ) {
        $warunki_szukania .= " and c.customers_black_list = 1";
    }   

    if ( isset($_GET['rodzaj_cen']) && $_GET['rodzaj_cen'] != '0' ) {
        $szukana_wartosc = ( $_GET['rodzaj_cen'] == '2' ? '0' : '1' );
        $warunki_szukania .= " and c.vat_netto = '".$szukana_wartosc."'";
    }    
    
    if ( isset($_GET['opiekun']) && (int)$_GET['opiekun'] > 0 ) {
        $szukana_wartosc = (int)$_GET['opiekun'];
        $warunki_szukania .= " and c.service = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }      

    if ( isset($_GET['kraj_klienta']) && (int)$_GET['kraj_klienta'] > 0 ) {
        $szukana_wartosc = (int)$_GET['kraj_klienta'];
        $warunki_szukania .= " and a.entry_country_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }     

    if ( isset($_GET['szukaj_newsletter']) && (int)$_GET['szukaj_newsletter'] > 0 ) {
        $szukana_wartosc = (int)$_GET['szukaj_newsletter'];
        $warunki_szukania .= " and c.customers_newsletter = '".(($szukana_wartosc == 1) ? 1 : 0)."'";
        unset($szukana_wartosc);
    }     
    
    if ( isset($_GET['szukaj_newsletter']) && (int)$_GET['szukaj_newsletter'] == 1 ) {
    
        if ( isset($_GET['szukaj_newsletter_grupa']) && (int)$_GET['szukaj_newsletter_grupa'] > 0 ) {
              //
              $id_klientow = array();
              //
              $sqlc = $db->open_query("select customers_id, customers_newsletter_group from customers");       
              //
              while ($infe = $sqlc->fetch_assoc()) {
                 //
                 $podzial = explode(',', (string)$infe['customers_newsletter_group']);
                 //
                 if ( in_array((int)$_GET['szukaj_newsletter_grupa'], $podzial) ) {
                      //
                      $id_klientow[] = $infe['customers_id'];
                      //
                 }
                 //
                 unset($podzial);
                 //
              }
              //
              $db->close_query($sqlc);                        
              //                        
              if ( count($id_klientow) > 0 ) {
                   $warunki_szukania .= " and (c.customers_id in (" . implode(',', (array)$id_klientow) . "))";
              } else {
                   $warunki_szukania .= " and c.customers_id = 0";
              }
              //
              //
              unset($id_klientow);
              //
        }

    }     

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT c.customers_id, CONCAT(c.customers_firstname, c.customers_lastname), c.service, c.vat_netto, c.vat_netto_forced, c.customers_shopping_points, c.customers_firstname, c.customers_lastname, c.customers_status, c.customers_email_address, c.customers_guest_account, c.customers_przetwarzanie, c.customers_telephone, c.customers_black_list, c.pp_code, c.pp_statistics, a.entry_country_id, a.entry_city, a.entry_street_address, a.entry_postcode, DATE_FORMAT(ci.customers_info_date_account_created, '%d.%m.%Y') AS data_rejestracji, ci.customers_info_date_account_last_modified, a.entry_company, c.customers_groups_id, count(cb.customers_id) AS koszyk, count(bs.basket_id) AS ilosc_koszykow, count(ic.cp_id) AS ilosc_cen, cp.unique_id
    " . ((KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak') ? ',(SELECT date_purchased FROM orders o WHERE o.customers_id = c.customers_id ORDER BY date_purchased desc LIMIT 1) as data_ostatniego_zamowienia,
                                                          (SELECT count(orders_id) FROM orders os WHERE os.customers_id = c.customers_id) as ilosc_zamowien' : '') . "
    FROM customers c
    LEFT JOIN address_book a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id
    LEFT JOIN customers_info ci on ci.customers_info_id = c.customers_id 
    LEFT JOIN customers_basket cb ON cb.customers_id = c.customers_id
    LEFT JOIN basket_save bs ON bs.customers_id = c.customers_id 
    LEFT JOIN customers_price ic ON ic.cp_customers_id = c.customers_id 
    LEFT JOIN customers_points cp ON cp.customers_id = c.customers_id AND cp.customers_id > 0 AND (cp.points_type = 'PP' OR cp.points_type = 'PM')
    " . $warunki_szukania;

    $zapytanie .= " GROUP BY c.customers_id"; 

    if ( isset($_GET['szukaj_koszyk']) && $_GET['szukaj_koszyk'] != '0' ) {
        if ( $_GET['szukaj_koszyk'] == '1' ) {
            $zapytanie .= " HAVING koszyk > 0 ";
        } elseif ( $_GET['szukaj_koszyk'] == '2' ) {
            $zapytanie .= " HAVING koszyk = 0 ";
        }
    }

    if ( isset($_GET['szukaj_zapisane_koszyki']) && $_GET['szukaj_zapisane_koszyki'] != '0' ) {
        if ( $_GET['szukaj_zapisane_koszyki'] == '1' ) {
            $zapytanie .= ((strpos((string)$zapytanie, 'HAVING') > -1) ? " and ilosc_koszykow > 0 " : " HAVING ilosc_koszykow > 0 ");
        } elseif ( $_GET['szukaj_zapisane_koszyki'] == '2' ) {
            $zapytanie .= ((strpos((string)$zapytanie, 'HAVING') > -1) ? " and ilosc_koszykow = 0 " : " HAVING ilosc_koszykow = 0 ");
        }
    }

    if ( isset($_GET['indywidualne_ceny']) && (int)$_GET['indywidualne_ceny'] > 0 ) {
        if ( $_GET['indywidualne_ceny'] == '1' ) {
            $zapytanie .= " HAVING ilosc_cen > 0 ";
        } elseif ( $_GET['indywidualne_ceny'] == '2' ) {
            $zapytanie .= " HAVING ilosc_cen = 0 ";
        }
    }

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
                $sortowanie = 'ci.customers_info_date_account_created desc';
                break;
            case "sort_a2":
                $sortowanie = 'ci.customers_info_date_account_created asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'c.customers_lastname desc';
                break;
            case "sort_a4":
                $sortowanie = 'c.customers_lastname asc';
                break;                 
            case "sort_a5":
                $sortowanie = 'c.customers_email_address desc';
                break;
            case "sort_a6":
                $sortowanie = 'c.customers_email_address asc';
                break;
            case "sort_a7":
                $sortowanie = 'c.customers_shopping_points desc';
                break;
            case "sort_a8":
                $sortowanie = 'c.customers_shopping_points asc';
                break;      
            case "sort_a9":
                $sortowanie = ((KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak') ? 'data_ostatniego_zamowienia desc' : 'c.customers_id');
                break;
            case "sort_a10":
                $sortowanie = ((KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak') ? 'data_ostatniego_zamowienia asc' : 'c.customers_id');
                break;  
            case "sort_a11":
                $sortowanie = ((KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak') ? 'ilosc_zamowien desc' : 'c.customers_id');
                break;
            case "sort_a12":
                $sortowanie = ((KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak') ? 'ilosc_zamowien asc' : 'c.customers_id');
                break;                
        }            
    } else { $sortowanie = 'ci.customers_info_date_account_created desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        // opiekun zamowienia - tablica
        $tablica_opiekunow = array();
        //
        $zapytanie_tmp = "select distinct * from admin";
        $sqls = $db->open_query($zapytanie_tmp);
        //
        if ((int)$db->ile_rekordow($sqls) > 0) {
            //
            while ($infs = $sqls->fetch_assoc()) {
                  $tablica_opiekunow[ $infs['admin_id'] ] = $infs['admin_firstname'] . ' ' . $infs['admin_lastname'];
            }
            //
        }
        unset($zapytanie_tmp, $infs);  
        $db->close_query($sqls);
        //   

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID', 'center'),
                                      array('Klient', 'center'),
                                      array('Kontakt', 'center'),
                                      array('Grupa', 'center', '', 'class="ListingSchowaj"'),
                                      array('Data rejestracji', 'center'),
                                      array('Zamówień', 'center'),
                                      array('Koszyk', 'center', '', 'class="ListingSchowaj"'),
                                      array('Schowek', 'center', '', 'class="ListingSchowaj"'),
                                      array('Punkty', 'center', '', 'class="ListingSchowaj"'));
                                      
            if ( NETTO_DLA_UE == 'tak' ) {
                 $tablica_naglowek[] = array('Ceny netto', 'center');
            }
            
            $tablica_naglowek[] = array('Status', 'center');
            
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
              
                  if ( KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'nie' ) {
                       $info['ilosc_zamowien'] = Klienci::pokazIloscZamowienKlienta($info['customers_id']);
                  }

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['customers_id']) {
                     $tekst .= '<tr class="pozycja_on' . (($info['customers_black_list'] == 1) ? ' CzarnaLista' : '') . '" id="sk_'.$info['customers_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off' . (($info['customers_black_list'] == 1) ? ' CzarnaLista' : '') . '" id="sk_'.$info['customers_id'].'">';
                  } 

                  // aktywany czy nieaktywny
                  if ($info['customers_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Konto jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'Konto jest nieaktywne'; }               

                  $tablica = array();
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="' . $info['customers_id'] . '" id="opcja_' . $info['customers_id'] . '" /><label class="OpisForPustyLabel" for="opcja_' . $info['customers_id'] . '"></label><input type="hidden" name="id[]" value="' . $info['customers_id'] . '" />','center');

                  $tablica[] = array((($info['customers_black_list'] == 1) ? '<div class="CzarnaListaId">' . $info['customers_id'] . '</div>' : $info['customers_id']),'center');
                  $wyswietlana_nazwa = '';
                  $kontakt = '';

                  if ( $info['entry_company'] != '' ) {
                    $wyswietlana_nazwa .= '<span class="Firma">'.$info['entry_company'] . '</span><br />';
                  }
                  $wyswietlana_nazwa .= $info['customers_firstname']. ' ' . $info['customers_lastname'] . '<br />';
                  $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                  $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'] . '<br />';
                  
                  if ( $_SESSION['krajDostawy']['id'] != $info['entry_country_id'] ) {
                       //
                       $wyswietlana_nazwa .= Klienci::pokazNazwePanstwa($info['entry_country_id']) . '<br />';
                       //
                  }
                  
                  // jezeli staly klient - tylko zaplacone zamowienia                  
                  if ( $info['ilosc_zamowien'] > 1 ) {
                       $iloscZam = (int)Klienci::pokazIloscZamowienKlienta($info['customers_id'], 0, true);
                       $wyswietlana_nazwa = '<em class="TipChmurka" style="float:right"><b>Stały klient - ilość zamówień: ' . $iloscZam . '</b><img src="obrazki/medal.png" alt="Stały klient" /></em>' . $wyswietlana_nazwa;
                       unset($iloscZam);                           
                  }
                  
                  // zarejestrowany czy nie
                  if ( $info['customers_guest_account'] == '1' ) { $wyswietlana_nazwa = '<em class="TipChmurka" style="float:right"><b>Klient bez rejestracji</b><img src="obrazki/gosc.png" alt="Klient bez rejestracji" /></em>' . $wyswietlana_nazwa; };

                  // program pp
                  if ( $info['pp_code'] != '' || (int)$info['pp_statistics'] > 0 || (int)$info['unique_id'] > 0 ) { 
                      // sprawdzi czy juz nie ma kodu dla tego klienta
                      $sqlKupon = $db->open_query("select coupons_name from coupons where coupons_pp_id = '" . $info['customers_id'] . "' and coupons_name = '" . $info['pp_code'] . "'");
                      //
                      if ( (int)$db->ile_rekordow($sqlKupon) > 0 ) {
                          //
                          $wyswietlana_nazwa = '<em class="TipChmurka" style="float:right;margin-left:3px"><b>Klient uczestniczy w programie partnerskim</b><img src="obrazki/pp.png" alt="Klient w PP" /></em>' . $wyswietlana_nazwa; 
                          //
                      }
                      //
                      $db->close_query($sqlKupon);
                  };                  
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:1.8');

                  if (!empty($info['customers_email_address'])) {
                     $kontakt .= '<span class="MalyMail">' . $info['customers_email_address'] . '</span>';
                  }
                  if (!empty($info['customers_telephone'])) {
                      $kontakt .= '<span class="MalyTelefon">' . $info['customers_telephone'] . '</span>';
                  }
                  $tablica[] = array($kontakt,'','line-height:17px');
                  
                  // opiekun klienta
                  if (isset($tablica_opiekunow[(int)$info['service']])) {
                      $opiekun = '<span class="Opiekun">Opiekun:<span>' . $tablica_opiekunow[(int)$info['service']] . '</span></span>';
                     } else {
                      $opiekun = '';
                  }   
                  
                  $tablica[] = array( (($info['customers_guest_account'] == '1') ? '-' : Klienci::pokazNazweGrupyKlientow($info['customers_groups_id']) . $opiekun), 'center', '', 'class="ListingSchowaj"');
                  
                  unset($opiekun);
                  
                  $tablica[] = array($info['data_rejestracji'],'center');
                  
                  $data_zamowienia = '';
                  if ( KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak' ) {                    
                      if ( Funkcje::czyNiePuste($info['data_ostatniego_zamowienia']) ) {
                           $data_zamowienia = '<br /><small style="color:#888" class="ListingSchowaj">Ostatnie zamówienie: <br />' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['data_ostatniego_zamowienia'])) . '</small>';
                      }
                  }
                  $tablica[] = array((($info['ilosc_zamowien'] > 0) ? $info['ilosc_zamowien'] : '-') . $data_zamowienia,'center');
                  unset($data_zamowienia);

                  $tablica[] = array(( $info['koszyk'] > 0 ? $info['koszyk'] : '-' ), 'center', '', 'class="ListingSchowaj"');
                  $tablica[] = array(Klienci::pokazIloscProduktowSchowka($info['customers_id']), 'center', '', 'class="ListingSchowaj"');
                  
                  /* punkty */
                  $tablica[] = array((((int)$info['customers_shopping_points'] == 0) ? '-' : $info['customers_shopping_points'] . ' pkt'), 'center', '', 'class="ListingSchowaj"');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['customers_id']; 
                  
                  $zmienne_do_przekazania_zamowienia = '?klient_id='.(int)$info['customers_id']; 
                  
                  if ( NETTO_DLA_UE == 'tak' ) {
                    
                      // tylko ceny netto
                      if ($info['vat_netto'] == '1') { $obraz_netto = 'cena.png'; $alt_netto = 'Klient kupuje w cenach netto'; } else { $obraz_netto = 'aktywny_off.png'; $alt_netto = 'Klient kupuje w cenach brutto'; }   
                      if ( $info['customers_guest_account'] == '1' ) {
                           $tablica[] = array('<em class="TipChmurka"><b>Tylko ceny netto</b><img src="obrazki/'.$obraz_netto.'" alt="'.$alt_netto.'" /></em>','center');                 
                        } else {
                           if ( (int)$info['vat_netto_forced'] == 0 ) {
                                $tablica[] = array('<a class="TipChmurka" href="klienci/klienci_ceny_netto.php'.$zmienne_do_przekazania.'"><b>Tylko ceny netto</b><img src="obrazki/'.$obraz_netto.'" alt="'.$alt_netto.'" /></a>','center');                 
                           } else {
                                $tablica[] = array('<em class="TipChmurka"><b>Tylko ceny netto - dla każdego zamówienia</b><img src="obrazki/'.$obraz_netto.'" alt="'.$alt_netto.'" /></em>','center');                 
                           }                                
                      }
                      unset($obraz_netto, $alt_netto);
                  
                  }
                  
                  $tgm = '-';
                  if ($info['customers_guest_account'] == '0') {
                      $tgm = '<a class="TipChmurka" href="klienci/klienci_status.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>';
                  }
                  
                  if ($info['customers_status'] == '0' && $info['customers_guest_account'] == '0') {
                      $tgm .= ' &nbsp; <a class="TipChmurka" href="klienci/klienci_status_email.php'.$zmienne_do_przekazania.'"><b>Aktywuj konto i wyślij email o aktywacji</b><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail o aktywacji" /></a>';
                  }

                  $tablica[] = array($tgm,'center');  

                  unset($tgm);

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_wyslij_email.php'.$zmienne_do_przekazania.'"><b>Wyślij wiadomość e-mail</b><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail" /></a>';
                  if ( SMS_WLACZONE == 'tak' ) {
                    if ( Klienci::CzyNumerGSM($info['customers_telephone']) ) {
                      $tekst .= '<a class="TipChmurka" href="klienci/klienci_wyslij_sms.php'.$zmienne_do_przekazania.'"><b>Wyślij wiadomość SMS</b><img src="obrazki/wyslij_sms.png" alt="Wyślij wiadomość SMS" /></a>';
                    } else {
                      $tekst .= '<em class="TipChmurka"><b>Brak numeru GSM - nie można wysłać wiadomości</b><img src="obrazki/wyslij_sms_off.png" alt="Brak numeru GSM - nie można wysłać wiadomości" /></em>';
                    }
                  }
                  
                  if ( $info['ilosc_zamowien'] > 0 ) {
                       $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia.php'.$zmienne_do_przekazania_zamowienia.'"><b>Zamówienia klienta</b><img src="obrazki/lista_wojewodztw.png" alt="Zamówienia klienta" /></a>';
                  }

                  if ( $info['koszyk'] > 0 ) {
                    $tekst .= '<em class="TipChmurka"><b>Pokaz zawartość koszyka</b><img onclick="podgladKoszyka(\'' . (int)$info['customers_id'] . '\')" class="cur" style="cursor:pointer;" src="obrazki/koszyk.png" alt="" /></em>';
                  }
                  
                  if ( $info['customers_guest_account'] != '1' && $info['ilosc_koszykow'] > 0 ) {
                    $tekst .= '<a class="TipChmurka" href="klienci/klienci_edytuj.php'.$zmienne_do_przekazania.'&zakladka=10"><b>Zapisane koszyki klienta</b><img src="obrazki/koszyk_zapisany.png" alt="Zapisane koszyki" /></a>';
                  }                     

                  if ( $info['customers_black_list'] == '1' ) {
                    $tekst .= '<a class="TipChmurka" href="klienci/klienci_czarna_lista_usun.php'.$zmienne_do_przekazania.'"><b>Usuń klienta z czarnej listy</b><img class="CzarnaListaUsun" src="obrazki/czarna_lista_ikona.png" alt="Czarna lista" /></a>';                      
                  } else {
                    $tekst .= '<a class="TipChmurka" href="klienci/klienci_czarna_lista_dodaj.php'.$zmienne_do_przekazania.'"><b>Dodaj klienta do czarnej listy</b><img src="obrazki/czarna_lista_ikona.png" alt="Czarna lista" /></a>';                      
                  }
                  
                  $tekst .= '<br /><br />';
                  
                  if ( $info['customers_guest_account'] != '1' ) {
                    $tekst .= '<a class="TipChmurka" href="klienci/klienci_zmien_haslo.php'.$zmienne_do_przekazania.'"><b>Zmień hasło</b><img src="obrazki/haslo.png" alt="Zmień hasło" /></a>';
                  }    

                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_dodaj.php?klient='.(int)$info['customers_id'].'"><b>Dodaj nowe zamówienie</b><img src="obrazki/import.png" alt="Dodaj nowe zamówienie" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
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
           $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_klienci.php', 50, 400 );
        });

        function podgladKoszyka(id_klienta) {
            $.colorbox( { href:"ajax/koszyk_klienta.php?uzytkownik_id=" + id_klienta, maxHeight:'90%', open:true, initialWidth:50, initialHeight:50, onComplete : function() { $(this).colorbox.resize(); } } ); 
        }
        </script>    

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Klienci</div>

            <div id="wyszukaj">
                <form action="klienci/klienci.php" method="post" id="klienciForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Grupa:</span>
                    <?php
                    $tablica = Klienci::ListaGrupKlientow();
                    echo Funkcje::RozwijaneMenu('szukaj_grupa', $tablica, ((isset($_GET['szukaj_grupa'])) ? $filtr->process($_GET['szukaj_grupa']) : '')); ?>
                </div>  

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablica_status= array();
                    $tablica_status[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_status[] = array('id' => '1', 'text' => 'aktywny');
                    $tablica_status[] = array('id' => '2', 'text' => 'nieaktywny');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_select">
                    <span>Typ:</span>
                    <?php
                    $tablica_typ = array();
                    $tablica_typ[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_typ[] = array('id' => '1', 'text' => 'zarejestrowany');
                    $tablica_typ[] = array('id' => '2', 'text' => 'gość');
                    echo Funkcje::RozwijaneMenu('szukaj_typ', $tablica_typ, ((isset($_GET['szukaj_typ'])) ? $filtr->process($_GET['szukaj_typ']) : '')); ?>
                </div>  

                <div class="wyszukaj_select">
                    <span>Koszyk (porzucony):</span>
                    <?php
                    $tablica_koszyk = array();
                    $tablica_koszyk[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_koszyk[] = array('id' => '1', 'text' => 'tak');
                    $tablica_koszyk[] = array('id' => '2', 'text' => 'nie');
                    echo Funkcje::RozwijaneMenu('szukaj_koszyk', $tablica_koszyk, ((isset($_GET['szukaj_koszyk'])) ? $filtr->process($_GET['szukaj_koszyk']) : '')); ?>
                </div> 
                
                <div class="wyszukaj_select">
                    <span>Zapisane koszyki:</span>
                    <?php
                    $tablica_koszyk = array();
                    $tablica_koszyk[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_koszyk[] = array('id' => '1', 'text' => 'tak');
                    $tablica_koszyk[] = array('id' => '2', 'text' => 'nie');
                    echo Funkcje::RozwijaneMenu('szukaj_zapisane_koszyki', $tablica_koszyk, ((isset($_GET['szukaj_zapisane_koszyki'])) ? $filtr->process($_GET['szukaj_zapisane_koszyki']) : '')); ?>
                </div>                 

                <div class="wyszukaj_select">
                    <span>Punkty:</span>
                    <?php
                    $tablica_typ = array();
                    $tablica_typ[] = array('id' => '0', 'text' => 'wszyscy');
                    $tablica_typ[] = array('id' => '1', 'text' => 'tylko z punktami');
                    echo Funkcje::RozwijaneMenu('szukaj_punkty', $tablica_typ, ((isset($_GET['szukaj_punkty'])) ? $filtr->process($_GET['szukaj_punkty']) : '')); ?>
                </div> 

                <div class="wyszukaj_select">
                    <span>Klienci na czarnej liście:</span>
                    <?php
                    $tablica_czarna_lista = array();
                    $tablica_czarna_lista[] = array('id' => '0', 'text' => 'wszyscy');
                    $tablica_czarna_lista[] = array('id' => '1', 'text' => 'tylko z czarnej listy');
                    echo Funkcje::RozwijaneMenu('szukaj_czarna_lista', $tablica_czarna_lista, ((isset($_GET['szukaj_czarna_lista'])) ? $filtr->process($_GET['szukaj_czarna_lista']) : '')); ?>
                </div>   

                <div class="wyszukaj_select">
                    <span>Rodzaj cen:</span>
                    <?php
                    $tablica_koszyk = array();
                    $tablica_koszyk[] = array('id' => '0', 'text' => 'wszystkie');
                    $tablica_koszyk[] = array('id' => '1', 'text' => 'tylko netto');
                    $tablica_koszyk[] = array('id' => '2', 'text' => 'tylko brutto');
                    echo Funkcje::RozwijaneMenu('rodzaj_cen', $tablica_koszyk, ((isset($_GET['rodzaj_cen'])) ? $filtr->process($_GET['rodzaj_cen']) : '')); ?>
                </div>                 
                
                <div class="wyszukaj_select">
                    <span>Opiekun:</span>
                    <?php
                    // pobieranie informacji od uzytkownikach
                    $zapytanie_tmp = "select * from admin order by admin_lastname, admin_firstname";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_user = array();
                    $tablica_user[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                           $tablica_user[] = array('id' => $infs['admin_id'], 'text' => $infs['admin_firstname'] . ' ' . $infs['admin_lastname']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    echo Funkcje::RozwijaneMenu('opiekun', $tablica_user, ((isset($_GET['opiekun'])) ? $filtr->process($_GET['opiekun']) : ''), ' style="max-width:150px"'); ?>
                </div>
                
                <div class="wyszukaj_select">
                    <span>Kraj klienta:</span>
                    <?php
                    $tablica_panstw = array();
                    $tablica_panstw[] = array('id' => 0, 'text' => 'dowolny');   
                    $tablica_panstw = array_merge($tablica_panstw, Klienci::ListaPanstw());
                    echo Funkcje::RozwijaneMenu('kraj_klienta', $tablica_panstw, ((isset($_GET['kraj_klienta'])) ? (int)$_GET['kraj_klienta'] : ''));
                    unset($tablica_panstw);
                    ?>
                </div> 
                
                <div class="wyszukaj_select">
                    <span>Indywidualne ceny:</span>
                    <?php
                    $tablica_indyw_ceny = Array();
                    $tablica_indyw_ceny[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_indyw_ceny[] = array('id' => '1', 'text' => 'tak');
                    $tablica_indyw_ceny[] = array('id' => '2', 'text' => 'nie');
                    echo Funkcje::RozwijaneMenu('indywidualne_ceny', $tablica_indyw_ceny, ((isset($_GET['indywidualne_ceny'])) ? $filtr->process($_GET['indywidualne_ceny']) : '')); ?>
                </div>                 
                
                <script>
                function grupa_news(id) {
                  if (parseInt(id) == 1) {
                      $('#grupy_newslettera').show();
                  } else {
                      $('#grupy_newslettera').hide();
                  }
                }
                </script>
                
                <div class="wyszukaj_select">
                    <span>Newsletter:</span>
                    <?php
                    $tablica_newsletter = array();
                    $tablica_newsletter[] = array('id' => '0', 'text' => 'wszyscy');
                    $tablica_newsletter[] = array('id' => '1', 'text' => 'zapisani');
                    $tablica_newsletter[] = array('id' => '2', 'text' => 'niezapisani');
                    echo Funkcje::RozwijaneMenu('szukaj_newsletter', $tablica_newsletter, ((isset($_GET['szukaj_newsletter'])) ? $filtr->process($_GET['szukaj_newsletter']) : ''), ' onchange="grupa_news(this.value)"'); ?>
                </div> 
                
                <?php
                $tablica_grup = Newsletter::GrupyNewslettera();
                if ( count($tablica_grup) > 0 ) {
                ?>                
                
                <div class="wyszukaj_select" id="grupy_newslettera" <?php echo ((isset($_GET['szukaj_newsletter']) && (int)$_GET['szukaj_newsletter'] == 1) ? '' : 'style="display:none"'); ?>>
                    <span>Grupa newslettera:</span>
                    <?php
                    $tablica_newsletter_grupa = array();
                    $tablica_newsletter_grupa[] = array('id' => '0', 'text' => 'dowolna');
                    foreach ($tablica_grup as $grupa) {
                        $tablica_newsletter_grupa[] = array('id' => $grupa['id'], 'text' => $grupa['text']);
                    }
                    echo Funkcje::RozwijaneMenu('szukaj_newsletter_grupa', $tablica_newsletter_grupa, ((isset($_GET['szukaj_newsletter_grupa'])) ? $filtr->process($_GET['szukaj_newsletter_grupa']) : '')); ?>
                </div>                 
                
                <?php
                unset($tablica_grup);
                }
                ?>                 
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra  
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>                

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="klienci/klienci.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="klienci/klienci_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="klienci/klienci.php?sort=sort_a1">daty rejestracji malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/klienci.php?sort=sort_a2">daty rejestracji rosnąco</a>
                <?php if ( KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak' ) { ?>
                <a id="sort_a9" class="sortowanie" href="klienci/klienci.php?sort=sort_a9">daty ostatniego zamówienia malejąco</a>
                <a id="sort_a10" class="sortowanie" href="klienci/klienci.php?sort=sort_a10">daty ostatniego zamówienia rosnąco</a>                
                <?php } ?>
                <a id="sort_a3" class="sortowanie" href="klienci/klienci.php?sort=sort_a3">nazwiska malejąco</a>
                <a id="sort_a4" class="sortowanie" href="klienci/klienci.php?sort=sort_a4">nazwiska rosnąco</a>
                <a id="sort_a5" class="sortowanie" href="klienci/klienci.php?sort=sort_a5">e-mail malejąco</a>
                <a id="sort_a6" class="sortowanie" href="klienci/klienci.php?sort=sort_a6">e-mail rosnąco</a>
                <a id="sort_a7" class="sortowanie" href="klienci/klienci.php?sort=sort_a7">ilość pkt malejąco</a>
                <a id="sort_a8" class="sortowanie" href="klienci/klienci.php?sort=sort_a8">ilość pkt rosnąco</a> 
                <?php if ( KLIENCI_DATA_OSTATNIEGO_ZAMOWIENIA == 'tak' ) { ?>
                <a id="sort_a11" class="sortowanie" href="klienci/klienci.php?sort=sort_a11">ilość zamówień malejąco</a>
                <a id="sort_a12" class="sortowanie" href="klienci/klienci.php?sort=sort_a12">ilość zamówień rosnąco</a>                 
                <?php } ?>

            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="klienci/klienci_dodaj.php">dodaj nowego klienta</a>
                    <a style="margin-left:10px" class="usun" href="klienci/klienci_usun_masowe.php">usuń klientów</a>
                </div> 
                <div id="Legenda" class="rg">
                    <span class="StalyKlient"> stały klient</span>
                    <span class="BezKonta"> klient bez rejestracji</span>
                    <span class="KlientPP"> klient uczestniczy w programie partnerskim</span>
                </div>                 
            </div>
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            
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
                        <option value="1">usuń zaznaczonych klientów</option>
                    </select>
                </div>
                
                <div style="clear:both;"></div>
                        
            </div>
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <?php if ($ile_pozycji > 0) { ?>
            <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
            <?php } ?>              
            
            </form> 
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('klienci/klienci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'customers_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
