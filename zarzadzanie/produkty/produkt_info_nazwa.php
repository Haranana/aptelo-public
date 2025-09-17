<?php
// dodatkowa zmienna do wylaczania mozliwosci zmiany statusu produktu jezeli kategoria
// do ktorej nalezy jest wylaczona
$wylacz_status = true;

// nazwa produktu i kategorie do jakich jest przypisany
$do_jakich_kategorii_przypisany = '<span class="MaleInfoKat">';
$kategorie = $db->open_query("select distinct categories_id from products_to_categories where products_id = '".(int)$info['products_id']."'");
//
if ( (int)$db->ile_rekordow($kategorie) > 0 ) {
    while ($id_kategorii = $kategorie->fetch_assoc()) {
        // okreslenie nazwy kategorii
        if ((int)$id_kategorii['categories_id'] == '0') {
            $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
            $wylacz_status = false;
          } else {
            //
            if ( isset($TablicaKategorii[(int)$id_kategorii['categories_id']]) ) {
                //
                $do_jakich_kategorii_przypisany .= '<span style="color:#ff0000">'.$TablicaKategorii[(int)$id_kategorii['categories_id']]['text'].'</span>, ';
                //
                if ($TablicaKategorii[(int)$id_kategorii['categories_id']]['status'] == '1') {
                   $wylacz_status = false;
                }
                //
            }
            //
        }
    }
  } else {
    $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
    $wylacz_status = false;
}
$do_jakich_kategorii_przypisany = substr((string)$do_jakich_kategorii_przypisany,0,-2);
$do_jakich_kategorii_przypisany .= '</span>';

$db->close_query($kategorie);
unset($kategorie);

$nr_kat = '';
if (trim((string)$info['products_model']) != '') {
    $nr_kat = '<span class="MaleNrKatalogowy">Nr kat: <b>'.$info['products_model'].'</b></span>';
}

$kod_producenta = '';
if (trim((string)$info['products_man_code']) != '') {
    $kod_producenta = '<span class="MaleNrKatalogowy">Kod prod: <b>'.$info['products_man_code'].'</b></span>';
}

$kod_ean = '';
if (trim((string)$info['products_ean']) != '') {
    $kod_ean = '<span class="MaleNrKatalogowy">EAN: <b>'.$info['products_ean'].'</b></span>';
}

$zakup = '';
$prd = '';

if ( !isset($zestawy) ) {

    // pobieranie danych o producencie
    if (trim((string)$info['manufacturers_name']) != '') {                     
        //
        $prd = '<span class="MaleProducent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
        //
    }    

    // pobieranie danych o cenie zakupu
    if ( (float)$info['products_purchase_price'] > 0 ) {                     
        //
        $zakup = '<span class="MaleCenaZakupu">Cena zakupu: <b>'.$waluty->FormatujCene($info['products_purchase_price'], false, $info['products_currencies_id']).'</b></span>';
        //
    }    

}    

$id_zew = '';
if ( $info['products_id_private'] != '' && PRODUKTY_LISTING_ID_ZEWNETRZNE == 'tak' ) {
     //
     $id_zew = '<span class="MaleIdZewnetrzne">Id zewnętrzne: <b>'.$info['products_id_private'].'</b></span>';
     //
}
    
// informacja o aukcji
$allegro = '';
if ( !isset($zestawy) ) {
  
    if (trim((string)$info['auction_id']) != '') {                     
        //
        // czy sa aktywane aukcje
        $aukcje = $db->open_query("select allegro_id from allegro_auctions where auction_status = 'ACTIVE' and (auction_date_end >= now() or auction_date_end = '1970-01-01 01:00:00') and products_id = '".(int)$info['products_id']."'");
        
        if ( (int)$db->ile_rekordow($aukcje) > 0 ) {
              $allegro = '<div class="InfoAllegro" id="allegro_' . $info['products_id'] . '" aria-haspopup="true"><div></div><img src="obrazki/logo/logo_allegro_male.png" alt="Produkt na Allegro" /></div>';
        }
        
        $db->close_query($aukcje);
        unset($aukcje);
        //
    }  

}

$szybki_link = '<span class="edpr" onclick="edpr('.$info['products_id'].')"></span>';

if ( !isset($zestawy) ) {

    $tgm = '<div class="EdycjaProduktu" id="edpr_'.$info['products_id'].'">' . $szybki_link . '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $kod_ean . $prd . $zakup . $id_zew . $allegro . '</div>';
    
  } else {
    
    // produkty zestawu
    $produkty_zestawu = unserialize($info['products_set_products']);
    $produkty_txt = '<strong class="TytulZestawLista">Produkty zestawu:</strong><ul class="ListaZestawu">';
    foreach ( $produkty_zestawu as $id => $dane ) {
        //
        $Produkt = new Produkt($id);
        //
        if ( isset($Produkt->info['id']) ) {
             //
             $produkty_txt .= '<li><a ' . (($Produkt->info['status'] == 0 || $Produkt->info['kupowanie'] == 0) ? 'class="PrdWylaczony TipChmurka"' : 'class="PrdWlaczony TipChmurka"') . ' href="produkty/produkty_edytuj.php?id_poz=' . $id . '">' . $Produkt->info['nazwa'] . '</a></li>';
             //
        }
        //
        unset($Produkt);
        //
    }
    $produkty_txt .= '</ul>';
  
    $tgm = '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $zakup . $id_zew . $allegro . $produkty_txt . '</div>';
    
    unset($produkty_zestawu);
   
}

$tgm_ajax = $szybki_link . '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $kod_ean . $prd . $zakup . $id_zew . $allegro;
?>