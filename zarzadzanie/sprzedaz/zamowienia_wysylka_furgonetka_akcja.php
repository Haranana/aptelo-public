<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $IDPoz = ( isset($_GET['id_poz']) ? (int)$_GET['id_poz'] : '' );
  $Zakladka = ( isset($_GET['zakladka']) ? (int)$_GET['zakladka'] : '' );
  $apiKurier       = new FurgonetkaRestApi(true, $IDPoz, $Zakladka);

  switch ( $_GET['akcja']) {

    case 'zamow':

      $UUID = $apiKurier->UUIDv4();
      $params = new stdClass;
      $Parcel = new stdClass;

      $Parcel->id = $_GET['przesylka'];
      $params->packages[] = $Parcel;

      $params->label = new stdClass;
      if ( $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT'] == 'pdf' ) {
        $params->label->page_format = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYDRUK_TYP'];
      }
      $params->label->file_format = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT'];

      $Wynik = $apiKurier->commandPut('order-commands/'.$UUID, $params);

      if ( $Wynik !== false ) {
        $pola = array(
                array('orders_shipping_uuid_order',$Wynik->uuid)
        );
        if ( $Zakladka != '' ) {
            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");
        } else {
            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."' AND orders_shipping_id = '".(int)$_GET["id_poz"]."'");
        }

        unset($pola);

        include('naglowek.inc.php');
        if ( $Zakladka != '' ) {
            echo Okienka::pokazOkno('Wynik', 'Operacja dodana do kolejki : ' . $Wynik->uuid, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y')));
        } else {
            echo Okienka::pokazOkno('Wynik', 'Operacja dodana do kolejki : ' . $Wynik->uuid, 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
        }
        include('stopka.inc.php');
      }

      break;

    case 'StatusZamowienia':

      $Wynik = $apiKurier->commandGet('order-commands/'.$_GET['uuid'], true, '', false);

      if ( $Wynik !== false ) {

          if ( isset($Wynik->status) && $Wynik->status == 'successful' ) {

                $pola = array(
                        array('orders_shipping_uuid_order',''),
                        array('orders_shipping_date_modified', date("Y-m-d H:i:s", FunkcjeWlasnePHP::my_strtotime($Wynik->datetime_change)))
                );
                if ( $Zakladka != '' ) {
                    $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");
                } else {
                    $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_shipping_id = '".(int)$_GET["id_poz"]."'");
                }
                unset($pola);

                if ( $Zakladka != '' ) {
                    Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
                } else {
                    Funkcje::PrzekierowanieURL('zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
                }

          } else {

            $Komunikat = '';
            $Bledy = '';
            $Komunikat .= 'Status : ' . $Wynik->status . '<br />';
            foreach ( $Wynik->errors as $Blad ) {
                $Bledy .= $Blad->path . ' : ' . $Blad->message . '<br />';
            }
            $Komunikat .= $Bledy;

            include('naglowek.inc.php');

            if ( $Zakladka != '' ) {
                echo Okienka::pokazOkno('Wynik', $Komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y')));
            } else {
                echo Okienka::pokazOkno('Wynik', $Komunikat, 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
            }
            include('stopka.inc.php');

          }
      }

      break;

    case 'anuluj':

      $UUID = $apiKurier->UUIDv4();
      $params = new stdClass;
      $params->packages = array();

      $Parcel = new stdClass;
      $Parcel->id = $_GET['przesylka'];

      $params->packages[] = $Parcel;

      $Wynik = $apiKurier->commandPut('cancel-command/'.$UUID, $params);

      if ( $Wynik !== false ) {
          if ( isset($Wynik->uuid) && $Wynik->uuid != '' ) {

              $pola = array(
                      array('orders_shipping_uuid_cancel',$Wynik->uuid)
              );

              if ( $Zakladka != '' ) {
                  $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");
              } else {
                  $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_shipping_id = '".(int)$_GET["id_poz"]."'");
              }

              unset($pola);

                include('naglowek.inc.php');
              if ( $Zakladka != '' ) {
                  echo Okienka::pokazOkno('Wynik', 'Operacja dodana do kolejki', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y')));
              } else {
                  echo Okienka::pokazOkno('Wynik', 'Operacja dodana do kolejki', 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
              }
          }
      }

      break;

    case 'usun':

      $Wynik = $apiKurier->commandDelete('packages/'.$_GET["przesylka"], '');

      if ( $Wynik !== false ) {

          if ( $Zakladka != '' ) {
            $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_misc = '".$filtr->process($_GET["przesylka"])."'");
          } else {
            $db->delete_query('orders_shipping' , " orders_shipping_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_misc = '".$filtr->process($_GET["przesylka"])."'");
          }
          include('naglowek.inc.php');

          if ( $Zakladka != '' ) {
            echo Okienka::pokazOkno('Wynik', 'Przesyłka została usunięta', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          } else {
            echo Okienka::pokazOkno('Wynik', 'Przesyłka została usunięta', 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]); 
          }
          include('stopka.inc.php');
      }

      break;

    case 'StatusAnulowania':

      $Wynik = $apiKurier->commandGet('cancel-command/'.$_GET['uuid'], true, '', false);

      if ( $Wynik !== false ) {

          if ( isset($Wynik->status) && $Wynik->status == 'successful' ) {

                $pola = array(
                        array('orders_shipping_status','cancelled'),
                        array('orders_shipping_link',''),
                        array('orders_shipping_uuid_cancel',''),
                        array('orders_shipping_protocol',''),
                        array('orders_shipping_date_modified', date("Y-m-d H:i:s", FunkcjeWlasnePHP::my_strtotime($Wynik->datetime_change)))
                );

                if ( $Zakladka != '' ) {
                    $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");
                } else {
                    $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_shipping_id = '".(int)$_GET["id_poz"]."'");
                }

                unset($pola);
                if ( $Zakladka != '' ) {
                    Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
                } else {
                    Funkcje::PrzekierowanieURL('zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
                }

          } else {

            $Komunikat = '';
            $Bledy = '';
            $Komunikat .= 'Status : ' . $Wynik->status . '<br />';
            foreach ( $Wynik->errors as $Blad ) {
                $Bledy .= $Blad->path . ' : ' . $Blad->message . '<br />';
            }
            $Komunikat .= $Bledy;
            $pola = array(
                    array('orders_shipping_date_modified', date("Y-m-d H:i:s", FunkcjeWlasnePHP::my_strtotime($Wynik->datetime_change)))
            );

            if ( $Zakladka != '' ) {
                $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");
            } else {
                $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$_GET["przesylka"]."' AND orders_shipping_id = '".(int)$_GET["id_poz"]."'");
            }
            unset($pola);

            include('naglowek.inc.php');
            if ( $Zakladka != '' ) {
                echo Okienka::pokazOkno('Wynik', $Komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y')));
            } else {
                echo Okienka::pokazOkno('Wynik', $Komunikat, 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
            }
            include('stopka.inc.php');

          }

      }

      break;

    case 'usunBaza':

          if ( $Zakladka != '' ) {
            $db->delete_query('orders_shipping' , " orders_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_misc = '".$filtr->process($_GET["przesylka"])."'");
          } else {
            $db->delete_query('orders_shipping' , " orders_shipping_id = '".(int)$_GET["id_poz"]."' AND orders_shipping_misc = '".$filtr->process($_GET["przesylka"])."'");
          }

          if ( $Zakladka != '' ) {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.(int)$_GET["zakladka"]);
          } else {
            Funkcje::PrzekierowanieURL('zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
          }

    case 'etykieta':

      $blad = '';
      
      $Wynik = $apiKurier->commandGetWydruk('packages/'.$_GET['przesylka'].'/label', true, '');
      if ( $Wynik !== false ) {
          if ( $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT'] == 'pdf' ) {
              header('Content-type: application/pdf');
              header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
          }
          if ( $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT'] == 'zpl' ) {
              header('Content-type: application/txt');
              header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.zpl"');
          }
          if ( $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT'] == 'epl' ) {
              header('Content-type: application/txt');
              header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.epl"');
          }
          echo $Wynik;
      }

      break;

    case 'protokol':

      $Parcel->id = $_GET['przesylka'];
      $params->packages[] = $Parcel;

      $Wynik = $apiKurier->commandPostWydruk('packages/protocol', $params);

      if ( $Wynik !== false ) {
          header('Content-type: application/pdf');
          header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
          echo $Wynik;
      }

      break;

    case 'szczegoly':

      $Wynik = $apiKurier->commandGet('packages/'.$_GET['przesylka'], true, '', false);

      if ( $Wynik !== false ) {

          $Informacja = '';
          $Informacja .= 'ID przesyłki  : ' . $Wynik->package_id . '<br />';
          $Informacja .= 'ID operatora  : ' . $Wynik->service_id . '<br />';
          $Informacja .= 'Koszt         : ' . $Wynik->pricing->price_gross . ' zł<br />';

          $Informacja .= 'Status        : ' . $Wynik->state . '<br />';
          if ( $Wynik->pickup_number != '' ) {
            $Informacja .= 'Numer zlecenia podjazdu   : ' . $Wynik->pickup_number . '<br />';
          }
          if ( isset($Wynik->pickup_date->date) ) {
            $Informacja .= 'Data podjazdu : ' . $Wynik->pickup_date->date . ' ' . $Wynik->pickup_date->min_time . '-'. $Wynik->pickup_date->max_time . '<br />';
          }

          include('naglowek.inc.php');
          if ( $Zakladka != '' ) {
            echo Okienka::pokazOkno('Informacja o przesyłce', $Informacja, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y')));
          } else {
            echo Okienka::pokazOkno('Informacja o przesyłce', $Informacja, 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
          }
          include('stopka.inc.php');
      }

      break;

    case 'tracking':

      $Wynik = $apiKurier->commandGet('packages/'.$_GET['przesylka'].'/tracking', true, '', false);

      $Informacja = '';

      if ( $Wynik !== false ) {
          if ( isset($Wynik->tracking) && count($Wynik->tracking) > 0 ) {
              foreach ( $Wynik->tracking as $Rekord ) {
                  $Informacja .= $Rekord->status . '<br>';
                  $Informacja .= $Rekord->branch . ' ' . date("Y-m-d H:i", FunkcjeWlasnePHP::my_strtotime($Rekord->datetime)) . '<br>';
                  $Informacja .= '<hr style=\"border-top: 1px dotted #000;\">';

              }
          } else {
            $Informacja = 'Brak danych do wyświetlenia';
          }
      }

      include('naglowek.inc.php');
      if ( $Zakladka != '' ) {
        echo Okienka::pokazOkno('Historia', $Informacja, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y')));
      } else {
        echo Okienka::pokazOkno('Historia', $Informacja, 'sprzedaz/zamowienia_wysylki_furgonetka.php?id_poz='.(int)$_GET["id_poz"]);
      }
      include('stopka.inc.php');

      break;


  }

}

?>