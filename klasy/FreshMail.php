<?php

class FreshMail {

    private $key = INTEGRACJA_FRESHMAIL_KEY; 
    private $secret = INTEGRACJA_FRESHMAIL_SEKRET; 
    
    // zapisanie do newslettera
    public function ZapiszSubskrybenta( $adres_email = '', $aktywacja = 1, $nazwaListy = '', $bezPowrotu = false ) {
      
        $mailing = new FmRestApi();
        $mailing->setApiKey( $this->key );
        $mailing->setApiSecret( $this->secret );      
        
        $hash = FreshMail::ListaOdbiorcowHash( $nazwaListy );
      
        // 'confirm' => 1 - wyslanie linku aktywacyjnego - dziala tylko ze state = 2
        $data = array(
            'email' => $adres_email,
            'list' => $hash,
            'state' => $aktywacja  // 1 - aktywny , 2 - do aktywacji, 4 - wypisany
        );      

        try {
            //
            $wynik = $mailing->doRequest('subscriber/add', $data);
            return $wynik;
            //
        } catch (Exception $e) {
            //
            if ( $bezPowrotu == false ) {
                //
                // usuwa subskrybenta
                FreshMail::UsunSubskrybenta( $adres_email, $nazwaListy );
                //
                // dodaje
                FreshMail::ZapiszSubskrybenta( $adres_email, 1, $nazwaListy, true );
                //
            }
            //
            return array('status' => 'ERROR',
                         'info' => $e->getMessage(),
                         'kod_bledu' => $e->getCode(),
                         'kod_http' => $mailing->getHttpCode());
            //
        }        
      
    }
    
    // wypisanie z newslettera
    public function UsunSubskrybenta( $adres_email = '', $nazwaListy = '', $ustalHash = true ) {
      
        $mailing = new FmRestApi();
        $mailing->setApiKey( $this->key );
        $mailing->setApiSecret( $this->secret );   
        
        if ( $nazwaListy != '' ) {

            if ( $ustalHash == true ) {
                 //
                 $hash = FreshMail::ListaOdbiorcowHash( $nazwaListy );
                 //
              } else {
                 //
                 $hash = $nazwaListy;
                 //
            }
          
            $data = array(
                'email' => $adres_email,
                'list' => $hash
            );      

            try {
                //
                $wynik = $mailing->doRequest('subscriber/delete', $data);
                return $wynik;
                //
            } catch (Exception $e) {
                //
                return array('status' => 'ERROR',
                             'info' => $e->getMessage(),
                             'kod_bledu' => $e->getCode(),
                             'kod_http' => $mailing->getHttpCode());
                //
            }  

        }
        
        // usuwa ze wszystkich list
        $TablicaList = FreshMail::ListyOdbiorcow();
        //
        if ( isset($TablicaList['lists']) && count($TablicaList['lists']) > 0 ) {
            //
            foreach ( $TablicaList['lists'] as $Lista ) {
                //
                FreshMail::UsunSubskrybenta( $adres_email, $Lista['subscriberListHash'], false );
                //
            }
            //
        }        
      
    }    

    // pobranie list odbiorcow
    public function ListyOdbiorcow() {
      
        $mailing = new FmRestApi();
        $mailing->setApiKey( $this->key );
        $mailing->setApiSecret( $this->secret );

        try {
            //
            $wynik = $mailing->doRequest('subscribers_list/lists');
            return $wynik;
            //
        } catch (Exception $e) {
            //
            return array('status' => 'ERROR',
                         'info' => $e->getMessage(),
                         'kod_bledu' => $e->getCode(),
                         'kod_http' => $mailing->getHttpCode());
            //
        }        
      
    }    
    
    // zwraca hash listy
    public function ListaOdbiorcowHash( $nazwa = '' ) {
        //
        $TablicaList = FreshMail::ListyOdbiorcow();
        //
        $JestLista = false;
        $HashListy = '';
        //
        // szuka czy dana lista istnieje
        if ( isset($TablicaList['lists']) && count($TablicaList['lists']) > 0 ) {
            //
            foreach ( $TablicaList['lists'] as $Lista ) {
                //
                if ( $Lista['name'] == trim((string)$nazwa) ) {
                     //
                     $JestLista = true;
                     $HashListy = $Lista['subscriberListHash'];
                     //
                }
                //
            }
            //
        }
        //
        // jezeli nie znalazl listy utworzy nowa
        if ( $JestLista == false ) {
            //
            $HashNowejListy = FreshMail::ListaOdbiorcowDodaj( trim((string)$nazwa) );
            //
            if ( isset($HashNowejListy['hash']) && trim((string)$HashNowejListy['hash']) != '' ) {
                //
                $HashListy = $HashNowejListy['hash'];
                //
            }
            //
        }
        //
        return $HashListy;
        //
      
    }
    
    // dodaje nowa liste odbiorcow
    public function ListaOdbiorcowDodaj( $nazwa = '' ) {
        //
        $mailing = new FmRestApi();
        $mailing->setApiKey( $this->key );
        $mailing->setApiSecret( $this->secret );
        
        $data = array('name' => $nazwa);         

        try {
            //
            $wynik = $mailing->doRequest('subscribers_list/create', $data);
            return $wynik;
            //
        } catch (Exception $e) {
            //
            return array('status' => 'ERROR',
                         'info' => $e->getMessage(),
                         'kod_bledu' => $e->getCode(),
                         'kod_http' => $mailing->getHttpCode());
            //
        }           
        //
    }

} 

?>