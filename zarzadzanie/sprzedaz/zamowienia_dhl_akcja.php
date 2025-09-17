<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        $apiKurier       = new DhlApi();

        // jezeli wydrukowanie protokolu
        if ( (int)$_POST['akcja_dolna'] == 2 ) {

            $Wynik = $apiKurier->getpnpAction($_POST['data_odbioru']);

            if ( $Wynik != false ) {

                if ( isset($Wynik->getPnpResult->fileName) && $Wynik->getPnpResult->fileName != '' ) {

                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="'.$Wynik->getPnpResult->fileName.'');
                    echo base64_decode((string)$Wynik->getPnpResult->fileData);

                }

            }
        }

        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            // jezeli zamowienie kuriera
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $PaczkidoZamowienia = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'DHL' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    $PaczkidoZamowienia[] = $info['orders_shipping_number'];
                }

                if ( isset($_POST['pickup_date']) && $_POST['pickup_date'] != '' && $_POST['pickup_date_hours_from'] != '' && $_POST['pickup_date_hours_to'] != '' ) {
                    $Data = explode('|', (string)$_POST['pickup_date']);

                    $DaneWejsciowe = array();

                    $DaneWejsciowe['pickupDate'] = $Data['0'];
                    $DaneWejsciowe['pickupTimeFrom'] = $_POST['pickup_date_hours_from'];
                    $DaneWejsciowe['pickupTimeTo'] = $_POST['pickup_date_hours_to'];
                    $DaneWejsciowe['additionalInfo'] = $_POST['comment'];
                    $DaneWejsciowe['shipmentIdList'] = $PaczkidoZamowienia;
                    if ( isset($_POST['courierWithLabel']) && $_POST['courierWithLabel'] == '1' ) {
                        $DaneWejsciowe['courierWithLabel'] = true;
                    }

                    $Wynik = $apiKurier->bookAction($DaneWejsciowe);

                    if ( $Wynik != false ) {

                        if ( isset($Wynik->bookCourierResult) && $Wynik->bookCourierResult->item != '' ) {

                            foreach ( $_POST['opcja'] as $key => $value ) {

                                $pola = array(
                                        array('orders_shipping_status', 'Kurier zamówiony'),
                                        array('orders_shipping_protocol', $Wynik->bookCourierResult->item),
                                        array('orders_shipping_misc', $Data['0']),
                                        array('orders_shipping_date_modified','now()'),
                                );

                                $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$value."'");

                            }
                            include('naglowek.inc.php');
                            echo Okienka::pokazOkno('Zamówienie kuriera', 'Zarejestrowano zlecenie odbioru przesyłki nr : '.$Wynik->bookCourierResult->item, 'sprzedaz/zamowienia_wysylki_dhl.php');
                            include('stopka.inc.php');

                        } else {
                            include('naglowek.inc.php');
                            echo Okienka::pokazOkno('Zamówienie kuriera', 'Błąd', 'sprzedaz/zamowienia_wysylki_dhl.php');
                            include('stopka.inc.php');
                        }

                    }

                }

            }

        }
     
    }
    
    //Funkcje::PrzekierowanieURL('zamowienia_wysylki_dhl.php');
    
}
?>