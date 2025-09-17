<?php
/**
 * Build a configuration array to pass to `Hybridauth\Hybridauth`
 */
 
// wczytanie ustawien inicjujacych system
if ( ( isset($_GET['provider']) && ($_GET['provider'] == 'Facebook' || $_GET['provider'] == 'Google') ) || isset($_GET['code']) || isset($_GET['logout']) ) {
       //
       chdir('../../');
       require_once('ustawienia/init_ajax.php');
       //
       $zapytanie = 'select code, value, js_type from settings';
       $sql = $GLOBALS['db']->open_query($zapytanie);
       //
       while ($info = $sql->fetch_assoc()) { 
        
           if ( !defined($info['code']) ) {
                define($info['code'], $info['value']);
           }
                 
        }
        //
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);       
        //
}

$link_social = '';

if ( isset($_GET['powrot']) ) {
     //
     switch ($_GET['powrot']) {
        case 'logowanie':
            $link_social = 'logowanie';
            break;              
        case 'rejestracja':
            $link_social = 'rejestracja';
            break;   
        case 'dane_adresowe':
            $link_social = 'dane_adresowe';
            break;               
     }
     //
}
if ( $link_social != '' ) {
     //
     $_SESSION['aktualnaStrona'] = $link_social;
     //
}
if ( isset($_GET['provider']) && ($_GET['provider'] == 'Facebook' || $_GET['provider'] == 'Google') ) {
     //
     if ( $_GET['provider'] == 'Facebook' ) {
           $_SESSION['socialTyp'] = 'facebook';
     }
     if ( $_GET['provider'] == 'Google' ) {
          $_SESSION['socialTyp'] = 'google';
     }            
     //
}
unset($sciezka_social, $link_social);

$config = array(
    'callback' => ((defined('ADRES_URL_SKLEPU')) ? ADRES_URL_SKLEPU : '') . '/programy/socialMedia/socialmedia.php',
    'providers' => array()
);

if ( defined('INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY') && INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY == 'tak' ) {
     //
     $config['providers']['Google'] = array(
                                          'enabled' => true,
                                          'keys' => [
                                              'id' => ((defined('INTEGRACJA_GOOGLE_LOGOWANIE_IDENTYFIKATOR')) ? INTEGRACJA_GOOGLE_LOGOWANIE_IDENTYFIKATOR : ''),
                                              'secret' => ((defined('INTEGRACJA_GOOGLE_LOGOWANIE_KLUCZ')) ? INTEGRACJA_GOOGLE_LOGOWANIE_KLUCZ : ''),
                                          ],
                                      );
}
if ( defined('INTEGRACJA_FB_LOGOWANIE_WLACZONY') && INTEGRACJA_FB_LOGOWANIE_WLACZONY == 'tak' ) {
     //
     $config['providers']['Facebook'] = array(
                                            'enabled' => true,
                                            'keys' => [
                                                'id' => ((defined('INTEGRACJA_FB_LOGOWANIE_IDENTYFIKATOR')) ? INTEGRACJA_FB_LOGOWANIE_IDENTYFIKATOR : ''),
                                                'secret' => ((defined('INTEGRACJA_FB_LOGOWANIE_SECRET')) ? INTEGRACJA_FB_LOGOWANIE_SECRET : ''),
                                            ],
                                        );
}
