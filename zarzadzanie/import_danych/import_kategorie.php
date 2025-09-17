<?php
// kategoria i podkategorie

$BylyKategorie = false;

$parent = 0;
$start = 1;

if (isset($_POST['kategoria_glowna']) && trim((string)$_POST['kategoria_glowna']) != '') {
    //
    $start = 0;
    $TablicaDane['Kategoria_0_nazwa'] = $filtr->process(trim((string)$_POST['kategoria_glowna']));
    //
}

for ($w = $start; $w < 11; $w++) {
  
    if (isset($TablicaDane['Kategoria_' . $w . '_nazwa']) && trim((string)$TablicaDane['Kategoria_' . $w. '_nazwa']) != '') {

        $zapytanieKategorie = "select c.categories_id, cd.categories_name from categories c, categories_description cd where cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = cd.categories_id and categories_name = '" . addslashes((string)$filtr->process($TablicaDane['Kategoria_' . $w . '_nazwa'])) . "' and parent_id = '" . $parent . "'";
        $sqlkat = $db->open_query($zapytanieKategorie);
        
        // jezeli jest tylko aktualizacja kategorii z pliku csv - sa kategorie a nie ma nr katalogowego
        if ($CzyDodawanie == false && !isset($TablicaDane['Nr_katalogowy'])) {
        
            if ((int)$db->ile_rekordow($sqlkat) > 0) {

                $info = $sqlkat->fetch_assoc();
            
                // aktualizacja zdjecia jezeli jest
                $pola = array();
                if (isset($TablicaDane['Kategoria_' . $w. '_zdjecie']) && trim((string)$TablicaDane['Kategoria_' . $w. '_zdjecie']) != '') {
                    $pola[] = array('categories_image',$filtr->process($TablicaDane['Kategoria_' . $w. '_zdjecie']));
                }                     
                
                if (count($pola) > 0) {
                    $db->update_query('categories' , $pola, "categories_id = '" . $info['categories_id'] . "'");                
                }                
                unset($pola);            
            
                $pola = array();

                // jezeli jest opis
                if (isset($TablicaDane['Kategoria_' . $w. '_opis']) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis']) != '') {
                    $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis']));
                } 
                // jezeli jest opis dolny
                if (isset($TablicaDane['Kategoria_' . $w. '_opis_dol']) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis_dol']) != '') {
                    $pola[] = array('categories_description_bottom',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_dol']));
                }                    
                // jezeli jest meta tytul
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul']) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_tytul']) != '') {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul']));
                  } else {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta opis
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis']) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_opis']) != '') {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis']));
                  } else {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta slowa kluczowe
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa']) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_slowa']) != '') {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa']));
                  } else {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                } 
                // jezeli jest adres url
                if (isset($TablicaDane['Kategoria_' . $w. '_adres_url']) && trim((string)$TablicaDane['Kategoria_' . $w. '_adres_url']) != '') {
                    $pola[] = array('categories_seo_url',$filtr->process($TablicaDane['Kategoria_' . $w. '_adres_url']));
                }  
                // jezeli jest link kanoniczny
                if (isset($TablicaDane['Kategoria_' . $w. '_link_kanoniczny']) && trim((string)$TablicaDane['Kategoria_' . $w. '_link_kanoniczny']) != '') {
                    $pola[] = array('categories_link_canonical',$filtr->process($TablicaDane['Kategoria_' . $w. '_link_kanoniczny']));
                }     
                
                // usuwa adres z linku kanonicznego
                if ( count($pola) > 0 ) {
                     foreach ( $pola as $klucz => $wartosc ) {
                        if (strpos($wartosc[0], 'link_canonical') > -1) {
                            //
                            $pola[$klucz] = array($wartosc[0], str_replace(ADRES_URL_SKLEPU . '/', '',$wartosc[1]));
                            //
                        }
                     }
                }      
                        
                if (count($pola) > 0) {
                    $db->update_query('categories_description' , $pola, "categories_id = '" . (int)$info['categories_id'] . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");                
                }
                
                // ---------------------------------------------------------------
                // dodawanie do innych jezykow jak sa inne jezyki
                for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                    //
                    $kod_jezyka = $ile_jezykow[$j]['kod'];
                    //
                    if (isset($TablicaDane['Kategoria_' . $w . '_nazwa_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]) != '') {
                    
                        $pola = array();
                        
                        // jezeli jest opis
                        if (isset($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]));
                        }   
                        // jezeli jest opis dolny
                        if (isset($TablicaDane['Kategoria_' . $w. '_opis_dol_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis_dol_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_description_bottom',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_dol_' . $kod_jezyka]));
                        }                           
                        // jezeli jest meta tytul
                        if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]));
                          } else {
                            $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]));
                        }
                        // jezeli jest meta opis
                        if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]));
                          } else {
                            $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]));
                        }
                        // jezeli jest meta slowa kluczowe
                        if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]));
                          } else {
                            $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]));
                        }                            
                        // jezeli jest adres url
                        if (isset($TablicaDane['Kategoria_' . $w. '_adres_url_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_adres_url_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_seo_url',$filtr->process($TablicaDane['Kategoria_' . $w. '_adres_url_' . $kod_jezyka]));
                        }
                        // jezeli jest link kanoniczny
                        if (isset($TablicaDane['Kategoria_' . $w. '_link_kanoniczny_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_link_kanoniczny_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_link_canonical',$filtr->process($TablicaDane['Kategoria_' . $w. '_link_kanoniczny_' . $kod_jezyka]));
                        }                        
                
                        if (count($pola) > 0) {
                            $db->update_query('categories_description' , $pola, "categories_id = '" . $info['categories_id'] . "' and language_id = '".$ile_jezykow[$j]['id']."'");                
                        }      

                        unset($pola);             
                    }    
                    //
                    unset($kod_jezyka);
                    //
                }                
                
                $parent = $info['categories_id'];
                unset($info);                
        
            }
        
        } else {

            if ((int)$db->ile_rekordow($sqlkat) == 0) {
                //
                $pola = array(
                        array('parent_id',$parent),
                        array('sort_order','1'),
                        array('categories_status','1'));
                        
                // jezeli jest zdjecie
                if (isset($TablicaDane['Kategoria_' . $w. '_zdjecie']) && trim((string)$TablicaDane['Kategoria_' . $w. '_zdjecie']) != '') {
                    $pola[] = array('categories_image',$filtr->process($TablicaDane['Kategoria_' . $w. '_zdjecie']));
                }                     
                
                $db->insert_query('categories' , $pola);
                $id_dodanej_pozycji = $db->last_id_query();
                unset($pola);
                //
                $pola = array(
                        array('categories_id',$id_dodanej_pozycji),
                        array('language_id',$_SESSION['domyslny_jezyk']['id']),
                        array('categories_name',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa'])));  

                // jezeli jest opis
                if (isset($TablicaDane['Kategoria_' . $w. '_opis']) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis']) != '') {
                    $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis']));
                }         
                // jezeli jest opis dolny
                if (isset($TablicaDane['Kategoria_' . $w. '_opis_dol']) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis_dol']) != '') {
                    $pola[] = array('categories_description_bottom',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_dol']));
                }                      
                // jezeli jest meta tytul
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul']) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_tytul']) != '') {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul']));
                  } else {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta opis
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis']) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_opis']) != '') {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis']));
                  } else {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta slowa kluczowe
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa']) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_slowa']) != '') {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa']));
                  } else {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }             
                // jezeli jest adres url
                if (isset($TablicaDane['Kategoria_' . $w. '_adres_url']) && trim((string)$TablicaDane['Kategoria_' . $w. '_adres_url']) != '') {
                    $pola[] = array('categories_seo_url',$filtr->process($TablicaDane['Kategoria_' . $w. '_adres_url']));
                } 
                // jezeli jest link kanoniczny
                if (isset($TablicaDane['Kategoria_' . $w. '_link_kanoniczny']) && trim((string)$TablicaDane['Kategoria_' . $w. '_link_kanoniczny']) != '') {
                    $pola[] = array('categories_link_canonical',$filtr->process($TablicaDane['Kategoria_' . $w. '_link_kanoniczny']));
                }      
                
                // usuwa adres z linku kanonicznego
                if ( count($pola) > 0 ) {
                     foreach ( $pola as $klucz => $wartosc ) {
                        if (strpos($wartosc[0], 'link_canonical') > -1) {
                            //
                            $pola[$klucz] = array($wartosc[0], str_replace(ADRES_URL_SKLEPU . '/', '',$wartosc[1]));
                            //
                        }
                     }
                }      
                        
                $db->insert_query('categories_description' , $pola);
                unset($pola);    
                //
                $parent = $id_dodanej_pozycji;
                
                // ---------------------------------------------------------------
                // dodawanie do innych jezykow jak sa inne jezyki
                for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                    //
                    $kod_jezyka = $ile_jezykow[$j]['kod'];
                    //
                    $NazwaTmp = $filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']);
                    if (isset($TablicaDane['Kategoria_' . $w . '_nazwa_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]) != '') {
                        $NazwaTmp = $filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]);
                    }
                    //
                    $pola = array(
                            array('categories_id',$id_dodanej_pozycji),
                            array('language_id',$ile_jezykow[$j]['id']),
                            array('categories_name',$NazwaTmp));    

                    // jezeli jest opis
                    if (isset($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]));
                    }
                    // jezeli jest opis dolny
                    if (isset($TablicaDane['Kategoria_' . $w. '_opis_dol_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_opis_dol_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_description_bottom',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_dol_' . $kod_jezyka]));
                    }                    
                    // jezeli jest meta tytul
                    if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]));
                      } else {
                        $pola[] = array('categories_meta_title_tag',$NazwaTmp);
                    }
                    // jezeli jest meta opis
                    if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]));
                      } else {
                        $pola[] = array('categories_meta_desc_tag',$NazwaTmp);
                    }
                    // jezeli jest meta slowa kluczowe
                    if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]));
                      } else {
                        $pola[] = array('categories_meta_keywords_tag',$NazwaTmp);
                    }                            
                    // jezeli jest adres url
                    if (isset($TablicaDane['Kategoria_' . $w. '_adres_url_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_adres_url_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_seo_url',$filtr->process($TablicaDane['Kategoria_' . $w. '_adres_url_' . $kod_jezyka]));
                    }  
                    // jezeli jest link kanoniczny
                    if (isset($TablicaDane['Kategoria_' . $w. '_link_kanoniczny_' . $kod_jezyka]) && trim((string)$TablicaDane['Kategoria_' . $w. '_link_kanoniczny_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_link_canonical',$filtr->process($TablicaDane['Kategoria_' . $w. '_link_kanoniczny_' . $kod_jezyka]));
                    }  
                    
                    if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                        $db->insert_query('categories_description' , $pola);
                    }
                    unset($pola);              
                    //
                    unset($kod_jezyka, $NazwaTmp);
                    //
                }
                
            } else {
            
                // jezeli znaleziono taka kategorie
                $info = $sqlkat->fetch_assoc();
                $parent = $info['categories_id'];
                unset($info);             
            
            }
            
        }
                
        $db->close_query($sqlkat);
        unset($zapytanieKategorie);
        
        $BylyKategorie = true;
        
    }

}
unset($id_dodanej_pozycji);
?>