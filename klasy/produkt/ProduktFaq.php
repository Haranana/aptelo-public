<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanie = "SELECT faq_question, faq_reply from faq where faq_type = 'produkt' and faq_type_id = '" . $this->id_produktu . "' and language_id = '" . $this->jezykDomyslnyId . "' ORDER BY sort";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['faq_question']) && !empty($info['faq_reply']) ) {
                //
                $this->Faq[] = array( 'pytanie' => $info['faq_question'],
                                      'odpowiedz'  => $info['faq_reply'] );
                // 
            }            
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie);

}
    
?>