<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['id_poz']) ) {
        $apiKurier       = new GeisApi((int)$_GET['id_poz']);
    } else {
        $apiKurier       = new GeisApi();
    }

    switch ( $_GET['akcja'] ) {

        //Wydruk protokolu
        case 'status':

            $Informacja = '';

            $DaneWejsciowe['DistributionChannel'] = '2';
            $DaneWejsciowe['ShipmentNumber'] = $filtr->process($_GET['przesylka']);

            $Wynik = $apiKurier->doShipmentDetail($DaneWejsciowe);

            if ( isset($Wynik->ShipmentDetailResult->ErrorCode) && $Wynik->ShipmentDetailResult->ErrorCode != '0000' && $Wynik->ShipmentDetailResult->ErrorCode != '0001' ) {
                $Informacja = $Wynik->ShipmentDetailResult->ErrorCode .' - ' . $Wynik->ShipmentDetailResult->ErrorMessage;
            } else {
                if ( count($Wynik->ShipmentDetailResult->ResponseObject->History->PackageHistory) > 1 ) {
                    foreach ( $Wynik->ShipmentDetailResult->ResponseObject->History->PackageHistory as $row ) {
                        $Data = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($row->StatusDate));
                        $Informacja .= $Data . ' : ' . $row->StatusName . '<br>';
                        $pola = array(
                                array('orders_shipping_status', $row->StatusCode),
                                array('orders_shipping_date_modified', $Data),
                            );
                    }
                } else {
                    $Data = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($Wynik->ShipmentDetailResult->ResponseObject->History->PackageHistory->StatusDate));
                    $Informacja = $Data . ' : ' . $Wynik->ShipmentDetailResult->ResponseObject->History->PackageHistory->StatusName;
                    $pola = array(
                            array('orders_shipping_status', $Wynik->ShipmentDetailResult->ResponseObject->History->PackageHistory->StatusCode),
                            array('orders_shipping_date_modified', $Data),
                        );
                }
                $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$filtr->process($_GET['przesylka'])."' AND orders_id = '".(int)$_GET['id_poz']."'");
            }

            include('naglowek.inc.php');
            if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                echo Okienka::pokazOkno('Status', $Informacja, 'sprzedaz/zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
            } else {
                echo Okienka::pokazOkno('Status', $Informacja, 'sprzedaz/zamowienia_wysylki_geis.php');
            }
            include('stopka.inc.php');

        break;

        //Wydruk protokolu
        case 'etykieta':

            $DaneWejsciowe = array($filtr->process($_GET['przesylka']));

            $Wynik = $apiKurier->doGetLabel($DaneWejsciowe);

            if ( isset($Wynik->GetLabelResult->ResponseObject->LabelData->LabelItemData->Data) && $Wynik->GetLabelResult->ResponseObject->LabelData->LabelItemData->Data != '' ) {
                if ( $Wynik->GetLabelResult->Request->RequestObject->Format == '1' || $Wynik->GetLabelResult->Request->RequestObject->Format == '5') {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                }
                if ( $Wynik->GetLabelResult->Request->RequestObject->Format == '2' ) {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.epl"');
                }
                if ( $Wynik->GetLabelResult->Request->RequestObject->Format == '3' ) {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.zpl"');
                }
                echo $Wynik->GetLabelResult->ResponseObject->LabelData->LabelItemData->Data;
            }

        break;

        //usuniecie przesylki z GEIS
        case 'usun':

            $DaneWejsciowe['DistributionChannel'] = '2';
            $DaneWejsciowe['ShipmentNumber'] = $filtr->process($_GET['przesylka']);

            $Wynik = $apiKurier->doShipmentDetail($DaneWejsciowe);

            if ( isset($Wynik->ShipmentDetailResult->ErrorCode) && $Wynik->ShipmentDetailResult->ErrorCode == '0002' ) {

                $DaneWejscioweKasuj['ShipmentsNumbers']['DeleteShipmentItem']['DistributionChannel'] = '2';
                $DaneWejscioweKasuj['ShipmentsNumbers']['DeleteShipmentItem']['ShipmentNumber'] = $filtr->process($_GET['przesylka']);
                $WynikKasuj = $apiKurier->doDeleteShipment($DaneWejscioweKasuj);
                if ( $WynikKasuj->DeleteShipmentResult->ResponseObject->ShipmentsNumbers->DeleteShipmentItemResponse->IsStorno == true ) {
                    $pola = array(
                        array('orders_shipping_status', 'Anulowana'),
                        array('orders_shipping_date_modified','now()'),
                    );
                    $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$filtr->process($_GET['przesylka'])."' AND orders_id = '".(int)$_GET['id_poz']."'");

                    if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
                    } else {
                        Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');
                    }

                } else {

                    include('naglowek.inc.php');
                    if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                        echo Okienka::pokazOkno('Status', $WynikKasuj->DeleteShipmentResult->ResponseObject->ShipmentsNumbers->DeleteShipmentItemResponse->ErrorMessage, 'sprzedaz/zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
                    } else {
                        echo Okienka::pokazOkno('Status', $WynikKasuj->DeleteShipmentResult->ResponseObject->ShipmentsNumbers->DeleteShipmentItemResponse->ErrorMessage, 'sprzedaz/zamowienia_wysylki_geis.php');
                    }
                    include('stopka.inc.php');
                }
            }
            if ( isset($Wynik->ShipmentDetailResult->ErrorCode) && $Wynik->ShipmentDetailResult->ErrorCode == '0003' ) {

                    include('naglowek.inc.php');
                    if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                        echo Okienka::pokazOkno('Status', $Wynik->ShipmentDetailResult->ErrorMessage, 'sprzedaz/zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
                    } else {
                        echo Okienka::pokazOkno('Status', $Wynik->ShipmentDetailResult->ErrorMessage, 'sprzedaz/zamowienia_wysylki_geis.php');
                    }
                    include('stopka.inc.php');

            }

        break;


        //Usuniecie przesylki z bazy sklepu
        case 'usunbaza':

          $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_number = '".$filtr->process($_GET["przesylka"])."'");

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_geis.php');
          }



        break;

    }
}


?>