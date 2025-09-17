<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new InPostShipX();

            // Utworzenie pliku pdf zawierającego etykiety dla dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $PaczkidoProtokolu = array();

                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'INPOST' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_protocol'] != '' ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_protocol'];
                    }
                }

                $DaneWejsciowe['type'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_TYP'];
                $DaneWejsciowe['format'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'];
                $DaneWejsciowe['shipment_ids'] = $PaczkidoProtokolu;

                $plikEtykiet = $apiKurier->FilesRequest('v1/organizations/' . $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'] . '/shipments/labels', $DaneWejsciowe );

                if ( $plikEtykiet['0'] == 'application/zip' ) {

                    header('Content-type: application/force-download');
                    header('Content-Disposition: attachment; filename="etykiety_'.time().'.zip"');

                } else {

                    if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'pdf' ) {
                      header('Content-type: application/pdf');
                      header('Content-Disposition: attachment; filename="etykiety_'.time().'.pdf"');
                    }
                    if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'zpl' ) {
                      header('Content-type: application/txt');
                      header('Content-Disposition: attachment; filename="etykiety_'.time().'.zpl"');
                    }

                }

                echo $plikEtykiet['1'];

                //Funkcje::PrzekierowanieURL('zamowienia_wysylki_inpost.php');

                return;

            }
     
            // Utworzenie zawierającego potwierdzenia nadania dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 2 ) {

                $PaczkidoProtokolu = '';
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'INPOST' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_misc'] != '' ) {
                        $PaczkidoProtokolu .= 'shipment_ids[]='.$info['orders_shipping_protocol'] .'&';
                    }
                }

                $DaneWejsciowe['format'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'];
                $DaneWejsciowe['shipment_ids'] = $PaczkidoProtokolu;

                $plikProtokolow = $apiKurier->FilesGetRequest('v1/organizations/'.$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/dispatch_orders/printouts?' . $PaczkidoProtokolu . 'format='.$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT']);

                if ( $plikProtokolow['0'] == 'application/zip' ) {

                    header('Content-type: application/force-download');
                    header('Content-Disposition: attachment; filename="protokoly_'.time().'.zip"');

                } else {

                    if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'pdf' ) {
                      header('Content-type: application/pdf');
                      header('Content-Disposition: attachment; filename="protokoly_'.time().'.pdf"');
                    }
                    if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'zpl' ) {
                      header('Content-type: application/txt');
                      header('Content-Disposition: attachment; filename="protokoly_'.time().'.zpl"');
                    }

                }

                echo $plikProtokolow['1'];

                //Funkcje::PrzekierowanieURL('zamowienia_wysylki_inpost.php');

                return;

            }

            // Zamowienie kuriera
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $PaczkidoOdbioru = array();
                $Komunikat = '';

                $idPaczek = implode(',', (array)$_POST['opcja']);

                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'INPOST' AND orders_shipping_id IN (".$idPaczek.") AND orders_shipping_packages = 'dispatch_order' AND orders_dispatch_status IS NULL";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_misc'] != '' ) {
                        $PaczkidoOdbioru[] = $info['orders_shipping_protocol'];
                    }
                }

                $db->close_query($sql);
                unset($zapytanie, $info);

                $przesylka = array();

                $przesylka['shipments'] = $PaczkidoOdbioru;
                $przesylka['comment'] = $_POST['komentarz'];
                $przesylka['address']['street'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ULICA'];
                $przesylka['address']['building_number'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_BUILDING'];
                $przesylka['address']['city'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_MIASTO'];
                $przesylka['address']['post_code'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_KOD_POCZTOWY'];
                $przesylka['address']['country_code'] = 'PL';

                $dane = $apiKurier->PostRequest('v1/organizations/' . $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/dispatch_orders', $przesylka);    

                if ( isset($dane->error) && $dane->error != '' ) {

                    foreach ( $dane->details as $key=>$value ) {
                       $Komunikat .= $key . ' : ' . ( is_string($value['0']) ? $value['0'] : '' ) . '<br />';
                    }
                    $apiKurier->PokazBlad('Błąd', $Komunikat, 'zamowienia_wysylki_inpost.php?id_poz='.$_GET['id_poz']);

                } else {

                    foreach ( $dane->shipments AS $Przesylka ) {
                      $pola = array();
                      $pola = array(
                                  array('orders_dispatch_status',$dane->status),
                                  array('orders_shipping_dispatch_id',$dane->id),
                                  array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($dane->updated_at)))
                              );

                      $db->update_query('orders_shipping' , $pola, " orders_shipping_protocol = '".$Przesylka->id."'");
                      unset($pola);
                    }
                }

                Funkcje::PrzekierowanieURL('zamowienia_wysylki_inpost.php');

            }

        }
    
    }

    //Funkcje::PrzekierowanieURL('zamowienia_wysylki_inpost.php');
    
}
?>