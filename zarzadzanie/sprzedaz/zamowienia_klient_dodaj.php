<?php
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    chdir('../');            

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    // zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
    $protKlient = new Dostep($db);

    if ($protKlient->wyswietlStrone) {    

        // grupy newslettera
        $grupyNewslettera = '';
        if ( isset($_POST['biuletyn']) ) {
             //
             if ( isset($_POST['newsletter_grupa']) ) {
                  $grupyNewslettera = ',' . implode(',', (array)$filtr->process($_POST['newsletter_grupa'])) . ',';
             }
             //
        }   
        
        $pola = array(
                array('customers_id_private',$filtr->process($_POST['id_klienta_magazyn'])),
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_reviews',( isset($_POST['klient_opinie']) ? '1' : '0')),                
                array('customers_marketing',( isset($_POST['klient_marketing']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('customers_status',(int)$_POST['aktywnosc']),
                array('customers_dod_info',$filtr->process($_POST['notatki'])),
                array('language_id',(int)$_SESSION['domyslny_jezyk']['id'])            
        );
        
        // jezeli jest konto z rejestracja
        if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
          //
          $zakodowane_haslo = Funkcje::zakodujHaslo($filtr->process($_POST["password"]));
          $pola[] = array('customers_password',$zakodowane_haslo);
          $pola[] = array('customers_discount',(float)$_POST['rabat']);
          $pola[] = array('customers_groups_id',(int)$_POST['grupa']);
          $pola[] = array('service',(int)$_POST['opiekun']);
          $pola[] = array('customers_email_address',$filtr->process($_POST['email']));
          $pola[] = array('customers_nick',$filtr->process($_POST['nick']));
          $pola[] = array('customers_guest_account','0');
          //
        } else {
          //
          $pola[] = array('customers_email_address',$filtr->process($_POST['email_bez_rejestracji']));
          $pola[] = array('customers_guest_account','1');
          //
        }

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
                array('entry_zone_id',(isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : '0')));

        $sql = $db->insert_query('address_book' , $pola);
        $id_dodanej_pozycji_adres = $db->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_default_address_id',(int)$id_dodanej_pozycji_adres));

        $db->update_query('customers' , $pola, " customers_id = '".(int)$id_dodanej_pozycji."'");	
        unset($pola);
        
        // dane do newslettera
        $pola = array(
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('date_added',( isset($_POST['biuletyn']) ? 'now()' : '0000-00-00')),
                array('customers_id',(int)$id_dodanej_pozycji));
                
        // jezeli jest konto z rejestracja
        if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {   
          //
          // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
          $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email'])."'"); 
          //      
          $pola[] = array('subscribers_email_address',$filtr->process($_POST['email']));
          //
        } else {
          // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
          $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email_bez_rejestracji'])."'"); 
          // 
          $pola[] = array('subscribers_email_address',$filtr->process($_POST['email_bez_rejestracji']));
          //
        }

        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola);
        
        // zapis do newslettera w systemie Freshmail
        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $freshMail = new FreshMail();
             $freshMail->ZapiszSubskrybenta( (((isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0)) ? $filtr->process($_POST['email']) : $filtr->process($_POST['email_bez_rejestracji'])), 1, INTEGRACJA_DOMYSLNA_LISTA );
             //
             if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
                //
                if ( INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA == 'tak' && $_POST['rodzaj_konta'] == 0 ) {
                    //
                    $freshMail->ZapiszSubskrybenta( $filtr->process($_POST['email']), 1, INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX );
                    //
                }                   
                //
             }
             //
             unset($freshMail);
             //
        }      

        // zapis do newslettera w systemie Mailerlite
        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && isset($_POST['biuletyn']) ) {
             //
             $mailerLite = new MailerLite();
             $mailerLite->ZapiszSubskrybenta( (((isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0)) ? $filtr->process($_POST['email']) : $filtr->process($_POST['email_bez_rejestracji'])), INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA );
             //
             if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
                //
                if ( INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA == 'tak' && $_POST['rodzaj_konta'] == 0 ) {
                    //
                    $mailerLite->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX );
                    //
                }                   
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
             $ecomail->ZapiszSubskrybenta( (((isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0)) ? $filtr->process($_POST['email']) : $filtr->process($_POST['email_bez_rejestracji'])), INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA );
             //
             if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
                //
                if ( INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA == 'tak' && $_POST['rodzaj_konta'] == 0 ) {
                    //
                    $ecomail->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX );
                    //
                }                   
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
             $mailjet->ZapiszSubskrybenta( (((isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0)) ? $filtr->process($_POST['email']) : $filtr->process($_POST['email_bez_rejestracji'])), INTEGRACJA_MAILJET_DOMYSLNA_LISTA, false );
             //
             if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
                //
                if ( INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA == 'tak' && $_POST['rodzaj_konta'] == 0 ) {
                    //
                    $mailjet->ZapiszSubskrybenta( $filtr->process($_POST['email']), INTEGRACJA_MAILJET_REJESTRACJA_PREFIX, true );
                    //
                }                   
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
             $getall->DodajSubskrybenta( (((isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0)) ? $filtr->process($_POST['email']) : $filtr->process($_POST['email_bez_rejestracji'])), $filtr->process($_POST['imie']), INTEGRACJA_GETALL_DOMYSLNA_LISTA );  
             //
             if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
                //
                if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' && $_POST['rodzaj_konta'] == 0 ) {
                    //
                    $getall->DodajSubskrybenta( $filtr->process($_POST['email']), $filtr->process($_POST['imie']), INTEGRACJA_GETALL_REJESTRACJA_PREFIX );
                    //
                }                   
                //
             }
             //
             unset($getall);
             //
        }             

        unset($grupyNewslettera);
        
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('zamowienia_dodaj.php?klient_id='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('zamowienia.php');
        }
        
    }
    
}

if (!class_exists('Dostep')) {
    exit;
}
  
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$protKlient = new Dostep($db);

if ($protKlient->wyswietlStrone) {    
?>

    <script>
    $(document).ready(function() {
        $("#klienciForm").validate({
          rules: {
            email: {required: function() {var wynik = true; if ( $("input[name='rodzaj_konta']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; },email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php"},
            email_bez_rejestracji: {required: function() {var wynik = true; if ( $("input[name='rodzaj_konta']:checked", "#klienciForm").val() == "0" ) { wynik = false; } return wynik; },email: true},
            nick: {remote: "ajax/sprawdz_czy_jest_nick.php"},
            imie: {required: true},
            nazwisko: {required: true},
            ulica: {required: true},
            kod_pocztowy: {required: true},
            miasto: {required: true},
            nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }},
            nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik;}},
            rabat: {range: [-100, 0],number: true},
            password: {required: function() {var wynik = true; if ( $("input[name='rodzaj_konta']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }}
          },
          messages: {
            email: {required: "Pole jest wymagane.",email: "Wpisano niepoprawny adres e-mail.",remote: "Taki adres jest już używany."},
            email_bez_rejestracji: {required: "Pole jest wymagane.",email: "Wpisano niepoprawny adres e-mail."},
            nick: {remote: "Taki login jest już używany."}
          }       
        });

        $('input.datepicker').Zebra_DatePicker({
            view: 'years',
            format: 'd-m-Y',
            inside: false,
            readonly_element: true
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

    function rejestracjaKlienta(nr) {
      //
      if ( nr == 0 ) {
           $('.bezRejestracji').slideUp();
           $('.mailRejestracja').hide();
           $('.mailRejestracja input').val('');
           $('.mailBezRejestracja').show();
        } else {
           $('.bezRejestracji').slideDown();
           $('.mailRejestracja').show();
           $('.mailBezRejestracja').hide();
           $('.mailBezRejestracja input').val('');
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

    <form action="sprzedaz/zamowienia_klient_dodaj.php" method="post" id="klienciForm" class="cmxform" autocomplete="off"> 
    
        <div style="margin:0px 10px 5px 10px">

            <div class="DodawanieNowegoKlienta">

                <div class="OknoDodawaniaKlienta">
                
                    <div class="poleForm">
                    
                        <div class="naglowek">Dane podstawowe</div>
                        
                        <div class="pozycja_edytowana">   

                        <input type="hidden" name="akcja" value="zapisz" />
                        
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
                                      document.location.href = '/zarzadzanie/sprzedaz/zamowienia_dodaj.php?ceidg=' + nip_ceidg; 
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
                        
                        <div class="SzukajCeidg">
                          <input type="text" id="ceidg" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nip'] : ''); ?>" placeholder="numer NIP" size="25" autocomplete="off" /> 
                          <button type="button" style="margin:1px 0px 0px 5px" class="przyciskNon" onclick="pobierz_ceidg()">Pobierz dane z CEIDG</button>    
                        </div>          

                        <?php } ?>

                        <p>
                          <label>Status konta:</label>
                          <input type="radio" value="1" name="aktywnosc" id="aktywny_tak" checked="checked" /> <label class="OpisFor" for="aktywny_tak">aktywne</label>
                          <input type="radio" value="0" name="aktywnosc" id="aktywny_nie" /> <label class="OpisFor" for="aktywny_nie">nieaktywne</label>
                        </p> 
                        
                        <p>
                          <label>Rodzaj konta:</label>
                          <input type="radio" value="1" onclick="rejestracjaKlienta(0)" name="rodzaj_konta" id="bez_rejestracji" checked="checked" /> <label class="OpisFor" for="bez_rejestracji">bez rejestracji</label>
                          <input type="radio" value="0" onclick="rejestracjaKlienta(1)" name="rodzaj_konta" id="rejestracja" /> <label class="OpisFor" for="rejestracja">z rejestracją</label>
                        </p>

                        <div class="bezRejestracji" style="display:none">

                            <p>
                              <label class="required">Hasło:</label>
                              <input type="password" name="password" id="password" value="" size="35" autocomplete="off" />
                            </p>

                            <p>
                              <label class="required">Powtórz hasło:</label>
                              <input type="password" name="nowe_haslo_powtorz" id="nowe_haslo_powtorz" value="" size="35" equalTo="#password" autocomplete="off" />
                            </p>
                        
                        </div>
                        
                        <div class="mailRejestracja" style="display:none">

                            <p>
                              <label class="required">Adres e-mail:</label>
                              <input type="text" name="email" id="email" size="35" value="" />
                              <em class="TipIkona"><b>Adres wykorzystywany do logowania oraz do korespondencji</b></em>                         
                            </p>

                        </div>
                        
                        <div class="mailBezRejestracja">
                        
                            <p>
                              <label class="required">Adres e-mail:</label>
                              <input type="text" name="email_bez_rejestracji" id="email_bez_rejestracji" size="35" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['email'] : ''); ?>" />
                              <em class="TipIkona"><b>Adres wykorzystywany do korespondencji</b></em>
                            </p> 
                            
                        </div>

                        <div class="bezRejestracji" style="display:none">
                            
                            <p>
                              <label>Login:</label>
                              <input type="text" name="nick" id="nick" size="35" value="" autocomplete="off" />
                              <em class="TipIkona"><b>Może być używany do logowania zamiennie z wprowadzonym adresem e-mail</b></em>
                            </p>

                        </div>

                        <p>
                          <label class="required">Imię:</label>
                          <input type="text" name="imie" id="imie" size="35" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['imie'] : ''); ?>" />
                        </p> 

                        <p>
                          <label class="required">Nazwisko:</label>
                          <input type="text" name="nazwisko" id="nazwisko" size="35" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nazwisko'] : ''); ?>" />
                        </p>

                        <?php
                        if ( KLIENT_POKAZ_PLEC == 'tak' ) {
                          ?>
                          <p>
                            <label>Płeć:</label>
                            <input type="radio" value="f" name="plec" checked="checked" id="plec_kobieta" /><label class="OpisFor" for="plec_kobieta">kobieta</label>
                            <input type="radio" value="m" name="plec"  id="plec_mezczyzna" /><label class="OpisFor" for="plec_mezczyzna">mężczyzna</label>
                          </p> 
                          <?php
                        }
                        ?>

                        <?php
                        if ( KLIENT_POKAZ_DATE_URODZENIA == 'tak' ) {
                          ?>
                          <p>
                            <label>Data urodzenia:</label>
                            <input type="text" name="data_urodzenia" id="data_urodzenia" size="30" value="" class="datepicker" />
                          </p> 
                          <?php
                        }
                        ?>

                        <?php
                        if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                          ?>
                          <p>
                            <label>Numer telefonu:</label>
                            <input type="text" name="telefon" id="telefon" size="32" value="" />
                          </p>
                          <?php
                        }
                        ?>

                        <?php
                        if ( KLIENT_POKAZ_FAX == 'tak' ) {
                          ?>
                          <p>
                            <label>Numer faxu:</label>
                            <input type="text" name="fax" id="fax" size="32" value="" />
                          </p>
                          <?php
                        }
                        ?>
                        
                        <div class="bezRejestracji" style="display:none">

                            <p>
                              <label>Grupa klientów:</label>
                              <?php
                              $tablica = Klienci::ListaGrupKlientow(false);
                              echo Funkcje::RozwijaneMenu('grupa', $tablica); ?>
                            </p>

                            <p>
                              <label>Indywidualny rabat [%]:</label>
                              <input type="text" name="rabat" id="rabat" value="" size="5" />
                              <em class="TipIkona"><b>Liczba z zakresu -100 do 0</b></em>
                            </p>
                            
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
                            
                        </div>
                        
                        <p>
                          <label>Id klienta w programie magazynowym:</label>
                          <input type="text" name="id_klienta_magazyn" size="20" value="" />
                        </p>                           
                        
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                        <p>
                          <label>Subskrypcja biuletynu:</label>
                          <input type="checkbox" checked="checked" value="1" name="biuletyn" onclick="pokazGrupyNewsletter()" id="biuletyn" /><label class="OpisForPustyLabel" for="biuletyn"></label>
                        </p>
                        
                        <?php
                        $TablicaGrup = Newsletter::GrupyNewslettera();
                        if ( count($TablicaGrup) > 0 ) {
                        ?>
                        <div id="grupy_newslettera" style="margin-left:2px">
                        
                          <table>
                          
                              <tr>
                                  <td><label>Przypisany do grup newslettera:</label></td>   
                                  <td>
                                  
                                  <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie klient będzie przypisany do wszystkich grup</span>
                                  
                                  <?php
                                  foreach ($TablicaGrup as $Grupa) {
                                      //
                                      echo '<input type="checkbox" value="' . $Grupa['id'] . '" id="grupa_newslettera_' . $Grupa['id'] . '" name="newsletter_grupa[]" /> <label class="OpisFor" for="grupa_newslettera_' . $Grupa['id'] . '">' . $Grupa['text'] . '</label><br />';
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
                          <label>Zgoda na zbieranie opinii o sklepie i produktach:</label>
                          <input type="checkbox" checked="checked" value="1" name="klient_opinie" id="klient_opinie" /><label class="OpisForPustyLabel" for="klient_opinie"></label>
                        </p>                        

                        <p>
                          <label>Zgoda na działania marketingowe:</label>
                          <input type="checkbox" checked="checked" value="1" name="klient_marketing" id="klient_marketing" /><label class="OpisForPustyLabel" for="klient_marketing"></label>
                        </p>                        

                        </div>
                    
                    </div>

                </div>
                
                <div class="OknoDodawaniaKlienta">
                
                    <div class="poleForm MarginesOkna">
                    
                        <div class="naglowek">Dane adresowe</div>
                        
                        <div class="pozycja_edytowana">                 
                
                            <p>
                              <label>Osobowość prawna:</label>
                              <input type="radio" value="1" name="osobowosc" id="osobowosc_fizyczna" onclick="zmienOsobowosc(0)" <?php echo ((!isset($_SESSION['ceidg'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="osobowosc_fizyczna">osoba fizyczna</label>
                              <input type="radio" value="0" name="osobowosc" id="osobowosc_prawna" onclick="zmienOsobowosc(1)" <?php echo ((isset($_SESSION['ceidg'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="osobowosc_prawna">firma</label>
                            </p> 

                            <p id="pesel" <?php echo ((isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?>>
                              <label>Numer PESEL:</label>
                              <input type="text" name="pesel" value="" size="32" autocomplete="off" />
                            </p>

                            <p id="firma" <?php echo ((!isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?>>
                              <label class="required">Nazwa firmy:</label>
                              <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['firma'] : ''); ?>" size="35" autocomplete="off" />
                            </p>

                            <p id="nip" <?php echo ((!isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?> class="required">
                              <label class="required">Numer NIP:</label>
                              <input type="text" name="nip_firmy" id="nip_firmy" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nip'] : ''); ?>" size="32" autocomplete="off" />
                            </p>
                            
                            <p id="regon" <?php echo ((!isset($_SESSION['ceidg'])) ? 'style="display:none"' : ''); ?>>
                              <label for="regon_firmy">REGON:</label>
                              <input type="text" name="regon_firmy" id="regon_firmy" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['regon'] : ''); ?>" size="15" autocomplete="off" />
                            </p>                        

                            <p>
                              <label class="required">Ulica i numer domu:</label>
                              <input type="text" name="ulica" id="ulica" size="35" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['ulica'] : '') . ' ' . ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['nr_domu'] : ''); ?>" />
                            </p>                          
                                     
                            <p>
                              <label class="required">Kod pocztowy:</label>
                              <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['kod_pocztowy'] : ''); ?>" />
                            </p> 

                            <p>
                              <label class="required">Miejscowość:</label>
                              <input type="text" name="miasto" id="miasto" size="35" value="<?php echo ((isset($_SESSION['ceidg'])) ? $_SESSION['ceidg']['miasto'] : ''); ?>" />
                            </p>

                            <p>
                              <label class="required">Kraj:</label>
                              <?php
                              $tablicaPanstw = Klienci::ListaPanstw();
                              echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, '170', 'id="selection"'); ?>
                            </p>

                            <?php
                            if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                              ?>
                              <p>
                                <label for="selectionresult">Województwo:</label>
                                <?php
                                $tablicaWojewodztw = Klienci::ListaWojewodztw('170');
                                //
                                $domyslneWojewodztwo = '';
                                //
                                foreach ($tablicaWojewodztw as $tmp) {
                                    //
                                    if ( isset($_SESSION['ceidg']) ) {
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
                    
                    </div>      

                    <div class="poleForm MarginesOkna" style="margin-top:10px;">
                    
                        <div class="naglowek">Uwagi</div>
                        
                        <div class="pozycja_edytowana">       
                        
                            <textarea name="notatki" cols="50" rows="5" class="UwagiKlienta"></textarea>
                            
                            <span class="maleInfo">Zawartość informacji widoczna tylko dla obsługi sklepu</span>
                            
                        </div>
                        
                    </div>
                
                </div>              
              
            </div>
            
        </div>
        
        <div class="przyciski_dolne">
          <input type="submit" class="przyciskNon" value="Zapisz dane i przejdź dalej" />   
        </div>         

    </form>    
    
<?php 
} 

unset($protKlient);
?>    