<?php

class Kupony {

    public $id;
    public $kupon;
    public $wartosc_zamowienia;
    public $ilosc_produktow;

    public function __construct( $kupon_kod ) {

        $this->id = $kupon_kod;
        $this->kupon = array();

        // ustalenie ilosci produktow i wartosci zamowienia
        $this->wartosc_zamowienia = 0;
        $this->ilosc_produktow = 0;
        
        $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
        
        $this->wartosc_zamowienia = $ZawartoscKoszyka['brutto_baza'];
        $this->ilosc_produktow = $ZawartoscKoszyka['ilosc_baza'];
        
        unset($ZawartoscKoszyka);        

        $this->DostepneKupony();

    }

    // funkcja zwraca w formie tablicy dane kuponu
    public function DostepneKupony() {

        $status = true;

        $data = date('Y-m-d');

        $zapytanie = "SELECT * FROM coupons
                               WHERE coupons_name = '" . $this->id . "' AND coupons_status = '1' AND
                               coupons_quantity > 0 AND 
                               ((('" . $data . "' >= coupons_date_start AND coupons_date_start != '0000-00-00') OR coupons_date_start = '0000-00-00') AND
                               ((coupons_date_end >= '" . $data . "' AND coupons_date_end != '0000-00-00') OR coupons_date_end = '0000-00-00'))";
      
        unset($data);

        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            $info = $sql->fetch_assoc();
            
            // kraj wysylki
            $kraj = $info['coupons_countries'];
            
            if ( isset($_SESSION['krajDostawy']['id']) && $info['coupons_countries'] != '0' && !empty($info['coupons_countries']) ) {
              
                 $podzial = explode(',', trim((string)$info['coupons_countries']));
                 
                 if ( !in_array($_SESSION['krajDostawy']['id'], $podzial) ) {
                      $status = false;
                 }
                 
                 unset($podzial);
                 
            }            
            
            $formaPlatnosci = false;
            
            // rodzaj platnosci
            if ( isset($_SESSION['rodzajPlatnosci']) && !empty($info['coupons_customers_payment_id']) ) {
                 //
                 if ( isset($_SESSION['rodzajPlatnosci']['platnosc_id']) ) {
                      //
                      $jakiePlatnosci = explode(',', (string)$info['coupons_customers_payment_id']);
                      //
                      if ( !in_array($_SESSION['rodzajPlatnosci']['platnosc_id'], $jakiePlatnosci) ) {
                           //
                           $status = false;
                           $formaPlatnosci = true;
                           //
                      }
                      //
                      unset($jakiePlatnosci);
                      //
                 }
                 //
            }                
            
            // ograniczenie tylko dla wybranej grupy klientow
            $grupaKlientowKuponu = false;
            
            if ( count(explode(',', (string)$info['coupons_customers_groups_id'])) > 0 && $info['coupons_customers_groups_id'] != '' && $status == true ) {
                //
                if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_SESSION['customers_groups_id']) && in_array($_SESSION['customers_groups_id'], explode(',', (string)$info['coupons_customers_groups_id'])) ) {
                     $status = true;
                   } else {
                     $status = false;
                     $grupaKlientowKuponu = true;                     
                     //
                     // sprawdzi czy nie jest dla niezalogowanych
                     //
                     if ( !isset($_SESSION['customers_groups_id']) ) {
                          //
                          if ( in_array('0', explode(',', (string)$info['coupons_customers_groups_id'])) ) {
                               //
                               $status = true;
                               $grupaKlientowKuponu = false;
                               //
                          }
                          //
                     }
                     //
                }
                //
            }
            
            // jezeli jest inna waluta
            if ( $info['coupons_currency'] != '' ) {
                 //
                 if ( $info['coupons_currency'] != $_SESSION['domyslnaWaluta']['kod'] ) {
                      $status = false;
                 }
                 //
            }

            // jezeli jest za mala ilosc produktow w koszyku
            if ( $info['coupons_min_quantity'] != '' && $info['coupons_min_quantity'] != '0' && $this->ilosc_produktow < $info['coupons_min_quantity'] ) {
                $status = false;
            }
            
            // jezeli jest za duza ilosc produktow w koszyku
            if ( $info['coupons_max_quantity'] != '' && $info['coupons_max_quantity'] != '0' && $this->ilosc_produktow > $info['coupons_max_quantity'] ) {
                $status = false;
            }            

            // jezeli jest za mala wartosc zamowienia w koszyku
            if ( $info['coupons_min_order'] != '' && $info['coupons_min_order'] != '0' && $this->wartosc_zamowienia < $GLOBALS['waluty']->PokazCeneBezSymbolu($info['coupons_min_order'],'',true) ) {
                $status = false;
            }

            // jezeli jest za duza wartosc zamowienia w koszyku
            if ( $info['coupons_max_order'] != '' && $info['coupons_max_order'] != '0' && $this->wartosc_zamowienia > $GLOBALS['waluty']->PokazCeneBezSymbolu($info['coupons_max_order'],'',true) ) {
                $status = false;
            }                
            
            $warunekPromocji = false;            
            $warunekPomniejszeniaZamowienia = false;
            $warunekPopUp = false;
            
            // dodatkowa tablica gdzie sa dodawane id produktow z promocji
            // zeby nie dublowac wykluczen jezeli produkt jest w promocji i np z niedozwolonej kategorii
            $idProduktowPromocji = array();
            
            // jezeli kupon ma wykluczenia promocji
            if ( $info['coupons_specials'] == '0' ) {
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) {
                    //
                    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                    //      
                    // jezeli jest produkt promocyjny
                    if ( ( $Produkt->ikonki['promocja'] == '1' || $Produkt->ikonki['wyprzedaz'] == '1' || $Produkt->info['produkt_dnia'] == 'tak' ) && $rekord['rodzaj_ceny'] == 'baza' ) {
                         //
                         $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                         //
                         $warunekPromocji = true;
                         $warunekPomniejszeniaZamowienia = true;
                         //
                         $idProduktowPromocji[] = Funkcje::SamoIdProduktuBezCech( $rekord['id'] );
                         //
                    }  
                    //
                    unset($Produkt);
                    //
                 }
                 //
            }
            
            // ograniczenia tylko dla konkretnych kategorii, producentow i produktow
            if ( !empty($info['coupons_exclusion']) && !empty($info['coupons_exclusion_id']) ) {
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) { 
                 
                    if ( $rekord['rodzaj_ceny'] == 'baza' ) {
                     
                        // jezeli jest tylko dla kategorii
                        if ( $info['coupons_exclusion'] == 'kategorie' ) {
                             //
                             // do jakich kategorii nalezy produkt
                             $tablica = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                             //
                             $nalezyDoKategorii = false;
                             foreach ( $tablica as $id ) {
                                // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                                if ( in_array($id, explode(',', (string)$info['coupons_exclusion_id']) ) ) {
                                     $nalezyDoKategorii = true;
                                }
                             }
                             //
                             // jezeli zadna z id katagorii nie nalezy do tablicy dozwolnych kategorii
                             // to obnizy wartosc zamowienia o wartosc produktu
                             if ( $nalezyDoKategorii == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                                  $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                                  $warunekPomniejszeniaZamowienia = true;
                             }
                             //
                             unset($nalezyDoKategorii);
                        }
                        
                        // jezeli jest tylko dla producenta
                        if ( $info['coupons_exclusion'] == 'producenci' ) {
                             //
                             // do jakich producentow nalezy produkt
                             $id = Producenci::ProduktProducent( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                             //
                             $nalezyDoProducenta = false;
                             // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                             if ( in_array($id, explode(',', (string)$info['coupons_exclusion_id']) ) ) {
                                 $nalezyDoProducenta = true;
                             }
                             //
                             // jezeli id producenta nie nalezy do tablicy dozwolnych producentow
                             // to obnizy wartosc zamowienia o wartosc produktu
                             if ( $nalezyDoProducenta == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                                  $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                                  $warunekPomniejszeniaZamowienia = true;
                             }
                             //
                             unset($id, $nalezyDoProducenta);
                        }  
                        
                        // jezeli jest tylko dla producentow i kategorii
                        if ( $info['coupons_exclusion'] == 'kategorie_producenci' ) {
                             //
                             $podzielTmp = explode('#', (string)$info['coupons_exclusion_id']);
                             //
                             if ( count($podzielTmp) == 2 ) {
                                 //
                                 // do jakich kategorii nalezy produkt
                                 $tablica = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                                 //
                                 $nalezyDoKategorii = false;
                                 foreach ( $tablica as $id ) {
                                    // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                                    if ( in_array($id, explode(',', (string)$podzielTmp[1]) ) ) {
                                         $nalezyDoKategorii = true;
                                    }
                                 }
                                 //
                                 // do jakich producentow nalezy produkt
                                 $id = Producenci::ProduktProducent( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                                 //
                                 $nalezyDoProducenta = false;
                                 // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                                 if ( in_array($id, explode(',', (string)$podzielTmp[0]) ) ) {
                                     $nalezyDoProducenta = true;
                                 }
                                 //
                                 // jezeli zadna z id katagorii nie nalezy do tablicy dozwolnych kategorii
                                 // to obnizy wartosc zamowienia o wartosc produktu
                                 if ( $nalezyDoProducenta == false && $nalezyDoKategorii == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                                      $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                                      $warunekPomniejszeniaZamowienia = true;
                                 }
                                 //
                                 unset($nalezyDoKategorii, $nalezyDoProducenta);
                                 //
                             } else {
                                 //
                                 $status = false;
                                 //
                             }
                             //
                        }                        

                        // jezeli jest tylko dla produktow
                        if ( $info['coupons_exclusion'] == 'produkty' ) {
                             //
                             $nalezyDoProduktow = false;
                             // sprawdza czy dane id nalezy do tablicy dozwolnych produktow
                             if ( in_array( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), explode(',', (string)$info['coupons_exclusion_id']) ) ) {
                                 $nalezyDoProduktow = true;
                             }
                             //
                             // jezeli id produktu nie nalezy do tablicy dozwolnych produktow
                             // to obnizy wartosc zamowienia o wartosc produktu
                             if ( $nalezyDoProduktow == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                                  $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                                  $warunekPomniejszeniaZamowienia = true;
                             }
                             //
                             unset($nalezyDoProduktow);
                        }                      
                        
                    }
                    
                 }
                 //                          
            }
            
            // po warunkach sprawdzi czy cos zostalo z kuponu
            if ( $this->wartosc_zamowienia <= 0 ) {
                 $status = false;
            }
            
            // jezeli jest dostepny tylko w powiazaniu z wybranymi produktami
            if ( $info['coupons_products_only'] == '1' && $info['coupons_products_only_id'] != '' ) {
                 //
                 $powiazany = false;
                 //
                 $jakieIdPowiazane = explode(',', (string)$info['coupons_products_only_id']);
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) {
                    //
                    if ( in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $jakieIdPowiazane) ) {
                         //
                         $powiazany = true;
                         //
                    }
                    //      
                 }
                 //
                 if ( $powiazany == false ) {
                      $status = false;
                 }
                 //
                 unset($powiazany, $jakieIdPowiazane);
                 //
            }
            
            // obliczanie wartosci kuponu
            switch ($info['coupons_discount_type']) {
            
              case "fixed":
              
                  $wartoscKuponu = $GLOBALS['waluty']->PokazCeneBezSymbolu($info['coupons_discount_value'],'',true);
              
                  if ( $info['coupons_currency'] != '' ) {
                       //
                       if ( $info['coupons_currency'] == $_SESSION['domyslnaWaluta']['kod'] ) {
                            $wartoscKuponu = $GLOBALS['waluty']->PokazCeneBezSymbolu($info['coupons_discount_value'],'',false);
                       }
                       //
                  }

                  if ( $wartoscKuponu >= $this->wartosc_zamowienia ) {
                       $wartoscKuponu = $this->wartosc_zamowienia;
                  }
                  
                  $typ_kuponu = 'kwota';
                  break;
                  
              case "percent":
              
                  $wartoscKuponu = round(($this->wartosc_zamowienia * ( $info['coupons_discount_value'] / 100 )),2);
                  
                  $typ_kuponu = 'procent';
                  break;
                  
              case "shipping":
              
                  $wartoscKuponu = round(0,2);
                  
                  if ( $_SESSION['rodzajDostawy']['wysylka_koszt'] ) {
                    
                       $DostepnaWysylkaKupon = true;
                       
                       if ( $info['coupons_modules_shipping'] != '' ) {
                            //
                            $PodzielWysylki = explode(',', (string)$info['coupons_modules_shipping']);
                            //
                            if ( !in_array($_SESSION['rodzajDostawy']['wysylka_id'], $PodzielWysylki) ) {
                                 //
                                 $DostepnaWysylkaKupon = false;
                                 //
                            }
                            //
                       }
                    
                       if ( $DostepnaWysylkaKupon == true ) {
                            //
                            // przeliczenie na inna walute jak jest
                            $wartoscKuponu = round((($_SESSION['rodzajDostawy']['wysylka_koszt'] * $_SESSION['domyslnaWaluta']['przelicznik']) * ((100 + $_SESSION['domyslnaWaluta']['marza']) / 100)),2);
                            //
                       }
                       
                       unset($DostepnaWysylkaKupon);
                    
                  }
                  
                  $typ_kuponu = 'wysylka';
                                
                  break;                  
                  
            }            
            
            // sprawdzenie czy kupon byl wygenerowany dla daneo uzytkownika jezeli jest adres email
            if ( isset($_SESSION['customer_email']) ) {
                 //
                 if ( $info['coupons_email'] != '' && $info['coupons_email_type'] == 'popup' ) {
                      //
                      if ( $info['coupons_email'] != $_SESSION['customer_email'] ) {
                            $status = false;
                            $warunekPopUp = true;
                      }
                      //
                 }
                 //
            }

            $this->kupon = array('kupon_id' => $info['coupons_id'],
                                 'kupon_kod' => $this->id,
                                 'kupon_typ' => $typ_kuponu,
                                 'kupon_wartosc' => $wartoscKuponu,
                                 'dostepne_wysylki' => $info['coupons_modules_shipping'],
                                 'kupon_status' => $status,
                                 'warunek_promocja' => $warunekPromocji,
                                 'mniejsza_wartosc' => $warunekPomniejszeniaZamowienia,
                                 'grupa_klientow' => $grupaKlientowKuponu,
                                 'warunek_popup' => $warunekPopUp,
                                 'tylko_zalogowani' => false,
                                 'kraj' => $kraj,
                                 'dostepne_platnosci' => $info['coupons_customers_payment_id'],
                                 'niedostepna_forma_platnosci' => $formaPlatnosci
            );
            
            $BylyWykluczenia = false;
            
            if ( PP_KOD_STATUS == 'tak' ) {
                
                // sprawdzanie czy kupon nie jest uzyty przez wlasciciela PP
                if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                    
                    $zapytanie_kupon = "SELECT coupons_id FROM coupons WHERE coupons_name = '" . $this->id . "' AND coupons_pp_id = " . (int)$_SESSION['customer_id'];
                    $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                    //
                    if ((int)$GLOBALS['db']->ile_rekordow($sql_kupon) > 0) {
                        //
                        $this->kupon = array();
                        //
                    }
                    //
                    $GLOBALS['db']->close_query($sql_kupon);
                    unset($zapytanie_kupon); 
                    
                }
            
            }
            
            // sprawdzanie czy kupon nie byl uzyty przez uzytkownika w ramach PP - jezeli tak to czysci tablice kuponu
            if ( PP_KOD_STATUS == 'tak' && PP_KOD_ILOSC_UZYC == 'tak' ) {
                 //
                 // id kuponu
                 $zapytanie_kupon = "SELECT coupons_id FROM coupons WHERE coupons_name = '" . $this->id . "' AND coupons_pp_id > 0";
                 $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                 //
                 $id_kuponu = '';
                 //
                 if ((int)$GLOBALS['db']->ile_rekordow($sql_kupon) > 0) {
                     //
                     $infe = $sql_kupon->fetch_assoc();
                     //
                     $id_kuponu = $infe['coupons_id'];
                     //
                     $GLOBALS['db']->close_query($sql_kupon);
                     unset($zapytanie_kupon); 
                     //
                     if ( $id_kuponu != '' && $_SESSION['gosc'] == '0' ) {
                     
                          // sprawdzanie uzyc
                          $zapytanie_kupon = "SELECT orders_id FROM coupons_to_customers WHERE coupons_id = '" . $id_kuponu . "' AND customers_id = '" . $_SESSION['customer_id'] . "'";
                          $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                          //
                          if ((int)$GLOBALS['db']->ile_rekordow($sql_kupon) > 0) {
                              //
                              $this->kupon = array();
                              //
                              $BylyWykluczenia = true;
                              //
                          } 
                          //
                          $GLOBALS['db']->close_query($sql_kupon);
                          unset($zapytanie_kupon);   
                          //
                     } else {
                          //
                          $this->kupon['tylko_zalogowani'] = true;
                          $this->kupon['kupon_status'] = false;
                          //
                          $BylyWykluczenia = true;
                          //
                     }
                     //
                 }
                 //
            }
            
            // uzycie przez klienta kuponu tylko 1 raz
            if ( (int)$info['coupons_customers_use'] == 1 && $BylyWykluczenia == false ) {
            
                  if ( isset($_SESSION['customer_email']) && isset($_SESSION['customer_id']) ) {
                    
                      // id kuponu
                      $zapytanie_kupon = "SELECT coupons_id FROM coupons WHERE coupons_name = '" . $this->id . "'";
                      $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                      //
                      $infe = $sql_kupon->fetch_assoc();
                      //
                      $id_kuponu = $infe['coupons_id'];
                      //
                      $GLOBALS['db']->close_query($sql_kupon);
                      unset($zapytanie_kupon); 
                      
                      // sprawdzanie uzyc
                      $zapytanie_kupon = "SELECT orders_id FROM coupons_to_orders WHERE coupons_id = '" . $id_kuponu . "'";
                      $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                      //
                      $uzyty = false;
                      //
                      if ((int)$GLOBALS['db']->ile_rekordow($sql_kupon) > 0) {
                          //
                          while ($infe = $sql_kupon->fetch_assoc()) {
                                //
                                $zapytanie_zamowienie = "SELECT customers_telephone, customers_email_address FROM orders WHERE orders_id = '" . $infe['orders_id'] . "'";
                                $sql_zamowienie = $GLOBALS['db']->open_query($zapytanie_zamowienie);                           
                                //
                                if ((int)$GLOBALS['db']->ile_rekordow($sql_zamowienie) > 0) {
                                    //
                                    $infz = $sql_zamowienie->fetch_assoc();
                                    //
                                    // mail
                                    if ( $infz['customers_email_address'] == $_SESSION['customer_email'] ) {
                                         $uzyty = true;
                                    }
                                    //
                                    // telefon
                                    if ( isset($_SESSION['adresDostawy']['telefon']) ) {
                                         //
                                         $telefon = preg_replace('/\D/', '', str_replace('+48', '', (string)$infz['customers_telephone']));
                                         $telefon_klient = preg_replace('/\D/', '', str_replace('+48', '', (string)$_SESSION['adresDostawy']['telefon']));
                                         //
                                         if ( $telefon == $telefon_klient ) {
                                              $uzyty = true;
                                         }
                                         //
                                    }
                                    //
                                }
                                //
                                $GLOBALS['db']->close_query($sql_zamowienie);
                                unset($zapytanie_zamowienie);                            
                                //
                          }
                          //
                      } 
                      //
                      if ( $uzyty == true ) {
                           //
                           $this->kupon['grupa_klientow'] = '';
                           $this->kupon['warunek_jedno_uzycie'] = true;
                           $this->kupon['kupon_status'] = false;
                           //
                           $BylyWykluczenia = true;
                           //
                      }
                      //
                      $GLOBALS['db']->close_query($sql_kupon);
                      unset($zapytanie_kupon);               

                  } 

            }   

            // uzycie tylko na pierwsze zakupy
            if ( (int)$info['coupons_customers_first_purchase'] == 1 && $BylyWykluczenia == false ) {
            
                  if ( isset($_SESSION['customer_email']) && isset($_SESSION['customer_id']) ) {

                      // sprawdzanie czy klient korzystal z kuponow
                      $zapytanie_kupon = "SELECT orders_id FROM coupons_to_orders";
                      $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                      //
                      $uzyty = false;
                      //
                      if ((int)$GLOBALS['db']->ile_rekordow($sql_kupon) > 0) {
                          //
                          while ($infe = $sql_kupon->fetch_assoc()) {
                                //
                                $zapytanie_zamowienie = "SELECT customers_telephone, customers_email_address FROM orders WHERE orders_id = '" . $infe['orders_id'] . "'";
                                $sql_zamowienie = $GLOBALS['db']->open_query($zapytanie_zamowienie);   
                                //
                                if ((int)$GLOBALS['db']->ile_rekordow($sql_zamowienie) > 0) {
                                    //
                                    $infz = $sql_zamowienie->fetch_assoc();
                                    //
                                    // mail
                                    if ( $infz['customers_email_address'] == $_SESSION['customer_email'] ) {
                                         $uzyty = true;
                                    }
                                    //
                                    // telefon
                                    if ( isset($_SESSION['adresDostawy']['telefon']) ) {
                                         //
                                         $telefon = preg_replace('/\D/', '', str_replace('+48', '', (string)$infz['customers_telephone']));
                                         $telefon_klient = preg_replace('/\D/', '', str_replace('+48', '', (string)$_SESSION['adresDostawy']['telefon']));
                                         //
                                         if ( $telefon == $telefon_klient ) {
                                              $uzyty = true;
                                         }
                                         //
                                    }
                                    //
                                }
                                //
                                $GLOBALS['db']->close_query($sql_zamowienie);
                                unset($zapytanie_zamowienie);                            
                                //
                          }
                          //
                      } 
                      //
                      if ( $uzyty == true ) {
                           //
                           $this->kupon['grupa_klientow'] = '';
                           $this->kupon['warunek_pierwsze_zakupy'] = true;
                           $this->kupon['kupon_status'] = false;
                           //
                      }
                      //
                      $GLOBALS['db']->close_query($sql_kupon);
                      unset($zapytanie_kupon);               

                  } 

            }            

            unset($wartoscKuponu, $warunekPromocji, $warunekPomniejszeniaZamowienia, $idProduktowPromocj, $grupaKlientowKuponu, $warunekPopUp, $info);

        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);

    }

} 

?>