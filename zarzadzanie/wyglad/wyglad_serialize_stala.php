<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ( isset($_POST['typ']) ) {

        if ( isset($_POST[$_POST['typ']]) ) {
             //
             $zmienneAktualizacji = $_POST[$_POST['typ']];
             //
        } else {
             //
             $zmienneAktualizacji = array();
             //
        }
        
        $ciagDoZapisu = '';

        if ( isset($zmienneAktualizacji) && is_array($zmienneAktualizacji) ) {

            foreach ($zmienneAktualizacji as $idModulu) {

                $dodajPole = true;
                
                if (isset($_POST['skasuj']) && $filtr->process($_POST['skasuj']) == '1') {
                
                    if ($filtr->process($_POST['idmodul']) == $idModulu) {
                        
                        $dodajPole = false;
                        
                    }
                    
                }
                
                if ($dodajPole == true) {
            
                    if (strpos((string)$idModulu,'strona') > 0) {
                        $ciagDoZapisu .= 'strona;'.(int)$idModulu.',';
                    }
                    if (strpos((string)$idModulu,'galeria') > 0) {
                        $ciagDoZapisu .= 'galeria;'.(int)$idModulu.',';
                    }
                    if (strpos((string)$idModulu,'formularz') > 0) {
                        $ciagDoZapisu .= 'formularz;'.(int)$idModulu.',';
                    } 
                    if (strpos((string)$idModulu,'kategoria') > 0) {
                        $ciagDoZapisu .= 'kategoria;'.(int)$idModulu.',';
                    } 
                    if (strpos((string)$idModulu,'artykul') > 0) {
                        $ciagDoZapisu .= 'artykul;'.(int)$idModulu.',';
                    } 
                    if (strpos((string)$idModulu,'kategproduktow') > 0) {
                        $ciagDoZapisu .= 'kategproduktow;'.(int)$idModulu.',';
                    }             
                    if (strpos((string)$idModulu,'grupainfo') > 0) {
                        $ciagDoZapisu .= 'grupainfo;'.(int)$idModulu.',';
                    } 
                    if (strpos((string)$idModulu,'artkategorie') > 0) {
                        $ciagDoZapisu .= 'artkategorie;'.(int)$idModulu.',';
                    } 
                    if (strpos((string)$idModulu,'prodkategorie') > 0) {
                        $ciagDoZapisu .= 'prodkategorie;'.(int)$idModulu.',';
                    } 
                    if (strpos((string)$idModulu,'linkbezposredni') > 0) {
                        $ciagDoZapisu .= 'linkbezposredni;'.$idModulu.',';
                    }  
                    if (strpos((string)$idModulu,'pozycjabannery') > 0) {
                        $ciagDoZapisu .= 'pozycjabannery;'.$idModulu.',';
                    }        
                    if (strpos((string)$idModulu,'dowolnatresc') > 0) {
                        $ciagDoZapisu .= 'dowolnatresc;'.(int)$idModulu.',';
                    }                               
                    if (strpos((string)$idModulu,'linkwszystkiekategorie') > -1) {
                        $ciagDoZapisu .= 'linkwszystkiekategorie;'.$idModulu.',';
                    }        
                    if (strpos((string)$idModulu,'linkwszyscyproducenci') > -1) {
                        $ciagDoZapisu .= 'linkwszyscyproducenci;'.$idModulu.',';
                    }               
                    
                }
            
            }
        }

        $ciagDoZapisu = substr((string)$ciagDoZapisu, 0, strlen((string)$ciagDoZapisu) - 1);

        $pola = array(array('value',$ciagDoZapisu));   
        
        $sql = $db->update_query('settings', $pola, " code = '".$filtr->process($_POST['stala'])."'");	
        unset($pola);    
        
    }

}
?>