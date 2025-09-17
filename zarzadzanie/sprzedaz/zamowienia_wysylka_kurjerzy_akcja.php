<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new KurjerzyApi();

  switch ( $_GET['akcja']) {

    case 'etykieta':

      $daneWejsciowe = array('order_number' => $_GET['przesylka']);
      $plikEtykiety = $apiKurier->getLabel($daneWejsciowe);

      if(array_key_exists('error', $plikEtykiety)){
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', implode('<br>', (array)$plikEtykiety['error']), 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');    
      } else {
        if( isset($plikEtykiety['order_label']) ) {
          header('Content-type: application/pdf');
          header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
          echo base64_decode((string)$plikEtykiety['order_label']);
        }
      }
      break;

    case 'status':

      $daneWejsciowe = array('order_number' => $_GET['przesylka']);

      $status = $apiKurier->getParcelStatus($daneWejsciowe);

      if(array_key_exists('error', $status)) {
        $pola = array(
                array('orders_shipping_comments',implode(';', (array)$status['error'])),
                array('orders_shipping_date_modified','now()'),
        );

        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
        unset($pola);
 
      } else {

        $wynik = implode(';', (array)$status['0']);
        $pola = array(
                array('orders_shipping_status',$wynik),
                array('orders_shipping_date_modified','now()'),
        );

        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
        unset($pola);

      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      break;

  }

}

?>