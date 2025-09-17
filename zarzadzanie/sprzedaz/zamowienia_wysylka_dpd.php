<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'DPD';

    if ( !isset($_POST['akcja']) ) {
        $apiKurier = new DpdApi((int)$_GET['id_poz']);
    }
    $weight_total = 0;
    $parcel = array();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $apiKurier = new DpdApi((int)$_POST['id']);


        $packageDetails['Paczki'] = array();
        for ( $i = 0, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
            $parcel['SizeX']               = ceil($_POST['parcel']['dlugosc'][$i]);
            $parcel['SizeY']               = ceil($_POST['parcel']['wysokosc'][$i]);
            $parcel['SizeZ']               = ceil($_POST['parcel']['szerokosc'][$i]);
            $parcel['Weight']              = $_POST['parcel']['waga'][$i];
            $parcel['Content']             = $filtr->process($_POST['parcel']['zawartosc'][$i]);
            $parcel['CustomerData1']       = $filtr->process($_POST['dpd_uwagi']);
            $weight_total += $_POST['parcel']['waga'][$i];
            array_push($packageDetails['Paczki'], $parcel);
        }

        $packageDetails["package_amount"] = count($_POST['parcel']['dlugosc']);
        $packageDetails["reference_number"] = uniqid();
        $packageDetails["Ref1"] = $filtr->process($_POST['dpd_uwagi']);
        $packageDetails["Ref2"] = '';
        $packageDetails["Ref3"] = '';

        if ( isset($_POST['pobranie']) && $_POST['pobranie'] == '1' ) {
            $packageDetails["COD"] =  $filtr->process($_POST['dpd_pobranie']);
        } else {
            $packageDetails["COD"] = '0';
        }

        if ( isset($_POST['pudo']) && $_POST['pudo'] == '1' ) {
            $packageDetails["PUDO"] =  $filtr->process($_POST['pudo_kod']);
        } else {
            $packageDetails["PUDO"] = '';
        }

        if ( isset($_POST['dox']) && $_POST['dox'] == '1' ) {
            $packageDetails["dox"] =  $filtr->process($_POST['dox']);
        } else {
            $packageDetails["dox"] = '0';
        }

        if ( isset($_POST['u_ubezp']) && $_POST['u_wart_ubezp'] > 1000 ) {
            $packageDetails["DeclaredValue"] =  $filtr->process($_POST['u_wart_ubezp']);
        } else {
            $packageDetails["DeclaredValue"] =  '0';
        }

        $packageDetails["Weight"] = $weight_total;


        $shipFromDpd["Fid"] = $_POST['fid'];

        $shipFromDpd["Company"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_NAZWA'];
        $shipFromDpd["Name"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_IMIE_NAZWISKO'];

        if ( $_POST['fid'] == $apiKurier->polaczenie['INTEGRACJA_DPD_FID'] ) {
            $shipFromDpd["Street"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_ULICA'];
            $shipFromDpd["City"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_MIASTO'];
            $shipFromDpd["PostalCode"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_KOD_POCZTOWY'];
        } else {
            $shipFromDpd["Street"] = $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_NADAWCA_ULICA'];
            $shipFromDpd["City"] = $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_NADAWCA_MIASTO'];
            $shipFromDpd["PostalCode"] = $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_NADAWCA_KOD_POCZTOWY'];
        }

        $shipFromDpd["CountryCode"] = "PL";
        $shipFromDpd["Phone"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_TELEFON'];
        $shipFromDpd["Email"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_EMAIL'];

        $apiKurier->setShipFrom($shipFromDpd);
        $apiKurier->setShipTo($_POST['shipToDpd']);
        $apiKurier->setPayerType($_POST['dpd_platnik']);
        $apiKurier->setPackageDetails($packageDetails);

        $PaczkiSer = serialize($packageDetails['Paczki']);

        $wynik = $apiKurier->registerNewPackage();

        if ( is_array($wynik) ) {

            if ( $wynik['type'] == 'error') {

                $komunikat = $wynik['message'];

            } else {

                $NumerListu = '';
                foreach ( $wynik['dane']['parcels']->Parcel as $Paczka ) {
                    $NumerListu .= $Paczka->Waybill.',';
                }
                $NumerListu = substr((string)$NumerListu,0, -1);

                foreach($wynik['dane']['reference'] as $i => $value) {
                    $NumerPaczkiId = $value;
                }
                //$NumerPaczkiId = $wynik['dane']['reference']['0'];

                $pola = array(
                        array('orders_id',$filtr->process($_POST["id"])),
                        array('orders_shipping_type',$api),
                        array('orders_shipping_number',$NumerListu),
                        array('orders_shipping_weight',$weight_total),
                        array('orders_parcels_quantity',count($_POST['parcel']['dlugosc'])),
                        array('orders_shipping_status','1'),
                        array('orders_shipping_date_created', 'now()'),
                        array('orders_shipping_date_modified', 'now()'),
                        array('orders_shipping_comments', $NumerPaczkiId),
                        array('orders_shipping_packages', $PaczkiSer),
                        array('orders_shipping_misc', $_POST['fid']),
                        array('orders_shipping_to_country', $_POST['shipToDpd']['CountryCode']),

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
                        <div id="WidokMapy"><div id='MapaZawartoscDPD'></div></div>
                    </div>
                </div>
            </div>

            <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        

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
                   $("#MapaWidocznaTlo").fadeOut(500, function() {
                       $("#MapaWidocznaTlo").remove();
                   });
                   enableScroll();
                });

            });

            function PokazMape(KodPunktu) {
                var kod = KodPunktu;
                disableScroll();
                $("#WybierzMape").append('<div id="MapaWidocznaTlo"></div>');
                $('#WybierzMape').removeClass('MapaUkryta').addClass('MapaWidoczna').show();

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
                    $('#pudo_kod').val(value);
                    $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                    $("#MapaWidocznaTlo").fadeOut(500, function() {
                        $("#MapaWidocznaTlo").remove();
                    });
                    enableScroll();
                }

                }, !1);


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

              if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
              $("#addrow").click(function() {
                
                var id = $(".UsunPozycjeListy").length;

                $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="4" name="parcel[waga][]" class="kropkaPusta required" /></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="50" name="parcel[zawartosc][]" /></td></tr>');

                $(".kropkaPustaZero").change(		
                  function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase();
                    if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                        //
                        if ($(this).val() != '') {
                            zamien_krp($(this), '0.00');
                        }
                        //
                    }
                  }
                ); 
                $(".kropkaPusta").change(		
                  function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase();
                    if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                        //
                        zamien_krp($(this),'');
                        //
                    }
                  }
                ); 

                pokazChmurki();
                
                if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
                
              });

              $('body').on('click', '.UsunPozycjeListy', function() {
                var row = $(this).parents('.item-row');
                $(this).parents('.item-row').remove();
                if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
              });

              $.validator.addMethod("valueNotEquals", function (value, element, arg) {
                return arg != value;
              }, "Wybierz opcję");

              $("#apiForm").validate({
                rules: {
                  zawartosc    : { required: true },
                  waga         : { digits: true }
                }
              });

              $('#ubezpieczenie').change(function() {
                 (($(this).is(':checked')) ? $("#ubezpieczenie_wartosc").prop('disabled', false) : $("#ubezpieczenie_wartosc").prop('disabled', true));
                 $("#ubezpieczenie_wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#pobranie').change(function() {
                  (($(this).is(':checked')) ? $("#dpd_pobranie").prop('disabled', false) : $("#dpd_pobranie").prop('disabled', true));
                  $("#dpd_pobranie").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#pudo').change(function() {
                  (($(this).is(':checked')) ? $("#pudo_kod").prop('disabled', false) : $("#pudo_kod").prop('disabled', true));
                  (($(this).is(':checked')) ? $("#pudo_przycisk").prop('disabled', false) : $("#pudo_przycisk").prop('disabled', true));
                  (($(this).is(':checked')) ? $("#pudo_przycisk").show() : $("#pudo_przycisk").hide());
              });

            });
            </script>

            <?php
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;
            $wymiary        = array();

            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
            $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_DPD_WYMIARY_DLUGOSC'];
            $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_DPD_WYMIARY_SZEROKOSC'];
            $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_DPD_WYMIARY_WYSOKOSC'];

            ?>

            <form action="sprzedaz/zamowienia_wysylka_dpd.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o przesyłce</div>

                        <p>
                            <label for="dox">FID nadawcy:</label>
                            <?php 
                            if ( $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_FID'] != '' ) {
                                $tablia_fid = Array();
                                $tablia_fid[] = array('id' => $apiKurier->polaczenie['INTEGRACJA_DPD_FID'], 'text' => $apiKurier->polaczenie['INTEGRACJA_DPD_FID']);
                                $tablia_fid[] = array('id' => $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_FID'], 'text' => $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_FID']);
                            } else {
                                $tablia_fid = Array();
                                $tablia_fid[] = array('id' => $apiKurier->polaczenie['INTEGRACJA_DPD_FID'], 'text' => $apiKurier->polaczenie['INTEGRACJA_DPD_FID']);
                            }
                            echo Funkcje::RozwijaneMenu('fid', $tablia_fid, ((isset($_GET['fid'])) ? $filtr->process($_GET['fid']) : ''), ' style="width:150px"'); 
                            ?>
                        </p> 

                        <p>
                            <label>Kto płaci za przesyłkę:</label>
                            <?php
                            echo Konfiguracja::Dopuszczalne_Wartosci_Auto('SENDER,RECEIVER', ( isset($_POST['dpd_platnik']) ? $_POST['dpd_platnik'] : $apiKurier->polaczenie['INTEGRACJA_DPD_PLATNIK'] ), 'dpd_platnik', '', 'nadawca,odbiorca', '2' );
                            ?>
                        </p> 

                        <p>
                            <label for="pudo">Usługa DPD PickUp:</label>
                            <input type="checkbox" value="1" name="pudo" id="pudo" style="margin-right:20px;" <?php echo ( isset($_POST['pudo']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="pudo" style="margin-right:10px;"></label>
                            <input type="text" size="20" name="pudo_kod" id="pudo_kod" value="<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>" disabled="disabled" />
                            <input type="text" id="pudo_przycisk" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape('<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>')" disabled="disabled" style="display:none;" />
                        </p> 

                        <p>
                            <label for="dox">Przesyłka kopertowa:</label>
                            <input type="checkbox" value="1" name="dox" id="dox" style="margin-right:20px;" <?php echo ( isset($_POST['dox']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="dox" style="margin-right:10px;"></label>
                        </p> 

                        <p>
                            <label for="pobranie">Pobranie:</label>
                            <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                <input type="checkbox" value="1" name="pobranie" id="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['pobranie']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="dpd_pobranie" id="dpd_pobranie" value="<?php echo ( isset($_POST['dpd_pobranie']) ? $_POST['dpd_pobranie'] : '' ); ?>" disabled="disabled" />
                            <?php } else { ?>
                                <input type="checkbox" value="1" name="pobranie" id="pobranie" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="dpd_pobranie" id="dpd_pobranie" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                            <?php } ?>
                        </p> 

                        <p>
                          <?php
                            $zaznaczenie = false;
                            if ( INTEGRACJA_DPD_UBEZPIECZENIE == 'tak' ) {
                                $zaznaczenie = true;
                            }
                            if ( isset($_POST['u_ubezp']) ) {
                                $zaznaczenie = true;
                            }
                          ?>
                          <label for="ubezpieczenie">Deklarowana wartość przesyłki 1000zł: Wartość [PLN]:</label>
                          <input id="ubezpieczenie" value="1" type="checkbox" name="u_ubezp" style="margin-right:20px;" <?php echo ( $zaznaczenie == true ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="ubezpieczenie" ></label> 
                          <input class="kropkaPustaZero" type="text" size="20" name="u_wart_ubezp" id="ubezpieczenie_wartosc" value="<?php echo ( $zaznaczenie == true ? $zamowienie->info['wartosc_zamowienia_val'] : '' ); ?>" <?php echo ( $zaznaczenie == false ? 'disabled="disabled"' : '' ); ?> /><em class="TipIkona"><b>Podaje się tylko wtedy, gdy wartość paczki w przesyłce przekracza 1000 PLN. Usługa to dodatkowe ubezpieczenie przesyłki wartościowej. Usługa łączy się oczywiście z dodatkową opłatą.</b></em>
                        </p> 

                        <p>
                            <label for="dpd_uwagi">Uwagi [max. 200 znaków]:</label>
                            <textarea cols="45" rows="2" name="dpd_uwagi" id="dpd_uwagi" onkeyup="licznik_znakow(this,'iloscZnakow',200)" ><?php echo ( isset($_POST['dpd_uwagi']) ? $_POST['dpd_uwagi'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?></textarea>
                        </p> 

                        <p>
                            <label></label>
                            <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow">200</span></span>
                        </p>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Informacje o paczkach</div>

                        <table class="listing_tbl">
                          <tr>
                            <td style="width:50px"></td>
                            <td class="Paczka">Długość [cm]</td>
                            <td class="Paczka">Szerokość [cm]</td>
                            <td class="Paczka">Wysokość [cm]</td>
                            <td class="Paczka">Waga [kg]</td>
                            <td class="Paczka">Zawartość</td>
                          </tr>

                          <tr class="item-row">
                            <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                            <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : ceil($waga_produktow) ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['zawartosc']['0']) ? $_POST['parcel']['zawartosc']['0'] : INTEGRACJA_DPD_ZAWARTOSC ); ?>" size="50" name="parcel[zawartosc][]"  /></td>
                          </tr>

                          <?php
                          if ( isset($_POST['parcel']['dlugosc']) && count($_POST['parcel']['dlugosc']) > 1 ) {
                            for ( $i = 1, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
                              ?>
                              <tr class="item-row">
                                <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc'][$i]) ? $_POST['parcel']['dlugosc'][$i] : '' ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc'][$i]) ? $_POST['parcel']['szerokosc'][$i] : '' ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc'][$i]) ? $_POST['parcel']['wysokosc'][$i] : '' ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga'][$i]) ? $_POST['parcel']['waga'][$i] : '' ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['zawartosc'][$i]) ? $_POST['parcel']['zawartosc'][$i] : INTEGRACJA_DPD_ZAWARTOSC ); ?>" size="50" name="parcel[zawartosc][]" /></td>

                              </tr>
                              <?php
                            }
                          }
                          ?>

                          <tr id="hiderow">
                            <td colspan="10" style="padding-left:10px;padding-top:10px;padding-bottom:10px;"><a id="addrow" href="javascript:void(0)" class="dodaj">dodaj paczkę</a></td>
                          </tr>

                        </table>

                    </div>

                </div>

                <div class="OknoDodatkowe">

                    <div class="poleForm">
                    
                        <div class="naglowek">Informacje</div>

                        <p>
                            <label class="readonly">Forma dostawy w zamówieniu:</label>
                            <input type="text" size="34" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <?php
                        if ( $zamowienie->info['wysylka_info'] != '' ) {
                                if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                                    ?>
                                    <p>
                                        <label class="readonly">Kod punktu odbioru:</label>
                                        <input type="text" name="punkt_odbioru_opis" value="<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>" readonly="readonly" class="readonly" />
                                    </p>
                                    <?php
                                }
                        }
                        ?>
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
                            <input type="text" size="40" name="shipToDpd[Company]" id="adresat_Company" value="<?php echo Funkcje::formatujTekstInput($zamowienie->dostawa['firma']); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Name">Nazwisko i imię:</label>
                            <input type="text" size="40" name="shipToDpd[Name]" id="adresat_Name" value="<?php echo preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Address">Ulica:</label>
                            <input type="text" size="40" name="shipToDpd[Address]" id="adresat_Address" value="<?php echo $zamowienie->dostawa['ulica']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_PostalCode">Kod pocztowy:</label>
                            <input type="text" size="40" name="shipToDpd[PostalCode]" id="adresat_PostalCode" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_City">Miejscowość:</label>
                            <input type="text" size="40" name="shipToDpd[City]" id="adresat_City" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                        </p> 

                        <p>
                            <label for="adresat_CountryCode">Kraj:</label>
                            <?php 
                            $domyslnie = $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); 
                            $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                            echo Funkcje::RozwijaneMenu('shipToDpd[CountryCode]', $tablicaPanstw, $domyslnie, 'id="adresat_CountryCode" class="klient" style="width:230px;"' ); 

                            unset($tablicaPanstw);
                            ?>
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

                            <input type="text" size="40" name="shipToDpd[Phone]" id="adresat_Phone" value="<?php echo $NumerTelefonu; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Email">Adres e-mail:</label>
                            <input type="text" size="40" name="shipToDpd[Email]" id="adresat_Email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
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
