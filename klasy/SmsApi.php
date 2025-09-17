<?php

class SmsApi {

    public static function wyslijSms($adresat, $tekst) {
      
        // integracja z SmsApi
        
        if ( SMS_SPOSOB == 'SMSAPI' && $tekst != '' ) {

            $token = SMS_HASLO;
            $url = 'https://api.smsapi.pl/sms.do';
            $komunikat = '';

            if ( SMS_PL_ZNAKI == 'tak' ) {
                 //
                 $plZnaki = array("Ą" => "A", "Ć" => "C", "Ę" => "E", "Ł" => "L", "Ń" => "N", "Ó" => "O", "Ś" => "S", "Ż" => "Z", "Ź" => "Z", "ą" => "a", "ć" => "c", "ę" => "e", "ł" => "l", "ń" => "n", "ó" => "o", "ś" => "s", "ż" => "z", "ź" => "z");
                 $tekst = strtr($tekst, $plZnaki);
                 //
            }
            
            $parameters = array('from' => SMS_NADAWCA,
                                'to' => $adresat,
                                'message' => $tekst,
                                'flash' => ((SMS_FLASH == 'tak') ? 1 : 0),
                                'format' => 'json');

            if ( SMS_PL_ZNAKI == 'tak' ) {
                 //
                 $parameters['normalize'] = 1;
                 //
            } else {
                 //
                 $parameters['encoding'] = 'utf-8';
                 //
            }

            $post_str = http_build_query($parameters);

            $headers = [
                  'Authorization: Bearer: ' . $token
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,20);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer $token"
            ));

            $content = curl_exec($ch);
            curl_close ($ch);

            return;

        }
        
        // integracja z SmsPlanet
        
        if ( SMS_SPOSOB == 'SMSPLANET' && $tekst != '' ) {
        
            $url = 'https://api2.smsplanet.pl/sms';

            $parameters = array('key' => SMSPLANET_API,
                                'password' => SMSPLANET_HASLO,
                                'from' => SMSPLANET_NADAWCA,
                                'to' => $adresat,
                                'msg' => $tekst,
                                'clear_polish' => ((SMS_PL_ZNAKI == 'tak') ? 1 : 0));
            
            $post_str = http_build_query($parameters);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,20);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($ch);
            curl_close ($ch); 

            return; 

        }            
        
    }

}

?>