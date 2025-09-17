<?php

class Ecomail {

    private $key = INTEGRACJA_ECOMAIL_KEY; 

    // zapisanie do newslettera
    public function ZapiszSubskrybenta( $adres_email = '', $nazwaListy = '' ) {
        global $filtr;
      
        $id_listy = Ecomail::ListaGrup( $nazwaListy );
        
        if ( !empty($id_listy) ) {
             
             $data = array(
                'subscriber_data' => array(
                    'email' => $adres_email
                ),
                'update_existing' => true,
                'trigger_autoresponders' => false
             );

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
                  $data['subscriber_data']['name'] = $info['customers_firstname'];
                  $data['subscriber_data']['surname'] = $info['customers_lastname'];
                  $data['subscriber_data']['company'] = $info['entry_company'];
                  $data['subscriber_data']['country'] = Klient::pokazKodPanstwa($info['entry_country_id']);
                  $data['subscriber_data']['phone'] = $info['customers_telephone'];
                  //
                  unset($info);
                   //
             }
                  
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);  

             $url = "https://api2.ecomailapp.cz/lists/" . $id_listy . "/subscribe";
 
             $ch = curl_init($url);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_POST, true);
             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
             curl_setopt($ch, CURLOPT_HTTPHEADER, [
                 'Content-Type: application/json',
                 'key: ' . $this->key
             ]);

            $response = curl_exec($ch);
            curl_close($ch);
            
            return $response;

        }            

    }
    
    // lista grup
    public function ListaGrup( $nazwaListy = '' ) {
      
        $url = 'https://api2.ecomailapp.cz/lists';

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'key: ' . $this->key
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $listy = json_decode($response, true);

        $id_grupy = null;
        
        if ( count($listy) > 0 ) {
        
            foreach ( $listy as $list ) {
              
                if ( isset($list['name']) && $list['name'] === $nazwaListy ) {
                  
                     $id_grupy = (string)$list['id'];
                     break;
                     
                }
                
            }
            
        }
        
        if ( $id_grupy == null ) {
          
             // dodawanie nowej listy
        
             $url = 'https://api2.ecomailapp.cz/lists';
             
             $data = array(
                'name' => $nazwaListy,
                'public_name' => $nazwaListy,
                'from_name' => DANE_NAZWA_FIRMY_SKROCONA,
                'from_email' => INFO_EMAIL_SKLEPU,
                'reply_to' => INFO_EMAIL_SKLEPU
             );

             $ch = curl_init($url);
             
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_POST, true);
             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
             curl_setopt($ch, CURLOPT_HTTPHEADER, [
                 'Content-Type: application/json',
                 'key: ' . $this->key
             ]);

             $response = curl_exec($ch);
             curl_close($ch);        

             // ustalenie id listy
             
             $url = 'https://api2.ecomailapp.cz/lists';

             $ch = curl_init($url);
             
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_HTTPHEADER, [
                 'Content-Type: application/json',
                 'key: ' . $this->key
             ]);

             $response = curl_exec($ch);
             curl_close($ch);

             $listy = json_decode($response, true);

             $id_grupy = null;
             
             if ( count($listy) > 0 ) {
               
                  foreach ( $listy as $list ) {
                    
                      if ( isset($list['name']) && $list['name'] === $nazwaListy ) {
                        
                          $id_grupy = (string)$list['id'];
                          break;
                          
                      }
                      
                  }
                  
             }
             
        }

        return $id_grupy;
      
    }
    
    // wypisanie z newslettera
    public function UsunSubskrybenta( $adres_email = '' ) {
      
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api2.ecomailapp.cz/subscribers/" . $adres_email . "/delete");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'key: ' . $this->key
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;   

    }    

} 
?>