<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new DpdApi();

            if ( $_POST['fid'] != '0' ) {
                $fid = $_POST['fid'];
            } else {
                $fid = $apiKurier->polaczenie['INTEGRACJA_DPD_FID'];
            }

            // jezeli wydrukowanie etykiet
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $kraj = 'PL';

                $PaczkidoEtykiet = array();
                $PaczkidoEtykietInt = array();

                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'DPD' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_to_country'] == 'PL' ) {
                        $PaczkidoEtykiet[] = $info['orders_shipping_comments'];
                    } else {
                        $PaczkidoEtykietInt[] = $info['orders_shipping_comments'];
                    }
                }

                if ( count($PaczkidoEtykietInt) > 0 ) {
                    $kraj = 'OTHER';
                    $wynik = $apiKurier->getLabelPDF(1, $PaczkidoEtykietInt, $kraj);
                } else {
                    $wynik = $apiKurier->getLabelPDF(1, $PaczkidoEtykiet, $kraj);
                }

                if( is_array($wynik) && $wynik['type'] == 'ok' && $wynik['file'] != '' ) {

                    
                    foreach ( $_POST['opcja'] as $key => $value ) {

                        $pola = array(
                                array('orders_shipping_status', '2'),
                                array('orders_shipping_date_modified','now()'),
                        );
                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$value."' AND orders_shipping_status != '999'");

                    }
                    

                    if ( $apiKurier->polaczenie['INTEGRACJA_DPD_FORMAT_PLIKU'] == 'PDF' ) {
                         header('Content-type: application/pdf');
                         header('Content-Disposition: attachment; filename="etykiety_'.time().'.pdf"');
                         echo base64_decode((string)$wynik['file']);
                    } else {
                         header('Content-type: application/txt');
                         header('Content-Disposition: attachment; filename="etykiety_'.time().'.zpl"');
                         echo base64_decode((string)$wynik['file']);
                    }

                }

            }

            // jezeli wydrukowanie protokolu
            if ( (int)$_POST['akcja_dolna'] == 2 ) {

                $PaczkidoProtokolu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'DPD' AND orders_shipping_id IN (".$idPaczek.") AND orders_shipping_misc = '".$fid."'";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    $PaczkidoProtokolu[] = $info['orders_shipping_comments'];
                }

                //get label by reference number
                $wynik = $apiKurier->getProtocol($PaczkidoProtokolu, $fid);

                if( is_array($wynik) && $wynik['type'] == 'ok' && $wynik['file'] != '' ) {

                    foreach ( $_POST['opcja'] as $key => $value ) {

                        $pola = array(
                                array('orders_shipping_status', '3'),
                                array('orders_shipping_date_modified','now()'),
                        );

                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$value."'");

                    }

                    if ( $apiKurier->polaczenie['INTEGRACJA_DPD_FORMAT_PLIKU'] == 'PDF' ) {
                         header('Content-type: application/pdf');
                         header('Content-Disposition: attachment; filename="protokol_'.time().'.pdf"');
                         echo base64_decode((string)$wynik['file']);
                    } else {
                         header('Content-type: application/txt');
                         header('Content-Disposition: attachment; filename="protokol_'.time().'.zpl"');
                         echo base64_decode((string)$wynik['file']);
                    }

                }

            }
            
            // jezeli generowanie zamowien pdf
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $DlugoscMax   = 0;
                $SzerokoscMax = 0;
                $WysokoscMax  = 0;
                $WagaMax      = 0;
                $WagaPaczek   = 0;
                $IloscPaczek  = 0;
                $Params       = array();

                foreach ( $_POST['opcja'] as $key => $value ) {

                    $zapytanie = "SELECT *
                    FROM orders_shipping
                    WHERE orders_shipping_type = 'DPD' AND orders_shipping_id = '".(int)$value."' AND orders_shipping_misc = '".$fid."'";

                    $sql = $db->open_query($zapytanie);

                    while ($info = $sql->fetch_assoc()) {

                        $Paczki = unserialize($info['orders_shipping_packages']);

                        foreach ( $Paczki as $Paczka ) {

                            if ( $Paczka['SizeX'] > $DlugoscMax ) {
                                $DlugoscMax   = $Paczka['SizeX'];
                            }

                            if ( $Paczka['SizeY'] > $SzerokoscMax ) {
                                $SzerokoscMax = $Paczka['SizeY'];
                            }

                            if ( $Paczka['SizeZ'] > $WysokoscMax ) {
                                $WysokoscMax  = $Paczka['SizeZ'];
                            }

                            if ( $Paczka['Weight'] > $WagaMax ) {
                                $WagaMax  = $Paczka['Weight'];
                            }

                            $WagaPaczek += $Paczka['Weight'];

                        }

                        $IloscPaczek += $info['orders_parcels_quantity'];

                    }
                    $db->close_query($sql);
                    unset($zapytanie, $Paczki);        

                }

                $GodzinyOdbioru = explode('-', (string)$_POST['godziny_odbioru']);

                $Params = array('dlugosc' => $DlugoscMax,
                               'szerokosc' => $SzerokoscMax,
                               'wysokosc' => $WysokoscMax,
                               'wagamax' => $WagaMax,
                               'waga' => $WagaPaczek,
                               'ilosc' => $IloscPaczek,
                               'data' => date('Y-m-d',FunkcjeWlasnePHP::my_strtotime($_POST['data_odbioru'])),
                               'godzinaOd' => $GodzinyOdbioru['0'],
                               'godzinaDo' => $GodzinyOdbioru['1']
                );

                $shipFromDpd["Company"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_NAZWA'];
                $shipFromDpd["Name"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_IMIE_NAZWISKO'];

                $shipFromDpd["Street"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_ULICA'];
                $shipFromDpd["City"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_MIASTO'];
                $shipFromDpd["PostalCode"] = $apiKurier->polaczenie['INTEGRACJA_DPD_NADAWCA_KOD_POCZTOWY'];

                if ( $fid == $apiKurier->polaczenie['INTEGRACJA_DPD_FID'] ) {
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
                $wynik = $apiKurier->getCallPickup($Params);

                if ( $wynik['type'] == 'ok' ) {

                    foreach ( $_POST['opcja'] as $key => $value ) {

                        $pola = array(
                              array('orders_shipping_status', '999'),
                              array('orders_shipping_date_modified','now()')
                        );

                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".$value."'");
                        unset($pola);

                    }

                    $apiKurier->PokazBlad('Sukces', 'Numer zamówienia kuriera : ' . $wynik['message'], 'zamowienia_wysylki_dpd.php?szukaj_status=9999', 'false');

                } else {
                    $apiKurier->PokazBlad('Błąd', $wynik['message']->Error->Code . ' : ' . $wynik['message']->Error->Fields, 'zamowienia_wysylki_dpd.php');
                }
            }

            //Funkcje::PrzekierowanieURL('zamowienia_wysylki_dpd.php');
            return;

        }
     
    }
    
    Funkcje::PrzekierowanieURL('zamowienia_wysylki_dpd.php');
    
}
?>