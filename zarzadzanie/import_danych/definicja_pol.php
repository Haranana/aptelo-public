<?php

$TablicaProducts = array();
$TablicaProducts[] = array('products_model','Nr_katalogowy');
$TablicaProducts[] = array('products_man_code','Kod_producenta');
$TablicaProducts[] = array('products_id_private','Id_produktu_magazyn');
$TablicaProducts[] = array('products_ean','Kod_ean');
$TablicaProducts[] = array('products_gtu','Kod_GTU');
$TablicaProducts[] = array('products_safety_information','Informacja_o_bezpieczenstwie');
$TablicaProducts[] = array('products_weight','Waga');
$TablicaProducts[] = array('products_weight_width','Waga_wolumetryczna_szerokosc');
$TablicaProducts[] = array('products_weight_height','Waga_wolumetryczna_wysokosc');
$TablicaProducts[] = array('products_weight_length','Waga_wolumetryczna_dlugosc');
$TablicaProducts[] = array('products_quantity','Ilosc_produktow');
$TablicaProducts[] = array('products_quantity_alarm','Alarm_magazynowy');
$TablicaProducts[] = array('location','Lokalizacja_magazyn');
$TablicaProducts[] = array('products_minorder','Min_ilosc_zakupu');
$TablicaProducts[] = array('products_maxorder','Max_ilosc_zakupu');
$TablicaProducts[] = array('products_quantity_order','Przyrost_ilosci');
$TablicaProducts[] = array('products_pack_type','Gabaryt');
$TablicaProducts[] = array('products_separate_package','Osobna_paczka');
$TablicaProducts[] = array('products_separate_package_quantity','Osobna_paczka_ilosc');
$TablicaProducts[] = array('products_price_tax','Cena_brutto');
$TablicaProducts[] = array('products_old_price','Cena_poprzednia');
$TablicaProducts[] = array('products_retail_price','Cena_katalogowa');
$TablicaProducts[] = array('products_purchase_price','Cena_zakupu');
$TablicaProducts[] = array('products_adminnotes','Notatki_produktu');
$TablicaProducts[] = array('products_date_available','Data_dostepnosci');
$TablicaProducts[] = array('sort_order','Sortowanie');
$TablicaProducts[] = array('export_id','Porownywarki_id');
$TablicaProducts[] = array('shipping_method','Wysylki_id');
if ( ILOSC_CEN > 1 ) {
    $TablicaProducts[] = array('products_price_tax_2','Cena_brutto_2');
    $TablicaProducts[] = array('products_old_price_2','Cena_poprzednia_2');
    $TablicaProducts[] = array('products_retail_price_2','Cena_katalogowa_2');
}
if ( ILOSC_CEN > 2 ) {
    $TablicaProducts[] = array('products_price_tax_3','Cena_brutto_3');
    $TablicaProducts[] = array('products_old_price_3','Cena_poprzednia_3');
    $TablicaProducts[] = array('products_retail_price_3','Cena_katalogowa_3');
}
if ( ILOSC_CEN > 3 ) {
    $TablicaProducts[] = array('products_price_tax_4','Cena_brutto_4');
    $TablicaProducts[] = array('products_old_price_4','Cena_poprzednia_4');
    $TablicaProducts[] = array('products_retail_price_4','Cena_katalogowa_4');
}
if ( ILOSC_CEN > 4 ) {
    $TablicaProducts[] = array('products_price_tax_5','Cena_brutto_5');
    $TablicaProducts[] = array('products_old_price_5','Cena_poprzednia_5');
    $TablicaProducts[] = array('products_retail_price_5','Cena_katalogowa_5');
}
if ( ILOSC_CEN > 5 ) {
    $TablicaProducts[] = array('products_price_tax_6','Cena_brutto_6');
    $TablicaProducts[] = array('products_old_price_6','Cena_poprzednia_6');
    $TablicaProducts[] = array('products_retail_price_6','Cena_katalogowa_6');
}
if ( ILOSC_CEN > 6 ) {
    $TablicaProducts[] = array('products_price_tax_7','Cena_brutto_7');
    $TablicaProducts[] = array('products_old_price_7','Cena_poprzednia_7');
    $TablicaProducts[] = array('products_retail_price_7','Cena_katalogowa_7');
}
if ( ILOSC_CEN > 7 ) {
    $TablicaProducts[] = array('products_price_tax_8','Cena_brutto_8');
    $TablicaProducts[] = array('products_old_price_8','Cena_poprzednia_8');
    $TablicaProducts[] = array('products_retail_price_8','Cena_katalogowa_8');
}
if ( ILOSC_CEN > 8 ) {
    $TablicaProducts[] = array('products_price_tax_9','Cena_brutto_9');
    $TablicaProducts[] = array('products_old_price_9','Cena_poprzednia_9');
    $TablicaProducts[] = array('products_retail_price_9','Cena_katalogowa_9');
}
if ( ILOSC_CEN > 9 ) {
    $TablicaProducts[] = array('products_price_tax_10','Cena_brutto_10');
    $TablicaProducts[] = array('products_old_price_10','Cena_poprzednia_10');
    $TablicaProducts[] = array('products_retail_price_10','Cena_katalogowa_10');
}
$TablicaProducts[] = array('new_status','Nowosc');
$TablicaProducts[] = array('star_status','Nasz_hit');
$TablicaProducts[] = array('featured_status','Polecany');
$TablicaProducts[] = array('specials_status','Promocja');
$TablicaProducts[] = array('specials_date','Promocja_czas_rozpoczecia');
$TablicaProducts[] = array('specials_date_end','Promocja_czas_zakonczenia');
$TablicaProducts[] = array('sale_status','Wyprzedaz');
$TablicaProducts[] = array('export_status','Do_porownywarek');
$TablicaProducts[] = array('products_make_an_offer','Negocjacja');
$TablicaProducts[] = array('free_shipping_status','Darmowa_dostawa');
$TablicaProducts[] = array('free_shipping_excluded','Wykluczona_darmowa_dostawa');
$TablicaProducts[] = array('pickup_excluded','Wykluczony_punkt_odbioru');
$TablicaProducts[] = array('products_buy','Kupowanie');
$TablicaProducts[] = array('export_ceneo_buy_now','Ceneo_kup_teraz');

$TablicaProducts[] = array('icon_1_status','Ikona_1');
$TablicaProducts[] = array('icon_2_status','Ikona_2');
$TablicaProducts[] = array('icon_3_status','Ikona_3');
$TablicaProducts[] = array('icon_4_status','Ikona_4');
$TablicaProducts[] = array('icon_5_status','Ikona_5');

$TablicaProducts[] = array('products_image','Zdjecie_glowne');
$TablicaProducts[] = array('products_image_description','Zdjecie_glowne_opis');
$TablicaProducts[] = array('products_status','Status');
$TablicaProducts[] = array('products_control_storage','Kontrola_magazynu');
$TablicaProducts[] = array('products_type','Rodzaj_produktu');
$TablicaProducts[] = array('shipping_cost','Indywidualny_koszt_wysylki');
$TablicaProducts[] = array('shipping_cost_delivery','Indywidualny_koszt_wysylki_pobranie');
$TablicaProducts[] = array('products_size','Rozmiar_pojemnosc');
$TablicaProducts[] = array('products_size_type','Jednostka_rozmiaru');

for ( $r = 1; $r < 6; $r++ ) {
      //
      $TablicaProducts[] = array('products_reference_number_' . $r,'Nr_referencyjny_' . $r);
      //
}

$TablicaProductsDescription = array();
$TablicaProductsDescription[] = array('products_name','Nazwa_produktu');
$TablicaProductsDescription[] = array('products_name_info','Dodatkowa_nazwa_produktu');
$TablicaProductsDescription[] = array('products_name_short','Krotka_nazwa_produktu');
$TablicaProductsDescription[] = array('products_description','Opis');
$TablicaProductsDescription[] = array('products_short_description','Opis_krotki');
$TablicaProductsDescription[] = array('products_meta_title_tag','Meta_tytul');
$TablicaProductsDescription[] = array('products_meta_desc_tag','Meta_opis');
$TablicaProductsDescription[] = array('products_meta_keywords_tag','Meta_slowa');
$TablicaProductsDescription[] = array('products_link_canonical','Link_kanoniczny');
$TablicaProductsDescription[] = array('products_search_tag','Tagi_szukania');

$TablicaProductsDescriptionAdditional = array();
$TablicaProductsDescriptionAdditional[] = array('products_info_description_1','Opis_dodatkowy_1');
$TablicaProductsDescriptionAdditional[] = array('products_info_description_2','Opis_dodatkowy_2');
?>