<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new SenditApi();

  switch ( $_GET['akcja']) {

    case 'etykieta':

      $daneWejsciowe = $_GET['przesylka'];

      $result = $apiKurier->getLabel($daneWejsciowe);

      if ( isset($result['status']) && $result['status'] == 'success' ) {

        $result2 = $apiKurier->OrderGet($daneWejsciowe);

        if ( $result2['status'] == 'success' ) {
            $status = end($result2['history']);
            $tracking_code = array();
            foreach( $result2['order']['trackingCodes'] as $track ) {
                $tracking_code[] = $track;
            }
            $tracking_code = serialize($tracking_code);

            $pola = array(
                    array('orders_shipping_comments',$tracking_code),
                    array('orders_shipping_status',$status['statusNumber']),
                    array('orders_shipping_date_modified','now()'),
            );

            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
            unset($pola);

            header( 'Content-type: application/pdf' );
            header( 'Content-disposition: attachment; filename=Sendit.pl-lp-'.date('d-m-Y').'.pdf' );
            if ( strstr( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE' ) !== false ) {
                header( 'Cache-Control: maxage=1' );
                header( 'Pragma: public' );
            } else {
                header( 'Pragma: no-cache' );
            }
            ob_clean();
            echo base64_decode((string)$result['pdf']);
            die();
        }

      } else {

        $result2 = $apiKurier->OrderGet($daneWejsciowe);

        if ( $result2['status'] == 'success' ) {
            $status = end($result2['history']);
            $tracking_code = array();
            foreach( $result2['order']['trackingCodes'] as $track ) {
                $tracking_code[] = $track;
            }
            $tracking_code = serialize($tracking_code);

            $pola = array(
                    array('orders_shipping_comments',$tracking_code),
                    array('orders_shipping_status',$status['statusNumber']),
                    array('orders_shipping_date_modified','now()'),
            );

            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
            unset($pola);
        }

        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $result['faultcode']. ': '. $result['faultstring'], 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');

      }

      break;

    case 'status':

      $daneWejsciowe = $_GET['przesylka'];

      $result = $apiKurier->getStatus($daneWejsciowe);

      if ( isset($result['status']) && $result['status'] == 'success' ) {

        $pola = array(
                array('orders_shipping_status',$result['orders']['0']['statusNumber']),
                array('orders_shipping_date_modified','now()'),
        );

        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
        unset($pola);

        $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_number = '" . $_GET['przesylka'] . "' AND orders_id = '" . (int)$_GET['id_poz']."'";
        $sql = $db->open_query($zapytanie);

        $info = $sql->fetch_assoc();

        if ( $info['orders_shipping_comments'] == '' ) {

            $result2 = $apiKurier->OrderGet($daneWejsciowe);

            if ( $result2['status'] == 'success' ) {
                $status = end($result2['history']);
                $tracking_code = array();
                foreach( $result2['order']['trackingCodes'] as $track ) {
                    $tracking_code[] = $track;
                }
                $tracking_code = serialize($tracking_code);

                $pola = array(
                        array('orders_shipping_comments',$tracking_code),
                        array('orders_shipping_date_modified','now()'),
                );

                $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
                unset($pola);
            }
        }

        $db->close_query($sql);
        unset($info,$zapytanie);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));

      } else {
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $result['faultcode']. ': '. $result['faultstring'], 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');    
      }

      break;

    case 'protokol':

      $daneWejsciowe = $_GET['przesylka'];

      $protocol_nr = '';

      $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_number = '" . $_GET['przesylka'] . "' AND orders_id = '" . (int)$_GET['id_poz']."'";
      $sql = $db->open_query($zapytanie);

      if ((int)$db->ile_rekordow($sql) > 0) {
          $info = $sql->fetch_assoc();

          if ( $info['orders_shipping_protocol'] == '' ) {

            $result2 = $apiKurier->OrderGet($daneWejsciowe);
            if( isset($result2['status']) && $result2['status'] == 'success') {
                $status = end($result2['history']);
                if( isset($result2['order']['protocolNumber']) ) {

                    $pola = array(
                            array('orders_shipping_status',$status['statusNumber']),
                            array('orders_shipping_date_modified','now()'),
                            array('orders_shipping_protocol',$result2['order']['protocolNumber']),
                    );

                    $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
                    unset($pola);
                } else {

                    $result = $apiKurier->ProtocolGenerate($daneWejsciowe);

                    if($result['status'] == 'success') {
                        $protocol_nr = $result['protocols'][0]['protocolNumber'];

                        $pola = array(
                                array('orders_shipping_status',$status['statusNumber']),
                                array('orders_shipping_date_modified','now()'),
                                array('orders_shipping_protocol',$result2['order']['protocolNumber']),
                        );

                        $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."'");	
                        unset($pola);

                    }
                }
            }

          } else {

            $protocol_nr = $info['orders_shipping_protocol'];
          }

          $result3 = $apiKurier->getProtokol($protocol_nr);

          if( isset($result3['status']) && $result3['status'] == 'success') {
                header( 'Content-type: application/pdf' );
                header( 'Content-disposition: attachment; filename=Sendit.pl-protocol-'. $protocol_nr .'.pdf' );
                if ( strstr( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE' ) !== false ) {
                    header( 'Cache-Control: maxage=1' );
                    header( 'Pragma: public' );
                } else {
                    header( 'Pragma: no-cache' );
                }
                ob_clean();
                echo base64_decode((string)$result3['pdf']);
                die();

          } else {

            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', $result3['faultcode']. ': '. $result3['faultstring'], 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
            include('stopka.inc.php');
          }

      } else {
          Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      }
      $db->close_query($sql);
      unset($info,$zapytanie);

      break;
  }

}

?>