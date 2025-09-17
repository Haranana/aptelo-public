<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["bez_cech"] == '0') {

            $pola = array(
                    array('products_quantity',$filtr->process($_POST["ilosc"])),
                    array('products_availability_id',(int)$_POST["dostepnosc"]),
                    array('products_shipping_time_id',(int)$_POST["wysylka"]),
                    array('products_shipping_time_zero_quantity_id',(int)$_POST["wysylka_zero"])
                    );
            //			
            $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'");	
            unset($pola);

        } else {
        
            $suma_ilosci = 0;
        
            for ($r = 1; $r < (int)$_POST["ilosc_magazynu"]; $r++) {
                //
                $Ilosc = 0;
                if (CECHY_MAGAZYN == 'tak') {
                    $Ilosc = $filtr->process($_POST["ilosc_" . $r]);
                }
                //
                $pola = array(
                        array('products_stock_quantity',$Ilosc),
                        array('products_stock_availability_id',(int)$_POST["dostepnosc_" . $r]),
                        array('products_stock_shipping_time_id',(int)$_POST["czas_wysylki_" . $r]),
                        array('products_id',(int)$_POST["id"]),
                        array('products_stock_attributes',$filtr->process($_POST["id_cechy_" . $r])),
                        array('products_stock_model',$filtr->process($_POST["nr_kat_" . $r])),
                        array('products_stock_ean',$filtr->process($_POST["ean_" . $r])));
                //	
                unset($Ilosc);
                
                // ceny cechy i obrazek cechy
                $TablicaCen = unserialize(base64_decode((string)$_POST["ceny_cechy_" . $r]));
                
                foreach ( $TablicaCen as $DodPozycja ) {
                  
                    $pola[] = array( $DodPozycja[0], $DodPozycja[1] );
                  
                }

                unset($TablicaCen);

                // kasuje rekordy w tablicy
                $db->delete_query('products_stock' , " products_id = '".(int)$_POST["id"]."' and products_stock_attributes = '".$filtr->process($_POST["id_cechy_" . $r])."'");	            
                
                // sprawdzi czy nie ma zdjecia cechy
                $ZdjecieCechy = false;
                foreach ( $pola as $spr ) {
                    //
                    if ( $spr[0] == 'products_stock_image' ) {
                         if ( $spr[1] != '' ) {
                              $ZdjecieCechy = true;
                         }
                    }
                    //
                }
                
                //
                if ($filtr->process($_POST["ilosc_" . $r]) != '' || $filtr->process($_POST["dostepnosc_" . $r]) > 0 || $filtr->process($_POST["czas_wysylki_" . $r]) > 0 || $filtr->process($_POST["nr_kat_" . $r]) != '' || $filtr->process($_POST["ean_" . $r]) != '' || $ZdjecieCechy == true) {
                    $db->insert_query('products_stock' , $pola);	
                }
                unset($pola, $ZdjecieCechy);
                //      
                $suma_ilosci = $suma_ilosci + (float)$filtr->process($_POST["ilosc_" . $r]);
                //
            }
            
            //
            if (CECHY_MAGAZYN == 'nie') {
                $suma_ilosci = $filtr->process($_POST["ilosc"]);
            }
            //
            $pola = array(
                    array('products_quantity',$suma_ilosci),
                    array('products_availability_id',(int)$_POST["dostepnosc"]),
                    array('products_shipping_time_id',(int)$_POST["wysylka"]),
                    array('products_shipping_time_zero_quantity_id',(int)$_POST["wysylka_zero"])
                    );
            //			
            $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'");	
            unset($pola);
            //            
        
        }
        
        if ( isset($_GET['dostep']) && ( $_GET['dostep'] != $filtr->process($_POST["dostepnosc"]) ) ) {
             unset($_GET);
        }    
        if ( isset($_GET['wysylka']) && ( $_GET['wysylka'] != $filtr->process($_POST["wysylka"]) ) ) {
             unset($_GET);
        }         
        
        if ( isset($_POST['produkt']) && (int)$_POST['produkt'] == (int)$_POST["id"] ) {
             //
             Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty.php?id_poz='.(int)$_POST["id"]);
             //
        } else {
             //
             Funkcje::PrzekierowanieURL('produkty_magazyn.php?id_poz='.(int)$_POST["id"]);
             //
        }
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <form action="produkty_magazyn/produkty_magazyn_edytuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <input type="hidden" name="rodzaj_cech" value="<?php echo $info['options_type']; ?>" />
                    
                    <?php if ( isset($_GET['produkt']) ) { ?>
                    <input type="hidden" name="produkt" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <?php } ?>
                    
                    <?php
                    $cechy = "select distinct * from products_attributes where products_id = '".$info['products_id']."'";
                    $sqlc = $db->open_query($cechy); 
                    //
                    if ($db->ile_rekordow($sqlc) > 0) { 
                        $zCechami = true;
                    } else {
                        $zCechami = false;
                    }
                    ?>
                    
                    <?php if ($zCechami == true) { ?>
                        
                        <input type="hidden" name="bez_cech" value="1" />

                        <?php
                        
                        if ( CECHY_KOMBINACJE_WSZYSTKIE == 'nie' ) {
                             //
                             echo '<div class="DodatkoweKombinacje">Dostępnymi kombinacjami cech można zarządzać w edycji produktu <a href="/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . $info['products_id'] . '&zakladka=5">edycja</a></div>';
                             //
                        }
                        
                        // tworzenie tablic z wartosciami cech
                        $wartosciCech = array(); 
                        //
                        $cechy = "select * from products_options_values where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                        $sqlc = $db->open_query($cechy);
                        while ($cecha = $sqlc->fetch_assoc()) {
                               $wartosciCech[ $cecha['products_options_values_id'] ] = $cecha['products_options_values_name'];
                        }
                        $db->close_query($sqlc);
                        unset($cecha, $cechy);        
                        //
                        
                        // tworzenie tablic z nazwami cech
                        $nazwyCech = array(); 
                        //
                        $cechy = "select * from products_options where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                        $sqlc = $db->open_query($cechy);
                        while ($cecha = $sqlc->fetch_assoc()) {
                               $nazwyCech[ $cecha['products_options_id'] ] = $cecha['products_options_name'];
                        }
                        $db->close_query($sqlc);
                        unset($cecha, $cechy);        
                        //        
                        
                        // tworzenie tablicy dostepnosci
                        $zapytanieDostepnosc = "select distinct * from products_availability p, products_availability_description pd where p.products_availability_id = pd.products_availability_id and p.mode = '0' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by pd.products_availability_name";
                        $sqls = $db->open_query($zapytanieDostepnosc);
                        //
                        $tablicaDostepnosci = array( array('id' => 0, 'text' => '-- brak --'),
                                                     array('id' => 99999, 'text' => 'AUTOMATYCZNY') );
                        while ($infs = $sqls->fetch_assoc()) { 
                              $tablicaDostepnosci[] = array('id' => $infs['products_availability_id'], 'text' => $infs['products_availability_name']);
                        }
                        $db->close_query($sqls);    
                        unset($zapytanieDostepnosc, $infs);
                        
                        
                        // tworzenie tablicy czasu wysylki
                        $zapytanieCzasWysylki = "select distinct * from products_shipping_time pst, products_shipping_time_description pstd where pst.products_shipping_time_id = pstd.products_shipping_time_id and pstd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by pst.products_shipping_time_day";
                        $sqls = $db->open_query($zapytanieCzasWysylki);
                        //
                        $tablicaCzasWysylki = array( array('id' => 0, 'text' => '-- brak --'));
                        while ($infs = $sqls->fetch_assoc()) { 
                              $tablicaCzasWysylki[] = array('id' => $infs['products_shipping_time_id'], 'text' => $infs['products_shipping_time_name']);
                        }
                        $db->close_query($sqls);    
                        unset($zapytanieCzasWysylki, $infs);                           
        
        
                        // wyszukiwanie cech z tablicy
                        $cechy_zapytanie = "select distinct pa.options_id, po.products_options_name, po.products_options_type from products_attributes pa, products_options po where pa.products_id = '".(int)$info['products_id']."' and pa.options_id = po.products_options_id and po.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by po.products_options_sort_order asc";
                        $sqlq = $db->open_query($cechy_zapytanie);                         
                        //
                        while ($cecha_pozycja = $sqlq->fetch_assoc()) { 
                            //
                            // wyszukiwanie wartosci cechy z tablicy
                            $cechy_wartosci_zapytanie = "select distinct * from products_attributes pa, products_options_values_to_products_options pv where pa.products_id = '".$info['products_id']."' and pa.options_id = '".(int)$cecha_pozycja['options_id']."' and pa.options_values_id = pv.products_options_values_id order by pv.products_options_values_sort_order asc";
                            $sqlw = $db->open_query($cechy_wartosci_zapytanie); 
                            
                            $CiagDoWartosci = '';                            
                        
                            while ($cecha_wartosc = $sqlw->fetch_assoc()) { 
                                $CiagDoWartosci .= $cecha_wartosc['products_options_values_id'].',';
                            }
                            
                            $CiagDoWartosci = substr((string)$CiagDoWartosci,0,-1);
                            $CiagDoTablic[$cecha_pozycja['options_id']] = explode(',', (string)$CiagDoWartosci);
                            $pozCech = $cecha_pozycja['options_id']; 
                        }
                        
                        if (CECHY_MAGAZYN == 'tak') {
                            echo '<div class="CechyNaglowek">Stan magazynowy cech produktu / dostępność produktów / nr katalogowy</div>';
                          } else {   
                            echo '<div class="CechyNaglowek">Dostępność produktów / nr katalogowy</div>';
                        }
                        
                        echo '<div class="StanyCech">';
                        echo '<table class="TablicaCechy">';         

                        if (isset($CiagDoTablic) && count($CiagDoTablic) > 1) {
                          
                            if ( CECHY_KOMBINACJE_WSZYSTKIE == 'tak' ) {
                              
                                  $tab = Funkcje::Permutations($CiagDoTablic);
                            
                            } else {
                              
                                  $tab = array();
                                  
                                  // szuka w bazie dodanych cech
                                  $cez = "select distinct products_stock_attributes from products_stock where products_id = '" . $info['products_id'] . "'";
                                  $sqlw = $db->open_query($cez);    
                                  //
                                  while ($infe = $sqlw->fetch_assoc()) {                
                                      //
                                      $tabs = array();
                                      //
                                      $tmpc = explode(',', (string)$infe['products_stock_attributes']);
                                      //
                                      foreach ( $tmpc as $klucz => $wart ) {
                                          //
                                          $podzial = explode('-', (string)$wart);
                                          //
                                          $tabs[$podzial[0]] = $podzial[1];
                                          //
                                          unset($podzial);
                                          //
                                      }
                                      //
                                      $tab[] = $tabs;
                                      //
                                      unset($tabs, $tmpc);
                                      //
                                  }
                                  //
                                  $db->close_query($sqlw);
                                  unset($infe, $cez);
                                  
                            }
                            
                            if ( isset($tab) && count($tab) == 0 && CECHY_KOMBINACJE_WSZYSTKIE == 'nie' ) {
                                  //
                                  echo '<tr><td>Brak wybranych kombinacji cech ...</td></tr>';
                                  //
                            } else {
                                  //                            
                                  $tworzenie_naglowka = false;
                                  $id_magazyn = 1;
                                  //
                                  foreach($tab as $tablica) {
                                      $ciag = '';
                                      ksort($tablica);
                                      foreach($tablica as $klucz => $wartosc) {
                                          $ciag .= $klucz . '-' . $wartosc . ',';
                                      }
                                      $stan = substr((string)$ciag,0,-1);
                                      //
                                      if ($tworzenie_naglowka == false) {
                                          echo '<tr>';
                                          //
                                          // tworzenie naglowka cech
                                          $ng_cech = explode(',', (string)$stan);
                                          for ($r = 0, $c = count($ng_cech); $r < $c; $r++) {
                                              //
                                              // ustala nazwy cech do naglowka
                                              $do = explode('-', (string)$ng_cech[$r]);
                                              echo '<td class="Nagl">';
                                              
                                              if ( isset( $nazwyCech[$do[0]] ) ) {
                                                   echo $nazwyCech[$do[0]];
                                              }
                                              
                                              echo '</td>'; 
                                              unset($do);                      
                                              //
                                          }
                                          //
                                          if (CECHY_MAGAZYN == 'tak') {
                                              echo '<td class="Nagl">Ilość</td>';
                                          }
                                          echo '<td class="Nagl">Dostępność</td>';
                                          echo '<td class="Nagl">Czas wysyłki</td>';
                                          echo '<td class="Nagl">Nr katalogowy</td>';
                                          echo '<td class="Nagl">Kod EAN</td>'; 
                                          
                                          echo '</tr>';
                                          //
                                          $tworzenie_naglowka = true;
                                      }
                                      //
                                      // generowanie poszczegolnych pozycji
                                      echo '<tr>';
                                      $ng_cech = explode(',', (string)$stan);
                                      for ($r = 0, $c = count($ng_cech); $r < $c; $r++) {
                                          // ustala wartosci cech
                                          $do = explode('-', (string)$ng_cech[$r]);
                                          echo '<td>';
                                          
                                          if ( isset( $wartosciCech[$do[1]] ) ) {
                                               echo $wartosciCech[$do[1]];
                                          }
                                          
                                          echo '</td>';
                                          unset($do);                  
                                          //
                                      }
                                      //

                                      // szuka w bazie ilosci magazynu
                                      $cec = "select distinct * from products_stock where products_id = '".$info['products_id'] ."' and products_stock_attributes = '".$stan."'";
                                      $sqlw = $db->open_query($cec);  
                                      //
                                      if ((int)$db->ile_rekordow($sqlw) > 0) {
                                          //                                        
                                          $ilosc_cechy = $sqlw->fetch_assoc(); 
                                          //
                                      } else {
                                          //
                                          $ilosc_cechy = array();
                                          $ilosc_cechy['products_stock_quantity'] = 0;
                                          $ilosc_cechy['products_stock_model'] = '';
                                          $ilosc_cechy['products_stock_ean'] = '';
                                          $ilosc_cechy['products_stock_size'] = 0;
                                          $ilosc_cechy['products_stock_availability_id'] = '';
                                          $ilosc_cechy['products_stock_shipping_time_id'] = '';
                                          $ilosc_cechy['products_stock_image'] = '';
                                          //
                                          for ($x = 1; $x <= ILOSC_CEN; $x++) {
                                               //
                                               $ilosc_cechy['products_stock_price' . (($x == 1) ? '' : '_' . $x )] = '';
                                               $ilosc_cechy['products_stock_tax' . (($x == 1) ? '' : '_' . $x )] = '';
                                               $ilosc_cechy['products_stock_price_tax' . (($x == 1) ? '' : '_' . $x )] = '';
                                               $ilosc_cechy['products_stock_retail_price' . (($x == 1) ? '' : '_' . $x )] = '';
                                               $ilosc_cechy['products_stock_old_price' . (($x == 1) ? '' : '_' . $x )] = '';
                                               //
                                          }
                                          //
                                      }
                                      $db->close_query($sqlw);                    
                                      //
                                      
                                      if (CECHY_MAGAZYN == 'tak') {    
                                          echo '<td>
                                                    <input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$stan.'" />
                                                    <input type="text" class="kropka" name="ilosc_'.$id_magazyn.'" size="8" value="'.(($ilosc_cechy['products_stock_quantity'] == 0) ? '' : $ilosc_cechy['products_stock_quantity']).'" />
                                                </td>';
                                      }
                                                
                                      echo '<td>';
                                      
                                            if (CECHY_MAGAZYN == 'nie') {    
                                                echo '<input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$stan.'" />';
                                                echo '<input type="hidden" name="ilosc_'.$id_magazyn.'" value="0" />';
                                            }                                
                                      
                                            //                          
                                            echo Funkcje::RozwijaneMenu('dostepnosc_'.$id_magazyn, $tablicaDostepnosci, $ilosc_cechy['products_stock_availability_id']);         
                                            //

                                            $ceny_cech = array();
                                            
                                            $ceny_cech[] = array('products_stock_price', $ilosc_cechy['products_stock_price']);
                                            $ceny_cech[] = array('products_stock_tax', $ilosc_cechy['products_stock_tax']);
                                            $ceny_cech[] = array('products_stock_price_tax', $ilosc_cechy['products_stock_price_tax']);
                                            $ceny_cech[] = array('products_stock_image', $ilosc_cechy['products_stock_image']);
                                            
                                            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                                                //
                                                $ceny_cech[] = array('products_stock_price_'.$x, $ilosc_cechy['products_stock_price_'.$x]);
                                                $ceny_cech[] = array('products_stock_tax_'.$x, $ilosc_cechy['products_stock_tax_'.$x]);
                                                $ceny_cech[] = array('products_stock_price_tax_'.$x, $ilosc_cechy['products_stock_price_tax_'.$x]);
                                                //
                                            }                                           
                                            
                                            echo '<input type="hidden" name="ceny_cechy_' . $id_magazyn . '" value="' . base64_encode(serialize($ceny_cech)) . '" />';
                                            
                                            unset($ceny_cech);

                                      echo '</td>';
                                      
                                      echo '<td>';
                                            //                          
                                            echo Funkcje::RozwijaneMenu('czas_wysylki_'.$id_magazyn, $tablicaCzasWysylki, $ilosc_cechy['products_stock_shipping_time_id']);         
                                            //
                                      echo '</td>';

                                      echo '<td>
                                                <input type="text" name="nr_kat_'.$id_magazyn.'" size="30" value="'.$ilosc_cechy['products_stock_model'].'" />
                                            </td>';   

                                      echo '<td>
                                                <input type="text" name="ean_'.$id_magazyn.'" size="20" value="'.$ilosc_cechy['products_stock_ean'].'" />
                                            </td>';                                        
                                      
                                      echo '</tr>';
                                      
                                      $id_magazyn++;
                                      unset($cec, $ilosc_cechy);                     
                                      //            
                                  }
                                  
                            }

                          } else { 
                          
                            $tab = array();
                            
                            if ( CECHY_KOMBINACJE_WSZYSTKIE == 'nie' ) {
                            
                                // szuka w bazie dodanych cech
                                $cez = "select distinct products_stock_attributes from products_stock where products_id = '" . $info['products_id'] . "'";
                                $sqlw = $db->open_query($cez);    
                                //
                                while ($infe = $sqlw->fetch_assoc()) {                
                                    //
                                    $tmpc = explode(',', (string)$infe['products_stock_attributes']);
                                    //
                                    foreach ( $tmpc as $klucz => $wart ) {
                                        //
                                        $podzial = explode('-', (string)$wart);
                                        //
                                        $tab[] = $podzial[1];
                                        //
                                        unset($podzial);
                                        //
                                    }
                                    //
                                    unset($tabs, $tmpc);
                                    //
                                }
                                //
                                $db->close_query($sqlw);
                                unset($infe, $cez);
                            
                            }        

                            if ( isset($tab) && count($tab) == 0 && CECHY_KOMBINACJE_WSZYSTKIE == 'nie' ) {
                                  //
                                  echo '<tr><td>Brak wybranych kombinacji cech ...</td></tr>';
                                  //
                            } else {                            
                          
                                echo '<tr>';
                                // ustala nazwy cech do naglowka
                                echo '<td class="Nagl">';
                                
                                if ( isset( $nazwyCech[$pozCech] ) ) {
                                     echo $nazwyCech[$pozCech];
                                }
                                
                                echo '</td>'; 
                                
                                //
                                if (CECHY_MAGAZYN == 'tak') {
                                    echo '<td class="Nagl">Ilość</td>';
                                }
                                echo '<td class="Nagl">Dostępność</td>';
                                echo '<td class="Nagl">Czas wysyłki</td>';
                                echo '<td class="Nagl">Nr katalogowy</td>';
                                echo '<td class="Nagl">Kod EAN</td>'; 

                                echo '</tr>';
                          
                                if ( CECHY_KOMBINACJE_WSZYSTKIE == 'nie' ) {
                                
                                      $CiagDoWartosciTmp = array();
                                      
                                      foreach ( $tab as $wart ) {
                                           //
                                           $CiagDoWartosciTmp[] = $wart;
                                           //
                                      }
                                      
                                }
                                
                                // --------------

                                if ( CECHY_KOMBINACJE_WSZYSTKIE == 'tak' ) {
                                  
                                     $WarCechPojedyncze = explode(',', (string)$CiagDoWartosci);
                                     
                                } else {
                                  
                                     $WarCechPojedyncze = $CiagDoWartosciTmp;
                                     
                                }
                                $id_magazyn = 1;
                                
                                for ($a = 0, $ca = count($WarCechPojedyncze); $a < $ca; $a++) {
                                    // ustala wartosci cech
                                    echo '<tr><td>';
                                    
                                    if ( isset( $wartosciCech[$WarCechPojedyncze[$a]] ) ) {
                                         echo $wartosciCech[$WarCechPojedyncze[$a]];
                                    }
                                    
                                    echo '</td>'; 
                                    //
                                    // szuka w bazie ilosci magayznu
                                    $cec = "select distinct * from products_stock where products_id = '".$info['products_id'] ."' and products_stock_attributes = '".$pozCech.'-'.$WarCechPojedyncze[$a]."'";
                                    $sqlw = $db->open_query($cec);    
                                    //
                                    if ((int)$db->ile_rekordow($sqlw) > 0) {
                                        //                                        
                                        $ilosc_cechy = $sqlw->fetch_assoc(); 
                                        //
                                    } else {
                                        //
                                        $ilosc_cechy = array();
                                        $ilosc_cechy['products_stock_quantity'] = 0;
                                        $ilosc_cechy['products_stock_model'] = '';
                                        $ilosc_cechy['products_stock_ean'] = '';
                                        $ilosc_cechy['products_stock_size'] = 0;
                                        $ilosc_cechy['products_stock_availability_id'] = '';
                                        $ilosc_cechy['products_stock_shipping_time_id'] = '';
                                        $ilosc_cechy['products_stock_image'] = '';
                                        //
                                        for ($x = 1; $x <= ILOSC_CEN; $x++) {
                                             //
                                             $ilosc_cechy['products_stock_price' . (($x == 1) ? '' : '_' . $x )] = '';
                                             $ilosc_cechy['products_stock_tax' . (($x == 1) ? '' : '_' . $x )] = '';
                                             $ilosc_cechy['products_stock_price_tax' . (($x == 1) ? '' : '_' . $x )] = '';
                                             $ilosc_cechy['products_stock_retail_price' . (($x == 1) ? '' : '_' . $x )] = '';
                                             $ilosc_cechy['products_stock_old_price' . (($x == 1) ? '' : '_' . $x )] = '';
                                             //
                                        }
                                        //
                                    }
                                    $db->close_query($sqlw);                    
                                    //

                                    if (CECHY_MAGAZYN == 'tak') {                        
                                        echo '<td>
                                                  <input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$pozCech.'-'.$WarCechPojedyncze[$a].'" />
                                                  <input type="text" class="kropka" name="ilosc_'.$id_magazyn.'" size="8" value="'.(($ilosc_cechy['products_stock_quantity'] == 0) ? '' : $ilosc_cechy['products_stock_quantity']).'" />
                                              </td>'; 
                                    }
                                    
                                    echo '<td>';
                                    
                                          if (CECHY_MAGAZYN == 'nie') {    
                                              echo '<input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$pozCech.'-'.$WarCechPojedyncze[$a].'" />';
                                              echo '<input type="hidden" name="ilosc_'.$id_magazyn.'" value="0" />';
                                          }                                     
                                    
                                          //                        
                                          echo Funkcje::RozwijaneMenu('dostepnosc_'.$id_magazyn, $tablicaDostepnosci, $ilosc_cechy['products_stock_availability_id']);         
                                          //
                                          
                                          $ceny_cech = array();
                                          
                                          $ceny_cech[] = array('products_stock_price', $ilosc_cechy['products_stock_price']);
                                          $ceny_cech[] = array('products_stock_tax', $ilosc_cechy['products_stock_tax']);
                                          $ceny_cech[] = array('products_stock_price_tax', $ilosc_cechy['products_stock_price_tax']);
                                          $ceny_cech[] = array('products_stock_image', $ilosc_cechy['products_stock_image']);
                                          
                                          for ($x = 2; $x <= ILOSC_CEN; $x++) {
                                              //
                                              $ceny_cech[] = array('products_stock_price_'.$x, $ilosc_cechy['products_stock_price_'.$x]);
                                              $ceny_cech[] = array('products_stock_tax_'.$x, $ilosc_cechy['products_stock_tax_'.$x]);
                                              $ceny_cech[] = array('products_stock_price_tax_'.$x, $ilosc_cechy['products_stock_price_tax_'.$x]);
                                              //
                                          }                                           
                                          
                                          echo '<input type="hidden" name="ceny_cechy_' . $id_magazyn . '" value="' . base64_encode(serialize($ceny_cech)) . '" />';
                                          
                                          unset($ceny_cech);                                      
                                          
                                    echo '</td>';
                                    
                                    echo '<td>';
                                          //                          
                                          echo Funkcje::RozwijaneMenu('czas_wysylki_'.$id_magazyn, $tablicaCzasWysylki, $ilosc_cechy['products_stock_shipping_time_id']);         
                                          //
                                    echo '</td>';
                                    
                                    echo '<td>
                                              <input type="text" name="nr_kat_'.$id_magazyn.'" size="30" value="'.$ilosc_cechy['products_stock_model'].'" />
                                          </td>';   

                                    echo '<td>
                                              <input type="text" name="ean_'.$id_magazyn.'" size="20" value="'.$ilosc_cechy['products_stock_ean'].'" />
                                          </td>';   

                                    echo '</tr>';
                                    
                                    $id_magazyn++;          
                                    unset($cec, $ilosc_cechy);                     
                                    ///
                                }
                                
                            }
                          
                        }

                        echo '</table>';   

                        echo '<input type="hidden" name="ilosc_magazynu" value="'.((!isset($id_magazyn)) ? 1 : $id_magazyn).'" />';
                        
                        echo '</div> <div class="cl"></div>';
                        
                        unset($tablicaDostepnosci, $nazwyCech, $wartosciCech);
                        
                        ?>
                        
                    <?php } ?>         

                    <div class="CechyNaglowek">Dane dla całego produktu</div>
                    
                    <?php if ($zCechami == false || CECHY_MAGAZYN == 'nie') { ?>
                        
                        <?php if ($zCechami == false) { ?>
                        <input type="hidden" name="bez_cech" value="0" />
                        <?php } ?>
                        
                        <p>
                          <label for="ilosc">Ilość w magazynie:</label>
                          <input type="text" name="ilosc" id="ilosc" size="5" class="kropka" value="<?php echo ((Funkcje::czyNiePuste($info['products_quantity'])) ? $info['products_quantity'] : ''); ?>" />
                        </p>
                        
                    <?php } ?>
                    
                    <p>
                      <label for="dostepnosc">Stan dostępności:</label>                                       
                      <?php echo Funkcje::RozwijaneMenu('dostepnosc', Produkty::TablicaDostepnosci('-- brak --'), $info['products_availability_id'], 'id="dostepnosc"'); ?>
                    </p>     

                    <p>
                      <label for="wysylka">Wysyłka:</label>                                        
                      <?php echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), $info['products_shipping_time_id'], 'id="wysylka"'); ?>
                    </p>
                    
                    <p>
                      <label for="wysylka_zero">Wysyłka jeżeli produkt ma stan magazynowy 0:</label>                                        
                      <?php echo Funkcje::RozwijaneMenu('wysylka_zero', Produkty::TablicaCzasWysylki('-- brak --'), $info['products_shipping_time_zero_quantity_id'], 'id="wysylka_zero"'); ?>
                    </p>                    
                    
                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <?php if ( isset($_GET['produkt']) ) { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty');">Powrót</button>           
                  <?php } else { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty_magazyn','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty_magazyn');">Powrót</button>           
                  <?php } ?>
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