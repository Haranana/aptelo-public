<?php
if ( isset($_GET['plugin']) && isset($_GET['token']) ) {

    chdir('zarzadzanie/');
   
    // wczytanie ustawien inicjujacych system
    require_once( getcwd() . '/ustawienia/init.php' );
    
    // sprawdzi czy jest poprawny plugin i token

    $porownywarki = array();
    //
    $zapytanie = "SELECT * FROM comparisons";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {
            //
            $porownywarki[] = array('plugin' => $info['comparisons_plugin'],
                                    'nazwa_pliku' => ((empty($info['comparisons_file_export'])) ? $info['comparisons_plugin'] : $info['comparisons_file_export']),
                                    'token' => $info['comparisions_cron_token'],
                                    'jest_cron' => $info['comparisions_cron']);
            //
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql);
    
    $jaki_plugin = '';    
    $nazwa_pliku = '';
    $jest_porownywarka = false;
    
    foreach ( $porownywarki as $tmp ) {    
        //
        if ( $tmp['plugin'] == $filtr->process($_GET['plugin']) && $tmp['token'] == $filtr->process($_GET['token']) && (int)$tmp['jest_cron'] == 1 ) {
             //
             $jest_porownywarka = true;
             $jaki_plugin = $tmp['plugin'];
             $nazwa_pliku = $tmp['nazwa_pliku'];
             //
        }
        //
    }
    
    if ( $jest_porownywarka == true ) {

        $_POST['plugin'] = $jaki_plugin;

        $separator = '/';
        
        $czas_start = explode(' ', microtime());
        
        if ( strpos((string)$jaki_plugin, 'googleshopping') > -1 || strpos((string)$jaki_plugin, 'kupujemy') > -1 || strpos((string)$jaki_plugin, 'szoker') > -1 || strpos((string)$jaki_plugin, 'webepartners') > -1 ) {
             //
             $separator = '>';
             //
        }
        if ( strpos((string)$jaki_plugin, 'sklepy24') > -1 ) {
             //
             $separator = ' > ';
             //
        }      
        if ( strpos((string)$jaki_plugin, 'empik') > -1 ) {
             //
             $separator = '/';
             //
        }  
        if ( strpos((string)$jaki_plugin, 'smartbay') > -1 ) {
             //
             $separator = ' / ';
             //
        }        
        
        $ImportZewnetrzny = true;
        
        // okresli ile jest produktow do eksportu
        $porownywarkiIlosc = new Porownywarki($_POST['plugin'], 0, 0, $separator, true);

        if ( $porownywarkiIlosc->IloscRekordow > 0 ) {
          
             $_POST['ilosc_rekordow'] = $porownywarkiIlosc->IloscRekordow;
          
             if ( strpos((string)$jaki_plugin, 'facebook_reklamy') > -1 || strpos((string)$jaki_plugin, 'csv') > -1 ) {
                  //
                  $nazwa_pliku = $nazwa_pliku . '.csv';
                  //
             } else {
                  //                
                  $nazwa_pliku = $nazwa_pliku . '.xml';
                  //
             }
             
             $_POST['nazwa_pliku'] = $nazwa_pliku;

             $plik = KATALOG_SKLEPU . 'xml/' . $nazwa_pliku;
             
             $fp = fopen($plik, "w");

             $podzielnik = ceil($porownywarkiIlosc->IloscRekordow / 1000);

             $_POST['offset'] = 0;

             for ( $x = 1; $x <= $podzielnik; $x++ ) {
               
                  ob_start();

                  $porownywarki = new Porownywarki($_POST['plugin'], ($x - 1) * 1000, 1000, $separator);
                  
                  $podzial = explode('__', (string)$_POST['plugin']);
                  $nazwa_plugin = $podzial[0];                  
                  
                  include( getcwd() . '/porownywarki/plugin/' . $nazwa_plugin . '.php' );

                  unset($porownywarki);              
                  
                  $ok = ob_get_contents();
                  ob_end_clean();      

                  unset($_POST['offset']);

             }
             
             if ( strpos((string)$jaki_plugin, 'facebook_reklamy') === false && strpos((string)$jaki_plugin, 'csv') === false ) {
               
                  ob_start();
                  
                  $podzial = explode('__', (string)$_POST['plugin']);
                  $nazwa_plugin = $podzial[0];                  
                  
                  $ZakonczeniePliku = true;
                  include( getcwd() . '/porownywarki/plugin/' . $nazwa_plugin . '.php' );
                  unset($ZakonczeniePliku);
                  
                  $ok = ob_get_contents();
                  ob_end_clean();  
                 
             }
                  
        }
        
        // aktualizacja statusu porownywarki
        $pola = array(array('comparisons_last_export','now()'),
                      array('comparisons_products_exported', $porownywarkiIlosc->IloscRekordow));
                      
        $GLOBALS['db']->update_query('comparisons' , $pola, " comparisons_id = '" . $porownywarkiIlosc->id_porownywarki . "'");	       

        echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;line-height:1.5">';
        
        echo '<b style="font-size:18px">Zakonczono generowanie ...</b> <br /> plik <a href="' . ADRES_URL_SKLEPU . '/xml/' . $nazwa_pliku . '">' . $nazwa_pliku . '</a>';

        echo '<br /><br />Ilosc wyeksportowanych produktow: ' . $porownywarkiIlosc->IloscRekordow;   
        
        $czas_koniec = explode(' ', microtime());
        echo '<br /><br />Czas generowania pliku: ' . number_format((($czas_koniec[1] + $czas_koniec[0]) - ($czas_start[1] + $czas_start[0])), 3, '.', '') . ' sek';   
        
        echo '</div>';
                
        unset($porownywarkiIlosc);
        
    } else {
      
        echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;">Brak autoryzacji ....</div>';      
      
    }
    
} else {
  
    echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;">Brak autoryzacji ....</div>';      
  
}
?>