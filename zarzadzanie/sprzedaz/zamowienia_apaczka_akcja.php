<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new ApaczkaApiV2();

            // Utworzenie zawierającego potwierdzenia nadania dowolnej liczby zleceń
            if ( (int)$_POST['akcja_dolna'] == 2 ) {

                $PaczkidoProtokolu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'APACZKA' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_misc'] != '' ) {
                        $PaczkidoProtokolu[] = $info['orders_shipping_misc'];
                    }
                }

                $Wynik = $apiKurier->turn_in($PaczkidoProtokolu);
                $Wynik = json_decode($Wynik);

                if ( $Wynik !== false ) {

                    if ( isset($Wynik) && $Wynik->status == '200' ) {

                        header('Content-type: application/pdf');
                        header('Content-Disposition: attachment; filename="etykiety.pdf');
                        echo base64_decode((string)$Wynik->response->turn_in);

                    }

                    //Funkcje::PrzekierowanieURL('zamowienia_wysylki_apaczka.php');
                    return;

                } else {

                    //Funkcje::PrzekierowanieURL('zamowienia_wysylki_apaczka.php');
                    return;
                }

            }
     
            // jezeli usuniecie wpisow z bazy
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $PaczkidoUsuniecia = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type LIKE '%APACZKA%' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                     $db->delete_query('orders_shipping' , " orders_shipping_id = '".$info["orders_shipping_id"]."'");  
                }

                Funkcje::PrzekierowanieURL('zamowienia_wysylki_apaczka.php');
            }
        }
    
    }

    //Funkcje::PrzekierowanieURL('zamowienia_wysylki_apaczka.php');
    
}
?>