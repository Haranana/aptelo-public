<?php

class SalesManago {

    private $clientId = INTEGRACJA_SALESMANAGO_ID_KLIENTA; 
    private $apiKey = 'j2q8qp4fbp9qf2b8p49fb'; 
    private $apiSecret = INTEGRACJA_SALESMANAGO_API_SECRET;
    private $endpoint = INTEGRACJA_SALESMANAGO_ENDPOINT; 
    private $owner = INTEGRACJA_SALESMANAGO_EMAIL;
    
    function ZapytaniePost($url, $data) {
        //
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array(
                         'Content-Type: application/json',
                         'Content-Length: ' . strlen((string)$data)
                    )
        );
        //
        return curl_exec($ch);
    }    
    
    public function ZapiszKlienta( $tablica = array(), $dodawanie = true, $zalogowany = false ) {

        $email = ((isset($tablica['email'])) ? $tablica['email'] : '');
        
        $data = array('clientId' => $this->clientId,
                      'apiKey' => $this->apiKey, 
                      'requestTime' => ceil(microtime(true) * 1000), 
                      'sha' => sha1($this->apiKey . $this->clientId . $this->apiSecret), 
                      'owner' => $this->owner,
                      'lang' => $_SESSION['domyslnyJezyk']['kod'],
                      'useApiDoubleOptIn' => true);
                      
        if ( $dodawanie == true ) {
             //             
             $data['contact'] = array('email' => $email);
             //
        }             
                      
        if ( isset($tablica['smclient']) ) {
             //             
             $data['contactId'] = $tablica['smclient'];
             //
        }                      
        
        // newsletter
        if ( isset($tablica['newsletter']) ) {
             //
             if ( $tablica['newsletter'] == 'tak' ) {
                  //
                  $data['forceOptIn'] = true;
                  $data['forceOptOut'] = false;
                  $data['forcePhoneOptIn'] = true;
                  $data['forcePhoneOptOut'] = false;
                  //
             } else {
                  //
                  $data['forceOptIn'] = false;
                  $data['forceOptOut'] = true;
                  $data['forcePhoneOptIn'] = false;
                  $data['forcePhoneOptOut'] = true;
                  //
             }                  
                  
        }
                      
        if ( isset($tablica['nazwa']) && !empty($tablica['nazwa']) ) {
             $data['contact']['name'] = $tablica['nazwa'];
        }
        if ( isset($tablica['firma']) && !empty($tablica['firma']) ) {
             $data['contact']['company'] = $tablica['firma'];
        }        
        if ( isset($tablica['telefon']) && !empty($tablica['telefon']) ) {
             $data['contact']['phone'] = $tablica['telefon'];
        } 
        if ( isset($tablica['ulica']) && !empty($tablica['ulica']) ) {
             $data['contact']['address']['streetAddress'] = $tablica['ulica'];
        } 
        if ( isset($tablica['kod_pocztowy']) && !empty($tablica['kod_pocztowy']) ) {
             $data['contact']['address']['zipCode'] = $tablica['kod_pocztowy'];
        } 
        if ( isset($tablica['miasto']) && !empty($tablica['miasto']) ) {
             $data['contact']['address']['city'] = $tablica['miasto'];
        } 
        if ( isset($tablica['kraj']) && !empty($tablica['kraj']) ) {
             $data['contact']['address']['country'] = $tablica['kraj'];
        } 
        
        if ( isset($tablica['tags']) ) {
             //
             $tmp_tablica = array();
             foreach ( explode(",", (string)$tablica['tags']) as $tmp ) {
                 //
                 $tmp_tablica[] = str_replace(' ', '_', (string)$tmp);
                 //
             }
             //        
             $data['tags'] = $tmp_tablica;
             //
        }
        if ( isset($tablica['removeTags']) ) {
             $data['removeTags'] = explode(",", (string)$tablica['removeTags']);
        }

        $json = json_encode($data);

        if ( $dodawanie == true ) {
             //
             $result = $this->ZapytaniePost('https://' . $this->endpoint .'/api/contact/insert', $json);
             //
          } else {
             //
             $result = $this->ZapytaniePost('https://' . $this->endpoint .'/api/contact/update', $json);
             //
        }
        
        $r = json_decode($result);
        
        /*
        $log = fopen('xml/sales.txt', 'a+');
        if ( $dodawanie == true ) {
             fwrite($log, $json. PHP_EOL);
             fwrite($log, 'contact/insert - ' . $result. PHP_EOL);
             fwrite($log, '-----------------------'. PHP_EOL);
        } else {
             fwrite($log, $json. PHP_EOL);
             fwrite($log, 'contact/update - ' . $result. PHP_EOL);
             fwrite($log, '-----------------------'. PHP_EOL);
        }
        fclose($log);          
        */

        if ( $dodawanie == true ) {
          
             if ( isset($r->{'contactId'}) ) {
                  //
                  $id = $r->{'contactId'};
                  if ( $id != '' && $zalogowany == false ) {
                       //
                       setcookie('smclient', $id, time()+31556926, '/');
                       //
                  }
                  return $id;        
                  //
             }
        
        } else {
          
             return $r;
             
        }
        
    }
    
    public function CzyJestKlient( $tablica = array(), $zalogowany = false ) {

        $email = $tablica['email'];

        $data = array('clientId' => $this->clientId,
                      'apiKey' => $this->apiKey, 
                      'requestTime' => ceil(microtime(true) * 1000),
                      'sha' => sha1($this->apiKey . $this->clientId . $this->apiSecret), 
                      'owner' => $this->owner,
                      'email' => $email  
        );        

        $json = json_encode($data);

        $result = $this->ZapytaniePost('https://' . $this->endpoint .'/api/contact/hasContact', $json);
        
        $r = json_decode($result);
        
        /*
        $log = fopen('xml/sales.txt', 'a+');
        fwrite($log, $json. PHP_EOL);
        fwrite($log, 'contact/hasContact - ' . $result. PHP_EOL);
        fwrite($log, '-----------------------'. PHP_EOL);    
        fclose($log);
        */
        
        if ( isset($r->{'contactId'}) ) {
             //
             $id = $r->{'contactId'};
             if ( $id != '' && $zalogowany == false ) {
                  //
                  setcookie('smclient', $id, time()+31556926, '/');
                  //
             }
             return $r->{'contactId'};        
             //
        } else {
             //
             return '';
             //
        }
        
    }    
        
    
    public function UsunKlienta( $tablica = array() ) {
      
        $email = $tablica['email'];

        $data = array('clientId' => $this->clientId,
                      'apiKey' => $this->apiKey, 
                      'requestTime' => ceil(microtime(true) * 1000),
                      'sha' => sha1($this->apiKey . $this->clientId . $this->apiSecret), 
                      'owner' => $this->owner,
                      'email' => $email,      
                      'permanently' => true  // tu proszę wpisać true/false w zależności od tego, czy kontakt ma być usunięty permanentnie     
        );        
        
        $json = json_encode($data);

        $result = $this->ZapytaniePost('https://' . $this->endpoint .'/api/contact/delete', $json);
        
        $r = json_decode($result);
        
        /*
        $log = fopen('xml/sales.txt', 'a+');
        fwrite($log, $json. PHP_EOL);
        fwrite($log, 'contact/delete - ' . $result. PHP_EOL);
        fwrite($log, '-----------------------'. PHP_EOL);          
        fclose($log);
        */
        
        return $r;
        
    }    
    
    public function DodajZdarzenieKlienta( $tablica = array() ) {
      
        $opis = $tablica['opis'];

        $event = array('date' => ceil(microtime(true) * 1000));
        
        if ( isset($_COOKIE["sm_" . strtolower((string)$tablica['typ'])]) ) {
             $event = array( 'date' => ceil(microtime(true) * 1000), 'eventId' => $_COOKIE["sm_" . strtolower((string)$tablica['typ'])] );
        }

        $data = array('clientId' => $this->clientId,
                      'apiKey' => $this->apiKey, 
                      'requestTime' => ceil(microtime(true) * 1000),
                      'sha' => sha1($this->apiKey . $this->clientId . $this->apiSecret), 
                      'owner' => $this->owner,  
                      'contactEvent' => array('description' => $opis,
                                              'products' => implode(',', (array)$tablica['produkty_id']),
                                              'location' => str_replace( array('http://', 'https://'), '', (string)ADRES_URL_SKLEPU),
                                              'detail1' => implode('/', (array)$tablica['produkty_nazwy']),
                                              'contactExtEventType' => $tablica['typ'],
                                              'shopDomain' => str_replace( array('http://', 'https://'), '', (string)ADRES_URL_SKLEPU))
         
        );     
        
        if ( isset($tablica['wartosc']) ) {
             //
             $data['contactEvent']['value'] = $tablica['wartosc'];
             //
        }
        
        $data['contactEvent'] = array_merge($event, $data['contactEvent']);
        
        if ( !isset($_COOKIE['sm_cart']) || $tablica['typ'] == 'PURCHASE' ) {
            
            if ( isset($tablica['smclient']) ) {
                 //             
                 $data['contactId'] = $tablica['smclient'];
                 //          
            } else if ( isset($tablica['email']) ) {
                 //
                 $data['email'] = $tablica['email'];
                 //
            }
            
        } else {
          
            $data['contactEvent']['eventId'] = $_COOKIE['sm_cart'];
          
        }

        $json = json_encode($data);

        if ( !isset($_COOKIE['sm_cart']) || $tablica['typ'] == 'PURCHASE' ) {
          
             $result = $this->ZapytaniePost('https://' . $this->endpoint .'/api/v2/contact/addContactExtEvent', $json);
             
        } else {
          
             $result = $this->ZapytaniePost('https://' . $this->endpoint .'/api/v2/contact/updateContactExtEvent', $json);
             
        }
        
        $r = json_decode($result); 
        
        /*
        if ( !isset($_COOKIE['sm_cart']) || $tablica['typ'] == 'PURCHASE' ) {
          
            $log = fopen('xml/sales.txt', 'a+');
            fwrite($log, $json. PHP_EOL);
            fwrite($log, 'contact/addContactExtEvent - ' . $result. PHP_EOL);
            fwrite($log, '-----------------------'. PHP_EOL); 
            fclose($log);
            
        } else {
          
            $log = fopen('xml/sales.txt', 'a+');
            fwrite($log, $json. PHP_EOL);
            fwrite($log, 'contact/updateContactExtEvent - ' . $result. PHP_EOL);
            fwrite($log, '-----------------------'. PHP_EOL); 
            fclose($log);


        }          
        */
        
        $eventid = '';
        if ( isset($r->{'eventId'}) && $tablica['typ'] = 'CART' ) {
             //
             if ( !isset($_COOKIE["sm_" . strtolower((string)$tablica['typ'])]) ) {
                  $eventid = $r->{'eventId'}; 
                  setcookie("sm_" . strtolower((string)$tablica['typ']), $eventid, time() + 86400, '/');
                  unset($eventid);
             }
             //
        }        

        return $r;
      
    }
    
} 

?>