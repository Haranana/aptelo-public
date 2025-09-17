<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'ORLEN PACZKA';

    //if ( !isset($_POST['akcja']) ) {
        $apiKurier = new PaczkaRuchApi((int)$_GET['id_poz']);
    //}

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $weight_total = $_POST['waga_zamowienia'];

        $wysylkaZamowienie = array();
        $wysylkaZamowienie['PartnerID']        = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_ID'];
        $wysylkaZamowienie['PartnerKey']       = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_KEY'];
        $wysylkaZamowienie['DestinationCode']  = $_POST['DestinationCode'];
        $wysylkaZamowienie['PackValue']        = $_POST['PackValue'] * 100;

        if ( isset($_POST['Insurance']) ) {
            $wysylkaZamowienie['Insurance']    = 'true';
        }


        if ( isset($_POST['BoxSize']) && ( $_POST['Typ'] == 'PSD' || $_POST['Typ'] == 'PPP' || $_POST['Typ'] == 'PSP' || $_POST['Typ'] == 'PSF' )) {
            $wysylkaZamowienie['BoxSize']    = 'MINI';
        }
        if ( $_POST['Typ'] == 'APM' || $_POST['Typ'] == 'PKN' ) {
            $wysylkaZamowienie['BoxSize']    = $_POST['gabaryt'];
        }

        if ( isset($_POST['CashOnDelivery']) && $_POST['AmountCashOnDelivery'] != '' && $_POST['AmountCashOnDelivery'] > 0 ) {
            $wysylkaZamowienie['CashOnDelivery']       = 'true';
            $wysylkaZamowienie['AmountCashOnDelivery'] = $_POST['AmountCashOnDelivery'] * 100;
            $wysylkaZamowienie['TransferDescription']  = $_POST['TransferDescription'];
        }

        $wysylkaZamowienie['EMail']           = $_POST['Email'];
        $wysylkaZamowienie['FirstName']       = $_POST['FirstName'];
        $wysylkaZamowienie['LastName']        = $_POST['LastName'];
        $wysylkaZamowienie['CompanyName']     = $_POST['CompanyName'];
        $wysylkaZamowienie['StreetName']      = $_POST['StreetName'];
        $wysylkaZamowienie['BuildingNumber']  = $_POST['BuildingNumber'];
        $wysylkaZamowienie['FlatNumber']      = $_POST['FlatNumber'];
        $wysylkaZamowienie['PostCode']        = $_POST['PostCode'];
        $wysylkaZamowienie['City']            = $_POST['City'];
        $wysylkaZamowienie['PhoneNumber']     = $_POST['PhoneNumber'];
        $wysylkaZamowienie['PhoneNumber']     = $_POST['PhoneNumber'];

        $wysylkaZamowienie['SenderEMail']     = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_EMAIL'];
        $wysylkaZamowienie['SenderFirstName'] = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_IMIE'];
        $wysylkaZamowienie['SenderLastName']  = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_NAZWISKO'];
        $wysylkaZamowienie['SenderCompanyName'] = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_NAZWA'];
        $wysylkaZamowienie['SenderStreetName'] = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_ULICA'];
        $wysylkaZamowienie['SenderBuildingNumber'] = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_DOM'];
        $wysylkaZamowienie['SenderFlatNumber'] = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_LOKAL'];
        $wysylkaZamowienie['SenderCity']      = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_MIASTO'];
        $wysylkaZamowienie['SenderPostCode']  = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_KOD_POCZTOWY'];
        $wysylkaZamowienie['SenderPhoneNumber'] = $apiKurier->polaczenie['INTEGRACJA_PACZKARUCH_NADAWCA_TELEFON'];

        $wysylkaZamowienie['PrintAdress']     = '1';
        $wysylkaZamowienie['PrintType']       = '1';
        $noweZamowienie = $apiKurier->doGenerateLabelBusinessPack($wysylkaZamowienie);

        if ( is_object($noweZamowienie) ) {

            $xml = simplexml_load_string($noweZamowienie->GenerateLabelBusinessPackResult->any);

            if ( $xml->NewDataSet->GenerateLabelBusinessPack->Err == '000' || $xml->NewDataSet->GenerateLabelBusinessPack->Err == '006' || $xml->NewDataSet->GenerateLabelBusinessPack->Err == '007' || $xml->NewDataSet->GenerateLabelBusinessPack->Err == '008' ) {

                $paczka = $xml->NewDataSet->GenerateLabelBusinessPack->PackCode_RUCH;

                if (!file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$paczka)) {
                    mkdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$paczka, 0777, true);
                }

                $binaryPDF = $noweZamowienie->LabelData;
                $nazwaPDF = $paczka;

                file_put_contents(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$paczka.'/'.$paczka.'.pdf', $binaryPDF);

                $pola = array(
                        array('orders_id',$filtr->process($_POST["id"])),
                        array('orders_shipping_type',$api),
                        array('orders_shipping_number',$paczka),
                        array('orders_shipping_weight',$weight_total),
                        array('orders_parcels_quantity','1'),
                        array('orders_shipping_status','200'),
                        array('orders_shipping_date_created', 'now()'),
                        array('orders_shipping_date_modified', 'now()'),
                        array('orders_shipping_comments', ''),
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);

                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

            }

        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      echo Okienka::pokazOkno('Błąd', $komunikat);
    }
    ?>

    <div id="naglowek_cont">Tworzenie wysyłki</div>
    <div id="cont">
    
    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    if ( !isset($_GET['zakladka']) ) {
         $_GET['zakladka'] = '0';
    }      
    
    if ( (int)$_GET['id_poz'] == 0 ) {
    ?>
       
      <div class="poleForm"><div class="naglowek">Wysyłka</div>
        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
      </div>      
      
    <?php
    } else {
    ?>    

      <div class="poleForm">
        <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

        <div class="pozycja_edytowana">  

          <div class="MapaUkryta" id="WybierzMape">
            <div id="WyborMapaWysylka">
                <div id="MapaKontener">
                    <div id="MapaZamknij">X</div>
                    <div id="WidokMapy"></div>
                </div>
            </div>
          </div>

          <link rel="stylesheet" href="https://ruch-osm.sysadvisors.pl/widget.css"/>
          <script type="text/javascript" src="https://ruch-osm.sysadvisors.pl/widget.js"></script>

          <script>
            $(document).ready(function() {
                var wid;

                $("#WybierzMape").css({
                    top: ( -210 ),
                    left: ( ($("#StronaPanel").outerWidth() - $("#MapaKontener").outerWidth()) / 2 )
                });

                $(window).scroll(function() {
                    var fromTop = $(window).scrollTop() - 210;

                    if ( $(window).scrollTop() > 0 ) {

                    $("#WybierzMape").css({
                        top: ( fromTop ),
                        left: ($("#StronaPanel").outerWidth() - $("#MapaKontener").outerWidth()) / 2
                    });
                    } else {
                    $("#WybierzMape").css({
                        top: ( -210 ),
                        left: ($("#StronaPanel").outerWidth() - $("#MapaKontener").outerWidth()) / 2
                    });

                    }

                });

                $('#MapaZamknij').click(function() {
                   $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                   $("#MapaWidocznaTlo").fadeOut(500, function() {
                       $("#MapaWidocznaTlo").remove();
                   });
                   enableScroll();
                });

            });

                function PokazMape() {
                    disableScroll();
                    $("#WybierzMape").append('<div id="MapaWidocznaTlo"></div>');
                    $('#WybierzMape').removeClass('MapaUkryta').addClass('MapaWidoczna').show();

                    button_init();


                }
                function button_init() {
                    wid = new RuchWidget('WidokMapy',	// ID div, gdzie będzie wyświetlany widget
                        {
                            readyCb: on_ready,
                            selectCb: on_select,
                            initialAddress: $('#adresat_City').val(),
                            sandbox: 0,
                            showCodFilter: 1,
                            showPointTypeFilter: 1
                        }
                    );
                    wid.init();
                }

                function on_ready() {
                    wid.showWidget(
                            parseInt(0),	// Cod czy nie
                            {					// Lista cen dla typów
                                'R': 10+0*2,
                                'P': 11+0*2,
                                'U': 11+0*2,
                                'A': 11+0*2
                            },
                            {					// Lista metod dla typów
                                'R': 'ruch_' + 0,
                                'P': 'partner_' + 0,
                                'U': 'partner_' + 0,
                                'A': 'orlen_' + 0
                            }
                        );
                }

                function on_select(p) {
                    if (p == null) {
                        $('#DestinationCode').val('');
                        $('#integracja_DestinationCode').val('');
                        $('#preferowany').val('');
                    } else {
                        var opis_punkt = p.a + '; ' + p.o;
                        var value = p.a;
                        var punktodbioru = p.id;
                        var pobranie = 'NIE';
                        if ( p.c == '1' ) {
                            pobranie = 'TAK';
                            $('#ParagrafPobranie').slideDown();
                        } else {
                            $('#ParagrafPobranie').slideUp();
                            $("#PobranieOpis").slideUp();
                            $("#AmountCashOnDelivery").val('');
                        }
                        if ( p.r == 'APM' || p.r == 'PKN' ) {
                            $('#ParagrafRozmiar').slideUp();
                            $('#ParagrafGabaryt').slideDown();
                        } else {
                            $('#ParagrafGabaryt').slideUp();
                            $('#ParagrafRozmiar').slideDown();
                        }

                        $('#Typ').val(p.r);
                        $('#preferowany').val(p.a + ' (' + p.r + ' Pobranie: ' + pobranie + ')');
                        $('#DestinationCode').val(punktodbioru);
                        $('#integracja_DestinationCode').val(punktodbioru);
                        $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                        enableScroll();
                    }
                }


            function disableScroll() { 
                scrollTop = window.pageYOffset || document.documentElement.scrollTop; 
                scrollLeft = window.pageXOffset || document.documentElement.scrollLeft, 
  
                window.onscroll = function() { 
                    window.scrollTo(scrollLeft, scrollTop); 
                }; 
            } 
  
            function enableScroll() { 
                window.onscroll = function() {}; 
            } 

           </script>



            <script>
            $(document).ready(function() {

              var pobranie = $("#Pobranie").val();
              var typ = $("#Typ").val();

              if ( pobranie == 'false' ) {
                $('#ParagrafPobranie').slideUp();
              } else {
                $('#ParagrafPobranie').slideDown();
              }
              if ( typ == 'APM' || typ == 'PKN' ) {
                $('#ParagrafRozmiar').slideUp();
                $('#ParagrafGabaryt').slideDown();
              } else {
                $('#ParagrafGabaryt').slideUp();
                $('#ParagrafRozmiar').slideDown();
              }

              $('#CashOnDelivery').change(function() {
                  (($(this).is(':checked')) ? $("#AmountCashOnDelivery").prop('disabled', false) : $("#AmountCashOnDelivery").prop('disabled', true));
                  $("#AmountCashOnDelivery").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");

              });
              $('#CashOnDelivery').change(function() {
                  if ( $(this).is(':checked') ) {
                    $("#PobranieOpis").slideDown();
                    $("#TransferDescription").val("zamowienie numer " + " " + $("#NumerZamowienia").val());
                  } else {
                    $("#PobranieOpis").slideUp();
                    $("#TransferDescription").val("");
                  }
              });


            });
            </script>

        <style>
            #MapaWidocznaTlo { position:fixed; top:0; left:0; bottom:0; right:0; z-index:999; background:#000000; opacity:0.6; filter:alpha(opacity=60); overflow:hidden; overflow-x:hidden;
            overflow-y:auto;}
            #WybierzMape { position:relative; margin:0px auto; z-index:99998; }

            #WyborMapaWysylka { position:absolute; z-index:99999; margin:auto; }

            .MapaUkryta { position:absolute; left:0px; width:100%; top:-1000px; opacity:0; filter:alpha(opacity=0); visibility:hidden; }

            .MapaWidoczna { height:100%; z-index:99997; 
                transition: all 0.50s ease-in-out; -moz-transition: all 0.50s ease-in-out; -webkit-transition: all 0.50s ease-in-out;
            }

            #MapaKontener { position:relative; overflow:visible; margin:0px auto; }
            #WidokMapy { position:relative; overflow:hidden; background:#fff; border:2px solid #fff;
                -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; -khtml-border-radius:5px;
                -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;                           
            }

            #MapaZamknij { cursor:pointer; position:absolute; z-index:100000; border:2px solid #fff; background:#000; color:#fff; font-weight:bold; font-style:normal; font-size:14px; font-family:Arial; width:26px; height:26px; line-height:26px; display:inline-block; text-align:center; -webkit-border-radius:50%; -moz-border-radius:50%; border-radius:50%; -khtml-border-radius:50%; }
                                  
            @media only screen and (max-width:1023px) {
                #MapaZamknij { right:0px; top:-40px; }
                #WidokMapy { width:524px; height:650px; overflow-x:scroll; }
            }
            @media only screen and (max-width:1023px) and (max-height:460px) {
                #MapaZamknij { right:-5px; top:-13px; }
                #WidokMapy { width:524px; height:650px; overflow-y:scroll; } 
            }                      
            @media only screen and (min-width:1024px) {
                #MapaZamknij { right:-15px; top:-15px; }
                #WidokMapy { width:992px; height:650px; }
            }    
            @media only screen and (min-width:1024px) and (max-height:460px) {
                #MapaZamknij { right:-13px; top:-13px; }
                #WidokMapy { width:992px; height:650px; overflow-y:scroll; }
            }       
        </style>



            <?php
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;
            $wymiary        = array();

            $AdresOK = true;
            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
            $adres_dom_lokal = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

            $PrzeksztalconyAdres = implode(' ', $adres_klienta); 
            if ( $PrzeksztalconyAdres != $zamowienie->dostawa['ulica'] ) {
                $AdresOK = false;
            }

            $klient = explode(' ', (string)$zamowienie->dostawa['nazwa']);
            ?>

            <script>
            $(document).ready(function() {
              <?php
              if ( !$AdresOK ) {
                ?>
                $( "<p style='padding:10px 25px 5px 25px;'><span class='ostrzezenie'>Sprawdź adres odbiorcy</span></p><p style='padding:0 20px 5px 25px;'><span><?php echo addslashes($zamowienie->dostawa['ulica']); ?></span></p>" ).insertBefore("#AdresOdbiorcy");
                <?php
              }
              ?>
            });
            </script>

            <form action="sprzedaz/zamowienia_wysylka_paczka_ruch.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" id="NumerZamowienia" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o przesyłce</div>

                        <?php 
                        $KodPunktuOdbioru = '';
                        $OpisPunktuOdbioru = '';
                        $Pobranie = false;
                        $TypPunktu = 'PSD';

                        $miasto = array();
                        if ( $zamowienie->info['wysylka_info'] != '' && $zamowienie->info['wysylka_modul'] == 'Paczka w RUCHU' ) {
                            $miasto = explode(' - ', (string)$zamowienie->info['wysylka_info']); 
                        }

                        //
                        $domyslny = '';
                        if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                            $tablicaKioskow = array();
                            $domyslny = $zamowienie->info['wysylka_punkt_odbioru'];
                            $tablicaKioskow = $apiKurier->doGiveMeAllRuchLocation();

                            if ( array_key_exists($domyslny, $tablicaKioskow) ) {
                                $KodPunktuOdbioru = $domyslny;
                                $OpisPunktuOdbioru = $tablicaKioskow[$domyslny]['opis'];
                                $Pobranie = $tablicaKioskow[$domyslny]['pobranie'];
                                $TypPunktu = $tablicaKioskow[$domyslny]['typ'];
                            }
                        }
                        ?>
                        <p>
                            <div style="padding:20px 20px 10px 20px;">

                                <input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape()" />

                                <input type="text" id="integracja_DestinationCode" name="integracja_DestinationCode" value="<?php echo $KodPunktuOdbioru; ?>" size="20" readonly="readonly" />

                                <input type="hidden" id="DestinationCode" name="DestinationCode" value="<?php echo $KodPunktuOdbioru; ?>" size="20" readonly="readonly" />
                                <input type="hidden" id="Pobranie" name="Pobranie" value="<?php echo $Pobranie; ?>" size="20" readonly="readonly" />
                                <input type="hidden" id="Typ" name="Typ" value="<?php echo $TypPunktu; ?>" size="20" readonly="readonly" />

                                <input type="text" id="preferowany" value="<?php echo $OpisPunktuOdbioru; ?>" name="preferowany" readonly="readonly"  style="margin-left:10px;" size="53" />

                            </div>
                        </p>

                        <p id="ParagrafRozmiar" style="display:none;">
                          <label for="BoxSize">Minipaczka:</label>
                          <input id="BoxSize" value="1" type="checkbox" name="BoxSize" style="margin-right:20px;" <?php echo ( isset($_POST['BoxSize']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="BoxSize"></label> 
                        </p> 

                        <p id="ParagrafGabaryt" style="display:none;">
                          <label>Gabaryt:</label>
                            <input type="radio" style="border:0px" name="gabaryt" value="S" id="gabaryt_s" /><label class="OpisFor" for="gabaryt_s">Gabaryt S</label>
                            <input type="radio" style="border:0px" name="gabaryt" value="M" id="gabaryt_m" /><label class="OpisFor" for="gabaryt_m">Gabaryt M</label>
                            <input type="radio" style="border:0px" name="gabaryt" value="L" id="gabaryt_l" /><label class="OpisFor" for="gabaryt_l">Gabaryt L</label>
                        </p> 

                        <p>
                          <label for="ubezpieczenie">Wartość przesyłki:</label>
                          <input class="kropkaPustaZero" type="text" size="20" name="PackValue" id="ubezpieczenie_wartosc" value="<?php echo ( isset($_POST['PackValue']) ? $_POST['PackValue'] : $zamowienie->info['wartosc_zamowienia_val'] ); ?>" />
                        </p> 

                        <p id="ParagrafPobranie" style="display:none;">
                            <label for="CashOnDelivery">Pobranie:</label>
                            <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                <input type="checkbox" value="1" name="CashOnDelivery" id="CashOnDelivery" style="margin-right:20px;" <?php echo ( isset($_POST['CashOnDelivery']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="CashOnDelivery" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="AmountCashOnDelivery" id="AmountCashOnDelivery" value="<?php echo ( isset($_POST['AmountCashOnDelivery']) ? $_POST['AmountCashOnDelivery'] : '' ); ?>" disabled="disabled" />
                            <?php } else { ?>
                                <input type="checkbox" value="1" name="CashOnDelivery" id="CashOnDelivery" style="margin-right:20px;"  checked="checked" /><label class="OpisForPustyLabel" for="CashOnDelivery" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="AmountCashOnDelivery" id="AmountCashOnDelivery" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                            <?php } ?>
                        </p> 
                        <?php 
                        $ukryj = '';
                        if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) {
                            $ukryj = 'style="display:none;"';
                        }
                        ?>
                        <p id="PobranieOpis" <?php echo $ukryj; ?>>
                            <label for="TransferDescription">Tytuł przelewu dla paczki z pobraniem:</label>
                            <textarea cols="45" rows="2" name="TransferDescription" id="TransferDescription" onkeyup="licznik_znakow(this,'iloscZnakow',200)" ><?php echo ( isset($_POST['TransferDescription']) ? $_POST['TransferDescription'] : 'Zamowienie numer ' . $_GET['id_poz'] ); ?></textarea>
                        </p> 


                        <p>
                          <label for="Insurance">Ubezpieczenie:</label>
                          <input id="Insurance" value="1" type="checkbox" name="Insurance" style="margin-right:20px;" <?php echo ( isset($_POST['Insurance']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="Insurance"></label> 
                        </p> 


                    </div>

                </div>

                <div class="OknoDodatkowe">

                    <div class="poleForm">
                    
                        <div class="naglowek">Informacje</div>

                        <p>
                            <label class="readonly">Forma dostawy w zamówieniu:</label>
                            <input type="text" size="34" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <?php if ( $zamowienie->info['wysylka_info'] != '' ) { ?>
                            <p>
                                <label class="readonly">Punkt odbioru:</label>
                                <textarea cols="30" rows="2" name="punkt_odbioru" id="punkt_odbioru"  readonly="readonly" class="readonly"><?php echo $zamowienie->info['wysylka_info']; ?></textarea>
                            </p>
                        <?php } ?>
                        <p>
                            <label class="readonly">Forma płatności w zamówieniu:</label>
                            <input type="text" size="34" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <p>
                            <label class="readonly">Wartość zamówienia:</label>
                            <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <p>
                            <label class="readonly">Waga produktów:</label>
                            <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                        </p> 

                    </div>

                    <div class="poleForm">
                    
                        <div class="naglowek">Informacje odbiorcy</div>

                        <p>
                            <label for="adresat_firma">Nazwa firmy:</label>
                            <input type="text" size="40" name="CompanyName" id="adresat_Company" value="<?php echo Funkcje::formatujTekstInput($zamowienie->dostawa['firma']); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Name">Imię:</label>
                            <input type="text" size="40" name="FirstName" id="adresat_Name" value="<?php echo preg_replace('!\s+!', ' ', (string)$klient[0]); ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_LastName">Nazwisko:</label>
                            <input type="text" size="40" name="LastName" id="adresat_LastName" value="<?php echo preg_replace('!\s+!', ' ', (string)$klient[1]); ?>"  class="klient" />
                        </p> 

                        <p id="AdresOdbiorcy">
                            <label for="ulica">Ulica:</label>
                            <input type="text" size="40" name="StreetName" id="ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="numerDomu">Numer domu:</label>
                            <input type="text" size="40" name="BuildingNumber" id="numerDomu" value="<?php echo $adres_dom_lokal['dom']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="numerLokalu">Numer lokalu:</label>
                            <input type="text" size="40" name="FlatNumber" id="numerLokalu" value="<?php echo $adres_dom_lokal['mieszkanie']; ?>" class="klient" />
                        </p> 

                        <p>
                            <label for="adresat_PostalCode">Kod pocztowy:</label>
                            <input type="text" size="40" name="PostCode" id="adresat_PostalCode" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_City">Miejscowość:</label>
                            <input type="text" size="40" name="City" id="adresat_City" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                        </p> 


                        <p>
                            <label for="adresat_Phone">Numer telefonu:</label>
                            <?php 
                            if ( $zamowienie->dostawa['telefon'] != '' ) {
                                $NumerTelefonu = $zamowienie->dostawa['telefon'];
                            } else {
                                $NumerTelefonu = $zamowienie->klient['telefon'];
                            }
                            ?>

                            <input type="text" size="40" name="PhoneNumber" id="adresat_Phone" value="<?php echo preg_replace('/\W/','', (string)$NumerTelefonu); ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Email">Adres e-mail:</label>
                            <input type="text" size="40" name="Email" id="adresat_Email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                        </p> 
                        
                    </div>
                    
                </div>

              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
              </div>
            </form>

        </div>
      </div>

    <?php } ?>
    
    </div>  

    <?php
    include('stopka.inc.php');    
    
} ?>
