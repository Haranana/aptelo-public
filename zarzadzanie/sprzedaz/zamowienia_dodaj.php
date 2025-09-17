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

        Funkcje::PrzekierowanieURL('zamowienia_dodaj.php');
      
    }  

    $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
    $GLOBALS['tlumacz'] = $i18n->tlumacz( array('WYSYLKI','PODSUMOWANIE_ZAMOWIENIA','PLATNOSCI'), null, true );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $zapytanie = "select c.customers_id, c.language_id, c.customers_status, c.customers_dod_info, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_guest_account, c.customers_email_address, a.entry_company, a.entry_nip, a.entry_pesel, a.entry_street_address, a.entry_postcode, a.entry_city, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_groups_id, c.customers_discount, c.customers_default_address_id, c.customers_nick from customers c left join address_book a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . (int)$_POST['id'] . "'";

      $sql = $db->open_query($zapytanie);

      $info = $sql->fetch_assoc();

      $wartosc_platnosci = explode('|', (string)$filtr->process($_POST["platnosc"]));

      $rodzajPlatnosciOpis = $GLOBALS['tlumacz']['PLATNOSC_'.$wartosc_platnosci[2].'_TEKST'];
      
      $dane_dostawa = explode('|', (string)$_POST['dostawa']);
      $dane_waluta = explode('|', (string)$_POST['waluta']);

      $pola_info = array(
              array('invoice_dokument',$filtr->process($_POST['dokument'])),
              array('customers_id',(int)$_POST['id']),
              array('customers_name',$filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko'])),
              array('customers_company',$info['entry_company']),
              array('customers_nip',$info['entry_nip']),
              array('customers_pesel',$info['entry_pesel']),
              array('customers_street_address',$info['entry_street_address']),
              array('customers_city',$info['entry_city']),
              array('customers_postcode',$info['entry_postcode']),
              array('customers_state',( $info['entry_zone_id'] != '' ? Klienci::pokazNazweWojewodztwa($info['entry_zone_id']) : '' )),
              array('customers_country',Klienci::pokazNazwePanstwa($info['entry_country_id'])),
              array('customers_telephone',( isset($_POST['telefon']) && $_POST['telefon'] != '' ? $filtr->process($_POST['telefon']) : '' ) ),
              array('customers_email_address',$filtr->process($_POST['email'])),
              array('customers_dummy_account',$info['customers_guest_account']), 
              array('last_modified','now()'),              
              array('date_purchased',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_zamowienia'])))),
              array('orders_status',(int)$_POST['status']),
              array('orders_source','4'),
              array('service',(int)$_POST['opiekun']),
              array('currency',$dane_waluta[0]),
              array('currency_value',(float)$dane_waluta[1]),
              array('payment_method',$filtr->process($wartosc_platnosci[1])),
              array('payment_info',$rodzajPlatnosciOpis),
              array('payment_method_class',$filtr->process($wartosc_platnosci[0])),
              array('payment_method_array',''),
              array('shipping_module',$dane_dostawa[1]));
              
      unset($dane_waluta, $dane_dostawa);

      $pola_dostawa_punkt = array();
      if ( isset($_POST['lokalizacja']) && $_POST['lokalizacja'] != '0' ) {
          $pola_dostawa_punkt = array(
                  array('shipping_info', $filtr->process($_POST['lokalizacja']) ));
      }
      if ( isset($_POST['lokalizacjaPunktOdbioru']) && $_POST['lokalizacjaPunktOdbioru'] != '' ) {
          $pola_dostawa_punkt = array(
                  array('shipping_info', $filtr->process($_POST['lokalizacjaPunktOdbioru']) ),
                  array('shipping_destinationcode', (isset($_POST['kodPunktuOdbioru']) ? $filtr->process($_POST['kodPunktuOdbioru']) : '' )),
              );
      }

      // jezeli adres dostawy taki sam jak faktury      
      if ( $_POST['adres_dostawy'] == '1' ) {
        
        $pola_dostawa = array(
                  array('delivery_name',$filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko'])),
                  array('delivery_company',( isset($_POST['nazwa_firmy']) && $_POST['nazwa_firmy'] != '' ? $filtr->process($_POST['nazwa_firmy']) : '' ) ),
                  array('delivery_nip',( isset($_POST['nip_firmy']) && $_POST['nip_firmy'] != '' ? $filtr->process($_POST['nip_firmy']) : '' ) ),
                  array('delivery_pesel',( isset($_POST['pesel']) && $_POST['pesel'] != '' ? $filtr->process($_POST['pesel']) : '' ) ),
                  array('delivery_street_address',$filtr->process($_POST['ulica'])),
                  array('delivery_city',$filtr->process($_POST['miasto'])),
                  array('delivery_postcode',$filtr->process($_POST['kod_pocztowy'])),
                  array('delivery_state',((isset($_POST['wojewodztwo'])) ? $filtr->process($_POST['wojewodztwo']) : '')),
                  array('delivery_country',$filtr->process($_POST['panstwo'])),
                  array('delivery_telephone', ( isset($_POST['telefon']) && $_POST['telefon'] != '' ? $filtr->process($_POST['telefon']) : ( isset($_POST['telefon']) && $_POST['telefon'] != '' ? $filtr->process($_POST['telefon']) : '' ) )));
       
      // jezeli adres dostawy wpisany z reki
      } if ( $_POST['adres_dostawy'] == '0' ) {
        
        $pola_dostawa = array(
                  array('delivery_name',$filtr->process($_POST['dostawa_imie']) . ' ' . $filtr->process($_POST['dostawa_nazwisko'])),
                  array('delivery_company',( isset($_POST['dostawa_nazwa_firmy']) && $_POST['dostawa_nazwa_firmy'] != '' ? $filtr->process($_POST['dostawa_nazwa_firmy']) : '' ) ),
                  array('delivery_nip',( isset($_POST['dostawa_nip_firmy']) && $_POST['dostawa_nip_firmy'] != '' ? $filtr->process($_POST['dostawa_nip_firmy']) : '' ) ),
                  array('delivery_street_address',$filtr->process($_POST['dostawa_ulica'])),
                  array('delivery_city',$filtr->process($_POST['dostawa_miasto'])),
                  array('delivery_postcode',$filtr->process($_POST['dostawa_kod_pocztowy'])),
                  array('delivery_state',(isset($_POST['dostawa_wojewodztwo']) ? $filtr->process($_POST['dostawa_wojewodztwo']) : '' )),
                  array('delivery_country',$filtr->process($_POST['dostawa_panstwo'])),
                  array('delivery_telephone', ( isset($_POST['dostawa_telefon']) && $_POST['dostawa_telefon'] != '' ? $filtr->process($_POST['dostawa_telefon']) : ( isset($_POST['telefon']) && $_POST['telefon'] != '' ? $filtr->process($_POST['telefon']) : '' ) )));
                  
      // jezeli adres dostawy z listy
      } if ( $_POST['adres_dostawy'] == '2' ) {
        
        $zapytanie_adres = "SELECT c.customers_id, 
                                   a.address_book_id, 
                                   a.entry_company, 
                                   a.entry_nip,
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
                             WHERE c.customers_id = '" . $info['customers_id'] . "' AND a.address_book_id = '" . $filtr->process($_POST['adres_lista_wybor']) . "'";
        
        $sql_adres = $db->open_query($zapytanie_adres); 
        $infa = $sql_adres->fetch_assoc();
                      
        $pola_dostawa = array(
                  array('delivery_name',$infa['entry_firstname'] . ' ' . $infa['entry_lastname']),
                  array('delivery_company',((!empty($infa['entry_company'])) ? $infa['entry_company'] : '' )),
                  array('delivery_nip',( !empty($infa['entry_company']) && !empty($infa['entry_nip']) ? $infa['entry_nip'] : '' )),
                  array('delivery_street_address',$infa['entry_street_address']),
                  array('delivery_city',$infa['entry_city']),
                  array('delivery_postcode',$infa['entry_postcode']),
                  array('delivery_state',Klienci::pokazNazweWojewodztwa($infa['entry_zone_id'])),
                  array('delivery_country',Klienci::pokazNazwePanstwa($infa['entry_country_id'])),
                  array('delivery_telephone', $infa['entry_telephone']));
                  
        $db->close_query($sql_adres); 
        unset($zapytanie_adres, $infa);
                               
      }
      $pola_platnik = array(
                array('billing_name',( !isset($_POST['nazwa_firmy']) || empty($_POST['nazwa_firmy']) ? $filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko']) : '')),
                array('billing_company',( isset($_POST['nazwa_firmy']) && $_POST['nazwa_firmy'] != '' ? $filtr->process($_POST['nazwa_firmy']) : '' ) ),
                array('billing_nip',( isset($_POST['nip_firmy']) && $_POST['nip_firmy'] != '' ? $filtr->process($_POST['nip_firmy']) : '' ) ),
                array('billing_pesel',( isset($_POST['pesel']) && $_POST['pesel'] != '' ? $filtr->process($_POST['pesel']) : '' ) ),
                array('billing_street_address',$filtr->process($_POST['ulica'])),
                array('billing_city',$filtr->process($_POST['miasto'])),
                array('billing_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('billing_state',((isset($_POST['wojewodztwo'])) ? $filtr->process($_POST['wojewodztwo']) : '')),
                array('billing_country',$filtr->process($_POST['panstwo'])));

      $pola = Array();
      $pola = array_merge( $pola_info, $pola_dostawa_punkt, $pola_dostawa, $pola_platnik );

      $db->insert_query('orders' , $pola);
      $id_dodanej_pozycji = $db->last_id_query();
      unset($pola);

      //
      $pola = array(
              array('orders_id ',(int)$id_dodanej_pozycji),
              array('orders_status_id',(int)$_POST['status']),
              array('date_added','now()'),
              array('customer_notified ','0'),
              array('customer_notified_sms','0'),
              array('comments',$filtr->process($_POST['komentarz'])),
              array('admin_id',(int)$_SESSION['userID']));

      $db->insert_query('orders_status_history' , $pola);
      unset($pola);

      $zamowienie = new Zamowienie($id_dodanej_pozycji);
      $suma = new SumaZamowienia();
      $tablica_modulow = $suma->przetwarzaj_moduly();
      
      foreach ( $tablica_modulow as $podsumowanie ) {

        $tekst = $waluty->FormatujCene($podsumowanie['wartosc']);
        
        // koszt dostawy
        if ( $podsumowanie['klasa'] == 'ot_shipping' && isset($_POST['koszt_dostawa']) && (float)$_POST['koszt_dostawa'] > 0 ) {
             $podsumowanie['wartosc'] = (float)$_POST['koszt_dostawa'];
        }

        $pola = array(
                  array('orders_id',(int)$id_dodanej_pozycji),
                  array('title', $podsumowanie['text'] ),
                  array('text', $tekst ),
                  array('value', (float)$podsumowanie['wartosc'] ),
                  array('prefix', $podsumowanie['prefix'] ),
                  array('class', $podsumowanie['klasa'] ),
                  array('sort_order', (int)$podsumowanie['sortowanie'] ));
                  
        if ( isset($podsumowanie['vat_id']) && isset($podsumowanie['vat_stawka']) ) {
            //
            $pola[] = array('tax',(float)$podsumowanie['vat_stawka']);
            $pola[] = array('tax_class_id',(int)$podsumowanie['vat_id']);
            //
        }                     

        $db->insert_query('orders_total' , $pola);
        
      }
      
      unset($_SESSION['koszyk']);
  
      if ( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) {
        
           Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.$id_dodanej_pozycji.'&klient_id='.(int)$_POST["id"].'&zakladka=2');
           
      } else {
        
           Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.$id_dodanej_pozycji.'&klient_id='.(int)$_POST["id"]);
           
      }
      
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

        <?php
        $tablica_wysylek_wlaczonych = Array();
        $tablica_wysylek_wlaczonych = Sprzedaz::ListaWysylekWlaczonych();
        
          $dhl = false;
          $paczkomaty = false;
          $ruch = false;
          $ruch_klucz = '';
          $poczta = false;
          $dpd = false;
          $gls = false;
          $bliskapaczka = false;
          $bliskapaczka_klucz_google = '';
          $bliskapaczka_klucz = '';
        //
        foreach ( $tablica_wysylek_wlaczonych as $tmp ) {
            //
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_inpost' ) {
                 $paczkomaty = true;
            }
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_inpost_eko' ) {
                 $paczkomaty = true;
            }            
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_dhlparcelshop' ) {
                 $dhl = true;
            }
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_pocztapunkt' ) {
                 $poczta = true;
            }
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_paczkaruch' ) {
                 $ruch = true;
            }  
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_dpdpickup' ) {
                 $dpd = true;
            }  
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_glspickup' ) {
                 $gls = true;
            }  
            if ( isset($tmp['WYSYLKA_GABARYT']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_bliskapaczka' ) {
                 $bliskapaczka = true;
            }  
            if ( isset($tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']) && $tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']['id'] == 'wysylka_bliskapaczka' ) {
                 $bliskapaczka_klucz = $tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']['value'];
            }                
            if ( isset($tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']) && $tmp['WYSYLKA_KLUCZ_API']['id'] == 'wysylka_bliskapaczka' ) {
                 $bliskapaczka_klucz_google = $tmp['WYSYLKA_KLUCZ_API']['value'];
            }                
            //
        }
        //
        unset($tablica_wysylek_wlaczonych);
        ?>

        <script>
        $(document).ready(function() {

            $("#zamowieniaForm").validate({
              rules: {
                imie: {
                  required: true
                },
                nazwisko: {
                  required: true
                },
                email: {
                  required: true
                },
                nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }},
                nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik;}},
                ulica: {
                  required: true
                },
                kod_pocztowy: {
                  required: true
                },
                miasto: {
                  required: true
                },
                panstwo: {
                  required: true
                },
                dostawa_ulica: {required: function() {var wynik = false; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "0" ) { wynik = true; } return wynik; }},
                dostawa_kod_pocztowy: {required: function() {var wynik = false; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "0" ) { wynik = true; } return wynik; }},
                dostawa_miasto: {required: function() {var wynik = false; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "0" ) { wynik = true; } return wynik; }},
                dostawa_panstwo: {required: function() {var wynik = false; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "0" ) { wynik = true; } return wynik; }}
              }
            });

            $.AutoUzupelnienie( 'panstwo', 'Podpowiedzi', 'ajax/autouzupelnienie_kraje.php', 50, 400 );
            $.AutoUzupelnienie( 'wojewodztwo', 'Podpowiedzi', 'ajax/autouzupelnienie_wojewodztwa.php', 50, 400 );
            $.AutoUzupelnienie( 'dostawa_panstwo', 'Podpowiedzi', 'ajax/autouzupelnienie_kraje.php', 50, 400 );
            $.AutoUzupelnienie( 'dostawa_wojewodztwo', 'Podpowiedzi', 'ajax/autouzupelnienie_wojewodztwa.php', 50, 400 );

            $("#selection").change( function() {
            $("#WidgetButtonInpost").hide();
            $("#WidgetButtonRuch").hide();
            $("#WidgetButtonDhl").hide();
            $("#WidgetButtonPoczta").hide();
            $("#WidgetButtonBliskaPaczka").hide();
            $("#WidgetButtonDpd").hide();
            $("#WidgetButtonGls").hide();

            $("#lokalizacje").show();
            $("#selectionresult").html('<img src="obrazki/_loader_small.gif">');
            var dane = $(this).val();
            var id = dane.split('|');
            $.ajax({
                type: "POST",
                data: "data=" + id[0],
                url: "ajax/wybor_lokalizacji_dostawy.php",
                success: function(msg){
                  if (msg != '') { 
                      $("#lokalizacje").slideDown(); 
                      $("#selectionresult").html(msg).show(); 
                  } else { 
                      $("#selectionresult").html(''); 
                      $("#lokalizacje").slideUp(); 
                  }
                }
            });
            });
            
            $('.inputKlienta').click( function() {
                $('#dodajZamowienie').fadeIn('fast');
            });    

            $('#dodajKlienta').click(function() {
               $('#dodawanieKlienta').slideDown();
               $('#dodajKlienta').slideUp();
            })
            
            pokazChmurki();
        });
        
        function fraza_klienci() { 
            //
            if ( $('#szukany').val().trim() == '' ) {
                //
                $.colorbox( { html:'<div id="PopUpInfo">Nie została podana szukana wartość.</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                //
             } else {
                //
                $('#dodajZamowienie').hide();
                //
                $('#wybierz_klienta').html('<img src="obrazki/_loader_small.gif">');
                $.get("ajax/lista_klientow.php", 
                    { fraza: $('#szukany').val(), tok: $('#tok').val() },
                    function(data) { 
                        $('#wybierz_klienta').css('display','none');
                        $('#wybierz_klienta').html(data);
                        $('#wybierz_klienta').css('display','block'); 
                        //
                        pokazChmurki();
                });    
                // 
             }
        }
        </script>

        <?php if ( $paczkomaty == true ) { ?>
        
            <script async src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>
            <link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css"/>
            
            <style>
            .MapyUkryteWysylkaPaczkomaty { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }
            .MapyWidoczneWysylkaPaczkomaty { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99997; 
              transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
            }
            #MapyWidoczneWysylkaTloPaczkomaty { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99998; background:#000000; opacity:0.6; filter:alpha(opacity=60); }
            #WyborMapaWysylkaPaczkomaty { position:absolute; z-index:99999; }
            
            #MapaKontenerWysylkaPaczkomaty { position:relative; overflow:visible; margin:0px auto; }
            
            #WidokMapWysylkaPaczkomaty { overflow:hidden; background:#fff; border:2px solid #fff;
              -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                        
            }
            
            #MapaZamknijWysylkaPaczkomaty { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
            
            @media only screen and (max-width:1023px) {
              #MapaZamknijWysylkaPaczkomaty { right:0px; top:-40px; }
              #WyborMapaWysylkaPaczkomaty { top:10%; bottom:10%; right:3%; left:3%; }
              #MapaKontenerWysylkaPaczkomaty { width:100%; height:100%; }
              #WidokMapWysylkaPaczkomaty { min-height:500px; max-height:100%; }
            }
            @media only screen and (max-width:1023px) and (max-height:600px) {
              #WyborMapaWysylkaPaczkomaty { top:7%; bottom:5%; }
              #MapaZamknijWysylkaPaczkomaty { right:-5px; top:-13px; }
              #WidokMapWysylkaPaczkomaty { min-height:50px; max-height:100%; }
            }
            @media only screen and (min-width:1024px) {
              #MapaZamknijWysylkaPaczkomaty { right:-13px; top:-13px; }
              #WyborMapaWysylkaPaczkomaty { top:50%; margin-top:-250px; right:0%; left:0%; }
              #MapaKontenerWysylkaPaczkomaty { width:800px; height:500px; }
              #WidokMapWysylkaPaczkomaty { min-height:500px; max-height:100%; }
            }   
            @media only screen and (min-width:1024px) and (max-height:600px) {
              #MapaZamknijWysylkaPaczkomaty { right:-13px; top:-13px; }
              #WyborMapaWysylkaPaczkomaty { top:5%; bottom:5%; margin-top:0px; }
              #MapaKontenerWysylkaPaczkomaty { height:100%; }
              #WidokMapWysylkaPaczkomaty { min-height:50px; max-height:100%; }
            }   
            #paczkomat { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
            }                        
            </style>           
            
            <script>    
            
            $(window).load(function() {
            
                $("body").append("<div class='MapyUkryteWysylkaPaczkomaty' id='WybierzMapyWysylkaPaczkomaty'><div id='MapyWidoczneWysylkaTloPaczkomaty'></div><div id='WyborMapaWysylkaPaczkomaty'><div id='MapaKontenerWysylkaPaczkomaty'><div id='MapaZamknijWysylkaPaczkomaty'>X</div><div id='WidokMapWysylkaPaczkomaty'></div></div></div></div>");

                $("#WidgetButtonInpost").click(function() {
                   $("#WybierzMapyWysylkaPaczkomaty").removeClass("MapyUkryteWysylkaPaczkomaty").addClass("MapyWidoczneWysylkaPaczkomaty");
                   
                });
                $("#MapaZamknijWysylkaPaczkomaty").click(function() {
                   $("#WybierzMapyWysylkaPaczkomaty").removeClass("MapyWidoczneWysylkaPaczkomaty").addClass("MapyUkryteWysylkaPaczkomaty");
                });   
            
                window.easyPackAsyncInit = function () {
                    easyPack.init({       
                        defaultLocale: 'pl',
                        mapType: 'osm',
                        searchType: 'osm',
                        mobileSize: 2000,
                        points: {
                            types: ['parcel_locker'],
                            functions: ['parcel_collect']
                        },
                        map: {
                            initialTypes: ['parcel_locker']
                        }              
                    });
                    var map = easyPack.mapWidget('WidokMapWysylkaPaczkomaty', function(point) {
                    
                        $("#PaczkomatOpis").val(point.address["line1"] + ", " + point.address["line2"]);
                        $("#PaczkomatID").val(point.name);
                        
                        $(".WybranyPunktMapyPaczkomaty").show(); 
                        $("#WybierzMapyWysylkaPaczkomaty").removeClass("MapyWidoczneWysylkaPaczkomaty").addClass("MapyUkryteWysylkaPaczkomaty")

                    });
                    
                } 
            
            });
            
            </script>     

        <?php } ?>

        <?php if ( $ruch == true ) { ?>
        
            <link rel="stylesheet" type="text/css" href="https://mapka.paczkawruchu.pl/paczkawruchu.css" />
            <script type="text/javascript" src="//maps.google.com/maps/api/js?language=pl&libraries=places&v=3.37<?php echo '&key='.$ruch_klucz; ?>"></script>
            <script type="text/javascript" src="https://mapka.paczkawruchu.pl/jquery.pwrgeopicker.js"></script>
            
            <style>
            .pac-container { display:none !important; }
            .MapyUkryteWysylkaRuch { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }
            .MapyWidoczneWysylkaRuch { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99997; 
              transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
            }
            #MapyWidoczneWysylkaTloRuch { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99998; background:#000000; opacity:0.6; filter:alpha(opacity=60); }
            #WyborMapaWysylkaRuch { position:absolute; z-index:99999; }
            
            #MapaKontenerWysylkaRuch { position:relative; overflow:visible; margin:0px auto; }
            
            #WidokMapWysylkaRuch { position:relative; overflow:hidden; background:#fff; border:2px solid #fff;
              -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                           
            }

            #MapaZamknijWysylkaRuch { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
            
            @media only screen and (max-width:1023px) {
              #MapaZamknijWysylkaRuch { right:0px; top:-40px; }
              #WyborMapaWysylkaRuch { top:10%; right:3%; left:3%; }
              #MapaKontenerWysylkaRuch { max-width:524px; height:415px; }
              #WidokMapWysylkaRuch { max-width:524px; overflow-x:scroll; }
            }
            @media only screen and (max-width:1023px) and (max-height:460px) {
              #MapaZamknijWysylkaRuch { right:-5px; top:-13px; }
              #WyborMapaWysylkaRuch { top:5%; bottom:5%; }
              #MapaKontenerWysylkaRuch { max-height:100%; }
              #WidokMapWysylkaRuch { height:100%; overflow-y:scroll; } 
            }                      
            @media only screen and (min-width:1024px) {
              #MapaZamknijWysylkaRuch { right:-15px; top:-15px; }
              #WyborMapaWysylkaRuch { top:50%; margin-top:-208px; right:0%; left:0%; }
              #WidokMapWysylkaRuch, #MapaKontenerWysylkaRuch { width:506px; height:415px; }
            }    
            @media only screen and (min-width:1024px) and (max-height:460px) {
              #MapaZamknijWysylkaRuch { right:-13px; top:-13px; }
              #WyborMapaWysylkaRuch { top:5%; bottom:5%; margin-top:0px; }
              #MapaKontenerWysylkaRuch { width:524px; height:100%; }
              #WidokMapWysylkaRuch { width:524px; height:100%; overflow-y:scroll; }
            }       
            #kioskRuchu { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
            }                        
            </style>           

            <script>
            $(window).load(function() {
              
                $('body').append("<div class='MapyUkryteWysylkaRuch' id='WybierzMapyWysylka'><div id='MapyWidoczneWysylkaTloRuch'></div><div id='WyborMapaWysylkaRuch'><div id='MapaKontenerWysylkaRuch'><div id='MapaZamknijWysylkaRuch'>X</div><div id='WidokMapWysylkaRuch'></div></div></div></div>");
              
                $('#WidgetButtonRuch').click(function() {
                   $('#WybierzMapyWysylka').removeClass('MapyUkryteWysylkaRuch').addClass('MapyWidoczneWysylkaRuch');
                   
                });
                $('#MapaZamknijWysylkaRuch').click(function() {
                   $('#WybierzMapyWysylka').removeClass('MapyWidoczneWysylkaRuch').addClass('MapyUkryteWysylkaRuch');
                });     

                $('#WidokMapWysylkaRuch').pwrgeopicker('inline', {
                    'form': {
                        'city': 'Warszawa',
                        'street': 'Marszałkowska'
                    },
                    'popup': true,
                    'autocomplete': true,
                    'auto_start': true,
                    'max_points' : 20,
                    'onselect': function(data){
                      
                        $('#PunktRuchOpis').val(data['City'] + ' - ' + data['StreetName']);
                        $('#PunktRuchID').val(data['DestinationCode']);                      
                        
                        $('.WybranyPunktMapyRuch').show(); 
                        $('#WybierzMapyWysylka').removeClass('MapyWidoczneWysylkaRuch').addClass('MapyUkryteWysylkaRuch');
                        
                    }
                    
                });
                
            });
            </script>

        <?php } ?>
        
        <?php if ( $dhl == true ) { ?>
        
            <style>
            .MapyUkryteWysylkaDhl { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }
            .MapyWidoczneWysylkaDhl { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99997; 
              transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
            }
            #MapyWidoczneWysylkaTloDhl { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99998; background:#000000; opacity:0.6; filter:alpha(opacity=60); }
            #WyborMapaWysylkaDhl { position:absolute; z-index:99999; }
            
            #MapaKontenerWysylkaDhl { position:relative; overflow:visible; margin:0px auto; }
            
            #WidokMapWysylkaDhl { position:relative; overflow:hidden; background:#fff; border:2px solid #fff;
              -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                           
            }

            #MapaZamknijWysylkaDhl { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
            
            @media only screen and (max-width:1023px) {
              #MapaZamknijWysylkaDhl { right:0px; top:-40px; }
              #WyborMapaWysylkaDhl { top:10%; bottom:10%; right:3%; left:3%; }
              #MapaKontenerWysylkaDhl { width:100%; height:100%; }
              #WidokMapWysylkaDhl { height:100%; }
              #WidokMapWysylkaDhl iframe { position:absolute; top:0px; left:0px; right:0px; bottom:0px; height:100%; width:100%; }   
            }
            @media only screen and (max-width:1023px) and (max-height:600px) {
              #MapaZamknijWysylkaDhl { right:-5px; top:-13px; }
              #WyborMapaWysylkaDhl { top:5%; bottom:5%; }
            }                      
            @media only screen and (min-width:1024px) {
              #MapaZamknijWysylkaDhl { right:-15px; top:-15px; }
              #WyborMapaWysylkaDhl { top:50%; margin-top:-250px; right:0%; left:0%; }
              #MapaKontenerWysylkaDhl { width:800px; height:500px; }
              #WidokMapWysylkaDhl, #WidokMapWysylkaDhl iframe { width:800px; height:500px; }               
            }    
            @media only screen and (min-width:1024px) and (max-height:600px) {
              #MapaZamknijWysylkaDhl { right:-13px; top:-13px; }
              #WyborMapaWysylkaDhl { top:5%; bottom:5%; margin-top:0px; }
              #MapaKontenerWysylkaDhl { height:100%; }
              #WidokMapWysylkaDhl, #WidokMapWysylkaDhl iframe { height:100%; }
            }    
            #punktDhl { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
            }                        
            </style>          

            <script>
            $(window).load(function() {
            
                $("body").append("<div class='MapyUkryteWysylkaDhl' id='WybierzMapyWysylkaDhl'><div id='MapyWidoczneWysylkaTloDhl'></div><div id='WyborMapaWysylkaDhl'><div id='MapaKontenerWysylkaDhl'><div id='MapaZamknijWysylkaDhl'>X</div><div id='WidokMapWysylkaDhl'><iframe frameborder='0' src='https://parcelshop.dhl.pl/mapa'></iframe></div></div></div></div>");

                $("#WidgetButtonDhl").click(function() {
                   $("#WybierzMapyWysylkaDhl").removeClass("MapyUkryteWysylkaDhl").addClass("MapyWidoczneWysylkaDhl");
                   
                });
                $("#MapaZamknijWysylkaDhl").click(function() {
                   $("#WybierzMapyWysylkaDhl").removeClass("MapyWidoczneWysylkaDhl").addClass("MapyUkryteWysylkaDhl");
                });         

                function IsJsonString(str) {
                  try {
                    var json = JSON.parse(str);
                    return (typeof json === 'object');
                  } catch (e) {
                    return false;
                  }
                }            

                function listenMessage(msg) {
                  
                    if ( IsJsonString(msg.data) ) {
                      
                         var point = JSON.parse(msg.data);
                
                         var opis_punkt = point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + ", " + point.name;
                         $('#DhlOpis').val(opis_punkt);
                         $('#DhlID').val(point.sap);            

                         $(".WybranyPunktMapyDhl").show(); 
                         $("#WybierzMapyWysylkaDhl").removeClass("MapyWidoczneWysylkaDhl").addClass("MapyUkryteWysylkaDhl");
                        
                    }
                        
                };

                if (window.addEventListener) {
                    window.addEventListener("message", listenMessage, false);
                } else {
                    window.attachEvent("onmessage", listenMessage);
                }

            
            });                   

            </script>

        <?php } ?>        
        
        <?php if ( $poczta == true ) { ?>
        
            <script src="https://mapa.ecommerce.poczta-polska.pl/widget/scripts/ppwidget.js"></script>
            
            <style>
            #punktPoczta { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
              -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
              -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
            }                         
            </style>          

            <script>
            $(window).load(function() {
                                  
                var pobranie = true;
                var address = "Warszawa";

                $('#WidgetButtonPoczta').click(function() {

                    PPWidgetApp.toggleMap(function(callback) {
                      
                        var value = callback['name'] + ' - ' + callback['city'] + ' - ' + callback['street']; 
                        var punktodbioru = callback['pni']; 
                        var opis_punkt = callback['description'];
                        opis_punkt = opis_punkt.replace(/#/gi,'<br />');
                        
                        $('#PocztaOpis').val(callback['name'] + ' - ' + callback['city'] + ' - ' + callback['street']);
                        $('#PocztaID').val(punktodbioru);   

                    },
                    pobranie, 
                    address
                    );
                    
                });
                
            });                            

            </script>

        <?php } ?>

        <?php if ( $bliskapaczka == true ) { ?>
        
            <style>
                      .pac-container { display:none !important; }
                      .MapyUkryteWysylkaBliskaPaczka { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }
                      .MapyWidoczneWysylkaBliskaPaczka { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99997; 
                        transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
                      }
                      #MapyWidoczneWysylkaTloBliskaPaczka { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99998; background:#000000; opacity:0.6; filter:alpha(opacity=60); }
                      #WyborMapaWysylkaBliskaPaczka { position:absolute; z-index:99999; }
                      
                      #MapaKontenerWysylkaBliskaPaczka { position:relative; overflow:visible; margin:0px auto; }
                      
                      #WidokMapWysylkaBliskaPaczka { position:relative; overflow:hidden; background:#fff; border:2px solid #fff;
                        -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
                        -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                        -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                           
                      }

                      #MapaZamknijWysylkaBliskaPaczka { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
                      
                      @media only screen and (max-width:1023px) {
                        #MapaZamknijWysylkaBliskaPaczka { right:0px; top:-40px; }
                        #WyborMapaWysylkaBliskaPaczka { top:10%; right:3%; left:3%; }
                        #MapaKontenerWysylkaBliskaPaczka { max-width:524px; height:415px; }
                        #WidokMapWysylkaBliskaPaczka { height:650px;max-width:524px; overflow-x:scroll; }
                      }
                      @media only screen and (max-width:1023px) and (max-height:460px) {
                        #MapaZamknijWysylkaBliskaPaczka { right:-5px; top:-13px; }
                        #WyborMapaWysylkaBliskaPaczka { top:5%; bottom:5%; }
                        #MapaKontenerWysylkaBliskaPaczka { max-height:100%; }
                        #WidokMapWysylkaBliskaPaczka { height:650px; overflow-y:scroll; } 
                      }                      
                      @media only screen and (min-width:1024px) {
                        #MapaZamknijWysylkaBliskaPaczka { right:-15px; top:-15px; }
                        #WyborMapaWysylkaBliskaPaczka { top:50%; margin-top:-325px; right:0%; left:0%; }
                        #WidokMapWysylkaBliskaPaczka, #MapaKontenerWysylkaBliskaPaczka { width:992px; height:650px; }
                      }    
                      @media only screen and (min-width:1024px) and (max-height:460px) {
                        #MapaZamknijWysylkaBliskaPaczka { right:-13px; top:-13px; }
                        #WyborMapaWysylkaBliskaPaczka { top:5%; bottom:5%; margin-top:0px; }
                        #MapaKontenerWysylkaBliskaPaczka { width:992px; height:100%; }
                        #WidokMapWysylkaBliskaPaczka { width:992px; height:100%; overflow-y:scroll; }
                      }       
                      #punktBliskaPaczka { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
                        -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                        -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
                      }                        
            </style>


            <script async type="text/javascript" src="https://widget.bliskapaczka.pl/v5.15/main.js"></script>
            <link rel="stylesheet" href="https://widget.bliskapaczka.pl/v5.15/main.css" />
            
            <script>
            
            $(window).load(function() {

                $("body").append("<div class='MapyUkryteWysylkaBliskaPaczka' id='WybierzMapyWysylka'><div id='MapyWidoczneWysylkaTloBliskaPaczka'></div><div id='WyborMapaWysylkaBliskaPaczka'><div id='MapaKontenerWysylkaBliskaPaczka'><div id='MapaZamknijWysylkaBliskaPaczka'>X</div><div id='WidokMapWysylkaBliskaPaczka'></div></div></div></div>");

              
                $("#WidgetButtonBliskaPaczka").click(function() {
                   $("#WybierzMapyWysylka").removeClass("MapyUkryteWysylkaBliskaPaczka").addClass("MapyWidoczneWysylkaBliskaPaczka");
                   
                });

                $("#MapaZamknijWysylkaBliskaPaczka").click(function() {
                   $("#WybierzMapyWysylka").removeClass("MapyWidoczneWysylkaBliskaPaczka").addClass("MapyUkryteWysylkaBliskaPaczka");
                });

                BPWidget.init(
                    document.getElementById('WidokMapWysylkaBliskaPaczka'),
                    {
                        googleMapApiKey: '<?php echo $bliskapaczka_klucz_google; ?>',
                        callback: function(point) {
                            console.log('point code:', point.code);
                            var value = point.operator + ' - ' + ( point.description != null ? point.description  + '; ' : '') + point.street + ', ' + point.postalCode + ' ' + point.city;
                            var punktodbioru = point.code;
                            var opis_punkt = point.description;
                            $("#BliskaPaczkaOpis").val(value);
                            $("#BliskaPaczkaID").val(point.code);
                            $("#WybierzMapyWysylka").removeClass("MapyWidoczneWysylkaBliskaPaczka").addClass("MapyUkryteWysylkaBliskaPaczka");

                        },
                        posType: 'DELIVERY',
                        codOnly: false
                    }
                );
                
            });
            </script>

        <?php } ?>               

        <?php if ( $dpd == true ) { ?>
        
            <style>
                .MapyUkryteWysylkaDpd { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }
                .MapyWidoczneWysylkaDpd { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99997; 
                  transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
                }
                #MapyWidoczneWysylkaTloDpd { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99998; background:#000000; opacity:0.6; filter:alpha(opacity=60); }
                #WyborMapaWysylkaDpd { position:absolute; z-index:99999; }
                
                #MapaKontenerWysylkaDpd { position:relative; overflow:visible; margin:0px auto; }
                
                #WidokMapWysylkaDpd { position:relative; overflow:hidden; background:#fff; border:2px solid #fff;
                  -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
                  -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                  -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                           
                }

                #MapaZamknijWysylkaDpd { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
                
                @media only screen and (max-width:1023px) {
                  #MapaZamknijWysylkaDpd { right:0px; top:-40px; }
                  #WyborMapaWysylkaDpd { top:10%; right:3%; left:3%; }
                  #MapaKontenerWysylkaDpd { max-width:524px; height:415px; }
                  #WidokMapWysylkaDpd { max-width:524px; overflow-x:scroll; }
                }
                @media only screen and (max-width:1023px) and (max-height:460px) {
                  #MapaZamknijWysylkaDpd { right:-5px; top:-13px; }
                  #WyborMapaWysylkaDpd { top:5%; bottom:5%; }
                  #MapaKontenerWysylkaDpd { max-height:100%; }
                  #WidokMapWysylkaDpd { height:100%; overflow-y:scroll; } 
                }                      
                @media only screen and (min-width:1024px) {
                  #MapaZamknijWysylkaDpd { right:-15px; top:-15px; }
                  #WyborMapaWysylkaDpd { top:50%; margin-top:-350px; right:0%; left:0%; }
                  #WidokMapWysylkaDpd, #MapaKontenerWysylkaDpd { width:1000px; height:600px; }
                  #WidokMapWysylkaDPD, #MapaKontenerWysylkaDpd iframe { width:1000px; height:600px; }               
                }    
                @media only screen and (min-width:1024px) and (max-height:600px) {
                  #MapaZamknijWysylkaDpd { right:-13px; top:-13px; }
                  #WyborMapaWysylkaDpd { top:5%; bottom:5%; margin-top:0px; }
                  #MapaKontenerWysylkaDpd { width:800px; height:100%; }
                  #WidokMapWysylkaDpd { width:800px; height:100%; overflow-y:scroll; }
                }       
                #kioskDpdu { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
                  -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                  -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
                }                        
            </style>           

            <script>    
            
            $(window).load(function() {

                $("body").append("<div class='MapyUkryteWysylkaDpd' id='WybierzMapyWysylkaDpd'><div id='MapyWidoczneWysylkaTloDpd'></div><div id='WyborMapaWysylkaDpd'><div id='MapaKontenerWysylkaDpd'><div id='MapaZamknijWysylkaDpd'>X</div><div id='WidokMapWysylkaDpd'><div id='MapaZawartoscDPD'></div></div></div></div></div>");

                $("#WidgetButtonDpd").click(function() {
                   $("#WybierzMapyWysylkaDpd").removeClass("MapyUkryteWysylkaDpd").addClass("MapyWidoczneWysylkaDpd");
                   
                });
                $("#MapaZamknijWysylkaDpd").click(function() {
                   $("#WybierzMapyWysylkaDpd").removeClass("MapyWidoczneWysylkaDpd").addClass("MapyUkryteWysylkaDpd");
                });   
            
                var iframe = document.createElement("iframe");
                iframe.src = '//pudofinder.dpd.com.pl/widget?key=a9b08eb6c1b218b975529f9cea0e1364';
                iframe.style.width = "100%";
                iframe.style.border = "none";
                iframe.style.minHeight = "600px";

                var zawartosc = document.getElementById("MapaZawartoscDPD");
                zawartosc.parentNode.insertBefore(iframe, zawartosc);

                var eventListener = window[window.addEventListener ? "addEventListener" : "attachEvent"];
                var messageEvent = ("attachEvent" == eventListener)? "onmessage" : "message";

                eventListener(messageEvent, function(a) {
                if (a.data.height && !isNaN(a.data.height)) {

                    iframe.style.height = a.data.height + "px"

                } else if( a.data.point_id) {
                    var value = a.data.point_id;
                    $('#DpdID').val(value);
                    $('#DpdOpis').val(value);
                    $("#WybierzMapyWysylkaDpd").removeClass("MapyWidoczneWysylkaDpd").addClass("MapyUkryteWysylkaDpd");
                    $("#MapaWidocznaTlo").fadeOut(500, function() {
                        $("#MapaWidocznaTlo").remove();
                    });
                }

                }, !1);

            
            });
            
            </script>     

        <?php } ?>

        <?php if ( $gls == true ) { ?>
        
            <script src="https://mapa.gls-poland.com/js/v4.0/maps_sdk.js"></script>

                <style>
                    .MapyUkryteWysylkaGls { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }
                    .MapyWidoczneWysylkaGls { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99997; 
                      transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
                    }
                    #MapyWidoczneWysylkaTloGls { position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:99998; background:#000000; opacity:0.6; filter:alpha(opacity=60); }
                    #WyborMapaWysylkaGls { position:absolute; z-index:99999; }
                    
                    #MapaKontenerWysylkaGls { position:relative; overflow:visible; margin:0px auto; }
                    
                    #WidokMapWysylkaGls { position:relative; overflow:hidden; background:#fff; border:2px solid #fff;
                      -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
                      -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                      -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                           
                    }

                    #MapaZamknijWysylkaGls { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
                    
                    @media only screen and (max-width:1023px) {
                      #MapaZamknijWysylkaGls { right:0px; top:-40px; }
                      #WyborMapaWysylkaGls { top:10%; right:3%; left:3%; }
                      #MapaKontenerWysylkaGls { max-width:524px; height:415px; }
                      #WidokMapWysylkaGls { max-width:524px; overflow-x:scroll; }
                    }
                    @media only screen and (max-width:1023px) and (max-height:460px) {
                      #MapaZamknijWysylkaGls { right:-5px; top:-13px; }
                      #WyborMapaWysylkaGls { top:5%; bottom:5%; }
                      #MapaKontenerWysylkaGls { max-height:100%; }
                      #WidokMapWysylkaGls { height:100%; overflow-y:scroll; } 
                    }                      
                    @media only screen and (min-width:1024px) {
                      #MapaZamknijWysylkaGls { right:-15px; top:-15px; }
                      #WyborMapaWysylkaGls { top:50%; margin-top:-350px; right:0%; left:0%; }
                      #WidokMapWysylkaGls, #MapaKontenerWysylkaGls { width:1000px; height:600px; }
                    }    
                    @media only screen and (min-width:1024px) and (max-height:600px) {
                      #MapaZamknijWysylkaGls { right:-13px; top:-13px; }
                      #WyborMapaWysylkaGls { top:5%; bottom:5%; margin-top:0px; }
                      #MapaKontenerWysylkaGls { width:800px; height:100%; }
                      #WidokMapWysylkaGls { width:800px; height:100%; overflow-y:scroll; }
                    }       
                    #kioskGlsu { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
                      -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                      -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
                    }                        
                </style>

            <script>    
            
            $(window).load(function() {

                $("body").append("<div class='MapyUkryteWysylkaGls' id='WybierzMapyWysylkaGls'><div id='MapyWidoczneWysylkaTloGls'></div><div id='WyborMapaWysylkaGls'><div id='MapaKontenerWysylkaGls'><div id='MapaZamknijWysylkaGls'>X</div><div id='WidokMapWysylkaGls'><div id='MapaZawartoscGls'><div id='map_gls' class='map' style='display:flex;width:100%;height:600px;'></div></div></div></div></div></div>");

                $("#WidgetButtonGls").click(function() {
                   $("#WybierzMapyWysylkaGls").removeClass("MapyUkryteWysylkaGls").addClass("MapyWidoczneWysylkaGls");
                   
                });
                $("#MapaZamknijWysylkaGls").click(function() {
                   $("#WybierzMapyWysylkaGls").removeClass("MapyWidoczneWysylkaGls").addClass("MapyUkryteWysylkaGls");
                });   
            
                SzybkaPaczkaMap.init({
                    lang: 'PL',
                    country_parcelshops: 'PL',
                    el: 'map_gls',
                    geolocation: false,
                    map_type: false,
                    parcel_weight: '5',
                    center_point: ''
                });

                window.addEventListener('get_parcel_shop',function(e){
                    $('#GlsOpis').val(e.target.ParcelShop.selected.street + ', ' + e.target.ParcelShop.selected.postal_code + ' ' + e.target.ParcelShop.selected.city + ', ' + e.target.ParcelShop.selected.name);
                    $('#GlsID').val(e.target.ParcelShop.selected.id);
                    $("#WybierzMapyWysylkaGls").removeClass("MapyWidoczneWysylkaGls").addClass("MapyUkryteWysylkaGls");
                })

            });
            
            </script>     

        <?php } ?>

        <?php
        // jezeli jest dodawanie zamowienia z poziomu menu klienci
        if ( isset($_GET['klient']) && (int)$_GET['klient'] > 0 ) {
            //
            $zapytanie = "select customers_id from customers where customers_id = '" . (int)$_GET['klient'] . "'";
            $sql = $db->open_query($zapytanie);          
            //
            if ((int)$db->ile_rekordow($sql) > 0) {
                $_GET['klient_id'] = (int)$_GET['klient'];
            }
            //
            $db->close_query($sql); 
            //
        }
        
        if ( !isset($_GET['klient_id']) || $_GET['klient_id'] == '' ) { ?>
        
          <div class="poleForm">

            <div class="naglowek">Wybierz klienta</div>
            
            <form action="sprzedaz/zamowienia_dodaj.php" method="get" id="zamowieniaForm" class="cmxform"> 
            
            <div class="pozycja_edytowana">

              <div class="info_content">

                <?php
                $tablica_klientow = Klienci::ListaKlientow( false );
                ?>
                
                <div style="margin:5px;" id="fraza">
                    <div>Wyszukaj klienta: <input type="text" size="15" value="" id="szukany" class="DlugiInput" /><em class="TipIkona"><b>Wpisz nazwisko imię, klienta, nazwę firmy, NIP lub adres email</b></em></div> <span onclick="fraza_klienci()" ></span>
                </div>                    
                
                <div class="ObramowanieTabeli WyborKlientaDoZamowienia" id="wybierz_klienta">
                
                  <?php if ( count($tablica_klientow) < 1000 ) { ?>
                
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td>Wybierz</td>
                      <td>ID</td>
                      <td>Klient</td>
                      <td>Firma</td>
                      <td>Adres</td>
                      <td>Rabat indywidualny</td>
                      <td>Grupa</td>
                      <td>Kontakt</td>
                    </tr>           

                    <?php
                    foreach ( $tablica_klientow as $klient) {
                        //
                        echo '<tr class="pozycja_off">';
                        echo '<td><input class="inputKlienta" type="radio" name="klient_id" id="klient_id_' . $klient['id'] . '" value="' . $klient['id'] . '" /><label class="OpisForPustyLabel" for="klient_id_' . $klient['id'] . '"></label></td>';
                        echo '<td>' . $klient['id'] . '</td>';
                        echo '<td>' . $klient['nazwa'] . (($klient['gosc'] == 1) ? '<em class="TipChmurka" style="float:right"><b>Klient bez rejestracji</b><img src="obrazki/gosc.png" alt="Klient bez rejestracji" /></em>' : '') . '</td>';
                        
                        if ( !empty($klient['firma']) ) {
                             echo '<td><span class="Firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                           } else{
                             echo '<td></td>';
                        }
                        
                        echo '<td>' . $klient['adres'] . '</td>';
                        echo '<td>' . (($klient['rabat'] != 0) ? $klient['rabat'] . '%' : ''). '</td>';
                        echo '<td>' . (($klient['gosc'] == 1) ? '-' : $klient['grupa']) . '</td>';
                        echo '<td><span class="MalyMail">' . $klient['email'] . '</span>' . ((!empty($klient['telefon'])) ? '<br /><span class="MalyTelefon">' . $klient['telefon'] . '</span>' : '') . '</td>';
                        echo '</tr>';
                        //
                    }
                    ?>
                    
                  </table>
                  
                  <?php } else { ?>
                  
                    <span class="maleInfo" style="font-weight:normal">Wyszukaj klienta przy użyciu wyszukiwarki</span>
                  
                  <?php } ?>
                  
                </div>
              
              </div>
            
            </div>

            <div class="przyciski_dolne">
              <?php
              if ( count($tablica_klientow) > 0 ) {
              ?>
              <input type="submit" class="przyciskNon" id="dodajZamowienie" style="display:none" value="Dodaj zamówienie" />
              <?php 
              }
              ?>
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button>   
            </div>   

            </form>
            
            <span id="dodajKlienta" class="DodajNowegoKlienta">dodaj nowego klienta</span>
            
            <div id="dodawanieKlienta" class="FormNowegoKlienta" <?php echo ((isset($_SESSION['ceidg'])) ? 'style="display:block"' : ''); ?>>       

                <?php
                include('zamowienia_klient_dodaj.php');
                
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
                ?>
                
            </div>

          </div>

        <?php } else {  ?>
        
          <form action="sprzedaz/zamowienia_dodaj.php" method="post" id="zamowieniaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie nowego zamówienia</div>
            
            <input type="hidden" name="akcja" value="zapisz" />
            
            <input type="hidden" name="id" value="<?php echo (int)$_GET['klient_id']; ?>" />

            <?php
            $zapytanie = "select c.service, c.customers_id, c.language_id, c.customers_status, c.customers_dod_info, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_nip, a.entry_pesel, a.entry_street_address, a.entry_postcode, a.entry_city, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_groups_id, c.customers_discount, c.customers_default_address_id, c.customers_nick from customers c left join address_book a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . (int)$_GET['klient_id'] . "'";
            $sql = $db->open_query($zapytanie);

            $info = $sql->fetch_assoc();
            ?>
            
            <div id="ZakladkiEdycji">
            
                <div id="LeweZakladki">
                
                    <a href="javascript:gold_tabs_horiz('0','0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                    <a href="javascript:gold_tabs_horiz('1','1')" class="a_href_info_zakl" id="zakl_link_1">Dane adresowe</a>
                    
                </div>
                
                <?php $licznik_zakladek = 0; ?>

                <div id="PrawaStrona">
                
                    <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                
                    <div id="zakl_id_0" style="display:none;">

                          <p>
                            <label for="data_zamowienia">Data zamówienia:</label>
                            <input type="text" name="data_zamowienia" id="data_zamowienia" size="53" value="<?php echo date('d-m-Y H:i:s'); ?>" readonly="readonly" />
                          </p>
                          <p>
                            <label>Dokument sprzedaży:</label>
                            <input type="radio" value="1" name="dokument" id="dokument_faktura" checked="checked" /> <label class="OpisFor" for="dokument_faktura">faktura</label>
                            <input type="radio" value="0" name="dokument" id="dokument_paragon" /> <label class="OpisFor" for="dokument_paragon">paragon</label>
                          </p>
                          <p>
                            <label for="imie" class="required">Imię:</label>
                            <input type="text" name="imie" id="imie" size="53" value="<?php echo $info['customers_firstname']; ?>" />
                          </p>
                          <p>
                            <label for="nazwisko" class="required">Nazwisko:</label>
                            <input type="text" name="nazwisko" id="nazwisko" size="53" value="<?php echo $info['customers_lastname']; ?>" />
                          </p>

                          <p>
                            <label for="email" class="required">Adres e-mail:</label>
                            <input type="text" name="email" id="email" size="53" value="<?php echo $info['customers_email_address']; ?>" />
                          </p>

                          <?php
                          if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                            ?>
                            <p>
                              <label for="telefon">Telefon:</label>
                              <input type="text" name="telefon" id="telefon" size="32" value="<?php echo $info['customers_telephone']; ?>" />
                            </p>
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_FAX == 'tak' ) {
                            ?>
                            <p>
                              <label for="fax">Fax</label>
                              <input type="text" name="fax" id="fax" size="32" value="<?php echo $info['customers_fax']; ?>" />
                            </p>
                            <?php
                          }
                          ?>

                          <p>
                            <label for="sel1">Status zamówienia:</label>
                            <?php
                            $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- Wybierz z listy ---');
                            echo Funkcje::RozwijaneMenu('status', $tablica,Sprzedaz::PokazDomyslnyStatusZamowienia(),'style="width: 344px;" id="sel1"'); ?>
                          </p>

                          <p>
                            <label for="koms">Komentarz:</label>
                            <textarea name="komentarz" id="koms" cols="60" rows="10">Zamówienie ręczne</textarea>
                          </p>

                          <p>
                            <label for="sel2">Forma płatności:</label>
                            <?php
                            $tablica_platnosci = Array();
                            $tablica_platnosci = Sprzedaz::ListaPlatnosciZamowien( false, true );
                            echo Funkcje::RozwijaneMenu('platnosc', $tablica_platnosci , '','style="width: 344px;" id="sel2"');
                            unset($tablica_platnosci);
                            ?>
                          </p>

                          <p>
                            <label for="selection">Dostawa:</label>
                            <?php
                            $tablica_wysylek = Array();
                            $zapytanie = "SELECT *
                                          FROM modules_shipping
                                          WHERE status = '1' ORDER BY sortowanie";
                         
                            $sqlw = $db->open_query($zapytanie);   
                            while ($wynik = $sqlw->fetch_assoc()) {
                                $tablica_wysylek[] = array('id'   => $wynik['klasa'].'|'.$wynik['nazwa'],
                                                           'text' => $wynik['nazwa']);
                            }
                            $db->close_query($sqlw); 
                            unset($zapytanie);      

                            echo Funkcje::RozwijaneMenu('dostawa', $tablica_wysylek , '','style="width: 344px;" id="selection"');
                            unset($tablica_wysylek);
                            ?>
                          </p>
                          
                          <p>
                            <label for="koszt_dostawa">Koszt dostawy:</label>
                            <input name="koszt_dostawa" id="koszt_dostawa" type="text" class="kropkaPusta" size="10" value="" />
                          </p>                          

                          <p id="lokalizacje" style="display:none;">
                            <label for="sel3">Lokalizacja:</label>
                            <?php
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonInpost" value="Wybierz paczkomat" readonly="readonly" style="display:none;" />';
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonRuch" value="Wybierz punkt" readonly="readonly" style="display:none;" />';
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonDhl" value="Wybierz punkt" readonly="readonly" style="display:none;" />';
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonPoczta" value="Wybierz punkt" readonly="readonly" style="display:none;" />';
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonBliskaPaczka" value="Wybierz punkt" readonly="readonly" style="display:none;" />';
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonDpd" value="Wybierz punkt" readonly="readonly" style="display:none;" />';
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonGls" value="Wybierz punkt" readonly="readonly" style="display:none;" />';
                            $tablicaLokalizacji[] = array('id' => '0',
                                                         'text' => '--- wybierz z listy ---');
                            echo '<span id="selectionresult">';
                            echo Funkcje::RozwijaneMenu('lokalizacja', $tablicaLokalizacji, '', 'style="width: 344px;" id="sel3"');
                            echo '</span>';
                            ?>
                          </p>

                          <p>
                            <label for="wybor_waluty">Waluta zamówienia:</label>
                            <?php
                            $tablica_walut = array();
                            $zapytanie = "select currencies_id, title, code, currencies_marza, value from currencies";
                         
                            $sqlw = $db->open_query($zapytanie);   
                            while ($wynik = $sqlw->fetch_assoc()) {
                                $tablica_walut[] = array('id' => $wynik['code'] . '|' . ($wynik['value'] * (1 + ((float)$wynik['currencies_marza'] / 100))),
                                                         'text' => $wynik['title']);
                            }
                            $db->close_query($sqlw); 
                            unset($zapytanie);      

                            echo Funkcje::RozwijaneMenu('waluta', $tablica_walut, '','style="width:200px" id="wybor_waluty"');
                            unset($tablica_walut);
                            ?>
                          </p>
                          
                          <p>
                            <label for="opiekun">Opiekun zamówienia:</label>
                            <?php
                            // pobieranie informacji od uzytkownikach
                            $tablica = array();
                            $zapytanie_uzytkownicy = "SELECT * FROM admin ORDER BY admin_lastname";
                            $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                            //
                            $tablica[] = array('id' => '0', 'text' => 'nie przypisany ...');
                            //
                            while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) { 
                                //
                                $tablica[] = array('id' => $uzytkownicy['admin_id'], 'text' => $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname']);
                                //
                            }
                            $db->close_query($sql_uzytkownicy); 
                            unset($zapytanie_uzytkownicy, $uzytkownicy);    
                            //                                   
                            echo Funkcje::RozwijaneMenu('opiekun', $tablica, (((int)$info['service'] > 0) ? (int)$info['service'] : $_SESSION['userID']));
                            ?>
                          </p>
                          
                    </div>
                    
                    <?php // ********************************************* KSIAZKA ADRESOWA *************************************************** ?>
                    
                    <div id="zakl_id_1" style="display:none;">
                    
                      <div class="NaglowekDodajZam">Dane płatnika</div>

                      <p>
                        <label>Osobowość prawna:</label>
                        <input type="radio" value="1" name="osobowosc" id="osobowosc_fizyczna" onclick="$('#pesel').slideDown();$('#firma').slideUp();$('#nip').slideUp()" <?php echo ( $info['entry_nip'] == '' ? 'checked="checked"' : '' ); ?> /> <label class="OpisFor" for="osobowosc_fizyczna">osoba fizyczna</label>
                        <input type="radio" value="0" name="osobowosc" id="osobowosc_prawna" onclick="$('#pesel').slideUp();$('#firma').slideDown();$('#nip').slideDown()" <?php echo ( $info['entry_nip'] != '' ? 'checked="checked"' : '' ); ?> /> <label class="OpisFor" for="osobowosc_prawna">firma</label>
                      </p> 

                      <p id="pesel" <?php echo ( $info['entry_nip'] == '' ? '' : 'style="display:none;"' ); ?> >
                        <label for="psl">Numer PESEL:</label>
                        <input type="text" name="pesel" id="psl" value="<?php echo $info['entry_pesel']; ?>" size="32" />
                      </p>

                      <p id="firma" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> >
                        <label for="nazwa_firmy" class="required">Nazwa firmy:</label>
                        <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo Funkcje::formatujTekstInput($info['entry_company']); ?>" size="53" />
                      </p>

                      <p id="nip" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> class="required">
                        <label for="nip_firmy" class="required">Numer NIP:</label>
                        <input type="text" name="nip_firmy" id="nip_firmy" value="<?php echo $info['entry_nip']; ?>" size="32" />
                      </p>

                      <p>
                        <label for="ulica" class="required">Ulica i numer domu:</label>
                        <input type="text" name="ulica" id="ulica" size="53" value="<?php echo Funkcje::formatujTekstInput($info['entry_street_address']); ?>" />
                      </p>                          
                               
                      <p>
                        <label for="kod_pocztowy" class="required">Kod pocztowy:</label>
                        <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="<?php echo $info['entry_postcode']; ?>" />
                      </p> 

                      <p>
                        <label for="miasto" class="required">Miejscowość:</label>
                        <input type="text" name="miasto" id="miasto" size="53" value="<?php echo $info['entry_city']; ?>" />
                      </p>

                      <p>
                        <label for="panstwo" class="required">Kraj:</label>
                        <input type="text" style="height:31px; padding-top:0px; padding-bottom:0px" name="panstwo" id="panstwo" size="53" value="<?php echo Klienci::pokazNazwePanstwa($info['entry_country_id']); ?>" />
                      </p>

                      <?php
                      if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                        ?>
                        <p>
                          <label for="wojewodztwo">Województwo:</label>
                          <input type="text" style="height:31px; padding-top:0px; padding-bottom:0px" name="wojewodztwo" id="wojewodztwo" size="53" value="<?php echo ( $info['entry_zone_id'] != '' ? Klienci::pokazNazweWojewodztwa($info['entry_zone_id']) : '' ); ?>" />
                        </p>
                        <?php
                      }
                      ?>
                      
                      <?php
                      // inne adresy dostawy
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
                                            WHERE a.address_book_id != c.customers_default_address_id AND c.customers_id = '" . $info['customers_id'] . "'";
                      
                      $sql_adresy = $db->open_query($zapytanie_adresy); 
                      ?>
                      
                      <script>
                      function ZmienAdresDostawy(element) {
                          if ( $(element).attr('id') == 'adres_bez_zmian' ) {
                               $('#dostawa_lista').stop().slideUp();
                               $('#dostawa').stop().slideUp();
                          }
                          if ( $(element).attr('id') == 'adres_zmiany' ) {
                               $('#dostawa_lista').stop().slideUp();
                               $('#dostawa').stop().slideDown();
                          } 
                          if ( $(element).attr('id') == 'adres_zmiany_lista' ) {
                               $('#dostawa_lista').stop().slideDown();
                               $('#dostawa').stop().slideUp();
                          }                                
                      }
                      </script>

                      <p>
                        <label>Adres dostawy:</label>
                          <input type="radio" value="1" name="adres_dostawy" id="adres_bez_zmian" onclick="ZmienAdresDostawy(this)" checked="checked" /> <label class="OpisFor" for="adres_bez_zmian">taki sam jak adres klienta</label>
                          <input type="radio" value="0" name="adres_dostawy" id="adres_zmiany" onclick="ZmienAdresDostawy(this)" /> <label class="OpisFor" for="adres_zmiany">inny (wpisywany ręcznie)</label>                          
                          <?php if ((int)$db->ile_rekordow($sql_adresy) > 0) { ?>
                          <input type="radio" value="2" name="adres_dostawy" id="adres_zmiany_lista" onclick="ZmienAdresDostawy(this)" /> <label class="OpisFor" for="adres_zmiany_lista">wybierz z listy</label>                          
                          <?php } ?>
                      </p>
                      
                      <div id="dostawa_lista" style="display:none;">
                      
                        <?php if ((int)$db->ile_rekordow($sql_adresy) > 0) { ?>
                      
                            <div class="NaglowekDodajZam" style="margin-top:20px">Dodatkowe adresy dostawy</div>
                          
                            <?php 
                            $p = 1;
                            while ( $infa = $sql_adresy->fetch_assoc() ) { ?>
                                  
                                <div class="AdresDodatkowy">
                                
                                    <ul>
                                        <li style="margin-bottom:10px"><input type="radio" id="adr_<?php echo $p; ?>" value="<?php echo $infa['address_book_id']; ?>" name="adres_lista_wybor" <?php echo (($p == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="adr_<?php echo $p; ?>">wybierz ten adres</label></li>
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
                                        
                                    </ul>
                                    
                                    <div class="cl"></div>
                                    
                                </div> 

                                <?php 
                                $p++;
                            } 
                            ?> 
                             
                        <?php unset($p); } ?>
                      
                      </div>

                      <div id="dostawa" style="display:none;">

                        <div class="NaglowekDodajZam" style="margin-top:20px">Adres dostawy</div>
                        
                        <p>
                          <label for="dostawa_nazwa_firmy">Nazwa firmy:</label>
                          <input type="text" name="dostawa_nazwa_firmy" id="dostawa_nazwa_firmy" value="" size="53" />
                        </p>

                        <p>
                          <label for="dostawa_imie">Imię:</label>
                          <input type="text" name="dostawa_imie" id="dostawa_imie" size="53" value="" />
                        </p>
                        <p>
                          <label for="dostawa_nazwisko">Nazwisko:</label>
                          <input type="text" name="dostawa_nazwisko" id="dostawa_nazwisko" size="53" value="" />
                        </p>

                        <p>
                          <label for="dostawa_ulica" class="required">Ulica i numer domu:</label>
                          <input type="text" name="dostawa_ulica" id="dostawa_ulica" size="53" value="" />
                        </p>                          
                                 
                        <p>
                          <label for="dostawa_kod_pocztowy" class="required">Kod pocztowy:</label>
                          <input type="text" name="dostawa_kod_pocztowy" id="dostawa_kod_pocztowy" size="12" value="" />
                        </p> 

                        <p>
                          <label for="dostawa_miasto" class="required">Miejscowość:</label>
                          <input type="text" name="dostawa_miasto" id="dostawa_miasto" size="53" value="" />
                        </p>

                        <p>
                          <label for="dostawa_panstwo" class="required">Kraj:</label>
                          <input type="text" style="height:31px; padding-top:0px; padding-bottom:0px" name="dostawa_panstwo" id="dostawa_panstwo" size="53" value="" />
                        </p>

                        <?php
                        if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          ?>
                          <p>
                            <label for="dostawa_wojewodztwo">Województwo:</label>
                            <input type="text" style="height:31px; padding-top:0px; padding-bottom:0px" name="dostawa_wojewodztwo" id="dostawa_wojewodztwo" size="53" value="" />
                          </p>
                          <?php
                        }
                        ?>
                        
                        <?php
                        if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                          ?>
                          <p>
                            <label for="dostawa_telefon">Telefon:</label>
                            <input type="text" name="dostawa_telefon" id="dostawa_telefon" size="32" value="" />
                          </p>
                          <?php
                        }
                        ?>                        
                        
                      </div>

                    </div>
                    
                    <?php
                    $db->close_query($sql_adresy);
                    unset($zapytanie_adresy, $infa);                          
                    ?>                       

                    <?php
                    $zakladka = '0';
                    if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                    ?>
                    <script>
                    gold_tabs_horiz(<?php echo $zakladka; ?>,'0');
                    </script>                         
                
                </div>
                
            </div>

            <?php
            $db->close_query($sql); 
            unset($zapytanie, $info);                
            ?>

          </div>         

          <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <?php
              // jezeli jest wywolanie z menu klientow
              if ( isset($_GET['klient']) ) {
              ?>
              <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Get(array('x','y','klient')); ?>','klienci');">Powrót</button>   
              <?php } else { ?>
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','klient_id')); ?>','sprzedaz');">Powrót</button>   
              <?php } ?>
          </div>              
          
          </form>
          
        <?php } ?>

    </div>    
    <?php
    include('stopka.inc.php');

}