<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $er_url = 'https://api.nbp.pl/api/exchangerates/tables/A';
    $headers = array(
        "Accept: application/json"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $er_url);

    $rates = curl_exec ($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $RatesArr = json_decode($rates);
    curl_close ($ch);

    if( $code == 200) {
        if ( $_SESSION['domyslna_waluta']['kod'] == 'PLN' ) {

            foreach ($RatesArr['0']->rates as $pozycja) {
            
              $zapytanie = "select currencies_id, currencies_marza, code from currencies where code = '" . $pozycja->code . "'";
              $sql = $db->open_query($zapytanie);
              
              if ($db->ile_rekordow($sql) > 0) {

                $waluta = $sql->fetch_assoc();              
                $marza = $waluta['currencies_marza'];
                if ((int)$marza > 0) {
                    $mar = 1 + ( $marza/100 );
                    $mar = 1;
                   } else {
                    $mar = 1;
                }
                
                $Jednostki = 1;

                $pola = array(
                              array('value', ($Jednostki/str_replace(',', '.', (string)$pozycja->mid)) * $mar ),
                              array('last_updated', 'now()' )
                              );
                
                $db->update_query('currencies' , $pola, " code = '" . $pozycja->code . "'");	
              }
              
            }
            
            $pola = array(
                          array('value', '1' ),
                          array('last_updated', 'now()' ),
                          );
            
            $db->update_query('currencies' , $pola, " code = '" . $_SESSION['domyslna_waluta']['kod'] . "'");    
            
          } else {
          
            foreach ($RatesArr['0']->rates as $pozycja) {
            
              if ( $pozycja->code == $_SESSION['domyslna_waluta']['kod'] ) {
              
                $przelicznik = str_replace(',', '.', (string)$pozycja->mid);
                
              }
              
            }
            
            $zapytanie = "select currencies_id, currencies_marza, code from currencies where code != '" . $_SESSION['domyslna_waluta']['kod'] . "'";
            $sql = $db->open_query($zapytanie);

            if ($db->ile_rekordow($sql) > 0) {
                
                while ($waluta = $sql->fetch_assoc()) {
                
                    $kurs = 0;
                    $marza = $waluta['currencies_marza'];
                    if ((int)$marza > 0) {
                      $mar = 1 + ( $marza/100 );
                      $mar = 1;
                    } else {
                      $mar = 1;
                    }
                    
                    if ( $waluta['code'] == 'PLN' && $_SESSION['domyslna_waluta']['kod'] != 'PLN' ) {

                        foreach ($RatesArr['0']->rates as $pozycja) {
                          if ( $pozycja->code == $_SESSION['domyslna_waluta']['kod'] ) {
                            $kurs = round(str_replace(',', '.', (string)$pozycja->mid),4);
                          }
                        }                     
                         
                      } else {
                               
                        foreach ($RatesArr['0']->rates as $pozycja) {
                          if ( $pozycja->code == $waluta['code'] ) {
                            $kurs = round(($przelicznik / str_replace(',', '.', (string)$pozycja->mid)),4);
                          }
                        }
                        
                    }
                    
                    $pola = array(
                                  array('value', $kurs * $mar ),
                                  array('last_updated', 'now()' ),
                                  );
                    
                    $db->update_query('currencies' , $pola, " code = '" . $waluta['code'] . "'");	  

                }

                $pola = array(
                              array('value', '1' ),
                              array('last_updated', 'now()' ),
                              );
                
                $db->update_query('currencies' , $pola, " code = '" . $_SESSION['domyslna_waluta']['kod'] . "'");              
                
                
            }
        }
    } else {
        exit('Blad: Nie znaleziono tabeli kursow.');
    }

    if (isset($_GET['wroc']) && $_GET['wroc'] == 'tak') {
        Funkcje::PrzekierowanieURL('waluty.php');
    }
}

?>