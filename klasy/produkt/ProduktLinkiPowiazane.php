<?php

if ( isset($pobierzFunkcje) ) {
  
    $GrupyLinkow = array();
    
    // sprawdzi czy sa linki powiazane do produktow
    
    $WynikCache = $GLOBALS['cache']->odczytaj('LinkiPowiazane_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
    
    if ( !$WynikCache ) {
      
        $zapytanie_grupy_linki = "select distinct * from products_related_links_group where language_id = '" . $this->jezykDomyslnyId . "'";
        $sqls = $GLOBALS['db']->open_query($zapytanie_grupy_linki);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sqls) > 0 ) {
        
            while ($link = $sqls->fetch_assoc()) {
                //
                $GrupyLinkow[] = $link;
                //          
            }
            
            unset($link);
            
        }

        $GLOBALS['cache']->zapisz('LinkiPowiazane_' . $_SESSION['domyslnyJezyk']['kod'], $GrupyLinkow, CACHE_INNE);
     
        $GLOBALS['db']->close_query($sqls);
        unset($zapytanie_grupy_linki);   
        
    } else {
      
        $GrupyLinkow = $WynikCache;
        
    }  
    
    if ( count($GrupyLinkow) > 0 ) {
      
        // czy sa grupy dla danego produktu
        
        $SaGrupyLinkow = false;
        
        foreach ( $GrupyLinkow as $GrupaProduktu ) {        
            //
            if ( $GrupaProduktu['products_id'] == $this->id_produktu && $GrupaProduktu['language_id'] == $this->jezykDomyslnyId ) {
                 //
                 $SaGrupyLinkow = true;
                 //
            }
            //
        }
  
        if ( $SaGrupyLinkow == true ) {
          
            $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_linkipowiazane_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

            if ( !$WynikCache && !is_array($WynikCache) ) {  

                foreach ( $GrupyLinkow as $GrupaProduktu ) {
                    //
                    // czy grupa danego produktu i jezyka
                    if ( $GrupaProduktu['products_id'] == $this->id_produktu && $GrupaProduktu['language_id'] == $this->jezykDomyslnyId ) {
                         //
                         $LinkiPowiazane = array();
                         //
                         $zapytanie_linki = "select distinct * from products_related_links where products_related_links_group_id = '" . $GrupaProduktu['products_related_links_group_id'] . "' and language_id = '" . $this->jezykDomyslnyId . "'";
                         $sqls_tmp = $GLOBALS['db']->open_query($zapytanie_linki);
                         //
                         if ( (int)$GLOBALS['db']->ile_rekordow($sqls_tmp) > 0 ) {
                             //
                             while ($link_tmp = $sqls_tmp->fetch_assoc()) {
                                 //
                                 // zapisuje tylko linki
                                 if ( $link_tmp['products_related_links_url'] != '' ) {
                                   
                                      $LinkiPowiazane[] = array( 'nazwa' => $link_tmp['products_related_links_name'],
                                                                 'samo_zdjecie' => $link_tmp['products_related_links_foto'],
                                                                 'foto' => '<img src="' . KATALOG_ZDJEC . '/' . $link_tmp['products_related_links_foto'] . '" alt="' . $link_tmp['products_related_links_name'] . '" />',
                                                                 'sam_url' => $link_tmp['products_related_links_url'],
                                                                 'url_tekst' => '<a href="' . $link_tmp['products_related_links_url'] . '">' . $link_tmp['products_related_links_name'] . '</a>',
                                                                 'url_foto' => '<a href="' . $link_tmp['products_related_links_url'] . '"><img src="' . KATALOG_ZDJEC . '/' . $link_tmp['products_related_links_foto'] . '" alt="' . $link_tmp['products_related_links_name'] . '" /></a>' );
                                                                 
                                 }
                                 //
                             }
                             //
                             unset($link_tmp);
                             //
                         }
                         //
                         $GLOBALS['db']->close_query($sqls_tmp); 
                         unset($zapytanie_linki);                            
                         //
                         $this->LinkiPowiazane[ $GrupaProduktu['products_related_links_group_id'] ] = array( 'id_grupy' => $GrupaProduktu['products_related_links_group_id'],
                                                                                                             'nazwa_grupy' => $GrupaProduktu['products_related_links_group_name'],
                                                                                                             'opis_grupy' => $GrupaProduktu['products_related_links_group_description'],
                                                                                                             'linki' => $LinkiPowiazane);
                         //
                         unset($LinkiPowiazane);
                         //
                    }
                    //
                }
                
                $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_linkipowiazane_' . $_SESSION['domyslnyJezyk']['kod'], $this->LinkiPowiazane, CACHE_PRODUKTY);
                
              } else {

                $this->LinkiPowiazane = $WynikCache;     
                
            } 

        }      

        unset($SaGrupyLinkow);
        
    }

    unset($GrupyLinkow);

}
    
?>