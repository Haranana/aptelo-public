<?php
// allegro - id kategorii, nazwa, zdjecie 

$pola = array();

if ((isset($TablicaDane['Allegro_id_kategoria']) && trim((string)$TablicaDane['Allegro_id_kategoria']) != '')) {
    //
    $pola[] = array('products_cat_id_allegro',(int)$TablicaDane['Allegro_id_kategoria']);             
    //
}
if ((isset($TablicaDane['Allegro_nazwa_produktu']) && trim((string)$TablicaDane['Allegro_nazwa_produktu']) != '')) {
    //
    $pola[] = array('products_name_allegro',$filtr->process($TablicaDane['Allegro_nazwa_produktu']));             
    //
}
if ((isset($TablicaDane['Allegro_zdjecie']) && trim((string)$TablicaDane['Allegro_zdjecie']) != '')) {
    //
    $pola[] = array('products_image_allegro',$filtr->process($TablicaDane['Allegro_zdjecie']));             
    //
}
if ((isset($TablicaDane['Allegro_cena']) && trim((string)$TablicaDane['Allegro_cena']) != '')) {
    //
    $pola[] = array('products_price_allegro',(float)$TablicaDane['Allegro_cena']);             
    //
}
if ((isset($TablicaDane['Allegro_waga']) && trim((string)$TablicaDane['Allegro_waga']) != '')) {
    //
    $pola[] = array('products_weight_allegro',(float)$TablicaDane['Allegro_waga']);             
    //
}
// 

if ( count($pola) > 0 ) {

    if ($CzyDodawanie == true) { 
    
        $pola[] = array('products_id',(int)$id_dodanej_pozycji); 
        $db->insert_query('products_allegro_info' , $pola);
        
      } else {
        // 
        $sqlall = $db->open_query("select * from products_allegro_info where products_id = '".(int)$id_aktualizowanej_pozycji."'");  
        //
        if ((int)$db->ile_rekordow($sqlall) > 0) {
          //
          $db->update_query('products_allegro_info' , $pola, ' products_id = ' . (int)$id_aktualizowanej_pozycji);
          //
        } else {
          //
          $pola[] = array('products_id',(int)$id_aktualizowanej_pozycji); 
          $db->insert_query('products_allegro_info' , $pola);
          //
        }
        //
        $db->close_query($sqlall);
        //        
    }

}
    
unset($pola);

?>