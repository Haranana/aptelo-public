<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['id_poz']) ) {
        $apiKurier       = new GlsApi((int)$_GET['id_poz']);
    } else {
        $apiKurier       = new GlsApi();
    }

    switch ( $_GET['akcja'] ) {

        //Wydruk naklejki na przesylke w przygotowalni
        case 'etykieta':

            //get label by reference number
            $wynik = $apiKurier->doAdePreparingBox_GetConsignLabels($_GET['przesylka']);

            if ( base64_encode(base64_decode((string)$wynik, true)) === $wynik){

                $pola = array(
                        array('orders_shipping_status','11'),
                        array('orders_shipping_date_modified','now()')
                );

                $formatEtykiety = 'pdf';
                $format = $apiKurier->polaczenie['INTEGRACJA_GLS_GETLABELS_MODE'];
                if ( $format == 'roll_160x100_datamax' ) {
                    $formatEtykiety = 'dpl';
                } elseif ( $format == 'roll_160x100_zebra' ) {
                    $formatEtykiety = 'zpl';
                } elseif ( $format == 'roll_160x100_zebra_epl' ) {
                    $formatEtykiety = 'epl';
                }

                $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."'");

                if ( $formatEtykiety == 'pdf' ) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                } else {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.'.$formatEtykiety.'"');
                }
                echo base64_decode((string)$wynik);

            } else {

                $pola = array(
                        array('orders_shipping_status','9999')
                );
                $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."'");
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka=1');
            }

            break;

        //Wydruk naklejki na przesylke na podstawie numeru paczki
        case 'etykietaNumer':

            $idPaczek = explode(',', (string)$_GET['przesylka']);

            //get label by reference number
            if ( isset($idPaczek) && count($idPaczek) > 1 ) {
                $wynik = $apiKurier->doAdePickup_GetParcelsLabels($idPaczek);
            } else {
                $wynik = $apiKurier->doAdePickup_GetParcelLabel($_GET['przesylka']);
            }
            $pola = array(
                    array('orders_shipping_status','21'),
                    array('orders_shipping_date_modified','now()')
            );

            if ( base64_encode(base64_decode((string)$wynik, true)) === $wynik){

                $formatEtykiety = 'pdf';
                $format = $apiKurier->polaczenie['INTEGRACJA_GLS_GETLABELS_MODE'];
                if ( $format == 'roll_160x100_datamax' ) {
                    $formatEtykiety = 'dpl';
                } elseif ( $format == 'roll_160x100_zebra' ) {
                    $formatEtykiety = 'zpl';
                } elseif ( $format == 'roll_160x100_zebra_epl' ) {
                    $formatEtykiety = 'epl';
                }

                $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$filtr->process($_GET['przesylka'])."'");

                if ( $formatEtykiety == 'pdf' ) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['id_poz'].'.pdf"');
                } else {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['id_poz'].'.'.$formatEtykiety.'"');
                }
                echo base64_decode((string)$wynik);

            } else {
               Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
            }

            break;

        //Pobranie z systemu etykiet pojedynczej przesyłki z dowolnego potwierdzenia nadania
        case 'etykietaID':

            //get label by reference number
            $wynik = $apiKurier->doAdePickup_GetConsignLabels($_GET['przesylka']);

            $pola = array(
                    array('orders_shipping_date_modified','now()')
            );

            if ( base64_encode(base64_decode((string)$wynik, true)) === $wynik){

                $formatEtykiety = 'pdf';
                $format = $apiKurier->polaczenie['INTEGRACJA_GLS_GETLABELS_MODE'];
                if ( $format == 'roll_160x100_datamax' ) {
                    $formatEtykiety = 'dpl';
                } elseif ( $format == 'roll_160x100_zebra' ) {
                    $formatEtykiety = 'zpl';
                } elseif ( $format == 'roll_160x100_zebra_epl' ) {
                    $formatEtykiety = 'epl';
                }

                $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."'");

                if ( $formatEtykiety == 'pdf' ) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                } else {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.'.$formatEtykiety.'"');
                }
                echo base64_decode((string)$wynik);

            } else {
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
            }

            break;

        //Usuniecie przesylki z przygotowalni
        case 'usun':

          $wynik = $apiKurier->doAdePreparingBox_DeleteConsign($_GET['przesylka']);

          if ( $wynik == $_GET['przesylka'] ) {

            $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_comments = '".$filtr->process($_GET["przesylka"])."'");

          } else {

                $pola = array(
                        array('orders_shipping_status','9999')
                );
                $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."'");
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka=1');
          }

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka=1');
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
          }

          break;

        //Usuniecie przesylki z bazy danych
        case 'usunbaza':

          $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_comments = '".$filtr->process($_GET["przesylka"])."'");

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka=1');
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
          }

          break;

        //stworzenie potwierdzenia nadania
        case 'potwierdzenie':

          // stworzenie potwierdzenia nadania z wybranych z przygotowalni przesyłek
          $wynik = $apiKurier->doAdePickup_Create($_GET['przesylka']);

          if ( $wynik ) {
              $protokolInfo =  $apiKurier->doAdePickup_Get($wynik);

              $pola = array(
                      array('gls_protocol_number', $wynik),
                      array('gls_protocol_quantity', $protokolInfo->quantity),
                      array('gls_protocol_weight', $protokolInfo->weight),
                      array('gls_protocol_date_added', $protokolInfo->datetime)
              );

              $db->insert_query('orders_shipping_gls_protocol' , $pola);
              unset($pola);

              // nowy identyfikator przesyłki z potwierdzenia nadania o wskazanym identyfikatorze
              $wynikID = $apiKurier->doAdePickup_GetConsignIDs($wynik);

              // numer przesylki
              $wynikNumer = $apiKurier->doAdePickup_GetConsign($wynikID);

              $pola = array(
                      array('orders_shipping_comments', $wynikID),
                      array('orders_shipping_number', $wynikNumer),
                      array('orders_shipping_protocol', $wynik),
                      array('orders_shipping_status', '2'),
                      array('orders_shipping_date_modified','now()')
              );

              $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."'");

          } else {

              $pola = array(
                      array('orders_shipping_status','9999')
              );
              $db->update_query('orders_shipping' , $pola, " orders_shipping_comments = '".$filtr->process($_GET['przesylka'])."'");
              Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka=1');

          }

          if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka=1');
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
          }

          break;

        //Wydruk naklejki na przesylke w przygotowalni
        case 'potwierdzenieDruk':

            //get label by reference number
            $wynik = $apiKurier->doAdePickup_GetReceipt($_GET['przesylka']);

            if ( base64_encode(base64_decode((string)$wynik, true)) === $wynik){

                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                echo base64_decode((string)$wynik);

            } else {
                //Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
            }

            break;

        //Pobranie z systemu etykiet wszystkich przesyłek z pojedynczego potwierdzenia nadania
        case 'etykietaMulti':

            //get label by reference number
            $wynik = $apiKurier->doAdePickup_GetLabels($_GET['przesylka']);

            if ( base64_encode(base64_decode((string)$wynik, true)) === $wynik){

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
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
                } else {
                    header('Content-type: application/txt');
                    header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.'.$formatEtykiety.'"');
                }
                echo base64_decode((string)$wynik);

            } else {
                //Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
            }

            break;

        //pobranie identyfikatorow potwierdzen
        case 'potwierdzenieIDS':

          $wynik = $apiKurier->doAdePickup_GetIDs($_GET['przesylka']);

          //if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
          //  Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
          //} else {
          //  Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
          //}

          break;

        //pobranie szczegolowych informacji o przesylce
        case 'przesylkaInfo':

          $wynik = $apiKurier->doAdePickup_GetConsign($_GET['przesylka']);

          //if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
          //  Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
          //} else {
          //  Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
          //}

          break;

        //pobiera infromacje na temat potwierdzenia nadania
        case 'protokolInfo':

          $wynik = $apiKurier->doAdePickup_Get($_GET['przesylka']);

          echo '<pre>';
          echo print_r($wynik);
          echo '</pre>';
          //if ( isset($_GET["zakladka"]) && $_GET["zakladka"] == '1' ) {
          //  Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
          //} else {
          //  Funkcje::PrzekierowanieURL('zamowienia_wysylki_gls.php');
          //}

          break;

    }
}


?>