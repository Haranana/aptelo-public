<?php

if ( isset($pobierzFunkcje) ) {

    // tylko dla zalogowanych
    $warunek = " and ( products_file_login = '1' ";
    if ( (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        $warunek .= " or products_file_login = '0' ";
    }
    $warunek .= ' ) ';

    $zapytanie = "SELECT products_file_unique_id, products_file_name, products_file, products_file_description FROM products_file WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "'" . $warunek . " ";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['products_file_name']) && !empty($info['products_file']) ) {
                //
                $UniqId = ($info['products_file_unique_id'] * $info['products_file_unique_id']);
                $LinkPobierz = '';
                //
                if ( KARTA_PRODUKTU_PLIKI_DYNAMICZNE == 'tak' ) {
                     // generowanie id                 
                     $LinkPobierz = 'pobierz-' . Sesje::Token() . '-' . $UniqId . '.html';
                } else {
                     //
                     $LinkPobierz = 'pobierz-' . str_replace('.html', '', Seo::link_SEO($info['products_file_name'],'','inna')) . '-' . $UniqId . '.html';
                }
                //
                $this->Pliki[] = array( 'nazwa' => $info['products_file_name'],
                                        'opis'  => $info['products_file_description'],
                                        'plik'  => $LinkPobierz,
                                        'sam_plik' => $info['products_file']);
                unset($UniqId, $LinkPobierz);
                //
            }            
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie);
  
}

?>