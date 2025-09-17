<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new KexApi();

  switch ( $_GET['akcja']) {

    case 'etykieta':

      $daneWejsciowe = "<Dane>";
      $daneWejsciowe .= "    <NazwaMetody>PobierzWydrukiEtykiet</NazwaMetody>";
      $daneWejsciowe .= "    <Parametry>";
      $daneWejsciowe .= "        <NumerPrzesylki>".$_GET['przesylka']."</NumerPrzesylki>";
      $daneWejsciowe .= "    </Parametry>";
      $daneWejsciowe .= "</Dane>";

      $wynik = $apiKurier->PobierzEtykiete( $daneWejsciowe );

      if( is_object($wynik) ) {

          if ( isset($wynik->Bledy->Blad) ) {

              $komunikat = '';
              foreach ( $wynik->Bledy->Blad as $rekord ) {
                  $komunikat .= str_replace('"', '', (string)$rekord->Komuniakt) . '<br /><br />';
              }
              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Błąd', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');

          } else {

              header('Content-type: application/pdf');
              header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
              echo base64_decode((string)$wynik->Wyniki->WydrukEtykiet->Dane);

         }

      }
      break;

    case 'status':

      $daneWejsciowe = "<Dane>";
      $daneWejsciowe .= "    <NazwaMetody>PobierzStatusy</NazwaMetody>";
      $daneWejsciowe .= "    <Parametry>";
      $daneWejsciowe .= "        <NumerPrzesylki>".$_GET['przesylka']."</NumerPrzesylki>";
      $daneWejsciowe .= "    </Parametry>";
      $daneWejsciowe .= "</Dane>";

      $wynik = $apiKurier->PobierzStatus( $daneWejsciowe );

      if( is_object($wynik) ) {

          if ( isset($wynik->Bledy->Blad) ) {

              $komunikat = '';
              foreach ( $wynik->Bledy->Blad as $rekord ) {
                  $komunikat .= str_replace('"', '', (string)$rekord->Komuniakt) . '<br /><br />';
              }
              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Błąd', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');

          } else {

              $pola = array(
                      array('orders_shipping_status',$wynik->Wyniki->StatusPrzesylki->Status),
                      array('orders_shipping_date_modified','now()'),
              );

              $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");

              unset($pola);

              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Status przesyłki', $wynik->Wyniki->StatusPrzesylki->Opis, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');

              //Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));

         }

      }

      break;

    case 'list':

      $daneWejsciowe = "<Dane>";
      $daneWejsciowe .= "    <NazwaMetody>PobierzWydrukiListowPrzewozowych</NazwaMetody>";
      $daneWejsciowe .= "    <Parametry>";
      $daneWejsciowe .= "        <NumerPrzesylki>".$_GET['przesylka']."</NumerPrzesylki>";
      $daneWejsciowe .= "    </Parametry>";
      $daneWejsciowe .= "</Dane>";

      $wynik = $apiKurier->PobierzListPrzewozowy( $daneWejsciowe );

      if( is_object($wynik) ) {

          if ( isset($wynik->Bledy->Blad) ) {

              $komunikat = '';
              foreach ( $wynik->Bledy->Blad as $rekord ) {
                  $komunikat .= str_replace('"', '', (string)$rekord->Komuniakt) . '<br /><br />';
              }
              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Błąd', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');

          } else {

              header('Content-type: application/pdf');
              header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
              echo base64_decode((string)$wynik->Wyniki->WydrukListowPrzewozowych->Dane);

         }

      }
      break;

    case 'anuluj':

      $daneWejsciowe = "<Dane>";
      $daneWejsciowe .= "    <NazwaMetody>AnulujPrzesylki</NazwaMetody>";
      $daneWejsciowe .= "    <Parametry>";
      $daneWejsciowe .= "        <NumerPrzesylki>".$_GET['przesylka']."</NumerPrzesylki>";
      $daneWejsciowe .= "    </Parametry>";
      $daneWejsciowe .= "</Dane>";

      $wynik = $apiKurier->AnulujPrzesylke( $daneWejsciowe );

      if( is_object($wynik) ) {

          if ( isset($wynik->Bledy->Blad) ) {

              $komunikat = '';
              foreach ( $wynik->Bledy->Blad as $rekord ) {
                  $komunikat .= str_replace('"', '', (string)$rekord->Komuniakt) . '<br /><br />';
              }
              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Błąd', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');

          } else {

              $pola = array(
                      array('orders_shipping_status','AN'),
                      array('orders_shipping_date_modified','now()'),
              );

              $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");

              unset($pola);
              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Anulowanie przesyłki', $wynik->Wyniki->AnulowanePrzesylki->NumerPrzesylki . ' - została anulowana', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');

         }

      }

      break;
}

}

?>