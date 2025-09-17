<?php

class SalesForce {

    private $client_id     = INTEGRACJA_SALESFORCE_CLIENT_ID; 
    private $client_secret = INTEGRACJA_SALESFORCE_CLIENT_SECRET; 
    private $subskrypcja   = INTEGRACJA_SALESFORCE_SUBSCRIPTION;
    private $zrodlo        = INTEGRACJA_SALESFORCE_SOURCE;
    private $adres         = INTEGRACJA_SALESFORCE_URL;
    private $urlAuth       = null;
    public $AuthToken      = null;
    public $headers        = array();

    public function __construct() {
        global $db;

        $this->urlAuth            = 'https://'.$this->adres.'/services/oauth2/token';

        $headers = [
           'Content-Type: application/json',
           'accept: application/json',
           'scope:'
           ];

        $par_token = 'grant_type=client_credentials&client_id='.$this->client_id.'&client_secret='.$this->client_secret;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_URL, $this->urlAuth);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $WynikJsonAuth = curl_exec($ch);

        curl_close($ch);

        $WynikAuth = json_decode($WynikJsonAuth,true);

        if ( isset($WynikAuth) && isset($WynikAuth['access_token']) ) {

            $this->AuthToken = $WynikAuth['access_token'];
            $this->headers = [
                'Content-Type: application/json',
                'accept: application/json',
                'Authorization: Bearer ' . $this->AuthToken
            ];

        } else {
            exit;
        }
    }

    // curl POST
    public function CommandPost($DaneWejsciowe = array()) {
        global $db;
      
        $DaneWejscioweJson = json_encode($DaneWejsciowe);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_URL, "https://".$this->adres."/services/data/v59.0/sobjects/Subscriber__c/");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $WynikJson = curl_exec($ch);

        curl_close($ch);

        return $WynikJson;
      
    }

    // zapisanie do newslettera
    public function ZapiszSubskrybenta($Email, $Imie = '', $Nazwisko = '') {
        global $db;

        $DaneWejsciowe = array(
                         "Subscriber_name__c"    => $Imie,
                         "Subscriber_last_name__c"  => $Nazwisko,
                         "Subscriber_email__c"   => $Email,
                         "Subscriber_source__c"   => $this->zrodlo,
                         "Consent__c" => '',
                         "Subscription__c" => $this->subskrypcja,
                         "OptIn_date__c" => date('Y-m-d\TH:i:s'),
                         "Status__c" => 'Active',
                         );

        $this->CommandPost($DaneWejsciowe);

        return;

    }

    // zmiana emaila klienta
    public function ZmienEmailKlienta( $StaryEmail, $NowyEmail, $Imie, $Nazwisko ) {

        global $db;
      
        // pobranie ID subskrybenta z SalesForce
        $zapytanie = "https://".$this->adres."/services/data/v59.0/queryAll?q=SELECT Id FROM Subscriber__c WHERE Subscriber_email__c='".urlencode($StaryEmail)."' AND Subscription__c='".$this->subskrypcja."' AND Status__c='active'";
        $zapytanie = str_replace(' ', '+', $zapytanie);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_URL, $zapytanie);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $WynikZmianaJson = curl_exec($ch);

        curl_close($ch);

        $WynikZmiana = json_decode($WynikZmianaJson,true);

        $UserId = '';

        // zmiana statusu i dodanie noweg osubskrybenta z nowym adresem email
        if ( is_array($WynikZmiana['records']) && count((array)$WynikZmiana['records']) > 0 ) {
            $UserId = $WynikZmiana['records'][0]['Id'];

            $DaneWejscioweZmiana = array(
                                     "OptOut_date__c" => date('Y-m-d\TH:i:s'),
                                     "Status__c" => 'resigned',
                                   );

            $DaneWejscioweZmianaJson = json_encode($DaneWejscioweZmiana);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($ch, CURLOPT_URL, "https://".$this->adres."/services/data/v59.0/sobjects/Subscriber__c/".$UserId."");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweZmianaJson);    
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $WynikZmianaJson = curl_exec($ch);

            curl_close($ch);

            $DaneWejsciowe = array(
                             "Subscriber_name__c"    => $Imie,
                             "Subscriber_last_name__c"  => $Nazwisko,
                             "Subscriber_email__c"   => $NowyEmail,
                             "Subscriber_source__c"   => $this->zrodlo,
                             "Consent__c" => '',
                             "Subscription__c" => $this->subskrypcja,
                             "OptIn_date__c" => date('Y-m-d\TH:i:s'),
                             "Status__c" => 'Active',
                             );

            $this->CommandPost($DaneWejsciowe);

        }

        return;

    }

    // usuniecie emaila klienta
    public function UsunEmailKlienta( $Email ) {
        global $db;
      
        // pobranie ID subskrybenta z SalesForce
        $zapytanie = "https://".$this->adres."/services/data/v59.0/queryAll?q=SELECT Id FROM Subscriber__c WHERE Subscriber_email__c='".urlencode($Email)."' AND Subscription__c='".$this->subskrypcja."' AND Status__c='active'";
        $zapytanie = str_replace(' ', '+', $zapytanie);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_URL, $zapytanie);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $WynikZmianaJson = curl_exec($ch);

        curl_close($ch);

        $WynikZmiana = json_decode($WynikZmianaJson,true);

        $UserId = '';

        // zmiana statusu i dodanie noweg osubskrybenta z nowym adresem email
        if ( is_array($WynikZmiana['records']) && count((array)$WynikZmiana['records']) > 0 ) {
            $UserId = $WynikZmiana['records'][0]['Id'];

            $DaneWejscioweZmiana = array(
                                     "OptOut_date__c" => date('Y-m-d\TH:i:s'),
                                     "Status__c" => 'resigned',
                                   );

            $DaneWejscioweZmianaJson = json_encode($DaneWejscioweZmiana);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($ch, CURLOPT_URL, "https://".$this->adres."/services/data/v59.0/sobjects/Subscriber__c/".$UserId."");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweZmianaJson);    
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $WynikZmianaJson = curl_exec($ch);
            curl_close($ch);

        }

        return;

    }

} 

?>