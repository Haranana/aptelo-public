<?php
chdir('../');            

if (isset($_POST['wartosc']) && (int)$_POST['wartosc'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
      
        $SystemyRatalneAktywne = Funkcje::AktywneSystemyRatalne();

        $SystemyRatalne = array();
      
        if ( isset($SystemyRatalneAktywne['platnosc_payu']) ) {
            $zapytanie = "SELECT * FROM `modules_payment_params` WHERE `kod` LIKE '%PLATNOSC_PAYU_%' AND `kod` NOT LIKE '%PLATNOSC_PAYU_REST%'";
        }
        if ( isset($SystemyRatalneAktywne['platnosc_payu_rest']) ) {
            $zapytanie = "SELECT * FROM `modules_payment_params` WHERE `kod` LIKE '%PLATNOSC_PAYU_REST%'";
        }
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        while ($info = $sql->fetch_assoc()) {
               //
               $SystemyRatalne['platnosc_payu'][ $info['kod'] ] = $info['wartosc'];
               //
        }      

        if ( isset($SystemyRatalneAktywne['platnosc_payu']) ) {
            if ( isset($SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_KALKULATOR']) && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_KALKULATOR'] == 'tak' && isset($SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_WLACZONE']) && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_WLACZONE'] == 'tak' ) {
              
                if ( $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {
                               
                    echo '
                    <div style="margin-bottom:5px;display:block" id="PayuKoszykImg"><img src="'.KATALOG_ZDJEC . '/platnosci/raty_payu_small_grey.png" alt="Raty PayU" /></div>
                    <div class="RatyP"><p>Rata już od: <span id="installment-mini"></span> miesięcznie</p></div>
                    
                    <script type="text/javascript">
                    
                          var value = ' . (int)$_POST['wartosc'] . ';
                          if (value >= 300 && value <= 50000) {
                            var options = {
                              creditAmount: value, 
                              posId: \'' . $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_POS_ID'] . '\', 
                              key: \'' . substr((string)$SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_KEY_1'], 0, 2) . '\', 
                              showLongDescription: false
                            };
                            OpenPayU.Installments.miniInstallment(\'#installment-mini\', options)
                                .then(function(result) {
                                    $(\'#RatyPayuWidget\').show(); 
                                });
                          } else {
                              $(\'#RatyPayuWidget\').hide(); 
                          }                        
                    
                    </script>';    

                }                

            }            
        }

        if ( isset($SystemyRatalneAktywne['platnosc_payu_rest']) ) {
            if ( isset($SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_REST_RATY_KALKULATOR']) && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_REST_RATY_KALKULATOR'] == 'tak' && isset($SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_REST_RATY_WLACZONE']) && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_REST_RATY_WLACZONE'] == 'tak' ) {
              
                if ( $_SESSION['domyslnyJezyk']['kod'] == 'pl' && $_SESSION['domyslnaWaluta']['kod'] == 'PLN' ) {
                               
                    echo '
                    <div style="margin-bottom:5px;display:block" id="PayuKoszykImg"><img src="'.KATALOG_ZDJEC . '/platnosci/raty_payu_small_grey.png" alt="Raty PayU" /></div>
                    <div class="RatyP"><p>Rata już od: <span id="installment-mini"></span> miesięcznie</p></div>
                    
                    <script type="text/javascript">
                    
                          var value = ' . (int)$_POST['wartosc'] . ';
                          if (value >= 300 && value <= 50000) {
                            var options = {
                              creditAmount: value, 
                              posId: \'' . $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_REST_POS_ID'] . '\', 
                              key: \'' . substr((string)$SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_REST_OAUTH_SECRET'], 0, 2) . '\', 
                              showLongDescription: false
                            };
                            OpenPayU.Installments.miniInstallment(\'#installment-mini\', options)
                                .then(function(result) {
                                    $(\'#RatyPayuWidget\').show(); 
                                });
                          } else {
                              $(\'#RatyPayuWidget\').hide(); 
                          }                        
                    
                    </script>';    

                }                

            }            
        }

        $GLOBALS['db']->close_query($sql); 
        unset($info, $zapytanie);

    }
    
}
?>
    
