<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (Sesje::TokenSpr()) {
  
    // bliska paczka
    
    if ( isset($_POST['modul']) && $_POST['modul'] == 'bliskapaczka' && isset($_POST['adres']) && isset($_POST['koszyk']) && isset($_POST['klucz']) && isset($_POST['plik']) && isset($_POST['operatorzy']) ) {
    
        $tekst = "";
        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];
        $klucz_api = $_POST['klucz'];
        $modul_id = $_POST['modul_id'];
        $operatorzy = base64_decode((string)$_POST['operatorzy']);

        if ( $_POST['plik'] == 'koszyk' ) {

            $tekst = "<script>
            
            $(document).ready(function() {

                var OperatorsArray = " . $operatorzy . ";
                var Operatorzy = '';

                BPWidget.init(
                    document.getElementById('WynikMapBliskapaczka'),
                    {
                        callback: function(point) {";

                            $tekst .= "
                            $('#punktBliskaPaczka').val(point.city + ' - ' + point.street);
                                        $('#ShippingDestinationCode').val(point.code);
                                        $('#kioskOpis').html(point.brand + ' - ' + point.city);
                                        $('#OpisPunktuOdbioru').val(point.brand + ' - ' + point.city);

                                        var value = point.operator + ' - ' + point.street + ', ' + point.postalCode + ' ' + point.city  + ' - ' + ( point.description != null ? point.description  + '; ' : '') + ' (' + point.code + ')';
                                        var punktodbioru = point.code;
                                        var opis_punkt = point.description;
                                        $('#WybranyPunktBliskaPaczka').html(value);

                                        var OperatorName = point.operator;
                                        var Operatorzy = OperatorsArray.find(tree => tree.operator === OperatorName);

                                        var data_id = " . $modul_id  .";
                                        var cena = Operatorzy.price;

                                        var cenaFormatowana = format_zl(Operatorzy.price) + ' " . $_SESSION['domyslnaWaluta']['symbol'] . "';
                                        $.ajax({
                                          type: 'POST',
                                          data: {data: data_id, price: cena}, 
                                          dataType : 'json',
                                          url: 'inne/zmiana_wysylki_bliskapaczka.php',
                                          success: function(json){
                                              PreloadWylaczSzybko();
                                              $('#rodzaj_platnosci').html(json['platnosci']).show(); 
                                              $('#podsumowanie_zamowienia').html(json['podsumowanie']).show(); 
                                              $('#CenaWysylki' + data_id).html(cenaFormatowana); 
                                              $('#przycisk_zamow').show();
                                          }
                                        });

                                        $.post('" . $adres . "?tok=" . Sesje::Token() . "', { rodzaj: 'wysylka_bliskapaczka', koszyk:1, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt, koszt:cena }, function(data) { $('#WybranyPunktBliskaPaczka').show(); myModal.close(); })";
                            $tekst .= "

                        },
                        posType: 'DELIVERY',
                        codOnly: false,
                        operators: OperatorsArray
                    }
                );
                
            });
            </script>";

        }

        $tekst .= '<div id="WynikMapBliskapaczka"></div>';
        echo $tekst;
        
    }    
    
    // Orlen paczka
    
    if ( isset($_POST['modul']) && $_POST['modul'] == 'ruch' && isset($_POST['adres']) && isset($_POST['koszyk']) ) {
    
        $tekst = '';

        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];

        $tekst .= "
            <script>

                (function (o, r, l, e, n) {
                    o[l] = o[l] || [];
                    var f = r.getElementsByTagName('head')[0];
                    var j = r.createElement('script');
                    j.async = true;
                    j.src = e + 'widget.js?token=' + n + '&v=1.0.0&t=' + Math.floor(new Date().getTime() / 1000)
                    f.insertBefore(j, f.firstChild);
                })
                (window, document, 'orlenpaczka', 'https://mapa.orlenpaczka.pl/', '".$_POST['apikey']."');";

            if ( $koszyk == false ) {

                $tekst .= "
                    document.addEventListener('click', (event) => {
                        let clickedButton = event.target.closest('.marker-list-item__select'); 
                        if (clickedButton) {
                            let pointCode = document.getElementById('ShippingDestinationCode');
                            let pointDesc = document.getElementById('kioskRuchu');
                            // do testowania
                            //console.log('Wybrano punkt:', pointCode.value + ' : ' + pointDesc );

                            $.post('".$adres."?tok=".Sesje::Token()."', { rodzaj: 'wysylka_paczkaruch', koszyk:0, value:pointDesc.value, punktodbioru:pointCode.value, punktopis:pointDesc.value }, function(data) { $('.WybranyPunktMapyRuch').show(); myModal.close(); });

                        }
                    });";

            } else {
                $tekst .= "
                    document.addEventListener('click', (event) => {
                        let clickedButton = event.target.closest('.marker-list-item__select'); 
                        if (clickedButton) {
                            let pointCode = document.getElementById('ShippingDestinationCode');
                            let pointDesc = document.getElementById('WybranyPunktKiosk').textContent;
                            // do testowania
                            //console.log('Wybrano punkt:', pointCode.value + ' : ' + pointDesc );

                            $.post('".$adres."?tok=".Sesje::Token()."', { rodzaj: 'wysylka_paczkaruch', koszyk:1, value:pointDesc, punktodbioru:pointCode.value, punktopis:pointDesc }, function(data) { $('#WybranyPunktKiosk').show(); myModal.close(); });

                        }
                    });";
            }

            $tekst .= "
                // do testowania
                //document.addEventListener('click', (event) => {
                //    console.log('Kliknięto:', event.target);
                //});
            </script>
        ";

        if ( $koszyk == false ) {
            $tekst .= '<div id="WidokMapWysylkaRuch" class="orlen-widget" data-label="#kioskRuchu" data-target="#ShippingDestinationCode" data-modal="false"></div>';
        } else {
            $tekst .= '<div id="WidokMapWysylkaRuch" class="orlen-widget" data-label="#WybranyPunktKiosk" data-target="#ShippingDestinationCode" data-modal="false"></div>';
        }

        echo $tekst;
        
    }    
  
    // dhl
    
    if ( isset($_POST['modul']) && $_POST['modul'] == 'dhl' && isset($_POST['adres']) && isset($_POST['koszyk']) ) {
    
        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];
        $kraj          = 'PL';

        if ( isset($_SESSION['krajDostawy']['kod']) ) {
            $kraj = $_SESSION['krajDostawy']['kod'];
        }

        $tekst = '<script>
        
        $(document).ready(function() {

            function IsJsonString(str) {
              try {
                var json = JSON.parse(str);
                return (typeof json === \'object\');
              } catch (e) {
                return false;
              }
            }            

            function listenMessage(msg) {
              
                    if ( IsJsonString(msg.data) ) {';
            
                    if ( $koszyk == false ) {

                         $tekst .= 'var point = JSON.parse(msg.data);
                                    var opis_punkt = point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + ", " + point.name;
                                    var value = point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + " - " + point.name + " (" + point.sap + ")";
                                    var punktodbioru = point.sap;
                                    
                                    $(\'#punktDhl\').val(value);
                                    $(\'#ShippingDestinationCode\').val(point.sap);
                                    $(\'#punktOpis\').html(point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + ", " + point.name);
                                    
                                    $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_dhlparcelshop\', koszyk:0, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyDhl").show(); myModal.close(); });';
                    
                    } else {
                      
                         $tekst .= 'var point = JSON.parse(msg.data);
                                    var opis_punkt = point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + ", " + point.name;
                                    var value = point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + " - " + point.name + " (" + point.sap + ")";
                                    var punktodbioru = point.sap;

                                    $("#WybranyPunktDhl").html(value);

                                    $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_dhlparcelshop\', koszyk:1, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPunktDhl").show(); myModal.close(); });';
                                      
                    }
                    
                    $tekst .= '} };

            if (window.addEventListener) {
                window.addEventListener("message", listenMessage, false);
            } else {
                window.attachEvent("onmessage", listenMessage);
            }

        
        });        

        </script>';        

        $tekst .= '<div id="WynikMapDhl"><iframe frameborder="0" allow="geolocation" src="https://parcelshop.dhl.pl/mapa?country='.$kraj.'"></iframe></div>';

        echo $tekst;
        
    }  
  
    // DPD
    
    if ( isset($_POST['modul']) && $_POST['modul'] == 'dpd' && isset($_POST['adres']) && isset($_POST['koszyk']) ) {
    
        $tekst = '';

        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];
        $api_key = $_POST['apikey'];

        $kraj_wysylki = '&query='.(isset($_SESSION['krajDostawy']['kod']) ? $_SESSION['krajDostawy']['kod'] : 'PL' );

        $tekst = '<script id="dpd-widget">
        
        function pointSelected(pointID){
            alert(\'Wybrano punkt: \'+pointID);
        }

        var iframe = document.createElement("iframe");

        iframe.src = "//pudofinder.dpd.com.pl/widget?key='.$api_key.$kraj_wysylki.'";
        iframe.style.width = "100%";
        iframe.style.border = "medium";
        iframe.style.minHeight = "600px";
        iframe.allow = "geolocation";

        var zawartosc = document.getElementById("WynikMapDPD");
        zawartosc.appendChild(iframe);

        var eventListener = window[window.addEventListener ? "addEventListener" : "attachEvent"];
        var messageEvent = ("attachEvent" == eventListener)? "onmessage" : "message";
        eventListener(messageEvent, function(a) {

            if (a.data.height && !isNaN(a.data.height)) {

                iframe.style.height = a.data.height + "px"; // Wysokość przekazana w danych

            } else if( a.data.point_id) {';


                if ( $koszyk == false ) {

                     $tekst .= 'var value = a.data.point_id;
                                var punktodbioru = a.data.point_id;
                                var opis_punkt = a.data.point_id;
                                $(\'#punktDPD\').val(value);
                                $(\'#ShippingDestinationCode\').val(value);
                                $(\'#punktOpis\').html(value);
                                    
                                $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_dpdpickup\', koszyk:0, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyDPD").show(); myModal.close(); });';

                } else {

                    $tekst .= 'var value = a.data.point_id;
                               var punktodbioru = a.data.point_id;
                               var opis_punkt = a.data.point_id;
                                    
                               $("#WybranyPunktDPD").html(value);

                                $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_dpdpickup\', koszyk:1, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPunktDPD").show(); myModal.close(); });';
                }

            $tekst .= '}
        }, !1);

        </script>';

        $tekst .= '<div id="WynikMapDPD"></div>';        

        echo $tekst;
        
    }  
  
    // poczta odbior w punkcie
    
    if ( isset($_POST['modul']) && $_POST['modul'] == 'pocztaodbior' && isset($_POST['adres']) && isset($_POST['koszyk']) && isset($_POST['pobranie']) && isset($_POST['ulica']) && isset($_POST['punkty']) ) {
      
        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];      
        $pobranie = $_POST['pobranie'];
        $adres_ulica = $_POST['ulica'];
        $jakie_punkty = $_POST['punkty'];
        
        $tekst = "<script>

        $(document).ready(function() {
                              
            " . $pobranie . "
            var address = \"" . $adres_ulica . "\";";
            
            if ( $jakie_punkty == 'poczta' ) {
            
                 $tekst .= "var type = [\"POCZTA\"];";
                 
            }

            $tekst .= "PPWidgetApp.toggleMap(function(callback) { ";

                        if ( $koszyk == false ) {
                          
                             $tekst .= "var value = callback['name'] + ' - ' + callback['city'] + ' - ' + callback['street']; 
                                        var punktodbioru = callback['pni']; 

                                        $('#punktPoczta').val(callback['name'] + ' - ' + callback['city'] + ' - ' + callback['street']); 
                                        $('#ShippingDestinationCode').val(callback['pni']); 
                                        var opis_punkt = callback['description'];
                                        opis_punkt = opis_punkt.replace(/#/gi,'<br />');
                                        $('#punktOpis').html(opis_punkt); 
                                        $('#OpisPunktuOdbioru').val(opis_punkt); 

                                        $.post('" . $adres . "?tok=" . Sesje::Token() . "', { rodzaj: 'wysylka_pocztapunkt', koszyk:0, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt }, function() { $('.WybranyPunktMapyPoczta').show() }); ";
                                        
                        } else {
                          
                             $tekst .= "var value = callback['name'] + ' - ' + callback['city'] + ' - ' + callback['street'];
                                        var punktodbioru = callback['pni'];
                                        var opis_punkt = callback['description'];
                                        
                                        $('#WybranyPunktPoczta').html(value);
                                        
                                        $.post('" . $adres . "?tok=" . Sesje::Token() . "', { rodzaj: 'wysylka_pocztapunkt', koszyk:1, value:value, punktodbioru:punktodbioru, punktopis:opis_punkt }, function() { $('#WybranyPunktPoczta').show() }); ";                          
                          
                        }

                      $tekst .= "},
                      pobranie, 
                      address
                      " . (($jakie_punkty == 'poczta') ? ',type' : '') . "
                      );

        });

        </script>";  
        
        echo $tekst;
        
    }
    
    // paczkomaty
    
    if ( isset($_POST['modul']) && ($_POST['modul'] == 'paczkomaty' || $_POST['modul'] == 'paczkomaty_eko' || $_POST['modul'] == 'paczkomaty_weekend') && isset($_POST['adres']) && isset($_POST['koszyk']) ) {
    
        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];
        $tekst = '';

        if ( $_POST['token'] != '' ) {

            echo '<link rel="stylesheet" href="https://geowidget.inpost.pl/inpost-geowidget.css"/>';
            echo '<script src="https://geowidget.inpost.pl/inpost-geowidget.js" defer></script>';
            $tekst .= '<div id="WidokMapWysylkaPaczkomaty">';
            $tekst .= '<inpost-geowidget onpoint="afterPointSelected" token="'.$_POST['token'].'" language="pl" config="parcelCollect"></inpost-geowidget>';
            $tekst .= '</div>';

            $tekst .= '<script>
                        function afterPointSelected(point) {
                            var opis_punkt = point.address["line1"] + ", " + point.address["line2"] + " - " + ( point["location_description"] != null ? point["location_description"] : "");
                            var punktodbioru = point.name;
                            var opis_punktu_z_kodem = opis_punkt + " " + " (" + point.name + ")";
                            ';
                        if ( $koszyk == false ) {

                             $tekst .= 'if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost" ) {                                       
                                             $("#paczkomat").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCode").val(punktodbioru);
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {                                      
                                             $("#paczkomatWeekend").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCodeWeekend").val(punktodbioru);
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {                                       
                                             $("#paczkomatEko").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCodeEko").val(punktodbioru);
                                        }
                                        
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost" ) {    
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {    
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_weekend\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {     
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_eko\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }';                                                                           
                                        
                        } else {
                          
                             $tekst .= 'if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost" ) {    
                                             $("#WybranyPaczkomat").html(opis_punktu_z_kodem);
                                        }
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {    
                                             $("#WybranyPaczkomatWeekend").html(opis_punktu_z_kodem);
                                        }                                    
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {
                                             $("#WybranyPaczkomatEko").html(opis_punktu_z_kodem);
                                        } 
                                        
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost" ) {    
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomat").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {    
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_weekend\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomatWeekend").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {     
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_eko\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomatEko").show(); myModal.close(); });
                                        }';                                         

                        }                                

            $tekst .= '
                        }
                        </script>';

        } else {

            echo '<script async src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>';
            echo '<link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css"/>';

            $tekst = '<script>    
            
                $(document).ready(function() {

                window.easyPackAsyncInit = function () {
                    easyPack.init({       
                        defaultLocale: \'pl\',
                        mapType: \'osm\',
                        searchType: \'osm\',
                        mobileSize: 2000,
                        points: {
                            types: [\'parcel_locker\'],
                            functions: [\'parcel_collect\']
                        },
                        map: {
                            initialTypes: [\'parcel_locker\']
                        }              
                    });

                    var map = easyPack.mapWidget(\'WidokMapWysylkaPaczkomaty\', function(point) {';

                        $tekst .= '
                            var opis_punkt = point.address["line1"] + ", " + point.address["line2"] + " - " + ( point["location_description"] != null ? point["location_description"] : "");
                            var punktodbioru = point.name;
                            var opis_punktu_z_kodem = opis_punkt + " " + " (" + point.name + ")";
                        ';
                    
                        if ( $koszyk == false ) {

                             $tekst .= 'if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost" ) {                                  
                                             $("#paczkomat").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCode").val(punktodbioru);
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {                                       
                                             $("#paczkomatWeekend").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCodeWeekend").val(punktodbioru);
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {                                     
                                             $("#paczkomatEko").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCodeEko").val(punktodbioru);
                                        }
                                        
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost" ) {     
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) { 
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_weekend\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_eko\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }';                                          
                                        
                        } else {
                          
                             $tekst .= 'if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost" ) {    
                                             $("#WybranyPaczkomat").html(opis_punktu_z_kodem);
                                        }
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {
                                             $("#WybranyPaczkomatWeekend").html(opis_punktu_z_kodem);
                                        }                                    
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {
                                             $("#WybranyPaczkomatEko").html(opis_punktu_z_kodem);
                                        }   
                                        
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost" ) {    
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomat").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_weekend" ) {
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_weekend\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomatWeekend").show(); myModal.close(); });
                                        }
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_eko" ) {
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_eko\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomatEko").show(); myModal.close(); });
                                        }';                                         

                        }                                
                          
                    $tekst .= '});';
                    
                if ( isset($_SESSION['adresDostawy']['miasto']) ) {
                     $tekst .= 'map.searchPlace("' . $_SESSION['adresDostawy']['miasto'] . '");';
                }

                $tekst .= '} 
            
            });
            
            </script>';
            $tekst .= '<div id="WidokMapWysylkaPaczkomaty"></div>';

        }

        echo $tekst;

    }
        
    // paczkomaty International
    
    if ( isset($_POST['modul']) && ($_POST['modul'] == 'paczkomaty_international') && isset($_POST['adres']) && isset($_POST['koszyk']) ) {
    
        $koszyk = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres = $_POST['adres'];
        $tekst = '';

        if ( $_POST['token'] != '' ) {

            $Kraj = $_SESSION['krajDostawy']['kod'];
            $DostepneKrajeTMP = explode(';', $_POST['dostepne_kraje']);

            if (($key = array_search($Kraj, $DostepneKrajeTMP)) !== false) {
                unset($DostepneKrajeTMP[$key]);
            }
            $DostepneKraje = implode(',', $DostepneKrajeTMP);
            $DostepneKraje = $Kraj . ',' . $DostepneKraje;

            echo '<link rel="stylesheet" href="https://geowidget.inpost-group.com/inpost-geowidget.css"/>';
            echo '<script src="https://geowidget.inpost-group.com/inpost-geowidget.js" defer></script>';
            $tekst .= '<div id="WidokMapWysylkaPaczkomaty">';
            $tekst .= '<inpost-geowidget onpoint="afterPointSelected" token="'.$_POST['token'].'" language="'.$_SESSION['domyslnyJezyk']['kod'].'" country="'.$DostepneKraje.'" config="parcelCollect"></inpost-geowidget>';
            $tekst .= '</div>';

            $tekst .= '<script>
                        function afterPointSelected(point) {
                            var opis_punkt = point.address["line1"] + ", " + point.address["line2"] + " - " + ( point["location_description"] != null ? point["location_description"] : "");
                            var punktodbioru = point.name;
                            var opis_punktu_z_kodem = opis_punkt + " " + " (" + point.name + ")";
                            ';
                        if ( $koszyk == false ) {

                             $tekst .= 'if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_international" ) {                                     
                                             $("#paczkomatInternational").val(opis_punktu_z_kodem);
                                             $("#ShippingDestinationCodeInternationa").val(punktodbioru);
                                        }
                                        
                                        if ( $(\'input[name="lokalizacjaRuch"]\').attr(\'wysylka\') == "wysylka_inpost_international" ) {
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_international\', koszyk:0, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $(".WybranyPunktMapyPaczkomaty").show(); myModal.close(); });
                                        }';                                                                           
                                        
                        } else {
                          
                             $tekst .= 'if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_international" ) {    
                                             $("#WybranyPaczkomatInternational").html(opis_punktu_z_kodem);
                                        }
                                        
                                        if ( $(\'input[name="rodzaj_wysylki"]:checked\').attr(\'wysylka\') == "wysylka_inpost_international" ) {    
                                             $.post("' . $adres . '?tok=' . Sesje::Token() . '", { rodzaj: \'wysylka_inpost_international\', koszyk:1, value:opis_punktu_z_kodem, punktodbioru:punktodbioru, punktopis:opis_punkt }, function(data) { $("#WybranyPaczkomatInternational").show(); myModal.close(); });
                                        }';                                         

                        }                                

            $tekst .= '
                        }
                        </script>';

        }

        echo $tekst;

    }
        
    // GLS

    if ( isset($_POST['modul']) && $_POST['modul'] == 'gls' && isset($_POST['adres']) && isset($_POST['koszyk']) && isset($_POST['waga']) ) {
    
        $tekst = '';

        $jezyk         = 'PL';
        $kraj          = 'PL';
        $miasto        = '';
        $koszyk        = (($_POST['koszyk'] == 'tak') ? true : false );
        $adres         = $_POST['adres'];
        $waga          = $_POST['waga'];
        if ( isset($_SESSION['adresDostawy']['kod_pocztowy']) ) {
            $miasto = 'center_point: "' . $_SESSION['adresDostawy']['kod_pocztowy'] . '",';
        }

        if ( isset($_SESSION['krajDostawy']['kod']) ) {
            $kraj = $_SESSION['krajDostawy']['kod'];
        }
        if ( $_SESSION['domyslnyJezyk']['kod'] != 'pl' ) {
            $jezyk         = 'EN';
        }

        $tekst = "<script>

            $(document).ready(function() {

                SzybkaPaczkaMap.init({
                    lang: '".$jezyk."',
                    country_parcelshops: '".$kraj."',
                    el: 'WynikMapGls',
                    geolocation: true,
                    map_type: false,
                    parcel_weight: '".$waga."',
                    ".$miasto."
                });

                window.addEventListener('get_parcel_shop',function(e){
                    var ID = e.target.ParcelShop.selected.id;
                    var opis_punkt = e.target.ParcelShop.selected.name;
                    var adres = e.target.ParcelShop.selected.street + ', ' + e.target.ParcelShop.selected.postal_code + ' ' + e.target.ParcelShop.selected.city + ' - ' + e.target.ParcelShop.selected.name + ' ' + ' (' + e.target.ParcelShop.selected.id + ')';";

                    if ( $koszyk == false ) {

                        $tekst .= "
                                  $('#kioskGlsu').val(adres); 
                                  $('#ShippingDestinationCode').val(ID); 
                                  $('#kioskOpis').html(opis_punkt); 
                                  $('#OpisPunktuOdbioru').val(opis_punkt); 

                                  $.post('" . $adres . "?tok=" . Sesje::Token() . "', { rodzaj: 'wysylka_glspickup', koszyk:0, value:adres, punktodbioru:ID, punktopis:opis_punkt }, function() { $('.WybranyPunktMapyGls').show(); myModal.close(); }); ";
                    } else {

                        $tekst .= "

                                   $('#WybranyPunktGls').html(adres);
                                        
                                   $.post('" . $adres . "?tok=" . Sesje::Token() . "', { rodzaj: 'wysylka_glspickup', koszyk:1, value:adres, punktodbioru:ID, punktopis:opis_punkt }, function() {  $('#WybranyPunktGls').show(); myModal.close(); }); ";
                    }

                    $tekst .="
                })

            });

        </script>";
        
        $tekst .= '<div id="WynikMapGls" class="szybkapaczka_map"style="width:100%;height:100%"></div>';        

        echo $tekst;
        
    }    
}
?>