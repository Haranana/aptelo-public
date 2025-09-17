<?php

class MailerLite {

    private $key = INTEGRACJA_MAILERLITE_KEY; 

    // zapisanie do newslettera
    public function ZapiszSubskrybenta( $adres_email = '', $nazwaListy = '' ) {
        global $filtr;
      
        $id_listy = MailerLite::ListaGrup( $nazwaListy );
        
        $url = 'https://connect.mailerlite.com/api/subscribers';
        
        $data = array(
            'email' => $adres_email
        );        
        
        if ( $id_listy > 0 ) {
             $data['groups'] = array($id_listy);
        }
        
        // dodatkowe dane konta klienta
        $zapytanie = "SELECT c.customers_firstname, c.customers_lastname, c.customers_telephone, ab.entry_company, ab.entry_country_id
                        FROM customers c 
                   LEFT JOIN address_book ab ON ab.customers_id = c.customers_id AND c.customers_default_address_id = ab.address_book_id
                       WHERE c.customers_email_address = '" . ((isset($_SESSION['customer_email'])) ? $filtr->process($_SESSION['customer_email']) : $filtr->process($adres_email)) . "'";
                       
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {   
             //
             $info = $sql->fetch_assoc();
             //
             $data['fields'] = array('name' => $info['customers_firstname'],  
                                     'last_name' => $info['customers_lastname'],
                                     'company' => $info['entry_company'],
                                     'country' => Klient::pokazNazwePanstwa($info['entry_country_id']),
                                     'phone' => $info['customers_telephone']);
             //
             unset($info);
             //
        }
            
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);       
        
        $jsonData = json_encode($data);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->key
        ]);

        $response = curl_exec($ch);

        if ( curl_errno($ch) ) {
             return curl_error($ch);
        }

        curl_close($ch);

        return $response;      

    }
    
    // lista grup
    public function ListaGrup( $nazwaListy = '' ) {
      
        $url = 'https://connect.mailerlite.com/api/groups';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->key
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
            
        $grupy = array();
            
        if ( $httpCode == 200 ) {

             $data = json_decode($response, true);

             if ( !empty($data) ) {
              
                 foreach ($data['data'] as $group) {
                      $grupy[ (string)$group['id'] ] = (string)$group['name'];
                 }
                
             }

        }
      
        $id_grupy = 0;
        
        if ( count($grupy) > 0 ) {
          
             foreach ( $grupy as $id_gr => $grupa ) {
                
                if ( $grupa == $nazwaListy ) {
                     return $id_gr;
                }
                
             }
             
        }

        if ( $id_grupy == 0 ) {
          
             $url = 'https://connect.mailerlite.com/api/groups';

             $data = json_encode(['name' => $nazwaListy]);

             $ch = curl_init($url);

             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_POST, true);
             curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
             curl_setopt($ch, CURLOPT_HTTPHEADER, [
                  'Authorization: Bearer ' . $this->key,
                  'Content-Type: application/json'
             ]);

             $response = curl_exec($ch);
             $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             curl_close($ch);

              if ( $httpCode == 201 ) {
                
                   $result = json_decode($response, true);
                   return $result['data']['id'];

              }

        }
        
        return $id_grupy;
      
    }
    
    // wypisanie z newslettera
    public function UsunSubskrybenta( $adres_email = '' ) {
      
        $url = 'https://connect.mailerlite.com/api/subscribers/' . urlencode($adres_email);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->key
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return curl_error($ch);
        }

        curl_close($ch);

        return $response;      

    }    

} 
?>