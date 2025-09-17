<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            $apiKurier       = new PaczkaRuchApi();

            // jezeli wydrukowanie protokolu
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $PaczkidoProtokolu = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'PACZKA W RUCHU' OR orders_shipping_type = 'ORLEN PACZKA' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_status'] != '201' ) {
                        $PaczkidoProtokolu[] = floatval($info['orders_shipping_number']);
                    }
                }

                $plikProtokolu = $apiKurier->doGenerateProtocol($PaczkidoProtokolu);

                $xml = simplexml_load_string($plikProtokolu->GenerateProtocolResult->any);

                $PdfDruk = false;

                foreach ( $xml->NewDataSet->Table as $Paczka ) {

                   if ( $Paczka->Err == '0' ) {
                       $PdfDruk = true;
                       $pola = array(
                               array('orders_shipping_status',$Paczka->status),
                               array('orders_shipping_date_modified', date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($Paczka->DATA_MOD)))
                       );

                       $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$Paczka->PackCodeRUCH."'");
                       unset($pola);

                    }

                }

                if ( $PdfDruk ) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="prot'.time().'.pdf"');

                    echo $plikProtokolu->LabelData;
                }

            }
            
            // jezeli usuniecie wpisow z bazy
            if ( (int)$_POST['akcja_dolna'] == 3 ) {

                $PaczkidoUsuniecia = array();
                $idPaczek = implode(',', (array)$_POST['opcja']);
                $zapytanie = "SELECT *
                              FROM orders_shipping
                              WHERE orders_shipping_type = 'PACZKA W RUCHU' OR orders_shipping_type = 'ORLEN PACZKA' AND orders_shipping_id IN (".$idPaczek.")";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    if ( $info['orders_shipping_status'] == '201' ) {
                        $db->delete_query('orders_shipping' , " orders_shipping_number = '".$info["orders_shipping_number"]."'");  
                    }
                }
                Funkcje::PrzekierowanieURL('zamowienia_wysylki_ruch.php');
            }
            return;

        }
     
    }
    
    Funkcje::PrzekierowanieURL('zamowienia_wysylki_ruch.php');
    
}
?>