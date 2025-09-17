<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['id_poz']) ) {
        $apiKurier       = new DpdApi((int)$_GET['id_poz']);
    } else {
        $apiKurier       = new DpdApi();
    }

    switch ( $_GET['akcja'] ) {

        //Wydruk naklejki na przesylke
        case 'etykieta':

            //get label by reference number
            $przesylki = array();
            $przesylki = array($_GET['przesylka']);
            $kraj = $_GET['destination'];

            $wynik = $apiKurier->getLabelPDF(1, $przesylki, $kraj);

            if( is_array($wynik) && $wynik['type'] == 'ok' && $wynik['file'] != '' ) {

                $pola = array(
                      array('orders_shipping_status', '2'),
                      array('orders_shipping_date_modified','now()'),
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."' AND orders_shipping_status != '999'");

                if ( $apiKurier->polaczenie['INTEGRACJA_DPD_FORMAT_PLIKU'] == 'PDF' ) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                    echo base64_decode((string)$wynik['file']);
                } else {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.zpl"');
                    echo base64_decode((string)$wynik['file']);
                }

            }

        break;

        //Wydruk protokolu
        case 'protokol':

            //get label by reference number
            $wynik = $apiKurier->getProtocol(array($_GET['przesylka']),$_GET['fid']);


            if( is_array($wynik) && $wynik['type'] == 'ok' && $wynik['file'] != '' ) {

                $pola = array(
                      array('orders_shipping_status', '3'),
                      array('orders_shipping_date_modified','now()'),
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."' AND orders_shipping_status != '999'");

                if ( $apiKurier->polaczenie['INTEGRACJA_DPD_FORMAT_PLIKU'] == 'PDF' ) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="protokol_'.$_GET['przesylka'].'.pdf"');
                    echo base64_decode((string)$wynik['file']);
                } else {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="protokol_'.$_GET['przesylka'].'.zpl"');
                    echo base64_decode((string)$wynik['file']);
                }

            }

        break;

        //Usuniecie przesylki
        case 'usun':

          $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_comments = '".$filtr->process($_GET["przesylka"])."'");

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_dpd.php');
          }



        break;

    }
}


?>