<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new PaczkaRuchApi((int)$_GET['id_poz']);

  switch ( $_GET['akcja']) {

    case 'tracking':

      $wynik = $apiKurier->doGiveMePackStatus($_GET['przesylka']);

      if ( is_object($wynik) ) {

          $pola = array(
                  array('orders_shipping_status',$wynik->Trans),
          );

          $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
          unset($pola);

          $komunikat = '';
          $komunikat .= 'Aktualny status : ' . $wynik->Trans_Des . '<br />';
          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Sukces', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');
      }

      break;

    case 'usun':

      $wynik = $apiKurier->doPutCustomerPackCanceled($_GET['przesylka']);

      if ( $wynik == 'OK' ) {
          $pola = array(
                  array('orders_shipping_status','201'),
          );

          $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
          unset($pola);

          if (file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$_GET['przesylka'].'/'.$_GET['przesylka'].'.pdf')) {
             unlink(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$_GET['przesylka'].'/'.$_GET['przesylka'].'.pdf');
             rmdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$_GET['przesylka']);
          }

          $komunikat = '';
          $komunikat .= 'Przesyłka została anulowana';
          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Sukces', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');
      }

      break;

    case 'drop':

          $db->delete_query('orders_shipping' , " orders_shipping_number = '".$_GET["przesylka"]."'");  

          $komunikat = 'Zapis został usunięty z bazy';
          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Sukces', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');

      break;

    case 'etykieta':

      $plikEtykiety = $apiKurier->doLabelPrintDuplicate($_GET['przesylka']);

      if (!file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$_GET['przesylka'])) {
        mkdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$_GET['przesylka'], 0777, true);
      }

      file_put_contents(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$_GET['przesylka'].'/'.$_GET['przesylka'].'.pdf', $plikEtykiety);

      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
      echo $plikEtykiety;
      break;

    case 'protokol':

      $plikProtokolu = $apiKurier->doGenerateProtocol($_GET['przesylka']);

      $xml = simplexml_load_string($plikProtokolu->GenerateProtocolResult->any);

      if ( $xml->NewDataSet->Table->Err == '0' ) { 

          $pola = array(
                  array('orders_shipping_status',$xml->NewDataSet->Table->status),
                  array('orders_shipping_date_modified', date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($xml->NewDataSet->Table->DATA_MOD)))
          );

          $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
          unset($pola);
          header('Content-type: application/pdf');
          header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
          echo $plikProtokolu->LabelData;
      }

      break;

    case 'pobierz':

      $PlikDoPobrania = KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/' . $_GET['przesylka'] . '/' . $_GET['paczka'];

      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="'.$_GET['paczka']);
      readfile($PlikDoPobrania);
      exit();
      break;


  }

}

?>