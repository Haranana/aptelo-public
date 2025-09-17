<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'GLS';

    if ( !isset($_POST['akcja']) ) {
        $apiKurier = new GlsApi((int)$_GET['id_poz']);
    }
    $weight_total = 0;
    $parcel = array();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $apiKurier = new GlsApi((int)$_POST['id']);

        $WagaCalkowita = array_sum($_POST['parcel']['waga']);
        $IloscPaczek = ( isset($_POST['parcel']['waga']) ? count($_POST['parcel']['waga']) : '0' );

        $packageDetails = new stdClass();

        $packageDetails->consign_prep_data = new stdClass();
        $packageDetails->consign_prep_data->rname1 = $_POST['rname1'];
        $packageDetails->consign_prep_data->rname2 = $_POST['rname2'];
        $packageDetails->consign_prep_data->rname3 = '';

        $packageDetails->consign_prep_data->rcountry = $_POST['rcountry'];
        $packageDetails->consign_prep_data->rzipcode = $_POST['rzipcode'];
        $packageDetails->consign_prep_data->rcity = $_POST['rcity'];
        $packageDetails->consign_prep_data->rstreet = $_POST['rstreet'];

        $packageDetails->consign_prep_data->rphone = $_POST['rphone'];
        $packageDetails->consign_prep_data->rcontact = $_POST['rcontact'];
           
        $packageDetails->consign_prep_data->references = $_POST['references'];
        $packageDetails->consign_prep_data->notes = $_POST['notes'];   
        $packageDetails->consign_prep_data->weight = $WagaCalkowita;
        $packageDetails->consign_prep_data->quantity = $IloscPaczek; // overwrited by ParcelsArray

        if ( isset($_POST['WysylkaPickup']) && $_POST['punkt_preferowany'] != '' ) {
            $packageDetails->consign_prep_data->srv_sds = new stdClass();
            $packageDetails->consign_prep_data->srv_sds->id = $_POST['punkt_preferowany'];
        }
        
        $packageDetails->consign_prep_data->sendaddr = new stdClass();
        $packageDetails->consign_prep_data->sendaddr->name1 = INTEGRACJA_GLS_SENDERADDRESS_NAME1;
        $packageDetails->consign_prep_data->sendaddr->name2 = INTEGRACJA_GLS_SENDERADDRESS_NAME2;
        $packageDetails->consign_prep_data->sendaddr->name3 = INTEGRACJA_GLS_SENDERADDRESS_NAME3;   
        $packageDetails->consign_prep_data->sendaddr->country = 'PL';
        $packageDetails->consign_prep_data->sendaddr->zipcode = INTEGRACJA_GLS_SENDERADDRESS_ZIPCODE;
        $packageDetails->consign_prep_data->sendaddr->city = INTEGRACJA_GLS_SENDERADDRESS_CITY;
        $packageDetails->consign_prep_data->sendaddr->street= INTEGRACJA_GLS_SENDERADDRESS_STREET;

        $packageDetails->consign_prep_data->srv_bool = new stdClass();
        if ( isset($_POST['cod']) ) {
            $packageDetails->consign_prep_data->srv_bool->cod = 1;
            $packageDetails->consign_prep_data->srv_bool->cod_amount = $_POST['cod_amount'];
        }
        if ( isset($_POST['exw']) ) {
            $packageDetails->consign_prep_data->srv_bool->exw = 1;
        }
        if ( isset($_POST['ow']) ) {
            $packageDetails->consign_prep_data->srv_bool->ow = 1;
        }
        if ( isset($_POST['rod']) ) {
            $packageDetails->consign_prep_data->srv_bool->rod = 1;
        }
        if ( isset($_POST['WysylkaPickup']) && $_POST['punkt_preferowany'] != '' ) {
            $packageDetails->consign_prep_data->srv_bool->sds = 1;
        }


        if ( isset($_POST['parcel']['waga']) && count($_POST['parcel']['waga']) > 1 ) {

            $packageDetails->consign_prep_data->parcels = new stdClass();
            
            for ( $i = 0, $c = count($_POST['parcel']['waga']); $i < $c; $i++ ) {

                $oParcel = new stdClass();
                $oParcel->reference = $_POST['parcel']['ref'][$i];
                $oParcel->weight = $_POST['parcel']['waga'][$i];   
                $packageDetails->consign_prep_data->parcels->items[] = $oParcel;

            }
        }

        $wynik = array();
        $wynik = $apiKurier->doAdePreparingBox_Insert( $packageDetails ); 

        if ( is_integer($wynik) ) {

            $pola = array(
                    array('orders_id',$filtr->process($_POST["id"])),
                    array('orders_shipping_type',$api),
                    array('orders_shipping_number',$wynik),
                    array('orders_shipping_weight',$WagaCalkowita),
                    array('orders_parcels_quantity',$IloscPaczek),
                    array('orders_shipping_status','1'),
                    array('orders_shipping_date_created', 'now()'),
                    array('orders_shipping_date_modified', 'now()'),
                    array('orders_shipping_comments', $wynik),
                    array('orders_shipping_packages', '')
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);

                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

        } else {
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

      <div class="poleForm">
        <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

        <div class="pozycja_edytowana">  

            <?php
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;
            $wymiary        = array();
            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);

            ?>

            <script src="https://mapa.gls-poland.com/js/v4.0/maps_sdk.js"></script>
            
            <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        

            <script>
            $(document).ready(function() {

              if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
              $("#addrow").click(function() {
                
                var id = $(".UsunPozycjeListy").length;

                $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="80" name="parcel[ref][]" /></td><td class="Paczka"><input type="text" value="" size="4" name="parcel[waga][]" class="kropkaPusta required" /></td></tr>');

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
                  quantity     : { digits: true }
                }
              });

              $('#ubezpieczenie').change(function() {
                  $("#ubezpieczenie_wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#pobranie').change(function() {
                  (($(this).is(':checked')) ? $("#gls_pobranie").prop('disabled', false) : $("#gls_pobranie").prop('disabled', true));
                  $("#gls_pobranie").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('input.datepicker').Zebra_DatePicker({
                format: 'Y-m-d',
                inside: false,
                readonly_element: false
              });             

              $('#WysylkaPickup').click(function() {

                    if ($(this).is(':checked')) {
                        $("#PunktWybor").slideDown();
                    } else {
                        $("#PunktWybor").slideUp();
                        $("#PunktWybrany").val('');
                        $("#punkt_preferowany").val('');
                    }
                });

            });
            </script>

            <script>    
            
            $(window).load(function() {

                $("body").append("<div class='MapyUkryteWysylkaGls' id='WybierzMapyWysylkaGls'><div id='MapyWidoczneWysylkaTloGls'></div><div id='WyborMapaWysylkaGls'><div id='MapaKontenerWysylkaGls'><div id='MapaZamknijWysylkaGls'>X</div><div id='WidokMapWysylkaGls'><div id='MapaZawartoscGls'><div id='map_gls' class='map' style='display:flex;width:100%;height:600px;'></div></div></div></div></div></div>");

                $("#WidgetButton").click(function() {
                   $("#WybierzMapyWysylkaGls").removeClass("MapyUkryteWysylkaGls").addClass("MapyWidoczneWysylkaGls");
                   
                });
                $("#MapaZamknijWysylkaGls").click(function() {
                   $("#WybierzMapyWysylkaGls").removeClass("MapyWidoczneWysylkaGls").addClass("MapyUkryteWysylkaGls");
                });   
            
                SzybkaPaczkaMap.init({
                    lang: 'PL',
                    country_parcelshops: '<?php echo $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); ?>',
                    el: 'map_gls',
                    geolocation: false,
                    map_type: false,
                    parcel_weight: '<?php echo ( $zamowienie->waga_produktow > 0 ? $zamowienie->waga_produktow : '5'); ?>',
                    center_point: '<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>'
                });

                window.addEventListener('get_parcel_shop',function(e){
                    $('#PunktWybrany').val(e.target.ParcelShop.selected.street + ', ' + e.target.ParcelShop.selected.postal_code + ' ' + e.target.ParcelShop.selected.city + ', ' + e.target.ParcelShop.selected.name);
                    $('#punkt_preferowany').val(e.target.ParcelShop.selected.id);
                    $("#WybierzMapyWysylkaGls").removeClass("MapyWidoczneWysylkaGls").addClass("MapyUkryteWysylkaGls");
                })

            });
            
            </script>     

            <form action="sprzedaz/zamowienia_wysylka_gls.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

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
                            <label for="gls_refer">Referencje:</label>
                            <input type="text" size="70" name="references" id="gls_refer" value="<?php echo ( isset($_POST['references']) ? $_POST['references'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?>" /><em class="TipIkona"><b>Referencje (pole to jest drukowane na etykietach, zazwyczaj podaje się w tym polu skrócony opis zawartości paczki, nr zamównienia etc.)</b></em>
                        </p> 

                        <p>
                            <label for="gls_uwagi">Uwagi [max. 40 znaków]:</label>
                            <textarea cols="45" rows="2" name="notes" id="gls_uwagi" onkeyup="licznik_znakow(this,'iloscZnakow',40)" ><?php echo ( isset($_POST['notes']) ? $_POST['notes'] : '' ); ?></textarea><em class="TipIkona"><b>Uwagi</b></em>
                        </p> 

                        <p>
                            <label></label>
                            <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow">40</span></span>
                        </p>

                        <p>
                            <label for="gls_date">Data nadania:</label>
                            <input type="text" name="date" id="gls_date" value="" size="20" class="datepicker" /><em class="TipIkona"><b>Data nadania, jeśli brak zostanie wstawiona aktualna data</b></em>
                        </p>


                        <p>
                            <label for="pobranie">COD:</label>
                            <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                <input type="checkbox" value="1" name="cod" id="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['cod']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="cod_amount" id="gls_pobranie" value="<?php echo ( isset($_POST['cod_amount']) ? $_POST['cod_amount'] : '' ); ?>" disabled="disabled" /><em class="TipIkona"><b>(Cash-Service) - Pobranie za towar, po tym symbolu może się pojawić kwota pobrania</b></em>
                            <?php } else { ?>
                                <input type="checkbox" value="1" name="cod" id="pobranie" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="cod_amount" id="gls_pobranie" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" /><em class="TipIkona"><b>(Cash-Service) - Pobranie za towar, po tym symbolu może się pojawić kwota pobrania</b></em>

                            <?php } ?>

                        </p> 

                        <p>
                            <label for="exw">EXW:</label>
                            <input type="checkbox" value="1" name="exw" id="exw" <?php echo ( isset($_POST['exw']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="exw" ></label><em class="TipIkona"><b>(ExWorks-Service) - Płaci odbiorca</b></em>
                        </p>

                        <p>
                            <label for="ow">OW:</label>
                            <input type="checkbox" value="1" name="ow" id="ow" <?php echo ( isset($_POST['ow']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="ow" ></label><em class="TipIkona"><b>(Odbiór własny) - Odbiór paczki w filii GLS Poland</b></em>
                        </p>

                        <p>
                            <label for="rod">ROD:</label>
                            <input type="checkbox" value="1" name="rod" id="rod" <?php echo ( isset($_POST['rod']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="rod" ></label><em class="TipIkona"><b>(DocumentReturn-Servic) - Zwrot dokumentów</b></em>
                        </p>

                        <div id="WysylkaDoPunktu">
                            <p>
                                <label for="WysylkaPickup">Do punktu:</label>
                                <input id="WysylkaPickup" type="checkbox" name="WysylkaPickup" <?php echo ( stripos((string)$zamowienie->info['wysylka_modul'], 'GLS') !== false && $zamowienie->info['wysylka_punkt_odbioru'] != '' ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="WysylkaPickup"></label><em class="TipIkona"><b>Odbiór paczki w dowolnym punkcie ParcelShop</b></em>
                            </p>

                            <p id="PunktWybor" <?php echo ( stripos((string)$zamowienie->info['wysylka_modul'], 'GLS') !== false && $zamowienie->info['wysylka_punkt_odbioru'] != '' ? '' : 'style="display:none;"' ); ?>>
                                <label for="WysylkaPickup">ParcelShop ID:</label>
                                <input type="text" class="przyciskPaczkomatu" id="WidgetButton" value="Wybierz punkt" readonly="readonly" />
                                <input type="text" size="30" id="PunktWybrany" value="<?php echo ( stripos((string)$zamowienie->info['wysylka_modul'], 'GLS') !== false && $zamowienie->info['wysylka_punkt_odbioru'] != '' ? $zamowienie->info['wysylka_info'] : '' ); ?>" name="lokalizacjaPunkt" readonly="readonly" id="wybor_punktu" />
                                <input type="text" id="punkt_preferowany" value="<?php echo ( stripos((string)$zamowienie->info['wysylka_modul'], 'GLS') !== false && $zamowienie->info['wysylka_punkt_odbioru'] != '' ? $zamowienie->info['wysylka_punkt_odbioru'] : '' ); ?>" name="punkt_preferowany" readonly="readonly"  style="margin-left:10px;" size="15" />
                            </p>

                        </div>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Informacje o paczkach</div>

                        <table class="listing_tbl">
                          <tr>
                            <td style="width:50px"></td>
                            <td class="Paczka">Referencja</td>
                            <td class="Paczka">Waga [kg]</td>
                          </tr>

                          <tr class="item-row">
                            <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['ref']['0']) ? $_POST['parcel']['ref']['0'] : '' ); ?>" size="80" name="parcel[ref][]" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : $waga_produktow ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                          </tr>

                          <?php
                          if ( isset($_POST['parcel']['dlugosc']) && count($_POST['parcel']['dlugosc']) > 1 ) {
                            for ( $i = 1, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
                              ?>
                              <tr class="item-row">
                                <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['ref'][$i]) ? $_POST['parcel']['ref'][$i] : '' ); ?>" size="80" name="parcel[ref][]" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga'][$i]) ? $_POST['parcel']['waga'][$i] : '' ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
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
                            <label for="adresat_Company">Pierwsza część nazwy odbiorcy:</label>
                            <input type="text" size="40" name="rname1" id="adresat_Company" value="<?php echo ( $zamowienie->dostawa['firma'] == '' ? preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']) : Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) ); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Name">Druga część nazwy odbiorcy:</label>
                            <input type="text" size="40" name="rname2" id="adresat_Name" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']) : '' ); ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Address">Ulica:</label>
                            <input type="text" size="40" name="rstreet" id="adresat_Address" value="<?php echo $zamowienie->dostawa['ulica']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_PostalCode">Kod pocztowy:</label>
                            <input type="text" size="40" name="rzipcode" id="adresat_PostalCode" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_City">Miejscowość:</label>
                            <input type="text" size="40" name="rcity" id="adresat_City" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                        </p> 

                        <p>
                            <label for="adresat_CountryCode">Kraj:</label>
                            <?php 
                            $domyslnie = $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); 
                            $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                            echo Funkcje::RozwijaneMenu('rcountry', $tablicaPanstw, $domyslnie, 'id="adresat_CountryCode" class="klient" style="width:230px;"' ); 

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

                            <input type="text" size="40" name="rphone" id="adresat_Phone" value="<?php echo $NumerTelefonu; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_Email">Adres e-mail:</label>
                            <input type="text" size="40" name="rcontact" id="adresat_Email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
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
