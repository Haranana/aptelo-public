<?php
chdir('../');         

if (isset($_POST['pole']) && !empty($_POST['pole'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr() && isset($_POST['pole']) && WYSZUKIWANIE_PODPOWIEDZI == 'tak' && ( WYSZUKIWANIE_PODPOWIEDZI_PRODUKTY == 'tak' || WYSZUKIWANIE_PODPOWIEDZI_FRAZY == 'tak' )) {
      
        // typ szablonu
        $TypSzablonu = 'nowy';
        
        if ( isset($_POST['szablon']) && !empty($_POST['szablon']) ) {
             //
             if ( $_POST['szablon'] == 'stary' ) {
                  //
                  $TypSzablonu = 'stary';
                  //
             }
             //
        }

        // czyszczenie szukanej wartosci
        
        $_POST['pole'] = rawurldecode(strip_tags((string)$_POST['pole']));
        
        // zamienia zmienne na poprawne znaki
        $_POST['pole'] = str_replace(array('[back]', '[proc]'), array('/', '%'), (string)$_POST['pole']);
        
        // zabezpieczenie przez hackiem
        $_POST['pole'] = str_replace(array('">', '<"'), array('', ''), (string)$_POST['pole']);           

        if ( trim((string)$_POST['pole']) != '' && strlen((string)$_POST['pole']) > 1 ) {

             // podzial fraz na wyrazy    
             if ( WYSZUKIWANIE_DOKLADNA_FRAZA == 'nie' ) {
                  //
                  $SzukaneFrazy = explode(' ', (string)$_POST['pole']);
                  //
             } else {
                  //
                  $SzukaneFrazy = array($_POST['pole']);
                  //
             }

             // tablica szukanych fraz
             $SzukaneFrazyWynik = array();
 
             // sprawdzanie czy dlugosc frazy wieksza od 1 znaku
             foreach ( $SzukaneFrazy as $FrazaTmp ) {
                  //
                  if ( strlen((string)$FrazaTmp) > 1 ) {
                       $SzukaneFrazyWynik[] = $FrazaTmp;
                  }
                  //
                  unset($FrazaTmp);
                  //
             }

             // zamiana na pl znaki
             $SzukaneFrazy = array();
     
             if ( WYSZUKIWANIE_PL_ZNAKI == 'tak' ) {
                  //
                  foreach ( $SzukaneFrazyWynik as $FrazaTmp ) {
                      //
                      $SzukaneFrazy[] = Funkcje::ZamienPlZnaki(preg_quote($FrazaTmp, '/'));
                      //
                  }
                  //
                   unset($FrazaTmp);
                  //
             } else {
                  //
                  $SzukaneFrazyWynikTmp = array();
                  //
                  foreach ( $SzukaneFrazyWynik as $Tmp ) {
                       //
                       $SzukaneFrazyWynikTmp[] = preg_quote($Tmp, '/');
                       //
                  }
                  //
                  $SzukaneFrazy = $SzukaneFrazyWynikTmp;
                  //
                  unset($SzukaneFrazyWynikTmp);
                  //
             }

             // podstawowe pola do wyszukiwania
             $ZapytaniePola = array('p.products_id',
                                    'pd.products_name');
                                    
             // zapytanie o produkty
             $zapytanie = "SELECT DISTINCT " . implode(', ', (array)$ZapytaniePola) . "
                           FROM products p 
                           LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                           WHERE p.products_status = '1' and p.listing_status = '0' and ( (p.customers_group_id = '0' or p.customers_group_id = '' ) )";

             unset($ZapytaniePola, $TablicaZapytania, $TablicaWarunki);      
     
             $sql = $GLOBALS['db']->open_query($zapytanie);
             
             // tablica z id produktow 
             $IdProduktow = array(0);             
         
             // tablica slow
             $TablicaSlow = array();
             
             // wyszukiwanie produktow
             
             if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
     
                 while ( $info = $sql->fetch_assoc() ) {

                     foreach ( $info as $TmpKlucz => $TmpId ) {
                       
                         if ( $TmpKlucz != 'products_id' ) {
     
                             // zliczy ile razy wystapil ciag
                             $IleWystapien = 0;
                             
                             $TmpId = strip_tags((string)$TmpId);
                           
                             foreach ( $SzukaneFrazy as $Fraza ) {
         
                                 // szuka czy dana fraza wystepuje                         
                                 if ( trim((string)$TmpId) != '' ) {
                                      //
                                      if ( @preg_match('/' . $Fraza . '/ui', $TmpId ) ) {
                                           $IleWystapien++;
                                      }
                                      //
                                 }
                                 
                             }
         
                             // jezeli nie bylo id dodaje do tablicy
                             if ( !isset($IdProduktow[$info['products_id']]) && $IleWystapien == count($SzukaneFrazy) ) {
                                  //
                                  $IdProduktow[] = $info['products_id'];
                                  //
                             }   
                             
                         }
         
                         unset($TmpId, $TmpKlucz);
                         
                     }
                     
                 }
                 
                 unset($info);
                 
             }
                 
             $GLOBALS['db']->close_query($sql);
             unset($zapytanie);

             $zapytanie = Produkty::SqlSzukajProdukty( " AND p.products_id IN (" . implode(',', (array)$IdProduktow) . ")", 'pd.products_name ASC' ); 
             $sql = $GLOBALS['db']->open_query($zapytanie);            

             if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) { 
             
                  // unikalne klasy css
                  $DopuszczalneZnaki = '1_234567890_qwertyui_opasdfgh_jkklzxc_vbnm';
                  
                  $RandCssStart = 'x_';
                  $RandCssKoniec = 'q_';
                  for ( $i = 0; $i <= 20; $i++ ) {
                      $RandCssStart .= $DopuszczalneZnaki[rand()%(strlen((string)$DopuszczalneZnaki))];
                      $RandCssKoniec .= $DopuszczalneZnaki[rand()%(strlen((string)$DopuszczalneZnaki))];
                  }                        
                  unset($DopuszczalneZnaki);                  
             
                  // czy byl produkt
                  $BylProdukt = 0;

                  // tablica z wynikami
                  $ZwrocProdukty = array();
                  $ZwrocSlowa = array();

                  while ($produkt = $sql->fetch_assoc()) {
                    
                      if ( WYSZUKIWANIE_DOKLADNA_FRAZA == 'nie' && WYSZUKIWANIE_PODPOWIEDZI_FRAZY == 'tak' ) {

                           // szuka samych fraz
                           $PodzielSlowa = explode(' ', mb_strtolower((string)$produkt['products_name'], 'UTF-8'));
                          
                           foreach ( $PodzielSlowa as $Slowo ) {
                            
                               // szuka czy dana fraza wystepuje                         
                               if ( trim((string)$Slowo) != '' ) {
                                    //
                                    foreach ( $SzukaneFrazy as $Fraza ) {
                                        //
                                        if ( @preg_match('/' . $Fraza . '/i', $Slowo) ) {
                                             //
                                             if ( !in_array(trim((string)$Slowo), $TablicaSlow) ) {
                                                  $TablicaSlow[] = trim((string)$Slowo);
                                             }
                                             //
                                        }
                                        //
                                    }
                                    //
                               }
                               
                               unset($Slowo);

                           }

                           unset($PodzielSlowa);
                          
                      }

                      if ( WYSZUKIWANIE_PODPOWIEDZI_PRODUKTY == 'tak' ) {

                          // produkty
                          if ( $BylProdukt < WYSZUKIWANIE_PODPOWIEDZI_PRODUKTY_ILOSC ) {
                              if ( $TypSzablonu == 'stary' ) {
                                
                                  $Produkt = new Produkt( $produkt['products_id'], 40, 40, '', false );
                          
                                  $ZwrocProduktyTmp = '<tr class="PodpowiedzFraza" role="button" tabindex="0" onclick="$.pobierzAutoodpowiedzProdukt(\'' . $Produkt->info['adres_seo'] . '\')">';

                                      $ZwrocProduktyTmp .= '<td style="width:40px">' . $Produkt->fotoGlowne['zdjecie'] . '</td>';
                                      
                                      $ZwrocProduktyTmp .= '<td>' . $Produkt->info['nazwa'] . '</td>';
                                      
                                      $ZwrocProduktyTmp .= '<td>' . $Produkt->info['cena_brutto'] . '</td>';
                                  
                                  $ZwrocProduktyTmp .= '</tr>';
                                  
                                  $ZwrocProdukty[] = $ZwrocProduktyTmp;
                                 
                                  unset($Produkt, $ZwrocProduktyTmp);   
                                
                              } else {
                              
                                  $Produkt = new Produkt( $produkt['products_id'], 100, 100, '', false );
                         
                                  $ZwrocProduktyTmp = '<a class="PodpowiedzProdukt" href="' . $Produkt->info['adres_seo'] . '">';

                                      $ZwrocProduktyTmp .= '<span class="PodpowiedzProduktFoto">' . $Produkt->fotoGlowne['zdjecie'] . '</span>';

                                      $ZwrocProduktyTmp .= '<span class="PodpowiedzProduktNazwaCena">';
                                          
                                          $ZwrocProduktyTmp .= $Produkt->info['nazwa'];

                                          $ZwrocProduktyTmp .= '<span class="PodpowiedzProduktCena">' . $Produkt->info['cena_brutto'] . '</span>';
                                     
                                      $ZwrocProduktyTmp .= '</span>';
     
                                  $ZwrocProduktyTmp .= '</a>';
                                  
                                  $ZwrocProdukty[] = $ZwrocProduktyTmp;
                                 
                                  unset($Produkt, $ZwrocProduktyTmp);    
         
                              }
                          }
                      
                      }
                      
                      $BylProdukt++;

                  }
                  
                  // parmutacje - frazy permutacje
                  
                  if ( WYSZUKIWANIE_DOKLADNA_FRAZA == 'nie' && WYSZUKIWANIE_PODPOWIEDZI_FRAZY == 'tak' && WYSZUKIWANIE_PERMUTACJE == 'tak' ) {
                    
                       if ( count($SzukaneFrazy) > 1 && count($SzukaneFrazy) < 4 ) {
                            //
                            $TablicaSlowDoPermutacji = $TablicaSlow;
                            //
                            if ( count($TablicaSlow) > 7 ) {
                                 //
                                 $TablicaSlowDoPermutacji = $SzukaneFrazyWynik;
                                 //
                            }
                            //
                            if ( count($TablicaSlowDoPermutacji) < 8 ) {
                                 //
                                 $TablicaTmp = [[]]; 
                                 //
                                 foreach ( $TablicaSlowDoPermutacji as $Element ) {
                                      //
                                      foreach ( $TablicaTmp as $Kombinacje ) {
                                          //
                                          foreach (array_diff($TablicaSlow, $Kombinacje) as $Tmp ) {
                                              //
                                              $Polacz = @array_merge($Kombinacje,[$Tmp]);
                                              $TablicaTmp[implode(' ', (array)$Polacz)] = $Polacz;
                                              //
                                          }
                                      }
                                 }
                                 //
                                 unset($TablicaTmp[0]); 
                                 //
                                 $Permutacje = array_keys($TablicaTmp);
                                 //
                                 $sql->data_seek(0);
                                  
                                 // szuka permutacji w nazwach produktow
                                  
                                 while ($produkt = $sql->fetch_assoc()) {
                                    
                                      foreach ( $Permutacje as $FrazaTmp ) {
       
                                           if ( trim((string)$FrazaTmp) != '' ) {
                                                //
                                                if ( @preg_match('/' . $FrazaTmp . '/i', $produkt['products_name']) ) {
                                                     //
                                                     if ( !in_array(trim((string)$FrazaTmp), $TablicaSlow) ) {
                                                          $TablicaSlow[] = trim((string)$FrazaTmp);
                                                     }
                                                     //
                                                }  
                                                //
                                           }
                  
                                           unset($FrazaTmp);
            
                                      }                            
                                     
                                 }
                            
                                 unset($Permutacje, $TablicaTmp);
                                 
                            }
                                 
                            unset($TablicaSlowDoPermutacji);
                             
                       }
                      
                  }
                  
                  // tworzenie wynikow

                  if ( count($TablicaSlow) > 0 ) {
                      
                      $TablicaSlow = array_unique($TablicaSlow);

                      foreach ( $TablicaSlow as $Slowa ) {

                          $LosowaWartosc = rand(1000000,19999999);
                          
                          if ( $TypSzablonu == 'stary' ) {
                                      
                              $ZwrocSlowaTmp = '<tr class="PodpowiedzFraza" role="button" tabindex="0" onclick="$.pobierzAutoodpowiedz(' . $LosowaWartosc . ')" onkeydown="if(event.key === \' \') { spacjaWcisnieta = true; }">';

                                  $ZwrocSlowaTmp .= '<td colspan="2">';
                                  
                                  $ZwrocSlowaTmp .= '<input type="hidden" value="' . $Slowa . '" id="auto_' . $LosowaWartosc . '" />';

                                  // podswietla fragmenty nazwy

                                  foreach ( $SzukaneFrazy as $Fraza ) {
                                  
                                      $Slowa = preg_replace("/(" . $Fraza . ")/i", $RandCssStart . '$1' . $RandCssKoniec, (string)$Slowa, 1); 
                                      
                                  }
                                  
                                  $ZwrocSlowaTmp .= $Slowa;
                                  
                                  $ZwrocSlowaTmp .= '</td><td></td>';

                              $ZwrocSlowaTmp .= '</tr>';                            

                              $ZwrocSlowa[] = $ZwrocSlowaTmp;
                              
                              unset($ZwrocSlowaTmp);
                              
                          } else {
                  
                              $ZwrocSlowaTmp = '<span class="PodpowiedzFraza" role="button" tabindex="0" onclick="$.pobierzAutoodpowiedz(' . $LosowaWartosc . ')" onkeydown="if(event.key === \' \') { spacjaWcisnieta = true; }">';

                              $ZwrocSlowaTmp .= '<input type="hidden" value="' . $Slowa . '" id="auto_' . $LosowaWartosc . '" />';
                                
                              // podswietla fragmenty nazwy

                              foreach ( $SzukaneFrazy as $Fraza ) {
                              
                                  $Slowa = preg_replace("/(" . $Fraza . ")/i", $RandCssStart . '$1' . $RandCssKoniec, (string)$Slowa, 1); 
                                  
                              }
                              
                              $ZwrocSlowaTmp .= $Slowa;

                              $ZwrocSlowaTmp .= '</span>';

                              $ZwrocSlowa[] = $ZwrocSlowaTmp;
                              
                              unset($ZwrocSlowaTmp);
                              
                          }

                          unset($LosowaWartosc);
                          
                      }
                      
                  }

                  if ( $BylProdukt > 0 ) {
                    
                      if ( $TypSzablonu == 'stary' ) {
                        
                           echo '<table>';

                           if ( count($ZwrocSlowa) > 0 && WYSZUKIWANIE_PODPOWIEDZI_FRAZY == 'tak' ) {
                                //
                                $DaneSlow = implode('', (array)$ZwrocSlowa);
                                //
                                $DaneSlow = str_replace($RandCssStart, '<span class="zaznacz">', (string)$DaneSlow);
                                $DaneSlow = str_replace($RandCssKoniec, '</span>', (string)$DaneSlow);
                                //
                                echo $DaneSlow;
                                //
                                unset($DaneSlow);
                                //
                           }
                           
                           if ( count($ZwrocProdukty) > 0 && WYSZUKIWANIE_PODPOWIEDZI_PRODUKTY == 'tak' ) {
                                //
                                $DaneProduktow = implode('', (array)$ZwrocProdukty);
                                //
                                $DaneProduktow = str_replace($RandCssStart, '<span class="zaznacz">', (string)$DaneProduktow);
                                $DaneProduktow = str_replace($RandCssKoniec, '</span>', (string)$DaneProduktow);
                                //
                                echo $DaneProduktow;
                                //
                                unset($DaneProduktow);
                                //
                           }                           
        
                           echo '</table>';

                      } else {

                           echo '<div class="OknoAutouzupelnienia">';
                           
                               echo '<div class="OknoAutouzupelnieniaZamknij" tabindex="0" role="button"><b>X</b></div>';
                               
                               echo '<div class="OknoAutouzupelnieniaTresc">';

                                   echo '<div class="OknoAutouzupelnieniaKontener">';

                                       if ( count($ZwrocSlowa) > 0 && WYSZUKIWANIE_PODPOWIEDZI_FRAZY == 'tak' ) {
                                            //
                                            $DaneSlow = implode('', (array)$ZwrocSlowa);
                                            //
                                            $DaneSlow = str_replace($RandCssStart, '<span class="zaznacz">', (string)$DaneSlow);
                                            $DaneSlow = str_replace($RandCssKoniec, '</span>', (string)$DaneSlow);
                                            //
                                            echo '<div class="OknoAutouzupelnieniaSlowa"' . ((WYSZUKIWANIE_PODPOWIEDZI_PRODUKTY == 'nie') ? 'style="width:100%"' : '') . '>
                                                    
                                                    <strong class="NaglowekPodpowiedzi">' . $GLOBALS['tlumacz']['PASUJACE_FRAZY'] . '</strong>
                                                    
                                                    <div class="OknoAutouzupelnieniaListaSlow">' . $DaneSlow . '</div>
                                                    
                                                  </div>';
                                            //
                                            unset($DaneSlow);
                                            //
                                       }
                                       
                                       if ( count($ZwrocProdukty) > 0 && WYSZUKIWANIE_PODPOWIEDZI_PRODUKTY == 'tak' ) {
                                            //
                                            $DaneProduktow = implode('', (array)$ZwrocProdukty);
                                            //
                                            $DaneProduktow = str_replace($RandCssStart, '<span class="zaznacz" role>', (string)$DaneProduktow);
                                            $DaneProduktow = str_replace($RandCssKoniec, '</span>', (string)$DaneProduktow);
                                            //
                                            echo '<div class="OknoAutouzupelnieniaProdukty"' . ((WYSZUKIWANIE_DOKLADNA_FRAZA == 'tak' || WYSZUKIWANIE_PODPOWIEDZI_FRAZY == 'nie') ? 'style="width:100%"' : '') . '>
                                            
                                                    <strong class="NaglowekPodpowiedzi">' . $GLOBALS['tlumacz']['PASUJACE_PRODUKTY'] . '</strong>
                                                    
                                                    <div class="OknoAutouzupelnieniaListaProduktow">' . $DaneProduktow . '</div>
                                                    
                                                  </div>';
                                            //
                                            unset($DaneProduktow);
                                            //
                                       }
                                       
                                    echo '</div>';
                                    
                                echo '</div>';

                           echo '</div>';
                           
                      }

                  }
                  
                  unset($ZwrocProdukty, $ZwrocSlowa, $TablicaSlow);

            }
            
            $db->close_query($sql);
            unset($zapytanie);
            
        }

    }
    
}

?>