<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $glowne_id = (int)$_POST['id_wybrany_produkt'];

        if (isset($_POST['kopiowanie']) && isset($_POST['id_produktow']) && count($_POST['id_produktow']) > 0) {
          
            foreach ($_POST['kopiowanie'] as $Opcja) {
             
                // kopiowanie cech
                if ( $Opcja == 'cechy' || $Opcja == 'stock' ) {
                     //
                     // pobiera dane cech z produktu
                     $sql = $db->open_query("select * from products_attributes where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_attributes_id']);
                        unset($info['products_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info);                     
                     //
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje cechy produktu
                        $db->delete_query('products_attributes' , "products_id = '" . $produkt . "'");     
                        // kasuje z tablicy stock
                        $db->delete_query('products_stock' , "products_id = '" . $produkt . "'");
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            // sprawdzi czy produkt nie jest zestawem
                            $sql_spr = $db->open_query("select * from products where products_id = '" . $produkt . "' and products_set = 1");
                            if ( (int)$db->ile_rekordow($sql_spr) == 0 ) {
                                 //
                                 $sql = $db->insert_query('products_attributes', $pola_tab);
                                 //
                            }
                            $db->close_query($sql_spr);
                            //
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }
                
                // kopiowanie cech i kombinacji cech
                if ( $Opcja == 'stock' ) {
                     //
                     // pobiera dane cech z produktu
                     $sql = $db->open_query("select * from products_stock where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_stock_id']);
                        unset($info['products_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     //
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje cechy produktu
                        $db->delete_query('products_stock' , "products_id = '" . $produkt . "'");     
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            // sprawdzi czy produkt nie jest zestawem
                            $sql_spr = $db->open_query("select * from products where products_id = '" . $produkt . "' and products_set = 1");
                            if ( (int)$db->ile_rekordow($sql_spr) == 0 ) {
                                 //
                                 $sql = $db->insert_query('products_stock', $pola_tab);
                                 //
                            }
                            $db->close_query($sql_spr);
                            //                            
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }    

                // kopiowanie pol tekstowych
                if ( $Opcja == 'pola_tekstowe' ) {
                     //
                     // pobiera pol tekstowych z produktu
                     $sql = $db->open_query("select products_text_fields_id from products_to_text_fields where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     //
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje cechy produktu
                        $db->delete_query('products_to_text_fields' , "products_id = '" . $produkt . "'");     
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            $sql = $db->insert_query('products_to_text_fields', $pola_tab);
                            //
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }                       
                
                // kopiowanie pol opisowych
                if ( $Opcja == 'pola_opisowe' ) {
                     //
                     // pobiera dane cech z produktu
                     $sql = $db->open_query("select * from products_to_products_extra_fields where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     //
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje pola opisowe produktu
                        $db->delete_query('products_to_products_extra_fields' , "products_id = '" . $produkt . "'");     
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            $sql = $db->insert_query('products_to_products_extra_fields', $pola_tab);
                            //
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }     

                // kopiowanie kategorii
                if ( $Opcja == 'kategorie' ) {
                     //
                     // pobiera dane kategorii z produktu
                     $sql = $db->open_query("select * from products_to_categories where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     //
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje cechy produktu
                        $db->delete_query('products_to_categories' , "products_id = '" . $produkt . "'");     
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            $sql = $db->insert_query('products_to_categories', $pola_tab);
                            //
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }      

                // kopiowanie wysylek
                if ( $Opcja == 'wysylki' ) {
                     //
                     // pobiera dane cech z produktu
                     $sql = $db->open_query("select shipping_method from products where products_id = '" . $glowne_id . "'");
                     $info = $sql->fetch_assoc();
                     //
                     $metody_wysylek = $info['shipping_method'];
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     //
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        // 
                        $pola_tab = array(array('shipping_method', $metody_wysylek));  
                        //
                        $sql = $db->update_query('products', $pola_tab, 'products_id = ' . $produkt);
                        //
                        unset($pola_tab);
                        //
                     }
                     //
                
                } 

                // kopiowanie linkow
                if ( $Opcja == 'linki' ) {
                     //
                     // pobiera dane z produktu
                     $sql = $db->open_query("select * from products_link where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_id']);
                        unset($info['products_link_unique_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje 
                        $db->delete_query('products_link' , "products_id = '" . $produkt . "'");     
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            $sql = $db->insert_query('products_link', $pola_tab);
                            //
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }         

                // kopiowanie youtube
                if ( $Opcja == 'youtube' ) {
                     //
                     // pobiera dane z produktu
                     $sql = $db->open_query("select * from products_youtube where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        unset($info['products_id']);
                        unset($info['products_youtube_unique_id']);
                        //
                        $pola[] = $info;
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje 
                        $db->delete_query('products_youtube' , "products_id = '" . $produkt . "'");     
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tmp['products_id'] = $produkt;
                            //
                            $pola_tab = array();
                            foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                           
                            //
                            $sql = $db->insert_query('products_youtube', $pola_tab);
                            //
                            unset($pola_tab);
                            //
                        }
                        //
                     }
                     //
                     unset($pola);
                
                }         

                // kopiowanie zakladki
                for ( $t = 1; $t < 5; $t++ ) {
                  
                    if ( $Opcja == 'zakladka_' . $t ) {
                         //
                         // pobiera dane z produktu
                         $sql = $db->open_query("select * from products_info where products_id = '" . $glowne_id . "' and products_info_id = '" . $t . "'");
                         //
                         $pola = array();
                         //
                         while ($info = $sql->fetch_assoc()) {
                            //
                            unset($info['products_id']);
                            //
                            $pola[] = $info;
                            //
                         }
                         //
                         $db->close_query($sql);
                         unset($info); 
                        
                         // dodawanie do produktow
                         foreach ($_POST['id_produktow'] as $produkt) {
                            //
                            // kasuje 
                            $db->delete_query('products_info' , "products_id = '" . $produkt . "' and products_info_id = '" . $t . "'");     
                            //      
                            foreach ($pola as $pola_tmp) {
                                //
                                $pola_tmp['products_id'] = $produkt;
                                //
                                $pola_tab = array();
                                foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                    //
                                    $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                    //
                                }                           
                                //
                                $sql = $db->insert_query('products_info', $pola_tab);
                                //
                                unset($pola_tab);
                                //
                            }
                            //
                         }
                         //
                         unset($pola);
                    
                    }  
                    
                }

                // kopiowanie linki powiazane
                if ( $Opcja == 'linki_powiazane' ) {
                     //
                     // pobiera dane z produktu
                     $sql = $db->open_query("select * from products_related_links_group where products_id = '" . $glowne_id . "'");
                     //
                     $pola = array();
                     //
                     while ($info = $sql->fetch_assoc()) {
                        //
                        // pobiera dane z produktu
                        $sql_linki = $db->open_query("select * from products_related_links where products_id = '" . $glowne_id . "' and products_related_links_group_id = '" . $info['products_related_links_group_id'] . "'");
                        //
                        $pola_linki = array();
                        //
                        while ($infs = $sql_linki->fetch_assoc()) {
                           //
                           unset($infs['products_related_links_id']);
                           unset($infs['products_id']);
                           unset($infs['products_related_links_group_id']);
                           //
                           $pola_linki[] = $infs;
                           //
                        }
                        //
                        $db->close_query($sql_linki);
                        unset($infe);                         
                        //
                        unset($info['products_related_links_group_id']);
                        unset($info['products_id']);
                        //
                        $pola[] = array( 'grupa' => $info,
                                         'linki' => $pola_linki);
                        //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje
                        $db->delete_query('products_related_links_group' , "products_id = '" . $produkt . "'");    
                        $db->delete_query('products_related_links' , "products_id = '" . $produkt . "'");                            
                        //      
                        foreach ($pola as $pola_tmp) {
                            //
                            $pola_tab = array();
                            $pola_tab[] = array('products_id', $produkt);
                            //
                            foreach ($pola_tmp['grupa'] as $pola_klucz => $pola_wartosc) {
                                //
                                $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                //
                            }                                    
                            //
                            $id_dodanej_grupy_linkow = $db->insert_query('products_related_links_group', $pola_tab, '', false, true);
                            //
                            unset($pola_tab);

                            foreach ($pola_tmp['linki'] as $link ) {
                                //
                                $pola_tab = array();
                                $pola_tab[] = array('products_id', $produkt);
                                $pola_tab[] = array('products_related_links_group_id', $id_dodanej_grupy_linkow);
                                //
                                foreach ( $link as $pola_klucz => $pola_wartosc) {
                                    //
                                    $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                    //
                                }
                                //
                                $sql = $db->insert_query('products_related_links', $pola_tab);
                                //
                                unset($pola_tab);
                                //
                            }
                            //
                        }
                        //
                     }
                     //
                     unset($pola);

                }                   
                
                // kopiowanie opisu
                if ( $Opcja == 'opis' ) {
                     //
                     $opisy = array();
                     //
                     // pobiera dane cech z produktu                     
                     $sql = $db->open_query("select products_description, language_id from products_description where products_id = '" . $glowne_id . "'");
                     //
                     while ($info = $sql->fetch_assoc()) {
                         //
                         $opisy[$info['language_id']] = $info['products_description'];
                         //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        $ile_jezykow = Funkcje::TablicaJezykow();
                        //
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                              //
                              if ( isset($opisy[$ile_jezykow[$w]['id']]) ) {
                                   //
                                   $pola_tab = array(array('products_description', $opisy[$ile_jezykow[$w]['id']])); 
                                   $sql = $db->update_query('products_description', $pola_tab, 'products_id = ' . $produkt . ' and language_id = ' . (int)$ile_jezykow[$w]['id']);
                                   unset($pola_tab);
                                   //
                              } else {
                                   //
                                   $pola_tab = array(array('products_description', '')); 
                                   $sql = $db->update_query('products_description', $pola_tab, 'products_id = ' . $produkt . ' and language_id = ' . (int)$ile_jezykow[$w]['id']);
                                   unset($pola_tab);
                                   //
                              }
                              //
                        }
                        //
                        unset($ile_jezykow);
                        //
                     }
                     //
                
                }                 
              
                // kopiowanie opisu krotkiego
                if ( $Opcja == 'opis_krotki' ) {
                     //
                     $opisy = array();
                     //
                     // pobiera dane cech z produktu                     
                     $sql = $db->open_query("select products_short_description, language_id from products_description where products_id = '" . $glowne_id . "'");
                     //
                     while ($info = $sql->fetch_assoc()) {
                         //
                         $opisy[$info['language_id']] = $info['products_short_description'];
                         //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        $ile_jezykow = Funkcje::TablicaJezykow();
                        //
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                              //
                              if ( isset($opisy[$ile_jezykow[$w]['id']]) ) {
                                   //
                                   $pola_tab = array(array('products_short_description', $opisy[$ile_jezykow[$w]['id']])); 
                                   $sql = $db->update_query('products_description', $pola_tab, 'products_id = ' . $produkt . ' and language_id = ' . (int)$ile_jezykow[$w]['id']);
                                   unset($pola_tab);
                                   //
                              } else {
                                   //
                                   $pola_tab = array(array('products_short_description', '')); 
                                   $sql = $db->update_query('products_description', $pola_tab, 'products_id = ' . $produkt . ' and language_id = ' . (int)$ile_jezykow[$w]['id']);
                                   unset($pola_tab);
                                   //
                              }
                              //
                        }
                        //
                        unset($ile_jezykow);
                        //
                     }
                     //
                
                } 
                
                // kopiowanie opisow dodatkowych
                if ( $Opcja == 'opis_dodatkowy' ) {
                     //   
                     $opisy = array();
                     //
                     // pobiera dane opisow z produktu                     
                     $sql = $db->open_query("select * from products_description_additional where products_id = '" . $glowne_id . "'");
                     //
                     while ($info = $sql->fetch_assoc()) {
                         //
                         $opisy[$info['language_id']] = array($info['products_info_description_1'], $info['products_info_description_2']);
                         //
                     }
                     //
                     $db->close_query($sql);
                     unset($info); 
                    
                     // dodawanie do produktow
                     foreach ($_POST['id_produktow'] as $produkt) {
                        //
                        // kasuje 
                        $db->delete_query('products_description_additional' , "products_id = '" . $produkt . "'");     
                        // 
                        $ile_jezykow = Funkcje::TablicaJezykow();
                        //
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                              //
                              if ( isset($opisy[$ile_jezykow[$w]['id']]) ) {
                                   //
                                   $pola_tab = array(array('products_id', $produkt),
                                                     array('products_info_description_1', $opisy[$ile_jezykow[$w]['id']][0]),  
                                                     array('products_info_description_2', $opisy[$ile_jezykow[$w]['id']][1]),
                                                     array('language_id', (int)$ile_jezykow[$w]['id'])); 
                                   $sql = $db->insert_query('products_description_additional', $pola_tab);
                                   unset($pola_tab);
                                   //
                              }
                              //
                        }
                        //
                        unset($ile_jezykow);
                        //
                     }
                     //
                
                }                 
                
                // kopiowanie akcesoria dodatkowe
                if ( $Opcja == 'akcesoria' ) {
                     //
                     // pobiera dane z produktu
                     $sql = $db->open_query("select * from products_accesories where pacc_products_id_master = '" . $glowne_id . "'");
                     //
                     if ( (int)$db->ile_rekordow($sql) > 0) {
                          //
                          $pola = array();
                          //
                          while ($info = $sql->fetch_assoc()) {
                              //                        
                              unset($info['pacc_id']);
                              unset($info['pacc_products_id_master']);
                              //
                              $pola[] = $info;
                              //
                          }
                          //
                          unset($info); 
                         
                          // dodawanie do produktow
                          foreach ($_POST['id_produktow'] as $produkt) {
                              //
                              // kasuje 
                              $db->delete_query('products_accesories' , "pacc_products_id_master = '" . $produkt . "'");     
                              //      
                              foreach ($pola as $pola_tmp) {
                                  //
                                  $pola_tmp['pacc_products_id_master'] = $produkt;
                                  //
                                  $pola_tab = array();
                                  foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                      //
                                      $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                      //
                                  }                           
                                  //
                                  $db->insert_query('products_accesories', $pola_tab);
                                  //
                                  unset($pola_tab);
                                  //
                              }
                              //
                          }
                          //
                          unset($pola);
                          //
                     }
                     //
                     $db->close_query($sql);
                     //
                }     

                // kopiowanie produkty powiazane
                if ( $Opcja == 'produkty_powiazane' ) {
                     //
                     // pobiera dane z produktu
                     $sql = $db->open_query("select * from products_related_products where prp_products_id_master = '" . $glowne_id . "'");
                     //
                     if ( (int)$db->ile_rekordow($sql) > 0) {
                          //
                          $pola = array();
                          //
                          while ($info = $sql->fetch_assoc()) {
                              //                        
                              unset($info['prp_id']);
                              unset($info['prp_products_id_master']);
                              //
                              $pola[] = $info;
                              //
                          }
                          //                          
                          unset($info); 
                         
                          // dodawanie do produktow
                          foreach ($_POST['id_produktow'] as $produkt) {
                              //
                              // kasuje 
                              $db->delete_query('products_related_products' , "prp_products_id_master = '" . $produkt . "'");     
                              //      
                              foreach ($pola as $pola_tmp) {
                                  //
                                  $pola_tmp['prp_products_id_master'] = $produkt;
                                  //
                                  $pola_tab = array();
                                  foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                      //
                                      $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                      //
                                  }                           
                                  //
                                  $db->insert_query('products_related_products', $pola_tab);
                                  //
                                  unset($pola_tab);
                                  //
                              }
                              //
                          }
                          //
                          unset($pola);
                          //
                     }
                     //
                     $db->close_query($sql);
                     //
                }    

                // kopiowanie produkty podobne
                if ( $Opcja == 'produkty_podobne' ) {
                     //
                     // pobiera dane z produktu
                     $sql = $db->open_query("select * from products_options_products where pop_products_id_master = '" . $glowne_id . "'");
                     //
                     if ( (int)$db->ile_rekordow($sql) > 0) {
                          //
                          $pola = array();
                          //
                          while ($info = $sql->fetch_assoc()) {
                              //                        
                              unset($info['pop_id']);
                              unset($info['pop_products_id_master']);
                              //
                              $pola[] = $info;
                              //
                          }
                          //                          
                          unset($info); 
                          
                          // dodawanie do produktow
                          foreach ($_POST['id_produktow'] as $produkt) {
                              //
                              // kasuje 
                              $db->delete_query('products_options_products' , "pop_products_id_master = '" . $produkt . "'");     
                              //      
                              foreach ($pola as $pola_tmp) {
                                  //
                                  $pola_tmp['pop_products_id_master'] = $produkt;
                                  //
                                  $pola_tab = array();
                                  foreach ($pola_tmp as $pola_klucz => $pola_wartosc) {
                                      //
                                      $pola_tab[] = array($pola_klucz, $pola_wartosc);
                                      //
                                  }                           
                                  //
                                  $db->insert_query('products_options_products', $pola_tab);
                                  //
                                  unset($pola_tab);
                                  //
                              }
                              //
                          }
                          //
                          unset($pola);
                          //
                     }
                     //
                     $db->close_query($sql);
                     //
                
                }                  
                
                // kopiowanie wariantow
                if ( $Opcja == 'warianty' ) {
                     //   
                     $warianty = array();
                     //
                     // pobiera dane wariantow z produktu                     
                     $sqls = $db->open_query("select * from products where products_id = '" . $glowne_id . "'");
                     //
                     if ((int)$db->ile_rekordow($sqls) > 0) {
                         //
                         $info = $sqls->fetch_assoc();
                         //
                         // dodawanie do produktow
                         foreach ($_POST['id_produktow'] as $produkt) {
                            //
                            $pola_tab = array(
                                        array('products_other_variant_text',$info['products_other_variant_text']),
                                        array('products_other_variant_range',$info['products_other_variant_range']),
                                        array('products_other_variant_method',$info['products_other_variant_method']),
                                        array('products_other_variant_image',$info['products_other_variant_image']),
                                        array('products_other_variant_name',$info['products_other_variant_name']),
                                        array('products_other_variant_name_type',$info['products_other_variant_name_type']),
                                        array('products_other_variant_name_type',$info['products_other_variant_name_type']));
                            //
                            $sql = $db->update_query('products', $pola_tab, 'products_id = ' . (int)$produkt);
                            unset($pola_tab);
                            //
                         }
                         //
                     }
                     //
                     $db->close_query($sqls);
                     unset($info);                     
                
                }                 
                
            }          
          
        }
        
        Funkcje::PrzekierowanieURL('produkty.php?id_poz=' . $glowne_id);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kopiowanie danych</div>
    <div id="cont">
    
        <script>
        $(document).ready(function() {
          var ilosc_wybranych = 0;
          $('.WyborCheckbox input').click(function() {
             ilosc_wybranych = 0;
             $('.WyborCheckbox input').each(function() {
                if ( $(this).prop('checked') ) {
                  ilosc_wybranych++;
                }                
             });
             if (ilosc_wybranych > 0) {
                 $('#ButtonKopiuj').show();
              } else {
                 $('#ButtonKopiuj').hide();
             }
          });          
        });
        
        function lista_kopiowania(id, rodzaj) {
            $('#drzewo_produkty').remove();
            $('#wynik_produktow_produkty').remove();
            $('#formi').stop().slideDown('fast');
            //
            $('#lista_do_wyboru').html('<img src="obrazki/_loader_small.gif">');
            $.get("ajax/lista_produktow_do_wyboru.php",
                { modul: 'produkty', tok: $('#tok').val() },
                function(data) { 
                    $('#lista_do_wyboru').html(data);
                    pokazChmurki();
            });             
            pokazChmurki();            
        }
        </script>
        
        <form action="produkty/produkty_kopiuj.php" method="post" id="produktyForm" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Kopiowanie danych</div>
          
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
 
                ?>             
          
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" id="rodzaj_modulu" value="produkty" />
                    
                    <input type="hidden" name="id_wybrany_produkt" id="id_wybrany_produkt" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <table class="WyborCheckbox" style="margin-left:-5px">
                        <tr>
                            <td><label>Wybierz dane jakie chcesz skopiować do innych produktów:</label></td>
                            <td>
                                <input type="checkbox" value="cechy" name="kopiowanie[]" id="kopiowanie_cechy" /><label class="OpisFor" for="kopiowanie_cechy">cechy produktu (bez numerów katalogowych, dostępności, cen z kombinacji cech)</label><br />
                                <input type="checkbox" value="stock" name="kopiowanie[]" id="kopiowanie_stock" /><label class="OpisFor" for="kopiowanie_stock">cechy produktu (z numerami katalogowymi, dostępnościami, cenami z kombinacji cech)</label><br />
                                <input type="checkbox" value="pola_opisowe" name="kopiowanie[]" id="kopiowanie_pola_opisowe" /><label class="OpisFor" for="kopiowanie_pola_opisowe">dodatkowe pola opisowe</label><br />
                                <input type="checkbox" value="pola_tekstowe" name="kopiowanie[]" id="kopiowanie_pola_tekstowe" /><label class="OpisFor" for="kopiowanie_pola_tekstowe">dodatkowe pola tekstowe</label><br />
                                <input type="checkbox" value="kategorie" name="kopiowanie[]" id="kopiowanie_kategorie" /><label class="OpisFor" for="kopiowanie_kategorie">przypisane kategorie</label><br />
                                <input type="checkbox" value="wysylki" name="kopiowanie[]" id="kopiowanie_wysylki" /><label class="OpisFor" for="kopiowanie_wysylki">dostępne wysyłki</label><br />
                                <input type="checkbox" value="linki" name="kopiowanie[]" id="kopiowanie_linki" /><label class="OpisFor" for="kopiowanie_linki">zakładka linki</label><br />
                                <input type="checkbox" value="youtube" name="kopiowanie[]" id="kopiowanie_youtube" /><label class="OpisFor" for="kopiowanie_youtube">filmy Youtube</label><br />
                                <input type="checkbox" value="zakladka_1" name="kopiowanie[]" id="kopiowanie_zakladki_nr_1" /><label class="OpisFor" for="kopiowanie_zakladki_nr_1">dodatkowa zakładka opisowa nr 1</label><br />
                                <input type="checkbox" value="zakladka_2" name="kopiowanie[]" id="kopiowanie_zakladki_nr_2" /><label class="OpisFor" for="kopiowanie_zakladki_nr_2">dodatkowa zakładka opisowa nr 2</label><br />
                                <input type="checkbox" value="zakladka_3" name="kopiowanie[]" id="kopiowanie_zakladki_nr_3" /><label class="OpisFor" for="kopiowanie_zakladki_nr_3">dodatkowa zakładka opisowa nr 3</label><br />
                                <input type="checkbox" value="zakladka_4" name="kopiowanie[]" id="kopiowanie_zakladki_nr_4" /><label class="OpisFor" for="kopiowanie_zakladki_nr_4">dodatkowa zakładka opisowa nr 4</label><br />
                                <input type="checkbox" value="linki_powiazane" name="kopiowanie[]" id="kopiowanie_linki_powiazane" /><label class="OpisFor" for="kopiowanie_linki_powiazane">linki powiązane</label><br /> 
                                <input type="checkbox" value="warianty" name="kopiowanie[]" id="kopiowanie_warianty" /><label class="OpisFor" for="kopiowanie_warianty">inne warianty produktu</label><br /> 
                                <input type="checkbox" value="opis" name="kopiowanie[]" id="kopiowanie_opis" /><label class="OpisFor" for="kopiowanie_opis">opis główny produktu</label><br />
                                <input type="checkbox" value="opis_krotki" name="kopiowanie[]" id="kopiowanie_opis_krotki" /><label class="OpisFor" for="kopiowanie_opis_krotki">opis krótki produktu</label><br />
                                <input type="checkbox" value="opis_dodatkowy" name="kopiowanie[]" id="kopiowanie_opis_dodatkowy" /><label class="OpisFor" for="kopiowanie_opis_dodatkowy">opisy dodatkowe produktu</label><br />
                                <input type="checkbox" value="akcesoria" name="kopiowanie[]" id="kopiowanie_akcesoria" /><label class="OpisFor" for="kopiowanie_akcesoria">akcesoria dodatkowe</label><br />
                                <input type="checkbox" value="produkty_powiazane" name="kopiowanie[]" id="kopiowanie_produkty_powiazane" /><label class="OpisFor" for="kopiowanie_produkty_powiazane">produkty powiązane</label><br />
                                <input type="checkbox" value="produkty_podobne" name="kopiowanie[]" id="kopiowanie_produkty_podobne" /><label class="OpisFor" for="kopiowanie_produkty_podobne">produkty podobne</label><br />
                            </td>
                        </tr>
                    </table>

                    <div class="GlownyListing">

                        <div style="GlownyListingProduktyEdycja">                               

                            <div id="wynik_produktow_produkty" class="WynikProduktowProduktyKopiuj" style="display:none"></div>     

                            <div id="formi" style="display:none">

                                <input type="hidden" value="," id="jakie_id" />
                                
                                <div id="wybrane_produkty"></div>
                                
                                <div id="lista_do_wyboru"></div>

                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <script>
                    lista_kopiowania(<?php echo (int)$_GET['id_poz']; ?>);
                    </script>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButtonKopiuj" style="display:none" />
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty');">Powrót</button>   
                </div> 
                
            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
