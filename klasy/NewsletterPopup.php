<?php

class NewsletterPopup { 

    public static function DodajKuponNewslettera( $adresEmail ) {
    
        // pobiera konfiguracje modulu stalego newsletter popup
        $zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'newsletter_popup.php'";        
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
          
            while ( $info = $sql->fetch_assoc() ) {
                //
                if ( !defined($info['modul_settings_code']) ) {
                     define( $info['modul_settings_code'], $info['modul_settings_value'] );
                }
                //
            }    
            
            unset($info);
            
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);      
    
        // zapisywanie kuponu do bazy
        
        $DopuszczalneZnaki = '1234567890QWERTYUIOPASDFGHJKKLZXCVBNM';
        $KodKuponu = '';
        for ($i=0; $i <= 10; $i++)
        {
            $KodKuponu .= $DopuszczalneZnaki[rand()%(strlen((string)$DopuszczalneZnaki))];
        }     
        unset($DopuszczalneZnaki);
        
        //            
        $pola = array(
                array('coupons_status','1'),
                array('coupons_name',$KodKuponu),
                array('coupons_description','Kupon za zapisanie do newslettera, email: ' . $adresEmail),
                array('coupons_discount_type',(( NEWSLETTER_KUPON_RODZAJ != 'procent' ) ? 'fixed' : 'percent' )),   
                array('coupons_discount_value',(int)NEWSLETTER_KUPON_WARTOSC),
                array('coupons_min_order',(int)NEWSLETTER_KUPON_MIN_WARTOSC),
                array('coupons_min_quantity','0'),
                array('coupons_max_quantity','0'),
                array('coupons_max_order','0'),
                array('coupons_quantity','1'),
                array('coupons_specials',(( NEWSLETTER_KUPON_PROMOCJE == 'tak' ) ? '1' : '0' )),
                array('coupons_date_added','now()'),
                array('coupons_email',$adresEmail),
                array('coupons_email_type','popup'),
                array('coupons_customers_groups_id',''),
                array('coupons_date_end','0000-00-00'),
                array('coupons_date_start','0000-00-00'),
                
        );

        //			
        $GLOBALS['db']->insert_query('coupons' , $pola);	
        unset($pola);  

        return $KodKuponu;
    
    }
    
    public static function WyslijKuponNewslettera( $kupon, $adresEmail ) {
    
        $danePopup = array();

        $tytul = @unserialize((string)NEWSLETTER_TYTUL_MAIL);
        
        if ( is_array($tytul) ) {
             //
             $danePopup['NEWSLETTER_TYTUL'] = $tytul[(int)$_SESSION['domyslnyJezyk']['id']];
             //
        }
        
        $tresc = @unserialize((string)NEWSLETTER_TRESC_MAIL);
        
        if ( is_array($tresc) ) {
             //
             $danePopup['NEWSLETTER_TRESC'] = $tresc[(int)$_SESSION['domyslnyJezyk']['id']];
             //
        }

        if ( count($danePopup) > 0 ) {
    
            // wyslanie maila
            $email = new Mailing;

            // podmiana linku do wypisania z newslettera
            if ( isset($danePopup['NEWSLETTER_TRESC']) && isset($danePopup['NEWSLETTER_TYTUL']) ) {

                $cont = $danePopup['NEWSLETTER_TRESC'];
                
                if ( strpos((string)$cont, '{PRODUKT_') > -1 ) {
                     //
                     $sqlp = $GLOBALS['db']->open_query("select distinct p.products_id from products p");
                     
                     if ( (int)$GLOBALS['db']->ile_rekordow($sqlp) > 0 ) {

                         while ($infp = $sqlp->fetch_assoc()) {
                              //
                              if ( strpos((string)$cont, '{PRODUKT_' . $infp['products_id']) > -1 ) {
                                   //
                                   $Produkt = new Produkt($infp['products_id']);
                                   //
                                   if ($Produkt->CzyJestProdukt == true) {
                                       //
                                       // nazwa produktu                       
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':NAZWA_PRODUKTU}', (string)$Produkt->info['nazwa'], (string)$cont); 
                                       //
                                       // link                       
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':LINK_NAZWA_PRODUKTU}', '<a href="' . $Produkt->info['adres_seo'] . '">' . $Produkt->info['nazwa'] . '</a>', (string)$cont); 
                                       //
                                       // nr katalogowy                      
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':NR_KATALOGOWY}', (string)$Produkt->info['nr_katalogowy'], (string)$cont); 
                                       //
                                       // kod producenta                    
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':KOD_PRODUCENTA}', (string)$Produkt->info['kod_producenta'], (string)$cont); 
                                       //
                                       // kod ean     
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':KOD_EAN}', (string)$Produkt->info['ean'], (string)$cont); 
                                       //
                                       // zdjecie glowne 200px    
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':ZDJECIE_GLOWNE}', '<img style="width:200px;max-width:200px;height:auto" src="' .ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] . '" alt="' . $Produkt->info['nazwa'] . '" />', (string)$cont); 
                                       // 
                                       // zdjecie glowne XXXpx    
                                       if ( strpos((string)$cont, '{PRODUKT_' . $infp['products_id'] . ':ZDJECIE_GLOWNE:') > -1 ) {
                                            //
                                            $preg = preg_match_all('|{PRODUKT_' . $infp['products_id'] . ':ZDJECIE_GLOWNE:([0-9]+?)px}|', $cont, $matches);
                                            //
                                            foreach ($matches[1] as $pixele) {
                                                //
                                                if ( (int)$pixele > 10 && (int)$pixele < 1500 ) {
                                                      //
                                                      $cont = str_replace('{PRODUKT_' . $infp['products_id'] . ':ZDJECIE_GLOWNE:' . $pixele . 'px}', '<img style="width:' . $pixele . 'px;max-width:' . $pixele . 'px;height:auto" src="' .ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] . '" alt="' . $Produkt->info['nazwa'] . '" />', (string)$cont);
                                                      //
                                                }
                                                //
                                            }
                                            //
                                            unset($preg);
                                            //
                                       }
                                       //                           
                                       // opis     
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':OPIS}', (string)$Produkt->info['opis'], (string)$cont); 
                                       //
                                       // opis krotki
                                       $cont = str_replace( '{PRODUKT_' . $infp['products_id'] . ':OPIS_KROTKI}', (string)$Produkt->info['opis_krotki'], (string)$cont); 
                                       //  
                                   }
                                   //    
                                   unset($Produkt);
                                   //
                              }
                              //
                         }
                         
                     }
                              
                     $GLOBALS['db']->close_query($sqlp);
                     //
                }            
                
                $cont = str_replace('{LINK}', '<a href="'.ADRES_URL_SKLEPU.'/newsletter-wypisz.html/email='.$adresEmail.'">', (string)$cont);
                $cont = str_replace('{/LINK}', '</a>', (string)$cont);            

                define('KUPON_RABATOWY', $kupon);
                
                $nadawca_email   = INFO_EMAIL_SKLEPU;
                $nadawca_nazwa   = INFO_NAZWA_SKLEPU;
                $adresat_email   = $adresEmail;
                $adresat_nazwa   = $adresEmail;
                $temat           = $danePopup['NEWSLETTER_TYTUL'];
                $cc              = '';
                $tekst           = $cont;
                $zalaczniki      = array();
                $szablon         = NEWSLETTER_KUPON_SZABLON_EMAIL;
                $jezyk           = (int)$_SESSION['domyslnyJezyk']['id'];

                $tekst = Funkcje::parsujZmienne($tekst);
                $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);        

                $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);    
                
            }

        }  
    
    }
    
    public static function KuponZaNewsletter( $email = '', $typ = '' ) {
             
        $BylKupon = false;
             
        if ( NEWSLETTER_KUPON_STATUS == 'tak' ) {
            
             $WyslijKupon = false;
          
             if ( $typ == 'popup' && NEWSLETTER_KUPON_KIEDY == 'zapis w newsletterze Popup' || NEWSLETTER_KUPON_KIEDY == 'w każdym' ) {
                  //
                  $WyslijKupon = true;
                  //
             }
          
             if ( $typ == 'modul' && NEWSLETTER_KUPON_KIEDY == 'zapis w module środkowym lub boxie' || NEWSLETTER_KUPON_KIEDY == 'w każdym' ) {
                  //
                  $WyslijKupon = true;
                  //
             }

             if ( $WyslijKupon == true ) {

                  // sprawdzi czy nie byl generowany kupon dla tego maila
                  //
                  $zapytanie_kupon = "SELECT coupons_description FROM coupons WHERE coupons_email = '" . $email . "' AND coupons_email_type = 'popup'";
                  $sql_kupon = $GLOBALS['db']->open_query($zapytanie_kupon); 
                  //
                  if ((int)$GLOBALS['db']->ile_rekordow($sql_kupon) > 0) {
                      $BylKupon = true;
                  }
                  //
                  $GLOBALS['db']->close_query($sql_kupon);
                  unset($zapytanie_kupon);                            
                  
                  if ( $BylKupon == false ) {                            
                  
                      // dodaje kupon do bazy
                      $KodKuponu = NewsletterPopup::DodajKuponNewslettera( $email );
                      
                      // wysyla kupon na maila
                      NewsletterPopup::WyslijKuponNewslettera( $KodKuponu, $email );
                      
                  }               
               
             }
             
        }
        
        return array($WyslijKupon, $BylKupon);
      
    }
    
}

?>