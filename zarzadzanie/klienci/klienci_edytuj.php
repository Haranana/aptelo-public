<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        // grupy newslettera
        $grupyNewslettera = '';
        if ( isset($_POST['biuletyn']) ) {
             //
             if ( isset($_POST['newsletter_grupa']) ) {
                  $grupyNewslettera = ',' . implode(',', (array)$filtr->process($_POST['newsletter_grupa'])) . ',';
             }
             //
        }
        
        // wymuszenie cen netto
        if ( isset($_POST['ceny_netto_wymuszone']) && (int)$_POST['ceny_netto_wymuszone'] == 1 ) {
             $_POST['ceny_netto'] = 1;
        }
    
        $pola = array(
                array('customers_id_private',$filtr->process($_POST['id_klienta_magazyn'])),
                array('customers_nick',((isset($_POST['nick'])) ? $filtr->process($_POST['nick']) : '')),
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_email_address',$filtr->process($_POST['email'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',( isset($_POST['biuletyn']) ? $grupyNewslettera : '' )),
                array('customers_reviews',( isset($_POST['klient_opinie']) ? '1' : '0')),
                array('customers_marketing',( isset($_POST['klient_marketing']) ? '1' : '0')),
                array('customers_discount',((isset($_POST['rabat'])) ? (float)$_POST['rabat'] : 0)),
                array('customers_groups_id',(int)$_POST['grupa']),
                array('service',((isset($_POST['opiekun'])) ? (int)$_POST['opiekun'] : '')),
                array('customers_status',(int)$_POST['aktywnosc']),
                array('customers_dod_info',$filtr->process($_POST['notatki'])),
                array('customers_dod_info_add',$filtr->process($_POST['dod_informacje'])),
                array('vat_netto',((isset($_POST['ceny_netto'])) ? (int)$_POST['ceny_netto'] : 0)),
                array('vat_netto_forced',((isset($_POST['ceny_netto_wymuszone'])) ? (int)$_POST['ceny_netto_wymuszone'] : 0))
        );
        
        if ( PP_KOD_STATUS == 'tak' && isset($_POST['pp_kod']) ) {
           $pola[] = array('pp_code',$filtr->process($_POST['pp_kod']));
           //
           if ( $_POST['pp_kod'] != '' ) {
               //
               $db->delete_query('coupons' , " coupons_name = '" . $filtr->process($_POST['pp_kod']) . "'");  
               //
               $pola_kupon = array(
                             array('coupons_status','1'),
                             array('coupons_name',$filtr->process($_POST['pp_kod'])),
                             array('coupons_description','Kupon PP - klient: ' . $filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko']) . ', ' . $filtr->process($_POST['email'])),
                             array('coupons_discount_type','percent'),   
                             array('coupons_discount_value',(float)PP_KOD_RABAT_KLIENTA),
                             array('coupons_min_order','0'),
                             array('coupons_min_quantity','0'),
                             array('coupons_max_order','0'),
                             array('coupons_max_quantity','0'),                             
                             array('coupons_quantity','99999'),
                             array('coupons_specials',((PP_KOD_PROMOCJE == 'tak') ? '1' : '0')),
                             array('coupons_date_added','now()'),
                             array('coupons_email',''),
                             array('coupons_customers_use',((PP_KOD_ILOSC_UZYC == 'tak') ? '1' : '0')),
                             array('coupons_customers_groups_id',''),
                             array('coupons_date_end','0000-00-00'),
                             array('coupons_date_start','0000-00-00'),
                             array('coupons_exclusion',''),
                             array('coupons_exclusion_id',''),
                             array('coupons_pp_id',(int)$_POST["id"])
               );           
               //
               $GLOBALS['db']->insert_query('coupons' , $pola_kupon);	
               unset($pola_kupon);            
               //
           }
           //
        }

        if (isset($_POST['data_urodzenia'])) {
          $pola[] = array('customers_dob', date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_urodzenia']))));
        }

        if (isset($_POST['plec'])) {
          $pola[] = array('customers_gender',$filtr->process($_POST['plec']));
        }
        
        $db->update_query('customers' , $pola, " customers_id = '".(int)$_POST["id"]."'");	
        unset($pola);

        $pola = array(
                array('customers_info_date_account_last_modified','now()')
        );
        $db->update_query('customers_info' , $pola, " customers_info_id = '".(int)$_POST["id"]."'");	
        unset($pola);

        $pola = array(
                array('entry_company',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nazwa_firmy']) : '')),
                array('entry_nip',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nip_firmy']) : '')),
                array('entry_regon',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['regon_firmy']) : '')),
                array('entry_pesel',(($_POST['osobowosc'] == '1') ? $filtr->process($_POST['pesel']) : '')),
                array('entry_firstname',$filtr->process($_POST['imie'])),
                array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                array('entry_street_address',$filtr->process($_POST['ulica'])),
                array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('entry_city',$filtr->process($_POST['miasto'])),
                array('entry_country_id',$filtr->process($_POST['panstwo'])),
                array('entry_zone_id',(isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : '0'))
        );

        $db->update_query('address_book' , $pola, " customers_id = '".(int)$_POST["id"]."' and address_book_id = '" . (int)$_POST["domyslny_adres"] . "'");	
        unset($pola);


        // dodatkowe pola klientow
        $db->delete_query('customers_to_extra_fields' , " customers_id = '".(int)$_POST["id"]."'");  

        $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type 
                                      FROM customers_extra_fields ce 
                                     WHERE ce.fields_status = '1'";

        $sql = $db->open_query($dodatkowe_pola_klientow);

        if ( (int)$db->ile_rekordow($sql) > 0  ) {

          while ( $dodatkowePola = $sql->fetch_assoc() ) {
            
            $wartosc = '';
            $pola = array();
            
            if ( $dodatkowePola['fields_input_type'] != '3' ) {
              $pola = array(
                      array('customers_id',(int)$_POST["id"]),
                      array('fields_id',(int)$dodatkowePola['fields_id']),
                      array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']]))
              );
            } else {
              if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
                foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
                  $wartosc .= $value . "\n";
                }
                $pola = array(
                        array('customers_id',(int)$_POST["id"]),
                        array('fields_id',(int)$dodatkowePola['fields_id']),
                        array('value',rtrim((string)$filtr->process($wartosc)))
                );
              }

            }

            if ( count($pola) > 0 ) {
              $pola[] = array('language_id', (int)$_POST['language_id']);
              $db->insert_query('customers_to_extra_fields' , $pola);
              unset($pola);
            }
            
          }

        }
        //
        
        // dane do newslettera
        $db->delete_query('subscribers' , " customers_id = '".(int)$_POST["id"]."'");         
        //
        $pola = array(
                array('customers_id',(int)$_POST["id"]),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',( isset($_POST['biuletyn']) ? $grupyNewslettera : '' )),
                array('date_added',( isset($_POST['biuletyn']) ? 'now()' : '0000-00-00')));          

        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola); 
        
        // dodatkowe informacje w panelu klienta
        if ( isset($_POST['ile_pol']) ) {
            //
            $db->delete_query('customers_account_fields' , " customers_id = '".(int)$_POST["id"]."'");       
            $db->delete_query('customers_account_fields_description' , " customers_id = '".(int)$_POST["id"]."'");       
            //
            $ile_jezykow = Funkcje::TablicaJezykow();
            //
            for ($w = 1, $c = count($ile_jezykow); $w <= $c; $w++) {
                //
                for ($x = 1; $x < 99; $x++ ) {
                    //
                    if ( isset($_POST['tytul_' . (($w * 100) + $x)]) && isset($_POST['wartosc_' . (($w * 100) + $x)]) ) {
                         //
                         if ( !empty($_POST['tytul_' . (($w * 100) + $x)]) && !empty($_POST['wartosc_' . (($w * 100) + $x)]) ) {
                            //
                            $pola = array(
                                    array('customers_id',(int)$_POST["id"]),
                                    array('customers_account_fields_type',$filtr->process($_POST['rodzaj_' . (($w * 100) + $x)])));    
                            //
                            $sql = $db->insert_query('customers_account_fields' , $pola);
                            $id_dodanej_pozycji = $db->last_id_query();
                            unset($pola);                             
                            //
                            $pola = array(
                                    array('customers_account_fields_id',(int)$id_dodanej_pozycji),
                                    array('customers_account_fields_name',$filtr->process($_POST['tytul_' . (($w * 100) + $x)])),
                                    array('customers_account_fields_text',$filtr->process($_POST['wartosc_' . (($w * 100) + $x)])),
                                    array('language_id',(int)$ile_jezykow[$w - 1]['id']),
                                    array('customers_id',(int)$_POST["id"]));    
                            //
                            $sql = $db->insert_query('customers_account_fields_description' , $pola);
                            unset($pola, $id_dodanej_pozycji);                             
                            //                            
                         }
                         //
                    }
                    //
                }
                //
            }
            //
            unset($ile_jezykow);
            //
        }
        
        // integracja z salesmanago
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' ) {
             //
             $salesmanago = new SalesManago();
             // sprawdzi czy jest klient                             
             $SmKlient = $salesmanago->CzyJestKlient( array('email' => $filtr->process($_POST['email'])), 'tak' );
             //
             if ( $SmKlient != '' ) {
                  //
                  if ( isset($_POST['biuletyn']) ) {
                       //
                       $dane = array('email' => $filtr->process($_POST['email']),
                                     'newsletter' => 'tak');
                  } else {
                       //
                       $dane = array('email' => $filtr->process($_POST['email']),
                                     'newsletter' => 'nie');
                  }
                  //
                  $dane['smclient'] = $SmKlient;
                  //
                  $sm = $salesmanago->ZapiszKlienta( $dane, false, 'tak' );
                  //
                  unset($dane);
                  //
             }
             //
        }           
        
        // zapis do newslettera w systemie Freshmail
        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' ) {
             //
             $freshMail = new FreshMail();
             //
             if ( isset($_POST['biuletyn']) ) {
                 //
                 $freshMail->ZapiszSubskrybenta( $filtr->process($_POST['email']), 1, INTEGRACJA_DOMYSLNA_LISTA );
                 //
                 if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' && $_POST['klient_gosc'] == 0 ) {
                      //
                      $freshMail->ZapiszSubskrybenta( $filtr->process($_POST['email']), 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                      //
                 }                
                 //
              } else{
                 //
                 $freshMail->UsunSubskrybenta( $filtr->process($_POST['email']) );
                 //
             }
             //
             unset($freshMail);
             //
        }   

        // zapis do newslettera w systemie MailerLite
        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' ) {
             //
             $mailerLite = new MailerLite();
             //
             if ( isset($_POST['biuletyn']) ) {
                 //
                 $mailerLite->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );
                 //
                 if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' && $_POST['klient_gosc'] == 0 ) {
                      //
                      $mailerLite->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                      //
                 }                
                 //
              } else{
                 //
                 $mailerLite->UsunSubskrybenta( $filtr->process($_POST['email']) );
                 //
             }
             //
             unset($mailerLite);
             //
        }  

        // zapis do newslettera w systemie Ecomail
        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' ) {
             //
             $ecomail = new Ecomail();
             //
             if ( isset($_POST['biuletyn']) ) {
                 //
                 $ecomail->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );
                 //
                 if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' && $_POST['klient_gosc'] == 0 ) {
                      //
                      $ecomail->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                      //
                 }                
                 //
              } else{
                 //
                 $ecomail->UsunSubskrybenta( $filtr->process($_POST['email']) );
                 //
             }
             //
             unset($ecomail);
             //
        }  

        // zapis do newslettera w systemie Mailjet
        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' ) {
             //
             $mailjet = new Mailjet();
             //
             if ( isset($_POST['biuletyn']) ) {
                 //
                 $mailjet->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );
                 //
                 if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' && $_POST['klient_gosc'] == 0 ) {
                      //
                      $mailjet->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                      //
                 }                
                 //
              } else{
                 //
                 $mailjet->UsunSubskrybenta( $filtr->process($_POST['email']) );
                 //
             }
             //
             unset($mailjet);
             //
        }  
        
        // zapis do newslettera w systemie Getall
        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' ) {
             //
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             //
             if ( isset($_POST['biuletyn']) ) {
                 //
                 $getall->DodajSubskrybenta( $filtr->process($_POST['email']), $filtr->process($_POST['imie']), INTEGRACJA_GETALL_DOMYSLNA_LISTA );  
                 //
                 if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' && $_POST['klient_gosc'] == 0 ) {
                      //
                      $getall->DodajSubskrybenta( $filtr->process($_POST['email']), $filtr->process($_POST['imie']), INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                      //
                 }                
                 //
              } else{
                 //
                 $getall->UsunSubskrybenta( $filtr->process($_POST['email']) );    
                 //
             }
             //
             unset($getall);
             //
        }         
        
        unset($grupyNewslettera);

        Funkcje::PrzekierowanieURL('klienci.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          <?php

          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }    
                      
          $zapytanie = "select c.customers_id, 
                               c.customers_id_private, 
                               c.language_id, 
                               c.customers_status, 
                               c.customers_dod_info, 
                               c.customers_dod_info_add,
                               c.customers_gender, 
                               c.customers_firstname, 
                               c.customers_lastname, 
                               c.customers_dob, 
                               c.customers_email_address,
                               c.customers_shopping_points,
                               c.customers_guest_account,
                               c.pp_code,
                               c.service,
                               c.vat_netto,
                               c.vat_netto_forced,
                               a.address_book_id,
                               a.entry_company, 
                               a.entry_nip, 
                               a.entry_regon, 
                               a.entry_pesel, 
                               a.entry_street_address, 
                               a.entry_postcode, 
                               a.entry_city, 
                               a.entry_zone_id, 
                               a.entry_country_id, 
                               a.entry_telephone, 
                               c.customers_telephone, 
                               c.customers_fax, 
                               c.customers_newsletter, 
                               c.customers_newsletter_group,
                               c.customers_reviews,
                               c.customers_marketing,
                               c.customers_groups_id, 
                               c.customers_discount, 
                               c.customers_default_address_id, 
                               c.customers_default_shipping_address_id,
                               c.customers_nick,
                               c.fb_id,
                               c.google_id,
                               c.customers_ip,
                               c.customers_black_list
                          from customers c left join address_book a on 
                               c.customers_default_address_id = a.address_book_id
                         where a.customers_id = c.customers_id and c.customers_id = '" . (int)$_GET['id_poz'] . "'";

                             
          $sql = $db->open_query($zapytanie);

          $info = $sql->fetch_assoc();

          ?>

          <script>
          $(document).ready(function() {

              $("#klienciForm").validate({
                rules: {
                  email: {required: true,email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php?user_id=<?php echo $info['customers_id']; ?>&tok=<?php echo Sesje::Token(); ?>"},
                  nick: {remote: "ajax/sprawdz_czy_jest_nick.php?user_id=<?php echo $info['customers_id']; ?>&tok=<?php echo Sesje::Token(); ?>"},
                  imie: {required: true},
                  nazwisko: {required: true},
                  ulica: {required: true},
                  kod_pocztowy: {required: true},
                  miasto: {required: true},
                  nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }},
                  nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik;}},
                  rabat: {range: [-100, 00],number: true},
                  pp_kod: {remote: "ajax/sprawdz_czy_jest_kupon.php?pp_kod=<?php echo $info['pp_code']; ?>&tok=<?php echo Sesje::Token(); ?>"},
                },
                messages: {
                  email: {required: "Pole jest wymagane.",email: "Wpisano niepoprawny adres e-mail.",remote: "Taki adres jest już używany."},
                  nick: {remote: "Taki login jest już używany."},
                  pp_kod: {remote: "Taki kod jest już używany."}
                }
              });

              $('input.datepicker').Zebra_DatePicker({
                  view: 'years',
                  format: 'd-m-Y',
                  inside: false,
                  readonly_element: true,
                  show_clear_date: false
              });
              
              $("#selection").change( function() {
                $("#selectionresult").html('<img src="obrazki/_loader_small.gif">');
                $.ajax({
                    type: "post",
                    data: "data=" + $(this).val(),
                    url: "ajax/wybor_wojewodztwa.php",
                    success: function(msg){
                      if (msg != '') { 
                        $("#selectionresult").html(msg).show(); 
                       } else { 
                        $("#selectionresult").html('<em>Brak</em>'); 
                      }
                    }
                });
              });
              
          });
          </script>

          <form action="klienci/klienci_edytuj.php" method="post" id="klienciForm" class="cmxform"> 

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
              <?php

              if ((int)$db->ile_rekordow($sql) > 0) {
              
                // znizki klienta
                $TblZnizki = Klienci::ZnizkiKlienta($info['customers_id'], $info['customers_discount']);              
                //
                // indywidualne ceny klienta
                $TblCenyKlienta = Klienci::IndywidualneCenyKlienta($info['customers_id'], $info['customers_groups_id']);              
                ?>
                
                <script>
                function szukajTbl(tablica, szuk) {
                  for (var i = 0; i < tablica.length; i++) {
                      if (tablica[i] == szuk) return true;
                  }
                }          

                function pokaz_dane( nr ) {
                  //
                  var pole = $('#ajax_zakladki').val();
                  var sprawdz = pole.split(',');
                  //
                  if ( !szukajTbl(sprawdz, nr) ) {
                      // koszyk klienta
                      if ( nr == '2' ) {
                        var pamietaj_html = $("#koszyk_klienta").html();
                        $('#ekr_preloader').css('display','block');
                        $("#koszyk_klienta").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_koszyk.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#koszyk_klienta").html(data);
                              } else {
                                $("#koszyk_klienta").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }  
                      // schowek klienta
                      if ( nr == '3' ) {
                        var pamietaj_html = $("#schowek_klienta").html();
                        $('#ekr_preloader').css('display','block');
                        $("#schowek_klienta").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_schowek.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#schowek_klienta").html(data);
                              } else {
                                $("#schowek_klienta").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }                     
                      // punkty klienta
                      if ( nr == '5' ) {
                        var pamietaj_html = $("#punkty").html();
                        $('#ekr_preloader').css('display','block');
                        $("#punkty").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_punkty.php?tok=<?php echo Sesje::Token(); ?>', { ogolem: <?php echo "'" . $info['customers_shopping_points'] . "'"; ?>, id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#punkty").html(data);
                              } else {
                                $("#punkty").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }
                      // recenzje
                      if ( nr == '6' ) {
                        var pamietaj_html = $("#recenzje").html();
                        $('#ekr_preloader').css('display','block');
                        $("#recenzje").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_recenzje.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#recenzje").html(data);
                              } else {
                                $("#recenzje").html(pamietaj_html);
                            } 
                            $('#ekr_preloader').delay(100).fadeOut('fast');   
                            pokazChmurki();
                        });
                      }
                      // statystyki
                      if ( nr == '7' ) {
                        var pamietaj_html = $("#statystyki").html();
                        $('#ekr_preloader').css('display','block');
                        $("#statystyki").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_statystyki.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#statystyki").html(data);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                        });
                      }
                      // lista zamowien
                      if ( nr == '9' ) {
                        var pamietaj_html = $("#zamowienia").html();
                        $('#ekr_preloader').css('display','block');
                        $("#zamowienia").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_zamowienia.php?tok=<?php echo Sesje::Token(); ?>', { produkt: '<?php echo ((isset($_GET['produkt']) && trim((string)$_GET['produkt']) != '') ? $_GET['produkt'] : ''); ?>', id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#zamowienia").html(data);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }                      
                      $('#ajax_zakladki').val( $('#ajax_zakladki').val() + ',' + nr );
                      // zapisane koszyki
                      if ( nr == '10' ) {
                        var pamietaj_html = $("#koszyki_zapisane").html();
                        $('#ekr_preloader').css('display','block');
                        $("#koszyki_zapisane").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_zapisane_koszyki.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#koszyki_zapisane").html(data);
                              } else {
                                $("#koszyki_zapisane").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }  
                      // zapisane koszyki
                      if ( nr == '12' ) {
                        var pamietaj_html = $("#pola_panel_klienta").html();
                        $('#ekr_preloader').css('display','block');
                        $("#pola_panel_klienta").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_dodatkowe_informacje.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#pola_panel_klienta").html(data);
                              } else {
                                $("#pola_panel_klienta").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }                        
                  }
                };     

                function pokazGrupyNewsletter() {
                    //
                    if ($('#biuletyn').prop('checked') == true) {
                        $('#grupy_newslettera').slideDown();
                        $('#data_newslettera').slideDown();
                      } else {
                        $('#grupy_newslettera').slideUp();
                        $('#data_newslettera').slideUp();
                    }
                    //
                }
                
                function podgladZapisanegoKoszyka(id_koszyka) {
                    $.colorbox( { href:"ajax/koszyk_zapisany_klienta.php?id_koszyka=" + id_koszyka, maxHeight:'90%', open:true, initialWidth:50, initialHeight:50, onComplete : function() { $(this).colorbox.resize(); } } ); 
                }       

                function zmienOsobowosc(akcja) {
                  //
                  if ( akcja == 1 ) {
                       $('#pesel').slideUp();
                       $('#firma').slideDown();
                       $('#nip').slideDown();
                       $('#regon').slideDown();
                  }
                  if ( akcja == 0 ) {
                       $('#pesel').slideDown();
                       $('#firma').slideUp();
                       $('#nip').slideUp();
                       $('#regon').slideUp();
                  }
                  //
                }                
                </script>
                
                <input type="hidden" id="ajax_zakladki" value="" />
          
                <input type="hidden" name="akcja" value="zapisz" />

                <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                <input type="hidden" name="language_id" value="<?php echo (int)$info['language_id']; ?>" />
                <input type="hidden" name="klient_gosc" value="<?php echo $info['customers_guest_account']; ?>" />
                <input type="hidden" name="domyslny_adres" value="<?php echo $info['address_book_id']; ?>" />

                <div id="ZakladkiEdycji">
                
                    <div id="LeweZakladki">
                    
                        <a href="javascript:gold_tabs_horiz('0','')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1','')" class="a_href_info_zakl" id="zakl_link_1">Dane adresowe</a> 
                        <a href="javascript:gold_tabs_horiz('9','');pokaz_dane('9')" class="a_href_info_zakl" id="zakl_link_9">Lista zamówień [<?php echo (int)Klienci::pokazIloscZamowienKlienta($info['customers_id']); ?>]</a> 
                        
                        <?php
                        // jezeli klient nie jest gosciem
                        if ( $info['customers_guest_account'] == '0' ) {
                        ?>
                        
                            <a href="javascript:gold_tabs_horiz('2','');pokaz_dane('2')" class="a_href_info_zakl" id="zakl_link_2">Zawartość koszyka [<?php echo Klienci::pokazIloscProduktowKoszyka($info['customers_id'], '0'); ?>]</a>
                            <a href="javascript:gold_tabs_horiz('10','');pokaz_dane('10')" class="a_href_info_zakl" id="zakl_link_10">Zapisane koszyki [<?php echo Klienci::pokazIloscZapisanychKoszykow($info['customers_id'], '0'); ?>]</a>
                            <a href="javascript:gold_tabs_horiz('3','');pokaz_dane('3')" class="a_href_info_zakl" id="zakl_link_3">Zawartość schowka [<?php echo Klienci::pokazIloscProduktowSchowka($info['customers_id'], '0'); ?>]</a>
                            <a href="javascript:gold_tabs_horiz('4','')" class="a_href_info_zakl" id="zakl_link_4">Zniżki klienta [<?php echo count($TblZnizki); ?>]</a>
                            <a href="javascript:gold_tabs_horiz('11','')" class="a_href_info_zakl" id="zakl_link_11">Indywidualne ceny produktów [<?php echo count($TblCenyKlienta); ?>]</a>                            
                            <?php
                            // oblicza ile jest pozycji w tabeli punktow
                            $zapytanie_punkty = "SELECT customers_id FROM customers_points WHERE customers_id = '" . $info['customers_id'] . "'";
                            $sql_punkty = $db->open_query($zapytanie_punkty);
                            $ile_poz = (int)$db->ile_rekordow($sql_punkty);
                            ?>                        
                            <a href="javascript:gold_tabs_horiz('5','');pokaz_dane('5')" class="a_href_info_zakl" id="zakl_link_5">System punktów [<?php echo $ile_poz; ?>]</a>
                            <?php
                            $db->close_query($sql_punkty);
                            unset($ile_poz, $zapytanie_punkty, $sql_punkty);
                            //
                            // oblicza ile jest pozycji w tabeli punktow
                            $zapytanie_recenzje = "SELECT reviews_id FROM reviews WHERE customers_id = '" . $info['customers_id'] . "'";
                            $sql_recenzje = $db->open_query($zapytanie_recenzje);
                            $ile_poz = (int)$db->ile_rekordow($sql_recenzje);
                            ?>                        
                            <a href="javascript:gold_tabs_horiz('6','');pokaz_dane('6')" class="a_href_info_zakl" id="zakl_link_6">Recenzje [<?php echo $ile_poz; ?>]</a>
                            <?php
                            $db->close_query($sql_recenzje);
                            unset($ile_poz, $zapytanie_recenzje, $sql_recenzje);
                            //
                            // oblicza ile jest pozycji w tabeli dodatkowych pol w panelu klienta
                            $zapytanie_informacje = "SELECT customers_account_fields_id FROM customers_account_fields WHERE customers_id = '" . $info['customers_id'] . "'";
                            $sql_informacje = $db->open_query($zapytanie_informacje);
                            $ile_poz = (int)$db->ile_rekordow($sql_informacje);                            
                            ?>
                            <a href="javascript:gold_tabs_horiz('12','');pokaz_dane('12')" class="a_href_info_zakl" id="zakl_link_12">Dodatkowe informacje <br /> w Panelu klienta [<?php echo $ile_poz; ?>]</a>
                            <?php
                            $db->close_query($sql_informacje);
                            unset($ile_poz, $zapytanie_informacje, $sql_informacje);   
                            
                        }
                        ?>      
                        
                        <a href="javascript:gold_tabs_horiz('7','');pokaz_dane('7')" class="a_href_info_zakl" id="zakl_link_7">Statystyka</a>                        
                        <a href="javascript:gold_tabs_horiz('8','')" class="a_href_info_zakl" id="zakl_link_8">Uwagi / informacje</a>
                    
                    </div>
                    
                    <?php $licznik_zakladek = 0; ?>

                    <div id="PrawaStrona">
                    
                        <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                    
                        <div id="zakl_id_0" style="display:none;">
                        
                          <?php if ( $info['customers_black_list'] == '1' ) { ?>
                          
                              <div class="KlientCzarnaLista ObramowanieTabeli">
                                  
                                  <span>Klient znajduje się na "Czarnej liście"</span>
                                  
                              </div>
                          
                          <?php } ?>
                        
                          <?php if ( $info['customers_guest_account'] == '0' ) { ?>
                        
                          <p>
                            <label>Status konta:</label>
                            <input type="radio" value="1" name="aktywnosc" <?php echo ( $info['customers_status'] == '1' ? 'checked="checked"' : '' ); ?> id="aktywnosc_tak" /><label class="OpisFor" for="aktywnosc_tak">aktywne</label>
                            <input type="radio" value="0" name="aktywnosc" <?php echo ( $info['customers_status'] == '0' ? 'checked="checked"' : '' ); ?> id="aktywnosc_nie" /><label class="OpisFor" for="aktywnosc_nie">nieaktywne</label>
                          </p> 
                          
                          <?php } else { ?>
                          
                          <p>
                            <span class="KlientGosc">Ten klient <b>nie jest zarejestrowany</b> - konto tylko do realizacji zamówienia.</span>
                            <input type="hidden" value="1" name="aktywnosc" />
                          </p>
                          
                          <?php } ?>

                          <p>
                            <label class="required" for="email">Adres e-mail:</label>
                            <input type="text" name="email" id="email" size="53" value="<?php echo $info['customers_email_address']; ?>" /><em class="TipIkona"><b>Adres wykorzystywany do logowania oraz do korespondencji</b></em>
                          </p>      

                          <?php if ( $info['customers_guest_account'] == '0' ) { ?>
                                    
                          <p>
                            <label for="nick">Login:</label>
                            <input type="text" name="nick" id="nick" size="53" value="<?php echo $info['customers_nick']; ?>" /><em class="TipIkona"><b>Może być używany do logowania zamiennie z wprowadzonym adresem e-mail</b></em>
                          </p>                             
                          
                          <?php } ?>

                          <p>
                            <label class="required" for="imie">Imię:</label>
                            <input type="text" name="imie" id="imie" size="53" value="<?php echo Funkcje::formatujTekstInput($info['customers_firstname']); ?>" />
                          </p> 

                          <p>
                            <label class="required" for="nazwisko">Nazwisko:</label>
                            <input type="text" name="nazwisko" id="nazwisko" size="53" value="<?php echo Funkcje::formatujTekstInput($info['customers_lastname']); ?>" />
                          </p>

                          <?php
                          if ( KLIENT_POKAZ_PLEC == 'tak' ) {
                            ?>
                            <p>
                              <label>Płeć:</label>
                              <input type="radio" value="f" id="plec_k" name="plec" <?php echo ( $info['customers_gender'] == 'f' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="plec_k">kobieta</label>
                              <input type="radio" value="m" id="plec_m" name="plec" <?php echo ( $info['customers_gender'] == 'm' || $info['customers_gender'] == '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="plec_m">mężczyzna</label>
                            </p> 
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_DATE_URODZENIA == 'tak' ) {
                            ?>
                            <p>
                              <label for="data_urodzenia">Data urodzenia:</label>
                              <input type="text" name="data_urodzenia" id="data_urodzenia" size="30" value="<?php echo ((Funkcje::czyNiePuste($info['customers_dob'])) ? date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['customers_dob'])) : ''); ?>" class="datepicker" />
                            </p> 
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                            ?>
                          <p>
                            <label for="telefon">Numer telefonu:</label>
                            <input type="text" name="telefon" id="telefon" size="32" value="<?php echo $info['customers_telephone']; ?>" />
                          </p>
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_FAX == 'tak' ) {
                            ?>
                            <p>
                              <label for="fax">Numer faxu:</label>
                              <input type="text" name="fax" id="fax" size="32" value="<?php echo $info['customers_fax']; ?>" />
                            </p>
                            <?php
                          }
                          ?>
                          
                          <?php if ( $info['customers_guest_account'] == '0' ) { ?>

                          <p>
                            <label for="grupa">Grupa klientów:</label>
                            <?php
                            $tablica = Klienci::ListaGrupKlientow(false);
                            echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['customers_groups_id'], 'id="grupa"' ); ?>
                          </p>

                          <p>
                            <label for="rabat">Indywidualny rabat [%]:</label>
                            <input type="text" name="rabat" id="rabat" value="<?php echo $info['customers_discount']; ?>" size="5" /><em class="TipIkona"><b>liczba z zakresu od -100 do 0</b></em>
                          </p>
                          
                          <?php if ( NETTO_DLA_UE == 'tak' ) { ?>
                          
                          <p>
                            <label for="ceny_netto">Ceny netto:</label>
                            <input type="checkbox" value="1" name="ceny_netto" id="ceny_netto" <?php echo ( ($info['vat_netto'] == '1' || $info['vat_netto_forced'] == '1') ? 'checked="checked"' : '' ); ?> />
                            <label class="OpisForPustyLabel" for="ceny_netto"></label>
                            <div class="maleInfo" style="margin:0 0 0 25px">Klient będzie miał ceny netto w zamówieniach - gdzie adres dostawy będzie poza granice kraju klienta</div>
                          </p> 

                          <p>
                            <label for="ceny_netto_wymuszone">Ceny netto na stałe:</label>
                            <input type="checkbox" value="1" name="ceny_netto_wymuszone" id="ceny_netto_wymuszone" <?php echo ( $info['vat_netto_forced'] == '1' ? 'checked="checked"' : '' ); ?> />
                            <label class="OpisForPustyLabel" for="ceny_netto_wymuszone"></label><em class="TipIkona"><b>Klient będzie miał zawsze ceny netto - w każdym zamówieniu składanym przez sklep</b></em>
                            <div class="maleInfo" style="margin:0 0 0 25px">Klient będzie miał ceny netto w każdym zamówieniu - niezależnie od adresu dostawy</div>
                          </p>   

                          <?php } ?>

                          <?php if ( INTEGRACJA_FB_LOGOWANIE_WLACZONY == 'tak' ) { ?>
                          
                          <p>
                            <label for="rabat">Konto powiązane z Facebook:</label>
                            <?php
                            if ( $info['fb_id'] != '' && $info['fb_id'] != '0' ) {
                                 echo '<em class="TipChmurka"><b>To konto jest połączone z Facebook. Klient może logować się za pośrednictwem Facebook.</b><img src="obrazki/facebook.png" alt="Facebook" /></em>';
                              } else {
                                 echo '<em class="TipChmurka"><b>To konto nie jest połączone z Facebook. Klient nie może logować się za pośrednictwem Facebook.</b><img style="opacity:0.4;filter:alpha(opacity=40)" src="obrazki/facebook.png" alt="Facebook" /></em>';
                            }
                            ?>
                          </p>

                          <?php } ?>      

                          <?php if ( INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY == 'tak' ) { ?>
                          
                          <p>
                            <label for="rabat">Konto powiązane z Google+:</label>
                            <?php
                            if ( $info['google_id'] != '' && $info['google_id'] != '0' ) {
                                 echo '<em class="TipChmurka"><b>To konto jest połączone z Google+. Klient może logować się za pośrednictwem Google+.</b><img src="obrazki/google.png" alt="Google+" /></em>';
                              } else {
                                 echo '<em class="TipChmurka"><b>To konto nie jest połączone z Google+. Klient nie może logować się za pośrednictwem Google+.</b><img style="opacity:0.4;filter:alpha(opacity=40)" src="obrazki/google.png" alt="Google+" /></em>';
                            }
                            ?>
                          </p>                              

                          <?php } ?>
                          
                          <?php } else { ?>
                            <input type="hidden" name="grupa" id="grupa" value="1" size="5" />
                          <?php } ?>
                          
                          <p>
                            <label for="id_klienta_magazyn">Id klienta w programie magazynowym:</label>
                            <input type="text" name="id_klienta_magazyn" id="id_klienta_magazyn" size="20" value="<?php echo $info['customers_id_private']; ?>" />
                          </p>                          
                          
                          <?php if ( !empty($info['customers_ip']) ) { ?>
                          
                          <p>
                            <label for="rabat">Adres IP klienta:</label>
                            <?php echo $info['customers_ip']; ?>
                          </p>    

                          <?php } ?>   

                          <?php
                          // jezeli klient nie jest gosciem
                          if ( $info['customers_guest_account'] == '0' ) {
                          ?>
                        
                          <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                          <p>
                            <label for="grupa">Opiekun klienta:</label>
                            <?php
                            // pobieranie informacji od uzytkownikach
                            $lista_uzytkownikow = array(array('id' => 0, 'text' => 'Nie przypisany ...'));
                            $zapytanie_uzytkownicy = "SELECT * FROM admin ORDER BY admin_lastname, admin_firstname";
                            $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                            //
                            while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) { 
                              $lista_uzytkownikow[] = array('id' => $uzytkownicy['admin_id'], 'text' => $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname']);
                            }
                            $db->close_query($sql_uzytkownicy); 
                            unset($zapytanie_uzytkownicy, $uzytkownicy);    
                            //     
                            echo Funkcje::RozwijaneMenu('opiekun', $lista_uzytkownikow, $info['service'], 'id="opiekun"' ); 
                            unset($lista_uzytkownikow);
                            ?>
                          </p>     
                            
                          <?php } ?>

                          <?php if ( PP_KOD_STATUS == 'tak' && $info['customers_guest_account'] == '0' ) { ?>
                          
                          <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                          
                          <p>
                            <label for="pp_kod">Kod do programu partnerskiego:</label>
                            <?php
                            // sprawdzi czy juz nie ma kodu dla tego klienta
                            $zapytKupon = "select coupons_name from coupons where coupons_pp_id = '" . $info['customers_id'] . "'";
                            $sqlKupon = $db->open_query($zapytKupon);
                            //
                            if ( (int)$db->ile_rekordow($sqlKupon) > 0 ) {
                            ?>
                            <input type="text" name="pp_kod" id="pp_kod" size="30" value="<?php echo $info['pp_code']; ?>" disabled="disabled" /> <em class="TipIkona"><b>Dla tego klienta jest już dodany kod i wygenerowany kupon rabatowy</b></em>
                            <?php
                            } else {
                            ?>
                            <input type="text" name="pp_kod" id="pp_kod" size="30" value="<?php echo $info['pp_code']; ?>" />
                            <?php 
                            } 
                            $db->close_query($sqlKupon);
                            ?>
                          </p>                             
                          
                          <?php } ?>
                          
                          <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                          <p>
                            <label for="biuletyn">Subskrypcja biuletynu:</label>
                            <input type="checkbox" value="1" name="biuletyn" onclick="pokazGrupyNewsletter()" id="biuletyn" <?php echo ( $info['customers_newsletter'] == '1' ? 'checked="checked"' : '' ); ?> />
                            <label class="OpisForPustyLabel" for="biuletyn"></label>
                          </p>  
                          
                          <?php 
                          if ( $info['customers_newsletter'] == '1' ) {
                          
                              $zapytanie_newsletter = "SELECT subscribers_email_address, date_added FROM subscribers WHERE customers_id = '" . $info['customers_id'] . "'";
                              $sql_newsletter = $db->open_query($zapytanie_newsletter);
                              //
                              if ((int)$db->ile_rekordow($sql_newsletter) > 0) {
                                  //                              
                                  $newsletter = $sql_newsletter->fetch_assoc();
                                  //
                                  echo '<p id="data_newslettera"><label>&nbsp;</label><span class="DataZapisania">Data zapisania: ' . date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($newsletter['date_added'])) . '</span>';
                                  //
                                  $db->close_query($sql_newsletter);
                              }                                  
                          
                          } 
                          ?>

                          <?php
                          $TablicaGrup = Newsletter::GrupyNewslettera();
                          if ( count($TablicaGrup) > 0 ) {
                          ?>
                          <div id="grupy_newslettera" class="GrupyNewslettera" <?php echo ( $info['customers_newsletter'] == '0' ? 'style="display:none"' : '' ); ?> >
                            <table>
                                <tr>
                                    <td><label>Przypisany do grup newslettera:</label></td>   
                                    <td>
                                    
                                    <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie klient będzie przypisany do wszystkich grup</span>
                                    
                                    <?php
                                    foreach ($TablicaGrup as $Grupa) {
                                        //
                                        echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" id="newsletter_grupa_' . $Grupa['id'] . '" ' . ((in_array((string)$Grupa['id'], explode(',', (string)$info['customers_newsletter_group']))) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="newsletter_grupa_' . $Grupa['id'] . '">' . $Grupa['text'] . '</label><br />';
                                        //
                                    }
                                    ?>
                                    </td>
                                </tr>
                            </table>
                          </div>
                          <?php
                          unset($TablicaGrup);
                          }
                          ?>

                          <p>
                            <label for="klient_opinie">Zgoda na zbieranie opinii o sklepie i produktach:</label>
                            <input type="checkbox" value="1" name="klient_opinie" id="klient_opinie" <?php echo ( $info['customers_reviews'] == '1' ? 'checked="checked"' : '' ); ?> />
                            <label class="OpisForPustyLabel" for="klient_opinie"></label>
                          </p>                             
                                            
                          <p>
                            <label for="klient_marketing">Zgoda na działania marketingowe:</label>
                            <input type="checkbox" value="1" name="klient_marketing" id="klient_marketing" <?php echo ( $info['customers_marketing'] == '1' ? 'checked="checked"' : '' ); ?> />
                            <label class="OpisForPustyLabel" for="klient_marketing"></label>
                          </p>                             

                          <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                          <div style="margin-top:10px;margin-left:10px;">
                          <?php echo Klienci::pokazDodatkowePolaKlientow($info['customers_id'],$info['language_id']); ?>
                          </div>

                        </div>
                        
                        <?php // ********************************************* KSIAZKA ADRESOWA *************************************************** ?>
                        
                        <div id="zakl_id_1" style="display:none;">

                          <p>
                            <label>Osobowość prawna:</label>
                            <input type="radio" value="1" name="osobowosc" id="osobowosc_osoba" onclick="zmienOsobowosc(0)" <?php echo ( $info['entry_nip'] == '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="osobowosc_osoba">osoba fizyczna</label>
                            <input type="radio" value="0" name="osobowosc" id="osobowosc_firma" onclick="zmienOsobowosc(1)" <?php echo ( $info['entry_nip'] != '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="osobowosc_firma">firma</label>
                          </p> 

                          <p id="pesel" <?php echo ( $info['entry_nip'] == '' ? '' : 'style="display:none;"' ); ?> >
                            <label for="pesel_val">Numer PESEL:</label>
                            <input type="text" name="pesel" id="pesel_val" value="<?php echo $info['entry_pesel']; ?>" size="32" />
                          </p>

                          <p id="firma" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> >
                            <label class="required" for="nazwa_firmy">Nazwa firmy:</label>
                            <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo Funkcje::formatujTekstInput($info['entry_company']); ?>" size="53" />
                          </p>

                          <p id="nip" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> class="required">
                            <label class="required" for="nip_firmy">Numer NIP:</label>
                            <input type="text" name="nip_firmy" id="nip_firmy" value="<?php echo $info['entry_nip']; ?>" size="32" />
                          </p>
                          
                          <p id="regon" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?>>
                            <label for="regon_firmy">REGON:</label>
                            <input type="text" name="regon_firmy" id="regon_firmy" value="<?php echo $info['entry_regon']; ?>" size="15" />
                          </p>                           

                          <p>
                            <label class="required" for="ulica">Ulica i numer domu:</label>
                            <input type="text" name="ulica" id="ulica" size="53" value="<?php echo Funkcje::formatujTekstInput($info['entry_street_address']); ?>" />
                          </p>                          
                                   
                          <p>
                            <label class="required" for="kod_pocztowy">Kod pocztowy:</label>
                            <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="<?php echo $info['entry_postcode']; ?>" />
                          </p> 

                          <p>
                            <label class="required" for="miasto">Miejscowość:</label>
                            <input type="text" name="miasto" id="miasto" size="53" value="<?php echo Funkcje::formatujTekstInput($info['entry_city']); ?>" />
                          </p>

                          <p>
                            <label class="required" for="selection">Kraj:</label>
                            <?php
                            $tablicaPanstw = Klienci::ListaPanstw();
                            echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, $info['entry_country_id'], 'id="selection"'); ?>
                          </p>

                          <?php
                          if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                            ?>
                            <p>
                              <label>Województwo:</label>
                              <?php
                              $tablicaWojewodztw = Klienci::ListaWojewodztw($info['entry_country_id']);
                              echo '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, $info['entry_zone_id']).'</span>';
                              ?>
                            </p>
                            <?php
                          }

                          $zapytanie_adresy = "SELECT c.customers_id, 
                                                      a.address_book_id, 
                                                      a.entry_company, 
                                                      a.entry_firstname, 
                                                      a.entry_lastname, 
                                                      a.entry_street_address, 
                                                      a.entry_postcode, 
                                                      a.entry_city, 
                                                      a.entry_country_id, 
                                                      a.entry_zone_id,
                                                      a.entry_telephone
                                                 FROM customers c 
                                            LEFT JOIN address_book a ON a.customers_id = c.customers_id
                                                WHERE c.customers_id = '" . $info['customers_id'] . "'";
                          
                          $sql_adresy = $db->open_query($zapytanie_adresy); 
                          
                          echo '<strong class="DodatkoweAdresy">Dodatkowe adresy dostawy</strong>';
                          
                          echo '<a href="klienci/klienci_adres_dodaj.php?id_poz=' . $info['customers_id'] . '&zakladka=1" class="dodaj" style="margin:5px 0px 20px 25px">dodaj nowy adres</a>';

                          if ((int)$db->ile_rekordow($sql_adresy) > 1) {
                            
                              while ( $infa = $sql_adresy->fetch_assoc() ) {
                                
                                  ?>
                                    
                                  <div class="AdresDodatkowy">
                                  
                                      <?php if ( (int)$info['customers_default_address_id'] != (int)$infa['address_book_id'] ) { ?>
                                  
                                      <a class="TipChmurka" href="klienci/klienci_adres_edytuj.php?id_poz=<?php echo $info['customers_id']; ?>&id=<?php echo $infa['address_book_id']; ?>&zakladka=1"><b>Edytuj adres</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>
                                      <a class="TipChmurka" href="klienci/klienci_adres_usun.php?id_poz=<?php echo $info['customers_id']; ?>&id=<?php echo $infa['address_book_id']; ?>&zakladka=1"><b>Usuń adres</b><img src="obrazki/kasuj.png" alt="Usun" /></a>
                                  
                                      <?php } ?>
                                  
                                      <ul>
                                          <?php if ( $infa['entry_company'] != '' ) echo '<li style="font-weight:bold;color:#098206">' . $infa['entry_company'] . '</li>'; ?>
                                          <li><?php echo $infa['entry_firstname'] . ' ' . $infa['entry_lastname']; ?></li>                                          
                                          <li><?php echo $infa['entry_street_address']; ?></li>
                                          <li><?php echo $infa['entry_postcode'] . ' ' . $infa['entry_city']; ?></li>
                                          <li><?php echo Klienci::pokazNazwePanstwa($infa['entry_country_id']); ?></li>
                                          
                                          <?php
                                          if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' && $infa['entry_zone_id'] != '' ) {
                                              ?>
                                              <li><?php echo Klienci::pokazNazweWojewodztwa($infa['entry_zone_id']); ?></li>
                                              <?php
                                          }
                                          if ( KLIENT_POKAZ_TELEFON == 'tak' && $infa['entry_telephone'] != '' ) {
                                              ?>
                                              <li>Tel: <?php echo $infa['entry_telephone']; ?></li>
                                              <?php
                                          }                                            
                                          ?>    

                                          <?php if ( (int)$info['customers_default_shipping_address_id'] == (int)$infa['address_book_id'] ) { ?>
                                          
                                          <li><div class="maleInfo" style="margin-left:0">Ten adres dostawy jest ustawiony jako domyślny</div></li>
                                          
                                          <?php } else { ?>
                                          
                                          <li><a href="klienci/klienci_zmiana_adresu_dostawy.php?id_poz=<?php echo $info['customers_id']; ?>&id=<?php echo $infa['address_book_id']; ?>&zakladka=1" class="przyciskNon" style="margin-left:0">Ustaw adres jako domyślny</a></li>
                                          
                                          <?php } ?>
                                          
                                      </ul>
                                      
                                      <div class="cl"></div>
                                      
                                  </div> 

                                  <?php
                                  
                              }
                          
                          }
                          $db->close_query($sql_adresy);
                          unset($zapytanie_adresy, $infa);                          
                          ?>

                        </div>
                        
                        <?php
                        if ( $info['customers_guest_account'] == '0' ) {
                        ?>
                        
                        <?php // ********************************************* KOSZYK *************************************************** ?>
                        
                        <div id="zakl_id_2" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="koszyk_klienta">
                                <span class="maleInfo">Klient nie ma nic w koszyku</span>
                            </div>

                        </div>   

                        <?php // ********************************************* ZAPISANE KOSZYKI *************************************************** ?>
                        
                        <div id="zakl_id_10" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="koszyki_zapisane">
                                <span class="maleInfo">Klient nie ma zapisanych koszyków</span>
                            </div>

                        </div>                          
                        
                        <?php // ********************************************* SCHOWEK *************************************************** ?>
                        
                        <div id="zakl_id_3" style="display:none;" class="ZaklCent">

                            <div class="pozycja_edytowana" id="schowek_klienta">
                                <span class="maleInfo">Klient nie ma nic w schowku</span>
                            </div>

                        </div>     

                        <?php // ********************************************* ZNIZKI KLIENTA *************************************************** ?>
                        
                        <div id="zakl_id_4" style="display:none;">
                        
                            <div class="pozycja_edytowana">

                            <?php
                            //
                            if (count($TblZnizki) > 0) {
                                //
                                ?>
                                <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
                                
                                    <table class="listing_tbl">
                                    
                                    <tr class="div_naglowek">
                                      <td style="text-align:center">Typ</td>
                                      <td style="text-align:center">Nazwa</td>
                                      <td style="text-align:right">Wartość</td>
                                    </tr> 
                                    
                                    <?php
                                    //
                                    for ($j = 0, $cj = count($TblZnizki); $j < $cj; $j++) {                                   
                                        if ($TblZnizki[$j][0] == $TblZnizki[$j][1]) {
                                            //
                                            echo '<tr class="pozycja_off"><td class="TypZnizki">-</td><td><b>' . $TblZnizki[$j][0] . '</b></td><td class="ZnizkiKlienta">' . $TblZnizki[$j][2] . ' %</td></tr>';
                                            //
                                          } else {
                                            //
                                            echo '<tr class="pozycja_off"><td class="TypZnizki">' . $TblZnizki[$j][0] . '</td><td><b>' . $TblZnizki[$j][1] . '</b></td><td class="ZnizkiKlienta">' . $TblZnizki[$j][2] . ' %</td></tr>';
                                            //
                                        }
                                    }
                                    //
                                    ?>
                                    
                                    </table>
                                    
                                </div>
                                
                                <?php
                                
                            } else {
         
                                echo '<div class="pozycja_edytowana"><span class="maleInfo" style="margin:0px">Brak zniżek przypisanych do konta klienta</span></div>';
  
                            }
                            unset($TblZnizki);
                            ?>
                            
                            </div>
                            
                        </div>  
                        
                        <?php // ********************************************* INDYWIDUALNE CENY KLIENTA *************************************************** ?>
                        
                        <div id="zakl_id_11" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana">
                            
                            <div style="margin-bottom:10px">
                                 <a class="dodaj" href="klienci/indywidualne_ceny_produktow_dodaj.php<?php echo '?id_klient=' . $info['customers_id']; ?>">dodaj nową pozycję</a>
                            </div>

                            <?php
                            //
                            if (count($TblCenyKlienta) > 0) {
                                //
                                ?>
                                <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
                                
                                    <table class="listing_tbl">
                                    
                                    <tr class="div_naglowek">
                                      <td>Id</td>
                                      <td class="ListingSchowajMobile">Foto</td>
                                      <td>Produkt</td>
                                      <td>Cena dla ...</td>
                                      <td class="ListingSchowajMobile">Cena standardowa</td>
                                      <td>Cena indywidualna</td>
                                      <td>Status</td>
                                      <td></td>
                                    </tr> 
                                    
                                    <?php
                                    //
                                    foreach ( $TblCenyKlienta as $CenaIndywidualna) {
                                        //
                                        echo '<tr class="pozycja_off">';
                                        echo '<td>' . $CenaIndywidualna['id_produktu'] . '</td>';
                                        echo '<td class="ListingSchowajMobile">' . Funkcje::pokazObrazek($CenaIndywidualna['foto'], $CenaIndywidualna['nazwa_produktu'], '40', '40') . '</td>';
                                        echo '<td style="text-align:left"><a href="produkty/produkty_edytuj.php?id_poz=' . $CenaIndywidualna['id_produktu'] . '"><b>' . $CenaIndywidualna['nazwa_produktu'] . '</b></a></td>';
                                        echo '<td>';
                                        
                                        if ( $CenaIndywidualna['id_grupy'] > 0 ) {
                                             echo 'Grupy klientów';
                                          } else {
                                             echo 'Indywidualna klienta';
                                          }
                                        
                                        echo '</td>';                                        
                                        
                                        $status_promocja = '';
                                        if ( ((FunkcjeWlasnePHP::my_strtotime($CenaIndywidualna['specials_date']) > time() && $CenaIndywidualna['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($CenaIndywidualna['specials_date_end']) < time() && $CenaIndywidualna['specials_date_end'] != '0000-00-00 00:00:00') ) && $CenaIndywidualna['specials_status'] == '1' ) {                             
                                            $status_promocja = '<div class="wylaczonaPromocja TipChmurka"><b>Produkt nie jest wyświetlany jako promocja ze względu na datę rozpoczęcia lub zakończenia promocji</b></div>';
                                        }                                         
                                        
                                        echo '<td class="ListingSchowajMobile">' . $status_promocja . (((float)$CenaIndywidualna['products_old_price'] == 0) ? '' : '<div class="cena_promocyjna">' . $waluty->FormatujCene($CenaIndywidualna['products_old_price'], false, $CenaIndywidualna['products_currencies_id']) . '</div>') . 
                                             '<div class="cena">'.$waluty->FormatujCene($CenaIndywidualna['products_price_tax'], false, $CenaIndywidualna['products_currencies_id']).'</div>'.
                                             (($CenaIndywidualna['products_points_only'] == 1) ? '<div class="TylkoPkt">' . $CenaIndywidualna['products_points_value'] . ' pkt + ' . $waluty->FormatujCene($CenaIndywidualna['products_points_value_money'],false) . '</div>' : ''); 
                                                                             
                                        echo '<td><div class="cena">' . $waluty->FormatujCene($CenaIndywidualna['cena_brutto'], false, $CenaIndywidualna['products_currencies_id']) . '</div></td>';
                                        
                                        echo '<td>';
 
                                        if ($CenaIndywidualna['status'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten produkt jest aktywny" />'; $tekst_opisu = 'Ten produkt jest aktywny'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Ten produkt jest nieaktywny" />'; $tekst_opisu = 'Ten produkt jest nieaktywny'; }
                                        echo '<em class="TipChmurka">'.$obraz.'<b>'.$tekst_opisu.'</b></em>';
                
                                        $zmienne_do_przekazania = '?id_poz=' . $CenaIndywidualna['id'] . '&id_klient=' . $info['customers_id'];
                                        
                                        echo '</td>';
                                        echo '<td class="rg_right IkonyPionowo">';
                                        echo '<a class="TipChmurka" href="klienci/indywidualne_ceny_produktow_edytuj.php' . $zmienne_do_przekazania . '"><b>Edytuj cenę produktu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                        echo '<a class="TipChmurka" href="klienci/indywidualne_ceny_produktow_usun.php' . $zmienne_do_przekazania . '"><b>Usuń tę pozycję</b><img src="obrazki/kasuj.png" alt="Usuń tę pozycję" /></a>';
                                        echo '</tr>';
                                        //
                                        unset($zmienne_do_przekazania);
                                        //
                                    }
                                    //
                                    ?>
                                    
                                    </table>
                                    
                                </div>
                                
                                <?php
                                
                            } else {
                              
                                echo '<div class="pozycja_edytowana"><span class="maleInfo" style="margin:-10px 0px 0px -8px">Brak indywidualnych cen produktów przypisanych do konta klienta</span></div>';
                                
                            }
                            unset($TblZnizki);
                            ?>
                            
                            </div>
                            
                        </div>                          

                        <?php // ********************************************* SYSTEM PUNKTOW *************************************************** ?>
                        
                        <div id="zakl_id_5" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="punkty">
                                <span class="maleInfo" style="margin:0px">Brak punktów</span>
                            </div>

                        </div>     
                        
                        <?php // ********************************************* RECENZJE *************************************************** ?>
                        
                        <div id="zakl_id_6" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="recenzje">
                                <span class="maleInfo" style="margin:0px">Klient nie napisał żadnej recenzji</span>
                            </div>

                        </div>  

                        <?php } ?>

                        <?php // ********************************************* STATYSTYKA *************************************************** ?>
                        
                        <div id="zakl_id_7" style="display:none;">
                        
                            <div id="statystyki">
                                <span class="maleInfo" style="margin:0px">Brak statystyk dla klienta</span>
                            </div>

                        </div>                        

                        <?php // ********************************************* UWAGI *************************************************** ?>
                        
                        <div id="zakl_id_8" style="display:none;">                           
                           <p>
                             <label style="width:70px" for="notatki">Uwagi:</label>
                             <textarea name="notatki" id="notatki" cols="100" rows="10"><?php echo $info['customers_dod_info']; ?></textarea>
                          </p>  
                           <p>
                             <label style="width:70px" for="dod_informacje">Dodatkowe informacje:</label>
                             <textarea name="dod_informacje" id="dod_informacje" cols="100" rows="10"><?php echo $info['customers_dod_info_add']; ?></textarea>
                          </p>                           
                          <div class="maleInfo" style="margin-left:25px">Informacje widoczne tylko dla obsługi sklepu</div>
                        </div>  

                        <?php // ********************************************* LISTA ZAMOWIEN *************************************************** ?>
                        
                        <div id="zakl_id_9" style="display:none;">
                        
                            <div class="pozycja_edytowana" id="zamowienia">
                                <span class="maleInfo" style="margin:0px">Brak zamówień dla klienta</span>
                            </div>

                        </div>    

                        <?php // ***************************** DODATKOWE INFORMACJE W PANELU KLIENTA ************************************** ?>
                        
                        <div id="zakl_id_12" style="display:none;">
                        
                            <div class="pozycja_edytowana" id="pola_panel_klienta">
                                <span class="maleInfo" style="margin:0px">Brak dodatkowych informacji dla tego klienta</span>
                            </div>

                        </div>                           

                        <?php
                        $zakladka = '0';
                        if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                        unset($_GET['zakladka']);
                        ?>
                        
                        <script>
                        gold_tabs_horiz(<?php echo $zakladka; ?>,'0'); pokaz_dane(<?php echo $zakladka; ?>,'0');
                        </script>                         
                    
                    </div>
                    
                </div>

                <?php

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>
            
          </div>  

          <?php if ((int)$db->ile_rekordow($sql) > 0) { ?>
          
          <br />
          
          <div class="przyciski_dolne">
          
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    

          </div>    

          <?php }
          
          $db->close_query($sql);
          unset($info);          
          
          ?>      

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
