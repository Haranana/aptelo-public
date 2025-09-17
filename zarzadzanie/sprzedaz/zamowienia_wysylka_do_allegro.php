<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $blad = true;

    $zapytanie = "SELECT * FROM allegro_transactions WHERE orders_id = '".$_GET['id_poz']."'";
    $sql = $db->open_query($zapytanie);

    if ((int)$db->ile_rekordow($sql) > 0) {

        $blad = false;

        while ($info = $sql->fetch_assoc()) {

            $AllegroRest = new AllegroRest( array('allegro_user' => $info['auction_seller']) );
            $PrzetwarzaneZamowienie = $AllegroRest->commandRequest('order/checkout-forms', $info['transaction_id'], '' );

        }

    }
    
    
    if ( !$blad ) {

        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

        $Kurier = $zamowienie->dostawy[$_GET['przesylka']]['rodzaj_przesylki'];

        if ( $zamowienie->dostawy[$_GET['przesylka']]['rodzaj_przesylki'] == 'FURGONETKA' || $zamowienie->dostawy[$_GET['przesylka']]['rodzaj_przesylki'] == 'BLISKAPACZKA' ) {
            $Kurier = $zamowienie->dostawy[$_GET['przesylka']]['komentarz'];
        }
        if ( $zamowienie->dostawy[$_GET['przesylka']]['rodzaj_przesylki'] == 'APACZKA' ) {
            $Kurier = $zamowienie->dostawy[$_GET['przesylka']]['komentarz'];
        }

        $FirmaKurierska = $AllegroRest->TablicaKurierow($Kurier);

        $Wysylka = new stdClass();
        $Wysylka->carrierId = $FirmaKurierska;
        $Wysylka->waybill = $zamowienie->dostawy[$_GET['przesylka']]['numer_przesylki'];
        if ( $FirmaKurierska == 'OTHER' ) {
            $Wysylka->carrierName = $zamowienie->dostawy[$_GET['przesylka']]['rodzaj_przesylki'];
        } else {
            $Wysylka->carrierName = "";
        }
        $Wysylka->lineItems = Array();

        if ( !isset($PrzetwarzaneZamowienie->errors) ) {

            if ( isset($PrzetwarzaneZamowienie->lineItems) && count($PrzetwarzaneZamowienie->lineItems) > 0 ) {
                foreach ($PrzetwarzaneZamowienie->lineItems as $Produkt ) {
                    if ( isset($Produkt->id) && $Produkt->id != '' ) {
                        $TransakcjaId = new stdClass();
                        $TransakcjaId->id = $Produkt->id;
                        $Wysylka->lineItems[] = $TransakcjaId;
                    }
                }
            }

            $wynik = $AllegroRest->commandPost('order/checkout-forms/'.$PrzetwarzaneZamowienie->id.'/shipments', $Wysylka);

            if ( isset($wynik->errors) ) {
                echo $AllegroRest->PokazBlad('Błąd', $wynik->errors[0]->message, 'zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"], 'true');
                //Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);

            } else {

                $pola = array(
                        array('orders_shipping_allegro', '1')
                );
                $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$_GET['przesylka']."'");
                echo $AllegroRest->PokazBlad('Dane zostaly przeslane', $wynik->createdAt, 'zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"], 'true');
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
            }
        } else {
            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
            echo $AllegroRest->PokazBlad('Błąd', $PrzetwarzaneZamowienie->errors[0]->message, 'zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"], 'true');
        }

    } else {

        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
        echo $AllegroRest->PokazBlad('Błąd', 'Nie znaleziono tranzakcji do tego zamówienia', 'zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"], 'true');

    }


}

?>