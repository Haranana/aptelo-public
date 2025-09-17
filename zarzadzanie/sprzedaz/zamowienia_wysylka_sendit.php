<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'SendIt';
    $apiKurier = new SenditApi();


    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $iloscPaczek = (int)$apiKurier->getVar('kPK') + (int)$apiKurier->getVar('nstd_kPK') + (int)$apiKurier->getVar('kP5') + (int)$apiKurier->getVar('nstd_kP5') + (int)$apiKurier->getVar('kP10') + (int)$apiKurier->getVar('nstd_kP10') + (int)$apiKurier->getVar('kP20') + (int)$apiKurier->getVar('nstd_kP20') + (int)$apiKurier->getVar('kP30') + (int)$apiKurier->getVar('nstd_kP30') + (int)$apiKurier->getVar('kP50') + (int)$apiKurier->getVar('nstd_kP50') + (int)$apiKurier->getVar('kP70') + (int)$apiKurier->getVar('nstd_kP70');


        $orderData = array(
        'senderCountryCode'         => 'PL',
        'senderEmail'               => $apiKurier->getVar('sender_email'),
        'senderName'                => $apiKurier->getVar('sender_name'),
        'senderStreet'              => $apiKurier->getVar('sender_street'),
        'senderCity'                => $apiKurier->getVar('sender_city'),
        'senderPhoneNumber'         => $apiKurier->getVar('sender_phone'),
        'senderZipCode'             => $apiKurier->getVar('sender_postcode'),
        'senderContactPerson'       => $apiKurier->getVar('sender_contact'),
        'receiverCountryCode'       => $apiKurier->getVar('receiver_country'),
        'receiverEmail'             => $apiKurier->getVar('receiver_email'),
        'receiverName'              => $apiKurier->getVar('receiver_name'),
        'receiverStreet'            => $apiKurier->getVar('receiver_street'),
        'receiverCity'              => $apiKurier->getVar('receiver_city'),
        'receiverPhoneNumber'       => $apiKurier->getVar('receiver_phone'),
        'receiverZipCode'           => $apiKurier->getVar('receiver_postcode'),
        'receiverContactPerson'     => $apiKurier->getVar('receiver_contact'),
        'kPK'                       => ((int)$apiKurier->getVar('kPK') + (int)$apiKurier->getVar('nstd_kPK')),
        'kP5'                       => ((int)$apiKurier->getVar('kP5') + (int)$apiKurier->getVar('nstd_kP5')),
        'kP10'                      => ((int)$apiKurier->getVar('kP10') + (int)$apiKurier->getVar('nstd_kP10')),
        'kP20'                      => ((int)$apiKurier->getVar('kP20') + (int)$apiKurier->getVar('nstd_kP20')),
        'kP30'                      => ((int)$apiKurier->getVar('kP30') + (int)$apiKurier->getVar('nstd_kP30')),
        'kP50'                      => ((int)$apiKurier->getVar('kP50') + (int)$apiKurier->getVar('nstd_kP50')),
        'kP70'                      => ((int)$apiKurier->getVar('kP70') + (int)$apiKurier->getVar('nstd_kP70')),
        'kPal'                      => ( ((int)$apiKurier->getVar('palletWeight') > 0 || (int)$apiKurier->getVar('palletHeight') > 0 )?'1':'0' ),
        'palletHeight'              => (int)$apiKurier->getVar('palletHeight'),
        'palletWeight'              => (int)$apiKurier->getVar('palletWeight'),
        'COD'                       => (float)str_replace(' ','',str_replace(',', '.', (string)$apiKurier->getVar('cod_value'))),
        'INS'                       => (float)str_replace(' ','',str_replace(',', '.', (string)$apiKurier->getVar('ins_value'))),
        'ROD'                       => ($apiKurier->getVar('ROD')?'1':'0'),
        'SRE'                       => ($apiKurier->getVar('SRE')?'1':'0'),
        'SSE'                       => ($apiKurier->getVar('SSE')?'1':'0'),
        'BYH'                       => ($apiKurier->getVar('BYH')?'1':'0'),
        'H24'                       => ($apiKurier->getVar('H24')?'1':'0'),
        'deliveryTime'              => $apiKurier->getVar('terms'),
        'alerts'                    => array(
                                        'receive' => array(
                                        'sender' => array( 'sms' => ($apiKurier->getVar('ReceiveSenderSMS')?'1':'0'), 'email' => ($apiKurier->getVar('ReceiveSenderEmail')?'1':'0')),
                                        'receiver' => array( 'sms' => ($apiKurier->getVar('ReceiveReceiverSMS')?'1':'0'), 'email' => ($apiKurier->getVar('ReceiveReceiverEmail')?'1':'0')),
                                        ),
        'courier' => array(
                                        'sender' => array( 'sms' => ($apiKurier->getVar('CourierSenderSMS')?'1':'0'), 'email' => ($apiKurier->getVar('CourierSenderEmail')?'1':'0')),
                                        'receiver' => array( 'sms' => ($apiKurier->getVar('CourierReceiverSMS')?'1':'0'), 'email' => ($apiKurier->getVar('CourierReceiverEmail')?'1':'0')),
                                        ),
        'advice' => array(
                                        'sender' => array( 'sms' => ($apiKurier->getVar('AwizoSenderSMS')?'1':'0'), 'email' => ($apiKurier->getVar('AwizoSenderEmail')?'1':'0')),
                                        'receiver' => array( 'sms' => ($apiKurier->getVar('AwizoReceiverSMS')?'1':'0'), 'email' => ($apiKurier->getVar('AwizoReceiverEmail')?'1':'0')),
                                        ),
        'deliver' => array(
                                        'sender' => array( 'sms' => ($apiKurier->getVar('DeliverSenderSMS')?'1':'0'), 'email' => ($apiKurier->getVar('DeliverSenderEmail')?'1':'0')),
                                        'receiver' => array( 'sms' => ($apiKurier->getVar('DeliverReceiverSMS')?'1':'0'), 'email' => ($apiKurier->getVar('DeliverReceiverEmail')?'1':'0')),
                                        ),
        'refuse' => array(
                                        'sender' => array( 'sms' => ($apiKurier->getVar('RefuseSenderSMS')?'1':'0'), 'email' => ($apiKurier->getVar('RefuseSenderEmail')?'1':'0')),
                                        'receiver' => array( 'sms' => ($apiKurier->getVar('RefuseReceiverSMS')?'1':'0'), 'email' => ($apiKurier->getVar('RefuseReceiverEmail')?'1':'0')),
                                        ),
        ),
        'NSTData'                   => array(
        'kPK'                       => (int)$apiKurier->getVar('nstd_kPK'),
        'kP5'                       => (int)$apiKurier->getVar('nstd_kP5'),
        'kP10'                      => (int)$apiKurier->getVar('nstd_kP10'),
        'kP20'                      => (int)$apiKurier->getVar('nstd_kP20'),
        'kP30'                      => (int)$apiKurier->getVar('nstd_kP30'),
        'kP50'                      => (int)$apiKurier->getVar('nstd_kP50'),
        'kP70'                      => (int)$apiKurier->getVar('nstd_kP70'),
        ),
        'comment'                   => $apiKurier->getVar('saleDocId'),
        'content'                   => $apiKurier->getVar('packageContent'),
        'invoiceFlag'               => 1,
        'protocolFlag'              => ($apiKurier->getVar('protocol')?'1':'0'),
        );

        $data_wyslania = date("Y-m-d H:i:s");

        if ( isset($_POST['sendit']['paymentType']) && $_POST['sendit']['paymentType'] != 'pre' ) {

            $result = $apiKurier->OrderConfirm($orderData, $_POST['courier']);

            if( count($result) > 0 && !isset($result['faultstring'])) {


                $result2 = $apiKurier->OrderGet($result['orderNumbers'][0]);
                $brutto = '';
                $status = 'Zlecenie wysłane do Sendit.pl';
                $courier  = $apiKurier->getVar('courier');
                if($result2['status'] == 'success' ) {
                    $courier = strtoupper((string)$result2['order']['courierName']);
                    $status = $result2['history']['0']['statusNumber'];
                }

                $pola = array(
                        array('orders_id',$filtr->process($_POST["id"])),
                        array('orders_shipping_type',$api .' - ' . $courier),
                        array('orders_shipping_number',$result['orderNumbers'][0]),
                        array('orders_shipping_weight',''),
                        array('orders_parcels_quantity',$iloscPaczek),
                        array('orders_shipping_status',$status),
                        array('orders_shipping_date_created', $data_wyslania),
                        array('orders_shipping_date_modified', $result2['history']['0']['date']),
                        array('orders_shipping_comments', ''),
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);
            }

        } else {
            $result = $apiKurier->OrderSave($orderData, $_POST['courier']);

            $pola = array(
                    array('orders_id',$filtr->process($_POST["id"])),
                    array('orders_shipping_type',$api .' - ' . $result['pricing']['0']['operator']),
                    array('orders_shipping_number',$result['orderNumber']),
                    array('orders_shipping_weight',''),
                    array('orders_parcels_quantity',$iloscPaczek),
                    array('orders_shipping_status',$result['pricing']['0']['result']['status']),
                    array('orders_shipping_date_created', $data_wyslania),
                    array('orders_shipping_date_modified', $data_wyslania),
                    array('orders_shipping_comments', ''),
            );

            $db->insert_query('orders_shipping' , $pola);
            unset($pola);
        }

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
 

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

          <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        
          <script type="text/javascript" src="javascript/paczka.js"></script>

          <script>
          $(document).ready(function() {
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#apiForm").validate({
              rules: {
                waga         : { digits: true },
                typ_wysylki  : { required: true, valueNotEquals: "0" },
                daty         : { required: true, valueNotEquals: "0" },
                godzina_od   : { required: true, valueNotEquals: "0" },
                godzina_do : { required: true, valueNotEquals: "0" }
              }
            });
          });

          $rate = false;

          $(document).ready(function() {

            $('input.pallet').keyup(function (){
                var val = parseInt($(this).val());
                if( isNaN(val))
                    val = 0;
                $(this).val(val);

                $( 'input.parcel').val(0);
                });
            $('input.parcel').keyup(function (){
                var val = parseInt($(this).val());
                if( isNaN(val))
                    val = 0;
                $(this).val(val);
                $('input.pallet').val(0);
            });

            $('input.pallet, input.parcel, #sender_postcode, #receiver_country, #receiver_postcode').change(function (){
                $('#services').html('');
                $('#rate').attr('disabled','disabled');
                $('div#summary').html('');
                if ( $rate )
                    $rate.abort();
            });
            $('#receiver_country').change(function (){
                if($(this).val() != "PL")
                {
                    $('input.sms').attr('disabled','disabled').removeAttr('checked');
                    $('input#palletWeight, input#palletHeight').val(0).attr('disabled','disabled');
                }
                else
                {
                    $('input.sms').removeAttr('disabled');
                    $('input#palletWeight, input#palletHeight').removeAttr('disabled');
                }
            });

            $('#checkService').click(function (){
                $('#services').html('<div id="loader"></div>');
                $('div#summary').html('');
                if ( $rate )
                    $rate.abort();
                var pallet = 0;
                if( $('#palletWeight').val() > 0 || $('#palletHeight').val() > 0)
                    pallet = 1;
                $.ajax(
                    {
                        url: "ajax/sendit.php",
                        type: "POST",
                        data: {
                            action: 'checkService',
                            sender_postcode: $('#sender_postcode').val(),
                            receiver_postcode: $('#receiver_postcode').val(),
                            receiver_country: $('#receiver_country').val(),
                            pallet: pallet
                        },
                        success: function( data )
                        {
                            $('#services').html(data);
                            $('#rate').removeAttr('disabled');

                            $('#COD').change(function() {
                                $('span#cod_value input').val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                                if( $(this).attr('checked') == "checked")
                                    $('#cod_value').show();
                                else
                                    $('#cod_value').hide();
                            });
                            $('#INS').change(function() {
                                $('span#ins_value input').val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                                if( $(this).attr('checked') == "checked")
                                    $('#ins_value').show();
                                else
                                    $('#ins_value').hide();
                            });
                            $('input.parcel, input.pallet, input.notify, input.terms, input.services').change(function() {
                                $('div#summary').html('');
                                if ( $rate )
                                    $rate.abort();
                            });
                        }
                    });
            });

            $('#rate').click(function (){

                var pobranie;
                var ubezpieczenie;

                $('div#summary').html('<div id="loader"></div>');
                if ($('#COD').is(':checked')) {
                    pobranie = $('span#cod_value input').val();
                } else {
                    pobranie = 0;
                    $('span#cod_value input').val('');
                }

                if ($('#INS').is(':checked')) {
                    ubezpieczenie = $('span#ins_value input').val();
                } else {
                    ubezpieczenie = 0;
                    $('span#ins_value input').val('');
                }

                if ( $rate )
                $rate.abort();
                $rate = $.ajax(
                    {
                        url: "ajax/sendit.php",
                        type: "POST",
                        data: {
                            action: 'rate',

                            sender_postcode: $('#sender_postcode').val(),
                            senderEmail: $('#sender_email').val(),
                            senderName: $('#sender_name').val(),
                            senderStreet: $('#sender_street').val(),
                            senderCity: $('#sender_city').val(),
                            senderPhoneNumber: $('#sender_phone').val(),
                            senderContactPerson: $('#sender_contact').val(),

                            receiver_postcode: $('#receiver_postcode').val(),
                            receiver_country: $('#receiver_country').val(),
                            receiverEmail: $('#receiver_email').val(),
                            receiverName: $('#receiver_name').val(),
                            receiverStreet: $('#receiver_street').val(),
                            receiverCity: $('#receiver_city').val(),
                            receiverPhoneNumber: $('#receiver_phone').val(),
                            receiverContactPerson: $('#receiver_contact').val(),

                            kPK: $('#kPK').val(),
                            kP5: $('#kP5').val(),
                            kP10: $('#kP10').val(),
                            kP20: $('#kP20').val(),
                            kP30: $('#kP30').val(),
                            kP50: $('#kP50').val(),
                            kP70: $('#kP70').val(),

                            nstd_kPK: $('#nstd_kPK').val(),
                            nstd_kP5: $('#nstd_kP5').val(),
                            nstd_kP10: $('#nstd_kP10').val(),
                            nstd_kP20: $('#nstd_kP20').val(),
                            nstd_kP30: $('#nstd_kP30').val(),
                            nstd_kP50: $('#nstd_kP50').val(),
                            nstd_kP70: $('#nstd_kP70').val(),

                            palletWeight: $('#palletWeight').val(),
                            palletHeight: $('#palletHeight').val(),

                            ReceiveSenderEmail: $('#ReceiveSenderEmail').attr('checked'),
                            ReceiveSenderSMS: $('#ReceiveSenderSMS').attr('checked'),
                            ReceiveReceiverEmail: $('#ReceiveReceiverEmail').attr('checked'),
                            ReceiveReceiverSMS: $('#ReceiveReceiverSMS').attr('checked'),

                            CourierSenderEmail: $('#CourierSenderEmail').attr('checked'),
                            CourierSenderSMS: $('#CourierSenderSMS').attr('checked'),
                            CourierReceiverEmail: $('#CourierReceiverEmail').attr('checked'),
                            CourierReceiverSMS: $('#CourierReceiverSMS').attr('checked'),

                            AwizoSenderEmail: $('#AwizoSenderEmail').attr('checked'),
                            AwizoSenderSMS: $('#AwizoSenderSMS').attr('checked'),
                            AwizoReceiverEmail: $('#AwizoReceiverEmail').attr('checked'),
                            AwizoReceiverSMS: $('#AwizoReceiverSMS').attr('checked'),

                            DeliverSenderEmail: $('#DeliverSenderEmail').attr('checked'),
                            DeliverSenderSMS: $('#DeliverSenderSMS').attr('checked'),
                            DeliverReceiverEmail: $('#DeliverReceiverEmail').attr('checked'),
                            DeliverReceiverSMS: $('#DeliverReceiverSMS').attr('checked'),

                            RefuseSenderEmail: $('#RefuseSenderEmail').attr('checked'),
                            RefuseSenderSMS: $('#RefuseSenderSMS').attr('checked'),
                            RefuseReceiverEmail: $('#RefuseReceiverEmail').attr('checked'),
                            RefuseReceiverSMS: $('#RefuseReceiverSMS').attr('checked'),


                            saleDocId: $('#saleDocId').val(),
                            packageContent: $('#packageContent').val(),

                            term: $('input.terms:checked').val(),
                            COD: $('#COD').attr('checked'),
                            INS: $('#INS').attr('checked'),
                            ROD: $('#ROD').attr('checked'),
                            SRE: $('#SRE').attr('checked'),
                            SSE: $('#SSE').attr('checked'),
                            BYH: $('#BYH').attr('checked'),
                            H24: $('#H24').attr('checked'),
                            cod_value: pobranie,
                            ins_value: ubezpieczenie

                        },

                        success: function( data )
                        {
                            $('div#summary').html(data);
                            $('button.confirmBut').click(function (){
                                $('#courier').val($(this).val());
                                $('form#apiForm').submit();
                            });
                        }
                });
            });

            $('a.alert_email.sender').click(function (){
                $('input#ReceiveSenderEmail, input#CourierSenderEmail, input#AwizoSenderEmail, input#DeliverSenderEmail, input#RefuseSenderEmail').attr('checked','checked');
                return false;
            });
            $('a.alert_sms.sender').click(function (){
                if( $('#receiver_country').val() == "PL" )
                    $('input#ReceiveSenderSMS, input#CourierSenderSMS, input#AwizoSenderSMS, input#DeliverSenderSMS, input#RefuseSenderSMS').attr('checked','checked');
                return false;
            });
            $('a.alert_email.receiver').click(function (){
                $('input#ReceiveReceiverEmail, input#CourierReceiverEmail, input#AwizoReceiverEmail, input#DeliverReceiverEmail, input#RefuseReceiverEmail').attr('checked','checked');
                return false;
            });
            $('a.alert_sms.receiver').click(function (){
                if( $('#receiver_country').val() == 'PL' )
                    $('input#ReceiveReceiverSMS, input#CourierReceiverSMS, input#AwizoReceiverSMS, input#DeliverReceiverSMS, input#RefuseReceiverSMS').attr('checked','checked');
                return false;
            });
          });
          </script>

          <?php
            //if ( $apiKurier->success ) {
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;

            $wysylki        = $apiKurier->produkty;

            //$adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);

            $kPK = $kP5 = $kP10 = $kP20 = $kP30 = $kP50 = $kP70 = 0;
            if( (float)$waga_produktow <= 5 ) {
                $kP5 = 1;
            } elseif( (float)$waga_produktow <= 10 ) {
                $kP10 = 1;
            } elseif( (float)$waga_produktow <= 20 ) {
                $kP20 = 1;
            } elseif( (float)$waga_produktow <= 30 ) {
                $kP30 = 1;
            } elseif( (float)$waga_produktow <= 50 ) {
                $kP50 = 1;
            } elseif( (float)$waga_produktow <= 70 ) {
                $kP70 = 1;
            }

            $kodPocztowyNadawcy = $apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_KOD_POCZTOWY'];
            if(preg_match("/^([0-9]{2})(-[0-9]{3})?$/i",$kodPocztowyNadawcy)) {
            } else {
                $kodPocztowyNadawcy = substr((string)$kodPocztowyNadawcy,'0','2') . '-' . substr((string)$kodPocztowyNadawcy,'2','5'); 
            }
            ?>

            <form action="sprzedaz/zamowienia_wysylka_sendit.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" id="zakladka" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" id="sender_name" name="sender_name" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_NAZWA']; ?>" />
                  <input type="hidden" id="sender_street" name="sender_street" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_ULICA']; ?>" />
                  <input type="hidden" id="sender_postcode" name="sender_postcode" value="<?php echo $kodPocztowyNadawcy; ?>" />
                  <input type="hidden" id="sender_city" name="sender_city" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_MIASTO']; ?>" />
                  <input type="hidden" id="sender_country" name="sender_country" value="<?php echo $apiKurier->getIsoCountry($apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_KRAJ']); ?>" />
                  <input type="hidden" id="sender_phone" name="sender_phone" value="<?php echo preg_replace( '/[^0-9+]/', '', (string)$apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_TELEFON']); ?>" />
                  <input type="hidden" id="sender_email" name="sender_email" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_EMAIL']; ?>" />
                  <input type="hidden" id="sender_contact" name="sender_contact" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_SENDIT_NADAWCA_KONTAKT']; ?>" />
                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o paczkach</div>

                        <div class="paczki">

                            <div class="paczkiStandardowe">
                                <h3>Paczki standardowe:</h3>
                                <ul class="paczkaForm">
                                    <li>
                                        <label for="kPK" class="labelPaczka">kopertowych: </label><input class="zero" name="kPK" id="kPK" size="2" type="text" value="<?php echo $kPK ?>" />
                                    </li>
                                    <li>
                                        <label for="kP5" class="labelPaczka">do 5kg: </label><input class="zero" name="kP5" id="kP5" size="2" type="text" value="<?php echo $kP5 ?>" />
                                    </li>
                                    <li>
                                        <label for="kP10" class="labelPaczka">do 10kg: </label><input class="zero" name="kP10" id="kP10" size="2" type="text" value="<?php echo $kP10 ?>" />
                                    </li>
                                    <li>
                                        <label for="kP20" class="labelPaczka">do 20kg: </label><input class="zero" name="kP20" id="kP20" size="2" type="text" value="<?php echo $kP20 ?>" />
                                    </li>
                                    <li>
                                        <label for="kP30" class="labelPaczka">do 30kg: </label><input class="zero" name="kP30" id="kP30" size="2" type="text" value="<?php echo $kP30 ?>" />
                                    </li>
                                    <li>
                                        <label for="kP50" class="labelPaczka">do 50kg: </label><input class="zero" name="kP50" id="kP50" size="2" type="text" value="<?php echo $kP50 ?>" />
                                    </li>
                                    <li>
                                        <label for="kP70" class="labelPaczka">do 70kg: </label><input class="zero" name="kP70" id="kP70" size="2" type="text" value="<?php echo $kP70 ?>" />
                                    </li>
                                </ul>
                            </div>

                            <div class="paczkiStandardowe">
                                <h3>Paczki niestandardowe:</h3>
                                <ul class="paczkaForm">
                                    <li>
                                        <label for="nstd_kPK" class="labelPaczka">kopertowych: </label><input name="nstd_kPK" id="nstd_kPK" class="parcel" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="nstd_kP5" class="labelPaczka">do 5kg: </label><input name="nstd_kP5" id="nstd_kP5" class="parcel" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="nstd_kP10" class="labelPaczka">do 10kg: </label><input name="nstd_kP10" id="nstd_kP10" class="parcel" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="nstd_kP20" class="labelPaczka">do 20kg: </label><input name="nstd_kP20" id="nstd_kP20" class="parcel" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="nstd_kP30" class="labelPaczka">do 30kg: </label><input name="nstd_kP30" id="nstd_kP30" class="parcel" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="nstd_kP50" class="labelPaczka">do 50kg: </label><input name="nstd_kP50" id="nstd_kP50" class="parcel" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="nstd_kP70" class="labelPaczka">do 70kg: </label><input name="nstd_kP70" id="nstd_kP70" class="parcel" type="text" value="0" />
                                    </li>
                                </ul>
                            </div>

                            <div class="paczkiStandardowe">
                                <h3>Palety:</h3>
                                <ul class="paczkaForm">
                                    <li>
                                        <label for="palletWeight" class="labelPaczka">waga: </label><input name="palletWeight" id="palletWeight" class="pallet" type="text" value="0" />
                                    </li>
                                    <li>
                                        <label for="palletHeight" class="labelPaczka">wysokość: </label><input name="palletHeight" id="palletHeight" class="pallet" type="text" value="0" />
                                    </li>
                                </ul>
                            </div>

                        </div>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Powiadomienia</div>

                                <table>
                                    <thead>
                                    <tr>
                                        <th style="width: 250px"></th>
                                        <th colspan="2">Nadawca</th>
                                        <th style="width: 20px"></th>
                                        <th colspan="2">Odbiorca</th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th><a href="" class="alert_email sender TipChmurka"><b>Zaznacz wszystkie e-maile dla nadawcy</b></a></th>
                                        <th><a href="" class="alert_sms sender TipChmurka"><b>Zaznacz wszystkie SMS-y dla nadawcy</b></a></th>
                                        <th></th>
                                        <th><a href="" class="alert_email receiver TipChmurka"><b>Zaznacz wszystkie e-maile dla odbiorcy></b></a></th>
                                        <th><a href="" class="alert_sms receiver TipChmurka"><b>Zaznacz wszystkie SMS-y dla odbiorcy></b></a></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><label>Odbiór od nadawcy</label></td>
                                        <td><input class="notify" name="ReceiveSenderEmail" id="ReceiveSenderEmail" type="checkbox" /><label class="OpisForPustyLabel" for="ReceiveSenderEmail"></label></td>
                                        <td><input class="notify sms" name="ReceiveSenderSMS" id="ReceiveSenderSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="ReceiveSenderSMS"></label></td>
                                        <td></td>
                                        <td><input class="notify" name="ReceiveReceiverEmail" id="ReceiveReceiverEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="ReceiveReceiverEmail"></label></td>
                                        <td><input class="notify sms" name="ReceiveReceiverSMS" id="ReceiveReceiverSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="ReceiveReceiverSMS"></label></td>
                                    </tr>
                                    <tr>
                                        <td><label>Wydanie kurierowi, Odbiór w terminalu</label></td>
                                        <td><input class="notify" name="CourierSenderEmail" id="CourierSenderEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="CourierSenderEmail"></label></td>
                                        <td><input class="notify sms" name="CourierSenderSMS" id="CourierSenderSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="CourierSenderSMS"></label></td>
                                        <td></td>
                                        <td><input class="notify" name="CourierReceiverEmail" id="CourierReceiverEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="CourierReceiverEmail"></label></td>
                                        <td><input class="notify sms" name="CourierReceiverSMS" id="CourierReceiverSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="CourierReceiverSMS"></label></td>
                                    </tr>
                                    <tr>
                                        <td><label>Awizowanie</label></td>
                                        <td><input class="notify" name="AwizoSenderEmail" id="AwizoSenderEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="AwizoSenderEmail"></label></td>
                                        <td><input class="notify sms" name="AwizoSenderSMS" id="AwizoSenderSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="AwizoSenderSMS"></label></td>
                                        <td></td>
                                        <td><input class="notify" name="AwizoReceiverEmail" id="AwizoReceiverEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="AwizoReceiverEmail"></label></td>
                                        <td><input class="notify sms" name="AwizoReceiverSMS" id="AwizoReceiverSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="AwizoReceiverSMS"></label></td>
                                    </tr>
                                    <tr>
                                        <td><label>Doręczenie</label></td>
                                        <td><input class="notify" name="DeliverSenderEmail" id="DeliverSenderEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="DeliverSenderEmail"></label></td>
                                        <td><input class="notify sms" name="DeliverSenderSMS" id="DeliverSenderSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="DeliverSenderSMS"></label></td>
                                        <td></td>
                                        <td><input class="notify" name="DeliverReceiverEmail" id="DeliverReceiverEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="DeliverReceiverEmail"></label></td>
                                        <td><input class="notify sms" name="DeliverReceiverSMS" id="DeliverReceiverSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="DeliverReceiverSMS"></label></td>
                                    </tr>
                                    <tr>
                                        <td><label>Odmowa przyjęcia</label></td>
                                        <td><input class="notify" name="RefuseSenderEmail" id="RefuseSenderEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="RefuseSenderEmail"></label></td>
                                        <td><input class="notify sms" name="RefuseSenderSMS" id="RefuseSenderSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="RefuseSenderSMS"></label></td>
                                        <td></td>
                                        <td><input class="notify" name="RefuseReceiverEmail" id="RefuseReceiverEmail" type="checkbox"  /><label class="OpisForPustyLabel" for="RefuseReceiverEmail"></label></td>
                                        <td><input class="notify sms" name="RefuseReceiverSMS" id="RefuseReceiverSMS" type="checkbox" <?php echo ($zamowienie->dostawa['kraj'] != 'Polska')?'disabled = "disabled"':''; ?>  /><label class="OpisForPustyLabel" for="RefuseReceiverSMS"></label></td>
                                    </tr>
                                    </tbody>
                                </table>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Informacje dodatkowe</div>

                        <p>
                            <label for="saleDocId">Numer dokumentu sprzedaży: </label>
                            <input name="saleDocId" id="saleDocId"  type="text" value="" maxlength="80" size="50" />
                        </p>
                        <p>
                            <label for="packageContent"> Zawartość przesyłki: </label>
                            <input name="packageContent" id="packageContent" type="text" value="" maxlength="80" size="50" />
                        </p>
                        <p>
                            <label for="protocol"> Automatycznie generuj protokół odbioru: </label>
                            <input name="protocol" id="protocol" type="checkbox" value="" /><label class="OpisForPustyLabel" for="protocol"></label>
                        </p>

                    </div>

                    <div id="services" style="margin-top:8px">

                    </div>

                    <div id="summary" style="margin-top:8px">

                    </div>

                </div>
                    
                <div class="OknoDodatkowe">

                    <div class="poleForm">

                        <div class="naglowek">Informacje</div>

                        <p>
                            <label class="readonly">Forma dostawy w zamówieniu:</label>
                            <input type="text" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                        </p> 
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
                                <label for="receiver_name">Adresat:</label>
                                <input type="text" size="40" name="receiver_name" id="receiver_name" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiver_street">Adres:</label>
                                <input type="text" size="40" name="receiver_street" id="receiver_street" value="<?php echo $zamowienie->dostawa['ulica']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiver_postcode">Kod pocztowy:</label>
                                <input type="text" size="40" name="receiver_postcode" id="receiver_postcode" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiver_city">Miejscowość:</label>
                                <input type="text" size="40" name="receiver_city" id="receiver_city" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiver_country">Kraj:</label>
                                <?php 
                                $domyslnie = $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); 
                                $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                                echo Funkcje::RozwijaneMenu('receiver_country', $tablicaPanstw, $domyslnie, 'id="receiver_country" class="klient" style="width:210px;"' ); 

                                unset($tablicaPanstw);
                                ?>
                            </p> 
                            <p>
                                <label for="receiver_phone">Numer telefonu:</label>
                                <?php 
                                if ( $zamowienie->dostawa['telefon'] != '' ) {
                                    $NumerTelefonu = $zamowienie->dostawa['telefon'];
                                } else {
                                    $NumerTelefonu = $zamowienie->klient['telefon'];
                                }
                                ?>
                                <input type="text" size="40" name="receiver_phone" id="receiver_phone" value="<?php echo preg_replace( '/[^0-9+]/', '', (string)$NumerTelefonu); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiver_email">Adres e-mail:</label>
                                <input type="text" size="40" name="receiver_email" id="receiver_email" value="<?php echo $zamowienie->klient['adres_email']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiver_contact">Osoba kontaktowa:</label>
                                <input type="text" size="40" name="receiver_contact" id="receiver_contact" value="<?php echo $zamowienie->klient['nazwa']; ?>" class="klient" />
                            </p> 
                        
                    </div>
                    
                </div>

              </div>

              <div class="przyciski_dolne">
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                <button class="przyciskNon" type="button" id="checkService">Sprawdź dostępność usług</button>
                <button class="przyciskNon" type="button" id="rate" disabled="disabled">Wyceń przesyłkę</button>
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
