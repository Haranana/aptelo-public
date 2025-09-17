<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    $NaglowekCsv = '';
    $CoDoZapisania = '';

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($filtr->process($_POST['plik']), "a");
    // blokada pliku do zapisu
    flock($fp, 2);

    $rodzaj_klienta = "and c.customers_guest_account = '0'";
    //
    if ( isset($_POST['rodzaj_klientow']) && (int)$_POST['rodzaj_klientow'] == 1 ) {
         $rodzaj_klienta = '';
    }

    $ZapytanieKlient = "select c.*, 
                               ci.customers_info_date_account_created, 
                               ci.customers_info_date_of_last_logon,
                               (select entry_country_id from address_book where address_book_id = c.customers_default_address_id and customers_id = c.customers_id) as panstwo
                          from customers c 
                     left join customers_info ci on ci.customers_info_id = c.customers_id where customers_id > 0 " . $rodzaj_klienta . " order by c.customers_firstname, c.customers_lastname limit ".(int)$_POST['limit'].",1";

    $sqlKlient = $db->open_query($ZapytanieKlient);
    $infc = $sqlKlient->fetch_assoc();  
    
    $importuj = true;
    
    if ( (int)$_POST['panstwo'] > 0 ) {
         
         if ( (int)$_POST['panstwo'] != (int)$infc['panstwo'] ) {
               
               $importuj = false;
               
         }
         
    }
           
    // id domyslnego adresu
    $Adres = $infc['customers_default_address_id'];
    $idKlient = $infc['customers_id'];
    
    $NaglowekCsv .= 'Id_baza;';
    $CoDoZapisania .= '"' . $idKlient . '";';    
    
    if ( $importuj == true ) {
      
        if ( (int)$_POST['telefon'] == 0 && (int)$_POST['email'] == 0 ) {
        
            if ( isset($_POST['kod_pp']) && (int)$_POST['kod_pp'] == 1 ) {
                 //
                 $NaglowekCsv .= 'PP_kod;';
                 $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['pp_code']) . '";';          
                 //
            }
            
            $NaglowekCsv .= 'Id_magazyn;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_id_private']) . '";';    
            
            $NaglowekCsv .= 'Nick;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_nick']) . '";';
            
            $NaglowekCsv .= 'Imie;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_firstname']) . '";';

            $NaglowekCsv .= 'Nazwisko;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_lastname']) . '";';
            
            $NaglowekCsv .= 'Adres_email;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_email_address']) . '";';

            $NaglowekCsv .= 'Telefon;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_telephone']) . '";';
            
            $NaglowekCsv .= 'Haslo;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_password']) . '";'; 

            $NaglowekCsv .= 'Data_rejestracji;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_info_date_account_created']) . '";'; 

            $NaglowekCsv .= 'Ostatnie_logowanie;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_info_date_of_last_logon']) . '";'; 

            $NaglowekCsv .= 'Newsletter;';
            if ($infc['customers_newsletter'] == '1') {
                $CoDoZapisania .= '"tak";';        
              } else {
                $CoDoZapisania .= '"nie";';     
            }
            
            $NaglowekCsv .= 'Zgoda_na_opinie;';
            if ($infc['customers_reviews'] == '1') {
                $CoDoZapisania .= '"tak";';        
              } else {
                $CoDoZapisania .= '"nie";';     
            }    
            
            $NaglowekCsv .= 'Znizka;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_discount']) . '";'; 

            // grupa klienta
            $zapytanieGrupa = "select distinct customers_groups_name from customers_groups where customers_groups_id = '".$infc['customers_groups_id']."'";
            $sqlNazwaGrupa = $db->open_query($zapytanieGrupa);
            $infg = $sqlNazwaGrupa->fetch_assoc();

            $NaglowekCsv .= 'Grupa_klientow;';
            $CoDoZapisania .= '"' . $infg['customers_groups_name'] . '";'; 
            
            $db->close_query($sqlNazwaGrupa);
            unset($infg, $zapytanieGrupa);
            
            // status
            $NaglowekCsv .= 'Status;';
            if ($infc['customers_status'] == '1') {
                $CoDoZapisania .= '"aktywny";';        
              } else {
                $CoDoZapisania .= '"nieaktywny";';     
            }        

            // punkty klienta
            if ( isset($_POST['punkty']) && (int)$_POST['punkty'] ) {
                 $NaglowekCsv .= 'Punkty;';      
                 $CoDoZapisania .= '"' . (float)$infc['customers_shopping_points'] . '";';     
            }
            
            // gosc czy zarejestrowany
            if ( isset($_POST['rodzaj_klientow']) && (int)$_POST['rodzaj_klientow'] == 1 ) {
                 $NaglowekCsv .= 'Rodzaj_klienta;';      
                 $CoDoZapisania .= '"' . ((((int)$infc['customers_guest_account']) == 0) ? 'zarejestrowany' : 'bez rejestracji') . '";';  
            }
            
            $db->close_query($sqlKlient);
            unset($infc, $ZapytanieKlient);

            $ZapytanieKlientAdres = "select * from address_book where address_book_id = '" . $Adres . "' and customers_id = '" . $idKlient . "'";
            $sqlAdres = $db->open_query($ZapytanieKlientAdres);
            $inft = $sqlAdres->fetch_assoc();        
            
            $NaglowekCsv .= 'Firma;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_company'])) ? Funkcje::CzyszczenieTekstu($inft['entry_company']) : '') . '";';        
            
            $NaglowekCsv .= 'Nip;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_nip'])) ? Funkcje::CzyszczenieTekstu($inft['entry_nip']) : '') . '";';           
            
            $NaglowekCsv .= 'REGON;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_regon'])) ? Funkcje::CzyszczenieTekstu($inft['entry_regon']) : '') . '";';     
            
            $NaglowekCsv .= 'Pesel;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_pesel'])) ? Funkcje::CzyszczenieTekstu($inft['entry_pesel']) : '') . '";';            
                
            $NaglowekCsv .= 'Ulica;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_street_address'])) ? Funkcje::CzyszczenieTekstu($inft['entry_street_address']) : '') . '";'; 

            $NaglowekCsv .= 'Kod_pocztowy;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_postcode'])) ? Funkcje::CzyszczenieTekstu($inft['entry_postcode']) : '') . '";';
            
            $NaglowekCsv .= 'Miasto;';
            $CoDoZapisania .= '"' . ((isset($inft['entry_city'])) ? Funkcje::CzyszczenieTekstu($inft['entry_city']) : '') . '";';

            // panstwo
            if ( isset($inft['entry_country_id']) ) {
              
                $zapytanieKraj = "select distinct countries_name from countries_description where countries_id = '".(int)$inft['entry_country_id']."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                $sqlNazwaKraj = $db->open_query($zapytanieKraj);
                $infk = $sqlNazwaKraj->fetch_assoc();

                $NaglowekCsv .= 'Kraj;';
                $CoDoZapisania .= '"' . $infk['countries_name'] . '";'; 
                
                $db->close_query($sqlNazwaKraj);
                unset($infk, $zapytanieKraj);
                
            } else {
              
                $NaglowekCsv .= 'Kraj;';
                $CoDoZapisania .= '"";';       
              
            }
            
            unset($Adres);
            
            // dodatkowe pola klientow
            if ( isset($_POST['pola_klientow']) && (int)$_POST['pola_klientow'] == 1 ) {
              
                $pola_klientow = array();

                $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type, ce.fields_required_status, cei.fields_input_value, cei.fields_name, ce.fields_status, ce.fields_input_type 
                                              FROM customers_extra_fields ce, customers_extra_fields_info cei 
                                             WHERE ce.fields_status = '1' AND cei.fields_id = ce.fields_id AND cei.languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                          ORDER BY ce.fields_order";    
            
                $sql = $db->open_query($dodatkowe_pola_klientow);

                if ( (int)$db->ile_rekordow($sql) > 0  ) {
                  
                    while ( $dodatkowePola = $sql->fetch_assoc() ) {
                      
                        $wartosc_zapytanie = "SELECT value FROM customers_to_extra_fields WHERE customers_id = '" . $idKlient . "' AND fields_id = '" . $dodatkowePola['fields_id'] . "'";
                        $wartosc_info = $db->open_query($wartosc_zapytanie);
                        
                        $wartosc_list = array();
                        
                        if ( (int)$db->ile_rekordow($wartosc_info) > 0  ) {
                             //
                             $infp = $wartosc_info->fetch_assoc();
                             //
                             $wartosc_list = explode("\n", trim((string)$infp['value']));
                             //
                             unset($infp);
                             //
                        }

                        $db->close_query($wartosc_info);
                        unset($wartosc_zapytanie);   

                        $pola_klientow[$dodatkowePola['fields_id']] = array('naglowek' => Funkcje::CzyszczenieTekstu($dodatkowePola['fields_name']),
                                                                            'wartosci' => Funkcje::CzyszczenieTekstu(implode(',',$wartosc_list)));
                      
                    }
                    
                    if ( count($pola_klientow) > 0 ) {
                      
                         foreach ( $pola_klientow as $tmp ) {
                            
                            $NaglowekCsv .= $tmp['naglowek'] . ';';
                            $CoDoZapisania .= '"' . $tmp['wartosci'] . '";'; 
                         
                         }
                    
                    }
                  
                }
                
                $db->close_query($sql);
                unset($dodatkowe_pola_klientow);
                
            }
            
        } else {
            
            $NaglowekCsv .= 'Imie;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_firstname']) . '";';

            $NaglowekCsv .= 'Nazwisko;';
            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_lastname']) . '";';
            
            if ( (int)$_POST['email'] == 1 ) {
            
                 $NaglowekCsv .= 'Adres_email;';
                 $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_email_address']) . '";';
      
            }
            
            if ( (int)$_POST['telefon'] == 1 ) {
              
                 $NaglowekCsv .= 'Telefon;';
                 $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_telephone']) . '";';
               
            }
            
        }

        $CoDoZapisania .= '"KONIEC"' . "\r\n";

        if ($_POST['limit'] == 0) {
            $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
        }          
        
        fwrite($fp, $CoDoZapisania);
        
        // zapisanie danych do pliku
        flock($fp, 3);
        // zamkniecie pliku
        fclose($fp);        
    
    }
        
}
?>