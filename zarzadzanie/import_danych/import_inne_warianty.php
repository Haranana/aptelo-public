<?php
// warianty produktu po ean, nr kat itd

if ((isset($TablicaDane['Wariant_tekst']) && trim((string)$TablicaDane['Wariant_tekst']) != '')) {
    //
    $pola[] = array('products_other_variant_text',$filtr->process($TablicaDane['Wariant_tekst']));             
    //
    if ((isset($TablicaDane['Wariant_opcja']) && trim((string)$TablicaDane['Wariant_opcja']) != '')) {
        //
        if ( trim((string)$TablicaDane['Wariant_opcja']) == 'ean' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nazwa_produktu' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'id_zewnetrzne' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nr_katalogowy' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'kod_producenta' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nr_referencyjny_1' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nr_referencyjny_2' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nr_referencyjny_3' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nr_referencyjny_4' ||
             trim((string)$TablicaDane['Wariant_opcja']) == 'nr_referencyjny_5' ) {
             //             
             $pola[] = array('products_other_variant_range',$filtr->process($TablicaDane['Wariant_opcja']));             
             //
        } else {
             //
             $pola[] = array('products_other_variant_range','ean'); 
             //          
        }
        //
    } else {
        //
        $pola[] = array('products_other_variant_range','ean'); 
        //
    }
    //
    if ((isset($TablicaDane['Wariant_sposob']) && trim((string)$TablicaDane['Wariant_sposob']) != '')) {
        //
        if ( trim((string)$TablicaDane['Wariant_sposob']) == 'dokladnie' ||
             trim((string)$TablicaDane['Wariant_sposob']) == 'fragment' ) {
             //             
             $pola[] = array('products_other_variant_method',$filtr->process($TablicaDane['Wariant_sposob']));             
             //
        } else {
             //
             $pola[] = array('products_other_variant_method','dokladnie'); 
             //          
        }
        //
    } else {
        //
        $pola[] = array('products_other_variant_method','dokladnie'); 
        //
    }
    //
}
?>