<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['id_poz'])) {
        $id_poz = (int)$_GET['id_poz'];
       } else {
        $id_poz = 0;
    }

    if ((int)$id_poz > 0) {

        $zapytanie = "select categories_status from categories where categories_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['categories_status'] == '1') {
                $pola = array(array('categories_status','0'));
                $db->update_query('categories' , $pola, " categories_id = '".(int)$_GET['id_poz']."'");
                unset($pola);            
                // wylaczanie podkategorii
                $drzewo = Kategorie::DrzewoKategorii($id_poz, '', '', '', true, false);
                for ($i=0, $c = count($drzewo); $i < $c; $i++) {
                    $pola = array(array('categories_status','0'));
                    $db->update_query('categories' , $pola, " categories_id = '".$drzewo[$i]['id']."'");
                    unset($pola);
                }
                unset($drzewo);   

                /*
                
                wylaczylem mozliwosci wylaczenia produktow - bo co jezeli produkt bedzie nalezal do kilku kategorii ??
                
                // wylaczanie produktow
                $zapytanie = "select products_id from products_to_categories where categories_id= '" . (int)$_GET['id_poz'] . "'";
                $sql = $db->open_query($zapytanie);
                //
                while ($info = $sql->fetch_assoc()) {
                    $pola = array();
                    $pola[] = array('products_status','0');
                    $db->update_query('products' , $pola, " products_id = '".$info['products_id']."'");
                    unset($pola);                
                }
                //        
                */
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['categories_status'] == '0') {
                
                // najpierw sprawdzi czy nadrzedna nie jest wylaczona
                //
                $moznaWlaczyc = true;
                //
                $PelnaSciezka = explode('_', (string)Kategorie::SciezkaKategoriiId((int)$_GET['id_poz']));
                for ($f = 0; $f < count($PelnaSciezka) - 1; $f++ ) {
                    //
                    if ( !empty($PelnaSciezka[$f]) ) {
                        //
                        $zapytanieStatusKategorii = "select categories_status from categories where categories_id = ".$PelnaSciezka[$f]." and categories_status = '0'";
                        $sqlStatusKategorii = $db->open_query($zapytanieStatusKategorii); 
                        
                        if ((int)$db->ile_rekordow($sqlStatusKategorii) > 0) { 
                            $moznaWlaczyc = false;
                        }
                        
                        $db->close_query($sqlStatusKategorii);
                        //
                    }
                    //
                }
                //
                
                if ( $moznaWlaczyc == true ) {
                
                    $pola = array(array('categories_status','1'));
                    $db->update_query('categories' , $pola, " categories_id = '".(int)$_GET['id_poz']."'");
                    unset($pola);            
                    // wylaczanie podkategorii
                    $drzewo = Kategorie::DrzewoKategorii($id_poz, '', '', '', true, false);
                    for ($i=0, $c = count($drzewo); $i < $c; $i++) {
                        $pola = array();
                        $pola[] = array('categories_status','1');
                        $db->update_query('categories' , $pola, " categories_id = '".$drzewo[$i]['id']."'");
                        unset($pola);
                    }
                    unset($drzewo); 

                }
                
                unset($moznaWlaczyc);
            }

            unset($pola,$info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('kategorie.php');
    
    }
}
?>