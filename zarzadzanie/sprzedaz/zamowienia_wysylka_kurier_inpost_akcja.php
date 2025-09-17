<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new InPostKurierApi();

  switch ( $_GET['akcja']) {

    case 'etykieta':

      $daneWejsciowe = $_GET['przesylka'];

      $plikEtykiety = $apiKurier->DoGetLabel($daneWejsciowe);

      if ( $plikEtykiety->GetLabelResult->responseDescription == 'Success' && isset($plikEtykiety->GetLabelResult->LabelData) && count($plikEtykiety->GetLabelResult->LabelData) > 0 ) {

          if ( is_array($plikEtykiety->GetLabelResult->LabelData->Label) ) {

              if (!file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$_GET['przesylka'])) {
                mkdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$_GET['przesylka'], 0777, true);
              }

              for ( $i = 0, $c = count($plikEtykiety->GetLabelResult->LabelData->Label); $i < $c; $i++ ) {

                  $binaryPDF = $plikEtykiety->GetLabelResult->LabelData->Label[$i]->MimeData;
                  $nazwaPDF = $plikEtykiety->GetLabelResult->LabelData->Label[$i]->ParcelID;

                  file_put_contents(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$_GET['przesylka'].'/'.$nazwaPDF.'.pdf', $binaryPDF);

                  //header('Content-type: application/pdf');
                  //header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
                  //echo $plikEtykiety->GetLabelResult->LabelData->Label[$i]->MimeData;
                  unset($binaryPDF, $nazwaPDF);

              }

          } else {

              if (!file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$_GET['przesylka'])) {
                mkdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$_GET['przesylka'], 0777, true);
              }

              $binaryPDF = $plikEtykiety->GetLabelResult->LabelData->Label->MimeData;
              $nazwaPDF = $plikEtykiety->GetLabelResult->LabelData->Label->ParcelID;

              file_put_contents(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$_GET['przesylka'].'/'.$nazwaPDF.'.pdf', $binaryPDF);

              //header('Content-type: application/pdf');
              //header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
              //echo $plikEtykiety->GetLabelResult->LabelData->Label->MimeData;

          }
          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Sukces', 'Etykieta dla przesylki ' . $_GET['przesylka'] . ' została wygenerowana', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');

      } else {

        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', 'Nie mozna pobrać etykiety dla wybranej paczki', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');

      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));

      break;

    case 'manifest':

      $daneWejsciowe = $_GET['przesylka'];

      $plikManifestu = $apiKurier->DoGetManifest($daneWejsciowe);


      if ( $plikManifestu->GetManifestResult->responseDescription == 'Success' ) {

          header('Content-type: application/pdf');
          header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
          echo $plikManifestu->GetManifestResult->MimeData;

      } else {

        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', 'Nie mozna pobrać etykiety dla wybranej paczki', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');

      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      break;

    case 'status':

      $daneWejsciowe = $_GET['przesylka'];

      $status = $apiKurier->DoGetTracking($daneWejsciowe);

      if ( $status->GetTrackingResult->responseDescription == 'Success' ) {

        //if ( $status->GetTrackingResult->CurrentStatus->Code != 'PPN' ) {
            $pola = array(
                    array('orders_shipping_status',$status->GetTrackingResult->CurrentStatus->Code),
                    array('orders_shipping_date_modified','now()')
            );

            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
            unset($pola);
        //}
 
        $komunikat = '';
        $komunikat .= 'Aktualny status : ' . $status->GetTrackingResult->CurrentStatus->Description . '<br />';
        $komunikat .= 'Data ostatniej modyfikacji : '. $status->GetTrackingResult->CurrentStatus->EventTimestamp . '<br />';
        if ( $status->GetTrackingResult->DatePicked != '' ) {
            $komunikat .= 'Data odebrania przesyłki od nadawcy : '. $status->GetTrackingResult->DatePicked . '<br />';
        }
        if ( $status->GetTrackingResult->DateDelivered != '' ) {
            $komunikat .= 'Data odebrania przesyłki na stacji : '. $status->GetTrackingResult->DateDelivered . '<br />';
        }

        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Sukces', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');

      } else {

          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Błąd', $status->GetTrackingResult->responseDescription, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');

      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));

      break;

    case 'zamowienie':

      $daneWejsciowe = array();
      $numerWysylki = $_GET['przesylka'];
      $Parcels['Parcel'] = array();
      $DataOdbioruMin = time();
      $DataOdbioruMax = time() + (3600*24*INTEGRACJA_KURIER_INPOST_MAX_ODBIOR);

      if(date('l', $DataOdbioruMax) == 'Sunday') {
          $DataOdbioruMax = time() + (3600*24);
      }

      $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_number = '".$_GET['przesylka']."'";
      $sql = $db->open_query($zapytanie);
      while ( $info = $sql->fetch_assoc() ) {
        $Parcels = unserialize($info['orders_shipping_comments']);
      }
      $db->close_query($sql);
      unset($zapytanie, $info);

      $ShipFrom = array (
                        'Address'      => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_ULICA'],
                        'City'         => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_MIASTO'],
                        'Contact'      => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_TELEFON'],
                        'CountryCode'  => 'PL',
                        'Email'        => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_EMAIL'],
                        'Name'         => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_NAZWA'],
                        'Person'       => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_IMIE_NAZWISKO'],
                        'PostCode'     => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_KOD_POCZTOWY']
      );

      $daneWejsciowe['callPickupRequest']['PickupLocation'] = $ShipFrom;
      $daneWejsciowe['callPickupRequest']['ReadyDate'] = $DataOdbioruMin;
      $daneWejsciowe['callPickupRequest']['MaxPickupDate'] = $DataOdbioruMax;
      $daneWejsciowe['callPickupRequest']['PackageNo'][] = $_GET['przesylka'];
      $daneWejsciowe['callPickupRequest']['TotalWeight'] = $_GET['waga'];

      $daneWejsciowe['callPickupRequest']['Parcels'] = $Parcels;

      $KurierZamowienie = $apiKurier->DoCallPickup($daneWejsciowe);

      if ( $KurierZamowienie->CallPickupResult->responseDescription == 'Success' ) {

        /*
        $pola = array(
                array('orders_shipping_status','ZWK'),
                array('orders_shipping_date_modified','now()')
        );

        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
        unset($pola);
        */

        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Sukces', 'Numer zamówienia : ' . $KurierZamowienie->CallPickupResult->PickupNo, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');

      } else {

          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Błąd', $KurierZamowienie->CallPickupResult->responseDescription, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');

      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      break;

    case 'usun':

      $daneWejsciowe = $_GET['przesylka'];

      $wynik = $apiKurier->DoCancelShipment($daneWejsciowe);


      if ( $wynik->CancelShipmentResult->responseDescription == 'Success' ) {

           $db->delete_query('orders_shipping' , " orders_shipping_number = '".$filtr->process($_GET["przesylka"])."' AND orders_id = '".(int)$_GET["id_poz"]."'");  

           $sciezka = KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/' . $_GET['przesylka'];

           if ( is_dir($sciezka) ) {

               $katalog = new DirectoryIterator($sciezka); 
               foreach ($katalog as $plik) { 
                    if ($plik->isFile() || $plik->isLink()) { 
                        unlink($plik->getPathName()); 
                    } elseif (!$plik->isDot() && $plik->isDir()) { 
                        removeDir($plik->getPathName()); 
                    } 
               } 
               rmdir($sciezka); 
           }

           include('naglowek.inc.php');
           echo Okienka::pokazOkno('Usunięcie przesyłki', $_GET['przesylka'] . ' - została usunięta', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
           include('stopka.inc.php');

      } else {

          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Błąd', $wynik->CancelShipmentResult->responseDescription, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');

      }

     Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      break;

    case 'pobierz':

      $PlikDoPobrania = KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/' . $_GET['przesylka'] . '/' . $_GET['paczka'];

      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="'.$_GET['paczka'].'.pdf"');
      readfile($PlikDoPobrania);
      exit();
      break;


  }

}

?>