<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $apiKurier = new DhlApi((int)$_GET['id_poz']);

    switch ( $_GET['akcja']) {

        //Usuniecie przesylki z DHL
        case 'usun':

            $Wynik = $apiKurier->removeAction($_GET['przesylka']);

            if ( $Wynik != false ) {
                if ( isset($Wynik->deleteShipmentsResult) && $Wynik->deleteShipmentsResult->item->error == '' ) {
                    $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_number = '".$_GET['przesylka']."'");
                    include('naglowek.inc.php');
                    echo Okienka::pokazOkno('Wynik', 'Przesyłka '.$_GET['przesylka'].' została usunięta', 'sprzedaz/zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
                    include('stopka.inc.php');
                } else {
                    include('naglowek.inc.php');
                    if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                        echo Okienka::pokazOkno('Wynik', $Wynik->deleteShipmentsResult->item->error, 'sprzedaz/zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
                    } else {
                        echo Okienka::pokazOkno('Wynik', $Wynik->deleteShipmentsResult->item->error, 'sprzedaz/zamowienia_wysylki_dhl.php');
                    }
                    include('stopka.inc.php');
                }
            }

            if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
            } else {
                Funkcje::PrzekierowanieURL('sprzedaz/zamowienia_wysylki_dhl.php');
            }


            break;

        case 'etykieta':

            $ZmienStatus = true;
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);

            foreach ( $zamowienie->dostawy as $dostawa ) {
                if ( $dostawa['numer_przesylki'] == $filtr->process($_GET["przesylka"]) ) {
                    if ( $dostawa['status_przesylki'] == 'Kurier zamówiony' ) {
                        $ZmienStatus = false;
                    }
                }
            }

            $Wynik = $apiKurier->labelAction($_GET['przesylka']);

            if ( $Wynik != false ) {
                if ( isset($Wynik->getLabelsResult) && $Wynik->getLabelsResult->item->labelName != '' ) {

                    if ( $ZmienStatus ) {
                        $pola = array(
                                array('orders_shipping_status', 'Etykieta wydrukowana'),
                                array('orders_shipping_date_modified','now()'),
                        );

                        $db->update_query('orders_shipping' , $pola, " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_number = '".$filtr->process($_GET["przesylka"])."'");
                    }

                    if ( $apiKurier->polaczenie['INTEGRACJA_DHL_WYDRUK_FORMAT'] == 'BLP' ) {
                        header('Content-type: application/pdf');
                        header('Content-Disposition: attachment; filename="'.$Wynik->getLabelsResult->item->labelName.'');
                        echo base64_decode((string)$Wynik->getLabelsResult->item->labelData);
                    } else {
                        header('Content-type: application/txt');
                        header('Content-Disposition: attachment; filename="'.$Wynik->getLabelsResult->item->labelName.'');
                        echo base64_decode((string)$Wynik->getLabelsResult->item->labelData);
                    }

                }
            }

            break;

        //Usuniecie przesylki z bazy sklepu
        case 'drop':

          $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_number = '".$filtr->process($_GET["przesylka"])."'");

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_dhl.php');
          }

          break;

        case 'tracking':

              $Wynik = $apiKurier->trackingAction($_GET['przesylka']);

              $Informacja = '';

              if ( $Wynik != false ) {

                  if ( isset($Wynik->getTrackAndTraceInfoResult) && (array)$Wynik->getTrackAndTraceInfoResult->events ) {

                      foreach ( $Wynik->getTrackAndTraceInfoResult->events as $Rekord ) {
                          $Informacja .= $Rekord->status . '<br>';
                          $Informacja .= $Rekord->description . ' ' . date("Y-m-d H:i", FunkcjeWlasnePHP::my_strtotime($Rekord->timestamp)) . '<br>';
                          $Informacja .= '<hr style=\"border-top: 1px dotted #000;\">';

                      }

                  } else {

                    $Informacja = 'Brak danych do wyświetlenia';

                  }
              }

              include('naglowek.inc.php');
              if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
                  echo Okienka::pokazOkno('Historia', $Informacja, 'sprzedaz/zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
              } else {
                  echo Okienka::pokazOkno('Historia', $Informacja, 'sprzedaz/zamowienia_wysylki_dhl.php');
              }
              include('stopka.inc.php');

              break;

  }
  
}

?>