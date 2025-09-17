<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if ( Funkcje::SprawdzAktywneAllegro() ) {

        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
    
        if ( isset($_POST['akcja']) ) {

          $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . (int)$_POST["id_aukcji"] . "'";
          $sql = $db->open_query($zapytanie);

          if ( $db->ile_rekordow($sql) > 0 ) {
          
            $info = $sql->fetch_assoc();
          
            $id_aukcji = floatval($info['auction_id']);
            
            $PrzetwarzanaAukcja = $AllegroRest->commandGet('sale/product-offers/'.$id_aukcji);

            if ( is_object($PrzetwarzanaAukcja) && !isset($PrzetwarzanaAukcja->errors) ) {

                $DaneDoAktualizacji = new stdClass();
                $DaneDoAktualizacji->name = $filtr->process($_POST['nazwa_nowa']);
                $DaneDoAktualizacji->stock = new stdClass();
                $DaneDoAktualizacji->stock->available = (int)$_POST['ilosc_nowa'];
                $DaneDoAktualizacji->sellingMode = new stdClass();
                $DaneDoAktualizacji->sellingMode->price = new stdClass();
                $DaneDoAktualizacji->sellingMode->price->amount = $_POST['cena_nowa'];

                $DaneDoAktualizacji->delivery = new stdClass();
                $DaneDoAktualizacji->delivery->handlingTime = $_POST['czas_wysylki'];
                $DaneDoAktualizacji->delivery->shippingRates = new stdClass();
                $DaneDoAktualizacji->delivery->shippingRates->id = $_POST['cennik_dostawy'];

                if ( $_POST['warunki_reklamacji'] != '' || $_POST['warunki_zwrotow'] != '' || $_POST['warunki_gwarancji'] != '' ) {
                    $DaneDoAktualizacji->afterSalesServices = new stdClass();
                }

                if ( $_POST['warunki_reklamacji'] != '' ) {
                    $DaneDoAktualizacji->afterSalesServices->impliedWarranty = new stdClass();
                    $DaneDoAktualizacji->afterSalesServices->impliedWarranty->id = $_POST['warunki_reklamacji'];
                }

                if ( $_POST['warunki_zwrotow'] != '' ) {
                    $DaneDoAktualizacji->afterSalesServices->returnPolicy = new stdClass();
                    $DaneDoAktualizacji->afterSalesServices->returnPolicy->id = $_POST['warunki_zwrotow'];
                }

                if ( $_POST['warunki_gwarancji'] != '' ) {
                    $DaneDoAktualizacji->afterSalesServices->warranty = new stdClass();
                    $DaneDoAktualizacji->afterSalesServices->warranty->id = $_POST['warunki_gwarancji'];
                }

                $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $DaneDoAktualizacji );

                if ( is_object($rezultat) && !isset($rezultat->errors) ) {
                                      
                        // aktualizacja promowan
                        
                        $JakiePromowania = explode(',', (string)$_POST['aktualne_promowania']);
                        $ExtraPackage = '';
                        $BasePackage = '';
                        
                        if ( isset($_POST['basepackage']) ) {
                             $BasePackage = $_POST['basepackage'];  
                        }
                        if ( isset($_POST['extrapackage']) ) {
                             $ExtraPackage = $_POST['extrapackage'];
                        }
                        
                        // najpierw wszystko usunie
                        if ( $ExtraPackage == '' ) {
                             //
                             $AllegroTablica = array();
                             $AllegroTablica['modifications'] = array( array('modificationType' => 'REMOVE_NOW',
                                                                             'packageType' => 'EXTRA',
                                                                             'packageId' => 'departmentPage' ));
                             $Wynik = $AllegroRest->commandPost('sale/offers/' . $id_aukcji . '/promo-options-modification', $AllegroTablica);
                             //
                        } else {
                             //
                             $AllegroTablica = array();
                             $AllegroTablica['modifications'] = array( array('modificationType' => 'CHANGE',
                                                                             'packageType' => 'EXTRA',
                                                                             'packageId' => 'departmentPage' ));
                             $Wynik = $AllegroRest->commandPost('sale/offers/' . $id_aukcji . '/promo-options-modification', $AllegroTablica);
                             //
                        }
                        
                        // usuwa wszystkie opcje BASE
                        
                        // wyroznienie 10 dni
                        $AllegroTablica = array();
                        $AllegroTablica['modifications'] = array( array('modificationType' => 'REMOVE_NOW',
                                                                        'packageType' => 'BASE',
                                                                        'packageId' => 'emphasized10d' ));
                        $Wynik = $AllegroRest->commandPost('sale/offers/' . $id_aukcji . '/promo-options-modification', $AllegroTablica);
                        //
                        // wyroznienie 1 dzien
                        $AllegroTablica = array();
                        $AllegroTablica['modifications'] = array( array('modificationType' => 'REMOVE_NOW',
                                                                        'packageType' => 'BASE',
                                                                        'packageId' => 'emphasized1d' ));
                        $Wynik = $AllegroRest->commandPost('sale/offers/' . $id_aukcji . '/promo-options-modification', $AllegroTablica);                  
                        //
                        // pakiet promo
                        $AllegroTablica = array();
                        $AllegroTablica['modifications'] = array( array('modificationType' => 'REMOVE_NOW',
                                                                        'packageType' => 'BASE',
                                                                        'packageId' => 'promoPackage' ));
                        $Wynik = $AllegroRest->commandPost('sale/offers/' . $id_aukcji . '/promo-options-modification', $AllegroTablica);                  
                        
                        if ( $BasePackage != '' ) {
                             //
                             $AllegroTablica = array();
                             $AllegroTablica['modifications'] = array( array('modificationType' => 'CHANGE',
                                                                             'packageType' => 'BASE',
                                                                             'packageId' => $BasePackage ));
                             $Wynik = $AllegroRest->commandPost('sale/offers/' . $id_aukcji . '/promo-options-modification', $AllegroTablica); 
                             //
                        }

                        $WyroznienieCiag = implode(',', array($BasePackage, $ExtraPackage));
                
                        $pola = array(
                                array('products_buy_now_price', (float)$_POST['cena_nowa']),
                                array('auction_quantity', (int)$_POST['ilosc_nowa']),
                                array('products_name', $filtr->process($_POST['nazwa_nowa'])),
                                array('allegro_options', $WyroznienieCiag)
                        );

                        $db->update_query('allegro_auctions' , $pola, " allegro_id = '" . (int)$_POST["id_aukcji"] . "'");              
                        unset($pola);                   

                        $wynik = '<div id="zaimportowano" style="margin:20px 20px 10px 20px">Dane na aukcji zostały zaktualizowane</div>';
                        
                } elseif ( is_object($rezultat) && ( isset($rezultat->errors) && count($rezultat->errors) > 0 ) ) {

                        $wynik = '<div class="ostrzezenie" style="margin:20px 20px 10px 20px">Wystąpił problem w Allegro :<br />';
                        foreach ( $rezultat->errors as $Blad ) {

                            $wynik .= '<b>'.$Blad->userMessage . '</b><br />';

                        }
                        $wynik .= '</div>';

                } else {

                        $wynik = '<div class="ostrzezenie" style="margin:20px 20px 10px 20px">Aukcja o nr <b>' . $id_aukcji . '</b> nie została odnaleziona</div>';
                      
                }

            } else {
                $wynik = '<div class="ostrzezenie" style="margin:20px 20px 10px 20px">Wystąpił problem w Allegro :<br />';
                foreach ( $PrzetwarzanaAukcja->errors as $Blad ) {

                    $wynik .= '<b>'.$Blad->message . '</b><br />';

                }
                $wynik .= '</div>';
            }

            unset($info);

          }
          
          $db->close_query($sql);
          unset($zapytanie);      
          

        }

    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Obsługa aukcji</div>
    <div id="cont">
          
        <form action="allegro/allegro_aukcja_zaktualizuj_parametry.php?id_poz=<?php echo (int)$_GET["id_poz"]; ?>" method="post" id="poForm" class="cmxform">          

        <div class="poleForm">
        
            <div class="naglowek">Aktualizacja parametrów aukcji</div>
            
            <?php
            if ( $wynik == '' ) {
            
                if ( !isset($_GET['id_poz']) ) {
                     $_GET['id_poz'] = 0;
                }    
                
                $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . (int)$_GET["id_poz"] . "' AND auction_type = 'BUY_NOW' AND auction_seller = '".$_SESSION['domyslny_uzytkownik_allegro']."'";
                $sql = $db->open_query($zapytanie);

                if ((int)$db->ile_rekordow($sql) > 0) {

                    $info = $sql->fetch_assoc();

                    $PrzetwarzanaAukcja = $AllegroRest->commandGet('sale/product-offers/'.$info['auction_id']);

                    if ( isset($PrzetwarzanaAukcja->errors) ) {

                        $val = 'NOT_FOUND';
                        foreach($PrzetwarzanaAukcja->errors as $obj) {
                            if ($val == $obj->code) {

                                ?>
                                <div class="pozycja_edytowana"><div class="maleInfo">Aukcja o numerze <?php echo $info['auction_id']; ?> nie odnaleziona w Allegro</div></div>
                                <div class="przyciski_dolne">
                                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>     
                                </div>

                                <?php
                            } else {
                                ?>
                                <div class="pozycja_edytowana"><div class="maleInfo">Błąd : <?php echo $obj->userMessage; ?></div></div>
                                <?php
                            }
                        }
                        ?>
                        <div class="przyciski_dolne">
                            <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>     
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="pozycja_edytowana">
                                
                            <input type="hidden" name="akcja" value="zapisz" />
                            <input type="hidden" name="id_poz" value="<?php echo (int)$_GET["id_poz"]; ?>" />
                            <input type="hidden" name="id_aukcji" value="<?php echo $info['allegro_id']; ?>" />
                                
                            <div class="info_content">
                                    
                                        <script>
                                        $(document).ready(function() {
                                            $("#poForm").validate({
                                              rules: {
                                                cena_nowa: {
                                                  required: true, range: [0.01, 1000000], number: true 
                                                },
                                                ilosc_nowa: {
                                                  required: true, range: [1, 100000], number: true 
                                                },
                                                nazwa_nowa: {
                                                  required: true 
                                                }                                   
                                              },
                                              messages: {
                                                cena_nowa: {
                                                  required: "Pole jest wymagane.",
                                                  range: "Niepoprawna wartość ceny."
                                                },
                                                ilosc_nowa: {
                                                  required: "Pole jest wymagane.",
                                                  range: "Niepoprawna wartość ilości."
                                                },
                                                nazwa_nowa: {
                                                  required: "Pole jest wymagane."
                                                }                                   
                                              }
                                            });
                                        });
                                        
                                        </script>

                                        <div class="DaneAukcjaNaglowek">Podstawowe dane produktu</div>
                                        
                                        <div id="OknoDiv">
                                        
                                            <p>
                                              <label>Aktualna nazwa na Allegro:</label>
                                              <input type="text" name="nazwa_obecna" id="nazwa_obecna" disabled="disabled" value="<?php echo $PrzetwarzanaAukcja->name; ?>" size="70" /> 
                                            </p>

                                            <p>
                                              <label for="nazwa_nowa">Nowa nazwa produktu:</label>
                                              <input type="text" name="nazwa_nowa" id="nazwa_nowa" onkeyup="licznik_znakow(this,'iloscZnakowNazwa',75)" value="<?php echo $info['products_name']; ?>" size="70" /> 
                                            </p>

                                            <p>
                                              <label></label>
                                              Ilość znaków do wpisania: <span class="iloscZnakow" style="display:inline" id="iloscZnakowNazwa"><?php echo (75-strlen(mb_convert_encoding((string)$info['products_name'], 'ISO-8859-1', 'UTF-8'))); ?></span>
                                            </p>
                                            
                                        </div>   

                                        <div id="OknoDiv">
                                        
                                            <?php
                                            $cenaZmiany = $info['products_buy_now_price'];
                                            
                                            ?>
                                            <p>
                                              <label>Aktualna cena na Allegro:</label>
                                              <input type="text" name="cena_obecna" class="kropka" disabled="disabled" id="cena_obecna" value="<?php echo $PrzetwarzanaAukcja->sellingMode->price->amount; ?>" size="20" />              
                                            </p> 
                                            
                                            <?php
                                            // id waluty PLN
                                            $IdPLN = 1;
                                            //
                                            if ( isset($_SESSION['tablica_walut_kod']) ) {
                                                //
                                                $IdWalut = $_SESSION['tablica_walut_kod'];
                                                foreach ( $IdWalut as $WalutaSklepu ) {
                                                    //
                                                    if ( $WalutaSklepu['kod'] == 'PLN' ) {
                                                         $IdPLN = $WalutaSklepu['id'];
                                                    }
                                                    //
                                                }
                                                unset($IdWalut);  
                                                //
                                            }  
                                            //
                                            $zapytanieProdukt = "SELECT ap.products_id,
                                                                        ap.products_stock_attributes,
                                                                        ps.products_stock_quantity as iloscMagazynCech,
                                                                        ps.products_stock_price_tax,
                                                                        p.products_price_tax,
                                                                        p.products_currencies_id, 
                                                                        p.products_points_only,     
                                                                        p.options_type,
                                                                        p.products_quantity
                                                                   FROM allegro_auctions ap 
                                                              LEFT JOIN products p ON p.products_id = ap.products_id 
                                                              LEFT JOIN products_stock ps ON ps.products_id = ap.products_id AND ps.products_stock_attributes = replace(ap.products_stock_attributes,'x', ',')
                                                                        WHERE ap.allegro_id = '" . $info['allegro_id'] . "'";  

                                            $sqlProdukt = $db->open_query($zapytanieProdukt);
                                            $produkt = $sqlProdukt->fetch_assoc();  
                                            $db->close_query($sqlProdukt);  

                                            if ( $produkt['products_stock_attributes'] != '' ) {
                                                $ilosc_magazyn = (float)$produkt['iloscMagazynCech'];
                                            } else {
                                                $ilosc_magazyn = (float)$produkt['products_quantity'];
                                            }

                                            if ( $produkt['products_points_only'] == 0 ) {
                                                 //
                                                 echo '<div class="maleInfo">Cena w sklepie: ';
                                                 //
                                                 if ( $produkt['options_type'] == 'ceny' && $produkt['products_stock_price_tax'] > 0 ) {
                                                      echo $waluty->FormatujCene($produkt['products_stock_price_tax'], true, $IdPLN, '', 2, $produkt['products_currencies_id']);                 $cenaZmiany = $produkt['products_stock_price_tax'];
                                                   } else {
                                                      echo $waluty->FormatujCene(Produkt::ProduktCenaCechy($produkt['products_id'], $produkt['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), true, $IdPLN, '', 2, $produkt['products_currencies_id']);
                                                      $cenaZmiany = Produkt::ProduktCenaCechy($produkt['products_id'], $produkt['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes']));
                                                 }
                                                 //
                                                 echo '</div>';
                                                 //
                                            }   
                                            // cena z zakladki dane allegro produktu
                                            $sqlProdukt = $db->open_query("SELECT * FROM products_allegro_info WHERE products_id = '" . (int)$info['products_id'] . "'");                                
                                            $produktAllegro = $sqlProdukt->fetch_assoc();  

                                            if ( isset($produktAllegro['products_price_allegro']) && $produktAllegro['products_price_allegro'] > 0 ) {
                                                 //
                                                 $cenaZmiany = $produktAllegro['products_price_allegro'];
                                                 //
                                            }
                                            
                                            $db->close_query($sqlProdukt);  

                                            if ( isset($produktAllegro['products_price_allegro']) && $produktAllegro['products_price_allegro'] > 0 ) {
                                                 //
                                                 echo '<div class="maleInfo">Cena dla Allegro: ';
                                                    echo $waluty->FormatujCene($produktAllegro['products_price_allegro'], true, $IdPLN, '', 2, $produkt['products_currencies_id']);
                                                 echo '</div>';
                                                 //
                                            }
                                            //
                                            unset($IdPLN, $zapytanieProdukt);
                                            ?>
                                            <p>
                                              <label for="cena_nowa">Nowa cena produktu:</label>
                                              <input type="text" name="cena_nowa" class="kropka" id="cena_nowa" value="<?php echo $cenaZmiany; ?>" size="20" />              
                                            </p> 

                                        </div>
                                        
                                        <div id="OknoDiv">

                                            <p>
                                              <label>Aktualna ilość na Allegro:</label>
                                              <input type="text" name="ilosc_obecna" id="ilosc_obecna" disabled="disabled" value="<?php echo $PrzetwarzanaAukcja->stock->available; ?>" size="20" /> 
                                            </p>
                                            <p>
                                              <label for="ilosc_nowa">Nowa ilość produktu:</label>
                                              <input type="text" name="ilosc_nowa" id="ilosc_nowa" value="<?php echo $ilosc_magazyn; ?>" size="20" /> 
                                            </p>

                                        </div>
                                        
                                        <div class="DaneAukcjaNaglowek">Dostawa, płatność i warunki oferty</div>
                                        <div id="OknoDiv">

                                              <p>
                                                <label class="required" for="czas_wysylki">Czas wysyłki:</label>
                                                <select name="czas_wysylki" id="czas_wysylki">
                                                    <option value="PT0S" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT0S' ? 'selected="selected"' : ''); ?>>natychmiast</option>
                                                    <option value="PT24H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT24H' ? 'selected="selected"' : ''); ?>>24 godziny</option>
                                                    <option value="PT48H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT48H' ? 'selected="selected"' : ''); ?>>2 dni</option>
                                                    <option value="PT72H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT72H' ? 'selected="selected"' : ''); ?>>3 dni</option>
                                                    <option value="PT96H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT96H' ? 'selected="selected"' : ''); ?>>4 dni</option>
                                                    <option value="PT120H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT120H' ? 'selected="selected"' : ''); ?>>5 dni</option>
                                                    <option value="PT168H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT168H' ? 'selected="selected"' : ''); ?>>7 dni</option>
                                                    <option value="PT240H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT240H' ? 'selected="selected"' : ''); ?>>10 dni</option>
                                                    <option value="PT336H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT336H' ? 'selected="selected"' : ''); ?>>14 dni</option>
                                                    <option value="PT504H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT504H' ? 'selected="selected"' : ''); ?>>21 dni</option>
                                                    <option value="PT720H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT720H' ? 'selected="selected"' : ''); ?>>30 dni</option>
                                                    <option value="PT1440H" <?php echo ($PrzetwarzanaAukcja->delivery->handlingTime == 'PT1440H' ? 'selected="selected"' : ''); ?>>60 dni</option>
                                                </select>
                                              </p>                  

                                              <p>
                                                <label class="required" for="cennik_dostawy">Cennik dostawy:</label>
                                                <select name="cennik_dostawy" id="cennik_dostawy" style="width:300px;">

                                                    <?php
                                                    $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                                    $cennik_dostaw = $AllegroRest->commandRequest('sale/shipping-rates', $dane, '');
                                                    
                                                    if ( isset($cennik_dostaw->shippingRates) && count($cennik_dostaw->shippingRates) > 0 ) {
                                                         //
                                                         foreach ( $cennik_dostaw->shippingRates as $cennik ) {
                                                             //
                                                             echo '<option value="' . $cennik->id . '" '.($PrzetwarzanaAukcja->delivery->shippingRates->id == $cennik->id ? 'selected="selected"' : '' ).'>' . $cennik->name . '</option>';
                                                             //
                                                         }
                                                         //
                                                    }
                                                    
                                                    unset($cennik_dostaw);
                                                    ?>
                                                                              
                                                </select><em class="TipIkona"><b>Wyświetlane są cenniki dostaw zdefiniowane bezpośrednio w Allegro</b></em>
                                              </p>    

                                              <?php //if ( isset($PrzetwarzanaAukcja->afterSalesServices->returnPolicy->id) ) { ?>
                                                  <p>
                                                    <label for="warunki_zwrotow" <?php echo (($AllegroRest->ParametryPolaczenia['ClientType'] == 'F') ? 'class="required"' : ''); ?>>Warunki zwrotów:</label>
                                                    <select name="warunki_zwrotow" id="warunki_zwrotow" style="width:300px;">
                                                        <option value="">--- wybierz ---</option>
                                                        
                                                        <?php
                                                        //
                                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                                        $warunki_zwrotow = $AllegroRest->commandRequest('after-sales-service-conditions/return-policies', $dane, '');
                                                        
                                                        if ( isset($warunki_zwrotow->returnPolicies) && count($warunki_zwrotow->returnPolicies) > 0 ) {
                                                             //
                                                             foreach ( $warunki_zwrotow->returnPolicies as $zwrot ) {
                                                                 //
                                                                 echo '<option value="' . $zwrot->id . '" '.( isset($PrzetwarzanaAukcja->afterSalesServices->returnPolicy->id) && $PrzetwarzanaAukcja->afterSalesServices->returnPolicy->id == $zwrot->id ? 'selected="selected"' : '' ) .'>' . $zwrot->name . '</option>';
                                                                 //
                                                             }
                                                             //
                                                        }
                                                        //
                                                        unset($zwroty, $zwrot, $dane);
                                                        ?>                                    
                                             
                                                    </select><em class="TipIkona"><b>Wyświetlane są warunki zwrotów zdefiniowane bezpośrednio w Allegro</b></em>                                
                                                  </p> 
                                                  <?php if ( $AllegroRest->ParametryPolaczenia['ClientType'] == 'F' ) { ?>
                                                  <script>
                                                  $(document).ready(function() {
                                                      $('#warunki_zwrotow').rules( "add", {
                                                          required: true, messages: { required: "Proszę wybrać warunki zwrotów." } 
                                                      });
                                                  });
                                                  </script>                                 
                                                  <?php } ?>
                                              <?php //} ?>

                                              <?php //if ( isset($PrzetwarzanaAukcja->afterSalesServices->impliedWarranty->id) ) { ?>
                                                  <p>
                                                    <label for="warunki_reklamacji" <?php echo (($AllegroRest->ParametryPolaczenia['ClientType'] == 'F') ? 'class="required"' : ''); ?>>Warunki reklamacji:</label>
                                                    <select name="warunki_reklamacji" id="warunki_reklamacji" style="width:300px;">
                                                        <option value="">--- wybierz ---</option>
                                                        
                                                        <?php
                                                        
                                                        //
                                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                                        $reklamacje = $AllegroRest->commandRequest('after-sales-service-conditions/implied-warranties', $dane, '');
                                                        
                                                        if ( isset($reklamacje->impliedWarranties) && count($reklamacje->impliedWarranties) > 0 ) {
                                                             //
                                                             foreach ( $reklamacje->impliedWarranties as $reklamacja ) {
                                                                 //
                                                                 echo '<option value="' . $reklamacja->id . '" '.( isset($PrzetwarzanaAukcja->afterSalesServices->impliedWarranty->id) && $PrzetwarzanaAukcja->afterSalesServices->impliedWarranty->id == $reklamacja->id ? 'selected="selected"' : '' ) .'>' . $reklamacja->name . '</option>';
                                                                 //
                                                             }
                                                             //
                                                        }
                                                        //
                                                        unset($reklamacje, $reklamacja, $dane);
                                                        ?>      
                                                        
                                                    </select><em class="TipIkona"><b>Wyświetlane są warunki reklamacji zdefiniowane bezpośrednio w Allegro</b></em>
                                                  </p>
                                                  <?php if ( $AllegroRest->ParametryPolaczenia['ClientType'] == 'F' ) { ?>
                                                  <script>
                                                  $(document).ready(function() {
                                                      $('#warunki_reklamacji').rules( "add", {
                                                          required: true, messages: { required: "Proszę wybrać warunki reklamacji." } 
                                                      });
                                                  });
                                                  </script>                                 
                                                  <?php } ?>                              
                                              <?php //} ?>

                                              <?php //if ( isset($PrzetwarzanaAukcja->afterSalesServices->warranty->id) ) { ?>
                                                  <p>
                                                    <label for="warunki_gwarancji">Gwarancja:</label>
                                                    <select name="warunki_gwarancji" id="warunki_gwarancji" style="width:300px;">
                                                        <option value="">--- wybierz ---</option>

                                                        <?php
                                                        //
                                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                                        $gwarancje = $AllegroRest->commandRequest('after-sales-service-conditions/warranties', $dane, '');
                                                        
                                                        if ( isset($gwarancje->warranties) && count($gwarancje->warranties) > 0 ) {
                                                             //
                                                             foreach ( $gwarancje->warranties as $gwarancja ) {
                                                                 //
                                                                 echo '<option value="' . $gwarancja->id . '" '.( isset($PrzetwarzanaAukcja->afterSalesServices->warranty->id) && $PrzetwarzanaAukcja->afterSalesServices->warranty->id == $gwarancja->id ? 'selected="selected"' : '' ) .'>' . $gwarancja->name . '</option>';
                                                                 //
                                                             }
                                                             //
                                                        }
                                                        //
                                                        unset($gwarancje, $gwarancja, $dane);
                                                        ?>
                                                                                  
                                                    </select><em class="TipIkona"><b>Wyświetlane są gwarancje zdefiniowane bezpośrednio w Allegro</b></em>
                                                  </p>             
                                              <?php //} ?>

                                              <div class="DaneAukcjaNaglowek">Opcje promowania</div>
                                              
                                              <div class="OknoDiv">
                                                 <label>Opcje promowania:</label>
                                                 
                                                 <?php 
                                                 $DanePromowania = $AllegroRest->commandGet('sale/offers/' . $PrzetwarzanaAukcja->id . '/promo-options');

                                                 $JakiePromowania = array();
                                                 
                                                 if ( isset($DanePromowania->basePackage) ) {

                                                      $DanePromowaniaAktualne = get_object_vars($DanePromowania->basePackage);
                                                   
                                                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'emphasized10d' ) { $JakiePromowania[] = 'emphasized10d'; }
                                                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'emphasized1d' ) { $JakiePromowania[] = 'emphasized1d'; }
                                                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'promoPackage' ) { $JakiePromowania[] = 'promoPackage'; }

                                                      if ( is_array($DanePromowania->extraPackages) && isset($DanePromowania->extraPackages[0]) ) {
                                                          $DanePromowaniaAktualne = get_object_vars((object)$DanePromowania->extraPackages[0]);
                                                      }
                                                  
                                                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'departmentPage' ) { $JakiePromowania[] = 'departmentPage'; } 
                                                      
                                                 }
                                                 ?>
                                                 
                                                 <div class="OknoParametryWybor">                             
                                                 
                                                    <input type="hidden" value="<?php echo implode(',', (array)$JakiePromowania); ?>" name="aktualne_promowania" />
                                                    <input type="radio" name="basepackage" value="emphasized10d" id="wyroznienie10d" <?php echo ( in_array('emphasized10d', $JakiePromowania) == true ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="wyroznienie10d">Wyróżnienie (10 dni)</label> <br />
                                                    <input type="radio" name="basepackage" value="emphasized1d" id="wyroznienie1d" <?php echo ( in_array('emphasized1d', $JakiePromowania) == true ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="wyroznienie1d">Wyróżnienie (1 dzień)</label> <br />
                                                    <input type="radio" name="basepackage" value="promoPackage" id="pakiet_promo" <?php echo ( in_array('promoPackage', $JakiePromowania) == true ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="pakiet_promo">Pakiet Promo (wyróżnienie, podświetlenie i pogrubienie)</label> <br />

                                                    <br /><input type="checkbox" name="extrapackage" value="departmentPage" id="strona_dzialu" <?php echo ( in_array('departmentPage', $JakiePromowania) == true ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="strona_dzialu">Promowanie na stronie działu</label> <br />
                                                    
                                                 </div>
                                                 
                                                 <?php unset($DanePromowania, $JakiePromowania); ?>
                                                 
                                              </div>
                                              
                                        </div> 

                             </div>
                                
                        </div>
                                  
                        <div class="przyciski_dolne">
                            <input type="submit" class="przyciskNon" value="Zmień dane na aukcji" />
                            <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>     
                        </div>

                        <?php
                    }

                    $db->close_query($sql);
                    unset($info);

                } else {
                
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                    ?>
                    <div class="przyciski_dolne">
                      <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>     
                    </div>
                    <?php
                }
                
            } else {
              
                echo $wynik;
                ?>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>     
                </div>                
                
                <?php              
            }
            ?>                   
                
        </div>
        
        </form>
      
    </div>
    
    <?php
    include('stopka.inc.php');

}