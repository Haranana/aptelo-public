<?php
chdir('../'); 

// ************** czesc kodu wymagana w przypadku zadan harmonogramu zadan **************

// zmienna zeby nie odczytywalo ponownie crona
$BrakCron = true;

// ustawienie separatora dziesietnego na kropke - problem w nazwa.pl
$LokaleConv = localeconv();
if ( isset($LokaleConv['decimal_point']) && $LokaleConv['decimal_point'] == ',' ) {
    setlocale(LC_NUMERIC, 'C');
}
unset($LokaleConv);

// wczytanie ustawien inicjujacych system
//require_once('ustawienia/init.php');
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');
define('WLACZENIE_CACHE', 'tak');

require_once('ustawienia/ustawienia_db.php');
include('klasy/Bazadanych.php');
$db = new Bazadanych();
include('klasy/Funkcje.php');
include('klasy/CacheSql.php');

$GLOBALS['cache'] = new CacheSql();

// ************** koniec **************

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


$zapytanieDomyslnaWaluta = "SELECT c.code FROM languages l LEFT JOIN currencies c ON c.currencies_id = l.currencies_default WHERE languages_default = '1'";
$sqlDomyslnaWaluta = $db->open_query($zapytanieDomyslnaWaluta);
$waluta = $sqlDomyslnaWaluta->fetch_assoc();
$DomyslnaWaluta = $waluta['code'];
$db->close_query($sqlDomyslnaWaluta);
unset($zapytanieDomyslnaWaluta,$sqlDomyslnaWaluta,$waluta);

if( $code == 200) {
    if ( $DomyslnaWaluta == 'PLN' ) {

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
                              array('last_updated', 'now()' ),
                              );

                $db->update_query('currencies' , $pola, " code = '" . $pozycja->code . "'");
                
            }
            
            $db->close_query($sql);
            unset($sql);
            
        }
        
        $pola = array(
                      array('value', '1' ),
                      array('last_updated', 'now()' ),
                      );
        
        $db->update_query('currencies' , $pola, " code = '" . $DomyslnaWaluta . "'");    
        
      } else {
      
        foreach ($RatesArr['0']->rates as $pozycja) {
        
            if ( $pozycja->code == $DomyslnaWaluta ) {

                $przelicznik = str_replace(',', '.', (string)$pozycja->mid);

            }
          
        }
        
        $zapytanie = "select currencies_id, currencies_marza, code from currencies where code != '" . $DomyslnaWaluta . "'";
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
                
                if ( $waluta['code'] == 'PLN' && $domyslna_waluta['kod'] != 'PLN' ) {

                    foreach ($RatesArr['0']->rates as $pozycja) {
                      if ( $pozycja->code == $DomyslnaWaluta ) {
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
            
            $db->update_query('currencies' , $pola, " code = '" . $DomyslnaWaluta . "'");              
            
        }
        
        $db->close_query($sql);
        unset($sql);
        
    }

    // ************** czesc kodu wymagana w przypadku zadan harmonogramu zadan - jezeli skrypt dotyczy produktow - musi zostac wyczyszczony cache **************

    $GLOBALS['cache']->UsunCacheProduktow();

    // ************** koniec **************
}
?>