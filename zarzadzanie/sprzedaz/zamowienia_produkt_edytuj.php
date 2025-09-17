<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $JestZamowienie = false;

    if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {
      $zamowienie = new Zamowienie((int)$_GET['id_poz']);
      $JestZamowienie = true;
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $kombinacja_cech = array();

        if ( isset($_POST['cecha']) && count($_POST['cecha']) > 0 ) {

          foreach ( $_POST['cecha'] as $key ) {
            $tablica_wartosc_cechy = explode( ';', (string)$key );
            $prefix = $_POST['cecha_prefix'][$tablica_wartosc_cechy['1']];
            $cena_cechy_netto = $_POST['cecha_cena_netto'][$tablica_wartosc_cechy['1']];
            $cena_cechy_brutto = $_POST['cecha_cena_brutto'][$tablica_wartosc_cechy['1']];
            $kombinacja_cech[ $tablica_wartosc_cechy['1'] ] = $tablica_wartosc_cechy['1'].'-'.$tablica_wartosc_cechy['0'];

            $zapytanie_wartosc_cechy = "SELECT * FROM products_options_values
                                            WHERE products_options_values_id = '" . (int)$tablica_wartosc_cechy['0']. "' 
                                            AND language_id =  '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                        
            $sql_wartosc_cechy = $db->open_query($zapytanie_wartosc_cechy);

            if ((int)$db->ile_rekordow($sql_wartosc_cechy) > 0) {
              $info_wartosc_cechy = $sql_wartosc_cechy->fetch_assoc();
              $nazwa_wartosci_cechy = $info_wartosc_cechy['products_options_values_name'];
            }
            
            if ( $prefix != '*' ) {

                $pola = array(
                        array('products_options_values',$nazwa_wartosci_cechy),
                        array('products_options_values_id',(int)$tablica_wartosc_cechy['0']),
                        array('options_values_price',(float)$cena_cechy_netto),
                        array('options_values_tax',((float)$cena_cechy_brutto - (float)$cena_cechy_netto)),
                        array('options_values_price_tax',(float)$cena_cechy_brutto),
                        array('price_prefix',$prefix)
                );
                
            } else {
              
                $pola = array(
                        array('products_options_values',$nazwa_wartosci_cechy),
                        array('products_options_values_id',(int)$tablica_wartosc_cechy['0']),
                        array('options_values_price',0),
                        array('options_values_tax',0),
                        array('options_values_price_tax',((float)$cena_cechy_brutto) / (float)$_POST["brut_1_podstawa"]),
                        array('price_prefix',$prefix)
                );              
              
            }

            $db->update_query('orders_products_attributes' , $pola, " orders_id = '".(int)$_POST["id"]."' AND orders_products_id = '".(int)$_POST["id_produktu"]."' AND products_options_id = '".(int)$tablica_wartosc_cechy['1']."'");	
            unset($pola);

          }
        }
        
        natsort($kombinacja_cech);
        $kombinacja_cech = implode(',', (array)$kombinacja_cech);
        
        // szuka czy dana kombinacja cech nie ma unikalnego nr katalogowego i zdjecia
        $nr_katalogowy_cechy = $filtr->process($_POST["model"]);
        $zdjecie_cechy = '';
        $kod_ean = '';

        // szuka zdjecia wartosci cechy
        $tab_cech = explode(',',(string)$kombinacja_cech);
              
        foreach ( $tab_cech as $tmp_cecha ) {
            //
            $podziel_tmp = explode('-', (string)$tmp_cecha);
            //
            if ( count($podziel_tmp) == 2 ) {
                 //
                 $zapytanie_cechy = "select options_values_image from products_attributes where products_id = '" . $filtr->process($_POST["id_produktu_org"]) . "' and options_values_id = '" . (int)$podziel_tmp[1] . "' and options_values_image != ''";
                 $sql_cechy = $GLOBALS['db']->open_query($zapytanie_cechy);   
                 //
                 if ((int)$GLOBALS['db']->ile_rekordow($sql_cechy) > 0) {
                     // 
                     $info_dane_cechy = $sql_cechy->fetch_assoc();
                     //
                     if ( isset($info_dane_cechy['options_values_image']) && $info_dane_cechy['options_values_image'] != '' ) {
                          //                  
                          $zdjecie_cechy = $info_dane_cechy['options_values_image'];
                          //
                     }
                     //
                }
                //
                $GLOBALS['db']->close_query($sql_cechy);   
                unset($zapytanie_cechy);                
                //
            }
            //
            unset($podziel_tmp);
            //
        }     
      
        $zapytanie_cechy = "SELECT products_stock_model, products_stock_image, products_stock_ean FROM products_stock WHERE products_stock_attributes = '" . $kombinacja_cech . "' and products_id = '" . $filtr->process($_POST["id_produktu_org"]) . "'";
        $sql_dane_cechy = $db->open_query($zapytanie_cechy);
        //
        if ((int)$db->ile_rekordow($sql_dane_cechy) > 0) {
          $info_dane_cechy = $sql_dane_cechy->fetch_assoc();
          //
          if (!empty($info_dane_cechy['products_stock_model'])) {
              $nr_katalogowy_cechy = $info_dane_cechy['products_stock_model'];
          }
          if (!empty($info_dane_cechy['products_stock_image'])) {
              $zdjecie_cechy = $info_dane_cechy['products_stock_image'];
          }      
          if (!empty($info_dane_cechy['products_stock_ean'])) {
              $kod_ean = $info_dane_cechy['products_stock_ean'];
          }             
          //
          unset($info_dane_cechy);
        }   
        //
        $db->close_query($sql_dane_cechy);      
        //       

        // dodatkowe pola opisowe
        // sprawdzi czy wogole sa 
        $ciagTxt ='';
        //
        if ( isset($_POST['pole_txt_nazwa_1']) ) {
            //
            for ( $p = 1; $p < 50; $p++ ) {
                //                            
                if ( isset($_POST['pole_txt_nazwa_' . $p]) ) {
                    //
                    if ( trim((string)$_POST['pole_txt_wartosc_' . $p]) != '' ) {
                         //
                         if ( ( $_POST['pole_txt_rodzaj_' . $p] == 'plik' && isset($_POST['plik_txt_' . $p]) ) || $_POST['pole_txt_rodzaj_' . $p] == 'edycja' ) {

                             $ciagTxt .= '{#{';
                             //                         
                             $ciagTxt .= $filtr->process($_POST['pole_txt_nazwa_' . $p]) . '|*|' . $filtr->process($_POST['pole_txt_wartosc_' . $p]);
                             //
                             if ( $_POST['pole_txt_rodzaj_' . $p] == 'plik' ) {
                                $ciagTxt .= '|*|plik';
                              } else {
                                $ciagTxt .= '|*|txt';
                             }
                             //
                             $ciagTxt .= '}#}';
                             
                         }
                         //
                    }
                }              
            }
        }
        //

        $pola = array(
                array('products_name',$filtr->process($_POST["nazwa"])),
                array('products_model',$nr_katalogowy_cechy),
                array('products_pkwiu',$filtr->process($_POST["pkwiu"])),
                array('products_gtu',$filtr->process($_POST["gtu"])),
                array('products_ean',$filtr->process($_POST["ean"])),
                array('products_man_code',$filtr->process($_POST["kod_producenta"])),
                array('products_quantity',(float)$_POST["ilosc"]),
                array('products_price',(($_POST['ma_cechy'] == 'tak') ? (float)$_POST["cena_1_podstawa"] : (float)$_POST["cena_1"])),
                array('products_price_tax',(($_POST['ma_cechy'] == 'tak') ? (float)$_POST["brut_1_podstawa"] : (float)$_POST["brut_1"])),
                array('final_price',(float)$_POST["cena_1"]),
                array('final_price_tax',(float)$_POST["brut_1"]),
                array('products_comments',$filtr->process($_POST["komentarz"])),
                array('products_text_fields',$ciagTxt),
                array('products_stock_attributes',$kombinacja_cech)
        );
        
        if ( $zdjecie_cechy != '' ) {
             //
             $pola[] = array('products_image', $zdjecie_cechy);
             //
        } else {
             //
             $pola[] = array('products_image', '');
             //
        }
        
        if ( $kod_ean != '' ) {
             //
             $pola[] = array('products_ean',$kod_ean);
             //
        }        
        
        $stawka_vat = explode('|', (string)$filtr->process($_POST['vat']));
        $pola[] = array('products_tax',(float)$stawka_vat[0]);
        $pola[] = array('products_tax_class_id',(int)$stawka_vat[1]);   
        unset($stawka_vat);        
        
        //			
        $db->update_query('orders_products' , $pola, " orders_products_id = '".(int)$_POST["id_produktu"]."'");	
        unset($pola, $nr_katalogowy_cechy, $zdjecie_cechy, $kod_ean);

        // aktualizacja ilosci sprzedanych produktow
        $Ilosc = $_POST["ilosc_org"] - $_POST["ilosc"];

        if ( $_POST["ilosc_org"] != $_POST["ilosc"] ) {

            $zapytanie_sprzedane = "SELECT products_ordered, products_quantity FROM products WHERE products_id = '".(int)$_POST['id_produktu_org']."'";
            $sql_sprzedane = $db->open_query($zapytanie_sprzedane);

            if ((int)$db->ile_rekordow($sql_sprzedane) > 0) {

                $sprzedane = $sql_sprzedane->fetch_assoc();

                if ( $Ilosc > 0 ) {
                    $sprzedane_akt = $sprzedane['products_ordered'] - $Ilosc;
                    $stanMagazynu_akt = $sprzedane['products_quantity'] + $Ilosc;
                } elseif ( $Ilosc < 0 ) {
                    $IloscProd = abs($Ilosc);
                    $sprzedane_akt = $sprzedane['products_ordered'] + $Ilosc;
                    $stanMagazynu_akt = $sprzedane['products_quantity'] - $IloscProd;
                }

                if ( $Ilosc != 0 ) {

                    if ( MAGAZYN_SPRAWDZ_STANY == 'tak' ) {

                        $pola = array(
                                array('products_ordered',(int)$sprzedane_akt),
                                array('products_quantity',(float)$stanMagazynu_akt));

                    } else {

                        $pola = array(
                                array('products_ordered',(int)$sprzedane_akt));

                    }

                    $db->update_query('products' , $pola, "products_id = '" . (int)$_POST['id_produktu_org'] . "'");

                }

            }

            $db->close_query($sql_sprzedane);         
            unset($zapytanie_sprzedane, $sprzedane, $pola);

        }

        // aktualizacja ilosci cech sprzedanych produktow
        if ( CECHY_MAGAZYN == 'tak' ) {

              if ( $kombinacja_cech != '' ) {

                  // cechy przeslane w formularzu
                  $TablicaKombinacji = explode(',', (string)$kombinacja_cech );
                  natsort($TablicaKombinacji);

                  // cechy w produkcie pierwotne
                  $TablicaKombinacjiOrg = $_POST['cechy_org'];
                  natsort($TablicaKombinacjiOrg);

                  // jezeli nie zostaly zmienione cechy - tylko ilosc produktu
                  if ( !array_diff($TablicaKombinacjiOrg, $TablicaKombinacji) && !array_diff($TablicaKombinacji, $TablicaKombinacjiOrg) && $Ilosc != 0 ) {

                      // wybranie stanow magazynowych cech dla modyfikowanego produktu
                      $zapytanie_cechy_sprzedane = "SELECT products_stock_quantity, products_stock_id, products_stock_attributes FROM products_stock WHERE products_id = '".(int)$_POST["id_produktu_org"]."'";
                      $sql_cechy_sprzedane = $db->open_query($zapytanie_cechy_sprzedane);

                      if ( (int)$db->ile_rekordow($sql_cechy_sprzedane) > 0 ) {

                          while ( $cechy_sprzedane = $sql_cechy_sprzedane->fetch_assoc() ) {

                              $TablicaKombinacjiBaza = array();
                              $TablicaKombinacjiBaza = explode(',', (string)$cechy_sprzedane['products_stock_attributes'] );

                              natsort($TablicaKombinacjiBaza);

                              if ( !array_diff($TablicaKombinacji, $TablicaKombinacjiBaza) && !array_diff($TablicaKombinacjiBaza, $TablicaKombinacji) ) {

                                  if ( $Ilosc > 0 ) {
                                      $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] + $Ilosc;
                                  } else {
                                      $IloscCech = abs($Ilosc);
                                      $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] - $IloscCech;
                                  }

                                  $pola = array(
                                          array('products_stock_quantity',(float)$cechyMagazyn_akt));

                                  $db->update_query('products_stock' , $pola, "products_id = '" . (int)$_POST["id_produktu_org"] . "' AND products_stock_id = '".(int)$cechy_sprzedane['products_stock_id']."'");

                                  unset($pola);

                              }

                              unset($TablicaKombinacjiBaza, $IloscCech);

                          }

                      }

                      $db->close_query($sql_cechy_sprzedane);         
                      unset($zapytanie_cechy_sprzedane, $cechy_sprzedane, $cechyMagazyn_akt);

                  }

                  // jezeli zostaly tylko zmienione cechy - ilosc produktu bez zmian
                  if ( array_diff($TablicaKombinacjiOrg, $TablicaKombinacji) && $Ilosc == 0 ) {

                      // wybranie stanow magazynowych cech dla modyfikowanego produktu
                      $zapytanie_cechy_sprzedane = "SELECT products_stock_quantity, products_stock_id, products_stock_attributes FROM products_stock WHERE products_id = '".(int)$_POST["id_produktu_org"]."'";
                      $sql_cechy_sprzedane = $db->open_query($zapytanie_cechy_sprzedane);

                      if ( (int)$db->ile_rekordow($sql_cechy_sprzedane) > 0 ) {

                          while ( $cechy_sprzedane = $sql_cechy_sprzedane->fetch_assoc() ) {

                              $TablicaKombinacjiBaza = array();
                              $TablicaKombinacjiBaza = explode(',', (string)$cechy_sprzedane['products_stock_attributes'] );

                              natsort($TablicaKombinacjiBaza);

                              //zmiana ilosci dla cechy pierwotnej
                              if ( !array_diff($TablicaKombinacjiOrg, $TablicaKombinacjiBaza) && !array_diff($TablicaKombinacjiBaza, $TablicaKombinacjiOrg) ) {

                                $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] + $_POST["ilosc"];

                                $pola = array(
                                        array('products_stock_quantity',(float)$cechyMagazyn_akt));

                                $db->update_query('products_stock' , $pola, "products_id = '" . (int)$_POST["id_produktu_org"] . "' AND products_stock_id = '".(int)$cechy_sprzedane['products_stock_id']."'");

                                unset($pola, $cechyMagazyn_akt);

                              }

                              //zmiana ilosci dla cechy nowej
                              if ( !array_diff($TablicaKombinacji, $TablicaKombinacjiBaza) && !array_diff($TablicaKombinacjiBaza, $TablicaKombinacji) ) {

                                $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] - $_POST["ilosc"];

                                $pola = array(
                                        array('products_stock_quantity',(float)$cechyMagazyn_akt));

                                $db->update_query('products_stock' , $pola, "products_id = '" . (int)$_POST["id_produktu_org"] . "' AND products_stock_id = '".(int)$cechy_sprzedane['products_stock_id']."'");

                                unset($pola, $cechyMagazyn_akt);

                              }

                              unset($TablicaKombinacjiBaza);

                          }

                      }

                      $db->close_query($sql_cechy_sprzedane);
                      unset($zapytanie_cechy_sprzedane, $cechy_sprzedane);

                  }

                  // jezeli zostaly zmienione cechy oraz ilosc produktu
                  if ( array_diff($TablicaKombinacjiOrg, $TablicaKombinacji) && $Ilosc != 0 ) {

                      // wybranie stanow magazynowych cech dla modyfikowanego produktu
                      $zapytanie_cechy_sprzedane = "SELECT products_stock_quantity, products_stock_id, products_stock_attributes FROM products_stock WHERE products_id = '".(int)$_POST["id_produktu_org"]."'";
                      $sql_cechy_sprzedane = $db->open_query($zapytanie_cechy_sprzedane);

                      if ( (int)$db->ile_rekordow($sql_cechy_sprzedane) > 0 ) {

                          while ( $cechy_sprzedane = $sql_cechy_sprzedane->fetch_assoc() ) {

                              $TablicaKombinacjiBaza = array();
                              $TablicaKombinacjiBaza = explode(',', (string)$cechy_sprzedane['products_stock_attributes'] );

                              natsort($TablicaKombinacjiBaza);

                              //zmiana ilosci dla cechy pierwotnej
                              if ( !array_diff($TablicaKombinacjiOrg, $TablicaKombinacjiBaza) && !array_diff($TablicaKombinacjiBaza, $TablicaKombinacjiOrg) ) {

                                  $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] + $_POST["ilosc_org"];

                                  $pola = array(
                                          array('products_stock_quantity',(float)$cechyMagazyn_akt));

                                  $db->update_query('products_stock' , $pola, "products_id = '" . (int)$_POST["id_produktu_org"] . "' AND products_stock_id = '".(int)$cechy_sprzedane['products_stock_id']."'");

                                  unset($pola, $cechyMagazyn_akt);

                              }

                              //zmiana ilosci dla cechy nowej
                              if ( !array_diff($TablicaKombinacji, $TablicaKombinacjiBaza) && !array_diff($TablicaKombinacjiBaza, $TablicaKombinacji) ) {

                                  $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] - $_POST["ilosc"];

                                  $pola = array(
                                          array('products_stock_quantity',(float)$cechyMagazyn_akt));

                                  $db->update_query('products_stock' , $pola, "products_id = '" . (int)$_POST["id_produktu_org"] . "' AND products_stock_id = '".(int)$cechy_sprzedane['products_stock_id']."'");

                                  unset($pola, $cechyMagazyn_akt);

                              }

                              unset($TablicaKombinacjiBaza);

                          }

                      }

                      $db->close_query($sql_cechy_sprzedane);         
                      unset($zapytanie_cechy_sprzedane, $cechy_sprzedane);

                  }

              }

        }

        Sprzedaz::PodsumowanieZamowieniaAktualizuj($_POST["id"], $_POST["waluta"]);

        //
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].((isset($_POST['zakladka'])) ? '&zakladka='.$filtr->process($_POST["zakladka"]) : ''));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
      <script>
      $(document).ready(function() {
        $("#zamowieniaForm").validate({
          rules: {
            nazwa: {
              required: true
            },
            ilosc: {
              required: true,
              range: [0, 999999]
            },
            cena_1: {
              required: true,
              range: [0, 999999]
            }
          }
        });
      });
      </script>        

      <?php
      if ( !isset($_GET['produkt_id']) ) {
           $_GET['produkt_id'] = 0;
      }          
      
      if ( $JestZamowienie == true && isset($zamowienie->produkty[(int)$_GET['produkt_id']]) && count($zamowienie->produkty[(int)$_GET['produkt_id']]) > 0 ) {
      ?>
        
        <form action="sprzedaz/zamowienia_produkt_edytuj.php" method="post" id="zamowieniaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja produktu</div>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="id_produktu" value="<?php echo (int)$_GET['produkt_id']; ?>" />
                    <?php if ( isset($_GET['zakladka']) ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>
                    <input type="hidden" name="id_produktu_org" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['id_produktu']; ?>" />
                    <input type="hidden" name="waluta" value="<?php echo $zamowienie->info['waluta']; ?>" />
                    <input type="hidden" name="ilosc_org" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ilosc']; ?>" />
                    <input type="hidden" name="typ_cechy" id="typ_cechy" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['typ_cechy']; ?>" />

                    <p>
                      <label class="required" for="nazwa">Nazwa produktu:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo str_replace('"', '&quot;', $zamowienie->produkty[$_GET['produkt_id']]['nazwa']); ?>" size="53" />
                    </p>   

                    <p>
                      <label for="model">Model:</label>
                      <input type="text" name="model" id="model" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['model']; ?>" size="53" />
                    </p>  

                    <p>
                      <label for="model">Kod producenta:</label>
                      <input type="text" name="kod_producenta" id="kod_producenta" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['kod_producenta']; ?>" size="53" />
                    </p>                       

                    <p>
                      <label for="ean">EAN:</label>
                      <input type="text" name="ean" id="ean" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ean']; ?>" size="53" />
                    </p>  

                    <p>
                      <label for="pkwiu">Symbol PKWiU:</label>
                      <input type="text" name="pkwiu" id="pkwiu" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['pkwiu']; ?>" size="53" />
                    </p>  

                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'GTU 01', 'text' => 'GTU 01 - Napoje alkoholowe');
                    $tablica[] = array('id' => 'GTU 02', 'text' => 'GTU 02 - Paliwa');
                    $tablica[] = array('id' => 'GTU 03', 'text' => 'GTU 03 - Oleje opałowe i oleje smarowe');
                    $tablica[] = array('id' => 'GTU 04', 'text' => 'GTU 04 - Wyroby tytoniowe');
                    $tablica[] = array('id' => 'GTU 05', 'text' => 'GTU 05 - Odpady');
                    $tablica[] = array('id' => 'GTU 06', 'text' => 'GTU 06 - Urządzenia elektroniczne oraz części i materiałów do nich');
                    $tablica[] = array('id' => 'GTU 07', 'text' => 'GTU 07 - Pojazdy oraz części samochodowe');
                    $tablica[] = array('id' => 'GTU 08', 'text' => 'GTU 08 - Metale szlachetne oraz nieszlachetne');
                    $tablica[] = array('id' => 'GTU 09', 'text' => 'GTU 09 - Leki oraz wyroby medyczne');
                    $tablica[] = array('id' => 'GTU 10', 'text' => 'GTU 10 - Budynki, budowle i grunty');
                    $tablica[] = array('id' => 'GTU 11', 'text' => 'GTU 11 - Obrót uprawnieniami do emisji gazów cieplarnianych');
                    $tablica[] = array('id' => 'GTU 12', 'text' => 'GTU 12 - Usługi niematerialne m.in. marketingowe, reklamowe');
                    $tablica[] = array('id' => 'GTU 13', 'text' => 'GTU 13 - Usługi transportowe i gospodarki magazynowej');            
                    //             
                    ?>
                    
                    <p>
                      <label for="gtu">Kod GTU:</label>
                      <?php                         
                      echo Funkcje::RozwijaneMenu('gtu', $tablica, $zamowienie->produkty[$_GET['produkt_id']]['gtu'], 'style="max-width:250px" id="gtu"');
                      unset($tablica);
                      ?>
                    </p>                     

                    <p>
                      <label class="required" for="ilosc">Ilość:</label>
                      <input type="text" name="ilosc" id="ilosc" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ilosc']; ?>" size="20" />
                    </p>   

                    <?php
                    $vat = Produkty::TablicaStawekVat('', true, true);
                    $domyslny_vat = $vat[1];

                    foreach ( $vat[0] as $poz_vat ) {
                        //
                        $tb_tmp = explode('|', (string)$poz_vat['id']);
                        if ( $tb_tmp[1] == $zamowienie->produkty[$_GET['produkt_id']]['tax_id'] ) {
                             $domyslny_vat = $poz_vat['id'];
                        }
                        //
                    }
                    //
                    unset($poz_vat);                         
                    ?>
                    <p>
                      <label class="required" for="vat">Stawka VAT:</label>
                      <?php echo Funkcje::RozwijaneMenu('vat', $vat[0], $domyslny_vat, ' id="vat" data-id="vat_cechy"'); ?>
                    </p>   
                    
                    <?php
                    unset($vat, $domyslny_vat);                    

                    $ProduktMaCechy = false;
                    if ( isset($zamowienie->produkty[$_GET['produkt_id']]['attributes']) && count($zamowienie->produkty[$_GET['produkt_id']]['attributes']) > 0 ) {
                         $ProduktMaCechy = true;
                    }
                    ?>
                    
                    <div>
                      <input name="ma_cechy" value="<?php echo (($ProduktMaCechy == true) ? 'tak' : 'nie'); ?>" type="hidden" />
                    </div>
                    
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                    <?php
                    if ( $ProduktMaCechy == true ) {
                    ?>
                    
                    <p>
                      <label class="required" for="cena_1_podstawa">Cena netto produktu bez cech:</label>
                      <input class="oblicz" type="text" name="cena_1_podstawa" id="cena_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_netto']; ?>" size="20" />
                      <input type="hidden" name="cena_1_podstawa_mnoznik" id="cena_1_podstawa_mnoznik" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_netto']; ?>" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p> 

                    <p>
                      <label class="required" for="brut_1_podstawa">Cena brutto produktu bez cech:</label>
                      <input class="oblicz_brutto" type="text" name="brut_1_podstawa" id="brut_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_brutto']; ?>" size="20" />
                      <input type="hidden" name="brut_1_podstawa_mnoznik" id="brut_1_podstawa_mnoznik" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_brutto']; ?>" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p> 

                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                    <?php } else { ?>
                    
                    <div>
                      <input type="hidden" name="cena_1_podstawa" id="cena_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_netto']; ?>" />
                      <input type="hidden" name="cena_1_podstawa_mnoznik" id="cena_1_podstawa_mnoznik" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_netto']; ?>" />
                      <input type="hidden" name="brut_1_podstawa" id="brut_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_brutto']; ?>" />
                      <input type="hidden" name="brut_1_podstawa_mnoznik" id="brut_1_podstawa_mnoznik" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_brutto']; ?>" />
                    </div>
                    
                    <?php } ?>

                    <p>
                      <label class="required" for="cena_1">Cena netto produktu <?php echo (($ProduktMaCechy == true) ? 'z cechami' : ''); ?>:</label>
                      <input class="oblicz" type="text" name="cena_1" id="cena_1" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_netto']; ?>" size="20" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p>   
                    <?php $podatek_vat  = $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_brutto'] - $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_netto']; ?>
                    <p>
                      <label class="required" for="v_at_1">Podatek VAT produktu:</label>
                      <input type="text" name="v_at_1" id="v_at_1" value="<?php echo $podatek_vat; ?>" size="20" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p>   

                    <p>
                      <label class="required" for="brut_1">Cena brutto produktu <?php echo (($ProduktMaCechy == true) ? 'z cechami' : ''); ?>:</label>
                      <input type="text" class="oblicz_brutto" name="brut_1" id="brut_1" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_brutto']; ?>" size="20" />
                      
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p> 
                    
                    <?php
                    if (!empty($zamowienie->produkty[$_GET['produkt_id']]['pola_txt'])) {
                      //
                      $PoleTxt = Funkcje::serialCiag($zamowienie->produkty[$_GET['produkt_id']]['pola_txt']);
                      if ( count($PoleTxt) > 0 ) {
                          $nrPola = 1;
                          foreach ( $PoleTxt as $WartoscTxt ) {
                              //
                              ?>
                              <p>
                                <?php
                                echo '<input type="hidden" value="' . $WartoscTxt['nazwa'] . '" name="pole_txt_nazwa_' . $nrPola . '" />';
                                if ($WartoscTxt['typ'] == 'plik') {
                                    ?>
                                    <label>Załącznik:</label>
                                    <?php                                    
                                    echo '<input type="checkbox" name="plik_txt_' . $nrPola . '" id="plik_txt_' . $nrPola . '" value="1" checked="checked" /><label class="OpisFor OpisForPustyLabel" for="plik_txt_' . $nrPola . '"></label>';
                                    echo '<a target="_blank" href="' . ADRES_URL_SKLEPU . '/wgrywanie/' . $WartoscTxt['tekst'] . '">załączony plik</a>';
                                    echo '<input type="hidden" name="pole_txt_wartosc_'. $nrPola . '" id="pole_txt_wartosc_'. $nrPola . '" value="' . $WartoscTxt['tekst'] . '" />';
                                    echo '<input type="hidden" name="pole_txt_rodzaj_' . $nrPola . '" value="plik" />';
                                } else {
                                    ?>
                                    <label for="pole_txt_wartosc_<?php echo $nrPola; ?>"><?php echo $WartoscTxt['nazwa']; ?>:</label>
                                    <textarea name="pole_txt_wartosc_<?php echo $nrPola; ?>" id="pole_txt_wartosc_<?php echo $nrPola; ?>" cols="60" rows="2"><?php echo $WartoscTxt['tekst']; ?></textarea>
                                    <input type="hidden" name="pole_txt_rodzaj_<?php echo $nrPola; ?>" value="edycja" />
                                    <?php
                                }
                                ?>
                              </p>                              
                              <?php
                              //    
                              $nrPola++;
                          }
                      }
                      unset($PoleTxt);
                      //
                    }                    
                    ?>

                    <p>
                      <label for="komentarz">Komentarz:</label>
                      <textarea name="komentarz" id="komentarz" cols="60" rows="3"><?php echo $zamowienie->produkty[$_GET['produkt_id']]['komentarz']; ?></textarea>
                    </p>    

                    <?php

                    $wartosc_cech_produktu = 0;
                    $wartosc_cech_produktu_tax = 0;
                    $i = 0;

                    if ( $ProduktMaCechy == true ) {

                      echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />';
                      
                      echo '<div id="CechyProduktu">';

                      $cechy = '';

                      foreach ( $zamowienie->produkty[$_GET['produkt_id']]['attributes'] as $cecha ) {
                        $cecha_biezaca = $cecha['id_cechy'].'-'.$cecha['id_wartosci'];
                        $cechy .= $cecha_biezaca .',';
                        echo '<p id="CechyProduktuEdycja">';
                        echo '<label for="cecha_'.$cecha['id_cechy'].'">'.$cecha['cecha'].':</label>';
                        $tablica = Funkcje::lista_wartosci_cechy_produktu($zamowienie->produkty[$_GET['produkt_id']]['id_produktu'], $cecha['id_cechy'], $zamowienie->produkty[$_GET['produkt_id']]['id_waluty'], $zamowienie->produkty[$_GET['produkt_id']]['tax']);

                        echo '<input type="hidden" name="cechy_org[]" value="'.$cecha_biezaca.'" />';

                        echo Sprzedaz::RozwijaneMenuCechy('cecha['.$cecha['id_cechy'].']', $tablica, $cecha['id_wartosci'], 'style="width:200px;" id="cecha_'.$cecha['id_cechy'].'" onchange="wyswietlCechy('.$cecha['id_cechy'].');" ','','', $zamowienie->produkty[$_GET['produkt_id']]['id_waluty'], $zamowienie->info['waluta'] );

                        echo ' &nbsp; <input type="hidden" name="cecha_prefix['.$cecha['id_cechy'].']" value="'.(($cecha['prefix'] == '') ? '+' : $cecha['prefix']).'" id="cecha_prefix_'.$cecha['id_cechy'].'" />';
                        echo '<input class="oblicz" ' . (($zamowienie->produkty[$_GET['produkt_id']]['typ_cechy'] == 'ceny') ? 'style="display:none"' : '') . ' size="10" type="text" name="cecha_cena_netto['.$cecha['id_cechy'].']" value="'.$cecha['cena_netto'].'" id="cecha_cena_'.$cecha['id_cechy'].'" /> &nbsp; ';
                        echo '<input class="oblicz_brutto" ' . (($zamowienie->produkty[$_GET['produkt_id']]['typ_cechy'] == 'ceny') ? 'style="display:none"' : '') . ' size="10" type="text" name="cecha_cena_brutto['.$cecha['id_cechy'].']" value="'.$cecha['cena_brutto'].'" id="cecha_brut_'.$cecha['id_cechy'].'" /> <span id="cecha_symbol_'.$cecha['id_cechy'].'" ' . (($zamowienie->produkty[$_GET['produkt_id']]['typ_cechy'] != 'ceny' && $cecha['prefix'] != '*') ? '' : 'style="display:none"') . '>' . $_SESSION['waluta_zamowienia_symbol'] . '</span>';
                        echo '</p>';

                        $i++;
                      }
                      $cechy = substr((string)$cechy, 0 ,-1);
                    }
                    
                    echo '</div>';
                    
                    unset($ProduktMaCechy);

                    ?>
                    <input type="hidden" name="ilosc_pierwotna" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ilosc']; ?>" />

                    </div>
                 
                </div>
                
                <script>         
                sumaCech();
                </script>                 
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                </div>

          </div>                      
        </form>

        <?php

      } else {

        ?>
        
        <div class="poleForm"><div class="naglowek">Edycja produktu</div>
            <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
        </div>
        
        <?php
        
      }

      ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}