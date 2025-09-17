<?php

chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier        = new InPostApi();
  $status           = $apiKurier->inpost_get_pack_status( $_GET['przesylka']);

  switch ( $_GET['akcja']) {

    case 'etykieta':

      $plikEtykiety = $apiKurier->inpost_get_sticker( $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'], $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_HASLO'], $_GET['przesylka'], $apiKurier->polaczenie['INTEGRACJA_INPOST_FORMAT_WYDRUKU'], $apiKurier->polaczenie['INTEGRACJA_INPOST_FORMAT_PLIKU'] );

      if( is_array($plikEtykiety) && array_key_exists('error', $plikEtykiety)){
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $plikEtykiety['error']['message'], 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');    
      } else {

        if ( isset($status) && $status != '0' ) {
          $pola = array(
                  array('orders_shipping_status', $status),
                  array('orders_shipping_date_modified','now()'),
          );
          $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");
        } else {
          $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_number = '".$_GET['przesylka']."' AND orders_shipping_status = 'Created'";
          $sql = $db->open_query($zapytanie);

          if ((int)$db->ile_rekordow($sql) > 0) {
            $info = $sql->fetch_assoc();

            $pola = array(
                  array('orders_shipping_status', 'Prepared'),
                  array('orders_shipping_date_modified','now()'),
            );

            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");
          }

          $db->close_query($sql);
          unset($zapytanie,$info,$pola);
        }

        if ( $apiKurier->polaczenie['INTEGRACJA_INPOST_FORMAT_PLIKU'] == 'PDF' ) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
            echo $plikEtykiety;
        }
        if ( $apiKurier->polaczenie['INTEGRACJA_INPOST_FORMAT_PLIKU'] == 'Epl2' ) {
            header('Content-type: application/txt');
            header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.zpl"');
            echo $plikEtykiety;
        }


      }
      break;

    case 'status':

      $wynik = $apiKurier->inpost_get_pack_status( $_GET['przesylka']);

      if( is_array($wynik) && array_key_exists('error', $wynik)){
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $wynik['error']['message'], 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');    
      } else {
        $pola = array(
                array('orders_shipping_status',$wynik),
                array('orders_shipping_date_modified','now()'),
        );

        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");

        unset($pola);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      }
      break;


    case 'potwierdzenie':

      $plikPotwierdzenia = $apiKurier->inpost_get_confirm_printout( $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'], $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_HASLO'], array($_GET['przesylka']), '0' );

      if( is_array($plikPotwierdzenia) && array_key_exists('error', $plikPotwierdzenia)){
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $plikPotwierdzenia['error']['message'], 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');
      } else {
        $pola = array(
                array('orders_shipping_date_modified','now()'),
        );
        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");
        unset($pola);

        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
        echo $plikPotwierdzenia;
      }
      break;

    case 'usun':

      $wynik = $apiKurier->inpost_cancel_pack( $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'], $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_HASLO'], $_GET['przesylka'] );

      if ( $wynik == '1' ) {
        $db->delete_query('orders_shipping' , " orders_shipping_number = '".$filtr->process($_GET["przesylka"])."'");  
      }
      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      break;
  }
}

?>