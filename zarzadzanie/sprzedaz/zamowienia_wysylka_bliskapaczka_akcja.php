<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['id_poz']) ) {
        $IDPoz = $_GET['id_poz'];
        $Zakladka = ((isset($_GET['zakladka'])) ? $_GET['zakladka'] : '');
        $apiKurier       = new BliskapaczkaApi($IDPoz, $Zakladka);
    } else {
        $apiKurier       = new BliskapaczkaApi();
    }


    switch ( $_GET['akcja'] ) {

        case 'status':

            $Wynik = $apiKurier->commandGet('v2/order/'.$_GET['przesylka']);

            $Statusy = '';
            $StatusyTablica = $apiKurier->bliskapaczka_status_array();
            $LinkSledzenia = '';

            foreach($Wynik->changes as $Status ) {

                $Statusy .= date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Status->dateTime)) . ' - ' . ( isset($StatusyTablica[$Status->status]) ? $StatusyTablica[$Status->status] : $Status->status) . ( $Status->reason != '' ? ' ('.$Status->reason.')' : '' ) . '<br />';

            }

            if ( isset($Wynik) && $Wynik->changes ) {

                if ( $Wynik->trackingNumber != '' ) {
                    $LinkSledzenia = Funkcje::LinkSledzeniaWysylki($Wynik->operatorName, $Wynik->trackingNumber);
                }

                $AktualnyStatus = end($Wynik->changes);

                $pola = array();
                $pola = array(
                              array('orders_shipping_status',$AktualnyStatus->status),
                              array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($AktualnyStatus->dateTime))),
                              array('orders_shipping_misc',$Wynik->trackingNumber),
                              array('orders_shipping_link',$LinkSledzenia)
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET['przesylka']."'");
                unset($pola);


            }


            $apiKurier->PokazBlad('Statusy przesyłki', $Statusy, 'zamowienia_szczegoly.php?id_poz='.$_GET['id_poz'].'&zakladka=1', 'true');

        break;

        case 'usun':
            $Wynik = $apiKurier->commandPost('v2/order/'.$_GET['przesylka'].'/cancel', '');

            if ( isset($Wynik) && $Wynik->changes ) {

                $AktualnyStatus = end($Wynik->changes);

                $pola = array();
                $pola = array(
                              array('orders_shipping_status',$AktualnyStatus->status),
                              array('orders_shipping_protocol',''),
                              array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($AktualnyStatus->dateTime)))
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET['przesylka']."'");
                unset($pola);

                $apiKurier->PokazBlad('Sukces', $AktualnyStatus->status, 'zamowienia_szczegoly.php?id_poz='.$_GET['id_poz'].'&zakladka=1', 'true');

            } else {
                if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                    Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
                } else {
                    Funkcje::PrzekierowanieURL('zamowienia_wysylki_bliskapaczka.php');
                }
            }
        break;

        //Wydruk etykiety
        case 'etykieta':

            $Wynik = $apiKurier->commandGet('v2/order/'.$_GET['przesylka'].'/waybill');

            if(  isset($Wynik) && $Wynik['0']->url != '' ) {

                $AdresEtykiety = $Wynik['0']->url;

                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                readfile($AdresEtykiety);

            }

        break;

        //Wydruk potwierdzenia
        case 'potwierdzenie':

            $Wynik = $apiKurier->commandGetProtokol('v2/report/pickupconfirmation?numbers='.$_GET['przesylka']);

            if(  isset($Wynik) && !is_array($Wynik) ) {

                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="protokol_'.$_GET['przesylka'].'.pdf"');
                echo $Wynik;

            }

        break;

        //Zamowienie kuriera
        case 'zamowienie':

            $Wynik = $apiKurier->commandGet('v2/order/pickup?orderNumbers='.$_GET['przesylka']);

            echo '<pre>';
            echo print_r($Wynik);
            echo '</pre>';

        break;

        case 'ponow':

            $Wynik = $apiKurier->commandPost('v2/order/'.$_GET['przesylka'].'/retry', '');

            echo '<pre>';
            echo print_r($Wynik);
            echo '</pre>';

        break;
    }
}


?>