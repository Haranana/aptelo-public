<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // pobieranie danych z ceidg
    if (isset($_GET['ceidg']) && INTEGRACJA_CEIDG_WLACZONY == 'tak') {
      
        CeidgKrs::PobierzCeidg( $filtr->process($_GET['ceidg']) );

        Funkcje::PrzekierowanieURL('klienci_dodaj.php');
      
    }

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
    
        $zakodowane_haslo = Funkcje::zakodujHaslo($filtr->process($_POST["password"]));
        $pola = array(
                array('customers_id_private',$filtr->process($_POST['id_klienta_magazyn'])),
                array('customers_nick',$filtr->process($_POST['nick'])),
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_email_address',$filtr->process($_POST['email'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_password',$zakodowane_haslo),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('customers_reviews',( isset($_POST['klient_opinie']) ? '1' : '0')),
                array('customers_marketing',( isset($_POST['klient_marketing']) ? '1' : '0')),
                array('customers_discount',(float)$_POST['rabat']),
                array('customers_groups_id',(int)$_POST['grupa']),
                array('service',(int)$_POST['opiekun']),
                array('customers_status',(int)$_POST['aktywnosc']),
                array('customers_dod_info',$filtr->process($_POST['notatki'])),
                array('language_id',(int)$_SESSION['domyslny_jezyk']['id']));

        if (isset($_POST['data_urodzenia'])) {
          $pola[] = array('customers_dob', date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_urodzenia']))));
        }

        if (isset($_POST['plec'])) {
          $pola[] = array('customers_gender',$filtr->process($_POST['plec']));
        }
        
        $sql = $db->insert_query('customers' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_info_id',(int)$id_dodanej_pozycji),
                array('customers_info_number_of_logons','0'),
                array('customers_info_date_account_created','now()'),
                array('customers_info_date_account_last_modified','now()'));
                
        $sql = $db->insert_query('customers_info' , $pola);
        unset($pola);

        $pola = array(
                array('customers_id',(int)$id_dodanej_pozycji),
                array('entry_company',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nazwa_firmy']) : '')),
                array('entry_nip',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nip_firmy']) : '')),
                array('entry_regon',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['regon_firmy']) : '')),
                array('entry_pesel',(($_POST['osobowosc'] == '1') ? $filtr->process($_POST['pesel']) : '')),
                array('entry_firstname',$filtr->process($_POST['imie'])),
                array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                array('entry_street_address',$filtr->process($_POST['ulica'])),
                array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('entry_city',$filtr->process($_POST['miasto'])),
                array('entry_country_id',(int)$_POST['panstwo']),
                array('entry_zone_id',(isset($_POST['wojewodztwo']) ? (int)$_POST['wojewodztwo'] : '0')));

        $sql = $db->insert_query('address_book' , $pola);
        $id_dodanej_pozycji_adres = $db->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_default_address_id',(int)$id_dodanej_pozycji_adres));

        $db->update_query('customers' , $pola, " customers_id = '".(int)$id_dodanej_pozycji."'");	
        unset($pola);

        // dodatkowe pola klientow
        $db->delete_query('customers_to_extra_fields' , " customers_id = '".(int)$id_dodanej_pozycji."'");  

        $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type FROM customers_extra_fields ce WHERE ce.fields_status = '1'";

        $sql = $db->open_query($dodatkowe_pola_klientow);

        if ( (int)$db->ile_rekordow($sql) > 0  ) {

          while ( $dodatkowePola = $sql->fetch_assoc() ) {
          
            $wartosc = '';
            if ( $dodatkowePola['fields_input_type'] != '3' ) {
            
              $pola = array(
                      array('customers_id',(int)$id_dodanej_pozycji),
                      array('fields_id',(int)$dodatkowePola['fields_id']),
                      array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']])));
              
            } else {
            
              if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
              
                foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
                  $wartosc .= $value . "\n";
                }
                
                $pola = array(
                        array('customers_id',(int)$id_dodanej_pozycji),
                        array('fields_id',(int)$dodatkowePola['fields_id']),
                        array('value',$filtr->process($wartosc)));
              }

            }
            
            if ( isset($pola) && count($pola) > 0 ) {
              $pola[] = array('language_id', '1');
              $db->insert_query('customers_to_extra_fields' , $pola);
              unset($pola);
            }
            
          }

        }
        //
        
        // dane do newslettera
        // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
        $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email'])."'"); 
        //
        $pola = array(
                array('customers_id',(int)$id_dodanej_pozycji),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('date_added',( isset($_POST['biuletyn']) ? 'now()' : '0000-00-00')));

        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola);
        
        // zapis do newslettera w systemie Freshmail
        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $freshMail = new FreshMail();
             $freshMail->ZapiszSubskrybenta( $filtr->process($_POST['email']), 1, INTEGRACJA_DOMYSLNA_LISTA );
             //
             if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $freshMail->ZapiszSubskrybenta( $filtr->process($_POST['email']), 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                  //
             }                   
             //
             unset($freshMail);
             //
        }  
        
        // zapis do newslettera w systemie MailerLite
        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $mailerLite = new MailerLite();
             $mailerLite->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );
             //
             if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $mailerLite->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                  //
             }                   
             //
             unset($mailerLite);
             //
        }         
        
        // zapis do newslettera w systemie Ecomail
        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $ecomail = new Ecomail();
             $ecomail->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );
             //
             if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $ecomail->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                  //
             }                   
             //
             unset($ecomail);
             //
        }         
        
        // zapis do newslettera w systemie Mailjet
        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $mailjet = new Mailjet();
             $mailjet->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );
             //
             if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $mailjet->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                  //
             }                   
             //
             unset($mailjet);
             //
        }         

        // zapis do newslettera w systemie Getall
        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
             $getall->DodajSubskrybenta( $filtr->process($_POST['email']), $filtr->process($_POST['imie']), INTEGRACJA_GETALL_DOMYSLNA_LISTA );  
             //
             if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' ) {
                  //
                  $getall->DodajSubskrybenta( $filtr->process($_POST['email']), $filtr->process($_POST['imie']), INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                  //
             }                   
             //
             unset($getall);
             //
        }          
        
        unset($grupyNewslettera);

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('klienci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('klienci.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    
    <div id="cont">

        <script>
        $(document).ready(function() {
            $("#klienciForm").validate({
              rules: {
                email: {required: true,email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php"},
                nick: {remote: "ajax/sprawdz_czy_jest_nick.php"},
                imie: {required: true},
                nazwisko: {required: true},
                ulica: {required: true},
                kod_pocztowy: {required: true},
                miasto: {required: true},
                nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }},
                nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik;}},
                rabat: {range: [-100, 0],number: true},
                password: {required: true}
              },
              messages: {
                email: {required: "Pole jest wymagane.",email: "Wpisano niepoprawny adres e-mail.",remote: "Taki adres jest już używany."},
                nick: {remote: "Taki login jest już używany."}
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
                      type: "POST",
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
        
        function pokazGrupyNewsletter() {
          //
          if ($('#biuletyn').prop('checked') == true) {
              $('#grupy_newslettera').slideDown();
            } else {
              $('#grupy_newslettera').slideUp();
          }
          //
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

        <form action="klienci/klienci_dodaj.php" method="post" id="klienciForm" class="cmxform"> 
        
        <div class="poleForm">
        
        <div class="naglowek">Dodawanie nowego klienta</div>
          
            <input type="hidden" name="akcja" value="zapisz" />

            <div id="ZakladkiEdycji">
              
                <div id="LeweZakladki">

                    <a href="javascript:gold_tabs_horiz('0','0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                    <a href="javascript:gold_tabs_horiz('1','1')" class="a_href_info_zakl" id="zakl_link_1">Dane adresowe</a> 
                    <a href="javascript:gold_tabs_horiz('2','2')" class="a_href_info_zakl" id="zakl_link_2">Uwagi</a>
                
                </div>

                <?php $licznik_zakladek = 0; ?>

                <div id="PrawaStrona">
                
                    <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                
                    <div id="zakl_id_0" style="display:none">
                    
                      <?php if ( INTEGRACJA_CEIDG_WLACZONY == 'tak' ) { ?>
                    
                      <script>
                      function sprawdz_nip( nip ) {
                          var sprawdzenie_nip = new Array(6,5,7,2,3,4,5,6,7); var nip = nip.replace(/[\ \-]/gi, ''); 
                          if (nip.length != 10) { 
                              return false; 
                          } else  {
                              var n = 0;
                              for (var i = 0; i < 9; i++) {	n += nip[i] * sprawdzenie_nip[i]; }
                              n %= 11;
                              if (n != nip[9]) { return false; }
                          }
                          return true;	                         
                      }           
                      function pobierz_ceidg() {
                          //
                          var nip_ceidg = $('#ceidg').val();
                          //
                          if ( nip_ceidg != '' ) {
                               //
                               nip_ceidg = nip_ceidg.replace(/[\ \-]/gi, '');
                               //
                               if ( sprawdz_nip( nip_ceidg ) ) {
                                    //
                                    document.location.href = '/zarzadzanie/klienci/klienci_dodaj.php?ceidg=' + nip_ceidg; 
                                    //
                               } else {
                                    //
                                    $.colorbox( { html:'<div id="PopUpInfo" style="text-align:center">Podano błędny numer NIP</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                                    //
                               }
                               //
                          }
                          //
                      }
                      <?php if (isset($_SESSION['ceidg_info'])) { ?>
                      $(document).ready(function() {
                          //
                          $.colorbox( { html:'<div id="PopUpInfo" style="text-align:center"><?php echo $_SESSION['ceidg_info']; ?></div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                          //
                      });
                      <?php } ?>
                      </script>
                      
                      <?php

                      ?>
                      
                      <div class="SzukajCeidg">
                        <input type="text" id="ceidg" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nip'] : ''); ?>" placeholder="numer NIP" size="25" /> 
                        <button type="button" style="margin:1px 0px 0px 5px" class="przyciskNon" onclick="pobierz_ceidg()">Pobierz dane z CEIDG</button>    
                      </div>
                      
                      <?php } ?>
                    
                      <p>
                        <label>Status konta:</label>
                        <input type="radio" value="1" name="aktywnosc" checked="checked" id="aktywnosc_tak" /><label class="OpisFor" for="aktywnosc_tak">aktywne</label>
                        <input type="radio" value="0" name="aktywnosc" id="aktywnosc_nie" /><label class="OpisFor" for="aktywnosc_nie">nieaktywne</label>
                      </p> 

                      <p>
                        <label class="required" for="email">Adres e-mail:</label>
                        <input type="text" name="email" id="email" size="53" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['email'] : ''); ?>" /><em class="TipIkona"><b>Adres wykorzystywany do logowania oraz do korespondencji</b></em>
                      </p>                          
                                
                      <p>
                        <label for="nick">Login:</label>
                        <input type="text" name="nick" id="nick" size="53" value="" /><em class="TipIkona"><b>Może być używany do logowania zamiennie z wprowadzonym adresem e-mail</b></em>
                      </p>                          
                                
                      <p>
                        <label class="required" for="imie">Imię:</label>
                        <input type="text" name="imie" id="imie" size="53" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['imie'] : ''); ?>" />
                      </p> 

                      <p>
                        <label class="required" for="nazwisko">Nazwisko:</label>
                        <input type="text" name="nazwisko" id="nazwisko" size="53" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nazwisko'] : ''); ?>" />
                      </p>

                      <?php
                      if ( KLIENT_POKAZ_PLEC == 'tak' ) {
                        ?>
                        <p>
                          <label>Płeć:</label>
                          <input type="radio" value="f" name="plec" id="plec_f" checked="checked" /><label class="OpisFor" for="plec_f">kobieta</label>
                          <input type="radio" value="m" name="plec" id="plec_m" /><label class="OpisFor" for="plec_m">mężczyzna</label>
                        </p> 
                        <?php
                      }
                      ?>

                      <?php
                      if ( KLIENT_POKAZ_DATE_URODZENIA == 'tak' ) {
                        ?>
                        <p>
                          <label for="data_urodzenia">Data urodzenia:</label>
                          <input type="text" name="data_urodzenia" id="data_urodzenia" size="30" value="" class="datepicker" />
                        </p> 
                        <?php
                      }
                      ?>

                      <?php
                      if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                        ?>
                        <p>
                          <label for="telefon">Numer telefonu:</label>
                          <input type="text" name="telefon" id="telefon" size="32" value="" />
                        </p>
                        <?php
                      }
                      ?>

                      <?php
                      if ( KLIENT_POKAZ_FAX == 'tak' ) {
                        ?>
                        <p>
                          <label for="fax">Numer faxu:</label>
                          <input type="text" name="fax" id="fax" size="32" value="" />
                        </p>
                        <?php
                      }
                      ?>

                      <p>
                        <label for="grupa">Grupa klientów:</label>
                        <?php
                        $tablica = Klienci::ListaGrupKlientow(false);
                        echo Funkcje::RozwijaneMenu('grupa', $tablica, '', 'id="grupa"'); ?>
                      </p>

                      <p>
                        <label for="rabat">Indywidualny rabat [%]:</label>
                        <input type="text" name="rabat" id="rabat" value="0" size="5" /><em class="TipIkona"><b>liczba z zakresu od -100 do 0</b></em>
                      </p>
                      
                      <p>
                        <label for="id_klienta_magazyn">Id klienta w programie magazynowym:</label>
                        <input type="text" name="id_klienta_magazyn" id="id_klienta_magazyn" size="20" value="" />
                      </p>                           
                      
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
                        echo Funkcje::RozwijaneMenu('opiekun', $lista_uzytkownikow, '', 'id="opiekun"' ); 
                        unset($lista_uzytkownikow);
                        ?>
                      </p>     

                      <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                      <p>
                        <label for="biuletyn">Subskrypcja biuletynu:</label>
                        <input type="checkbox" checked="checked" value="1" name="biuletyn" onclick="pokazGrupyNewsletter()" id="biuletyn" />
                        <label class="OpisForPustyLabel" for="biuletyn"></label>
                      </p>
                      
                      <?php
                      $TablicaGrup = Newsletter::GrupyNewslettera();
                      if ( count($TablicaGrup) > 0 ) {
                      ?>
                      <div id="grupy_newslettera" class="GrupyNewslettera">
                        <table>
                            <tr>
                                <td><label>Przypisany do grup newslettera:</label></td>   
                                <td>
                                
                                <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie klient będzie przypisany do wszystkich grup</span>
                                
                                <?php
                                foreach ($TablicaGrup as $Grupa) {
                                    //
                                    echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" id="newsletter_grupa_' . $Grupa['id'] . '" /><label class="OpisFor" for="newsletter_grupa_' . $Grupa['id'] . '">' . $Grupa['text'] . '</label><br />';
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
                        <input type="checkbox" value="1" name="klient_opinie" id="klient_opinie" />
                        <label class="OpisForPustyLabel" for="klient_opinie"></label>
                      </p>                       
                      <p>
                        <label for="klient_marketing">Zgoda na działania marketingowe:</label>
                        <input type="checkbox" value="1" name="klient_marketing" id="klient_marketing" />
                        <label class="OpisForPustyLabel" for="klient_marketing"></label>
                      </p>                             

                      <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                      <p>
                        <label class="required" for="password">Hasło:</label>
                        <input type="password" name="password" id="password" value="" size="53" />
                      </p>

                      <p>
                        <label class="required" for="nowe_haslo_powtorz">Powtórz hasło:</label>
                        <input type="password" name="nowe_haslo_powtorz" id="nowe_haslo_powtorz" value="" size="53" equalTo="#password" />
                      </p>
                      
                      <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;">

                      <div style="margin-top:10px;margin-left:10px;">
                      <?php echo Klienci::pokazDodatkowePolaKlientow('',$_SESSION['domyslny_jezyk']['id']); ?>
                      </div>

                    </div>
                    
                    <?php // ********************************************* KSIAZKA ADRESOWA *************************************************** ?>
                    
                    <div id="zakl_id_1" style="display:none">

                      <p>
                        <label>Osobowość prawna:</label>
                        <input type="radio" value="1" name="osobowosc" id="osobowosc_osoba" onclick="zmienOsobowosc(0)" <?php echo ((!isset($_SESSION['ceidg'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="osobowosc_osoba">osoba fizyczna</label>
                        <input type="radio" value="0" name="osobowosc" id="osobowosc_firma" onclick="zmienOsobowosc(1)" <?php echo ((isset($_SESSION['ceidg'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="osobowosc_firma">firma</label>
                      </p> 
                      
                      <p id="pesel" <?php echo ((isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?>>
                        <label for="pesel_val">Numer PESEL:</label>
                        <input type="text" name="pesel" id="pesel_val" value="" size="32" />
                      </p>

                      <p id="firma" <?php echo ((!isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?>>
                        <label class="required" for="nazwa_firmy">Nazwa firmy:</label>
                        <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['firma'] : ''); ?>" size="53" />
                      </p>

                      <p id="nip" <?php echo ((!isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?> class="required">
                        <label class="required" for="nip_firmy">Numer NIP:</label>
                        <input type="text" name="nip_firmy" id="nip_firmy" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nip'] : ''); ?>" size="32" />
                      </p>
                      
                      <p id="regon" <?php echo ((!isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?>>
                        <label for="regon_firmy">REGON:</label>
                        <input type="text" name="regon_firmy" id="regon_firmy" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['regon'] : ''); ?>" size="15" />
                      </p>                        

                      <p>
                        <label class="required" for="ulica">Ulica i numer domu:</label>
                        <input type="text" name="ulica" id="ulica" size="53" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['ulica'] : '') . ' ' . ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nr_domu'] : ''); ?>" />
                      </p>                          
                               
                      <p>
                        <label class="required" for="kod_pocztowy">Kod pocztowy:</label>
                        <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['kod_pocztowy'] : ''); ?>" />
                      </p> 

                      <p>
                        <label class="required" for="miasto">Miejscowość:</label>
                        <input type="text" name="miasto" id="miasto" size="53" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['miasto'] : ''); ?>" />
                      </p>

                      <p>
                        <label class="required" for="selection">Kraj:</label>
                        <?php
                        $tablicaPanstw = Klienci::ListaPanstw();
                        echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, '170', 'id="selection"'); ?>
                      </p>

                      <?php
                      if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                        ?>
                        <p>
                          <label>Województwo:</label>
                          <?php
                          $tablicaWojewodztw = Klienci::ListaWojewodztw('170');
                          //
                          $domyslneWojewodztwo = '';
                          //
                          foreach ($tablicaWojewodztw as $tmp) {
                              //
                              if (isset($_SESSION['ceidg'])) {
                                  if ( mb_strtoupper((string)$_SESSION['ceidg']['wojewodztwo']) == mb_strtoupper((string)$tmp['text']) ) {
                                       $domyslneWojewodztwo = $tmp['id'];
                                  }
                              }
                              //
                          }
                          //
                          echo '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, $domyslneWojewodztwo).'</span>';
                          ?>
                        </p>
                        <?php
                      }
                      ?>

                    </div>
                    
                    <?php // ********************************************* UWAGI *************************************************** ?>
                    
                    <div id="zakl_id_2" style="display:none">
                       <p>
                         <label for="notatki">Uwagi:<em class="TipIkona"><b>Zawartość informacji widoczna tylko dla obsługi sklepu.</b></em></label>
                         <textarea name="notatki" id="notatki" cols="100" rows="10"></textarea>
                      </p>                                        
                    </div>    

                    <script>
                    gold_tabs_horiz('0','0');
                    </script>                         
                
                </div>
            
            </div>

        </div>
        
        <br />
          
        <div class="przyciski_dolne">
          <input type="submit" class="przyciskNon" value="Zapisz dane" />
          <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
        </div>          

        </form>

    </div>
    
    <?php
    
    if ( isset($_SESSION['ceidg']) ) {
         //
         unset($_SESSION['ceidg']);
         //
    }
    if ( isset($_SESSION['ceidg_info']) ) {
         //
         unset($_SESSION['ceidg_info']);
         //
    }
    
    include('stopka.inc.php');    
    
} ?>