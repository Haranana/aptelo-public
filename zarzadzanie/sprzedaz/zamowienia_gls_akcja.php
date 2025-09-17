<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {

        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new GlsApi();

            // Utworzenie z przesyłek znajdujących się w przygotowalni potwierdzenia nadania
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $PaczkidoProtokolu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'GLS' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_protocol'] == '' && ( $info['orders_shipping_status'] == '1' || $info['orders_shipping_status'] == '11' ) ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_number'];
                    }
                }
                if ( count($PaczkidoProtokolu) > 0 ) {

                    $wynik = $apiKurier->doAdePickup_Create($PaczkidoProtokolu);

                    $protokolInfo =  $apiKurier->doAdePickup_Get($wynik);

                    $pola = array(
                          array('gls_protocol_number', $wynik),
                          array('gls_protocol_quantity', (int)$protokolInfo->quantity),
                          array('gls_protocol_weight', (float)$protokolInfo->weight),
                          array('gls_protocol_date_added', $protokolInfo->datetime)
                    );

                    $db->insert_query('orders_shipping_gls_protocol' , $pola);
                    unset($pola);


                    $wynikID = $apiKurier->doAdePickup_GetConsignIDs($wynik);

                    if ( is_array($wynikID) ) {

                        $wynikID = array_reverse($wynikID);

                        $i = 0;

                        foreach ( $wynikID as $val ) {

                            $wynikNumer = $apiKurier->doAdePickup_GetConsign($val);

                            $pola = array(
                                  array('orders_shipping_comments', $val),
                                  array('orders_shipping_number', $wynikNumer),
                                  array('orders_shipping_protocol', $wynik),
                                  array('orders_shipping_status', '2'),
                                  array('orders_shipping_date_modified','now()')
                            );
                            $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$PaczkidoProtokolu[$i]."'");
                            $i++;
                            unset($wynikNumer);

                        }

                    } else {

                        $wynikNumer = $apiKurier->doAdePickup_GetConsign($wynikID);

                        $pola = array(
                              array('orders_shipping_comments', $wynikID),
                              array('orders_shipping_number', $wynikNumer),
                              array('orders_shipping_protocol', $wynik),
                              array('orders_shipping_status', '2'),
                              array('orders_shipping_date_modified','now()')
                        );
                        $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$PaczkidoProtokolu['0']."'");
                        unset($wynikNumer);
                    }

                }
                Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');

            }
            
            if ( (int)$_POST['akcja_dolna'] == 2 ) {
                $PaczkidoEtykiet = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);

                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'GLS' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_number'] != '' ) {
                        $listaPaczek = array();
                        $listaPaczek = explode( ',', (string)$info['orders_shipping_number'] );

                        for ( $i = 0, $c = count($listaPaczek); $i < $c; $i++ ) {
                            $PaczkidoEtykiet[] = $listaPaczek[$i];
                        }
                    }
                }

                $wynik = $apiKurier->doAdePickup_GetParcelsLabels($PaczkidoEtykiet);

                if ( base64_encode(base64_decode((string)$wynik, true)) === $wynik) {

                    $formatEtykiety = 'pdf';
                    $format = $apiKurier->polaczenie['INTEGRACJA_GLS_GETLABELS_MODE'];
                    if ( $format == 'roll_160x100_datamax' ) {
                        $formatEtykiety = 'dpl';
                    } elseif ( $format == 'roll_160x100_zebra' ) {
                        $formatEtykiety = 'zpl';
                    } elseif ( $format == 'roll_160x100_zebra_epl' ) {
                        $formatEtykiety = 'epl';
                    }

                    if ( $formatEtykiety == 'pdf' ) {
                        header('Content-type: application/pdf');
                        header('Content-Disposition: attachment; filename="etykiety.pdf"');
                    } else {
                        header('Content-type: application/txt');
                        header('Content-Disposition: attachment; filename="etykiety.'.$formatEtykiety.'"');
                    }
                    echo base64_decode((string)$wynik);

                    foreach ( $_POST['opcja'] as $val ) {
                        $pola = array(
                                array('orders_shipping_status','21'),
                                array('orders_shipping_date_modified','now()')
                        );
                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$val."'");

                    }

                } else {
                    Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
                }

            }

            if ( (int)$_POST['akcja_dolna'] == 10 ) {
              
                $PaczkidoPodjazdu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);

                $NumerProtokolow = '';

                $IloscPaczek = 0;

                $zapytanie = "SELECT *
                              FROM orders_shipping_gls_protocol
                              WHERE gls_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['gls_protocol_quantity'] != '' ) {
                        $IloscPaczek = $IloscPaczek + $info['gls_protocol_quantity'];
                        $NumerProtokolow .= $info['gls_protocol_number'] . '<br />';
                    }
                }

                $wynikNumer = $apiKurier->doadeCourier_Order($IloscPaczek, $_POST['data_odbioru'], $_POST['powiadomienie']);

                if ( $wynikNumer ) {
                  
                    foreach ( $_POST['opcja'] as $val ) {
                        
                        $pola = array(
                                      array('gls_protocol_date_order', $_POST['data_odbioru'])
                        );
                        $db->update_query('orders_shipping_gls_protocol' , $pola, " gls_id = '".$val."'");
                    }

                    Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls_protocol.php');
                    
                }

            }


        } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
            return;
        }
     
    } else {
        Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
    }
}
?>