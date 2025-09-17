<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new SiodemkaApi();

  switch ( $_GET['akcja']) {

    case 'etykieta':

      $plikEtykiety = $apiKurier->wydrukEtykietaPdf( array('numery' => $_GET['przesylka'], 'klucz' => $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_API_PIN'], 'separator' => ';'));

      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
      echo $plikEtykiety->result;
      break;

    case 'status':

      $wynik = $apiKurier->statusyPrzesylki( array('numerListu' => $_GET['przesylka'], 'czyOstatni'=>'1', 'klucz' => $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_API_PIN']));

      if( is_object($wynik) && isset($wynik) && count($wynik) > 0 ) {

          $pola = array(
                  array('orders_shipping_status',$wynik->result->skrot),
                  array('orders_shipping_date_modified','now()'),
          );

          $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");

          unset($pola);

          Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));

      } else {
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', 'Zwrócony wynik jest pusty. Prosze sprawdzić przesyłkę w serwisie Siódemki.', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');    
      }

      break;

    case 'list':

      $plikEtykiety = $apiKurier->wydrukListPdf( array('numer' => $_GET['przesylka'], 'klucz' => $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_API_PIN']));

      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
      echo $plikEtykiety->result;
      break;



  }

}

?>