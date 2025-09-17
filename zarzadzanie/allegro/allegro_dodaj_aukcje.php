<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    //
    if ( Funkcje::SprawdzAktywneAllegro() ) {
        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
    }
    //        

    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // ---------------------------------- zdjecie
        //

        // czy jest glowny katalog zdjec allegro
        if ( is_dir( '../' . KATALOG_ZDJEC . '/produkty_allegro' ) == false ) {
             //
             $old_mask = umask(0);
             mkdir('../' . KATALOG_ZDJEC . '/produkty_allegro', 0777, true);
             umask($old_mask);
             //
        }      
        // czy jest katalog dla zdjec konkretnej aukcji
        if ( is_dir( '../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) ) == false ) {
             //
             $old_mask = umask(0);
             mkdir('../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']), 0777, true);
             umask($old_mask);
            //
        }          
        //
        if ( isset($_POST['zdjecie_allegro']) && $_POST['zdjecie_allegro'] != '' ) {
             //
             $CzyJestPlik = file_get_contents( $filtr->process($_POST['zdjecie_allegro']), false, stream_context_create($arrContextOptions) );
             //
             if ( !empty($CzyJestPlik) ) {
                  //
                  $NazwaPliku = '../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) . '/aukcja_' . floatval($_POST['id_aukcji']) . '.jpg';
                  //
                  // jezeli jest plik to go usunie
                  if (file_exists($NazwaPliku)) { 
                      //
                      unlink($NazwaPliku);
                      //
                      if (is_dir('../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) . '/mini/')) {
                          //
                          $KatalogZdjecAllegro = glob('../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) . '/mini/*.*');
                          if ( !empty($KatalogZdjecAllegro) ) {
                              foreach ($KatalogZdjecAllegro as $plik) {
                                  if (is_file($plik)) {
                                      unlink($plik);
                                  }
                              } 
                          }
                          //
                          rmdir('../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) . '/mini/');
                      }
                      //
                  }                  
                  //
                  // zapisanie pobranego obrazka na serwerze
                  $fp = fopen($NazwaPliku, 'x');
                  fwrite($fp, $CzyJestPlik);
                  fclose($fp);  
                  //
                  unset($NazwaPliku);
                  //
             }
             unset($CzyJestPlik);
             //
        }
        //
        $ZdjeciaDodane = array();
        //

        if ( isset($_POST['zdjecia_produktu']) && count($_POST['zdjecia_produktu']) > 0 ) {
             //     
             foreach ( $_POST['zdjecia_produktu'] as $ZdjeciaAllegro ) {
                 //
                 $TmpZdjecie = unserialize(base64_decode((string)$ZdjeciaAllegro));
                 //
                 $CzyJestPlik = file_get_contents( $filtr->process( $TmpZdjecie[0] ), false, stream_context_create($arrContextOptions) );
                 //
                 if ( !empty($CzyJestPlik) ) {
                      //
                      $NazwaPliku = '../' . KATALOG_ZDJEC . '/' . $TmpZdjecie[1];
                      $ZdjeciaDodane[] = $TmpZdjecie[1];
                      //
                      $_POST['opis_aukcji'] = str_replace($TmpZdjecie[0], '/' . KATALOG_ZDJEC . '/' . $TmpZdjecie[1], (string)$_POST['opis_aukcji']);
                      //
                      // podmiana zdjecia w opisie allegro (zakladka allegro)
                      $opis_allegro = unserialize(base64_decode((string)$_POST['opis_aukcji_allegro']));
                      //
                      if ( is_array($opis_allegro) ) {
                           //
                           for ( $t = 1; $t <= count($opis_allegro); $t++ ) {
                                //
                                if ( $opis_allegro[$t][0] == 'zdjecie_listing' || $opis_allegro[$t][0] == 'zdjecie' ) {
                                     //
                                     if ( $opis_allegro[$t][1][0] == $TmpZdjecie[0] ) {
                                          $opis_allegro[$t][1][0] = $TmpZdjecie[1];
                                     }
                                }
                                if ( $opis_allegro[$t][0] == 'listing_zdjecie' ) {
                                     //
                                     if ( $opis_allegro[$t][1][1] == $TmpZdjecie[0] ) {
                                          $opis_allegro[$t][1][1] = $TmpZdjecie[1];
                                     }
                                }
                                if ( $opis_allegro[$t][0] == 'zdjecie_zdjecie' ) {
                                     //
                                     if ( $opis_allegro[$t][1][0] == $TmpZdjecie[0] ) {
                                          $opis_allegro[$t][1][0] = $TmpZdjecie[1];
                                     }
                                     if ( $opis_allegro[$t][1][1] == $TmpZdjecie[0] ) {
                                          $opis_allegro[$t][1][1] = $TmpZdjecie[1];
                                     }                                     
                                }                                  
                                //
                           }
                           //
                      }
                      //
                      $_POST['opis_aukcji_allegro'] = base64_encode(serialize($opis_allegro));
                      unset($opis_allegro);
                      //
                      // jezeli jest plik to go usunie
                      if (file_exists($NazwaPliku)) {
                          //
                          unlink($NazwaPliku);
                          //
                      }                  
                      //
                      // zapisanie pobranego obrazka na serwerze
                      $fp = fopen($NazwaPliku, 'x');
                      fwrite($fp, $CzyJestPlik);
                      fclose($fp);  
                      //
                      unset($NazwaPliku);
                      //
                      //
                 }
                 unset($CzyJestPlik);
                 //
                 unset($TmpZdjecie);
                 //
             }         
             //
        }

        //

        // jezeli jest dodane reczne produktu
        if ( $_POST['produkt_typ'] == 0 ) {
             //
             // ---------------------------------- products
             //
             $CenaBrutto = $_POST['cena'];
             //
             $StawkaVatTab = explode('|', (string)$filtr->process($_POST['vat']));
             $CenaNetto = round(($CenaBrutto / ((100 + $StawkaVatTab[0]) / 100)), CENY_MIEJSCA_PO_PRZECINKU );
             $Vat = $CenaBrutto - $CenaNetto;
             //
             $pola = array(
                     array('products_status','1'),
                     array('products_ean',$filtr->process($_POST['ean'])),
                     array('products_model',$filtr->process($_POST['nr_katalogowy'])),
                     array('products_man_code',$filtr->process($_POST['kod_producenta'])),
                     array('products_date_added','now()'),
                     array('products_condition_products_id','1'),
                     array('products_type',$filtr->process($_POST['rodzaj_produktu'])),
                     array('products_quantity',(float)$_POST['ilosc']),
                     array('products_jm_id',(int)$_POST['jednostka_miary']),
                     array('products_price',(float)$CenaNetto),
                     array('products_tax',(float)$Vat),
                     array('products_price_tax',(float)$CenaBrutto),
                     array('products_currencies_id',(int)$_SESSION['domyslna_waluta']['id']),
                     array('products_tax_class_id',(int)$StawkaVatTab[1]),
                     array('products_id_private',$filtr->process($_POST['sygnatura']))
                     );
             //                       
             unset($CenaBrutto, $StawkaVatTab, $CenaNetto, $Vat);
             //
             if ( isset($ZdjeciaDodane[0]) ) {
                  //
                  $pola[] = array('products_image',$ZdjeciaDodane[0]);
                  //
             }
             //
             $id_dodanej_pozycji = $db->insert_query('products' , $pola, '', false, true);
             unset($pola);    
             //
             // ---------------------------------- description
             //
             $ile_jezykow = Funkcje::TablicaJezykow();
             //
             for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                 //       
                 $pola = array(
                         array('products_id',$id_dodanej_pozycji),
                         array('language_id',(int)$ile_jezykow[$w]['id']),
                         array('products_name',$filtr->process($_POST['nazwa_produktu'])),
                         array('products_description',$filtr->process($_POST['opis_aukcji'])),       
                         array('products_meta_title_tag',$filtr->process($_POST['nazwa_produktu'])),
                         array('products_meta_desc_tag',$filtr->process($_POST['nazwa_produktu'])),
                         array('products_meta_keywords_tag',$filtr->process($_POST['nazwa_produktu'])));  
                         
                 $sql = $db->insert_query('products_description' , $pola);
                 unset($pola);
                 //            
             }  
             //
             unset($ile_jezykow);
             //
             // ---------------------------------- products to categories
             //
             // jezeli jest kategoria sklepu
             if ( $_POST['rodzaj_kategorii'] == 'sklep' ) {
                  //
                  if (isset($_POST['id_kat'])) {
                      //
                      $tablica_kat = $_POST['id_kat'];
                      //
                      for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                           //
                           $pola = array(
                                   array('products_id',(int)$id_dodanej_pozycji),
                                   array('categories_id',(int)$tablica_kat[$q])); 
                           //                        
                           $sql = $db->insert_query('products_to_categories' , $pola);        
                           //
                      }
                      //
                      unset($tablica_kat, $pola);   
                      //
                  }
                  //
             }

             if ( $_POST['rodzaj_kategorii'] == 'allegro' && isset($_POST['allegro_kategoria_sciezka']) ) {
                  //
                  $ile_jezykow = Funkcje::TablicaJezykow();

                  $tablica_kat = explode(' > ', (string)$_POST['allegro_kategoria_sciezka']);

                  $parent = 0;

                  for ($w = 0; $w < count($tablica_kat); $w++) {
                    
                      $zapytanie_kategorie = "select c.categories_id, cd.categories_name from categories c, categories_description cd where cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = cd.categories_id and categories_name = '" . $tablica_kat[$w] . "' and parent_id = '" . (int)$parent . "'";
                      $sql_kategorie = $db->open_query($zapytanie_kategorie);
                      
                      if ((int)$db->ile_rekordow($sql_kategorie) == 0) {
                          //
                          $pola = array(
                                  array('parent_id', $parent),
                                  array('sort_order', '1'),
                                  array('categories_status', '1'));

                          $db->insert_query('categories' , $pola);
                          $id_dodanej_kategorii = $db->last_id_query();
                          unset($pola);
                          //
                          $pola = array(
                                  array('categories_id', $id_dodanej_kategorii),
                                  array('language_id', (int)$_SESSION['domyslny_jezyk']['id']),
                                  array('categories_name', $tablica_kat[$w]));  

                          $db->insert_query('categories_description' , $pola);
                          unset($pola);    
                          //
                          $parent = $id_dodanej_kategorii;
                          
                          // ---------------------------------------------------------------
                          // dodawanie do innych jezykow jak sa inne jezyki
                          for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                              //
                              $pola = array(
                                      array('categories_id', $id_dodanej_kategorii),
                                      array('language_id', (int)$ile_jezykow[$j]['id']),
                                      array('categories_name', $tablica_kat[$w]));    

                              if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                  $db->insert_query('categories_description' , $pola);
                              }
                              unset($pola);              
                              //
                          }
                          
                      } else {
                      
                          // jezeli znaleziono taka kategorie
                          $infg = $sql_kategorie->fetch_assoc();
                          $parent = $infg['categories_id'];
                          $db->close_query($sql_kategorie);
                          unset($infg);             
                      
                      }
                      
                      unset($zapytanie_kategorie);
                      
                  }        
                  
                  if ( (int)$parent > 0 ) {
                        //
                        $pola = array(
                                array('products_id',(int)$id_dodanej_pozycji),
                                array('categories_id',(int)$parent)); 
                        //                        
                        $sql = $db->insert_query('products_to_categories' , $pola);        
                        //  
                  }
                  
                  unset($tablica_kat, $parent);
                  //
             }
             
             //
             // ---------------------------------- products_allegro_info
             $pola = array(
                     array('products_id',(int)$id_dodanej_pozycji),
                     array('products_description_allegro',base64_decode((string)$_POST['opis_aukcji_allegro'])),
                     array('products_name_allegro',$filtr->process($_POST['nazwa_produktu'])),
                     array('products_cat_id_allegro',(int)$_POST['kategoria']),
                     array('products_price_allegro',(float)$_POST['cena']));      
                     
             if ( file_exists('../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) . '/aukcja_' . floatval($_POST['id_aukcji']) . '.jpg') ) {
                  //
                  $pola[] = array('products_image_allegro','produkty_allegro/' . floatval($_POST['id_aukcji']) . '/aukcja_' . floatval($_POST['id_aukcji']) . '.jpg');
                  //
             } else if ( isset($ZdjeciaDodane[0]) ) {
                  //
                  $pola[] = array('products_image_allegro',$ZdjeciaDodane[0]);
                  //               
             }
                     
             $sql = $db->insert_query('products_allegro_info' , $pola);
             unset($pola);        

             // ---------------------------------- additional_images
             if ( isset($ZdjeciaDodane) && count($ZdjeciaDodane) > 1 ) {
                  //
                  for ($x = 1; $x < count($ZdjeciaDodane); $x++ ) {
                     //
                     $pola = array(
                             array('products_id',(int)$id_dodanej_pozycji),
                             array('popup_images',$ZdjeciaDodane[$x]));                      
                             //
                     $sql = $db->insert_query('additional_images' , $pola);
                     unset($pola);
                     //
                  }
                  //
             }
             
             // ---------------------------------- products_extra_fields
             if ( isset($_POST['dodatkowe_pole']) && count($_POST['dodatkowe_pole']) > 0 ) {
                  //
                  foreach ( $_POST['dodatkowe_pole'] as $PoleDodatkowe ) {
                      //
                      $TablicaPola = unserialize(base64_decode((string)$PoleDodatkowe));
                      //
                      // sprawdza czy dodatkowe pole jest juz w bazie
                      $zapytanieDodPole = "select products_extra_fields_id, products_extra_fields_name from products_extra_fields where products_extra_fields_name = '" . $filtr->process($TablicaPola['nazwa']) . "' and languages_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";
                      $sqlc = $db->open_query($zapytanieDodPole);
                      //    
                      if ((int)$db->ile_rekordow($sqlc) > 0) {
                          //
                          $info = $sqlc->fetch_assoc();
                          $IdPolaDodatkowego = $info['products_extra_fields_id'];
                          //   
                          $db->close_query($sqlc);
                          unset($info);
                          //
                       } else {
                          // jezeli nie ma dodatkowego pola to doda je do bazy
                          $pole = array(
                                  array('products_extra_fields_name',$filtr->process($TablicaPola['nazwa'])),
                                  array('products_extra_fields_status','1'),
                                  array('languages_id',(int)$_SESSION['domyslny_jezyk']['id']));   
                          $db->insert_query('products_extra_fields' , $pole); 
                          //
                          $IdPolaDodatkowego = $db->last_id_query();
                          unset($pole);
                          //
                      }                      
                      //
                      $pole = array(
                              array('products_id', (int)$id_dodanej_pozycji),
                              array('products_extra_fields_id', (int)$IdPolaDodatkowego),
                              array('products_extra_fields_value', $filtr->process($TablicaPola['wartosci'])));  

                      $db->insert_query('products_to_products_extra_fields' , $pole);  
                      //
                      unset($pole, $IdPolaDodatkowego);                           
                      //
                  }
                  //
             }
             
             //
             $kategoriaSklepu = 0;
             
             // dane produktu
             $zapytanie = "select ptc.categories_id, p.products_quantity from products_to_categories ptc, products p where p.products_id = '" . (int)$id_dodanej_pozycji . "' and p.products_id = ptc.products_id order by categories_default";
             $sql = $db->open_query($zapytanie);
             //
             if ((int)$db->ile_rekordow($sql) > 0) {
                 //
                 $info = $sql->fetch_assoc();        
                 //
                 $kategoriaSklepu = $info['categories_id'];
                 //
                 unset($info);
                 //
             }
             //
             $db->close_query($sql); 
             unset($zapytanie);
             
             $cechyProduktu = array();
             //
             $_POST['id_prod'] = $id_dodanej_pozycji;
             //
             unset($id_dodanej_pozycji);
             //
        } else {
             //
             $kategoriaSklepu = 0;
             
             // dane produktu
             $zapytanie = "select ptc.categories_id, p.products_quantity from products_to_categories ptc, products p where p.products_id = '" . (int)$_POST['id_prod'] . "' and p.products_id = ptc.products_id order by categories_default";
             $sql = $db->open_query($zapytanie);
             //
             if ((int)$db->ile_rekordow($sql) > 0) {
                 //
                 $info = $sql->fetch_assoc();        
                 //
                 $kategoriaSklepu = $info['categories_id'];
                 //
                 unset($info);
                 //
             }
             //
             $db->close_query($sql); 
             unset($zapytanie);
             //
             // cechy produktu
             $cechyProduktu = array();
             //
             if ( isset($_POST['cecha']) ) {
                 //
                 foreach ( $_POST['cecha'] as $id => $wartosc ) {
                     //
                     $cechyProduktu[$id] = $id . '-' . $wartosc;
                     //              
                 }
                 //
                 ksort($cechyProduktu);
                 //
             }
             //
        }

        $DodawanaAukcja = $AllegroRest->AukcjaSzczegoly($_POST['id_aukcji']);

        if ( is_object($DodawanaAukcja) && count((array)$DodawanaAukcja) > 0 ) {
             //
             if ( isset($DodawanaAukcja->publication->startedAt) ) {
                 $DataRozpoczecia = $DodawanaAukcja->publication->startedAt;
             }
             if ( isset($DodawanaAukcja->publication->startingAt) ) {
                 $DataRozpoczecia = $DodawanaAukcja->publication->startingAt;
             }
             $DataStart = date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataRozpoczecia));

             if ( isset($DodawanaAukcja->publication->endingAt) && $DodawanaAukcja->publication->endingAt != '' ) {
                $DataKoniec = date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DodawanaAukcja->publication->endingAt));
             } else {
                $DataKoniec = '1970-01-01 01:00:00';
             }
             $Status = $DodawanaAukcja->publication->status;
             $IloscObserwujacych = $DodawanaAukcja->stats->watchersCount;
             $IloscWyswietlen = $DodawanaAukcja->stats->visitsCount;
             $IloscDostepnych = $DodawanaAukcja->stock->available;
             $IloscSprzedanych = $DodawanaAukcja->stock->sold;
             //
        } else {
             //
             $DataStart = '';
             $DataKoniec = '';
             $Status = 'ACTIVE';
             $IloscObserwujacych = 0;
             $IloscWyswietlen = 0;
             $IloscDostepnych = $_POST['ilosc'];
             $IloscSprzedanych = 0;
             //
        }
        

        $pola = array(
                array('auction_id',floatval($_POST['id_aukcji'])),
                array('products_id',(int)$_POST['id_prod']),
                array('products_name',$filtr->process($_POST['nazwa_produktu'])),
                array('allegro_category',(int)$_POST['kategoria']),
                array('allegro_category_name',str_replace(' > ', ';',$filtr->process((string)$_POST['allegro_kategoria_sciezka']))),
                array('allegro_category_shop',(int)$kategoriaSklepu),
                array('allegro_options',( isset($_POST['opcja']) ? implode(',', (array)$_POST['opcja']) : '')),
                array('products_quantity',(int)$IloscDostepnych), 
                array('products_stock_attributes', implode('x', (array)$cechyProduktu)),
                array('products_date_start',$DataStart),
                array('products_date_end',$DataKoniec),
                array('allegro_server',$AllegroRest->polaczenie['CONF_COUNTRY']),
                array('allegro_sandbox',( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'tak' ? '1' : '0' )),
                array('auction_source','1'),
                array('auction_type','BUY_NOW'),
                array('auction_date_start',$DataStart),
                array('auction_date_end',$DataKoniec),                
                array('auction_price',(float)$_POST['cena']),
                array('auction_seller',(int)$_SESSION['domyslny_uzytkownik_allegro']),
                array('auction_quantity',(int)$IloscDostepnych),
                array('auction_status',$Status),
                array('auction_buy_now','1'),
                array('products_buy_now_price',(float)$_POST['cena']),
                array('auction_hits',(int)$IloscWyswietlen),
                array('products_sold',(int)$IloscSprzedanych),
                array('auction_watching',(int)$IloscObserwujacych),
                array('synchronization','0'),
                array('external_id',$_POST['sygnatura']),
         );

        if ( file_exists('../' . KATALOG_ZDJEC . '/produkty_allegro/' . floatval($_POST['id_aukcji']) . '/aukcja_' . floatval($_POST['id_aukcji']) . '.jpg') ) {
             //
             $pola[] = array('products_image','produkty_allegro/' . floatval($_POST['id_aukcji']) . '/aukcja_' . floatval($_POST['id_aukcji']) . '.jpg');
             //
        }        
        
        $db->insert_query('allegro_auctions' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();    

        unset($pola);
        unset($DataStart, $DataKoniec, $Status, $IloscObserwujacych, $IloscWyswietlen, $IloscDostepnych, $IloscSprzedanych );
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('allegro_aukcje.php?id_poz=' . $id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('allegro_aukcje.php');
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>

    <div class="poleForm cmxform" style="margin-bottom:10px">
        <div class="naglowek">Ustawienia konfiguracji połączenia z Allegro</div>

        <div class="pozycja_edytowana">
              
            <?php require_once('allegro_naglowek.php'); ?>
                
        </div>
    </div>

    <div id="cont">
          
        <script>
        $(document).ready(function() {
          
          $("#eForm").validate({
            rules: {
              id_aukcji: { required: true, range: [1, 90000000000], number: true },
              id_prod: {
                required: function(element) {
                  if ($("#id_prod").val() == '' && $('#produkt_typ').val() == '1') {
                      return true;
                    } else {
                      return false;
                  }
                }
              },
              id_kategorii: {
                required: function(element) {
                  if ($("#id_kategorii").val() == '' && $('#produkt_typ').val() == '0') {
                      return true;
                    } else {
                      return false;
                  }
                }
              }               
            },
            messages: {
              id_aukcji: {
                required: "Pole jest wymagane."
              },
              id_prod: {
                required: "Nie został wybrany produkt."
              },
              id_kategorii: {
                required: "Nie została wybrana kategoria."
              }               
            }
          });
                      
        });                        

        function pokaz_allegro_cechy(id) {
          
          $('#WyborCechy').html('<img src="obrazki/_loader_small.gif" alt="" />');
          
          $.post("ajax/allegro_cechy_produktu.php?tok=" + $('#tok').val(),
              { id_produktu: id },
              function(data) { 
                  $('#WyborCechy').hide();
                  $('#WyborCechy').html(data);
                  $('#WyborCechy').slideDown();
              }           
          );  

        }
        
        function pobierz_dane_aukcji() {
          
          $('#DaneAukcji').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
          
          $('#DaneProduktu').hide();
          $('#DodajProdukt').hide();
          
          $.post("ajax/allegro_dane_aukcji.php?tok=" + $('#tok').val(),
              { id_aukcji: $('#id_aukcji').val() },
              function(data) { 
                  $('#DaneAukcji').hide();
                  $('#DaneAukcji').html(data);
                  $('#DaneAukcji').slideDown();
                  
                  $('#pobranie_aukcji').hide();
                  
                  if ( $('#DaneAukcji').html().indexOf('class="ostrzezenie"') > 0 ) {
                       $('#DaneProduktu').hide();
                       $('#DodajProdukt').hide();
                    } else {
                       $('#DaneProduktu').slideDown();
                       $('#DodajProdukt').slideDown();
                  }
              }           
          );             
          
        }
        
        function produkt_allegro_dodaj() {
         
          $('#DodajProduktLoader').html('<img src="obrazki/_loader.gif">');
          
          $.post("ajax/allegro_dane_aukcji_dodaj_produkt.php?tok=" + $('#tok').val(),
              { id_aukcji: $('#id_aukcji').val(), kategoria: $('#kategoria_allegro').val() },
              function(data) { 
              
                  $('#DaneProduktu').remove();
                  $('#DodajProdukt').remove();
              
                  $('#PolaUkryte').hide();
                  $('#PolaUkryte').html(data);
                  $('#PolaUkryte').show();
                  
                  $('#DodajProduktLoader').hide();
                  
                  $('#produkt_typ').val(0);

              }           
          );           

        }
        </script>        

        <form action="allegro/allegro_dodaj_aukcje.php" method="post" id="eForm" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Dodawanie danych</div>
          
          <div class="pozycja_edytowana">
          
              <div class="info_content">
          
              <input type="hidden" name="akcja" value="zapisz" />

                  <p>
                    <label class="required" for="nazwa">ID aukcji:</label>
                    <input type="text" class="calkowita" name="id_aukcji" id="id_aukcji" class="required" value="" size="53" />
                    <button type="button" class="przyciskNon" id="pobranie_aukcji" onclick="pobierz_dane_aukcji()">Pobierz dane aukcji</button>   
                  </p>   
                  
                  <div id="DaneAukcji">
                  
                  </div>
                  
                  <div id="DaneProduktu" style="display:none">
                  
                      <div class="NaglowekNowyProdukt">Przypisanie produktu z aukcji Allegro do produktu w sklepie</div>                  

                      <p>
                        <label for="szukany">Produkt do jakiego będzie przypisana aukcja:</label>
                      </p>
                      
                      <div class="WybieranieProduktow">

                          <div class="GlownyListing">

                              <div class="GlownyListingKategorieEdycja">
                          
                                  <div id="fraza">
                                      <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /></div> <span onclick="fraza_produkty()"></span>
                                  </div>                        
                              
                                  <div id="drzewo" style="margin:0px">
                                      <?php
                                      //
                                      echo '<table class="pkc">';
                                      //
                                      $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                      for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                          $podkategorie = false;
                                          if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                          //
                                          echo '<tr>
                                                  <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="id_kat_' . $tablica_kat[$w]['id'] . '" /><label class="OpisFor" for="id_kat_' . $tablica_kat[$w]['id'] . '">'.$tablica_kat[$w]['text'].'</label></td>
                                                  <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                                </tr>
                                                '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                      }
                                      echo '</table>';
                                      unset($tablica_kat,$podkategorie);   
                                      ?>            
                                  </div>
                                  
                              </div>
                              
                              <div style="GlownyListingProduktyEdycja">  
                                  
                                  <input type="hidden" id="rodzaj_modulu" value="allegro_produkty" />
                                  <div id="wynik_produktow_allegro_produkty" class="WynikProduktowAllegroProdukty"></div> 

                              </div>
                              
                          </div>

                      </div>
   
                      <p class="errorRwd">
                        <input type="hidden" name="id_prod" id="id_prod" value="" />
                      </p>  

                      <div id="WyborCechy"></div>
                      
                  </div>
                  
                  <input type="hidden" value="1" id="produkt_typ" name="produkt_typ" />
                      
                  <span id="DodajProdukt" class="ProduktInnyBaza" onclick="produkt_allegro_dodaj()">dodaj jako nowy produkt do bazy sklepu</span>
                  
                  <div id="DodajProduktLoader" style="margin:10px"></div>
 
              </div>
              
          </div>

          <div class="przyciski_dolne">
            <input type="submit" class="przyciskNon" value="Zapisz dane" />
            <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>   
          </div>            

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}