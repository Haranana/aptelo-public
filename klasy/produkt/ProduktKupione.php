<?php

if ( isset($pobierzFunkcje) ) {

    $TablicaZakupow = array();
    $IloscSztuk = 0;
    
    $zapytanie = "SELECT DISTINCT o.orders_id, o.date_purchased, o.customers_name, op.products_quantity FROM orders o
                       RIGHT JOIN orders_products op ON o.orders_id = op.orders_id AND products_id = '" . $this->id_produktu . "'
                            WHERE o.orders_id > 0
                         ORDER BY o.date_purchased DESC";
    
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
    
        while ( $zakupy = $sql->fetch_assoc() ) {
          
            $nazwa = $zakupy['customers_name'];
            $nazwaStart = mb_substr((string)$nazwa,0,2, "utf-8");
            $nazwaKoniec = mb_substr((string)$nazwa,-2,2, "utf-8");
            $TablicaZakupow[] = array('id_zamowienia' => $zakupy['orders_id'],
                                      'data_zamowienia' => date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($zakupy['date_purchased'])),
                                      'klient' => $nazwaStart . '...' . $nazwaKoniec,
                                      'ilosc' => $zakupy['products_quantity']
            );

            $IloscSztuk = $IloscSztuk + $zakupy['products_quantity'];
          
        }
        
        unset($zakupy);        
        
    }
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);

}
       
?>