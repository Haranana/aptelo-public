<?php

class Mailjet {

    private $key = INTEGRACJA_MAILJET_KEY; 
    private $secret = INTEGRACJA_MAILJET_SECRET; 

    // zapisanie do newslettera
    public function ZapiszSubskrybenta( $adres_email = '', $nazwaListy = '', $dodanie = false ) {
        global $filtr;
      
        $id_listy = Mailjet::ListaGrup( $nazwaListy );
            
        if ( $dodanie == false ) {

             if ( !empty($id_listy) ) {
                 
                 $url = 'https://api.mailjet.com/v3/REST/contact';

                 $data = array(
                      'Email' => $adres_email,
                      'IsExcludedFromCampaigns' => false 
                 );
                 
                 $ch = curl_init();
                 
                 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                 curl_setopt($ch, CURLOPT_POST, true);
                 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                 curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                 curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);

                 $response = curl_exec($ch);
                 $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                 
                 curl_close($ch);

                 if ( $httpCode == 201 || $httpCode == 304 ) { 
                  
                      $url = "https://api.mailjet.com/v3/REST/contactslist/" . $id_listy . "/managecontact";

                      $data = [
                          'Email' => $adres_email,
                          'Action' => 'addnoforce'
                      ];

                      $ch = curl_init();
                      
                      curl_setopt($ch, CURLOPT_URL, $url);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($ch, CURLOPT_POST, true);
                      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                      curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);

                      $response = curl_exec($ch);
                      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                      
                      curl_close($ch);

                      if ( $httpCode == 201 ) {
                            
                           return true;
                      
                      } else {
                          
                           return false;
                            
                      }
                      
                 } else {
                   
                      return false;
                      
                 }
                  
             }
                          
        } else {
          
             if ( !empty($id_listy) ) {
               
                  $id_kontaktu = Mailjet::IdSubskrybenta( $adres_email );
         
                  if ( !empty($id_kontaktu) ) {
                    
                       $url = "https://api.mailjet.com/v3/REST/contact/{$id_kontaktu}/managecontactslists";

                       $data = array(
                            "ContactsLists" => [
                                [
                                    "Action" => "addforce",
                                    "ListID" => $id_listy
                                ]
                            ]
                        );

                        $ch = curl_init();
                        
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        
                        curl_close($ch);
                        
                  }
             
             }                  
          
        }
        
        return false;

    }
    
    // lista grup
    public function ListaGrup( $nazwaListy = '' ) {
      
        $url = "https://api.mailjet.com/v3/REST/contactslist";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        $id_grupy = null;

        if ( $httpCode == 200 ) {
          
             $data = json_decode($response, true);
             $lists = $data['Data'];

             foreach ( $lists as $list ) {
               
                  if ( $list['Name'] === $nazwaListy ) {
                    
                      $id_grupy = $list['ID'];

                  }
                  
             }

        }

        if ( $id_grupy == null ) {
          
             // dodawanie nowej listy
             
             $url = 'https://api.mailjet.com/v3/REST/contactslist';

             $data = array(
                  'Name' => $nazwaListy
             );

             $ch = curl_init();
             
             curl_setopt($ch, CURLOPT_URL, $url);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_POST, true);
             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
             curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
             curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);

             $response = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             
             curl_close($ch);

             if ( $httpCode == 201 ) {
               
                  $responseData = json_decode($response, true);
                  $id_grupy = $responseData['Data'][0]['ID'];
                  
             }             
  
        }

        return $id_grupy;
      
    }
    
    // id subskrybenta
    public function IdSubskrybenta( $adres_email = '' ) {
      
        $limit = 100; 
        $offset = 0;  
        $kontaktFound = false;
        
        $id_kontaktu = null;
        
        do {

            $url = 'https://api.mailjet.com/v3/REST/contact?Count=' . $limit . '&Offset=' . $offset;

            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);

            if ($httpCode == 200) {
              
                $kontakty = json_decode($response, true)['Data'];

                foreach ($kontakty as $kontakt) {
                  
                    if ( $kontakt['Email'] == $adres_email ) {
                      
                         $id_kontaktu = $kontakt['ID'];  
                         $kontaktFound = true;

                         return $id_kontaktu;

                    }
                }

                $offset += $limit;

            }
            
        } while (!$kontaktFound && !empty($kontakty)); 

        return $id_kontaktu;

    }     
    
    // wypisanie z newslettera
    public function UsunSubskrybenta( $adres_email = '' ) {
      
        $id_kontaktu = Mailjet::IdSubskrybenta( $adres_email );
         
        if ( !empty($id_kontaktu) ) {
          
             $url = "https://api.mailjet.com/v4/contacts/{$id_kontaktu}";

             $ch = curl_init();
             
             curl_setopt($ch, CURLOPT_URL, $url);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
             curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
             curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);

             $response = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             
             curl_close($ch);  
        
        }

    }    

} 
?>