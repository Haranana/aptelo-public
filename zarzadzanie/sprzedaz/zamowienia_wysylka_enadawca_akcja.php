<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new ElektronicznyNadawca();

  switch ( $_GET['akcja']) {

    //Wsylanie paczek do serwisu enadawcy
    case 'sendenvelope':

        $Z = new sendEnvelope();
        $Z->urzadNadania = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_URZAD_NADANIA'];
        $wynik = $apiKurier->sendEnvelope($Z);

        include('naglowek.inc.php');

        if ( isset($wynik) && count((array)$wynik) > 0 ) {
            if ( $wynik->error == '' ) {
                echo Okienka::pokazOkno('Sukces', 'Przesyłki zostały przesłane z bufora do serwisu EN', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 

                $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_type = 'Elektroniczny Nadawca' AND orders_shipping_status = '0' AND DATE(orders_shipping_date_created) = CURRENT_DATE";
                $sql = $db->open_query($zapytanie);

                if ( $db->ile_rekordow($sql) > 0 ) {

                    while ($info = $sql->fetch_assoc()) {
                        $id = $info['orders_shipping_id'];
                        $komentarz = $info['orders_shipping_comments'] .':'.$wynik->idEnvelope;
                        $status = $wynik->envelopeStatus;

                        $pola = array(
                                array('orders_shipping_comments',$filtr->process($komentarz)),
                                array('orders_shipping_status',$status),
                                array('orders_shipping_date_modified', 'now()'),
                        );
                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$id."'");	
                    }

                }
                $db->close_query($sql);
                unset($zapytanie, $info);

            } else {
                echo Okienka::pokazOkno('Błąd', $wynik->error->errorNumber . ': ' . $wynik->error->errorDesc, 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            }
        } else {
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
        }

        include('stopka.inc.php');    

        break;

    //Wyczyszczenie bufora z paczek utworzonych do wyslania
    case 'clearbufor':

        try
        {
            $Z = new clearEnvelope();
            $wynik = $apiKurier->clearEnvelope($Z);
        }
        catch(SoapFault $soapFault)
        {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y')));
            include('stopka.inc.php');
            break;
        }

        include('naglowek.inc.php');

        if ( is_object($wynik) ) {
            if ( $wynik->error == '' ) {
                $db->delete_query('orders_shipping' , " orders_shipping_type = 'Elektroniczny Nadawca' AND orders_shipping_status = '0'");  
                echo Okienka::pokazOkno('Sukces', 'Bufor przesyłek został opróżniony', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            } else {
                echo Okienka::pokazOkno('Błąd', $wynik->error, 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            }
        } else {
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
        }

        include('stopka.inc.php');

        break;

    //Pobranie zawartosci znajdujacej sie w buforze
    case 'getenvelopebufor':

        try
        {
            $Z = new getEnvelopeBufor();
            $wynik = $apiKurier->getEnvelopeBufor($Z);
        }
        catch(SoapFault $soapFault)
        {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y')));
            include('stopka.inc.php');
            break;
        }

        if ( is_object($wynik) && count((array)$wynik) ) {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Przesyłki w buforze', 'Ilość przesyłek : ' . ( isset($wynik->przesylka) ? count((array)$wynik->przesylka) : '-' ), 'index.php'); 
            include('stopka.inc.php');    
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Wystąpił problem w dostępie do API', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

    //Pobranie listy zdefiniowanych buforow
    case 'getenvelopebuforlist':

        try
        {
            $Z = new getEnvelopeBuforList();
            $wynik = $apiKurier->getEnvelopeBuforList($Z);
        }
        catch(SoapFault $soapFault)
        {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y')));
            include('stopka.inc.php');
            break;
        }

        if ( is_object($wynik) ) {
            $opisBufora = '';
            if ( is_object($wynik->bufor) ) {
                $opisBufora .= 'Opis : ' . $wynik->bufor->opis . ' ID : ' . $wynik->bufor->idBufor . ' Data nadania : ' . $wynik->bufor->dataNadania . '<br/>';
                include('naglowek.inc.php');
                echo Okienka::pokazOkno('Utworzone bufory', $opisBufora, 'index.php'); 
                include('stopka.inc.php');
            } elseif ( is_array($wynik->bufor) && count((array)$wynik->bufor) > 0 ) {
                foreach ( $wynik->bufor as $Bufor ) {
                    if ( isset($Bufor->opis) ) {
                      $opisBufora .= 'Opis : ' . $Bufor->opis . ' ID : ' . $Bufor->idBufor . ' Data nadania : ' . $Bufor->dataNadania . '<br/>';
                    }
                }
                include('naglowek.inc.php');
                echo Okienka::pokazOkno('Utworzone bufory', $opisBufora, 'index.php'); 
                include('stopka.inc.php');    
            } else {
                include('naglowek.inc.php');
                echo Okienka::pokazOkno('Utworzone bufory', 'Brak utworzonych aktywnych buforów', 'index.php'); 
                include('stopka.inc.php');    
            }
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

    //Sprawdzenie czy jest aktywny bufor - jesli nie to utworzenie
    case 'createbufor':

        try
        {
            $Z = new getEnvelopeBuforList();
            $wynik = $apiKurier->getEnvelopeBuforList($Z);
        }
        catch(SoapFault $soapFault)
        {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y')));
            include('stopka.inc.php');
            break;
        }

        if ( isset($wynik) && count((array)$wynik) > 0 ) {
            if ( count($wynik->bufor) == 0 ) {

                $tmp = new createEnvelopeBufor();

                $B1 = new buforType();

                $B1->urzadNadania = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_URZAD_NADANIA'];
                $B1->dataNadania = date('d-m-Y');
                $B1->active      = true;
                $B1->opis        = 'Przesyłki ze sklepu';

                $tmp->bufor = $B1;

                $wynik = $apiKurier->createEnvelopeBufor($tmp);
            }
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }
        break;

    //Zmiana danych w buforze
    case 'updateenvelopebufor':

        $Z = new updateEnvelopeBufor();
        $wynik = $apiKurier->updateEnvelopeBufor($Z);

        if ( isset($wynik) && count((array)$wynik) > 0 ) {
            echo '<pre>';
            echo print_r($wynik);
            echo '</pre>';
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }
        break;

    //Usuniecie z bufora pojedynczej paczki
    case 'clearbuforGuid':

        $Z = new clearEnvelopeByGuids();
        $Z->guid = $_GET['przesylka'];

        $wynik = $apiKurier->clearEnvelopeByGuids($Z);

        include('naglowek.inc.php');

        if ( isset($wynik) && count((array)$wynik) > 0 ) {
            if ( $wynik->error == '' ) {
                echo Okienka::pokazOkno('Sukces', 'Przesyłka została usunięta z bufora', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 

                $db->delete_query('orders_shipping' , " orders_shipping_id = '".$_GET['przesylkaId']."'");

            } else {
                echo Okienka::pokazOkno('Błąd', $wynik->error->errorNumber . ': ' . $wynik->error->errorDesc, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            }
        } else {
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
        }
        include('stopka.inc.php');    

        break;

    //Usuniecie z bufora paczek z wczesniejsza data utworzenia
    case 'clearbuforGuidMulti':

        $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_type = 'Elektroniczny Nadawca' AND orders_shipping_status = '0' AND DATE(orders_shipping_date_created) < CURRENT_DATE";
        $sql = $db->open_query($zapytanie);

        $listaPaczek = '';

        if ( $db->ile_rekordow($sql) > 0 ) {

            while ($info = $sql->fetch_assoc()) {
                $tablicaPaczek[] = $info['orders_shipping_comments'];
            }

            $Z = new clearEnvelopeByGuids();
            $Z->guid = $tablicaPaczek;

            $wynik = $apiKurier->clearEnvelopeByGuids($Z);

            include('naglowek.inc.php');

            if ( isset($wynik) && count((array)$wynik) > 0 ) {
                if ( $wynik->error == '' ) {
                    echo Okienka::pokazOkno('Sukces', 'Przesyłki z wcześniejszą datą utworzenia zostały usunięte z bufora', 'sprzedaz/index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 

                    for ($i=0, $n=count($tablicaPaczek); $i<$n; $i++) {
                        $db->delete_query('orders_shipping' , " orders_shipping_comments = '".$tablicaPaczek[$i]."'");
                    }

                } else {
                    echo Okienka::pokazOkno('Błąd', $wynik->error->errorNumber . ': ' . $wynik->error->errorDesc, 'sprzedaz/index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
                }
            } else {
                echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'sprzedaz/index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            }
            include('stopka.inc.php');    

        }
        $db->close_query($sql);
        unset($zapytanie, $info);
        
        break;

    //Wydruk naklejki na przesylke
    case 'etykieta':

        $Z = new getAddresLabelByGuid();
        $Z->guid = $_GET['przesylka'];
        $wynik = $apiKurier->getAddresLabelByGuid($Z);
        $komunikat = '';

        if ( is_object($wynik) ) {
            if ( $wynik->error == '' && $wynik->content != '' ) {
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
                echo $wynik->content->pdfContent;
            } else {
                include('naglowek.inc.php');
                if ( is_array($wynik->error) && count($wynik->error) > 0 ) {
                    foreach( $wynik->error as $Blad ) {
                        $komunikat .= $Blad->errorNumber . ': '. $Blad->errorDesc . '<br>';
                    }
                } else {
                    $komunikat .= $wynik->error->errorNumber . ': '. $wynik->error->errorDesc . '<br>';
                }
                echo Okienka::pokazOkno('Błąd', $komunikat, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
                include('stopka.inc.php');    
            }
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

    //Wydruk blankietu pobrania
    case 'pobranie':

        $Z = new getBlankietPobraniaByGuids();
        $Z->guid = $_GET['przesylka'];
        $wynik = $apiKurier->getBlankietPobraniaByGuids($Z);

        if ( isset($wynik) && count((array)$wynik) > 0 ) {
            if ( $wynik->error == '' && $wynik->content != '' ) {
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
                echo $wynik->content->pdfContent;
            } elseif ( $wynik->error == '' && $wynik->content == '' ) {
                include('naglowek.inc.php');
                echo Okienka::pokazOkno('Błąd', 'Brak danych - nie można wygenerować blankietu', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
                include('stopka.inc.php');    
            } else {
                include('naglowek.inc.php');
                echo Okienka::pokazOkno('Błąd', $wynik->error->errorNumber .': '. $wynik->error->errorDesc, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
                include('stopka.inc.php');    
            }
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

    //Wydruk naklejek na przesylke dla wyslanej paczki
    case 'etykiety':

        $Z = new getAddresLabelCompact();
        $Z->idEnvelope = $_GET['idEnvelope'];
        $wynik = $apiKurier->getAddresLabelCompact($Z);
        $komunikat = '';
        if ( is_object($wynik) ) {
            if ( $wynik->error == '' && $wynik->pdfContent != '' ) {
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="etykiety_'.$_GET['idEnvelope'].'.pdf"');
                echo $wynik->pdfContent;
            } else {
                include('naglowek.inc.php');
                if ( is_array($wynik->error) && count($wynik->error) > 0 ) {
                    foreach( $wynik->error as $Blad ) {
                        $komunikat .= $Blad->errorNumber . ': '. $Blad->errorDesc . '<br>';
                    }
                } else {
                        $komunikat .= $wynik->error->errorNumber . ': '. $wynik->error->errorDesc . '<br>';
                }
                echo Okienka::pokazOkno('Błąd', $komunikat, 'sprzedaz/zamowienia_wysylki_zestawienie.php'.Funkcje::Zwroc_Get(array('id_poz','akcja','x','y'))); 
                include('stopka.inc.php');    
            }
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'sprzedaz/zamowienia_wysylki_zestawienie.php'.Funkcje::Zwroc_Get(array('id_poz','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

    case 'ksiazka':

        $Z = new getOutboxBook();
        $Z->idEnvelope = $_GET['idEnvelope'];
        $wynik = $apiKurier->getOutboxBook($Z);

        if ( $wynik->error == '' && $wynik->pdfContent != '' ) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="ksiazka_'.$_GET['idEnvelope'].'.pdf"');
            echo $wynik->pdfContent;
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Brak danych do wyświetlnia', 'sprzedaz/zamowienia_wysylki_zestawienie.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

    case 'firmowa':

        $Z = new getFirmowaPocztaBook();
        $Z->idEnvelope = $_GET['idEnvelope'];
        $wynik = $apiKurier->getFirmowaPocztaBook($Z);

        if ( $wynik->error == '' && $wynik->pdfContent != '' ) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="zestawienie_'.$_GET['idEnvelope'].'.pdf"');
            echo $wynik->pdfContent;
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Brak danych do wyświetlnia', 'sprzedaz/zamowienia_wysylki_zestawienie.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

        break;

  }

  /*
  if ( isset($_POST['ksiazka_multi']) && $_POST['ksiazka_multi'] == 'true' ) {

        $Z = new getOutboxBook();
        $Z->idEnvelope = $_GET['idEnvelope'];
        $wynik = $apiKurier->getOutboxBook($Z);


        if ( $wynik->error == '' && $wynik->pdfContent != '' ) {
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
            echo $wynik->pdfContent;
        } else {
            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Błąd', 'Brak danych do wyświetlnia', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
            include('stopka.inc.php');    
        }

  }
  */

}

?>