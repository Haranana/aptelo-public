<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'BLISKAPACZKA';

    if ( isset($_GET['id_poz']) && isset($_GET['zakladka']) ) {
        $IDPoz = $_GET['id_poz'];
        $Zakladka = $_GET['zakladka'];
    }
    if ( isset($_POST['id_poz']) && isset($_POST['zakladka']) ) {
        $IDPoz = $_POST['id_poz'];
        $Zakladka = $_POST['zakladka'];
    }

    $apiKurier       = new BliskapaczkaApi($IDPoz, $Zakladka);

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $punktNadania = '';

        $Przesylka = array();
        $Przesylka["senderFirstName"]            = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_IMIE'];
        $Przesylka["senderLastName"]             = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_NAZWISKO'];
        $Przesylka["senderCompanyName"]          = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_FIRMA'];
        $Przesylka["senderPhoneNumber"]          = preg_replace( '/[^0-9+]/', '', (string)$apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_TELEFON']);
        $Przesylka["senderEmail"]                = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_EMAIL'];
        $Przesylka["senderStreet"]               = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_ULICA'];
        $Przesylka["senderBuildingNumber"]       = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_DOM'];
        $Przesylka["senderFlatNumber"]           = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIESZKANIE'];
        $Przesylka["senderPostCode"]             = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_KOD_POCZTOWY'];
        $Przesylka["senderCity"]                 = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIASTO'];
        $Przesylka["countryCode"]                = 'PL';
        $Przesylka["codPayoutBankAccountNumber"] = ( isset($_POST['codValue']) && $_POST['codValue'] > 0 ? $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NUMER_KONTA'] : null );

        $Przesylka["receiverFirstName"]          = $_POST['receiverFirstName'];
        $Przesylka["receiverLastName"]           = $_POST['receiverLastName'];
        $Przesylka["receiverCompanyName"]        = $_POST['receiverCompanyName'];
        $Przesylka["receiverPhoneNumber"]        = preg_replace( '/[^0-9+]/', '', (string)$_POST['receiverPhoneNumber']);
        $Przesylka["receiverEmail"]              = $_POST['receiverEmail'];

        if ( $_POST['deliveryType'] == 'D2D' ) {
            $Przesylka["receiverStreet"]             = $_POST['receiverStreet'];
            $Przesylka["receiverBuildingNumber"]     = $_POST['receiverBuildingNumber'];
            $Przesylka["receiverFlatNumber"]         = $_POST['receiverFlatNumber'];
            $Przesylka["receiverPostCode"]           = $_POST['receiverPostCode'];
            $Przesylka["receiverCity"]               = $_POST['receiverCity'];
            $Przesylka["receiverCountryCode"]        = $_POST['receiverCountryCode'];
        }
        
        if ( $_POST['operatorName'] == 'KURIER_48' ) {
            $_POST['operatorName'] = 'POCZTA';
        }
        if ( $_POST['operatorName'] == 'UPSAP' ) {
            $_POST['operatorName'] = 'UPS';
        }

        $Przesylka["operatorName"]               = $_POST['operatorName'];

        if ( $_POST['deliveryType'] == 'P2P' || $_POST['deliveryType'] == 'D2P' ) {
            $Przesylka["destinationCode"]            = $_POST['destinationCode'];
        }
        if ( $_POST['deliveryType'] == 'P2P' ) {

            if ( $_POST['operatorName'] == 'INPOST' ) {
                $punktNadania = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_INPOST'];
            }
            if ( $_POST['operatorName'] == 'RUCH' ) {
                $punktNadania = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_RUCH'];
            }
            if ( $_POST['operatorName'] == 'POCZTA' ) {
                $punktNadania = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_POCZTA'];
            }
            if ( $_POST['operatorName'] == 'DPD' ) {
                $punktNadania = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_DPD'];
            }
            if ( $_POST['operatorName'] == 'UPS' ) {
                $punktNadania = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_UPS'];
            }
            if ( $_POST['operatorName'] == 'FEDEX' ) {
                $punktNadania = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_FEDEX'];
            }
            $Przesylka["postingCode"]                = $punktNadania;
        }

        $Przesylka["additionalInformation"]      = $_POST['additionalInformation'];
        $Przesylka["reference"]                  = $_POST['reference'];
        $Przesylka["additionalReference"]        = $_POST['additionalReference'];
        $Przesylka["codValue"]                   = ( isset($_POST['codValue']) && $_POST['codValue'] > 0 ? $_POST['codValue'] : null );
        $Przesylka["deliveryType"]               = $_POST['deliveryType'];

        $Przesylka['parcels'] = array();
        $parcel = array();

        $parcel['dimensions']['length']          = $_POST['parcel']['dimensions']['length'];
        $parcel['dimensions']['width']           = $_POST['parcel']['dimensions']['width'];
        $parcel['dimensions']['height']          = $_POST['parcel']['dimensions']['height'];
        $parcel['dimensions']['weight']          = $_POST['parcel']['dimensions']['weight'];

        $parcel['insuranceValue']                = ( isset($_POST['parcel']['insuranceValue']) && $_POST['parcel']['insuranceValue'] > 0 ? $_POST['parcel']['insuranceValue'] : null );

        array_push($Przesylka['parcels'], $parcel);

        $Wynik = $apiKurier->commandPost('v2/order/advice', $Przesylka);

        $komunikat = '';
        if ( isset($Wynik->errors) ) {

            foreach ( $Wynik->errors as $Blad ) {
                $komunikat .= $Blad->field . ' : ' . $Blad->value . '<br />';
            }

        } else {

            //echo '<pre>';
            //echo print_r($Wynik);
            //echo '</pre>';
            $numerPrzesylki = '';
            $LinkSledzenia = '';

            if ( isset($Wynik) && $Wynik !== false && $Wynik->number ) {
                $numerPrzesylki = $Wynik->number;
            }
            if ( $numerPrzesylki != '' ) {

              $DataModyfikacji = end($Wynik->changes);

              $pola = array(
                      array('orders_id',(int)$_POST["id"]),
                      array('orders_shipping_type',$api),
                      array('orders_shipping_number',$numerPrzesylki),
                      array('orders_shipping_weight',$_POST['parcel']['dimensions']['weight']),
                      array('orders_parcels_quantity','1'),
                      array('orders_shipping_status',$Wynik->status),
                      array('orders_shipping_date_created', date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Wynik->creationDate))),
                      array('orders_shipping_date_modified', date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($DataModyfikacji->dateTime))),
                      array('orders_shipping_comments', $Wynik->operatorName),
                      array('orders_shipping_misc',''),
                      array('orders_dispatch_status',$_POST['deliveryType'])
              );

              $db->insert_query('orders_shipping' , $pola);
              unset($pola);

              $id_dodanej_pozycji = $db->last_id_query();
              sleep(5);

              $wynikBliskaPaczka = $apiKurier->commandGet('v2/order/'.$numerPrzesylki);

              if ( isset($wynikBliskaPaczka) && $wynikBliskaPaczka->changes ) {

                if ( $wynikBliskaPaczka->trackingNumber != '' ) {
                    $LinkSledzenia = Funkcje::LinkSledzeniaWysylki($wynikBliskaPaczka->operatorName, $wynikBliskaPaczka->trackingNumber);
                }

                $AktualnyStatus = end($wynikBliskaPaczka->changes);

                $pola = array();
                $pola = array(
                        array('orders_shipping_status',$AktualnyStatus->status),
                        array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($AktualnyStatus->dateTime))),
                        array('orders_shipping_misc',$wynikBliskaPaczka->trackingNumber),
                        array('orders_shipping_link',$LinkSledzenia)
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$id_dodanej_pozycji."'");
                unset($pola);


              }

              Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
            }

        }


    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      //echo Okienka::pokazOkno('Błąd', $komunikat);
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
          <?php 
          $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
          ?>

          <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        
          <script type="text/javascript" src="javascript/paczka.js"></script>

          <script type="text/javascript" src="https://widget.bliskapaczka.pl/v8.1/main.js"></script>
          <link rel="stylesheet" href="https://widget.bliskapaczka.pl/v8.1/main.css" />

          <script>
            $(document).ready(function() {
              

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
                   $("#BPWidget").remove();
                   $("#MapaWidocznaTlo").fadeOut(500, function() {
                       $("#MapaWidocznaTlo").remove();
                   });
                   enableScroll();
                });

            });

            function PokazMape(Kurier, COD) {
                var pobranie = COD;
                var str = Kurier;
                var dostawca = str.toLowerCase();
                disableScroll();
                $("#WybierzMape").append('<div id="MapaWidocznaTlo"></div>');
                $('#WybierzMape').removeClass('MapaUkryta').addClass('MapaWidoczna').show();
                BPWidget.init(
                        document.getElementById('WidokMapy'),
                        {
                            callback: function(point) {
                                $('#preferowany_' + dostawca + '').val(point.city + ' - ' + point.street);
                                $('#integracja_bliskapaczka_' + dostawca + '').val(point.code);
                                $('#destinationCode_' + dostawca + '').val(point.code);
                                $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                                $("#BPWidget").remove();
                                $("#MapaWidocznaTlo").fadeOut(500, function() {
                                   $("#MapaWidocznaTlo").remove();
                                });
                                enableScroll();
                            },
                            posType: 'DELIVERY',
                            codOnly: pobranie,
                            operators: [{operator: Kurier}],
                            initialAddress: $('#receiverCity').val()
                        }
                );

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
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#apiForm").validate({
              rules: {
                waga         : { digits: true }
              }
            });

            $('#cod').change(function() {
                (($(this).is(':checked')) ? $("#codValue").prop('disabled', false) : $("#codValue").prop('disabled', true));
                $("#codValue").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
            });
            $('#insurance').change(function() {
                  (($(this).is(':checked')) ? $("#insuranceValue").prop('disabled', false) : $("#insuranceValue").prop('disabled', true));
                  $("#insuranceValue").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
            });

            $('#deliverytype').change(function() {
                $('#PrzyciskZatwierdz').attr('disabled','disabled');
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
            });

            $('#checkService').click(function (){
                if ( $('#weight').val() < 0.01 ) {
                    $.colorbox( { html:'<div id="PopUpInfo">Waga paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                    return;
                }
                if ( $('#length').val() < 0.01 ) {
                    $.colorbox( { html:'<div id="PopUpInfo">Długość paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                    return;
                }
                if ( $('#width').val() < 0.01 ) {
                    $.colorbox( { html:'<div id="PopUpInfo">Szerokość paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                    return;
                }
                if ( $('#height').val() < 0.01 ) {
                    $.colorbox( { html:'<div id="PopUpInfo">Wysokość zamówienia musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                    return;
                }
                $('#PrzyciskZatwierdz').attr('disabled','disabled');
                $('#DaneWysylki').slideUp();
                $('#DostepneUslugi').slideDown();
                $('#Uslugi').html('<div id="loader"></div>');
                $.ajax(
                    {
                        url: "ajax/bliskapaczka.php",
                        type: "POST",
                        data: {
                            action: 'operatorzy',
                            weight: $('#weight').val(),
                            length: $('#length').val(),
                            width: $('#width').val(),
                            height: $('#height').val(),
                            deliverytype: $('#deliverytype').val(),
                            codValue: $('#codValue').val(),
                            insuranceValue: $('#insuranceValue').val(),
                            orderId: '<?php echo $_GET['id_poz']; ?>',
                            insuranceValue: $('#insuranceValue').val(),
                        },
                        success: function( data )
                        {
                            $('#Uslugi').html(data);

                        }
                    });

            
            });


          });

          </script>

          <?php
            $waga_produktow = $zamowienie->waga_produktow;

            $AdresOK = true;
            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
            $adres_dom_lokal = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

            $PrzeksztalconyAdres = implode(' ', $adres_klienta); 
            if ( $PrzeksztalconyAdres != $zamowienie->dostawa['ulica'] ) {
                $AdresOK = false;
            }

            $klient = explode(' ', (string)$zamowienie->dostawa['nazwa']);

            $kodPocztowyNadawcy = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_KOD_POCZTOWY'];
            if(preg_match("/^([0-9]{2})(-[0-9]{3})?$/i",$kodPocztowyNadawcy)) {
            } else {
                $kodPocztowyNadawcy = substr((string)$kodPocztowyNadawcy,'0','2') . '-' . substr((string)$kodPocztowyNadawcy,'2','5'); 
            }
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

            <form action="sprzedaz/zamowienia_wysylka_bliskapaczka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" id="zakladka" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />

                  <input type="hidden" id="reference" name="reference" value="<?php echo time() . '-' . $_GET['id_poz']; ?>" />
                  <input type="hidden" id="additionalReference" name="additionalReference" value="<?php echo $_GET['id_poz']; ?>" />

                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o paczce</div>

                            <p>
                                <label for="deliverytype">Rodzaj dostawy:</label>
                                <?php
                                $domyslny = 'D2D';
                                if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                                    $domyslny = 'P2P';
                                }
                                $tablica = $apiKurier->bliskapaczka_product_array(false);
                                echo Funkcje::RozwijaneMenu('deliverytype', $tablica, $domyslny, 'id="deliverytype" style="width:326px;"');
                                unset($tablica);
                                ?>

                            </p> 

                            <p>
                                <label class="required" for="weight">Waga (kg):</label>
                                <input type="text" size="20" name="parcel[dimensions][weight]" id="weight" value="<?php echo ( isset($_POST['parcel']['dimensions']['weight']) ? $_POST['parcel']['dimensions']['weight'] : $waga_produktow ); ?>" class="required kropkaPustaZero" />
                            </p> 

                            <p id="Wymiary">
                                <label class="required" for="length">Wymiary (dł. x szer. x wys.):</label>
                                <input type="text" size="10" name="parcel[dimensions][length]" id="length" value="<?php echo ( isset($_POST['parcel']['dimensions']['length']) ? $_POST['parcel']['dimensions']['length'] : $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_WYMIARY_DLUGOSC'] ); ?>" class="required kropkaPustaZero" /> x
                                <input type="text" size="10" name="parcel[dimensions][width]" id="width" value="<?php echo ( isset($_POST['parcel']['dimensions']['width']) ? $_POST['parcel']['dimensions']['width'] : $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_WYMIARY_SZEROKOSC'] ); ?>" class="required kropkaPustaZero" /> x
                                <input type="text" size="10" name="parcel[dimensions][height]" id="height" value="<?php echo ( isset($_POST['parcel']['dimensions']['height']) ? $_POST['parcel']['dimensions']['height'] : $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_WYMIARY_WYSOKOSC'] ); ?>" class="required kropkaPustaZero" />
                            </p> 

                            <p>
                                <label for="cod" style="height:28px; line-height:28px;">Pobranie:</label>
                                <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                    <input type="checkbox" value="1" name="cod" id="cod" style="margin-right:20px;" <?php echo ( isset($_POST['cod']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="cod" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="codValue" id="codValue" value="<?php echo ( isset($_POST['codValue']) ? $_POST['codValue'] : '' ); ?>"  disabled="disabled" />
                                <?php } else { ?>
                                    <input type="checkbox" value="1" name="cod" id="cod" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="cod" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="codValue" id="codValue" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                                <?php } ?>
                            </p> 

                            <p>
                                <label for="insurance" style="height:28px; line-height:28px;">Dodatkowe ubezpieczenie:</label>
                                <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                    <input type="checkbox" value="1" name="insurance" id="insurance" style="margin-right:20px;" <?php echo ( isset($_POST['insurance']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="insurance" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="parcel[insuranceValue]" id="insuranceValue" value="<?php echo ( isset($_POST['parcel']['insuranceValue']) ? $_POST['parcel']['insuranceValue'] : '' ); ?>" disabled="disabled" />
                                <?php } else { ?>
                                    <input type="checkbox" value="1" name="insurance" id="insurance" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="insurance" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="parcel[insuranceValue]" id="insuranceValue" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                                <?php } ?>
                            </p>
                            <p>
                                <label for="additionalInformation">Informacje dodatkowe:</label>
                                <textarea cols="45" rows="2" name="additionalInformation" id="additionalInformation" ><?php echo ( isset($_POST['additionalInformation']) ? $_POST['additionalInformation'] : $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_ZAWARTOSC'] ); ?></textarea>
                            </p>

                            <p>
                                <button class="przyciskNon" type="button" id="checkService">Sprawdź dostępność usług</button>
                            </p>

                    </div>

                    <div class="poleForm" id="DostepneUslugi" style="display:none;">

                        <div class="naglowek">Dostępne oferty</div>

                        <div id="Uslugi"></div>

                    </div>

                    <div class="poleForm" id="DaneWysylki" style="display:none;">

                        <div class="naglowek">Wybrana oferta</div>

                        <div id="Wysylka"></div>

                    </div>

                </div>
                    
                <div class="OknoDodatkowe">

                    <div class="poleForm">

                        <div class="naglowek">Informacje</div>

                        <p>
                            <label class="readonly">Forma dostawy w zamówieniu:</label>
                            <input type="text" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <?php
                        if ( $zamowienie->info['wysylka_info'] != '' ) {
                                ?>
                                <p>
                                    <label class="readonly">Punkt odbioru:</label>
                                    <textarea cols="30" rows="2" name="punkt_odbioru" id="punkt_odbioru"  readonly="readonly" class="readonly"><?php echo $zamowienie->info['wysylka_info']; ?></textarea>
                                </p>
                                <?php
                                if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                                    ?>
                                    <p>
                                        <label class="readonly">Kod punktu odbioru:</label>
                                        <input type="text" name="punkt_odbioru_kod" value="<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>" readonly="readonly" class="readonly" />
                                    </p>
                                    <?php
                                }
                        }
                        ?>
                        <p>
                            <label class="readonly">Forma płatności w zamówieniu:</label>
                            <input type="text" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
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

                        <div class="naglowek">Informacje o odbiorcy</div>

                            <p>
                                <label for="receiverCompanyName">Firma:</label>
                                <input type="text" size="40" name="receiverCompanyName" id="receiverCompanyName" value="<?php echo ( isset($_POST['receiverCompanyName']) ? $_POST['receiverCompanyName'] : $zamowienie->dostawa['firma'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverFirstName">Imię:</label>
                                <input type="text" size="40" name="receiverFirstName" id="receiverFirstName" value="<?php echo ( isset($_POST['receiverFirstName']) ? $_POST['receiverFirstName'] : $klient['0'] ) ; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverLastName">Nazwisko:</label>
                                <input type="text" size="40" name="receiverLastName" id="receiverLastName" value="<?php echo ( isset($_POST['receiverLastName']) ? $_POST['receiverLastName'] : $klient['1'] ); ?>" class="klient" />
                            </p> 
                            <p id="AdresOdbiorcy">
                                <label for="receiverStreet">Ulica:</label>
                                <input type="text" size="40" name="receiverStreet" id="receiverStreet" value="<?php echo ( isset($_POST['receiverStreet']) ? $_POST['receiverStreet'] : $adres_klienta['ulica'] ); ?>" class="klient" />
                            </p> 

                            <p>
                                <label for="receiverBuildingNumber">Numer domu:</label>
                                <input type="text" size="40" name="receiverBuildingNumber" id="receiverBuildingNumber" value="<?php echo ( isset($_POST['receiverBuildingNumber']) ? $_POST['receiverBuildingNumber'] : $adres_dom_lokal['dom'] ); ?>" class="klient" />
                            </p> 

                            <p>
                                <label for="receiverFlatNumber">Numer lokalu:</label>
                                <input type="text" size="40" name="receiverFlatNumber" id="receiverFlatNumber" value="<?php echo ( isset($_POST['receiverFlatNumber']) ? $_POST['receiverFlatNumber'] : $adres_dom_lokal['mieszkanie'] ); ?>" class="klient" />
                            </p> 

                            <p>
                                <label for="receiverPostCode">Kod pocztowy:</label>
                                <input type="text" size="40" name="receiverPostCode" id="receiverPostCode" value="<?php echo ( isset($_POST['receiverPostCode']) ? $_POST['receiverPostCode'] : $zamowienie->dostawa['kod_pocztowy'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverCity">Miejscowość:</label>
                                <input type="text" size="40" name="receiverCity" id="receiverCity" value="<?php echo ( isset($_POST['receiverCity']) ? $_POST['receiverCity'] : $zamowienie->dostawa['miasto'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverCountryCode">Kraj:</label>
                                <?php 
                                $domyslnie = $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); 
                                $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                                echo Funkcje::RozwijaneMenu('receiverCountryCode', $tablicaPanstw, $domyslnie, 'id="receiverCountryCode" class="klient" style="width:210px;"' ); 

                                unset($tablicaPanstw);
                                ?>
                            </p> 
                            <p>
                                <label for="receiverPhoneNumber">Numer telefonu:</label>
                                <?php 
                                if ( $zamowienie->dostawa['telefon'] != '' ) {
                                    $NumerTelefonu = $zamowienie->dostawa['telefon'];
                                } else {
                                    $NumerTelefonu = $zamowienie->klient['telefon'];
                                }
                                ?>
                                <input type="text" size="40" name="receiverPhoneNumber" id="receiverPhoneNumber" value="<?php echo preg_replace( '/[^0-9+]/', '', ( isset($_POST['receiverPhoneNumber']) ? (string)$_POST['receiverPhoneNumber'] : (string)$NumerTelefonu )); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverEmail">Adres e-mail:</label>
                                <input type="text" size="40" name="receiverEmail" id="receiverEmail" value="<?php echo ( isset($_POST['receiverEmail']) ? $_POST['receiverEmail'] : $zamowienie->klient['adres_email'] ); ?>" class="klient" />
                            </p> 
                        
                    </div>
                    
                </div>

              </div>

              <div class="przyciski_dolne">
                <input id="PrzyciskZatwierdz" type="submit" class="przyciskNon" value="Utwórz przesyłkę" disabled="disabled" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>

              </div>
            </form>
            <?php //} else {
                //echo 'Sprawdź konfigurację modułu';
            //} ?>
        
        </div>
      </div>

    <?php } ?>
    
    </div>    
    
    <?php
    include('stopka.inc.php');    
    
} ?>
