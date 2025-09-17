<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( !isset($_GET['zakladka']) ) {
       $_GET['zakladka'] = '0';
    }

    if ( ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) || ( isset($_POST['id_poz']) && (int)$_POST['id_poz'] > 0 ) ) {

        if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
          $zamowienie = new Zamowienie((int)$_GET['id_poz']);
        } elseif ( isset($_POST['id']) && $_POST['id'] != '' ) {
          $zamowienie = new Zamowienie((int)$_POST['id']);
        }
        
        $i18n = new Translator($db, $zamowienie->klient['jezyk']);
        
    } else {
    
        $_GET['id_poz'] = 0;   
    
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $pola_info = Array();
      $pola_dostawa_punkt = Array();
      $Dostawa = explode('|', (string)$_POST['dostawa']);

      $pola_info = array(
                   array('shipping_module', $Dostawa['1'] ),
                   array('shipping_module_id', $Dostawa['2'] ));

      if ( isset($_POST['lokalizacjaPunktOdbioru']) && $_POST['lokalizacjaPunktOdbioru'] != '' ) {
          $pola_dostawa_punkt = array(
                  array('shipping_info', $filtr->process($_POST['lokalizacjaPunktOdbioru']) ),
                  array('shipping_destinationcode', ((isset($_POST['kodPunktuOdbioru'])) ? $filtr->process($_POST['kodPunktuOdbioru']) : '') ),
              );
      } else {
          $pola_dostawa_punkt = array(
                  array('shipping_info', '' ),
                  array('shipping_destinationcode', '' ),
              );
      }

      $pola = Array();
      $pola = array_merge( $pola_info, $pola_dostawa_punkt );

      $db->update_query('orders', $pola, "orders_id = '" . (int)$_POST["id"] . "'");

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"].'');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <script>
        $(document).ready(function() {
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
        });
    </script>

    <div id="naglowek_cont">Edycja pozycji</div>

    <?php 
    $kodIsoPanstwa = Klienci::pokazISOPanstwa($zamowienie->dostawa['kraj'], $_SESSION['domyslny_jezyk']['id']);
    ?>

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
          $bliskapaczka_klucz = '';
          $orlen_klucz = '';
          //
          foreach ( $tablica_wysylek_wlaczonych as $tmp ) {
            //
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_inpost' ) {
                 $paczkomaty = true;
            }
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_inpost_eko' ) {
                 $paczkomaty = true;
            }            
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_dhlparcelshop' ) {
                 $dhl = true;
            }
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_pocztapunkt' ) {
                 $poczta = true;
            }
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_paczkaruch' ) {
                 $ruch = true;
            }  
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_dpdpickup' ) {
                 $dpd = true;
            }  
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_glspickup' ) {
                 $gls = true;
            }  
            if ( isset($tmp['WYSYLKA_GABARYT']['id']) && $tmp['WYSYLKA_GABARYT']['id'] == 'wysylka_bliskapaczka' ) {
                 $bliskapaczka = true;
            }  
            if ( isset($tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']['id']) && $tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']['id'] == 'wysylka_bliskapaczka' ) {
                 $bliskapaczka_klucz = $tmp['WYSYLKA_KLUCZ_API_BLISKAPACZKA']['value'];
            }                
            if ( isset($tmp['WYSYLKA_API_KEY_ORLEN']['id']) && $tmp['WYSYLKA_API_KEY_ORLEN']['id'] == 'wysylka_paczkaruch' ) {
                 $orlen_klucz = $tmp['WYSYLKA_API_KEY_ORLEN']['value'];
            }                
            //
          }
          //
          unset($tablica_wysylek_wlaczonych);

          $zapytanie = "SELECT 
                        * 
                        FROM orders
                        WHERE orders_id = '" . (int)$_GET['id_poz']. "'";
                        
          $sql = $db->open_query($zapytanie);
            
          if ((int)$db->ile_rekordow($sql) > 0) {
              $info = $sql->fetch_assoc();
              ?>
              <form action="sprzedaz/zamowienia_wysylka_edytuj.php" method="post" id="zamowieniaForm" class="cmxform">          

                <div class="poleForm">
                
                  <div class="naglowek">Edycja zamówienia numer : <?php echo $_GET['id_poz']; ?></div>
                  
                      <div class="pozycja_edytowana">
                          
                          <div class="info_content">
                      
                          <input type="hidden" name="akcja" value="zapisz" />
                      
                          <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                          <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                          <p>
                              <label>Wybrana forma dostawy:</label>
                              <span class="BiezacaWysylka">
                                  <?php
                                  echo $info['shipping_module'];
                                  ?>
                              </span>
                          </p>
                          <?php if ( $info['shipping_info'] != '' ) { ?>
                            <p>
                              <label>Wybrana punkt odbioru:</label>
                                <span class="BiezacaWysylka">
                                <?php echo $info['shipping_info']; ?>
                                </span>
                            </p>
                          <?php } ?>
                          <?php if ( $info['shipping_destinationcode'] != '' ) { ?>
                            <p>
                              <label>Kod punktu odbioru:</label>
                                <span class="BiezacaWysylka">
                                <?php echo $info['shipping_destinationcode']; ?>
                                </span>
                            </p>

                            <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                          <?php } ?>

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

            <style>
                .orlen-widget.orlen-widget-button.przyciskPaczkomatu { border:1px solid #ccc; font-size:100%; font-family:Arial, Tahoma, sans-serif; padding:7px 5px 7px 5px; box-shadow:0 0 10px #eee inset; border-radius:4px; margin:0 10px 0 0; width:120px; }
            </style>           

            <script>
                (function (o, r, l, e, n) {
                    o[l] = o[l] || [];
                    var f = r.getElementsByTagName('head')[0];
                    var j = r.createElement('script');
                    j.async = true;
                    j.src = e + 'widget.js?token=' + n + '&v=1.0.0&t=' + Math.floor(new Date().getTime() / 1000);
                    f.insertBefore(j, f.firstChild);
                })(window, document, 'orlenpaczka', 'https://mapa.orlenpaczka.pl/', '<?php echo $orlen_klucz; ?>');

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
                var address = "<?php echo $zamowienie->dostawa['miasto']; ?>";

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

            <?php
            if ( $zamowienie->waga_produktow == 0 || $zamowienie->waga_produktow < 1 ) {
                $zamowienie->waga_produktow = 1;
            }
            $dane = '{
                "parcel":{
                    "dimensions":{
                        "length":10,
                        "width":10,
                        "height":10,
                        "weight":'.$zamowienie->waga_produktow.'
                    },
                    "insuranceValue":'.$zamowienie->info['wartosc_zamowienia_val'].'
                },
                "deliveryType":"P2P"
            }';

            $url = 'https://api.bliskapaczka.pl/v2/pricing' ;
            //$url = 'https://api.sandbox-bliskapaczka.pl/v2/pricing';
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $bliskapaczka_klucz
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dane);    

            $content = curl_exec($ch);

            $Operatorzy = false;

            if ( is_string($content) && is_array(json_decode($content, true)) && (json_last_error() == JSON_ERROR_NONE) ) {

                if ( $content === false ) {
                    $Operatorzy = false;
                } else {
                    $Operatorzy = json_decode($content);
                }
            
            }

            $OperatorzyTablica = '';

            if ($Operatorzy !== false) {
                $OperatorzyTablica = '[';
                foreach ( $Operatorzy as $Operator ) {
                    if ( isset($Operator->price->gross) ) {
                        $OperatorzyTablica .= "{operator: '".$Operator->operatorName."', price: ".( $koszt_wysylki > 0 ? $Operator->price->gross : 0 ) ."},";
                    }
                    unset($Operator);
                }
                $OperatorzyTablica = substr((string)$OperatorzyTablica, 0, -1);
                $OperatorzyTablica .= ']';
            }
            
            curl_close($ch);
            ?>
        
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


            <script type="text/javascript" src="https://widget.bliskapaczka.pl/v8.1/main.js"></script>
            <link rel="stylesheet" href="https://widget.bliskapaczka.pl/v8.1/main.css" />
            
            <script>
            
            $(window).load(function() {

                $("body").append("<div class='MapyUkryteWysylkaBliskaPaczka' id='WybierzMapyWysylkaBliskaPaczka'><div id='MapyWidoczneWysylkaTloBliskaPaczka'></div><div id='WyborMapaWysylkaBliskaPaczka'><div id='MapaKontenerWysylkaBliskaPaczka'><div id='MapaZamknijWysylkaBliskaPaczka'>X</div><div id='WidokMapWysylkaBliskaPaczka'></div></div></div></div>");

                $("#WidgetButtonBliskaPaczka").click(function() {
                   $("#WybierzMapyWysylkaBliskaPaczka").removeClass("MapyUkryteWysylkaBliskaPaczka").addClass("MapyWidoczneWysylkaBliskaPaczka");
                   
                });

                $("#MapaZamknijWysylkaBliskaPaczka").click(function() {
                   $("#WybierzMapyWysylkaBliskaPaczka").removeClass("MapyWidoczneWysylkaBliskaPaczka").addClass("MapyUkryteWysylkaBliskaPaczka");
                });

                var OperatorsArray = "<?php echo $OperatorzyTablica; ?>";

                BPWidget.init(
                    document.getElementById('WidokMapWysylkaBliskaPaczka'),
                    {
                        callback: function(point) {
                            var value = point.operator + ' - ' + ( point.description != null ? point.description  + '; ' : '') + point.street + ', ' + point.postalCode + ' ' + point.city;
                            var punktodbioru = point.code;
                            $("#BliskaPaczkaOpis").val(value);
                            $("#BliskaPaczkaID").val(point.code);
                            $("#WybierzMapyWysylkaBliskaPaczka").removeClass("MapyWidoczneWysylkaBliskaPaczka").addClass("MapyUkryteWysylkaBliskaPaczka");

                        },
                        posType: 'DELIVERY',
                        codOnly: false,
                        operators: OperatorsArray,
                        initialAddress: '<?php echo $zamowienie->dostawa['miasto']; ?>'

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
                    country_parcelshops: '<?php echo $kodIsoPanstwa; ?>',
                    el: 'map_gls',
                    geolocation: false,
                    map_type: false,
                    parcel_weight: '<?php echo ( $zamowienie->waga_produktow > 0 ? $zamowienie->waga_produktow : '5'); ?>'
                });

                window.addEventListener('get_parcel_shop',function(e){
                    $('#GlsOpis').val(e.target.ParcelShop.selected.street + ', ' + e.target.ParcelShop.selected.postal_code + ' ' + e.target.ParcelShop.selected.city + ', ' + e.target.ParcelShop.selected.name);
                    $('#GlsID').val(e.target.ParcelShop.selected.id);
                    $("#WybierzMapyWysylkaGls").removeClass("MapyWidoczneWysylkaGls").addClass("MapyUkryteWysylkaGls");
                })

            });
            
            </script>     

        <?php } ?>
                          <p>
                            <label for="selection">Nowa forma dostawy:</label>
                            <?php

                            $tablica_wysylek = Array();
                            $zapytanies = "SELECT *
                                          FROM modules_shipping
                                          WHERE status = '1' ORDER BY sortowanie";
                         
                            $sqls = $db->open_query($zapytanies);   
                            while ($wynik = $sqls->fetch_assoc()) {
                                $tablica_wysylek[] = array('id'   => $wynik['klasa'].'|'.$wynik['nazwa'].'|'.$wynik['id'],
                                                           'text' => $wynik['nazwa']);
                            }
                            $db->close_query($sqls); 
                            unset($zapytanies);      

                            echo Funkcje::RozwijaneMenu('dostawa', $tablica_wysylek , '','style="width: 344px;" id="selection"');
                            unset($tablica_wysylek);
                            ?>
                          </p>

                          <p id="lokalizacje" style="display:none;">
                            <label for="sel3">Lokalizacja:</label>
                            <?php
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButtonInpost" value="Wybierz paczkomat" readonly="readonly" style="display:none;" />';
                            echo '<a class="orlen-widget orlen-widget-button przyciskPaczkomatu" id="WidgetButtonRuch" style="display:none;" data-modal="true" data-target="#PunktRuchID" data-label="#PunktRuchOpis" data-layout="tabs"/>Wybierz punkt</a>';
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


                          </div>
                       
                      </div>

                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" />
                        <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('x','y','jezyk')); ?>','sprzedaz');">Powrót</button>           
                      </div>

                  </div>   
                  
              </form>

          <?php

          unset($info);            

        } else {

          ?>
          
          <div class="poleForm"><div class="naglowek">Edycja wysyłki w zamówieniu</div>
              <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
          </div>
          
          <?php

        }
        
        $db->close_query($sql);

          ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}

?>
