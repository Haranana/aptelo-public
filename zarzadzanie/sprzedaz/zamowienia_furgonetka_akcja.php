<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {

        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new FurgonetkaRestApi(true, '', '');

            // Utworzenie pliku pdf zawierającego etykiety dla dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $UUID = $apiKurier->UUIDv4();

                $params = new stdClass;
                $params->packages = array();

                $Komunikat = '';
                $Bledy = '';

                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'FURGONETKA' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_misc'] != '' ) {
                        $Parcel             = new stdClass;
                        $Parcel->id         = $info['orders_shipping_misc'];
                        $params->packages[] = $Parcel;

                    }
                }

                $params->documents_types = array();
                $params->documents_types[] = 'labels';

                $WynikUUID = $apiKurier->commandPut('documents-command/'.$UUID, $params);
                sleep(5);
                $Wynik = $apiKurier->commandGet('documents-command/'.$WynikUUID->uuid, true, '', false);

                if ( $Wynik && $Wynik->url == '' && $Wynik->status == 'error' ) {

                    $Komunikat .= 'Status : ' . $Wynik->status . '<br />';
                    foreach ( $Wynik->errors as $Blad ) {
                        $Bledy .= $Blad->path . ' : ' . $Blad->message . '<br />';
                    }
                    $Komunikat .= $Bledy;

                    include('naglowek.inc.php');
                    echo Okienka::pokazOkno('Wynik', $Komunikat, 'sprzedaz/zamowienia_wysylki_furgonetka.php');
                    include('stopka.inc.php');

                } elseif ( $Wynik->url != '' && ( $Wynik->status == 'successful' || $Wynik->status == 'partial_success' ) ) {

                    $link = '<a class=\"pobierzPlik\" href=\"'.$Wynik->url.'\">kliknij żeby pobrać plik z etykietami</a>';
                    include('naglowek.inc.php');
                    echo Okienka::pokazOkno('Plik został wygenerowany', $link, 'sprzedaz/zamowienia_wysylki_furgonetka.php');
                    include('stopka.inc.php');

                }

            }
     
            // Utworzenie zawierającego potwierdzenia nadania dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 2 ) {

                $UUID = $apiKurier->UUIDv4();

                $params = new stdClass;
                $params->packages = array();

                $Komunikat = '';
                $Bledy = '';

                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'FURGONETKA' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_misc'] != '' ) {
                        $Parcel             = new stdClass;
                        $Parcel->id         = $info['orders_shipping_misc'];
                        $params->packages[] = $Parcel;

                    }
                }

                $params->documents_types = array();
                $params->documents_types[] = 'protocols_others';

                $WynikUUID = $apiKurier->commandPut('documents-command/'.$UUID, $params);
                sleep(5);
                $Wynik = $apiKurier->commandGet('documents-command/'.$WynikUUID->uuid, true, '', false);

                if ( $Wynik && $Wynik->url == '' && $Wynik->status == 'error' ) {

                    $Komunikat .= 'Status : ' . $Wynik->status . '<br />';
                    foreach ( $Wynik->errors as $Blad ) {
                        $Bledy .= $Blad->path . ' : ' . $Blad->message . '<br />';
                    }
                    $Komunikat .= $Bledy;

                    include('naglowek.inc.php');
                    echo Okienka::pokazOkno('Wynik', $Komunikat, 'sprzedaz/zamowienia_wysylki_furgonetka.php');
                    include('stopka.inc.php');

                } elseif ( $Wynik->url != '' && ( $Wynik->status == 'successful' || $Wynik->status == 'partial_success' ) ) {

                    $link = '<a class=\"pobierzPlik\" href=\"'.$Wynik->url.'\">kliknij żeby pobrać plik z protokołami</a>';
                    include('naglowek.inc.php');
                    echo Okienka::pokazOkno('Plik został wygenerowany', $link, 'sprzedaz/zamowienia_wysylki_furgonetka.php');
                    include('stopka.inc.php');

                }

            }

            // Zamowienie kuriera
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $UUID = $apiKurier->UUIDv4();

                $DataOdbioru = array();
                if ( isset($_POST['odbior']) ) {
                    $DataOdbioru = explode('|', (string)$_POST['odbior']);
                }

                $params = new stdClass;
                $params->packages = array();

                $Komunikat = '';
                $Bledy = '';

                for ( $i = 0, $c = count($_POST['paczki']); $i < $c; $i++ ) {
                    $Parcel             = new stdClass;
                    $Parcel->id         = $_POST['paczki'][$i];
                    $params->packages[] = $Parcel;
                }

                if ( isset($_POST['odbior']) ) {
                    $params->pickup_date = new stdClass;
                    $params->pickup_date->date = $DataOdbioru['0'];
                    $params->pickup_date->min_time = $DataOdbioru['1'];
                    $params->pickup_date->max_time = $DataOdbioru['2'];
                }

                $Wynik = $apiKurier->commandPut('pickup-commands/'.$UUID, $params);

                if ( $Wynik ) {
                    for ( $i = 0, $c = count($_POST['opcja']); $i < $c; $i++ ) {
                        $pola = array(
                                array('orders_shipping_uuid_pickup',$Wynik->uuid)
                        );
                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$_POST['opcja'][$i]."'");
                    }
                }

                Funkcje::PrzekierowanieURL('zamowienia_wysylki_furgonetka.php');

            }

        } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_furgonetka.php');
        }
    
    } else {

        Funkcje::PrzekierowanieURL('zamowienia_wysylki_furgonetka.php');
    }
    
}
?>