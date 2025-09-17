<?php
class IpnListener {
    
    public $use_curl = true;     
    public $force_ssl_v3 = false;
    public $force_ssl_tls = true;
    public $follow_location = false;     
    public $use_ssl = true;      
    public $use_sandbox = false; 
    public $timeout = 30;       
    
    private $post_data = array();
    private $post_uri = '';     
    private $response_status = '';
    private $response = '';

    const PAYPAL_HOST = 'www.paypal.com';
    const SANDBOX_HOST = 'www.sandbox.paypal.com';
    
    protected function curlPost($encoded_data) {

        if ($this->use_ssl) {
            $uri = 'https://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        } else {
            $uri = 'http://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        }
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->follow_location);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        //if ($this->force_ssl_v3) {
        //    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        //}

        if ($this->force_ssl_tls) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        }
        
        $this->response = curl_exec($ch);
        $this->response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        
        if ($this->response === false || $this->response_status == '0') {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            throw new Exception("cURL error: [$errno] $errstr");
        }
    }
    
    private function getPaypalHost() {
        if ($this->use_sandbox) return self::SANDBOX_HOST;
        else return self::PAYPAL_HOST;
    }
    
    public function getPostUri() {
        return $this->post_uri;
    }
    
    public function getResponse() {
        return $this->response;
    }
    
    public function getResponseStatus() {
        return $this->response_status;
    }
    
    public function getTextReport() {
        return $this->post_data;
    }
    
    public function processIpn($post_data=null) {

        $encoded_data = 'cmd=_notify-validate';
        
        if ($post_data === null) { 
            // use raw POST data 
            if (!empty($_POST)) {
                $this->post_data = $_POST;
                $encoded_data .= '&'.file_get_contents('php://input');
            } else {
                throw new Exception("No POST data found.");
            }
        } else { 
            // use provided data array
            $this->post_data = $post_data;
            
            foreach ($this->post_data as $key => $value) {
                $encoded_data .= "&$key=".urlencode($value);
            }
        }

        if ($this->use_curl) $this->curlPost($encoded_data); 
        else $this->fsockPost($encoded_data);
        
        if (strpos((string)$this->response_status, '200') === false) {
            throw new Exception("Invalid response status: ".$this->response_status);
        }
        
        if (strpos((string)$this->response, "VERIFIED") !== false) {
            return true;
        } elseif (strpos((string)$this->response, "INVALID") !== false) {
            return false;
        } else {
            throw new Exception("Unexpected response from PayPal.");
        }
    }
    
    public function requirePostMethod() {
        // require POST requests
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Allow: POST', true, 405);
            throw new Exception("Invalid HTTP request method.");
        }
    }
}

