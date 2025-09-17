<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($filtr->process($_POST['plik']), "a");
    // blokada pliku do zapisu
    flock($fp, 2);
    
    $CoDoZapisania = '';
    
    if ((int)$_POST['limit'] == 0) {
        //
        $CoDoZapisania = 'Id produktu;Nr katalogowy;Nazwa produktu;Cena zakupu;Cena poprzednia;Cena netto;Cena brutto;Ilość;Status'."\r\n";
        //
    }
    
    $warunki_szukania = '';
    if (isset($_POST['kategoria']) && (int)$_POST['kategoria'] > 0) {
        $warunki_szukania = " AND pc.categories_id = '" . (int)$_POST['kategoria'] . "'";
    }
    if (isset($_POST['producent']) && (int)$_POST['producent'] > 0) {
        $warunki_szukania = " AND p.manufacturers_id = '" . (int)$_POST['producent'] . "'";
    }                    
    
    $zapytanie = 'SELECT DISTINCT
                         p.products_id, 
                         p.products_status,
                         p.products_model,
                         p.products_price,
                         p.products_price_tax,
                         p.products_old_price,
                         p.products_purchase_price,
                         p.products_quantity,
                         p.manufacturers_id,
                         pd.products_id, 
                         p.products_currencies_id,        
                         pd.language_id, 
                         pd.products_name
                  FROM products p, products_to_categories pc, products_description pd
                  WHERE pd.products_id = p.products_id AND pc.products_id = p.products_id AND p.products_quantity > 0
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" ' . $warunki_szukania . ' order by p.products_quantity, pd.products_name asc limit '.(int)$_POST['limit'].',20';    

    $sql = $db->open_query($zapytanie);
    
    $top = 0;
    while ($info = $sql->fetch_assoc()) {
        //
        // id produktu
        $CoDoZapisania .= (int)$info['products_id'] . ';';        
        // nr kat
        $CoDoZapisania .= Funkcje::CzyszczenieTekstu($info['products_model']) . ';';
        // nazwa
        $CoDoZapisania .= Funkcje::CzyszczenieTekstu($info['products_name']) . ';';
        // cena zakupu
        if ((float)$info['products_purchase_price'] > 0) {
            $CoDoZapisania .= $waluty->FormatujCene($info['products_purchase_price'], false, $info['products_currencies_id']) . ';';
           } else {
            $CoDoZapisania .= '-;';
        }

        // cena poprzednia
        if ((float)$info['products_old_price'] > 0) {
            $CoDoZapisania .= $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . ';';
           } else {
            $CoDoZapisania .= '-;';
        }
        
        // cena netto
        $CoDoZapisania .= $waluty->FormatujCene($info['products_price'], false, $info['products_currencies_id']) . ';';
                
        // cena brutto
        $CoDoZapisania .= $waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']) . ';';
        
        // ilosc
        $CoDoZapisania .= $info['products_quantity'] . ';';        
        
        // status
        if ($info['products_status'] == 1) {
            $CoDoZapisania .= 'tak;' ."\r\n";
          } else {
            $CoDoZapisania .= 'nie;' ."\r\n";
        } 
        //
        
        if (CECHY_MAGAZYN == 'tak') {
        
            // sprawdzanie cech produktu
            $zapytanieCechy = "SELECT DISTINCT products_stock_quantity, products_stock_model, products_stock_attributes FROM products_stock WHERE products_id = '" . (int)$info['products_id'] . "' and products_stock_quantity > 0";
            $sqlCecha = $db->open_query($zapytanieCechy);
            while ($infc = $sqlCecha->fetch_assoc()) {
                //
                $NazwaWartosciCech = explode(',', (string)$infc['products_stock_attributes']);
                //
                $CiagCech = '';
                for ($r = 0, $cr = count($NazwaWartosciCech); $r < $cr; $r++) {
                    //
                    $Podz = explode('-', (string)$NazwaWartosciCech[$r]);
                    $CiagCech .= Funkcje::NazwaCechy($Podz[0]) . ': <b>' . Funkcje::WartoscCechy($Podz[1]) . '</b>, ';
                    unset($Podz);
                    //
                }
                $CiagCech = substr((string)$CiagCech, 0, strlen((string)$CiagCech)-2);                                
                //
                // nr kat cechy
                if (empty($infc['products_stock_model'])){
                    $CoDoZapisania .= '-;';
                  } else {
                    $CoDoZapisania .= $infc['products_stock_model'] . ';';
                }
                // nazwa cech
                $CoDoZapisania .= Funkcje::CzyszczenieTekstu($info['products_name']) . ' ' . strip_tags(Funkcje::CzyszczenieTekstu($CiagCech)) . ';';
                // ceny brak
                $CoDoZapisania .= '-;-;-;-;';
                // ilosc
                $CoDoZapisania .= $infc['products_stock_quantity'] . ';';
                
                // status
                if ($info['products_status'] == 1) {
                    $CoDoZapisania .= 'tak;' ."\r\n";
                  } else {
                    $CoDoZapisania .= 'nie;' ."\r\n";
                } 
                //
             
                unset($CiagCech, $NazwaWartosciCech);
                //
            }

        }
        
    }
    
    $db->close_query($sql);
    unset($info, $zapytanie);        

    fwrite($fp, $CoDoZapisania);
    
    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);  

    unset($CoDoZapisania);    

}

?>