<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $apiKurier = new ApaczkaApiV2();

    switch ( $_GET['akcja'] ) {

        //Wydruk naklejki na przesylke
        case 'etykieta':

            $wynik = $apiKurier->waybill($_GET["przesylka"]);
            $Wynik = json_decode($wynik);

            if ( isset($Wynik) && $Wynik->status == '200' ) {

                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                echo base64_decode((string)$Wynik->response->waybill);

            }

        break;

        //Wydruk protokolu
        case 'potwierdzenie':

            $WysylkiTablica = array($_GET["przesylka"]);
            $wynik = $apiKurier->turn_in($WysylkiTablica);
            $Wynik = json_decode($wynik);

            if ( isset($Wynik) && $Wynik->status == '200' ) {

                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="potwierdzenie_'.$_GET['przesylka'].'.pdf"');
                echo base64_decode((string)$Wynik->response->turn_in);

            }

        break;

        //Usuniecie przesylki
        case 'usun':

          $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_misc = '".$filtr->process($_GET["przesylka"])."'");

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_apaczka.php');
          }

        break;

        //Anulowanie zamowienia
        case 'anuluj':

        $wynik = $apiKurier->cancel_order($_GET["przesylka"]);
        $Wynik = json_decode($wynik);

        if ( isset($Wynik) && $Wynik->status == '200' ) {

            $wynikZam = $apiKurier->order($_GET["przesylka"]);
            $WynikZam = json_decode($wynikZam);
            if ( isset($WynikZam) && $WynikZam->status == '200' ) {

                $pola = array(
                        array('orders_shipping_status', $WynikZam->response->order->status),
                        array('orders_shipping_date_modified','now()'),
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$filtr->process($_GET['przesylka'])."'");
            }

            if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
            } else {
                Funkcje::PrzekierowanieURL('zamowienia_wysylki_dpd.php');
            }

        }

        break;
    }
}


?>