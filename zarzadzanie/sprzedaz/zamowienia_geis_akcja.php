<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0 ) {

        $apiKurier       = new GeisApi();

        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 && $_POST['akcja_dolna'] != '3' ) {
        

            // Wydrukowanie etykiet dla zaznaczonych wysylek
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $PaczkidoProtokolu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'GEIS' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_comments'] == 'export' && $info['orders_shipping_status'] != 'Anulowana' ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_number'];
                    }
                }
                if ( count($PaczkidoProtokolu) > 0 ) {

                    $Wynik = $apiKurier->doGetLabel($PaczkidoProtokolu);

                    if ( isset($Wynik->GetLabelResult->ResponseObject->LabelData->LabelItemData->Data) && $Wynik->GetLabelResult->ResponseObject->LabelData->LabelItemData->Data != '' ) {
                        if ( $Wynik->GetLabelResult->Request->RequestObject->Format == '1' || $Wynik->GetLabelResult->Request->RequestObject->Format == '5') {
                            header('Content-type: application/pdf');
                            header('Content-Disposition: attachment; filename="etykiety.pdf"');
                        }
                        if ( $Wynik->GetLabelResult->Request->RequestObject->Format == '2' ) {
                            header('Content-type: application/txt');
                            header('Content-Disposition: attachment; filename="etykiety.epl"');
                        }
                        if ( $Wynik->GetLabelResult->Request->RequestObject->Format == '3' ) {
                            header('Content-type: application/txt');
                            header('Content-Disposition: attachment; filename="etykiety.zpl"');
                        }
                        echo $Wynik->GetLabelResult->ResponseObject->LabelData->LabelItemData->Data;
                    }

                }
                //Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');

            }
            
            // Wydrukowanie protokolu dla wybranych wysylek
            if ( (int)$_POST['akcja_dolna'] == 2 ) {

                $PaczkidoProtokolu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'GEIS' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_comments'] == 'export' && $info['orders_shipping_status'] != 'Anulowana' ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_number'];
                    }
                }
                if ( count($PaczkidoProtokolu) > 0 ) {

                    $Wynik = $apiKurier->doGetPickupList($PaczkidoProtokolu, true);

                    if ( isset($Wynik->GetPickupListResult->ResponseObject->PickupListData) && $Wynik->GetPickupListResult->ResponseObject->PickupListData != '' ) {
                        header('Content-type: application/pdf');
                        header('Content-Disposition: attachment; filename="protokol.pdf"');
                        echo $Wynik->GetPickupListResult->ResponseObject->PickupListData;
                    }

                }
                //Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');

            }

        } else if ( isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && $_POST['akcja_dolna'] == '3' ) {

            // Wydrukowanie protokolu dla wybranej daty
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $PaczkidoProtokolu = $_POST['data_odbioru_kuriera'];

                $Wynik = $apiKurier->doGetPickupList($PaczkidoProtokolu, false);

                    if ( isset($Wynik->GetPickupListResult->ResponseObject->PickupListData) && $Wynik->GetPickupListResult->ResponseObject->PickupListData != '' ) {
                        header('Content-type: application/pdf');
                        header('Content-Disposition: attachment; filename="protokol.pdf"');
                        echo $Wynik->GetPickupListResult->ResponseObject->PickupListData;
                    }

                //Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');

            }


        } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');
            return;
        }
     
    } else {
        Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');
    }
}
?>