<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new BliskapaczkaApi();

            // Utworzenie pliku pdf zawierającego etykiety dla dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $PaczkidoProtokolu = array();

                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'BLISKAPACZKA' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_number'] != '' ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_number'];
                    }
                }

            }
     
            // Utworzenie zawierającego potwierdzenia nadania dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 2 ) {

                $PaczkidoProtokolu =  array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'BLISKAPACZKA' AND orders_shipping_id IN (".$idPaczek.") AND orders_shipping_status = 'READY_TO_SEND'";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_number'] != '' ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_number'];
                    }
                }

                $PaczkiDoZamowienia = implode(',', (array)$PaczkidoProtokolu);

                $Wynik = $apiKurier->commandGetProtokol('v2/report/pickupconfirmation?numbers='.$PaczkiDoZamowienia);

                if(  isset($Wynik) && !is_array($Wynik) ) {

                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="protokol_'.$_GET['przesylka'].'.pdf"');
                    echo $Wynik;

                }

                Funkcje::PrzekierowanieURL('zamowienia_wysylki_bliskapaczka.php');

                return;

            }

            // Zamowienie kuriera
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $PaczkidoOdbioru = array();
                $DataOdbioru = array();
                $DataOdbioru = explode('|', (string)$_POST['odbior']);
                $Komunikat = '';

                $Zamowienie = array();
                $Zamowienie['orderNumbers'] = $_POST['paczki'];
                $Zamowienie['pickupWindow']['date'] = ( isset($DataOdbioru['0']) ? $DataOdbioru['0'] : '' );
                $Zamowienie['pickupWindow']['timeRange']['from'] = ( isset($DataOdbioru['1']) ? $DataOdbioru['1'] : '' );
                $Zamowienie['pickupWindow']['timeRange']['to'] = ( isset($DataOdbioru['2']) ? $DataOdbioru['2'] : '' );

                $Zamowienie['pickupAddress']['street'] = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_ULICA'];
                $Zamowienie['pickupAddress']['buildingNumber'] = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_DOM'];
                $Zamowienie['pickupAddress']['flatNumber'] = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIESZKANIE'];
                $Zamowienie['pickupAddress']['city'] = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIASTO'];
                $Zamowienie['pickupAddress']['postCode'] = $apiKurier->polaczenie['INTEGRACJA_BLISKAPACZKA_NADAWCA_KOD_POCZTOWY'];

                $Zamowienie['firstAvailable'] = false;

                $Wynik = $apiKurier->commandPost('v2/order/pickup', $Zamowienie);    

                if ( isset($Wynik->errors) ) {

                    foreach ( $Wynik->errors as $Blad ) {
                        $Komunikat .= $Blad->field . ' : ' . $Blad->value . '<br />';
                    }

                } else {

                    foreach($_POST['paczki'] as $key => $value) {
                      $pola = array();
                      $pola = array(
                                  array('orders_shipping_protocol',(string)$Wynik->number),
                              );

                      $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$value."'");
                      unset($pola);
                    }
                }

                Funkcje::PrzekierowanieURL('zamowienia_wysylki_bliskapaczka.php');

            }

        }
    
    }

    Funkcje::PrzekierowanieURL('zamowienia_wysylki_bliskapaczka.php');
    
}
?>