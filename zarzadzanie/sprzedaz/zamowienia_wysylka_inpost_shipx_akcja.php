<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier = new InPostShipX();

  switch ( $_GET['akcja']) {

    case 'usun':

      $wynik = $apiKurier->DelRequest('v1/shipments', $_GET['przesylka'], $_GET["id_poz"], $_GET["zakladka"]);

      echo '<pre>';
      echo print_r($wynik);
      echo '</pre>';

      if ( is_object($wynik) && !isset($wynik->error) ) {

          Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      }

      break;

    case 'etykieta':

      $TypWydruku = '&type='.$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_TYP'];

      if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'zpl' || $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'epl' ) {
          $TypWydruku = '';
      }

      $plikEtykiety = $apiKurier->FileRequest('v1/shipments', $_GET['przesylka'] . '/label?format='.$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'].''.$TypWydruku, $_GET["id_poz"], $_GET["zakladka"]);

      if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'pdf' ) {
          header('Content-type: application/pdf');
          header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.pdf"');
      }
      if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT'] == 'zpl' ) {
          header('Content-type: application/txt');
          header('Content-Disposition: attachment; filename="etykieta_'.$_GET['przesylka'].'.zpl"');
      }

      echo $plikEtykiety;

      break;

    case 'potwierdzenie':

      $plikProtokolu = $apiKurier->FileRequest('v1/organizations/'.$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/dispatch_orders', 'printouts?shipment_ids[]=' . $_GET['przesylka'] . '&format=pdf', $_GET["id_poz"], $_GET["zakladka"]);

      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="potwierdzenie_'.$_GET['przesylka'].'.pdf"');

      echo $plikProtokolu;

      break;

    case 'zamow_dla_paczki':
      
      $przesylka = array();
      $Komunikat = '';

      $przesylka['shipments'] = array($_GET['przesylka']);
      $przesylka['comment'] = '';
      $przesylka['address']['street'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ULICA'];
      $przesylka['address']['building_number'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_BUILDING'];
      $przesylka['address']['city'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_MIASTO'];
      $przesylka['address']['post_code'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_KOD_POCZTOWY'];
      $przesylka['address']['country_code'] = 'PL';

      $dane = $apiKurier->PostRequest('v1/organizations/' . $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/dispatch_orders', $przesylka);         

      if ( isset($dane->error) && $dane->error != '' ) {

          foreach ( $dane->details as $key=>$value ) {
            $Komunikat .= $key . ' : ' . ( is_string($value['0']) ? $value['0'] : '' ) . '<br />';
          }
          $apiKurier->PokazBlad('Błąd', $Komunikat, 'zamowienia_wysylki_inpost.php?id_poz='.$_GET['id_poz']);

      } else {

          $pola = array();
          $pola = array(
                    array('orders_dispatch_status',$dane->status),
                    array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($dane->updated_at)))
                  );

          $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$_GET['id_poz']."'");
          unset($pola);

          Funkcje::PrzekierowanieURL('zamowienia_wysylki_inpost.php');
      }

      break;

    case 'usun_baza':

      $db->delete_query('orders_shipping' , " orders_shipping_id = '".(int)$_GET['id_poz']."'");

      Funkcje::PrzekierowanieURL($_SERVER['HTTP_REFERER']);

      break;

    case 'status':

      $wynikInPost = $apiKurier->GetRequest('v1/organizations', $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/shipments?id='.$_GET['przesylka']);

      if ( isset($_GET['zlecenie']) && $_GET['zlecenie'] != '' ) {
        $wynikInPostOdbior = $apiKurier->GetRequest('v1/dispatch_orders/'.$_GET['zlecenie'], '');
      }

      $Komunikat = '';

      if ( isset($wynikInPost) && $wynikInPost->count ) {
          foreach ( $wynikInPost->items as $Przesylka ) {

              if ( $Przesylka->tracking_number != '' ) {

                  $Komunikat .= $Przesylka->tracking_number . ' - ' . $Przesylka->status . '<br />';

                  $pola = array();
                  $pola = array(
                                array('orders_shipping_number',$Przesylka->tracking_number),
                                array('orders_shipping_status',$Przesylka->status),
                                array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Przesylka->updated_at)))
                          );

                  $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$_GET['id_poz']."'");
                  unset($pola);

              }

          }
      }

      if ( isset($_GET['zlecenie']) && $_GET['zlecenie'] != '' ) {
          if ( isset($wynikInPostOdbior) ) {

                      $Komunikat .= 'Zlecenie odbioru - ' . $wynikInPostOdbior->status . '<br />';

                      $pola = array();
                      $pola = array(
                                    array('orders_dispatch_status',$wynikInPostOdbior->status),
                                    array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($wynikInPostOdbior->updated_at)))
                              );

                      $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$_GET['id_poz']."'");
                      unset($pola);

          }
      }
      $apiKurier->PokazBlad('Status przesyłki', $Komunikat, $_SERVER['HTTP_REFERER'], 'true');

      break;
  }

}

?>