<?php

/**
 * Getall.pl API connector class
 * 
 * @author Getall.pl
 * @copyright Getall.pl
 * @version 1.0
 * @since 2015-09-15
 */
 
class GetAll {
  
  private $api_key = null;
  private $url = 'https://www.getall.pl/api/';

  public function __construct($api_key) {
      $this->api_key = $api_key;
  }
  
  public function __call($name, $arguments) { 
    
      $arguments = ((isset($arguments[0])) ? $arguments[0] : $arguments);
      $request = array();
      $arguments["key"] = $this->api_key;
      $arguments["method"] = $name;

      foreach($arguments as $k=>$v) $request[] = $k."=".urlencode($v);	
      
      $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
      
      if($ch = curl_init()) {
      
          curl_setopt($ch, CURLOPT_URL,$this->url);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($ch, CURLOPT_POST,1);
          curl_setopt($ch, CURLOPT_POSTFIELDS,join("&",$request));

          if ($result = curl_exec ($ch)) {		

              return json_decode($result);
      
          }
      }
 	
	}
  
  public function PobierzListy() {
    
     $user_lists = $this->ResponderGetLists();
     
     $lists_array = array();
     
     if ($user_lists -> status == 'ok') {
       
        foreach( $user_lists -> data as $lista ) {
           //
           $lists_array[] = array('id' => $lista -> listid, 'nazwa' => $lista -> name);
           //
         }
         
     } else {
      
        $lists_array = $user_lists -> error;	    
        
     }
     
     return $lists_array;
    
  }
  
  public function DodajSubskrybenta($email = '', $imie = '', $lista = 0) {
    
     $request = array(
        "listid" => $lista,
        "name" => ((empty($imie)) ? '' : $imie),
        "email" => $email,
        "queue" => 1
     );
 
     $subscriber = $this->ResponderAddSubscriber($request);
 
     if ($subscriber -> status == 'ok') {
         //
         return $subscriber->id;
         //
     } else {
         //
         return $subscriber->error;		 
     }
    
  }  
  
  public function UsunSubskrybenta($email = '', $lista = 0) {
    
     $grupy_klienta = array();
     
     // jezeli ze wszystkich grup
     if ( $lista == 0 ) {
       
         $tablica_list = array(array('id' => INTEGRACJA_GETALL_DOMYSLNA_LISTA));
         
         if ( INTEGRACJA_GETALL_WLACZONY_PRODUKTY == 'tak' ) {  
              $tablica_list[] = array('id' => INTEGRACJA_GETALL_PRODUKTY_PREFIX);
         }
         if ( INTEGRACJA_GETALL_WLACZONY_KUPUJACY == 'tak' ) {  
              $tablica_list[] = array('id' => INTEGRACJA_GETALL_KUPUJACY_PREFIX);
         }
         if ( INTEGRACJA_GETALL_WLACZONY_REJESTRACJA == 'tak' ) {  
              $tablica_list[] = array('id' => INTEGRACJA_GETALL_REJESTRACJA_PREFIX);
         }

     } else {

         $tablica_list = array(array('id' => $lista));

     }     
         
     if ( is_array($tablica_list) && count($tablica_list) > 0 ) {
       
         foreach ( $tablica_list as $lista ) {
       
             $request = array("listid" => $lista['id']);
             
             $subscribers = $this->ResponderGetSubscriber($request);
             
             if ($subscribers->status == 'ok') {
                //
                if ( isset($subscribers->data) ) {
                  
                    for ( $i = 0; $i < count($subscribers->data); $i++ ) {
                          //
                          if ( $subscribers->data[$i]->email == $email ) {
                               //
                               if ( !in_array($lista['id'] . '-' . $subscribers->data[$i]->id, $grupy_klienta) ) {
                                    //
                                    $grupy_klienta[$lista['id'] . '-' . $subscribers->data[$i]->id] = array('id_listy' => $lista['id'], 'id_klienta' => $subscribers->data[$i]->id);
                                    //
                               }
                               //
                          }
                          //
                    }
                    
                }
                //
             } 

         }                  
       
     }
     
     $bledy = array();
     
     if ( count($grupy_klienta) > 0 ) {
       
          foreach ( $grupy_klienta as $tmp ) {
            
              $request = array("id" => $tmp['id_klienta']);
         
              $delete_subscriber = $this->ResponderDeleteSubscriber($request);
         
              if ( $delete_subscriber->status == 'ok' ) {
                   //
                   $bledy[] = 'ok';
                   //
              } else {
                   //
                   $bledy[] = $delete_subscriber->error;	
                   //
              }
              
          }              
       
     }

  }   

}
?>