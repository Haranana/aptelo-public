<?php

if ( isset($pobierzFunkcje) ) {

    if ( KARTA_PRODUKTU_ZAKUP_ALLEGRO == 'tak' ) {
      
        $zapytanie = "SELECT auction_id, products_stock_attributes, allegro_sandbox FROM allegro_auctions WHERE products_id = '" . $this->id_produktu . "' and auction_status = 'ACTIVE' AND (auction_date_end >= now() OR auction_date_end = '1970-01-01 01:00:00') ORDER BY auction_date_start desc";        
        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

            while ($info = $sql->fetch_assoc()) {
                //
                $this->AukcjeAllegro[] = array( 'nr_aukcji' => $info['auction_id'],
                                                'link_aukcji' => 'http://allegro.pl/i' .  $info['auction_id'] . '.html',
                                                'cechy_aukcji' => $info['products_stock_attributes']);
                //            
            }
            
            unset($info);
            
        }
        
        $GLOBALS['db']->close_query($sql); 

        unset($zapytanie);

    }
    
}
    
?>